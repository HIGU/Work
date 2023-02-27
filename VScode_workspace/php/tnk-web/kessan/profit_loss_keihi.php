<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 経費実績内訳表                                         //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/01/17 Created   profit_loss_keihi.php                               //
// 2003/01/24 表示用データを view_data[][] で統一 単位・桁を可変にした      //
// 2003/01/27 データをテキストファイル(FTP)からデータベースに変更           //
// 2003/01/28 データベースの設計を変更し期のフィールドを設け前期を計算      //
// 2003/02/21 Font を monospace (等間隔font) へ変更                         //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                       //
// 2003/03/06 title_font today_font を設定 少数以下の桁数６桁を追加         //
// 2003/03/11 Location: http → Location $url_referer に変更                //
//            メッセージを出力するため site_index site_id をコメントにし    //
//                                          parent.menu_site.を有効に変更   //
// 2003/05/01 工場長からの指示で認証をAccount_groupから通常へ変更           //
// 2004/05/06 外形標準課税の対応のため事業等の科目追加(7520)D36 $r=35       //
//            kin1=製造経費 kin2=販管費 なので kin3～kin9は必要ないので削除 //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタンを追加                 //
// 2004/06/04 $rec_keihi = 27→28へ変更 (外形標準課税の事業等追加による)    //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2009/10/15 各小計・合計を太字に変更                                 大谷 //
// 2010/10/09 累計データの取得が常に期全体の値になっていたのを、期初から    //
//            照会した月までの累計を取得するように変更                 大谷 //
// 2012/01/26 経費データの登録を追加（２期比較用）                     大谷 //
// 2012/02/28 2012年1月 業務委託費 調整 リニア製造経費 +1,156,130円    大谷 //
//             ※ 平出横川派遣料 2月に逆調整を行うこと                      //
// 2012/03/05 2012年1月 業務委託費 調整 リニア製造経費 +1,156,130円 戻 大谷 //
// 2013/11/07 2013年10月 商管業務委託費 調整 +1,245,035円              大谷 //
//             ※ 横川派遣料 11月に逆調整を行うこと                         //
// 2013/11/07 2013年11月 商管業務委託費 調整 -1,245,035円 戻し処理     大谷 //
// 2015/02/20 クレーム対応費の事業等の科目追加(7550)D37 $r=36               //
//            kin1=製造経費 kin2=販管費 なので kin3～kin9は必要ないので削除 //
//            $rec_keihi = 28→29へ変更 (クレーム対応費追加による)          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
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
$menu->set_title("第 {$ki} 期　{$tuki} 月度　経 費 実 績 内 訳 表");

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
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // 期初年月

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
$rec_jin   =  8;    // 人件費の使用科目数
$rec_keihi = 29;    // 経費の使用科目数  クレーム対応費 追加により 28→29へ
$f_mei     = 13;    // 明細(表)のフィールド数

////// D仕様の物理レコード数
define('D_REC', 38);    // 2015/02/20 クレーム対応費の追加により37→38

////// データベースよりデータ取り込み
$res = array();     /*** 当月のデータ取得 ***/      // kin1=製造経費  kin2=販管費  D仕様の場合
$query = sprintf("select kin1, kin2 from pl_bs_summary where pl_bs_ym=%d and t_id='D' order by t_id, t_row ASC", $yyyymm);
if (($rows=getResult($query,$res)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    for ($i=0; $i<$rows; $i++) {
        // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
        if ($yyyymm == 201201) {
            if ($i == 17) {
                $res[$i][0] += 1156130;
            }
        }
        if ($yyyymm == 201202) {
            if ($i == 17) {
                $res[$i][0] -= 1156130;
            }
        }
        // 2013/11/07 追加 2013年10月度 商管業務委託費（横川派遣料）調整
        if ($yyyymm == 201310) {
            if ($i == 17) {
                $res[$i][0] += 1245035;
            }
        }
        if ($yyyymm == 201311) {
            if ($i == 17) {
                $res[$i][0] -= 1245035;
            }
        }
        if ($res[$i][0] != 0)
            $res[$i][0] = ($res[$i][0] / $tani);
        if ($res[$i][1] != 0)
            $res[$i][1] = ($res[$i][1] / $tani);
    }
    if ($rows == (D_REC-1)) {    // クレーム対応費のD37が無い場合(レコード数=37)は
        $res[D_REC-1][0] = $res[$rows-1][0];  // D99 を38レコード目にコピー
        $res[D_REC-1][1] = $res[$rows-1][1];  // D99 を38レコード目にコピー
        $res[$rows-1][0] = 0;                    // 追加になったD37を0で初期化
        $res[$rows-1][1] = 0;                    // 追加になったD37を0で初期化
    }
    $res_p1 = array();     /*** 前月のデータ取得 ***/
    $query_p1 = sprintf("select kin1, kin2 from pl_bs_summary where pl_bs_ym=%d and t_id='D' order by t_id, t_row ASC", $p1_ym);
    if (($rows_p1=getResult($query_p1,$res_p1)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
        for ($i=0; $i<$rows_p1; $i++) {
            // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
            if ($p1_ym == 201201) {
                if ($i == 17) {
                    $res_p1[$i][0] += 1156130;
                }
            }
            if ($p1_ym == 201202) {
                if ($i == 17) {
                    $res_p1[$i][0] -= 1156130;
                }
            }
            // 2013/11/07 追加 2013年10月度 商管業務委託費（横川派遣料）調整
            if ($p1_ym == 201310) {
                if ($i == 17) {
                    $res_p[$i][0] += 1245035;
                }
            }
            if ($p1_ym == 201311) {
                if ($i == 17) {
                    $res_p[$i][0] -= 1245035;
                }
            }
            if ($res_p1[$i][0] != 0)
                $res_p1[$i][0] = ($res_p1[$i][0] / $tani);
            if ($res_p1[$i][1] != 0)
                $res_p1[$i][1] = ($res_p1[$i][1] / $tani);
        }
        if ($rows_p1 == (D_REC-1)) {    // クレーム対応費のD37が無い場合(レコード数=37)は
            $res_p1[D_REC-1][0] = $res_p1[$rows_p1-1][0];  // D99 を38レコード目にコピー
            $res_p1[D_REC-1][1] = $res_p1[$rows_p1-1][1];  // D99 を38レコード目にコピー
            $res_p1[$rows_p1-1][0] = 0;                    // 追加になったD37を0で初期化
            $res_p1[$rows_p1-1][1] = 0;                    // 追加になったD37を0で初期化
        }
    } else {        // データが無い場合に ０で初期化 仕様上３８レコードあることに注意(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_p1[$i][0] = 0;
                $res_p1[$i][1] = 0;
        }
    }
    $res_p2 = array();     /*** 前々月のデータ取得 ***/
    $query_p2 = sprintf("select kin1, kin2 from pl_bs_summary where pl_bs_ym=%d and t_id='D' order by t_id, t_row ASC", $p2_ym);
    if (($rows_p2=getResult($query_p2,$res_p2)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
        for ($i=0; $i<$rows_p2; $i++) {
            // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
            if ($p2_ym == 201201) {
                if ($i == 17) {
                    $res_p2[$i][0] += 1156130;
                }
            }
            if ($p2_ym == 201202) {
                if ($i == 17) {
                    $res_p2[$i][0] -= 1156130;
                }
            }
            // 2013/11/07 追加 2013年10月度 商管業務委託費（横川派遣料）調整
            if ($p2_ym == 201310) {
                if ($i == 17) {
                    $res_p2[$i][0] += 1245035;
                }
            }
            if ($p2_ym == 201311) {
                if ($i == 17) {
                    $res_p2[$i][0] -= 1245035;
                }
            }
            if ($res_p2[$i][0] != 0)
                $res_p2[$i][0] = ($res_p2[$i][0] / $tani);
            if ($res_p2[$i][1] != 0)
                $res_p2[$i][1] = ($res_p2[$i][1] / $tani);
        }
        if ($rows_p2 == (D_REC-1)) {    // クレーム対応費のD37が無い場合(レコード数=37)は
            $res_p2[D_REC-1][0] = $res_p2[$rows_p2-1][0];  // D99 を38レコード目にコピー
            $res_p2[D_REC-1][1] = $res_p2[$rows_p2-1][1];  // D99 を38レコード目にコピー
            $res_p2[$rows_p2-1][0] = 0;                    // 追加になったD37を0で初期化
            $res_p2[$rows_p2-1][1] = 0;                    // 追加になったD37を0で初期化
        }
    } else {        // データが無い場合に ０で初期化 仕様上３８レコードあることに注意(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_p2[$i][0] = 0;
                $res_p2[$i][1] = 0;
        }
    }
    $res_rui = array();     /*** 累計のデータ取得 ***/
    $query_rui = sprintf("select sum(kin1),sum(kin2) from pl_bs_summary where pl_bs_ym>=%d and pl_bs_ym<=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $str_ym, $yyyymm);
    //$query_rui = sprintf("select sum(kin1),sum(kin2) from pl_bs_summary where ki=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $ki);
    if (($rows_rui=getResult($query_rui,$res_rui)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
        for ($i=0; $i<$rows_rui; $i++) {
            // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
            if (($yyyymm >= 201201) && ($yyyymm <= 201203)) {
                if ($i == 17) {
                    $res_rui[$i][0] += 1156130;
                }
            }
            if (($yyyymm >= 201202) && ($yyyymm <= 201203)) {
                if ($i == 17) {
                    $res_rui[$i][0] -= 1156130;
                }
            }
            // 2013/11/07 追加 2013年10月度 商管業務委託費（横川派遣料）調整
            if (($yyyymm >= 201310) && ($yyyymm <= 201403)) {
                if ($i == 17) {
                    $res_rui[$i][0] += 1245035;
                }
            }
            if (($yyyymm >= 201311) && ($yyyymm <= 201403)) {
                if ($i == 17) {
                    $res_rui[$i][0] -= 1245035;
                }
            }
            if ($res_rui[$i][0] != 0)
                $res_rui[$i][0] = ($res_rui[$i][0] / $tani);
            if ($res_rui[$i][1] != 0)
                $res_rui[$i][1] = ($res_rui[$i][1] / $tani);
        }
        if ($rows_rui == (D_REC-1)) {    // クレーム対応費のD37が無い場合(レコード数=37)は
            $res_rui[D_REC-1][0] = $res_rui[$rows_rui-1][0];  // D99 を38レコード目にコピー
            $res_rui[D_REC-1][1] = $res_rui[$rows_rui-1][1];  // D99 を38レコード目にコピー
            $res_rui[$rows_rui-1][0] = 0;                    // 追加になったD37を0で初期化
            $res_rui[$rows_rui-1][1] = 0;                    // 追加になったD37を0で初期化
        }
    } else {        // データが無い場合に ０で初期化 仕様上３８レコードあることに注意(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_rui[$i][0] = 0;
                $res_rui[$i][1] = 0;
        }
    }
    $res_avg = array();     /*** 前期 累計より月平均のデータ取得 ***/
    $ki_p = $ki - 1;
    if ($ki_p >= 2) {       ///// 前期が２期以上の場合は１２で割る
        $query_avg = sprintf("select round((sum(kin1)+sum(kin2))/12) from pl_bs_summary where ki=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $ki_p);
        if (($rows_avg=getResult($query_avg,$res_avg)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            for ($i=0; $i<$rows_avg; $i++) {
                if ($res_avg[$i][0] != 0)
                    $res_avg[$i][0] = ($res_avg[$i][0] / $tani);
            }
            if ($rows_avg == (D_REC-1)) {    // クレーム対応費のD37が無い場合(レコード数=37)は
                $res_avg[D_REC-1][0] = $res_avg[$rows_avg-1][0];  // D99 を38レコード目にコピー
                $res_avg[$rows_avg-1][0] = 0;                    // 追加になったD37を0で初期化
            }
        } else {        // データが無い場合に ０で初期化 仕様上３８レコードあることに注意(D1--D37+D99)
            for ($i=0; $i<D_REC; $i++) {
                    $res_avg[$i][0] = 0;
            }
        }
    } elseif ($ki_p == 1) { ///// 前期が１期の場合には６で割る
        $query_avg = sprintf("select round((sum(kin1)+sum(kin2))/6) from pl_bs_summary where ki=%d and t_id='D' group by t_id, t_row order by t_id, t_row ASC", $ki_p);
        if (($rows_avg=getResult($query_avg,$res_avg)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            for ($i=0; $i<$rows_avg; $i++) {
                if ($res_avg[$i][0] != 0)
                    $res_avg[$i][0] = ($res_avg[$i][0] / $tani);
            }
            if ($rows_avg == (D_REC-1)) {    // クレーム対応費のD37が無い場合(レコード数=37)は
                $res_avg[D_REC-1][0] = $res_avg[$rows_avg-1][0];  // D99 を38レコード目にコピー
                $res_avg[$rows_avg-1][0] = 0;                    // 追加になったD37を0で初期化
            }
        } else {        // データが無い場合に ０で初期化 仕様上３８レコードあることに注意(D1--D37+D99)
            for ($i=0; $i<D_REC; $i++) {
                    $res_avg[$i][0] = 0;
            }
        }
    } else {        // データが無い場合に ０で初期化 仕様上３８レコードあることに注意(D1--D37+D99)
        for ($i=0; $i<D_REC; $i++) {
                $res_avg[$i][0] = 0;
        }
    }

    ///////// 表示用データの生成 (画面の表データイメージ)
    ///// 人件費と経費の明細部
    $view_data = array();      // 表示用変数 配列で初期化
    for ($r=0; $r<$rows; $r++) {
        for ($c=0; $c<14; $c++) {
            switch ($c) {
            case  0:        // 製造経費 前々月
                $view_data[$r][$c] = number_format($res_p2[$r][0],$keta); break;
            case  4:        // 販管費   前々月
                $view_data[$r][$c] = number_format($res_p2[$r][1],$keta); break;
            case  8:        // 合　計   前々月
                $view_data[$r][$c] = number_format($res_p2[$r][0] + $res_p2[$r][1],$keta); break;
            case  1:        // 製造経費 前月
                $view_data[$r][$c] = number_format($res_p1[$r][0],$keta); break;
            case  5:        // 販管費   前月
                $view_data[$r][$c] = number_format($res_p1[$r][1],$keta); break;
            case  9:        // 合　計   前月
                $view_data[$r][$c] = number_format($res_p1[$r][0] + $res_p1[$r][1],$keta); break;
            case  2:        // 製造経費 当月
                $view_data[$r][$c] = number_format($res[$r][0],$keta); break;
            case  6:        // 販管費   当月
                $view_data[$r][$c] = number_format($res[$r][1],$keta); break;
            case 10:        // 合　計   当月
                $view_data[$r][$c] = number_format($res[$r][0] + $res[$r][1],$keta); break;
            case  3:        // 製造経費 累計
                $view_data[$r][$c] = number_format($res_rui[$r][0],$keta); break;
            case  7:        // 販管費   累計
                $view_data[$r][$c] = number_format($res_rui[$r][1],$keta); break;
            case 11:        // 合　計   累計
                $view_data[$r][$c] = number_format($res_rui[$r][0] + $res_rui[$r][1],$keta); break;
            case 12:        // 前期月平均
                $view_data[$r][$c] = number_format($res_avg[$r][0],$keta); break;
            case 13:        // 補足事項
                $view_data[$r][$c] = "-"; break;
            default:        // その他は無いが
                $view_data[$r][$c] = number_format(0,$keta); break;
            }
        }
    }
    ///// 小計の計算 人件費
    $jin_sum = array();
    for ($c=0; $c < $f_mei; $c++) {
        $jin_sum[$c] = 0;       // 以下で += を使用するため初期化
    }
    for ($i=0; $i < $rec_jin; $i++) {
        for ($c=0; $c < $f_mei; $c++) {         ///// 補足事項を抜いて １３レコードになる
            switch ($c) {
            case  0:        // 製造経費 前々月
                $jin_sum[$c] += $res_p2[$i][0]; break;
            case  4:        // 販管費   前々月
                $jin_sum[$c] += $res_p2[$i][1]; break;
            case  8:        // 合　計   前々月
                $jin_sum[$c] += ($res_p2[$i][0] + $res_p2[$i][1]); break;
            case  1:        // 製造経費 前月
                $jin_sum[$c] += $res_p1[$i][0]; break;
            case  5:        // 販管費   前月
                $jin_sum[$c] += $res_p1[$i][1]; break;
            case  9:        // 合　計   前月
                $jin_sum[$c] += ($res_p1[$i][0] + $res_p1[$i][1]); break;
            case  2:        // 製造経費 当月
                $jin_sum[$c] += $res[$i][0]; break;
            case  6:        // 販管費   当月
                $jin_sum[$c] += $res[$i][1]; break;
            case 10:        // 合　計   当月
                $jin_sum[$c] += ($res[$i][0] + $res[$i][1]); break;
            case  3:        // 製造経費 累計
                $jin_sum[$c] += $res_rui[$i][0]; break;
            case  7:        // 販管費   累計
                $jin_sum[$c] += $res_rui[$i][1]; break;
            case 11:        // 合　計   累計
                $jin_sum[$c] += ($res_rui[$i][0] + $res_rui[$i][1]); break;
            case 12:        // 前期月平均
                $jin_sum[$c] += $res_avg[$i][0]; break;
            default:        // その他は無いが
                $jin_sum[$c] += 0; break;
            }
        }
    }
    ///// 小計の計算 経費
    $kei_sum = array();
    for ($c=0; $c < $f_mei; $c++) {
        $kei_sum[$c] = 0;       // 以下で += を使用するため初期化
    }
    for ($i=0; $i < $rec_keihi; $i++){
        for ($c=0; $c < $f_mei; $c++) {         ///// 補足事項を抜いて １３レコードになる
            switch ($c) {
            case  0:        // 製造経費 前々月
                $kei_sum[$c] += $res_p2[$i+8][0]; break;
            case  4:        // 販管費   前々月
                $kei_sum[$c] += $res_p2[$i+8][1]; break;
            case  8:        // 合　計   前々月
                $kei_sum[$c] += ($res_p2[$i+8][0] + $res_p2[$i+8][1]); break;
            case  1:        // 製造経費 前月
                $kei_sum[$c] += $res_p1[$i+8][0]; break;
            case  5:        // 販管費   前月
                $kei_sum[$c] += $res_p1[$i+8][1]; break;
            case  9:        // 合　計   前月
                $kei_sum[$c] += ($res_p1[$i+8][0] + $res_p1[$i+8][1]); break;
            case  2:        // 製造経費 当月
                $kei_sum[$c] += $res[$i+8][0]; break;
            case  6:        // 販管費   当月
                $kei_sum[$c] += $res[$i+8][1]; break;
            case 10:        // 合　計   当月
                $kei_sum[$c] += ($res[$i+8][0] + $res[$i+8][1]); break;
            case  3:        // 製造経費 累計
                $kei_sum[$c] += $res_rui[$i+8][0]; break;
            case  7:        // 販管費   累計
                $kei_sum[$c] += $res_rui[$i+8][1]; break;
            case 11:        // 合　計   累計
                $kei_sum[$c] += ($res_rui[$i+8][0] + $res_rui[$i+8][1]); break;
            case 12:        // 前期月平均
                $kei_sum[$c] += $res_avg[$i+8][0]; break;
            default:        // その他は無いが
                $kei_sum[$c] += 0; break;
            }
        }
    }
    ///// 合計の計算   ///// 小計・合計の表示用データ生成
    $all_sum = array();
    $view_jin_sum = array();
    $view_kei_sum = array();
    $view_all_sum = array();
    for ($c=0; $c < $f_mei; $c++) {         ///// 補足事項を抜いて $f_mei=13フィールドになる
        $all_sum[$c]  = $jin_sum[$c] + $kei_sum[$c];             // 合計の計算
        $view_jin_sum[$c] = number_format($jin_sum[$c],$keta);   // 表示用 人件費計
        $view_kei_sum[$c] = number_format($kei_sum[$c],$keta);   // 表示用 経費計
        $view_all_sum[$c] = number_format($all_sum[$c],$keta);   // 表示用 合　計
    }
} else {
    $_SESSION["s_sysmsg"] = sprintf("対象データがありません！<br>第%d期%d月",$ki,$tuki);
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
    for ($i = 0; $i < 3; $i++) {
        if ($i == 0) {
            $c = 2;             // 製造経費
        } elseif ($i == 1) {
            $c = 6;             // 販管費
        } elseif ($i == 2) {
            $c = 10;            // 合計
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
            $head  = "製造経費";
        } elseif ($i == 1) {
            $head  = "販管費";
        } elseif ($i == 2) {
            $head  = "合計";
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
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <TR>
                    <TD rowspan="2" align="center" nowrap class='pt10b' bgcolor='#ffffc6'>勘定科目</TD>
                    <TD colspan="4" align="center" height="20" class='pt10b' bgcolor='#ffffc6'>製造経費</TD>
                    <TD colspan="4" align="center" height="20" class='pt10b' bgcolor='#ffffc6'>販 管 費</TD>
                    <TD colspan="4" align="center" height="20" class='pt10b' bgcolor='#ffffc6'>合　　計</TD>
                    <TD rowspan="2" nowrap class='pt8' bgcolor='#ffffc6'>前期月平均</TD>
                    <TD rowspan="2" nowrap class='pt10b'>補足事項</TD>
                </TR>
                <TR>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p2_ym,0,4), substr($p2_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p1_ym,0,4), substr($p1_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffff'><?php printf("%s/%s", substr($yyyymm,0,4), substr($yyyymm,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffc6'>累　　計</TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p2_ym,0,4), substr($p2_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p1_ym,0,4), substr($p1_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffff'><?php printf("%s/%s", substr($yyyymm,0,4), substr($yyyymm,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffc6'>累　　計</TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p2_ym,0,4), substr($p2_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b'><?php printf("%s/%s", substr($p1_ym,0,4), substr($p1_ym,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffff'><?php printf("%s/%s", substr($yyyymm,0,4), substr($yyyymm,4,2)) ?></TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ffffc6'>累　　計</TD>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>役員報酬</TD>
                    <?php
                        $r = 0;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)         // 累計
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)     // 当月
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>給料手当</TD>
                    <?php
                        $r = 1;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与手当</TD>
                    <?php
                        $r = 2;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>顧問料</TD>
                    <?php
                        $r = 3;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>法定福利費</TD>
                    <?php
                        $r = 4;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>厚生福利費</TD>
                    <?php
                        $r = 5;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>賞与引当金繰入</TD>
                    <?php
                        $r = 6;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>退職給付費用</TD>
                    <?php
                        $r = 7;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>人件費計</TD>
                    <?php
                        for ($c=0;$c<14;$c++) {
                            if ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10b' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <TD nowrap class='pt10'>旅費交通費</TD>
                    <?php
                        $r = 8;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>海外出張</TD>
                    <?php
                    $r = 9;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>通　信　費</TD>
                    <?php
                    $r = 10;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>会　議　費</TD>
                    <?php
                    $r = 11;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>交際接待費</TD>
                    <?php
                    $r = 12;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>広告宣伝費</TD>
                    <?php
                    $r = 13;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>求　人　費</TD>
                    <?php
                    $r = 14;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>運賃荷造費</TD>
                    <?php
                    $r = 15;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>図書教育費</TD>
                    <?php
                    $r = 16;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>業務委託費</TD>
                    <?php
                    $r = 17;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>事　業　等</TD>
                    <?php
                    $r = 35;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸税公課</TD>
                    <?php
                    $r = 18;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>試験研究費</TD>
                    <?php
                    $r = 19;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>雑　　　費</TD>
                    <?php
                    $r = 20;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>修　繕　費</TD>
                    <?php
                    $r = 21;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保証修理費</TD>
                    <?php
                    $r = 22;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>事務用消耗品費</TD>
                    <?php
                    $r = 23;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>工場消耗品費</TD>
                    <?php
                    $r = 24;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>車　両　費</TD>
                    <?php
                    $r = 25;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>保　険　料</TD>
                    <?php
                    $r = 26;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>水道光熱費</TD>
                    <?php
                    $r = 27;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>諸　会　費</TD>
                    <?php
                    $r = 28;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>支払手数料</TD>
                    <?php
                    $r = 29;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>地代家賃</TD>
                    <?php
                    $r = 30;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>寄　付　金</TD>
                    <?php
                    $r = 31;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>倉　敷　料</TD>
                    <?php
                    $r = 32;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>賃　借　料</TD>
                    <?php
                    $r = 33;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>減価償却費</TD>
                    <?php
                    $r = 34;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>クレーム対応費</TD>
                    <?php
                    $r = 36;     // 該当レコード
                        for ($c=0;$c<14;$c++) {
                            if ($c == 3 || $c == 7 || $c == 11)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 2 || $c == 6 || $c == 10)
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>経費計</TD>
                    <?php
                        for ($c=0;$c<14;$c++) {
                            if ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10b' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>合　計</TD>
                    <?php
                        for ($c=0;$c<14;$c++) {
                            if ($c == 13)                           // 補足事項
                                printf("<td nowrap class='pt10b' align='center'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
