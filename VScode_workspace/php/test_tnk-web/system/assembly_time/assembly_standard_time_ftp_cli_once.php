#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 組立工数明細ファイル (MGUJTML) 取込 処理用 FTP CLI版                     //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created  assembly_standard_time_ftp_cli_once.php              //
// 2007/05/16 自動処理用に FTP CLI版 を作成                                 //
//            朝１番の１回のみの処理用に変更AS/400のデータを空にする処理追加//
// 2007/08/20 ftp_close($ftp_stream)→if($ftp_stream) ftp_close($ftp_stream)//
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/15 メールにメッセージが無かった為、echoを追加               大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ない

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "組立工数明細の更新処理及びAS/400側クリアー処理\n");
fwrite($fpb, "/var/www/html/system/assembly_time/assembly_standard_time_ftp_cli_once.php\n");
echo "組立工数明細の更新処理及びAS/400側クリアー処理\n";
echo "/var/www/html/system/assembly_time/assembly_standard_time_ftp_cli_once.php\n";

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#MGUJTM';           // AS/400ファイル download
// 保存先のディレクトリとファイル名
$save_file = "{$currentFullPathName}/backup/W#MGUJTM.TXT";     // download file の保存先

// コネクションを取る(FTP接続のオープン)
$ftp_flg = false;   // 転送の成功・失敗フラグ
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ターゲットファイル
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 組立工数明細 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            fwrite($fpb,"$log_date 組立工数明細 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            echo "$log_date 組立工数明細 ftp_get download OK " . $target_file . '→' . $save_file . "\n";
            $ftp_flg = true;
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 組立工数明細 ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date 組立工数明細 ftp_get() error " . $target_file . "\n");
            echo "$log_date 組立工数明細 ftp_get() error " . $target_file . "\n";
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date 組立工数明細 ftp_login() error \n");
        fwrite($fpb,"$log_date 組立工数明細 ftp_login() error \n");
        echo "$log_date 組立工数明細 ftp_login() error \n";
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ftp_connect() error --> 組立工数明細\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 組立工数明細\n");
    echo "$log_date ftp_connect() error --> 組立工数明細\n";
}
if (!$ftp_flg) {
    if ($ftp_stream) ftp_close($ftp_stream);
    fclose($fpa);      ////// 日報用ログ書込み終了
    fwrite($fpb, "------------------------------------------------------------------------\n");
    fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
    exit();
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');    // 大量登録用にはコメントアウト
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 組立工数明細 db_connect() error \n");
    fwrite($fpb, "$log_date 組立工数明細 db_connect() error \n");
    echo "$log_date 組立工数明細 db_connect() error \n";
    exit();
}
///// 準備作業
$file_orign  = $save_file;
$file_backup = "{$currentFullPathName}/backup/W#MGUJTM-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-MGUJTM.TXT";
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (($data = fgetcsv($fp, 100, '_')) !== FALSE) {
        ///// 実レコードは67バイトなのでちょっと余裕をデリミタは('_'アンダバー)
        $rec++;
        
        if ($data[0] == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            fwrite($fpb, "$log_date AS/400 del record=$rec \n");
            //echo "$log_date AS/400 del record=$rec \n";
            continue;
        }
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);          // "'"等がデータにある場合に\でエスケープする
            //if ($f == 1) {
            //    $data[$f] = mb_convert_kana($data[$f]); // 半角カナを全角カナに変換
            //}
        }
        
        $query_chk = "
            SELECT assy_no FROM assembly_standard_time
            WHERE assy_no='{$data[0]}' AND reg_no={$data[1]} AND pro_no={$data[2]}
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO assembly_standard_time
                    (assy_no, reg_no, pro_no, pro_mark, line_no, ext_no, ext_assy, assy_time, setup_time, man_count)
                VALUES(
                    '{$data[0]}',
                     {$data[1]} ,
                     {$data[2]} ,
                    '{$data[3]}',
                    '{$data[4]}',
                    '{$data[5]}',
                    '{$data[6]}',
                     {$data[7]} ,
                     {$data[8]} ,
                     {$data[9]} )
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ASSY番号:{$data[0]}, 登録番号:{$data[1]}, 工程番号:{$data[2]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date ASSY番号:{$data[0]}, 登録番号:{$data[1]}, 工程番号:{$data[2]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "
                UPDATE assembly_standard_time
                SET
                    assy_no     ='{$data[0]}',
                    reg_no      = {$data[1]} ,
                    pro_no      = {$data[2]} ,
                    pro_mark    ='{$data[3]}',
                    line_no     ='{$data[4]}',
                    ext_no      ='{$data[5]}',
                    ext_assy    ='{$data[6]}',
                    assy_time   = {$data[7]} ,
                    setup_time  = {$data[8]} ,
                    man_count   = {$data[9]}
                WHERE assy_no='{$data[0]}' AND reg_no={$data[1]} AND pro_no={$data[2]}
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ASSY番号:{$data[0]}, 登録番号:{$data[1]}, 工程番号:{$data[2]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date ASSY番号:{$data[0]}, 登録番号:{$data[1]}, 工程番号:{$data[2]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 組立工数明細の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 組立工数明細の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 組立工数明細の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 組立工数明細の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 組立工数明細の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 組立工数明細の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 組立工数明細の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 組立工数明細の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 組立工数明細の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
            fwrite($fpb,"$log_date DownLoad File $file_orign をBackupできません！\n");
            echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "$log_date ファイル$file_orign がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'COMMIT');    // 大量登録用にはコメントアウト
// echo $query . "\n";  // debug

////////// 書込みにＮＧが無ければ AS/400側のデータを空にする
if (!$rec_ng) {
    // 削除用ダミーファイルの指定
    $local_file = "{$currentFullPathName}/AS400_erase.txt";
    if (ftp_put($ftp_stream, $target_file, $local_file, FTP_ASCII)) {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        fwrite($fpa, "$log_date 組立工数明細 : AS/400のファイルを空にしました。\n");
        fwrite($fpb, "$log_date 組立工数明細 : AS/400のファイルを空にしました。\n");
        echo "$log_date 組立工数明細 : AS/400のファイルを空にしました。\n";
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        fwrite($fpa, "$log_date 組立工数明細 : AS/400のファイルを空に出来ませんでした。\n");
        fwrite($fpb, "$log_date 組立工数明細 : AS/400のファイルを空に出来ませんでした。\n");
        echo "$log_date 組立工数明細 : AS/400のファイルを空に出来ませんでした。\n";
    }
}

ftp_close($ftp_stream);     // FTP を閉じる
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
