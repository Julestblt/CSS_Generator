<?php

$cxmax = 0;
$cymax = 0;
$pngsource = array();
$pngnum = 0;
$szFoldertoScan = "";
$fRecursive = false;
$szOutPngFile = "sprite.png";
$szOutCssFile = "style.css";
$fDeletePNG = false;
$fSpriteRow = false;
$fDebug = false;
$iOverrideValue = 0;
$fOverrideSize = false;
$szOutHTMLFile= "index.html";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncAddPNG
// Ici on récupere les width , height et path de chaque image récuperée via le myscandir
function FuncAddPNG($path) {
    global $pngsource, $pngnum;

    // on récupere les valeurs x et y via get image size via le $path
    list($cx, $cy) = getimagesize($path);

    // on crée un tableau $pngsource,
    // pngnum s'incremente a chaque valeur récuperée via le tableau multidimentionnel (tableau de tableau)
    $pngsource[$pngnum] = array("cx"=>$cx,"cy"=>$cy,"path"=>$path);

    // ici on incremente a chaque image passée
    $pngnum = $pngnum+1;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncCalculateSize
// Fonction qui calcule la taille du sprite de destination en fonction des PNG source et de la disposition finale attendue
function FuncCalculateSize()
{
    global $pngsource, $cxmax, $cymax, $iOverrideValue, $fOverrideSize, $fSpriteRow;
    $cxfixe = $iOverrideValue;
    $cyfixe = $iOverrideValue;

    foreach ($pngsource as $png) {

        $cx = $fOverrideSize == true ? $cxfixe : $png["cx"];
        $cy = $fOverrideSize == true ? $cyfixe : $png["cy"];

        if ($fSpriteRow == false) {
            $cxmax = $cx > $cxmax ? $cx : $cxmax;
            $cymax = $cy + $cymax;
        } else {
            $cymax = $cy > $cymax ? $cy : $cymax;
            $cxmax = $cx + $cxmax;
        }
    }

    Debug("cxmax=".$cxmax);
    Debug("cymax=".$cymax);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncGenerateCSS_PNG
// fonction qui créer et reprends les valeurs récupéré préalablement (x et y) pour créer le css et le png
function FuncGenerateCSS_PNG($cssfile,$pngfile) {

    global $pngsource, $cptimg, $fSpriteRow,$cxmax,$cymax;
    $positionx = 0;
    $positiony = 0;

    $sprite = imagecreatetruecolor($cxmax, $cymax);
    $bg = imagecolorallocatealpha($sprite, 0 , 0, 0, 127);
    imagefill($sprite, 0, 0, $bg);
    imagesavealpha($sprite , true);

    $fp = fopen("$cssfile", "w+");
    fwrite($fp, ".sprite {
    background-image: url($pngfile);
    background-repeat: no-repeat;
    display: block;
}\n");

    foreach($pngsource as $key => $value) {

        $width = $value["cx"];
        $height = $value["cy"];

    if($fSpriteRow==true) {
        fwrite($fp, "#img$cptimg {
    width: " . $width . "px;
    height: " . $height . "px;
    background-position: -" . $positionx . "px 0px;
}");
    } else{
        fwrite($fp, "#img$cptimg {
    width: " . $width . "px;
    height: " . $height . "px;
    background-position:  0px -".$positiony."px;
}");
    }

        $src = imagecreatefrompng($value["path"]);
        imagecopy($sprite,$src,$positionx,$positiony,0,0,$width,$height);

        $cptimg++;

        if ($fSpriteRow == True) {
            $positionx += $width;
        } else {
            $positiony += $height;
        }

        imagedestroy($src);
    }
    fclose($fp);
    imagepng($sprite,$pngfile);
    imagedestroy($sprite);

}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Function qui créer l'html
function FuncGenerateHTML(){
    global $cpthmtl, $pngsource, $szOutCssFile, $szOutHTMLFile;
    $fo = fopen($szOutHTMLFile, 'w+');
    fwrite($fo, '<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="'.$szOutCssFile.'">
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>');
    foreach($pngsource as $key => $value){

        fwrite($fo, '
<i class="sprite" id="img'.$cpthmtl.'"></i>');
        $cpthmtl++;
    }
    fwrite($fo, "
</body>
</html>");
    fclose($fo);
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncDeletePNG();
// Supprimer les PNG listés dans le tableau
function FuncDeletePNG()
{
    global $pngsource;

    foreach ($pngsource as $key => $value) {
        $pathtodelete = $value["path"];
        Debug("Delete de " . $pathtodelete);
        unlink($pathtodelete);
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncScandir
// fonction recursive qui va recuperer les fichier png dans le dossier et dans les sous dossier !! A CHANGER LE OPENDIR !!
function FuncScandir($pathname) {
    global $fRecursive;

    if (!is_dir($pathname)) {
        exit ("assets_folder doesn't exist!");
    }

    if ($handle = opendir($pathname)) {

        while (false !== ($entry = readdir($handle))) {

            Debug("var entry : ".$entry);

            if ($entry != "." && $entry != "..") {

                $fullpath=realpath($pathname.DIRECTORY_SEPARATOR.$entry);

                if( strtolower(substr($entry, strrpos($entry, '.') + 1)) == 'png')
                {
                    Debug("PNG : ".$entry);
                    FuncAddPNG($fullpath);
                }
                else {
                    if (is_dir($fullpath) && $fRecursive == true) {
                        Debug("DIR : ".$entry);
                        FuncScandir($fullpath);
                    }
                    else {
                        Debug("no action : ".$entry);
                    }
                }

            }
        }

        closedir($handle);
    }
    else {
        exit ("fatal error, unable to get an handle");
    }

}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncBadArgs
// Cette fonction est appellée lorsqu'un argument est manquant ou incoorect
function FuncBadArgs($argerror)
{
    exit ( "Error: ".$argerror ."\n");
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cette fonction affiche un message à l'écran seulement si la variable globale fDebug est activée
// Paramètres:
function Debug($message)
{
    global $fDebug;

    if ($fDebug == true) echo "debug:".$message."\n";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cette fonction affiche le man
function FuncHelp() {
    echo <<<EOL
CSS_GENERATOR(1) UserCommands CSS_GENERATOR(1)
NAME
     css_generator - sprite generator for HTML use
SYNOPSIS
     css_generator [OPTIONS]. . . assets_folder
DESCRIPTION
     Concatenate all images inside a folder in one sprite and write a style sheet ready to use. 
     Mandatory arguments to long options are mandatory for short options too.
     -r, --recursive
     Look for images into the assets_folder passed as arguement and all of its subdirectories.
     
     -i, --output-image=IMAGE
     Name of the generated image. If blank, the default name is « sprite.png ».
     
     -s, --output-style=STYLE
     Name of the generated stylesheet. If blank, the default name is « style.css ».
     -p, --padding=NUMBER
     Add padding between images of NUMBER pixels.
     
     -d, --delete
     Delete the original PNG after the merge. If blank, the default style is false.
     -o, --override-size=SIZE
     Force each images of the sprite to fit a size of SIZExSIZE pixels.
     
     -c, --columns_number=NUMBER
     The maximum number of elements to be generated horizontally.
     
     -w, --row
     Option to get the sprite in width.
     
     -h, --output-html=HTML
     Name of the HTML file.
     
     --debug
     Debug option.
EOL;
    exit ("");
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cette fonction affiche la valeur d'un boolean un chaine de caractere
// Paramètre :
//    Un boolean
// Retour :
//    Une chaine de caractère "TRUE" ou "FALSE"
function BooleanToString($myBoolean) {
    if ($myBoolean==true) {
        return "True";
    } else {
        return "False";
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cette fonction teste tous les arguments passés en ligne de commande
function FuncTestArgs()
{
    global $argc, $argv ;

    global $fRecursive, $iOverrideValue, $fOverrideSize, $szOutPngFile, $szOutCssFile, $szFoldertoScan,$fSpriteRow,$fDeletePNG,$fDebug, $szOutHTMLFile;

    if ($argc <2 )  FuncBadArgs("missing arguments");

    if ( $argv[1]=="-h" || $argv[1]=="--help") FuncHelp();

    for ($i = 1; $i < $argc - 1; $i++) {

        $arg = $argv[$i];
        Debug ("argument " . $i . " est " . $arg);

        if ($arg[0] == "-") {

            Debug ( "il s'agit d'une option");

            $j = explode("=", $arg);

            $value = "";
            if (count($j) == 2) {

                Debug( "l'option contient une valeur");
                Debug( "la valeur de " . $j[0] . " est égale à " . $j[1]);

                $arg = $j[0];
                $value = $j[1];
            }

            switch ($arg) {
                case "-s":
                case "--output-style":
                    if ($value <> "") {
                        echo "Out CSS file : " . $value . "\n";
                        $szOutCssFile = $value;
                    } else FuncBadArgs($arg);
                    break;
                case "-i":
                case "--output-image":
                    if ($value <> "") {
                        echo "Out PNG file : " . $value . "\n";
                        $szOutPngFile = $value;
                    } else FuncBadArgs($arg);
                    break;
                case "-r":
                case "--recursive":
                    if ($value == "") {
                        echo "Recursivity option activated\n";
                        $fRecursive = true;
                    } else FuncBadArgs($arg);
                    break;
                case "--debug":
                    $fDebug=true;
                    break;
                case "-d":
                case "--delete":
                    echo "Delete of the png files\n";
                    $fDeletePNG = true;
                    break;
                case "-w":
                case "--row":
                    echo "ROW option activated\n";
                    $fSpriteRow=true;
                    break;
                case "-o":
                case "--override-size=":
                    if ($value <> "") {
                        echo "Resizing option with : " . $value . "px\n";
                        $iOverrideValue = $value;
                        $fOverrideSize = true;
                    } else FuncBadArgs($arg);
                    break;
                case "-m":
                case "--manual":
                    FuncHelp();
                    break;
                case "-h";
                case "--output-html";
                if ($value <> "") {
                    echo "Out HTML file : " . $value . "\n";
                    $szOutHTMLFile = $value;
                    } else FuncBadArgs($arg);
                    break;
                default:
                    FuncBadArgs($arg);
                    break;
            }
        }
    }
    $szFoldertoScan = $argv[$argc - 1];
    Debug("var szFoldertoScan=".$szFoldertoScan);
    Debug("var szOutPngFile=".$szOutPngFile);
    Debug("var fDeletePNG=".BooleanToString($fDeletePNG));
    Debug("var szOutCssFile=".$szOutCssFile);
    Debug("var fRecursive=".BooleanToString($fRecursive));
    Debug("var fSpriteRow=".BooleanToString($fSpriteRow));
    Debug("var iOverrideValue=".$iOverrideValue);
    Debug("var fOverrideSize=".BooleanToString($fOverrideSize));

    if ($szFoldertoScan[0] == "-") FuncBadArgs("wrong output folder");
    if ($szFoldertoScan == "") FuncBadArgs("empty output folder");
    if ($szOutPngFile == "") FuncBadArgs("missing output png file");
    if ($szOutCssFile == "") FuncBadArgs("missing output css file");

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// MAIN BLOC

// Premier bloc
// Tester les arguments
FuncTestArgs();

// Second bloc
// Scanner le repertoire avec FuncScandir
FuncScandir($szFoldertoScan);

// Troisième bloc
// Calculer la taille du Sprite Final
FuncCalculateSize();

// Quatrième bloc
// Generer le CSS et le PNG et l'HTML
FuncGenerateCSS_PNG($szOutCssFile,$szOutPngFile);
FuncGenerateHTML();
// Cinquième bloc
// Supprimer les source PNG
if($fDeletePNG==true) FuncDeletePNG();
