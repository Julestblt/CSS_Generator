<?php

//
// Exemple d'utilisation
// php index.php -outpng=test.png -outcss=test.css images
// php index.php -outpng=test.png -outcss=test.css -r images
//

// On définit la largeur/hauteur max dans le cas ou il n'y a pas d'images
$cxmax = 0;
$cymax = 0;


// Norme de nommage des variables
// sz = String
// i  = Number
// f  = Boolean


// ******************* Les variables du tableau d'images à traiter
//

// On defini la variable pngsource en tant qu'un tableau
$pngsource = array();
$pngnum = 0;


// ******************* Les arguments passés en paramètre
//

// css_generator [OPTIONS]. . . assets_folder
// assets_folder
$szFoldertoScan = "";

// -r, --recursive
// Look for images into the assets_folder passed as arguement and all of its subdirectories.
$fRecursive = false;

// -i, --output-image=IMAGE
// Name of the generated image. If blank, the default name is « sprite.png ».
$szOutPngFile = "sprite.png";

// -s, --output-style=STYLE
// Name of the generated stylesheet. If blank, the default name is « style.css ».
$szOutCssFile = "style.css";

// -d, --delete
// Delete the original PNG after the merge. If blank, the default style is false.
$fDeletePNG = false;

// -w, --row
// Create a sprite - or |
$fSpriteRow = false;

// --debug
// Hidden option to enable the debug mode
$fDebug = false;


// -o, --override-size=SIZE
// Force each images of the sprite to fit a size of SIZExSIZE pixels.
$iOverrideValue = 0;
$fOverrideSize = false;

/*-c, --columns_number=NUMBER
The maximum number of elements to be generated horizontaly
-h, --manual
*/


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncAddPNG
// Ici on récupere les width , height et path de chaque image récuperée via le myscandir
//
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
//
function FuncCalculateSize()
{
    global $pngsource, $cxmax, $cymax, $iOverrideValue, $fOverrideSize, $fSpriteRow;

    // on définit les valeurs x et y fixes à partir de la variable $iOverrideValue
    // X et Y seront donc identiques
    // mais nous pourrions utiliser 2 arguments pour avoir X et Y différents
    $cxfixe = $iOverrideValue;
    $cyfixe = $iOverrideValue;

    foreach ($pngsource as $png) {

        // ici si on permet a l'utilisateur de changer la valeurs x et y de notre tableau multidimentionnel
        // dans le cas ou il veut une taille fixée
        $cx = $fOverrideSize == true ? $cxfixe : $png["cx"];
        $cy = $fOverrideSize == true ? $cyfixe : $png["cy"];

        // condition si pour avoir le sprit en hauteur via le false
        // mode column
        if ($fSpriteRow == false) {
            $cxmax = $cx > $cxmax ? $cx : $cxmax;
            $cymax = $cy + $cymax;
        }
        // condition else si $fSpriteRow == pas a false donc a true on auras donc un sprite en largeur
        // mode row
        else {
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

    // on crée 2 variables pour le background position que l'on definis a 0
    $positionx = 0;
    $positiony = 0;

    // Creation du sprite à la taille prealablement calculee cxmax et ctag
    // $sprite est le handle de sprite
    $sprite = imagecreatetruecolor($cxmax, $cymax);
    $bg = imagecolorallocatealpha($sprite, 0 , 0, 0, 127);
    imagefill($sprite, 0, 0, $bg);
    imagesavealpha($sprite , true);

    // création et ouverture du fichier css puis écriture w+ pour ouverture ecriture et lecture
    // $fp est le handle de fichier
    $fp = fopen("$cssfile", "w+");
    fwrite($fp, ".sprite {
    background-image: url($pngfile);
    background-repeat: no-repeat;
    display: block;
}\n");

    // création de la boucle foreach pour chaque image qui va créer le css en fonction de leurs valeurs
    foreach($pngsource as $key => $value) {

        // on définit la valeurs que l'on a récuperer via le tableau
        $width = $value["cx"];
        $height = $value["cy"];

        // Ecrire dans le CSS
        fwrite($fp, "#img$cptimg {
    width: " . $width . "px;
    height: " . $height . "px;
    background-position: " . $positionx . "px ".$positiony."px;
}");

        // FAIRE LE MERGE
        $src = imagecreatefrompng($value["path"]);
        imagecopy($sprite,$src,$positionx,$positiony,0,0,$width,$height);

        // au dessus on a donc nos 2 position x et y prédefinis a 0
        // cpt img s'incrémente de 1 a chaque fois que la boucle ecris le css du png
        $cptimg++;

        // condition pour savoir si le sprite sera en largeur ou hauteur donc pour savoir si il faut placer la position x ou y et savoir laquelle restera a 0px
        if ($fSpriteRow == True) {
            // on veut pas recuperer la premiere position car la premiere image sera de 0px 0px donc on commence a partir de la width += 1
            $positionx += $width;
        } else {
            // pareil qu'au dessus mais avec la hauteur
            $positiony += $height;
        }

        imagedestroy($src);
    }

    // Liberer le handle $fp de fichier CSS
    fclose($fp);

    //header('Content-Type: image/png');
    imagepng($sprite,$pngfile);

    // Liberer le handle $sprite de fichier PNG
    imagedestroy($sprite);

}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Function qui créer l'html
function FuncGenerateHTML($htmlfile){
    global $cpthmtl, $pngsource, $szOutCssFile;
    $fo = fopen($htmlfile, 'w+');
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

    // création de la boucle foreach pour chaque image qui va créer le css en fonction de leurs valeurs
    foreach ($pngsource as $key => $value) {

        // on définit la valeurs que l'on a récuperer via le tableau
        $pathtodelete = $value["path"];

        Debug("Delete de " . $pathtodelete);
        unlink($pathtodelete);
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncScandir
// fonction recursive qui va recuperer les fichier png dans le dossier et dans les sous dossier !! A CHANGER LE OPENDIR !!
function FuncScandir($pathname) {

    // Recuperer l'argument de recursivite
    global $fRecursive;

    // Tester si le dir existe
    if (!is_dir($pathname)) {
        exit ("assets_folder doesn't exist!");
    }

    // Un handle c'est une variable (un numéro) attribuée par le SYSTEME
    // Il identifie un flux ouvert sur lequel on peut ecrire ou recevoir des data
    // par exemple un fichier (mais aussi un flux réseau ou un autre process)
    if ($handle = opendir($pathname)) {

        // On demande au handle de procéder à lister les fichiers du répertoire
        // Pour cela on appelle la fonction "readdir"
        // Donc
        // While (tant que tu me retournes qqchose avec) readdir
        //    je vais traiter ce que tu m'envoies avec le code entre crochets {}
        // Exemple
        // si dans le rep images nous avons
        ///   one.png et two.png
        /// readdir va nous retourner 4 entrées
        ///   la premiere c'est . (indicateur systeme de repertoire courant)
        ///   la seconde c'est .. (indicateur systeme de repertoire précédent)
        ///   la troisieme c'est one.png
        ///   la quatrieme c'est two.png
        //
        while (false !== ($entry = readdir($handle))) {

            Debug("var entry : ".$entry);

            // on ne teste pas les . et ..
            if ($entry != "." && $entry != "..") {

                // Ici, on fabrique le chemin complet
                // exemple
                // avec $entry = one.png
                // $fullpath = /users/joe/test/one.png
                $fullpath=realpath($pathname.DIRECTORY_SEPARATOR.$entry);

                // Check if png
                // strtolower :
                //      str (string) to (vers) lower (lettre minuscules)
                //      transforme une string en lettres minuscules ex : PNG devient png
                //
                // substr:
                //      sub (sous) str (string)
                //      sous-chaine de entry ex one.png devient png
                //
                // strrpos :
                //      str (string) r (reverse) pos (position
                //      position du caractere "." en partant de la fin de la chaine
                if( strtolower(substr($entry, strrpos($entry, '.') + 1)) == 'png')
                {
                    Debug("PNG : ".$entry);
                    FuncAddPNG($fullpath);
                }
                else {
                    // Si ce n'est pas un PNG, regardons s'il s'agit d'un subdir
                    // et si c'est le cas, scannons le subdir si l'option de recursivité a été demandée
                    if (is_dir($fullpath) && $fRecursive == true) {
                        Debug("DIR : ".$entry);
                        FuncScandir($fullpath);
                    }
                    else {
                        // not a png
                        // not a dir
                        Debug("no action : ".$entry);
                    }
                }

            }
        }

        // Liberer le handle $handle de opendir
        closedir($handle);
    }
    else {
        exit ("fatal error, unable to get an handle");
    }

}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FuncBadArgs
// Cette fonction est appellée lorsqu'un argument est manquant ou incoorect
// Paramètres:
//  $argerror : l'argument en erreur
//
function FuncBadArgs($argerror)
{
    exit ( "Error: ".$argerror ."\n");
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cette fonction affiche un message à l'écran seulement si la variable globale fDebug est activée
// Paramètres:
//   Le message à afficher
function Debug($message)
{
    // Variable de debug
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
     Option to get the sprite in width
     
     --debug
     Debug option
EOL;

    exit ("");

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cette fonction affiche la valeur d'un boolean un chaine de caractere
// Paramètre :
//    Un boolean
// Retour :
//    Une chaine de caractère "TRUE" ou "FALSE"
//
function BooleanToString($myBoolean) {
    if ($myBoolean==true) {
        return "True";
    } else {
        return "False";
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cette fonction teste tous les arguments passés en ligne de commande
//
function FuncTestArgs()
{
    // Variables système
    // argc: nombre d'arguments
    // argv: le tableau "string" des arguments
    global $argc, $argv ;

    // Variables d'aguments passés en ligne de commande
    global $fRecursive, $iOverrideValue, $fOverrideSize, $szOutPngFile, $szOutCssFile, $szFoldertoScan,$fSpriteRow,$fDeletePNG,$fDebug;

    // Tester le nombre minimal d'arguments
    // css_generator assets_folder
    // donc argc doit etre au minimum 2
    if ($argc <2 )  FuncBadArgs("missing arguments");

    // Tester si assets_folder ne serait pas en fait un appel -h ou --help
    if ( $argv[1]=="-h" || $argv[1]=="--help") FuncHelp();

    // On checke tous les arguments passés en ligne de commande (sauf le asset folder)
    // css_generator -option1  -option2 -option3  assets_folder
    //    argv[0]     argv[1]  argv[2]  argv[3]  argv[4]
    // Ici argc = 5
    //                  $i=1            jusque 3 (donc inférieur à 4 donc argc -1)
    // On passe dans la boucle tant que $i est inférieur à 4
    for ($i = 1; $i < $argc - 1; $i++) {

        // On place l'argument de l'index $i dans la variable $arg
        // pour simplifier l'utilisation ensuite
        $arg = $argv[$i];

        // debug: afficher l'argument utilisé
        Debug ("argument " . $i . " est " . $arg);

        // Vérifions que le remiere car est bien un -
        // -option1
        // [0] doit être égal au caractère -
        if ($arg[0] == "-") {

            Debug ( "il s'agit d'une option");

            // Récupérons maintenant l'option et sa valeur éventuelle
            // Pour cela utilisons la fonction "explode"
            // Qui va remplir un tableau avec l'option ET sa valeur
            // L'option sera dans le [0] du tableau
            // La valeur sera dans le [1] du tableau
            // Explode prend 2 arguments :
            //    Le séparateur (pour nous c'est le caractère =)
            //    La chaine de caractère à découper (pour nous c'est donc $arg qui est l'option que nous sommes en train de traiter)
            // Exemple
            //   $argc = "--output-style=fichier.css"
            //   Après explode, $j sera :
            //   $j[0] = --output-style
            //   $j[1] = fichier.css
            $j = explode("=", $arg);

            $value = "";
            if (count($j) == 2) {

                Debug( "l'option contient une valeur");
                Debug( "la valeur de " . $j[0] . " est égale à " . $j[1]);

                // Nous allons remettre uniquement la valeur de l'option dans $argc
                // Exemple
                // AVANT $argc = "--output-style=fichier.css"
                // APRES $argc = "--output-style"
                $arg = $j[0];

                // Et récupérer la valeur dans $value
                $value = $j[1];
            }

            // Tester si $arg est une option reconnue
            switch ($arg) {
                case "-s":
                case "--output-style":
                    if ($value <> "") {
                        echo "Fichier CSS de sortie : " . $value . "\n";
                        $szOutCssFile = $value;
                    } else FuncBadArgs($arg);
                    break;
                case "-i":
                case "--output-image":
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
                case "--debug":
                    $fDebug=true;
                    break;
                case "-d":
                case "--delete":
                    $fDeletePNG = true;
                    break;
                case "-w":
                case "--row":
                    echo "Option ROW activée\n";
                    $fSpriteRow=true;
                    break;
                case "-o":
                case "--override-size=":
                    if ($value <> "") {
                        echo "Option de redimensionnement activée avec " . $value . "\n";
                        $iOverrideValue = $value;
                        $fOverrideSize = true;
                    } else FuncBadArgs($arg);
                    break;
                case "-h":
                case "--help":
                    FuncHelp();
                    break;
                default:
                    FuncBadArgs($arg);
                    break;
            }
        }
    }

    // On récupére le dernier argument (notre nom de dossier)
    // css_generator option1   option2  option3  assets_folder
    //    0             1        2         3         4
    //  argc = 5 donc le dernier argument est 4 donc argc-1
    $szFoldertoScan = $argv[$argc - 1];

    // On trace les valeurs retenues en debug
    Debug("var szFoldertoScan=".$szFoldertoScan);
    Debug("var szOutPngFile=".$szOutPngFile);
    Debug("var fDeletePNG=".BooleanToString($fDeletePNG));
    Debug("var szOutCssFile=".$szOutCssFile);
    Debug("var fRecursive=".BooleanToString($fRecursive));
    Debug("var fSpriteRow=".BooleanToString($fSpriteRow));
    Debug("var iOverrideValue=".$iOverrideValue);
    Debug("var fOverrideSize=".BooleanToString($fOverrideSize));

    // Chaine de caractere c'est un tableau de caractères
    // Donc si szTest = ABCD
    // Alors szTest[0] = A
    // Alors szTest[3] = D
    //
    // On teste si le folder to scan n'est pas une option
    // Donc si le premier caractère n'est pas un signe -
    if ($szFoldertoScan[0] == "-") FuncBadArgs("wrong output folder");

    // On checke ici les arguments obligatoires
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
//     qui appelle la fonction FuncAddPNG pour chaque PNG trouvé
//     celle-ci placera les valeurs X et Y des PNG dans le tableau $pngsource
FuncScandir($szFoldertoScan);

// Troisième bloc
// Calculer la taille du Sprite Final
//     qui donne les valeurs dans $cxmax et $cymax
FuncCalculateSize();

// Quatrième bloc
// Generer le CSS et le PNG
FuncGenerateCSS_PNG($szOutCssFile,$szOutPngFile);
FuncGenerateHTML("index.html");
// Cinquième bloc
// Supprimer les source PNG
if($fDeletePNG==true) FuncDeletePNG();
