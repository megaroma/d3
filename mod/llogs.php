<?php
if (!defined('IN_D3'))
{
	exit;
}

class mod_llogs
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


if ($rr[0] == 'del')
{//------запрос на управление  данными

if ($_POST["del"] <> '')
 {//---удаление
 $sql = "select id from d3_load_log where stat = 1";
 try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $row = $db->sql_fetchrow($res);
 if ($row["id"] <> '')
 { 
 $out_data['stats'] =  "Операция не выполнена, файл находится в обработке!!!";
 } else
 { //откат последней записи
 $sql = "select l.id as id, l.seq_gr_id as seq_gr_id, l.seq_art_id  as seq_art_id from  d3_load_log l where l.id = (select max(m.id) from  d3_load_log m where m.stat <> 3)";
 try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $row = $db->sql_fetchrow($res);
 $l_id =  $row['id'];
 $seq_gr_id =  $row['seq_gr_id'];
 $seq_art_id =  $row['seq_art_id'];
 $sql = "delete from d3_lookup where l_id = $l_id"; 
 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $sql = "delete from d3_sgroups where 
         fd > (select l.dt  from d3_load_log l where l.id = $l_id )";		 
 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $sql = "update d3_sgroups d set d.td = STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
        where d.td < STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
        and d.td >  (select l.dt from d3_load_log l where l.id = $l_id )";
 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $sql = "update `d3_seq_art_id` set id = $seq_art_id";
 try {$row = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $sql = "ALTER TABLE `d3_seq_art_id` auto_increment = $seq_art_id";
 try {$row = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $sql = "update `d3_seq_gr_id` set id = $seq_gr_id";
 try {$row = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $sql = "ALTER TABLE `d3_seq_gr_id` auto_increment = $seq_gr_id";
 try {$row = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $sql = "update d3_load_log set stat = 3 where id = $l_id";
 try {$row = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $out_data['stats'] =  "Последняя загрузка успешно удалена.";
 }
 
 
 }//-----end del
}




$sql = "
select get_cur_d3_art_id() as cur_art_id, 
       get_cur_d3_gr_id() as cur_gr_id
from dual	   
";
try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$row = $db->sql_fetchrow($res);
$out_data['cur_art_id'] = $row['cur_art_id'];
$out_data['cur_gr_id'] = $row['cur_gr_id'];

$sql = "select
id, dt, seq_art_id, seq_gr_id, fname, stat
from d3_load_log order by id
";
try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$i = 0;
while ($row = $db->sql_fetchrow($res))
{
$i++;
$out_data['log'][$i]['id'] = $row['id'];
$out_data['log'][$i]['dt'] = $row['dt'];
$out_data['log'][$i]['seq_art_id'] = $row['seq_art_id'];
$out_data['log'][$i]['seq_gr_id'] = $row['seq_gr_id'];
$out_data['log'][$i]['fname'] = $row['fname'];
if ($row['stat'] == 1)  { $out_data['log'][$i]['stat'] = "Обрабатывается";}
if ($row['stat'] == 2)  { $out_data['log'][$i]['stat'] = "Загружен <a href=\"logs/log_".$row['id'].".txt\">Лог</a>";}
if ($row['stat'] == 3)  { $out_data['log'][$i]['stat'] = "Удален";}

}
if ($i > 0 ) { $out_data['log_yes'][1]= array(); }

$out_data['out_view'] = "html/llogs.htm";
$out_data['text'] = "Модуль llogs";
$out_data['full_text'] = "Просмотр истории загрузок";
$out_data['d4_act'] = "class=\"active\"";


} 


}



?>