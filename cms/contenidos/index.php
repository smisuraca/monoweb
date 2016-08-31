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


function handleevent($event)
{
	global $objContenidos, $objCMSUser, $dbCMS;

	switch ($event)
	{
		case "edit":
			if (!$objCMSUser->checkPermission("Contenidos::lectura"))
				return handleevent("permission_denied");

			$objContenidos->fetch($objContenidos->ID());

			$objContenidos_tipos = new contenidos_tipos();
			$objContenidos_tipos->db = $dbCMS;
			$objContenidos_tipos->order_by=" descripcion ";

			$objMmedia_tipos = new links_tipos();
			$objMmedia_tipos->db = $dbCMS;
			$objMmedia_tipos->order_by=" descripcion ";
			$objMmedia_tipos->disableTables(array('links'));

			$sql = "SELECT contenidos_links.id as idcl FROM contenidos_links, links, links_tipos WHERE contenidos_links.idlink = links.idlink AND links.idlink_tipo = links_tipos.idlink_tipo AND contenidos_links.idcontenido=".$objContenidos->ID()." AND links_tipos.descripcion LIKE 'Cortometraje::url-video'";
			$idcl = 0;
			if ($dbCMS->exec($sql, 0)) {
				$dbCMS->getrow();

				$idcl = $dbCMS->Fields["idcl"];

			}
			$objvideo = new contenidos_links();
			$objvideo->db = $dbCMS;
			$objvideo->disableTables(array('contenidos'));
			$objvideo->where="contenidos_links.id = $idcl";

			$objAreas = new areas();
			$objAreas->db = $dbCMS;
			$objAreas->order_by = 'descripcion';
			$objAreas->disableTables(array('publicacion'));

			$result = $objContenidos_tipos->doformatall("/contenidos_edit.html", $GLOBALS["TEMPLATE_ROOT"]);
			$result = $objAreas->doformatall($result);
			$result = $objMmedia_tipos->doformatall($result);
			$result = $objContenidos->doformat($result);
			$result = $objvideo->doformatall($result);
			$result = $objvideo->doformatall($result);

			return $result;
			break;

		case "delete":
			if (!$objCMSUser->checkPermission("Contenidos::escritura"))
				return handleevent("permission_denied");
			$objContenidos->delete();
			break;

		case "store":
			if (!$objCMSUser->checkPermission("Contenidos::escritura"))
				return handleevent("permission_denied");

			$objContenidos->store();
			break;

		case "list":
			if (!$objCMSUser->checkPermission("Contenidos::lectura"))
				return handleevent("permission_denied");

			$objContenidos_tipos = new contenidos_tipos();
			$objContenidos_tipos->db = $dbCMS;
			$objContenidos_tipos->order_by=" descripcion ";

			if($objContenidos->where == "1" || !$objContenidos->where ) $objContenidos->where = "1=0";
			$result = $objContenidos->doformatall("/contenidos_list.html", $GLOBALS["TEMPLATE_ROOT"]);
			return $objContenidos_tipos->doformatall($result);

			break;

		case "filter":
			$objContenidos->where = "1";
			if ($objContenidos->field('idcontenido')) $objContenidos->where.=" AND contenidos.idcontenido=".$objContenidos->values['idcontenido'];
			if ($objContenidos->field('idcontenido_tipo')) $objContenidos->where.=" AND contenidos.idcontenido_tipo=".$objContenidos->values['idcontenido_tipo'];
			if ($GLOBALS["fecha"].$GLOBALS["hora"]) $objContenidos->where.=" AND contenidos.fecha='".$GLOBALS["fecha"].$GLOBALS["hora"]."'";
			if ($objContenidos->field('titulo')) $objContenidos->where.=" AND contenidos.titulo RLIKE '".$objContenidos->values['titulo']."'";
			if ($objContenidos->field('texto')) $objContenidos->where.=" AND contenidos.texto RLIKE '".$objContenidos->values['texto']."'";
			if ($objContenidos->field('comentarios')) $objContenidos->where.=" AND contenidos.comentarios='".$objContenidos->values['comentarios']."'"; 
			break;

		case "order":
			$objContenidos->order_by = $GLOBALS["order_by"];
			break;

		//************************************
		// Eventos para manejo de opciones
		//************************************
		case "opcion_store":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");
			if (!$objContenidos->ID()) handleevent("store");
			$objOpciones = new opciones();
			$objOpciones->db = $dbCMS;
			$objOpciones->ID($GLOBALS['opcion_idopcion']);
			$objOpciones->field('descripcion', $GLOBALS['opcion_descripcion']);
			$objOpciones->field('idcontenido', $objContenidos->ID());
			$objOpciones->field('opcion_correcta', $GLOBALS['opcion_correcta']);
			$objOpciones->store();
			break;


		case "opcion_delete":
			if (!$objCMSUser->checkPermission("Contenidos::escritura"))
				return handleevent("permission_denied");
			$objOpciones = new opciones();
			$objOpciones->db = $dbCMS;
			$objOpciones->ID($GLOBALS['opcion_idopcion']);
			$objOpciones->delete();
			break;

        //************************************
        // Eventos para publicacion
        //************************************
		case "publicacion_store":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");
			if (!$objContenidos->ID()) handleevent("store");
			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;
			$objPublicacion->ID($GLOBALS['publicacion_idpublicacion']);
			$objPublicacion->field('idcontenido', $objContenidos->ID());
			$objPublicacion->field('idarea', $GLOBALS['publicacion_idarea']);
			$objPublicacion->field('fecha_publicacion', $GLOBALS['publicacion_fecha']);
			$objPublicacion->field('destacada', $GLOBALS['publicacion_destacada']);
			$objPublicacion->field('orden', $GLOBALS['publicacion_orden']);
			$objPublicacion->store();
			break;

		case "publicacion_delete":
			if (!$objCMSUser->checkPermission("Publicar::escritura"))
				return handleevent("permission_denied");
			$objPublicacion = new publicacion();
			$objPublicacion->db = $dbCMS;
			$objPublicacion->ID($GLOBALS['publicacion_idpublicacion']);
			$objPublicacion->delete();
			break;

        //************************************
        // Eventos para links (mmedia)
        //************************************
		case "mmedia_store":
			if (!$objCMSUser->checkPermission("MMedias::escritura"))
				return handleevent("permission_denied");

			if (!$objContenidos->ID()) handleevent("store");

			$objMMedia = new links();
			$objMMedia->db = $dbCMS;
			$objMMedia->ID($GLOBALS['mmedia_idlink']);
			$objMMedia->field('idlink_tipo', $GLOBALS['mmedia_idtipo']);
			$objMMedia->field('titulo', ($GLOBALS['mmedia_titulo'] ? $GLOBALS['mmedia_titulo'] : $GLOBALS['mmedia_file_name']));
			$objMMedia->field('url', $GLOBALS['mmedia_url']);
			$objMMedia->field('orden', $GLOBALS['mmedia_orden']);
			$objMMedia->field('file', $GLOBALS['mmedia_file']);
			$objMMedia->field('uploadpath', $GLOBALS['app_uploadpath']);
			$objMMedia->field('ftype', ($GLOBALS['mmedia_file'] ? $GLOBALS['mmedia_file_type'] : $GLOBALS['mmedia_ftype']));
			$objMMedia->field('fname', ($GLOBALS['mmedia_file'] ? $GLOBALS['mmedia_file_name'] : $GLOBALS['mmedia_fname']));
			$objMMedia->store();

			if (!$GLOBALS['mmedia_id'])
			{
				$objCMMedia = new contenidos_links();
				$objCMMedia->db = $dbCMS;
				$objCMMedia->field('idcontenido', $objContenidos->ID());
				$objCMMedia->field('idlink', $objMMedia->ID());
				$objCMMedia->store();
			}

			break;

		case "mmedia_delete":
			if (!$objCMSUser->checkPermission("MMedias::escritura"))
				return handleevent("permission_denied");
			$objMMedia = new links();
			$objMMedia->db = $dbCMS;
			$objMMedia->ID($GLOBALS['mmedia_idlink']);
			$objMMedia->field('uploadpath', $GLOBALS['app_uploadpath']);
			$objMMedia->delete();
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

$objCMSUser = new usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

$objContenidos = new contenidos();
$objContenidos->db = $dbCMS;
$objContenidos->ID($idcontenido);
$objContenidos->field('idcontenido_tipo', $idcontenido_tipo);
$objContenidos->field('fecha', $fecha);
$objContenidos->field('titulo', $titulo);
$objContenidos->field('titulo_corto', $titulo_corto);
$objContenidos->field('volanta', $volanta);
$objContenidos->field('bajada', $bajada);
$objContenidos->field('texto', $texto);
$objContenidos->field('extra1', $extra1);
$objContenidos->field('extra2', $extra2);
$objContenidos->field('extra3', $extra3);
$objContenidos->field('extra4', $extra4);
$objContenidos->field('comentarios', $comentarios);

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS::ABM Contenidos");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);

$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
