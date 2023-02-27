<?php
//////////////////////////////////////////////////////////////////////////////
// �� �� �� �� (����ۤη�ץǡ�������)                                   //
// Copyright (C) 2002-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/02/07 Created  system_getuji.php                                    //
// 2002/02/22 php�κǸ�Υ�����ȴ���Ƥ����Զ�����                       //
// 2002/08/08 ���å����������ѹ�                                          //
// 2002/12/03 �����ȥ�˥塼���ɲ�                                          //
// 2003/06/12 �����ײ�(assembly_schedule)����������̤���褦���ѹ�       //
//            DB¦�� Uround() ����ѻͼθ�����ǽ���ɲä���פ򻻽�          //
// 2004/06/07 ���ץ�ɸ��ζ�ۻ�����ˡ���ѹ� �������Τ��饫�ץ�����򸺻�   //
//            (�����������ײ�ޥ����������ʬ=1��ɸ���ʤ���Ф��Ƥ���)      //
// 2005/03/04 2��ʬ��L���ʤ�T����夷������(������='L' or ������='T')���ɲ� //
// 2007/05/01 $menu->out_alert_java(false)���б����뤿��s_sysmsg='\n'���ѹ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');
// require_once ('../define.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name �ϼ�ư����

$sysmsg = $_SESSION['s_sysmsg'];

$_SESSION['s_sysmsg'] = NULL;
$_SESSION['system_getuji'] = date('H:i');
if($_SESSION['Auth'] <= 2){
    $_SESSION['s_sysmsg'] = '�����ƥ������˥塼�ϴ����ԤΤ߻��ѤǤ��ޤ���';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}
if(!$_POST['yyyymm']){
    $_SESSION['s_sysmsg'] = '�ǯ����Ϥ���Ƥ��ޤ���';
    header('Location: http:' . WEB_HOST . 'system/system_getuji_select.php');
    exit();
}
$yyyymm = $_POST['yyyymm'];     

$s_date = $yyyymm . '01';
$e_date = $yyyymm . '31';

// C ������
$query = "select sum(Uround(����*ñ��,0)) as ��׶�� from hiuuri left outer join assembly_schedule on �ײ��ֹ�=plan_no where �׾���>=$s_date and �׾���<=$e_date and ������='C' and note15 like 'SC%'";
if (getUniResult($query, $sp_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "ǯ��:$yyyymm ���ץ�����Υǡ���������ޤ���!";
    exit();
} else {
    $_SESSION['s_sysmsg'] = "<font color='white'>ǯ�$yyyymm<br>C�����ۡ���" . number_format($sp_kin) . '<br>\n';
}
/******************** ��SQLʸ
$query = "select ����,ñ�� from hiuuri h, mipmst m where h.assyno=m.seihin and h.�׾���>=$s_date and h.�׾���<=$e_date and h.������='C' and m.kubun='3' order by h.�׾��� asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // �����ι�׶�ۤ򻻽�
        $sp_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// C ɸ����
$query = "select sum(Uround(����*ñ��,0)) as ��׶�� from hiuuri where �׾���>=$s_date and �׾���<=$e_date and ������='C' and datatype='1'";   // ���� ���Τ�
// $query = "select sum(Uround(����*ñ��,0)) as ��׶�� from hiuuri left outer join assembly_schedule on �ײ��ֹ�=plan_no where �׾���>=$s_date and �׾���<=$e_date and ������='C' and sei_kubun='1'";
if (getUniResult($query, $c_sei_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "ǯ��:$yyyymm ���ץ�ɸ��Υǡ���������ޤ���!";
    exit();
} else {
    $std_kin = ($c_sei_kin - $sp_kin);      // �����ɸ��ᥫ�ץ��������Ρݥ��ץ���������
    $_SESSION['s_sysmsg'] .= "<font color='white'>ǯ�$yyyymm<br>Cɸ���ۡ���" . number_format($std_kin) . '</font><br>\n';
}
/******************** ��SQLʸ
$query = "select ����,ñ�� from hiuuri h, mipmst m where h.assyno=m.seihin and h.�׾���>=$s_date and h.�׾���<=$e_date and h.������='C' and m.kubun='1' order by h.�׾��� asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // �����ι�׶�ۤ򻻽�
        $std_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// ���ץ�ι�׶�ۤ�׻�
$query = "select sum(Uround(����*ñ��,0)) from hiuuri where �׾���>=$s_date and �׾���<=$e_date and ������='C'";
if (getUniResult($query, $c_all_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "ǯ��:$yyyymm ���ץ��פΥǡ���������ޤ���!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>ǯ�$yyyymm<br>C��׶�ۡ���" . number_format($c_all_kin) . '</font><br>\n';
}
/******************** ��SQLʸ
$query = "select ����,ñ�� from hiuuri where �׾���>=$s_date and �׾���<=$e_date and ������='C' order by �׾��� asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // �����ι�׶�ۤ򻻽�
        $c_all_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// ��˥��ι�׶�ۤ�׻�
$query = "select sum(Uround(����*ñ��,0)) from hiuuri where �׾���>=$s_date and �׾���<=$e_date and (������='L' or ������='T')";
if (getUniResult($query, $l_all_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "ǯ��:$yyyymm ��˥���פΥǡ���������ޤ���!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>ǯ�$yyyymm<br>L��׶�ۡ���" . number_format($l_all_kin) . '</font><br>\n';
}
/******************** ��SQLʸ
$query = "select ����,ñ�� from hiuuri where �׾���>=$s_date and �׾���<=$e_date and ������='L' order by �׾��� asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // �����ι�׶�ۤ򻻽�
        $l_all_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// ���ץ�����ʶ�ۤ�׻�
$query = "select sum(Uround(����*ñ��,0)) from hiuuri where �׾���>=$s_date and �׾���<=$e_date and ������='C' and datatype='1'";
if (getUniResult($query, $c_sei_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "ǯ��:$yyyymm ���ץ����ʤΥǡ���������ޤ���!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>ǯ�$yyyymm<br>C���ʶ�ۡ���" . number_format($c_sei_kin) . '</font><br>\n';
}
/******************** ��SQLʸ
$query = "select ����,ñ�� from hiuuri where �׾���>=$s_date and �׾���<=$e_date and ������='C' and datatype='1' order by �׾��� asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // �����ι�׶�ۤ򻻽�
        $c_sei_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// ��˥������ʶ�ۤ�׻�
$query = "select sum(Uround(����*ñ��,0)) from hiuuri where �׾���>=$s_date and �׾���<=$e_date and (������='L' or ������='T') and datatype='1'";
if (getUniResult($query, $l_sei_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "ǯ��:$yyyymm ��˥����ʤΥǡ���������ޤ���!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>ǯ�$yyyymm<br>L���ʶ�ۡ���" . number_format($l_sei_kin) . '</font><br>\n';
}
/******************** ��SQLʸ
$query = "select ����,ñ�� from hiuuri where �׾���>=$s_date and �׾���<=$e_date and ������='L' and datatype='1' order by �׾��� asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // �����ι�׶�ۤ򻻽�
        $l_sei_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/


$all_kin = $c_all_kin + $l_all_kin;
$query = "select ǯ�� from wrk_uriage where ǯ��='$yyyymm'";
$res = array();
if($rows = getResult($query,$res)){
    $update_qry  = "update wrk_uriage set c����=$sp_kin, cɸ��=$std_kin, ���ץ�=$c_all_kin, ��˥�=$l_all_kin, ����=$all_kin, c����=$c_sei_kin, l����=$l_sei_kin where ǯ��=$yyyymm";
    if(funcConnect()){
        execQuery('begin');
        if(execQuery($update_qry)>=0){
            execQuery('commit');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= "<font color='white'>������� UPDATE ���������ޤ�����</font>";
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }else{
            execQuery('rollback');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= '�������UPDATE�˼��Ԥ��ޤ������ǡ����١������å���Ĵ�٤Ʋ�������';
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }
    }
}else{
    $insert_qry = "insert into wrk_uriage (ǯ��,c����,cɸ��,���ץ�,��˥�,����,c����,l����) values ($yyyymm,$sp_kin,$std_kin,$c_all_kin,$l_all_kin,$all_kin,$c_sei_kin,$l_sei_kin)";
    if(funcConnect()){
        execQuery('begin');
        if(execQuery($insert_qry)>=0){
            execQuery('commit');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= "<font color='white'>ǯ��:$yyyymm �ο����ɲ�����</font>";
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }else{
            execQuery('rollback');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= '�������INSERT�˼��Ԥ��ޤ������ǡ����١������å���Ĵ�٤Ʋ�������';
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }
    }
}
?>
