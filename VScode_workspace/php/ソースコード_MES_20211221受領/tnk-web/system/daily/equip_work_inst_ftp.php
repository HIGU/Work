#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 内作指示ヘッダー・工程明細ファイル FTP Download cronで処理予定 cgi版     //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/06/29 Created  equip_work_inst_ftp.php                              //
// 2004/07/29 "rollback" → 'rollback' へ変更  4.3.7→4.3.8へ変更           //
// 2004/11/18 php-5.0.2-cliへバージョンアップ *シェルスクリプトに対応に変更 //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2010/01/20 $log_dateの前後は'ではなく"なので修正                    大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "内作指示のヘッダーと明細データの更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/equip_work_inst_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/equip_work_inst_ftp.php\n";
        
// FTPのターゲットファイル
define('D_MGIPRD', 'UKWLIB/W#MGIPRD');      // 内作指示ヘッダーファイル download file
define('D_MGIROT', 'UKWLIB/W#MGIROT');      // 内作指示工程明細ファイル download file
// 保存先のディレクトリとファイル名
define('S_MGIPRD', '/home/www/html/tnk-web/system/backup/W#MGIPRD.TXT');  // 内作指示ヘッダーファイル save file
define('S_MGIROT', '/home/www/html/tnk-web/system/backup/W#MGIROT.TXT');  // 内作指示工程明細ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 内作指示ヘッダーファイル
        if (ftp_get($ftp_stream, S_MGIPRD, D_MGIPRD, FTP_ASCII)) {
            echo "$log_date 内作指示ヘッダー ftp_get download OK ", D_MGIPRD, "→", S_MGIPRD, "\n";
            fwrite($fpa,"$log_date 内作指示ヘッダー ftp_get download OK " . D_MGIPRD . '→' . S_MGIPRD . "\n");
            fwrite($fpb,"$log_date 内作指示ヘッダー ftp_get download OK " . D_MGIPRD . '→' . S_MGIPRD . "\n");
        } else {
            echo "$log_date 内作指示ヘッダー ftp_get() error ", D_MGIPRD, "\n";
            fwrite($fpa,"$log_date 内作指示ヘッダー ftp_get() error " . D_MGIPRD . "\n");
            fwrite($fpb,"$log_date 内作指示ヘッダー ftp_get() error " . D_MGIPRD . "\n");
        }
        ///// 内作指示工程明細ファイル
        if (ftp_get($ftp_stream, S_MGIROT, D_MGIROT, FTP_ASCII)) {
            echo "$log_date 内作指示工程明細 ftp_get download OK ", D_MGIROT, "→", S_MGIROT, "\n";
            fwrite($fpa,"$log_date 内作指示工程明細 ftp_get download OK " . D_MGIROT . '→' . S_MGIROT . "\n");
            fwrite($fpb,"$log_date 内作指示工程明細 ftp_get download OK " . D_MGIROT . '→' . S_MGIROT . "\n");
        } else {
            echo "$log_date 内作指示工程明細 ftp_get() error ", D_MGIROT, "\n";
            fwrite($fpa,"$log_date 内作指示工程明細 ftp_get() error " . D_MGIROT . "\n");
            fwrite($fpb,"$log_date 内作指示工程明細 ftp_get() error " . D_MGIROT . "\n");
        }
    } else {
        echo "$log_date 内作指示工程明細 ftp_login() error \n";
        fwrite($fpa,"$log_date 内作指示工程明細 ftp_login() error \n");
        fwrite($fpb,"$log_date 内作指示工程明細 ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> 内作指示\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 内作指示\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 内作指示\n");
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 内作指示工程明細 db_connect() error \n");
    fwrite($fpb, "$log_date 内作指示工程明細 db_connect() error \n");
    echo "$log_date 内作指示工程明細 db_connect() error \n";
    exit();
}
// 内作指示ヘッダーファイル 準備作業
$file_orign  = S_MGIPRD;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MGIPRD.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // 実レコードは69バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 8) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 8 record=$rec \n");
                fwrite($fpb, "$log_date field not 8 record=$rec \n");
                echo "$log_date field not 8 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
        $query_chk = sprintf("SELECT parts_no FROM equip_work_inst_header
                                WHERE inst_no=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO equip_work_inst_header (inst_no, parts_no, material, inst_date, delivery, inst_qt,
                            mate_kg, sei_no)
                      VALUES(
                         {$data[0]} ,
                        '{$data[1]}',
                        '{$data[2]}',
                         {$data[3]} ,
                         {$data[4]} ,
                         {$data[5]} ,
                         {$data[6]} ,
                         {$data[7]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 指示番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
            $query = "UPDATE equip_work_inst_header SET
                            inst_no     = {$data[0]} ,
                            parts_no    ='{$data[1]}',
                            material    ='{$data[2]}',
                            inst_date   = {$data[3]} ,
                            delivery    = {$data[4]} ,
                            inst_qt     = {$data[5]} ,
                            mate_kg     = {$data[6]} ,
                            sei_no      = {$data[7]} 
                WHERE inst_no={$data[0]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 指示番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
    fwrite($fpa, "$log_date 内作指示ヘッダーの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 内作指示ヘッダーの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 内作指示ヘッダーの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 内作指示ヘッダーの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 内作指示ヘッダーの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 内作指示ヘッダーの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 内作指示ヘッダーの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 内作指示ヘッダーの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 内作指示ヘッダーの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
// 内作指示工程明細ファイル 準備作業
$file_orign  = S_MGIROT;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MGIROT.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // 実レコードは46バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 8) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 8 record=$rec \n");
                fwrite($fpb, "$log_date field not 8 record=$rec \n");
                echo "$log_date field not 8 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        
        $query_chk = sprintf("SELECT parts_no FROM equip_work_instruction
                                WHERE inst_no=%d and koutei=%d",
                                $data[0], $data[2]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO equip_work_instruction (inst_no, koutei, parts_no, pro_mark
                                    , prog_deg, pro_cost, mac_no)
                      VALUES(
                         {$data[0]} ,
                         {$data[2]} ,
                        '{$data[1]}',
                        '{$data[3]}',
                         {$data[4]} ,
                         {$data[5]} ,
                         {$data[6]}{$data[7]}
                       )";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 指示番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
            $query = "UPDATE equip_work_instruction SET
                            inst_no     = {$data[0]} ,
                            koutei      = {$data[2]} ,
                            parts_no    ='{$data[1]}',
                            pro_mark    ='{$data[3]}',
                            prog_deg    = {$data[4]} ,
                            pro_cost    = {$data[5]} ,
                            mac_no      = {$data[6]}{$data[7]}
                WHERE inst_no={$data[0]} and koutei={$data[2]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 指示番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 指示番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
                // query_affected_trans($con, 'rollback');     // transaction rollback
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
    fwrite($fpa, "$log_date 内作指示工程明細の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 内作指示工程明細の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 内作指示工程明細の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 内作指示工程明細の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 内作指示工程明細の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 内作指示工程明細の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 内作指示工程明細の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 内作指示工程明細の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 内作指示工程明細の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');

fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
