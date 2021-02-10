<?php

function login($username, $password) {
$dbconn = pg_connect("host=localhost port=5432 dbname=tournesol_staging user=tournesol password=Tetu8raiwieGh3I");
$auth_table = 'auth_user';

$result = pg_query($dbconn, "select * from $auth_table where username='$username'");

if(!$result) {
return false;
}

$n_rows = pg_num_rows($result);

if($n_rows != 1) {
return false;
}

echo "$n_rows rows\n";

$rs = pg_fetch_assoc($result);

var_dump($rs);

$password_hashed_db = $rs['password'];

var_dump($password_hashed_db);

list($algo_db, $iterations_db, $salt_db, $hash_db) = explode('$', $password_hashed_db);
list($algo_db_1, $algo_db_2) = explode('_', $algo_db);

if($algo_db_1 != 'pbkdf2') {
	echo "Unknown algorithm $algo_db_1";
	return false;
}

var_dump($algo_db, $iterations_db, $salt_db);

$supplied_hash_password = base64_encode(hash_pbkdf2($algo_db_2, $password, $salt_db, $iterations_db, 32, true));

echo "$supplied_hash_password\n";


return $supplied_hash_password == $hash_db;


}

$result = login('harry_potter', 'Iefaegh0Yohdah6k');
var_dump($result);
echo "res=$result\n";

?>
