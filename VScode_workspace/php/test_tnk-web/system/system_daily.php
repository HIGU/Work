<?php
//////////////////////////////////////////////////////////////////////////////
// 日報(daily weekly)　処　理  (実際にはdailyで処理している)                //
// Copyright (C) 2002-2010      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/02/22 Created  system_daily.php                                     //
// 2002/08/08 セッション管理に変更                                          //
// 2002/11/11 stderr(2)→stdout(1) 2>&1 を 追加                             //
// 2002/12/03 サイトメニューに追加のため site_index と site_id 追加         //
// 2003/06/04 miitem の last_date last_user 分を \t \N にして書込み追加     //
// 2003/06/20 miitem の psql から insert update Transaction 処理に変更      //
// 2003/11/28 miitem の$str_flgを\r検出時リセットする前の行の不具合対策     //
//            miitem の書込み失敗時に break する様に変更。大量エラー対応    //
//            miitem を2002/02/ 以前のデータを全てコンバートしなおした。    //
// 2003/12/22 miitem の部品名を半角カナのまま使用するように変更 中止        //
// 2004/01/08 労務費・経費サマリーと製品仕掛で \copyの仕様が変わり (V7.4)   //
//            ERROR:end-of-copy marker does not match previous newline style//
//            その為 CRLF → LF のみにファイルを生成するようにロジック変更  //
// 2004/01/13 MLで fgetcsv()の仕様が変わった事を知りmiitemにも上記を摘要    //
// 2004/10/15 AS/400からPCIXを使用したFTP転送に切替えたため trim()を使用    //
// 2005/03/04 dir変更 /home/www/html/monthly/ → /home/guest/monthly/       //
// 2005/10/06 製品仕掛サマリー／労務費・経費サマリーを分離したDBServerに対応//
// 2007/10/05 労務費・経費サマリー更新時に旧データのチェックをし削除を追加  //
// 2007/10/16 上記の処理結果のメッセージに \n → \\n へ修正                 //
// 2010/05/19 販管費の取り込みを追加                                   大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 180);        // 最大実行時間=3分
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start();  //Warning: Cannot add header の対策のため追加。
require_once ('../function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name は自動取得

$_SESSION['site_index'] = 99;               // 最後のメニューにするため 99 を使用
$_SESSION['site_id'] = 10;                  // 下位メニュー無し (0 < であり)

if ($_SESSION['Auth'] <= 2) {
    $_SESSION['s_sysmsg'] = 'システム管理メニューは管理者のみ使用できます。';
    header('Location: http:' . WEB_HOST);
    exit();
}
/////// 処理報告用 変数 初期化
$msg   = "";        // メッセージ
$flag1 = "";        // 処理実行フラグ 売上
$flag2 = "";        // 処理実行フラグ アイテム
$flag3 = "";        // 処理実行フラグ 製品仕掛
$flag4 = "";        // 処理実行フラグ 労務費・経費
$flag5 = "";        // 処理実行フラグ 販管費
$b     = 0;         // テキストファイルのレコード数
$c     = 0;
$d     = 0;
$e     = 0;
$f     = 0;

// 売上 日報処理 準備作業       // 現在は FTP 転送に切替えたため使用していない
$file_name = "/home/www/html/daily/HIUURI.CSV";
$file_write = "/home/www/html/daily/HIUURI.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // １文字 読込
        $data = mb_convert_encoding($data, "UTF-8", "SJIS");       // SJISをEUC-JPへ変換
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\r":
            $b++;
            fwrite($fpw,"\t\\N\t\\N\r");      // last_date last_user 分を \t \N にして書込む
            break;
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // 一時ファイルを削除 CSV
}

// 売上 日報処理       // 現在は FTP 転送に切替えたため使用していない
$file_name = "/home/www/html/daily/HIUURI.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    ///////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result1 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/daily/hiuuri 2>&1`;
    unlink($file_name);     // 一時ファイルを削除 txt
    $flag1 = 1;
}



// アイテムマスター 週単位処理 準備作業
// $file_name = "/home/www/html/weekly/Q#MIITEM.CSV";
$file_name  = "/home/guest/daily/Q#MIITEM.CSV";
$file_temp  = "/home/guest/daily/Q#MIITEM.tmp";
$file_write = "/home/guest/daily/Q#MIITEM.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
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
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    $str_flg = 0;       // 文字列フィールド内かどうかのフラグ
    while (FALSE!==($data = fgetc($fp)) ) {           // １文字 読込
        switch ($data) {
        case '"':
            if ($str_flg == 0) {
                $str_flg = 1;       // 文字列内フィールドにセット
            } else {
                $str_flg = 0;       // 文字列外フィールドにセット
            }
            break;
        case ',':
            if ($str_flg == 0)           // 文字列外の ',' カンマならタブに変更
                fwrite($fpw,"\t");
            else
                fwrite($fpw,$data); // 文字列内の ',' カンマならそのまま書き込む
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");      // last_date last_user 分を \t \N にして書込む
            $str_flg = 0;   // CR を検出したら文字列フラグをリセット(不当な"の対策)
            break;
        case "\r":
            break;                          // CR は読み飛ばす
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // 一時ファイルを削除 CSV
    unlink($file_temp);     // 一時ファイルを削除 tmp
}

// アイテムマスター 週単位処理
$file_name = "/home/guest/daily/Q#MIITEM.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $miitem_ng_flg = FALSE;      // ＤＢ書込みＮＧフラグ
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin トランザクション開始
        if ( !($con = db_connect()) ) {
            $msg .= "データベースに接続できません<br>";
        } else {
            query_affected_trans($con, 'begin');
            while ($data = fgetcsv($fp, 200, "\t")) {
                // $num = count($data);     // CSV File の field 数
                $rowcsv++;
                $data[1] = addslashes($data[1]);    // "'"等がデータにある場合に\でエスケープする
                $data[1] = trim($data[1]);          // 部品名の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
                $data[2] = trim($data[2]);          // 材質名の前後のスペースを削除
                $data[3] = trim($data[3]);          // 親機種の前後のスペースを削除
                ///////// 登録済みのチェック
                $query_chk = sprintf("select mipn from miitem where mipn='%s'", $data[0]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = sprintf("insert into miitem (mipn, midsc, mzist, mepnt, madat)
                            values('%s','%s','%s','%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "miitem insert error rec No.=$rowcsv <br>";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_in++;      // insert 成功
                    }
                } else {
                    ///// 登録あり update 使用
                    $query = sprintf("update miitem set mipn='%s', midsc='%s', mzist='%s', mepnt='%s', madat=%d
                            where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "miitem update error rec No.=$rowcsv <br>";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_up++;      // update 成功
                    }
                }
            }
        }
        /////////// commit トランザクション終了
        if ($miitem_ng_flg) {
            query_affected_trans($con, "rollback");     // transaction rollback
        } else {
            query_affected_trans($con, "commit");       // 書込み完了
        }
    } else {
        $msg .= "Q#MIITEM.txtをオープン出来ません<br>";
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if ( !($miitem_ng_flg) ) {
        unlink($file_name);     // 一時ファイルを削除 txt
    }
    $flag2 = 1;
}



// 製品仕掛(投入実際と完成標準)サマリーファイル 実際金額をつかむためのファイルとして使用 準備作業
$file_name = "/home/guest/monthly/Q#SGKSIKP.CSV";
$file_temp = "/home/guest/monthly/Q#SGKSIKP.tmp";
$file_write = "/home/guest/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "UTF-8", "SJIS");       // SJISをEUC-JPへ変換
        //  半角カナデータなし $data_KV = mb_convert_kana($data);           // 半角カナを全角カナに変換
        fwrite($fpw,$data);
        $d++;
        if (feof($fp)) {
            $d--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // １文字 読込
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");    // last_date last_user 分を \t \N にして書込む
            break;
        case "\r":
            break;                          // CR は読み飛ばす
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // 一時ファイルを削除 CSV
    unlink($file_temp);     // 一時ファイルを削除 tmp
}

// 製品仕掛(投入実際と完成標準)サマリーファイル  月単位処理
$file_name = "/home/guest/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    //////////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result3 = `/usr/local/pgsql/bin/psql -h 10.1.3.247 TnkSQL < /home/guest/monthly/sgksikp 2>&1`;
    unlink($file_name);     // 一時ファイルを削除 txt
    $flag3 = 1;
}


// 労務費・経費サマリーファイル download 準備作業
$file_name = "/home/guest/monthly/AAYLAWL2.CSV";
$file_temp = "/home/guest/monthly/AAYLAWL2.tmp";
$file_write = "/home/guest/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "UTF-8", "SJIS");       // SJISをEUC-JPへ変換
        //  半角カナデータなし $data_KV = mb_convert_kana($data);           // 半角カナを全角カナに変換
        fwrite($fpw,$data);
        $e++;
        if (feof($fp)) {
            $e--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // １文字 読込
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");    // last_date last_user 分を \t \N にして書込む
            break;
        case "\r":
            break;                          // CR は読み飛ばす
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // 一時ファイルを削除 CSV
    unlink($file_temp);     // 一時ファイルを削除 tmp
}

// 労務費・経費サマリーファイル  月単位処理 本作業
$file_name = "/home/guest/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $chk_data = fgetcsv($fp, 300, "\t");
    $query = "SELECT * FROM act_summary WHERE act_yymm = {$chk_data[2]} LIMIT 1";
    if (getUniResult($query, $res) > 0) {
        $sql = "DELETE FROM act_summary WHERE act_yymm = {$chk_data[2]}";
        $del_cnt = query_affected($sql);
        $msg .= "製造経費サマリーを {$del_cnt} 件 削除して実行\\n";
    }
    //////////////////////////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result4 = `/usr/local/pgsql/bin/psql -h 10.1.3.247 TnkSQL < /home/guest/monthly/act_summary 2>&1`;
    unlink($file_name);     // 一時ファイルを削除 txt
    $flag4 = 1;
}

// 販管費サマリーファイル download 準備作業
$file_name = "/home/guest/monthly/AAYECTL6.CSV";
$file_temp = "/home/guest/monthly/AAYECTL6.tmp";
$file_write = "/home/guest/monthly/AAYECTL6.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,300);
        $data = mb_convert_encoding($data, "UTF-8", "SJIS");       // SJISをEUC-JPへ変換
        //  半角カナデータなし $data_KV = mb_convert_kana($data);           // 半角カナを全角カナに変換
        fwrite($fpw,$data);
        $f++;
        if (feof($fp)) {
            $f--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp,"r");
    $fpw = fopen($file_write,"a");
    while (FALSE!==($data = fgetc($fp)) ) {           // １文字 読込
        switch ($data) {
        case '"':
            break;
        case ',':
            fwrite($fpw,"\t");
            break;
        case "\n":
            fwrite($fpw,"\t\\N\t\\N\n");    // last_date last_user 分を \t \N にして書込む
            break;
        case "\r":
            break;                          // CR は読み飛ばす
        default:
            fwrite($fpw,$data);
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    unlink($file_name);     // 一時ファイルを削除 CSV
    unlink($file_temp);     // 一時ファイルを削除 tmp
}
/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // 大量登録用にはコメントアウト
} else {
    exit();
}
// 販管費サマリーファイル  月単位処理 本作業
$file_name = "/home/guest/monthly/AAYECTL6.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $chk_data = fgetcsv($fp, 300, "\t");
    $query = "SELECT * FROM act_sga_summary WHERE act_yymm = {$chk_data[1]} LIMIT 1";
    if (getUniResult($query, $res) > 0) {
        $sql = "DELETE FROM act_sga_summary WHERE act_yymm = {$chk_data[1]}";
        $del_cnt = query_affected_trans($con,$sql);
        $msg .= "販管費サマリーを {$del_cnt} 件 削除して実行\\n";
    }
    //////////////////////////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    //$result4 = `/usr/local/pgsql/bin/psql -h 10.1.3.247 TnkSQL < /home/guest/monthly/act_summary 2>&1`;
    if ( ($fp = fopen($file_name, 'r')) ) {
    $row_han = 0;       // 販管費の数
    $row_sei = 0;       // 製造経費の数
    $rec_ok = 0;        // 成功数カウント
    $rec_ng = 0;        // 失敗数カウント
        while ($data = fgetcsv($fp, 300, "\t")) {
        // while ($data = fgetcsv($fp, 200, "_")) {     // FTP接続用
            ///////// 登録済みのチェック
            $query_chk = sprintf("select * from act_summary where act_ki=%d and act_yymm=%d and act_id=%d", $data[0], $data[1], $data[2]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                ///// 登録なし insert 使用
                switch ($data[17]) {
                case 4:
                    $ser_month = 1;
                    $act_month = $data[3];
                    $act_sum   = $data[3];
                    $act_get   = $data[3];
                    break;
                case 5:
                    $ser_month = 2;
                    $act_month = $data[4];
                    $act_sum   = $data[3] + $data[4];
                    $act_get   = $data[4];
                    break;
                case 6:
                    $ser_month = 3;
                    $act_month = $data[5];
                    $act_sum   = $data[3] + $data[4] + $data[5];
                    $act_get   = $data[5];
                    break;
                case 7:
                    $ser_month = 4;
                    $act_month = $data[6];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6];
                    $act_get   = $data[6];
                    break;
                case 8:
                    $ser_month = 5;
                    $act_month = $data[7];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7];
                    $act_get   = $data[7];
                    break;
                case 9:
                    $ser_month = 6;
                    $act_month = $data[8];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8];
                    $act_get   = $data[8];
                    break;
                case 10:
                    $ser_month = 7;
                    $act_month = $data[9];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9];
                    $act_get   = $data[9];
                    break;
                case 11:
                    $ser_month = 8;
                    $act_month = $data[10];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10];
                    $act_get   = $data[10];
                    break;
                case 12:
                    $ser_month = 9;
                    $act_month = $data[11];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11];
                    $act_get   = $data[11];
                    break;
                case 1:
                    $ser_month = 10;
                    $act_month = $data[12];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11] + $data[12];
                    $act_get   = $data[12];
                    break;
                case 2:
                    $ser_month = 11;
                    $act_month = $data[13];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11] + $data[12] + $data[13];
                    $act_get   = $data[13];
                    break;
                case 3:
                    $ser_month = 12;
                    $act_month = $data[14];
                    $act_sum   = $data[3] + $data[4] + $data[5] + $data[6] + $data[7] + $data[8] + $data[9] + $data[10] + $data[11] + $data[12] + $data[13] + $data[14];
                    $act_get   = $data[14];
                    break;
                default:    // その他
                    $ser_month = 0;
                    $act_month = 0;
                    $act_sum   = 0;
                    $act_get   = 0;
                }
                $row_han++;     // 内販管費の数
                $query_chk = sprintf("select * from act_sga_summary where act_ki=%d and act_yymm=%d and act_id=%d and actcod=%d and aucod=%d", $data[0], $data[1], $data[2], $data[15], $data[16]);
                if (getResultTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    $query = sprintf("insert into act_sga_summary (act_ki, act_ser, act_yymm, act_id, act_monthly, act_sum, act_getu, actcod, aucod)
                            values(%d,%d,%d,%d,%d,%d,%d,%d,%d)", $data[0],$ser_month,$data[1],$data[2],$act_month,$act_sum,$act_get,$data[15],$data[16]);
                    if (($act_sum != 0) || ($act_month != 0)) {
                        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                            $rec_ng++;      // 失敗数カウント
                            break;          // NG のため抜ける
                        } else {
                            $rec_ok++;      // 成功数カウント
                        }
                    }
                } else {
                    $act_month = $res_chk[0][4] + $act_month;
                    $act_sum   = $res_chk[0][5] + $act_sum;
                    $act_get   = $res_chk[0][6] + $act_get;
                    if (($act_sum != 0) || ($act_month != 0)) {
                        $query = sprintf("update act_sga_summary set act_monthly=%d, act_sum=%d, act_getu=%d
                                where act_ki=%d and act_yymm=%d and act_id=%d and actcod=%d and aucod=%d", $act_month, $act_sum, $act_get,$res_chk[0][0],$res_chk[0][2],$res_chk[0][3],$res_chk[0][7],$res_chk[0][8]);
                        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                            $rec_ng++;      // 失敗数カウント
                            break;          // NG のため抜ける
                        } else {
                            $rec_ok++;      // 成功数カウント
                        }
                    }
                }
            } else {
                $row_sei++;         // 販管費ではない（製造経費）
            }
        }
        $flag5 = 1;
    }
    unlink($file_name);     // 一時ファイルを削除 txt
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト

// メッセージを返す
if ($flag1==1) {
    $msg .= "<font color='white'>売上データを追加しました。<br>";
    $msg .= $b . "件<br>";
    $msg .= $result1 . "<br></font>";
} else {
    $msg .= "<font color='yellow'>売上データの追加データがありません。</font><br><br>";
}
if ($flag2==1) {
    $msg .= "<font color='white'>アイテムマスター更新<br>";
    $msg .= "insert $row_in 件<br>";
    $msg .= "update $row_up 件<br>";
    $msg .= "CSV_file $rowcsv 件<br>";
    $msg .= "Original $c 件<br><br></font>";
    // $msg .= $c . "件<br>";
    // $msg .= $result2 . "<br>";
} else {
    $msg .= "<font color='yellow'>アイテムマスターの追加データがありません。</font><br><br>";
}
if ($flag3==1) {
    $msg .= "<font color='white'>製品仕掛サマリーファイルを追加しました。<br>";
    $msg .= $d . "件<br>";
    $msg .= $result3 . "<br></font>";
} else {
    $msg .= "<font color='yellow'>SGKSIKPの追加データがありません。</font><br><br>";
}
if ($flag4==1) {
    $msg .= "<font color='white'>労務費・経費サマリーファイルを追加しました。<br>";
    $msg .= $e . "件<br>";
    $msg .= $result4 . '</font>';
} else {
    $msg .= "<font color='yellow'>AAYLAWL2.txtの追加データがありません。</font><br>";
}
if ($flag5==1) {
    $msg .= "<font color='white'>販管費サマリーファイルを追加しました。<br>";
    $msg .= $row_sei . "件 /" . $f . "件 は製造経費,<br>";
    $msg .= $row_han . "件 /" . $f . "件 は販管費,<br>";
    $msg .= "販管費の内" . $rec_ok . "件 /" . $row_han . "件 追加,<br>";
    $msg .= "販管費の内" . $rec_ng . "件 /" . $row_han . "件 失敗<br></font>";
} else {
    $msg .= "<font color='yellow'>AAYECTL6.txtの追加データがありません。</font><br>";
}
$_SESSION["s_sysmsg"] = $msg;
header('Location: ' . H_WEB_HOST . SYS_MENU);


ob_end_flush();  //Warning: Cannot add header の対策のため追加。

?>

