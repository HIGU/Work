<?php
//////////////////////////////////////////////////////////////////////////////
//  ����(daily weekly)���衡��  �ƥ�����                                    //
//  2002/02/22   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp             //
//  �ѹ�����                                                                //
//  2002/02/22                                                              //
//////////////////////////////////////////////////////////////////////////////
ob_start();  //Warning: Cannot add header ���к��Τ����ɲá�
	session_start();
	session_register("s_sysmsg");
	require ("system_function.php");
	require ("../tnk_func.php");
	if($Auth<=2){
		$s_sysmsg = "�����ƥ������˥塼�ϴ����ԤΤ߻��ѤǤ��ޤ���";
		$sysmsg = urlencode("�����ƥ������˥塼�ϴ����ԤΤ߻��ѤǤ��ޤ���");
		header("Location: http:" . WEB_HOST . "index1.php?sysmsg=" . $sysmsg);
		exit();
	}

$f01 = array();$f02 = array();$f03 = array();$f04 = array();$f05 = array();$f06 = array();$f07 = array();
$f08 = array();$f09 = array();$f10 = array();$f11 = array();$f12 = array();$f13 = array();$f14 = array();
$f15 = array();$f16 = array();$f17 = array();$f18 = array();$f19 = array();

$rec = hiuuri_get_field($f01,$f02,$f03,$f04,$f05,$f06,$f07,$f08,$f09,$f10,$f11,$f12,$f13,$f14,$f15,$f16,$f17,$f18,$f19);
echo "Record Count : " . $rec . "<br>\n";
for($i=0;$i<$rec;$i++){
	echo ($i+1) . " " . $f01[$i] . " " . $f02[$i] . " " . $f03[$i] . " " . $f04[$i] . " " . $f05[$i] . " " . $f06[$i] 
		. " " . $f07[$i] . " " . $f08[$i] . " " . $f09[$i] . " " . $f10[$i] . " " . $f11[$i] . " " . $f12[$i] . " " 
		. $f13[$i] . " " . $f14[$i] . " " . $f15[$i] . " " . $f16[$i] . " " . $f17[$i] . " " . $f18[$i] . " " . $f19[$i] . "<br>\n";
}

ob_end_flush();  //Warning: Cannot add header ���к��Τ����ɲá�

?>

