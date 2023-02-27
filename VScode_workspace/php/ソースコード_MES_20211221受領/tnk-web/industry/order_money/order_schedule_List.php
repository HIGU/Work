<?php
//////////////////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ���ۤξȲ񥰥��                                                             //
// Copyright (C) 2009-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp                //
// Changed history                                                                      //
// 2009/11/09 Created  order_schedule_List.php(/order��/order_money�˥��ԡ�����ή��     //
// 2010/05/26 �����ȥ뤬�㤦�Τǽ���                                                    //
// 2015/05/25 ����ʬ����ɽ������褦���ѹ����ġ����C/L��Ʊ�ͤ�����ɽ��               //
// 2018/06/29 ¿�����T���ʹ������б�                                              ��ë //
//////////////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function (define.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
require_once ('order_function.php');        // order �ط��ζ��� function
require_once ('../../tnk_func.php');        // TNK date_offset()�ǻ���
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
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
// $menu->set_action('ͽ������', INDUST . 'order/order_details/order_details.php');
$menu->set_action('ͽ������', INDUST . 'order/order_details/order_details_Main.php');
$menu->set_action('ͽ�����ټ�����', INDUST . 'order/order_details/order_details_next.php');
$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ��ͽ��ȸ����ų����٤ξȲ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

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
} elseif (isset($_REQUEST['insEnd'])) {
    $select = 'insEnd';                     // �����ѥꥹ��
    $_SESSION['select'] = 'insEnd';         // ���å�������¸
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'graph';          // ���å�������¸
} elseif (isset($_REQUEST['list'])) {
    $select = 'list';                      // Ǽ��ͽ�꽸��
    $_SESSION['select'] = 'list';          // ���å�������¸
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(���å���󤫤�)
    } else {
        $select = 'graph';                  // �����(Ǽ��ͽ�ꥰ���)���ޤ��̣��̵��
    }
}
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];      // �����ֹ�λ��꤬����и�������
    // $select = 'miken';                      // ̤�����ꥹ��  2007/09/30 �����Ѥȶ��Ѥ��뤿�ᥳ����
    // $_SESSION['select'] = 'miken';          // ���å�������¸
} else {
    $parts_no = '';                         // ������Τ�
}
/////////// �����ֹ�����ȯ���襳���ɤǸ��� ����
$where_parts = true;
if (is_numeric($parts_no) && strlen($parts_no) == 5) {
    // ȯ���襳���ɤǸ���
    $where_parts = false;
    $vendor_query = "SELECT vendor FROM vendor_master WHERE vendor = '{$parts_no}'";
    if (getResult2($vendor_query, $chk_res) <= 0) {
        $where_parts = true;
    }
}
if ($where_parts) {
    // �����ֹ�Ǹ���
    if (preg_match('/\*/', $parts_no)) {
        $parts_no = str_replace('*', '%', $parts_no);   // likeʸ���б�������
    } else {
        $parts_no = ('%' . $parts_no . '%');
    }
}

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
while (isset($_REQUEST['cancel'])) {        // cancel �ϻȤ��ʤ�������ա�
    if (!client_check()) break;
    $order_seq = $_REQUEST['cancel'];
    acceptanceInspectionCancel($order_seq, $hostName);
    break;
}

if ($select == 'miken' || $select == 'insEnd') {
    $where_div = getDivWhereSQL($div);
    if ($parts_no == '') {
        $where_parts = '';                                      // ���⤷�ʤ�
    } elseif ($where_parts) {
        $where_parts = "AND data.parts_no like '{$parts_no}'";  // �����ֹ��like����
    } else {
        $where_parts = "AND data.vendor = '{$parts_no}'";       // ȯ���襳���ɤǸ���(likeʸ�ˤ���Ⱦ嵭�ȽŤʤꤢ���ޤ��ˤʤ��
    }
    if ($select == 'miken') {
        $ken_date = 'ken_date = 0       -- ̤����ʬ';
        $timestamp = "( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )";
    } else {
        $str_date = date_offset(3);
        $end_date = date_offset(0);
        $ken_date = "ken_date = 0 AND end_timestamp IS NOT NULL";
        $timestamp = 'true'; // "ken.end_timestamp IS NOT NULL";
    }
    ////////// SQL Statement �����
    $query = getSQLbody($ken_date, $timestamp, $where_div, $where_parts);
    $res = array();
    if (($rows = getResult($query, $res)) <= 0) {
        if ($select == 'miken') {
            $_SESSION['s_sysmsg'] = "�����ųݤ�����ޤ���";
            if (strlen($parts_no) == 11) {
                $_SESSION['s_sysmsg'] .= ' ' . getItemMaster(str_replace('%', '', $parts_no));
            }
        } else {
            $_SESSION['s_sysmsg'] = "�����ѥǡ���������ޤ���";
        }
        $view = 'NG';
    } else {
        $view = 'OK';
    }
    ////////// �����ѥꥹ�Ȥ�2���ʬ���Ƽ���(��Ŭ���Τ���)
    if ($select == 'insEnd') {
        $ken_date = "ken_date >= {$str_date} AND ken_date <= {$end_date}";
        $query = getSQLbody($ken_date, $timestamp, $where_div, $where_parts);
        $res2 = array();
        if (($rows2=getResult($query, $res2)) <= 0 && $rows <= 0) {
            $_SESSION['s_sysmsg'] = "�����ѥǡ���������ޤ���";
            if (strlen($parts_no) == 11) {
                $_SESSION['s_sysmsg'] .= ' ' . getItemMaster(str_replace('%', '', $parts_no));
            }
            $view = 'NG';
        } else {
            $_SESSION['s_sysmsg'] = '';
            $i = $rows;
            foreach ($res2 as $tmpArray) {
                foreach ($tmpArray as $key => $value) {
                    $res[$i][$key] = $value;
                }
                ++$i;
            }
            $view = 'OK';
        }
    }
} elseif ($select == 'graph') {
    //////////// �����ų�ʬ(̤�������)�ι�פ����
    $where_div = getDivWhereSQL($div);
    $query = "SELECT  sum(data.order_q * data.order_price)
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
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
                LIMIT 1
    ";
    if (getUniResult($query, $res_miken) <= 0) {
        $res_miken = 0;
    }
    ////////// �����ޤǤ���ķ�������
    $yesterday = date('Ymd', time() - 86400);
    $lower_limit_day = date('Ymd', time() - (86400*200));
    //$upper_limit_day = date('Ymd', time() + (86400*93));
    $upper_limit_day_t = date('Ym');
    $upper_limit_day = $upper_limit_day_t . '31';
    if ($div == 'C') {
        $all_title = '���ץ����� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "data.parts_no like 'C%' AND proc.locate != '52   '";
        $graph_title = '���ץ����� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '���ץ����� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'C%' AND proc.locate != '52   '";
        $graph2_title = '���ץ����� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '���ץ����� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'SC') {
        $all_title = '���ץ������� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no like '%SC%' AND proc.locate != '52   '";
        $graph_title = '���ץ������� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '���ץ������� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no like '%SC%' AND proc.locate != '52   '";
        $graph2_title = '���ץ������� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '���ץ������� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'CS') {
        $all_title = '���ץ�ɸ���� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $graph_title = '���ץ�ɸ���� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '���ץ�ɸ���� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $graph2_title = '���ץ�ɸ���� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '���ץ�ɸ���� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'L') {
        $all_title = '��˥����� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "data.parts_no like 'L%' AND proc.locate != '52   '";
        $graph_title = '��˥����� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '��˥����� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'L%' AND proc.locate != '52   '";
        $graph2_title = '��˥����� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '��˥����� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'T') {
        $all_title = '�Ƶ��郎�������� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "data.parts_no like 'T%' AND proc.locate != '52   '";
        $graph_title = '�Ƶ��郎�������� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '�Ƶ��郎�������� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'T%' AND proc.locate != '52   '";
        $graph2_title = '�Ƶ��郎�������� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '�Ƶ��郎�������� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'F') {
        $all_title = '�Ƶ��郎�ƣ����� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "data.parts_no like 'F%' AND proc.locate != '52   '";
        $graph_title = '�Ƶ��郎�ƣ����� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '�Ƶ��郎�ƣ����� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'F%' AND proc.locate != '52   '";
        $graph2_title = '�Ƶ��郎�ƣ����� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '�Ƶ��郎�ƣ����� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'A') {
        $all_title = '�������� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate != '52   '";
        $graph_title = '�������� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '�������� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate != '52   '";
        $graph2_title = '�������� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '�������� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'N') {
        $all_title = '���칩��(���ץ�) Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate = '52   '";
        $graph_title = '���칩��(���ץ�) Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '���칩��(���ץ�) Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate = '52   '";
        $graph2_title = '���칩��(���ץ�) ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '���칩��(���ץ�) ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'NKB') {
        $all_title = '�Σˣ� Ǽ��ͽ�� �����׶�ۡ�';
        $where_div = "plan.locate = '14'";
        $graph_title = '�Σˣ� Ǽ��ͽ�� �����ۥ���� (��ʸ��ȯ�ԺѤ�)������ߡ�';
        $total_title = '�Σˣ� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "plan.locate = '14'";
        $graph2_title = '�Σˣ� ������ Ǽ��ͽ�� �����ۥ���� (��ʸ��̤ȯ��)������ߡ�';
        $total2_title = '�Σˣ� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    //////////// Ǽ���٤�ʬ�ι�פ����
    $query = "SELECT sum(data.order_q * data.order_price)
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan) <= 0) {
        $res_zan = 0;
    }
    //////////// ������դ�ͽ���� ����  2007/09/22 ADD
    $page = 22;
    $maxrows = 66;
    //////////// �ڡ������ե��å�����
    $offset = $session->get_local('offset');
    if ($offset == '') $offset = 0;         // �����
    if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
        $offset += $page;
        if ($offset >= $maxrows) {
            $offset -= $page;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            } else {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            }
        }
    } elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
        $offset -= $page;
        if ($offset < 0) {
            $offset = 0;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            } else {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            }
        }
    } elseif ( isset($_REQUEST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
        $offset = $offset;
    } elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ�����
        $offset = $offset;
    } else {
        $offset = 0;                            // ���ξ��ϣ��ǽ����
    }
    $session->add_local('offset', $offset);
    
    /////////// �����ʹߤΥ��ޥ꡼�����
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "��ĥǡ���������ޤ���";
        $view = 'NG';
        $month_all = 0;
        $month_total  = 0;
    } else {
        $view = 'OK';
        $datax = array(); $datay = array();
        $datax[0] = mb_convert_encoding('�����ų�', 'UTF-8');
        $datax_color[0] = 'blue';
        $datay[0] = $res_miken / 1000;
        $datax[1] = mb_convert_encoding('Ǽ���٤�', 'UTF-8');
        $datay[1] = $res_zan / 1000;
        $datax_color[1] = 'darkred';
        $month_total = $res_miken + $res_zan;
        for ($i=0; $i<$rows; $i++) {
            $datax[$i+2] = $res[$i]['delivery'];
            $datay[$i+2] = ($res[$i]['kin'] / 1000);
            $month_total += $res[$i]['kin'];
            $datax_color[$i+2] = 'black';
        }
        $month_all = $month_total;
        $month_total  = number_format($month_total, 0);
        require_once ('../../../jpgraph.php');
        require_once ('../../../jpgraph_bar.php');
        $graph = new Graph(820, 360);               // ����դ��礭�� X/Y
        $graph->SetScale('textlin'); 
        $graph->img->SetMargin(50, 30, 40, 70);    // ����հ��֤Υޡ����� �����岼
        $graph->SetShadow(); 
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
        $graph->title->Set(mb_convert_encoding($graph_title, 'UTF-8')); 
        $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph->yaxis->title->Set(mb_convert_encoding('���', 'UTF-8'));
        $graph->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph->xaxis->SetTickLabels($datax, $datax_color); // ��������
        // $graph->xaxis->SetFont(FF_FONT1);     // �ե���Ȥϥܡ���ɤ����Ǥ��롣
        $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);     // �ե���Ȥϥܡ���ɤ����Ǥ��롣
        $graph->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot = new BarPlot($datay); 
        $bplot->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot->SetColor('navy');
        $bplot->value->SetFormat('%d');     // �����ե����ޥå�
        $bplot->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/26 �ɲ�
        $bplot->value->Show();              // ����ɽ��
        $targ = array();
        $alts = array();
        $targ[0] = "";
        $alts[0] = '�����ųݤζ�ۡ�%3d';
        $targ[1] = "JavaScript:win_open3('" . $menu->out_action('ͽ������') . "?date=OLD')";
        $alts[1] = 'Ǽ���٤�ζ�ۡ�%3d';
        for ($i=0; $i<$rows; $i++) {
            $targ[$i+2] = "JavaScript:win_open('" . $menu->out_action('ͽ������') . "?date={$datax[$i+2]}')";
            $alts[$i+2] = "'{$datax[$i+2]}��Ǽ��ͽ�� ��ۡ�%3d";
        }
        $bplot->SetCSIMTargets($targ, $alts); 
        $graph->Add($bplot);
        // $graph_name = ('graph/order' . session_id() . '.png');
        $graph_name = "graph/order_schedule_{$_SESSION['User_ID']}.png";
        $graph->Stroke($graph_name);
        chmod($graph_name, 0666);                   // file������rw�⡼�ɤˤ���
    }
    //////////// ����գ� ��������(��ʸ��̤ȯ��)��Ǽ��ͽ�ꥰ��դκ��� //////////////
    //////////// Ǽ���٤�ʬ�ι�פ����
    $query = "SELECT sum(plan.order_q * proc.order_price)
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div2}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan2) <= 0) {
        $res_zan2 = 0;
    }
    /////////// �����ʹߤΥ��ޥ꡼�����
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- ��ʸ��ȯ�ԺѤߤ�+1 2007/09/22 �ѹ��ˤ��嵭����դ�Ʊ��
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "����������ĥǡ���������ޤ���";
        $view_graph2 = 'NG';
        $month_all  = number_format($month_all, 0);
        $month_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $datax2 = array(); $datay2 = array();
        $datax2[0] = mb_convert_encoding('Ǽ���٤�', 'UTF-8');
        $datax2_color[0] = 'darkred';
        $datay2[0] = $res_zan2 / 1000;
        $month_total2 = $res_zan2;
        for ($i=0; $i<$rows; $i++) {
            $datax2[$i+1] = $res2[$i]['delivery'];
            $datay2[$i+1] = ($res2[$i]['kin'] / 1000);
            //$datay2[$i+1] = $res2[$i]['cnt'];
            $month_total2 += $res2[$i]['kin'];
            $datax2_color[$i+1] = 'black';
        }
        $month_all = $month_all + $month_total2;
        $month_total2  = number_format($month_total2, 0);
        $month_all  = number_format($month_all, 0);
        require_once ('../../../jpgraph.php');
        require_once ('../../../jpgraph_bar.php');
        $graph2 = new Graph(820, 360);               // ����դ��礭�� X/Y
        $graph2->SetScale('textlin'); 
        $graph2->img->SetMargin(50, 30, 40, 70);    // ����հ��֤Υޡ����� �����岼
        $graph2->SetShadow(); 
        $graph2->title->SetFont(FF_GOTHIC, FS_NORMAL, 14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
        $graph2->title->Set(mb_convert_encoding($graph2_title, 'UTF-8')); 
        $graph2->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph2->yaxis->title->Set(mb_convert_encoding('���', 'UTF-8'));
        $graph2->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph2->xaxis->SetTickLabels($datax2, $datax2_color); // ��������
        // $graph2->xaxis->SetFont(FF_FONT1);     // �ե���Ȥϥܡ���ɤ����Ǥ��롣
        $graph2->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);     // �ե���Ȥϥܡ���ɤ����Ǥ��롣
        $graph2->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot2 = new BarPlot($datay2); 
        $bplot2->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot2->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot2->SetColor('navy');
        $bplot2->value->SetFormat('%d');     // �����ե����ޥå�
        $bplot2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/26 �ɲ�
        $bplot2->value->Show();              // ����ɽ��
        $targ2 = array();
        $alts2 = array();
        $targ2[0] = "JavaScript:win_open('" . $menu->out_action('ͽ�����ټ�����') . "?date=OLD')";
        $alts2[0] = 'Ǽ���٤�ζ�ۡ�%3d';
        for ($i=0; $i<$rows; $i++) {
            $targ2[$i+1] = "JavaScript:win_open('" . $menu->out_action('ͽ�����ټ�����') . "?date={$datax2[$i+1]}')";
            $alts2[$i+1] = "'{$datax2[$i+1]}��Ǽ��ͽ�� ��ۡ�%3d";
        }
        $bplot2->SetCSIMTargets($targ2, $alts2); 
        $graph2->Add($bplot2);
        $graph2_name = "graph/order_schedule_next_{$_SESSION['User_ID']}.png";
        $graph2->Stroke($graph2_name);
        chmod($graph2_name, 0666);                   // file������rw�⡼�ɤˤ���
    }
} elseif ($select == 'list') {
    ////////// �����ޤǤ���ķ�������
    $yesterday = date('Ymd', time() - 86400);
    $lower_limit_day = date('Ymd', time() - (86400*200));
    //$upper_limit_day = date('Ymd', time() + (86400*93));
    $upper_limit_day_t = date('Ym');
    $upper_limit_day = $upper_limit_day_t . '31';
    ///// �о�����
    if (substr($upper_limit_day_t,4,2)!=01) {
        $b1_ym = $upper_limit_day_t - 1;
    } else {
        $b1_ym = $upper_limit_day_t - 100;
        $b1_ym = $b1_ym + 11;
    }
    ///// �о����
    if (substr($upper_limit_day_t,4,2)!=12) {
        $p2_ym = $upper_limit_day_t + 1;
    } else {
        $p2_ym = $upper_limit_day_t + 100;
        $p2_ym = $p2_ym - 11;
    }
    ///// �о��⡹��
    if (substr($p2_ym,4,2)!=12) {
        $p3_ym = $p2_ym + 1;
    } else {
        $p3_ym = $p2_ym + 100;
        $p3_ym = $p3_ym - 11;
    }
    $dd_today_t = date('Ymd');                    // ����ǯ����
    $dd_today   = substr($dd_today_t,6,2);        // ��������
    $str_dayb1  = $b1_ym . '01';                  // ���ʬ������
    $end_dayb1  = $b1_ym . '31';                  // ���ʬ��λ��
    $yyyyb1     = substr($b1_ym,0,4);             // ���ǯ
    $mmb1       = substr($b1_ym,4,2);             // ����
    $str_day    = $upper_limit_day_t . '01';      // ����ʬ������
    $end_day    = $upper_limit_day;               // ����ʬ��λ��
    $yyyy1      = substr($upper_limit_day_t,0,4); // ����ǯ
    $mm1        = substr($upper_limit_day_t,4,2); // �����
    $str_day2   = $p2_ym . '01';                  // ���ʬ������
    $end_day2   = $p2_ym . '31';                  // ���ʬ��λ��
    $yyyy2      = substr($p2_ym,0,4);             // ���ǯ
    $mm2        = substr($p2_ym,4,2);             // ����
    $str_day3   = $p3_ym . '01';                  // �⡹��ʬ������
    $end_day3   = $p3_ym . '31';                  // �⡹��ʬ��λ��
    $yyyy3      = substr($p3_ym,0,4);             // �⡹ǯ
    $mm3        = substr($p3_ym,4,2);             // �⡹��
    //////////// �����ų�ʬ(̤�������)�ι�פ����
    if ($dd_today <= 10) {          // 10���ޤǤ�����ʬ�θ����������ޤǤ�����ʬ�Ȥ��Ƽ���
        $where_div = getDivWhereSQL($div);
        $query = "SELECT  sum(data.order_q * data.order_price)
                    FROM
                        order_data          AS data
                    LEFT OUTER JOIN
                        acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                    LEFT OUTER JOIN
                        order_plan          AS plan     USING (sei_no)
                    WHERE
                        ken_date <= 0       -- ̤����ʬ
                        AND
                        data.sei_no > 0     -- ��¤�ѤǤ���
                        AND
                        (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                        AND
                        ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                        AND
                        uke_date < {$str_day}
                        AND
                        {$where_div}
                    LIMIT 1
        ";
        if (getUniResult($query, $res_mikenb1) <= 0) {
            $res_mikenb1 = 0;
        }
        $where_div = getDivWhereSQL($div);
        $query = "SELECT  sum(data.order_q * data.order_price)
                    FROM
                        order_data          AS data
                    LEFT OUTER JOIN
                        acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                    LEFT OUTER JOIN
                        order_plan          AS plan     USING (sei_no)
                    WHERE
                        ken_date <= 0       -- ̤����ʬ
                        AND
                        data.sei_no > 0     -- ��¤�ѤǤ���
                        AND
                        (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                        AND
                        ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                        AND
                        uke_date >= {$str_day}
                        AND
                        {$where_div}
                    LIMIT 1
        ";
        if (getUniResult($query, $res_miken) <= 0) {
            $res_miken = 0;
        }
    } else {
        $res_mikenb1 = 0;
        $where_div = getDivWhereSQL($div);
        $query = "SELECT  sum(data.order_q * data.order_price)
                    FROM
                        order_data          AS data
                    LEFT OUTER JOIN
                        acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                    LEFT OUTER JOIN
                        order_plan          AS plan     USING (sei_no)
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
                    LIMIT 1
        ";
        if (getUniResult($query, $res_miken) <= 0) {
            $res_miken = 0;
        }
    }
    if ($div == 'C') {
        $all_title = '���ץ����� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '���ץ����� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "data.parts_no like 'C%' AND proc.locate != '52   '";
        $total_title = '���ץ����� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'C%' AND proc.locate != '52   '";
        $total2_title = '���ץ����� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
        $search = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_day, $end_day, $div);
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_dayb1, $end_dayb1, $div);
    }
    if ($div == 'SC') {
        $all_title = '���ץ������� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '���ץ������� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no like '%SC%' AND proc.locate != '52   '";
        $total_title = '���ץ������� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no like '%SC%' AND proc.locate != '52   '";
        $total2_title = '���ץ������� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'CS') {
        $all_title = '���ץ�ɸ���� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '���ץ�ɸ���� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $total_title = '���ץ�ɸ���� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $total2_title = '���ץ�ɸ���� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'L') {
        $all_title = '��˥����� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '��˥����� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "data.parts_no like 'L%' AND proc.locate != '52   '";
        $total_title = '��˥����� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'L%' AND proc.locate != '52   '";
        $total2_title = '��˥����� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
        //$search = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_day, $end_day, $div);
        //$searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_dayb1, $end_dayb1, $div);
        $search = sprintf("where act_date>=%d and act_date<=%d and div='%s' and parts_no not like '%s'", $str_day, $end_day, $div, 'T%');
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s' and parts_no not like '%s'", $str_dayb1, $end_dayb1, $div, 'T%');
    }
    if ($div == 'T') {
        $all_title = '�Ƶ��郎�������� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '�Ƶ��郎�������� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "data.parts_no like 'T%' AND proc.locate != '52   '";
        $total_title = '�Ƶ��郎�������� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'T%' AND proc.locate != '52   '";
        $total2_title = '�Ƶ��郎�������� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
        //$search = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_day, $end_day, $div);
        //$searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_dayb1, $end_dayb1, $div);
        $search = sprintf("where act_date>=%d and act_date<=%d and (div='%s' or (div<>'T' and div<>'C' and parts_no like '%s'))", $str_day, $end_day, $div, 'T%');
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d and (div='%s' or (div<>'T' and div<>'C' and parts_no like '%s'))", $str_dayb1, $end_dayb1, $div, 'T%');
    }
    if ($div == 'F') {
        $all_title = '�Ƶ��郎�ƣ����� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '�Ƶ��郎�ƣ����� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "data.parts_no like 'F%' AND proc.locate != '52   '";
        $total_title = '�Ƶ��郎�ƣ����� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "proc.parts_no like 'F%' AND proc.locate != '52   '";
        $total2_title = '�Ƶ��郎�ƣ����� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'A') {
        $all_title = '�������� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '�������� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate != '52   '";
        $total_title = '�������� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate != '52   '";
        $total2_title = '�������� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
        $search = sprintf("where act_date>=%d and act_date<=%d", $str_day, $end_day);
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d", $str_dayb1, $end_dayb1);
    }
    if ($div == 'N') {
        $all_title = '���칩��(���ץ�) Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '���칩��(���ץ�) Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate = '52   '";
        $total_title = '���칩��(���ץ�) Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate = '52   '";
        $total2_title = '���칩��(���ץ�) ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    if ($div == 'NKB') {
        $all_title = '�Σˣ� Ǽ��ͽ�� �����׶�ۡ�';
        $list_title = '�Σˣ� Ǽ��ͽ���۽��ס���ñ�̡��ߡ�';
        $where_div = "plan.locate = '14'";
        $total_title = '�Σˣ� Ǽ��ͽ�� ������ (��ʸ��ȯ�ԺѤ�)��';
        $where_div2 = "plan.locate = '14'";
        $total2_title = '�Σˣ� ������ Ǽ��ͽ�� ������ (��ʸ��̤ȯ��)��';
    }
    //////////// ��ݶ�ۤμ�������������
    if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) {
        $query = sprintf('select count(*), sum(Uround(order_price * siharai,0)) from act_payable %s', $searchb1);
        $res_max = array();
        if ( getResult2($query, $res_max) <= 0) {         // $maxrows �μ���
            $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
            exit();
        } else {
            $maxrowsb1 = $res_max[0][0];                  // ��ץ쥳���ɿ��μ���
            $sum_kinb1 = $res_max[0][1];                  // �����ݶ�ۤμ���
        }
    } else {
        $sum_kinb1 = 0;
    }
    $month_totalb1 = $res_mikenb1;
    $month_allb1 = $month_totalb1;
    $month_sumb1 = $month_allb1 + $sum_kinb1;
    $month_totalb1  = number_format($month_totalb1, 0);
    $sum_kinb1  = number_format($sum_kinb1, 0);
    $month_allb1  = number_format($month_allb1, 0);
    $month_sumb1  = number_format($month_sumb1, 0);
    //////////// ��ݶ�ۤμ�����������������
    if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) {
        $query = sprintf('select count(*), sum(Uround(order_price * siharai,0)) from act_payable %s', $search);
        $res_max = array();
        if ( getResult2($query, $res_max) <= 0) {         // $maxrows �μ���
            $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
            exit();
        } else {
            $maxrows = $res_max[0][0];                  // ��ץ쥳���ɿ��μ���
            $sum_kin = $res_max[0][1];                  // �����ݶ�ۤμ���
        }
    } else {
        $sum_kin = 0;
    }
    //////////// Ǽ���٤�ʬ�ι�פ����
    $query = "SELECT sum(data.order_q * data.order_price)
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan) <= 0) {
        $res_zan = 0;
    }
    //////////// ������դ�ͽ���� ����  2007/09/22 ADD
    $page = 22;
    $maxrows = 66;
    //////////// �ڡ������ե��å�����
    $offset = $session->get_local('offset');
    if ($offset == '') $offset = 0;         // �����
    if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
        $offset += $page;
        if ($offset >= $maxrows) {
            $offset -= $page;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            } else {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            }
        }
    } elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
        $offset -= $page;
        if ($offset < 0) {
            $offset = 0;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            } else {
                $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
            }
        }
    } elseif ( isset($_REQUEST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
        $offset = $offset;
    } elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ�����
        $offset = $offset;
    } else {
        $offset = 0;                            // ���ξ��ϣ��ǽ����
    }
    $session->add_local('offset', $offset);
    
    /////////// �����ʹߤΥ��ޥ꡼�����(�����
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "��ĥǡ���������ޤ���";
        $view = 'NG';
        $month_all = 0;
        $month_total  = 0;
    } else {
        $view = 'OK';
        $month_total = $res_miken + $res_zan;
        for ($i=0; $i<$rows; $i++) {
            $month_total += $res[$i]['kin'];
        }
        $month_all = $month_total;
        $month_total  = number_format($month_total, 0);
    }
    /////////// �����ʹߤΥ��ޥ꡼�����(����
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_day2}
                    AND
                    proc.delivery <= {$end_day2}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "��ĥǡ���������ޤ���";
        $view = 'NG';
        $month2_all = 0;
        $month2_total  = 0;
    } else {
        $view = 'OK';
        $month2_total = 0;
        for ($i=0; $i<$rows; $i++) {
            $month2_total += $res[$i]['kin'];
        }
        $month2_all = $month2_total;
        $month2_total  = number_format($month2_total, 0);
    }
    /////////// �����ʹߤΥ��ޥ꡼�����(�⡹���
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_day3}
                    AND
                    proc.delivery <= {$end_day3}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "��ĥǡ���������ޤ���";
        $view = 'NG';
        $month3_all = 0;
        $month3_total  = 0;
    } else {
        $view = 'OK';
        $month3_total = 0;
        for ($i=0; $i<$rows; $i++) {
            $month3_total += $res[$i]['kin'];
        }
        $month3_all = $month3_total;
        $month3_total = number_format($month3_total, 0);
    }
    //////////// ����գ� ��������(��ʸ��̤ȯ��)��Ǽ��ͽ�ꥰ��դκ��� //////////////
    //////////// Ǽ���٤�ʬ�ι�פ����
    $query = "SELECT sum(plan.order_q * proc.order_price)
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div2}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan2) <= 0) {
        $res_zan2 = 0;
    }
    /////////// �����ʹߤΥ��ޥ꡼�����(����)
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- ��ʸ��ȯ�ԺѤߤ�+1 2007/09/22 �ѹ��ˤ��嵭����դ�Ʊ��
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "����������ĥǡ���������ޤ���";
        $view_graph2 = 'NG';
        $month_sum = $month_all + $sum_kin;
        $sum_kin  = number_format($sum_kin, 0);
        $month_all  = number_format($month_all, 0);
        $month_sum  = number_format($month_sum, 0);
        $month_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $month_total2 = $res_zan2;
        for ($i=0; $i<$rows; $i++) {
            $month_total2 += $res2[$i]['kin'];
        }
        $month_all = $month_all + $month_total2;
        $month_sum = $month_all + $sum_kin;
        $month_total2  = number_format($month_total2, 0);
        $sum_kin  = number_format($sum_kin, 0);
        $month_all  = number_format($month_all, 0);
        $month_sum  = number_format($month_sum, 0);
    }
    /////////// �����ʹߤΥ��ޥ꡼�����������
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_day2}
                    AND
                    proc.delivery <= {$end_day2}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- ��ʸ��ȯ�ԺѤߤ�+1 2007/09/22 �ѹ��ˤ��嵭����դ�Ʊ��
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "����������ĥǡ���������ޤ���";
        $view_graph2 = 'NG';
        $month2_all  = number_format($month2_all, 0);
        $month2_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $month2_total2 = 0;
        for ($i=0; $i<$rows; $i++) {
            $month2_total2 += $res2[$i]['kin'];
        }
        $month2_all = $month2_all + $month2_total2;
        $month2_total2  = number_format($month2_total2, 0);
        $month2_all  = number_format($month2_all, 0);
    }
    /////////// �����ʹߤΥ��ޥ꡼�����������
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_day3}
                    AND
                    proc.delivery <= {$end_day3}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- ��ʸ��ȯ�ԺѤߤ�+1 2007/09/22 �ѹ��ˤ��嵭����դ�Ʊ��
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "����������ĥǡ���������ޤ���";
        $view_graph2 = 'NG';
        $month3_all  = number_format($month3_all, 0);
        $month3_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $month3_total2 = 0;
        for ($i=0; $i<$rows; $i++) {
            $month3_total2 += $res2[$i]['kin'];
        }
        $month3_all = $month3_all + $month3_total2;
        $month3_total2  = number_format($month3_total2, 0);
        $month3_all  = number_format($month3_all, 0);
    }
}

/////////// ��ư�����ȼ�ư�����ξ���ڴ���
if ($select == 'graph') {
    $auto_reload = 'off';
} elseif ($select == 'list') {
    $auto_reload = 'off';
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php // if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
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
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
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
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
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
<script type='text/javascript' language='JavaScript'>
<!--
function init() {
     setInterval('document.reload_form.submit()', 30000);   // 30��
     //  onLoad='init()' ������� <body>������������OK
}
function win_open(url) {
    var w = 820;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open2(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open3(url) {
    var w = 1100;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win3', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('�����ֹ桧' + parts_no + '\n\n����̾�Ρ�' + parts_name + " ��\n\n�۵����� ��������򤷤ޤ���\n\n�������Ǥ�����")) {
        parent.Header.document.form_parts.parts_no.value = "";  // �����ֹ�θ������������� 2007/05/29 �ɲ�
        parent.Header.document.form_parts.parts_no.focus();     // ³�������ϤǤ���褦�˥ե�����������
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
    <input type='hidden' name='retUrl' value='<?php echo $menu->out_self() ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
<form name='reload_form' action='order_schedule_List.php<?php if ($order_seq != '') echo "#{$order_seq}"; ?>' method='get' target='_self'>
    <input type='hidden' name='order_seq' value='<?php echo $order_seq?>'>
</form>
<form name='miken_submit_form' action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
    <input type='hidden' name='miken' value='�����ųݥꥹ��'>
    <input type='hidden' name='div' value='<?php echo $div?>'>
</form>
</head>
<body>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>�ǡ���������ޤ���</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'miken') { ?>
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
                        if ($rec['hold_flg'] == '������') {
                            echo "<tr style='color:gray;' onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"̤��λ\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"{$rec['hold_time']}\")'>\n";
                        } else {
                            echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"̤��λ\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                        }
                    }
                } else {    // ���֥륯��å��Ƕ۵޸������꤬�����
                    echo "<tr onDblClick='inspection_recourse(\"{$rec['order_seq']}\",\"{$rec['parts_no']}\",\"{$rec['parts_name']}\")'>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='order_schedule_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$i}</a></td>\n";
                if ($rec['str_timestamp']) {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>����</td>\n";
                    } else {
                        if ($rec['hold_flg'] == '������') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>����</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='order_schedule_List.php?end={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:yellow;'>����</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='order_schedule_List.php?str={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>����</a></td>\n";
                }
                if ( ($rec['str_timestamp']) || ($rec['end_timestamp']) ) {
                    if ( ($rec['str_timestamp']) && ($rec['end_timestamp']) ) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap><a href='order_schedule_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:gray;'>����</a></td>\n";
                    } else {
                        if ($rec['hold_flg'] == '������') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>���</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap><a href='order_schedule_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:red;'>���</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray ;' align='center' width='44' nowrap>���</td>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'>{$rec['uke_no']}<br><span style='color:gray';>{$rec['delivery']}</span></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91' onClick='win_open2(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                echo "    <a class='link' href='javascript:void(0)' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                // echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91'><a class='link' href='javascript:win_open2(\"{$menu->out_action('�߸˷���')}?parts_no=" . urlencode($rec['parts_no']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='150'>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 27) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'>" . mb_convert_kana($rec['parts_zai'], 'k') . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'>{$rec['pro_mark']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['ȯ��Ϣ��']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70' >{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130'>{$rec['������']}</td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } elseif ($select == 'insEnd') { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
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
                        if ($rec['hold_flg'] == '������') {
                            echo "<tr style='color:gray;' onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"̤��λ\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"{$rec['hold_time']}\")'>\n";
                        } else {
                            echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"̤��λ\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                        }
                    }
                } else {
                    echo "<tr>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='order_schedule_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$i}</a></td>\n";
                if ($rec['str_timestamp'] && $rec['end_timestamp']) {
                    echo "<td class='{$winbox}' style='font-size:12; font-weight:bold; color:gray;' align='center' width='93' colspan'2'>", substr($rec['str_timestamp'], 5), '<br>', substr($rec['end_timestamp'], 5), "</td>\n";
                } elseif ($rec['str_timestamp']) {
                    echo "<td class='{$winbox}' style='font-size:12; font-weight:bold; color:gray;' align='center' width='93' colspan'2'>", substr($rec['str_timestamp'], 5), "<br>̤����</td>\n";
                } else {
                    echo "<td class='{$winbox}' style='font-size:12; font-weight:bold; color:gray;' align='center' width='93' colspan'2'>̤����<br>{$rec['ken_date']}</td>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'>{$rec['uke_no']}<br><span style='color:gray';>{$rec['delivery']}</span></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91' onClick='win_open2(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                if ($rec['ken_date'] == '0000/00/00') $dataKen = "<br><span style='color:red;'>AS̤����</span>"; else $dataKen = '';
                echo "    <a class='link' href='javascript:void(0)' target='_self' style='text-decoration:none;'>{$rec['parts_no']}{$dataKen}</a></td>\n";
                // echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91'><a class='link' href='javascript:win_open2(\"{$menu->out_action('�߸˷���')}?parts_no=" . urlencode($rec['parts_no']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='150'>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 27) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'>" . mb_convert_kana($rec['parts_zai'], 'k') . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'>{$rec['pro_mark']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['ȯ��Ϣ��']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70' >{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130'>{$rec['������']}</td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } elseif ($select == 'graph') { ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self();?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
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
        <B><font size=4>
        <?php echo $all_title ?>
        <font color=red><?php echo $month_all ?></font>
        ����
        </font></B>
        <br><br>
        <!-- �����1 Ǽ��ͽ��(��ʸ��ȯ�ԺѤ�) -->
        <B>
        <?php echo $total_title . $month_total . '����' ?>
        </B>
        <table width='100%' border='0'>
            <tr>
            <td align='center'>
                <?php echo $graph->GetHTMLImageMap('order_map') ?>
                <?php echo "<img src='", $graph_name, '?', $uniq, "' ismap usemap='#order_map' alt='Ǽ��ͽ�� ��� ���ץ���� (��ʸ��ȯ�ԺѤ�)' border='0'>\n"; ?>
            </td>
            </tr>
        </table>
        <!-- �����2 �������ʤ�ͽ��(��ʸ��̤ȯ��) -->
        <br>
        <B>
        <?php echo $total2_title . $month_total2 . '����' ?>
        </B>
        <table width='100%' border='0'>
            <tr>
            <td align='center'>
                <?php
                if ($view_graph2 == 'OK') {
                    echo $graph2->GetHTMLImageMap('order_map2');
                    echo "<img src='", $graph2_name, '?', $uniq, "' ismap usemap='#order_map2' alt='������ Ǽ��ͽ�� ��� ���ץ���� (��ʸ��̤ȯ��)' border='0'>\n";
                } else {
                    echo "<b style='color: teal;'>�������Υǡ���������ޤ���</b>\n";
                }
                ?>
            </td>
            </tr>
        </table>
        <?php } elseif ($select == 'list') { ?>
        <BR><BR>
        <B><font size=4>
        <?php echo $list_title ?>
        </font></B>
        <BR><BR>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        ǯ����
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        ��ݶ��(������)
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        ��ʸ��ȯ�ԺѤ߶��
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        ��ʸ��̤ȯ�Զ��
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        Ǽ��ͽ���۷�
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        ���ͽ���۷�
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        Ǽ��ͽ���۷�
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <?php if ($dd_today <= 10) { ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyyb1 . 'ǯ' . $mmb1 . '��' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kinb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_totalb1 ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        ��
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_allb1 ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sumb1 ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_allb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy1 . 'ǯ' . $mm1 . '��' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kin ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sum ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <?php } else { ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyyb1 . 'ǯ' . $mmb1 . '��' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kinb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        ��
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        ��
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        ��
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sumb1 ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_allb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy1 . 'ǯ' . $mm1 . '��' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kin ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sum ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy2 . 'ǯ' . $mm2 . '��' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        ��
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_all ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy3 . 'ǯ' . $mm3 . '��' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        ��
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_all ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                    ��
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (A)
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (B)
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (C)
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (D)=(B)+(C)
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        (A)+(D)
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        (B)+(C)
                    </div>
                    </td>
                    <?php } ?>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <BR><BR>
        <B>
        �� �������ʸ��ȯ�ԺѤ߶�ۤˤϡ������ųݤ�Ǽ���٤��ޤ�
        </B>
        <?php if ($dd_today <= 10) { ?>
        <BR><BR>
        <B>
        ���������������� �������ޤǤ��������ʬ������ʬ����ʸ��ȯ�ԺѶ�ۤȤ��Ʒ׻�����
        </B>
        <?php } ?>
        <?php } ?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // ������ѣ�����
// -->
</script>
<?php echo $menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
