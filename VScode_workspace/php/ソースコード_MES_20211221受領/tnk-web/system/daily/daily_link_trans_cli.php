#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 連結取引連絡表日報(daily)処理                                            //
// AS/400 UKWLIB/W#RENURIKA：売掛金経歴                                     //
//        UKWLIB/W#RKAIOUNK：買掛金相殺金額 NKIT 以外                       //
//        UKWLIB/W#RENKEINK：経費関連 NK                                    //
//        UKWLIB/W#RENKEISK：経費関連 SNK                                   //
//        UKWLIB/W#RENKEIMT：経費関連 MT                                    //
//        UKWLIB/W#RENKEIIT：経費関連 NKIT                                  //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#RENURIKA) LIB(UKWLIB)                       //
//         PCFILE(W#RENURIKA.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2017/10/12 新規作成 daily_link_trans_cli.php                             //
// 2017/10/18 取り込みまでPGM完了                                           //
// 残りは、買掛金・その他経費・売掛金などを月毎に計算すること               //
// 取り込み時に計算をして、照会画面では取得するだけにしておく               //
// 2018/10/29 $del_fgが初回の時、１件目のデータを削除してしまう為、修正     //
// 2019/02/05 前月のみの計算を忘れて取り込んでいなかったので手動で日付を    //
//            変えて強制的に計算                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "売掛金経歴の更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_link_trans_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 売掛金経歴 db_connect() error \n");
    fwrite($fpb, "$log_date 売掛金経歴 db_connect() error \n");
    echo "$log_date 売掛金経歴 db_connect() error \n\n";
    exit();
}

///////// 売掛金経歴ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#RENURIKA.TXT';
$file_backup = '/home/guest/daily/backup/W#RENURIKA-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENURIKA.TXT';
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
        if ($num != 10) {           // フィールド数のチェック
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_sales WHERE sales_code='%s' and sales_ym=%d", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO link_trans_sales (sales_code, sales_ym, sales_kuri, sales_kei, sales_kai, sales_zan, sales_tou, sales_syo, sales_cho, sales_chozei)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]} ,
                       {$data[7]} ,
                       {$data[8]} ,
                       {$data[9]})";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 売掛金経歴:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 売掛金経歴:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE link_trans_sales SET
                            sales_code   ='{$data[0]}',
                            sales_ym     = {$data[1]} ,
                            sales_kuri   = {$data[2]} ,
                            sales_kei    = {$data[3]} ,
                            sales_kai    = {$data[4]} ,
                            sales_zan    = {$data[5]} ,
                            sales_tou    = {$data[6]} ,
                            sales_syo    = {$data[7]} ,
                            sales_cho    = {$data[8]} ,
                            sales_chozei = {$data[9]}
                      where sales_code='{$data[0]}' and sales_ym={$data[1]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 売掛金経歴:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 売掛金経歴:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 売掛金経歴 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 売掛金経歴 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 売掛金経歴 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 売掛金経歴 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 売掛金経歴 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 売掛金経歴 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 売掛金経歴 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 売掛金経歴 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 売掛金経歴 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 売掛金経歴の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 売掛金経歴の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 売掛金経歴の更新ファイル {$file_orign} がありません！\n";
}

///////// 買掛金相殺金額 NKIT 以外ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#RKAIOUNK.TXT';
$file_backup = '/home/guest/daily/backup/W#RKAIOUNK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RKAIOUNK.TXT';
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_offset WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_code='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO link_trans_offset (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_code, den_kin)
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
                fwrite($fpa, "$log_date 買掛金相殺金額 NKIT 以外:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 買掛金相殺金額 NKIT 以外:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE link_trans_offset SET
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
                            den_code     ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_code='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 買掛金相殺金額 NKIT 以外:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 買掛金相殺金額 NKIT 以外:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 買掛金相殺金額 NKIT 以外 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 買掛金相殺金額 NKIT 以外 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 買掛金相殺金額 NKIT 以外 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 買掛金相殺金額 NKIT 以外 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 買掛金相殺金額 NKIT 以外 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 買掛金相殺金額 NKIT 以外 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 買掛金相殺金額 NKIT 以外 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 買掛金相殺金額 NKIT 以外 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 買掛金相殺金額 NKIT 以外 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 買掛金相殺金額 NKIT 以外の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 買掛金相殺金額 NKIT 以外の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 買掛金相殺金額 NKIT 以外の更新ファイル {$file_orign} がありません！\n";
}

// 金額計算（符号付け）符号付金額がないもののみ
$query_chk = sprintf("SELECT * FROM link_trans_offset WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7] * -1;
        } else {
            $kin = $res[$r][7];
        }
        $query = "UPDATE link_trans_offset SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_code='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// 経費関連 NKファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#RENKEINK.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEINK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEINK.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_fg = 0;    // 削除フラグ
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_nk WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_nk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 NK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 NK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_nk WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// 削除後 update 使用
            $query = "INSERT INTO link_trans_expense_nk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 NK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 NK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 経費関連 NK : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 経費関連 NK : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 経費関連 NK : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 経費関連 NK : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 経費関連 NK : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 経費関連 NK : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 経費関連 NK : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 経費関連 NK : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 経費関連 NK : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 経費関連 NKの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 経費関連 NKの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 経費関連 NKの更新ファイル {$file_orign} がありません！\n";
}

// 金額計算（符号付け）符号付金額がないもののみ
$query_chk = sprintf("SELECT * FROM link_trans_expense_nk WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_nk SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// 経費関連 SNKファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#RENKEISK.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEISK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEISK.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_fg = 0;    // 削除フラグ
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_snk WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_snk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 SNK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 SNK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_snk WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// 削除後 update 使用
            $query = "INSERT INTO link_trans_expense_snk (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 SNK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 SNK:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 経費関連 SNK : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 経費関連 SNK : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 経費関連 SNK : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 経費関連 SNK : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 経費関連 SNK : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 経費関連 SNK : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 経費関連 SNK : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 経費関連 SNK : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 経費関連 SNK : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 経費関連 SNKの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 経費関連 SNKの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 経費関連 SNKの更新ファイル {$file_orign} がありません！\n";
}

// 金額計算（符号付け）符号付金額がないもののみ
$query_chk = sprintf("SELECT * FROM link_trans_expense_snk WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_snk SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// 経費関連 MTファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#RENKEIMT.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEIMT-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEIMT.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_fg = 0;    // 削除フラグ
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_mt WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_mt (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 MT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 MT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_mt WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// 削除後 update 使用
            $query = "INSERT INTO link_trans_expense_mt (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 MT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 MT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 経費関連 MT : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 経費関連 MT : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 経費関連 MT : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 経費関連 MT : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 経費関連 MT : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 経費関連 MT : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 経費関連 MT : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 経費関連 MT : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 経費関連 MT : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 経費関連 MTの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 経費関連 MTの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 経費関連 MTの更新ファイル {$file_orign} がありません！\n";
}

// 金額計算（符号付け）符号付金額がないもののみ
$query_chk = sprintf("SELECT * FROM link_trans_expense_mt WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_mt SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// 経費関連 NKITファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#RENKEIIT.TXT';
$file_backup = '/home/guest/daily/backup/W#RENKEIIT-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-RENKEIIT.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_fg = 0;    // 削除フラグ
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
        
        $query_chk = sprintf("SELECT * FROM link_trans_expense_nkit WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $del_fg = 1;
            $query = "INSERT INTO link_trans_expense_nkit (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 NKIT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 NKIT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            if ($del_fg == 0) {
                $query_del = sprintf("DELETE FROM link_trans_expense_nkit WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// 削除後 update 使用
            $query = "INSERT INTO link_trans_expense_nkit (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_cname, den_kin)
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
                fwrite($fpa, "$log_date 経費関連 NKIT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 経費関連 NKIT:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 経費関連 NKIT : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 経費関連 NKIT : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 経費関連 NKIT : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 経費関連 NKIT : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 経費関連 NKIT : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 経費関連 NKIT : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 経費関連 NKIT : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 経費関連 NKIT : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 経費関連 NKIT : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 経費関連 NKITの更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 経費関連 NKITの更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 経費関連 NKITの更新ファイル {$file_orign} がありません！\n";
}

// 金額計算（符号付け）符号付金額がないもののみ
$query_chk = sprintf("SELECT * FROM link_trans_expense_nkit WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE link_trans_expense_nkit SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_cname='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

/*
// 金額計算（符号付け）符号付金額がないもののみ
$query_chk = sprintf("SELECT * FROM manufacture_cost_cal WHERE den_kin='0'");
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][13] == '部品仕買') {   // 部品仕掛買掛金の場合 貸借区分[3]が1の時符号が逆 それ以外はそのまま
            if ($res[$r][4] == '1') {
                $kin = $res[$r][7] * -1;
            } else {
                $kin = $res[$r][7];
            }
        } else {    //それ以外の場合 貸借区分[3]が1の時そのまま それ以外は符号が逆になる
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
*/

// ここから各経歴計算 ここで計算して照会画面では照会だけ

// 経歴計算年月取得
$target_ym   = date('Ym');          //201710  当月と前月のみ再計算
//$target_ym   = 201901;
//$target_ym   = 201709;              // テスト用
$b_target_ym = $target_ym;          //201709
$b_mm  = substr($b_target_ym, -2, 2);
if ($b_mm == '01') {
    $b_target_ym = $b_target_ym - 100;  // １月であればとりあえずマイナス100で前年
    $b_target_ym = $b_target_ym + 11;   // さらにプラス11で12月
} else {
    $b_target_ym = $b_target_ym - 1;    // １月以外であればマイナス1で前月
}
// 仕入高用支払日計算（翌月）
$n_target_ym = $target_ym;          //201711
$n_mm  = substr($n_target_ym, -2, 2);
if ($n_mm == '12') {
    $n_target_ym = $n_target_ym + 100;  // １２月であればとりあえずプラス100で翌年
    $n_target_ym = $n_target_ym - 11;   // さらにマイナス11で1月
} else {
    $n_target_ym = $n_target_ym + 1;    // １月以外であればプラス1で翌月
}

// 経歴計算用全前月計算
$bb_target_ym = $b_target_ym;          //201708
$bb_mm  = substr($bb_target_ym, -2, 2);
if ($bb_mm == '01') {
    $bb_target_ym = $bb_target_ym - 100;  // １月であればとりあえずマイナス100で前年
    $bb_target_ym = $bb_target_ym + 11;   // さらにプラス11で12月
} else {
    $bb_target_ym = $bb_target_ym - 1;    // １月以外であればマイナス1で前月
}

// 計算用年月日
$str_ymd = $target_ym . '00';
$end_ymd = $target_ym . '99';

$b_str_ymd = $b_target_ym . '00';
$b_end_ymd = $b_target_ym . '99';

// 収益・費用関係計算
// link_trans_expense_nk   取引先コード00001（売掛のコードで統一）den_summary1と2が[0500] DBで分けてるので使わない
// link_trans_expense_snk  取引先コード00005（売掛のコードで統一）den_summary1と2が[0501] DBで分けてるので使わない
// link_trans_expense_mt   取引先コード00004（売掛のコードで統一）den_summary1と2が[0502] DBで分けてるので使わない
// link_trans_expense_nkit 取引先コード00101（売掛のコードで統一）den_summary1と2が[0503] DBで分けてるので使わない

// 収益計算
// 各取引先の金額格納
$shueki_money   = array();   // 当月：[0][0]が00001の受取利息 [0][1]が00001の受取配当金、[1][0]が00005の受取利息 [1][1]が00005の受取配当金
$b_shueki_money = array();   // 前月：[0][0]が00001の受取利息 [0][1]が00001の受取配当金、[1][0]が00005の受取利息 [1][1]が00005の受取配当金

// 受取利息
// NK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][0] = 0;
    } else {
        $b_shueki_money[0][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][0] = 0;
    } else {
        $shueki_money[0][0] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][0] = 0;
    } else {
        $b_shueki_money[1][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][0] = 0;
    } else {
        $shueki_money[1][0] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][0] = 0;
    } else {
        $b_shueki_money[2][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][0] = 0;
    } else {
        $shueki_money[2][0] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][0] = 0;
    } else {
        $b_shueki_money[3][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9101' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][0] = 0;
    } else {
        $shueki_money[3][0] = $res[0][0];
    }
}
// 受取配当金
// NK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][1] = 0;
    } else {
        $b_shueki_money[0][1] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][1] = 0;
    } else {
        $shueki_money[0][1] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][1] = 0;
    } else {
        $b_shueki_money[1][1] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][1] = 0;
    } else {
        $shueki_money[1][1] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][1] = 0;
    } else {
        $b_shueki_money[2][1] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][1] = 0;
    } else {
        $shueki_money[2][1] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][1] = 0;
    } else {
        $b_shueki_money[3][1] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9102' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][1] = 0;
    } else {
        $shueki_money[3][1] = $res[0][0];
    }
}
// 家賃収入 9103 20 で検索
// NK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][2] = 0;
    } else {
        $b_shueki_money[0][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][2] = 0;
    } else {
        $shueki_money[0][2] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][2] = 0;
    } else {
        $b_shueki_money[1][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][2] = 0;
    } else {
        $shueki_money[1][2] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][2] = 0;
    } else {
        $b_shueki_money[2][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][2] = 0;
    } else {
        $shueki_money[2][2] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][2] = 0;
    } else {
        $b_shueki_money[3][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='9103' and den_break='20' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][2] = 0;
    } else {
        $shueki_money[3][2] = $res[0][0];
    }
}
// 有償支給
// NK
$b_shueki_money[0][3] = 0;
$shueki_money[0][3]   = 0;
// SNK
$b_shueki_money[1][3] = 0;
$shueki_money[1][3]   = 0;
// MT
$b_shueki_money[2][3] = 0;
$shueki_money[2][3]   = 0;
// NKIT
$b_shueki_money[3][3] = 0;
$shueki_money[3][3]   = 0;
// 資産売却
// NK
$b_shueki_money[0][4] = 0;
$shueki_money[0][4]   = 0;
// SNK
$b_shueki_money[1][4] = 0;
$shueki_money[1][4]   = 0;
// MT
$b_shueki_money[2][4] = 0;
$shueki_money[2][4]   = 0;
// NKIT
$b_shueki_money[3][4] = 0;
$shueki_money[3][4]   = 0;
// 雑収入
// NK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[0][5] = 0;
    } else {
        $b_shueki_money[0][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[0][5] = 0;
    } else {
        $shueki_money[0][5] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[1][5] = 0;
    } else {
        $b_shueki_money[1][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[1][5] = 0;
    } else {
        $shueki_money[1][5] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[2][5] = 0;
    } else {
        $b_shueki_money[2][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[2][5] = 0;
    } else {
        $shueki_money[2][5] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_shueki_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_shueki_money[3][5] = 0;
    } else {
        $b_shueki_money[3][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE ((den_account='9103' and den_break='00') or (den_account='9107' and den_break='00')) and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $shueki_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $shueki_money[3][5] = 0;
    } else {
        $shueki_money[3][5] = $res[0][0];
    }
}
// 収益登録処理
// 共通設定
// SQL共有処理 得意先コード
$input_code   = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 科目名
$input_kamoku = array('受取利息','受取配当金','家賃収入','有償支給','資産売却','雑収入');
$code_num     = 4;                  // 得意先コード数 4
$kamoku_num   = 6;                  // 科目数 6

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 登録なし insert 使用
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $b_shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// 登録あり update 使用
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $b_shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 登録なし insert 使用
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// 登録あり update 使用
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $shueki_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}

// 費用計算
// link_trans_expense_nk   取引先コード00001（売掛のコードで統一）den_summary1と2が[0500] DBで分けてるので使わない
// link_trans_expense_snk  取引先コード00005（売掛のコードで統一）den_summary1と2が[0501] DBで分けてるので使わない
// link_trans_expense_mt   取引先コード00004（売掛のコードで統一）den_summary1と2が[0502] DBで分けてるので使わない
// link_trans_expense_nkit 取引先コード00101（売掛のコードで統一）den_summary1と2が[0503] DBで分けてるので使わない

// 各取引先の金額格納
$hiyo_money   = array();   // 当月：[0][0]が00001の受取利息 [0][1]が00001の受取配当金、[1][0]が00005の受取利息 [1][1]が00005の受取配当金
$b_hiyo_money = array();   // 前月：[0][0]が00001の受取利息 [0][1]が00001の受取配当金、[1][0]が00005の受取利息 [1][1]が00005の受取配当金

// 支払利息
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][0] = 0;
    } else {
        $b_hiyo_money[0][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][0] = 0;
    } else {
        $hiyo_money[0][0] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][0] = 0;
    } else {
        $b_hiyo_money[1][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][0] = 0;
    } else {
        $hiyo_money[1][0] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][0] = 0;
    } else {
        $b_hiyo_money[2][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][0] = 0;
    } else {
        $hiyo_money[2][0] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][0] = 0;
    } else {
        $b_hiyo_money[3][0] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='8201' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][0] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][0] = 0;
    } else {
        $hiyo_money[3][0] = $res[0][0];
    }
}

// 通信費
// NK計算
// 前月
// NKのみ回収以外と回収を分ける(消費税除外する為)
// 回収以外
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][1] = 0;
    } else {
        $b_hiyo_money[0][1] = $res[0][0];
    }
}
// 回収のみ
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][1] += 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][1] += 0;
    } else {
        $b_hiyo_money[0][1] += round($res[0][0]/1.08);
    }
}
// 当月
// NKのみ回収以外と回収を分ける(消費税除外する為)
// 回収以外
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][1] = 0;
    } else {
        $hiyo_money[0][1] = $res[0][0];
    }
}
// 回収のみ
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7503' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][1] += 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][1] += 0;
    } else {
        $hiyo_money[0][1] += round($res[0][0]/1.08);
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][1] = 0;
    } else {
        $b_hiyo_money[1][1] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][1] = 0;
    } else {
        $hiyo_money[1][1] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][1] = 0;
    } else {
        $b_hiyo_money[2][1] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][1] = 0;
    } else {
        $hiyo_money[2][1] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][1] = 0;
    } else {
        $b_hiyo_money[3][1] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7503' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][1] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][1] = 0;
    } else {
        $hiyo_money[3][1] = $res[0][0];
    }
}

// 工場消耗品費
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][2] = 0;
    } else {
        $b_hiyo_money[0][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][2] = 0;
    } else {
        $hiyo_money[0][2] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][2] = 0;
    } else {
        $b_hiyo_money[1][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][2] = 0;
    } else {
        $hiyo_money[1][2] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][2] = 0;
    } else {
        $b_hiyo_money[2][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][2] = 0;
    } else {
        $hiyo_money[2][2] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][2] = 0;
    } else {
        $b_hiyo_money[3][2] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7527' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][2] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][2] = 0;
    } else {
        $hiyo_money[3][2] = $res[0][0];
    }
}

// 事務消耗品費
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][3] = 0;
    } else {
        $b_hiyo_money[0][3] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][3] = 0;
    } else {
        $hiyo_money[0][3] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][3] = 0;
    } else {
        $b_hiyo_money[1][3] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][3] = 0;
    } else {
        $hiyo_money[1][3] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][3] = 0;
    } else {
        $b_hiyo_money[2][3] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][3] = 0;
    } else {
        $hiyo_money[2][3] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][3] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][3] = 0;
    } else {
        $b_hiyo_money[3][3] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7526' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][3] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][3] = 0;
    } else {
        $hiyo_money[3][3] = $res[0][0];
    }
}

// 業務委託費
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][4] = 0;
    } else {
        $b_hiyo_money[0][4] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][4] = 0;
    } else {
        $hiyo_money[0][4] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][4] = 0;
    } else {
        $b_hiyo_money[1][4] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][4] = 0;
    } else {
        $hiyo_money[1][4] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][4] = 0;
    } else {
        $b_hiyo_money[2][4] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][4] = 0;
    } else {
        $hiyo_money[2][4] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][4] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][4] = 0;
    } else {
        $b_hiyo_money[3][4] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7512' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][4] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][4] = 0;
    } else {
        $hiyo_money[3][4] = $res[0][0];
    }
}
// 運賃荷造費
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][5] = 0;
    } else {
        $b_hiyo_money[0][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][5] = 0;
    } else {
        $hiyo_money[0][5] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][5] = 0;
    } else {
        $b_hiyo_money[1][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][5] = 0;
    } else {
        $hiyo_money[1][5] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][5] = 0;
    } else {
        $b_hiyo_money[2][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][5] = 0;
    } else {
        $hiyo_money[2][5] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][5] = 0;
    } else {
        $b_hiyo_money[3][5] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7509' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][5] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][5] = 0;
    } else {
        $hiyo_money[3][5] = $res[0][0];
    }
}
// 水道光熱費
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][6] = 0;
    } else {
        $b_hiyo_money[0][6] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][6] = 0;
    } else {
        $hiyo_money[0][6] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][6] = 0;
    } else {
        $b_hiyo_money[1][6] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][6] = 0;
    } else {
        $hiyo_money[1][6] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][6] = 0;
    } else {
        $b_hiyo_money[2][6] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][6] = 0;
    } else {
        $hiyo_money[2][6] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][6] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][6] = 0;
    } else {
        $b_hiyo_money[3][6] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7531' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][6] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][6] = 0;
    } else {
        $hiyo_money[3][6] = $res[0][0];
    }
}
// 試験研究費
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][7] = 0;
    } else {
        $b_hiyo_money[0][7] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][7] = 0;
    } else {
        $hiyo_money[0][7] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][7] = 0;
    } else {
        $b_hiyo_money[1][7] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][7] = 0;
    } else {
        $hiyo_money[1][7] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][7] = 0;
    } else {
        $b_hiyo_money[2][7] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][7] = 0;
    } else {
        $hiyo_money[2][7] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][7] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][7] = 0;
    } else {
        $b_hiyo_money[3][7] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7522' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][7] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][7] = 0;
    } else {
        $hiyo_money[3][7] = $res[0][0];
    }
}
// 地代家賃
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][8] = 0;
    } else {
        $b_hiyo_money[0][8] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][8] = 0;
    } else {
        $hiyo_money[0][8] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][8] = 0;
    } else {
        $b_hiyo_money[1][8] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][8] = 0;
    } else {
        $hiyo_money[1][8] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][8] = 0;
    } else {
        $b_hiyo_money[2][8] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][8] = 0;
    } else {
        $hiyo_money[2][8] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][8] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][8] = 0;
    } else {
        $b_hiyo_money[3][8] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7536' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][8] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][8] = 0;
    } else {
        $hiyo_money[3][8] = $res[0][0];
    }
}
// 倉敷料
// NK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][9] = 0;
    } else {
        $b_hiyo_money[0][9] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][9] = 0;
    } else {
        $hiyo_money[0][9] = $res[0][0];
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][9] = 0;
    } else {
        $b_hiyo_money[1][9] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][9] = 0;
    } else {
        $hiyo_money[1][9] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][9] = 0;
    } else {
        $b_hiyo_money[2][9] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][9] = 0;
    } else {
        $hiyo_money[2][9] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][9] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][9] = 0;
    } else {
        $b_hiyo_money[3][9] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7538' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][9] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][9] = 0;
    } else {
        $hiyo_money[3][9] = $res[0][0];
    }
}
// 賃借料
// NK計算
// 前月
// NKのみ回収以外と回収を分ける(消費税除外する為)
// 回収以外
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][10] = 0;
    } else {
        $b_hiyo_money[0][10] = $res[0][0];
    }
}
// 回収のみ
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[0][10] += 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[0][10] += 0;
    } else {
        $b_hiyo_money[0][10] += round($res[0][0]/1.08);
    }
}
// 当月
// NKのみ回収以外と回収を分ける(消費税除外する為)
// 回収以外
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id<>'C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][10] = 0;
    } else {
        $hiyo_money[0][10] = $res[0][0];
    }
}
// 回収のみ
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='7540' and den_id='C' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[0][10] += 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[0][10] += 0;
    } else {
        $hiyo_money[0][10] += round($res[0][0]/1.08);
    }
}
// SNK計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[1][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[1][10] = 0;
    } else {
        $b_hiyo_money[1][10] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[1][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[1][10] = 0;
    } else {
        $hiyo_money[1][10] = $res[0][0];
    }
}
// MT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[2][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[2][10] = 0;
    } else {
        $b_hiyo_money[2][10] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[2][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[2][10] = 0;
    } else {
        $hiyo_money[2][10] = $res[0][0];
    }
}
// NKIT計算
// 前月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $b_str_ymd, $b_end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $b_hiyo_money[3][10] = 0;
} else {
    if ($res[0][0] == '') {
        $b_hiyo_money[3][10] = 0;
    } else {
        $b_hiyo_money[3][10] = $res[0][0];
    }
}
// 当月
$query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='7540' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    $hiyo_money[3][10] = 0;
} else {
    if ($res[0][0] == '') {
        $hiyo_money[3][10] = 0;
    } else {
        $hiyo_money[3][10] = $res[0][0];
    }
}

// 費用登録処理
// 共通設定
// SQL共有処理 得意先コード
$input_code   = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 科目名
$input_kamoku = array('支払利息','通信費','工場消耗品費','事務消耗品費','業務委託費','運賃荷造費','水道光熱費','試験研究費','地代家賃','倉敷料','賃借料');
$code_num     = 4;                  // 得意先コード数 4
$kamoku_num   = 11;                 // 科目数 11

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 登録なし insert 使用
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $b_hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// 登録あり update 使用
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $b_hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$kamoku_num; $c++) {
        $query_chk = getQueryStatement1($input_ym, $input_code[$r], $input_kamoku[$c]);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 登録なし insert 使用
            $query = getQueryStatement2($input_ym, $input_code[$r], $input_kamoku[$c], $hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        } else {
            ///// 登録あり update 使用
            $query = getQueryStatement3($input_ym, $input_code[$r], $input_kamoku[$c], $hiyo_money[$r][$c]);
            query_affected_trans($con, $query);
        }
    }
}

// 売上高・仕入高計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$input_div = array('C','L','T','SSL','NKB');            // 追加の場合はNKBを最後にする事
$code_num  = 4;                 // 得意先コード数 4
$div_num   = 5;                 // 科目数 5

$b_sum_money = 0;               // 前月商管以外の部門合計
$sum_money   = 0;               // 商管以外の部門合計
$sales_money = array();         // 保管用
$b_sales_money = array();       // 前月保管用
$total_money = array();         // 合計保管用
$b_total_money = array();       // 前月合計保管用

// 売上高データ取得
// 前月
for ($r=0; $r<$code_num; $r++) {
    $b_sum_money = 0;
    for ($c=0; $c<$div_num; $c++) {
        if( $r == 3 ) {     // NKIT の場合は必要ないので0にする。
            $b_sales_money[$r][$c] = 0;
            $b_total_money[$r]     = 0;
        } else {
            if( $c <> 4 ) {     // NKB以外 の場合
                $query_chk = getQueryStatement4($b_str_ymd, $b_end_ymd, $input_code[$r], $input_div[$c]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $b_sales_money[$r][$c] = 0;
                    $b_sum_money          += 0;
                } else {
                    if ($res[0][0] == '') {
                        $b_sales_money[$r][$c] = 0;
                        $b_sum_money          += 0;
                    } else {
                        $b_sales_money[$r][$c] = $res[0][0];
                        $b_sum_money          += $res[0][0];
                    }
                }
            } else {            // NKB の場合
                $query_chk = sprintf("SELECT round(sales_kei/1.08) FROM link_trans_sales WHERE  sales_code='%s' and sales_ym=%d", $input_code[$r], $b_target_ym);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $b_total_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $b_total_money[$r] = 0;
                    } else {
                        $b_total_money[$r] = $res[0][0];
                    }
                }
                $b_sales_money[$r][$c] = $b_total_money[$r] - $b_sum_money;
            }
        }
    }
}
// 当月
for ($r=0; $r<$code_num; $r++) {
    $sum_money = 0;
    for ($c=0; $c<$div_num; $c++) {
        if( $r == 3 ) {     // NKIT の場合は必要ないので0にする。
            $sales_money[$r][$c] = 0;
            $total_money[$r]     = 0;
        } else {
            if( $c <> 4 ) {     // NKB以外 の場合
                $query_chk = getQueryStatement4($str_ymd, $end_ymd, $input_code[$r], $input_div[$c]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $sales_money[$r][$c] = 0;
                    $sum_money          += 0;
                } else {
                    if ($res[0][0] == '') {
                        $sales_money[$r][$c] = 0;
                        $sum_money          += 0;
                    } else {
                        $sales_money[$r][$c] = $res[0][0];
                        $sum_money          += $res[0][0];
                    }
                }
            } else {            // NKB の場合
                $query_chk = sprintf("SELECT round(sales_kei/1.08) FROM link_trans_sales WHERE  sales_code='%s' and sales_ym=%d", $input_code[$r], $target_ym);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $total_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $total_money[$r] = 0;
                    } else {
                        $total_money[$r] = $res[0][0];
                    }
                }
                $sales_money[$r][$c] = $total_money[$r] - $sum_money;
            }
        }
    }
}

// 売上高登録
$kamoku = '売上高';
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$input_div = array('C','L','T','SSL','NKB','TOTAL');  // 追加の場合はTOTALを最後にする事
$code_num  = 4;                 // 得意先コード数 4
$div_num   = 6;                 // 科目数 6
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$div_num; $c++) {
        $query_chk = getQueryStatement5($input_ym, $input_code[$r], $kamoku);
        if ($c <> 5) {  // 合計以外の時
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// 登録なし insert 使用
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $b_sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// 登録あり update 使用
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $b_sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            }
        } else {        // 合計のとき
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// 登録なし insert 使用
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $b_total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// 登録あり update 使用
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $b_total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            }
        }
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    for ($c=0; $c<$div_num; $c++) {
        $query_chk = getQueryStatement5($input_ym, $input_code[$r], $kamoku);
        if ($c <> 5) {  // 合計以外の時
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// 登録なし insert 使用
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// 登録あり update 使用
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $sales_money[$r][$c], $kamoku);
                query_affected_trans($con, $query);
            }
        } else {        // 合計のとき
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                ///// 登録なし insert 使用
                $query = getQueryStatement6($input_ym, $input_code[$r], $input_div[$c], $total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            } else {
                ///// 登録あり update 使用
                $query = getQueryStatement7($input_ym, $input_code[$r], $input_div[$c], $total_money[$r], $kamoku);
                query_affected_trans($con, $query);
            }
        }
    }
}

// 仕入高データ取得
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('91111','01566','00040','05001');  // 91111:NK 01556:SNK 00040:MT 05001:NKIT
$code_num  = 4;                 // 得意先コード数 4

$purchase_money   = array();    // 保管用
$b_purchase_money = array();    // 前月保管用

// 前月
$payment_ym = $target_ym;
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement8($b_str_ymd, $b_end_ymd, $payment_ym, $input_code[$r]);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $b_purchase_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $b_purchase_money[$r] = 0;
        } else {
            $b_purchase_money[$r] = $res[0][0];
        }
    }
}
// 当月
$payment_ym = $n_target_ym;
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement8($str_ymd, $end_ymd, $payment_ym, $input_code[$r]);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $purchase_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $purchase_money[$r] = 0;
        } else {
            $purchase_money[$r] = $res[0][0];
        }
    }
}

// 仕入高登録

$kamoku = '仕入高';
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement9($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用
        $query = getQueryStatement10($input_ym, $input_code[$r], $b_purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement11($input_ym, $input_code[$r], $b_purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement9($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用
        $query = getQueryStatement10($input_ym, $input_code[$r], $purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement11($input_ym, $input_code[$r], $purchase_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}

// 買掛金・経費関連経歴計算
// 買掛金計算 仕入高データ取得 → ×1.08で当月の買掛金データへ
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 手形得意先
$tegata_code = array('001','005','004','101');  // 001:NK 005:SNK 004:MT 101:NKIT SNKとNKITは無いが得意先コードと合わせる為適当
$code_num    = 4;                 // 得意先コード数 4
$tegata_num  = 4;                 // 手形得意先コード数 4   // 実際は2つだが得意先コードに合わせる

$ap_zen_money   = array();    // 繰越保管用
$b_ap_zen_money = array();    // 前月繰越保管用
$ap_kei_money   = array();    // 計上保管用
$b_ap_kei_money = array();    // 前月計上保管用
$ap_kai_money   = array();    // 解消高保管用
$b_ap_kai_money = array();    // 前月解消高保管用
$ap_zan_money   = array();    // 残高保管用
$b_ap_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$account      = '3103';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    $kamoku = '買掛金';
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_ap_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_ap_zen_money[$r] = 0;
            } else {
                $b_ap_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_ap_zen_money[$r] = 12671410; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_ap_zen_money[$r] = 639416; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_ap_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_ap_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 仕入高データ → ×1.08で当月買掛発生高へ
    $kamoku = '仕入高';
    $query_chk = getQueryStatement13($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $b_ap_kei_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $b_ap_kei_money[$r] = 0;
        } else {
            if ($input_code[$r] == '00101') {               // NKITは税込み
                $b_ap_kei_money[$r] = $res[0][0];
            } else {
                $b_ap_kei_money[$r] = round($res[0][0] * 1.08);
            }
        }
    }
    // 相殺金額取得 → 当月解消高 SNKは取得しない為分ける、NKITは別SQL
    if ($input_code[$r] == '00005') {               // SNKはないので強制的に0
        $b_ap_kai_money[$r] = 0;
    } elseif ($input_code[$r] == '00101') {         // NKITはデータの場所が違うので別SQL
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_ap_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_ap_kai_money[$r] = 0;
            } else {
                $b_ap_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // NKとMT
        if ($input_code[$r] == '00004') {               // MTは4月の時データがないので強制
            if ($input_ym == 201704) {
                $b_ap_kai_money[$r] = 690569;
            } else {
                $query_chk = getQueryStatement14($b_str_ymd, $b_end_ymd, $tegata_code[$r]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $b_ap_kai_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $b_ap_kai_money[$r] = 0;
                    } else {
                        $b_ap_kai_money[$r] = $res[0][0];
                    }
                }
            }
        } else {
            $query_chk = getQueryStatement14($b_str_ymd, $b_end_ymd, $tegata_code[$r]);
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                $b_ap_kai_money[$r] = 0;
            } else {
                if ($res[0][0] == '') {
                    $b_ap_kai_money[$r] = 0;
                } else {
                    $b_ap_kai_money[$r] = $res[0][0];
                }
            }
        }
    }
    // 最後に残高計算
    $b_ap_zan_money[$r] = $b_ap_zen_money[$r] + $b_ap_kei_money[$r] - $b_ap_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    $kamoku = '買掛金';
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201704以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $ap_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $ap_zen_money[$r] = 0;
            } else {
                $ap_zen_money[$r] = $res[0][0];
            }
        }
    } else {                        // 前月が201703は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $ap_zen_money[$r] = 12671410; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $ap_zen_money[$r] = 639416; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $ap_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $ap_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    $kamoku = '仕入高';
    // 仕入高データ → ×1.08で当月買掛発生高へ
    $query_chk = getQueryStatement13($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        $ap_kei_money[$r] = 0;
    } else {
        if ($res[0][0] == '') {
            $ap_kei_money[$r] = 0;
        } else {
            if ($input_code[$r] == '00101') {               // NKITは税込み
                $ap_kei_money[$r] = $res[0][0];
            } else {
                $ap_kei_money[$r] = round($res[0][0] * 1.08);
            }
        }
    }
    // 相殺金額取得 → 当月解消高 SNKは取得しない為分ける、NKITは別SQL
    if ($input_code[$r] == '00005') {               // SNKはないので強制的に0
        $ap_kai_money[$r] = 0;
    } elseif ($input_code[$r] == '00101') {         // NKITはデータの場所が違うので別SQL
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='3103' and den_ymd>=%d and den_ymd<=%d", $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $ap_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $ap_kai_money[$r] = 0;
            } else {
                $ap_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // NKとMT
        if ($input_code[$r] == '00004') {               // MTは4月の時データがないので強制
            if ($input_ym == 201704) {
                $ap_kai_money[$r] = 690569;
            } else {
                $query_chk = getQueryStatement14($str_ymd, $end_ymd, $tegata_code[$r]);
                if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                    $ap_kai_money[$r] = 0;
                } else {
                    if ($res[0][0] == '') {
                        $ap_kai_money[$r] = 0;
                    } else {
                        $ap_kai_money[$r] = $res[0][0];
                    }
                }
            }
        } else {
            $query_chk = getQueryStatement14($str_ymd, $end_ymd, $tegata_code[$r]);
            if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
                $ap_kai_money[$r] = 0;
            } else {
                if ($res[0][0] == '') {
                    $ap_kai_money[$r] = 0;
                } else {
                    $ap_kai_money[$r] = $res[0][0];
                }
            }
        }
    }
    // 最後に残高計算
    $ap_zan_money[$r] = $ap_zen_money[$r] + $ap_kei_money[$r] - $ap_kai_money[$r];
}


// 買掛金登録
$kamoku = '買掛金';
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_ap_zen_money[$r], $b_ap_kei_money[$r], $b_ap_kai_money[$r], $b_ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_ap_zen_money[$r], $b_ap_kei_money[$r], $b_ap_kai_money[$r], $b_ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $ap_zen_money[$r], $ap_kei_money[$r], $ap_kai_money[$r], $ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $ap_zen_money[$r], $ap_kei_money[$r], $ap_kai_money[$r], $ap_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}

// 経費関連計算
// 未収入金計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // 得意先コード数 4

$input_zen_money   = array();    // 繰越保管用
$b_input_zen_money = array();    // 前月繰越保管用
$input_kei_money   = array();    // 計上保管用
$b_input_kei_money = array();    // 前月計上保管用
$input_kai_money   = array();    // 解消高保管用
$b_input_kai_money = array();    // 前月解消高保管用
$input_zan_money   = array();    // 残高保管用
$b_input_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$kamoku = '未収入金';
$account      = '1503';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_input_zen_money[$r] = 427126; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $input_zen_money[$r] = 427126; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// 未収入金登録
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 立替金計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // 得意先コード数 4

$input_zen_money   = array();    // 繰越保管用
$b_input_zen_money = array();    // 前月繰越保管用
$input_kei_money   = array();    // 計上保管用
$b_input_kei_money = array();    // 前月計上保管用
$input_kai_money   = array();    // 解消高保管用
$b_input_kai_money = array();    // 前月解消高保管用
$input_zan_money   = array();    // 残高保管用
$b_input_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$kamoku = '立替金';
$account      = '1505';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_input_zen_money[$r] = 12838542; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_input_zen_money[$r] = 60440; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_input_zen_money[$r] = 17320; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $input_zen_money[$r] = 12838542; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $input_zen_money[$r] = 60440; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $input_zen_money[$r] = 17320; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// 立替金登録
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 未払金計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // 得意先コード数 4

$input_zen_money   = array();    // 繰越保管用
$b_input_zen_money = array();    // 前月繰越保管用
$input_kei_money   = array();    // 計上保管用
$b_input_kei_money = array();    // 前月計上保管用
$input_kai_money   = array();    // 解消高保管用
$b_input_kai_money = array();    // 前月解消高保管用
$input_zan_money   = array();    // 残高保管用
$b_input_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$kamoku       = '未払金';
$account      = '3105';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// 未払金登録
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 未払費用計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // 得意先コード数 4

$input_zen_money   = array();    // 繰越保管用
$b_input_zen_money = array();    // 前月繰越保管用
$input_kei_money   = array();    // 計上保管用
$b_input_kei_money = array();    // 前月計上保管用
$input_kai_money   = array();    // 解消高保管用
$b_input_kai_money = array();    // 前月解消高保管用
$input_zan_money   = array();    // 残高保管用
$b_input_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$kamoku       = '未払費用';
$account      = '3224';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_input_zen_money[$r] = 1420678; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $input_zen_money[$r] = 1420678; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// 未払費用登録
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 有償支給未収入金計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // 得意先コード数 4

$input_zen_money   = array();    // 繰越保管用
$b_input_zen_money = array();    // 前月繰越保管用
$input_kei_money   = array();    // 計上保管用
$b_input_kei_money = array();    // 前月計上保管用
$input_kai_money   = array();    // 解消高保管用
$b_input_kai_money = array();    // 前月解消高保管用
$input_zan_money   = array();    // 残高保管用
$b_input_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$kamoku       = '有償支給未収入金';
$account      = '1302';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_input_zen_money[$r] = 13620603; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $input_zen_money[$r] = 13620603; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// 有償支給未収入金登録
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 前受金計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // 得意先コード数 4

$input_zen_money   = array();    // 繰越保管用
$b_input_zen_money = array();    // 前月繰越保管用
$input_kei_money   = array();    // 計上保管用
$b_input_kei_money = array();    // 前月計上保管用
$input_kai_money   = array();    // 解消高保管用
$b_input_kai_money = array();    // 前月解消高保管用
$input_zan_money   = array();    // 残高保管用
$b_input_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$kamoku       = '前受金';
$account      = '3221';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// 前受金登録
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 預り金計算
// 共通設定
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
$code_num    = 4;                 // 得意先コード数 4

$input_zen_money   = array();    // 繰越保管用
$b_input_zen_money = array();    // 前月繰越保管用
$input_kei_money   = array();    // 計上保管用
$b_input_kei_money = array();    // 前月計上保管用
$input_kai_money   = array();    // 解消高保管用
$b_input_kai_money = array();    // 前月解消高保管用
$input_zan_money   = array();    // 残高保管用
$b_input_zan_money = array();    // 前月残高保管用

// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $bb_target_ym;      // SQL共有処理 前月繰越高日付
$kamoku       = '預り金';
$account      = '3222';
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_zen_money[$r] = 0;
            } else {
                $b_input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $b_input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kei_money[$r] = 0;
            } else {
                $b_input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $b_str_ymd, $b_end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $b_input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $b_input_kai_money[$r] = 0;
            } else {
                $b_input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $b_input_zan_money[$r] = $b_input_zen_money[$r] + $b_input_kei_money[$r] - $b_input_kai_money[$r];
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
$zen_ym       = $b_target_ym;     // SQL共有処理 前月繰越高日付
for ($r=0; $r<$code_num; $r++) {
    // 前々月の残高を取得し、繰越金額へ
    if ($zen_ym > 201703) {         // 201705以降は通常計算
        $query_chk = getQueryStatement12($zen_ym, $input_code[$r], $kamoku);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_zen_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_zen_money[$r] = 0;
            } else {
                $input_zen_money[$r] = $res[0][0];
            }
        }
    } else {                                // 201704は前月のデータがないので強制入力
        if ($input_code[$r] == '00001') {   // NKの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00004') {   // MTの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else if ($input_code[$r] == '00101') {   // NKITの場合
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        } else {                                   // それ以外（現時点ではSNK）
            $input_zen_money[$r] = 0; // 201704の前月繰越高
        }
    }
    // 当月発生高取得
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=1", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kei_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kei_money[$r] = 0;
            } else {
                $input_kei_money[$r] = $res[0][0];
            }
        }
    }
    // 当月解消高
    if ($input_code[$r] == '00005') {               // SNK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_snk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00101') {         // NKIT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nkit WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } elseif ($input_code[$r] == '00001') {         // NK
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_nk WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    } else {                                    // MT
        $query_chk = sprintf("SELECT -SUM(den_kin) FROM link_trans_expense_mt WHERE den_account='%s' and den_ymd>=%d and den_ymd<=%d and den_loan=2", $account, $str_ymd, $end_ymd);
        if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            $input_kai_money[$r] = 0;
        } else {
            if ($res[0][0] == '') {
                $input_kai_money[$r] = 0;
            } else {
                $input_kai_money[$r] = $res[0][0];
            }
        }
    }
    // 最後に残高計算
    $input_zan_money[$r] = $input_zen_money[$r] + $input_kei_money[$r] - $input_kai_money[$r];
}

// 預り金登録
// 取得に合わせて以下は変更すること
// SQL共有処理 得意先コード
$input_code = array('00001','00005','00004','00101');  // 00001:NK 00005:SNK 00004:MT 00101:NKIT
// SQL共有処理 部門名
$code_num  = 4;                 // 得意先コード数 4
// 前月
$input_ym     = $b_target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $b_input_zen_money[$r], $b_input_kei_money[$r], $b_input_kai_money[$r], $b_input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
// 当月
$input_ym     = $target_ym;       // SQL共有処理 日付の格納
for ($r=0; $r<$code_num; $r++) {
    $query_chk = getQueryStatement15($input_ym, $input_code[$r], $kamoku);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 登録なし insert 使用 
        $query = getQueryStatement16($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    } else {
        ///// 登録あり update 使用
        $query = getQueryStatement17($input_ym, $input_code[$r], $input_zen_money[$r], $input_kei_money[$r], $input_kai_money[$r], $input_zan_money[$r], $kamoku);
        query_affected_trans($con, $query);
    }
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

    ///// 収益・費用関連SQLステートメント取得
    // 重複チェックSQL
    function getQueryStatement1($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        revenue_money
                    FROM
                        link_trans_revenue_history
                    WHERE revenue_ym={$input_ym} and revenue_code='{$input_code}' and revenue_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement2($input_ym, $input_code, $input_kamoku, $input_money)
    {
        $query = "INSERT INTO 
                        link_trans_revenue_history (revenue_code, revenue_ym, revenue_kamoku, revenue_money)
                    VALUES(
                        '{$input_code}',
                         {$input_ym} ,
                        '{$input_kamoku}',
                         {$input_money})
        ";
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement3($input_ym, $input_code, $input_kamoku, $input_money)
    {
        $query = "UPDATE 
                        link_trans_revenue_history 
                    SET
                        revenue_money = {$input_money}
                    WHERE revenue_ym={$input_ym} and revenue_code='{$input_code}' and revenue_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    ///// 売上・仕入SQLステートメント取得
    // 売上高データ取得SQL
    function getQueryStatement4($d_start, $d_end, $customer, $div)
    {
        $query = "select
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";

        $search = "where 計上日>=$d_start and 計上日<=$d_end";
        $search .= " and 得意先='{$customer}'";
        if ($div == "C") {
            $search .= " and 事業部='$div'";
            $search .= " and (assyno not like 'NKB%%')";
            $search .= " and (assyno not like 'SS%%')";
        } elseif ($div == "L") {
            $search .= " and 事業部='$div'";
            $search .= " and (assyno not like 'SS%%')";
        } elseif ($div == "SSL") {   // リニア試験・修理の場合は assyno でチェック
            $search .= " and 事業部='L' and (assyno like 'SS%%')";
        } elseif ($div != " ") {
            $search .= " and 事業部='$div'";
        }
        $query = sprintf("$query %s", $search);     // SQL query 文の完成
        return $query;
    }
    // 重複チェックSQL
    function getQueryStatement5($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        sales_c_money
                    FROM
                        link_trans_sales_history
                    WHERE sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement6($input_ym, $input_code, $input_div, $input_money, $kamoku)
    {
        if ($input_div == 'C') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_c_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'L') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_l_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'T') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_t_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'SSL') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_s_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'NKB') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_b_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_div == 'TOTAL') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        }
        
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement7($input_ym, $input_code, $input_div, $input_money, $kamoku)
    {
        if ($input_div == 'C') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_c_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'L') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_l_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'T') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'SSL') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_s_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'NKB') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_b_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_div == 'TOTAL') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        }
        
        return $query;
    }
    // 仕入高データ取得SQL
    function getQueryStatement8($d_start, $d_end, $payment_ym, $customer)
    {
        $query = "SELECT
                        SUM(Uround(order_price * siharai,0))
                    FROM
                        act_payable AS a
                    LEFT OUTER JOIN
                        vendor_master USING(vendor)
                    LEFT OUTER JOIN
                        miitem ON (parts_no = mipn)
                    LEFT OUTER JOIN
                        parts_stock_master AS m ON (m.parts_no=a.parts_no)
        ";

        $search = "WHERE uke_date>=$d_start and uke_date<=$d_end and h_pay_date=$payment_ym and vendor='{$customer}'";
        $query = sprintf("$query %s", $search);     // SQL query 文の完成
        return $query;
    }
    // 重複チェックSQL
    function getQueryStatement9($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        sales_c_money
                    FROM
                        link_trans_sales_history
                    WHERE sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement10($input_ym, $input_code, $input_money, $kamoku)
    {
        if ($input_code == '00001') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money})
            ";
        } elseif($input_code == '00005') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_t_money, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money} ,
                            {$input_money})
            ";
        } elseif($input_code == '00004') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_t_money, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money} ,
                            {$input_money})
            ";
        } elseif($input_code == '00101') {
            $query = "INSERT INTO 
                            link_trans_sales_history (sales_code, sales_ym, sales_kamoku, sales_c_money, sales_to_money)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_money} ,
                            {$input_money})
            ";
        }
        
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement11($input_ym, $input_code, $input_money, $kamoku)
    {
        if ($input_code == '00001') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_code == '00005') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money  = {$input_money},
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_code == '00004') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_t_money  = {$input_money},
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        } elseif($input_code == '00101') {
            $query = "UPDATE 
                            link_trans_sales_history
                        SET
                            sales_c_money  = {$input_money},
                            sales_to_money = {$input_money}
                        WHERE  sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$kamoku}'
            ";
        }
        
        return $query;
    }
    // 買掛金・経費関連経歴
    // 前月の残高を取得で繰越高とする
    function getQueryStatement12($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        expense_zan
                    FROM
                        link_trans_expense_history
                    WHERE expense_ym={$input_ym} and expense_code='{$input_code}' and expense_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    // 仕入高取得 → 1.08で買掛金へSQL
    function getQueryStatement13($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        sales_to_money
                    FROM
                        link_trans_sales_history
                    WHERE sales_ym={$input_ym} and sales_code='{$input_code}' and sales_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // 買掛金相殺額取得
    function getQueryStatement14($str_ymd, $end_ymd, $input_code)
    {
       $query = "SELECT
                        -SUM(den_kin) 
                    FROM link_trans_offset
                    WHERE den_code='{$input_code}' and den_ymd>={$str_ymd} and den_ymd<={$end_ymd}
        ";
        return $query;
    }
    // 重複チェックSQL
    function getQueryStatement15($input_ym, $input_code, $input_kamoku)
    {
       $query = "SELECT
                        expense_zan
                    FROM
                        link_trans_expense_history
                    WHERE expense_ym={$input_ym} and expense_code='{$input_code}' and expense_kamoku='{$input_kamoku}'
        ";
        return $query;
    }
    
    // insert SQL
    function getQueryStatement16($input_ym, $input_code, $input_kuri, $input_kei, $input_kai, $input_zan, $kamoku)
    {
        $query = "INSERT INTO 
                            link_trans_expense_history (expense_code, expense_ym, expense_kamoku, expense_kuri, expense_kei, expense_kai, expense_zan)
                        VALUES(
                            '{$input_code}',
                             {$input_ym} ,
                            '{$kamoku}',
                            {$input_kuri},
                            {$input_kei},
                            {$input_kai},
                            {$input_zan})
        ";
        
        return $query;
    }
    // UPDATE SQL
    function getQueryStatement17($input_ym, $input_code, $input_kuri, $input_kei, $input_kai, $input_zan, $kamoku)
    {
        $query = "UPDATE 
                            link_trans_expense_history
                        SET
                            expense_kuri = {$input_kuri},
                            expense_kei  = {$input_kei},
                            expense_kai  = {$input_kai},
                            expense_zan  = {$input_zan}
                        WHERE  expense_ym={$input_ym} and expense_code='{$input_code}' and expense_kamoku='{$kamoku}'
        ";
        
        return $query;
    }
?>
