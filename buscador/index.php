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

	$palabras = getvalue('palabras', '');

	$type = getvalue('type', $DEFAULT_TYPE);
	$area = getvalue('area', $DEFAULT_AREA);
	$frame = ($_GET['frame'] ? $_GET['frame'] : $DEFAULT_LAYOUT);
	$lang = getvalue('lang', $DEFAULT_LANG);
	$usuariosweb_usuarios_idusuario = getvalue('usuariosweb_usuarios_idusuario', '0');
	$usuariosweb_username = getvalue('usuariosweb_username', '0');
	$tpl  = "home_buscador";

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

        $pagina_descripcion = "CINEVIVO es una organizacion sin fines de lucro que nace del espiritu creativo y comprometido de un grupo de personas abocadas a fomentar el libre desarrollo y difusion del cine independiente, cuya caracteristica en comun es la voluntad de accion; lo cual es imprescindible para que un proyecto como este cobre forma y tenga continuidad";


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

	$strLimite = 10;
        $strLimiteFrom = ($GLOBALS["pagerbuscador_from"]) ? $GLOBALS["pagerbuscador_from"] : 0;
        $strLimiteFrom_Show = $strLimiteFrom * $strLimite;
	$paginas = 0;


	$sql = "SELECT DISTINCT count(*) as count FROM contenidos, publicacion WHERE publicacion.idcontenido = contenidos.idcontenido AND MATCH(titulo, titulo_corto, volanta, bajada, texto, palabras) AGAINST ('$palabras')";
	$db->exec($sql);
	if (!$db->numrows()) return "";
	$db->getrow();

	$total = $db->Fields['count'];
	if($strLimite > 0)
		$paginas = ceil($db->Fields['count']/$strLimite);

	$strPaginado = $tplmarco->getBlock("PAGINADO", "{BLOCK PAGINADO}");
	$tplPaginado = new Template($strPaginado);

	$strPagina = $tplPaginado->getBlock("PAGINADO.PAGINA", "{BLOCK PAGINA}");
	$strPaginaActiva = $tplPaginado->getBlock("PAGINADO.PAGINA_ACTIVA", "");
	
	$actual = ($strLimiteFrom + 1);
	$inicio = $actual - 5;
	$fin = $actual + 4;
	if($inicio < 0) $inicio = 0;
	if($fin > $paginas) $fin = $paginas;

	$strPaginaResult = "";
	for($x = $inicio ; $x < $fin ; $x ++) {
		if(($x+1) == $actual) {
			$tplPagina = new Template($strPaginaActiva);
			$tplPagina->setvar("{pager.pag_mostrar}", $x+1);
			$strPaginaResult.=$tplPagina->Template;
		} else {
			$tplPagina = new Template($strPagina);
			$tplPagina->setvar("{pager.pag}", $x);
			$tplPagina->setvar("{pager.pag_mostrar}", $x+1);
			$strPaginaResult.=$tplPagina->Template;
		}
	}
	$tplPaginado->setVar("{BLOCK PAGINA}", $strPaginaResult);
        $tplPaginado->setvar("{back.show}", (($strLimiteFrom + 1) <= 1 ) ? "none" : "block");
        $tplPaginado->setvar("{next.show}", (($strLimiteFrom + 1) >= $paginas) ? "none" : "block");
        $tplPaginado->setvar("{pager.paginas}", $paginas);
        $tplPaginado->setvar("{pager.pagina}", ($strLimiteFrom + 1));
        $tplPaginado->setvar("{pager.back}", (($strLimiteFrom - 1) < 0 ) ? 0 : ($strLimiteFrom - 1));
        $tplPaginado->setvar("{pager.next}", ($strLimiteFrom + 1));

	if($db->Fields['count'] == 0) 
		$tplmarco->setVar("{BLOCK PAGINADO}", "");
	else
		$tplmarco->setVar("{BLOCK PAGINADO}", $tplPaginado->Template);














	$sql = "SELECT DISTINCT contenidos.idcontenido as id, contenidos.idcontenido_tipo as tipo, MATCH(titulo, titulo_corto, volanta, bajada, texto, palabras) AGAINST ('$palabras') as score FROM contenidos, publicacion WHERE (publicacion.idcontenido = contenidos.idcontenido OR (contenidos.idcontenido_tipo = 9 AND contenidos.volanta > now() )) AND MATCH(titulo, titulo_corto, volanta, bajada, texto, palabras) AGAINST ('$palabras') order by score desc";
	$sql .= " limit $strLimiteFrom_Show, $strLimite";
	$db->exec($sql);

	$strCorto           = $tplmarco->getBlock("Corto", "{BLOCK Corto}");
	$strNota            = $tplmarco->getBlock("Nota", "{BLOCK Nota}");
	$strVideoEntrevista = $tplmarco->getBlock("Videoentrevista", "{BLOCK Videoentrevista}");
	$strAgenda          = $tplmarco->getBlock("Agenda", "{BLOCK Agenda}");

	$strResultado = "";
	while ($db->getrow()) {
		switch($db->Fields["tipo"]) {
			//Corto
			case 1:
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strCorto);
				break;
			//Editorial
			case 22:
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strNota);
				break;
			//Agenda
			case 9:
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strAgenda);
				break;
			//VideoEntrevista
			case 23:
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strVideoEntrevista);
				break;
			//Trailers
/*			case 28:
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strDestacada);
				$strDestacada = $strCorto;
				break;
			default:
				$strDestacada = $strCorto;
				breaK;*/
		}
	}
	$tplmarco->setVar("{BLOCK Result}", $strResultado);
	$tplmarco->setVar("{BLOCK Corto}", "");
	$tplmarco->setVar("{BLOCK Nota}", "");
	$tplmarco->setVar("{BLOCK Videoentrevista}", "");
	$tplmarco->setVar("{BLOCK Agenda}", "");
	$tplmarco->setVar("{palabras}", $palabras);


 	$tplmarco->setVar('{contenidos.titulo}', " Buscador | Cinevivo | cortos | realizadores independientes | festivales de cine | convocatorias de cortometrajes");	
	$tplmarco->setVar('{login}', ($usuariosweb_usuarios_idusuario ? "none" : "block" ));	
	$tplmarco->setVar('{user}',  ($usuariosweb_usuarios_idusuario ? "block" : "none" ));	
	$tplmarco->setVar('{username}', $usuariosweb_username);	
	$tplmarco->setVar('{pagina_descripcion}', $pagina_descripcion);	



	print($tplmarco->Template);

	$db->close();
//	endcache();
//}
print("<!-- left time: $et sec.-->");
?>
