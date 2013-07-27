<?php
	class Route extends Controller{
	
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
			
				$paths = explode('/', trim(preg_replace('/^(.*)\?.*$/U','$1',trim($url)),'/') );
				
				if(!empty($paths))
				{
				
					$this->route_call = $paths;
					
					if (class_exists($paths[0])) {
					
						$class = new $paths[0];
						
						if(isset($paths[1]) && is_callable(array($class, $paths[1]))){
							
							$this->route_call[1] = $paths[1];
							preg_match( "|{".$paths[0]."/".$paths[1]."}/(.*)$|i", $url, $match );
							
							if( !empty( $match[1] ) ) {
								$this->route_call_args = explode( "/", $match[1] );
							}
						}
						else if( is_callable(array($class, 'index')) ){
							$this->route_call[1] = 'index';
							$this->route_call_args = (array)$paths[1];
						}
						else
						{
							return $this->notFound();
						}
						
						
						$this->callRoute( );
						return true;
							
					
					}
				
				}
			}
			
			return $this->notFound();
	 
		} // function routeURL( )
		
		
		private function notFound() {
			if( !empty( $this->routes['_not_found_'] ) ) {
				$this->route_call = $this->routes['_not_found_'];
				$this->callRoute( );
				return true;
			}
		}
		
	 
		private function callRoute( ) {
			$call = $this->route_call;
	 
			if( is_array( $call ) ) {				
				$call_obj = new $call[0]( );
				$call_obj->$call[1]( $this->route_call_args );
				//initiate view for object
				$call_obj->view();
			}
			else {
				$call( $this->route_call_args );
			}
		} // function callRoute
		
	 
	} // class Route