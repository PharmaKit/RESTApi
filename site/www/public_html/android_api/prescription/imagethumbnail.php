<?php
    
//This class creates thumbnails of given source image
class ThumbnailHelper {
     
    public function thumbnailImage($imagePath) {
        $imagick = new Imagick(realpath($imagePath));
        $imagick->setbackgroundcolor('rgb(64, 64, 64)');
        $imagick->thumbnailImage(100, 100, true, true);
        header("Content-Type: image/jpg");
        echo $imagick->getImageBlob();
    }
}

?>