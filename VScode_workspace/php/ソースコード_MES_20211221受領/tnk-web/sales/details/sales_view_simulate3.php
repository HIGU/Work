<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 明細 照会  new version   sales_view.php                             //
// Copyright (C) 2001-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   sales_view.php                                      //
// 2002/08/07 セッション管理を追加                                          //
// 2002/09/26 select文を left outer join on u.assyno=m.mipn に変更          //
// 2003/01/10 substr($res[$r][$n],0,38)→mb_substr($res[$r][$n],0,12)       //
//                   マルチバイトに対応させてX軸の画面内に収める            //
// 2003/06/16 合計金額・件数・数量を SQL で取得 明細は１ページ分のみ        //
//              取得に Logic を大幅に変更   事業部にバイモルを追加          //
// 2003/09/05 購買単価の登録が０の場合を考慮したロジックへ変更              //
//            総材料費の登録が０の場合も同様(既に対応済み)                  //
//            error_reporting = E_ALL 対応のため 配列変数の初期化追加       //
// 2003/10/31 個別 製品番号 指定 追加  事業部にカプラ特注を追加             //
// 2003/11/26 デザインとロジックを一新 view_uriage.php → sales_view.php    //
// 2003/11/28 Ｃ特注の販売価格を追加 left outerをassymblyに対してjoinし     //
//            on結合は plan_noだけで行う indexも plan_no だけに変更         //
// 2003/12/11 Ｃ特注の場合の製品名 width='150' → width='170' へ変更        //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用し管理する      //
// 2003/12/17 Ｃ特注の総材料費のチェックロジックを追加 (総材料費入力中)     //
// 2003/12/19 総材料費照会のリンクロジックを作成 現在はＣ特注のみ           //
//            $_SESSION['offset']→$_SESSION['sales_offset']に  元の頁へ戻る//
// 2003/12/22 製品名の全角カナ英数字を半角カナ英数字へtest的にコンバート    //
//            Ｃ特注以外も総材料費・率 照会のリンクロジックを作成           //
// 2003/12/23 販売単価・率 及び 総材料費・率 が０の場合は '-'に変換して表示 //
// 2003/12/24 ob_gzhandlerをＸ 使用すると１頁１００件の時にGETが戻らないため//
//            order by 計上日 に , assynoを追加 １頁の行数を変更しても OK   //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/11/01 特注以外の総材料費を計画番号の登録がなければ最後の登録を使う  //
// 2004/11/09 部門を全グループ・カプラ全体・特注・標準・リニア全体等に分けた//
// 2005/01/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//              set_focus()に document.focus()を使い F2/F12キーを有効にした //
// 2005/02/01 総材料費のmate.sum_priceが0の物があり計画番号=C1261631その対応//
//             mate.sum_price <= 0    具体的には部品は支給品だけで組立費のみ//
//                     ↓                                                   //
//            (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 //
// 2005/05/27 PAGE > 25 により style='overflow:hidden;' の制御を追加        //
// 2005/06/03 regdate DESC → assy_no DESC, regdate DESC へindex変更による  //
// 2005/09/06 グループ(事業部)が無いのもがあるのでチェック出来るように追加  //
// 2005/09/21 日付チェックの検証用にcheckdate(month, day, year)を使用       //
// 2006/01/24 WHEN m.midsc IS NULL THEN '&nbsp;' を追加                     //
// 2006/02/01 特注以外の照会時に部品の材料費を表示し率も追加 105未満は赤字  //
//            parts_cost_history より取得 継続のみにする場合はkubun=1を追加 //
// 2006/02/02 上記のリンク先を単価登録照会追加 &reg_noは文字化け→& reg_no  //
// 2006/02/12 部品の材料費取得SQL文を SUB→JOIN へ変更しスピードアップ      //
// 2006/03/22 総材料費等のリンクをクリックして戻った時に行マーカー追加      //
// 2006/09/21 sales/details ディレクトリの下に再配置                        //
// 2007/04/18 2007/04/02以前での総材料を表示(暫定仕切単価アップのため)      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
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
$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売 上 明 細 2007年4月2日暫定仕切単価シミュレーション');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    $_SESSION['s_uri_passwd'] = $_POST['uri_passwd'];
    $_SESSION['s_div']        = $_POST['div'];
    $_SESSION['s_d_start']    = $_POST['d_start'];
    $_SESSION['s_d_end']      = $_POST['d_end'];
    $_SESSION['s_kubun']      = $_POST['kubun'];
    $_SESSION['s_uri_ritu']   = $_POST['uri_ritu'];
    $_SESSION['s_sales_page'] = $_POST['sales_page'];
    $_SESSION['uri_assy_no']  = $_POST['assy_no'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $kubun      = $_SESSION['s_kubun'];
    $uri_ritu   = $_SESSION['s_uri_ritu'];
    $assy_no    = $_SESSION['uri_assy_no'];
        ///// day のチェック
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
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
        }
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// パスワードチェック
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
    ///////////// 合計金額・件数等を取得
    if ( ($div != 'S') && ($div != 'D') ) {      // Ｃ特注と標準 以外なら
        $query = "select
                        count(数量) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri";
    } else {
        $query = "select
                        count(数量) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no";
                  //left outer join
                  //      aden_master as aden
                  //on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)";
    }
    //////////// SQL where 句を 共用する
    $search = "where 計上日>=$d_start and 計上日<=$d_end";
    if ($assy_no != '') {       // 製品番号が指定された場合
        $search .= " and assyno like '{$assy_no}%%'";
    } elseif ($div == 'S') {    // Ｃ特注なら
        $search .= " and 事業部='C' and note15 like 'SC%%'";
    } elseif ($div == 'D') {    // Ｃ標準なら
        $search .= " and 事業部='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
    } elseif ($div == "N") {    // リニアのバイモルを除く assyno でチェック
        $search .= " and 事業部='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
    } elseif ($div == "B") {    // バイモルの場合は assyno でチェック
        $search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
    } elseif ($div == "_") {    // 事業部なし
        $search .= " and 事業部=' '";
    } elseif ($div != " ") {
        $search .= " and 事業部='$div'";
    }
    if ($kubun != " ") {
        $search .= " and datatype='$kubun'";
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
} else {                                                // ページ切替なら
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$search     = $_SESSION['sales_search'];

///// 製品グループ(事業部)名の設定
if ($div == " ") $div_name = "全グループ";
if ($div == "C") $div_name = "カプラ全体";
if ($div == "D") $div_name = "カプラ標準";
if ($div == "S") $div_name = "カプラ特注";
if ($div == "L") $div_name = "リニア全体";
if ($div == "N") $div_name = "リニアのみ";
if ($div == "B") $div_name = "バイモルのみ";
if ($div == "T") $div_name = "ツール";
if ($div == "_") $div_name = "なし";

//////////// 表題の設定
$ft_kingaku = number_format($t_kingaku);                    // ３桁ごとのカンマを付加
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);
$menu->set_caption("<u>部門=<font color='red'>{$div_name}</font>：{$f_d_start}〜{$f_d_end}：合計件数={$ft_ken}：合計金額={$ft_kingaku}：合計数量={$ft_kazu}<u>");

//////////// 一頁の行数
if (isset($_SESSION['s_sales_page'])) {
    define('PAGE', $_SESSION['s_sales_page']);
} else {
    define('PAGE', 25);
}

//////////// 合計レコード数取得     (対象テーブルの最大数をページ制御に使用)
$maxrows = $t_ken;

//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['sales_offset'] += PAGE;
    if ($_SESSION['sales_offset'] >= $maxrows) {
        $_SESSION['sales_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['sales_offset'] -= PAGE;
    if ($_SESSION['sales_offset'] < 0) {
        $_SESSION['sales_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {                // 現在のページを維持する GETに注意
    $offset = $_SESSION['sales_offset'];
} elseif ( isset($_GET['page_keep']) ) {                // 現在のページを維持する
    $offset = $_SESSION['sales_offset'];
} else {
    $_SESSION['sales_offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['sales_offset'];

//////////// 表形式のデータ表示用のサンプル Query & 初期化
if ($div != 'S') {      // Ｃ特注 以外なら
    $query = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '完成'
                                WHEN u.datatype=2 THEN '個別'
                                WHEN u.datatype=3 THEN '手打'
                                WHEN u.datatype=4 THEN '調整'
                                WHEN u.datatype=5 THEN '移動'
                                WHEN u.datatype=6 THEN '直納'
                                WHEN u.datatype=7 THEN '売上'
                                WHEN u.datatype=8 THEN '振替'
                                WHEN u.datatype=9 THEN '受注'
                                ELSE u.datatype
                            END             as 区分,                    -- 1
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 4
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 5
                            u.数量          as 数量,                    -- 6
                            u.単価          as 仕切単価,                -- 7
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 8
                            mate.sum_price + Uround(mate.assy_time * assy_rate, 2)
                                                    as 総材料費,        -- 9
                            CASE
                                WHEN (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.単価 / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100
                            END                     as 率％,            --10
                            -- (select sum_price + Uround(assy_time * assy_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=計上日 order by assy_no DESC, regdate DESC limit 1)
                            (select sum_price + Uround(assy_time * assy_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<'20070402 240000' order by assy_no DESC, regdate DESC limit 1)
                                                    AS 総材料費2,       --11
                            -- (select Uround(u.単価 / (sum_price + Uround(assy_time * assy_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=計上日 order by assy_no DESC, regdate DESC limit 1)
                            (select Uround(u.単価 / (sum_price + Uround(assy_time * assy_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<'20070402 240000' order by assy_no DESC, regdate DESC limit 1)
                                                    AS 率２,            --12
                            -- (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=計上日 order by assy_no DESC, regdate DESC limit 1)
                            (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<'20070402 240000' order by assy_no DESC, regdate DESC limit 1)
                                                    AS 計画番号2,       --13
                            CASE
                                WHEN mate.sum_price IS NULL THEN
                                -- (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<計上日 AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                pmate.unit_cost
                                ELSE 0
                            END                     AS 部品材料費,      --14
                            CASE
                                WHEN mate.sum_price IS NULL THEN
                                -- (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<計上日 AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                pmate.cost_reg
                                ELSE 0
                            END                     AS 単価登録番号     --15
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.計画番号=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.計画番号=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.計上日=pmate.sales_date)
                      %s
                      order by 計上日, assyno
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // 共用 $search で検索
} else {    ////////////////////////////////////////// Ｃ特注の場合
    $query = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '完成'
                                WHEN u.datatype=2 THEN '個別'
                                WHEN u.datatype=3 THEN '手打'
                                WHEN u.datatype=4 THEN '調整'
                                WHEN u.datatype=5 THEN '移動'
                                WHEN u.datatype=6 THEN '直納'
                                WHEN u.datatype=7 THEN '売上'
                                WHEN u.datatype=8 THEN '振替'
                                WHEN u.datatype=9 THEN '受注'
                                ELSE u.datatype
                            END             as 区分,                    -- 1
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 2
                            u.assyno        as 製品番号,                -- 3
                            CASE
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,18)
                            END                     as 製品名,          -- 4
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 5
                            u.数量          as 数量,                    -- 6
                            u.単価          as 仕切単価,                -- 7
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 8
                            trim(a.note15)  as 工事番号,                -- 9
                            aden.order_price  as 販売単価,              --10
                            CASE
                                WHEN aden.order_price <= 0 THEN '0'
                                ELSE Uround(u.単価 / aden.order_price, 3) * 100
                            END                     as 率％,            --11
                            mate.sum_price + Uround(mate.assy_time * assy_rate, 2)
                                                    as 総材料費,        --12
                            CASE
                                WHEN (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.単価 / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100
                            END                     as 率％             --13
                      from
                            (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                      left outer join
                            assembly_schedule as a
                      on u.計画番号=a.plan_no
                      left outer join
                            aden_master as aden
                      -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                      on (a.plan_no=aden.plan_no)
                      left outer join
                            material_cost_header as mate
                      on u.計画番号=mate.plan_no
                      %s
                      order by 計上日, assyno
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // 共用 $search で検索
}
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s〜%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    for ($r=0; $r<$rows; $r++) {
        $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // 全角カナを半角カナへテスト的にコンバート
    }
    $_SESSION['SALES_TEST'] = sprintf("order by 計上日 offset %d limit %d", $offset, PAGE);
}

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
                    if ($i >= 11) if ($div != 'S') break;
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
                        if ($i >= 11) if ($div != 'S') break;
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        if ($div != 'S') { // Ｃ特注 以外なら
                            switch ($i) {
                            case 0:     // 計上日
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 4:     // 製品名
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // 数量
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // 仕切単価
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 8:     // 金額
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 9:     // 総材料費
                                if ($res[$r][11]) {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                            , number_format($res[$r][11], 2), "</a></td>\n";
                                } elseif ($res[$r][14]) {   // 部品の材料費をチェックして表示する
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('単価登録照会'), "?parts_no=", urlencode($res[$r][3]), "& reg_no={$res[$r][15]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][14], 2), "</a></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                }
                                break;
                            case 10:    // 率(総材料費)
                                if ($res[$r][12]) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][12], 1), "</div></td>\n";
                                } elseif ($res[$r][14]) {
                                    if ( ($res[$r][7]/$res[$r][14]) < 1.049 ) {   // 赤字表示の分岐
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9' style='color:red;'>", number_format($res[$r][7]/$res[$r][14]*100, 1), "</div></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][7]/$res[$r][14]*100, 1), "</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                }
                                break;
                            default:    // その他
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                            }
                        } else {        // Ｃ特注なら
                            switch ($i) {
                            case 0:     // 計上日
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 4:     // 製品名
                                echo "<td class='winbox' nowrap width='130' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // 数量
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // 仕切単価
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 8:     // 金額
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 10:    // 販売単価
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='55' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='55' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                }
                                break;
                            case 11:    // 率
                                if ($res[$r][$i] > 0 && $res[$r][$i] < $uri_ritu) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            case 12:    // 総材料費
                                if ($res[$r][$i] == 0) {
                                    // echo "<td nowrap width='60' align='right' class='pt9'>" . number_format($res[$r][$i], 2) . "</td>\n";
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 13:    // 率(総材料費)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            default:    // その他
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            }
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
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>総材料費の青色表示は同計画番号で登録がある物で、茶色は同計画では無いが、それ以前で最新の登録を表示</td></tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
