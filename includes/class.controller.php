<?php

	class Controller
	{
	
		protected $get;
		protected $post;
		
		public $_request;
		public $view_ext = 'tp.php';
		
		public $_view;
		private $_view_path;
		private $_view_data;
		
		public $name;
		
		public $Auth;
		
		public function __construct()
		{

			$this->get = $_GET;
			$this->post = $_POST;
			
			if ($this->name === null) {
				$this->name = get_class($this);
			}
			
			$this->_view_path = strtolower($this->name);
			
			// Initialize current user
			$this->Auth = Auth::getAuth();
		}
		
		public function request()
		{
			
		}
		
		public function setview($view)
		{
			$view = $view.'.'.$this->view_ext;
			
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