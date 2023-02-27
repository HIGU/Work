<?php
//////////////////////////////////////////////////////////////////////////////
// NK無償支給品・客先支給品の更新   AS/400 UKWLIB/W#PROVID                  //
//   AS/400 ----> Web Server (PHP) FTP転送は不可 EBCDICの変換が出来ないため //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/08 Created  rovide_month_update.php                              //
//                                  inventory_month_update.phpを雛型に使用  //
//            http → cli版へ変更出来るように requier_once を絶対指定へ     //
//            AS/400 で RUNQRY QRY(UKPLIB/Q#PROVID) で実行し、端末で転送    //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2005/03/04 dir変更 /home/www/html/monthly/ → /home/guest/monthly/       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()で使用
access_log();                           // Script Name 自動取得
// $_SESSION['site_index'] = 20;           // 月次損益関係=10 最後のメニューは 99 を使用
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

/////////// 月次対象年月を取得
if (isset($_SESSION['act_ym'])) {
    $act_ym = $_SESSION['act_ym'];
} else {
    $_SESSION['s_sysmsg'] = '月次対象年月が指定されていません!';
    header('Location: ' . $act_referer);
    exit();
}

$log_date = date('Y-m-d H:i:s');                    ///// 日報用ログの日時
$fpa = fopen('/tmp/monthly_update.log', 'a');       ///// 日報用ログファイルへの書込みでオープン
$_SESSION['s_sysmsg'] = '';     // 初期化

/********************
/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#PROVID';
$save_file = '/home/www/html/monthly/W#PROVID.TXT';

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
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> 棚卸月次処理<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> 棚卸月次処理\n");
}
*********************/

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}
///////// 棚卸ファイルの更新 準備作業
$file_orign  = '/home/guest/monthly/W#PROVID.TXT';
$file_backup = 'backup/W#PROVID-BAK.TXT';
$file_test   = 'debug/debug-PROVID.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, "_");     // 実レコードは12バイト デリミタはタブ
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 2) {
            $_SESSION['s_sysmsg'] .= "field not 2 record=$rec <br>";
            fwrite($fpa, "$log_date field not 2 record=$rec \n");
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}'\n");     // debug
                }
                fwrite($fpw,"\n");                      // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
        $query_chk = sprintf("SELECT parts_no FROM provide_item WHERE reg_ym={$act_ym} and parts_no='%s'", $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO provide_item (reg_ym, parts_no, type)
                      VALUES(
                        $act_ym   ,
                      '{$data[0]}',
                      '{$data[1]}')";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $_SESSION['s_sysmsg'] .= "{$rec}:レコード目の書込みに失敗しました!<br>";
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE provide_item SET reg_ym={$act_ym}, parts_no='{$data[0]}', type='{$data[1]}'
                      where reg_ym={$act_ym} and parts_no='{$data[0]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $_SESSION['s_sysmsg'] .= "{$rec}:レコード目のUPDATEに失敗しました!\n";
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} 件登録しました。</font><br><br>";
    $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} 件 追加<br>";
    $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} 件 変更</font>";
    fwrite($fpa, "$log_date 無償支給品の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 無償支給品の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 無償支給品の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
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
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>トランザクションファイルがありません！</font>";
    fwrite($fpa,"$log_date : 無償支給品の更新ファイル {$file_orign} がありません！\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// 月次用ログ書込み終了

header('Location: ' . H_WEB_HOST . ACT . 'provide_month_view.php');   // チェックリストへ
// header('Location: http://masterst.tnk.co.jp/account/inventory_month_view.php');
// header('Location: ' . $act_referer);
exit();
?>
