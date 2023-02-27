#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 日報データ(単価経歴ファイル) 自動FTP Download cronで処理用 cgi版         //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/14 Created  parts_cost_ftp.php                                   //
// 2004/06/03 parts_cost_historyのprimary key(uniq key)にlot_noを追加       //
//            begin commit 及び FTP を 大量登録用にコメントアウト           //
// 2004/06/07 php-4.3.6-cgi -q → php-4.3.7-cgi -q  バージョンアップに伴う  //
// 2004/11/18 php-5.0.2-cliへバージョンアップ *シェルスクリプトに対応に変更 //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2010/01/20 $log_dateの前後は'ではなく"なので修正                    大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "単価経歴データの更新\n");
fwrite($fpb, "/var/www/html/system/daily/parts_cost_ftp.php\n");
echo "/var/www/html/system/daily/parts_cost_ftp.php\n";
        
// FTPのターゲットファイル
define('D_HICOST', 'UKWLIB/W#HICOST');      // 単価経歴ファイル download file
// 保存先のディレクトリとファイル名
define('S_HICOST', '/var/www/html/system/backup/W#HICOST.TXT');  // 単価経歴ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 単価経歴ファイル
        if (ftp_get($ftp_stream, S_HICOST, D_HICOST, FTP_ASCII)) {
            echo "$log_date 単価経歴 ftp_get download OK ", D_HICOST, "→", S_HICOST, "\n";
            fwrite($fpa,"$log_date 単価経歴 ftp_get download OK " . D_HICOST . '→' . S_HICOST . "\n");
            fwrite($fpb,"$log_date 単価経歴 ftp_get download OK " . D_HICOST . '→' . S_HICOST . "\n");
        } else {
            echo "$log_date 単価経歴 ftp_get() error ", D_HICOST, "\n";
            fwrite($fpa,"$log_date 単価経歴 ftp_get() error " . D_HICOST . "\n");
            fwrite($fpb,"$log_date 単価経歴 ftp_get() error " . D_HICOST . "\n");
        }
    } else {
        echo "$log_date 単価経歴 ftp_login() error \n";
        fwrite($fpa,"$log_date 単価経歴 ftp_login() error \n");
        fwrite($fpb,"$log_date 単価経歴 ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> 単価経歴\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 単価経歴\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 単価経歴\n");
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // 大量登録用にはコメントアウト
} else {
    fwrite($fpa, "$log_date 単価経歴 db_connect() error \n");
    fwrite($fpb, "$log_date 単価経歴 db_connect() error \n");
    echo "$log_date 単価経歴 db_connect() error \n";
    exit();
}
// 単価経歴ファイル 日報処理 準備作業
$file_orign  = S_HICOST;
$file_debug  = '/var/www/html/system/debug/debug-HICOST.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // 実レコードは75バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 13) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 13 record=$rec \n");
                fwrite($fpb, "$log_date field not 13 record=$rec \n");
                echo "$log_date field not 13 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
        $query_chk = sprintf("SELECT parts_no FROM parts_cost_history
                                WHERE parts_no='%s' and reg_no=%d and vendor='%s' and pro_no=%d and lot_no=%d",
                                $data[0], $data[1], $data[3], $data[4], $data[8]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO parts_cost_history (parts_no, reg_no, pro_mark, vendor, pro_no, mtl_cond,
                            kubun, pro_kubun, lot_no, lot_str, lot_end, lot_cost, as_regdate)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]},
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]},
                      '{$data[5]}',
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]},
                       {$data[9]},
                       {$data[10]},
                       {$data[11]},
                       {$data[12]})";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE parts_cost_history SET
                            parts_no    ='{$data[0]}',
                            reg_no      = {$data[1]} ,
                            pro_mark    ='{$data[2]}',
                            vendor      ='{$data[3]}',
                            pro_no      = {$data[4]} ,
                            mtl_cond    ='{$data[5]}',
                            kubun       ='{$data[6]}',
                            pro_kubun   ='{$data[7]}',
                            lot_no      = {$data[8]} ,
                            lot_str     = {$data[9]} ,
                            lot_end     = {$data[10]},
                            lot_cost    = {$data[11]},
                            as_regdate  = {$data[12]}
                WHERE parts_no='{$data[0]}' and reg_no={$data[1]} and vendor='{$data[3]}' and pro_no={$data[4]} and lot_no={$data[8]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 単価経歴の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 単価経歴の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 単価経歴の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 単価経歴の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 単価経歴の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 単価経歴の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 単価経歴の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 単価経歴の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 単価経歴の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

?>
