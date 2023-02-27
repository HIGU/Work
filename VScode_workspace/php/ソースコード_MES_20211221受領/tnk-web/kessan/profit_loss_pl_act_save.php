<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �ã�»�׷׻��� �׻���� ��¸                                //
// Copyright (C) 2003-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/02/14 Created   profit_loss_pl_act_save.php                         //
// 2003/02/20 ����ê����Υޥ��ʥ���Ͽ��ߤ᤿�Ȳ���˥ޥ��ʥ�ɽ��          //
// 2003/02/23 ����ê����Ĵ���ȴ���ê����Ĵ���Ȼ������Ĵ�����å��ɲ�      //
//              sprintf �� like ʸ��Ȥ���� '%����'��'%%����'�ˤ���        //
// 2003/03/06 �����ζ�̳�����������ܤ�������ˤ���¾��ȴ����Х�����        //
// 2003/03/10 �����Ĵ�������ɲäˤ��Ĵ�����å��ɲ�                    //
// 2004/01/08 ������Υ�ƥ��ο������ѹ�ͽ�� (������顩) �ʲ����ͤ�    //
// 2004/06/08 �裵������ ���ץ�=77.87% ��˥�=22.13% ���ѹ� Ⱦ����˸�ľ��  //
// 2004/11/05 �裵���������� ���ץ�=76.73% ��˥�=23.27% ���ѹ�             //
// 2005/05/30 �裶��������� ���ץ�=80.71% ��˥�=19.29% ���ѹ�             //
// 2005/11/08 �裶���������� ���ץ�=80.27% ��˥�=19.73% ���ѹ�             //
// 2005/11/11 11��η�ʹߥ�˥���ͭ�ζ�̳�����������б� $tmp_gyoumu_l    //
// 2005/12/06 C/L���꽪λ�塢���Τζ�̳���������򸵤��᤹���ɲá��嵭�ν��� //
// 2006/05/09 �裷��������� ���ץ�=78.87% ��˥�=24.63% ���ѹ�             //
// 2006/11/06 �裷���������� ���ץ�=81.27% ��˥�=18.73% ���ѹ�             //
// 2007/11/05 �裸���������� ���ץ�=82.14% ��˥�=17.86% ���ѹ�        ��ë //
// 2008/05/01 �裹��������� ���ץ�=82.42% ��˥�=17.58% ���ѹ�        ��ë //
// 2008/10/10 �裹���������� ���ץ�=83.65% ��˥�=16.35% ���ѹ�        ��ë //
// 2009/07/02 2009ǯ6��ʬ�Τ߶�̳����������Ĵ������褦���ѹ�               //
//            2009ǯ7�����˥��ζ�̳���������θ�ͭ�ͤ��̣���ʤ��褦     //
//            �ѹ�                                                     ��ë //
// 2009/07/07 �������ʥ��ڡ��������äƤ����Τǽ���                     ��ë //
// 2009/08/18 ���������������Ͽ�Υ��å����ɲ�                   ��ë //
// 2009/08/21 �����Х�����������Ͽ���ɲ�                         ��ë //
// 2009/08/26 ��������������Ψ���������Ȥ���Ψ���ѹ�             ��ë //
// 2009/10/06 200909��꾦�ɤ����⤬AS�˥��ץ�Ȥ������Ϥ���Ƥ���Τ�    //
//            ���ץ����Τ��龦�ɤ������DB����Ͽ����                   ��ë //
// 2009/12/09 �����ӥ�������Ͽ�����å���Ԥ��褦���ѹ�                    //
//            ��Ψ�׻������פʤΤǥ����ӥ��������Ϥ��ʤ��ä���            //
//            ���������̤�»�פ��������׻�����ʤ��ä�����           ��ë //
// 2009/12/10 �����Ȥ�����                                           ��ë //
// 2010/01/13 �����ӥ�������Ͽ�����å����դ��ä��Τ���             ��ë //
// 2010/01/27 �����Ψ��ư��Ⱦ�����Ȥ˷׻�����褦���ѹ�                //
//            ���ɡ��Ĵ�����ϸ�ˤ���˺Ʒ׻�������褦�ˤ���       ��ë //
// 2010/01/28 2010/01�����Ⱦ���������ư�׻�����褦���ѹ�             //
//            ���Τγ��˻���ɲ�                                   ��ë //
//            ���ɤ���������ɲá����Τγ��˲�̣�����뤫���            //
// 2010/04/12 �����׾������̤�����Τޤ޼¹Ԥ���Ƥ��ޤä��Τǥ����å�      //
//            ���ɲ�                                                   ��ë //
// 2010/10/06 ���������ʬ�������00222�ˤ�������201009��Ĵ��      ��ë //
// 2011/06/07 2011/04������������581�ɲ�                         ��ë //
// 2011/06/08 500����η��񤬻������������ۤ���Ƥ����Τ�               //
//            2011/06������ۤ��ʤ��褦���ѹ�                          ��ë //
// 2013/01/28 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
// 2013/01/31 SQLʬ�����Ĥ��ä��ΤǤ⤦��Ĥˤ��ɲ�                    ��ë //
// 2013/12/02 2013ǯ11����ꡢ�����ê���������ˡ���ѹ�           ��ë //
// 2015/05/11 ��������μ������ɲ�                                   ��ë //
// 2015/06/03 �ץ����ߥ�����                                     ��ë //
// 2015/06/10 �����η׻����ɲ�                                         ��ë //
// 2015/09/03 ��̳����������ϳ��ΰ٣��ʤΤǥץ���������ѹ�       ��ë //
//            2015/09/03�Ǹ����������ϸ����᤹��                          //
// 2016/04/21 ��¤��(582)������ؤ�������ɲá�����¾ʸ��Ĵ��          ��ë //
// 2017/10/31 ��������ݤ�Ĵã����������Ψ���ɲ�                       ��ë //
// 2017/11/08 4��5��10��11�ξ��ϣ���ȣ������Ѥ���褦�ѹ�         ��ë //
// 2018/06/29 ¿�����T���ʹ���ȴ�Ф���CC��������Ƥ��������        ��ë //
// 2018/10/05 �����Ǥλ��ˡ������Ѥ�Ĵã����������Ψ�������Ǥ��ʤ���        //
//            ľ�������Ψ����Ѥ���褦��SQL���ѹ�                    ��ë //
// 2018/10/17 �����Ȥ���                                           ��ë //
// 2020/10/14 �����ʤ��ʤ�Τ�����ط��򥳥��Ȳ�                     ��ë //
// 2020/12/21 �����ʤ��ʤ�Τ���ݤ򥳥��Ȳ�                         ��ë //
// 2021/01/20 1�����ݤ�����Τ�SQL�������ƺ���                     ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name �ϼ�ư����
$_SESSION['site_index'] = 10;               // �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
$_SESSION['site_id']    =  7;               // ���̥�˥塼̵�� (0 <=)

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
$d_start = $yyyymm . '01';   
$d_end   = $yyyymm . '31';
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о���Ⱦ��
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
///// yymm����
$ym4 = substr($yyyymm, 2, 4);

////////// ��Ͽ�ѤߤΥ����å�
$query = sprintf("SELECT pl_bs_ym FROM act_pl_history WHERE pl_bs_ym=%d", $yyyymm);
if ((getUniResult($query,$res_chk)) > 0) {
    $_SESSION["s_sysmsg"] .= sprintf("»�׷׻��ϼ¹ԺѤߤǤ�<br>�� %d�� %d��",$ki,$tuki);
    // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
////////// �����ӥ������Ͽ�ѤߤΥ����å�
$query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d and (total_item='��������' or total_item='��������' or total_item='�ó�����')", $yyyymm);
if ((getUniResult($query,$res_chk)) > 0) {
} else {
    $_SESSION["s_sysmsg"] .= sprintf("��˥����ӥ�������Ͽ��ԤäƤ���������");
    // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
////////// �����׾�����ѤΥ����å�
$query = sprintf("SELECT sum_payable, sum_provide, cnt_payable, cnt_provide FROM act_purchase_header WHERE purchase_ym=%d AND item='�Х����'", $yyyymm);
if ((getUniResult($query,$res_chk)) > 0) {
} else {
    $_SESSION["s_sysmsg"] .= sprintf("��˷�����˥塼�λ����׾������ԤäƤ���������");
    // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
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
/***** ��    ��    �� *****/
$res = array();
$query = sprintf("SELECT ����, ���ץ�, ��˥� FROM wrk_uriage WHERE ǯ��=%d", $yyyymm);
if ((getResult($query,$res)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    $uri   = $res[0]['����'];
    $uri_c = $res[0]['���ץ�'];
    $uri_l = $res[0]['��˥�'];
        ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym=%d AND note LIKE '%%����Ĵ��'", $yyyymm); // ����
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri = ($uri + ($res_adjust));      // �ޥ��ʥ��ξ����θ����()��Ȥ�
    }
    $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note) VALUES(%d, %d, '%s')", $yyyymm, $uri, "��������");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION["s_sysmsg"] .= sprintf("�����������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
        ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym=%d AND note='���ץ�����Ĵ��'", $yyyymm); // ���ץ�
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_c = ($uri_c + ($res_adjust));      // �ޥ��ʥ��ξ����θ����()��Ȥ�
    }
    
    // ���ʴ�����̳�������Ͽ��2009/09���饫�ץ�˾��ɤ����⤬���äƤ����
    if ($yyyymm >= 200909) {
        $query = "SELECT
                            COUNT(����) AS t_ken,
                            SUM(����) AS t_kazu,
                            SUM(Uround(����*ñ��,0)) AS t_kingaku
                        FROM
                            hiuuri";
        //////////// SQL where ��� ���Ѥ���
        $search = "WHERE �׾���>=$d_start AND �׾���<=$d_end";
        $search .= " AND (assyno LIKE 'NKB%%')";
        $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
        $_SESSION['sales_search'] = $search;        // SQL��where�����¸
        $res_sum = array();
        if (getResult($query, $res_sum) <= 0) {
            $t_kingaku = 0;
        } else {
            $t_kingaku = $res_sum[0]['t_kingaku'];
        }
        
        $uri_c = $uri_c - $t_kingaku;               // ���ץ��������˾������⤬���äƤ���٥ޥ��ʥ�
        
        $res_chk = array();
        $query_chk = sprintf("SELECT kin FROM act_pl_history WHERE pl_bs_ym=%d AND note='��������'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
                                // ������Ͽ
            $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note) VALUES (%d, %d, '��������')", $yyyymm, $t_kingaku);
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ���";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {                // UPDATE
            $query = sprintf("UPDATE act_pl_history SET kin=%d WHERE pl_bs_ym=%d AND note='��������'", $t_kingaku, $yyyymm);
            if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
                $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
    }
        
    $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note, allo) VALUES(%d, %d, '%s', 1.00000)", $yyyymm, $uri_c, "���ץ�����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION["s_sysmsg"] .= sprintf("���ץ��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
        ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym=%d AND note='��˥�����Ĵ��'", $yyyymm); // ��˥�
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_l = ($uri_l + ($res_adjust));      // �ޥ��ʥ��ξ����θ����()��Ȥ�
    }
    $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note, allo) VALUES(%d, %d, '%s', 1.00000)", $yyyymm, $uri_l, "��˥�����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION["s_sysmsg"] .= sprintf("��˥��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // �ȥ�󥶥������ rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("������оݥǡ���������ޤ���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
// ��Ⱦ������
$res = array();
$query = sprintf("SELECT SUM(����), SUM(���ץ�), SUM(��˥�) FROM wrk_uriage WHERE ǯ��>=%d AND ǯ��<=%d", $z_start_ym, $z_end_ym);
if ((getResult($query,$res)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    $uri_total   = $res[0][0];
    $uri_c_total = $res[0][1];
    $uri_l_total = $res[0][2];
        ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note LIKE '%%����Ĵ��'", $z_start_ym, $z_end_ym); // ����
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_total = ($uri_total + ($res_adjust));          // �ޥ��ʥ��ξ����θ����()��Ȥ�
    }
        ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note='���ץ�����Ĵ��'", $z_start_ym, $z_end_ym); // ���ץ�
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_c_total = ($uri_c_total + ($res_adjust));      // �ޥ��ʥ��ξ����θ����()��Ȥ�
    }
        ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note='��˥�����Ĵ��'", $z_start_ym, $z_end_ym); // ��˥�
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_l_total = ($uri_l_total + ($res_adjust));      // �ޥ��ʥ��ξ����θ����()��Ȥ�
    }
}

// ��Ⱦ������꾦�ɤ��������׻�
$query = sprintf("SELECT SUM(kin) FROM act_pl_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note='��������'", $z_start_ym, $z_end_ym);
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $b_total_kingaku = 0;
    $b_allo       = 0.00000;
} else {
    $b_total_kingaku = $res_sum[0][0];
    $b_allo       = Uround(($b_total_kingaku / $uri_total),4);    // ���ɤ�������Ψ(��������
}
$res_chk = array();
$query = sprintf("UPDATE act_pl_history SET allo=%1.4f WHERE pl_bs_ym=%d AND note='��������'", $b_allo, $yyyymm);
if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
    $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
    query_affected_trans($con, "rollback");         // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// ���������̳�������Ͽ
$query = "SELECT
                    COUNT(����) AS t_ken,
                    SUM(����) AS t_kazu,
                    SUM(Uround(����*ñ��,0)) AS t_kingaku
              FROM
                    hiuuri";
//////////// SQL where ��� ���Ѥ���
$search = "WHERE �׾���>=$d_start AND �׾���<=$d_end";
$search .= " AND (assyno LIKE 'SS%%')";
$query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$_SESSION['sales_search'] = $search;        // SQL��where�����¸
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $t_kingaku = 0;
} else {
    $t_kingaku = $res_sum[0]['t_kingaku'];
}

if (($yyyymm >= 200904) && ($yyyymm <= 200909)) {       // ����ϸ����ͤ�0.00900
    $ss_allo = 0.00900;
} else {
    $query = "SELECT
                    COUNT(����) AS t_ken,
                    SUM(����) AS t_kazu,
                    SUM(UROUND(����*ñ��,0)) AS t_kingaku
              FROM
                    hiuuri";
    //////////// SQL where ��� ���Ѥ���
    $search = "WHERE �׾���>=$z_start_ymd AND �׾���<=$z_end_ymd";
    $search .= " AND (assyno LIKE 'SS%%')";
    $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $total_kingaku = 0;
        $ss_allo       = 0.00000;
    } else {
        $total_kingaku = $res_sum[0]['t_kingaku'];
        $ss_allo       = Uround(($total_kingaku / $uri_total),4);    // ���������̳��������Ψ(��������
    }
}
// ��˥�����׻��ʵ����γ��׻��˻��ѡ�
$uri_l_last= $uri_l - $t_kingaku;

$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
                        // ������Ͽ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '�����', %1.5f)", $yyyymm, $t_kingaku, $ss_allo);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ���";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='�����'", $t_kingaku, $ss_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
// �Х����������Ͽ
$query = "select
                    count(����) as t_ken,
                    sum(����) as t_kazu,
                    sum(Uround(����*ñ��,0)) as t_kingaku
              from
                    hiuuri
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
//////////// SQL where ��� ���Ѥ���
$search = "where �׾���>=$d_start and �׾���<=$d_end";
$search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
$query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$_SESSION['sales_search'] = $search;        // SQL��where�����¸
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $t_kingaku = 0;
} else {
    $t_kingaku = $res_sum[0]['t_kingaku'];
}
$query = "select
                    count(����) as t_ken,
                    sum(����) as t_kazu,
                    sum(Uround(����*ñ��,0)) as t_kingaku
              from
                    hiuuri
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
//////////// SQL where ��� ���Ѥ���
$search = "where �׾���>=$z_start_ymd and �׾���<=$z_end_ymd";
//$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
$search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
$query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$_SESSION['sales_search'] = $search;        // SQL��where�����¸
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $total_kingaku = 0;
    $bimor_allo    = 0.00000;
} else {
    $total_kingaku = $res_sum[0]['t_kingaku'];
    $bimor_allo    = Uround(($total_kingaku / $uri_l_total),5);    // �Х�����������Ψ
}

$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х��������'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
                        // ������Ͽ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '�Х��������', %1.5f)", $yyyymm, $t_kingaku, $bimor_allo);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ���";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='�Х��������'", $t_kingaku, $bimor_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

// �����������Ͽ
$query = "select
                    count(����) as t_ken,
                    sum(����) as t_kazu,
                    sum(Uround(����*ñ��,0)) as t_kingaku
              from
                    hiuuri";
//////////// SQL where ��� ���Ѥ���
$search = "where �׾���>=$d_start and �׾���<=$d_end";
//$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
$search .= " and ������='T'";
$query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$_SESSION['sales_search'] = $search;        // SQL��where�����¸
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $total_kingaku = 0;
    $tool_allo    = 0.00000;
} else {
    $total_kingaku = $res_sum[0]['t_kingaku'];
    $tool_allo    = Uround(($total_kingaku / $uri_l_last),5);    // ������������Ψ
}

$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  // ����Ͽ�ѤߤΥ����å�
                        // ������Ͽ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '��������', %1.5f)", $yyyymm, $total_kingaku, $tool_allo);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ���";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='��������'", $total_kingaku, $tool_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
// ���ץ�����������Ͽ
$query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no";
//////////// SQL where ��� ���Ѥ���
$search = "where �׾���>=$d_start and �׾���<=$d_end";
$search .= " and ������='C' and note15 like 'SC%%'";
$query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$_SESSION['sales_search'] = $search;        // SQL��where�����¸
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $t_kingaku = 0;
} else {
    $t_kingaku = $res_sum[0]['t_kingaku'];
    if ($yyyymm == 201009) {
        $t_kingaku = $t_kingaku + 6249616;
    }
}
$query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no";
//////////// SQL where ��� ���Ѥ���
$search = "where �׾���>=$z_start_ymd and �׾���<=$z_end_ymd";
$search .= " and ������='C' and note15 like 'SC%%'";
$query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$_SESSION['sales_search'] = $search;        // SQL��where�����¸
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $total_kingaku = 0;
    $ctoku_allo   = 0.00000;
} else {
    $total_kingaku = $res_sum[0]['t_kingaku'];
    $ctoku_allo    = Uround(($total_kingaku / $uri_c_total),5);    // ���ץ������������Ψ
}
$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
                        // ������Ͽ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���ץ���������', %1.5f)", $yyyymm, $t_kingaku, $ctoku_allo);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ���";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='���ץ���������'", $t_kingaku, $ctoku_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

/***** ��������ų���ê���� *****/
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        ///// �о�����δ���ê������оݷ�δ���ê�������Ͽ
    $res_kin = ($res_kin * (1));               // ���ȿž��᤿ »�׷׻����ǥޥ��ʥ�ɽ��������
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "���δ���ê����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���Τδ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $invent = $res_kin;     // ���δ���ê����
} else {        // act_pl_history(»�׷׻������)�����ê���⤬�ʤ����act_invent_history(ê������)�������
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d", $p1_ym); // ����
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
             ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
        $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note like '%%����ê����Ĵ��'", $p1_ym); // ����
        getUniResult($query, $res_adjust);
        if ($res_adjust != 0) {
            $res_kin = ($res_kin + ($res_adjust));
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "���δ���ê����");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���Τδ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $invent = $res_kin;     // ���δ���ê����
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("���Τδ���ê������оݥǡ���������ޤ���<br>����ǯ�� %d", $p1_ym);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        ///// �о�����δ���ê������оݷ�δ���ê�������Ͽ
    $res_kin = ($res_kin * (1));               // ���ȿž��᤿ »�׷׻����ǥޥ��ʥ�ɽ��������
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "���ץ����ê����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ����ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $invent_c = $res_kin;     // ���ץ����ê����
} else {        // act_pl_history(»�׷׻������)�����ê���⤬�ʤ����act_invent_history(ê������)�������
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='���ץ�'", $p1_ym);
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
             ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
        $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='���ץ����ê����Ĵ��'", $p1_ym); // ���ץ�
        getUniResult($query, $res_adjust);
        if ($res_adjust != 0) {
            $res_kin = ($res_kin + ($res_adjust));
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "���ץ����ê����");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $invent_c = $res_kin;     // ���ץ����ê����
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�δ���ê������оݥǡ���������ޤ���<br>����ǯ�� %d", $p1_ym);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
// ���ץ�������󡦴���ê����
// 2013/11�ʹߤ������ê����μ�����ˡ���ѹ��ʼ�ư���ϡ�
if ($yyyymm >= 201311) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $p1_ym);
    if ((getUniResult($query,$res_kin)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        ///// �о�����δ���ê������оݷ�δ���ê�������Ͽ
        $res_kin = ($res_kin * (1));               // ���ȿž��᤿ »�׷׻����ǥޥ��ʥ�ɽ��������
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "���ץ��������ê����");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ��������ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {    // act_pl_history(»�׷׻������)�����ê���⤬�ʤ����act_invent_history(ê������)�������
        $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='������'", $p1_ym);
        getUniResult($query,$res_kin);
        if ($res_kin != 0) {
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "���ץ��������ê����");
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("���ץ�����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("���ץ�����δ���ê������оݥǡ���������ޤ���<br>����ǯ�� %d", $p1_ym);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    ///// act_invent_history ������ê���� ����
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='������'", $yyyymm);
    if (getUniResult($query,$ctoku_kin) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ��������ê������оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��������ê����')", $yyyymm, $ctoku_kin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ��������ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $search = "where invent_ym={$p1_ym} and item='���ץ�����'";
    //////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
    $query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
    $res_sum = array();         // �����
    if ( getResult($query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");      // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "���ץ��������ê����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");      // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $search = "where invent_ym={$yyyymm} and item='���ץ�����'";
    //////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
    $query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
    $res_sum = array();         // �����
    if ( getResult($query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "���ץ��������ê����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        ///// �о�����δ���ê������оݷ�δ���ê�������Ͽ
    $res_kin = ($res_kin * (1));               // ���ȿž��᤿ »�׷׻����ǥޥ��ʥ�ɽ��������
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "��˥�����ê����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥�����ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $invent_l = $res_kin;     // ��˥�����ê����
} else {        // act_pl_history(»�׷׻������)�����ê���⤬�ʤ����act_invent_history(ê������)�������
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='��˥�'", $p1_ym);
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
             ///// act_invent_history �ξ���Ĵ���⤢��Τǥ����å� act_adjust_history ��ߤ�
        $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='��˥�����ê����Ĵ��'", $p1_ym); // ��˥�
        getUniResult($query, $res_adjust);
        if ($res_adjust != 0) {
            $res_kin = ($res_kin + ($res_adjust));
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "��˥�����ê����");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("��˥��δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $invent_l = $res_kin;     // ��˥�����ê����
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("��˥��δ���ê������оݥǡ���������ޤ���<br>����ǯ�� %d", $p1_ym);
        query_affected_trans($con, "rollback");         // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

// �Х������󡦴���ê����
$search = "where invent_ym={$p1_ym} and item='�Х����'";
//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResult($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= sprintf("�Х����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");      // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "�Х�������ê����");
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$search = "where invent_ym={$yyyymm} and item='�Х����'";
//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResult($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= sprintf("�Х����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");      // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "�Х�������ê����");
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// �������󡦴���ê����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������ê����'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    ///// �о�����δ���ê������оݷ�δ���ê�������Ͽ
    $res_kin = ($res_kin * (1));               // ���ȿž��᤿ »�׷׻����ǥޥ��ʥ�ɽ��������
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "��������ê����");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("��������ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {    // act_pl_history(»�׷׻������)�����ê���⤬�ʤ����act_invent_history(ê������)�������
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='�ġ�����'", $p1_ym);
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "��������ê����");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("�����δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("�����δ���ê������оݥǡ���������ޤ���<br>����ǯ�� %d", $p1_ym);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// act_invent_history ������ê���� ����
$query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='�ġ�����'", $yyyymm);
if (getUniResult($query,$ctoku_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��������ê������оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��������ê����')", $yyyymm, $ctoku_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��������ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ������(������) *****/
    ///// ���������ɽ�η׻�(������ǣã���Ψ�����)
$query = sprintf("select kin1 from pl_bs_summary where t_id='E' and pl_bs_ym=%d order by t_row ASC", $yyyymm);
$res = array();
if ((getResult($query, $res)) > 0) {
    $shiire_c = ($res[0][0] - $res[2][0]);      // ��ݶ⣱���� �� ͭ���ٵ�̤�����⣱���� ���ץ�
    $shiire_l = ($res[1][0] - $res[3][0]);      // ��ݶ⣱���� �� ͭ���ٵ�̤�����⣱���� ��˥�
    ///// Ĵ����ɬ�פʾ���Ĵ������
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='���ץ������Ĵ��'", $yyyymm);
    if ((getUniResult($query, $adjust_c)) > 0) {
        $shiire_c = ($shiire_c + ($adjust_c));
    }
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='��˥�������Ĵ��'", $yyyymm);
    if ((getUniResult($query, $adjust_l)) > 0) {
        $shiire_l = ($shiire_l + ($adjust_l));
    }
    $shiire   = ($shiire_c + $shiire_l);            // ����
    $c_ritu   = Uround(($shiire_c / $shiire),5);    // ���ץ�κ�����Ψ
    $l_ritu   = Uround(($shiire_l / $shiire),5);    // ��˥��κ�����Ψ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���λ�����', 1.00000)", $yyyymm, $shiire);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���λ��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '���ץ������', %1.5f)", $yyyymm, $shiire_c, $c_ritu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '��˥�������', %1.5f)", $yyyymm, $shiire_l, $l_ritu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥����������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("���������ɽ���оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// ���ץ�����񡦥�˥��������
    ///// ��帶���κ����񤫤�ã̺������׻�(������ã���Ψ) �ʲ�����帶���ǽ�������
    ///// ���ץ�����κ�����

// �Х����
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$yyyymm} and item='�Х����'";
$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $paya_l_bimor_kin = $res[0][0];         // ���
    $prov_l_bimor_kin = $res[0][1];         // ͭ���ٵ�
} else {
    $paya_l_bimor_kin = $res[0][0];         // ���
    $prov_l_bimor_kin = $res[0][1];         // ͭ���ٵ�
}
$l_bimor_sum_kin = ($paya_l_bimor_kin - $prov_l_bimor_kin);
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '�Х���������', 1.00000)", $yyyymm, $l_bimor_sum_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// ����
$str_ymd = $yyyymm . '01';
$end_ymd = $yyyymm . '99';
$query = "select sum(Uround(order_price * siharai,0)) from act_payable as a 
            LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) 
            where act_date>={$str_ymd} and act_date<={$end_ymd} and div='T'";
            
$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $tool_kin = 0;                  // ��� ������T
} else {
    $tool_kin = $res[0][0];         // ��� ������T
}
/* �߸ˤ��ʤ��ʤ�Τ���ݤ�CL�� */

$query = "select sum(Uround(order_price * siharai,0)) from act_payable as a 
            LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) 
            LEFT OUTER JOIN miitem ON (mipn = a.parts_no) 
            where act_date>={$str_ymd} and act_date<={$end_ymd} and ((a.div<>'T' and a.div<>'C' and a.parts_no like 'T%' and ( mepnt like 'ADR%%' or mepnt like 'L-25%%' )))";
            

$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $tool_kin_o = 0;                  // ��� ������T�ʳ���Ƭ��T�Τ��
} else {
    $tool_kin_o = $res[0][0];         // ��� ������T�ʳ���Ƭ��T�Τ��
}

$tool_kin = $tool_kin + $tool_kin_o;

if($yyyymm==202006) {
    $tool_kin = $tool_kin - 600000;
}

///// ����Ψ������ ǯ����
$yyyy_hai = substr($yyyymm, 0,4);
$mm_hai   = substr($yyyymm, 4,2);

if($mm_hai == 4) {
    $hai_ym = $yyyy_hai . '03';
} elseif($mm_hai == 5) {
    $hai_ym = $yyyy_hai . '03';
} elseif($mm_hai == 10) {
    $hai_ym = $yyyy_hai . '09';
} elseif($mm_hai == 11) {
    $hai_ym = $yyyy_hai . '09';
} else {
    $hai_ym = $yyyymm;
}
/* �����ʤ��ʤ�Τ�����ʤ�
$query_a = sprintf("SELECT * FROM indirect_cost_allocate WHERE total_date<=%d and item='��˥�' ORDER BY total_date DESC limit 1", $hai_ym);
$res_a = array();
$rows_a = getResult($query_a, $res_a);
if ($res_a[0]['suppli_section_cost'] == '') {
    $allo_suppli = 0;
} else {
    $allo_suppli     = $res_a[0]['suppli_section_cost'] / 100;
    $tool_kin_suppli = round($tool_kin * $allo_suppli);
    $tool_kin        = $tool_kin + $tool_kin_suppli;
}

// �����λ����� ��˥��������������ֻ��ˤʤꤽ���ʤ���Ĵ��(���ä���)
if ($hai_ym==201802) {
    $tool_kin = $tool_kin - 260000;
}
if ($hai_ym==201809) {
    $tool_kin = $tool_kin - 1134000;
}
if ($hai_ym==201901) {
    $tool_kin = $tool_kin - 1000000;
}
*/
/*
if ($hai_ym==202103) {
    $tool_kin = $tool_kin - 382140;
}
*/
$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����������'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
                        // ������Ͽ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '����������', 1.00000)", $yyyymm, $tool_kin);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١����ο�����Ͽ�˼��Ԥ��ޤ���";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='����������'", $tool_kin, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // �����ѥ����꡼�μ¹�
        $_SESSION['s_sysmsg'] .= "<br>�ǡ����١�����UPDATE�˼��Ԥ��ޤ��� No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

/***** ϫ    ̳    �� *****/
    ///// act_cl_history �Σã��̷������ɽ�������
$query = sprintf("select sum(kin00), sum(kin01), sum(kin02) from act_cl_history where pl_bs_ym=%d and actcod>=8101 and actcod<=8130", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_roumu = $res[0][0];
    $l_roumu = $res[0][1];
    $roumu   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '����ϫ̳��')", $yyyymm, $roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ϫ̳��')", $yyyymm, $c_roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�ϫ̳��')", $yyyymm, $l_roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥�ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("ϫ̳����оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

    // ���ץ�����ϫ̳��
$ctoku_roumu = 0;
    // 525 ����ϫ̳��
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=525 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += $res[0][0];
    // 556 ����ϫ̳��
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=556 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += $res[0][0];
    // ���ץ���������� �����ӥ������
$query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d and (total_item='��������' or total_item='��������' or total_item='�ó�����')", $yyyymm);
$res = array();
getResult($query, $res);
$ctoku_roumu += $res[0][0];
    // 523 ���ץ���ΩHAϫ̳������50��
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=523 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * 0.5),0);
    // 500 ������ϫ̳������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 510 ������Cϫ̳������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=510 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 518 ��¤����ϫ̳������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=518 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
    // 582 ��¤��ϫ̳������ ��2016/04����
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=582 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 511 ����������Cô��ϫ̳������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=511 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 512 ���������ݷײ�Cô��ϫ̳������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=512 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 513 �����ϫ̳������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=513 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 514 ���ץ���ϫ̳������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=514 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);

$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ϫ̳��'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  ///// ����Ͽ�ѤߤΥ����å�
    // ������Ͽ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����ϫ̳��')", $yyyymm, $ctoku_roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�����ϫ̳�����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='���ץ�����ϫ̳��'", $ctoku_roumu, $yyyymm);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�����ϫ̳��ι�������<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
    
/***** ��          �� *****/
    ///// act_cl_history �Σã��̷������ɽ������� ***** ��¤����η��� *****
$query = sprintf("select sum(kin00), sum(kin01), sum(kin02) from act_cl_history where pl_bs_ym=%d and actcod<=8000", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_keihi = $res[0][0];
    $l_keihi = $res[0][1];
    $keihi   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '������¤����')", $yyyymm, $keihi);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("������¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ���¤����')", $yyyymm, $c_keihi);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ���¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���¤����')", $yyyymm, $l_keihi);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥���¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("��¤����η��� �оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

    // �Х������¤����
$b_keihi = 0;
    // 560 �������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$b_keihi += $res[0][0];
    // 500 ��������������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$b_keihi += Uround(($res[0][0] * $bimor_allo),0);

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х������¤����')", $yyyymm, $b_keihi);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х������¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    // �������¤����
$s_keihi = 0;
    // 559 �������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=559 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$s_keihi += $res[0][0];
    // 2011/04 ��� 581 ������� �ɲ�
if ($yyyymm >= 201104) {
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=581 and actcod<=8000", $ym4);
    $res = array();
    getResult($query, $res);
    $s_keihi += $res[0][0];
}
if ($yyyymm < 201106) {     // 2011ǯ6�������ꤷ�ʤ�
        // 500 ��������������
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod<=8000", $ym4);
    $res = array();
    getResult($query, $res);
    $s_keihi += Uround(($res[0][0] * $ss_allo),0);
}

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���¤����')", $yyyymm, $s_keihi);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// ���ץ�������¤����
$ctoku_keihi = 0;
    // 525 ������¤����
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=525 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += $res[0][0];
    // 556 ������¤����
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=556 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += $res[0][0];
    // 523 ���ץ���ΩHA��¤��������50��
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=523 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * 0.5),0);
    // 500 ��������¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 510 ������C��¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=510 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 518 ��¤������¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=518 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 582 ��¤����¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=582 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 511 ����������Cô����¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=511 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 512 ���������ݷײ�Cô����¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=512 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 513 �������¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=513 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 514 ���ץ�����¤��������
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=514 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�������¤����')", $yyyymm, $ctoku_keihi);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�������¤�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/***** ���������ų���ê���� *****/
    ///// act_invent_history ���ê���� ����
$query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='���ץ�'", $yyyymm);
if (getUniResult($query,$c_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ����ê������оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='��˥�'", $yyyymm);
if (getUniResult($query,$l_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥�����ê������оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// Ĵ����ɬ�פʾ���Ĵ������
$query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='���ץ����ê����Ĵ��'", $yyyymm);
if ((getUniResult($query, $adjust_c)) > 0) {
    $c_kin = ($c_kin + ($adjust_c));
}
$query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='��˥�����ê����Ĵ��'", $yyyymm);
if ((getUniResult($query, $adjust_l)) > 0) {
    $l_kin = ($l_kin + ($adjust_l));
}
$all_kin = (($c_kin + $l_kin) * (1));       // ����ê���� ����ȿž�Ϥ�᤿ »�׷׻����ɽ����ǥޥ��ʥ�������
$c_kin   = ($c_kin * (1));                  // ���ץ�ê���� ��
$l_kin   = ($l_kin * (1));                  // ��˥�ê���� ��
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���δ���ê����')", $yyyymm, $all_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���δ���ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ����ê����')", $yyyymm, $c_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ����ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�����ê����')", $yyyymm, $l_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥�����ê�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ��  ��  ��  �� *****/
///// ���Τ���帶���˴���ê�����­����ʪ�������Τ���¤���� ϫ̳�񡦷���ȴ���ê�����������Ĥ�����Τκ�����
///// �Ȥ��ƻ�����Σã���Ψ����Ѥ��ƺ������ã��̤�ʬ���롣���줫��ã��̤˴���ê���⡦������ϫ̳�񡦷����
///// ­���ƴ���ê���������ƣã��̤���帶���򻻽Ф��롣
    // ������帶��   = (pl_bs_summary �� t_id='A' t_row=2 pl_bs_ym=�ǯ��)
    // ���κ�����     = (������帶�� - (���δ���ê���� + ����ϫ̳�� + ������¤���� - ���δ���ê����))
    // ���ץ������   = Uround((���κ����� * ���ץ������=>allo),0)
    // ��˥�������   = (���κ����� - ���ץ������)
    // ���ץ���帶�� = (���ץ����ê���� + ���ץ������ + ���ץ�ϫ̳�� + ���ץ���¤���� - ���ץ����ê����)
    // ��˥���帶�� = (��˥�����ê���� + ��˥������� + ��˥�ϫ̳�� + ��˥���¤���� - ��˥�����ê����)
$query = sprintf("select kin1 from pl_bs_summary where t_id='A' and t_row=2 and pl_bs_ym=%d", $yyyymm);
getUniResult($query,$res_kin);
if ($res_kin != 0) {
    $uri_genka = $res_kin;                                              // ������帶��
        ///// Ĵ����������ˤ�Ĵ������
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='��帶��Ĵ��'", $yyyymm);
    if ((getUniResult($query, $adjust)) > 0) {
        $uri_genka = ($uri_genka + ($adjust));
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("��帶�����оȥǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$material    = ($uri_genka - ($invent + $roumu + $keihi - $all_kin));       // ���κ����� $all_kin �ϥޥ��ʥ�
$material_c  = Uround(($material * $c_ritu), 0);                            // ���ץ������
$material_l  = ($material - $material_c);                                   // ��˥�������
$uri_genka_c = ($invent_c + $material_c + $c_roumu + $c_keihi - $c_kin);    // ���ץ���帶�� $c_kin �ϥޥ��ʥ�
$uri_genka_l = ($invent_l + $material_l + $l_roumu + $l_keihi - $l_kin);    // ��˥���帶�� $l_kin �ϥޥ��ʥ�
    ///// ���������Ͽ
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���κ�����')", $yyyymm, $material);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���κ��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ������')", $yyyymm, $material_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ���������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�������')", $yyyymm, $material_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥����������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// ��帶������Ͽ
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '������帶��')", $yyyymm, $uri_genka);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("������帶������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ���帶��')", $yyyymm, $uri_genka_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ���帶������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���帶��')", $yyyymm, $uri_genka_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥���帶������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** �� �� �� �� �� *****/
    ///// ��������� = (���� - ��帶��)
$gross_profit   = ($uri - $uri_genka);              // �������������
$gross_profit_c = ($uri_c - $uri_genka_c);          // ���ץ����������
$gross_profit_l = ($uri_l - $uri_genka_l);          // ��˥����������
    ///// ��Ͽ
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '����������')", $yyyymm, $gross_profit);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���������פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�������')", $yyyymm, $gross_profit_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ������פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�������')", $yyyymm, $gross_profit_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥������פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** �δ���οͷ��� *****/
    ///// act_cl_history �Σã��̷������ɽ�������
$query = sprintf("select sum(kin10), sum(kin11), sum(kin12) from act_cl_history where pl_bs_ym=%d and actcod>=8101 and actcod<=8130", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_jin = $res[0][0];
    $l_jin = $res[0][1];
    $jin   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���οͷ���')", $yyyymm, $jin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���οͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�ͷ���')", $yyyymm, $c_jin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��ͷ���')", $yyyymm, $l_jin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥��ͷ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�δ���οͷ����оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** �δ���� �� �� *****/
    ///// act_cl_history �Σã��̷������ɽ�������
$query = sprintf("select sum(kin10), sum(kin11), sum(kin12) from act_cl_history where pl_bs_ym=%d and actcod<=8000", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_kei = $res[0][0];
    $l_kei = $res[0][1];
    $kei   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���η���')", $yyyymm, $kei);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���η������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ����')", $yyyymm, $c_kei);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥�����')", $yyyymm, $l_kei);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥��������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�δ���η����оݥǡ���������ޤ���<br>�ǯ�� %d", $yyyymm);
    query_affected_trans($con, "rollback");         // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** �δ���� �� �� *****/
$hankan   = ($jin + $kei);      // �����δ���
$hankan_c = ($c_jin + $c_kei);  // ���ץ��δ���
$hankan_l = ($l_jin + $l_kei);  // ��˥��δ���
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�����δ���')", $yyyymm, $hankan);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�����δ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��δ���')", $yyyymm, $hankan_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ��δ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��δ���')", $yyyymm, $hankan_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥��δ������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/***** ��  ��  ��  �� *****/
    ///// �Ķ����� = (��������� - �δ��� ��)
$ope_profit   = ($gross_profit - $hankan);              // ���αĶ�����
$ope_profit_c = ($gross_profit_c - $hankan_c);          // ���ץ�Ķ�����
$ope_profit_l = ($gross_profit_l - $hankan_l);          // ��˥��Ķ�����
    ///// ��Ͽ
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���αĶ�����')", $yyyymm, $ope_profit);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���αĶ����פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķ�����')", $yyyymm, $ope_profit_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķ����פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķ�����')", $yyyymm, $ope_profit_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķ����פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ��̳���� �� �� *****/
$query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=7 and pl_bs_ym=%d", $yyyymm);
getUniResult($query,$res_kin);
/* 2015/09/03
if ($res_kin != 0) {
*/
    $gyoumu = $res_kin;     // ���ζ�̳��������
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ζ�̳��������')", $yyyymm, $gyoumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ζ�̳������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// ����¾��������Ƥ���
    $query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=5 and pl_bs_ym=%d", $yyyymm);
    if (getUniResult($query,$p_other) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("�Ķȳ����� ����¾�Υǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
/* 2015/09/03
} else {
    ///// 2003/01 �����ϲ��ܤ��äƤ��ʤ��ä��ΤǤ����򸫤뤳�Ȥˤʤ�
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='��̳��������'", $yyyymm);
    if ((getUniResult($query, $gyoumu)) > 0) {
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ζ�̳��������')", $yyyymm, $gyoumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���ζ�̳������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("��̳�����������оȥǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
*/
    ///// �����ǥ��ץ�ȥ�˥�������Ψ���������ͽ�� (����������)
    ///// �裳���ϡ��裲���Υǡ����� ���ץ�=72.61% ��˥�=27.39%��
    ///// �裴���ϡ��裳���Υǡ����� ���ץ�=72.61% ��˥�=27.39%��
    ///// �裵��������� ���ץ�=77.87% ��˥�=22.13% ���ѹ� Ⱦ����˸�ľ��ͽ��
    ///// �裵���������� ���ץ�=76.73% ��˥�=23.27% ���ѹ� Ⱦ����˸�ľ��ͽ��
    ///// �裶��������� ���ץ�=80.71% ��˥�=19.29% ���ѹ� Ⱦ����˸�ľ��ͽ��
    ///// �裶���������� ���ץ�=80.27% ��˥�=19.73% ���ѹ� Ⱦ����˸�ľ��ͽ��
    ///// �裷��������� ���ץ�=78.87% ��˥�=24.63% ���ѹ�
    ///// �裷���������� ���ץ�=81.27% ��˥�=18.73% ���ѹ�
    ///// �裸���������� ���ץ�=82.14% ��˥�=17.86% ���ѹ�
    ///// �裹��������� ���ץ�=82.42% ��˥�=17.58% ���ѹ�
    ///// �裹���������� ���ץ�=83.65% ��˥�=16.35% ���ѹ�
    ///// ����Ĵ����ʾ��ɡ��Ĵ������nkb_input�θ��Ψ�ȶ�ۤ��Ĵ�������
if ($yyyymm <= 201001) {       // 2009ǯ12��ޤǤϸ����ͤ�
    $zenki_uriagehi_c = 0.8365;     // ����Ⱦ��ʬ���������ޥ����������������ͽ��
    $zenki_uriagehi_l = 0.1635;
} else {
    $zenki_uriagehi_c = Uround(($uri_c_total / $uri_total),4);    // ���ץ��������Ψ(��������
    $zenki_uriagehi_l = 1 - $zenki_uriagehi_c - $ss_allo;         // ��˥���������Ψ(1-���ץ�������-��������
    // ���Τγ��˾��ɤ��̣������
    //$zenki_uriagehi_l = 1 - $zenki_uriagehi_c - $ss_allo - $b_allo;         // ��˥���������Ψ(1-���ץ�������-�������-�����������
}

// 2005ǯ11��ʹߤη�ʹߥ�˥���ͭ�ζ�̳�����������б�
if ($yyyymm >= 200512) {
    if ($yyyymm >= 200907) {
        $tmp_gyoumu_l = 0;                      // 200907�ʹߤϥ�˥���ͭ�ʤ�
    } elseif ($yyyymm == 200906) {
    // 200906�ϥ�˥���ͭʬ��Ĵ���Ѥ�(2����ʬ�ޥ��ʥ�)�ΰٸ����᤹
        $tmp_gyoumu_l = 1550450;
        $gyoumu = ($gyoumu + $tmp_gyoumu_l * 2);
    } else {
        $tmp_gyoumu_l = 1550450;
        $gyoumu = ($gyoumu - $tmp_gyoumu_l);    // ���Τ����˥���ͭ��ʬ����˰����Ƥ���
    }
} elseif ($yyyymm == 200511) {
    $tmp_gyoumu_l = 2713288;                // ����11�������ۤ��㤦 (�����10��ʬ��11��ʬ��绻�������)
    $gyoumu = ($gyoumu - $tmp_gyoumu_l);    // ���Τ����˥���ͭ��ʬ����˰����Ƥ���
} else {
    $tmp_gyoumu_l = 0;                      // ����¹Ԥ��������б�
}
$gyoumu_c = Uround(($gyoumu * $zenki_uriagehi_c), 0);       // ���ץ��̳��������
if ($yyyymm == 200906) {
    $gyoumu_l = ($gyoumu - $gyoumu_c - $tmp_gyoumu_l * 2);  // ��˥���̳��������
    $gyoumu = ($gyoumu - $tmp_gyoumu_l * 2);    // ���Τζ�̳���������򸵤��᤹(C/L���꽪λ�Τ���)
} else {
    $gyoumu_l = ($gyoumu - $gyoumu_c + $tmp_gyoumu_l);          // ��˥���̳��������
    $gyoumu = ($gyoumu + $tmp_gyoumu_l);        // ���Τζ�̳���������򸵤��᤹(C/L���꽪λ�Τ���)
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��̳��������')", $yyyymm, $gyoumu_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ��̳������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���̳��������')", $yyyymm, $gyoumu_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥���̳������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
if ($yyyymm == 200906) {
    $gyoumu_l_chosei = $gyoumu_l + 3100900;
} elseif ($yyyymm == 200905) {
    $gyoumu_l_chosei = $gyoumu_l - 1550450;
} elseif ($yyyymm == 200904) {
    $gyoumu_l_chosei = $gyoumu_l - 1550450;
} else {
    $gyoumu_l_chosei = $gyoumu_l;
}

$gyoumu_b     = Uround(($gyoumu_l_chosei * $bimor_allo),0);    // �Х�����̳��������
$gyoumu_s     = Uround(($gyoumu_l_chosei * $ss_allo),0);       // �������̳��������
$gyoumu_ctoku = Uround(($gyoumu_c * $ctoku_allo),0);           // ���ץ������̳��������

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х�����̳��������')", $yyyymm, $gyoumu_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х�����̳������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���̳��������')", $yyyymm, $gyoumu_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���̳������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ������̳��������')", $yyyymm, $gyoumu_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ������̳������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ��  ��  ��  �� *****/
$query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=6 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$s_wari) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���λ������')", $yyyymm, $s_wari);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���λ����������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("����������оȥǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// �裳���� ���ץ�=72.61% ��˥�=27.39%
$s_wari_c = Uround(($s_wari * $zenki_uriagehi_c), 0);      // ���ץ�������
$s_wari_l = ($s_wari - $s_wari_c);              // ��˥��������
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�������')", $yyyymm, $s_wari_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�����������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��������')", $yyyymm, $s_wari_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

$s_wari_b     = Uround(($s_wari_l * $bimor_allo),0);    // �Х����������
$s_wari_s     = Uround(($s_wari_l * $ss_allo),0);       // ������������
$s_wari_ctoku = Uround(($s_wari_c * $ctoku_allo),0);    // ���ץ�����������

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����������')", $yyyymm, $s_wari_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х��������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��������')", $yyyymm, $s_wari_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����������')", $yyyymm, $s_wari_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ���������������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ��    ��    ¾ *****/
    ///// A5 = ����¾���� ��̳���������β��ܤ��ʤ��ä����Τ����
    ///// ����¾ = (A5 - ��̳��������)
if (!isset($p_other)) {     // $p_other �����åȤ���Ƥ��ʤ���о嵭��Ŭ��
    $query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=5 and pl_bs_ym=%d", $yyyymm);
    if (getUniResult($query,$other) > 0) {
        $p_other = ($other - $gyoumu);      // ���αĶȳ����פ���¾
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���αĶȳ����פ���¾')", $yyyymm, $p_other);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("���λ����������Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("�Ķȳ�����A5���оȥǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    ///// �Ķȳ����� ����¾ A5 $p_other ����Ͽ
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���αĶȳ����פ���¾')", $yyyymm, $p_other);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���αĶȳ����פ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
    ///// �裳���� ���ץ�=72.61% ��˥�=27.39%
$p_other_c = Uround(($p_other * $zenki_uriagehi_c), 0);        // ���ץ�Ķȳ����פ���¾
$p_other_l = ($p_other - $p_other_c);               // ��˥��Ķȳ����פ���¾
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����פ���¾')", $yyyymm, $p_other_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����פ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����פ���¾')", $yyyymm, $p_other_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����פ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$p_other_b     = Uround(($p_other_l * $bimor_allo),0);    // �Х����Ķȳ����פ���¾
$p_other_s     = Uround(($p_other_l * $ss_allo),0);       // ������Ķȳ����פ���¾
$p_other_ctoku = Uround(($p_other_c * $ctoku_allo),0);    // ���ץ�����Ķȳ����פ���¾

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����Ķȳ����פ���¾')", $yyyymm, $p_other_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����פ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��Ķȳ����פ���¾')", $yyyymm, $p_other_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����פ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����Ķȳ����פ���¾')", $yyyymm, $p_other_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����פ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** �Ķȳ����׹�� *****/
$nonope_p_sum   = ($gyoumu + $s_wari + $p_other);         // ���αĶȳ����׷�
$nonope_p_c_sum = ($gyoumu_c + $s_wari_c + $p_other_c);   // ���ץ�Ķȳ����׷�
$nonope_p_l_sum = ($gyoumu_l + $s_wari_l + $p_other_l);   // ��˥��Ķȳ����׷�
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���αĶȳ����׷�')", $yyyymm, $nonope_p_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���αĶȳ����׹�פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����׷�')", $yyyymm, $nonope_p_c_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����׹�פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����׷�')", $yyyymm, $nonope_p_l_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����׹�פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ��  ʧ  ��  © *****/
                        // kin1 �ˤʤ�Τ����
$query = sprintf("select kin1 from pl_bs_summary where t_id='A' and t_row=8 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$risoku) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���λ�ʧ��©')", $yyyymm, $risoku);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���λ�ʧ��©����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("��ʧ��©���оȥǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// �裳���� ���ץ�=72.61% ��˥�=27.39%
$risoku_c = Uround(($risoku * $zenki_uriagehi_c), 0);      // ���ץ��ʧ��©
$risoku_l = ($risoku - $risoku_c);              // ��˥���ʧ��©
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ��ʧ��©')", $yyyymm, $risoku_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ��ʧ��©����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥���ʧ��©')", $yyyymm, $risoku_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥���ʧ��©����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$risoku_b     = Uround(($risoku_l * $bimor_allo),0);    // �Х�����ʧ��©
$risoku_s     = Uround(($risoku_l * $ss_allo),0);       // �������ʧ��©
$risoku_ctoku = Uround(($risoku_c * $ctoku_allo),0);    // ���ץ������ʧ��©

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х�����ʧ��©')", $yyyymm, $risoku_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х�����ʧ��©����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ʧ��©')", $yyyymm, $risoku_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ʧ��©����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ������ʧ��©')", $yyyymm, $risoku_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ������ʧ��©����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ��    ��    ¾ *****/        // �Ķȳ�����
                        // kin1 �ˤʤ�Τ����
$query = sprintf("select kin1 from pl_bs_summary where t_id='A' and t_row=9 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$l_other) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���αĶȳ����Ѥ���¾')", $yyyymm, $l_other);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���αĶȳ����Ѥ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�Ķȳ����Ѥ���¾���оȥǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// �裳���� ���ץ�=72.61% ��˥�=27.39%
$l_other_c = Uround(($l_other * $zenki_uriagehi_c), 0);        // ���ץ�Ķȳ����Ѥ���¾
$l_other_l = ($l_other - $l_other_c);               // ��˥��Ķȳ����Ѥ���¾
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����Ѥ���¾')", $yyyymm, $l_other_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����Ѥ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����Ѥ���¾')", $yyyymm, $l_other_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����Ѥ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$l_other_b     = Uround(($l_other_l * $bimor_allo),0);    // �Х����Ķȳ����Ѥ���¾
$l_other_s     = Uround(($l_other_l * $ss_allo),0);       // ������Ķȳ����Ѥ���¾
$l_other_ctoku = Uround(($l_other_c * $ctoku_allo),0);    // ���ץ�����Ķȳ����Ѥ���¾

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '�Х����Ķȳ����Ѥ���¾')", $yyyymm, $l_other_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�Х����Ķȳ����Ѥ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��Ķȳ����Ѥ���¾')", $yyyymm, $l_other_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��Ķȳ����Ѥ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�����Ķȳ����Ѥ���¾')", $yyyymm, $l_other_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�����Ķȳ����Ѥ���¾����Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** �Ķȳ����ѹ�� *****/
$nonope_l_sum   = ($risoku + $l_other);             // ���αĶȳ����ѷ�
$nonope_l_c_sum = ($risoku_c + $l_other_c);         // ���ץ�Ķȳ����ѷ�
$nonope_l_l_sum = ($risoku_l + $l_other_l);         // ��˥��Ķȳ����ѷ�
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���αĶȳ����ѷ�')", $yyyymm, $nonope_l_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���αĶȳ����׹�פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�Ķȳ����ѷ�')", $yyyymm, $nonope_l_c_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�Ķȳ����ѹ�פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��Ķȳ����ѷ�')", $yyyymm, $nonope_l_l_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥��Ķȳ����ѹ�פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** ��  ��  ��  �� *****/
$query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=10 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$current_profit) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'AS���ηо�����')", $yyyymm, $current_profit);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("���ηо����פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("�о����פ��оȥǡ������ʤ�<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// ���������ų���ê���⡦��帶�� ����Ĵ�������뤿��о����פϷ׻��ǵ���
$current_p   = ($ope_profit + ($nonope_p_sum) - ($nonope_l_sum));       // ���ηо����� �ޥ��ʥ����θ����()��Ȥ�
$current_p_c = ($ope_profit_c + ($nonope_p_c_sum) - ($nonope_l_c_sum)); // ���ץ�о�����
$current_p_l = ($ope_profit_l + ($nonope_p_l_sum) - ($nonope_l_l_sum)); // ��˥��о�����
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ηо�����')", $yyyymm, $current_p);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ηо����פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '���ץ�о�����')", $yyyymm, $current_p_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץ�о����פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '��˥��о�����')", $yyyymm, $current_p_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��˥��о����פ���Ͽ�˼���<br>�� %d�� %d��",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$current_sai = number_format($current_profit - $current_p);     // �о����פ�AS/400 �Ȥκ���
$_SESSION["s_sysmsg"] .= sprintf("<font color='white'>�о����פκ���=%s</font><br>",$current_sai);


/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, "commit");
$_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>��%d�� %d���»�׷׻���λ</font>",$ki,$tuki);
header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
exit();

