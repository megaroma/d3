<?php
if (!defined('IN_D3'))
{
	exit;
}
abstract class class_db
{

private $db_connect_id;
private $query_result='';
private $affected_rows=0;

 public static function getInstance($typ)
    {
	$class_name = "class_db_".$typ;
	return new $class_name;
	}

abstract function sql_server_info();
abstract function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false);
abstract function sql_query($query);
abstract function sql_transaction($status = 'begin');
abstract function sql_query_limit($query, $total, $offset = 0);
abstract function sql_fetchrow($query_id = false);
abstract function sql_freeresult($query_id = false);
abstract function sql_close();
abstract function sql_affected_rows();



}



?>