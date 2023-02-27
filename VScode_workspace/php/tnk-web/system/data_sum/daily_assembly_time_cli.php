#!/usr/loca l/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 組立作業時間(daily)処理  (スケジューラーより)                            //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2020/02/03 Created   daily_assembly_time_cli.php                         //
// 2020/02/25 変換登録なし、計画NOの1文字目がCLTではない場合はCにするよう   //
//            変更（@計画で変換登録ない場合）                               //
// 2020/09/29 データが始めに保管される場所を変更                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');
/////// 処理報告用 変数 初期化
$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$msg   = '';        // メッセージ
$flag1 = '';        // 処理実行フラグ 売上
$flag2 = '';        // 処理実行フラグ アイテム
$flag3 = '';        // 処理実行フラグ 製品仕掛
$flag4 = '';        // 処理実行フラグ 労務費・経費
$b     = 0;         // テキストファイルのレコード数
$c     = 0;
$d     = 0;
$e     = 0;

// 組立作業時間 日報処理 準備作業
// ファイル名はスケジューラー側で決めるので決定後直す
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
// 以下が正しい
//$file_name  = '/home/guest/daily/FLEXSCHE/CRESULT_WEB.CSV';
//$file_temp  = '/home/guest/daily/CRESULT_WEB.tmp';
//$file_write = '/home/guest/daily/CRESULT_WEB.txt';

$file_name  = '/home/guest/daily/CRESULT_WEB.CSV';
$file_temp  = '/home/guest/daily/CRESULT_WEB.tmp';
$file_write = '/home/guest/daily/CRESULT_WEB.txt';
///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'KV', 'UTF-8'); // 半角カナを全角カナに変換 (DB保存時は全角で照会時は必要に応じて半角変換する)
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp, 'r');
    $fpw = fopen($file_write, 'a');
    while (FALSE !== ($data = fgetcsv($fp, 300, ',')) ) {    // CSV file として読込み
        if ($data[0] == '' && $data[1] == '') continue;   // 空行の処理
        $data[1] = str_replace('"', '', $data[1]);  // なぜか？"の入る位置がズレるのと￥まで書込まれるので削除する
                                                    // 上記は下のpg_escape_string()以前の問題である
        //$data[1] = pg_escape_string($data[1]);      // 品名
        //$data[2] = pg_escape_string($data[2]);      // 材質
        //$data[3] = pg_escape_string($data[3]);      // 親機種
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\t{$data[5]}\t{$data[6]}\t{$data[7]}\n");
        ///// 文字列内(品名等)に","があった場合は fgetcsv()にまかせる。
    }
    fclose($fp);
    fclose($fpw);
    // unlink($file_name);     // 一時ファイルを削除 CSV
    // unlink($file_temp);     // 一時ファイルを削除 tmp
    if (file_exists("{$file_name}.bak")) {
        unlink("{$file_name}.bak");         // 前回のデータを削除
    }
    if (file_exists("{$file_temp}.bak")) {
        unlink("{$file_temp}.bak");         // 前回のデータを削除
    }
    if (!rename($file_name, "{$file_name}.bak")) {
        echo "$log_date DownLoad File $file_name をBackupできません！\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        echo "$log_date DownLoad File $file_temp をBackupできません！\n";
    }
    // exit(); // debug用
}

// 組立作業時間取得 日報処理
// ファイル名はスケジューラー側で決めるので決定後直す
$file_name = '/home/guest/daily/CRESULT_WEB.txt';
$file_name_bak = '/home/guest/daily/backup/CRESULT_WEB-bak.txt';
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $miitem_ng_flg = FALSE;      // ＤＢ書込みＮＧフラグ
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin トランザクション開始
        if ( !($con = db_connect()) ) {
            $msg .= "データベースに接続できません\n";
        } else {
            query_affected_trans($con, 'begin');
            while ($data = fgetcsv($fp, 200, "\t")) {
                // $num = count($data);     // CSV File の field 数
                $rowcsv++;
                //$data[1] = addslashes($data[1]);    // "'"等がデータにある場合に\でエスケープする
                //$data[1] = trim($data[1]);          // 部品名の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
                //$data[2] = trim($data[2]);          // 材質名の前後のスペースを削除
                //$data[3] = trim($data[3]);          // 親機種の前後のスペースを削除
                // group_no $data[0]を変換
                $chk = "select group_no from assembly_line_change where line_no='{$data[0]}'";
                if (getUniResult($chk, $group_no) <= 0) {    // グループNoマスターにあるか
                    // 登録ない場合
                    // 計画Noのチェック
                    if (substr($data[1], 0, 1) == 'C') {
                        $data[0] = 5;
                    } elseif (substr($data[1], 0, 1) == 'L') {
                        $data[0] = 9;
                    } elseif (substr($data[1], 0, 1) == 'T') {
                        $data[0] = 11;
                    } else {    // 変換登録なしで@計画の場合はとりあえずカプラに
                        $query_chk = sprintf("select parts_no FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
                        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                            // 計画Noが見つからなければとりあえずカプラに
                            $data[0] = 5;
                        } else {
                            // 計画Noから部品番号を取得し１文字目で判別
                            if (substr($res_chk[0], 0, 1) == 'C') {
                                $data[0] = 5;
                            } elseif (substr($res_chk[0], 0, 1) == 'L') {
                                $data[0] = 9;
                            } elseif (substr($res_chk[0], 0, 1) == 'T') {
                                $data[0] = 11;
                            } else {
                                // 部品番号でも判別できなければカプラ
                                $data[0] = 5;
                            }
                        }
                    }
                } else {
                    // 登録ある場合
                    $data[0] = $group_no;
                }
                ///////// 登録済みのチェック
                $query_chk = sprintf("select serial_no from assembly_process_time where plan_no='%s' and user_id='%s' and str_time='%s'", $data[1], $data[2], $data[3]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = sprintf("insert into assembly_process_time (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, assy_time, plan_pcs)
                            values('%s','%s','%s','%s','%s',%d,'%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "assembly_process_time insert error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_in++;      // insert 成功
                    }
                } else {
                /*
                    ///// 登録あり update 使用
                    $query = sprintf("update miitem set mipn='%s', midsc='%s', mzist='%s', mepnt='%s', madat=%d
                            where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "miitem update error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_up++;      // update 成功
                    }
                */
                }
            }
        }
        /////////// commit トランザクション終了
        if ($miitem_ng_flg) {
            query_affected_trans($con, 'rollback');     // transaction rollback
        } else {
            query_affected_trans($con, 'commit');       // 書込み完了
        }
    } else {
        $msg .= "Q#TEST.txtをオープン出来ません\n";
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if (file_exists($file_name_bak)) unlink($file_name_bak);    // 前回のバックアップを削除
    if (!rename($file_name, $file_name_bak)) {                  // 今回のデータをバックアップ
        echo "$log_date {$file_name} をBackupできません！\n";
    }
    $flag2 = 1;
}


// メッセージを返す
if ($flag2==1) {
    $msg .= "{$log_date} 組立作業時間更新\n";
    $msg .= "{$log_date} insert $row_in 件\n";
    $msg .= "{$log_date} update $row_up 件\n";
    $msg .= "{$log_date} CSV_file $rowcsv 件\n";
    $msg .= "{$log_date} Original $c 件\n";
} else {
    $msg .= "{$log_date}:組立作業時間の更新データがありません。\n";
}

// 組立作業時間 日報処理 準備作業
// ファイル名はスケジューラー側で決めるので決定後直す
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
// 以下が正しい
//$file_name  = '/home/guest/daily/FLEXSCHE/LRESULT_WEB.CSV';
//$file_temp  = '/home/guest/daily/LRESULT_WEB.tmp';
//$file_write = '/home/guest/daily/LRESULT_WEB.txt';

$file_name  = '/home/guest/daily/LRESULT_WEB.CSV';
$file_temp  = '/home/guest/daily/LRESULT_WEB.tmp';
$file_write = '/home/guest/daily/LRESULT_WEB.txt';
///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'KV', 'UTF-8'); // 半角カナを全角カナに変換 (DB保存時は全角で照会時は必要に応じて半角変換する)
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp, 'r');
    $fpw = fopen($file_write, 'a');
    while (FALSE !== ($data = fgetcsv($fp, 300, ',')) ) {    // CSV file として読込み
        if ($data[0] == '' && $data[1] == '') continue;   // 空行の処理
        $data[1] = str_replace('"', '', $data[1]);  // なぜか？"の入る位置がズレるのと￥まで書込まれるので削除する
                                                    // 上記は下のpg_escape_string()以前の問題である
        //$data[1] = pg_escape_string($data[1]);      // 品名
        //$data[2] = pg_escape_string($data[2]);      // 材質
        //$data[3] = pg_escape_string($data[3]);      // 親機種
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\t{$data[5]}\t{$data[6]}\t{$data[7]}\n");
        ///// 文字列内(品名等)に","があった場合は fgetcsv()にまかせる。
    }
    fclose($fp);
    fclose($fpw);
    // unlink($file_name);     // 一時ファイルを削除 CSV
    // unlink($file_temp);     // 一時ファイルを削除 tmp
    if (file_exists("{$file_name}.bak")) {
        unlink("{$file_name}.bak");         // 前回のデータを削除
    }
    if (file_exists("{$file_temp}.bak")) {
        unlink("{$file_temp}.bak");         // 前回のデータを削除
    }
    if (!rename($file_name, "{$file_name}.bak")) {
        echo "$log_date DownLoad File $file_name をBackupできません！\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        echo "$log_date DownLoad File $file_temp をBackupできません！\n";
    }
    // exit(); // debug用
}

// 組立作業時間取得 日報処理
// ファイル名はスケジューラー側で決めるので決定後直す
$file_name = '/home/guest/daily/LRESULT_WEB.txt';
$file_name_bak = '/home/guest/daily/backup/LRESULT_WEB-bak.txt';
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $miitem_ng_flg = FALSE;      // ＤＢ書込みＮＧフラグ
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin トランザクション開始
        if ( !($con = db_connect()) ) {
            $msg .= "データベースに接続できません\n";
        } else {
            query_affected_trans($con, 'begin');
            while ($data = fgetcsv($fp, 200, "\t")) {
                // $num = count($data);     // CSV File の field 数
                $rowcsv++;
                //$data[1] = addslashes($data[1]);    // "'"等がデータにある場合に\でエスケープする
                //$data[1] = trim($data[1]);          // 部品名の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
                //$data[2] = trim($data[2]);          // 材質名の前後のスペースを削除
                //$data[3] = trim($data[3]);          // 親機種の前後のスペースを削除
                // group_no $data[0]を変換
                $chk = "select group_no from assembly_line_change where line_no='{$data[0]}'";
                if (getUniResult($chk, $group_no) <= 0) {    // グループNoマスターにあるか
                    // 登録ない場合
                    // 計画Noのチェック
                    if (substr($data[1], 0, 1) == 'C') {
                        $data[0] = 5;
                    } elseif (substr($data[1], 0, 1) == 'L') {
                        $data[0] = 9;
                    } elseif (substr($data[1], 0, 1) == 'T') {
                        $data[0] = 11;
                    } else {    // 変換登録なしで@計画の場合はとりあえずカプラに
                        $query_chk = sprintf("select parts_no FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
                        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                            // 計画Noが見つからなければとりあえずカプラに
                            $data[0] = 5;
                        } else {
                            // 計画Noから部品番号を取得し１文字目で判別
                            if (substr($res_chk[0], 0, 1) == 'C') {
                                $data[0] = 5;
                            } elseif (substr($res_chk[0], 0, 1) == 'L') {
                                $data[0] = 9;
                            } elseif (substr($res_chk[0], 0, 1) == 'T') {
                                $data[0] = 11;
                            } else {
                                // 部品番号でも判別できなければカプラ
                                $data[0] = 5;
                            }
                        }
                    }
                } else {
                    // 登録ある場合
                    $data[0] = $group_no;
                }
                ///////// 登録済みのチェック
                $query_chk = sprintf("select serial_no from assembly_process_time where plan_no='%s' and user_id='%s' and str_time='%s'", $data[1], $data[2], $data[3]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = sprintf("insert into assembly_process_time (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, assy_time, plan_pcs)
                            values('%s','%s','%s','%s','%s',%d,'%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "assembly_process_time insert error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_in++;      // insert 成功
                    }
                } else {
                /*
                    ///// 登録あり update 使用
                    $query = sprintf("update miitem set mipn='%s', midsc='%s', mzist='%s', mepnt='%s', madat=%d
                            where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "miitem update error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_up++;      // update 成功
                    }
                */
                }
            }
        }
        /////////// commit トランザクション終了
        if ($miitem_ng_flg) {
            query_affected_trans($con, 'rollback');     // transaction rollback
        } else {
            query_affected_trans($con, 'commit');       // 書込み完了
        }
    } else {
        $msg .= "Q#TEST.txtをオープン出来ません\n";
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if (file_exists($file_name_bak)) unlink($file_name_bak);    // 前回のバックアップを削除
    if (!rename($file_name, $file_name_bak)) {                  // 今回のデータをバックアップ
        echo "$log_date {$file_name} をBackupできません！\n";
    }
    $flag2 = 1;
}


// メッセージを返す
if ($flag2==1) {
    $msg .= "{$log_date} 組立作業時間更新\n";
    $msg .= "{$log_date} insert $row_in 件\n";
    $msg .= "{$log_date} update $row_up 件\n";
    $msg .= "{$log_date} CSV_file $rowcsv 件\n";
    $msg .= "{$log_date} Original $c 件\n";
} else {
    $msg .= "{$log_date}:組立作業時間の更新データがありません。\n";
}
/*
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "日報(daily)処理\n");
fwrite($fpb, "/var/www/html/system/daily/daily_cli.php\n");
fwrite($fpb, "------------------------------------------------------------------------\n");
fwrite($fpb, "組立作業時間の更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_miitem_cli.php\n");

fwrite($fpa, $msg);
fwrite($fpb, $msg);
echo "$msg";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
*/
exit();
?>
