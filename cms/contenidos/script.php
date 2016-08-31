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

require_once("../config.inc.php");




function get_anno($field)
{
	$val = split("-", $field);
	return $val[0];

}

function get_formato($field)
{
	$field = strtoupper($field);
	if(strstr($field, "NI-DV"))
		return 2;
	if($field == "DV")
		return 3;
	if(strstr($field, "HDV"))
		return 4;
	if($field == "HD")
		return 9;
	if(strstr($field, "8"))
		return 5;
	if(strstr($field, "16"))
		return 6;
	if(strstr($field, "35"))
		return 7;

	return 8;
}

function get_genero($field)
{
	$field = strtoupper($field);
	if(strstr($field, "ICC"))
		return 1;
	if(strstr($field, "OCU"))
		return 2;
	if(strstr($field, "XPERI"))
		return 3;
	if(strstr($field, "NIMA"))
		return 4;
	if(strstr($field, "OCLI"))
		return 5;
	if(strstr($field, "OMIN"))
		return 6;

	if(strstr($field, "RAI"))
		return 10;

	return 1;
}

function campo_similiar($field)
{
	switch ($field)
	{
		case "Cortometraje::Lugar Realizacion":
			return "lugar_realizacion";
		case "Cortometraje::Direccion":
			return "direccion";
		case "Cortometraje::Edicion":
			return "edicion";
		case "Cortometraje::Guion":
			return "guion";
		case "Cortometraje::Produccion":
			return "produccion";
		case "Cortometraje::Asistente de Direccion":
			return "asistente_direccion";
		case "Cortometraje::Sonido":
			return "sonido";
		case "Cortometraje::Fotografia":
			return "fotografia";
		case "Cortometraje::Camara":
			return "camara";
		case "Cortometraje::Arte":
			return "arte";
		case "Cortometraje::Musica":
			return "musica";
		case "Cortometraje::Productora":
			return "productora";
		case "Cortometraje::Idea Original":
			return "";
		case "Cortometraje::Interprete":
			return "interpretes";
		case "Cortometraje::Animacion":
			return "animacion";
		case "Cortometraje::Duracion":
			return "duracion";
		case "Cortometraje::Formato realizacion":
			return "formato_realizacion";
		case "Cortometraje::Idioma":
			return "idioma";
		case "Cortometraje::Subtitulo":
			return "";
		case "Cortometraje::Lugar realizacion":
			return "lugar_realizacion";
		case "Cortometraje::Premios":
			return "premios";

		case "image-gif":
			return "image_gif";
		case "image-jpg":
			return "";
		case "Cortometraje::url-video":
			return "url_cinevivo";
		case "Cortometraje::url-cinenacional":
			return "url_cinenacional";
		case "Cortometraje::url-kane":
			return "url_kane";
		case "Cortometraje::url-ext-video":
			return "url";
		case "Cortometraje::web":
			return "web";

		default: 
			return "";
	}
}

session_start();
if (!$HTTP_SESSION_VARS["usuarios_idusuario"]) {
	header("Location: ../home/");
	exit(0);
}
$dbCMS   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$dbCMS->connect();

// $objCMSUser = new usuarios();
// $objCMSUser->db = $dbCMS;
// $objCMSUser->name = 'loginuser';
// $objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);


$f = new ficha();
$f->db = $dbCMS;

$objContenidos = new contenidos();
$objContenidos->db = $dbCMS;
//$objContenidos->debug=1;
//$objContenidos->where = "contenidos.idcontenido_tipo=1";
$objContenidos->where = "contenidos.titulo_corto like '%rail%'";
//$objContenidos->where = "contenidos.titulo_corto like '%icci%'";

//$objContenidos->where = "contenidos.titulo_corto like '%rail%' OR  contenidos.titulo_corto like '%ocument%' OR  contenidos.titulo_corto like '%icci%' OR  contenidos.titulo_corto like '%xperime%' OR  contenidos.titulo_corto like '%nimac%' OR  contenidos.titulo_corto like '%deocli%' OR  contenidos.titulo_corto like '%deominu%'";
//$objContenidos->limit_from = 400;
//$objContenidos->limit_count = 600;
//exit(0);
//$objContenidos->where = "contenidos.idcontenido_tipo=1 ";
//$objContenidos->order_by = "fecha asc";
//$objContenidos->limit_count = 5;
foreach ($objContenidos->fetchall() as $row) {

	$f = new ficha();
	$f->db = $dbCMS;
	$f->ID('');
	$f->field('titulo', $row['titulo']);
	$f->field('reproducciones', $row['extra1']);
	if(get_genero($row['titulo_corto']) == 10) {
		$f->field('material', 1);
		$f->field('genero', 7);
	} else {
		$f->field('material', 0);
		$f->field('genero', get_genero($row['titulo_corto']));
	}
	$f->field('sinopsis_es', $row['texto']);
	$f->field('anno_realizacion', get_anno($row['fecha']));
	
	foreach($row["mmedias"] as $mmedia) {
		
		$campo_nuevo = campo_similiar($mmedia["mmedia"]["tipo"]["descripcion"]);

		if($campo_nuevo != "") {
	    		if($campo_nuevo == "formato_realizacion") {
    				$f->field($campo_nuevo, get_formato($mmedia["mmedia"]["titulo"]));
    				
			} else if($campo_nuevo == "image_gif") {
    				$f->field($campo_nuevo, $mmedia["mmedia"]["idlink"]);
			} else {
//	    			echo "<br><br>LLL: ".$f->values[$campo_nuevo]."<br><br>";
	    			if($f->field($campo_nuevo) != "") {
	    				if($campo_nuevo == "premios") {
						$f->field($campo_nuevo, $f->values[$campo_nuevo]."\n".$mmedia["mmedia"]["titulo"]);
					} else {
						$f->field($campo_nuevo, $f->values[$campo_nuevo].", ".$mmedia["mmedia"]["titulo"]);
					}
				} else {
					$f->field($campo_nuevo, $mmedia["mmedia"]["titulo"]);
				}
			}
		}
//		echo " ";
//		echo $mmedia["mmedia"]["titulo"];
//		echo "<br>";
		
	}

	$f->store();
	
	echo $f->ID();
	echo "  :: ";
	echo $f->values["titulo"];
	echo "  :: ";
	echo $f->values["material"];
	echo "<br>";
//	print_r($f);

// 
// 
// 
// 
}


$dbCMS->close();
?>
