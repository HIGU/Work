#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 製品グループコード 自動FTP Download cron で処理用       コマンドライン版 //
// AS/400 ----> Web Server (PHP)                                            //
// AS UKSLIB/QCLSRC \TNKDAILYCのLOOPの前に以下を登録すること                //
// SNDF       RCDFMT(TITLE)                                                 //
// SNDF       RCDFMT(MSHMAS)                                                //
// RUNQRY     QRY(UKPLIB/Q#MSHMAS)                                          //
// Copyright (C) 2009-2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed histoy                                                           //
// 2009/11/19 Created  product_code_get_ftp.php                             //
// 2009/12/25 FTPで直接データを取得するように変更                           //
// 2009/12/28 コメントにASへの組み込みを追加(ASへの組込みは未実施)          //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加            //
// 2010/01/20 $log_dateの前後は'ではなく"なので修正                         //
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
fwrite($fpb, "製品グループコードの更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/product_code_get_ftp.php \n");

// FTPのターゲットファイル
define('MSHMAS', 'UKWLIB/W#MSHMAS');        // 製品グループコードファイル
define('W_MSHMAS', '/home/www/html/tnk-web/system/backup/W#MSHMAS.TXT');  // 製品グループコードのDownloadファイル

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        /*** 製品グループコードデータ ***/
        if (ftp_get($ftp_stream, W_MSHMAS, MSHMAS, FTP_ASCII)) {
            echo "$log_date 製品グループコード ftp_get download OK ", MSHMAS, "→", W_MSHMAS, "\n";
            fwrite($fpa,"$log_date 製品グループコード ftp_get download OK " . MSHMAS . '→' . W_MSHMAS . "\n");
            fwrite($fpb,"$log_date 製品グループコード ftp_get download OK " . MSHMAS . '→' . W_MSHMAS . "\n");
        } else {
            echo "$log_date 製品グループコード ftp_get() error ", MSHMAS, "\n";
            fwrite($fpa,"$log_date 製品グループコード ftp_get() error " . MSHMAS . "\n");
            fwrite($fpb,"$log_date 製品グループコード ftp_get() error " . MSHMAS . "\n");
        }
    } else {
        echo "$log_date 製品グループコード ftp_login() error \n";
        fwrite($fpa,"$log_date 製品グループコード ftp_login() error \n");
        fwrite($fpb,"$log_date 製品グループコード ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> 製品グループコード\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 製品グループコード\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 製品グループコード\n");
}

/////// 処理報告用 変数 初期化\
$flag1 = '';        // 処理実行フラグ 売上
$flag2 = '';        // 処理実行フラグ アイテム
$flag3 = '';        // 処理実行フラグ 製品仕掛
$flag4 = '';        // 処理実行フラグ 労務費・経費
$b     = 0;         // テキストファイルのレコード数
$c     = 0;
$d     = 0;
$e     = 0;

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // 大量登録用にはコメントアウト
} else {
    fwrite($fpa, "$log_date 製品グループコード db_connect() error \n");
    fwrite($fpb, "$log_date 製品グループコード db_connect() error \n");
    echo "$log_date 製品グループコード db_connect() error \n";
    exit();
}

// 製品グループコード 日報処理
$file_name = '/home/www/html/tnk-web/system/backup/W#MSHMAS.TXT';
$file_name_bak = '/home/www/html/tnk-web/system/backup/W#MSHMAS-bak.txt';
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $rec_ok = 0;        // 成功数カウント
    $mshmas_ng_flg = FALSE;      // ＤＢ書込みＮＧフラグ
    if ( ($fp = fopen($file_name, 'r')) ) {
        while ($data = fgetcsv($fp, 200, "_")) {
            if ($data[0] == '') continue;   // 空行の処理
            // $num = count($data);     // CSV File の field 数
            $rowcsv++;
            $data[1] = addslashes($data[1]);    // "'"等がデータにある場合に\でエスケープする
            $data[1] = trim($data[1]);          // 部品名の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
            ///////// 登録済みのチェック
            $query_chk = sprintf("select mipn from mshmas where mipn='%s'", $data[0]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                ///// 登録なし insert 使用
                $query = sprintf("insert into mshmas (mipn, mhscd, mhjcd, mhshc)
                        values('%s','%s','%s','%s')", $data[0],$data[1],$data[2],$data[3]);
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    echo "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n");
                    $mshmas_ng_flg = TRUE;
                    break;          // NG のため抜ける
                } else {
                    $row_in++;      // insert 成功
                    $rec_ok++;      // 成功数カウント
                }
            } else {
                ///// 登録あり update 使用
                $query = sprintf("update mshmas set mipn='%s', mhscd='%s', mhjcd='%s', mhshc='%s'
                        where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[0]);
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    echo "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n";
                    fwrite($fpa, "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n");
                    fwrite($fpb, "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n");
                    $mshmas_ng_flg = TRUE;
                    break;          // NG のため抜ける
                } else {
                    $row_up++;      // update 成功
                    $rec_ok++;      // 成功数カウント
                }
            }
        }
    } else {
        echo "W#MSHMAS.txtをオープン出来ません\n";
        fwrite($fpa,"".$rowcsv."W#MSHMAS.txtをオープン出来ません\n");
        fwrite($fpb,"".$rowcsv."W#MSHMAS.txtをオープン出来ません\n");
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
    echo "$log_date 製品グループコードの更新: $rec_ok/$rowcsv 件登録しました。\n";
    echo "$log_date 製品グループコードの更新: {$row_in}/{$rowcsv} 件 追加 \n";
    echo "$log_date 製品グループコードの更新: {$row_up}/{$rowcsv} 件 変更 \n";
    fwrite($fpa, "$log_date 製品グループコードの更新: $rec_ok/$rowcsv 件登録しました。\n");
    fwrite($fpa, "$log_date 製品グループコードの更新: {$row_in}/{$rowcsv} 件 追加 \n");
    fwrite($fpa, "$log_date 製品グループコードの更新: {$row_up}/{$rowcsv} 件 変更 \n");
    fwrite($fpb, "$log_date 製品グループコードの更新: $rec_ok/$rowcsv 件登録しました。\n");
    fwrite($fpb, "$log_date 製品グループコードの更新: {$row_in}/{$rowcsv} 件 追加 \n");
    fwrite($fpb, "$log_date 製品グループコードの更新: {$row_up}/{$rowcsv} 件 変更 \n");
} else {
    echo "{$log_date} 製品グループコードの更新データがありません。\n";
    fwrite($fpa, "$log_date 製品グループコードの更新データがありません。\n");
    fwrite($fpb, "$log_date 製品グループコードの更新データがありません。\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

?>
