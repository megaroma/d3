<?php
if (!defined('IN_D3'))
{
	exit;
}


class class_core
{

private $s_modules = array();

public function reg($name,$s_mod)
{
if (array_key_exists($name, $this->s_modules)) 
  {
  return false;
  } else
  {
  $this->s_modules[$name] = $s_mod;
  return true;
  }
}

public function get($name)
{
if (array_key_exists($name, $this->s_modules)) 
  {
  return $this->s_modules[$name];
  } else
  {
  return false;
  }
}



}


?>