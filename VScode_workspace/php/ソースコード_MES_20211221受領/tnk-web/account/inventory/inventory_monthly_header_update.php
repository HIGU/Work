<?php
//////////////////////////////////////////////////////////////////////////////
// »���о������̤�ê����ۤι���(���ץ顦��˥������ץ������Х����      //
//                                ���Ρ�����¾����)                         //
// Copyright (C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/09 Created   inventory_monthly_header_update.php                 //
// 2003/12/10 ̵���ٵ��ʡ�����ٵ��ʤ���Ͽ�����å����å����ɲ�            //
// 2004/01/07 �嵭�Υ��å��� rollback ��ȴ���Ƥ���Τ����� ����¾��ê���� //
//        �ʤ������б����å����ɲ�(����ä�Ĵ������Фʤ���礬�ۤȤ��)//
//        ���Τ�̵���ٵ��ʤ�������å���ȴ���Ƥ���Τ���(��˥�������)  //
// 2005/02/09 �ǥ��쥯�ȥ���ѹ� account/ �� account/inventory/ ��          //
// 2017/10/12 2017/10�����˽���ɸ�फ������ذ�������              ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
// ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()�ǻ���
access_log();                               // Script Name �ϼ�ư����

$_SESSION['site_index'] = 20;               // ��������ط�=20 �Ǹ�Υ�˥塼 = 99   �����ƥ�����Ѥϣ�����
$_SESSION['site_id']    = 32;               // ���̥�˥塼̵�� <= 0    �ƥ�ץ졼�ȥե�����ϣ�����

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
// $url_referer     = $_SERVER['HTTP_REFERER'];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
$url_referer     = $_SESSION['act_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////////// ǧ�ڥ����å�
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // ���¥�٥뤬���ʲ��ϵ���
if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    // $_SESSION['s_sysmsg'] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
    $_SESSION['s_sysmsg'] = "Accounting Group �θ��¤�ɬ�פǤ���";
    // header("Location: http:" . WEB_HOST . "account/act_menu.php");   // ����ƽи������
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// �о�ǯ������ (ǯ��Τߤ����)
if ( isset($_SESSION['act_ym']) ) {
    $act_ym = $_SESSION['act_ym'];
    $s_ymd  = $act_ym . '01';   // ������
    $e_ymd  = $act_ym . '99';   // ��λ��
} else {
    $_SESSION['s_sysmsg'] = '�о�ǯ����ꤵ��Ƥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    exit();
}

////////// ̵���ٵ��ʡ�����ٵ��ʤη��Ͽ������Ƥ��뤫�����å�
$query_chk = "SELECT parts_no FROM provide_item WHERE reg_ym={$act_ym} limit 1";
if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
    ///// ��Ͽ�ʤ�(�����Ͽ��ɬ��)
    query_affected_trans($con, 'rollback');             // transaction rollback
    $_SESSION['s_sysmsg'] .= "{$act_ym}���ε���ٵ��ʤι���������Ƥ��ޤ���<br>�����Ͽ���Ʋ�������";
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/********************* ���Τ���Ͽ�ѤߤΥ����å� ****************************/
$search = "where invent_ym={$act_ym} and item='����'";
//////////// �إå����˥쥳���ɤ����뤫��
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "<font color='white'>���ΤΥǡ�������Ͽ�ѤߤǤ���</font><br>";      // .= ��å��������ɲä���
} else {
    //////////// ��� ��ۡ��쥳���ɿ�����
    $search = "where invent_ym={$act_ym} and pro.type is null";     // ̵���ٵ��ʤ����
    // $search = "where invent_ym={$act_ym}";     // num_div 1=���� 3=��˥� 5=���ץ�
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as ���_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���_t
                      from
                            inventory_monthly as inv
                      left outer join
                            provide_item as pro
                      on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                      %s', $search);
    $res_sum = array();     // �����
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "���Τι�׶�ۤ������Ǥ��ޤ���";      // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    $maxrows   = $res_sum[0][0];  // ��ץ쥳���ɿ�
    $sum_kin_z = $res_sum[0][1];  // ��� ê�� ���(����)
    $sum_kin_t = $res_sum[0][2];  // ��� ê�� ���(����)
    /////////// �쥳���ɤ�̵ͭ������å�
    if ( $maxrows == 0) {         // $maxrows �ǥ����å�
        $_SESSION['s_sysmsg'] .= "{$act_ym}��ê���ǡ������ʤ���";   // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    /////////// header �ơ��֥�˽����
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, '����', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '���ΤΥإå�������ߤ˼��ԡ�';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>���Τ�ê����ۤ򹹿����ޤ�����</font><br>";      // .= ��å��������ɲä���
    }
}

/********************* ���ץ����Ͽ�ѤߤΥ����å� ****************************/
$search = "where invent_ym={$act_ym} and item='���ץ�'";
//////////// �إå����˥쥳���ɤ����뤫��
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "<font color='white'>���ץ�Υǡ�������Ͽ�ѤߤǤ���</font><br>";      // .= ��å��������ɲä���
} else {
    //////////// ��� ��ۡ��쥳���ɿ�����
    $search = "where invent_ym={$act_ym} and num_div='5'";     // num_div 1=���� 3=��˥� 5=���ץ�
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as ���_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���_t
                      from inventory_monthly as inv %s', $search);
    $res_sum = array();     // �����
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "���ץ�ι�׶�ۤ������Ǥ��ޤ���";      // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    $maxrows   = $res_sum[0][0];  // ��ץ쥳���ɿ�
    $sum_kin_z = $res_sum[0][1];  // ��� ê�� ���(����)
    $sum_kin_t = $res_sum[0][2];  // ��� ê�� ���(����)
    /////////// �쥳���ɤ�̵ͭ������å�
    if ( $maxrows == 0) {         // $maxrows �ǥ����å�
        $_SESSION['s_sysmsg'] .= "{$act_ym}��ê���ǡ������ʤ���";   // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    /////////// header �ơ��֥�˽����
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, '���ץ�', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '���ץ�Υإå�������ߤ˼��ԡ�';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ץ��ê����ۤ򹹿����ޤ�����</font><br>";      // .= ��å��������ɲä���
    }
}

/********************* ��˥�����Ͽ�ѤߤΥ����å� ****************************/
$search = "where invent_ym={$act_ym} and item='��˥�'";
//////////// �إå����˥쥳���ɤ����뤫��
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "<font color='white'>��˥��Υǡ�������Ͽ�ѤߤǤ���</font><br>";      // .= ��å��������ɲä���
} else {
    //////////// ��� ��ۡ��쥳���ɿ�����
    $search = "where invent_ym={$act_ym} and num_div='3' and pro.type is null";     // num_div 1=���� 3=��˥� 5=���ץ�
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as ���_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���_t
                      from
                            inventory_monthly as inv
                      left outer join
                            provide_item as pro
                      on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                      %s', $search);
    $res_sum = array();     // �����
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "��˥��ι�׶�ۤ������Ǥ��ޤ���";      // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    $maxrows   = $res_sum[0][0];  // ��ץ쥳���ɿ�
    $sum_kin_z = $res_sum[0][1];  // ��� ê�� ���(����)
    $sum_kin_t = $res_sum[0][2];  // ��� ê�� ���(����)
    /////////// �쥳���ɤ�̵ͭ������å�
    if ( $maxrows == 0) {         // $maxrows �ǥ����å�
        $_SESSION['s_sysmsg'] .= "{$act_ym}��ê���ǡ������ʤ���";   // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    /////////// header �ơ��֥�˽����
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, '��˥�', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '��˥��Υإå�������ߤ˼��ԡ�';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>��˥���ê����ۤ򹹿����ޤ�����</font><br>";      // .= ��å��������ɲä���
    }
}

/********************* �Х�������Ͽ�ѤߤΥ����å� ****************************/
$search = "where invent_ym={$act_ym} and item='�Х����'";
//////////// �إå����˥쥳���ɤ����뤫��
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "<font color='white'>�Х����Υǡ�������Ͽ�ѤߤǤ���</font><br>";      // .= ��å��������ɲä���
} else {
    //////////// ��� ��ۡ��쥳���ɿ�����
    $search = "where invent_ym={$act_ym} and (inv.parts_no like 'LR%%' or inv.parts_no like 'LC%%')"; // num_div 1=���� 3=��˥� 5=���ץ�
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as ���_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���_t
                      from inventory_monthly as inv %s', $search);
    $res_sum = array();     // �����
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "�Х����ι�׶�ۤ������Ǥ��ޤ���";      // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    $maxrows   = $res_sum[0][0];  // ��ץ쥳���ɿ�
    $sum_kin_z = $res_sum[0][1];  // ��� ê�� ���(����)
    $sum_kin_t = $res_sum[0][2];  // ��� ê�� ���(����)
    /////////// �쥳���ɤ�̵ͭ������å�
    if ( $maxrows == 0) {         // $maxrows �ǥ����å�
        $_SESSION['s_sysmsg'] .= "{$act_ym}��ê���ǡ������ʤ���";   // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    /////////// header �ơ��֥�˽����
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, '�Х����', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '��˥��Υإå�������ߤ˼��ԡ�';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>�Х�����ê����ۤ򹹿����ޤ�����</font><br>";      // .= ��å��������ɲä���
    }
}

/********************* ���ץ��������Ͽ�ѤߤΥ����å� ****************************/
$search = "where invent_ym={$act_ym} and item='���ץ�����'";
//////////// �إå����˥쥳���ɤ����뤫��
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "<font color='white'>���ץ�����Υǡ�������Ͽ�ѤߤǤ���</font><br>";      // .= ��å��������ɲä���
} else {
    //////////// �о�ǯ��ǥ��ץ�����Τߤ�ȴ�Ф����ǡ��������뤫�����å�����Ѥ���
    //////////// SQL ʸ�� where ��� ���Ѥ���
    $search = "where  invent_ym={$act_ym} and num_div='5' and (select kouji_no
                                                      from
                                                            act_payable as act
                                                      left outer join
                                                            order_plan
                                                      using(sei_no)
                                                      where
                                                            act_date<={$e_ymd} and
                                                            act.parts_no=inv.parts_no
                                                      order by act_date DESC limit 1)
                                                      like 'SC%%'";     // num_div 1=���� 3=��˥� 5=���ץ�
    
    //////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
    $query = sprintf("insert into inventory_monthly_ctoku
            select
                invent_ym     as ǯ��,
                parts_no      as �����ֹ�,
                par_code      as ������,
                zen_zai       as ����߸�,
                tou_zai       as ����߸�,
                gai_tan       as ����ñ��,
                nai_tan       as ���ñ��,
                num_div       as ������,
                (select kouji_no from act_payable as act
                    left outer join order_plan using(sei_no)
                    where act_date<={$e_ymd} and act.parts_no=inv.parts_no
                    order by act_date DESC limit 1) as kouji_no
            from
                inventory_monthly as inv
            %s 
            ", $search);       // ���� $search �ϻ���
    
    /////////// �ȥ�󥶥��������ǹ����¹�
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '���ץ�����Υǡ���ȴ�Ф��˼��ԡ�';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    }
    /*
    // �����ɸ���ʤ��椫���������ʤȶ��Ѥ�ʪ��ʬ������Ψ�˽�����Ͽ 2017/10 ���
    if ($act_ym >= 201710) {
        //////////// SQL ʸ�� where ��� ���Ѥ���
        $search = "where  invent_ym={$act_ym} and num_div='5' and ctoku_allo > 0 and (select kouji_no
                                                          from
                                                                act_payable as act
                                                          left outer join
                                                                order_plan
                                                          using(sei_no)
                                                          where
                                                                act_date<={$e_ymd} and
                                                                act.parts_no=inv.parts_no
                                                          order by act_date DESC limit 1)
                                                          not like 'SC%%'";     // num_div 1=���� 3=��˥� 5=���ץ�
    
        //////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
        $query = sprintf("insert into inventory_monthly_ctoku
                select
                    invent_ym     as ǯ��,
                    parts_no      as �����ֹ�,
                    par_code      as ������,
                    round(zen_zai * ctoku_allo) as ����߸�,
                    round(tou_zai * ctoku_allo) as ����߸�,
                    gai_tan       as ����ñ��,
                    nai_tan       as ���ñ��,
                    num_div       as ������,
                    (select kouji_no from act_payable as act
                        left outer join order_plan using(sei_no)
                        where act_date<={$e_ymd} and act.parts_no=inv.parts_no
                        order by act_date DESC limit 1) as kouji_no
                from
                    inventory_monthly as inv
                left outer join
                    inventory_ctoku_par using(parts_no)
                %s 
                ", $search);       // ���� $search �ϻ���
    }
    /////////// �ȥ�󥶥��������ǹ����¹�
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '���ץ�����Υǡ���ȴ�Ф��˼��ԡ�';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    }
    */
    //////////// ��� ��ۡ��쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
    $search = "where  invent_ym={$act_ym}";     // 
    $query = sprintf('select
                        count(*),
                        sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as ���_z,
                        sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���_t
                      from inventory_monthly_ctoku as inv %s', $search);
    $res_sum = array();     // �����
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "���ץ�����ι�׶�ۤ������Ǥ��ޤ���";      // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    $maxrows   = $res_sum[0][0];  // ��ץ쥳���ɿ�
    $sum_kin_z = $res_sum[0][1];  // ��� ê�� ���(����)
    $sum_kin_t = $res_sum[0][2];  // ��� ê�� ���(����)
    /////////// �쥳���ɤ�̵ͭ������å�
    if ( $maxrows == 0) {         // $maxrows �ǥ����å�
        $_SESSION['s_sysmsg'] .= "{$act_ym}��ê���ǡ������ʤ���";   // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    /////////// header �ơ��֥�˽����
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, '���ץ�����', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '���ץ�����Υإå�������ߤ˼��ԡ�';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ץ������ê����ۤ򹹿����ޤ�����</font><br>";      // .= ��å��������ɲä���
    }
}

/********************* ����¾����Ͽ�ѤߤΥ����å� ****************************/
$search = "where invent_ym={$act_ym} and item='����¾'";
//////////// �إå����˥쥳���ɤ����뤫��
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "<font color='white'>����¾�Υǡ�������Ͽ�ѤߤǤ���</font><br>";      // .= ��å��������ɲä���
} else {
    //////////// ��� ��ۡ��쥳���ɿ�����
    $search = "where invent_ym={$act_ym} and num_div != '3' and num_div != '5'";     // num_div 1=���� 3=��˥� 5=���ץ�
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as ���_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���_t
                      from inventory_monthly as inv %s', $search);
    $res_sum = array();     // �����
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "����¾�ι�׶�ۤ������Ǥ��ޤ���";      // .= ��å��������ɲä���
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
    $maxrows   = $res_sum[0][0];  // ��ץ쥳���ɿ�
    $sum_kin_z = $res_sum[0][1];  // ��� ê�� ���(����)
    $sum_kin_t = $res_sum[0][2];  // ��� ê�� ���(����)
    /////////// �쥳���ɤ�̵ͭ������å�
    if ( $maxrows == 0) {         // $maxrows �ǥ����å�
        $_SESSION['s_sysmsg'] .= "<font color='white'>{$act_ym}������¾��ê���Ϥ���ޤ���Ǥ�����</font>";   // .= ��å��������ɲä���
    } else {
        /////////// header �ơ��֥�˽����
        $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                        values ({$act_ym}, '����¾', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
        if (($rows = query_affected_trans($con, $query)) <= 0) {
            $_SESSION['s_sysmsg'] .= '����¾�Υإå�������ߤ˼��ԡ�';
            query_affected_trans($con, 'rollback');         // transaction rollback
            header("Location: $url_referer");               // ľ���θƽи������
            exit();
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>����¾��ê����ۤ򹹿����ޤ�����</font><br>";      // .= ��å��������ɲä���
        }
    }
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');
$_SESSION['s_sysmsg'] .= "<font color='white'>{$act_ym}�����ƽ�����λ</font>";
header("Location: $url_referer");                   // ľ���θƽи������
// header('Location: http:' . WEB_HOST . 'account/inventory_monthly_ctoku_view.php');   // �Ȳ񥹥���ץȤ�
exit();

/********** Logic End   **********/
?>
