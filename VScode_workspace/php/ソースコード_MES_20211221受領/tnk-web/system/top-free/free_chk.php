<?php
//////////////////////////////////////////////////////////////////////////////
// �ե꡼���꡼�����å�(���ޤ�Ū�ʤ��)                                   //
// Copyright(C) 2001-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/10/01 Created  free.chk.php                                         //
// 2002/12/03 �����ȥ�˥塼�����줿���� access_log �ȸ��¤��ɲ�            //
// 2002/12/27 php-4.3.0 �� leak() ���Ȥ��ʤ��ΤǺ��������                  //
// 2005/01/28 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2006/04/20 �ƥ��Ȼ��γ�ǧ�Ѥ˥��å����ɣĤ�ɽ���ɲ�                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name ��ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
// ���� 0 1 2 NG 3�Υ��ɥߥˤΤ߻��Ѳ�ǽ
////////////// ����������
$menu->set_site(99, 50);                    // site_index=40(�����ƥ��˥塼) site_id=50(�ե꡼����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ƥ� ���꡼ �����å�');
//////////// ɽ�������
$menu->set_caption('���ߤΥ����ƥ�Υ�����Ѿ��֤�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$free_memory = `free -ot`;

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
.margin1 {
    margin: 1%;
}
pre {
    color:          black;
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-decoration:underline;
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
<body onLoad='set_focus()' style='overflow:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0' cellspacing='0' cellpadding='10'>
            <tr>
                <td class='caption_font'><?=$menu->out_caption()?></td>
            </tr>
            <tr>
                <td>
                <pre>
<?php
                    echo "{$free_memory}\n";
                    echo session_id();
?>
                </pre>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
