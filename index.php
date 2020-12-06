<?php

//
// Exemple d'utilisation
// php index.php -outpng=test.png -outcss=test.css images
// php index.php -outpng=test.png -outcss=test.css -r images
//

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
$spriteLarge = True;

// Les argumens passés en paramètre
$szOutPngFile = "";
$szOutCssFile = "";
$szFoldertoScan = "";
$fRecursive = false;
$iSize = 0;


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
function calculate_size()
{
    global $pngsource, $cxmax, $cymax, $spriteLarge;
    // donc on defini fixed size en false de base si l'utilisateur veux une taille définis il va falloir faire en sorte qu'il puisse changer la valeur en true puis lui demander les valeurs
    $fixedSize = false;
    // temporaire mais ici on définis les valeurs a 150 comme si l'utilisateur voulais que chaque image fasse 150x150
    $cxfixe = 150;
    $cyfixe = 150;

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

    // on lui donne les valeurs final du sprite (cxmax et cymax)
    /*$new = imagecreatetruecolor($cxmax, $cymax);
    $bg = imagecolorallocate($new, 0 , 0, 0);
    imagefill($new, 0, 0, $bg);
    imagesavealpha($new , true);
    //création du fichier png
    imagepng($new, $pngfile);
    imagedestroy($new);*/
}

// fonction qui créer et reprends les valeurs récupéré préalablement (x et y) pour créer le css en fonction des valeurs
function my_generate_css($cssfile,$pngfile) {

    global $pngsource, $cptimg, $spriteLarge,$cxmax,$cymax;

    // on crée 2 variables pour le background position que l'on definis a 0
    $positionx = 0;
    $positiony = 0;

    $sprite = imagecreatetruecolor($cxmax, $cymax);
    $bg = imagecolorallocate($sprite, 0 , 0, 0);
    imagefill($sprite, 0, 0, $bg);
    imagealphablending($sprite, false);
    imagesavealpha($sprite , true);

    // création et ouverture du fichier css puis écriture w+ pour ouverture ecriture et lecture
    $fp = fopen("$cssfile", "w+");
    fwrite($fp, "* {
    margin: 0px;
    padding: Opx;
}\n");
    fwrite($fp, ".sprite {
    background-image: url($pngfile);
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
}\n");

        // FAIRE LE MERGE
        //$dest = imagecreatefrompng($szOutPngFile);
        $src = imagecreatefrompng($value["path"]);
        //echo($szOutPngFile.",".$positionx.",".$positiony.",".$width.",".$height."\n");
        imagecopy($sprite,$src,$positionx,$positiony,0,0,$width,$height);


        // au dessus on a donc nos 2 position x et y prédefinis a 0
        // cpt img s'incrémente de 1 a chaque fois que la boucle ecris le css du png
        $cptimg += 1;
        // condition pour savoir si le sprite sera en largeur ou hauteur donc pour savoir si il faut placer la position x ou y et savoir laquelle restera a 0px
        if ($spriteLarge == True) {
            // on veut pas recuperer la premiere position car la premiere image sera de 0px 0px donc on commence a partir de la width += 1
            $positionx += $width;
        } else {
            // pareil qu'au dessus mais avec la hauteur
            $positiony += $height;
        }

        imagedestroy($src);
    }
    fclose($fp);

    //header('Content-Type: image/png');
    imagepng($sprite,$pngfile);

    imagedestroy($sprite);


}


// fonction recursive qui va recuperer les fichier png dans le dossier et dans les sous dossier !! A CHANGER LE OPENDIR !!
function my_scandir($pathname) {

    global $fRecursive;

    if ($handle = opendir($pathname)) {

        while (false !== ($entry = readdir($handle))) {

            // on ne teste pas les . et ..
            if ($entry != "." && $entry != "..") {

                $fullpath=realpath($pathname.DIRECTORY_SEPARATOR.$entry);

                // Check if png
                if( strtolower(substr($fullpath, strrpos($fullpath, '.') + 1)) == 'png')
                {
                    Func_addpng($fullpath);
                }
                else {
                    if (is_dir($fullpath) && $fRecursive == true) my_scandir($fullpath);
                }

            }
        }

        closedir($handle);
    }

}


// Cette fonction est appellée lorsqu'un argument est manquant ou incoorect
// Paramètres:
//  $argerror : l'argument en erreur
//
function FuncBadArgs($argerror)
{
    global $argv;
    echo $argerror . " : Erreur de syntaxe\n";
    echo "Syntaxe acceptée :\n";
    echo "    $argv[0] arguments options dossier\n";
    echo "Les arguments suivantes sont attendus :\n";
    echo "    -outpng=valeur : nom du fichier png à générer\n";
    echo "    -outcss=valeur : nom du fichier css à générer\n";
    echo "Les options suivantes sont acceptées :\n";
    echo "    -r|--recursive : scan récursif des répertoires\n";
    echo "    -s|size=valeur : taille de redimensionnement des images\n";
    exit ("");
}

// Cette fonction teste tous les arguments passés en ligne de commande
//
function FuncTestArgs()
{
    global $argc, $argv, $fRecursive, $iSize, $szOutPngFile, $szOutCssFile, $szFoldertoScan, $fDebug;

    // On checke tous les arguments passés en ligne de commande
    for ($i = 1; $i < $argc - 1; $i++) {

        $arg = $argv[$i];

        if ($fDebug == true) echo "argument " . $i . " est " . $arg . "\n";

        if ($arg[0] == "-") {

            if ($fDebug == true) echo "    il s'agit d'une option\n";

            $j = explode("=", $arg);
            $value = "";
            if (count($j) == 2) {

                if ($fDebug == true) echo "    l'option contient une valeur\n";
                if ($fDebug == true) echo "    la valeur de " . $j[0] . " est égale à " . $j[1] . "\n";
                $arg = $j[0];
                $value = $j[1];
            }

            switch ($arg) {
                case "-outcss":
                    if ($value <> "") {
                        echo "Fichier CSS de sortie : " . $value . "\n";
                        $szOutCssFile = $value;
                    } else FuncBadArgs($arg);
                    break;
                case "-outpng":
                    if ($value <> "") {
                        echo "Fichier PNG de sortie : " . $value . "\n";
                        $szOutPngFile = $value;
                    } else FuncBadArgs($arg);
                    break;
                case "-r":
                case "--recursive":
                    if ($value == "") {
                        echo "Option de récursivité activée\n";
                        $fRecursive = true;
                    } else FuncBadArgs($arg);
                    break;
                case "-s":
                case "-size":
                    if ($value <> "") {
                        echo "Option de redimensionnement activée avec " . $value . "\n";
                        $iSize = $value;
                    } else FuncBadArgs($arg);
                    break;
                default:
                    FuncBadArgs($arg);
                    break;
            }
        }
    }

    // On récupére le dernier argument (notre nom de dossier)
    $szFoldertoScan = $argv[$argc - 1];
    if ($szFoldertoScan[0] == "-") FuncBadArgs("dossier");

    // On checke ici les arguments obligatoires
    if ($szFoldertoScan == "") FuncBadArgs("dossier");
    if ($szOutPngFile == "") FuncBadArgs("-outpng");
    if ($szOutCssFile == "") FuncBadArgs("-outcss");
}

// Fonction appellée pour tester les arguments
FuncTestArgs();

my_scandir($szFoldertoScan);
calculate_size();
my_generate_css($szOutCssFile,$szOutPngFile);