<?
if (!defined('IN_D3'))
{
	exit;
}

class mod_tobitrix
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

//---------------------Добавление айди
if ($_POST['add_id'] <> '')
{
$sql = "
insert into d3_id_tobix
select 
null, d.art_bra_md5,d.pro_id,d.price
from d3_price d where 
not exists ( select id from d3_id_tobix b where
d.art_bra_md5 = b.art_bra_md5
and d.pro_id = b.pro_id
and d.price = b.price)
group by d.art_bra_md5,d.pro_id,d.price";

$db->sql_query($sql);
$kol = $db->sql_affected_rows();

$out_data['stats'] = "Добавленно: $kol индексов";

}

//---------------------удаление айди
if ($_POST['del_id'] <> '')
{
$sql = "
update d3_id_tobix b
set b.art_bra_md5 =  unhex(md5(\"fuck the police\"))
where  not exists
(
select id from d3_price d where
d.art_bra_md5 = b.art_bra_md5
and d.pro_id = b.pro_id
and d.price = b.price
)";
$db->sql_query($sql);

$sql = "delete from d3_id_tobix where art_bra_md5 =  unhex(md5(\"fuck the police\"))";
$db->sql_query($sql);

$kol = $db->sql_affected_rows();

$out_data['stats'] = "Удалено: $kol индексов";

}


//---------------------загрузка в битрикс
if ($_POST['load'] <> '')
{
ob_start(); 

//--разделы
$sql = "delete from pod12345_sitemanager.b_iblock_section where id <> 1";
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
echo "b_iblock_section обнулена<br>";



$sql = 'select id, lower(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(brand," ",""),"-",""),".",""),"/",""),"#",""),"\'","")) as symbol, brand from d3_brands where typ=1';
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
$i=0;
while($row = $db->sql_fetchrow($res))
{
$i++;
$id = $row['id'] + 1;
$symbol =  $row['symbol']; 
$brand =  $row['brand']; 
$l = $i + 2;
$r = $i + 4;

$sql = "insert into pod12345_sitemanager.b_iblock_section value ($id,now(),1,now(),1,3,null,\"Y\",\"Y\",500,\"$brand\",null,$l,$r,1,\"\",\"text\",upper(\"$brand\"),\"$symbol\",null,null,null,null)";
try { $db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
echo "добавлен раздел $symbol <br>";

}



//--отчищаем

$sql = "delete from pod12345_sitemanager.b_catalog_product";
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
echo "b_catalog_product обнулена<br>";

$sql = "delete from pod12345_sitemanager.b_iblock_element";
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
echo "b_iblock_element обнулена<br>";

$sql = "delete from  pod12345_sitemanager.b_iblock_element_property";
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
echo "b_iblock_element_property обнулена<br>";


$sql = "delete from pod12345_sitemanager.b_iblock_section_element";
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
echo "b_iblock_section_element обнулена<br>";


$sql = "delete from pod12345_sitemanager.b_catalog_price";
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
echo "b_catalog_price обнулена<br>";


ob_flush(); 
flush(); 


$sql = "select 
b.id as id,
p.brand_id as brand_id,
(select g.brand from d3_brands g where g.id = p.brand_id and g.typ=1) as brand2,
p.art as art,
p.art_disp as art_disp,
p.price as price,
p.pro_id as pro_id,
p.info as info,
p.num as num,
hex(p.art_bra_md5) as art_bra_md5
from d3_price p,d3_id_tobix b where
p.art_bra_md5 = b.art_bra_md5
and p.pro_id = b.pro_id
and p.price = b.price
";
try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }

$str = 0;
while($row = $db->sql_fetchrow($res))
{
$str++;
$id = $row['id']; //id товара в битриксе
$price = $row['price']; //Цена
$kol = $row['num']; //количество
$w = 0; //вес
$name = $row['brand2'].' '.$row['art_disp'];
$pre_text = $row['art_disp'];
$info = $row['info'];
$xml_id = $row['art_bra_md5']; //уникальный номер
$simbol = $row['art'].$row['price'].$row['pro_id']; //символьное название идентификатор
$artikul = $row['art'];// артикул
$brand = $row['brand2'];//бренд
$katalog = $row['brand_id'] + 1;

$sql = "insert into pod12345_sitemanager.b_catalog_product  value($id,$kol,\"D\",$w,null,\"S\",0,\"D\",null,\"N\",\"Y\",0,\"N\",\"D\",\"D\",null,\"0.00\",null,\"N\",0)";
if(!mysql_query($sql)) 
{
echo "<br>Дубликат $id<br>";
} else
{
$sql = "insert into pod12345_sitemanager.b_iblock_element  value($id,now(),1,now(),1,3,$katalog,\"Y\",null,null,500,\"$name\",null,\"$pre_text\",\"text\",null,\"$info\",\"text\",upper(CONCAT(\"$name\",\"$info\")),1,null,null,null,null,null,\"Y\",\"$xml_id\",\"$simbol\",\"\",0,null,null,null)";
try { $db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }

$sql = "insert into pod12345_sitemanager.b_iblock_element_property  value(null,1,$id,\"$artikul\",\"text\",0,\"0.0000\",\"\")";
try { $db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
$sql = "insert into pod12345_sitemanager.b_iblock_element_property  value(null,2,$id,\"$brand\",\"text\",0,\"0.0000\",\"\")";
try { $db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }


$sql = "insert into pod12345_sitemanager.b_iblock_section_element  value($katalog,$id,null)";
try { $db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }

$sql = "insert into  pod12345_sitemanager.b_catalog_price value(null,$id,null,1,$price, \"RUB\", null, null, null,null )";
try { $db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
}


if ($str==300)
 {
 $str =0;
 echo "<br>";
 }

echo ".";
ob_flush(); 
flush(); 





}

echo "<br> end";
exit;
}

//---------------------страница
$sql = "
select count(*) as kol , '1' as typ from d3_price 
union 
select count(*) as kol , '2' as typ from d3_id_tobix";

try { $res=$db->sql_query($sql); } catch (Exception $e) {  $error->run($e); }
$out_data['zag'] = '';
while($row = $db->sql_fetchrow($res))
{
 if ($row['typ'] == '1' )
 {
 $out_data['zag'] .= " Позиций в прайсе: ".$row['kol']." <br>"; 
 } else
 {
 $out_data['zag'] .= " Число айдишников: ".$row['kol']." <br>"; 
 }
}








$out_data['out_view'] = "html/tobitrix.htm";
$out_data['text'] = "Модуль tobitrix";
$out_data['full_text'] = "Загрузка прайса в битрикс.";
$out_data['b1_act'] = "class=\"active\"";




}
}

?>