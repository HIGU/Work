<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 ＣＬ商品管理 経費実績表                                //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/01/29 Created   profit_loss_cl_keihi.php                            //
// 2003/01/30 明細フィールドのデータ計算が終了してから単位調整に変更        //
// 2003/02/12 配賦処理を別プログラムに変更。経歴テーブルからデータ取得      //
// 2003/02/21 font を monospace (等間隔font) へ変更                         //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                       //
// 2003/03/06 title_font today_font を設定 少数以下の桁数６桁を追加         //
// 2003/03/10 売上高 材料(仕入高) 材料(製造原価) を追加                     //
// 2003/03/11 Location: http → Location $url_referer に変更                //
//            メッセージを出力するため site_index site_id をコメントにし    //
//                                            parent.menu_site.を有効に変更 //
// 2003/05/01 工場長からの指示で認証をAccount_groupから通常へ変更           //
// 2004/05/06 外形標準課税の対応のため事業等の科目追加(7520)B36 $r=35       //
//            下位互換性のため事業等7520を除いてselectし7520のみをselectへ  //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタンを追加                 //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2009/08/20 商品管理の経費実績表示を追加。旧ファイルは_oldとして          //
//            別メニューに残した                                       大谷 //
// 2009/10/15 各小計・合計を太字に変更                                 大谷 //
// 2009/11/10 商管給与配賦分を加味するように変更（給与手当）           大谷 //
// 2010/01/15 200912分の経費に調整を掛けようとしたがExcel側が出来ない為     //
//            調整しない。                                             大谷 //
// 2010/02/05 添田さんの給与をC・L35％、試修30％に振分けるように変更        //
//            後日マスター化にする。                                   大谷 //
// 2010/02/08 試修給与配賦をマスターから取得するように変更             大谷 //
// 2010/06/04 2010/05は売上高（全体とカプラ）に+800,000調整                 //
//            2010/06に戻し予定 → やらないことに決定したので戻し      大谷 //
// 2012/02/08 2012年1月 業務委託費 調整 リニア製造経費 +1,156,130円    大谷 //
//             ※ 平出横川派遣料 2月に逆調整を行うこと                      //
// 2012/02/09 カプラ・リニア・商管の製造経費登録を追加                 大谷 //
// 2012/03/05 2012年1月 業務委託費 調整 リニア製造経費 -1,156,130円 戻 大谷 //
// 2013/11/07 2013年10月 商管業務委託費 調整 +1,245,035円              大谷 //
//             ※ 横川派遣料 11月に逆調整を行うこと                         //
// 2013/11/07 2013年11月 商管業務委託費 調整 -1,245,035円 戻し処理     大谷 //
// 2015/02/20 クレーム対応費の事業等の科目追加(7550)D37 $r=36               //
//            kin1=製造経費 kin2=販管費 なので kin3～kin9は必要ないので削除 //
//            $rec_keihi = 28→29へ変更 (クレーム対応費追加による)          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

$url_referer     = $_SESSION['pl_referer'];     // 呼出もとの URL を取得

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　Ｃ Ｌ 商品管理 経 費 実 績 内 訳 表");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// 表示単位を設定取得
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // 初期値 表示単位 千円
    $_SESSION['keihi_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // 初期値 小数点以下桁数
    $_SESSION['keihi_keta'] = $keta;
}
//////////// 人件費・経費のレコード数 フィールド数
$rec_jin =  8;    // 人件費の使用科目数
$rec_kei = 29;    // 経費の使用科目数       クレーム対応費対応のため 28→29
$f_mei   = 13;    // 明細(表)のフィールド数

//////////// 勘定科目の配列設定
// 人件費の Start End 科目
$str_jin = 8101;
$end_jin = 8123;
/******
    8101 = 役員報酬
    8102 = 給料手当
    8103 = 賞与手当
    8104 = 顧問料
    8105 = 法定福利費
    8106 = 厚生福利費
    8121 = 賞与引当金繰入
    8123 = 退職給付費用  旧名→退職給与引当金繰入
******/
$jin_act = array(8101,8102,8103,8104,8105,8106,8121,8123);

// 経費の Start End 科目
$str_kei = 7501;
$end_kei = 8000;
/******
    7501 = 旅費交通費
    7502 = 海外出張
    7503 = 通信費
    7504 = 会議費
    7505 = 交際接待費
    7506 = 広告宣伝費
    7508 = 求人費
    7509 = 運賃荷造費
    7510 = 図書教育費
    7512 = 業務委託費
    7520 = 事業等       // 外形標準課税により追加
    7521 = 諸税公課
    7522 = 試験研究費
    7523 = 雑費
    7524 = 修繕費
    7525 = 保証修理費
    7526 = 事務用消耗品費
    7527 = 工場消耗品費
    7528 = 車両費
    7530 = 保険料
    7531 = 水道光熱費
    7532 = 諸会費
    7533 = 支払手数料
    7536 = 地代家賃
    7537 = 寄付金
    7538 = 倉敷料
    7540 = 賃借料
    7550 = クレーム対応費
    8000 = 減価償却費
******/
$kei_act = array(7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,7550,8000);
////// 全体の配列
$actcod  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,7550,8000);

/***** 売    上    高 *****/
$res = array();                     ///// 売上の月次処理で作られたデータを使用
$query = sprintf("select 全体, カプラ, リニア from wrk_uriage where 年月=%d", $yyyymm);
if ((getResult($query,$res)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    $uri   = $res[0]['全体'];
    $uri_c = $res[0]['カプラ'];
    $uri_l = $res[0]['リニア'];
        ///// 調整データの取得
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note like '%%売上高調整'", $yyyymm); // 全体
    getUniResult($query, $adjust_all);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='カプラ売上高調整'", $yyyymm); // カプラ
    getUniResult($query, $adjust_c);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='リニア売上高調整'", $yyyymm); // リニア
    getUniResult($query, $adjust_l);
        ///// 調整ロジック END
    $uri   = ($uri + ($adjust_all));    // マイナスも考慮して()を使用する
    $uri_c = ($uri_c + ($adjust_c));
    $uri_l = ($uri_l + ($adjust_l));
    $view_uriage   = number_format(($uri / $tani), $keta);
    $view_uriage_c = number_format(($uri_c / $tani), $keta);
    $view_uriage_l = number_format(($uri_l / $tani), $keta);
        ///// 売上比 算出
    $uri_ritu_c = (Uround(($uri_c / $uri), 3)) * 100;
    $uri_ritu_l = (100 - $uri_ritu_c);
    $view_ritu_c = number_format($uri_ritu_c, 1) . '%';
    $view_ritu_l = number_format($uri_ritu_l, 1) . '%';
    $view_ritu   = number_format(($uri_ritu_c + $uri_ritu_l), 1) . '%';
} else {
    $view_uriage   = "未登録";
    $view_uriage_c = "未登録";
    $view_uriage_l = "未登録";
    $view_ritu_c   = "未登録";
    $view_ritu_l   = "未登録";
    $view_ritu     = "未登録";
}

/********** 材料費(仕入高) **********/
$res = array();
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='全体仕入高'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire      = $res[0]['kin'];
    $shiire_ritu = (Uround($res[0]['allo'], 3) * 100);
    $view_shiire = number_format(($shiire / $tani), $keta);
    $view_shiire_ritu = number_format($shiire_ritu, 1) . '%';
} else {
    $view_shiire = "未計算";
    $view_shiire_ritu = "未計算";
}
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='カプラ仕入高'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire_c      = $res[0]['kin'];
    $shiire_ritu_c = (Uround($res[0]['allo'], 3) * 100);
    $view_shiire_c = number_format(($shiire_c / $tani), $keta);
    $view_shiire_ritu_c = number_format($shiire_ritu_c, 1) . '%';
} else {
    $view_shiire_c = "未計算";
    $view_shiire_ritu_c = "未計算";
}
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='リニア仕入高'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire_l      = $res[0]['kin'];
    $shiire_ritu_l = (100 - $shiire_ritu_c);        // 合計を合わせるため 100 から カプラを引いた値にする
    $view_shiire_l = number_format(($shiire_l / $tani), $keta);
    $view_shiire_ritu_l = number_format($shiire_ritu_l, 1) . '%';
} else {
    $view_shiire_l = "未計算";
    $view_shiire_ritu_l = "未計算";
}

/********** 材料費(製造原価) **********/
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体材料費'", $yyyymm);
if (getUniResult($query, $material) < 1) {
    $view_material   = "未計算";     // 検索失敗
    $view_material_c = "未計算";
    $view_material_l = "未計算";
    $view_barance    = "-----";
} else {
    $view_material = number_format(($material / $tani), $keta);
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $yyyymm);
    if (getUniResult($query, $material_c) < 1) {
        $view_material_c = "未計算";     // 検索失敗
        $view_material_l = "未計算";
        $view_barance    = "-----";
    } else {
        $view_material_c = number_format(($material_c / $tani), $keta);
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $yyyymm);
        if (getUniResult($query, $material_l) < 1) {
            $view_material_l = "未計算";     // 検索失敗
            $view_barance    = "-----";
        } else {
            $view_material_l = number_format(($material_l / $tani), $keta);
                ///// 材料比 算出
            $mate_ritu_c = (Uround(($material_c / $material), 3)) * 100;
            $mate_ritu_l = (100 - $mate_ritu_c);
            $view_mate_ritu_c = number_format($mate_ritu_c, 1) . '%';
            $view_mate_ritu_l = number_format($mate_ritu_l, 1) . '%';
            $view_mate_ritu   = number_format(($mate_ritu_c + $mate_ritu_l), 1) . '%';
            $balance = ($shiire - $material);
            $view_barance = number_format(($balance / $tani), $keta);
        }
    }
}
////// 給与配賦額の取得
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦額'", $yyyymm);
    if (getUniResult($query, $s_kyu_kei) < 1) {
        $s_kyu_kei = 0;                    // 検索失敗
        $s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦率'", $yyyymm);
        if (getUniResult($query, $s_kyu_kin) < 1) {
            $s_kyu_kin = 0;
        }
    }
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ給与配賦率'", $yyyymm);
    if (getUniResult($query, $c_kyu_kin) < 1) {
        $c_kyu_kin = 0;
    }
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア給与配賦率'", $yyyymm);
    if (getUniResult($query, $l_kyu_kin) < 1) {
        $l_kyu_kin = 0;
    }
}

////// 経歴テーブルよりデータ取り込み
$res_jin = array();     /*** 当月のデータ取得 ***/
$query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12, actcod from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) order by actcod ASC", $yyyymm, $str_jin, $end_jin);
if (($rows_jin = getResult2($query,$res_jin)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    $res_kei = array();                                             // 互換性のため actcod=7520と7550 を最初は除外する
    $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12, actcod from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) and actcod!=7520 and actcod!=7550 order by actcod ASC", $yyyymm, $str_kei, $end_kei);
    if (($rows_kei = getResult2($query,$res_kei)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
        // 商品管理580部門をカプラ間接費よりマイナス
        $bkan_jin = array();
        $view_bkan_jin = array();
        for ($r=0; $r < $rows_jin; $r++) {
            $res_580_jin = array();
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 580 order by actcod ASC", $yyyymm, $res_jin[$r][13]);
            if (($rows_580_jin = getResult2($query,$res_580_jin)) > 0) {
                // 200907のみ商品管理合併つきにより給与手当を調整
                if (($r == 1) && ($yyyymm == 200907)) {
                    $res_580_jin[0][3] = $res_580_jin[0][3] + 2338178;
                }
                if (($r == 1) && ($yyyymm == 200912)) {
                    $res_jin[1][6] = $res_jin[1][6] + 1227429;
                    $res_jin[1][7] = $res_jin[1][7] - 1409708 + 182279;
                    //$res_jin[1][8] = $res_jin[1][8] + 1409708;
                    //$res_jin[1][9] = $res_jin[1][9] + 1409708;
                    $res_jin[1][0] = $res_jin[1][0] + 1227429;
                    $res_jin[1][1] = $res_jin[1][1] - 1409708 + 182279;
                    //$res_jin[1][2] = $res_jin[1][2] + 1409708;
                }
                if (($r == 1) && ($yyyymm >= 201001)) {
                    $res_jin[1][6] = $res_jin[1][6] + $c_kyu_kin;
                    $res_jin[1][7] = $res_jin[1][7] - $s_kyu_kei + $s_kyu_kin + $l_kyu_kin;
                    //$res_jin[1][8] = $res_jin[1][8] + 302626;
                    //$res_jin[1][9] = $res_jin[1][9] + 302626;
                    $res_jin[1][0] = $res_jin[1][0] + $c_kyu_kin;
                    $res_jin[1][1] = $res_jin[1][1] - $s_kyu_kei + $s_kyu_kin + $l_kyu_kin;
                    //$res_jin[1][2] = $res_jin[1][2] + 302626;
                }
                $res_jin[$r][6] = $res_jin[$r][6] - $res_580_jin[0][3];
                $res_jin[$r][0] = $res_jin[$r][0] - $res_580_jin[0][3];
                if (($r == 4) && ($yyyymm == 201408)) {
                    // 当月の製造経費（一番左）
                    $res_jin[4][0] = $res_jin[4][0] + 93951;    // カプラ法定福利
                    $res_jin[4][1] = $res_jin[4][1] + 35232;    // リニア法定福利
                    // 間接経費調整
                    $res_jin[4][6] = $res_jin[4][6] + 93951;    // カプラ法定福利
                    $res_jin[4][7] = $res_jin[4][7] + 35232;    // リニア法定福利
                    // 商管調整
                    $res_580_jin[0][3] = $res_580_jin[0][3] - 129183;
                }
                if (($r == 6) && ($yyyymm == 201408)) {
                    // 当月の製造経費（一番左）
                    $res_jin[6][0] = $res_jin[6][0] + 519590;    // カプラ賞与引当
                    $res_jin[6][1] = $res_jin[6][1] + 194846;    // リニア賞与引当
                    // 間接経費調整
                    $res_jin[6][6] = $res_jin[6][6] + 519590;    // カプラ賞与引当
                    $res_jin[6][7] = $res_jin[6][7] + 194846;    // リニア賞与引当
                    // 商管調整
                    $res_580_jin[0][3] = $res_580_jin[0][3] - 714436;
                }
                if (($r == 7) && ($yyyymm == 201408)) {
                    // 当月の製造経費（一番左）
                    $res_jin[7][0] = $res_jin[7][0] - 1637;     // カプラ退職給付引当
                    $res_jin[7][1] = $res_jin[7][1] - 614;      // リニア退職給付引当
                    // 間接経費調整
                    $res_jin[7][6] = $res_jin[7][6] - 1637;     // カプラ退職給付引当
                    $res_jin[7][7] = $res_jin[7][7] - 614;      // リニア退職給付引当
                    // 商管調整
                    $res_580_jin[0][3] = $res_580_jin[0][3] + 2251;
                }
                $bkan_jin[$r] = $res_580_jin[0][3];
                $bkan_jin_all += $bkan_jin[$r];
                $view_bkan_jin[$r] = number_format(($bkan_jin[$r] / $tani),$keta);
                $view_bkan_jin_all = number_format(($bkan_jin_all / $tani),$keta);
            } else {
                $bkan_jin[$r] = 0;
                $view_bkan_jin[$r] = number_format(($bkan_jin[$r] / $tani),$keta);
                $view_bkan_jin_all = number_format(($bkan_jin_all / $tani),$keta);
            }
        }
        $bkan_kei = array();
        $view_bkan_kei = array();
        $s = 8;     // $view_dataの経費部分が8から始まるため
        for ($r=0; $r < $rows_kei; $r++) {
            $res_580_kei = array();
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 580 and actcod!=7520 and actcod!=7550 order by actcod ASC", $yyyymm, $res_kei[$r][13]);
            if (($rows_580_kei = getResult2($query,$res_580_kei)) > 0) {
                $res_kei[$r][6] = $res_kei[$r][6] - $res_580_kei[0][3];
                $res_kei[$r][0] = $res_kei[$r][0] - $res_580_kei[0][3];
                $bkan_kei[$s] = $res_580_kei[0][3];
                // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
                if ($yyyymm == 201310) {
                    $bkan_kei[17] += 1245035;
                }
                if ($yyyymm == 201311) {
                    $bkan_kei[17] -= 1245035;
                }
                $bkan_kei_all += $bkan_kei[$s];
                $view_bkan_kei[$s] = number_format(($bkan_kei[$s] / $tani),$keta);
                $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
            } else {
                $bkan_kei[$s] = 0;
                $view_bkan_kei[$s] = number_format(($bkan_kei[$s] / $tani),$keta);
                $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
            }
            $s += 1;
        }
        // 09/11/10追加
        // 商管販管費給与手当配賦取得
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $yyyymm);
        if (getUniResult($query, $c_allo_kin) < 1) {
            $c_allo_kin = 0;     // 検索失敗
        } else {
            //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
        }
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $yyyymm);
        if (getUniResult($query, $l_allo_kin) < 1) {
            $l_allo_kin = 0;     // 検索失敗
        } else {
            //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
        }
        
        // 商品管理670部門をカプラ販管費よりマイナス
        $bhan_jin = array();
        $view_bhan_jin = array();
        for ($r=0; $r < $rows_jin; $r++) {
            $res_670_jin = array();     /*** 当月のデータ取得 ***/
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 670 order by actcod ASC", $yyyymm, $res_jin[$r][13]);
            if (($rows_670_jin = getResult2($query,$res_670_jin)) > 0) {
                // 200907のみ商品管理合併つきにより給与手当を調整
                if (($r == 1) && ($yyyymm == 200907)) {
                    $res_670_jin[0][3] = $res_670_jin[0][3] + 180298;
                }
                // 09/11/10追加
                // 商管販管費給与手当配賦加味
                if ($r == 1) {
                    $res_670_jin[0][3] = $res_670_jin[0][3] + $c_allo_kin;
                    $res_jin[$r][10] = $res_jin[$r][10] - $res_670_jin[0][3];
                    $res_jin[$r][11] = $res_jin[$r][11] - $l_allo_kin;
                    $bhan_jin[$r] = $res_670_jin[0][3] + $l_allo_kin;
                } else {
                    $res_jin[$r][10] = $res_jin[$r][10] - $res_670_jin[0][3];
                    $bhan_jin[$r] = $res_670_jin[0][3];
                }
                $bhan_jin_all += $bhan_jin[$r];
                $view_bhan_jin[$r] = number_format(($bhan_jin[$r] / $tani),$keta);
                $view_bhan_jin_all = number_format(($bhan_jin_all / $tani),$keta);
            } else {
                $bhan_jin[$r] = 0;
                $view_bhan_jin[$r] = number_format(($bhan_jin[$r] / $tani),$keta);
                $view_bhan_jin_all = number_format(($bhan_jin_all / $tani),$keta);
            }
        }
        $bhan_kei = array();
        $view_bhan_kei = array();
        $s = 8;     // $view_dataの経費部分が8から始まるため
        for ($r=0; $r < $rows_kei; $r++) {
            $res_670_kei = array();
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 670 and actcod!=7520 and actcod!=7550 order by actcod ASC", $yyyymm, $res_kei[$r][13]);
            if (($rows_670_kei = getResult2($query,$res_670_kei)) > 0) {
                $res_kei[$r][10] = $res_kei[$r][10] - $res_670_kei[0][3];
                $bhan_kei[$s] = $res_670_kei[0][3];
                $bhan_kei_all += $bhan_kei[$s];
                $view_bhan_kei[$s] = number_format(($bhan_kei[$s] / $tani),$keta);
                $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
            } else {
                $bhan_kei[$s] = 0;
                $view_bhan_kei[$s] = number_format(($bhan_kei[$s] / $tani),$keta);
                $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
            }
            $s += 1;
        }
        ///// 人件費と経費の明細部
        // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
        if ($yyyymm == 201201) {
            $res_kei[9][1] += 1156130;
            $res_kei[9][2] += 1156130;
            $res_kei[9][4] += 1156130;
            $res_kei[9][5] += 1156130;
            $res_kei[9][9] += 1156130;
        }
        if ($yyyymm == 201202) {
            $res_kei[9][1] -= 1156130;
            $res_kei[9][2] -= 1156130;
            $res_kei[9][4] -= 1156130;
            $res_kei[9][5] -= 1156130;
            $res_kei[9][9] -= 1156130;
        }
        // 2013/11/07 追加 2013年10月度 商管業務委託費（横川派遣料）調整（計のみ）
        if ($yyyymm == 201310) {
            $res_kei[9][2] += 1245035;
            $res_kei[9][8] += 1245035;
            $res_kei[9][9] += 1245035;
        }
        if ($yyyymm == 201311) {
            $res_kei[9][2] -= 1245035;
            $res_kei[9][8] -= 1245035;
            $res_kei[9][9] -= 1245035;
        }
        $data      = array();       // 計算用変数 配列で初期化
        $view_data = array();       // 表示用変数 配列で初期化
        ///////// 表示用データの生成 (画面の表データイメージ)
        ///// 明細の 単位調整
        $r = 0;
        $c = 0;
        foreach ($res_jin as $row) {    // 人件費
            foreach ($row as $col) {
                $data[$r][$c] = $col / $tani;
                $view_data[$r][$c] = number_format($data[$r][$c],$keta);
                $c++;
            }
            $r++;
            $c = 0;
        }
        foreach ($res_kei as $row) {    // 経費
            foreach ($row as $col) {
                $data[$r][$c] = $col / $tani;
                $view_data[$r][$c] = number_format($data[$r][$c],$keta);
                $c++;
            }
            $r++;
            $c = 0;
        }
        ///// 外形標準課税の事業等 追加分
        $res_580_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 580 and actcod=7520 order by actcod ASC", $yyyymm);
        if (($rows_580_gai = getResult2($query,$res_580_gai)) > 0) {
            $bkan_kei[35] = $res_580_gai[0][3];
            $bkan_kei_all += $bkan_kei[35];
            $view_bkan_kei[35] = number_format(($bkan_kei[35] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        } else {
            $bkan_kei[35] = 0;
            $view_bkan_kei[35] = number_format(($bkan_kei[35] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        }
        $res_670_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 670 and actcod=7520 order by actcod ASC", $yyyymm);
        if (($rows_670_gai = getResult2($query,$res_670_gai)) > 0) {
            $bhan_kei[35] = $res_670_gai[0][3];
            $bhan_kei_all += $bhan_kei[35];
            $view_bhan_kei[35] = number_format(($bhan_kei[35] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        } else {
            $bhan_kei[35] = 0;
            $view_bhan_kei[35] = number_format(($bhan_kei[35] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        }
        $res_gai = array();
        $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and actcod=7520", $yyyymm);
        if (($rows_gai = getResult2($query,$res_gai)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
            for ($c = 0; $c < $f_mei; $c++) {
                if ($c == 6) {
                    $data[35][$c]      = ($res_gai[0][$c] - $bkan_kei[35]) / $tani;
                    $view_data[35][$c] = number_format($data[35][$c], $keta);
                } elseif ($c == 10) {
                    $data[35][$c]      = ($res_gai[0][$c] - $bkan_kei[35]) / $tani;
                    $view_data[35][$c] = number_format($data[35][$c], $keta);
                } else {
                    $data[35][$c]      = $res_gai[0][$c] / $tani;
                    $view_data[35][$c] = number_format($data[35][$c], $keta);
                }
            }
        } else {
            for ($c = 0; $c < $f_mei; $c++) {   // 事業等(7520)が無ければ0で初期化
                if ($c == 6) {
                    $data[35][$c]      = 0 - $bkan_kei[35];
                    $view_data[35][$c] = number_format(($data[35][$c] / $tani), $keta);
                } elseif ($c == 10) {
                    $data[35][$c]      = 0 - $bkan_kei[35];
                    $view_data[35][$c] = number_format(($data[35][$c] / $tani), $keta);
                } else {
                    $data[35][$c]      = 0;
                    $view_data[35][$c] = 0;
                }
            }
        }
        ///// クレーム対応費 追加分
        $res_580_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 580 and actcod=7550 order by actcod ASC", $yyyymm);
        if (($rows_580_gai = getResult2($query,$res_580_gai)) > 0) {
            $bkan_kei[36] = $res_580_gai[0][3];
            $bkan_kei_all += $bkan_kei[36];
            $view_bkan_kei[36] = number_format(($bkan_kei[36] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        } else {
            $bkan_kei[36] = 0;
            $view_bkan_kei[36] = number_format(($bkan_kei[36] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        }
        $res_670_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 670 and actcod=7550 order by actcod ASC", $yyyymm);
        if (($rows_670_gai = getResult2($query,$res_670_gai)) > 0) {
            $bhan_kei[36] = $res_670_gai[0][3];
            $bhan_kei_all += $bhan_kei[36];
            $view_bhan_kei[36] = number_format(($bhan_kei[36] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        } else {
            $bhan_kei[36] = 0;
            $view_bhan_kei[36] = number_format(($bhan_kei[36] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        }
        $res_gai = array();
        $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and actcod=7550", $yyyymm);
        if (($rows_gai = getResult2($query,$res_gai)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
            for ($c = 0; $c < $f_mei; $c++) {
                if ($c == 6) {
                    $data[36][$c]      = ($res_gai[0][$c] - $bkan_kei[36]) / $tani;
                    $view_data[36][$c] = number_format($data[36][$c], $keta);
                } elseif ($c == 10) {
                    $data[36][$c]      = ($res_gai[0][$c] - $bkan_kei[36]) / $tani;
                    $view_data[36][$c] = number_format($data[36][$c], $keta);
                } else {
                    $data[36][$c]      = $res_gai[0][$c] / $tani;
                    $view_data[36][$c] = number_format($data[36][$c], $keta);
                }
            }
        } else {
            for ($c = 0; $c < $f_mei; $c++) {   // 事業等(7550)が無ければ0で初期化
                if ($c == 6) {
                    $data[36][$c]      = 0 - $bkan_kei[36];
                    $view_data[36][$c] = number_format(($data[36][$c] / $tani), $keta);
                } elseif ($c == 10) {
                    $data[36][$c]      = 0 - $bkan_kei[36];
                    $view_data[36][$c] = number_format(($data[36][$c] / $tani), $keta);
                } else {
                    $data[36][$c]      = 0;
                    $view_data[36][$c] = 0;
                }
            }
        }
        ///// 商品管理分 人件費・経費合計
        $bkan_sum = $bkan_jin_all + $bkan_kei_all;
        $bhan_sum = $bhan_jin_all + $bhan_kei_all;
        $view_bkan_sum = number_format(($bkan_sum / $tani), $keta);
        $view_bhan_sum = number_format(($bhan_sum / $tani), $keta);
        ///// 
        ///// その他(9999)の科目があるかチェック
        $query = sprintf("select (kin00+kin01+kin02+kin03+kin04+kin05+kin06+kin07+kin08+kin09+kin10+kin11+kin12) as other from act_cl_history where pl_bs_ym=%d and actcod=9999", $yyyymm);
        if (getUniResult($query, $res_oth) > 0) {
            if ($res_oth > 0) {
                $_SESSION['s_sysmsg'] = sprintf("その他に金額があります！<br>第%d期%d月：%d", $ki, $tuki, $res_oth);
            }
        }
        
        ///// 小計の計算 人件費
        $jin_sum = array();
        for ($c=0; $c < $f_mei; $c++) {
            $jin_sum[$c] = 0;       // 以下で += を使うため初期化
        }
        for ($r=0; $r < $rec_jin; $r++) {
            for ($c=0; $c < $f_mei; $c++) {
                $jin_sum[$c] += $data[$r][$c];
            }
        }
        ///// 小計の計算 経費
        $kei_sum = array();
        for ($c=0; $c < $f_mei; $c++) {
            $kei_sum[$c] = 0;       // 以下で += を使うため初期化
        }
        for ($r=0; $r<$rec_kei; $r++) {
            for ($c=0; $c < $f_mei; $c++) {
                $kei_sum[$c] += $data[$r+8][$c];
            }
        }
        ///// 合計の計算   ///// 小計・合計の表示用データ生成
        $all_sum = array();
        $view_jin_sum = array();
        $view_kei_sum = array();
        $view_all_sum = array();
        for ($c=0;$c<$f_mei;$c++) {
            $all_sum[$c]  = $jin_sum[$c] + $kei_sum[$c];             // 合計の計算
            $view_jin_sum[$c] = number_format($jin_sum[$c],$keta);   // 表示用 人件費計
            $view_kei_sum[$c] = number_format($kei_sum[$c],$keta);   // 表示用 経費計
            $view_all_sum[$c] = number_format($all_sum[$c],$keta);   // 表示用 合　計
        }
    } else {
        $_SESSION['s_sysmsg'] = sprintf("経費の対象データがありません！<br>第%d期%d月",$ki,$tuki);
        header("Location: $url_referer");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = sprintf("対象データがありません！<br>第%d期%d月",$ki,$tuki);
    header("Location: $url_referer");
    exit();
}

    //// 当月データの登録
if (isset($_POST['input_data'])) {
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "役員報酬";
    $item[1]   = "給料手当";
    $item[2]   = "賞与手当";
    $item[3]   = "顧問料";
    $item[4]   = "法定福利費";
    $item[5]   = "厚生福利費";
    $item[6]   = "賞与引当金繰入";
    $item[7]   = "退職給付費用";
    $item[8]   = "人件費計";
    $item[9]   = "旅費交通費";
    $item[10]  = "海外出張";
    $item[11]  = "通信費";
    $item[12]  = "会議費";
    $item[13]  = "交際接待費";
    $item[14]  = "広告宣伝費";
    $item[15]  = "求人費";
    $item[16]  = "運賃荷造費";
    $item[17]  = "図書教育費";
    $item[18]  = "業務委託費";
    $item[19]  = "事業等";
    $item[20]  = "諸税公課";
    $item[21]  = "試験研究費";
    $item[22]  = "雑費";
    $item[23]  = "修繕費";
    $item[24]  = "保証修理費";
    $item[25]  = "事務用消耗品費";
    $item[26]  = "工場消耗品費";
    $item[27]  = "車両費";
    $item[28]  = "保険料";
    $item[29]  = "水道光熱費";
    $item[30]  = "諸会費";
    $item[31]  = "支払手数料";
    $item[32]  = "地代家賃";
    $item[33]  = "寄付金";
    $item[34]  = "倉敷料";
    $item[35]  = "賃借料";
    $item[36]  = "減価償却費";
    $item[37]  = "クレーム対応費";
    $item[38]  = "経費計";
    $item[39]  = "合計";
    ///////// 各データの保管
    ///// 表示データからカンマを削除する
    ///// $number = str_replace(',','',$english_format_number);
    for ($i = 0; $i < 6; $i++) {
        if ($i == 2 || $i == 5) {
            if ($i == 2) {          // 商管製造経費
                $input_data = array();
                $input_data[0]   = str_replace(',','',$view_bkan_jin[0]);   // 役員報酬
                $input_data[1]   = str_replace(',','',$view_bkan_jin[1]);   // 給料手当
                $input_data[2]   = str_replace(',','',$view_bkan_jin[2]);   // 賞与手当
                $input_data[3]   = str_replace(',','',$view_bkan_jin[3]);   // 顧問料
                $input_data[4]   = str_replace(',','',$view_bkan_jin[4]);   // 法定福利
                $input_data[5]   = str_replace(',','',$view_bkan_jin[5]);   // 厚生福利費
                $input_data[6]   = str_replace(',','',$view_bkan_jin[6]);   // 賞与引当金繰入
                $input_data[7]   = str_replace(',','',$view_bkan_jin[7]);   // 退職給付費用
                $input_data[8]   = str_replace(',','',$view_bkan_jin_all);   // 人件費計
                $input_data[9]   = str_replace(',','',$view_bkan_kei[8]);   // 旅費交通費
                $input_data[10]  = str_replace(',','',$view_bkan_kei[9]);   // 海外出張
                $input_data[11]  = str_replace(',','',$view_bkan_kei[10]);   // 通信費
                $input_data[12]  = str_replace(',','',$view_bkan_kei[11]);   // 会議費
                $input_data[13]  = str_replace(',','',$view_bkan_kei[12]);   // 交際接待費
                $input_data[14]  = str_replace(',','',$view_bkan_kei[13]);   // 広告宣伝費
                $input_data[15]  = str_replace(',','',$view_bkan_kei[14]);   // 求人費
                $input_data[16]  = str_replace(',','',$view_bkan_kei[15]);   // 運賃荷造費
                $input_data[17]  = str_replace(',','',$view_bkan_kei[16]);   // 図書教育費
                $input_data[18]  = str_replace(',','',$view_bkan_kei[17]);   // 業務委託費
                $input_data[19]  = str_replace(',','',$view_bkan_kei[35]);   // 事業等
                $input_data[20]  = str_replace(',','',$view_bkan_kei[18]);   // 諸税公課
                $input_data[21]  = str_replace(',','',$view_bkan_kei[19]);   // 試験研究費
                $input_data[22]  = str_replace(',','',$view_bkan_kei[20]);   // 雑費
                $input_data[23]  = str_replace(',','',$view_bkan_kei[21]);   // 修繕費
                $input_data[24]  = str_replace(',','',$view_bkan_kei[22]);   // 保証修理費
                $input_data[25]  = str_replace(',','',$view_bkan_kei[23]);   // 事務用消耗品費
                $input_data[26]  = str_replace(',','',$view_bkan_kei[24]);   // 工場消耗品費
                $input_data[27]  = str_replace(',','',$view_bkan_kei[25]);   // 車両費
                $input_data[28]  = str_replace(',','',$view_bkan_kei[26]);   // 保険料
                $input_data[29]  = str_replace(',','',$view_bkan_kei[27]);   // 水道光熱費
                $input_data[30]  = str_replace(',','',$view_bkan_kei[28]);   // 諸会費
                $input_data[31]  = str_replace(',','',$view_bkan_kei[29]);   // 支払手数料
                $input_data[32]  = str_replace(',','',$view_bkan_kei[30]);   // 地代家賃
                $input_data[33]  = str_replace(',','',$view_bkan_kei[31]);   // 寄付金
                $input_data[34]  = str_replace(',','',$view_bkan_kei[32]);   // 倉敷料
                $input_data[35]  = str_replace(',','',$view_bkan_kei[33]);   // 賃借料
                $input_data[36]  = str_replace(',','',$view_bkan_kei[34]);   // 減価償却費
                $input_data[37]  = str_replace(',','',$view_bkan_kei[36]);   // クレーム対応費
                $input_data[38]  = str_replace(',','',$view_bkan_kei_all);   // 経費計
                $input_data[39]  = str_replace(',','',$view_bkan_sum);   // 合計
                
                $head  = "商管製造経費";
                
            } elseif ($i == 5) {        // 商管販管費
                $input_data = array();
                $input_data[0]   = str_replace(',','',$view_bhan_jin[0]);   // 役員報酬
                $input_data[1]   = str_replace(',','',$view_bhan_jin[1]);   // 給料手当
                $input_data[2]   = str_replace(',','',$view_bhan_jin[2]);   // 賞与手当
                $input_data[3]   = str_replace(',','',$view_bhan_jin[3]);   // 顧問料
                $input_data[4]   = str_replace(',','',$view_bhan_jin[4]);   // 法定福利
                $input_data[5]   = str_replace(',','',$view_bhan_jin[5]);   // 厚生福利費
                $input_data[6]   = str_replace(',','',$view_bhan_jin[6]);   // 賞与引当金繰入
                $input_data[7]   = str_replace(',','',$view_bhan_jin[7]);   // 退職給付費用
                $input_data[8]   = str_replace(',','',$view_bhan_jin_all);   // 人件費計
                $input_data[9]   = str_replace(',','',$view_bhan_kei[8]);   // 旅費交通費
                $input_data[10]  = str_replace(',','',$view_bhan_kei[9]);   // 海外出張
                $input_data[11]  = str_replace(',','',$view_bhan_kei[10]);   // 通信費
                $input_data[12]  = str_replace(',','',$view_bhan_kei[11]);   // 会議費
                $input_data[13]  = str_replace(',','',$view_bhan_kei[12]);   // 交際接待費
                $input_data[14]  = str_replace(',','',$view_bhan_kei[13]);   // 広告宣伝費
                $input_data[15]  = str_replace(',','',$view_bhan_kei[14]);   // 求人費
                $input_data[16]  = str_replace(',','',$view_bhan_kei[15]);   // 運賃荷造費
                $input_data[17]  = str_replace(',','',$view_bhan_kei[16]);   // 図書教育費
                $input_data[18]  = str_replace(',','',$view_bhan_kei[17]);   // 業務委託費
                $input_data[19]  = str_replace(',','',$view_bhan_kei[35]);   // 事業等
                $input_data[20]  = str_replace(',','',$view_bhan_kei[18]);   // 諸税公課
                $input_data[21]  = str_replace(',','',$view_bhan_kei[19]);   // 試験研究費
                $input_data[22]  = str_replace(',','',$view_bhan_kei[20]);   // 雑費
                $input_data[23]  = str_replace(',','',$view_bhan_kei[21]);   // 修繕費
                $input_data[24]  = str_replace(',','',$view_bhan_kei[22]);   // 保証修理費
                $input_data[25]  = str_replace(',','',$view_bhan_kei[23]);   // 事務用消耗品費
                $input_data[26]  = str_replace(',','',$view_bhan_kei[24]);   // 工場消耗品費
                $input_data[27]  = str_replace(',','',$view_bhan_kei[25]);   // 車両費
                $input_data[28]  = str_replace(',','',$view_bhan_kei[26]);   // 保険料
                $input_data[29]  = str_replace(',','',$view_bhan_kei[27]);   // 水道光熱費
                $input_data[30]  = str_replace(',','',$view_bhan_kei[28]);   // 諸会費
                $input_data[31]  = str_replace(',','',$view_bhan_kei[29]);   // 支払手数料
                $input_data[32]  = str_replace(',','',$view_bhan_kei[30]);   // 地代家賃
                $input_data[33]  = str_replace(',','',$view_bhan_kei[31]);   // 寄付金
                $input_data[34]  = str_replace(',','',$view_bhan_kei[32]);   // 倉敷料
                $input_data[35]  = str_replace(',','',$view_bhan_kei[33]);   // 賃借料
                $input_data[36]  = str_replace(',','',$view_bhan_kei[34]);   // 減価償却費
                $input_data[37]  = str_replace(',','',$view_bhan_kei[36]);   // クレーム対応費
                $input_data[38]  = str_replace(',','',$view_bhan_kei_all);   // 経費計
                $input_data[39]  = str_replace(',','',$view_bhan_sum);   // 合計
                
                $head  = "商管販管費";
                
            }
        } else {
            if ($i == 0) {
                $c = 0;             // カプラ製造経費
            } elseif ($i == 1) {
                $c = 1;             // リニア製造経費
            } elseif ($i == 3) {
                $c = 10;            // カプラ販管費
            } elseif ($i == 4) {
                $c = 11;            // リニア販管費
            }
            $input_data = array();
            $input_data[0]   = str_replace(',','',$view_data[0][$c]);   // 役員報酬
            $input_data[1]   = str_replace(',','',$view_data[1][$c]);   // 給料手当
            $input_data[2]   = str_replace(',','',$view_data[2][$c]);   // 賞与手当
            $input_data[3]   = str_replace(',','',$view_data[3][$c]);   // 顧問料
            $input_data[4]   = str_replace(',','',$view_data[4][$c]);   // 法定福利
            $input_data[5]   = str_replace(',','',$view_data[5][$c]);   // 厚生福利費
            $input_data[6]   = str_replace(',','',$view_data[6][$c]);   // 賞与引当金繰入
            $input_data[7]   = str_replace(',','',$view_data[7][$c]);   // 退職給付費用
            $input_data[8]   = str_replace(',','',$view_jin_sum[$c]);   // 人件費計
            $input_data[9]   = str_replace(',','',$view_data[8][$c]);   // 旅費交通費
            $input_data[10]  = str_replace(',','',$view_data[9][$c]);   // 海外出張
            $input_data[11]  = str_replace(',','',$view_data[10][$c]);   // 通信費
            $input_data[12]  = str_replace(',','',$view_data[11][$c]);   // 会議費
            $input_data[13]  = str_replace(',','',$view_data[12][$c]);   // 交際接待費
            $input_data[14]  = str_replace(',','',$view_data[13][$c]);   // 広告宣伝費
            $input_data[15]  = str_replace(',','',$view_data[14][$c]);   // 求人費
            $input_data[16]  = str_replace(',','',$view_data[15][$c]);   // 運賃荷造費
            $input_data[17]  = str_replace(',','',$view_data[16][$c]);   // 図書教育費
            $input_data[18]  = str_replace(',','',$view_data[17][$c]);   // 業務委託費
            $input_data[19]  = str_replace(',','',$view_data[35][$c]);   // 事業等
            $input_data[20]  = str_replace(',','',$view_data[18][$c]);   // 諸税公課
            $input_data[21]  = str_replace(',','',$view_data[19][$c]);   // 試験研究費
            $input_data[22]  = str_replace(',','',$view_data[20][$c]);   // 雑費
            $input_data[23]  = str_replace(',','',$view_data[21][$c]);   // 修繕費
            $input_data[24]  = str_replace(',','',$view_data[22][$c]);   // 保証修理費
            $input_data[25]  = str_replace(',','',$view_data[23][$c]);   // 事務用消耗品費
            $input_data[26]  = str_replace(',','',$view_data[24][$c]);   // 工場消耗品費
            $input_data[27]  = str_replace(',','',$view_data[25][$c]);   // 車両費
            $input_data[28]  = str_replace(',','',$view_data[26][$c]);   // 保険料
            $input_data[29]  = str_replace(',','',$view_data[27][$c]);   // 水道光熱費
            $input_data[30]  = str_replace(',','',$view_data[28][$c]);   // 諸会費
            $input_data[31]  = str_replace(',','',$view_data[29][$c]);   // 支払手数料
            $input_data[32]  = str_replace(',','',$view_data[30][$c]);   // 地代家賃
            $input_data[33]  = str_replace(',','',$view_data[31][$c]);   // 寄付金
            $input_data[34]  = str_replace(',','',$view_data[32][$c]);   // 倉敷料
            $input_data[35]  = str_replace(',','',$view_data[33][$c]);   // 賃借料
            $input_data[36]  = str_replace(',','',$view_data[34][$c]);   // 減価償却費
            $input_data[37]  = str_replace(',','',$view_data[36][$c]);   // クレーム対応費
            $input_data[38]  = str_replace(',','',$view_kei_sum[$c]);   // 経費計
            $input_data[39]  = str_replace(',','',$view_all_sum[$c]);   // 合計
            if ($i == 0) {
                $head  = "カプラ製造経費";    // カプラ製造経費
            } elseif ($i == 1) {
                $head  = "リニア製造経費";    // リニア製造経費
            } elseif ($i == 3) {
                $head  = "カプラ販管費";      // カプラ販管費
            } elseif ($i == 4) {
                $head  = "リニア販管費";      // リニア販管費
            }
        }
        insert_date($item,$head,$yyyymm,$input_data);
    }
}
function insert_date($item,$head,$yyyymm,$input_data) 
{
    for ($i = 0; $i < 40; $i++) {
        //$item_in     = array();
        //$item_in[$i] = $item[$i];
        //$input_data[$i][$sec] = str_replace(',','',$input_data[$i][$sec]);
        $item_in[$i] = $head . $item[$i];
        $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into profit_loss_keihi_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 貸借対照表データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_keihi_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 貸借対照表データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "当月のデータを登録しました。";
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<style type='text/css'>
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
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='13' bgcolor='#d6d3ce' align='right' class='pt10'>
                        単位
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>　千円</option>\n";
                            else
                                echo "<option value='1000'>　千円</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>　　円</option>\n";
                            else
                                echo "<option value='1'>　　円</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>百万円</option>\n";
                            else
                                echo "<option value='1000000'>百万円</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>　万円</option>\n";
                            else
                                echo "<option value='10000'>　万円</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>十万円</option>\n";
                            else
                                echo "<option value='100000'>十万円</option>\n";
                        ?>
                        </select>
                        少数桁
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>０桁</option>\n";
                            else
                                echo "<option value='0'>０桁</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>３桁</option>\n";
                            else
                                echo "<option value='3'>３桁</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>６桁</option>\n";
                            else
                                echo "<option value='6'>６桁</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>１桁</option>\n";
                            else
                                echo "<option value='1'>１桁</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>２桁</option>\n";
                            else
                                echo "<option value='2'>２桁</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>４桁</option>\n";
                            else
                                echo "<option value='4'>４桁</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>５桁</option>\n";
                            else
                                echo "<option value='5'>５桁</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='単位変更'>
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='当月データ登録' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='当月データ登録' onClick='return data_input_click(this)' disabled>
                        <?php
                            }
                        }
                        ?>
                    </td>
                </form>
            </tr>
        </table>
        <!-- win_gray='#d6d3ce' -->
        <table width='100%' bgcolor='white' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td width='10' rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'>区分</td>
                    <td rowspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>勘定科目</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>当　月　の　製　造　経　費</td>
                    <td colspan='4' rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販売費及び一般管理費</td>
                </tr>
                <tr>
                    <td colspan='4' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>合　　　計</td>
                    <td colspan='3' nowrap align='center' class='pt10b'>直接経費</td>
                    <td colspan='4' nowrap align='center' class='pt10b'>間接経費</td>
                    <td rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>合　計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>カプラ</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>リニア</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>商　管</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>合　計</td>
                    <td nowrap align='center' class='pt10b'>カプラ</td>
                    <td nowrap align='center' class='pt10b'>リニア</td>
                    <td nowrap align='center' class='pt10b'>合　計</td>
                    <td nowrap align='center' class='pt10b'>カプラ</td>
                    <td nowrap align='center' class='pt10b'>リニア</td>
                    <td nowrap align='center' class='pt10b'>商　管</td>
                    <td nowrap align='center' class='pt10b'>合　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>カプラ</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>リニア</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>商　管</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>合　計</td>
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>売上</td>
                    <td nowrap class='pt10'>カプラ</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 商  管 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>リニア</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>         <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>         <!-- 商  管 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- カプラ -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>売上比</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- 商  管 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>     <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu ?>  </td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10b' bgcolor='#ffffc6'>売上計</td>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>     <!-- カプラ -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 商  管 -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>     <!-- 合計 -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>材料</td>
                    <td nowrap class='pt10'>仕入材料</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 商  管 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_shiire ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>製造原価材料</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 商  管 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>材料比率</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 商  管 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>差額</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_barance ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>材料計</td>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- カプラ -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 商  管 -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- カプラ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- リニア -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 間接費 -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- 合計 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>　</td>       <!-- 余白 販管費 -->
                </tr>
                <tr>
                    <td width='10' rowspan='<?= $rec_jin+1 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>人件費</td>
                    <TD nowrap class='pt10'>役員報酬</TD>
                    <?php
                        $r = 0;     // 該当レコード 水色 #b4ffff
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>給料手当</TD>
                    <?php
                        $r = 1;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与手当</TD>
                    <?php
                        $r = 2;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>顧問料</TD>
                    <?php
                        $r = 3;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>法定福利費</TD>
                    <?php
                        $r = 4;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>厚生福利費</TD>
                    <?php
                        $r = 5;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与引当金繰入</TD>
                    <?php
                        $r = 6;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>退職給付費用</TD>
                    <?php
                        $r = 7;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>人件費計</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 2) {                                // 商品管理分入れ込み
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin_all);
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_jin_sum[$c]);
                            } elseif ($c == 8) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_jin_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                            } elseif ($c == 12) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bhan_jin_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                            } else {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                            }
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='<?= $rec_kei+1 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>経費</td>
                    <TD nowrap class='pt10'>旅費交通費</TD>
                    <?php
                        $r = 8;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>海外出張</TD>
                    <?php
                    $r = 9;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>通　信　費</TD>
                    <?php
                    $r = 10;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>会　議　費</TD>
                    <?php
                    $r = 11;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>交際接待費</TD>
                    <?php
                    $r = 12;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>広告宣伝費</TD>
                    <?php
                    $r = 13;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>求　人　費</TD>
                    <?php
                    $r = 14;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>運賃荷造費</TD>
                    <?php
                    $r = 15;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>図書教育費</TD>
                    <?php
                    $r = 16;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>業務委託費</TD>
                    <?php
                    $r = 17;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>事　業　等</td>
                    <?php
                    $r = 35;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸税公課</TD>
                    <?php
                    $r = 18;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>試験研究費</TD>
                    <?php
                    $r = 19;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>雑　　　費</TD>
                    <?php
                    $r = 20;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>修　繕　費</TD>
                    <?php
                    $r = 21;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保証修理費</TD>
                    <?php
                    $r = 22;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>事務用消耗品費</TD>
                    <?php
                    $r = 23;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>工場消耗品費</TD>
                    <?php
                    $r = 24;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>車　両　費</TD>
                    <?php
                    $r = 25;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保　険　料</TD>
                    <?php
                    $r = 26;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>水道光熱費</TD>
                    <?php
                    $r = 27;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸　会　費</TD>
                    <?php
                    $r = 28;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>支払手数料</TD>
                    <?php
                    $r = 29;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>地代家賃</TD>
                    <?php
                    $r = 30;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>寄　付　金</TD>
                    <?php
                    $r = 31;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>倉　敷　料</TD>
                    <?php
                    $r = 32;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>賃　借　料</TD>
                    <?php
                    $r = 33;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>減価償却費</TD>
                    <?php
                    $r = 34;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>クレーム対応費</TD>
                    <?php
                    $r = 36;     // 該当レコード
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // 製造経費 カプラ リニア 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // 商品管理分入れ込み
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // 販管費 カプラ リニア
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // 販管費 商品管理 合計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>経費計</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 2) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_kei_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            } elseif ($c == 8) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_kei_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            } elseif ($c == 12) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bhan_kei_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            } else {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>合　計</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 2) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_sum);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            } elseif ($c == 8) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_sum);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            } elseif ($c == 12) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bhan_sum);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            } else {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            }
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
