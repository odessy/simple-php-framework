<?php
    // Application flag
    define('SPF', true);
    defined('DS') ? null : define('DS',DIRECTORY_SEPARATOR);
    define('DOC_ROOT', realpath(dirname(__FILE__) . './'));
    define('APPLICATION_ROOT', realpath(dirname(__FILE__) . './application/'));
