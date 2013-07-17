<?php

if (!defined('IN_D3'))
{
	exit;
}

class mod_loadcsv
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


//-------- запрос на добавление файла
if (($rr[0] == 'load') && ($_FILES["filename"]["tmp_name"] <> '') )
{
   if(is_uploaded_file($_FILES["filename"]["tmp_name"]))
   {
   move_uploaded_file($_FILES["filename"]["tmp_name"], "upload/".$_FILES["filename"]["name"]);
   $out_data['stats'] = "Файл "."upload/".$_FILES["filename"]["name"]." загружен<br>";	
   	$handle = @fopen("upload/".$_FILES["filename"]["name"], "r");
	if ($handle) 
	   {
	   $sql = "delete from d3_pre_lookup";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $sql = "delete from d3_add_lookup";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $sql = "delete from d3_load_log where stat = 1";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $i=0;
	   
	    while (($buffer = fgets($handle)) !== false) {		 
		$i++;
		$buf = explode(";", $buffer,4);
		$sql = "insert into d3_pre_lookup values (\"".$buf[0]."\",\"".$buf[1]."\",\"".trim($buf[2])."\",\"".trim($buf[3])."\")";
	    try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
        }
        fclose($handle);
		
     	$sql = "insert into d3_load_log 
	            select null, now(), get_cur_d3_art_id(),get_cur_d3_gr_id(),\"".$_FILES["filename"]["name"]."\",1 from dual;";
		try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
        $out_data['stats'] = "Загружен файл ".$_FILES["filename"]["name"].", загружено строк: $i";
	   
	   } else 
	   {
	   $out_data['stats'] = "Ошибка чтения загруженого файла";
	   }
   
      } else {
	  $out_data['stats'] = "Ошибка загрузки файла";
   }
} //----end загрузка из файла


if ($rr[0] == 'go')
{//------запрос на управление  данными

if ($_POST["del"] <> '')
 {//---удаление
	   $sql = "delete from d3_pre_lookup";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $sql = "delete from d3_add_lookup";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $sql = "delete from d3_load_log where stat = 1";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); } 
 $out_data['stats'] = "Данные удалены.";	   
 }

if ($_POST["go"] <> '')
 {//----отправляем данные 
 $sql = "select bra from
 (select  p.bra as bra, 
  (select id from d3_brands b where b.brand = REPLACE(upper(p.bra),\" \",\"\")) as b_id
  from d3_pre_lookup p) l where b_id is null";  
  try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
  if($db->sql_fetchrow($res))
  {
  $out_data['stats'] = "Невозможно передать данные (обнаружены не извесные бренды).";
  
  } else
  {//--передача
   $sql = "insert into d3_add_lookup
   select 
   (select b.id from d3_brands b where b.brand =  REPLACE(upper(f.bra),\" \",\"\")),
   REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(f.disp,\" \",\"\"),\"-\",\"\"),\".\",\"\"),\"/\",\"\"),\"#\",\"\"),
   f.disp,
   d.art_id,
   f.kind,
   UNHEX(MD5(upper(REPLACE(CONCAT((select b.id from d3_brands b where b.brand =  REPLACE(upper(f.bra),\" \",\"\")),REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(f.disp,\" \",\"\"),\"-\",\"\"),\".\",\"\"),\"/\",\"\"),\"#\",\"\")),\" \",\"\"))))  		
   from d3_pre_lookup f,		
   (select n,get_next_d3_art_id() as art_id from d3_pre_lookup 
   group by n) d
   where f.n = d.n;";
   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
   $out_data['stats'] = "Данные переданы на добавление.";	    
   $sql = "delete from d3_pre_lookup";
   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
  
  
  }//--end передача
 
 
 
 }
} //----end--управление




//---------просмотр--------------------------
$sql = "select id from d3_load_log where stat = 1";
try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
if ($db->sql_fetchrow($res)) 
{ // -----Загруженый файл есть

$sql = "select count(*) as kol from d3_pre_lookup";
try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$row = $db->sql_fetchrow($res);
if ($row["kol"] > 0 ) 
 { //---------файл ожидает передачи для загрузки
 $out_data['send_form'][1] = array();
 $out_data['z_kol'] = $row["kol"];
 $sql = "select bra from
 (select  p.bra as bra, 
  (select id from d3_brands b where b.brand = REPLACE(upper(p.bra),\" \",\"\")) as b_id
  from d3_pre_lookup p) l where b_id is null group by bra";
  
  try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
  $i=0;
  while ($row = $db->sql_fetchrow($res))
    {
	$i++;
	$out_data['bra'][$i]['brand'] = $row['bra'];   
    }	
  if ($i > 0) $out_data['bra_yes'][1]= array();
  
 } else
 { //---------данные переданы ожидают слияния с базой
 $out_data['data_ready'][1]= array();
 
 }

} else
{ // -------загруженого файла нет

$out_data['load_form'][1] = array();



}




//----------------------------------------------------------
$out_data['out_view'] = "html/loadcsv.htm";
$out_data['text'] = "Модуль Load CSV";
$out_data['full_text'] = "Загрузка кроссов из CSV";
$out_data['d1_act'] = "class=\"active\"";

}

}

?>