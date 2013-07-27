<?php

	//autoload classes from the includes folder
	function autoload_classes($class_name)
	{
		$file = DOC_ROOT .'/includes/class.' . strtolower($class_name). '.php';

		if (file_exists($file))
		{
			require_once($file);
		}
	}

	//autoload controllers from the controller folder
	function autoload_controllers($class_name)
	{
		$file = DOC_ROOT.'/controllers/' . strtolower($class_name). '.php';
		if (file_exists($file))
		{
			require_once($file);
		}
	}
	
	//autoload models from the models folder
	function autoload_models($class_name)
	{
		$file = DOC_ROOT.'/models/' . strtolower($class_name). '.php';
		if (file_exists($file))
		{
			require_once($file);
		}
	}

	spl_autoload_register('autoload_classes');
	spl_autoload_register('autoload_controllers');
	spl_autoload_register('autoload_models');
