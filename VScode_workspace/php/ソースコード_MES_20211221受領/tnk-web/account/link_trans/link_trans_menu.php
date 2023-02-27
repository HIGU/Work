<?php
//////////////////////////////////////////////////////////////////////////////
// Ϣ�������ɽ�ᥤ���˥塼 link_trans_menu.php                         //
// Copyright (C) 2017-2017 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2017/10/24 Created  link_trans_menu.php                                  //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                  // ǧ�ڥ����å�0=���̾� �����=TOP_MENU �����ȥ�̤����
////////////// ����������
//$menu->set_site(10, 4);                     // site_index=10(»�ץ�˥塼) site_id=4(��Ω��Ψ�׻�ɽ)

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ϣ�������ɽ�Ȳ�');
//////////// ɽ�������
$menu->set_caption('Ϣ�������ɽ �Ȳ� ��˥塼�����о�ǯ������λ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�ĸ���̳�Ȳ�',   PL . 'kessan/wage_rate/assemblyRate_groupMaster_Main.php');
$menu->set_action('�����Ȳ�',   PL . 'kessan/wage_rate/assemblyRate_capitalAsset_Main.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
//$menu->set_action('�����������Ͽ',   INDUST . 'material/materialCost_entry_old.php');

$request = new Request;
$session = new Session;

////////////// �꥿���󥢥ɥ쥹����
//$menu->set_RetUrl($session->get('wage_referer'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

//////////// �о�ǯ��Υ��å����ǡ�������
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} elseif(isset($_POST['wage_ym'])) {
    $wage_ym = $_POST['wage_ym'];
} elseif(isset($_SESSION['wage_ym'])) {
    $wage_ym = $_SESSION['wage_ym'];
} else {
    $wage_ym = date('Ym');           // ���å����ǡ������ʤ����ν����(����)
}

//////////// �о�ǯ��Υ��å����ǡ�������
if ($request->get('customer') != '') {
    $customer = $request->get('customer');
} elseif(isset($_POST['customer'])) {
    $customer = $_POST['customer'];
} elseif(isset($_SESSION['customer'])) {
    $customer = $_SESSION['customer'];
} else {
    $customer = '00001';           // ���å����ǡ������ʤ����ν����(00001:NK)
}
/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'wage_branch.php?wage_name=' + script_name;
    document.monthly_form.submit();
}
// -->
</script>

<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
    font-size:      14pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
</head>
<body>
    <center>
    <?php echo $menu->out_title_border()?>
        <form name='data_input' action='link_trans_branch.php' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' colspan='5' class='winbox'>
                    <span class='caption_font'>
                    	<?php echo $menu->out_caption() . "\n" ?>
                        <select name='wage_ym'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($wage_ym == $ym) {
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n", $ym, substr($ym, 0, 4),substr($ym, 4, 2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%sǯ%s��</option>\n", $ym, substr($ym, 0, 4),substr($ym, 4, 2));
                                }
                                if (substr($ym, 4, 2) != 01) {
                                    $ym--;      // ����Ǥʤ���з���ĥޥ��ʥ�
                                } else {        // ����ʤ�
                                    $ym = $ym - 100;    // ǯ��ޥ��ʥ�
                                    $ym = $ym + 11;     // ���11��ץ饹=12��ˤ���
                                }
                                if ($ym < 200010) {
                                    break;
                                }
                            }
                            ?>
                        </select>
                        <select name='customer'>
                            <option value="00001"<?php if($customer=="00001") echo("selected"); ?>>���칩��</option>
                            <option value="00004"<?php if($customer=="00004") echo("selected"); ?>>��ɥƥå�</option>
                            <option value="00005"<?php if($customer=="00005") echo("selected"); ?>>��ϣΣ�</option>
                            <option value="00101"<?php if($customer=="00101") echo("selected"); ?>>�Σˣɣ�</option>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                	<input class='pt10b' type='submit' name='service_name' value='�ĸ���̳�Ȳ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='�����Ȳ�'>
                </td>
             </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>

