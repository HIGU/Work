<?php
//////////////////////////////////////////////////////////////////////////////
// ��������Х����οͰ���ӷ׻�ɽ����Ͽ�������ڤӾȲ����               //
// Copyright (C) 2009-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/24 Created   profit_loss_bls_input.php                           //
// 2009/11/02 �Х���롦�������ʬ�������˥�˥����δ���οͷ����        //
//            ���ɤؤμҰ���ʬ��Ϳ���̣����褦�ѹ�                        //
// 2009/12/09 �������ϫ̳��򣱣�ʬ�ϸ����ͤˤ���褦���ѹ�              //
//            �����ӥ������Ͽ˺��ΰ�                                      //
// 2009/12/10 �����Ȥ�����                                                //
// 2010/03/04 ź�Ĥ���ε�Ϳ������̣����ϫ̳���׻�����褦���ѹ�        //
// 2010/06/04 ��ư��ȼ��Ĵ���Ͱ�̾���ѹ�                                    //
// 2010/10/06 ����Υǡ������ԡ����ɲ�                                      //
// 2011/06/07 2011/04������������581�ɲ�                              //
// 2011/06/08 500����η��񤬻������������ۤ���Ƥ����Τ�               //
//            2011/06������ۤ��ʤ��褦���ѹ�                               //
// 2013/01/28 �Х�������Υݥ�פ��ѹ���ɽ���Τߥǡ����ϥХ����Τޤޡ�  //
// 2014/05/07 ��ư��ȼ��Ĵ���Ͱ�̾���ѹ�                                    //
// 2014/08/06 ���������Ȥ��ɲ�                                            //
// 2015/06/10 �����η׻����ɲ�                                              //
// 2015/06/15 �����ε�Ϳ�����6���٤���ѹ�                                 //
// 2015/11/06 �����ε�Ϳ�����10���٤���ѹ�                                //
//                                  �� �����᤹                             //
// 2016/02/02 ���Ĥ���������8��2�ʻ����˥��ˤ��ѹ�                    //
//            ���������Ϥ�����Ϳ��0.5���˥�������ä��������Ϥ�����Ϳ�� //
//            0.2������˥��ʡ�-0.2�ˤ��ѹ�                             //
// 2016/04/25 2016/04��굡���ε�Ϳ������ѹ�                               //
// 2016/07/22 �������ѵ�»�פΤ����ϫ̳�񡦷����׻���Ͽ                  //
// 2016/10/14 ���Ĥ�����ã������ѹ�����������׸�Ƥ$invent[16]��       //
// 2016/10/31 ��ã�����100���ʤΤǡ���ۤ����Ϥ��ʤ�(2016/10��)        //
// 2016/11/18 ���ֲ��˰��ķ�Ĺ��Ϳ��20���ư���ꤹ��褦�ɲ�              //
//            ��˥�����ޥ��ʥ�(ϫ̳��ʳ��ˤϱƶ������ʤ�)                //
// 2017/05/08 �ͻ���ư�ˤ��̾���ѹ�                                        //
// 2017/05/09 2017/05��굡���������ѹ�����������Ĺ���к��Ĺ����ʬ��     //
// 2018/04/19 2018/04��굡���������ѹ�����������Ĺ�����Ĳ�Ĺʬ��         //
// 2018/10/17 �����Ȥ���                                                //
// 2019/02/05 �����Ȥ���                                                //
// 2019/05/09 �ͻ���ư��ȼ��̾�Τ��ѹ�                                      //
// 2021/03/03 ������λ��ȼ�����ۤν�λ                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting',E_ALL);        // E_ALL='2047' debug ��
// ini_set('display_errors','1');           // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
   // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

///// ����������
// $menu->set_site(10, 7);                  // site_index=10(»�ץ�˥塼) site_id=7(�»��)
///// ɽ�������
// $menu->set_caption('�������칩��(��)');
///// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SESSION['pl_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

///// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("��{$ki}����{$tuki}���١��£̣� �Ͱ���Ψ�׻�ɽ");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// yymm����
$ym4 = substr($yyyymm, 2, 4);

///// �Х���������Ψ�Υǡ�������
$res_b_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='�Х��������'", $yyyymm);
if ((getResult($query,$res_b_allo)) > 0) {
    $bimor_allo = $res_b_allo[0][0];
} else {
    $_SESSION['s_sysmsg'] .= "�ã�»�׷׻����¹Ԥ���Ƥ��ޤ���<br>";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// ���������Ψ�Υǡ�������
$res_t_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
if ((getResult($query,$res_t_allo)) > 0) {
    $tool_allo = $res_t_allo[0][0];
} else {
    $tool_allo = 0;
}
///// ������������Ψ�Υǡ�������
$res_s_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
if ((getResult($query,$res_s_allo)) > 0) {
    $ss_allo = $res_s_allo[0][0];
} else {
    $_SESSION['s_sysmsg'] .= "�ã�»�׷׻����¹Ԥ���Ƥ��ޤ���<br>";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// ���ץ������������Ψ�Υǡ�������
if ($yyyymm >= 200911) {
    $res_sc_allo = array();
    $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $yyyymm);
    if ((getResult($query,$res_sc_allo)) > 0) {
        $sc_allo = $res_sc_allo[0][0];
    } else {
        $_SESSION['s_sysmsg'] .= "���ץ�����������夬��Ͽ����Ƥ��ޤ���<br>��˥��ץ���������������Ͽ���Ƥ���������<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// ���ɼҰ���ʬ��Ϳ�ʥ�˥��˥ǡ�������
$l_allo_kin = 0;
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $yyyymm);
    $res = array();
    if ((getResult($query,$res)) > 0) {
        $l_allo_kin = $res[0][0];
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("���ɤ�»����Ͽ������Ƥ��ޤ���<br>��˾��ɤؤΰ�ʬ��Ϳ�����Ϥ�ԤäƤ���������", $yyyymm);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// ��˥��δ���οͷ���ǡ�������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��ͷ���'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $l_jin = $res[0][0] - $l_allo_kin;
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�ã�»�׷׻����¹Ԥ���Ƥ��ޤ���<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// ��˥��δ���η���ǡ�������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $l_kei = $res[0][0];
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�ã�»�׷׻����¹Ԥ���Ƥ��ޤ���<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

///// ��������Ψ�μ���
$ss_uri_allo = 0;
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
$res = array();
if ((getResult($query,$res)) > 0) {
    $ss_uri_allo = $res[0][0];
} else {
    $ss_uri_allo = 0;
}

///// �ѵ�����Ψ�μ���
$st_uri_allo = 0;
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='�ѵ�����'", $yyyymm);
$res = array();
if ((getResult($query,$res)) > 0) {
    $st_uri_allo = $res[0][0];
} else {
    $st_uri_allo = 0;
}

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

///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
$item = array();
$item[0]   = "��˥��Ұ���";
$item[1]   = "��˥��ѡ��ȿ�";
$item[2]   = "�Х����ѡ��ȿ�";
$item[3]   = "������ѡ��ȿ�";
$item[4]   = "�Х����Ұ�����";
$item[5]   = "�Х����Ұ�����";
$item[6]   = "�Х����Ұ�����";
$item[7]   = "������Ұ�����";
$item[8]   = "������Ұ�����";
$item[9]   = "������Ұ�����";
$item[10]  = "�Х����Ұ����۵�Ϳ";
$item[11]  = "������Ұ����۵�Ϳ";
$item[12]  = "���ץ��Ϳ����Ψ";
$item[13]  = "��˥���Ϳ����Ψ";
$item[14]  = "���Ϳ����Ψ";
$item[15]  = "���Ϳ�����";
$item[16]  = "������Ұ����۵�Ϳ��";
$item[17]   = "�����Ұ����۵�Ϳ��";
$item[18]   = "�����Ұ����۵�Ϳ��";
$item[19]   = "�����Ұ����۵�Ϳ��";
$item[20]   = "�����Ұ����۵�Ϳ��";
$item[21]   = "�����Ұ����۵�Ϳ��";
$item[22]   = "��������Ĵ��";
$item[23]   = "������Ұ����۵�Ϳ��";
///////// ����text �ѿ� �����
$invent = array();
for ($i = 0; $i < 24; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i]   = $_POST['invent'][$i];
        $invent_z[$i] = $_POST['invent_z'][$i];     // ����ʬ
    } else {
        $invent[$i]   = 0;
        $invent_z[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {                      // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ��ê����ۼ����������
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10 && $i <= 11) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } elseif ($i >= 12 && $i <= 14) {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } elseif ($i >= 15) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res) > 0) {
            $invent[$i] = $res[0][0];
        } else {
            $invent[$i] = 0;
        }
    }
    $l_part50 = UROUND(($invent[1] * 0.5),2);           // ��˥��ѡ��ȳ�Ψ50��������
    $l_total  = $invent[0] + $l_part50;                 // ��˥��ס������
    $b_shain  = $invent[4] + $invent[5] + $invent[6];   // �Х����Ұ����������
    $b_part50 = UROUND(($invent[2] * 0.5),2);           // �Х����ѡ��ȳ�Ψ50��������
    $b_total  = $b_shain + $b_part50;                   // �Х����ס������
    $s_shain  = $invent[7] + $invent[8] + $invent[9];   // ��Ұ����������
    $s_part50 = UROUND(($invent[3] * 0.5),2);           // ��ѡ��ȳ�Ψ50��������
    $s_total  = $s_shain + $s_part50;                   // ��ס������
    
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[12]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $c_hai_kin = number_format($res[0][0]);
    } else {
        $c_hai_kin = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[13]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $l_hai_kin = number_format($res[0][0]);
    } else {
        $l_hai_kin = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[14]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $s_hai_kin = number_format($res[0][0]);
    } else {
        $s_hai_kin = 0;
    }
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10 && $i <= 11) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 12 && $i <= 14) {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 15) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res_z) > 0) {
            $invent_z[$i] = $res_z[0][0];
        } else {
            $invent_z[$i] = 0;
        }
    }
    $l_part50_z = UROUND(($invent_z[1] * 0.5),2);               // ��˥��ѡ��ȳ�Ψ50��������
    $l_total_z  = $invent_z[0] + $l_part50_z;                   // ��˥��ס������
    $b_shain_z  = $invent_z[4] + $invent_z[5] + $invent_z[6];   // �Х����Ұ����������
    $b_part50_z = UROUND(($invent_z[2] * 0.5),2);               // �Х����ѡ��ȳ�Ψ50��������
    $b_total_z  = $b_shain_z + $b_part50_z;                     // �Х����ס������
    $s_shain_z  = $invent_z[7] + $invent_z[8] + $invent_z[9];   // ��Ұ����������
    $s_part50_z = UROUND(($invent_z[3] * 0.5),2);               // ��ѡ��ȳ�Ψ50��������
    $s_total_z  = $s_shain_z + $s_part50_z;                     // ��ס������
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[12]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $c_hai_kin_z = number_format($res[0][0]);
    } else {
        $c_hai_kin_z = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[13]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $l_hai_kin_z = number_format($res[0][0]);
    } else {
        $l_hai_kin_z = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[14]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $s_hai_kin_z = number_format($res[0][0]);
    } else {
        $s_hai_kin_z = 0;
    }
    
    if (isset($_POST['copy'])) {                        // ����ǡ����Υ��ԡ�
        $l_part50 = $l_part50_z;                        // ��˥��ѡ��ȳ�Ψ50��������
        $l_total  = $l_total_z;                         // ��˥��ס������
        $b_shain  = $b_shain_z;                         // �Х����Ұ����������
        $b_part50 = $b_part50_z;                        // �Х����ѡ��ȳ�Ψ50��������
        $b_total  = $b_total_z;                         // �Х����ס������
        $s_shain  = $s_shain_z;                         // ��Ұ����������
        $s_part50 = $s_part50_z;                        // ��ѡ��ȳ�Ψ50��������
        $s_total  = $s_total_z;                         // ��ס������
        $c_hai_kin = $c_hai_kin_z;
        $l_hai_kin = $l_hai_kin_z;
        $s_hai_kin = $s_hai_kin_z;
        for ($i = 0; $i < 24; $i++) {
            $invent[$i] = $invent_z[$i];
        }
    }
} else {    // ��Ͽ����  �ȥ�󥶥������ǹ������Ƥ��뤿��쥳����ͭ��̵���Υ����å��Τ�
    $allo_kei = $invent[12] + $invent[13] + $invent[14];
    if ($allo_kei != 100) {
        if ($allo_kei != 0) {
            $_SESSION["s_sysmsg"] .= "Ψ���������ǤϤ���ޤ���";
            header("Location: $current_script");
            exit();
        } else {
            $invent[12] = 0;
            $invent[13] = 0;
            $invent[14] = 0;
            $invent[15] = 0;
        }
    }
    $l_part50 = UROUND(($invent[1] * 0.5),2);
    $l_total  = $invent[0] + $l_part50;
    $b_shain  = $invent[4] + $invent[5] + $invent[6];
    $b_part50 = UROUND(($invent[2] * 0.5),2);
    $b_total  = $b_shain + $b_part50;
    $s_shain  = $invent[7] + $invent[8] + $invent[9];
    $s_part50 = UROUND(($invent[3] * 0.5),2);
    $s_total  = $s_shain + $s_part50;
    
    $l_part50_z = UROUND(($invent_z[1] * 0.5),2);
    $l_total_z  = $invent_z[0] + $l_part50_z;
    $b_shain_z  = $invent_z[4] + $invent_z[5] + $invent_z[6];
    $b_part50_z = UROUND(($invent_z[2] * 0.5),2);
    $b_total_z  = $b_shain_z + $b_part50_z;
    $s_shain_z  = $invent_z[7] + $invent_z[8] + $invent_z[9];
    $s_part50_z = UROUND(($invent_z[3] * 0.5),2);
    $s_total_z  = $s_shain_z + $s_part50_z;
    $c_hai_kin_z = number_format($c_hai_kin_z);
    $l_hai_kin_z = number_format($l_hai_kin_z);
    $s_hai_kin_z = number_format($s_hai_kin_z);
    
    // �������Ϳ����׻���ź�Ĥ���ʬ �������ϫ̳����ޥ��ʥ����Ƴ�����Ψ�����ꤹ���
    $ckyu_kin = 0;      // ���ץ��Ϳ�����
    $lkyu_kin = 0;      // ��˥���Ϳ�����
    $skyu_kin = 0;      // ���Ϳ�����
    
    $allkyu_kin = $invent[15];
    $ckyu_kin   = UROUND(($invent[15] * $invent[12] / 100), 0);
    $lkyu_kin   = UROUND(($invent[15] * $invent[13] / 100), 0);
    $skyu_kin   = $invent[15] - $ckyu_kin - $lkyu_kin;
    // CL���������׻�
    $sckyu_kin = UROUND(($skyu_kin * $sc_allo), 0);
    $slkyu_kin = $skyu_kin - $sckyu_kin;
    
    $c_hai_kin = number_format($ckyu_kin);
    $l_hai_kin = number_format($lkyu_kin);
    $s_hai_kin = number_format($skyu_kin);    
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10 && $i <= 11) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 12 && $i <= 14) {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 15) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res_z) > 0) {
            $invent_z[$i] = $res_z[0][0];
        } else {
            $invent_z[$i] = 0;
        }
    }
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res) <= 0) {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                header("Location: $current_script");
                exit();
            }
            ////////// Insert Start
            if ($i >= 10 && $i <= 11) {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            } elseif ($i >= 12 && $i <= 15) {
                if ($i == 12) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $ckyu_kin, $item[$i], $invent[$i]);
                } elseif ($i == 13) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $lkyu_kin, $item[$i], $invent[$i]);
                } elseif ($i == 14) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $skyu_kin, $item[$i], $invent[$i]);
                } elseif ($i == 15) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $invent[$i], $item[$i], 100.00);
                }
            } elseif ($i >= 16) {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            } else {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, allo, note) values (%d, %1.1f, '%s')", $yyyymm, $invent[$i], $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� ���� ��Ͽ��λ</font>",$ki,$tuki);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                header("Location: $current_script");
                exit();
            }
            ////////// UPDATE Start
            if ($i >= 10 && $i <= 11) {
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            } elseif ($i >= 12 && $i <= 15) {
                if ($i == 12) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $ckyu_kin, $invent[$i], $yyyymm, $item[$i]);
                } elseif ($i == 13) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $lkyu_kin, $invent[$i], $yyyymm, $item[$i]);
                } elseif ($i == 14) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $skyu_kin, $invent[$i], $yyyymm, $item[$i]);
                } elseif ($i == 15) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $invent[$i], 100.00, $yyyymm, $item[$i]);
                }
            } elseif ($i >= 16) {
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            } else {
                $query = sprintf("update act_pl_history set allo=%1.1f where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� �ѹ� ��λ</font>",$ki,$tuki);
        }
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���Ϳ�����'", $yyyymm);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            header("Location: $current_script");
            exit();
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ���Ϳ�����')", $yyyymm, $sckyu_kin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("���ץ���Ϳ����ۤο�����Ͽ�˼���<br>�� %d�� %d��", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� ���� ��Ͽ��λ</font>",$ki,$tuki);
    } else {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            header("Location: $current_script");
            exit();
        }
        ////////// UPDATE Start
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ���Ϳ�����'", $sckyu_kin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("���ץ���Ϳ����ۤ�UPDATE�˼���<br>�� %d�� %d��", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� �ѹ� ��λ</font>",$ki,$tuki);
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����Ϳ�����'", $yyyymm);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            header("Location: $current_script");
            exit();
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥����Ϳ�����')", $yyyymm, $slkyu_kin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("��˥����Ϳ����ۤο�����Ͽ�˼���<br>�� %d�� %d��", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� ���� ��Ͽ��λ</font>",$ki,$tuki);
    } else {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            header("Location: $current_script");
            exit();
        }
        ////////// UPDATE Start
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥����Ϳ�����'", $slkyu_kin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("��˥����Ϳ����ۤ�UPDATE�˼���<br>�� %d�� %d��", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� �ѹ� ��λ</font>",$ki,$tuki);
    }

    // �Х����ϫ̳��
    $b_roumu = 0;
    // 560 ����ϫ̳��
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $b_roumu += $res[0][0];
    // �Х��������� �����ӥ������
    $query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d and (total_item='�Х����' or total_item='����Х�')", $yyyymm);
    $res   = array();
    getResult($query, $res);
    $b_roumu += $res[0][0];
    // 500 ��2������ϫ̳������
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $b_roumu += Uround(($res[0][0] * $bimor_allo),0);
    // �Х������ܼҰ��ε�Ϳ20��
    $b_roumu += Uround(($invent[10] * 0.2),0);
    
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ϫ̳��'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����ϫ̳��')", $yyyymm, $b_roumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�Х����ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х����ϫ̳��'", $b_roumu, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�Х����ϫ̳��ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    
    // �����ϫ̳��
    $s_roumu = 0;
    // 559 ����ϫ̳��
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=559 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $s_roumu += $res[0][0];
    $roumu_559 = $res[0][0];    // ����ϫ̳�����׻�
    // 2011/04 ��� 581 ����ϫ̳��
    if ($yyyymm >= 201104) {
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=581 and actcod>=8101 and actcod<=8130", $ym4);
        $res   = array();
        getResult($query, $res);
        $s_roumu += $res[0][0];
        $roumu_581 = $res[0][0];    // �ѵ�ϫ̳�����׻�
    }
    // ���������� �����ӥ������
    $query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d", $yyyymm);
    $res   = array();
    getResult($query, $res);
    $s_roumu += Uround(($res[0][0] * $ss_allo),0);
    if ($yyyymm < 201106) {                        // 2011ǯ6�������ꤷ�ʤ�
        // 500 ��2������ϫ̳������
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod>=8101 and actcod<=8130", $ym4);
        $res   = array();
        getResult($query, $res);
        $s_roumu += Uround(($res[0][0] * $ss_allo),0);
    }
    // ��������ܼҰ��ε�Ϳ20��
    if ($yyyymm < 201104) {
        $s_roumu += Uround(($invent[11] * 0.2),0);
    } else { // 201104���10%
        $s_roumu += Uround(($invent[11] * 0.1),0);
    }
    if ($yyyymm == 200911) {                        // 2009ǯ11��ϸ�����
        $s_roumu = 2001186;
    }
    $s_roumu += Uround(($invent[16] * -0.2),0);
    
    // ���Ĥ���ε�Ϳ������������� �� ����ѵפΤߤ�����
    $s_roumu += Uround(($invent[23] * 0.1),0);
    
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ϫ̳��')", $yyyymm, $s_roumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ϫ̳��'", $s_roumu, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�ϫ̳��ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // ����ϫ̳��
    $t_roumu = 0;
    // 560 ����ϫ̳��
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $t_roumu += $res[0][0];
    if ($yyyymm >= 202102) {
        $t_roumu = 0;
    }
    // ��˼��Ĺ�����ε�Ϳ6��ޤǤ�50�󤽤�ʹߤ�5��
    if ($yyyymm >= 201504 && $yyyymm <= 201505) {
        $t_roumu += Uround(($invent[17] * 0.5),0);
        // ���Ĳ�Ĺ�ε�Ϳ5���0.05��(4��ϵ�Ϳ��Ͽ�ʤ��ʤΤ�IFʸ�ʤ�)
        $t_roumu += Uround(($invent[18] * 0.05),0);
        // �滳��Ĺ�����ε�Ϳ5���0.05��(4��ϵ�Ϳ��Ͽ�ʤ��ʤΤ�IFʸ�ʤ�)
        $t_roumu += Uround(($invent[19] * 0.05),0);
        // ͽ���Ұ��ε�Ϳ5���0.05��(4��ϵ�Ϳ��Ͽ�ʤ��ʤΤ�IFʸ�ʤ�)
        $t_roumu += Uround(($invent[20] * 0.05),0);
    } elseif ($yyyymm >= 201506 && $yyyymm <= 201509) {
        // 6��ʹߡ���Ⱦ���ϰʲ���Ψ��5��ޤǤȤϽ��֤���Ψ��㤦��
        // $invent[17] ����ë��Ĺ 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] ��˼��Ĺ���� 50%
        $t_roumu += Uround(($invent[18] * 0.5),0);
        // $invent[19] ���Ĳ�Ĺ 30%
        $t_roumu += Uround(($invent[19] * 0.3),0);
        // $invent[20] �滳��Ĺ���� 5%
        $t_roumu += Uround(($invent[20] * 0.05),0);
    } elseif($yyyymm >= 201510 && $yyyymm <= 201603) {
        // $invent[17] ����ë��Ĺ 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] ��˼��Ĺ���� 50%
        $t_roumu += Uround(($invent[18] * 0.5),0);
        // $invent[19] ���Ĳ�Ĺ 30%
        $t_roumu += Uround(($invent[19] * 0.3),0);
        // $invent[20] �滳��Ĺ���� 5%
        $t_roumu += Uround(($invent[20] * 0.05),0);
    } elseif($yyyymm >= 201604 && $yyyymm <= 201703) {
        // $invent[17] ����ë������Ĺ 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] ��˼��Ĺ 10%
        $t_roumu += Uround(($invent[18] * 0.1),0);
        // $invent[19] ��������Ĺ 40%
        $t_roumu += Uround(($invent[19] * 0.4),0);
        // $invent[20] �滳��Ĺ 10%
        $t_roumu += Uround(($invent[20] * 0.1),0);
    } elseif($yyyymm >= 201704 && $yyyymm <= 201703) {
        // $invent[17] ����ë������Ĺ 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] ��˼��Ĺ 10%
        $t_roumu += Uround(($invent[18] * 0.1),0);
        // $invent[19] ��������Ĺ 80%
        $t_roumu += Uround(($invent[19] * 0.8),0);
        // $invent[20] �滳��Ĺ 10%
        $t_roumu += Uround(($invent[20] * 0.1),0);
        // $invent[21] �к��Ĺ���� 20%
        $t_roumu += Uround(($invent[21] * 0.2),0);
    } elseif($yyyymm >= 201804) {
        // $invent[17] ����0 10��
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] ��˼��Ĺ 10%
        $t_roumu += Uround(($invent[18] * 0.1),0);
        // $invent[19] ��������Ĺ 10%
        $t_roumu += Uround(($invent[19] * 0.1),0);
        // $invent[20] �滳��Ĺ 10%
        $t_roumu += Uround(($invent[20] * 0.1),0);
        // $invent[21] ���Ĳ�Ĺ 80%
        if($yyyymm >= 202010) {
            $t_roumu += Uround(($invent[21] * 0.1),0);
        } else {
            $t_roumu += Uround(($invent[21] * 0.8),0);
        }
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '����ϫ̳��')", $yyyymm, $t_roumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='����ϫ̳��'", $t_roumu, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳��ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // ������¤����
    $t_keihi = 0;
    // 560 �������
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod<=8000", $ym4);
    $res = array();
    getResult($query, $res);
    $t_keihi += $res[0][0];
    // ��������Ĵ�����ɲ� 560����ʳ��ǽ������Ƥ��ޤä���ۤ��ɲ�
    $t_keihi += $invent[22];
    if ($yyyymm >= 202102) {
        $t_keihi = 0;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '������¤����')", $yyyymm, $t_keihi);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("������¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='������¤����'", $t_keihi, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("������¤����ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }

    //////////////////////////////////////  �δ�����Ͽ
    //////////////////////////////////////  �������������
    // �Х�����δ���ͷ������Ͽ
    if ($l_total == 0) {
        $b_han_jin = 0;
    } elseif ($b_total == 0) {
        $b_han_jin = 0;
    } elseif ($l_jin == 0) {
        $b_han_jin = 0;
    } else {
        $b_han_jin = Uround(($l_jin * $b_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ͷ���'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����ͷ���')", $yyyymm, $b_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�Х����ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х����ͷ���'", $b_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�Х����ͷ���ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // ������δ���ͷ������Ͽ
    if ($l_total == 0) {
        $s_han_jin = 0;
    } elseif ($s_total == 0) {
        $s_han_jin = 0;
    } elseif ($l_jin == 0) {
        $s_han_jin = 0;
    } else {
        $s_han_jin = Uround(($l_jin * $s_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��ͷ���')", $yyyymm, $s_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("��ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��ͷ���'", $s_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("��ͷ���ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // �����δ���ͷ������Ͽ
    $t_han_jin = Uround((($l_jin - $s_han_jin) * $tool_allo),0);
    if($yyyymm == 202010) {
        $t_han_jin = $t_han_jin - 97000;
    }
    if($yyyymm >= 202102) {
        $t_han_jin = 0;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����ͷ���'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����ͷ���')", $yyyymm, $t_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�����ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����ͷ���'", $t_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�����ͷ���ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // �Х�����δ���������Ͽ
    if ($l_total == 0) {
        $b_han_kei = 0;
    } elseif ($b_total == 0) {
        $b_han_kei = 0;
    } elseif ($l_kei == 0) {
        $b_han_kei = 0;
    } else {
        $b_han_kei = Uround(($l_kei * $b_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����δ������'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х�����δ������')", $yyyymm, $b_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�Х�����δ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х�����δ������'", $b_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�Х�����δ������ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // ������δ���������Ͽ
    if ($l_total == 0) {
        $s_han_kei = 0;
    } elseif ($s_total == 0) {
        $s_han_kei = 0;
    } elseif ($l_kei == 0) {
        $s_han_kei = 0;
    } else {
        $s_han_kei = Uround(($l_kei * $s_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��δ������')", $yyyymm, $s_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("��δ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��δ������'", $s_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("��δ������ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // �����δ���������Ͽ
    $t_han_kei = Uround((($l_kei - $s_han_kei) * $tool_allo),0);
    if($yyyymm == 202010) {
        $t_han_kei = $t_han_kei - 95000;
    }
    if($yyyymm >= 202102) {
        $t_han_kei = 0;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����δ������')", $yyyymm, $t_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�����δ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����δ������'", $t_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�����δ������ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // ����� CL ����Ʒ׻�
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    if ($yyyymm >= 200911) {
        $ss_item = array();
        $ss_item[0]   = "�ϫ̳��";
        $ss_item[1]   = "���¤����";
        $ss_item[2]   = "��ͷ���";
        $ss_item[3]   = "��δ������";
        $ss_item[4]   = "���̳��������";
        $ss_item[5]   = "��������";
        $ss_item[6]   = "��Ķȳ����פ���¾";
        $ss_item[7]   = "���ʧ��©";
        $ss_item[8]   = "��Ķȳ����Ѥ���¾";
        $ss_item[9]   = "���ץ�ϫ̳��";
        $ss_item[10]  = "���ץ���¤����";
        $ss_item[11]  = "���ץ��ͷ���";
        $ss_item[12]  = "���ץ��δ������";
        $ss_item[13]  = "���ץ���̳��������";
        $ss_item[14]  = "���ץ��������";
        $ss_item[15]  = "���ץ��Ķȳ����פ���¾";
        $ss_item[16]  = "���ץ���ʧ��©";
        $ss_item[17]  = "���ץ��Ķȳ����Ѥ���¾";
        $ss_item[18]  = "��˥��ϫ̳��";
        $ss_item[19]  = "��˥����¤����";
        $ss_item[20]  = "��˥���ͷ���";
        $ss_item[21]  = "��˥���δ������";
        $ss_item[22]  = "��˥����̳��������";
        $ss_item[23]  = "��˥���������";
        $ss_item[24]  = "��˥���Ķȳ����פ���¾";
        $ss_item[25]  = "��˥����ʧ��©";
        $ss_item[26]  = "��˥���Ķȳ����Ѥ���¾";
        ////////// ��Ͽ�Ѥߤʤ�ж�ۼ���
        $ss_invent = array();
        for ($i = 0; $i < 27; $i++) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $ss_item[$i]);
            $res = array();
            if (getResult2($query,$res) > 0) {
                $ss_invent[$i] = $res[0][0];
            }
        }
        // ���ץ�
        $ss_roumu      = $ss_invent[0] - $allkyu_kin + $skyu_kin;
        $ss_invent[9]  = Uround(($ss_roumu * $sc_allo),0);
        //$ss_invent[9]  = Uround(($ss_invent[0] * $sc_allo),0);
        $ss_invent[10] = Uround(($ss_invent[1] * $sc_allo),0);
        $ss_invent[11] = Uround(($ss_invent[2] * $sc_allo),0);
        $ss_invent[12] = Uround(($ss_invent[3] * $sc_allo),0);
        $ss_invent[13] = Uround(($ss_invent[4] * $sc_allo),0);
        $ss_invent[14] = Uround(($ss_invent[5] * $sc_allo),0);
        $ss_invent[15] = Uround(($ss_invent[6] * $sc_allo),0);
        $ss_invent[16] = Uround(($ss_invent[7] * $sc_allo),0);
        $ss_invent[17] = Uround(($ss_invent[8] * $sc_allo),0);
        // ��˥�
        $ss_invent[18] = $ss_roumu - $ss_invent[9];
        //$ss_invent[18] = $ss_invent[0] - $ss_invent[9];
        $ss_invent[19] = $ss_invent[1] - $ss_invent[10];
        $ss_invent[20] = $ss_invent[2] - $ss_invent[11];
        $ss_invent[21] = $ss_invent[3] - $ss_invent[12];
        $ss_invent[22] = $ss_invent[4] - $ss_invent[13];
        $ss_invent[23] = $ss_invent[5] - $ss_invent[14];
        $ss_invent[24] = $ss_invent[6] - $ss_invent[15];
        $ss_invent[25] = $ss_invent[7] - $ss_invent[16];
        $ss_invent[26] = $ss_invent[8] - $ss_invent[17];
        
        for ($i = 0; $i < 27; $i++) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $ss_item[$i]);
            $res = array();
            if (getResult2($query,$res) <= 0) {
                /////////// begin �ȥ�󥶥�����󳫻�
                if ($con = db_connect()) {
                    query_affected_trans($con, "begin");
                } else {
                    $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                    header("Location: $current_script");
                    exit();
                }
                ////////// Insert Start
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $ss_invent[$i], $ss_item[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br>�� %d�� %d��", $ss_item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit �ȥ�󥶥������λ
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� ���� ��Ͽ��λ</font>",$ki,$tuki);
            } else {
                /////////// begin �ȥ�󥶥�����󳫻�
                if ($con = db_connect()) {
                    query_affected_trans($con, "begin");
                } else {
                    $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                    header("Location: $current_script");
                    exit();
                }
                ////////// UPDATE Start
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $ss_invent[$i], $yyyymm, $ss_item[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br>�� %d�� %d��", $ss_item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit �ȥ�󥶥������λ
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� BLS�Ͱ���ӷ׻��ǡ��� �ѹ� ��λ</font>",$ki,$tuki);
            }
        }
        // �ѵס�����»�׷׻���
        // ϫ̳��׻�
        //$ss_roumu  = $roumu_559 + $invent[15];  // ���꽤��ϫ̳���559����ϫ̳��Ⱥ��ꤵ��ε�Ϳ���
        $ss_roumu  = $invent[15];  // ���꽤��ϫ̳���559����ϫ̳��Ⱥ��ꤵ��ε�Ϳ���
        $st_roumu  = $roumu_581 + Uround(($invent[16] * -0.2),0) + Uround(($invent[23] * 0.2),0);  // �����ѵ�ϫ̳���581����ϫ̳��Ȱ�ã����ε�Ϳ��8��ι�פȰ��Ĥ���ε�Ϳ20��
        
        // �������ѵפ�ϫ̳��׻�
        $s_roumu_all  = $ss_invent[0];
        $roumu_sagaku = $s_roumu_all - $ss_roumu - $st_roumu;
        if($roumu_sagaku <> 0) {
            //$ss_roumu_sagaku = Uround(($roumu_sagaku * $ss_uri_allo), 0);
            $ss_roumu_sagaku = 0;
            $st_roumu_sagaku = $roumu_sagaku - $ss_roumu_sagaku;
            $ss_roumu    = $ss_roumu + $ss_roumu_sagaku;
            $st_roumu    = $st_roumu + $st_roumu_sagaku;
        }
        // ��¤����׻�
        // 559 ������� ������¤����
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=559 and actcod<=8000", $ym4);
        $res = array();
        getResult($query, $res);
        $ss_keihi = $res[0][0];
        // 581 ������� �ѵ���¤����
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=581 and actcod<=8000", $ym4);
        $res = array();
        getResult($query, $res);
        $st_keihi = $res[0][0];
        
        // �������ѵפ���¤����׻�
        $s_keihi_all  = $ss_invent[1];
        $keihi_sagaku = $s_keihi_all - $ss_keihi - $st_keihi;
        if($keihi_sagaku <> 0) {
            $ss_keihi_sagaku = Uround(($keihi_sagaku * $ss_uri_allo), 0);
            $st_keihi_sagaku = $keihi_sagaku - $ss_keihi_sagaku;
            $ss_keihi    = $ss_keihi + $ss_keihi_sagaku;
            $st_keihi    = $st_keihi + $st_keihi_sagaku;
        }
        
        // �ͷ���׻�
        $s_han_jin_all = $ss_invent[2];
        $st_han_jin    = Uround(($s_han_jin_all * $st_uri_allo), 0);
        $ss_han_jin    = $s_han_jin_all - $st_han_jin;
        
        // �δ������׻�
        $s_han_kei_all  = $ss_invent[3];
        $st_han_kei    = Uround(($s_han_kei_all * $st_uri_allo), 0);
        $ss_han_kei    = $s_han_kei_all - $st_han_kei;
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '����ϫ̳��')", $yyyymm, $ss_roumu);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='����ϫ̳��'", $ss_roumu, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ�ϫ̳��'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵ�ϫ̳��')", $yyyymm, $st_roumu);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵ�ϫ̳��'", $st_roumu, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ��¤����
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '������¤����')", $yyyymm, $ss_keihi);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='������¤����'", $ss_keihi, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������¤����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ���¤����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵ���¤����')", $yyyymm, $st_keihi);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ���¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵ���¤����'", $st_keihi, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ���¤����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����ͷ���'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����ͷ���')", $yyyymm, $ss_han_jin);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����ͷ���'", $ss_han_jin, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����ͷ���ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׿ͷ���'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵ׿ͷ���')", $yyyymm, $st_han_jin);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׿ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵ׿ͷ���'", $st_han_jin, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׿ͷ���ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����δ������')", $yyyymm, $ss_han_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����δ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����δ������'", $ss_han_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����δ������ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ��δ������'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵ��δ������')", $yyyymm, $st_han_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ��δ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵ��δ������'", $st_han_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ��δ������ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
    }
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
<?= $menu->out_jsBaseClass() ?>

<script type=text/javascript language='JavaScript'>
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == ".") {
            return true;
        }
        if (("0" > c) || (c > "9")) {
            alert("���Ͱʳ������Ͻ���ޤ���");
            return false;
        }
    }
    return true;
}
function isDigitcho(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((i == 0) && (c == "-")) {
            return true;
        }
        if (("0" > c) || (c > "9")) {
            alert("���Ͱʳ������Ͻ���ޤ���");
            return false;
        }
    }
    return true;
}
/* ������ϥ�����Ȥإե������������� */
function set_focus(){
    document.invent.invent_1.focus();
    document.invent.invent_1.select();
}
function data_copy_click(obj) {
    return confirm("����Υǡ����򥳥ԡ����ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���");
}
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
.pt11 {
    font-size:11pt;
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
    font:bold 11pt;
    font-family: monospace;
}
.title_font {
    font:bold 14pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.rightb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
.rightbg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ccffcc';
}
.rightby{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffffcc';
}
.rightbb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ccffff';
}
.margin0 {
    margin:0%;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <form name='invent' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='2' border='1'>
                <th colspan='3' bgcolor='#ccffcc' width='110'>��</th><th bgcolor='#ccffcc' width='110'><?php echo $p1_ym ?></th><th bgcolor='#ccffcc' width='110'><?php echo $yyyymm ?></th>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>�ꡡ�ˡ���</td>
                    <td align='center' bgcolor='white' class='pt11b'>�Ұ���</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[0] ?>'>
                        <?php echo $invent_z[0] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[0] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�ѡ��ȿ�</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[1] ?>'>
                        <?php echo $invent_z[1] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>�ѡ��ȳ�Ψ50��</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>���Υݥ��</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>�Ұ���</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_shain ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�ѡ��ȿ�</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[2] ?>'>
                        <?php echo $invent_z[2] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[2] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>�ѡ��ȳ�Ψ50��</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>��</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>�������</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>�Ұ���</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_shain ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�ѡ��ȿ�</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[3] ?>'>
                        <?php echo $invent_z[3] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[3] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>�ѡ��ȳ�Ψ50��</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>��</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ffffcc' class='pt11b'>
                    ���Υݥ�׼Ұ���
                    <br>
                    �׻�<font color='red'>����</font>
                    </td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�к��Ĺ����</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[4] ?>'>
                        <?php echo $invent_z[4] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[5] ?>'>
                        <?php echo $invent_z[5] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[6] ?>'>
                        <?php echo $invent_z[6] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ccffff' class='pt11b'>
                    ��Ұ���
                    <br>
                    �׻�<font color='red'>����</font>
                    </td>
                    <td align='center' bgcolor='white' class='pt11b'>��ã�ݰ�</td>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[7] ?>'>
                        <?php echo $invent_z[7] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201507) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�����Ĺ����</td>
                    <?php
                    } elseif ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���ϲ�Ĺ</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ����</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�����Ĺ</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[8] ?>'>
                        <?php echo $invent_z[8] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[9] ?>'>
                        <?php echo $invent_z[9] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[9] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>��Ϳ����׻�<br>(���Υݥ��)<font color='red'>�������Ϥ��ʤ�</font></td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�к��Ĺ����</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[10] ?>'>
                        <?php echo $invent_z[10] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[10] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>��Ϳ����׻�<br>(�������)<font color='red'>�������Ϥ��ʤ�</font></td>
                    <?php
                    if ($yyyymm < 201507) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�����Ĺ����</td>
                    <?php
                    } elseif ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���ϲ�Ĺ</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ����</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�����Ĺ</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[11] ?>'>
                        <?php echo $invent_z[11] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[11] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='7' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>���Ϳ����<font color='red'>����</font><BR>��\\Fs1\��̳������\�ͻ��ط�<BR>\�������Ďʎߎ��ġ����َʎގ��ĵ�Ϳ\2019ǯ�� ź��<BR>��Ϳ�ܾ�Ϳ<BR>��2019ǯ4��5����ü�</td>
                    <td align='center' bgcolor='white' class='pt11b'>���ץ�����Ψ</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[12] ?>'>
                        <?php echo $invent_z[12] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[12] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>���ץ�������</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='c_hai_kin_z' value='<?php echo $c_hai_kin_z ?>'>
                        <?php echo $c_hai_kin_z ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='c_hai_kin' value='<?php echo $c_hai_kin ?>'>
                        <?php echo $c_hai_kin ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��˥�����Ψ</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[13] ?>'>
                        <?php echo $invent_z[13] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[13] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��˥�������</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='l_hai_kin_z' value='<?php echo $l_hai_kin_z ?>'>
                        <?php echo $l_hai_kin_z ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='l_hai_kin' value='<?php echo $l_hai_kin ?>'>
                        <?php echo $l_hai_kin ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>���������Ψ</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[14] ?>'>
                        <?php echo $invent_z[14] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[14] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>�����������</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='s_hai_kin_z' value='<?php echo $s_hai_kin_z ?>'>
                        <?php echo $s_hai_kin_z ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='s_hai_kin' value='<?php echo $s_hai_kin ?>'>
                        <?php echo $s_hai_kin ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��Ϳ�����</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[15] ?>'>
                        <?php echo $invent_z[15] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[15] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>��Ϳ����׻�<br>(�������)<font color='red'>�������Ϥ��ʤ�</font></td>
                    <td align='center' bgcolor='white' class='pt11b'>��ã�ݰ�</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[16] ?>'>
                        <?php echo $invent_z[16] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[16] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201704) {
                    ?>
                    <td rowspan='5' align='center' bgcolor='#ccffff' class='pt11b'>
                    <?php
                    } else {
                    ?>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b'>
                    <?php
                    }
                    ?>
                    ��������
                    <br>
                    �׻�<font color='red'>����</font>
                    </td>
                    <?php
                    if ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>����ë������Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>����ë��Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>����ë��Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��˼��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[17] ?>'>
                        <?php echo $invent_z[17] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[17] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>������Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��˼��Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��˼��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��˼��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[18] ?>'>
                        <?php echo $invent_z[18] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[18] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�滳��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��������Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��������Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>40%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>30%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>30%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�滳��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[19] ?>'>
                        <?php echo $invent_z[19] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[19] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>������Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�滳��Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�滳��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�滳��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[20] ?>'>
                        <?php echo $invent_z[20] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[20] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <?php
                if ($yyyymm >= 201704) {
                ?>
                <tr>
                    <?php
                    if ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�к��Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[21] ?>'>
                        <?php echo $invent_z[21] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[21] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    ��������
                    <br>
                    Ĵ��<font color='red'>����</font>
                    </td>
                    <td align='center' bgcolor='white' class='pt11b'>ñ�̡���</td>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[22] ?>'>
                        <?php echo $invent_z[22] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[22] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>��Ϳ����׻�<br>(�������)<BR><font color='red'>�ѵפ����ꢨ��</font></td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���ķ�Ĺ</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���Ĳ�Ĺ����</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�����Ĺ</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[23] ?>'>
                        <?php echo $invent_z[23] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[23] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td colspan='5' align='center'>
                        <input type='submit' name='entry' value='�¹�' >
                        &nbsp;&nbsp;&nbsp;
                        <input type='submit' name='copy' value='����ǡ������ԡ�' onClick='return data_copy_click(this)'>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        <br>
        <b>���� ������˷Ȥ��Ұ��������Ϥ��Ƥ�������</b>
        <br>
        <b>���� ��Ϳ�����Ԥ����ε�Ϳ�λٵ���ܡ��ٵ��פ����ϡʣ������ư�����</b>
        <br>
        <b>���� ��Ͽ������Ϳ����ۤ�������ϫ̳���������Ψ������</b>
        <br>
        <b>���� ��Ϳ�����Ԥ����ε�Ϳ�λٵ���ܡ��ٵ��פ����ϡʻ8����˥�2������-�����ޥ��ʥ���</b>
        <br>
        <b>���� ��Ϳ�����Ԥ����ε�Ϳ�λٵ���ܡ��ٵ��פ����ϡʳƳ��Ǽ�ư�����</b>
        <br>
        <b>���� 560����ʳ��ǵ��������ꤹ����¤���������</b>
        <br>
        <b>���� ��Ϳ�����Ԥ����ε�Ϳ�λٵ���ܡ��ٵ��פ����ϡʣ������<font color='red'>�ѵפ˼�ư����</font>��</b>
        <br>
    </center>
</body>
</html>
