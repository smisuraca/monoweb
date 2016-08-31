<?php
require_once("../config.inc.php");
// require_once("phpCache.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
require_once("cmsdb.php");
require_once("funciones_www.php");

session_start();
header("Content-Type: text/html;charset=iso-8859-1");

$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$idcontenido = getvalue('idcontenido', 0, false);
$mensaje = getvalue('mensaje', '', false);
$letras = getvalue('letras', '', false);
$nombre = getvalue('nombre', '', false);
$mail   = getvalue('mail', '', false);
// $usuariosweb_usuarios_idusuario = getvalue('usuariosweb_usuarios_idusuario', '0');
$remote_ip   = $_SERVER["REMOTE_ADDR"];
if($letras != $_SESSION['tmpletras']) {
	echo "invalidletter";
	exit();

}
/*if(!$usuariosweb_usuarios_idusuario) {
	echo "nouser"; 
	exit();
}*/

/*if (!($et=cache_all($CACHE_HIGH)))
{*/

if($mensaje == "") {
	echo "Error de datos";
	exit();
}

	$sql = "SELECT count(*) as count FROM foros WHERE idcontenido=$idcontenido AND UNIX_TIMESTAMP(fecha) >= (UNIX_TIMESTAMP()-86400) AND ip like '$remote_ip'";

	if ($db->exec($sql, 0))
	{
		$db->getrow();
		if ($db->Fields["count"]==0)
		{


			$mensaje = htmlspecialchars(utf8_decode($mensaje), ENT_QUOTES);
			$nombre = htmlspecialchars(utf8_decode($nombre), ENT_QUOTES);
			$mail = htmlspecialchars(utf8_decode($mail), ENT_QUOTES);
			$sql = "insert into foros (idcontenido, nombre, mail, mensaje, ip, fecha) values ($idcontenido, '$nombre', '$mail', '$mensaje', '$remote_ip', NOW())";

			$datetime = date('Y-m-d H:i');

// 			$tmp = split("@", $usuariosweb_username);
// 			$user = $tmp[0];

			$db->exec($sql, 0);
			echo "<table border='0' cellspacing='8' cellpadding='0' width='100%' class='foro_contenido'><tr><td width='100%'><div style='float:right'>$nombre</div><b>$datetime</b><br>$mensaje</td></tr></table>";

			exit();
/* 		} else {
	    		echo "error";
			exit();
		}*/
// 	}
		} else {
	    		echo "Ya se ha escrito un comentario"; 
		}
	}
// }
$db->close();

/*$db->close();
mysql_free_result();*/
// print("<!-- left time: $et sec.-->");
?>
