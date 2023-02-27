<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 明細 標準品専用 照会                                                //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/03 Created   sales_standard_pre_view.php                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
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
$menu->set_site( 1, 14);                    // site_index=01(売上メニュー) site_id=14(標準品売上照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$div = $_SESSION['standard_div'];
if ($div == 'A') {                  // 全体
    $menu->set_title('売上明細 全体 照会');
} elseif ($div == 'C') {            // カプラ全体
    $menu->set_title('売上明細 カプラ全体 照会');
} elseif ($div == 'CH') {           // カプラ標準品
    $menu->set_title('売上明細 カプラ標準品 照会');
} elseif ($div == 'CS') {           // カプラ特注
    $menu->set_title('売上明細 カラプ特注品 照会');
} elseif ($div == 'L') {            // リニア標準品
    $menu->set_title('売上明細 リニア全体 照会');
} elseif ($div == 'LL') {           // カプラ標準品
    $menu->set_title('売上明細 リニアのみ 照会');
} elseif ($div == 'LB') {           // カプラ特注
    $menu->set_title('売上明細 バイモル 照会');
} else {
    $menu->set_title('売上明細 原価率分析専用 照会');
}
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('実績工数照会',   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
$menu->set_action('総材料費の履歴', INDUST . 'material/materialCost_view_assy.php');
$menu->set_retGET('sum_exec', 'on');
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    if (isset($_REQUEST['ym_p'])) {
        $last_day = last_day(substr($_REQUEST['ym_p'], 0, 4), substr($_REQUEST['ym_p'], 4, 2));
        $d_start = ($_REQUEST['ym_p'] . '01');
        $d_end   = ($_REQUEST['ym_p'] . $last_day);
    } else {
        $d_start = $_SESSION['standard_d_start'];
        $d_end   = $_SESSION['standard_d_end'];
    }
    $_SESSION['st_view_d_start'] = $d_start;
    $_SESSION['st_view_d_end']   = $d_end;
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['standard_div'];
    $where_div  = $_SESSION['standard_where_div'];
    $kubun      = $_SESSION['standard_kubun'];
    $uri_ritu   = 52;       // リテラルに変更
    $assy_no    = $_SESSION['standard_assy_no'];
    
    ////////////// パスワードチェック
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
    ///////////// 合計金額・件数等を取得
    $query = "select
                    count(数量)                 as t_ken
                    ,
                    sum(数量)                   as t_kazu
                    ,
                    sum(Uround(数量*単価,0))    as t_kingaku
                    ,
                    sum((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 数量)
                                                as 総材料費
              from
                    hiuuri
              left outer join
                    assembly_schedule as assem
                on 計画番号=plan_no
              left outer join
                    aden_master as aden
                on 計画番号=aden.plan_no
              left outer join
                    material_cost_header as mate
                on 計画番号=mate.plan_no
    ";
    //////////// SQL where 句を セッションから取得
    if (isset($_REQUEST['standard_view1'])) {
        $search = $_SESSION['standard_condition1'];       // 条件１
        $view_name = "条件１={$_SESSION['standard_lower_uri_ritu']}% 〜 {$_SESSION['standard_upper_uri_ritu']}%";
    } elseif (isset($_REQUEST['standard_view2'])) {
        $search = $_SESSION['standard_condition2'];       // 条件２
        $view_name = "条件２={$_SESSION['standard_lower_mate_ritu']}% 〜 {$_SESSION['standard_upper_mate_ritu']}%";
    } elseif (isset($_REQUEST['standard_view3'])) {
        $search = $_SESSION['standard_condition3'];       // 条件３
        $view_name = "条件３={$_SESSION['standard_lower_equal_ritu']}% 〜 {$_SESSION['standard_upper_equal_ritu']}%";
    } elseif (isset($_REQUEST['standard_view4'])) {
        $search = $_SESSION['standard_condition4'];       // 条件４
        $view_name = 'その他(総材料費未登録等)';
    } elseif (isset($_REQUEST['standard_view'])) {
        // $search = $_SESSION['standard_condition'];
        $search = '';                                   // 標準品全体
        $view_name = '全体';
    } else {
        // $search = $_SESSION['standard_condition'];
        $search = '';                                   // 標準品全体
        $view_name = '全体';
    }
    $_SESSION['standard_view_name'] = $view_name;
    $where_assy_no = $_SESSION['standard_where_assy_no'];
    // $where = $_SESSION['standard_where'];
    if ($div == 'CH') { // 標準品なら
        $where = "
            where
            kanryou>={$d_start} and kanryou<={$d_end} and {$where_div}
            and
            note15 not like 'SC%' {$where_assy_no}
        ";
        //  計上日>={$d_start} and 計上日<={$d_end} and datatype={$kubun} and {$where_div}
    } elseif ($div == 'CS') { // Ｃ特注なら
        $where = "
            where
            kanryou>={$d_start} and kanryou<={$d_end} and {$where_div}
            and
            note15 like 'SC%' {$where_assy_no}
        ";
        //  計上日>={$d_start} and 計上日<={$d_end} and datatype={$kubun} and {$where_div}
    } else {            // 全体・リニア全体・リニアのみ・バイモル
        $where = "
            where
            kanryou>={$d_start} and kanryou<={$d_end} and {$where_div}
            {$where_assy_no}
        ";
        //  計上日>={$d_start} and 計上日<={$d_end} and datatype={$kubun} and {$where_div}
    }
    $search = ($where . $search);               // グラフと共用するため追加
    $query = sprintf("$query %s", $search);     // SQL query 文の完成
    $_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '合計金額の取得に失敗しました。';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on&page_keep=on');    // 直前の呼出元へ戻る
        exit();
    } else {
        $t_ken     = $res_sum[0]['t_ken'];
        $t_kazu    = $res_sum[0]['t_kazu'];
        $t_kingaku = $res_sum[0]['t_kingaku'];  // 売上金額
        $t_zai     = $res_sum[0]['総材料費'];
        $_SESSION['u_t_ken']  = $t_ken;
        $_SESSION['u_t_kazu'] = $t_kazu;
        $_SESSION['u_t_kin']  = $t_kingaku;
        $_SESSION['u_t_zai']  = $t_zai;
    }
} else {                                                // ページ切替なら
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];
    $t_zai     = $_SESSION['u_t_zai'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['standard_div'];
$where_div  = $_SESSION['standard_where_div'];
$d_start = $_SESSION['st_view_d_start'];
$d_end   = $_SESSION['st_view_d_end'];
// $d_start    = $_SESSION['standard_d_start'];
// $d_end      = $_SESSION['standard_d_end'];
$kubun      = $_SESSION['standard_kubun'];
$uri_ritu   = 52;   // リテラルに変更
$assy_no    = $_SESSION['standard_assy_no'];
$search     = $_SESSION['sales_search'];

///// 条件名の設定
$view_name = $_SESSION['standard_view_name'];

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("select
                            a.kanryou         as 完了予定日,                  -- 0
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
                                WHEN trim(a.plan_no)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE a.plan_no
                            END                     as 計画番号,        -- 2
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                     as 製品番号,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 4
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 5
                            (a.plan - a.cut_plan)   as 数量,            -- 6
                            u.単価          as 仕切単価,                -- 7
                            Uround((a.plan - a.cut_plan) * u.単価, 0) as 金額,         -- 8
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                    as 総材料費,        -- 9
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as 率％,            --10
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS 総材料費2,       --11
                            (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS 率２,            --12
                            (select plan_no from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                                    AS 計画番号2,       --13
                            s.price                 AS 仕切単価,        --14
                            Uround((a.plan - a.cut_plan) * s.price, 0) AS 金額2            --15
                      from
                            assembly_schedule as a
                      left outer join
                            hiuuri as u
                      on u.計画番号=a.plan_no
                      left outer join
                            miitem as m
                      on a.parts_no=m.mipn
                      left outer join
                            material_cost_header as mate
                      on a.plan_no=mate.plan_no
                      left outer join
                           sales_price_nk as s
                      on  a.parts_no=s.parts_no
                      %s AND a.parts_no NOT LIKE '999999999' AND (a.plan - a.cut_plan) != 0
                      AND assy_site = '01111'
                      AND ((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 != 0)
                      OR (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) !=0)
                      order by a.kanryou, a.parts_no
                      ", $search);   // 共用 $search で検索
$res_sum   = array();
$field_sum = array();
if (($rows_sum = getResultWithField3($query, $field_sum, $res_sum)) <= 0) {
    $_SESSION['s_sysmsg'] = '合計金額の取得に失敗しました。';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on&page_keep=on');    // 直前の呼出元へ戻る
    exit();
} else {
    $t_kazu    = 0;
    $t_kingaku = 0;  // 売上金額
    $t_zai     = 0;
    $t_ken     = $rows_sum;
    for ($r=0; $r<$rows_sum; $r++) {
        $t_kazu    += $res_sum[$r][6];
        if ($res_sum[$r][8] == 0) {
            $t_kingaku += $res_sum[$r][15];           // 売上金額2
        } else {
            $t_kingaku += $res_sum[$r][8];            // 売上金額
        }
        if ($res_sum[$r][9] == 0) {
            $t_zai     += $res_sum[$r][11] * $res_sum[$r][6];           // 総材料費2 * 数量
        } else {
            $t_zai     += $res_sum[$r][9] * $res_sum[$r][6];            // 総材料費 * 数量
        }
    }
}

//////////// 表題の設定
$ft_kingaku = number_format($t_kingaku);                    // ３桁ごとのカンマを付加
$ft_zai     = number_format($t_zai);                        // ３桁ごとのカンマを付加
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);
if ($t_kingaku == 0) {
    $zai_ritu = '0.0';
} else {
    $zai_ritu   = number_format(Uround($t_zai / $t_kingaku * 100, 1), 1);   // 合計 総材料 比率
}
if ($t_zai == 0) {
    $kin_ritu = '0.0';
} else {
    $kin_ritu = number_format(Uround($t_kingaku / $t_zai * 100, 1), 1);   // 合計 仕切 比率
}
// $menu->set_caption("<u><font color='red'>{$view_name}</font>：{$f_d_start}〜{$f_d_end}：合計件数={$ft_ken}：合計金額={$ft_kingaku}：合計数量={$ft_kazu}<u>");
$menu->set_caption("
    <u><font color='red'>　{$view_name}</font>：{$f_d_start}〜{$f_d_end}：合計件数={$ft_ken}：合計数量={$ft_kazu}　<br>　
    合計<font style='color:brown;'>売上高={$ft_kingaku}</font>：合計<font style='color:blue;'>総材料費={$ft_zai}　総材料比率={$zai_ritu}%　仕切単価比率={$kin_ritu}%　</font><u>
");

//////////// 一頁の行数
if (isset($_SESSION['standard_sales_page'])) {
    define('PAGE', $_SESSION['standard_sales_page']);
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
$query = sprintf("select
                            a.kanryou         as 完了予定日,                  -- 0
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
                                WHEN trim(a.plan_no)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE a.plan_no
                            END                     as 計画番号,        -- 2
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                     as 製品番号,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 4
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 5
                            (a.plan - a.cut_plan)   as 数量,            -- 6
                            u.単価          as 仕切単価,                -- 7
                            Uround((a.plan - a.cut_plan) * u.単価, 0) as 金額,         -- 8
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                    as 総材料費,        -- 9
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as 率％,            --10
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS 総材料費2,       --11
                            (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS 率２,            --12
                            (select plan_no from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                                    AS 計画番号2,       --13
                            s.price                 AS 仕切単価,        --14
                            Uround((a.plan - a.cut_plan) * s.price, 0) AS 金額2            --15
                      from
                            assembly_schedule as a
                      left outer join
                            hiuuri as u
                      on u.計画番号=a.plan_no
                      left outer join
                            miitem as m
                      on a.parts_no=m.mipn
                      left outer join
                            material_cost_header as mate
                      on a.plan_no=mate.plan_no
                      left outer join
                           sales_price_nk as s
                      on  a.parts_no=s.parts_no
                      %s AND a.parts_no NOT LIKE '999999999' AND (a.plan - a.cut_plan) != 0
                      AND assy_site = '01111'
                      AND ((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 != 0)
                      OR (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) !=0)
                      order by a.kanryou, a.parts_no
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // 共用 $search で検索

$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s〜%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // 直前の呼出元へ戻る
    exit();
} else {
    //$t_kazu    = $res_sum[0]['t_kazu'];
    //$t_kingaku = $res_sum[0]['t_kingaku'];  // 売上金額
    //$t_zai     = $res_sum[0]['総材料費'];
    $num = count($field);       // フィールド数取得
    $t_ken     = $rows;
    for ($r=0; $r<$rows; $r++) {
        $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // 全角カナを半角カナへテスト的にコンバート
        //$t_kazu    = $res_sum[0]['t_kazu'];
        //$t_kingaku = $res_sum[0]['t_kingaku'];  // 売上金額
        //$t_zai     = $res_sum[0]['総材料費'];
    }
    $_SESSION['SALES_TEST'] = sprintf("order by a.kanryou offset %d limit %d", $offset, PAGE);
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.body.focus();                          // F2/F12キーで戻るための対応
    // document.form_name.element_name.select();
}
/* 新規ウィンドウで開く */
function win_open(url, w, h)
{
    if (!w) w = 800;     // 初期値
    if (!h) h = 600;     // 初期値
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    w -= 10; h -= 30;   // 微調整が必要
    window.open(url, '', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
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
    color:              blue;
    text-decoration:    none;
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
<body onLoad='set_focus()' <?php if (PAGE <= 25) echo "style='overflow:hidden;'"?>>
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
                            case 2:    // 計画番号
                                echo "<td class='winbox' nowrap align='center'><a class='pt9' href='javascript:win_open(\"{$menu->out_action('実績工数照会')}?targetPlanNo={$res[$r][$i]}&noMenu=yes\", 900, 600)'>" . $res[$r][$i] . "</a></td>\n";
                                break;
                            case 3:    // 製品番号
                                echo "<td class='winbox' nowrap align='center'><a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('総材料費の履歴')}?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][2]), "\")' target='_self'>" . $res[$r][$i] . "</a></td>\n";
                                break;
                            case 4:     // 製品名
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // 数量
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // 仕切単価
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'><font color='brown'>" . number_format($res[$r][14], 2) . "</font></div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                }
                                break;
                            case 8:     // 金額
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'><font color='brown'>" . number_format($res[$r][15], 0) . "</font></div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                }
                                break;
                            case 9:     // 総材料費
                                if ($res[$r][$i] == 0) {
                                    if ($res[$r][11]) {
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                                , number_format($res[$r][11], 2), "</a></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 10:    // 率(総材料費)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    if ($res[$r][12]) {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][12], 1) . "</div></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            default:    // その他
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
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
                            case 8:     // 売上金額
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9' style='color:brown;'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
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
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
