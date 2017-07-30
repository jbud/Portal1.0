<?php
if (file_exists("cmsdata/cmsconfig.php"))
{
	include("cmsdata/cmsconfig.php");
}
else
{
	header("Location: cmsdata/install/install.php");
}

include("cmsdata/class.cmsdatabase.php");
include("cmsdata/class.cmsnews.php");
include("cmsdata/class.cmssessions.php");
include("cmsdata/class.cmsusers.php");
include("cmsdata/class.cmsrss.php");
include("cmsdata/class.cmssettings.php");
include("cmsdata/class.cmsmisc.php");

$cmsNews = new cmsNews;
$cmsDatabase = new cmsDatabase;
$cmsSessions = new cmsSessions;
$cmsUsers = new cmsUsers;
$cmsRSS = new cmsRSS;
$cmsSettings = new cmsSettings;
$cmsMisc = new cmsMisc;

$return = $cmsSettings->getSettings();

$links = $return['links'];
$pages = $return['pages'];
$cmsSiteName = $return['sitename'];
$cmsTagLine = $return['tagline'];
$cmsSiteAddress = $return['siteaddress'];
$cmsSupportEmail = $return['supportemail'];
$cmsAboutText = $return['abouttext'];
$cmsAdScript = $return['adscript'];
$cmsSiteNote = $return['sitenote'];
$cmsSiteTheme = $return['theme'];

$loggedIn = false;
$isAdmin = false;
$isMod = false;
$isEditor = false;
$newLogin = false;
$showNextNews = false;
$showPrevNews = false;

$numberOfPosts = $postsPerPage;
$newsOffset = 0;
$newsPosts = $cmsNews->getNumberOfNewsPosts();
$errMsg = "&nbsp;";

if (!empty($_GET['offset']))
{
	$newsOffset = $_GET['offset'];
	$numberOfPosts = $newsOffset + $numberOfPosts;
}

if ($newsPosts > $newsOffset + $postsPerPage)
{
	$showNextNews = true;
}

if ($newsOffset > 0)
{
	$showPrevNews = true;
}

$sid = $_COOKIE['PortalCMSSession'];

// Begin Modules
if ($_GET['rss'] == "true")
{
	header("Content-type: application/rss+xml");
	die($cmsRSS->rss());
}
if ($_GET['login'] == "true")
{
	if (!empty($_POST['user']) && !empty($_POST['pass']))
	{
		$login = $cmsUsers->login($_POST['user'], $_POST['pass']);
		if (!$login)
		{
			$errMsg = "Login Failed, the user/password combination was incorrect.";
		}
		else
		{
			$newLogin = true;
		}
	}
	else
	{
		$errMsg = "Failed to log in, please enter a password and username to continue.";
		$m_mode = "login";
	}
}
if ($_GET['logout'] == "true")
{
	if (!$cmsUsers->logout())
	{
		$errMsg = "failed to log out!";
	}
}
if ($_GET['editpost'] == "true")
{
    	if ($cmsSessions->verifySession($sid))
    	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)) || $cmsUsers->isEditor($cmsSessions->getUidBySession($sid)))
    		{
			$news = $cmsNews->editNews($_POST['newsid'], $_POST['title'], $_POST['body'], $_POST['uid']);
			if (!$news)
			{
				$errMsg = "Edit Failed!";
			}
			else
			{
				$cmsRSS->updaterss($cmsRSS->grss());
			}
		}
	}
}
if ($_GET['rempost'] == "true" && !empty($_GET['post']))
{
	if ($cmsSessions->verifySession($sid))
    	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)) || $cmsUsers->isEditor($cmsSessions->getUidBySession($sid)))
    		{
    			$rem = $cmsNews->rem($_GET['post']);
    			if (!$rem)
    			{
    				$errMsg = "Removal Failed!";
    			}
    		}
    	}
}
if ($_GET['postnew'] == "true")
{
	if ($cmsSessions->verifySession($sid))
	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)) || $cmsUsers->isEditor($cmsSessions->getUidBySession($sid)))
		{
			if (!empty($_POST['body']))
			{
			    $news = $cmsNews->postNews($_POST['title'], $_POST['body'], $_POST['uid']);
			
			    if (!$news)
			    {
			    	$errMsg = "Post Failed!";
			    }
		    	else
		    	{
		    		$cmsRSS->updaterss($cmsRSS->grss());
			    }
	    	}
    	}
    	else
    	{
    	    $errMsg = "You are not permitted to post";
    	}
	}
	else
	{
	    $errMsg = "Your session could not be verified";
	}
}
if ($_GET['register'] == "true")
{
	$regfailedmessage = "";
	$regfailed = false;
	if (!empty($_POST['user']) && !empty($_POST['pass']) && !empty($_POST['vpass']) && !empty($_POST['email']))
	{
		if ($_POST['pass'] == $_POST['vpass'])
		{
			if (!$cmsUsers->doesUserExist($_POST['user']))
			{
			    $rez = $cmsUsers->addUser($_POST['user'], $_POST['pass'], $_POST['email']);
				if (!$rez)
				{
					$regfailedmessage = "Failed to Register, Please contact <a href='mailto:$cmsSupportEmail'>$cmsSupportEmail</a> ERROR2: $rez! ".mysql_error();
					$regfailed = true;
				}
				else
				{
					$login = $cmsUsers->login($_POST['user'], $_POST['pass']);
					$newLogin = true;
				}
			}
			else
			{
				$regfailedmessage = "Username Already Exists!";
				$regfailed = true;
			}
		}
		else
		{
			$regfailedmessage = "Your passwords do not match!";
			$regfailed = true;
		}
	}
	else
	{
		$regfailedmessage = "One or more of the fields was blank! All fields are required to register";
		$regfailed = true;
	}
}
if ($_GET['remaccount'] == "true")
{
	if ($cmsSessions->verifySession($sid))
    	{
    		$rem = $cmsUsers->removeUser($_POST['uid']);
    		$cmsUsers->logout();
		if (!$rem)
		{
			$errMsg = "Removal Failed!";
		}
    	}
}
if ($_GET['settings'] == "true")
{
	$settingsSiteName = $_POST['sitename'];
	$settingsTagLine = $_POST['tagline'];
	$settingsSiteAddress = $_POST['siteaddress'];
	$settingsSupportEmail = $_POST['supportemail'];
	$settingsAboutText = $_POST['abouttext'];
	$settingsAdScript = $_POST['adscript'];
	$settingsSiteNote = $_POST['sitenote'];
	$settingsSiteTheme = $_POST['theme'];
	if (!empty($settingsSiteName) && !empty($settingsTagLine) && !empty($settingsSiteAddress))
	{
		if (!empty($settingsSupportEmail) && !empty($settingsAboutText) && !empty($settingsAdScript))
		{
			if (!empty($settingsSiteNote) && !empty($settingsSiteTheme))
			{
				$query = "UPDATE settingsb SET sitename='$settingsSiteName', abouttext='".$settingsAboutText."', adscript='".$settingsAdScript."', sitenote='".$settingsSiteNote."', tagline = '".$settingsTagLine."', siteaddress = '".$settingsSiteAddress."', supportemail = '".$settingsSupportEmail."', theme = '".$settingsSiteTheme."' WHERE id='1'";
				$d = $cmsDatabase->db($query);
			}
			else
			{
				$errMsg = "SettingsError0: One or more of the following fields were empty:<br />Site Note, or Site Theme";
			}
		}
		else
		{
			$errMsg = "SettingsError1: One or more of the following fields were empty:<br />Support Email, About Text, or Ad Script";
		}
	}
	else
	{
		$errMsg = "SettingsError2: One or more of the following fields were empty:<br />Site Name, Site Address, or Tag Line";
	}
}
if ($_GET['navsettings'] == "true")
{
	$count = count($_POST);
	for($i=0;$i<=$count-2;$i++)
	{
		$update = $_POST["link$i"];
		if (!empty($update))
		{
			$query = "UPDATE links SET link='$update' WHERE id='$i'";
			$d = $cmsDatabase->db($query);
			if (!$d)
			{
				$errMsg = "Failed to update one or more links!";
			}
		}
	}
	$update = $_POST["link$i"];
	if (!empty($update))
	{
		$query = "INSERT INTO links (link) VALUES ('$update')";
		$d = $cmsDatabase->db($query);
		if (!$d)
		{
			$errMsg = "Failed to add new link!";
		}
	}
}
if ($_GET['comment'] == "true")
{
	if (!empty($_POST['comment']) && !empty($_POST['name']) && !empty($_POST['email']))
	{
		$r = $cmsNews->insertComment($_POST['newsid'], $_POST['comment'], $_POST['name'], $_POST['email']);
	}
	else
	{
		$errMsg = "Failed to submit comment!";
	}
}
if ($_GET['remcomment'] == "true")
{
	if ($cmsSessions->verifySession($sid))
    	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)) || $cmsUsers->isEditor($cmsSessions->getUidBySession($sid)))
    		{
    			$cmsNews->remComment($_GET['c']);
    		}
    	}
}
if ($_GET['contact'] == "true")
{
	if (!empty($_POST['body']) && !empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['subject']))
	{
		$cmsUsers->contact($_POST['body'], $_POST['subject'], $_POST['name'], $_POST['email'], $cmsMisc->getIp());
	}
	else
	{
		$m_mode = "contact";
		$errMsg = "Failed to send contact message, one or more of the forms was empty";
	}
}
if ($_GET['editprofile'] == "true")
{
	$failed = true;
	if (!empty($_POST['uid']))
	{
		if ($cmsSessions->verifySession($sid))
    		{
    			$info = $cmsUsers->getUserInfoById($_POST['uid']);
			if (!empty($_POST['pass']) && !empty($_POST['npass']) && !empty($_POST['vpass']))
			{
				if ($cmsUsers->isPassValid($_POST['uid'], $_POST['pass']))
				{
					if ($_POST['npass'] == $_POST['vpass'])
					{
						if ($cmsUsers->changePass($_POST['uid'], $_POST['npass']))
						{
							$failed = false;
						}
					}
				}
			}
			if (!empty($_POST['theme']))
			{
				if ($cmsUsers->setUserTheme($_POST['uid'], $_POST['theme']))
				{
					$failed = false;
				}
			}
			if (!empty($_POST['email']) && $_POST['email'] != $info[3])
			{
				if ($cmsUsers->changeEmail($_POST['uid'], $_POST['email']))
				{
					$failed = false;
				}
			}
			if (empty($_POST['subscriber']))
			{
				$cmsUsers->removeSubscriber($_POST['uid']);
			}
			else
			{
				$cmsUsers->addSubscriber($_POST['uid']);
			}
		}
	}
	if ($failed)
	{
		$errMsg = "Failed to edit profile, contact the administrator!";
	}
}
if ($_GET['newpage'] == "true")
{
	if ($cmsSessions->verifySession($sid))
	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)))
		{
			if (!empty($_POST['body']) && !empty($_POST['title']))
			{
				$php = (empty($_POST['isphp'])) ? '0' : '1';
				$active = (empty($_POST['isactive'])) ? '0' : '1';
				$news = $cmsSettings->addPage($_POST['title'], $_POST['body'], $php, $active);
			}
		}
	}
}
if ($_GET['editpage'] == "true")
{
	if ($cmsSessions->verifySession($sid))
	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)))
		{
    		$php = (empty($_POST['isphp'])) ? '0' : '1';
			$active = (empty($_POST['isactive'])) ? '0' : '1';
			$news = $cmsSettings->editPage($_POST['title'], $_POST['body'], $php, $active,$_POST['id']);
		}
	}
}
if ($_GET['rempage'] == "true" && !empty($_GET['pid']))
{
	if ($cmsSessions->verifySession($sid))
    	{
    		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)))
    		{
    			$rem = $cmsSettings->remPage($_GET['pid']);
				$errMsg = "Page Removed";
    		}
    	}
}
if ($_GET['remlink'] == "true" && !empty($_GET['link']))
{
	if ($cmsSessions->verifySession($sid))
	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)))
		{
			$rem = $cmsSettings->remLink($_GET['link']);
			$errMsg = "Link Removed";
		}
	}
}
if ($_GET['admineditprofile'] == "true")
{
	if ($cmsSessions->verifySession($sid))
	{
		if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)))
		{
			$uid = $_POST['uid'];
			$isadm = (empty($_POST['isadmin'])) ? '0' : '1';
			$ismo = (empty($_POST['ismod'])) ? '0' : '1';
			$isedit = (empty($_POST['iseditor'])) ? '0' : '1';
			$errMsg = $cmsUsers->admEditUser($uid, $isadm, $ismo, $isedit);
		}
	}
}

if ($newLogin)
{
	$sid = $login;
}
else
{
	$sid = $_COOKIE['PortalCMSSession'];
}

if ($cmsSessions->verifySession($sid))
{
	$loggedIn = true;
	if ($cmsUsers->isAdmin($cmsSessions->getUidBySession($sid)))
	{
		$isAdmin = true;
	}
	if ($cmsUsers->isEditor($cmsSessions->getUidBySession($sid)))
	{
		$isEditor = true;
	}
	if ($cmsUsers->isMod($cmsSessions->getUidBySession($sid)))
	{
		$isMod = true;
	}
}

if ($loggedIn)
{
	$theme = $cmsUsers->getUserThemeById($cmsSessions->getUidBySession($sid));
	if ($theme != "none" && !empty($theme))
	{
		$cmsSiteTheme = $theme;
	}
}

$mode = $_GET['m'];
$pos = $_GET['p'];

if (isset($_GET['s']))
{
	$mode = "news";
	$pos = $_GET['s'];
}

if (isset($m_mode))
{
	$mode = $m_mode;
}

$postId = $_GET['post'];
$pageId = $_GET['p'];


switch($mode)
{
	case "users":
		$mode = 12;
		break;
	case "pages":
		$mode = 11.1;
		break;
	case "page":
		$mode = 11;
		break;
	case "contact":
		$mode = 10;
		break;
	case "nav":
		$mode = 9.1;
		break;
	case "settings":
		$mode = 9;
		break;
	case "profile":
		$mode = 7;
		break;
	case "post":
		$mode = 6;
		break;
	case "memberlist":
		$mode = 8;
		break;
	case "register":
		$mode = 5;
		break;
	case "reg":
		$mode = 5.1;
		break;
	case "login":
		$mode = 4;
		break;
	case "edit":
		$mode = 2;
		break;
	case "about":
		$mode = 3;
		break;
	case "news":
		$mode = 1.1;
		break;
	default:
		$mode = 1;
		break;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $cmsSiteName;?> - Home</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js">
</script>
<!--Begin JQUERY Scrolling Nav Panel Script-->
<script type="text/javascript">
$(function() {
    var $sidebar   = $(".sidebar1"),
        $window    = $(window),
        offset     = $sidebar.offset(),
        topPadding = 15;

    $window.scroll(function() {
        if ($window.scrollTop() > offset.top) {
            $sidebar.stop().animate({
                marginTop: $window.scrollTop() - offset.top + topPadding
            });
        } else {
            $sidebar.stop().animate({
                marginTop: 0
            });
        }
    });
});
</script>
<!--End JQUERY Script-->
<!--Begin WYSIWYG Includes-->
<script type="text/javascript" src="cmsdata/edit/ckeditor.js"></script>
<script src="cmsdata/edit/sample.js" type="text/javascript"></script>
<link href="cmsdata/edit/sample.css" rel="stylesheet" type="text/css" />
<!--End WYSIWYG Includes-->
<link rel="stylesheet" type="text/css" href="cmsdata/themes/<?php echo $cmsSiteTheme;?>/cmsstyles.css" />
<!--[if lte IE 7]>
<style>
.content { margin-right: -1px; } 
ul.nav a { zoom: 1; } 
</style>
<![endif]-->
</head>

<body>

<div class="container">
  <div class="header">
    <h1>
    <a id="top"></a><a href="<?php echo $cmsSiteAddress;?>"><?php echo $cmsSiteName;?></a></h1>
    <p><?php echo $cmsTagLine;?></p>
  </div>


  <div class="sidebar1">
  	<h4>Navigation</h4>
    <ul class="nav">
      <li><a href="<?php echo $cmsSiteAddress;?>">Home</a></li>
      <li><a href="<?php echo $cmsSiteAddress;?>?m=about">About</a></li>
      <li><a href="<?php echo $cmsSiteAddress;?>?m=contact">Contact Us</a></li>
      <?php
      foreach($pages as $p)
      {
			if ($p['active'] == 1)
			{
      		?>
      		<li><a href="<?php echo $cmsSiteAddress;?>?m=page&p=<?php echo $p['id'];?>"><?php echo $p['name'];?></a></li>
			<?php
			}
      }
      foreach($links as $l)
      {
      	echo "<li>".$l."</li>";
      }
      ?>
    </ul>
    <?php echo $cmsSiteNote;?>
    <!-- end .sidebar1 -->
  </div>


  <div class="content">
    <?php
    	if ($mode == 1)
	{
    ?>
	    <h2>News<a href="<?php echo $cmsSiteAddress;?>?rss=true"><img src="cmsdata/themes/<?php echo $cmsSiteTheme;?>/rss.png" width='32' height='32' style="float:right" alt="Subscribe to RSS Feed" title="Subscribe to RSS Feed"/></a></h2>
	    <p style="color:red; text-align:center;"><?php echo $errMsg;?></p>
	    <?php
	    $e = 1;
	    for ($i=$newsOffset;$i<=$numberOfPosts - 1;$i++)
	    {
	    if ($e != 1)
	    {
	    ?>
		    <p>&nbsp;</p>
		    <?php
		    }
		    	$newsArray = $cmsNews->getNewsPost($i);
		    	$newsId = $newsArray[0];
		    	$newsTitle = $newsArray[2];
		    	$newsDate = $newsArray[3];
		    	$newsAuthor = $newsArray[4];
		    	$newsBody = $newsArray[1];
		    	if ($e >= 2)
		    	{
		    		$span = true;
		    	}
		    	$e++;
		    	if (!empty($newsBody))
		    	{
		    ?>
			    <h3><a href="<?php echo $cmsSiteAddress;?>?m=news&amp;p=<?php echo $newsId;?>"><?php echo $newsTitle; ?></a> - <span class="smallinfo"><?php echo $newsDate; ?> PST by <?php echo $newsAuthor; ?></span>
			    </h3>
			    <p><?php if ($span){ echo "<span class='small'>"; } echo $newsBody; if ($span){ echo "</span>"; } ?> <br /><span class="smallinfo"><a href="<?php echo $cmsSiteAddress;?>?m=news&amp;p=<?php echo $newsId;?>#comments">-<?php echo $cmsNews->countComments($newsId);?> Comment<?php if ($cmsNews->countComments($newsId) != 1){echo "s";}?>-</a></span><?php if ($isAdmin || $isEditor) {?><span class="smallinfo"> | <a href="<?php echo $cmsSiteAddress;?>?m=edit&amp;post=<?php echo $newsId;?>">-Edit-</a> | <a href="<?php echo $cmsSiteAddress?>?rempost=true&amp;post=<?php echo $newsId;?>">-Delete-</a></span><?php }?></p>
	    <?php
	    	}
	    }
	    ?>
	    <p>&nbsp;</p>
	    <?php 
	    if ($showPrevNews)
	    {
	    $off = $newsOffset - $postsPerPage;
	    ?>
	    	<span style="text-align:center;"><a href="<?php echo $cmsSiteAddress;?>?offset=<?php echo $off;?>">Prev</a></span>
	    <?php
	    }
	    ?>
	    <?php 
	    if ($showNextNews) 
	    {
	    $off = $newsOffset + $postsPerPage;
	    ?>
	    	<span style="text-align:center;"><a href="<?php echo $cmsSiteAddress;?>?offset=<?php echo $off;?>">Next</a></span>
	    <?php
	    }
	    ?>
    <?php
    	}
    	elseif ($mode == 1.1)
	{
    ?>
	    <h2>News</h2>
	    
	    <?php
	    
	    ?>
		    <p>&nbsp;</p>
		    <?php
		    	$newsArray = $cmsNews->getNewsPostById($pos);
		    	$newsId = $newsArray[0];
		    	$newsTitle = $newsArray[2];
		    	$newsDate = $newsArray[3];
		    	$newsAuthor = $newsArray[4];
		    	$newsBody = $newsArray[1];
		    	$comments = $cmsNews->getComments($newsId);
		    	$count = $cmsNews->countComments($newsId);
		    	$url = $cmsSiteAddress."?s=".$newsId;
		    	$shorty = $cmsMisc->curl_get_file_contents($apiurl.$url);
		    	$newsTitleF = str_replace(" ", "+", $newsTitle);
		    	if (!empty($newsBody))
		    	{
		    ?>
			    <h3><?php echo $newsTitle; ?> - <span class="smallinfo"><?php echo $newsDate; ?> PST by <?php echo $newsAuthor; ?></span>
			    </h3>
			    <p><?php echo $newsBody; ?> <br /><span class="smallinfo"><a href="http://twitter.com/home/?status=<?php echo $newsTitleF;?>+-+<?php echo $shorty;?>+News+Post+on+<?php echo $twittertag;?>".$twittertag>-Tweet This-</a></span><?php if ($isAdmin || $isEditor) {?><span class="smallinfo"> | <a href="<?php echo $cmsSiteAddress;?>?m=edit&post=<?php echo $newsId;?>">-Edit-</a> | <a href="<?php echo $cmsSiteAddress;?>?rempost=true&post=<?php echo $newsId;?>">-Delete-</a></span><?php }?></p>
			    <a id="comments"></a><h5>Comments</h5>
			    <table width='100%'>
			    <?php
			    	for($i=0;$i<=$count-1;$i++)
			    	{
					$comment = nl2br($comments[$i]['comment']);
					$user = $comments[$i]['username'];
					$id = $comments[$i]['id'];
					$date = $cmsNews->formatDate($comments[$i]['date']);
					?>
					<tr><td width="5%">&nbsp;</td><td  width="75%"><span class="small"><?php echo $date;?> by <?php echo $user;?></span></td><td  width="20%">&nbsp;</td></tr>
					<tr><td width="5%">&nbsp;</td><td width="75%"><span class="small"><?php echo $comment;?></span></td><td width="20%"><?php if ($isAdmin || $isMod){?><span class="smallinfo"><a href="<?php echo $cmsSiteAddress;?>?m=news&p=<?php echo $newsId;?>&remcomment=true&c=<?php echo $id;?>">-Delete-</a></span><?php }?></td></tr>
					<tr><td colspan='2'>______________________</td></tr>
					<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
					<?php
				}
			    ?>
			    </table>
		<form method="post" action="<?php echo $cmsSiteAddress.'?m=news&p='.$newsId.'&comment=true';?>">
		
		<table width="300">
		 <?php 
		if (!$loggedIn)
		{
		?>
		 <tr><td><label>Name: </td><td><input name="name" value=""/></label></td></tr>   		
	    	 <tr><td><label>Email: </td><td><input name="email" value=""/></label></td></tr>
		<?php
		}
		else
		{
			$user = $cmsUsers->getUserInfoById($cmsSessions->getUidBySession($sid));
		    	$name = $user[1];
		    	$email = $user[3];
		 ?>
		 	<input type="hidden" name="name" value="<?php echo $name;?>">
		 	<input type="hidden" name="email" value="<?php echo $email;?>">	
		 <?php
		}
		?>
		 
		 <input type="hidden" name="newsid" value="<?php echo $newsId;?>">
	    	 <tr><td>Comment: </td><td><textarea name="comment" cols='40' rows='10'></textarea></td></tr>
	    	 <tr><td><input type="submit" value="Post"></td><td>&nbsp;</td></tr>
	    	</table>
	    	<?php
	    	}
	    	else
	    	{
	    	?>
	    	<p>Invalid News Post.</p>
	    	<?php
	    	}
	    	?>
	    <p>&nbsp;</p>
     <?php
    	}
	elseif ($mode == 2)
	{
    ?>
	    <h2>Edit</h2>
	     <?php
	    	$newsArray = $cmsNews->getNewsPostById($postId);
	    	$newsId = $newsArray[0];
	    	$newsTitle = $newsArray[2];
	    	$newsDate = $newsArray[3];
	    	$newsAuthor = $newsArray[4];
	    	$newsBody = $newsArray[1];
	    	$user = $cmsSessions->getUidBySession($sid);
	    	
	    ?>
	    <form method="post" action="<?php echo $cmsSiteAddress.'?editpost=true';?>">
	    	 <p><label>Title: <input name="title" value="<?php echo $newsTitle;?>"/></label></p>
	    	 <p class="form"><textarea class="ckeditor" name="body" cols='67' rows='30'><?php echo $newsBody;?></textarea></p>
	    	 <input type="hidden" name="newsid" value="<?php echo $newsId;?>"/>
	    	 <input type="hidden" name="uid" value="<?php echo $user;?>"/>
	    	 <p><input type="submit" value="Edit"/></p>
	    </form>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 4)
	{
    ?>
	    <h2>Login</h2>
	   <p style="color:red; text-align:center;"><?php echo $errMsg;?></p>
	    <p>&nbsp;</p>
	    <form method="post" action="<?php echo $cmsSiteAddress.'?login=true';?>">
	    	<table width="300">
	    	<tr><td><label>User: </td><td><input name="user" maxlength="15"></label></td></tr>
	    	<tr><td><label>Password: </td><td><input type="password" name="pass" maxlength="15"></label></td></tr>
	    	<tr><td><input type="submit" value="Login"></td><td>&nbsp;</td></tr>
	    	</table>
	    </form>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 3)
	{
    ?>
	    <h2>About</h2>
	    <p>&nbsp;</p>
	   <?php echo $cmsAboutText;?>
	   <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 5)
	{
    ?>
	    <h2>Register</h2>
	   
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <form method="post" action="<?php echo $cmsSiteAddress.'?register=true&m=reg';?>">
	    	<table width="300">
		    	<tr><td><label>Username: </td><td><input name="user" maxlength="15"></label></td></tr>
		    	<tr><td><label>Email: </td><td><input name="email"></label></td></tr>
		    	<tr><td><label>Password: </td><td><input type="password" name="pass" maxlength="15"></label></td></tr>
		    	<tr><td><label>Verify Password: </td><td><input type="password" name="vpass" maxlength="15"></label></td></tr>
		    	<tr><td><input type="submit" value="Register"></td><td>&nbsp;</td></tr>
	    	</table>
	    </form>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <?php
    	}
	elseif ($mode == 5.1)
	{
    ?>
	    <h2>Register</h2>
	   
	    <p>&nbsp;</p>
	    <?php 
	    if ($regfailed)
	    {
	    ?>
		    <p>Failed - <?php echo $regfailedmessage; ?></p>
		    <p>&nbsp;</p>
		    <form method="post" action="<?php echo $cmsSiteAddress.'?register=true&m=reg';?>">
		    	<table width="300">
			    	<tr><td><label>Username: </td><td><input name="user" value="<?php echo $_POST['user']; ?>" maxlength="15" /></label></td></tr>
			    	<tr><td><label>Email: </td><td><input name="email" value="<?php echo $_POST['email']; ?>" /></label></td></tr>
			    	<tr><td><label>Password: </td><td><input type="password" name="pass" maxlength="15" /></label></td></tr>
			    	<tr><td><label>Verify Password: </td><td><input type="password" name="vpass" maxlength="15" /></label></td></tr>
			    	<tr><td><input type="submit" value="Register"></td><td>&nbsp;</td></tr>
		    	</table>
		    </form>
	    <?php 
	    }
	    else
	    {
	    ?>
	    <p>Registration Complete!</p>
	    <?php 
	    }
	    ?>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 6)
	{
    ?>
	    <h2>Add news post</h2>
	     <?php
	    	$user = $cmsSessions->getUidBySession($sid);
	    	if ($isAdmin || $isEditor)
	    	{
	    ?>
	    <form method="post" action="<?php echo $cmsSiteAddress.'?postnew=true';?>">
	    	 <p><label>Title: <input name="title" value=""/></label></p>
	    	 <p class="form"><textarea class="ckeditor" name="body" cols='67' rows='30'></textarea></p>
	    	 <input type="hidden" name="uid" value="<?php echo $user;?>"/>
	    	 <p><input type="submit" value="Post"/></p>
	    </form>
	    <?php 
	    }
	    else
	    {
	    ?>
	    <p>Warning: You must be logged in to post!</p>
	    <?php
	    }
	    ?>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 7)
	{
    ?>
	    <h2>Profile</h2>
	    <?php
		if ($loggedIn)
	{
		$id = $cmsSessions->getUidBySession($sid);
		$user = $cmsUsers->getUserInfoById($id);
	    ?>
		<table width="300">
	    		<tr><td>User ID:</td><td><?php echo $user[0];?></td></tr>
	    		<tr><td>Username: </td><td><?php echo $user[1];?></td></tr>
	    		<tr><td>Email: </td><td><?php echo $user[3];?></td></tr>
	    		<tr><td>Posts: </td><td><?php echo $user[4];?></td></tr>
				<tr><td>Joined: </td><td><?php echo $cmsNews->formatDate($user[10]);?></td></tr>
				<tr><td>Last Login: </td><td><?php echo $cmsNews->formatDate($user[11]);?></td></tr>
	    		<tr><td>
				<?php if ($user[5] == 1) {?>
	    			[Admin]
	    		<?php } ?>
				<?php if ($user[7] == 1 || $user[5] == 1) {?>
	    			[Moderator]
	    		<?php } ?>
				<?php if ($user[8] == 1 || $user[5] == 1) {?>
	    			[Editor]
	    		<?php } ?>
				<?php if ($user[6] == 1) {?>
	    			[Subscriber]
	    		<?php } ?></td><td></td></tr>
	    		
	    	</table>
	    	
    		<form method="post" action="<?php echo $cmsSiteAddress.'?editprofile=true';?>">
    			<input type="hidden" name="uid" value="<?php echo $id;?>"/>
    			<table width="300">
    			<tr><td><label>Change Email: </td><td><input name="email" value="<?php echo $user[3];?>"/></label></td></tr>
		 	<tr><td><label>Current password: </td><td><input name="pass" value=""/></label></td></tr>
		 	<tr><td><label>New password: </td><td><input name="npass" value=""/></label></td></tr>
		 	<tr><td><label>Verify new password: </td><td><input name="vpass" value=""/></label></td></tr>
		 	<?php $themes = $cmsMisc->readThemes();?>
		    	 <tr><td>Custom Theme:</td><td><select name="theme">
		<?php
		foreach ($themes as $t)
		{
			if ($t == $cmsSiteTheme)
			{
			    	?>
					<option value="<?php echo $t;?>" selected="selected"><?php echo $t;?></option>
				<?php
			}
			else
			{
				?>
					<option value="<?php echo $t;?>"><?php echo $t;?></option>
				<?php
			}
		}
			 ?>
			 </select></td></tr>
		<?php
		if ($cmsUsers->isNewsSubscriber($id))
		{
			$sub = "checked=\"checked\"";
		}
		else
		{
			$sub = "";
		}
			 ?>
			<tr><td>Subscribe to news posts?: </td><td><input type="checkbox" <?php echo $sub;?> name="subscriber" value="1" /></td></tr>
		 	</table>
		 	<p><input type="submit" value="Submit Changes"/></p>
    		</form>
	<?php
	}
	else
	{
	?>
		<p>You need to be logged in to do this.</p>
	<?php
	}
	?>
		<p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 8)
	{
    ?>
	    <h2>Member List</h2>
	    <?php
	    	$id = $cmsSessions->getUidBySession($sid);
	    	$users = $cmsUsers->getAllUids();
	    	if ($isAdmin)
	    	{
		    	foreach($users as $uid)
		    	{
		    		$user = $cmsUsers->getUserInfoById($uid);
		    ?>
			<table width="300">
		    		<tr><td>User ID:</td><td><?php echo $user[0];?></td></tr>
		    		<tr><td>Username: </td><td><?php echo $user[1];?></td></tr>
		    		<tr><td>Email: </td><td><?php echo $user[3];?></td></tr>
		    		<tr><td>Posts: </td><td><?php echo $user[4];?></td></tr>
		    		<?php if ($user[5] == 1) {?>
		    			<tr><td>[Admin]</td><td></td></tr>
		    		<?php } ?>
		    		
		    	</table>
		    <?php
		    	}
	    	}
	    	else
	    	{
		?>
		    <p>Warning: You are not an admin!</p>
	    <?php
	    	}
	    ?>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	     <?php
    	}
	elseif ($mode == 9)
	{
    ?>
	    <h2>Site Settings</h2>
	    <?php
	    	if ($isAdmin)
	    	{
	    	$about = str_replace("&nbsp;", "&amp;nbsp;", $cmsAboutText);
	    ?>
		<form method="post" action="<?php echo $cmsSiteAddress.'?settings=true';?>">
		<table width="300">
		 
		 <tr><td><label>Site Address: </td><td><input name="siteaddress" value="<?php echo $cmsSiteAddress;?>"/></label></td></tr>   		
	    	 <tr><td><label>Site Name: </td><td><input name="sitename" value="<?php echo $cmsSiteName;?>"/></label></td></tr>
	    	 <tr><td><label>Tag Line: </td><td><input name="tagline" value="<?php echo $cmsTagLine;?>"/></label></td></tr>
	    	 <tr><td><label>Support Email: </td><td><input name="supportemail" value="<?php echo $cmsSupportEmail;?>"/></label></td></tr>
	    	 <?php $themes = $cmsMisc->readThemes();?>
	    	 <tr><td><select name="theme">
	    	 <?php
	    	 foreach ($themes as $t)
	    	 {
				if ($t == $cmsSettings->getCurrTheme())
	    	 	{
		    	?>
					<option value="<?php echo $t;?>" selected="selected"><?php echo $t;?></option>
				<?php
				}
				else
				{
				?>
					<option value="<?php echo $t;?>"><?php echo $t;?></option>
				<?php
				}
			 }
			 ?>
		 </select></td><td>&nbsp;</td></tr>
		 </table>
	    	 About Page:<br/><textarea class="ckeditor" name="abouttext" cols='67' rows='30'><?php echo $about;?></textarea><br/>
	    	 Site Note:<br/><textarea class="ckeditor" name="sitenote" cols='67' rows='30'><?php echo $cmsSiteNote;?></textarea><br/>
	    	 Ad Script:<br/><textarea name="adscript" cols='67' rows='30'><?php echo $cmsAdScript;?></textarea><br/>
	    	 <input type="submit" value="Post"/>
	    </form>
	    <?php
	    }
	    else
	    {
	    ?>
	    <p>Warning: You need to be logged in to change settings.</p>
	    <?php 
	    }
	    ?>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
<?php }
	elseif ($mode == 9.1)
	{
    ?>
	    <h2>Navigation Settings</h2>
	    <?php
	    	if ($isAdmin)
	    	{
		    	$i = 0;
		    	foreach($links as $a)
		    	{
		    		$links[$i] = str_replace('"', "&quot;", $a);
		    		$i++;
		    	}
	    ?>
		<form method="post" action="<?php echo $cmsSiteAddress.'?navsettings=true';?>">
		<table width="300">
		 <?php $i=0; foreach ($links as $l) { $id = $cmsSettings->getLinkId($link);?>
		 <tr><td><label>Link #<?php echo $i; ?>: </td><td><input name="link<?php echo $i;?>" value="<?php echo $l;?>"/></label><a href="<?php echo $cmsSiteAddress;?>?remlink=true&link=<?php echo $id;?>">-Delete-</a></td></tr>   
		<?php $i++; }?>
		 <tr><td><label>Add new link: </td><td><input name="link<?php echo $i;?>" value=""/></label></td></tr>
	    	 <tr><td><input type="submit" value="Post"/></td><td></td></tr>
	    	 </table>
	    </form>
	    <?php
	    }
	    else
	    {
	    ?>
	    <p>Warning: You need to be logged in to change settings.</p>
	    <?php 
	    }
	    ?>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 10)
	{
    ?>
	    <h2>Contact Us</h2>
	     
	    <form method="post" action="<?php echo $cmsSiteAddress.'?contact=true';?>">
	    <table width="100%">
	    	 <tr><td><label>Name: </td><td><input name="name" value=""/></label></td></tr>
	    	 <tr><td><label>Subject: </td><td><input name="subject" value=""/></label></td></tr>
	    	 <tr><td><label>Email Address: </td><td><input name="email" value=""/></label></td></tr>
	    	 <tr><td>Email: </td><td><textarea class="ckeditor" cols='67' rows='30' name="body" cols='40' rows='20'></textarea></td></tr>
	    	 <tr><td><input type="submit" value="Post"/></td><td>&nbsp;</td></td></tr>
	    </table>
	    </form>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 11)
	{
    ?>
	    
	     <?php
	     	$page = $cmsSettings->getPage($pageId);
	     ?>
	     <h2><?php echo $page['name'];?></h2>
	     <?php if ($cmsSettings->isPhp($pageId)){ echo eval($page['page']); } else { echo $page['page'];}?>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
	elseif ($mode == 11.1)
	{
	if (empty($pageId) && $_GET['new'] != 'true')
	{
	?>
	<h3>Edit page - Select a page to edit...</h3>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<ul>
	<?php
	    foreach($pages as $p)
      	    {
      		?>
      		<li><a href="<?php echo $cmsSiteAddress;?>?m=pages&p=<?php echo $p['id'];?>&w=true"><?php echo $p['name'];?></a> - <a href="<?php echo $cmsSiteAddress;?>?m=pages&rempage=true&pid=<?php echo $p['id'];?>">-Delete-</a></li>
      		<?php
      	    }
      	    ?>
      	    <li><a href="<?php echo $cmsSiteAddress;?>?m=pages&new=true&w=true">New Page</a></li>
      	    </ul>
      	    <?php
      	 }
      	 else
      	 {
      	 $useWysiwyg = false;
      	 $edornew = ($_GET['new'] == 'true') ? "new" : "edit";
	 $new = ($_GET['new'] == 'true') ? true : false;
	 if (!$new)
	 {
	 	$page = $cmsSettings->getPage($pageId);
	 	$wlink = "?m=pages&p=$pageId&w=true";
	 }
	 else
	 {
	 	$wlink = "?m=pages&new=true&w=true";
	 }
	 if ($_GET['w'] == "true")
	 {
	 	$useWysiwyg = true;
	 }
	 if ($useWysiwyg)
	 {
	 	if (!$new)
		 {
		 	$wlink = "?m=pages&p=$pageId";
		 }
		 else
		 {
		 	$wlink = "?m=pages&new=true";
		 }
		 $w = "class=\"ckeditor\"";
	 }
	 
      ?>
      	<h3>Edit page</h3>
      	<p>&nbsp;</p>
	<p>&nbsp;</p>
            <?php if (!$useWysiwyg) {?><a href="<?php echo $cmsSiteAddress.$wlink;?>">Enable WYSIWYG Editor</a> - (Unsaved changes will be lost)<br />
            <?php } else {?><a href="<?php echo $cmsSiteAddress.$wlink;?>">Disable WYSIWYG Editor</a> - (Unsaved changes will be lost)<br /><?php }?>
	    <form method="post" action="<?php echo $cmsSiteAddress.'?'.$edornew.'page=true';?>">
	    	 <p><label>Title: <input name="title" value="<?php echo $page['name'];?>"/></label></p>
	    	 <input type="hidden" name="id" value="<?php echo $pageId;?>"/>
	    	 <p><label>PHP: <input type="checkbox" name="isphp" value="1" /></label> - PHP scripts may not start with '&lt;'</p>
			 <p><label>Active: <input type="checkbox" name="isactive" checked="checked" value="1" /></label> - If checked, page link will show in the nav panel.</p>
	    	 <p class="form"><textarea <?php echo $w;?> name="body" cols='67' rows='30'><?php echo $page['page'];?></textarea></p>
	    	 <p><input type="submit" value="Post"/></p>
	    </form>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php 
    	   }
    	   ?>
    	   <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	<?php
	}
	elseif ($mode == 12)
	{
    ?>
	    <h2>User Management</h2>
	    <?php if (empty($_POST['id']) && empty($_GET['id']) && empty($_POST['name'])){?>
	    <form method="post" action="<?php echo $cmsSiteAddress.'?m=users';?>">
		<p>Find a user to manage. (Enter a name or ID to find a user.)</p>
	    <table width="300">
	    	 <tr><td><label>Name: </td><td><input name="name" value=""/></label></td></tr>
	    	 <tr><td><label>ID: </td><td><input name="id" value=""/></label></td></tr>
	    	 <tr><td><input type="submit" value="Post"/></td><td>&nbsp;</td></td></tr>
	    </table>
	    </form>
		<?php 
		}
		elseif (!empty($_POST['name']))
		{
		$usersFound = $cmsUsers->findUserByName($_POST['name']);
		?>
		<ul>
		<?php
		if (!empty($usersFound))
		{
			foreach($usersFound as $p)
			{
		?>
      			<li><a href="<?php echo $cmsSiteAddress;?>?m=users&id=<?php echo $p[0];?>"><?php echo $p[1];?></a></li>
		<?php
			}
		}
		else
		{
		?>
			<li>No users found!</li>
		<?php
      	}	
		?>
		</ul>
			<?php 
		
		}
		elseif (!empty($_POST['id']) || !empty($_GET['id']))
		{
		$id = (!empty($_POST['id'])) ? $_POST['id'] : $_GET['id'];
		$adminChecked = ($cmsUsers->isAdmin($id)) ? "checked=\"checked\"" : "";
		$modChecked = ($cmsUsers->isMod($id)) ? "checked=\"checked\"" : "";
		$editorChecked = ($cmsUsers->isEditor($id)) ? "checked=\"checked\"" : "";
		$user = $cmsUsers->getUserInfoById($id);
	    ?>
			<table width="300">
	    		<tr><td>User ID:</td><td><?php echo $user[0];?></td></tr>
	    		<tr><td>Username: </td><td><?php echo $user[1];?></td></tr>
	    		<tr><td>Email: </td><td><?php echo $user[3];?></td></tr>
	    		<tr><td>Posts: </td><td><?php echo $user[4];?></td></tr>
				<tr><td>Joined: </td><td><?php echo $cmsNews->formatDate($user[10]);?></td></tr>
				<tr><td>Last Login: </td><td><?php echo $cmsNews->formatDate($user[11]);?></td></tr>
			</table>
			<p>&nbsp;</p>
			<form method="post" action="<?php echo $cmsSiteAddress.'?admineditprofile=true';?>">
    			<input type="hidden" name="uid" value="<?php echo $id;?>"/>
    			<table width="300">
					<tr><td><label>Site Admin: </td><td><input type="checkbox" name="isadmin" <?php echo $adminChecked;?> value="1" /></label></td></tr>
	    			<tr><td><label>Comment Moderator: </td><td><input type="checkbox" name="ismod" <?php echo $modChecked;?> value="1" /></label></td></tr>
	    			<tr><td><label>News Editor: </td><td><input type="checkbox" name="iseditor" <?php echo $editorChecked;?> value="1" /></label></td></tr>
	    			<tr><td><input type="submit" value="Edit User"/></td><td>&nbsp;</td></tr>
				</table>
			</form>
		<?php
		}
		?>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    	<?php
    	}
    	else
    	{
    ?>
    	    <p>&nbsp;</p>	
	    <p style="text-align:center;">An error occurred: Error1 (Mode is unset!)</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
	    <p>&nbsp;</p>
    <?php
    	}
    ?>
  <!-- end .content -->
  </div>


  <div class="sidebar2">
    <h4>User-Panel</h4>
    <ul class="nav">
      <?php
      if ($loggedIn)
      {
      ?>
		<li><a href="<?php echo $cmsSiteAddress.'?m=profile';?>">Profile</a></li>
		<li><a href="<?php echo $cmsSiteAddress.'?logout=true';?>">Logout</a></li>
      <?php
      }
      else
      {
      ?>
		<li><a href="<?php echo $cmsSiteAddress.'?m=register';?>">Register</a></li>
		<li><a href="<?php echo $cmsSiteAddress.'?m=login';?>">Login</a></li>
      <?php
      }
      ?>
    </ul>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <!-- end .sidebar2 -->
  </div>


    <?php if ($isAdmin || $isEditor)
      		{
      ?>		<div class="sidebar3">
      			<h4>Admin-Panel</h4>
      			<ul class="nav">
      				<li><a href="<?php echo $cmsSiteAddress.'?m=post';?>">New Post</a></li>
	      			 <?php if ($isAdmin){?>
					<li><a href="<?php echo $cmsSiteAddress.'?m=settings';?>">Site Settings</a></li>
	      			<li><a href="<?php echo $cmsSiteAddress.'?m=nav';?>">Nav Management</a></li>
	      			<li><a href="<?php echo $cmsSiteAddress.'?m=pages';?>">Page Management</a></li>
					<li><a href="<?php echo $cmsSiteAddress.'?m=users';?>">User Management</a></li>
					<li><a href="<?php echo $cmsSiteAddress.'?m=memberlist';?>">Member List</a></li>
					 <?php }?>
			</ul>
      			<p>&nbsp;</p>
   				<p>&nbsp;</p>
      			</div>
      		<?php 
      		}
      		?>
    <?php
      if (!$loggedIn)
      {
    ?>
    <div class="sidebar4">
	    <h4>Sponsored By:</h4>
	     <?php echo $cmsAdScript;?>
		 <p>&nbsp;</p>
    </div>
    <?php 
    }
    ?>


    <div class="footer">
      <p class="copy">&copy;2011 - 2017 <a href="http://www.jbud.org/">JBud.ORG</a> - Portal CMS version 0.4.1 Revision 1</p>
	<p class="copy"><a target="_blank" href="http://validator.w3.org/"><img alt="Valid HTML5.0 Markup" title="Valid HTML5.0 Markup" src="cmsdata/themes/validhtml5.png" /></a>&nbsp;&nbsp;<a href="#top">Back to top</a>&nbsp;&nbsp;<a href="http://validator.w3.org/feed/" target="_blank" ><img alt="Valid RSS2.0 Markup" title="Valid RSS2.0 Markup" src="cmsdata/themes/validrss2.gif" /></a></p>
      <p>&nbsp;</p>
    </div>


  <!-- end .container -->
</div>
</body>
</html>
