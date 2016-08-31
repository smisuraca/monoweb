<?
//
// This source file is part of CEMESE, publishing software.
//
// Copyright (C) 2000-2001 Garcia Rodrigo
// All rights reserved.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, US
//

function render_mod_home($db, $area, $tplcontent) {
global  $WEB_NUM_NO_DESTACADA, $WEB_NAME, $TEMPLATE_ROOT;

    $area_tofile = strtolower(strtr($area, " áéíóúñÁÉÍÓÚÑ", "_aeiounAEIOUN"));

    if ($tplcontent->isTag('<!--#include virtual="home_'.$area_tofile.'.html" -->')) {
    	
        $tplmodulo = new Template("");
        $tplmodulo->setFileRoot($TEMPLATE_ROOT);
        $tplmodulo->Open("/home_".$area_tofile.".html");

        $render_result = 0;

        $result = getArea($db, $WEB_NAME, "$area::Home", $tplmodulo->getBlock('Destacada', '{BLOCK Destacada}'), 1, "('SI')", $WEB_NUM_DESTACADA);
        if ($result != "") {$render_result = 1;}
        $tplmodulo->setVar('{BLOCK Destacada}', $result);

        $result = getArea($db, $WEB_NAME, "$area::Home", $tplmodulo->getBlock('No Destacada', '{BLOCK No Destacada}'), 1, "('NO')", $WEB_NUM_NO_DESTACADA);
        if ($result != "") {$render_result = 1;}
        $tplmodulo->setVar('{BLOCK No Destacada}', $result);

        $result = getArea($db, $WEB_NAME, "$area::Encuestas", $tplmodulo->getBlock('Encuesta', '{BLOCK Encuesta}'), 1, "('SI')", $WEB_NUM_NO_DESTACADA);
        if ($result != "") {$render_result = 1;}
        $tplmodulo->setVar('{BLOCK Encuesta}', $result);


        $result = getArea($db, $WEB_NAME, "$area::Foros", $tplmodulo->getBlock('Foro', '{BLOCK Foro}'), 1, "('SI')", $WEB_NUM_NO_DESTACADA);
        if ($result != "") {$render_result = 1;}
        $tplmodulo->setVar('{BLOCK Foro}', $result);


        if ($render_result) {
            $tplcontent->setVar('<!--#include virtual="home_'.$area_tofile.'.html" -->', $tplmodulo->Template);
        } else {
            $tplcontent->setVar('<!--#include virtual="home_'.$area_tofile.'.html" -->', "");        
        }
    }

    return $tplcontent;
}

function render_mod_detalle_nota($db, $idcontenido, $idforo, $limit, $tplcontent, $idusuario) {
global $TEMPLATE_ROOT, $WEB_NAME, $WEB_NUM_DESTACADA, $WEB_NUM_NO_DESTACADA;

    $tplmodulo = new Template("");
    $tplmodulo->setFileRoot($TEMPLATE_ROOT);

    if ($tplcontent->isTag('<!--#include virtual="mod_detalle_nota.html" -->')) {
        if (!$idforo) {
            $tplmodulo->Open("/mod_detalle_nota.html");
            $tplcontent->setVar('<!--#include virtual="mod_detalle_nota.html" -->',
                getNota($db, $WEB_NAME, $idcontenido, $tplmodulo->Template, 1, $limit, $idusuario));
        } else {
            $tplmodulo->Open("/mod_detalle_foro.html");
            $tplcontent->setVar('<!--#include virtual="mod_detalle_nota.html" -->',
                getForo($db, $idforo, $tplmodulo->Template, 1, $limit, $idusuario));
        }
    }

    return $tplcontent;
}

function render_mod_mas_notas($db, $area, $tplcontent) {
global $TEMPLATE_ROOT, $WEB_NAME, $WEB_NUM_DESTACADA, $WEB_NUM_NO_DESTACADA;

    $tplmodulo = new Template("");
    $tplmodulo->setFileRoot($TEMPLATE_ROOT);

    if ($tplcontent->isTag('<!--#include virtual="mod_mas_notas.html" -->')) {
    	
        $tplmodulo->Open("/mod_mas_notas.html");

	    $tplcontent->setVar('<!--#include virtual="mod_mas_notas.html" -->',
            getArea($db, $WEB_NAME, $area, $tplmodulo->Template, 1, "('SI','NO')", $WEB_NUM_DESTACADA+$WEB_NUM_NO_DESTACADA));
    }
    
    return $tplcontent;
}


function render_mod_otras_secciones($db, $seccion, $title, $tplcontent) {
global $TEMPLATE_ROOT, $WEB_NAME, $WEB_NUM_DESTACADA, $WEB_NUM_NO_DESTACADA;

    $tplmodulo = new Template("");
    $tplmodulo->setFileRoot($TEMPLATE_ROOT);

    if ($tplcontent->isTag('<!--#include virtual="mod_otras_secciones.html" -->')) {
    	
        $tplmodulo->Open("/mod_otras_secciones.html");

        $tplmodulo->setVar('{BLOCK Recursos Humanos}', getArea($db, $WEB_NAME, 'Recursos Humanos::'.$seccion, $tplmodulo->getBlock('Recursos Humanos', '{BLOCK Recursos Humanos}'), 1, "('SI')", $WEB_NUM_DESTACADA));

        $tplmodulo->setVar('{BLOCK Servicios}', getArea($db, $WEB_NAME, 'Servicios::'.$seccion, $tplmodulo->getBlock('Servicios', '{BLOCK Servicios}'), 1, "('SI')", $WEB_NUM_DESTACADA));

        $tplmodulo->setVar('{BLOCK Noticiero}', getArea($db, $WEB_NAME, 'Noticiero::'.$seccion, $tplmodulo->getBlock('Noticiero', '{BLOCK Noticiero}'), 1, "('SI')", $WEB_NUM_DESTACADA));

        $tplmodulo->setVar('{BLOCK Prensa}', getArea($db, $WEB_NAME, 'Prensa::'.$seccion, $tplmodulo->getBlock('Prensa', '{BLOCK Prensa}'), 1, "('SI')", $WEB_NUM_DESTACADA));

        $tplmodulo->setVar('{BLOCK Sistemas}', getArea($db, $WEB_NAME, 'Sistemas::'.$seccion, $tplmodulo->getBlock('Sistemas', '{BLOCK Sistemas}'), 1, "('SI')", $WEB_NUM_DESTACADA));

        $tplmodulo->setVar('{BLOCK Compras}', getArea($db, $WEB_NAME, 'Compras::'.$seccion, $tplmodulo->getBlock('Compras', '{BLOCK Compras}'), 1, "('SI')", $WEB_NUM_DESTACADA));

        $tplmodulo->setVar('{BLOCK Fílmico}', getArea($db, $WEB_NAME, 'Fílmico::'.$seccion, $tplmodulo->getBlock('Fílmico', '{BLOCK Fílmico}'), 1, "('SI')", $WEB_NUM_DESTACADA));

        $tplmodulo->setVar('{titulo}', $title);

	    $tplcontent->setVar('<!--#include virtual="mod_otras_secciones.html" -->', $tplmodulo->Template);
    }

    return $tplcontent;
}



function render_mod_notas_areas_home($db, $area, $seccion, $tplcontent) {
global $TEMPLATE_ROOT, $WEB_NAME, $WEB_NUM_DESTACADA, $WEB_NUM_NO_DESTACADA;

    $tplmodulo = new Template("");
    $tplmodulo->setFileRoot($TEMPLATE_ROOT);


    if ($tplcontent->isTag('<!--#include virtual="mod_notas_areas_home.html" -->')) {
    	
        $tplmodulo->Open("/mod_notas_areas_home.html");

        $tplmodulo->setVar('{BLOCK Destacada}',
            getArea($db, $WEB_NAME, $area."::".$seccion, 
                $tplmodulo->getBlock('Destacada', '{BLOCK Destacada}'), 1, "('SI')", $WEB_NUM_DESTACADA
            )
        );

        $tplmodulo->setVar('{BLOCK No Destacada}',
            getArea($db, $WEB_NAME, $area."::".$seccion, 
                $tplmodulo->getBlock('No Destacada', '{BLOCK No Destacada}'), 1, "('NO')", $WEB_NUM_NO_DESTACADA
            )
        );

	    $tplcontent->setVar('<!--#include virtual="mod_notas_areas_home.html" -->', $tplmodulo->Template);
    }

    return $tplcontent;
}


function render_marco($db, $area, $tplcontent) {
global $TEMPLATE_ROOT, $WEB_NAME, $WEB_NUM_DESTACADA, $WEB_NUM_NO_DESTACADA;

    $area_tofile = strtolower(strtr($area, " áéíóúñÁÉÍÓÚÑ", "_aeiounAEIOUN"));

    $tplmarco = new Template("");
    $tplmarco->setFileRoot($TEMPLATE_ROOT);
    
    $tplmodulo = new Template("");
    $tplmodulo->setFileRoot($TEMPLATE_ROOT);
    

    $tplmarco->Open("/".$area_tofile."_marco.html");
    $tplmarco->setVar('{contenido}', $tplcontent->Template);

    $tplmodulo->Open("/".$area_tofile."_marco_menu.html");
	$tplmarco->setVar('<!--#include virtual="'.$area_tofile.'_marco_menu.html" -->', $tplmodulo->Template);
//	$tplmarco->setVar('<!--#include virtual="mod_menu_'.$area.'.html" -->', eregi_replace(">$area::", ">",getAreas($db, $tplmodulo->Template, 1, "descripcion LIKE '$area::%' AND descripcion NOT LIKE '$area::%::%' AND descripcion NOT LIKE '$area::Home'", " descripcion ")));
    
    $tplmodulo->Open("/mod_marco_izq.html");
	$tplmarco->setVar('<!--#include virtual="mod_marco_izq.html" -->', $tplmodulo->Template);

	$tplmodulo->Open("/mod_foros.html");
	$tplmarco->setVar('<!--#include virtual="mod_foros.html" -->', getArea($db, $WEB_NAME, $area."::Foros", $tplmodulo->Template, 1, "('SI')", 3));

	$tplmodulo->Open("/mod_encuesta.html");
	$tplmarco->setVar('<!--#include virtual="mod_encuesta.html" -->', getArea($db, $WEB_NAME, $area."::Encuestas", $tplmodulo->Template, 1, "('SI')", 1));

    $tplmarco->setVar('{area}', $area);
    $tplmarco->setVar('{area.tofile}', $area_tofile);


    return $tplmarco;

}


?>