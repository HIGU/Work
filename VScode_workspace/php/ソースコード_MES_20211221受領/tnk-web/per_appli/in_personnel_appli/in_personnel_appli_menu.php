<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼�ʼ��� �ͻ��˴ؤ����ϽС�                            //
// Copyright (C) 2014-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_personnel_appli_menu.php                          //
// 2015/02/05 �Ƽ︶����ɲ�                                                //
// 2015/02/17 �����Υ���ڤ������                                        //
// 2015/02/26 ����Ϥε�������ɲä�����                                    //
// 2015/03/05 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���            //
// 2015/04/09 ��������ѿ�������ɲá�����ϤȤΥ��åȤ��                  //
// 2015/06/17 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���            //
// 2016/01/13 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(16/01)     //
//            ��Ϳ�����ԤΡ�������28ǯ�٤ع���                            //
// 2016/02/10 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(16/02)     //
// 2016/04/01 ����ϡ������㡢����ϡ���������åȤ�����16/04��           //
// 2016/04/05 �ͻ������ѹ��ǡ��������Ȥ�PDF���ѹ�(B4��A4�˽̾��Ѥ�)         //
// 2016/04/22 ��������ѿ���������                                        //
// 2017/05/09 ����Ϥ�17.5.9�Ǥ˲���                                        //
// 2017/06/05 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(17/06)     //
// 2017/08/10 ����ϡ���������åȤ�17.5.9�Ǥ�(����Ϥ��̥����Ȥˤ���)      //
// 2018/07/09 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(18/06)     //
// 2018/10/17 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(18/10)     //
// 2018/11/14 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(18/11)     //
// 2019/04/11 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(19/04)     //
// 2019/05/13 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(19/05)     //
// 2019/07/22 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(19/07)     //
// 2019/09/06 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(19/09) ����//
// 2019/10/07 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(19/10) ����//
// 2020/01/10 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(20/01) ��ë//
// 2020/03/04 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(20/03) ����//
// 2020/04/06 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(20/04) ����//
// 2020/07/03 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(20/07) ����//
// 2020/09/04 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(20/09) ����//
// 2021/01/08 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(21/01) ����//
// 2021/02/04 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(21/02) ����//
// 2021/03/08 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(21/03) ����//
// 2021/03/11 ���鷱���ѹ���߿����� ������ɲ�                         ����//
// 2021/03/24 ��Ū��ʼ��� ���������� �� ������ڤӾ���⿽����(�ѹ�) ����//
// 2021/04/08 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(21/04) ����//
// 2021/07/05 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(21/07) ����//
// 2021/11/04 �ޥ������̶м������ѹ����ϡ��ѡ����̶о����Ϥβ���(21/11) ����//
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
$menu->set_title('�ϽС��������˥塼�ʼ��� �ͻ���');
//////////// ɽ�������
$menu->set_caption('�ϽС��������˥塼�ʼ��� �ͻ���');

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
    <BR>
    ������ź�ս���ˤʤ�Τǡ������κݤ˼̤���ź�դ��Ʋ�������
    <BR>
    �������ˤʤ���������ϡ���̳�ݤޤǤ�Ϣ��������
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutg' id='start' align='center'>
            ����
            </td>
            <td class='layoutg' id='start'>
            ����<BR>No.
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
            <td class='layoutg' id='start' rowspan='5' nowrap>
            �����դ˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0101
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/17.5.9����ϡʲ���2��.xls">�����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/20160401����ϵ�����.pdf">������</a>�򻲹ͤ˵������Ʋ�������<BR><B><font color='red'>'17/05����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0102
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0103
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/����ֳ���Ⱦ�����.xls">����ֳ���Ⱦ�����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0104
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/121018���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
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
            <td class='layoutg' id='start'>
            0105
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/������ʲ�����.xls">��������ѿ�����</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/17.5.9����ϡʲ���2��.xls">����ϡ���������å�</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'17/05����</font></B>��������ѿ�����<BR>����ϡ���������å�
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4' nowrap>
            ����Ϳ�˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0201
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/��Ϳ���������ɲá��ѹ��ϸ���.xls">��Ϳ�����߸����ɲá��ѹ���</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0202
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/21.11@164�ώ������̶м������ѹ�����.xlsx">�ޥ������̶м������ѹ�����</a><BR>
                �������������������ʼҰ��ѡ�
                <BR>
                <a href="download_file.php/21.11@164�ѡ����̶о�����.xlsx">�ѡ����̶о�����</a><BR>
                �������������������ʥѡ��ȼҰ��ѡ�
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            ���ñ���ѹ��θ�ľ���ˤ�ꡢ��ۤ��Ѥ��ޤ���<B><font color='red'>'21/11����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0203
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/�˸��ض�̳���ӵ���ɽ.xls">����ض�̳���ӵ���ɽ</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0204
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/���е��ľ�.xls">���ѡ����ѳ��е��ľ�</a>
                -->
                <a href="download_file.php/20140901������ʸ�����.xls">������ʸ�����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='7' nowrap>
            �������ѹ��˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0301
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/�����ѹ���ȼ����н������.doc">��н������ɽ</a>
            </td>
            <td class='layout' id='start'>
            �Ϥ���ˤ��ɤ߲�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0302
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/�ͻ������ѹ��ǡ���������.pdf">�ͻ������ѹ��ǡ���������</a>
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
            0303
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��Ϳ�����Ԥ����ܹ��������.pdf">��Ϳ�����Ԥ����ܹ�����<BR>�ʰ�ư�˿����</a>
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�ݤؿ��ФƲ�������<B><font color='red'>'16/01����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0304
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/21.11@164�ώ������̶м������ѹ�����.xlsx">�ޥ������̶м������ѹ�����</a><BR>
                �������������������ʼҰ��ѡ�
                <BR>
                <a href="download_file.php/21.11@164�ѡ����̶о�����.xlsx">�ѡ����̶о�����</a><BR>
                �������������������ʥѡ��ȼҰ��ѡ�
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            ���ñ���ѹ��θ�ľ���ˤ�ꡢ��ۤ��Ѥ��ޤ���<B><font color='red'>'21/11����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0305
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/������ �������.doc">������ �������</a>
            </td>
            <td class='layout' id='start'>
            ������ �����ξ������
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <!--
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0306
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <!--
                <a href="download_file.php/�������󥿡������˾������.doc">�������󥿡������˾������</a>
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
            <td class='layoutg' id='start'>
            0306
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/�ޥ������̶п�����1.doc">�ޥ������̶з���־�<BR>���ѵ��Ŀ�����</a>
            </td>
            <td class='layout' id='start'>
            �ޥ������̶мԤ����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0307
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                ���۶��Ԥ�ǯ���Ģ<BR>���ʴ���ǯ���ֹ����ν�ˤμ̤�
            </td>
            <td class='layout' id='start'>
            �۶��Ԥ����ܤ��Ƥ���������
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/��̱ǯ����3�����ݸ��Խ����ѹ���.pdf">��̱ǯ���裳�����ݸ��Խ����ѹ���</a>
            <BR>
            <a href="download_file.php/���ݸ��Խ����ѹ���.pdf">����ǯ���ݸ� ���ݸ��Խ����ѹ���</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='11' nowrap>
            ���뺧�˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0401
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/�뺧��ȼ����н������.doc">��н������ɽ</a>
            </td>
            <td class='layout' id='start'>
            �Ϥ���ˤ��ɤ߲�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0402
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/�ͻ������ѹ��ǡ���������.pdf">�ͻ������ѹ��ǡ���������</a>
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
            0403
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��Ϳ�����Ԥ����ܹ��������.pdf">��Ϳ�����Ԥ����ܹ�����<BR>�ʰ�ư�˿����</a>
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�ݤؿ��ФƲ�������<B><font color='red'>'16/01����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0404
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/���ݸ������ܼ԰�ư��.pdf">���ݸ������ܼԡʰ�ư����</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            �ѻ�ϡ�����ʣ�̤������ѻ�Ǥ�����̳�ݤؿ��ФƲ�������
            <BR>
            <a href="download_file.php/�����ܼ԰�ư�ϡʵ������.pdf">������</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0405
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��̱ǯ����3�����ݸ��Ի�ʼ�����.pdf">��̱ǯ���裳�����ݸ��Ի�ʼ�����</a>
                <BR>
                ���۶��Ԥ�ǯ���Ģ<BR>���ʴ���ǯ���ֹ����ν�ˤμ̤�
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
            0406
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                �����ݸ����ݸ���Υ��ɼ�ݣ����ݣ�
            </td>
            <td class='layout' id='start'>
            �����ݸ��β����Ԥ��ä��������ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            �����ݸ�����������ܤ�����뤳�ȤϽ���ޤ���
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0407
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                �����������������
            </td>
            <td class='layout' id='start'>
            ����ħ��ɼ�ޤ�������Ǿ�����
            <BR>
            ǯ�����Ԥϡ�ǯ��������ν�μ̤�
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0408
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                ���Ҳ��ݸ��Ӽ�������
            </td>
            <td class='layout' id='start'>
            �Ҳ��ݸ��˲������Ƥ�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0409
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/�Ļ��ط��Ͻн񡡸���.xls">�Ļ��ط��Ͻн�</a>
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            ���ˤ��⤬�ٵ뤵��ޤ���
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0410
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��̾�ѹ���.pdf">���ݸ�������ǯ���ݸ� ��̾�ѹ���</a>
                <BR>
                ������̾�ν�̱ɼ��ǯ���Ģ
            </td>
            <td class='layout' id='start'>
            ��̾���Ѥ����Τ���Сʸ��ܣ����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0411
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                ������¾����̾�ѹ��������ѹ���<BR>���ؤ����Ͻ�
            </td>
            <td class='layout' id='start'>
            ����̾�ѹ��佻���ѹ������ä����
            </td>
            <td class='layout' id='start'>
            ����̾�ѹ��佻���ѹ���ȼ�����ϡ����ӽ����ѹ��˴ؤ����Ͻн���Ϳ�����߸����ѹ��Ϥ�ɬ�פˤʤ�ޤ��Τǡ���̳�ݤޤǤ�Ϣ��������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='7' nowrap>
            ���л��˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0501
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/�л���ȼ����н������.doc">��н������ɽ</a>
            </td>
            <td class='layout' id='start'>
            �Ϥ���ˤ��ɤ߲�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0502
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/�ͻ������ѹ��ǡ���������.pdf">�ͻ������ѹ��ǡ���������</a>
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
            0503
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��Ϳ�����Ԥ����ܹ��������.pdf">��Ϳ�����Ԥ����ܹ�����<BR>�ʰ�ư�˿����</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�ݤؿ��ФƲ�������<B><font color='red'>'16/01����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0504
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/���ݸ������ܼ԰�ư��.pdf">���ݸ������ܼԡʰ�ư����</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            �ѻ�ϡ�����ʣ�̤������ѻ�Ǥ�����̳�ݤؿ��ФƲ�������
            <BR>
            <a href="download_file.php/�����ܼ԰�ư�ϡʵ������.pdf">������</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0505
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/�л��������������.pdf">�л��������������</a>
            </td>
            <td class='layout' id='start'>
            ���ݸ��Ԥޤ��������۶��Ԥνл��ξ��Τ����
            </td>
            <td class='layout' id='start'>
            ��դξ�����ɬ�פǤ���
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0506
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/�л������������.pdf">�л������⡦�л�������ö������</a>
            </td>
            <td class='layout' id='start'>
            ���ݸ��Ԥνл��ξ��Τ����
            </td>
            <td class='layout' id='start'>
            ��դξ�����ɬ�פǤ���
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0507
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/�Ļ��ط��Ͻн񡡸���.xls">�Ļ��ط��Ͻн�</a>
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            ���ˤ��⤬�ٵ뤵��ޤ���
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='8' nowrap>
            �����ܼԤ����ä�<BR>���ؤ����Ͻ�
            <BR><B>
            ���ʷ뺧���л�<BR>�����ʳ��ξ���
            </B>
            </td>
            <td class='layoutg' id='start'>
            0601
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/�������ä�ȼ����н������.doc">��н������ɽ</a>
            </td>
            <td class='layout' id='start'>
            �Ϥ���ˤ��ɤ߲�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0602
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/�ͻ������ѹ��ǡ���������.pdf">�ͻ������ѹ��ǡ���������</a>
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
            0603
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��Ϳ�����Ԥ����ܹ��������.pdf">��Ϳ�����Ԥ����ܹ�����<BR>�ʰ�ư�˿����</a>
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�ݤؿ��ФƲ�������<B><font color='red'>'16/01����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0604
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/���ݸ������ܼ԰�ư��.pdf">���ݸ������ܼԡʰ�ư����</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            �ѻ�ϡ�����ʣ�̤������ѻ�Ǥ�����̳�ݤؿ��ФƲ�������
            <BR>
            <a href="download_file.php/���ܼ԰�ư�ϡʵ������.pdf">������</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0605
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��̱ǯ����3�����ݸ��Ի�ʼ�����.pdf">��̱ǯ���裳�����ݸ��Ի�ʼ�����</a>
                <BR>
                ���۶��Ԥ�ǯ���Ģ<BR>���ʴ���ǯ���ֹ����ν�ˤμ̤�
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
            0606
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                �����ݸ����ݸ���Υ��ɼ�ݣ����ݣ�
            </td>
            <td class='layout' id='start'>
            �����ݸ��β����Ԥ��ä��������ܤ�����Τ����
            </td>
            <td class='layout' id='start'>
            �����ݸ�����������ܤ�����뤳�ȤϽ���ޤ���
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0607
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                �����������������
            </td>
            <td class='layout' id='start'>
            ����ħ��ɼ�ޤ�������Ǿ�����
            <BR>
            ǯ�����Ԥϡ�ǯ��������ν�μ̤�
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0608
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                ���Ҳ��ݸ��Ӽ�������
            </td>
            <td class='layout' id='start'>
            �Ҳ��ݸ��˲������Ƥ�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='5' nowrap>
            �����ܼԤθ�����<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0701
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/���ܸ�����ȼ����н������.doc">��н������ɽ</a>
            </td>
            <td class='layout' id='start'>
            �Ϥ���ˤ��ɤ߲�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0702
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/�ͻ������ѹ��ǡ���������.pdf">�ͻ������ѹ��ǡ���������</a>
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
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��Ϳ�����Ԥ����ܹ��������.pdf">��Ϳ�����Ԥ����ܹ�����<BR>�ʰ�ư�˿����</a>
            </td>
            <td class='layout' id='start'>
            �������
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�ݤؿ��ФƲ�������<B><font color='red'>'16/01����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0704
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/���ݸ������ܼ԰�ư��.pdf">���ݸ������ܼԡʰ�ư����</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ��鳰���������
            </td>
            <td class='layout' id='start'>
            �ѻ�ϡ�����ʣ�̤������ѻ�Ǥ�����̳�ݤؿ��ФƲ�������
            <BR>
            <a href="download_file.php/���ܼ԰�ư�ϡʵ������.pdf">������</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0705
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                ���ݸ����ݸ��Ծ�
            </td>
            <td class='layout' id='start'>
            ���ܤ��鳰�������ݸ��ڤ��ֵѤ��Ʋ�������
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='6' nowrap>
            ��Ĥ�֤˴ؤ���<BR>���Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0801
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/Ĥ�ִط���ȼ����н������.doc">��н������ɽ</a>
            </td>
            <td class='layout' id='start'>
            �Ϥ���ˤ��ɤ߲�����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0802
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/�ͻ������ѹ��ǡ���������.pdf">�ͻ������ѹ��ǡ���������</a>
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
            0803
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/��Ϳ�����Ԥ����ܹ��������.pdf">��Ϳ�����Ԥ����ܹ�����<BR>�ʰ�ư�˿����</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ��Ƥ������Τ����
            </td>
            <td class='layout' id='start'>
            �ѻ����̳�ݤؿ��ФƲ�������<B><font color='red'>'16/01����</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0804
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/�·翽���.doc">�·翽���</a>
                -->
                <a href="download_file.php/���ݸ������ܼ԰�ư��.pdf">���ݸ������ܼԡʰ�ư����</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ��Ƥ������Τ����
            </td>
            <td class='layout' id='start'>
            �ѻ�ϡ�����ʣ�̤������ѻ�Ǥ�����̳�ݤؿ��ФƲ�������
            <BR>
            <a href="download_file.php/���ܼ԰�ư�ϡʵ������.pdf">������</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0805
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                <a href="download_file.php/����������������ö������.pdf">����������������ö������</a>
            </td>
            <td class='layout' id='start'>
            ���ܤ��Ƥ������Τ����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0806
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/����ֳ���Ⱦ�����.pdf">����ֳ���Ⱦ�����</a>
                -->
                ���ݸ����ݸ��Ծ�
            </td>
            <td class='layout' id='start'>
            ���ܤ��Ƥ������Τ����
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='1' nowrap>
            ���ޥ������ѹ���<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutg' id='start'>
            0901
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/�ޥ������̶п�����1.doc">�ޥ������̶з���־�<BR>���ѵ��Ŀ�����</a>
            </td>
            <td class='layout' id='start'>
            �ޥ������̶мԤΤ����
            <BR>
            ����ξ��Ǥ���ݸ��ѹ����Ϻ����
            </td>
            <td class='layout' id='start'>
            �������ѹ������ϼָ��ڵڤ�Ǥ�ݼ̤���ź��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='1' nowrap>
            ����Ū��ʼ�����<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutg' id='start'>
            2101
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/���������񡡷󡡷�����ڤӾ���⿽����ʿ���.doc">��Ū��ʼ��� ���������� ��<BR>����������ڤӾ���⿽����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��ʼ����Τ���ιֽ���������֤���������Ф��Ʋ�������
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='2' nowrap>
            �����鷱����<BR>���ؤ����Ͻ�
            </td>
            <td class='layoutg' id='start'>
            3101
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004����ϡʲ���2��.xls">�����</a>
                -->
                <a href="download_file.php/���鷱���»����񡡸���.DOC">���鷱���»�����</a>
            </td>
            <td class='layout' id='start'>
            ��
            </td>
            <td class='layout' id='start'>
            ��
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            3102
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/418-CUA-011_���鷱���ѹ�����߿������2016.07.25��.DOC">���鷱���ѹ���߿�����</a>
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
