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

require_once("../config.inc.php");

function handleevent($event)
{
    global $objForos, $objCMSUser;
    
    switch ($event) {
        case "form":
			if (!$objCMSUser->checkPermission("foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objForos->getvalue($objForos->idforo);

		    return $objForos->doformat("/foros/form_abm.html",0);
            break;
        case "form_buscar":
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objContenidos= new contenidos($objForos->db);
			$objContenidos->order_by=" titulo ";
			$objContenidos->where=" comentarios = 'SI' ";
			return $objContenidos->doformatall("/foros/form_buscar.html",0);

/*            $objForos->getvalue($objForos->idforo);
            return $objForos->doformat("/foros/form_buscar.html",0);*/
            break;
        case "form_borrar":
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objContenidos= new contenidos($objForos->db);
			$objContenidos->order_by=" titulo ";
			$objContenidos->where=" comentarios = 'SI' ";
			return $objContenidos->doformatall("/foros/form_borrar.html",0);


            break;
        case "buscar":
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objForos->where = "1";
            if ($GLOBALS["idcontenido"]) {$objForos->where .= " AND idcontenido =". $GLOBALS["idcontenido"];}


            if ($GLOBALS["asunto"]) {$objForos->where .= " AND asunto LIKE '%". $GLOBALS["asunto"]. "%' ";}
            if ($GLOBALS["mensaje"]) {$objForos->where .= " AND mensaje LIKE '%". $GLOBALS["mensaje"]. "%'";}
            if ($GLOBALS["tipo"]) {$objForos->where .= " AND tipo = '". $GLOBALS["tipo"]. "'";}
            if ($GLOBALS["aprobado"]) {$objForos->where .= " AND aprobado = '". $GLOBALS["aprobado"]. "'";}
            return handleevent("list", $objForos, $objCMSUser, $objCMSPermisos);
            break;
        case "borrar":
            if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_borrar")) {
                header("Location: /home/index.php");
                exit(0);
            }
            $ant_where=$objForos->where;
            if (isset($GLOBALS["idforos"])){
                for ($i=0; $i<sizeof ($GLOBALS["idforos"]); $i++){
                    $objForos->getvalue($GLOBALS["idforos"][$i]);
                    $objForos->where =" idforo = ". $GLOBALS["idforos"][$i];
                    $objForos->doabm($event);
                }
            }
            $objForos->where=$ant_where;
            return handleevent("list", $objForos, $objCMSUser, $objCMSPermisos);
            break;

        case "borrar_mas":
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objForos->where = " 1 ";
			if ($GLOBALS["idcontenido"] and ($GLOBALS["asunto"] or $GLOBALS["mensaje"])){
	            if ($GLOBALS["idcontenido"]) {$objForos->where .= " AND idcontenido = ". $GLOBALS["idcontenido"];}
		        if ($GLOBALS["asunto"]) {$objForos->where .= " AND asunto = '". $GLOBALS["asunto"]. "' ";}
			    if ($GLOBALS["mensaje"]) {$objForos->where .= " AND mensaje LIKE '%". $GLOBALS["mensaje"]. "%'";}
	
		        $sql = " select idforo from foros where $objForos->where ";
				$db = $objForos->db;
    			$db->exec($sql);
	            while ($db->getrow()) {
					$objForos->idforo = $db->Fields["idforo"];
			        $objForos->doabm("borrar");
				}
	     		$objForos->where=" idcontenido = ".$GLOBALS["idcontenido"];
				$GLOBALS["asunto"] ="";
				$GLOBALS["mensaje"]="";
     			return handleevent("buscar", $objForos, $objCMSUser, $objCMSPermisos);
			}
			else{
				$objForos->where=" idcontenido = ".$GLOBALS["idcontenido"];
				$GLOBALS["asunto"] ="";
				$GLOBALS["mensaje"]="";
	     	    return handleevent("form_borrar", $objForos, $objCMSUser, $objCMSPermisos);
			
			}
            break;
        case "grabar":
			if ($objForos->idforo) {$permiso = "foros_modificar";}else{$permiso = "foros_modificar";}
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, $permiso)) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objForos->doabm($event);
            return handleevent("list", $objForos, $objCMSUser, $objCMSPermisos);
            break;
        case "list":
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            return $objForos->doformatall("/foros/list.html");
            break;
        case "list_foros":
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
			$objContenidos = new contenidos($objForos->db);
			$objContenidos->where=" comentarios = 'SI' ";
            return $objContenidos->doformatall("/foros/list_foros.html");
            break;
		case "ordenar":
			if (!$objCMSPermisos->checkPermission($objCMSUser->idusuario, "foros_lectura")) {
				header("Location: /home/index.php");
				exit(0);
			}
            $objForos->order_by = $GLOBALS["order_by"];
            return handleevent("list", $objForos, $objCMSUser, $objCMSPermisos);
            break;
        default:
            return handleevent("list", $objForos, $objCMSUser, $objCMSPermisos);
    }
}

if (!$HTTP_SESSION_VARS["usuarios_idusuario"]) {
	header("Location: ../home/");
	exit(0);
}

$dbCMS   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$dbCMS->connect();

$tplmarco = new Template("");
$tplmarco->setFileRoot($TEMPLATE_ROOT);
$tplmarco->Open("/marco.html");

$objForos = new foros($dbCMS, $idforo, $idcontenido, $idusuario, $idusuario_to, $idpadre, $asunto, $mensaje, $fecha, $ip, $tipo, $aprobado);
$objForos->where     = urldecode(stripslashes($HTTP_COOKIE_VARS["foros_where"]));
$objForos->order_by  = urldecode(stripslashes($HTTP_COOKIE_VARS["foros_order_by"]));

$objCMSUser = new usuarios($dbCMS);
$objCMSUser->getvalue($HTTP_COOKIE_VARS["usuarios_idusuario"]);

$objCMSPermisos = new permisos($dbCMS);


$tplmarco->setVar("{titulo}", "ABM Foros");
$tplmarco->setVar("{base}", $Db["CMS"]);
$tplmarco->setVar("{contenido}", handleevent($event, $objForos, $objCMSUser, $objCMSPermisos));


setcookie("foros_where",$objForos->where, 0, "/");	
setcookie("foros_order_by",$objForos->order_by, 0, "/");	

print($tplmarco->Template);
$dbCMS->close();

$foros_where = $objForos->where;
$foros_order_by = $objForos->order_by;



?>
