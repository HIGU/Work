#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 決算書計算用期首データ取得(daily)処理                                    //
// AS/400 UKWLIB/W#KESKISHU                                                 //
// AS/400 UKWLIB/W#KESGEKEI                                                 //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#KESKISHU) LIB(UKWLIB)                       //
//         PCFILE(W#KESKISHU.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2018/06/14 新規作成 daily_financial_report_cli.php                       //
// 2018/07/05 月計データの取得を追加                                        //
// 2018/10/17 銀行コードがないとうまく集計できないので追加                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "決算書計算用期首データの更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_financial_report_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 決算書計算用期首データ db_connect() error \n");
    fwrite($fpb, "$log_date 決算書計算用期首データ db_connect() error \n");
    echo "$log_date 決算書計算用期首データ db_connect() error \n\n";
    exit();
}
///////// 部品 A/C 有償支給ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#KESKISHU.TXT';
$file_backup = '/home/guest/daily/backup/W#KESKISHU-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-KESKISHU.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // 実レコードは183バイト デリミタは '|' アンダースコア'_'は使えない
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 7) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            fwrite($fpb, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// UTF-8 へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
        }
        
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $data[0], $data[1], $data[2], $data[3]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO financial_report_cal (rep_ymd, rep_summary1, rep_summary2, rep_gin, rep_cri, rep_de, rep_cr)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                      '{$data[2]}',
                      '{$data[3]}',
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 決算書計算用期首データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用期首データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE financial_report_cal SET
                            rep_cri      = {$data[4]} ,
                            rep_de       = {$data[5]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary1='{$data[1]}' and rep_summary2='{$data[2]}' and rep_gin='{$data[3]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 決算書計算用期首データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用期首データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 決算書計算用期首データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 決算書計算用期首データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 決算書計算用期首データ : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 決算書計算用期首データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 決算書計算用期首データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 決算書計算用期首データ : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 決算書計算用期首データ : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 決算書計算用期首データ : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 決算書計算用期首データ : {$upd_ok}/{$rec} 件 変更 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign をBackupできません！\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign をBackupできません！\n");
            echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : 決算書計算用期首データの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 決算書計算用期首データの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 決算書計算用期首データファイル {$file_orign} がありません！\n";
}

///////// 部品 A/C 有償支給ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#KESGEKEI.TXT';
$file_backup = '/home/guest/daily/backup/W#KESGEKEI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-KESGEKEI.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // 実レコードは183バイト デリミタは '|' アンダースコア'_'は使えない
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 6) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 5 record=$rec \n");
            fwrite($fpb, "$log_date field not 5 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// UTF-8 へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
        }
        
        $query_chk = sprintf("SELECT * FROM financial_report_month WHERE rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $data[2], $data[0], $data[1], $data[5]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO financial_report_month (rep_summary1, rep_summary2, rep_ymd, rep_de, rep_cr, rep_gin)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                       {$data[2]} ,
                       {$data[3]} ,
                       {$data[4]} ,
                      '{$data[5]}'
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 決算書計算用月計表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用月計表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE financial_report_month SET
                            rep_de       = {$data[3]} ,
                            rep_cr       = {$data[4]}
                      where rep_summary1='{$data[0]}' and rep_summary2='{$data[1]}' and rep_ymd={$data[2]} and rep_gin='{$data[5]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 決算書計算用月計表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用月計表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 決算書計算用月計表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 決算書計算用月計表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 決算書計算用月計表データ : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 決算書計算用月計表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 決算書計算用月計表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 決算書計算用月計表データ : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 決算書計算用月計表データ : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 決算書計算用月計表データ : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 決算書計算用月計表データ : {$upd_ok}/{$rec} 件 変更 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign をBackupできません！\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign をBackupできません！\n");
            echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : 決算書計算用月計表データの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 決算書計算用月計表データの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 決算書計算用月計表データファイル {$file_orign} がありません！\n";
}

/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
