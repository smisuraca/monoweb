<?
//
// This source file is part of CEMESE, publishing software.
//
// Copyright (C) 2000-2001 Garcia Rodrigo.
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

/*require_once($app_includepath."/class.webs.php");*/
require_once($app_includepath."/locale.php");
require_once($app_includepath."/cmsdb.php");

class foros {
    var $db;
    var $idforo;
    var $idcontenido;
    var $idusuario;
    var $idusuario_to;
    var $idpadre;
    var $nombre;
    var $asunto;
    var $mensaje;
	var $fecha;
	var $ip;
    var $tipo;
    var $aprobado;

    var $where;
    var $order_by;

    //******************************************
    // inicializa el objeto
    //******************************************
    function foros($db, $idforo=0, $idcontenido=0, $idusuario=0, $idusuario_to=0, $idpadre=0, $asunto="", $mensaje="", $fecha="", $ip="", $tipo="PRIVADO", $aprobado="NO", $nombre="") {
        $this->db = $db;
        $this->setvalue($idforo, $idcontenido, $idusuario, $idusuario_to, $idpadre, $asunto, $mensaje , $fecha, $ip, $tipo, $aprobado, $nombre);
    }

    //******************************************
    // setea los valores
    //******************************************
    function setvalue($idforo=0, $idcontenido=0, $idusuario=0, $idusuario_to=0, $idpadre=0, $asunto="", $mensaje="", $fecha="", $ip="", $tipo="PRIVADO", $aprobado="NO", $nombre="") {
        $this->idforo           = isset($idforo) ? $idforo : 0;
        $this->idcontenido      = isset($idcontenido) ? $idcontenido : 0;
        $this->idusuario        = isset($idusuario) ? $idusuario : 0;
        $this->idusuario_to     = isset($idusuario_to) ? $idusuario_to : 0;
        $this->idpadre          = isset($idpadre) ? $idpadre : 0;
        $this->nombre           = isset($nombre) ? $nombre : "";
        $this->asunto           = isset($asunto) ? $asunto : "";
        $this->mensaje          = isset($mensaje) ? $mensaje : "";
        $this->fecha            = isset($fecha) ? $fecha : "";
        $this->ip               = isset($ip) ? $ip : "";
        $this->tipo             = isset($tipo) ? $tipo : "PRIVADO";
        $this->aprobado         = isset($aprobado) ? $aprobado : "NO";
    }


    //******************************************
    // obtiene los valores desde un idforo
    //******************************************
    function getvalue($idforo) {
        $find = false;

        if ($idforo) {
            $sql  = "SELECT * FROM foros WHERE idforo=$idforo";

            $this->db->exec($sql);
            if ($this->db->getrow()) {
                $find = true;
                $this->idforo      = $this->db->Fields["idforo"];
                $this->idcontenido = $this->db->Fields["idcontenido"];
                $this->idusuario   = $this->db->Fields["idusuario"];
                $this->idusuario_to= $this->db->Fields["idusuario_to"];
                $this->idpadre     = $this->db->Fields["idpadre"];
                $this->asunto      = $this->db->Fields["asunto"];
                $this->mensaje     = $this->db->Fields["mensaje"];
                $this->fecha       = $this->db->Fields["fecha"];
                $this->ip          = $this->db->Fields["ip"];
                $this->tipo        = $this->db->Fields["tipo"];
                $this->aprobado    = $this->db->Fields["aprobado"];
                $this->nombre      = $this->db->Fields["nombre"];
            }
        }

        if (!$find) { $this->setvalue(); }
    }


    //*******************************************
    // Realiza las altas, bajas y modificaciones
    //*******************************************
    function doabm($action) {
        switch ($action) {
            case "grabar":
				$this->asunto = $this->dividir_cadena($this->asunto);
				$this->mensaje = $this->dividir_cadena($this->mensaje);
                if (!$this->idforo) {
                    $sql = "INSERT INTO foros (idcontenido, idusuario, idusuario_to, idpadre, asunto, mensaje, ip, tipo, aprobado) values($this->idcontenido, $this->idusuario, $this->idusuario_to, $this->idpadre, '$this->asunto', '$this->mensaje', '".$GLOBALS["REMOTE_ADDR"]."', '$this->tipo', '$this->aprobado')";
			        $this->db->exec($sql);
                } else {
                    $sql="UPDATE foros SET idcontenido=$this->idcontenido, idusuario=$this->idusuario, idusuario_to=$this->idusuario_to, idpadre=$this->idpadre, asunto='$this->asunto', mensaje='$this->mensaje', ip='".$GLOBALS["REMOTE_ADDR"]."', tipo='$this->tipo', aprobado='$this->aprobado' WHERE idforo=$this->idforo";
			        $this->db->exec($sql);
                }
                break;
            case "borrar":

				if ($this->idforo>0){
					$this->borrar_hijos($this->idforo);
				}

                $sql="DELETE FROM foros WHERE idforo=$this->idforo";
		        $this->db->exec($sql);
                break;
        }


        if( $this->db->Error ){return 0; }else{ return 1;}
    }


    //*******************************************
    // Devuelve los datos formateados de un area
    // en particular
    //*******************************************
    function doformat($strtemplate, $intisstr=0) {
        global $TEMPLATE_ROOT, $FORMAT_DATE, $FORMAT_HOURS;
        if ($intisstr) {
            $tplform = new Template($strtemplate);
        } else {
            $tplform = new Template("");
            $tplform->setFileRoot($TEMPLATE_ROOT);
            $tplform->Open($strtemplate);
        }


        if ($tplform->isTag("<!-- BEGIN foros.fecha.format -->")) {
            $fecha_format = $tplform->getBlock("foros.fecha.format", "");
        } else {
            $fecha_format = $FORMAT_DATE;
        }
        
        if ($tplform->isTag("<!-- BEGIN foros.hora.format -->")) {
            $hora_format = $tplform->getBlock("foros.hora.format", "");
        } else {
            $hora_format = $FORMAT_HOURS;
        }



        $tplform->setVar("{foros.idforo}", $this->idforo);
        $tplform->setVar("{foros.idcontenido}", $this->idcontenido);
        $tplform->setVar("{foros.idusuario}", $this->idusuario);
        $tplform->setVar("{foros.idusuario_to}", $this->idusuario_to);
        $tplform->setVar("{foros.idpadre}", $this->idpadre);
        $tplform->setVar("{foros.nombre}", $this->nombre);


	if (substr($this->fecha, 4, 1) == "-") {
		$fecha_ano	= substr($this->fecha, 0, 4);
		$fecha_mes	= substr($this->fecha, 5, 2);
		$fecha_dia	= substr($this->fecha, 8, 2);
		$fecha_hora	= substr($this->fecha, 11, 2);
		$fecha_minuto	= substr($this->fecha, 14, 2);
		$fecha_segundo	= substr($this->fecha, 17, 2);
	} else {
		$fecha_hora	= substr($this->fecha, 8, 2);
		$fecha_minuto	= substr($this->fecha, 10, 2);
		$fecha_segundo	= substr($this->fecha, 12, 2);
		$fecha_dia	= substr($this->fecha, 6, 2);
		$fecha_mes	= substr($this->fecha, 4, 2);
		$fecha_ano	= substr($this->fecha, 0, 4);
	}


		if ($fecha_ano.$fecha_mes.$fecha_dia!="00000000") {
			$tplform->setVar("{foros.fecha}", ftime($fecha_format, mktime($fecha_hora, $fecha_minuto, $fecha_segundo, $fecha_mes, $fecha_dia, $fecha_ano)));
		} else {
			$tplform->setVar("{foros.fecha}", "");
		}

		if ($fecha_hora.$fecha_minuto.$fecha_segundo!="000000") {
			$tplform->setVar("{foros.hora}", ftime($hora_format, mktime($fecha_hora, $fecha_minuto, $fecha_segundo, $fecha_mes, $fecha_dia, $fecha_ano)));
		} else {
			$tplform->setVar("{foros.hora}", "");
		}


        $tplform->setVar("{foros.tipo}", $this->tipo);
        $tplform->setVar("{foros.aprobado}", $this->aprobado);
        $tplform->setVar("{foros.ip}", $this->ip);
        $tplform->setVar("{foros.asunto}", $this->asunto);
        $tplform->setVar("{foros.mensaje}", $this->mensaje);
        $tplform->setVar("{foros.total_mensajes}", $this->total_mensajes());
		$tplform->setVar("{foros.root}", $this->root_foro());

		$objForos_padre= new foros($this->db);
		$objForos_padre->getvalue($this->idpadre);
        $tplform->setVar("{foros.asunto_padre}", $objForos_padre->asunto);
        $tplform->setVar("{foros.mensaje_padre}", $objForos_padre->mensaje);

        $tplform->setVar("{foros.where}", $this->where);
        $tplform->setVar("{foros.order_by}", $this->order_by);

		$objContenidos = new contenidos();
		$objContenidos->db = $this->db;
		$objContenidos->fetch($this->idcontenido);
		$tplform->Template = $objContenidos->doformat($tplform->Template);

		$objUsuarios = new usuarios();
		$objUsuarios->db = $this->db;
		$objUsuarios->fetch($this->idusuario);
		$tplform->Template = $objUsuarios->doformat($tplform->Template);


        return $tplform->Template;
    }


    //*******************************************
    // Genera un lista con todos los registros
    //*******************************************
    function doformatall($strtemplate, $intisstr=0) {
        if ($intisstr) {
            $tpllist = new Template($strtemplate);
        } else {
            $tpllist = new Template("");
            $tpllist->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
            $tpllist->Open($strtemplate);
        }

        $sql  = "SELECT * FROM foros ";
        if ($this->where) {$sql .= "WHERE $this->where ";}
        if ($this->order_by) {$sql .= " ORDER BY $this->order_by";}
        $this->db->exec($sql);
        $strResultado = "";
        $strRow = $tpllist->getBlock('foros.Row', '{BLOCK foros.Row}');

        while ($this->db->getrow()) {
		$objF = new foros(clone($this->db), 0, 0);
		$objF->setvalue($this->db->Fields["idforo"], $this->db->Fields["idcontenido"], $this->db->Fields["idusuario"], $this->db->Fields["idusuario_to"], $this->db->Fields["idpadre"], $this->db->Fields["asunto"], $this->db->Fields["mensaje"], $this->db->Fields["fecha"], $this->db->Fields["ip"], $this->db->Fields["tipo"], $this->db->Fields["aprobado"], $this->db->Fields["nombre"]);
		$strResultado .= $objF->doformat($strRow, 1);
        }

        $tpllist->setVar('{BLOCK foros.Row}', $strResultado);

        return $tpllist->Template;
    }

    function total_mensajes() {
        $dbForo = $this->db;
        if ($this->idforo){
            $sql= "SELECT count(idforo) as total_mensajes FROM foros WHERE idpadre = $this->idforo ";
        } else{
            $sql  = "SELECT count(idforo) as total_mensajes FROM foros WHERE idcontenido = $this->idcontenido and idpadre = 0 ";
        }
        $dbForo->exec($sql);
        $dbForo->getrow();
        return $dbForo->Fields["total_mensajes"];
    }

    function paginado($cantidad, $strtemplate, $intisstr=0){
        if ($intisstr) {
            $tpllist = new Template($strtemplate);
        } else {
            $tpllist = new Template("");
            $tpllist->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
            $tpllist->Open($strtemplate);
        }

        $strResultado = "";
        $strRow = $tpllist->getBlock('foros.paginado.Row', '{BLOCK foros.paginado.Row}');

        $this->db;
        $cant = $this->total_mensajes()/$cantidad;
        $cont = 1;
        $root = "";
        $inflevel=0;
        while ($cant>0){

            $tplrow = new Template($strRow);
            $tplrow->setVar('{foros.paginado.pagina}'   , $cont);
            $tplrow->setVar('{foros.paginado.desde}'    , $inflevel);
            $tplrow->setVar('{foros.paginado.cantidad}' , $cantidad);
            $strResultado .= $tplrow->Template;

            $inflevel = $inflevel+$cantidad;
            $cant-= 1;
            $cont+= 1;
        }

        $tpllist->setVar('{BLOCK foros.paginado.Row}', $strResultado);

		return $tpllist->Template;
	}




	function root_foro(){
		$dbForo = $this->db;
	    $etiquetanum=1;
		$foro=$this->idforo;
		$root="";
	    $sql= "SELECT idpadre FROM foros WHERE idforo = $foro "; 
		$dbForo->exec($sql);
		$dbForo->getrow();
		$idpad=$dbForo->Fields["idpadre"];
		if (($this->idpadre)!=($idpad)){
    		$root="<a href='index.php?idpadre=$idpad'><--| </a>";
        }
		while ($idpad!=0) {
			$etiquetanum++;
			$foro=$idpad;
		    $sql= "SELECT idpadre FROM foros WHERE idforo = $foro "; 
			$dbForo->exec($sql);
			$dbForo->getrow();
			$idpad=$dbForo->Fields["idpadre"];
			$root="<a href='index.php?idpadre=$idpad'><--| </a>".$root;
		 } 
		 if ($root!="nada"){
    		 return $root;
		 }

			
        }

		function borrar_hijos($idforo){
			$db=$this->db;
			$sql=" select idforo from foros where idpadre=$idforo";
			$db->exec($sql);
			$db_delete = $db;
			while ($db->getrow()) {
				$this->borrar_hijos($db->Fields["idforo"]);
				$db_delete->exec("delete from foros where idpadre = ".$db->Fields["idforo"]);
		    
			}  
     		$db_delete->exec("delete from foros where idpadre = ".$idforo);

		}


		
		function dividir_cadena($texto){
			$cadena = $texto;
			$separada = explode (" ", $cadena);
			for($i = 0; $i < count($separada);$i++){

				if(strlen($separada[$i])>30){
						$palabra = $separada[$i];
						$comienzo = substr ($palabra, 0, 30);
						$final = substr ($palabra, 30);
						if (strlen($final)>30){
							$final=$this->dividir_cadena($final);
						}
						$separada[$i] = $comienzo." ".$final;
				}
			}
			$junta = implode (" ", $separada);
			return $junta;
		}


}
?>