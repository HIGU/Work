#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 有給取得状況計算処理                                                     //
// Copyright(C) 2015-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Cronで３０分おきに自動で有給取得状況を計算しておくことで                 //
// 照会時に処理が重くなるのを防ぐ(通常実行時20分ちょいかかる)               //
// Changed history                                                          //
// 2015/01/29 新規作成 daily_yukyu_cal.php                                  //
// 2015/03/20 cronで実行する為変更                                          //
// 2015/03/24 timeproの時間休計算をASに合わせて変更                         //
// 2015/03/27 期が変わった時の動きを暫定的に再現テスト                      //
// 2015/04/16 勤怠終了年月日がない場合(有給更新のみ終了の場合)に            //
//            timeproのデータをYYYY0401～当日まで取得して計算する様に変更   //
//            例外的に終了年月日がない人もいるが計算の時間を少なくできる    //
//            （0から当日までよりは少ないはず）                             //
// 2015/04/17 16日の変更がうまく行ってなかったので再変更                    //
// 2018/09/25 端数の計算は切上みたいなので修正                              //
//            ASのPGM変更後L55をコメント解除                                //
// 2018/10/17 契約社員の労働時間が3桁なので÷100を追加                      //
//            ASのPGM変更後は2018/09/25で検索してPGM入替                    //
// 2018/10/22 ASのPGM変更完了により、こちらのPGMも変更                      //
// 2018/10/25 契約社員の勤務時間で7時間30分など分数が発生した場合           //
//            ASの登録は730など、時分で入力する為PGM訂正                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');

// 有給残計算
$timeDate = date('Ym');
$today_ym = date('Ymd');
$tmp = $timeDate - 195603;     // 期計算係数195603
$tmp = $tmp / 100;             // 年の部分を取り出す
$ki  = ceil($tmp);             // roundup と同じ    
$query = "
            SELECT
                 before_day     AS 前期繰越日数     --  0
                ,current_day    AS 当期発生日数     --  1
                ,day_holiday    AS 有給取得日数     --  2
                ,half_holiday   AS 半日有給回数     --  3
                ,time_holiday   AS 時間休取得分     --  4
                ,total_holiday  AS 当期合計取得     --  5
                ,end_ymd        AS 勤怠終了年月日   --  6
                ,work_time_p    AS パート労働時間   --  7
                ,work_time_s    AS 社員労働時間     --  8
                ,uid            AS 社員番号         --  9
                ,ki             AS 期               -- 10
                FROM paid_holiday_master
                WHERE ki={$ki};
                ";
$rows=getResult2($query, $yukyu);
for ($r=0; $r<$rows; $r++) {
    // AS400有給データ処理
    $first_yukyu = $yukyu[$r][0] + $yukyu[$r][1];
    if ($yukyu[$r][8] == 9) {       // 9は契約社員の為、固定で
            // $work_time          = 6;
            // $first_time_holiday = 30;
            // ASのプログラム修正後こっち 2018/09/25
            // 2018/10/25 契約社員の勤務時間が30単位等になった場合に対応
            // ASのPGMは3桁で7時間30分は730の入力を行う
            // 分の端数と時間休の限度計算は切り上げにしているが、実際に起きた際にも確認
            $temp_time          = floor($yukyu[$r][7] / 100);
            $temp_min           = $yukyu[$r][7] - ($temp_time * 100);
            $temp_min_nue       = ceil(($temp_min / 60) * 1000) / 1000;
            $work_time          = $temp_time + $temp_min_nue;
            $first_time_holiday = ceil($work_time * 5);
            // $work_time          = $yukyu[$r][7] / 100 * 1;
            // $first_time_holiday = $yukyu[$r][7] / 100 * 5;
    } else {
        if ($yukyu[$r][7] != 0) {          // パートの場合は労働時間×5
            $work_time          = $yukyu[$r][7] * 1;
            $first_time_holiday = $yukyu[$r][7] * 5;
        } else {                            // パート労働時間の登録がない場合は一般社員
            $work_time          = 8;
            $first_time_holiday = 40;
            
            // 以下は暫定版、実際は労働時間が入ってくる
            /* if文の構成を変更 以下は変更前のif文
            if ($yukyu[$r][8] == 9) {       // 9は契約社員の為、固定で
                $work_time          = 6;
                $first_time_holiday = 30;
            } else {
                $work_time          = 8;
                $first_time_holiday = 40;
            }
            */
        }
    }
    $total_holiday     = $yukyu[$r][5] * 1;
    $day_holiday       = $yukyu[$r][2] - ($yukyu[$r][3] * 0.5);
    $half_holiday      = $yukyu[$r][3] * 0.5;
    $time_holiday      = $total_holiday - $day_holiday - $half_holiday;
    $yukyu_zan         = $first_yukyu - $yukyu[$r][5];
    $half_holiday_num  = $yukyu[$r][3] * 1;
    $time_holiday_hour = $yukyu[$r][4] / 60;
    // 期首の時、有給計算時には勤怠終了年月日が0になる為、強制的に前期末に日付を変更
    $kishu_temp        = $yukyu[$r][6] * 1;
    if ($kishu_temp == 0 ) {
        $yukyu[$r][6] = date('Y');
        $yukyu[$r][6] = $yukyu[$r][6] * 1;
        $yukyu[$r][6] = $yukyu[$r][6] * 10000 + 331;
    }
    // timepro有給データ処理
    $timepro_end_ym    = date('Ymd');
    $query = "SELECT count(*) AS 有給取得回数
                     FROM timepro_daily_data
                     WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                     AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                     AND substr(timepro, 173, 2)=11;
             ";
    getResult2($query, $timepro_yukyu);
    $yukyu_zan = $yukyu_zan - $timepro_yukyu[0][0];
    $query = "SELECT count(*) AS 半休取得回数
                     FROM timepro_daily_data
                     WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                     AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                     AND (substr(timepro, 37, 2)=41 OR substr(timepro, 45, 2)=42);
             ";
    getResult2($query, $timepro_yukyu);
    $half_holiday_num = $half_holiday_num + $timepro_yukyu[0][0];
    $yukyu_zan = $yukyu_zan - $timepro_yukyu[0][0] * 0.5;
    $timepro_time = 0;
    $query = "SELECT count(*)    AS １時間休取得回数
                    ,count(*)*1  AS １時間休取得時間
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=62 or substr(timepro, 37, 2)=62 or substr(timepro, 45, 2)=62);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time = $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS ２時間休取得回数
                    ,count(*)*2  AS ２時間休取得時間
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=65 or substr(timepro, 37, 2)=65 or substr(timepro, 45, 2)=65);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS ３時間休取得回数
                    ,count(*)*3  AS ３時間休取得時間
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=66 or substr(timepro, 37, 2)=66 or substr(timepro, 45, 2)=66);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS ４時間休取得回数
                    ,count(*)*4  AS ４時間休取得時間
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=67 or substr(timepro, 37, 2)=67 or substr(timepro, 45, 2)=67);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS ５時間休取得回数
                    ,count(*)*5  AS ５時間休取得時間
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=68 or substr(timepro, 37, 2)=68 or substr(timepro, 45, 2)=68);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS ６時間休取得回数
                    ,count(*)*6  AS ６時間休取得時間
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=69 or substr(timepro, 37, 2)=69 or substr(timepro, 45, 2)=69);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $query = "SELECT count(*)    AS ７時間休取得回数
                    ,count(*)*7  AS ７時間休取得時間
                    FROM timepro_daily_data
                    WHERE substr(timepro, 3, 6)='{$yukyu[$r][9]}'
                    AND substr(timepro, 17, 8) > {$yukyu[$r][6]} AND substr(timepro, 17, 8) <= {$timepro_end_ym} 
                    AND (substr(timepro, 53, 2)=70 or substr(timepro, 37, 2)=70 or substr(timepro, 45, 2)=70);
             ";
    getResult2($query, $timepro_yukyu);
    $timepro_time += $timepro_yukyu[0][1];
    $time_holiday_hour = $time_holiday_hour + $timepro_time;
    // 時間休を取得時間に変換 ASと同様に計算
    if ($timepro_time != 0) {
        $l1hkyu        = floor($timepro_time * 60);             // 時間休取得時間を分に変換
        
        $jikan         = floor($work_time * 60);                // 勤務時間を分に変換
        
        //$wkake         = floor((60 / $jikan) * 1000) / 1000;    // １時間取得した際の日数を計算
        // 端数の計算は切上みたい 2018/09/25
        $wkake         = ceil((60 / $jikan) * 1000) / 1000;     // １時間取得した際の日数を計算
        // 取得した時間休が１日単位に繰り上がる日数を計算
        $wnisu         = floor($l1hkyu / $jikan);
        
        $yukyu_zan     = $yukyu_zan - $wnisu;                   // １日単位に繰り上がった日数を有給残からマイナス
        
        $whiku         = floor($wnisu * $jikan);                // １日単位に繰り上がった分数を計算
        $wnoko         = $l1hkyu - $whiku;                      // 取得した時間から１日単位に繰り上がった分をマイナス
        $whour         = round($wnoko / 60, 3);                 // 残った分数を時間に換算
        $work53        = round($whour * $wkake, 3);             // 残った分数を日数に換算
        
        $yukyu_zan     = $yukyu_zan - $work53;
    }
    //$yukyu_zan     = $yukyu_zan - $timepro_time / $work_time;
    $total_holiday   = $first_yukyu - $yukyu_zan;
    // 登録ロジック
    $query = sprintf("SELECT uid FROM holiday_rest_master WHERE uid='%s' and ki=%d",
                        $yukyu[$r][9], $ki);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 変更
        $query = sprintf("UPDATE holiday_rest_master SET current_day=%f,
                            total_holiday=%f, holiday_rest=%f, half_holiday=%d, time_holiday=%d, time_limit=%d, update_ymd=%d,
                            web_ymd=%d, work_time=%f, last_date=CURRENT_TIMESTAMP",
                          $first_yukyu, $total_holiday, $yukyu_zan, $half_holiday_num, $time_holiday_hour, $first_time_holiday,
                          $yukyu[$r][6], $today_ym, $work_time);
        $query .= sprintf(" WHERE uid='%s' and ki=%d",
                        $yukyu[$r][9], $ki);
        if (query_affected($query) <= 0) {
            echo "{$yukyu[$r][9]},{$ki},{$first_yukyu},{$total_holiday},{$yukyu_zan},{$half_holiday_num}, {$time_holiday_hour}, {$first_time_holiday},{$yukyu[$r][6]}, {$today_ym}, {$work_time}<BR>\n";
        } else {
            echo "{$yukyu[$r][9]},{$ki},{$first_yukyu},{$total_holiday},{$yukyu_zan},{$half_holiday_num}, {$time_holiday_hour}, {$first_time_holiday},{$yukyu[$r][6]}, {$today_ym}, {$work_time}<BR>\n";
        }
    } else {                                    //////// 登録なし INSERT 新規
        $query = sprintf("INSERT INTO holiday_rest_master (uid, ki, current_day, total_holiday, holiday_rest,
                        half_holiday, time_holiday, time_limit, update_ymd, web_ymd, work_time, last_date)
                      VALUES ('%s', %d, %f, %f, %f, %d, %d, %d, %d, %d, %f, CURRENT_TIMESTAMP)",
                        $yukyu[$r][9], $ki, $first_yukyu, $total_holiday, $yukyu_zan, $half_holiday_num, $time_holiday_hour, $first_time_holiday,
                        $yukyu[$r][6], $today_ym, $work_time);
        if (query_affected($query) <= 0) {
            
        } else {
            
        }
    }
}
?>
