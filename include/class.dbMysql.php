<?

class dbMysql {
	var $idConnection = "";
	var $idResult = 0;
	var $isOpen = 0;
	var $Error = "";

	var $Host;
	var $Db;
	var $User;
	var $Password;
	var $Fields;
	var $LastId;
	/* Inicializador */
	function initialize($Host, $Db, $User="", $Password="")
	{
		$this->Host	= $Host;
		$this->Db 	= $Db;
		$this->User	= $User;
		$this->Password = $Password;
		$this->isOpen 	= 0;
		$this->Error 	= "";
	}

	/* Constructor */
	function dbMysql($Host, $Db, $User="", $Password="")
	{
		$this->Host	= $Host;
		$this->Db 	= $Db;
		$this->User	= $User;
		$this->Password = $Password;
		$this->isOpen 	= 0;
		$this->Error 	= "";
	}


	/* Abre una conexion con una base de datos */
	function connect()
	{
		if (!$this->isOpen) {
			$this->idConnection = mysql_connect($this->Host, $this->User, $this->Password);
			$this->Error = "";
			$this->isOpen = 1;
			
			mysql_select_db($this->Db, $this->idConnection);
			
			return $this->idConnection;
		} else {
			$this->Error = "ERROR>>connect() [la conexion esta abierta]";
			return 0;
		}
	}


	/* Cierra una conexion con una base de datos */
	function close()
	{
		if ($this->isOpen) {
			if ($this->idResult) {
				mysql_free_result($this->idResult);
				$this->idResult = NULL;
			}

			mysql_close($this->idConnection);
			$this->Error = "";
			$this->isOpen = 0;
			return 1;
		} else {
			$this->Error = "ERROR>>close() [la conexion esta cerrada]";
			return 0;
		}
	}


	/* Ejecuta una consulta */
	function exec($sql, $islog=1) {
		if ($this->isOpen) {

			$this->idResult = mysql_query($sql, $this->idConnection);

			if ($this->idResult) {
				$this->Error = "";
				return $this->idResult;
			} else {
				$this->Error = "ERROR>>exec() [fallo al realizar la consulta]".mysql_error();
				return 0;
			}
		} else {
			$this->Error = "ERROR>>exec() [la conexion esta cerrada]".mysql_error();
			return 0;
		}
	}


	/* Obtiene un registro da la consulta */
	function getrow() {
		if ($this->isOpen) {
			if ($this->idResult) {
				$this->Error = "";
				$this->Fields = mysql_fetch_assoc($this->idResult);
				
				$result = $this->Fields;
				
				if ($result === FALSE && $this->idResult) {
					mysql_free_result($this->idResult);
					$this->idResult = NULL;
				}

				return $result;
			} else {
				$this->Error = "ERROR>>getrow() [debe realizar una consulta primero]";
				return 0;
			}
		} else {
			$this->Error = "ERROR>>getrow() [la conexion esta cerrada]";
			return 0;
		}
	}


	/* Obtiene el valor de un determinado campo */
	function getfield($fieldname) {
		if ($this->isOpen) {
			if ($this->idResult) {
				$this->Error = "";
				return $this->Fields[$fieldname];
			} else {
				$this->Error = "ERROR>>getfield() [debe realizar una consulta primero]";
				return 0;
			}
		} else {
			$this->Error = "ERROR>>getfield() [la conexion esta cerrada]";
			return 0;
		}
	}

	/* Obtiene el ultimo id insertado */
	function lastid() {
		if ($this->isOpen) {
			if ($this->idResult) {
				$this->Error = "";
				$this->LastId = mysql_insert_id($this->idConnection);
				return $this->LastId;
			} else {
				$this->Error = "ERROR>>lastid() [debe realizar una consulta primero]";
				return 0;
			}
		} else {
			$this->Error = "ERROR>>lastid() [la conexion esta cerrada]";
			return 0;
		}
	}

	/* Devuelve la cantidad de filas en el resultado de la consulta */
	function numrows() {
		if ($this->isOpen) {
			if ($this->idResult) {
				$this->Error = "";
				return mysql_affected_rows($this->idConnection);
			} else {
				$this->Error = "ERROR>>numrows() [debe realizar una consulta primero]";
				return 0;
			}
		} else {
			$this->Error = "ERROR>>numrows() [la conexion esta cerrada]";
			return 0;
		}
	}

	
}
?>
