<?php

$twittertag = "%23JBudORG";				// Tag for twitter shares.
$apiurl = "http://go.jbud.org/index.php?u=";		// API for URL Shortener (Recommended: "http://go.jbud.org/index.php?u=")
$postsPerPage = 4;

class cmsConfig {
	// Database:
	const USER = "USER";				// SQL Login Username.
	const PASSW = "PASSWORD";			// SQL Login Password.
	const DATABASE = "DATABASE";			// SQL Database Name.
	const URL = "localhost";			// SQL Server Location.
	
	// Config:
	const DBNEWS = "news";				// ONLY change this if you have a custom database structure.
	const DOEMAIL = true;				// If true, emails are sent to all registered users for each news post.
	
	// Sessions:
	const DBSESSIONS = "sessions";			// ONLY change this if you have a custom database structure.
	
	// Users:
	const DBUSERS = "users";			// ONLY change this if you have a custom database structure.
	const NEWUSERMAIL = "";				// This is not yet used.
	const FROMEMAILADDR = "no-reply@jbud.org";	// Email which subscribers' emails are sent "from"
	const ADMINEMAIL = "admin@jbud.org";		// Email to which comments are sent.
	const SUPPORTEMAIL = "support@jbud.org";	// Email that is used to unsubscribe from news emails. (support email is recommended)

	// Common:
	const COOKIEURL = ".jbud.org";			// Cookie URL.
	const SITEURL = "http://www.jbud.org/";		// Site URL.
	const SITENAME = "JBudORG";			// Site name.
}

?>
