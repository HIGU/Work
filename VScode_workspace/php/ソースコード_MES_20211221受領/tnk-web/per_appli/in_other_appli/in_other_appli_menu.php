<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�ʼ��� ����¾���ϽС�                                //
// Copyright (C) 2014-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_other_appli.php                                   //
// 2015/02/06 �����ο�������ɲ�                                            //
// 2015/06/17 ���񡦺ҳ�ȯ��������ɲ�                                  //
// 2016/02/22 �ѥ�ݥ���ȥƥ�ץ졼�Ȥ��ɲ�                              //
// 2016/04/22 ����������2016ǯ�٤ع���                                    //
// 2016/06/08 �ۡ���ڡ����Ǻܰ������ɲ�                                  //
// 2016/07/07 ���������ϫ�ҡ�ʪ»�����ѡˤ򹹿�                          //
// 2017/02/08 ������ε�������ɲ�                                          //
// 2017/06/09 �ѥ�ݥ���ȥƥ�ץ졼�Ȥλ�����ˡ���ɲ�                    //
// 2017/06/20 �ȵĽ񸶻���ɲ�                                              //
// 2017/06/23 �ƥ�ץ졼�Ȥ򹹿�                                            //
// 2017/06/30 �ȵĽ񸶻�򹹿� �����ɲ�                                     //
// 2017/07/18 �ƥ�ץ졼�Ȥ򹹿�(201707)                                    //
// 2017/11/09 ����������18���˹���                                        //
// 2018/04/13 ������ΰ������п������18ǯ4���Ǥع���                       //
// 2019/05/29 ������ΰ������п������19ǯ5���Ǥع���                       //
// 2019/07/17 ��������ʼ�ξ�����ѡˤ��ɲ�                                //
// 2020/07/27 ����������21���˹���                                        //
// 2020/09/24 ����¾�˥ƥ�ץ졼�Ȥ򤤤��Ĥ��ɲ�                            //
// 2020/11/10 22���Υ����������ɲ�                                        //
// 2021/03/15 22���Υ��������򹹿�                                   ���� //
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
$menu->set_title('�ϽС��������˥塼�ʼ��� ����¾��');
//////////// ɽ�������
$menu->set_caption('�ϽС��������˥塼�ʼ��� ����¾��');

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
            <td class='layoutg' id='start'>
            ���ȵĽ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�ȵĽ񸶻�20210528.xls">�ȵĽ�</a>
            </td>
            <td class='layout' id='start'>
            ��¸����ľ�����Ϥ��Ƥ���������
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'21ǯ5��28����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ������˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/����ٶȿ�����.doc">����ٶȿ�����</a>
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
                <a href="download_file.php/����ٶȱ�Ĺû�̿��н�.xls">����ٶȱ�Ĺ��û�̿��н�</a>
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
                <a href="download_file.php/���û���ֶ�̳������.doc">���û���ֶ�̳������</a>
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
                <a href="download_file.php/130501�ҤδǸ�ٲ������.xls">�ҤδǸ�Τ���εٲ������</a>
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
            �����˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���ٶȿ�����.doc">���ٶȿ�����</a>
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
                <a href="download_file.php/���ٶȱ�Ĺû�̿��н�.xls">���ٶȱ�Ĺ��û�̿��н�</a>
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
                <a href="download_file.php/���û���ֶ�̳������.doc">���û���ֶ�̳������</a>
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
                <a href="download_file.php/��²�β��Τ���εٲ������.doc">��²�β��Τ���εٲ������</a>
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
            ����̳���Ѥ���<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��̳���Ѵ�λ����.doc">���Ѵ�λ����</a>
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
                <a href="download_file.php/��۸��°����Ѿ����Ŀ�����(����).doc">��۸��°����Ѿ����Ŀ�����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='7'>
            ��ͻ�����դ�<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�����������ǰ��.doc">�����������ǰ��</a>
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
                <a href="download_file.php/���������������.doc">���������������</a>
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
                <a href="download_file.php/��������Ѿڽ�.xls">��������Ѿڽ�</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/���Ѿڽ�����ʽ������.pdf">������</a>
            </td>
            <td class='layout' id='start'>
            �������������ǧ�塢��Ф��Ƥ���������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���������������.doc">���������������</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/�������������������.pdf">������</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��������Ѿڽ�.doc">��������Ѿڽ�</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/���Ѿڽ�����ʶ������.pdf">������</a>
            </td>
            <td class='layout' id='start'>
            �������������ǧ�塢��Ф��Ƥ���������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�������ն���������񸶻�.xls">�������ն����������</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/�������ն��������������.pdf">������</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�������ն���Ѿڽ�.xls">�������ն���Ѿڽ�</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/���Ѿڽ�����ʰ������ն��.pdf">������</a>
            </td>
            <td class='layout' id='start'>
            �������������ǧ�塢��Ф��Ƥ���������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='3'>
            ���������˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���������ʪ»�����ѡˡ�����.xls">���������ʪ»�����ѡ�</a>
            </td>
            <td class='layout' id='start'>
            ʪ»�ˤ����������Ԥ�����<BR>���������ȹ�碌����Ф��Ʋ�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���������ϫ���ѡˡ�����.xls">���������ϫ���ѡ�</a>
            </td>
            <td class='layout' id='start'>
            ϫ��ȯ����®�䤫����Ф��Ʋ�������
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��������ʼ�ξ�����ѡˡ�����.xls">��������ʼ�ξ�����ѡ�</a>
            </td>
            <td class='layout' id='start'>
            ����ȯ����®�䤫����Ф��Ʋ�������
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ������ԥ塼����<BR>����Ϣ���Ͻ�
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
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�ۡ���ڡ����Ǻܰ���� ��-HP-01-v01.pdf">�ۡ���ڡ����Ǻܰ����</a>
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
            �����ݸ��ȹ��<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���ݸ����ٳ�Ŭ��ǧ�꿽����.pdf">���ݸ����ٳ�Ŭ��ǧ�꿽����</a>
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
                <a href="download_file.php/���������ٵ뿽����.pdf">�ܿ͡���²���������ٵ뿽����</a>
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�˿��ФƲ�������
            <BR>
            �μ��ڤμ̤���ź�դ��Ʋ�������
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���¼����������.pdf">���¼����������</a>
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�˿��ФƲ�������
            <BR>
            ��դξ�����ɬ�פǤ���
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ��������˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/���������񿽹��� .xls">���񿽹���</a>
            </td>
            <td class='layout' id='start'>
            ǯ���󣷷�
            <BR>
            ����1,000�ߡ���Ϳ��10��ޤ�
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�ٻߡ��Ƴ��������ѹ�������.xls">�ٻߡ��Ƴ��������ѹ�������</a>
            </td>
            <td class='layout' id='start'>
            ǯ���󣷷�
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/19.5�������п�����.xlsx">�������п�����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            <font color='red'><B>19ǯ5���</B></font>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/����������Ͻн�.xls">�����</a>
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
            ��������˴ؤ���<BR>������
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/�۵�Ϣ����ʿ�����Ͽ���ѹ��ϡ�.xls">�᡼�륢�ɥ쥹�ѹ�����</a>
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
                <a href="download_file.php/����������.ppt">���ݳ�ǧ���̡������ˡ</a>
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
                <a href="download_file.php/�ܿ;������.ppt">�ܿ;�����̡������ˡ</a>
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
                <a href="download_file.php/���ǥ᡼���к�.pdf">���ǥ᡼���к���ˡ</a>
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
            ����������
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/��22���������칩�｢ϫ��������.xlsx">��22��<BR>�������칩�參������</a>
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
                <a href="download_file.php/��23���������칩�｢ϫ��������.xlsx">��23��<BR>�������칩�參������</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <!--
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/2020��21�� ��̩ʸ������������.xlsx">��21��<BR>��̩ʸ������������</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        -->
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='5'>
            ������¾
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/TNK_J_201707.potx">�ѥ�ݥ����<BR>�ƥ�ץ졼��</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/�ƥ�ץ졼�Ȥλ�����ˡ.xls">������ˡ</a>
            </td>
            <td class='layout' id='start'>
            <font color='red'><B>������ˡ��褯�ɤ�Ǥ����Ѥ���������</B></font>
            <BR>
            <font color='red'><B>17ǯ7���</B></font>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                TNK FaxSheet
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/1.TNK_FaxSheet.xlsx">Excel</a>
            ����
            <a href="download_file.php/1.TNK_FaxSheet.dotx">Word</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/2.TNK_�������հ���_2017.dotx">TNK�������հ���</a>
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
                TNK�쥿���إå�
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/3.TNK��ʸ�쥿���إå�_2017.dotx">��ʸ</a>
            ����
            <a href="download_file.php/3.TNK��ʸ�쥿���إå�_2017.dotx">��ʸ</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                �������칩�ﳰ���ۿ���
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/4.�������칩�ﳰ���ۿ��ѡ���ʸ��.dotx">��ʸ</a>
            ����
            <a href="download_file.php/4.�������칩�ﳰ���ۿ��ѡʱ�ʸ��.dotx">��ʸ</a>
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
