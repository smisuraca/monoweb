<?php

$OBJDB_CACHE = array();

class objdb 
{
	var $db;
	var $table;
	var $name;

	var $where;
	var $order_by;

	var $limit_from;
	var $limit_count;

	var $fields;
	var $values;

	var $cache;
	var $cache_ttl;

	var $disable;
	var $joins;

	var $debug;

	function objdb() 
	{
		$this->db			= NULL;

		$this->table		= NULL;
		$this->name			= $this->table;

		$this->where		= NULL;
		$this->order_by		= NULL;

		$this->limit_from	= NULL;
		$this->limit_count	= NULL;

		$this->fields		= array();
		$this->values		= array();
		
		$this->cache		= 1;
		$this->cache_ttl	= 60;

		$this->disable		= array();
		$this->joins		= array();

		$this->debug		= 0;
	}


	function initialize () 
	{
		foreach($this->fields as $value)
			$this->field($value, "");
	}

	function ID_field() 
	{
		return 'id';
	}

	function change_name($name) 
	{
		$this->name = $name;
	}

	function ID($value=NULL) 
	{
		return $this->field($this->ID_field(), $value);
	}

	function field($fieldname, $value=NULL) 
	{
		if (isset($value)) {$this->values[$fieldname] = $value;}
		return $this->values[$fieldname];
	}

	function disableTables($tables)
	{
		foreach($tables as $tablename)
			$this->disable[$tablename] = 1;
	}
	
	function enableTables($tables)
	{
		foreach($tables as $tablename)
			if (array_key_exists($this->disable[$tablename]))
				$this->disable[$tablename] = NULL;
	}
	
	function join($name, $join=NULL)
	{
		if (isset($join)) {$this->joins[$name] = $join;}
		return $self->joins[$name];
	}
	
	#**************************************
	# Methods
	#**************************************
	function &fetch($ID) 
	{
		global $OBJDB_CACHE;
	
		if ($ID > 0)
		{
			if ($this->cache && isset($OBJDB_CACHE[$this->table][$ID]) &&  
			time()-$OBJDB_CACHE[$this->table][$ID][time] <= $this->cache_ttl) 
			{
				if ($this->debug) print "Return from CACHE ".$this->table."\n";
				$OBJDB_CACHE[$this->table][$ID][time] = time();
				$this->values = $OBJDB_CACHE[$this->table][$ID][values];
			} 
			else 
			{
				if ($this->debug) print "Query DB ".$this->table."\n";
				$tables = array($this->table);

				$fields = array();
				foreach($this->fields as $field)
					$fields[] = $this->table.".$field";
					
				$filter = $this->table.".".$this->ID_field()." = '".$ID."'";
	
				$sql  = "SELECT ".join(",", $fields)." FROM ".join(",", $tables)." WHERE $filter";
				$this->db->exec($sql);
				if ($this->debug) print "$sql (Error=".$this->db->Error.")<br>";
				$this->db->getrow();
				if ($this->debug) print "$sql (Error=".$this->db->Error.")<br>";
				$this->values = $this->db->Fields;
				
				$OBJDB_CACHE[$this->table][$ID][values] = $this->values;
				$OBJDB_CACHE[$this->table][$ID][time] 	= time();
			}
		} 
		else 
			$this->initialize();

		foreach($this->joins as $name => $j) 
		{
			$obj = eval('return new '.$j->class.'();');
			$obj->db = $this->db;
			$obj->debug = $this->debug;

			$disable = array_keys($this->disable);
			$disable[] = $this->table;
			$obj->disableTables($disable);

			if (get_class($j) == "foreignkeys" && !$this->disable[$obj->table])
				$this->values[$name] = $obj->fetch($this->values[$j->key]);
			elseif (get_class($j) == "multiplejoin" && !$this->disable[$obj->table])  
			{
				$obj->where = $obj->table.".".$j->key."=".$this->ID();
				if (isset($j->where)) $obj->where = " AND ".$j->where;
				$obj->order_by = $j->order_by;
				$obj->limit_from = $j->limit_from;
				$obj->limit_count = $j->limit_count;
				$this->values[$name] = $obj->fetchall();
			} 
			else
				$this->values[$name] = array();
		}
		return $this->values;
	}
	
	function &fetchall()
	{
		$tables = array($this->table);
		$filter = "1 ";

		foreach($this->joins as $name => $j) 
		{
			if (get_class($j) == "foreignkeys")
			{
				$obj = eval('return new '.$j->class.'();');
				$tables[] = $obj->table;
				$filter .= " AND ".$this->table.".".$j->key."=".$obj->table.".".$obj->ID_field();
			}
		}

		$sql  = "SELECT ".$this->table.".".$this->ID_field();
		$sql .= " FROM ".join(",",$tables);
    		$sql .= " WHERE $filter";
		
		if ($this->where)       { $sql .= " AND ".$this->where;}
		if ($this->order_by)    { $sql .= " ORDER BY ".$this->order_by;}
		if (isset($this->limit_from) && isset($this->limit_count))  
			$sql .= " LIMIT $this->limit_from, $this->limit_count";
		elseif (isset($this->limit_count))  
			$sql .= " LIMIT $this->limit_count";

		$db = clone($this->db);
		$db->exec($sql);
		if ($this->debug) print "$sql (Error=".$this->db->Error.")";
		$rows = array();
		while ($db->getrow())
				$rows[] = $this->fetch($db->Fields[$this->ID_field()]);

		return $rows;
	}

	//*******************************************
	// Realiza las altas y modificaciones
	//*******************************************
	function store() 
	{
		global $OBJDB_CACHE;

		if ($this->ID()>0) { $insert=false; } else { $insert=true;  }
		$first   = true;
		$fields  = "";
		$values  = "";

		if ($insert) 
		{
			$fields=" ( ";
			$values=" values ( ";
		}

		foreach($this->fields as $key) 
		{
			$this->values[$key] = stripslashes($this->values[$key]);
			$this->values[$key] = addslashes($this->values[$key]);
			if ($key!=$this->ID_field())
			{
				if (!$first) {
					$fields  .=",";
					$values  .=",";
				} 
				else 
					$first = false;

				if ($insert) 
				{
					$fields  .= $key;
					$values  .= "'".$this->values[$key]."'";
				} 
				else 
				{
					$fields  .= $key;
					$fields  .= "='".$this->values[$key]."'";
				}
			}
		}

		if ($insert) 
		{
			$fields  .= " ) ";
			$values  .= " ) ";
			$sql      = "INSERT INTO ".$this->table.$fields.$values;
			$this->db->exec($sql);
			if ($this->debug) print "$sql (Error=".$this->db->Error.")";

			if ($this->db->Error) return 0;
			$this->ID($this->db->lastid());

		} 
		else 
		{
			$sql = "UPDATE ".$this->table." SET ".$fields." WHERE ".$this->ID_field()."='".$this->ID()."'";
			$this->db->exec($sql);
			if ($this->debug) print "$sql (Error=".$this->db->Error.")";

			if ($this->db->Error) return 0;
		}
		
		if ($this->cache) 
		{
			if ($this->debug) print "Update CACHE ".$this->table."\n";
			$OBJDB_CACHE[$this->table][$this->ID()][values] = $this->values;
			$OBJDB_CACHE[$this->table][$this->ID()][time] 	= time();
		} 
		return 1;
	}

	function delete() 
	{
		$sql="DELETE FROM ".$this->table." WHERE ".$this->ID_field()."='".$this->ID()."'";
		$this->db->exec($sql);
		if ($this->debug) print "$sql (Error=".$this->db->Error.")";

		if ($this->db->Error) return 0;

		if ($this->cache && isset($OBJDB_CACHE[$this->table][$ID])) 
		{
			if ($this->debug) print "Delete from CACHE ".$this->table."\n";
			$OBJDB_CACHE[$this->table][$this->ID()] = NULL;
		} 

		return 1;
	}


	#*******************************************
	# Devuelve los datos formateados
	#*******************************************
	function doformat($strtemplate, $tplpath=NULL) 
	{
		if (!$tplpath)
			$tplform     = new Template($strtemplate);
		else {
			$tplform     = new Template("");
			$tplform->setFileRoot($tplpath);
			$tplform->Open($strtemplate);
		}

		$tplform->setVars($this->values, $this->name);
		$tplform->setVar("{".$this->name.".where}",        $this->where);
		$tplform->setVar("{".$this->name.".order_by}",     $this->order_by);
		$tplform->setVar("{".$this->name.".limit_from}",   $this->limit_from);
		$tplform->setVar("{".$this->name.".limit_count}",  $this->limit_count);

		return $tplform->Template;
	}

	function doformatall($strtemplate, $tplpath=NULL) 
	{
		if (!$tplpath)
			$tpllist     = new Template($strtemplate);
		else 
		{
			$tpllist     = new Template("");
			$tpllist->setFileRoot($tplpath);
			$tpllist->Open($strtemplate);
		}

		$tpllist->setVars($this->fetchall(), $this->name);
		$tpllist->setVar("{".$this->name.".where}",        $this->where);
		$tpllist->setVar("{".$this->name.".order_by}",     $this->order_by);
		$tpllist->setVar("{".$this->name.".limit_from}",   $this->limit_from);
		$tpllist->setVar("{".$this->name.".limit_count}",  $this->limit_count);
		
		return $tpllist->Template;
	}

	function count()
	{
		$table      = $this->table;
		$name       = $this->table;
		$where      = $this->where;
		$id_field   = $this->ID_field();
		$debug      = $this->debug;
		$db         = $this->db;
	
		$sql  = "SELECT count($name.$id_field) as count ";
		$sql .= " FROM $table as $name";
		$sql .= " WHERE 1 ";
		if ($where) { $sql .= " AND $where";}
	
		$db->exec($sql);
		if ($debug) print "$sql (Error=".$db->Error.")";
		$db->getrow();
		return $db->Fields['count'];
	}

}

class Join
{
	var $class;
	
	function Join($class)
	{
		$this->class = $class;
    }
}

class ForeignKeys extends Join
{
	var $key;
	
	function ForeignKeys($class, $key)
	{
		Join::Join($class);
		$this->key	= $key;
	}
}

class MultipleJoin extends Join
{
	var $key;
	var $where;
	var $order_by;
	var $limit_from;
	var $limit_count;
	
	function MultipleJoin($class, $key)
	{
		Join::Join($class);
		$this->key		= $key;
		$this->where		= NULL;
		$this->order_by		= NULL;
		$this->limit_from	= NULL;
		$this->limit_count	= NULL;
	}
}
?>
