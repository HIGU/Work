<?php
//////////////////////////////////////////////////////////////////////////////
// ���⵬����˥塼 ����������Ϣ  company regulation                        //
// Copyright (C) 2010-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/06/15 Created  regulation_inter_menu.php                            //
// 2010/07/06 �������������ƥ�δ������ˤ�����2010/06/22�ա�              //
// 2012/10/25 �����������������2011/11/22�ա�                            //
//            ���������ꡦ�̻棱������2012/09/03�ա�                    //
// 2012/10/25 ����ץ饤���󥹵��������2013/04/01�ա�                    //
//            ���������������2013/04/01�ա�                            //
//            ���������ꡦ�̻棱������2013/04/01�ա�                    //
// 2013/07/03 �����������������2013/04/01�ա�                            //
//            �����������ˤ��ä��������������ƥ�ξ���(�ȿ�)���̤�ɽ��      //
// 2013/09/25 �������������̻棱������2013/09/01�ա�                    //
// 2013/10/21 �������������̻棱������2013/10/01�ա�                    //
// 2014/01/23 ����ץ饤���󥹵�����������������NK�ȶ��ѵ����Ȥʤ�        //
//            �̾ﵬ�����Ȥ߹�����ΤǤ����餫��Ϻ��                      //
// 2015/03/31 ����������������ܵ����ذ�ư(4/1����)                         //
// 2015/08/07 �����ݾ�͢�д��������������ݾ�͢�д���������§����ź��        //
//            2015/08/03�դع���                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
// require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
if (isset($_SESSION['REGU_Auth'])) {
    $menu = new MenuHeader(-1);             // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
} else {
    $menu = new MenuHeader(0);              // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
}

////////////// ����������
$menu->set_site(INDEX_REGU, 0);            // site_index=INDEX_REGU(���⵬����˥塼) site_id=0(�ʤ�)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl('../regulation_menu.php');                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���⵬�� �Ȳ� ��˥塼 ����������Ϣ����');
//////////// ɽ�������
$menu->set_caption('�ʲ��ε������ Acrobat Reader 5 �ʾ�Ǳ�������ޤ���');
$uniq = 'ID=' . uniqid('regu');

$today = date('Ymd');
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
<script type='text/javascript' language='JavaScript' src='regulation_inter.js?id=<?= $uniq ?>'></script>
<link rel='stylesheet' href='regulation_inter.css?id=<?= $uniq ?>' type='text/css' media='screen'>
</head>
<body onLoad='Regu.set_focus(document.getElementById("start", ""))' style='overflow:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
    <div class='pt12b'><?php echo $menu->out_caption()?></div>
    <div class='pt12b'>&nbsp;</div>
    <table class='layout'>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kihon10.06.22.pdf", "")'
                onMouseover="status='�������������ƥ�δ������ˤ�ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������������ƥ�δ������ˤ�ɽ�����ޤ���'
            >�������������ƥ�δ�������</a>
        </td>
        <?php
        //if ($_SESSION['User_ID'] == '300144') {
        if ($today >= 20150401) {
        ?>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu15.08.03.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������ɽ�����ޤ���'
            >�����ݾ�͢�д�������</a>
        </td>
        <?php
        } else {
        ?>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kitei13.04.01.pdf", "")'
                onMouseover="status='��������������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='��������������ɽ�����ޤ���'
            >������������</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kitei_jyokyo13.04.01.pdf", "")'
                onMouseover="status='�������������ƥ�ξ������ȿ��ˤ�ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�������������ƥ�ξ������ȿ��ˤ�ɽ�����ޤ���'
            >����(�ȿ�)</a>
        </td>
        <?php
        }
        ?>
    </tr>
    <?php
    //if ($_SESSION['User_ID'] == '300144') {
    if ($today >= 20150401) {
    ?>
    <?php
    } else {
    ?>
    <tr class='layout'>
        <td class='layout' colspan='2'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu07.04.01.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������ɽ�����ޤ���'
            >�����ݾ�͢�д�������</a>
        </td>
    </tr>
    <?php
    }
    if ($today >= 20150803) {
    ?>
    <tr class='layout'>
        <td class='layout' colspan='2' style='border-bottom:none;'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai15.08.03.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§��ɽ�����ޤ���'
            >�����ݾ�͢�д���������§</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi1-12_15.08.03.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����������ɽ�����ޤ���'
            >��ź��������</a>
        </td>
    </tr>
    <?php
    } else {
    ?>
    <tr class='layout'>
        <td class='layout' colspan='2' style='border-bottom:none;'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai07.04.01.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§��ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§��ɽ�����ޤ���'
            >�����ݾ�͢�д���������§</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi1_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi2_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi3_07.04.20.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi4_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi5_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' colspan='2' style='border-top:none;'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi6_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi7_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi8_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi9_07.04.12.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź����ɽ�����ޤ���'
            >��ź��</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi10_07.04.13.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź������ɽ�����ޤ���'
            >��ź����</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi11_07.04.13.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź������ɽ�����ޤ���'
            >��ź����</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_yushutsu_sai_beshi12_07.04.13.pdf", "")'
                onMouseover="status='�����ݾ�͢�д���������§����ź������ɽ�����ޤ���';return true;"
                onMouseout="status=''"
                title='�����ݾ�͢�д���������§����ź������ɽ�����ޤ���'
            >��ź����</a>
        </td>
    </tr>
    <?php
    }
    ?>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
