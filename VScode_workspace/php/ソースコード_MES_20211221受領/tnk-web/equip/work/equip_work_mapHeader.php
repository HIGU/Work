<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�α�ž���������ޥå�ɽ��(�쥤������)Header�ե졼��   //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/23 Created  equip_work_mapHeader.php                             //
//            ��ɽ���� method �� post����get���ѹ� JavaScript��reload�б�   //
//            ����ɽ�������ؤ���ܥ�����ɲ�                                //
// 2005/02/16 �ꥯ�����Ȥ򥻥å�������¸ $_SESSION['factory'] = $factory  //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2005/07/11 �����ʬ�򤳤Υ�˥塼����ϥ��å�������Ͽ���ʤ��褦���ѹ�  //
// 2005/07/25 �嵭�򸵤��ᤷ                                                //
// 2005/08/20 $menu->_parent �� $menu->out_parent() ���ѹ�                  //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2021/06/22 �������﫤�SUS��ʬΥ                                  ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 12);                    // site_index=40(������˥塼) site_id=12(�ޥåװ���)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��ư���� �쥤������ ɽ��');
//////////// ɽ�������
$menu->set_caption('��������');

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
    $_SESSION['factory'] = $factory;
} else {
    ///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
    $factory = @$_SESSION['factory'];
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:    5px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.mac_form.factory.focus();      // �������륭���ǹ�����ư����褦�ˤ���
}
function parts_upper(obj) {
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    return true;
}
function win_open(url) {
    var w = 430;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td class='winbox'>
                    <input style='font-size:10pt; font-weight:bold; color:blue;' type='submit' name='map_help' value='����' onClick='win_open("map_help.html")'>
                </td>
                <td class='winbox' align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_parent() ?>' target='_parent'>
                    <select name='factory' class='ret_font' onChange='document.mac_form.submit()'>
                        <!--
                        <option value='' <?php if($factory=='') echo 'selected'; ?>>������</option>
                        <option value='1' <?php if($factory==1) echo 'selected'; ?>>������</option>
                        <option value='2' <?php if($factory==2) echo 'selected'; ?>>������</option>
                        <option value='4' <?php if($factory==4) echo 'selected'; ?>>������</option>
                        <option value='5' <?php if($factory==5) echo 'selected'; ?>>������</option>
                        <option value='6' <?php if($factory==6) echo 'selected'; ?>>������</option>
                        -->
                        <option value='7' <?php if($factory==7) echo 'selected'; ?>>������(���)</option>
                        <option value='8' <?php if($factory==8) echo 'selected'; ?>>������(SUS)</option>
                    </select>
                    </form>
                </td>
                <td class='winbox'>
                    <form name='reload_form' action='equip_work_mapList.php' method='get' target='List'>
                        <input style='font-size:10pt; color:blue;' type='submit' name='reload' value='��ɽ��'>
                        <input type='hidden' name='factory' value='<?=$factory?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='equip_work_all.php' method='get' target='_parent'>
                        <input style='font-size:10pt; color:blue;' type='submit' name='all_view' value='����ɽ��'>
                        <input type='hidden' name='factory' value='<?=$factory?>'>
                    </form>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
