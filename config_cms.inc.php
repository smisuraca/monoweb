<?php
/**
 * This source file is part of CEMESE, publishing software.
 *
 * Copyright (C) 2000-2006 Garcia Rodrigo.
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
 */


function clone($obj) {
	return $obj;
}


/**
 * @global string $app_rootpath Ubicación de la aplicación
 */

$app_rootpath       = dirname(__FILE__);

/**
 * @global string $app_includepath Ubicación de las Librerias
 */
$app_includepath    = "$app_rootpath/include";

/**
 * @global string $app_uploadpath Ubicación donde se realizan Uploads
 */
$app_uploadpath     = "$app_rootpath/upload";

/**
 * @global string $app_sessionpath Ubicación de las sesiones 
 */
$app_sessionpath    = "$app_rootpath/sessions";

/**
 * @global hash $DB Configuración de la Base de Datos 
 */
$Host["CMS"]  = "localhost";
$Db["CMS"]    = "cinevivo";
$User["CMS"]  = "root";
$Pass["CMS"]  = "1goshushijo6";

/**
 * @global hash Configuración del servidor de mail 
 */
$SMTP['HOST'] = 'localhost';
$SMTP['PORT'] = 25;

/**
 * @global Constante formateo de fecha y hora
 */
//setlocale ("LC_ALL", "spanish");
$FORMAT_DATE        = "%Y%m%d%H%M%S";
$FORMAT_HOURS       = "%H:%M";

ini_set('include_path', "$app_includepath:".ini_get('include_path'));
session_save_path($app_sessionpath);

require_once("funciones_common.php");

?>
