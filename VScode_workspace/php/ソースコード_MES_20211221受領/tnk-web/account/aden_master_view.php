<?php
//////////////////////////////////////////////////////////////////////////////
// ��������ե�����ξȲ� �� �����å���  ������ UKWLIB/W#MIADIM             //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// �ѹ�����                                                                 //
// 2003/11/27 ��������  aden_master_view.php                                //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2007/09/10 ���˥塼���å��򿷥�˥塼���å��� phpɸ�ॿ��(�侩��)��//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 60);          // ����¹Ի���=1ʬ CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(20, 13);                    // site_index=20(������˥塼) site_id=13(��������ι��������å�)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(ACT_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��������ι��� �����å��ꥹ��');
//////////// ɽ�������
$menu->set_caption('��������ι��� �����å��ꥹ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �о�ǯ���������
$act_ymd = $_SESSION['act_ymd'];    // ��������Ǥ�ɬ�פʤ���
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// ���ǤιԿ�
define('PAGE', '25');

//////////// SQL ʸ�� where ��� ���Ѥ���
// $search = sprintf("where aden_no>='%s'", $act_ymd);
$search = '';

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('SELECT count(*) FROM aden_master %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}

//////////// �ڡ������ե��å�����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// ��������Υ����å��ꥹ�Ⱥ��� Query & �����
$query = sprintf("
        SELECT
            aden_no     AS ����,                        -- 0
            eda_no      AS ��,                          -- 1
            CASE
                WHEN trim(parts_no) = '' THEN '---'
                ELSE parts_no
            END         AS �����ֹ�,                    -- 2
            sale_name   AS ���侦��̾,                  -- 3
            CASE
                WHEN trim(midsc) IS NULL THEN '---'
                ELSE substr(midsc, 1, 12)
            END         AS ��������̾,                  -- 4
            CASE
                WHEN trim(plan_no) = '' THEN '---'
                ELSE plan_no
            END         AS �ײ��ֹ�,                    -- 5
            CASE
                WHEN trim(approval) = '' THEN '---'
                ELSE approval
            END         AS ��ǧ��,                      -- 6
            CASE
                WHEN trim(ropes_no) = '' THEN '---'
                ELSE ropes_no
            END         AS ���ν�,                      -- 7
            CASE
                WHEN trim(kouji_no) = '' THEN '---'
                ELSE kouji_no
            END         AS �����ֹ�,                    -- 8
            order_q     AS �������,                    -- 9
            order_price AS ����ñ��,                    --10
            Uround(order_q * order_price, 0) AS ���,   --11
            espoir_deli AS ��˾Ǽ��,                    --12
            delivery    AS ����Ǽ��                     --13
        FROM
            aden_master
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        %s 
        ORDER BY aden_no DESC OFFSET %d LIMIT %d
        
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '��������Υǡ����������Ǥ��ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<!--    �ե��������ξ��
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language='JavaScript'>
<!--
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� -->
<link rel='stylesheet' href='act_menu.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<style type="text/css">
<!--
.winbox_field th {
    background-color:   yellow;
    color:              blue;
    font-weight:        bold;
    font-size:          0.80em;
    font-family:        monospace;
}
-->
</style>
</head>
<body style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?php echo format_date($act_ymd) . "  {$menu->out_title()}\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- �ơ��֥� �إå�����ɽ�� -->
            <tr>
                <th nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
            <?php
            for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
            ?>
                <th nowrap><?php echo $field[$i] ?></th>
            <?php
            }
            ?>
            </tr>
                    <!--  bgcolor='#ffffc6' �������� -->
                    <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows; $r++) {
            ?>
                <tr>
                    <td nowrap class='pt10b' align='right'><?php echo ($r + $offset + 1) ?></td>    <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                    switch ($i) {
                    case 3:
                    case 4:
                        echo "<td nowrap align='left' class='pt9'>{$res[$r][$i]}</td>\n";
                        break;
                    case  9:
                    case 10:
                    case 11:
                        echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 0) . "</td>\n";
                        break;
                    case 12:
                    case 13:
                        echo "<td nowrap align='center' class='pt9'>" . format_date($res[$r][$i]) . "</td>\n";
                        break;
                    default:
                        echo "<td nowrap align='center' class='pt9'>{$res[$r][$i]}</td>\n";
                    }
                }
                ?>
                </tr>
            <?php
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
