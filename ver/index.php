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
$idusuario   = getvalue('idusuario', 0);
$remote_ip   = $_SERVER["REMOTE_ADDR"];

//if (!($et=cache_all($CACHE_HIGH)))
//{
	$sql = "SELECT count(*) as votos FROM votos WHERE idcontenido=$idcontenido AND UNIX_TIMESTAMP(time) >= (UNIX_TIMESTAMP()-600) AND remote_ip='$remote_ip' AND idopcion = 0";

	if ($db->exec($sql, 0))
	{
		$db->getrow();
		if ($db->Fields["votos"]==0)
		{
			$sql = "DELETE FROM votos WHERE idcontenido=$idcontenido AND remote_ip='$remote_ip' AND idopcion = 0";
			$db->exec($sql, 0);
    

			$sql = "UPDATE ficha set fecha = fecha , reproducciones = reproducciones + 1 WHERE id=$idcontenido";
			$db->exec($sql, 0);

/*			$c = new contenidos();
			$c->db = $db;
			$c->joins = array();
			$c->fetch($idcontenido);
			$c->field('extra1', $c->field('extra1') + 1);
			$c->store();
		*/
			$sql = "insert into votos (idcontenido, idopcion, remote_ip, time) values ($idcontenido, 0, '$remote_ip', NOW())";
// 			echo $sql;
			$db->exec($sql, 0);
			echo "ok"; 
		} else {
	    		echo "error"; 
		}
	}
//}

$db->close();
print("<!-- left time: $et sec.-->");
?>
