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

function handleevent($event) 
{
	global  $objPermisos, $objCMSUser;

	switch ($event) 
	{
		case "edit":
			if (!$objCMSUser->checkPermission("Permisos::lectura"))
				return handleevent("permission_denied");

			$objPermisos->fetch($objPermisos->ID());
			
			$objGrupos = new grupos();
			$objGrupos->db = $objPermisos->db;
			$objGrupos->order_by = "nombre";
			
			$result= $objPermisos->doformat("/permisos_edit.html", $GLOBALS["TEMPLATE_ROOT"]);
			$result = $objGrupos->doformatall($result);
			return $result;
			break;

		case "delete":
			if (!$objCMSUser->checkPermission("Permisos::escritura"))
				return handleevent("permission_denied");
			$objPermisos->delete();
			break;

		case "store":
			if (!$objCMSUser->checkPermission("Permisos::escritura"))
				return handleevent("permission_denied");
			$objPermisos->store();
			break;
			
		case "list":
			if (!$objCMSUser->checkPermission("Permisos::lectura"))
				return handleevent("permission_denied");

			$objPermisos->disableTables(array('grupos_permisos'));
			$objPermisos->order_by = "descripcion";
			return $objPermisos->doformatall("/permisos_list.html", $GLOBALS["TEMPLATE_ROOT"]);
			break;

		case "grupo_store":
			if (!$objCMSUser->checkPermission("Permisos::escritura"))
				return handleevent("permission_denied");
			
			if (!$objPermisos->ID()) handleevent("store");

			$objGrupos_Permisos = new grupos_permisos();
			$objGrupos_Permisos->db = $objPermisos->db;
			$objGrupos_Permisos->debug = 1;
			$objGrupos_Permisos->field("idgrupo",  $GLOBALS["idgrupo"]);
			$objGrupos_Permisos->field("idpermiso",  $objPermisos->ID());
			$objGrupos_Permisos->store();
			break;

		case "grupo_delete":
			if (!$objCMSUser->checkPermission("Permisos::escritura"))
				return handleevent("permission_denied");

			$objGrupos_Permisos = new grupos_permisos();
			$objGrupos_Permisos->db = $objPermisos->db;
			$objGrupos_Permisos->ID($GLOBALS["idgrupo_permiso"]);
			$objGrupos_Permisos->delete();
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

$objPermisos = new permisos();
$objPermisos->db = $dbCMS;
$objPermisos->ID($idpermiso);
$objPermisos->field('nombre', $nombre);
$objPermisos->field('descripcion', $descripcion);

$objCMSUser = new usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS::ABM Permisos");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);

$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
