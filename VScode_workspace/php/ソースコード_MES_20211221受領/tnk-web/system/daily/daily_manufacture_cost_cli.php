#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 製造原価計算日報(daily)処理                                              //
// AS/400 UKWLIB/W#SEIBUHYU：部品 A/C 有償支給                              //
//        UKWLIB/W#SEIBUKAI：部品仕掛 A/C 買掛金                            //
//        UKWLIB/W#SEIBUSWA：部品仕掛 A/C 仕訳                              //
//        UKWLIB/W#SEIGENKA：原材料 A/C 買掛金                              //
//        UKWLIB/W#SEIGENSW：原材料 A/C 仕訳                                //
//        UKWLIB/W#SEIGENYU：原材料 A/C 有償支給                            //
//        UKWLIB/W#SEIKIRIS：切粉                                           //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#SEIBUHYU) LIB(UKWLIB)                       //
//         PCFILE(W#SEIBUHYU.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2017/09/06 新規作成 daily_manufacture_cost_cli.php                       //
// 2017/10/05 AS/400からのダウンロードプログラムが古かったので訂正          //
// 2018/08/22 間違えて上書きしてしまったので再作成                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "製造原価計算の更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_manufacture_cost_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 製造原価計算 db_connect() error \n");
    fwrite($fpb, "$log_date 製造原価計算 db_connect() error \n");
    echo "$log_date 製造原価計算 db_connect() error \n\n";
    exit();
}
///////// 部品 A/C 有償支給ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SEIBUHYU.TXT';
$file_backup = '/home/guest/daily/backup/W#SEIBUHYU-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SEIBUHYU.TXT';
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
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_cname='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO manufacture_cost_cal (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品 A/C 有償支給:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 部品 A/C 有償支給:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE manufacture_cost_cal SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_cname    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_cname='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品 A/C 有償支給:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 部品 A/C 有償支給:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 部品 A/C 有償支給 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 部品 A/C 有償支給 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 部品 A/C 有償支給 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 部品 A/C 有償支給 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 部品 A/C 有償支給 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 部品 A/C 有償支給 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 部品 A/C 有償支給 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 部品 A/C 有償支給 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 部品 A/C 有償支給 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 部品 A/C 有償支給の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 部品 A/C 有償支給の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 部品 A/C 有償支給の更新ファイル {$file_orign} がありません！\n";
}
///////// 部品仕掛 A/C 買掛金ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SEIBUKAI.TXT';
$file_backup = '/home/guest/daily/backup/W#SEIBUKAI-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SEIBUKAI.TXT';
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
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_cname='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO manufacture_cost_cal (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品仕掛 A/C 買掛金:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 部品仕掛 A/C 買掛金:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE manufacture_cost_cal SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_cname    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_cname='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品仕掛 A/C 買掛金:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 部品仕掛 A/C 買掛金:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 部品仕掛 A/C 買掛金 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 部品仕掛 A/C 買掛金 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 部品仕掛 A/C 買掛金 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 部品仕掛 A/C 買掛金 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 部品仕掛 A/C 買掛金 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 部品仕掛 A/C 買掛金 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 部品仕掛 A/C 買掛金 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 部品仕掛 A/C 買掛金 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 部品仕掛 A/C 買掛金 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 部品仕掛 A/C 買掛金の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 部品仕掛 A/C 買掛金の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 部品仕掛 A/C 買掛金の更新ファイル {$file_orign} がありません！\n";
}
///////// 部品仕掛 A/C 仕訳ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SEIBUSWA.TXT';
$file_backup = '/home/guest/daily/backup/W#SEIBUSWA-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SEIBUSWA.TXT';
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
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_cname='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO manufacture_cost_cal (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品仕掛 A/C 仕訳:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 部品仕掛 A/C 仕訳:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE manufacture_cost_cal SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_cname    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_cname='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品仕掛 A/C 仕訳:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 部品仕掛 A/C 仕訳:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 部品仕掛 A/C 仕訳 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 部品仕掛 A/C 仕訳 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 部品仕掛 A/C 仕訳 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 部品仕掛 A/C 仕訳 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 部品仕掛 A/C 仕訳 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 部品仕掛 A/C 仕訳 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 部品仕掛 A/C 仕訳 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 部品仕掛 A/C 仕訳 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 部品仕掛 A/C 仕訳 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 部品仕掛 A/C 仕訳の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 部品仕掛 A/C 仕訳の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 部品仕掛 A/C 仕訳の更新ファイル {$file_orign} がありません！\n";
}
///////// 原材料 A/C 買掛金ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SEIGENKA.TXT';
$file_backup = '/home/guest/daily/backup/W#SEIGENKA-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SEIGENKA.TXT';
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
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_cname='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO manufacture_cost_cal (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 原材料 A/C 買掛金:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 原材料 A/C 買掛金:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE manufacture_cost_cal SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_cname    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_cname='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 原材料 A/C 買掛金:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 原材料 A/C 買掛金:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 原材料 A/C 買掛金 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 原材料 A/C 買掛金 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 原材料 A/C 買掛金 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 原材料 A/C 買掛金 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 原材料 A/C 買掛金 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 原材料 A/C 買掛金 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 原材料 A/C 買掛金 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 原材料 A/C 買掛金 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 原材料 A/C 買掛金 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 原材料 A/C 買掛金の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 原材料 A/C 買掛金の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 原材料 A/C 買掛金の更新ファイル {$file_orign} がありません！\n";
}
///////// 原材料 A/C 仕訳ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SEIGENSW.TXT';
$file_backup = '/home/guest/daily/backup/W#SEIGENSW-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SEIGENSW.TXT';
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
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_cname='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO manufacture_cost_cal (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 原材料 A/C 仕訳:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 原材料 A/C 仕訳:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE manufacture_cost_cal SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_cname    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_cname='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 原材料 A/C 仕訳:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 原材料 A/C 仕訳:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 原材料 A/C 仕訳 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 原材料 A/C 仕訳 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 原材料 A/C 仕訳 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 原材料 A/C 仕訳 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 原材料 A/C 仕訳 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 原材料 A/C 仕訳 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 原材料 A/C 仕訳 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 原材料 A/C 仕訳 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 原材料 A/C 仕訳 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 原材料 A/C 仕訳の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 原材料 A/C 仕訳の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 原材料 A/C 仕訳の更新ファイル {$file_orign} がありません！\n";
}
///////// 原材料 A/C 有償支給ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SEIGENYU.TXT';
$file_backup = '/home/guest/daily/backup/W#SEIGENYU-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SEIGENYU.TXT';
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
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_cname='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO manufacture_cost_cal (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 原材料 A/C 有償支給:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 原材料 A/C 有償支給:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE manufacture_cost_cal SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_cname    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_cname='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 原材料 A/C 有償支給:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 原材料 A/C 有償支給:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 原材料 A/C 有償支給 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 原材料 A/C 有償支給 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 原材料 A/C 有償支給 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 原材料 A/C 有償支給 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 原材料 A/C 有償支給 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 原材料 A/C 有償支給 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 原材料 A/C 有償支給 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 原材料 A/C 有償支給 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 原材料 A/C 有償支給 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 原材料 A/C 有償支給の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 原材料 A/C 有償支給の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 原材料 A/C 有償支給の更新ファイル {$file_orign} がありません！\n";
}
///////// 切粉ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SEIKIRIS.TXT';
$file_backup = '/home/guest/daily/backup/W#SEIKIRIS-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SEIKIRIS.TXT';
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
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
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
        
        $query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_cname='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO manufacture_cost_cal (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 切粉:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 切粉:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE manufacture_cost_cal SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_cname    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_cname='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 切粉:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 切粉:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 切粉 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 切粉 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 切粉 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 切粉 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 切粉 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 切粉 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 切粉 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 切粉 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 切粉 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 切粉の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 切粉の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 切粉の更新ファイル {$file_orign} がありません！\n";
}

// 金額計算（符号付け）符号付金額がないもののみ
$query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][13] == '部品仕買') {
            // 部品仕掛買掛の場合は貸借区分[3]が1の時マイナス それ以外はそのまま
            if ($res[$r][4] == '1') {
                $kin = $res[$r][7] * -1;
            } else {
                $kin = $res[$r][7];
            }
        } else {
            // 貸借区分[3]が1の時そのまま それ以外は符号が逆になる
            if ($res[$r][4] == '1') {
                $kin = $res[$r][7];
            } else {
                $kin = $res[$r][7] * -1;
            }
        }
        $query = "UPDATE manufacture_cost_cal SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
