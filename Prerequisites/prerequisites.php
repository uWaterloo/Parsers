<?php

function parse_reqs($reqs)
{
    $reqs = preg_replace('/prereq:/i', '', $reqs);
    $reqs = preg_replace('/([\/,;])/', '$1 ', $reqs);
    if (!preg_match('/^.*[0-9]{2,3}(?!%)[A-Z\)]*(.*\))*/', $reqs, $reqs)) return '';
    $reqs = $reqs[0];

    $reqs = preg_replace('/(,\s*)(one|two|three|1|2|3)( of)/i', ' and $2$3', $reqs);

    while($reqs != ($pstmp=preg_replace('/^(^|.*[^A-Z])([A-Z]{2,})(([^A-Z][A-Z]?)+)([\/\s]+)([0-9]{2,3}(?!%))/', '$1$2$3$5$2 $6', $reqs)))
        $reqs = $pstmp;
    while($reqs != ($pstmp=preg_replace('/([A-Z]{2,})([,\/\s]+[^0-9]+)([0-9]{2,3})/', '$1 $3$2$3', $reqs)))
        $reqs = $pstmp;

    return rec_parse_reqs($reqs);
}

function rec_parse_reqs($reqs)
{
    $reqs = trim($reqs);

    if (preg_match('/^\(([^\(\)]+|(\(.+\).*)*)\)$/', $reqs, $tokens))
        return rec_parse_reqs($tokens[1]);

    $engcards = array("one" => 1, "two" => 2, "three" => 3, "1" => 1, "2" => 2, "3" => 3);
    $seps = array(";" => array(), "and" => array(), "\\&" => array(), "" => array(), "," => array(), "or" => array(1), "\\/" => array(1));
    foreach ($seps as $regpart => $sellen)
    {
        if ($regpart === ""){
            if (preg_match('/^(one|two|three|1|2|3) of(.+)$/i', $reqs, $tokens))
                return count($pstmp = rec_parse_reqs(preg_replace('/,(?=[^\)]*(?:\(|$))/', ' or', $tokens[2]))) == 1 ?
                    $pstmp : array_merge(array($engcards[strtolower($tokens[1])]), array_slice($pstmp, 1));
        }
        else if (($tokens = preg_split('/' . $regpart . '(?=[^\\)]*(?:\\(|$))/i', $reqs, NULL, PREG_SPLIT_NO_EMPTY)) && $tokens[0]!=$reqs){
            $res = array();
            foreach ($tokens as $token)
            {
                $token_parsed = rec_parse_reqs($token);
                $e = empty($sellen);
                if (!$token_parsed || (count($token_parsed) == 1 && is_numeric($token_parsed[0]))) continue;
                if (($e && is_numeric($token_parsed[0])) || (!$e && count($token_parsed) > 1)) $token_parsed = array($token_parsed);
                $res = array_merge($res,  $token_parsed);
            }
            return array_merge($sellen, $res);
        }
    }

    $reqs = preg_replace('/\W/', '', $reqs);
    if (preg_match('/[A-Z]{2,}[0-9]{2,3}[A-Z]*/', $reqs, $tokens))
        return array($tokens[0]);
    else
        return;
}

if ($_GET['q'])
{
    echo '<pre>';
    print_r(parse_reqs($_GET['q']));
    echo '</pre>';
}
else
{
    assert('parse_reqs("Prereq: PHYS 334 or AMATH 373; PHYS 364 or AMATH 351; PHYS 365 or (AMATH 332 and 353)") == array(array(1, "PHYS334", "AMATH373"), array(1, "PHYS364", "AMATH351"), array(1, "PHYS365", array("AMATH332", "AMATH353")))');
    assert('parse_reqs("Prereq: (One of CO 250/350, 352, 255/355, CM 340) and MATH 128 with a grade of at least 70% or MATH 138 or 148; Not open to General Mathematics students") == array(array(1, array(1, "CO250", "CO350"), "CO352", array(1, "CO255", "CO355"), "CM340"), array(1, "MATH128", "MATH138", "MATH148"))');
    assert('parse_reqs("Prereq: AMATH/PMATH 331 or PMATH 351; Not open to General Mathematics students") == array(1, array(1, "AMATH331", "PMATH331"), "PMATH351")');
    assert('parse_reqs("Prereq: (ECE 261/361; Level at least 4A Comp or Elec Eng)or (MTE 120, 220, 320; Level at least 3B Mechtr Eng) or ((ECE 240 or 241),ME 123; Level at least 4A Mech Eng/Mechtr Opt) or (SYDE 292,292L; Level at least 4A Sys Des Eng/Mechtr Opt)") == array(1, array(1, "ECE261", "ECE361"), array("MTE120", "MTE220", "MTE320"), array(array(1, "ECE240", "ECE241"), "ME123"), array("SYDE292", "SYDE292L"))');
    assert('parse_reqs("Prereq:1 of PHYS 112,122,125, ECE106,(NE 122, 1 of PHYS 112,122,125,ECE 126);1 of MATH128,138,148,(SYDE 111,112);1 of PHYS 233,234,(1 of PHYS224,241,242, 252),256,280,380,ECE 209,370,375,NE 232,241,SYDE 283,AMATH 231,373,CM 473, CS 473, CHEM 209, 356") == array(array(1, "PHYS112", "PHYS122", "PHYS125", "ECE106", array("NE122", array(1, "PHYS112", "PHYS122", "PHYS125", "ECE126"))), array(1, "MATH128", "MATH138", "MATH148", array("SYDE111", "SYDE112")), array(1, "PHYS233", "PHYS234", array(1, "PHYS224", "PHYS241", "PHYS242","PHYS252"), "PHYS256", "PHYS280", "PHYS380", "ECE209", "ECE370", "ECE375", "NE232", "NE241", "SYDE283", "AMATH231", "AMATH373", "CM473", "CS473", "CHEM209", "CHEM356"))');

    echo 'If no errors printed, tests passed!';
}

?>