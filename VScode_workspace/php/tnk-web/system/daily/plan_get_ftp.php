#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 日報データ 日程計画ファイルdownload 自動FTP Download  cron で処理用      //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright(C) 2003-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed histoy                                                           //
// 2003/05/30 Created   plan_get_ftp.php                                    //
// 2003/05/30 組立日程計画表の DownLoad 日報処理用(一括とファイル兼用)      //
//            問題点 備考やユーザー名に [']等が使われている場合がある 注意  //
// 2003/05/31 上記の問題点は addslashes() で解決 備考１５桁と受注先名       //
// 2003/06/06 AS/400のTIPPLNP等のトランザクションファイルはキー無しの物理   //
//             ファイルを頭から順番に読込書込しないといけない重複レコードが //
//             あるため最新に保てない。                                     //
// 2003/11/17 cgi → cli版へ変更出来るように requier_once を絶対指定へ      //
// 2004/04/19 php-4.3.4-cgi --> php-4.3.6-cgi へ変更                        //
// 2004/06/07 php-4.3.6-cgi -q → php-4.3.7-cgi -q  バージョンアップに伴う  //
// 2004/11/18 php-5.0.2-cliへバージョンアップ *シェルスクリプトに対応に変更 //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2006/01/31 $plan_no の初期化追加                                         //
// 2007/01/29 受注先名に"'"が入り addslashes() → pg_escape_string()で対応  //
//            postgresql 8.2.X への VerUP への対応も兼ねている              //
// 2007/02/05 echo文をコメントアウト                                        //
// 2007/07/30 AS/400とのFTP error 対応のため ftpCheckAndExecute()関数を追加 //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2009/12/28 コメントの整理                                           大谷 //
// 2010/01/15 メールにメッセージが無かった為、echoを追加               大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2010/01/20 $log_dateの前後は'では無く"なので修正                    大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版は必要ない
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "日程計画データの更新\n");
fwrite($fpb, "/var/www/html/system/daily/plan_get_ftp.php\n");
echo "/var/www/html/system/daily/plan_get_ftp.php\n";

// ターゲットファイル
define('MIPPLP', 'UKWLIB/W#MIPPLP');    // 日程計画ファイル
// 保存先のディレクトリとファイル名
define('W_MIPPLP', '/var/www/html/system/backup/W#MIPPLP.TXT');  // 日程計画

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftpCheckAndExecute($ftp_stream, W_MIPPLP, MIPPLP, FTP_ASCII)) {
            echo "$log_date 日程計画 ftp_get download OK ", MIPPLP, "→", W_MIPPLP, "\n";
            fwrite($fpa,"$log_date 日程計画 ftp_get download OK " . MIPPLP . '→' . W_MIPPLP . "\n");
            fwrite($fpb,"$log_date 日程計画 ftp_get download OK " . MIPPLP . '→' . W_MIPPLP . "\n");
        } else {
            echo "$log_date 日程計画 ftp_get() error", MIPPLP, "\n";
            fwrite($fpa,"$log_date 日程計画 ftp_get() error " . MIPPLP . "\n");
            fwrite($fpb,"$log_date 日程計画 ftp_get() error " . MIPPLP . "\n");
        }
    } else {
        echo "$log_date 日程計画 ftp_login() error \n";
        fwrite($fpa,"$log_date 日程計画 ftp_login() error \n");
        fwrite($fpb,"$log_date 日程計画 ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> 日程計画\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 日程計画\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 日程計画\n");
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date 日程計画 db_connect() error \n";
    fwrite($fpa,"$log_date 日程計画 db_connect() error \n");
    fwrite($fpb,"$log_date 日程計画 db_connect() error \n");
    exit();
}
// 組立日程計画 日報処理 準備作業
$file_orign  = W_MIPPLP;
// $file_backup = "W#MIPPLP-BAK.TXT";
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign,"r");
    // $fpw = fopen($file_test,"w");        // TEST 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $plan_no = 'データなし';    // 初期化 2006/01/31 ADD
    while (1) {
        $data = fgets($fp,150);       // 実レコードは140バイトなのでちょっと余裕を
        $data = mb_convert_encoding($data, "UTF-8", "auto");       // autoをEUC-JPへ変換
        // $data_KV = mb_convert_kana($data);           // 半角カナを全角カナに変換
        // fwrite($fpw,$data_KV);
        if (feof($fp)) {
            break;
        }
        $plan_no   = substr($data,0,8);         // 計画番号
        $parts_no  = substr($data,8,9);         // 部品番号
        $syuka     = substr($data,17,8);        // 集荷日  
        $chaku     = substr($data,25,8);        // 着手日  
        $kanryou   = substr($data,33,8);        // 完了日  
        $plan      = substr($data,41,8);        // 計画数  
        $cut_plan  = substr($data,49,8);        // 打切数  
        $kansei    = substr($data,57,8);        // 完成数  
        $nyuuko    = substr($data,65,2);        // 入庫場所
        $sei_kubun = substr($data,67,1);        // 製造区分
        $line_no   = substr($data,68,4);        // ライン№
        //$note15    = addslashes(substr($data,72,15));     // 備考15桁
        $note15    = pg_escape_string(substr($data,72,15)); // 備考15桁
        $order_no  = substr($data,87,6);        // 受注№  
        // $user_name = addslashes(substr($data,93,15));    // 受注先名
        $user_name = pg_escape_string(substr($data,93,15)); // 受注先名
        $p_kubun   = substr($data,108,1);       // 計画区分
        $assy_site = substr($data,109,5);       // 組立場所
        $dept      = substr($data,114,1);       // 事業部  
        $orign_kan = substr($data,115,8);       // 元完了日
        $priority  = substr($data,123,1);       // 優先順位
        $rep_date  = substr($data,124,8);       // 更新日  
        $crt_date  = substr($data,132,8);       // 作成日  
        
        $rec++;
        
        $query_chk = sprintf("select plan_no from assembly_schedule where plan_no='%s'", $plan_no);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = sprintf("insert into assembly_schedule values(
                '%s','%s',%d,%d,%d,%d,%d,%d,'%s','%s','%s','%s',%d,'%s','%s','%s','%s',%d,'%s',%d,%d)", 
                $plan_no,  
                $parts_no, 
                $syuka,    
                $chaku,    
                $kanryou,  
                $plan,     
                $cut_plan, 
                $kansei,   
                $nyuuko,   
                $sei_kubun,
                $line_no,  
                $note15,   
                $order_no, 
                $user_name,
                $p_kubun,  
                $assy_site,
                $dept,     
                $orign_kan,
                $priority, 
                $rep_date, 
                $crt_date);
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa,"$log_date 日程計画:$plan_no : ".($rec).":レコード目の書込みに失敗しました!\n");
                fwrite($fpb,"$log_date 日程計画:$plan_no : ".($rec).":レコード目の書込みに失敗しました!\n");
                // echo ($rec) . ":レコード目の書込みに失敗しました!\n";
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        } else {
            ///// 登録あり update 使用
            $query = "update assembly_schedule set parts_no='$parts_no', syuka=$syuka, chaku=$chaku,
                kanryou=$kanryou, plan=$plan, cut_plan=$cut_plan, kansei=$kansei, nyuuko='$nyuuko',
                sei_kubun='$sei_kubun', line_no='$line_no', note15='$note15', order_no=$order_no,
                user_name='$user_name', p_kubun='$p_kubun', assy_site='$assy_site', dept='$dept',
                orign_kan=$orign_kan, priority='$priority', rep_date=$rep_date, crt_date=$crt_date 
                where plan_no='$plan_no'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa,"$log_date 日程計画:$plan_no : ".($rec).":レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb,"$log_date 日程計画:$plan_no : ".($rec).":レコード目のUPDATEに失敗しました!\n");
                // echo ($rec) . ":レコード目のUPDATEに失敗しました!\n";
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
            } else {
                $rec_ok++;
            }
        }
    }
    fclose($fp);
    // fclose($fpw);
    fwrite($fpa,"$log_date 日程計画:$plan_no : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb,"$log_date 日程計画:$plan_no : $rec_ok/$rec 件登録しました。\n");
    echo "$log_date 日程計画 : $rec_ok/$rec 件登録しました。\n";
    /*****
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        unlink($file_backup);       // Backup ファイルの削除
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
            // echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
    *****/
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "$log_date 日程計画ファイル$file_orign がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

exit();

function ftpCheckAndExecute($stream, $local_file, $as400_file, $ftp)
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
?>
