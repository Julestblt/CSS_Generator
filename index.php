<?php
function my_merge_image($first_img_path, $second_img_path, $filename){
    $image1 = dirname($first_img_path);
    $image2 = dirname($second_img_path);

    /* Get images dimensions */
    $size1 = getimagesize($first_img_path);
    $size2 = getimagesize($second_img_path);

    /* Load the two existing images */
    $im1 = imagecreatefrompng($first_img_path);
    $im2 = imagecreatefrompng($second_img_path);

    /* Create the new image, width is combined but height is the max height of either image */
    $im = imagecreatetruecolor($size1[0] + $size2[0], max($size1[1], $size2[1]));

    /* Merge the two images into the new one */
    imagecopy($im, $im1, 0, 0, 0, 0, $size1[0], $size1[1]);
    imagecopy($im, $im2, $size1[0], 0, 0, 0, $size2[0], $size2[1]);

    imagepng($im, "$filename");
    #Création du fichier css et transfert des données dans le fichier.
    function my_generate_css($cssfilename, $filename){
    $fp = fopen("$cssfilename", 'w');
    fwrite($fp, ".sprite {\n background-image: url($filename); \n}\n");
    fwrite($fp, ".img1 {\n width: ".$size1."px;\n height: ".$size1."px;\n}");
    fwrite($fp, "\n.img2 {\n width: ".$size2."px;\n height: ".$size2."px;\n}");
    fclose($fp);
    }
}
my_merge_image("a.png", "b.png", "toto.png");
my_generate_css('style.css', 'toto.png');


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