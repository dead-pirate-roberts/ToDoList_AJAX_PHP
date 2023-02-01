<?php
$servername = "localhost";
$username = "root";
$password = getenv('MARIA_PW');
$db = "B_DB_02";


$con = new mysqli($servername,$username,$password,$db);
?>