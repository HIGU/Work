<?php
//////////////////////////////////////////////////////////////////////////////
// ���ҿͰ���η׻��ǡ�������Ͽ�������ڤӾȲ����                           //
// �Ͱ���ǱĶȳ�»����ʬ��Ʒ׻�����                                       //
// Copyright (C) 2010-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/02/01 Created   profit_loss_staff_input.php                         //
// 2010/03/04 201002�ٱĶȳ����פ���¾��Ĵ�����ɲá�201003�ˤ��ᤷ          //
// 2010/10/06 ����Υǡ������ԡ����ɲ�                                      //
// 2011/04/06 ���ɤαĶȳ����Ѥ���¾����Ͽ���Υߥ�������                    //
// 2012/07/07 2012ǯ6��αĶȳ����Ѥ���¾�Ϥ��٤ƥ�˥�ɸ��ΰ�             //
//            ��ư����                                                      //
// 2012/09/05 2012ǯ8��αĶȳ����פ���¾�θ������ѱפϤ��٤ƥ��ץ�ɸ�� //
//            �ΰټ�ư����                                                  //
// 2012/10/09 2012ǯ9��αĶȳ����פ���¾�θ������ѱ� ����ʬ�Ϥ��٤�    //
//            ���ץ�ɸ��ΰټ�ư����                                        //
// 2013/01/28 �Х�������Υݥ�פ��ѹ���ɽ���Τߥǡ����ϥХ����Τޤޡ�  //
// 2013/06/06 NKITͭ���ٵ���غ�»�פ����Ϥ��ɲ�                            //
// 2013/07/05 ���غ�»�פκ�»�Ⱥ��פ�ξ�����פ��ä��Τ����Ѥ���ʬ          //
// 2014/03/05 2014ǯ2��αĶȳ����פ���¾�θ������ѱפϤ��٤ƥ��ץ�ɸ�� //
//            �ΰټ�ư����                                                  //
// 2014/04/03 2014ǯ3��αĶȳ����פ���¾�λ�����(PC���ץ�ⷿ������)��     //
//            ���٤ƥ��ץ�ɸ��ΰټ�ư����                                  //
// 2014/04/04 2014ǯ4��αĶȳ����פ���¾�λ�����(PC���ץ�ⷿ��������ᤷ) //
//            �Ϥ��٤ƥ��ץ�ɸ��ΰټ�ư����                                //
// 2014/08/07 2014ǯ7��αĶȳ����פ���¾�λ�����(Ȭ���Ĥ��������ʬ)       //
//            �Ϥ��٤ƾ��ɤΰټ�ư����(1,754,636��)                         //
// 2014/09/03 2014ǯ8��αĶȳ����פ���¾�λ�����(7����ᤷʬ)              //
//            �Ϥ��٤ƾ��ɤΰټ�ư����(-1,754,636��)                        //
// 2014/10/01 �Ķȳ����פ���¾�ȱĶȳ����Ѥ���¾�����ϲ��̤������          //
//            Ĵ����Ԥ���褦�ˤ�����                                      //
// 2016/07/22 �������ѵ�»���Ѥ˱Ķȳ���׻�                                //
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
$menu->set_title("��{$ki}����{$tuki}���١����� �Ͱ�����Ͽ");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// ���ץ�Ķȳ����פ���¾�ǡ�������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_pother = $res[0][0];
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

///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
$item = array();
$item[0]    = "���ץ�Ͱ���";
$item[1]    = "���ץ�����Ͱ���";
$item[2]    = "���ץ�ɸ��Ͱ���";
$item[3]    = "��˥��Ͱ���";
$item[4]    = "�Х����Ͱ���";
$item[5]    = "��˥�ɸ��Ͱ���";
$item[6]    = "��Ͱ���";
$item[7]    = "���ץ��Ͱ���";
$item[8]    = "��˥���Ͱ���";
$item[9]    = "���ɿͰ���";

$item[10]   = "���ץ�ɸ����غ���";
$item[11]   = "���ץ�ɸ����غ�»";
$item[12]   = "���ץ�������غ���";
$item[13]   = "���ץ�������غ�»";
$item[14]   = "��˥�ɸ����غ���";
$item[15]   = "��˥�ɸ����غ�»";
$item[16]   = "���Υݥ�װ��غ���";
$item[17]   = "���Υݥ�װ��غ�»";

$item[18]   = "���ץ�ɸ��Ķȳ����פ���¾Ĵ��";
$item[19]   = "���ץ�����Ķȳ����פ���¾Ĵ��";
$item[20]   = "��˥�ɸ��Ķȳ����פ���¾Ĵ��";
$item[21]   = "���Υݥ�ױĶȳ����פ���¾Ĵ��";
$item[22]   = "��Ķȳ����פ���¾Ĵ��";
$item[23]   = "���ɱĶȳ����פ���¾Ĵ��";

$item[24]   = "���ץ�ɸ��Ķȳ����Ѥ���¾Ĵ��";
$item[25]   = "���ץ�����Ķȳ����Ѥ���¾Ĵ��";
$item[26]   = "��˥�ɸ��Ķȳ����Ѥ���¾Ĵ��";
$item[27]   = "���Υݥ�ױĶȳ����Ѥ���¾Ĵ��";
$item[28]   = "��Ķȳ����Ѥ���¾Ĵ��";
$item[29]   = "���ɱĶȳ����Ѥ���¾Ĵ��";
// ����ʬ�ǡ����μ���
for ($i = 0; $i < 30; $i++) {
    $query_b = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
    $res_b = array();
    if (getResult2($query_b,$res_b) > 0) {
        $jin_b[$i] = $res_b[0][0];
    } else {
        $jin_b[$i] = 0;
    }
}
for ($i = 0; $i < 30; $i++) {
    $query_b = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
    $res_b = array();
    if (getResult2($query_b,$res_b) > 0) {
        $allo_b[$i] = $res_b[0][0];
    } else {
        $allo_b[$i] = 0;
    }
}
///////// ����text �ѿ� �����
$jin = array();
for ($i = 0; $i < 30; $i++) {
    if (isset($_POST['jin'][$i])) {
        $jin[$i] = $_POST['jin'][$i];
    } else {
        $jin[$i] = 0;
    }
    if (isset($_POST['allo'][$i])) {
        $allo[$i] = $_POST['allo'][$i];
    } else {
        $allo[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ�пͰ�����
    for ($i = 0; $i < 30; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $jin[$i] = $res[0][0];
        }
    }
    for ($i = 0; $i < 30; $i++) {
        $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $allo[$i] = $res[0][0];
        }
    }
    
    if (isset($_POST['copy'])) {                        // ����ǡ����Υ��ԡ�
        for ($i = 0; $i < 30; $i++) {
            $jin[$i]  = $jin_b[$i];
            $allo[$i] = $allo_b[$i];
        }
    }
    
} else {    // ��Ͽ����  �ȥ�󥶥������ǹ������Ƥ��뤿��쥳����ͭ��̵���Υ����å��Τ�
    // ������Ͽ��λ��� Ψ��׻��� �Ķȳ��Υǡ�����Ʒ׻�������
    if ($yyyymm >= 200912) {    // �ƥ����Ѥǣ�������׻���Ŭ�ѷ���ǧ�����ѹ����뤳��
        // �Ͱ���η׻�
        $jin[0]  = $jin[1] + $jin[2];                        // ���ץ�Ͱ�
        $jin[3]  = $jin[4] + $jin[5];                        // ��˥��Ͱ�
        $jin[6]  = $jin[7] + $jin[8];                        // ��Ͱ�
        
        $t_jin   = $jin[0] + $jin[3] + $jin[6] + $jin[9];    // ���ץ顦��˥���������ɤι�׿Ͱ��η׻�
        $allo[0] = Uround(($jin[0] / $t_jin), 4);            // ���ץ�Ͱ���
        $allo[3] = Uround(($jin[3] / $t_jin), 4);            // ��˥��Ͱ���
        $allo[6] = Uround(($jin[6] / $t_jin), 4);            // ��Ͱ���
        $allo[9] = 1 - $allo[0] - $allo[3] - $allo[6];       // ���ɿͰ���
        
        $allo[1] = Uround(($jin[1] / $jin[0]), 4);           // ���ץ�����Ͱ���
        $allo[2] = 1 - $allo[1];                             // ���ץ�ɸ��Ͱ���
        $allo[4] = Uround(($jin[4] / $jin[3]), 4);           // �Х����Ͱ���
        $allo[5] = 1 - $allo[4];                             // ��˥�ɸ��Ͱ���
        $allo[7] = Uround(($jin[7] / $jin[6]), 4);           // ���ץ��Ͱ���
        $allo[8] = 1 - $allo[7];                             // ��˥���Ͱ���
        
        // �ƿͰ������Ͱ������Ͽ
        for ($i = 0; $i < 30; $i++) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
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
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %1.4f)", $yyyymm, $jin[$i], $item[$i], $allo[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit �ȥ�󥶥������λ
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� ���ҿͰ���ǡ��� ���� ��Ͽ��λ</font>",$ki,$tuki);
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
                $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='%s'", $jin[$i], $allo[$i], $yyyymm, $item[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit �ȥ�󥶥������λ
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� ���ҿͰ���ǡ��� �ѹ� ��λ</font>",$ki,$tuki);
            }
        }
        
        // �ƱĶȳ��ζ�ۤ�׻�
        /***** ��̳���������μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ζ�̳��������'", $yyyymm);
        getUniResult($query,$res_kin);
        
        $gyoumu       = $res_kin;
        $gyoumu_c     = Uround(($gyoumu * $allo[0]), 0);                // ���ץ��̳��������
        $gyoumu_l     = Uround(($gyoumu * $allo[3]), 0);                // ��˥���̳��������
        $gyoumu_b     = Uround(($gyoumu * $allo[9]), 0);                // ���ɶ�̳��������
        $gyoumu_s     = $gyoumu - $gyoumu_c - $gyoumu_l - $gyoumu_b;    // ���̳��������
        
        $gyoumu_ctoku = Uround(($gyoumu_c * $allo[1]), 0);              // ���ץ������̳��������
        $gyoumu_chyou = $gyoumu_c - $gyoumu_ctoku;                      // ���ץ�ɸ���̳��������
        $gyoumu_lb    = Uround(($gyoumu_l * $allo[4]), 0);              // �Х�����̳��������
        $gyoumu_lh    = $gyoumu_l - $gyoumu_lb;                         // ��˥�ɸ���̳��������
        $gyoumu_sc    = Uround(($gyoumu_s * $allo[7]), 0);              // ���ץ���̳��������
        $gyoumu_sl    = $gyoumu_s - $gyoumu_sl;                         // ��˥����̳��������
        
        // �������ѵ׷׻�
        $gyoumu_st = Uround(($gyoumu_s * $st_uri_allo), 0);
        $gyoumu_ss = $gyoumu_s - $gyoumu_st;
        
        // ��̳������������Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��̳���������Ʒ׻�')", $yyyymm, $gyoumu_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $gyoumu_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���̳���������Ʒ׻�')", $yyyymm, $gyoumu_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $gyoumu_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ���ɤ��̣������
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɶ�̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɶ�̳���������Ʒ׻�')", $yyyymm, $gyoumu_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɶ�̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɶ�̳���������Ʒ׻�'", $gyoumu_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɶ�̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���̳���������Ʒ׻�')", $yyyymm, $gyoumu_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $gyoumu_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ������̳���������Ʒ׻�')", $yyyymm, $gyoumu_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ������̳���������Ʒ׻�'", $gyoumu_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ���̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ɸ���̳���������Ʒ׻�')", $yyyymm, $gyoumu_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�ɸ���̳���������Ʒ׻�'", $gyoumu_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х�����̳���������Ʒ׻�')", $yyyymm, $gyoumu_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х�����̳���������Ʒ׻�'", $gyoumu_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ���̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ɸ���̳���������Ʒ׻�')", $yyyymm, $gyoumu_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥�ɸ���̳���������Ʒ׻�'", $gyoumu_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ���̳���������Ʒ׻�')", $yyyymm, $gyoumu_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ���̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ���̳���������Ʒ׻�'", $gyoumu_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ���̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥����̳���������Ʒ׻�')", $yyyymm, $gyoumu_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥����̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥����̳���������Ʒ׻�'", $gyoumu_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥����̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '������̳���������Ʒ׻�')", $yyyymm, $gyoumu_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='������̳���������Ʒ׻�'", $gyoumu_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׶�̳���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵ׶�̳���������Ʒ׻�')", $yyyymm, $gyoumu_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׶�̳���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵ׶�̳���������Ʒ׻�'", $gyoumu_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׶�̳���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** ��������μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ������'", $yyyymm);
        getUniResult($query,$s_wari);
        $s_wari       = $s_wari;
        $s_wari_c     = Uround(($s_wari * $allo[0]), 0);                // ���ץ�������
        $s_wari_l     = Uround(($s_wari * $allo[3]), 0);                // ��˥��������
        $s_wari_b     = Uround(($s_wari * $allo[9]), 0);                // ���ɻ������
        $s_wari_s     = $s_wari - $s_wari_c - $s_wari_l - $s_wari_b;    // ��������
        
        $s_wari_ctoku = Uround(($s_wari_c * $allo[1]), 0);              // ���ץ�����������
        $s_wari_chyou = $s_wari_c - $s_wari_ctoku;                      // ���ץ�ɸ��������
        $s_wari_lb    = Uround(($s_wari_l * $allo[4]), 0);              // �Х����������
        $s_wari_lh    = $s_wari_l - $s_wari_lb;                         // ��˥�ɸ��������
        $s_wari_sc    = Uround(($s_wari_s * $allo[7]), 0);              // ���ץ��������
        $s_wari_sl    = $s_wari_s - $s_wari_sl;                         // ��˥���������
        
        // �������ѵ׷׻�
        $s_wari_st = Uround(($s_wari_s * $st_uri_allo), 0);
        $s_wari_ss = $s_wari_s - $s_wari_st;
        
        // �����������Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��������Ʒ׻�')", $yyyymm, $s_wari_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $s_wari_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���������Ʒ׻�')", $yyyymm, $s_wari_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $s_wari_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ���ɤ��̣������
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɻ�������Ʒ׻�')", $yyyymm, $s_wari_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɻ�������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɻ�������Ʒ׻�'", $s_wari_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɻ�������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���������Ʒ׻�')", $yyyymm, $s_wari_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���������Ʒ׻�'", $s_wari_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ������������Ʒ׻�')", $yyyymm, $s_wari_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ������������Ʒ׻�'", $s_wari_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ɸ���������Ʒ׻�')", $yyyymm, $s_wari_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�ɸ���������Ʒ׻�'", $s_wari_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х�����������Ʒ׻�')", $yyyymm, $s_wari_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х�����������Ʒ׻�'", $s_wari_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ɸ���������Ʒ׻�')", $yyyymm, $s_wari_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥�ɸ���������Ʒ׻�'", $s_wari_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ���������Ʒ׻�')", $yyyymm, $s_wari_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ���������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ���������Ʒ׻�'", $s_wari_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ���������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥����������Ʒ׻�')", $yyyymm, $s_wari_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥����������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥����������Ʒ׻�'", $s_wari_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥����������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '������������Ʒ׻�')", $yyyymm, $s_wari_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='������������Ʒ׻�'", $s_wari_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�������Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵ׻�������Ʒ׻�')", $yyyymm, $s_wari_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׻�������Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵ׻�������Ʒ׻�'", $s_wari_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׻�������Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** �Ķȳ����פ���¾�μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����פ���¾'", $yyyymm);
        getUniResult($query,$p_other);
        $p_other       = $p_other;
        if ($yyyymm == 201002) {
            $p_other = $p_other + 600000;
        } elseif ($yyyymm == 201003) {
            $p_other = $p_other - 600000;
        }
        // 2012ǯ8�� �������ѱפϥ��ץ�ʰ١��������˥ޥ��ʥ�����
        if ($yyyymm == 201208) {
            $p_other = $p_other - 2808462;
        }
        // 2012ǯ9�� �������ѱ� ����ʬ�ϥ��ץ�ʰ١��������˥��ꥢ����
        if ($yyyymm == 201209) {
            $p_other = $p_other + 2808462 - 2528029;
        }
        // 2014ǯ2�� �������ѱפϥ��ץ�ɸ��ʰ١��������˥ޥ��ʥ�����
        if ($yyyymm == 201402) {
            $p_other = $p_other - 1169700;
        }
        // 2014ǯ3�� �������ϥ��ץ�ɸ��ʰ١��������˥ޥ��ʥ�����
        if ($yyyymm == 201403) {
            $p_other = $p_other - 1795407;
        }
        // 2014ǯ4�� �������ϥ��ץ�ɸ��ʰ١��������˥ץ饹����ʣ�����ᤷʬ��
        if ($yyyymm == 201404) {
            $p_other = $p_other + 1795407;
        }
        // 2014ǯ7�� Ȭ���Ļ������Ͼ��ɤʰ١��������˥ޥ��ʥ�����
        if ($yyyymm == 201407) {
            $p_other = $p_other - 1754636;
        }
        
        // 2014ǯ8�� Ȭ���Ļ��������ᤷ�ʥޥ��ʥ��ˤϾ��ɤʰ١��������˥ץ饹����
        if ($yyyymm == 201408) {
            $p_other = $p_other + 1754636;
        }
        
        // NKITͭ���ٵ���غ�»�׷׻��ΰ١��������˰���ȴ�Сʺ�»�����ѡ����פϼ��ס�
        // ����
        $p_other       = $p_other - $jin[10] - $jin[12] - $jin[14] - $jin[16];
        
        // �Ķȳ����פ�ƥ������ȤΤߤ˿���ʬ����١��������˰���ȴ���Ф�
        $p_other       = $p_other - $jin[18] - $jin[19] - $jin[20] - $jin[21] - $jin[22] - $jin[23];
        
        // �ƥ��������̤αĶȳ����פ���¾�η׻�
        $p_other_c     = Uround(($p_other * $allo[0]), 0);                  // ���ץ�Ķȳ����פ���¾
        $p_other_l     = Uround(($p_other * $allo[3]), 0);                  // ��˥��Ķȳ����פ���¾
        $p_other_b     = Uround(($p_other * $allo[9]), 0);                  // ���ɱĶȳ����פ���¾
        $p_other_s     = $p_other - $p_other_c - $p_other_l - $p_other_b;   // ��Ķȳ����פ���¾
        
        $p_other_ctoku = Uround(($p_other_c * $allo[1]), 0);                // ���ץ�����Ķȳ����פ���¾
        $p_other_chyou = $p_other_c - $p_other_ctoku;                       // ���ץ�ɸ��Ķȳ����פ���¾
        $p_other_lb    = Uround(($p_other_l * $allo[4]), 0);                // �Х����Ķȳ����פ���¾
        $p_other_lh    = $p_other_l - $p_other_lb;                          // ��˥�ɸ��Ķȳ����פ���¾
        $p_other_sc    = Uround(($p_other_s * $allo[7]), 0);                // ���ץ��Ķȳ����פ���¾
        $p_other_sl    = $p_other_s - $p_other_sl;                          // ��˥���Ķȳ����פ���¾
        
        // �ƥ������Ȥΰ��غ�»�פ��᤹�ʺ�»�����ѡ����פϼ��ס�
        $p_other_c     = $p_other_c + $jin[10] + $jin[12];                  // ���ץ�Ķȳ����פ���¾
        $p_other_l     = $p_other_l + $jin[14] + $jin[16];                  // ��˥��Ķȳ����פ���¾
        
        $p_other_ctoku = $p_other_ctoku + $jin[12];                         // ���ץ�����Ķȳ����פ���¾
        $p_other_chyou = $p_other_chyou + $jin[10];                         // ���ץ�ɸ��Ķȳ����פ���¾
        $p_other_lb    = $p_other_lb    + $jin[16];                         // �Х����Ķȳ����פ���¾
        $p_other_lh    = $p_other_lh    + $jin[14];                         // ��˥�ɸ��Ķȳ����פ���¾
        
        // �ƥ������ȤαĶȳ����פ��᤹
        $p_other_c     = $p_other_c + $jin[18] + $jin[19];                  // ���ץ�Ķȳ����פ���¾
        $p_other_l     = $p_other_l + $jin[20] + $jin[21];                  // ��˥��Ķȳ����פ���¾
        $p_other_s     = $p_other_s + $jin[22];                             // ��Ķȳ����פ���¾
        $p_other_b     = $p_other_b + $jin[23];                             // ���ɱĶȳ����פ���¾
        
        $p_other_chyou = $p_other_chyou + $jin[18];                         // ���ץ�ɸ��Ķȳ����פ���¾
        $p_other_ctoku = $p_other_ctoku + $jin[19];                         // ���ץ�����Ķȳ����פ���¾
        $p_other_lh    = $p_other_lh    + $jin[20];                         // ��˥�ɸ��Ķȳ����פ���¾
        $p_other_lb    = $p_other_lb    + $jin[21];                         // �Х����Ķȳ����פ���¾
        
        // 2012ǯ8�� �������ѱפϥ��ץ�ʰ١����ץ�ˤΤߥץ饹����
        if ($yyyymm == 201208) {
            $p_other_c     = $p_other_c + 2808462;      // ���ץ�Ķȳ����פ���¾
            $p_other_chyou = $p_other_chyou + 2808462;  // ���ץ�ɸ��Ķȳ����פ���¾
        }
        // 2012ǯ9�� �������ѱ� ����ʬ�ϥ��ץ�ʰ١����ץ�ˤΤ߷׾夹��
        if ($yyyymm == 201209) {
            $p_other_c     = $p_other_c - 2808462 + 2528029;      // ���ץ�Ķȳ����פ���¾
            $p_other_chyou = $p_other_chyou - 2808462 + 2528029;  // ���ץ�ɸ��Ķȳ����פ���¾
        }
        // 2014ǯ2�� �������ѱפϥ��ץ�ɸ��ʰ١����ץ��ɸ��˥ץ饹����
        if ($yyyymm == 201402) {
            $p_other_c     = $p_other_c + 1169700;      // ���ץ�Ķȳ����פ���¾
            $p_other_chyou = $p_other_chyou + 1169700;  // ���ץ�ɸ��Ķȳ����פ���¾
        }
        // 2014ǯ3�� �������ϥ��ץ�ɸ��ʰ١����ץ��ɸ��˥ץ饹����
        if ($yyyymm == 201403) {
            $p_other_c     = $p_other_c + 1795407;      // ���ץ�Ķȳ����פ���¾
            $p_other_chyou = $p_other_chyou + 1795407;  // ���ץ�ɸ��Ķȳ����פ���¾
        }
        // 2014ǯ4�� �������ϥ��ץ�ɸ��ʰ١����ץ��ɸ��˥ץ饹����(������ᤷʬ)
        if ($yyyymm == 201404) {
            $p_other_c     = $p_other_c - 1795407;      // ���ץ�Ķȳ����פ���¾
            $p_other_chyou = $p_other_chyou - 1795407;  // ���ץ�ɸ��Ķȳ����פ���¾
        }
        // 2014ǯ7�� Ȭ���Ļ������Ͼ��ɤʰ١����ɤ˥ץ饹����
        if ($yyyymm == 201407) {
            $p_other_b     = $p_other_b + 1754636;      // ���ɱĶȳ����פ���¾
        }
        // 2014ǯ8�� Ȭ���Ļ��������ᤷ�ʥޥ��ʥ�)�Ͼ��ɤʰ١����ɤ˥ޥ��ʥ�����
        if ($yyyymm == 201408) {
            $p_other_b     = $p_other_b - 1754636;      // ���ɱĶȳ����פ���¾
        }
        
        // �������ѵ׷׻�
        $p_other_st = Uround(($p_other_s * $st_uri_allo), 0);
        $p_other_ss = $p_other_s - $p_other_st;
        
        // �Ķȳ����פ���¾����Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $p_other_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $p_other_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ���ɤ��̣������
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɱĶȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $p_other_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $p_other_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾�Ʒ׻�'", $p_other_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ɸ��Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����פ���¾�Ʒ׻�'", $p_other_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾�Ʒ׻�'", $p_other_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ɸ��Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����פ���¾�Ʒ׻�'", $p_other_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾�Ʒ׻�'", $p_other_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥���Ķȳ����פ���¾�Ʒ׻�'", $p_other_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����Ķȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����Ķȳ����פ���¾�Ʒ׻�'", $p_other_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����פ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵױĶȳ����פ���¾�Ʒ׻�')", $yyyymm, $p_other_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����פ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵױĶȳ����פ���¾�Ʒ׻�'", $p_other_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����פ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** �Ķȳ����׷פμ��� *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $yyyymm);
        //getUniResult($query,$nonope_p_sum);
        //$nonope_p_sum       = $nonope_p_sum;
        // �Ķȳ����׷פη׻�
        $nonope_p_sum_c     = $gyoumu_c + $s_wari_c + $p_other_c;               // ���ץ�Ķȳ����׷�
        $nonope_p_sum_l     = $gyoumu_l + $s_wari_l + $p_other_l;               // ��˥��Ķȳ����׷�
        $nonope_p_sum_b     = $gyoumu_b + $s_wari_b + $p_other_b;               // ���ɱĶȳ����׷�
        $nonope_p_sum_s     = $gyoumu_s + $s_wari_s + $p_other_s;               // ��Ķȳ����׷�
        
        $nonope_p_sum_ctoku = $gyoumu_ctoku + $s_wari_ctoku + $p_other_ctoku;   // ���ץ�����Ķȳ����׷�
        $nonope_p_sum_chyou = $gyoumu_chyou + $s_wari_chyou + $p_other_chyou;   // ���ץ�ɸ��Ķȳ����׷�
        $nonope_p_sum_lb    = $gyoumu_lb + $s_wari_lb + $p_other_lb;            // �Х����Ķȳ����׷�
        $nonope_p_sum_lh    = $gyoumu_lh + $s_wari_lh + $p_other_lh;            // ��˥�ɸ��Ķȳ����׷�
        $nonope_p_sum_sc    = $gyoumu_sc + $s_wari_sc + $p_other_sc;            // ���ץ��Ķȳ����׷�
        $nonope_p_sum_sl    = $gyoumu_sl + $s_wari_sl + $p_other_sl;            // ��˥���Ķȳ����׷�
        
        // �������ѵ׷׻�
        $nonope_p_sum_ss    = $gyoumu_ss + $s_wari_ss + $p_other_ss;            // �����Ķȳ����׷�
        $nonope_p_sum_st    = $gyoumu_st + $s_wari_st + $p_other_st;            // �ѵױĶȳ����׷�
        
        // �Ķȳ����׷פ���Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ���ɤ��̣������
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɱĶȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɱĶȳ����׷׺Ʒ׻�'", $nonope_p_sum_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�����Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ɸ��Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х����Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ɸ��Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ��Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥���Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����Ķȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����Ķȳ����׷׺Ʒ׻�'", $nonope_p_sum_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����׷׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵױĶȳ����׷׺Ʒ׻�')", $yyyymm, $nonope_p_sum_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����׷׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵױĶȳ����׷׺Ʒ׻�'", $nonope_p_sum_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����׷׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** ��ʧ��©�μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ�ʧ��©'", $yyyymm);
        getUniResult($query,$risoku);
        $risoku       = $risoku;
        $risoku_c     = Uround(($risoku * $allo[0]), 0);                 // ���ץ��ʧ��©
        $risoku_l     = Uround(($risoku * $allo[3]), 0);                // ��˥���ʧ��©
        $risoku_b     = Uround(($risoku * $allo[9]), 0);                // ���ɻ�ʧ��©
        $risoku_s     = $risoku - $risoku_c - $risoku_l - $risoku_b;    // ���ʧ��©
        
        $risoku_ctoku = Uround(($risoku_c * $allo[1]), 0);          // ���ץ������ʧ��©
        $risoku_chyou = $risoku_c - $risoku_ctoku;                  // ���ץ�ɸ���ʧ��©
        $risoku_lb    = Uround(($risoku_l * $allo[4]), 0);          // �Х�����ʧ��©
        $risoku_lh    = $risoku_l - $risoku_lb;                     // ��˥�ɸ���ʧ��©
        $risoku_sc    = Uround(($risoku_s * $allo[7]), 0);          // ���ץ���ʧ��©
        $risoku_sl    = $risoku_s - $risoku_sl;                     // ��˥����ʧ��©
        
        // �������ѵ׷׻�
        $risoku_st = Uround(($risoku_s * $st_uri_allo), 0);
        $risoku_ss = $risoku_s - $risoku_st;
        
        // ��ʧ��©����Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��ʧ��©�Ʒ׻�')", $yyyymm, $risoku_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $risoku_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���ʧ��©�Ʒ׻�')", $yyyymm, $risoku_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $risoku_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ���ɤ��̣������
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɻ�ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɻ�ʧ��©�Ʒ׻�')", $yyyymm, $risoku_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɻ�ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɻ�ʧ��©�Ʒ׻�'", $risoku_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɻ�ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ʧ��©�Ʒ׻�')", $yyyymm, $risoku_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $risoku_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ������ʧ��©�Ʒ׻�')", $yyyymm, $risoku_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ������ʧ��©�Ʒ׻�'", $risoku_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ���ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ɸ���ʧ��©�Ʒ׻�')", $yyyymm, $risoku_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�ɸ���ʧ��©�Ʒ׻�'", $risoku_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х�����ʧ��©�Ʒ׻�')", $yyyymm, $risoku_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х�����ʧ��©�Ʒ׻�'", $risoku_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ���ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ɸ���ʧ��©�Ʒ׻�')", $yyyymm, $risoku_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥�ɸ���ʧ��©�Ʒ׻�'", $risoku_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ���ʧ��©�Ʒ׻�')", $yyyymm, $risoku_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ���ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ���ʧ��©�Ʒ׻�'", $risoku_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ���ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥����ʧ��©�Ʒ׻�')", $yyyymm, $risoku_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥����ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥����ʧ��©�Ʒ׻�'", $risoku_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥����ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '������ʧ��©�Ʒ׻�')", $yyyymm, $risoku_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='������ʧ��©�Ʒ׻�'", $risoku_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("������ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�ʧ��©�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵ׻�ʧ��©�Ʒ׻�')", $yyyymm, $risoku_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׻�ʧ��©�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵ׻�ʧ��©�Ʒ׻�'", $risoku_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵ׻�ʧ��©�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** �Ķȳ����Ѥ���¾�μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����Ѥ���¾'", $yyyymm);
        getUniResult($query,$l_other);
        
        
        // NKITͭ���ٵ���غ�»�׷׻��ΰ١��������˰���ȴ�Сʺ�»�����ѡ����פϼ��ס�
        $l_other       = $l_other - $jin[11] - $jin[13] - $jin[15] - $jin[17];
        
        // �Ķȳ����Ѥ�ƥ������ȤΤߤ˿���ʬ����١��������˰���ȴ���Ф�
        $l_other       = $l_other - $jin[24] - $jin[25] - $jin[26] - $jin[27] - $jin[28] - $jin[29];
        
        // �ƥ��������̤αĶȳ����פ���¾�η׻�
        $l_other_c     = Uround(($l_other * $allo[0]), 0);                  // ���ץ�Ķȳ����Ѥ���¾
        $l_other_l     = Uround(($l_other * $allo[3]), 0);                  // ��˥��Ķȳ����Ѥ���¾
        $l_other_b     = Uround(($l_other * $allo[9]), 0);                  // ���ɱĶȳ����Ѥ���¾
        $l_other_s     = $l_other - $l_other_c - $l_other_l - $l_other_b;   // ��Ķȳ����Ѥ���¾
        
        $l_other_ctoku = Uround(($l_other_c * $allo[1]), 0);                // ���ץ�����Ķȳ����Ѥ���¾
        $l_other_chyou = $l_other_c - $l_other_ctoku;                       // ���ץ�ɸ��Ķȳ����Ѥ���¾
        $l_other_lb    = Uround(($l_other_l * $allo[4]), 0);                // �Х����Ķȳ����Ѥ���¾
        $l_other_lh    = $l_other_l - $l_other_lb;                          // ��˥�ɸ��Ķȳ����Ѥ���¾
        $l_other_sc    = Uround(($l_other_s * $allo[7]), 0);                // ���ץ��Ķȳ����Ѥ���¾
        $l_other_sl    = $l_other_s - $l_other_sl;                          // ��˥���Ķȳ����Ѥ���¾
        
        // �ƥ������Ȥΰ��غ�»�פ��᤹�ʺ�»�����ѡ����פϼ��ס�
        $l_other_c     = $l_other_c + $jin[11] + $jin[13];                  // ���ץ�Ķȳ����פ���¾
        $l_other_l     = $l_other_l + $jin[15] + $jin[17];                  // ��˥��Ķȳ����פ���¾
        
        $l_other_ctoku = $l_other_ctoku + $jin[13];                         // ���ץ�����Ķȳ����פ���¾
        $l_other_chyou = $l_other_chyou + $jin[11];                         // ���ץ�ɸ��Ķȳ����פ���¾
        $l_other_lb    = $l_other_lb    + $jin[17];                         // �Х����Ķȳ����פ���¾
        $l_other_lh    = $l_other_lh    + $jin[15];                         // ��˥�ɸ��Ķȳ����פ���¾
        
        // �ƥ������ȤαĶȳ����Ѥ��᤹
        $l_other_c     = $l_other_c + $jin[24] + $jin[25];                  // ���ץ�Ķȳ����Ѥ���¾
        $l_other_l     = $l_other_l + $jin[26] + $jin[27];                  // ��˥��Ķȳ����Ѥ���¾
        $l_other_s     = $l_other_s + $jin[28];                             // ��Ķȳ����Ѥ���¾
        $l_other_b     = $l_other_b + $jin[29];                             // ���ɱĶȳ����Ѥ���¾
        
        $l_other_chyou = $l_other_chyou + $jin[24];                         // ���ץ�ɸ��Ķȳ����Ѥ���¾
        $l_other_ctoku = $l_other_ctoku + $jin[25];                         // ���ץ�����Ķȳ����Ѥ���¾
        $l_other_lh    = $l_other_lh    + $jin[26];                         // ��˥�ɸ��Ķȳ����Ѥ���¾
        $l_other_lb    = $l_other_lb    + $jin[27];                         // �Х����Ķȳ����Ѥ���¾
        
        // 2012ǯ6��Τߤ��٤ƥ�˥��ʰټ�ư����
        if ($yyyymm == 201206) {
            $l_other_c     = 0;         // ���ץ�Ķȳ����Ѥ���¾
            $l_other_l     = 238144;    // ��˥��Ķȳ����Ѥ���¾
            $l_other_b     = 0;         // ���ɱĶȳ����Ѥ���¾
            $l_other_s     = 0;         // ��Ķȳ����Ѥ���¾
            
            $l_other_ctoku = 0;         // ���ץ�����Ķȳ����Ѥ���¾
            $l_other_chyou = 0;         // ���ץ�ɸ��Ķȳ����Ѥ���¾
            $l_other_lb    = 0;         // �Х����Ķȳ����Ѥ���¾
            $l_other_lh    = 238144;    // ��˥�ɸ��Ķȳ����Ѥ���¾
            $l_other_sc    = 0;         // ���ץ��Ķȳ����Ѥ���¾
            $l_other_sl    = 0;         // ��˥���Ķȳ����Ѥ���¾
        }
        
        // �������ѵ׷׻�
        $l_other_st = Uround(($l_other_s * $st_uri_allo), 0);
        $l_other_ss = $l_other_s - $l_other_st;
        
        // �Ķȳ����Ѥ���¾����Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ���ɤ��̣������
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɱĶȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $l_other_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥���Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����Ķȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����Ķȳ����Ѥ���¾�Ʒ׻�'", $l_other_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵױĶȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵױĶȳ����Ѥ���¾�Ʒ׻�'", $l_other_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����Ѥ���¾�Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** �Ķȳ����ѷפμ��� *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $yyyymm);
        //getUniResult($query,$nonope_l_sum);
        //$nonope_l_sum       = $nonope_l_sum;
        // �Ķȳ����ѷפη׻�
        $nonope_l_sum_c     = $risoku_c + $l_other_c;         // ���ץ�Ķȳ����ѷ�
        $nonope_l_sum_l     = $risoku_l + $l_other_l;         // ��˥��Ķȳ����ѷ�
        $nonope_l_sum_b     = $risoku_b + $l_other_b;         // ���ɱĶȳ����ѷ�
        $nonope_l_sum_s     = $risoku_s + $l_other_s;         // ��Ķȳ����ѷ�
        
        $nonope_l_sum_ctoku = $risoku_ctoku + $l_other_ctoku; // ���ץ�����Ķȳ����ѷ�
        $nonope_l_sum_chyou = $risoku_chyou + $l_other_chyou; // ���ץ�ɸ��Ķȳ����ѷ�
        $nonope_l_sum_lb    = $risoku_lb + $l_other_lb;       // �Х����Ķȳ����ѷ�
        $nonope_l_sum_lh    = $risoku_lh + $l_other_lh;       // ��˥�ɸ��Ķȳ����ѷ�
        $nonope_l_sum_sc    = $risoku_sc + $l_other_sc;       // ���ץ��Ķȳ����ѷ�
        $nonope_l_sum_sl    = $risoku_sl + $l_other_sl;       // ��˥���Ķȳ����ѷ�
        
        // �������ѵ׷׻�
        $nonope_l_sum_ss    = $risoku_ss + $l_other_ss;       // �����Ķȳ����ѷ�
        $nonope_l_sum_st    = $risoku_st + $l_other_st;       // �ѵױĶȳ����ѷ�
        
        // �Ķȳ����ѷפ���Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // ���ɤ��̣������
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɱĶȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɱĶȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�����Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ɸ��Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�ɸ��Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ��Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�Х����Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ɸ��Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥�ɸ��Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ��Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ��Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥���Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����Ķȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����Ķȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����Ķȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����ѷ׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�ѵױĶȳ����ѷ׺Ʒ׻�')", $yyyymm, $nonope_l_sum_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����ѷ׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�ѵױĶȳ����ѷ׺Ʒ׻�'", $nonope_l_sum_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�ѵױĶȳ����ѷ׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        // �Ǹ�˱Ķȳ����פ��ѹ����줿�ΤǷо����פ�Ʒ׻�����
        // �ƱĶ����פμ���
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $yyyymm);
        if (getUniResult($query, $c_ope_profit) < 1) {
            $c_ope_profit = 0;                          // ��������
        }
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķ�����'", $yyyymm);
        if (getUniResult($query, $l_ope_profit) < 1) {
            $l_ope_profit = 0;                          // ��������
        }
        // �о����׺Ʒ׻�
        $c_kei = $c_ope_profit + $nonope_p_sum_c - $nonope_l_sum_c;     // ���ץ�о����׺Ʒ׻�
        $l_kei = $l_ope_profit + $nonope_p_sum_l - $nonope_l_sum_l;     // ��˥��о����׺Ʒ׻�
        
        // �Ʒ׻������о����פ���Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�о����׺Ʒ׻�')", $yyyymm, $c_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�о����׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $c_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�о����׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��о����׺Ʒ׻�')", $yyyymm, $l_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��о����׺Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $l_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��о����׺Ʒ׻��ι�������<br>�� %d�� %d��",$ki,$tuki);
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
    document.jin.jin_1.focus();
    document.jin.jin_1.select();
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
.rightbo{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffcc99';
}
.rightbg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffffcc';
}
.rightbgr{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#d6d3ce';
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
        <form name='jin' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='2' border='1'>
                <th colspan='2' rowspan='2' bgcolor='#ccffcc'>������</th>
                <th bgcolor='#d6d3ce' colspan='2'><?php echo $p1_ym ?></th>
                <th bgcolor='#ccffcc' colspan='2'><?php echo $yyyymm ?></th>
                <tr>
                    <th bgcolor='#d6d3ce'>�Ͱ�</th>
                    <th bgcolor='#d6d3ce'>�Ͱ���</th>
                    <th bgcolor='#ccffcc'>�Ͱ�</th>
                    <th bgcolor='#ccffcc'>�Ͱ���</th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    ���ץ�
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>��</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[0] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[0] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='jin[]' value='<?php echo $jin[0] ?>'>
                        <?php echo $jin[0] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[0] ?>'>
                        <?php echo $allo[0] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>��</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    ���ץ�����
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[1] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[1] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[1] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[1] ?>'>
                        <?php echo $allo[1] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>��</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    ���ץ�ɸ��
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[2] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[2] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[2] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[2] ?>'>
                        <?php echo $allo[2] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    ��˥�
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>��</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[3] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[3] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='jin[]' value='<?php echo $jin[3] ?>'>
                        <?php echo $jin[3] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[3] ?>'>
                        <?php echo $allo[3] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>��</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    ���Υݥ��
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[4] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[4] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[4] ?>'>
                        <?php echo $allo[4] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>��</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    ��˥�ɸ��
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[5] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[5] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[5] ?>'>
                        <?php echo $allo[5] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    �����
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>��</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[6] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[6] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='jin[]' value='<?php echo $jin[6] ?>'>
                        <?php echo $jin[6] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[6] ?>'>
                        <?php echo $allo[6] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>��</td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    ���ץ�
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[7] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[7] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[7] ?>'>
                        <?php echo $allo[7] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>��</td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    ��˥��
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[8] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[8] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[8] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[8] ?>'>
                        <?php echo $allo[8] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    ���ʴ���
                    </td>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[9] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[9] ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[9] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[9] ?>'>
                        <?php echo $allo[9] ?>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        NKITͭ���ٵ�
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    ���ץ�ɸ��
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' >���غ���</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[10] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>���غ�»</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[11] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    ���ץ�����
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' >���غ���</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[12] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>���غ�»</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[13] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    ��˥�ɸ��
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' >���غ���</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[14] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>���غ�»</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[15] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    ���Υݥ��
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' >���غ���</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[16] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>���غ�»</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[17] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        �Ķȳ����פ���¾Ĵ��
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>
                    ���ץ�ɸ��
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[18] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>���ץ�����</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[19] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>
                    ��˥�ɸ��
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[20] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>���Υݥ��</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[21] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>
                    �������
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[22] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>���ʴ���</td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[23] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        �Ķȳ����Ѥ���¾Ĵ��
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>
                    ���ץ�ɸ��
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[24] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>���ץ�����</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[25] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>
                    ��˥�ɸ��
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[26] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>���Υݥ��</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[27] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>
                    �������
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[28] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>���ʴ���</td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[29] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td colspan='6' align='center'>
                        <input type='submit' name='entry' value='�¹�' >
                        &nbsp;&nbsp;&nbsp;
                        <input type='submit' name='copy' value='����ǡ������ԡ�' onClick='return data_copy_click(this)'>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
    </center>
</body>
</html>
