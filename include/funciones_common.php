<?php

function mkpath($path, $mode = 0700) 
{
	$dirs = explode("/",$path);
	$path = $dirs[0];
	for($i = 1;$i < count($dirs);$i++) 
	{
		$path .= "/".$dirs[$i];
		if(!is_dir($path)) mkdir($path,$mode);
	}
}

function getvalue($var, $default, $save=true)
{
    $result = $default;
    if ($_SESSION[$var]) $result = $_SESSION[$var];
    if ($_GET[$var]) $result = $_GET[$var];
    if ($_POST[$var]) $result = $_POST[$var];
    if ($save) $_SESSION[$var] = $result;
    return $result;
}

?>