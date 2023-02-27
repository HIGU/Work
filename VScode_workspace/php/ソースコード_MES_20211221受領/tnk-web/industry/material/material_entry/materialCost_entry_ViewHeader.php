<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ͽ materialCost_entry_ViewHeader.php                         //
// Copyright (C) 2008 - 2015 Norihisa.Ohya                                  //
// Changed history                                                          //
// 2007/05/23 Created   metarialCost_entry_main.php                         //
// 2007/06/21 php���硼�ȥ�����ɸ�ॿ���ء� HTML��;ʬ�ʥ��������� ����   //
//            $menu->out_retF2Script() �ɲ� ����                            //
// 2007/06/22 $uniq�����Ѥ���Ƥ��ʤ��Τǥ����ȥ����ȡ�����               //
// 2007/09/14 �������κǿ���Ͽ������������ֹ���� ����                   //
// 2007/09/18 E_ALL | E_STRICT ���ѹ� ����                                  //
// 2007/09/19 elseif (substr($plan_no, 0, 2) == 'ZZ') 25.60 ���ɲ� ����     //
// 2008/02/14 if (substr($assy_no, 0, 1) == 'C') 25.6 else 37.0 ���ɲ�      //
// 2015/05/21 �������ʤ����������Ͽ���б�                                  //
// 2020/06/11 �������ư��Ͽ������ä�����ɽ����ݻ������ �ɲ�      ���� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 21);                    // site_index=30(������˥塼) site_id=21(����������Ͽ)
/////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� Ͽ (��������)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����������Ͽ',   INDUST . 'material/materialCost_entry_old.php');
//////////// ����ؤ�GET�ǡ�������
$menu->set_retGET('page_keep', 'On');
$menu->set_retGET('material', '1');   // �������ư��Ͽ������ä�����ɽ����ݻ������

$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

$request = new Request;
$session = new Session;
//////////// �֥饦�����Υ���å����к���
// $uniq = $menu->set_useNotCache('target');

if (isset($_REQUEST['msg_flg'])) {
    $msg_flg = 'alert';
} else {
    $msg_flg = 'site';
}
//////////// �ײ��ֹ桦�����ֹ�����
$plan_no = $session->get('plan_no');
$assy_no = $session->get('assy_no');

//////////// �������κǿ���Ͽ������������ֹ����
if (substr($plan_no, 0, 2) == 'ZZ') $menu->set_retGET('assy', $assy_no);

//////////// �졼�Ȥ�ײ��ֹ椫�����(�����ޥ����������ѹ�ͽ��)
if (substr($plan_no, 0, 1) == 'C') {
    $sql2 = "
        SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $sc = '';
    getUniResult($sql2, $sc);
    if ($sc == 'SC') {
        define('RATE', 25.60);  // ���ץ�����
    } else {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        if ($kan < 20071001) {
            define('RATE', 25.60);  // ���ץ�ɸ�� 2007/10/01���ʲ������
        } elseif ($kan < 20110401) {
            define('RATE', 57.00);  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
        } else {
            define('RATE', 45.00);  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
        }
    }
} elseif (substr($plan_no, 0, 2) == 'ZZ') {
    if (substr($assy_no, 0, 1) == 'C') {
        if ($kan < 20110401) {
            define('RATE', 57.00);  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
        } else {
            define('RATE', 45.00);  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
        }
    } elseif (substr($assy_no, 0, 1) == 'L') {
        if ($kan < 20110401) {
            define('RATE', 44.00);  // ��˥� 2007/10/01���ʲ���ʹ�
        } else {
            define('RATE', 53.00);  // ��˥� 2011/04/01���ʲ���ʹ�
        }
    } else {
        define('RATE', 50.00);  // �ġ���
    }
} elseif (substr($plan_no, 0, 1) == 'L') {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    if ($kan < 20081001) {
        define('RATE', 37.00);  // ��˥� 2008/10/01���ʲ������
    } elseif ($kan < 20110401) {
        define('RATE', 44.00);  // ��˥� 2008/10/01���ʲ���ʹ�
    } else {
        define('RATE', 53.00);  // ��˥� 2011/04/01���ʲ���ʹ�
    }
} else {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    define('RATE', 50.00);  // �ġ���
}

//////////// ����̾�μ���
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // ����̾�μ���
    $_SESSION['s_sysmsg'] .= "����̾�μ����˼���";      // .= ��å��������ɲä���
    $msg_flg = 'alert';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ɽ�������
// $menu->set_caption('���ߤη�����Ψ��' . number_format(RATE, 2));
$menu->set_caption("�ײ��ֹ桧{$plan_no}&nbsp;&nbsp;�����ֹ桧{$assy_no}&nbsp;&nbsp;����̾��{$assy_name}");

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script language="JavaScript">
</script>

<style type="text/css">
<!--
.pt10 {
    font:normal     10pt;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          9pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
        <div class='pt10' style='color:gray;'>���ߤη�����Ψ��<?php echo number_format(RATE, 2) ?></div>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <th class='winbox' width=' 4%' nowrap>No</th>
                <th class='winbox' width=' 5%' nowrap>Level</th>
                <th class='winbox' width=' 8%' nowrap>�����ֹ�</th>
                <th class='winbox' width='39%' nowrap>����̾</th>
                <th class='winbox' width=' 6%' nowrap>���ѿ�</th>
                <th class='winbox' width=' 4%' nowrap>����</th>
                <th class='winbox' width=' 6%' nowrap>����̾</th>
                <th class='winbox' width=' 7%' nowrap>����ñ��</th>
                <th class='winbox' width=' 7%' nowrap>�������</th>
                <th class='winbox' width=' 6%' nowrap>�⳰��</th>
                <th class='winbox' width=' 8%' nowrap>���ֹ�</th>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php echo $menu->out_retF2Script() ?>
</center>
</body>
<?php if ($msg_flg == 'alert') echo $menu->out_alert_java(); ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END    
?>