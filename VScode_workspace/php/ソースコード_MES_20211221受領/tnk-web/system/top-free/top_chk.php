<?php
//////////////////////////////////////////////////////////////////////////////
// System status view(�����ƥ����ɽ��)                                     //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2005/03/03 Created   top.chk.php                                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name ��ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
// ���� 0 1 2 NG 3�Υ��ɥߥˤΤ߻��Ѳ�ǽ
////////////// ����������
$menu->set_site(99, 52);                    // site_index=40(�����ƥ��˥塼) site_id=52(top)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('System status view');
//////////// ɽ�������
$menu->set_caption('�����ƥ����ɽ�� �꥽���������̽�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// Iframe File ��ɬ���ɤ߹��ޤ���
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

<style type='text/css'>
<!--
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
pre {
    color:          black;
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    /* text-decoration:underline; */
}
-->
</style>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.form_name.element_name.select();
}
// -->
</script>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden; background-color:#ffffc6;'>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' class='caption_font'><?=$menu->out_caption()?></td>
            </tr>
            <tr>
                <td style='background-color:#d6d3ce;'>
                    <iframe hspace='0' vspace='0' scrolling='yes' src='top_chk_iframe.php?name=<?=$uniq?>' name='top_chk' align='center' width='760' height='590' title='top_check'>
                        �����ƥ������CPU���Ѿ������ˤ�ɽ�����ޤ���
                    </iframe>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
