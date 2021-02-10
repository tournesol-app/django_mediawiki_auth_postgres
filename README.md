## Authenticate mediawiki users with a Django postgres database

This repository implements a custom authenticator for MediaWiki that tries to log in with credentials
stored in a Django Posgres database. Currently, only pbkdf2-sha256 hashing is supported.

### File structure:

* `postgres_django_auth.php` -- function to authenticate
* `demo_auth.php` -- command-line interface for the function

   Example:
   - `python manage.py changepassword sergei`, enter `eer5ohrees7Eic3`
   - `php demo_auth.php --username="sergei" --password="eer5ohrees7Eic3" --db_password="***"`
   
     Will print "Login successful" and exit with code 0
   - `php demo_auth.php --username="sergei" --password="eer5ohrees7Eic3___" --db_password="***"`
   
     Will print "Login failed: Wrong password" and exit with code 1
