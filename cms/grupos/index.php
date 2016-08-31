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
	global $objGrupos, $objCMSUser;

	switch ($event) {
		case "edit":
			if (!$objCMSUser->checkPermission("Grupos::lectura"))
				return handleevent("permission_denied");

			$objGrupos->joins[permisos]->order_by = 'permisos.descripcion';
			$objGrupos->fetch($objGrupos->ID());

			$objPermisos = new permisos();
			$objPermisos->db = $objGrupos->db;
			$objPermisos->order_by = "descripcion";

			$objUsuarios = new usuarios();
			$objUsuarios->db = $objGrupos->db;
			$objUsuarios->order_by = "username";

			$result = $objGrupos->doformat("/grupos_edit.html", $GLOBALS["TEMPLATE_ROOT"]);
			$result = $objUsuarios->doformatall($result);
			$result = $objPermisos->doformatall($result);
	
			return $result;
			break;

		case "delete":
			if (!$objCMSUser->checkPermission("Grupos::escritura"))
				return handleevent("permission_denied");

			$objGrupos->delete();
			break;

		case "store":
			if (!$objCMSUser->checkPermission("Grupos::escritura"))
				return handleevent("permission_denied");

			$objGrupos->store();
			break;

		case "list":
			if (!$objCMSUser->checkPermission("Grupos::lectura"))
				return handleevent("permission_denied");

			$objGrupos->disableTables(array('grupos', 'permisos'));
			$objGrupos->order_by = "nombre";
			return $objGrupos->doformatall("/grupos_list.html", $GLOBALS["TEMPLATE_ROOT"]);
			break;

		case "permiso_store":
			if (!$objCMSUser->checkPermission("Grupos::escritura"))
				return handleevent("permission_denied");

			if (!$objGrupos->ID()) handleevent("store");

			$objGrupos_Permisos = new grupos_permisos();
			$objGrupos_Permisos->db = $objGrupos->db;
			$objGrupos_Permisos->field("idgrupo", $objGrupos->ID());
			$objGrupos_Permisos->field("idpermiso", $GLOBALS["idpermiso"]);
			$objGrupos_Permisos->store();
			break;

		case "permiso_delete":
			if (!$objCMSUser->checkPermission("Grupos::escritura"))
				return handleevent("permission_denied");

			$objGrupos_Permisos = new grupos_permisos();
			$objGrupos_Permisos->db = $objGrupos->db;
			$objGrupos_Permisos->ID($GLOBALS["idgrupo_permiso"]);
			$objGrupos_Permisos->delete();
			break;

		case "usuario_store":
			if (!$objCMSUser->checkPermission("Grupos::escritura"))
				return handleevent("permission_denied");
			
			if (!$objGrupos->ID()) handleevent("store");
			
			$objUsuarios_Grupos = new usuarios_grupos();
			$objUsuarios_Grupos->db = $objGrupos->db;
			$objUsuarios_Grupos->field("idgrupo", $objGrupos->ID());
			$objUsuarios_Grupos->field("idusuario", $GLOBALS["idusuario"]);
			$objUsuarios_Grupos->store();
			break;

		case "usuario_delete":
			if (!$objCMSUser->checkPermission("Grupos::escritura"))
				return handleevent("permission_denied");

			$objUsuarios_Grupos = new usuarios_grupos();
			$objUsuarios_Grupos->db = $objGrupos->db;
			$objUsuarios_Grupos->ID($GLOBALS["idusuario_grupo"]);
			$objUsuarios_Grupos->delete();
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

$objGrupos = new grupos();
$objGrupos->db = $dbCMS;
$objGrupos->ID($idgrupo);
$objGrupos->field("nombre", $nombre);

$objCMSUser = new usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->ID($HTTP_SESSION_VARS["usuarios_idusuario"]);
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS::ABM Grupos");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);
$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
