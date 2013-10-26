<?php

//Code 1 = Success

//Errors
//0 - No post; 5 - Post invalid, 2 - Email/ID Invalid, 3 - MySQL Down, 4 - Database Down, 6 - Insert Failed
if(!empty($_POST)) {
  if(empty($_POST['first_name'])||
     empty($_POST['last_name'])||
     empty($_POST['initials'])||
     empty($_POST['student_email'])||
     empty($_POST['student_id'])) {
    die("1");   
  } else {
    if(!preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $_POST['student_email'])) {
      die("2");
    } else if(!is_numeric($_POST['student_id'])) {
      die("2");
    } else {
      include_once("includes/pegDB.class.php");
      $db = new pegDB("server", "localhost", "pegpro", "P3gpr023b", 3, 4, true);
      
      $person = array("first_name"=>$_POST['first_name'],
                          "last_name"=>$_POST['last_name'],
                          "initials"=>$_POST['initials'],
                          "email"=>$_POST['student_email'],
                          "student_id"=>$_POST['student_id'],
                          "created"=>time(),
                          "lastUpdate"=>time());
       
      include_once("includes/pegLDAP.class.php");
      include_once("includes/uomLDAP.class.php");
      include_once("includes/uomPersonQuery.class.php");
      $q = new uomPersonQuery("ou=People", false, 1, 2);
      $results = $q->searchPeople($_POST['student_id']);
      if(count($results)>=1) {
        $person['student_faculty'] = $results[0]['umanprimaryou'][0];
        $person['student_level'] = $results[0]['title'][0];
        $person['student_magid'] = $results[0]['umanmagstripe'][0];
        $person['student_year'] = intval($results[0]['umanstudentyearofstudy'][0]);
        $person['student_email'] = $results[0]['umanstudentyearofstudy'][0];
      }
      
      /* I <3 my db class */
      $db->insert("gaming_signups", $person);

      if(is_numeric($db->affectedRows())&&$db->affectedRows()>0) {
        die("1"); //All Worked
      } else {
        die("6"); //It Failed For Some Reason
      }
    }
  }
} else {
  die("0");
}

?>