<?php
//////////////////////////////////////////////////////////////////////////////
// ���顼�����å������ʿ��������ʿ��Ѵ� post data (select)                  //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// History                                                                  //
// 2002/09/09 Created color_check_input.php  register global off �б�       //
// 2002/12/03 �����ȥ�˥塼���ɲäΤ��� site_index site_id=20 ���ɲ�       //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
// 2004/07/20 Class MenuHeader �����                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // TNK ������ function
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);    // ǧ�ڥ����å�1=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(99, 20);                // site_index=99(�����ƥ������˥塼) site_id=20(color check)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ڡ����ǥ������ѥ��顼�����å�');
//////////// ɽ�������
$menu->set_caption('RGB �ν��֤ǿ��ֹ����ꤷ�Ʋ�������(10�ʿ�)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('color_check_view', SYS . 'color_check_view.php');

if (isset($_POST['r']) && isset($_POST['g']) && isset($_POST['b'])) {
    $r = $_POST['r'];               // POST �ǡ����ǽ����
    $g = $_POST['g'];
    $b = $_POST['b'];
} else {
    $r = "";                        // �����
    $g = "";
    $b = "";
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
<body onLoad='document.ini_form.r.focus()'>
    <center>
<?= $menu->out_title_border() ?>
    
        <br>
        <form name='ini_form' action='<?= $menu->out_action('color_check_view') ?>' method='post'>
            <div class='caption_font'>RGB �ν��֤ǿ��ֹ����ꤷ�Ʋ�������(10�ʿ�)</div>
            <br>
            <input type='text' name='r' size='3' value='<?php echo $r ?>' maxlength='3'>
            <input type='text' name='g' size='3' value='<?php echo $g ?>' maxlength='3'>
            <input type='text' name='b' size='3' value='<?php echo $b ?>' maxlength='3'>
            <input type='submit' name='view' value='�¹�' >
        </form>
       <br>
        <div class='pt10'>
            ��Ȥ��� Windows2000�ޤǤΥǥե�����ͤΥ��졼���� R G B �ν��֤� 214 211 206 �Ǥ���
        </div>
    </center>
</body>
</html>
