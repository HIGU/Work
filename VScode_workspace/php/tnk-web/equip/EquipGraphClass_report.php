<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理用 運転日報 対応版 Graph Create Class Report                 //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/08/23 Created   EquipGraphClass_report.php                          //
//            jpGraph-1.16 base created  modify jpgraph_equip.php           //
//            function SetTextLabelInterval($aStep)                         //
//                                  $graph->xaxis->SetTextLabelInterval(2); //
//            function SetTextTickInterval($aStep,$aStart)                  //
//                                 $graph->xaxis->SetTextTickInterval(1,2); //
//            最終的にSetTextTickInterval(Step数, Start位置)を使用した      //
//            EquipGraphClass_report.php  based create.                     //
// 2004/08/23 jpgraph.php -> jpgraph_equip.php に変更してY軸の(-)表示削除   //
// 2004/08/26 直前のwork_cntとmax_work_cntを比較($max_work_cnt < $res[0][1])//
//    Ver1.01 yaxis_min()に与えるデータを決定するように変更                 //
// 2004/08/30 2ページ移行のoffset処理を追加(バグ修正)                       //
//    Ver1.02 $xdata += ($this->xtime * ($this->graph_page - 1))            //
//            日報EndとグラフEndを比較してmax_work_cntを決定するように変更  //
// 2004/09/01 Ver1.03 out_graph()にオプションでサマリー結果を書込む機能追加 //
// 2005/05/20 db_connect() → funcConnect() へ変更 pgsql.phpで統一のため    //
// 2005/08/30 php5 へ移行  Ver1.04 (=& new → = new, var → private)        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// require_once ('../function.php');
require_once ('equip_function.php');
require_once ('/home/www/html/jpgraph_equip.php');
require_once ('/home/www/html/jpgraph_line.php');

if (class_exists('EquipGraphReport')) {
    return;
}
define('EGR_VERSION', '1.04');

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
class EquipGraphReport
{
    ///// Private properties
    private $mac_no;                    // 機械番号
    private $str_timestamp;             // 開始日時(Header) 'YYYY/MM/DD HH24:MI:SS'
    private $end_timestamp;             // 終了日時(Header OR CURRENT_TIMESTAMP)
    private $xtimeArrVal;               // X軸の時間をセットできる値の設定名称をkeyとする連想配列
    private $xtime;                     // X軸の時間範囲(6/12/24/48...hr)
    private $graph_strTime;             // 指定範囲の開始日時 'YYYY/MM/DD HH24:MI:SS'
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
    private $graph_page;                // グラフを表示するページ番号(実ページ数 1.2.3...)
    private $forward;                   // 次ページのフラグ
    private $backward;                  // 前ページのフラグ
    private $rui_state;                 // state 毎の累計を保存する 配列
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5へ移行時は __construct() へ変更予定)
    function EquipGraphReport($mac_no, $date='', $sampling=180)
    {
        if (!isset($_SESSION)) {                    // セッションの開始チェック
            session_start();                        // Notice を避ける場合は頭に@
        }
        if ($this->set_condition($date, $mac_no) == false) {
            $addr_status = "ErrorEquipGraphPage.php?status=2&date=$date&mac_no=$mac_no";
            header('Location: ' . H_WEB_HOST . ERROR . $addr_status);
            exit();
        }
        $this->set_xtimeArrVal();               // X軸のセットできる配列の初期化
        $this->xtime = 24;                      // 初期化 Default=24hr (日報ベース)
        $this->set_graph_page();                // 初期化 Default=0 表示ページのoffsetページ数(-1/0/1/2/3...)
        $this->set_graphWH();                   // 初期化
        $this->set_title();                     // 初期化
        $this->set_sampling($sampling);         // 初期化
    }
    
    /*************************** Set & Check methods ************************/
    // 機械番号・指示番号・工程番号のチェックと設定及び開始終了日時の取得
    function set_condition($date, $mac_no)
    {
        if ($mac_no  == '') return false;
        if ($date == '') {
            $date = date('Y/m/d', mktime() - 86400);    // 前日にセット
        }
        $query = " select to_char(CAST('{$date} 08:30:00' AS TIMESTAMP), 'YYYY/MM/DD HH24:MI:SS') AS str_timestamp
                        , CASE
                            WHEN (CAST('{$date} 08:30:00' AS TIMESTAMP) + interval '24 hour') > CURRENT_TIMESTAMP THEN
                                to_char(CURRENT_TIMESTAMP, 'YYYY/MM/DD HH24:MI:SS')
                            ELSE
                                to_char(CAST('{$date} 08:30:00' AS TIMESTAMP) + interval '24 hour', 'YYYY/MM/DD HH24:MI:SS')
                          END
                            AS end_timestamp
        ";
        $res = array();
        if (getResult($query, $res) <= 0) {
            return false;
        } else {
            $this->str_timestamp = $res[0]['str_timestamp'];
            $this->end_timestamp = $res[0]['end_timestamp'];
        }
        $this->mac_no = $mac_no;
        return true;
    }
    // X軸(時間軸)の設定
    function set_xtime($xtime=24)
    {
        $unset = true;
        foreach ($this->xtimeArrVal as $time) {
            if ($xtime <= $time) {
                $xtime = $time;
                $unset = false;
                break;
            }
        }
        if ($unset) {
            $xtime = 24;   // 見つからない場合はDefault値
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
    function set_title($title='生産数 状態 運転日報グラフ')
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
        $strDate = substr($this->graph_strTime,  0, 10);
        $strTime = substr($this->graph_strTime, 11,  8);
        $endDate = substr($this->graph_endTime,  0, 10);
        $endTime = substr($this->graph_endTime, 11,  8);
        return array('strDate' => "$strDate", 'strTime' => "$strTime", 'endDate' => "$endDate", 'endTime' => "$endTime");
    }
    // ロット全体の開始日時の出力 (format付)  出力書式=配列(strDate, strTime, endDate, endTime)
    function out_lot_timestamp()
    {
        $strDate = substr($this->str_timestamp,  0, 10);
        $strTime = substr($this->str_timestamp, 11,  8);
        $endDate = substr($this->end_timestamp,  0, 10);
        $endTime = substr($this->end_timestamp, 11,  8);
        return array('strDate' => "$strDate", 'strTime' => "$strTime", 'endDate' => "$endDate", 'endTime' => "$endTime");
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
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->title->Set(mb_convert_encoding($this->title, 'UTF-8'));
        $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 10);
        $graph->subtitle->Set(mb_convert_encoding("{$this->graph_strTime} ～ {$this->graph_endTime}  {$this->graph_page}ページ", 'UTF-8'));
        $graph->subtitle->SetAlign('right');
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
    function set_xtimeArrVal()
    {
        for ($i=2; $i<=24; $i+=2) {                 // 2時間～24時間
            $this->xtimeArrVal["{$i}時間"] = $i;
        }
        /************   // これはEquipGraphClassの場合
        for ($i=48; $i<=1440; $i+=24) {             // 2日間～60日間
            $day = ($i / 24);
            $this->xtimeArrVal["{$day}日間"] = $i;
        }
        ************/
    }
    // グラフ化する開始時間と終了時間の設定
    function set_graph_strTime($graph_page=1)
    {
        if ($graph_page <= 1) {
            $this->backward = false;
        } else {
            $this->backward = true;
        }
        $xtime = ($this->xtime * ($graph_page - 1) );
        $query = "select to_char( (CAST('{$this->str_timestamp}' AS TIMESTAMP) + interval '$xtime hour'), 'YYYY/MM/DD HH24:MI:SS')";
        getUniResult($query, $strTime);
        $this->graph_strTime = $strTime;
        $this->set_graph_endTime($this->graph_strTime, $this->xtime);
    }
    // グラフ化する終了時間の設定
    function set_graph_endTime($strTime, $xtime)
    {
        $query = "select to_char( (CAST('{$strTime}' AS TIMESTAMP) + interval '$xtime hour'), 'YYYY/MM/DD HH24:MI:SS')";
        getUniResult($query, $endTime);
        $this->graph_endTime = $endTime;
        if ($this->end_timestamp > $this->graph_endTime) {
            $this->forward = true;
        } else {
            $this->forward = false;
        }
    }
    // グラフのサンプリング間隔の設定
    function set_sample_time()
    {
        $this->sample_time = (($this->xtime * 60) / $this->sampling);   // 例 (12*60)/180=4分間隔(default)
    }
    // グラフのX軸(時間)の scale用 倍率設定
    function set_multiply()
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
    function generate_data()
    {
        /////////// begin トランザクション開始
        if ($con = funcConnect()) {
            query_affected_trans($con, 'begin');
        }
        if ($this->end_timestamp > $this->graph_endTime) {
            $graph_end = $this->graph_endTime;
        } else {
            $graph_end = $this->end_timestamp;
        }
        ////////// 指定範囲内にデータがあるかチェック
        $query = " select work_cnt
                    from
                        equip_work_log2
                    where
                        equip_index2(mac_no, date_time) >= '{$this->mac_no}{$this->graph_strTime}'
                        and
                        equip_index2(mac_no, date_time) <= '{$this->mac_no}{$graph_end}'
                    offset 0 limit 1
        ";
        if (getUniResTrs($con, $query, $max_work_cnt) <= 0) {
            $empty = true;
        } else {
            $empty = false;
        }
        ////////// 指定範囲内での最大生産数を取得してyaxis_min_dataを設定する
        if ($empty == false) {
            // 指定範囲にデータがある場合の処理
            $query = " select max(work_cnt)
                        from
                            equip_work_log2
                        where
                            equip_index2(mac_no, date_time) >= '{$this->mac_no}{$this->graph_strTime}'
                            and
                            equip_index2(mac_no, date_time) <= '{$this->mac_no}{$graph_end}'
            ";
            getUniResTrs($con, $query, $max_work_cnt);
        } else {
            // 指定範囲にデータがない場合の処理
            $query = " select work_cnt
                        from
                            equip_work_log2
                        where
                            equip_index2(mac_no, date_time) < '{$this->mac_no}{$this->graph_strTime}'
                        order by
                            equip_index2(mac_no, date_time) DESC
                        offset 0 limit 1
            ";
            if (getUniResTrs($con, $query, $max_work_cnt) <= 0) {
                $max_work_cnt = 0;
            }
        }
        $this->yaxis_min_data = yaxis_min($max_work_cnt);     // work_counter の最大加工数からグラフの最小値を算出
        ////////// 初回データの取得 (初回はgraph_strTime以下のデータを1件取得)
        $query = " select mac_state
                        , work_cnt
                        , siji_no
                        , koutei
                    from
                        equip_work_log2
                    where
                        equip_index2(mac_no, date_time) <= '{$this->mac_no}{$this->graph_strTime}'
                    order by
                        equip_index2(mac_no, date_time) DESC
                    offset 0 limit 1
        ";
        $this->xdata = array(0 => 0);      // 累積時間(hr)
        $this->ydata = array();
        $rui_time = array();
        $res = array();
        $r = 0;
        if (getResultTrs($con, $query, $res) <= 0) {
            // 直前のデータ(初回データに使用)がない場合 (初回の機械)
            $query_exc = " select mac_state
                                , work_cnt
                                , siji_no
                                , koutei
                            from
                                equip_work_log2
                            where
                                equip_index2(mac_no, date_time) > '{$this->mac_no}{$this->graph_strTime}'
                                and
                                equip_index2(mac_no, date_time) <= '{$this->mac_no}{$graph_end}'
                            order by
                                equip_index2(mac_no, date_time) ASC
                            offset 0 limit 1
            ";
            if (getResultTrs($con, $query_exc, $res) <= 0) {
                // それでも無い場合は電源OFF 生産数0
                $res[0][0] = 0;
                $res[0][1] = 0;
                $res[0][2] = 0;
                $res[0][3] = 0;
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
        $this->xdata[$r] = $this->xdata_offset($con, 0);    // 初回の分
        
        $r++;
        $this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // 分を時間へ変換
        $this->xdata[$r] = $this->xdata_offset($con, $this->xdata[$r]);
        @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // 分を時間へ変換
        $query_now = "select to_char( (CAST('{$this->graph_strTime}' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYY/MM/DD HH24:MI:SS')";
        getUniResTrs($con, $query_now, $now_time);
        ////////// 2回目以降はループで処理
        $mac_state = $res[0][0];
        $work_cnt  = $res[0][1];
        $siji_no   = $res[0][2];
        $koutei    = $res[0][3];
        while (1) {
            $query = " select mac_state
                            , work_cnt
                            , siji_no
                            , koutei
                        from
                            equip_work_log2
                        where
                            equip_index2(mac_no, date_time) >= '{$this->mac_no}{$this->graph_strTime}'
                            and
                            equip_index2(mac_no, date_time) <= '{$this->mac_no}{$now_time}'
                        order by
                            equip_index2(mac_no, date_time) DESC
                        offset 0 limit 1
            ";
            getResultTrs($con, $query, $res);
            if (isset($res[0][0])) $mac_state = $res[0][0]; // データが無い場合は前のデータを使う
            if (isset($res[0][1])) $work_cnt  = $res[0][1];
            if (isset($res[0][2])) $siji_no   = $res[0][2];
            if (isset($res[0][3])) $koutei    = $res[0][3];
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                if ($mac_state == $i) {
                    // ヘッダーで完了日のチェック
                    $query_chk = "select to_char(end_timestamp, 'YYYY/MM/DD HH24:MI:SS') from equip_work_log2_header where mac_no={$this->mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                    getUniResTrs($con, $query_chk, $end_timestamp);
                    if ( ($end_timestamp != '') && ($now_time > $end_timestamp) ) {
                        $this->ydata[$i][$r] = $this->yaxis_min_data;    // 完了されている物は最小値をセットする。
                    } else {
                        $this->ydata[$i][$r] = $work_cnt;         // 状態が一致した物はwork_cntをセット
                    }
                } else {
                    $this->ydata[$i][$r] = $this->yaxis_min_data;    // 違うものは最小値をセットする。
                }
            }
            $r++;
            $this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // 分を時間へ変換
            $this->xdata[$r] = $this->xdata_offset($con, $this->xdata[$r]);
            @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // 分を時間へ変換
            $query = "select to_char( (CAST('{$now_time}' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYY/MM/DD HH24:MI:SS')";
            getUniResTrs($con, $query, $now_time);
            $date = substr($now_time, 0, 10);
            $time = substr($now_time, 11, 8);
            ////////// 完了日時(又は現在時刻)かグラフの範囲終了でブレイク   (文字列の比較なので注意)
            if ( ($now_time > $this->end_timestamp) || ($now_time > $this->graph_endTime) ) {
                break;
            }
        }
        ////////// 現在時刻を過ぎてもグラフ範囲が残っている場合に処理 (ブランクデータの生成)
        while ($now_time <= $this->graph_endTime) {
            $this->xdata[$r] = round((@$rui_time[$r-1] + round($this->sample_time / 60, 6)), 0);  // 分を時間へ変換
            $this->xdata[$r] = $this->xdata_offset($con, $this->xdata[$r]);
            @$rui_time[$r] = (@$rui_time[$r-1] + round($this->sample_time / 60, 6));  // 分を時間へ変換
            for ($i=0; $i<=R_STAT_MAX_NO; $i++) {
                $this->ydata[$i][$r] = $this->yaxis_min_data;   // ログがないので最小値のみセットする。
            }
            $r++;
            $query = "select to_char( (CAST('{$now_time}' AS TIMESTAMP) + interval '{$this->sample_time} minute'), 'YYYY/MM/DD HH24:MI:SS')";
            getUniResTrs($con, $query, $now_time);
            if ($now_time > $this->graph_endTime) {
                break;
            }
        }
        /////////// トランザクション終了
        query_affected_trans($con, 'commit');
        ///// status 毎の集計をして終了
        $this->state_summary();
    }
    ////////////////////////////////////////////////////////////////////////////
    ////////// status 毎の集計データ生成                                      //
    ////////////////////////////////////////////////////////////////////////////
    function state_summary()
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
    ////////////////////////////////////////////////////////////////////////////
    ////////// X軸のラベルを日報時間に合わせるオフセット処理                  //
    ////////////////////////////////////////////////////////////////////////////
    function xdata_offset($con, $xdata)
    {
        if ($this->graph_page > 1) {
            $xdata += ($this->xtime * ($this->graph_page - 1));     // 2ページ移行のoffset処理
        }
        switch ($xdata) {
        CASE (0):
            return '08:30';
            break;
        CASE (1):
            return '09:30';
            break;
        CASE (2):
            return '10:30';
            break;
        CASE (3):
            return '11:30';
            break;
        CASE (4):
            return '12:30';
            break;
        CASE (5):
            return '13:30';
            break;
        CASE (6):
            return '14:30';
            break;
        CASE (7):
            return '15:30';
            break;
        CASE (8):
            return '16:30';
            break;
        CASE (9):
            return '17:30';
            break;
        CASE (10):
            return '18:30';
            break;
        CASE (11):
            return '19:30';
            break;
        CASE (12):
            return '20:30';
            break;
        CASE (13):
            return '21:30';
            break;
        CASE (14):
            return '22:30';
            break;
        CASE (15):
            return '23:30';
            break;
        CASE (16):
            return '00:30';
            break;
        CASE (17):
            return '01:30';
            break;
        CASE (18):
            return '02:30';
            break;
        CASE (19):
            return '03:30';
            break;
        CASE (20):
            return '04:30';
            break;
        CASE (21):
            return '05:30';
            break;
        CASE (22):
            return '06:30';
            break;
        CASE (23):
            return '07:30';
            break;
        CASE (24):
            return '08:30';
            break;
        default:
            return '';
            break;
        }
        /*****************************
        if ( $xdata == 24) {
            return '08:30';                     // 24時間後は下のSQLでエラーになるためリテラルで返す
        }
        for ($i=0; $i<24; $i++) {
            if ($i == $xdata) {
                $query = "select to_char( (CAST('{$xdata}:00' AS TIME) + interval '8:30 hour'), 'HH24:MI')";
                getUniResTrs($con, $query, $new_xdata);
                return $new_xdata;
            } else {
                return $xdata;                  // 範囲外又は整数でなければ値をそのまま返す
            }
        }
        *****************************/
    }
    
} // class EquipGraph End

?>
