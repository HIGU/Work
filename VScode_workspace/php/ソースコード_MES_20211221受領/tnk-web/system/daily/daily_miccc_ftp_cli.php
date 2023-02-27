#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 日報データ(MICCC CC部品TNKCC部品) 自動FTP Download cronで処理用 cli版    //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/08 Created  daily_miccc_ftp_cli.php                              //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2010/01/20 $log_dateの前後は'では無く"なので修正                    大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "ＣＣ部品ＴＮＫＣＣ部品のテーブル更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_miccc_ftp_cli.php\n");

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#MICCC';        // download file
// 保存先のディレクトリとファイル名
$save_file = '/home/www/html/tnk-web/system/backup/W#MICCC.TXT';     // save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo "$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_get download OK ", $target_file, "→", $save_file, "\n";
            fwrite($fpa,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            fwrite($fpb,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
        } else {
            echo "$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_get() error ", $target_file, "\n";
            fwrite($fpa,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_login() error \n";
        fwrite($fpa,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_login() error \n");
        fwrite($fpb,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_connect() error --> MICCC\n";
    fwrite($fpa,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_connect() error --> MICCC\n");
    fwrite($fpb,"$log_date ＣＣ部品ＴＮＫＣＣ部品 ftp_connect() error --> MICCC\n");
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    echo "$log_date ＣＣ部品ＴＮＫＣＣ部品 db_connect() error \n";
    exit();
}
// MICCCファイル 日報処理 準備作業
$file_orign  = $save_file;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-MICCC.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_ok = 0;    // DELETE用カウンターOK
    $del_ng = 0;    // DELETE用カウンターNG
    $sql_del = 'DELETE FROM miccc';
    if ( ($del_ok=query_affected_trans($con, $sql_del)) <= 0) {
        fwrite($fpa, "$log_date MICCCの削除対象データがありません！\n");
        fwrite($fpb, "$log_date MICCCの削除対象データがありません！\n");
        echo "$log_date MICCCの削除対象データがありません！\n";
    }
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 50, '_');     // 実レコードは13バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 2) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 2 record=$rec \n");
                fwrite($fpb, "$log_date field not 2 record=$rec \n");
                // echo "$log_date field not 2 record=$rec \n";
            }
           continue;
        }
        // if (!isset($data[1])) $data[1]='';    // 'D'=CC 'E'=TNKCCがセットされているかチェック
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        ///// 登録
        $query = "INSERT INTO miccc (mipn, miccc)
                        VALUES(
                            '{$data[0]}',
                            '{$data[1]}' 
                        )
        ";
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date 部品番号:{$data[0]} MICCC:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
            fwrite($fpb, "$log_date 部品番号:{$data[0]} MICCC:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
            // echo "$log_date 部品番号:{$data[0]} MICCC:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date MICCCの更新前データ件数 : $del_ok 件削除しました。\n");
    fwrite($fpa, "$log_date MICCCの更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date MICCCの更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date MICCCの更新前データ件数 : $del_ok 件削除しました。\n");
    fwrite($fpb, "$log_date MICCCの更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date MICCCの更新 : {$ins_ok}/{$rec} 件 追加 \n");
    echo "$log_date MICCCの更新前データ件数 : $del_ok 件削除しました。\n";
    echo "$log_date MICCCの更新 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date MICCCの更新 : {$ins_ok}/{$rec} 件 追加 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
