<?php

// translator ready
// addnews ready
// mail ready

// Okay, someone wants to use this outside of normal game flow.. no real harm
define('OVERRIDE_FORCED_NAV', true);

// Translate Untranslated Strings
// Originally Written by Christian Rutsch
// Slightly modified by JT Traub
require_once 'common.php';
require_once 'lib/superusernav.php';

check_su_access(SU_IS_TRANSLATOR);

tlschema('untranslated');

$op = httpget('op');
page_header('Untranslated Texts');

//chcek if he/she is allowed to edit that language
if (! in_array($session['user']['prefs']['language'], explode(',', $session['user']['translatorlanguages'])))
{
    output('Sorry, please change your language to one you are allowed to translate.`n`n');
    superusernav();
    page_footer();
}

if ('list' == $op)
{
    $mode = httpget('mode');
    $namespace = httpget('ns');

    if ('save' == $mode)
    {
        $intext = httppost('intext');
        $outtext = httppost('outtext');

        if ('' != $outtext)
        {
            $login = $session['user']['login'];
            $language = $session['user']['prefs']['language'];
            $sql = 'INSERT INTO '.DB::prefix('translations').' (language,uri,intext,outtext,author,version) VALUES'." ('$language','$namespace','$intext','$outtext','$login','".\Lotgd\Core\Application::VERSION."')";
            DB::query($sql);
            $sql = 'DELETE FROM '.DB::prefix('untranslated')." WHERE intext = '$intext' AND language = '$language' AND namespace = '$namespace'";
            DB::query($sql);
        }
    }

    if ('edit' == $mode)
    {
        rawoutput("<form action='untranslated.php?op=list&mode=save&ns=".rawurlencode($namespace)."' method='post'>");
        addnav('', 'untranslated.php?op=list&mode=save&ns='.rawurlencode($namespace));
    }
    else
    {
        rawoutput("<form action='untranslated.php?op=list' method='get'>");
        addnav('', 'untranslated.php?op=list');
    }

    $sql = 'SELECT namespace,count(*) AS c FROM '.DB::prefix('untranslated')." WHERE language='".$session['user']['prefs']['language']."' GROUP BY namespace ORDER BY namespace ASC";
    $result = DB::query($sql);
    rawoutput("<input type='hidden' name='op' value='list'>");
    output('Known Namespaces:');
    rawoutput("<select name='ns'>");

    while ($row = DB::fetch_assoc($result))
    {
        rawoutput('<option value="'.htmlentities($row['namespace'], ENT_COMPAT, getsetting('charset', 'UTF-8')).'"'.((htmlentities($row['namespace'], ENT_COMPAT, getsetting('charset', 'UTF-8')) == $namespace) ? 'selected' : '').'>'.htmlentities($row['namespace'], ENT_COMPAT, getsetting('charset', 'UTF-8'))." ({$row['c']})</option>");
    }
    rawoutput('</select>');
    rawoutput("<input type='submit' class='button' value='".translate_inline('Show')."'>");
    rawoutput('<br>');

    if ('edit' == $mode)
    {
        rawoutput(translate_inline('Text:').'<br>');
        rawoutput("<textarea name='intext' cols='60' rows='5' readonly>".htmlentities(stripslashes(httpget('intext')), ENT_COMPAT, getsetting('charset', 'UTF-8')).'</textarea><br/>');
        rawoutput(translate_inline('Translation:').'<br>');
        rawoutput("<textarea name='outtext' cols='60' rows='5'></textarea><br/>");
        rawoutput("<input type='submit' value='".translate_inline('Save')."' class='button'>");
    }
    else
    {
        rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
        rawoutput("<tr class='trhead'><td>".translate_inline('Ops').'</td><td>'.translate_inline('Text').'</td></tr>');
        $sql = 'SELECT * FROM '.DB::prefix('untranslated')." WHERE language='".$session['user']['prefs']['language']."' AND namespace='".$namespace."'";
        $result = DB::query($sql);

        if (DB::num_rows($result) > 0)
        {
            $i = 0;

            while ($row = DB::fetch_assoc($result))
            {
                $i++;
                rawoutput("<tr class='".($i % 2 ? 'trlight' : 'trdark')."'><td>");
                rawoutput("<a href='untranslated.php?op=list&mode=edit&ns=".rawurlencode($row['namespace']).'&intext='.rawurlencode($row['intext'])."'>".translate_inline('Edit').'</a>');
                addnav('', 'untranslated.php?op=list&mode=edit&ns='.rawurlencode($row['namespace']).'&intext='.rawurlencode($row['intext']));
                rawoutput('</td><td>');
                rawoutput(htmlentities($row['intext'], ENT_COMPAT, getsetting('charset', 'UTF-8')));
                rawoutput('</td></tr>');
            }
        }
        else
        {
            rawoutput("<tr><td colspan='2'>".translate_inline('No rows found').'</td></tr>');
        }
        rawoutput('</table>');
    }

    rawoutput('</form>');
}
else
{
    if ('step2' == $op)
    {
        $intext = httppost('intext');
        $outtext = httppost('outtext');
        $namespace = httppost('namespace');
        $language = httppost('language');

        if ('' != $outtext)
        {
            $login = $session['user']['login'];
            $sql = 'INSERT INTO '.DB::prefix('translations').' (language,uri,intext,outtext,author,version) VALUES'." ('$language','$namespace','$intext','$outtext','$login','".\Lotgd\Core\Application::VERSION."')";
            DB::query($sql);
            $sql = 'DELETE FROM '.DB::prefix('untranslated')." WHERE intext = '$intext' AND language = '$language' AND namespace = '$namespace'";
            DB::query($sql);
            invalidatedatacache('translations-'.$namespace.'-'.$language);
        }
    }

    $sql = 'SELECT count(intext) AS count FROM '.DB::prefix('untranslated');
    $count = DB::fetch_assoc(DB::query($sql));

    if ($count['count'] > 0)
    {
        $sql = 'SELECT * FROM '.DB::prefix('untranslated')." WHERE language = '".$session['user']['prefs']['language']."' ORDER BY rand(".e_rand().') LIMIT 1';
        $result = DB::query($sql);

        if (1 == DB::num_rows($result))
        {
            $row = DB::fetch_assoc($result);
            $row['intext'] = stripslashes($row['intext']);
            $submit = translate_inline('Save Translation');
            $skip = translate_inline('Skip Translation');
            rawoutput("<form action='untranslated.php?op=step2' method='post'>");
            output('`^`cThere are `&%s`^ untranslated texts in the database.´c`n`n', $count['count']);
            rawoutput("<table width='80%'>");
            rawoutput("<tr><td width='30%'>");
            output('Target Language: %s', $row['language']);
            rawoutput('</td><td></td></tr>');
            rawoutput("<tr><td width='30%'>");
            output('Namespace: %s', $row['namespace']);
            rawoutput('</td><td></td></tr>');
            rawoutput("<tr><td width='30%'><textarea cols='35' rows='4' name='intext'>".$row['intext'].'</textarea></td>');
            rawoutput("<td width='30%'><textarea cols='25' rows='4' name='outtext'></textarea></td></tr></table>");
            rawoutput("<input type='hidden' name='id' value='{$row['id']}'>");
            rawoutput("<input type='hidden' name='language' value='{$row['language']}'>");
            rawoutput("<input type='hidden' name='namespace' value='{$row['namespace']}'>");
            rawoutput("<input type='submit' value='$submit' class='button'>");
            rawoutput('</form>');
            rawoutput("<form action='untranslated.php' method='post'>");
            rawoutput("<input type='submit' value='$skip' class='button'>");
            rawoutput('</form>');
            addnav('', 'untranslated.php?op=step2');
            addnav('', 'untranslated.php');
        }
        else
        {
            output('There are `&%s`^ untranslated texts in the database, but none for your selected language.', $count['count']);
            output('Please change your language to translate these texts.');
        }
    }
    else
    {
        output('There are no untranslated texts in the database!');
        output('Congratulations!!!');
    } // end if
} // end list if
addnav('R?Restart Translator', 'untranslated.php');
addnav('N?Translate by Namespace', 'untranslated.php?op=list');
superusernav();
page_footer();