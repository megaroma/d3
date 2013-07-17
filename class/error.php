<?php
if (!defined('IN_D3'))
{
	exit;
}
abstract class class_error
{

public static function getInstance()
    {
	if (conf::$debug)
       {
	   return new class_error_debug;
	   } else
	   {
	   return new class_error_work;
	   }

	}


abstract function run(Exception  $e);

}



?>