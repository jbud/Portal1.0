<?php
if (file_exists("cmscfig.temp"))
{
	$cfigStr = file_get_contents("cmscfig.temp");
}
$sqlfiles = array(
	"struct.comments.sql", "struct.links.sql", "struct.news.sql",
	"struct.rss.sql", "struct.sessions.sql", "struct.settingsb.sql",
	"struct.users.sql", "struct.pages.sql"
);
function dummyrss()
{
	$rss = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$rss .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n";
	$rss .= "</channel>\n</rss>";
	return $rss;
}
function editConfig($tag, $content, $cfigStr)
{
	return str_replace($tag, $content, $cfigStr);
}
function writeConfig($cfigStr)
{
	$config_file_name = "cmscfig.temp";
	$file_handle = fopen($config_file_name, 'w');
	if (!$file_handle)
	{
		return false;
	}
	fwrite($file_handle, $cfigStr);
	fclose($file_handle);
	// Verify Contents:
	if (file_get_contents($config_file_name) == $cfigStr)
	{
		return true;
	}
	else
	{
		return -1;
	}
}
function writeFinalConfig($cfigStr)
{
	$config_file_name = "../cmsconfig.php";
	$file_handle = fopen($config_file_name, 'w');
	if (!$file_handle)
	{
		return false;
	}
	fwrite($file_handle, $cfigStr);
	fclose($file_handle);
	// Verify Contents:
	if (file_get_contents($config_file_name) == $cfigStr)
	{
		return true;
	}
	else
	{
		return -1;
	}
}

function readThemes()
{
	$DIR = "../themes/";
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

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript" src="../edit2/ckeditor.js"></script>
<link rel="stylesheet" type="text/css" href="../themes/default/cmsstyles.css" /> 
<title>Portal CMS - Install</title>
<body>
<div class="container"> 
<div class="header"> 
    <h1> 
    Portal CMS Installer</h1> 
</div>
<div class="sidebar1">
	<h2>Progress</h2>
	<?php $step = (empty($_POST['step'])) ? '0' : $_POST['step']; ?>
	<p>step: <?php echo $step;?> of 7</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
</div>
<div class="content">
<?
switch( $_POST['step'] ){
	case 1:
		$cfigStr = file_get_contents("cmscfig.portal");
		writeConfig($cfigStr);
		?>
		First things first, we need get your connection info and check to see if the database can be connected to!
		<form method="POST" action="<? echo $PHP_SELF; ?>">
		<input name="step" type="hidden" value="1.5">
		Server address: <input name="server" type="text" value="localhost"><br />
		Username: <input name="username" type="text" value=""><br />
		Password: <input name="password" type="password" value=""><br />
		<input type="submit" value="Check connection!" name="submit">
		</form>
		<?
		break;
	case 1.5:
		$link = mysql_connect($_POST['server'],$_POST['username'],$_POST['password']);
		if( !@$link ){
			mysql_close($link);
			?>
			Connection failed, please check data.<br />
			<form method="POST" action="<? echo $PHP_SELF; ?>">
			<input name="step" type="hidden" value="1.5">
			Server address: <input name="server" type="text" value="<? echo $_POST['server']; ?>"><br />
			Username: <input name="username" type="text" value="<? echo $_POST['username']; ?>"><br />
			Password: <input name="password" type="password" value=""><br />
			<input type="submit" value="Check connection!" name="submit">
			</form>
			<?
		} else {
			?>
			Connection seems fine and dandy!  Please double check the information and enter the database name.<br />
			<form method="POST" action="<? echo $PHP_SELF; ?>">
			<input name="step" type="hidden" value="2">
			Server address: <input name="server" type="text" value="<? echo $_POST['server']; ?>"><br />
			Username: <input name="username" type="text" value="<? echo $_POST['username']; ?>"><br />
			Password: <input name="password" type="password" value="<? echo $_POST['password']; ?>"><br />
			Database: <input name="database" type="text" value=""><br />
			<input type="submit" value="Check database!" name="submit">
			</form>
			<?
			}
		break;
	case 2:
		$link = mysql_connect($_POST['server'],$_POST['username'],$_POST['password']);
		if ( !mysql_select_db($_POST['database'],$link) ){
			?>
			Connection seems fine and dandy, but I had trouble connecting to the database.  Check information and try again?<br />
			<form method="POST" action="<? echo $PHP_SELF; ?>">
			<input name="step" type="hidden" value="2">
			Server address: <input name="server" type="text" value="<? echo $_POST['server']; ?>"><br />
			Username: <input name="username" type="text" value="<? echo $_POST['username']; ?>"><br />
			Password: <input name="password" type="password" value="<? echo $_POST['password']; ?>"><br />
			Database: <input name="database" type="text" value="<? echo $_POST['database']; ?>"><br />
			<input type="submit" value="Check database!" name="submit">
			</form>
			<?
		} else {
			?>
			Congratulations, everything seems in order!  Now to insert the database with the Portal CMS database schema (technical mumbo jumbo)!<br />
			<form method="POST" action="<? echo $PHP_SELF; ?>">
			<input name="step" type="hidden" value="3">
			<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
			<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
			<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
			<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
			<input type="submit" value="Insert technical mumbo jumbo!" name="submit">
			</form>
			<?
		}
		break;
	case 3:
		$cfigStr = editConfig("#USER#", $_POST['username'], $cfigStr);
		$cfigStr = editConfig("#PASSWORD#", $_POST['password'], $cfigStr);
		$cfigStr = editConfig("#DATABASE#", $_POST['database'], $cfigStr);
		$cfigStr = editConfig("#SQLURL#", $_POST['server'], $cfigStr);
		writeConfig($cfigStr);
		$link = mysql_connect($_POST['server'],$_POST['username'],$_POST['password']);
		if ( !mysql_select_db($_POST['database'],$link) ){
			echo "Error! Unable to connect to database.";
		} else {
			foreach($sqlfiles as $f)
			{
				$sql = file_get_contents($f);
				if (!mysql_query( $sql , $link))
				{
					echo mysql_error();
				}
				else
				{
					echo "Inserted Schema: ".$f."<br />";
				}
			}
			?>
				Now, lets setup the site, click next to continue<br />
				<form method="POST" action="<? echo $PHP_SELF; ?>">
				<input name="step" type="hidden" value="4">
				<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
				<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
				<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
				<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
				<input type="submit" value="Next..." name="submit">
			<?php
		}
		break;
	case 4:
		?>
			Please fill out the form and click next to continue...<br />
			<form method="POST" action="<? echo $PHP_SELF; ?>">
			<input name="step" type="hidden" value="5">
			Site Name: <input name="sitename" type="text" value=""> - Example: My Web Site<br />
			Site URL: <input name="siteurl" type="text" value=""> - Example: http://www.example.com<br />
			Cookie URL: <input name="cookieurl" type="text" value=""> - <a class="click" onclick="javascript:alert('If your site url is http://www.example.com then this should be .example.com')">What's this?</a><br />
			Admin Email: <input name="aemail" type="text" value=""> - Example: admin@example.com<br />
			Support Email: <input name="semail" type="text" value=""> - Example: support@example.com<br />
			Reply-to email: <input name="remail" type="text" value=""> - Example: no-reply@example.com<br />
			Send news emails to Subscribers?: <input name="subscribers" checked="checked" type="checkbox" value="1"><br />
			How many news posts per page?: <select name="ppp"><option value="6">6</option><option value="5">5</option><option value="4" selected="selected">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option></select><br />
			Short Link API: <input name="api" type="text" value="http://go.jbud.org/index.php?u="> - <a class="click" onclick="javascript:alert('Change this only if you know what you are doing.')">What's this?</a><br />
			Hash tag for twitter shares?: <input name="twitter" type="text" value=""> - Example: #MyWebSite<br />
			<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
			<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
			<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
			<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
			<input type="submit" value="Next..." name="submit">
		<?php
		break;
	case 5:
		$cfigStr = editConfig("#SITENAME#", $_POST['sitename'], $cfigStr);
		$cfigStr = editConfig("#SITEURL#", $_POST['siteurl'], $cfigStr);
		$cfigStr = editConfig("#COOKIEURL#", $_POST['cookieurl'], $cfigStr);
		$cfigStr = editConfig("#ADMINEMAIL#", $_POST['aemail'], $cfigStr);
		$cfigStr = editConfig("#SUPPORTEMAIL#", $_POST['semail'], $cfigStr);
		$cfigStr = editConfig("#FROMEMAIL#", $_POST['remail'], $cfigStr);
		$cfigStr = editConfig("#POSTSPERPAGE#", $_POST['ppp'], $cfigStr);
		$cfigStr = editConfig("#API#", $_POST['api'], $cfigStr);
		$cfigStr = editConfig("#TWITTER#", $_POST['twitter'], $cfigStr);
		if (empty($_POST['subscribers']))
		{
			$doemail = "false";
		}
		else
		{
			$doemail = "true";
		}
		$cfigStr = editConfig("#DOEMAIL#", $doemail, $cfigStr);
		writeConfig($cfigStr);
		?>
		All that's needed now is the content Press next to continue...<br />
		<form method="POST" action="<? echo $PHP_SELF; ?>">
		<input name="step" type="hidden" value="5.1">
		<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
		<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
		<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
		<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
		<input name="aemail" type="hidden" value="<? echo $_POST['aemail']; ?>">
		<input type="submit" value="Next..." name="submit">
		<?php
		break;
	case 5.1:
		?>
		<form method="POST" action="<? echo $PHP_SELF; ?>">
		<input name="step" type="hidden" value="5.5">
		Admin Username: <input name="admin" type="text" value=""><br />
		Admin Password: <input name="pass" type="password" value=""><br />
		Verify Password: <input name="vpass" type="password" value=""><br />
		<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
		<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
		<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
		<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
		<input name="aemail" type="hidden" value="<? echo $_POST['aemail']; ?>">
		<input type="submit" value="Next..." name="submit">
		
		<?php
		break;
	case 5.5:
		if ($_POST['pass'] == $_POST['vpass'])
		{
			$link = mysql_connect($_POST['server'],$_POST['username'],$_POST['password']);
			if ( !mysql_select_db($_POST['database'],$link) ){
				echo "Error! Unable to connect to database.";
			} else {
				$phpass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
				$sql = "INSERT INTO users (user, pass, email, posts, admin, subscriber) VALUES ('".$_POST['admin']."', '".$phpass."', '".$_POST['aemail']."', '0', '1', '1')";
				if (!mysql_query( $sql , $link))
				{
					die(mysql_error(). " " .$sql);
				}
			}
		?>
		All that's needed now is the content Press next to continue...<br />
		<form method="POST" action="<? echo $PHP_SELF; ?>">
		<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
		<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
		<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
		<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
		<input name="step" type="hidden" value="6">
		<input type="submit" value="Next..." name="submit">
		<?php
		}
		else
		{
		?>
		Error, your passwords don't match<br />
		<form method="POST" action="<? echo $PHP_SELF; ?>">
		<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
		<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
		<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
		<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
		<input name="step" type="hidden" value="5.1">
		<input type="submit" value="Go Back..." name="submit">
		<?php
		}
		break;
	case 6:
		?>
		Note: This can all be edited later if desired<br />
		<form method="post" action="<? echo $PHP_SELF; ?>">
		<input name="server" type="hidden" value="<? echo $_POST['server']; ?>">
		<input name="username" type="hidden" value="<? echo $_POST['username']; ?>">
		<input name="password" type="hidden" value="<? echo $_POST['password']; ?>">
		<input name="database" type="hidden" value="<? echo $_POST['database']; ?>">
		<table width="300">
		 
		 <tr><td><label>Site Address: </td><td><input name="siteaddress" value=""/></label></td></tr>   		
	    	 <tr><td><label>Site Name: </td><td><input name="sitename" value=""/></label></td></tr>
	    	 <tr><td><label>Tag Line: </td><td><input name="tagline" value=""/></label></td></tr>
	    	 <tr><td><label>Support Email: </td><td><input name="supportemail" value=""/></label></td></tr>
	    	 <?php $themes = readThemes();?>
	    	 <tr><td><select name="theme">
	    	 <?php
	    	 foreach ($themes as $t)
	    	 {
	    	 	if ($t == "default")
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
	    	 About Page:<br/><textarea id="ckeditor" name="abouttext" cols='67' rows='30'></textarea><br/>
	    	 Site Note:<br/><textarea id="ckeditor" name="sitenote" cols='67' rows='30'></textarea><br/>
	    	 Ad Script:<br/><textarea name="adscript" cols='67' rows='30'></textarea><br/>
	    	 <input name="step" type="hidden" value="7">
	    	 <input type="submit" value="Post"/>
	    </form>
	    <?php
		break;
	case 7:
		$settingsSiteName = $_POST['sitename'];
		$settingsTagLine = $_POST['tagline'];
		$settingsSiteAddress = $_POST['siteaddress'];
		$settingsSupportEmail = $_POST['supportemail'];
		$settingsAboutText = $_POST['abouttext'];
		$settingsAdScript = $_POST['adscript'];
		$settingsSiteNote = $_POST['sitenote'];
		$settingsSiteTheme = $_POST['theme'];
		$rss = dummyrss();
		
		$link = mysql_connect($_POST['server'],$_POST['username'],$_POST['password']);
			if ( !mysql_select_db($_POST['database'],$link) ){
				echo "Error! Unable to connect to database.";
			} else {
				$sql = "INSERT INTO settingsb (id, sitename, abouttext, adscript, sitenote, tagline, siteaddress, supportemail, theme) VALUES ('1', '$settingsSiteName', '".$settingsAboutText."', '".$settingsAdScript."', '".$settingsSiteNote."', '".$settingsTagLine."', '".$settingsSiteAddress."', '".$settingsSupportEmail."', '".$settingsSiteTheme."')";
				if (!mysql_query( $sql , $link))
				{
					die(mysql_error());
				}
				$sql = "INSERT INTO rss (id, rss, date) VALUES ('1', '$rss', '".time()."')";
				if (!mysql_query( $sql , $link))
				{
					die(mysql_error());
				}
			}
			writeFinalConfig($cfigStr);
			?>
				You're done, you may delete the install directory!!! <a href="<?php echo $_POST['siteaddress'];?>">take me to my site!</a>
			<?
		break;
	default:
		?>
		Welcome to Portal CMS.  Before you get started you will need to install Portal CMS!<br />
		<form method="POST" action="<? echo $PHP_SELF; ?>">
		<input name="step" type="hidden" value="1">
		<input type="submit" value="Get started!" name="submit">
		</form>
		<?
		break;
}
?>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
</div>
<script type="text/javascript">
CKEDITOR.replace( 'ckeditor' );
</script>
<div class="footer">
      <p class="copy">&copy;2011 <a href="http://www.jbud.org/">JBud.ORG</a> - Portal CMS version 0.3 BETA</p>
	<p class="copy"><a target="_blank" href="http://validator.w3.org/"><img alt="Valid HTML5.0 Markup" title="Valid HTML5.0 Markup" src="../themes/validhtml5.png" /></a>&nbsp;&nbsp;<a href="#top">Back to top</a>&nbsp;&nbsp;<a href="http://validator.w3.org/feed/" target="_blank" ><img alt="Valid RSS2.0 Markup" title="Valid RSS2.0 Markup" src="../themes/validrss2.gif" /></a></p>
      <p>&nbsp;</p>
    </div>
</div>
</body>
</html>
