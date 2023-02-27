<?php
//////////////////////////////////////////////////////////////////////////////
// 月次 比較棚卸表 照会                                                     //
// Copyright (C) 2003-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
//        with patTemplate templates/getsuji_comp_invent.templ.html         //
// Changed history                                                          //
// 2003/07/29 Created  getsuji_comp_invent.php            php-4.3.3rc2      //
// 2003/09/29 テーブルを act_comp_invent_history で query php-4.3.3         //
// 2003/10/16 patTemplate に代入するハッシュ配列を tbody?[]にまとめた       //
// 2003/10/16 単位変更が出来る様にロジック追加 <option {selected}>を使用    //
// 2004/01/08 四捨五入が合わないため資材部品明細の合計に以下のロジックを追加//
//             $tbody2['tbody2_ckeip'] = $pmonth['カプラ資材部品'];リニアも //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタン{PAGE_SITE_VIEW}を追加 //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/10/10 getsuji_comp_invent.php → invent_comp/invent_comp_view.phpへ //
// 2010/02/09 201001よりgetsuji_comp_invent_201001.templ.htmlを使用    大谷 //
//            $rowsの数が変わった為、各データの格納時の$rの範囲を変更       //
// 2015/06/01 201504より機工追加。実際は201505からだが、データ取得の関係で  //
//            201504より追加                                           大谷 //
// 2016/04/14 201604より機工の目標を追加                               大谷 //
// 2020/02/06 202001より部品明細にDPを追加。その他からの分離。              //
//            201912も更新したので合わせて表示がずれない様に変更       大谷 //
// 2020/04/07 単位で四捨五入していた為、合計がずれていたので修正       大谷 //
// 2021/05/07 202104より機工除外                                       大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL || E_STRICT);
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

///// 呼出もとの URL を取得
$url_referer     = $_SESSION['pl_referer'];
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

/********** Logic Start **********/
///////////// サイトメニュー On / Off 
if ($_SESSION['site_view'] == 'on') {
    $site_view = 'MenuOFF';
} else {
    $site_view = 'MenuON';
}

//////////////// サイトメニューのＵＲＬ設定 & JavaScript生成
$menu_site_url = 'http:' . WEB_HOST . 'menu_site.php';
$menu_site_script =
"<script language='JavaScript'>
<!--
    parent.menu_site.location = '$menu_site_url';
// -->
</script>";
$menu_site_script = "";         // 月次メニューのため使わない

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid("target");

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
$tuki = $tuki + 1 -1;   // 数値データに変換(09を9にしたいため)キャストでもいいのだが

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月末　比 較 棚 卸 表");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月 これはとりあえず使わない
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 期初年月

///// 表示単位を設定取得
if (isset($_POST['comp_tani'])) {
    $_SESSION['comp_tani'] = $_POST['comp_tani'];
    $tani = $_SESSION['comp_tani'];
} elseif (isset($_SESSION['comp_tani'])) {
    $tani = $_SESSION['comp_tani'];
} else {
    $tani = 1000000;        // 初期値 表示単位 百万円
    $_SESSION['comp_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['comp_keta'])) {
    $_SESSION['comp_keta'] = $_POST['comp_keta'];
    $keta = $_SESSION['comp_keta'];
} elseif (isset($_SESSION['comp_keta'])) {
    $keta = $_SESSION['comp_keta'];
} else {
    $keta = 1;          // 初期値 小数点以下桁数
    $_SESSION['comp_keta'] = $keta;
}
// $keta = 1;              // 比較棚卸表では小数点以下は1に固定しようと思ったがしない。


///// act_comp_invent_history よりデータ取得
    ///// 当月
$month = array();
$query = "select item, kin from act_comp_invent_history where invent_ym=$yyyymm";
if (($rows = getResult2($query, $month)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("比較棚卸表のデータなし！<br>第 %d期 %d月",$ki,$tuki);
    header("Location: $url_referer");
    exit();
} else {
    ///// item の名前と金額を指定の単位と少数桁数でハッシュへ代入
    for ($r=0; $r<$rows; $r++) {
        //$month["{$month[$r][0]}"] = Uround($month[$r][1] / $tani, $keta);
        $month["{$month[$r][0]}"] = $month[$r][1] / $tani;
    }
    ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
    $buhin_c = $month['カプラ資材部品']+$month['カプラ原材料']+$month['カプラ工作仕掛']+
                $month['カプラ検査仕掛']+$month['カプラ外注仕掛']+$month['カプラＣＣ部品'];
    $sum_c = $buhin_c + $month['カプラ組立仕掛'];
    $sag_c = $month['カプラ財務会計'] - $sum_c;
    
    /////////////////////////////////////////////////////////////////////// カプラ START
    ///// 各棚卸金額を３桁カンマでハッシュへ代入
    $tbody['tbody_kum_c'] = number_format($month['カプラ組立仕掛'], $keta);
    $tbody['tbody_siz_c'] = number_format($month['カプラ資材部品'], $keta);
    $tbody['tbody_gen_c'] = number_format($month['カプラ原材料']  , $keta);
    $tbody['tbody_kou_c'] = number_format($month['カプラ工作仕掛'], $keta);
    $tbody['tbody_ken_c'] = number_format($month['カプラ検査仕掛'], $keta);
    $tbody['tbody_gai_c'] = number_format($month['カプラ外注仕掛'], $keta);
    $tbody['tbody_cc_c']  = number_format($month['カプラＣＣ部品'], $keta);
    $tbody['tbody_zai_c'] = number_format($month['カプラ財務会計'], $keta);
    ///// 計算結果をハッシュへ代入
    //$tbody['tbody_buh_c'] = number_format($buhin_c, $keta);
    $tbody['tbody_buh_c'] = number_format($buhin_c, $keta);
    $tbody['tbody_gou_c'] = number_format($sum_c, $keta);
    $tbody['tbody_sag_c'] = number_format($sag_c, $keta);
    /////////////////////////////////////////////////////////////////////// カプラ END

    /////////////////////////////////////////////////////////////////////// リニア START
    ///// 各棚卸金額を３桁カンマでハッシュへ代入
    $tbody['tbody_kum_l'] = number_format($month['リニア組立仕掛'], $keta);
    $tbody['tbody_siz_l'] = number_format($month['リニア資材部品'], $keta);
    $tbody['tbody_gen_l'] = number_format($month['リニア原材料']  , $keta);
    $tbody['tbody_kou_l'] = number_format($month['リニア工作仕掛'], $keta);
    $tbody['tbody_ken_l'] = number_format($month['リニア検査仕掛'], $keta);
    $tbody['tbody_gai_l'] = number_format($month['リニア外注仕掛'], $keta);
    $tbody['tbody_cc_l']  = number_format($month['リニアＣＣ部品'], $keta);
    $tbody['tbody_zai_l'] = number_format($month['リニア財務会計'], $keta);
    ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
    $buhin_l = $month['リニア資材部品']+$month['リニア原材料']+$month['リニア工作仕掛']+
                $month['リニア検査仕掛']+$month['リニア外注仕掛']+$month['リニアＣＣ部品'];
    $sum_l = $buhin_l + $month['リニア組立仕掛'];
    $sag_l = $month['リニア財務会計'] - $sum_l;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_buh_l'] = number_format($buhin_l, $keta);
    $tbody['tbody_gou_l'] = number_format($sum_l, $keta);
    $tbody['tbody_sag_l'] = number_format($sag_l, $keta);
    /////////////////////////////////////////////////////////////////////// リニア END
    if ($yyyymm >= 201504 && $yyyymm <= 202103) {
        /////////////////////////////////////////////////////////////////////// ツール START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kum_t'] = number_format($month['ツール組立仕掛'], $keta);
        $tbody['tbody_siz_t'] = number_format($month['ツール資材部品'], $keta);
        $tbody['tbody_gen_t'] = number_format($month['ツール原材料']  , $keta);
        $tbody['tbody_kou_t'] = number_format($month['ツール工作仕掛'], $keta);
        $tbody['tbody_ken_t'] = number_format($month['ツール検査仕掛'], $keta);
        $tbody['tbody_gai_t'] = number_format($month['ツール外注仕掛'], $keta);
        $tbody['tbody_cc_t']  = number_format($month['ツールＣＣ部品'], $keta);
        $tbody['tbody_zai_t'] = number_format($month['ツール財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $buhin_t = $month['ツール資材部品']+$month['ツール原材料']+$month['ツール工作仕掛']+
                    $month['ツール検査仕掛']+$month['ツール外注仕掛']+$month['ツールＣＣ部品'];
        $sum_t = $buhin_t + $month['ツール組立仕掛'];
        $sag_t = $month['ツール財務会計'] - $sum_t;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buh_t'] = number_format($buhin_t, $keta);
        $tbody['tbody_gou_t'] = number_format($sum_t, $keta);
        $tbody['tbody_sag_t'] = number_format($sag_t, $keta);
        /////////////////////////////////////////////////////////////////////// ツール END
        
        /////////////////////////////////////////////////////////////////////// 全体 START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kum_a'] = number_format($month['カプラ組立仕掛'] + $month['リニア組立仕掛'] + $month['ツール組立仕掛'], $keta);
        $tbody['tbody_siz_a'] = number_format($month['カプラ資材部品'] + $month['リニア資材部品'] + $month['ツール資材部品'], $keta);
        $tbody['tbody_gen_a'] = number_format($month['カプラ原材料']   + $month['リニア原材料']   + $month['ツール原材料']  , $keta);
        $tbody['tbody_kou_a'] = number_format($month['カプラ工作仕掛'] + $month['リニア工作仕掛'] + $month['ツール工作仕掛'], $keta);
        $tbody['tbody_ken_a'] = number_format($month['カプラ検査仕掛'] + $month['リニア検査仕掛'] + $month['ツール検査仕掛'], $keta);
        $tbody['tbody_gai_a'] = number_format($month['カプラ外注仕掛'] + $month['リニア外注仕掛'] + $month['ツール外注仕掛'], $keta);
        $tbody['tbody_cc_a']  = number_format($month['カプラＣＣ部品'] + $month['リニアＣＣ部品'] + $month['ツールＣＣ部品'], $keta);
        $tbody['tbody_zai_a'] = number_format($month['カプラ財務会計'] + $month['リニア財務会計'] + $month['ツール財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $buhin_a = $buhin_c + $buhin_l + $buhin_t;
        $sum_a = $buhin_a + $month['カプラ組立仕掛'] + $month['リニア組立仕掛'] + $month['ツール組立仕掛'];
        $sag_a = ($month['カプラ財務会計'] + $month['リニア財務会計'] + $month['ツール財務会計']) - $sum_a;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buh_a'] = number_format($buhin_a, $keta);
        $tbody['tbody_gou_a'] = number_format($sum_a, $keta);
        $tbody['tbody_sag_a'] = number_format($sag_a, $keta);
        /////////////////////////////////////////////////////////////////////// 全体 END
    } else {
        /////////////////////////////////////////////////////////////////////// 全体 START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kum_a'] = number_format($month['カプラ組立仕掛'] + $month['リニア組立仕掛'], $keta);
        $tbody['tbody_siz_a'] = number_format($month['カプラ資材部品'] + $month['リニア資材部品'], $keta);
        $tbody['tbody_gen_a'] = number_format($month['カプラ原材料']   + $month['リニア原材料']  , $keta);
        $tbody['tbody_kou_a'] = number_format($month['カプラ工作仕掛'] + $month['リニア工作仕掛'], $keta);
        $tbody['tbody_ken_a'] = number_format($month['カプラ検査仕掛'] + $month['リニア検査仕掛'], $keta);
        $tbody['tbody_gai_a'] = number_format($month['カプラ外注仕掛'] + $month['リニア外注仕掛'], $keta);
        $tbody['tbody_cc_a']  = number_format($month['カプラＣＣ部品'] + $month['リニアＣＣ部品'], $keta);
        $tbody['tbody_zai_a'] = number_format($month['カプラ財務会計'] + $month['リニア財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $buhin_a = $buhin_c + $buhin_l;
        $sum_a = $buhin_a + $month['カプラ組立仕掛'] + $month['リニア組立仕掛'];
        $sag_a = ($month['カプラ財務会計'] + $month['リニア財務会計']) - $sum_a;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buh_a'] = number_format($buhin_a, $keta);
        $tbody['tbody_gou_a'] = number_format($sum_a, $keta);
        $tbody['tbody_sag_a'] = number_format($sag_a, $keta);
        /////////////////////////////////////////////////////////////////////// 全体 END
    }
}

    ///// 前月
$pmonth = array();
$query = "select item, kin from act_comp_invent_history where invent_ym=$p1_ym";
if (($prows = getResult2($query, $pmonth)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("比較棚卸表の前月データなし！<br>%d", $p1_ym);
    header("Location: $url_referer");
    exit();
} else {
    ///// item の名前と金額を指定の単位と少数桁数でハッシュへ代入
    for ($r=0; $r<$prows; $r++) {
        //$pmonth["{$pmonth[$r][0]}"] = Uround($pmonth[$r][1] / $tani, $keta);
        $pmonth["{$pmonth[$r][0]}"] = $pmonth[$r][1] / $tani;
    }
    /////////////////////////////////////////////////////////////////////// カプラ START
    ///// 各棚卸金額を３桁カンマでハッシュへ代入
    $tbody['tbody_kump_c'] = number_format($pmonth['カプラ組立仕掛'], $keta);
    $tbody['tbody_sizp_c'] = number_format($pmonth['カプラ資材部品'], $keta);
    $tbody['tbody_genp_c'] = number_format($pmonth['カプラ原材料']  , $keta);
    $tbody['tbody_koup_c'] = number_format($pmonth['カプラ工作仕掛'], $keta);
    $tbody['tbody_kenp_c'] = number_format($pmonth['カプラ検査仕掛'], $keta);
    $tbody['tbody_gaip_c'] = number_format($pmonth['カプラ外注仕掛'], $keta);
    $tbody['tbody_ccp_c']  = number_format($pmonth['カプラＣＣ部品'], $keta);
    $tbody['tbody_zaip_c'] = number_format($pmonth['カプラ財務会計'], $keta);
    ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
    $pbuhin_c = $pmonth['カプラ資材部品']+$pmonth['カプラ原材料']+$pmonth['カプラ工作仕掛']+
                $pmonth['カプラ検査仕掛']+$pmonth['カプラ外注仕掛']+$pmonth['カプラＣＣ部品'];
    $psum_c = $pbuhin_c + $pmonth['カプラ組立仕掛'];
    $psag_c = $pmonth['カプラ財務会計'] - $psum_c;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_buhp_c'] = number_format($pbuhin_c, $keta);
    $tbody['tbody_goup_c'] = number_format($psum_c, $keta);
    $tbody['tbody_sagp_c'] = number_format($psag_c, $keta);
    /////////////////////////////////////////////////////////////////////// カプラ END

    /////////////////////////////////////////////////////////////////////// リニア START
    ///// 各棚卸金額を３桁カンマでハッシュへ代入
    $tbody['tbody_kump_l'] = number_format($pmonth['リニア組立仕掛'], $keta);
    $tbody['tbody_sizp_l'] = number_format($pmonth['リニア資材部品'], $keta);
    $tbody['tbody_genp_l'] = number_format($pmonth['リニア原材料']  , $keta);
    $tbody['tbody_koup_l'] = number_format($pmonth['リニア工作仕掛'], $keta);
    $tbody['tbody_kenp_l'] = number_format($pmonth['リニア検査仕掛'], $keta);
    $tbody['tbody_gaip_l'] = number_format($pmonth['リニア外注仕掛'], $keta);
    $tbody['tbody_ccp_l']  = number_format($pmonth['リニアＣＣ部品'], $keta);
    $tbody['tbody_zaip_l'] = number_format($pmonth['リニア財務会計'], $keta);
    ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
    $pbuhin_l = $pmonth['リニア資材部品']+$pmonth['リニア原材料']+$pmonth['リニア工作仕掛']+
                $pmonth['リニア検査仕掛']+$pmonth['リニア外注仕掛']+$pmonth['リニアＣＣ部品'];
    $psum_l = $pbuhin_l + $pmonth['リニア組立仕掛'];
    $psag_l = $pmonth['リニア財務会計'] - $psum_l;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_buhp_l'] = number_format($pbuhin_l, $keta);
    $tbody['tbody_goup_l'] = number_format($psum_l, $keta);
    $tbody['tbody_sagp_l'] = number_format($psag_l, $keta);
    /////////////////////////////////////////////////////////////////////// リニア END
    if ($p1_ym >= 201504 || $p1_ym <= 202103) {
        /////////////////////////////////////////////////////////////////////// ツール START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kump_t'] = number_format($pmonth['ツール組立仕掛'], $keta);
        $tbody['tbody_sizp_t'] = number_format($pmonth['ツール資材部品'], $keta);
        $tbody['tbody_genp_t'] = number_format($pmonth['ツール原材料']  , $keta);
        $tbody['tbody_koup_t'] = number_format($pmonth['ツール工作仕掛'], $keta);
        $tbody['tbody_kenp_t'] = number_format($pmonth['ツール検査仕掛'], $keta);
        $tbody['tbody_gaip_t'] = number_format($pmonth['ツール外注仕掛'], $keta);
        $tbody['tbody_ccp_t']  = number_format($pmonth['ツールＣＣ部品'], $keta);
        $tbody['tbody_zaip_t'] = number_format($pmonth['ツール財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $pbuhin_t = $pmonth['ツール資材部品']+$pmonth['ツール原材料']+$pmonth['ツール工作仕掛']+
                    $pmonth['ツール検査仕掛']+$pmonth['ツール外注仕掛']+$pmonth['ツールＣＣ部品'];
        $psum_t = $pbuhin_t + $pmonth['ツール組立仕掛'];
        $psag_t = $pmonth['ツール財務会計'] - $psum_t;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buhp_t'] = number_format($pbuhin_t, $keta);
        $tbody['tbody_goup_t'] = number_format($psum_t, $keta);
        $tbody['tbody_sagp_t'] = number_format($psag_t, $keta);
        /////////////////////////////////////////////////////////////////////// ツール END
        
        /////////////////////////////////////////////////////////////////////// 全体 START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kump_a'] = number_format($pmonth['カプラ組立仕掛'] + $pmonth['リニア組立仕掛'] + $pmonth['ツール組立仕掛'], $keta);
        $tbody['tbody_sizp_a'] = number_format($pmonth['カプラ資材部品'] + $pmonth['リニア資材部品'] + $pmonth['ツール資材部品'], $keta);
        $tbody['tbody_genp_a'] = number_format($pmonth['カプラ原材料']   + $pmonth['リニア原材料']   + $pmonth['ツール原材料']  , $keta);
        $tbody['tbody_koup_a'] = number_format($pmonth['カプラ工作仕掛'] + $pmonth['リニア工作仕掛'] + $pmonth['ツール工作仕掛'], $keta);
        $tbody['tbody_kenp_a'] = number_format($pmonth['カプラ検査仕掛'] + $pmonth['リニア検査仕掛'] + $pmonth['ツール検査仕掛'], $keta);
        $tbody['tbody_gaip_a'] = number_format($pmonth['カプラ外注仕掛'] + $pmonth['リニア外注仕掛'] + $pmonth['ツール外注仕掛'], $keta);
        $tbody['tbody_ccp_a']  = number_format($pmonth['カプラＣＣ部品'] + $pmonth['リニアＣＣ部品'] + $pmonth['ツールＣＣ部品'], $keta);
        $tbody['tbody_zaip_a'] = number_format($pmonth['カプラ財務会計'] + $pmonth['リニア財務会計'] + $pmonth['ツール財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $pbuhin_a = $pbuhin_c + $pbuhin_l + $pbuhin_t;
        $psum_a = $pbuhin_a + $pmonth['カプラ組立仕掛'] + $pmonth['リニア組立仕掛'] + $pmonth['ツール組立仕掛'];
        $psag_a = ($pmonth['カプラ財務会計'] + $pmonth['リニア財務会計'] + $pmonth['ツール財務会計']) - $psum_a;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buhp_a'] = number_format($pbuhin_a, $keta);
        $tbody['tbody_goup_a'] = number_format($psum_a, $keta);
        $tbody['tbody_sagp_a'] = number_format($psag_a, $keta);
        /////////////////////////////////////////////////////////////////////// 全体 END
    } else {
        /////////////////////////////////////////////////////////////////////// 全体 START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kump_a'] = number_format($pmonth['カプラ組立仕掛'] + $pmonth['リニア組立仕掛'], $keta);
        $tbody['tbody_sizp_a'] = number_format($pmonth['カプラ資材部品'] + $pmonth['リニア資材部品'], $keta);
        $tbody['tbody_genp_a'] = number_format($pmonth['カプラ原材料']   + $pmonth['リニア原材料']  , $keta);
        $tbody['tbody_koup_a'] = number_format($pmonth['カプラ工作仕掛'] + $pmonth['リニア工作仕掛'], $keta);
        $tbody['tbody_kenp_a'] = number_format($pmonth['カプラ検査仕掛'] + $pmonth['リニア検査仕掛'], $keta);
        $tbody['tbody_gaip_a'] = number_format($pmonth['カプラ外注仕掛'] + $pmonth['リニア外注仕掛'], $keta);
        $tbody['tbody_ccp_a']  = number_format($pmonth['カプラＣＣ部品'] + $pmonth['リニアＣＣ部品'], $keta);
        $tbody['tbody_zaip_a'] = number_format($pmonth['カプラ財務会計'] + $pmonth['リニア財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $pbuhin_a = $pbuhin_c + $pbuhin_l;
        $psum_a = $pbuhin_a + $pmonth['カプラ組立仕掛'] + $pmonth['リニア組立仕掛'];
        $psag_a = ($pmonth['カプラ財務会計'] + $pmonth['リニア財務会計']) - $psum_a;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buhp_a'] = number_format($pbuhin_a, $keta);
        $tbody['tbody_goup_a'] = number_format($psum_a, $keta);
        $tbody['tbody_sagp_a'] = number_format($psag_a, $keta);
        /////////////////////////////////////////////////////////////////////// 全体 END
    }
}

    ///// 前々月    とりあえず今は使わない
// $ppmonth = array();
// $query = "select item, kin from act_comp_invent_history where invent_ym=$p2_ym";
// $pprows = getResult2($query, $ppmonth);

    ///// 期首 (前期末の決算データ)
$kisyu_month = array();
$query = "select item, kin from act_comp_invent_history where invent_ym=$pre_end_ym";
if (($kisyu_rows = getResult2($query, $kisyu_month)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("比較棚卸表の期首データなし！<br>%d", $pre_end_ym);
    header("Location: $url_referer");
    exit();
} else {
    ///// item の名前と金額を指定の単位と少数桁数でハッシュへ代入
    for ($r=0; $r<$kisyu_rows; $r++) {
        //$kisyu_month["{$kisyu_month[$r][0]}"] = Uround($kisyu_month[$r][1] / $tani, $keta);
        $kisyu_month["{$kisyu_month[$r][0]}"] = $kisyu_month[$r][1] / $tani;
    }
    /////////////////////////////////////////////////////////////////////// カプラ START
    ///// 各棚卸金額を３桁カンマでハッシュへ代入
    $tbody['tbody_kum3_c'] = number_format($kisyu_month['カプラ組立仕掛'], $keta);
    $tbody['tbody_siz3_c'] = number_format($kisyu_month['カプラ資材部品'], $keta);
    $tbody['tbody_gen3_c'] = number_format($kisyu_month['カプラ原材料']  , $keta);
    $tbody['tbody_kou3_c'] = number_format($kisyu_month['カプラ工作仕掛'], $keta);
    $tbody['tbody_ken3_c'] = number_format($kisyu_month['カプラ検査仕掛'], $keta);
    $tbody['tbody_gai3_c'] = number_format($kisyu_month['カプラ外注仕掛'], $keta);
    $tbody['tbody_cc3_c']  = number_format($kisyu_month['カプラＣＣ部品'], $keta);
    $tbody['tbody_zai3_c'] = number_format($kisyu_month['カプラ財務会計'], $keta);
    ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
    $buhin3_c = $kisyu_month['カプラ資材部品']+$kisyu_month['カプラ原材料']+$kisyu_month['カプラ工作仕掛']+
                $kisyu_month['カプラ検査仕掛']+$kisyu_month['カプラ外注仕掛']+$kisyu_month['カプラＣＣ部品'];
    $sum3_c = $buhin3_c + $kisyu_month['カプラ組立仕掛'];
    $sag3_c = $kisyu_month['カプラ財務会計'] - $sum3_c;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_buh3_c'] = number_format($buhin3_c, $keta);
    $tbody['tbody_gou3_c'] = number_format($sum3_c, $keta);
    $tbody['tbody_sag3_c'] = number_format($sag3_c, $keta);
    /////////////////////////////////////////////////////////////////////// カプラ END

    /////////////////////////////////////////////////////////////////////// リニア START
    ///// 各棚卸金額を３桁カンマでハッシュへ代入
    $tbody['tbody_kum3_l'] = number_format($kisyu_month['リニア組立仕掛'], $keta);
    $tbody['tbody_siz3_l'] = number_format($kisyu_month['リニア資材部品'], $keta);
    $tbody['tbody_gen3_l'] = number_format($kisyu_month['リニア原材料']  , $keta);
    $tbody['tbody_kou3_l'] = number_format($kisyu_month['リニア工作仕掛'], $keta);
    $tbody['tbody_ken3_l'] = number_format($kisyu_month['リニア検査仕掛'], $keta);
    $tbody['tbody_gai3_l'] = number_format($kisyu_month['リニア外注仕掛'], $keta);
    $tbody['tbody_cc3_l']  = number_format($kisyu_month['リニアＣＣ部品'], $keta);
    $tbody['tbody_zai3_l'] = number_format($kisyu_month['リニア財務会計'], $keta);
    ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
    $buhin3_l = $kisyu_month['リニア資材部品']+$kisyu_month['リニア原材料']+$kisyu_month['リニア工作仕掛']+
                $kisyu_month['リニア検査仕掛']+$kisyu_month['リニア外注仕掛']+$kisyu_month['リニアＣＣ部品'];
    $sum3_l = $buhin3_l + $kisyu_month['リニア組立仕掛'];
    $sag3_l = $kisyu_month['リニア財務会計'] - $sum3_l;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_buh3_l'] = number_format($buhin3_l, $keta);
    $tbody['tbody_gou3_l'] = number_format($sum3_l, $keta);
    $tbody['tbody_sag3_l'] = number_format($sag3_l, $keta);
    /////////////////////////////////////////////////////////////////////// リニア END
    if ($pre_end_ym >= 201504 || $pre_end_ym <= 202103) {
        /////////////////////////////////////////////////////////////////////// ツール START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kum3_t'] = number_format($kisyu_month['ツール組立仕掛'], $keta);
        $tbody['tbody_siz3_t'] = number_format($kisyu_month['ツール資材部品'], $keta);
        $tbody['tbody_gen3_t'] = number_format($kisyu_month['ツール原材料']  , $keta);
        $tbody['tbody_kou3_t'] = number_format($kisyu_month['ツール工作仕掛'], $keta);
        $tbody['tbody_ken3_t'] = number_format($kisyu_month['ツール検査仕掛'], $keta);
        $tbody['tbody_gai3_t'] = number_format($kisyu_month['ツール外注仕掛'], $keta);
        $tbody['tbody_cc3_t']  = number_format($kisyu_month['ツールＣＣ部品'], $keta);
        $tbody['tbody_zai3_t'] = number_format($kisyu_month['ツール財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $buhin3_t = $kisyu_month['ツール資材部品']+$kisyu_month['ツール原材料']+$kisyu_month['ツール工作仕掛']+
                    $kisyu_month['ツール検査仕掛']+$kisyu_month['ツール外注仕掛']+$kisyu_month['ツールＣＣ部品'];
        $sum3_t = $buhin3_t + $kisyu_month['ツール組立仕掛'];
        $sag3_t = $kisyu_month['ツール財務会計'] - $sum3_t;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buh3_t'] = number_format($buhin3_t, $keta);
        $tbody['tbody_gou3_t'] = number_format($sum3_t, $keta);
        $tbody['tbody_sag3_t'] = number_format($sag3_t, $keta);
        /////////////////////////////////////////////////////////////////////// ツール END
        
        /////////////////////////////////////////////////////////////////////// 全体 START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kum3_a'] = number_format($kisyu_month['カプラ組立仕掛'] + $kisyu_month['リニア組立仕掛'] + $kisyu_month['ツール組立仕掛'], $keta);
        $tbody['tbody_siz3_a'] = number_format($kisyu_month['カプラ資材部品'] + $kisyu_month['リニア資材部品'] + $kisyu_month['ツール資材部品'], $keta);
        $tbody['tbody_gen3_a'] = number_format($kisyu_month['カプラ原材料']   + $kisyu_month['リニア原材料']   + $kisyu_month['ツール原材料']  , $keta);
        $tbody['tbody_kou3_a'] = number_format($kisyu_month['カプラ工作仕掛'] + $kisyu_month['リニア工作仕掛'] + $kisyu_month['ツール工作仕掛'], $keta);
        $tbody['tbody_ken3_a'] = number_format($kisyu_month['カプラ検査仕掛'] + $kisyu_month['リニア検査仕掛'] + $kisyu_month['ツール検査仕掛'], $keta);
        $tbody['tbody_gai3_a'] = number_format($kisyu_month['カプラ外注仕掛'] + $kisyu_month['リニア外注仕掛'] + $kisyu_month['ツール外注仕掛'], $keta);
        $tbody['tbody_cc3_a']  = number_format($kisyu_month['カプラＣＣ部品'] + $kisyu_month['リニアＣＣ部品'] + $kisyu_month['ツールＣＣ部品'], $keta);
        $tbody['tbody_zai3_a'] = number_format($kisyu_month['カプラ財務会計'] + $kisyu_month['リニア財務会計'] + $kisyu_month['ツール財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $buhin3_a = $buhin3_c + $buhin3_l + $buhin3_t;
        $sum3_a = $buhin3_a + $kisyu_month['カプラ組立仕掛'] + $kisyu_month['リニア組立仕掛'] + $kisyu_month['ツール組立仕掛'];
        $sag3_a = ($kisyu_month['カプラ財務会計'] + $kisyu_month['リニア財務会計'] + $kisyu_month['ツール財務会計']) - $sum3_a;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buh3_a'] = number_format($buhin3_a, $keta);
        $tbody['tbody_gou3_a'] = number_format($sum3_a, $keta);
        $tbody['tbody_sag3_a'] = number_format($sag3_a, $keta);
        /////////////////////////////////////////////////////////////////////// 全体 END
    } else {
        /////////////////////////////////////////////////////////////////////// 全体 START
        ///// 各棚卸金額を３桁カンマでハッシュへ代入
        $tbody['tbody_kum3_a'] = number_format($kisyu_month['カプラ組立仕掛'] + $kisyu_month['リニア組立仕掛'], $keta);
        $tbody['tbody_siz3_a'] = number_format($kisyu_month['カプラ資材部品'] + $kisyu_month['リニア資材部品'], $keta);
        $tbody['tbody_gen3_a'] = number_format($kisyu_month['カプラ原材料']   + $kisyu_month['リニア原材料']  , $keta);
        $tbody['tbody_kou3_a'] = number_format($kisyu_month['カプラ工作仕掛'] + $kisyu_month['リニア工作仕掛'], $keta);
        $tbody['tbody_ken3_a'] = number_format($kisyu_month['カプラ検査仕掛'] + $kisyu_month['リニア検査仕掛'], $keta);
        $tbody['tbody_gai3_a'] = number_format($kisyu_month['カプラ外注仕掛'] + $kisyu_month['リニア外注仕掛'], $keta);
        $tbody['tbody_cc3_a']  = number_format($kisyu_month['カプラＣＣ部品'] + $kisyu_month['リニアＣＣ部品'], $keta);
        $tbody['tbody_zai3_a'] = number_format($kisyu_month['カプラ財務会計'] + $kisyu_month['リニア財務会計'], $keta);
        ///// 部品合計と在庫合計及び財務会計評価額と在庫合計との差額(製造間接費他)を計算
        $buhin3_a = $buhin3_c + $buhin3_l;
        $sum3_a = $buhin3_a + $kisyu_month['カプラ組立仕掛'] + $kisyu_month['リニア組立仕掛'];
        $sag3_a = ($kisyu_month['カプラ財務会計'] + $kisyu_month['リニア財務会計']) - $sum3_a;
        ///// 計算結果をハッシュへ代入
        $tbody['tbody_buh3_a'] = number_format($buhin3_a, $keta);
        $tbody['tbody_gou3_a'] = number_format($sum3_a, $keta);
        $tbody['tbody_sag3_a'] = number_format($sag3_a, $keta);
        /////////////////////////////////////////////////////////////////////// 全体 END
    }
}

////////// 対前月増減を計算しハッシュへ代入
    /////////////////////////////////////////////////////////////////////// カプラ START
$tbody['tbody_kumz_c'] = number_format($month['カプラ組立仕掛'] - $pmonth['カプラ組立仕掛'], $keta);
$tbody['tbody_sizz_c'] = number_format($month['カプラ資材部品'] - $pmonth['カプラ資材部品'], $keta);
$tbody['tbody_genz_c'] = number_format($month['カプラ原材料']   - $pmonth['カプラ原材料']  , $keta);
$tbody['tbody_kouz_c'] = number_format($month['カプラ工作仕掛'] - $pmonth['カプラ工作仕掛'], $keta);
$tbody['tbody_kenz_c'] = number_format($month['カプラ検査仕掛'] - $pmonth['カプラ検査仕掛'], $keta);
$tbody['tbody_gaiz_c'] = number_format($month['カプラ外注仕掛'] - $pmonth['カプラ外注仕掛'], $keta);
$tbody['tbody_ccz_c']  = number_format($month['カプラＣＣ部品'] - $pmonth['カプラＣＣ部品'], $keta);
$tbody['tbody_buhz_c'] = number_format($buhin_c - $pbuhin_c, $keta);    // 部品合計
$tbody['tbody_gouz_c'] = number_format($sum_c - $psum_c, $keta);        // 在庫合計
$tbody['tbody_sagz_c'] = number_format($sag_c - $psag_c, $keta);        // 差額(製造間接費他)
$tbody['tbody_zaiz_c'] = number_format($month['カプラ財務会計'] - $pmonth['カプラ財務会計'], $keta);
    /////////////////////////////////////////////////////////////////////// カプラ END

    /////////////////////////////////////////////////////////////////////// リニア START
$tbody['tbody_kumz_l'] = number_format($month['リニア組立仕掛'] - $pmonth['リニア組立仕掛'], $keta);
$tbody['tbody_sizz_l'] = number_format($month['リニア資材部品'] - $pmonth['リニア資材部品'], $keta);
$tbody['tbody_genz_l'] = number_format($month['リニア原材料']   - $pmonth['リニア原材料']  , $keta);
$tbody['tbody_kouz_l'] = number_format($month['リニア工作仕掛'] - $pmonth['リニア工作仕掛'], $keta);
$tbody['tbody_kenz_l'] = number_format($month['リニア検査仕掛'] - $pmonth['リニア検査仕掛'], $keta);
$tbody['tbody_gaiz_l'] = number_format($month['リニア外注仕掛'] - $pmonth['リニア外注仕掛'], $keta);
$tbody['tbody_ccz_l']  = number_format($month['リニアＣＣ部品'] - $pmonth['リニアＣＣ部品'], $keta);
$tbody['tbody_buhz_l'] = number_format($buhin_l - $pbuhin_l, $keta);    // 部品合計
$tbody['tbody_gouz_l'] = number_format($sum_l - $psum_l, $keta);        // 在庫合計
$tbody['tbody_sagz_l'] = number_format($sag_l - $psag_l, $keta);        // 差額(製造間接費他)
$tbody['tbody_zaiz_l'] = number_format($month['リニア財務会計'] - $pmonth['リニア財務会計'], $keta);
    /////////////////////////////////////////////////////////////////////// リニア END
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    /////////////////////////////////////////////////////////////////////// ツール START
    $tbody['tbody_kumz_t'] = number_format($month['ツール組立仕掛'] - $pmonth['ツール組立仕掛'], $keta);
    $tbody['tbody_sizz_t'] = number_format($month['ツール資材部品'] - $pmonth['ツール資材部品'], $keta);
    $tbody['tbody_genz_t'] = number_format($month['ツール原材料']   - $pmonth['ツール原材料']  , $keta);
    $tbody['tbody_kouz_t'] = number_format($month['ツール工作仕掛'] - $pmonth['ツール工作仕掛'], $keta);
    $tbody['tbody_kenz_t'] = number_format($month['ツール検査仕掛'] - $pmonth['ツール検査仕掛'], $keta);
    $tbody['tbody_gaiz_t'] = number_format($month['ツール外注仕掛'] - $pmonth['ツール外注仕掛'], $keta);
    $tbody['tbody_ccz_t']  = number_format($month['ツールＣＣ部品'] - $pmonth['ツールＣＣ部品'], $keta);
    $tbody['tbody_buhz_t'] = number_format($buhin_t - $pbuhin_t, $keta);    // 部品合計
    $tbody['tbody_gouz_t'] = number_format($sum_t - $psum_t, $keta);        // 在庫合計
    $tbody['tbody_sagz_t'] = number_format($sag_t - $psag_t, $keta);        // 差額(製造間接費他)
    $tbody['tbody_zaiz_t'] = number_format($month['ツール財務会計'] - $pmonth['ツール財務会計'], $keta);
    /////////////////////////////////////////////////////////////////////// ツール END
    
    /////////////////////////////////////////////////////////////////////// 全体 START
    $tbody['tbody_kumz_a'] = number_format( ($month['カプラ組立仕掛'] - $pmonth['カプラ組立仕掛']) + ($month['リニア組立仕掛'] - $pmonth['リニア組立仕掛']) + ($month['ツール組立仕掛'] - $pmonth['ツール組立仕掛']), $keta);
    $tbody['tbody_sizz_a'] = number_format( ($month['カプラ資材部品'] - $pmonth['カプラ資材部品']) + ($month['リニア資材部品'] - $pmonth['リニア資材部品']) + ($month['ツール資材部品'] - $pmonth['ツール資材部品']), $keta);
    $tbody['tbody_genz_a'] = number_format( ($month['カプラ原材料']   - $pmonth['カプラ原材料'])   + ($month['リニア原材料']   - $pmonth['リニア原材料'])   + ($month['ツール原材料']   - $pmonth['ツール原材料'])  , $keta);
    $tbody['tbody_kouz_a'] = number_format( ($month['カプラ工作仕掛'] - $pmonth['カプラ工作仕掛']) + ($month['リニア工作仕掛'] - $pmonth['リニア工作仕掛']) + ($month['ツール工作仕掛'] - $pmonth['ツール工作仕掛']), $keta);
    $tbody['tbody_kenz_a'] = number_format( ($month['カプラ検査仕掛'] - $pmonth['カプラ検査仕掛']) + ($month['リニア検査仕掛'] - $pmonth['リニア検査仕掛']) + ($month['ツール検査仕掛'] - $pmonth['ツール検査仕掛']), $keta);
    $tbody['tbody_gaiz_a'] = number_format( ($month['カプラ外注仕掛'] - $pmonth['カプラ外注仕掛']) + ($month['リニア外注仕掛'] - $pmonth['リニア外注仕掛']) + ($month['ツール外注仕掛'] - $pmonth['ツール外注仕掛']), $keta);
    $tbody['tbody_ccz_a']  = number_format( ($month['カプラＣＣ部品'] - $pmonth['カプラＣＣ部品']) + ($month['リニアＣＣ部品'] - $pmonth['リニアＣＣ部品']) + ($month['ツールＣＣ部品'] - $pmonth['ツールＣＣ部品']), $keta);
    $tbody['tbody_buhz_a'] = number_format( ($buhin_c - $pbuhin_c) + ($buhin_l - $pbuhin_l) + ($buhin_t - $pbuhin_t), $keta);    // 部品合計
    $tbody['tbody_gouz_a'] = number_format( ($sum_c - $psum_c)     + ($sum_l - $psum_l)     + ($sum_t - $psum_t), $keta);        // 在庫合計
    $tbody['tbody_sagz_a'] = number_format( ($sag_c - $psag_c)     + ($sag_l - $psag_l)     + ($sag_t - $psag_t), $keta);        // 差額(製造間接費他)
    $tbody['tbody_zaiz_a'] = number_format( ($month['カプラ財務会計'] - $pmonth['カプラ財務会計']) + ($month['リニア財務会計'] - $pmonth['リニア財務会計']) + ($month['ツール財務会計'] - $pmonth['ツール財務会計']), $keta);
    /////////////////////////////////////////////////////////////////////// 全体 END
} else {
    /////////////////////////////////////////////////////////////////////// 全体 START
    $tbody['tbody_kumz_a'] = number_format( ($month['カプラ組立仕掛'] - $pmonth['カプラ組立仕掛']) + ($month['リニア組立仕掛'] - $pmonth['リニア組立仕掛']), $keta);
    $tbody['tbody_sizz_a'] = number_format( ($month['カプラ資材部品'] - $pmonth['カプラ資材部品']) + ($month['リニア資材部品'] - $pmonth['リニア資材部品']), $keta);
    $tbody['tbody_genz_a'] = number_format( ($month['カプラ原材料']   - $pmonth['カプラ原材料'])   + ($month['リニア原材料']   - $pmonth['リニア原材料'])  , $keta);
    $tbody['tbody_kouz_a'] = number_format( ($month['カプラ工作仕掛'] - $pmonth['カプラ工作仕掛']) + ($month['リニア工作仕掛'] - $pmonth['リニア工作仕掛']), $keta);
    $tbody['tbody_kenz_a'] = number_format( ($month['カプラ検査仕掛'] - $pmonth['カプラ検査仕掛']) + ($month['リニア検査仕掛'] - $pmonth['リニア検査仕掛']), $keta);
    $tbody['tbody_gaiz_a'] = number_format( ($month['カプラ外注仕掛'] - $pmonth['カプラ外注仕掛']) + ($month['リニア外注仕掛'] - $pmonth['リニア外注仕掛']), $keta);
    $tbody['tbody_ccz_a']  = number_format( ($month['カプラＣＣ部品'] - $pmonth['カプラＣＣ部品']) + ($month['リニアＣＣ部品'] - $pmonth['リニアＣＣ部品']), $keta);
    $tbody['tbody_buhz_a'] = number_format( ($buhin_c - $pbuhin_c) + ($buhin_l - $pbuhin_l), $keta);    // 部品合計
    $tbody['tbody_gouz_a'] = number_format( ($sum_c - $psum_c)     + ($sum_l - $psum_l), $keta);        // 在庫合計
    $tbody['tbody_sagz_a'] = number_format( ($sag_c - $psag_c)     + ($sag_l - $psag_l), $keta);        // 差額(製造間接費他)
    $tbody['tbody_zaiz_a'] = number_format( ($month['カプラ財務会計'] - $pmonth['カプラ財務会計']) + ($month['リニア財務会計'] - $pmonth['リニア財務会計']), $keta);
    /////////////////////////////////////////////////////////////////////// 全体 END
}

////////////////////////////////////////////// 資材在庫明細 tbody2 のハッシュを使用
    ///// 前月
$tbody2['tbody2_c1p']  = number_format($pmonth['カプラ１']  , $keta);
$tbody2['tbody2_c2p']  = number_format($pmonth['カプラ２']  , $keta);
$tbody2['tbody2_c3p']  = number_format($pmonth['カプラ３']  , $keta);
$tbody2['tbody2_c4p']  = number_format($pmonth['カプラ４']  , $keta);
$tbody2['tbody2_c5p']  = number_format($pmonth['カプラ５']  , $keta);
$tbody2['tbody2_c6p']  = number_format($pmonth['カプラ６']  , $keta);
$tbody2['tbody2_c7p']  = number_format($pmonth['カプラ７']  , $keta);
$tbody2['tbody2_c8p']  = number_format($pmonth['カプラ８']  , $keta);
$tbody2['tbody2_c9p']  = number_format($pmonth['カプラ９']  , $keta);
$tbody2['tbody2_c10p'] = number_format($pmonth['カプラ１０'], $keta);
$tbody2['tbody2_c11p'] = number_format($pmonth['カプラ１１'], $keta);

$tbody2['tbody2_l1p'] = number_format($pmonth['リニア１'], $keta);
$tbody2['tbody2_l2p'] = number_format($pmonth['リニア２'], $keta);
$tbody2['tbody2_l3p'] = number_format($pmonth['リニア３'], $keta);
$tbody2['tbody2_l4p'] = number_format($pmonth['リニア４'], $keta);
$tbody2['tbody2_l5p'] = number_format($pmonth['リニア５'], $keta);
$tbody2['tbody2_l6p'] = number_format($pmonth['リニア６'], $keta);
$tbody2['tbody2_l7p'] = number_format($pmonth['リニア７'], $keta);
if ($yyyymm >= 202001) {
    $tbody2['tbody2_l8p'] = number_format($pmonth['リニア８'], $keta);
}

if ($yyyymm <= 200912) {
    $tbody2['tbody2_l8p'] = number_format($pmonth['リニア８'], $keta);
    $tbody2['tbody2_l9p'] = number_format($pmonth['リニア９'], $keta);
}

    ///// 当月
$tbody2['tbody2_c1'] = number_format($month['カプラ１'], $keta);
$tbody2['tbody2_c2'] = number_format($month['カプラ２'], $keta);
$tbody2['tbody2_c3'] = number_format($month['カプラ３'], $keta);
$tbody2['tbody2_c4'] = number_format($month['カプラ４'], $keta);
$tbody2['tbody2_c5'] = number_format($month['カプラ５'], $keta);
$tbody2['tbody2_c6'] = number_format($month['カプラ６'], $keta);
$tbody2['tbody2_c7'] = number_format($month['カプラ７'], $keta);
$tbody2['tbody2_c8'] = number_format($month['カプラ８'], $keta);
$tbody2['tbody2_c9'] = number_format($month['カプラ９'], $keta);
$tbody2['tbody2_c10'] = number_format($month['カプラ１０'], $keta);
$tbody2['tbody2_c11'] = number_format($month['カプラ１１'], $keta);

$tbody2['tbody2_l1'] = number_format($month['リニア１'], $keta);
$tbody2['tbody2_l2'] = number_format($month['リニア２'], $keta);
$tbody2['tbody2_l3'] = number_format($month['リニア３'], $keta);
$tbody2['tbody2_l4'] = number_format($month['リニア４'], $keta);
if ($yyyymm == 201912) {
    $tbody2['tbody2_l5'] = number_format($month['リニア６'], $keta);
    $tbody2['tbody2_l6'] = number_format($month['リニア７']+$month['リニア５'], $keta);
    $tbody2['tbody2_l7'] = number_format($month['リニア８'], $keta);
} else {
    $tbody2['tbody2_l5'] = number_format($month['リニア５'], $keta);
    $tbody2['tbody2_l6'] = number_format($month['リニア６'], $keta);
    $tbody2['tbody2_l7'] = number_format($month['リニア７'], $keta);
}
if ($yyyymm >= 202001) {
    $tbody2['tbody2_l8'] = number_format($month['リニア８'], $keta);
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_l8'] = number_format($month['リニア８'], $keta);
    $tbody2['tbody2_l9'] = number_format($month['リニア９'], $keta);
}
    ///// 前月増減
$tbody2['tbody2_c1_zou']  = number_format($month['カプラ１']   - $pmonth['カプラ１']  , $keta);
$tbody2['tbody2_c2_zou']  = number_format($month['カプラ２']   - $pmonth['カプラ２']  , $keta);
$tbody2['tbody2_c3_zou']  = number_format($month['カプラ３']   - $pmonth['カプラ３']  , $keta);
$tbody2['tbody2_c4_zou']  = number_format($month['カプラ４']   - $pmonth['カプラ４']  , $keta);
$tbody2['tbody2_c5_zou']  = number_format($month['カプラ５']   - $pmonth['カプラ５']  , $keta);
$tbody2['tbody2_c6_zou']  = number_format($month['カプラ６']   - $pmonth['カプラ６']  , $keta);
$tbody2['tbody2_c7_zou']  = number_format($month['カプラ７']   - $pmonth['カプラ７']  , $keta);
$tbody2['tbody2_c8_zou']  = number_format($month['カプラ８']   - $pmonth['カプラ８']  , $keta);
$tbody2['tbody2_c9_zou']  = number_format($month['カプラ９']   - $pmonth['カプラ９']  , $keta);
$tbody2['tbody2_c10_zou'] = number_format($month['カプラ１０'] - $pmonth['カプラ１０'], $keta);
$tbody2['tbody2_c11_zou'] = number_format($month['カプラ１１'] - $pmonth['カプラ１１'], $keta);

$tbody2['tbody2_l1_zou'] = number_format($month['リニア１'] - $pmonth['リニア１'], $keta);
$tbody2['tbody2_l2_zou'] = number_format($month['リニア２'] - $pmonth['リニア２'], $keta);
$tbody2['tbody2_l3_zou'] = number_format($month['リニア３'] - $pmonth['リニア３'], $keta);
$tbody2['tbody2_l4_zou'] = number_format($month['リニア４'] - $pmonth['リニア４'], $keta);
if ($yyyymm == 201912) {
    $tbody2['tbody2_l5_zou'] = number_format($month['リニア６'] - $pmonth['リニア５'], $keta);
    $tbody2['tbody2_l6_zou'] = number_format($month['リニア７'] + $month['リニア５'] - $pmonth['リニア６'], $keta);
    $tbody2['tbody2_l7_zou'] = number_format($month['リニア８'] - $pmonth['リニア７'], $keta);
} else {
    $tbody2['tbody2_l5_zou'] = number_format($month['リニア５'] - $pmonth['リニア５'], $keta);
    $tbody2['tbody2_l6_zou'] = number_format($month['リニア６'] - $pmonth['リニア６'], $keta);
    $tbody2['tbody2_l7_zou'] = number_format($month['リニア７'] - $pmonth['リニア７'], $keta);
}
if ($yyyymm >= 202001) {
    $tbody2['tbody2_l8_zou'] = number_format($month['リニア８'] - $pmonth['リニア８'], $keta);
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_l8_zou'] = number_format($month['リニア８'] - $pmonth['リニア８'], $keta);
    $tbody2['tbody2_l9_zou'] = number_format($month['リニア９'] - $pmonth['リニア９'], $keta);
}
//////////////////////////////////////////////////////////////////// 資材在庫明細 tbody2 END

////////// 資材在庫明細 合計
    ///// 前月カプラ
$tbody2['tbody2_ckeip'] = 0;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ１']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ２']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ３']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ４']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ５']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ６']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ７']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ８']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ９']  ;
$tbody2['tbody2_ckeip'] += $pmonth['カプラ１０'];
$tbody2['tbody2_ckeip'] += $pmonth['カプラ１１'];
$tbody2['tbody2_ckeip'] = $pmonth['カプラ資材部品'];     // 四捨五入が合わないためここに追加
    ///// 当月カプラ
$tbody2['tbody2_ckei'] = 0;
$tbody2['tbody2_ckei'] += $month['カプラ１']  ;
$tbody2['tbody2_ckei'] += $month['カプラ２']  ;
$tbody2['tbody2_ckei'] += $month['カプラ３']  ;
$tbody2['tbody2_ckei'] += $month['カプラ４']  ;
$tbody2['tbody2_ckei'] += $month['カプラ５']  ;
$tbody2['tbody2_ckei'] += $month['カプラ６']  ;
$tbody2['tbody2_ckei'] += $month['カプラ７']  ;
$tbody2['tbody2_ckei'] += $month['カプラ８']  ;
$tbody2['tbody2_ckei'] += $month['カプラ９']  ;
$tbody2['tbody2_ckei'] += $month['カプラ１０'];
$tbody2['tbody2_ckei'] += $month['カプラ１１'];
$tbody2['tbody2_ckei'] = $month['カプラ資材部品'];     // 四捨五入が合わないためここに追加
    ///// 前月増減
$tbody2['tbody2_ckei_zou'] = number_format($tbody2['tbody2_ckei'] - $tbody2['tbody2_ckeip'], $keta);
    ///// 前月・当月 合計セット
$tbody2['tbody2_ckeip'] = number_format($tbody2['tbody2_ckeip'], $keta);
$tbody2['tbody2_ckei'] = number_format($tbody2['tbody2_ckei'], $keta);

    ///// 前月リニア
$tbody2['tbody2_lkeip'] = 0;
$tbody2['tbody2_lkeip'] += $pmonth['リニア１']  ;
$tbody2['tbody2_lkeip'] += $pmonth['リニア２']  ;
$tbody2['tbody2_lkeip'] += $pmonth['リニア３']  ;
$tbody2['tbody2_lkeip'] += $pmonth['リニア４']  ;
$tbody2['tbody2_lkeip'] += $pmonth['リニア５']  ;
$tbody2['tbody2_lkeip'] += $pmonth['リニア６']  ;
$tbody2['tbody2_lkeip'] += $pmonth['リニア７']  ;
if ($yyyymm >= 202001) {
    $tbody2['tbody2_lkeip'] += $pmonth['リニア８']  ;
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_lkeip'] += $pmonth['リニア８']  ;
    $tbody2['tbody2_lkeip'] += $pmonth['リニア９']  ;
}
$tbody2['tbody2_lkeip'] = $pmonth['リニア資材部品'];     // 四捨五入が合わないためここに追加
    ///// 当月リニア
$tbody2['tbody2_lkei'] = 0;
$tbody2['tbody2_lkei'] += $month['リニア１']  ;
$tbody2['tbody2_lkei'] += $month['リニア２']  ;
$tbody2['tbody2_lkei'] += $month['リニア３']  ;
$tbody2['tbody2_lkei'] += $month['リニア４']  ;
$tbody2['tbody2_lkei'] += $month['リニア５']  ;
$tbody2['tbody2_lkei'] += $month['リニア６']  ;
$tbody2['tbody2_lkei'] += $month['リニア７']  ;
if ($yyyymm >= 202001) {
    $tbody2['tbody2_lkei'] += $month['リニア８']  ;
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_lkei'] += $month['リニア８']  ;
    $tbody2['tbody2_lkei'] += $month['リニア９']  ;
}
$tbody2['tbody2_lkei'] = $month['リニア資材部品'];     // 四捨五入が合わないためここに追加
    ///// 前月増減
$tbody2['tbody2_lkei_zou'] = number_format($tbody2['tbody2_lkei'] - $tbody2['tbody2_lkeip'], $keta);
    ///// 前月・当月 合計セット
$tbody2['tbody2_lkeip'] = number_format($tbody2['tbody2_lkeip'], $keta);
$tbody2['tbody2_lkei'] = number_format($tbody2['tbody2_lkei'], $keta);


////////// 目標在庫金額 tbody3 のハッシュを使用
$tbody3['tbody3_moku_c'] = number_format($month['カプラ目標'], $keta);
$tbody3['tbody3_moku_l'] = number_format($month['リニア目標'], $keta);
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    $tbody3['tbody3_moku_t'] = number_format($month['ツール目標'], $keta);
    $tbody3['tbody3_moku_a'] = number_format($month['カプラ目標'] + $month['リニア目標'] + $month['ツール目標'], $keta);
} else {
    $tbody3['tbody3_moku_a'] = number_format($month['カプラ目標'] + $month['リニア目標'], $keta);
}
///// 当月(在庫合計)
$tbody3['tbody3_mon_c'] = $tbody['tbody_gou_c'];
$tbody3['tbody3_mon_l'] = $tbody['tbody_gou_l'];
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    $tbody3['tbody3_mon_t'] = $tbody['tbody_gou_t'];
    $tbody3['tbody3_mon_a'] = number_format($sum_c + $sum_l + $sum_t, $keta);
} else {
    $tbody3['tbody3_mon_a'] = number_format($sum_c + $sum_l, $keta);
}
///// 目標に対する増減
$tbody3['tbody3_zou_c'] = number_format($sum_c - $month['カプラ目標'], $keta);
$tbody3['tbody3_zou_l'] = number_format($sum_l - $month['リニア目標'], $keta);
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    $tbody3['tbody3_zou_t'] = number_format($sum_t - $month['ツール目標'], $keta);
    $tbody3['tbody3_zou_a'] = number_format(($sum_c + $sum_l + $sum_t) - ($month['カプラ目標'] + $month['リニア目標'] + $month['ツール目標']), $keta);
} else {
    $tbody3['tbody3_zou_a'] = number_format(($sum_c + $sum_l) - ($month['カプラ目標'] + $month['リニア目標']), $keta);
}

/********** patTemplate 書出し ************/
include_once ( '../../../patTemplate/include/patTemplate.php' );
$tmpl = new patTemplate();

//  In diesem Verzeichnis liegen die Templates
$tmpl->setBasedir( 'templates' );

if ($yyyymm >= 202104) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_202104.templ.html' );
} elseif ($yyyymm >= 202001) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_202001.templ.html' );
} elseif ($yyyymm >= 201604) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_201604.templ.html' );
} elseif ($yyyymm >= 201504) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_201504.templ.html' );
} elseif ($yyyymm >= 201001) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_201001.templ.html' );
} else {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent.templ.html' );
}


$tmpl->addVar('page', 'PAGE_TITLE'         , '比較棚卸表');
$tmpl->addVar('page', 'PAGE_MENU_SITE_URL' , $menu_site_script);
$tmpl->addVar('page', 'PAGE_UNIQUE'        , $uniq);
$tmpl->addVar('page', 'PAGE_RETURN_URL'    , $url_referer);
$tmpl->addVar('page', 'PAGE_CURRENT_URL'   , $current_script);
$tmpl->addVar('page', 'PAGE_SITE_VIEW'     , $site_view);
$tmpl->addVar('page', 'PAGE_HEADER_TITLE'  , "第{$ki}期 {$tuki}月末比較棚卸表");
$tmpl->addVar('page', 'PAGE_HEADER_TODAY'  , $today);
$tmpl->addVar('page', 'OUT_CSS'            , $menu->out_css());
$tmpl->addVar('page', 'OUT_JSBASE'         , $menu->out_jsBaseClass());
$tmpl->addVar('page', 'OUT_TITLE_BORDER'   , $menu->out_title_border());

///// 表示単位をテンプレート変数への登録
if ($tani == 1) {
    $tmpl->addVar('page', 'en'       , 'selected');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , 'selected');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 100000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , 'selected');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
} else {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
}
///// 小数点以下の桁数
if ($keta == 0) {
    $tmpl->addVar('page', 'zero' , 'selected');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 1) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 3) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , 'selected');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 6) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , 'selected');
} else {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
}


if ($yyyymm >= 201504 || $yyyymm <= 202103) {
    $tmpl->addVar('tbody', 'tbody_monthp_c'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_l'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_t'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_a'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_c'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_l'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_t'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_month_c'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_l'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_t'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_a'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_c'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_l'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_t'   , $yyyymm);
} else {
    $tmpl->addVar('tbody', 'tbody_monthp_c'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_l'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_a'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_c'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_l'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_month_c'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_l'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_a'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_c'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_l'   , $yyyymm);

}

///// ハッシュ配列で patTemplate に展開 カプラ・リニア・全体が tbody[]に代入されている
$tmpl->addVars('tbody', $tbody);
$tmpl->addVars('tbody2', $tbody2);
$tmpl->addVars('tbody3', $tbody3);

//$tmpl->addVars( 'tbody_rows', array('TBODY_DSP_NUM' => $dsp_num) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD0'  => $field0) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD1'  => $field1) );


/********** Logic End   **********/

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

//  Alle Templates ausgeben
$tmpl->displayParsedTemplate();
/************* patTemplate 終了 *****************/

?>
