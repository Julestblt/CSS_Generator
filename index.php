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

    /*foreach ($new as $file => $img) {
        if (isset($img['extension'], $img['x'], $img['y'], $img['width'], $img['height'])) {
            image_overlay(
                $new,
                $path.'/'.$file,
                $img['extension'],
                $type=='x'? 0 : $img['x'], // dst_x
                $type=='t'? 0 : $img['y'], // dst_y
                0, // src_x
                0, // src_y
                $type=='x'? $x : $img['width'], // dst_w
                $type=='y'? $y : $img['height'], // dst_h
                $img['width'], // src_w
                $img['height'] // src_h
            );
        }
    }*/


    #Création du fichier css et transfert des données dans le fichier.
        $fp = fopen("style.css", 'w');
        fwrite($fp, ".sprite {\n background-image: url($filename); \n}\n");
        fwrite($fp, ".img1 {\n width: ".$img1_width."px;\n height: ".$img1_height."px;\n}");
        fwrite($fp, "\n.img2 {\n width: ".$img2_width."px;\n height: ".$img2_height."px;\n}");
        fclose($fp);

}
my_merge_image("a.png", "b.png", "merge.png");

/*function my_scandir($dir_path){

    if ($handle = opendir('.')) {

        while (false !== ($entry = readdir($handle))) {

            if ($entry != "." && $entry != ".." && substr($entry, -3) == "png") {

                echo "$entry\n";
            }
        }

        closedir($handle);
    }
}*/