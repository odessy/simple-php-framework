<?php

	class Controller extends Route
	{
		public $_request;
		
		public $_view;
		
		public $name;
		
		public $_view_path;
		
		public function __construct()
		{			
			if ($this->name === null) {
				$this->name = get_class($this);
			}
			
			$this->_view_path = strtolower($this->name);
			
			parent::__construct();
		}
		
		public function request()
		{
			
		}
		
		public function setview($view)
		{
			if(count(explode(DS, $view)) == 1)
			{
				$view = $this->_view_path.DS.$view;
			}
			
			//inlclude view
			$view = APPLICATION_ROOT.'/views/'.$view;
			
			if(file_exists($view))
				include $view;
		}

	}
	