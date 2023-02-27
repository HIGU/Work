<?php
//////////////////////////////////////////////////////////////////////////////
// 会議帯 [会議室]をウィンドウ表示（会議室の利用状況を確認できる）          //
// Copyright (C) 2021-2021 Ryota.Waki ryota_waki@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2021/05/14 Created   meeting_schedule_room.php                           //
// 2021/06/10 今日へ戻るリンクを追加                                   大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 60);          // 最大実行時間=60秒 WEB CGI版
//ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
//session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');     // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');       // TNK 全共通 MVC Controller Class
require_once ('meeting_schedule_Model.php');        // MVC の Model部
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

//////////// リクエストオブジェクトの取得
$request = new Request();

//////////// リザルトのインスタンス生成
$result = new Result();

//////////// セッション オブジェクトの取得
$session = new Session();

$menu->set_title('会議室予定表');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///// カレントの年月日が設定されているかチェック
if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
    // 初期値(本日)を設定
    $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
}
///// 一覧表示時の期間(1日間,7日間,14,28...)
if ($request->get('listSpan') == '') {
    if ($session->get_local('listSpan') != '') {
        $request->add('listSpan', $session->get_local('listSpan'));
    } else {
        $request->add('listSpan', '0');             // 初期値(本日のみ)
    }
}
$session->add_local('listSpan', $request->get('listSpan')); // セッションデータも変更

//////////// ビジネスモデル部のインスタンス生成
$model = new MeetingSchedule_Model($request);

// 
$year = $request->get('year');
$month = $request->get('month');
$day = $request->get('day');
$uniq = 'meeting';


$url = $menu->out_self() . "?showMenu=List&" . $model->get_htmlGETparm() . "&id={$uniq}";

$today   = date('Ymd');
$today_y = substr($today,0,4);
$today_m = substr($today,4,2);
$today_d = substr($today,6,2);

if (preg_match('/\?/', $url)) {
    $url_para = $url . "&year={$today_y}&month={$today_m}&day={$today_d}";
} else {
    $url_para = $url . "?year={$today_y}&month={$today_m}&day={$today_d}";
}

// 年月日のドロップダウンリスト作成
function SelectOptionDate($start, $end, $def)
{
    for ($i = $start; $i <= $end ; $i++) {
        if ($i == $def) {
            echo "<option value='" . sprintf("%02d", $i) . "' selected>" . $i . "</option>";
        } else {
            echo "<option value='" . sprintf("%02d", $i) . "'>" . $i . "</option>";
        }
    }
}

// 会社の休日情報取得（前後１年分）
function GetHoliday($year)
{
    if( !$year ) return "";
    $s_year = $year++;
    $e_year = $year--;

    $query = "
            SELECT  tdate           AS 日付
            FROM    company_calendar
            WHERE   tdate >= '{$s_year}0101' AND tdate <= '{$e_year}1231' AND bd_flg = 'f'
        ";
    if( getResult2($query, $res) <= 0 ) {
        return "";
    }
    return $res;
}

// どの大会議室か？
function WhereDaiRoom( $room_name )
{
    if( ! strstr($room_name, "大会議室") ) return false;  // 大会議室か？

    $pos = strpos($room_name, '('); // どこの大会議室かを判断する為、'('の文字位置を取得。

    if( ! $pos ) return false;      // '('があるか？

    $where = substr($room_name, $pos+1, 2); // 北 or 中 or 南 をセット

    return $where;
}

// 複数 大会議室 使用？
function IsDaiRoomMulti( $room_name )
{
    if( ! strstr($room_name, "/") ) return false;         // 複数か？
    if( ! strstr($room_name, "大会議室") ) return false;  // 大会議室か？

    return true;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='meeting_schedule.css' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule.js'></script>

<style>
</style>

</head>
<body onLoad='set_focus()'>
<center>

    <form name='ControlForm' action='<?php echo "./meeting_schedule_Main.php?year={$year}&month={$month}&day={$day}&showMenu=Apend&only=yes&{$model->get_htmlGETparm()}&id={$uniq}"; ?>' method='post'>

    <script>setSelectDate(<?php echo $year; ?>,<?php echo $month; ?>,<?php echo $day; ?>);</script>
    <script>var holiday = '<?php echo json_encode(GetHoliday($year)); ?>';setHoliday(holiday);</script>

    <BR>
    <input type="button" value="< 前の日 <" name="befor" onClick="setBeforDate(); checkANDexecute(8);">　

    <select name="ddlist" id="id_year" onChange="setDdlistDate(); checkANDexecute(8);">
        <?php SelectOptionDate(date('Y')-1, date('Y')+1, $year); ?>
    </select>年
    <select name="ddlist" id="id_month" onChange="setDdlistDate(); checkANDexecute(8);">
        <?php SelectOptionDate(1, 12, $month); ?>
    </select>月
    <select name="ddlist" id="id_day" onChange="setDdlistDate(); checkANDexecute(8);">
        <?php SelectOptionDate(1, 31, $day); ?>
    </select>日
    <font id='id_week'></font>
    <script>viewWeek();</script>

    　<input type="button" value="> 次の日 >" name="next" onClick="setNextDate(); checkANDexecute(8);">

    <BR>
    <a href='<?php echo $url_para ?>' style='text-decoration:none;' class='current'><font size='2'><B>今日</B></font></a>
    <BR>
    <input type="button" value=">>> 再表示 <<<" name="review" onClick='checkANDexecute(8);'>
    　<input type="button" value="[×]閉じる" name="close" onClick='window.parent.close();'>
    <BR>

    <?php
    $room_info[0][0] = "　";        // [行]時間[列]部屋 利用状況データ格納領域
    $s_hour =  8; $s_minute = 0;    // 開始
    $e_hour = 21; $e_minute = 0;    // 終了

    // 行データ作成（行数：$r_idx）開始〜終了まで５分刻みのデータ作成
    $r_idx = 1; // 初期値セット（0：は、空）
    for( $h=$s_hour; $h<=$e_hour; $h++ ) {
        if( $h==$e_hour ) $max=$e_minute; else $max=59;
        for( $m=0; $m<=$max; $m=$m+5 ) {    // 5分単位
            $t = sprintf("%02d:%02d", $h, $m);
            $room_info[$r_idx][0] = $t; // 時間セット
            $r_idx++;   // セット後、行数カウントアップ
        }
    }

    // 会議室の情報を取得
    $rows = $model->getActiveRoomList($result); // 0:部屋No. 1:部屋名
    $res  = $result->get_array();
    $cnt  = 0;  // 使用する部屋数
//    $th_width = 102;    // 列の幅
    $th_width = 108;    // 列の幅
//    $th_width = 140;    // 列の幅

    // 列データ作成（列数：$f_idx）使用部屋分のデータ作成
    $f_idx = 1; // 初期値セット（0：は、空）
    for( $r=0; $r<$rows; $r++ ){
        if( !strstr($res[$r][1], "会議室") && !strstr($res[$r][1], "応接室") ) continue;
        if( strstr($res[$r][1], "/") ) continue;   // 大会議室の複数使用をはじく。
        $room = str_replace("事務所棟", "", $res[$r][1]);   // 部屋名より【事務所棟】削除

        $pos = strpos($room, '('); // '('の文字位置取得
        if( $pos ) {
            $room = substr_replace($room, '<BR>(', $pos, 1); // '('の前を改行する。
        }

        $room_info[0][$f_idx] = $room;      // 表示する部屋名(簡略名称)
        $f_idx++;   // セット後、列数カウントアップ
        $room_name[0][$cnt] = $res[$r][0];  // 使用部屋No.
        $room_name[1][$cnt] = $res[$r][1];  // 使用部屋名(正式名称)
        $cnt++; // 使用部屋数
    }

    // 予定情報を取得
    $rows = $model->getViewList($result);
    $res  = $result->get_array();   // 0：シリアル番号 1：件名 2：開始日時 3：終了日時 4：部屋

    // 利用データ作成（予定ある時は情報をセット）部屋と時間をチェックし、結合列数：【開始-終了時間】件名
    for( $r=0; $r<$cnt; $r++ ) {    // 表示する部屋数ループ
        $where = WhereDaiRoom($room_name[1][$r]);           // どこの大会議室か、北 or 中 or 南 を取得
        for( $c=0; $c<$rows; $c++ ) {   // 取得した予定情報分ループ
            $flag = false;
            if( $where && IsDaiRoomMulti($res[$c][4]) ) {   // 予定で、複数の大会議室を使用？
                $flag = strstr($res[$c][4], $where);        // チェック中の大会議も含まれるか？
            }

            if( $room_name[1][$r] == $res[$c][4] || $flag ) {    // 使用部屋名の予定情報か？
                $s_h = substr($res[$c][2], 9, 2); $s_m = substr($res[$c][2], 12, 2);    // 開始時間
                $e_h = substr($res[$c][3], 9, 2); $e_m = substr($res[$c][3], 12, 2);    // 終了時間
                $s_t = (($s_h*60) + $s_m - ($s_hour*60+$s_minute)) / 5;  // 開始時間、分変換後（開始ぶんは減算）5分単位 
                $e_t = (($e_h*60) + $e_m - ($s_hour*60+$s_minute)) / 5;  // 終了時間、分変換後（開始ぶんは減算）5分単位
                $start_end = '【' . $s_h . ':' . $s_m . '-' . $e_h . ':' . $e_m . '】<BR>';

                $rang = $e_t - $s_t;
                $room_info[$s_t+1][$r+1] = $rang . ':' . $start_end . $res[$c][1]; // 結合数：【開始-終了時間】件名
                for( $n=$s_t+1; $n<$e_t; $n++) {
                    $room_info[$n+1][$r+1] = 1; // 予定ありフラグ 1 をセット
                }
            }
        }
    }

    // 利用データ作成（空へ 0 をセット 行の最終列へ予定ありなしフラグをセット）
    for( $r=1; $r<$r_idx; $r++ ) {
        $room_info[$r][$f_idx] = 'off';     // 初期値 予定なし off をセット
        for( $f=1; $f<$f_idx; $f++ ) {
            if( empty($room_info[$r][$f]) ) {
                $room_info[$r][$f] = '0';   // 空きフラグ 0 をセット
            } else {
                $room_info[$r][$f_idx] = 'on';    // 予定がある時間なら on をセット
            }
        }
    }
    ?>
    <BR>    <!-- 会議室利用状況テーブルを表示 -->
    <table class='list' style='height:78%; width:100%; bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
        </caption>
        <tr><td> <!-- ダミー -->

        <!-- 見出し行をスクロールバー付きのテーブルで表示(位置を揃える為) -->
        <div style='width:100%; overflow-y:scroll;'>
            <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            echo "<th style='font-size:0pt; width:48;' class='winbox' nowrap>　</th>";
            for( $f=1; $f<$f_idx; $f++ ) {
                echo "<th style='width:{$th_width};' class='winbox' nowrap>{$room_info[0][$f]}</th>";
            }
            ?>
            </table>
        </div>

        <!-- 利用状況をスクロールバー付きのテーブルで表示 -->
        <div style='height:94%; width:100%; overflow-y:scroll;'>
            <table class='winbox_field' bgcolor='#e6e6e6' align='center' border='1' cellspacing='0' cellpadding='3'>
                <input type='hidden' name='room_no' id='id_room_no' value=''>
                <input type='hidden' name='str_hour' id='id_str_hour' value=''>
                <input type='hidden' name='str_minute' id='id_str_minute' value=''>
                <input type='hidden' name='end_hour' id='id_end_hour' value=''>
                <input type='hidden' name='end_minute' id='id_end_minute' value=''>
            <?php
            $time_flag = $time_flag2 = false;
            for( $r=1; $r<$r_idx-1; $r++ ) {
                $hu = substr($room_info[$r][0], 0, 2);
                $mi = substr($room_info[$r][0], 3, 2);

                $view_style = '';   // 行の表示スタイル 背景：グレー、文字：黒
                $row_time = sprintf("%04d%02d%02d%02d%02d00", $request->get('year'), $request->get('month'), $request->get('day'), $hu, $mi);
                if( date('YmdHis') > $row_time+500 ) { // 現時刻より前 背景：グレー、文字：グレー
                    $view_style .= 'background-color:#e6e6e6; color:DarkGray;';
                } else if(! $time_flag && date('d') == $request->get('day') ){    // 現時刻のみ 背景：黄色、文字：青
                    $view_style .= 'background-color:yellow; color:blue;';
                    $time_flag = true;  // 1行のみの為、すぐフラグを切り替える。
                }
                echo "<tr class='winbox' style='{$view_style}'>";

                // 時間部分の表示
                if( $mi == "00" || $mi == "30" ) {  // 30分単位で表示領域を結合する
                    $r_s = 6;       // 30分÷5分＝6分 6行分結合
                    $view_style = '';   // 行の表示スタイル 背景：グレー、文字：黒
                    if( $mi == "00") {
                        $view_style = 'border-bottom-style:none; ';
                    } else {
                        $view_style = 'border-top-style:dashed; border-bottom-style:none; ';
                    }

                    if( date('YmdHis') < $row_time+3000 ) {
                        if(! $time_flag2 && date('d') == $request->get('day')){    // 現時刻のみ 背景：黄色、文字：青
                            $view_style .= 'background-color:yellow; color:blue;';
                            $time_flag2 = true;  // 1行のみの為、すぐフラグを切り替える。
                        }
                    }
                    echo "<td rowspan='{$r_s}' style='{$view_style}'>{$room_info[$r][0]}</td>";
                }

                // 各部屋の情報を表示
                for( $f=1; $f<$f_idx; $f++ ) {
                    if( $room_info[$r][$f] == '0' ) {           // 空いている場合
                        $view_style = 'font-size: 5pt; line-height: 0; ';
                        if( $mi == "00" ) {         // 00分〜05分 上：表示、下：非表示
                            $view_style .= 'border-bottom-style:none;';
                        } else if( $mi == "30") {   // 30分〜35分 上：破線、下：非表示
                            $view_style .= 'border-top-style:dashed; border-bottom-style:none;';
                        } else {                    // それ以外のフィールド 上：非表示、下：非表示
                            $view_style .= 'border-top-style:none; border-bottom-style:none;';
                        }

                        // 現在の時刻より、空き状況をチェックする。（00〜30 or 30〜60 の30分ぶん）
                        if( $mi < 30 ) {    // 00〜25
                            $s_hu = $hu; $s_max = "00"; $e_hu = $hu; $e_max = "25";
                        } else {            // 30〜55
                            $s_hu = $hu; $s_max = "30"; $e_hu = $hu; $e_max = "55";
                        }
                        
                        // 現在の時刻より、前（〜00 or 〜30）に使用されていないかチェック。
                        for( $w=$r, $w_mi=$mi; $w_mi>=$s_max; $w--, $w_mi=$w_mi-5) {
                            if( $room_info[$w][$f] != '0' ) break;
                            $s_mi = $w_mi;      // 開始できる分をセット
                        }
                        
                        // 現在の時刻より、後（〜30 or 〜60）に使用されていないかチェック。
                        for( $w=$r, $w_mi=$mi; $w_mi<=$e_max; $w++, $w_mi=$w_mi+5) {
                            if( $room_info[$w][$f] != '0' ) break;
                            $e_mi = $w_mi+5;    // 終了できる分をセット
                        }
                        
                        if( $w_mi == 60 ) { // 60分まで使用されているので、次の時間へ変更、分は 00 をセット
                            $e_hu = $hu+1;
                            $e_mi = "00";
                        }

                        echo "<td style='{$view_style} width:{$th_width};' onClick='setApendData({$s_hu},{$s_mi},{$e_hu},{$e_mi},{$room_name[0][$f-1]}); submit();'>　</td>";
                    } else if( $room_info[$r][$f] != '1' ) {    // 予定あり
                        $pos = strpos($room_info[$r][$f], ':'); // 結合数までの文字数取得
                        $num = preg_replace('/[^0-9]/', '', substr($room_info[$r][$f], 0, $pos));   // 結合数取得
                        $title = substr($room_info[$r][$f], $pos+1);    // 【開始-終了時間】件名取得
                        if( $room_info[$r][$f_idx] == 'on' ) $view_style = 'background-color:PaleTurquoise; color:White; ';

                        $title_only = substr($room_info[$r][$f], $pos+20);  // 件名のみ取得

                        $where = WhereDaiRoom($room_name[1][$f-1]);         // どこの大会議室か、北 or 中 or 南 を取得
                        for( $c=0; $c<$rows; $c++ ) {   // 取得した予定情報分ループ
                            $flag = false;
                            if( $where && IsDaiRoomMulti($res[$c][4]) ) {   // 予定で、複数の大会議室を使用？
                                $flag = strstr($res[$c][4], $where);        // チェック中の大会議も含まれるか？
                            }

                            if( $room_name[1][$f-1] == $res[$c][4] || $flag ) {    // 使用部屋名の予定情報か？
                                if( $title_only == $res[$c][1] && $hu == substr($res[$c][2], 9, 2) && $mi == substr($res[$c][2], 12, 2) ) {
                                    break;
                                }
                            }
                        }

                        if( $c != $rows ) {
                            $col = 1;   // 使用部屋数の初期値セット
                            if( $flag ) {
                                if( $f == 1 || $f == 2 ) { // 大会議室（北 or 中）の時、大会議室（中 or 南）と同じ予定か？
                                    if( $room_info[$r][$f] == $room_info[$r][$f+1] ) {
                                        $room_info[$r][$f+1] = 1;
                                        $col=2;
                                    }
                                }
                                if( $f == 1 && $col == 2) { // 大会議室（北）の時、大会議室（南）と同じ予定か？
                                    if( $room_info[$r][$f] == $room_info[$r][$f+2] ) {
                                        $room_info[$r][$f+2] = 1;
                                        $col=3;
                                    }
                                }
                            }

                            $a_view_style='background-color:PaleTurquoise; ';
                            $e_date = sprintf("%04s%02s%02s", $year, $month, $day);
                            $e_time = substr($title, 8, 2) . substr($title, 11, 2);
                            if( date('YmdHi') >= ($e_date . $e_time)) { // 過去の予定
                                $view_style = 'background-color:Silver; color:White; ';
                                $a_view_style = 'background-color:Silver; color:White; ';
                            }
                            $td_width = $th_width * $col;   // 使用部屋分の件名表示領域セット（幅）
                            echo "<td class='winbox' rowspan='{$num}' colspan='{$col}' style='{$view_style} width:{$td_width};'>
                                    <a href='./meeting_schedule_Main.php?serial_no={$res[$c][0]}&year={$year}&month={$month}&day={$day}&showMenu=Edit&only=yes&{$model->get_htmlGETparm()}&id={$uniq}'
                                        style='{$a_view_style}text-decoration:none;'>{$title}
                                    </a>
                                </td>";
                        }
                    }
                }
                echo "</tr>";
            }

            // 最終行へ、見出しと同じ幅をセット（表示を崩さないため）
            echo "<th style='font-size:0pt; width:48;' class='winbox' nowrap>　</th>";
            for( $f=1; $f<$f_idx; $f++ ) {
                echo "<th style='font-size:0pt; width:{$th_width};' class='winbox' nowrap>　</th>";
            }
            ?>
            </table>
        </div>
        </td></tr> <!-- ダミー -->
    </table>

    </form>
</center>
</body>

<?php echo $menu->out_alert_java()?>
</html>
