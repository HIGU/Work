<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�����ܼԤ����ä˴ؤ����ϽС�                         //
// Copyright (C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/19 Created  support_inc_appli_menu.php                           //
// 2014/09/22 ������ˡ��ɽ�����ɲ�                                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('../../function.php');       // TNK ������ function
require_once ('../../MenuHeader.php');     // TNK ������ menu class
require_once ('../../tnk_func.php');
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);    // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�

////////////// ����������
$menu->set_site(97, 999);                // site_index=4(�ץ���೫ȯ) site_id=999(�ҥ�˥塼����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);            // ������ꤷ�Ƥ���
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ϽС��������˥塼�ʿͻ���');
//////////// ɽ�������
$menu->set_caption('�ϽС��������˥塼�ʿͻ���');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script type='text/javascript' language='JavaScript' src='../per_appli.js'></script>
<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
</head>
<body onLoad='Regu.set_focus(document.getElementById("start", ""))'>
    <center>
<?= $menu->out_title_border() ?>
    <div class='pt12b'>&nbsp;</div>
    <B>
    ������������������������������������������������������������������������������������������
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutt' id='start' colspan='2'>
            <B>�����ܼԤ����ä˴ؤ����ϽСʷ뺧���л��ʳ��ξ���</B>
            </td>
        </tr>
    </table>
     <B>
    ������������������������������������������������������������������������������������������
    </B>
    <BR><B>
    ���Ƽ���н���򥯥�å�����ȡ��س����١�����¸�٤Υ�˥塼��ɽ������ޤ��Τ�<BR>
      �ѥ�������ݴɤ������Ѥ��Ʋ�������
    </B><BR>
     <B>
    ������������������������������������������������������������������������������������������
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            ����No.
            </td>
            <td class='layoutg' id='start'>
            �ܿ���н���
            </td>
            <td class='layoutg' id='start'>
            ����
            </td>
            <td class='layoutg' id='start'>
            ��̳�� �������ࡢ��ջ���
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0701
            </td>
            <td class='layoutb' id='start'>
                ��н������ɽ
                <!-- <a href="download_file.php/131004����ϡʲ���2��.xls">��н������ɽ</a> -->
            </td>
            <td class='layouty' id='start'>
            <font color='red'><B>�Ϥ���ˤ��ɤ߲�����</B></font>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0702
            </td>
            <td class='layoutb' id='start'>
                �ͻ������ѹ��ǡ���������
                <!-- <a href="download_file.php/�·翽���.doc">�ͻ������ѹ��ǡ���������</a> -->
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0703
            </td>
            <td class='layoutb' id='start'>
                ��Ϳ�����Ԥ����ܹ������ʰ�ư��
                <BR>
                �����
                <!-- <a href="download_file.php/����ֳ���Ⱦ�����.pdf">��Ϳ�����Ԥ����ܹ������ʰ�ư��<BR>�����</a> -->
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0704
            </td>
            <td class='layoutb' id='start'>
                ���ݸ������ܼԡʰ�ư����
                <!-- <a href="download_file.php/����ֳ���Ⱦ�����.pdf">���ݸ������ܼԡʰ�ư����</a> -->
            </td>
            <td class='layout' id='start'>
            ���ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            �ѻ�ϡ�����ʣ�̤������ѻ�
            <BR>
            �Ǥ�����̳�ݤ˿��ФƲ�������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0705
            </td>
            <td class='layoutb' id='start'>
                ��̱ǯ���裳�����ݸ��Ի�ʼ�����
                <!-- <a href="download_file.php/���е��ľ�.xls">��̱ǯ���裳�����ݸ��Ի�ʼ�����</a> -->
                <BR>
                ���۶��Ԥ�ǯ���Ģ
                <BR>
                ���ʴ���ǯ���ֹ����ν�ˤμ̤�
            </td>
            <td class='layout' id='start'>
            �۶��Ԥ����ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0706
            </td>
            <td class='layoutb' id='start'>
                �����ݸ����ݸ���Υ��ɼ�ݣ����ݣ�
                <!-- <a href="download_file.php/���е��ľ�.xls">�����ݸ����ݸ���Υ��ɼ�ݣ����ݣ�</a> -->
            </td>
            <td class='layout' id='start'>
            �����ݸ��β����Ԥ��ä�����
            <BR>
            ���ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0707
            </td>
            <td class='layoutb' id='start'>
                �����������������
                <!-- <a href="download_file.php/���е��ľ�.xls">�����������������</a> -->
            </td>
            <td class='layout' id='start'>
            ����ħ��ɼ�ޤ�������Ǿ�����
            <BR>
            ǯ�����Ԥϡ�ǯ��������ν��
            <BR>
            �μ̤�
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
    </table>
    <font color='red'><B>������ź�ս���ˤʤ�Τǡ������κݤ˼̤���ź�դ��Ʋ�������</font></B>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
