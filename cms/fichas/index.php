<?
//
// This source file is part of CEMESE, publishing software.
//
// Copyright (C) 2000-2005 Garcia Rodrigo.
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

require_once("JSON.php");
require_once("JSON_UTF.php");

function handleevent($event)
{
	global $objFichas, $objCMSUser, $dbCMS;

	switch ($event) {
		case "show":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$objFichas->where="1=0";
			$result = $objFichas->doformatall("/fichas.html", $GLOBALS["TEMPLATE_ROOT"]);
			return $result;

			break;

		case "show_upload_foto":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$obj = new ficha();
			$obj->db = $dbCMS;

			$obj->fetch($GLOBALS['id']);
			if($obj->values['id'] > 0) {
				$result = $obj->doformat("/upload_foto.html", $GLOBALS["TEMPLATE_ROOT"]);
			} else {
				$result = "No hay ficha cargada aun";
			}
			print $result;
			exit(0);
			
			break;



/*		case "publicacion_store":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");

			if (!$objFichas->ID()) handleevent("store");

			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;
			$objPublicacion->ID($GLOBALS['publicacion_idpublicacion']);
			$objPublicacion->field('idficha', $GLOBALS['publicacion_idficha']);
			$objPublicacion->field('idarea', $objFichas->ID());
			$objPublicacion->field('fecha_publicacion', $GLOBALS['publicacion_fecha']);
			$objPublicacion->field('destacada', $GLOBALS['publicacion_destacada']);
			$objPublicacion->field('orden', $GLOBALS['publicacion_orden']);
			$objPublicacion->store();

			$GLOBALS['publicacion_idpublicacion'] = 0;
			break;

		case "publicacion_delete":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");
			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;
			$objPublicacion->ID($GLOBALS['publicacion_idpublicacion']);
			$objPublicacion->delete();

			$GLOBALS['publicacion_idpublicacion'] = 0;
			break;
*/
		case "list":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$obj = new ficha();
			$obj->db = $dbCMS;
		
			$where = "1";

		        if($GLOBALS['filter_id']) $where.= " AND id = ".$GLOBALS['filter_id'];
		        if($GLOBALS['filter_titulo']) $where.= " AND titulo like '%".$GLOBALS['filter_titulo']."%'";
		        if($GLOBALS['filter_direccion']) $where.= " AND direccion like '%".$GLOBALS['filter_direccion']."%'";
		        if($GLOBALS['filter_genero']) $where.= " AND genero = ".$GLOBALS['filter_genero'];
		        if($GLOBALS['filter_material'] != "") $where.= " AND material = ".$GLOBALS['filter_material'];
		        if($GLOBALS['filter_publicacion'] != "") $where.= " AND publicacion = ".$GLOBALS['filter_publicacion'];
		        
		        
			$obj->where = $where;
			$obj->limit_from  = ($GLOBALS['fichas_limit_from']  > 0) ? $GLOBALS['fichas_limit_from'] : 0;
			$obj->limit_count = ($GLOBALS['fichas_limit_count'] > 0) ? $GLOBALS['fichas_limit_count'] : 20;
			$obj->order_by    = ($GLOBALS['filter_order_by']    != "") ? $GLOBALS['filter_order_by'] : "titulo ASC";
			$json = new JSON();

// 			print_r($objForos->fetchall());
			$result = array();
			$result['fichas'] = $obj->fetchall();
			$result['from'] = $obj->limit_from;
			$result['count'] = $obj->limit_count;
			$result['total'] = $obj->count();

                        print($json->stringify($result));
                        exit();

			break;

		case "edit":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$obj = new ficha();
			$obj->db = $dbCMS;
//			$objacion->disableTables(array("fichas_links", "fichas_tipos", "areas"));

			$json = new JSON();

			$result = array();
			$result['f'] = $obj->fetch($GLOBALS['id']);

                        print($json->stringify($result));
                        exit();

			break;

		case "delete":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");

			$obj = new ficha();
			$obj->db = $dbCMS;

			$ids_array = split(",", $GLOBALS['ids']);
			if (count($ids_array) <= 1) 
				exit();

			$sql = "DELETE FROM ficha WHERE id IN (".$GLOBALS['ids'].")";

			if ($obj->db->exec($sql, 0))
			{
				echo "0";
			} else {
				echo "1";
			}
			exit();

			break;

		case "grabar_ficha":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");

			$obj = new ficha();
			$obj->db = $dbCMS;
			
			foreach ($obj->fields as $f) {
				$obj->field($f, $GLOBALS[$f]);
			}
			$obj->store();

//			echo "holaaa ".$GLOBALS['titulo'];
			exit();
			break;
/*
		case "autocompleter_fichas":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$fichas = new fichas();
			$fichas->db = $dbCMS;
			$fichas->disableTables(array("fichas_links", "publicacion", "opciones"));
			$fichas->where = "titulo like '".$GLOBALS['ficha_titulo']."%'";
			$fichas->order_by = "titulo asc";
			$fichas->limit_count = 10;

			print($fichas->doformatall("/autocompleter_fichas.html", $GLOBALS["TEMPLATE_ROOT"]));
                        exit();

			break;
*/

		case "upload_foto":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");


			$obj = new ficha();
			$obj->db = $dbCMS;

			$obj->fetch($GLOBALS['idficha']);

                        $objMMedia = new links();
                        $objMMedia->db = $dbCMS;
                        $objMMedia->ID($obj->values['image_gif']);
                        $objMMedia->field('idlink_tipo', 44);
                        $objMMedia->field('titulo', "");
                        $objMMedia->field('url', "");
                        $objMMedia->field('orden', 0);
                        $objMMedia->field('file', $GLOBALS['mmedia_file']);
                        $objMMedia->field('uploadpath', $GLOBALS['app_uploadpath']);
                        $objMMedia->field('ftype', ($GLOBALS['mmedia_file'] ? $GLOBALS['mmedia_file_type'] : $GLOBALS['mmedia_ftype']));
                        $objMMedia->field('fname', ($GLOBALS['mmedia_file'] ? $GLOBALS['mmedia_file_name'] : $GLOBALS['mmedia_fname']));
                        $objMMedia->store();

			$obj->field('image_gif', $objMMedia->ID());
			$obj->store();
			print "IDficha: ".$GLOBALS['idficha']." - Imagen subida #".$objMMedia->ID();

			exit(0);
			break;

		case "permission_denied":
			return $objCMSUser->doformat("/permission_denied.html", $GLOBALS["TEMPLATE_ROOT"]);
			break;

		default:
			return handleevent("show");
	}
}


session_start();
if (!$HTTP_SESSION_VARS["usuarios_idusuario"]) {
	header("Location: ../home/");
	exit(0);
}
$dbCMS   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$dbCMS->connect();

$objFichas = new areas();
$objFichas->db = $dbCMS;
$objFichas->ID($idarea);

$objCMSUser = new usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS::ABM Fichas");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);

$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
