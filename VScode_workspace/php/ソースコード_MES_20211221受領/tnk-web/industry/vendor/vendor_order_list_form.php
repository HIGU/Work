<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ϲ�������ĥꥹ�ȤξȲ� �������ե�����                              //
// Copyright (C) 2005-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/04/26 Created   vendor_order_list_form.php                          //
// 2005/04/30 ����������ľ���������������٤뵡ǽ�ɲ� *�֥�󥯽������ݥ����//
// 2006/08/31 ��ĥꥹ�Ȥ��鸡�������ͽ�󤬽����褦�˵�ǽ����(��ʸ��Τ�)//
//            ʬǼ��ɼ����ʸ�ǡ�����2�Ť˸����Ƥ��ޤ����ḡ�������ѤȤ���   //
//            �̥ܥ���Ǽ�������褦�ˤ�����                                //
// 2011/05/25 ��ĥꥹ�Ȥ�CSV�ǽ��ϤǤ���褦�ˤ�����                  ��ë //
// 2015/10/19 ���ʥ��롼�פ�T=�ġ�����ɲá�                           ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
// require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 51);                    // site_index=30(������˥塼) site_id=999(̤��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���Ϲ����� ��ĥꥹ�� (�������)');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ĥꥹ��',   INDUST . 'vendor/vendor_order_list.php');
$menu->set_action('��ĥꥹ��2',  INDUST . 'vendor/vendor_order_list-2line.php');
$menu->set_action('��ĥꥹ��3',  INDUST . 'vendor/vendor_order_list_inspection.php');
$menu->set_action('��ĥꥹ��4',  INDUST . 'vendor/vendor_order_list-2line_inspection.php');
$menu->set_action('CSV����',   INDUST . 'vendor/vendor_order_csv.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

/////////////// �����Ϥ��ѿ��ν����
// �����ƥ��å�������¸���Ƥ��ʤ�
$vendor = '';
$div    = '';
$plan_cond = '';

//////// ���Ϲ���̾�ڤӥ����ɤμ��� (�٥����̾�����꽻�꤬��Ͽ����Ƥ�����)���ܶ⤫�齻����ѹ�
$query = "select vendor, substr(trim(name),1,10) from vendor_master where trim(name) != '' and trim(address1) != '' order by vendor ASC";
$res = array();
if (($rows = getResult2($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] = "ȯ����ޥ�����������Ǥ��ޤ���";
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
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    �ե��������ξ�� -->
<script language='JavaScript' src='./vendor_order_list_form.js?<?= $uniq ?>'></script>

<script language="JavaScript">
<!--
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size:      12pt;
    font-weight:    bold;
    /* font-family:    monospace; */
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
-->
</style>
</head>
<body onLoad='set_focus();' style='overflow-y:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='vendor_form'
            action='JavaScript:win_open("<?=$menu->out_action('��ĥꥹ��')?>", document.vendor_form.vendor.value)'
            method='post' onSubmit='return chk_vendor_order_list_form(this)'
        >
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' colspan='3' align='center' style='background-color:blue; color:white;'>
                        <span class='caption_font'><?= $menu->out_caption(), "\n" ?></span>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' style='background-color:#ffffc6; color:blue;'>
                        ����
                    </td>
                    <td class='winbox' align='center' style='background-color:#ffffc6; color:blue;'>
                        �������
                    </td>
                    <td class='winbox' align='center' style='background-color:#ffffc6; color:blue;'>
                        ľ�ܻ���
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ȯ����̾������<br>
                        ����<br>
                        ȯ���襳���ɤ����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='vendor2' size='10' class='pt12b'
                            onClick='vendor_copy()'
                            onChange='vendor_copy()'
                            onDblClick='if (chk_vendor_order_list_form(document.vendor_form)==true) document.vendor_form.submit();'
                        >
                        <?php
                            for ($i=0; $i<$rows; $i++) {
                                echo "<option value='{$res[$i][0]}'>\n";
                                echo "{$res[$i][0]} {$res[$i][1]}\n";
                                echo "</option>\n";
                            }
                        ?>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='vendor' class='pt12b' size='6' value='<?= $vendor ?>' maxlength='5' onChange='vendor_copy2()'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ʥ��롼��<br>
                        �֥�󥯡�����
                        <!-- (C=���ץ� L=��˥� T=�ġ��� SC=C���� CS=Cɸ��) -->
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div2' size='5' class='pt12b'
                            onClick='div_copy()'
                            onChange='div_copy()'
                            onDblClick='if (chk_vendor_order_list_form(document.vendor_form)==true) document.vendor_form.submit();'
                        >
                            <option <?php if ($div=='C') echo 'selected'?>  value= 'C'>���ץ�</option>
                            <option <?php if ($div=='SC') echo 'selected'?> value='SC'>������</option>
                            <option <?php if ($div=='CS') echo 'selected'?> value='CS'>��ɸ��</option>
                            <option <?php if ($div=='L') echo 'selected'?>  value= 'L'>��˥�</option>
                            <option <?php if ($div=='L') echo 'selected'?>  value= 'T'>�ġ���</option>
                            <option <?php if ($div==' ') echo 'selected'?>  value= '' >������</option>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='div' class='pt12b' size='2' value='<?= $div ?>' maxlength='2' style='text-align:center;' onChange='div_copy2()'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ȯ�������ʬ<br>
                        �֥�󥯡�����
                        <!-- (�֥��=���� O=��ʸ��ȯ�Ժ� R=�⼨�� P=ͽ��) -->
                    </td>
                    <td class='winbox' align='center'>
                        <select name='plan_cond2' size='4' class='pt12b'
                            onClick='plan_cond_copy()'
                            onChange='plan_cond_copy()'
                            onDblClick='if (chk_vendor_order_list_form(document.vendor_form)==true) document.vendor_form.submit();'
                        >
                            <option <?php if ($plan_cond==' ') echo 'selected'?> value='' >������</option>
                            <option <?php if ($plan_cond=='P') echo 'selected'?> value='P'>ͽ����</option>
                            <option <?php if ($plan_cond=='R') echo 'selected'?> value='R'>�⼨��</option>
                            <option <?php if ($plan_cond=='O') echo 'selected'?> value='O'>��ʸ��</option>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='plan_cond' class='pt12b' size='2' value='<?= $plan_cond ?>' maxlength='1' style='text-align:center;' onChange='plan_cond_copy2()'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='3' align='center'>
                        <input type='submit' name='list_view' value='����ɽ��'>
                        <input type='button' name='list_view2' value='����ɽ��'
                            onClick='win_open2("<?=$menu->out_action('��ĥꥹ��2')?>", document.vendor_form.vendor.value)'>
                        <input type='button' name='list_inspection' value='���ʰ�����'
                            onClick='win_open2("<?=$menu->out_action('��ĥꥹ��3')?>", document.vendor_form.vendor.value)'>
                        <input type='button' name='list_inspection2' value='���ʰ�����'
                            onClick='win_open2("<?=$menu->out_action('��ĥꥹ��4')?>", document.vendor_form.vendor.value)'>
                        <input type='button' name='csv_output' value='CSV����'
                            onClick='csv_output2("<?=$menu->out_action('CSV����')?>", document.vendor_form.vendor.value)'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    </center>
</body>
<?= $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
