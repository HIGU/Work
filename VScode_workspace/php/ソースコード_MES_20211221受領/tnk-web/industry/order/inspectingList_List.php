<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Υꥹ�� �Ȳ� �ڤ� ���ǻؼ����ƥʥ�          List�ե졼��  //
// Copyright (C) 2007-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/01/17 Created  inspectingList_List.php                              //
// 2007/01/19 ��å������ν��� ̤�����ǡ��� �� ������ǡ���                 //
//            ����(��α)��������ɽ��(���졼��)�� ���� �� ���� ���ѹ�      //
// 2007/01/22 �����Υ���󥻥���å��� order_function.php(���̲�)���ѹ�   //
//            �������� ���ϥ���󥻥������������������뤿��             //
// 2007/01/23 �Ԥ���֥륯��å����˸�����̾�����ǻ��֤�ɽ�����ɲ�          //
// 2007/01/24 order_data��ken_date����������Ƥⴰλ���Ϥ���Ƥ��ʤ����ɽ��//
// 2007/01/25 ���ѤʤΤ����Ǥ�����Ƥ��ޤ��Զ���б� ���Ǥ�---���Ѵ�        //
// 2007/02/22 �����ֹ�˥�󥯤��ɲä��ƺ߸˷���ͽ��Ȳ�POPUP Window��ɽ��//
// 2007/04/18 <a href='javascript:win_open(..)'�� <a href='javascript:void()//
//            onClick='win_open(...)'�ν񼰤��ѹ��ˤ�������ֹ��#1�����б� //
// 2007/10/25 E_ALL | E_STRICT�� ��������WHERE���getDivWhereSQL()������  //
// 2007/10/26 SQL��WHERE���Ŭ�� �ʲ��Τ褦���ѹ�                           //
//           (CURRENT_TIMESTAMP - ken.end_timestamp) <= interval '10 minute'//
//                                      ��                                  //
//          (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')//
// 2017/07/27 ����Ǽ������դ��ɲ�                                          //
// 2018/12/14 ɽ�����٤��ΤǸ������֤������� ��������������α         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function (define.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('order_function.php');        // order �ط��ζ��� function
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 50);                    // site_index=30(������˥塼) site_id=50(Ǽ�������ų�) 999(̤��)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// ��ʬ��ե졼��������Ѥ���
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('ͽ������', INDUST . 'order/order_detailes.php');
$menu->set_action('ͽ�����ټ�����', INDUST . 'order/order_detailes_next.php');
$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('����������ꥹ�ȾȲ� �ڤ� ���ǽ���');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///////// �ѥ�᡼���������å�������
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];                // ������
    $_SESSION['div'] = $_REQUEST['div'];    // ���å�������¸
} else {
    if (isset($_SESSION['div'])) {
        $div = $_SESSION['div'];            // Default(���å���󤫤�)
    } else {
        $div = 'C';                         // �����(���ץ�)���ޤ��̣��̵��
    }
}
if (isset($_REQUEST['miken'])) {
    $select = 'miken';                      // ̤�����ꥹ��
    $_SESSION['select'] = 'miken';          // ���å�������¸
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'graph';          // ���å�������¸
} elseif (isset($_REQUEST['sgraph'])) {
    $select = 'sgraph';                     // ����Ǽ�������
    $_SESSION['select'] = 'sgraph';         // ���å�������¸
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(���å���󤫤�)
    } else {
        $select = 'graph';                  // �����(Ǽ��ͽ�ꥰ���)���ޤ��̣��̵��
    }
}
if (isset($_REQUEST['vendor_no'])) {
    $vendor_no = $_REQUEST['vendor_no'];    // Ǽ����λ��꤬����и�������
    $select = 'inspecting';                 // ������ꥹ��
    $_SESSION['select'] = 'inspecting';     // ���å�������¸
} else {
    $vendor_no = '';                        // ������Τ�
}
//$vendor_no = str_replace('*', '%', $vendor_no);   // likeʸ���б�������

if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];      // �����ֹ�λ��꤬����и�������
    $select = 'miken';                      // ̤�����ꥹ��
    $_SESSION['select'] = 'miken';          // ���å�������¸
} else {
    $parts_no = '';                         // ������Τ�
}
$parts_no = str_replace('*', '%', $parts_no);   // likeʸ���б�������

if (isset($_REQUEST['order_seq'])) {
    $order_seq = $_REQUEST['order_seq'];
} else {
    $order_seq = '';    // ������Τ� ���󥫡��ǻ��Ѥ��뤿��
}

/////////// ���̾���μ���
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

$uniq = 'id=' . uniqid('order');    // ����å����ɻ��ѥ�ˡ���ID
/////////// ���饤����ȤΥۥ���̾(����IP Address)�μ���
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);

/////////// ���� ������������Ͽ���å�
while (isset($_REQUEST['hold'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['hold'];
    acceptanceInspectionHold($order_seq, $hostName);
    break;
}
/////////// ���� ��λ��������Ͽ���å�
while (isset($_REQUEST['restart'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['restart'];
    acceptanceInspectionRestart($order_seq, $hostName);
    break;
}
/////////// ����������������Ͽ���å�(�ºݤˤϤ��Υ��å���ư�����Ȥ�̵��)
while (isset($_REQUEST['str'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['str'];
    acceptanceInspectionStart($order_seq, $hostName);
    break;
}
/////////// ��λ��������Ͽ���å�
while (isset($_REQUEST['end'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['end'];
    acceptanceInspectionEnd($order_seq, $hostName);
    break;
}
/////////// ���ϡ���λ�����Υ���󥻥� ���å�
while (isset($_REQUEST['cancel'])) {        // cancel �ϻȤ��ʤ�������ա�
    if (!client_check()) break;
    $order_seq = $_REQUEST['cancel'];
    acceptanceInspectionCancel($order_seq, $hostName);
    break;
}

while (1) {
    $where_div = getDivWhereSQL($div);
    if ($vendor_no == '') {
        $where_vendor = '';                                      // ���⤷�ʤ�
    } else {
        $where_vendor = "AND data.vendor = '{$vendor_no}'";     // Ǽ�����ֹ�Ǹ���
    }
    // �Ť��к� ����������ꤷ�ƾȲ� �������飳����
    $today      = date("Ymd",strtotime("-3 month"));
    $where_date = "AND uke_date >= " . $today;
    // �Ť��к� ��������������ꤷ�ƾȲ� �������飳����
    // ���������α�� ������λ˺����ɤ��ʤ� ����;�פ˽Ť��ʤä��������롩
    $today      = date("Ymd",strtotime("-3 month"));
    $where_ken  = "AND to_char(ken.str_timestamp, 'YYYYMMDD') >= " . $today;
    $query = "
        SELECT  substr(to_char(uke_date, 'FM9999/99/99'), 6, 5) AS uke_date
            , data.order_seq            AS order_seq
            , to_char(data.order_seq,'FM000-0000')            AS ȯ��Ϣ��
            , data.uke_no               AS uke_no
            , data.parts_no             AS parts_no
            , replace(midsc, ' ', '')   AS parts_name
            , CASE
                    WHEN trim(mzist) = '' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE substr(mzist, 1, 8)
              END                       AS parts_zai
            , CASE
                    WHEN trim(mepnt) = '' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE substr(mepnt, 1, 8)
              END                       AS parts_parent
            , uke_q                                         -- ���տ�
            , pro_mark                                      -- ��������
            , data.vendor               AS vendor           -- Ǽ�����ֹ�
            , substr(mast.name, 1, 8)   AS vendor_name      -- Ǽ����̾
            , to_char(data.sei_no,'FM0000000')  AS sei_no   -- �������Ǥ�0�ͤ᥵��ץ�
            , CASE
                    WHEN trim(data.kouji_no) = '' THEN '---'    --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE trim(data.kouji_no)
              END                       AS kouji_no
            , CASE
                    WHEN proc.next_pro = 'END..' THEN proc.next_pro    --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE (SELECT substr(name, 1, 8) FROM vendor_master WHERE vendor=proc.next_pro)
              END                       AS ������
            , ken.str_timestamp         AS str_timestamp
            , ken.end_timestamp         AS end_timestamp
            , (SELECT str_timestamp FROM inspection_holding WHERE order_seq=data.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
                                        AS hold_time
            , ken.uid                   AS uid
            , (SELECT trim(name) FROM user_detailes WHERE uid=ken.uid LIMIT 1)
                                        AS user_name
        FROM
            acceptance_kensa    AS ken
        LEFT OUTER JOIN
            order_data          AS data     USING (order_seq)
        LEFT OUTER JOIN
            order_process       AS proc     USING (sei_no, order_no, vendor)
        LEFT OUTER JOIN
            order_plan          AS plan     USING (sei_no)
        LEFT OUTER JOIN
            vendor_master       AS mast     ON (data.vendor=mast.vendor)
        LEFT OUTER JOIN
            miitem                          ON (data.parts_no=mipn)
        WHERE
            -- �����NG ( (ken.end_timestamp IS NULL) OR ((CURRENT_TIMESTAMP - ken.end_timestamp) <= (interval '10 minute')) )
            ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
            AND
            ken.str_timestamp IS NOT NULL   -- ������Τ�
            AND
            data.sei_no > 0     -- ��¤�ѤǤ���
            AND
            (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
            AND
            {$where_div} {$where_vendor} {$where_date}
        ORDER BY
            uke_date ASC, uke_no ASC
        OFFSET 0
        LIMIT 1000
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>������ǡ���������ޤ���</font>";
        $view = 'NG';
    } else {
        $view = 'OK';
    }
    break;
}

/////////// ��ư�����ȼ�ư�����ξ���ڴ���
if ($select == 'graph') {
    $auto_reload = 'on';
} elseif ($select == 'sgraph') {
    $auto_reload = 'on';
} elseif ($order_seq != '') {
    $auto_reload = 'on';
} else {
    $auto_reload = 'off';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?php // if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left:    20px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_gray {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
    color: gray;
}
.winbox_mark {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#eaeaee;
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
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
<script language='JavaScript'>
<!--
function init() {
     setInterval('document.reload_form.submit()', 30000);   // 30��
     //  onLoad='init()' ������� <body>������������OK
}
function win_open(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('�����ֹ桧' + parts_no + '\n\n����̾�Ρ�' + parts_name + " ��\n\n�۵����� ��������򤷤ޤ���\n\n�������Ǥ�����")) {
        // �¹Ԥ��ޤ���
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.retUrl.value = (document.inspection_form.retUrl.value + '#' + order_seq);
        document.inspection_form.submit();
    } else {
        alert('��ä��ޤ�����');
    }
}
function inspection_time(parts_no, parts_name, str_timestamp, end_timestamp, uid, name, hold_time) {
    if (hold_time == "-") {
        alert('�����ֹ桡����' + parts_no + '\n\n����̾�Ρ�����' + parts_name + '\n\n������������������' + str_timestamp + '\n\n������λ����������' + end_timestamp + '\n\n�Ұ��ֹ桡����' + uid + '\n\n������̾������' + name);
    } else {
        alert('�����ֹ桡����' + parts_no + '\n\n����̾�Ρ�����' + parts_name + '\n\n������������������' + str_timestamp + '\n\n������λ����������' + end_timestamp + '\n\n�Ұ��ֹ桡����' + uid + '\n\n������̾������' + name + '\n\n��������������' + hold_time);
    }
}
function miken_submit() {
    document.miken_submit_form.submit();
}
function vendor_code_view(vendor, vendor_name) {
    alert('ȯ���襳���ɡ�' + vendor + '\n\nȯ����̾��' + vendor_name + '\n\n');
}
// -->
</script>
<form name='inspection_form' method='get' action='inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?= $menu->out_self() ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
<form name='reload_form' action='inspectingList_List.php<?php if ($order_seq != '') echo "#{$order_seq}"; ?>' method='get' target='_self'>
    <input type='hidden' name='order_seq' value='<?=$order_seq?>'>
</form>
<form name='miken_submit_form' action='<?= $menu->out_parent() ?>' method='get' target='_parent'>
    <input type='hidden' name='miken' value='�����ųݥꥹ��'>
    <input type='hidden' name='div' value='<?=$div?>'>
</form>
</head>
<body <?php if ($auto_reload == 'on') echo "onLoad='init()'"; ?>>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>�ǡ���������ޤ���</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='98' nowrap colspan='2' style='font-size:14;'>�������Ͻ�λ</th>
            <th class='winbox' width='70' nowrap>������</th>
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>����No</th>
            <th class='winbox' width='90' nowrap>�����ֹ�</th>
            <th class='winbox' width='150' nowrap>����̾</th>
            <th class='winbox' width='90' nowrap style='font-size:14;'>���/�Ƶ���</th>
            <th class='winbox' width='70' nowrap>���տ�</th>
            <th class='winbox' width='35' nowrap style='font-size:9.5pt;'>����</th>
            <th class='winbox' width='130' nowrap>Ǽ����</th>
            <?php if ($display == 'wide') { ?>
            <th class='winbox' width='80' nowrap>�����ֹ�</th>
            <th class='winbox' width='80' nowrap>ȯ��Ϣ��</th>
            <th class='winbox' width='70' nowrap>��¤�ֹ�</th>
            <th class='winbox' width='130' nowrap>������</th>
            <?php } ?>
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                if ($rec['end_timestamp']) $winbox = 'winbox_gray'; else $winbox = 'winbox';
                if ($rec['order_seq'] == $order_seq) $winbox = 'winbox_mark'; else $winbox = 'winbox';
                if ($rec['str_timestamp']) { // ���֥륯��å��Ǹ������ϻ��֤Ƚ�λ���֤�ɽ�� 2005/02/21 �ɲ�
                    if ($rec['end_timestamp']) {
                        echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"{$rec['end_timestamp']}\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                    } else {
                        if ($rec['hold_time']) {
                            echo "<tr style='color:gray;' onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"̤��λ\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"{$rec['hold_time']}\")'>\n";
                        } else {
                            echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"̤��λ\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                        }
                    }
                } else {    // ���֥륯��å��Ƕ۵޸������꤬�����
                    echo "<tr onDblClick='inspection_recourse(\"{$rec['order_seq']}\",\"{$rec['parts_no']}\",\"{$rec['parts_name']}\")'>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='inspectingList_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$i}</a></td>\n";
                if ($rec['str_timestamp']) {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44 nowrap'>����</td>\n";
                    } else {
                        if ($rec['hold_time']) {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>����</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspectingList_List.php?end={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:yellow;'>����</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspectingList_List.php?str={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>����</a></td>\n";
                }
                if ( ($rec['str_timestamp']) || ($rec['end_timestamp']) ) {
                    if ( ($rec['str_timestamp']) && ($rec['end_timestamp']) ) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red;' align='center' width='44' nowrap><a href='inspectingList_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:gray;'>����</a></td>\n";
                    } else {
                        if ($rec['hold_time']) {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>���</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red;' align='center' width='44' nowrap><a href='inspectingList_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:red;'>���</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray ;' align='center' width='44' nowrap>���</td>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'  nowrap><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                // echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap>{$rec['uke_no']}</td>\n";
                if ($rec['hold_time']) {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap><a href='inspectingList_List.php?restart={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>�Ƴ�</a></td>\n";
                } else {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='55'  nowrap>---</td>\n";
                    } else {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap><a href='inspectingList_List.php?hold={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>����</a></td>\n";
                    }
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91'  nowrap onClick='win_open(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                echo "    <a class='link' href='javascript:void(0)' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='150' nowrap>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 27) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'  nowrap>" . mb_convert_kana($rec['parts_zai'], 'k') . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'  nowrap>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'  nowrap>{$rec['pro_mark']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' nowrap onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80'  nowrap>{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80'  nowrap>{$rec['ȯ��Ϣ��']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'  nowrap>{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' nowrap>{$rec['������']}</td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // ������ѣ�����
// -->
</script>
<?=$menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
