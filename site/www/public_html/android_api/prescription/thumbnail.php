<?php

//echo phpinfo();
$imageName = $_GET['image'];
require_once 'imagethumbnail.php';
        // connecting to database
    $thumbnailHelper = new ThumbnailHelper();
    $thumbnailHelper->thumbnailImage("uploads/$imageName.jpg")

?>