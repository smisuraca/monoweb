<?php
/**
 * This source file is part of CEMESE, publishing software.
 *
 * Copyright (C) 2000-2006 Garcia Rodrigo.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, US
 */

require_once("../config.inc.php");

session_start();

$tplmarco = new Template("");
$tplmarco->setFileRoot($TEMPLATE_ROOT);

$tplcontent = new Template("");
$tplcontent->setFileRoot($TEMPLATE_ROOT);

$dbCMS   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$dbCMS->connect();

session_register ("usuarios_idusuario");
session_register ("login_retry");
session_register ("login_last");

$objCMSUser = new Usuarios();
$objCMSUser->db = $dbCMS;
$objCMSUser->name = "loginuser";

$username    = getvalue('username', null);
$password    = getvalue('password', null);
$login_retry = getvalue('login_retry', 0);
$usuarios_idusuario = getvalue('usuarios_idusuario', 0);

if ($usuarios_idusuario)
{
	$objCMSUser->fetch($usuarios_idusuario);
}
elseif ($username && $password)
{
	$login_error = "";
	//Si hizo 3 intentos... lo hago esperar 1 minuto...
	if ($login_retry < 3)
	{
		if ($objCMSUser->validateUser($username, $password))
		{
			$usuarios_idusuario=$objCMSUser->ID();
			//pongo en CERO los intentos
			$login_retry = 0;
		}
		else
		{
			//Aumento 1 a la cantidad de intentos que hizo de wrong username/passwd...
			$HTTP_SESSION_VARS["login_retry"]++;
			$HTTP_SESSION_VARS["login_last"] = time();
			$login_error = "invalid_credentials";
		}
	}

	if ($HTTP_SESSION_VARS["login_retry"] >= 3)
	{
		if ($HTTP_SESSION_VARS["login_retry"] == 3)
		{
			$HTTP_SESSION_VARS["login_retry"]++;
			$HTTP_SESSION_VARS["login_last"] = time();
			$login_error = "too_many_retry";
		}
		else
		{
			if (time() - $HTTP_SESSION_VARS["login_last"] > 60);
			{
				$login_error = "account_disabled";
				$HTTP_SESSION_VARS["login_retry"] = 2;
			}
		}
	}
}

if ($objCMSUser->ID() && !$logout) {
	$tplmarco->Open("/marco.html");
	$tplcontent->Open("/welcome.html");
} else {
	$usuarios_idusuario=0;
	$tplmarco->Open("/marco_login.html");
	$tplcontent->Open("/login.html");
	$tplcontent->setVar("{login.error}", $login_error);
}

$tplmarco->setVar("{marco.title}", "CMS::Home");
$tplmarco->setVar("{marco.content}", $tplcontent->Template);
$tplmarco->setVar("{system.hostname}", HOSTNAME);

$tplmarco->Template = $objCMSUser->doformat($tplmarco->Template);

print($tplmarco->Template);

$dbCMS->close();
?>
