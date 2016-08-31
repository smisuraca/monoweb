<?php
/**
 * This source file is part of 3WaySolutions, software.
 *
 * Copyright (C) 2000-2007 Garcia Rodrigo.
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

require_once("class.objdb.php");

/**
 * Definicion de la clase de Usuarios
 * Relationships: usuarios_grupos.idusuario
**/
class usuarios extends objdb 
{
	function usuarios()
	{
		objdb::objdb();
		$this->table    = 'usuarios';
		$this->name     = $this->table;
		$this->fields   = array('idusuario', 'username', 'password', 'email', 'nombre', 'apellido');
		$this->join('grupos', new MultipleJoin('Usuarios_Grupos', 'idusuario'));
		$this->initialize();
	}

	function ID_field() {
		return 'idusuario';
	}
	
	function validateUser($username, $password) {
		$sql = "SELECT ".$this->ID_field()." FROM usuarios WHERE username='$username' AND password='$password'";
		$this->db->exec($sql);
		if ($this->debug) print "$sql (Error=".$this->db->Error.")";
		if ($this->db->getrow())
		{
			if ($this->debug) print "$sql (Error=".$this->db->Error.")";
			$this->fetch($this->db->Fields[$this->ID_field()]);
			return 1;
		}
		else
		{
			if ($this->debug) print "$sql (Error=".$this->db->Error.")";
			$this->initialize();
			return 0;
		}
	}
	
	function checkPermission($nombre="") 
	{
		$sql = "SELECT count(*) as permisook FROM usuarios as u, usuarios_grupos as ug, grupos_permisos as gp, permisos as p WHERE u.idusuario=ug.idusuario AND ug.idgrupo=gp.idgrupo AND gp.idpermiso=p.idpermiso AND u.idusuario='".$this->ID()."' AND p.nombre='$nombre'";
		$this->db->exec($sql);
		if ($this->debug) print "$sql (Error=".$this->db->Error.")";

		if ($this->db->getrow() && $this->db->Fields["permisook"]>0) { return 1;} else { return 0;}
	}

	function fetch($ID)
	{
		objdb::fetch($ID);
		$this->values['fullname'] = $this->values['apellido'].", ".$this->values['nombre'];
		$tmp = split("@", $this->values['username']);
		$this->values['user'] = $tmp[0];
		return $this->values;
	}

	function delete()
	{
		$tmp = new usuarios_grupos();
		$tmp->db = $this->db;
		$tmp->where = "idusuario=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}

		objdb::delete();
	}
}

/**
 * Definicion de la clase de Permisos
 * Relationships: grupos_permisos.idpermiso
**/
class permisos extends objdb
{
	function permisos(){
		objdb::objdb();
		$this->table    = 'permisos';
		$this->name     = $this->table;
		$this->fields   = array('idpermiso', 'nombre', 'descripcion');
		$this->join('grupos', new MultipleJoin('grupos_permisos', 'idpermiso'));
		$this->initialize();
	}
	
	function ID_field() {
		return 'idpermiso';
	}
	
	function delete() 
	{
		$tmp = new grupos_permisos();
		$tmp->db = $this->db;
		$tmp->where = "idpermiso=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}
		
		objdb::delete();		
	}
}

/**
 * Definicion de la clase de Grupos
 * Relationships: usuarios_grupos.idgrupo, grupos_permisos.idgrupo
**/
class grupos extends objdb 
{
	function grupos(){
		objdb::objdb();
		$this->table    = 'grupos';
		$this->name     = $this->table;
		$this->fields   = array('idgrupo', 'nombre');
		$this->join('permisos', new MultipleJoin('grupos_permisos', 'idgrupo'));
		$this->join('usuarios', new MultipleJoin('usuarios_grupos', 'idgrupo'));
		$this->initialize();
	}
	
	function ID_field() {
		return 'idgrupo';
	}

	function delete() 
	{
		$tmp = new usuarios_grupos();
		$tmp->db = $this->db;
		$tmp->where = "idgrupo=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}
		
		$tmp = new grupos_permisos();
		$tmp->db = $this->db;
		$tmp->where = "idgrupo=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}

		objdb::delete();
	}
}

/**
 * Definicion de la clase de Usuarios_Grupos
 * Relationships: usuarios.idusuario, grupos.idgrupo
**/
class usuarios_grupos extends objdb
{
	function usuarios_grupos(){
		objdb::objdb();
		$this->table    = 'usuarios_grupos';
		$this->name     = $this->table;
		$this->fields   = array('id', 'idusuario', 'idgrupo');
		$this->join('grupo', new ForeignKeys('Grupos', 'idgrupo'));
		$this->join('usuario', new ForeignKeys('Usuarios', 'idusuario'));
		$this->initialize();
	}
}

/**
 * Definicion de la clase de Grupos_Permisos
 * Relationships: grupos.idgrupo, permisos.idpermiso
**/
class grupos_permisos extends objdb 
{
	function grupos_permisos(){
		objdb::objdb();
		$this->table    = 'grupos_permisos';
		$this->name     = $this->table;
		$this->fields   = array('id', 'idpermiso', 'idgrupo');
		$this->join('grupo', new ForeignKeys('Grupos', 'idgrupo'));
		$this->join('permiso', new ForeignKeys('Permisos', 'idpermiso'));
		$this->initialize();
	}
}

/**
 * Definicion de la clase de Areas
 * Relationships: Publicacion
**/
class areas extends objdb
{
	function areas()
	{
		objdb::objdb();
		$this->table    = 'areas';
		$this->name     = $this->table;
		$this->fields   = array('idarea', 'idweb', 'descripcion');
		$this->join('publicacion', new multiplejoin('publicacion', 'idarea'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idarea';
	}

	function delete()
	{
		$tmp = new publicacion();
		$tmp->db = $this->db;
		$tmp->joins = array();
		$tmp->where = "idarea=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}
		objdb::delete();
	}
}

/**
 * Definicion de la clase de Publicacion
 * Relationships: Contenidos, Areas
**/
class publicacion extends objdb
{
	function publicacion(){
		objdb::objdb();
		$this->table    = 'publicacion';
		$this->name     = $this->table;
		$this->fields   = array('idpublicacion', 'idcontenido', 'idarea', 'fecha_publicacion', 'destacada', 'orden');
		$this->join('area', new foreignkeys('areas', 'idarea'));
		$this->join('contenido', new foreignkeys('contenidos', 'idcontenido'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idpublicacion';
	}

	function initialize()
	{
		objdb::initialize();
		$this->values['fecha_publicacion'] = '00000000000000';
	}

	function &fetch($ID)
	{
		objdb::fetch($ID);
		if (eregi("([0-9]{4}).*([0-9]{2}).*([0-9]{2}).*([0-9]{2}).*([0-9]{2}).*([0-9]{2})", $this->values['fecha_publicacion'], $regs))
			$this->values['fecha_publicacion.nice'] = sprintf(
					"%s-%s-%s %s:%s:%s", 
					$regs[1], 
					$regs[2], 
					$regs[3], 
					$regs[4],
					$regs[5],
					$regs[6]
			);
		else 
			$this->values['fecha_publicacion.nice'] = "0000-00-00 00:00:00";
		
		$this->values['fecha_publicacion_nice'] = $this->values['fecha_publicacion.nice'];

		return $this->values;
	}

	function store()
	{
		$this->values['fecha_publicacion'] = str_replace(' ', '', $this->values['fecha_publicacion']);
		$this->values['fecha_publicacion'] = str_replace(':', '', $this->values['fecha_publicacion']);
		$this->values['fecha_publicacion'] = str_replace('-', '', $this->values['fecha_publicacion']);
		objdb::store();
	}
}


/**
 * Definicion de la clase de Contenidos
 * Relationships: Contenidos Tipos
**/
class contenidos extends objdb
{
	function contenidos(){
		objdb::objdb();
		$this->table    = 'contenidos';
		$this->name     = $this->table;
		$this->fields   = array(
							'idcontenido',
							'idcontenido_tipo',
							'fecha',
							'titulo',
							'titulo_corto',
							'volanta',
							'bajada',
							'texto',
							'extra1',
							'extra2',
							'extra3',
							'extra4',
							'comentarios',
							'palabras'
						);

		$this->join('tipo', new foreignkeys('contenidos_tipos', 'idcontenido_tipo'));
		$this->join('mmedias', new multiplejoin('contenidos_links', 'idcontenido'));
		$this->join('opciones', new multiplejoin('opciones', 'idcontenido'));
		$this->join('publicacion', new multiplejoin('publicacion', 'idcontenido'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idcontenido';
	}

	function initialize()
	{
		objdb::initialize();
		$this->values['fecha'] = '00000000000000';
	}

	function &fetch($ID)
	{
		objdb::fetch($ID);
		if (eregi("([0-9]{4}).*([0-9]{2}).*([0-9]{2}).*([0-9]{2}).*([0-9]{2}).*([0-9]{2})", $this->values['fecha'], $regs))
			$this->values['fecha.nice'] = sprintf(
					"%s-%s-%s %s:%s:%s", 
					$regs[1], 
					$regs[2], 
					$regs[3], 
					$regs[4],
					$regs[5],
					$regs[6]
			);
		else
			$this->values['fecha.nice'] = "0000-00-00 00:00:00";

		return $this->values;
	}

	function store()
	{
		$this->values['fecha'] = str_replace(' ', '', $this->values['fecha']);
		$this->values['fecha'] = str_replace(':', '', $this->values['fecha']);
		$this->values['fecha'] = str_replace('-', '', $this->values['fecha']);
		objdb::store();

// 		$this->enableTables(array('contenidos_links','links'));

		echo $this->ID();
		objdb::fetch($this->ID());

		$palabras = array();
		foreach($this->values['mmedias'] as $row) {
			array_push($palabras, $row['mmedia']['titulo']);
			
			
		}
		$this->values['palabras'] = join(" ", $palabras);
// 		print_r($palabras);
		objdb::store();
	}
}

/**
 * Definicion de la clase de Contenidos
 * Relationships: Contenidos Tipos
**/
class contenidos_tipos extends objdb
{
	function contenidos_tipos(){
		objdb::objdb();
		$this->table    = 'contenidos_tipos';
		$this->name     = $this->table;
		$this->fields   = array(
							'idcontenido_tipo',
							'descripcion',
							'extra1_etiqueta',
							'extra1_valores',
							'extra2_etiqueta',
							'extra2_valores',
							'extra3_etiqueta',
							'extra3_valores'
						);

		$this->join('contenidos', new multiplejoin('contenidos', 'idcontenido'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idcontenido_tipo';
	}
}


/**
 * Definicion de la clase de Contenidos Links
 * Relationships: Links, Contenidos
**/
class contenidos_links extends objdb
{
	function contenidos_links(){
		objdb::objdb();
		$this->table    = 'contenidos_links';
		$this->name     = $this->table;
		$this->fields   = array('id', 'idcontenido', 'idlink');
		$this->join('contenido', new ForeignKeys('contenidos', 'idcontenido'));
		$this->join('mmedia', new ForeignKeys('links', 'idlink'));
		$this->initialize();
	}
}


/**
 * Definicion de la clase de Links
 * Relationships: Links Tipos
**/
class links extends objdb
{
	function links(){
		objdb::objdb();
		$this->table    = 'links';
		$this->name     = $this->table;
		$this->fields   = array(
							'idlink',
							'idlink_tipo',
							'titulo',
							'url',
							'orden',
							'url_foto',
							'ftype',
							'fname',
						);

		$this->join('tipo', new foreignkeys('links_tipos', 'idlink_tipo'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idlink';
	}

	function delete()
	{
		$dir = $this->values["uploadpath"];
		$this->fetch($this->ID());
		$dir .= $this->values["tipo"]["uploadpath"].sprintf("%d",$this->ID()/1000);
		$file = $this->ID();

		if (file_exists("$dir/$file")) unlink("$dir/$file");

		$tmp = new contenidos_links();
		$tmp->db = $this->db;
		$tmp->joins = array();
		$tmp->where = "idlink=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}

		objdb::delete();

	}

	function store()
	{
		objdb::store();
		
		if ($this->values["file"] && is_uploaded_file($this->values["file"]) && $this->values["uploadpath"])
		{
			$this->fetch($this->ID());
			$dir = $this->values["uploadpath"].$this->values["tipo"]["uploadpath"].sprintf("%d", $this->ID()/1000);
			$file = $this->ID();

			if (!is_dir($dir)) mkpath($dir, 0770);

			if(file_exists("$dir/$file")) {
				unlink("$dir/$file");
			}

			move_uploaded_file($this->values["file"], "$dir/$file");
			
			chmod("$dir/$file", 0660);
		}
	}
}


/**
 * Definicion de la clase de Links Tipos
 * Relationships: Links
**/
class links_tipos extends objdb
{
	function links_tipos(){
		objdb::objdb();
		$this->table    = 'links_tipos';
		$this->name     = $this->table;
		$this->fields   = array(
							'idlink_tipo',
							'descripcion',
							'defaultpath',
							'target',
							'uploadpath'
						);

		$this->join('links', new multiplejoin('links', 'idlink_tipo'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idlink_tipo';
	}

	function delete()
	{
		$tmp = new links();
		$tmp->db = $this->db;
		$tmp->joins = array();
		$tmp->where = "idlink_tipo=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}
		objdb::delete();
	}
}

/**
 * Definicion de la clase de Opciones
 * Relationships: Contenidos
**/
class opciones extends objdb
{
	function opciones(){
		objdb::objdb();
		$this->table    = 'opciones';
		$this->name     = $this->table;
		$this->fields   = array(
							'idopcion',
							'idcontenido',
							'descripcion',
							'opcion_correcta'
						);
		$this->initialize();
	}

	function ID_field()
	{
		return 'idopcion';
	}

	function &fetch($ID)
	{
		objdb::fetch($ID);

		$sql  = "SELECT count(*) as votos FROM votos ";
		$sql .= "WHERE idcontenido='".$this->values['idcontenido']."' AND idopcion='".$this->ID()."'";
		$this->db->exec($sql);
		$this->db->getrow();
		$this->values['votos'] = $this->db->Fields["votos"];

		$sql  = "SELECT count(*) as votos_total FROM votos ";
		$sql .= "WHERE idcontenido='".$this->values['idcontenido']."'";
		$this->db->exec($sql);
		$this->db->getrow();
		$this->values['votos_total'] = $this->db->Fields["votos_total"];

		if ($this->values['votos'] > 0)
				$this->values['votos_porcentaje'] = sprintf("%d", ($this->values['votos']/$this->values['votos_total'])*100);
		else
				$this->values['votos_porcentaje'] = sprintf("%d", 0);

		return $this->values;
	}

	function delete()
	{
		$tmp = new votos();
		$tmp->db = $this->db;
		$tmp->joins = array();
		$tmp->where = "idopcion=".$this->ID();
		foreach ($tmp->fetchall() as $row)
		{
			$tmp->ID($row[$tmp->ID_field()]);
			$tmp->delete();
		}
		objdb::delete();
	}
}

/**
 * Definicion de la clase de Votos
 * Relationships: Contenidos, Opciones, Usuarios
**/
class votos extends objdb
{
	function votos(){
		objdb::objdb();
		$this->table    = 'votos';
		$this->name     = $this->table;
		$this->fields   = array(
							'idvoto',
							'idcontenido',
							'idopcion',
							'remote_ip',
							'time',
							'idusuario'
						);

		$this->join('usuario', new foreignkeys('usuarios', 'idusuario'));
		$this->join('opcion', new foreignkeys('opciones', 'idopcion'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idlink_tipo';
	}
}

/**
 * Definicion de la clase de Foros
 * Relationships: Contenidos Tipos
**/
class comentarios extends objdb
{
	function comentarios(){
		objdb::objdb();
		$this->table    = 'foros';
		$this->name     = $this->table;
		$this->fields   = array(
			'idforo',
			'idcontenido',
			'mensaje',
			'fecha',
			'nombre',
			'mail'
		);

		$this->join('contenido', new foreignkeys('contenidos', 'idcontenido'));
		$this->initialize();
	}

	function ID_field()
	{
		return 'idforo';
	}
}

/**
 * Definicion de la clase de Ficha
 * Relationships: 
**/
class ficha extends objdb
{
	function ficha(){
		objdb::objdb();
		$this->table    = 'ficha';
		$this->name     = $this->table;
		$this->fields   = array(
			'id',
			'titulo',
			'titulo_en',
			'duracion',
			'genero',
			'formato_realizacion',
			'formato_proyeccion',
			'idioma',
			'subtitulo_es',
			'subtitulo_en',
			'anno_realizacion',
			'lugar_realizacion',
			'web',
			'premios',
			'sinopsis_es',
			'sinopsis_en',
			'direccion',
			'guion',
			'produccion',
			'asistente_direccion',
			'fotografia',
			'camara',
			'arte',
			'musica',
			'sonido',
			'edicion',
			'animacion',
			'productora',
			'interpretes',
			'director_nombre',
			'director_domicilio',
			'director_codigo_postal',
			'director_telefono',
			'director_mail',
			'director_nacionalidad',
			'director_fecha_nac',
			'url',
			'url_cinevivo',
			'url_cinenacional',
			'url_kane',
			'image_gif',
			'reproducciones',
			'fecha',
			'publicacion',
			'material'
		);
	

		$this->initialize();
	}

	function &fetch($ID)
	{
		objdb::fetch($ID);

		$this->values['material_nice'] = "Corto";
		if($this->values['material'] == 1) $this->values['material_nice'] = "Largo";
		if($this->values['material'] == 2) $this->values['material_nice'] = "VOD";
		
		$this->values['genero_nice'] = "Ficci&oacute;n";
		if($this->values['genero'] == 2) $this->values['genero_nice'] = "Documental";
		if($this->values['genero'] == 3) $this->values['genero_nice'] = "Experimental";
		if($this->values['genero'] == 4) $this->values['genero_nice'] = "Animaci&oacute;n";
		if($this->values['genero'] == 5) $this->values['genero_nice'] = "Videoclip";
		if($this->values['genero'] == 6) $this->values['genero_nice'] = "Videominuto";
		if($this->values['genero'] == 7) $this->values['genero_nice'] = "Trailer";
		
		$this->values['titulo'] = ucfirst($this->values['titulo']);

		$this->values['publicacion_nice'] = "NO";
		if($this->values['publicacion'] == 1) $this->values['publicacion_nice'] = "SI";
		if($this->values['publicacion'] == 2) $this->values['publicacion_nice'] = "HOME";
		if($this->values['publicacion'] == 3) $this->values['publicacion_nice'] = "MAIN";
		
		return $this->values;
	}

}
