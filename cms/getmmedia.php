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
include("config.inc.php");
require_once($app_includepath."/class.dbMysql.php");

/*
(.asf,.asx) video/x-ms-asf
(.wmv)      video/x-ms-wmv
(.wm)       video/x-ms-wm
(.wmx)      video/x-ms-wmx
(.wvx)      video/x-ms-wvx
(.wmz)      application/x-ms-wmz
(.wmd)      application/x-ms-wmd
(.ra,.ram,.rm) audio/x-pn-realaudio

(.wma)      audio/x-ms-wma
(.wax)      audio/x-ms-wax
.mp3		audio/mp3
.mp2, .mp3	audio/mpeg
.mp3		audio/x-mp3
.mp3		audio/x-mpeg
.mp3		audio/m3u
.mp3		audio/x-m3u


.jpg, .jpeg	image/jpeg
.gif        image/gif
.png        image/png


*/

$db   = new dbMysql($Host["CMS"], $Db["CMS"], $User["CMS"], $Pass["CMS"]);
$db->connect();

$sql =" SELECT links.idlink, links.fname, links.ftype, links.titulo, links_tipos.uploadpath";
$sql.=" from links, links_tipos where links.idlink_tipo=links_tipos.idlink_tipo";
$sql.=" and links.idlink = $idlink";
$db->exec($sql);

if ($db->getrow())
{
	$retpath=$app_uploadpath.$db->Fields['uploadpath'].sprintf("%d", $db->Fields['idlink']/1000)."/".$db->Fields['idlink'];

	$filename = $db->Fields['fname'];

	header("Content-Type: ".$db->Fields['ftype']);
	if (isset($disposition))
		header('Content-Disposition: '.$disposition.';filename="'.$filename.'"');

	readfile($retpath);
}
$db->close();
?>
