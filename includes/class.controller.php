<?php

	class Controller extends Auth
	{
	
		protected $get;
		protected $post;
		
		public $_request;
		
		public $_view;
		private $_view_path;
		private $_view_data;
		
		public $name;
		
		public function __construct()
		{

			$this->get = $_GET;
			$this->post = $_POST;
			
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
			
			$this->_view = DOC_ROOT.'/views/'.$view;

		}
		
		public function view()
		{
			if(!file_exists($this->_view))
				die('view file does not exist');
				
			$View = new View($this->_view, $this->_view_data);
			
			$html = $View->get();
			
			echo $html;
		}

	}