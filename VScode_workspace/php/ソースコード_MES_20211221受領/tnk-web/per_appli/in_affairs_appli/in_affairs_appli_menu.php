<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�ʼ��� ��̳�˴ؤ����ϽС�                            //
// Copyright (C) 2014-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_affairs_appli.php                                 //
// 2018/03/08 ���ͽ��ɽ���4���ѹ�                                    ��ë //
// 2019/05/30 ��Ϳ�������19ǯ�Ǥ��ѹ�                                 ��ë //
// 2021/11/24 �ޥ������ζ�̳���Ѥο�������ɲ�                         ��ë //
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
$menu->set_title('�ϽС��������˥塼�ʼ��� ��̳��');
//////////// ɽ�������
$menu->set_caption('�ϽС��������˥塼�ʼ��� ��̳��');

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
            ����Ĥ�˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/������Ĥ�ִط��Ͻн��ѻ�.doc">Ĥ�ִط��Ͻн�ʼ����</a>
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
                <a href="download_file.php/�Ļ��ط��Ͻн񡡸���.xls">�Ļ��ط��Ͻн�ʼ����</a>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
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
                <a href="download_file.php/�ҳ���Ĥ�ִط��Ͻн��ѻ�.doc">Ĥ�ִط��Ͻн�ʼҳ���</a>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
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
                �Ļ��ط��Ͻн�ʼҳ���
                <BR>
                <a href="download_file.php/�Ļ��ط��Ͻн�(�ҳ��ѵ�ǰ��ŵ)����.xls">�ҳ��ѵ�ǰ��ŵ</a>
                <BR>
                <a href="download_file.php/�Ļ��ط��Ͻн�(�ҳ��ѷ뺧)����.xls">�ҳ��ѷ뺧</a>
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
                <a href="download_file.php/���Ű����.doc">���Ű����</a>
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
                <a href="download_file.php/������ط��Ͻн񸶻�.xls">������ط��Ͻн�</a>
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
            <td class='layoutg' id='start' rowspan='2'>
            ��������������˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��Ϳ�����񸶻�19ǯ����.xls">��Ϳ������
                <BR>
                �ʺ������������������������</a>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'19ǯ5���</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/������̶п�����.doc">������̶п�����</a>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
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
            <td class='layoutg' id='start' rowspan='1'>
            �������˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���������-����.xls">���������</a>
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
            <td class='layoutg' id='start' rowspan='3'>
            ������ʸ��˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                ����ʸ���ݴɰ����
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
                <a href="download_file.php/���ϲ��追������ͼ��ݣ���.doc">���ϲ��追����</a>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ����Ĺ̾�ξ��ϰ��̰������̾�Τߤξ��ϳѰ�
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                ��۸��°Ѿ�������
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
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
            <td class='layoutg' id='start' rowspan='4'>
            ����ư�֡��Х�������ž�֤�<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
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
                <a href="download_file.php/���Ѽֻ��Ѽ��Ѵꤤ.doc">���Ѽֻ��Ѽ��Ѵ�</a>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
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
                <a href="download_file.php/���ѻ��ֳ����Ѽֻ��Ѵ�.doc">���ѻ��ֳ����Ѽֻ��Ѵ�</a>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
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
                <a href="download_file.php/�ޥ������ζ�̳���Ѥο�����.doc">�ޥ������ζ�̳���Ѥο�����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='1'>
            ����Ҥ˴ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���ͽ��Ϣ��ɼ�������4.xls">���ͽ��Ϣ��ɼ</a>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'18ǯ3���</font></B>
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
