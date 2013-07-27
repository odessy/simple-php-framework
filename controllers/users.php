<?php
	class Users extends Controller
	{
	
		public function login($args = false)
		{
		
			if($this->Auth->loggedIn())
				$this->redirect('/users');
				
			if(!empty($this->post['username']))
			{
				if($this->Auth->login($this->post['username'], $this->post['password']))
				{
					if(isset($_REQUEST['r']) && strlen($_REQUEST['r']) > 0)
						redirect($_REQUEST['r']);
					else
						$this->redirect('/users');
				}
			}
			
		}
		
		public function logout()
		{				
			$this->Auth->logout();
		}
		
		
		public function register()
		{
			if($this->Auth->loggedIn())
				$this->redirect('/users');
			
			if(!empty($this->post['username']))
			{
				$this->Auth->createNewUser($this->post['username'], $this->post['password']);
			}
			
		}
		
		public function index()
		{
		}
	
	}