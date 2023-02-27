<?php
//////////////////////////////////////////////////////////////////////////////
// 資材出庫時間の集計･分析 結果 照会                         MVC Model 部   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/30 Created   parts_pickup_analyze_Model.php                      //
// 2006/06/12 $option = '\n'; → $option = "\n"; タイプミスを修正           //
// 2006/06/14 リニア全体の出庫データを追加                                  //
// 2006/06/24 getUniResult() → $this->getUniResult() へ変更  137行目       //
// 2007/09/03 バーコード入力率の Division by zero 対応ロジックの追加        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PartsPickupAnalyze_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $last_avail_pcs;                    // 最終有効数(最終予定在庫数)
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        ///// 基本WHERE区の設定
        switch ($request->get('showMenu')) {
        case 'List':
        case 'ListWin':
            // $this->where = $this->SetInitWhere($request);
            // break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ///// 対象年月のHTML <select> option の出力
    public function getTargetDateYMvalues($request)
    {
        // 初期化
        $option = "\n";
        $yyyymm = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($request->get('targetDateYM') == $yyyymm) {
            $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
        } else {
            $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
        }
        for ($i=1; $i<=12; $i++) {   // 12ヶ月前まで
            $mm--;
            if ($mm < 1) {
                $mm = 12; $yyyy -= 1;
            }
            $mm = sprintf('%02d', $mm);
            $yyyymm = $yyyy . $mm;
            if ($request->get('targetDateYM') == $yyyymm) {
                $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
            } else {
                $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
            }
        }
        return $option;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    データの明細 一覧表
    public function outViewListHTML($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        /*****************
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_pickup_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        *****************/
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_pickup_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** フッター部を作成 *****/
        /************************
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_pickup_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        ************************/
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
        if ($this->getUniResult($query, $comment) < 1) {
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
        if ($request->get('showMenu') == 'Graph') {
            $where = "{$request->get('targetDateStr')}, {$request->get('targetDateEnd')}, '{$request->get('targetLine')}'";
        } else {
            $where = "{$request->get('targetDateList')}, {$request->get('targetDateList')}, '{$request->get('targetLine')}'";
        }
        return $where;
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
    ///// List部   組立のライン別工数グラフの 明細データ作成
    private function getViewHTMLbody($request, $menu)
    {
        $temp = array();
        $query = $this->getQueryStatement1($request);
        $c_all_jisseki = $this->getResult2($query, $temp);      // カプラ全体バーコード件数
        $query = $this->getQueryStatement2($request);
        $c_toku_jisseki = $this->getResult2($query, $temp);     // カラプ特注バーコード件数
        $c_std_jisseki = $c_all_jisseki - $c_toku_jisseki;      // カプラ標準バーコード件数
        
        $query = $this->getQueryStatement3($request);
        $c_all_plan = $this->getResult2($query, $temp);         // カプラ全体 計画件数
        $query = $this->getQueryStatement4($request);
        $c_toku_plan = $this->getResult2($query, $temp);        // カプラ特注 計画件数
        $c_std_plan = $c_all_plan - $c_toku_plan;               // カプラ標準 計画件数
        
        $query = $this->getQueryStatement5($request);
        $c_all_time = 0;
        $this->getUniResult($query, $c_all_time);               // カプラ全体 出庫時間
        $query = $this->getQueryStatement6($request);
        $c_toku_time = 0;
        $this->getUniResult($query, $c_toku_time);              // カプラ特注 出庫時間
        $c_std_time = $c_all_time - $c_toku_time;               // カプラ標準 出庫時間
        
        
        $query = $this->getQueryStatement11($request);
        $l_all_jisseki = $this->getResult2($query, $temp);      // リニア全体バーコード件数
        $query = $this->getQueryStatement12($request);
        $l_all_plan = $this->getResult2($query, $temp);         // リニア全体 計画件数
        $query = $this->getQueryStatement13($request);
        $l_all_time = 0;
        $this->getUniResult($query, $l_all_time);               // リニア全体 出庫時間
        
        
        $query = $this->getQueryStatement7($request);
        $c_std_pcs = 0;
        $this->getUniResult($query, $c_std_pcs);                // カプラ標準 出庫個数
        
        $query = $this->getQueryStatement8($request);
        $c_toku_pcs = 0;
        $this->getUniResult($query, $c_toku_pcs);               // カプラ特注 出庫個数
        
        $query = $this->getQueryStatement9($request);
        $rinear_pcs = 0;
        $this->getUniResult($query, $linear_pcs);               // リニア 出庫個数
        
        ///// バーコード入力率の Division by zero 対応のため前もって計算
        if ($c_all_plan != 0) $c_all_ratio  = number_format($c_all_jisseki / $c_all_plan * 100, 1);
        else $c_all_ratio  = number_format(0, 1);
        if ($c_toku_plan != 0) $c_toku_ratio = number_format($c_toku_jisseki / $c_toku_plan * 100, 1);
        else $c_toku_ratio  = number_format(0, 1);
        if ($c_std_plan != 0) $c_std_ratio  = number_format($c_std_jisseki / $c_std_plan * 100, 1);
        else $c_std_ratio = number_format(0, 1);
        if ($l_all_plan != 0) $l_all_ratio  = number_format($l_all_jisseki / $l_all_plan * 100, 1);
        else $l_all_ratio = number_format(0, 1);
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ($c_all_time == 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox'>&nbsp;</th>\n";
            $listTable .= "        <th class='winbox'>カプラ全体</th>\n";
            $listTable .= "        <th class='winbox'>カプラ特注</th>\n";
            $listTable .= "        <th class='winbox'>カプラ標準</th>\n";
            $listTable .= "        <th class='winbox'>リニア全体</th>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>出庫更新 計画数</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_plan) . "件</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_plan) . "件</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_plan) . "件</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_plan) . "件</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>バーコード入力計画数</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_jisseki) . "件</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_jisseki) . "件</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_jisseki) . "件</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_jisseki) . "件</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>バーコード入力率</td>\n";
            $listTable .= "        <td class='winbox' align='right'>{$c_all_ratio}％</td>\n";
            $listTable .= "        <td class='winbox' align='right'>{$c_toku_ratio}％</td>\n";
            $listTable .= "        <td class='winbox' align='right'>{$c_std_ratio}％</td>\n";
            $listTable .= "        <td class='winbox' align='right'>{$l_all_ratio}％</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>バーコード入力時間</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_time) . "分</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_time) . "分</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_time) . "分</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_time) . "分</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>１計画あたりの平均出庫時間</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_time / $c_all_jisseki, 1) . "分</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_time / $c_toku_jisseki, 1) . "分</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_time / $c_std_jisseki, 1) . "分</td>\n";
            if ($l_all_jisseki != 0) {
                $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_time / $l_all_jisseki, 1) . "分</td>\n";
            } else {
                $listTable .= "        <td class='winbox' align='right'>---分</td>\n";
            }
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        $listTable .= "<br>\n";
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <caption>品質保証課用 月次資料</caption>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ($c_std_pcs == 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox'>&nbsp;</th>\n";
            $listTable .= "        <th class='winbox'>カプラ標準</th>\n";
            $listTable .= "        <th class='winbox'>カプラ特注</th>\n";
            $listTable .= "        <th class='winbox'>リニア全体</th>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>合計 出庫 個数</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_pcs) . "個</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_pcs) . "個</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($linear_pcs) . "個</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   一覧表の ヘッダー部を作成
    private function getViewHTMLheader($request)
    {
        // タイトルをSQLのストアードプロシージャーから取得
        $query = "SELECT parts_stock_title('{$request->get('targetPartsNo')}')";
        $title = '';
        $this->getUniResult($query, $title);
        if (!$title) {  // レコードが無い場合もNULLレコードが返るため変数の内容でチェックする
            $title = 'アイテムマスター未登録！';
        }
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>{$title}</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>集荷日</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>実施日</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>計画番号</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>製品番号</th>\n";
        $listTable .= "        <th class='winbox' width='18%'>製　品　名</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>引当数</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>発注数</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>有効数</th>\n";
        $listTable .= "        <th class='winbox' width=' 4%'>CK</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>備考</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   一覧表の フッター部を作成
    private function getViewHTMLfooter()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='79%' align='right'>最終有効在庫数</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>{$this->last_avail_pcs}</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>&nbsp;</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   一覧表のSQLステートメント取得
    // バーコード入力全体の計画件数 カプラ全体
    private function getQueryStatement1($request)
    {
        $query = "
            SELECT count(time.plan_no) FROM parts_pickup_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 070000' AND time.end_time <= '{$request->get('targetDateEnd')} 235959'
            AND sche.dept = 'C'
            GROUP BY time.plan_no
        ";
        return $query;
    }
    
    // バーコード入力特注の計画件数 カプラ特注
    private function getQueryStatement2($request)
    {
        $query = "
            SELECT count(time.plan_no) FROM parts_pickup_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 070000' AND time.end_time <= '{$request->get('targetDateEnd')} 235959'
            AND sche.dept = 'C' AND sche.note15 LIKE 'SC%' -- 特注
            GROUP BY time.plan_no
        ";
        return $query;
    }
    
    // 出庫全体の計画件数 カプラ全体
    private function getQueryStatement3($request)
    {
        $query = "
            SELECT count(hist.plan_no) FROM parts_stock_history AS hist
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE hist.upd_date >= {$request->get('targetDateStr')} AND hist.upd_date <= {$request->get('targetDateEnd')}
            AND hist.den_kubun = '3' AND hist.out_id = '2'
            AND sche.dept = 'C'
            GROUP BY hist.plan_no
        ";
        return $query;
    }
    
    // 出庫全体の計画件数 カプラ特注
    private function getQueryStatement4($request)
    {
        $query = "
            SELECT count(hist.plan_no) FROM parts_stock_history AS hist
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE hist.upd_date >= {$request->get('targetDateStr')} AND hist.upd_date <= {$request->get('targetDateEnd')}
            AND hist.den_kubun = '3' AND hist.out_id = '2'
            AND sche.dept = 'C' AND sche.note15 LIKE 'SC%' -- 特注
            GROUP BY hist.plan_no
        ";
        return $query;
    }
    
    // バーコードの入力時間 カプラ全体
    private function getQueryStatement5($request)
    {
        $query = "
            SELECT sum(pick_time) FROM parts_pickup_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 080000' AND time.end_time <= '{$request->get('targetDateEnd')} 210000'
            AND sche.dept = 'C'
        ";
        return $query;
    }
    
    // バーコードの入力時間 カプラ特注
    private function getQueryStatement6($request)
    {
        $query = "
            SELECT sum(pick_time) FROM parts_pickup_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 080000' AND time.end_time <= '{$request->get('targetDateEnd')} 210000'
            AND sche.dept = 'C' AND sche.note15 LIKE 'SC%' -- 特注
        ";
        return $query;
    }
    
    // バーコード入力全体の計画件数 リニア全体
    private function getQueryStatement11($request)
    {
        $query = "
            SELECT count(time.plan_no) FROM parts_pickup_linear AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 070000' AND time.end_time <= '{$request->get('targetDateEnd')} 235959'
            AND sche.dept = 'L'
            GROUP BY time.plan_no
        ";
        return $query;
    }
    
    // 出庫全体の計画件数 リニア全体
    private function getQueryStatement12($request)
    {
        $query = "
            SELECT count(hist.plan_no) FROM parts_stock_history AS hist
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE hist.upd_date >= {$request->get('targetDateStr')} AND hist.upd_date <= {$request->get('targetDateEnd')}
            AND hist.den_kubun = '3' AND hist.out_id = '2'
            AND sche.dept = 'L'
            GROUP BY hist.plan_no
        ";
        return $query;
    }
    
    // バーコードの入力時間 リニア全体
    private function getQueryStatement13($request)
    {
        $query = "
            SELECT sum(pick_time) FROM parts_pickup_linear AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 080000' AND time.end_time <= '{$request->get('targetDateEnd')} 210000'
            AND sche.dept = 'L'
        ";
        return $query;
    }
    
    ///// 品質保証課資料用 合計出庫個数 抽出 カプラ標準
    private function getQueryStatement7($request)
    {
        $query = "
            SELECT
                sum(stock_mv)   AS カプラ標準出庫数
            FROM
                parts_stock_history AS hist
            LEFT OUTER JOIN
                assembly_schedule USING(plan_no)
            WHERE
                upd_date>={$request->get('targetDateStr')} AND upd_date<={$request->get('targetDateEnd')} AND dept='C' AND note15 NOT LIKE 'SC%' AND den_kubun='3' AND out_id='2' AND note LIKE 'ｸﾐﾀﾃ ｳｼﾞｲｴ%'
                AND NOT EXISTS (SELECT miccc FROM miccc WHERE mipn=hist.parts_no)
        ";
        return $query;
    }
    
    ///// 品質保証課資料用 合計出庫個数 抽出 カプラ特注
    private function getQueryStatement8($request)
    {
        $query = "
            SELECT
                sum(stock_mv)   AS カプラ特注出庫数
            FROM
                parts_stock_history AS hist
            LEFT OUTER JOIN
                assembly_schedule USING(plan_no)
            WHERE
                upd_date>={$request->get('targetDateStr')} AND upd_date<={$request->get('targetDateEnd')} AND dept='C' AND note15 LIKE 'SC%' AND den_kubun='3' AND out_id='2' AND note LIKE 'ｸﾐﾀﾃ ｳｼﾞｲｴ%'
                AND NOT EXISTS (SELECT miccc FROM miccc WHERE mipn=hist.parts_no)
        ";
        return $query;
    }
    
    ///// 品質保証課資料用 合計出庫個数 抽出 リニア
    private function getQueryStatement9($request)
    {
        $query = "
            SELECT
                sum(stock_mv)   AS リニア出庫数
            FROM
                parts_stock_history AS hist
            LEFT OUTER JOIN
                assembly_schedule USING(plan_no)
            WHERE
                upd_date>={$request->get('targetDateStr')} AND upd_date<={$request->get('targetDateEnd')} AND dept='L' AND den_kubun='3' AND out_id='2' AND note LIKE 'ｸﾐﾀﾃ ｳｼﾞｲｴ%'
                AND NOT EXISTS (SELECT miccc FROM miccc WHERE mipn=hist.parts_no)
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
<title>出庫時間集計照会</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../parts_pickup_analyze.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../parts_pickup_analyze.js'></script>
</head>
<body style='background-color:#d6d3ce;'>  <!--  -->
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
    
} // Class PartsPickupAnalyze_Model End

?>
