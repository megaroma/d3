<?php
if (!defined('IN_D3'))
{
	exit;
}

class class_router
{
private $route;

public function __construct($r)
 {
 $this->route = $r;
 }

public function get_mod()
 {
 $m = explode("/", $this->route,2);
 if ($m[0] == '') return new mod_index($m[1]);
 
 if(file_exists( "mod/".$m[0].".php")) 
  {
  $mod_name = "mod_".$m[0];
  return new $mod_name($m[1]);
  } else
  {
  return new mod_index($m[1]);
  }
 
 }
 



}

?>