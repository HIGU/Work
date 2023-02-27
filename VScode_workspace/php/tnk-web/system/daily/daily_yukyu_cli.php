#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 有給取得情報日報(daily)処理   AS/400 UKWLIB/W#HPKYDY                     //
// RUNQRY     QRY(UKPLIB/Q#HPKYDY)                                          //
// \FTPTNK    USER(AS400) ASFILE(W#HPKYDY) LIB(UKWLIB)                      //
//            PCFILE(W#HPKYDY.TXT) MODE(TXT)                                //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2015-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2015/01/29 新規作成 aden_daily_cli.phpを改造 daily_yukyu_cli.php         //
// 2015/02/18 daily_cli.phpに組み込む為、AS/400コマンド等を追加             //
// 2015/03/16 パート・社員の労働時間追加                                    //
// 2018/09/25 社員の勤務時間がASで追加されたらフィールド数を14にして        //
//            data[12]が9だったらdata[13]をdata[11]に入れるようにする       //
//            作成済なので2018/09/25で検索してコメント解除                  //
// 2018/11/02 契約社員の勤務時間はdata[11]に組み込んだので上記コメント解除  //
//            は不要                                                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "有給取得情報の更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_yukyu_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 有給の更新 db_connect() error \n");
    fwrite($fpb, "$log_date 有給の更新 db_connect() error \n");
    echo "$log_date 有給の更新 db_connect() error \n\n";
    exit();
}
///////// 有給情報ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#HPKYDY.TXT';
$file_backup = '/home/guest/daily/backup/W#HPKYDY-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-HPKYDY.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '_', '`');     // 実レコードは75バイト デリミタは '_'アンダースコア field囲い子は'`'バッククォート
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        
        /* 2018/09/25 後でコメント解除 かわりに13の方をコメント化 ⇒ 不要
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
            continue;
        }
        */
        
        if ($num != 13) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 13 record=$rec \n");
            fwrite($fpb, "$log_date field not 13 record=$rec \n");
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
        $data[11] = $data[11] * 1;
        $data[12] = $data[12] * 1;
        
        /* 2018/09/25 後でコメント解除 ⇒ 不要
        $data[13] = $data[13] * 1;
        if ($data[12] == 9) {
            $data[11] = $data[13];
        }
        */
        
        $query_chk = sprintf("SELECT uid FROM paid_holiday_master WHERE uid='%s' and ki=%d", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO paid_holiday_master (uid, ki, before_day, current_day, day_holiday, half_holiday,
                      time_holiday, total_holiday, update_ym, str_ymd, end_ymd, work_time_p, work_time_s)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                      '{$data[2]}',
                      '{$data[3]}',
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                       {$data[10]} ,
                       {$data[11]} ,
                       {$data[12]})";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE paid_holiday_master SET
                            uid           ='{$data[0]}',
                            ki            = {$data[1]} ,
                            before_day    ='{$data[2]}',
                            current_day   ='{$data[3]}',
                            day_holiday   ='{$data[4]}',
                            half_holiday  = {$data[5]} ,
                            time_holiday  = {$data[6]} ,
                            total_holiday ='{$data[7]}',
                            update_ym     = {$data[8]} ,
                            str_ymd       = {$data[9]} ,
                            end_ymd       = {$data[10]},
                            work_time_p   = {$data[11]},
                            work_time_s   = {$data[12]}
                      where uid='{$data[0]}' and ki={$data[1]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 有給の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 有給の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 有給の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 有給の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 有給の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 有給の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 有給の更新 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 有給の更新 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 有給の更新 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : 有給情報の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 有給情報の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 有給情報の更新ファイル {$file_orign} がありません！\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
