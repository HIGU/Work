<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� �ȥå� ��˥塼 (TOP MENU)                                  //
// Copyright(C) 2001-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created   menu.php                                            //
// 2002/08/07 ���å����������ɲ� & register_globals = Off �б�            //
// 2003/02/14 TNK TOP MENU �Υե���Ȥ� style �ǻ�����ѹ�                  //
//                              �֥饦�����ˤ���ѹ�������ʤ��ͤˤ���      //
// 2003/11/17 �������������˥塼���ɲ�  ưŪ��˥塼����������ѹ�        //
// 2003/12/10 top_font��caption_font�� $menu_caption(TNK TOP MENU)�ɲ�      //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2003/12/22 PL_MENU�ˤ��ä� menuOFF�� TOP_MENU�ˤ��ɲ�                    //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/06/10 view_user($_SESSION['User_ID']) ���˥塼�إå����β����ɲ�  //
// 2004/07/06 ������˥塼 �ƽ���� ������˥塼2 ���ѹ�                    //
// 2004/07/26 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2004/09/27 ������˥塼�� EQUIP_MENU2��'equip_factory_select.php'���ѹ�  //
// 2004/12/25 style='overflow-y:hidden;' ���ɲ�                             //
// 2005/01/14 F2/F12������ͭ���������б��Τ��� document.body.focus()���ɲ�  //
// 2005/08/02 �ƥ�˥塼�֤�<br>�쥤�����Ȥ�<div>&nbsp;</div>���ѹ�NN�б�   //
// 2005/08/20 $menu->set_RetUrl()�򥳥��� MenuHeader �ǥ��å��б��Τ��� //
// 2005/09/02 logout.php �ξ��� target='application �� target='_parent'�� //
// 2005/09/12 �Ķ�����ꥻ�åȤ�baseJS.EnvInfoReset()�򱦲������ɲ�         //
// 2006/07/12 ��Ҥδ��ܥ��������Ȳ��Խ���˥塼��set_action()�ɲ�      //
// 2007/08/23 �������뷿TNK�Υޥåפ����(ALPS MAPPING K.K)               //
// 2008/08/29 �ʼ���˥塼QUALITY_MENU �ɲ�                            ��ë //
// 2010/04/09 ALPS MAPPING K.K �����ӥ���λ�ΰ١��ޥå�ɽ���򥳥���  ��ë //
// 2010/10/05 �񻺴�����˥塼 ASSET_MENU �ɲ�                         ��ë //
// 2013/11/11 �Ͻп������˥塼 PER_APPLI_MENU(�ͻ�)                       //
//                               ACT_APPLI_MENU(����) �ɲ�             ��ë //
// 2014/09/29 �Ͻп������˥塼�����ȼҳ����ѹ�                     ��ë //
// 2015/02/17 �Ͻп������˥塼�����ȼҳ���ʬ��ʤ�����             ��ë //
// 2021/07/07 �ʼ���˥塼���ʼ����Ķ���˥塼���ѹ�                   ���� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('function.php');              // ������ define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('tnk_func.php');              // menu_bar() ���������ǻ���
require_once ('MenuHeader.php');            // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(0, 999);                    // site_index=0(TOP��˥塼) site_id=999(site�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ȥå� ��˥塼');
//////////// ɽ�������
$menu->set_caption('TNK TOP MENU');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('indust_menu' , INDUST_MENU);
$menu->set_action('sales_menu'  , SALES_MENU);
$menu->set_action('equip_menu2' , EQUIP2 . 'equip_factory_select.php');
$menu->set_action('emp_menu'    , EMP_MENU);
// $menu->set_action('genka_menu' , TOP_MENU);  // ������˥塼�������
$menu->set_action('pl_menu'     , PL_MENU);
$menu->set_action('act_menu'    , ACT_MENU);
$menu->set_action('dev_menu'    , DEV_MENU);
$menu->set_action('sys_menu'    , SYS_MENU);
$menu->set_action('���⵬��'    , REGU_MENU);
$menu->set_action('quality_menu', QUALITY_MENU);
$menu->set_action('asset_menu', ASSET_MENU);
$menu->set_action('per_appli_menu', PER_APPLI_MENU);
$menu->set_action('act_appli_menu', ACT_APPLI_MENU);
// ���ߤϥ��ߡ��ǥ��å�
$menu->set_action('��ҥ�������'   , SYS . 'calendar/companyCalendar_Main.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();

$uid   = $_SESSION['User_ID'];
$query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
$res   = array();
getResult($query,$res);
$sid   = $res[0][0];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<style type="text/css">
<!--
/** font-weight: normal;        **/
/** font-weight: 400;    ��Ʊ�� **/
/** font-weight: bold;          **/
/** font-weight: 700;    ��Ʊ�� **/
/**         100��900�ޤ�100��� **/
.caption_font {
    font-size:   12pt;
    font-family: serif;
    font-weight: bold;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body style='overflow-y:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center'><img src='<?= IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption() ?>
                </td>
            </tr>
        </table>
        
        <table width='70%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?= $menu->out_action('indust_menu') ?>'>
                        <td align='center'>
                            <input type='image' alt='���� �ط� ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_industry_menu.png', '  �� �� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php
                if ($sid != '95') {
                ?>
                <tr>
                    <form method='post' action='<?= $menu->out_action('sales_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='���ط��Ȳ��˥塼' border=0 src='<?php echo IMG ?>menu_item_urimenu.gif'> -->
                            <input type='image' alt='��� �ط� �Ȳ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_sales_menu.png', '  �� �� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu2') ?>'>
                        <td align="center">
                            <!-- <input type='image' alt='��������' border=0 src='<?php echo IMG ?>menu_item_equipment.gif'> -->
                            <input type='image' alt='���������� ��ư���������ƥ� ���Υ�˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu.png', '  �� �� ��˥塼', 14, 0) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('emp_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='�ͻ�(�Ұ��������)' border=0 src='<?php echo IMG ?>menu_item_employ.gif'> -->
                            <input type='image' alt='�Ұ��ζ��顦�������������ֽ��� ���ε�Ͽ�Ȳ�' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_emp_menu.png', '  �� �� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?=$menu->out_action('���⵬��') ?>'>
                        <td align='center'>
                            <input type='image' alt='���⵬�� �Ȳ� ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_regulation_menu.png', '  �� �� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?=$menu->out_action('per_appli_menu') ?>'>
                        <td align='center'>
                            <input type='image' alt='�ϽС������� ��˥塼(����)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_per_appli_menu.png', '  �ϽС������� ��˥塼', 10) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?=$menu->out_action('quality_menu') ?>'>
                        <td align='center'>
                            <input type='image' alt='�ʼ����Ķ� ��˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_quality_environment_menu.png', '  �� �����Ķ� ��˥塼', 12) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            <!--
                <tr>
                    <form method='post' action='<?php echo TOP_MENU ?>'>
                        <td align='center'>
                            <input type='image' alt='���� �׻� ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_costAct_menu.png', '  �� �� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
            <?php
            }
            ?>
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <?php
                if ($sid != '95') {
                ?>
                <tr>
                    <form method='post' action='<?= $menu->out_action('pl_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' alt='�����֡��軻������˥塼' border=0 src='<?php echo IMG ?>menu_item_kessan_menu.gif'> -->
                            <input type='image' alt='»�פξȲ񡦺��� ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_menu.png', '  » �� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?= $menu->out_action('act_menu') ?>'>
                        <td align="center">
                            <input type='image' alt='���� �ط� ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_act_menu.png', '  �� �� ��˥塼', 14, 0) . "?$uniq" ?>'>
                            <!-- <input type='image' value='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'> -->
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('asset_menu') ?>'>
                        <td align="center">
                            <input type='image' alt='�񻺴��� ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_asset_menu.png', '  �񻺴��� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('dev_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='�ץ���೫ȯ' border=0 src='<?php echo IMG ?>menu_item_develop.gif'> -->
                            <input type='image' alt='�ץ���೫ȯ����� �������Ȳ�' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_dev_req_menu.png', '  �� ȯ ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('sys_menu') ?>'>
                        <td align="center">
                            <!-- <input type='image' value='�����ƥ����' border=0 src='<?php echo IMG ?>menu_item_edp.gif'> -->
                            <input type='image' alt='�����ƥ�������� ������˥塼' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_system_menu.png', '  �� �� ��˥塼', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php if (getCheckAuthority(38)) { ?>
                <tr>
                    <form method='post' action='<?= $menu->out_action('act_appli_menu') ?>'>
                        <td align="center">
                            <input type='image' alt='�ϽС��������˥塼(�ҳ�)' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_act_appli_menu.png', '  �ϽС������� ��˥塼(�ҳ�)', 10) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php } else { ?>
                <tr>
                    <td align='center'>
                        <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                        <div>&nbsp;</div>
                    </td>
                </tr>
                <?php } ?>
                <?php
                }
                ?>
                <tr>
                    <form method='post' action='<?= ROOT, 'logout.php' ?>' target='_parent'>
                        <td align="center">
                            <!-- <input type='image' value='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'> -->
                            <input type='image' alt='�ĿͤΥ��å�����λ���ޤ���' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_logout.png', '  �� λ (��������)', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            </table>
        </td>
        </tr>
        </table>
        <!-- ALPS MAP �����ӥ���λ�ΰ١������Ȳ� -->
        <!--<script type='text/javascript' src='/test/ALPS_MAPPING/scrollmap.js'></script> -->
        <!-- <script type='text/javascript' src='http://slide.alpslab.jp/scrollmap.js'></script> -->
        <!--<div class='alpslab-slide'> -->
        <!--    scale:70000 36/42/22.299,139/58/5.726 -->
        <!--    <a href='http://base.alpslab.jp/?s=25000;p=36/42/22.299,139/58/5.726' target='_blank'><img src='http://clip.alpslab.jp/bin/map?pos=36/42/22.299,139/58/5.726&scale=25000'></a> -->
        <!--</div> -->
        <span style='position:absolute; bottom:1px; right:1px;'>
            <input type='button' name='envReset' value='�Ķ��ꥻ�å�' onClick='baseJS.EnvInfoReset();'
                onMouseover='status="Window�ΰ��֤��礭���ڤӳ����Ƥ��뤫���ξ���������֤��ᤷ�ޤ���"; return true;'
                onMouseout='status=""'
                title='Window�ΰ��֤��礭���ڤӳ����Ƥ��뤫���ξ���������֤��ᤷ�ޤ���'
            >
        </span>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
