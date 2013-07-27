<?php

	class Controller extends Core
	{
	
		protected $get;
		protected $post;
		
		public $_request;
		public $view_ext = 'tp.php';
		
		public $layout;
		
		public $_view;
		private $_view_path;
		private $_view_data = array();
		
		protected $javascripts = array();
		protected $stylesheets = array();
		
		public $name;
		public $path;
		
		public $Auth;
		
		public function __construct()
		{

			$this->get = $_GET;
			$this->post = $_POST;
			
			if ($this->name === null) {
				$this->name = get_class($this);
			}
			
			$this->_view_path = strtolower($this->name);
			
			//set default template file
			$this->layout = 'default';
			
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
				
			$this->_view_data['javascripts'] = $this->javascripts;
			$this->_view_data['stylesheets'] = $this->stylesheets;
			$this->_view_data['path'] = $this->path;
				
			$View = new View($this->_view, $this->_view_data);
			$this->_view_data['content'] = $View->get();
			
			$layout = DOC_ROOT.'/views/layouts'.DS.$this->layout.'.'.$this->view_ext;
			
			if(!file_exists($layout))
				die('layout file does not exist');
			
			$LayoutView = new View($layout, $this->_view_data);
			
			$html = $LayoutView->get();
			
			echo $html;
		}

	}