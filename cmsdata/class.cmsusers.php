<?php
class cmsUsers{
	function email($to, $subject, $message)
	{
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: ".cmsConfig::FROMEMAILADDR;
		mail($to, $subject, $message, $headers);
	}
	function emailfrom($to, $subject, $message, $from)
	{
		$headers = "From: ".$from;
		mail($to, $subject, $message, $headers);
	}
	function contact($body, $subject, $name, $email, $ip)
	{
		$bod = cmsConfig::SITENAME." Support Email from ".$name." @ ".$ip;
		$bod .= "\n-------------------\n\n";
		$bod .= $body;
		$this->emailfrom(cmsConfig::SUPPORTEMAIL, $subject, $bod, $email);
	}
	function removeSubscriber($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBUSERS." SET subscriber='0' WHERE id='$id'";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function addSubscriber($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBUSERS." SET subscriber='1' WHERE id='$id'";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function isNewsSubscriber($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT subscriber FROM ".cmsConfig::DBUSERS." WHERE id='1'";
		$return = $cmsDatabase->db($query, true);
		if ($return['subscriber'] == "1")
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function getNewsSubscribers()
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBUSERS." WHERE subscriber='1'";
		$return = $cmsDatabase->db($query, true, true);
		$i = 0;
		$array = array();
		foreach($return as $r)
		{
			$array[$i] = $r['email'];
			$i++; 
		}
		return $array;
	}
	function sendNewsEmail(/*Array*/$news)
	{
		$newsSubject = "[News] ".$news[0];
		$newsBody = $news[1]."<br/><br/>----------<br/><a href='".cmsConfig::SITEURL."'>".cmsConfig::SITEURL."</a><br/>----------<br/>News Post from ".cmsConfig::SITENAME."<br/>If you do not wish to recieve these emails, contact <a href='mailto:".cmsConfig::SUPPORTEMAIL."'>".cmsConfig::SUPPORTEMAIL."</a>";
		$newsTo = $this->getNewsSubscribers();
		foreach($newsTo as $e)
		{
			$this->email($e, $newsSubject, $newsBody);
		}
	}
	function sendCommentEmail(/*Array*/$news)
	{
		$newsSubject = "[Comment] ".$news[0];
		$newsBody = $news[1]."\n\n----------\n".cmsConfig::SITEURL."\n----------\nNew comment on ".cmsConfig::SITENAME."\n";
		$e = cmsConfig::ADMINEMAIL;
		$this->email($e, $newsSubject, $newsBody);
	}
	function doesUserExist($name)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBUSERS." WHERE user='".$name."'";
		$return = $cmsDatabase->db($query, true);
		if (!$return)
		{
			return false;
		}
		else
		{
			if (empty($return))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}
	function getAllUids()
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBUSERS;
		$return = $cmsDatabase->db($query, true, true);
		$i = 0;
		$array = array();
		foreach($return as $r)
		{
			$array[$i] = $r['id'];
			$i++; 
		}
		return $array;
	}
	function getUserNameById($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBUSERS." WHERE id='".$id."'";
		$return = $cmsDatabase->db($query, true);
		if (!$return)
		{
			return false;
		}
		else
		{
			return $return['user'];
		}
	}
	function addUser($user, $pass, $email)
	{
		$cmsDatabase = new cmsDatabase;
		$cmsMisc = new cmsMisc;

		$query = "INSERT INTO ".cmsConfig::DBUSERS." (`user`, `pass`, `email`, `posts`, `admin`, `subscriber`, `mod`, `editor`, `ip`, `joined`, `lastlogin`) VALUES ('".$user."', '".$pass."', '".$email."', '0', '0', '0', '0', '0', '".$cmsMisc->getIp()."', '".time()."', '".time()."')";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function removeUser($uid)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "DELETE FROM users WHERE id='$uid'";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function getUserId($user)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM users WHERE user = '".$user."'";
		$return = $cmsDatabase->db($query, true);
		//print_r($return);
		if (empty($return))
		{
			return false;
		}
		else
		{
			return $return['id'];
		}
	}
	function getUserInfoByName($name)
	{
		// TODO
	}
	function getUserInfoById($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBUSERS." WHERE id='".$id."'";
		$return = $cmsDatabase->db($query, true);
		if (!$return)
		{
			return false;
		}
		else
		{
			return array($id, $return['user'], $return['pass'], $return['email'], $return['posts'], $return['admin'], $return['subscriber'], $return['mod'], $return['editor'], $return['ip'], $return['joined'], $return['lastlogin']);
		}
	}
	function getUserThemeById($id)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT theme FROM ".cmsConfig::DBUSERS." WHERE id='".$id."'";
		$return = $cmsDatabase->db($query, true);
		return $return['theme'];
	}
	function setUserTheme($id, $theme)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBUSERS." SET theme='".$theme."' WHERE id='".$id."'";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function changePass($id, $pass)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBUSERS." SET pass='".$pass."' WHERE id='".$id."'";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function changeEmail($id, $email)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBUSERS." SET email='".$email."' WHERE id='".$id."'";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	function addAdmin($userId)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBUSERS." SET admin='1' WHERE id='$userId'";
		$return = $cmsDatabase->db($query);
		return (!$return) ? false : true;
	}
	function addMod($userId)
	{
		$cmsDatabase = new cmsDatabase;
		
		$query = "UPDATE ".cmsConfig::DBUSERS." SET mod='1' WHERE id='$userId'";
		$return = $cmsDatabase->db($query);
		return (!$return) ? false : true;
	}
	function addEditor($userId)
	{
		$cmsDatabase = new cmsDatabase;
		
		$query = "UPDATE ".cmsConfig::DBUSERS." SET editor='1' WHERE id='$userId'";
		$return = $cmsDatabase->db($query);
		return (!$return) ? false : true;
	}
	function isAdmin($userId)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBUSERS." WHERE id='".$userId."'";
		$return = $cmsDatabase->db($query, true);
		if (!$return || empty($return))
		{
			return false;
		}
		else
		{
			if ($return['admin'] == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	function isMod($userId)
	{
		$cmsDatabase = new cmsDatabase;
		
		$query = "SELECT * FROM ".cmsConfig::DBUSERS." WHERE id='".$userId."'";
		$return = $cmsDatabase->db($query, true);
		if (!$return || empty($return))
		{
			return false;
		}
		else
		{
			if ($return['mod'] == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	function isEditor($userId)
	{
		$cmsDatabase = new cmsDatabase;
		
		$query = "SELECT * FROM ".cmsConfig::DBUSERS." WHERE id='".$userId."'";
		$return = $cmsDatabase->db($query, true);
		if (!$return || empty($return))
		{
			return false;
		}
		else
		{
			if ($return['editor'] == 1)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	function isPassValid($id, $pass)
	{
		$profile = $this->getUserInfoById($id);
		if ($profile[2] == $pass)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function login($user, $pass)
	{
		$cmsDatabase = new cmsDatabase;
		$cmsSessions = new cmsSessions;
		$cmsMisc = new cmsMisc;

		$id = $this->getUserId($user);
		$query = "UPDATE users SET lastlogin='".time()."', ip='".$cmsMisc->getIp()."' WHERE id='$id'";
		$cmsDatabase->db($query);
		//echo $id;
		if (!$id)
		{
			return false;
		}
		else
		{
			$profile = $this->getUserInfoById($id);
			$passhd = $profile[2];
			
			if (password_verify($pass, $passhd))
			{
				$sid = $cmsSessions->createSession($user);
				if (!$sid)
				{
					return false;
				}
				else
				{
					if ($cmsSessions->verifySession($sid))
					{
						return $sid;
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		}
	}
	function logout()
	{
		$cmsSessions = new cmsSessions;

		if (!$cmsSessions->destroySession($_COOKIE['PortalCMSSession']))
		{
			return false;
		}
		else
		{
			setcookie("PortalCMSSession", $sessionid, time()+1, "/", cmsConfig::COOKIEURL);
			return true;
		}
	}
	function findUserByName($search)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT id,user FROM users WHERE user LIKE '%$search%'";
		$return = $cmsDatabase->db($query, true, true);
		if (empty($return))
		{
			return false;	
		}
		else
		{
			return $return;
		}
	}
	function admEditUser($id, $isAdmin, $isMod, $isEditor)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBUSERS." SET `admin`='$isAdmin', `mod`='$isMod', `editor`='$isEditor' WHERE id='$id'";
		$return = $cmsDatabase->db($query);
		return (!$return) ? mysql_error() : true;
	}
}
?>
