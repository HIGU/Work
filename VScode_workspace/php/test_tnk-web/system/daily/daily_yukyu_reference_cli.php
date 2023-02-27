#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 有給5日基準日(daily)処理                                                 //
// Copyright(C) 2019-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2019/05/273 新規作成 daily_yukyu_cli.phpを改造                           //
//                      daily_yukyu_reference_cli.php                       //
// 2019/07/25 基準終了日と必要日数を追加                                    //
// 2019/10/02 20期以前の入社者は加味しないように変更                        //
// 2020/07/03 中途社員が基準日計算できないが少ないので手動修正              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "有給5日基準日の更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_yukyu_reference_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 有給の更新 db_connect() error \n");
    fwrite($fpb, "$log_date 有給の更新 db_connect() error \n");
    echo "$log_date 有給の更新 db_connect() error \n\n";
    exit();
}

$ym    =  date("Ym");
$tmp   = $ym - 200003;
$tmp   = $tmp / 100;
$ki    = ceil($tmp);
$nk_ki = $ki + 44;
$yyyy = substr($ym, 0,4);
$mm   = substr($ym, 4,2);

// 年度計算
if ($mm < 4) {              // 1～3月の場合
    $business_year = $yyyy - 1;
} else {
    $business_year = $yyyy;
}
$ki_first_ym     = $business_year . '04';    // 当期期初年月
$ki_first_ymd    = $business_year . '0401';  // 当期期初年月日
$b_ki_first_ymd  = $ki_first_ymd - 10000;    // 前期初年月日
$bb_ki_first_ymd = $b_ki_first_ymd - 10000;  // 前々期初年月日
$ki_end_yy       = $business_year + 1;
$ki_end_ym       = $ki_end_yy . '03';    // 当期期末年月
$ki_end_ymd      = $ki_end_yy . '0331';  // 当期期末年月日
// パート有給発生基準日。 当期入社で10月以降であれば当期の有給は発生しない。
if ($mm < 4) {              // 1～3月の場合
    $s_yy = $yyyy - 1;
} else {
    $s_yy = $yyyy;
}
$six_ym  = $s_yy . '10';    // パート有給発生基準月
$six_ymd = $s_yy . '1001';  // パート有給発生基準日

$query_u = "SELECT p.uid, d.name, p.ki, p.current_day, d.sid, to_char(d.enterdate, 'YYYYMMDD'), d.retire_date, p.work_time_p, p.work_time_s
          FROM paid_holiday_master as p left outer join user_detailes as d on p.uid=d.uid 
          WHERE ki={$nk_ki};
        ";
$res = array();
$rows = getResult2($query_u, $res);
// 上記の条件で、前期までの退職者と日東工器の社員は除外される
//$fp = fopen($file_orign, 'r');
//$fpw = fopen($file_test, 'w');      // debug 用ファイルのオープン
$rec = 0;       // レコード№
$rec_ok = 0;    // 書込み成功レコード数
$rec_ng = 0;    // 書込み失敗レコード数
$ins_ok = 0;    // INSERT用カウンター
for ($r=0; $r<$rows; $r++) {
    $query_chk = sprintf("SELECT uid FROM five_yukyu_master WHERE uid='%s' and business_year=%d", $res[$r][0], $business_year);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー 
    // 登録なしの場合は以下のチェックで登録を行う。
       if ($res[$r][4] <> '80') {              // 顧問を除外
            if ($res[$r][3] == 0) {             // 当期取得有給が0の場合
                if ($res[$r][6] == '') {        // 退職日が存在しない 
                    // 前期(6ヶ月以内)もしくは当期入社のパート
                    if ($res[$r][5] >= $ki_first_ymd) { // 当期入社
                        if ($res[$r][5] <= $six_ymd) {  // 入社日が10月より前(10月以降であれば当期の有給は発生しない)
                            $r_yy = substr($res[$r][5], 0,4);
                            $r_mm = substr($res[$r][5], 4,2);
                            $r_dd = substr($res[$r][5], 6,2);
                            // 厳密な有給取得日を情報システム部に確認 ⇒ AS自動付与ではない
                            // 当期入社で10月より前の場合、予め基準日を計算しておく
                            if ($r_mm < 7) {
                                $reference_ym = $res[$r][5] + 600;
                            } else {
                                $reference_ym = $res[$r][5] - 600 + 10000;
                            }
                            $c_yy = substr($reference_ym, 0,4);
                            $c_mm = substr($reference_ym, 4,2);
                            $c_dd = substr($reference_ym, 6,2);
                            if(checkdate($c_mm, $c_dd, $c_yy)) {
                                // 存在日付OK 何もしない
                            } else {
                                // 日付が存在しないので遡る。月末入社対応。
                                // 1/31 ⇒ 2/31 ⇒ 2/30 ⇒ 2/29 ⇒ 2/28 -3まででOK
                                $t_ymd = $reference_ym - 1;     // 2/30
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // 存在日付OK 基準日置換え 31⇒30日の場合はここでOK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // 日付が存在しないので遡る
                                    $t_ymd = $reference_ym - 1;     // 2/29
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // 存在日付OK 基準日置換え うるう年はここでOK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // 日付が存在しないので遡る
                                        $t_ymd = $reference_ym - 1;     // 2/28
                                        $t_yy = substr($t_ymd, 0,4);
                                        $t_mm = substr($t_ymd, 4,2);
                                        $t_dd = substr($t_ymd, 6,2);
                                        if(checkdate($t_mm, $t_dd, $t_yy)) {
                                            // 存在日付OK 基準日置換え うるう年はここでOK
                                            $reference_ym = $t_ymd;
                                        } else {
                                            // こっちにくることは無いが念のため基準日置換え
                                            $reference_ym = $t_ymd;
                                        }
                                    }
                                }
                            }
                            $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                                VALUES(
                                '{$res[$r][0]}',
                                {$business_year} ,
                                {$reference_ym})";
                            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                                fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                                fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                                // query_affected_trans($con, "rollback");     // transaction rollback
                                $rec_ng++;
                                ////////////////////////////////////////// Debug start
                                for ($f=0; $f<$rows; $f++) {
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
                        // 当期10月以降入社は当期に有給は発生しないので登録無し
                    } elseif ($res[$r][5] >= $b_ki_first_ymd) { // 前期入社
                        // 前期入社なので、その日付から今年度の基準日をここで計算
                        // 前期入社でも10月以降の場合は、今期有給が発生する
                        // 9月以前の場合は、基準日が前期となるが終了日が今期と合算になる為、基準日がある。
                        // 但し、入社日が2018/09以前の場合は年休付与が2019/03までと
                        // なる為、法的に今期と合算する必要はない。(4/1発生のものだけでいい)
                        $r_yy = substr($res[$r][5], 0,4);
                        $r_mm = substr($res[$r][5], 4,2);
                        $r_dd = substr($res[$r][5], 6,2);
                        $r_ym = substr($res[$r][5], 0,6);
                        $r_md = substr($res[$r][5], 4,4);
                        if ($r_mm > 6) {
                            $reference_ym = $res[$r][5] - 600 + 10000;
                        } else {
                            $reference_ym = $res[$r][5] + 600;
                        }
                        $c_yy = substr($reference_ym, 0,4);
                        $c_mm = substr($reference_ym, 4,2);
                        $c_dd = substr($reference_ym, 6,2);
                        if(checkdate($c_mm, $c_dd, $c_yy)) {
                            // 存在日付OK 何もしない
                        } else {
                            // 日付が存在しないので遡る。月末入社対応。
                            // 1/31 ⇒ 2/31 ⇒ 2/30 ⇒ 2/29 ⇒ 2/28 -3まででOK
                            $t_ymd = $reference_ym - 1;     // 2/30
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // 存在日付OK 基準日置換え 31⇒30日の場合はここでOK
                                $reference_ym = $t_ymd;
                            } else {
                                // 日付が存在しないので遡る
                                $t_ymd = $reference_ym - 1;     // 2/29
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // 存在日付OK 基準日置換え うるう年はここでOK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // 日付が存在しないので遡る
                                    $t_ymd = $reference_ym - 1;     // 2/28
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // 存在日付OK 基準日置換え うるう年はここでOK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // こっちにくることは無いが念のため基準日置換え
                                        $reference_ym = $t_ymd;
                                    }
                                }
                            }
                        }
                        $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                            VALUES(
                              '{$res[$r][0]}',
                               {$business_year} ,
                               {$reference_ym})";
                        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                            fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                            fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                            // query_affected_trans($con, "rollback");     // transaction rollback
                            $rec_ng++;
                            ////////////////////////////////////////// Debug start
                            for ($f=0; $f<$rows; $f++) {
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
                    } else {                        // 前々期入社
                        // 入社日が前々期の10/02以降は対象となる
                        // 10/02⇒基準日04/02⇒04/01までとなる為
                        $r_yy = substr($res[$r][5], 0,4);
                        $r_mm = substr($res[$r][5], 4,2);
                        $r_dd = substr($res[$r][5], 6,2);
                        $r_ym = substr($res[$r][5], 0,6);
                        $r_md = substr($res[$r][5], 4,4);
                        if ($r_md >= 1002) {
                            $reference_ym = $res[$r][5] - 600 + 10000;
                        }
                        $c_yy = substr($reference_ym, 0,4);
                        $c_mm = substr($reference_ym, 4,2);
                        $c_dd = substr($reference_ym, 6,2);
                        if(checkdate($c_mm, $c_dd, $c_yy)) {
                            // 存在日付OK 何もしない
                        } else {
                            // 日付が存在しないので遡る。月末入社対応。
                            // 1/31 ⇒ 2/31 ⇒ 2/30 ⇒ 2/29 ⇒ 2/28 -3まででOK
                            $t_ymd = $reference_ym - 1;     // 2/30
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // 存在日付OK 基準日置換え 31⇒30日の場合はここでOK
                                $reference_ym = $t_ymd;
                            } else {
                                // 日付が存在しないので遡る
                                $t_ymd = $reference_ym - 1;     // 2/29
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // 存在日付OK 基準日置換え うるう年はここでOK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // 日付が存在しないので遡る
                                    $t_ymd = $reference_ym - 1;     // 2/28
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // 存在日付OK 基準日置換え うるう年はここでOK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // こっちにくることは無いが念のため基準日置換え
                                        $reference_ym = $t_ymd;
                                    }
                                }
                            }
                        }
                        $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                            VALUES(
                              '{$res[$r][0]}',
                               {$business_year} ,
                               {$reference_ym})";
                        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                            fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                            fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                            // query_affected_trans($con, "rollback");     // transaction rollback
                            $rec_ng++;
                            ////////////////////////////////////////// Debug start
                            for ($f=0; $f<$rows; $f++) {
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
                }
                //有給発生前に退職なので登録不要
            } elseif ($res[$r][3] >= 10) {      // 当期取得有給が10日以上(中途社員を除外)
                // 有給がすでに付与されているので確実に前期6ヶ月以上前の入社となる
                // 前期(6ヶ月以内)もしくは当期入社のパート
                if ($res[$r][5] >= $ki_first_ymd) { // 当期入社
                if ($res[$r][7] != 0 && $res[$r][8] == 0) {
                    if ($res[$r][5] <= $six_ymd) {    // 入社日が10月より前(10月以降であれば当期の有給は発生しない)
                        $r_yy = substr($res[$r][5], 0,4);
                        $r_mm = substr($res[$r][5], 4,2);
                        $r_dd = substr($res[$r][5], 6,2);
                        // 厳密な有給取得日を情報システム部に確認 ⇒ AS自動付与ではない
                        // 当期入社で10月より前の場合、予め基準日を計算しておく
                        if ($r_mm < 7) {
                            $reference_ym = $res[$r][5] + 600;
                        } else {
                            $reference_ym = $res[$r][5] - 600 + 10000;
                        }
                        $c_yy = substr($reference_ym, 0,4);
                        $c_mm = substr($reference_ym, 4,2);
                        $c_dd = substr($reference_ym, 6,2);
                        if(checkdate($c_mm, $c_dd, $c_yy)) {
                            // 存在日付OK 何もしない
                        } else {
                            // 日付が存在しないので遡る。月末入社対応。
                            // 1/31 ⇒ 2/31 ⇒ 2/30 ⇒ 2/29 ⇒ 2/28 -3まででOK
                            $t_ymd = $reference_ym - 1;     // 2/30
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // 存在日付OK 基準日置換え 31⇒30日の場合はここでOK
                                $reference_ym = $t_ymd;
                            } else {
                                // 日付が存在しないので遡る
                                $t_ymd = $reference_ym - 1;     // 2/29
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // 存在日付OK 基準日置換え うるう年はここでOK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // 日付が存在しないので遡る
                                    $t_ymd = $reference_ym - 1;     // 2/28
                                    $t_yy = substr($t_ymd, 0,4);
                                    $t_mm = substr($t_ymd, 4,2);
                                    $t_dd = substr($t_ymd, 6,2);
                                    if(checkdate($t_mm, $t_dd, $t_yy)) {
                                        // 存在日付OK 基準日置換え うるう年はここでOK
                                        $reference_ym = $t_ymd;
                                    } else {
                                        // こっちにくることは無いが念のため基準日置換え
                                        $reference_ym = $t_ymd;
                                    }
                                }
                            }
                        }
                        $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                            VALUES(
                            '{$res[$r][0]}',
                            {$business_year} ,
                            {$reference_ym})";
                        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                            fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                            fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                            // query_affected_trans($con, "rollback");     // transaction rollback
                            $rec_ng++;
                            ////////////////////////////////////////// Debug start
                            for ($f=0; $f<$rows; $f++) {
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
                    // 当期10月以降入社は当期に有給は発生しないので登録無し
                } else {
                    $reference_ym = $res[$r][5];
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                          VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                } elseif ($res[$r][5] >= $b_ki_first_ymd && $res[$r][5] >= 20180999) { // 前期入社
                    // 前期入社なので、その日付から今年度の基準日をここで計算
                    // 前期入社でも10月以降の場合は、今期有給が発生する
                    // 9月以前の場合は、基準日が前期となるが終了日が今期と合算になる為、基準日がある。
                    // 但し、入社日が2018/09以前の場合は年休付与が2019/03までと
                    // なる為、法的に今期と合算する必要はない。(4/1発生のものだけでいい)
                if ($res[$r][7] != 0 && $res[$r][8] == 0) {
                    $r_yy = substr($res[$r][5], 0,4);
                    $r_mm = substr($res[$r][5], 4,2);
                    $r_dd = substr($res[$r][5], 6,2);
                    $r_ym = substr($res[$r][5], 0,6);
                    $r_md = substr($res[$r][5], 4,4);
                    if ($r_mm > 6) {
                        $reference_ym = $res[$r][5] - 600 + 10000;
                    } else {
                        $reference_ym = $res[$r][5] + 600;
                    }
                    $c_yy = substr($reference_ym, 0,4);
                    $c_mm = substr($reference_ym, 4,2);
                    $c_dd = substr($reference_ym, 6,2);
                    if(checkdate($c_mm, $c_dd, $c_yy)) {
                        // 存在日付OK 何もしない
                    } else {
                        // 日付が存在しないので遡る。月末入社対応。
                        // 1/31 ⇒ 2/31 ⇒ 2/30 ⇒ 2/29 ⇒ 2/28 -3まででOK
                        $t_ymd = $reference_ym - 1;     // 2/30
                        $t_yy = substr($t_ymd, 0,4);
                        $t_mm = substr($t_ymd, 4,2);
                        $t_dd = substr($t_ymd, 6,2);
                        if(checkdate($t_mm, $t_dd, $t_yy)) {
                            // 存在日付OK 基準日置換え 31⇒30日の場合はここでOK
                            $reference_ym = $t_ymd;
                        } else {
                            // 日付が存在しないので遡る
                            $t_ymd = $reference_ym - 1;     // 2/29
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // 存在日付OK 基準日置換え うるう年はここでOK
                                $reference_ym = $t_ymd;
                            } else {
                                // 日付が存在しないので遡る
                                $t_ymd = $reference_ym - 1;     // 2/28
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // 存在日付OK 基準日置換え うるう年はここでOK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // こっちにくることは無いが念のため基準日置換え
                                    $reference_ym = $t_ymd;
                                }
                            }
                        }
                    }
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                        VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                } else {        // 社員
                    $reference_ym = $ki_first_ymd;
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                          VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                } elseif ($res[$r][5] >= $bb_ki_first_ymd) {    // 前々期入社
                    // 入社日が前々期の10/02以降は対象となる
                    // 10/02⇒基準日04/02⇒04/01までとなる為
                    // それ以外は今期4/1有給付与となる。
                    $r_yy  = substr($res[$r][5], 0,4);
                    $r_mm  = substr($res[$r][5], 4,2);
                    $r_dd  = substr($res[$r][5], 6,2);
                    $r_ym  = substr($res[$r][5], 0,6);
                    $r_md  = substr($res[$r][5], 4,4);
                    if ($r_md >= 1002) {
                        $reference_ym = $res[$r][5] - 600 + 10000;
                    } else {    // 前々期の10/1以前入社
                        $reference_ym = $ki_first_ymd;
                    }
                    $c_yy = substr($reference_ym, 0,4);
                    $c_mm = substr($reference_ym, 4,2);
                    $c_dd = substr($reference_ym, 6,2);
                    if(checkdate($c_mm, $c_dd, $c_yy)) {
                        // 存在日付OK 何もしない
                    } else {
                        // 日付が存在しないので遡る。月末入社対応。
                        // 1/31 ⇒ 2/31 ⇒ 2/30 ⇒ 2/29 ⇒ 2/28 -3まででOK
                        $t_ymd = $reference_ym - 1;     // 2/30
                        $t_yy = substr($t_ymd, 0,4);
                        $t_mm = substr($t_ymd, 4,2);
                        $t_dd = substr($t_ymd, 6,2);
                        if(checkdate($t_mm, $t_dd, $t_yy)) {
                            // 存在日付OK 基準日置換え 31⇒30日の場合はここでOK
                            $reference_ym = $t_ymd;
                        } else {
                            // 日付が存在しないので遡る
                            $t_ymd = $reference_ym - 1;     // 2/29
                            $t_yy = substr($t_ymd, 0,4);
                            $t_mm = substr($t_ymd, 4,2);
                            $t_dd = substr($t_ymd, 6,2);
                            if(checkdate($t_mm, $t_dd, $t_yy)) {
                                // 存在日付OK 基準日置換え うるう年はここでOK
                                $reference_ym = $t_ymd;
                            } else {
                                // 日付が存在しないので遡る
                                $t_ymd = $reference_ym - 1;     // 2/28
                                $t_yy = substr($t_ymd, 0,4);
                                $t_mm = substr($t_ymd, 4,2);
                                $t_dd = substr($t_ymd, 6,2);
                                if(checkdate($t_mm, $t_dd, $t_yy)) {
                                    // 存在日付OK 基準日置換え うるう年はここでOK
                                    $reference_ym = $t_ymd;
                                } else {
                                    // こっちにくることは無いが念のため基準日置換え
                                    $reference_ym = $t_ymd;
                                }
                            }
                        }
                    }
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                        VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
                } else {    // 前々期の10/01以前入社 当期の4/1が基準日
                    $reference_ym = $ki_first_ymd;
                    $query = "INSERT INTO five_yukyu_master (uid, business_year, reference_ym)
                          VALUES(
                          '{$res[$r][0]}',
                           {$business_year} ,
                           {$reference_ym})";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        fwrite($fpa, "$log_date 社員番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        fwrite($fpb, "$log_date Ａ伝番号:{$data[0]} : 期:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                        // query_affected_trans($con, "rollback");     // transaction rollback
                        $rec_ng++;
                        ////////////////////////////////////////// Debug start
                        for ($f=0; $f<$rows; $f++) {
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
            }
        }
    }
}

//fclose($fp);
//fclose($fpw);       // debug
fwrite($fpa, "$log_date 有給の更新 : $rec_ok/$rec 件登録しました。\n");
fwrite($fpa, "$log_date 有給の更新 : {$ins_ok}/{$rec} 件 追加 \n");
fwrite($fpb, "$log_date 有給の更新 : $rec_ok/$rec 件登録しました。\n");
fwrite($fpb, "$log_date 有給の更新 : {$ins_ok}/{$rec} 件 追加 \n");
echo "$log_date 有給の更新 : $rec_ok/$rec 件登録しました。\n";
echo "$log_date 有給の更新 : {$ins_ok}/{$rec} 件 追加 \n";

// 終了基準日 必要日数計算
$query_u = "SELECT uid, reference_ym
          FROM five_yukyu_master
          WHERE business_year={$business_year};
        ";
$res = array();
$rows = getResult2($query_u, $res);
// 上記の条件で、前期までの退職者と日東工器の社員は除外される
for ($r=0; $r<$rows; $r++) {
    if ($res[$r][1] == $ki_first_ymd) { // 基準日が期首日付の場合 終了日は当期期末、必要日数は5日
        $end_ref_ym = $ki_end_ymd;
        if ($ki_first_ymd >= 20210401) {
            $need_day   = 6;
        } else {
            $need_day   = 5;
        }
    } elseif ($res[$r][1] > $ki_first_ymd) {
    // 基準日が当期の場合 当期発生なので終了日は+1年（存在日付チェック）、必要日数はとりあえず5日
    // 来期になると日付と日数は更新される。
        $need_day    = 5;
        $cal_end_ymd = $res[$r][1] + 10000;                                     // 基準日の１年後
        $e_yy = substr($cal_end_ymd, 0,4);                                      // 計算用年
        $e_mm = substr($cal_end_ymd, 4,2);                                      // 計算用月
        $e_dd = substr($cal_end_ymd, 6,2);                                      // 計算用日
        $end_ref_ym = date('Ymd', mktime(0, 0, 0, $e_mm, $e_dd - 1, $e_yy));    // １日前計算
    } else {    // 基準日が前期の場合は必要日数の計算を行う。終了日は当期期末。
        $end_ref_ym = $ki_end_ymd;
        $cal_mm      = 0;                               // 計算用の月数をリセット
        $b_ki_end_ymd = $ki_end_ymd - 10000;            // 前期末年月日
        $str_ym       = substr($res[$r][1], 0,6);       // 基準年月
        $b_end_ym     = substr($b_ki_end_ymd, 0,6);     // 前期末年月
        $str_mm       = substr($res[$r][1], 4,2);       // 基準月
        if ($str_mm < 3) {  // 1～3月
            $cal_mm = $b_end_ym - $str_ym + 1 + 12;     // 計算用月数
            if ($ki_first_ymd >= 20210401) {
                $need_day = round($cal_mm / 12 * 6, 1);     // 月数÷１２×６で日数計算
            } else {
                $need_day = round($cal_mm / 12 * 5, 1);     // 月数÷１２×５で日数計算
            }
            $need_day = ceil($need_day * 2) / 2;        // 0.5単位で切り上げ
        } else {
            $cal_mm = $b_end_ym - $str_ym - 87 + 12;    // 計算用月数
            if ($ki_first_ymd >= 20210401) {
                $need_day = round($cal_mm /12 * 6, 1);      // 月数÷１２×６で日数計算
            } else {
                $need_day = round($cal_mm /12 * 5, 1);      // 月数÷１２×５で日数計算
            }
            $need_day = ceil($need_day * 2) / 2;        // 0.5単位で切り上げ
        }
    }
    $query = "UPDATE five_yukyu_master SET
                            need_day      = {$need_day},
                            end_ref_ym    = {$end_ref_ym}
                      WHERE uid ='{$res[$r][0]}'  and business_year={$business_year}";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                break;                                  // debug
            } else {
            }
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
