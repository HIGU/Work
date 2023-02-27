<?php
//////////////////////////////////////////////////////////////////////////////
// ������ۤη׻� �� ���� �¹ԥ��å�  (��ݶ� �� ͭ���ٵ���)            //
// Copyright(C) 2003-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/12/09 ��������  act_purchase_update.php                             //
//            �������Ť����� act_purchase_header �ơ��֥�������           //
//            ���ߤϥإå����ե�����˹�׶�ۤȥ쥳���ɿ�����¸            //
//            ����Ū�ˤ����٤��̥ơ��֥�˻��Ĥ褦�ˤ���ͽ��                //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2014/02/06 ����0�������Ѥ����ǽ��������١����ܣ����飵�Τߤ�         //
//            ȴ���Ф��褦�˥ץ������ѹ�                           ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');       // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
access_log();                           // Script Name �ϼ�ư����
$_SESSION['site_index'] = 20;           // ��������ط�=20 �Ǹ�Υ�˥塼 = 99   �����ƥ�����Ѥϣ�����
$_SESSION['site_id']    = 31;           // ���̥�˥塼̵�� <= 0    �ƥ�ץ졼�ȥե�����ϣ�����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
// $url_referer     = $_SERVER['HTTP_REFERER'];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
$url_referer     = $_SESSION['act_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////////// ǧ�ڥ����å�
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // ���¥�٥뤬���ʲ��ϵ���
if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    $_SESSION['s_sysmsg'] = "Accounting Group �θ��¤�ɬ�פǤ���";
    // header("Location: http:" . WEB_HOST . "menu.php");   // ����ƽи������
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/********** Logic Start **********/
//////////// �����ȥ�����ա���������
$today = date('Y/m/d H:i:s');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �����ƥ��å������ѿ������
// $_SESSION['s_sysmsg'] = "";      // menu_site.php �ǻ��Ѥ��뤿�ᤳ���ǽ�������Բ�

//////////// �о�ǯ������ (ǯ��Τߤ����)
if ( isset($_SESSION['act_ym']) ) {
    $act_ym = $_SESSION['act_ym'];
    $s_ymd  = $act_ym . '01';   // ������
    $e_ymd  = $act_ym . '99';   // ��λ��
} else {
    $_SESSION['s_sysmsg'] = '��о�ǯ����ꤵ��Ƥ��ޤ���!';
    header('Location: ' . $url_referer);
    exit();
}
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu_title = "$act_ym �����׾��� �Ȳ�";

//////////// ���ǤιԿ�
define('PAGE', '25');

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    exit();
}

//////////// ������Ͽ�Ѥߤ������å�
$query = "select item from act_purchase_header where purchase_ym={$act_ym}";
if ( getResultTrs($con, $query, $res) > 0) {         // �쥳���ɤ����뤫��
    $_SESSION['s_sysmsg'] .= "{$act_ym}���ϴ�����Ͽ�ѤߤǤ���";      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . $url_referer);
    exit();
}

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where act_date>=%d and act_date<=%d and vendor !='01111' and vendor !='00222' and vendor !='99999'", $s_ymd, $e_ymd);

/******************* ��ݶ�μ��� ****************************/
// ����
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1", $search);

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_all) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τ���� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ���ץ�
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and div='C'", $search);

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_c) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ����� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ���ץ� ����
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and paya.div='C' and kouji_no like 'SC%%'", $search);

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable as paya left outer join order_plan using(sei_no) %s", $search_kin);
if ( getResultTrs($con, $query, $paya_ctoku) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ��������� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ��˥�
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and div='L'", $search);

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_l) <= 0) {
    $_SESSION['s_sysmsg'] .= '��˥������ ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ��˥� BIMOR
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and div='L' and (parts_no like 'LR%%' or parts_no like 'LC%%')", $search);

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_bimor) <= 0) {
    $_SESSION['s_sysmsg'] .= '�Х�������� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

/******************************************************************/


/******************* ͭ���ٵ��ۤμ��� ****************************/
// ����
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=���� �������=2(ͭ��)
$search_kin = sprintf("%s and mtl_cond='2'", $search);

//////////// ���������׶��
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_all) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τ�ͭ���ٵ� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ���ץ�
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=���� �������=2(ͭ��)
$search_kin = sprintf("%s and mtl_cond='2' and div='C'", $search);

//////////// ���������׶��
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_c) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ��ͭ���ٵ� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ���ץ� ����
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=���� �������=2(ͭ��)
$search_kin = sprintf("%s and mtl_cond='2' and mi.div='C' and kouji_no like 'SC%%'", $search);

//////////// ���������׶��
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov as mi left outer join order_plan on substr(_sei_no,2,7)=sei_no %s", $search_kin);
if ( getResultTrs($con, $query, $prov_ctoku) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ������ͭ���ٵ� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ��˥�
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=���� �������=2(ͭ��)
$search_kin = sprintf("%s and mtl_cond='2' and div='L'", $search);

//////////// ���������׶��
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_l) <= 0) {
    $_SESSION['s_sysmsg'] .= '��˥���ͭ���ٵ� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

// ��˥� BIMOR
//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=���� �������=2(ͭ��)
$search_kin = sprintf("%s and mtl_cond='2' and div='L' and (parts_no like 'LR%%' or parts_no like 'LC%%')", $search);

//////////// ���������׶��
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_bimor) <= 0) {
    $_SESSION['s_sysmsg'] .= '�Х�����ͭ���ٵ� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

/******************************************************************/


//////////// ���Τ�إå����˽����
if ($paya_all[0][0] == '') $paya_all[0][0] = 0;     // �쥳����̵���Υ����å�
if ($paya_all[0][1] == '') $paya_all[0][1] = 0;     //   ��
if ($prov_all[0][0] == '') $prov_all[0][0] = 0;     //   ��
if ($prov_all[0][1] == '') $prov_all[0][1] = 0;     //   ��
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, '����', {$paya_all[0][0]}, {$prov_all[0][0]}, {$paya_all[0][1]}, {$prov_all[0][1]})";
/////////// �ȥ�󥶥��������ǹ����¹�
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ΤΥإå�������ߤ˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

//////////// ���ץ��إå����˽����
if ($paya_c[0][0] == '') $paya_c[0][0] = 0;     // �쥳����̵���Υ����å�
if ($paya_c[0][1] == '') $paya_c[0][1] = 0;     //   ��
if ($prov_c[0][0] == '') $prov_c[0][0] = 0;     //   ��
if ($prov_c[0][1] == '') $prov_c[0][1] = 0;     //   ��
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, '���ץ�', {$paya_c[0][0]}, {$prov_c[0][0]}, {$paya_c[0][1]}, {$prov_c[0][1]})";
/////////// �ȥ�󥶥��������ǹ����¹�
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ�Υإå�������ߤ˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

//////////// ��˥���إå����˽����
if ($paya_l[0][0] == '') $paya_l[0][0] = 0;     // �쥳����̵���Υ����å�
if ($paya_l[0][1] == '') $paya_l[0][1] = 0;     //   ��
if ($prov_l[0][0] == '') $prov_l[0][0] = 0;     //   ��
if ($prov_l[0][1] == '') $prov_l[0][1] = 0;     //   ��
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, '��˥�', {$paya_l[0][0]}, {$prov_l[0][0]}, {$paya_l[0][1]}, {$prov_l[0][1]})";
/////////// �ȥ�󥶥��������ǹ����¹�
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= '��˥��Υإå�������ߤ˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

//////////// ���ץ������إå����˽����
if ($paya_ctoku[0][0] == '') $paya_ctoku[0][0] = 0;     // �쥳����̵���Υ����å�
if ($paya_ctoku[0][1] == '') $paya_ctoku[0][1] = 0;     //   ��
if ($prov_ctoku[0][0] == '') $prov_ctoku[0][0] = 0;     //   ��
if ($prov_ctoku[0][1] == '') $prov_ctoku[0][1] = 0;     //   ��
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, '���ץ�����', {$paya_ctoku[0][0]}, {$prov_ctoku[0][0]}, {$paya_ctoku[0][1]}, {$prov_ctoku[0][1]})";
/////////// �ȥ�󥶥��������ǹ����¹�
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ�����Υإå�������ߤ˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

//////////// �Х�����إå����˽����
if ($paya_bimor[0][0] == '') $paya_bimor[0][0] = 0;     // �쥳����̵���Υ����å�
if ($paya_bimor[0][1] == '') $paya_bimor[0][1] = 0;     //   ��
if ($prov_bimor[0][0] == '') $prov_bimor[0][0] = 0;     //   ��
if ($prov_bimor[0][1] == '') $prov_bimor[0][1] = 0;     //   ��
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, '�Х����', {$paya_bimor[0][0]}, {$prov_bimor[0][0]}, {$paya_bimor[0][1]}, {$prov_bimor[0][1]})";
/////////// �ȥ�󥶥��������ǹ����¹�
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= '�Х����Υإå�������ߤ˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // ľ���θƽи������
    exit();
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
$_SESSION['s_sysmsg'] .= "<font color='yellow'>{$act_ym}�������׾� ���� ��λ</font>";
header('Location: ' . H_WEB_HOST . ACT . 'act_purchase_view.php');   // �Ȳ񥹥���ץȤ�
// header('Location: http:' . WEB_HOST . 'account/act_purchase_view.php');   // �Ȳ񥹥���ץȤ�
exit();

/********** Logic End   **********/
?>
