<?php

class uomPersonQuery {
	private $ldap;
	private $debugOn;
	private $db;

	public function __construct($base="ou=People", $debug=false, $timelimit=5, $globallimit=5) {
		$this->ldap = new uomLDAP($base, $debug, $timelimit, $globallimit);
		$this->debugOn = $debug;
		$this->db = mysql_connect("localhost", "pegpro", "P3gpr023b");
    mysql_select_db("server", $this->db);
	}


	private function debug($message) {
		if($this->debugOn) echo $message.'<br />';
	}


	private function clean($string) {
		return addslashes(strip_tags(trim($string)));
	}


	private function isInt($number) {
		return (is_numeric($number)&&$number>0);
	}


	public function peopleCount($query) {
		$query = $this->clean($query);
		$this->debug("Query: ".$query);
		if(is_numeric($query)) {
			$this->debug("is number");
			$count = $this->ldap->searchBaseCount("(|(umanPersonID=".$query.")(umanbarcode=".$query.")(umanmagstripe=".$query."))");
			if(!$this->isInt($count)) $count = $this->ldap->searchBaseCount($this->ldap->getLastQuery());
		} else {
			$this->debug("is string");
			$count = $this->ldap->searchBaseCount("(|(cn=".$query.")(sn=".$query."))");
			if(!$this->isInt($count)) $count = $this->ldap->searchBaseCount("(|(cn=".$query."*)(sn=".$query."*))");
			if(!$this->isInt($count)) $count = $this->ldap->searchBaseCount("(|(cn=*".$query."*)(sn=*".$query."*))");
		}

		return $count;
	}


	public function searchPeople($query, $attributes=array("cn", "umanprimaryou", "title", "mail", "umanpersonid",
	                                                       "umanstudentyearofstudy", "ou", "umanmagstripe", "givenname", "sn")) {
    $cacheArray = null;
    if(is_numeric($query)) {
	    $safe=mysql_real_escape_string($query);
      $result = mysql_query("SELECT * FROM ldap_cache WHERE (studentid='".$safe."' OR barcodeid='".$safe."') AND timestamp > ".(time()-604800));
      
      if(mysql_num_rows($result)==1) {
        $data = mysql_fetch_array($result);
        $cacheData = unserialize(stripslashes($data['data']));
        if(is_array($cacheData)) {
          $cacheArray = array($cacheData);
        }
      }
	  }
	  
	  if(is_array($cacheArray)) {
	    return $cacheArray;
	  } elseif ($this->peopleCount($query)>=1) {
		  
	    $array = $this->ldap->searchBase($this->ldap->getLastQuery(), $attributes);
	    if(isset($array['umanstudentyearofstudy'][0])) $array['umanstudentyearofstudy'][0] = intval($array['umanstudentyearofstudy'][0]);
      $this->stripCount($array);
      
      //Now cache the data
      foreach($array as $person) {
        mysql_query("INSERT INTO ldap_cache (studentid,barcodeid,data,timestamp)
                                     VALUES ('{$person['umanpersonid'][0]}','{$person['umanmagstripe'][0]}',
                                             '".mysql_real_escape_string(serialize($person))."',".time().")
                                  ON DUPLICATE KEY UPDATE
                                     data='".mysql_real_escape_string(serialize($person))."', timestamp=".time()."", $this->db)
                    or die(mysql_error()."<hr />"."INSERT INTO ldap_cache (studentid,barcodeid,data,timestamp)
                                     VALUES ('{$person['umanpersonid'][0]}','{$person['umanmagstripe'][0]}',
                                             '".serialize($person)."',".time().")
                                  ON DUPLICATE KEY UPDATE
                                     data='".serialize($person)."', timestamp=".time()."");
                                     
      }
      
			return $array;
		} else {
			return array();
		}
	}


	private function stripCount(array &$arr, $depth = 0) {
		foreach ($arr as $key => $value) {
			if (is_array($arr[$key])) {
				$this->stripCount($arr[$key], $depth+1);
			} else {
				if (!is_numeric($key)&&($key=="count"|$key=="dn")) {
					unset($arr[$key]);
				} elseif(is_numeric($key)&&$depth==1) {
					unset($arr[$key]);
				}
			}
		}
	}


}

?>