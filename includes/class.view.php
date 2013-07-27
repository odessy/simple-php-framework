<?php

	class View 
	{
	
		private $view;
		private $data;
	
		public function __construct($view, $data)
		{
			$this->view = $view;
			$this->data = $data;
			
			if(!file_exists($view))
				return;
			
			return true;
		
		}
		
		public function get()
		{
			$data = $this->data;
			ob_start();
			require_once($this->view);
			$buffer = ob_get_contents();
			ob_end_clean();
			return $buffer;
		
		}
	
	}