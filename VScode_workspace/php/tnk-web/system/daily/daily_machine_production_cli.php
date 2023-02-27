#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 設備製作部品仕掛C伝票日報(daily)処理                                     //
// AS/400 UKWLIB/W#SETUBIC：設備製作部品仕掛C伝票                           //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// 設備製作部品仕掛 C 抜出し      Q#SETUBIC   UKWLIB/W#SETUBIC              //
// 設備製作部品仕掛 C 抜出し 買掛 Q#SETUBICK  UKWLIB/W#SETUBICK             //
// \FTPTNK USER(AS400) ASFILE(W#SETUBICK) LIB(UKWLIB)                       //
//         PCFILE(W#SETUBICK.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2018/08/22 新規作成 daily_machine_production_cli.php                     //
// 2018/10/25 一応完成 仕訳・未払と買掛を対象期の稟議No.・年月ごとに集計    //
//            machine_production_masterで期毎の稟議No.一覧                  //
//            machine_production_totalで集計結果を参照できる                //
// 2018/10/29 仕訳は完全に同じ伝票があるので、一度すべて削除しINSERT        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "設備製作部品仕掛C伝票の更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_manufacture_cost_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 設備製作部品仕掛C伝票 db_connect() error \n");
    fwrite($fpb, "$log_date 設備製作部品仕掛C伝票 db_connect() error \n");
    echo "$log_date 設備製作部品仕掛C伝票 db_connect() error \n\n";
    exit();
}
$data_ki = 0;
///////// 設備製作部品仕掛C伝票ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SETUBIC.TXT';
$file_backup = '/home/guest/daily/backup/W#SETUBIC-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SETUBIC.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_fg = 0;    // 削除フラグ
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // 実レコードは183バイト デリミタは '|' アンダースコア'_'は使えない
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// UTF-8 へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
        }
        $data_ki = $data[12];                                   // 後ほど使用するデータの期を保管
        //$query_chk = sprintf("SELECT * FROM machine_production_denc WHERE den_ymd=%d and den_no=%d and den_eda=%d and den_gyo=%d and den_loan='%s' and den_account='%s' and den_break='%s' and den_money=%f and den_summary1='%s' and den_summary2='%s' and den_id='%s' and den_iymd=%d and den_ki=%d and den_rin='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12], $data[13]);
        $query_chk = sprintf("SELECT * FROM machine_production_denc WHERE den_ki=%d", $data[12]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $del_fg = 1;
            $query = "INSERT INTO machine_production_denc (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_rin, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 設備製作部品仕掛C伝票:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 設備製作部品仕掛C伝票:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            if ($del_fg == 0) {
                ///// 登録あり DELETE 使用
                $query_del = sprintf("DELETE FROM machine_production_denc WHERE den_ki=%d", $data[12]);
                query_affected_trans($con, $query_del);
                $del_fg = 1;
            }
            ///// 登録あり DELETE後 INSERT
            $query = "INSERT INTO machine_production_denc (den_ymd, den_no, den_eda, den_gyo, den_loan, den_account, den_break, den_money, den_summary1, den_summary2, den_id, den_iymd, den_ki, den_rin, den_kin)
                      VALUES(
                       {$data[0]} ,
                       {$data[1]} ,
                       {$data[2]} ,
                       {$data[3]} ,
                      '{$data[4]}',
                      '{$data[5]}',
                      '{$data[6]}',
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                       {$data[12]} ,
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 設備製作部品仕掛C伝票:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 設備製作部品仕掛C伝票:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            /*
            $query = "UPDATE machine_production_denc SET
                            den_ymd      = {$data[0]} ,
                            den_no       = {$data[1]} ,
                            den_eda      = {$data[2]} ,
                            den_gyo      = {$data[3]} ,
                            den_loan     ='{$data[4]}',
                            den_account  ='{$data[5]}',
                            den_break    ='{$data[6]}',
                            den_money    = {$data[7]} ,
                            den_summary1 ='{$data[8]}',
                            den_summary2 ='{$data[9]}',
                            den_id       ='{$data[10]}',
                            den_iymd     = {$data[11]} ,
                            den_ki       = {$data[12]} ,
                            den_rin    ='{$data[13]}'
                      where den_ymd={$data[0]} and den_no={$data[1]} and den_eda={$data[2]} and den_gyo={$data[3]} and den_loan='{$data[4]}' and den_account='{$data[5]}' and den_break='{$data[6]}' and den_money={$data[7]} and den_summary1='{$data[8]}' and den_summary2='{$data[9]}' and den_id='{$data[10]}' and den_iymd={$data[11]} and den_ki={$data[12]} and den_rin='{$data[13]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 設備製作部品仕掛C伝票:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 設備製作部品仕掛C伝票:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
            */
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 設備製作部品仕掛C伝票 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 設備製作部品仕掛C伝票 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 設備製作部品仕掛C伝票 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 設備製作部品仕掛C伝票 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 設備製作部品仕掛C伝票 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 設備製作部品仕掛C伝票 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 設備製作部品仕掛C伝票 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 設備製作部品仕掛C伝票 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 設備製作部品仕掛C伝票 : {$upd_ok}/{$rec} 件 変更 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign をBackupできません！\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign をBackupできません！\n");
            echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : 設備製作部品仕掛C伝票の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 設備製作部品仕掛C伝票の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 設備製作部品仕掛C伝票の更新ファイル {$file_orign} がありません！\n";
    $data_ki = 0;
}
// 金額計算（符号付け）符号付金額がないもののみ
// UPDATE時も考慮して時間は掛かるが、$data_kiで対象期のデータすべて再計算
$query_chk = sprintf("SELECT * FROM machine_production_denc WHERE den_ki=%d", $data_ki);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
        ///// 符号付金額がない場合は何もしない
} else {
    ///// 符号無しあり update 使用
    for ($r=0; $r<$rows; $r++) {
        // 貸借区分[3]が1の時そのまま それ以外は符号が逆になる
        if ($res[$r][4] == '1') {
            $kin = $res[$r][7];
        } else {
            $kin = $res[$r][7] * -1;
        }
        $query = "UPDATE machine_production_denc SET
                    den_kin = {$kin}
                    where den_ymd={$res[$r][0]} and den_no={$res[$r][1]} and den_eda={$res[$r][2]} and den_gyo={$res[$r][3]} and den_loan='{$res[$r][4]}' and den_account='{$res[$r][5]}' and den_break='{$res[$r][6]}' and den_money={$res[$r][7]} and den_summary1='{$res[$r][8]}' and den_summary2='{$res[$r][9]}' and den_id='{$res[$r][10]}' and den_iymd={$res[$r][11]} and den_ki={$res[$r][12]} and den_rin='{$res[$r][13]}'";
        query_affected_trans($con, $query);
    }
}

///////// 設備製作部品仕掛C 買掛伝票ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#SETUBICK.TXT';
$file_backup = '/home/guest/daily/backup/W#SETUBICK-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-SETUBICK.TXT';
$data_ki = 0;
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '|');     // 実レコードは183バイト デリミタは '|' アンダースコア'_'は使えない
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 14) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 14 record=$rec \n");
            fwrite($fpb, "$log_date field not 14 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// UTF-8 へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
        }
        $data_ki = $data[3];                                   // 後ほど使用するデータの期を保管
        //$query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_uke='%s'", $data[2]);
        $query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_uke='%s' and den_type='%s' and den_symd=%d", $data[2], $data[1], $data[6]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO machine_production_kai_denc (den_rin, den_type, den_uke, den_ki, den_uymd, den_kymd, den_symd, den_tori, den_tan, den_gnum, den_snum, den_toku, den_div, den_kamoku, den_kin)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}',
                       {$data[3]} ,
                       {$data[4]} ,
                       {$data[5]} ,
                       {$data[6]} ,
                      '{$data[7]}',
                       {$data[8]} ,
                       {$data[9]} ,
                       {$data[10]} ,
                       {$data[11]} ,
                      '{$data[12]}',
                      '{$data[13]}',
                      0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 設備製作部品仕掛C買掛伝票:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 設備製作部品仕掛C買掛伝票:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE machine_production_kai_denc SET
                            den_rin      ='{$data[0]}',
                            den_type     ='{$data[1]}',
                            den_uke      ='{$data[2]}',
                            den_ki       = {$data[3]} ,
                            den_uymd     = {$data[4]} ,
                            den_kymd     = {$data[5]} ,
                            den_symd     = {$data[6]} ,
                            den_tori     ='{$data[7]}',
                            den_tan      = {$data[8]} ,
                            den_gnum     = {$data[9]} ,
                            den_snum     = {$data[10]} ,
                            den_toku     = {$data[11]} ,
                            den_div      ='{$data[12]}',
                            den_kamoku   ='{$data[13]}'
                      where den_uke='{$data[2]}' and den_type='{$data[1]}' and den_symd={$data[6]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 設備製作部品仕掛C買掛伝票:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 設備製作部品仕掛C買掛伝票:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 設備製作部品仕掛C買掛伝票 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 設備製作部品仕掛C買掛伝票 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 設備製作部品仕掛C買掛伝票 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 設備製作部品仕掛C買掛伝票 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 設備製作部品仕掛C買掛伝票 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 設備製作部品仕掛C買掛伝票 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 設備製作部品仕掛C買掛伝票 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 設備製作部品仕掛C買掛伝票 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 設備製作部品仕掛C買掛伝票 : {$upd_ok}/{$rec} 件 変更 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            fwrite($fpa, "$log_date DownLoad File $file_orign をBackupできません！\n");
            fwrite($fpb, "$log_date DownLoad File $file_orign をBackupできません！\n");
            echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
} else {
    fwrite($fpa, "$log_date : 設備製作部品仕掛C買掛伝票の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : 設備製作部品仕掛C買掛伝票の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : 設備製作部品仕掛C買掛伝票の更新ファイル {$file_orign} がありません！\n";
    $data_ki = 0;
}

if ($data_ki<>0) {
    // 金額計算（符号付け）符号付金額がないもののみ
    // UPDATE時も考慮して時間は掛かるが、$data_kiで対象期のデータすべて再計算
    $query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_ki=%d", $data_ki);
    //$query_chk = sprintf("SELECT * FROM machine_production_kai_denc WHERE den_kin='0'");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 符号付金額がない場合は何もしない
    } else {
        ///// 符号無しあり update 使用
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][11] > 0) {
                $allo = $data[11] / 100;
                $kin  = round(($res[$r][8] * $res[$r][10] * $allo),0);
            } else {
                $kin = round(($res[$r][8] * $res[$r][10]),0);
            }
            $query = "UPDATE machine_production_kai_denc SET
                        den_kin = {$kin}
                        where den_uke='{$res[$r][2]}' and den_type='{$res[$r][1]}' and den_symd={$res[$r][6]}";
            query_affected_trans($con, $query);
        }
    }
    
    // 集計計算
    // 両方とも同じ期であると想定してPGM設計
    // 対象期の管理No.一覧を更新 仕訳・未払から
    $query_chk = sprintf("SELECT DISTINCT den_rin ,den_ki FROM machine_production_denc as d WHERE d.den_ki=%d and NOT EXISTS(SELECT 1 FROM machine_production_master as m WHERE d.den_rin = m.kanri_no and m.total_ki=%d)", $data_ki, $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 一覧に追加の必要がないので何もしない
    } else {
        ///// 一覧に追加の必要有り insert 使用
        for ($r=0; $r<$rows; $r++) {
            $query = "INSERT INTO machine_production_master (kanri_no, total_ki)
                          VALUES(
                          '{$res[$r][0]}',
                           {$data_ki})";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                break;                                  // debug
            }
        }
    }
    // 同様に買掛伝票の一覧から管理No.一覧を更新
    $query_chk = sprintf("SELECT DISTINCT den_rin ,den_ki FROM machine_production_kai_denc as d WHERE d.den_ki=%d and NOT EXISTS(SELECT 1 FROM machine_production_master as m WHERE d.den_rin = m.kanri_no and m.total_ki=%d)", $data_ki, $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 一覧に追加の必要がないので何もしない
    } else {
        ///// 一覧に追加の必要有り insert 使用
        for ($r=0; $r<$rows; $r++) {
            $query = "INSERT INTO machine_production_master (kanri_no, total_ki)
                          VALUES(
                          '{$res[$r][0]}',
                           {$data_ki})";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                break;                                  // debug
            }
        }
    }
    // 更新した管理No.一覧より、買掛・仕訳・未払のデータより各月の金額を集計
    // 伝票種類は 買掛 ⇒ 買掛、仕訳・未払⇒仕訳 で統一 machine_production_total
    // 初期設定
    // 開始月
    //$str_ym = ($data_ki + 1955) * 100 + 4;   // YYYY × 100 ⇒ YYYY00 ⇒ ＋4で 
    $str_y   = $data_ki + 1955;
    $str_ym  = $str_y * 100 + 4;   // YYYY × 100 ⇒ YYYY00 ⇒ ＋4で
    $end_chk = $str_ym + 100 - 1;  // 201704 + 100 - 1 = 201803
    $end_ym  = date("Ym");
    if ($end_chk < $end_ym) {
        $end_ym = $str_ym + 100 - 1; // 当期より前のデータ登録に対応の為、endを期の最終月へ
    }
    $end_mm  = substr($end_ym, 4,2);
    if ($end_mm < 4) {
        $num = $end_ym - $str_ym - 87;
    } else {
        $num = $end_ym - $str_ym + 1;
    }
    $total_ym = array();
    for ($r=0; $r<$num; $r++) {
        if ($r < 9) {
            $total_ym[$r] = $str_ym + $r;
        } else {
            $total_ym[$r] = $str_ym + $r + 88;
        }
    }
    $total_num = count($total_ym);
    
    // 仕訳・未払の集計
    $den_name = '仕訳';
    $kin = 0;
    $query_chk = sprintf("select kanri_no FROM machine_production_master WHERE total_ki=%d ORDER BY kanri_no", $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 一覧がないので何もしない
    } else {
        ///// すべての管理Noで繰り返し
        for ($r=0; $r<$rows; $r++) {
            ///// 各月の伝票を集計
            for ($i=0; $i<$total_num; $i++) {
                $str_ymd = $total_ym[$i] * 100 + 1;
                $end_ymd = $total_ym[$i] * 100 + 99;
                $query_sum = sprintf("select SUM(den_kin) FROM machine_production_denc WHERE den_ymd>=%d and den_ymd<=%d and den_rin='%s'", $str_ymd, $end_ymd, $res[$r][0]);
                if (($rows_sum = getResultWithField3($query_sum, $field_sum, $res_sum)) <= 0) {
                    $kin = 0;
                } else {
                    if($res_sum[0][0] == "") {
                        $kin = 0;
                    } else {
                        $kin = $res_sum[0][0];
                    }
                }
                $query_chk = sprintf("select * FROM machine_production_total WHERE kanri_no='%s' and total_ki=%d and total_ym=%d and total_den='%s' ORDER BY kanri_no", $res[$r][0], $data_ki, $total_ym[$i], $den_name);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = "INSERT INTO machine_production_total (kanri_no, total_ki, total_ym, total_kin, total_den)
                              VALUES(
                              '{$res[$r][0]}',
                               {$data_ki} ,
                               {$total_ym[$i]} ,
                               {$kin} ,
                              '{$den_name}')";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        break;                                  // debug
                    }
                } else {
                    ///// 登録あり update 使用
                    $query = "UPDATE machine_production_total SET
                                    total_kin    = {$kin}
                              WHERE kanri_no='{$res[$r][0]}' and total_ki={$data_ki} and total_ym={$total_ym[$i]} and total_den='{$den_name}'";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        break;                                  // debug
                    }
                }
            }
        }
    }
    $den_name = '買掛';
    $kin = 0;
    $query_chk = sprintf("select kanri_no FROM machine_production_master WHERE total_ki=%d ORDER BY kanri_no", $data_ki);
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 一覧がないので何もしない
    } else {
        ///// すべての管理Noで繰り返し
        for ($r=0; $r<$rows; $r++) {
            ///// 各月の伝票を集計
            for ($i=0; $i<$total_num; $i++) {
                $str_ymd = $total_ym[$i] * 100 + 1;
                $end_ymd = $total_ym[$i] * 100 + 99;
                $query_sum = sprintf("select SUM(den_kin) FROM machine_production_kai_denc WHERE den_kymd>=%d and den_kymd<=%d and den_rin='%s'", $str_ymd, $end_ymd, $res[$r][0]);
                if (($rows_sum = getResultWithField3($query_sum, $field_sum, $res_sum)) <= 0) {
                    $kin = 0;
                } else {
                    if($res_sum[0][0] == "") {
                        $kin = 0;
                    } else {
                        $kin = $res_sum[0][0];
                    }
                }
                $query_chk = sprintf("select * FROM machine_production_total WHERE kanri_no='%s' and total_ki=%d and total_ym=%d and total_den='%s' ORDER BY kanri_no", $res[$r][0], $data_ki, $total_ym[$i], $den_name);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = "INSERT INTO machine_production_total (kanri_no, total_ki, total_ym, total_kin, total_den)
                              VALUES(
                              '{$res[$r][0]}',
                               {$data_ki} ,
                               {$total_ym[$i]} ,
                               {$kin} ,
                              '{$den_name}')";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        break;                                  // debug
                    }
                } else {
                    ///// 登録あり update 使用
                    $query = "UPDATE machine_production_total SET
                                    total_kin    = {$kin}
                              WHERE kanri_no='{$res[$r][0]}' and total_ki={$data_ki} and total_ym={$total_ym[$i]} and total_den='{$den_name}'";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        break;                                  // debug
                    }
                }
            }
        }
    }
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
