<?php 

//for mysqli docs see

error_reporting(E_ALL);

$DB = new mysqli("sandbox.stkywll.com", "root", "bitnami", "submarino");
if ($DB->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

?>