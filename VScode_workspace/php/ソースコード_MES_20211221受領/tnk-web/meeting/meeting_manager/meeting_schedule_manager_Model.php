<?php
//////////////////////////////////////////////////////////////////////////////
// 部課長用会議スケジュール照会         MVC Model 部                        //
// Copyright (C) 2010 Norihisa.Ohya nirihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_Model.php                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');    // TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
* 部課長用会議スケジュール照会 MVCのModel部の base class 基底クラスの定義                *
*****************************************************************************************/
class MeetingSchedule_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $whereNoLine;                       // 共用 SQLのWHERE句(Line条件を除く)
    private $GraphName;
                                                // GanttChartのファイル名
    
    ///// public properties
    public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        ///// プロパティーの初期化
        $this->GraphName = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}.png";
        // 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        $year       = $request->get('year');
        $month      = $request->get('month');
        $day        = $request->get('day');
        if ($year == '') {
            // 本日の日付に設定
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        $listSpan   = $request->get('listSpan');
        $room_no    = $request->get('room_no');
        $str_date   = $request->get('str_date');
        $end_date   = $request->get('end_date');
        if ($str_date == '') {
            $str_date = $year . $month . $day;
        }
        if ($end_date == '') {
            $end_date = $year . $month . $day;
        }
        if ($request->get('showMenu') == 'MyList') {
            $request->add('my_flg', 1);
        }
        switch ($request->get('showMenu')) {
        case 'GanttChart':
            // 基本WHERE区の設定
            if ($request->get('CTM_pageRec') > 100) {
                $request->add('CTM_pageRec', 100);
                $_SESSION['s_sysmsg'] = 'ガントチャートでは１００行までです。\n\n１００行に調整しました。';
            }
            // 100行表示で固定化
            $request->add('CTM_pageRec', 100);
            $this->where = "WHERE str_time>='{$year}-{$month}-{$day} 00:00:00' AND str_time<=(timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        case 'PlanList':
        case 'Room':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM meeting_room_master {$this->where}
            ";
            break;
        case 'Group':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM (SELECT count(group_no) FROM meeting_mail_group GROUP BY group_no {$this->where})
                AS meeting_group
            ";
            break;
        case 'MyList':
            $request->add('my_flg', 1);
            $this->where = "'{$_SESSION['User_ID']}', timestamp '{$year}-{$month}-{$day} 00:00:00', timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day'";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_mylist({$this->where})
            ";
            break;
        case 'Print' :
            if ($room_no != '') {
                $this->where = "WHERE room_no = {$room_no} and to_char(str_time, 'YYYYMMDD') >= {$str_date} and to_char(end_time, 'YYYYMMDD') <= {$end_date}";
            } else {
                $this->where = "WHERE to_char(str_time, 'YYYYMMDD') >= {$str_date} and to_char(end_time, 'YYYYMMDD') <= {$end_date}";
            }
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        case 'List'  :
        case 'Apend' :
        case 'Edit'  :
        default      :
            $this->where = "WHERE str_time>='{$year}-{$month}-{$day} 00:00:00' AND str_time<=(timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        }
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'meeting_schedule_manager.log');
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// マネージャー登録を抜出す
    public function getViewManager(&$result)
    {
        $query = "
            SELECT id,                            -- 00
                   name                           -- 01
            FROM
                common_authority AS c
            LEFT OUTER JOIN
                user_detailes AS u on c.id =u.uid
            WHERE
                division = 33
            ORDER BY id
        ";
        // 小森谷課長011061
        $res_m = array();
        if ( ($rows_m=$this->execute_List($query, $res_m)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res_m);
        return $rows_m;
    }
    
    ///// MyList部
    public function getViewMyList(&$result, $u_id, $str_date, $end_date)
    {
        $this->where = "'{$u_id}', timestamp '{$str_date}', timestamp '{$end_date}'";
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YYYY-MM-DD HH24:MI')  -- 02
                ,to_char(end_time, 'YYYY-MM-DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN '有効'
                    ELSE '無効'
                 END                    AS 期限         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 15
            FROM
                meeting_schedule_mylist({$this->where}) AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            ORDER BY
                str_time ASC, end_time ASC
        ";
        // 小森谷課長011061
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subjectの改行を<br>に置換え
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Get部    ガントチャートのラインタイトル表示用データ取得
    public function getLineTitle($request)
    {
        if ($request->get('targetLineMethod') == '1') {
            // 個別指定
            if ($request->get('showLine')) $showLine = $request->get('showLine').' '; else $showLine = '全て ';
        } else {
            // 複数指定
            $arrayLine = $request->get('arrayLine');
            $showLine = '';
            for ($i=0; $i<count($arrayLine); $i++) {
                $showLine .= $arrayLine[$i] . ' ';
            }
        }
        return $showLine;
    }
    
    ///// Get部    ガントチャート表示用データ取得
    public function getViewGanttChart($request, $result, $menu, $str_ymd, $g_name, $map)
    {
        $this->GraphName = $g_name;
        //$graph = new GanttGraph(990, -1, 'auto');   // -1=自動, 0=でも問題は無かった
        $graph = new GanttGraph(990, -1);   // 'auto'を入れると3/8がなぜかエラーだった
        $graph->SetShadow();
        $graph->img->SetMargin(15, 17, 10, 15);     // defaultの 25→10 へ変更
        // グラフ用タイトル取得
        //if ($request->get('targetSeiKubun') == '1')
        //    $sei_kubun = '標準品';
        //elseif ($request->get('targetSeiKubun') == '3')
        //    $sei_kubun = '特注品';
        //else
        //    $sei_kubun = '全て';
        // Add title and subtitle
        $graph->title->Set(mb_convert_encoding("部課長スケジュール", 'UTF-8'));
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);
            // $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 10);
            // $graph->subtitle->Set(mb_convert_encoding("指定ライン：{$showLine} 製品区分：{$sei_kubun}", 'UTF-8'));
        // Show day, week and month scale
        $graph->ShowHeaders(GANTT_HDAY | GANTT_HHOUR);
        //$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
        //$graph->ShowHeaders(GANTT_HMIN | GANTT_HHOUR | GANTT_HDAY);
        // $graph->ShowHeaders(GANTT_HDAY | GANTT_HMONTH);
        // 1.5 line spacing to make more room
        $graph->SetVMarginFactor(1.0);      // 着手の実績を表示に伴い 2.5→1.0 へ
        // Setup some nonstandard colors
        $graph->SetMarginColor('lightgreen@0.8');
        $graph->SetBox(true, 'yellow:0.6', 2);
        $graph->SetFrame(true, 'darkgreen', 4);
        $graph->scale->divider->SetColor('yellow:0.6');
        $graph->scale->dividerh->SetColor('yellow:0.6');
        // 項目名を設定
        //$graph->scale->tableTitle->Set(mb_convert_encoding("\n計画No. 製品No. 数量\n製　　品　　名", 'UTF-8'));
        //$graph->scale->tableTitle->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        //$graph->scale->SetTableTitleBackground('darkgreen@0.6');
        //$graph->scale->tableTitle->Show(true);
        $item1 = mb_convert_encoding("名　　前", 'UTF-8');
        $item2 = mb_convert_encoding("\n出庫率", 'UTF-8');
        $graph->scale->actinfo->SetColTitles(array($item1));
        $graph->scale->actinfo->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->scale->actinfo->vgrid->SetColor('gray');
        $graph->scale->actinfo->SetBackgroundColor('darkgreen@0.6');
        $graph->scale->actinfo->SetColor('darkgray');
        
        // レンジを固定する 開始日と終了日を設定
        //$year  = substr($request->get('targetDate'), 0, 4);
        //$month = substr($request->get('targetDate'), 4, 2);
        //$lastDay = last_day($year, $month);
        //$graph->scale->SetRange($year.$month.'01', $year.$month.$lastDay);
        //$year       = $request->get('year');
        //$month      = $request->get('month');
        //$day        = $request->get('day');
        $year       = substr($str_ymd, 0, 4);
        $month      = substr($str_ymd, 4, 2);
        $day        = substr($str_ymd, 6, 2);
        //$year       = '2010';
        //$month      = '03';
        //$day        = '08';
        
        $str_range = $year . '-' . $month . '-' . $day . ' 07:00';
        $end_range = $year . '-' . $month . '-' . $day . ' 21:00';
        $str_date  = $year . '-' . $month . '-' . $day . ' 00:00';
        $end_date  = $year . '-' . $month . '-' . $day . ' 23:59';
        
        $graph->scale->SetRange($str_range, $end_range);
        // Make the WEEK scale
        $graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
        $graph->scale->week->SetFont(FF_FONT1);
        // Make the hour scale
        $graph->scale->hour->SetIntervall('1:00');
        $graph->scale->hour->SetStyle(HOURSTYLE_HM24);
        $graph->scale->hour->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Make the day scale
        $graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE5);
        $graph->scale->day->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Make the MINUTES scale
        //$graph->scale->minute->SetStyle(MINUTESTYLE_MM);
        //$graph->scale->minute->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Make the hour scale
        //$graph->scale->hour->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Instead of week number show the date for the first day in the week
        // on the week scale
        //$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY2);
        // Make the week scale font smaller than the default
        //$graph->scale->week->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Use the short name of the month together with a 2 digit year
        // on the month scale
        //$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
        //$graph->scale->month->SetFont(FF_GOTHIC, FS_NORMAL, 14);
        //$graph->scale->month->SetFontColor('white');
        //$graph->scale->month->SetBackgroundColor('blue');
        
        // 表示日付の最小値を取得
        $graph->scale->AdjustStartEndDay();     // マニュアルに無いメソッド・プロパティを使用
        $viewStartDate = date('Ymd', $graph->scale->iStartDate);
        
        if ($request->get('my_flg') != 1) {
            $rows_m = $this->getViewManager($result);
            if ($rows_m <= 0) return $rows_m;   // データが無ければChartは作らない
            $res_m = $result->get_array();
        } else {
            $rows_m      = 1;
            $res_m[0][0] = $_SESSION['User_ID'];
            $query = "SELECT trim(name) FROM user_detailes WHERE uid='{$res_m[0][0]}'";
            if (getUniResult($query, $name) <= 0) {
                $rows_m = 0;
                return $rows_m;   // データが無ければChartは作らない
            }
            $res_m[0][1] = $name;
        }
        for ($i=0; $i<$rows_m; $i++) {
            $num = 0;   // グラフ位置の配列(着手を追加による)
            $u_id   = $res_m[$i][0];
            $u_name = $res_m[$i][1];
        
            // 対象データを取得(PlanListのデータを使う)
            $rows = $this->getViewMyList($result, $u_id, $str_date, $end_date);
            //if ($rows <= 0) return $rows;   // データが無ければChartは作らない
            // GanttChartの作成
            $res = $result->get_array();
        
            // CSIM用データ初期化
            $targ = array();
            $alts = array();
            if ($rows <= 0) {
                $plan_no  = '';    // 計画番号
                $assy_no  = '';    // 製品番号
                $assy_name= '';    // 製品名
                $plan_zan = '';    // 計画残数
                $syuka    = '';    // 集荷日
                $chaku    = '';    // 開始日時
                $kanryou  = '';    // 終了日時
                $bikou    = '';    // 備考
                $plan_pcs = '';    // 計画数
                $cut_pcs  = '';    // 打切り数
                $end_pcs  = '';   // 完成数
                $ritu     = '';   // 出庫率
                $kousu    = '';   // 登録工数
                
                $item = mb_convert_encoding("{$u_name}", 'UTF-8');
                $strDate = '19700101';
                $endDate = '19700101';
                $activity[$num] = new GanttBar($i, array($item), $strDate, $endDate);
                $activity[$num]->caption->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                $activity[$num]->caption->SetColor('blue');
                $activity[$num]->SetPattern(BAND_RDIAG, 'white');
                $activity[$num]->SetFillColor('red');
                $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 10);
                if ($u_id == $_SESSION['User_ID']) {
                    $activity[$num]->title->SetColor('red');
                }
                // CSIM データ設定
                //$this->setActivityCSIM($activity[$num], $request, $menu, $res[$r]);
                $graph->Add($activity[$num]);

                $num++;     // 着手の実績表示のためインクリメント
                $num++;
            } else {
                for ($r=0; $r<$rows; $r++) {
                    $plan_no  = $res[$r][0];    // 計画番号
                    $assy_no  = $res[$r][1];    // 製品番号
                    $assy_name= $res[$r][2];    // 製品名
                    $plan_zan = $res[$r][3];    // 計画残数
                    $syuka    = $res[$r][4];    // 集荷日
                    
                    //$chaku    = $res[$r][5];    // 着手日
                    //$kanryou  = $res[$r][6];    // 完了日
                    $chaku    = $res[$r][2];    // 開始日時
                    $kanryou  = $res[$r][3];    // 終了日時
                    
                    $chaku_h  = substr($chaku, 11, 2) + 1 + $r;
                    $kanryou_h= substr($kanryou, 11, 2) + 2 + $r;
                    
                    $chaku2   = substr($chaku, 0, 11) . $chaku_h . ':' . substr($chaku, 14, 2);    // 開始日時調整
                    $kanryou2 = substr($kanryou, 0, 11) . $kanryou_h . ':' . substr($kanryou, 14, 2);    // 終了日時調整
                    
                    $bikou    = $res[$r][7];    // 備考
                    $plan_pcs = $res[$r][8];    // 計画数
                    $cut_pcs  = $res[$r][9];    // 打切り数
                    $end_pcs  = $res[$r][10];   // 完成数
                    $ritu     = $res[$r][11];   // 出庫率
                    $kousu    = $res[$r][12];   // 登録工数
                    $assy_name = mb_convert_kana($assy_name, 'k');
                    
                    //$manager_name = $u_name;
                    $item = mb_convert_encoding("{$u_name}", 'UTF-8');
                    
                    //$item = mb_convert_encoding("{$plan_no}", 'UTF-8');
                                            // ($row, $title, $startdate, $enddate)
                    // 完了月・着手月が違う場合はキャプションを表示
                    //if (substr($kanryou, 4, 2) != $month && substr($chaku, 4, 2) != $month) {
                        if ($num > 0 ) {
                            $activity[$num] = new GanttBar($i, '', $chaku, $kanryou);
                        } else {
                            $activity[$num] = new GanttBar($i, array($item), $chaku, $kanryou);
                        }
                        //$activity[$num] = new GanttBar($num, array($item), $viewStartDate, $viewStartDate);
                        //$activity[$num]->caption->Set(mb_convert_encoding("{$chaku}〜{$kanryou}", 'UTF-8'));
                        $activity[$num]->caption->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                        $activity[$num]->caption->SetColor('blue');
                        $activity[$num]->SetPattern(BAND_RDIAG, 'white');
                        if ($res[$r][4] == '出張') {
                            $activity[$num]->SetFillColor('red');
                        } elseif ($res[$r][4] == '外出') {
                            $activity[$num]->SetFillColor('green');
                        } else {
                            $activity[$num]->SetFillColor('blue');
                        }
                    //} else {
                    //    $activity[$num] = new GanttBar($num, array($item), $chaku, $kanryou);
                        //$activity[$num] = new GanttBar($num, array($item, $ritu_kousu), $chaku, $kanryou);
                        // Yellow diagonal line pattern on a red background
                    //    $activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
                    //    $activity[$num]->SetFillColor('blue');
                    //    $activity[$num]->SetShadow(true, 'black');
                    //}
                    // $activity[$num]->title->Align('right', 'center');  // これをやると画面が崩れるSetAlignも同じ
                    $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 10);
                    if ($u_id == $_SESSION['User_ID']) {
                        $activity[$num]->title->SetColor('red');
                    }
                    // CSIM データ設定
                    $this->setActivityCSIM($activity[$num], $request, $menu, $res[$r]);
                    $graph->Add($activity[$num]);
                    
                    $num++;     // 着手の実績表示のためインクリメント
                    //$activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                    //$activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
                    //$activity[$num]->SetFillColor('teal');
                    //$activity[$num]->SetShadow(true, 'black');
                    // CSIM データ設定
                    // $this->setActivityCSIMreal($activity[$num], $request, $menu, $res[$r]);
                    //$this->setActivityCSIMrealWin($activity[$num], $request, $menu, $res[$r]);
                    //$graph->Add($activity[$num]);
                    
                    $num++;
                }
                // 会社の休日を取得･設定
                //$j = 0; $vline = array();   // 初期化
                //for ($i=(-5); $i<37; $i++) {
                //    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                //    if (date('w',$timestamp) == 0) continue;    // 日曜日
                //    if (date('w',$timestamp) == 6) continue;    // 土曜日
                //    if (day_off($timestamp)) {  // 会社の休みをチェック
                //        $vline[$j] = new GanttVLine(date('Ymd', $timestamp));
                //        $vline[$j]->SetDayOffset(0.5);
                //        $graph->Add($vline[$j]);
                //        $j++;
                //    }
                //}
                //return $rows;
            }
        }
        $graph->Stroke($this->GraphName);
        $map_name = "myimagemap" . $map;
        $graph->StrokeCSIM($this->GraphName, $map_name, 0);
        chmod($this->GraphName, 0666);     // fileを全てrwモードにする
        $this->graph = $graph;              // Viewで使用するためgraphオブジェクトを保存
        return $rows_m;
    }
    public function computeDate($year, $month, $day, $addDays) 
    {
        $baseSec = mktime(0, 0, 0, $month, $day, $year);//基準日を秒で取得
        $addSec = $addDays * 86400;//日数×１日の秒数
        $targetSec = $baseSec + $addSec;
        return date("Ymd", $targetSec);
    }
    
    ///// Get部    GanttChartのファイル名を返す
    public function getGraphName()
    {
        return $this->GraphName;
    }
    
    ///// Get部    GanttChartをヘッダー部とボディ部に分割してリザルトにファイル名を返す
    public function getViewZoomGantt($request, $result, $menu)
    {
        // グラフファイルの最終更新日を取得して更新するか決定する
        // clearstatcache();   // ファイルステータスのキャッシュをクリア
        if ( (mktime() - filemtime($this->GraphName)) > 60) {   // グラフファイルが更新されたのが60秒前なら
            $this->getViewGanttChart($request, $result, $menu); // 更新する
        }
        // オリジナルのグラフファイルからコピーを作成して[gd-png:  fatal libpng error: IDAT: CRC error]を回避 2006/07/26 ADD
        $header_height  = 87;                   // ソース画像の見出しの高さ
        $scale = $request->get('targetScale');  // 生成される画像の倍率指定
        $tempGraphName = $this->GraphName . session_id() . '.png';
        while (!copy($this->GraphName, $tempGraphName)) {
            sleep(2);   // グラフファイルをコピーできなければ２秒ずらしてトライ
        }
        $src_id = imagecreatefrompng($tempGraphName);
        $src_x  = imagesx($src_id);
        $src_y  = imagesy($src_id);
        $src_header_y   = $header_height;
        $src_body_y     = $src_y - $header_height;
        $dst_header_x   = $src_x * $scale;
        $dst_header_y   = $header_height * $scale;
        $dst_body_x     = $src_x * $scale;
        $dst_body_y     = ($src_y - $header_height) * $scale;
        $dst_header_id  = imagecreatetruecolor($dst_header_x, $dst_header_y);
        $dst_body_id    = imagecreatetruecolor($dst_body_x, $dst_body_y);
        imagecopyresampled($dst_header_id, $src_id, 0, 0, 0, 0, $dst_header_x, $dst_header_y, $src_x, $src_header_y);
        imagecopyresampled($dst_body_id, $src_id, 0, 0, 0, $header_height, $dst_body_x, $dst_body_y, $src_x, $src_body_y);
        $dst_header_file = ('zoom/MeetingScheduleManagerZoomGanttHeader-' . $_SESSION['User_ID'] . '.png');
        $dst_body_file   = ('zoom/MeetingScheduleManagerZoomGanttBody-' . $_SESSION['User_ID'] . '.png');
        ImagePng ($dst_header_id, $dst_header_file);
        ImagePng ($dst_body_id, $dst_body_file);
        chmod($dst_header_file, 0666);      // fileを全てrwモードにする
        chmod($dst_body_file, 0666);        // fileを全てrwモードにする
        ImageDestroy ($dst_header_id);
        ImageDestroy ($dst_body_id);
        ImageDestroy ($src_id);
        if (file_exists($tempGraphName)) {  // 2007/08/21 条件追加
            unlink($tempGraphName); // 2006/07/26 ADD
        }
        $result->add('zoomGanttHeader', $dst_header_file);
        $result->add('zoomGanttbody', $dst_body_file);
        return;
    }
    public function add($request)
    {
        ///// パラメーターの分割
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // 会議件名 2005/12/27 全角変換追加
        $request->add('subject', $subject);
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $room_no    = $request->get('room_no');             // 会議室番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // 年月日のチェック  現在は Main Controllerで初期値を設定しているので必要ないが、そのまま残す。
        if ($year == '') {
            // 本日の日付に設定
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        // 開始・終了 時間の重複チェック
        if ($this->duplicateCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $room_no)) {
            $serial_no = $this->add_execute($request);
            if ($serial_no) {
                if ($mail == 't') {
                    if ($this->guideMeetingMail($request, $serial_no)) {
                        $_SESSION['s_sysmsg'] = 'メールを送信しました。';
                    } else {
                        $_SESSION['s_sysmsg'] = 'メール送信できませんでした。';
                    }
                }
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '登録できませんでした。';
            }
        }
        return false;
    }
    
    ////////// 会議スケジュールの完全削除
    public function delete($request)
    {
        ///// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // シリアル番号
        $subject    = $request->get('subject');             // 会議件名
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // 対象スケジュールの存在チェック
        $chk_sql = "
            SELECT subject FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // 指定のシリアル番号の存在チェック
            $_SESSION['s_sysmsg'] = "「{$subject}」は他の人に変更されました！";
        } else {
            if ($mail == 't') {
                if ($this->guideMeetingMail($request, $serial_no, true)) {
                    $_SESSION['s_sysmsg'] = 'キャンセルのメールを送信しました。';
                } else {
                    $_SESSION['s_sysmsg'] = 'キャンセルのメール送信ができませんでした。';
                }
            }
            $response = $this->del_execute($serial_no, $subject);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '削除できませんでした。';
            }
        }
        return false;
    }
    
    ////////// 会議スケジュールの変更
    public function edit($request)
    {
        ///// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // 連番(キーフィールド)
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // 会議件名 2005/12/27 全角変換追加
        $request->add('subject', $subject);
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $room_no    = $request->get('room_no');             // 会議室番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        $reSend     = $request->get('reSend');              // 変更時のメールの再送信Yes/No
        // 年月日のチェック
        if ($year == '') {
            // 本日の日付に設定
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        
        $query = "
            SELECT subject FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($query, $check) > 0) {  // 変更前のシリアル番号が登録されているか？
            // 開始・終了 時間の重複チェック
            if ($this->duplicateCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $room_no, $serial_no)) {
                $response = $this->edit_execute($request);
                if ($response) {
                    if ($reSend == 't' && $mail == 't') {
                        if ($this->guideMeetingMail($request, $serial_no)) {
                            $_SESSION['s_sysmsg'] = 'メールを再送信しました。';
                        } else {
                            $_SESSION['s_sysmsg'] = 'メールの再送信ができませんでした。';
                        }
                    }
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '変更できませんでした。';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = "「{$subject}」は他の人に変更されました！";
        }
        return false;
    }
    
    ////////// 会議室の登録・変更
    public function room_edit($room_no, $room_name, $duplicate)
    {
        ///// room_noの適正チェック
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT room_no, room_name, duplicate FROM meeting_room_master WHERE room_no={$room_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // 会議室の登録
            $response = $this->roomInsert($room_no, $room_name, $duplicate);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を登録しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '会議室の登録が出来ませんでした！';
            }
        } else {
            // 会議室の変更
            // データが変更されているかチェック
            if ($room_no == $res[0][0] && $room_name == $res[0][1] && $duplicate == $res[0][2]) return true;
            // 会議室の変更 実行
            $response = $this->roomUpdate($room_no, $room_name, $duplicate);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を変更しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '会議室の変更が出来ませんでした！';
            }
        }
        return false;
    }
    
    ////////// 会議室の 削除
    public function room_omit($room_no, $room_name)
    {
        ///// room_noの適正チェック
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT room_no, room_name FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} は削除対象データがありません！";
        } else {
            ///// 削除しても問題ないか過去のデータをチェック
            $query = "
                SELECT subject, to_char(str_time, 'YYYY/MM/DD') FROM meeting_schedule_header WHERE room_no={$room_no} limit 1;
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                $response = $this->roomDelete($room_no);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を削除しました。";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} を削除出来ませんでした！";
                }
            } else {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} は過去 [ {$res[0][1]} ] の日に [ {$res[0][0]} ] で使用されています。削除できません！ 無効にして下さい。";
            }
        }
        return false;
    }
    
    ////////// 会議室の 有効・無効
    public function room_activeSwitch($room_no, $room_name)
    {
        ///// room_noの適正チェック
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} の対象データがありません！";
        } else {
            // ここに last_date last_host の登録処理を入れる
            // regdate=自動登録
            $last_date = date('Y-m-d H:i:s');
            $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($active == 't') {
                $active = 'FALSE';
            } else {
                $active = 'TRUE';
            }
            // 保存用のSQL文を設定
            $save_sql = "
                SELECT active FROM meeting_room_master WHERE room_no={$room_no}
            ";
            $update_sql = "
                UPDATE meeting_room_master SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE room_no={$room_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// 出席者グループの登録・変更
    public function group_edit($group_no, $group_name, $atten, $owner)
    {
        ///// group_noの適正チェック
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT owner, group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // グループの登録
            $response = $this->groupInsert($group_no, $group_name, $atten, $owner);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を登録しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '出席者グループの登録が出来ませんでした！';
            }
        } else {
            // グループの変更
            // データが変更されているかチェック
                // $atten[]の配列があるため省略する
            // 持主が同じかチェック
            if ($res[0][0] != '000000' && $res[0][0] != $_SESSION['User_ID']) {
                $_SESSION['s_sysmsg'] = '個人のグループ登録です。 変更できません！';
                return false;
            }
            // グループの変更 実行
            $response = $this->groupUpdate($group_no, $group_name, $atten, $owner);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を変更しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '出席者グループの変更が出来ませんでした！';
            }
        }
        return false;
    }
    
    ////////// 出席者グループの 削除
    public function group_omit($group_no, $group_name)
    {
        ///// group_noの適正チェック
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} は削除対象データがありません！";
        } else {
            ///// 削除しても問題ないか過去のデータをチェックは今回は必要ない
            $response = $this->groupDelete($group_no);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を削除しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} を削除出来ませんでした！";
            }
        }
        return false;
    }
    
    ////////// 出席者グループの 有効・無効
    public function group_activeSwitch($group_no, $group_name)
    {
        ///// group_noの適正チェック
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} の対象データがありません！";
        } else {
            // ここに last_date last_host の登録処理を入れる
            // regdate=自動登録
            $last_date = date('Y-m-d H:i:s');
            $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($active == 't') {
                $active = 'FALSE';
            } else {
                $active = 'TRUE';
            }
            // 保存用のSQL文を設定
            $save_sql = "
                SELECT active FROM meeting_mail_group WHERE group_no={$group_no}
            ";
            $update_sql = "
                UPDATE meeting_mail_group SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE group_no={$group_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部
    public function getViewList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN '有効'
                    ELSE '無効'
                 END                    AS 期限         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 15
            FROM
                meeting_schedule_header AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                str_time ASC, end_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subjectの改行を<br>に置換え
        }
        $result->add_array($res);
        return $rows;
    }
    ///// 出席者の List部 attendance 複数対応
    public function getViewAttenList(&$result, $serial_no)
    {
        $query_a = "
            SELECT serial_no                            -- 00
                ,atten                                  -- 01
                ,trim(name)                             -- 02
                ,CASE
                    WHEN mail THEN '送信済'
                    ELSE '未送信'
                 END                                    -- 03
            FROM
                meeting_schedule_attendance AS meet
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                serial_no = {$serial_no}
            ORDER BY
                atten ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query_a, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 照会・印刷 List部
    public function getPrintList(&$result)
    {
        $query_p = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS 氏名         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN '有効'
                    ELSE '無効'
                 END                    AS 期限         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,to_char(end_time, 'YYYY')              -- 15
                ,to_char(end_time, 'MM')                -- 16
                ,to_char(end_time, 'DD')                -- 17
                ,CASE
                    WHEN mail THEN '送信する' ELSE '送信しない'
                 END                                    -- 18
            FROM
                meeting_schedule_header AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                room_no ASC, str_time ASC, end_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query_p, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subjectの改行を<br>に置換え
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 部署毎の社員番号と氏名を取得
    /*** userId_name 配列を返す, atten 配列 selected の設定用 ***/
    public function getViewUserName(&$userID_name, $atten)
    {
        $query = "
            SELECT uid       AS 社員番号
                , trim(name) AS 氏名
            FROM
                user_detailes
            WHERE
                retire_date IS NULL
                AND
                sid != 31
            ORDER BY
                pid DESC, sid ASC, uid ASC
            
        ";
        $userID_name = array();
        if ( ($rows=$this->getResult2($query, $userID_name)) < 1 ) {
            $_SESSION['s_sysmsg'] = '社員データの登録がありません！';
        }
        if (is_array($atten)) {
            $r = count($atten);
            for ($i=0; $i<$rows; $i++) {
                for ($j=0; $j<$r; $j++) {
                    if ($userID_name[$i][0] == $atten[$j]) {
                        $userID_name[$i][2] = ' selected';
                        break;
                    } else {
                        $userID_name[$i][2] = '';
                    }
                }
            }
        }
        return $rows;
        
    }
    
    ///// Edit 時の 1レコード分
    public function getViewEdit($serial_no, $result)
    {
        $query = "
            SELECT serial_no                    -- 00
                ,subject                        -- 01
                ,to_char(str_time, 'HH24:MI')   -- 02
                ,to_char(end_time, 'HH24:MI')   -- 03
                ,room_no                        -- 04
                ,sponsor                        -- 05
                ,atten_num                      -- 06
                ,mail                           -- 07
                ,room_name                      -- 08
                ,to_char(str_time, 'YYYY')      -- 09
                ,to_char(str_time, 'MM')        -- 10
                ,to_char(str_time, 'DD')        -- 11
            FROM
                meeting_schedule_header
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('serial_no',  $res[0][0]);
            $result->add_once('subject',    $res[0][1]);
            $result->add_once('str_time',   $res[0][2]);
            $result->add_once('end_time',   $res[0][3]);
            $result->add_once('room_no',    $res[0][4]);
            $result->add_once('sponsor',    $res[0][5]);
            $result->add_once('atten_num',  $res[0][6]);
            $result->add_once('mail',       $res[0][7]);
            $result->add_once('room_name',  $res[0][8]);
            $result->add_once('editYear',   $res[0][9]);
            $result->add_once('editMonth',  $res[0][10]);
            $result->add_once('editDay',    $res[0][11]);
        }
        return $rows;
    }
    
    ///// List時の 表題(キャプション)の生成
    public function get_caption($switch, $year, $month, $day)
    {
        switch ($switch) {
        case 'List':
            // $caption = '会議(打合せ) 一覧';
            $caption = '〜';
            $caption = sprintf("%04d年%02d月%02d日{$caption}", $year, $month, $day);
            break;
        case 'Apend':
            $caption = '会議(打合せ)の追加';
            break;
        case 'Edit':
            $caption = '会議(打合せ)の編集';
            break;
        default:
            $caption = '';
        }
        return $caption;
        
    }
    
    ///// List時の 登録データがない場合のメッセージ生成
    public function get_noDataMessage($year, $month, $day)
    {
        if ($year != '') {
            if (sprintf('%04d%02d%02d', $year, $month, $day) < date('Ymd')) {
                $noDataMessage = '登録がありません。';  // 過去の場合
            } else {
                $noDataMessage = '予定がありません。';  // 未来の場合
            }
        } else {
            // 本日の場合
            $noDataMessage = '予定がありません。';
        }
        return $noDataMessage;
        
    }
    
    ///// 会議室の List部
    public function getViewRoomList(&$result)
    {
        $query = "
            SELECT room_no                              -- 00
                ,room_name                              -- 01
                ,CASE
                    WHEN duplicate THEN 'する'
                    ELSE 'しない'
                 END                    AS 重複         -- 02
                ,CASE
                    WHEN active THEN '有効'
                    ELSE '無効'
                 END                    AS 有効無効     -- 03
                ,to_char(regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 05
            FROM
                meeting_room_master
            ORDER BY
                room_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 会議室の <select>表示用 List部
    public function getActiveRoomList(&$result)
    {
        $query = "
            SELECT room_no                              -- 00
                ,room_name                              -- 01
            FROM
                meeting_room_master
            WHERE
                active IS TRUE
            ORDER BY
                room_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 出席者グループの List部
    public function getViewGroupList(&$result)
    {
        $query = "
            SELECT group_no                             -- 00
                ,group_name                             -- 01
                ,owner                                  -- 02
                ,CASE
                    WHEN active THEN '有効'
                    ELSE '無効'
                 END                    AS 有効無効     -- 03
                ,to_char(mail.regdate, 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(mail.last_date, 'YY/MM/DD HH24:MI')
                                                        -- 05
                ,trim(name)                             -- 06
            FROM
                meeting_mail_group AS mail
            LEFT OUTER JOIN
                user_detailes ON (owner=uid)
            GROUP BY
                group_no, group_name, owner, active, mail.regdate, mail.last_date, name
            ORDER BY
                group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 出席者グループの １グループ分 Attendance List部
    public function getGroupAttenList(&$result, $group_no)
    {
        $query = "
            SELECT
                 trim(name)                             -- 00
                ,atten                                  -- 01
            FROM
                meeting_mail_group
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                group_no={$group_no}
            ORDER BY
                pid DESC, sid ASC, uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 出席者グループの有効なリスト Active List部
    // JSgroup_name=グループ名の１次元配列, JSgroup_member=グループ名に対応した出席者の２次元配列, 戻り値=有効件数
    // owner='000000'は共有グループ, 指定がある場合は個人のグループ
    public function getActiveGroupList(&$JSgroup_name, &$JSgroup_member, $uid)
    {
        // 初期化
        $JSgroup_name = array();
        $JSgroup_member = array();
        // グループ名の配列の取得
        $query = "
            SELECT group_name                             -- 00
                 , group_no                               -- 01
            FROM
                meeting_mail_group
            WHERE
                active AND (owner='000000' OR owner='{$uid}')
            GROUP BY
                group_no, group_name
            ORDER BY
                group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            return false;
        }
        for ($i=0; $i<$rows; $i++) {
            $JSgroup_name[$i] = $res[$i][0];
            // グループメンバーの2次元配列の取得
            $query = "
                SELECT
                     atten                             -- 00
                FROM
                    meeting_mail_group
                LEFT OUTER JOIN
                    user_detailes ON (atten=uid)
                WHERE
                    group_no={$res[$i][1]}
                ORDER BY
                    pid DESC, sid ASC, uid ASC
            ";
            $resMem = array();
            if ( ($rowsMem=$this->getResult2($query, $resMem)) < 1 ) {
                return false;
            }
            for ($j=0; $j<$rowsMem; $j++) {
                $JSgroup_member[$i][$j] = $resMem[$j][0];
            }
        }
        return $rows;
    }
    
    ///// Set部  Activity の CSIM設定 showMenuの内容で文字コードを切替
    protected function setActivityCSIM($activity, $request, $menu, $res)
    {
        $subject   = str_ireplace('<br>', '　', $res[1]);    // 会議名
        //$subject   = $res[1];    // 会議名
        $str_time  = $res[2];    // 開始時間
        $end_time  = $res[3];    // 終了時間
        $room_name = $res[4];    // 会議場所
        $sponsor   = $res[6];    // 主催者
        $atten_num = $res[7];    // 参加者
        $kanryou  = $res[6];    // 完了日
        $bikou    = $res[7];    // 備考
        $plan_pcs = $res[8];    // 計画数
        $cut_pcs  = $res[9];    // 打切り数
        $end_pcs  = $res[10];   // 完成数
        $ritu     = $res[11];   // 出庫率
        $targ1 = "JavaScript:alert('会議名：{$subject}\\n\\n開始時間：{$str_time}\\n\\n終了時間：{$end_time}\\n\\n会議場所：{$room_name}\\n\\n主催者：{$sponsor}\\n\\n参加者：{$atten_num}名')";
        $alts1 = "会議名：{$subject}　開始時間：{$str_time}　終了時間：{$end_time}　会議場所：{$room_name}";
        //$targ2 = "{$menu->out_action('引当構成表')}?plan_no=".urlencode($plan_no)."&material=1&id={$menu->out_useNotCache()}";
        //$alts2 = 'この計画番号の引当部品構成表を表示します。';
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
            //$targ2 = mb_convert_encoding($targ2, 'UTF-8', 'EUC-JP');
            //$alts2 = mb_convert_encoding($alts2, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        //$activity->title->SetCSIMTarget($targ2, $alts2);
        //if ($request->get('material_plan_no') == $plan_no) {
        //    $activity->title->SetColor('red');  // マーカー用
        //}
    }
    
    ///// Set部  実績チャート用 Activity の CSIM設定 showMenuの内容で文字コードを切替
    protected function setActivityCSIMreal($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // 計画番号
        $assy_name= $res[2];    // 製品名
        $targ1 = "{$menu->out_action('実績工数照会')}?showMenu=CondForm&targetPlanNo=" . urlencode($plan_no);
        $alts1 = "製品名：{$assy_name}　の登録工数と実績工数を照会します。";
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ1, $alts1);
    }
    
    ///// Set部  実績チャート用 Activity の CSIM設定 showMenuの内容で文字コードを切替 Window版
    protected function setActivityCSIMrealWin($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // 計画番号
        $assy_name= $res[2];    // 製品名
        $targ1 = "javascript:AssemblyScheduleShow.win_open('{$menu->out_action('実績工数照会')}?targetPlanNo=" . urlencode($plan_no) . "&noMenu=yes', 900, 600)";
        $alts1 = "製品名：{$assy_name}　の登録工数と実績工数を照会します。";
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ1, $alts1);
    }
    ////////// 会議室のroom_noの適正をチェックしメッセージ＋結果(true=OK,false=NG)を返す
    protected function checkRoomNo($room_no)
    {
        ///// room_noの適正チェック
        if (is_numeric($room_no)) {
            if ($room_no >= 1 && $room_no <= 32000) {   // int2に対応
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "会議室の番号 {$room_no} は範囲外です！ 1〜32000までです。";
            }
        } else {
            $_SESSION['s_sysmsg'] = "会議室の番号 {$room_no} は数字以外が含まれています。";
        }
        return false;
    }
    
    ////////// 会議室のroom_noの適正をチェックしメッセージ＋結果(true=OK,false=NG)を返す
    protected function checkGroupNo($group_no)
    {
        ///// group_noの適正チェック
        if (is_numeric($group_no)) {
            if ($group_no >= 1 && $group_no <= 999) {   // int2 以内が実際の範囲
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "出席者のグループ番号 {$group_no} は範囲外です！ 1〜999までです。";
            }
        } else {
            $_SESSION['s_sysmsg'] = "出席者のグループ番号 {$group_no} は数字以外が含まれています。";
        }
        return false;
    }
    
    ////////// 会議室の登録 (実行部)
    protected function roomInsert($room_no, $room_name, $duplicate)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // $duplicate は 't' 又は 'f' なので そのまま使う
        $insert_sql = "
            INSERT INTO meeting_room_master
            (room_no, room_name, duplicate, active, last_date, last_host)
            VALUES
            ('$room_no', '$room_name', '$duplicate', TRUE, '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 会議室の変更 (実行部)
    protected function roomUpdate($room_no, $room_name, $duplicate)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_room_master WHERE room_no={$room_no}
        ";
        // $duplicate は 't' 又は 'f' なので そのまま使う
        $update_sql = "
            UPDATE meeting_room_master SET
            room_no={$room_no}, room_name='{$room_name}', duplicate='{$duplicate}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE room_no={$room_no}
        "; 
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 会議室の削除 (実行部)
    protected function roomDelete($room_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_room_master WHERE room_no={$room_no}
        ";
        // 削除用SQL文を設定
        $delete_sql = "
            DELETE FROM meeting_room_master WHERE room_no={$room_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 会議(打合せ)の案内を email で出だす
    protected function guideMeetingMail($request, $serial_no, $cancel=false)
    {
        ///// パラメーターの分割
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = $request->get('subject');             // 会議件名
        $subject2   = str_replace("\r\n", "\r\n　　　　　　", $subject);  // subjectの改行をスペースを付加したものに置換え
        $subject3   = str_replace("\r\n", '　', $subject);  // subjectの改行をスペースに置換え
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $atten_num  = count($atten);                        // 出席者数
        $room_no    = $request->get('room_no');             // 会議室番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        ///// 曜日を取得する 2006/07/24 ADD
        $week = array('日', '月', '火', '水', '木', '金', '土');
        $dayWeek = $week[date('w', mktime(0, 0, 0, $month, $day, $year))];
        // 主催者の名前を取得
        if (!$this->getSponsorName($sponsor, $res)) {
            $_SESSION['s_sysmsg'] = "メール案内で主催者の名前が見つかりません！ [ $sponsor ]";
        } else {
            $sponsor_name = $res[0][0];
            $sponsor_addr = $res[0][1];
            // 会議室名の取得
            $room_name = $this->getRoomName($room_no);
            // 出席者の名前取得 (引数３個は全て配列)
            $this->getAttendanceName($atten, $atten_name, $flag);
            // 出席者のメールアドレスの取得とメール送信
            for ($i=0; $i<$atten_num; $i++) {
                if ($flag[$i] == 'NG') continue;
                // 出席者のメールアドレス取得
                if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                    continue;
                }
                $to_addres = $atten_addr;
                $message  = "この案内は {$sponsor_name} さんが出席者にメール案内を出す設定にしたため送信されたものです。\n\n";
                $message .= "{$subject}\n\n";
                if ($cancel) {
                    $message .= "下記の会議(打合せ)が{$this->getUserName()}さんによりキャンセル(削除)されましたので、ご連絡致します。\n\n";
                } else {
                    $message .= "下記の日時で行われますので、ご出席お願い致します。\n\n";
                }
                $message .= "                               記\n\n";
                $message .= "１. 開催日：{$year}年 {$month}月 {$day}日({$dayWeek})\n\n";
                $message .= "２. 時　間：{$str_time} 〜 {$end_time}\n\n";
                $message .= "３. 場　所：{$room_name}\n\n";
                $message .= "４. 主催者：{$sponsor_name}\n\n";
                $message .= "５. 出席者：{$this->getAttendanceNameList($atten, $atten_name)}";
                $message .= "\n\n";
                $message .= "６. 会議名：{$subject2}\n\n";
                $message .= "以上、宜しくお願い致します。\n\n";
                $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
                $attenSubject = '宛先： ' . $atten_name[$i] . ' 様　 ' . $subject3;
                if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                    // 出席者へのメール送信履歴を保存
                    $this->setAttendanceMailHistory($serial_no, $atten[$i]);
                }
                ///// Debug
                if ($cancel) {
                    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                }
            }
            return true;
        }
        return false;
    }
    
    ////////// 出席者グループの登録 (実行部)
    protected function groupInsert($group_no, $group_name, $atten, $owner)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $insert_sql = '';
        $cnt = count($atten);
        for ($i=0; $i<$cnt; $i++) {
            $insert_sql .= "
                INSERT INTO meeting_mail_group
                (group_no, group_name, atten, owner, active, last_date, last_host)
                VALUES
                ('$group_no', '$group_name', '{$atten[$i]}', '$owner', TRUE, '$last_date', '$last_host')
                ;
            ";
        }
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 出席者グループの変更 (実行部)
    protected function groupUpdate($group_no, $group_name, $atten, $owner)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $update_sql = '';
        $update_sql .= "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
            ;
        "; 
        $cnt = count($atten);
        ///// 有効・無効の active は変更時に 常に有効となる
        for ($i=0; $i<$cnt; $i++) {
            $update_sql .= "
                INSERT INTO meeting_mail_group
                (group_no, group_name, atten, owner, active, last_date, last_host)
                VALUES
                ('$group_no', '$group_name', '{$atten[$i]}', '$owner', TRUE, '$last_date', '$last_host')
                ;
            ";
        }
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 出席者グループの削除 (実行部)
    protected function groupDelete($group_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        // 削除用SQL文を設定
        $delete_sql = "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 会議の重複チェック(会議室の重複チェック指定がされているものだけ)
    // string $str_timestamp=開始時間(DBのTIMESTAMP型), string $end_time=終了時間(DBのTIMESTAMP型),
    // int $room=会議室番号, [int $serial_no=変更時の元データの連番]
    private function duplicateCheck($str_timestamp, $end_timestamp, $room_no, $serial_no=0)
    {
        // データ変更時の元データの除外指定
        $deselect = "AND serial_no != {$serial_no}";
        // 会議室マスターで重複チェックになっているか？
        $query = "
            SELECT duplicate FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getUniResult($query, $duplicate) <= 0) {
            return true;
        } else {
            if ($duplicate == 'f') return true;
        }
        // 開始時間の重複チェック
        $chk_sql1 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$str_timestamp}'
            AND end_time > '{$str_timestamp}'
            AND room_no = {$room_no}
            {$deselect}
            limit 1
        ";
        // 終了時間の重複チェック
        $chk_sql2 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$end_timestamp}'
            AND end_time > '{$end_timestamp}'
            AND room_no = {$room_no}
            {$deselect}
            limit 1
        ";
        // 全体の重複チェック
        $chk_sql3 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time >= '{$str_timestamp}'
            AND end_time <= '{$end_timestamp}'
            AND room_no = {$room_no}
            {$deselect}
            limit 1
        ";
        if ($this->getUniResult($chk_sql1, $check) > 0) {           // 開始時間の重複チェック
            $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
            $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
            $_SESSION['s_sysmsg'] = "開始時間が　「{$check}」　と重複しています。";
            return false;
        } elseif ($this->getUniResult($chk_sql2, $check) > 0) {     // 終了時間の重複チェック
            $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
            $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
            $_SESSION['s_sysmsg'] = "終了時間が　「{$check}」　と重複しています。";
            return false;
        } elseif ($this->getUniResult($chk_sql3, $check) > 0) {     // 全体の重複チェック
            $check = str_replace("\r", '　', $check);               // 件名の改行をスペースへ変換
            $check = str_replace("\n", '　', $check);               // 件名の改行をスペースへ変換
            $_SESSION['s_sysmsg'] = "「{$check}」　と重複しています。";
            return false;
        } else {
            return true;    // 重複なし
        }
    }
    
    ////////// 会議スケジュールの実行部 追加
    private function add_execute($request)
    {
        ///// パラメーターの分割
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = $request->get('subject');             // 会議件名
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $room_no    = $request->get('room_no');             // 会議室番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // メール送信 Y/N を boolean型に変換
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        // 出席者の人数を取得
        $atten_num = count($atten);
        $insert_qry = "
            INSERT INTO meeting_schedule_header
            (subject, str_time, end_time, room_no, sponsor, atten_num, mail, last_date, last_host)
            VALUES
            ('$subject', '{$year}-{$month}-{$day} {$str_time}', '{$year}-{$month}-{$day} {$end_time}', $room_no, '$sponsor', $atten_num, $mail, '$last_date', '$last_host')
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $insert_qry .= "
                INSERT INTO meeting_schedule_attendance
                (serial_no, atten, mail)
                VALUES
                ((SELECT max(serial_no) FROM meeting_schedule_header), '{$atten[$i]}', FALSE)
                ;
            ";
        }
        if ($this->execute_Insert($insert_qry)) {
            $query = "SELECT max(serial_no) FROM meeting_schedule_header";
            $serial_no = false;     // 初期値
            $this->getUniResult($query, $serial_no);
            return $serial_no;      // 登録したシリアル番号を返す
        } else {
            return false;
        }
    }
    
    ////////// 会議スケジュールの実行部 削除(完全)
    private function del_execute($serial_no, $subject)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        $delete_sql = "
            DELETE FROM meeting_schedule_header WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM meeting_schedule_attendance WHERE serial_no={$serial_no}
            ;
        ";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 会議スケジュールの実行部 変更
    private function edit_execute($request)
    {
        ///// パラメーターの分割
        $serial_no  = $request->get('serial_no');           // 連番(キーフィールド)
        $year       = $request->get('yearReg');             // 会議予定の年４桁
        $month      = $request->get('monthReg');            // 会議予定の月２桁
        $day        = $request->get('dayReg');              // 会議予定の日２桁
        $subject    = $request->get('subject');             // 会議件名
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列)
        $room_no    = $request->get('room_no');             // 会議室番号
        $mail       = $request->get('mail');                // メールの送信 Y/N
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT * FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        // 出席者の人数を取得
        $atten_num = count($atten);
        $update_sql = "
            UPDATE meeting_schedule_header SET
            subject='{$subject}', str_time='{$year}-{$month}-{$day} {$str_time}', end_time='{$year}-{$month}-{$day} {$end_time}',
            room_no={$room_no}, sponsor='{$sponsor}', atten_num='{$atten_num}', mail='{$mail}',
            last_date='{$last_date}', last_host='{$last_host}'
            where serial_no={$serial_no}
            ;
        "; 
        $update_sql .= "
            DELETE FROM meeting_schedule_attendance WHERE serial_no={$serial_no}
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $update_sql .= "
                INSERT INTO meeting_schedule_attendance
                (serial_no, atten, mail)
                VALUES
                ({$serial_no}, '{$atten[$i]}', FALSE)
                ;
            ";
        }
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 主催者の名前を取得
    private function getSponsorName($sponsor, &$res)
    {
        $query = "
            SELECT trim(name), trim(mailaddr)
            FROM
                user_detailes
            LEFT OUTER JOIN
                user_master USING(uid)
            WHERE
                uid = '{$sponsor}'
                AND
                retire_date IS NULL     -- 退職していない
                AND
                sid != 31               -- 出向していない
        ";
        $res = array();     // 初期化
        if ($this->getResult2($query, $res) < 1) {
            return false;
        } else {
            return true;
        }
    }
    
    ////////// 会議室名の取得
    private function getRoomName($room_no)
    {
        $query = "
            SELECT trim(room_name) FROM meeting_room_master WHERE room_no={$room_no}
        ";
        $room_name = '';    // 初期化
        $this->getUniResult($query, $room_name);
        return $room_name;
    }
    
    ////////// 出席者の名前取得
    private function getAttendanceName($atten, &$atten_name, &$flag)
    {
        $atten_num = count($atten);
        $atten_name = array();
        $flag = array();
        for ($i=0; $i<$atten_num; $i++) {
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid = '{$atten[$i]}' AND retire_date IS NULL AND sid != 31
            ";
            $atten_name[$i] = '';
            if ($this->getUniResult($query, $atten_name[$i]) < 1) {
                $_SESSION['s_sysmsg'] .= "メール案内で出席者の名前が見つかりません！ [ {$atten[$i]} ]";
                $flag[$i] = 'NG';
            } else {
                $flag[$i] = 'OK';
            }
        }
    }
    
    ////////// 出席者のメールアドレス取得
    private function getAttendanceAddr($atten)
    {
        $query = "
            SELECT trim(mailaddr) FROM user_master WHERE uid = '{$atten}'
        ";
        $atten_addr = '';
        if ($this->getUniResult($query, $atten_addr) < 1) {
            $_SESSION['s_sysmsg'] .= "メール案内で出席者のメールアドレスが見つかりません！ [ {$atten} ]";
        }
        return $atten_addr;
    }
    
    ////////// 出席者の名前をメールに載せるため文字列で一括取得
    private function getAttendanceNameList($atten, $atten_name)
    {
        $atten_num = count($atten);
        $message = '';
        for ($j=0; $j<$atten_num; $j++) {
            if (!$atten_name[$j]) continue;
            if ($j == 0) {
                $message .= "{$atten_name[$j]}";
            } else {
                $message .= ", {$atten_name[$j]}";
            }
        }
        return $message;
    }
    
    ////////// 出席者へのメール送信履歴を保存
    private function setAttendanceMailHistory($serial_no, $atten)
    {
        $update_sql = "
            UPDATE meeting_schedule_attendance SET
                mail=TRUE
            WHERE
                serial_no={$serial_no} AND atten='{$atten}'
        ";
        $this->execute_Update($update_sql);
    }
    
    ////////// クライアントの名前取得
    private function getUserName()
    {
        if (!$_SESSION['User_ID']) {
            return gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        $query = "
            SELECT trim(name) FROM user_detailes WHERE uid = '{$_SESSION['User_ID']}' AND retire_date IS NULL AND sid != 31
        ";
        if ($this->getUniResult($query, $userName) < 1) {
            return gethostbyaddr($_SERVER['REMOTE_ADDR']);
        } else {
            return $userName;
        }
    }
    
} // Class AssemblyScheduleShow_Model End

?>
