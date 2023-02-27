<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� emp_menu.php �� include file ���Ȱ������׾���ɽ��         //
// Copyright (C) 2003-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/04/23 Created  view_user_statistic.php �Ȥꤢ����ʿ��ǯ��Τ�       //
// 2003/04/25 ľ����Ψ���ɲ� ľ�����硧(����������δ�������) ľ�ܴ���      //
// 2004/02/06 ���ǯ�𤬤ʤ���date('-m-d')�ˤʤäƤ���Τ�'-04-01'�ؽ���    //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2005/01/26 ���׾���� division by zero check ���ɲ�                      //
//            background-image���ɲä��ƥǥ������ѹ�(AM/PM�����ؼ�)         //
// 2006/01/11 �Ұ����׾���˼Ұ����ȥѡ��ȿ����ɲ�                          //
// 2016/08/26 �Ϳ��׻���ˡ�򸽺ߤξ����˹�碌���ѹ�                   ��ë //
// 2017/09/13 ���칩�������sid=95�ˤ�׻�������������͡�����         ��ë //
// 2021/07/12 �Ұ�����ɽ���ɲá����׾���Ǽ�������и��Ԥʤ�                //
//            ;�פʿͰ����ޤޤ�Ƥ���Τ����                         ��ë //
//////////////////////////////////////////////////////////////////////////////
// access_log('view_user_statistic.php');        // Script Name ��ư����
//access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
//echo view_file_name(__FILE__);
require_once ('../tnk_func.php');
require_once ('../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');      // TNK ������ menu class
require_once ('emp_function.php');          // �Ұ���˥塼����
require_once ('../ControllerHTTP_Class.php');   // TNK ������ MVC Controller Class
if (isset($_POST['offset'])) {
    $offset = $_POST['offset'];
} else {
    $offset = 0;
}
/*** �����꡼������ & �¹� ***/
    ///// ���������(YYYY-04-01)    ��Ĺ �࿦�� �и��� �����
if (date('m') <= 3) {
    $yyyy = (date('Y') - 1);
    // $base_date = ($yyyy . date('-m-d'));
    $base_date = ($yyyy . '-04-01');
} else {
    $base_date = (date('Y') . '-04-01');
}
$query = sprintf("select avg(extract(years from age('%s'::timestamp, (birthday)::timestamp))) as avg_years from user_detailes where pid != 120 and retire_date is null and sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826'", $base_date);
//��°������ڤӤ���¾����������칩������
getUniResult($query, $res_base_avg);
$res_base_avg = Uround($res_base_avg, 2);
//$query = "select count(birthday) from user_detailes where pid != 120 and retire_date is null and sid != 31";
$query = "select count(birthday) from user_detailes where pid != 120 and retire_date is null and sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826'";
//��°������ڤӤ���¾����������칩������
getUniResult($query, $res_base_count);
    ///// ��������                  ��Ĺ�����
//$query = "select avg(extract(years from age(birthday::timestamp))) as avg_years from user_detailes where pid != 120 and retire_date is null and sid != 31";
//��°������ڤӤ���¾����������칩������
$query = "select avg(extract(years from age(birthday::timestamp))) as avg_years from user_detailes where pid != 120 and retire_date is null and sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826'";
getUniResult($query, $res_today_avg);
$res_today_avg = Uround($res_today_avg, 2);

$res_base_avg  = number_format($res_base_avg, 2);
$res_today_avg = number_format($res_today_avg, 2);

/*** ľ����Ψ�Υ����꡼�ȷ׻� ***/
    ///// ľ������οͿ�
$select = "
    SELECT count(cd.uid)
    FROM act_table AS act
    LEFT OUTER JOIN cd_table AS cd USING(act_id)
    LEFT OUTER JOIN user_detailes AS u USING(uid)
";
$where = "
    WHERE act.act_flg = 't' and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $res_t_man);
    ///// ��������οͿ�
$where = "
    WHERE act.act_flg = 'f' and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $res_k_man);
    ///// �δ�������οͿ�
/*
$where = "
    WHERE act.act_flg = 'h'
";
*/
// �δ�������и��Ԥ����
$where = "
    WHERE act.act_flg = 'h' and u.sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $res_h_man);
    ///// ���ΤοͿ�
$all_man = $res_h_man + $res_k_man + $res_t_man;
    ///// ��¤����οͿ�
$sei_man = $res_k_man + $res_t_man;
    ///// ��������δ���οͿ�
$kan_man = $res_k_man + $res_h_man;
    ///// ���(��)�׻� �������о�
if ($all_man > 0) $tyoku_ritu = number_format(Uround($res_t_man / $all_man, 3) * 100, 1); else $tyoku_ritu = '0.0';
if ($all_man > 0) $kanse_ritu = number_format(Uround($kan_man / $all_man, 3) * 100, 1); else $kanse_ritu = '0.0';
    ///// ���(��)�׻� ��¤���������о�
if ($sei_man > 0) $direct_ritu = number_format(Uround($res_t_man / $sei_man, 3) * 100, 1); else $direct_ritu = '0.0';
if ($sei_man > 0) $kanset_ritu = number_format(Uround($res_k_man / $sei_man, 3) * 100, 1); else $kanset_ritu = '0.0';

/*** �嵭�����Ұ��ȥѡ���(����Х��Ȥ�ޤ�)��ʬ���� ***/
    ///// ľ������μҰ���(����=8�ޤ�)
$where = "
    WHERE act.act_flg = 't' AND u.pid >= 8 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $t_syain);
    ///// ľ������Υѡ��ȿ�(�ѡ��ȥ꡼����=7�ʲ�)
$where = "
    WHERE act.act_flg = 't' AND u.pid < 8 and retire_date is null
";
$query = $select . $where;
getUniResult($query, $t_part);
    ///// ��������μҰ���(����=8�ޤ�)
$where = "
    WHERE act.act_flg = 'f' AND u.pid >= 8 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $f_syain);
    ///// ��������Υѡ��ȿ�(�ѡ��ȥ꡼����=7�ʲ�)
$where = "
    WHERE act.act_flg = 'f' AND u.pid < 8 and retire_date is null
";
$query = $select . $where;
getUniResult($query, $f_part);
    ///// �δ�������μҰ���(����=8�ޤ�)
/*
$where = "
    WHERE act.act_flg = 'h' AND u.pid >= 8
";
*/
// �δ���������и��Ԥ����
$where = "
    WHERE act.act_flg = 'h' AND u.pid >= 8 and u.sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $h_syain);
    ///// �δ�������Υѡ��ȿ�(�ѡ��ȥ꡼����=7�ʲ�)
/*
$where = "
    WHERE act.act_flg = 'h' AND u.pid < 8
";
*/
// �δ���������и��Ԥ����
$where = "
    WHERE act.act_flg = 'h' AND u.pid < 8 and u.sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $h_part);
    ///// ���ΤμҰ���
$syain = $t_syain + $f_syain + $h_syain;
    ///// ���ΤΥѡ��ȿ�
$part  = $t_part + $f_part + $h_part;
    ///// ���Τν��Ȱ���
$zen = $syain + $part;

    ///// �Ұ������η׻�
    ///// ����Ұ�
$where = "
    WHERE u.pid = 9 and uid !='009504' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $kei_syain);
    ///// ����
$where = "
    WHERE u.pid = 8 and uid !='009504' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $syo_syain);
    ///// ����Х���
$where = "
    WHERE u.pid = 15 and uid !='009504' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $aru_syain);
    ///// ���Ұ�
$ss_syain = $syain - $kei_syain - $syo_syain - $aru_syain;

    ///// �Ұ������η׻�
    ///// �ѡ��ȥ����å�
$where = "
    WHERE u.pid < 8 and pid = 6 and retire_date is null
";
$query = $select . $where;
getUniResult($query, $staff_part);
    ///// �ѡ���
$i_part = $part - $staff_part;

// �����̼Ұ�������
// �ǡ��������
$president           = 0;
$factory_manager     = 0;
$sub_factory_manager = 0;
// ISO��̳�� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$iso_sya             = 0;
$iso_ps              = 0;
$iso_p               = 0;
$iso_k               = 0;
$iso_syo             = 0;
$iso_a               = 0;
// ������ �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$seisanbu_sya        = 0;
$seisanbu_ps         = 0;
$seisanbu_p          = 0;
$seisanbu_k          = 0;
$seisanbu_syo        = 0;
$seisanbu_a          = 0;
// C��Ω �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$c_kumi_sya          = 0;
$c_kumi_ps           = 0;
$c_kumi_p            = 0;
$c_kumi_k            = 0;
$c_kumi_syo          = 0;
$c_kumi_a            = 0;
// L��Ω �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$l_kumi_sya          = 0;
$l_kumi_ps           = 0;
$l_kumi_p            = 0;
$l_kumi_k            = 0;
$l_kumi_syo          = 0;
$l_kumi_a            = 0;
// ���� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$seikan_sya          = 0;
$seikan_ps           = 0;
$seikan_p            = 0;
$seikan_k            = 0;
$seikan_syo          = 0;
$seikan_a            = 0;
// ������ �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$gijyutbu_sya        = 0;
$gijyutbu_ps         = 0;
$gijyutbu_p          = 0;
$gijyutbu_k          = 0;
$gijyutbu_syo        = 0;
$gijyutbu_a          = 0;
// �ʾ� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$hin_sya             = 0;
$hin_ps              = 0;
$hin_p               = 0;
$hin_k               = 0;
$hin_syo             = 0;
$hin_a               = 0;
// ���� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$gi_sya              = 0;
$gi_ps               = 0;
$gi_p                = 0;
$gi_k                = 0;
$gi_syo              = 0;
$gi_a                = 0;
// ��¤�� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$seizobu_sya         = 0;
$seizobu_ps          = 0;
$seizobu_p           = 0;
$seizobu_k           = 0;
$seizobu_syo         = 0;
$seizobu_a           = 0;
// ��¤�� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$sei1_sya            = 0;
$sei1_ps             = 0;
$sei1_p              = 0;
$sei1_k              = 0;
$sei1_syo            = 0;
$sei1_a              = 0;
// ��¤�� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$sei2_sya            = 0;
$sei2_ps             = 0;
$sei2_p              = 0;
$sei2_k              = 0;
$sei2_syo            = 0;
$sei2_a              = 0;
// ������ �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$kanibu_sya          = 0;
$kanibu_ps           = 0;
$kanibu_p            = 0;
$kanibu_k            = 0;
$kanibu_syo          = 0;
$kanibu_a            = 0;
// ��̳ �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$soumu_sya           = 0;
$soumu_ps            = 0;
$soumu_p             = 0;
$soumu_k             = 0;
$soumu_syo           = 0;
$soumu_a             = 0;
// ���� �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$syokan_sya          = 0;
$syokan_ps           = 0;
$syokan_p            = 0;
$syokan_k            = 0;
$syokan_syo          = 0;
$syokan_a            = 0;
// ���(��) �Ұ� �ѡ��ȥ����å� �ѡ��� ����Ұ� ���� ����Х���
$total_sya           = 0;
$total_ps            = 0;
$total_p             = 0;
$total_k             = 0;
$total_syo           = 0;
$total_a             = 0;
// ���(��) ��Ĺ ����Ĺ ������Ĺ ISO��̳�� ������ C��Ω L��Ω ����
//          ������ �ʾ� ���� ��¤�� ��¤�� ��¤�� ������ ��̳ ���� ����
$total_pre           = 0;
$total_mana          = 0;
$total_smana         = 0;
$total_iso           = 0;
$total_seisanbu      = 0;
$total_ckumi         = 0;
$total_lkumi         = 0;
$total_seikan        = 0;
$total_gijyubu       = 0;
$total_hin           = 0;
$total_gijyuka       = 0;
$total_seizobu       = 0;
$total_seizo1        = 0;
$total_seizo2        = 0;
$total_kanri         = 0;
$total_soumu         = 0;
$total_syokan        = 0;
$total_all           = 0;

// �ǡ�������
// user_detailes ��Ĺ������Ĺ��������Ĺ�Ͽ���̾������pid�ΤߤǼ���
// ��Ĺ=120, ����Ĺ=110, ������Ĺ=95
// ¾�Ͻ�°������sid�ȿ���̾������pid��ʻ�Ѥ��Ƽ���
// �Ұ�(����,��ĹB,��ĹA,�������ѡ��ȣ�����,��Ĺ����,��Ĺ����,��Ĺ,����Ĺ,��Ĺ)=10,31,32,33,34,35,46,47,50,60,70
// �ѡ��ȥ����å�=6,�ѡ���=5,����Ұ�=9,����=8,����Х���=15
// ���� ISO=30��������=8,C��Ω=2,L��Ω=3,����=32��������=38,�ʾ�=18,����=4����¤��=17,��¤��=34,��¤��=35
//      ������=9,��̳=5,����=19

// ��Ĺ
$query = "select count(*) from user_detailes where pid=120 and retire_date is null";
getUniResult($query, $president);
// ����Ĺ
$query = "select count(*) from user_detailes where pid=110 and retire_date is null";
getUniResult($query, $factory_manager);
// ������Ĺ
$query = "select count(*) from user_detailes where pid=95 and retire_date is null";
getUniResult($query, $sub_factory_manager);


// ISO��̳��
// �Ұ�
$query = "select count(*) from user_detailes where sid=30 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $iso_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=30 and pid=6 and retire_date is null";
getUniResult($query, $iso_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=30 and pid=5 and retire_date is null";
getUniResult($query, $iso_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=30 and pid=9 and retire_date is null";
getUniResult($query, $iso_k);
// ����
$query = "select count(*) from user_detailes where sid=30 and pid=8 and retire_date is null";
getUniResult($query, $iso_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=30 and pid=15 and retire_date is null";
getUniResult($query, $iso_a);
// ���
$total_iso = $iso_sya + $iso_ps + $iso_p + $iso_k + $iso_syo + $iso_a;

// ������
// �Ұ�
$query = "select count(*) from user_detailes where sid=8 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $seisanbu_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=8 and pid=6 and retire_date is null";
getUniResult($query, $seisanbu_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=8 and pid=5 and retire_date is null";
getUniResult($query, $seisanbu_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=8 and pid=9 and retire_date is null";
getUniResult($query, $seisanbu_k);
// ����
$query = "select count(*) from user_detailes where sid=8 and pid=8 and retire_date is null";
getUniResult($query, $seisanbu_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=8 and pid=15 and retire_date is null";
getUniResult($query, $seisanbu_a);
// ���
$total_seisanbu = $seisanbu_sya + $seisanbu_ps + $seisanbu_p + $seisanbu_k + $seisanbu_syo + $seisanbu_a;

// C��Ω
// �Ұ�
$query = "select count(*) from user_detailes where sid=2 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $c_kumi_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=2 and pid=6 and retire_date is null";
getUniResult($query, $c_kumi_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=2 and pid=5 and retire_date is null";
getUniResult($query, $c_kumi_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=2 and pid=9 and retire_date is null";
getUniResult($query, $c_kumi_k);
// ����
$query = "select count(*) from user_detailes where sid=2 and pid=8 and retire_date is null";
getUniResult($query, $c_kumi_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=2 and pid=15 and retire_date is null";
getUniResult($query, $c_kumi_a);
// ���
$total_ckumi = $c_kumi_sya + $c_kumi_ps + $c_kumi_p + $c_kumi_k + $c_kumi_syo + $c_kumi_a;

// L��Ω
// �Ұ�
$query = "select count(*) from user_detailes where sid=3 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $l_kumi_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=3 and pid=6 and retire_date is null";
getUniResult($query, $l_kumi_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=3 and pid=5 and retire_date is null";
getUniResult($query, $l_kumi_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=3 and pid=9 and retire_date is null";
getUniResult($query, $l_kumi_k);
// ����
$query = "select count(*) from user_detailes where sid=3 and pid=8 and retire_date is null";
getUniResult($query, $l_kumi_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=3 and pid=15 and retire_date is null";
getUniResult($query, $l_kumi_a);
// ���
$total_lkumi = $l_kumi_sya + $l_kumi_ps + $l_kumi_p + $l_kumi_k + $l_kumi_syo + $l_kumi_a;

// ����
// �Ұ�
$query = "select count(*) from user_detailes where sid=32 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $seikan_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=32 and pid=6 and retire_date is null";
getUniResult($query, $seikan_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=32 and pid=5 and retire_date is null";
getUniResult($query, $seikan_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=32 and pid=9 and retire_date is null";
getUniResult($query, $seikan_k);
// ����
$query = "select count(*) from user_detailes where sid=32 and pid=8 and retire_date is null";
getUniResult($query, $seikan_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=32 and pid=15 and retire_date is null";
getUniResult($query, $seikan_a);
// ���
$total_seikan = $seikan_sya + $seikan_ps + $seikan_p + $seikan_k + $seikan_syo + $seikan_a;

// ������
// �Ұ�
$query = "select count(*) from user_detailes where sid=38 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $gijyutbu_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=38 and pid=6 and retire_date is null";
getUniResult($query, $gijyutbu_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=38 and pid=5 and retire_date is null";
getUniResult($query, $gijyutbu_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=38 and pid=9 and retire_date is null";
getUniResult($query, $gijyutbu_k);
// ����
$query = "select count(*) from user_detailes where sid=38 and pid=8 and retire_date is null";
getUniResult($query, $gijyutbu_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=38 and pid=15 and retire_date is null";
getUniResult($query, $gijyutbu_a);
// ���
$total_gijyutbu = $gijyutbu_sya + $gijyutbu_ps + $gijyutbu_p + $gijyutbu_k + $gijyutbu_syo + $gijyutbu_a;

// �ʾ�
// �Ұ�
// NK�и�����������ڤ����ޤ����
$query = "select count(*) from user_detailes where sid=18 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
// NK�и�����������ڤ����ޤ�ʤ����
//$query = "select count(*) from user_detailes where sid=18 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $hin_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=18 and pid=6 and retire_date is null";
getUniResult($query, $hin_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=18 and pid=5 and retire_date is null";
getUniResult($query, $hin_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=18 and pid=9 and retire_date is null";
getUniResult($query, $hin_k);
// ����
$query = "select count(*) from user_detailes where sid=18 and pid=8 and retire_date is null";
getUniResult($query, $hin_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=18 and pid=15 and retire_date is null";
getUniResult($query, $hin_a);
// ���
$total_hin = $hin_sya + $hin_ps + $hin_p + $hin_k + $hin_syo + $hin_a;

// ���Ѳ�
// �Ұ�
$query = "select count(*) from user_detailes where sid=4 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $gi_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=4 and pid=6 and retire_date is null";
getUniResult($query, $gi_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=4 and pid=5 and retire_date is null";
getUniResult($query, $gi_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=4 and pid=9 and retire_date is null";
getUniResult($query, $gi_k);
// ����
$query = "select count(*) from user_detailes where sid=4 and pid=8 and retire_date is null";
getUniResult($query, $gi_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=4 and pid=15 and retire_date is null";
getUniResult($query, $gi_a);
// ���
$total_gi = $gi_sya + $gi_ps + $gi_p + $gi_k + $gi_syo + $gi_a;

// ��¤��
// �Ұ�
$query = "select count(*) from user_detailes where sid=17 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $seizobu_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=17 and pid=6 and retire_date is null";
getUniResult($query, $seizobu_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=17 and pid=5 and retire_date is null";
getUniResult($query, $seizobu_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=17 and pid=9 and retire_date is null";
getUniResult($query, $seizobu_k);
// ����
$query = "select count(*) from user_detailes where sid=17 and pid=8 and retire_date is null";
getUniResult($query, $seizobu_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=17 and pid=15 and retire_date is null";
getUniResult($query, $seizobu_a);
// ���
$total_seizobu = $seizobu_sya + $seizobu_ps + $seizobu_p + $seizobu_k + $seizobu_syo + $seizobu_a;

// ��¤��
// �Ұ�
$query = "select count(*) from user_detailes where sid=34 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $sei1_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=34 and pid=6 and retire_date is null";
getUniResult($query, $sei1_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=34 and pid=5 and retire_date is null";
getUniResult($query, $sei1_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=34 and pid=9 and retire_date is null";
getUniResult($query, $sei1_k);
// ����
$query = "select count(*) from user_detailes where sid=34 and pid=8 and retire_date is null";
getUniResult($query, $sei1_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=34 and pid=15 and retire_date is null";
getUniResult($query, $sei1_a);
// ���
$total_sei1 = $sei1_sya + $sei1_ps + $sei1_p + $sei1_k + $sei1_syo + $sei1_a;+

// ��¤��
// �Ұ�
$query = "select count(*) from user_detailes where sid=35 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $sei2_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=35 and pid=6 and retire_date is null";
getUniResult($query, $sei2_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=35 and pid=5 and retire_date is null";
getUniResult($query, $sei2_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=35 and pid=9 and retire_date is null";
getUniResult($query, $sei2_k);
// ����
$query = "select count(*) from user_detailes where sid=35 and pid=8 and retire_date is null";
getUniResult($query, $sei2_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=35 and pid=15 and retire_date is null";
getUniResult($query, $sei2_a);
// ���
$total_sei2 = $sei2_sya + $sei2_ps + $sei2_p + $sei2_k + $sei2_syo + $sei2_a;+

// ������
// �Ұ�
$query = "select count(*) from user_detailes where sid=9 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $kanribu_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=9 and pid=6 and retire_date is null";
getUniResult($query, $kanribu_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=9 and pid=5 and retire_date is null";
getUniResult($query, $kanribu_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=9 and pid=9 and retire_date is null";
getUniResult($query, $kanribu_k);
// ����
$query = "select count(*) from user_detailes where sid=9 and pid=8 and retire_date is null";
getUniResult($query, $kanribu_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=9 and pid=15 and retire_date is null";
getUniResult($query, $kanribu_a);
// ���
$total_kanribu = $kanribu_sya + $kanribu_ps + $kanribu_p + $kanribu_k + $kanribu_syo + $kanribu_a;+

// ��̳
// �Ұ�
$query = "select count(*) from user_detailes where sid=5 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $soumu_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=5 and pid=6 and retire_date is null";
getUniResult($query, $soumu_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=5 and pid=5 and retire_date is null";
getUniResult($query, $soumu_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=5 and pid=9 and retire_date is null";
getUniResult($query, $soumu_k);
// ����
$query = "select count(*) from user_detailes where sid=5 and pid=8 and retire_date is null";
getUniResult($query, $soumu_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=5 and pid=15 and retire_date is null";
getUniResult($query, $soumu_a);
// ���
$total_soumu = $soumu_sya + $soumu_ps + $soumu_p + $soumu_k + $soumu_syo + $soumu_a;+

// ��̳
// �Ұ�
$query = "select count(*) from user_detailes where sid=19 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $syokan_sya);
// �ѡ��ȥ����å�
$query = "select count(*) from user_detailes where sid=19 and pid=6 and retire_date is null";
getUniResult($query, $syokan_ps);
// �ѡ���
$query = "select count(*) from user_detailes where sid=19 and pid=5 and retire_date is null";
getUniResult($query, $syokan_p);
// ����Ұ�
$query = "select count(*) from user_detailes where sid=19 and pid=9 and retire_date is null";
getUniResult($query, $syokan_k);
// ����
$query = "select count(*) from user_detailes where sid=19 and pid=8 and retire_date is null";
getUniResult($query, $syokan_syo);
// ����Х���
$query = "select count(*) from user_detailes where sid=19 and pid=15 and retire_date is null";
getUniResult($query, $syokan_a);
// ���
$total_syokan = $syokan_sya + $syokan_ps + $syokan_p + $syokan_k + $syokan_syo + $syokan_a;+

// ����׷׻�
$total_sya           = $president + $factory_manager + $sub_factory_manager + $iso_sya + $seisanbu_sya + $c_kumi_sya + $l_kumi_sya + $seikan_sya + $gijyutbu_sya + $hin_sya + $gi_sya + $seizobu_sya + $sei1_sya + $sei2_sya + $kanribu_sya + $soumu_sya + $syokan_sya;
$total_ps            = $iso_ps + $seisanbu_ps + $c_kumi_ps + $l_kumi_ps + $seikan_ps + $gijyutbu_ps + $hin_ps + $gi_ps + $seizobu_ps + $sei1_ps + $sei2_ps + $kanribu_ps + $soumu_ps + $syokan_ps;
$total_p             = $iso_p + $seisanbu_p + $c_kumi_p + $l_kumi_p + $seikan_p + $gijyutbu_p + $hin_p + $gi_p + $seizobu_p + $sei1_p + $sei2_p + $kanribu_p + $soumu_p + $syokan_p;
$total_k             = $iso_k + $seisanbu_k + $c_kumi_k + $l_kumi_k + $seikan_k + $gijyutbu_k + $hin_k + $gi_k + $seizobu_k + $sei1_k + $sei2_k + $kanribu_k + $soumu_k + $syokan_k;
$total_syo           = $iso_syo + $seisanbu_syo + $c_kumi_syo + $l_kumi_syo + $seikan_syo + $gijyutbu_syo + $hin_syo + $gi_syo + $seizobu_syo + $sei1_syo + $sei2_syo + $kanribu_syo + $soumu_syo + $syokan_syo;
$total_a             = $iso_a + $seisanbu_a + $c_kumi_a + $l_kumi_a + $seikan_a + $gijyutbu_a + $hin_a + $gi_a + $seizobu_a + $sei1_a + $sei2_a + $kanribu_a + $soumu_a + $syokan_a;

// ���׷׻�
$total_all = $total_sya + $total_ps + $total_p + $total_k + $total_syo + $total_a;


if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '011061') {
?>
<BR>
<table bgcolor='white' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='white' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            
            <?php
            // �ţģ���ݶ�׾������ 2����ɽ���ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='3'><span class='pt9'>��</span></th>\n";
            echo "<th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='17'><span class='pt9'>���ҽ��Ȱ���<font color='red'>����Ĺ�������и��Դޤ�</font></span></th>\n";
            echo "<th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='3'><span class='pt9'>��<BR>��</span></th>\n";
            echo "</tr>\n";
            
            // ���磱
            echo "<tr>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>��<BR>Ĺ</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>��<BR>��<BR>Ĺ</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>��<BR>����<BR>Ĺ</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>ISO<BR>��̳<BR>��</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='4'><span class='pt9'>������</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='3'><span class='pt9'>������</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='3'><span class='pt9'>��¤��</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='3'><span class='pt9'>������</span></td>\n";
            echo "</tr>\n";
            
            // ���磲
            echo "<tr>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>����<BR>��</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��<BR>��Ω</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��<BR>��Ω</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��<BR>��</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>����<BR>��</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��<BR>��</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��<BR>��</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��¤<BR>��</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��¤<BR>��</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��¤<BR>��</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>����<BR>��</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowra bgcolor='#F0FFFF'p align='center'><span class='pt9'>��<BR>̳</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>��<BR>��</span></td>\n";
            echo "</tr>\n";
            
            // ���Ұ�
            echo "<tr>\n";
            // �����ȥ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>���Ұ�</span></td>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($president) . "</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($factory_manager) . "</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sub_factory_manager) . "</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_sya) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_sya) . "</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_sya) . "</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_sya) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_sya) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_sya) . "</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_sya) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_sya) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_sya) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_sya) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_sya) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_sya) . "</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_sya) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_sya) . "</span></td>\n";
            // ���
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_sya) . "</span></td>\n";
            echo "</tr>\n";
           
            // �ѡ��ȥ����å�
            echo "<tr>\n";
            // �����ȥ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>�ѡ���<BR>�����å�</span></td>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_ps) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_ps) . "</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_ps) . "</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_ps) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_ps) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_ps) . "</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_ps) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_ps) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_ps) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_ps) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_ps) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_ps) . "</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_ps) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_ps) . "</span></td>\n";
            // ���
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_ps) . "</span></td>\n";
            echo "</tr>\n";
            
            // �ѡ���
            echo "<tr>\n";
            // �����ȥ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>�ѡ���</span></td>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_p) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_p) . "</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_p) . "</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_p) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_p) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_p) . "</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_p) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_p) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_p) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_p) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_p) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_p) . "</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_p) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_p) . "</span></td>\n";
            // ���
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_p) . "</span></td>\n";
            echo "</tr>\n";
            
            // ����Ұ�
            echo "<tr>\n";
            // �����ȥ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>����<BR>�Ұ�</span></td>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_k) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_k) . "</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_k) . "</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_k) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_k) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_k) . "</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_k) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_k) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_k) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_k) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_k) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_k) . "</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_k) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_k) . "</span></td>\n";
            // ���
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_k) . "</span></td>\n";
            echo "</tr>\n";
            
            // ����
            echo "<tr>\n";
            // �����ȥ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>����</span></td>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_syo) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_syo) . "</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_syo) . "</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_syo) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_syo) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_syo) . "</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_syo) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_syo) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_syo) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_syo) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_syo) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_syo) . "</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_syo) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_syo) . "</span></td>\n";
            // ���
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_syo) . "</span></td>\n";
            echo "</tr>\n";
            
            // ����Х���
            echo "<tr>\n";
            // �����ȥ�
            echo "  <th class='winbox' nowra bgcolor='#F0FFFF'p align='right'><span class='pt9'>����<BR>�Х���</span></td>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_a) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_a) . "</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_a) . "</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_a) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_a) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_a) . "</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_a) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_a) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_a) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_a) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_a) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_a) . "</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_a) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_a) . "</span></td>\n";
            // ���
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_a) . "</span></td>\n";
            echo "</tr>\n";
            
            // ���
            echo "<tr>\n";
            // �����ȥ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>���</span></td>\n";
            // ��Ĺ
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($president) . "</span></td>\n";
            // ����Ĺ
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($factory_manager) . "</span></td>\n";
            // ������Ĺ
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($sub_factory_manager) . "</span></td>\n";
            // ISO��̳��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_iso) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_seisanbu) . "</span></td>\n";
            // C��Ω
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_ckumi) . "</span></td>\n";
            // L��Ω
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_lkumi) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_seikan) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_gijyutbu) . "</span></td>\n";
            // �ʾ�
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_hin) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_gi) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_seizobu) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_sei1) . "</span></td>\n";
            // ��¤��
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_sei2) . "</span></td>\n";
            // ������
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_kanribu) . "</span></td>\n";
            // ��̳
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_soumu) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_syokan) . "</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_all) . "</span></td>\n";
            echo "</tr>\n";
            ?>
            
            </tbody>
        </table>
        <BR>
<?php
}
?>
<table width='100%'>
    <tr><td colspan='2' bgcolor='#003e7c' align='center' class='nasiji'>
        <font color='#ffffff'>���Ȱ� ���� ���� ɽ��</font></td>
    </tr>
    <tr><td valign='top'>
        <font color='#ff7e00'><b>1.</b></font>ʿ��ǯ��
        <hr>
        <table width='100%'>
            <tr>
                <td>��Ĺ���и��Ԥ���������Ȱ���ʿ��ǯ��(<?php echo $base_date ?>���)</td>
                <td width='20%' align='right'><?php echo $res_base_avg ?>�С�<?php echo $res_base_count ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>��Ĺ���и��Ԥ���������Ȱ���ʿ��ǯ��(�������ߴ��)</td>
                <td width='20%' align='right'><?php echo $res_today_avg ?>�С�<?php echo $res_base_count ?>��</td>
            </tr>
        </table>
        <hr>
        <font color='#ff7e00'><b>2.</b></font>ľ����Ψ
        <hr>
        <table width='100%'>
            <tr>
                <td>��Ĺ���и��Ԥ���������Ȱ��о� ľ������ �� (����������δ�������)</td>
                <td width='20%' align='right'><?php echo $tyoku_ritu ?>�� �� <?php echo $kanse_ritu ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td colspan='2' width='100%' align='right'><?php echo $res_t_man ?>�� �� <?php echo $kan_man ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>��¤����������оݤ� ��ľ������ �� ��������</td>
                <td width='20%' align='right'><?php echo $direct_ritu ?>�� �� <?php echo $kanset_ritu ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td colspan='2' width='100%' align='right'><?php echo $res_t_man ?>�� �� <?php echo $res_k_man ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td colspan='2' width='100%' align='right'>�δ�������οͿ���<?php echo $res_h_man ?>��</td>
            </tr>
        </table>
        <hr>
        <font color='#ff7e00'><b>3.</b></font>�Ұ��ȥѡ��ȤοͿ���(��Ĺ�Ƚи��Խ���)
        <hr>
        <table width='100%'>
            <tr>
                <td>���� ���Ȱ���</td>
                <td width='20%' align='right'><?php echo $zen ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>���� �Ұ������ѡ��ȿ�</td>
                <td width='20%' align='right'><?php echo $syain, '��', $part ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>�Ұ����� ���Ұ�������Ұ�������������Х���</td>
                <td width='20%' align='right'><?php echo $ss_syain, '��', $kei_syain, '��', $syo_syain, '��', $aru_syain ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>�ѡ������� �ѡ��ȥ����åա��ѡ���</td>
                <td width='20%' align='right'><?php echo $staff_part, '��', $i_part ?>��</td>
            </tr>
        </table>
        <hr>
        <table width='100%'>
            <tr>
                <td>ľ������ �Ұ������ѡ��ȿ�</td>
                <td width='20%' align='right'><?php echo $t_syain, '��', $t_part ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>�������� �Ұ������ѡ��ȿ�</td>
                <td width='20%' align='right'><?php echo $f_syain, '��', $f_part ?>��</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>�δ������� �Ұ������ѡ��ȿ�</td>
                <td width='20%' align='right'><?php echo $h_syain, '��', $h_part ?>��</td>
            </tr>
        </table>
        <hr>
    </td>
    </tr>
</table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
