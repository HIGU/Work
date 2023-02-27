<?php
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理用 グラフ Create Class                                   //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created   EquipGraphClassMoni.php                             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// require_once ('../function.php');
require_once ('equip_function.php');
require_once ('/home/www/html/jpgraph_equip.php'); 
require_once ('/home/www/html/jpgraph_line.php'); 

if (class_exists('EquipGraph')) {
    return;
}
define('EG_VERSION', '1.06');

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
class EquipGraph
{
    ///// Private properties
    private $mac_no;                    // 機械番号
    private $plan_no;                   // 指示番号(5桁と7桁あり)
    private $koutei;                    // 工程番号
    private $str_timestamp;             // 開始日時(Header) 'YYYYMMDDHH24MISS'
    private $end_timestamp;             // 終了日時(Header OR CURRENT_TIMESTAMP)
    private $interval;                  // 終了-開始日時のインターバル(間隔)をhourで保存
    private $xtimeArrVal;               // X軸の時間をセットできる値の設定名称をkeyとする連想配列
    private $xtime;                     // X軸の時間範囲(6/12/24/48...hr)
    private $graph_strTime;             // 指定範囲の開始日時 'YYYYMMDDHH24MISS'
    private $graph_endTime;             // 指定範囲の終了日時
    private $width;                     // グラフの幅(pixel)
    private $height;                    // グラフの高さ(pixel)
    private $title;                     // グラフのタイトル名
    private $sampling;                  // 指定範囲内のサンプリング数
    private $sample_time;               // サンプルタイム(分)間隔
    private $multiply;                  // X軸の(時間) scale 倍率
    private $yaxis_min_data;            // Y軸の最小値
    private $xdata;                     // X軸の配列データ1次元
    private $ydata;                     // Y軸の配列データ2次元
    private $graph_page;                // グラフを表示するページ番号
    private $forward;                   // 次ページのフラグ
    private $backward;                  // 前ページのフラグ
    private $rui_state;                 // state 毎の累計を保存する 配列
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5へ移行時は __construct() へ変更予定)
    function EquipGraph($mac_no, $plan_no, $koutei, $sampling=180)
    {
        if (!isset($_SESSION)) {                    // セッションの開始チェック
            session_start();                        // Notice を避ける場合は頭に@
        }
        if ($this->set_condition($mac_no, $plan_no, $koutei) == false) {
            $addr_status = "ErrorEquipGraphPage.php?status=1&mac_no=$mac_no&plan_no=$plan_no&koutei=$koutei";
            header('Location: ' . H_WEB_HOST . ERROR . $addr_status);
            exit();
        }
        $this->set_xtimeArrVal();               // X軸のセットできる配列の初期化
        $this->xtime = 12;                      // 初期化 Default=12hr
        $this->set_graph_page();                // 初期化 Default=0 表示ページのoffsetページ数(-1/0/1/2/3...)
        $this->set_graphWH();                   // 初期化
        $this->set_title();                     // 初期化
        $this->set_sampling($sampling);         // 初期化
    }
    
    /*************************** Set & Check methods ************************/
    // 機械番号・指示番号・工程番号のチェックと設定及び開始終了日時の取得
    function set_condition($mac_no, $plan_no, $koutei)
    {
        if ($mac_no  == '') return false;
        if ($plan_no == '') return false;
        if ($koutei  == '') return false;
        $query = " select to_char(str_timestamp, 'YYYYMMDDHH24MISS') as str_timestamp
                        , to_char(end_timestamp, 'YYYYMMDDHH24MISS') as end_timestamp
                    from
                        equip_work_log2_header_moni
                    where
                        mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei
        ";
        $res = array();
        if (getResult($query, $res) <= 0) {
            return false;
        } else {
            $this->str_timestamp = $res[0]['str_timestamp'];
            $this->end_timestamp = $res[0]['end_timestamp'];
            if ($this->end_timestamp == '') {
                $query = "select to_char(CURRENT_TIMESTAMP, 'YYYYMMDDHH24MISS') as end_timestamp";
                getUniResult($query, $this->end_timestamp);
            }
        }
        $str_date = substr($this->str_timestamp, 0, 8);
        $str_time = substr($this->str_timestamp, 8, 6);
        $end_date = substr($this->end_timestamp, 0, 8);
        $end_time = substr($this->end_timestamp, 8, 6);
        $query = "select EXTRACT(EPOCH FROM(CAST('{$end_date} {$end_time}' AS TIMESTAMP) - CAST('{$str_date} {$str_time}' AS TIMESTAMP))/60/60)";
        getUniResult($query, $this->interval);
        $this->mac_no  = $mac_no;
        $this->plan_no = $plan_no;
        $this->koutei  = $koutei;
        return true;
    }
    // X軸(時間軸)の設定
    function set_xtime($xtime=12)
    {
        if (is_string($xtime)) {
            $xtime = strtolower($xtime);
            if ($xtime == 'max') {
                $xtime = $this->interval;       // ロット全体を指定された場合
            }
        }
        $unset = true;
        foreach ($this->xtimeArrVal as $time) {
            if ($xtime <= $time) {
                $xtime = $time;
                $unset = false;
                break;
            }
        }
        if ($unset) {
            $xtime = 1440;   // 60日(見つからない場合は最大値を設定)
        }
        $this->xtime = $xtime;
        $this->set_graph_page();        // xtimeの変更による再設定
        $this->set_sample_time();       // サンプリング間隔の設定
        $this->set_multiply();          // X軸(時間scale)の倍率設定
    }
    // グラフの表示ページの設定
    function set_graph_page($page_offset=0)
    {
        if (isset($_SESSION['equip_graph_page'])) {
            $this->graph_page = $_SESSION['equip_graph_page'];   // 表示ページの初期化
        } else {
            $_SESSION['equip_graph_page'] = 1;
            $this->graph_page = $_SESSION['equip_graph_page'];
        }
        $this->graph_page += ($page_offset);
        if ($this->graph_page <= 0) {
            $this->graph_page = 1;
        }
        $_SESSION['equip_graph_page'] = $this->graph_page;
        $this->set_graph_strTime($this->graph_page);
    }
    // グラフの横幅・高さを設定
    function set_graphWH($width=670, $height=350)
    {
        $this->width  = $width;
        $this->height = $height;
    }
    // グラフのタイトル名を設定
    function set_title($title='生産数 状態 グラフ')
    {
        //////////////// 機械マスターから機械名を取得
        $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$this->mac_no} limit 1";
        if (getUniResult($query, $mac_name) <= 0) {
            $mac_name = '　';   // error時は機械名をブランク
        }
        $this->title  = "{$this->mac_no}　{$mac_name}　{$title}";
    }
    // ログの指定範囲内でのサンプリング数を設定
    function set_sampling($sampling=180)
    {
        if ($sampling <= 180) {
            $sampling = 180;
        } elseif ($sampling <= 360) {
            $sampling = 360;
        } else {
            $sampling = 180;
        }
        $this->sampling = $sampling;    // サンプリング数の設定
        $this->set_sample_time();       // サンプリング間隔の設定
        $this->set_multiply();          // X軸(時間scale)の倍率設定
    }
    
    /******************************* Out methods ****************************/
    // グラフのX軸の時間スケール値を出力
    function out_xtime()
    {
        return $this->xtime;
    }
    // グラフの表示ページコントロール出力
    function out_page_ctl($flg='backward')
    {
        if ($flg == 'backward') {
            return $this->backward;
        } elseif($flg == 'forward') {
            return $this->forward;
        } else {
            return false;
        }
    }
    // グラフ開始日時の出力
    function out_graph_strTime()
    {
        return $this->graph_strTime;
    }
    // グラフ終了日時の出力
    function out_graph_endTime()
    {
        return $this->graph_endTime;
    }
    // グラフ範囲の開始日時の出力 (format付)  出力書式=配列(strDate, strTime, endDate, endTime)
    function out_graph_timestamp()
    {
        $year1  = substr($this->graph_strTime, 0, 4);
        $month1 = substr($this->graph_strTime, 4, 2);
        $day1   = substr($this->graph_strTime, 6, 2);
        $HH1    = substr($this->graph_strTime, 8, 2);
        $MI1    = substr($this->graph_strTime, 10, 2);
        $SS1    = substr($this->graph_strTime, 12, 2);
        $year2  = substr($this->graph_endTime, 0, 4);
        $month2 = substr($this->graph_endTime, 4, 2);
        $day2   = substr($this->graph_endTime, 6, 2);
        $HH2    = substr($this->graph_endTime, 8, 2);
        $MI2    = substr($this->graph_endTime, 10, 2);
        $SS2    = substr($this->graph_endTime, 12, 2);
        return array('strDate' => "$year1/$month1/$day1", 'strTime' => "$HH1:$MI1:$SS1", 'endDate' => "$year2/$month2/$day2", 'endTime' => "$HH2:$MI2:$SS2");
    }
    // ロット全体の開始日時の出力 (format付)  出力書式=配列(strDate, strTime, endDate, endTime)
    function out_lot_timestamp()
    {
        $year1  = substr($this->str_timestamp, 0, 4);
        $month1 = substr($this->str_timestamp, 4, 2);
        $day1   = substr($this->str_timestamp, 6, 2);
        $HH1    = substr($this->str_timestamp, 8, 2);
        $MI1    = substr($this->str_timestamp, 10, 2);
        $SS1    = substr($this->str_timestamp, 12, 2);
        $year2  = substr($this->end_timestamp, 0, 4);
        $month2 = substr($this->end_timestamp, 4, 2);
        $day2   = substr($this->end_timestamp, 6, 2);
        $HH2    = substr($this->end_timestamp, 8, 2);
        $MI2    = substr($this->end_timestamp, 10, 2);
        $SS2    = substr($this->end_timestamp, 12, 2);
        return array('strDate' => "$year1/$month1/$day1", 'strTime' => "$HH1:$MI1:$SS1", 'endDate' => "$year2/$month2/$day2", 'endTime' => "$HH2:$MI2:$SS2");
    }
    ////////// グラフを作成して出力
    function out_graph($graph_name='', $summary='no')
    {
        $this->generate_data();
        if ($summary == 'yes') {
            $this->height += 20;                            // サマリーラベルが必要なので高さを+20
            $m_b = 80;                                      // Margin-bottom=80
        } else {
            $m_b = 60;                                      // Margin-bottom=60 サマリー無しの場合
        }
        $graph = new Graph($this->width, $this->height, 'auto');   // グラフの大きさ X/Y
        $graph->img->SetMargin(60, 20, 20, $m_b);                   // グラフ位置のマージン 左右上下
        $graph->SetScale('textlin');                        // X / Y LinearX LinearY (通常はtextlin TextX LinearY)
        $graph->SetShadow(); 
        $graph->yscale->SetGrace(10);                       // Set 10% grace. 余裕スケール
        $graph->yaxis->SetColor('blue');
        $graph->yaxis->SetWeight(2);
        $graph->yaxis->scale->ticks->SupressFirst();        // Y軸の最初のメモリラベルを表示しない
        $graph->yscale->SetAutoMin($this->yaxis_min_data);  // Y軸のスタートを変更
        $graph->xaxis->SetPos('min');                       // X軸のプロットエリアを一番下へ
        $graph->xaxis->SetTickLabels($this->xdata);
        $graph->xaxis->SetFont(FF_FONT1);
        $graph->xaxis->SetLabelAngle(0);                    // 90 → 0
        // $graph->xaxis->SetTextLabelInterval($this->sampling/$this->xtime);     // ステップ
        $graph->xaxis->SetTextTickInterval($this->sampling / $this->xtime * $this->multiply, 0);
                                                            // ステップ, スタート
        $plot = array();
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {               // 0～11
            if ($this->rui_state[$i] <= 0) {
                continue;         // state type 毎の累積時間を取得している場合は、ある物だけを処理する
            }
            equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
            $plot[$i] = new LinePlot($this->ydata[$i]);
            $plot[$i]->SetFillColor($bg_color);
            $plot[$i]->SetFillFromYMin($this->yaxis_min_data);  // Y軸の最小値を変更
            $plot[$i]->SetColor($bg_color);
            $plot[$i]->SetCenter();
            $plot[$i]->SetStepStyle();
            $graph->Add($plot[$i]);
        }
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 12); // FF_GOTHIC 14 以上 FF_MINCHO は 17 以上を指定する
        $graph->title->Set(mb_convert_encoding($this->title, 'UTF-8'));
        $graph->xaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph->xaxis->title->Set(mb_convert_encoding('時間(H)', 'UTF-8'));
        $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph->yaxis->title->Set(mb_convert_encoding('生産数', 'UTF-8'));
        $graph->yaxis->title->SetMargin(20, 0, 0, 0);
        ///// サマリー結果をグラフに書込む
        $g_x = 50;      // 書込むX軸の初期位置
        if ($summary == 'yes') {
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                if ($this->rui_state[$i] <= 0) {
                    continue;
                }
                $name = equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
                $name .=  "\n" . number_format($this->rui_state[$i]/60, 2) . '(' . number_format($this->rui_state[$i]) . ')';
                $g_txt = new Text(mb_convert_encoding($name, 'UTF-8'), $g_x, $this->height - 50);
                $g_txt->SetFont(FF_GOTHIC, FS_NORMAL, 9);
                $g_txt->SetBox($bg_color, $bg_color, 'gray4');  // bg-color, border-color, shadow-color
                $g_txt->SetColor($txt_color);
                $g_txt->ParagraphAlign('center');
                $graph->AddText($g_txt);
                $g_x += ($g_txt->GetWidth($graph->img) + 25);   // 次のためにTextBoxの幅を取得
            }
        }
        if ($graph_name == '') {
            $graph_name = ('graph/equip' . session_id() . '.png');
        }
        $graph->Stroke($graph_name);
    }
    ////////// state 毎の累積された集計表の出力
    function out_state_summary()
    {
        $out = '';
        $out .= "        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
        $out .= "            <tr><td> <!-- ダミー(デザイン用) -->\n";
        $out .= "        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='2'>\n";
        $out .= "            <th width='100'>&nbsp;</th>\n";
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
            if ($this->rui_state[$i] <= 0) {
                continue;
            }
            $name = equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
            $out .= "            <th bgcolor='$bg_color' width='100' style='font-size:11pt'><font color='$txt_color'>" . $name . "</font></th>\n";
        }
        $out .= "            <tr>\n";
        $out .= "                <td align='center' style='font-size:11pt'>累積時間H (M)</td>\n";
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
            if ($this->rui_state[$i] <= 0) {
                continue;
            }
            $name = equip_machine_state($this->mac_no, $i, $bg_color, $txt_color);
            $out .= "            <td align='center' style='font-size:12pt'>" . number_format($this->rui_state[$i]/60, 2) . ' (' . number_format($this->rui_state[$i]) . ")</td>\n";
        }
        $out .= "            </tr>\n";
        $out .= "        </table>\n";
        $out .= "            </td></tr>\n";
        $out .= "        </table> <!-- ダミーEnd -->\n";
        return $out;
    }
    ////////// グラフのX軸 時間スケール値をHTMLのselect->option のみを出力
    function out_select_xtime($xtime)
    {
        $option = "\n";     // これがミソ ソース表示を見やすくする
        foreach ($this->xtimeArrVal as $name => $time) {
            if ($xtime == $time) {
                if (mb_strlen($name) <= 3) {
                    $option .= "                            <option value='{$time}' selected>&nbsp;{$name}</option>\n";
                } else {
                    $option .= "                            <option value='{$time}' selected>{$name}</option>\n";
                }
            } else {
                if (mb_strlen($name) <= 3) {
                    $option .= "                            <option value='{$time}'>&nbsp;{$name}</option>\n";
                } else {
                    $option .= "                            <option value='{$time}'>{$name}</option>\n";
                }
            }
        }
        return $option;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    // X軸のスケールセットできる配列を初期化
    protected function set_xtimeArrVal()
    {
        for ($i=2; $i<=24; $i+=2) {                 // 2時間～24時間
            $this->xtimeArrVal["{$i}時間"] = $i;
        }
        for ($i=48; $i<=1440; $i+=24) {             // 2日間～60日間
            $day = ($i / 24);
            $this->xtimeArrVal["{$day}日間"] = $i;
        }
    }
    // グラフ化する開始時間と終了時間の設定
    protected function set_graph_strTime($graph_page=1)
    {
        if ($graph_page <= 1) {
            $this->backward = false;
        } else {
            $this->backward = true;
        }
        $date = substr($this->str_timestamp, 0, 8);
        $time = substr($this->str_timestamp, 8, 6);
        if ($graph_page > 1) {
            $time = '00:00:00';
        }
        $xtime = ($this->xtime * ($graph_page - 1) );
        $query = "select to_char( (CAST('$date $time' AS TIMESTAMP) + interval '$xtime hour'), 'YYYYMMDDHH24MISS')";
        getUniResult($query, $strTime);
        $this->graph_strTime = $strTime;
        $this->set_graph_endTime($this->graph_strTime, $this->xtime, $graph_page);
    }
    // グラフ化する終了時間の設定
    protected function set_graph_endTime($strTime, $xtime, $graph_page)
    {
        $date = substr($strTime, 0, 8);
        $time = substr($strTime, 8, 6);
        //$time = 235959;
        $query = "select to_char( (CAST('$date $time' AS TIMESTAMP) + interval '$xtime hour'), 'YYYYMMDDHH24MISS')";
        //$query = "select to_char( (CAST('$date $time' AS TIMESTAMP)), 'YYYYMMDDHH24MISS')";
        getUniResult($query, $endTime);
        $this->graph_endTime = $endTime;
        if ($this->end_timestamp > $this->graph_endTime) {
            $this->forward = true;
        } else {
            //$this->graph_endTime = $this->end_timestamp;
            $this->forward = false;
        }
    }
    // グラフのサンプリング間隔の設定
    protected function set_sample_time()
    {
        $this->sample_time = (($this->xtime * 60) / $this->sampling);   // 例 (12*60)/180=4分間隔(default)
    }
    // グラフのX軸(時間)の scale用 倍率設定
    protected function set_multiply()
    {
        if ($this->sampling <= 180) {   // サンプル数＝解像度 180の時
            if ($this->sample_time <= 4) {
                $this->multiply = 1;
            } else {
                $this->multiply = ($this->sample_time / 4);           // scale の倍率設定
            }
        } else {                        // 現在は 360の解像度の時
            if ($this->sample_time <= 2) {
                $this->multiply = 1;
            } else {
                $this->multiply = ($this->sample_time / 2);           // scale の倍率設定
            }
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    // グラフ用データ生成 X軸とY軸(最大12種類)                                //
    ////////////////////////////////////////////////////////////////////////////
    protected function generate_data()
    {
        /////////// begin トランザクション開始
        if ($con = funcConnect()) {
            query_affected_trans($con, "begin; SET enable_seqscan TO 'off';");
        }
        ////////// 指定範囲内にデータがあるかチェック
        $query = " select work_cnt
                    from
                        equip_work_log2_moni
                    where
                        plan_no='{$this->plan_no}' and mac_no={$this->mac_no} and koutei={$this->koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') >={$this->graph_strTime} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <= {$this->graph_endTime}
                    offset 0 limit 1
        ";
        /*
        $query = " select work_cnt
                    from
                        equip_work_log2_moni
                    where
                        equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_strTime}'
                    and
                        equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_endTime}'
                    offset 0 limit 1
        ";
        */
        if (getUniResTrs($con, $query, $max_work_cnt) <= 0) {
            $empty = true;
        } else {
            $empty = false;
        }
        ////////// 指定範囲内での最大生産数を取得してyaxis_min_dataを設定する
        if ($empty == false) {
            // 指定範囲にデータがある場合の処理
            $query = " 
                select max(work_cnt)
                from
                    equip_work_log2_moni
                where
                    plan_no='{$this->plan_no}' and mac_no={$this->mac_no} and koutei={$this->koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') >={$this->graph_strTime} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <= {$this->graph_endTime}
            ";
            /*
            $query = " 
                select max(work_cnt)
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_strTime}'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_endTime}' ;
            ";
            */
            getUniResTrs($con, $query, $max_work_cnt);
        } else {
            // 指定範囲にデータがない場合の処理
            $query = " select work_cnt
                        from
                            equip_work_log2_moni
                        where
                            plan_no='{$this->plan_no}' and mac_no={$this->mac_no} and koutei={$this->koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') < {$this->graph_strTime}
                        order by
                            date_time DESC
                        offset 0 limit 1
            ";
            /*
            $query = " select work_cnt
                        from
                            equip_work_log2_moni
                        where
                            equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$this->mac_no}{$this->plan_no}{$this->koutei}00000000000000'
                        and
                            equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_strTime}'
                        order by
                            equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                        offset 0 limit 1
            ";
            */
            if (getUniResTrs($con, $query, $max_work_cnt) <= 0) {
                $max_work_cnt = 0;
            }
        }
        $this->yaxis_min_data = yaxis_min($max_work_cnt);     // work_counter の最大加工数からグラフの最小値を算出
        ////////// 初回データの取得 (初回はgraph_strTime以下のデータを1件取得)
        $query = " select mac_state
                        , work_cnt
                    from
                        equip_work_log2_moni
                    where
                        plan_no='{$this->plan_no}' and mac_no={$this->mac_no} and koutei={$this->koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <= {$this->graph_strTime}
                    order by
                        date_time DESC
                    offset 0 limit 1
        ";
        /*
        $query = " select mac_state
                        , work_cnt
                    from
                        equip_work_log2_moni
                    where
                        equip_index_moni(mac_no, plan_no, koutei, date_time) >  '{$this->mac_no}{$this->plan_no}{$this->koutei}00000000000000'
                    and
                        equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_strTime}'
                    order by
                        equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                    offset 0 limit 1
        ";
        */
        $date = substr($this->graph_strTime, 0, 8);
        $time = substr($this->graph_strTime, 8, 6);
        $this->xdata = array(0 => 0);      // 累積時間(hr)
        $this->ydata = array();
        $rui_time = array();
        $res = array();
        $r = 0;
        if (getResultTrs($con, $query, $res) <= 0) {
            // 直前のデータ(初回データに使用)がない場合
            $query_exc = " select mac_state
                                , work_cnt
                            from
                                equip_work_log2_moni
                            where
                                plan_no='{$this->plan_no}' and mac_no={$this->mac_no} and koutei={$this->koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') > {$this->graph_strTime} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <= {$this->graph_endTime}
                            order by
                                date_time ASC
                            offset 0 limit 1
            ";
            /*
            $query_exc = " select mac_state
                                , work_cnt
                            from
                                equip_work_log2_moni
                            where
                                equip_index_moni(mac_no, plan_no, koutei, date_time) >  '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_strTime}'
                            and
                                equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_endTime}'
                            order by
                                equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
                            offset 0 limit 1
            ";
            */
            if (getResultTrs($con, $query_exc, $res) <= 0) {
                // それでも無い場合は電源OFF 生産数0
                $res[0][0] = 0;
                $res[0][1] = 0;
            }
        }
        if ($max_work_cnt < $res[0][1]) {
            // 直前のwork_cntの方が大きければmax_work_cntにセットしなおす
            $this->yaxis_min_data = yaxis_min($res[0][1]); // work_counter の最大加工数からグラフの最小値を算出
        }
        for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
            if ($res[0][0] == $i) {
                $this->ydata[$i][$r] = $res[0][1];         // 状態が一致した物はwork_cntをセット
            } else {
                $this->ydata[$i][$r] = $this->yaxis_min_data;    // 違うものは最小値をセットする。
            }
        }
        $r++;
        @$this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // 分を時間へ変換
        @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // 分を時間へ変換
        $query_now = "select to_char( (CAST('$date $time' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYYMMDDHH24MISS')";
        getUniResTrs($con, $query_now, $now_time);
        $date = substr($now_time, 0, 8);
        $time = substr($now_time, 8, 6);
        ////////// 2回目以降はループで処理
        $mac_state = $res[0][0];
        $work_cnt  = $res[0][1];
        while (1) {
            $query = " select mac_state
                            , work_cnt
                        from
                            equip_work_log2_moni
                        where
                            plan_no='{$this->plan_no}' and mac_no={$this->mac_no} and koutei={$this->koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') >= {$this->graph_strTime} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <= {$now_time}
                        order by
                            date_time DESC
                        offset 0 limit 1
            ";
            /*
            $query = " select mac_state
                            , work_cnt
                        from
                            equip_work_log2_moni
                        where
                            equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$this->graph_strTime}'
                        and
                            equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$this->mac_no}{$this->plan_no}{$this->koutei}{$now_time}'
                        order by
                            equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                        offset 0 limit 1
            ";
            */
            getResultTrs($con, $query, $res);
            if (isset($res[0][0])) $mac_state = $res[0][0]; // データが無い場合は前のデータを使う
            if (isset($res[0][1])) $work_cnt  = $res[0][1];
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                if ($mac_state == $i) {
                    $this->ydata[$i][$r] = $work_cnt;         // 状態が一致した物はwork_cntをセット
                } else {
                    $this->ydata[$i][$r] = $this->yaxis_min_data;    // 違うものは最小値をセットする。
                }
            }
            $r++;
            @$this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // 分を時間へ変換
            @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // 分を時間へ変換
            $query = "select to_char( (CAST('$date $time' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYYMMDDHH24MISS')";
            getUniResTrs($con, $query, $now_time);
            $date = substr($now_time, 0, 8);
            $time = substr($now_time, 8, 6);
            ////////// 完了日時(又は現在時刻)かグラフの範囲終了でブレイク
            if ( ($now_time > $this->end_timestamp) || ($now_time > $this->graph_endTime) ) {
                break;
            }
        }
        ////////// 現在時刻を過ぎてもグラフ範囲が残っている場合に処理 (ブランクデータの生成)
        while ($now_time <= $this->graph_endTime) {
            @$this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // 分を時間へ変換
            @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // 分を時間へ変換
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                $this->ydata[$i][$r] = $this->yaxis_min_data;   // ログがないので最小値のみセットする。
            }
            $r++;
            $query = "select to_char( (CAST('$date $time' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYYMMDDHH24MISS')";
            getUniResTrs($con, $query, $now_time);
            if ($now_time > $this->graph_endTime) {
                break;
            }
            $date = substr($now_time, 0, 8);
            $time = substr($now_time, 8, 6);
        }
        /////////// トランザクション終了
        query_affected_trans($con, "commit; SET enable_seqscan TO 'on';");
        ///// status 毎の集計をして終了
        $this->state_summary();
    }
    ////////////////////////////////////////////////////////////////////////////
    ////////// status 毎の集計データ生成                                      //
    ////////////////////////////////////////////////////////////////////////////
    protected function state_summary()
    {
        $this->rui_state = array();
        for ($r=0; $r<$this->sampling; $r++) {      // 各状態毎の累積時間を算出
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                if ($this->ydata[$i][$r] > $this->yaxis_min_data) {  // work_cntがセットされていれば時間を累積する
                    @$this->rui_state[$i] += $this->sample_time;     // 初期化していないので頭に@
                } else {
                    @$this->rui_state[$i] += 0;
                }
            }
        }
    }
    
} // class EquipGraph End

?>
