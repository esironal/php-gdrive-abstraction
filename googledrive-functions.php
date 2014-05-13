<?php

// If your Google API PHP client resides elsewhere, change these paths:

require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';

// Set your security information from the Google API console:

define("gd_client_id", "your-google-client-id");
define("gd_client_secret", "your-google-client-secret");
define("gd_redirect_uri", "your-google-oauth-redirect-uri");


define("gd_token_store", "gdtokens"); // Path to a file to store access and refresh tokens

class gd_auth {

/**
 * gd_auth
 *
 * A helper class that provides oAuth token management related functions.
 *
 * @copyright	2014 Jonathan Lovatt
 * @license 	GNU General Public License v3
 * @version		1.0.0
 * @link		https://github.com/lovattj/php-gdrive-abstraction
 * @since		Class available since 1.0.0
 */ 

	public static function get_oauth_token($auth) {
	
	/**
	 * Exchanges an authorization code for an oAuth access_token and refresh_token bundle.
	 *
	 * @param  string  $auth The authorization code returned from Google
	 * @return array   oAuth token bundle containing access_token, refresh_token and expiry time.
	 */ 	
	 
		$arraytoreturn = array();
			$output = "";
			try {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://accounts.google.com/o/oauth2/token");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/x-www-form-urlencoded',
					));
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);		

				$data = "client_id=".gd_client_id."&redirect_uri=".urlencode(gd_redirect_uri)."&client_secret=".urlencode(gd_client_secret)."&code=".$auth."&grant_type=authorization_code";	
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$output = curl_exec($ch);
			} catch (Exception $e) {
			}

			$out2 = json_decode($output, true);
			$arraytoreturn = Array('access_token' => $out2['access_token'], 'refresh_token' => $out2['refresh_token'], 'expires_in' => $out2['expires_in']);
			return $arraytoreturn;
	
		}
}

class gd_tokenstore {

/**
 * gd_tokenstore
 *
 * The main class used for making requests.
 *
 * @copyright	2014 Jonathan Lovatt
 * @license 	GNU General Public License v3
 * @version		1.0.0
 * @link		https://github.com/lovattj/php-gdrive-abstraction
 * @since		Class available since 1.0.0
 */ 
 
	public $client;
	public $service;
	public $current_token;
	public $alltokens;
	
	public function __construct() {
		$token = gd_tokenstore::acquire_token();
			if (!$token) {
				$this->client = new Google_Client();
				$this->client->setClientId(gd_client_id);
				$this->client->setClientSecret(gd_client_secret);
				$this->client->setRedirectUri(gd_redirect_uri);
				$this->client->setScopes(array('https://www.googleapis.com/auth/drive'));	
				$authUrl = $this->client->createAuthUrl();			
				throw new Exception($authUrl); 
				exit;
			} else {
				$this->client = new Google_Client();
				$this->client->setClientId(gd_client_id);
				$this->client->setClientSecret(gd_client_secret);
				$this->client->setRedirectUri(gd_redirect_uri);
				$this->client->setScopes(array('https://www.googleapis.com/auth/drive'));					
				$this->client->setAccessToken($token);
				$this->service = new Google_DriveService($this->client);
			}
	}
	
	
	public function about() {
	
		/**
		* Get metadata about the currently logged in user.
		*
		* @return array User metadata contained in an associative array.
		*/ 
		
		$resp = $this->service->about->get();
		$this->common_code();			
		return $resp;			
	}

	public function listFiles($parameters) {
	
		/**
		 * List files within a search scope on Google Drive
		 *
		 * @param  array  $parameters  An array of Google Drive search parameters.
		 * @return array  An associative array of files meeting the criteria.
		 */ 

		$resp = $this->service->files->listFiles($parameters);
		$this->common_code();					
		return $resp;
	}
	
	public function get($id) {
	
		/**
		* Get metadata on a file
		*
		* @param  array  $id  A Google Drive file ID reference.
		* @return array  An associative array file metadata.
		*/ 

		$resp = $this->service->files->get($id);
		$this->common_code();					
		return $resp;
	}
	
	
	public function get_current_access_token() {
	
		/**
		* Get the current access token
		*
		* @return string  The currently valid access_token.
		*/ 
			
		$resp = $this->about();	
		$this->common_code();	
		return $this->current_token;
	}

	public function get_token_array() {
	
		/**
		* Get the current access token bundle, including access_token, refresh_token and expiry_time.
		*
		* @return array  The token bundle.
		*/ 
		
		$resp = $this->about();
		$this->common_code();
		return $this->alltokens;	
	}
	
	public function list_permissions($id) {

		/**
		* Get permissions on a file
		*
		* @param  string  $id  A Google Drive file ID reference.
		* @return array  An associative array of file permissions.
		*/ 
	
		$resp = $this->service->permissions->listPermissions($id);
		$this->common_code();		
		return $resp;
	}
	
	public function upload($filename, $filesize, $filemime, $parentfolder) {
	
		/**
		* Upload a file
		*
		* @param  string  $filename  Name of the file to upload including full path.
		* @param  string  $filesize  Size of the file in bytes.
		* @param  string  $filemime  MIME content-type of the file.
		* @param  string  $parentfolder  A Google Drive ID referencing the folder to upload the file to.

		* @return Status  true on success.
		*/ 
			
		$chunkSizeBytes = 1 * 1024 * 1024;
		$uf = $filename;
		
		$file = new Google_DriveFile();
		$file->setTitle(basename($filename));
		$file->setMimeType($filemime);

		$parent = new Google_ParentReference();
		$parent->setId($parentfolder);
		$file->setParents(array($parent));

		$media = new Google_MediaFileUpload($filemime, null, true, $chunkSizeBytes);
		$media->setFileSize($filesize);
		
		
		$result = $this->service->files->insert($file, array('mediaUpload' => $media));

		$status = false;
		$handle = fopen($uf, "rb");
	
		while (!$status && !feof($handle)) {
			$chunk = fread($handle, $chunkSizeBytes);
			$uploadStatus = $media->nextChunk($result, $chunk);
			echo "."; // Echo one dot every chunk to indicate that it's working.
			ob_flush();
		}

		fclose($handle);
		$this->common_code();
		
		return true;		
	}
	
	protected function common_code() {
	
		$auth = $this->client->getAuth();
	
		$tokenarray = array();
		$tokenarray = $auth->token;			
	
		$tsresp = gd_tokenstore::save_tokens_to_store($tokenarray);								
		$this->current_token = $tokenarray['access_token'];
		$this->alltokens = $tokenarray;	
		
	}	

	public static function acquire_token() {
		
			$response = gd_tokenstore::get_tokens_from_store();
			if (empty($response['access_token'])) {	// No token at all, needs to go through login flow. Return false to indicate this.
			 return false;
			} else {
			 return json_encode($response);
			 }
	}
			

	protected static function get_tokens_from_store() {
		$response = json_decode(@file_get_contents(gd_token_store), TRUE);
		return $response;
	}
	
	public static function save_tokens_to_store($tokens) {
		if (file_put_contents(gd_token_store, json_encode($tokens))) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function destroy_tokens_in_store() {
		if (file_put_contents(gd_token_store, "loggedout")) {
			return true;
		} else {
			return false;
		}
		
	}
}
?>