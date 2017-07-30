<?php
class cmsRSS {
	function updaterss($rss)
	{
		$cmsDatabase = new cmsDatabase;
	
		$query = "UPDATE rss SET rss='$rss', date='".time()."' WHERE id='1'";
		$return = $cmsDatabase->db($query);
		if (!$return)
		{
			return false;
		}
	}
	function grss()
	{
		$cmsNews = new cmsNews;
	
		$news = $cmsNews->getAllNews();
		$posts = $cmsNews->getNumberOfNewsPosts();
		$rss = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$rss .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n<atom:link href=\"".cmsConfig::SITEURL."?rss=true\" rel=\"self\" type=\"application/rss+xml\" />\n";
		$rss .= "<title>".cmsConfig::SITENAME." Latest News Posts</title>\n<link>".cmsConfig::SITEURL."</link>\n<description>This is the rss2 feed for ".cmsConfig::SITENAME."</description>\n<lastBuildDate>".date("D, d M Y H:i:s T", time())."</lastBuildDate>\n<language>en-us</language>\n";
		for($i=0;$i<=$posts-1;$i++)
		{
			$snews = $news[$i];
			$body = str_replace("<","&lt;",substr($snews['body'], 0, 150)."[...]");
			$body = str_replace(">","&gt;",$body);
			$link = "".cmsConfig::SITEURL."?m=news&amp;p=".$snews['id'];
			$title = $snews['title'];
			$date = $snews['date'];
			$rss .= "<item>\n<title>$title</title>\n<link>$link</link>\n<guid>$link</guid>\n<pubDate>".date("D, d M Y H:i:s T", $date)."</pubDate>\n<description><![CDATA[ $body ]]></description>\n</item>\n\n";
		}
		$rss .= "</channel>\n</rss>";
		return $rss;
	}
	function rss()
	{
		$cmsDatabase = new cmsDatabase;
	
		$query = "SELECT rss FROM rss WHERE id='1'";
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
}
