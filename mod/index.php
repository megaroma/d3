<?php
if (!defined('IN_D3'))
{
	exit;
}

class mod_index
{
public $route;

public function __construct($r)
 {
 $this->route = $r;
 }


public function run($core,&$out_data)
{






$out_data['out_view'] = "html/index.htm";
$out_data['text'] = "������ index";
$out_data['full_text'] = "������� ��������";
$out_data['l1_act'] = "class=\"active\"";


} 


}



?>