<?php

	//include routing
	require "./config.php";
	require "./bootstrap.php";
	require "./routes.php";
	
    // Fix magic quotes
    if(get_magic_quotes_gpc())
    {
        $_POST    = fix_slashes($_POST);
        $_GET     = fix_slashes($_GET);
        $_REQUEST = fix_slashes($_REQUEST);
        $_COOKIE  = fix_slashes($_COOKIE);
    }
	
    // Load our config settings
    $Config = Config::getConfig();

    // Store session info in the database?
    if(Config::get('useDBSessions') === true)
        DBSession::register();

    // Initialize our session
    session_name('spfs');
    session_start();
	
	//initiate route
	$route = new Route();
	$route->setRoutes( $routes );
	if(!isset($_GET['_route_']))$_GET['_route_'] = "/"; //prevent notice error from appearing
	$route->routeURL( preg_replace( "|/$|", "", $_GET['_route_'] ) );
	
    // Initialize current user
    $Auth = Auth::getAuth();

    // Object for tracking and displaying error messages
    $Error = Error::getError();