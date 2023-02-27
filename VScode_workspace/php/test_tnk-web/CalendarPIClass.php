<?php
//////////////////////////////////////////////////////////////////////////////
// カレンダーの基本 クラス http://aki.adam.ne.jp/php/calendar/download.php  //
// 上記クラスのソースを改変 Copyrightの記述が無いため以下で表示していない   //
// Copyright (C) 2005-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/31 Created   CalendarClass.php                                   //
//  Ver1.00   $this->holiday[$tday] のワーニング抑制のため @を付加          //
// 2005/11/10 リンク設定時に<a hrefを日付で設定しているためクリック面積が少 //
//  Ver1.01   ないので<td>を含めて設定。その他</td>等が抜けているのを修正   //
//            1月年始休暇, 12月年末休暇, 8月夏期休暇 をset_holiday()に追加  //
//            リンク時の status に祝日名と休業日名を追加                    //
// 2005/11/12 <td> にonMouseover=,onMouseout= を追加して<a href>の代用へ変更//
//  Ver1.02   over=this.style.color='white' onMouseout=this.style.color=''  //
// 2005/11/14 NN 7.1で<a href>の入れ子に<td>を使用するとクリックが効かない  //
//  Ver1.03   ため <a href>を全面的に廃止し<td>のみでイベント使用へ変更した //
// 2008/03/21 振替休日の当日が祝日のチェック追加(３連休まで)                //
//  Ver1.04   CalendarTnkClass.php より引用                            大谷 //
// 2010/08/23 2011年1月5日が自動的に休業になってしまう為調整           大谷 //
// 2014/01/22 2014年12月23日と26日が振替となるのでPGM内で調整          大谷 //
// 2015/06/09 2015年12月23日と25日が振替となるのでPGM内で調整          大谷 //
// 2018/12/26 20期の新天皇即位日と天皇誕生日の変更を追加               大谷 //
// 2019/11/14 21期の休みに対応(スポーツの日・海の日・体育の日等)       大谷 //
// 2021/10/28 祝祭日の名前を正式名称に（スポーツの日、こどもの日等）   大谷 //
//////////////////////////////////////////////////////////////////////////////
require_once ('tnk_func.php');              // 栃木日東工器の休業日を反映させる

if (class_exists('Calendar')) {
    return;
}
define('Calendar_VERSION', '1.03');

/****************************************************************************
*                       base class 基本クラスの定義                         *
****************************************************************************/
///// namespace Common {} は現在使用しない 使用例：Common::ComTableMnt → $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class Calendar
{
    ///// Private properties
    private $wfrom;
    private $beforeandafterday;
    
    private $link = array();
    private $style = array();
    
    private $kind;
    private $bgcolor;
    private $week;
    private $holiday;
    private $holiday_name;
    
    /**
     * コンストラクタ
     *
     * @param int $arg1
     * @param int $arg2
     * @return void
     */
    public function __construct($arg1 = 0, $arg2 = 0)
    {
        // 開始曜日（0-日曜, 6-土曜）
        $this->wfrom = $arg1;
        
        // 当月以外の日付を表示するかどうか（0-表示しない 1-表示する）
        $this->beforeandafterday = $arg2;
        
        // --- 以下、表示設定 ---
        // スタイルの設定
        $this->style['table'] = " class='calendar'";
        $this->style['th'] = " style='background-color:#d6d3ce;'";
        $this->style['tr'] = '';
        $this->style['td'] = '';
        $this->style['tf'] = " class='tf'";
        
        // 曜日に対する背景色の設定（0-平日, 1-土, 2-日祝日, 3-当月以外の平日, 4-当日）
        $this->kind = array(2, 0, 0, 0, 0, 0, 1);
        $this->bgcolor = array('#eeeeee', '#ccffff', '#ffcccc', '#ffffff', '#ffffcc', 'yellow');
        
        // 曜日の名前
        $this->week = array('日', '月', '火', '水', '木', '金', '土');
        
    }
    
    /**
     * 設定された内容でカレンダーを表示します
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function show_calendar($year, $month, $day = 0)
    {
        // 休日の算出
        if (!isset($this->holiday)) $this->set_holiday($year, $month);
        // その月の開始とする数値を取得
        $from = 1;
        while (date('w', mktime(0, 0, 0, $month, $from, $year)) != $this->wfrom) {
            $from--;
        }
        // 前月と次月の年月を取得
        list($ny, $nm, $nj) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month+1, 1, $year)));
        list($by, $bm, $bj) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month-1, 1, $year)));
        // 当日取得
        $arr = getdate();
        // 表示開始
        echo "<table {$this->style['table']} summary='カレンダー'>\n";
        echo "<tr>\n";
        if ($year == $arr['year'] && $month == $arr['mon']) {
            echo "<th style='background-color:{$this->bgcolor[4]};' colspan='14'>\n";
            echo $year . '年' . $month . "月\n";
            echo "&nbsp;今日{$arr['mday']}日\n";
        } else {
            echo "<th{$this->style['th']} colspan='14'>\n";
            echo $year . '年' . $month . "月\n";
        }
        echo "</th>\n";
        echo "</tr>\n";
        // 曜日表示
        echo '<tr' . $this->style['tr'] . " style='text-align:center;'>\n";
        for ($i=0; $i<7; $i++) {
            $wk = ($this->wfrom + $i) % 7;
            echo '<td' . $this->style['td'] . " bgcolor='" . $this->bgcolor[$this->kind[$wk]] . "' colspan='2'>" . $this->week[$wk] . "</td>\n";
        }
        echo "</tr>\n";
        // $tdayがその月の日数を超えるまでループ
        $tday = $from;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        $wnum = 0;  // 週番号
        while ($tday <= $mday) {
            // 日付表示部
            echo '<tr' . $this->style['tr'] . ">\n";
            for ($i=0; $i<7; $i++) {
                $fstyle = '';
                $wk = ($this->wfrom + $i) % 7;
                $bgcolor = $this->bgcolor[$this->kind[$wk]];
                /*
                if ($year == 2020 && $month == 12 && $tday ==26) {
                    $bgcolor = '#eeeeee';
                }
                */
                // 当月判定
                if ($tday >= 1 && $tday <= $mday) {
                    if ($arr['year'] == $year && $arr['mon'] == $month && $arr['mday'] == $tday) {
                        // 当日
                        $bgcolor = $this->bgcolor[4];
                    } else if (@$this->holiday[$tday] == 1) {// holidayがセットされていない場合がほとんどの為、@で制御
                        // 祝日
                        $bgcolor = $this->bgcolor[2];
                    }
                    // 指定日
                    if ($day == $tday) {
                        $fstyle = " style='font-weight:bold; color:red;'";  // onMouseoutした場合は黒色になるので注意
                        $bgcolor = $this->bgcolor[5];   // 上記はリンク時に反映されないため、これを追加
                    }
                } else {
                    // 当月以外の平日
                    if ($wk > 0 && $wk < 6) $bgcolor = $this->bgcolor[3];
                }
                list($lyear, $lmonth, $lday) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month, $tday, $year)));
                // データ部分表示
                if (($tday >= 1 && $tday <= $mday) || $this->beforeandafterday) {
                    echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, " colspan='2'>\n";
                    echo "<div align='right' vlign='top'><B>{$lday}</B></div>\n";
                    echo "</td>\n";
                } else {
                    echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, " colspan='2'>\n";
                    echo "    &nbsp;\n";
                    echo "</td>\n";
                }
                $tday++;
            }
            $tday=$tday - 7;
            // データ表示部
            echo "</tr>\n"; 
            echo '<tr' . $this->style['tr'] . ">\n";
            for ($i=0; $i<7; $i++) {
                $fstyle = '';
                $wk = ($this->wfrom + $i) % 7;
                $bgcolor = $this->bgcolor[$this->kind[$wk]];
                // 当月判定
                if ($tday >= 1 && $tday <= $mday) {
                    if ($arr['year'] == $year && $arr['mon'] == $month && $arr['mday'] == $tday) {
                        // 当日
                        $bgcolor = $this->bgcolor[4];
                    } else if (@$this->holiday[$tday] == 1) {// holidayがセットされていない場合がほとんどの為、@で制御
                        // 祝日
                        $bgcolor = $this->bgcolor[2];
                    }
                    // 指定日
                    if ($day == $tday) {
                        $fstyle = " style='font-weight:bold; color:red;'";  // onMouseoutした場合は黒色になるので注意
                        $bgcolor = $this->bgcolor[5];   // 上記はリンク時に反映されないため、これを追加
                    }
                } else {
                    // 当月以外の平日
                    if ($wk > 0 && $wk < 6) $bgcolor = $this->bgcolor[3];
                }
                $ymd_month = '';
                $ymd_day   = '';
                if ($month<10) {
                    $ymd_month = '0' . $month;
                } else {
                    $ymd_month = $month;
                }
                if ($tday<10) {
                    $ymd_day = '0' . $tday;
                } else {
                    $ymd_day = $tday;
                }
                $ymd_date= $year . $ymd_month . $ymd_day;
                $query = "select subject                        as 件名     -- 0
                                , to_char(str_time, 'HH24:MI')  as 開始時間 -- 1
                                , to_char(end_time, 'HH24:MI')  as 終了時間 -- 2
                            from
                                meeting_schedule_header
                            where pi_flg=1 and  to_char(str_time, 'YYYYMMDD')={$ymd_date}
                            ORDER BY str_time ASC";
                $res   = array();
                $field = array();
                if ( ($rows = getResultWithField2($query, $field, $res)) > 0) {
                    $num = count($field);       // フィールド数取得
                } else {
                    $num = 0;   
                }
                list($lyear, $lmonth, $lday) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month, $tday, $year)));
                // データ部分表示
                if (($tday >= 1 && $tday <= $mday) || $this->beforeandafterday) {
                    if ($rows>0) {
                        echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                        //echo "<div align='left' vlign='top'> <B>{$lday}</B></div><BR>\n";
                        for ($t=0; $t<$rows; $t++) {
                            echo "    <nowrap>{$res[$t][1]}～{$res[$t][2]}<BR>\n";
                        }
                        echo "</td>\n";
                        echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                        echo "<div align='left' vlign='top'> <B>　</B></div><BR>\n";
                        for ($t=0; $t<$rows; $t++) {
                            echo "    <nowrap>{$res[$t][0]}<BR>\n";
                        }
                        echo "</td>\n";
                    } else {
                        echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                        //echo "<div align='left' vlign='top'><B>{$lday}</B></div><BR>\n";
                        echo "</td>\n";
                        echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                        echo "    &nbsp;\n";
                        echo "</td>\n";
                    }
                    
                } else {
                    echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                    echo "    &nbsp;\n";
                    echo "</td>\n";
                    echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                    echo "    &nbsp;\n";
                    echo "</td>\n";
                }
                $tday++;
            }
            echo "</tr>\n"; 
            $wnum++;
        }
        switch ($wnum) {
        case 4;
            echo "<tr>\n";
            echo "<td {$this->style['tf']} colspan='14'>&nbsp;</td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td {$this->style['tf']} colspan='14'>&nbsp;</td>\n";
            echo "</tr>\n";
            break;
        case 5;
            echo "<tr>\n";
            echo "<td {$this->style['tf']} colspan='14'>&nbsp;</td>\n";
            echo "</tr>\n";
            break;
        }
        /*****
        echo "<tr>\n";
        echo '<td' . $this->style['tf'] . " colspan='7'>\n";
        if ($year == $arr['year'] && $month == $arr['mon']) {
            echo '本日：' . $arr['year'] . '年' . $arr['mon'] . '月' . $arr['mday'] . "日\n";
        } else {
            echo '&nbsp;';
        }
        echo "</td>\n";
        echo "</tr>\n";
        *****/
        echo "</table>\n";
    }
    
    /**
     * 指定された日に対してリンクを設定します。
     *
     * @param int $day
     * @param string $url
     * @param string $title
     */
    public function set_link($day, $url, $title, $status='')
    {
        $this->link[$day]['url'] = $url;
        $this->link[$day]['title'] = $title;
        if ($status == '') {
            $this->link[$day]['status'] = 'ここをクリックすれば指定されたアクションを行います。';
        } else {
            $this->link[$day]['status'] = $status;
        }
        $this->link[$tday]['id'] = sprintf('%02d', $day);
    }
    public function setAllLinkYMD($year, $month, $url)
    {
        // $tdayがその月の日数を超えるまでループ
        $tday = 1;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        while ($tday <= $mday) {
            if (preg_match('/\?/', $url)) {
                $url_para = $url . "&year={$year}&month={$month}&day={$tday}";
            } else {
                $url_para = $url . "?year={$year}&month={$month}&day={$tday}";
            }
            $this->link[$tday]['url']    = $url_para;
            $this->link[$tday]['title']  = "{$year}年 {$month}月 {$tday}日 の内容を表示します。";
            $this->link[$tday]['status'] = $this->link[$tday]['title'];
            $this->link[$tday]['id']     = sprintf('%4d%02d%02d', $year, $month, $tday);
            $tday++;
        }
    }
    
    /**
     * 現在設定されているリンクを全て解除します。
     *
     */
    public function clear_link()
    {
        $this->link = array();
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /**
     * 休日の計算を行います。
     * （休日名もセットしていますが、現在は出力していません。）
     *
     * @param int $year
     * @param int $month
     */
    protected function set_holiday($year, $month)
    {
        // その月の最初の月曜日が何日かを算出
        $day = 1;
        while (date('w', mktime(0 ,0 ,0 , $month, $day, $year)) <> 1) {
            $day++;
        }
        // 祝日をセット
        switch ($month) {
        case 1:
            // 元旦
            $this->holiday[1] = 1;
            $this->holiday_name[1] = '元旦'; 
            // 成人の日
            if ($year < 2000) {
                $this->holiday[15] = 1;
                $this->holiday_name[15] = '成人の日'; 
            } else {
                $this->holiday[$day+7] = 1;
                $this->holiday_name[$day+7] = '成人の日'; 
            }
            // 会社の休業日をセット
            for ($i=2; $i<=15; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if($year==2011 && $i==4) {
                    } elseif($year==2023 && $i==2) {
                    } else {    
                        if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                            if (day_off($timestamp)) {
                                $this->holiday[$i] = 1;
                                $this->holiday_name[$i] = '年始休暇';
                            }
                        }
                    }
                }
            }
            break;
        case 2:
            // 建国記念の日
            $this->holiday[11] = 1;
            $this->holiday_name[11] = '建国記念の日'; 
            // 天皇誕生日
            if ($year > 2019) {
                $this->holiday[23] = 1;
                $this->holiday_name[23] = '天皇誕生日';
            }
            break;
        case 3:
            // 春分の日
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(20.8431+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holiday_name[$tmp] = '春分の日'; 
            }
            break;
        case 4:
            // 天皇誕生日 or みどりの日
            $this->holiday[29] = 1;
            if ($year < 1989) {
                $this->holiday_name[29] = '天皇誕生日';
            } elseif ($year < 2017) {
                $this->holiday_name[29] = 'みどりの日';
            } else {
                $this->holiday_name[29] = '昭和の日';
            }
            if ($year == 2019) {
                $this->holiday[30] = 1;
                $this->holiday_name[30] = '休日';
            }
            break;
        case 5:
            // 憲法記念日
            $this->holiday[3] = 1;
            $this->holiday_name[3] = '憲法記念日';
            if ($year > 2017) {
                $this->holiday[4] = 1;
                $this->holiday_name[4] = 'みどりの日';
            }
            // こどもの日
            $this->holiday[5] = 1;
            $this->holiday_name[5] = 'こどもの日';
            if ($year == 2019) {
                $this->holiday[1] = 1;
                $this->holiday_name[1] = '新天皇即位日';
                $this->holiday[2] = 1;
                $this->holiday_name[2] = '休日';
            }
            break;
        case 7:
            // 海の日
            if ($year > 2002) {
                $this->holiday[$day+14] = 1;
                $this->holiday_name[$day+14] = '海の日';
            } elseif($year > 1994) {
                $this->holiday[21] = 1;
                $this->holiday_name[21] = '海の日';
            }
            if ($year == 2020) {
                $this->holiday[$day+14] = 0;
                $this->holiday_name[$day+14] = '';
                $this->holiday[23] = 1;
                $this->holiday_name[24] = '海の日';
            }
            if ($year == 2020) {
                $this->holiday[24] = 1;
                $this->holiday_name[24] = 'スポーツの日';
            }
            break;
        case 8:
            if ($year > 2017) {
                $this->holiday[11] = 1;
                $this->holiday_name[11] = '山の日';
            }
            if ($year == 2020) {
                $this->holiday[11] = 1;
                $this->holiday_name[11] = '';
                $this->holiday[10] = 1;
                $this->holiday_name[10] = '山の日';
            }
            // 会社の休業日をセット
            for ($i=5; $i<=26; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                        if (day_off($timestamp)) {
                            $this->holiday[$i] = 1;
                            $this->holiday_name[$i] = '夏期休暇';
                        }
                    }
                }
            }
            break;
        case 9:
            // 敬老の日
            if ($year < 2003) {
                $this->holiday[15] = 1;
                $this->holiday_name[15] = '敬老の日';
            } else {
                $this->holiday[$day+14] = 1;
                $this->holiday_name[$day+14] = '敬老の日';
            }
            // 秋分の日
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(23.2488+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holiday_name[$tmp] = '秋分の日';
            }
            break;
        case 10;
            // 体育の日
            if ($year < 2000) {
                $this->holiday[10] = 1;
                $this->holiday_name[10] = '体育の日';
            } else {
                $this->holiday[$day+7] = 1;
                $this->holiday_name[$day+7] = 'スポーツの日';
            }
            if ($year == 2019) {
                $this->holiday[22] = 1;
                $this->holiday_name[22] = '即位礼正殿の儀';
            }
            
            if ($year == 2020) {
                $this->holiday[$day+7] = 0;
                $this->holiday_name[$day+7] = '';
            }
            break;
        case 11:
            // 文化の日
            $this->holiday[3] = 1;
            $this->holiday_name[3] = '文化の日';
            
            // 勤労感謝の日
            $this->holiday[23] = 1;
            $this->holiday_name[23] = '勤労感謝の日';
            break;
        case 12:            
            // 天皇誕生日
            if ($year > 1988) {
                $this->holiday[23] = 1;
                $this->holiday_name[23] = '天皇誕生日';
            }
            if ($year == 2014) {
                $this->holiday[23] = 0;
                $this->holiday_name[23] = '天皇誕生日 26日と振替出勤';
            }
            if ($year == 2015) {
                $this->holiday[23] = 0;
                $this->holiday_name[23] = '天皇誕生日 25日と振替出勤';
            }
            if ($year == 2014) {
                $this->holiday[26] = 1;
                $this->holiday_name[26] = '年末休暇 23日と振替休日';
            }
            if ($year == 2015) {
                $this->holiday[25] = 1;
                $this->holiday_name[25] = '年末休暇 23日と振替休日';
            }
            // 天皇誕生日 2019年から無くなる
            if ($year > 2018) {
                $this->holiday[23] = 0;
                $this->holiday_name[23] = '';
            }
            // 2020年のみ26日出勤
            /*
            if ($year == 2020) {
                $this->holiday[26] = 0;
                $this->holiday_name[26] = '';
            }
            */
            // 会社の休業日をセット
            for ($i=20; $i<=31; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                        if (day_off($timestamp)) {
                            $this->holiday[$i] = 1;
                            $this->holiday_name[$i] = '年末休暇';
                        }
                    }
                }
            }
            break;  
        }
        
        // 国民の休日をセット
        if ($year > 1985 && $year < 2017) {
            for ($i=1; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i++) {
                if (isset($this->holiday[$i]) && isset($this->holiday[$i+2])) {
                    $this->holiday[$i+1] = 1;
                    $this->holiday_name[$i+1] = '国民の休日';
                    $i = $i + 3;
                }
            }
        }
        
        // 振り替え休日をセット
        $sday = $day - 1;
        if ($sday == 0) $sday = 7;
        for ($i=$sday; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i=$i+7) {
            if (isset($this->holiday[$i]) && isset($this->holiday[$i+1]) && isset($this->holiday[$i+2])) {
                $this->holiday[$i+3] = 1;
                $this->holidayName[$i+3] = '振替休日';
            } elseif (isset($this->holiday[$i]) && isset($this->holiday[$i+1])) {
                $this->holiday[$i+2] = 1;
                $this->holidayName[$i+2] = '振替休日';
            } elseif (isset($this->holiday[$i])) {
                $this->holiday[$i+1] = 1;
                $this->holidayName[$i+1] = '振替休日';
            }
        }
        if ($year == 2015) {
            $this->holiday[24] = 0;
            $this->holiday_name[24] = '';
        }
    }
} // Class Calendar End

?>
