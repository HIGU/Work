#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// Ａ伝情報(販売価格等)日報(daily)処理   AS/400 UKWLIB/W#MIADIM             //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2004-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/10/15 新規作成 aden_daily_cli.php aden_master_update.phpを改造      //
// 変更経歴   http → cli版へ変更出来るように requier_once を絶対指定へ     //
//            AS/400で RUNQRY QRY(UKPLIB/Q#MIADIM) で実行しそのまま更新する //
// 2003/11/28 ログをコメントにしていたのを monthly_update.log にして追加    //
// 2004/01/05 データに"がありfgetcsvのオプションで'`'フィールド囲い子を変更 //
// 2004/01/20 日報処理の元ファイルのディレクトリ変更 /home/guest/daily へ   //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/10/15 cronを使ったスケジュールへ変更                                //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2020/08/17 部品名に_が入っているものがあったので|に変更             大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "Ａ伝情報の更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_aden_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date Ａ伝の更新 db_connect() error \n");
    fwrite($fpb, "$log_date Ａ伝の更新 db_connect() error \n");
    echo "$log_date Ａ伝の更新 db_connect() error \n\n";
    exit();
}
///////// Ａ伝情報ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#MIADIM.TXT';
$file_backup = '/home/guest/daily/backup/W#MIADIM-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-MIADIM.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|', '`');     // 実レコードは117バイト デリミタは '|' field囲い子は'`'バッククォート
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
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
        
        $query_chk = sprintf("SELECT aden_no FROM aden_master WHERE aden_no='%s' and eda_no=%d", $data[0], $data[1]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO aden_master (aden_no, eda_no, parts_no, sale_name, plan_no, approval,
                      ropes_no, kouji_no, order_q, order_price, espoir_deli, delivery, publish_day)
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
                       {$data[12]} )";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date Ａ伝番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE aden_master SET
                            aden_no    ='{$data[0]}',
                            eda_no     = {$data[1]} ,
                            parts_no   ='{$data[2]}',
                            sale_name  ='{$data[3]}',
                            plan_no    ='{$data[4]}',
                            approval   ='{$data[5]}',
                            ropes_no   ='{$data[6]}',
                            kouji_no   ='{$data[7]}',
                            order_q    = {$data[8]} ,
                            order_price= {$data[9]} ,
                            espoir_deli= {$data[10]},
                            delivery   = {$data[11]},
                            publish_day= {$data[12]}
                      where aden_no='{$data[0]}' and eda_no={$data[1]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date Ａ伝番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date Ａ伝の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date Ａ伝の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date Ａ伝の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date Ａ伝の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date Ａ伝の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date Ａ伝の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date Ａ伝の更新 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date Ａ伝の更新 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date Ａ伝の更新 : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : Ａ伝情報の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : Ａ伝情報の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : Ａ伝情報の更新ファイル {$file_orign} がありません！\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
