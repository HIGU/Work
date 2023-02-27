<?php
//////////////////////////////////////////////////////////////////////////////
// NK̵���ٵ��ʡ�����ٵ��ʤξȲ� �� �����å���  ������ UKWLIB/W#PROVID     //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/12/08 Created   provide_month_view.php                              //
// 2003/12/11 �����ֹ��'9%%'��'F%%'���оݤ������ 9=�ƥ����� F=FA          //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2007/09/07 ���˥塼���å��򿷥�˥塼���å��� phpɸ�ॿ��(�侩��)��//
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
$menu->set_site(20, 37);                    // site_index=20(������˥塼) site_id=37(����ٵ��ʥ����å�)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(ACT_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('NK̵���ٵ��ʡ�����ٵ��ʤΥ����å��ꥹ��');
//////////// ɽ�������
$menu->set_caption('NK̵���ٵ��ʡ�����ٵ��ʤΥ����å��ꥹ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �о�ǯ���������
if (isset($_SESSION['act_ym'])) {
    $act_ym = $_SESSION['act_ym'];
} else {
    $_SESSION['s_sysmsg'] = '�о�ǯ����ꤵ��Ƥ��ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ���ǤιԿ�
define('PAGE', '25');

//////////// SQL ʸ�� WHERE ��� ���Ѥ���
$search = "WHERE reg_ym = $act_ym AND parts_no NOT LIKE '9%%' AND parts_no NOT LIKE 'F%%'";     // �о�ǯ��

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('SELECT count(*) from provide_item %s', $search);
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

//////////// �����å��ꥹ�Ȥκ�ɽ
$query = sprintf("
        SELECT
            parts_no                AS �����ֹ�,            -- 1
            substr(m.midsc,1,30)    AS ����̾,              -- 2
            CASE
                WHEN trim(type) = ''  THEN '-'
                WHEN trim(type) = 'M' THEN '����̵��'
                WHEN trim(type) = 'P' THEN '����ٵ�'
                ELSE type
            END                     AS �ٵ��ʬ             -- 3
        FROM
            provide_item AS pro LEFT OUTER JOIN miitem AS m ON pro.parts_no = m.mipn
        -- WHERE��
        %s
        ORDER by parts_no ASC
        OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // ���� $search �ϻ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '̵���ٵ��ʤΥǡ����������Ǥ��ޤ���!';
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

<style type='text/css'>
<!--
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
                        <?php echo $act_ym . '��' . $menu->out_caption() . "\n" ?>
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
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- �ơ��֥� �إå�����ɽ�� -->
            <tr>
                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
            <?php
            for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
            ?>
                <th class='winbox' nowrap><?php echo $field[$i] ?></th>
            <?php
            }
            ?>
            </tr>
            <!-- ���ߤϥեå����ϲ���ʤ� -->
                    <!--  bgcolor='#ffffc6' �������� -->
                    <!-- ����ץ�<td class='winbox pt10b' rowspan='2' colspan='3' width='200' align='center' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows; $r++) {
            ?>
                <tr>
                    <td nowrap class='winbox pt10b' align='right'><?php echo ($r + $offset + 1) ?></td>    <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                    switch ($i) {
                    case 1:
                        if ($res[$r][$i] != '') {
                            echo "<td class='winbox pt10' nowrap width='350' align='left'>{$res[$r][$i]}</td>\n";
                        } else {
                            echo "<td class='winbox pt10' nowrap width='350' align='center'>---</td>\n";
                        }
                        break;
                    default:
                        echo "<td class='winbox pt10' nowrap align='center'>{$res[$r][$i]}</td>\n";
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
