<?php
if (!defined('IN_D3'))
{
	exit;
}

class mod_brandna
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
$act_panel = 1;


if ($rr[0] == 'pan1')
{//------запрос на управление  данными в первой панели
$act_panel = 1;

if ($_POST["view"] <> '')
 {//---просмотр
  $sql = '
  select 
  "N/A" as bra,
  p.art as art,
  (select (select b.brand from d3_brands b where b.id = l.brand_id and typ = 1 )  from d3_lookup l  where 
  p.art = l.art
  and l.brand_id <> 657
  and l.kind = 1
  group by l.brand_id
  ) as new_bra
  from d3_add_lookup p 
  where p.brand_id = 657
  and 
 (select count(DISTINCT l.brand_id)   from d3_lookup l  where 
  p.art = l.art
  and l.kind = 1
  and l.brand_id <> 657
  ) = 1';
  try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
  $i= 0;
  while($row = $db->sql_fetchrow($res))
   {
   $i++;
   $out_data['bra'][$i]['bra'] = $row['bra'];   
   $out_data['bra'][$i]['art'] = $row['art'];   
   $out_data['bra'][$i]['new_bra'] = $row['new_bra'];   
   }
   if ($i > 0) { $out_data['bra_yes'][1] = array(); }
 } 

if ($_POST["go"] <> '')
 {//---замена
 $sql = "update d3_add_lookup p  set p.brand_id = (select l.brand_id   from d3_lookup l  where p.art = l.art and l.brand_id <> 657 and l.kind = 1 group by l.brand_id)
         where (select count(DISTINCT l.brand_id)   from d3_lookup l  where p.art = l.art and l.brand_id <> 657 and l.kind = 1 ) = 1 and p.brand_id = 657";
 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); } 
 $sql = 'update d3_add_lookup p  set p.art_bra_md5 = unhex(md5(upper(replace(concat(p.brand_id,p.art)," ",""))))
         where p.art_bra_md5 <> unhex(md5(upper(replace(concat(p.brand_id,p.art)," ",""))))';
 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); } 		 
 $out_data['stats'] = "Ѕренды обновлены.";

 } 



}//------end panel1



if ($rr[0] == 'pan2')
{//------запрос на управление  данными в первой панели
$act_panel = 2;
if ($_POST["view"] <> '')
 {//---просмотр
 $sql = 'select 
 "N/A" as bra,
 l.art as art,
 (
 select (select b.brand from d3_brands b where b.id = p.brand_id and typ = 1 )   from d3_add_lookup p   where 
 p.art = l.art
 and p.kind = 1
 and p.brand_id <> 657
 group by p.brand_id
 ) as new_bra
 from d3_lookup l
 where l.brand_id = 657
 and l.kind = 1
 and 
 (select count(DISTINCT p.brand_id)   from d3_add_lookup p   where 
 p.art = l.art
 and p.kind = 1
 and p.brand_id <> 657
 ) = 1';
   try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
  $i= 0;
  while($row = $db->sql_fetchrow($res))
   {
   $i++;
   $out_data['bra2'][$i]['bra'] = $row['bra'];   
   $out_data['bra2'][$i]['art'] = $row['art'];   
   $out_data['bra2'][$i]['new_bra'] = $row['new_bra'];   
   }
   if ($i > 0) { $out_data['bra2_yes'][1] = array(); }

 }

 
 if ($_POST["go"] <> '')
 {//---замена
 $sql = "update d3_lookup l set l.brand_id = (select  p.brand_id  from d3_add_lookup p   where p.art = l.art and p.kind = 1 and p.brand_id <> 657 group by p.brand_id)
        where (select count(DISTINCT p.brand_id)   from d3_add_lookup p   where p.art = l.art and p.kind = 1 and p.brand_id <> 657 ) = 1 
        and l.brand_id = 657 and l.kind = 1";
 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); } 
 $sql = 'update d3_lookup l set l.art_bra_md5 = unhex(md5(upper(replace(concat(l.brand_id,l.art)," ",""))))
where l.art_bra_md5 <> unhex(md5(upper(replace(concat(l.brand_id,l.art)," ",""))))';
 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); } 		 
 $out_data['stats'] = "Ѕренды обновлены.";

 } 



}//------end panel2

$sql = "select count(*) as kol from d3_add_lookup where brand_id = 657";
try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$row = $db->sql_fetchrow($res);
$out_data['na_from_add'] = $row['kol'];

$sql = "select count(*) as kol from d3_lookup where brand_id = 657";
try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$row = $db->sql_fetchrow($res);
$out_data['na_from_lookup'] = $row['kol'];


if ($act_panel == 1) 
{
$out_data['pan1'] = 'active';
} else 
{
$out_data['pan2'] = 'active';
}
$out_data['out_view'] = "html/brandna.htm";
$out_data['text'] = "ћодуль brandna";
$out_data['full_text'] = "ѕоиск бренда дл€ N/A записи.";
$out_data['d3_act'] = "class=\"active\"";


} 


}



?>