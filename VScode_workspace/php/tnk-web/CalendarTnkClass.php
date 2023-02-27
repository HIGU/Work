<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器カレンダー ユーザーインターフェース クラス 休日はDBより取得  //
//                          データアクセスオブジェクトクラスを拡張している  //
// Copyright (C) 2006-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/19 Created   CalendarTnkClass.php                                //
//  Ver1.00   DB(company_holiday, assembly_holiday, ...)より休日を取得      //
// 2006/06/26 データベースのテーブルを変更 (全ての日に休日・稼働日の設定)   //
//  Ver1.10   company_holiday → company_calendar (コメントも全ての日に対応)//
//            show_calendar()メソッドを echo 出力から return 出力へ変更     //
// 2006/06/29 setAllLinkYMD()メソッドの $url は onClick=''用の JavaScript   //
//  Ver1.11   メソッドを指定する $url="location.replace(\"?????\")" Ajax対応//
// 2006/07/01 show_calendar()の <td> → <td nowrap> を追加                  //
//  Ver1.12                                                                 //
// 2006/07/06 プロパティー(メンバー)を public → protected へ変更           //
//  Ver1.13   setUserHoliday()メソッドの一部ロジック変更 protected→private //
// 2006/07/10 CSSにclassOnMouseOver,weekClassを追加しHTMLソースからstyle削除//
//  Ver1.14   calendar.css も Ver1.14 を使用すること                        //
// 2007/02/06 みどりの日が昭和の日に変わる条件を追加。国民の日→新みどりの日//
//  Ver1.15   の条件を追加。振替休日の当日が祝日のチェック追加(３連休まで)  //
// 2010/08/23 2011年1月5日が自動的に休業になってしまう為調整           大谷 //
// 2019/11/14 21期の休みに対応(スポーツの日・海の日・体育の日等)       大谷 //
// 2021/10/28 祝祭日の名前を正式名称に（スポーツの日、こどもの日等）   大谷 //
//////////////////////////////////////////////////////////////////////////////
if (class_exists('CalendarTNK')) {
    return;
}
// require_once ('CalendarClass.php');             // 基底カレンダークラス
require_once ('daoInterfaceClass.php');         // TNK 全共通 DAOインターフェースクラス
define('CalendarTNK_VERSION', '1.15');

/********************************************************************************
*             CalendarTNK (base class) 基底(実務上の)クラス                     *
********************************************************************************/
///// namespace Common {} は現在使用しない 使用例：Common::ComTableMnt → $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class CalendarTNK extends daoInterfaceClass
{
    ///// Private   properties
    ///// Protected properties
    protected $wfrom;
    protected $beforeandafterday;
    protected $link = array();
    protected $kindTitle;
    protected $kind;
    protected $cssClass;
    protected $week;
    protected $holiday;
    protected $holidayName;
    protected $userHoliday;
    protected $userHolidayName;
    ///// Public    properties
    
    /****************************************************************************
    *                               Public methods                              *
    ****************************************************************************/
    // ************************************************************************** //
    // * コンストラクタ                                                         * //
    // * @param int $startWeek  開始曜日(0=日曜～6=土曜)                        * //
    // * @param int $dsplayFlg  対象月以外の日付を表示 (0=しない, 1=する)       * //
    // * @return void                                                           * //
    // ************************************************************************** //
    ///// Constructer の定義 {php5 は __construct()} {デストラクタ__destruct()}
    public function __construct($startWeek=0, $dsplayFlg=0)
    {
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        // parent::__construct($startWeek, $dsplayFlg);
        //return;
        
        // 開始曜日（0-日曜, 6-土曜）
        $this->wfrom = $startWeek;
        
        // 当月以外の日付を表示するかどうか（0-表示しない 1-表示する）
        $this->beforeandafterday = $dsplayFlg;
        
        // 曜日に対する背景色の設定（0-平日, 1-土, 2-日祝日, 3-当月以外の平日, 4-当日）
        // $this->cssClass = array('#eeeeee', '#ccffff', '#ffcccc', '#ffffff', '#ffffcc', 'yellow');
        $this->cssClass = array(" class='class0'", " class='class1'", " class='class2'", " class='class3'", " class='class4'", " class='class5'", " class='class6'");
        $this->kindTitle = array(1, 0, 0, 0, 0, 0, 1);  // 旧は array(2, 0, 0, 0, 0, 0, 1) 日土は同じ色にして指定休日を強調
        $this->kind      = array(0, 0, 0, 0, 0, 0, 0);  // 旧は array(2, 0, 0, 0, 0, 0, 1)
        
        // 曜日の名前
        $this->week = array('日', '月', '火', '水', '木', '金', '土');
    }
    
    // ************************************************************************** //
    // * 設定された内容でカレンダーを表示する。                                 * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param int $day                                                        * //
    // * @return string $calendar (カレンダーテーブル出力)                      * //
    // ************************************************************************** //
    public function show_calendar($year, $month, $day=0)
    {
        // 休日の算出
        if (!isset($this->userHoliday)) $this->setUserHoliday($year, $month);
        
        // 実行
        return $this->showCalendarExecute($year, $month, $day);
    }
    
    // ************************************************************************** //
    // * 指定された日に対してリンクを設定する。個別リンク                       * //
    // * @param int $day                                                        * //
    // * @param string $url                                                     * //
    // * @param string $title               チップヘルプ表示                    * //
    // * @param string $status  default=''  ステータスバー表示                  * //
    // * @return void                                                           * //
    // ************************************************************************** //
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
    
    // ************************************************************************** //
    // * 全ての日に対してリンクを設定する。                                     * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param string $url                                                     * //
    // * @param string $title  default=''   チップヘルプとステータスバー表示    * //
    // * @return void                                                           * //
    // ************************************************************************** //
    public function setAllLinkYMD($year, $month, $url, $title='')
    {
        ///// Ver1.11～は $url は onClick=''用の JavaScriptのメソッドを指定する
        $uniq = uniqid();
        // $tdayがその月の日数を超えるまでループ
        $tday = 1;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        while ($tday <= $mday) {
            if (preg_match('/\?/', $url)) {
                $url_para = "&year={$year}&month={$month}&day={$tday}&id={$uniq}";
            } else {
                $url_para = "?year={$year}&month={$month}&day={$tday}&id={$uniq}";
            }
            if (preg_match('/%s/', $url)) {
                $urlParaAdd = sprintf($url, $url_para);
            } else {
                $urlParaAdd = $url;
            }
            $this->link[$tday]['url']    = $urlParaAdd;
            $this->link[$tday]['title']  = "{$year}年 {$month}月 {$tday}日 {$title}";
            $this->link[$tday]['status'] = $this->link[$tday]['title'];
            $this->link[$tday]['id']     = sprintf('%4d%02d%02d', $year, $month, $tday);
            $tday++;
        }
    }
    
    // ************************************************************************** //
    // * 現在設定されているリンクを全て解除する。                               * //
    // * @return void                                                           * //
    // ************************************************************************** //
    public function clear_link()
    {
        $this->link = array();
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    // ************************************************************************** //
    // * 設定された内容でカレンダーを表示する。実行ロジック                     * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param int $day                                                        * //
    // * @return string $calendar (カレンダーテーブル出力)                      * //
    // ************************************************************************** //
    protected function showCalendarExecute($year, $month, $day)
    {
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
        $calendar = "\n";   // 初期化
        $calendar .= "<table class='calendar' summary='カレンダー'>\n";
        $calendar .= "<tr>\n";
        if ($year == $arr['year'] && $month == $arr['mon']) {
            $calendar .= "<th class='currentTitle' colspan='7'>\n";
            $calendar .= $year . '年' . $month . "月\n";
            $calendar .= "&nbsp;今日{$arr['mday']}日\n";
        } else {
            $calendar .= "<th class='title' colspan='7'>\n";
            $calendar .= $year . '年' . $month . "月\n";
        }
        $calendar .= "</th>\n";
        $calendar .= "</tr>\n";
        // 曜日表示
        $calendar .= "<tr class='weekClass'>\n";
        for ($i=0; $i<7; $i++) {
            $wk = ($this->wfrom + $i) % 7;
            $calendar .= '<td nowrap' . $this->cssClass[$this->kindTitle[$wk]] . ">" . $this->week[$wk] . "</td>\n";
        }
        $calendar .= "</tr>\n";
        // $tdayがその月の日数を超えるまでループ
        $tday = $from;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        $wnum = 0;  // 週番号
        while ($tday <= $mday) {
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $wk = ($this->wfrom + $i) % 7;
                $cssClass = $this->cssClass[$this->kind[$wk]];
                /*
                if ($year == 2020 && $month == 12 && $tday == 26) {
                    $cssClass = " class='class0'";
                }
                */
                // 当月判定
                if ($tday >= 1 && $tday <= $mday) {
                    if ($arr['year'] == $year && $arr['mon'] == $month && $arr['mday'] == $tday) {
                        // 当日
                        if ($this->userHoliday[$tday] != 1) {
                            $cssClass = $this->cssClass[4];   // 当日
                        } else {
                            $cssClass = $this->cssClass[6];   // 当日と休日が重複した場合
                        }
                    } else if ($this->userHoliday[$tday] == 1) {    // (旧タイプは@$this->holiday[$tday] == 1)
                        // 休日(ユーザー指定)
                        $cssClass = $this->cssClass[2];
                    }
                    // 指定日
                    if ($day == $tday) {    // $day が0に設定されていれば無効
                        if ($this->userHoliday[$tday] != 1) {
                            $cssClass = $this->cssClass[5];   // 指定日(クリックした日)
                        } else {
                            $cssClass = $this->cssClass[6];   // 指定日と休日が重複した場合
                        }
                    }
                } else {
                    // if ($wk > 0 && $wk < 6)オリジナル → から変更して当月以外の全ての日
                    if ($wk >= 0 && $wk <= 6) $cssClass = $this->cssClass[3];
                }
                list($lyear, $lmonth, $lday) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month, $tday, $year)));
                // データ部分表示
                if (($tday >= 1 && $tday <= $mday) || $this->beforeandafterday) {
                    if (isset($this->link[$tday])) {
                        if ($this->userHolidayName[$tday] != '') {
                            $title  = "{$this->link[$tday]['title']} {$this->userHolidayName[$tday]}";
                            $status = "{$this->link[$tday]['status']} {$this->userHolidayName[$tday]}";
                        } else {
                            $title  = "{$this->link[$tday]['title']}";
                            $status = "{$this->link[$tday]['status']}";
                        }
                        $className = str_replace(' class=', '', $cssClass);
                        $className = str_replace("'", '', $className);
                        $calendar .= "<td nowrap{$cssClass}\n";
                        $calendar .= "    onClick='{$this->link[$tday]['url']}; return false;'\n";
                        $calendar .= "    title='{$title}'\n";
                        $calendar .= "    onMouseover=\"this.className='classOnMouseOver'; status='{$status}';return true;\"\n";
                        $calendar .= "    onMouseout =\"this.className='{$className}'; status=''\"\n";
                        $calendar .= "    id='{$this->link[$tday]['id']}'\n";
                        $calendar .= ">\n";
                        $calendar .= "    <label for='{$this->link[$tday]['id']}'>{$lday}</label>\n";
                        $calendar .= "</td>\n";
                    } else {
                        $calendar .= "<td nowrap{$cssClass}>\n";
                        $calendar .= "    {$lday}\n";
                        $calendar .= "</td>\n";
                    }
                } else {
                    $calendar .= "<td nowrap{$cssClass}>\n";
                    $calendar .= "    &nbsp;\n";
                    $calendar .= "</td>\n";
                }
                $tday++;
            }
            $calendar .= "</tr>\n"; 
            $wnum++;
        }
        switch ($wnum) {
        case 4;
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $calendar .= "<td nowrap class='class3'>&nbsp;</td>\n";
            }
            $calendar .= "</tr>\n";
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $calendar .= "<td nowrap class='class3'>&nbsp;</td>\n";
            }
            $calendar .= "</tr>\n";
            break;
        case 5;
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $calendar .= "<td nowrap class='class3'>&nbsp;</td>\n";
            }
            $calendar .= "</tr>\n";
            break;
        }
        $calendar .= "</table>\n";
        return $calendar;
    }
    
    /***************************************************************************
    *                              Private methods                             *
    ***************************************************************************/
    // ************************************************************************** //
    // * 休日の計算を行う。(休日名もセットする)  ユーザー設定カレンダーより取得 * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function setUserHoliday($year, $month)
    {
        $endMonth = $month + 1;
        $endYear  = $year;
        if ($endMonth == 13) {
            $endMonth = '01';
            $endYear++;
        }
        // DBより会社の休日データを取得
        $query = "
            SELECT tdate FROM company_calendar
            WHERE tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        if (($rows = $this->getResult2($query, $check)) <= 0) {
            ///// 登録無しholidayのデータを使用してDBを初期化する
            $this->initCalendarFormat($year, $month);
        }
        // DBの再読込み 必要なカラムを取得する
        $query = "
            SELECT to_char(tdate, 'DD'), note, bd_flg FROM company_calendar
            WHERE tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        $res = array();
        if (($rows = $this->getResult2($query, $res)) >= 1) {
            for ($i=0; $i<$rows; $i++) {
                $key = sprintf('%d', $res[$i][0]);
                if ($res[$i][2] == 'f') {
                    $this->userHoliday[$key] = 1;
                } else {
                    $this->userHoliday[$key] = 0;
                }
                $this->userHolidayName[$key] = $res[$i][1];
            }
        }
    }
    
    // ************************************************************************** //
    // * カレンダーの初期化を行う。(this->holidayのデータを使用)                * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function initCalendarFormat($year, $month)
    {
        if (!isset($this->holiday)) $this->setHoliday($year, $month);
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        while (1) {
            $day = date('j', $timestamp);               // 先頭に0を付けない(1-31)
            if (isset($this->holiday[$day])) {
                $bd_flg = 'FALSE';                      // 休日にセット
                $note   = $this->holidayName[$day];     // 休日名をセット
            } else {
                $bd_flg = 'TRUE';                       // 営業日にセット
                $note   = '';
            }
            if (date('w', $timestamp) == 0 || date('w', $timestamp) == 6) { // 0=日曜か6(土曜)は休日
                $bd_flg = 'FALSE';                      // 休日にセット
            }
            $insert = "
                INSERT INTO company_calendar (tdate, bd_flg, note, last_date, last_user)
                VALUES (DATE '{$year}-{$month}-{$day}', {$bd_flg}, '{$note}', '{$last_date}', '{$last_user}')
            ";
            if ($this->query_affected($insert) <= 0) {
                $_SESSION['s_sysmsg'] = 'カレンダーの初期化に失敗しました。管理担当者に連絡して下さい！';
                break;
            }
            $timestamp += 86400;
            if (date('m', $timestamp) != $month) break;
        }
    }
    
    // ************************************************************************** //
    // * 休日の計算を行う。(休日名もセットする)                                 * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function setHoliday($year, $month)
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
            $this->holidayName[1] = '元旦';
            // 成人の日
            if ($year < 2000) {
                $this->holiday[15] = 1;
                $this->holidayName[15] = '成人の日';
            } else {
                $this->holiday[$day+7] = 1;
                $this->holidayName[$day+7] = '成人の日';
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
                                $this->holidayName[$i] = '年始休暇';
                            }
                        }
                    }
                }
            }
            break;
        case 2:
            // 建国記念の日
            $this->holiday[11] = 1;
            $this->holidayName[11] = '建国記念の日';
            // 天皇誕生日
            if ($year > 2019) {
                $this->holiday[23] = 1;
                $this->holidayName[23] = '天皇誕生日';
            }
            break;
        case 3:
            // 春分の日
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(20.8431+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holidayName[$tmp] = '春分の日';
            }
            break;
        case 4:
            // 天皇誕生日 or みどりの日 or 昭和の日
            $this->holiday[29] = 1;
            if ($year < 1989) {
                $this->holidayName[29] = '天皇誕生日';
            } elseif ($year < 2007) {
                $this->holidayName[29] = 'みどりの日';
            } else {
                $this->holidayName[29] = '昭和の日';
            }
            break;
        case 5:
            // 憲法記念日
            $this->holiday[3] = 1;
            $this->holidayName[3] = '憲法記念日';
            
            // こどもの日
            $this->holiday[5] = 1;
            $this->holidayName[5] = 'こどもの日';
            break;
        case 7:
            // 海の日
            if ($year > 2002) {
                $this->holiday[$day+14] = 1;
                $this->holidayName[$day+14] = '海の日';
            } elseif($year > 1994) {
                $this->holiday[21] = 1;
                $this->holidayName[21] = '海の日';
            }
            if ($year == 2020) {
                $this->holiday[$day+14] = 0;
                $this->holidayName[$day+14] = '';
                $this->holiday[24] = 1;
                $this->holidayName[24] = '海の日';
            }
            if ($year == 2020) {
                $this->holiday[24] = 1;
                $this->holidayName[24] = 'スポーツの日';
            }
            break;
        case 8:
            if ($year > 2017) {
                $this->holiday[11] = 1;
                $this->holidayName[11] = '山の日';
            }
            if ($year == 2020) {
                $this->holiday[11] = 1;
                $this->holidayName[11] = '';
                $this->holiday[10] = 1;
                $this->holidayName[10] = '山の日';
            }
            // 会社の休業日をセット
            for ($i=5; $i<=26; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                        if (day_off($timestamp)) {
                            $this->holiday[$i] = 1;
                            $this->holidayName[$i] = '夏期休暇';
                        }
                    }
                }
            }
           /*
            if ($year > 2017) {
                $this->holiday[11] = 1;
                $this->holidayName[11] = '山の日';
            }
            */
            break;
        case 9:
            // 敬老の日
            if ($year < 2003) {
                $this->holiday[15] = 1;
                $this->holidayName[15] = '敬老の日';
            } else {
                $this->holiday[$day+14] = 1;
                $this->holidayName[$day+14] = '敬老の日';
            }
            // 秋分の日
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(23.2488+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holidayName[$tmp] = '秋分の日';
            }
            break;
        case 10;
            // 体育の日
            if ($year < 2000) {
                $this->holiday[10] = 1;
                $this->holidayName[10] = '体育の日';
            } else {
                $this->holiday[$day+7] = 1;
                $this->holidayName[$day+7] = 'スポーツの日';
            }
            if ($year == 2019) {
                $this->holiday[22] = 1;
                $this->holidayName[22] = '即位礼正殿の儀';
            }
            
            if ($year == 2020) {
                $this->holiday[$day+7] = 0;
                $this->holidayName[$day+7] = '';
            }
            break;
        case 11:
            // 文化の日
            $this->holiday[3] = 1;
            $this->holidayName[3] = '文化の日';
            
            // 勤労感謝の日
            $this->holiday[23] = 1;
            $this->holidayName[23] = '勤労感謝の日';
            break;
        case 12:            
            // 天皇誕生日
            if ($year < 2019) {
                $this->holiday[23] = 1;
                $this->holidayName[23] = '天皇誕生日';
            }
            /*
            // 天皇誕生日 2019年から無くなる
            if ($year > 2018) {
                $this->holiday[23] = 0;
                $this->holiday_name[23] = '';
            }
            */
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
                            $this->holidayName[$i] = '年末休暇';
                        }
                    }
                }
            }
            break;
        }
        
        // 国民の休日をセット
        if ($year > 1985 && $year <= 2006) {
            for ($i=1; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i++) {
                if (isset($this->holiday[$i]) && isset($this->holiday[$i+2])) {
                    $this->holiday[$i+1] = 1;
                    $this->holidayName[$i+1] = '国民の休日';
                    $i = $i + 3;
                }
            }
        }
        
        // 新 みどり日をセット
        if ($year >= 2007) {
            for ($i=1; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i++) {
                if (isset($this->holiday[$i]) && isset($this->holiday[$i+2])) {
                    $this->holiday[$i+1] = 1;
                    $this->holidayName[$i+1] = 'みどりの日';
                    $i = $i + 3;
                }
            }
        }
        
        // 振替休日をセット
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
    }
    
} // Class CalendarTNK End

?>