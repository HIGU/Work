<?php
//////////////////////////////////////////////////////////////////////////////
// ���顼�����å������ʿ��������ʿ��Ѵ�                                     //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// History                                                                  //
// 2002/09/09 Created color_check_view.php  register global off �б�        //
// 2002/12/03 �����ȥ�˥塼���ɲäΤ��� site_index site_id=20 ���ɲ�       //
// 2004/07/20 Class MenuHeader �����                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // TNK ������ function
require_once ('../MenuHeader.php');     // TNK ������ menu class
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);    // ǧ�ڥ����å�1=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(4, 20);                 // site_index=99(�����ƥ������˥塼) site_id=20(color check)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ڡ����ǥ������ѥ��顼�ӥ塼');
//////////// ɽ�������
$menu->set_caption('���顼�����å������ʿ��������ʿ��Ѵ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('color_check_view', DEV . 'color_check_view.php');

if (isset($_POST['r'])) {
    $r = $_POST['r'];   // RGB �� R
    $g = $_POST['g'];   // RGB �� G
    $b = $_POST['b'];   // RGB �� B
    $_SESSION['r'] = $r;
    $_SESSION['g'] = $g;
    $_SESSION['b'] = $b;
} else {
    $r = $_SESSION['r'];
    $g = $_SESSION['g'];
    $b = $_SESSION['b'];
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
<style type='text/css'>
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
.pt10 {
    font-size:      10pt;
}
.fllbp{
    font-size:      16pt;
    font-weight:    bold;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <hr>
        <?php
            print("$r �Σ����ʿ���:". dechex ($r) . "<br>\n");
            print("$g �Σ����ʿ���:". dechex ($g) . "<br>\n");
            print("$b �Σ����ʿ���:". dechex ($b) . "<br>\n");
            $rgb = sprintf("%02x%02x%02x",$r,$g,$b);
            print("<div class='caption_font'>#{$rgb}</div>\n");
        ?>
        <table width ='90%' bgcolor='#<?php echo $rgb ?>' border='1' cellspacing='0' cellpadding='1'>
            <tr><td>
        <table width ='100%' bgcolor='#<?php echo $rgb ?>' border='1'>
            <tr><td width ='100%' height='100'></td></tr>
        </table>
            </td></tr>
        </table>
        <hr>
        <form action='<?= $menu->out_RetUrl() ?>' method='post'>
            <input type='hidden' name='r' value='<?php echo $r ?>'>
            <input type='hidden' name='g' value='<?php echo $g ?>'>
            <input type='hidden' name='b' value='<?php echo $b ?>'>
            <input type="submit" name="input" value="���" class='ret_font'>
        </form>
    </center>
</body>
</html>
