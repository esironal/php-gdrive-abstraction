php-gdrive-abstraction
======================

An abstraction to simplify use of Google Drive's PHP client API.

Google Drive's PHP Client API library is not well documented. Not at all, in fact. The underlying REST resources are well documented, but the PHP library has next to no documentation, apart from some examples.

This library works in conjunction with the PHP client library and makes common tasks easier. It handles storing oAuth tokens and refreshing them if necessary and makes calls to some of the major methods easier.

Not all methods are implemented, I'll get around to implementing everything in time.

Requirements:
- Google Drive PHP client library (download from Google if you need it).
- PHP 5 - I tested it with 5.3.3

How to install:
- Clone project
- Edit `gd-f.php`
- On lines 2 and 3, make sure the path to the Google Drive PHP API is correct.
- On lines 5, 6 and 7, enter your credentials from Google's API console.
- On line 8, enter a filename which is used to store Google's oAuth token bundle.

How to acquire an oAuth token bundle:
- Create an `gd_tokenstore` object, e.g. `$response = new gd_tokenstore;`
- An exception should be thrown containing the correct Google oAuth URL.
- Redirect to this URL and log in with Google.
- Google will redirect you to your callback page.
- On your callback page, call `$response = gd_auth::get_oauth_token($_GET['code'];`
- Save the returned token bundle: `gd_tokenstore::save_tokens_to_store($response);`
- This will save the access token & refresh token in the file you specified in step 5 of installation.

How to call methods:
- Create a `gd_tokenstore` object, e.g. `$response = new gd_tokenstore;`
- If an exception is thrown, acquire another token bundle.
- Otherwise, call your method!
- e.g. `$googleinfo = $response->about();`
- The client libary will handle refreshing tokens automatically, as long as your token bundle is in place.

Method references:
- See the wiki.
