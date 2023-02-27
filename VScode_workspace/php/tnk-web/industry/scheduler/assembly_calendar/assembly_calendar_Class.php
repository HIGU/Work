<?php
//////////////////////////////////////////////////////////////////////////////
// 組立ラインのカレンダー クラス DBメンテナンスを含む                       //
//                     会社基本カレンダークラス(CalendarTNK)を拡張している  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/10 Created   assembly_calendar_Class.php                         //
//////////////////////////////////////////////////////////////////////////////
if (class_exists('AssemblyCalendar')) {
    return;
}
require_once ('../../../CalendarTnkClass.php');     // 基底カレンダークラス

/********************************************************************************
*         AssemblyCalendarClass CalendarTNK (base class) 基底クラス             *
********************************************************************************/
///// namespace Common {} は現在使用しない 使用例：Common::ComTableMnt → $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyCalendar extends CalendarTNK
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
        parent::__construct($startWeek, $dsplayFlg);
        return;
    }
    
    // ************************************************************************** //
    // * 設定された内容でカレンダーを表示する。                                 * //
    // * @param char(4) $line                                                   * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param int $day                                                        * //
    // * @return string $calendar (カレンダーテーブル出力)                      * //
    // ************************************************************************** //
    public function show_calendar($line, $year, $month, $day=0)
    {
        // 休日の算出
        if (!isset($this->userHoliday)) $this->setUserHoliday($line, $year, $month);
        
        // 実行
        return $this->showCalendarExecute($year, $month, $day);
    }
    
    /***************************************************************************
    *                              Private methods                             *
    ***************************************************************************/
    // ************************************************************************** //
    // * 停止日の計算を行う。(コメントもセットする)組立ラインカレンダーより取得 * //
    // * @param char(4) $line                                                   * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function setUserHoliday($line, $year, $month)
    {
        $endMonth = $month + 1;
        $endYear  = $year;
        if ($endMonth == 13) {
            $endMonth = '01';
            $endYear++;
        }
        // DBよりラインの停止日データを取得
        $query = "
            SELECT tdate FROM assembly_calendar
            WHERE line = '{$line}' AND tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        if (($rows = $this->getResult2($query, $check)) <= 0) {
            ///// 登録無しholidayのデータを使用してDBを初期化する
            $this->initCalendarFormat($line, $year, $month);
        }
        // DBの再読込み 必要なカラムを取得する
        $query = "
            SELECT to_char(tdate, 'DD'), note, bd_flg FROM assembly_calendar
            WHERE line = '{$line}' AND tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
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
    private function initCalendarFormat($line, $year, $month)
    {
        if (!isset($this->holiday)) $this->setHoliday($year, $month);
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        while (1) {
            $day = date('j', $timestamp);               // 先頭に0を付けない(1-31)
            if ($this->holiday[$day]) {
                $bd_flg = 'FALSE';                      // 停止日にセット
            } else {
                $bd_flg = 'TRUE';                       // 営業日にセット
                $note   = '';
            }
            $note = $this->holidayName[$day];   // コメントをセット
            $insert = "
                INSERT INTO assembly_calendar (line, tdate, bd_flg, note, last_date, last_user)
                VALUES ('{$line}', DATE '{$year}-{$month}-{$day}', {$bd_flg}, '{$note}', '{$last_date}', '{$last_user}')
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
        $endMonth = $month + 1;
        $endYear  = $year;
        if ($endMonth == 13) {
            $endMonth = '01';
            $endYear++;
        }
        // DBより会社基本カレンダーの休日データを取得
        $query = "
            SELECT to_char(tdate, 'DD'), note, bd_flg FROM company_calendar
            WHERE tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
            ORDER BY tdate ASC
        ";
        $res = array();
        if (($rows = $this->getResult2($query, $res)) <= 0) {
            ///// 登録無し 運用エラー
            $_SESSION['s_sysmsg'] = '基本カレンダーにデータがありません。担当者へ連絡して下さい。';
            for ($i=0; $i<=31; $i++) {
                $this->holiday[$i] = 0;         // ダミーで全て稼働日
                $this->holidayName[$i] = '';     // ダミー
            }
        } else {
            for ($i=0; $i<$rows; $i++) {
                $key = sprintf('%d', $res[$i][0]);
                if ($res[$i][2] == 'f') {
                    $this->holiday[$key] = 1;
                } else {
                    $this->holiday[$key] = 0;
                }
                $this->holidayName[$key] = $res[$i][1];
            }
        }
    }
    
} // Class CalendarTNK End

?>
