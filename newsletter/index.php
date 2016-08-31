<?php
require_once("../config.inc.php");
//require_once("phpCache.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
//require_once("class.smtp.inc.php");
require_once("cmsdb.php");
require_once("funciones_www.php");
require_once("class.phpmailer.php");

session_start();

$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$username = getvalue('username', 0, false);
$nombre   = getvalue('nombre',   0, false);
$apellido = getvalue('apellido', 0, false);
$mail     = getvalue('mail',     0, false);

//if (!($et=cache_all($CACHE_HIGH)))
//{
	$type = getvalue('type',  $DEFAULT_TYPE);
	$lang = getvalue('lang',  $DEFAULT_LANG);
	
	$objUsuarios = new usuarios();
	$objUsuarios->db = $db;
	$objUsuarios->where="email like '".$mail."'";
	if($objUsuarios->fetchall() || (!$nombre) || (!$apellido) || (!$mail)) {
		$tpl_  = "home_registro_failed";
	} else {
		$tpl_  = "home_registro_ok";

		$objUsuarios->ID('');
		$objUsuarios->field("username", $username);
		$objUsuarios->field("password", substr(md5(date("Y-m-d h:i:s")),0,8));
		$objUsuarios->field("email", $mail);
		$objUsuarios->field("nombre", $nombre);
		$objUsuarios->field("apellido", $apellido);
		$objUsuarios->store();

// 		if($objUsuarios->ID()) {
		enviaMail($objUsuarios->ID());
// 		}

	}
	$tplcontent = new Template("");
	$tplcontent->setFileRoot($TEMPLATE_ROOT);
	$tplcontent->Open("/${tpl_}_$lang.$type");

	$tplmarco = new Template("");
	$tplmarco->setFileRoot($TEMPLATE_ROOT);
	$tplmarco->Open("/frame_empty_$lang.$type");
	$tplmarco->setVar('{contenido}', $tplcontent->Template);
	$tplmarco->setVar('{mail}', $mail);	

        if(!$idcontenido)
            $tplmarco->setVar('{contenidos.titulo}', "Cinevivo - el portal online de cine independiente - ".$area);
	$tplmarco->setVar('{type}', $type);	
	$tplmarco->setVar('{tpl}', $tpl_);
	$tplmarco->setVar('{lang}', $lang);
	
	header("Content-Type: text/html;charset=iso-8859-1");
	print($tplmarco->Template);
//}

function enviaMail($idusuario) {
	global $db, $SMTP;

	$objUsuario = new usuarios();
	$objUsuario->db = $db;
	$objUsuario->fetch($idusuario);
	$username = $objUsuario->field("username");
	$email = $objUsuario->field("email");

	$objContenido = new contenidos();
	$objContenido->db = $db;
	$objContenido->where = "contenidos_tipos.descripcion like 'Cinevivo::Mailregistro::newsletter'";
	$txtmail = $objContenido->fetchall();

	$tplmail = new Template($txtmail[0]['texto']);
	$tplmail->setVar('{usuario}', $username);
// 	$tplmail->setVar('{password}', $objUsuario->field("password"));

/*	$smtp = new smtp_class;
	$smtp->host_name = $SMTP['HOST'];
	$headers = array(
		"From: \"Cinevivo\" <noreply@cinevivo.com.ar>",
		"To: ".$email,
		"Subject: Registracion de usuario de Cinevivo.com.ar"
	);*/
	$body = $tplmail->Template;
//	$smtp->SendMessage('noreply@cinevivo.com.ar', array($email), $headers, $body);

	$mail = new PHPMailer();

	$mail->From = "no-reply@cinevivo.com.ar";
	$mail->FromName = "Cinevivo";
	$mail->AddAddress($email);
	$mail->Subject = "Registracion de usuario de Cinevivo.com.ar";
	$mail->Body = $body;
	$mail->IsHTML (true);

	$mail->IsSMTP();
	$mail->Host = 'smtp.cinevivo.org';
	$mail->Port = 25;
	$mail->SMTPAuth = true;
	$mail->Username = 'info@cinevivo.org';
	$mail->Password = 'elportalonline';

	$mail->Send();
/*	if(!$mail->Send()) {
    	    echo 'Error: ' . $mail->ErrorInfo;
	}
	else {
	        echo 'Mail enviado!';
	}*/
}

$db->close();
print("<!-- left time: $et sec.-->");
?>
