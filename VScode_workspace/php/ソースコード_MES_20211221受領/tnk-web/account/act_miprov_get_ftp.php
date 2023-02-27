<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q → php-4.3.7-cgi -q                    //
// 日報データ 自動FTP Download  支給更新確定ファイル UKWLIB/W#MIPROV        //
//                                  AS/400 ----> Web Server (PHP)           //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/11/19 新規作成 act_miprov_get_ftp.php                               //
//            http → cli版へ変更出来るように requier_once を絶対指定へ     //
// 2003/11/20 uniqe key が無いため 更新日でチェックして insertのみに変更    //
// 2004/01/05 mb_convert_encodingの 'auto'→'SJIS' へ変更 $rec++の位置変更  //
// 2004/02/06 処理日(計上日)と日報日が同じかチェックを追加                  //
// 2004/04/05 header('Location: http:' . WEB_HOST . ACT -->                 //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/06/07 php-4.3.6-cgi -q → php-4.3.7-cgi -q  バージョンアップ        //
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

///// 日報処理日の取得
if (isset($_SESSION['act_ymd'])) {
    $yyyymmdd = $_SESSION['act_ymd'];
} else {
    $_SESSION['s_sysmsg'] = "日報処理日が指定されていません！";
    header('Location: ' . $act_referer);
    exit();
}

/////////// Download file AS/400 & Save file
$down_file = 'UKWLIB/W#MIPROV';
$save_file = 'W#MIPROV.TXT';

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
    $_SESSION['s_sysmsg'] .= 'ftp_connect() error --> 支給票 日報処理<br>';
    fwrite($fpa,"$log_date ftp_connect() error --> 支給票 日報処理\n");
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}

// 支給票 確定処理 日報処理 準備作業
$file_orign  = 'W#MIPROV.TXT';
$file_backup = 'backup/W#MIPROV-BAK.TXT';
$file_test   = 'debug/debug-MIPROV.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    ///////// ここで先に登録済みのチェック
    $data = fgetcsv($fp, 300, '_');     // 実レコードは187バイトなのでちょっと余裕をデリミタは'_'に注意
    $query_chk = sprintf("SELECT act_date FROM act_miprov WHERE act_date=%d ", $data[15]);
    if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // トランザクション内での 照会専用クエリー
        /////////// 日報日(更新日)で登録済みのチェック
        $_SESSION['s_sysmsg'] .= sprintf("支給票は既に確定済みです！<br>日報日：%s", format_date($data[15]) );
        fwrite($fpa, "$log_date 支給票は既に確定済みです！日報日{$data[15]}\n");
        query_affected_trans($con, "rollback");     // transaction rollback
        header('Location: ' . $act_referer);
        exit();
    }
    fclose($fp);
    $fp = fopen($file_orign, 'r');
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 300, '_');     // 実レコードは187バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 23) {
            $_SESSION['s_sysmsg'] .= "field not 23 record=$rec <br>";
            fwrite($fpa, "$log_date field not 23 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        ///// 日報日のチェック
        if ($data[15] != $yyyymmdd) {
            $_SESSION['s_sysmsg'] .= sprintf("データと日報処理日が違います！<br>日報日：%s", format_date($data[15]) );
            fwrite($fpa, "$log_date データと日報処理日が違います！日報日{$data[15]}\n");
            query_affected_trans($con, "rollback");     // transaction rollback
            header('Location: ' . $act_referer);
            exit();
        }
        
        ///////// 主キーを削除し 更新日のみのチェックとした Unique key が無いため
        // $query_chk = sprintf("SELECT act_date FROM act_miprov WHERE act_date=%d and _sei_no='%s'
        //                       and parts_no='%s' and prov_no='%s'", $data[15], $data[6], $data[7], $data[2]);
        // if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
        ///// 登録なし insert 使用
        $query = "INSERT INTO act_miprov (mpvpn, mpvpd, prov_no, prov_date, div, vendor,
                  _sei_no, parts_no, sozai, mecin, mtl_cond, require, prov, prov_tan,
                  delete, act_date, parts_name, note, zai_kubun, kamoku, r_kubun, gai_price, tax_kubun)
                  VALUES(
                  '{$data[0]}',
                   {$data[1]} ,
                  '{$data[2]}',
                   {$data[3]} ,
                  '{$data[4]}',
                  '{$data[5]}',
                  '{$data[6]}',
                  '{$data[7]}',
                  '{$data[8]}',
                  '{$data[9]}',
                  '{$data[10]}',
                   {$data[11]} ,
                   {$data[12]} ,
                   {$data[13]} ,
                  '{$data[14]}',
                   {$data[15]} ,
                  '{$data[16]}',
                  '{$data[17]}',
                  '{$data[18]}',
                   {$data[19]} ,
                  '{$data[20]}',
                   {$data[21]} ,
                  '{$data[22]}' )";
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            $_SESSION['s_sysmsg'] .= "{$rec}:レコード目の書込みに失敗しました!<br>";
            fwrite($fpa, "$log_date 計画番号:{$data[6]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            /***********************
        } else {
            ///// 登録あり update 使用
            $query = "UPDATE act_miprov SET mpvpn='{$data[0]}', mpvpd={$data[1]}, prov_no='{$data[2]}',
                      prov_date={$data[3]}, div='{$data[4]}', vendor='{$data[5]}', _sei_no='{$data[6]}',
                      parts_no='{$data[7]}', sozai='{$data[8]}', mecin='{$data[9]}', mtl_cond='{$data[10]}',
                      require={$data[11]}, prov={$data[12]}, prov_tan={$data[13]},
                      delete='{$data[14]}', act_date={$data[15]}, parts_name='{$data[16]}', note='{$data[17]}',
                      zai_kubun='{$data[18]}', kamoku={$data[19]}, r_kubun='{$data[20]}',
                      gai_price={$data[21]}, tax_kubun='{$data[22]}'
                where act_date={$data[15]} and _sei_no='{$data[6]}' and parts_no='{$data[7]}' and prov_no='{$data[2]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $_SESSION['s_sysmsg'] .= "{$rec}:レコード目のUPDATEに失敗しました!\n";
                fwrite($fpa, "$log_date 計画番号:{$data[6]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
            **************************/
    }
    fclose($fp);
    fclose($fpw);       // debug
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec_ok}/{$rec} 件登録しました。</font><br><br>";
    $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins_ok}/{$rec} 件 追加<br>";
    $_SESSION['s_sysmsg'] .= "{$upd_ok}/{$rec} 件 変更</font>";
    fwrite($fpa, "$log_date 支給票の更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 支給票の更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 支給票の更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
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
    $_SESSION['s_sysmsg'] .= "支給票ファイル {$file_orign} がありません!";
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了

header('Location: ' . H_WEB_HOST . ACT . 'act_miprov_view.php');   // チェックリストへ
// header('Location: http://masterst.tnk.co.jp/account/act_miprov_view.php');
// header('Location: ' . $act_referer);
exit();
?>
