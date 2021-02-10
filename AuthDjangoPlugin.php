<?php
// Django authentication plugin.
// Based on https://github.com/mpetroff/django-mediawiki-authentication/blob/master/AuthDjango.php

// Configuration variables
$wgAuthDjangoConfig = array();

$wgAuthDjangoConfig['DjangoDbPassword'] = "Tetu8raiwieGh3I";
$wgAuthDjangoConfig['DjangoDbUsername'] = "tournesol";
$wgAuthDjangoConfig['DjangoDbHost']     = "localhost";
$wgAuthDjangoConfig['DjangoDbPort']     = 5432;

$wgAuthDjangoConfig['DjangoDbName']     = 'tournesol';
$wgAuthDjangoConfig['UserTable']        = 'auth_user';
$wgAuthDjangoConfig['DjangoAuthTable']  = 'user_auth';

// Load classes
$wgAutoloadClasses['AuthPlugin'] = dirname('./include') . '/AuthDjangoPlugin.php';
$wgAutoloadClasses['AuthDjango'] = dirname(__FILE__) . '/AuthDjangoPlugin.body.php';

$wgExtensionFunctions[] = "initAuthDjango";
function initAuthDjango() {
    global $wgAuth;
    $wgAuth = new AuthDjango();     // Initiate Auth Plugin
}
