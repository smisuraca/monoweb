<?
//
// This source file is part of CEMESE, publishing software.
//
// Copyright (C) 2000-2001 Garcia Rodrigo.
// All rights reserved.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, US
//

function clone($obj) {
	return $obj;
}

/**
 * @global string $app_rootpath Ubicación de la aplicación
 */

$app_rootpath       = "./";

/**
 * @global string $app_includepath Ubicación de las Librerias
 */
$app_includepath    = "$app_rootpath/include";

/**
 * @global string $app_uploadpath Ubicación donde se realizan Uploads
 */
$app_uploadpath     = "$app_rootpath/upload";

/**
 * @global string $app_sessionpath Ubicación de las sesiones 
 */
$app_sessionpath    = "$app_rootpath/sessions";

/**
 * @global hash $DB Configuración de la Base de Datos 
 */
$Host["CMS"]  = "127.0.0.1";
$Db["CMS"]    = "cinevivo";
$User["CMS"]  = "root";
$Pass["CMS"]  = "1goshushijo6";

/**
 * @global hash Configuración del servidor de mail 
 */
$SMTP['HOST'] = 'localhost';
$SMTP['PORT'] = 25;

/**
 * @global Constante formateo de fecha y hora
 */
//setlocale ("LC_ALL", "spanish");
$FORMAT_DATE        = "%Y%m%d%H%M%S";
$FORMAT_HOURS       = "%H:%M";

ini_set('include_path', "$app_includepath:".ini_get('include_path'));
session_save_path($app_sessionpath);

require_once("funciones_common.php");

$app_rootpath       = dirname(__FILE__);
$TEMPLATE_ROOT      = "$app_rootpath/templates";

// Contantes para el control del cache
$CACHE_HIGH         = 1;
$CACHE_MEDIUM       = 1;
$CACHE_LOW          = 1;

/* Configuracion del Web */
$DEFAULT_NUM_DESTACADA      = 1;
$DEFAULT_NUM_NO_DESTACADA   = 4;
$DEFAULT_TYPE               = "html";
$DEFAULT_AREA               = "Portada";
$DEFAULT_LAYOUT             = "frame_default";
$DEFAULT_LANG               = "es";

require_once("locale.php");
require_once("locale_es_AR.php");




require_once("class.dbMysql.php");

/*
(.asf,.asx) video/x-ms-asf
(.wmv)      video/x-ms-wmv
(.wm)       video/x-ms-wm
(.wmx)      video/x-ms-wmx
(.wvx)      video/x-ms-wvx
(.wmz)      application/x-ms-wmz
(.wmd)      application/x-ms-wmd
(.ra,.ram,.rm) audio/x-pn-realaudio

(.wma)      audio/x-ms-wma
(.wax)      audio/x-ms-wax
.mp3		audio/mp3
.mp2, .mp3	audio/mpeg
.mp3		audio/x-mp3
.mp3		audio/x-mpeg
.mp3		audio/m3u
.mp3		audio/x-m3u


.jpg, .jpeg	image/jpeg
.gif        image/gif
.png        image/png


*/

$url = $GLOBALS["url"];
if($url) {
	$t = $GLOBALS["type"];

	if(strpos($url, "youtu") !== false) {
	

		preg_match("/(youtu.be\/|\/watch\?v=|\/embed\/)([a-z0-9\-_]+)/i", $url, $matches, 0, 0);
		$newurl = "http://img.youtube.com/vi/$matches[2]/default.jpg";
		if($t == "big")
			$newurl = "http://img.youtube.com/vi/$matches[2]/hqdefault.jpg";

		header("Location: $newurl");

	}

	if(strpos($url, "vimeo") !== false) {
	

		preg_match("/(vimeo.com\/|player.vimeo.com\/video\/)([a-z0-9\-_]+)/i", $url, $matches, 0, 0);


		$xmldata = file_get_contents("http://vimeo.com/api/v2/video/".$matches[2].".xml");

		preg_match("/<thumbnail_small>(\S+)<\/thumbnail_small>/i", $xmldata, $matches, 0, 0);
		$newurl = $matches[1];

		if($t == "big") {
			preg_match("/<thumbnail_large>(\S+)<\/thumbnail_large>/i", $xmldata, $matches, 0, 0);
			$newurl = $matches[1];
		}

		header("Location: $newurl");

	}



}


$db   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$sql =" SELECT links.idlink, links.fname, links.ftype, links.titulo, links_tipos.uploadpath";
$sql.=" from links, links_tipos where links.idlink_tipo=links_tipos.idlink_tipo";
$sql.=" and links.idlink = $idlink";
$db->exec($sql);

if ($db->getrow())
{
	$retpath=$app_uploadpath.$db->Fields['uploadpath'].sprintf("%d", $db->Fields['idlink']/1000)."/".$db->Fields['idlink'];

	$filename = $db->Fields['fname'];

	header("Content-Type: ".$db->Fields['ftype']);
	if (isset($disposition))
		header('Content-Disposition: '.$disposition.';filename="'.$filename.'"');

	readfile($retpath);
}
$db->close();
?>
