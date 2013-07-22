<?php
if (!defined('IN_D3'))
{
	exit;
}


class mod_search
{
public $route;

public function __construct($r)
 {
 $this->route = $r;
 }


public function run($core,&$out_data)
{
//----------------------init-----------------------------------
$db = $core->get("db");
$error = $core->get("error");

$rr = explode("/", $this->route,4);

//-------------------search----------------------------------------------------
if ($rr[0] == 'go')
 {
 if ($_POST["art"] <> '')
  {
  $val = new class_valid();
  $bra = $val->get('bra','for_sql','POST');
  $art = $val->get('art','for_sql','POST'); 

  
  $out_data['b_bra'] = $bra;
  $out_data['b_art'] = $art;
  
  if ($bra) 
  {
  $sql = "select id from d3_brands where brand = REPLACE(upper(\"$bra\"),\" \",\"\")";
  try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
  $row = $db->sql_fetchrow($res);
  }
  if ($row["id"] <> '')
   { //-----------------------бренд найден------------------------------------
   $bra_id = $row["id"];
   $sql = "select 
   (select b.brand from d3_brands b where b.id = p.brand_id and b.typ = 1) as br_name,
    p.art as art,
    p.price as charge,
    p.info as term,
    (select t.name from d3_providers t where t.id = p.pro_id) as post,
    p.num as kol
    from  d3_price p where 
	ART_BRA_MD5 = UNHEX(MD5(upper(REPLACE(CONCAT(\"$bra_id\",REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(\"$art\",\" \",\"\"),\"-\",\"\"),\".\",\"\"),\"/\",\"\"),\"#\",\"\")),\" \",\"\"))))  
    and p.brand_id = $bra_id";
    try { $res=$db->sql_query($sql); } catch (Exception $e) {  echo $sql;
	                                                         $error->run($e); }
	$i = 0;
    while($row = $db->sql_fetchrow($res))
    {
    $i++;
    $out_data['price'][$i]['br_name'] = $row["br_name"];
    $out_data['price'][$i]['art'] = $row["art"];
    $out_data['price'][$i]['charge'] = $row["charge"];
    $out_data['price'][$i]['term'] = $row["term"];
    $out_data['price'][$i]['post'] = $row["post"];
    $out_data['price'][$i]['kol'] = $row["kol"];
    }
    if ($i > 0) {$out_data['price_yes'][1] = array(); }
	
   //------------поиск кроссов-----------------------------------------
    $sql = " 
     select 
     GROUP_CONCAT( CONCAT(ART_ID) SEPARATOR \",\") as a_id
     from 
     (select 
      ART_ID as ART_ID 
      from d3_lookup where 
      ART_BRA_MD5 = UNHEX(MD5(upper(REPLACE(CONCAT(\"$bra_id\",REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(\"$art\",\" \",\"\"),\"-\",\"\"),\".\",\"\"),\"/\",\"\"),\"#\",\"\")),\" \",\"\"))))  
      and brand_id = $bra_id
      group by ART_ID) a
      ";
    try { $res=$db->sql_query($sql); } catch (Exception $e) { echo $sql;
	$error->run($e); }
	$row = $db->sql_fetchrow($res);
	$art_id = $row["a_id"];
	if   ($art_id <> "")
     { //кроссы найдены
	 $sql = "select
     GROUP_CONCAT( CONCAT(j.gr_id) SEPARATOR \",\") as gr_id
     from
     (select g.gr_id as gr_id  from d3_sgroups g where g.art_id in ( $art_id) and g.td = STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s')  group by g.gr_id) j
      ";
     try { $res=$db->sql_query($sql); } catch (Exception $e) { echo $sql;
	 $error->run($e); }
     $row = $db->sql_fetchrow($res);
     $gr_id = $row["gr_id"];   
/*	 
     $sql = " select
     GROUP_CONCAT( CONCAT(j.art_id) SEPARATOR \",\") as art_id
     from
     (  
      select 
      art_id
      from d3_sgroups d 
      where
      d.gr_id in ($gr_id)
      and d.td = STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
      group by gr_id,art_id) j";
      try { $res=$db->sql_query($sql); } catch (Exception $e) {echo $sql;
	  $error->run($e); }
      $row = $db->sql_fetchrow($res);
      $full_art_id = $row["art_id"];
	  $sql = 
      "select 
      (select b.brand from d3_brands b where b.id = p.brand_id and b.typ = 1) as br_name,
       p.art as art,
       p.price as charge,
       p.info as term,
       (select t.name from d3_providers t where t.id = p.pro_id) as post,
       p.num as kol
       from  d3_price p, 
       (select 
        ART_BRA_MD5 
        from d3_lookup where 
        ART_ID in ($full_art_id) and
        ART_BRA_MD5 <> UNHEX(MD5(upper(REPLACE(CONCAT(\"$bra_id\",REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(\"$art\",\" \",\"\"),\"-\",\"\"),\".\",\"\"),\"/\",\"\"),\"#\",\"\")),\" \",\"\"))))  
       group by ART_BRA_MD5) a
       where p.ART_BRA_MD5 =  a.ART_BRA_MD5 
       order by br_name
       "; 
*/	   
	   //-----------------------------
	   	  $sql = 
      "select 
    (select b.brand from d3_brands b where b.id = p.brand_id and b.typ = 1) as br_name,
     p.art as art,
     p.price as charge,
     p.info as term,
     (select t.name from d3_providers t where t.id = p.pro_id) as post,
     p.num as kol
     from  d3_price p, 
       (select 
        l.ART_BRA_MD5 
        from d3_lookup l,d3_sgroups d  where 
        l.ART_ID = d.art_id 
		and d.gr_id in ($gr_id)
        and d.td = STR_TO_DATE('2050-01-01 00:00:00', '%Y-%m-%d %H:%i:%s') 
  	    and l.ART_BRA_MD5 <> UNHEX(MD5(upper(REPLACE(CONCAT(\"$bra_id\",REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(\"$art\",\" \",\"\"),\"-\",\"\"),\".\",\"\"),\"/\",\"\"),\"#\",\"\")),\" \",\"\"))))  
       group by l.ART_BRA_MD5) a
       where p.ART_BRA_MD5 =  a.ART_BRA_MD5 
       order by br_name
       "; 
	   
	   //-----------------------------
	   try { $res=$db->sql_query($sql); } catch (Exception $e) { echo $sql;
	   $error->run($e); }
	   $i=0;
	      while($row = $db->sql_fetchrow($res))
           {
           $i++;
           $out_data['cross'][$i]['br_name'] = $row["br_name"];
           $out_data['cross'][$i]['art'] = $row["art"];
           $out_data['cross'][$i]['charge'] = $row["charge"];
           $out_data['cross'][$i]['term'] = $row["term"];
           $out_data['cross'][$i]['post'] = $row["post"];
           $out_data['cross'][$i]['kol'] = $row["kol"];
           }
        if ($i > 0) {$out_data['cross_yes'][1] = array(); }
	 
	 } else
	 { //кроссы не найдены
	 $out_data['cross_help'] = "Ќе найдено";
	 }

   
   } else
   { //-------------------------бренд не найден---------------------------------
   if ($bra)
    {
    $out_data['help'] =  $bra." - такой бренд не найден в системе, поиск по артикулу:";
	}else
	{
	$out_data['help'] =  "Ќе введен бренд, поиск по артикулу:";
	}
   //поиск по артикулу	
   $sql = "select 
   (select b.brand from d3_brands b where b.id = p.brand_id and b.typ = 1) as br_name,
    p.art as art,
    p.price as charge,
    p.info as term,
    (select t.name from d3_providers t where t.id = p.pro_id) as post,
    p.num as kol
    from  d3_price p where 
	upper(p.art) = upper(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(\"$art\",\" \",\"\"),\"-\",\"\"),\".\",\"\"),\"/\",\"\"),\"#\",\"\"))
    ";
    try { $res=$db->sql_query($sql); } catch (Exception $e) { $error->run($e); }
	$i = 0;
    while($row = $db->sql_fetchrow($res))
    {
    $i++;
    $out_data['price'][$i]['br_name'] = $row["br_name"];
    $out_data['price'][$i]['art'] = $row["art"];
    $out_data['price'][$i]['charge'] = $row["charge"];
    $out_data['price'][$i]['term'] = $row["term"];
    $out_data['price'][$i]['post'] = $row["post"];
    $out_data['price'][$i]['kol'] = $row["kol"];
    }
    if ($i > 0) {
	             $out_data['price_yes'][1] = array(); 
				 $out_data['cross_help'] = "„тобы начать поиск кроссов, выбирете бренд и начните новый поиск с брендом.";
				 }

	
	
	
   }

  
  }
 }






//---------------show----------------------------------------

$out_data['out_view'] = "html/search.htm";
$out_data['text'] = "ћодуль search";
$out_data['full_text'] = "ѕоиск по бренду и артикулу.";
$out_data['l3_act'] = "class=\"active\"";
} 








}






?>