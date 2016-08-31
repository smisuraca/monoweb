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

function handleevent($event) {

    global $objContenidos_tipos, $objCMSUser;
    
    switch ($event) {
        case "form":
			if (!$objCMSUser->checkPermission("tipos_contenidos_lectura")) {
				header("Location: ../home/index.php");
				exit(0);
			}
            $objContenidos_tipos->getvalue($objContenidos_tipos->idcontenido_tipo);
            return $objContenidos_tipos->doformat("/contenidos_tipos/form_abm.html");
            break;
        case "borrar":
			if (!$objCMSUser->checkPermission("tipos_contenidos_borrar")) {
				header("Location: ../home/index.php");
				exit(0);
			}
            $objContenidos_tipos->doabm($event);
            return handleevent("list", $objContenidos_tipos, $objCMSUser, $objCMSPermisos);
            break;
        case "grabar":
			if ($objContenidos_tipos->idcontenido_tipo ) {$permiso = "tipos_contenidos_modificar";}else{$permiso = "tipos_contenidos_agregar";}
			if (!$objCMSUser->checkPermission($permiso)) {
				header("Location: ../home/index.php");
				exit(0);
			}
            $objContenidos_tipos->doabm($event);
            return handleevent("list", $objContenidos_tipos, $objCMSUser, $objCMSPermisos);
            break;
        case "list":
				if (!$objCMSUser->checkPermission($objCMSUser->idusuario, "tipos_contenidos_lectura")) {
				header("Location: ../home/index.php");
				exit(0);
			}
            return $objContenidos_tipos->doformatall("/contenidos_tipos/list.html");
            break;
		case "ordenar":
			if (!$objCMSUser->checkPermission("tipos_contenidos_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objContenidos_tipos->order_by = $GLOBALS["order_by"];
     		return handleevent("list", $objContenidos_tipos,  $objCMSUser, $objCMSPermisos);
            break;
        default:
     		return handleevent("list", $objContenidos_tipos, $objCMSUser, $objCMSPermisos);
    }
}

session_start();
if (!$HTTP_SESSION_VARS["usuarios_idusuario"]) {
	header("Location: ../home/");
	exit(0);
}
$dbCMS   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$dbCMS->connect();

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");

$objCMSUser       = new usuarios();
$objCMSUser->db   = $dbCMS;
$objCMSUser->name = 'loginuser';
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

$objCMSPermisos = new permisos($dbCMS);

$objContenidos_tipos = new contenidos_tipos($dbCMS, $idcontenido_tipo, $descripcion,
                                                $extra1_etiqueta, $extra1_valores,
                                                $extra2_etiqueta, $extra2_valores,
                                                $extra3_etiqueta, $extra3_valores);
$objContenidos_tipos->where     = urldecode(stripslashes($HTTP_COOKIE_VARS["contenidos_tipos_where"]));
$objContenidos_tipos->order_by  = urldecode(stripslashes($HTTP_COOKIE_VARS["contenidos_tipos_order_by"]));

$tplmarco->setVar("{titulo}", "ABM Tipos de Contenidos");
$tplmarco->setVar("{base}", $Db["CMS"]);
$tplmarco->setVar("{contenido}", handleevent($event, $objContenidos_tipos, $objCMSUser, $objCMSPermisos));


setcookie("contenidos_tipos_where", $objContenidos_tipos->where, 0, "/");
setcookie("contenidos_tipos_order_by", $objContenidos_tipos->order_by, 0, "/");

print($tplmarco->Template);
$dbCMS->close();

?>