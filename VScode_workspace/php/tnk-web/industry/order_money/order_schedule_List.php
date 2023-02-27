<?php
//////////////////////////////////////////////////////////////////////////////////////////
// 納入予定金額の照会グラフ                                                             //
// Copyright (C) 2009-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp                //
// Changed history                                                                      //
// 2009/11/09 Created  order_schedule_List.php(/orderを/order_moneyにコピーして流用     //
// 2010/05/26 タイトルが違うので修正                                                    //
// 2015/05/25 前月分を常に表示するように変更、ツールもC/Lと同様に明細表示               //
// 2018/06/29 多部門のT部品購入に対応                                              大谷 //
//////////////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
require_once ('order_function.php');        // order 関係の共通 function
require_once ('../../tnk_func.php');        // TNK date_offset()で使用
//////////// セッションのインスタンスを登録
$session = new Session();
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('予定明細', INDUST . 'order/order_details/order_details.php');
$menu->set_action('予定明細', INDUST . 'order/order_details/order_details_Main.php');
$menu->set_action('予定明細次工程', INDUST . 'order/order_details/order_details_next.php');
$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('納入予定と検査仕掛明細の照会');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///////// パラメーターチェックと設定
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];                // 事業部
    $_SESSION['div'] = $_REQUEST['div'];    // セッションに保存
} else {
    if (isset($_SESSION['div'])) {
        $div = $_SESSION['div'];            // Default(セッションから)
    } else {
        $div = 'C';                         // 初期値(カプラ)あまり意味は無い
    }
}
if (isset($_REQUEST['miken'])) {
    $select = 'miken';                      // 未検収リスト
    $_SESSION['select'] = 'miken';          // セッションに保存
} elseif (isset($_REQUEST['insEnd'])) {
    $select = 'insEnd';                     // 検収済リスト
    $_SESSION['select'] = 'insEnd';         // セッションに保存
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // 納入予定グラフ
    $_SESSION['select'] = 'graph';          // セッションに保存
} elseif (isset($_REQUEST['list'])) {
    $select = 'list';                      // 納入予定集計
    $_SESSION['select'] = 'list';          // セッションに保存
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(セッションから)
    } else {
        $select = 'graph';                  // 初期値(納入予定グラフ)あまり意味は無い
    }
}
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];      // 部品番号の指定があれば検索する
    // $select = 'miken';                      // 未検収リスト  2007/09/30 検査済と共用するためコメント
    // $_SESSION['select'] = 'miken';          // セッションに保存
} else {
    $parts_no = '';                         // 初期化のみ
}
/////////// 部品番号又は発注先コードで検索 処理
$where_parts = true;
if (is_numeric($parts_no) && strlen($parts_no) == 5) {
    // 発注先コードで検索
    $where_parts = false;
    $vendor_query = "SELECT vendor FROM vendor_master WHERE vendor = '{$parts_no}'";
    if (getResult2($vendor_query, $chk_res) <= 0) {
        $where_parts = true;
    }
}
if ($where_parts) {
    // 部品番号で検索
    if (preg_match('/\*/', $parts_no)) {
        $parts_no = str_replace('*', '%', $parts_no);   // like文に対応させる
    } else {
        $parts_no = ('%' . $parts_no . '%');
    }
}

if (isset($_REQUEST['order_seq'])) {
    $order_seq = $_REQUEST['order_seq'];
} else {
    $order_seq = '';    // 初期化のみ アンカーで使用するため
}

/////////// 画面情報の取得
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

$uniq = 'id=' . uniqid('order');    // キャッシュ防止用ユニークID
/////////// クライアントのホスト名(又はIP Address)の取得
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);

/////////// 開始日時の登録ロジック
while (isset($_REQUEST['str'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['str'];
    acceptanceInspectionStart($order_seq, $hostName);
    break;
}
/////////// 終了日時の登録ロジック
while (isset($_REQUEST['end'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['end'];
    acceptanceInspectionEnd($order_seq, $hostName);
    break;
}
/////////// 開始・終了日時のキャンセル ロジック
while (isset($_REQUEST['cancel'])) {        // cancel は使えない事に注意！
    if (!client_check()) break;
    $order_seq = $_REQUEST['cancel'];
    acceptanceInspectionCancel($order_seq, $hostName);
    break;
}

if ($select == 'miken' || $select == 'insEnd') {
    $where_div = getDivWhereSQL($div);
    if ($parts_no == '') {
        $where_parts = '';                                      // 何もしない
    } elseif ($where_parts) {
        $where_parts = "AND data.parts_no like '{$parts_no}'";  // 部品番号でlike検索
    } else {
        $where_parts = "AND data.vendor = '{$parts_no}'";       // 発注先コードで検索(like文にすると上記と重なりあいまいになる）
    }
    if ($select == 'miken') {
        $ken_date = 'ken_date = 0       -- 未検収分';
        $timestamp = "( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )";
    } else {
        $str_date = date_offset(3);
        $end_date = date_offset(0);
        $ken_date = "ken_date = 0 AND end_timestamp IS NOT NULL";
        $timestamp = 'true'; // "ken.end_timestamp IS NOT NULL";
    }
    ////////// SQL Statement を取得
    $query = getSQLbody($ken_date, $timestamp, $where_div, $where_parts);
    $res = array();
    if (($rows = getResult($query, $res)) <= 0) {
        if ($select == 'miken') {
            $_SESSION['s_sysmsg'] = "検査仕掛がありません！";
            if (strlen($parts_no) == 11) {
                $_SESSION['s_sysmsg'] .= ' ' . getItemMaster(str_replace('%', '', $parts_no));
            }
        } else {
            $_SESSION['s_sysmsg'] = "検査済データがありません！";
        }
        $view = 'NG';
    } else {
        $view = 'OK';
    }
    ////////// 検査済リストは2回に分けて取得(最適化のため)
    if ($select == 'insEnd') {
        $ken_date = "ken_date >= {$str_date} AND ken_date <= {$end_date}";
        $query = getSQLbody($ken_date, $timestamp, $where_div, $where_parts);
        $res2 = array();
        if (($rows2=getResult($query, $res2)) <= 0 && $rows <= 0) {
            $_SESSION['s_sysmsg'] = "検査済データがありません！";
            if (strlen($parts_no) == 11) {
                $_SESSION['s_sysmsg'] .= ' ' . getItemMaster(str_replace('%', '', $parts_no));
            }
            $view = 'NG';
        } else {
            $_SESSION['s_sysmsg'] = '';
            $i = $rows;
            foreach ($res2 as $tmpArray) {
                foreach ($tmpArray as $key => $value) {
                    $res[$i][$key] = $value;
                }
                ++$i;
            }
            $view = 'OK';
        }
    }
} elseif ($select == 'graph') {
    //////////// 検査仕掛分(未検収件数)の合計を取得
    $where_div = getDivWhereSQL($div);
    $query = "SELECT  sum(data.order_q * data.order_price)
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    {$where_div}
                LIMIT 1
    ";
    if (getUniResult($query, $res_miken) <= 0) {
        $res_miken = 0;
    }
    ////////// 前日までの注残件数を取得
    $yesterday = date('Ymd', time() - 86400);
    $lower_limit_day = date('Ymd', time() - (86400*200));
    //$upper_limit_day = date('Ymd', time() + (86400*93));
    $upper_limit_day_t = date('Ym');
    $upper_limit_day = $upper_limit_day_t . '31';
    if ($div == 'C') {
        $all_title = 'カプラ全体 納入予定 当月合計金額　';
        $where_div = "data.parts_no like 'C%' AND proc.locate != '52   '";
        $graph_title = 'カプラ全体 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = 'カプラ全体 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'C%' AND proc.locate != '52   '";
        $graph2_title = 'カプラ全体 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = 'カプラ全体 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'SC') {
        $all_title = 'カプラ特注品 納入予定 当月合計金額　';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no like '%SC%' AND proc.locate != '52   '";
        $graph_title = 'カプラ特注品 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = 'カプラ特注品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no like '%SC%' AND proc.locate != '52   '";
        $graph2_title = 'カプラ特注品 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = 'カプラ特注品 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'CS') {
        $all_title = 'カプラ標準品 納入予定 当月合計金額　';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $graph_title = 'カプラ標準品 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = 'カプラ標準品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $graph2_title = 'カプラ標準品 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = 'カプラ標準品 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'L') {
        $all_title = 'リニア全体 納入予定 当月合計金額　';
        $where_div = "data.parts_no like 'L%' AND proc.locate != '52   '";
        $graph_title = 'リニア全体 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = 'リニア全体 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'L%' AND proc.locate != '52   '";
        $graph2_title = 'リニア全体 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = 'リニア全体 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'T') {
        $all_title = '親機種が機工部品 納入予定 当月合計金額　';
        $where_div = "data.parts_no like 'T%' AND proc.locate != '52   '";
        $graph_title = '親機種が機工部品 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = '親機種が機工部品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'T%' AND proc.locate != '52   '";
        $graph2_title = '親機種が機工部品 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = '親機種が機工部品 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'F') {
        $all_title = '親機種がＦＡ部品 納入予定 当月合計金額　';
        $where_div = "data.parts_no like 'F%' AND proc.locate != '52   '";
        $graph_title = '親機種がＦＡ部品 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = '親機種がＦＡ部品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'F%' AND proc.locate != '52   '";
        $graph2_title = '親機種がＦＡ部品 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = '親機種がＦＡ部品 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'A') {
        $all_title = '栃木全体 納入予定 当月合計金額　';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate != '52   '";
        $graph_title = '栃木全体 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = '栃木全体 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate != '52   '";
        $graph2_title = '栃木全体 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = '栃木全体 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'N') {
        $all_title = '日東工器(カプラ) 納入予定 当月合計金額　';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate = '52   '";
        $graph_title = '日東工器(カプラ) 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = '日東工器(カプラ) 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate = '52   '";
        $graph2_title = '日東工器(カプラ) 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = '日東工器(カプラ) 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'NKB') {
        $all_title = 'ＮＫＢ 納入予定 当月合計金額　';
        $where_div = "plan.locate = '14'";
        $graph_title = 'ＮＫＢ 納入予定 当月金額グラフ (注文書発行済み)　（千円）';
        $total_title = 'ＮＫＢ 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "plan.locate = '14'";
        $graph2_title = 'ＮＫＢ 次工程 納入予定 当月金額グラフ (注文書未発行)　（千円）';
        $total2_title = 'ＮＫＢ 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    //////////// 納期遅れ分の合計を取得
    $query = "SELECT sum(data.order_q * data.order_price)
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    {$where_div}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan) <= 0) {
        $res_zan = 0;
    }
    //////////// 棒グラフの予定日 日数  2007/09/22 ADD
    $page = 22;
    $maxrows = 66;
    //////////// ページオフセット設定
    $offset = $session->get_local('offset');
    if ($offset == '') $offset = 0;         // 初期化
    if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
        $offset += $page;
        if ($offset >= $maxrows) {
            $offset -= $page;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "次頁はありません。";
            } else {
                $_SESSION['s_sysmsg'] .= "次頁はありません。";
            }
        }
    } elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
        $offset -= $page;
        if ($offset < 0) {
            $offset = 0;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "前頁はありません。";
            } else {
                $_SESSION['s_sysmsg'] .= "前頁はありません。";
            }
        }
    } elseif ( isset($_REQUEST['page_keep']) ) {               // 現在のページを維持する
        $offset = $offset;
    } elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する
        $offset = $offset;
    } else {
        $offset = 0;                            // 初回の場合は０で初期化
    }
    $session->add_local('offset', $offset);
    
    /////////// 本日以降のサマリーを取得
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "注残データがありません！";
        $view = 'NG';
        $month_all = 0;
        $month_total  = 0;
    } else {
        $view = 'OK';
        $datax = array(); $datay = array();
        $datax[0] = mb_convert_encoding('検査仕掛', 'UTF-8');
        $datax_color[0] = 'blue';
        $datay[0] = $res_miken / 1000;
        $datax[1] = mb_convert_encoding('納期遅れ', 'UTF-8');
        $datay[1] = $res_zan / 1000;
        $datax_color[1] = 'darkred';
        $month_total = $res_miken + $res_zan;
        for ($i=0; $i<$rows; $i++) {
            $datax[$i+2] = $res[$i]['delivery'];
            $datay[$i+2] = ($res[$i]['kin'] / 1000);
            $month_total += $res[$i]['kin'];
            $datax_color[$i+2] = 'black';
        }
        $month_all = $month_total;
        $month_total  = number_format($month_total, 0);
        require_once ('../../../jpgraph-4.4.1/src/jpgraph.php');
        require_once ('../../../jpgraph-4.4.1/src/jpgraph_bar.php');
        $graph = new Graph(820, 360);               // グラフの大きさ X/Y
        $graph->SetScale('textlin'); 
        $graph->img->SetMargin(50, 30, 40, 70);    // グラフ位置のマージン 左右上下
        $graph->SetShadow(); 
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
        $graph->title->Set(mb_convert_encoding($graph_title, 'UTF-8')); 
        $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph->yaxis->title->Set(mb_convert_encoding('金額', 'UTF-8'));
        $graph->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph->xaxis->SetTickLabels($datax, $datax_color); // 項目設定
        // $graph->xaxis->SetFont(FF_FONT1);     // フォントはボールドも指定できる。
        $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);     // フォントはボールドも指定できる。
        $graph->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot = new BarPlot($datay); 
        $bplot->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot->SetColor('navy');
        $bplot->value->SetFormat('%d');     // 整数フォーマット
        $bplot->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/26 追加
        $bplot->value->Show();              // 数値表示
        $targ = array();
        $alts = array();
        $targ[0] = "";
        $alts[0] = '検査仕掛の金額＝%3d';
        $targ[1] = "JavaScript:win_open3('" . $menu->out_action('予定明細') . "?date=OLD')";
        $alts[1] = '納期遅れの金額＝%3d';
        for ($i=0; $i<$rows; $i++) {
            $targ[$i+2] = "JavaScript:win_open('" . $menu->out_action('予定明細') . "?date={$datax[$i+2]}')";
            $alts[$i+2] = "'{$datax[$i+2]}の納入予定 金額＝%3d";
        }
        $bplot->SetCSIMTargets($targ, $alts); 
        $graph->Add($bplot);
        // $graph_name = ('graph/order' . session_id() . '.png');
        $graph_name = "graph/order_schedule_{$_SESSION['User_ID']}.png";
        $graph->Stroke($graph_name);
        chmod($graph_name, 0666);                   // fileを全てrwモードにする
    }
    //////////// グラフ２ 次工程品(注文書未発行)の納入予定グラフの作成 //////////////
    //////////// 納期遅れ分の合計を取得
    $query = "SELECT sum(plan.order_q * proc.order_price)
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    {$where_div2}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan2) <= 0) {
        $res_zan2 = 0;
    }
    /////////// 本日以降のサマリーを取得
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- 注文書発行済みに+1 2007/09/22 変更により上記グラフと同じ
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "次工程の注残データがありません！";
        $view_graph2 = 'NG';
        $month_all  = number_format($month_all, 0);
        $month_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $datax2 = array(); $datay2 = array();
        $datax2[0] = mb_convert_encoding('納期遅れ', 'UTF-8');
        $datax2_color[0] = 'darkred';
        $datay2[0] = $res_zan2 / 1000;
        $month_total2 = $res_zan2;
        for ($i=0; $i<$rows; $i++) {
            $datax2[$i+1] = $res2[$i]['delivery'];
            $datay2[$i+1] = ($res2[$i]['kin'] / 1000);
            //$datay2[$i+1] = $res2[$i]['cnt'];
            $month_total2 += $res2[$i]['kin'];
            $datax2_color[$i+1] = 'black';
        }
        $month_all = $month_all + $month_total2;
        $month_total2  = number_format($month_total2, 0);
        $month_all  = number_format($month_all, 0);
        require_once ('../../../jpgraph-4.4.1/src/jpgraph.php');
        require_once ('../../../jpgraph-4.4.1/src/jpgraph_bar.php');
        $graph2 = new Graph(820, 360);               // グラフの大きさ X/Y
        $graph2->SetScale('textlin'); 
        $graph2->img->SetMargin(50, 30, 40, 70);    // グラフ位置のマージン 左右上下
        $graph2->SetShadow(); 
        $graph2->title->SetFont(FF_GOTHIC, FS_NORMAL, 14); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
        $graph2->title->Set(mb_convert_encoding($graph2_title, 'UTF-8')); 
        $graph2->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph2->yaxis->title->Set(mb_convert_encoding('金額', 'UTF-8'));
        $graph2->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph2->xaxis->SetTickLabels($datax2, $datax2_color); // 項目設定
        // $graph2->xaxis->SetFont(FF_FONT1);     // フォントはボールドも指定できる。
        $graph2->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);     // フォントはボールドも指定できる。
        $graph2->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot2 = new BarPlot($datay2); 
        $bplot2->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot2->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot2->SetColor('navy');
        $bplot2->value->SetFormat('%d');     // 整数フォーマット
        $bplot2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/26 追加
        $bplot2->value->Show();              // 数値表示
        $targ2 = array();
        $alts2 = array();
        $targ2[0] = "JavaScript:win_open('" . $menu->out_action('予定明細次工程') . "?date=OLD')";
        $alts2[0] = '納期遅れの金額＝%3d';
        for ($i=0; $i<$rows; $i++) {
            $targ2[$i+1] = "JavaScript:win_open('" . $menu->out_action('予定明細次工程') . "?date={$datax2[$i+1]}')";
            $alts2[$i+1] = "'{$datax2[$i+1]}の納入予定 金額＝%3d";
        }
        $bplot2->SetCSIMTargets($targ2, $alts2); 
        $graph2->Add($bplot2);
        $graph2_name = "graph/order_schedule_next_{$_SESSION['User_ID']}.png";
        $graph2->Stroke($graph2_name);
        chmod($graph2_name, 0666);                   // fileを全てrwモードにする
    }
} elseif ($select == 'list') {
    ////////// 前日までの注残件数を取得
    $yesterday = date('Ymd', time() - 86400);
    $lower_limit_day = date('Ymd', time() - (86400*200));
    //$upper_limit_day = date('Ymd', time() + (86400*93));
    $upper_limit_day_t = date('Ym');
    $upper_limit_day = $upper_limit_day_t . '31';
    ///// 対象前月
    if (substr($upper_limit_day_t,4,2)!=01) {
        $b1_ym = $upper_limit_day_t - 1;
    } else {
        $b1_ym = $upper_limit_day_t - 100;
        $b1_ym = $b1_ym + 11;
    }
    ///// 対象翌月
    if (substr($upper_limit_day_t,4,2)!=12) {
        $p2_ym = $upper_limit_day_t + 1;
    } else {
        $p2_ym = $upper_limit_day_t + 100;
        $p2_ym = $p2_ym - 11;
    }
    ///// 対象翌々月
    if (substr($p2_ym,4,2)!=12) {
        $p3_ym = $p2_ym + 1;
    } else {
        $p3_ym = $p2_ym + 100;
        $p3_ym = $p3_ym - 11;
    }
    $dd_today_t = date('Ymd');                    // 本日年月日
    $dd_today   = substr($dd_today_t,6,2);        // 本日日付
    $str_dayb1  = $b1_ym . '01';                  // 翌月分開始日
    $end_dayb1  = $b1_ym . '31';                  // 翌月分終了日
    $yyyyb1     = substr($b1_ym,0,4);             // 翌月年
    $mmb1       = substr($b1_ym,4,2);             // 翌月月
    $str_day    = $upper_limit_day_t . '01';      // 当月分開始日
    $end_day    = $upper_limit_day;               // 当月分終了日
    $yyyy1      = substr($upper_limit_day_t,0,4); // 当月年
    $mm1        = substr($upper_limit_day_t,4,2); // 当月月
    $str_day2   = $p2_ym . '01';                  // 翌月分開始日
    $end_day2   = $p2_ym . '31';                  // 翌月分終了日
    $yyyy2      = substr($p2_ym,0,4);             // 翌月年
    $mm2        = substr($p2_ym,4,2);             // 翌月月
    $str_day3   = $p3_ym . '01';                  // 翌々月分開始日
    $end_day3   = $p3_ym . '31';                  // 翌々月分終了日
    $yyyy3      = substr($p3_ym,0,4);             // 翌々年
    $mm3        = substr($p3_ym,4,2);             // 翌々月
    //////////// 検査仕掛分(未検収件数)の合計を取得
    if ($dd_today <= 10) {          // 10日までは前月分の検査開始日までは前月分として取得
        $where_div = getDivWhereSQL($div);
        $query = "SELECT  sum(data.order_q * data.order_price)
                    FROM
                        order_data          AS data
                    LEFT OUTER JOIN
                        acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                    LEFT OUTER JOIN
                        order_plan          AS plan     USING (sei_no)
                    WHERE
                        ken_date <= 0       -- 未検収分
                        AND
                        data.sei_no > 0     -- 製造用であり
                        AND
                        (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                        AND
                        ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                        AND
                        uke_date < {$str_day}
                        AND
                        {$where_div}
                    LIMIT 1
        ";
        if (getUniResult($query, $res_mikenb1) <= 0) {
            $res_mikenb1 = 0;
        }
        $where_div = getDivWhereSQL($div);
        $query = "SELECT  sum(data.order_q * data.order_price)
                    FROM
                        order_data          AS data
                    LEFT OUTER JOIN
                        acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                    LEFT OUTER JOIN
                        order_plan          AS plan     USING (sei_no)
                    WHERE
                        ken_date <= 0       -- 未検収分
                        AND
                        data.sei_no > 0     -- 製造用であり
                        AND
                        (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                        AND
                        ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                        AND
                        uke_date >= {$str_day}
                        AND
                        {$where_div}
                    LIMIT 1
        ";
        if (getUniResult($query, $res_miken) <= 0) {
            $res_miken = 0;
        }
    } else {
        $res_mikenb1 = 0;
        $where_div = getDivWhereSQL($div);
        $query = "SELECT  sum(data.order_q * data.order_price)
                    FROM
                        order_data          AS data
                    LEFT OUTER JOIN
                        acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                    LEFT OUTER JOIN
                        order_plan          AS plan     USING (sei_no)
                    WHERE
                        ken_date <= 0       -- 未検収分
                        AND
                        data.sei_no > 0     -- 製造用であり
                        AND
                        (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                        AND
                        ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                        AND
                        {$where_div}
                    LIMIT 1
        ";
        if (getUniResult($query, $res_miken) <= 0) {
            $res_miken = 0;
        }
    }
    if ($div == 'C') {
        $all_title = 'カプラ全体 納入予定 当月合計金額　';
        $list_title = 'カプラ全体 納入予定金額集計　（単位：円）';
        $where_div = "data.parts_no like 'C%' AND proc.locate != '52   '";
        $total_title = 'カプラ全体 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'C%' AND proc.locate != '52   '";
        $total2_title = 'カプラ全体 次工程 納入予定 当月金額 (注文書未発行)　';
        $search = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_day, $end_day, $div);
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_dayb1, $end_dayb1, $div);
    }
    if ($div == 'SC') {
        $all_title = 'カプラ特注品 納入予定 当月合計金額　';
        $list_title = 'カプラ特注品 納入予定金額集計　（単位：円）';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no like '%SC%' AND proc.locate != '52   '";
        $total_title = 'カプラ特注品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no like '%SC%' AND proc.locate != '52   '";
        $total2_title = 'カプラ特注品 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'CS') {
        $all_title = 'カプラ標準品 納入予定 当月合計金額　';
        $list_title = 'カプラ標準品 納入予定金額集計　（単位：円）';
        $where_div = "data.parts_no like 'C%' AND data.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $total_title = 'カプラ標準品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'C%' AND plan.kouji_no not like '%SC%' AND proc.locate != '52   '";
        $total2_title = 'カプラ標準品 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'L') {
        $all_title = 'リニア全体 納入予定 当月合計金額　';
        $list_title = 'リニア全体 納入予定金額集計　（単位：円）';
        $where_div = "data.parts_no like 'L%' AND proc.locate != '52   '";
        $total_title = 'リニア全体 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'L%' AND proc.locate != '52   '";
        $total2_title = 'リニア全体 次工程 納入予定 当月金額 (注文書未発行)　';
        //$search = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_day, $end_day, $div);
        //$searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_dayb1, $end_dayb1, $div);
        $search = sprintf("where act_date>=%d and act_date<=%d and div='%s' and parts_no not like '%s'", $str_day, $end_day, $div, 'T%');
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s' and parts_no not like '%s'", $str_dayb1, $end_dayb1, $div, 'T%');
    }
    if ($div == 'T') {
        $all_title = '親機種が機工部品 納入予定 当月合計金額　';
        $list_title = '親機種が機工部品 納入予定金額集計　（単位：円）';
        $where_div = "data.parts_no like 'T%' AND proc.locate != '52   '";
        $total_title = '親機種が機工部品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'T%' AND proc.locate != '52   '";
        $total2_title = '親機種が機工部品 次工程 納入予定 当月金額 (注文書未発行)　';
        //$search = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_day, $end_day, $div);
        //$searchb1 = sprintf("where act_date>=%d and act_date<=%d and div='%s'", $str_dayb1, $end_dayb1, $div);
        $search = sprintf("where act_date>=%d and act_date<=%d and (div='%s' or (div<>'T' and div<>'C' and parts_no like '%s'))", $str_day, $end_day, $div, 'T%');
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d and (div='%s' or (div<>'T' and div<>'C' and parts_no like '%s'))", $str_dayb1, $end_dayb1, $div, 'T%');
    }
    if ($div == 'F') {
        $all_title = '親機種がＦＡ部品 納入予定 当月合計金額　';
        $list_title = '親機種がＦＡ部品 納入予定金額集計　（単位：円）';
        $where_div = "data.parts_no like 'F%' AND proc.locate != '52   '";
        $total_title = '親機種がＦＡ部品 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "proc.parts_no like 'F%' AND proc.locate != '52   '";
        $total2_title = '親機種がＦＡ部品 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'A') {
        $all_title = '栃木全体 納入予定 当月合計金額　';
        $list_title = '栃木全体 納入予定金額集計　（単位：円）';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate != '52   '";
        $total_title = '栃木全体 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate != '52   '";
        $total2_title = '栃木全体 次工程 納入予定 当月金額 (注文書未発行)　';
        $search = sprintf("where act_date>=%d and act_date<=%d", $str_day, $end_day);
        $searchb1 = sprintf("where act_date>=%d and act_date<=%d", $str_dayb1, $end_dayb1);
    }
    if ($div == 'N') {
        $all_title = '日東工器(カプラ) 納入予定 当月合計金額　';
        $list_title = '日東工器(カプラ) 納入予定金額集計　（単位：円）';
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') AND proc.locate = '52   '";
        $total_title = '日東工器(カプラ) 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "(proc.parts_no like 'C%' or proc.parts_no like 'L%' or proc.parts_no like 'T%' or proc.parts_no like 'F%') AND proc.locate = '52   '";
        $total2_title = '日東工器(カプラ) 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    if ($div == 'NKB') {
        $all_title = 'ＮＫＢ 納入予定 当月合計金額　';
        $list_title = 'ＮＫＢ 納入予定金額集計　（単位：円）';
        $where_div = "plan.locate = '14'";
        $total_title = 'ＮＫＢ 納入予定 当月金額 (注文書発行済み)　';
        $where_div2 = "plan.locate = '14'";
        $total2_title = 'ＮＫＢ 次工程 納入予定 当月金額 (注文書未発行)　';
    }
    //////////// 買掛金額の取得（前月迄）
    if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) {
        $query = sprintf('select count(*), sum(Uround(order_price * siharai,0)) from act_payable %s', $searchb1);
        $res_max = array();
        if ( getResult2($query, $res_max) <= 0) {         // $maxrows の取得
            $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
            exit();
        } else {
            $maxrowsb1 = $res_max[0][0];                  // 合計レコード数の取得
            $sum_kinb1 = $res_max[0][1];                  // 合計買掛金額の取得
        }
    } else {
        $sum_kinb1 = 0;
    }
    $month_totalb1 = $res_mikenb1;
    $month_allb1 = $month_totalb1;
    $month_sumb1 = $month_allb1 + $sum_kinb1;
    $month_totalb1  = number_format($month_totalb1, 0);
    $sum_kinb1  = number_format($sum_kinb1, 0);
    $month_allb1  = number_format($month_allb1, 0);
    $month_sumb1  = number_format($month_sumb1, 0);
    //////////// 買掛金額の取得（当月前日迄）
    if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) {
        $query = sprintf('select count(*), sum(Uround(order_price * siharai,0)) from act_payable %s', $search);
        $res_max = array();
        if ( getResult2($query, $res_max) <= 0) {         // $maxrows の取得
            $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
            exit();
        } else {
            $maxrows = $res_max[0][0];                  // 合計レコード数の取得
            $sum_kin = $res_max[0][1];                  // 合計買掛金額の取得
        }
    } else {
        $sum_kin = 0;
    }
    //////////// 納期遅れ分の合計を取得
    $query = "SELECT sum(data.order_q * data.order_price)
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    {$where_div}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan) <= 0) {
        $res_zan = 0;
    }
    //////////// 棒グラフの予定日 日数  2007/09/22 ADD
    $page = 22;
    $maxrows = 66;
    //////////// ページオフセット設定
    $offset = $session->get_local('offset');
    if ($offset == '') $offset = 0;         // 初期化
    if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
        $offset += $page;
        if ($offset >= $maxrows) {
            $offset -= $page;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "次頁はありません。";
            } else {
                $_SESSION['s_sysmsg'] .= "次頁はありません。";
            }
        }
    } elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
        $offset -= $page;
        if ($offset < 0) {
            $offset = 0;
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] .= "前頁はありません。";
            } else {
                $_SESSION['s_sysmsg'] .= "前頁はありません。";
            }
        }
    } elseif ( isset($_REQUEST['page_keep']) ) {               // 現在のページを維持する
        $offset = $offset;
    } elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する
        $offset = $offset;
    } else {
        $offset = 0;                            // 初回の場合は０で初期化
    }
    $session->add_local('offset', $offset);
    
    /////////// 本日以降のサマリーを取得(当月）
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "注残データがありません！";
        $view = 'NG';
        $month_all = 0;
        $month_total  = 0;
    } else {
        $view = 'OK';
        $month_total = $res_miken + $res_zan;
        for ($i=0; $i<$rows; $i++) {
            $month_total += $res[$i]['kin'];
        }
        $month_all = $month_total;
        $month_total  = number_format($month_total, 0);
    }
    /////////// 本日以降のサマリーを取得(翌月）
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_day2}
                    AND
                    proc.delivery <= {$end_day2}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "注残データがありません！";
        $view = 'NG';
        $month2_all = 0;
        $month2_total  = 0;
    } else {
        $view = 'OK';
        $month2_total = 0;
        for ($i=0; $i<$rows; $i++) {
            $month2_total += $res[$i]['kin'];
        }
        $month2_all = $month2_total;
        $month2_total  = number_format($month2_total, 0);
    }
    /////////// 本日以降のサマリーを取得(翌々月）
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(data.order_q * data.order_price) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_day3}
                    AND
                    proc.delivery <= {$end_day3}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    {$where_div}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "注残データがありません！";
        $view = 'NG';
        $month3_all = 0;
        $month3_total  = 0;
    } else {
        $view = 'OK';
        $month3_total = 0;
        for ($i=0; $i<$rows; $i++) {
            $month3_total += $res[$i]['kin'];
        }
        $month3_all = $month3_total;
        $month3_total = number_format($month3_total, 0);
    }
    //////////// グラフ２ 次工程品(注文書未発行)の納入予定グラフの作成 //////////////
    //////////// 納期遅れ分の合計を取得
    $query = "SELECT sum(plan.order_q * proc.order_price)
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$yesterday}
                    AND
                    proc.delivery >= {$lower_limit_day}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    {$where_div2}
                OFFSET 0
                LIMIT 1
    ";
    if (getUniResult($query, $res_zan2) <= 0) {
        $res_zan2 = 0;
    }
    /////////// 本日以降のサマリーを取得(当月)
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$yesterday}
                    AND
                    proc.delivery <= {$upper_limit_day}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- 注文書発行済みに+1 2007/09/22 変更により上記グラフと同じ
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "次工程の注残データがありません！";
        $view_graph2 = 'NG';
        $month_sum = $month_all + $sum_kin;
        $sum_kin  = number_format($sum_kin, 0);
        $month_all  = number_format($month_all, 0);
        $month_sum  = number_format($month_sum, 0);
        $month_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $month_total2 = $res_zan2;
        for ($i=0; $i<$rows; $i++) {
            $month_total2 += $res2[$i]['kin'];
        }
        $month_all = $month_all + $month_total2;
        $month_sum = $month_all + $sum_kin;
        $month_total2  = number_format($month_total2, 0);
        $sum_kin  = number_format($sum_kin, 0);
        $month_all  = number_format($month_all, 0);
        $month_sum  = number_format($month_sum, 0);
    }
    /////////// 本日以降のサマリーを取得（翌月）
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_day2}
                    AND
                    proc.delivery <= {$end_day2}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- 注文書発行済みに+1 2007/09/22 変更により上記グラフと同じ
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "次工程の注残データがありません！";
        $view_graph2 = 'NG';
        $month2_all  = number_format($month2_all, 0);
        $month2_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $month2_total2 = 0;
        for ($i=0; $i<$rows; $i++) {
            $month2_total2 += $res2[$i]['kin'];
        }
        $month2_all = $month2_all + $month2_total2;
        $month2_total2  = number_format($month2_total2, 0);
        $month2_all  = number_format($month2_all, 0);
    }
    /////////// 本日以降のサマリーを取得（翌月）
    $query = "SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(plan.order_q * proc.order_price) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_day3}
                    AND
                    proc.delivery <= {$end_day3}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    {$where_div2}
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
                OFFSET {$offset}
                LIMIT {$page}    -- 注文書発行済みに+1 2007/09/22 変更により上記グラフと同じ
    ";
    $res2 = array();
    if (($rows = getResult($query, $res2)) < 1) {
        // $_SESSION['s_sysmsg'] .= "次工程の注残データがありません！";
        $view_graph2 = 'NG';
        $month3_all  = number_format($month3_all, 0);
        $month3_total2  = 0;
    } else {
        $view_graph2 = 'OK';
        $month3_total2 = 0;
        for ($i=0; $i<$rows; $i++) {
            $month3_total2 += $res2[$i]['kin'];
        }
        $month3_all = $month3_all + $month3_total2;
        $month3_total2  = number_format($month3_total2, 0);
        $month3_all  = number_format($month3_all, 0);
    }
}

/////////// 自動更新と手動更新の条件切換え
if ($select == 'graph') {
    $auto_reload = 'off';
} elseif ($select == 'list') {
    $auto_reload = 'off';
} elseif ($order_seq != '') {
    $auto_reload = 'on';
} else {
    $auto_reload = 'off';
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
<title><?php echo $menu->out_title() ?></title>
<?php // if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left:    20px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_gray {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
    color: gray;
}
.winbox_mark {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#eaeaee;
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
<script type='text/javascript' language='JavaScript'>
<!--
function init() {
     setInterval('document.reload_form.submit()', 30000);   // 30秒
     //  onLoad='init()' ←これを <body>タグへ入れればOK
}
function win_open(url) {
    var w = 820;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open2(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open3(url) {
    var w = 1100;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win3', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + " の\n\n緊急部品 検査依頼をします。\n\n宜しいですか？")) {
        parent.Header.document.form_parts.parts_no.value = "";  // 部品番号の検索条件欄を初期化 2007/05/29 追加
        parent.Header.document.form_parts.parts_no.focus();     // 続けて入力できるようにフォーカスする
        // 実行します。
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.retUrl.value = (document.inspection_form.retUrl.value + '#' + order_seq);
        document.inspection_form.submit();
    } else {
        alert('取消しました。');
    }
}
function inspection_time(parts_no, parts_name, str_timestamp, end_timestamp, uid, name, hold_time) {
    if (hold_time == "-") {
        alert('部品番号　：　' + parts_no + '\n\n部品名称　：　' + parts_name + '\n\n検査開始日時　：　' + str_timestamp + '\n\n検査終了日時　：　' + end_timestamp + '\n\n社員番号　：　' + uid + '\n\n検査員名　：　' + name);
    } else {
        alert('部品番号　：　' + parts_no + '\n\n部品名称　：　' + parts_name + '\n\n検査開始日時　：　' + str_timestamp + '\n\n検査終了日時　：　' + end_timestamp + '\n\n社員番号　：　' + uid + '\n\n検査員名　：　' + name + '\n\n中断日時　：　' + hold_time);
    }
}
function miken_submit() {
    document.miken_submit_form.submit();
}
function vendor_code_view(vendor, vendor_name) {
    alert('発注先コード：' + vendor + '\n\n発注先名：' + vendor_name + '\n\n');
}
// -->
</script>
<form name='inspection_form' method='get' action='inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?php echo $menu->out_self() ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
<form name='reload_form' action='order_schedule_List.php<?php if ($order_seq != '') echo "#{$order_seq}"; ?>' method='get' target='_self'>
    <input type='hidden' name='order_seq' value='<?php echo $order_seq?>'>
</form>
<form name='miken_submit_form' action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
    <input type='hidden' name='miken' value='検査仕掛リスト'>
    <input type='hidden' name='div' value='<?php echo $div?>'>
</form>
</head>
<body>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>データがありません！</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'miken') { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='98' nowrap colspan='2' style='font-size:14;'>検査開始終了</th>
            <th class='winbox' width='70' nowrap>受付日</th>
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>受付No</th>
            <th class='winbox' width='90' nowrap>部品番号</th>
            <th class='winbox' width='150' nowrap>部品名</th>
            <th class='winbox' width='90' nowrap style='font-size:14;'>材質/親機種</th>
            <th class='winbox' width='70' nowrap>受付数</th>
            <th class='winbox' width='35' nowrap style='font-size:9.5pt;'>工程</th>
            <th class='winbox' width='130' nowrap>納入先</th>
            <?php if ($display == 'wide') { ?>
            <th class='winbox' width='80' nowrap>工事番号</th>
            <th class='winbox' width='80' nowrap>発行連番</th>
            <th class='winbox' width='70' nowrap>製造番号</th>
            <th class='winbox' width='130' nowrap>次工程</th>
            <?php } ?>
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                if ($rec['end_timestamp']) $winbox = 'winbox_gray'; else $winbox = 'winbox';
                if ($rec['order_seq'] == $order_seq) $winbox = 'winbox_mark'; else $winbox = 'winbox';
                if ($rec['str_timestamp']) { // ダブルクリックで検査開始時間と終了時間を表示 2005/02/21 追加
                    if ($rec['end_timestamp']) {
                        echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"{$rec['end_timestamp']}\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                    } else {
                        if ($rec['hold_flg'] == '中断中') {
                            echo "<tr style='color:gray;' onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"未終了\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"{$rec['hold_time']}\")'>\n";
                        } else {
                            echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"未終了\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                        }
                    }
                } else {    // ダブルクリックで緊急検査依頼が出来る
                    echo "<tr onDblClick='inspection_recourse(\"{$rec['order_seq']}\",\"{$rec['parts_no']}\",\"{$rec['parts_name']}\")'>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='order_schedule_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$i}</a></td>\n";
                if ($rec['str_timestamp']) {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>検済</td>\n";
                    } else {
                        if ($rec['hold_flg'] == '中断中') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>中断</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='order_schedule_List.php?end={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:yellow;'>検中</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='order_schedule_List.php?str={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>開始</a></td>\n";
                }
                if ( ($rec['str_timestamp']) || ($rec['end_timestamp']) ) {
                    if ( ($rec['str_timestamp']) && ($rec['end_timestamp']) ) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap><a href='order_schedule_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:gray;'>訂正</a></td>\n";
                    } else {
                        if ($rec['hold_flg'] == '中断中') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>取消</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap><a href='order_schedule_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:red;'>取消</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray ;' align='center' width='44' nowrap>取消</td>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'>{$rec['uke_no']}<br><span style='color:gray';>{$rec['delivery']}</span></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91' onClick='win_open2(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                echo "    <a class='link' href='javascript:void(0)' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                // echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91'><a class='link' href='javascript:win_open2(\"{$menu->out_action('在庫経歴')}?parts_no=" . urlencode($rec['parts_no']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='150'>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 27) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'>" . mb_convert_kana($rec['parts_zai'], 'k') . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'>{$rec['pro_mark']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['発行連番']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70' >{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130'>{$rec['次工程']}</td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } elseif ($select == 'insEnd') { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                if ($rec['end_timestamp']) $winbox = 'winbox_gray'; else $winbox = 'winbox';
                if ($rec['order_seq'] == $order_seq) $winbox = 'winbox_mark'; else $winbox = 'winbox';
                if ($rec['str_timestamp']) { // ダブルクリックで検査開始時間と終了時間を表示 2005/02/21 追加
                    if ($rec['end_timestamp']) {
                        echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"{$rec['end_timestamp']}\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                    } else {
                        if ($rec['hold_flg'] == '中断中') {
                            echo "<tr style='color:gray;' onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"未終了\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"{$rec['hold_time']}\")'>\n";
                        } else {
                            echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"未終了\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                        }
                    }
                } else {
                    echo "<tr>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='order_schedule_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$i}</a></td>\n";
                if ($rec['str_timestamp'] && $rec['end_timestamp']) {
                    echo "<td class='{$winbox}' style='font-size:12; font-weight:bold; color:gray;' align='center' width='93' colspan'2'>", substr($rec['str_timestamp'], 5), '<br>', substr($rec['end_timestamp'], 5), "</td>\n";
                } elseif ($rec['str_timestamp']) {
                    echo "<td class='{$winbox}' style='font-size:12; font-weight:bold; color:gray;' align='center' width='93' colspan'2'>", substr($rec['str_timestamp'], 5), "<br>未入力</td>\n";
                } else {
                    echo "<td class='{$winbox}' style='font-size:12; font-weight:bold; color:gray;' align='center' width='93' colspan'2'>未入力<br>{$rec['ken_date']}</td>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'>{$rec['uke_no']}<br><span style='color:gray';>{$rec['delivery']}</span></td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91' onClick='win_open2(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                if ($rec['ken_date'] == '0000/00/00') $dataKen = "<br><span style='color:red;'>AS未検収</span>"; else $dataKen = '';
                echo "    <a class='link' href='javascript:void(0)' target='_self' style='text-decoration:none;'>{$rec['parts_no']}{$dataKen}</a></td>\n";
                // echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91'><a class='link' href='javascript:win_open2(\"{$menu->out_action('在庫経歴')}?parts_no=" . urlencode($rec['parts_no']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='150'>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 27) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'>" . mb_convert_kana($rec['parts_zai'], 'k') . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'>{$rec['pro_mark']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80' >{$rec['発行連番']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70' >{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130'>{$rec['次工程']}</td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } elseif ($select == 'graph') { ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self();?>'>
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
        <B><font size=4>
        <?php echo $all_title ?>
        <font color=red><?php echo $month_all ?></font>
        　円
        </font></B>
        <br><br>
        <!-- グラフ1 納入予定(注文書発行済み) -->
        <B>
        <?php echo $total_title . $month_total . '　円' ?>
        </B>
        <table width='100%' border='0'>
            <tr>
            <td align='center'>
                <?php echo $graph->GetHTMLImageMap('order_map') ?>
                <?php echo "<img src='", $graph_name, '?', $uniq, "' ismap usemap='#order_map' alt='納入予定 金額 日計グラフ (注文書発行済み)' border='0'>\n"; ?>
            </td>
            </tr>
        </table>
        <!-- グラフ2 次工程品の予定(注文書未発行) -->
        <br>
        <B>
        <?php echo $total2_title . $month_total2 . '　円' ?>
        </B>
        <table width='100%' border='0'>
            <tr>
            <td align='center'>
                <?php
                if ($view_graph2 == 'OK') {
                    echo $graph2->GetHTMLImageMap('order_map2');
                    echo "<img src='", $graph2_name, '?', $uniq, "' ismap usemap='#order_map2' alt='次工程 納入予定 金額 日計グラフ (注文書未発行)' border='0'>\n";
                } else {
                    echo "<b style='color: teal;'>次工程のデータがありません！</b>\n";
                }
                ?>
            </td>
            </tr>
        </table>
        <?php } elseif ($select == 'list') { ?>
        <BR><BR>
        <B><font size=4>
        <?php echo $list_title ?>
        </font></B>
        <BR><BR>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        年　月
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        買掛金額(前日迄)
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        注文書発行済み金額
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        注文書未発行金額
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        納入予定金額計
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        買掛予定金額計
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        納入予定金額計
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <?php if ($dd_today <= 10) { ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyyb1 . '年' . $mmb1 . '月' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kinb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_totalb1 ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        　
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_allb1 ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sumb1 ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_allb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy1 . '年' . $mm1 . '月' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kin ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sum ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <?php } else { ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyyb1 . '年' . $mmb1 . '月' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kinb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        　
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        　
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        　
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sumb1 ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_allb1 ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy1 . '年' . $mm1 . '月' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $sum_kin ?>
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_sum ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy2 . '年' . $mm2 . '月' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        　
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_all ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month2_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffff' nowrap>
                    <div class='pt12b'>
                        <?php echo $yyyy3 . '年' . $mm3 . '月' ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#d6d3ce' nowrap>
                    <div class='pt11b'>
                        　
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_total ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_total2 ?>
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ffffff' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_all ?>
                    </div>
                    </td>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_all ?>
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='right' bgcolor='#ccffcc' nowrap>
                    <div class='pt11b'>
                        <?php echo $month3_all ?>
                    </div>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                    　
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (A)
                    </div>
                    </td>
                    <?php } ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (B)
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (C)
                    </div>
                    </td>
                    <?php if (($div == 'C') || ($div == 'L') || ($div == 'T') || ($div == 'A')) { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ffffff' nowrap>
                    <div class='pt12b'>
                        (D)=(B)+(C)
                    </div>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        (A)+(D)
                    </div>
                    </td>
                    <?php } else { ?>
                    <td nowrap class='winbox' align='center' bgcolor='#ccffcc' nowrap>
                    <div class='pt12b'>
                        (B)+(C)
                    </div>
                    </td>
                    <?php } ?>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <BR><BR>
        <B>
        ※ 当月の注文書発行済み金額には、検査仕掛と納期遅れを含む
        </B>
        <?php if ($dd_today <= 10) { ?>
        <BR><BR>
        <B>
        　　　　　　　※ 毎月１０日までは前月受付分を前月分の注文書発行済金額として計算する
        </B>
        <?php } ?>
        <?php } ?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // リロード用１０秒
// -->
</script>
<?php echo $menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
