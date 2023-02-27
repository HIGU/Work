<?php
//////////////////////////////////////////////////////////////////////////////
// DATA SUM �� �С������ɥ����� �����ե�����                                //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/02/18 Created   datasum_barcode.php                                 //
// 2004/02/19 check digit ���б����Ƥ��ʤ��С������ɤ��Ȥ��Ƥ��뤿�� 0��  //
// 2004/06/15 ����� 777001��777099 �� 777999 ���ѹ� AS/400��77XXXX������� //
// 2005/02/10 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/10/19 ���硼�ȥ��åȤ�ɸ�ॿ�����ѹ� E_ALL �� E_ALL | E_STRICT ��   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047'�����ߤϥ����ɤ��㤦 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');       // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');     // TNK ������ menu class
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30,  5);                    // site_index=30(������˥塼) site_id=5(�ǡ�������С�������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ǡ������� �С������ɥ����ɺ���');
//////////// ɽ�������
$menu->set_caption('�С������� ������ ���᡼�� �ե�����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('barCode');

//////////// �С������ɤκ����ե饰
$exec_flg = false;

//////////// template �� ������
if (isset($_POST['check_uid'])) {
    $uid = $_POST['check_uid'];
    if ($uid == '') {
        $_SESSION['s_sysmsg'] = '�Ұ��ֹ椬̤���ϡ�';
        $uid  = '';
        $name = '̤����';
    } elseif (!is_numeric($uid)) {    // ���ͤ��ɤ����Υ����å�
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ椬�����ǤϤ���ޤ��󡪡�{$uid}";
        $uid  = '';
        $name = '̤����';
    } else {
        if ( ($uid < 777001) || ($uid > 777999) ) {
            //////////// SQL ʸ�μ¹�
            $search = sprintf("where uid='%06d'", $uid);
            $query  = sprintf('SELECT trim(name) as name  FROM user_detailes %s', $search);
            if ( getUniResult($query, $name) <= 0) {         // �Ұ���̾���μ���
                $_SESSION['s_sysmsg'] .= "�Ұ��ֹ椬̤��Ͽ����{$uid}";  // .= ��å��������ɲä���
                $uid  = '';
                $name = '̤����';
            } else {
                $uid = sprintf('%06d', $uid);
                $_SESSION['dsum_uid']  = $uid;  // ���å������ݴ�
                $_SESSION['dsum_name'] = $name;
                $exec_flg = true;       // ��������
            }
        } else {
            $name = '�����';
            $uid  = sprintf('%06d', $uid);
            $_SESSION['dsum_uid'] = $uid;       // ���å������ݴ�
            $_SESSION['dsum_name'] = $name;
            $exec_flg = true;       // ��������
        }
    }
} else {
    $uid  = '';
    $name = '̤����';
}

////////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.uid_form.check_uid.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    document.uid_form.check_uid.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
body {
    background-color:   #ffffc6;
    overflow-y:         hidden;
}
th {
    background-color: yellow;
    color:            blue;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <br>
        
        <!----------------- ������ caption �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' class='caption_font' style='color:blue;'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' nowrap align='left' bgcolor='white' width='98' height='90'> <!-- width=98 and height='90'�����ϻ��ȤΥХ�󥹼��(�ºݤ�75) -->
                    <div class='pt10b'>�����糫��</div>
                </td>
                <td class='winbox' nowrap align='center' bgcolor='white'>
                    <div class='pt10'>
                    <img src='/barcode/barcode39_create_png.php?data=916&check=0&mode=white'
                        alt='���糫�ϤΥС������� 916'> <!-- width='110' height='50' -->
                    <br>*��9 ��1 ��6��*
                    </div>
                </td>
            </tr>
            <tr>
                <? if ($exec_flg) { ?>
                    <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                        <div class='pt10b'>��<?php echo $name ?></div>
                    </td>
                    <td class='winbox' nowrap align='center' bgcolor='white'>
                        <div class='pt10'>
                        <img src='/barcode/barcode39_create_png.php?data=<?php echo $uid ?>&check=0&mode=white'
                            alt='<?php echo $name ?>�ΥС������� <?php echo $uid ?>' width='220' height='50'>
                        <br>* ��<?php echo substr($uid,0,1),' ��',substr($uid,1,1),' ��',substr($uid,2,1),' ��',substr($uid,3,1),' ��',substr($uid,4,1),' ��',substr($uid,5,1) ?> ��*
                        </div>
                    </td>
                <? } else { ?>
                    <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                        <div class='pt10'>��<font color='gray'><?php echo $name ?></font></div>
                    </td>
                    <td class='winbox' nowrap align='center' bgcolor='white'>
                        <div class='pt10'><font color='gray'>̤����</font></div>
                    </td>
                <? } ?>
            </tr>
            <tr>
                <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                    <div class='pt10b'>������¾�ײ�</div>
                </td>
                <td class='winbox' nowrap align='center' bgcolor='white'>
                    <div class='pt10'>
                    <img src='/barcode/barcode39_create_png.php?data=C9999999&check=0&mode=white'
                        alt='����¾�ײ� C9999999' width='250' height='50'>
                    <br>*��C ��9 ��9 ��9 ��9 ��9 ��9 ��9��*
                    </div>
                </td>
            </tr>
            <tr>
                <td class='winbox' nowrap align='left' bgcolor='white' height='90'>
                    <div class='pt10b'>���ܺ��<div>
                </td>
                <td class='winbox' nowrap align='center' bgcolor='white'>
                    <div class='pt10'>
                    <img src='/barcode/barcode39_create_png.php?data=910&check=0&mode=white'
                        alt='�ܺ�ȤΥС������� 910' width='110' height='50'>
                    <br>*��9 ��1 ��0��*
                    </div>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <br>
        
        <table border='0'>
            <tr>
                <td nowrap align='center' class='pt10b'>
                    <form name='uid_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        �Ұ��ֹ������
                        <input class='pt11b' type='text' name='check_uid' size='7' maxlength='6' value='<? $uid ?>'>
                        <input class='pt10b' type='submit' name='exec_chk' value='��ǧ'>
                    </form>
                </td>
                <? if ($exec_flg) { ?>
                <td align='center'>
                    <form name='print_form' method='get' action='datasum_barcode_mbfpdf.php'>
                        <input class='pt10b' type='submit' name='exec_print' value='PDF����'>
                    </form>
                </td>
                <? } else { ?>
                <td align='center'>
                    ��
                </td>
                <? } ?>
            </tr>
            <tr>
                <td colspan='2' align='center' class='pt10'>
                    ����Ԥ�777001��777999�ޤ�
                </td>
            </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
