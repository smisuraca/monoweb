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

require_once("JSON.php");
require_once("JSON_UTF.php");


function handleevent($event)
{
	global $objForos, $objCMSUser, $dbCMS;

	switch ($event)
	{
		case "show":
			if (!$objCMSUser->checkPermission("Contenidos::lectura"))
				return handleevent("permission_denied");

// 			$contenidos_tipos = new contenidos_tipos();
// 			$contenidos_tipos->db = $dbCMS;
// 			$contenidos_tipos->order_by=" descripcion ";

			$tpl = new Template("");
			$tpl->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
			$tpl->Open("/comentarios.html");
			$tpl->setVar("{date}", date("Y-m-d"));

			return $tpl->Template;
			break;

// 		case "edit":
// 			if (!$objCMSUser->checkPermission("Foros::lectura"))
// 				return handleevent("permission_denied");
// 
// 			$objForos->fetch($objForos->ID());
// 
// 			$objForos_tipos = new foros_tipos();
// 			$objForos_tipos->db = $dbCMS;
// 			$objForos_tipos->order_by=" descripcion ";
// 
// 			$objMmedia_tipos = new links_tipos();
// 			$objMmedia_tipos->db = $dbCMS;
// 			$objMmedia_tipos->order_by=" descripcion ";
// 			$objMmedia_tipos->disableTables(array('links'));
// 
// 			$objAreas = new areas();
// 			$objAreas->db = $dbCMS;
// 			$objAreas->order_by = 'descripcion';
// 			$objAreas->disableTables(array('publicacion'));
// 
// 			$result = $objForos_tipos->doformatall("/foros_edit.html", $GLOBALS["TEMPLATE_ROOT"]);
// 			$result = $objAreas->doformatall($result);
// 			$result = $objMmedia_tipos->doformatall($result);
// 			$result = $objForos->doformat($result);
// 
// 			return $result;
// 			break;
// 
// 		case "delete":
// 			if (!$objCMSUser->checkPermission("Foros::escritura"))
// 				return handleevent("permission_denied");
// 			$objForos->delete();
// 			break;
// 
// 		case "store":
// 			if (!$objCMSUser->checkPermission("Foros::escritura"))
// 				return handleevent("permission_denied");
// 			$objForos->store();
// 			break;
// 
		case "list":
			if (!$objCMSUser->checkPermission("Contenidos::lectura"))
				return handleevent("permission_denied");
		
			$where = "1 ";

			if($GLOBALS['fecha'])     $where .= " AND foros.fecha like '".$GLOBALS['fecha']."%'";
			if($GLOBALS['nombre'])    $where .= " AND foros.nombre like '%".$GLOBALS['nombre']."%'";
	
// 			echo $where;
			$objForos->where = $where;
			$objForos->disableTables(array("contenidos_links","publicacion"));
			$objForos->limit_from  = ($GLOBALS['comentarios_limit_from']  > 0) ? $GLOBALS['comentarios_limit_from'] : 0;
			$objForos->limit_count = ($GLOBALS['comentarios_limit_count'] > 0) ? $GLOBALS['comentarios_limit_count'] : 10;
			$objForos->order_by    = ($GLOBALS['comentarios_order_by']    > 0) ? $GLOBALS['comentarios_order_by'] : "foros.fecha desc";
			$json = new JSON();

// 			print_r($objForos->fetchall());
			$result = array();
			$result['comentarios'] = $objForos->fetchall();
			$result['from'] = $objForos->limit_from;
			$result['count'] = $objForos->limit_count;
			$result['total'] = $objForos->count();

                        print($json->stringify($result));
                        exit();

			break;

		case "delete":
			if (!$objCMSUser->checkPermission("Contenidos::escritura"))
				return handleevent("permission_denied");

			$ids_array = split(",", $GLOBALS['ids']);
			if (count($ids_array) <= 1) 
				exit();

			$sql = "DELETE FROM foros WHERE idforo IN (".$GLOBALS['ids'].")";

			if ($objForos->db->exec($sql, 0))
			{
				echo "0";
			} else {
				echo "1";
			}
			exit();

			break;
// 		case "filter":
// 			$objForos->where = "1";
// 			if ($objForos->field('idcontenido')) $objForos->where.=" AND foros.idcontenido=".$objForos->values['idcontenido'];
// 			if ($objForos->field('idcontenido_tipo')) $objForos->where.=" AND foros.idcontenido_tipo=".$objForos->values['idcontenido_tipo'];
// 			if ($GLOBALS["fecha"].$GLOBALS["hora"]) $objForos->where.=" AND foros.fecha='".$GLOBALS["fecha"].$GLOBALS["hora"]."'";
// 			if ($objForos->field('titulo')) $objForos->where.=" AND foros.titulo RLIKE '".$objForos->values['titulo']."'";
// 			if ($objForos->field('texto')) $objForos->where.=" AND foros.texto RLIKE '".$objForos->values['texto']."'";
// 			if ($objForos->field('comentarios')) $objForos->where.=" AND foros.comentarios='".$objForos->values['comentarios']."'"; 
// 			break;
// 
// 		case "order":
// 			$objForos->order_by = $GLOBALS["order_by"];
// 			break;


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

$objCMSUser = new usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

$objForos = new comentarios();
$objForos->db = $dbCMS;

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS::ABM Foros");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);


$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
