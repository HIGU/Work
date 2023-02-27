#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// 生産用 部品在庫 経歴(history) マスター 前日分の差分レコード更新用 cli版  //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/27 Created  parts_stock_history_master_ftp_cli3.php              //
// 2004/12/20 カナが取込めない不具合対策でphp(5.0.x) → php-4.3.9-cliに変更 //
//           parts_stock_sync_controlテーブルを追加しレコード制御で経歴追加 //
//            土曜日等に更新されたデータを手動操作で更新するために作成      //
//            ファイル名 W#HIZHS2 W#MIBZM2 に変えている事に注意             //
// 使用方法：AS側で CALL IPKK151C PARM(X'020041217F') を実行しPCOM転送後実行//
// 2004/12/21 sync_controlを使用すると当日分のデータに影響するため削除      //
// 2004/12/27 前日分の差分レコードの更新用に新規作成                        //
// 2006/08/30 コマンドラインオプションに -c php4.ini を追加 simplate.so対応 //
// 2006/09/05 文字化けの原因はfgetcsv()のLANG環境変数の設定である事が分かり //
//            cronの定義ファイル(as400get_ftp)にLANG=ja_JP,eucJPを追加し対応//
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');                // 日報用ログの日時
$fpa = fopen('/tmp/parts_stock.log', 'a');      // 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "在庫経歴・在庫マスター前日分の更新\n");
fwrite($fpb, "/var/www/html/system/daily/parts_stock_history_master_ftp_cli3.php\n");

// FTPのターゲットファイル
// 保存先のディレクトリとファイル名
$save_file2 = '/home/guest/daily/W#MIBZOOY.CSV';     // save file2

/********************************************
// コネクションを取る(FTP接続のオープン) 在庫経歴
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file1, $target_file1, FTP_ASCII)) {
            echo 'ftp_get download OK ', $target_file1, '→', $save_file1, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file1 . '→' . $save_file1 . "\n");
        } else {
            echo 'ftp_get() error ', $target_file1, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file1 . "\n");
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> 在庫経歴\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 在庫経歴\n");
}

// コネクションを取る(FTP接続のオープン) 在庫マスター
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file2, $target_file2, FTP_ASCII)) {
            echo 'ftp_get download OK ', $target_file2, '→', $save_file2, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file2 . '→' . $save_file2 . "\n");
        } else {
            echo 'ftp_get() error ', $target_file2, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file2 . "\n");
        }
    } else {
        echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "ftp_connect() error --> 在庫マスター\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 在庫マスター\n");
}
********************************************/



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 在庫経歴・在庫マスター db_connect() error \n");
    fwrite($fpb, "$log_date 在庫経歴・在庫マスター db_connect() error \n");
    echo "$log_date 在庫経歴・在庫マスター db_connect() error \n";
    exit();
}
// 在庫マスター 日報処理 準備作業
$file_orign  = $save_file2;
$file_debug  = '/home/guest/daily/debug/debug-MIBZMT_difference.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    // $rec_ng = 0;    // 書込み失敗レコード数  // 上で既に使用しているため初期化しない
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, ',');     // 実レコードは85バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        if ($rec <= $rec_tnk) continue;      // 更新済みのレコードは読み飛ばす
        $num  = count($data);       // フィールド数の取得
        if ($num < 2) {    // フィールド数チェック
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                fwrite($fpb, "$log_date AS/400 del record=$rec \n");
                echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 10 record=$rec \n");
                fwrite($fpb, "$log_date field not 10 record=$rec \n");
                echo "$log_date field not 10 record=$rec \n";
            }
           continue;
        }
        //$data[7]  = trim($data[7]);    // 棚番の無駄な余白を削除
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
            
        } else {
            ///// 登録あり update 使用
            $query = "UPDATE parts_stock_master SET
                            parts_no      ='{$data[0]}',
                            stock_id      ='{$data[1]}'
                WHERE parts_no='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 部品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
    fwrite($fpa, "$log_date TNKサーバー側レコード : $rec_tnk/$rec 件登録済みです。\n");
    fwrite($fpa, "$log_date 在庫マスター差分の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 在庫マスター差分の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 在庫マスター差分の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date TNKサーバー側レコード : $rec_tnk/$rec 件登録済みです。\n");
    fwrite($fpb, "$log_date 在庫マスター差分の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 在庫マスター差分の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 在庫マスター差分の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date TNKサーバー側レコード : $rec_tnk/$rec 件登録済みです。\n";
    echo "$log_date 在庫マスター差分の更新 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 在庫マスター差分の更新 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 在庫マスター差分の更新 : {$upd_ok}/{$rec} 件 変更 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
if ($rec_ng == 0) {
    query_affected_trans($con, 'commit');
} else {
    query_affected_trans($con, 'rollback');
}
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
