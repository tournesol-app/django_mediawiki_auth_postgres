<?php

function login_django_postgres($username, $password, $host='localhost', $port=5432,
                               $dbname='tournesol_staging', $db_username='tournesol',
                               $db_password='', $auth_table = 'auth_user') {
	/*
	Log in to a Django database stored in Postgres, assuming pbkdf2-sha256 encryption

	Args:
		$username: username of the user trying to log in
		$password: password of the user trying to log in in plain text
		$host: Postgres database host
		$port: Postgres database port
		$dbname: Postgres database name
		$db_username: Postgres user name
		$db_password: Postgres user password
		$auth_table: Postgres Django authentication table

	Returns:
	    true if the login was successful, false on authentication failure
	    throws exceptions in case if connection failed/database does not exist/user does not exist
	*/

	// connecto to the database
	$dbconn = pg_connect("host=$host port=$port dbname=$dbname user=$db_username password=$db_password");

	// no connection -> no login
	if(!$dbconn) {
    	throw new Exception("Database connection failed");
	}
	
	// query the user table
	$username_escaped = pg_escape_string($username);
	$result = pg_query($dbconn, "select * from $auth_table where username='$username_escaped'");

	// no data -> not logged in
	if(!$result) {
	    throw new Exception("Username not found");
	}

	// number of rows with the username
	$n_rows = pg_num_rows($result);
	if($n_rows != 1) {
		throw new Exception("User not found");
	}

	// obtaining the password for the single row
	$rs = pg_fetch_assoc($result);
	$password_hashed_db = $rs['password'];

	// destructuring password parameters
	list($algo_db, $iterations_db, $salt_db, $hash_db) = explode('$', $password_hashed_db);
	list($algo_db_1, $algo_db_2) = explode('_', $algo_db);

	// only pbkdf2 is implemented
	if($algo_db_1 != 'pbkdf2') {
		throw new Exception("Encryption mechanism not supported");
	}

	// hashing the given password
	$supplied_hash_password = base64_encode(hash_pbkdf2($algo_db_2, $password, $salt_db, $iterations_db, 32, true));

	// logged in === hashed($password) == $stored_hash
	return $supplied_hash_password == $hash_db;
}

?>
