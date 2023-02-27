#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// Ａ伝情報(詳細)日報(daily)処理   AS/400 UKWLIB/W#MIADIMDE                 //
//   AS/400 ----> Web Server (PHP) PCIXでFTP転送済の物を更新する            //
// Copyright(C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// \FTPTNK USER(AS400) ASFILE(W#MIADIMDE) LIB(UKWLIB)                       //
//         PCFILE(W#MIADIMDE.TXT) MODE(TXT)                                 //
// Changed history                                                          //
// 2016/03/18 新規作成 daily_aden_details_cli.php aden_daily_cli.phpを改造  //
// 2017/09/21 A伝回答日がNK→業務回答日だったものをTNK→NK回答日へ変更      //
//            過去データに関してはNK→業務回答日に置き換える                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "Ａ伝情報(詳細)の更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_aden_details_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date Ａ伝の更新(詳細) db_connect() error \n");
    fwrite($fpb, "$log_date Ａ伝の更新(詳細) db_connect() error \n");
    echo "$log_date Ａ伝の更新(詳細) db_connect() error \n\n";
    exit();
}
///////// Ａ伝情報ファイルの更新 準備作業
$file_orign  = '/home/guest/daily/W#MIADIMDE.TXT';
$file_backup = '/home/guest/daily/backup/W#MIADIMDE-BAK.TXT';
$file_test   = '/home/guest/daily/debug/debug-MIADIMDE.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
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
        if ($num != 24) {           // フィールド数のチェック
            fwrite($fpa, "$log_date field not 24 record=$rec \n");
            fwrite($fpb, "$log_date field not 24 record=$rec \n");
            continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換 autoはNG(自動ではエンコーディングを認識できない)
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            /////// EUC-JP へエンコーディングすれば半角カナも クライアントがWindows上なら問題なく使える
            // if ($f == 3) {
            //     $data[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
            // }
            // TNK回答日処理
            // 20=spare2 と 11=answer_day を比較 11が空白か0の場合は 20を確認
            // 20が空白でなければ11を置換え
            if ($data[11] == 0) {
                if ($data[20] == 0) {
                    // 両方データがなければそのまま
                } else {
                    if ($data[20] < 20170922 ) {    // NK回答日が20170921以前の場合は置換え（NK誤回答への対応）
                        $data[11] = $data[20];      // 回答日をNK生管回答日へ置換え（業務への回答日 過去データ対応）
                    }
                }
            }
        }
        
        $query_chk = sprintf("SELECT aden_no FROM aden_details_master WHERE aden_no='%s' and eda_no=%d", $data[0], $data[18]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO aden_details_master (aden_no, publish_day, receive_day, parts_no, sale_name, order_q,
                      espoir_deli, delivery, order_price, kouji_no, plan_no, answer_day, user_code, user_name, estimate_no,
                      ropes_no, div, divide_deli, eda_no, spare1, spare2, spare3, spare4, spare5, deli_com)
                      VALUES(
                      '{$data[0]}',
                       {$data[1]} ,
                       {$data[2]} ,
                      '{$data[3]}',
                      '{$data[4]}',
                      '{$data[5]}',
                       {$data[6]} ,
                       {$data[7]} ,
                      '{$data[8]}',
                      '{$data[9]}',
                      '{$data[10]}',
                       {$data[11]} ,
                      '{$data[12]}',
                      '{$data[13]}',
                      '{$data[14]}',
                      '{$data[15]}',
                      '{$data[16]}',
                      '{$data[17]}',
                       {$data[18]} ,
                      '{$data[19]}',
                      '{$data[20]}',
                      '{$data[21]}',
                      '{$data[22]}',
                      '{$data[23]}',
                       0)";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date Ａ伝番号(詳細):{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date Ａ伝番号(詳細):{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query = "UPDATE aden_details_master SET
                            aden_no     ='{$data[0]}',
                            publish_day = {$data[1]} ,
                            receive_day = {$data[2]} ,
                            parts_no    ='{$data[3]}',
                            sale_name   ='{$data[4]}',
                            order_q     ='{$data[5]}',
                            espoir_deli = {$data[6]} ,
                            delivery    = {$data[7]} ,
                            order_price ='{$data[8]}',
                            kouji_no    ='{$data[9]}',
                            plan_no     ='{$data[10]}',
                            answer_day  = {$data[11]} ,
                            user_code   ='{$data[12]}',
                            user_name   ='{$data[13]}',
                            estimate_no ='{$data[14]}',
                            ropes_no    ='{$data[15]}',
                            div         ='{$data[16]}',
                            divide_deli ='{$data[17]}',
                            eda_no      = {$data[18]} ,
                            spare1      ='{$data[19]}',
                            spare2      ='{$data[20]}',
                            spare3      ='{$data[21]}',
                            spare4      ='{$data[22]}',
                            spare5      ='{$data[23]}'
                      where aden_no='{$data[0]}' and eda_no={$data[18]}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date Ａ伝番号(詳細):{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date Ａ伝番号(詳細):{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    // 希望L/Tの計算 当日更新のみ
    $query_chk = sprintf("SELECT receive_day, espoir_deli, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and espoir_deli<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 希望納期が無いので処理しない 
    } else {
        ///// 登録あり update 使用
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][0] <= $res[$r][1]) {   // マイナス計算対応
                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][0], $res[$r][1]);
            } else {
                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][1], $res[$r][0]);
            }
            if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 受注日と希望納期が同じ ＝ 0
                $espoir_lt = 0;
            } else {
                $espoir_lt = $res_e[0][0];
            }
            $query = "UPDATE aden_details_master SET
                        espoir_lt = {$espoir_lt}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    // 回答L/Tの計算 当日更新のみ
    $query_chk = sprintf("SELECT receive_day, delivery, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and delivery<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 回答納期が無いので処理しない 
    } else {
        ///// 登録あり update 使用
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][0] <= $res[$r][1]) {   // マイナス計算対応
                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][0], $res[$r][1]);
            } else {
                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][1], $res[$r][0]);
            }
            if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 受注日と回答納期が同じ ＝ 0
                $ans_lt = 0;
            } else {
                $ans_lt = $res_e[0][0];
            }
            $query = "UPDATE aden_details_master SET
                        ans_lt = {$ans_lt}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    // L/T差の計算 当日更新のみ
    $query_chk = sprintf("SELECT espoir_lt, ans_lt, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and espoir_lt<>0 and ans_lt<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 希望・回答L/Tが無いので処理しない 
    } else {
        ///// 登録あり update 使用
        for ($r=0; $r<$rows; $r++) {
            $lt_diff = $res[$r][1] - $res[$r][0];
            $query = "UPDATE aden_details_master SET
                        lt_diff = {$lt_diff}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    // 実完成日 完成遅れの更新 当日更新のみ
    $query_chk = sprintf("SELECT a.aden_no, a.eda_no, a.delivery, a.plan_no, s.plan, s.cut_plan, s.kansei, a.espoir_deli FROM aden_details_master AS a left outer join assembly_schedule as s on ( a.plan_no=s.plan_no) WHERE to_char(a.last_date,'yyyy-mm-dd')=current_date and (s.cut_plan<>0 OR s.kansei<>0) and a.plan_no<>''");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// 回答納期が無いので処理しない 
    } else {
        ///// 登録あり update 使用
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][4] == $res[$r][5]) {     // すべて打切り
                $query_e = "UPDATE aden_details_master SET
                            spare1 = 'U'
                            where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                query_affected_trans($con, $query_e);
            } elseif (($res[$r][4] - $res[$r][5] - $res[$r][6]) > 0) {   // 納入有り未完了
                if ($res[$r][6] > 0) {  // 完成が0以上のもの（一部打ち切っただけのものを除外）
                    // 実完成日の取得
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // 完成遅れの計算
                        if ($res[$r][2] == 0) {   // 回答納期の入力が無かった場合、希望納期と比較する
                            if ($res[$r][7] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 実完成日と回答納期が同じ ＝ 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'B', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                }
            } elseif ($res[$r][4] == $res[$r][6]) {   // 分納-完納と１発完納を抜き出し
                $query_e    = sprintf("SELECT count(plan_no) FROM assembly_completion_history WHERE plan_no='%s'", $res[$r][3]);
                $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                if ($res_e[0][0] < 2 ) { // 完成経歴が1回以下のため１発完納
                    // 実完成日の取得
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    if ($rows_e == 0) {
                        $finish_day = 0;
                    } else {
                        $finish_day = $res_e[0][0];
                    }
                    if ( $finish_day <> '') {
                        // 完成遅れの計算
                        if ($res[$r][2] == 0) {   // 回答納期の入力が無かった場合、希望納期と比較する
                            if ($res[$r][7] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 実完成日と回答納期が同じ ＝ 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'K', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                } else {    // 完成経歴が2回以上のため分納-完納
                    // 実完成日の取得
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // 完成遅れの計算
                        if ($res[$r][2] == 0) {   // 回答納期の入力が無かった場合、希望納期と比較する
                            if ($res[$r][7] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 実完成日と回答納期が同じ ＝ 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'BK', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                }
            } elseif (($res[$r][4] - $res[$r][5] - $res[$r][6]) == 0) {   // 打切-完納と分納-打切-完納を抜き出す
                $query_e    = sprintf("SELECT count(plan_no) FROM assembly_completion_history WHERE plan_no='%s'", $res[$r][3]);
                $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                if ($res_e[0][0] < 2 ) { // 完成経歴が1回以下のため打切-完納
                    // 実完成日の取得
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // 完成遅れの計算
                        if ($res[$r][2] == 0) {   // 回答納期の入力が無かった場合、希望納期と比較する
                            if ($res[$r][7] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 実完成日と回答納期が同じ ＝ 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'UK', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                } else {    // 完成経歴が2回以上のため分納-打切-完納
                    // 実完成日の取得
                    $query_e    = sprintf("SELECT comp_date FROM assembly_completion_history WHERE plan_no='%s' ORDER BY comp_date DESC limit 1", $res[$r][3]);
                    $rows_e     = getResultWithField3($query_e, $field_e, $res_e);
                    $finish_day = $res_e[0][0];
                    if ( $finish_day <> '') {
                        // 完成遅れの計算
                        if ($res[$r][2] == 0) {   // 回答納期の入力が無かった場合、希望納期と比較する
                            if ($res[$r][7] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][7], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][7]);
                            }
                        } else {
                            if ($res[$r][2] <= $finish_day) {   // マイナス計算対応
                                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][2], $finish_day);
                            } else {
                                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $finish_day, $res[$r][2]);
                            }
                        }
                        if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 実完成日と回答納期が同じ ＝ 0
                            $finish_del = 0;
                        } else {
                            $finish_del = $res_e[0][0];
                        }
                        $query_e = "UPDATE aden_details_master SET
                                    spare1 = 'BUK', finish_day = {$finish_day}, finish_del = {$finish_del}
                                    where aden_no='{$res[$r][0]}' and eda_no={$res[$r][1]}";
                        query_affected_trans($con, $query_e);
                    }
                }
            }
        }
    }
    // A伝回答L/Tの計算 当日更新のみ
    $query_chk = sprintf("SELECT receive_day, answer_day, aden_no, eda_no FROM aden_details_master WHERE to_char(last_date,'yyyy-mm-dd')=current_date and answer_day<>0");
    if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
            ///// A伝回答日が無いので処理しない 
    } else {
        ///// 登録あり update 使用
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r][0] <= $res[$r][1]) {   // マイナス計算対応
                $query_e = sprintf("SELECT COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][0], $res[$r][1]);
            } else {
                $query_e = sprintf("SELECT -COUNT(tdate) FROM company_calendar WHERE to_char(tdate,'yyyymmdd')>%d and to_char(tdate,'yyyymmdd')<=%d and bd_flg='t' GROUP BY bd_flg", $res[$r][1], $res[$r][0]);
            }
            if (($rows_e = getResultWithField3($query_e, $field_e, $res_e)) <= 0) {   // データなし＝ 受注日と希望納期が同じ ＝ 0
                $ans_day_lt = 0;
            } else {
                $ans_day_lt = $res_e[0][0];
            }
            $query = "UPDATE aden_details_master SET
                        ans_day_lt = {$ans_day_lt}
                        where aden_no='{$res[$r][2]}' and eda_no={$res[$r][3]}";
            query_affected_trans($con, $query);
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date Ａ伝の更新(詳細) : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date Ａ伝の更新(詳細) : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date Ａ伝の更新(詳細) : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date Ａ伝の更新(詳細) : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date Ａ伝の更新(詳細) : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date Ａ伝の更新(詳細) : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date Ａ伝の更新(詳細) : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date Ａ伝の更新(詳細) : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date Ａ伝の更新(詳細) : {$upd_ok}/{$rec} 件 変更 \n";
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
    fwrite($fpa, "$log_date : Ａ伝情報(詳細)の更新ファイル {$file_orign} がありません！\n");
    fwrite($fpb, "$log_date : Ａ伝情報(詳細)の更新ファイル {$file_orign} がありません！\n");
    echo "$log_date : Ａ伝情報(詳細)の更新ファイル {$file_orign} がありません！\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
