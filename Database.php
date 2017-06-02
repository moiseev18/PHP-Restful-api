<?php
session_start();
define ("ADMIN_TITLE", "JobMe");
//if($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '192.168.1.212'){
    define ("DB_HOST", "localhost");
    define ("DB_USER", "root");
    define ("DB_PASS", "");
    define ("DB_NAME", "jobme");

//} else{
//define ("DB_HOST", "localhost");
//define ("DB_USER", "root");
//define ("DB_PASS", "");
//define ("DB_NAME", "mwahchat_db");
//}


class Database {

    var $_db = null;

    public function __construct() {
        $this -> _db = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);
		if (!$this -> _db)
			die('Could not connect: ' . mysql_error());

		mysqli_set_charset($this -> _db, "utf8");
	}

	public function __desctruct() {
		//mysql_close($this -> _db);
	}

	public function get_fields($table_name) {
		$result = $this -> result("SHOW COLUMNS FROM " . $table_name);

		$fieldnames = array();
		for ($i = 0; $i < count($result); $i++) :
			$fieldnames[] = $result[$i]["Fields"];
		endfor;

		return $fieldnames;
	}

	public function execute($sql) {
		if ($sql == "") :
			return false;
		else :
			return $this -> _db -> query($sql);
		endif;

	}

	public function single($sql) {
		$b = $this -> result($sql);

		return count($b) > 0 ? $b[0] : array();
	}

	public function result($sql) {
		$result = $this -> _db -> query($sql);

		if (!$result)
			echo 'Could not run query: ' . $this -> _db -> error;

		$b = array();
		while ($row = $result -> fetch_array(MYSQLI_ASSOC)) :
			$b[] = $row;
		endwhile;

		return $b;
	}
}
