<?php
function my_merge_image ($first_img_path, $second_img_path){
    $width = 512;
    $height = 256;

    //header("Content-Type: image/png");
    $im = imagecreate($width, $height) or die("Error");
    $canvas = imagecolorallocate($im, 245, 245, 245);
    $image_a = imagecreatefrompng($first_img_path);
    imagecopyresampled($im, $image_a, 0, 0, 0, 0, 256, 256, 256, 256);

    $image_a = imagecreatefrompng($second_img_path);
    imagecopyresampled($im, $image_a, 256, 0, 0, 0, 256, 256, 256, 256);

    imagepng($im, "merge.png");

}
my_merge_image("a.png", "b.png");