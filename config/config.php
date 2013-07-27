<?php
    // Application flag
    define('SPF', true);
	
	//Configuration Array
	$config = array();
	
	// Add your server hostnames to the appropriate arrays. ($_SERVER['HTTP_HOST'])
	// Each array item should be a regular expression. This gives you the option to detect a whole range
	// of server names if needed. Otherwise, you can simply detect a single server like '/^servername\.com$/'
	$config['Servers'] = array(
		'production' => array('/^your-domain\.com$/'),
		'staging' => array(),
		'local' => array('/^localhost$/', '/^127.0.0.1$/'),
	);
	
	
	// Add code/variables to be run only on production servers
	$config['production'] = array(
		'dbType'	=> 'mysql',
        'dbHost'      => 'localhost',
        'dbName'          => '',
		'dbUsername'  => '',
		'dbPassword'  => '',
		'dbOnError'       => '',
		'dbEmailOnError'  => false,
		'displayErrors' => 0
	);

	// Add code/variables to be run only on staging servers
	$config['staging'] = array(
		'dbType'	=> 'mysql',
        'dbHost'      => 'localhost',
        'dbName'          => '',
		'dbUsername'  => '',
		'dbPassword'  => '',
		'dbEmailOnError'  => 'die',
		'displayErrors' => 1,
		'errorReporting' =>  E_ALL	
	);
	
	// Add code/variables to be run only on local (testing) servers
	$config['local'] = array(
		'dbType'	=> 'mysql',
        'dbHost'      => 'localhost',
        'dbName'          => 'spf',
		'dbUsername'  => 'root',
		'dbPassword'  => '',
		'dbOnError'       => '',
		'dbEmailOnError'  => 'die',
		'displayErrors' => 1,
		'errorReporting' =>  E_ALL
	);
	
	// Add code/variables to be run only on when script is launched from the shell
	$config['shell'] = array(
		'dbType'	=> 'mysql',
        'dbHost'      => 'localhost',
        'dbName'          => '',
		'dbUsername'  => '',
		'dbPassword'  => '',
		'dbOnError'       => false,
		'dbEmailOnError'  => false,
		'displayErrors' => 1,
		'errorReporting' =>  E_ALL
	);