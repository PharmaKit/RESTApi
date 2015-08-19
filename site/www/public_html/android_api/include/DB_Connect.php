<?php

class DB_Connect {

    // constructor
    function __construct() {
        
    }

    // destructor
    function __destruct() {
        // $this->close();
    }

    // Connecting to database
    public function connect() {
        require_once 'config.php';

        /*
        $mysqli_host = "mysql16.000webhost.com";
        $mysqli_database = "a8748905_android";
        $mysqli_user = "a8748905_root";
        $mysqli_password = "tiger123";
        */
        $mysqli_host = "localhost";
        $mysqli_database = "1541215_android";
        $mysqli_user = "root";
        $mysqli_password = "tiger123x";


        $mysqli = new mysqli($mysqli_host, $mysqli_user, $mysqli_password, $mysqli_database);
        // selecting database
        //mysqli_select_db($con,$mysqli_database);
        // return database handler
        return $mysqli;
    }

    // Closing database connection
    public function close() {
        mysqli_close();
    }

}

?>