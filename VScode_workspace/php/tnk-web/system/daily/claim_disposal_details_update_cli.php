#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 不適合処置連絡書更新 バッチ用 (W#MICLDTN1,W#MICLDTN2) 取込 処理用 CLI版  //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2013-2013 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// AS UKSLIB/QCLSRC \TNKDAILYCのLOOPの前に以下を登録すること                //
// SNDF     RCDFMT(TITLE)                                                   //
// SNDF     RCDFMT(MICLDTN1)                                                //
// RUNQRY   QRY(UKPLIB/Q#MICLDTN1)                                          //
// \FTPTNK  USER(AS400) ASFILE(W#MICLDTN1) PCFILE(Q#MICLDTN1.TXT) MODE(TXT) //
// SNDF     RCDFMT(TITLE)                                                   //
// SNDF     RCDFMT(MICLDTN2)                                                //
// RUNQRY   QRY(UKPLIB/Q#MICLDTN2)                                          //
// \FTPTNK  USER(AS400) ASFILE(W#MICLDTN2) PCFILE(Q#MICLDTN2.TXT) MODE(TXT) //
// Changed history                                                          //
// 2013/01/10 Created  claim_disposal_details_update_cli.php                //
// 2013/01/25 update時のデータ抜けを修正                                    //
// 2013/01/28 データの追加                                                  //
// 2013/01/29 文字化けの解決とデータ量増大の為、ファイルを２つに分けた      //
// 2013/01/31 更新時のメール結果表示を訂正                                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ない
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "不適合処置連絡書の更新\n");
fwrite($fpb, "/var/www/html/system/daily/claim_disposal_details_update_cli.php \n");

$_ENV['LANG'] = 'ja_JP.eucJP';
/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
//     $_SESSION['s_sysmsg'] .= 'db_connect() error';
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    exit();
}
// 不適合処置連絡書１ 登録処理 準備作業
$file_orign1  = '/home/guest/daily/W#MICLDTN1.TXT';
$file_temp1   = '/home/guest/daily/W#MICLDTN1-TEMP.TXT';

$sql = "DELETE FROM claim_disposal_details";
query_affected($sql);
echo "$log_date 不適合処置連絡書サマリーを削除して実行\n";
fwrite($fpa, "$log_date 不適合処置連絡書サマリーを削除して実行\n");
fwrite($fpb, "$log_date 不適合処置連絡書サマリーを削除して実行\n");

// 不適合処置連絡書１ 登録処理
if(file_exists($file_orign1)){           // ファイルの存在チェック
    $fp = fopen($file_orign1,"r");
        ///////////// SJIS → EUC 変換ロジック START (SJISでEUCにない文字はNULLバイトに変換される事に注意)
    $fp_conv = fopen($file_temp1, 'w');  // EUC へ変換用
    while (!(feof($fp))) {
        $data = fgets($fp,800);     // 実際には120 でOKだが余裕を持って
        ///////////// SJIS → EUC 変換ロジック START (SJISでEUCにない文字はNULLバイトに変換される事に注意)
        //$data = mb_convert_encoding($data, 'UTF-8', 'SJIS');             // SJISをEUC-JPへ変換(auto)
        $data = mb_convert_encoding($data, 'eucJP-win', 'sjis-win');      // SJISをEUC-JPへ変換(auto)
        $data = str_replace("\0", ' ', $data);                            // NULLバイトをSPACEへ変換
        $data = mb_ereg_replace('　', '  ', $data);                       // 位置がズレるので全角スペースを半角へ
        $data = mb_ereg_replace('㈱', '||', $data);                       // 機種依存文字を規格文字へ一時変更
        $data = mb_ereg_replace('№', '@@', $data);                       // 機種依存文字を規格文字へ一時変更
        fwrite($fp_conv, $data);
    }
    fclose($fp);
    fclose($fp_conv);
    $fp = fopen($file_temp1, 'r');       // EUC へ変換後のファイル
    ///////////// SJIS → EUC 変換ロジック END
    $rec1 = 0;       // レコード№
    $rec1_ok = 0;    // 書込み成功レコード数
    $rec1_ng = 0;    // 書込み失敗レコード数
    $ins1_ok = 0;    // INSERT用カウンター
    $upd1_ok = 0;    // UPDATE用カウンター
    while (!feof($fp)) {            // ファイルのEOFチェック
        $data = fgetcsv($fp, 800, "_");     // 実レコードは150バイト デリミタはタブからアンダースコアへ変更
        if (feof($fp)) {
            break;
        }
        $rec1++;
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $data[4] = str_replace('||', '(株)', $data[4]);       // 機種依存文字を規格文字へ変更
        $data[4] = str_replace('@@', 'No.', $data[4]);        // 機種依存文字を規格文字へ変更
        $data[6] = str_replace('||', '(株)', $data[6]);       // 機種依存文字を規格文字へ変更
        $data[6] = str_replace('@@', 'No.', $data[6]);        // 機種依存文字を規格文字へ変更
        $data[7] = str_replace('||', '(株)', $data[7]);       // 機種依存文字を規格文字へ変更
        $data[7] = str_replace('@@', 'No.', $data[7]);        // 機種依存文字を規格文字へ変更
        
        if ($data[10] != '') {
            if ($data[10] != '00') {
                if ($data[10] != '0 ') {
                    if ($data[10] != ' 0') {
                        $data[10] = $data[10] * 1;
                        $data[10] = '0' . $data[10];
                    } else {
                        $data[10] = '00';
                    }
                } else {
                    $data[10] = '00';
                }
            }
        }
        $query_chk = sprintf("SELECT assy_no FROM claim_disposal_details WHERE assy_no='%s' AND publish_no='%s' AND parts_no='%s'", $data[0], $data[1], $data[5]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO claim_disposal_details (assy_no, publish_no, publish_date, claim_no,
                     claim_name, parts_no, claim_explain1, claim_explain2, ans_hope_date, delivery_date,
                     process_name, claim_sec, product_no, delivery_num, bad_num, bad_par, charge_no)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}',
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                      '{$data[10]}',
                      '{$data[11]}',
                      '{$data[12]}',
                       {$data[13]} ,
                       {$data[14]} ,
                       {$data[15]} ,
                      '{$data[16]}')";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                // $_SESSION['s_sysmsg'] .= "{$rec1}:レコード目の書込みに失敗しました!<br>";
                fwrite($fpa, "$log_date 発行番号:{$data[1]} : {$rec1}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 発行番号:{$data[1]} : {$rec1}:レコード目の書込みに失敗しました!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec1_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec1_ok++;
                $ins1_ok++;
            }
        } else {
            ///// 登録あり update 使用
            $query = "UPDATE claim_disposal_details SET publish_date={$data[2]}, claim_no='{$data[3]}', claim_name='{$data[4]}',
                      claim_explain1='{$data[6]}', claim_explain2='{$data[7]}', ans_hope_date={$data[8]},
                      delivery_date={$data[9]}, process_name='{$data[10]}', claim_sec='{$data[11]}', product_no='{$data[12]}',
                      delivery_num={$data[13]}, bad_num={$data[14]}, bad_par={$data[15]}, charge_no='{$data[16]}'
                      where assy_no='{$data[0]}' and publish_no='{$data[1]}' and parts_no='{$data[5]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                // $_SESSION['s_sysmsg'] .= "{$rec1}:レコード目のUPDATEに失敗しました!\n";
                fwrite($fpa, "$log_date 発行番号:{$data[1]} : {$rec1}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 発行番号:{$data[1]} : {$rec1}:レコード目のUPDATEに失敗しました!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec1_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec1_ok++;
                $upd1_ok++;
            }
        }
    }
    fclose($fp);
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec1_ok}/{$rec1} 件登録しました。</font><br><br>";
    // $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins1_ok}/{$rec1} 件 追加<br>";
    // $_SESSION['s_sysmsg'] .= "{$upd1_ok}/{$rec1} 件 変更</font>";
    echo "$log_date 不適合処置連絡書ファイルの更新１ : {$rec1_ok}/{$rec1} 件登録しました。\n";
    echo "$log_date 不適合処置連絡書ファイルの更新１ : {$ins1_ok}/{$rec1} 件 追加 \n";
    echo "$log_date 不適合処置連絡書ファイルの更新１ : {$upd1_ok}/{$rec1} 件 変更 \n";
    fwrite($fpa, "$log_date 不適合処置連絡書ファイルの更新１ : $rec1_ok/$rec1 件登録しました。\n");
    fwrite($fpa, "$log_date 不適合処置連絡書ファイルの更新１ : {$ins1_ok}/{$rec1} 件 追加 \n");
    fwrite($fpa, "$log_date 不適合処置連絡書ファイルの更新１ : {$upd1_ok}/{$rec1} 件 変更 \n");
    fwrite($fpb, "$log_date 不適合処置連絡書ファイルの更新１ : $rec1_ok/$rec1 件登録しました。\n");
    fwrite($fpb, "$log_date 不適合処置連絡書ファイルの更新１ : {$ins1_ok}/{$rec1} 件 追加 \n");
    fwrite($fpb, "$log_date 不適合処置連絡書ファイルの更新１ : {$upd1_ok}/{$rec1} 件 変更 \n");
} else {
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>トランザクションファイルがありません！</font>";
    fwrite($fpa,"$log_date 不適合処置連絡書の更新ファイル１ :  {$file_orign1} がありません！\n");
    fwrite($fpb,"$log_date 不適合処置連絡書の更新ファイル１ :  {$file_orign1} がありません！\n");
    echo '$log_date 不適合処置連絡書の更新ファイル１ :  {$file_orign1} がありません！\n';
}

// 不適合処置連絡書２ 登録処理 準備作業
$file_orign2  = '/home/guest/daily/W#MICLDTN2.TXT';
$file_temp2   = '/home/guest/daily/W#MICLDTN2-TEMP.TXT';

// 不適合処置連絡書２ 登録処理
if(file_exists($file_orign2)){           // ファイルの存在チェック
    $fp = fopen($file_orign2,"r");
        ///////////// SJIS → EUC 変換ロジック START (SJISでEUCにない文字はNULLバイトに変換される事に注意)
    $fp_conv = fopen($file_temp2, 'w');  // EUC へ変換用
    while (!(feof($fp))) {
        $data = fgets($fp,800);     // 実際には120 でOKだが余裕を持って
        ///////////// SJIS → EUC 変換ロジック START (SJISでEUCにない文字はNULLバイトに変換される事に注意)
        //$data = mb_convert_encoding($data, 'UTF-8', 'SJIS');             // SJISをEUC-JPへ変換(auto)
        $data = mb_convert_encoding($data, 'eucJP-win', 'sjis-win');      // SJISをEUC-JPへ変換(auto)
        $data = str_replace("\0", ' ', $data);                            // NULLバイトをSPACEへ変換
        $data = mb_ereg_replace('　', '  ', $data);                       // 位置がズレるので全角スペースを半角へ
        $data = mb_ereg_replace('㈱', '||', $data);                       // 機種依存文字を規格文字へ一時変更
        $data = mb_ereg_replace('№', '@@', $data);                       // 機種依存文字を規格文字へ一時変更
        fwrite($fp_conv, $data);
    }
    fclose($fp);
    fclose($fp_conv);
    $fp = fopen($file_temp2, 'r');       // EUC へ変換後のファイル
    ///////////// SJIS → EUC 変換ロジック END
    $rec2 = 0;       // レコード№
    $rec2_ok = 0;    // 書込み成功レコード数
    $rec2_ng = 0;    // 書込み失敗レコード数
    $ins2_ok = 0;    // INSERT用カウンター
    $upd2_ok = 0;    // UPDATE用カウンター
    while (!feof($fp)) {            // ファイルのEOFチェック
        $data = fgetcsv($fp, 800, "_");     // 実レコードは150バイト デリミタはタブからアンダースコアへ変更
        if (feof($fp)) {
            break;
        }
        $rec2++;
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            // $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $data[3]  = str_replace('||', '(株)', $data[3]);    // 機種依存文字を規格文字へ変更
        $data[3]  = str_replace('@@', 'No.', $data[3]);     // 機種依存文字を規格文字へ変更
        $data[4]  = str_replace('||', '(株)', $data[4]);    // 機種依存文字を規格文字へ変更
        $data[4]  = str_replace('@@', 'No.', $data[4]);     // 機種依存文字を規格文字へ変更
        $data[5]  = str_replace('||', '(株)', $data[5]);    // 機種依存文字を規格文字へ変更
        $data[5]  = str_replace('@@', 'No.', $data[5]);     // 機種依存文字を規格文字へ変更
        $data[6]  = str_replace('||', '(株)', $data[6]);    // 機種依存文字を規格文字へ変更
        $data[6]  = str_replace('@@', 'No.', $data[6]);     // 機種依存文字を規格文字へ変更
        $data[7]  = str_replace('||', '(株)', $data[7]);    // 機種依存文字を規格文字へ変更
        $data[7]  = str_replace('@@', 'No.', $data[7]);     // 機種依存文字を規格文字へ変更
        $data[8]  = str_replace('||', '(株)', $data[8]);    // 機種依存文字を規格文字へ変更
        $data[8]  = str_replace('@@', 'No.', $data[8]);     // 機種依存文字を規格文字へ変更
        $data[9]  = str_replace('||', '(株)', $data[9]);    // 機種依存文字を規格文字へ変更
        $data[9]  = str_replace('@@', 'No.', $data[9]);     // 機種依存文字を規格文字へ変更
        $data[10] = str_replace('||', '(株)', $data[10]);   // 機種依存文字を規格文字へ変更
        $data[10] = str_replace('@@', 'No.', $data[10]);    // 機種依存文字を規格文字へ変更
        
        $query_chk = sprintf("SELECT assy_no FROM claim_disposal_details WHERE assy_no='%s' AND publish_no='%s' AND parts_no='%s'", $data[0], $data[1], $data[2]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なしはありえないので何もしない
        } else {
            ///// 登録あり update 使用
            $query = "UPDATE claim_disposal_details SET occur_cause1='{$data[3]}', occur_cause2='{$data[4]}', outflow_cause1='{$data[5]}', outflow_cause2='{$data[6]}',
                      occur_measures1='{$data[7]}', occur_measures2='{$data[8]}', outflow_measures1='{$data[9]}', outflow_measures2='{$data[10]}'
                      where assy_no='{$data[0]}' and publish_no='{$data[1]}' and parts_no='{$data[2]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                // $_SESSION['s_sysmsg'] .= "{$rec2}:レコード目のUPDATEに失敗しました!\n";
                fwrite($fpa, "$log_date 発行番号:{$data[1]} : {$rec2}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 発行番号:{$data[1]} : {$rec2}:レコード目のUPDATEに失敗しました!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec2_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec2_ok++;
                $upd2_ok++;
            }
        }
    }
    fclose($fp);
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$rec2_ok}/{$rec2} 件登録しました。</font><br><br>";
    // $_SESSION['s_sysmsg'] .= "<font color='white'>{$ins2_ok}/{$rec2} 件 追加<br>";
    // $_SESSION['s_sysmsg'] .= "{$upd2_ok}/{$rec2} 件 変更</font>";
    echo "$log_date 不適合処置連絡書ファイルの更新２ : {$rec2_ok}/{$rec2} 件登録しました。\n";
    echo "$log_date 不適合処置連絡書ファイルの更新２ : {$ins2_ok}/{$rec2} 件 追加 \n";
    echo "$log_date 不適合処置連絡書ファイルの更新２ : {$upd2_ok}/{$rec2} 件 変更 \n";
    fwrite($fpa, "$log_date 不適合処置連絡書ファイルの更新２ : $rec2_ok/$rec2 件登録しました。\n");
    fwrite($fpa, "$log_date 不適合処置連絡書ファイルの更新２ : {$ins2_ok}/{$rec2} 件 追加 \n");
    fwrite($fpa, "$log_date 不適合処置連絡書ファイルの更新２ : {$upd2_ok}/{$rec2} 件 変更 \n");
    fwrite($fpb, "$log_date 不適合処置連絡書ファイルの更新２ : $rec2_ok/$rec2 件登録しました。\n");
    fwrite($fpb, "$log_date 不適合処置連絡書ファイルの更新２ : {$ins2_ok}/{$rec2} 件 追加 \n");
    fwrite($fpb, "$log_date 不適合処置連絡書ファイルの更新２ : {$upd2_ok}/{$rec2} 件 変更 \n");
} else {
    // $_SESSION['s_sysmsg'] .= "<font color='yellow'>トランザクションファイルがありません！</font>";
    fwrite($fpa,"$log_date 不適合処置連絡書の更新ファイル２ : {$file_orign2} がありません！\n");
    fwrite($fpb,"$log_date 不適合処置連絡書の更新ファイル２ : {$file_orign2} がありません！\n");
    echo '$log_date 不適合処置連絡書の更新ファイル２ : {$file_orign2} がありません！\n';
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
fwrite($fpa,"$log_date : LANG = {$_ENV['LANG']}\n");    // fgetcsv()用のLANG環境変数の確認
fwrite($fpb,"$log_date : LANG = {$_ENV['LANG']}\n");    // fgetcsv()用のLANG環境変数の確認
fclose($fpa);      ////// 日報用ログ書込み終了
fclose($fpb);      ////// 日報用ログ書込み終了

// header('Location: ' . H_WEB_HOST . ACT . 'vendor_master_view.php');   // チェックリストへ
exit();
?>
