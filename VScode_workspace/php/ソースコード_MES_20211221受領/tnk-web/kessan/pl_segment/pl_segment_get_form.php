<?php
//////////////////////////////////////////////////////////////////////////////
// »�׷׻��� ����������(ɸ�������펥��˥����Х����)�Υǡ�������ߥե����� //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/10 Created   pl_segment_get_form.php                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_PL, 7);               // site_index=10(»�ץ�˥塼) site_id=7(»�׺�����˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('»�׷׻��� ���������̤Υǡ��������');
//////////// ɽ�������
$menu->set_caption('���ǯ�����ꤷ�Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('����¹�',   PL . 'pl_segment_get.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('plSegment');

//////////// �ǯ��μ���
if (isset($_SESSION['pl_ym'])) {
    $yyyymm = $_SESSION['pl_ym'];
} else {
    $yyyymm = '';
}

//////////// �ǯ���backward/forward
if ( isset($_POST['backward']) ) {
    if (isset($_SESSION['pl_ym'])) {
        if (substr($_SESSION['pl_ym'], 4, 2) == '01') { // �оݷ1����ä���
            $_SESSION['pl_ym'] = ($_SESSION['pl_ym'] - 89); // ��ǯ��12��ˤ���
        } else {
            $_SESSION['pl_ym']--;   // �嵭�ʳ��ϥǥ�����ȤΤ�
        }
        $yyyymm = $_SESSION['pl_ym'];
    } else {
        $yyyymm = '';
    }
}
if ( isset($_POST['forward']) ) {
    if (isset($_SESSION['pl_ym'])) {
        if (substr($_SESSION['pl_ym'], 4, 2) == '12') { // �оݷ12����ä���
            $_SESSION['pl_ym'] = ($_SESSION['pl_ym'] + 89); // ��ǯ��1��ˤ���
        } else {
            $_SESSION['pl_ym']++;   // �嵭�ʳ��ϥ��󥯥���ȤΤ�
        }
        $yyyymm = $_SESSION['pl_ym'];
    } else {
        $yyyymm = '';
    }
}

//////////// �ǡ�������߳���
if (isset($_POST['getPLsegment'])) {
    // ������ header('Location: http:' . WEB_HOST . $menu->out_action('����¹�'));
    // ���� require ('invent_comp_get.php');
    // ������Ʋ��̺Ǹ�˼���ߴ�λ�Υ�å�������Ф��� �����ƥ��å�������Ф����ɤ��餫�ˤ��롣
    if ( (require ('pl_segment_get.php')) == TRUE) {   // ��̤�ͥ���̤����
        //////////// �����ƥ��å������ѿ��ش�λ���Τ�����
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>����ߴ�λ���ޤ�����</font>";  // menu_site.php �ǻ��Ѥ���
    }
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!--    �ե��������ξ��
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language='JavaScript'>
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
function set_focus(){
    document.page_form.yyyymm.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    document.page_form.yyyymm.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 11pt;
    font-family: monospace;
}
body {
    overflow-y:             hidden;
    background-image:       url(/img/t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <br>
        <br>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='ǯ��-'>
                            </td>
                        </table>
                    </td>
                    <td align='center' class='pt11b'>
                        <?php echo "{$menu->out_caption()}\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='ǯ��+'>
                            </td>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <input type='text' name='yyyymm' size='8' value='<?php echo $yyyymm ?>' maxlength='6'>
                        <br>�㡧200404 ��2004ǯ04���
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <br>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='getPLsegment' value='�¹�'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
