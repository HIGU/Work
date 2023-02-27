<?php
//////////////////////////////////////////////////////////////////////////////
// �����֡��軻 �����ӥ���� ��˥塼                                   //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/10/17 Created   service_percentage_menu.php                         //
//            �����ӥ���������(�������)�ȾȲ�ڤ���¤������������      //
// 2003/10/24 ǯ��λ���˷軻����ɲ�(3���9��) �㡧200309�軻             //
// 2003/10/28 ����ʬ�������Ͻ����褦���ѹ�                                //
// 2003/10/30 �о�ǯ��Υ��å����ǡ�����̵���������������ѹ�        //
// 2005/08/25 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� 
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10, 5);                     // site_index=10(»�ץ�˥塼) site_id=5(�����ӥ�����˥塼)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ӥ���� ���� ��˥塼');
//////////// ɽ�������
$menu->set_caption('�����ӥ���� ���� ��˥塼�����о�ǯ��λ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����ӥ��������',   PL . 'service/service_category_select.php?exec=entry');
$menu->set_action('�����ӥ����Ȳ�',   PL . 'service/service_category_select.php?exec=view');
$menu->set_action('��� ���� �Ȳ�',     PL . 'service/service_percent_view_total.php');
$menu->set_action('��¤���������',     PL . 'service/service_percent_act_allo.php');
$menu->set_action('�ޥ������Խ�',       PL . 'service/service_item_master_mnt.php');
$menu->set_action('ͽ¬����Ψ������',   PL . 'service/service_percent_act_allo_plan.php');
$menu->set_action('��������',       PL . 'service/service_final_set.php?set');
$menu->set_action('�������',       PL . 'service/service_final_set.php?unset');
$menu->set_action('��¤�������겾��',   PL . 'service/service_percent_act_allo_kari.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');           // ���å����ǡ������ʤ����ν����(����)
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

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
<body onLoad='document.service.service_ym.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <form name='service' action='service_branch.php?id=<%=$uniq%>' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' colspan='5' class='winbox'>
                    <span class='caption_font'>
                        <?= $menu->out_caption() . "\n" ?>
                        <select name='service_ym'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($service_ym == $ym) {
                                    if ( (substr($ym,4,2) == '03') || (substr($ym,4,2) == '09') ) {
                                        printf("<option value='%s'>%s�軻</option>\n",$ym . '32', $ym);
                                    }
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else {
                                    if ( (substr($ym,4,2) == '03') || (substr($ym,4,2) == '09') ) {
                                        printf("<option value='%s'>%s�軻</option>\n",$ym . '32', $ym);
                                    }
                                    printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                }
                                if (substr($ym,4,2) != 01) {
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
                    <input class='pt10b' type='submit' name='service_name' value='�����ӥ��������'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='�����ӥ����Ȳ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='��� ���� �Ȳ�'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>   <!-- #ffffc6 �������� -->
                    <input class='pt10b' type='submit' name='service_name' value='��¤���������'>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='�ޥ������Խ�'>
                </td> <!-- ;�� -->
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='��������'>
                </td> <!-- ;�� -->
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='�������'>
                </td> <!-- ;�� -->
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='ͽ¬����Ψ������'>
                </td> <!-- ;�� -->
            </tr>
            <tr>
                <td align='center' bgcolor='#d6d3ce' colspan='3' class='winbox'>
                    &nbsp;
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='��¤�������겾��'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
