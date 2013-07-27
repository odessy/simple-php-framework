<?php

	//MySQL PDO (https://github.com/maxfierke/arcanecms/blob/master/includes/class.database.php) replacement written by Max Fierke.

    class Database
    {
        // Singleton object. Leave $me alone.
        private static $me;

        public $DB;
         
        public $type;
         
        public $Host;

        public $name;

        public $Username;
		public $Password;

        public $onError; // Can be '', 'die', or 'redirect'
        public $emailOnError;
        public $queries;
        public $result;

        public $emailTo; // Where to send an error report
        public $emailSubject;

        public $errorUrl; // Where to redirect the user on error

        // Singleton constructor
        private function __construct()
        {
            $this->type = Config::get('dbType');
            $this->name = Config::get('dbName');
            $this->onError = Config::get('dbOnError');
            $this->emailOnError = Config::get('dbEmailOnError');
            $this->queries = array();
            // MySQL specific stuff
            if($this->type=='mysql') {
                $this->writeHost = Config::get('dbHost');
                $this->Username = Config::get('dbUsername');
                $this->Password = Config::get('dbPassword');
                $this->DB = false;
            } else if($this->type=='sqlite') {
				$this->DB = false;
			} else {
				die('Unsupported database type specified in class.config.php');
			}
        }

        // Get Singleton object
        public static function getInstance()
        {
            if(is_null(self::$me))
                self::$me = new Database();
            return self::$me;
        }
        public function __wakeup()
        {
         $this->Connect();
        }
        public function __sleep()
        {
             if(is_object($this->DB)) {
              $this->DB = null;
             }
             return array_keys(get_object_vars($this));
        }

        // Do we have a valid read/write database connection?
        public function isConnected()
        {
            return is_object($this->DB);
        }

		// Are we using MySQL?
		public function isMySQL()
		{
			if($this->type=='mysql') return true;
			else return false;
		}

        // Do we have a valid database connection and have we selected a database?
        public function databaseSelected()
        {
            if(!$this->isConnected()) return false;
			if($this->isMySQL()) {
					$result = $this->readDB->query("SHOW TABLES FROM $this->name");
			} else {
				$this->Connect();
				$result = $this->readDB->query("SELECT name FROM sqlite_master WHERE type = 'table'");
			}
			return is_object($result);
        }

        public function Connect()
        {
			if($this->isMySQL()) {
				$this->DB = new PDO("mysql:dbname=$this->name;host=$this->Host", $this->Username, $this->Password) or $this->notify();
				if($this->DB === false) return false;
				$this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			} else {
				$this->DB = new PDO("sqlite:".DOC_ROOT.DS."db".DS."$this->name.sqlite") or $this->notify();
				if($this->DB === false) return false;
				$this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			}
            return $this->isConnected();
        }

        public function query($sql, $args_to_prepare = null)
        {
			$sql = trim($sql);
			if(!$this->isConnected()) $this->Connect();
			$this->queries[] = $sql;	
			$this->result = $this->DB->prepare($sql) or $this->notify();
			$this->result->execute($args_to_prepare);
			return $this->result;
        }
		
		public function showColumns($arg)
		{
			if($this->isMySQL())
			return $this->query("SHOW COLUMNS FROM $arg");
			else return $this->query("PRAGMA table_info($arg);");
		}
        // Returns the number of rows.
        // You can pass in nothing, a string, or a db result
        public function numRows($arg = null)
        {
            $result = $this->resulter($arg);
			return ($result !== false) ? $result->rowCount() : false;
        }
		// Returns the number of rows in the previously executed select statement
		public function rowCountResult()
		{
			if(!$this->isMySQL()) { // For SQLite only.
			if( strtoupper( substr( $this->lastQuery(), 0, 6 ) ) == 'SELECT' )
			{
             // Do a SELECT COUNT(*) on the previously executed query
             $res = $this->query('SELECT COUNT(*)' . substr( $this->lastQuery(), strpos( strtoupper( $this->lastQuery() ), 'FROM' ) ) )->fetch( PDO::FETCH_NUM );
             return $res[0];
             }
            }
             else return $this->result->rowCount(); // The last query was not a SELECT query. Return the row count normally.
		}
        // Returns true / false if the result has one or more rows
        public function hasRows($arg = null)
        {
            $result = $this->resulter($arg);
            return is_object($result) && ($this->rowCountResult() > 0);
		}

        // Returns the number of rows affected by the previous WRITE operation
        public function affectedRows()
        {
            if(!$this->isConnected()) return false;
			$ret = $this->result->rowCount();
			return $ret;
        }

        // Returns the auto increment ID generated by the previous insert statement
        public function insertId()
        {
            if(!$this->isConnected()) return false;
			$ret = ((is_null($this->DB->lastInsertId())) ? false : $this->DB->lastInsertId());
			return $ret;
		}

        // Returns a single value.
        // You can pass in nothing, a string, or a db result
        public function getValue($arg = null)
        {
            $result = $this->resulter($arg);
			return $this->hasRows($result) ? $result->fetchColumn() : false;
		}

        // Returns an array of the first value in each row.
        // You can pass in nothing, a string, or a db result
        public function getValues($arg = null)
        {
            $result = $this->resulter($arg);
            if(!$this->hasRows($result)) return array();

            $values = array();
			$values = $result->fetchAll((PDO::FETCH_COLUMN|PDO::FETCH_GROUP), 0);
            return $values;
        }

        // Returns the first row.
        // You can pass in nothing, a string, or a db result
        public function getRow($arg = null)
        {
            $result = $this->resulter($arg);
			return $this->hasRows($result) ? $result->fetch(PDO::FETCH_ASSOC) : false;
		}

        // Returns an array of all the rows.
        // You can pass in nothing, a string, or a db result
        public function getRows($arg = null)
        {
            $result = $this->resulter($arg);
            if(!$this->hasRows($result)) return array();

            $rows = array();
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
            return $rows;
        }

        // Same as escape()
        public function quote($var)
        {
			return $this->escape($var);
        }

        // Escapes a value.
        public function escape($var)
        {
            if(!$this->isConnected()) $this->Connect();
			$ret = $this->DB->quote($var);
			return $ret;
        }

        public function numQueries()
        {
            return count($this->queries);
        }

        public function lastQuery()
        {
            if($this->numQueries() > 0)
                return $this->queries[$this->numQueries() - 1];
            else
                return false;
        }

        private function notify()
        {
            if($this->emailOnError === true)
            {
                $globals = print_r($GLOBALS, true);

                $msg = '';
                $msg .= "Url: " . full_url() . "\n";
                $msg .= "Date: " . dater() . "\n";
                $msg .= "Server: " . $_SERVER['SERVER_NAME'] . "\n";

                //$msg .= "ReadDB Error:\n" . ((is_object($this->readDB)) ? mysqli_error($this->readDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "\n\n";
                //$msg .= "WriteDB Error:\n" . ((is_object($this->writeDB)) ? mysqli_error($this->writeDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "\n\n";

                ob_start();
                debug_print_backtrace();
                $trace = ob_get_contents();
                ob_end_clean();

                $msg .= $trace . "\n\n";

                $msg .= $globals;

                mail($this->emailTo, $this->emailSubject, $msg);
            }

            if($this->onError == 'die')
            {
				//echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Read Database Error:</strong><br/>" . ((is_object($this->readDB)) ? mysqli_error($this->readDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "</p>";
				//echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Write Database Error:</strong><br/>" . ((is_object($this->writeDB)) ? mysqli_error($this->writeDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "</p>";
				//$this->readDB->errorInfo();
				//$this->writeDB->errorInfo();
				//print_r($this->result->errorInfo());
				echo '<br />';
				echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Last Query:</strong><br/>" . $this->lastQuery() . "</p>";
                echo "<pre>";
                debug_print_backtrace();
                echo "</pre>";
                exit;
            }

            if($this->onError == 'redirect')
            {
                redirect($this->errorUrl);
            }
        }

        // Takes nothing, a MySQL result, or a query string and returns
        // the correspsonding MySQL result resource or false if none available.
        private function resulter($arg = null)
        {
            if(is_null($arg) && is_object($this->result))
                return $this->result;
            elseif(is_object($arg))
                return $arg;
            elseif(is_string($arg))
            {
                $this->query($arg);
                if(is_object($this->result))
                    return $this->result;
                else
                    return false;
            }
            else
                return false;
        }
    }