#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 発注計画F のAS/400との完全データリンクのため CHECK DATA DOWNLOAD   CLI版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/17 Created  order_plan_get_ftp2.php                              //
//                                      allocated_parts_ftp2.php を元に作成 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 (CLI版は必要なし）
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル2
define('D_MIOPLN', 'UKWLIB/W#MIOPLD');      // 発注計画ファイル download file
// 保存先のディレクトリとファイル名
define('S_MIOPLN', '/home/www/html/tnk-web/system/backup/W#MIOPLD.TXT');  // 発注計画ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 発注計画ファイル
        if (ftp_get($ftp_stream, S_MIOPLN, D_MIOPLN, FTP_ASCII)) {
            // echo 'ftp_get download OK ', D_MIOPLN, '→', S_MIOPLN, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . D_MIOPLN . '→' . S_MIOPLN . "\n");
        } else {
            // echo 'ftp_get() error ', D_MIOPLN, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . D_MIOPLN . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> 発注計画の差異データ処理\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 発注計画の差異データ処理\n");
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    exit();
}
// 発注計画ファイル 発注計画の差異データ処理 準備作業
$file_orign  = S_MIOPLN;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MIOPLD.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_ok = 0;    // DELETE用カウンター
    $del_old = 0;   // 削除済用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // 実レコードは54バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 7) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 7 record=$rec \n");
                // echo "$log_date field not 11 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        ////////// ステータスチェック (C=変更分 D=削除分) (今回は A=追加分 はなし)
        if ($data[6] == 'C') {
            $query_chk = sprintf("SELECT parts_no FROM order_plan WHERE sei_no=%s", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                ///// 登録なし なにもしない
            } else {
                ///// 登録あり update 使用
                $zan_q = ($data[3] - $data[4] - $data[5]);  // あえて注残は計算で出す
                $query = "UPDATE order_plan SET
                                order_q     = {$data[3]} ,
                                utikiri     = {$data[4]} ,
                                nyuko       = {$data[5]} ,
                                zan_q       = {$zan_q}    
                    WHERE sei_no={$data[0]}";
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                    // echo "$log_date 製造番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
        } elseif ($data[6] == 'D') {    //////// D=削除分の処理
            $query_chk = sprintf("SELECT parts_no FROM order_plan WHERE sei_no=%s", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // トランザクション内での 照会専用クエリー
                ////// 登録あり 削除実行
                $query_del = sprintf("DELETE FROM order_plan WHERE sei_no=%s", $data[0]);
                if ( ($del_num = query_affected_trans($con, $query_del)) != 1) {  // 更新用クエリーの実行
                    fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}：削除レコード数{$del_num}:レコード目のDELETEに失敗しました!\n");
                    // echo "$log_date 製造番号:{$data[0]} : {$rec}：削除レコード数{$del_num}:レコード目のDELETEに失敗しました!\n";
                    // query_affected_trans($con, "rollback");     // transaction rollback
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    for ($f=0; $f<$num; $f++) {
                        fwrite($fpw,"'{$data[$f]}',");      // debug
                    }
                    fwrite($fpw,"\n");                      // debug
                    fwrite($fpw, "$query_del \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $del_ok++;
                }
            } else {
                $del_old++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 発注計画の差異データ更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 発注計画の差異データ更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 発注計画の差異データ更新 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 発注計画の差異データ更新 : {$del_ok}/{$rec} 件 削除 \n");
    fwrite($fpa, "$log_date 発注計画の差異データ更新 : {$del_old}/{$rec} 件 削除済 \n");
    // echo "$log_date 発注計画の差異データ更新 : $rec_ok/$rec 件登録しました。\n";
    // echo "$log_date 発注計画の差異データ更新 : {$ins_ok}/{$rec} 件 追加 \n";
    // echo "$log_date 発注計画の差異データ更新 : {$upd_ok}/{$rec} 件 変更 \n";
    // echo "$log_date 発注計画の差異データ更新 : {$del_ok}/{$rec} 件 削除 \n";
    // echo "$log_date 発注計画の差異データ更新 : {$del_old}/{$rec} 件 削除済 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    // echo "{$log_date}: file:{$file_orign} がありません!\n";
}
$query_chk = sprintf("SELECT sei_no FROM order_plan WHERE plan_cond='R'");
$res=array();
if($rows=getResult($query_chk,$res)){
    for($i=0;$i<$rows;$i++){
        $del_chk = sprintf("SELECT * FROM order_plan WHERE sei_no=%s AND plan_cond='O'", $res[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
        } else {
            
        }
    }
}

/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// 発注計画の差異データ用ログ書込み終了
?>
