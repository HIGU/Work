<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω��Ψ �Ƽ�ǡ������ϥᥤ���˥塼 wage_various_data_input_menu.php   //
// Copyright (C) 2006-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2006/08/22 Created  wage_various_data_input_menu                         //
// 2007/09/25 ���פ�$Returl����                                           //
// 2007/10/05 �ե����ooya���������٥��ɥ쥹���ѹ�                        //
// 2007/10/19 E_ALL��E_STRICT�آ������Ȳ�                                 //
//            ���硼�ȥ�����ɸ�ॿ��(�侩��)���ѹ�                          //
// 2007/10/20 ,�θ�˥��ڡ������ʤ��Ľ������                               //
// 2007/10/22 <!DOCTYPE HTML �����˲��Ԥ�����Τ���                       //
// 2007/10/24 ob_end_flush()������Τ�ob_start()�ʤ��Τ��ɲ�                //
// 2007/12/13 �о�ǯ��μ����Ϥ��Ѥ�$request������                          //
// 2007/12/29 �Ƽ��˥塼�򿷥ץ����إ���ѹ�                        //
// 2008/01/09 Session���ɲ���äƤ����������դ��ݻ�����ʤ��Τ���         //
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
$menu->set_title('�Ƽ�ǡ����Խ�');
//////////// ɽ�������
$menu->set_caption('�Ƽ�ǡ��� �Խ� ��˥塼�����о�ǯ��λ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('���롼�ץޥ������Խ�',   PL . 'kessan/wage_rate/assemblyRate_groupMaster_Main.php');
$menu->set_action('�������Ģ�Խ�',   PL . 'kessan/wage_rate/assemblyRate_capitalAsset_Main.php');
$menu->set_action('�꡼������Ģ�Խ�',     PL . 'kessan/wage_rate/assemblyRate_leasedAsset_Main.php');
$menu->set_action('��������ǡ����Խ�',     PL . 'kessan/wage_rate/assemblyRate_machineWork_Main.php');
$menu->set_action('���ȥǡ����Խ�',       PL . 'kessan/wage_rate/assemblyRate_manRate_Main.php');
$menu->set_action('����Ψ�׻��ǡ����Խ�',       PL . 'kessan/wage_rate/assemblyRate_costAllocation_Main.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
//$menu->set_action('�����������Ͽ',   INDUST . 'material/materialCost_entry_old.php');

$request = new Request;
$session = new Session;

////////////// �꥿���󥢥ɥ쥹����
$menu->set_RetUrl($session->get('wage_referer'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

//////////// �о�ǯ��Υ��å����ǡ�������
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} else {
    $wage_ym = date('Ym');           // ���å����ǡ������ʤ����ν����(����)
}

$session->add('wage_ym', $wage_ym);

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
        <form name='data_input' action='various_data_branch.php' method='post'>
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
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                	<input class='pt10b' type='submit' name='service_name' value='���롼�ץޥ������Խ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='�������Ģ�Խ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='�꡼������Ģ�Խ�'>
                </td>
             </tr>
             <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='��������ǡ����Խ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='���ȥǡ����Խ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='����Ψ�׻��ǡ����Խ�'>
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

