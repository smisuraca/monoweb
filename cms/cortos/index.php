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
	global $objCMSUser;

	switch ($event) {
		case "list":
                        if (!$objCMSUser->checkPermission("Contenidos::lectura"))
                                header("Location: ../home/");

			return $objCMSUser->doformat("/cortos/index.html", $GLOBALS["TEMPLATE_ROOT"]);
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
$objCMSUser->ID($HTTP_SESSION_VARS["usuarios_idusuario"]);
$objCMSUser->fetch($HTTP_SESSION_VARS["usuarios_idusuario"]);

foreach(explode(",", $event) as $value)
	$content = handleevent($value);

$tplmarco = new Template("");
$tplmarco->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
$tplmarco->Open("/marco.html");
$tplmarco->setVar("{marco.title}", "CMS Visualizacion de Cortos");
$tplmarco->setVar("{system.hostname}", HOSTNAME);
$tplmarco->setVar("{marco.content}", $content);
$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
