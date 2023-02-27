#!/usr/local/bin/php-4.3.8-cgi -q
<?php
//////////////////////////////////////////////////////////////////////////////
// 日報データ(製造用部品マスター) 自動FTP Download cronで処理用 cgi版       //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// 変更経歴                                                                 //
// 2004/05/28 新規作成 production_parts_master_cgi.php                      //
// 2004/06/07 php-4.3.6-cgi -q → php-4.3.7-cgi -q  バージョンアップに伴う  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);    // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル
define('D_MIITEM', 'UKWLIB/W#MIITE');       // ITEMファイル download file
// 保存先のディレクトリとファイル名
define('S_MIITEM', '/home/guest/daily/W#MIITEM.TXT');  // ITEMファイル save file

/************************************************
// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 単価経歴ファイル
        if (ftp_get($ftp_stream, S_MIITEM, D_MIITEM, FTP_ASCII)) {
            echo 'ftp_get download 成功 ', D_MIITEM, '→', S_MIITEM, "\n";
            fwrite($fpa,"$log_date ftp_get download 成功 " . D_MIITEM . '→' . S_MIITEM . "\n");
        } else {
            echo 'ftp_get() error ', D_MIITEM, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . D_MIITEM . "\n");
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> 日報処理\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 日報処理\n");
}
************************************************/



// 製造用部品マスターファイル 日報処理 準備作業
$file_orign  = S_MIITEM;
$file_debug  = 'debug/debug-MIITEM.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 50, '_');     // 実レコードは13バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 3) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 3 record=$rec \n");
                echo "$log_date field not 3 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
        $query_chk = sprintf("SELECT parts_no FROM production_parts_master
                                WHERE parts_no='%s'", $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO production_parts_master (parts_no, midiv, miccc)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}')";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
        } else {
            ///// 登録あり update 使用
            $query = "UPDATE production_parts_master SET
                            parts_no    ='{$data[0]}',
                            midiv       ='{$data[1]}',
                            miccc       ='{$data[2]}'
                WHERE parts_no='{$data[0]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
/////////// commit トランザクション終了
query_affected_trans($con, "commit");
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 製造用部品の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 製造用部品の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 製造用部品の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 製造用部品の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 製造用部品の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 製造用部品の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
?>
