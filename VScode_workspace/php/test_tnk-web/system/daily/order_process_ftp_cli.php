#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 発注データ工程明細 自動更新FTP版 Download cronで処理用 cli版             //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/14 Created  order_process_ftp_cli.php                            //
// 2004/11/18 php-5.0.2-cliへバージョンアップ *シェルスクリプトに対応に変更 //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2005/05/11 コマンドライン出力用の echo 文をコメントアウト                //
// 2005/06/01 予定一括削除機能を追加 注文番号=100000? (予定の削除)          //
// 2005/07/25 更新準備 コントロールファイルをロックのロジックを追加         //
// 2005/07/29 tableのユニークキーを sei_no,order_no に変更 vendorを外した   //
// 2006/04/26 大量更新のため BEGIN COMMIT をコメント                        //
// 2006/11/08 checkTableChange()を追加してデータが変更されている物のみ更新へ//
// 2007/07/30 データが重複している場合のメッセージロジックを削除ロジックへ  //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/15 メールにメッセージが無かった為、echoを追加               大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ないが
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        // 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "発注標準工程データの更新\n");
fwrite($fpb, "/var/www/html/system/daily/order_process_ftp_cli.php\n");
echo "/var/www/html/system/daily/order_process_ftp_cli.php\n";

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#MIORDR';           // 発注工程明細ファイル download file
// 保存先のディレクトリとファイル名
$save_file = '/var/www/html/system/backup/W#MIORDR.TXT';     // 発注工程明細ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 発注工程明細ファイル
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            // echo 'ftp_get download OK ', $target_file, '→', $save_file, "\n";
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 発注工程明細 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            fwrite($fpb,"$log_date 発注工程明細 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            echo "$log_date 発注工程明細 ftp_get download OK " . $target_file . '→' . $save_file . "\n";
        } else {
            // echo 'ftp_get() error ', $target_file, "\n";
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 発注工程明細 ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date 発注工程明細 ftp_get() error " . $target_file . "\n");
            echo "$log_date 発注工程明細 ftp_get() error " . $target_file . "\n";
        }
    } else {
        // echo "ftp_login() error \n";
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date 発注工程明細 ftp_login() error \n");
        fwrite($fpb,"$log_date 発注工程明細 ftp_login() error \n");
        echo "$log_date 発注工程明細 ftp_login() error \n";
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> 発注工程明細\n";
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ftp_connect() error --> 発注工程明細\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 発注工程明細\n");
    echo "$log_date ftp_connect() error --> 発注工程明細\n";
}


/////////// 更新準備 コントロールファイルをロック
do {
    if ($fp_ctl = fopen('/tmp/order_process_lock', 'w')) {
        flock($fp_ctl, LOCK_EX);
        $log_date = date('Y-m-d H:i:s');
        fwrite($fp_ctl, "$log_date " . __FILE__ . "\n");
        break;
    } else {
        sleep(5);   // 書込みでオープン出来なければ５秒待機
        continue;
    }
} while (0);

/////////// begin トランザクション開始
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 発注工程明細 db_connect() error \n");
    fwrite($fpb, "$log_date 発注工程明細 db_connect() error \n");
    echo "$log_date 発注工程明細 db_connect() error \n";
    exit();
}
// 発注工程明細ファイル 日報処理 準備作業
$file_orign  = $save_file;
$file_backup = '/var/www/html/system/backup/W#MIORDR-BAK.TXT';
$file_debug  = '/var/www/html/system/debug/debug-MIORDR.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $noChg  = 0;    // 未変更カウンター
    $del_ok = 0;    // DELETE用カウンターOK
    $del_ng = 0;    // DELETE用カウンターNG
    ///////////// 予定一括削除 2005/06/01 ADD
    $del2_rec = 0;  // 予定一括削除対象数
    $del2_ok  = 0;  // 予定一括DELETE用カウンターOK
    $query_chk = "SELECT count(*) FROM order_process WHERE order_no>=1000000 and order_no<=1000009";
    if (getUniResTrs($con, $query_chk, $del2_rec) > 0) {    // トランザクション内での 照会専用クエリー
        $sql_del = "DELETE FROM order_process WHERE order_no>=1000000 and order_no<=1000009";
        $del2_ok = query_affected_trans($con, $sql_del);
    }
    ///////////// 予定一括削除 END
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // 実レコードは189バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        $log_date = date('Y-m-d H:i:s');
        if ($num < 23) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                //echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 23 record=$rec \n");
                fwrite($fpb, "$log_date field not 23 record=$rec \n");
                //echo "$log_date field not 23 record=$rec \n";
            }
           continue;
        }
        if (!isset($data[23])) $data[23]='';    // 無検査がセットされているかチェック
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $query_chk = "
            SELECT * FROM order_process
                WHERE sei_no={$data[2]} and order_no={$data[0]}
        ";
            // 旧は WHERE sei_no={$data[2]} and order_no={$data[0]} and vendor='{$data[1]}'
        if (($rows_chk=getResultTrs($con, $query_chk, $res_chk)) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 注文番号の100000X --> MIORDR\2 で1=発行区分(1桁), 00000=基本注文番号(5桁), X=工程番号(1桁)
            ///// 追加する前に上記の基本注文番号=00000 をチェックして発注予定データを削除する。
            $order_no = ('100000' . substr(trim($data[0]), -1) );
            $query_chk = "SELECT sei_no FROM order_process WHERE sei_no={$data[2]} and order_no=$order_no";
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // トランザクション内での 照会専用クエリー
                $sql_del = "DELETE FROM order_process WHERE sei_no={$data[2]} and order_no=$order_no";
                if (query_affected_trans($con, $sql_del) > 0) {
                    $del_ok++;
                } else {
                    $del_ng++;
                }
            }
            ///// 登録なし insert 使用
            $query = "INSERT INTO order_process (order_no, vendor, sei_no, parts_no, pro_mark, mtl_cond, pro_kubun, order_price, order_date,
                            delivery, order_q, locate, kamoku, order_ku, plan_cond, masine, tatene, kiriko, genpin, siharai, cut_genpin, cut_siharai, next_pro, kensa)
                      VALUES(
                       {$data[0]} ,
                      '{$data[1]}',
                       {$data[2]} ,
                      '{$data[3]}',
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                       {$data[8]},
                       {$data[9]},
                       {$data[10]} ,
                      '{$data[11]}',
                       {$data[12]} ,
                      '{$data[13]}',
                      '{$data[14]}',
                       {$data[15]} ,
                       {$data[16]} ,
                       {$data[17]} ,
                       {$data[18]} ,
                       {$data[19]} ,
                       {$data[20]} ,
                       {$data[21]} ,
                      '{$data[22]}',
                      '{$data[23]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                // echo "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
        } elseif ($rows_chk <= 1) {
            if (checkTableChange($data, $res_chk[0])) {
                $noChg++;
                continue;
            }
            ///// 登録あり update 使用
            $query = "UPDATE order_process SET
                            order_no    = {$data[0]} ,
                            vendor      ='{$data[1]}',
                            sei_no      = {$data[2]} ,
                            parts_no    ='{$data[3]}',
                            pro_mark    ='{$data[4]}',
                            mtl_cond    ='{$data[5]}',
                            pro_kubun   ='{$data[6]}',
                            order_price = {$data[7]} ,
                            order_date  = {$data[8]} ,
                            delivery    = {$data[9]} ,
                            order_q     = {$data[10]} ,
                            locate      ='{$data[11]}',
                            kamoku      = {$data[12]} ,
                            order_ku    ='{$data[13]}',
                            plan_cond   ='{$data[14]}',
                            masine      = {$data[15]} ,
                            tatene      = {$data[16]} ,
                            kiriko      = {$data[17]} ,
                            genpin      = {$data[18]} ,
                            siharai     = {$data[19]} ,
                            cut_genpin  = {$data[20]} ,
                            cut_siharai = {$data[21]} ,
                            next_pro    ='{$data[22]}',
                            kensa       ='{$data[23]}'
                WHERE sei_no={$data[2]} and order_no={$data[0]}
            ";
                // 旧は WHERE sei_no={$data[2]} and order_no={$data[0]} and vendor='{$data[1]}'
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                // echo "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
        } else {    // 既に２重データになっている物の対応
            ///// 登録あり update 使用
            $query = "UPDATE order_process SET
                            order_no    = {$data[0]} ,
                            vendor      ='{$data[1]}',
                            sei_no      = {$data[2]} ,
                            parts_no    ='{$data[3]}',
                            pro_mark    ='{$data[4]}',
                            mtl_cond    ='{$data[5]}',
                            pro_kubun   ='{$data[6]}',
                            order_price = {$data[7]} ,
                            order_date  = {$data[8]} ,
                            delivery    = {$data[9]} ,
                            order_q     = {$data[10]} ,
                            locate      ='{$data[11]}',
                            kamoku      = {$data[12]} ,
                            order_ku    ='{$data[13]}',
                            plan_cond   ='{$data[14]}',
                            masine      = {$data[15]} ,
                            tatene      = {$data[16]} ,
                            kiriko      = {$data[17]} ,
                            genpin      = {$data[18]} ,
                            siharai     = {$data[19]} ,
                            cut_genpin  = {$data[20]} ,
                            cut_siharai = {$data[21]} ,
                            next_pro    ='{$data[22]}',
                            kensa       ='{$data[23]}'
                WHERE sei_no={$data[2]} and order_no={$data[0]} and vendor='{$data[1]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                // echo "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
                $del_sql = "
                    DELETE FROM order_process
                        WHERE sei_no={$data[2]} AND order_no={$data[0]} AND vendor != '{$data[1]}'
                ";
                $log_date = date('Y-m-d H:i:s');
                if (query_affected_trans($con, $del_sql) > 0) {    // 更新用クエリーの実行
                    fwrite($fpa, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} vendor:{$data[1]} 以外はデータが重複しているため削除しました！\n");
                    fwrite($fpb, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} vendor:{$data[1]} 以外はデータが重複しているため削除しました！\n");
                } else {
                    fwrite($fpa, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} 重複データの削除で失敗しました！\n");
                    fwrite($fpb, "$log_date 製造番号:{$data[2]} 注文番号:{$data[0]} 重複データの削除で失敗しました！\n");
                }
                $rec_ok++;
                $upd_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    $del = $del_ok + $del_ng;
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 発注工程明細の更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 発注工程明細の更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 発注工程明細の更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 発注工程明細の更新:{$data[2]} : {$noChg}/{$rec} 件 未変更 \n");
    fwrite($fpa, "$log_date 発注予定 100000? の削除 : {$del_ok}/{$del} 件 削除 \n");
    fwrite($fpa, "$log_date 発注予定一括 100000? の削除 : {$del2_ok}/{$del2_rec} 件 削除 \n");
    fwrite($fpb, "$log_date 発注工程明細の更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 発注工程明細の更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 発注工程明細の更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 発注工程明細の更新:{$data[2]} : {$noChg}/{$rec} 件 未変更 \n");
    fwrite($fpb, "$log_date 発注予定 100000? の削除 : {$del_ok}/{$del} 件 削除 \n");
    fwrite($fpb, "$log_date 発注予定一括 100000? の削除 : {$del2_ok}/{$del2_rec} 件 削除 \n");
    echo "$log_date 発注工程明細の更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 発注工程明細の更新:{$data[2]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 発注工程明細の更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n";
    echo "$log_date 発注工程明細の更新:{$data[2]} : {$noChg}/{$rec} 件 未変更 \n";
    echo "$log_date 発注予定 100000? の削除 : {$del_ok}/{$del} 件 削除 \n";
    echo "$log_date 発注予定一括 100000? の削除 : {$del2_ok}/{$del2_rec} 件 削除 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            // echo "$log_date DownLoad File $file_orign をBackupできません！\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
            fwrite($fpb,"$log_date DownLoad File $file_orign をBackupできません！\n");
            echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
// query_affected_trans($con, 'COMMIT');
fclose($fp_ctl);   ////// Exclusive用ファイルクローズ
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
exit();

/***** テーブルが変更されている場合はfalseを返す     *****/
/***** 引数は比較するデータの配列とテーブルの配列   *****/
function checkTableChange($data, $res)
{
    for ($i=0; $i<23; $i++) {   // 最後の検査はNULLが多いため除外
        // 比較に邪魔をするスペースを削除
        if (trim($data[$i]) != trim($res[$i])) {
            return false;
        }
    }
    return true;
}

?>
