#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// #!/usr/local/bin/php-4.3.4-cgi -q                                        //
// ベンダーマスター(担当者)の更新  AS400 UKWLIB/W#MIWKCKTA                  //
// AS/400 ----> Web Server (PHP) FTP転送は不可 EBCDICの変換が出来ないため   //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/05/26 Created  vendor_person_master_update.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版は必要ない
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('/var/www/html/function.php');
// require_once ('/var/www/html/tnk_func.php');   // account_group_check()で使用
// access_log();                               // Script Name 自動取得
// $_SESSION['site_index'] = 20;               // 月次損益関係=10 最後のメニューは 99 を使用
// $_SESSION['site_id']    = 10;               // 下位メニュー無し (0 <=)

if (!isset($_ENV['LANG'])) {
    $_SESSION['s_sysmsg'] = 'LANG 環境変数が設定されていません！ 中止します。';
    exit();
    // $_ENV['LANG'] = 'ja_JP.eucJP';
}

//////////// 呼出元の取得
// $act_referer = $_SESSION['act_referer'];

//////////// 認証チェック
// if (account_group_check() == FALSE) {
//     $_SESSION['s_sysmsg'] = "Accounting Group の権限が必要です！";
//     header('Location: ' . $act_referer);
//     exit();
// }

$log_date = date('Y-m-d H:i:s');                    ///// 日報用ログの日時
$fpa = fopen('/tmp/monthly_update.log', 'a');       ///// 日報用ログファイルへの書込みでオープン
// $_SESSION['s_sysmsg'] = '';     // 初期化

/********************
/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#HIBCTR';
$save_file = 'W#HIBCTR.TXT';

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $down_file, FTP_ASCII)) {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>ftp_get download 成功 $down_file → $save_file </font><br>";
            fwrite($fpa,"$log_date ftp_get download 成功 $down_file → $save_file \n");
        } else {
            $_SESSION['s_sysmsg'] .=  "ftp_get() error $down_file <br>";
            fwrite($fpa,"$log_date ftp_get() error $down_file \n");
        }
    } else {
        $_SESSION['s_sysmsg'] .=  'ftp_login() error<br>';
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> 買掛日報処理<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> 買掛日報処理\n");
}
*********************/

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
//     $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
// ベンダーマスターの更新 準備作業
$file_orign  = '/home/guest/monthly/W#MIWKCKTA.TXT';
$file_temp   = 'W#MIWKCKTA-TEMP.TXT';
$file_backup = 'backup/W#MIWKCKTA-BAK.TXT';
$file_test   = 'debug/debug-MIWKCKTA.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
        ///////////// SJIS → EUC 変換ロジック START (SJISでEUCにない文字はNULLバイトに変換される事に注意)
    $fp_conv = fopen($file_temp, 'w');  // EUC へ変換用
    while (!(feof($fp))) {
        $data = fgets($fp, 300);
        $data = mb_convert_encoding($data, 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
        $data = str_replace("\0", ' ', $data);                      // NULLバイトをSPACEへ変換
        $data = mb_ereg_replace('㈱', '（株）', $data);             // 機種依存文字を規格文字へ変更
        fwrite($fp_conv, $data);
    }
    fclose($fp);
    fclose($fp_conv);
    $fp = fopen($file_temp, 'r');       // EUC へ変換後のファイル
        ///////////// SJIS → EUC 変換ロジック END
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, "_");     // 実レコードは150バイト デリミタはタブからアンダースコアへ変更
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        /*
        if ($num < 6) {
            $rec_no = $rec;     // 実際のレコード番号 上で$rec++するようにしたので、そのままでＯＫ
            // $_SESSION['s_sysmsg'] .= "field not 6&7 record=$rec_no <br>";
            fwrite($fpa, "$log_date field not 6&7 record=$rec_no \n");
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}'\n");     // debug
                }
                fwrite($fpw,"\n");                      // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            continue;
        } elseif ($num == 6) {
            $data[6] = '';      // 代表者をblank
        }
        */
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $query_chk = sprintf("SELECT vendor FROM vendor_person_master WHERE vendor='%s' AND div='%s'", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO vendor_person_master (vendor, div, uid)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}')";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                // $_SESSION['s_sysmsg'] .= "{$rec}:レコード目の書込みに失敗しました!<br>";
                fwrite($fpa, "$log_date 発注先名:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE vendor_person_master SET vendor='{$data[0]}', div='{$data[1]}', uid='{$data[2]}'
                      where vendor='{$data[0]}' AND div='{$data[1]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                // $_SESSION['s_sysmsg'] .= "{$rec}:レコード目のUPDATEに失敗しました!\n";
                fwrite($fpa, "$log_date 発注先名:{$data[1]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} 件登録しました。</font><br><br>";
    // $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} 件 追加<br>";
    // $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} 件 変更</font>";
    echo "<font color='yellow'>{$rec_ok}/{$rec} 件登録しました。</font><br><br>";
    echo "<font color='white'>{$ins_ok}/{$rec} 件 追加 <br>";
    echo "{$upd_ok}/{$rec} 件 変更</font>";
    fwrite($fpa, "$log_date 発注先の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 発注先の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 発注先の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
            unlink($file_temp);         // temp ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            // $_SESSION['s_sysmsg'] .= "$log_date DownLoad File $file_orign をBackupできません！\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
        }
    }
} else {
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>トランザクションファイルがありません！</font>";
    fwrite($fpa,"$log_date : 発注先の更新ファイル {$file_orign} がありません！\n");
    echo '発注先の更新ファイルがありません！';
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fwrite($fpa,"$log_date : LANG = {$_ENV['LANG']}\n");    // fgetcsv()用のLANG環境変数の確認
fclose($fpa);      ////// 日報用ログ書込み終了

// header('Location: ' . H_WEB_HOST . ACT . 'vendor_master_view.php');   // チェックリストへ
exit();
?>
