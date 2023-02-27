#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 決算書計算用勘定科目内訳明細表(daily)処理                                //
// AS/400 UKWLIB/W#KESSISHO                                                 //
// AS/400 UKWLIB/W#UCHIURI                                                  //
// AS/400 UKWLIB/W#UCHIKAI                                                  //
// AS/400 UKWLIB/W#UCHITAI                                                  //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#KESSISHO) LIB(UKWLIB)                       //
//         PCFILE(W#KESSISHO.TXT) MODE(TXT)                                 //
// 未払いは月次処理内の未払トップ10を実行                                   //
// Changed history                                                          //
// 2020/06/22 新規作成 daily_account_statement_cli.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "決算書計算用勘定科目内訳明細表データの更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_account_statement_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ db_connect() error \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ db_connect() error \n");
    echo "$log_date 決算書計算用勘定科目内訳明細表データ db_connect() error \n\n";
    exit();
}
///////// 決算書計算用勘定科目内訳明細表売掛金データの更新 準備作業
$file_orign  = '/home/guest/daily/W#UCHIURI.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHIURI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHIURI.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
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
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// EUC-JP へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
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
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 決算書計算用勘定科目内訳明細表データの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 決算書計算用勘定科目内訳明細表データの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 決算書計算用勘定科目内訳明細表データファイル {$file_orign} がありません！\n";
}


///////// 決算書計算用勘定科目内訳明細表買掛金データの更新 準備作業
$file_orign  = '/home/guest/daily/W#UCHIKAI.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHIKAI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHIKAI.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
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
        
        if ($rec == 11) {
            break;
        }
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 7) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            fwrite($fpb, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// EUC-JP へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
        }
        /*
        $yyyy = substr($data[0], 0,4);
        $mm   = substr($data[0], 4,2);
        if ($mm == '01') {
            $yyyy = ($yyyy - 1);
            $mm   = 12;
        } else {
            $mm   = $mm - 1;
            if($mm == '03') {
                $mm = '03';
            } elseif($mm == '06') {
                $mm = '06';
            } elseif($mm == '09') {
                $mm = '09';
            }
        }
        $data[0] = $yyyy . $mm;
        */
        $data[3] = $rec;
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary2='%s' and rep_gin='%s'", $data[0], $data[2], $data[3]);
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
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
                            rep_summary1 ='{$data[1]}',
                            rep_cri      = {$data[4]} ,
                            rep_de       = {$data[5]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary2='{$data[2]}' and rep_gin='{$data[3]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 決算書計算用勘定科目内訳明細表データの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 決算書計算用勘定科目内訳明細表データの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 決算書計算用勘定科目内訳明細表データファイル {$file_orign} がありません！\n";
}
// ベンダーマスターの更新 準備作業
$file_orign  = '/home/guest/daily/W#UCHIMIHA.TXT';
$file_temp   = 'W#UCHIMIHA-TEMP.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHIMIHA-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHIMIHA.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
        ///////////// SJIS → EUC 変換ロジック START (SJISでEUCにない文字はNULLバイトに変換される事に注意)
    $fp_conv = fopen($file_temp, 'w');  // EUC へ変換用
    while (!(feof($fp))) {
        $data = fgets($fp, 500);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
        $data = str_replace("\0", ' ', $data);                      // NULLバイトをSPACEへ変換
        $data = mb_ereg_replace('��', '（株）', $data);             // 機種依存文字を規格文字へ変更
        $data = preg_replace("/( |　)/", "", $data);
        fwrite($fp_conv, $data);
    }
    fclose($fp);
    fclose($fp_conv);
    $fp = fopen($file_temp, 'r');       // EUC へ変換後のファイル
        ///////////// SJIS → EUC 変換ロジック END
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        
        $data = fgetcsv($fp, 500, "_");     // 実レコードは150バイト デリミタはタブからアンダースコアへ変更
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec == 11) {
            break;
        }
        
        $num  = count($data);       // フィールド数の取得
        /*
        if ($num < 8) {
            $rec_no = $rec;     // 実際のレコード番号 上で$rec++するようにしたので、そのままでＯＫ
            // $_SESSION['s_sysmsg'] .= "field not 6&7 record=$rec_no <br>";
            fwrite($fpa, "$log_date field not 6&7 record=$rec_no \n");
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f+
        }
        */
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
        $data[5] = $rec;
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary1='%s' and rep_de=%d", $data[0], $data[1], $data[5]);
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
            $query = "UPDATE financial_report_cal SET
                            rep_summary2 = '{$data[2]}' ,
                            rep_cri      = {$data[4]} ,
                            rep_de       = {$data[5]} ,
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary1='{$data[1]}' and rep_de={$data[5]}";
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
    echo "未払TOP10：{$rec_ok}/{$rec} 件登録しました。";
    echo "未払TOP10：{$ins_ok}/{$rec} 件 追加";
    echo "未払TOP10：{$upd_ok}/{$rec} 件 変更";
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

///////// 決算書計算用勘定科目内訳明細表消費税計算データの更新 準備作業
$file_orign  = '/home/guest/daily/W#KESSISHO.TXT';
$file_backup = '/home/guest/daily/backup/W#KESSISHO-BAK.TXT';
$file_temp   = '/home/guest/daily/W#KESSISHO-TEMP.TXT';
$file_test   = '/home/guest/daily/debug/debug-W#KESSISHO.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $fp_conv = fopen($file_temp, 'w');  // EUC へ変換用
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 500, '|');     // 実レコードは183バイト デリミタは '|' アンダースコア'_'は使えない
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec == 11) {
            break;
        }
        
        $num  = count($data);       // フィールド数の取得
            
        if ($num != 7) {           // フィールド数のチェック
            //echo "$log_date テスト$rec\n";
            echo "$log_date テスト$data[2]\n";
            fwrite($fpa, "$log_date field not 7 record=$rec \n");
            fwrite($fpb, "$log_date field not 7 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            $data[$f] = str_replace("\0", ' ', $data[$f]);                      // NULLバイトをSPACEへ変換
            /////// EUC-JP へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
        }
        if ($data[1]=='') {
            $data[1] = $data[3];
            $data[2] = $data[4];
        }
        $data[3] = $data[6];
        $data[4] = $data[5];
        $data[5] = 0;
        $data[6] = 0;
        
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
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 決算書計算用勘定科目内訳明細表データ : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 決算書計算用勘定科目内訳明細表データの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 決算書計算用勘定科目内訳明細表データの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 決算書計算用勘定科目内訳明細表データファイル {$file_orign} がありません！\n";
}

///////// 決算書計算用勘定科目内訳明細表退職給付引当データの更新 準備作業
$file_orign  = '/home/guest/daily/W#UCHITAI.TXT';
$file_backup = '/home/guest/daily/backup/W#UCHITAI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-UCHITAI.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
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
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// EUC-JP へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
        }
        
        $query_chk = sprintf("SELECT * FROM financial_report_cal WHERE rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s' and rep_de=%d", $data[0], $data[1], $data[2], $data[3], $data[5]);
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
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
                            rep_cr       = {$data[6]}
                      where rep_ymd={$data[0]} and rep_summary1='{$data[1]}' and rep_summary2='{$data[2]}' and rep_gin='{$data[3]}' and rep_de={$data[5]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 決算書計算用勘定科目内訳明細表退職給付引当データ : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 決算書計算用勘定科目内訳明細表退職給付引当データの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 決算書計算用勘定科目内訳明細表退職給付引当データの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 決算書計算用勘定科目内訳明細退職給付引当表データファイル {$file_orign} がありません！\n";
}

/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
