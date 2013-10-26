<?php

class uomLDAP {
	private $connection;
	private $con;
	private $base;
	private $debugOn;
	private $lastQuery;

	public function __construct($base=false, $debug=false, $timelimit=5, $globallimit=5) {
		$this->connection = new pegLDAP("edir.manchester.ac.uk", $timelimit, $globallimit);
		$this->con =& $this->connection;
		$this->base = ($base ? $base.", ":"")."o=University of Manchester, c=GB";
		$this->debugOn = $debug;
	}


	private function debug($message) {
		if($this->debugOn) echo $message.'<br />';
	}


	public function searchBase($search, $attributes=array(), $attronly=0, $limit=false, $timelimit=false) {
		$this->lastQuery = $search;
		$this->con->search($this->base, $search, $attributes, $attronly, $limit, $timelimit);
		return $this->con->getArray();
	}


	public function searchBaseCount($search) {
		$this->debug("Search for ".$search);
		$this->lastQuery = $search;
		return $this->con->searchResultsFound($this->base, $search);
	}


	public function searchBaseExists($search) {
		$this->lastQuery = $search;
		return $this->con->searchRowExists($this->base, $search);
	}


	public function getLastQuery() {
		return $this->lastQuery;
	}


}

?>