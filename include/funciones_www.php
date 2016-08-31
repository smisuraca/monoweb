<?/**
 * This source file is part of CEMESE, publishing software.
 *
 * Copyright (C) 2000-2001 Garcia Rodrigo.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, US
**/

//*****************************************************************************
// Devuelve las noticias de un area especifica
//*****************************************************************************
function getArea_by_Id($dbCMS, $idArea, $strTemplate, $name= "Nota", $strDestacada="('SI')", $strLimite="", $str_from=0, $strFilter="", $strOrder="publicacion.fecha_publicacion DESC, publicacion.orden DESC", $strPaginar="0")
{
	global $TEMPLATE_ROOT;


        $strLimiteFrom = ($GLOBALS["pager".$idArea."_from"]) ? $GLOBALS["pager".$idArea."_from"] : 0;
        $strLimiteFrom_Show = $strLimiteFrom * $strLimite;

	if ($strPaginar) {

		$strSQL    = "SELECT count(*) as count ";
		$strSQL   .= "FROM publicacion, contenidos ";
		$strSQL   .= "WHERE publicacion.idcontenido = contenidos.idcontenido ";
		$strSQL   .= "AND publicacion.idarea  IN (".$idArea.") ";
		$strSQL   .= "AND publicacion.fecha_publicacion  <= '".date("YmdHis", time())."' ";
		$strSQL   .= "AND publicacion.destacada  IN ".$strDestacada." ";
		if ($strFilter) $strSQL .= " $strFilter ";
		
		$dbCMS->exec($strSQL);
		if (!$dbCMS->numrows()) return "";
		
		$dbCMS->getrow();
		
		$paginas = 0;
		if($strLimite > 0)
			$paginas = ceil($dbCMS->Fields['count']/$strLimite);
	
	}

	$tplNota = new Template($strTemplate);

	$strPaginado = $tplNota->getBlock("PAGINADO", "{BLOCK PAGINADO}");
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
			$tplPagina->setvar("{pager.idarea}", $idArea);
			$tplPagina->setvar("{pager.pag}", $x);
			$tplPagina->setvar("{pager.pag_mostrar}", $x+1);
			$strPaginaResult.=$tplPagina->Template;
		}
	}
	$tplPaginado->setVar("{BLOCK PAGINA}", $strPaginaResult);

        $tplPaginado->setvar("{pager.idarea}", $idArea);
        $tplPaginado->setvar("{back.show}", (($strLimiteFrom + 1) <= 1 ) ? "none" : "block");
        $tplPaginado->setvar("{next.show}", (($strLimiteFrom + 1) >= $paginas) ? "none" : "block");
        $tplPaginado->setvar("{pager.paginas}", $paginas);
        $tplPaginado->setvar("{pager.pagina}", ($strLimiteFrom + 1));
        $tplPaginado->setvar("{pager.back}", (($strLimiteFrom - 1) < 0 ) ? 0 : ($strLimiteFrom - 1));
        $tplPaginado->setvar("{pager.next}", ($strLimiteFrom + 1));
	$tplNota->setVar("{BLOCK PAGINADO}", $tplPaginado->Template);



	$strSQL    = "SELECT DISTINCTROW contenidos.idcontenido ";
	$strSQL   .= "FROM publicacion, contenidos ";
	$strSQL   .= "WHERE publicacion.idcontenido = contenidos.idcontenido ";
	$strSQL   .= "AND publicacion.idarea  IN (".$idArea.") ";
	$strSQL   .= "AND publicacion.fecha_publicacion  <= '".date("YmdHis", time())."' ";
	$strSQL   .= "AND publicacion.destacada  IN ".$strDestacada." ";
	if ($strFilter) $strSQL .= " $strFilter ";
	if ($strOrder)  $strSQL .= "ORDER BY $strOrder ";
    
        if ($strPaginar && $strLimite) { 
	    $strSQL .= "LIMIT $strLimiteFrom_Show,$strLimite ";
	} else if ($strLimite) {
		if($str_from > 0) {
		    $strSQL .= "LIMIT $str_from,$strLimite ";
		} else  {
		    $strSQL .= "LIMIT $strLimite ";
		}
	}

	$dbCMS->exec($strSQL);
	if (!$dbCMS->numrows()) return "";




	$strResultado = "";

	$strDestacada = $tplNota->getBlock($name, "{BLOCK $name}");

	while ($dbCMS->getrow())
		$strResultado .= getNota(clone($dbCMS), $dbCMS->Fields['idcontenido'], $strDestacada);

	$tplNota->setVar("{BLOCK $name}", $strResultado);

	return $tplNota->Template;
}


function getArea($dbCMS, $strArea, $strTemplate, $name="Nota", $strDestacada="('SI')", $strLimite="", $strFilter="", $strOrder="")
{
	global $TEMPLATE_ROOT;
	$strSQL   = " SELECT areas.* FROM areas" ;
	$strSQL   .= " WHERE areas.descripcion  = '".$strArea."' ";
	$dbCMS->exec($strSQL);
	if (!$dbCMS->getrow()) return "";

	return getArea_by_Id($dbCMS, $dbCMS->Fields["idarea"], $strTemplate, $name, $strDestacada, $strLimite, "", $strFilter);
}


function getAreas($dbCMS, $strTemplate, $where="", $order_by="", $limit="")
{
	$tplAreas = new Template($strTemplate);
	$objAreas = new Areas();
	$objAreas->db = $dbCMS;

	if ($where) $objAreas->where = $where;
    if ($order_by) $objAreas->order_by = $order_by;
    if ($limit) $objAreas->order_by .= " $limit";

	$tplAreas->Template = $objAreas->doformatall($tplAreas->Template);

	return $tplAreas->Template;
}

//*****************************************************************************
// Devuelve los informacion de los links asociados a las notas de un area
// y tipo especifico
//*****************************************************************************
function getArea_Related($dbCMS, $strArea, $strTipo, $strTemplate, $strDestacada="('SI')")
{
	global $TEMPLATE_ROOT;

	$strSQL   = " SELECT areas.idarea FROM areas" ;
	$strSQL   .= " WHERE areas.descripcion  = '".$strArea."' ";
	$dbCMS->exec($strSQL);
	if (!$dbCMS->getrow()) return "";

	$strSQL    = "SELECT idcontenido  ";
	$strSQL   .= "FROM publicacion ";
	$strSQL   .= "WHERE publicacion.idarea  = ".$dbCMS->Fields["idarea"]." ";
	$strSQL   .= "AND publicacion.fecha_publicacion  <= '".date("YmdHis", time())."' ";
	$strSQL   .= "AND publicacion.destacada  IN ".$strDestacada." ";
	$strSQL   .= "ORDER BY publicacion.orden DESC";
	$dbCMS->exec($strSQL);
	if (!$dbCMS->getrow()) return "";

	$strSQL		 = "SELECT contenidos_links.idcontenido as idcontenido , links.url as url, links.titulo as titulo ";
	$strSQL		.= "FROM contenidos_links, links, links_tipos ";
	$strSQL     .= "WHERE contenidos_links.idlink=links.idlink AND ";
	$strSQL     .= "links.idlink_tipo=links_tipos.idlink_tipo AND ";
	$strSQL     .= "links_tipos.descripcion IN ".$strTipo." AND ";
	$strSQL		.= "contenidos_links.idcontenido = " .$dbCMS->Fields['idcontenido']. " ";
	$strSQL     .= "ORDER BY links.orden DESC";
	$dbCMS ->exec($strSQL, 0);
	if (!$dbCMS->numrows()) { return ""; }

	$strResultado = "";

	if ($intTplisStr) {
		$tplModulo = new Template($strTemplate);
	} else {
		$tplModulo = new Template("");
		$tplModulo->setFileRoot($TEMPLATE_ROOT);
		$tplModulo->Open($strTemplate);
	}
	
	$strModulo = $tplModulo->getBlock("Link", "{BLOCK Link}");

	while ($dbCMS->getrow()) {
		$tplLink = new Template($strModulo);
		$tplLink->setVar("{codigo}", $dbCMS->Fields['idcontenido']);
		$tplLink->setVar("{nombre}", $dbCMS->Fields['titulo']);
		$tplLink->setVar("{link}", $dbCMS->Fields['url']);
		
		$strResultado .= $tplLink->Template;
	}
	
	$tplModulo->setVar("{BLOCK Link}", $strResultado);

	return $tplModulo->Template;
}

//********************************************************************
//Devuelve toda el form de una encuesta
//********************************************************************
function getEncuesta_form($dbCMS, $intEncuesta, $strTemplate)
{
	global $TEMPLATE_ROOT;

	$strSQL  = "SELECT * ";
	$strSQL	.= "FROM opciones ";
	$strSQL .= "WHERE idcontenido =".$intEncuesta;
	$dbCMS ->exec($strSQL, 0);
	if (!$dbCMS->numrows()) return "";

	$tplEncuesta = new Template($strTemplate);

	$strResultado = "";
	$strOpciones = $tplEncuesta->getBlock('Opciones', '{BLOCK Opciones}');

	while ($dbCMS->getrow()) {
		$tplOpciones = new Template($strOpciones);
		$tplOpciones->setVar('{opciones.idcontenido}', $dbCMS->Fields['idcontenido']);
		$tplOpciones->setVar('{opciones.idopcion}',    $dbCMS->Fields['idopcion']);
		$tplOpciones->setVar('{opciones.descripcion}', $dbCMS->Fields['descripcion']);
		$strResultado .= $tplOpciones->Template;
	}

	$tplEncuesta->setVar('{BLOCK Opciones}', $strResultado);

	return $tplEncuesta->Template;
}


//********************************************************************
//Devuelve toda la info de una nota a partir de su id
//********************************************************************
function getNota($dbCMS, $intNota, $strTemplate, $limite_foro="", $idusuario=0)
{
	global $TEMPLATE_ROOT, $FORMAT_DATE, $FORMAT_HOURS;

	$strSQL  = "SELECT * ";
	$strSQL	.= "FROM contenidos ";
	$strSQL .= "WHERE idcontenido =".$intNota;
	$dbCMS->exec($strSQL);
	if (!$dbCMS->getrow()) return "";

	$tplNota = new Template($strTemplate);

	switch($dbCMS->Fields['titulo_corto']) {
		case "videoclip":	$tplNota->setVar('{contenidos.genero}', "Videoclip");	break;
		case "documental":	$tplNota->setVar('{contenidos.genero}', "Documental");	break;
		case "experimental":	$tplNota->setVar('{contenidos.genero}', "Experimental");break;
		default:
			if(strpos($dbCMS->Fields['titulo_corto'], "icci", 0) == "1")
				$tplNota->setVar('{contenidos.genero}', "Ficcion");
			else
				$tplNota->setVar('{contenidos.genero}', "Animacion");
			break;

	}
	$tplNota->setVar('{contenidos.idcontenido}', $dbCMS->Fields['idcontenido']);
	$tplNota->setVar('{contenidos.titulo}', $dbCMS->Fields['titulo']);
	$tplNota->setVar('{contenidos.titulo_corto}', $dbCMS->Fields['titulo_corto']);
	$tplNota->setVar('{contenidos.bajada}', $dbCMS->Fields['bajada']);
	$tplNota->setVar('{contenidos.texto}', $dbCMS->Fields['texto']);
	$tplNota->setVar('{contenidos.extra1}', $dbCMS->Fields['extra1']);
	$tplNota->setVar('{contenidos.extra2}', $dbCMS->Fields['extra2']);
	$tplNota->setVar('{contenidos.extra3}', $dbCMS->Fields['extra3']);
	$tplNota->setVar('{contenidos.extra4}', $dbCMS->Fields['extra4']);
	
	if (substr($dbCMS->Fields['fecha'], 4, 1) == "-") {
		$fecha_ano	= substr($dbCMS->Fields['fecha'], 0, 4);
		$fecha_mes	= substr($dbCMS->Fields['fecha'], 5, 2);
		$fecha_dia	= substr($dbCMS->Fields['fecha'], 8, 2);
		$fecha_hora	= substr($dbCMS->Fields['fecha'], 11, 2);
		$fecha_minuto	= substr($dbCMS->Fields['fecha'], 14, 2);
		$fecha_segundo	= substr($dbCMS->Fields['fecha'], 17, 2);
	} else {
		$fecha_hora	= substr($dbCMS->Fields['fecha'], 8, 2);
		$fecha_minuto	= substr($dbCMS->Fields['fecha'], 10, 2);
		$fecha_segundo	= substr($dbCMS->Fields['fecha'], 12, 2);
		$fecha_dia	= substr($dbCMS->Fields['fecha'], 6, 2);
		$fecha_mes	= substr($dbCMS->Fields['fecha'], 4, 2);
		$fecha_ano	= substr($dbCMS->Fields['fecha'], 0, 4);
	}
	if ($tplNota->isTag("<!-- BEGIN contenidos.fecha.format -->")) {
		$fecha_format = $tplNota->getBlock("contenidos.fecha.format", "");
	} else {
		$fecha_format = $FORMAT_DATE;
	}
	if ( ereg( "([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $dbCMS->Fields['volanta'], $regs ) ) {
		$tplNota->setVar("{contenidos.volanta}", ftime($fecha_format, mktime(0, 0, 0, $regs[2], $regs[3], $regs[1])));
	} else {
		$tplNota->setVar('{contenidos.volanta}', $dbCMS->Fields['volanta']);
	}

	if ($fecha_ano.$fecha_mes.$fecha_dia!="00000000") {
		$tplNota->setVar("{contenidos.fecha}", ftime($fecha_format, mktime($fecha_hora, $fecha_minuto, $fecha_segundo, $fecha_mes, $fecha_dia, $fecha_ano)));
	} else {
		$tplNota->setVar("{contenidos.fecha}", "");
	}
	
	if ($tplNota->isTag("<!-- BEGIN contenidos.hora.format -->")) {
		$hora_format = $tplNota->getBlock("contenidos.hora.format", "");
	} else {
		$hora_format = $FORMAT_HOURS;
	}

	if ($fecha_hora.$fecha_minuto.$fecha_segundo!="000000") {
		$tplNota->setVar("{contenidos.hora}", ftime($hora_format, mktime($fecha_hora, $fecha_minuto, $fecha_segundo, $fecha_mes, $fecha_dia, $fecha_ano)));
	} else {
		$tplNota->setVar("{contenidos.hora}", "");
	}

	if ($tplNota->isTag("<!-- BEGIN Foros -->")) {
		$tplmmedia	= $tplNota->getBlock("Foros", "{BLOCK Foros}");
		if ($dbCMS->Fields["comentarios"]=="NO") {
			$tplNota->setVar("{BLOCK Foros}", "");
		} else {
			$tplNota->setVar("{BLOCK Foros}", getForo_Mensajes($dbCMS, $intNota, 0, $tplmmedia, $limite_foro, $idusuario));
		}
	}

	if ($tplNota->isTag("<!-- BEGIN Encuesta -->")) {
	    	$tplNota->setVar("{BLOCK Encuesta}", getEncuesta_form($dbCMS, $intNota, $tplNota->getBlock("Encuesta", "{BLOCK Encuesta}"), 1));
	}

	$sql="SELECT idlink_tipo, descripcion from links_tipos";
	$dbCMS->exec($sql);

	while ($dbCMS->getrow())
	{
		$links_tipo_nombre = $dbCMS->Fields["descripcion"];
		if ($tplNota->isTag("<!-- BEGIN $links_tipo_nombre -->")) {
			$tplmmedia	= $tplNota->getBlock($links_tipo_nombre, "{BLOCK $links_tipo_nombre}");
			$tplmmedia	= getNota_Related(clone($dbCMS), $intNota, "('$links_tipo_nombre')", $tplmmedia);
			$tplNota->setVar("{BLOCK $links_tipo_nombre}", $tplmmedia);
		}
	}
    
	return $tplNota->Template;
}


//****************************************************************
function getNota_Related($dbCMS, $intNota, $strTipo, $strTemplate, $strLimite="")
{
	global $TEMPLATE_ROOT, $FORMAT_DATE, $FORMAT_HOURS;
	$strSQL	= "SELECT contenidos_links.idcontenido as idcontenido , links.idlink as idlink, links.url as url, links.titulo as titulo, links.fname as fname, links.ftype as ftype , links_tipos.defaultpath, links_tipos.target, links_tipos.descripcion as links_tipos_descripcion ";
	$strSQL	.= "FROM contenidos_links, links, links_tipos ";
	$strSQL .= "WHERE contenidos_links.idlink=links.idlink AND ";
	$strSQL .= "links.idlink_tipo=links_tipos.idlink_tipo AND ";
	$strSQL .= "links_tipos.descripcion IN ".$strTipo." AND ";
	$strSQL	.= "contenidos_links.idcontenido = " .$intNota. " ";
	$strSQL .= "ORDER BY links.orden DESC ";
	if ($strLimite) $strSQL .= "LIMIT $strLimite";
	$dbCMS->exec($strSQL, 0);
	if (!$dbCMS->numrows()) return "";

	$tplModulo = new Template($strTemplate);

	$strModulo = $tplModulo->getBlock("Link", "{BLOCK Link}");

	$strResultado = "";
	$count = 0;
	while ($dbCMS->getrow())
	{
		$count++;
		$tplLink = new Template($strModulo);
		$tplLink->setVar("{links.count}", $count);

		$tplLink->setVar("{links.idlink}", $dbCMS->Fields['idlink']);
		$tplLink->setVar("{links.idlink $count}", $dbCMS->Fields['idlink']);

		$tplLink->setVar("{links.titulo}", $dbCMS->Fields['titulo']);
		$tplLink->setVar("{links.titulo $count}", $dbCMS->Fields['titulo']);

		$tplLink->setVar("{links.url}", $dbCMS->Fields['url']);
		$tplLink->setVar("{links.url $count}", $dbCMS->Fields['url']);

		$tplLink->setVar("{links.fname}", $dbCMS->Fields['fname']);
		$tplLink->setVar("{links.fname $count}", $dbCMS->Fields['fname']);

		$tplLink->setVar("{links.ftype}", $dbCMS->Fields['ftype']);
		$tplLink->setVar("{links.ftype $count}", $dbCMS->Fields['ftype']);

		$tplLink->setVar("{links_tipos.defaultpath}", $dbCMS->Fields['defaultpath']);
		$tplLink->setVar("{links_tipos.defaultpath $count}", $dbCMS->Fields['defaultpath']);

		$tplLink->setVar("{links_tipos.defaultext}", $dbCMS->Fields['defaultext']);
		$tplLink->setVar("{links_tipos.defaultext $count}", $dbCMS->Fields['defaultext']);

		$tplLink->setVar("{links_tipos.target}", $dbCMS->Fields['target']);
		$tplLink->setVar("{links_tipos.target $count}", $dbCMS->Fields['target']);

		$tplLink->setVar("{links_tipos.descripcion}", $dbCMS->Fields['links_tipos_descripcion']);
		$tplLink->setVar("{links_tipos.descripcion $count}", $dbCMS->Fields['links_tipos_descripcion']);
		$strResultado .= $tplLink->Template;

	}

	$tplModulo->setVar("{BLOCK Link}", $strResultado);

	return $tplModulo->Template;
}


//*****************************************************************************
// Funcion para traer los links relacionados de un modulo
//*****************************************************************************
function getNota_Related_by_Titulo($dbCMS, $strModulo, $strTipo, $strTemplate, $strLimite="")
{
	$strSQL = "SELECT idcontenido FROM contenidos WHERE titulo='" . $strModulo . "'";
	$dbCMS->exec($strSQL, 0);
	if (!$dbCMS->getrow()) return "";

	return getNota_Related($dbCMS, $dbCMS->Fields['idcontenido'], $strTipo, $strTemplate, $strLimite);
}

//********************************************************************
// Funciones para el manejo de Encuesta
//********************************************************************
function Encuesta_view($dbCMS, $idcontenido, $strTemplate, $order_by=0, $top=9999, $desde=0, $hasta=0) {
	global $TEMPLATE_ROOT;

	$strSQL  = "SELECT * ";
	$strSQL	.= "FROM contenidos ";
	$strSQL .= "WHERE idcontenido =".$idcontenido;
	$dbCMS->exec($strSQL, 0);
	if (!$dbCMS->getrow()) return "";

	$tplEncuesta = new Template($strTemplate);

    $tplEncuesta->setVar("{preguntas.idcontenido}", $idcontenido);
    $tplEncuesta->setVar("{preguntas.descripcion}", $dbCMS->Fields["titulo"]);

	$Total_Votos = Encuesta_votos_total($dbCMS, $idcontenido, $desde, $hasta);
    $tplEncuesta->setVar("{preguntas.total_votos}", $Total_Votos);
    
    $strResultado = "";
    $strRow       = $tplEncuesta->getBlock("Row", "{BLOCK Row}");

	$sql = "SELECT opciones.*, time, count(*) as votos ";
	$sql.= "FROM votos, opciones ";
	$sql.= "WHERE votos.idopcion=opciones.idopcion AND votos.idcontenido=$idcontenido ";
		if ($desde) {$sql.= "AND time >= '$desde' ";}
		if ($hasta) {$sql.= "AND time <= '$hasta' ";}
	$sql.= "GROUP BY opciones.idopcion ";
		if ($order_by) {$sql .= "ORDER BY $order_by";}
    $dbCMS->exec($sql, 0);

	$Opciones = "(";
	while ($dbCMS->getrow() && $top)
	{
        $Votos = $dbCMS->Fields["votos"];
        if ($Votos>0) {
                $Percent = sprintf("%d", $Votos/$Total_Votos*100);
        } else {
                $Percent = sprintf("%d", 0);
        }
        $tplrow = new Template($strRow);
        $tplrow->setVar("{opciones.idcontenido}", $dbCMS->Fields["idcontenido"]);
        $tplrow->setVar("{opciones.idopcion}", $dbCMS->Fields["idopcion"]);
        $tplrow->setVar("{opciones.descripcion}", $dbCMS->Fields["descripcion"]);
        $tplrow->setVar("{opciones.porcentaje}", $Percent);
        $tplrow->setVar("{opciones.votos}", $Votos);
        $strResultado .= $tplrow->Template;
		$Opciones .= "'".$dbCMS->Fields["idopcion"]."',";
		$top--;
	}
	$Opciones .= "'')";

	$sql    = "SELECT * FROM opciones WHERE idcontenido=$idcontenido AND idopcion NOT IN $Opciones";
    $dbCMS->exec($sql, 0);
	while ($dbCMS->
	getrow() && $top)
	{
        $tplrow = new Template($strRow);
        $tplrow->setVar("{opciones.idcontenido}", $dbCMS->Fields["idcontenido"]);
        $tplrow->setVar("{opciones.idopcion}", $dbCMS->Fields["idopcion"]);
        $tplrow->setVar("{opciones.descripcion}", $dbCMS->Fields["descripcion"]);
        $tplrow->setVar("{opciones.porcentaje}", 0);
        $tplrow->setVar("{opciones.votos}", 0);
        $strResultado .= $tplrow->Template;
		$top--;
	}

    $tplEncuesta->setVar("{BLOCK Row}", $strResultado);    
    return $tplEncuesta->Template;
}

function Encuesta_view_2($dbCMS, $idcontenido, $strTemplate, $order_by=0, $top=9999, $desde=0, $hasta=0)
{
	global $TEMPLATE_ROOT;

	$strSQL  = "SELECT * ";
	$strSQL	.= "FROM preguntas ";
	$strSQL .= "WHERE idcontenido =".$idcontenido;
	$dbCMS ->exec($strSQL);
	if (!$dbCMS->getrow()) return "";

	$tplEncuesta = new Template($strTemplate);

    $tplEncuesta->setVar("{preguntas.idcontenido}", $idcontenido);
    $tplEncuesta->setVar("{preguntas.descripcion}", $dbCMS->Fields["descripcion"]);
    $tplEncuesta->setVar("{preguntas.idarea}", $dbCMS->Fields["idarea"]);

	$Total_Votos = Encuesta_votos_total($dbCMS, $idcontenido, $desde, $hasta);
    $tplEncuesta->setVar("{preguntas.total_votos}", $Total_Votos);
    
	$sql = "SELECT opciones.*, time, count(*) as votos ";
	$sql.= "FROM votos, opciones ";
	$sql.= "WHERE votos.idopcion=opciones.idopcion AND votos.idcontenido=$idcontenido ";
	if ($desde) $sql.= "AND time >= '$desde' ";
	if ($hasta) $sql.= "AND time <= '$hasta' ";
	$sql.= "GROUP BY opciones.idopcion ";
	if ($order_by) $sql .= "ORDER BY $order_by";
    $dbCMS->exec($sql);

	$Count = 1;
	$Opciones = "(";
	while ($dbCMS->getrow() && $top)
	{
        $Votos = $dbCMS->Fields["votos"];
        if ($Votos>0)
                $Percent = sprintf("%d", $Votos/$Total_Votos*100);
        else
                $Percent = sprintf("%d", 0);

        $tplEncuesta->setVar("{opciones$Count.idcontenido}", $dbCMS->Fields["idcontenido"]);
        $tplEncuesta->setVar("{opciones$Count.idopcion}", $dbCMS->Fields["idopcion"]);
        $tplEncuesta->setVar("{opciones$Count.descripcion}", $dbCMS->Fields["descripcion"]);
        $tplEncuesta->setVar("{opciones$Count.porcentaje}", $Percent);
        $tplEncuesta->setVar("{opciones$Count.votos}", $Votos);
		$Opciones .= "'".$dbCMS->Fields["idopcion"]."',";
		$top--;
		$Count++;
	}
	$Opciones .= "'')";

	$sql    = "SELECT * FROM opciones WHERE idcontenido=$idcontenido AND idopcion NOT IN $Opciones";
    $dbCMS->exec($sql, 0);
	while ($dbCMS->getrow() && $top)
	{
        $tplEncuesta->setVar("{opciones$Count.idcontenido}", $dbCMS->Fields["idcontenido"]);
        $tplEncuesta->setVar("{opciones$Count.idopcion}", $dbCMS->Fields["idopcion"]);
        $tplEncuesta->setVar("{opciones$Count.descripcion}", $dbCMS->Fields["descripcion"]);
        $tplEncuesta->setVar("{opciones$Count.porcentaje}", 0);
        $tplEncuesta->setVar("{opciones$Count.votos}", 0);
		$top--;
		$Count++;
	}

    return $tplEncuesta->Template;
}

function Encuesta_save($dbCMS, $idcontenido, $idopcion, $remote_ip, $idusuario=0)
{
	if ($idusuario)
	    $sql = "SELECT count(*) as votos FROM votos WHERE idcontenido=$idcontenido AND idusuario=$isusuario";
	else
	    $sql = "SELECT count(*) as votos FROM votos WHERE idcontenido=$idcontenido AND UNIX_TIMESTAMP(time) >= UNIX_TIMESTAMP()-1800 AND remote_ip='$remote_ip'";

	if ($dbCMS->exec($sql, 0) && isset($idopcion))
	{
		$dbCMS->getrow();
		if ($dbCMS->Fields["votos"]==0)
		{
				$sql = "insert into votos (idcontenido, idopcion, remote_ip, time, idusuario) values ($idcontenido, $idopcion, '$remote_ip', NOW(), $idusuario)";
				return $dbCMS->exec($sql, 0);
		}
    }
    return false;
}


function Encuesta_votos($dbCMS, $idcontenido, $idopcion, $desde=0, $hasta=0)
{
    $dbCMS->exec("SELECT count(*) as votos FROM votos WHERE idcontenido=$idcontenido AND idopcion=$idopcion", 0);
    $dbCMS->getrow();
    return $dbCMS->Fields["votos"];
}


function Encuesta_votos_total($dbCMS, $idcontenido, $desde=0, $hasta=0)
{
	$sql = "SELECT count(*) as total_votos FROM votos WHERE idcontenido=$idcontenido ";
	if ($desde) {$sql .= "AND time >= '$desde' ";}
	if ($hasta) {$sql .= "AND time <= '$hasta' ";}
    $dbCMS->exec($sql, 0);
    $dbCMS->getrow();
    return $dbCMS->Fields["total_votos"];
}



//********************************************************************
//Funciones para el manejo de foros
//********************************************************************
function getForo_Mensajes($dbCMS, $idcontenido, $idpadre, $strTemplate, $limite="", $idusuario=0)
{
	global $TEMPLATE_ROOT, $app_includepath;

	require_once("class.foros.php");

	$tplForos = new Template($strTemplate);

	$objForos = new foros(clone($dbCMS), $idpadre, $idcontenido);
	$objForos->db = clone($dbCMS);
	if ($limite)
	{
		$cantidad=explode(",",$limite);
		$tplForos->Template = $objForos->paginado($cantidad[1], $tplForos->Template, 1);
		$tplForos->setVar("{foros.cantidad_limite}", "limite=0,$cantidad[1]");
	}

	$objForos->order_by  = "fecha DESC";
	$objForos->order_by .= " $limite";
	$objForos->where     = " idpadre = $idpadre and idcontenido = $idcontenido ";
	$objForos->where    .= " AND (idusuario=$idusuario OR idusuario_to=$idusuario OR idusuario_to=0)";
	$tplForos->Template  = $objForos->doformatall($tplForos->Template, 1);

	return $tplForos->Template;
}


function getForo($dbCMS, $idForo, $strTemplate, $limite="", $idusuario=0)
{
	global $TEMPLATE_ROOT, $app_includepath;

	require_once("class.foros.php");

	$tplForos = new Template($strTemplate);

	$objForos = new foros($dbCMS);
	$objForos->getvalue($idForo);

	if ($tplForos->isTag("<!-- BEGIN Foros -->"))
	{
		$tplForos_mensajes	= $tplForos->getBlock("Foros", "{BLOCK Foros}");
		$tplForos->setVar("{BLOCK Foros}", getForo_Mensajes($dbCMS, $objForos->idcontenido, $objForos->idforo, $tplForos_mensajes, $limite, $idusuario));
	}

	$tplForos->Template = $objForos->doFormat($tplForos->Template, 1);
	return $tplForos->Template;
}


//********************************************************
// Checkea las ACLs por la direccion de IP del cliente
//********************************************************
function checkIP($dbCMS, $strIp)
{
	$strSQL = "SELECT * FROM acl WHERE INSTR('$strIp', ip)>0";
	$dbCMS->exec($strSQL);
	if ($dbCMS->getrow())
		return 1;
	else
		return 0;
}

?>
