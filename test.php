<?php
function my_merge_image($first_img_path, $second_img_path, $filename)
{
    global $img1_width, $img1_height, $img2_height, $img2_width;
    list($img1_width, $img1_height) = getimagesize($first_img_path);
    list($img2_width, $img2_height) = getimagesize($second_img_path);
    $source1 = imagecreatefrompng($first_img_path);
    $source2 = imagecreatefrompng($second_img_path);
    $new_width = $img1_width > $img2_width ? $img1_width : $img2_width;
    $new_height = $img1_height + $img2_height;
    $new = imagecreatetruecolor($new_width, $new_height);
    imagealphablending($new, false);
    imagesavealpha($new, true);
    imagecopy($new, $source1, 0, 0, 0, 0, $img1_width, $img1_height);
    imagecopy($new, $source2, 0, $img1_height, 0, 0, $img2_width, $img2_height);
    imagepng($new, "$filename");
}


function css_generator($cssfile)
{
    global $img1_width, $img1_height, $img2_height, $img2_width, $filename;
    $fp = fopen("$cssfile", 'w');
    fwrite($fp, "* {
    padding: 0px;
    margin: 0px;
}\n");
    fwrite($fp, ".sprite {
    background-image: url($filename);
    background-repeat: no-repeat;
    display: block;
}\n");
    var_dump($filename);
    fwrite($fp, "#img1 {
    width: " . $img1_height . "px;
    height: " . $img1_width . "px;
    background-position: 0px 0px;
}\n");

    fwrite($fp, "#img2 {
    width: " . $img2_width . "px;
    height: " . $img2_height . "px;
    background-position: 0px -" . $img1_height . "px;
    position: absolute;
    left: 962px;
    top: 0px;
}");
    fclose($fp);
}
/*function my_scandir($dir_path){

if ($handle = opendir("$dir_path")) {

    while (false !== ($entry = readdir($handle))) {

        if ($entry != "." && $entry != ".." && substr($entry, -3) == "png") {

            echo "$entry\n";
        }
    }

    closedir($handle);
}
}*/

my_merge_image( "$argv[1]", "$argv[2]", "$argv[3]");
css_generator("style.css");
/*my_scandir("CSS_Generator");*/

