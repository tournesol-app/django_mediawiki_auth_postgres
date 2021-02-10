## Authenticate mediawiki users with a Django postgres database

This repository implements a custom authenticator for MediaWiki that tries to log in with credentials
stored in a Django Posgres database. Currently, only pbkdf2-sha256 hashing is supported.

### File structure:

* `postgres_django_auth.php` -- function to authenticate
* `demo_auth.php` -- command-line interface for the function
