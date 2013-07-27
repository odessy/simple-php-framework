<?php
	class Users extends Controller
	{
	
		public function login($args = false)
		{
			if(!empty($this->post['username']))
			{
				if($this->Auth->login($this->post['username'], $this->post['password']))
				{
					if(isset($_REQUEST['r']) && strlen($_REQUEST['r']) > 0)
						redirect($_REQUEST['r']);
				}
			}
			
		}
		
		public function logout()
		{
			$this->Auth->logout();
		}
		
		
		public function register()
		{
		
		}
	
	}