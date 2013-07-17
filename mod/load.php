<?php
if (!defined('IN_D3'))
{
	exit;
}

class mod_load
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

if ($rr[0] == 'go')
{//------������ �� ����������  �������

if ($_POST["del"] <> '')
 {//---��������
       $sql = "select seq_art_id from d3_load_log where stat = 1";
	   try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $row = $db->sql_fetchrow($res);
       if ($row['seq_art_id'] <> '') 
	    {   
		$old_art_id = $row['seq_art_id'];
		$sql = "update `d3_seq_art_id` set id = $old_art_id";
		try {$row = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
        $sql = "ALTER TABLE `d3_seq_art_id` auto_increment = $old_art_id";
        try {$row = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
		}
 
	   $sql = "delete from d3_pre_lookup";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $sql = "delete from d3_add_lookup";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $sql = "delete from d3_load_log where stat = 1";
	   try { $db->sql_query($sql); } catch (Exception $e) { $error->run($e); } 
 $out_data['stats'] = "������ �������.";	   
 }

 
if ($_POST["go"] <> '')
 {//----���������� ������ 
 $sql = "select id, fname, dt from d3_load_log where stat = 1 ";
 try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 $row = $db->sql_fetchrow($res);
 $file = fopen ("logs/log_".$row['id'].".txt","w+");
 $str = "��� ���� �������� ".$row['fname']." �� ".$row['dt']."\r\n";
 fwrite($file, $str);
 $sql = "select 
           art_id, count(*) as kol
         from d3_add_lookup
         group by art_id";
 try {$res2 = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
 while ($row2 = $db->sql_fetchrow($res2))
  { //--������� art_id 
   $cur_gr = $row2["art_id"];
   $gr_id = 0;//��� �� ������� �� � ���� ����� ������
   $str = "�������� � ������� ".$cur_gr.": \r\n";
   fwrite($file, $str);
   
   $sql = "select g.gr_id  as gr_id
                  from d3_sgroups g, d3_lookup l, d3_add_lookup a
                  where
           g.art_id = l.art_id
           and g.td = STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
           and l.art_bra_md5 = a.art_bra_md5
           and a.art_id = $cur_gr
           group by g.gr_id"; 
    try {$res = $db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
    while ($row = $db->sql_fetchrow($res))
     {//���� ����������
      if ($gr_id == 0)
	   {//���� �� �������� � ����� ������ ��������� � ��������
	   $g = $row["gr_id"];
	   $sql = "insert into d3_sgroups
       select
       	null,$g,$cur_gr,now(),STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s')  from dual";
	   try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $gr_id = $g;
	   $str = "�������� � ����� ������:".$g.";";
       fwrite($file, $str);
	 } else
	 {//���� ��� ��� �������� � ����� ������ ������ ��� ��������� � ������ �� ����� 
	   $g = $row["gr_id"];
	   $sql = "insert into d3_sgroups
	         select 
             null,$g ,art_id,now(),STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
             from d3_sgroups where 
             td = STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
             and gr_id = $gr_id";
	   try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	     
       $sql = "update d3_sgroups set td = now()
             where td = STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
             and gr_id = $gr_id";
	   try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	   $str = "�������� �� $gr_id � ����� ������:".$g.";";
       fwrite($file, $str);
       $gr_id = $g;
	 }
   
    $str = "\r\n";
    fwrite($file, $str);
    }	 
   
   //���� �� ������� ������������ ������� ����� ������
   if ($gr_id == 0) 
   {
	 $sql = "insert into d3_sgroups
           select
	       null,get_next_d3_gr_id(),$cur_gr,now(),STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s')  from dual";
	 try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
     $str = "�������� � ����� ����� ������.";
     fwrite($file, $str);
    }
   
  
  //----
  $str = "\r\n";
  fwrite($file, $str);
  } // ---end ������� art_id

 //����� �� ������������� ������ 

  $sql = "
         insert into d3_lookup
         select
         null,
         brand_id,
         art,
         art_disp,
         art_id,
		 kind,
		 (select id from d3_load_log where stat = 1),
         art_bra_md5
         from d3_add_lookup
         ";
   try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
   
   $sql = "delete from d3_add_lookup";
   try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
   $sql = "update d3_load_log set stat = 2 where stat = 1"; 
   try {$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }

   $out_data['stats'] = "������ �������� � �������� �������, ��������� ������ ��������";



  $str = "\r\n The end";
  fwrite($file, $str);
  fclose ($file); 
 }//----END ���������� ������ 

}



//----------------------------------------------------------

$sql = "
select 
count(*) as kk,
sum(a.kol) as ks
from
(select 
art_id,
count(*) as kol
from d3_add_lookup
group by art_id) a
";

try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
$row = $db->sql_fetchrow($res);

if ($row["kk"] > 0 ) 
{
$out_data['info'] = "������� ��� ����������:<br>�����:".$row["kk"].", ���������:".$row["ks"];
$out_data['contr'][1] = array();

} else 
{
$out_data['info'] = "��� ������ ��� ��������.";
}



  

$out_data['out_view'] = "html/load.htm";
$out_data['text'] = "������ load";
$out_data['full_text'] = "�������� ������� � ����.";
$out_data['d2_act'] = "class=\"active\"";
}
}
?>