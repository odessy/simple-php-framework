<?php
	class Route extends Auth{
	
		protected $route_match      = false;
		protected $route_call       = false;
		protected $route_call_args  = false;
	 
		protected $routes           = array( );
	 
		public function __construct( ) {
			parent::__construct();
		} // function __construct( )
	 
		public function setRoutes( $routes ) {
			$this->routes = $routes;
		} // function setRoutes
	 
		public function routeURL( $url = false ) {
			// Look for exact matches
			if( isset( $this->routes[$url] ) ) {
				$this->route_match = $url;
				$this->route_call = $this->routes[$url];
	 
				$this->callRoute( );
				return true;
			}
	 
			// See if the first part of the route exists
			foreach( $this->routes as $path => $call ) {
				if( empty( $path ) ) {
					continue;
				}
	 
				preg_match( "|{$path}/(.*)$|i", $url, $match );
				if( !empty( $match[1] ) ) {
					$this->route_match = $path;
					$this->route_call = $call;
					$this->route_call_args = explode( "/", $match[1] );
	 
					$this->callRoute( );
					return true;
				} // if
			} // foreach
	 
	 
			// If no match was found, call the default route if there is one
			if( $this->route_call === false ) {
				if( !empty( $this->routes['_not_found_'] ) ) {
					$this->route_call = $this->routes['_not_found_'];
					$this->callRoute( );
					return true;
				}
			}
	 
		} // function routeURL( )
		
		// public function file_get_php_classes($filepath) {
		  // $php_code = file_get_contents($filepath);
		  // $classes = get_php_classes($php_code);
		  // return $classes;
		// }

		// public function get_php_classes($php_code) {
		  // $classes = array();
		  // $tokens = token_get_all($php_code);
		  // $count = count($tokens);
		  // for ($i = 2; $i < $count; $i++) {
			// if ( $tokens[$i - 2][0] == T_CLASS
				// && $tokens[$i - 1][0] == T_WHITESPACE
				// && $tokens[$i][0] == T_STRING) {

				// $class_name = $tokens[$i][1];
				// $classes[] = $class_name;
			// }
		  // }
		  // return $classes;
		// }
	 
		private function callRoute( ) {
			$call = $this->route_call;
	 
			if( is_array( $call ) ) {
				$call_obj = new $call[0]( );
				$call_obj->$call[1]( $this->route_call_args );
			}
			else {
				$call( $this->route_call_args );
			}
		} // function callRoute
		
	 
	} // class Route