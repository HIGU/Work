<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�ʼ��� �����˴ؤ����ϽС�                            //
// Copyright (C) 2014-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_account_appli.php                                 //
// 2015/02/06 �����ο�������ɲ�                                            //
// 2015/02/12 �����ο�������ɲ�                                            //
// 2015/02/17 ��ݡ�̤ʧ�ν����ʬ��                                        //
// 2016/04/22 ��������ѿ���������                                        //
// 2017/02/10 ��ݹ��������ν�ν񼰤��ѹ�                                  //
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
$menu->set_title('�ϽС��������˥塼�ʼ��� ������');
//////////// ɽ�������
$menu->set_caption('�ϽС��������˥塼�ʼ��� ������');

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
            <td class='layoutg' id='start' rowspan='6'>
            ����ĥ�˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��ĥ����.xls">��ĥ����</a>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/������ĥ����.xls">������ĥ����ʱߴ�����</a>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��ȯ���Υ졼�Ȥ���̳�ݤ˳�ǧ���뤳�ȡ�
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/������ʲ�����.xls">��������ѿ�����</a>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'16/04����</font></B>��������ѿ�����
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/������������(��).xls">������������ʣʣҡ�</a>
                <!--
                <a href="download_file.php/���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�Ҷ������������.pdf">�Ҷ������������</a>
                <!--
                <a href="download_file.php/���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��-������������������-12.06.xls">��������������</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='2'>
            ����ʧ�ѹ��˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��ʧ������̤ʧ��ɼ��.xls">��ʧ������̤ʧ��ɼ�ط���</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��ʧ�ѹ������.xls">��ʧ�ѹ������</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='3'>
            ���������Ͽ�˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��ʧ�ѹ�-̤ʧ.xls">̤ʧ��ɼ���������ν�</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��ݹ��������ν�_20160713.xls">��ݹ��������ν�</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��Կ��������.doc">��Ը��¿��������</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
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
