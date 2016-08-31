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
	global $objAreas, $objCMSUser, $dbCMS;

	switch ($event) {
		case "edit":
			if (!$objCMSUser->checkPermission("Areas::lectura"))
				return handleevent("permission_denied");

			$objAreas->disableTables(array("publicacion"));

// 			$objAreas->joins[publicacion]->order_by = 'destacada, fecha_publicacion DESC, orden DESC';
			$objAreas->fetch($objAreas->ID());

// 			$objContenidos = new contenidos();
// 			$objContenidos->db = $objAreas->db;
// 			$objContenidos->joins = array();
// 			$objContenidos->order_by=" titulo ";

// 			$objPublicacion = new publicacion();
// 			$objPublicacion->joins = array();
// 			$objPublicacion->db = $objAreas->db;

// 			$result = $objContenidos->doformatall("/areas_edit.html", $GLOBALS["TEMPLATE_ROOT"]);

// 			$objPublicacion->fetch($GLOBALS['publicacion_idpublicacion']);
// 			$result = $objPublicacion->doformat("/areas_edit.html", $GLOBALS["TEMPLATE_ROOT"]);
			$result = $objAreas->doformat("/areas_edit.html", $GLOBALS["TEMPLATE_ROOT"]);


			return $result;

			break;

		case "delete":
			if (!$objCMSUser->checkPermission("Areas::escritura"))
				return handleevent("permission_denied");
			$objAreas->delete();
			break;

		case "store":
			if (!$objCMSUser->checkPermission("Areas::escritura"))
				return handleevent("permission_denied");
			$objAreas->store();
			break;

		case "list":
			if (!$objCMSUser->checkPermission("Areas::lectura"))
				return handleevent("permission_denied");

			$objAreas->joins = array();
			$objAreas->order_by="descripcion";
			return $objAreas->doformatall("/areas_list.html", $GLOBALS["TEMPLATE_ROOT"]);
			break;

		case "order":
			if (!$objCMSUser->checkPermission("Areas::lectura"))
				return handleevent("permission_denied");
			$objAreas->order_by = $GLOBALS["order_by"];
			break;

		case "publicacion_store":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");

			if (!$objAreas->ID()) handleevent("store");

			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;
			$objPublicacion->ID($GLOBALS['publicacion_idpublicacion']);
			$objPublicacion->field('idcontenido', $GLOBALS['publicacion_idcontenido']);
			$objPublicacion->field('idarea', $objAreas->ID());
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

		case "list_contenidos":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;
		
			$where = "1 AND areas.idarea = ".$GLOBALS['idarea'];

/*			if($GLOBALS['fecha'])     $where .= " AND fecha like '".$GLOBALS['fecha']."%'";
			if($GLOBALS['nombre'])    $where .= " AND nombre like '%".$GLOBALS['nombre']."%'";*/
	
// 			echo $where;
			$objPublicacion->where = $where;
			$objPublicacion->disableTables(array("contenidos_links","contenidos_tipos", "areas"));
			$objPublicacion->limit_from  = ($GLOBALS['contenidos_limit_from']  > 0) ? $GLOBALS['contenidos_limit_from'] : 0;
			$objPublicacion->limit_count = ($GLOBALS['contenidos_limit_count'] > 0) ? $GLOBALS['contenidos_limit_count'] : 20;
			$objPublicacion->order_by    = ($GLOBALS['contenidos_order_by']    != "") ? $GLOBALS['contenidos_order_by'] : "destacada, fecha_publicacion DESC, orden DESC";
			$json = new JSON();

// 			print_r($objForos->fetchall());
			$result = array();
			$result['contenidos'] = $objPublicacion->fetchall();
			$result['from'] = $objPublicacion->limit_from;
			$result['count'] = $objPublicacion->limit_count;
			$result['total'] = $objPublicacion->count();

                        print($json->stringify($result));
                        exit();

			break;

		case "editar_publicacion":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;
			$objPublicacion->disableTables(array("contenidos_links", "contenidos_tipos", "areas"));

			$json = new JSON();

			$result = array();
			$result['publicacion'] = $objPublicacion->fetch($GLOBALS['idpublicacion']);

                        print($json->stringify($result));
                        exit();

			break;

		case "delete_contenidos":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");

			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;

			$ids_array = split(",", $GLOBALS['ids']);
			if (count($ids_array) <= 1) 
				exit();

			$sql = "DELETE FROM publicacion WHERE idpublicacion IN (".$GLOBALS['ids'].")";

			if ($objPublicacion->db->exec($sql, 0))
			{
				echo "0";
			} else {
				echo "1";
			}
			exit();

			break;

		case "grabar_publicacion":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");
			if (!$objAreas->ID()) exit();

			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;

			if($GLOBALS['publicacion_idpublicacion']) {
				$objPublicacion->ID($GLOBALS['publicacion_idpublicacion']);
				$objPublicacion->field('idcontenido', $GLOBALS['publicacion_idcontenido']);
			} else {
				$array = split("-", $GLOBALS['publicacion_contenido_titulo']);
				$contenidos = new contenidos();
				$contenidos->db = $dbCMS;
				$contenidos->disableTables(array("contenidos_links", "publicacion", "opciones", "contenidos_tipos"));
				$contenidos->where = "titulo like '".$array[0]."'";
				$rows = $contenidos->fetchall();
				echo $rows[0]['idcontenido'];
				$objPublicacion->field('idcontenido', $rows[0]['idcontenido']);
			}
			$objPublicacion->field('idarea', $objAreas->ID());
			$objPublicacion->field('fecha_publicacion', $GLOBALS['publicacion_fecha']);
			$objPublicacion->field('destacada', $GLOBALS['publicacion_destacada']);
			$objPublicacion->field('orden', $GLOBALS['publicacion_orden']);
			$objPublicacion->store();
			echo $objPublicacion->ID();

			exit();
			break;

		case "autocompleter_contenidos":
			if (!$objCMSUser->checkPermission("Publicar::lectura"))
				return handleevent("permission_denied");

			$contenidos = new contenidos();
			$contenidos->db = $dbCMS;
			$contenidos->disableTables(array("contenidos_links", "publicacion", "opciones"));
			$contenidos->where = "titulo like '".$GLOBALS['contenido_titulo']."%'";
			$contenidos->order_by = "titulo asc";
			$contenidos->limit_count = 10;

			print($contenidos->doformatall("/autocompleter_contenidos.html", $GLOBALS["TEMPLATE_ROOT"]));
                        exit();

			break;

		case "permission_denied":
			return $objCMSUser->doformat("/permission_denied.html", $GLOBALS["TEMPLATE_ROOT"]);
			break;

		default:
			return handleevent("list");
	}
}


session_start();
if (!$HTTP_SESSION_VARS["usuarios_idusuario"]) {
	header("Location: ../home/");
	exit(0);
}
$dbCMS   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$dbCMS->connect();

$objAreas = new areas();
$objAreas->db = $dbCMS;
$objAreas->ID($idarea);
$objAreas->field('idweb', $idweb);
$objAreas->field('descripcion', $descripcion);

$objCMSUser = new usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS::ABM Areas");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);

$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
