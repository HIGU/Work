<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ψ�׻�ɽ ����(���)��������ž���֤����Ϥ���Ψ��ư����          //
// Copyright (C) 2002-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/09/23 Created   machine_labor_rate_mnt.php                          //
// 2002/10/09 ������Ψ�򾮿����ʲ����夬���Ǥ�ɬ��ɽ��������(�Ȳ�)          //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
// 2003/09/08 ��ͳ����(������)����SQLʸ�ξ����Զ�礢�� (>=)��(=)       //
// 2003/10/08 ����������Ψ�� SQLʸ�� offset 1 ���ɲä����軻������Ψ��      //
// 2003/12/18 �ʤ���ñ��������� POST �ǡ�������������ʤ��Զ����б�      //
// 2004/10/28 user_check()function���ɲä��Խ������桼���������          //
// 2005/10/27 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/11/05 ñ�������������Ͽ����븽�ݤ��Ф�����MenuHeader��Ǹ�ذ�ư  //
// 2007/02/05 account_group_check()����Ͽ�Ǥ���桼�����γ�ǧ���ɲ�         //
// 2007/09/25 �ѿ��ν�������ɲ�                                            //
// 2010/06/03 ����������Ψ��SQLʸ������                                ��ë //
// 2014/04/11 ��¤���ݤδ��������������ɲ�                           ��ë //
// 2016/05/23 ��¤�����ɲäκݤ����꤬���������ʤäƤ����Τ�����       ��ë //
// 2016/06/09 ���ˤ�ä����꺹�ۤ�ȯ���������ۤϺ���γ�������     ��ë //
// 2018/06/05 ñ��������������ǡ��������ǥ��顼�ΰ١�����             ��ë //
// 2021/04/06 2103�˥꡼������Ĵ�� �꡼���񻺤�527�ǥޥ��ʥ��ΰ�       ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL & ~E_NOTICE);  // E_ALL='2047' debug ��
// ini_set('display_errors', '1');          // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

/////////// �桼�����Υ����å�
$uid = $_SESSION['User_ID'];            // �桼����
function user_check($uid)
{
    if (account_group_check() == FALSE) {
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥϵ�����Ψ����Ͽ�Ͻ���ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    } else {
        return true;
    }
    switch ($uid) {
    case '017850':      // ����
    case '300055':      // ��ƣ
    case '300101':      // ��ë
    case '010561':      // ����
        return TRUE;
        break;
    default:
        $query = "select trim(name) from user_detailes where uid = '{$uid}' limit 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$uid}��{$name}����Ǥϵ�����Ψ����Ͽ�Ͻ���ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
        return FALSE;
    }
}

//////////// POST �ǡ����Զ��Τ���ʲ����ɲ�
if (isset($_POST['rate_ym'])) {
    $_POST['tangetu'] = 'ñ�����';
}

$today = date("Y-m-d");

if ( isset($_POST['tangetu']) ) {
    $_SESSION['rate_ym'] = $_POST['rate_ym'];
    $_SESSION['tangetu'] = $_POST['tangetu'];
    unset($_SESSION['kessan']);
    $_SESSION['str_ym'] = "";           // �����
    $_SESSION['end_ym'] = "";           // �����
    $_SESSION['span']   = "";           // �����
}
if ( isset($_POST['kessan']) ) {
    $_SESSION['str_ym'] = $_POST['str_ym'];
    $_SESSION['end_ym'] = $_POST['end_ym'];
    $_SESSION['span'] = $_POST['span'];
    $_SESSION['kessan'] = $_POST['kessan'];
    unset($_SESSION['tangetu']);
    unset($_SESSION['rate_ym']);
}
if (isset($_SESSION['rate_ym'])) {
    $rate_ym = $_SESSION['rate_ym'];
} else {
    $rate_ym = "";                      // �����
}
if (!isset($_SESSION['str_ym'])) {
    $_SESSION['str_ym'] = "";           // �����
}
if (!isset($_SESSION['end_ym'])) {
    $_SESSION['end_ym'] = "";           // �����
}
if (!isset($_SESSION['span'])) {
    $_SESSION['span'] = "";             // �����
}
if ( isset($_POST['check']) ) {
    $_SESSION['h_cost'] = $_POST['h_cost'];
    $_SESSION['ope_time'] = $_POST['ope_time'];
}
if ( isset($_POST['tangetu']) || isset($_POST['kessan']) || isset($_POST['check']) || isset($_POST['insert']) ) {
    if (isset($_SESSION['tangetu'])) { ////////////// ����� ɬ���Ԥ�����
        $query = "select * from machine_labor_rate where rate_ym=" . $_SESSION['rate_ym'] . " and settle=0 order by reg_date DESC";
    } else if (isset($_SESSION['kessan'])) {
        if ($_SESSION['span'] == 1) { /////////////// ��ַ軻
            $query = "select * from machine_labor_rate where rate_ym>=" . $_SESSION['str_ym'] . " and rate_ym<=" . $_SESSION['end_ym'] . " and settle=1 order by reg_date DESC";
        } else if ($_SESSION['span'] == 2) { ////////// �����軻
            $query = "select * from machine_labor_rate where rate_ym>=" . $_SESSION['str_ym'] . " and rate_ym<=" . $_SESSION['end_ym'] . " and settle=2 order by reg_date DESC";
        } else { ///////////////////////////////////// ������(ǯ�֡���Ⱦ�� ��)
            $query = "select * from machine_labor_rate where str_ym = " . $_SESSION['str_ym'] . " and rate_ym = " . $_SESSION['end_ym'] . " and settle = 3 order by reg_date DESC";
        }
    }
    $res = array();
    if ( ($rows_act=getResult($query,$res)) >= 1) {      // ��Ͽ�ѤߤΥ����å�
        ///////////////////////////// ���򤢤�
        $register = "�Ȳ�";
        $act_id = array();      // ���祳����
        $b_name = array();      // ����̾(û��)
        $depre = array();       // ����������
        $lease = array();       // �꡼����
        $repair = array();      // ������
        $w_cost = array();      // �����������
        $p_cost = array();      // �ͷ���
        $e_cost = array();      // ������
        $other  = array();      // ����¾
        $m_cost = array();      // ������������
        $h_cost = array();      // ������
        $t_cost = array();      // ���
        $man    = array();      // �Ϳ�
        $ope_time = array();    // ��ž����
        $labor_rate = array();  // ������Ψ
        ////////////////////////// �����
        $depre_sum  = 0;
        $lease_sum  = 0;
        $repair_sum = 0;
        $w_cost_sum = 0;
        $p_cost_sum = 0;
        $e_cost_sum = 0;
        $other_sum  = 0;
        $m_cost_sum = 0;
        $man_sum    = 0;
        $h_cost_sum = 0;
        $ope_time_sum = 0;
        ////////////////////////// ����� END
        for ($i=0; $i<$rows_act; $i++) {                // $rows_act �ϲ���¿�Ѥ��뤿�������
            $act_id[$i] = $res[$i]['act_id'];
            $b_name[$i] = $res[$i]['s_name'];
            $depre[$i] = $res[$i]['depre'];
            $lease[$i] = $res[$i]['lease'];
            $repair[$i] = $res[$i]['repair'];
            $w_cost[$i] = $res[$i]['w_cost'];
            $p_cost[$i] = $res[$i]['p_cost'];
            $e_cost[$i] = $res[$i]['e_cost'];
            $other[$i] = $res[$i]['other'];
            $m_cost_all[$i] = $res[$i]['m_cost'];
            $_SESSION['h_cost'][$i] = $res[$i]['h_cost'];
            $_SESSION['ope_time'][$i] = $res[$i]['ope_time'];
            $man[$i] = $res[$i]['man'];
            $labor_rate[$i] = $res[$i]['labor_rate'];
            ////////////////// ��¤��׽���
            $depre_sum  += $depre[$i];
            $lease_sum  += $lease[$i];
            $repair_sum += $repair[$i];
            $w_cost_sum += $w_cost[$i];
            $p_cost_sum += $p_cost[$i];
            $e_cost_sum += $e_cost[$i];
            $other_sum  += $other[$i];
            $m_cost_sum += $m_cost_all[$i];
            ///// $h_cost_sum �ϲ��Ƿ׻�
            ///// $ope_time_sum �ϲ��Ƿ׻�
            $man_sum += $man[$i];
        }
    } else {
        ///////////////////////////// ����
        $register = "��Ͽ";
        ///////////////////////////// �о����祳���� ����̾�μ���
        $query = "select act_id,s_name from act_table where rate_flg='1' order by act_id ASC";
        $res_act = array();
        if ( ($rows_act=getResult($query,$res_act)) >= 1) {
            $act_id = array();
            $b_name = array();
            if ( isset($_SESSION['tangetu']) ) {
                $yymm = substr($_SESSION['rate_ym'],2,4);
            } elseif ( isset($_SESSION['kessan']) ) {
                $s_yymm = substr($_SESSION['str_ym'],2,4);
                $e_yymm = substr($_SESSION['end_ym'],2,4);
            }
            $depre = array();   // ����������
            $lease = array();   // �꡼����
            $repair = array();  // ������
            $w_cost = array();  // �����������
            $p_cost = array();  // �ͷ���
            $e_cost = array();  // ������
            $other  = array();  // ����¾
            $depre_sum  = 0;
            $lease_sum  = 0;
            $repair_sum = 0;
            $w_cost_sum = 0;
            $p_cost_sum = 0;
            $e_cost_sum = 0;
            $other_sum  = 0;
            for ($i=0; $i<$rows_act; $i++) {
                $act_id[$i] = $res_act[$i]['act_id'];
                $b_name[$i] = trim($res_act[$i]['s_name']);
                ///////////////////////////// ������μ��Ȼ��֡���ž���֤Υǡ�������
                if ( isset($_SESSION['kessan']) ) {
                    $query = "select sum(h_cost) as h_c,sum(ope_time) as o_t from machine_labor_rate where settle=0 and rate_ym>=" . $_SESSION['str_ym'] . " and rate_ym<=" . $_SESSION['end_ym'] . " and act_id=" . $act_id[$i];
                    $res_rate = array();
                    if ( ($rows_rate=getResult($query,$res_rate)) >= 1) {
                        if ( !isset($_POST['check']) && !isset($_POST['insert']) ) { ////// �ե�����������ǡ�����������Ͻ��ؤ��ʤ�
                            $_SESSION['h_cost'][$i] = $res_rate[0]['h_c'];
                            $_SESSION['ope_time'][$i] = $res_rate[0]['o_t'];
                        }
                    }
                }
                ////////////////////////////////////////////////// ľ�ܷ���μ���
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=8000 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=8000 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $depre[$i] = 0;
                    } else {
                        $depre[$i] = $res_summ[0]['sum'];       // ����������
                    }
                }
                if (isset($_SESSION['tangetu'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7540 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7540 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $lease[$i] = 0;
                    }
                    $lease[$i] = $res_summ[0]['sum'];       // �꡼����
                    if ($_SESSION['tangetu']) {
                        if ($yymm == 2103) {
                            if ($act_id[$i] == 527) {
                                $lease[$i] = $lease[$i] + 1193400;
                            } elseif ($act_id[$i] == 528) {
                                $lease[$i] = $lease[$i] - 1193400;
                            }
                        }
                    }
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7524 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7524 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $repair[$i] = 0;
                    }
                    $repair[$i] = $res_summ[0]['sum'];      // ������
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7527 and act_id=" . $act_id[$i];
                } elseif ( isset($_SESSION['kessan']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7527 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $w_cost[$i] = 0;
                    }
                    $w_cost[$i] = $res_summ[0]['sum'];      // �����������
                }
                ///////////////////////////////////////////// ���ܷ���μ���
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod>=8101 and actcod<=8123 and act_id=" . $act_id[$i];
                } elseif ( isset($_SESSION['kessan']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod>=8101 and actcod<=8123 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $p_cost[$i] = 0;
                    }
                    $p_cost[$i] = $res_summ[0]['sum'];      // �ͷ���
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7531 and aucod=10 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7531 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $e_cost[$i] = 0;
                    }
                    $e_cost[$i] = $res_summ[0]['sum'];      // ������
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    $other[$i] = $res_summ[0]['sum'];       // ����
                    $other[$i] -= ($depre[$i] + $lease[$i] + $repair[$i] + $w_cost[$i] + $p_cost[$i] + $e_cost[$i]);
                    if ($other[$i] == "") {
                        $depre[$i] = 0;
                    }
                }
                $depre_sum += $depre[$i];
                $lease_sum += $lease[$i];
                $repair_sum += $repair[$i];
                $w_cost_sum += $w_cost[$i];
                $p_cost_sum += $p_cost[$i];
                $e_cost_sum += $e_cost[$i];
                $other_sum += $other[$i];
            }
        }
        /////////////////////////////////////////// ������������� act_allocation ����Ψ�ޥ����������������褦���ѹ�ͽ��
        $query = "select act_id,s_name from act_table where rate_flg='2' order by act_id ASC"; ///// ����������������
        $res_tbl = array();
        if ( ($rows_tbl=getResult($query,$res_tbl)) >= 1) {
            $m_cost   = array();      // �󼡸�����
            $m_check  = array();      // �����纹��ȯ���б�
            for ($a=0; $a<$rows_tbl; $a++) {        // ��������(���긵)�����ɤ�ʣ����������б�
                $m_sagaku = 0;            // �����纹��ȯ���б� ��������
                if($res_tbl[$a]['act_id'] == 518) {
                    $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_id=11 order by dest_id ASC";
                } elseif($res_tbl[$a]['act_id'] == 547) {
                    $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_id=17 order by dest_id ASC";
                }
                $res_allo = array();
                if ( ($rows_allo=getResult($query,$res_allo) )>= 1) { ////// ����Ψ�ޥ�����������������硦����Ψ����
                    if ( isset($_SESSION['tangetu']) ) {
                        $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and act_id=" . $res_tbl[$a]['act_id'];
                    } elseif ( isset($_SESSION['kessan']) ) {
                        $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and act_id=" . $res_tbl[$a]['act_id'];
                    }
                    $res_man = array();
                    if ( ($rows_man=getResult($query,$res_man)) >= 1) {
                        for ($b=0; $b<$rows_allo; $b++) { ///////// ����Ψ�ޥ������������
                            $m_cost[$a][$b] = corrc_round($res_man[0]['sum'] * ($res_allo[$b]['allo_rate'] / 100)); /////// ��¤ ��ľ�����������Ψ
                            // �����纹��ȯ���б�
                            $m_check[$a] += $m_cost[$a][$b];
                        }
                        // �����纹��ȯ���б�
                        if($res_man[0]['sum'] <> $m_check[$a]) {            // ����ۤ������ۤ˺�������к���Ĵ��
                            $m_sagaku = $res_man[0]['sum'] - $m_check[$a];  // ���ۤ�׻�
                            // ���줾��κ������������Ψ�����
                            if($res_tbl[$a]['act_id'] == 518) {
                                $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_rate=(select max(allo_rate) from act_allocation where orign_id=518) and allo_id=11 order by dest_id ASC";
                            } elseif($res_tbl[$a]['act_id'] == 547) {
                                $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_rate=(select max(allo_rate) from act_allocation where orign_id=547) and allo_id=17 order by dest_id ASC";
                            }
                            $mres_allo  = array();
                            $mrows_allo = getResult($query,$mres_allo);
                            for ($b=0; $b<$rows_allo; $b++) { ///////// ����Ψ�ޥ������������
                                if ($mres_allo[0]['allo_rate'] == $res_allo[$b]['allo_rate']) {
                                    $m_cost[$a][$b] = $m_cost[$a][$b] + $m_sagaku;
                                }
                            }
                        }
                    }
                }
            }
            $m_cost_all = array();
            for ($a=0; $a<$rows_tbl; $a++) { ///////////// ��������(���긵)�����ɤ�ʣ����������б�
                for ($b=0; $b<$rows_allo; $b++) { //////////// ����Ψ�ޥ������������
                    $m_cost_all[$b] += $m_cost[$a][$b];
                }
            }
            $m_cost_sum = 0;           // �����
            for ($b=0; $b<$rows_allo; $b++) { //////////// ����Ψ�ޥ������������
                $m_cost_sum     += $m_cost_all[$b];
            }
        }
    }
    /////////////////////// ľ����ξ��� ����
    $s1_sum = array();
    for ($i=0; $i<$rows_act; $i++) {
        $s1_sum[$i] = ($depre[$i] + $lease[$i] + $repair[$i] + $w_cost[$i]);
    }
    $s1_sum_all = ($depre_sum + $lease_sum + $repair_sum + $w_cost_sum);
    /////////////////////// ������ξ��� ����
    $s2_sum = array();
    for ($i=0; $i<$rows_act; $i++) {
        $s2_sum[$i] = ($p_cost[$i] + $e_cost[$i] + $other[$i] + $m_cost_all[$i]);
    }
    $s2_sum_all = ($p_cost_sum + $e_cost_sum + $other_sum + $m_cost_sum);
    /////////////////////// ��� ����
    $m_sum = array();
    for ($i=0; $i<$rows_act; $i++) {
        $m_sum[$i] = $s1_sum[$i] + $s2_sum[$i];
    }
    $m_sum[$i] = $s1_sum_all + $s2_sum_all;
    /////////////////////// ��� ����
    $t_sum = array();
    $h_cost_tmp = 0;
    for ($i=0; $i <= $rows_act; $i++) {
        if ($i != $rows_act) {
            $t_sum[$i] = $m_sum[$i] - $_SESSION['h_cost'][$i];      // ��פ����Ȥ����
            $h_cost_tmp += $_SESSION['h_cost'][$i];
        } else {
            $t_sum[$i] = $m_sum[$i] - $h_cost_tmp;      // �Ǹ����¤��פʤΤ���פ���׼��Ȥ����
        }
    }
    /////////////////////// ��¤��� ������ž���� ����
    $ope_time_sum = 0;   // �����
    for ($i=0; $i<$rows_act; $i++) {
        $ope_time_sum += $_SESSION['ope_time'][$i];
    }
    /////////////////////// ľ�ܷ���ˤ�뵡����Ψ ����
    if ( !isset($labor_rate[0]) ) { /////////////// �Ȳ񤸤�ʤ���� ���������夬���Ǥ�ɬ��ɽ�������뤿��
        $labor_rate = array();
        for ($i=0; $i<$rows_act; $i++) {
            if ($_SESSION['ope_time'][$i] > 0)
                $labor_rate[$i] = Uround(($t_sum[$i] / $_SESSION['ope_time'][$i]),2);       // ��� �� ��ž����
        }
    }
    if ($ope_time_sum > 0) {
        $labor_rate[$rows_act] = Uround(($t_sum[$rows_act] / $ope_time_sum),2);     // ��� �� ��ž����(��¤���)
    }
    /////////////////////////////////////////////////// �����Υǡ��������  2003/10/08 offset 1 ���ɲ�
    // 2018/06/05 ñ���������end_ym��̵���ΤǼ������顼�Ȥʤ�١�ifʸ���ɲ�
    if ( isset($_POST['kessan']) || isset($_POST['check']) || isset($_POST['insert']) ) {
        for ($i=0; $i<$rows_act; $i++) {
            //$query = "select t_cost,ope_time,labor_rate from machine_labor_rate where settle>=1 and settle<=2 and act_id=" . $act_id[$i] . " order by rate_ym DESC limit 1 offset 1";
            $query = "select t_cost,ope_time,labor_rate from machine_labor_rate where settle>=1 and settle<=2 and act_id=" . $act_id[$i] . " and rate_ym<" . $_SESSION['end_ym'] . " order by rate_ym DESC limit 1 offset 0";
            $res_pre = array();
            if ( ($rows_pre=getResult($query,$res_pre)) >= 1) {
                $pre_t_cost[$i] = $res_pre[0]['t_cost'];
                $pre_ope_time[$i] = $res_pre[0]['ope_time'];
                $pre_rate[$i] = $res_pre[0]['labor_rate'];
            }
        }
    }
}
/////////////////////////////////////////////////// ��Ψ�׻���̤���Ͽ
while ( isset($_POST['insert']) ) {
    if (!user_check($uid)) break;
    if (isset($_SESSION['rate_ym'])) $rate_ym = $_SESSION['rate_ym']; else $rate_ym = '';
    if (isset($_SESSION['end_ym'])) $end_ym = $_SESSION['end_ym']; else $end_ym = '';
    if ( isset($_SESSION['tangetu']) ) {
        $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
            other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($rate_ym,$rate_ym,0,'$today',1,";
    } elseif ( isset($_SESSION['kessan']) ) {
        if (isset($_SESSION['str_ym'])) $str_ym = $_SESSION['str_ym']; else $str_ym = '';
        if ($_SESSION['span'] == 1) {//////////////// ��ַ軻
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
                other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($end_ym,$str_ym,1,'$today',1,";
        } elseif ($_SESSION['span'] == 2) {/////////// �����軻
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
                other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($end_ym,$str_ym,2,'$today',1,";
        } else {////////////////////////////////////// ������(ǯ�֡���Ⱦ���ʤ�)
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
                other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($end_ym,$str_ym,3,'$today',1,";
        }
    }
    $res_reg = array();
    //////////////////////////////////// �����꡼ʸ�� 0 �����å�
    for ($i=0; $i<$rows_act; $i++) {
        if ($depre[$i] == 0) {
            $query2 = "0,";
        } else {
            $query2 = $depre[$i] . ",";
        }
        if ($lease[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $lease[$i] . ",";
        }
        if ($repair[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $repair[$i] . ",";
        }
        if ($w_cost[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $w_cost[$i] . ",";
        }
        if ($p_cost[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $p_cost[$i] . ",";
        }
        if ($e_cost[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $e_cost[$i] . ",";
        }
        if ($other[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $other[$i] . ",";
        }
        if ($m_cost_all[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $m_cost_all[$i] . ",";
        }
        if ($_SESSION['h_cost'][$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $_SESSION['h_cost'][$i] . ",";
        }
        if ($t_sum[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $t_sum[$i] . ",";
        }
        if ($man[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $man[$i] . ",";
        }
        if ($_SESSION['ope_time'][$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $_SESSION['ope_time'][$i] . ",";
        }
        if ($labor_rate[$i] == 0) {
            $query2 .= "0,NULL," . $act_id[$i] . ",'" . $b_name[$i] . "')";
        } else {
            $query2 .= $labor_rate[$i] . ",NULL," . $act_id[$i] . ",'" . $b_name[$i] . "')";
        }
        $query3 = ($query . $query2);
        if ( ($rows_reg=getResult($query3,$res_reg)) >= 0) {
            $_SESSION['s_sysmsg'] .= $act_id[$i] . " : ����Ͽ���ޤ�����<BR>";
        }
    }
    ////////////////// �����������Ͽ
    for ($a=0; $a<$rows_tbl; $a++) {        // �������祳���ɤ�ʣ����������б�
        if ( isset($_SESSION['tangetu']) ) {
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($rate_ym,$rate_ym,0,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
        } elseif ( isset($_SESSION['kessan']) ) {
            if ($_SESSION['span'] == 1) {//////////////// ��ַ軻
                $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($end_ym,$str_ym,1,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
            } elseif ($_SESSION['span'] == 2) {/////////// �����軻
                $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($end_ym,$str_ym,2,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
            } else {////////////////////////////////////// ������(ǯ�֡���Ⱦ���ʤ�)
                $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($end_ym,$str_ym,3,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
            }
        }
        if ( ($rows_reg=getResult($query,$res_reg)) >= 0) {
            $_SESSION['s_sysmsg'] .= "<BR>" . $res_tbl[$a]['act_id'] . " : �����������Ͽ";
        }
    }
    unset($_SESSION['h_cost']);
    unset($_SESSION['ope_time']);   // �����ϤΥǡ�������(���Τ����)
    break;
}

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
$menu->set_site(10, 3);                     // site_index=10(»�ץ�˥塼) site_id=7(������Ψ�ξȲ���Ͽ)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������Ψ�׻�ɽ�κ������Ȳ�');

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
<?= $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript' src='machine_labor_rate_mnt.js'></script>

<style type='text/css'>
<!--
body {
    font-size:9.0pt;
    margin:0%;
}
th {
    font-size:11.0pt;
}
td {
    font-size:9.0pt;
}
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
select          {background-color:teal;
                color:white;}
textarea        {background-color:black;
                color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black;
                color:white;}
.right          {text-align:right;}
.center         {text-align:center;}
.left           {text-align:left;}
.pt10           {font-size:10pt;}
.pt10b          {font-size:10pt;
                font:bold;}
.pt11           {font-size:11pt;}
.pt11b          {font-size:11pt;
                font:bold;}
.pt12b          {font-size:12pt;
                font:bold;}
.fc_red         {color:red;}
.fc_blue        {color:blue;}
.margin1        {margin:1%;}
-->
</style>
</head>
<body style='overflow-y:hidden;' onLoad='document.ini_form.rate_ym.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <div class='pt10'>����������ϥ����ƥ������˥塼�η��������¤������оݷ�μ���ߤ�Ԥä��塢�¹Ԥ��롣</div>
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <form name='ini_form' action='<?=$menu->out_self()?>' method='post' onSubmit='return ym_chk(this)'>
                <tr>
                    <td colspan='2' align='right' valign='middle' class='pt11'>
                        �о�ǯ�����ꤷ�Ʋ��������㡧200204 (2002ǯ04��)
                        <input type='text' name='rate_ym' size='7' value='<?php echo $rate_ym ?>' maxlength='6'>
                    </td>
                    <td align='left'>
                        <input class='pt11b' type='submit' name='tangetu' value='ñ�����'>
                    </td>
                </tr>
            </form>
            <form action='machine_labor_rate_mnt.php' method='post' onSubmit='return kessan_chk(this)'>
                <tr>
                    <td align='left' class='pt11'>
                        �о�ǯ�� �ϰϤ���ꤷ�Ʋ�������
                        <input type='text' name='str_ym' size='7' value='<?php echo($_SESSION['str_ym']); ?>' maxlength='6'>
                        ��
                        <input type='text' name='end_ym' size='7' value='<?php echo($_SESSION['end_ym']); ?>' maxlength='6'>
                    </td>
                    <td align='left' class='pt11'>
                        <label for='1'>���</label><input type='radio' name='span' value='1' id='1'<?php if($_SESSION['span']==1)echo ' checked' ?>>
                        <label for='2'>����</label><input type='radio' name='span' value='2' id='2'<?php if($_SESSION['span']==2)echo ' checked' ?>>
                        <label for='3'>��ͳ</label><input type='radio' name='span' value='3' id='3'<?php if($_SESSION['span']==3)echo ' checked' ?>>
                    </td>
                    <td align='left'>
                        <input class='pt11b' type='submit' name='kessan' value='�軻����'>
                    </td>
                </tr>
            </form>
        </table>
    <?php
    if(isset($_POST['insert'])){
        echo "<hr>\n";
        if (user_check($uid)) {
            echo "<br><font class='pt12b fc_blue'>��Ͽ���ޤ�����</font>\n";
        } else {
            echo "<br><font class='pt12b fc_red'>��Ͽ����ޤ���Ǥ�����</font>\n";
        }
    }
    else if(isset($_POST['tangetu']) || isset($_POST['kessan']) || isset($_POST['check'])){
    ?>
        <hr>
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <caption class='pt12b'>������Ψ�׻�ɽ</caption>
            <?php
            if($register == "��Ͽ")
                echo "<th colspan='3' class='fc_red'>$register</th>\n";
            else
                echo "<th colspan='3' class='fc_blue'>$register</th>\n";
            for($i=0;$i<$rows_act;$i++){
                echo "<th>" . $b_name[$i] . "(" . $act_id[$i] . ")</th>\n";
            }
            ?>
            <th nowrap>��¤���</th>
            <tr>
                <td rowspan='10' width='10'>ľ��������</td>
                <td rowspan='4' width='10'>ľ����</td>
                <td nowrap>����������</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($depre[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($depre[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($depre_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>�꡼����</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($lease[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($lease[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($lease_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>������</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($repair[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($repair[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($repair_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>�����������</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($w_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($w_cost[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($w_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='2' align='center'>����</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($s1_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($s1_sum[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($s1_sum_all) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td rowspan='4' width='10'>������</td>
                <td>�ͷ���</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($p_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($p_cost[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($p_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>������</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($e_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($e_cost[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($e_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>����¾</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($other[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($other[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($other_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>������������</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($m_cost_all[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($m_cost_all[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($m_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='2' align='center'>����</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($s2_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($s2_sum[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($s2_sum_all) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>���</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    if($m_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($m_sum[$i]) . "</td>\n";
                }
                ?>
            </tr>
            <?php
            if((!isset($_POST['check'])) && $register <> "�Ȳ�")
                echo "<form action='machine_labor_rate_mnt.php' method='post'>\n";
            ?>
            <tr>
                <td colspan='3' align='center'>���ȷ�������</td>
                <?php
                if(isset($_POST['check']) || $register == "�Ȳ�"){
                    $h_cost_sum = 0;    // �����
                    for($i=0;$i<$rows_act;$i++){
                        if($_SESSION['h_cost'][$i] > 0)
                            echo "<td nowrap align='right'>��" . number_format($_SESSION['h_cost'][$i]) . "</td>\n";
                        else
                            echo "<td nowrap align='right'>" . number_format($_SESSION['h_cost'][$i]) . "</td>\n";
                        $h_cost_sum += $_SESSION['h_cost'][$i];
                    }
                    if($h_cost_sum > 0)
                        echo "<td nowrap align='right'>��" . number_format($h_cost_sum) . "</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($h_cost_sum) . "</td>\n";
                }else{
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'><input type='text' class='right' name='h_cost[]' size='9' value='" . $_SESSION['h_cost'][$i] . "' maxlength='8'></td>\n";
                    }
                    echo "<td nowrap align='center'><input type='submit' name='check' value='��ǧ'></td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>���</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    if($t_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($t_sum[$i]) . "</td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>��°�Ͱ�</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($man[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($man[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($man_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>�ʼ�ޤൡ����ž����</td>
                <?php
                if(isset($_POST['check']) || $register == "�Ȳ�"){
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'>" . number_format($_SESSION['ope_time'][$i]) . "</td>\n";
                    }
                    echo "<td nowrap align='right'>" . number_format($ope_time_sum) . "</td>\n";
                }else{
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'><input type='text' class='right' name='ope_time[]' size='9' value='" . $_SESSION['ope_time'][$i] . "' maxlength='8'></td>\n";
                    }
                    echo "<td nowrap align='center'><input type='submit' name='check' value='��ǧ'></td>\n";
                }
                ?>
            </tr>
            <?php
            if((!isset($_POST['check'])) && $register <> "�Ȳ�")
                echo "</form>\n";
            ?>
            <tr>
                <td colspan='3' align='center' class='fc_red'>ľ�ܷ��� ������Ψ</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($_SESSION['ope_time'][$i] > 0)
                        echo "<td nowrap align='right' class='fc_red'>" . $labor_rate[$i] . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                if($labor_rate[$i] > 0)
                    echo "<td nowrap align='right' class='fc_red'>" . $labor_rate[$i] . "</td>\n";
                else
                    echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>ɸ���ࡡ�¡�Ψ</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    echo "<td nowrap align='right'>---</td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='<?php echo (4+$rows_act) ?>' align='center' bgcolor='white'>�������������¡�����</td>
            </tr>
            <tr>
                <td colspan='3' align='center'>ľ�ܷ���</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_t_cost[$i] > 0)
                        echo "<td nowrap align='right'>" . number_format($pre_t_cost[$i]) . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center' nowrap>�ʼ�ޤൡ����ž����</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_ope_time[$i] > 0)
                        echo "<td nowrap align='right'>" . number_format($pre_ope_time[$i]) . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>ľ�ܷ��� ������ΨA</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_rate[$i] > 0)
                        echo "<td nowrap align='right'>" . $pre_rate[$i] . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
        </table>
    <?php
    }
    if(isset($_POST['check'])){
        echo "<form action='machine_labor_rate_mnt.php' method='post'>\n";
        echo "<td nowrap align='center'><input type='submit' name='insert' value='��Ͽ' class='fc_red'></td>\n";
        echo "</form>\n";
    }
    ?>
    </center>
</body>
</html>
