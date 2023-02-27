#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 購買納期回答 AS/400<-->TNKサーバー同期 自動FTP Download  cron で処理用   //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright(C) 2007-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed histoy                                                           //
// 2007/04/26 Created   order_delivery_answer_get_ftp.php                   //
// 2007/05/10 ログメッセージ変更 データがあるで終了 → データがあるので終了 //
// 2007/10/25 ftpGetCheckAndExecute(),ftpPutCheckAndExecute()を追加         //
//            FTP転送のリトライ強化   E_ALL → E_ALL | E_STRICT へ変更      //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2010/01/20 $log_dateの前後は'ではなく"なので修正                    大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版は必要ない
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "購買納期回答データの同期実行(排他制御あり)\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php\n");
echo "/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php\n";

// 排他制御用コントロールファイル
define('CHIDLIV', 'UKWLIB/C#HIDLIV');   // 購買納期回答コントロール
// 保存先のディレクトリとファイル名
define('C_HIDLIV', '/home/www/html/tnk-web/system/backup/C#HIDLIV.TXT');  // 購買納期回答コントロール

// ターゲットファイル
define('HIDLIV', 'UKWLIB/W#HIDLIV');    // 購買納期回答ファイル
// 保存先のディレクトリとファイル名
define('W_HIDLIV', '/home/www/html/tnk-web/system/backup/W#HIDLIV.TXT');  // 購買納期回答
// AS/400のファイルを空にするためのダミーファイル名
define('LOCAL_FILE', '/home/www/html/tnk-web/system/backup/W#HIDLIV-clear.TXT');

if (file_exists(W_HIDLIV)) {         // 前回処理したファイルの存在チェック
    unlink(W_HIDLIV);
}

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftpGetCheckAndExecute($ftp_stream, C_HIDLIV, CHIDLIV, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            echo "$log_date ftp_get コントロール download OK ", CHIDLIV, "→", C_HIDLIV, "\n";
            fwrite($fpa,"$log_date ftp_get コントロール download OK " . CHIDLIV . '→' . C_HIDLIV . "\n");
            fwrite($fpb,"$log_date ftp_get コントロール download OK " . CHIDLIV . '→' . C_HIDLIV . "\n");
            if (checkControlFile($fpa, $fpb, C_HIDLIV)) {
                echo "$log_date コントロールファイルにデータがあるので終了します。\n";
                fwrite($fpa,"$log_date コントロールファイルにデータがあるので終了します。\n");
                fwrite($fpb,"$log_date コントロールファイルにデータがあるので終了します。\n");
                ftp_close($ftp_stream);
                fclose($fpa);      ////// 強制終了
                fwrite($fpb, "------------------------------------------------------------------------\n");
                fclose($fpb);      ////// 日報データ再取得用ログ書込み強制終了
                exit();
            }
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            echo "$log_date ftp_get() コントロール error ", CHIDLIV, "\n";
            fwrite($fpa,"$log_date ftp_get() コントロール error " . CHIDLIV . "\n");
            fwrite($fpb,"$log_date ftp_get() コントロール error " . CHIDLIV . "\n");
            ftp_close($ftp_stream);
            fclose($fpa);      ////// 強制終了
            fwrite($fpb, "------------------------------------------------------------------------\n");
            fclose($fpb);      ////// 日報データ再取得用ログ書込み強制終了
            exit();
        }
        if (ftpGetCheckAndExecute($ftp_stream, W_HIDLIV, HIDLIV, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            echo "$log_date 購買納期回答 ftp_get download OK ", HIDLIV, "→", W_HIDLIV, "\n";
            fwrite($fpa,"$log_date 購買納期回答 ftp_get download OK " . HIDLIV . '→' . W_HIDLIV . "\n");
            fwrite($fpb,"$log_date 購買納期回答 ftp_get download OK " . HIDLIV . '→' . W_HIDLIV . "\n");
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            echo "$log_date 購買納期回答 ftp_get() error ", HIDLIV, "\n";
            fwrite($fpa,"$log_date 購買納期回答 ftp_get() error " . HIDLIV . "\n");
            fwrite($fpb,"$log_date 購買納期回答 ftp_get() error " . HIDLIV . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        echo "$log_date 購買納期回答 ftp_login() error \n";
        fwrite($fpa,"$log_date 購買納期回答 ftp_login() error \n");
        fwrite($fpb,"$log_date 購買納期回答 ftp_login() error \n");
    }



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');        ///// ログの日時
    echo "$log_date 購買納期回答 db_connect() error \n";
    fwrite($fpa,"$log_date 購買納期回答 db_connect() error \n");
    fwrite($fpb,"$log_date 購買納期回答 db_connect() error \n");
    exit();
}
// 同期処理 準備作業
if (file_exists(W_HIDLIV)) {         // ファイルの存在チェック
    $fp = fopen(W_HIDLIV, 'r');
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    while (1) {
        $data = fgetcsv($fp, 50, '_');     // 実レコードは31バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 4) continue;     // フィールド数のチェック
        if (!$data[0]) continue;    // 計画オーダー(製造番号)が0のものがあるのでチェック
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
        }
        
        $query_chk = "
            SELECT * FROM order_delivery_answer
            WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO order_delivery_answer (sei_no, order_no, vendor, delivery)
                VALUES({$data[0]}, {$data[1]}, '{$data[2]}', {$data[3]})
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date 購買納期回答 : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":レコード目の書込みに失敗しました!\n");
                fwrite($fpb,"$log_date 購買納期回答 : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":レコード目の書込みに失敗しました!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        } else {
            ///// 登録あり update 使用
            $query = "
                UPDATE order_delivery_answer SET sei_no={$data[0]}, order_no={$data[1]}, vendor='{$data[2]}',
                    delivery={$data[3]}
                WHERE sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date 購買納期回答 : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb,"$log_date 購買納期回答 : sei_no={$data[0]} AND order_no={$data[1]} AND vendor='{$data[2]}' delivery={$data[3]} ".($rec).":レコード目のUPDATEに失敗しました!\n");
                // query_affected_trans($con, 'ROLLBACK');     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        }
    }
    fclose($fp);
    $log_date = date('Y-m-d H:i:s');        ///// ログの日時
    echo "$log_date 購買納期回答 : $rec_ok/$rec 件登録しました。\n";
    fwrite($fpa, "$log_date 購買納期回答 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 購買納期回答 : $rec_ok/$rec 件登録しました。\n");
    
    ////////// AS/400のファイルを空にする
    if (ftpPutCheckAndExecute($ftp_stream, HIDLIV, LOCAL_FILE, FTP_ASCII)) {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        echo "$log_date 購買納期回答 : AS/400のファイルを空にしました。\n";
        fwrite($fpa, "$log_date 購買納期回答 : AS/400のファイルを空にしました。\n");
        fwrite($fpb, "$log_date 購買納期回答 : AS/400のファイルを空にしました。\n");
        /////////// commit トランザクション終了
        query_affected_trans($con, 'COMMIT');
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        echo "$log_date 購買納期回答 : AS/400のファイルを空に出来ませんでした。\n";
        fwrite($fpa, "$log_date 購買納期回答 : AS/400のファイルを空に出来ませんでした。\n");
        fwrite($fpb, "$log_date 購買納期回答 : AS/400のファイルを空に出来ませんでした。\n");
        query_affected_trans($con, 'ROLLBACK');     // transaction rollback
    }
} else {
    echo '$log_date ファイル ', W_HIDLIV, " がありません!\n";
    fwrite($fpa, "$log_date ファイル " . W_HIDLIV . " がありません!\n");
    fwrite($fpb, "$log_date ファイル " . W_HIDLIV . " がありません!\n");
}



    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');        ///// ログの日時
    echo "$log_date ftp_connect() error --> 購買納期回答\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 購買納期回答\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 購買納期回答\n");
}

fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了



function checkControlFile($fpa, $fpb, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // 実レコードは11バイトなのでちょっと余裕を
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        fwrite($fpa, "$log_date 購買納期回答 : 使用端末は {$data}");
        fwrite($fpb, "$log_date 購買納期回答 : 使用端末は {$data}");
        return true;
    }
}

function ftpGetCheckAndExecute($stream, $local_file, $as400_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}

function ftpPutCheckAndExecute($stream, $as400_file, $local_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
