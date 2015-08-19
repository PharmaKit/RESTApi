<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DB_Functions_Tests
 *
 * @author mustaqim
 */
class DB_Functions_Tests extends PHPunit_Framework_Testcase {
    //put your code here
        
    public function testStoreImage() {
        
        require_once 'DB_Connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->mysqli = $this->db->connect();
        
        include('DB_Functions.php');        
        $db = new DB_Functions();
        
        $result = $db->saveImageUploadDetails("jpg", 2, "test recepient", "test address", "test offer");
        
        $response = mysqli_query($this->mysqli, "delete from image_uploads where resource_id = $result");
        
        $this->assertEquals($response, TRUE);
    }
}
