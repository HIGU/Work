<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ����� �Ȳ� �������                                       //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2002/02/12 �������� dev_req_select.php                                   //
// 2002/08/09 register_globals = Off �б�                                   //
// 2002/08/27 �ե졼���б�                                                  //
// 2003/07/22 Opne Source Logo ����ɽ��                                     //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���          //
// 2004/07/17 MenuHeader()���饹�򿷵��������ǥ�����ǧ�����Υ��å�����  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // TNK ������ function
require_once ('../MenuHeader.php');     // TNK ������ menu class
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();               // ǧ�ڥ����å���ԤäƤ���

////////////// ����������
$menu->set_site(4, 1);                  // site_index=4(�ץ���೫ȯ) site_id=1(�����ξȲ�)
////////////// �����ȥ�����ץȤΥ��ɥ쥹����
// $menu->set_self($_SERVER['PHP_SELF']);
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(DEV_MENU);            // return address��ƽи���������ѹ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ץ���೫ȯ�����ξȲ�');
//////////// ɽ�������
$menu->set_caption('��ȯ�����ʾ������');

$dev_req_No      = @$_SESSION['s_dev_req_No'];          // �����ֹ�
$dev_req_sdate   = @$_SESSION['s_dev_req_sdate'];       // ������
$dev_req_edate   = @$_SESSION['s_dev_req_edate'];       // ��λ��
$dev_req_section = @$_SESSION['s_dev_req_section'];     // ��������
$dev_req_client  = @$_SESSION['s_dev_req_client'];      // �����
$dev_req_sort    = @$_SESSION['s_dev_req_sort'];        // �����Ⱦ��
$dev_req_kan     = @$_SESSION['s_dev_req_kan'];         // ��λ���

if ($dev_req_sort == '') {
    $dev_req_sort = '������';               // ����ͤλ���
}
if ($dev_req_kan == '') {
    $dev_req_kan = '����';                  //����ͤλ���
}

$_SESSION['s_rec_No'] = 0;  // ɽ���ѥ쥳����No��0�ˤ��롣

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
<script language='JavaScript' src='./dev_req.js'>
</script>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0'>
            <form action='edit_dev_req.php' method='post' onSubmit='return chk_dev_req_input(this)'>
                <table border='0'>
                    <tr><td><p><img src='<?php echo IMG ?>t_nitto_logo3.gif' width='348' height='83'></p></td></tr>
                </table>
                <table border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td align='center' class='caption_font'>
                            <?= $menu->out_caption() , "\n" ?>
                        </td>
                    </tr>
                    <tr>
                        <td align='center'>
                            <img src='<?php echo IMG ?>tnk-turbine_small.gif'>
                        </td>
                    </tr>
                </table>
                <table cellspacing='0' cellpadding='3' border='1' bordercolor='#003e7c'>
                    <tr>
                        <th>1</th>
                        <td align='left' nowrap>
                            �¤ӽ�����򤷤Ʋ�������<br>
                            <input type='radio' name='dev_req_sort' value='������'
                                <?php if($dev_req_sort=='������') echo('checked') ?>>��������
                            <input type='radio' name='dev_req_sort' value='��������'
                                <?php if($dev_req_sort=='��������') echo('checked') ?>>���������
                            <input type='radio' name='dev_req_sort' value='�����'
                                <?php if($dev_req_sort=='�����') echo('checked') ?>>����Խ�
                            <input type='radio' name='dev_req_sort' value='��λ��'
                                <?php if($dev_req_sort=='��λ��') echo('checked') ?>>��λ����
                            <input type='radio' name='dev_req_sort' value='��ȯ����'
                                <?php if($dev_req_sort=='��ȯ����') echo('checked') ?>>��ȯ������
                            <!--<br>-->
                            <input type='radio' name='dev_req_sort' value='�ֹ�'
                                <?php if($dev_req_sort=='�ֹ�') echo('checked') ?>>�����ֹ��
                        </td>
                    </tr>
                    <tr>
                        <th>2</th>
                        <td align='left'>
                            ����ԤμҰ�No����
                            <input type='text' name='dev_req_client' size='7' value='<?php echo($dev_req_client); ?>' maxlength='6'>
                            ���������
                        </td>
                    </tr>
                    <tr>
                        <th>3</th>
                        <td align='left'>
                            ���������ϰϻ���
                            <input type='text' name='dev_req_sdate' size='9' value='<?php echo($dev_req_sdate); ?>' maxlength='8'>
                            ��
                            <input type='text' name='dev_req_edate' size='9' value='<?php echo($dev_req_edate); ?>' maxlength='8'>
                            ���������
                        </td>
                    </tr>
                    <tr>
                        <th>4</th>
                        <td align='left'>
                            ����No(����No)����
                            <input type='text' name='dev_req_No' size='5' value='<?php echo($dev_req_No); ?>' maxlength='5'>
                            ���������
                        </td>
                    </tr>
                    <tr>
                        <th>5</th>
                        <td align='left' nowrap>
                            ��λ��ʬ�����򤷤Ʋ�������<br>
                            <input type='radio' name='dev_req_kan' value='����'
                                <?php if($dev_req_kan=='����') echo('checked') ?>>�����о�
                            <input type='radio' name='dev_req_kan' value='̤��λ'
                                <?php if($dev_req_kan=='̤��λ') echo('checked') ?>>̤��λʬ
                            <input type='radio' name='dev_req_kan' value='��λ'
                                <?php if($dev_req_kan=='��λ') echo('checked') ?>>��λʬ
                            <input type='radio' name='dev_req_kan' value='��α¾'
                                <?php if($dev_req_kan=='��α¾') echo('checked') ?>>��α����¾ʬ
                        </td>
                    </tr>
                </table>
                <div align='center'><input type='submit' name='view_dev_req' value='�¹�' ></div>
            </form>
        </table>
    </center>
</body>
</html>
