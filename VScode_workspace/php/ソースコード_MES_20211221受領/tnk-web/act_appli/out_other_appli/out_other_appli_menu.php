<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�ʼҳ� ����¾���ϽС�                                //
// Copyright (C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  out_other_appli.php                                  //
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
$menu->set_title('�ϽС��������˥塼�ʼҳ� ����¾��');
//////////// ɽ�������
$menu->set_caption('�ϽС��������˥塼�ʼҳ� ����¾��');

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
    <BR><B>
    ���Ƽ���н���򥯥�å�����ȡ��س����١�����¸�٤Υ�˥塼��ɽ������ޤ��Τ�<BR>
      �ѥ�������ݴɤ������Ѥ��Ʋ�������
    </B><BR>
     <B>
    ������������������������������������������������������������������������������������������
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutg' id='start' align='center'>
            ����
            </td>
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
            <td class='layoutg' id='start' rowspan='3'>
            ������ԥ塼������Ϣ���Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0101
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��ȯ�����20080710.doc">��ȯ�����</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/��ȯ������ê������ɽ��.doc">������</a>
            </td>
            <td class='layout' id='start'>
            AS/400 �ץ���೫ȯ����������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0102
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�ǡ���ľ�ܽ���������20080409.xls">�ǡ���ľ�ܽ���������</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/�ǡ���ľ�ܽ���������(������).xls">������</a>
            </td>
            <td class='layout' id='start'>
            AS/400 �ǡ���ľ�ܽ�������������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0103
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/����������������20080407.xls">����������������</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            �᡼�롦ü�� �ɲð���������
            </td>
        </tr>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
