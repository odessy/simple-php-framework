<?php

	class Controller extends Core
	{
		protected $input;
		
		public $_request;
		public $view_ext = 'tp.php';
		
		private $_layout;
		
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

			//Initialize an input class
			$this->input = new Input();
			
			if ($this->name === null) {
				$this->name = get_class($this);
			}
			
			$this->_view_path = strtolower($this->name);
			
			//set default template file
			$this->setLayout('default');
			
			// Initialize current user
			$this->Auth = Auth::getAuth();
		}


		public function setView($view)
		{
			$view = $view.'.'.$this->view_ext;
			
			if(count(explode(DS, $view)) == 1)
			{
				$view = $this->_view_path.DS.$view;
			}
		
			$this->_view = DOC_ROOT.'/views/'.$view;

		}
		
		public function setLayout($view)
		{
			$layout = $view.'.'.$this->view_ext;
			
			$this->_layout = DOC_ROOT.'/views/layouts'.DS.$layout;
		}

		public function setData($key, $value)
		{
			$this->_view_data[$key] = $value;
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

			if(!file_exists($this->_layout))
				die('layout file does not exist');
			
			$LayoutView = new View($this->_layout, $this->_view_data);
			
			$html = $LayoutView->get();
			
			echo $html;
		}

	}