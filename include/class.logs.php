<?

class logs {
    var $db;
    var $idlog;
    var $idusuario;
    var $time;
    var $syncok;
    var $text;

    var $where;
    var $order_by;

    //******************************************
    // inicializa el objeto
    //******************************************
    function logs($db, $idlog=0, $idusuario=0, $time="", $syncok="", $text="") {
        $this->db = $db;
        $this->setvalue($idlog, $idusuario, $time, $syncok, $text);
    }

    //******************************************
    // setea los valores
    //******************************************
    function setvalue($idlog=0, $idusuario=0, $time="", $syncok="", $text="") {
        $this->idlog           = $idlog;
        $this->idusuario       = $idusuario;
        $this->time			   = $time;
        $this->syncok          = $syncok;
        $this->text			   = $text;
    }


    //******************************************
    // obtiene los valores desde un idlog
    //******************************************
    function getvalue($idlog) {
        $find = false;

        if ($idlog) {
            $sql  = "SELECT * FROM logs WHERE idlog=$idlog";

            $this->db->exec($sql, 0);
            if ($this->db->getrow()) {
                $find = true;
                $this->idlog           = $this->db->Fields["idlog"];
                $this->idusuario       = $this->db->Fields["idusuario"];
                $this->time			   = $this->db->Fields["time"];
                $this->syncok          = $this->db->Fields["syncok"];
                $this->text			   = $this->db->Fields["text"];
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
                if (!$this->idlog) {
                    $sql = "INSERT INTO logs (idusuario, text) values ($this->idusuario, '$this->text')";
			        $this->db->exec($sql, 0);
                } else {
                    $sql="UPDATE logs SET idusuario=$this->idusuario, time='$this->time', syncok='$this->syncok', text='$this->text'  WHERE idlog=$this->idlog";
			        $this->db->exec($sql, 0);
                }
                break;
            case "borrar":
                $sql="DELETE FROM logs WHERE idlog=$this->idlog";
		        $this->db->exec($sql, 0);
                break;
        }


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

        $tplform->setVar("{logs.idlog}", $this->idlog);
        $tplform->setVar("{logs.idusuario}", $this->idusuario);
        $tplform->setVar("{logs.time}", $this->time);
        $tplform->setVar("{logs.syncok}", $this->syncok);
        $tplform->setVar("{logs.text}", $this->text);
        $tplform->setVar("{logs.where}", $this->where);
        $tplform->setVar("{logs.order_by}", $this->order_by);

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

        $sql  = "SELECT * FROM logs ";
        if ($this->where) {$sql .= "WHERE $this->where ";}
        if ($this->order_by) {$sql .= " ORDER BY $this->order_by";}

        $this->db->exec($sql, 0);

        $strResultado = "";
        $strRow = $tpllist->getBlock('logs.Row', '{BLOCK logs.Row}');

        while ($this->db->getrow()) {
            $this->setvalue($this->db->Fields["idlog"], $this->db->Fields["idusuario"], $this->db->Fields["time"], $this->db->Fields["syncok"], $this->db->Fields["text"]);
            $strResultado .= $this->doformat($strRow, 1);
        }
        $tpllist->setVar('{BLOCK logs.Row}', $strResultado);
        return $tpllist->Template;
    }
}
?>
