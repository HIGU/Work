<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ������ ��˥塼 (�ե졼��κ�¦��ɽ��������)                //
// Copyright (C) 2002-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/08/01 Created  menu_site.php                                        //
// 2002/08/26 ���å����������ɲ� & register_globals = Off �б�            //
// 2002/09/21 ��˥塼��������� 32X32 �� 22X22 ���ѹ� ʸ��������11pt��     //
//              ʸ������û�� ������Ư������Ĺ������Ŭ 800X600�β��̤�       //
// 2002/09/24 �ͥ����к��Τ���<body> �� link='white' vling='white' �ɲ�     //
// 2002/12/27 �֥饦�����Υ���å����к��Τ��� uniqid() ���ɲ�              //
//            <a href='/system/phpinfo.php?". uniqid("menu") ."'            //
// 2003/01/23 <a href='*****.php?". uniqid("menu") ."'  ��  ?$uniq ��       //
// 2003/02/27 ʸ����������֥饦�������ѹ��Ǥ��ʤ�����[�����ƥ��å�����]  //
// 2003/05/13 ������Ư������ �ù� ���� �Ȳ� ���ɲ�                          //
// 2003/05/15 ��ʬ������Ψ�ݼ����ʬ�� �����ݼ���ѹ� �����祳�����ݼ���  //
// 2003/06/30 ��ȯ�ѥƥ�ץ졼�ȥե������ɽ�����˥塼���ɲ�              //
// 2003/08/25 style sheet �� a:hover { background-color:blue; } �ɲ�        //
// 2003/10/17 �����֡��軻����˥����ӥ���������˥塼���ɲ�          //
// 2003/10/31 ����˥塼�ˣ�����ɸ�॰��դ��ɲ� site_index=1 site_id=9   //
// 2003/11/15 font style 11pt �� 9pt �� <a> ����ʬ�򾮤���������            //
// 2003/11/18 font style sysmsg_body �� 8.7pt �� 7.2pt �� ������������      //
// 2003/11/29 <img width='22' height='22'>�Υ�����������'16'���ѹ��ȹ�®��//
// 2003/12/01 site_index�����פ�������<td bgcolor='blue'>img</td>���ɲ�   //
// 2003/12/02 site_icon1_on.gif?1��site_icon1_on.gif?v=2 ���ѹ�(�ǿ��ɹ�)   //
//            (off.gif) site_icon1_on.gif?$uniq �ˤ���� IE6���Զ�礬�Ǥ���//
// 2003/12/11 �����ƥ��å�������font�� 8.7pt �� 7.9pt ���ѹ�              //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�   ��λ(��������)��(logout)  //
// 2004/02/13 ?$uniq �� ?id=$uniq ���ѹ� �Ұ���˥塼�˼Ұ�̾��������ɲ�   //
//            ". TOP_MENU ." ��Ϣ��� ", TOP_MENU, "�� echo��()�򳰤�ɬ��ͭ //
// 2004/04/28 ?id=$uniq �� ?$uniq ($uniq='id=menu42498dds')�ѿ���������� //
//            ����Ѥߤ�SID����Ѥ��ƥ��å������Ĥξ��Ƚ�ǥ��å����ɲ�   //
// 2004/07/09 ���������˥塼�򸢸¥�٥�Ǳ��� ��������ž������ɲ�       //
// 2005/01/13 MenuHeader�Ƕ��̥��������Ƥ�ȼ��parent.application.focus()�ɲ�//
// 2005/01/14 �嵭���� ������ϰ��֤�set_focus()���Ƥ�������Զ�礬�Ф�//
// 2005/01/18 ��ƥ�뤫�� SITE_ICON_ON  SITE_ICON_OFF ��define���������   //
// 2005/02/25 CSS�ط�a.current���ɲä�<a>�򵼻����饹������active red->gold //
// 2005/09/02 target='application��<a href='logout.php' target='_parent'>�� //
// 2005/09/13 ������˥塼���ɲ�  ��  session_register('s_sysmsg')��ű��    //
// 2006/06/23 ��Ҥδ��ܥ����������ƥʥ󥹤򥷥��ƥ��˥塼���ɲ�      //
// 2006/07/15 �嵭�Υ���������TOP���ѹ���󥯤θ��Ĥ������̸����Խ����ɲ� //
// 2006/08/30 �Ǽ��� �������ȥåץ�˥塼���ɲ� $uniq��ID���ղä��Ƥ�NG   //
// 2006/09/29 ���Ȳ��˥塼�� sales/ �� sales/details/ �ذ�ư            //
// 2007/02/20 parts_stock_plan_Main.php �� parts_stock_plan_form.php ���ѹ� //
// 2007/03/07 ��ư������ư�����������ë ���ʺ߸˷���ǥ��쥯�ȥ��ѹ� ����//
// 2007/03/24 sales_miken allo_conf_parts �Υǥ��쥯�ȥ�(�ץ����)�ѹ�    //
// 2007/03/27 ������˥塼��INDEX_EQUIP ���ѹ�  ��������˥塼�����Ȥ���//
// 2007/03/29 ������⡼�ɤǤϵ�����ž�����ɽ�����ʤ�                      //
// 2007/05/23 ����˥塼��������˥塼�ˤ������̤�����Ȳ���ɲ�          //
// 2007/10/05 ��Ω��Ψ�Υե���������ѹ�ooya����                     ��ë //
// 2007/10/07 »�״ط��Υ���պ�����˥塼��»�ס�����˥塼���ɲ�   ���� //
// 2007/10/09 site_index��define������Ƥ��ʤ���ʬ����               ���� //
// 2007/10/28 ���� ����¤����ξȲ�������˥塼���ɲ�                ���� //
// 2008/08/29 INDEX_QUALITY �ʼ���˥塼 �ɲ�                          ��ë //
// 2008/09/25 �Ұ���˥塼�˽��Ƚ����ɲ�                               ��ë //
// 2010/03/11 ����Ĺ�������塼����ɲ�                                      //
//            ͽ¬����Ψʬ�Ϥ��ɲ�(�ƥ���300144�Τ�)                   ��ë //
// 2010/05/19 �δ���ξȲ��ɲäˤ�ꡢ��˥塼����                     ��ë //
// 2010/06/21 ����ɽ��test����tel�ե�����ذ�ư                        ��ë //
// 2010/10/05 INDEX_ASSET ��������˥塼 �ɲ�                        ��ë //
// 2011/11/21 ���ͽ��Ȳ�(18)������˥塼���ɲ�                     ��ë //
// 2011/11/22 ������˥塼��Ǽ���٤����ʤξȲ���ɲ�                   ��ë //
// 2013/01/28 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2018/04/20 ����ʬ�Τߤζ��顦��ư������ɲ�                         ��ë //
// 2018/08/29 ������˥塼������˥塼��̤�����Ȳ��ʬΥ             ��ë //
// 2021/06/22 ������˥塼����Ω�Ȳù������ǥ�˥塼ʬ��factory=6      ��ë //
//            �������Ω�Τ��̤ˤ��롣����AS�����ؤǲù�������              //
//            ��¤No�������ˤʤ��ǽ������ʻؼ�No���ʤ��ʤ��         ��ë //
// 2021/07/07 �ʼ���˥塼���ʼ����Ķ���˥塼���ѹ�                   ���� //
//            �ʼ����Ķ���˥塼��[�����̥��ԡ��ѻ������]���ɲ�            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

// require_once ('function.php');
require_once ('define.php');                // ������ define���
require_once ('./function.php');

////////////// �����ƥ��å������ν����
if ( !(isset($_SESSION['s_sysmsg'])) ) {
    $_SESSION['s_sysmsg'] = '';             // ���ξ�����Ͽ
}
$sysmsg = $_SESSION['s_sysmsg'];
$_SESSION['s_sysmsg'] = '';                 // NULL �� '' ���ѹ� 2003/11/17

if (isset($_REQUEST['factory'])) {
    $_SESSION['factory'] = $_REQUEST['factory'];
    $factory = $_SESSION['factory'];
} elseif(isset($_SESSION['factory'])) {
    $factory = $_SESSION['factory'];
} else {
    $factory = '';
    $_SESSION['factory'] = $factory;
}
//////////////// �ƥ��󥫡����ѿ��ǥ��åȤ��� �ؿ�������Υ����С��إåɤ򣱲�ǺѤޤ��뤿��
if (SID == '') {
    $uniq = 'id=' . uniqid('menu'); // ���å��������Ĥ���Ƥ�����ϥ�ˡ�����ID������
} else {
    $uniq = strip_tags(SID);        // ���å��������Ĥ���Ƥ��ʤ����ϥ��å����ID����Ͽ
}                                   // XSS�˴ؤ��빶����ɻߤ��뤿�� strip_tags()�����

$uid   = $_SESSION['User_ID'];
$query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
$res   = array();
if( getResult($query,$res) <= 0 ) {
    $sid   = "";
} else {
    $sid   = $res[0][0];
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');               // ���դ����
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');  // ��˽�������Ƥ���
header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');                                     // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-type' content='text/javascript'>
<title>TNK Site Menu</title>
<style type='text/css'>
<!--
body {
    margin:             0%;
}
form {
    margin:             0%;
}
table {
    margin:             0%;
}
.yellow {
    color:              yellow;
    text-decoreation:   none;
}
.none_ {
    text-decoreation:   none;
}
.sysmsg_title {
    font-size:          7.9pt;
    color:              white;
}
.sysmsg_body {
    font-size:          7.2pt;
    color:              #ff0000;
}
a {
    font-size:          12; /* 9pt = 12px */
    color:              white;
}
a.current {
    color:              yellow;
}
a:hover {
    background-color:   blue;
}
a:active {
    background-color:   gold;
    color:              black;
}
-->
</style>
</head>

<body bgcolor='#000000' text='#ffffff' background='<?php echo IMG?>wallpaper_b1.gif' link='white' vlink='white'>
<div id='Layer1'><img alt='TNK Site Menu' width='100%' border='0' src='<?php echo IMG?>silver_line2.gif'></div>
<table border='0'>
    <?php
////////////////////////////////////////////////// index=0 �ȥåץ�˥塼
    if ($_SESSION['site_index'] == INDEX_TOP) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", TOP_MENU, "?$uniq' target='application' onMouseover=\"status='�������칩�� ���ΤΥ�˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='Top Menu' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", TOP_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�������칩�� ���ΤΥ�˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�������칩�� ���ΤΥ�˥塼��ɽ�����ޤ���'>�ȥåץ�˥塼</a></td>\n";
        echo "</tr>\n";
        if ($sid != '95') {
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 1) {  // �ƥ���Ū��TNK����ɽ��ɽ����Ԥ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='tel/tnk_tel.php?$uniq' target='application' style='text-decoration:none;' class='current'>�ԣΣ�����ɽ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='tel/tnk_tel.php?$uniq' target='application' style='text-decoration:none;'>�ԣΣ�����ɽ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // ���Ҷ�ͭ ���(�ǹ礻)�������塼��ɽ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='meeting/meeting_schedule_Main.php?$uniq' target='_blank' style='text-decoration:none;' class='current'>��İ���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='meeting/meeting_schedule_Main.php?$uniq' target='_blank' style='text-decoration:none;'>��İ���</a></td>\n";
                echo "</tr>\n";
            }
            if (getCheckAuthority(34)) {
            //if ($_SESSION['User_ID'] == '300144') {
                if ($_SESSION['site_id'] == 8) {  // ����Ĺ�� ���(�ǹ礻)�������塼��ɽ
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='meeting/meeting_manager/meeting_schedule_manager_Main.php?$uniq' target='_blank' style='text-decoration:none;' class='current'>����Ĺ�������塼��</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='meeting/meeting_manager/meeting_schedule_manager_Main.php?$uniq' target='_blank' style='text-decoration:none;'>����Ĺ�������塼��</a></td>\n";
                    echo "</tr>\n";
                }
            }
            if ($_SESSION['site_id'] == 3) {  // ��Ҥδ��ܥ��������Ȳ��Խ�(���¤�ɬ��)��˥塼
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ҥ�������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;'>��ҥ�������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // ��̳�� �Ǽ���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/soumu/bbs.php' target='_blank' style='text-decoration:none;' class='current'>��̳�� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/soumu/bbs.php' target='_blank' style='text-decoration:none;'>��̳�� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {  // ���ץ������ �Ǽ���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/toku/bbs.php' target='_blank' style='text-decoration:none;' class='current'>����� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/toku/bbs.php' target='_blank' style='text-decoration:none;'>����� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {  // ���������� �Ǽ���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/seikan/bbs.php' target='_blank' style='text-decoration:none;' class='current'>���ɲ� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/seikan/bbs.php' target='_blank' style='text-decoration:none;'>���ɲ� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {  // ���Ѳ�(�ù�����) �Ǽ���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/gijyutu/bbs.php' target='_blank' style='text-decoration:none;' class='current'>���Ѳ� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='bbs/gijyutu/bbs.php' target='_blank' style='text-decoration:none;'>���Ѳ� �Ǽ���</a></td>\n";
                echo "</tr>\n";
            }
        }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", TOP_MENU, "?$uniq' target='application' onMouseover=\"status='�������칩�� ���ΤΥ�˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='Top Menu' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", TOP_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�������칩�� ���ΤΥ�˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�������칩�� ���ΤΥ�˥塼��ɽ�����ޤ���'>�ȥåץ�˥塼</a></td>\n";
        echo "</tr>\n";
    }
    
////////////////////////////////////////////////// index=30 ������˥塼
    if ($_SESSION['site_index'] == INDEX_INDUST) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", INDUST_MENU, "?$uniq' target='application' onMouseover=\"status='���� �ط� ������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='���� �ط� ������˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", INDUST_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='���� �ط� ������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='���� �ط� ������˥塼��ɽ�����ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
        if ($sid != '95') {
        // 17=������ʽи˽���, 18=��˥����и˥�˥塼, 27=���긡�����ǻ����ݴɾ��ΰ�����
        // 28=��Ω�饤��Υ�������, 29=������������
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 16) {  // ���ʺ߸�ͽ��Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_plan/parts_stock_plan_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>���ʺ߸�ͽ��</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_plan/parts_stock_plan_form.php?$uniq' target='application' style='text-decoration:none;'>���ʺ߸�ͽ��</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 40) {  // ���ʺ߸˷���Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_history/parts_stock_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>���ʺ߸˷���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_stock_history/parts_stock_form.php?$uniq' target='application' style='text-decoration:none;'>���ʺ߸˷���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 50) {  // Ǽ��ͽ��ȸ����ųݵڤӸ�������ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_schedule.php?$uniq' target='application' style='text-decoration:none;' class='current'>Ǽ���������ų�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_schedule.php?$uniq' target='application' style='text-decoration:none;'>Ǽ���������ų�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 51) {  // ���Ϲ�������ĥꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "vendor/vendor_order_list_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ĥꥹ��</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "vendor/vendor_order_list_form.php?$uniq' target='application' style='text-decoration:none;'>��ĥꥹ��</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 25) {  // ����ñ���������������ɽ �Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/sales_material_comp_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>���ڤ������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/sales_material_comp_form.php?$uniq' target='application' style='text-decoration:none;'>���ڤ������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 23) {  // �������ξȲ�(ASSY�ֹ�)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_assy.php?$uniq' target='application' style='text-decoration:none;' class='current'>�������ASSY</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_assy.php?$uniq' target='application' style='text-decoration:none;'>�������ASSY</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 20) {  // �������ξȲ�(�ײ��ֹ�)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_plan.php?$uniq' target='application' style='text-decoration:none;' class='current'>������� �ײ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_view_plan.php?$uniq' target='application' style='text-decoration:none;'>������� �ײ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 24) {  // ����� ̤��Ͽ �Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_unregist_view&$uniq' target='application' style='text-decoration:none;' class='current'>�����̤��Ͽ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_unregist_view&$uniq' target='application' style='text-decoration:none;'>�����̤��Ͽ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 19) {  // ���������������Ψɽ
                echo "<tr>\n";
                // echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_sales_comp&$uniq' target='application' style='text-decoration:none;' class='current'>�����������</a></td>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                // echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=materialCost_sales_comp&$uniq' target='application' style='text-decoration:none;'>�����������</a></td>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;'>�����������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 21) {  // ����������Ͽ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_entry_plan.php?$uniq' target='application' style='text-decoration:none;' class='current'>���������Ͽ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "material/materialCost_entry_plan.php?$uniq' target='application' style='text-decoration:none;'>���������Ͽ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 30) {  // ������� ̤���� ���� �Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>���̤����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;'>���̤����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 13) {  // ��������ξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=aden_master_view&$uniq' target='application' style='text-decoration:none;' class='current'>��������Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=aden_master_view&$uniq' target='application' style='text-decoration:none;'>��������Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 10) {  // ��ݼ��ӤξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "payable/act_payable_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ݼ��ӾȲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "payable/act_payable_form.php?$uniq' target='application' style='text-decoration:none;'>��ݼ��ӾȲ�</a></td>\n";
                echo "</tr>\n";
            }
            /*********************
            if ($_SESSION['site_id'] == 11) {  // �ٵ�ɼ�ξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;' class='current'>�ٵ���ӾȲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;'>�ٵ���ӾȲ�</a></td>\n";
                echo "</tr>\n";
            }
            *********************/
            if ($_SESSION['site_id'] == 14) {  // ñ������ξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_cost_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>ñ������Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/parts_cost_form.php?$uniq' target='application' style='text-decoration:none;'>ñ������Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 26) {  // �������ʹ���ɽ�ξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/allocate_config/allo_conf_parts_form.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='��Ω�ײ�ΰ������ʤι���ɽ��Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\">�������ʾȲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts/allocate_config/allo_conf_parts_form.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='��Ω�ײ�ΰ������ʤι���ɽ��Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\">�������ʾȲ�</a></td>\n";
                echo "</tr>\n";
            }
            /*****
            if ($_SESSION['site_id'] == 12) {  // ȯ��ײ�ե�����ξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_plan_view.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='ȯ��ײ�ǡ����ξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">ȯ��ײ�Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/order_plan_view.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='ȯ��ײ�ǡ����ξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">ȯ��ײ�Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 22) {  // ȯ����ޥ������ξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=vendor_master_view&$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='ȯ����ޥ������ξȲ��Խ���Ԥ��ޤ���';return true;\" onMouseout=\"status=''\">ȯ����ξȲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=vendor_master_view&$uniq' target='application' style='text-decoration:none; onMouseover=\"status='ȯ����ޥ������ξȲ��Խ���Ԥ��ޤ���';return true;\" onMouseout=\"status=''\"'>ȯ����ξȲ�</a></td>\n";
                echo "</tr>\n";
            }
            *****/
            if ($_SESSION['site_id'] == 1) {    // ���ʡ����ʤΥ����ƥ�ޥ�������Ȳ��Խ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "master/parts_item/parts_item_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='���ʡ����ʤΥ����ƥ�ޥ�������Ȳ��Խ���Ԥ��ޤ���';return true;\" onMouseout=\"status=''\">�����ƥ�ޥ�����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "master/parts_item/parts_item_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='���ʡ����ʤΥ����ƥ�ޥ�������Ȳ��Խ����ޤ���';return true;\" onMouseout=\"status=''\">�����ƥ�ޥ�����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {    // ������ʽи˥�˥塼
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts_control/parts_pickup_time_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�������ʽи˻��ֽ����Ѥ���ꡦ��λ���֤����Ϥ��ޤ���';return true;\" onMouseout=\"status=''\">���ʽи˥�˥塼</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "parts_control/parts_pickup_time_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�������ʽи˻��ֽ����Ѥ���ꡦ��λ���֤����Ϥ��ޤ���';return true;\" onMouseout=\"status=''\">���ʽи˥�˥塼</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {    // ��Ω�ؼ���˥塼(����������)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process/assembly_process_time_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='��Ω�����δ����ڤӹ��������Ѥ���ꡦ��λ���֤����Ϥ��ޤ���';return true;\" onMouseout=\"status=''\">��Ω�ؼ���˥塼</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process/assembly_process_time_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='��Ω�����δ����ڤӹ��������Ѥ���ꡦ��λ���֤����Ϥ��ޤ���';return true;\" onMouseout=\"status=''\">��Ω�ؼ���˥塼</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {    // ��Ω�����Խ�(����������)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_edit/assembly_time_edit_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='��Ω�����δ����ڤӹ��������Ѥ���ꡦ��λ���֤����Ϥ��ޤ���';return true;\" onMouseout=\"status=''\">��Ω�����Խ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_edit/assembly_time_edit_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='��Ω�����δ����ڤӹ��������Ѥ���ꡦ��λ���֤����Ϥ��ޤ���';return true;\" onMouseout=\"status=''\">��Ω�����Խ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {    // �ǡ������� �С������ɺ���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=datasum_barcode&$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�ǡ�������ΥС������ɥ����ɤ�������������ޤ���';return true;\" onMouseout=\"status=''\">�С������ɺ���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "ind_branch.php?ind_name=datasum_barcode&$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�ǡ�������ΥС������ɥ����ɤ�������������ޤ���';return true;\" onMouseout=\"status=''\">�С������ɺ���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {    // ��Ω�����Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process_show/assembly_process_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='��Ω��������ꡦ��λ������Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\">��Ω�����Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_process_show/assembly_process_show_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='��Ω��������ꡦ��λ������Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\">��Ω�����Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {    // ��Ω�����Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "scheduler/schedule_show/assembly_schedule_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�ƥ饤�������Ω�����ײ�ξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">��Ω�����Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "scheduler/schedule_show/assembly_schedule_show_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�ƥ饤�������Ω�����ײ�ξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">��Ω�����Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 8) {    // ���ӹ����Ȳ�(��Ͽ���������)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_show/assembly_time_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�ƥ饤�������Ω�����ײ�ξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">���ӹ����Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_show/assembly_time_show_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�ƥ饤�������Ω�����ײ�ξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">���ӹ����Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 9) {    // ��Ω�������������ӹ�������Ͽ���� ���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_compare/assembly_time_compare_Main.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='��Ω�������������ӹ�������Ͽ���� ��Ӥ򤷤ޤ���';return true;\" onMouseout=\"status=''\">������������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "assembly/assembly_time_compare/assembly_time_compare_Main.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='��Ω�������������ӹ�������Ͽ���� ��Ӥ򤷤ޤ���';return true;\" onMouseout=\"status=''\">������������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 52) {    // Ǽ���٤����ʤξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/delivery_late/delivery_late_form.php?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='Ǽ���٤����ʤξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">Ǽ���٤�Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", INDUST, "order/delivery_late/delivery_late_form.php?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='Ǽ���٤����ʤξȲ�򤷤ޤ���';return true;\" onMouseout=\"status=''\">Ǽ���٤�Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
        }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", INDUST_MENU, "?$uniq' target='application' onMouseover=\"status='���� �ط� ������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='���� �ط� ���� ��˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", INDUST_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='���� �ط� ������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='���� �ط� ������˥塼��ɽ�����ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
    }
    if ($sid != '95') {
////////////////////////////////////////////////// index=1 ����˥塼
    if ($_SESSION['site_index'] == INDEX_SALES) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", SALES_MENU, "?$uniq' target='application' onMouseover=\"status='����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='����˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", SALES_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='����˥塼��ɽ�����ޤ���'>����˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 11) {  // �����ӾȲ� new version ������پȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "details/sales_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>������پȲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "details/sales_form.php?$uniq' target='application' style='text-decoration:none;'>������پȲ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 18) {  // ���ͽ��Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_plan/sales_plan_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>���ͽ��Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_plan/sales_plan_form.php?$uniq' target='application' style='text-decoration:none;'>���ͽ��Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 90) {  // ������� ̤���� ���� �Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>���̤����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_miken/sales_miken_Main.php?$uniq' target='application' style='text-decoration:none;'>���̤����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 13) {  // ������̹��ɽ �����ץ�����
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "custom/sales_custom_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "custom/sales_custom_form.php?$uniq' target='application' style='text-decoration:none;'>�����������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 14) {  // ��帶��Ψʬ��(����������)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material/sales_standard_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>����Ψʬ��</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material/sales_standard_form.php?$uniq' target='application' style='text-decoration:none;'>����Ψʬ��</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['User_ID'] == '300144') {
            if ($_SESSION['site_id'] == 17) {  // ͽ¬��帶��Ψʬ��(���������Ω�����ײ����)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material_pre/sales_standard_pre_form.php?$uniq' target='application' style='text-decoration:none;' class='current'>ͽ¬����Ψʬ��</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "sales_material_pre/sales_standard_pre_form.php?$uniq' target='application' style='text-decoration:none;'>ͽ¬����Ψʬ��</a></td>\n";
                echo "</tr>\n";
            }
            }
            if ($_SESSION['site_id'] == 12) {  // ���������������ɽ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp.php?$uniq' target='application' style='text-decoration:none;'>�����������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 15) {  // ���κ���������ɽ(»�׷׻�������) ���ߤǤ��������κ�����Ȳ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp2.php?$uniq' target='application' style='text-decoration:none;' class='current'>������������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "materialCost_sales_comp2.php?$uniq' target='application' style='text-decoration:none;'>������������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 16) {  // �������κ�����
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "parts_material/parts_material_show_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>������������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "parts_material/parts_material_show_Main.php?$uniq' target='application' style='text-decoration:none;'>������������</a></td>\n";
                echo "</tr>\n";
            }
            /****************************************************************************
            if ($_SESSION['site_id'] == 1) {  // �����ӾȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ӾȲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage.php?$uniq' target='application' style='text-decoration:none;'>�����ӾȲ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // ��������Ψ�ʲ��������ӾȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_temp.php?$uniq' target='application' style='text-decoration:none;' class='current'>��������Ψ�ʲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_temp.php?$uniq' target='application' style='text-decoration:none;'>��������Ψ�ʲ�</a></td>\n";
                echo "</tr>\n";
            }
            ****************************************************************************/
            if ($_SESSION['site_id'] == 3) {  // ������ץ���� ǯ�����
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_graph_daily_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>������ץ����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "uriage_graph_daily_select.php?$uniq' target='application' style='text-decoration:none;'>������ץ����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // ����ץ����
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_all_tuki.php' target='application' style='text-decoration:none;' class='current'>����ץ����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_all_tuki.php' target='application' style='text-decoration:none;'>����ץ����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {  // ���ʡ����ʤ���奰���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_all_hiritu.php' target='application' style='text-decoration:none;' class='current'>�������ʥ����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_all_hiritu.php' target='application' style='text-decoration:none;'>�������ʥ����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {  // ���ץ顦��˥�����奰���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_cl_graph.php' target='application' style='text-decoration:none;' class='current'>���ץ��˥������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "view_cl_graph.php' target='application' style='text-decoration:none;'>���ץ��˥������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 9) {  // ���ץ�ɸ���ʡ������ʤ���奰���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std.php' target='application' style='text-decoration:none;' class='current'>������ɸ�॰���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std.php' target='application' style='text-decoration:none;'>������ɸ�॰���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {  // ���ץ�ɸ���ʡ������ʤμºݸ���������ۥ����
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std_jissai.php' target='application' style='text-decoration:none;' class='current'>�üºݸ��������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", SALES, "uriage_graph_sp_std_jissai.php' target='application' style='text-decoration:none;'>�üºݸ��������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 8) {  // �»�׾Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>�»�׾Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SALES, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;'>�»�׾Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 99) { // »�� ����պ�����˥塼
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;' class='current'>����պ�����˥塼</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;'>����պ�����˥塼</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", SALES_MENU, "?$uniq' target='application' onMouseover=\"status='����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='����˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", SALES_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='����˥塼��ɽ�����ޤ���'>����˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=40 ������Ư����
    if ($_SESSION['site_index'] == INDEX_EQUIP) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' onMouseover=\"status='������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='������Ư������˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='������˥塼��ɽ�����ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($factory == '6') {
                if ($_SESSION['site_id'] == 23) {  // ���� ��ž �ؼ� 2
                  echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "monitoring/monitoring_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�ù��ؼ�</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "monitoring/monitoring_Main.php?$uniq' target='application' style='text-decoration:none;'>�ù��ؼ�</a></td>\n";
                    echo "</tr>\n";
                }
                /*
                if ($_SESSION['site_id'] == 10) {  // ���������� ��ž���� ɽ��2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;' class='current'>��ž����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;'>��ž����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 11) {  // ���������� ���ߥ���� ɽ��2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;' class='current'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                }
                */
                if ($_SESSION['site_id'] == 6) {    // �ù����ӾȲ�2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select_moni.php?$uniq' target='application' style='text-decoration:none;' class='current'>�ù�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select_moni.php?$uniq' target='application' style='text-decoration:none;'>�ù�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['factory'] != '') {   // ������⡼�ɤǤ�ɽ�����ʤ�
                    if ($_SESSION['site_id'] == 7) {    // ������ž����2
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report_moni/EquipMenu.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ž����</a></td>\n";
                        echo "</tr>\n";
                    } else {
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report_moni/EquipMenu.php?$uniq' target='application' style='text-decoration:none;'>��ž����</a></td>\n";
                        echo "</tr>\n";
                    }
                }
                /*
                if ($_SESSION['site_id'] == 8) {    // �������塼�顼�ξȲ�ڤӥ���
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;' class='current'>�������塼��</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;'>�������塼��</a></td>\n";
                    echo "</tr>\n";
                }
                */
                if ($_SESSION['site_id'] == 9) {    // ���߱�ž��ΰ���ɽ
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_moni.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_moni.php?$uniq' target='application' style='text-decoration:none;'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 12) {   // ��ž�����ޥå�(�쥤������)ɽ��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_monimap.php?$uniq' target='application' style='text-decoration:none;' class='current'>�쥤������</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_monimap.php?$uniq' target='application' style='text-decoration:none;'>�쥤������</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 25) {  // ���������Υޥ������ݼ�2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;'>�����ޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 26) {  // ���������Υ��󥿡��ե����� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>���󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>���󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 27) {  // ���������Υ����󥿡� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>������ȥޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>������ȥޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 28) {  // ������������ߤ���� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�������ޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>�������ޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 29) {  // ���������ε����λ��ѥ��󥿡��ե����� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ȥ��󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;'>�����ȥ��󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 30) {  // ���������ι����ʬ(���롼��) �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ʬ�ޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>�����ʬ�ޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                /*
                if ($_SESSION['site_id'] == 96) {  // ���ݥåȤ� fwserver1 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;' class='current'>FwServer1����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;'>FwServer1����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 90) {  // ���ݥåȤ� fwserver2 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;' class='current'>FwServer2����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;'>FwServer2����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 91) {  // ���ݥåȤ� fwserver3 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;' class='current'>FwServer3����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;'>FwServer3����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 92) {  // ���ݥåȤ� fwserver4 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;' class='current'>FwServer4����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;'>FwServer4����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 93) {  // ���ݥåȤ� fwserver5 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;' class='current'>FwServer5����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;'>FwServer5����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 94) {  // ���ݥåȤ� fwserver6 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;' class='current'>FwServer6����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;'>FwServer6����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 95) {  // ���ݥåȤ� fwserver7 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;' class='current'>FwServer7����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;'>FwServer7����</a></td>\n";
                    echo "</tr>\n";
                }
                */
            } else {
                if ($_SESSION['site_id'] == 23) {  // ���� ��ž �ؼ� 2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work_mnt/equip_workMnt_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�ù��ؼ�</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work_mnt/equip_workMnt_Main.php?$uniq' target='application' style='text-decoration:none;'>�ù��ؼ�</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 10) {  // ���������� ��ž���� ɽ��2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;' class='current'>��ž����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=chart' target='application' style='text-decoration:none;'>��ž����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 11) {  // ���������� ���ߥ���� ɽ��2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;' class='current'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_working_disp.php?$uniq&status=graph' target='application' style='text-decoration:none;'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 6) {    // �ù����ӾȲ�2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>�ù�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "hist/equip_jisseki_select.php?$uniq' target='application' style='text-decoration:none;'>�ù�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['factory'] != '') {   // ������⡼�ɤǤ�ɽ�����ʤ�
                    if ($_SESSION['site_id'] == 7) {    // ������ž����2
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report/EquipMenu.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ž����</a></td>\n";
                        echo "</tr>\n";
                    } else {
                        echo "<tr>\n";
                        echo "<td></td>\n<td nowrap><a href='", EQUIP2, "daily_report/EquipMenu.php?$uniq' target='application' style='text-decoration:none;'>��ž����</a></td>\n";
                        echo "</tr>\n";
                    }
                }
                /*
                if ($_SESSION['site_id'] == 8) {    // �������塼�顼�ξȲ�ڤӥ���
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;' class='current'>�������塼��</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "plan/equip_plan_graph.php?$uniq' target='application' style='text-decoration:none;'>�������塼��</a></td>\n";
                    echo "</tr>\n";
                }
                */
                if ($_SESSION['site_id'] == 9) {    // ���߱�ž��ΰ���ɽ
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_all.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_all.php?$uniq' target='application' style='text-decoration:none;'>��ž�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 12) {   // ��ž�����ޥå�(�쥤������)ɽ��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_map.php?$uniq' target='application' style='text-decoration:none;' class='current'>�쥤������</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "work/equip_work_map.php?$uniq' target='application' style='text-decoration:none;'>�쥤������</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 25) {  // ���������Υޥ������ݼ�2
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_macMasterMnt_Main.php?$uniq' target='application' style='text-decoration:none;'>�����ޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 26) {  // ���������Υ��󥿡��ե����� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>���󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_interfaceMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>���󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 27) {  // ���������Υ����󥿡� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>������ȥޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_counterMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>������ȥޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 28) {  // ������������ߤ���� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�������ޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_stopMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>�������ޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 29) {  // ���������ε����λ��ѥ��󥿡��ե����� �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ȥ��󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_machineInterface_Main.php?$uniq' target='application' style='text-decoration:none;'>�����ȥ��󥿡��ե�����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 30) {  // ���������ι����ʬ(���롼��) �ޥ������ݼ�
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ʬ�ޥ�����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "master/equip_groupMaster_Main.php?$uniq' target='application' style='text-decoration:none;'>�����ʬ�ޥ�����</a></td>\n";
                    echo "</tr>\n";
                }
                /*
                if ($_SESSION['site_id'] == 96) {  // ���ݥåȤ� fwserver1 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;' class='current'>FwServer1����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS1.php' target='application' style='text-decoration:none;'>FwServer1����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 90) {  // ���ݥåȤ� fwserver2 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;' class='current'>FwServer2����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS2.php' target='application' style='text-decoration:none;'>FwServer2����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 91) {  // ���ݥåȤ� fwserver3 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;' class='current'>FwServer3����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS3.php' target='application' style='text-decoration:none;'>FwServer3����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 92) {  // ���ݥåȤ� fwserver4 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;' class='current'>FwServer4����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS4.php' target='application' style='text-decoration:none;'>FwServer4����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 93) {  // ���ݥåȤ� fwserver5 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;' class='current'>FwServer5����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS5.php' target='application' style='text-decoration:none;'>FwServer5����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 94) {  // ���ݥåȤ� fwserver6 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;' class='current'>FwServer6����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS6.php' target='application' style='text-decoration:none;'>FwServer6����</a></td>\n";
                    echo "</tr>\n";
                }
                if ($_SESSION['site_id'] == 95) {  // ���ݥåȤ� fwserver7 �β�ư���� Check��
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;' class='current'>FwServer7����</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EQUIP2, "fws/equip_FWS7.php' target='application' style='text-decoration:none;'>FwServer7����</a></td>\n";
                    echo "</tr>\n";
                }
                */
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' onMouseover=\"status='������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='������Ư������˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", EQUIP2, 'equip_factory_select.php', "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='������˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='������˥塼��ɽ�����ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=3 �Ұ�������� �� �Ұ���˥塼��
    if ($_SESSION['site_index'] == INDEX_EMP) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", EMP_MENU, "?$uniq' target='application' onMouseover=\"status='�Ұ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�Ұ����������˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", EMP_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�Ұ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�Ұ���˥塼��ɽ�����ޤ���'>�Ұ���˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 1) {    // �Ұ�̾��(������)��ī PDF����(����) ja��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_ja&$uniq' target='application' style='text-decoration:none;' class='current'>̾��(����)��ī</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_ja&$uniq' target='application' style='text-decoration:none;'>̾��(����)��ī</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {    // �Ұ�̾��(������)�����å� PDF����(����) MBFPDF��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>̾��(����)�����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_section_mbfpdf&$uniq' target='application' style='text-decoration:none;'>̾��(����)�����å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {    // �Ұ�̾��(������)��ī PDF����(����) ja��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_ja&$uniq' target='application' style='text-decoration:none;' class='current'>̾��(����)��ī</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_ja&$uniq' target='application' style='text-decoration:none;'>̾��(����)��ī</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {    // �Ұ�̾��(������)�����å� PDF����(����) MBFPDF��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>̾��(����)�����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_position_mbfpdf&$uniq' target='application' style='text-decoration:none;'>̾��(����)�����å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {    // �Ұ��ζ��顦��ʡ���ư������� �����å� PDF����(����) MBFPDF��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>���顦��ư����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_mbfpdf&$uniq' target='application' style='text-decoration:none;'>���顦��ư����</a></td>\n";
                echo "</tr>\n";
            }
            /*
            if ($_SESSION['site_id'] == 6) {    // �Ұ��ζ��顦��ʡ���ư������� �����å� PDF����(����) MBFPDF�� ����ʬ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_z_mbfpdf&$uniq' target='application' style='text-decoration:none;' class='current'>�������顦��ư����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", EMP, "print/print_emp_branch.php?emp_name=print_emp_history_z_mbfpdf&$uniq' target='application' style='text-decoration:none;'>�������顦��ư����</a></td>\n";
                echo "</tr>\n";
            }
            */
            if (getCheckAuthority(27)) {
                if ($_SESSION['site_id'] == 7) {    // ���Ȱ��ν��Ƚ���Ȳ����
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EMP, "working_hours_report/working_hours_report_Main.php' target='application' style='text-decoration:none;' class='current'>���Ƚ���Ȳ�</a></td>\n";
                    echo "</tr>\n";
                } else {
                    echo "<tr>\n";
                    echo "<td></td>\n<td nowrap><a href='", EMP, "working_hours_report/working_hours_report_Main.php' target='application' style='text-decoration:none;'>���Ƚ���Ȳ�</a></td>\n";
                    echo "</tr>\n";
                }
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", EMP_MENU, "?$uniq' target='application' onMouseover=\"status='�Ұ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�Ұ����������˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", EMP_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�Ұ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�Ұ���˥塼��ɽ�����ޤ���'>�Ұ���˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=INDEX_REGU ���⵬����˥塼
    if ($_SESSION['site_index'] == INDEX_REGU) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", REGU_MENU, "?$uniq' target='application' onMouseover=\"status='���⵬����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='���⵬����˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", REGU_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='���⵬����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='���⵬����˥塼��ɽ�����ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
    } else {
        echo "<tr>\n";
        echo "<td><a href='", REGU_MENU, "?$uniq' target='application' onMouseover=\"status='���⵬����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='���⵬����˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", REGU_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='���⵬����˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='���⵬����˥塼��ɽ�����ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=INDEX_QUALITY �ʼ����Ķ���˥塼
    if ($_SESSION['site_index'] == INDEX_QUALITY) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", QUALITY_MENU, "?$uniq' target='application' onMouseover=\"status='�ʼ����Ķ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�ʼ����Ķ���˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", QUALITY_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�ʼ����Ķ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�ʼ����Ķ���˥塼��ɽ�����ޤ���'>�ʼ����Ķ���˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 71) {  // ��Ŭ������ �Ȳ񡦺���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "unfit_report/unfit_report_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>��Ŭ������ �Ȳ񡦺���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "unfit_report/unfit_report_Main.php?$uniq' target='application' style='text-decoration:none;'>��Ŭ������ �Ȳ񡦺���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 72) {  // �����̥��ԡ��ѻ������
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "copy_pepar/copy_pepar.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����̥��ԡ��ѻ������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", QUALITY, "copy_pepar/copy_pepar.php?$uniq' target='application' style='text-decoration:none;'>�����̥��ԡ��ѻ������</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", QUALITY_MENU, "?$uniq' target='application' onMouseover=\"status='�ʼ����Ķ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�ʼ����Ķ���˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", QUALITY_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�ʼ����Ķ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�ʼ����Ķ���˥塼��ɽ�����ޤ���'>�ʼ����Ķ���˥塼</a></td>\n";
        echo "</tr>\n";
    }
    ////////////////////////////////////////////////// index=INDEX_ASSET �񻺴�����˥塼
    if ($_SESSION['site_index'] == INDEX_ASSET) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", ASSET_MENU, "?$uniq' target='application' onMouseover=\"status='�ʼ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�񻺴�����˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", ASSET_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='�ʼ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�񻺴�����˥塼��ɽ�����ޤ���'>�񻺴�����˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 81) {  // ���ۻ񻺴���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "smallsum_assets_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>���ۻ񻺴���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "smallsum_assets_menu.php?$uniq' target='application' style='text-decoration:none;'>���ۻ񻺴���</a></td>\n";
                echo "</tr>\n";
            }
        }
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 82) {  // ��¤�������
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "press_tool_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>��¤�������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ASSET, "press_tool_menu.php?$uniq' target='application' style='text-decoration:none;'>��¤�������</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", ASSET_MENU, "?$uniq' target='application' onMouseover=\"status='�ʼ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�񻺴�����˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", ASSET_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='�ʼ���˥塼��ɽ�����ޤ���';return true;\" onMouseout=\"status=''\" title='�񻺴�����˥塼��ɽ�����ޤ���'>�񻺴�����˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=10 �����֡��軻���� �� »�ץ�˥塼
    if ($_SESSION['site_index'] == INDEX_PL) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", PL_MENU, "?$uniq' target='application' onMouseover=\"status='��ڤӷ軻��»�׻�����������Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\"><img alt='»�״ط�(�����֡��軻) ������˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", PL_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='��ڤӷ軻��»�׻�����������Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\" title='��ڤӷ軻��»�׻�����������Ȳ񤷤ޤ���'>»�ץ�˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 13) { // �»�� �Ȳ��˥塼��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>»�׾Ȳ��˥塼</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_query_menu.php?$uniq' target='application' style='text-decoration:none;'>»�׾Ȳ��˥塼</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 7) {  // �»�� ���� ������˥塼��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>»�׺�����˥塼</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "profit_loss_select.php?$uniq' target='application' style='text-decoration:none;'>»�׺�����˥塼</a></td>\n";
                echo "</tr>\n";
            }
/*** �쥿���� ������
            if ($_SESSION['site_id'] == 1) {  // �������祳���ɡ�����Ψ���ݼ� (�쥿����)
                echo "<tr>\n";
                echo "<td bgcolor='blue'></td>\n<td nowrap><a href='", PL, "act_table_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>���祳�����ݼ��</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "act_table_mnt.php?$uniq' target='application' style='text-decoration:none;'>���祳�����ݼ��</a></td>\n";
                echo "</tr>\n";
            }
***/
            if ($_SESSION['site_id'] == 10) { // �������祳�����ݼ� (��������)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "act_table_mnt_new.php?$uniq' target='application' style='text-decoration:none;' class='current'>���祳�����ݼ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "act_table_mnt_new.php?$uniq' target='application' style='text-decoration:none;'>���祳�����ݼ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 11) { // ��ʬ�� �����ݼ� cate_allocation category_item
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "category_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ʬ�� �����ݼ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "category_mnt.php?$uniq' target='application' style='text-decoration:none;'>��ʬ�� �����ݼ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 12) { // ��ʬ������Ψ�ޥ������ݼ� act_allocation allocation_item
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "allocation_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ʬ������Ψ�ݼ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "allocation_mnt.php?$uniq' target='application' style='text-decoration:none;'>��ʬ������Ψ�ݼ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // �������ȿ����ͻ������ɥơ��֥��ݼ� & �����ӥ���� ���� �ͷ���������ݼ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "cd_table_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ĎގÎ��̎ގ��ݼ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "cd_table_mnt.php?$uniq' target='application' style='text-decoration:none;'>�����ĎގÎ��̎ގ��ݼ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {  // ������Ψ�׻�ɽ�κ������ݼ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "machine_labor_rate_mnt.php?$uniq' target='application' style='text-decoration:none;' class='current'>������Ψ�׻�ɽ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "machine_labor_rate_mnt.php?$uniq' target='application' style='text-decoration:none;'>������Ψ�׻�ɽ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // ��Ω��ư����Ψ����Ȱ���Ψ �������Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "wage_rate/wage_rate_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>��Ω��Ψ�׻�ɽ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "wage_rate/wage_rate_menu.php?$uniq' target='application' style='text-decoration:none;'>��Ω��Ψ�׻�ɽ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 5) {  // ľ������ؤΥ����ӥ����ɽ������
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "service/service_percentage_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ӥ��������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "service/service_percentage_menu.php?$uniq' target='application' style='text-decoration:none;'>�����ӥ��������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 6) {  // ��ȱ�����������
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "pl_menu.php?$uniq' target='application' style='text-decoration:none;' class='current'>������������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "pl_menu.php?$uniq' target='application' style='text-decoration:none;'>������������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 14) { // »�� ����պ�����˥塼
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;' class='current'>����պ�����˥塼</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", PL, "graphCreate/graphCreate_Form.php?$uniq' target='application' style='text-decoration:none;'>����պ�����˥塼</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", PL_MENU, "?$uniq' target='application' onMouseover=\"status='��ڤӷ軻��»�׻�����������Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�����֡��軻��»�״ط� ������˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", PL_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='��ڤӷ軻��»�׻�����������Ȳ񤷤ޤ���';return true;\" onMouseout=\"status=''\" title='��ڤӷ軻��»�׻�����������Ȳ񤷤ޤ���'>»�ץ�˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=20 ������˥塼
    if ($_SESSION['site_index'] == INDEX_ACT) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", ACT_MENU, "?$uniq' target='application' onMouseover=\"status='���������󡦷������Ԥ��ޤ���';return true;\" onMouseout=\"status=''\"><img alt='���������󡦷 �ط�������˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", ACT_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='���������󡦷������Ԥ��ޤ���';return true;\" onMouseout=\"status=''\" title='���������󡦷������Ԥ��ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 10) {  // ��ݶ�Υ����å��ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_payable_view&$uniq' target='application' style='text-decoration:none;' class='current'>��ݶ�����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_payable_view&$uniq' target='application' style='text-decoration:none;'>��ݶ�����å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 11) {  // �ٵ�ɼ�Υ����å��ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;' class='current'>�ٵ�ɼ�����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_miprov_view&$uniq' target='application' style='text-decoration:none;'>�ٵ�ɼ�����å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 12) {  // ȯ��ײ�ե�����Υ����å��ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=order_plan_view&$uniq' target='application' style='text-decoration:none;' class='current'>ȯ��ײ�����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=order_plan_view&$uniq' target='application' style='text-decoration:none;'>ȯ��ײ�����å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 13) {  // ��������Υ����å��ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=aden_master_view&$uniq' target='application' style='text-decoration:none;' class='current'>������������å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=aden_master_view&$uniq' target='application' style='text-decoration:none;'>������������å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 21) {  // ê���ǡ����Υ����å��ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_view&$uniq' target='application' style='text-decoration:none;' class='current'>ê���ǡ��������å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_view&$uniq' target='application' style='text-decoration:none;'>ê���ǡ��������å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 22) {  // ȯ����ޥ������Υ����å��ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=vendor_master_view&$uniq' target='application' style='text-decoration:none;' class='current'>ȯ����Υ����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=vendor_master_view&$uniq' target='application' style='text-decoration:none;'>ȯ����Υ����å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 37) {  // ���̵���ٵ��ʤΥ����å��ꥹ��
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=provide_month_view&$uniq' target='application' style='text-decoration:none;' class='current'>̵���ٵ��ʥꥹ��</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=provide_month_view&$uniq' target='application' style='text-decoration:none;'>̵���ٵ��ʥꥹ��</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 35) {  // ��Υ��ץ� ê����ۤξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_c_view&$uniq' target='application' style='text-decoration:none;' class='current'>���ץ�ê�����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_c_view&$uniq' target='application' style='text-decoration:none;'>���ץ�ê�����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 36) {  // ��Υ�˥� ê����ۤξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_l_view&$uniq' target='application' style='text-decoration:none;' class='current'>��˥�ê�����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_l_view&$uniq' target='application' style='text-decoration:none;'>��˥�ê�����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 32) {  // ��Υ��ץ����� ê����ۤξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_ctoku_view&$uniq' target='application' style='text-decoration:none;' class='current'>������ê�����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_ctoku_view&$uniq' target='application' style='text-decoration:none;'>������ê�����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 34) {  // ��α��Υݥ�� ê����ۤξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_bimor_view&$uniq' target='application' style='text-decoration:none;' class='current'>���Υݥ��ê�����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=inventory_month_bimor_view&$uniq' target='application' style='text-decoration:none;'>���Υݥ��ê�����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 31) {  // ��λ�����ۤξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_purchase_view&$uniq' target='application' style='text-decoration:none;' class='current'>������ۤξȲ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_branch.php?act_name=act_purchase_view&$uniq' target='application' style='text-decoration:none;'>������ۤξȲ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 14) {  // ���� ����¤����ξȲ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_summary/act_summary_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����̷���Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", ACT, "act_summary/act_summary_Main.php?$uniq' target='application' style='text-decoration:none;'>�����̷���Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", ACT_MENU, "?$uniq' target='application' onMouseover=\"status='���������󡦷������Ԥ��ޤ���';return true;\" onMouseout=\"status=''\"><img alt='���������󡦷 �ط�������˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", ACT_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='���������󡦷������Ԥ��ޤ���';return true;\" onMouseout=\"status=''\" title='���������󡦷������Ԥ��ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=4 �ץ���೫ȯ������˥塼
    if ($_SESSION['site_index'] == INDEX_DEV) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", DEV_MENU, "?$uniq' target='application'><img alt='�ץ���೫ȯ������˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", DEV_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current'>��ȯ��˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 1) {  // �ץ���೫ȯ����� �����Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ȯ�����Ȳ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_select.php?$uniq' target='application' style='text-decoration:none;'>��ȯ�����Ȳ�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 2) {  // �ץ���೫ȯ����� ����������
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_submit.php?$uniq' target='application' style='text-decoration:none;' class='current'>������������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "dev_req_submit.php?$uniq' target='application' style='text-decoration:none;'>������������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 3) {  // ��ȯ��������������
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph_jisseki.php' target='application' style='text-decoration:none;' class='current'>��ȯ������������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph_jisseki.php' target='application' style='text-decoration:none;'>��ȯ������������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 4) {  // ��ȯ���ա���λ��̤��λ�����
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph2.php' target='application' style='text-decoration:none;' class='current'>���� ��λ ̤��λ</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='/processing_msg.php?{$uniq}&script=", DEV, "dev_req_graph2.php' target='application' style='text-decoration:none;'>���� ��λ ̤��λ</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 20) { // �ե�����Υ��顼�����å�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "color_check_input.php?$uniq' target='application' style='text-decoration:none;' class='current'>���顼�����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", DEV, "color_check_input.php?$uniq' target='application' style='text-decoration:none;'>���顼�����å�</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", DEV_MENU, "?$uniq' target='application'><img alt='�ץ���೫ȯ������˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", DEV_MENU, "?$uniq' target='application' style='text-decoration:none;'>��ȯ��˥塼</a></td>\n";
        echo "</tr>\n";
    }
////////////////////////////////////////////////// index=99 �����ƥ������˥塼
    if ($_SESSION['site_index'] == INDEX_SYS) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", SYS_MENU, "?$uniq' target='application' onMouseover=\"status='���Υ�˥塼�ϥ����ƥ����ô���ԤΤ߻��ѤǤ��ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�����ƥ������˥塼' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", SYS_MENU, "?$uniq' target='application' style='text-decoration:none;' class='current' onMouseover=\"status='���Υ�˥塼�ϥ����ƥ����ô���ԤΤ߻��ѤǤ��ޤ���';return true;\" onMouseout=\"status=''\" title='���Υ�˥塼�ϥ����ƥ����ô���ԤΤ߻��ѤǤ��ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
        if ($_SESSION['site_id'] > 0) {
            if ($_SESSION['site_id'] == 10) { // �������
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_daily.php?$uniq' target='application' style='text-decoration:none;' class='current'>�������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_daily.php?$uniq' target='application' style='text-decoration:none;'>�������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 11) { // �����
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_getuji_select.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_getuji_select.php?$uniq' target='application' style='text-decoration:none;'>�����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 20) { // �ե�����Υ��顼�����å�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "color_check_input.php?$uniq' target='application' style='text-decoration:none;' class='current'>���顼�����å�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "color_check_input.php?$uniq' target='application' style='text-decoration:none;'>���顼�����å�</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 30) { // �ǡ����١�������(���ߤϥ��Υ����å��˻���)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "database/system_db.php?$uniq' target='application' style='text-decoration:none;' class='current'>�ģ½���</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "database/system_db.php?$uniq' target='application' style='text-decoration:none;'>�ģ½���</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 31) { // AS/400 Object Source File Reference �ե�����Ȳ񡦥���
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_as400_file.php?$uniq' target='application' style='text-decoration:none;' class='current'>AS/400����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "system_as400_file.php?$uniq' target='application' style='text-decoration:none;'>AS/400����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 41) { // php�Υ�ɽ�������ꥢ
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "log_view/php_log_view_clear.php?$uniq' target='application' style='text-decoration:none;' class='current'>log_view</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "log_view/php_log_view_clear.php?$uniq' target='application' style='text-decoration:none;'>log_view</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 50) { // �ե꡼��������å�(���ޤ�Ū�ʤ��)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/free_chk.php?$uniq' target='application' style='text-decoration:none;' class='current'>�ե꡼����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/free_chk.php?$uniq' target='application' style='text-decoration:none;'>�ե꡼����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 52) { // top System status view
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/top_chk.php?$uniq' target='application' style='text-decoration:none;' class='current'>System status</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "top-free/top_chk.php?$uniq' target='application' style='text-decoration:none;'>System status</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 51) { // phpinfo �УȣФξܺپ���Ȳ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "phpinfo/phpinfoMain.php?$uniq' target='application' style='text-decoration:none;' class='current'>�����ƥ����</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "phpinfo/phpinfoMain.php?$uniq' target='application' style='text-decoration:none;'>�����ƥ����</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 60) { // ��ȯ�� Template �ե�����μ¹�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "templateSample/template.php?$uniq' target='application' style='text-decoration:none;' class='current'>��ȯtemplate</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "templateSample/template.php?$uniq' target='application' style='text-decoration:none;'>��ȯtemplate</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 70) { // ��Ҥδ��ܥ������� ���ƥʥ� (���ߤϥ�󥯤Τߤ�70�ϻ��Ѥ���Ƥ��ʤ�)
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>���ܥ�������</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "calendar/companyCalendar_Main.php?$uniq' target='application' style='text-decoration:none;'>���ܥ�������</a></td>\n";
                echo "</tr>\n";
            }
            if ($_SESSION['site_id'] == 71) { // ���� ���� �ơ��֥� ���ƥʥ�
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "common_authority/common_authority_Main.php?$uniq' target='application' style='text-decoration:none;' class='current'>���� ���� �Խ�</a></td>\n";
                echo "</tr>\n";
            } else {
                echo "<tr>\n";
                echo "<td></td>\n<td nowrap><a href='", SYS, "common_authority/common_authority_Main.php?$uniq' target='application' style='text-decoration:none;'>���� ���� �Խ�</a></td>\n";
                echo "</tr>\n";
            }
        }
    } else {
        echo "<tr>\n";
        echo "<td><a href='", SYS_MENU, "?$uniq' target='application' onMouseover=\"status='���Υ�˥塼�ϥ����ƥ����ô���ԤΤ߻��ѤǤ��ޤ���';return true;\" onMouseout=\"status=''\"><img alt='�����ƥ������˥塼' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", SYS_MENU, "?$uniq' target='application' style='text-decoration:none;' onMouseover=\"status='���Υ�˥塼�ϥ����ƥ����ô���ԤΤ߻��ѤǤ��ޤ���';return true;\" onMouseout=\"status=''\" title='���Υ�˥塼�ϥ����ƥ����ô���ԤΤ߻��ѤǤ��ޤ���'>������˥塼</a></td>\n";
        echo "</tr>\n";
    }
    }
////////////////////////////////////////////////// index=999 �������Ƚ���
    if ($_SESSION['site_index'] == INDEX_LOGOUT) {
        echo "<tr>\n";
        echo "<td bgcolor='blue'><a href='", ROOT, "logout.php?$uniq' target='_parent'><img alt='��λ(��������)' border='0' src='", SITE_ICON_ON, "'></a></td>\n";
        echo "<td nowrap><a href='", ROOT, "logout.php?$uniq' target='_parent' style='text-decoration:none;' class='current'>��λ(logout)</a></td>\n";
        echo "</tr>\n";
    } else {
        echo "<tr>\n";
        echo "<td><a href='", ROOT, "logout.php?$uniq' target='_parent' onMouseover=\"status='��λ������Ԥ��ޤ���';return true;\" onMouseout=\"status=''\"><img alt='��λ(��������)' border='0' src='", SITE_ICON_OFF, "'></a></td>\n";
        echo "<td nowrap><a href='", ROOT, "logout.php?$uniq' target='_parent' style='text-decoration:none;' onMouseover=\"status='��λ������Ԥ��ޤ���';return true;\" onMouseout=\"status=''\" title='��λ������Ԥ��ޤ���'>��λ(logout)</a></td>\n";
        echo "</tr>\n";
    }
    ?>
</table>
<div id='Layer2'><img alt='TNK Site Menu' width='100%' border='0' src='<?php echo IMG?>silver_line2.gif'></div>
<br><span class='sysmsg_title'>[�����ƥ��å�����]</span><br>
<span class='sysmsg_body'><?php echo $sysmsg ?></span>
<div id='Layer3'><img alt='TNK Site Menu' width='100%' border='0' src='<?php echo IMG?>silver_line1-2.gif'></div>
<!-- <hr> -->
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
