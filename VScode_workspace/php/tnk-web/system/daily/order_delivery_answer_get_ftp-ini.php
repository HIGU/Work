#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 購買納期回答 AS/400<-->TNKサーバー同期 初回 処理用                       //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright(C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed histoy                                                           //
// 2007/04/26 Created   order_delivery_answer_get_ftp-ini.php               //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版は必要ない
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// ログファイルへの書込みでオープン

// 排他制御用コントロールファイル
define('CHIDLIV', 'UKWLIB/C#HIDLIV');   // 購買納期回答コントロール
// 保存先のディレクトリとファイル名
define('C_HIDLIV', '/var/www/html/system/backup/C#HIDLIV.TXT');  // 購買納期回答コントロール

// ターゲットファイル
define('HIDLIV', 'UKWLIB/W#HIDLIV');    // 購買納期回答ファイル
// 保存先のディレクトリとファイル名
define('W_HIDLIV', '/var/www/html/system/backup/W#HIDLIV.TXT');  // 購買納期回答
// AS/400のファイルを空にするためのダミーファイル名
define('LOCAL_FILE', '/var/www/html/system/backup/W#HIDLIV-clear.TXT');


/////////// begin トランザクション開始
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');        ///// ログの日時
    fwrite($fpa,"$log_date db_connect() error \n");
    exit();
}
// 同期処理 準備作業
if (file_exists(W_HIDLIV)) {         // ファイルの存在チェック
    $fp = fopen(W_HIDLIV, 'r');
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    while (1) {
        $data = fgetcsv($fp, 50, ',');     // 実レコードは31バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 4) continue;     // フィールド数のチェック
        if (!$data[0]) continue;    // 計画オーダー(製造番号)が0のものがあるのでチェック
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
        }
        
        $query_chk = "
            SELECT * FROM order_delivery_answer
            WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO order_delivery_answer (sei_no, order_no, vendor, delivery)
                VALUES({$data[0]}, {$data[1]}, '{$data[2]}', {$data[3]})
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date 購買納期回答 : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":レコード目の書込みに失敗しました!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        } else {
            ///// 登録あり update 使用
            $query = "
                UPDATE order_delivery_answer SET sei_no={$data[0]}, order_no={$data[1]}, vendor='{$data[2]}',
                    delivery={$data[3]}
                WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date 購買納期回答 : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":レコード目のUPDATEに失敗しました!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        }
    }
    fclose($fp);
    $log_date = date('Y-m-d H:i:s');        ///// ログの日時
    fwrite($fpa, "$log_date 購買納期回答 : $rec_ok/$rec 件登録しました。\n");
    
} else {
    fwrite($fpa, "$log_date ファイル " . W_HIDLIV . " がありません!\n");
}


fclose($fpa);      ////// 日報用ログ書込み終了
/////////// commit トランザクション終了
// query_affected_trans($con, 'COMMIT');



function checkControlFile($fpa, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // 実レコードは11バイトなのでちょっと余裕を
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        fwrite($fpa, "$log_date 購買納期回答 : 使用端末は {$data}");
        return true;
    }
}
?>
