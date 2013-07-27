<?php

	class Controller extends Auth
	{
		public $_request;
		public $_view;
		
		private function __contruct()
		{
			$this->view = "";
		}
		
		public function request()
		{
			
		}
		
		public function setview($view)
		{
			//inlclude view
			$view = APPLICATION_ROOT.'/views/'.$view;
			
			if(file_exists($view))
				include $view;
		}

	}
	