<?php
require_once("../config.inc.php");
require_once("phpCache.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
require_once("class.smtp.inc.php");
require_once("cmsdb.php");
require_once("funciones_www.php");

session_start();

$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$clave   = getvalue('clave',   0, false);
$usuariosweb_usuarios_idusuario = getvalue('usuariosweb_usuarios_idusuario', '0');

if(!$usuariosweb_usuarios_idusuario) {
	echo "no se encuentra habilitado para realizar la operacion."; 
	exit();
}

if (!($et=cache_all($CACHE_HIGH)))
{
	$type = getvalue('type',  $DEFAULT_TYPE);
	$lang = getvalue('lang',  $DEFAULT_LANG);
	
	$objUsuarios = new usuarios();
	$objUsuarios->db = $db;

	if ($objUsuarios->fetch($usuariosweb_usuarios_idusuario)) {
		$tpl_  = "home_cambioclave_ok";
		$objUsuarios->field("password", $clave);
		$objUsuarios->store();

#		enviaMail($objUsuarios->ID());

	} else {
		$tpl_  = "home_cambioclave_failed";
	}

	$tplcontent = new Template("");
	$tplcontent->setFileRoot($TEMPLATE_ROOT);
	$tplcontent->Open("/${tpl_}_$lang.$type");

	$tplmarco = new Template("");
	$tplmarco->setFileRoot($TEMPLATE_ROOT);
	$tplmarco->Open("/frame_empty_$lang.$type");
	$tplmarco->setVar('{contenido}', $tplcontent->Template);
	$tplmarco->setVar('{mail}', $objUsuarios->field("email"));	

        if(!$idcontenido) {
            if($area == "Portada") {
                $area = "el portal online de Cine Independiente";
            }
            $tplmarco->setVar('{contenidos.titulo}', $area." | Cinevivo | cortos | realizadores independientes | festivales de cine | convocatorias de cortometrajes");
        }
	$tplmarco->setVar('{type}', $type);	
	$tplmarco->setVar('{tpl}', $tpl_);
	$tplmarco->setVar('{lang}', $lang);
	
	header("Content-Type: text/html;charset=iso-8859-1");
	print($tplmarco->Template);
}

function enviaMail($idusuario) {
	global $db, $SMTP;

	$objUsuario = new usuarios();
	$objUsuario->db = $db;
	$objUsuario->fetch($idusuario);
	$username = $objUsuario->field("username");
	$email = $objUsuario->field("email");

	$txtmail = "Su nueva clave es : {password}";

	$tplmail = new Template($txtmail);
/*	$tplmail->setVar('{usuario}', $username);*/
	$tplmail->setVar('{password}', $objUsuario->field("password"));

	$smtp = new smtp_class;
	$smtp->host_name = $SMTP['HOST'];
	$headers = array(
		"From: \"Cinevivo\" <noreply@cinevivo.com.ar>",
		"To: ".$email,
		"Subject: Registracion de usuario de Cinevivo.com.ar"
	);
	$body = $tplmail->Template;
	$smtp->SendMessage('noreply@cinevivo.com.ar', array($email), $headers, $body);
}


$db->close();
print("<!-- left time: $et sec.-->");
?>
