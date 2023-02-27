#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
//      旧タイプ #!/usr/local/bin/php-4.3.9-cgi -q                          //
// 日報データ(注文書発行データ) 自動FTP Download cronで処理用 cli版         //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/15 Created  order_data_ftp_cli.php                               //
// 2004/11/18 php-5.0.2-cliへバージョンアップ *シェルスクリプトに対応に変更 //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2010/01/20 $log_dateの前後は'では無く"なので修正                    大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "注文書発行データの更新\n");
fwrite($fpb, "/var/www/html/system/daily/order_data_ftp_cli.php\n");
echo "/var/www/html/system/daily/order_data_ftp_cli.php\n";

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#MIORDD';       // 注文書発行ファイル download file
// 保存先のディレクトリとファイル名
$save_file = '/var/www/html/system/backup/W#MIORDD.TXT';     // 注文書発行ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 注文書発行ファイル
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo "$log_date 注文書発行 ftp_get download OK ", $target_file, "→", $save_file, "\n";
            fwrite($fpa,"$log_date 注文書発行 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            fwrite($fpb,"$log_date 注文書発行 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
        } else {
            echo "$log_date 注文書発行 ftp_get() error ", $target_file, "\n";
            fwrite($fpa,"$log_date 注文書発行 ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date 注文書発行 ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "$log_date 注文書発行 ftp_login() error \n";
        fwrite($fpa,"$log_date 注文書発行 ftp_login() error \n");
        fwrite($fpb,"$log_date 注文書発行 ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> 注文書発行F\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 注文書発行F\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 注文書発行F\n");
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 注文書発行 db_connect() error \n");
    fwrite($fpb, "$log_date 注文書発行 db_connect() error \n");
    echo "$log_date 注文書発行 db_connect() error \n";
    exit();
}
// 注文書発行ファイル 日報処理 準備作業
$file_orign  = $save_file;
$file_debug  = '/var/www/html/system/debug/debug-MIORDD.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 220, '_');     // 実レコードは206バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 20) {    // 実際には 21 あり(最後がない場合があるため)
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 20 record=$rec \n");
                fwrite($fpb, "$log_date field not 20 record=$rec \n");
                echo "$log_date field not 20 record=$rec \n";
            }
           continue;
        }
        if (!isset($data[20])) $data[20]='';    // 工事番号がセットされているかチェック
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $query_chk = sprintf("SELECT order_seq FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO order_data
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                       {$data[2]} ,
                       {$data[3]} ,
                       {$data[4]} ,
                       {$data[5]} ,
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                       {$data[10]} ,
                       {$data[11]} ,
                       {$data[12]} ,
                       {$data[13]} ,
                       {$data[14]} ,
                       {$data[15]} ,
                       {$data[16]} ,
                       {$data[17]} ,
                       {$data[18]} ,
                      '{$data[19]}',
                      '{$data[20]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 発行番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 発行番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 発行番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
            $query = "UPDATE order_data SET
                            order_seq   = {$data[0]} ,
                            uke_no      ='{$data[1]}',
                            date_issue  = {$data[2]} ,
                            pre_seq     = {$data[3]} ,
                            sei_no      = {$data[4]} ,
                            order_no    = {$data[5]} ,
                            parts_no    ='{$data[6]}',
                            vendor      ='{$data[7]}',
                            order_q     = {$data[8]} ,
                            order_price = {$data[9]} ,
                            delivery    = {$data[10]} ,
                            uke_date    = {$data[11]} ,
                            uke_q       = {$data[12]} ,
                            ken_date    = {$data[13]} ,
                            genpin      = {$data[14]} ,
                            siharai     = {$data[15]} ,
                            cut_date    = {$data[16]} ,
                            cut_genpin  = {$data[17]} ,
                            cut_siharai = {$data[18]} ,
                            cut_kubun   ='{$data[19]}',
                            kouji_no    ='{$data[20]}'
                WHERE order_seq={$data[0]}
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 発行番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 発行番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 発行番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 注文書発行Fの更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 注文書発行Fの更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 注文書発行Fの更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 注文書発行Fの更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 注文書発行Fの更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 注文書発行Fの更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 注文書発行Fの更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 注文書発行Fの更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 注文書発行Fの更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
