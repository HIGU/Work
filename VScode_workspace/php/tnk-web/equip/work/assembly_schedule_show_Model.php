<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール 照会         MVC Model 部           //
// Copyright (C) 2006-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/23 Created   assembly_schedule_show_Model.php                    //
// 2006/02/03 getViewGanttChart($request, $result, $menu)引数$menuを追加    //
//            title クリックで引当構成表の照会を追加したため                //
// 2006/02/04 chmod($this->graph_name, 0666);を追加                         //
// 2006/02/06 引当部品のチェックをし戻った場合にマーカーとして赤色にする    //
//            getViewGanttChart()プロパティーに上記機能を追加               //
// 2006/02/07 GanttChartに出庫率を追加 getPickingRatio()メソッドを追加      //
//            最終的にはSubQueryで実現 formatPickingRatio()メソッド追加     //
// 2006/02/08 指定日までの時、月初から指定日まで → 前月1日から指定日まで   //
//            出庫率計算のSQL文を division by zero 対応に変更 (3箇所)       //
//            GanttChartで完了日・着手日が対象月と違う場合captionを表示     //
//            Ajaxのための文字コード変換メソッド setActivityCSIM() を追加   //
// 2006/02/24 現場用モニター設置により２３行表示条件を追加                  //
// 2006/03/02 上記をデフォルトと同じ１５行表示へ変更(現場の解像度を変えた)  //
// 2006/03/03 実績・登録 工数照会用にsetActivityCSIMreal()メソッドを追加    //
//            完成済の日程表を照会できるように機能追加                      //
// 2006/03/05 $graph->img->SetMargin(15,17,10,15)を追加 25→10へ変更した    //
// 2006/03/15 登録工数を表示追加 ＆ 実績工数と登録工数をWindow表示へ変更    //
// 2006/03/17 assembly_time_sum(sche.parts_no, sche.chaku)→sche.kanryou    //
// 2006/05/12 出庫率を小数点１位までgetViewPlanList,formatPickingRatio 変更 //
// 2006/06/16 ガントチャートのみを別ウィンドウで開くgetViewZoomGantt()を追加//
// 2006/06/19 グラフファイルの最終更新日を取得して60秒経過していれば更新する//
// 2006/06/22 上記の処理のためにファイル名を__construct で初期化            //
// 2006/07/08 ガントチャートの縦サイズ指定を0→-1へ  表示行数を100行までに  //
// 2006/07/26 getViewZoomGantt()メソッドに libpng error: 回避ロジックを追加 //
// 2006/10/19 リクエストtargetLineMethod(1=個別選択,2=複数選択)追加による   //
//            処理とライン番号表示メソッド追加 setLineWhere()メソッドを追加 //
// 2006/11/01 getViewZoomGantt()メソッドに倍率指定を追加 targetScale        //
// 2006/11/07 ラインの複数選択時にグラフタイトルも複数表示getLineTitle()追加//
// 2007/02/01 出庫率の小数部を1桁→2桁へ Uround(3ヶ所)formatPickingRatio変更//
// 2007/08/21 getViewZoomGantt()にcopy失敗時のsleep(2)を追加libpngエラー回避//
// 2013/05/20 日程計画一覧表示時にデータが当月作成or当月変更されたものの    //
//            完了日を赤くする為の関数を追加 plan_add_check()          大谷 //
// 2013/05/23 ガントチャート側も同じように色を変えるように変更         大谷 //
// 2014/05/23 plan_add_check()を分割 追加はplan_add_check()で赤表示         //
//            変更はplan_chage_check()で青表示に変更(カプラ組立依頼)   大谷 //
// 2015/05/20 機工対応の為、検索にTを追加                              大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../ComTableMntClass.php');    // TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
* 組立日程計画表(AS/400版)スケジュール 照会用 MVCのModel部の base class 基底クラスの定義 *
*****************************************************************************************/
class AssemblyScheduleShow_Model extends ComTableMnt
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
        $this->GraphName = "graph/AssemblyScheduleGanttChart-{$_SESSION['User_ID']}.png";
        
        switch ($request->get('showMenu')) {
        case 'GanttChart':
            // 基本WHERE区の設定
            $where = $this->InitWherePlanList($request);
            if ($request->get('CTM_pageRec') > 100) {
                $request->add('CTM_pageRec', 100);
                $_SESSION['s_sysmsg'] = 'ガントチャートでは１００行までです。\n\n１００行に調整しました。';
            }
            break;
        case 'PlanList':
        default:
            // 基本WHERE区の設定
            $where = $this->InitWherePlanList($request);
        }
        $sql_sum = "
            SELECT count(*) FROM assembly_schedule $where
        ";
        ///// SQL文のWHERE区をPropertiesに登録
        $this->where  = $where;
        ///// log file の指定
        $log_file = 'assembly_schedule_show.log';
        ///// 1ページのレコード数をデフォルト値の20→15へ変更
        if ($_SERVER['REMOTE_ADDR'] != '10.1.3.67') {
            $pageRec = 15;
        } else {
            $pageRec = 15;  // scheduler-1(現場モニター用）23→15へ変更(2006/03/02)
        }
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, $log_file, $pageRec);
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    組立日程計画 一覧表
    public function getViewPlanList($request, $result)
    {
        switch ($request->get('targetDateItem')) {
        case 'chaku':
            $order = 'chaku ASC, parts_no ASC';
            break;
        case 'syuka':
            $order = 'syuka ASC, parts_no ASC';
            break;
        case 'kanryou':
        default:
            $order = 'kanryou ASC, parts_no ASC';
        }
        $query = "
            SELECT
                 plan_no        AS 計画番号         -- 00
                ,parts_no       AS 製品番号         -- 01
                ,substr(midsc, 1, 20)
                                AS 製品名           -- 02
                ,plan - cut_plan - kansei
                                AS 計画残数         -- 03
                ,syuka          AS 集荷日           -- 04
                ,chaku          AS 着手日           -- 05
                ,kanryou        AS 完了日           -- 06
                ,CASE
                    WHEN trim(note15) = '' THEN '&nbsp;'
                    ELSE note15
                 END            AS 備考             -- 07
                -----------------------------リストは上記まで
                ,plan           AS 計画数           -- 08
                ,cut_plan       AS 打切り数         -- 09
                ,kansei         AS 完成数           -- 10
                ,(
                    SELECT 
                        CASE
                            WHEN sum(allo_qt) = 0 THEN 0    -- division by zero 対応
                            ELSE
                            Uround(
                                CAST(sum(sum_qt) AS numeric(11, 2)) / CAST(sum(allo_qt) AS numeric(11, 2)) * 100, 2
                            )
                        END
                    FROM allocated_parts WHERE plan_no=sche.plan_no AND assy_no=sche.parts_no
                 )              AS 出庫率           -- 11
                ,(SELECT assembly_time_sum(sche.parts_no, sche.kanryou))
                                AS 登録工数         -- 12
            FROM
                assembly_schedule AS sche
            LEFT OUTER JOIN
                miitem ON (parts_no=mipn)
            {$this->where}
            ORDER BY
                {$order}
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
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
    public function getViewGanttChart($request, $result, $menu)
    {
        // 対象データを取得(PlanListのデータを使う)
        $rows = $this->getViewPlanList($request, $result);
        if ($rows <= 0) return $rows;   // データが無ければChartは作らない
        // GanttChartの作成
        $res = $result->get_array();
        
        $graph = new GanttGraph(990, -1, 'auto');   // -1=自動, 0=でも問題は無かった
        $graph->SetShadow();
        $graph->img->SetMargin(15, 17, 10, 15);     // defaultの 25→10 へ変更
        // グラフ用タイトル取得
        $showLine = $this->getLineTitle($request);
        if ($request->get('targetSeiKubun') == '1')
            $sei_kubun = '標準品';
        elseif ($request->get('targetSeiKubun') == '3')
            $sei_kubun = '特注品';
        else
            $sei_kubun = '全て';
        // Add title and subtitle
        $graph->title->Set(mb_convert_encoding("組立日程計画 (ガントチャート)  指定ライン：{$showLine}製品区分：{$sei_kubun}", 'UTF-8'));
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);
            // $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 10);
            // $graph->subtitle->Set(mb_convert_encoding("指定ライン：{$showLine} 製品区分：{$sei_kubun}", 'UTF-8'));
        // Show day, week and month scale
        $graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
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
        $item1 = mb_convert_encoding("当月 追加:赤 変更:青\n計画No. 製品No. 数量\n製　　品　　名", 'UTF-8');
        $item2 = mb_convert_encoding("\n出庫率\n工　数", 'UTF-8');
        $graph->scale->actinfo->SetColTitles(array($item1, $item2));
        $graph->scale->actinfo->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->scale->actinfo->vgrid->SetColor('gray');
        $graph->scale->actinfo->SetBackgroundColor('darkgreen@0.6');
        $graph->scale->actinfo->SetColor('darkgray');
        
        // レンジを固定する 開始日と終了日を設定
        $year  = substr($request->get('targetDate'), 0, 4);
        $month = substr($request->get('targetDate'), 4, 2);
        $lastDay = last_day($year, $month);
        $graph->scale->SetRange($year.$month.'01', $year.$month.$lastDay);
        // Make the day scale
        $graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE4);
        $graph->scale->day->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Instead of week number show the date for the first day in the week
        // on the week scale
        $graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY2);
        // Make the week scale font smaller than the default
        // $graph->scale->week->SetFont(FF_FONT0);  // これはオリジナル
        $graph->scale->week->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Use the short name of the month together with a 2 digit year
        // on the month scale
        $graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
        $graph->scale->month->SetFont(FF_GOTHIC, FS_NORMAL, 14);
        $graph->scale->month->SetFontColor('white');
        $graph->scale->month->SetBackgroundColor('blue');
        // 表示日付の最小値を取得
        $graph->scale->AdjustStartEndDay();     // マニュアルに無いメソッド・プロパティを使用
        $viewStartDate = date('Ymd', $graph->scale->iStartDate);
        // CSIM用データ初期化
        $targ = array();
        $alts = array();
        $num = 0;   // グラフ位置の配列(着手を追加による)
        for ($r=0; $r<$rows; $r++) {
            $plan_no  = $res[$r][0];    // 計画番号
            $assy_no  = $res[$r][1];    // 製品番号
            $assy_name= $res[$r][2];    // 製品名
            $plan_zan = $res[$r][3];    // 計画残数
            $syuka    = $res[$r][4];    // 集荷日
            $chaku    = $res[$r][5];    // 着手日
            $kanryou  = $res[$r][6];    // 完了日
            $bikou    = $res[$r][7];    // 備考
            $plan_pcs = $res[$r][8];    // 計画数
            $cut_pcs  = $res[$r][9];    // 打切り数
            $end_pcs  = $res[$r][10];   // 完成数
            $ritu     = $res[$r][11];   // 出庫率
            $kousu    = $res[$r][12];   // 登録工数
            $assy_name = mb_convert_kana($assy_name, 'k');
            $item  = mb_convert_encoding("{$plan_no} {$assy_no} {$plan_zan}\n{$assy_name}", 'UTF-8');
                                    // ($row, $title, $startdate, $enddate)
            // 出庫率を求める %付の文字列で返す
                    // $ritu = $this->getPickingRatio($plan_no, $assy_no);
            $ritu = $this->formatPickingRatio($ritu);
            // 工数の値を調べる
            if ($kousu) $kousu = (' ' . $kousu); else $kousu = '未登録';
            // 出庫率と登録工数を連結させる
            $ritu_kousu = mb_convert_encoding(" {$ritu}\n{$kousu}", 'UTF-8');
            // 完了月・着手月が違う場合はキャプションを表示
            if (substr($kanryou, 4, 2) != $month && substr($chaku, 4, 2) != $month) {
                $activity[$num] = new GanttBar($num, array($item, $ritu_kousu), $viewStartDate, $viewStartDate);
                if ($this->plan_add_check($plan_no)) {
                    $activity[$num]->title->SetColor('red');
                } elseif ($this->plan_change_check($plan_no)) {
                    $activity[$num]->title->SetColor('blue');
                }
                $activity[$num]->caption->Set(mb_convert_encoding("{$chaku}～{$kanryou}", 'UTF-8'));
                $activity[$num]->caption->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                $activity[$num]->caption->SetColor('blue');
                $activity[$num]->SetPattern(BAND_RDIAG, 'white');
                $activity[$num]->SetFillColor('red');
            } else {
                $activity[$num] = new GanttBar($num, array($item, $ritu_kousu), $chaku, $kanryou);
                // Yellow diagonal line pattern on a red background
                if ($this->plan_add_check($plan_no)) {
                    $activity[$num]->title->SetColor('red');
                } elseif ($this->plan_change_check($plan_no)) {
                    $activity[$num]->title->SetColor('blue');
                }
                $activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
                $activity[$num]->SetFillColor('blue');
                $activity[$num]->SetShadow(true, 'black');
            }
            // $activity[$num]->title->Align('right', 'center');  // これをやると画面が崩れるSetAlignも同じ
            $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 10);
            // CSIM データ設定
            $this->setActivityCSIM($activity[$num], $request, $menu, $res[$r]);
            $graph->Add($activity[$num]);
            
            $num++;     // 着手の実績表示のためインクリメント
            if ($this->plan_chaku_check($plan_no, $strDate, $endDate)) {
                if ($request->get('targetCompleteFlag') == 'no') {
                    $item2 = mb_convert_encoding("　　　着手済", 'UTF-8');
                } else {
                    $item2 = mb_convert_encoding("　　　実績値", 'UTF-8');
                }
                $activity[$num] = new GanttBar($num, array($item2, '  -'), $strDate, $endDate);
                $activity[$num]->title->SetColor('teal');
            } else {
                if ($request->get('targetCompleteFlag') == 'no') {
                    $item2 = mb_convert_encoding("　　　未着手", 'UTF-8');
                } else {
                    $item2 = mb_convert_encoding("　　　未入力", 'UTF-8');
                }
                $activity[$num] = new GanttBar($num, array($item2, '  -'), $strDate, $endDate);
                $activity[$num]->title->SetColor('gray');
            }
            $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
            $activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
            $activity[$num]->SetFillColor('teal');
            $activity[$num]->SetShadow(true, 'black');
            // CSIM データ設定
            // $this->setActivityCSIMreal($activity[$num], $request, $menu, $res[$r]);
            $this->setActivityCSIMrealWin($activity[$num], $request, $menu, $res[$r]);
            $graph->Add($activity[$num]);
            
            $num++;
        }
        // 会社の休日を取得･設定
        $j = 0; $vline = array();   // 初期化
        for ($i=(-5); $i<37; $i++) {
            $timestamp = mktime(0, 0, 0, $month, $i, $year);
            if (date('w',$timestamp) == 0) continue;    // 日曜日
            if (date('w',$timestamp) == 6) continue;    // 土曜日
            if (day_off($timestamp)) {  // 会社の休みをチェック
                $vline[$j] = new GanttVLine(date('Ymd', $timestamp));
                $vline[$j]->SetDayOffset(0.5);
                $graph->Add($vline[$j]);
                $j++;
            }
        }
        $graph->Stroke($this->GraphName);
        chmod($this->GraphName, 0666);     // fileを全てrwモードにする
        $this->graph = $graph;              // Viewで使用するためgraphオブジェクトを保存
        return $rows;
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
        $dst_header_file = ('zoom/AssemblyScheduleZoomGanttHeader-' . $_SESSION['User_ID'] . '.png');
        $dst_body_file   = ('zoom/AssemblyScheduleZoomGanttBody-' . $_SESSION['User_ID'] . '.png');
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
    
    ///// List部    組立ライン $this->where(条件)内での 一覧表 (ページコントロールなし)
    public function getViewLineList(&$result)
    {
        $query = "
            SELECT
                line_no            AS ライン番号           -- 00
            FROM
                assembly_schedule
            {$this->whereNoLine}
                AND trim(line_no) != ''
            GROUP BY
                line_no
            ORDER BY
                line_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// 組立日程計画表の対象日から前後各1ヶ月の日付を返す
    // HTMLのformに挿入するselectメニューを表示
    public function getDateSpanHTML($targetDate)
    {
        $year = substr($targetDate, 0, 4); $mon = substr($targetDate, 4, 2); $day = substr($targetDate, 6, 2);
        $HtmlSource  = "\n";
        $HtmlSource .= "<select name='targetDate' onChange='submit()' style='text-align:right; font-size:12pt; font-weight:bold;'>\n";
        for ($i=(-31); $i<=31; $i++) {
            $timestamp = mktime(0, 0, 0, $mon, ($day + $i), $year);
            if (!day_off($timestamp)) {
                // 営業日なら
                $date = date('Ymd', $timestamp);
                if ($targetDate == $date) {
                    $HtmlSource .= "<option value='{$date}' style='color:white;background-color:red;' selected>{$date}</option>\n";
                } else {
                    $HtmlSource .= "<option value='{$date}'>{$date}</option>\n";
                }
            }
        }
        $HtmlSource .= "</select>\n";
        return $HtmlSource;
    }
    
    ///// 出庫率を求める。なるべくassy_noを指定したほうが良い(枝番違いで同じ計画番号がある場合がある)
    public function getPickingRatio($plan_no, $assy_no=false)
    {
        $query = "
            SELECT
                CASE
                    WHEN sum(allo_qt) = 0 THEN 0    -- division by zero 対応
                    ELSE
                    Uround(
                        CAST(sum(sum_qt) AS numeric(11, 2)) / CAST(sum(allo_qt) AS numeric(11, 2)) * 100, 2
                    )
                END
            FROM allocated_parts
        ";
        if (!$assy_no) {
            $query .= " WHERE plan_no='{$plan_no}' AND assy_no='{$assy_no}'";
        } else {
            $query .= " WHERE plan_no='{$plan_no}'";
        }
        $ritu = '  0';
        $this->getUniResult($query, $ritu);     // データがあれば$rituに値が入る
        switch (strlen($ritu)) {
            case 1: $ritu = '  ' . $ritu . '%'; break;
            case 2: $ritu = ' ' . $ritu . '%'; break;
            case 3: $ritu = $ritu . '%'; break;
        }
        return $ritu;
    }
    
    ///// 出庫率のデータを％付で桁数を揃える フォーマットメソッド
    public function formatPickingRatio($ritu)
    {
        // 出庫率を0.0(小数点１位)にしたため 123 → 345 へ変更
        // 出庫率を0.00(小数点２位)にしたため 345 → 456 へ変更 2007/02/01 (99.96 → 100.0になってしまうため)
        switch (strlen($ritu)) {
            case 4: $ritu = '  ' . $ritu . '%'; break;
            case 5: $ritu = ' ' . $ritu . '%'; break;
            case 6: $ritu = $ritu . '%'; break;
            default: $ritu = '  0%'; break;
        }
        return $ritu;
    }
    
    ///// Edit Confirm_delete 1レコード分の詳細データ用
    public function getViewDataEdit($request)
    {
        $query = "
            SELECT
                 plan_no        AS 計画番号         -- 00
                ,parts_no       AS 製品番号         -- 01
                ,substr(midsc, 1, 20)
                                AS 製品名           -- 02
                ,plan - cut_plan - kansei
                                AS 計画残数         -- 03
                ,syuka          AS 集荷日           -- 04
                ,chaku          AS 着手日           -- 05
                ,kanryou        AS 完了日           -- 06
                ,CASE
                    WHEN trim(note15) = '' THEN '&nbsp;'
                    ELSE note15
                 END            AS 備考             -- 07
                -----------------------------リストは上記まで
                ,plan           AS 計画数           -- 08
                ,cut_plan       AS 打切り数         -- 09
                ,kansei         AS 完成数           -- 10
                ,(
                    SELECT
                        CASE
                            WHEN sum(allo_qt) = 0 THEN 0    -- division by zero 対応
                            ELSE
                            Uround(
                                CAST(sum(sum_qt) AS numeric(11, 2)) / CAST(sum(allo_qt) AS numeric(11, 2)) * 100, 2
                            )
                        END
                    FROM allocated_parts WHERE plan_no=sche.plan_no AND assy_no=sche.parts_no
                 )              AS 出庫率           -- 11
            FROM
                assembly_schedule AS sche
            LEFT OUTER JOIN
                miitem ON (parts_no=mipn)
            WHERE
                plan_no = '{$request->get('plan_no')}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $request->add('plan_no',    $res[0][0]);
            $request->add('assy_no',    $res[0][1]);
            $request->add('assy_name',  $res[0][2]);
            $request->add('plan_zan',   $res[0][3]);
            $request->add('syuka',      $res[0][4]);
            $request->add('chaku',      $res[0][5]);
            $request->add('kanryou',    $res[0][6]);
            $request->add('bikou',      $res[0][7]);
            $request->add('plan_pcs',   $res[0][8]);
            $request->add('cut_pcs',    $res[0][9]);
            $request->add('end_pcs',    $res[0][10]);
            $request->add('pick_ratio', $res[0][11]);
        }
        return $rows;
    }
    
    ///// ライン名の表示メソッド
    public function showLineNameButton($request, $menu, $rowsLine, $resLine, $pageParameter, $uniq)
    {
        $tr = 0; $column = 10;
        $arrayLine = $request->get('arrayLine');
        for ($i=(-1); $i<$rowsLine; $i++) {
            if ($tr == 0) {
                echo "<tr>\n";
            }
            echo "<td class='winbox' align='center' nowrap>\n";
            if ($i == (-1)) {
                echo "<input type='button' name='showLine' value='全て' class='pt12b bg'\n";
                echo "    onClick='AssemblyScheduleShow.setLineMethod(\"1\"); AssemblyScheduleShow.targetLineExecute(\"{$menu->out_self()}?showLine=0&showMenu={$request->get('showMenu')}&{$pageParameter}&id={$uniq}\")'\n";
                // 個別指定のみ
                if ($request->get('showLine') == '') echo "    style='color:red;'\n";
                echo ">\n";
            } else {
                echo "<input type='button' name='showLine' value='{$resLine[$i][0]}' class='pt12b bg'\n";
                echo "    onClick='AssemblyScheduleShow.targetLineExecute(\"{$menu->out_self()}?showLine={$resLine[$i][0]}&showMenu={$request->get('showMenu')}&{$pageParameter}&id={$uniq}\")'\n";
                if ($request->get('targetLineMethod') == '1') {
                    // 個別指定
                    if ($resLine[$i][0] == $request->get('showLine')) echo "    style='color:red;'\n";
                } else {
                    // 複数指定
                    if (array_search($resLine[$i][0], $arrayLine) !== false)
                        echo "    style='color:blue;'\n";
                }
                echo ">\n";
            }
            echo "</td>\n";
            $tr++;
            if ($tr >= $column) {
                echo "</tr>\n";
                $tr = 0;
            }
        }
        if ($tr != 0) {
            while ($tr < $column) {
                echo "    <td class='winbox' width='55'>&nbsp;</td>\n";
                $tr++;
            }
            echo "</tr>\n";
        }
    }
    // 当月追加計画の判定（当月追加されたものはTRUE）
    public function plan_add_check($plan_no)
    {
        $cstr_date = date('Ym') . '01';
        $cend_date = date('Ym') . '99';
        $query = "
            SELECT
                 plan_no        AS 計画番号         -- 00
                ,kanryou        AS 完了日           -- 01
                ,rep_date       AS 更新日           -- 02
                ,crt_date       AS 作成日           -- 03
            FROM
                assembly_schedule
            WHERE
                plan_no = '{$plan_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            if ($cstr_date <= $res[0][1] && $cend_date >= $res[0][1]) {
                if ($cstr_date <= $res[0][3] && $cend_date >= $res[0][3]) {
                    return true;
                }
            }
        }
        return false;
    }
    // 当月追加計画の判定（当月変更されたものはTRUE）
    public function plan_change_check($plan_no)
    {
        $cstr_date = date('Ym') . '01';
        $cend_date = date('Ym') . '99';
        $query = "
            SELECT
                 plan_no        AS 計画番号         -- 00
                ,kanryou        AS 完了日           -- 01
                ,rep_date       AS 更新日           -- 02
                ,crt_date       AS 作成日           -- 03
            FROM
                assembly_schedule
            WHERE
                plan_no = '{$plan_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            if ($cstr_date <= $res[0][1] && $cend_date >= $res[0][1]) {
                if ($cstr_date <= $res[0][2] && $cend_date >= $res[0][2]) {
                    return true;
                }
            }
        }
        return false;
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 組立指示メニューの編集権限チェックメソッド(共用メソッド)
    protected function assemblyAuthUser()
    {
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {             // 社員の所属する部門コードでチェック
            case 500:                   // 生産部 (2005/12/15追加)
            case 176:
            case 522:
            case 523:
            case 525:
                return true;            // カプラ組立(資材を除く)
            case 551:
            case 175:
            case 560:
            case 537:
            case 534:
                return true;            // リニア組立(資材・検査を除く)
            default:
                if ($_SESSION['Auth'] >= 3) { // テスト用
                    return true;
                }
                return false;
            }
        } else {
            return false;
        }
    }
    
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function InitWherePlanList($request)
    {
        ///// 指定日の範囲
        switch ($request->get('targetDateSpan')) {
        case '0':   // 指定日のみ
            if ($request->get('targetDateItem') == 'chaku') {
                $where = "WHERE chaku = {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } elseif ($request->get('targetDateItem') == 'syuka') {
                $where = "WHERE syuka = {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } else {
                $where = "WHERE kanryou = {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            }
            break;
        case '1':   // 月初から指定日まで → 前月1日から指定日まで
        default :
            ///// 未完成分の表示=前月の月初から, 完成分の表示は=当月の月初から
            if ($request->get('targetCompleteFlag') == 'no') {
                // 前月の1日に設定
                // 以下は  date('Ymd', mktime(0, 0, 0, 月-1, 1日, 年)) で前月の1日を設定している mktime()の自動修正機能を利用
                $strDate = date('Ymd', mktime(0, 0, 0, substr($request->get('targetDate'), 4, 2) - 1, 1, substr($request->get('targetDate'), 0, 4)));
            } else {
                // 月初を設定
                $strDate = substr($request->get('targetDate'), 0, 4) . substr($request->get('targetDate'), 4, 2) . '01';
            }
            if ($request->get('targetDateItem') == 'chaku') {
                $where = "WHERE chaku >= {$strDate} AND chaku <= {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } elseif ($request->get('targetDateItem') == 'syuka') {
                $where = "WHERE syuka >= {$strDate} AND syuka <= {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            } else {
                $where = "WHERE kanryou >= {$strDate} AND kanryou <= {$request->get('targetDate')} AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'";
            }
        }
        ///// 未完成分・完成分の切替 指定   2006/10/19 'AND → ' AND スペースが抜けていたのを修正
        if ($request->get('targetCompleteFlag') == 'no') {
             $where .= ' AND (plan - cut_plan - kansei) > 0';
        } else {
             $where .= ' AND (plan - cut_plan) = kansei AND kansei > 0';
        }
        ///// 製品区分の指定
        switch ($request->get('targetSeiKubun')) {
        case '1':   // 製品
            $where .= " AND sei_kubun = '1'";
            break;
        case '2':   // Lホヨウ
            $where .= " AND sei_kubun = '2'";
            break;
        case '3':   // C特注
            $where .= " AND sei_kubun = '3'";
            break;
        case '4':   // Lピストン
            $where .= " AND sei_kubun = '4'";
            break;
        case '0':   // 全て(デフォルト)
        default :
        }
        ///// 製品事業部の指定
        if ($request->get('targetDept') == 'C') {
            $where .= " AND dept = 'C'";
        } elseif ($request->get('targetDept') == 'L') {
            $where .= " AND dept = 'L'";
        } elseif ($request->get('targetDept') == 'T') {
            $where .= " AND dept = 'T'";
        }
        ///// 基本WHERE区をプロパティへ登録
        $this->whereNoLine = $where;
        ///// ライン番号の指定
        $where .= $this->setLineWhere($request);
        return $where;
    }
    
    ///// ラインの指定方法及びライン選択のWHERE区をセット
    protected function setLineWhere($request)
    {
        if ($request->get('targetLineMethod') == '1') {
            // 個別指定
            if ($request->get('showLine') != '') {
                return " AND line_no = '{$request->get('showLine')}'";
            } else {
                return '';
            }
        } else {
            // 複数指定
            $arrayLine = $request->get('arrayLine');
            $i = 0;
            foreach ($arrayLine as $value) {
                if ($i == 0) {
                    $where = ' AND (';
                } else {
                    $where .= ' OR ';
                }
                $where .= "line_no='{$value}'";
                $i++;
            }
            if (isset($where)) {
                $where .= ')';
            } else { 
                $where = " AND line_no=''";
            }
            return $where;
        }
    }
    
    ///// Set部  Activity の CSIM設定 showMenuの内容で文字コードを切替
    protected function setActivityCSIM($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // 計画番号
        $assy_no  = $res[1];    // 製品番号
        $assy_name= $res[2];    // 製品名
        $plan_zan = $res[3];    // 計画残数
        $syuka    = $res[4];    // 集荷日
        $chaku    = $res[5];    // 着手日
        $kanryou  = $res[6];    // 完了日
        $bikou    = $res[7];    // 備考
        $plan_pcs = $res[8];    // 計画数
        $cut_pcs  = $res[9];    // 打切り数
        $end_pcs  = $res[10];   // 完成数
        $ritu     = $res[11];   // 出庫率
        $targ1 = "JavaScript:alert('計画番号：{$plan_no}\\n\\n製品番号：{$assy_no}\\n\\n製品名称：{$assy_name}\\n\\n計画数：{$plan_pcs}\\n\\n打切数：{$cut_pcs}\\n\\n完成数：{$end_pcs}\\n\\n計画残：{$plan_zan}\\n\\n集荷日：{$syuka}\\n\\n着手日：{$chaku}\\n\\n完了日：{$kanryou}\\n\\n備　 考：{$bikou}')";
        $alts1 = "計画番号：{$plan_no}　製品番号：{$assy_no}　計画残：{$plan_zan}　製品名称：{$assy_name}";
        $targ2 = "{$menu->out_action('引当構成表')}?plan_no=".urlencode($plan_no)."&material=1&id={$menu->out_useNotCache()}";
        $alts2 = 'この計画番号の引当部品構成表を表示します。';
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'UTF-8');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'UTF-8');
            $targ2 = mb_convert_encoding($targ2, 'UTF-8', 'UTF-8');
            $alts2 = mb_convert_encoding($alts2, 'UTF-8', 'UTF-8');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ2, $alts2);
        if ($request->get('material_plan_no') == $plan_no) {
            $activity->title->SetColor('red');  // マーカー用
        }
    }
    
    ///// Set部  実績チャート用 Activity の CSIM設定 showMenuの内容で文字コードを切替
    protected function setActivityCSIMreal($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // 計画番号
        $assy_name= $res[2];    // 製品名
        $targ1 = "{$menu->out_action('実績工数照会')}?showMenu=CondForm&targetPlanNo=" . urlencode($plan_no);
        $alts1 = "製品名：{$assy_name}　の登録工数と実績工数を照会します。";
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'UTF-8');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'UTF-8');
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
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'UTF-8');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'UTF-8');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ1, $alts1);
    }
    
    ///// 指定された計画番号の着手日時をチェックしてあれば最小値と最大値をセットなければ19700101をセット
    protected function plan_chaku_check($plan_no, &$strDate, &$endDate)
    {
        $query = "
            SELECT to_char(str_time, 'YYYYMMDD') FROM assembly_process_time WHERE plan_no='{$plan_no}'
            ORDER BY str_time ASC LIMIT 1
        ";
        $strDate = '19700101';
        $endDate = '19700101';
        if ($this->getUniResult($query, $strDate) > 0) {
            $query = "
                SELECT to_char(str_time, 'YYYYMMDD') FROM assembly_process_time WHERE plan_no='{$plan_no}'
                ORDER BY str_time DESC LIMIT 1
            ";
            $this->getUniResult($query, $endDate);
        } else {
            return false;
        }
        return true;
    }
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 組立実績の追加実行
    private function ApendExecute($request)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO assembly_process_time
            (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, plan_pcs, assy_time, last_date, last_host)
            values
            ({$request->get('showGroup')}, '{$request->get('plan_no')}', '{$request->get('user_id')}', '{$request->get('str_time')}', '{$request->get('end_time')}'
            , {$request->get('plan')}, {$request->get('plan')}, {$request->get('assy_time')}, '{$last_date}', '{$last_host}')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 組立実績の削除実行
    private function DeleteExecute($request)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // 同時作業計画分のために必要なデータを先に残す
        $query = "
            SELECT str_time, end_time, user_id FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $request->add('str_time', $res[0][0]);
            $request->add('end_time', $res[0][1]);
            $request->add('user_id',  $res[0][2]);
        } else {
            return false;
        }
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $delete_sql = "
            DELETE FROM assembly_process_time
            WHERE serial_no={$request->get('serial_no')}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// 組立実績の修正実行
    private function EditExecute($request, $session)
    {
        // ここに last_date last_host の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        // 最初に単独で変更を実行
        $update_sql = "
            UPDATE assembly_process_time SET
                plan_no='{$request->get('plan_no')}', user_id='{$request->get('user_id')}',
                str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                plan_all_pcs={$request->get('plan')}, plan_pcs={$request->get('plan')},
                assy_time={$request->get('assy_time')}, last_date='{$last_date}', last_host='{$last_host}'
            WHERE
                serial_no={$request->get('serial_no')}
        ";
        if (!$this->execute_Update($update_sql, $save_sql)) {
            return false;
        }
        // 同時作業計画が存在するかチェック
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        $rows = $this->getResult2($query, $res);
        // 社員番号をチェックし同時作業計画の修正を分岐させる
        if ($session->get_local('pre_user_id') == $request->get('user_id') && $rows > 0) {
            // 社員番号が同じなので同時作業計画のstr_timeとend_timeを変更
            $update_sql = "
                UPDATE assembly_process_time SET
                    str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                    last_date='{$last_date}', last_host='{$last_host}'
                WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            ";
            return $this->execute_Update($update_sql, $save_sql);
        } else {
            // 社員番号が変わったため単独と見なして同時作業計画の日時変更はしない
            // 又は同時作業計画が存在しない(最初はトランザクションで行っていたが同時作業計画が存在しない場合に単独の更新が出来なくなるため個別にした)
        }
        return true;
    }
    
} // Class AssemblyScheduleShow_Model End

?>
