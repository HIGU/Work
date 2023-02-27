<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 明細(総材料費比較) 照会 製品別                                      //
// Copyright (C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2011/05/30 Created   material_compare_sale_view_product.php              //
// 2011/05/31 グループコード変更に伴いSQL文を変更                           //
// 2011/06/01 区分を強制的に1=完成に変更、材料増減を追加                    //
// 2011/06/07 表示行の整理、SQL文の整理                                     //
// 2011/06/13 各データの平均等を計算して表示                                //
// 2011/06/14 金額増減計を表示するようにした。                              //
// 2011/06/20 掛率平均を掛率判定値($power_rate)を基準に色分け               //
//            CSV出力を追加(製品群-開始年月-終了年月-基準月-グループ)       //
// 2011/07/06 製品グループにカプラ標準とリニア標準を追加                    //
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
//$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('製品グループ別 売上照会(総材料費比較)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費履歴',   INDUST . 'material/materialCost_view_assy.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_section']    = $_REQUEST['section'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_first_ym']   = $_REQUEST['first_ym'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $_SESSION['s_uri_ritu']   = $_REQUEST['uri_ritu'];
    $_SESSION['s_sales_page'] = $_REQUEST['sales_page'];
    $_SESSION['uri_assy_no']  = $_REQUEST['assy_no'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $section    = $_SESSION['s_section'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $first_ym   = $_SESSION['s_first_ym'];
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
        $query = "select
                        count(数量) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
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
                  on p.mhshc=gnm.mhgcd";
    //////////// SQL where 句を 共用する
    $search = "where 計上日>=$d_start and 計上日<=$d_end";
    if ($assy_no != '') {       // 製品番号が指定された場合
        $search .= " and assyno like '{$assy_no}%%'";
    } elseif ($section != " ") {    // 製品グループコードで絞込み（全グループ設定をはずしたのでブランクはないはず）
        $search .= " and gnm.mhggp='$section'";
    }
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
$section    = $_SESSION['s_section'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$first_ym   = $_SESSION['s_first_ym'];
$kubun      = $_SESSION['s_kubun'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$search     = $_SESSION['sales_search'];

$cost1_ym = $first_ym;

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

///////// 掛率判定値
///////// 掛率が一定ではなくなったら表示部のロジックも変更する。
$power_rate = 1.13;      // 2011/04/01移行

///// 製品グループ名の設定
if ($section != " ") {
    $query_s = "
            SELECT  groupm.group_no                AS グループ番号     -- 0
                ,   groupm.group_name              AS グループ名       -- 1
            FROM
                product_serchGroup AS groupm
            WHERE
                groupm.group_no = {$section}
            ORDER BY
                group_name
        ";

    $res_s = array();
    if (($rows_s = getResultWithField2($query_s, $field_s, $res_s)) <= 0) {
        $_SESSION['s_sysmsg'] = "グループの登録がありません！";
        $field[0]   = "グループ番号";
        $field[1]   = "グループ名";
        $_SESSION['s_sysmsg'] = "登録がありません！";
        //$result->add_array2('res_s', '');
        //$result->add_array2('field_s', '');
        //$result->add('num_s', 2);
        //$result->add('rows_s', '');
        $section_name = '';
    } else {
        $num_s = count($field_s);
        //$result->add_array2('res_s', $res_s);
        //$result->add_array2('field_s', $field_s);
        //$result->add('num_s', $num_s);
        //$result->add('rows_s', $rows_s);
        $section_name = $res_s[0][1];
    }
}
///// 製品グループ名の設定
if ($section == " ") $section_name = "全グループ";                  // 全グループ設定は外したので使っていない

//////////// 表形式のデータ表示用のサンプル Query & 初期化
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
                            END                      AS 単価増減        --11
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
                      ", $search);   // 共用 $search で検索
$res_t   = array();
$field_t = array();
if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num_t           = count($field_t);     // フィールド数取得
    $diff_kin_total   = 0;                   // 金額増減トータル
    $diff_cost_total  = 0;                   // 単価増減トータル
    $diff_cost_ave    = 0;                   // 単価増減平均
    $diff_cost_count  = 0;                   // 単価増減平均用カウンター
    $diff_cost_sum    = 0;                   // 単価増減平均用トータル
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

//////////// 表題の設定
$ft_kingaku    = number_format($t_kingaku);                    // ３桁ごとのカンマを付加
$ft_ken        = number_format($t_ken);
$ft_kazu       = number_format($t_kazu);
$f_cost_rate   = number_format($cost_rate, 2);
$f_diff_rate   = number_format($diff_rate, 2);
$f_diff_cost_t = number_format($diff_cost_total, 2);
$f_diff_cost_a = number_format($diff_cost_ave, 2);
$f_diff_kin_t  = number_format($diff_kin_total, 0);
$f_d_start     = format_date($d_start);                        // 日付を / でフォーマット
$f_d_end       = format_date($d_end);
$menu->set_caption("<u>製品=<font color='red'>{$section_name}</font>：{$f_d_start}～{$f_d_end}：合計件数={$ft_ken}：合計金額={$ft_kingaku}：合計数量={$ft_kazu}<u>");
$menu->set_caption2("<u>単価増減計{$f_diff_cost_t}：単価増減平均{$f_diff_cost_a}：単価増減率％平均={$f_diff_rate}％：金額増減計{$f_diff_kin_t}：掛率平均={$f_cost_rate}<u>");

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

//////////// 表形式のデータ表示用のサンプル Query & 初期化
    $query = sprintf("select
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
                            END                      AS 単価増減        --11
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
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    for ($r=0; $r<$rows; $r++) {
        $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
    }
    $_SESSION['SALES_TEST'] = sprintf("order by 計上日 offset %d limit %d", $offset, PAGE);
}

//////////////////// 総材料費カプラ標準賃率57円置換用
//$query_i = sprintf("select
//                            CASE
//                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
//                                ELSE u.計画番号
//                            END                     as 計画番号        -- 0
//                      from
//                            hiuuri as u
//                      left outer join
//                            assembly_schedule as a
//                      on u.計画番号=a.plan_no
//                      left outer join
//                            miitem as m
//                      on u.assyno=m.mipn
//                      left outer join
//                            material_cost_header as mate
//                      on u.計画番号=mate.plan_no
//                      LEFT OUTER JOIN
//                            sales_parts_material_history AS pmate
//                      ON (u.assyno=pmate.parts_no AND u.計上日=pmate.sales_date)
//                      WHERE 計上日>=20071001 and 計上日<=20080331
//                      AND 事業部='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)
//                      AND datatype=1
//                      order by 計画番号
//                        ");   // 共用 $search で検索
//$res_i   = array();
//$field_i = array();
//if (($rows_i = getResultWithField3($query_i, $field_i, $res_i)) <= 0) {
//    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
//    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
//    exit();
//} else {
//    for ($r=0; $r<$rows_i; $r++) {
//        $query_c = sprintf("UPDATE material_cost_header SET assy_rate = 57.00 WHERE plan_no='{$res_i[$r][0]}'");
//        $res_c   = array();
//        if (getResult($query_c, $res_c) <= 0) {
//        } else {
//        }
//    }
//}
// SQLのサーチ部も日本語を英字に変更。'もエラーになるので/に一時変更
$csv_search = str_replace('計上日','keidate',$search);
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
<body onLoad='set_focus()'>
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
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption2(), "\n" ?>
                </td>
                <td align='center' class='caption_font'>
                    <a href='material_compare_sale_view_csv.php?csvdiv=<?php echo $div ?>&csvd_start=<?php echo $d_start ?>&csvd_end=<?php echo $d_end ?>&csvfirst_ym=<?php echo $first_ym ?>&csvsearch=<?php echo $csv_search ?>&csvsection=<?php echo $section ?>&csvdiv=<?php echo $div ?>'>
                        <B>CSV出力<B>
                    </a>
                <td>
            </tr>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    if ($i <= 14) {
                        if ($i == 1) {
                ?>
                        <th class='winbox' nowrap>計画<BR>番号</th>
                <?php
                        } elseif ($i == 2) {
                ?>
                        <th class='winbox' nowrap>製品<BR>番号</th>
                <?php
                        } elseif ($i == 5) {
                ?>
                        <th class='winbox' nowrap>仕切<BR>単価</th>
                <?php
                        } elseif ($i == 7) {
                ?>
                        <th class='winbox' nowrap>総材<BR>料費</th>
                <?php
                        } elseif ($i == 9) {
                ?>
                        <th class='winbox' nowrap>基準総<BR>材料費</th>
                <?php
                        } elseif ($i == 10) {
                ?>
                        <th class='winbox' nowrap>基準<BR>登録日</th>
                <?php
                        } elseif ($i == 11) {
                ?>
                        <th class='winbox' nowrap>単価<BR>増減</th>
                <?php
                        } elseif ($i == 12) {
                ?>
                        <th class='winbox' nowrap>単価増<BR>減率％</th>
                <?php
                        } elseif ($i == 13) {
                ?>
                        <th class='winbox' nowrap>金額<BR>増減</th>
                <?php
                        } elseif ($i == 14) {
                ?>
                        <th class='winbox' nowrap>掛<BR>率</th>
                <?php
                        } else {
                ?>
                        <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                        }
                    }
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
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                            case 0:     // 計上日
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 2:
                                echo "<td class='winbox' nowrap align='center'><a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('総材料費履歴')}?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][1]), "\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                                break;
                            case 3:     // 製品名
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 4:     // 数量
                                echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 5:     // 仕切単価
                                echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 6:     // 金額
                                echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // 総材料費
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>-----</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][1]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 9:     // 基準総材料費
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'>
                                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費照会'), "?plan_no={$res[$r][15]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none;'>"
                                                    , number_format($res[$r][$i], 2), "</a></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 11:     // 単価増減
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                } elseif (($res[$r][7] == $res[$r][9]) && ($res[$r][7] != 0 || $res[$r][9] != 0)) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 12:     // 増減率％
                                if ($res[$r][7] != 0 && $res[$r][11] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                } elseif ($res[$r][7] != 0 && $res[$r][11] == 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>0.00</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 13:     // 金額増減
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                } elseif (($res[$r][7] == $res[$r][9]) && ($res[$r][7] != 0 || $res[$r][9] != 0)) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 14:     // 掛率
                                if ($res[$r][5] != 0 && $res[$r][7] != 0) {
                                    if ($res[$r][$i] > $power_rate) {
                                        echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'><font color='blue'>" . number_format($res[$r][$i], 2) . "</font></div></td>\n";
                                    } elseif ($res[$r][$i] < $power_rate) {
                                        echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'><font color='red'>" . number_format($res[$r][$i], 2) . "</font></div></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 15:     // 基準総材料計画
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
        <!--
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>総材料費の青色表示は同計画番号で登録がある物で、茶色は同計画では無いが、それ以前で最新の登録を表示</td></tr>
        </table>
        -->
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
