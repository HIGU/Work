<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q → php-4.3.7-cgi -q → php(最新)へ     //
// 日報データ 自動FTP Download  発注計画ファイル UKWLIB/W#MIOPLN            //
//                                          AS/400 ----> Web Server (PHP)   //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
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
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間 1200=20分
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()で使用
access_log();                           // Script Name 自動取得
// $_SESSION['site_index'] = 20;           // 経理日報関係=20 月次損益関係=10 最後のメニューは 99 を使用
// $_SESSION['site_id']    = 10;           // 下位メニュー無し (0 <=)

//////////// 呼出元の取得
$act_referer = $_SESSION['act_referer'];

//////////// 認証チェック
if (account_group_check() == FALSE) {
    // $_SESSION['s_sysmsg'] = 'あなたは許可されていません!<br>管理者に連絡して下さい!';
    $_SESSION['s_sysmsg'] = "Accounting Group の権限が必要です！";
    header('Location: ' . $act_referer);
    exit();
}

$log_date = date('Y-m-d H:i:s');                ///// 日報用ログの日時
$fpa = fopen('/tmp/act_payable.log', 'a');      ///// 日報用ログファイルへの書込みでオープン
$_SESSION['s_sysmsg'] = '';     // 初期化

/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#MIOPLN';
$save_file = 'W#MIOPLN.TXT';

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
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> 発注計画<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> 発注計画 \n");
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
// 発注計画 日報処理 準備作業
$file_orign  = 'W#MIOPLN.TXT';
$file_backup = 'backup/W#MIOPLN-BAK.TXT';
$file_test   = 'debug/debug-MIOPLN.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
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
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>AS/400 del record=$rec </font><br>";
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                $_SESSION['s_sysmsg'] .= "field error record=$rec <br>";
                fwrite($fpa, "$log_date field error record=$rec  num=$num \n");
            }
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
        $query_chk = sprintf("SELECT sei_no FROM order_plan WHERE sei_no=%d", $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
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
                $_SESSION['s_sysmsg'] .= "{$rec}:レコード目の書込みに失敗しました!<br>";
                fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE order_plan SET sei_no={$data[0]}, order5={$data[1]}, parts_no='{$data[2]}',
                      mecin='{$data[3]}', so_kubun='{$data[4]}', order_q={$data[5]}, utikiri={$data[6]},
                      nyuko={$data[7]}, plan_date={$data[8]}, last_delv={$data[9]}, order_ku='{$data[10]}',
                      plan_cond='{$data[11]}', locate='{$data[12]}', zan_q={$data[13]},
                      div='{$data[14]}', tan_no='{$data[15]}', kouji_no='{$data[16]}', org_delv={$data[17]},
                      hakou={$data[18]}, kubun='{$data[19]}'
                where sei_no={$data[0]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $_SESSION['s_sysmsg'] .= "{$rec}:レコード目のUPDATEに失敗しました!\n";
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
        ///// AS/400内で削除された物の処理
        $del_sql = "DELETE FROM order_plan WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del_rec = query_affected_trans($con, $del_sql)) < 0) {     // 更新用クエリーの実行
            $_SESSION['s_sysmsg'] .= "{$rec}:レコード目のDELETEに失敗しました!\n";
            fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目のDELETEに失敗しました!\n");
        } else {
            $del_ok += $del_rec;
        }
        ///// 発注工程明細の削除処理
        $del_sql = "DELETE FROM order_process WHERE sei_no > {$pre_sei_no} AND sei_no < {$data[0]}";
        if (($del2_rec = query_affected_trans($con, $del_sql)) < 0) {     // 更新用クエリーの実行
            $_SESSION['s_sysmsg'] .= "{$rec}:レコード目 発注工程明細のDELETEに失敗しました!\n";
            fwrite($fpa, "$log_date 製造番号:{$data[0]} : {$rec}:レコード目 発注工程明細のDELETEに失敗しました!\n");
        } else {
            $del2_ok += $del2_rec;
        }
        $pre_sei_no = $data[0];     // 次の処理のため保存
    }
    fclose($fp);
    fclose($fpw);       // debug
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} 件更新しました。</font><br><br>";
    $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} 件 追加<br>";
    $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} 件 変更<br>";
    $_SESSION['s_sysmsg'] .= "{$del_ok}/{$rec} 件 削除<br>";
    $_SESSION['s_sysmsg'] .= "工程明細 {$del2_ok}/{$rec} 件 削除</font>";
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : $rec_ok/$rec 件更新しました。\n");
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 発注計画の更新:{$data[0]} : {$del_ok}/{$rec} 件 削除 \n");
    fwrite($fpa, "$log_date 発注工程明細の更新:{$data[0]} : {$del2_ok}/{$rec} 件 削除 \n");
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            $_SESSION['s_sysmsg'] .= "$log_date DownLoad File $file_orign をBackupできません！\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
        }
    }
} else {
    $_SESSION['s_sysmsg'] .= "発注計画ファイル {$file_orign} がありません!";
    fwrite($fpa,"$log_date ファイル $file_orign がありません!\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了

header('Location: ' . H_WEB_HOST . ACT . 'order_plan_view.php');   // チェックリストへ
// header('Location: http://masterst.tnk.co.jp/account/order_plan_view.php');
// header('Location: ' . $act_referer);
exit();
?>
