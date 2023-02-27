<?php
//////////////////////////////////////////////////////////////////////////
// miitem → miitem2 へデータコンバート 全角カナを半角カナへ            //
// 2003/12/22 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                             //
// 2003/12/22 新規作成 miitem_to_miitem2.php                            //
//              table設計も一部変更 regdateを追加 without time zoen削除 //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
session_start();                    // ini_set()の次に指定すること Script 最上行
// ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮

require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');
// access_log();                       // Script Name は自動取得



// アイテムマスター 週単位処理 準備作業
$file_name = "/home/www/html/weekly/Q#MIITEM.CSV";
$file_temp = "/home/www/html/weekly/Q#MIITEM.tmp";
$file_write = "/home/www/html/weekly/Q#MIITEM.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'ka', 'EUC-JP');     // 全角カナを半角カナに変換
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
        case "\r":
            fwrite($fpw,"\t\\N\t\\N\r");      // last_date last_user 分を \t \N にして書込む
            $str_flg = 0;   // CR を検出したら文字列フラグをリセット(不当な"の対策)
            break;
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
$file_name = "/home/www/html/weekly/Q#MIITEM.txt";
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
                // 現在は必要ない $data[1] = trim($data[1]);          // 部品名の前後のスペースを削除
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
$file_name = "/home/www/html/monthly/Q#SGKSIKP.CSV";
$file_temp = "/home/www/html/monthly/Q#SGKSIKP.tmp";
$file_write = "/home/www/html/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJISをEUC-JPへ変換
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
        case "\r":
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
    unlink($file_temp);     // 一時ファイルを削除 tmp
}

// 製品仕掛(投入実際と完成標準)サマリーファイル  月単位処理
$file_name = "/home/www/html/monthly/Q#SGKSIKP.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    //////////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result3 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/monthly/sgksikp 2>&1`;
    unlink($file_name);     // 一時ファイルを削除 txt
    $flag3 = 1;
}



// 労務費・経費サマリーファイル download 準備作業
$file_name = "/home/www/html/monthly/AAYLAWL2.CSV";
$file_temp = "/home/www/html/monthly/AAYLAWL2.tmp";
$file_write = "/home/www/html/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name,"r");
    $fpw = fopen($file_temp,"a");
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, "EUC-JP", "SJIS");       // SJISをEUC-JPへ変換
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
        case "\r":
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
    unlink($file_temp);     // 一時ファイルを削除 tmp
}

// 労務費・経費サマリーファイル  月単位処理 本作業
$file_name = "/home/www/html/monthly/aaylawl2.txt";
if (file_exists($file_name)) {            // ファイルの存在チェック
    //////////////////////////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result4 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/monthly/act_summary 2>&1`;
    unlink($file_name);     // 一時ファイルを削除 txt
    $flag4 = 1;
}



// メッセージを返す
if ($flag1==1) {
    $msg .= "売上データを追加しました。<br>";
    $msg .= $b . "件<br>";
    $msg .= $result1 . "<br>";
} else {
    $msg .= "売上データの追加データがありません。<br><br>";
}
if ($flag2==1) {
    $msg .= "アイテムマスター更新<br>";
    $msg .= "insert $row_in 件<br>";
    $msg .= "update $row_up 件<br>";
    $msg .= "CSV_file $rowcsv 件<br>";
    $msg .= "Original $c 件<br><br>";
    // $msg .= $c . "件<br>";
    // $msg .= $result2 . "<br>";
} else {
    $msg .= "アイテムマスターの追加データがありません。<br><br>";
}
if ($flag3==1) {
    $msg .= "製品仕掛サマリーファイルを追加しました。<br>";
    $msg .= $d . "件<br>";
    $msg .= $result3 . "<br>";
} else {
    $msg .= "SGKSIKPの追加データがありません。<br><br>";
}
if ($flag4==1) {
    $msg .= "労務費・経費サマリーファイルを追加しました。<br>";
    $msg .= $e . "件<br>";
    $msg .= $result4;
} else {
    $msg .= "AAYLAWL2.txtの追加データがありません。<br>";
}
$_SESSION["s_sysmsg"] = $msg;
header("Location: http:" . WEB_HOST . "system/system_menu.php");


ob_end_flush();  //Warning: Cannot add header の対策のため追加。

?>

