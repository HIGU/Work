<?php
//////////////////////////////////////////////////////////////////////////
// �����׻��ǻ��Ѥ��륵���ӥ����� Branch (ʬ��)����                   //
// 2003/10/20 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                             //
// 2003/10/20 ��������  service_branch.php                              //
// 2003/10/24 service_category_select.php?exec=entry OR view(�Ȳ�)���ɲ�//
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../../function.php");
require_once ("../../tnk_func.php");
access_log();                       // Script Name �ϼ�ư����

////////////// ǧ�ڥ����å�
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit();
}

$_SESSION['service_ym']      = $_POST['service_ym'];            // �о�ǯ��򥻥å�������¸
//////// service_category_select���оݤ��鳰�� service_percentage_menu�ȶ��Ѥ����뤿��
if ( !preg_match('/service_category_select.php/', $_SERVER['HTTP_REFERER']) ) {
    $_SESSION['service_referer'] = $_SERVER['HTTP_REFERER'];        // �ƽФ�Ȥ�URL�򥻥å�������¸
}
switch ($_POST['service_name']) {
    case '�����ӥ��������' : $script_name = 'kessan/service/service_category_select.php?exec=entry'; break;
    case '�����ӥ����Ȳ�' : $script_name = 'kessan/service/service_category_select.php?exec=view' ; break;
    case '��� ���� �Ȳ�'   : $script_name = 'kessan/service/service_percent_view_total.php'        ; break;
    case '��¤���������'   : $script_name = 'kessan/service/service_percent_act_allo.php'          ; break;
    case '�ޥ������Խ�'     : $script_name = 'kessan/service/service_item_master_mnt.php'           ; break;
    case 'ͽ¬����Ψ������' : $script_name = 'kessan/service/service_percent_act_allo_plan.php'     ; break;
    case '��������'     : $script_name = 'kessan/service/service_final_set.php?set'             ; break;
    case '�������'     : $script_name = 'kessan/service/service_final_set.php?unset'           ; break;
    case '��¤�������겾��' : $script_name = 'kessan/service/service_percent_act_allo_kari.php'     ; break;
    
    default: $script_name = 'kessan/service/service_percentage_menu.php';          // �ƽФ�Ȥص���
             $url_name    = $_SESSION['service_referer'];        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>�����ӥ���� ʬ������</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>������Ǥ������Ԥ���������</center>

    <script language="JavaScript">
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            echo "location = 'http:" . WEB_HOST . "$script_name'";
        }
    ?>
    // -->
    </script>
</BODY>
</HTML>
