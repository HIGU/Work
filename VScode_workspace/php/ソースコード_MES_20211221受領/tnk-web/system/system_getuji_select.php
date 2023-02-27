<?php
//////////////////////////////////////////////////////////////////////////////
// ������衡��  (ǯ������ե�����)                                       //
// Copyright(C) 2002-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// History                                                                  //
// 2002/02/07 Created system_getuji_select.php                              //
// 2002/08/08  ���å����������ѹ�                                         //
// 2002/08/27 �ե졼���б�                                                  //
// 2002/12/03 �����ȥ�˥塼���ɲäΤ��� site_id=11 ���ɲ�                  //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
// 2004/07/20 Class MenuHeader ���ɲ�                                       //
// 2004/10/12 php4 �� php5 �إեå����Υ��ѹ�                             //
// 2006/04/19 style='overflow-y:hidden;' �ɲ�                               //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // TNK ������ function
require_once ('../MenuHeader.php');     // TNK ������ menu class
require_once ('../tnk_func.php');       // menu_bar() �ǻ���
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);    // ǧ�ڥ����å�3=admin�ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(99, 11);                // site_index=99(�����ƥ������˥塼) site_id=11(�����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�������� �����');
//////////// ɽ�������
$menu->set_caption('�����(ǯ�����)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��¹�', SYS . 'system_getuji.php');

if (isset($_SESSION['yyyymm'])) {
    $yyyymm = $_SESSION['yyyymm'];      // ���å�����ѿ��ǽ����
} else {
    $yyyymm = "";                       // �����(SESSION�ѿ��ˤ���Ͽ���Ƥ��ʤ����Ȥꤢ��������Ƥ���)
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
</head>
<body bgcolor='#ffffff' text='#000000' onLoad='document.ini_form.yyyymm.focus()' style='overflow-y:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <table height='92.7%' width='80%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� height��bottom�ΰ���Ĵ�� -->
        <tr><td valign='top'>
            <table>
                <tr><td><p><img src='../img/t_nitto_logo3.gif' width='348' height='83'></p></td></tr>
            </table>
            <table width='100%'>
                <tr><td align='center'><b><?= $menu->out_caption() ?></b></td></tr>
                <tr><td align='center'>
                <br>
                <img src='../img/tnk-turbine.gif' width='68' height='72'>
                </td></tr>
            </table>
            <table width='100%' cellspacing='0' cellpadding='3'>
                <form name='ini_form' action='<?= $menu->out_action('��¹�') ?>' method='post'>
                    <tr>
                        <td></td>
                        <td align='center'>
                            �������ǯ�����ꤷ�Ʋ�������
                            <input type='text' name='yyyymm' size='8' value='<?php echo($yyyymm); ?>' maxlength='6'>
                            <br>�㡧200202 ��2002ǯ02���
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td align='center'>
                            <input type='submit' name='system_getuji_select' value='�¹�' >
                        </td>
                    </tr>
                </form>
            </table>
        </td></tr>
        <tr><td valign='bottom'>
            <!-- <img src='../img/php4.gif' width='64' height='32'> -->
            <img src='../img/php5_logo.gif'>
            <img src='../img/linux.gif' width='74' height='32'>  
            <img src='../img/redhat.gif' width='96' height='32'>   
            <img src='../img/apache.gif' width='259' height='32'> 
            <img src='../img/pgsql.gif' width='160' height='32'>
        </td></tr>
        </table>
    </table>
    </center>
</body>
</html>
