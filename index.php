<?php
class spritify {
function my_merge_image($first_img_path, $second_img_path){
    $width = auto;
    $height = auto;

    header("Content-Type: image/png");
    $im = imagecreate($width, $height);
    $image_a = imagecreatefrompng($first_img_path);
    imagecopyresampled($im, $image_a, 0, 0, 0, 0, 256, 256, 256, 256);

    $image_b = imagecreatefrompng($second_img_path);
    imagecopyresampled($im, $image_b, 256, 0, 0, 0, 256, 256, 256, 256);

    //imagepng($im, "merge.png");
    imagepng($im);

}

}
my_merge_image("a.png", "b.png");

function my_scandir($dir_path){

    if ($handle = opendir('.')) {

        while (false !== ($entry = readdir($handle))) {

            if ($entry != "." && $entry != "..") {

                echo "$entry\n";
            }
        }

        closedir($handle);
    }
}
