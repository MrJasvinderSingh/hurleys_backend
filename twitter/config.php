<?php

require_once 'messages.php';

//site specific configuration declartion
define( 'BASE_PATH', '#');
define( 'DB_HOST', 'localhost' );
define( 'DB_USERNAME', '');
define( 'DB_PASSWORD', '');
define( 'DB_NAME', '');

//Twitter login
define('TWITTER_CONSUMER_KEY', '');
define('TWITTER_CONSUMER_SECRET', '');
define('TWITTER_OAUTH_CALLBACK', '#');



function __autoload($class)
{
	$parts = explode('_', $class);
	$path = implode(DIRECTORY_SEPARATOR,$parts);
	require_once $path . '.php';
}
