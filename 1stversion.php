<?php
// On defini la variable pngsource en tant qu'un tableau
$pngsource = array();

$pngnum = 0;
// On définit la largeur max dans le cas ou il n'y a pas d'images
$cxmax = 0;
// On définit la hauteur max dans le cas ou il n'y a pas d'images
$cymax = 0;
/* On definis sprite large en false pour que les images soit en colonnes si l'utilisateur décide
   qu'il veut un sprite en largeur il devras retourner True
*/
$spriteLarge = False;
// Temporaire
$cssfile="style.css";

// Ici on récupere les width , height et path de chaque image récuperer via Func_recursive
function Func_addpng($path) {
    global $pngsource, $pngnum;
    // on récupere les valeurs x et y via get image size via le $path
    list($cx, $cy) = getimagesize($path);
    // on crée un tableau $pngsource, pngnum s'incremente a chaque valeur récuperé via le tableau multidimentionnel (tableau de tableau)
    $pngsource[$pngnum] = array("cx"=>$cx,"cy"=>$cy,"path"=>$path);
    // ici on incremente a chaque image passé
    $pngnum = $pngnum+1;
}

// Ici on determine si l'on veux que le sprite soit en hauteur ou largeur via un True / false et si l'utilisateur veux definir une valeur de ses images
function Func_BuildNewPng()
{
    global $pngsource, $cxmax, $cymax;
    // donc on defini fixed size en false de base si l'utilisateur veux une taille définis il va falloir faire en sorte qu'il puisse changer la valeur en true puis lui demander les valeurs
    $fixedSize = false;
    // temporaire mais ici on définis les valeurs a 150 comme si l'utilisateur voulais que chaque image fasse 150x150
    $cxfixe = 150;
    $cyfixe = 150;

    // On definis spritelarge en false donc le sprite sera crée en hauteur l'utilisateur pourras choisir de changer la valeur en true pour qu'il puisse avoir un sprite en largeur
    $spriteLarge = false;

    // on a une boucle foreach
    foreach ($pngsource as $png) {
        // ici si on permet a l'utilisateur de changer la valeurs x et y de notre tableau multidimentionnel dans le cas ou il veut une taille fixée
        $cx = $fixedSize == true ? $cxfixe : $png["cx"];
        $cy = $fixedSize == true ? $cyfixe : $png["cy"];
        // condition si pour avoir le sprit en hauteur via le false
        if ($spriteLarge == false) {
            $cxmax = $cx > $cxmax ? $cx : $cxmax;
            $cymax = $cy + $cymax;
        }
        // condition else si $spritelarge == pas a false donc a true on auras donc un sprite en largeur
        else {
            $cymax = $png["cy"] > $cymax ? $png["cy"] : $cymax;
            $cxmax = $png["cx"] + $cxmax;
        }


    }
}

// fonction qui créer et reprends les valeurs récupéré préalablement (x et y) pour créer le css en fonction des valeurs
function Func_create_css_and_add_values($cssfile, $filename){
    global $pngsource, $cptimg, $spriteLarge;
    // on crée 2 variables pour le background position que l'on definis a 0
    $positionx = 0;
    $positiony = 0;
    // création et ouverture du fichier css puis écriture w+ pour ouverture ecriture et lecture
    $fp = fopen("$cssfile", "w+");
    fwrite($fp, ".sprite {
    background-image: url($filename);
    background-repeat: no-repeat;
    display: block;
}\n");
    // création de la boucle foreach pour chaque image qui va crée le css en fonction de leurs valeurs
    foreach($pngsource as $key => $value) {
        // on définit la valeurs que l'on a récuperer via le tableau
        $width = $value["cx"];
        $height = $value["cy"];
        fwrite($fp, "#img$cptimg {
    width: " . $width . "px;
    height: " . $height . "px;
    background-position: " . $positionx . "px ".$positiony."px;
}");
        // au dessus on a donc nos 2 position x et y prédefinis a 0
        // cpt img s'incrémente de 1 a chaque fois que la boucle ecris le css du png
        $cptimg += 1;
        // condition pour savoir si le sprite sera en largeur ou hauteur donc pour savoir si il faut placer la position x ou y et savoir laquelle restera a 0px
        if ($spriteLarge == True) {
            // on veut pas recuperer la premiere position car la premiere image sera de 0px 0px donc on commence a partir de la width += 1
            $positionx += $width + 1;
        } else {
            // pareil qu'au dessus mais avec la hauteur
            $positiony += $height + 1;
        }
    }
    fclose($fp);
}

// fonction qui crée le sprite
function Func_BuildSprite($filename) {

    global $cxmax, $cymax;
    // on lui donne les valeurs final du sprite (cxmax et cymax)
    $new = imagecreatetruecolor($cxmax, $cymax);
    $bg = imagecolorallocatealpha($new, 0 , 0, 0, 127);
    imagefill($new, 0, 0, $bg);
    imagesavealpha($new , true);
    //création du fichier png
    imagepng($new, $filename);

}
// fonction recursive qui va recuperer les fichier png dans le dossier et dans les sous dossier !! A CHANGER LE OPENDIR !!
function Func_recursive($pathname) {
    $files = scandir($pathname);
    foreach($files as $key => $value){
        $path = realpath($pathname.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            Func_addpng($path);
        } elseif($value != "." && $value != "..") {
            if($mode_recursif = true) {
                recursive($path);
            }
        }
    }
}

$pngfolder="./images/";
$recurive = true;

Func_recursive($pngfolder);
Func_BuildNewPng();
Func_create_css_and_add_values("style.css", "merge.png");
Func_BuildSprite("merge.png");

//Fonction a créer qui va supprimer tout les png pour ne garder que le sprite    Func_DeletePNG();
