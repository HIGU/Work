<?php
//////////////////////////////////////////////////////////////////////////////
// Ŭ���߸˿��η׻��ե�����                                                 //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_calc_form.php                      //
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
$menu->set_site(INDEX_INDUST, 16);               // site_index=10(»�ץ�˥塼) site_id=7(»�׺�����˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ŭ���߸˿��η׻�');
//////////// ɽ�������
$menu->set_caption('���ǯ�����ꤷ�Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�׻��¹�',   INDUST . 'reasonable_stock_calc.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('r_stock');

//////////// �ǯ��μ���
if (isset($_POST['st_ym'])) {
    $st_ym = $_POST['st_ym'];
} else {
    $st_ym = '';
}

//////////// �ǡ�������߳���
if (isset($_POST['calcRstock'])) {
    // ������ header('Location: http:' . WEB_HOST . $menu->out_action('����¹�'));
    // ���� require ('invent_comp_get.php');
    // ������Ʋ��̺Ǹ�˼���ߴ�λ�Υ�å�������Ф��� �����ƥ��å�������Ф����ɤ��餫�ˤ��롣
    if ( (require ('reasonable_stock_calc.php')) == TRUE) {   // ��̤�ͥ���̤����
        //////////// �����ƥ��å������ѿ��ش�λ���Τ�����
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>�׻���λ���ޤ�����</font>";  // menu_site.php �ǻ��Ѥ���
        unset($_POST['calcRstock']);
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='red'>�׻��˼��Ԥ��ޤ�����</font>";  // menu_site.php �ǻ��Ѥ���
        unset($_POST['calcRstock']);
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
function rs_calc_click(obj) {
    if (confirm("Ŭ���߸˿��׻���¹Ԥ��ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���\n���ˤ��᤻�ޤ���")){
        return confirm("�����˼¹Ԥ��Ƥ����Ǥ��͡�");
    }
    return false;
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
    font-size:   10pt;
    font-weight: normal;
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
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        <br>
        <br>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='center' class='pt11b'>
                        <?php echo "{$menu->out_caption()}\n" ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <select name='st_ym'>
                        <?php
                        $ym = date("Ym");
                        while(1) {
                            if (substr($ym,4,2)>03) {
                                $ym = substr($ym, 0, 4) . "03";
                            } else {
                                $ym = substr($ym, 0, 4) - 1 . "03";
                            }
                            printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                            if ($ym <= 200803)
                                break;
                        }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <br>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='calcRstock' value='�׻��¹�' onClick='return rs_calc_click(this)'>
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
