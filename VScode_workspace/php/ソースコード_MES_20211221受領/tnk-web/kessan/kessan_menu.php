<?php
//////////////////////////////////////////////////////////////////////////
// �����֡��軻���� ��˥塼                                        //
// 2002/03/22 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                             //
// 2002/08/09 register_globals = Off �б�                               //
// 2002/08/27 �ե졼�� �б�                                             //
// 2002/09/20 �����ȥ�˥塼�˲��̥�˥塼���ɲ�                        //
// 2003/01/15 ưŪ��˥塼����������ѹ�  menu_bar()                    //
// 2003/02/14 ���ط��˥塼 �Υե���Ȥ� style �ǻ�����ѹ�            //
//                              �֥饦�����ˤ���ѹ�������ʤ��ͤˤ���  //
// 2003/10/17 �����ӥ���������˥塼���ɲ�                            //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
// require_once ("../define.php");    // function.php �� require ����Ƥ���
require_once ("../tnk_func.php");   // menu_bar() �ǻ���
access_log();                       // Script Name �ϼ�ư����
// $sysmsg = $_SESSION["s_sysmsg"];
// $_SESSION["s_sysmsg"] = NULL;
$_SESSION["site_index"] = 10;       // �Ȥꤢ�����ϣ������ܤΥ�˥塼�ˤ��Ƥ���
$_SESSION["site_id"] = 999;     // �Ȥꤢ�������̥�˥塼̵�� (0 < �Ǥ���) 999 �ϲ��̥�˥塼����ɽ��
// $_SESSION["dev_req_menu"] = date("H:i");

/////////// ǧ�ڥ����å�
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ�����Login ��ľ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}
unset($_SESSION['act_offset']);     // ���祳���ɥơ��֥�ǻ��Ѥ���offset�ͤ���
unset($_SESSION['cd_offset']);      // �����ɥơ��֥�ǻ��Ѥ���offset�ͤ���

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=euc-jp">
<META http-equiv="Content-Style-Type" content="text/css">
<title>�����֡��軻���� ��˥塼</title>

<style type="text/css">
<!--
.top-font {
    font-size: 12pt;
    font-family: monospace;
    font-weight: bold;
}
select {
    background-color: teal;
    color: white;
}
textarea {
    background-color: black;
    color: white;
}
input.sousin {
    background-color: red;
}
input.text {
    background-color: black;
    color: white;
}
.pt11 {
    font-size: 11pt;
}
-->
</style>

<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>

</head>

<body bgcolor="#ffffff" text="#000000">
<table width="100%" height="100%"><tr>

    <!-- right view -->

    <td valign="top">
        <script language="Javascript">
        <!--
            str=navigator.appName.toUpperCase();
            if(str.indexOf("NETSCAPE")>=0) document.write("<table width='100%' height=585 bgcolor='#ffffff' cellpadding=10>");
            if(str.indexOf("EXPLORER")>=0) document.write("<table width='100%' height='100%' bgcolor='#ffffff' cellpadding=10>");
        //-->
        </script>
        <noscript><table width="100%" height="100%" bgcolor="#ffffff" cellpadding=10></noscript>
        <tr><td valign="top">
            <table width="100%">
                <tr><td><p><img src="../img/t_nitto_logo3.gif" width=348 height=83></p></td></tr>
                <tr><td align="center" class='top-font'>�����֡��軻���� ��˥塼</td></tr>
            </table>

            <table width="100%">
                <tr><td align="center">
                <img src='../img/tnk-turbine.gif'>
                </td></tr>
            </table>

            <table width="100%">
                <tr>
                    <td align="center">
                        <form method="post" action="act_table_mnt.php">
                            <input type='image' alt='�����祳����ɽ���ƥʥ�' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_act_table_mnt.png","�����祳����ɽ �ݼ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="act_table_mnt_new.php">
                            <input type='image' alt='�����祳����ɽ���ƥʥ�' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_act_table_mnt_new.png","�����祳����ɽ �ݼ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="category_mnt.php">
                            <input type='image' alt='��ʬ������Ψ���ݼ�(»�״ط�)' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_category_mnt.png","��ʬ������Ψ �ݼ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="allocation_mnt.php">
                            <input type='image' alt='��ʬ������Ψ���ݼ�(»�פȸ����ط�)' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_allocation_mnt.png","��ʬ������Ψ �ݼ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="cd_table_mnt.php">
                            <input type='image' alt='�������ȿ����ͻ������ɥơ��֥���ݼ�' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_cd_table_mnt.png","�����ɥơ��֥� �ݼ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="machine_labor_rate_mnt.php">
                            <input type='image' alt='��¤�ε�����Ψ�׻�ɽ�κ������Ȳ�' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_machine_labor_rate.png","������Ψ �������Ȳ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="service/service_percentage_menu.php">
                            <input type='image' alt='ľ������ؤΥ����ӥ���������' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_servis.png","�����ӥ���������",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="wage_rate.php">
                            <input type='image' alt='��Ω��Ψ�׻�ɽ�κ������Ȳ�' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_wage_rate.png","��Ω��Ψ�κ������Ȳ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="kessan_menu.php">
                            <input type='image' alt='��ȱ�����������' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_aid.png","��ȱ�����������",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="profit_loss_select.php">
                            <input type='image' alt='�»�״ط� �������Ȳ�' border=0 src='<?php echo menu_bar("menu_tmp/menu_item_profit_loss_select.png","�»�� �������Ȳ�",14)."?".uniqid("menu") ?>'>
                        </form>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <form method="post" action="kessan_menu.php">
                            <input type='image' alt='���Υ����ƥ�' border=0 src='../img/menu_item.gif'>
                        </form>
                    </td>
                    <td align="center">
                        <form method="post" action="kessan_menu.php">
                            <input type='image' alt='���Υ����ƥ�' border=0 src='../img/menu_item.gif'>
                        </form>
                    </td>
                </tr>
            </table>

        </td></tr>
        <!--
        <tr><td valign="bottom">
            <img src="../img/php4.gif" width=64 height=32>
            <img src="../img/linux.gif" width=74 height=32>  
            <img src="../img/redhat.gif" width=96 height=32>   
            <img src="../img/apache.gif" width=259 height=32> 
            <img src="../img/pgsql.gif" width=160 height=32>
        </td></tr>
        -->
    </td>
</tr></table>
</body>
</html>
