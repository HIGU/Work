<?php
//////////////////////////////////////////////////////////////////////////////
// 組立のライン別工数 各種グラフ                             MVC Model 部   //
// Copyright (C) 2006-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created   assembly_time_graph_Model.php                       //
// 2006/05/19 getTargetSupportTimeValues() の初期値を追加 $defaultTime      //
// 2006/06/15 明細を合計明細と工程明細にロジックで分けた(ListとDetaileList) //
// 2006/09/10 getTargetDateYMvalues($request)メソッドを前月から対応へ変更   //
// 2006/09/15 グラフの開始日・終了日のオフセット処理追加(１日単位の頁送り)  //
//            getGraphData()メソッドをタイムスタンプ化し月またがりに対応    //
// 2006/09/17 開始日・終了日だけでなく全体(両方)を追加 (開始・全体・終了)   //
// 2006/09/27 明細表示順 ORDER BY chaku, plan_no ASC を追加                 //
// 2006/09/27 グラフタイプ(工数計算方法)のオプション(工数日割り計算)追加    //
// 2006/09/28 getGraphData()メソッド内の day_off()→day_off_line()へ変更    //
// 2006/09/29 chaku, plan_no ASC → chaku ASC, kanryou ASC, plan_no ASC へ  //
// 2006/11/02 getViewHTMLgraph()メソッドにグラフの倍率(スケール)設定 を追加 //
// 2006/11/04 $option = '\n'; → $option = "\n"; タイプミスを修正３箇所     //
// 2006/11/06 複数ライン指定に対応 メンバー lineArray, targetLine, titleLine//
// 2007/01/16 過去工数のグラフ表示ON/OFF追加 pastDataView,getGraphData()変更//
// 2007/02/02 工程明細リストを親行番号だけにしたgetViewHTMLdetaileTable 変更//
// 2007/06/20 浅香の工数グラフを強制的に表示のため追加 getTargetLineValues()//
// 2007/11/08 工程明細の工程記号欄にalign='center'→align='left'へ変更      //
// 2016/06/30 4ヶ月先まで表示に変更                                    大谷 //
// 2020/06/25 合計工数を追加                                           大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class AssemblyTimeGraph_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句(今回はストアードプロシージャー用のパラメーター)
    private $lineArray;                         // 複数ラインのSQL ARRAY[] 指定用
    private $kousuSumList = 0;                  // グラフの明細表示時の合計工数格納
    private $targetLine = '';                   // 代表ライン
    private $titleLine = '';                    // 複数ラインのグラフタイトルに対応
    private $pastDataView = false;              // 前日までの工数もグラフ化 する(true)/しない(false)
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        /////リクエストにより初期化
        if ($request->get('targetPastData') == 1) $this->pastDataView = true;
        ///// 基本WHERE区の設定
        switch ($request->get('showMenu')) {
        case 'Graph':
        case 'List':
        case 'DetaileList':
            $this->where = $this->SetInitWhere($request);
            $this->targetLine = $this->setTargetLine($request);
            $this->titleLine = $this->setTitleLine($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ///// 複数指定の配列から代表ラインを設定
    public function setTargetLine($request)
    {
        $targetLineArray = $request->get('targetLine');
        return $targetLineArray[0];
    }
    
    ///// 複数指定の配列からグラフ用のライン名称を設定
    public function setTitleLine($request)
    {
        $array = $request->get('targetLine');
        $title = '';
        for ($i=0; $i<count($array); $i++) {
            $title .= $array[$i] . ' ';
        }
        return $title;
    }
    
    ///// 対象年月のHTML <select> option の出力
    public function getTargetDateYMvalues($request)
    {
        // 初期化
        $option = "\n";
        $yyyy = date('Y'); $mm = date('m');
        // $yyyymm = date('Ym');    // 当月からだったのを下記のように前月からに変更
        // 前月からに変更 $mm-1=0は前年の1月と同じ 2006/09/10
        $yyyy = date('Y', mktime(0, 0, 0, $mm-1, 1, $yyyy)); $mm = date('m', mktime(0, 0, 0, $mm-1, 1, $yyyy));
        $yyyymm = $yyyy . $mm ;
        for ($i=0; $i<=5; $i++) {   // ４ヶ月先まで(前月からなので5)
            if ($request->get('targetDateYM') == $yyyymm) {
                $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
            } else {
                $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
            }
            $mm++;
            if ($mm > 12) {
                $mm = 1; $yyyy += 1;
            }
            $mm = sprintf('%02d', $mm);
            $yyyymm = $yyyy . $mm;
        }
        return $option;
    }
    
    ///// 対象 組立ラインのHTML <select> option の出力
    public function getTargetLineValues($request)
    {
        // 指定年月内のライン番号の配列を取得
        $rows = $this->getViewLineList($request, $arrayLineNo);
        // 初期化
        $option = "\n";
        for ($i=0; $i<$rows; $i++) {
            // if ($request->get('targetLine') == $arrayLineNo[$i]) {   // 2006/11/06 単独指定から複数指定に対応
            if (array_search($arrayLineNo[$i], $request->get('targetLine')) !== false) {
                $option .= "<option value='{$arrayLineNo[$i]}' selected>" . mb_convert_kana($arrayLineNo[$i], 'RN') . "</option>\n";
            } else {
                $option .= "<option value='{$arrayLineNo[$i]}'>" . mb_convert_kana($arrayLineNo[$i], 'RN') . "</option>\n";
            }
        }
        $option .= "<option value='3LG1'>３ＬＧ１</option>\n";  // 2007/06/20 浅香の工数グラフを強制的に表示のため追加
        return $option;
    }
    
    ///// 組立ラインの１人の持工数のHTML <select> option の出力
    public function getTargetSupportTimeValues($request)
    {
        // 初期化
        $option = "\n";
        for ($i=(440-60); $i<=620; $i+=5) {  // 440分から5分刻みで3時間残業まで(パートの場合のため-60追加)
            if ($request->get('targetSupportTime') == '') $defaultTime = 440; else $defaultTime = $request->get('targetSupportTime');
            if ($defaultTime == $i) {
                $option .= "<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "</option>\n";
            } else {
                $option .= "<option value='{$i}'>" . mb_convert_kana($i, 'N') . "</option>\n";
            }
        }
        return $option;
    }
    
    ///// Graph部    グラフ作成指示
    public function outViewGraphHTML($request, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLgraph($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "graph/assembly_time_graph_ViewGraph-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    クリックされたグラフデータの明細 一覧表
    public function outViewListHTML($request, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLtable($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/assembly_time_graph_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
                /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter($request);
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/assembly_time_graph_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// List部    クリックされたグラフデータの 工程明細 一覧表
    public function outViewDetaileListHTML($request, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLdetaileTable($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/assembly_time_graph_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
                /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter($request);
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/assembly_time_graph_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// 製品のコメントを保存
    public function commentSave($request)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        // if ($request->get('comment') == '') return;  // これを行うと削除できない
        if ($request->get('targetPlanNo') == '') return;
        if ($request->get('targetAssyNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if (getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO assembly_time_plan_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        } else {
            $sql = "
                UPDATE assembly_time_plan_comment SET comment='{$request->get('comment')}',
                last_date='{$last_date}', last_host='{$last_host}'
                WHERE plan_no='{$request->get('targetPlanNo')}'
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        }
        return ;
    }
    
    ///// 製品のコメントを取得
    public function getComment($request, $result)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        if ($request->get('targetAssyNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            assembly_time_plan_comment ON(mipn=assy_no)
            WHERE mipn='{$request->get('targetAssyNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $result->add('comment', $res[0][0]);
            $result->add('assy_name', $res[0][1]);
            $result->add('title', "{$request->get('targetPlanNo')}　{$request->get('targetAssyNo')}：{$res[0][1]}");
            return true;
        } else {
            return false;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function SetInitWhere($request)
    {
        // ストアードプロシージャーの形式
        // SELECT * FROM assembly_schedule_time_line($request->get('targetDateStr'), $request->get('targetDateEnd'), '$request->get('targetLine')')
        $array = $request->get('targetLine');
        for ($i=0; $i<count($array); $i++) {
            if ($i == 0) $lineArray = "'{$array[$i]}'";
            $lineArray .= ", '{$array[$i]}'";
        }
        $this->lineArray = $lineArray;  // プロパティーへ登録
        if ($request->get('showMenu') == 'Graph') {
            $where = "{$request->get('targetDateStr')}, {$request->get('targetDateEnd')}, ARRAY[{$this->lineArray}]";
        } else {
            $where = "{$request->get('targetDateList')}, {$request->get('targetDateList')}, ARRAY[{$this->lineArray}]";
        }
        return $where;
    }
    
    ///// List部    組立ライン $this->where(条件)内での 一覧表 (ページコントロールなし)
    protected function getViewLineList($request, &$arrayLineNo)
    {
        $query = "
            SELECT
                line_no            AS ライン番号           -- 00
            FROM
                assembly_schedule
            WHERE
                kanryou >= {$request->get('targetDateYM')}01 AND kanryou <= {$request->get('targetDateYM')}31 AND assy_site = '01111' AND parts_no != '999999999' AND p_kubun = 'F'
                AND trim(line_no) != ''
            GROUP BY
                line_no
            ORDER BY
                line_no ASC
        ";
        $res = array();
        $arrayLineNo = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = 'ライン番号の登録がありません！';
        }
        for ($i=0; $i<$rows; $i++) {
            $arrayLineNo[$i] = $res[$i][0];
        }
        return $rows;
    }
    
    ///// 計画番号から製品番号・製品名・計画数・完成数を取得
    protected function getPlanData($request, &$res)
    {
        // 計画番号から製品番号の取得(実績データの無い場合の対応)
        $query = "SELECT parts_no       AS 製品番号     -- 00
                        ,substr(midsc, 1, 20)
                                        AS 製品名       -- 01
                        ,plan-cut_plan  AS 計画数       -- 02
                        ,kansei         AS 完成数       -- 03
                    FROM assembly_schedule
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    WHERE plan_no='{$request->get('targetPlanNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $res['assy_no']   = $res[0][0];
            $res['assy_name'] = $res[0][1];
            $res['keikaku']   = $res[0][2];
            $res['kansei']    = $res[0][3];
            return true;
        } else {
            $res['assy_no']   = '';
            $res['assy_name'] = '';
            $res['keikaku']   = '';
            $res['kansei']    = '';
            return false;
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// Graph部   組立のライン別工数グラフ
    private function getViewHTMLgraph($request, $menu)
    {
        $rows = $this->getGraphData($request, $graphDataX, $graphDataY, $graphDataY_kousu);
        if ($rows <= 0) {
            // 初期化
            $listTable = '';
            $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
            $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>組立日程計画データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            return $listTable;
        }
        require_once ('../../../../jpgraph-4.4.1/src/jpgraph.php');
        require_once ('../../../../jpgraph-4.4.1/src/jpgraph_bar.php');
        ///// グラフの倍率(スケール)設定
        $width  = (int)(830 * $request->get('targetScale'));
        $height = (int)(500 * $request->get('targetScale'));
        $graph = new Graph($width, $height);               // グラフの大きさ X/Y
        $graph->SetScale('textlin'); 
        $graph->img->SetMargin(55, 30, 40, 80);     // グラフ位置のマージン 左右上下
        $graph->SetShadow(); 
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 16);
        $graph->title->Set(mb_convert_encoding("{$this->titleLine}ラインの日計工数グラフ", 'UTF-8')); 
        $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->yaxis->title->Set(mb_convert_encoding('人数', 'UTF-8'));
        $graph->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph->xaxis->SetTickLabels($graphDataX);  // 項目設定
        $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot = new BarPlot($graphDataY); 
        $bplot->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot->SetFillGradient('darkgreen', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot->SetColor('navy');
        $bplot->value->SetFormat('%0.1f');          // 少数１位フォーマット
        $bplot->value->Show();                      // 数値表示
        // Set CSIMTarget
        $targ = array();
        $alts = array();
        $total_kousu = 0;
        for ($i=0; $i<$rows; $i++) {
            $targ[$i] = "JavaScript:AssemblyTimeGraph.win_open('{$menu->out_self()}?showMenu=List&targetDateList={$graphDataX[$i]}&noMenu=yes', 950, 600)";
            $alts[$i] = "合計工数は {$graphDataY_kousu[$i]}分です。";
            $total_kousu = $total_kousu + $graphDataY_kousu[$i];
        }
        $graph->title->Set(mb_convert_encoding("{$this->titleLine}ラインの日計工数グラフ 合計工数：{$total_kousu}", 'UTF-8')); 
        $bplot->SetCSIMTargets($targ, $alts); 
        $graph->Add($bplot);
        $graph_name = "graph/assembly_time_graph_{$_SESSION['User_ID']}.png";
        $graph->Stroke($graph_name);
        chmod($graph_name, 0666);                   // fileを全てrwモードにする
        
        $listTable = "\n";
        $listTable .= "<table width='100%' border='0'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td align='left'>\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetStr1' value='←１日前' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=-1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "            開始\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetStr2' value='１日後→' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=+1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "        <td align='center'>\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetStr3' value='<<１日前' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=-1\"; parent.AssemblyTimeGraph.parameter+=\"&targetOffsetEnd=-1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "            全体\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetEnd3' value='１日後>>' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetStr=+1\"; parent.AssemblyTimeGraph.parameter+=\"&targetOffsetEnd=+1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "        <td align='right'>\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetEnd1' value='←１日前' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetEnd=-1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "            終了\n";
        $listTable .= "            <input class='pageButton' type='button' name='offsetEnd2' value='１日後→' onClick='parent.AssemblyTimeGraph.parameter=\"&targetOffsetEnd=+1\"; parent.AssemblyTimeGraph.AjaxLoadTable(\"Graph\", \"showAjax\");'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td align='center' colspan='3'>\n";
        $listTable .= "            {$graph->GetHTMLImageMap('kousu_map')}\n";
        $listTable .= "            <img src='assembly_time_graph_{$_SESSION['User_ID']}.png" . '?id=' . time() . "' ismap usemap='#kousu_map' alt='ライン別 工数 グラフ' border='0'>\n";
        $listTable .= "        </td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    
    ///// Get部   指定ラインの日付別 合計工数データを取得
    private function getGraphData($request, &$graphDataX, &$graphDataY, &$graphDataY_kousu)
    {
        $rows = 0;
        $graphDataX = array();
        $graphDataY = array();
        $strTimeStamp = mktime(0, 0, 0, substr($request->get('targetDateStr'), 4, 2), substr($request->get('targetDateStr'), 6, 2), substr($request->get('targetDateStr'), 0, 4));
        $endTimeStamp = mktime(0, 0, 0, substr($request->get('targetDateEnd'), 4, 2), substr($request->get('targetDateEnd'), 6, 2), substr($request->get('targetDateEnd'), 0, 4));
        for ($i=$strTimeStamp; $i<=$endTimeStamp; $i+=86400) {
            if (day_off_line($i, $this->targetLine)) {
                continue;
            }
            $date = date('Ymd', $i);
            if ($request->get('targetGraphType') == 'avr') {
                $query = "SELECT sum(kousu_sum) FROM assembly_schedule_time_lineArray_average({$date}, {$date}, ARRAY[{$this->lineArray}])";
            } else {
                $query = "SELECT sum(kousu_sum) FROM assembly_schedule_time_lineArray({$date}, {$date}, ARRAY[{$this->lineArray}])";
            }
            $kousu = 0;
            if ($this->pastDataView || $date >= date('Ymd')) {
                $this->getUniResult($query, $kousu);
            }
            $graphDataX[] = substr($date, 2, 2) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2);
            $graphDataY[] = Uround($kousu / $request->get('targetSupportTime'), 1);
            $graphDataY_kousu[] = Uround($kousu, 1);
            $rows++;
        }
        return $rows;
    }
    
    ///// List部   組立のライン別工数グラフの 明細データ作成
    private function getViewHTMLtable($request, $menu)
    {
        $query = $this->getQueryStatement($request);
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>組立日程計画データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                // if ($res[$i][10] != '') {   // コメントがあれば色を変える
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                // } else {
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                // }
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 3%' align='right' >" . ($i+1) . "</td>\n";                    // 行番号
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";                     // 計画番号
                $listTable .= "        <td class='winbox' width='13%' align='center'>{$res[$i][1]}</td>\n";                     // 製品番号
                $listTable .= "        <td class='winbox' width='22%' align='left'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // 製品名
                $listTable .= "        <td class='winbox' width=' 8%' align='right' >" . number_format($res[$i][3]) . "</td>\n";// 計画残数
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][4]}</td>\n";                     // 着手日
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][5]}</td>\n";                     // 完了日
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][6]}</td>\n";                     // 工番
                $listTable .= "        <td class='winbox' width='10%' align='right' >{$res[$i][7]}</td>\n";                     // 合計工数
                $listTable .= "    </tr>\n";
                $this->kousuSumList += $res[$i][7];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   組立のライン別工数グラフの 工程 明細データ作成
    private function getViewHTMLdetaileTable($request, $menu)
    {
        $query = $this->getQueryStatement2($request);
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>組立日程計画データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $res[-1][0] = '';   // ダミー用に初期化
            $j          = 0;    // 計画番号単位の行番号(親行番号)
            for ($i=0; $i<$rows; $i++) {
                // if ($res[$i][10] != '') {   // コメントがあれば色を変える
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                // } else {
                //     $listTable .= "    <tr onDblClick='AssemblyTimeGraph.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                // }
                $listTable .= "    <tr>\n";
                if ($res[$i][0] == $res[$i-1][0]) { // 親行番号・親計画・親製品・親製品名 等の表示だけに変更
                    $listTable .= "        <td class='winbox' width=' 3%' align='right' >&nbsp;</td>\n";                    // 行番号なし
                    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";                    // 計画番号なし
                    $listTable .= "        <td class='winbox' width='13%' align='center'>&nbsp;</td>\n";                    // 製品番号なし
                    $listTable .= "        <td class='winbox' width='13%' align='left'>&nbsp;</td>\n";                      // 製品名なし
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' >&nbsp;</td>\n";                    // 計画残数なし
                    $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";                    // 着手日なし
                    $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";                    // 完了日なし
                    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";                    // 工番なし
                } else {
                    $j++;   // 親行番号
                    $listTable .= "        <td class='winbox' width=' 3%' align='right' >" . $j . "</td>\n";                    // 行番号
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";                 // 計画番号
                    $listTable .= "        <td class='winbox' width='13%' align='center'>{$res[$i][1]}</td>\n";                 // 製品番号
                    $listTable .= "        <td class='winbox' width='13%' align='left'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // 製品名
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' >" . number_format($res[$i][3]) . "</td>\n";// 計画残数
                    $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][4]}</td>\n";                 // 着手日
                    $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][5]}</td>\n";                 // 完了日
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][6]}</td>\n";                 // 工番
                }
                $listTable .= "        <td class='winbox' width=' 6%' align='right' >{$res[$i][7]}</td>\n";                     // 工程番号
                $listTable .= "        <td class='winbox' width=' 6%' align='left'  >{$res[$i][8]}</td>\n";                     // 工程記号
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >{$res[$i][9]}</td>\n";                     // 合計工数
                $listTable .= "    </tr>\n";
                $this->kousuSumList += $res[$i][9];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   組立のライン別工数グラフの 明細データ フッター部を作成
    private function getViewHTMLfooter($request)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        if ($request->get('showMenu') == 'List') {
            $listTable .= "        <td class='winbox' width='90%' align='right'>総合計工数(分)</td>\n";
            $listTable .= "        <td class='winbox' width='10%' align='right'>" . number_format($this->kousuSumList, 1) . "</td>\n";
        } else {
            $listTable .= "        <td class='winbox' width='93%' align='right'>総合計工数(分)</td>\n";
            $listTable .= "        <td class='winbox' width=' 7%' align='right'>" . number_format($this->kousuSumList, 1) . "</td>\n";
        }
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   ライン別 組立日程計画 工数 一覧表
    private function getQueryStatement($request)
    {
        $query = "
            SELECT   plan_no        AS 計画番号         -- 00
                    ,assy_no        AS 製品番号         -- 01
                    ,substr(midsc, 1, 16)
                                    AS 製品名           -- 02
                    ,zan_pcs        AS 計画残数         -- 03
                    ,to_char(chaku, 'FM0000/00/00')
                                    AS 着手日           -- 04
                    ,to_char(kanryou, 'FM0000/00/00')
                                    AS 完了日           -- 05
                    ,CASE
                        WHEN trim(note15) = '' THEN '&nbsp;'
                        ELSE substr(note15, 1, 8)
                     END            AS 工番             -- 06
                    ,sum(Uround(kousu_sum, 1))
                                    AS 合計工数         -- 07
            FROM
        ";
        if ($request->get('targetGraphType') == 'avr') {
            $query .= "    assembly_schedule_time_lineArray_average({$this->where})";
        } else {
            $query .= "    assembly_schedule_time_lineArray({$this->where})";
        }
        $query .= "
            LEFT OUTER JOIN
                miitem ON (assy_no=mipn)
            -- LEFT OUTER JOIN
            --     assembly_schedule_time_line_comment USING (plan_no, line_no)
            GROUP BY
                plan_no, assy_no, midsc, zan_pcs, chaku, kanryou, note15
            ORDER BY
                chaku ASC, kanryou ASC, plan_no ASC
        ";
        return $query;
    }
    
    ///// List部   ライン別 組立日程計画 工程明細 工数 一覧表
    private function getQueryStatement2($request)
    {
        $query = "
            SELECT   plan_no        AS 計画番号         -- 00
                    ,assy_no        AS 製品番号         -- 01
                    ,substr(midsc, 1, 16)
                                    AS 製品名           -- 02
                    ,zan_pcs        AS 計画残数         -- 03
                    ,to_char(chaku, 'FM0000/00/00')
                                    AS 着手日           -- 04
                    ,to_char(kanryou, 'FM0000/00/00')
                                    AS 完了日           -- 05
                    ,CASE
                        WHEN trim(note15) = '' THEN '&nbsp;'
                        ELSE substr(note15, 1, 8)
                     END            AS 工番             -- 06
                    ,pro_no         AS 工程番号         -- 07
                    ,pro_mark       AS 工程記号         -- 08
                    ,Uround(kousu_sum, 1)
                                    AS 合計工数         -- 09
            FROM
        ";
        if ($request->get('targetGraphType') == 'avr') {
            $query .= "    assembly_schedule_time_lineArray_average({$this->where})";
        } else {
            $query .= "    assembly_schedule_time_lineArray({$this->where})";
        }
        $query .= "
            LEFT OUTER JOIN
                miitem ON (assy_no=mipn)
            -- LEFT OUTER JOIN
            --     assembly_schedule_time_line_comment USING (plan_no, line_no)
            ORDER BY
                chaku ASC, kanryou ASC, plan_no ASC, pro_no ASC
        ";
        return $query;
    }
    
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status)
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>組立のライン別工数グラフ</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../assembly_time_graph.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../assembly_time_graph.js'></script>
</head>
<body style='background-color:#d6d3ce;'>
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = 
"
</center>
</body>
</html>
";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
} // Class AssemblyTimeGraph_Model End

?>
