<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ�����ɸ��οͰ���ӷ׻�ɽ����Ͽ�������ڤӾȲ����                 //
// Copyright (C) 2009-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/24 Created   profit_loss_ctoku_input.php                         //
// 2009/11/02 ����ɸ���ʬ�������˥��ץ���δ���οͷ����                //
//            ���ɤؤμҰ���ʬ��Ϳ���̣����褦�ѹ�                        //
// 2009/12/09 ����ɽ�������ޤ����äƤʤ��ä�������                        //
// 2009/12/10 �����Ȥ�����                                                //
// 2010/06/04 ��ư��ȼ��Ĵ���Ͱ�̾���ѹ�                                    //
// 2010/10/06 ����Υǡ������ԡ����ɲ�                                      //
// 2012/06/05 ����οͰ�̾���ѹ�                                            //
// 2013/04/12 ����οͰ�̾���ѹ�                                            //
// 2013/06/05 ������Ω�οͰ��˾����ݰ����ɲ�                                //
// 2014/05/07 ��ư��ȼ��Ĵ���Ͱ�̾���ѹ�                                    //
// 2014/07/01 ������Ω�οͰ�������ݰ��Ⱥ�ƣ�ݰ����ɲ�                      //
// 2015/05/08 ��ư��ȼ���Ͱ�̾���ѹ�                                        //
// 2016/03/03 ��ư��ȼ���Ͱ�̾���ѹ�                                        //
// 2016/04/21 ��ư��ȼ���Ͱ�̾���ѹ�                                        //
// 2017/05/08 ��ư��ȼ���Ͱ�̾���ѹ�                                        //
// 2017/07/06 ��ư��ȼ���Ͱ�̾���ѹ�                                        //
// 2017/11/13 ɸ�ࢪ����λ�����������ɲ�                                  //
// 2018/10/10 2018/09���������ʬ�Ϥ��٤ƥ��ץ�ɸ��ʤΤ�Ĵ��        ��ë //
// 2019/05/09 �ͻ���ư��ȼ��̾���ѹ�                                   ��ë //
// 2019/11/11 ��ݤǥޥ��ʥ�ʬ��Ĵ��                                   ��ë //
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

$current_script  = $_SERVER['PHP_SELF'];    // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SESSION['pl_referer']; // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

///// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("��{$ki}����{$tuki}���١����ץ�����ɸ�� �Ͱ���Ψ�׻�ɽ");

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

///// ���ץ����������Ψ�Υǡ�������
$res_ctoku_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='���ץ���������'", $yyyymm);
if ((getResult($query,$res_ctoku_allo)) > 0) {
    $ctoku_allo = $res_ctoku_allo[0][0];
} else {
    $_SESSION['s_sysmsg'] .= "�ã�»�׷׻����¹Ԥ���Ƥ��ޤ���<br>";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// ���ɼҰ���ʬ��Ϳ�ʥ��ץ�˥ǡ�������
$c_allo_kin = 0;
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $yyyymm);
    $res = array();
    if ((getResult($query,$res)) > 0) {
        $c_allo_kin = $res[0][0];
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("���ɤ�»����Ͽ������Ƥ��ޤ���<br>��˾��ɤؤΰ�ʬ��Ϳ�����Ϥ�ԤäƤ���������", $yyyymm);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// ���ץ��δ���οͷ���ǡ�������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ͷ���'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_jin = $res[0][0] - $c_allo_kin;
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�ã�»�׷׻����¹Ԥ���Ƥ��ޤ���<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// ���ץ��δ���η���ǡ�������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_kei = $res[0][0];
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�ã�»�׷׻����¹Ԥ���Ƥ��ޤ���<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
$item = array();
$item[0]   = "��¤�Ұ���";
$item[1]   = "��¤�ѡ��ȿ�";
$item[2]   = "������¤�ѡ��ȿ�";
$item[3]   = "��Ω�Ұ���";
$item[4]   = "��Ω�ѡ��ȿ�";
$item[5]   = "������Ω�ѡ��ȿ�";
$item[6]   = "������¤�Ұ�����";
$item[7]   = "������¤�Ұ�����";
$item[8]   = "������¤�Ұ�����";
$item[9]   = "������Ω�Ұ�����";
$item[10]  = "������Ω�Ұ�����";
$item[11]  = "������Ω�Ұ�����";
$item[12]  = "����ץ��ʧ��";
///////// ����text �ѿ� �����
$invent = array();
for ($i = 0; $i < 13; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i]   = $_POST['invent'][$i];
        $invent_z[$i] = $_POST['invent_z'][$i];
    } else {
        $invent[$i]   = 0;
        $invent_z[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ��ê����ۼ����������
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
    $sei_part50      = UROUND(($invent[1] * 0.5),2);                // ��¤�ݥѡ��ȳ�Ψ50��������
    $sei_total       = $invent[0] + $sei_part50;                    // ��¤�ݹ�ס������
    $seitoku_shain   = $invent[6] + $invent[7] + $invent[8];        // ��¤������Ұ����������
    $seitoku_part50  = UROUND(($invent[2] * 0.5),2);                // ��¤������ѡ��ȳ�Ψ50��������
    $seitoku_total   = $seitoku_shain + $seitoku_part50;            // ��¤�������ס������
    $kumi_part50     = UROUND(($invent[4] * 0.5),2);                // ��Ω�ѡ��ȳ�Ψ50��������
    $kumi_total      = $invent[3] + $kumi_part50;                   // ��Ω��ס������
    $kumitoku_shain  = $invent[9] + $invent[10] + $invent[11];      // ������Ω�Ұ����������
    $kumitoku_part50 = UROUND(($invent[5] * 0.5),2);                // ������Ω�ѡ��ȳ�Ψ50��������
    $kumitoku_total  = $kumitoku_shain + $kumitoku_part50;          // ������Ω��ס������
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
    $sei_part50_z      = UROUND(($invent_z[1] * 0.5),2);                // ��¤�ݥѡ��ȳ�Ψ50��������
    $sei_total_z       = $invent_z[0] + $sei_part50_z;                  // ��¤�ݹ�ס������
    $seitoku_shain_z   = $invent_z[6] + $invent_z[7] + $invent_z[8];    // ��¤������Ұ����������
    $seitoku_part50_z  = UROUND(($invent_z[2] * 0.5),2);                // ��¤������ѡ��ȳ�Ψ50��������
    $seitoku_total_z   = $seitoku_shain_z + $seitoku_part50_z;          // ��¤�������ס������
    $kumi_part50_z     = UROUND(($invent_z[4] * 0.5),2);                // ��Ω�ѡ��ȳ�Ψ50��������
    $kumi_total_z      = $invent_z[3] + $kumi_part50_z;                 // ��Ω��ס������
    $kumitoku_shain_z  = $invent_z[9] + $invent_z[10] + $invent_z[11];  // ������Ω�Ұ����������
    $kumitoku_part50_z = UROUND(($invent_z[5] * 0.5),2);                // ������Ω�ѡ��ȳ�Ψ50��������
    $kumitoku_total_z  = $kumitoku_shain_z + $kumitoku_part50_z;        // ������Ω��ס������
    
    if (isset($_POST['copy'])) {                        // ����ǡ����Υ��ԡ�
        $sei_part50      = $sei_part50_z;               // ��¤�ݥѡ��ȳ�Ψ50��������
        $sei_total       = $sei_total_z;                // ��¤�ݹ�ס������
        $seitoku_shain   = $seitoku_shain_z;            // ��¤������Ұ����������
        $seitoku_part50  = $seitoku_part50_z;           // ��¤������ѡ��ȳ�Ψ50��������
        $seitoku_total   = $seitoku_total_z;            // ��¤�������ס������
        $kumi_part50     = $kumi_part50_z;              // ��Ω�ѡ��ȳ�Ψ50��������
        $kumi_total      = $kumi_total_z;               // ��Ω��ס������
        $kumitoku_shain  = $kumitoku_shain_z;           // ������Ω�Ұ����������
        $kumitoku_part50 = $kumitoku_part50_z;          // ������Ω�ѡ��ȳ�Ψ50��������
        $kumitoku_total  = $kumitoku_total_z;           // ������Ω��ס������
        for ($i = 0; $i < 13; $i++) {
            $invent[$i] = $invent_z[$i];
        }
    }
    
} else {    // ��Ͽ����  �ȥ�󥶥������ǹ������Ƥ��뤿��쥳����ͭ��̵���Υ����å��Τ�
    $sei_part50      = UROUND(($invent[1] * 0.5),2);
    $sei_total       = $invent[0] + $sei_part50;
    $seitoku_shain   = $invent[6] + $invent[7] + $invent[8];
    $seitoku_part50  = UROUND(($invent[2] * 0.5),2);
    $seitoku_total   = $seitoku_shain + $seitoku_part50;
    $kumi_part50     = UROUND(($invent[4] * 0.5),2);
    $kumi_total      = $invent[3] + $kumi_part50;
    $kumitoku_shain  = $invent[9] + $invent[10] + $invent[11];
    $kumitoku_part50 = UROUND(($invent[5] * 0.5),2);
    $kumitoku_total  = $kumitoku_shain + $kumitoku_part50;
    
    $sei_part50_z      = UROUND(($invent_z[1] * 0.5),2);
    $sei_total_z       = $invent_z[0] + $sei_part50_z;
    $seitoku_shain_z   = $invent_z[6] + $invent_z[7] + $invent_z[8];
    $seitoku_part50_z  = UROUND(($invent_z[2] * 0.5),2);
    $seitoku_total_z   = $seitoku_shain_z + $seitoku_part50_z;
    $kumi_part50_z     = UROUND(($invent_z[4] * 0.5),2);
    $kumi_total_z      = $invent_z[3] + $kumi_part50_z;
    $kumitoku_shain_z  = $invent_z[9] + $invent_z[10] + $invent_z[11];
    $kumitoku_part50_z = UROUND(($invent_z[5] * 0.5),2);
    $kumitoku_total_z  = $kumitoku_shain_z + $kumitoku_part50_z;
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
            if ($i >= 12) {
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
            if ($i >= 12) {
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
    
    //////////////////////////////////////  �δ�����Ͽ
    //////////////////////////////////////  �������������
    // ���ץ������δ���ͷ������Ͽ
    if ($sei_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($kumi_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($seitoku_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($kumitoku_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($c_jin == 0) {
        $ctoku_han_jin = 0;
    } else {
        $ctoku_han_jin = Uround(($c_jin * $seitoku_total / $sei_total),0);
        $ctoku_han_jin += Uround(($c_jin * $kumitoku_total / $kumi_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ͷ���'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����ͷ���')", $yyyymm, $ctoku_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�����ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�����ͷ���'", $ctoku_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�����ͷ���ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // ���ץ������δ���������Ͽ
    if ($sei_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($kumi_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($seitoku_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($kumitoku_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($c_kei == 0) {
        $ctoku_han_kei = 0;
    } else {
        // 2018/09 ����񻺽������� �δ������� ɸ��ʤΤ�����������Ԥ�ʤ���
        if ($yyymm==201809) {
            $c_kei = $c_kei - 270803;
        }
        $ctoku_han_kei = Uround(($c_kei * $seitoku_total / $sei_total),0);
        $ctoku_han_kei += Uround(($c_kei * $kumitoku_total / $kumi_total),0);
        if ($yyymm==201809) {
            $c_kei = $c_kei + 270803;
        }
    }
    if ($yyymm==201809) {
        $ctoku_han_kei = 4957036;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������δ������'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ������δ������')", $yyyymm, $ctoku_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ������δ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ������δ������'", $ctoku_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ������δ������ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // ���ץ�����
    $query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
                from act_purchase_header
                where purchase_ym={$yyyymm} and item='���ץ�����'";
    $res = array();     // �����
    if ( getResultTrs($con, $query, $res) <= 0) {
        $paya_c_toku_kin = 0;         // ���
        $prov_c_toku_kin = 0;         // ͭ���ٵ�
    } else {
        $paya_c_toku_kin = $res[0][0];         // ���
        $prov_c_toku_kin = $res[0][1];         // ͭ���ٵ�
    }
    $c_toku_sum_kin = ($paya_c_toku_kin - $prov_c_toku_kin);
    $c_toku_sum_kin = $c_toku_sum_kin + UROUND(($invent[12] * 0.5),0);
    // ɸ�������ɲ�
    $str_ymd = $yyyymm . '01';
    $end_ymd = $yyyymm . '99';
    //////////// ����Ψǯ������
    $allo_mm  = substr($yyyymm, -2, 2);
    $allo_yy  = substr($yyyymm,  0, 4);
    $allo_mm  = $allo_mm * 1;
    if ($allo_mm > 9) {          // ����(10��12��)�ξ��
        $allo_ym  = $allo_yy . '09';
    } elseif ($allo_mm < 4)  {   // ����(1��3��)�ξ��
        $allo_ym  = $allo_yy - 1 . '09';
        $allo_ym  = $allo_ym * 1;
    } else {                    // ����ξ��
        $allo_ym  = $allo_yy . '03';
    }
    $query = "select SUM(Uround(Uround(cast(siharai * p.ctoku_allo as numeric), 0) * order_price, 0))
                from
                    (act_payable as paya left outer join vendor_master using(vendor))
                left outer join
                    order_plan as o using(sei_no)
                LEFT OUTER JOIN
                    parts_stock_master AS m ON (m.parts_no=paya.parts_no)
                LEFT OUTER JOIN
                    inventory_ctoku_par as p ON (p.parts_no=paya.parts_no and p.ctoku_ym={$allo_ym})
                LEFT OUTER JOIN
                    inventory_monthly_ctoku as t ON (p.parts_no = t.parts_no and t.invent_ym={$yyyymm})
                where act_date>={$str_ymd} and act_date<={$end_ymd} and kamoku<=5 and paya.div='C' and o.kouji_no NOT like 'SC%%'  and p.parts_no is not NULL and t.parts_no is NULL";
    $res = array();     // �����
    if ( getResultTrs($con, $query, $res) <= 0) {
        $hyo_c_toku_kin = 0;            // ɸ�ࢪ����ʬ
    } else {
        $hyo_c_toku_kin = $res[0][0];   // ɸ�ࢪ����ʬ
    }
    
    if ($yyyymm==201910) {
        $c_toku_sum_kin = $c_toku_sum_kin + $hyo_c_toku_kin - 16000000;
    } else {
        $c_toku_sum_kin = $c_toku_sum_kin + $hyo_c_toku_kin;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����������'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���ץ����������', 1.00000)", $yyyymm, $c_toku_sum_kin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ����������'", $c_toku_sum_kin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
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
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>����¤����</td>
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
                        <?php echo $sei_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $sei_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $sei_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $sei_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>������¤</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>�Ұ���</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_shain ?>
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
                        <?php echo $seitoku_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>��</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>��Ωô��</td>
                    <td align='center' bgcolor='white' class='pt11b'>�Ұ���</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[0] ?>'>
                        <?php echo $invent_z[3] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[3] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�ѡ��ȿ�</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[4] ?>'>
                        <?php echo $invent_z[4] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>�ѡ��ȳ�Ψ50��</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>������Ω</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>�Ұ���</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_shain ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�ѡ��ȿ�</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[5] ?>'>
                        <?php echo $invent_z[5] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>�ѡ��ȳ�Ψ50��</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>��</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ffffcc' class='pt11b'>������¤<br>�Ұ����׻�<font color='red'>����</font></td>
                    <?php
                    if ($yyyymm < 201706) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>������Ĺ��̾Ȫ�ܷ�Ĺ</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>�����Ų�Ĺ������̾Ȫ�ܷ�Ĺ</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[6] ?>'>
                        <?php echo $invent_z[6] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201706) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���͡���߷�������³��</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>556����Ұ�</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[7] ?>'>
                        <?php echo $invent_z[7] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' bgcolor='white' class='pt11b'>��</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[8] ?>'>
                        <?php echo $invent_z[8] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ccffff' class='pt11b'>������Ω<br>�Ұ����׻�<font color='red'>����</font></td>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>Ȭ�����ݰ�</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>Ȭ������Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[9] ?>'>
                        <?php echo $invent_z[9] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[9] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>������Ĺ����</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>������Ĺ</td>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[10] ?>'>
                        <?php echo $invent_z[10] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[10] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>���桦��ƣ������ݰ�</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>��Ĺ�ʳ�525����Ұ�</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[11] ?>'>
                        <?php echo $invent_z[11] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[11] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='3'>����ץ��ʧ��<font color='red'>����</font></td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[10] ?>'>
                        <?php echo $invent_z[12] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[12] ?>' class='right' onChange='return isDigit(value);'>
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
        <b>
        ���� AS������˥塼 26��20��20 �������<font color='red'>01298</font>
        <br>
        ����������ǯ������Ϥ��ꥹ�Ȱ���<font color='red'>��</font>�ǥꥹ�Ȥ�������ǲ���
        <br>
        �����������������������ʧͽ��ζ�ۤ����ϡ�50%�����������������
        <br>
        </b>
    </center>
</body>
</html>
