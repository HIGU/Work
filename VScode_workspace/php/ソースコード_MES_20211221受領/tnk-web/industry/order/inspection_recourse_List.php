<?php
//////////////////////////////////////////////////////////////////////////////
// �۵� ���� ���� ���� �Ȳ�ڤӥ��ƥʥ�      List�ե졼��               //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/19 Created  inspection_recourse_List.php                         //
// 2004/10/20 Ǽ�����ν��� and proc.locate != '52   '����               //
// 2004/10/21 mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 14)��  //
//            PostgreSQL �� replace(midsc, ' ', '')�Τߤ��ѹ�               //
// 2004/10/22 ����ˤ⥫�����ʤ���Ѥ��Ƥ����Τ����뤿��mb_substr()��Ŭ�� //
// 2004/10/29 ë������ȴ���Ƥ���Τ���                                  //
// 2004/11/10 NKʬ����uke_no�ξ����ɲ� 400000��500000 ���ڤǸ���������  //
// 2004/11/24 ��������ä򲡤�������order_schedule_List���鼫ʬ������       //
// 2004/12/06 ��������SQLʸ���Զ�礬���ä��Τ���(���顼�ˤʤ�ʤ��Զ��) //
//(SELECT substr(mast.name, 1, 8) FROM vendor_master WHERE proc.next_pro=vendor)
//               ��                                                         //
//(SELECT substr(name, 1, 8) FROM vendor_master WHERE vendor=proc.next_pro) //
// 2004/12/29 ������˾����������Ф�tnk_func��day_off()�ؿ���Ȥ��褦���ѹ� //
// 2005/02/23 �������Ϥ��줿���ʤϥ��֥륯��å��Ǹ������ϡ���λ������ɽ��  //
// 2005/03/10 ��ë�������� �ڤ���Ҥ��줿�ͤΥ���                       //
// 2005/05/26 WHERE��� acceptance_kensa �� end_timestamp �����ɲ�        //
//               ȯ����̾�򥯥�å������ȯ���襳���ɤ�Ȳ�Ǥ��뵡ǽ���ɲ� //
// 2005/07/22 ��˾����SELECT���������ѹ����� order_seq�����ʤ��Τ���    //
//            user_check()��$order_seq�ؤ������θ���ѹ� �ޡ�����ɽ�������� //
//            priority �Υ��󥯥���ȡ��ǥ������ ���å����ѹ�        //
// 2006/04/13 �ͤΰ�ư��ȼ�������ѹ�(���������ϡ�������ź�ġ��޽���)        //
// 2006/04/20 ���´ط����� function ���ѹ� order_function.php             //
// 2006/07/04 �������ϻ���uid(�Ұ��ֹ�)����Ͽ���ɲ� acceptance_kensa        //
// 2006/08/02 ���ʥ��롼�פˣΣˣ¤��ɲ� ���Τ��� SQL�� order_plan �ɲ�     //
// 2006/08/31 ������˾�����ϰϤ�10���֢�17���֤��ѹ�(����ݰ���ˤ��)      //
// 2006/10/26 Windows2000��IE���ȹ礻�ǥ��쥯�ȥܥå�����Υ�������С��� //
//            �ܥ����DB����å�����ȥ����DB����å��������ˤʤ븽�ݤ��б�//
//            <tr onDblClick �� <tr>��  <td{$inspec}���ѿ�����������б�  //
// 2007/01/18 �������Ǥ�ɽ����ǽ�ɲ� hold_flg �Ǹ���                        //
// 2007/01/22 �����Υ���󥻥���å��� order_function.php(���̲�)���ѹ�   //
//            �������� ���ϥ���󥻥������������������뤿��             //
// 2007/02/22 �����ֹ�˥�󥯤��ɲä��ƺ߸˷���ͽ��Ȳ�POPUP Window��ɽ��//
// 2007/04/18 <a href='javascript:win_open(..)'�� <a href='javascript:void()//
//            onClick='win_open(...)'�ν񼰤��ѹ��ˤ�������ֹ��#1�����б� //
// 2007/10/25 E_STRICT �� E_ALL | E_STRICT ��   and �� AND ��               //
// 2007/10/26 SQL��WHERE���Ŭ�� �ʲ��Τ褦���ѹ�                           //
//           (CURRENT_TIMESTAMP - ken.end_timestamp) <= interval '10 minute'//
//                                      ��                                  //
//          (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function (define.php��ޤ�)
require_once ('../../tnk_func.php');        // TNK ���� function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('order_function.php');        // order �ط��ζ��� function
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// ��ʬ��ե졼��������Ѥ���
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
// $menu->set_action('ͽ������', INDUST . 'order/order_detailes.php');
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�۵����ʸ�������ξȲ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///////// �ѥ�᡼���������å�������
$div = $_SESSION['div'];                    // Default(���å���󤫤�)
$select = 'inspc';                          // ��������
if (isset($_REQUEST['order_seq'])) {
    $order_seq = $_REQUEST['order_seq'];
} else {
    $order_seq = '';    // ������Τ� ���󥫡��ǻ��Ѥ��뤿��
}

/////////// ���̲����٤μ���
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

$uniq = 'id=' . uniqid('order');    // ����å����ɻ��ѥ�ˡ���ID
/////////// ���饤����ȤΥۥ���̾(����IP Address)�μ���
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);

/////////// ������������Ͽ���å�
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
while (isset($_REQUEST['cancel'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['cancel'];
    acceptanceInspectionCancel($order_seq, $hostName);
    break;
}
/////////// ͥ���٤��ѹ����å�(���󥯥����)
while (isset($_REQUEST['priority_inc'])) {
    $order_seq = $_REQUEST['priority_inc'];
    if (!user_check($_SESSION['User_ID'], 3)) break;
    $ymd       = $_REQUEST['ymd'];
    ////////// maximum check
    $chk_sql = "SELECT max(priority) FROM inspection_recourse WHERE wantdate = '{$ymd} 170000' AND order_seq != {$order_seq}";
    if (getUniResult($chk_sql, $priority_max) > 0) {
        $priority_max++;
        $chk_sql = "SELECT priority FROM inspection_recourse WHERE order_seq = {$order_seq}";
        if (getUniResult($chk_sql, $priority) > 0) {
            $priority++;
            if ($priority > $priority_max) $priority = $priority_max;
            ////////// UPDATE
            $update = "UPDATE inspection_recourse SET priority = {$priority} WHERE order_seq = {$order_seq}";
            if (query_affected($update) <= 0) {
                $_SESSION['s_sysmsg'] = 'ͥ���٤򲼤����������ޤ���Ǥ�����';
            }
        }
    }
    // ��ʬ�ʳ���Ʊ�����դ��ʤ����䥨�顼�ξ��ϲ��⤷�ʤ�
    break;
}
/////////// ͥ���٤��ѹ����å�(�ǥ������)
while (isset($_REQUEST['priority_dec'])) {
    $order_seq = $_REQUEST['priority_dec'];
    if (!user_check($_SESSION['User_ID'], 3)) break;
    $ymd       = $_REQUEST['ymd'];
    ////////// minimum check
    $chk_sql = "SELECT min(priority) FROM inspection_recourse WHERE wantdate = '{$ymd} 170000' AND order_seq != {$order_seq}";
    if (getUniResult($chk_sql, $priority_min) > 0) {
        $priority_min--;
        $chk_sql = "SELECT priority FROM inspection_recourse WHERE order_seq = {$order_seq}";
        if (getUniResult($chk_sql, $priority) > 0) {
            $priority--;
            if ($priority < $priority_min) $priority = $priority_min;
            ////////// UPDATE
            $update = "UPDATE inspection_recourse SET priority = {$priority} WHERE order_seq = {$order_seq}";
            if (query_affected($update) <= 0) {
                $_SESSION['s_sysmsg'] = 'ͥ���٤򲼤����������ޤ���Ǥ�����';
            }
        }
    }
    // ��ʬ�ʳ���Ʊ�����դ��ʤ����䥨�顼�ξ��ϲ��⤷�ʤ�
    break;
}
/////////// ��˾�����ѹ����å�
while (isset($_REQUEST['wantdate'])) {
    $order_seq = $_REQUEST['order_seq'];
    if (!user_check($_SESSION['User_ID'], 3)) break;
    $wantdate  = $_REQUEST['wantdate'];
    if ($wantdate < date('Ymd')) {
        $_SESSION['s_sysmsg'] = '��˾��������������ˤ�����Ͻ���ޤ���Ǥ�����';
        break;
    }
    ////////// UPDATE
    $update = "UPDATE inspection_recourse SET wantdate = '{$wantdate} 170000' WHERE order_seq = {$order_seq}";
    if (query_affected($update) <= 0) {
        $_SESSION['s_sysmsg'] = '��˾�����ѹ������������ޤ���Ǥ�����';
    }
    break;
}

if ($select == 'inspc') {
    ////// Ǽ�����ν��� AND proc.locate != '52   ' �λ������ �ѹ������ڤ�Ǽ���������礬���뤿��
    if ($div == 'C') {
        $where_div = "data.parts_no like 'C%'";
    }
    if ($div == 'SC') {
        $where_div = "data.parts_no like 'C%' AND data.kouji_no like '%SC%'";
    }
    if ($div == 'CS') {
        $where_div = "data.parts_no like 'C%' AND data.kouji_no not like '%SC%'";
    }
    if ($div == 'L') {
        $where_div = "data.parts_no like 'L%'";
    }
    if ($div == 'T') {
        $where_div = "data.parts_no like 'T%'";
    }
    if ($div == 'F') {
        $where_div = "data.parts_no like 'F%'";
    }
    if ($div == 'A') {
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%')";
    }
    if ($div == 'N') {
        $where_div = "uke_no <= 500000 AND uke_no >= 400000 AND (data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%')";
    }
    if ($div == 'NKB') {
        $where_div = "plan.locate = '14'";
    }
    $query = "SELECT  to_char(ins.wantdate, 'MM/DD')                    AS ��˾��
                    , to_char(ins.wantdate, 'YYYYMMDD')                 AS ymd
                    , trim(substr(usr.name, 1, 3))                      AS �����
                    , CASE
                            WHEN uke_date = 0 THEN '---'
                            ELSE substr(to_char(uke_date, 'FM9999/99/99'), 6, 5)
                      END                                               AS uke_date
                    , data.order_seq                                    AS order_seq
                    , to_char(data.order_seq,'FM000-0000')              AS ȯ��Ϣ��
                    , CASE
                            WHEN data.uke_no = '' THEN '---'
                            WHEN data.uke_no IS NULL THEN '---'     -- 2005/05/26 ADD
                            ELSE data.uke_no
                      END                                               AS uke_no
                    , data.parts_no                                     AS parts_no
                    , replace(midsc, ' ', '')                           AS parts_name
                    , CASE
                            WHEN trim(mzist) = '' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE mzist
                      END                                               AS parts_zai
                    , CASE
                            WHEN trim(mepnt) = '' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE substr(mepnt, 1, 8)
                      END                                               AS parts_parent
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
                    , CASE
                            WHEN (SELECT order_seq FROM inspection_holding WHERE order_seq=data.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL) IS NULL
                            THEN ''
                            ELSE '������'
                      END                       AS hold_flg
                FROM
                    inspection_recourse     AS ins
                LEFT OUTER JOIN
                    order_data              AS data  on(data.order_seq=ins.order_seq)
                LEFT OUTER JOIN
                    order_process           AS proc
                                                using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan              AS plan     USING (sei_no)
                LEFT OUTER JOIN
                    vendor_master           AS mast  on(data.vendor=mast.vendor)
                LEFT OUTER JOIN
                    miitem                      on(data.parts_no=mipn)
                LEFT OUTER JOIN
                    acceptance_kensa        AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    user_detailes           AS usr  on(ins.uid=usr.uid)
                WHERE
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    {$where_div}
                ORDER BY
                    ins.wantdate ASC, ins.priority ASC, ins.regdate ASC
                OFFSET 0
                LIMIT 1000
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>��������ǡ���������ޤ���</font>";
        $view = 'NG';
    } else {
        $view = 'OK';
        $maxDate = 19;                  // 10���� �� 17����(2006/08/31 change)��19����(preDate�������б��ˤ�������)
        $preDate = 3;                   //  3�������� (2007/01/18 preDate���ɲä������б���)
        $timestamp = time();            //  E_STRICT��mktime()��time()��ɸ��˽����褦�˥�å��������Ф�����
        for ($i=0; $i<$preDate; $i++) {
            $timestamp -= 86400;
            while (day_off($timestamp)) {
                $timestamp -= 86400;
            }
        }
        $chgdate = array(); $fmtdate = array();
        for ($i=0; $i<$maxDate; $i++) {
            while (day_off($timestamp)) {
                $timestamp += 86400;
            }
            $chgdate[$i] = date('Ymd', $timestamp);
            $fmtdate[$i] = date('m/d', $timestamp);
            $timestamp += 86400;
        }
    }
}

/////////// ��ư�����ȼ�ư�����ξ���ڴ���
if ($select == 'graph') {
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
    left:     1px;
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
function inspection_recourse_del(order_seq, parts_no, parts_name) {
    if (confirm('�����ֹ桧' + parts_no + '\n\n����̾�Ρ�' + parts_name + "\n\n���������ֺ���פ��ޤ���\n\n�������Ǥ�����")) {
        // �¹Ԥ��ޤ���
        document.inspection_form.del_order_seq.value = order_seq;
        document.inspection_form.submit();
    } else {
        alert('��ä��ޤ�����');
    }
}
function wantdate_chg(order_seq, old_date) {
    if (!(new_date = prompt('��˾������λ�����ѹ����Ʋ�������', old_date))) {
        return;
    }
}
function inspection_time(parts_no, parts_name, str_timestamp, end_timestamp) {
    alert('�����ֹ桧' + parts_no + '\n\n����̾�Ρ�' + parts_name + '\n\n��������������' + str_timestamp + '\n\n������λ������' + end_timestamp);
}
function vendor_code_view(vendor, vendor_name) {
    alert('ȯ���襳���ɡ�' + vendor + '\n\nȯ����̾��' + vendor_name + '\n\n');
}
// -->
</script>
<form name='inspection_form' method='get' action='inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?= $menu->out_self() ?>'>
    <input type='hidden' name='del_order_seq' value=''>
</form>
<form name='reload_form' action='inspection_recourse_List.php<?php if ($order_seq != '') echo "#{$order_seq}"; ?>' method='get' target='_self'>
    <input type='hidden' name='order_seq' value='<?=$order_seq?>'>
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
        <?php } elseif ($select == 'inspc') { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='<?php if ($display=='normal') echo 96;else echo 94;?>' nowrap colspan='2' style='font-size:10pt;'>�������Ͻ�λ</th>
            <th class='winbox' width='50' nowrap style='font-size:10pt;'>������</th>
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>����No</th>
            <th class='winbox' width='155' nowrap>�����ֹ桦̾��</th>
            <th class='winbox' width='90' nowrap style='font-size:11pt;'>���/�Ƶ���</th>
            <th class='winbox' width='70' nowrap>���տ�</th>
            <th class='winbox' width='35' nowrap style='font-size:9.5pt;'>����</th>
            <th class='winbox' width='130' nowrap>Ǽ����</th>
            <th class='winbox' width='37' nowrap style='font-size:8.5pt;'>�����</th>
            <th class='winbox' width='90' nowrap style='font-size:11pt;'>��˾��</th>
            <?php if ($display == 'wide') { ?>
            <th class='winbox' width='80' nowrap>�����ֹ�</th>
            <th class='winbox' width='78' nowrap>ȯ��Ϣ��</th>
            <th class='winbox' width='70' nowrap>��¤�ֹ�</th>
            <th class='winbox' width='127' nowrap>������</th>
            <?php } ?>
            -->
        <?php
            $r = 0;
            foreach ($res as $rec) {
                $r++;
                if ($rec['end_timestamp']) $winbox = 'winbox_gray'; else $winbox = 'winbox';
                if ($rec['order_seq'] == $order_seq) $winbox = 'winbox_mark'; else $winbox = 'winbox';
                if ($rec['hold_flg'] == '������') {
                    echo "<tr style='color:gray;'>\n";
                } else {
                    echo "<tr>\n";
                }
                if ($rec['str_timestamp']) { // ���֥륯��å��Ǹ������ϻ��֤Ƚ�λ���֤�ɽ�� 2005/02/21 �ɲ�
                    if ($rec['end_timestamp']) {
                        $inspec = " onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"{$rec['end_timestamp']}\")'";
                    } else {
                        $inspec = " onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"̤��λ\")'";
                    }
                } else {
                    ///// ���֥륯��å��Ƕ۵޸�������κ���������
                    $inspec = " onDblClick='inspection_recourse_del(\"{$rec['order_seq']}\",\"{$rec['parts_no']}\",\"{$rec['parts_name']}\")'";
                }
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='inspection_recourse_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$r}</a></td>\n";
                if ($rec['str_timestamp']) {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44 nowrap'>����</td>\n";
                    } else {
                        if ($rec['hold_flg'] == '������') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>����</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspection_recourse_List.php?end={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:yellow;'>����</a></td>\n";
                        }
                    }
                } else {
                    echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspection_recourse_List.php?str={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>����</a></td>\n";
                }
                if ( ($rec['str_timestamp']) || ($rec['end_timestamp']) ) {
                    if ( ($rec['str_timestamp']) && ($rec['end_timestamp']) ) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap  bgcolor='#d6d3ce'><a href='inspection_recourse_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:gray;'>����</a></td>\n";
                    } else {
                        if ($rec['hold_flg'] == '������') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>���</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap  bgcolor='#d6d3ce'><a href='inspection_recourse_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:red;'>���</a></td>\n";
                        }
                    }
                } else {
                    echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold; color:gray ;' align='center' width='44' nowrap>���</td>\n";
                }
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='50'  nowrap><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap>{$rec['uke_no']}</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='155' nowrap onClick='win_open(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a><br>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 14) . "</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'  nowrap>" . mb_substr(mb_convert_kana($rec['parts_zai'], 'k'), 0, 8) . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'  nowrap>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'  nowrap>{$rec['pro_mark']}</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' nowrap onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:14; font-weight:bold;' align='center' width='37'  nowrap>{$rec['�����']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='center' width='90'  nowrap>\n";
                echo "  <form name='wantdate_form{$r}' method='get' action='inspection_recourse_List.php?{$uniq}#{$rec['order_seq']}' target='_self'>\n";
                echo "    <input type='hidden' name='order_seq' value='{$rec['order_seq']}'>\n";
                echo "    <select name='wantdate' style='font-size:11pt; font-weight:bold;' onChange='document.wantdate_form{$r}.submit()'>\n";
                for ($i=0; $i<$maxDate; $i++) {
                    if($fmtdate[$i] == $rec['��˾��']) {
                        echo "    <option value='{$chgdate[$i]}' selected>{$rec['��˾��']}</option>\n";
                    } else {
                        echo "    <option value='{$chgdate[$i]}'>{$fmtdate[$i]}</option>\n";
                    }
                }
                echo "    </select>\n";
                echo "  </form>\n";
                echo "    <a href='inspection_recourse_List.php?priority_dec={$rec['order_seq']}&ymd={$rec['ymd']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>��</a>\n";
                echo "    <a href='inspection_recourse_List.php?priority_inc={$rec['order_seq']}&ymd={$rec['ymd']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>��</a>\n";
                echo "</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80'  nowrap>{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='78'  nowrap>{$rec['ȯ��Ϣ��']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'  nowrap>{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='127' nowrap>{$rec['������']}</td>\n";
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
<script type='text/javascript' language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // ������ѣ�����
// -->
</script>
<?=$menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
