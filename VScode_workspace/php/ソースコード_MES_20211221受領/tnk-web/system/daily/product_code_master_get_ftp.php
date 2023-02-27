#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 製品グループコードマスター自動FTP Download cron で処理用コマンドライン版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2009-2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// AS UKSLIB/QCLSRC \TNKDAILYCのLOOPの前に以下を登録すること                //
// SNDF       RCDFMT(TITLE)                                                 //
// SNDF       RCDFMT(MSHGNM)                                                //
// RUNQRY     QRY(UKPLIB/Q#MSHGNM)                                          //
// \FTPTNK    USER(AS400) ASFILE(W#MSHGNM) PCFILE(Q#MSHGNM.CSV) MODE(CSV)   //
// Changed histoy                                                           //
// 2009/11/19 Created  product_code_master_get_ftp.php                      //
// 2009/12/25 FTPで直接データを取得するように変更                           //
// 2009/12/28 FTPで直接データを取得すると文字化けするのでCSV取得に戻す  大谷//
// 2010/01/15 ASから出力するCSVをW#にしてしまったのでこちらを変更       大谷//
// 2011/05/31 表示はMSHGNMだがASのファイルを実際はMSSHG3に変更          大谷//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "製品グループコードマスターの更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/product_code_master_get_ftp.php \n");

//////////////////////////// 文字化けする為FTP接続は使用しない
// FTPのターゲットファイル
//define('MSHGNM', 'UKWLIB/W#MSHGNM');        // 製品グループコードマスターファイル
//define('W_MSHGNM', '/home/www/html/tnk-web/system/backup/W#MSHGNM.TXT');  // 製品グループコードマスターのDownloadファイル

// コネクションを取る(FTP接続のオープン)
//if ($ftp_stream = ftp_connect(AS400_HOST)) {
//    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
//        /*** 製品グループコードマスターデータ ***/
//        if (ftp_get($ftp_stream, W_MSHGNM, MSHGNM, FTP_ASCII)) {
//            echo 'ftp_get download OK ', MSHGNM, '→', W_MSHGNM, "\n";
//            fwrite($fpa,"$log_date ftp_get download OK " . MSHGNM . '→' . W_MSHGNM . "\n");
//            fwrite($fpb,"$log_date ftp_get download OK " . MSHGNM . '→' . W_MSHGNM . "\n");
//        } else {
//            echo 'ftp_get() error ', MSHGNM, "\n";
//            fwrite($fpa,"$log_date ftp_get() error " . MSHGNM . "\n");
//            fwrite($fpb,"$log_date ftp_get() error " . MSHGNM . "\n");
//        }
//    } else {
//        echo "ftp_login() error \n";
//        fwrite($fpa,"$log_date ftp_login() error \n");
//        fwrite($fpb,"$log_date ftp_login() error \n");
//    }
//    ftp_close($ftp_stream);
//} else {
//    echo "ftp_connect() error --> 製品グループコードマスター\n";
//    fwrite($fpa,"$log_date ftp_connect() error --> 製品グループコードマスター\n");
//    fwrite($fpb,"$log_date ftp_connect() error --> 製品グループコードマスター\n");
//}

/////// 処理報告用 変数 初期化
$flag1 = '';        // 処理実行フラグ 売上
$flag2 = '';        // 処理実行フラグ アイテム
$flag3 = '';        // 処理実行フラグ 製品仕掛
$flag4 = '';        // 処理実行フラグ 労務費・経費
$b     = 0;         // テキストファイルのレコード数
$c     = 0;
$d     = 0;
$e     = 0;

// 製品グループコード 日報処理 準備作業
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
$file_name  = '/home/guest/daily/W#MSHGNM.CSV';
$file_temp  = '/home/guest/daily/Q#MSHGNM.tmp';
$file_write = '/home/guest/daily/Q#MSHGNM.txt';

///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'KV', 'EUC-JP'); // 半角カナを全角カナに変換 (DB保存時は全角で照会時は必要に応じて半角変換する)
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp, 'r');
    $fpw = fopen($file_write, 'a');
    while (FALSE !== ($data = fgetcsv($fp, 300, ',')) ) {    // CSV file として読込み
        if ($data[0] == '') continue;   // 空行の処理
        $data[1] = str_replace('"', '', $data[1]);  // なぜか？"の入る位置がズレるのと￥まで書込まれるので削除する
                                                    // 上記は下のpg_escape_string()以前の問題である
        $data[1] = pg_escape_string($data[1]);      // 品名
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        fwrite($fpw,"{$data[0]}\t{$data[1]}\n");
        ///// 文字列内(品名等)に","があった場合は fgetcsv()にまかせる。
    }
    fclose($fp);
    fclose($fpw);
    // unlink($file_name);     // 一時ファイルを削除 CSV
    // unlink($file_temp);     // 一時ファイルを削除 tmp
    if (file_exists("{$file_name}.bak")) {
        unlink("{$file_name}.bak");         // 前回のデータを削除
    }
    if (file_exists("{$file_temp}.bak")) {
        unlink("{$file_temp}.bak");         // 前回のデータを削除
    }
    if (!rename($file_name, "{$file_name}.bak")) {
        echo "$log_date DownLoad File $file_name をBackupできません！\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        echo "$log_date DownLoad File $file_temp をBackupできません！\n";
    }
    // exit(); // debug用
}


/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // 大量登録用にはコメントアウト
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}

// 製品グループコードマスター 日報処理
$file_name = '/home/guest/daily/Q#MSHGNM.txt';
$file_name_bak = '/home/guest/daily/Q#MSHGNM-bak.txt';
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $rec_ok = 0;        // 成功数カウント
    $msshg3_ng_flg = FALSE;      // ＤＢ書込みＮＧフラグ
    if ( ($fp = fopen($file_name, 'r')) ) {
        while ($data = fgetcsv($fp, 200, "\t")) {
        // while ($data = fgetcsv($fp, 200, "_")) {     // FTP接続用
            if ($data[0] == '') continue;   // 空行の処理
            // $num = count($data);     // CSV File の field 数
            $rowcsv++;
            $data[1] = addslashes($data[1]);    // "'"等がデータにある場合に\でエスケープする
            $data[1] = trim($data[1]);          // 部品名の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
            ///////// 登録済みのチェック
            $query_chk = sprintf("select mhgcd from msshg3 where mhgcd='%s'", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                ///// 登録なし insert 使用
                $query = sprintf("insert into msshg3 (mhgcd, mhgnm)
                        values('%s','%s')", $data[0],$data[1]);
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    echo "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n");
                    $msshg3_ng_flg = TRUE;
                    break;          // NG のため抜ける
                } else {
                    $row_in++;      // insert 成功
                    $rec_ok++;      // 成功数カウント
                }
            } else {
                ///// 登録あり update 使用
                $query = sprintf("update msshg3 set mhgcd='%s', mhgnm='%s'
                        where mhgcd='%s'", $data[0], $data[1], $data[0]);
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    echo "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n");
                    $msshg3_ng_flg = TRUE;
                    break;          // NG のため抜ける
                } else {
                    $row_up++;      // update 成功
                    $rec_ok++;      // 成功数カウント
                }
            }
        }
    } else {
        echo "Q#MSHGNM.txtをオープン出来ません\n";
        fwrite($fpa,"".$rowcsv."W#MSHGNM.txtをオープン出来ません\n");
        fwrite($fpb,"".$rowcsv."W#MSHGNM.txtをオープン出来ません\n");
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if (file_exists($file_name_bak)) unlink($file_name_bak);    // 前回のバックアップを削除
    if (!rename($file_name, $file_name_bak)) {                  // 今回のデータをバックアップ
        echo "$log_date {$file_name} をBackupできません！\n";
        fwrite($fpa,"".$log_date." ".$file_name." をBackupできません！\n");
        fwrite($fpb,"".$log_date." ".$file_name." をBackupできません！\n");
    }
    $flag2 = 1;
}


// メッセージを返す
if ($flag2==1) {
    echo "$log_date 製品グループコードマスターの更新: $rec_ok/$rowcsv 件登録しました。\n";
    echo "$log_date 製品グループコードマスターの更新: {$row_in}/{$rowcsv} 件 追加 \n";
    echo "$log_date 製品グループコードマスターの更新: {$row_up}/{$rowcsv} 件 変更 \n";
    fwrite($fpa, "$log_date 製品グループコードマスターの更新: $rec_ok/$rowcsv 件登録しました。\n");
    fwrite($fpa, "$log_date 製品グループコードマスターの更新: {$row_in}/{$rowcsv} 件 追加 \n");
    fwrite($fpa, "$log_date 製品グループコードマスターの更新: {$row_up}/{$rowcsv} 件 変更 \n");
    fwrite($fpb, "$log_date 製品グループコードマスターの更新: $rec_ok/$rowcsv 件登録しました。\n");
    fwrite($fpb, "$log_date 製品グループコードマスターの更新: {$row_in}/{$rowcsv} 件 追加 \n");
    fwrite($fpb, "$log_date 製品グループコードマスターの更新: {$row_up}/{$rowcsv} 件 変更 \n");
} else {
    echo "{$log_date} 製品グループコードマスターの更新データがありません。\n";
    fwrite($fpa, "$log_date 製品グループコードマスターの更新データがありません。\n");
    fwrite($fpb, "$log_date 製品グループコードマスターの更新データがありません。\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

?>
