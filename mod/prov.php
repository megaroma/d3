<?php
if (!defined('IN_D3'))
{
	exit;
}

class mod_prov
{
public $route;

public function __construct($r)
 {
 $this->route = $r;
 }


public function run($core,&$out_data)
{

$db = $core->get("db");
$error = $core->get("error");
$rr = explode("/", $this->route,4);

if ($rr[0] == 'add')
 {
 if (($_POST["pname"] <> '' ))
  {
  $val = new class_valid();
  $p_name = $val->get('pname','for_sql','POST');
  $p_info = $val->get('pinfo','for_sql','POST'); 
  $sql = "insert into d3_providers 
          select (select  max(id) + 1 from d3_providers),\"$p_name\",now(),\"$p_info\" from dual";
  try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
  
  
  }
 }





//---------------------------------------------------------------------------------------------------
$sql = "select id,name,dt,info from d3_providers";
try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$i = 0;
while($row = $db->sql_fetchrow($res))
{
$i++;
$out_data['prov'][$i]['id'] = $row["id"];
$out_data['prov'][$i]['name'] = $row["name"];
$out_data['prov'][$i]['dt'] = $row["dt"];
$out_data['prov'][$i]['info'] = $row["info"];
}




$out_data['out_view'] = "html/prov.htm";
$out_data['text'] = "Модуль prov";
$out_data['full_text'] = "Поставщики";
$out_data['l4_act'] = "class=\"active\"";


} 


}




?>