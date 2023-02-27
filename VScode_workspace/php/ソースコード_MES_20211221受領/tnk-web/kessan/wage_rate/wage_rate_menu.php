<?php
//////////////////////////////////////////////////////////////////////////////
// �����֡��軻 ��Ω��Ψ�׻� ��˥塼                                   //
// Copyright (C) 2006-2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2006/05/23 Created   wage_rate_menu.php                                  //
// 2007/10/05 �ե����ooya���������٥��ɥ쥹���ѹ�                        //
// 2007/10/19 E_ALL��E_STRICT�آ������Ȳ�                                 //
//            ���硼�ȥ�����ɸ�ॿ��(�侩��)���ѹ�                          //
// 2007/10/20 ,�θ�˥��ڡ������ʤ��Ľ������                               //
// 2007/10/22 <!DOCTYPE HTML �����˲��Ԥ�����Τ���                  ���� //
// 2007/10/24 ;ʬ�ʥ������륷���Ȥκ��                                    //
// 2007/12/13 �о�ǯ��μ����Ϥ��Ѥ�$request������                          //
// 2007/12/29 ������������Ω��Ψ������������Ψ�򿷥ץ����إ���ѹ�  //
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
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
////////////// ����������
$menu->set_site(10, 4);                     // site_index=10(»�ץ�˥塼) site_id=4(��Ω��Ψ�׻�ɽ)
////////////// �꥿���󥢥ɥ쥹����
//$menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��Ω��Ψ ���� ��˥塼');
//////////// ɽ�������
$menu->set_caption('��Ω��Ψ ���� ��˥塼�����о�ǯ��λ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�Ƽ�ǡ�������',  'wage_various_data_input_main.php');
$menu->set_action('����������Ȳ�',  'assemblyRate_depreciationCal_Main.php');
$menu->set_action('��Ω��Ψ�ξȲ�',  'assemblyRate_reference_Main.php');
$menu->set_action('����������Ψ�ξȲ�',  'assemblyRate_actAllocate_Main.php');

$request = new Request;
$session = new Session;

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// ��å��������ϥե饰
$msg_flg = 'site';

//////////// �о�ǯ��Υ��å����ǡ�������
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} else if ($session->get('wage_ym') != '') {
    $wage_ym = $session->get('wage_ym'); 
} else {
    $wage_ym = date('Ym');           // ���å����ǡ������ʤ����ν����(����)
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
</head>
<body onLoad='document.wage_rate.wage_ym.focus()'>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='wage_rate' action='wage_branch.php' method='post'>
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
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n", $ym, substr($ym, 0, 4), substr($ym, 4, 2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%sǯ%s��</option>\n", $ym, substr($ym, 0, 4), substr($ym, 4, 2));
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
                    <input class='pt10b' type='submit' name='wage_name' value='�Ƽ�ǡ�������'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='wage_name' value='����������Ȳ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='wage_name' value='��Ω��Ψ�ξȲ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='wage_name' value='����������Ψ�ξȲ�'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>

