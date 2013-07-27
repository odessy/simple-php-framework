<?php
  /**
	*  routes in $routes array variable
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
		"home" => array("Pages", "homePage"),
	);
