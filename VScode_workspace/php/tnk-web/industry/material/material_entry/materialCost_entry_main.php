<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の登録 materialCost_entry_main.php                               //
// Copyright (C) 2007-2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/05/23 Created   metarialCost_entry_main.php                         //
// 2007/06/21 phpショートタグ→標準タグへ。 ボディに絶対指定を%指定へ小林   //
// 2007/06/22 $uniq に id= を埋め込み。小林                                 //
// 2007/09/18 E_ALL | E_STRICT へ変更 小林                                  //
// 2007/09/27 暫定でZZの計画だけ日付を強制変更ロジック追加 小林             //
// 2007/09/29 上記の暫定をコメントアウトして元に戻す。 小林                 //
// 2010/11/12 ASへのアップロードファイルへの書込みを追加               大谷 //
// 2020/06/01 「引当部品構成表の照会」から来た時、登録データがなければ      //
//            引当結果を登録画面へコピー（組立費は前回登録データ）     和氣 //
// 2020/06/11 総材料費自動登録時、戻った時の表示を維持する為 追加      和氣 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');     // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 21);                    // site_index=30(生産メニュー) site_id=21(総材料費の登録)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 登 録 (工程明細)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('旧総材料費登録',   INDUST . 'material/materialCost_entry_old.php');

$menu->set_frame('登録ヘッダー',     INDUST . 'material/material_entry/materialCost_entry_ViewHeader.php');
$menu->set_frame('登録ボディ',       INDUST . 'material/material_entry/materialCost_entry_ViewBody.php');
$menu->set_frame('登録フッター',     INDUST . 'material/material_entry/materialCost_entry_ViewFooter.php');

//////////// 戻先へのGETデータ設定
$menu->set_retGET('page_keep', 'On');
$menu->set_retGET('material', '1');   // 総材料費自動登録時、戻った時の表示を維持する為

$request = new Request;
$session = new Session;
//////////// ブラウザーのキャッシュ対策用
$uniq = 'id=' . $menu->set_useNotCache('target');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

//////////// 計画番号・製品番号をセッションから取得
if ($request->get('plan_no') != '') {
    $plan_no = $request->get('plan_no');
    $session->add('material_plan_no', $plan_no);
    $session->add('plan_no', $plan_no);
} elseif ($session->get('plan_no') != '') {
    $plan_no = $session->get('plan_no');
} else {
    $_SESSION['s_sysmsg'] .= '計画番号が指定されてない！';      // .= メッセージを追加する
    $msg_flg = 'alert';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
if ($request->get('assy_no') != '') {
    $assy_no = $request->get('assy_no');
    $session->add('assy_no', $assy_no);
} elseif ($session->get('assy_no') != '') {
    $assy_no = $session->get('assy_no');
} else {
    $_SESSION['s_sysmsg'] .= '製品番号が指定されてない！';      // .= メッセージを追加する
    $msg_flg = 'alert';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

/* 「引当部品構成表の照会」からた時、登録データがなければ、照会結果をコピーする -> */
if( $request->get('data_copy') != '' ) {   // 引当部品構成表から来た
    if( isset($_SESSION['entry_data']) ) { // 登録できる照会データがある（引当部品構成表で登録）
        // 材料の登録
        $query = "SELECT parts_no FROM material_cost_history WHERE plan_no='{$plan_no}' AND assy_no='{$assy_no}'";
        if( getResult2($query, $res_chk) <= 0 ) { // まだ、登録されていない
            $res = $_SESSION['entry_data'];
            // １レコードごと登録する為、レコード分繰り返す
            for( $r=0; !empty($res[$r]); $r++ ) {
                $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark, par_parts, pro_price, pro_num, intext, last_date, last_user)
                                  VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, CURRENT_TIMESTAMP, '%s')",
                                  $plan_no, $assy_no, $res[$r][0], $res[$r][1], $res[$r][2], $res[$r][3], $res[$r][4], $res[$r][5], $res[$r][6], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "{$res[$r][0]}：工程{$res[$r][1]}の追加に失敗！";    // .= に注意
                }
            }

            if( isset($_SESSION['assy_reg_data']) ) { // 登録できる組立費がある（引当部品構成表で登録）
                // 組立費の登録（前回登録時の工数と賃率を使用）
                $query = "SELECT plan_no FROM material_cost_header WHERE plan_no='{$plan_no}'";
                if( getResult2($query, $res_chk) <= 0 ) {
                    $assy_reg_data = $_SESSION['assy_reg_data']; // 引当部品構成表で登録
                    $m_time = $assy_reg_data[0];
                    $m_rate = $assy_reg_data[1];
                    $a_time = $assy_reg_data[2];
                    $a_rate = $assy_reg_data[3];
                    $g_time = $assy_reg_data[4];
                    $g_rate = $assy_reg_data[5];
                    $s_rate = 0; // materialCost_entry_ViewFooter.phpで、RATEの値を登録
                    $assy_time = ($m_time + $a_time + $g_time);

                    $query = sprintf("INSERT INTO material_cost_header
                                    (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, last_date, last_user)
                                    VALUES ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} で組立費の追加に失敗！";    // .= に注意
                        $msg_flg = 'alert';
                    } else {
                        $_SESSION['s_sysmsg'] .= "<font color='yellow'>計画番号：{$plan_no} の組立費を追加しました</font>";
                    }

                }
            }
        }
        unset( $_SESSION['entry_data'] );    // 登録を抜けるため解放
        unset( $_SESSION['assy_reg_data'] ); // 登録を抜けるため解放
    }
}
/* <------------------------------------------------------------------------------ */

//////////// SQL 文の where 句を 共用する
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// 合計レコード数・総材料費の取得     (対象データの最大数をページ制御に使用)
$query = sprintf("SELECT count(*), sum(Uround(pro_price * pro_num, 2)) FROM material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // 内作の総材料費
    $_SESSION['s_sysmsg'] .= "外作総材料費の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}
$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // 外作の総材料費
    $_SESSION['s_sysmsg'] .= "内作総材料費の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}

////////////// 登録・変更ロジックの前処理
if ($request->get('entry') != '') {
    $parts_no = $request->get('parts_no');
    $par_parts = $request->get('par_parts');
    $query = "SELECT midsc FROM miitem WHERE mipn='{$parts_no}'";
    if (getResult2($query, $res_chk) <= 0) {
        $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no} はマスター未登録です！";    // .= に注意
        $msg_flg = 'alert';
        $request->del('entry');
        // $unreg_msg = 1;     // JavaScriptのalert へ継ぐためセットする 2005/02/08 alert()を削除
    } else {
        if ($request->get('par_parts') != '') {
            $query = "SELECT parts_no FROM material_cost_history WHERE plan_no='{$plan_no}' AND parts_no='{$par_parts}'";
            if (getResult2($query, $res_chk) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "親番号：{$par_parts} が見つかりません！ 先に登録して下さい。";    // .= に注意
                $msg_flg = 'alert';
                $request->del('entry');
                $request->add('page_keep', 1);
            }
        }
    }
}

////////////// 登録・変更ロジック (合計レコード数取得前に行う)
if ($request->get('entry') != '') {
    $parts_no = $request->get('parts_no');
    $pro_no   = $request->get('pro_no');
    $pro_mark = $request->get('pro_mark');
    $par_parts = $request->get('par_parts');
    $pro_price = $request->get('pro_price');
    // if ($pro_price == '') $pro_price = 0;
    $pro_num   = $request->get('pro_num');
    $intext    = $request->get('intext');
    $query = sprintf("SELECT parts_no FROM material_cost_history WHERE plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 変更
        $query = sprintf("UPDATE material_cost_history SET plan_no='%s', assy_no='%s', parts_no='%s',
                            pro_no=%d, pro_mark='%s', par_parts='%s', pro_price=%01.2f, pro_num=%01.4f,
                            intext=%01d, last_date=CURRENT_TIMESTAMP, last_user='%s'",
                          $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price, $pro_num,
                          $intext, $_SESSION['User_ID']);
        $query .= sprintf(" WHERE plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}：工程{$pro_no}の変更に失敗！";    // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程{$pro_no}を変更しました！</font>";    // .= に注意
        }
        //$request->del('entry');   // UPDATEの場合はページを維持するため entry を削除して
    } else {                                    //////// 登録なし INSERT 新規
        /*****
        if (substr($plan_no, 0, 2) == 'ZZ') {
            $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                            par_parts, pro_price, pro_num, intext, regdate, last_date, last_user)
                          VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, '2007-10-06 00:00:00', CURRENT_TIMESTAMP, '%s')",
                            $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price,
                            $pro_num, $intext, $_SESSION['User_ID']);
        } else {
        }
        *****/
        $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, last_date, last_user)
                      VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, CURRENT_TIMESTAMP, '%s')",
                        $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price,
                        $pro_num, $intext, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}：工程{$pro_no}の追加に失敗！";    // .= に注意
            ///////////////////////////////////// debug ADD 2005/05/27
            $fp_error = fopen($error_log_name, 'a');   // エラーログへの書込みでオープン
            $log_msg  = date('Y-m-d H:i:s');
            $log_msg .= " エラーの時の SQL 文は以下 \n";
            fwrite($fp_error, $log_msg);
            fwrite($fp_error, $query);
            fclose($fp_error);
            ///////////////////////////////////// debug END
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程{$pro_no}を追加しました！</font>";    // .= に注意
        }
        //$request->del('entry');   // INSERTの場合も(2006/02/27)ページを維持するため entry を削除して
    }
}

//////////// 削除ボタンが押された時
if ($request->get('del') != '') {
    $parts_no = $request->get('parts_no');
    $pro_no   = $request->get('pro_no');
    $par_parts = $request->get('par_parts');
    $c_number = $request->get('c_number');
    $query = "SELECT parts_no, pro_no FROM material_cost_history ";
    $search_del = " WHERE plan_no='{$plan_no}' and parts_no='{$parts_no}' and pro_no={$pro_no} and par_parts='{$par_parts}'";
    $query .= $search_del;
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "{$parts_no}：工程 {$pro_no}：は登録されていません！";    // .= に注意
        $msg_flg = 'alert';
    } else {
        $query = "SELECT parts_no FROM material_cost_history WHERE plan_no='{$plan_no}' AND par_parts='{$parts_no}'";
        if (getResult2($query, $res_chk) > 0 ) {
            $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no} は既に子部品が登録されています！ 先に子部品を削除して下さい。";    // .= に注意
            $msg_flg = 'alert';
            $request->del('del');
            $request->add('no_del', 1);   // 削除の場合はページを維持するため page_keepを使用
            $no_del_num = $c_number;
            $request->add('page_keep', 1);   // 削除の場合はページを維持するため page_keepを使用
        } else {
            $query = "delete FROM material_cost_history ";
            $query .= $search_del;
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$parts_no}：工程 {$pro_no}：の削除に失敗！";    // .= に注意
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程 {$pro_no}：を削除しました！</font>";
            }
            $request->add('page_keep', 1);   // 削除の場合はページを維持するため page_keepを使用
        }
    }
}

//////////// 完了ボタンが押された時
if ($request->get('final') != '') {
    $query = "SELECT assy_time FROM material_cost_header WHERE plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {
        ///// 登録済 UPDATE
        $query = sprintf("UPDATE material_cost_header SET
                        plan_no='{$plan_no}', assy_no='{$assy_no}',
                        sum_price=%01.2f, ext_price=%01.2f, int_price=%01.2f,
                        last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                        WHERE plan_no='{$plan_no}'",
                    $sum_kin, $ext_kin, $int_kin
        );
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "合計サマリーの計画番号：{$plan_no} の変更に失敗！";   // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>計画番号：{$plan_no} => 総材料費の登録を完了しました。</font>";
            // AS400登録用サマリー登録
            $sql2 = "
                SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
            ";
            $kan = '';
            getUniResult($sql2, $kan);
            if ($kan != '') {
                $hg_date = substr($kan, 2, 4);                  // 完成年月（原価日付）
                $hg_ym   = substr($kan, 0, 2);                  // 原価年月YY
            } else {
                $hg_date = '';
                $hg_ym   = '';
            }
            $today      = date('Ymd');
            $entry_date = substr($today, 2, 6);                 // 登録日
            $entry_year = substr($today, 0, 2);                 // 登録年YY
            $sum_price  = $sum_kin;                             // 社内材料費
            $query = "SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
                        FROM material_cost_header WHERE plan_no='{$plan_no}'";
            $res_time = array();
            if ( getResult2($query, $res_time) > 0 ) {
                $m_time     = $res_time[0][0];
                $m_rate     = $res_time[0][1];
                $a_time     = $res_time[0][2];
                $a_rate     = $res_time[0][3];
                $g_time     = $res_time[0][4];    
                $g_rate     = $res_time[0][5];
                $assy_rate  = $res_time[0][7];
            } else {
                $m_time     = 0;
                $m_rate     = 0;
                $a_time     = 0;
                $a_rate     = 0;
                $g_time     = 0;
                $g_rate     = 0;
                $assy_rate  = 0;
            }
            $m_price    = Uround($m_time * $assy_rate, 2);      // 手作業組立費
            $a_price    = Uround($a_time * $a_rate, 2);         // 自動機組立費
            $g_price    = Uround($g_time * $assy_rate, 2);      // 外注組立費
            $m_place    = '01111';                              // 組立場所（01111固定）
            $hgkkk      = 'W';                                  // 決算区分（W固定 Webの意）
            $query = sprintf("SELECT plan_no FROM material_cost_summary WHERE plan_no='%s' and assy_no='%s'",
                        $plan_no, $assy_no);
            $res_chk = array(); 
            if ( getResult2($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 変更
                $query = sprintf("UPDATE material_cost_summary SET assy_no='%s', plan_no='%s', sum_price=%01.2f, m_time=%01.3f,
                                a_time=%01.3f, g_time=%01.3f, m_price=%01.2f, a_price=%01.2f, g_price=%01.2f,
                                m_place='%s', hgkkk='%s', hg_date='%s', entry_date='%s', hg_ym=%d, entry_year=%d",
                                $assy_no, $plan_no, $sum_price, $m_time, $a_time, $g_time, $m_price, $a_price,
                                $g_price, $m_place, $hgkkk, $hg_date, $entry_date, $hg_ym, $entry_year);
                $query .= sprintf(" WHERE plan_no='%s' and assy_no='%s'",
                                    $plan_no, $assy_no);
                if (query_affected($query) <= 0) {
                    //$_SESSION['s_sysmsg'] .= "{$parts_no}：工程{$pro_no}の変更に失敗！";    // .= に注意
                    //$msg_flg = 'alert';
                } else {
                    //$_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程{$pro_no}を変更しました！</font>";    // .= に注意
                }
                //$request->del('entry');   // UPDATEの場合はページを維持するため entry を削除して
            } else {                                    //////// 登録なし INSERT 新規
                /*****
                if (substr($plan_no, 0, 2) == 'ZZ') {
                    $query = sprintf("INSERT INTO material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                                    par_parts, pro_price, pro_num, intext, regdate, last_date, last_user)
                                  VALUES ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, '2007-10-06 00:00:00', CURRENT_TIMESTAMP, '%s')",
                                    $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price,
                                    $pro_num, $intext, $_SESSION['User_ID']);
                } else {
                }
                *****/
                $query = sprintf("INSERT INTO material_cost_summary (assy_no, plan_no, sum_price, m_time, a_time, g_time,
                                m_price, a_price, g_price, m_place, hgkkk, hg_date, entry_date, hg_ym, entry_year)
                            VALUES ('%s', '%s', %01.2f, %01.3f, %01.3f, %01.3f, %01.2f, %01.2f, %01.2f, '%s', '%s', '%s', '%s', %d, %d)",
                                $assy_no, $plan_no, $sum_price, $m_time, $a_time, $g_time, $m_price, $a_price,
                                $g_price, $m_place, $hgkkk, $hg_date, $entry_date, $hg_ym, $entry_year);
                if (query_affected($query) <= 0) {
                    //$_SESSION['s_sysmsg'] .= "{$parts_no}：工程{$pro_no}の追加に失敗！";    // .= に注意
                    ///////////////////////////////////// debug ADD 2005/05/27
                    //$fp_error = fopen($error_log_name, 'a');   // エラーログへの書込みでオープン
                    //$log_msg  = date('Y-m-d H:i:s');
                    //$log_msg .= " エラーの時の SQL 文は以下 \n";
                    //fwrite($fp_error, $log_msg);
                    //fwrite($fp_error, $query);
                    //fclose($fp_error);
                    ///////////////////////////////////// debug END
                    //$msg_flg = 'alert';
                } else {
                    //$_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程{$pro_no}を追加しました！</font>";    // .= に注意
                }
                    //$request->del('entry');   // INSERTの場合も(2006/02/27)ページを維持するため entry を削除して
            }
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . $menu->out_retGET());  // 直前の呼出元へ帰る
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} は組立費が未登録です。先に登録して下さい！";    // .= に注意
        $msg_flg = 'alert';
    }
}

//////////// 一括削除ボタンが押された時
if ($request->get('all_del') != '') {
    while (1) {
        if ( !($con = funcConnect()) ) {
            $_SESSION['s_sysmsg'] .= "データベースに接続できません！ 担当者へ連絡して下さい。";   // .= に注意
            $msg_flg = 'alert';
            break;
        }
        query_affected_trans($con, 'begin');    // トランザクションスタート
        /******** ヘッダー headerの削除 *********/
        $query = "DELETE FROM material_cost_header WHERE plan_no='{$plan_no}'";
        if (query_affected_trans($con, $query) < 0) {   // 0件削除はOKにするに注意
            query_affected_trans($con, 'rollback');     // ロールバック
            $_SESSION['s_sysmsg'] .= "ヘッダーファイルの削除でエラーが発生しました！ 担当者へ連絡して下さい。";   // .= に注意
            $msg_flg = 'alert';
            break;
        }
        /******** 明細 historyの削除 *********/
        $query = "DELETE FROM material_cost_history WHERE plan_no='{$plan_no}'";
        if ( ($del_rec = query_affected_trans($con, $query)) < 0) {   // 0件削除はOKにするに注意
            query_affected_trans($con, 'rollback');     // ロールバック
            $_SESSION['s_sysmsg'] .= "明細ファイルの削除でエラーが発生しました！ 担当者へ連絡して下さい。";   // .= に注意
            $msg_flg = 'alert';
            break;
        }
        query_affected_trans($con, 'commit');     // コミット
        $_SESSION['s_sysmsg'] .= "{$del_rec}点の部品を一括削除しました。";   // .= に注意
        $msg_flg = 'alert';
        break;
    }
}

////////////// 組立費の登録・変更ロジック (ロジック位置の指定はない)
if (isset($_POST['assy_reg'])) {
    $m_time = $request->get('m_time');
    $m_rate = $request->get('m_rate');
    $a_time = $request->get('a_time');
    $a_rate = $request->get('a_rate');
    $g_time = $request->get('g_time');
    $g_rate = $request->get('g_rate');
    $s_rate = $request->get('s_rate');
    $assy_time = ($m_time + $a_time + $g_time);
    ////////// 登録済みのチェック
    $query = "SELECT plan_no FROM material_cost_header WHERE plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {      ///// 登録済 UPDATE
        $query = sprintf("UPDATE material_cost_header SET
                            m_time=%01.3f, m_rate=%01.2f,
                            a_time=%01.3f, a_rate=%01.2f,
                            g_time=%01.3f, g_rate=%01.2f,
                            assy_time=%01.3f, assy_rate=%01.2f,
                            last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                            WHERE plan_no='{$plan_no}'",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "組立費→計画番号：{$plan_no} の変更に失敗！";    // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>組立費→計画番号：{$plan_no} を変更しました</font>";
        }
    } else {                                        ///// 未登録 INSERT
        /*****
        if (substr($plan_no, 0, 2) == 'ZZ') {
            $query = sprintf("INSERT INTO material_cost_header
                            (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, regdate, last_date, last_user)
                            VALUES ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, '2007-10-06 00:00:00', CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
        } else {
        }
        *****/
        $query = sprintf("INSERT INTO material_cost_header
                        (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, last_date, last_user)
                        VALUES ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, $s_rate);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} で組立費の追加に失敗！";    // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>計画番号：{$plan_no} の組立費を追加しました</font>";
        }
    }
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css()?>
</head>
<body style='overflow-y:hidden;'>
<?php
if ($msg_flg == 'alert') {
    echo "<iframe hspace='0' tabindex='21' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewHeader.php?msg_flg=1&{$uniq}' name='header' align='center' width='100%' height='114' title='項目'>\n";
} else {
    echo "<iframe hspace='0' tabindex='21' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewHeader.php?{$uniq}' name='header' align='center' width='100%' height='114' title='項目'>\n";
}
echo "    項目を表示しています。\n";
echo "</iframe>\n";
if ($request->get('entry') != '') {    //登録・変更の場合マーカー用に部品番号と工程番号と親部品を
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?mark=1", $_SERVER['QUERY_STRING'], "&parts_no=", $parts_no ,"&pro_mark=", $pro_mark ,"&par_parts=", $par_parts ,"&par_parts=", $par_parts , "&{$uniq}#mark' name='list' align='center' width='100%' height='40%' title='一覧'>\n";
} else if ($request->get('del') != '') {    //削除の場合マーカー用に削除したNoを
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?c_mark=1", $_SERVER['QUERY_STRING'], "&c_number=", $c_number, "&{$uniq}&{$msg_flg}#mark' name='list' align='center' width='100%' height='40%' title='一覧'>\n";
} else if ($request->get('no_del') != '') {
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?no_del_mark=1", $_SERVER['QUERY_STRING'], "&no_del_num=", $no_del_num, "&{$uniq}&{$msg_flg}#mark' name='list' align='center' width='100%' height='40%' title='一覧'>\n";
} else {
    echo "<iframe hspace='0' tabindex='19' vspace='0' frameborder='0' scrolling='yes' src='materialCost_entry_ViewBody.php?", $_SERVER['QUERY_STRING'], "&{$uniq}&{$msg_flg}#mark' name='list' align='center' width='100%' height='40%' title='一覧'>\n";
}
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' tabindex='20' vspace='0' frameborder='0' scrolling='yes' src='". $menu->out_frame('登録フッター') ."?{$uniq}' name='footer' align='center' width='100%' height='43%' title='フッター'>\n";
echo "    フッターを表示しています。\n";
echo "</iframe>\n";
?>
        
</center>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END    
?>