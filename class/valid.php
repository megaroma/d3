<?php
/*
check
not_empty, email, number, en_text,

set
number, for_sql, for_output

get
from session,cookie,get,post
 with set:  none,number,for_sql,for_output,string

*/
if (!defined('IN_D3'))
{
	exit;
}

class class_valid
{


   public function check($value,$mod)
     {
         switch ($mod)
         {
             case 'not_empty':
             if (empty($value))
              {
              return false;
              } else
              {
              return true;
              }
             break;
             
             case 'email':
             if (!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $value))
              {
              return false;
              } else
              {
              return true;
              }
             break;
             
             case 'number':
             if (preg_match('/\D/s',$value))
              {
              return false;
              } else
              {
              return true;
              }
             break;

             
             default:
             return false;

         }

     }


     public function set($value,$mod)
     {
       switch ($mod)
         {
		 case 'number':
		 return intval($value);
		 break;
		 
		 case 'for_sql':
		 return mysql_real_escape_string($value);
		 break;
		 
		 case 'for_output':
		 $value = htmlspecialchars($value);
		 $value = nl2br($value);
		 return $value;
		 break;
		 
		  
          default:
          return false; 
		 }



     }
	 
	 public function get($value,$mod,$typ)
	 {
	 $buf = '';
	 switch ($typ)
	  {
	  case 'POST':
	  $buf = $_POST[$value];
	  break;
	  case 'GET':
	  $buf = $_GET[$value];
	  break;
	  case 'SESSION':
	  $buf = $_SESSION[$value];
	  break;
	  case 'COOKIE':
	  $buf = $_COOKIE[$value];
	  break;
	  default: 
	  return false;
	  }
	 
	 switch ($mod)
	  {
	  case 'none':
	  return $buf;
	  break;
	  case 'number':
	  return $this->set($buf,'number');
	  break;
	  case 'for_sql':
	  return $this->set($buf,'for_sql');
	  break;
	  case 'for_output':
	  return $this->set($buf,'for_output');
	  break;
	  default: 
	  return false;	  
	  }
	 
	 
	 }
	 
	 public function cut($value,$l)
	 {
	 return substr($value,0,$l);
	 //return mb_substr($value,0,$l);
	 }


}



?>
