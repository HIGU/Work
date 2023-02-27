<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 売上 標準品専用→売上の原価率分析 照会 条件選択フォーム     //
// Copyright (C) 2005-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/02 Created   sales_standard_pre_form.php                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1, 17);                    // site_index=01(売上メニュー) site_id=14(標準品売上)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売上の原価率分析 総材料・仕切単価率 条件設定');
//////////// 表題の設定
$menu->set_caption('下記に必要な条件を設定又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('売上明細', SALES . 'sales_material_pre/sales_standard_pre_view.php');
$menu->set_action('グラフ',   SALES . 'sales_material_pre/sales_standard_pre_graph.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/////////////// 受渡し変数の初期化
/************ パスワード **************/
if ( isset($_REQUEST['uri_passwd']) ) {
    $uri_passwd = $_REQUEST['uri_passwd'];
    $_SESSION['s_uri_passwd'] = $uri_passwd;    // 売上の共通パスワード使用
} else {
    if ( isset($_SESSION['s_uri_passwd']) ) {
        $uri_passwd = $_SESSION['s_uri_passwd'];
    } else {
        $uri_passwd = '';
    }
}
/************ 製品グループ **************/
if ( isset($_REQUEST['div']) ) {
    $div = $_REQUEST['div'];
    $_SESSION['standard_div'] = $div;
} else {
    if ( isset($_SESSION['standard_div']) ) {
        $div = $_SESSION['standard_div'];
    } else {
        $div = 'A';     // 初期値(全グループ)
    }
}
/*********** 製品グループの条件文 設定 **************/
if ($div == 'A') $where_div = "dept!=' '";        // グループ全体
if ($div == 'C') $where_div = "dept='{$div}'";    // Ｃ全体
if ($div == 'CH') $where_div = "dept='C'";   // Ｃ標準
if ($div == 'CS') $where_div = "dept='C'";        // Ｃ特注を追加
if ($div == 'L') $where_div = "dept='{$div}'";    // Ｌ全体
if ($div == 'LL') $where_div = "dept='L' and parts_no NOT LIKE 'LC%' and parts_no NOT LIKE 'LR%'"; // Ｌのみ
if ($div == 'LB') $where_div = "dept='L' and (parts_no LIKE 'LC%' OR parts_no LIKE 'LR%')";        // バイモルのみ
$_SESSION['standard_div'] = $div;
$_SESSION['standard_where_div'] = $where_div;

/************ 日付 **************/
if ( isset($_REQUEST['d_start']) ) {
    $d_start = $_REQUEST['d_start'];
    ///// day のチェック
    if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
    ///// 最終日をチェックしてセットする
    if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
        $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $_SESSION['s_sysmsg'] = '日付の指定が不正です！';
        }
    }
    $_SESSION['standard_d_start'] = $d_start;
} else {
    if ( isset($_SESSION['standard_d_start']) ) {
        $d_start = $_SESSION['standard_d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_REQUEST['d_end']) ) {
    $d_end = $_REQUEST['d_end'];
    ///// day のチェック
    if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
    ///// 最終日をチェックしてセットする
    if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
        $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $_SESSION['s_sysmsg'] = '日付の指定が不正です！';
        }
    }
    $_SESSION['standard_d_end'] = $d_end;
} else {
    if ( isset($_SESSION['standard_d_end']) ) {
        $d_end = $_SESSION['standard_d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
/************ 製品番号 **************/
if ( isset($_REQUEST['assy_no']) ) {
    $assy_no = $_REQUEST['assy_no'];
    $_SESSION['standard_assy_no'] = $assy_no;
    if ($assy_no != '') {
        $where_assy_no = "and parts_no='{$assy_no}'";
    } else {
        $where_assy_no = '';
    }
} else {
    if ( isset($_SESSION['standard_assy_no']) ) {
        $assy_no = $_SESSION['standard_assy_no'];
        if ($assy_no != '') {
            $where_assy_no = "and parts_no='{$assy_no}'";
        } else {
            $where_assy_no = '';
        }
    } else {
        $assy_no = '';      // 初期化
        $where_assy_no = '';
    }
}
$_SESSION['standard_where_assy_no'] = $where_assy_no;
/************ 売上区分 **************/
if ( isset($_REQUEST['kubun']) ) {
    $kubun = $_REQUEST['kubun'];
    $_SESSION['standard_kubun'] = $kubun;
} else {
    if ( isset($_SESSION['standard_kubun']) ) {
        $kubun = $_SESSION['standard_kubun'];
    } else {
        $kubun = '1';
    }
}
/************ 条件１ **************/
if ( isset($_REQUEST['lower_uri_ritu']) ) {
    $lower_uri_ritu = $_REQUEST['lower_uri_ritu'];
    $_SESSION['standard_lower_uri_ritu'] = $lower_uri_ritu;
} else {
    if ( isset($_SESSION['standard_lower_uri_ritu']) ) {
        $lower_uri_ritu = $_SESSION['standard_lower_uri_ritu'];
    } else {
        $lower_uri_ritu = '116.0';   // 初期化
    }
}
if ( isset($_REQUEST['upper_uri_ritu']) ) {
    $upper_uri_ritu = $_REQUEST['upper_uri_ritu'];
    $_SESSION['standard_upper_uri_ritu'] = $upper_uri_ritu;
} else {
    if ( isset($_SESSION['standard_upper_uri_ritu']) ) {
        $upper_uri_ritu = $_SESSION['standard_upper_uri_ritu'];
    } else {
        $upper_uri_ritu = '9999';   // 初期化
    }
}
/************ 条件２ **************/
if ( isset($_REQUEST['lower_mate_ritu']) ) {
    $lower_mate_ritu = $_REQUEST['lower_mate_ritu'];
    $_SESSION['standard_lower_mate_ritu'] = $lower_mate_ritu;
} else {
    if ( isset($_SESSION['standard_lower_mate_ritu']) ) {
        $lower_mate_ritu = $_SESSION['standard_lower_mate_ritu'];
    } else {
        $lower_mate_ritu = '108.0';     // 初期化
    }
}
if ( isset($_REQUEST['upper_mate_ritu']) ) {
    $upper_mate_ritu = $_REQUEST['upper_mate_ritu'];
    $_SESSION['standard_upper_mate_ritu'] = $upper_mate_ritu;
} else {
    if ( isset($_SESSION['standard_upper_mate_ritu']) ) {
        $upper_mate_ritu = $_SESSION['standard_upper_mate_ritu'];
    } else {
        $upper_mate_ritu = '116.0';     // 初期化
    }
}
/************ 条件３ **************/
if ( isset($_REQUEST['lower_equal_ritu']) ) {
    $lower_equal_ritu = $_REQUEST['lower_equal_ritu'];
    $_SESSION['standard_lower_equal_ritu'] = $lower_equal_ritu;
} else {
    if ( isset($_SESSION['standard_lower_equal_ritu']) ) {
        $lower_equal_ritu = $_SESSION['standard_lower_equal_ritu'];
    } else {
        $lower_equal_ritu = '0.0';     // 初期化
    }
}
if ( isset($_REQUEST['upper_equal_ritu']) ) {
    $upper_equal_ritu = $_REQUEST['upper_equal_ritu'];
    $_SESSION['standard_upper_equal_ritu'] = $upper_equal_ritu;
} else {
    if ( isset($_SESSION['standard_upper_equal_ritu']) ) {
        $upper_equal_ritu = $_SESSION['standard_upper_equal_ritu'];
    } else {
        $upper_equal_ritu = '108.0';    // 初期化
    }
}
/************ 明細表示行数 **************/
// $_SESSION['s_rec_No'] = 0;  // 表示用レコード�發�0にする。
if ( isset($_REQUEST['sales_page']) ) {
    $sales_page = $_REQUEST['sales_page'];
    $_SESSION['standard_sales_page'] = $sales_page;
} else {
    if ( isset($_SESSION['standard_sales_page']) ) {      // １ページ表示行数設定
        $sales_page = $_SESSION['standard_sales_page'];   // 常に Default 25 になるようにコメント解除
    } else {
        $sales_page = 25;             // Default 25
    }
}

/*************** 合計表照会のリクエストをdiv→sum_execで判断 ****************/
while (isset($_REQUEST['sum_exec'])) {
    ////////////// パスワードチェック
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_self());  // 自分にメッセージ
        exit();
    }
    ////////////// 条件別合計表のデータ生成
    /******************* 条件１ ***********************/
    $sql = "select
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
    ";
    $_SESSION['costom_sql'] = $sql;         // グラフのためセッションに基本部分を保存(当面は売上金額を使用)
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
    $_SESSION['standard_where'] = $where;    // 明細表のためセッションに保存
    $condition = "and
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) >= {$lower_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) >= {$lower_uri_ritu})
                and
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) <= {$upper_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) <= {$upper_uri_ritu})
    ";
    // and
    // (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) > 0
    $_SESSION['standard_where1'] = ($where . $condition); // 明細表のためセッションに保存
    $out = "and a.parts_no NOT LIKE '999999999' AND (a.plan - a.cut_plan) != 0
                      AND assy_site = '01111'
                      AND ((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 != 0)
                      OR (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) !=0)
    ";
    $_SESSION['standard_condition1'] = $condition . $out;        // グラフのための条件保存
    $query = ($sql . $where . $condition . $out);              // 合体
    $res_sum = array();
    if (($rows_sum = getResult($query, $res_sum)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
    }
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
    if ($t_ken <= 0) {
        // $_SESSION['s_sysmsg'] .= '指定された条件では条件１に合致するデータはありません。';
        $sum1_ken = 0;
        $sum1_suu = 0;
        $sum1_uri = 0;
        $sum1_sou = 0;
        $sum1_rit = 0;
        $sum1_sik = 0;
    } else {
        $sum1_ken = $t_ken;
        $sum1_suu = $t_kazu;
        $sum1_uri = $t_kingaku;
        $sum1_sou = $t_zai;
        if ($sum1_uri == 0) {       // Division by zero 対応 2007/11/07
            $sum1_rit = 0;
        } else {
            $sum1_rit = Uround($sum1_sou / $sum1_uri * 100, 1);
        }
        if ($sum1_sou == 0) {       // Division by zero 対応 2007/11/07
            $sum1_sik = 0;
        } else {
            $sum1_sik = Uround($sum1_uri / $sum1_sou * 100, 1);
        }
    }
    ////////// 合計の計算
    $sum_ken = $sum1_ken;
    $sum_suu = $sum1_suu;
    $sum_uri = $sum1_uri;
    $sum_sou = $sum1_sou;
    $uri1 = $sum1_uri;  // 書式設定する前に数値で保存
    $sum1_ken = number_format($sum1_ken);
    $sum1_suu = number_format($sum1_suu);
    $sum1_uri = number_format($sum1_uri);
    $sum1_sou = number_format(Uround($sum1_sou, 0));
    if ($sum1_rit > 100.0) {
        $sum1_rit = "<font style='color:red;'>" . number_format($sum1_rit, 1) . '％' . '</font>';
    } else {
        $sum1_rit = number_format($sum1_rit, 1) . '％';
    }
    if ($sum1_sik < 100.0 && $sum1_sik > 0.0) {
        $sum1_sik = "<font style='color:red;'>" . number_format($sum1_sik, 1) . '％' . '</font>';
    } else {
        $sum1_sik = number_format($sum1_sik, 1) . '％';
    }
    /******************* 条件２ ***********************/
    $condition = "and
                    (
                        (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) < {$lower_uri_ritu})
                or
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) > {$upper_uri_ritu})
                    )
                and
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) >= {$lower_mate_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) >= {$lower_mate_ritu})
                and
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) <= {$upper_mate_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) <= {$upper_mate_ritu})
    ";
    $_SESSION['standard_where2'] = ($where . $condition); // 明細表のためセッションに保存
    $_SESSION['standard_condition2'] = $condition . $out;        // グラフのための条件保存
    $query = ($sql . $where . $condition . $out);              // 合体
    $res_sum = array();
    if (($rows_sum = getResult($query, $res_sum)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
    }
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
    if ($t_ken <= 0) {
        // $_SESSION['s_sysmsg'] .= '指定された条件では条件２に合致するデータはありません。';
        $sum2_ken = 0;
        $sum2_suu = 0;
        $sum2_uri = 0;
        $sum2_sou = 0;
        $sum2_rit = 0;
        $sum2_sik = 0;
    } else {
        $sum2_ken = $t_ken;
        $sum2_suu = $t_kazu;
        $sum2_uri = $t_kingaku;
        $sum2_sou = $t_zai;
        if ($sum2_uri == 0) {       // Division by zero 対応 2007/11/07
            $sum2_rit = 0;
        } else {
            $sum2_rit = Uround($sum2_sou / $sum2_uri * 100, 1);
        }
        if ($sum2_sou == 0) {       // Division by zero 対応 2007/11/07
            $sum2_sik = 0;
        } else {
            $sum2_sik = Uround($sum2_uri / $sum2_sou * 100, 1);
        }
    }
    ////////// 合計の計算
    $sum_ken += $sum2_ken;
    $sum_suu += $sum2_suu;
    $sum_uri += $sum2_uri;
    $sum_sou += $sum2_sou;
    $uri2 = $sum2_uri;  // 書式設定する前に数値で保存
    $sum2_ken = number_format($sum2_ken);
    $sum2_suu = number_format($sum2_suu);
    $sum2_uri = number_format($sum2_uri);
    $sum2_sou = number_format(Uround($sum2_sou, 0));
    if ($sum2_rit > 100.0) {
        $sum2_rit = "<font style='color:red;'>" . number_format($sum2_rit, 1) . '％' . '</font>';
    } else {
        $sum2_rit = number_format($sum2_rit, 1) . '％';
    }
    if ($sum2_sik < 100.0 && $sum2_sik > 0.0) {
        $sum2_sik = "<font style='color:red;'>" . number_format($sum2_sik, 1) . '％' . '</font>';
    } else {
        $sum2_sik = number_format($sum2_sik, 1) . '％';
    }
    /******************* 条件３ ***********************/
    $condition = "and
                    (
                        (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) < {$lower_uri_ritu})
                or
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) > {$upper_uri_ritu})
                    )
                and
                    (
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_mate_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) < {$lower_mate_ritu})
                or
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_mate_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) > {$upper_mate_ritu})
                    )
                and
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) >= {$lower_equal_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) >= {$lower_equal_ritu})
                and
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) <= {$upper_equal_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) <= {$upper_equal_ritu})
    ";
    $_SESSION['standard_where3'] = ($where . $condition); // 明細表のためセッションに保存
    $_SESSION['standard_condition3'] = $condition . $out;        // グラフのための条件保存
    $query = ($sql . $where . $condition . $out);              // 合体
    $res_sum = array();
    if (($rows_sum = getResult($query, $res_sum)) < 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
    }
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
    if ($t_ken <= 0) {
        // $_SESSION['s_sysmsg'] .= '指定された条件では条件３に合致するデータはありません。';
        $sum3_ken = 0;
        $sum3_suu = 0;
        $sum3_uri = 0;
        $sum3_sou = 0;
        $sum3_rit = 0;
        $sum3_sik = 0;
    } else {
        $sum3_ken = $t_ken;
        $sum3_suu = $t_kazu;
        $sum3_uri = $t_kingaku;
        $sum3_sou = $t_zai;
        if ($sum3_uri == 0) {       // Division by zero 対応 2007/11/07
            $sum3_rit = 0;
        } else {
            $sum3_rit = Uround($sum3_sou / $sum3_uri * 100, 1);
        }
        if ($sum3_sou == 0) {       // Division by zero 対応 2007/11/07
            $sum3_sik = 0;
        } else {
            $sum3_sik = Uround($sum3_uri / $sum3_sou * 100, 1);
        }
    }
    ////////// 合計の計算
    $sum_ken += $sum3_ken;
    $sum_suu += $sum3_suu;
    $sum_uri += $sum3_uri;
    $sum_sou += $sum3_sou;
    $uri3 = $sum3_uri;  // 書式設定する前に数値で保存
    $sum3_ken = number_format($sum3_ken);
    $sum3_suu = number_format($sum3_suu);
    $sum3_uri = number_format($sum3_uri);
    $sum3_sou = number_format(Uround($sum3_sou, 0));
    if ($sum3_rit > 100.0) {
        $sum3_rit = "<font style='color:red;'>" . number_format($sum3_rit, 1) . '％' . '</font>';
    } else {
        $sum3_rit = number_format($sum3_rit, 1) . '％';
    }
    if ($sum3_sik < 100.0 && $sum3_sik > 0.0) {
        $sum3_sik = "<font style='color:red;'>" . number_format($sum3_sik, 1) . '％' . '</font>';
    } else {
        $sum3_sik = number_format($sum3_sik, 1) . '％';
    }
    /******************* 条件４上記以外の残り全て ***********************/
    $condition = "and
                    (
                        (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) < {$lower_uri_ritu})
                or
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_uri_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) > {$upper_uri_ritu})
                    )
                and
                    (
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_mate_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) < {$lower_mate_ritu})
                or
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_mate_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) > {$upper_mate_ritu})
                    )
                and
                    (
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) < {$lower_equal_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) < {$lower_equal_ritu})
                or
                    (((Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100) > {$upper_equal_ritu})
                or
                    (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) > {$upper_equal_ritu})
                    )
    ";
    $_SESSION['standard_where4'] = ($where . $condition); // 明細表のためセッションに保存
    $_SESSION['standard_condition4'] = $condition . $out;        // グラフのための条件保存
    $query = ($sql . $where . $condition . $out);              // 合体
    $res_sum = array();
    if (($rows_sum = getResult($query, $res_sum)) < 0) {
        $_SESSION['s_sysmsg'] .= 'データベースからの抽出でエラーが発生しました。管理担当者へ連絡して下さい！';
    }
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
    if ($t_ken <= 0) {
        $sum4_ken = 0;
        $sum4_suu = 0;
        $sum4_uri = 0;
        $sum4_sou = 0;
        $sum4_rit = 0;
        $sum4_sik = 0;
    } else {
        $sum4_ken = $t_ken;
        $sum4_suu = $t_kazu;
        $sum4_uri = $t_kingaku;
        $sum4_sou = $t_zai;
        if ($sum4_uri == 0) {       // Division by zero 対応 2007/11/07
            $sum4_rit = 0;
        } else {
            $sum4_rit = Uround($sum4_sou / $sum4_uri * 100, 1);
        }
        if ($sum4_sou > 0) {    // 0割チェック
            $sum4_sik = Uround($sum4_uri / $sum4_sou * 100, 1);
        } else {
            $sum4_sik = 0;
        }
    }
    ////////// 合計の計算
    $sum_ken += $sum4_ken;
    $sum_suu += $sum4_suu;
    $sum_uri += $sum4_uri;
    $sum_sou += $sum4_sou;
    ////////// 条件４は最後なので全体の計算もする
    if ($sum_uri <= 0) {
        $sum_rit = 0;
    } else {
        $sum_rit = Uround($sum_sou / $sum_uri * 100, 1);
    }
    if ($sum_sou <= 0) {
        $sum_sik = 0;
    } else {
        $sum_sik = Uround($sum_uri / $sum_sou * 100, 1);
    }
    $uri4 = $sum4_uri;  // 書式設定する前に数値で保存
    $sum4_ken = number_format($sum4_ken);
    $sum4_suu = number_format($sum4_suu);
    $sum4_uri = number_format($sum4_uri);
    $sum4_sou = number_format(Uround($sum4_sou, 0));
    if ($sum4_rit > 100.0) {
        $sum4_rit = "<font style='color:red;'>" . number_format($sum4_rit, 1) . '％' . '</font>';
    } else {
        $sum4_rit = number_format($sum4_rit, 1) . '％';
    }
    if ($sum4_sik < 100.0 && $sum4_sik > 0.0) {
        $sum4_sik = "<font style='color:red;'>" . number_format($sum4_sik, 1) . '％' . '</font>';
    } else {
        $sum4_sik = number_format($sum4_sik, 1) . '％';
    }
    /***************** 総合計の書式設定 *******************/
    $uri = $sum_uri;    // 書式設定する前に数値で保存
    $sum_ken = number_format($sum_ken);
    $sum_suu = number_format($sum_suu);
    $sum_uri = number_format($sum_uri);
    $sum_sou = number_format(Uround($sum_sou, 0));
    if ($sum_rit > 100.0) {
        $sum_rit = "<font style='color:red;'>" . number_format($sum_rit, 1) . '％' . '</font>';
    } else {
        $sum_rit = number_format($sum_rit, 1) . '％';
    }
    if ($sum_sik < 100.0 && $sum_sik > 0.0) {
        $sum_sik = "<font style='color:red;'>" . number_format($sum_sik, 1) . '％' . '</font>';
    } else {
        $sum_sik = number_format($sum_sik, 1) . '％';
    }
    /***************** 各売上/全体売上*100 %を計算 *******************/
    if ($uri > 0) {
        $tmp1_uri_ritu = Uround($uri1 / $uri * 100, 1);
        /*************** 四捨五入の合計時の誤差補正ロジック *****************/
        if ($uri2 == 0) {
            $tmp2_uri_ritu = 0;
        } elseif ($uri3 == 0 && $uri4 == 0)  {
            $tmp2_uri_ritu = (100.0 - ($tmp1_uri_ritu));
        } else {
            $tmp2_uri_ritu = Uround($uri2 / $uri * 100, 1);
        }
        if ($uri3 == 0) {
            $tmp3_uri_ritu = 0;
        } elseif ($uri4 == 0)  {
            $tmp3_uri_ritu = (100.0 - ($tmp1_uri_ritu + $tmp2_uri_ritu));
        } else {
            $tmp3_uri_ritu = Uround($uri3 / $uri * 100, 1);
        }
        if ($uri4 == 0) {
            $tmp4_uri_ritu = 0;
        } else {
            $tmp4_uri_ritu = (100.0 - ($tmp1_uri_ritu + $tmp2_uri_ritu + $tmp3_uri_ritu));
        }
        /*************** 合計で１００％になるのを確認するロジック *****************/
        $tmp_uri_ritu  = ($tmp1_uri_ritu + $tmp2_uri_ritu + $tmp3_uri_ritu + $tmp4_uri_ritu);
        /*************** 四捨五入補正ロジック END ****************/
        $sum1_uri_ritu = number_format($tmp1_uri_ritu, 1) . '％';   // 条件１
        $sum2_uri_ritu = number_format($tmp2_uri_ritu, 1) . '％';   // 条件２
        $sum3_uri_ritu = number_format($tmp3_uri_ritu, 1) . '％';   // 条件３
        $sum4_uri_ritu = number_format($tmp4_uri_ritu, 1) . '％';   // その他
        $sum_uri_ritu  = number_format($tmp_uri_ritu , 1) . '％';   // 全体
    } else {
        $sum1_uri_ritu = '0.0％';
        $sum2_uri_ritu = '0.0％';
        $sum3_uri_ritu = '0.0％';
        $sum4_uri_ritu = '0.0％';
        $sum_uri_ritu  = '0.0％';
    }
    ////////// ブロック終了
    break;
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>
<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./sales_standard_pre_form.js?<?= $uniq ?>'>
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
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
.pt12br {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     right;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
}
.sum {
    font-size:          12pt;
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
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>

<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    document.select_form.uri_passwd.focus();      // パスワード入力位置へ
    document.select_form.uri_passwd.select();
}
function graph_exec(sel) {
    switch (sel) {
    case 1:
        document.graph_form.graph_exec.value = '1';
        break;
    case 2:
        document.graph_form.graph_exec.value = '2';
        break;
    case 3:
        document.graph_form.graph_exec.value = '3';
        break;
    case 4:
        document.graph_form.graph_exec.value = '4';
        break;
    default:
        return FALSE;
    }
    document.graph_form.submit();
}
// -->
</script>
<form name='graph_form' action='<?=$menu->out_action('グラフ')?>' method='get'>
    <input type='hidden' name='graph_exec' value=''>
</form>
</head>

<body onLoad='set_focus()' style='overflow:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='select_form' action='<?=$menu->out_self()?>' method='post' onSubmit='return chk_select_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' style='background-color:blue; color:yellow;' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption()?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        パスワードを入れて下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='12' value='<?php echo $uri_passwd ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品グループを選択して下さい。
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' class='pt12b' onChange='document.getElementById("graphExecForm").innerHTML = "&nbsp;";'>
                            <option value='A' <?php if ($div=='A') echo 'selected'?>>　全　体　</option>
                            <option value='C' <?php if ($div=='C') echo 'selected'?>>カプラ全体</option>
                            <option value='CH' <?php if ($div=='CH') echo 'selected'?>>カプラ標準</option>
                            <option value='CS' <?php if ($div=='CS') echo 'selected'?>>カプラ特注</option>
                            <option value='L' <?php if ($div=='L') echo 'selected'?>>リニア全体</option>
                            <option value='LL' <?php if ($div=='LL') echo 'selected'?>>リニアのみ</option>
                            <option value='LB' <?php if ($div=='LB') echo 'selected'?>>バイモル</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        日付を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='d_start' size='8' class='pt12b' value='<?php echo $d_start ?>' maxlength='8'>
                        〜
                        <input type='text' name='d_end' size='8' class='pt12b' value='<?php echo $d_end ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品番号の指定
                        (指定しない場合は空白)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='9' class='pt12b' value='<?= $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        売上区分 =１：完成 (これのみ照会可能)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='kubun'>
                            <option value='1' selected>1完成</option>
                        <select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='left' style='font-size:11pt;font-weight:bold;'>
                        条件１<br>
                        総材料費に対する仕切単価の率範囲１１８％(例：116.0〜9999)<br>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lower_uri_ritu' size='4' class='pt12br' value='<?=$lower_uri_ritu?>' maxlength='5'>
                        ％ 〜
                        <input type='text' name='upper_uri_ritu' size='4' class='pt12br' value='<?=$upper_uri_ritu?>' maxlength='5'>
                        ％
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='left' style='font-size:11pt;font-weight:bold;'>
                        条件２<br>
                        総材料費に対する仕切単価の率範囲１１０％(例：108.0〜116.0)<br>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lower_mate_ritu' size='4' class='pt12br' value='<?=$lower_mate_ritu?>' maxlength='5'>
                        ％ 〜
                        <input type='text' name='upper_mate_ritu' size='4' class='pt12br' value='<?=$upper_mate_ritu?>' maxlength='5'>
                        ％
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='left' style='font-size:11pt;font-weight:bold;'>
                        条件３<br>
                        総材料費に対する仕切単価の率範囲(下限値)(例：0.0〜108.0)<br>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lower_equal_ritu' size='4' class='pt12br' value='<?=$lower_equal_ritu?>' maxlength='5'>
                        ％ 〜
                        <input type='text' name='upper_equal_ritu' size='4' class='pt12br' value='<?=$upper_equal_ritu?>' maxlength='5'>
                        ％
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        １ページの表示行数を指定して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sales_page' size='3' value='<?php echo $sales_page ?>' maxlength='3' style='text-align:center;'>
                        初期値：25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='1' align='center' id='graphExecForm'>
                        <?php if (isset($_REQUEST['sum_exec'])) { ?>
                        <input type='button' name='graph1' value='３ヶ月グラフ' onClick='graph_exec(1)'>
                        <input type='button' name='graph2' value='６ヶ月グラフ' onClick='graph_exec(2)'>
                        <input type='button' name='graph3' value='12ヶ月グラフ' onClick='graph_exec(3)'>
                        <input type='button' name='graph3' value='18ヶ月グラフ' onClick='graph_exec(4)'>
                        <?php } ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </td>
                    <td class='winbox' colspan='1' align='center'>
                        <input type='submit' name='sum_exec' value='合計表照会'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        <?php if (isset($_REQUEST['sum_exec'])) { ?>
        <br>
        <form name='sum_form' action='<?=$menu->out_action('売上明細')?>' method='get'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <th class='winbox' width= '70'>合計表</th>
                <th class='winbox'>照会</th>
                <th class='winbox' width= '70'>合計件数</th>
                <th class='winbox' width= '90'>合計数量</th>
                <th class='winbox' width='140'>合計売上金額(円)</th>
                <th class='winbox' width= '90'>売上/全体<br>*100</th>
                <th class='winbox' width='140'>合計総材料費(円)</th>
                <th class='winbox' width= '90'>総材/売上<br>*100</th>
                <th class='winbox' width= '90'>売上/総材<br>*100</th>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>条件１</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view1' value='明細' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum1_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>条件２</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view2' value='明細' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum2_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>条件３</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view3' value='明細' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum3_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>その他</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view4' value='明細' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum4_sik?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <div class='sum'>全　体</div>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' name='standard_view' value='明細' >
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_ken?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_suu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_uri?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_uri_ritu?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_sou?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_rit?></div>
                    </td>
                    <td class='winbox' align='right'>
                        <div class='sum'><?=$sum_sik?></div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        <?php } ?>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
