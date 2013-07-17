<?php

if (!defined('IN_D3'))
{
	exit;
}

class mod_brand
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
//----------------------add-----------------------------------------------------

if ($rr[0] == 'add_brand')
 {
 if ($_POST['bra'] <> '')
  {
  $bra = $_POST['bra'];
  $sql = "select id from d3_brands where brand = REPLACE(upper(\"$bra\"),\" \",\"\")";
  try
  {
   $res=$db->sql_query($sql);
  } catch (Exception $e)
  {
  $error->run($e);
  }
  $row = $db->sql_fetchrow($res);
  if ($row["id"] <> '')
  {
  $out_data['add_bra'] = $bra;
  $out_data['stats'] = "Такой бренд есть в базе!!!";
  } else
  {
  if ($_POST['org']) 
  {
  $kind = 1;
  } else
  {
  $kind = 2;
  }
  $sql = "insert into d3_brands (id,typ,brand,kind) 
          select (select max(id) + 1 from  d3_brands),1,REPLACE(upper(\"$bra\"),\" \",\"\"),$kind from dual";
  try
  {
   $res=$db->sql_query($sql);
  } catch (Exception $e)
  {
  $error->run($e);
  }
  $out_data['stats'] = "Добавлен бренд $bra";
  }
  }
 }
 
 //------------------add syn-------------------------------------------------------------
if ($rr[0] == 'add_syn')
{
if ($_POST['bra'] <> '')
{
$id =  $_POST['id'];
$bra = $_POST['bra'];
  $sql = "select id from d3_brands where brand = REPLACE(upper(\"$bra\"),\" \",\"\")";
  try
  {
   $res=$db->sql_query($sql);
  } catch (Exception $e)
  {
  $error->run($e);
  }
  $row = $db->sql_fetchrow($res);
  if ($row["id"] <> '')
  {
  $out_data['add_sbra'] = $bra;
  $out_data['id_bra'] = $id;
  $out_data['stats'] = "Такой бренд есть в базе!!!";
  } else
  {

$sql = "insert into d3_brands (id,typ,brand) 
        select $id,2,REPLACE(upper(\"$bra\"),\" \",\"\") from dual";
  try
  {
   $res=$db->sql_query($sql);
  } catch (Exception $e)
  {
  $error->run($e);
  }
  $out_data['stats'] = "Добавлен синоним $bra"; 
  }
} 
}
//---------------del-------------------------------------------
if ($rr[0] == 'del')
 {
 if ($rr[1] == '1')
  { //бренд
  $sql = "delete from d3_brands where id = ".$rr[2];
  try
  {
   $res=$db->sql_query($sql);
  } catch (Exception $e)
  {
  $error->run($e);
  } 
  $out_data['stats'] = "Удален бренд ".$rr[3];
  } else
  { //синоним
  $sql = "delete from d3_brands where id = ".$rr[2]." and typ = 2 and brand = REPLACE(upper(\"".$rr[3]."\"),\" \",\"\")";
  try
  {
   $res=$db->sql_query($sql);
  } catch (Exception $e)
  {
  $error->run($e);
  }  
  $out_data['stats'] = "Удален синоним ".$rr[3];
  }
 
 }

 



//-----------------main---------------------------------------------------
$sql="select id,typ,brand,kind from d3_brands order by id";
try
{
$res=$db->sql_query($sql);
} catch (Exception $e)
{
 $error->run($e);
}

$i =0;

while($row = $db->sql_fetchrow($res))
 {
 $i++;
 $out_data['brands'][$i]['id'] = $row["id"];
 $out_data['brands'][$i]['typ'] = $row["typ"];
 $out_data['brands'][$i]['brand'] = $row["brand"];
 if ($row["kind"] == 1) { $out_data['brands'][$i]['kind'] =  "Оригинал"; }
 if ($row["kind"] == 2) { $out_data['brands'][$i]['kind'] =  "Не оригинал"; }
 }


$out_data['out_view'] = "html/brand.htm";
$out_data['text'] = "Модуль brand";
$out_data['full_text'] = "Управление брендами в системе.";
$out_data['l2_act'] = "class=\"active\"";





} 


}




?>