#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// 生産用 部品在庫 経歴(history) マスター 日報更新 用 cli版                 //
//                      5.0.4-cli --- 5.1.6-cli までは半角カナがNG          //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/15 Created  parts_stock_history_master_ftp_cli.php               //
// 2004/12/17 カナが取込めない不具合対策でphp(5.0.x) → php-4.3.9-cliに変更 //
//           parts_stock_sync_controlテーブルを追加しレコード制御で経歴追加 //
// 2004/12/20 rename()を使用して取込みデータを抽出及び本日データのチェック  //
//            本番用に echo 文をコメント                                    //
// 2004/12/27 FTP転送途中のエラーチェックを変更(レコード数でsyncの更新有無) //
// 2005/02/04 ASからのUPLOAD file のロックによる使用中チェックを追加        //
// 2005/07/26 前回のデータ削除はASデータが存在した時のみ flockを'r+'へ変更  //
// 2006/08/28 php-4.3.9-cli → php(カレントは5.1.6) simplate.so をDSO module//
//            で取込むようにしたため module API=20050922 と php API=20020429//
//            が合わなくなった。やはり半角カナが取込めないため下記の対策    //
// 2006/08/29 simplate.so をDSO module で取込んだため php4は -c php4.ini追加//
// 2006/09/05 文字化けの原因はfgetcsv()のLANG環境変数の設定である事が分かり //
//            cronの定義ファイル(as400get_ftp)にLANG=ja_JP,eucJPを追加し対応//
// 2007/08/03 在庫・有効数マイナスリスト(parts_stock_avail_minus)更新を追加 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');                // 日報用ログの日時
$fpa = fopen('/tmp/parts_stock.log', 'a');      // 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル
$target_file1 = 'UKWLIB/W#HIZHST.TXT';               // download file1
$target_file2 = 'UKWLIB/W#MIBZMT.TXT';               // download file2
// 保存先のディレクトリとファイル名
$as_file1   = '/home/guest/daily/W#HIZHST.TXT';     // AS/400 file1
$as_file2   = '/home/guest/daily/W#MIBZMT.TXT';     // AS/400 file2
$save_file1 = '/home/guest/daily/HIZHST.txt';       // save file1
$save_file2 = '/home/guest/daily/MIBZMT.txt';       // save file2

if (!file_exists($as_file1)) endJOB($fpa);          // ASからのUPLOAD file の存在チェック
if (!file_exists($as_file2)) endJOB($fpa);

if (file_exists($save_file1)) unlink($save_file1);  // 前回のデータを削除
if (file_exists($save_file2)) unlink($save_file2);  // 前回のデータを削除

if (!($fp1=fopen($as_file1, 'r+'))) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file1 がオープンできないので終了します。 \n");
    endJOB($fpa); // open 出来なければ終了
} else {
    if (!flock($fp1, LOCK_EX)) {
        fclose($fp1);
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date $as_file1 がロックできないので終了します。 \n");
        endJOB($fpa);   // ロックできないので終了
    }
}
if (!($fp2=fopen($as_file2, 'r+'))) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file2 がオープンできないので終了します。 \n");
    endJOB($fpa); // open 出来なければ終了
} else {
    if (!flock($fp2, LOCK_EX)) {
        fclose($fp2);
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date $as_file2 がロックできないので終了します。 \n");
        endJOB($fpa);   // ロックできないので終了
    }
}
fclose($fp1);   // ロック出来たので開放
fclose($fp2);   // 次のリネームに備える

if (!@rename($as_file1, $save_file1) ) {             // ファイル名変更
    // $as_file1のファイルの存在チェックをすると常に無しになる(ASが使用している)ため no check
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file1 rename() NG \n");
    // endJOB($fpa);
}
if (!@rename($as_file2, $save_file2) ) {             // ファイル名変更
    // $as_file2のファイルの存在チェックをすると常に無しになる(ASが使用している)ため no check
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date $as_file2 rename() NG \n");
    // endJOB($fpa);
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
    $rec_ng = 0;    // 書込み失敗レコード数
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    endJOB($fpa);
}
$today = date('Ymd');
///// 在庫経歴のレコード制御
$query_ctl = "SELECT to_char(sync_date, 'YYYYMMDD'), sync_no FROM parts_stock_sync_control WHERE rec_no=1";
$res_ctl = array();
if (getResultTrs($con, $query_ctl, $res_ctl) > 0) {
    if ($today == $res_ctl[0][0]) {
        $history_rec = $res_ctl[0][1];
    } else {
        $history_rec = 0;           // 本日のデータでなければクリアー
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date sync_control 1 error \n");
    // echo "$log_date sync_control 1 error \n";
    endJOB($fpa);
}
///// 在庫マスターのレコード制御
$query_ctl = "SELECT to_char(sync_date, 'YYYYMMDD'), sync_no FROM parts_stock_sync_control WHERE rec_no=2";
$res_ctl = array();
if (getResultTrs($con, $query_ctl, $res_ctl) > 0) {
    if ($today == $res_ctl[0][0]) {
        $master_rec = $res_ctl[0][1];
    } else {
        $master_rec = 0;           // 本日のデータでなければクリアー
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date sync_control 2 error \n");
    // echo "$log_date sync_control 2 error \n";
    endJOB($fpa);
}

// 在庫経歴 日報処理 準備作業
$file_orign  = $save_file1;
$file_debug  = '/home/guest/daily/debug/debug-HIZHST.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // 実レコードは93バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec <= $history_rec) continue;     // 既に更新済みを除外
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 13) {    // 実際には 9 あり(最後がない場合があるため)
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date field not 13 record=$rec \n");
                // echo "$log_date field not 13 record=$rec \n";
            }
           continue;
        }
        $data[6]  = trim($data[6]);    // 伝票番号の無駄な余白を削除
        $data[7]  = trim($data[7]);    // 摘要(計画番号)の無駄な余白を削除
        $data[10] = trim($data[10]);   // 備考の無駄な余白を削除
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        // 初回レコードで更新日をチェック
        if ($rec == 1) {
            if ($data[12] != $today) {  // 本日の更新データかチェック
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 更新日:{$data[12]} : 本日の更新データでない!\n");
                // echo "$log_date 更新日:{$data[12]} : 本日の更新データでない!\n";
                query_affected_trans($con, 'rollback');     // transaction rollback
                endJOB($fpa);
            }
        }
        ///// 登録なし insert 使用
        $query = "INSERT INTO parts_stock_history
                  VALUES(
                  '{$data[0]}',     -- 部品番号
                  '{$data[1]}',     -- ABC区分
                   {$data[2]} ,     -- 在庫移動数
                  '{$data[3]}',     -- 受入部門
                  '{$data[4]}',     -- 払出部門
                  '{$data[5]}',     -- 伝票区分
                  '{$data[6]}',     -- 伝票番号
                  '{$data[7]}',     -- 摘要(計画番号等)
                   {$data[8]} ,     -- NK在庫
                   {$data[9]} ,     -- TNK在庫
                  '{$data[10]}',    -- 備考
                   {$data[11]} ,    -- データ日(記帳日)
                   {$data[12]})     -- 更新日
        ";
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
            // echo "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
    }
    fclose($fp);
    fclose($fpw);       // debug
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 在庫経歴の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 在庫経歴の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 在庫経歴の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    // echo "$log_date 在庫経歴の更新 : $rec_ok/$rec 件登録しました。\n";
    // echo "$log_date 在庫経歴の更新 : {$ins_ok}/{$rec} 件 追加 \n";
    // echo "$log_date 在庫経歴の更新 : {$upd_ok}/{$rec} 件 変更 \n";
    ///// 在庫経歴のコントロールテーブルの更新
    if ($rec > $history_rec) {   // FTP転送途中のエラーチェック
        $query_ctl = "UPDATE parts_stock_sync_control SET sync_date=CURRENT_TIMESTAMP, sync_no={$rec}, pre_sync_no={$history_rec} WHERE rec_no=1";
        if (query_affected_trans($con, $query_ctl) <= 0) {      // 更新用クエリーの実行
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date sync_control 1 UPDATE error \n");
            // echo "$log_date sync_control 1 UPDATE error \n";
            query_affected_trans($con, 'rollback');     // transaction rollback
            endJOB($fpa);
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    // echo "{$log_date}: file:{$file_orign} がありません!\n";
}



// 在庫マスター 日報処理 準備作業
$file_orign  = $save_file2;
$file_debug  = '/home/guest/daily/debug/debug-MIBZMT.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // 実レコードは85バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec <= $master_rec) continue;      // 既に更新済みを除外
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 10) {    // 実際には 9 あり(最後がない場合があるため)
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date field not 10 record=$rec \n");
                // echo "$log_date field not 10 record=$rec \n";
            }
           continue;
        }
        $data[7]  = trim($data[7]);    // 棚番の無駄な余白を削除
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $query_chk = "SELECT parts_no FROM parts_stock_master
                                WHERE parts_no='{$data[0]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO parts_stock_master
                      VALUES(
                      '{$data[0]}',     -- 部品番号
                      '{$data[1]}',     -- ABC区分
                       {$data[2]} ,     -- NK在庫
                       {$data[3]} ,     -- TNK在庫
                       {$data[4]} ,     -- 前月在庫NK
                       {$data[5]} ,     -- 前月在庫TNK
                      '{$data[6]}',     -- stock_id
                      '{$data[7]}',     -- 棚番
                       {$data[8]} ,     -- 調整日
                       {$data[9]} ,     -- 登録日
                       {$data[10]})     -- 更新日
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                // echo "$log_date 部品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
            $query = "UPDATE parts_stock_master SET
                            parts_no      ='{$data[0]}',
                            abc_kubun     ='{$data[1]}',
                            nk_stock      = {$data[2]} ,
                            tnk_stock     = {$data[3]} ,
                            pre_nk_stock  = {$data[4]} ,
                            pre_tnk_stock = {$data[5]} ,
                            stock_id      ='{$data[6]}',
                            tnk_tana      ='{$data[7]}',
                            adj_date      = {$data[8]} ,
                            reg_date      = {$data[9]} ,
                            upd_date      = {$data[10]}
                WHERE parts_no='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                // echo "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 在庫マスターの更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 在庫マスターの更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 在庫マスターの更新 : {$upd_ok}/{$rec} 件 変更 \n");
    // echo "$log_date 在庫マスターの更新 : $rec_ok/$rec 件登録しました。\n";
    // echo "$log_date 在庫マスターの更新 : {$ins_ok}/{$rec} 件 追加 \n";
    // echo "$log_date 在庫マスターの更新 : {$upd_ok}/{$rec} 件 変更 \n";
    ///// 在庫マスターのコントロールテーブルの更新
    if ($rec > $master_rec) {   // FTP転送途中のエラーチェック
        $query_ctl = "UPDATE parts_stock_sync_control SET sync_date=CURRENT_TIMESTAMP, sync_no={$rec}, pre_sync_no={$master_rec} WHERE rec_no=2";
        if (query_affected_trans($con, $query_ctl) <= 0) {      // 更新用クエリーの実行
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date sync_control 2 UPDATE error \n");
            // echo "$log_date sync_control 2 UPDATE error \n";
            query_affected_trans($con, 'rollback');     // transaction rollback
            endJOB($fpa);
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    // echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
if ($rec_ng == 0) {
    query_affected_trans($con, 'commit');
    parts_stock_avail_minus_update($con, $fpa);     // 在庫・有効数マイナスリストの更新
} else {
    query_affected_trans($con, 'rollback');
}
// echo $query . "\n";  // debug
endJOB($fpa);       ////// 日報用ログ書込み終了


/***** 共通終了処理 *****/
function endJOB($fpa)
{
    fclose($fpa);
    exit();
}

/***** 在庫・有効数マイナスリストの更新 *****/
function parts_stock_avail_minus_update($con, $fpa)
{
    $query = "
        BEGIN;
        DELETE FROM parts_stock_avail_minus_table;
        INSERT INTO parts_stock_avail_minus_table SELECT * FROM parts_stock_avail_minus(0);
        COMMIT;
    ";
    query_affected_trans($con, $query);
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date 在庫・有効利用数マイナスリストを更新しました。parts_stock_avail_minus_table\n");
}
?>
