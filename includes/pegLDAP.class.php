<?php

/* Generic LDAP Class */
class pegLDAP {
	private $lastSearch;
	private $connection;
	private $binding;
	private $address;
	private $timeLimit;
	private $globalLimit;

	/*
   * Constructor
   */
	public function __construct($address=false, $timelimit = 0, $globallimit = 0){
		//Connect
		if($address) {
			$this->address = $address;
			$this->connection = ldap_connect($address);
		}
		if(!$this->connection) die("Could not connect to ".($address ? $address : "server"));
		else {
			$this->binding = ldap_bind($this->connection);
		}
		if($this->binding) {
			$this->timeLimit = $timelimit;
			$this->globalLimit = $globallimit;
		}
	}


	public function __toString() {
		return "Status:".(!$connection ? "Connected" : "Error").",Address:".$address;
	}


	public function __destruct() {
		if($this->connection) ldap_close($this->connection);
	}


	public function search($base, $search, $attributes=array(), $attronly=0, $limit=false, $timelimit=false) {
		if(!is_numeric($limit)) $limit = $this->globalLimit;
		if(!is_numeric($timelimit)) $timelimit = $this->timeLimit;
		$this->lastSearch = ldap_search($this->connection, $base, $search, $attributes, $attronly, $limit, $timelimit);
		return $this->lastSearch;
	}


	public function count() {
		if($this->lastSearch&&$this->connection)
			return ldap_count_entries($this->connection, $this->lastSearch);
		return false;
	}


	public function getArray($search=false) {
		if($this->lastSearch&&$this->connection)
			if($search)
				return ldap_get_entries($this->connection, $search);
			else
				return ldap_get_entries($this->connection, $this->lastSearch);
			return false;
	}


	public function searchResultsFound($base, $search) {
		$this->search($base, $search, array("dn"), 1);
		return $this->count();
	}


	public function searchRowExists($base, $search) {
		return ($this->searchResultsFound($bae, $search) >= 1);
	}


}

?>