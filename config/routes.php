<?php
	/**
	*  Routes are automatically generated aditional 
	*  routes can be specified $routes array variable
	*/
	
	//example
	// $routes = array(
		// ""  => array( "EVENT", "index" ),
		// "index/user"  => array( "USERS", "index" ),
		// "about"  => array( "HOME", "aboutPage" ),
		// "home" => array("HOME", "homePage"),
	// );
	
	$routes = array(
		//place routes here
		//route	 =>(controller,	function)
		"login" => array("users", "login"),
		"register" => array("users", "register"),
		"logout" => array("users", "logout"),
	);
