<?php
//////////////////////////////////////////////////////////////////////////////
// 緊急 部品 検査 依頼 登録プログラム  returnはheader()                     //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/19 Created  inspection_recourse_regist.php                       //
// 2004/10/28 谷口さんが抜けているのを修正                                  //
// 2004/11/20 削除時のメッセージが登録時のメッセージと同じなのを修正        //
// 2005/03/10 菅谷さんを許可 及び退社された人のメンテ                       //
// 2006/04/20 人の移動に伴う権限変更(深見・菊地・吉成・添田・五十嵐)        //
//            権限関係を共通 function へ変更 order_function.php             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む)
require_once ('order_function.php');        // order 関係の共通 function
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
// $menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
// $menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('予定明細', INDUST . 'order/order_detailes.php');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
// $menu->set_title('緊急部品検査依頼登録');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///////// パラメーターチェックと設定
if (isset($_REQUEST['order_seq'])) {
    $order_seq = $_REQUEST['order_seq'];    // 注文書の発行連番
} else {
    $order_seq = '';                        // 発行連番が無ければerror
}
///// 削除用パラメーター
if (isset($_REQUEST['del_order_seq'])) {
    $del_order_seq = $_REQUEST['del_order_seq'];    // 注文書の発行連番
} else {
    $del_order_seq = '';                            // 発行連番が無ければerror
}
if (isset($_REQUEST['retUrl'])) {
    $retUrl = $_REQUEST['retUrl'];         // リターンURL
} else {
    $retUrl = $_SERVER['HTTP_REFERER'];     // リターンURLが無い場合
}
if (isset($_SESSION['User_ID'])) {
    $uid = $_SESSION['User_ID'];            // 依頼登録ユーザー
    if ($uid == '') {
        $_SESSION['s_sysmsg'] = "社員番号がないので依頼は出来ません。管理担当者へ連絡して下さい。";
        header('location: ' . H_WEB_HOST . $retUrl);    // セッションデータが無い場合は強制リターン
    }
} else {
    header('location: ' . H_WEB_HOST . $retUrl);    // セッションデータが無い場合は強制リターン
}

// $uniq = 'id=' . uniqid('order');    // キャッシュ防止用ユニークID
/////////// クライアントのホスト名(又はIP Address)の取得
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
/////////// 本日を取得
$today = date('Ymd');
/////////// 緊急部品の登録ロジック
while ($order_seq != '') {
    if (!user_check($uid, 1)) break;
    $query = "select order_seq from inspection_recourse where order_seq = {$order_seq} limit 1";
    if (getUniResult($query, $check) <= 0) {
        $query = "select delivery, uke_date from order_data where order_seq = {$order_seq} limit 1";
        $res = array();
        if (getResult($query, $res) > 0) {
            $delivery = $res[0]['delivery'];
            $uke_date = $res[0]['uke_date'];
        } else {
            $delivery = 0;
            $uke_date = 0;
        }
        $priority = 100;
        ////////// INSERT
        if ($uke_date > 0) {
            if ($uke_date < $today) {
                $wantdate = "{$today} 170000";
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$today}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $priority = (100 + $cnt);
            } else {
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$uke_date}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $wantdate = "{$uke_date} 170000";
                $priority = (100 + $cnt);
            }
        } elseif ($delivery > 0) {
            if ($delivery < $today) {
                $wantdate = "{$today} 170000";
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$today}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $priority = (100 + $cnt);
            } else {
                $wantdate = "{$delivery} 170000";
                $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$delivery}'";
                if (getUniResult($query, $cnt) <= 0) $cnt = 0;
                $priority = (100 + $cnt);
            }
        } else {
            $wantdate = "{$today} 170000";
            $query = "select count(wantdate) from inspection_recourse where to_char(wantdate, 'YYYYMMDD') = '{$today}'";
            if (getUniResult($query, $cnt) <= 0) $cnt = 0;
            $priority = (100 + $cnt);
        }
        $insert = "insert into inspection_recourse (order_seq, uid, client, wantdate, priority) values({$order_seq}, '{$uid}', '{$hostName}', '{$wantdate}', $priority)";
        if (query_affected($insert) <= 0) {
            $_SESSION['s_sysmsg'] = '緊急部品の検査依頼を登録出来ませんでした。';
        } else {
            $_SESSION['s_sysmsg'] = '登録しました。';
        }
    } else {
        ////////// UPDATE はしない
        $_SESSION['s_sysmsg'] = '既に登録されています。';
    }
    break;
}
/////////// 緊急部品の削除ロジック
while ($del_order_seq != '') {
    if (!user_check($uid, 2)) break;
    $query = "select order_seq from inspection_recourse where order_seq = {$del_order_seq} limit 1";
    if (getUniResult($query, $check) > 0) {
        //////////// DELETE
        $delete = "delete from inspection_recourse where order_seq = {$del_order_seq}";
        if (query_affected($delete) <= 0) {
            $_SESSION['s_sysmsg'] = '緊急部品の検査依頼を削除出来ませんでした。';
        } else {
            $_SESSION['s_sysmsg'] = '削除しました。';
        }
    } else {
        $_SESSION['s_sysmsg'] = '登録されていません。';
    }
    break;
}
header('location: ' . H_WEB_HOST . $retUrl);    // 終了
?>
