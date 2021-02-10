<?php
// Django authentication plugin.
// Based on https://github.com/mpetroff/django-mediawiki-authentication/blob/master/AuthDjango.php
//

use Hooks;
use MWException;
use PluggableAuth;
use MediaWiki\Session\SessionManager;
use PluggableAuthLogin;
use RequestContext;
use User;

include_once('AuthDjangoPlugin.body.php');
