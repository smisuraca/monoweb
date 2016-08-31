<?php
require_once("../config.inc.php");
require_once("phpCache.inc.php");
require_once("class.dbMysql.php");
require_once("class.template.php");
require_once("cmsdb.php");
require_once("funciones_www.php");

session_start();

$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$idcontenido = getvalue('idcontenido', 0, false);
$idopcion    = getvalue('idopcion',    0, false);
$idusuario   = getvalue('usuariosweb_idusuario', 0);
$remote_ip   = $_SERVER["REMOTE_ADDR"];

if ($idcontenido && $idopcion)
	Encuesta_save($db, $idcontenido, $idopcion, $remote_ip, $idusuario);

if (!($et=cache_all($CACHE_HIGH)))
{
	$type = getvalue('type',  $DEFAULT_TYPE);
	$lang = getvalue('lang',  $DEFAULT_LANG);
	$tpl  = getvalue('tpl',   "home_encuesta_view", false);
	
	$tplcontent = new Template("");
	$tplcontent->setFileRoot($TEMPLATE_ROOT);
	$tplcontent->Open("/${tpl}_$lang.$type");

	$tplmarco = new Template("");
	$tplmarco->setFileRoot($TEMPLATE_ROOT);
	$tplmarco->Open("/frame_empty_$lang.$type");
	$tplmarco->setVar('{contenido}', $tplcontent->Template);

	$tplmarco->setVar('{type}', $type);	
	$tplmarco->setVar('{tpl}', $tpl);
	$tplmarco->setVar('{lang}', $lang);
	
	$tplmarco->Template = Encuesta_view($db, $idcontenido, $tplmarco->Template);

	header("Content-Type: text/html;charset=iso-8859-1");
	print($tplmarco->Template);
}

$db->close();
print("<!-- left time: $et sec.-->");
?>
