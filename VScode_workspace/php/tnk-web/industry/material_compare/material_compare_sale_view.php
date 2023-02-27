<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 集計 照会 製品別  new version   material_compare_sale_view.php      //
// Copyright (C) 2011 - 2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2011/05/26 material_compare_sale_view.php                                //
// 2011/05/31 グループコード変更に伴いSQL文を変更                           //
// 2011/06/01 古いロジックがエラーを起こしていたのでコメント化              //
// 2011/06/13 各データの平均等を計算して表示                                //
// 2011/06/14 金額増減計を表示するようにした。                              //
// 2011/06/15 印刷ボタンを設置した（プレビュー表示）                        //
// 2011/06/22 掛率平均を掛率判定値($power_rate)を基準に色分け               //
// 2011/07/06 製品グループにカプラ標準とリニア標準を追加                    //
// 2012/03/29 製品グループの名称を変更                                      //
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

$result  = new Result;

////////////// サイト設定
//$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('製品グループ別 売上照会（総材料費比較）');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費履歴',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('売上明細',       INDUST . 'material_compare/material_compare_sale_view_product.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['s_uri_passwd']) ) {
    $_REQUEST['uri_passwd'] = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    if ( !isset($_REQUEST['d_start']) ) {
        $_REQUEST['d_start'] = $_SESSION['s_d_start'];
    }
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    if ( !isset($_REQUEST['d_end']) ) {
        $_REQUEST['d_end'] = $_SESSION['s_d_end'];
    }
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_first_ym']) ) {
    if ( !isset($_REQUEST['first_ym']) ) {
        $_REQUEST['first_ym'] = $_SESSION['s_first_ym'];
    }
} else {
    if ( isset($_POST['first_ym']) ) {
        $first_ym = $_POST['first_ym'];
    } else {
        //$first_ym = date_offset(1);
        $first_ym = '';
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    $_REQUEST['kubun'] = $_SESSION['s_kubun'];
} else {
    $kubun = '';
}
if ( isset($_SESSION['s_div']) ) {
    if ( !isset($_REQUEST['div']) ) {
        $_REQUEST['div'] = $_SESSION['s_div'];
    }
} else {
    if ( isset($_POST['div']) ) {
        $div = $_POST['div'];
    } else {
        $div = 'A';
    }
}

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
//if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_first_ym']   = $_REQUEST['first_ym'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $first_ym   = $_SESSION['s_first_ym'];
    $kubun      = $_SESSION['s_kubun'];
    $div        = $_SESSION['s_div'];
        ///// day のチェック
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// 最終日をチェックしてセットする
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '日付の指定が不正ですz！';
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
                $_SESSION['s_sysmsg'] = '日付の指定が不正ですz！';
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
    //////////// SQL where 句を 共用する
    $search = "where 計上日>=$d_start and 計上日<=$d_end";
    if ($div == 'S') {    // 特注のみなら
        $search .= " and note15 like 'SC%%'";
    } elseif ($div == 'C') {    // 標準のみなら
        $search .= " and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
    } elseif ($div == 'CC') {    // カプラ標準のみなら
        $search .= " and 事業部='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
    } elseif ($div == 'CL') {    // リニア標準のみなら
        $search .= " and 事業部='L' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
    }
    $search .= " and datatype='1'";
    $_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
//}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$search     = $_SESSION['sales_search'];
$first_ym   = $_SESSION['s_first_ym'];

///////// 掛率判定値
///////// 掛率が一定ではなくなったら表示部のロジックも変更する。
$power_rate = 1.13;      // 2011/04/01移行

///////////// 大分類金額・件数等を取得
$query_k = sprintf("select
                        sum(Uround(数量*単価,0)) as 金額,       -- 0
                        pts.top_no as 大分類名                  -- 1
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no
                  left outer join
                        mshmas as p
                  on assyno=p.mipn
                  left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                  -- on p.mhjcd=gnm.mhgcd
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  left outer join
                        product_top_serchgroup as pts
                  on psc.top_code=pts.top_no
                  %s
                  group by pts.top_no
                  order by pts.top_no
                  ", $search);   // 共用 $search で検索
$res_k   = array();
$field = array();
if (($rows_k = getResultWithField3($query_k, $field, $res_k)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}
///////////// 照会順に並び替え
$query_o = sprintf("select
                        top_no as 大分類No,                  -- 0
                        top_name as 大分類名,                -- 1
                        s_order as 照合順                    -- 2
                  from
                        product_top_serchgroup
                  order by s_order
                  ");   
$res_o   = array();
$field_o = array();
if (($rows_o = getResultWithField3($query_o, $field_o, $res_o)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>照合大分類が登録されていません。");
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num_o = count($field_o);       // フィールド数取得
    $data_top_t = 0;
    $view_data = array();
    for ($i=0; $i<$rows_o; $i++) {
        $data_top[$i][0] = '';
        $data_top[$i][1] =  0;
        $data_top[$i][2] = '';
        for ($r=0; $r<$rows_k; $r++) {
            if ($res_o[$i][0] == $res_k[$r][1]) {
                $data_top[$i][0] = $res_o[$i][1];
                $data_top[$i][1] = $res_k[$r][0];
                $data_top[$i][2] = $res_k[$r][1];
                $data_top[$i][3] = $res_o[$i][0];
                $data_top_t      += $res_k[$r][0];
            }
        }
    }
}

function get_middle_data($top_code, $search_middle, $result, $data_middle_t) {
    $search_middle .= " and psc.top_code='$top_code'";
    $query_m = sprintf("select
                        sum(Uround(数量*単価,0)) as 金額,       -- 0
                        psc.group_no as 中分類No                -- 1
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no
                  left outer join
                        mshmas as p
                  on assyno=p.mipn
                  left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                  -- on p.mhjcd=gnm.mhgcd
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  %s
                  group by psc.group_no
                  order by psc.group_no
                  ", $search_middle);   // 共用 $search で検索
    $field_m = array();
    if (($rows_m = getResultWithField3($query_m, $field_m, $res_m)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>中分類が登録されていません</font>");
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        //exit();
    } else {
        $num_m = count($res_m);       // データ数取得
        for ($r=0; $r<$rows_m; $r++) {
            $group_no = $res_m[$r][1];
            $search_c = "where group_no='$group_no'";
            $query_c = sprintf("select
                            group_name as 中分類名                  -- 0
                    from
                            product_serchGroup
                    %s
                    LIMIT 1
                    ",  $search_c);   
            $res_c   = array();
            $field_c = array();
            if (($rows_c = getResultWithField3($query_c, $field_c, $res_c)) <= 0) {
                $group_name[$r] = '';
            } else {
                $group_name[$r] = $res_c[0][0];
            }
        }
        $data_middle_sum = 0;
        for ($r=0; $r<$rows_m; $r++) {
            $res_m[$r][2]     = $group_name[$r];
            $data_middle_sum += $res_m[$r][0];
        }
        $data_middle_t += $data_middle_sum;
        $result->add_array2('data_middle', $res_m);
        $result->add('num_m', $num_m);
        $result->add('data_middle_sum', $data_middle_sum);
        $result->add('data_middle_t', $data_middle_t);
    }
}

function get_middle_rate($section, $search_rate, $result, $cost1_ym) {
    $nen        = substr($cost1_ym, 0, 4);
    $tsuki      = substr($cost1_ym, 4, 2);
    $cost1_name = $nen . "/" . $tsuki;

    if (substr($cost1_ym,4,2)!=12) {
        $cost1_ymd = $cost1_ym + 1;
        $cost1_ymd = $cost1_ymd . '10';
    } else {
        $cost1_ymd = $cost1_ym + 100;
        $cost1_ymd = $cost1_ymd - 11;
        $cost1_ymd = $cost1_ymd . '10';
    }
    $search_rate .= " and gnm.mhggp='$section'";
    $query_t = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 1
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 3
                            u.数量          as 数量,                    -- 4
                            u.単価          as 仕切単価,                -- 5
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN '-----'
                                ELSE to_char(mate.regdate, 'YYYY/MM/DD')
                            END                     AS 登録日,          -- 8
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE 
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                ELSE (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準総材料費     -- 9
                            ,
                            CASE
                                WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
                                ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準登録日       --10
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                    ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                END
                            END                      AS 材料増減        --11
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                    ELSE
                                    CASE
                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                        IS NULL THEN    CASE
                                                            WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                            ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                                        END
                                        ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                    END
                                END
                            END                      AS 増減率          --12
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                                    END
                                    ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                END
                            END                      AS 金額増減        --13
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE 
                                CASE
                                    WHEN u.単価 IS NULL THEN 0
                                    ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                                END
                            END                      AS 掛率            --14
                            ---------------- リスト外 -----------------
                            ,
                            (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                     AS 基準総材料計画  --15
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
                      left outer join
                        mshmas as p
                      on u.assyno=p.mipn
                      left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                      -- on p.mhjcd=gnm.mhgcd
                      on p.mhshc=gnm.mhgcd
                      %s
                      order by 計上日, assyno
                      ", $search_rate);   // 共用 $search で検索
    $res_t   = array();
    $field_t = array();
    if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        //exit();
        $result->add('cost_rate', '---');
        $result->add('diff_rate', '---');
        $result->add('diff_cost_total', '---');
        $result->add('diff_cost_ave', '---');
        $result->add('diff_kin_total', '---');
    } else {
        $num_t           = count($field_t);     // フィールド数取得
        $diff_kin_total   = 0;                   // 金額増減トータル
        $diff_cost_total  = 0;                   // 単価増減トータル
        $diff_cost_ave    = 0;                   // 単価増減額平均
        $diff_cost_count  = 0;                   // 単価増減額平均用カウンター
        $diff_cost_sum    = 0;                   // 単価増減額平均用トータル
        $cost_rate        = 0;                   // 掛率平均
        $cost_rate_count  = 0;                   // 掛率平均用カウンター
        $cost_rate_sum    = 0;                   // 掛率平均用トータル
        $diff_rate        = 0;                   // 単価増減率平均
        $diff_rate_count  = 0;                   // 単価増減率平均用カウンター
        $diff_rate_sum    = 0;                   // 単価増減率平均用トータル
        for ($r=0; $r<$rows_t; $r++) {
            $res_t[$r][4] = mb_convert_kana($res_t[$r][4], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
            if( $res_t[$r][14] != 0 ) {
                $cost_rate_sum += $res_t[$r][14];
                $cost_rate_count++;
            }
            if($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
                $diff_rate_sum += $res_t[$r][12];
                $diff_rate_count++;
            }
            $diff_cost_total += $res_t[$r][11];
            if ($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
                $diff_cost_sum += $res_t[$r][11];
                $diff_cost_count++;
            }
            $diff_kin_total += $res_t[$r][13];
        }
        if ($cost_rate_sum != 0 && $cost_rate_count != 0) {
            $cost_rate = $cost_rate_sum / $cost_rate_count;
        }
        if ($diff_rate_sum != 0 && $diff_rate_count != 0) {
            $diff_rate = $diff_rate_sum / $diff_rate_count;
        }
        if ($diff_cost_sum != 0 && $diff_cost_count != 0) {
            $diff_cost_ave = $diff_cost_sum / $diff_cost_count;
        }
    }
    $f_cost_rate   = number_format($cost_rate, 2);
    $f_diff_rate   = number_format($diff_rate, 2);
    $f_diff_cost_t = number_format($diff_cost_total, 2);
    $f_diff_cost_a = number_format($diff_cost_ave, 2);
    $f_diff_kin_t  = number_format($diff_kin_total, 0);
    $result->add('cost_rate', $f_cost_rate);
    $result->add('diff_rate', $f_diff_rate);
    $result->add('diff_cost_total', $f_diff_cost_t);
    $result->add('diff_cost_ave', $f_diff_cost_a);
    $result->add('diff_kin_total', $f_diff_kin_t);
}

function get_middle_total($top_code, $search_total, $result, $cost1_ym) {
    $nen        = substr($cost1_ym, 0, 4);
    $tsuki      = substr($cost1_ym, 4, 2);
    $cost1_name = $nen . "/" . $tsuki;

    if (substr($cost1_ym,4,2)!=12) {
        $cost1_ymd = $cost1_ym + 1;
        $cost1_ymd = $cost1_ymd . '10';
    } else {
        $cost1_ymd = $cost1_ym + 100;
        $cost1_ymd = $cost1_ymd - 11;
        $cost1_ymd = $cost1_ymd . '10';
    }
    $search_total .= " and psc.top_code='$top_code'";
    $query_t = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 1
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 3
                            u.数量          as 数量,                    -- 4
                            u.単価          as 仕切単価,                -- 5
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN '-----'
                                ELSE to_char(mate.regdate, 'YYYY/MM/DD')
                            END                     AS 登録日,          -- 8
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE 
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                ELSE (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準総材料費     -- 9
                            ,
                            CASE
                                WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
                                ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準登録日       --10
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                    ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                END
                            END                      AS 材料増減        --11
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                    ELSE
                                    CASE
                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                        IS NULL THEN    CASE
                                                            WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                            ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                                        END
                                        ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                    END
                                END
                            END                      AS 増減率          --12
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                                    END
                                    ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                END
                            END                      AS 金額増減        --13
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE 
                                CASE
                                    WHEN u.単価 IS NULL THEN 0
                                    ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                                END
                            END                      AS 掛率            --14
                            ---------------- リスト外 -----------------
                            ,
                            (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                     AS 基準総材料計画  --15
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
                      left outer join
                        mshmas as p
                      on u.assyno=p.mipn
                      left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                      -- on p.mhjcd=gnm.mhgcd
                      on p.mhshc=gnm.mhgcd
                      left outer join
                        product_serchGroup as psc
                      on gnm.mhggp=psc.group_no
                      %s
                      order by 計上日, assyno
                      ", $search_total);   // 共用 $search で検索
    $res_t   = array();
    $field_t = array();
    if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        //exit();
        $result->add('cost_rate', '---');
        $result->add('diff_rate', '---');
        $result->add('diff_cost_total', '---');
        $result->add('diff_cost_ave', '---');
        //exit();
    } else {
        $num_t           = count($field_t);     // フィールド数取得
        $diff_kin_total  = 0;                   // 金額増減トータル
        $diff_cost_total = 0;                   // 単価増減トータル
        $diff_cost_ave   = 0;                   // 単価増減平均
        $diff_cost_count = 0;                   // 単価増減平均用カウンター
        $diff_cost_sum   = 0;                   // 単価増減平均用トータル
        $cost_rate       = 0;                   // 掛率平均
        $cost_rate_count = 0;                   // 掛率平均用カウンター
        $cost_rate_sum   = 0;                   // 掛率平均用トータル
        $diff_rate       = 0;                   // 単価増減率平均
        $diff_rate_count = 0;                   // 単価増減率平均用カウンター
        $diff_rate_sum   = 0;                   // 単価増減率平均用トータル
        for ($r=0; $r<$rows_t; $r++) {
            $res_t[$r][4] = mb_convert_kana($res_t[$r][4], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
            if( $res_t[$r][14] != 0 ) {
                $cost_rate_sum += $res_t[$r][14];
                $cost_rate_count++;
            }
            if($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
                $diff_rate_sum += $res_t[$r][12];
                $diff_rate_count++;
            }
            $diff_cost_total += $res_t[$r][11];
            if ($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
                $diff_cost_sum += $res_t[$r][11];
                $diff_cost_count++;
            }
            $diff_kin_total += $res_t[$r][13];
        }
        if ($cost_rate_sum != 0 && $cost_rate_count != 0) {
            $cost_rate = $cost_rate_sum / $cost_rate_count;
        }
        if ($diff_rate_sum != 0 && $diff_rate_count != 0) {
            $diff_rate = $diff_rate_sum / $diff_rate_count;
        }
        if ($diff_cost_sum != 0 && $diff_cost_count != 0) {
            $diff_cost_ave = $diff_cost_sum / $diff_cost_count;
        }
        $f_cost_rate   = number_format($cost_rate, 2);
        $f_diff_rate   = number_format($diff_rate, 2);
        $f_diff_cost_t = number_format($diff_cost_total, 2);
        $f_diff_cost_a = number_format($diff_cost_ave, 2);
        $f_diff_kin_t  = number_format($diff_kin_total, 0);
        $result->add('cost_rate', $f_cost_rate);
        $result->add('diff_rate', $f_diff_rate);
        $result->add('diff_cost_total', $f_diff_cost_t);
        $result->add('diff_cost_ave', $f_diff_cost_a);
        $result->add('diff_kin_total', $f_diff_kin_t);
    }
}

function get_middle_all($search_all, $result, $cost1_ym) {
    $nen        = substr($cost1_ym, 0, 4);
    $tsuki      = substr($cost1_ym, 4, 2);
    $cost1_name = $nen . "/" . $tsuki;

    if (substr($cost1_ym,4,2)!=12) {
        $cost1_ymd = $cost1_ym + 1;
        $cost1_ymd = $cost1_ymd . '10';
    } else {
        $cost1_ymd = $cost1_ym + 100;
        $cost1_ymd = $cost1_ymd - 11;
        $cost1_ymd = $cost1_ymd . '10';
    }
    $query_t = sprintf("select
                            u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 1
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as 製品名,                  -- 3
                            u.数量          as 数量,                    -- 4
                            u.単価          as 仕切単価,                -- 5
                            Uround(u.数量 * u.単価, 0) as 金額,         -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as 総材料費,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN '-----'
                                ELSE to_char(mate.regdate, 'YYYY/MM/DD')
                            END                     AS 登録日,          -- 8
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE 
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                ELSE (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準総材料費     -- 9
                            ,
                            CASE
                                WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
                                ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS 基準登録日       --10
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                    ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                END
                            END                      AS 材料増減        --11
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                    ELSE
                                    CASE
                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                        IS NULL THEN    CASE
                                                            WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                            ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                                        END
                                        ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                    END
                                END
                            END                      AS 増減率          --12
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                                    END
                                    ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.数量
                                END
                            END                      AS 金額増減        --13
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE 
                                CASE
                                    WHEN u.単価 IS NULL THEN 0
                                    ELSE Uround(u.単価 / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                                END
                            END                      AS 掛率            --14
                            ---------------- リスト外 -----------------
                            ,
                            (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                     AS 基準総材料計画  --15
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
                      left outer join
                        mshmas as p
                      on u.assyno=p.mipn
                      left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                      -- on p.mhjcd=gnm.mhgcd
                      on p.mhshc=gnm.mhgcd
                      left outer join
                        product_serchGroup as psc
                      on gnm.mhggp=psc.group_no
                      %s
                      order by 計上日, assyno
                      ", $search_all);   // 共用 $search で検索
    $res_t   = array();
    $field_t = array();
    if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        //exit();
        $result->add('cost_rate', '---');
        $result->add('diff_rate', '---');
        $result->add('diff_cost_total', '---');
        $result->add('diff_cost_ave', '---');
        //exit();
    } else {
        $num_t           = count($field_t);     // フィールド数取得
        $diff_kin_total  = 0;                   // 金額増減トータル
        $diff_cost_total = 0;                   // 単価増減トータル
        $diff_cost_ave   = 0;                   // 単価増減平均
        $diff_cost_count = 0;                   // 単価増減平均用カウンター
        $diff_cost_sum   = 0;                   // 単価増減平均用トータル
        $cost_rate       = 0;                   // 掛率平均
        $cost_rate_count = 0;                   // 掛率平均用カウンター
        $cost_rate_sum   = 0;                   // 掛率平均用トータル
        $diff_rate       = 0;                   // 単価増減率平均
        $diff_rate_count = 0;                   // 単価増減率平均用カウンター
        $diff_rate_sum   = 0;                   // 単価増減率平均用トータル
        for ($r=0; $r<$rows_t; $r++) {
            $res_t[$r][4] = mb_convert_kana($res_t[$r][4], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
            if( $res_t[$r][14] != 0 ) {
                $cost_rate_sum += $res_t[$r][14];
                $cost_rate_count++;
            }
            if($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
                $diff_rate_sum += $res_t[$r][12];
                $diff_rate_count++;
            }
            $diff_cost_total += $res_t[$r][11];
            if ($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
                $diff_cost_sum += $res_t[$r][11];
                $diff_cost_count++;
            }
            $diff_kin_total += $res_t[$r][13];
        }
        if ($cost_rate_sum != 0 && $cost_rate_count != 0) {
            $cost_rate = $cost_rate_sum / $cost_rate_count;
        }
        if ($diff_rate_sum != 0 && $diff_rate_count != 0) {
            $diff_rate = $diff_rate_sum / $diff_rate_count;
        }
        if ($diff_cost_sum != 0 && $diff_cost_count != 0) {
            $diff_cost_ave = $diff_cost_sum / $diff_cost_count;
        }
        $f_cost_rate  = number_format($cost_rate, 2);
        $f_diff_rate  = number_format($diff_rate, 2);
        $f_diff_cost_t = number_format($diff_cost_total, 2);
        $f_diff_cost_a = number_format($diff_cost_ave, 2);
        $f_diff_kin_t = number_format($diff_kin_total, 0);
        $result->add('cost_rate', $f_cost_rate);
        $result->add('diff_rate', $f_diff_rate);
        $result->add('diff_cost_total', $f_diff_cost_t);
        $result->add('diff_cost_ave', $f_diff_cost_a);
        $result->add('diff_kin_total', $f_diff_kin_t);
    }
}

///// 製品グループ(事業部)名の設定
if ($div == "A") $div_name  = "全グループ";
if ($div == "C") $div_name  = "標準品";
if ($div == "CC") $div_name = "カプラ標準品";
if ($div == "CL") $div_name = "リニア標準品";
if ($div == "S") $div_name  = "特注品";
//////////// 表題の設定
$ft_kingaku = number_format($data_top_t);                    // ３桁ごとのカンマを付加
//$ft_ken     = number_format($t_ken);
//$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);
$f_first_ym = format_date6($first_ym);
$menu->set_caption("グループ {$div_name} : 対象年月 {$f_d_start}～{$f_d_end}：基準年月 {$f_first_ym}：合計金額={$ft_kingaku}");
//$menu->set_caption("対象年月 {$f_d_start}～{$f_d_end}：合計件数={$ft_ken}：合計金額={$ft_kingaku}：合計数量={$ft_kazu}<u>");
// SQLのサーチ部も日本語を英字に変更。'もエラーになるので/に一時変更
$csv_search = str_replace('計上日','keidate',$search);
$csv_search = str_replace('事業部','jigyou',$search);
$csv_search = str_replace('\'','/',$csv_search);
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
function framePrint() {
    //page_form.focus();
    print();
}
function PrintPreview()
{
    if(window.ActiveXObject == null || document.body.insertAdjacentHTML == null) return;
    var sWebBrowserCode = '<object width="0" height="0" classid="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></object>'; 
    document.body.insertAdjacentHTML('beforeEnd', sWebBrowserCode);
    var objWebBrowser = document.body.lastChild;
    if(objWebBrowser == null) return;
    objWebBrowser.ExecWB(7, 1);
    document.body.removeChild(objWebBrowser);
}
-->
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
.pt9b {
    font-size:      9pt;
    font-weight:    bold;
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
.winboxb {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffff;
}
.winboxg {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffcc;
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       yellow;
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
<style media=print>
<!--
/*ブラウザのみ表示*/
.dspOnly {
    display:none;
}
.footer {
    display:none;
}
// -->
</style>
</head>
    <center>
    <div class='dspOnly'>
    <?php echo $menu->out_title_border()?>
    </div>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='center' class='dspOnly'>
                        <input type="button" name="print" value="印刷" onclick="PrintPreview()">
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#FFFFFF' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <!--
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                    <th class='winbox' nowrap><div class='pt10b'><?php echo $field[1] ?></div></th>
                    <th class='winbox' nowrap><div class='pt10b'><?php echo $field[0] ?></div></th>
                    <th class='winbox' nowrap><div class='pt10b'>中分類名</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>金額</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>単価<BR>増減計</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>単価<BR>増減平均</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>単価増減<BR>率％平均</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>金額<BR>増減計</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>掛率<BR>平均</div></th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                $data_middle_t = 0;
                for ($r=0; $r<$rows_o; $r++) {
                    $flg_gu = ' ';
                    $check_gu = $r % 2;
                    if ($check_gu == 0) {
                        $flg_gu = '1';
                    }
                    if($data_top[$r][1] != 0) {
                        get_middle_data($data_top[$r][2], $search, $result, $data_middle_t);
                        $data_middle_t = $result->get('data_middle_t');
                        $num_m           = $result->get('num_m');
                        $data_middle     = $result->get_array2('data_middle');
                        $data_middle_sum = $result->get('data_middle_sum');
                        $num_m2      = $num_m + 1;
                        $assy_no = '';
                        echo "<tr>\n";
                        //echo "  <td rowspan = '" . $num_m2 . "' class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- 行ナンバーの表示 -->\n";
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='left'><div class='pt10b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='right'><div class='pt10b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='left'><div class='pt10b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='right'><div class='pt10b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        }
                        get_middle_rate($data_middle[0][1], $search, $result, $first_ym);
                        $cost_rate       = $result->get('cost_rate');
                        $diff_rate       = $result->get('diff_rate');
                        $diff_cost_total = $result->get('diff_cost_total');
                        $diff_cost_ave   = $result->get('diff_cost_ave');
                        $diff_kin_total  = $result->get('diff_kin_total');
                        echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[0][2] . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('売上明細'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&first_ym={$first_ym}&kubun={$kubun}&section={$data_middle[0][1]}&uri_ritu=52&sales_page=25&assy_no={$assy_no}&div={$div}\")' target='application' style='text-decoration:none;'>"
                                . number_format($data_middle[0][0], 0) . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_total . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_ave . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_rate . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_kin_total . "</div></td>\n";
                        if ($cost_rate > $power_rate) {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                        } elseif ($cost_rate < $power_rate) {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                        } else {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $cost_rate . "</div></td>\n";
                        }
                        echo "</tr>\n";
                        for ($i=1; $i<$num_m; $i++) {
                            get_middle_rate($data_middle[$i][1], $search, $result, $first_ym);
                            $cost_rate       = $result->get('cost_rate');
                            $diff_rate       = $result->get('diff_rate');
                            $diff_cost_total = $result->get('diff_cost_total');
                            $diff_cost_ave   = $result->get('diff_cost_ave');
                            $diff_kin_total  = $result->get('diff_kin_total');
                            echo "<tr>\n";
                            echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[$i][2] . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('売上明細'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&first_ym={$first_ym}&kubun={$kubun}&section={$data_middle[$i][1]}&uri_ritu=52&sales_page=25&assy_no={$assy_no}&div={$div}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[$i][0], 0) . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_total . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_ave . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_rate . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_kin_total . "</div></td>\n";
                            if ($cost_rate > $power_rate) {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                            } elseif ($cost_rate < $power_rate) {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                            } else {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $cost_rate . "</div></td>\n";
                            }
                            echo "</tr>\n";
                        }
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            get_middle_total($data_top[$r][3], $search, $result, $first_ym);
                            $cost_rate       = $result->get('cost_rate');
                            $diff_rate       = $result->get('diff_rate');
                            $diff_cost_total = $result->get('diff_cost_total');
                            $diff_cost_ave   = $result->get('diff_cost_ave');
                            $diff_kin_total  = $result->get('diff_kin_total');
                            echo "  <td class='winboxb' nowrap align='left'><div class='pt9b'>小計</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_cost_total . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_cost_ave . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_rate . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_kin_total . "</div></td>\n";
                            if ($cost_rate > $power_rate) {
                                echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                            } elseif ($cost_rate < $power_rate) {
                                echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                            } else {
                                echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $cost_rate . "</div></td>\n";
                            }
                        } else {
                            get_middle_total($data_top[$r][3], $search, $result, $first_ym);
                            $cost_rate       = $result->get('cost_rate');
                            $diff_rate       = $result->get('diff_rate');
                            $diff_cost_total = $result->get('diff_cost_total');
                            $diff_cost_ave   = $result->get('diff_cost_ave');
                            $diff_kin_total  = $result->get('diff_kin_total');
                            echo "  <td class='winboxg' nowrap align='left'><div class='pt9b'>小計</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_cost_total . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_cost_ave . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_rate . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_kin_total . "</div></td>\n";
                            if ($cost_rate > $power_rate) {
                                echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                            } elseif ($cost_rate < $power_rate) {
                                echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                            } else {
                                echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $cost_rate . "</div></td>\n";
                            }
                            
                        }
                        echo "</tr>\n";
                    }
                }
                ?>
            </tbody>
            <tr>
                <?php
                get_middle_all($search, $result, $first_ym);
                $cost_rate       = $result->get('cost_rate');
                $diff_rate       = $result->get('diff_rate');
                $diff_cost_total = $result->get('diff_cost_total');
                $diff_cost_ave   = $result->get('diff_cost_ave');
                $diff_kin_total  = $result->get('diff_kin_total');
                ?>
                <td class='winboxy' nowrap align='left'><div class='pt10b'>大分類計</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo number_format($data_top_t, 0) ?></div></td>
                <td class='winboxy' nowrap align='left'><div class='pt10b'>中分類計</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo number_format($data_middle_t, 0) ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_cost_total ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_cost_ave ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_rate ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_kin_total ?></div></td>
                <?php
                if ($cost_rate > $power_rate) {
                ?>
                    <td class='winboxy' nowrap align='right'><div class='pt10b'><font color='blue'><?php echo $cost_rate ?></font></div></td>
                <?php
                } elseif ($cost_rate < $power_rate) {
                ?>
                    <td class='winboxy' nowrap align='right'><div class='pt10b'><font color='red'><?php echo $cost_rate ?></font></div></td>
                <?php
                } else {
                ?>
                    <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $cost_rate ?></div></td>
                <?php
                }
                ?>
             </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
