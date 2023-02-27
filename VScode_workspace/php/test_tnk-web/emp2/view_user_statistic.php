<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の emp_menu.php の include file 従業員の統計情報表示         //
// Copyright (C) 2003-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/04/23 Created  view_user_statistic.php とりあえず平均年齢のみ       //
// 2003/04/25 直間比率を追加 直接部門：(間接部門＋販管費部門) 直接間接      //
// 2004/02/06 基準年齢がなぜかdate('-m-d')になっているのを'-04-01'へ修正    //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2005/01/26 統計情報で division by zero check を追加                      //
//            background-imageを追加してデザイン変更(AM/PMで切替式)         //
// 2006/01/11 社員統計情報に社員数とパート数を追加                          //
// 2016/08/26 人数計算方法を現在の状況に合わせて変更                   大谷 //
// 2017/09/13 日東工器部門（sid=95）を計算から除外する様、修正         大谷 //
// 2021/07/12 社員数の表を追加。統計情報で受け入れ出向者など                //
//            余計な人員が含まれているのを除外                         大谷 //
//////////////////////////////////////////////////////////////////////////////
// access_log('view_user_statistic.php');        // Script Name 手動設定
//access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
//echo view_file_name(__FILE__);
require_once ('../tnk_func.php');
require_once ('../function.php');        // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');      // TNK 全共通 menu class
require_once ('emp_function.php');          // 社員メニュー専用
require_once ('../ControllerHTTP_Class.php');   // TNK 全共通 MVC Controller Class
if (isset($_POST['offset'])) {
    $offset = $_POST['offset'];
} else {
    $offset = 0;
}
/*** クエリーを生成 & 実行 ***/
    ///// 基準日現在(YYYY-04-01)    社長 退職者 出向者 を除く
if (date('m') <= 3) {
    $yyyy = (date('Y') - 1);
    // $base_date = ($yyyy . date('-m-d'));
    $base_date = ($yyyy . '-04-01');
} else {
    $base_date = (date('Y') . '-04-01');
}
$query = sprintf("select avg(extract(years from age('%s'::timestamp, (birthday)::timestamp))) as avg_years from user_detailes where pid != 120 and retire_date is null and sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826'", $base_date);
//所属：顧問及びその他も除外、日東工器も除外
getUniResult($query, $res_base_avg);
$res_base_avg = Uround($res_base_avg, 2);
//$query = "select count(birthday) from user_detailes where pid != 120 and retire_date is null and sid != 31";
$query = "select count(birthday) from user_detailes where pid != 120 and retire_date is null and sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826'";
//所属：顧問及びその他も除外、日東工器も除外
getUniResult($query, $res_base_count);
    ///// 本日現在                  社長を除く
//$query = "select avg(extract(years from age(birthday::timestamp))) as avg_years from user_detailes where pid != 120 and retire_date is null and sid != 31";
//所属：顧問及びその他も除外、日東工器も除外
$query = "select avg(extract(years from age(birthday::timestamp))) as avg_years from user_detailes where pid != 120 and retire_date is null and sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826'";
getUniResult($query, $res_today_avg);
$res_today_avg = Uround($res_today_avg, 2);

$res_base_avg  = number_format($res_base_avg, 2);
$res_today_avg = number_format($res_today_avg, 2);

/*** 直間比率のクエリーと計算 ***/
    ///// 直接部門の人数
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
    ///// 間接部門の人数
$where = "
    WHERE act.act_flg = 'f' and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $res_k_man);
    ///// 販管費部門の人数
/*
$where = "
    WHERE act.act_flg = 'h'
";
*/
// 販管部門より出向者を除く
$where = "
    WHERE act.act_flg = 'h' and u.sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $res_h_man);
    ///// 全体の人数
$all_man = $res_h_man + $res_k_man + $res_t_man;
    ///// 製造経費の人数
$sei_man = $res_k_man + $res_t_man;
    ///// 間接費と販管費の人数
$kan_man = $res_k_man + $res_h_man;
    ///// 割合(％)計算 全部門対象
if ($all_man > 0) $tyoku_ritu = number_format(Uround($res_t_man / $all_man, 3) * 100, 1); else $tyoku_ritu = '0.0';
if ($all_man > 0) $kanse_ritu = number_format(Uround($kan_man / $all_man, 3) * 100, 1); else $kanse_ritu = '0.0';
    ///// 割合(％)計算 製造経費部門対象
if ($sei_man > 0) $direct_ritu = number_format(Uround($res_t_man / $sei_man, 3) * 100, 1); else $direct_ritu = '0.0';
if ($sei_man > 0) $kanset_ritu = number_format(Uround($res_k_man / $sei_man, 3) * 100, 1); else $kanset_ritu = '0.0';

/*** 上記を正社員とパート(アルバイトを含む)に分ける ***/
    ///// 直接部門の社員数(嘱託=8含む)
$where = "
    WHERE act.act_flg = 't' AND u.pid >= 8 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $t_syain);
    ///// 直接部門のパート数(パートリーダー=7以下)
$where = "
    WHERE act.act_flg = 't' AND u.pid < 8 and retire_date is null
";
$query = $select . $where;
getUniResult($query, $t_part);
    ///// 間接部門の社員数(嘱託=8含む)
$where = "
    WHERE act.act_flg = 'f' AND u.pid >= 8 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $f_syain);
    ///// 間接部門のパート数(パートリーダー=7以下)
$where = "
    WHERE act.act_flg = 'f' AND u.pid < 8 and retire_date is null
";
$query = $select . $where;
getUniResult($query, $f_part);
    ///// 販管費部門の社員数(嘱託=8含む)
/*
$where = "
    WHERE act.act_flg = 'h' AND u.pid >= 8
";
*/
// 販管費部門より出向者を除く
$where = "
    WHERE act.act_flg = 'h' AND u.pid >= 8 and u.sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $h_syain);
    ///// 販管費部門のパート数(パートリーダー=7以下)
/*
$where = "
    WHERE act.act_flg = 'h' AND u.pid < 8
";
*/
// 販管費部門より出向者を除く
$where = "
    WHERE act.act_flg = 'h' AND u.pid < 8 and u.sid != 31 and sid != 80 and sid != 90 and sid != 95 and uid !='023856' and uid !='020826' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $h_part);
    ///// 全体の社員数
$syain = $t_syain + $f_syain + $h_syain;
    ///// 全体のパート数
$part  = $t_part + $f_part + $h_part;
    ///// 全体の従業員数
$zen = $syain + $part;

    ///// 社員内訳の計算
    ///// 契約社員
$where = "
    WHERE u.pid = 9 and uid !='009504' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $kei_syain);
    ///// 嘱託
$where = "
    WHERE u.pid = 8 and uid !='009504' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $syo_syain);
    ///// アルバイト
$where = "
    WHERE u.pid = 15 and uid !='009504' and retire_date is null
";
$query = $select . $where;
getUniResult($query, $aru_syain);
    ///// 正社員
$ss_syain = $syain - $kei_syain - $syo_syain - $aru_syain;

    ///// 社員内訳の計算
    ///// パートスタッフ
$where = "
    WHERE u.pid < 8 and pid = 6 and retire_date is null
";
$query = $select . $where;
getUniResult($query, $staff_part);
    ///// パート
$i_part = $part - $staff_part;

// 部門別社員数取得
// データ初期化
$president           = 0;
$factory_manager     = 0;
$sub_factory_manager = 0;
// ISO事務局 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$iso_sya             = 0;
$iso_ps              = 0;
$iso_p               = 0;
$iso_k               = 0;
$iso_syo             = 0;
$iso_a               = 0;
// 生産部 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$seisanbu_sya        = 0;
$seisanbu_ps         = 0;
$seisanbu_p          = 0;
$seisanbu_k          = 0;
$seisanbu_syo        = 0;
$seisanbu_a          = 0;
// C組立 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$c_kumi_sya          = 0;
$c_kumi_ps           = 0;
$c_kumi_p            = 0;
$c_kumi_k            = 0;
$c_kumi_syo          = 0;
$c_kumi_a            = 0;
// L組立 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$l_kumi_sya          = 0;
$l_kumi_ps           = 0;
$l_kumi_p            = 0;
$l_kumi_k            = 0;
$l_kumi_syo          = 0;
$l_kumi_a            = 0;
// 生管 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$seikan_sya          = 0;
$seikan_ps           = 0;
$seikan_p            = 0;
$seikan_k            = 0;
$seikan_syo          = 0;
$seikan_a            = 0;
// 技術部 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$gijyutbu_sya        = 0;
$gijyutbu_ps         = 0;
$gijyutbu_p          = 0;
$gijyutbu_k          = 0;
$gijyutbu_syo        = 0;
$gijyutbu_a          = 0;
// 品証 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$hin_sya             = 0;
$hin_ps              = 0;
$hin_p               = 0;
$hin_k               = 0;
$hin_syo             = 0;
$hin_a               = 0;
// 技術 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$gi_sya              = 0;
$gi_ps               = 0;
$gi_p                = 0;
$gi_k                = 0;
$gi_syo              = 0;
$gi_a                = 0;
// 製造部 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$seizobu_sya         = 0;
$seizobu_ps          = 0;
$seizobu_p           = 0;
$seizobu_k           = 0;
$seizobu_syo         = 0;
$seizobu_a           = 0;
// 製造１ 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$sei1_sya            = 0;
$sei1_ps             = 0;
$sei1_p              = 0;
$sei1_k              = 0;
$sei1_syo            = 0;
$sei1_a              = 0;
// 製造２ 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$sei2_sya            = 0;
$sei2_ps             = 0;
$sei2_p              = 0;
$sei2_k              = 0;
$sei2_syo            = 0;
$sei2_a              = 0;
// 管理部 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$kanibu_sya          = 0;
$kanibu_ps           = 0;
$kanibu_p            = 0;
$kanibu_k            = 0;
$kanibu_syo          = 0;
$kanibu_a            = 0;
// 総務 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$soumu_sya           = 0;
$soumu_ps            = 0;
$soumu_p             = 0;
$soumu_k             = 0;
$soumu_syo           = 0;
$soumu_a             = 0;
// 商管 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$syokan_sya          = 0;
$syokan_ps           = 0;
$syokan_p            = 0;
$syokan_k            = 0;
$syokan_syo          = 0;
$syokan_a            = 0;
// 合計(横) 社員 パートスタッフ パート 契約社員 嘱託 アルバイト
$total_sya           = 0;
$total_ps            = 0;
$total_p             = 0;
$total_k             = 0;
$total_syo           = 0;
$total_a             = 0;
// 合計(縦) 社長 工場長 副工場長 ISO事務局 生産部 C組立 L組立 生管
//          技術部 品証 技術 製造部 製造１ 製造２ 管理部 総務 商管 総合計
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

// データ取得
// user_detailes 社長・工場長・副工場長は職位名コードpidのみで取得
// 社長=120, 工場長=110, 副工場長=95
// 他は所属コードsidと職位名コードpidと併用して取得
// 社員(一般,係長B,係長A,エキスパート３～１,課長代理,部長代理,課長,副部長,部長)=10,31,32,33,34,35,46,47,50,60,70
// パートスタッフ=6,パート=5,契約社員=9,嘱託=8,アルバイト=15
// 部門 ISO=30、生産部=8,C組立=2,L組立=3,生管=32、技術部=38,品証=18,技術=4、製造部=17,製造１=34,製造２=35
//      管理部=9,総務=5,商管=19

// 社長
$query = "select count(*) from user_detailes where pid=120 and retire_date is null";
getUniResult($query, $president);
// 工場長
$query = "select count(*) from user_detailes where pid=110 and retire_date is null";
getUniResult($query, $factory_manager);
// 副工場長
$query = "select count(*) from user_detailes where pid=95 and retire_date is null";
getUniResult($query, $sub_factory_manager);


// ISO事務局
// 社員
$query = "select count(*) from user_detailes where sid=30 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $iso_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=30 and pid=6 and retire_date is null";
getUniResult($query, $iso_ps);
// パート
$query = "select count(*) from user_detailes where sid=30 and pid=5 and retire_date is null";
getUniResult($query, $iso_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=30 and pid=9 and retire_date is null";
getUniResult($query, $iso_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=30 and pid=8 and retire_date is null";
getUniResult($query, $iso_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=30 and pid=15 and retire_date is null";
getUniResult($query, $iso_a);
// 合計
$total_iso = $iso_sya + $iso_ps + $iso_p + $iso_k + $iso_syo + $iso_a;

// 生産部
// 社員
$query = "select count(*) from user_detailes where sid=8 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $seisanbu_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=8 and pid=6 and retire_date is null";
getUniResult($query, $seisanbu_ps);
// パート
$query = "select count(*) from user_detailes where sid=8 and pid=5 and retire_date is null";
getUniResult($query, $seisanbu_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=8 and pid=9 and retire_date is null";
getUniResult($query, $seisanbu_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=8 and pid=8 and retire_date is null";
getUniResult($query, $seisanbu_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=8 and pid=15 and retire_date is null";
getUniResult($query, $seisanbu_a);
// 合計
$total_seisanbu = $seisanbu_sya + $seisanbu_ps + $seisanbu_p + $seisanbu_k + $seisanbu_syo + $seisanbu_a;

// C組立
// 社員
$query = "select count(*) from user_detailes where sid=2 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $c_kumi_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=2 and pid=6 and retire_date is null";
getUniResult($query, $c_kumi_ps);
// パート
$query = "select count(*) from user_detailes where sid=2 and pid=5 and retire_date is null";
getUniResult($query, $c_kumi_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=2 and pid=9 and retire_date is null";
getUniResult($query, $c_kumi_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=2 and pid=8 and retire_date is null";
getUniResult($query, $c_kumi_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=2 and pid=15 and retire_date is null";
getUniResult($query, $c_kumi_a);
// 合計
$total_ckumi = $c_kumi_sya + $c_kumi_ps + $c_kumi_p + $c_kumi_k + $c_kumi_syo + $c_kumi_a;

// L組立
// 社員
$query = "select count(*) from user_detailes where sid=3 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $l_kumi_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=3 and pid=6 and retire_date is null";
getUniResult($query, $l_kumi_ps);
// パート
$query = "select count(*) from user_detailes where sid=3 and pid=5 and retire_date is null";
getUniResult($query, $l_kumi_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=3 and pid=9 and retire_date is null";
getUniResult($query, $l_kumi_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=3 and pid=8 and retire_date is null";
getUniResult($query, $l_kumi_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=3 and pid=15 and retire_date is null";
getUniResult($query, $l_kumi_a);
// 合計
$total_lkumi = $l_kumi_sya + $l_kumi_ps + $l_kumi_p + $l_kumi_k + $l_kumi_syo + $l_kumi_a;

// 生管
// 社員
$query = "select count(*) from user_detailes where sid=32 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $seikan_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=32 and pid=6 and retire_date is null";
getUniResult($query, $seikan_ps);
// パート
$query = "select count(*) from user_detailes where sid=32 and pid=5 and retire_date is null";
getUniResult($query, $seikan_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=32 and pid=9 and retire_date is null";
getUniResult($query, $seikan_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=32 and pid=8 and retire_date is null";
getUniResult($query, $seikan_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=32 and pid=15 and retire_date is null";
getUniResult($query, $seikan_a);
// 合計
$total_seikan = $seikan_sya + $seikan_ps + $seikan_p + $seikan_k + $seikan_syo + $seikan_a;

// 技術部
// 社員
$query = "select count(*) from user_detailes where sid=38 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
getUniResult($query, $gijyutbu_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=38 and pid=6 and retire_date is null";
getUniResult($query, $gijyutbu_ps);
// パート
$query = "select count(*) from user_detailes where sid=38 and pid=5 and retire_date is null";
getUniResult($query, $gijyutbu_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=38 and pid=9 and retire_date is null";
getUniResult($query, $gijyutbu_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=38 and pid=8 and retire_date is null";
getUniResult($query, $gijyutbu_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=38 and pid=15 and retire_date is null";
getUniResult($query, $gijyutbu_a);
// 合計
$total_gijyutbu = $gijyutbu_sya + $gijyutbu_ps + $gijyutbu_p + $gijyutbu_k + $gijyutbu_syo + $gijyutbu_a;

// 品証
// 社員
// NK出向受け入れ高木さんを含める場合
$query = "select count(*) from user_detailes where sid=18 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null";
// NK出向受け入れ高木さんを含めない場合
//$query = "select count(*) from user_detailes where sid=18 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $hin_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=18 and pid=6 and retire_date is null";
getUniResult($query, $hin_ps);
// パート
$query = "select count(*) from user_detailes where sid=18 and pid=5 and retire_date is null";
getUniResult($query, $hin_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=18 and pid=9 and retire_date is null";
getUniResult($query, $hin_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=18 and pid=8 and retire_date is null";
getUniResult($query, $hin_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=18 and pid=15 and retire_date is null";
getUniResult($query, $hin_a);
// 合計
$total_hin = $hin_sya + $hin_ps + $hin_p + $hin_k + $hin_syo + $hin_a;

// 技術課
// 社員
$query = "select count(*) from user_detailes where sid=4 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $gi_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=4 and pid=6 and retire_date is null";
getUniResult($query, $gi_ps);
// パート
$query = "select count(*) from user_detailes where sid=4 and pid=5 and retire_date is null";
getUniResult($query, $gi_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=4 and pid=9 and retire_date is null";
getUniResult($query, $gi_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=4 and pid=8 and retire_date is null";
getUniResult($query, $gi_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=4 and pid=15 and retire_date is null";
getUniResult($query, $gi_a);
// 合計
$total_gi = $gi_sya + $gi_ps + $gi_p + $gi_k + $gi_syo + $gi_a;

// 製造部
// 社員
$query = "select count(*) from user_detailes where sid=17 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $seizobu_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=17 and pid=6 and retire_date is null";
getUniResult($query, $seizobu_ps);
// パート
$query = "select count(*) from user_detailes where sid=17 and pid=5 and retire_date is null";
getUniResult($query, $seizobu_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=17 and pid=9 and retire_date is null";
getUniResult($query, $seizobu_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=17 and pid=8 and retire_date is null";
getUniResult($query, $seizobu_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=17 and pid=15 and retire_date is null";
getUniResult($query, $seizobu_a);
// 合計
$total_seizobu = $seizobu_sya + $seizobu_ps + $seizobu_p + $seizobu_k + $seizobu_syo + $seizobu_a;

// 製造１
// 社員
$query = "select count(*) from user_detailes where sid=34 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $sei1_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=34 and pid=6 and retire_date is null";
getUniResult($query, $sei1_ps);
// パート
$query = "select count(*) from user_detailes where sid=34 and pid=5 and retire_date is null";
getUniResult($query, $sei1_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=34 and pid=9 and retire_date is null";
getUniResult($query, $sei1_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=34 and pid=8 and retire_date is null";
getUniResult($query, $sei1_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=34 and pid=15 and retire_date is null";
getUniResult($query, $sei1_a);
// 合計
$total_sei1 = $sei1_sya + $sei1_ps + $sei1_p + $sei1_k + $sei1_syo + $sei1_a;+

// 製造２
// 社員
$query = "select count(*) from user_detailes where sid=35 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $sei2_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=35 and pid=6 and retire_date is null";
getUniResult($query, $sei2_ps);
// パート
$query = "select count(*) from user_detailes where sid=35 and pid=5 and retire_date is null";
getUniResult($query, $sei2_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=35 and pid=9 and retire_date is null";
getUniResult($query, $sei2_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=35 and pid=8 and retire_date is null";
getUniResult($query, $sei2_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=35 and pid=15 and retire_date is null";
getUniResult($query, $sei2_a);
// 合計
$total_sei2 = $sei2_sya + $sei2_ps + $sei2_p + $sei2_k + $sei2_syo + $sei2_a;+

// 管理部
// 社員
$query = "select count(*) from user_detailes where sid=9 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $kanribu_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=9 and pid=6 and retire_date is null";
getUniResult($query, $kanribu_ps);
// パート
$query = "select count(*) from user_detailes where sid=9 and pid=5 and retire_date is null";
getUniResult($query, $kanribu_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=9 and pid=9 and retire_date is null";
getUniResult($query, $kanribu_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=9 and pid=8 and retire_date is null";
getUniResult($query, $kanribu_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=9 and pid=15 and retire_date is null";
getUniResult($query, $kanribu_a);
// 合計
$total_kanribu = $kanribu_sya + $kanribu_ps + $kanribu_p + $kanribu_k + $kanribu_syo + $kanribu_a;+

// 総務
// 社員
$query = "select count(*) from user_detailes where sid=5 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $soumu_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=5 and pid=6 and retire_date is null";
getUniResult($query, $soumu_ps);
// パート
$query = "select count(*) from user_detailes where sid=5 and pid=5 and retire_date is null";
getUniResult($query, $soumu_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=5 and pid=9 and retire_date is null";
getUniResult($query, $soumu_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=5 and pid=8 and retire_date is null";
getUniResult($query, $soumu_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=5 and pid=15 and retire_date is null";
getUniResult($query, $soumu_a);
// 合計
$total_soumu = $soumu_sya + $soumu_ps + $soumu_p + $soumu_k + $soumu_syo + $soumu_a;+

// 総務
// 社員
$query = "select count(*) from user_detailes where sid=19 and (pid=10 or pid=31 or pid=32 or pid=33 or pid=34 or pid=35 or pid=46 or pid=47 or pid=50 or pid=60 or pid=70) and retire_date is null and uid<>'020826'";
getUniResult($query, $syokan_sya);
// パートスタッフ
$query = "select count(*) from user_detailes where sid=19 and pid=6 and retire_date is null";
getUniResult($query, $syokan_ps);
// パート
$query = "select count(*) from user_detailes where sid=19 and pid=5 and retire_date is null";
getUniResult($query, $syokan_p);
// 契約社員
$query = "select count(*) from user_detailes where sid=19 and pid=9 and retire_date is null";
getUniResult($query, $syokan_k);
// 嘱託
$query = "select count(*) from user_detailes where sid=19 and pid=8 and retire_date is null";
getUniResult($query, $syokan_syo);
// アルバイト
$query = "select count(*) from user_detailes where sid=19 and pid=15 and retire_date is null";
getUniResult($query, $syokan_a);
// 合計
$total_syokan = $syokan_sya + $syokan_ps + $syokan_p + $syokan_k + $syokan_syo + $syokan_a;+

// 横合計計算
$total_sya           = $president + $factory_manager + $sub_factory_manager + $iso_sya + $seisanbu_sya + $c_kumi_sya + $l_kumi_sya + $seikan_sya + $gijyutbu_sya + $hin_sya + $gi_sya + $seizobu_sya + $sei1_sya + $sei2_sya + $kanribu_sya + $soumu_sya + $syokan_sya;
$total_ps            = $iso_ps + $seisanbu_ps + $c_kumi_ps + $l_kumi_ps + $seikan_ps + $gijyutbu_ps + $hin_ps + $gi_ps + $seizobu_ps + $sei1_ps + $sei2_ps + $kanribu_ps + $soumu_ps + $syokan_ps;
$total_p             = $iso_p + $seisanbu_p + $c_kumi_p + $l_kumi_p + $seikan_p + $gijyutbu_p + $hin_p + $gi_p + $seizobu_p + $sei1_p + $sei2_p + $kanribu_p + $soumu_p + $syokan_p;
$total_k             = $iso_k + $seisanbu_k + $c_kumi_k + $l_kumi_k + $seikan_k + $gijyutbu_k + $hin_k + $gi_k + $seizobu_k + $sei1_k + $sei2_k + $kanribu_k + $soumu_k + $syokan_k;
$total_syo           = $iso_syo + $seisanbu_syo + $c_kumi_syo + $l_kumi_syo + $seikan_syo + $gijyutbu_syo + $hin_syo + $gi_syo + $seizobu_syo + $sei1_syo + $sei2_syo + $kanribu_syo + $soumu_syo + $syokan_syo;
$total_a             = $iso_a + $seisanbu_a + $c_kumi_a + $l_kumi_a + $seikan_a + $gijyutbu_a + $hin_a + $gi_a + $seizobu_a + $sei1_a + $sei2_a + $kanribu_a + $soumu_a + $syokan_a;

// 総合計計算
$total_all = $total_sya + $total_ps + $total_p + $total_k + $total_syo + $total_a;


if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '011061') {
?>
<BR>
<table bgcolor='white' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='white' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            
            <?php
            // ＥＤＰ買掛金計上仕入額 2行目表示なし
            echo "<tr>\n";
            // タイトル
            echo "<th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='3'><span class='pt9'>　</span></th>\n";
            echo "<th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='17'><span class='pt9'>全社従業員　<font color='red'>※社長・受入出向者含む</font></span></th>\n";
            echo "<th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='3'><span class='pt9'>合<BR>計</span></th>\n";
            echo "</tr>\n";
            
            // 部門１
            echo "<tr>\n";
            // 社長
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>社<BR>長</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>工<BR>場<BR>長</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>副<BR>工場<BR>長</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' rowspan='2'><span class='pt9'>ISO<BR>事務<BR>局</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='4'><span class='pt9'>生産部</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='3'><span class='pt9'>技術部</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='3'><span class='pt9'>製造部</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center' colspan='3'><span class='pt9'>管理部</span></td>\n";
            echo "</tr>\n";
            
            // 部門２
            echo "<tr>\n";
            // 生産部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>生産<BR>部</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>Ｃ<BR>組立</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>Ｌ<BR>組立</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>生<BR>管</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>技術<BR>部</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>品<BR>証</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>技<BR>術</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>製造<BR>部</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>製造<BR>１</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>製造<BR>２</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>管理<BR>部</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowra bgcolor='#F0FFFF'p align='center'><span class='pt9'>総<BR>務</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='center'><span class='pt9'>商<BR>管</span></td>\n";
            echo "</tr>\n";
            
            // 正社員
            echo "<tr>\n";
            // タイトル
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>正社員</span></td>\n";
            // 社長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($president) . "</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($factory_manager) . "</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sub_factory_manager) . "</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_sya) . "</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_sya) . "</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_sya) . "</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_sya) . "</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_sya) . "</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_sya) . "</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_sya) . "</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_sya) . "</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_sya) . "</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_sya) . "</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_sya) . "</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_sya) . "</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_sya) . "</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_sya) . "</span></td>\n";
            // 合計
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_sya) . "</span></td>\n";
            echo "</tr>\n";
           
            // パートスタッフ
            echo "<tr>\n";
            // タイトル
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>パート<BR>スタッフ</span></td>\n";
            // 社長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_ps) . "</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_ps) . "</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_ps) . "</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_ps) . "</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_ps) . "</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_ps) . "</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_ps) . "</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_ps) . "</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_ps) . "</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_ps) . "</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_ps) . "</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_ps) . "</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_ps) . "</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_ps) . "</span></td>\n";
            // 合計
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_ps) . "</span></td>\n";
            echo "</tr>\n";
            
            // パート
            echo "<tr>\n";
            // タイトル
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>パート</span></td>\n";
            // 社長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_p) . "</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_p) . "</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_p) . "</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_p) . "</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_p) . "</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_p) . "</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_p) . "</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_p) . "</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_p) . "</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_p) . "</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_p) . "</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_p) . "</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_p) . "</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_p) . "</span></td>\n";
            // 合計
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_p) . "</span></td>\n";
            echo "</tr>\n";
            
            // 契約社員
            echo "<tr>\n";
            // タイトル
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>契約<BR>社員</span></td>\n";
            // 社長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_k) . "</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_k) . "</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_k) . "</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_k) . "</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_k) . "</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_k) . "</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_k) . "</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_k) . "</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_k) . "</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_k) . "</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_k) . "</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_k) . "</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_k) . "</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_k) . "</span></td>\n";
            // 合計
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_k) . "</span></td>\n";
            echo "</tr>\n";
            
            // 嘱託
            echo "<tr>\n";
            // タイトル
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>嘱託</span></td>\n";
            // 社長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_syo) . "</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_syo) . "</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_syo) . "</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_syo) . "</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_syo) . "</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_syo) . "</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_syo) . "</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_syo) . "</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_syo) . "</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_syo) . "</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_syo) . "</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_syo) . "</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_syo) . "</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_syo) . "</span></td>\n";
            // 合計
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_syo) . "</span></td>\n";
            echo "</tr>\n";
            
            // アルバイト
            echo "<tr>\n";
            // タイトル
            echo "  <th class='winbox' nowra bgcolor='#F0FFFF'p align='right'><span class='pt9'>アル<BR>バイト</span></td>\n";
            // 社長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($iso_a) . "</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seisanbu_a) . "</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($c_kumi_a) . "</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($l_kumi_a) . "</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seikan_a) . "</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gijyutbu_a) . "</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($hin_a) . "</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($gi_a) . "</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($seizobu_a) . "</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei1_a) . "</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($sei2_a) . "</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($kanribu_a) . "</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($soumu_a) . "</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($syokan_a) . "</span></td>\n";
            // 合計
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_a) . "</span></td>\n";
            echo "</tr>\n";
            
            // 合計
            echo "<tr>\n";
            // タイトル
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>合計</span></td>\n";
            // 社長
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($president) . "</span></td>\n";
            // 工場長
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($factory_manager) . "</span></td>\n";
            // 副工場長
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($sub_factory_manager) . "</span></td>\n";
            // ISO事務局
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_iso) . "</span></td>\n";
            // 生産部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_seisanbu) . "</span></td>\n";
            // C組立
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_ckumi) . "</span></td>\n";
            // L組立
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_lkumi) . "</span></td>\n";
            // 生管
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_seikan) . "</span></td>\n";
            // 技術部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_gijyutbu) . "</span></td>\n";
            // 品証
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_hin) . "</span></td>\n";
            // 技術
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_gi) . "</span></td>\n";
            // 製造部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_seizobu) . "</span></td>\n";
            // 製造１
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_sei1) . "</span></td>\n";
            // 製造２
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_sei2) . "</span></td>\n";
            // 管理部
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_kanribu) . "</span></td>\n";
            // 総務
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_soumu) . "</span></td>\n";
            // 商管
            echo "  <th class='winbox' nowrap bgcolor='#F0FFFF' align='right'><span class='pt9'>" . number_format($total_syokan) . "</span></td>\n";
            // 総合計
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
        <font color='#ffffff'>従業員 統計 情報 表示</font></td>
    </tr>
    <tr><td valign='top'>
        <font color='#ff7e00'><b>1.</b></font>平均年齢
        <hr>
        <table width='100%'>
            <tr>
                <td>社長・出向者を除く全従業員の平均年齢(<?php echo $base_date ?>基準)</td>
                <td width='20%' align='right'><?php echo $res_base_avg ?>歳／<?php echo $res_base_count ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>社長・出向者を除く全従業員の平均年齢(本日現在基準)</td>
                <td width='20%' align='right'><?php echo $res_today_avg ?>歳／<?php echo $res_base_count ?>人</td>
            </tr>
        </table>
        <hr>
        <font color='#ff7e00'><b>2.</b></font>直間比率
        <hr>
        <table width='100%'>
            <tr>
                <td>社長・出向者を除く全従業員対象 直接部門 ： (間接部門＋販管費部門)</td>
                <td width='20%' align='right'><?php echo $tyoku_ritu ?>％ ： <?php echo $kanse_ritu ?>％</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td colspan='2' width='100%' align='right'><?php echo $res_t_man ?>人 ： <?php echo $kan_man ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>製造経費部門を対象に 　直接部門 ： 間接部門</td>
                <td width='20%' align='right'><?php echo $direct_ritu ?>％ ： <?php echo $kanset_ritu ?>％</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td colspan='2' width='100%' align='right'><?php echo $res_t_man ?>人 ： <?php echo $res_k_man ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td colspan='2' width='100%' align='right'>販管費部門の人数＝<?php echo $res_h_man ?>人</td>
            </tr>
        </table>
        <hr>
        <font color='#ff7e00'><b>3.</b></font>社員とパートの人数　(社長と出向者除く)
        <hr>
        <table width='100%'>
            <tr>
                <td>全社 従業員数</td>
                <td width='20%' align='right'><?php echo $zen ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>全社 社員数：パート数</td>
                <td width='20%' align='right'><?php echo $syain, '：', $part ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>社員内訳 正社員：契約社員：嘱託：アルバイト</td>
                <td width='20%' align='right'><?php echo $ss_syain, '：', $kei_syain, '：', $syo_syain, '：', $aru_syain ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>パート内訳 パートスタッフ：パート</td>
                <td width='20%' align='right'><?php echo $staff_part, '：', $i_part ?>人</td>
            </tr>
        </table>
        <hr>
        <table width='100%'>
            <tr>
                <td>直接部門 社員数：パート数</td>
                <td width='20%' align='right'><?php echo $t_syain, '：', $t_part ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>間接部門 社員数：パート数</td>
                <td width='20%' align='right'><?php echo $f_syain, '：', $f_part ?>人</td>
            </tr>
        </table>
        <table width='100%'>
            <tr>
                <td>販管費部門 社員数：パート数</td>
                <td width='20%' align='right'><?php echo $h_syain, '：', $h_part ?>人</td>
            </tr>
        </table>
        <hr>
    </td>
    </tr>
</table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
