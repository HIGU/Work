<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʴ�����������η׻��ǡ�������Ͽ�������ڤӾȲ����                   //
// Copyright (C) 2009-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/18 Created   profit_loss_nkb_input.php                           //
// 2009/08/19 ʪή���ʴ�����̾���ѹ�                                      //
// 2009/10/06 ���ɤαĶȳ����פ�7������ϤǤ��ʤ��褦�ˤʤäƤ�������     //
//            200909��꾦�ɤ�����ľ�����Ϥ���Ĵ�����Ϥ��ѹ�              //
// 2009/11/02 10���꾦�ɤ˴�������ε�Ϳ�����ꤹ��������ɲ�              //
//            ���ɤؤ��������$allo_nkb_kyu���ѹ����뤳��                 //
//            ���ץ顦��˥��θ���ʬ�⤳���Ƿ׻�                            //
// 2009/11/09 ���ɤؤε�Ϳ�����$allo_nkb_kyu����$allo_nkb_kyu1��2���ѹ�    //
//            09/11/09������$allo_nkb_kyu1=0.09 $allo_nkb_kyu2=0.52         //
// 2009/11/10 $allo_nkb_kyu1=0.20 $allo_nkb_kyu2=1.00���ѹ�                 //
// 2009/12/07 ������˥��ץ�ʬ���ɲá�Ĵ���ǤϤʤ���������              //
//            �����Ψ��ϫ̳�񡦷����CL�˰�ʬ                              //
// 2009/12/10 �����Ȥ�����                                                //
// 2010/01/27 ���ƤΥǡ�������Ͽ���������Ʒ׻������Ķȳ�����������    //
//            �¹Ԥ����2009/12�Ϸ׻�������»�׾��̤Ŭ�ѡ�                 //
//            ���Ĥ���»ܤ��뤫���ǧ����ifʬ���ѹ�                        //
// 2010/01/28 ��Ⱦ���������椫���оݷ�ޤǤ���������ѹ�                //
// 2010/02/01 201001��꾦�ɤαĶȳ����פ���¾�����ϤǤ��ʤ��褦�ˤ���      //
// 2014/08/06 2014/07��ꡢ���ʴ����ݤ������Ϳ�򹩾�Ĺ0.2(20%)��0.05(5%)   //
//            ������Ĺ1.0(100%)��0.5(50%)���ѹ�(����Ĺ�����������)       //
// 2014/08/07 ���ɤ������Ϳ�򸵤��ᤷ����                                  //
//            ����Ĺ0.05(5%)��0.2(20%)��������Ĺ0.5(50%)��1.0(100%)         //
// 2016/07/22 �������ѵפ���������������Ͽ                            //
// 2017/07/06 ɽ����ʬ����䤹������Ĺ�ȡ�������Ĺ���ɲ�                    //
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
$menu->set_title("��{$ki}����{$tuki}���١����ʴ������ »�פ���Ͽ");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
//�о�ǯ����
$ymd_str = $yyyymm . "01";
$ymd_end = $yyyymm . "99";

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
///// �����Υǡ�������
$res_sl = array();
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
if ((getResult($query,$res_sl)) > 0) {
    $sl_uri = $res_sl[0][0];
} else {
    $sl_uri = 0;
}

///// ���ɼҰ���Ϳ��ʬ�ѳ��
///// 1������Ĺ��2��������Ĺ
// 2014/08/07 �����ᤷ����
$allo_nkb_kyu1 = 0.20;
$allo_nkb_kyu2 = 1.00;
// 2014/07 �������ѹ�
// $allo_nkb_kyu1 = 0.05;
// $allo_nkb_kyu2 = 0.50;

///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
$item = array();
if ($yyyymm >= 200909) {
    $item[0]   = "�������Ĵ����";
    $item[1]   = "����Ĵ����";
    $item[2]   = "���ץ�����";
} else {
    $item[0]   = "��������";
    $item[1]   = "�����";
    $item[2]   = "���ץ�����";
}
$item[3]   = "���ɴ���ê����";
$item[4]   = "�����ê����";
$item[5]   = "���ɺ�����";
$item[6]   = "�������";
$item[7]   = "���ץ�������";
$item[8]   = "����ϫ̳��";
$item[9]   = "�ϫ̳��";
$item[10]  = "������¤����";
$item[11]  = "���¤����";
$item[12]  = "���ɴ���ê����";
$item[13]  = "�����ê����";
$item[14]  = "���ɿͷ���";
$item[15]  = "��ͷ���";
$item[16]  = "�����δ������";
$item[17]  = "��δ������";
$item[18]  = "���ɶ�̳��������";
$item[19]  = "���̳��������";
$item[20]  = "���ɻ������";
$item[21]  = "��������";
$item[22]  = "���ɱĶȳ����פ���¾";
$item[23]  = "��Ķȳ����פ���¾";
$item[24]  = "���ɻ�ʧ��©";
$item[25]  = "���ʧ��©";
$item[26]  = "���ɱĶȳ����Ѥ���¾";
$item[27]  = "��Ķȳ����Ѥ���¾";
$item[28]  = "���ɼҰ����۵�Ϳ��";
$item[29]  = "���ɼҰ����۵�Ϳ��";
$item[30]  = "���ɼҰ���ʬ��Ϳ";
$item[31]  = "���ץ龦�ɼҰ���ʬ��Ϳ";
$item[32]  = "��˥����ɼҰ���ʬ��Ϳ";
$item[33]  = "���ץ�ϫ̳��";
$item[34]  = "���ץ���¤����";
$item[35]  = "���ץ��ͷ���";
$item[36]  = "���ץ��δ������";
$item[37]  = "���ץ���̳��������";
$item[38]  = "���ץ��������";
$item[39]  = "���ץ��Ķȳ����פ���¾";
$item[40]  = "���ץ���ʧ��©";
$item[41]  = "���ץ��Ķȳ����Ѥ���¾";
$item[42]  = "��˥��ϫ̳��";
$item[43]  = "��˥����¤����";
$item[44]  = "��˥���ͷ���";
$item[45]  = "��˥���δ������";
$item[46]  = "��˥����̳��������";
$item[47]  = "��˥���������";
$item[48]  = "��˥���Ķȳ����פ���¾";
$item[49]  = "��˥����ʧ��©";
$item[50]  = "��˥���Ķȳ����Ѥ���¾";

///////// ����text �ѿ� �����
$invent = array();
for ($i = 0; $i < 51; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i] = $_POST['invent'][$i];
    } else {
        $invent[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ�ж�ۼ���
    for ($i = 0; $i < 51; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $invent[$i] = $res[0][0];
        }
    }
} else {    // ��Ͽ����  �ȥ�󥶥������ǹ������Ƥ��뤿��쥳����ͭ��̵���Υ����å��Τ�
    $invent[30] = Uround(($invent[28] * $allo_nkb_kyu1),0) + Uround(($invent[29] * $allo_nkb_kyu2),0);    // ���ɼҰ���ʬ��Ϳ
    $invent[31] = Uround(($invent[30] * $allo_c_kyu),0);    // ���ץ龦�ɼҰ���ʬ��Ϳ
    $invent[32] = $invent[30] - $invent[31];                // ��˥����ɼҰ���ʬ��Ϳ
    // ���ץ������γƶ�ۤλ���
    if (($sl_uri > 0) && ($invent[2] > 0)) {                // ����������ȥ��ץ齤���ζ�ۤ������
        $ss_uri   = $sl_uri + $invent[1] + $invent[2];      // ���������סʥ�˥��ܥ�˥�Ĵ���ܥ��ץ��
        $sc_allo  = Uround(($invent[2] / $ss_uri),5);       // ���ץ���������Ψ
        // ���ץ�
        $invent[33] = Uround(($invent[9] * $sc_allo),0);
        $invent[34] = Uround(($invent[11] * $sc_allo),0);
        $invent[35] = Uround(($invent[15] * $sc_allo),0);
        $invent[36] = Uround(($invent[17] * $sc_allo),0);
        $invent[37] = Uround(($invent[19] * $sc_allo),0);
        $invent[38] = Uround(($invent[21] * $sc_allo),0);
        $invent[39] = Uround(($invent[23] * $sc_allo),0);
        $invent[40] = Uround(($invent[25] * $sc_allo),0);
        $invent[41] = Uround(($invent[27] * $sc_allo),0);
        // ��˥�
        $invent[42] = $invent[9] - $invent[33];
        $invent[43] = $invent[11] - $invent[34];
        $invent[44] = $invent[15] - $invent[35];
        $invent[45] = $invent[17] - $invent[36];
        $invent[46] = $invent[19] - $invent[37];
        $invent[47] = $invent[21] - $invent[38];
        $invent[48] = $invent[23] - $invent[39];
        $invent[49] = $invent[25] - $invent[40];
        $invent[50] = $invent[27] - $invent[41];
    } else {
        $sc_allo = 0;
    }
    for ($i = 0; $i < 51; $i++) {
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
            if ($i == 2) {  // ���ץ���������ξ�硢Ψ����Ͽ
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %1.5f)", $yyyymm, $invent[$i], $item[$i], $sc_allo);
            } else {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� ���ʴ������ »�ץǡ��� ���� ��Ͽ��λ</font>",$ki,$tuki);
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
            if ($i == 2) {  // ���ץ���������ξ�硢Ψ����Ͽ
                $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='%s'", $invent[$i], $sc_allo, $yyyymm, $item[$i]);
            } else {
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� ���ʴ������ »�ץǡ��� �ѹ� ��λ</font>",$ki,$tuki);
        }
    }
    
    $p_other_ctoku = Uround((($c_pother - $invent[22]) * $ctoku_allo),0);    // ���ץ�����Ķȳ����פ���¾
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����Ķȳ����פ���¾')", $yyyymm, $p_other_ctoku);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����פ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾'", $p_other_ctoku, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����פ���¾�ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    
    // �������ѵפ�������������׻����롣
    $ss_uri = $invent[1] + $invent[2];
    $query = sprintf("select sum(Uround(����*ñ��,0)) as t_kingaku from hiuuri where �׾���>=%d and �׾���<=%d and ������='L' and (assyno like 'SS%%')", $ymd_str, $ymd_end);
    if (getUniResult($query, $st_uri) < 1) {
        $st_uri        = 0;     // ��������
    }
    $s_uri_all = $ss_uri + $st_uri;
    if ($s_uri_all <> 0) {
        $st_uri_allo     = Uround(($st_uri / $s_uri_all), 3);    // �ѵ�����Ψ
        $ss_uri_allo     = 1 - $st_uri_allo;                     // ��������Ψ
    } else {
        $st_uri_allo = 0;
        $st_uri_allo = 0;
    }
    
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '��������', %1.5f)", $yyyymm, $ss_uri, $ss_uri_allo);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�����������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='��������'", $ss_uri, $ss_uri_allo, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("��������ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ�����'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // ����Ͽ�ѤߤΥ����å�
        // ������Ͽ
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '�ѵ�����', %1.5f)", $yyyymm, $st_uri, $st_uri_allo);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�ѵ��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='�ѵ�����'", $st_uri, $st_uri_allo, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�ѵ�����ι�������<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    
    // ������Ͽ��λ��� Ψ��Ʒ׻��� �Ķȳ��Υǡ�����Ʒ׻�������
    if ($yyyymm >= 200912) {    // �ƥ����Ѥǣ�������׻���Ŭ�ѷ���ǧ�����ѹ����뤳��
        ///// �о���Ⱦ��        // ���κݡ�»�׷׻�����Ȥ߹��ळ�ȡʡ��Ʒ׻���
        $yyyy = substr($yyyymm,0,4);
        $mm   = substr($yyyymm,4,2);
        if (($mm>=4) && ($mm<=9)) {
            $z_start_yyyy = $yyyy - 1;
            $z_start_ym   = $z_start_yyyy . '10';
            $z_end_ym     = $yyyy . '03';
            $z_start_ymd  = $z_start_ym . '01';
            $z_end_ymd    = $z_end_ym . '31';
        } elseif (($mm>=10) && ($mm<=12)) {
            $z_start_ym   = $yyyy . '04';
            $z_end_ym     = $yyyy . '09';
            $z_start_ymd  = $z_start_ym . '01';
            $z_end_ymd    = $z_end_ym . '31';
        } else {
            $z_start_yyyy = $yyyy - 1;
            $z_start_ym   = $z_start_yyyy . '04';
            $z_end_ym     = $z_start_yyyy . '09';
            $z_start_ymd  = $z_start_ym . '01';
            $z_end_ymd    = $z_end_ym . '31';
        }
        // �оݷ�ޤǤ���������ѹ�
        $z_end_ym = $yyyymm;
        
        // ������μ���
        if($yyyymm >= 201004) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri) < 1) {
                $rui_b_uri        = 0;          // ��������
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri_sagaku = 0;
            }
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������Ĵ����'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri_cho) < 1) {
                // �������� Ĵ����̵���Τǲ��⤷�ʤ�
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
                $rui_b_uri_sagaku = $rui_b_uri_cho;
            }
        } else if($yyyymm >= 200909 && $yyyymm <= 201003) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri) < 1) {
                $rui_b_uri        = 0;          // ��������
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri_sagaku = 0;
            }
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������Ĵ����'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri_cho) < 1) {
                // �������� Ĵ����̵���Τǲ��⤷�ʤ�
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
                $rui_b_uri_sagaku = $rui_b_uri_cho + 25354300;      // 7��8��ʬ��Ĵ����9������줿ʬ���ᤷ
            }
        } else {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri) < 1) {
                $rui_b_uri        = 0;          // ��������
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri_sagaku = $rui_b_uri;
            }
        }
        if ( $yyyymm >= 200911) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_sc_uri) < 1) {
                $rui_sc_uri        = 0;         // ��������
                $rui_sc_uri_sagaku = 0;
                $rui_sc_uri_temp   = 0;
            } else {
                $rui_sc_uri_temp   = $rui_sc_uri;
                $rui_sc_uri_sagaku = $rui_sc_uri;
            }
        } else{
            $rui_sc_uri        = 0;             // ��������
            $rui_sc_uri_sagaku = 0;
            $rui_sc_uri_temp   = 0;
        }
        if ($yyyymm >= 200909) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_s_uri) < 1) {
                $rui_s_uri        = 0;          // ��������
                $rui_s_uri_sagaku = 0;
            } else {
                $rui_s_uri_sagaku = $rui_s_uri;
            }
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����Ĵ����'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_s_uri_cho) < 1) {
                // ��������
                $rui_s_uri = $rui_s_uri + $rui_sc_uri_sagaku;                   // ���ץ��������̣
            } else {
                $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
                $rui_s_uri        = $rui_s_uri_sagaku + $rui_sc_uri_sagaku;     // ���ץ��������̣��temp�θ�ݥ�˥�����ޥ��ʥ����Ƥ��ޤ��١�
            }
        } else {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_s_uri) < 1) {
                $rui_s_uri        = 0;          // ��������
                $rui_s_uri_sagaku = 0;
            } else {
                if ($yyyymm == 200905) {
                    $rui_s_uri = $rui_s_uri + 3100900;
                } elseif ($yyyymm == 200904) {
                    $rui_s_uri = $rui_s_uri + 1550450;
                }
                $rui_s_uri_sagaku = $rui_s_uri;
            }
        }
        $rui_sl_uri = $rui_s_uri - $rui_sc_uri;     // ��˥����Ⱦ������
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_all_uri) < 1) {
            $rui_all_uri = 0;                   // ��������
        } else {
            if ($yyyymm == 200905) {
                $rui_all_uri = $rui_all_uri + 3100900;
            } elseif ($yyyymm == 200904) {
                $rui_all_uri = $rui_all_uri + 1550450;
            }
            $rui_all_uri = $rui_all_uri + $rui_b_uri_sagaku;
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_c_uri) < 1) {
            $rui_c_uri = 0;                     // ��������
        } else {
            $rui_c_uri = $rui_c_uri - $rui_sc_uri_sagaku;                   // ���ץ��������̣
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�����'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_l_uri) < 1) {
            $rui_l_uri = 0 - $rui_s_uri_sagaku;     // ��������
        } else {
            $rui_l_uri = $rui_l_uri - $rui_s_uri_sagaku;
            if ($yyyymm == 200905) {
                $rui_l_uri = $rui_l_uri + 3100900;
            } elseif ($yyyymm == 200904) {
                $rui_l_uri = $rui_l_uri + 1550450;
            }
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���������'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_ctoku_uri) < 1) {
            $rui_ctoku_uri         = 0;     // ��������
            $rui_ctoku_uri_sagaku  = 0;
        } else {
            $rui_ctoku_uri_sagaku  = $rui_ctoku_uri;
        }
        $rui_chyou_uri = $rui_c_uri - $rui_ctoku_uri;               // ���ץ�ɸ����Ⱦ���߷�����
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х��������'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_lb_uri) < 1) {
            $rui_lb_uri        = 0;         // ��������
            $rui_lb_uri_sagaku = 0;
        } else {
            $rui_lb_uri_sagaku = $rui_lb_uri;
        }
        $rui_lh_uri = $rui_l_uri - $rui_lb_uri;                     // ��˥�ɸ����Ⱦ���߷�����
        
        // ������η׻�
        $rui_t_uri      = $rui_c_uri + $rui_l_uri + $rui_s_uri;     // ���ץ顦��˥�����������η׻�
        // ���ɤ��̣������
        $rui_t_uri      = $rui_t_uri + $rui_b_uri;                  // ���ɤ�­�������Τ������׻�
        $c_uri_allo     = Uround(($rui_c_uri / $rui_t_uri), 4);     // ���ץ�������
        $l_uri_allo     = Uround(($rui_l_uri / $rui_t_uri), 4);     // ��˥�������
        // ���ɤ��̣������
        $b_uri_allo     = Uround(($rui_b_uri / $rui_t_uri), 4);     // ����������
        //$s_uri_allo     = 1 - $c_uri_allo - $l_uri_allo;            // �������
        $s_uri_allo     = 1 - $c_uri_allo - $l_uri_allo - $b_uri_allo; // �������
        
        
        $ctoku_uri_allo = Uround(($rui_ctoku_uri / $rui_c_uri), 4); // ���ץ�����������
        $chyou_uri_allo = 1 - $ctoku_uri_allo;                      // ���ץ�ɸ��������
        $lb_uri_allo    = Uround(($rui_lb_uri / $rui_l_uri), 4);    // �Х����������
        $lh_uri_allo    = 1 - $lb_uri_allo;                         // ��˥�ɸ��������
        $sc_uri_allo    = Uround(($rui_sc_uri / $rui_s_uri), 4);    // ���ץ�������
        $sl_uri_allo    = 1 - $sc_uri_allo;                         // ��˥��������
        
        // (��Ⱦ��)�߷����ڤ����������Ͽ
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���ץ��߷�����', %1.4f)", $yyyymm, $rui_c_uri, $c_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='���ץ��߷�����'", $rui_c_uri, $c_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���ץ������߷�����', %1.4f)", $yyyymm, $rui_ctoku_uri, $ctoku_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='���ץ������߷�����'", $rui_ctoku_uri, $ctoku_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ������߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ɸ���߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���ץ�ɸ���߷�����', %1.4f)", $yyyymm, $rui_chyou_uri, $chyou_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='���ץ�ɸ���߷�����'", $rui_chyou_uri, $chyou_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�ɸ���߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '��˥��߷�����', %1.4f)", $yyyymm, $rui_l_uri, $l_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='��˥��߷�����'", $rui_l_uri, $l_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥��߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '�Х�����߷�����', %1.4f)", $yyyymm, $rui_lb_uri, $lb_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='�Х�����߷�����'", $rui_lb_uri, $lb_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�Х�����߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ɸ���߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '��˥�ɸ���߷�����', %1.4f)", $yyyymm, $rui_lh_uri, $lh_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='��˥�ɸ���߷�����'", $rui_lh_uri, $lh_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥�ɸ���߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '�����߷�����', %1.4f)", $yyyymm, $rui_b_uri, $b_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='�����߷�����'", $rui_b_uri, $b_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '��߷�����', %1.4f)", $yyyymm, $rui_s_uri, $s_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='��߷�����'", $rui_s_uri, $s_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���ץ��߷�����', %1.4f)", $yyyymm, $rui_sc_uri, $sc_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='���ץ��߷�����'", $rui_sc_uri, $sc_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ��߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '��˥���߷�����', %1.4f)", $yyyymm, $rui_sl_uri, $sl_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='��˥���߷�����'", $rui_sl_uri, $sl_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("��˥���߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����߷�����'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
            // ������Ͽ
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����߷�����')", $yyyymm, $rui_all_uri);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����߷��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='�����߷�����'", $rui_all_uri, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("�����߷�����ι�������<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // �ƱĶȳ��ζ�ۤ�׻�
        /***** ��̳���������μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ζ�̳��������'", $yyyymm);
        getUniResult($query,$res_kin);
        
        $gyoumu       = $res_kin;
        $gyoumu_c     = Uround(($gyoumu * $c_uri_allo), 0);         // ���ץ��̳��������
        $gyoumu_l     = Uround(($gyoumu * $l_uri_allo), 0);         // ��˥���̳��������
        // ���ɤ��̣������
        $gyoumu_b     = Uround(($gyoumu * $b_uri_allo), 0);         // ���ɶ�̳��������
        //$gyoumu_s     = $gyoumu - $gyoumu_c - $gyoumu_l;            // ���̳��������
        $gyoumu_s     = $gyoumu - $gyoumu_c - $gyoumu_l - $gyoumu_b;  // ���̳��������
        
        $gyoumu_ctoku = Uround(($gyoumu_c * $ctoku_uri_allo), 0);   // ���ץ������̳��������
        $gyoumu_chyou = $gyoumu_c - $gyoumu_ctoku;                  // ���ץ�ɸ���̳��������
        $gyoumu_lb    = Uround(($gyoumu_l * $lb_uri_allo), 0);      // �Х�����̳��������
        $gyoumu_lh    = $gyoumu_l - $gyoumu_lb;                     // ��˥�ɸ���̳��������
        $gyoumu_sc    = Uround(($gyoumu_s * $sc_uri_allo), 0);      // ���ץ���̳��������
        $gyoumu_sl    = $gyoumu_s - $gyoumu_sl;                     // ��˥����̳��������
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
        
        /***** ��������μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ������'", $yyyymm);
        getUniResult($query,$s_wari);
        $s_wari       = $s_wari;
        $s_wari_c     = Uround(($s_wari * $c_uri_allo), 0);         // ���ץ�������
        $s_wari_l     = Uround(($s_wari * $l_uri_allo), 0);         // ��˥��������
        // ���ɤ��̣������
        $s_wari_b     = Uround(($s_wari * $b_uri_allo), 0);         // ���ɻ������
        //$s_wari_s     = $s_wari - $s_wari_c - $s_wari_l;            // ��������
        $s_wari_s     = $s_wari - $s_wari_c - $s_wari_l - $s_wari_b;  // ��������
        
        $s_wari_ctoku = Uround(($s_wari_c * $ctoku_uri_allo), 0);   // ���ץ�����������
        $s_wari_chyou = $s_wari_c - $s_wari_ctoku;                  // ���ץ�ɸ��������
        $s_wari_lb    = Uround(($s_wari_l * $lb_uri_allo), 0);      // �Х����������
        $s_wari_lh    = $s_wari_l - $s_wari_lb;                     // ��˥�ɸ��������
        $s_wari_sc    = Uround(($s_wari_s * $sc_uri_allo), 0);      // ���ץ��������
        $s_wari_sl    = $s_wari_s - $s_wari_sl;                     // ��˥���������
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
        
        /***** �Ķȳ����פ���¾�μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����פ���¾'", $yyyymm);
        getUniResult($query,$p_other);
        $p_other       = $p_other;
        $p_other_c     = Uround(($p_other * $c_uri_allo), 0);         // ���ץ�Ķȳ����פ���¾
        $p_other_l     = Uround(($p_other * $l_uri_allo), 0);         // ��˥��Ķȳ����פ���¾
        // ���ɤ��̣������
        $p_other_b     = Uround(($p_other * $b_uri_allo), 0);         // ���ɱĶȳ����פ���¾
        //$p_other_s     = $p_other - $p_other_c - $p_other_l;            // ��Ķȳ����פ���¾
        $p_other_s     = $p_other - $p_other_c - $p_other_l - $p_other_b; // ��Ķȳ����פ���¾
        
        
        
        $p_other_ctoku = Uround(($p_other_c * $ctoku_uri_allo), 0);   // ���ץ�����Ķȳ����פ���¾
        $p_other_chyou = $p_other_c - $p_other_ctoku;                  // ���ץ�ɸ��Ķȳ����פ���¾
        $p_other_lb    = Uround(($p_other_l * $lb_uri_allo), 0);      // �Х����Ķȳ����פ���¾
        $p_other_lh    = $p_other_l - $p_other_lb;                     // ��˥�ɸ��Ķȳ����פ���¾
        $p_other_sc    = Uround(($p_other_s * $sc_uri_allo), 0);      // ���ץ��Ķȳ����פ���¾
        $p_other_sl    = $p_other_s - $p_other_sl;                     // ��˥���Ķȳ����פ���¾
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
        
        /***** �Ķȳ����׷פμ��� *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $yyyymm);
        //getUniResult($query,$nonope_p_sum);
        //$nonope_p_sum       = $nonope_p_sum;
        //$nonope_p_sum_c     = Uround(($nonope_p_sum * $c_uri_allo), 0);         // ���ץ�Ķȳ����׷�
        //$nonope_p_sum_l     = Uround(($nonope_p_sum * $l_uri_allo), 0);         // ��˥��Ķȳ����׷�
        // ���ɤ��̣������
        //$nonope_p_sum_b     = Uround(($nonope_p_sum * $b_uri_allo), 0);         // ���ɱĶȳ����׷�
        //$nonope_p_sum_s     = $nonope_p_sum - $nonope_p_sum_c - $nonope_p_sum_l;            // ��Ķȳ����׷�
        //$nonope_p_sum_s     = $nonope_p_sum - $nonope_p_sum_c - $nonope_p_sum_l - $nonope_p_sum_b;            // ��Ķȳ����׷�
        //$nonope_p_sum_ctoku = Uround(($nonope_p_sum_c * $ctoku_uri_allo), 0);   // ���ץ�����Ķȳ����׷�
        //$nonope_p_sum_chyou = $nonope_p_sum_c - $nonope_p_sum_ctoku;                  // ���ץ�ɸ��Ķȳ����׷�
        //$nonope_p_sum_lb    = Uround(($nonope_p_sum_l * $lb_uri_allo), 0);      // �Х����Ķȳ����׷�
        //$nonope_p_sum_lh    = $nonope_p_sum_l - $nonope_p_sum_lb;                     // ��˥�ɸ��Ķȳ����׷�
        //$nonope_p_sum_sc    = Uround(($nonope_p_sum_s * $sc_uri_allo), 0);      // ���ץ��Ķȳ����׷�
        //$nonope_p_sum_sl    = $nonope_p_sum_s - $nonope_p_sum_sl;                     // ��˥���Ķȳ����׷�
        
        // �Ķȳ����׷פη׻�
        $nonope_p_sum_c     = $gyoumu_c + $s_wari_c + $p_other_c;         // ���ץ�Ķȳ����׷�
        $nonope_p_sum_l     = $gyoumu_l + $s_wari_l + $p_other_l;         // ��˥��Ķȳ����׷�
        // ���ɤ��̣������
        $nonope_p_sum_b     = $gyoumu_b + $s_wari_b + $p_other_b;         // ���ɱĶȳ����׷�
        
        $nonope_p_sum_s     = $gyoumu_s + $s_wari_s + $p_other_s;         // ��Ķȳ����׷�
        $nonope_p_sum_ctoku = $gyoumu_ctoku + $s_wari_ctoku + $p_other_ctoku;   // ���ץ�����Ķȳ����׷�
        $nonope_p_sum_chyou = $gyoumu_chyou + $s_wari_chyou + $p_other_chyou;   // ���ץ�ɸ��Ķȳ����׷�
        $nonope_p_sum_lb    = $gyoumu_lb + $s_wari_lb + $p_other_lb;            // �Х����Ķȳ����׷�
        $nonope_p_sum_lh    = $gyoumu_lh + $s_wari_lh + $p_other_lh;            // ��˥�ɸ��Ķȳ����׷�
        $nonope_p_sum_sc    = $gyoumu_sc + $s_wari_sc + $p_other_sc;            // ���ץ��Ķȳ����׷�
        $nonope_p_sum_sl    = $gyoumu_sl + $s_wari_sl + $p_other_sl;            // ��˥���Ķȳ����׷�
        
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
        
        /***** ��ʧ��©�μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���λ�ʧ��©'", $yyyymm);
        getUniResult($query,$risoku);
        $risoku       = $risoku;
        $risoku_c     = Uround(($risoku * $c_uri_allo), 0);         // ���ץ��ʧ��©
        $risoku_l     = Uround(($risoku * $l_uri_allo), 0);         // ��˥���ʧ��©
        // ���ɤ��̣������
        $risoku_b     = Uround(($risoku * $b_uri_allo), 0);         // ���ɻ�ʧ��©
        //$risoku_s     = $risoku - $risoku_c - $risoku_l;            // ���ʧ��©
        $risoku_s     = $risoku - $risoku_c - $risoku_l - $risoku_b;  // ���ʧ��©
        
        
        $risoku_ctoku = Uround(($risoku_c * $ctoku_uri_allo), 0);   // ���ץ������ʧ��©
        $risoku_chyou = $risoku_c - $risoku_ctoku;                  // ���ץ�ɸ���ʧ��©
        $risoku_lb    = Uround(($risoku_l * $lb_uri_allo), 0);      // �Х�����ʧ��©
        $risoku_lh    = $risoku_l - $risoku_lb;                     // ��˥�ɸ���ʧ��©
        $risoku_sc    = Uround(($risoku_s * $sc_uri_allo), 0);      // ���ץ���ʧ��©
        $risoku_sl    = $risoku_s - $risoku_sl;                     // ��˥����ʧ��©
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
        
        /***** �Ķȳ����Ѥ���¾�μ��� *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����Ѥ���¾'", $yyyymm);
        getUniResult($query,$l_other);
        $l_other       = $l_other;
        $l_other_c     = Uround(($l_other * $c_uri_allo), 0);         // ���ץ�Ķȳ����Ѥ���¾
        $l_other_l     = Uround(($l_other * $l_uri_allo), 0);         // ��˥��Ķȳ����Ѥ���¾
        // ���ɤ��̣������
        $l_other_b     = Uround(($l_other * $b_uri_allo), 0);         // ���ɱĶȳ����Ѥ���¾
        //$l_other_s     = $l_other - $l_other_c - $l_other_l;            // ��Ķȳ����Ѥ���¾
        $l_other_s     = $l_other - $l_other_c - $l_other_l - $l_other_b; // ��Ķȳ����Ѥ���¾
        
        
        $l_other_ctoku = Uround(($l_other_c * $ctoku_uri_allo), 0);   // ���ץ�����Ķȳ����Ѥ���¾
        $l_other_chyou = $l_other_c - $l_other_ctoku;                  // ���ץ�ɸ��Ķȳ����Ѥ���¾
        $l_other_lb    = Uround(($l_other_l * $lb_uri_allo), 0);      // �Х����Ķȳ����Ѥ���¾
        $l_other_lh    = $l_other_l - $l_other_lb;                     // ��˥�ɸ��Ķȳ����Ѥ���¾
        $l_other_sc    = Uround(($l_other_s * $sc_uri_allo), 0);      // ���ץ��Ķȳ����Ѥ���¾
        $l_other_sl    = $l_other_s - $l_other_sl;                     // ��˥���Ķȳ����Ѥ���¾
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
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ɱĶȳ����Ѥ���¾�Ʒ׻�')", $yyyymm, $l_other_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ɱĶȳ����Ѥ���¾�Ʒ׻�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ɱĶȳ����Ѥ���¾�Ʒ׻�'", $l_other_s, $yyyymm);
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
        
        /***** �Ķȳ����ѷפμ��� *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $yyyymm);
        //getUniResult($query,$nonope_l_sum);
        //$nonope_l_sum       = $nonope_l_sum;
        //$nonope_l_sum_c     = Uround(($nonope_l_sum * $c_uri_allo), 0);         // ���ץ�Ķȳ����ѷ�
        //$nonope_l_sum_l     = Uround(($nonope_l_sum * $l_uri_allo), 0);         // ��˥��Ķȳ����ѷ�
        // ���ɤ��̣������
        //$nonope_l_sum_b     = Uround(($nonope_l_sum * $b_uri_allo), 0);         // ���ɱĶȳ����ѷ�
        //$nonope_l_sum_s     = $nonope_l_sum - $nonope_l_sum_c - $nonope_l_sum_l;            // ��Ķȳ����ѷ�
        //$nonope_l_sum_s     = $nonope_l_sum - $nonope_l_sum_c - $nonope_l_sum_l - $nonope_l_sum_b;            // ��Ķȳ����ѷ�
        //$nonope_l_sum_ctoku = Uround(($nonope_l_sum_c * $ctoku_uri_allo), 0);   // ���ץ�����Ķȳ����ѷ�
        //$nonope_l_sum_chyou = $nonope_l_sum_c - $nonope_l_sum_ctoku;                  // ���ץ�ɸ��Ķȳ����ѷ�
        //$nonope_l_sum_lb    = Uround(($nonope_l_sum_l * $lb_uri_allo), 0);      // �Х����Ķȳ����ѷ�
        //$nonope_l_sum_lh    = $nonope_l_sum_l - $nonope_l_sum_lb;                     // ��˥�ɸ��Ķȳ����ѷ�
        //$nonope_l_sum_sc    = Uround(($nonope_l_sum_s * $sc_uri_allo), 0);      // ���ץ��Ķȳ����ѷ�
        //$nonope_l_sum_sl    = $nonope_l_sum_s - $nonope_l_sum_sl;                     // ��˥���Ķȳ����ѷ�
        
        // �Ķȳ����ѷפη׻�
        $nonope_l_sum_c     = $risoku_c + $l_other_c;         // ���ץ�Ķȳ����ѷ�
        $nonope_l_sum_l     = $risoku_l + $l_other_l;         // ��˥��Ķȳ����ѷ�
        // ���ɤ��̣������
        $nonope_l_sum_b     = $risoku_b + $l_other_b;         // ���ɱĶȳ����ѷ�
        
        $nonope_l_sum_s     = $risoku_s + $l_other_s;         // ��Ķȳ����ѷ�
        $nonope_l_sum_ctoku = $risoku_ctoku + $l_other_ctoku; // ���ץ�����Ķȳ����ѷ�
        $nonope_l_sum_chyou = $risoku_chyou + $l_other_chyou; // ���ץ�ɸ��Ķȳ����ѷ�
        $nonope_l_sum_lb    = $risoku_lb + $l_other_lb;       // �Х����Ķȳ����ѷ�
        $nonope_l_sum_lh    = $risoku_lh + $l_other_lh;       // ��˥�ɸ��Ķȳ����ѷ�
        $nonope_l_sum_sc    = $risoku_sc + $l_other_sc;       // ���ץ��Ķȳ����ѷ�
        $nonope_l_sum_sl    = $risoku_sl + $l_other_sl;       // ��˥���Ķȳ����ѷ�
        
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
    document.invent.invent_1.focus();
    document.invent.invent_1.select();
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
                <th colspan='1' bgcolor='#ccffcc' width='110'>��</th>
                <th bgcolor='#ffffcc' width='110'>���ʴ���</th>
                <th bgcolor='#ccffff' width='110'>��˥��</th>
                <th bgcolor='#ccffff' width='110'>���ץ�</th>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    <?php if ($yyyymm >= 200909) { ?>
                    ����Ĵ����
                    <font color='red'>����</font>
                    <?php } else { ?>
                    ����
                    <?php } ?>
                    </td>
                    </td>
                    <?php if ($yyyymm >= 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[0] ?>' class='right'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[0] ?>'>
                            <?php echo $invent[0] ?>
                        </td>
                    <?php } ?>
                    <?php if ($yyyymm >= 200911) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='right'>
                        </td>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[2] ?>' class='right'>
                        </td>
                    <?php } elseif ($yyyymm >= 200909) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='right'>
                        </td>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[2] ?>'>
                            <?php echo $invent[2] ?>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[1] ?>'>
                            <?php echo $invent[1] ?>
                        </td>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[2] ?>'>
                            <?php echo $invent[2] ?>
                        </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>����ê����</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[3] ?>'>
                        <?php echo $invent[3] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[4] ?>'>
                        <!-- <?php echo $invent[4] ?> -->
                        ��
                    </td>
                    <td align='center' class='rightbb'>��</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>������</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[5] ?>'>
                        <?php echo $invent[5] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <?php if ($yyyymm >= 200911) { ?>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <?php } else { ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[7] ?>'>
                        <?php echo $invent[7] ?>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    ϫ̳��
                    <?php if ($yyyymm == 200907) { ?>
                    <font color='red'>����</font>
                    <?php } ?>
                    </td>
                    <?php if ($yyyymm == 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='right' onChange='return isDigitcho(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[8] ?>'>
                            <?php echo $invent[8] ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[9] ?>'>
                        <!-- <?php echo $invent[9] ?> -->
                        <?php echo $invent[42] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <?php echo $invent[33] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��¤����</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[10] ?>'>
                        <?php echo $invent[10] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[11] ?>'>
                        <!-- <?php echo $invent[11] ?> -->
                        <?php echo $invent[43] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[34] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>����ê����</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[12] ?>'>
                        <?php echo $invent[12] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[13] ?>'>
                        <!-- <?php echo $invent[13] ?> -->
                        ��
                    </td>
                    <td align='center' class='rightbb'>��</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    �ͷ���
                    <?php if ($yyyymm == 200907) { ?>
                    <font color='red'>����</font>
                    <?php } ?>
                    </td>
                    <?php if ($yyyymm == 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[14] ?>' class='right' onChange='return isDigitcho(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[14] ?>'>
                            <?php echo $invent[14] ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[15] ?>'>
                        <!-- <?php echo $invent[15] ?> -->
                        <?php echo $invent[44] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[35] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>�δ������</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[16] ?>'>
                        <?php echo $invent[16] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[17] ?>'>
                        <!-- <?php echo $invent[17] ?> -->
                        <?php echo $invent[45] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[36] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��̳��������</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[18] ?>'>
                        <?php echo $invent[18] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[19] ?>'>
                        <!-- <?php echo $invent[19] ?> -->
                        <?php echo $invent[46] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[37] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>�������</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[20] ?>'>
                        <?php echo $invent[20] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[21] ?>'>
                        <!-- <?php echo $invent[21] ?> -->
                        <?php echo $invent[47] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[38] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>�Ķȳ����פ���¾</td>
                    <?php if ($yyyymm >= 201001) { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[22] ?>'>
                            <?php echo $invent[22] ?>
                        </td>
                    <?php } elseif ($yyyymm >= 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[22] ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[22] ?>'>
                            <?php echo $invent[22] ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[23] ?>'>
                        <!-- <?php echo $invent[23] ?> -->
                        <?php echo $invent[48] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[39] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>��ʧ��©</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[24] ?>'>
                        <?php echo $invent[24] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[25] ?>'>
                        <!-- <?php echo $invent[25] ?> -->
                        <?php echo $invent[49] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[40] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>�Ķȳ����Ѥ���¾</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[26] ?>'>
                        <?php echo $invent[26] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[27] ?>'>
                        <!-- <?php echo $invent[27] ?> -->
                        <?php echo $invent[50] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[41] ?>
                    </td>
                </tr>
                <?php if ($yyyymm >= 200910) { ?>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>���ɼҰ���Ϳ��ʬ��(20% ����Ĺ)<font color='red'>����</font></td>
                    <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[28] ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <td align='center' class='rightbb'>��</td>
                    <td align='center' class='rightbb'>��</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>���ɼҰ���Ϳ��ʬ��(100% ������Ĺ)<font color='red'>����</font></td>
                    <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[29] ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <td align='center' class='rightbb'>��</td>
                    <td align='center' class='rightbb'>��</td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='�¹�' >
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        <?php if ($yyyymm == 200907) { ?>
            <br>
            <b>���� ���ʴ�����ϫ̳�񡦿ͷ����Ĵ����ۤ�����</b>
        <?php } ?>
        <?php if ($yyyymm >= 200911) { ?>
            <br>
            <b>���� ���ʴ�������˥���������Ĵ����ۤ�����</b>
            <br>
            <b>���ץ�����������������</b>
        <?php } elseif ($yyyymm >= 200909) { ?>
            <br>
            <b>���� ���ʴ�������������Ĵ����ۤ�����</b>
        <?php } ?>
        <?php if ($yyyymm >= 200910) { ?>
            <br><br>
            <b>���� ��Ϳ�����Ԥ����ε�Ϳ�λٵ���ܡ��ٵ��פ����ϡʤ��줾��Ρ�Ǽ�ư�����</b>
        <?php } ?>
    </center>
</body>
</html>
