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
	global $objUsuarios, $objCMSUser;

	switch ($event) {
		case "edit":
			if (!$objCMSUser->checkPermission("Usuarios::lectura"))
				return handleevent("permission_denied");

			$objUsuarios->fetch($objUsuarios->ID());

			$objGrupos = new grupos();
			$objGrupos->db = $objUsuarios->db;
			$objGrupos->order_by = "nombre";
			
			$result = $objUsuarios->doformat("/usuarios_edit.html",  $GLOBALS["TEMPLATE_ROOT"]);
			$result = $objGrupos->doformatall($result);

			return $result;
			break;

		case "list":
			if (!$objCMSUser->checkPermission("Usuarios::lectura"))
				return handleevent("permission_denied");

			$objUsuarios->order_by = "apellido, nombre";
			$objUsuarios->disableTables(array('usuarios_grupos'));
			if($objUsuarios->where == "1" || !$objUsuarios->where ) $objUsuarios->where = "1=0";
			return $objUsuarios->doformatall("/usuarios_list.html", $GLOBALS["TEMPLATE_ROOT"]);
			break;

		case "filter":
			$objUsuarios->where = "1";
			if ($objUsuarios->field('idusuario')) $objUsuarios->where.=" AND usuarios.idusuario=".$objUsuarios->values['idusuario'];
			if ($GLOBALS['user']) $objUsuarios->where.=" AND usuarios.username RLIKE '".$GLOBALS['user']."'";
			if ($objUsuarios->field('email')) $objUsuarios->where.=" AND usuarios.email RLIKE '".$objUsuarios->values['email']."'";
			if ($objUsuarios->field('nombre')) $objUsuarios->where.=" AND usuarios.nombre RLIKE '".$objUsuarios->values['nombre']."'";
			if ($objUsuarios->field('apellido')) $objUsuarios->where.=" AND usuarios.apellido='".$objUsuarios->values['apellido']."'";
			break;

		case "delete":
			if (!$objCMSUser->checkPermission("Usuarios::escritura"))
				return handleevent("permission_denied");
			$objUsuarios->delete();
			break;

		case "store":
			if (!$objCMSUser->checkPermission("Usuarios::escritura")) 
				return handleevent("permission_denied");
			$objUsuarios->store();
			break;

		case "grupo_store":
			if (!$objCMSUser->checkPermission("Usuarios::escritura"))
				return handleevent("permission_denied");

			if (!$objUsuarios->ID()) handleevent("grabar");

			$objUsuarios_Grupos = new usuarios_grupos();
			$objUsuarios_Grupos->db = $objUsuarios->db;
			$objUsuarios_Grupos->field("idgrupo",  $GLOBALS["idgrupo"]);
			$objUsuarios_Grupos->field("idusuario", $objUsuarios->ID());
			$objUsuarios_Grupos->store();
			break;

		case "grupo_delete":
			if (!$objCMSUser->checkPermission("Usuarios::escritura"))
				return handleevent("permission_denied");

			$objUsuarios_Grupos = new usuarios_grupos();
			$objUsuarios_Grupos->db = $objUsuarios->db;
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

$objUsuarios = new usuarios();
$objUsuarios->db = $dbCMS;
$objUsuarios->ID($idusuario);
$objUsuarios->field("username", $username);
$objUsuarios->field("password", $password);
$objUsuarios->field("shell", $shell);
$objUsuarios->field("email", $email);
$objUsuarios->field("nombre", $nombre);
$objUsuarios->field("apellido", $apellido);
$objUsuarios->field("habilitado", $habilitado);
$objUsuarios->field("telefono", $telefono);
$objUsuarios->field("documento", $documento);

$objCMSUser = new usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS::ABM Usuarios");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);
$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();


?>
