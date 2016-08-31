<?php
require_once("../config.inc.php");
//require_once("phpCache.inc.php");

//if (!($et=cache_all($CACHE_HIGH)))
//{
	require_once("class.dbMysql.php");
	require_once("class.template.php");
	require_once("cmsdb.php");
	require_once("funciones_www.php");

$titulo_web = "";

function handleEvent($event, $db) {

	switch($event) {
		case "cortos_list":
			$page_size = 20;
			$page = ($GLOBALS["page"]) ? $GLOBALS["page"] : 1;
			
			$f = new ficha();
			$f->db = $db;
			$f->order_by = ($GLOBALS["cortos_order_by"]) ? $GLOBALS["cortos_order_by"] : "id desc";
			$f->where = "ficha.publicacion > 0 AND ";

			if(!$GLOBALS["genero"]) $GLOBALS["genero"] = 1;
			if(!$GLOBALS["material"]) $GLOBALS["material"] = 0;

			if($GLOBALS["genero"] == 100)
				$f->where .= "1";
			else
				$f->where .= "genero = ".$GLOBALS["genero"];

			$f->where .= " AND material = ".$GLOBALS["material"];

			$total = $f->count();
			
			$f->limit_from = ($page - 1) * $page_size;
			$f->limit_count = $page_size;
			
			$tpltmp = new Template("");
			$tpltmp->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
			$tpltmp->Open("/cortos_list.html");

	                $paginas = 0;
                        $paginas = ceil($total/$page_size);

			$strPagPre = $strPagPos = $tpltmp->getBlock("CORTOS.PAGINADO.PAGINA", "");

			$inicio = $page - 8;
			$fin = $page + 7;
			if($inicio < 1) $inicio = 1;
			if($fin > $paginas) $fin = $paginas;

			$pre = "";
			for($i = $inicio ; $i < $page ; $i++) {
				$tpl = new Template($strPagPre);
				$tpl->setVar('{page}', $i);
				$pre .= $tpl->Template;
			}
			$tpltmp->setVar('{page_pre}', $pre);

			$tpltmp->setVar('{page}', $i);
	
			$pos = "";
			for($i = $page + 1 ; $i <= $fin ; $i++) {
				$tpl = new Template($strPagPos);
				$tpl->setVar('{page}', $i);
				$pos .= $tpl->Template;
			}
			$tpltmp->setVar('{page_pos}', $pos);

			
			$tpltmp->setVar('{back}', ($page - 1 < 1) ? 1 : ($page -1 ));
			$tpltmp->setVar('{next}', ($page + 1 > $fin) ? $fin : ($page + 1));
			$tpltmp->setVar('{fin}', $paginas);
			$tpltmp->setVar('{order_by}', $f->order_by);
			$tpltmp->setVar('{genero}', $GLOBALS["genero"]);
			$tpltmp->setVar('{material}', $GLOBALS["material"]);

			$result = $f->doformatall($tpltmp->Template);
		
			return $result;
		
		case "cortos_edit":

			$f = new ficha();
			$f->db = $db;
			$f->fetch($GLOBALS["id"]);

			if($f->values["publicacion"] == 0 && $GLOBALS["test"] != 1)
				header("Location: ?tpl=home");

			$f->values["premios"] = str_replace("\n", "<br>", $f->values["premios"]);
			$tpltmp = new Template("");
			$tpltmp->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
			$tpltmp->Open("/cortos_edit.html");
		
			foreach ($f->fields as $field)
				if($f->values[$field] == "")
					$tpltmp->getBlock("SHOW.$field", "");

			$GLOBALS['titulo_web'] = $f->values["titulo"].", Dir. ".$f->values["direccion"];
			$result = $f->doformat($tpltmp->Template);

			return $result;
			
		default:
		
			return "";
	
	}

}


	session_start();
	header("Content-Type: text/html;charset=iso-8859-1");
	$db  = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
	$db->connect();

	$type = getvalue('type', $DEFAULT_TYPE);
	$area = getvalue('area', $DEFAULT_AREA);
	$frame = ($_GET['frame'] ? $_GET['frame'] : $DEFAULT_LAYOUT);
	$lang = getvalue('lang', $DEFAULT_LANG);
	$lang = "es";
	$calidad = getvalue('calidad', $calidad);
	$tpl  = getvalue('tpl', "home");
	$usuariosweb_usuarios_idusuario = getvalue('usuariosweb_usuarios_idusuario', '0');
	$usuariosweb_username = getvalue('usuariosweb_username', '0');
// 	print "|$type|$area|$frame|$lang|$tpl|";

	$tplcontent = new Template("");
	$tplcontent->setFileRoot($TEMPLATE_ROOT);
	$tplcontent->Open("/${tpl}_$lang.$type");

	$tplmarco = new Template("");
	$tplmarco->setFileRoot($TEMPLATE_ROOT);
	$tplmarco->Open("/${frame}_$lang.$type");
	
	
	if($evento) {
		$tplmarco->setVar('{contenido}', handleEvent($evento, $db));
	} else {
		$tplmarco->setVar('{contenido}', $tplcontent->Template);
	}

	$tplmarco->setVar('{login}', ($usuariosweb_usuarios_idusuario ? "none" : "block" ));	
	$tplmarco->setVar('{user}',  ($usuariosweb_usuarios_idusuario ? "block" : "none" ));	
	$tplmarco->setVar('{username}', $usuariosweb_username);	
	$tplmarco->setVar('{area}', $area);	
	$tplmarco->setVar('{type}', $type);	
	$tplmarco->setVar('{frame}', $frame);
	$tplmarco->setVar('{tpl}', $tpl);
	$tplmarco->setVar('{lang}', $lang);
	$tplmarco->setVar('{publicidades}', "Home");
	$tplmarco->setVar('{calidad}', (($calidad == "_baja") ? $calidad: "" ));
	$tplmarco->setVar('{random}', rand(1, 6));

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

			$area_cantidad_from = '';
			if ($tplarea->isTag("<!-- BEGIN $row[descripcion].limite_from -->"))
				$area_cantidad_from = $tplarea->getBlock("$row[descripcion].limite_from", "");

			$area_paginar = '0';
			if ($tplarea->isTag("<!-- BEGIN $row[descripcion].paginar -->"))
				$area_paginar = $tplarea->getBlock("$row[descripcion].paginar", "");
		    
//			echo $GLOBALS["pager170_from"];
			$tplmarco->setVar("<!-- BLOCK $row[descripcion] -->",
				getArea_by_Id(clone($db), $row[idarea], $tplarea->Template, "Nota", $area_destacada, $area_cantidad, $area_cantidad_from, "", "publicacion.fecha_publicacion DESC, publicacion.orden DESC", $area_paginar)
			);
		}
	}



	if ($tpl == "home") {
		$ranking_posicion = rand(0, 9);
		$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos WHERE contenidos.extra3 > 5 AND contenidos.idcontenido_tipo = 1 ORDER BY extra4 desc, extra1 desc LIMIT $ranking_posicion,1";
		$db->exec($sql);
	
		$strDestacada = $tplmarco->getBlock("Ranking", "{BLOCK Ranking}");
		while ($db->getrow()) {
			$idcontenido_ranking = $db->Fields["id"];
			$strResultado = getNota(clone($db), $db->Fields["id"], $strDestacada);
		}
		$tplmarco->setVar("{BLOCK Ranking}", $strResultado);
		$tplmarco->setVar('{ranking_posicion}', ($ranking_posicion+1));
	

		$vistos_position = rand(0, 9);
		$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos WHERE contenidos.idcontenido_tipo = 1 AND contenidos.idcontenido <> $idcontenido_ranking ORDER BY extra1 desc LIMIT $vistos_position,1";
		$db->exec($sql);
	
		$strDestacada = $tplmarco->getBlock("Vistos", "{BLOCK Vistos}");
		while ($db->getrow()) {
			$strResultado = getNota(clone($db), $db->Fields["id"], $strDestacada);
		}
		$tplmarco->setVar("{BLOCK Vistos}", $strResultado);
		$tplmarco->setVar('{vistos_position}', ($vistos_position+1));
	


		$dentro_15_dias = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+15, date("Y")));
		$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos WHERE idcontenido_tipo = 9 AND contenidos.titulo_corto like 'Convocatoria' AND contenidos.volanta > '$dentro_15_dias' ORDER BY volanta asc LIMIT 1";
		$db->exec($sql);
		$strResultado = "";
		$strDestacada = $tplmarco->getBlock("Convocatoria", "{BLOCK Convocatoria}");
		while ($db->getrow()) {
			$strResultado .= getNota(clone($db), $db->Fields["id"], $strDestacada);
		}
		$tplmarco->setVar("{BLOCK Convocatoria}", $strResultado);


// 		$dentro_15_dias = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+15, date("Y")));
		$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos WHERE idcontenido_tipo = 9 AND contenidos.titulo_corto like 'Festival' AND contenidos.fecha >= now() ORDER BY fecha asc LIMIT 1";
		$db->exec($sql);
		$strResultado2 = "";
		$strDestacada2 = $tplmarco->getBlock("Festival", "{BLOCK Festival}");
		while ($db->getrow()) {
			$strResultado2 .= getNota(clone($db), $db->Fields["id"], $strDestacada2);
		}
		$tplmarco->setVar("{BLOCK Festival}", $strResultado2);
	
	} else {
		if ($tpl == "home_corto") {
			$sql = "select count(*) as cortos from contenidos, publicacion where idcontenido_tipo = 1 AND publicacion.idcontenido = contenidos.idcontenido;";
			if ($db->exec($sql))
			{
				$db->getrow();
				$total_cortos= $db->Fields["cortos"];
				$tercio = intval($total_cortos/3);
				$azar1 = rand(1, $tercio);
				$azar2 = rand(1, $tercio);
				$azar3 = rand(1, $tercio) + $tercio;
				
				$strDestacada = $tplmarco->getBlock("Cortos Azar", "{BLOCK Cortos Azar}");
				$sql = "select contenidos.idcontenido as idcont from contenidos, publicacion where idcontenido_tipo = 1 AND contenidos.idcontenido <> $idcontenido AND publicacion.idcontenido = contenidos.idcontenido order by extra1 desc limit $azar2,1";
				if ($db->exec($sql))
				{
					$db->getrow();
					$strResultado .= getNota(clone($db), $db->Fields["idcont"], $strDestacada);
				}
				$sql = "select contenidos.idcontenido as idcont from contenidos, publicacion where idcontenido_tipo = 1 AND contenidos.idcontenido <> $idcontenido AND publicacion.idcontenido = contenidos.idcontenido order by extra1 desc limit $azar3,1";
				if ($db->exec($sql))
				{
					$db->getrow();
					$strResultado .= getNota(clone($db), $db->Fields["idcont"], $strDestacada);
				}
				$sql = "select contenidos.idcontenido as idcont from contenidos, publicacion where idcontenido_tipo = 1 AND contenidos.idcontenido <> $idcontenido AND publicacion.idcontenido = contenidos.idcontenido order by extra1 limit $azar1,1";
				if ($db->exec($sql))
				{
					$db->getrow();
					$strResultado .= getNota(clone($db), $db->Fields["idcont"], $strDestacada);
				}

				$tplmarco->setVar("{BLOCK Cortos Azar}", $strResultado);
			} 
		} else if ( $tpl = "home_cortos") {

			$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos, publicacion, areas WHERE contenidos.idcontenido = publicacion.idcontenido AND publicacion.idarea = areas.idarea AND areas.descripcion like 'Cortos::$area' AND publicacion.destacada = 'SI' ORDER BY RAND() LIMIT 3";
			$db->exec($sql);
	
			$ids = array();
	
			$strDestacada = $tplmarco->getBlock("Recomendados", "{BLOCK Recomendados}");
			$strResultado = "";
			while ($db->getrow()) {
				array_push($ids, $db->Fields["id"]);
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strDestacada);
			}
			$tplmarco->setVar("{BLOCK Recomendados}", $strResultado);
	
			$sql_ids = join(",", $ids);
	
			$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos WHERE contenidos.extra3 > 5 AND contenidos.idcontenido_tipo = 1 AND contenidos.titulo_corto like '$area' AND contenidos.idcontenido NOT IN ($sql_ids) ORDER BY extra4 desc, extra1 desc LIMIT 3";
			$db->exec($sql);
		
			$strDestacada = $tplmarco->getBlock("Ranking", "{BLOCK Ranking}");

			$strResultado = "";
			while ($db->getrow()) {
				array_push($ids, $db->Fields["id"]);
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strDestacada);
			}
			$tplmarco->setVar("{BLOCK Ranking}", $strResultado);
	// 		$tplmarco->setVar('{ranking_posicion}', ($ranking_posicion+1));
		
			$sql_ids = join(",", $ids);
	
			$sql = "SELECT DISTINCT contenidos.idcontenido as id FROM contenidos WHERE contenidos.idcontenido_tipo = 1 AND contenidos.titulo_corto like '$area' AND contenidos.idcontenido NOT IN ($sql_ids) ORDER BY extra1 desc LIMIT 3";
			$db->exec($sql);
		
			$strDestacada = $tplmarco->getBlock("Vistos", "{BLOCK Vistos}");
			$strResultado = "";
			while ($db->getrow()) {
				$strResultado .= getNota(clone($db), $db->Fields["id"], $strDestacada);
			}
			$tplmarco->setVar("{BLOCK Vistos}", $strResultado);

		}
	}

	if ($idcontenido) {
        	$tplmarco->setVar('{pagina_descripcion}', "{contenidos.bajada}");
		$tplmarco->Template = getNota($db, $idcontenido, $tplmarco->Template);
        }

        if(!$idcontenido) {
            if($area == "Portada") {
                $area = "Cine Independiente | cine online";
            }
            if ($titulo_web != "") {
           	$tplmarco->setVar('{contenidos.titulo}', $titulo_web);
            } else {
           	$tplmarco->setVar('{contenidos.titulo}', $area." | Cinevivo | cortos | realizadores independientes | festivales de cine | convocatorias de cortometrajes | Bafici 2011");
            }
        }
        

	$tplmarco->setVar('{area}', $area);	
	$tplmarco->setVar('{type}', $type);	
	$tplmarco->setVar('{frame}', $frame);
	$tplmarco->setVar('{tpl}', $tpl);
	$tplmarco->setVar('{lang}', $lang);
	$tplmarco->setVar('{pagina_descripcion}', $pagina_descripcion);

	if ($tpl == "home" && !$evento) {

		$f = new ficha();
		$f->db = $db;
		$f->order_by = "id desc";
		$f->where = "ficha.publicacion = 3";
		$f->limit_count = 1;
		$f->change_name("ficha_main");
		$row = $f->fetchall();
		
		$tpltmp = new Template($tplmarco->Template);
		foreach ($f->fields as $field)
			if($row[0][$field] == "")
				$tpltmp->getBlock("SHOW.$field", "");
		
		$result = $f->doformatall($tpltmp->Template);

		$ff = new ficha();
		$ff->db = $db;
		$ff->order_by = "id desc";
		$ff->where = "ficha.publicacion = 2";
		$ff->limit_from = 0;
		$ff->limit_count = 3;
		$ff->change_name("ficha_home");
		
		$result = $ff->doformatall($result);
		

		$ff = new ficha();
		$ff->db = $db;
		$ff->order_by = "id desc";
		$ff->where = "ficha.publicacion = 2";
		$ff->limit_from = 3;
		$ff->limit_count = 3;
		$ff->change_name("ficha_home_2");
		
		$result = $ff->doformatall($result);
		
		print $result;
				
	} else {
		print($tplmarco->Template);
	}
	$db->close();
//	endcache();
//}
print("<!-- left time: $et sec.-->");
?>
