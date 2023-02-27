<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �ã��� ����(�������δ���)����׻�                         //
// Copyright(C) 2003-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/02/03 ��������  profit_loss_cl_allocation.php                       //
//            ���ѥơ��֥� �ɹ� bm_km_summary (�������������)              //
//                         ��� act_allo_history (�����Ψ�׻�����)       //
// 2003/02/07 ����Ψ�ξ�������ѹ� %1.3f �� %1.5f Uround(???.5) ��          //
//                                      Excel �ȸ����ܤ��碌�뤿��        //
// 2003/02/12 �оݥǡ����Υ����å��� select pl_bs_ym �� select sum(kin)     //
//              ���ѹ� ��׶�ۤ� 0 �ξ��ϥ��顼�Ȥ��롣                  //
// 2003/03/04 �ǡ���������ȥ�󥶥��������ѹ� (�ǡ������ݾ�)             //
// 2004/02/05 sprintf�� $allo �� %d �� %01.5f �ؽ��� 173 174 500 ����ʬ     //
//            (PostgreSQL V7.4.1 PHP V4.3.5RC2 �ǥȥ�֥뤢��)              //
// 2004/07/02 ������򥫥ץ�=83.133% ��˥�=16.867% ���ѹ� ����ʬ���Ŭ��   //
// 2009/06/10 ��������501����η�������׻����ɲ�                      ��ë //
// 2009/08/07 ʪή»���ɲäΰ١�580�������¤�����670������δ����        //
//            ����Ū�˥��ץ�˿�ʬ��������б�                         ��ë //
// 2009/08/20 ������»�׵ڤӷ������ɽ�ǻ���Ū�˥��ץ�˿�ʬ���������      //
//            ���줾���������������ᤷɽ������褦���ѹ�����         ��ë //
// 2012/11/06 500�����CL�γ����ѹ� C:30��L:70��C:70��L:30           ��ë //
// 2016/10/04 545�����CL�γ����ɲ� C:70��L:30                       ��ë //
// 2016/10/14 2016/10��ꤹ�٤Ƥγ���C:80��L:20���ѹ�                ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name �ϼ�ư����
$_SESSION["site_index"] = 10;       // �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
$_SESSION["site_id"] = 7;           // ���̥�˥塼̵�� (0 <=)
// if(!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])){
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}
///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
//////////// 173 174 500 501 545 ����οͷ��� ����Ψ
if ($yyyymm > 201609) {
    $allo_173_c = 0.80;     // 173 ����Ψ���ץ�
    $allo_173_l = 0.20;     // 173 ����Ψ��˥�
    $allo_174_c = 0.80;     // 174 ����Ψ���ץ�
    $allo_174_l = 0.20;     // 174 ����Ψ��˥�
    $allo_500_c = 0.80;     // 500 ����Ψ���ץ�
    $allo_500_l = 0.20;     // 500 ����Ψ��˥�
    $allo_501_c = 0.80;     // 501 ����Ψ���ץ�
    $allo_501_l = 0.20;     // 501 ����Ψ��˥�
    $allo_545_c = 0.80;     // 545 ����Ψ���ץ�
    $allo_545_l = 0.20;     // 545 ����Ψ��˥�
} else {
    $allo_173_c = 0.70;     // 173 ����Ψ���ץ�
    $allo_173_l = 0.30;     // 173 ����Ψ��˥�
    $allo_174_c = 0.60;     // 174 ����Ψ���ץ�
    $allo_174_l = 0.40;     // 174 ����Ψ��˥�
    $allo_500_c = 0.70;     // 500 ����Ψ���ץ�
    $allo_500_l = 0.30;     // 500 ����Ψ��˥�
    $allo_501_c = 0.70;     // 501 ����Ψ���ץ�
    $allo_501_l = 0.30;     // 501 ����Ψ��˥�
    $allo_545_c = 0.70;     // 545 ����Ψ���ץ�
    $allo_545_l = 0.30;     // 545 ����Ψ��˥�
}
//////////// �оݥǡ����Υ����å�
$res     = array();     // �����ǡ���������
$query   = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d", $yyyymm);
$rows = getResult($query,$res);
if ($res[0][0] == 0) {      ///// �ǡ���̵���Υ����å�
    $_SESSION['s_sysmsg'] .= sprintf("�����̷��� �оݥǡ����ʤ� ��%d��%d��", $ki, $tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/********** ����������������ɹ� **********/
$res     = array();     // �����ǡ���������
$act_id  = 173;         // ���祳���� 173 �� �ͷ���ط��β���(8101��) ��ۼ���
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    foreach ($res as $res1) {
        /********** ����ơ��֥�Υǡ��������å� *************/
        $res_hist = array();
        $dest_id  = 1;          // ���ץ�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_173_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // ��˥�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_173_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ���δ����� �оݥǡ����ʤ� ����=%d ��%d��%d��<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 174;         // ���祳���� 174 �� �ͷ���ط��β���(8101��) ��ۼ���
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    foreach ($res as $res1) {
        /********** ����ơ��֥�Υǡ��������å� *************/
        $res_hist = array();
        $dest_id  = 1;          // ���ץ�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_174_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // ��˥�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_174_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ���δ����� �оݥǡ����ʤ� ����=%d ��%d��%d��<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 500;         // ���祳���� 174 �� �ͷ���ط��β���(8101��) ��ۼ���
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    foreach ($res as $res1) {
        /********** ����ơ��֥�Υǡ��������å� *************/
        $res_hist = array();
        $dest_id  = 1;          // ���ץ�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_500_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // ��˥�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_500_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ���δ����� �оݥǡ����ʤ� ����=%d ��%d��%d��<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 501;         // ���祳���� 501 �� �ͷ���ط��β���(8101��) ��ۼ���
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    foreach ($res as $res1) {
        /********** ����ơ��֥�Υǡ��������å� *************/
        $res_hist = array();
        $dest_id  = 1;          // ���ץ�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_501_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // ��˥�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_501_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ���δ����� �оݥǡ����ʤ� ����=%d ��%d��%d��<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 545;         // ���祳���� 545 �� �ͷ���ط��β���(8101��) ��ۼ���
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    foreach ($res as $res1) {
        /********** ����ơ��֥�Υǡ��������å� *************/
        $res_hist = array();
        $dest_id  = 1;          // ���ץ�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_545_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // ��˥�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $allo     = $allo_545_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ���δ����� �оݥǡ����ʤ� ����=%d ��%d��%d��<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 580;         // ���祳���� 580 �� ����ۼ���
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    foreach ($res as $res1) {
        /********** ����ơ��֥�Υǡ��������å� *************/
        $res_hist = array();
        $dest_id  = 1;          // ���ץ�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $dest_kin = $res1['kin'];
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ���δ����� �оݥǡ����ʤ� ����=%d ��%d��%d��<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 670;         // ���祳���� 670 �� ����ۼ���
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    foreach ($res as $res1) {
        /********** ����ơ��֥�Υǡ��������å� *************/
        $res_hist = array();
        $dest_id  = 1;          // ���ץ�
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            ////// ����Ψ��۷׻�
            $dest_kin = $res1['kin'];
            /********** ����ơ��֥�˽���� ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d:����=%d:������=%d ������Ѥ�<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ���δ����� �оݥǡ����ʤ� ����=%d ��%d��%d��<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/*********** �δ���οͷ��� �ã� ����Ψ�׻� *************/
// ľ����Σã̵����椫�� ����Ψ��׻�
$actcod = 8102;     // ��������
    // ���ץ�
$query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='C'", $yyyymm, $actcod);
if ((getResult($query,$res)) > 0) {
    $kin_c = $res[0][0];
}
if ($kin_c != 0) {      ///// �ǡ���̵���Υ����å�
        // ��˥�
    $query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='L'", $yyyymm, $actcod);
    if ((getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        $kin_l  = $res[0][0];
        $kin    = $kin_c + $kin_l;
        $allo_c = Uround(($kin_c / $kin),5);
        $allo_l = 1 - $allo_c;
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=0 and k_kubun=' ' and div=' '", $yyyymm, $actcod);
        if (($rows=getResult($query,$res)) <= 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            // ���ץ�
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, ' ', ' ', 1, %d, %1.5f, '���ץ������Ψ')",
                $yyyymm, $actcod, $kin, $kin_c, $allo_c);
            if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��ľ�ܵ���������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_c);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
            // ��˥�
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, ' ', ' ', 2, %d, %1.5f, '��˥�������Ψ')",
                $yyyymm, $actcod, $kin, $kin_l, $allo_l);
            if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
                $_SESSION['s_sysmsg'] .= sprintf("��˥���ľ�ܵ���������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_l);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            // ���˷���ե��������Ͽ��Ƥ���
            $_SESSION["s_sysmsg"] .= sprintf("�ͷ�����δ��� ����Ψ ��Ͽ�Ѥ� ��%d��%d��<br>", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION["s_sysmsg"] .= sprintf("�ͷ�����δ��� ��˥��оݥǡ����ʤ� ��%d��%d��<br>", $ki, $tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�ͷ�����δ��� ���ץ��оݥǡ����ʤ� ��%d��%d��<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/*********** ����δ������δ��� �ã� ����Ψ�׻� *************/
// ������ ľ�ܣã���Ψ�ˤ�� ����Ψ
    // ���ץ�ι��ľ��������
$query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and k_kubun='1' and div='C'", $yyyymm);
if ((getResult($query,$res)) > 0) {
    $kin_c = $res[0][0];
}
if ($kin_c != 0) {      ///// �ǡ���̵���Υ����å�
        // ��˥��ι��ľ��������
    $query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and k_kubun='1' and div='L'", $yyyymm);
    if ((getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        $kin_l = $res[0][0];
        $kin    = $kin_c + $kin_l;
        $allo_c = Uround(($kin_c / $kin),5);
        $allo_l = 1 - $allo_c;
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=0 and orign_id=0 and k_kubun='1' and div='C'", $yyyymm);
        if (($rows=getResult($query,$res)) <= 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            // ���ץ�
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, 0, 0, %d, '1', 'C', 1, %d, %1.5f, '���ץ������Ψ')",
                $yyyymm, $kin, $kin_c, $allo_c);
            if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�η�������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_c);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
            // ��˥�
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, 0, 0, %d, '1', 'L', 2, %d, %1.5f, '��˥�������Ψ')",
                $yyyymm, $kin, $kin_l, $allo_l);
            if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
                $_SESSION['s_sysmsg'] .= sprintf("��˥��η�������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_l);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            // ���˷���ե��������Ͽ��Ƥ���
            $_SESSION["s_sysmsg"] .= sprintf("������ ����Ψ ��Ͽ�Ѥ� ��%d��%d��<br>", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION["s_sysmsg"] .= sprintf("�����ľ���� ��˥��оݥǡ����ʤ� ��%d��%d��<br>", $ki, $tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�����ľ���� ���ץ��оݥǡ����ʤ� ��%d��%d��<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
//////////// �ã������� ����Ψ
$allo_c = 0.72439;     // ����Ψ���ץ� �裵���Σ���ޤ�
$allo_l = 0.27561;     // ����Ψ��˥� �裵���Σ���ޤ�
// �裵���Σ�����ʲ����ͤ��ѹ�
$allo_c = 0.83133;
$allo_l = 0.16867;
$query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=7512 and orign_id=0 and k_kubun='1' and div='C'", $yyyymm, $actcod);
if (($rows=getResult($query,$res)) <= 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    // ���ץ�
    $actcod = 7512;     // ��̳������
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'C', 1, 0, %1.5f, '���ץ�������Ψ')",
        $yyyymm, $actcod, $allo_c);
    if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
        $_SESSION['s_sysmsg'] .= sprintf("���ץ��������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_c);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    // ��˥�
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'L', 2, 0, %1.5f, '��˥�������Ψ')",
        $yyyymm, $actcod, $allo_l);
    if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
        $_SESSION['s_sysmsg'] .= sprintf("��˥���������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_l);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $actcod = 7540;     // �¼���
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'C', 1, 0, %1.5f, '���ץ�������Ψ')",
        $yyyymm, $actcod, $allo_c);
    if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
        $_SESSION['s_sysmsg'] .= sprintf("���ץ��������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_c);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    // ��˥�
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'L', 2, 0, %1.5f, '��˥�������Ψ')",
        $yyyymm, $actcod, $allo_l);
    if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
        $_SESSION['s_sysmsg'] .= sprintf("��˥���������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_l);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $actcod = 8000;     // ����������
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'C', 1, 0, %1.5f, '���ץ�������Ψ')",
        $yyyymm, $actcod, $allo_c);
    if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
        $_SESSION['s_sysmsg'] .= sprintf("���ץ��������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_c);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    // ��˥�
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'L', 2, 0, %1.5f, '��˥�������Ψ')",
        $yyyymm, $actcod, $allo_l);
    if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
        $_SESSION['s_sysmsg'] .= sprintf("��˥���������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_l);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    // ���˷���ե��������Ͽ��Ƥ���
    $_SESSION["s_sysmsg"] .= sprintf("���Ѥˤ�� ����Ψ ��Ͽ�Ѥ� ��%d��%d��<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/*********** ����������� ľ����Σã���Ψ�ˤ�� ����Ψ�׻� *************/
$actcod = 7527;     // �����������
    // ���ץ�ι��ľ��������
$query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='C'", $yyyymm, $actcod);
if ((getResult($query,$res)) > 0) {
    $kin_c = $res[0][0];
}
if ($kin_c != 0) {      ///// �ǡ���̵���Υ����å�
        // ��˥��ι��ľ��������
    $query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='L'", $yyyymm, $actcod);
    if ((getResult($query,$res)) > 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        $kin_l = $res[0][0];
        $kin    = $kin_c + $kin_l;
        $allo_c = Uround(($kin_c / $kin),5);
        $allo_l = 1 - $allo_c;
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=0 and k_kubun='1' and div='C'", $yyyymm, $actcod);
        if (($rows=getResult($query,$res)) <= 0) {       ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            // ���ץ�
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, '1', 'C', 1, %d, %1.5f, '���ץ��������Ψ')",
                $yyyymm, $actcod, $kin, $kin_c, $allo_c);
            if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ξ���������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_c);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
            // ��˥�
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, '1', 'L', 2, %d, %1.5f, '��˥���������Ψ')",
                $yyyymm, $actcod, $kin, $kin_l, $allo_l);
            if ((query_affected_trans($con, $query)) <= 0) {    // �ȥ�󥶥�������ѥ����꡼�¹�
                $_SESSION['s_sysmsg'] .= sprintf("��˥��ξ���������Ψ=%1.5f �η���ؿ�����Ͽ�˼��Ԥ��ޤ���<br>", $allo_l);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            // ���˷���ե��������Ͽ��Ƥ���
            $_SESSION["s_sysmsg"] .= sprintf("�������� ����Ψ ��Ͽ�Ѥ� ��%d��%d��<br>", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION["s_sysmsg"] .= sprintf("�������� ��˥��оݥǡ����ʤ� ��%d��%d��<br>", $ki, $tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�������� ���ץ��оݥǡ����ʤ� ��%d��%d��<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
$_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>��%d�� %d�������Ψ�׻���λ</font>",$ki,$tuki);
header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
exit();


//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:m:s");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>�ã̷�������Ψ�׻�</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt8 {
    font:normal 8pt;
}
.pt10 {
    font:normal 10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt12b {
    font:bold 12pt;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0'>
    <center>
    </center>
</BODY>
</HTML>
