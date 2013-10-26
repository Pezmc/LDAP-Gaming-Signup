<?php

/*ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ERROR);*/

//Stuff UoM ldap appears to return:
/*
    [cn][0] => Pez Cuckow
    [sn][0] => Cuckow
    [displayname][0] => Pez Cuckow
    [givenname][0] => Pez
    [umanprimaryou][0] => School of Computer Science
    [umanorgunitid][0] => 3060
    [umanprimarydiv][0] => School of Computer Science
    [ou] =>
            [0] => School of Computer Science
            [1] => Faculty of Engineering & Physical Sciences
    [telephonenumber][0] => School of Computer Science
    [umantelephonenumberallow][0] => 1
    [umanroomnumberallow][0] => 1
    [umanindirectory][0] => 1
    [title] => Array
            [0] => Undergraduate
    [mail] => Array
            [0] => pez.cuckow@student.manchester.ac.uk
    [employeetype]
            [0] => Undergraduate
    [edupersonaffiliation]
            [0] => student
            [1] => member
    [edupersonscopedaffiliation]
            [0] => student@manchester.ac.uk
            [1] => member@manchester.ac.uk
    [edupersonentitlement] - A horrible mess, ignored
    [umanmst][0] => CS email - UK-AC-MAN-CS-FS6 - Fuck knows
    [umanbarcode][0] => 75650250 - Barcode Number - Student Number
    [umanmagstripe][0] => 900075650250 - Barcode Strip
    [umanlegacyemailallow][0] => 0 - Does legacy email work I guess
    [edupersonprincipalname][0] => 50297445@manchester.ac.uk - No idea?!?
    [labeleduri][0] => http://personalpages.manchester.ac.uk/student/pez.cuckow - Web Page NOT for CS
    [umaniconstatus][0] => 0 - No idea
    [umanpersonid][0] => 7565025 - Your ID number, awesome!
    [umanstudentprogramofstudy][0] = 0067600553 - Course ID of some sort?!?
    [umanstudentyearofstudy][0] = 01 - Year of Study
    [umanroleid][0] = 264765 - Fuck Knows
    [dn] - Long string of stuff
*/

include_once("includes/pegLDAP.class.php");
include_once("includes/uomLDAP.class.php");
include_once("includes/uomPersonQuery.class.php");

function print_r_html($arr) {
	?><pre><?php
	print_r($arr);
	?></pre><?php
}

$data = array();
$data['status'] = "OK"; //REQUEST_DENIED, OVER_LIMIT, INVALID_REQUEST, HIT_LIMIT, OK
$data['count'] = 0;

$query = (isset($_GET['query']) ? $_GET['query'] : "");
$resultLimit = (isset($_GET['limit'])&&is_numeric($_GET['limit']) ? $_GET['limit'] : 5);

if(empty($query)) {
	$data['status'] = "INVALID_REQUEST";
} else {
	$q = new uomPersonQuery("ou=People", false, 3, $resultLimit);
	$data['results'] = $q->searchPeople($query);
	$data['count'] = count($data['results']);
	if($data['count']==0) $data['status'] = "ZERO_RESULTS";
	if($data['count']>=$resultLimit) $data['status'] = "HIT_LIMIT";
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($data);
//print_r_html($data);

?>