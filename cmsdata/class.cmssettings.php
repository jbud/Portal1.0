<?php
class cmsSettings {
	function getSettings()
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM settingsb WHERE id='1'";
		$return = $cmsDatabase->db($query, true);
		$query = "SELECT link FROM links ORDER BY id ASC";
		$linkz = $cmsDatabase->db($query, true, true);
		$query = "SELECT COUNT(*) FROM links";
		$linkc = $cmsDatabase->db($query, true);
		$links = array();
		$query = "SELECT * FROM pages ORDER BY id ASC";
		$pagez = $cmsDatabase->db($query, true, true);
		$query = "SELECT COUNT(*) FROM pages";
		$pagec = $cmsDatabase->db($query, true);
		$pages = array();
		for($i=0;$i<=$pagec[0] - 1;$i++)
		{
			$pages[$i] = array();
			$pages[$i]['id'] = $pagez[$i]['id'];
			$pages[$i]['name'] = $pagez[$i]['name'];
			$pages[$i]['active'] = $pagez[$i]['active'];
		}
		for($i=0;$i<=$linkc[0] - 1;$i++)
		{
			//$links[$i] = $linkz[$i]['link'];
            $links[$i] = array();
            $links[$i]['id'] = $linkz[$i]['id'];
            $links[$i]['link'] = $linkz[$i]['link'];
		}

		$cmsSiteName = $return['sitename'];
		$cmsTagLine = $return['tagline'];
		$cmsSiteAddress = $return['siteaddress'];
		$cmsSupportEmail = $return['supportemail'];
		$cmsAboutText = $return['abouttext'];
		$cmsAdScript = $return['adscript'];
		$cmsSiteNote = $return['sitenote'];
		$cmsSiteTheme = $return['theme'];
		return array(
			'sitename'=>$cmsSiteName, 'tagline'=>$cmsTagLine, 'siteaddress'=>$cmsSiteAddress,
			'supportemail'=>$cmsSupportEmail, 'abouttext'=>$cmsAboutText, 'adscript'=>$cmsAdScript,
			'sitenote'=>$cmsSiteNote, 'theme'=>$cmsSiteTheme, 'links'=>$links, 'pages'=>$pages
		);
	}
	function editSettings($settingsSiteName, $settingsAboutText, $settingsAdScript, $settingsSiteNote, $settingsTagLine, $settingsSiteAddress, $settingsSupportEmail, $settingsSiteTheme)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE settingsb SET sitename='$settingsSiteName', abouttext='".$settingsAboutText."', adscript='".$settingsAdScript."', sitenote='".$settingsSiteNote."', tagline = '".$settingsTagLine."', siteaddress = '".$settingsSiteAddress."', supportemail = '".$settingsSupportEmail."', theme = '".$settingsSiteTheme."' WHERE id='1'";
		$d = $cmsDatabase->db($query);
		return !$d ? false : true;
	}
	function editLink($update, $i)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE links SET link='$update' WHERE id='$i'";
		$d = $cmsDatabase->db($query);
		return !$d ? false : true;
	}
	function addLink($update)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "INSERT INTO links (link) VALUES ('$update')";
		$d = $cmsDatabase->db($query);
		return !$d ? false : true;
	}
	function addPage($name, $content, $php, $active)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "INSERT INTO pages (name, page, isphp, active) VALUES ('$name', '$content', '$php', '$active')";
		$d = $cmsDatabase->db($query);
		return !$d ? false : true;
	}
	function editPage($name, $content, $php, $active, $id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE pages SET name='$name', page='$content', isphp='$php', active='$active' WHERE id='$id'";
		$d = $cmsDatabase->db($query);
		return !$d ? false : true;
	}
	function remPage($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "DELETE FROM pages WHERE id='$id'";
		$d = $cmsDatabase->db($query);
		return !$d ? false : true;
	}
	function remLink($id)
	{
		$cmsDatabase = new cmsDatabase;
		
		$query = "DELETE FROM links WHERE id='$id'";
		$d = $cmsDatabase->db($query);
		return !$d ? false : true;
	}
	function getPage($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM pages WHERE id='$id'";
		$d = $cmsDatabase->db($query, true);
		return $d;
	}
	function isPhp($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT isphp FROM pages WHERE id='$id'";
		$d = $cmsDatabase->db($query, true);
		if ($d['isphp'] == '1')
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function getLinkId($link)// Deprecated!
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT id FROM links WHERE link='$link'";
		$return = $cmsDatabase->db($query, true);
		return $return['id'];
	}
	function getCurrTheme()
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT theme FROM settingsb WHERE id='1'";
		$return = $cmsDatabase->db($query, true);
		return $return['theme'];
	}
}

?>
