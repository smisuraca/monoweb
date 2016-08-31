<?php
require_once("../config.inc.php");
require_once("phpCache.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
require_once("cmsdb.php");
require_once("funciones_www.php");

session_start();

session_register ("usuarios_idusuario");
session_register ("login_retry");
session_register ("login_last");

$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$usuariosweb_username    = getvalue('usuariosweb_username', 0, true);
$usuariosweb_login_retry = getvalue('usuariosweb_login_retry', 0, false);
$usuariosweb_usuarios_idusuario = getvalue('usuariosweb_idusuario', 0, true);

if (!($et=cache_all($CACHE_HIGH)))
{
	$type = getvalue('type',  $DEFAULT_TYPE);
	$lang = getvalue('lang',  $DEFAULT_LANG);
	$tpl  = getvalue('tpl',   "home_encuesta_view", false);
	

	$objCMSUser = new Usuarios();
	$objCMSUser->db = $db;
	$objCMSUser->name = "loginuser";
	
	
	if ($usuariosweb_usuarios_idusuario) {
		$objCMSUser->fetch($usuariosweb_usuarios_idusuario);
	} elseif ($usuariosweb_username && $usuariosweb_password) {
		
		$login_error = "";
		//Si hizo 3 intentos... lo hago esperar 1 minuto...
		if ($login_retry < 3)
		{
			if ($objCMSUser->validateUser($usuariosweb_username, $usuariosweb_password))
			{
				$usuariosweb_usuarios_idusuario=$objCMSUser->ID();
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
		echo "bienvenido";
	} else {
		if ($logout) {
			$usuariosweb_usuarios_idusuario=0;
			$usuariosweb_username=0;
			echo "la sesión se ha cerrado";
		} else {
			$usuariosweb_usuarios_idusuario=0;
			$usuariosweb_username=0;
			echo "error, verifique sus datos";
		}
	}

// 	$tplcontent = new Template("");
// 	$tplcontent->setFileRoot($TEMPLATE_ROOT);
// 	$tplcontent->Open("/${tpl}_$lang.$type");
// 
// 	$tplmarco = new Template("");
// 	$tplmarco->setFileRoot($TEMPLATE_ROOT);
// 	$tplmarco->Open("/frame_empty_$lang.$type");
// 	$tplmarco->setVar('{contenido}', $tplcontent->Template);
// 
// 	$tplmarco->setVar('{type}', $type);	
// 	$tplmarco->setVar('{tpl}', $tpl);
// 	$tplmarco->setVar('{lang}', $lang);
// 	
// 	$tplmarco->Template = Encuesta_view($db, $idcontenido, $tplmarco->Template);

/*	header("Content-Type: text/html;charset=iso-8859-1");
	print($tplmarco->Template);*/
}

$db->close();
// print("<!-- left time: $et sec.-->");
?>
