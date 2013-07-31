<?php
if (!defined('IN_D3'))
{
	exit;
}
//config file
class conf
{
// DB 
public static $db_type = 'mysql';
public static $db_login = 'pod12345_it';
public static $db_pass = 'pricebase';
public static $db_host = 'localhost';
public static $db_name = 'pod12345_resourses';

//LOG
public static $debug = true;

}



function __autoload ($class_name)
{
 $path=str_replace("_", "/", $class_name);
 require_once($path.".php");
}
?>
