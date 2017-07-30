<?php
class cmsSessions{
	function destroySession($sid)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBSESSIONS." WHERE sid='".$sid."'";
		$return = $cmsDatabase->db($query, true);
		$id = $return['id'];
		$query = "DELETE FROM ".cmsConfig::DBSESSIONS." WHERE id='".$id."'";
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
	function purgeSessions()
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBSESSIONS;
		$session = $cmsDatabase->db($query, true, true);
		foreach($session as $s)
		{
			if ($s['expires'] < time())
			{
				$query = "DELETE FROM ".cmsConfig::DBSESSIONS." WHERE id='".$s['id']."'";
				$session = $cmsDatabase->db($query);
			}
		}
	
	}
	function createSession($user)
	{
		$cmsDatabase = new cmsDatabase;
		$cmsUsers = new cmsUsers;

		$uid = $cmsUsers->getUserId($user);
		$sessionid = base64_encode(dechex(time()));
		$time = time() + 3000;
		// Remove old sessions:
		$this->purgeSessions();
		// End remove old sessions.
		$query = "INSERT INTO ".cmsConfig::DBSESSIONS." (sid, expires, uid) VALUES ('".$sessionid."', '".$time."', '".$uid."')";
		$session = $cmsDatabase->db($query);
		
		setcookie("PortalCMSSession", $sessionid, time()+3000, "/", cmsConfig::COOKIEURL);
		
		if (!$session)
		{
			return false;
		}
		else
		{
			return $sessionid;
		}
	}
	function verifySession($sid)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBSESSIONS." WHERE sid='".$sid."'";
		$session = $cmsDatabase->db($query, true);

		if (empty($session))
		{
			return false;
		}
		else
		{
			$sess = $session['sid'];
			$expires = $session['expires'];
			if ($sess == $sid)
			{
				$sessionid = hexdec(base64_decode($sid));
				if ($sessionid > $expires)
				{
					return -1;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return false;
			}
		}
	}
	function getUidBySession($sid)
	{
		$cmsDatabase = new cmsDatabase;

		if ($this->verifySession($sid))
		{
			$query = "SELECT * FROM ".cmsConfig::DBSESSIONS." WHERE sid='".$sid."'";
			$session = $cmsDatabase->db($query, true);
			$uid = $session['uid'];
			return $uid;
		}
		else
		{
			return false;
		}
	}
}
?>
