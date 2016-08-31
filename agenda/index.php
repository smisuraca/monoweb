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
	$tpl  = getvalue('tpl', "home");
	$usuariosweb_usuarios_idusuario = getvalue('usuariosweb_usuarios_idusuario', '0');
	$usuariosweb_username = getvalue('usuariosweb_username', '0');

	if(($area != "Festival")&&($area != "Evento")&&($area != "Ciclo")&&($area != "Cinevivo"))  $area = "Convocatoria";

//	print "|$type|$area|$frame|$lang|$tpl|";

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

	$tplmarco->setVar('{area}', $area);	
	$tplmarco->setVar('{type}', $type);	
	$tplmarco->setVar('{frame}', $frame);
	$tplmarco->setVar('{tpl}', $tpl);
	$tplmarco->setVar('{lang}', $lang);



	$strLimite = 10;
        $strLimiteFrom = ($GLOBALS["pageragenda_from"]) ? $GLOBALS["pageragenda_from"] : 0;
        $strLimiteFrom_Show = $strLimiteFrom * $strLimite;
	$paginas = 0;


	$c = new contenidos();
	$c->db = $db;
	$c->joins = array();
	$c->where = "idcontenido_tipo = 9";
	$c->where.= " AND contenidos.titulo_corto like '".$area."'";
	if($area == "Convocatoria") {
		$c->where.= " AND contenidos.volanta > now()";
		$c->order_by = "volanta asc";
	} else {
		$c->where.= " AND contenidos.volanta > now()";
		$c->order_by = "fecha asc";
	}
	$c->limit_from = $strLimiteFrom_Show;
	$c->limit_count = $strLimite;






	$strSQL    = "SELECT count(*) as count ";
	$strSQL   .= "FROM contenidos ";
	$strSQL   .= "WHERE idcontenido_tipo = 9 ";
	$strSQL   .= " AND contenidos.titulo_corto like '".$area."'";
	if($area == "Convocatoria") {
		$strSQL .= " AND contenidos.volanta > now()";
		$strSQL .= " ORDER BY volanta asc";
	} else {
		$strSQL .= " AND contenidos.volanta > now()";
		$strSQL .= " ORDER BY fecha asc";
	}

	
	$db->exec($strSQL);
	if (!$db->numrows()) return "";
	
	$db->getrow();
	
	$total = $db->Fields['count'];
	$paginas = 0;
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










	$strDestacada = $tplmarco->getBlock("Agenda", "{BLOCK Agenda}");
	foreach( $c->fetchall() as $row) {
		
		$strResultado .= getNota($db, $row['idcontenido'], $strDestacada);
	}

	if ($idcontenido) {
	        $tplmarco->setVar('{pagina_descripcion}', "{contenidos.bajada}");	
		$tplmarco->Template = getNota($db, $idcontenido, $tplmarco->Template);
        }

        if(!$idcontenido) {
            if($area == "Portada") {
                $area = "el portal online de Cine Independiente";
            }
            $tplmarco->setVar('{contenidos.titulo}', $area." | Cinevivo | cortos | realizadores independientes | festivales de cine | convocatorias de cortometrajes");
        }
	$tplmarco->setVar("{BLOCK Agenda}", $strResultado);
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
