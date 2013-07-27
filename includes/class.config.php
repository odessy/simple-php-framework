<?PHP
    // The Config class provides a single object to store your application's settings.
    // Define your settings as public members. (We've already setup the standard options
    // required for the Database and Auth classes.) Then, assign values to those settings
    // inside the "location" functions. This allows you to have different configuration
    // options depending on the server environment you're running on. Ex: local, staging,
    // and production.

    class Config
    {
        // Singleton object. Leave $me alone.
        private static $me;

        // Add your server hostnames to the appropriate arrays. ($_SERVER['HTTP_HOST'])
        // Each array item should be a regular expression. This gives you the option to detect a whole range
        // of server names if needed. Otherwise, you can simply detect a single server like '/^servername\.com$/'
        private $productionServers = array('/^your-domain\.com$/');
        private $stagingServers    = array();
        private $localServers      = array();

        // Standard Config Options...
		private $_config = array();

        // ...For Auth Class
        public $authDomain;         // Domain to set for the cookie
        public $authSalt;           // Can be any random string of characters

        // ...For Database Class
        public $dbReadHost;   // Database read-only server
        public $dbWriteHost;  // Database read/write server
        public $dbName;
        public $dbReadUsername;
        public $dbWriteUsername;
        public $dbReadPassword;
        public $dbWritePassword;

        public $dbOnError; // What do do on a database error (see class.database.php for details)
        public $dbEmailOnError; // Email an error report on error?

        // Add your config options here...
        public $useDBSessions; // Set to true to store sessions in the database

        // Singleton constructor
        private function __construct($config)
        {
			$this->_config = $config;
			
            $this->everywhere();

            $i_am_here = $this->whereAmI();

            if('production' == $i_am_here)
                $this->setConfig('production');
            elseif('staging' == $i_am_here)
                $this->setConfig('staging');
            elseif('local' == $i_am_here)
                $this->setConfig('local');
            elseif('shell' == $i_am_here)
                $this->setConfig('shell');
            else
                die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>class.config.php</code></p>
                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
        }

        // Get Singleton object
        public static function getConfig($config = null)
        {
            if(is_null(self::$me))
                self::$me = new Config($config);
            return self::$me;
        }

        // Allow access to config settings statically.
        // Ex: Config::get('some_value')
        public static function get($key)
        {
            return self::$me->$key;
        }

        // Add code to be run on all servers
        private function everywhere()
        {
            // Store sesions in the database?
            $this->useDBSessions = true;

            // Settings for the Auth class
            $this->authDomain = $_SERVER['HTTP_HOST'];
            $this->authSalt   = '';
        }

        // Set configuratioin to be run by server
        private function setConfig($type)
        {
            ini_set('display_errors', $this->_config[$type]['displayErrors']);
			
			if(isset($this->_config[$type]['errorReporting']))
				ini_set($this->_config[$type]['errorReporting'], E_ALL);
				
				
			//check that database name is present
			if(empty($this->_config[$type]['dbName']))
				die('<h1>Database Config Settings?</h1> <p>You need to setup your database name in <code>config.php</code></p>');

            $this->dbType      = $this->_config[$type]['dbType'];
            $this->dbHost     = $this->_config[$type]['dbHost'];
            $this->dbName          = $this->_config[$type]['dbName'];
            $this->dbUsername  = $this->_config[$type]['dbUsername'];
            $this->dbPassword = $this->_config[$type]['dbPassword'];
            $this->dbOnError       = $this->_config[$type]['dbOnError'];
            $this->dbEmailOnError  = $this->_config[$type]['dbEmailOnError'];
        }

        public function whereAmI()
        {
            for($i = 0; $i < count($this->_config['Servers']['production']); $i++)
                if(preg_match($this->_config['Servers']['production'][$i], getenv('HTTP_HOST')) === 1)
                    return 'production';

            for($i = 0; $i < count($this->_config['Servers']['staging']); $i++)
                if(preg_match($this->_config['Servers']['staging'][$i], getenv('HTTP_HOST')) === 1)
                    return 'staging';

            for($i = 0; $i < count($this->_config['Servers']['local']); $i++)
                if(preg_match($this->_config['Servers']['local'][$i], getenv('HTTP_HOST')) === 1)
                    return 'local';

            if(isset($_ENV['SHELL']))
                return 'shell';

            return false;
        }
    }
