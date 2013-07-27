<?php

	function autoload_classes($class_name)
	{
		$file = DOC_ROOT .'/includes/class.' . strtolower($class_name). '.php';

		if (file_exists($file))
		{
			require_once($file);
		}
	}

	function autoload_controllers($class_name)
	{
		$file = DOC_ROOT.'/controllers/' . strtolower($class_name). '.php';
		if (file_exists($file))
		{
			require_once($file);
		}
	}

	spl_autoload_register('autoload_classes');
	spl_autoload_register('autoload_controllers');
