<?
require_once($app_includepath."/class.preguntas.php");
require_once($app_includepath."/class.opciones.php");

class votos {
    var $db;
    var $idvoto;
    var $idopcion;
    var $idpregunta;
    var $remote_ip;
	var $time;
	var $idusuario;

	var $total_votos;
	var $votos;
	var $porcentaje;

    var $where;
    var $order_by;

    //******************************************
    // inicializa el objeto
    //******************************************
    function votos($db, $idvoto=0, $idopcion=0, $idpregunta=0, $remote_ip="", $time="", $idusuario=0) {
        $this->db = $db;
        $this->setvalue($idvoto, $idopcion, $idpregunta, $remote_ip, $time, $idusuario);
    }

    //******************************************
    // setea los valores
    //******************************************
    function setvalue($idvoto=0, $idopcion=0, $idpregunta=0, $remote_ip="", $time="", $idusuario=0) {
        $this->idvoto      = isset($idvoto) ? $idvoto : 0;
        $this->idopcion    = isset($idopcion) ? $idopcion : 0;
        $this->idpregunta  = isset($idpregunta) ? $idpregunta : 0;
        $this->remote_ip   = isset($remote_ip) ? $remote_ip : "";
        $this->time        = isset($time) ? $time : "";
        $this->idusuario   = isset($idusuario) ? $idusuario : 0;
    }


    //******************************************
    // obtiene los valores desde un idpregunta
    //******************************************
    function getvalue($idvoto) {
        $find = false;

        if ($idvoto) {
            $sql  = "SELECT * FROM votos WHERE idvoto=$idvoto ";

            $this->db->exec($sql);
            if ($this->db->getrow()) {
                $find = true;
                $this->idvoto       = $this->db->Fields["idvoto"];
                $this->idopcion     = $this->db->Fields["idopcion"];
                $this->idpregunta   = $this->db->Fields["idpregunta"];
                $this->remote_ip    = $this->db->Fields["remote_ip"];
                $this->time			= $this->db->Fields["time"];
                $this->idusuario    = $this->db->Fields["idusuario"];
            }
        }

        if (!$find) { $this->setvalue(); }
    }


    //******************************************
    // 
    //******************************************
	function gettotal_votos() {
		$this->db->exec("SELECT count(*) as total_votos FROM votos WHERE $this->where");
		$this->db->getrow();
		$this->total_votos = $this->db->Fields["total_votos"];
		return $this->total_votos;
	}

    //******************************************
    // 
    //******************************************
	function getvotos() {
		$this->db->exec("SELECT count(*) as votos FROM votos WHERE $this->where");
		$this->db->getrow();
		$this->votos = $this->db->Fields["votos"];
		return $this->votos;
	}


    //******************************************
    // 
    //******************************************
	function getporcentaje() {
        if ($this->votos>0) {
                $this->porcentaje = sprintf("%d", ($this->votos/$this->total_votos)*100);
        } else {
                $this->porcentaje = sprintf("%d", 0);
        }
		return $this->porcentaje;
	}

	
	
	//*******************************************
    // Realiza las altas, bajas y modificaciones
    //*******************************************
    function doabm($action) {
        switch ($action) {
            case "grabar":
                if (!$this->idvoto) {
                    $sql = "INSERT INTO votos ( idopcion, idpregunta, remote_ip, time, idusuario) values($this->idopcion, $this->idpregunta, '$this->remote_ip', '$this->time', $this->idusuario)";
                } else {
                    $sql="UPDATE opciones SET idopcion= $this->idopcion, idpregunta= $this->idpregunta, remote_ip= '$this->remote_ip', time= '$this->time', idusuario=$this->idusuario WHERE idvoto=$this->idvoto";
                }
                break;
            case "borrar":
                $sql="DELETE FROM votos WHERE idvoto=$this->idvoto ";
                break;
        }

        $this->db->exec($sql);
        if( $this->db->Error ){return 0; }else{ return 1;}
    }


    //*******************************************
    // Devuelve los datos formateados de un area
    // en particular
    //*******************************************
    function doformat($strtemplate, $intisstr=0) {
        if ($intisstr) {
            $tplform = new Template($strtemplate);
        } else {
            $tplform = new Template("");
            $tplform->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
            $tplform->Open($strtemplate);
        }

        $tplform->setVar("{votos.idvoto}", $this->idvoto);
        $tplform->setVar("{votos.idopcion}", $this->idopcion);
		$tplform->setVar("{votos.idpregunta}", $this->idpregunta);
		$tplform->setVar("{votos.remote_ip}", $this->remote_ip);
        $tplform->setVar("{votos.time}", $this->time);
        $tplform->setVar("{votos.total_votos}", $this->total_votos);
        $tplform->setVar("{votos.votos}", $this->votos);
        $tplform->setVar("{votos.porcentaje}", $this->porcentaje);
        $tplform->setVar("{votos.idusuario}", $this->idusuario);

		$tplform->setVar("{votos.where}", $this->where);
        $tplform->setVar("{votos.order_by}", $this->order_by);


        $objpreguntas = new preguntas($this->db);
        $objpreguntas->getvalue($this->idpregunta);
        $tplform->Template = $objpreguntas->doformat($tplform->Template, 1);

		$objopciones = new opciones($this->db);
        $objopciones->getvalue($this->idopcion);
        $tplform->Template = $objopciones->doformat($tplform->Template, 1);

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

        $sql  = "SELECT * FROM votos ";
        if ($this->where) {$sql .= "WHERE $this->where ";}
        if ($this->order_by) {$sql .= " ORDER BY $this->order_by";}

        $this->db->exec($sql);

        $strResultado = "";
        $strRow = $tpllist->getBlock('votos.Row', '{BLOCK votos.Row}');

        while ($this->db->getrow()) {
            $this->setvalue($this->db->Fields["idvoto"], $this->db->Fields["idopcion"], $this->db->Fields["idpregunta"],$this->db->Fields["remote_ip"], $this->db->Fields["time"] );
            $strResultado .= $this->doformat($strRow, 1);
        }

        $tpllist->setVar('{BLOCK votos.Row}', $strResultado);

        return $tpllist->Template;
    }



	//**********************************************************
	function dobars($template, $intisstr=0) {
			if ($intisstr) {
				$tpllist = new Template($template);
			} else {
				$tpllist = new Template("");
				$tpllist->setFileRoot($GLOBALS["TEMPLATE_ROOT"]);
				$tpllist->Open($template);
			}

		$this->where = " idpregunta=$this->idpregunta ";
		$this->gettotal_votos();


		$objopciones = new opciones($this->db);
        $objopciones->where = "idpregunta=$this->idpregunta";
		$objopciones->db->exec("SELECT idopcion FROM opciones WHERE ".$objopciones->where);

        $strResultado = "";
        $strRow = $tpllist->getBlock('votos.Row', '{BLOCK votos.Row}');

        while ($objopciones->db->getrow()) {
            $this->setvalue(0, $objopciones->db->Fields["idopcion"], $this->idpregunta, 0, 0);
			$this->where = " idpregunta=$this->idpregunta and idopcion=$this->idopcion ";
			$this->getvotos();
			$this->getporcentaje();
            $strResultado .= $this->doformat($strRow, 1);
        }

        $tpllist->setVar('{BLOCK votos.Row}', $strResultado);

        return $tpllist->Template;
	}
}
?>
