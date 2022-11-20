<?php

if (isset($_GET['path'])) {
    $pathToImage = $_GET['path'];
    $thumbWidth = isset($_GET['width']) ? $_GET['width'] : 80;

    //if (is_file($pathToImage)) {
        $info = pathinfo($pathToImage);

        $extension = strtolower($info['extension']);
        if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {

            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $img = imagecreatefromjpeg("{$pathToImage}");
                    break;
                case 'png':
                    $img = imagecreatefrompng("{$pathToImage}");
                    break;
                case 'gif':
                    $img = imagecreatefromgif("{$pathToImage}");
                    break;
                default:
                    $img = imagecreatefromjpeg("{$pathToImage}");
            }

            // load image and get image size
            $width = imagesx($img);
            $height = imagesy($img);

            // calculate thumbnail size
            $new_width = $thumbWidth;
            $new_height = floor($height * ($thumbWidth / $width)) + 0;

            // create a new temporary image
            $tmp_img = imagecreatetruecolor($new_width, $new_height);

            if ($extension == "png" || $extension == "gif") {
                imagealphablending($tmp_img, false);
                imagesavealpha($tmp_img, true);
                imagealphablending($img, true);
            }

            // copy and resize old image into new image
            imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            $fichero = strtolower($info['basename']);
            $fichero_t = explode($fichero, $pathToImage);
            $directory = $fichero_t[0];
            $pathToImage = $directory . 'thumb.' . strtolower($info['filename']) . "." . $extension;

            // save thumbnail into a file
            if ($extension == "png") {
                header("Content-type: image/png");
                imagepng($tmp_img);
            } else if ($extension == "gif") {
                header("Content-type: image/gif");
                imagegif($tmp_img);
            } else {
                header("Content-type: image/jpeg");
                imagejpeg($tmp_img);
            }
        }
    //}
}
?>