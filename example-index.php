<?php

/** 
	gd-test.php
	A quick proof of concept example to illustrate the Google Drive abstraction.
	
	@copyright	2014 Jonathan Lovatt
	@license	GNU General Public License v3
	@version	1.0.0
	@link		https://github.com/lovattj/php-gdrive-abstraction

*/

require_once "gd-f.php";

/**
	This is a really basic example that creates a new instance of the Google Drive class and tries
	to call the about() function to get the user metadata. If no access token is available, an exception
	gets thrown by the class which we catch here to generate the oAuth sign-in URL and we present that to
	the user. Otherwise, we make the request and echo the response from Google.
	
*/

try {
	$gd = new gd_tokenstore();
	$userdetails = $gd->about();
	
	echo "Logged in as: ".$userdetails['name'];
	echo "<br>";
	echo "Total Allocated Quota: ".(int)(($userdetails['quotaBytesTotal']/1024)/1024)." MBytes";
	echo "<br><br>";
	
	$oauthdetails = $gd->get_token_array();
	echo "Access token: ".$oauthdetails['access_token'];
	echo "<br>";
	echo "Refresh token: ".$oauthdetails['refresh_token'];
	
	unset($gd);
} catch (Exception $e) {
	echo "<a href='".$e->getMessage()."'>Click Here to sign-in with Google</a>";
}
	
?>