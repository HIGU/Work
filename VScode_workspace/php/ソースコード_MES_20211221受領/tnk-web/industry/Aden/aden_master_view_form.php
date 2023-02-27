<?php
//////////////////////////////////////////////////////////////////////////////
// ��������ե�����ξȲ��� ������ե�����  ������ UKWLIB/W#MIADIM        //
// Copyright (C) 2004-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/21 Created   aden_master_view_form.php                           //
// 2004/10/22 �ӣù��֤������ֹ�assy_no(parts_no)�Ǹ����Ǥ��뵡ǽ�ɲ�       //
// 2004/10/23 �嵭���ѹ��ǣ�����θ���������뤿�ḫ�䤹�������ȥ��ưŪ�ѹ�//
//            ���֥륯��å��Ǽ¹Ԥ��뵡ǽ���ɲ� onDblClick='submit()'      //
// 2005/01/18 ������å������ɲ� �������칩��Υ��򱦲����ɲ� background  //
// 2005/01/24 ���֤��Υ��֤�onKeyDown='tab_chk()'�������ܥ����onFocus()��  //
// 2011/01/11 SC���֤����Ϸ��8�夫��9����ѹ�                         ��ë //
// 2015/02/06 A��̤�����Τߤ���Ф��ɲ�                                ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 13);                    // ����=20 ����=13

////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);          // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� �ե�����');
//////////// ɽ�������
$menu->set_caption('�ӣù��֤����Ϥ���Enter�����򲡤��Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('A������ξȲ�',   INDUST . 'Aden/aden_master_view.php');

///// ���å�������Υѥ�᡼��������
if (isset($_SESSION['sc_no'])) {
    $sc_no = $_SESSION['sc_no'];
} else {
    $sc_no = '';
}
if (isset($_SESSION['aden_no'])) {
    $aden_no = $_SESSION['aden_no'];
} else {
    $aden_no = '';
}
if (isset($_SESSION['aden_assy_no'])) {
    $aden_assy_no = $_SESSION['aden_assy_no'];
} else {
    $aden_assy_no = '';
}

//////////// JavaScript Stylesheet File ����cache�ɻ�
$uniq = uniqid('target');

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

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    document.sc_form.sc_no.focus();
    document.sc_form.sc_no.select();
}
function assy_upper(obj) {
    obj.aden_assy_no.value = obj.aden_assy_no.value.toUpperCase();
    return true;
}
function sc_upper(obj) {
    obj.sc_no.value = obj.sc_no.value.toUpperCase();
    return true;
}
function title_chg(name) {
    if (document.all) {                         // IE4-
        document.all.sc.style.backgroundColor   = '#d6d3ce';
        document.all.sc.style.color             = 'blue';
        document.all.aden.style.backgroundColor = '#d6d3ce';
        document.all.aden.style.color           = 'blue';
        document.all.assy.style.backgroundColor = '#d6d3ce';
        document.all.assy.style.color           = 'blue';
    } else if (document.getElementById) {       // NN6- NN7.1- (IE5.5����Ѳ�)
        document.getElementById('sc').style.backgroundColor   = '#d6d3ce';
        document.getElementById('sc').style.color             = 'blue';
        document.getElementById('aden').style.backgroundColor = '#d6d3ce';
        document.getElementById('aden').style.color           = 'blue';
        document.getElementById('assy').style.backgroundColor = '#d6d3ce';
        document.getElementById('assy').style.color           = 'blue';
    } else {
        return;
    }
    switch (name) {
    case 'aden_assy_no':
        if (document.all) {
            document.all.assy.style.backgroundColor = 'darkblue';
            document.all.assy.style.color           = 'white';
        } else {
            document.getElementById('assy').style.backgroundColor = 'darkblue';
            document.getElementById('assy').style.color           = 'white';
        }
        break;
    case 'aden_no':
        if (document.all) {
            document.all.aden.style.backgroundColor = 'darkblue';
            document.all.aden.style.color           = 'white';
        } else {
            document.getElementById('aden').style.backgroundColor = 'darkblue';
            document.getElementById('aden').style.color           = 'white';
        }
        break;
    case 'sc_no':
        if (document.all) {
            document.all.sc.style.backgroundColor = 'darkblue';
            document.all.sc.style.color           = 'white';
        } else {
            document.getElementById('sc').style.backgroundColor = 'darkblue';
            document.getElementById('sc').style.color           = 'white';
        }
        break;
    default:
        return;
    }
    return;
}
function tab_chk() {
    if (event.keyCode == 9) {       // tab
        document.all.note.focus();
        return;
    }
    //  onKeyDown='tab_chk()'
    /***********
    if (event.keyCode == 16) {      // shift
        document.aden_form.aden_no.focus();
        return;
    }
    ***********/
    return;
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
th {
    background-color:yellow;
    color:          blue;
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
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
.note {
    border: 2px solid #0A0;
}
body {
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='sc_form' method='get' action='<?= $menu->out_action('A������ξȲ�'), "?id=$uniq" ?>' onSubmit='return sc_upper(this)'>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; color:blue; font-family:monospace;' id='sc'>
                        <?= $menu->out_caption() . "\n" ?>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; font-family:monospace;'>
                        �㣱:SC410181 �㣲:SC410* �㣳:*410181 �㣴:*4101*
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:11pt;'>
                        ���Ϥ����ˡ�Enter�פ򲡤��кǿ��Σӣù��ֽ�����ƾȲ�
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <input class='pt12b' type='text' name='sc_no' size='10' maxlength='9' value='<?= $sc_no ?>' style='text-align:center;' onFocus='title_chg(this.name)' onDblClick='submit()' tabindex='2'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <br>
        
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='aden_form' method='get' action='<?= $menu->out_action('A������ξȲ�'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; color:blue; font-family:monospace;' id='aden'>
                        �����ֹ�����Ϥ���Enter�����򲡤��Ʋ�������
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; font-family:monospace;'>
                        �㣱:795404 �㣲:7953* �㣳:*95367 �㣴:*9536*
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:11pt;'>
                        ���Ϥ����ˡ�Enter�פ򲡤��кǿ���A���ֹ������ƾȲ�
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <input class='pt12b' type='text' name='aden_no' size='7' maxlength='6' value='<?= $aden_no ?>' style='text-align:center;' onFocus='title_chg(this.name)' onDblClick='submit()' tabindex='3'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <br>
        
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='assy_form' method='get' action='<?= $menu->out_action('A������ξȲ�'), "?id=$uniq" ?>' onSubmit='return assy_upper(this)'>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; color:blue; font-family:monospace;' id='assy'>
                        ���ʡ������ֹ�����Ϥ���Enter�����򲡤��Ʋ�������
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:12pt; font-weight:bold; font-family:monospace;'>
                        �㣱:CB21655-0 �㣲:CB2165* �㣳:*21655-0 �㣴:*21655*
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' style='font-size:11pt;'>
                        ���Ϥ����ˡ�Enter�פ򲡤������ʡ������ֹ������ƾȲ�
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <input class='pt12b' type='text' name='aden_assy_no' size='10' maxlength='9' value='<?= $aden_assy_no ?>' style='text-align:center;' onFocus='title_chg(this.name)' onDblClick='submit()' tabindex='4'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <br>
        
        
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' cellspacing="0" cellpadding="3" border='1'>
            <form name='assy_form' method='submit' action='<?= $menu->out_action('A������ξȲ�'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' width='500' nowrap align='center'>
                        <input class='pt11b' type='submit' name='aden_mikan' value='A��̤�����ξȲ�(SC����ͭ)'>
                    </td>
                </tr>
            </form>
            <form name='assy_form' method='submit' action='<?= $menu->out_action('A������ξȲ�'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' width='500' nowrap align='center'>
                        <input class='pt11b' type='submit' name='aden_mikanc' value='A��̤�����ξȲ�(���ץ� SC����̵)'>
                    </td>
                </tr>
            </form>
            <form name='assy_form' method='submit' action='<?= $menu->out_action('A������ξȲ�'), "?id=$uniq" ?>'>
                <tr>
                    <td class='winbox' width='500' nowrap align='center'>
                        <input class='pt11b' type='submit' name='aden_mikanl' value='A��̤�����ξȲ�(��˥�)'>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        
        
        <hr>
        <br>
        
        <table class='note'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>�嵭�γ�������ǥ��֥륯��å������Enter������Ʊ�����¹Ԥ��ޤ���</td></tr>
        </table>
        <br>
        <table class='note'>
            <tr><td align='center' class='pt11b'>�ԣ��¥�������������ư�Ǥ��ޤ����ޤ���F2���������β��̤����ޤ���</td></tr>
        </table>
    </center>
</body>
<input type='button' name='none' value='' tabindex='5' onFocus='set_focus()' style='font-size:1pt;'>
</html>
