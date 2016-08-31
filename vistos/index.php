<?php
require_once("../config.inc.php");
//require_once("phpCache.inc.php");

//if (!($et=cache_all($CACHE_HIGH)))
//{
	require_once("class.dbMysql.php");
	require_once("class.template.php");
	require_once("cmsdb.php");
	require_once("funciones_www.php");

	session_start();
	header("Content-Type: text/html;charset=iso-8859-1");

	$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
	$db->connect();

	$type = getvalue('type', $DEFAULT_TYPE);
	$area = getvalue('area', $DEFAULT_AREA);
	$frame = ($_GET['frame'] ? $_GET['frame'] : $DEFAULT_LAYOUT);
	$lang = getvalue('lang', $DEFAULT_LANG);
	$usuariosweb_usuarios_idusuario = getvalue('usuariosweb_usuarios_idusuario', '0');
	$usuariosweb_username = getvalue('usuariosweb_username', '0');
	$tpl  = "home_vistos";

	$tplcontent = new Template("");
	$tplcontent->setFileRoot($TEMPLATE_ROOT);
	$tplcontent->Open("/${tpl}_$lang.$type");

	$tplmarco = new Template("");
	$tplmarco->setFileRoot($TEMPLATE_ROOT);
	$tplmarco->Open("/${frame}_$lang.$type");
	$tplmarco->setVar('{contenido}', $tplcontent->Template);

	$tplmarco->setVar('{area}', $area);	
	$tplmarco->setVar('{type}', $type);	
	$tplmarco->setVar('{frame}', $frame);
	$tplmarco->setVar('{tpl}', $tpl);
	$tplmarco->setVar('{lang}', $lang);
	$tplmarco->setVar('{publicidades}', "Home");

	$a = new Areas();
	$a->db = $db;
	$a->joins = array();
	
	$tplarea = new Template("");

	foreach( $a->fetchall() as $row)
	{
		while ($tplmarco->isTag("<!-- BEGIN $row[descripcion] -->"))
		{
			$tplarea->Template = $tplmarco->getBlock($row[descripcion], "<!-- BLOCK $row[descripcion] -->");

			$area_destacada = "('SI', 'NO')";
			if ($tplarea->isTag("<!-- BEGIN $row[descripcion].destacada -->"))
				$area_destacada = $tplarea->getBlock("$row[descripcion].destacada", "");

			$area_cantidad = '';
			if ($tplarea->isTag("<!-- BEGIN $row[descripcion].limite -->"))
				$area_cantidad = $tplarea->getBlock("$row[descripcion].limite", "");

			$tplmarco->setVar("<!-- BLOCK $row[descripcion] -->",
				getArea_by_Id($db, $row[idarea], $tplarea->Template, "Nota", $area_destacada, $area_cantidad)
			);
		}
	}


	$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos, publicacion WHERE publicacion.idcontenido = contenidos.idcontenido AND contenidos.idcontenido_tipo = 1 ORDER BY extra1 desc LIMIT 3";
	$db->exec($sql);

	$strDestacada = $tplmarco->getBlock("Nota", "{BLOCK Nota}");
	while ($db->getrow()) {
		$strResultado .= getNota(clone($db), $db->Fields["id"], $strDestacada);
	}
	$tplmarco->setVar("{BLOCK Nota}", $strResultado);


	$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos, publicacion WHERE publicacion.idcontenido = contenidos.idcontenido AND contenidos.idcontenido_tipo = 1 ORDER BY extra1 desc LIMIT 3,7";
	$db->exec($sql);
	$strResultado = "";
	$strDestacada = $tplmarco->getBlock("Nota_2", "{BLOCK Nota_2}");

	while ($db->getrow()) {
		$strResultado .= getNota(clone($db), $db->Fields["id"], $strDestacada);
	}
	
        if(!$idcontenido) {
            if($area == "Portada") {
                $area = "el portal online de Cine Independiente";
            }
            $tplmarco->setVar('{contenidos.titulo}', $area." | Cinevivo | cortos | realizadores independientes | festivales de cine | convocatorias de cortometrajes");
        }
	$tplmarco->setVar("{BLOCK Nota_2}", $strResultado);
	$tplmarco->setVar('{login}', ($usuariosweb_usuarios_idusuario ? "none" : "block" ));	
	$tplmarco->setVar('{user}',  ($usuariosweb_usuarios_idusuario ? "block" : "none" ));	
	$tplmarco->setVar('{username}', $usuariosweb_username);	
	$tplmarco->setVar('{pagina_descripcion}', "Los cortos mas vistos de Cinevivo");	



	print($tplmarco->Template);

	$db->close();
//	endcache();
//}
print("<!-- left time: $et sec.-->");
?>
