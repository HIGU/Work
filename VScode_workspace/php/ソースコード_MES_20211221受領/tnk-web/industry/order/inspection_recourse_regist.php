<?php
//////////////////////////////////////////////////////////////////////////////
// �۵� ���� ���� ���� ��Ͽ�ץ����  return��header()                     //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/19 Created  inspection_recourse_regist.php                       //
// 2004/10/28 ë������ȴ���Ƥ���Τ���                                  //
// 2004/11/20 ������Υ�å���������Ͽ���Υ�å�������Ʊ���ʤΤ���        //
// 2005/03/10 ��ë�������� �ڤ���Ҥ��줿�ͤΥ���                       //
// 2006/04/20 �ͤΰ�ư��ȼ�������ѹ�(���������ϡ�������ź�ġ��޽���)        //
//            ���´ط����� function ���ѹ� order_function.php             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function (define.php��ޤ�)
require_once ('order_function.php');        // order �ط��ζ��� function
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
// $menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
// $menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
////////////// target����
// $menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// ��ʬ��ե졼��������Ѥ���
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('ͽ������', INDUST . 'order/order_detailes.php');
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
// $menu->set_title('�۵����ʸ���������Ͽ');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///////// �ѥ�᡼���������å�������
if (isset($_REQUEST['order_seq'])) {
    $order_seq = $_REQUEST['order_seq'];    // ��ʸ���ȯ��Ϣ��
} else {
    $order_seq = '';                        // ȯ��Ϣ�֤�̵�����error
}
///// ����ѥѥ�᡼����
if (isset($_REQUEST['del_order_seq'])) {
    $del_order_seq = $_REQUEST['del_order_seq'];    // ��ʸ���ȯ��Ϣ��
} else {
    $del_order_seq = '';                            // ȯ��Ϣ�֤�̵�����error
}
if (isset($_REQUEST['retUrl'])) {
    $retUrl = $_REQUEST['retUrl'];         // �꥿����URL
} else {
    $retUrl = $_SERVER['HTTP_REFERER'];     // �꥿����URL��̵�����
}
if (isset($_SESSION['User_ID'])) {
    $uid = $_SESSION['User_ID'];            // ������Ͽ�桼����
    if ($uid == '') {
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ椬�ʤ��Τǰ���Ͻ���ޤ��󡣴���ô���Ԥ�Ϣ���Ʋ�������";
        header('location: ' . H_WEB_HOST . $retUrl);    // ���å����ǡ�����̵�����϶����꥿����
    }
} else {
    header('location: ' . H_WEB_HOST . $retUrl);    // ���å����ǡ�����̵�����϶����꥿����
}

// $uniq = 'id=' . uniqid('order');    // ����å����ɻ��ѥ�ˡ���ID
/////////// ���饤����ȤΥۥ���̾(����IP Address)�μ���
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
/////////// ���������
$today = date('Ymd');
/////////// �۵����ʤ���Ͽ���å�
while ($order_seq != '') {
    if (!user_check($uid, 1)) break;
    $query = "select order_seq from inspection_recourse where order_seq = {$order_seq} limit 1";
    if (getUniResult($query, $check) <= 0) {
        $query = "select delivery, uke_date from order_data where order_seq = {$order_seq} limit 1";
        $res = array();
        if (getResult($query, $res) > 0) {
            $delivery = $res[0]['delivery'];
            $uke_date = $res[0]['uke_date'];
        } else {
            $delivery = 0;
            $uke_date = 0;
        }
        $priority = 100;
        ////////// INSERT
        if ($uke_date > 0) {
            if ($uke_date < $today) {
                $wantdate = "{$today} 170000";
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$today}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $priority = (100 + $cnt);
            } else {
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$uke_date}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $wantdate = "{$uke_date} 170000";
                $priority = (100 + $cnt);
            }
        } elseif ($delivery > 0) {
            if ($delivery < $today) {
                $wantdate = "{$today} 170000";
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$today}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $priority = (100 + $cnt);
            } else {
                $wantdate = "{$delivery} 170000";
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$delivery}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $priority = (100 + $cnt);
            }
        } else {
            $wantdate = "{$today} 170000";
            $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$today}'";
            if (getUniResult($query, $cnt) <= 0) $cnt = 0;
            $priority = (100 + $cnt);
        }
        $insert = "insert into inspection_recourse (order_seq, uid, client, wantdate, priority) values({$order_seq}, '{$uid}', '{$hostName}', '{$wantdate}', $priority)";
        if (query_affected($insert) <= 0) {
            $_SESSION['s_sysmsg'] = '�۵����ʤθ����������Ͽ����ޤ���Ǥ�����';
        } else {
            $_SESSION['s_sysmsg'] = '��Ͽ���ޤ�����';
        }
    } else {
        ////////// UPDATE �Ϥ��ʤ�
        $_SESSION['s_sysmsg'] = '������Ͽ����Ƥ��ޤ���';
    }
    break;
}
/////////// �۵����ʤκ�����å�
while ($del_order_seq != '') {
    if (!user_check($uid, 2)) break;
    $query = "select order_seq from inspection_recourse where order_seq = {$del_order_seq} limit 1";
    if (getUniResult($query, $check) > 0) {
        //////////// DELETE
        $delete = "delete from inspection_recourse where order_seq = {$del_order_seq}";
        if (query_affected($delete) <= 0) {
            $_SESSION['s_sysmsg'] = '�۵����ʤθ��������������ޤ���Ǥ�����';
        } else {
            $_SESSION['s_sysmsg'] = '������ޤ�����';
        }
    } else {
        $_SESSION['s_sysmsg'] = '��Ͽ����Ƥ��ޤ���';
    }
    break;
}
header('location: ' . H_WEB_HOST . $retUrl);    // ��λ
?>
