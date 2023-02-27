<?php
//////////////////////////////////////////////////////////////////////////////
// 損益計算書 セグメント別のデータ取込み ロジック部 include(require) file   //
// 月次 比較棚卸表のデータ取込み ロジック部 include file                    //
// Copyright(C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp            //
// Changed history                                                          //
// 2007/10/10 Created   pl_segment_get.php                                  //
//////////////////////////////////////////////////////////////////////////////
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

//////////////// 認証チェック
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION["Auth"] <= 2) {
    $_SESSION['s_sysmsg'] = "認証されていないか認証期限が切れました。ログインからお願いします。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

/////// pl_segmentYYYYMM.csv ファイルからセグメント別損益計算書データの取込み
// $file_name = "/home/www/html/monthly/pl_segment" . $yyyymm . ".csv";    // 読込ファイルSJIS
$file_name = "/home/guest/monthly/pl_segment" . $yyyymm . ".csv";    // 読込ファイルSJIS
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin トランザクション開始
        if ( !($con = db_connect()) ) {
            $_SESSION['s_sysmsg'] = "データベースに接続できません";
            return FALSE;
        } else {
            query_affected_trans($con, 'BEGIN');
            while ($data = fgetcsv($fp, 100, "\t")) {
                if ( ($num=count($data)) != 2) {        // CSV File の field 数チェック
                    $_SESSION['s_sysmsg'] = "CSVファイルのfield数が2個でない！:$num";
                    return FALSE;
                }
                $data[0] = mb_convert_encoding($data[0], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
                $data[1] = mb_convert_encoding($data[1], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
                // 暫定処置
                $data[0] = str_replace('J', 'カ',   $data[0]);
                $data[0] = str_replace('j', 'リニ', $data[0]);
                $rowcsv++;
                ///////// 登録済みのチェック
                $query_chk = "SELECT note FROM act_pl_history WHERE pl_bs_ym={$yyyymm} AND note='{$data[0]}'";
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = "INSERT INTO act_pl_history (pl_bs_ym, note, kin)
                            VALUES({$yyyymm}, '{$data[0]}', {$data[1]})
                    ";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        query_affected_trans($con, 'ROLLBACK');         // transaction rollback
                        $_SESSION['s_sysmsg'] = "セグメント別 損益計算書 INSERT ERROR rec No.={$rowcsv}";
                        return FALSE;
                    } else {
                        $row_in++;      // insert 成功
                    }
                } else {
                    ///// 登録あり update 使用
                    $query = "
                        UPDATE act_pl_history SET pl_bs_ym={$yyyymm}, note='{$data[0]}', kin={$data[1]}
                            WHERE pl_bs_ym={$yyyymm} and note='{$data[0]}'
                    ";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        query_affected_trans($con, 'ROLLBACK');         // transaction rollback
                        $_SESSION['s_sysmsg'] = "セグメント別 損益計算書 UPDATE ERROR rec No.={$rowcsv}";
                        return FALSE;
                    } else {
                        $row_up++;      // update 成功
                    }
                }
            }
        }
        query_affected_trans($con, 'COMMIT');       // 書込み完了
    } else {
        $_SESSION['s_sysmsg'] = "指定年月:{$yyyymm} のファイルがオープン出来ません";
        return FALSE;
    }
    fclose($fp);
    unlink($file_name);     // 一時ファイルを削除 txt
} else {
    $_SESSION['s_sysmsg'] = "指定年月:{$yyyymm} のファイルがありません";
    return FALSE;
}
$_SESSION['s_sysmsg'] = "<font color='yellow'>CSV file = $rowcsv 件<br>\\n\\nInsert file = $row_in 件<br>\\n\\nUpdate file = $row_up 件<br></font>\\n\\n";
return TRUE;    // 大文字小文字に依存しないが JavaScriptに合わせる。

?>

