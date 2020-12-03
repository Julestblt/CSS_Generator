<?php
function my_merge_image($first_img_path, $second_img_path, $filename){
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


        $fp = fopen("style.css", 'w');
        fwrite($fp, ".sprite-c, .sprite-d {
    background-image: url($filename);
    background-repeat: no-repeat;
    display: block;
}\n");

        fwrite($fp, ".sprite-c {
    width: ".$img1_width."px;
    height: ".$img1_height."px;
    background-position: 0px 0px;
}\n");

        fwrite($fp, ".sprite-d {
    width: ".$img2_width."px;
    height: ".$img2_height."px;
    background-position: 0px -".$img1_height."px;
}");
        fclose($fp);

        if ($handle = opendir('.')) {

            while (false !== ($entry = readdir($handle))) {

                if ($entry != "." && $entry != ".." && substr($entry, -3) == "png") {

                    echo "$entry\n";
                }
            }

            closedir($handle);
        }
}
my_merge_image("c.png", "d.png", "merge.png");
