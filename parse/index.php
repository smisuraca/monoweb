<?
	$url = $GLOBALS["url"];
	$t = $GLOBALS["type"];

	if(strpos($url, "youtu") !== false) {
	

		preg_match("/(youtu.be\/|\/watch\?v=|\/embed\/)([a-z0-9\-_]+)/i", $url, $matches, 0, 0);
		if($t == "video" or !$t)
			$newurl = "http://www.youtube.com/embed/".$matches[2];
		if($t == "img")
			$newurl = "http://img.youtube.com/vi/$matches[2]/default.jpg";
		if($t == "imgbig")
			$newurl = "http://img.youtube.com/vi/$matches[2]/hqdefault.jpg";

		header("Location: $newurl");

	}

	if(strpos($url, "vimeo") !== false) {
	

		preg_match("/(vimeo.com\/|player.vimeo.com\/video\/)([a-z0-9\-_]+)/i", $url, $matches, 0, 0);


		if($t == "video" or !$t) {
			$newurl = "http://player.vimeo.com/video/".$matches[2];
		} else {

			$xmldata = file_get_contents("http://vimeo.com/api/v2/video/".$matches[2].".xml");

			if($t == "img") {
				preg_match("/<thumbnail_small>(\S+)<\/thumbnail_small>/i", $xmldata, $matches, 0, 0);
				$newurl = $matches[1];
			}

			if($t == "imgbig") {
				preg_match("/<thumbnail_large>(\S+)<\/thumbnail_large>/i", $xmldata, $matches, 0, 0);
				$newurl = $matches[1];
			}

		}

		header("Location: $newurl");

	}



?>

