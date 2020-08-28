<?php
include_once('mysql2i.class.php');
class cmsDatabase {
	function doSql($args) // use c0re MySQL function.
	{
		$conn = mysql_connect($args['url'], $args['user'], $args['pass']);
		if (!$conn)
		{
			return false; // unable to connect, bad password, bad username, or sql server offline.
			die(mysql_error());
		}
		else
		{
			mysql_select_db($args['db']);
			if ($args['type'] == "RETURN")
			{
				return mysql_query($args['query'], $conn);
			}
			if ($args['type'] == "RETURNARRAY")
			{
				$q = mysql_query($args['query'], $conn);
				if (!$q)
				{
					die(mysql_error());
				}
				else
				{
					return mysql_fetch_array($q);
				}
			}
			if ($args['type'] == "RETURNFULLARRAY")
			{
				$return = array();
				$q = mysql_query($args['query'], $conn);
				if (!$q)
				{
					die(mysql_error());
				}
				else
				{
					$i = 0;
					while($row = mysql_fetch_array($q))
					{
						$return[$i] = $row;
						$i++;
					}
					return $return;
				}
			}
			else
			{
				return false; // no valid type recieved
			}
			mysql_close($conn);
		}
	}
	function db($query, $array = false, $full = false)
	{
		if ($array)
		{
			if ($full)
			{
				$type = "RETURNFULLARRAY";
			}
			else
			{
				$type = "RETURNARRAY";
			}
		}
		else
		{
			$type = "RETURN";
		}
		return $this->doSql(
			array(
					"query"=>$query,
					"url"=>cmsConfig::URL,
					"user"=>cmsConfig::USER,
					"pass"=>cmsConfig::PASSW,
					"db"=>cmsConfig::DATABASE,
					"type"=>$type
				)
			);
	}
}
?>
