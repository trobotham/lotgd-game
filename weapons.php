<?php

// translator ready
// addnews ready
// mail ready
require_once 'common.php';
require_once 'lib/villagenav.php';

tlschema('weapon');

checkday();
$tradeinvalue = round(($session['user']['weaponvalue'] * .75), 0);
$basetext = [
    'title' => "MightyE's Weapons",
    'desc' => [
        '`!MightyE `7stands behind a counter and appears to pay little attention to you as you enter, but you know from experience that he has his eye on every move you make.',
        ['He may be a humble weapons merchant, but he still carries himself with the grace of a man who has used his weapons to kill mightier %s than you.`n`n', translate_inline($session['user']['sex'] ? 'women' : 'men')],
        "The massive hilt of a claymore protrudes above his shoulder; its gleam in the torch light not much brighter than the gleam off of `!MightyE's`7 bald forehead, kept shaved mostly as a strategic advantage, but in no small part because nature insisted that some level of baldness was necessary.`n`n",
        '`!MightyE`7 finally nods to you, stroking his goatee and looking like he wished he could have an opportunity to use one of these weapons.',
    ],
    'tradein' => [
        '`7You stroll up the counter and try your best to look like you know what most of these contraptions do.',
        ["`!MightyE`7 looks at you and says, \"`#I'll give you `^%s`# trade-in value for your `5%s`#.", $tradeinvalue, $session['user']['weapon']],
        "Just click on the weapon you wish to buy, what ever 'click' means`7,\" and looks utterly confused.",
        'He stands there a few seconds, snapping his fingers and wondering if that is what is meant by "click," before returning to his work: standing there and looking good.`n`n',
    ],
    'nosuchweapon' => "`!MightyE`7 looks at you, confused for a second, then realizes that you've apparently taken one too many bonks on the head, and nods and smiles.",
    'tryagain' => 'Try again?',
    'notenoughgold' => "Waiting until `!MightyE`7 looks away, you reach carefully for the `5%s`7, which you silently remove from the rack upon which it sits. Secure in your theft, you turn around and head for the door, swiftly, quietly, like a ninja, only to discover that upon reaching the door, the ominous `!MightyE`7 stands, blocking your exit. You execute a flying kick. Mid flight, you hear the \"SHING\" of a sword leaving its sheath.... your foot is gone. You land on your stump, and `!MightyE`7 stands in the doorway, claymore once again in its back holster, with no sign that it had been used, his arms folded menacingly across his burly chest.  \"`#Perhaps you'd like to pay for that?`7\" is all he has to say as you collapse at his feet, lifeblood staining the planks under your remaining foot.`n`nYou wake up some time later, having been tossed unconscious into the street.",
    'payweapon' => "`!MightyE`7 takes your `5%s`7 and promptly puts a price on it, setting it out for display with the rest of his weapons.`n`nIn return, he hands you a shiny new `5%s`7 which you swoosh around the room, nearly taking off `!MightyE`7's head, which he deftly ducks; you're not the first person to exuberantly try out a new weapon.",
];

$schemas = [
    'title' => 'weapon',
    'desc' => 'weapon',
    'tradein' => 'weapon',
    'nosuchweapon' => 'weapon',
    'tryagain' => 'weapon',
    'notenoughgold' => 'weapon',
    'payweapon' => 'weapon',
];

$basetext['schemas'] = $schemas;
$texts = modulehook('weaponstext', $basetext);
$schemas = $texts['schemas'];

tlschema($schemas['title']);
page_header($texts['title']);
output('`c`b`&'.$texts['title'].'`0`b`c');
tlschema();

$op = httpget('op');

if ('' == $op)
{
    tlschema($schemas['desc']);

    if (is_array($texts['desc']))
    {
        foreach ($texts['desc'] as $description)
        {
            output_notl(sprintf_translate($description));
        }
    }
    else
    {
        output($texts['desc']);
    }
    tlschema();

    $sql = 'SELECT max(level) AS level FROM '.DB::prefix('weapons').' WHERE level<='.(int) $session['user']['dragonkills'];
    $result = DB::query($sql);
    $row = DB::fetch_assoc($result);

    $sql = 'SELECT * FROM '.DB::prefix('weapons').' WHERE level = '.(int) $row['level'].' ORDER BY damage ASC';
    $result = DB::query($sql);

    tlschema($schemas['tradein']);

    if (is_array($texts['tradein']))
    {
        foreach ($texts['tradein'] as $description)
        {
            output_notl(sprintf_translate($description));
        }
    }
    else
    {
        output($texts['tradein']);
    }
    tlschema();

    //-- Render template: list of weapons
    $data = [
        'content' => $result,
        'user' => $session['user'],
        'tradeinvalue' => $tradeinvalue
    ];
    rawoutput($lotgd_tpl->renderThemeTemplate('pages/weapon/list.twig', $data));

    villagenav();
}
elseif ('buy' == $op)
{
    $id = httpget('id');
    $sql = 'SELECT * FROM '.DB::prefix('weapons')." WHERE weaponid='$id'";
    $result = DB::query($sql);

    if (0 == DB::num_rows($result))
    {
        tlschema($schemas['nosuchweapon']);
        output($texts['nosuchweapon']);
        tlschema();
        tlschema($schemas['tryagain']);
        addnav($texts['tryagain'], 'weapons.php');
        tlschema();
        villagenav();
    }
    else
    {
        $row = DB::fetch_assoc($result);
        $row = modulehook('modify-weapon', $row);

        if ($row['value'] > ($session['user']['gold'] + $tradeinvalue))
        {
            tlschema($schemas['notenoughgold']);
            output($texts['notenoughgold'], $row['weaponname']);
            tlschema();
            villagenav();
        }
        else
        {
            tlschema($schemas['payweapon']);
            output($texts['payweapon'], $session['user']['weapon'], $row['weaponname']);
            tlschema();
            debuglog('spent '.($row['value'] - $tradeinvalue).' gold on the '.$row['weaponname'].' weapon');
            $session['user']['gold'] -= $row['value'];
            $session['user']['weapon'] = $row['weaponname'];
            $session['user']['gold'] += $tradeinvalue;
            $session['user']['attack'] -= $session['user']['weapondmg'];
            $session['user']['weapondmg'] = $row['damage'];
            $session['user']['attack'] += $session['user']['weapondmg'];
            $session['user']['weaponvalue'] = $row['value'];
            villagenav();
        }
    }
}

page_footer();
