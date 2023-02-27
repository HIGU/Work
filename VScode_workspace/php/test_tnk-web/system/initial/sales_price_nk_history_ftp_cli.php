#!/usr/local/bin/php-5.0.2-cli
<?php
//////////////////////////////////////////////////////////////////////////////
// 日東工器の仕切単価ファイル 栃木の売上単価 経歴 自動FTP Download 用 cli版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/29 Created  sales_price_nk_history_ftp_cli.php                   //
//                                      重複キーがあるため追加のみで対応    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#HGUSI@L';      // 仕切単価最新ファイル download file 経歴の場合はW#HGUSI@L
// 保存先のディレクトリとファイル名
$save_file = '/home/guest/daily/W#HGUSI.TXT';      // 仕切単価最新ファイル save file

/********************************************
// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 注文書発行ファイル
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo 'ftp_get download 成功 ', $target_file, '→', $save_file, "\n";
            fwrite($fpa,"$log_date ftp_get download 成功 " . $target_file . '→' . $save_file . "\n");
        } else {
            echo 'ftp_get() error ', $target_file, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> 仕切単価F\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 仕切単価F\n");
}
********************************************/



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
// 注文書発行ファイル 日報処理 準備作業
$file_orign  = $save_file;
$file_debug  = '/home/guest/daily/debug/debug-MGUSI.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // 実レコードは75バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 9) {    // 実際には 9 あり(最後がない場合があるため)
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 20 record=$rec \n");
                echo "$log_date field not 20 record=$rec \n";
            }
           continue;
        }
        $data[6] = trim($data[6]);    // 備考の無駄な余白を削除
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        /*************************************  重複キーがあるため追加のみで対応
        $query_chk = sprintf("SELECT parts_no FROM sales_price_nk_history
                                WHERE parts_no='%s' and regdate=%d",
                                $data[0], $data[3]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
        *************************************/
            ///// 登録なし insert 使用
            $query = "INSERT INTO sales_price_nk_history
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                      '{$data[2]}',
                       {$data[3]} ,
                      '{$data[4]}',
                       {$data[5]} ,
                      '{$data[6]}',
                      '{$data[7]}')
                      -- {$data[8]} ) 今回は取込まない
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        /*************************************
        } else {
            ///// 登録あり update 使用
            $query = "UPDATE sales_price_nk_history SET
                            parts_no    ='{$data[0]}',
                            price       = {$data[1]} ,
                            nk_kubun    ='{$data[2]}',
                            regdate     = {$data[3]} ,
                            div         ='{$data[4]}',
                            lot         = {$data[5]} ,
                            note        ='{$data[6]}',
                            reg_kubun   ='{$data[7]}'
                            -- mushou      = {$data[8]}
                WHERE parts_no='{$data[0]}' and regdate={$data[3]}
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 製品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $upd_ok++;
            }
        }
        *************************************/
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 仕切単価履歴の更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 仕切単価履歴の更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 仕切単価履歴の更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 仕切単価履歴の更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 仕切単価履歴の更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 仕切単価履歴の更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
?>
