<?php
// mail ready
// addnews ready
// translator ready
define('ALLOW_ANONYMOUS', true);
require_once 'common.php';
require_once 'lib/systemmail.php';
require_once 'lib/checkban.php';
require_once 'lib/http.php';
require_once 'lib/serverfunctions.class.php';

tlschema('login');
translator_setup();
$op = httpget('op');
$name = httppost('name');
$iname = getsetting('innname', LOCATION_INN);
$vname = getsetting('villagename', LOCATION_FIELDS);

if ($name != '')
{
    if ($session['loggedin'])
    {
		redirect('badnav.php');
    }
    else
    {
		$password = httppost('password');
		$password = stripslashes($password);
        if (substr($password, 0, 5) == "!md5!")
        {
			$password = md5(substr($password, 5));
        }
        elseif (substr($password, 0, 6) == "!md52!" && strlen($password) == 38)
        {
			$force = httppost('force');
            if ($force)
            {
				$password = substr($password, 6);
				$password = preg_replace("/[^a-f0-9]/", "", $password);
            }
            else
            {
				$password='no hax0rs for j00!';
			}
        }
        else
        {
			$password = md5(md5($password));
		}

        checkban(); //check if this computer is banned

		$sql = "SELECT * FROM " . DB::prefix("accounts") . " WHERE login = '$name' AND password='$password' AND locked=0";
		$result = DB::query($sql);
        if ($result->count() == 1)
        {
            $session['user'] = $result->current();
			$companions = @unserialize($session['user']['companions']);
			if (!is_array($companions)) $companions = [];
			$baseaccount = $session['user'];
			checkban($session['user']['login']); //check if this account is banned

			// If the player isn't allowed on for some reason, anything on
			// this hook should automatically call page_footer and exit
			// itself.
			modulehook('check-login');
			if (ServerFunctions::isTheServerFull() == true && httppost('force') != 1)
			{
				//sanity check if the server is / got full --> back to home
				$session['message'] = translate_inline("`4Sorry, server full!");
				$session['user'] = [];
				redirect('home.php');
			}

            if ($session['user']['emailvalidation'] != '' && substr($session['user']['emailvalidation'],0,1) != 'x')
            {
				$session['user'] = [];
				$session['message'] = translate_inline("`4Error, you must validate your email address before you can log in.");
				echo appoencode($session['message']);
				exit();
            }
            else
            {
				$session['loggedin'] = true;
				$session['laston'] = date('Y-m-d H:i:s');
				$session['sentnotice']=0;
				$session['user']['dragonpoints']=unserialize($session['user']['dragonpoints']);
				$session['user']['prefs']=unserialize($session['user']['prefs']);
				$session['bufflist']=unserialize($session['user']['bufflist']);
				if (!is_array($session['bufflist']))
					$session['bufflist'] = [];
				if (!is_array($session['user']['dragonpoints'])) $session['user']['dragonpoints'] = [];
				invalidatedatacache("charlisthomepage");
				invalidatedatacache("list.php-warsonline");
				$session['user']['laston'] = date("Y-m-d H:i:s");

				// Handle the change in number of users online
				translator_check_collect_texts();

				// Let's throw a login module hook in here so that modules
				// like the stafflist which need to invalidate the cache
				// when someone logs in or off can do so.
				modulehook("player-login");

                if ($session['user']['loggedin'])
                {
                    $session['allowednavs']=unserialize($session['user']['allowednavs']);
                    $session['user']['restorepage'] = $session['user']['restorepage'] != '' ?: 'news.php';
                    $link = sprintf('<a href="%s">%s</a>' , $session['user']['restorepage'], $session['user']['restorepage']);

					$str = sprintf_translate('Sending you to %s, have a safe journey', $link);
					header("Location: {$session['user']['restorepage']}");
					saveuser();
					echo $str;
					exit();
				}

				DB::query("UPDATE " . DB::prefix("accounts") . " SET loggedin=".true.", laston='".date("Y-m-d H:i:s")."' WHERE acctid = ".$session['user']['acctid']);

				$session['user']['loggedin'] = true;
				if ($session['user']['location'] == $iname) $session['user']['location'] = $vname;

                if ($session['user']['restorepage'] > '')
                {
					redirect($session['user']['restorepage']);
                }
                else
                {
                    if ($session['user']['location'] == $iname)
                    {
						redirect('inn.php?op=strolldown');
                    }
                    else
                    {
						redirect('news.php');
					}
				}
			}
		}
		else
		{
			$session['message'] = translate_inline("`4Error, your login was incorrect`0");
			//now we'll log the failed attempt and begin to issue bans if
			//there are too many, plus notify the admins.
			checkban();
			$sql = "SELECT acctid FROM " . DB::prefix("accounts") . " WHERE login='$name'";
			$result = DB::query($sql);
			if (DB::num_rows($result)>0)
			{
				// just in case there manage to be multiple accounts on
				// this name.
				while ($row=DB::fetch_assoc($result)){
					$post = httpallpost();
					$sql = "INSERT INTO " . DB::prefix("faillog") . " VALUES (0,'".date("Y-m-d H:i:s")."','".addslashes(serialize($post))."','{$_SERVER['REMOTE_ADDR']}','{$row['acctid']}','{$_COOKIE['lgi']}')";
					DB::query($sql);
					$sql = "SELECT " . DB::prefix("faillog") . ".*," . DB::prefix("accounts") . ".superuser,name,login FROM " . DB::prefix("faillog") . " INNER JOIN " . DB::prefix("accounts") . " ON " . DB::prefix("accounts") . ".acctid=" . DB::prefix("faillog") . ".acctid WHERE ip='{$_SERVER['REMOTE_ADDR']}' AND date>'".date("Y-m-d H:i:s",strtotime("-1 day"))."'";
					$result2 = DB::query($sql);
					$c=0;
					$alert="";
					$su=false;
					while ($row2=DB::fetch_assoc($result2))
					{
						if ($row2['superuser']>0) {$c+=1; $su=true;}
						$c+=1;
						$alert.="`3{$row2['date']}`7: Failed attempt from `&{$row2['ip']}`7 [`3{$row2['id']}`7] to log on to `^{$row2['login']}`7 ({$row2['name']}`7)`n";
					}
					if ($c>=10)
					{
						// 5 failed attempts for superuser, 10 for regular user
						$banmessage=translate_inline("Automatic System Ban: Too many failed login attempts.");
						$sql = "INSERT INTO " . DB::prefix("bans") . " VALUES ('{$_SERVER['REMOTE_ADDR']}','','".date("Y-m-d H:i:s",strtotime("+15 minutes"))."','$banmessage','System','0000-00-00 00:00:00')";
						DB::query($sql);
						if ($su){
							// send a system message to admins regarding
							// this failed attempt if it includes superusers.
							$sql = "SELECT acctid FROM " . DB::prefix("accounts") ." WHERE (superuser&".SU_EDIT_USERS.")";
							$result2 = DB::query($sql);
							$subj = translate_mail(array("`#%s failed to log in too many times!",$_SERVER['REMOTE_ADDR']),0);
							while ($row2 = DB::fetch_assoc($result2)) {
								//delete old messages that
								$sql = "DELETE FROM " . DB::prefix("mail") . " WHERE msgto={$row2['acctid']} AND msgfrom=0 AND subject = '".serialize($subj)."' AND seen=0";
								DB::query($sql);
								if (DB::affected_rows()>0) $noemail = true; else $noemail = false;
								$msg = translate_mail(array("This message is generated as a result of one or more of the accounts having been a superuser account.  Log Follows:`n`n%s",$alert),0);
								systemmail($row2['acctid'],$subj,$msg,0,$noemail);
							}//end for
						}//end if($su)
					}//end if($c>=10)
				}//end while
			}//end if (DB::num_rows)
			redirect("index.php");
		}
	}
}
else if ($op=="logout")
{
    if ($session['user']['loggedin'])
    {
		$location = $session['user']['location'];
        if ($location == $iname)
        {
			$session['user']['restorepage'] = 'inn.php?op=strolldown';
        }
        elseif ($session['user']['superuser'] & (0xFFFFFFFF &~ SU_DOESNT_GIVE_GROTTO))
        {
			$session['user']['restorepage'] = 'superuser.php';
        }
        else {
			$session['user']['restorepage'] = 'news.php';
		}
	    $sql = "UPDATE " . DB::prefix("accounts") . " SET loggedin=0, restorepage='{$session['user']['restorepage']}' WHERE acctid = ".$session['user']['acctid'];
		DB::query($sql);
		invalidatedatacache('charlisthomepage');
		invalidatedatacache('list.php-warsonline');

		// Handle the change in number of users online
		translator_check_collect_texts();

		// Let's throw a logout module hook in here so that modules
		// like the stafflist which need to invalidate the cache
		// when someone logs in or off can do so.
		modulehook('player-logout');
		saveuser();
	}
	$session = [];
	redirect('index.php');
}
// If you enter an empty username, don't just say oops.. do something useful.
$session = [];
$session['message'] = translate_inline('`4Error, your login was incorrect`0');

redirect('index.php');
