<?php
require_once("../config.inc.php");
//require_once("phpCache.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
require_once("cmsdb.php");
require_once("funciones_www.php");

session_start();

$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$idcontenido = getvalue('idcontenido', 0, false);
$voto = getvalue('voto', 0, false);
// $usuariosweb_usuarios_idusuario = getvalue('usuariosweb_usuarios_idusuario', '0');
$remote_ip   = $_SERVER["REMOTE_ADDR"];
/*if(!$usuariosweb_usuarios_idusuario) {
	echo "nouser"; 
	exit();
}*/
//if (!($et=cache_all($CACHE_HIGH)))
//{
	$sql = "SELECT count(*) as votos FROM votos WHERE idcontenido=$idcontenido AND UNIX_TIMESTAMP(time) >= (UNIX_TIMESTAMP()-1200) AND remote_ip='$remote_ip' AND idopcion = -1";

// 	$sql = "SELECT count(*) as votos FROM votos WHERE idcontenido=$idcontenido AND idusuario = $usuariosweb_usuarios_idusuario AND idopcion = -1";

	if ($db->exec($sql))
	{
		$db->getrow();
		if ($db->Fields["votos"]==0)
		{
/*			$c = new contenidos();
			$c->db = $db;
			$c->joins = array();
			$c->fetch($idcontenido);
			$c->field('extra2', $c->field('extra2') + $voto);
			$c->field('extra3', $c->field('extra3') + 1);
			$c->field('extra4', sprintf("%.2f",$c->field('extra2') / $c->field('extra3')));
			$c->store();
*/
			$sql = "update contenidos set fecha = fecha, extra1 = extra1, extra2 = (extra2 + $voto), extra3 = (extra3 + 1), extra4 = round((extra2 / extra3),2) where idcontenido = $idcontenido";
//                      echo $sql;
                        $db->exec($sql, 0);
		
			$sql = "insert into votos (idcontenido, idopcion, remote_ip, time) values ($idcontenido, -1, '$remote_ip', NOW())";

/*			$sql = "insert into votos (idcontenido, idopcion, remote_ip, time, idusuario) values ($idcontenido, -1, '$remote_ip', NOW(), $usuariosweb_usuarios_idusuario)";*/
// 			echo $sql;
			$db->exec($sql, 0);

			$sql = "SELECT extra4 FROM contenidos WHERE idcontenido=$idcontenido";
	
			if ($db->exec($sql))
			{
				$db->getrow();
				echo $db->Fields["extra4"];
				exit();
			} 
/*			else {
			echo "error";
// 	    		echo "error"; 
		}*/
		}
	}
//}
$db->close();

/*$db->close();
mysql_free_result();*/
// print("<!-- left time: $et sec.-->");
?>
