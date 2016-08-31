<?php

require_once("../config.inc.php");
require_once("phpCache.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
require_once("cmsdb.php");
require_once("funciones_www.php");

function randomText($length) {
$pattern = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
for($i=0;$i<$length;$i++) { $key .= $pattern{rand(0,35)}; }
return $key;
}

session_start();

// PARAMETROS DE LA IMAGEN //////////////////////////////
$ancho = 100; // Ancho de la imágen
$alto = 30; // Alto de la imágen
$lineas = 10; // Cantidad de lineas de relleno
$chars = 4; // Cantidad de caracteres del captcha

// CREO EL OBJETO IMAGEN Y LOS COLORES A UTILIZAR ///////
$imagen = imagecreate($ancho,$alto);
$cLineas = imagecolorallocate($imagen,140,140,140);
$cFondo = imagecolorallocate($imagen,200,200,200);
$cTexto = imagecolorallocate($imagen,000,000,000);

// PINTO EL FONDO ///////////////////////////////////////
imagefill($imagen, 0, 0, $cFondo);

// AGREGO UNAS LINEAS DE RELLENO ////////////////////////
for($c=0; $c <= $lineas; $c++) {
$x1=rand(0,$ancho);
$y1=rand(0,$alto);
$x2=rand(0,$ancho);
$y2=rand(0,$alto);
imageline($imagen,$x1, $y1, $x2, $y2, $cLineas);
}


// GENERO EL TEXTO ALEATORIO ////////////////////////////
$_SESSION['tmpletras'] = randomText($chars);

// AGREGO EL TEXTO ALEATORIO A LA IMAGEN ////////////////
imagestring($imagen, 7, 30, 7, $_SESSION['tmpletras'], $cTexto);

// DEVUELVO LA IMAGEN GENERADA //////////////////////////
header("Content-type: image/jpeg", true);
imagejpeg($imagen);

// DESTRUYO EL OBJETO IMAGEN PARA LIBERAR MEMORIA ///////
imagedestroy($imagen); 

?>
