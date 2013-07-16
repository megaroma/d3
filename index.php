<?php
define('IN_D3', true);
include ("conf.php");

$core = new class_core();
$db = class_db::getInstance(conf::$db_type);
$error = class_error::getInstance();
$core->reg('db',$db);
$core->reg('error',$error);
$out_data = array();

try
{
 $db->sql_connect(conf::$db_host, conf::$db_login, conf::$db_pass, conf::$db_name);
} catch (Exception $e)
{
 $error->run($e);
}
$sql="SET CHARACTER SET cp1251";
try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$sql="SET NAMES cp1251";
try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$sql="SET time_zone = '+11:00'";
try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }


$route = $_GET["route"];
$router = new class_router($route);

$mod = $router->get_mod();

$mod->run($core,$out_data);




$view = new class_templ();
$view->show($out_data);
$db->sql_close();
?>