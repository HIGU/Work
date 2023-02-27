#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 部品単価レート経歴マスター自動FTP Download cron で処理用コマンドライン版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2013-2013 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// AS UKSLIB/QCLSRC \TNKDAILYCのLOOPの前に以下を登録すること                //
// SNDF       RCDFMT(TITLE)                                                 //
// SNDF       RCDFMT(TANRATE)                                               //
// RUNQRY     QRY(UKPLIB/Q#TANRATE)                                         //
// \FTPTNK    USER(AS400) ASFILE(W#TANRATE) PCFILE(W#TANRATE.CSV) MODE(CSV) //
// Changed histoy                                                           //
// 2013/05/27 Created  parts_rate_history_update_ftp.php                    //
// 2013/06/05 ASのプログラムが間違っていたので訂正                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "部品単価レート経歴の更新\n");
fwrite($fpb, "/var/www/html/system/daily/parts_rate_history_update_ftp.php \n");

/////// 処理報告用 変数 初期化
$flag1 = '';        // 処理実行フラグ 売上
$flag2 = '';        // 処理実行フラグ アイテム
$flag3 = '';        // 処理実行フラグ 製品仕掛
$flag4 = '';        // 処理実行フラグ 労務費・経費
$b     = 0;         // テキストファイルのレコード数
$c     = 0;
$d     = 0;
$e     = 0;

// 単価レート区分 日報処理 準備作業
$file_name  = '/home/guest/daily/W#TANRATE.CSV';
$file_temp  = '/home/guest/daily/Q#TANRATE.tmp';
$file_write = '/home/guest/daily/Q#TANRATE.txt';

///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'KV', 'UTF-8'); // 半角カナを全角カナに変換 (DB保存時は全角で照会時は必要に応じて半角変換する)
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
        //$data[2] = str_replace('"', '', $data[2]);  // なぜか？"の入る位置がズレるのと￥まで書込まれるので削除する
                                                    // 上記は下のpg_escape_string()以前の問題である
        //$data[2] = pg_escape_string($data[2]);      // 名称
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\n");
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

// 部品単価レート経歴 日報処理
$file_name = '/home/guest/daily/Q#TANRATE.txt';
$file_name_bak = '/home/guest/daily/Q#TANRATE-bak.txt';
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
            //$data[2] = addslashes($data[2]);    // "'"等がデータにある場合に\でエスケープする
            //$data[2] = trim($data[2]);          // 名称の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
            ///////// 登録済みのチェック
            $query_chk = sprintf("select rate_div from parts_rate_history where parts_no='%s' and reg_no='%s'", $data[0],$data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                ///// 登録なし insert 使用
                $query = sprintf("insert into parts_rate_history (parts_no,reg_no,rate_div)
                        values('%s','%s','%s')", $data[0],$data[1],$data[2]);
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
                $query = sprintf("update parts_rate_history set rate_div='%s'
                        where parts_no='%s' and reg_no='%s'", $data[2], $data[0], $data[1]);
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
        echo "Q#TANRATE.txtをオープン出来ません\n";
        fwrite($fpa,"".$rowcsv."W#TANRATE.txtをオープン出来ません\n");
        fwrite($fpb,"".$rowcsv."W#TANRATE.txtをオープン出来ません\n");
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
    echo "$log_date 部品単価レート経歴の更新: $rec_ok/$rowcsv 件登録しました。\n";
    echo "$log_date 部品単価レート経歴の更新: {$row_in}/{$rowcsv} 件 追加 \n";
    echo "$log_date 部品単価レート経歴の更新: {$row_up}/{$rowcsv} 件 変更 \n";
    fwrite($fpa, "$log_date 部品単価レート経歴の更新: $rec_ok/$rowcsv 件登録しました。\n");
    fwrite($fpa, "$log_date 部品単価レート経歴の更新: {$row_in}/{$rowcsv} 件 追加 \n");
    fwrite($fpa, "$log_date 部品単価レート経歴の更新: {$row_up}/{$rowcsv} 件 変更 \n");
    fwrite($fpb, "$log_date 部品単価レート経歴の更新: $rec_ok/$rowcsv 件登録しました。\n");
    fwrite($fpb, "$log_date 部品単価レート経歴の更新: {$row_in}/{$rowcsv} 件 追加 \n");
    fwrite($fpb, "$log_date 部品単価レート経歴の更新: {$row_up}/{$rowcsv} 件 変更 \n");
} else {
    echo "{$log_date} 部品単価レート経歴の更新データがありません。\n";
    fwrite($fpa, "$log_date 部品単価レート経歴の更新データがありません。\n");
    fwrite($fpb, "$log_date 部品単価レート経歴の更新データがありません。\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

?>
