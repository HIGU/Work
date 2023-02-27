<?php
//////////////////////////////////////////////////////////////////////////////
// ʿ�й����ê���ǡ����Ȳ�(ǯ��ˤ����ֻ���)                             //
// Copyright (C) 2012-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2012/02/20 Created hiraide_stocktaking_view.php                          //
// 2012/02/24 ���Ĵ�������ݤʤΤ�DB��parts_stock_master���ѹ�              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
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
$menu->set_site(30, 99);                    // site_index=30(������˥塼) site_id=10(��ݼ��ӾȲ�Υ��롼��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ʿ�й��� ê���ǡ����ξȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('�����������',   INDUST . 'material/materialCost_view.php');

//////////// ���ǤιԿ�
define('PAGE', '1000');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

$e_ymd = date('Ymd');

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select
                            *
                    from
                            parts_stock_master AS m
                    WHERE m.tnk_tana LIKE '%s'
                    ", '8%');
if (($maxrows = getResultTrs($con, $query, $paya_ctoku)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("ʿ�й���ê���ǡ���������ޤ���");
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY �λ��Ͻ���ؿ��ϻȤ��ʤ�
}
$query = sprintf("SELECT SUM(CASE
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1'
                        THEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2'
                        THEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                  END)                                       as ��׶��
            FROM
                parts_stock_master as m
                LEFT OUTER JOIN
                parts_stock_master_hiraide as h
                ON (m.parts_no=h.parts_no)
            WHERE m.tnk_tana LIKE '%s'
            ", '8%');
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '��ݹ�׶�ۤμ���������ޤ���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}
//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$page    = PAGE;
$query = "SELECT  m.parts_no as �����ֹ�       -- 0
                , 
                i.midsc    as ����̾         -- 1
                ,
                m.tnk_tana as ê��           -- 2
                , 
                CASE
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1'
                        THEN
                            CASE
                                WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2'
                        THEN
                            CASE
                                WHEN (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    ELSE
                        CASE
                            WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                THEN 0
                            ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                        END
                  END                                       as ���ߺ߸�     -- 3
                ,
                h.tanka                                     as ê��ñ��     -- 4
                , 
                CASE
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    ELSE
                        CASE
                            WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                THEN 0
                            ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                        END
                  END                                       as �߸˶��     -- 5
                ,
                CASE
                    WHEN h.abc_kubun = '' THEN '��'
                    ELSE h.abc_kubun
                END                                         as ���           -- 6
                ,
                CASE
                    WHEN h.stock_id = '' THEN '��'
                    ELSE h.stock_id
                END                                         as ê����ʬ         -- 7
            FROM
                parts_stock_master as m
            LEFT OUTER JOIN
                miitem as i
            ON (m.parts_no=i.mipn)
            LEFT OUTER JOIN
                parts_stock_master_hiraide as h
            ON (m.parts_no=h.parts_no)
            WHERE m.tnk_tana LIKE '8%'
            ORDER BY m.parts_no ASC
        offset {$offset} limit {$page}
    ";
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("ʿ�й���ê���ѥǡ���������ޤ���");
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // �ե�����ɿ�����
}

//////////// ɽ�������
$caption = "$e_ymd" . '���ߡ���׶�ۡ�' . number_format($sum_kin) . '����׷����' . number_format($maxrows);
$menu->set_caption("{$caption}");

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font:           10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font:           11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font:               10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= $menu->out_caption(), "\n" ?>
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
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><div class='pt10b'><?= $field[$i] ?></div></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' �������� -->
                        <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?= ($r + $offset + 1) ?></div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        case 4:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 5:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        case 7:
                            if($res[$r][$i] == 'E') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>TNKCC</div></td>\n";
                            } elseif($res[$r][$i] == 'C') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>CC����</div></td>\n";
                            } elseif($res[$r][$i] == 'X') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>�оݳ�</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                            }
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // ���ϥХåե�����gzip���� END
?>
