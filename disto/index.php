<?php
require_once("../config.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
//require_once("class.smtp.inc.php");
require_once("cmsdb.php");
require_once("funciones_www.php");
require_once("class.phpmailer.php");

session_start();

$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$event = htmlspecialchars(utf8_decode(getvalue('event', '', false)), ENT_QUOTES);
$idficha = htmlspecialchars(utf8_decode(getvalue('idficha', 0, false)), ENT_QUOTES);

$titulo = htmlspecialchars(utf8_decode(getvalue('titulo', '', false)), ENT_QUOTES);
$director_nombre = htmlspecialchars(utf8_decode(getvalue('director_nombre', '', false)), ENT_QUOTES);
$director_mail = htmlspecialchars(utf8_decode(getvalue('director_mail', '', false)), ENT_QUOTES);
$direccion = htmlspecialchars(utf8_decode(getvalue('direccion', '', false)), ENT_QUOTES);

$type = getvalue('type',  $DEFAULT_TYPE);
$lang = getvalue('lang',  $DEFAULT_LANG);

if($event == "cargar_foto") {

	$obj = new ficha();
	$obj->db = $db;

	$obj->fetch($idficha);

	$objMMedia = new links();
	$objMMedia->db = $db;
	$objMMedia->ID($obj->values['image_gif']);
	$objMMedia->field('idlink_tipo', 44);
	$objMMedia->field('titulo', "");
	$objMMedia->field('url', "");
	$objMMedia->field('orden', 0);
	$objMMedia->field('file', $GLOBALS['mmedia_file']);
	$objMMedia->field('uploadpath', $GLOBALS['app_uploadpath']);
	$objMMedia->field('ftype', ($GLOBALS['mmedia_file'] ? $GLOBALS['mmedia_file_type'] : $GLOBALS['mmedia_ftype']));
	$objMMedia->field('fname', ($GLOBALS['mmedia_file'] ? $GLOBALS['mmedia_file_name'] : $GLOBALS['mmedia_fname']));
	$objMMedia->store();
	$obj->field('image_gif', $objMMedia->ID());
	
	$objMMedia = new links();
	$objMMedia->db = $db;
	$objMMedia->ID($obj->values['image_gif_director']);
	$objMMedia->field('idlink_tipo', 44);
	$objMMedia->field('titulo', "");
	$objMMedia->field('url', "");
	$objMMedia->field('orden', 0);
	$objMMedia->field('file', $GLOBALS['mmedia_file_director']);
	$objMMedia->field('uploadpath', $GLOBALS['app_uploadpath']);
	$objMMedia->field('ftype', ($GLOBALS['mmedia_file_director'] ? $GLOBALS['mmedia_file_director_type'] : $GLOBALS['mmedia_ftype']));
	$objMMedia->field('fname', ($GLOBALS['mmedia_file_director'] ? $GLOBALS['mmedia_file_director_name'] : $GLOBALS['mmedia_fname']));
	$objMMedia->store();
	$obj->field('image_gif_director', $objMMedia->ID());

	$objMMedia = new links();
	$objMMedia->db = $db;
	$objMMedia->ID($obj->values['image_gif_poster']);
	$objMMedia->field('idlink_tipo', 44);
	$objMMedia->field('titulo', "");
	$objMMedia->field('url', "");
	$objMMedia->field('orden', 0);
	$objMMedia->field('file', $GLOBALS['mmedia_file_poster']);
	$objMMedia->field('uploadpath', $GLOBALS['app_uploadpath']);
	$objMMedia->field('ftype', ($GLOBALS['mmedia_file_poster'] ? $GLOBALS['mmedia_file_poster_type'] : $GLOBALS['mmedia_ftype']));
	$objMMedia->field('fname', ($GLOBALS['mmedia_file_poster'] ? $GLOBALS['mmedia_file_poster_name'] : $GLOBALS['mmedia_fname']));
	$objMMedia->store();
	$obj->field('image_gif_poster', $objMMedia->ID());


	$obj->store();
	if($obj->ID() > 0) {
		echo "<b style='color:#aa0407'>Se ha cargado la ficha correctamente. La publicacion no es automatica.</b>";
		exit(0);
	} else {
		$event = "upload_foto";
		echo "<b style='color:#aa0407'>Error al cargar la imagen. Verifique si la imagen corresponde con lo requerido</b><br>";
	}
	
}

if($event == "upload_foto") {
	$tplmarco = new Template("");
	$tplmarco->setFileRoot($TEMPLATE_ROOT);
	$tplmarco->Open("/upload_foto_disto.html");
	$tplmarco->setVar("{idficha}", $idficha);
	echo $tplmarco->Template;
	exit(0);

}
sleep(1);

	if($letras != $_SESSION['tmpletras']) {
        	echo "invalidletter";
                exit();
        }
 	
	if((!$titulo) || (!$director_nombre) || (!$director_mail) || (!$direccion)) {
		header("Content-Type: text/html;charset=iso-8859-1");
		echo "error";
		exit();
	}
	$objFicha = new ficha();
	$objFicha->db = $db;
	$objFicha->where="titulo like '".$titulo."' AND director_nombre like '".$director_nombre."'";
	if($objFicha->fetchall()) {
		header("Content-Type: text/html;charset=iso-8859-1");
		echo "duplicado";
		exit();
	} else {
		$objFicha->ID('');
		$objFicha->field("url",                 htmlspecialchars(utf8_decode(getvalue('url', '', false)), ENT_QUOTES));
		$objFicha->field("titulo",              htmlspecialchars(utf8_decode(getvalue('titulo', '', false)), ENT_QUOTES));
		$objFicha->field("titulo_en",           htmlspecialchars(utf8_decode(getvalue('titulo_en', '', false)), ENT_QUOTES));
		$objFicha->field("duracion",            htmlspecialchars(utf8_decode(getvalue('duracion', 0, false)), ENT_QUOTES));
		$objFicha->field("genero",              htmlspecialchars(utf8_decode(getvalue('genero', 0, false)), ENT_QUOTES));
		$objFicha->field("material",            htmlspecialchars(utf8_decode(getvalue('material', 0, false)), ENT_QUOTES));
		$objFicha->field("formato_realizacion", htmlspecialchars(utf8_decode(getvalue('formato_realizacion', 0, false)), ENT_QUOTES));
		$objFicha->field("formato_proyeccion",  htmlspecialchars(utf8_decode(getvalue('formato_proyeccion', 0, false)), ENT_QUOTES));
		$objFicha->field("formato_proyeccion_otros",  htmlspecialchars(utf8_decode(getvalue('formato_proyeccion_otros', 0, false)), ENT_QUOTES));
		$objFicha->field("idioma",              htmlspecialchars(utf8_decode(getvalue('idioma', '', false)), ENT_QUOTES));
		$objFicha->field("subtitulo_es",        htmlspecialchars(utf8_decode(getvalue('subtitulo_es', 0, false)), ENT_QUOTES));
		$objFicha->field("subtitulo_en",        htmlspecialchars(utf8_decode(getvalue('subtitulo_en', 0, false)), ENT_QUOTES));
		$objFicha->field("anno_realizacion",    htmlspecialchars(utf8_decode(getvalue('anno_realizacion', 0, false)), ENT_QUOTES));
		$objFicha->field("lugar_realizacion",   htmlspecialchars(utf8_decode(getvalue('lugar_realizacion', '', false)), ENT_QUOTES));
		$objFicha->field("web",                 htmlspecialchars(utf8_decode(getvalue('web', '', false)), ENT_QUOTES));
		$objFicha->field("facebook",            htmlspecialchars(utf8_decode(getvalue('facebook', '', false)), ENT_QUOTES));
		$objFicha->field("twitter",             htmlspecialchars(utf8_decode(getvalue('twitter', '', false)), ENT_QUOTES));
		$objFicha->field("clasificacion_inca",  htmlspecialchars(utf8_decode(getvalue('clasificacion_inca', '', false)), ENT_QUOTES));
		$objFicha->field("premios",             htmlspecialchars(utf8_decode(getvalue('premios', '', false)), ENT_QUOTES));
		$objFicha->field("sinopsis_es",         htmlspecialchars(utf8_decode(getvalue('sinopsis_es', '', false)), ENT_QUOTES));
		$objFicha->field("sinopsis_en",         htmlspecialchars(utf8_decode(getvalue('sinopsis_en', '', false)), ENT_QUOTES));
		$objFicha->field("direccion",           htmlspecialchars(utf8_decode(getvalue('direccion', '', false)), ENT_QUOTES));
		$objFicha->field("guion",               htmlspecialchars(utf8_decode(getvalue('guion', '', false)), ENT_QUOTES));
		$objFicha->field("produccion",          htmlspecialchars(utf8_decode(getvalue('produccion', '', false)), ENT_QUOTES));
		$objFicha->field("asistente_direccion", htmlspecialchars(utf8_decode(getvalue('asistente_direccion', '', false)), ENT_QUOTES));
		$objFicha->field("fotografia",          htmlspecialchars(utf8_decode(getvalue('fotografia', '', false)), ENT_QUOTES));
		$objFicha->field("camara",              htmlspecialchars(utf8_decode(getvalue('camara', '', false)), ENT_QUOTES));
		$objFicha->field("arte",                htmlspecialchars(utf8_decode(getvalue('arte', '', false)), ENT_QUOTES));
		$objFicha->field("musica",              htmlspecialchars(utf8_decode(getvalue('musica', '', false)), ENT_QUOTES));
		$objFicha->field("sonido",              htmlspecialchars(utf8_decode(getvalue('sonido', '', false)), ENT_QUOTES));
		$objFicha->field("edicion",             htmlspecialchars(utf8_decode(getvalue('edicion', '', false)), ENT_QUOTES));
		$objFicha->field("animacion",           htmlspecialchars(utf8_decode(getvalue('animacion', '', false)), ENT_QUOTES));
		$objFicha->field("productora",          htmlspecialchars(utf8_decode(getvalue('productora', '', false)), ENT_QUOTES));
		$objFicha->field("interpretes",         htmlspecialchars(utf8_decode(getvalue('interpretes', '', false)), ENT_QUOTES));
		$objFicha->field("director_nombre",        htmlspecialchars(utf8_decode(getvalue('director_nombre', '', false)), ENT_QUOTES));
		$objFicha->field("director_domicilio",     htmlspecialchars(utf8_decode(getvalue('director_domicilio', '', false)), ENT_QUOTES));
		$objFicha->field("director_codigo_postal", htmlspecialchars(utf8_decode(getvalue('director_codigo_postal', '', false)), ENT_QUOTES));
		$objFicha->field("director_telefono",      htmlspecialchars(utf8_decode(getvalue('director_telefono', '', false)), ENT_QUOTES));
		$objFicha->field("director_mail",          htmlspecialchars(utf8_decode(getvalue('director_mail', '', false)), ENT_QUOTES));
		$objFicha->field("director_nacionalidad",  htmlspecialchars(utf8_decode(getvalue('director_nacionalidad', '', false)), ENT_QUOTES));
		$objFicha->field("director_fecha_nac",     htmlspecialchars(utf8_decode(getvalue('director_fecha_nac', '', false)), ENT_QUOTES));

		$objC = new contactos();
		$objC->db = $db;
	
		if(getvalue('p_on', '', false) == 1) {
			$objC->ID('');
			$objC->field("nombre",        htmlspecialchars(utf8_decode(getvalue('p_nombre', '', false)), ENT_QUOTES));
			$objC->field("domicilio",     htmlspecialchars(utf8_decode(getvalue('p_domicilio', '', false)), ENT_QUOTES));
			$objC->field("telefono",      htmlspecialchars(utf8_decode(getvalue('p_telefono', '', false)), ENT_QUOTES));
			$objC->field("email",          htmlspecialchars(utf8_decode(getvalue('p_mail', '', false)), ENT_QUOTES));
			$objC->store();
			$objFicha->field('idproductor', $objC->ID());
		}
	
		if(getvalue('a_on', '', false) == 1) {
			$objC->ID('');
			$objC->field("nombre",        htmlspecialchars(utf8_decode(getvalue('a_nombre', '', false)), ENT_QUOTES));
			$objC->field("domicilio",     htmlspecialchars(utf8_decode(getvalue('a_domicilio', '', false)), ENT_QUOTES));
			$objC->field("telefono",      htmlspecialchars(utf8_decode(getvalue('a_telefono', '', false)), ENT_QUOTES));
			$objC->field("email",          htmlspecialchars(utf8_decode(getvalue('a_mail', '', false)), ENT_QUOTES));
			$objC->store();
			$objFicha->field('idagente', $objC->ID());
		}

		if(getvalue('d_on', '', false) == 1) {
			$objC->ID('');
			$objC->field("nombre",        htmlspecialchars(utf8_decode(getvalue('d_nombre', '', false)), ENT_QUOTES));
			$objC->field("domicilio",     htmlspecialchars(utf8_decode(getvalue('d_domicilio', '', false)), ENT_QUOTES));
			$objC->field("telefono",      htmlspecialchars(utf8_decode(getvalue('d_telefono', '', false)), ENT_QUOTES));
			$objC->field("email",          htmlspecialchars(utf8_decode(getvalue('d_mail', '', false)), ENT_QUOTES));
			$objC->store();
			$objFicha->field('iddistribuidor', $objC->ID());
		}
 
		$objFicha->store();
	
	// 		if($objUsuarios->ID()) {
	// 			enviaMail($objUsuarios->ID());
	// 		}
	
		if($objFicha->ID() > 0) {
			header("Content-Type: text/html;charset=iso-8859-1");
			echo $objFicha->ID();
			exit();
		} else {
			header("Content-Type: text/html;charset=iso-8859-1");
			echo "error";
			exit();
		}
	
	}
	
	/*	$tplcontent = new Template("");
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
		$tplmarco->setVar('{lang}', $lang);*/
		
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
	$objContenido->where = "contenidos_tipos.descripcion = 'Cinevivo::Mailregistro::newsletter'";
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
