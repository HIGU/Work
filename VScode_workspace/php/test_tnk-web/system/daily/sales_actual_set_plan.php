#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 予定 月初データDBへ保存 new version  sales_actual_set_plan.php      //
// Copyright (C) 2020-2020 Waki.Ryota tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/17 Created   sales_actual_set_plan.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('error_reporting', E_ALL);               // E_ALL='2047' debug 用
// ini_set('display_errors', '1');                  // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');                // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);             // 最大実行時間=20分 CLI CGI版
// ob_start('ob_gzhandler');                           // 出力バッファをgzip圧縮
// session_start();                                    // ini_set()の次に指定すること Script 最上行

require_once ('/var/www/html/function.php');                // define.php と pgsql.php を require_once している
require_once ('/var/www/html/tnk_func.php');                // TNK に依存する部分の関数を require_once している
require_once ('/var/www/html/MenuHeader.php');              // TNK 全共通 menu class
require_once ('/var/www/html/ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class

/*
//////////// セッションのインスタンスを登録
$session = new Session();

if( isset($_REQUEST['start_date']) ) {
    $d_start = $_REQUEST['start_date'];
} else {
    $d_start = 20201201;    // テスト固定
}

if( isset($_REQUEST['end_date']) ) {
    $d_end = $_REQUEST['end_date'];
} else {
    $d_end = 20201231;      // テスト固定
}

access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

$err_flg = false;

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
*/

//$d_start = 20201201;    // テスト固定
//$d_end   = 20201231;    // テスト固定

$today_ym = date('Ymd');

//if (substr($today_ym, 6, 2) == 1) {
    
    $d_start = $today_ym;
    $d_end   = substr($today_ym, 0, 6) . '99';
    
    // 既に、月初予定が登録されていないかチェック
    $target_ym = substr($d_start,0,6);
    $query = "SELECT kanryou FROM month_first_sales_plan WHERE kanryou LIKE '{$target_ym}%' LIMIT 1";
    if( getResult2($query, $res_chk) > 0 ) {
        //$_SESSION['s_sysmsg'] .= "月初予定は既に登録されています。{$d_start} ～ {$d_end}";
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        //echo "月初予定は既に登録されています。$d_start ～ $d_end \n";
        exit();
    }
    
    //////////// 表形式のデータ表示用のサンプル Query & 初期化
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
                            a.line_no                     AS ラインNo     -- 7
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
                      WHERE a.kanryou>=%d AND a.kanryou<=%d AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0
                      order by a.kanryou
                      ", $d_start, $d_end);
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上予定のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    } else {
        $num = count($field);       // フィールド数取得
        for ($r=0; $r<$rows; $r++) {
            $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'UTF-8');   // 全角カナを半角カナへテスト的にコンバート
        }
    }
    
    for ($r=0; $r<$rows; $r++) {
        $set_arr = "";  // 登録情報収集用
        for ($i=0; $i<$num; $i++) {    // レコード数分繰返し
            if ($i >= 8) break;
            $set_arr[$i] = $res[$r][$i];
        }
    
        if( $set_arr[5] == 0 ) {
            $insert_qry = "INSERT INTO month_first_sales_plan (kanryou, plan_no, parts_no, midsc, plan, line_no ) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[7]}');";
        } else {
            $insert_qry = "INSERT INTO month_first_sales_plan (kanryou, plan_no, parts_no, midsc, plan, partition_price, price, line_no) VALUES ('{$set_arr[0]}', '{$set_arr[1]}', '{$set_arr[2]}', '{$set_arr[3]}', '{$set_arr[4]}', '{$set_arr[5]}', '{$set_arr[6]}', '{$set_arr[7]}');";
        }
        if( query_affected($insert_qry) <= 0 ) {
            $err_flg = true;
    //        $_SESSION['s_sysmsg'] .= "月初予定登録失敗。({$r}){$set_arr[5]}";
    //        $_SESSION['s_sysmsg'] .= $insert_qry;
        }
    
    }
//}

/*
if( $err_flg ) {
    $_SESSION['s_sysmsg'] .= "月初予定の登録に失敗しているレコードがあります。";
} else {
    $_SESSION['s_sysmsg'] .= "月初予定の登録に成功しました。";
}

header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
exit();
*/

?>
