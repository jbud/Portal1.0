<?php
class cmsNews {
	function formatDate($datecode)
	{
		return date("m/d/y G:i", $datecode); 
	}
	function getNewsPost($offset)
	{
		$cmsDatabase = new cmsDatabase;
		$cmsUsers = new cmsUsers;

		$query = "SELECT * FROM ".cmsConfig::DBNEWS." ORDER BY date DESC LIMIT ".$offset." , 1";
		$return = $cmsDatabase->db($query, true);
		$return = array(
			$return['id'],
			$return['body'],
			$return['title'],
			$this->formatDate($return['date']), 
			$cmsUsers->getUserNameById($return['author'])//$cmsUsers->getUserNameById()
		);
		return $return;
	}
	function getNumberOfNewsPosts()
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT COUNT(*) FROM news";
		$return = $cmsDatabase->db($query, true);
		if (!$return)
		{
			return false;
		}
		else
		{
			return $return[0];
		}
	}
	function getAllNews()
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM ".cmsConfig::DBNEWS;
		$return = $cmsDatabase->db($query, true, true);
		return $return;
	}
	function getNewsPostById($postId)
	{
		$cmsDatabase = new cmsDatabase;
		$cmsUsers = new cmsUsers;

		$query = "SELECT * FROM ".cmsConfig::DBNEWS." WHERE id='".$postId."'";
		$return = $cmsDatabase->db($query, true);
		$return = array(
			$return['id'],
			$return['body'],
			$return['title'],
			$this->formatDate($return['date']), 
			$cmsUsers->getUserNameById($return['author'])
		);
		return $return;
	}
	function getComments($newsId)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT * FROM comments WHERE newsid='".$newsId."'";
		$return = $cmsDatabase->db($query, true, true);
		return $return;
	}
	function countComments($newsId)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "SELECT COUNT(*) FROM comments WHERE newsid='".$newsId."'";
		$return = $cmsDatabase->db($query, true);
		return $return[0];
	}
	function getTitleByNewsId($postId)
	{
		$news = $this->getNewsPostById($postId);
		return $news[2];
	}
	function insertComment($newsId, $comment, $user, $email)
	{
		$cmsDatabase = new cmsDatabase;
		$cmsUsers = new cmsUsers;
		
		$query = "INSERT INTO comments (newsid, username, email, comment, date) VALUES ('".$newsId."', '".$user."', '".$email."', '".$comment."', '".time()."')";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			$com = "New comment from ".$user." (".$email.")\n";
			$com .= "\n------------------\n\n";
			$com .= $comment;
			$cmsUsers->sendCommentEmail(array($this->getTitleByNewsId($newsId), $com));
			return true;
		}
	}
	function remComment($commentId)
	{
		$cmsDatabase = new cmsDatabase;
		

		$query = "DELETE FROM comments WHERE id='$commentId'";
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
	function postNews($newsTitle, $newsBody, $userId)
	{
		$cmsDatabase = new cmsDatabase;
		$cmsUsers = new cmsUsers;
		
		$query = "INSERT INTO ".cmsConfig::DBNEWS." (title, body, author, date) VALUES ('".$newsTitle."', '".$newsBody."', '".$userId."', '".time()."')";
		$return = $cmsDatabase->db($query);
		$query = "SELECT posts FROM users WHERE id='$userId'";
		$posts = $cmsDatabase->db($query, true);
		$post = $posts[0] + 1;
		$query = "UPDATE users SET posts='$post' WHERE id='$userId'";
		$cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
		else
		{
			if (cmsConfig::DOEMAIL)
			{
				$cmsUsers->sendNewsEmail(array($newsTitle, $newsBody));
			}
			return true;
		}
	}
	function editNews($newsId, $newsTitle, $newsBody, $userId)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "UPDATE ".cmsConfig::DBNEWS." SET title='".$newsTitle."', body='".$newsBody."', author='".$userId."' WHERE id='".$newsId."'";
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
	function rem($newsId)
	{
		$cmsDatabase = new cmsDatabase;

		$query = "DELETE FROM news WHERE id='$newsId'";
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
}
?>
