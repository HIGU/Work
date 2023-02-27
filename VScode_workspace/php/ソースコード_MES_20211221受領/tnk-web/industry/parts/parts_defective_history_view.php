<?php
//////////////////////////////////////////////////////////////////////////////
// ������ ���ʺ߸˷��� �Ȳ� ɽ������(�֣ͣ�)                                //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created  parts_stock_view.php                                 //
// 2004/12/21 order by��serial_no DESC��upd_date DESC, serial_no DESC���ѹ� //
// 2004/12/23 Ⱦ�ѥ��ʤ�mb_substr��Ȥ�ʤ� length �����ʤ��ʤ롣         //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);������  //
// 2005/01/11 �ã����ʤλ������˷��򤬤ʤ�������ݤΥ�󥯤�ɽ�������      //
//            ��ë�����˾�Ǿ����դ����˥�󥯤Ȥ���褦���ѹ�            //
// 2005/01/12 ��������̤��Ͽ����θƽл���500��200���ѹ� retGET��#mark�ɲ�//
//            ���ץ�Υܡ��������к���tnk_stock��100000�Ĥ�Ķ�������400  //
// 2005/01/31 $menu->set_retGETanchor('mark') ���ɲ� urlencode()���б�      //
//            '&material=' . urlencode($_SESSION['stock_parts']) ���ɲ�     //
//            ���Τ߹��ֹ�򥻥å�������¸�����꥿������˹��ֹ���֤�  //
// 2005/03/02 allo_parts_row��material_plan_no��@���޻�(ñ�θƽ��б�)       //
// 2005/05/22 order by parts_no DESC, upd_date DESC, serial_no DESC ���ѹ�  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');// zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 40);                    // site_index=30(������˥塼) site_id=40(���ʺ߸˷���)999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ʺ߸˷���ξȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_frame('�ե졼���ɽ��',   INDUST . 'parts/parts_stock_iframe.php');
$menu->set_action('��ݼ��ӾȲ�',     INDUST . 'payable/act_payable_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// GET & POST �ǡ����μ���
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['stock_parts'] = $parts_no;
} else {
    $parts_no = $_SESSION['stock_parts'];
}
if (isset($_REQUEST['row'])) {
    $_SESSION['allo_parts_row'] = $_REQUEST['row'];             // ���Τ߹��ֹ�򥻥å�������¸
}

    $material = '';
    $plan_no  = '��';

if (isset($_REQUEST['date_low'])) {
    $date_low = $_REQUEST['date_low'];
    $_SESSION['stock_date_lower'] = $date_low;
} else {
    $date_low = '20000401';      // ���ꤵ��Ƥ��ʤ����
}
if (isset($_REQUEST['date_upp'])) {
    $date_upp = $_REQUEST['date_upp'];
    $_SESSION['stock_date_upper'] = $date_upp;
} else {
    $date_upp = date('Ymd');    // ���ꤵ��Ƥ��ʤ����
}
if (isset($_REQUEST['view_rec'])) {
    $view_rec = $_REQUEST['view_rec'];
    $_SESSION['stock_view_rec'] = $view_rec;
} else {
    $sql = "select tnk_stock from parts_stock_master where parts_no='{$parts_no}'";
    getUniResult($sql, $tnk_stock);
    if ($tnk_stock >= 100000) {
        $view_rec = '400';      // ���ꤵ��Ƥ��ʤ����(��������̤��Ͽ����θƽ���) 500��400
    } else {
        $view_rec = '200';      // ���ꤵ��Ƥ��ʤ����(��������̤��Ͽ����θƽ���) 500��200
    }
}

//////////// ɽ�������
$query = "select substr(midsc, 1, 20)
                , substr(mzist, 1, 6)
                , substr(mepnt, 1, 6)
                , tnk_tana, nk_stock+tnk_stock AS sum_stock
            from
                miitem
                left outer join
                parts_stock_master
                on mipn=parts_no
            where mipn='{$parts_no}'";
$item  = array();
if (getResult2($query, $item) <= 0) {
    $_SESSION['s_sysmsg'] .= '�ޥ�����̤��Ͽ';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
        // (���ѥ��ʤ�Ⱦ�Ѥ�)Ⱦ�ѥ��ʤ�mb_substr��Ȥ�ʤ��Ȥ��ޤ������ʤ���
    $name  = mb_substr(mb_convert_kana($item[0][0], 'krsna'), 0, 10);   // ����̾
    $zai   = mb_substr(mb_convert_kana($item[0][1], 'krsna'), 0,  7);   // ���
    $kisyu = $item[0][2];   // �Ƶ���
    $tana  = $item[0][3];   // TNKê��
    $zaiko = number_format($item[0][4]);   // ���ߺ߸�
}
//////////// ɽ�������
/****************************************
if (getUniResult("select miccc from miccc where mipn='{$parts_no}'", $miccc)) {
    if ($miccc == 'D') {
        $link = "<a href='" . $menu->out_action('��ݼ��ӾȲ�') . "?parts_no={$parts_no}&material=1' style='text-decoration:none;'>{$parts_no}</a>";
        $menu->set_caption("�����ֹ桧{$link}������̾��{$name}&nbsp;&nbsp;�����{$zai}&nbsp;&nbsp;�Ƶ��{$kisyu}��ê�֡�{$tana}�����ߺ߸ˡ�<font color='red'>{$zaiko}</font>");
    } else {
        $menu->set_caption("�����ֹ桧{$parts_no}������̾��{$name}&nbsp;&nbsp;�����{$zai}&nbsp;&nbsp;�Ƶ��{$kisyu}��ê�֡�{$tana}�����ߺ߸ˡ�<font color='red'>{$zaiko}</font>");
    }
} else {
    $menu->set_caption("�����ֹ桧{$parts_no}������̾��{$name}&nbsp;&nbsp;�����{$zai}&nbsp;&nbsp;�Ƶ��{$kisyu}��ê�֡�{$tana}�����ߺ߸ˡ�<font color='red'>{$zaiko}</font>");
}
****************************************/
$link = "<a href='" . $menu->out_action('��ݼ��ӾȲ�') . "?parts_no=" . urlencode($parts_no) . "&material=1' style='text-decoration:none;'>{$parts_no}</a>";
$menu->set_caption("�����ֹ桧{$link}������̾��{$name}&nbsp;&nbsp;�����{$zai}&nbsp;&nbsp;�Ƶ��{$kisyu}��ê�֡�{$tana}�����ߺ߸ˡ�<font color='red'>{$zaiko}</font>");

//////////// ɽ�����Υǡ���ɽ���� Query & �����
$query = "select substr(to_char(ent_date, 'FM9999/99/99'), 3, 8)
                                                            as �׾���       -- 0
                , CASE
                    WHEN plan_no = '' THEN '&nbsp;'
                    ELSE plan_no
                  END                                       as Ŧ����       -- 1
                , CASE
                    WHEN out_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN out_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as �и˿�       -- 2
                , CASE
                    WHEN in_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN in_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as ���˿�       -- 3
                , CASE
                    WHEN out_id = '1' THEN nk_stock  - stock_mv + tnk_stock
                    WHEN out_id = '2' THEN tnk_stock - stock_mv + nk_stock
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv + tnk_stock
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv + nk_stock
                    ELSE nk_stock + tnk_stock
                  END                                       as ��׺߸�     -- 4
                , den_kubun                                 as ��ʬ         -- 5
                , CASE
                    WHEN den_no = '' THEN '&nbsp;'
                    ELSE den_no
                  END                                       as ��ɼ�ֹ�     -- 6
                , CASE
                    WHEN out_id = '2' THEN tnk_stock - stock_mv
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv
                    ELSE tnk_stock
                  END                                       as ���ں߸�     -- 7
                , CASE
                    WHEN out_id = '1' THEN nk_stock  - stock_mv
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv
                    ELSE nk_stock
                  END                                       as �Σ˺߸�     -- 8
                , CASE
                    WHEN note = '' THEN '&nbsp;'
                    ELSE note
                  END                                       as ������       -- 9
            from
                parts_stock_history
            where
                parts_no='{$parts_no}'
                and
                upd_date>={$date_low}
                and
                upd_date<={$date_upp}
            order by
                parts_no DESC, upd_date DESC, serial_no DESC
";
$_SESSION['stock_history_query'] = $query . " limit {$view_rec}";
$query = $query . ' limit 0';
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $num = count($field);       // �ե�����ɿ�����
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    �ե��������ξ��
<script type='text/javascript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script type='text/javascript'>
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
.pt8 {
    font-size:   8pt;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    background-color: blue;
    color:            yellow;
    font-size:        12pt;
    font-weight:      bold;
    font-family:      monospace;
}
td {
    font-size:   12pt;
    font-weight: bold;
    /* font-family: monospace; */
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
-->
</style>
</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table width='100%' align='center' border='0'>
            <tr>
                <td nowrap class='pt12b' align='center'>
                    <?= $menu->out_caption(), "\n"?>
                </td>
            </tr>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table width='880' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th width='42' class='winbox' nowrap>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    switch ($i) {
                    case 0:     // �׾���
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 1:     // Ŧ��
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 2:     // �и˿�
                    case 3:     // ���˿�
                    case 4:     // ��׺߸�
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 5:     // ��ɼ��ʬ
                        echo "<th width='40' class='winbox' nowrap style='font-size:10pt;'>{$field[$i]}</th>\n";
                        break;
                    case 6:     // ��ɼ�ֹ�
                    case 7:     // ���ں߸�
                    case 8:     // �Σ˺߸�
                        echo "<th width='80' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    case 9:     // ����
                        echo "<th width='120' class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    default:    // ����¾�������
                        echo "<th class='winbox' nowrap>{$field[$i]}</th>\n";
                        break;
                    }
                }
                ?>
                    <th width='5' class='winbox' nowrap>&nbsp;</th>     <!-- ��������С���������� -->
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <!-- iframe��ɽ�� -->
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
                <?php echo "<iframe hspace='0' vspace='0' scrolling='yes' src='", $menu->out_frame('�ե졼���ɽ��'), "?plan_no=", urlencode($plan_no), $material, "&id={$uniq}#last' name='parts_stock_iframe' align='center' width='882' height='560' title='parts_stock_history'>\n" ?>
                    ���ʺ߸˷�������٤�ɽ�����ޤ���
                </iframe>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
