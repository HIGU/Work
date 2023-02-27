#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q → php-4.3.7-cgi -q → php(最新)へ     //
// 発注計画ファイル(AS/400 UKWLIB/W#MIOPLN)の自動更新用                     //
//                                          AS/400 ----> Web Server (PHP)   //
// Copyright (C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/20 Created  order_plan_get_ftp.php                               //
//            http → cli版へ変更出来るように requier_once を絶対指定へ     //
// 2004/01/05 field数が19の場合があるため チェックを20→19に変更            //
// 2004/03/24 php-4.3.5RC4 にしたためか？ AS/400 del record のロジック追加  //
// 2004/04/05 header('Location: http:' . WEB_HOST . ACT -->                 //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/04/09 php-4.3.6RC2で更にfgetcsv()の仕様が変更され$numのチェック変更 //
// 2004/06/07 php-4.3.6-cgi -q → php-4.3.7-cgi -q  バージョンアップ        //
// 2005/04/28 AS/400データの削除レコードに対応 製造番号が無い物を削除       //
//            最後のレコード(製造番号)以降を削除対象にしていないのはAS/400の//
//            転送時にエラーが起こり途中のレコードで処理される場合を想定    //
// 2005/05/07 経理メニューで httpで実行していた物を CLI版へ変更             //
// 2005/05/11 コマンドライン出力用の echo 文をコメントアウト                //
// 2006/04/26 大量更新のため BEGIN COMMIT をコメント                        //
// 2006/11/08 checkTableChange()を追加してデータが変更されている物のみ更新へ//
// 2017/06/12 注文後の内示データの削除を追加                           大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);        // 最大実行時間 1200=20分
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        // 日報用ログファイルへの書込みでオープン

/////////// Download file AS/400 & Save file
// FTPのターゲットファイル
$target_file = 'UKWLIB/W#MIOPLN';           // 発注計画ファイル download file
// 保存先のディレクトリとファイル名
$save_file = '/home/www/html/tnk-web/system/backup/W#MIOPLN.TXT';   // 発注計画ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            // echo '$log_date ftp_get download OK ', $target_file, '→', $save_file, "\n";
            fwrite($fpa,"$log_date ftp_get download 成功 $target_file → $save_file \n");
        } else {
            // echo '$log_date ftp_get() error ', $target_file, "\n";
            fwrite($fpa,"$log_date ftp_get() error $target_file \n");
        }
    } else {
        // echo "$log_date ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "$log_date ftp_connect() error --> 発注工程明細\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 発注計画 \n");
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    // echo "$log_date db_connect() error \n";
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
// 発注計画 日報処理 準備作業
$file_orign  = $save_file;
$file_backup = '/home/www/html/tnk-web/system/backup/W#MIOPLN-BAK.TXT';
$file_test   = '/home/www/html/tnk-web/system/debug/debug-MIOPLN.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $noChg  = 0;    // 未変更カウンター
    $pre_sei_no = '9999999';    // 前の製造番号(初期値は9999999で対象外にする)
    $del_ok = 0;    // DELETE用カウンター
    $del2_ok = 0;   // DELETE用カウンター(発注工程明細)
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '_');     // 実レコードは129バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num == 19) {
            $data[19] = '';     // フィールド数は通常20だが19の時があるため、その対応で20個目のフィールドを定義
        } elseif ($num < 19) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                // echo "$log_date AS/400 del record=$rec \n";
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                // echo "$log_date field error 19 LT record=$rec \n";
                fwrite($fpa, "$log_date field error record=$rec  num=$num \n");
            }
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
        $query_chk = sprintf("SELECT * FROM order_plan WHERE sei_no=%d", $data[0]);
        if (getResultTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO order_plan (sei_no, order5, parts_no, mecin, so_kubun, order_q,
                      utikiri, nyuko, plan_date, last_delv, order_ku, plan_cond, locate, zan_q, div,
                      tan_no, kouji_no, org_delv, hakou, kubun)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                      '{$data[2]}',
                      '{$data[3]}',
                      '{$data[4]}',
                       {$data[5]} ,
                       {$data[6]} ,
                       {$data[7]} ,
                       {$data[8]} ,
                       {$data[9]} ,
                      '{$data[10]}',
                      '{$data[11]}',
                      '{$data[12]}',
                       {$data[13]} ,
                      '{$data[14]}',
                      '{$data[15]}',
                      '{$data[16]}',
                       {$data[17]} ,
                       {$data[18]} ,
                      '{$data[19]}' )";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                // echo "$log_date {$rec}:レコード目のInsertに失敗しました!\n";
                fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目のInsertに失敗しました!\n");
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
            if (checkTableChange($data, $res_chk[0])) {
                $noChg++;
                // AS/400内で削除された物の処理のため continue をコメントにする
                // continue;
            } else {
                ///// 登録あり update 使用
                $query = "UPDATE order_plan SET sei_no={$data[0]}, order5={$data[1]}, parts_no='{$data[2]}',
                          mecin='{$data[3]}', so_kubun='{$data[4]}', order_q={$data[5]}, utikiri={$data[6]},
                          nyuko={$data[7]}, plan_date={$data[8]}, last_delv={$data[9]}, order_ku='{$data[10]}',
                          plan_cond='{$data[11]}', locate='{$data[12]}', zan_q={$data[13]},
                          div='{$data[14]}', tan_no='{$data[15]}', kouji_no='{$data[16]}', org_delv={$data[17]},
                          hakou={$data[18]}, kubun='{$data[19]}'
                    where sei_no={$data[0]}";
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    // echo "$log_date {$rec}:レコード目のUPDATEに失敗しました!\n";
                    fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
        ///// AS/400内で削除された物の処理
        $del_sql = "DELETE FROM order_plan WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del_rec = query_affected_trans($con, $del_sql)) < 0) {     // 更新用クエリーの実行
            // echo "$log_date {$rec}:レコード目のDELETEに失敗しました!\n";
            fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目のDELETEに失敗しました!\n");
        } else {
            $del_ok += $del_rec;
        }
        ///// 発注工程明細の削除処理
        $del_sql = "DELETE FROM order_process WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del2_rec = query_affected_trans($con, $del_sql)) < 0) {     // 更新用クエリーの実行
            // echo "$log_date {$rec}:レコード目 発注工程明細のDELETEに失敗しました!\n";
            fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目 発注工程明細のDELETEに失敗しました!\n");
        } else {
            $del2_ok += $del2_rec;
        }
        /*
        ///// 内示中の削除処理（追加 2017/06/12）
        $chk_sql = "SELECT * FROM order_process WHERE sei_no = {$data[0]} and plan_cond='O'";
        if (($chk_rec = query_affected_trans($con, $chk_sql)) < 0) {     // 更新用クエリーの実行
            // echo "$log_date {$rec}:レコード目 発注工程明細のDELETEに失敗しました!\n";
            //fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目 発注工程明細のDELETEに失敗しました!\n");
        } else {
            //$del2_ok += $del2_rec;
            $chk2_sql = "SELECT * FROM order_process WHERE sei_no = {$data[0]} and plan_cond='R'";
            if (($chk2_rec = query_affected_trans($con, $chk2_sql)) < 0) {     // 更新用クエリーの実行
            } else {
                $del2_sql = "DELETE FROM order_process WHERE sei_no = {$data[0]} AND plan_cond='R'";
                if (($del2_rec = query_affected_trans($con, $del2_sql)) < 0) {     // 更新用クエリーの実行
                } else {
                }
            }
        }
        */
        $pre_sei_no = $data[0];     // 次の処理のため保存
    }
    fclose($fp);
    fclose($fpw);       // debug
    // echo "$log_date 発注計画の更新:{$rec_ok}/{$rec} 件更新しました。\n";
    // echo "$log_date 発注計画の更新:{$ins_ok}/{$rec} 件 追加\n";
    // echo "$log_date 発注計画の更新:{$upd_ok}/{$rec} 件 変更\n";
    // echo "$log_date 発注計画の更新:{$del_ok}/{$rec} 件 削除\n";
    // echo "$log_date 発注工程明細の更新 {$del2_ok}/{$rec} 件 削除\n";
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : $rec_ok/$rec 件更新しました。\n");
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : {$noChg}/{$rec} 件 未変更 \n");
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : {$del_ok}/{$rec} 件 削除 \n");
    fwrite($fpa, "$log_date 発注工程明細の更新:{$data[0]} : {$del2_ok}/{$rec} 件 削除 \n");
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            // echo "$log_date DownLoad File $file_orign をBackupできません！\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
        }
    }
} else {
    // echo "$log_date 発注計画ファイル {$file_orign} がありません!\n";
    fwrite($fpa,"$log_date ファイル $file_orign がありません!\n");
}
/////////// commit トランザクション終了
// query_affected_trans($con, 'COMMIT');
fclose($fpa);      ////// 日報用ログ書込み終了

exit();

/***** テーブルが変更されている場合はfalseを返す     *****/
/***** 引数は比較するデータの配列とテーブルの配列   *****/
function checkTableChange($data, $res)
{
    for ($i=0; $i<20; $i++) {
        // 比較に邪魔をするスペースを削除
        if (trim($data[$i]) != trim($res[$i])) {
            return false;
        }
    }
    return true;
}

?>
