<?php
/*****************************************************************
* Input wrapper for incoming data
******************************************************************/

class Input {

	# Set up inputs
	public function __construct() {

		$this->GET	  = $this->prepare($_GET);
		$this->POST	  = $this->prepare($_POST);
		$this->COOKIE = $this->prepare($_COOKIE);
		$this->FILES = $this->prepare($_FILES);

	}

	# Return array with keys converted to lowercase and values cleaned
	private function prepare($array) {

		$return = array();

		foreach ( $array as $key => $value ) {
			$return[strtolower($key)] = self::clean($value);
		}

		return $return;

	}

	# Get an input - inputs can be requested in the form pVarName
	# where VarName is (case insensitive) name of variable (duh!)
	# and p denotes from _POST. G and C are also available.
	public function __get($name) {

		# Do we have a varname?
		if ( ! isset($name[1]) ) {
			return NULL;
		}

		# Split into GPC and VarName (case insensitive)
		$from = strtolower($name[0]);
		$var	= strtolower(substr($name, 1));

		# Define $from to target relationships
		$targets = array('g' => $this->GET,
							  'p' => $this->POST,
							  'c' => $this->COOKIE,
							  'f' => $this->FILES);

		# Look for the value and return it
		if ( isset($targets[$from][$var]) ) {
			return $targets[$from][$var];
		}

		# Not found, return false
		return NULL;

	}

	# Clean a value
	static public function clean($val) {

		static $magicQuotes;

		# What is our magic quotes setting?
		if ( ! isset($magicQuotes) ) {
			$magicQuotes = get_magic_quotes_gpc();
		}

		# What type is this?
		switch ( true ) {
			case is_string($val):

				# Strip slashes and trim
				if ( $magicQuotes ) {
					$val = stripslashes($val);
				}

				$val = trim($val);

				break;

			case is_array($val):

				$val = array_map(array('Input', 'clean'), $val);

				break;

			default:
				return $val;
		}
		return $val;
	}

}