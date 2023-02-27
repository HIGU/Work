#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// Web-EDI エラーログ 自動FTP Download cron で処理用       コマンドライン版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2010-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed histoy                                                           //
// 2010/03/30 Created  webedi_error_log_get_ftp.php                         //
// 2010/05/07 送信アドレスにYasuhiro_Maeda@nitto-kohki.co.jpを追加          //
//            （NK情報S部 前田）                                            //
// 2010/05/07 送信アドレスにkazumi_yoshinari@nitto-kohki.co.jpを追加        //
// 2016/04/13 送信アドレスにyoshimitsu_izawa@nitto-kohki.co.jpを追加        //
// 2017/06/09 送信アドレスからukobai@nitto-kohki.co.jpを削除                //
// 2019/03/19 送信アドレスから小森谷工場長を削除、中山課長、渋谷係長を追加  //
// 2020/02/28 送信アドレスにryota_waki@nitto-kohki.co.jpを追加              //
// 2020/04/20 送信アドレスに佐藤拓弥を追加、中山・前田を削除                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$log_name_a = '/tmp/edierror.log';
$fpa = fopen($log_name_a, 'w+');    ///// 全てのログ w=過去のログを消す
fwrite($fpa, "--------------------------------------------------------------------------------------------\n");
fwrite($fpa, "ＥＤＩシステム　データ交換エラーレポート（AS取込エラー） \n");
fwrite($fpa, "--------------------------------------------------------------------------------------------\n");
fwrite($fpa, "レコードエラーの為、ASで以下のデータが取り込めませんでした。\n");
fwrite($fpa, "ASの取込データを確認し対応を行ってください。\n");
fwrite($fpa, "\n");

// FTPのターゲットファイル
//define('F6ERRFP', 'UKFLIB/F6ERRFP');        // 製品グループコードファイル
//define('W_F6ERRFP', '/var/www/html/system/backup/W#F6ERRFP.TXT');  // 製品グループコードのDownloadファイル

// コネクションを取る(FTP接続のオープン)
//if ($ftp_stream = ftp_connect(AS400_HOST)) {
    //if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        /*** 製品グループコードデータ ***/
        //if (ftp_get($ftp_stream, W_F6ERRFP, F6ERRFP, FTP_ASCII)) {
            //echo "$log_date 製品グループコード ftp_get download OK ", F6ERRFP, "→", W_F6ERRFP, "\n";
            //fwrite($fpa,"$log_date 製品グループコード ftp_get download OK " . F6ERRFP . '→' . W_F6ERRFP . "\n");
            //fwrite($fpb,"$log_date 製品グループコード ftp_get download OK " . F6ERRFP . '→' . W_F6ERRFP . "\n");
        //} else {
            //echo "$log_date 製品グループコード ftp_get() error ", F6ERRFP, "\n";
            //fwrite($fpa,"$log_date 製品グループコード ftp_get() error " . F6ERRFP . "\n");
            //fwrite($fpb,"$log_date 製品グループコード ftp_get() error " . F6ERRFP . "\n");
        //}
    //} else {
        //echo "$log_date 製品グループコード ftp_login() error \n";
        //fwrite($fpa,"$log_date 製品グループコード ftp_login() error \n");
        //fwrite($fpb,"$log_date 製品グループコード ftp_login() error \n");
    //}
    //ftp_close($ftp_stream);
//} else {
    //echo "$log_date ftp_connect() error --> 製品グループコード\n";
    //fwrite($fpa,"$log_date ftp_connect() error --> 製品グループコード\n");
    //fwrite($fpb,"$log_date ftp_connect() error --> 製品グループコード\n");
//}

/////// 処理報告用 変数 初期化
//$flag1 = '';        // 処理実行フラグ 売上
$flag2 = '';        // 処理実行フラグ アイテム
//$flag3 = '';        // 処理実行フラグ 製品仕掛
//$flag4 = '';        // 処理実行フラグ 労務費・経費
//$b     = 0;         // テキストファイルのレコード数
$c     = 0;
//$d     = 0;
//$e     = 0;

// EDIエラー メール送信処理 準備作業
$file_name  = '/home/guest/daily/W#F6ERR.CSV';
$file_temp  = '/home/guest/daily/Q#F6ERR.tmp';
$file_write = '/home/guest/daily/Q#F6ERR.txt';
///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data =fgets($fp,400);
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
    while (FALSE !== ($data = fgetcsv($fp, 400, ',')) ) {    // CSV file として読込み
        if ($data[0] == '') continue;   // 空行の処理
        $data[8] = str_replace('\'', '"', $data[8]);  // 'が入るとエラーになるので"に変換する
        $data[9] = str_replace('\'', '"', $data[9]);  // 'が入るとエラーになるので"に変換する
        //$data[1] = pg_escape_string($data[1]);      // 品名
        $data[6] = str_replace('№', 'NO', $data[6]);  // '№'の文字化け対応
        $data[8] = str_replace('№', 'NO', $data[8]);  // '№'の文字化け対応
        $data[9] = str_replace('№', 'NO', $data[9]);  // '№'の文字化け対応
        //$data[9] = '　';
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\t{$data[5]}\t{$data[6]}\t{$data[7]}\t{$data[8]}\t{$data[9]}\n");
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
        //echo "$log_date DownLoad File $file_name をBackupできません！\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        //echo "$log_date DownLoad File $file_temp をBackupできません！\n";
    }
    // exit(); // debug用
}


/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // 大量登録用にはコメントアウト
} else {
    //fwrite($fpa, "$log_date db_connect() error \n");
    //fwrite($fpb, "$log_date db_connect() error \n");
    //echo "$log_date db_connect() error \n";
    exit();
}

// 製品グループコードマスター 日報処理
$file_name = '/home/guest/daily/Q#F6ERR.txt';
$file_name_bak = '/home/guest/daily/Q#F6ERR-bak.txt';
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $rec_ok = 0;        // 成功数カウント
    $mshgnm_ng_flg = FALSE;      // ＤＢ書込みＮＧフラグ
    if ( ($fp = fopen($file_name, 'r')) ) {
        while ($data = fgetcsv($fp, 400, "\t")) {
        // while ($data = fgetcsv($fp, 200, "_")) {     // FTP接続用
            if ($data[0] == '') continue;   // 空行の処理
            // $num = count($data);     // CSV File の field 数
            $rowcsv++;
            //$data[8] = addslashes($data[8]);    // "'"等がデータにある場合に\でエスケープする
            //$data[1] = trim($data[1]);          // 部品名の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
            ///////// 登録済みのチェック
            $query_chk = sprintf("select * from f6errfp where f6date='%d' and f6time='%d' and f6pgid='%s' and f6key='%s'", $data[0], $data[1], $data[2], $data[6]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                ///// 登録なし insert 使用
                $query = sprintf("insert into f6errfp (f6date, f6time, f6pgid, f6job, f6user, f6jbnr, f6key, f6step, f6ems1, f6ems2)
                        values('%d','%d','%s','%s','%s','%s','%s','%s','%s','%s')", $data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8],$data[9]);
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    //echo "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n";
                    //fwrite($fpa, "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n");
                    //fwrite($fpb, "$log_date {$rowcsv}:レコード目の書込みに失敗しました!\n");
                    $mshgnm_ng_flg = TRUE;
                    break;          // NG のため抜ける
                } else {
                    $row_in++;      // insert 成功
                    $rec_ok++;      // 成功数カウント
                    $flag2  = 1;     // insert データが１件でもあればon
                    $t_ymd  = $data[0];
                    $nen    = substr($t_ymd, 0, 4);
                    $tsuki  = substr($t_ymd, 4, 2);
                    $hi     = substr($t_ymd, 6, 2);
                    $v_ymd  = $nen . "/" . $tsuki . "/" . $hi;
                    $t_time = sprintf("%06d", $data[1]); 
                    $hour   = substr($t_time, 0, 2);
                    $minu   = substr($t_time, 2, 2);
                    $seco   = substr($t_time, 4, 2);
                    $v_time = $hour . ":" . $minu . ":" . $seco;
                    fwrite($fpa, "――――――――――――――――――――――――――――――――――――――――――――――\n");
                    fwrite($fpa, "[発生日]　{$v_ymd}　　[発生時刻]　$v_time\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[発生PGM]　　[発生ジョブ]　　[発生ユーザー]　　[発生ジョブ番号]　　[STEP]\n");
                    fwrite($fpa, "$data[2]　　$data[3]　　　　$data[4]　　　　　$data[5]　　　　　$data[7]\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[キー情報]　$data[6]\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[エラーメッセージ１]　$data[8]\n");
                    fwrite($fpa, "------------------------------------------------------------------------------------------\n");
                    fwrite($fpa, "[エラーメッセージ２]　$data[9]\n");
                    fwrite($fpa, "――――――――――――――――――――――――――――――――――――――――――――――\n\n\n");
                    
                }
            } else { // UPDATEは不要
                ///// 登録あり update 使用
                //$query = sprintf("update mshgnm set mhgcd='%s', mhgnm='%s'
                //        where mhgcd='%s'", $data[0], $data[1], $data[0]);
                //if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                //    echo "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n";
                //    fwrite($fpa, "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n");
                //    fwrite($fpb, "$log_date {$rowcsv}:レコード目のUPDATEに失敗しました!\n");
                //    $mshgnm_ng_flg = TRUE;
                //    break;          // NG のため抜ける
                //} else {
                //    $row_up++;      // update 成功
                //    $rec_ok++;      // 成功数カウント
                //}
            }
        }
    } else {
        //echo "Q#MSHGNM.txtをオープン出来ません\n";
        //fwrite($fpa,"".$rowcsv."W#MSHGNM.txtをオープン出来ません\n");
        //fwrite($fpb,"".$rowcsv."W#MSHGNM.txtをオープン出来ません\n");
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if (file_exists($file_name_bak)) unlink($file_name_bak);    // 前回のバックアップを削除
    if (!rename($file_name, $file_name_bak)) {                  // 今回のデータをバックアップ
        //echo "$log_date {$file_name} をBackupできません！\n";
        //fwrite($fpa,"".$log_date." ".$file_name." をBackupできません！\n");
        //fwrite($fpb,"".$log_date." ".$file_name." をBackupできません！\n");
    }
}

/******** メール送信(insertが１個でもあったら  *********/
fwrite($fpa, "--------------------------------------------------------------------------------------------\n");
fwrite($fpa, "                    * * 担当者にお渡しください * *\n");
if ($flag2 == 1 ) {
    if (rewind($fpa)) {
        $to = 'jsystem2@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp, ryota_waki@nitto-kohki.co.jp, hajime_nakayama@nitto-kohki.co.jp, kazumi_yoshinari@nitto-kohki.co.jp, hiroshi_shibuya@nitto-kohki.co.jp, yoshimitsu_izawa@nitto-kohki.co.jp, Takuya_Sato@nitto-kohki.co.jp';
        // テスト用
        //$to = 'norihisa_ooya@nitto-kohki.co.jp';
        $subject = "NKG-EDI System DataExchange_ErrorMail(AS側)";
        $msg = fread($fpa, filesize($log_name_a));
        $header = "From: jsystem2@nitto-kohki.co.jp\r\n";
        mb_send_mail($to, $subject, $msg, $header);
    }
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト
fclose($fpa);      ////// EDIエラー情報書込み終了

?>
