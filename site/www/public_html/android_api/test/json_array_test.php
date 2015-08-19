<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$mysqli_host = "localhost";
$mysqli_database = "1541215_android";
$mysqli_user = "root";
$mysqli_password = "tiger123x";


$mysqli = new mysqli($mysqli_host, $mysqli_user, $mysqli_password, $mysqli_database);

$result = mysqli_query($mysqli, "SELECT * from users;");
$rows = array();
while ($r = mysqli_fetch_assoc($result)) {
    $rows['patient_history'][] = $r;
}

print json_encode($rows);
