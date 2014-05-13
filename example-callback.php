<?php
/** 
	gd-callback.php
	An example callback page that is used to exchange an oAuth code for an access token and refresh token pair.
	Used in conjunction with "gd-test.php" to provide a really basic proof of concept example.
	
	@copyright	2014 Jonathan Lovatt
	@license	GNU General Public License v3
	@version	1.0.0
	@link		https://github.com/lovattj/php-gdrive-abstraction

*/

require_once 'googledrive-functions.php';

/**
	The idea is that Google redirect the user to this page once they've successfully logged in, then we call the
	gd_auth::get_oauth_token function to get an access token & refresh token bundle. We then save that to the token
	store file registered in the class, if this succeeds then we redirect the user back to the main example page,
	which can then use these tokens to make authenticated requests.
	
*/

$response = gd_auth::get_oauth_token($_GET['code']);
if (gd_tokenstore::save_tokens_to_store($response)) {
	header('Location: example-index.php');
} else {
	echo "Error saving tokens.";
}

?>