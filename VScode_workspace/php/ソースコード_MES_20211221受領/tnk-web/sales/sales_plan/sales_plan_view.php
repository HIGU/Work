<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 予定 照会                                                           //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/21 Created   sales_plan_view.php                                 //
// 2011/11/30 カプラ標準とカプラ特注にはNKCTを含まないように変更            //
//            ただし、カプラ全体には含む。またリニアのみとバイモルも        //
//            同様にNKTを含まないよう変更。ただしリニア全体には含む         //
// 2011/12/13 日付をチェックして最終日を自動で調整する機能をコメント化      //
//            予定計画の場合休日でも納期に指定していた為集計できなかった為。//
// 2012/01/05 並び順をASにあわせる為、parts_noから計画No.下5桁へ変更        //
// 2012/03/28 ＮＫＴ部品出庫分(NKTB)の照会を追加                            //
// 2012/12/17 ＮＫＴ部品出庫分で部品名が別のものを取得していたのを訂正      //
// 2013/01/29 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                        //
// 2013/01/29 バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま     //
// 2013/01/31 リニアのみのDPE抜出SQLを訂正                             大谷 //
// 2013/05/28 2013/05よりNKCT/NKTの売上げを抜き出さないように修正      大谷 //
// 2015/05/12 機工生産に対応                                           大谷 //
// 2018/06/08 特注A伝販売単価に対応しようとしたが、52％だけではないので保留 //
// 2018/06/22 特注のエラーメッセージが間違っていたので訂正             大谷 //
// 2018/07/31 shikiriがエラーになっていたので強制                      大谷 //
// 2018/08/21 特注A伝販売単価52％に対応                                大谷 //
//            特注の場合は最新仕切とA伝52%単価の比較表を出力できるように    //
// 2020/12/07 売上予定照会に達成率追加による変更                       和氣 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1, 18);                    // site_index=01(売上メニュー) site_id=18(売上予定明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売 上 予 定 照 会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費履歴',   INDUST . 'material/materialCost_view_assy.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

// 特注A伝販売単価対応保留 強制最新仕切'S'
//$_REQUEST['shikiri'] = 'S';
//$shikiri    = 'S';

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_uri_ritu']   = $_REQUEST['uri_ritu'];
    $_SESSION['s_shikiri']    = $_REQUEST['shikiri'];
    $_SESSION['s_sales_page'] = $_REQUEST['sales_page'];
    $_SESSION['uri_assy_no']  = $_REQUEST['assy_no'];
    $_SESSION['s_tassei']       = $_REQUEST['tassei']; // 2020/12/07 add.
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $uri_ritu   = $_SESSION['s_uri_ritu'];
    $shikiri    = $_SESSION['s_shikiri'];
    $tassei     = $_SESSION['s_tassei']; // 2020/12/07 add.
    // 強制最新仕切
    //$shikiri    = 'S';
    $assy_no    = $_SESSION['uri_assy_no'];
        ///// day のチェック
        /* if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// 最終日をチェックしてセットする
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '日付の指定が不正です！';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
                exit();
            }
        }
        ///// day のチェック
        if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
        ///// 最終日をチェックしてセットする
        if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
            $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '日付の指定が不正です！';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
                exit();
            }
        } */
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// パスワードチェック
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
    if ($div == "NKTB") {  // NKT部品出庫分の場合は別SQLで集計
        ///////////// 合計金額・件数等を取得
        $query = "select
                        count(a.chaku)                     AS t_ken,
                        sum((allo.allo_qt - allo.sum_qt))  AS t_kazu,
                        sum(Uround((allo.allo_qt - allo.sum_qt) * (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1), 0)) AS t_kingaku
                    FROM
                        assembly_schedule as a
                    LEFT OUTER JOIN
                        allocated_parts as allo
                    on a.plan_no=allo.plan_no
                    left outer join
                        miitem as m
                    on a.parts_no=m.mipn";
    } else {
        ///////////// 合計金額・件数等を取得
        $query = "select
                        count((a.plan -a.cut_plan - a.kansei)) as t_ken,
                        sum((a.plan -a.cut_plan - a.kansei)) as t_kazu,
                        sum(Uround((a.plan -a.cut_plan - a.kansei)*(SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1),0)) as t_kingaku
                    from
                        assembly_schedule as a
                    left outer join
                        product_support_master AS groupm
                    on a.parts_no=groupm.assy_no
                    left outer join
                        miitem as m
                    on a.parts_no=m.mipn";
    }
    //////////// SQL where 句を 共用する
    if ($div == "NKTB") {  // NKT部品出庫分の場合は assy_site でチェック
        $search = "WHERE a.chaku>=$d_start AND a.chaku<=$d_end AND (a.plan -a.cut_plan) > 0 AND assy_site='05001' AND (a.plan -a.cut_plan - kansei) > 0 AND (allo.allo_qt - allo.sum_qt) > 0";
    } else {
        $search = "WHERE a.kanryou>=$d_start AND a.kanryou<=$d_end AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0";
    }
    if ($assy_no != '') {       // 製品番号が指定された場合
        $search .= " and a.parts_no like '{$assy_no}%%'";
    } elseif ($div == 'S') {    // Ｃ特注なら
        $search .= " and a.dept='C' and a.note15 like 'SC%%'";
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
        //$search .= " and groupm.support_group_code IS NULL";
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END";
    } elseif ($div == 'D') {    // Ｃ標準なら
        $search .= " and a.dept='C' and (a.note15 NOT like 'SC%%' OR a.note15 IS NULL)";    // 部品売りを標準へする
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
        //$search .= " and groupm.support_group_code IS NULL";
        $search .= " and (CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END)";
    } elseif ($div == "N") {    // リニアのバイモル・試験修理を除く assyno でチェック
        $search .= " and a.dept='L' and (a.parts_no NOT like 'LC%%' AND a.parts_no NOT like 'LR%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
        //$search .= " and CASE WHEN a.parts_no = '' THEN a.dept='L' ELSE m.midsc not like 'DPE%%' END";
        $search .= " and CASE WHEN a.parts_no = '' THEN a.dept='L' ELSE CASE WHEN m.midsc IS NULL THEN a.dept='L' ELSE m.midsc not like 'DPE%%' END END";
        //$search .= " and groupm.support_group_code IS NULL";
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='L' END";
    } elseif ($div == "B") {    // バイモルの場合は assyno でチェック
        //$search .= " and (a.parts_no like 'LC%%' or a.parts_no like 'LR%%')";
        $search .= " and (a.parts_no like 'LC%%' or a.parts_no like 'LR%%' or m.midsc like 'DPE%%')";
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='L' END";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "NKCT") { // NKCTの場合は支援先コード(1)でチェック
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code=1 END";
        //$search .= " and groupm.support_group_code=1";
    } elseif ($div == "NKT") {  // NKTの場合は支援先コード(2)でチェック
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code=2 END";
        //$search .= " and groupm.support_group_code=2";
    } elseif ($div == "C") {
        $search .= " and a.dept='$div'";
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
    } elseif ($div == "L") {
        $search .= " and a.dept='$div'";
        $search .= " and (a.parts_no not like 'SS%%')";
    } elseif ($div == "T") {
        $search .= " and a.dept='$div'";
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
    } elseif ($div == "NKTB") {
    } elseif ($div != " ") {
        $search .= " and a.dept='$div'";
    }
    $query = sprintf("$query %s", $search);     // SQL query 文の完成
    $_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '合計金額の取得に失敗しました。';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    } else {
        $t_ken     = $res_sum[0]['t_ken'];
        $t_kazu    = $res_sum[0]['t_kazu'];
        $t_kingaku = $res_sum[0]['t_kingaku'];
        $_SESSION['u_t_ken']  = $t_ken;
        $_SESSION['u_t_kazu'] = $t_kazu;
        $_SESSION['u_t_kin']  = $t_kingaku;
    }
    $_SESSION['s_yotei']      = "";
} else {                                                // ページ切替なら
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];

    // 2020/12/07 add. ------------------------------------------------------->
    if( isset($_REQUEST['tassei']) ) {
        $_SESSION['s_tassei']       = $_REQUEST['tassei'];
    }

    if( isset($_REQUEST['yotei']) ) {
        $_SESSION['s_yotei']       = $_REQUEST['yotei'];
    }
    if( $_SESSION['s_yotei'] == "on" ) {
        $menu->set_RetUrl(SALES . 'sales_plan/sales_plan_view.php?page_keep=1&yotei=');
    } else {
        $menu->set_RetUrl($menu->out_RetUrl());
    }
    // <-----------------------------------------------------------------------
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$search     = $_SESSION['sales_search'];
$shikiri    = $_SESSION['s_shikiri'];
$tassei     = $_SESSION['s_tassei']; // 2020/12/07 add.
$yotei      = $_SESSION['s_yotei'];  // 2020/12/07 add.
/*
if( isset($_REQUEST['yotei']) ) {
    $menu->set_RetUrl(SALES . 'sales_plan/sales_plan_view.php?page_keep=1&yotei=');
} else {
    $menu->set_RetUrl($menu->out_RetUrl());
}
*/
///// 製品グループ(事業部)名の設定
if ($div == " ") $div_name = "全グループ";
if ($div == "C") $div_name = "カプラ全体";
if ($div == "D") $div_name = "カプラ標準";
if ($div == "S") $div_name = "カプラ特注";
if ($div == "L") $div_name = "リニア全体";
if ($div == "N") $div_name = "リニアのみ";
if ($div == "B") $div_name = "液体ポンプ";
if ($div == "SSC") $div_name = "カプラ試修";
if ($div == "SSL") $div_name = "リニア試修";
if ($div == "NKB") $div_name = "商品管理";
if ($div == "T") $div_name = "ツール";
if ($div == "TRI") $div_name = "試作";
if ($div == "NKCT") $div_name = "ＮＫＣＴ";
if ($div == "NKT") $div_name = "ＮＫＴ";
if ($div == "NKTB") $div_name = "NKT部品出庫分";
if ($div == "_") $div_name = "なし";

//////////// 表題の設定
$ft_kingaku = number_format($t_kingaku);                    // ３桁ごとのカンマを付加
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);
$menu->set_caption("<u>部門=<font color='red'>{$div_name}</font>：{$f_d_start}〜{$f_d_end}：合計件数={$ft_ken}：合計金額={$ft_kingaku}：合計数量={$ft_kazu}<u>");

// 達成率計算用データ取得処理 2020/12/07 add. Start -------------------------->
// /masterst/TNK-WEB/tnk-web/sales/details/sales_view.php よりコピー
if( $tassei == 'tassei' || $yotei != 'on' ) {

    $file_orign     = '../..' . SYS . 'backup/W#TIUKSL.TXT';
    $res            = array();
    $total_price    = 0;    // 金額
    $total_ken      = 0;    // 件数
    $total_count    = 0;    // 数量
    $rec            = 0;    // レコード��
    if (file_exists($file_orign)) {         // ファイルの存在チェック
        $fp = fopen($file_orign, 'r');
        while (!(feof($fp))) {
            $data = fgetcsv($fp, 130, '_');     // 実レコードは103バイトなのでちょっと余裕をデリミタは'_'に注意
            if (feof($fp)) {
                break;
            }
            $num  = count($data);       // フィールド数の取得
            if ($num != 14) {   // AS側の削除レコードは php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
               continue;
            }
            for ($f=0; $f<$num; $f++) {
                $res[$rec][$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
                $res[$rec][$f] = addslashes($res[$rec][$f]);    // "'"等がデータにある場合に\でエスケープする
                // $data_KV[$f] = mb_convert_kana($data[$f]);   // 半角カナを全角カナに変換
            }
            if($res[$rec][5] !='C8385407') {
                $query = sprintf("select midsc from miitem where mipn='%s' limit 1", $res[$rec][3]);
                getUniResult($query, $res[$rec][4]);       // 製品名の取得 (製品コードを上書きする)
                /******** 総材料費の登録済みの項目追加 *********/
                $sql = "
                    SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$rec][5]}'
                ";
                if (getUniResult($sql, $temp) <= 0) {
                    $res[$rec][13] = '登録';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                } else {
                    $res[$rec][13] = '登録済';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                }
                /******** 特注・標準の項目追加 *********/
                $sql2 = "
                    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$res[$rec][5]}'
                ";
                $sc = '';
                getUniResult($sql2, $sc);
                if ($sc == 'SC') {
                    $res[$rec][15] = '特注';
                } else {
                    $res[$rec][15] = '標準';
                }
                /******** 仕切単価が元データにない場合の上書き処理 *********/
                if ($res[$rec][12] == 0) {                                  // 元データに仕切があるかどうか
                    $res[$rec][14] = '1';
                    $sql = "
                        SELECT price FROM sales_price_nk WHERE parts_no='{$res[$rec][3]}'
                    ";
                    if (getUniResult($sql, $sales_price) <= 0) {            // 最新仕切が登録されているか
                        $sql = "
                            SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                        ";
                        if (getUniResult($sql, $sales_price) <= 0) {        // 計画の総材料費が登録されているか
                            $sql_c = "
                                SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                            ";
                            if (getUniResult($sql, $sales_price) <= 0) {    // 製品の総材料費が登録されているか
                                $res[$rec][12] = 0;
                            } else {
                                if ($res[$rec][15] == '特注') {
                                    $res[$rec][12] = round(($sales_price * 1.27), 2);   // 特注のときの倍率？
                                } else {
                                    $res[$rec][12] = round(($sales_price * 1.13), 2);
                                }
                            }
                        } else {
                            if ($res[$rec][15] == '特注') {
                                $res[$rec][12] = round(($sales_price * 1.27), 2);       // 特注のときの倍率？
                            } else {
                                $res[$rec][12] = round(($sales_price * 1.13), 2);
                            }
                        }
                    } else {
                        $res[$rec][12] = $sales_price;
                    }
                } else {
                    $res[$rec][14] = '0';
                }
                /******** 集計 計算 *********/
                $res[$rec][16] = round(($res[$rec][11] * $res[$rec][12]), 0);
                if( $div == " ") {  // グループ全体
                    $total_price  += $res[$rec][16];
                    $total_ken++;
                    $total_count  += $res[$rec][11];
                } else if( $div == "C") {  // カプラ全体
                    if( $res[$rec][0] == 'C' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                } else if( $div == "D") {  // カプラ標準
                    if( $res[$rec][0] == 'C' && $res[$rec][15] == '標準' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                } else if( $div == "S") {  // カプラ特注
                    if( $res[$rec][0] == 'C' && $res[$rec][15] == '特注' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                } else if( $div == "L" || $div == "N" ) {  // リニア全体 リニアのみ
                    if( $res[$rec][0] == 'L' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                }

                $rec++;
            }
        }
        // 0=>'事業部', 1=>'完成日', 3=>'製品番号', 4=>'製品名', 5=>'計画番号', 11=>'完成数', 12=>'仕切単価'
    }
    $t_kingaku3 = $total_price;
    $t_ken3     = $total_ken;
    $t_kazu3    = $total_count;

    ///////////// 合計金額・件数等を取得
    if ( ($div != 'S') && ($div != 'D') ) {      // Ｃ特注と標準 以外なら
        $query2 = "select
                        count(数量) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
    } else {
        $query2 = "select
                        count(数量) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
                  //left outer join
                  //      aden_master as aden
                  //on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)";
    }
    //////////// SQL where 句を 共用する
    $search2 = "where 計上日>=$d_start and 計上日<=$d_end";
    if ($assy_no != '') {       // 製品番号が指定された場合
        $search2 .= " and assyno like '{$assy_no}%%'";
    }
    if ($div == 'S') {    // Ｃ特注なら
        $search2 .= " and 事業部='C' and note15 like 'SC%%'";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == 'D') {    // Ｃ標準なら
        $search2 .= " and 事業部='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END)";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "N") {    // リニアのバイモル・試験修理を除く assyno でチェック
        $search2 .= " and 事業部='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and CASE WHEN assyno = '' THEN 事業部='L' ELSE CASE WHEN m.midsc IS NULL THEN 事業部='L' ELSE m.midsc not like 'DPE%%' END END";
        $search2 .= " and CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='L' END";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "B") {    // バイモルの場合は assyno でチェック
        //$search2 .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
        $search2 .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='L' END";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "SSC") {   // カプラ試験・修理の場合は assyno でチェック
        $search2 .= " and 事業部='C' and (assyno like 'SS%%')";
    } elseif ($div == "SSL") {   // リニア試験・修理の場合は assyno でチェック
        // カプラ試修がなくなったので事業部Lは省く
        //$search2 .= " and 事業部='L' and (assyno like 'SS%%')";
        $search2 .= " and (assyno like 'SS%%')";
    } elseif ($div == "NKB") {  // 商品管理の場合は assyno でチェック
        $search2 .= " and (assyno like 'NKB%%')";
    } elseif ($div == "TRI") {  // 試作の場合は事業部・売上区分・伝票番号でチェック
        $search2 .= " and 事業部='C'";
        $search2 .= " and ( datatype='3' or datatype='7' )";
        $search2 .= " and 伝票番号='00222'";
    } elseif ($div == "NKCT") { // NKCTの場合は支援先コード(1)でチェック
        $search2 .= " and CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code=1 END";
        //$search2 .= " and groupm.support_group_code=1";
    } elseif ($div == "NKT") {  // NKTの場合は支援先コード(2)でチェック
        $search2 .= " and CASE WHEN 計上日>=20111101 and 計上日<20130501 THEN groupm.support_group_code=2 END";
        //$search2 .= " and groupm.support_group_code=2";
    } elseif ($div == "_") {    // 事業部なし
        $search2 .= " and 事業部=' '";
    } elseif ($div == "C") {
        $search2 .= " and 事業部='$div'";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and (assyno not like 'SS%%')";
    } elseif ($div == "L") {
        $search2 .= " and 事業部='$div'";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (assyno not like 'NKB%%')";
    } elseif ($div != " ") {
        $search2 .= " and 事業部='$div'";
    }
    $search2 .= " and datatype='1'"; // 1：完成 固定

    $query2 = sprintf("$query2 %s", $search2);     // SQL query 文の完成
    $res_sum = array();
    if (getResult($query2, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '合計金額の取得に失敗しました。';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    } else {
        $t_ken2     = $res_sum[0]['t_ken'];
        $t_kazu2    = $res_sum[0]['t_kazu'];
        $t_kingaku2 = $res_sum[0]['t_kingaku'];
    }

    // 未検収 表示用
    $ft_kingaku3 = number_format($t_kingaku3);  // ３桁ごとのカンマを付加
    $ft_ken3     = number_format($t_ken3);
    $ft_kazu3    = number_format($t_kazu3);

    // 完了 表示用
    $ft_kingaku2 = number_format($t_kingaku2);  // ３桁ごとのカンマを付加
    $ft_ken2     = number_format($t_ken2);
    $ft_kazu2    = number_format($t_kazu2);

    // 合計 計算用
    $a_kingaku = $t_kingaku + $t_kingaku2 + $t_kingaku3;
    $a_ken = $t_ken + $t_ken2 + $t_ken3;
    $a_kazu = $t_kazu + $t_kazu2 + $t_kazu3;

    // 合計 表示用
    $at_kingaku = number_format($a_kingaku);    // ３桁ごとのカンマを付加
    $at_ken     = number_format($a_ken);
    $at_kazu    = number_format($a_kazu);

    // 達成率
    if( $at_kingaku == 0 ) {
        $ri_kingaku = 0;
    } else {
        $ri_kingaku = round(($t_kingaku2 + $t_kingaku3) / $a_kingaku * 100, 2);
    }
    if( $at_ken == 0 ) {
        $ri_ken = 0;
    } else {
        $ri_ken = round(($t_ken2 + $t_ken3) / $a_ken * 100, 2);
    }
    if( $at_kazu == 0 ) {
        $ri_kazu = 0;
    } else {
        $ri_kazu = round(($t_kazu2 + $t_kazu3) / $a_kazu * 100, 2);
    }
}
// <--------------------------------------------------------2020/12/07 add. End

//////////// 一頁の行数
if (isset($_SESSION['s_sales_page'])) {
    define('PAGE', $_SESSION['s_sales_page']);
} else {
    define('PAGE', 25);
}

//////////// 合計レコード数取得     (対象テーブルの最大数をページ制御に使用)
$maxrows = $t_ken;

//////////// ページオフセット設定
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $_SESSION['sales_offset'] += PAGE;
    if ($_SESSION['sales_offset'] >= $maxrows) {
        $_SESSION['sales_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
    $_SESSION['sales_offset'] -= PAGE;
    if ($_SESSION['sales_offset'] < 0) {
        $_SESSION['sales_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する GETに注意
    $offset = $_SESSION['sales_offset'];
} elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する
    $offset = $_SESSION['sales_offset'];
} else {
    $_SESSION['sales_offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['sales_offset'];

if ($div == "S") {    // 特注の場合 合計金額取得の為、一度SQLを流し合計金額を計算
    if ($shikiri == "A") {    // A伝販売単価52％の場合
        $query = sprintf("select
                                a.kanryou                     AS 完了予定日,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.plan_no
                                END                           AS 計画番号,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS 製品番号,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS 製品名,      -- 3
                                a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                                (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                                              AS 仕切単価,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, 計画番号3
                          ", $search);   // 共用 $search で検索
    } elseif ($shikiri == "AS") {    // A伝販売単価52％＞最新仕切の場合
        $query = sprintf("select
                                a.kanryou                     AS 完了予定日,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.plan_no
                                END                           AS 計画番号,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS 製品番号,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS 製品名,      -- 3
                                a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    ELSE (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                END                           AS 仕切単価,    -- 5
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    ELSE Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                END
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, 計画番号3
                          ", $search);   // 共用 $search で検索
    } else {    // 最新仕切の場合
        $query = sprintf("select
                                a.kanryou                     AS 完了予定日,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.plan_no
                                END                           AS 計画番号,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS 製品番号,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS 製品名,      -- 3
                                a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                                (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                              AS 仕切単価,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, 計画番号3
                          ", $search);   // 共用 $search で検索
    }
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上予定のデータがありません。<br>%s〜%s</font><BR>", format_date($d_start), format_date($d_end));
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    } else {
        $t_kingaku = 0;
        for ($r=0; $r<$rows; $r++) {
            $t_kingaku += $res[$r][6];
        }
        $ft_kingaku = number_format($t_kingaku);                    // ３桁ごとのカンマを付加
        $ft_ken     = number_format($t_ken);
        $ft_kazu    = number_format($t_kazu);
        $f_d_start  = format_date($d_start);                        // 日付を / でフォーマット
        $f_d_end    = format_date($d_end);
        $menu->set_caption("<u>部門=<font color='red'>{$div_name}</font>：{$f_d_start}〜{$f_d_end}：合計件数={$ft_ken}：合計金額={$ft_kingaku}：合計数量={$ft_kazu}<u>");
    }
}

//////////// 表形式のデータ表示用のサンプル Query & 初期化
if ($div == "NKTB") {  // NKT部品出庫分の場合は別SQLで集計
    $query = sprintf("select
                        a.chaku                     AS 出庫予定,  -- 0
                        CASE
                            WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE a.plan_no
                        END                           AS 計画番号,    -- 1
                        CASE
                            WHEN trim(allo.parts_no) = '' THEN '---'
                            ELSE allo.parts_no
                        END                           AS 部品番号,    -- 2
                        CASE
                            WHEN trim(substr(m.midsc,1,38)) = '' THEN ''
                            WHEN m.midsc IS NULL THEN ''
                            ELSE substr(m.midsc,1,38)
                        END                           AS 部品名,      -- 3
                        (allo.allo_qt - allo.sum_qt) AS 数量,        -- 4
                        (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1)
                                                      AS 仕切単価,    -- 5
                        Uround((allo.allo_qt - allo.sum_qt) * (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1), 0)
                                                      AS 金額        -- 6
                  FROM
                        assembly_schedule as a
                  LEFT OUTER JOIN
                        allocated_parts as allo
                  on a.plan_no=allo.plan_no
                  left outer join
                        miitem as m
                  on allo.parts_no=m.mipn
                  %s
                  order by a.chaku, a.plan_no, allo.parts_no
                  offset %d limit %d
                  ", $search, $offset, PAGE);   // 共用 $search で検索
} elseif ($div == "S") {    // 特注の場合
    if ($shikiri == "A") {    // A伝販売単価52％の場合
        $query = sprintf("select
                                a.kanryou                     AS 完了予定日,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.plan_no
                                END                           AS 計画番号,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS 製品番号,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS 製品名,      -- 3
                                a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                                (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                                              AS 仕切単価,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, 計画番号3
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // 共用 $search で検索
    } elseif ($shikiri == "AS") {    // A伝販売単価52％＞最新仕切の場合
        $query = sprintf("select
                                a.kanryou                     AS 完了予定日,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.plan_no
                                END                           AS 計画番号,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS 製品番号,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS 製品名,      -- 3
                                a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    ELSE (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                END                           AS 仕切単価,    -- 5
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    ELSE Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                END
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, 計画番号3
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // 共用 $search で検索
    } else {    // 最新仕切の場合
        $query = sprintf("select
                                a.kanryou                     AS 完了予定日,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE a.plan_no
                                END                           AS 計画番号,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS 製品番号,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS 製品名,      -- 3
                                a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                                (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                              AS 仕切単価,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                              AS 金額,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS 総材料費,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS 率％,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 総材料費2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 率２,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS 計画番号2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 部品材料費,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS 単価登録番号, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS 計画番号3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, 計画番号3
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // 共用 $search で検索
    }
} else {
    $query = sprintf("select
                            a.kanryou                     AS 完了予定日,  -- 0
                            CASE
                                WHEN trim(a.plan_no)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE a.plan_no
                            END                           AS 計画番号,    -- 1
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                           AS 製品番号,    -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END                           AS 製品名,      -- 3
                            a.plan -a.cut_plan - a.kansei AS 数量,        -- 4
                            (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                          AS 仕切単価,    -- 5
                            Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                          AS 金額,        -- 6
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                          AS 総材料費,    -- 7
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                           AS 率％,        -- 8
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS 総材料費2,   -- 9
                            (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS 率２,        --10
                            (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS 計画番号2,   --11
                            (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS 部品材料費,  --12
                            (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS 単価登録番号, --13
                            CASE
                                WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE substr(a.plan_no,4,5)
                            END                           AS 計画番号3    -- 14
                      FROM
                            assembly_schedule as a
                      left outer join
                            miitem as m
                      on a.parts_no=m.mipn
                      left outer join
                            material_cost_header as mate
                      on a.plan_no=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                      left outer join
                            product_support_master AS groupm
                      on a.parts_no=groupm.assy_no
                      %s
                      order by a.kanryou, 計画番号3
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // 共用 $search で検索
}
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上予定のデータがありません。<br>%s〜%s</font><BR>", format_date($d_start), format_date($d_end));
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    for ($r=0; $r<$rows; $r++) {
        $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'EUC-JP');   // 全角カナを半角カナへテスト的にコンバート
    }
/* テスト：予定を取り込む処理 *
    for ($r=0; $r<$rows; $r++) {
        $query = "SELECT * FROM month_sales_plan WHERE plan_no='{$res[$r][1]}' AND parts_no='{$res[$r][2]}'";
        if( getResult2($query, $res_chk) > 0 ) continue;

        if( empty($res[$r][6]) ) {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan ) VALUES ('{$res[$r][0]}', '{$res[$r][1]}', '{$res[$r][2]}', '{$res[$r][3]}', '{$res[$r][4]}');";
        } else {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan, partition_price, price, materials_price, rate) VALUES ('{$res[$r][0]}', '{$res[$r][1]}', '{$res[$r][2]}', '{$res[$r][3]}', '{$res[$r][4]}', '{$res[$r][5]}', '{$res[$r][6]}', '{$res[$r][9]}', '{$res[$r][10]}');";
        }

        if( query_affected($insert_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "月初予定登録失敗。({$r}){$res[$r][6]}";
            $_SESSION['s_sysmsg'] .= $insert_qry;
        }
    }
/**/
}

// ここからCSV出力用の準備作業
// ファイル名に日本語をつけると受け渡しでエラーになるので一時英字に変更
if ($div == " ") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "D") $act_name = "C-hyou";
if ($div == "S") $act_name = "C-toku";
if ($div == "L") $act_name = "L-all";
if ($div == "N") $act_name = "L-hyou";
if ($div == "B") $act_name = "L-bimor";
if ($div == "SSC") $act_name = "C-shuri";
if ($div == "SSL") $act_name = "L-shuri";
if ($div == "NKB") $act_name = "NKB";
if ($div == "T") $act_name = "TOOL";
if ($div == "TRI") $act_name = "SHISAKU";
if ($div == "NKCT") $act_name = "NKCT";
if ($div == "NKT") $act_name = "NKT";
if ($div == "NKTB") $act_name = "NKTB";
if ($div == "_") $act_name = "NONE";

// SQLのサーチ部も日本語を英字に変更。'もエラーになるので/に一時変更
$csv_search = str_replace('\'','/',$search);

// CSVファイル名を作成（開始年月-終了年月-事業部）
$outputFile = $d_start . '-' . $d_end . '-' . $act_name;

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();                          // F2/F12キーで戻るための対応
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>

<?php if (PAGE > 25) { ?>
<body onLoad='set_focus()'>
<?php } else { ?>
<body onLoad='set_focus()' style='overflow:hidden;'>
<?php } ?>
    <center>
<?php echo $menu->out_title_border()?>
    <?php
    if( $tassei != 'tassei' || $yotei == 'on' ) { // 2020/12/07 add.
    ?>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                        
                        <a href='sales_plan_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&div=<?php echo $act_name ?>&shikiri=<?php echo $shikiri ?>'>
                        CSVデータ
                        </a>
                        <?php
                        if ($div == "S") {
                        ?>
                        ／
                        <a href='sales_plan_com_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&div=<?php echo $act_name ?>&shikiri=<?php echo $shikiri ?>'>
                        比較表
                        </a>
                        <?php
                        }
                        ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    if ($i >= 9) break;
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + $offset + 1) . "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        if ($i >= 9) break;
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                            switch ($i) {
                            case 0:     // 計上日
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 3:     // 製品名
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 4:     // 数量
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 5:     // 仕切単価
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                }
                                break;
                            case 6:     // 金額
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // 総材料費
                                if ($res[$r][$i] == 0) {
                                    if ($res[$r][9]) {
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][11]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                                , number_format($res[$r][9], 2), "</a></td>\n";
                                    } elseif ($res[$r][12]) {   // 部品の材料費をチェックして表示する
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('単価登録照会'), "?parts_no=", urlencode($res[$r][2]), "& reg_no={$res[$r][13]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][12], 2), "</a></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][1]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 8:    // 率(総材料費)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>", number_format($res[$r][$i], 1), "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    if ($res[$r][10]) {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][10], 1), "</div></td>\n";
                                    } elseif ($res[$r][12]) {
                                        if ( ($res[$r][5]/$res[$r][12]) < 1.049 ) {   // 赤字表示の分岐
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9' style='color:red;'>", number_format($res[$r][5]/$res[$r][12]*100, 1), "</div></td>\n";
                                        } else {
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][5]/$res[$r][12]*100, 1), "</div></td>\n";
                                        }
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][$i], 1), "</div></td>\n";
                                }
                                break;
                            default:    // その他
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                            }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>総材料費の青色表示は同計画番号で登録がある物で、茶色は同計画では無いが、それ以前で最新の登録を表示</td></tr>
        </table>
    <?php
    } else { // 達成率表示の処理 2020/12/07 add. Start ----------------------->
    ?>
        <!--------------- ここから本文の表を表示する -------------------->
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption><?php echo "期間：{$f_d_start} 〜 {$f_d_end}" ?></caption>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>

                <tr align='center' style='background-color:yellow; color:blue;'>
                    <td><font color='red'><?php echo $div_name ?></font></td>
<!-- page_keep -->
                    <form name='tassei_form' action='<?php echo $menu->out_self() ?>?page_keep=1&yotei=on' method='post'>
                    <td><a href="javascript:tassei_form.submit()">予 定</a></td>
                    </form>
                    <form name='miken_form' action='<?php echo INDUST . "sales_miken/sales_miken_Main.php" ?>?tassei=on' method='post'>
                    <td><a href="javascript:miken_form.submit()">未検収</a></td>
                    </form>
                    <form name='meisai_form' action='<?php echo SALES . "details/sales_view.php?uri_passwd={$uri_passwd}&div={$div}&d_start={$d_start}&d_end={$d_end}&kubun=1&uri_ritu={$uri_ritu}&sales_page={$_SESSION['s_sales_page']}&assy_no=&customer=" . " " . "&syukei=meisai&yotei=on"?>' method='post'>
                    <td><a href="javascript:meisai_form.submit()">完 了</td>
                    </form>
                    <td>合 計</td>
                    <td>達成率</td>
                </tr>
                <tr align='right'>
                    <td align='center' style='background-color:yellow; color:blue;'>件 数</td>
                    <td><?php echo "{$ft_ken} 件" ?></td>
                    <td><?php echo "{$ft_ken3} 件" ?></td>
                    <td><?php echo "{$ft_ken2} 件" ?></td>
                    <td><?php echo "{$at_ken} 件" ?></td>
                    <td><?php echo "{$ri_ken} ％" ?></td>
                </tr>
                <tr align='right'>
                    <td align='center' style='background-color:yellow; color:blue;'>金 額</td>
                    <td><?php echo "{$ft_kingaku} 円" ?></td>
                    <td><?php echo "{$ft_kingaku3} 円" ?></td>
                    <td><?php echo "{$ft_kingaku2} 円" ?></td>
                    <td><?php echo "{$at_kingaku} 円" ?></td>
                    <td><?php echo "{$ri_kingaku} ％" ?></td>
                </tr>
                <tr align='right'>
                    <td align='center' style='background-color:yellow; color:blue;'>数 量</td>
                    <td><?php echo "{$ft_kazu} 個" ?></td>
                    <td><?php echo "{$ft_kazu3} 個" ?></td>
                    <td><?php echo "{$ft_kazu2} 個" ?></td>
                    <td><?php echo "{$at_kazu} 個" ?></td>
                    <td><?php echo "{$ri_kazu} ％" ?></td>
                </tr>

        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    <?php
    } // <------------------------------------------------- 2020/12/07 add. End
    ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
