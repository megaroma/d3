<?php
class class_error_work extends class_error 
{

public function run(Exception  $e)
 {
   echo "Ошибка:".$e->getCode();
   exit;
 }




}




?>