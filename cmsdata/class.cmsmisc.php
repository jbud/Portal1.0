<?php
class cmsMisc {
	function curl_get_file_contents($URL) // Get Contents of a URL, (Used by Short URL Service to Tweet Posts.
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);
	 
		if ($contents){
		        return $contents;
		} else {
		        return false;
		}
	}
	function readThemes()
	{
		$DIR = "cmsdata/themes/";
		$themes = array();
		$i = 0;
		if (is_dir($DIR))
		{
			if ($dh = opendir($DIR))
			{
				while (($file = readdir($dh)) !== false)
				{
					if (!is_file($file))
					{
						if (file_exists($DIR.$file."/cmsstyles.css") && file_exists($DIR.$file."/rss.png"))
						{
							$themes[$i] = $file;
							$i++;
						}
					}
				}
			}
		}
		return $themes;
	}
	function getIp()
	{
		if (isset($_SERVER["REMOTE_ADDR"]))    
		{ 
		    $ip = $_SERVER["REMOTE_ADDR"]; 
		} 
		elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{ 
		    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
		}
		elseif (isset($_SERVER["HTTP_CLIENT_IP"]))
		{ 
		    $ip = $_SERVER["HTTP_CLIENT_IP"]; 
		}
	}
}

?>
