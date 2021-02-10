## Authenticate mediawiki users with a Django postgres database

This repository implements a custom authenticator for MediaWiki that tries to log in with credentials
stored in a Django Posgres database. Currently, only pbkdf2-sha256 hashing is supported.

Tested with MediaWiki 1.35.1 and Django 3.0.7.

### File structure:

* `postgres_django_auth.php` -- function to authenticate
* `demo_auth.php` -- command-line interface for the function

   Example:
   - `python manage.py changepassword sergei`, enter `eer5ohrees7Eic3`
   - `php demo_auth.php --username="sergei" --password="eer5ohrees7Eic3" --db_password="***"`
   
     Will print "Login successful" and exit with code 0
   - `php demo_auth.php --username="sergei" --password="eer5ohrees7Eic3___" --db_password="***"`
   
     Will print "Login failed: Wrong password" and exit with code 1
* `extention.json` MediaWiki extension config
* `includes` MediaWiki extension code
* `i18n` MediaWiki extension messages

### Installation
1. Install the PluggableAuth plugin: https://www.mediawiki.org/wiki/Extension:PluggableAuth
2. Link this directory to $mediawiki/extensions/AuthDjango. Make sure the user has the permissions
3. Edit `$mediawiki/LocalSettings.php`:

	```
	// Configuration variables
	$wgAuthDjangoConfig = array();

	$wgAuthDjangoConfig['DjangoDbPassword'] = "*****tournesol db password******";
	$wgAuthDjangoConfig['DjangoDbUsername'] = "tournesol";
	$wgAuthDjangoConfig['DjangoDbHost']     = "localhost";
	$wgAuthDjangoConfig['DjangoDbPort']     = 5432;

	$wgAuthDjangoConfig['DjangoDbName']     = 'tournesol';
	$wgAuthDjangoConfig['DjangoAuthTable']  = 'auth_user';


	// enable Django Tournesol Auth
	wfLoadExtension( 'AuthDjango' );
	wfLoadExtension( 'PluggableAuth' );

	// disabling registration
	$wgGroupPermissions['*']['createaccount'] = true;
	$wgGroupPermissions['*']['autocreateaccount'] = true;

	// can view wiki anonymously
	$wgPluggableAuth_EnableAutoLogin = false;

	// disable built-in auth
	$wgPluggableAuth_EnableLocalLogin = false;

	// disable built-in user attributes
	$wgPluggableAuth_EnableLocalProperties = false;
	```

4. To debug, enable in LocalSettings.php (don't forget to disable in production) and look in /var/log/mediawiki/

	```
	// ENABLE DEBUG
	$wgMainCacheType = CACHE_NONE;
	$wgCacheDirectory = false;
	$wgDebugLogFile = "/var/log/mediawiki/debug-{$wgDBname}.log";

	error_reporting( -1 );
	$wgShowExceptionDetails = true;
	ini_set( 'display_errors', 1 );
	```
