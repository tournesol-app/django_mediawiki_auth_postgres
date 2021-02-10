<?php

require 'postgres_django_auth.php';

// command-line interface
if ('cli' === PHP_SAPI) {
    $options_default = [
        "username" => "",
        "password" => "",
        "db_password" => "",
        "host" => "localhost",
        "port" => 5432,
        "dbname" => "tournesol",
        "db_username" => "tournesol",
        "auth_table" => "auth_user",
    ];
    $options = getopt('', ["username:", "password:", "db_password:",
                           "host::", "port::", "dbname::",
                           "db_username::", "auth_table::"]);
    // var_dump($options);
    $options = $options + $options_default;
    // var_dump($options);

    if(!$options['username'] || !$options['password'] || !$options['db_password']) {
        $fn = $_SERVER['SCRIPT_FILENAME'];
        echo "Usage: php $fn --username=LOGIN_USERNAME --password=LOGIN_PASSWORD ";
        echo "--db_password=DB_PASSWORD\n";
        echo "  [--host=DB_HOST] [--port=DB_PORT] [--dbname=DB_NAME] ";
        echo "  [--db_username=DB_USERNAME] [--auth_table=AUTH_TABLE]";
        echo "\n";
        exit(1);
    }

    // logging in
    try {
	$result_arr = call_user_func_array("login_django_postgres", $options);
	$result = $result_arr['authorized'];
	$id = $result['id'];
        $error = "Wrong password";
    } catch (Exception $e) {
        $result = false;
        $error = $e->getMessage();
    }


    if($result) {
        echo "Login successful $id\n";
        exit(0);
    }
    else {
        echo "Login failed: $error\n";
        exit(1);
    }
}

?>
