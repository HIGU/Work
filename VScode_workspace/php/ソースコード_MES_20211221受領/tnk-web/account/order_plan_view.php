<?php
//////////////////////////////////////////////////////////////////////////////
// ȯ��ײ�ξȲ� �� �����å���  ������ UKWLIB/W#MIOPLN                     //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/11/20 Created   order_plan_view.php                                 //
// 2003/12/11 �����ֹ椬�֥�󥯤λ���'--------'ɽ���ˤʤ�褦���ѹ�        //
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
$menu->set_site(20, 12);                    // site_index=20(������˥塼) site_id=12(ȯ��ײ�ι��������å�)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(ACT_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ȯ��ײ�ǡ����ι����ǡ��� �����å��ꥹ��');
//////////// ɽ�������
$menu->set_caption('ȯ��ײ�ǡ����ι����ǡ��� �����å��ꥹ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �о�ǯ���������
$act_ymd = $_SESSION['act_ymd'];
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// ���ǤιԿ�
define('PAGE', '25');

//////////// SQL ʸ�� where ��� ���Ѥ���
// $search = sprintf('where sei_no=%d', $act_ymd);

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
/********
// $query = sprintf('select count(*) from act_payable %s', $search);
$query = 'select count(*) from act_payable';
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}
**********/
$maxrows = 117000;  // 2003/11/20 ���ߤΥ쥳���ɿ� ����®�٤�夲�뤿���ƥ��ǻ���

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

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        select
            o.sei_no                as ��¤�ֹ�,            -- 0
            o.order5                as ��ʸ�ֹ�,            -- 1
            o.parts_no              as �����ֹ�,            -- 2
            substr(m.midsc,1,12)    as ����̾,              -- 3
            CASE
                WHEN trim(o.kouji_no) = ''  THEN '--------'
                ELSE o.kouji_no
            END                     as ����,                -- 4
            o.order_q               as ȯ���,              -- 5
            o.utikiri               as ���ڿ�,              -- 6
            o.nyuko                 as Ǽ����,              -- 7
            o.zan_q                 as �Ŀ�,                -- 8
            o.plan_date             as ȯ��ͽ��,            -- 9
            o.last_delv             as �ǽ�Ǽ��,            --10
            o.plan_cond             as ��ʬ,                --11
            o.locate                as ����,                --12
            o.div                   as ��,                  --13
            o.org_delv              as ��Ǽ��               --14
        from
            order_plan as o left outer join miitem as m on o.parts_no = m.mipn
        -- where sei_no = 1482716
        order by sei_no DESC
        offset %d limit %d
    ", $offset, PAGE);       // ���� $search �ϻ��Ѥ��ʤ�
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'ȯ��ײ�Υǡ���������ޤ���!';
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
<meta http-equip="Content-Script-Type" content="text/javascript">
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
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
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
                        <?php echo format_date($act_ymd) . '��' . $menu->out_caption() . "\n" ?>
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
                        echo "<td nowrap align='left' class='pt9'>{$res[$r][$i]}</td>\n";
                        break;
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                        echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 0) . "</td>\n";
                        break;
                    case  9:
                    case 10:
                    case 14:
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
