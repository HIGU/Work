<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� ��������¹� �ã��̷����������ɽ ��������¸              //
// Copyright(C) 2003-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/01/29 ��������  profit_loss_cl_keihi_save.php                       //
// 2003/01/30 ���٥ե�����ɤΥǡ����׻�����λ���Ƥ���ñ��Ĵ�����ѹ�        //
// 2003/02/07 ���ټ¹Ԥ����ǡ��������Ѥ��ͤȤ��ƥե��������¸���뤿��      //
//            profit_loss_cl_keihi �� profit_loss_cl_keihi_save ���ѹ�      //
// 2003/02/12 ����Ψ�׻����¹Ԥ���Ƥ��ʤ����˥�å�������Ф��ƽ�λ      //
// 2003/03/04 �ǡ���������ȥ�󥶥��������ѹ� (�ǡ������ݾ�)             //
// 2004/05/06 ����ɸ����Ǥ��б��Τ���������β����ɲ�(7520)B36 $r=35       //
//            kin1=ľ���񥫥ץ� kin2=ľ�����˥� kin3=������ kin4=�δ���   //
//            $actcod=arry(,7520)����Τ������ɲä���դ����               //
// 2009/06/10 ��������501����η���������ɲ�                          ��ë //
// 2009/08/07 ʪή»���ɲäΰ١�580�������¤�����670������δ����        //
//            ����Ū�˥��ץ�˿�ʬ��������б�                         ��ë //
//              ��������»�פϥ��ץ餫��ʪή�������ɽ������褦�б�   ��ë //
// 2009/08/19 ʪή�򸡺�����ݲ���9999��actcod[]�����äƤ��ʤ��ä���        //
//            ���顼�ˤʤäƤ��ޤ��Τ���                             ��ë //
// 2011/08/04 ���ܷ���ι�פ���¤����ι�פη׻���ˡ���ѹ�           ��ë //
// 2015/02/20 ���졼���б���β����ɲ�(7550)B37 $r=36                       //
//            kin1=ľ���񥫥ץ� kin2=ľ�����˥� kin3=������ kin4=�δ���   //
//            $actcod=arry(,7550)����Τ������ɲä���դ����               //
//            $rec_keihi = 27��28���ѹ� (���졼���б����ɲäˤ��)          //
// 2016/10/04 ���������ݡ�545����η���������ɲ�                      ��ë //
// 2018/10/10 2018/09 ����񻺽������� ���٤ƥ��ץ���δ����          ��ë //
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
//////////// �ͷ��񡦷���Υ쥳���ɿ� �ե�����ɿ�
$rec_jin   =  8;    // �ͷ���λ��Ѳ��ܿ�
$rec_keihi = 28;    // ����λ��Ѳ��ܿ�
$f_mei     = 13;    // ����(ɽ)�Υե�����ɿ�
//////////// ������ܤ���������
/******
    8101 = �����
    8102 = ��������
    8103 = ��Ϳ����
    8104 = ������
    8105 = ˡ��ʡ����
    8106 = ����ʡ����
    8121 = ��Ϳ�����ⷫ��
    8123 = �࿦��������  ��̾���࿦��Ϳ�����ⷫ��
******/
$jin_act = array(8101,8102,8103,8104,8105,8106,8121,8123);
/******
    7501 = ι�������
    7502 = ������ĥ
    7503 = �̿���
    7504 = �����
    7505 = ���������
    7506 = ����������
    7508 = �����
    7509 = ���²�¤��
    7510 = �޽񶵰���
    7512 = ��̳������
    7520 = ������       // ����ɸ����Ǥˤ���ɲ�
    7521 = ���Ǹ���
    7522 = �������
    7523 = ����
    7524 = ������
    7525 = �ݾڽ�����
    7526 = ��̳�Ѿ�������
    7527 = �����������
    7528 = ��ξ��
    7530 = �ݸ���
    7531 = ��ƻ��Ǯ��
    7532 = ������
    7533 = ��ʧ�����
    7536 = �������
    7537 = ���ն�
    7538 = ������
    7540 = �¼���
    7550 = ���졼���б���
    8000 = ����������
******/
$kei_act = array(7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,7550,8000);
////// ���Τ�����   ����ɸ����Ǥλ�����(7520)��Ǹ���ɲ�
$actcod  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000,7520,7550);
$actcod_nkb  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000,7520,7550,9999);

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
////// �ǡ����١������ǡ���������
$res = array();     /*** ����Υǡ������� ***/  // kin1=ľ���񥫥ץ� kin2=ľ�����˥� kin3=������ kin4=�δ���
$query = sprintf("select kin1,kin2,kin3,kin4 from pl_bs_summary where pl_bs_ym=%d and t_id='B' order by t_id, t_row ASC", $yyyymm);
if (($rows=getResult($query,$res)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    for ($i=0; $i<$rows; $i++) {
        /*
        if ($res[$i][0] != 0)
            $res[$i][0] = ($res[$i][0] / $tani);
        if ($res[$i][1] != 0)
            $res[$i][1] = ($res[$i][1] / $tani);
        if ($res[$i][2] != 0)
            $res[$i][2] = ($res[$i][2] / $tani);
        if ($res[$i][3] != 0)
            $res[$i][3] = ($res[$i][3] / $tani);
        */
    }
    ///// ľ���� ������Ψ�Υǡ�������
    $res_jin = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='���ץ������Ψ'", $yyyymm);
    if ((getResult($query,$res_jin)) > 0) {
        $allo_c_kyu = $res_jin[0][0];
    } else {
        $allo_c_kyu = 0;
        $_SESSION['s_sysmsg'] .= "��������Ψ�׻����¹Ԥ���Ƥ��ޤ���<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// ľ���� ��������Ψ�Υǡ�������
    $res_kei = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='���ץ������Ψ'", $yyyymm);
    if ((getResult($query,$res_kei)) > 0) {
        $allo_c_kei = $res_kei[0][0];
    } else {
        $allo_c_kei = 0;
        $_SESSION['s_sysmsg'] .= "��������Ψ�׻����¹Ԥ���Ƥ��ޤ���<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// ������Ψ�Υǡ�������
    $res_kei = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='���ץ�������Ψ'", $yyyymm);
    if ((getResult($query,$res_kei)) > 0) {
        $allo_c_men = $res_kei[0][0];
    } else {
        $allo_c_men = 0;
        $_SESSION['s_sysmsg'] .= "��������Ψ�׻����¹Ԥ���Ƥ��ޤ���<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// ľ���� �����������Ψ�Υǡ�������
    $res_kei = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='���ץ��������Ψ'", $yyyymm);
    if ((getResult($query,$res_kei)) > 0) {
        $allo_c_shou = $res_kei[0][0];
    } else {
        $allo_c_shou = 0;
        $_SESSION['s_sysmsg'] .= "��������Ψ�׻����¹Ԥ���Ƥ��ޤ���<br>";
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    
    ///// �ͷ���ȷ����������
    $data      = array();       // �׻����ѿ� ����ǽ����
    $view_data = array();       // ɽ�����ѿ� ����ǽ����
    for ($r=0; $r<$rows; $r++) {
        for ($c=0; $c<$f_mei; $c++) {
            switch ($c) {
                case  0:        // ��¤�����פΥ��ץ�
                    if ($r >= 0 && $r < $rec_jin) {     // �ͷ���δ������������˰㤦��Ψ
                        $res_jin = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and (orign_id=173 or orign_id=174 or orign_id=500 or orign_id=501 or orign_id=545) and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $jin_act[$r]);
                        if ((getResult($query,$res_jin)) > 0) {
                            $data[$r][6] = $res_jin[0][0];                  // ������ ���ץ�
                        }
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 ������ ���ץ� �û�
                        }
                        // $data[$r][6] = Uround($res[$r][2] * 0.65);       // ������ ���ץ�
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // ��̳������ 7512 �¼��� 7540 ���������� 8000 �ã�������Ψ
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 ������ ���ץ� ����
                        }
                        $data[$r][6] = Uround($res[$r][2] * $allo_c_men);   // ������ ���ץ�
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 ������ ���ץ� �û�
                        }
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];         // ������� 580 �ᤷ
                        }
                    } elseif ($r == 24) {                                   // ����������� 7527
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 ������ ���ץ� ����
                        }
                        $data[$r][6] = Uround($res[$r][2] * $allo_c_shou);  // ������ ���ץ�
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 ������ ���ץ� �û�
                        }
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // ������� 580 �ᤷ
                        }
                    } else {                            // ����ϣã�ľ���� ��������Ψ
                        // $data[$r][6] = Uround($res[$r][2] * 0.853);      // ������ ���ץ�
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 ������ ���ץ� ����
                        }
                        $data[$r][6] = Uround($res[$r][2] * $allo_c_kei);   // ������ ���ץ�
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 ������ ���ץ� �û�
                        }
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // ������� 580 �ᤷ
                        }
                    }
                    break;
                case  1:        // ��¤�����פΥ�˥�
                    if ($r >= 0 && $r < $rec_jin) {     // �ͷ����������˰㤦��Ψ
                        $res_jin = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and (orign_id=173 or orign_id=174 or orign_id=500 or orign_id=501 or orign_id=545) and k_kubun='1' and div=' ' and dest_id=2", $yyyymm, $jin_act[$r]);
                        if ((getResult($query,$res_jin)) > 0) {
                            $data[$r][7] = $res_jin[0][0];
                        }
                        // $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * 0.65)));    // ������ ��˥�
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // ��̳������ 7512 �¼��� 7540 ���������� 8000 �ã�������Ψ
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 ������ ���ץ� ����
                        }
                        $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * $allo_c_men)));  // ������ ��˥�
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // ������� 580 �ᤷ
                        }
                    } elseif ($r == 24) {                                   // ����������� 7527
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 ������ ���ץ� ����
                        }
                        $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * $allo_c_shou))); // ������ ��˥�
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // ������� 580 �ᤷ
                        }
                    } else {                            // ����ϣã�ľ���� ��������Ψ
                        // $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * 0.853)));    // ������ ��˥�
                                                        // ���ץ�ʬ��������Ĥ꤬��˥�
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 ������ ���ץ� ����
                        }
                        $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * $allo_c_kei))); // ������ ��˥�
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // ������� 580 �ᤷ
                        }
                    }
                    break;
                case  2:        // ��¤����ι��
                    $data[$r][$c] = $data[$r][0] + $data[$r][1];
                    break;
                case  3:        // ľ�ܷ��� ���ץ�
                    $data[$r][$c] = $res[$r][0];
                    break;
                case  4:        // ľ�ܷ��� ��˥�
                    $data[$r][$c] = $res[$r][1];
                    break;
                case  5:        // ľ�ܷ��� ���
                    $data[$r][$c] = $data[$r][3] + $data[$r][4];
                    break;
                case  6:        // ���ܷ��� ���ץ�
                    // case 0 �Ƿ׻�
                    break;
                case  7:        // ���ܷ��� ��˥�
                    // case 1 �Ƿ׻�
                    break;
                case  8:        // ���ܷ��� ���
                    //$data[$r][$c] = $res[$r][2];
                    // 2011/08/04 ����
                    $data[$r][$c] = $data[$r][6] + $data[$r][7];
                    break;
                case  9:        // ��¤���� ���
                    //$data[$r][$c] = $res[$r][0] + $res[$r][1] + $res[$r][2];
                    // 2011/08/04 ����
                    $data[$r][$c] = $data[$r][3] + $data[$r][4] + $data[$r][6] + $data[$r][7];
                    break;
                case 10:        // �δ��� ���ץ�
                    if ($r >= 0 && $r < $rec_jin) {     // �ͷ���ϣã�ľ�ܵ�����
                        // $data[$r][$c] = Uround($res[$r][3] * 0.784);
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_kyu);
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 �δ��� ���ץ� �û�
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // ��̳������ 7512 �¼��� 7540 ���������� 8000 �ã�������Ψ
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        // 2018/09 ����񻺽��Ѽ�� �δ��񥫥ץ�Τߤ�
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] - 270803;
                            }
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_men);
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 �δ��� ���ץ� �û�
                        }
                        // 2018/09 ����񻺽��Ѽ�� �δ��񥫥ץ�Τߤ�
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $data[$r][$c] = $data[$r][$c] + 270803;
                            }
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                        // 2018/09 ����񻺽��Ѽ�� �δ��񥫥ץ�Τߤ�
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] + 270803;
                            }
                        }
                    } elseif ($r == 24) {                                   // ����������� 7527
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_shou);
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 �δ��� ���ץ� �û�
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                    } else {                            // ����ϣã�ľ���� ��������Ψ
                        // $data[$r][$c] = Uround($res[$r][3] * 0.853);
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_kei);  // �������Ϥ���������ͽ��
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 �δ��� ���ץ� �û�
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                    }
                    break;
                case 11:        // �δ��� ��˥�
                    if ($r >= 0 && $r < $rec_jin) {     // �ͷ���ϣã�ľ�ܵ�����
                        // $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * 0.784)));
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_kyu)));    // ���ץ�ʬ��������Ĥ꤬��˥�
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // ��̳������ 7512 �¼��� 7540 ���������� 8000 �ã�������Ψ
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        // 2018/09 ����񻺽��Ѽ�� �δ��񥫥ץ�Τߤ�
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] - 270803;
                            }
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_men)));
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                        // 2018/09 ����񻺽��Ѽ�� �δ��񥫥ץ�Τߤ�
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] + 270803;
                            }
                        }
                    } elseif ($r == 24) {                                   // ����������� 7527
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_shou)));
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                    } else {                            // ����ϣã�ľ���� ��������Ψ
                        // $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * 0.853)));
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 �δ��� ���ץ� ����
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_kei)));  // �������Ϥ���������ͽ��
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 �δ��� ���ץ� �ᤷ
                        }
                    }
                    break;
                case 12:        // �δ��� ���
                    $data[$r][$c] = $res[$r][3];
                    break;
                default:        // ����¾��̵����
                    $data[$r][$c] = $res[$r][$c];
                    break;
            }
        }
    }
    ///////// ��Ͽ�ѤߤΥ����å�
    $res_chk = array();
    $query = sprintf("select pl_bs_ym from act_cl_history where pl_bs_ym=%d", $yyyymm);
    if ((getResult($query,$res_chk)) > 0) {
        $_SESSION["s_sysmsg"] .= sprintf("����������� �¹ԺѤߤǤ�<br>�� %d�� %d��",$ki,$tuki);
        // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    } else {
        ///////// �ơ��֥���¸�ѥǡ���������
        $query_ins = "insert into act_cl_history (pl_bs_ym, actcod, kin00, kin01, kin02, kin03, kin04, kin05, 
            kin06, kin07, kin08, kin09, kin10, kin11, kin12) values (%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d)";
        $rec = count($actcod);
        for ($r=0; $r < $rec; $r++) {
        // foreach ($actcod as $code) {
            $query = sprintf($query_ins, $yyyymm, $actcod[$r], $data[$r][0], $data[$r][1], $data[$r][2], $data[$r][3], $data[$r][4]
                , $data[$r][5], $data[$r][6], $data[$r][7], $data[$r][8], $data[$r][9], $data[$r][10], $data[$r][11], $data[$r][12]);
            if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d �������ۤ���Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $actcod[$r]);
                $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }       ///// �Ǹ�Υ쥳���ɤ� ����¾�β��� ��ۤ����뤫�����å��Τ���
        $query = sprintf($query_ins, $yyyymm, 9999, $data[$r][0], $data[$r][1], $data[$r][2], $data[$r][3], $data[$r][4]
            , $data[$r][5], $data[$r][6], $data[$r][7], $data[$r][8], $data[$r][9], $data[$r][10], $data[$r][11], $data[$r][12]);
        if (query_affected_trans($con, $query) <= 0) {      ///// �ȥ�󥶥�������ѥ����꡼�μ¹�
            $_SESSION['s_sysmsg'] .= sprintf("ǯ��=%d:����=%d �������ۤ���Ͽ�˼��Ԥ��ޤ���<br>", $yyyymm, $actcod[$r]);
            $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("�оݥǡ���������ޤ���<br>�� %d�� %d��",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
$_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>��%d�� %d������������λ</font>",$ki,$tuki);
header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
exit();

