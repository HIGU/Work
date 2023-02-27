<?php
//////////////////////////////////////////////////////////////////////////////
// 部門別 製造経費及び販管費の照会                           MVC Model 部   //
// Copyright (C) 2007-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_Model.php                               //
// 2007/10/16 前期平均と今期平均の２項目を追加                              //
// 2007/11/08 メソッドgetLaborAuth()を追加して労務費の明細照会レベルを分ける//
// 2008/05/14 早房部長依頼により科目が対象月に存在する物しか表示されなかった//
//            のを対象期or前期に存在した物は全て表示するように変更     大谷 //
// 2008/05/21 半期累計表示を追加                                       大谷 //
// 2008/06/11 経費の表示部門を変更(500生産部なども表示するように)      大谷 //
// 2008/09/12 全社合計の製造経費の照会を追加                           大谷 //
// 2009/12/10 NK川上部長依頼により小森谷課長も労務費の内訳を見れるよう      //
//            変更                                                     大谷 //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class ActSummary_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $offset;                            // 共用 SQLのOFFSET句
    private $limit;                             // 共用 SQLのLIMIT句
    private $total_expense;                     // 当月 経費の小計
    private $total_laborCost;                   // 当月 労務費の小計
    private $total_cost;                        // 当月 合計
    private $sum_expense;                       // 累計 経費の小計
    private $sum_laborCost;                     // 累計 労務費の小計
    private $sum_cost;                          // 累計 合計
    private $pre_expense;                       // 前期平均 経費の小計
    private $pre_laborCost;                     // 前期平均 労務費の小計
    private $pre_cost;                          // 前期平均 合計
    private $now_expense;                       // 今期平均 経費の小計
    private $now_laborCost;                     // 今期平均 労務費の小計
    private $now_cost;                          // 今期平均 合計
    private $hulf_expense;                      // 半期累計 経費の小計
    private $hulf_laborCost;                    // 半期累計 労務費の小計
    private $hulf_cost;                         // 半期累計 合計
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct()
    {
        ///// Properties の初期化
        $this->where  = '';
        $this->order  = '';
        $this->offset = '';
        $this->limit  = '';
        $this->total_expense    = 0;
        $this->total_laborCost  = 0;
        $this->total_cost       = 0;
        $this->sum_expense      = 0;
        $this->sum_laborCost    = 0;
        $this->sum_cost         = 0;
        $this->pre_expense      = 0;
        $this->pre_laborCost    = 0;
        $this->pre_cost         = 0;
        $this->now_expense      = 0;
        $this->now_laborCost    = 0;
        $this->now_cost         = 0;
        $this->hulf_expense     = 0;
        $this->hulf_laborCost   = 0;
        $this->hulf_cost        = 0;
    }
    
    ///// SQLのWHERE区の設定
    public function setWhere($session)
    {
        $this->where = $this->setWhereBody($session);
    }
    
    ///// 対象年月のHTML <select> option の出力
    public function getTargetDateYMvalues($session)
    {
        $str_ym = date('Ym') - 10000;   // １年前から
        $query = "
            SELECT act_yymm + 200000
            FROM act_summary
            WHERE (act_yymm + 200000) >= {$str_ym}
            GROUP BY act_yymm ORDER BY act_yymm DESC
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        // 初期化
        $option = "\n";
        for ($i=0; $i<$rows; $i++) {
            $yyyy = substr($res[$i][0], 0, 4);
            $mm   = substr($res[$i][0], 4, 2);
            if ($session->get_local('targetDateYM') == $res[$i][0]) {
                $option .= "<option value='{$res[$i][0]}' selected>{$yyyy}年{$mm}月</option>\n";
            } else {
                $option .= "<option value='{$res[$i][0]}'>{$yyyy}年{$mm}月</option>\n";
            }
        }
        return $option;
    }
    
    ///// 対象部門コードのHTML <select> option の出力
    public function getTargetAct_idValues($session)
    {
        $query = "
            SELECT
                act_id      AS 部門コード
                ,
                act_name    AS 部門名
            FROM act_table
            WHERE act_id < 600 AND act_id NOT IN (395)
            ORDER BY act_name ASC
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        // 初期化
        $option = "\n";
        $all_rows = $rows + 1;
        // 全体照会用 ここから
        array_unshift($res, array('000', '全社合計の製造経費'));
        for ($i=0; $i<$rows+1; $i++) {
        // ここまで
        //for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('targetAct_id') == $res[$i][0]) {
                if ($res[$i][0] == '000') {
                    $option .= "<option value='{$res[$i][0]}' style='color:blue;' selected>{$res[$i][1]}</option>\n";
                } else {
                    $option .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
                }
            } else {
                if ($res[$i][0] == '000') {
                    $option .= "<option value='{$res[$i][0]}' style='color:blue;'>{$res[$i][1]}</option>\n";
                } else {
                    $option .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
                }
            }
        }
        return $option;
    }
    
    ///// Window表示の時のタイトルの出力
    public function getTitleDateValues($session)
    {
        $yyyymm = substr($session->get_local('targetDateYM'), 0, 4) . '年' . substr($session->get_local('targetDateYM'), 4, 2) . '月';
        $query = "
            SELECT
                act_name    AS 部門名
            FROM act_table
            WHERE act_id = {$session->get_local('targetAct_id')}
        ";
        $this->getUniResult($query, $name);
        return $yyyymm . '　' . $name;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    データの明細 一覧表
    public function outViewListHTML($session, $menu)
    {
                /***** ヘッダー部を作成 *****/
        /*****************
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader();
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/act_summary_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        *****************/
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($session, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/act_summary_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/act_summary_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        $query = "SELECT comment FROM act_summary_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO act_summary_comment (assy_no, plan_no, comment, last_date, last_host)
                VALUES ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        } else {
            $sql = "
                UPDATE act_summary_comment SET comment='{$request->get('comment')}',
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
            act_summary_comment ON(mipn=assy_no)
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
    ////////// リクエストによりSQL文のWHERE区を設定
    protected function setWhereBody($session)
    {
        return "WHERE act_yymm = {$session->get_local('targetDateYM')} AND act_id = {$session->get_local('targetAct_id')}";
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   一覧表のSQLステートメント取得
    // 指定部門コードで指定年月の期とその前期分で発生した科目の一覧を取得 SQL
    private function getQueryActCode($session)
    {
        // 前期期初の年月４桁を取得する(例：0604)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy -= 1;
            $mm = '04';
        } else {
            $yyyy -= 2;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                actcod              AS 科目
                ,
                to_char(aucod, 'FM00')
                                    AS 内訳
                ,
                COALESCE(sub.s_name, par.s_name, NULL)  -- 左から順番にNULLでない最初の値を返す
                                    AS 科目名
            FROM act_summary AS act
            LEFT OUTER JOIN mactukl AS sub USING(actcod, aucod)
            LEFT OUTER JOIN macuntl AS par USING(actcod)
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
            GROUP BY actcod, aucod, sub.s_name, par.s_name
            ORDER BY actcod ASC, aucod ASC
        ";
        return $query;
    }
    // 全ての部門の指定年月の期とその前期分で発生した科目の一覧を取得 SQL
    private function getQueryActCodeAll($session)
    {
        // 前期期初の年月４桁を取得する(例：0604)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy -= 1;
            $mm = '04';
        } else {
            $yyyy -= 2;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                actcod              AS 科目
                ,
                to_char(aucod, 'FM00')
                                    AS 内訳
                ,
                COALESCE(sub.s_name, par.s_name, NULL)  -- 左から順番にNULLでない最初の値を返す
                                    AS 科目名
            FROM act_summary AS act
            LEFT OUTER JOIN mactukl AS sub USING(actcod, aucod)
            LEFT OUTER JOIN macuntl AS par USING(actcod)
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
            GROUP BY actcod, aucod, sub.s_name, par.s_name
            ORDER BY actcod ASC, aucod ASC
        ";
        return $query;
    }
    // 指定年月の指定部門コードで製造経費・販管費の金額を取得 SQL
    private function getQueryStatement($session, $actCode, $detailCode)
    {
        $ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                CASE
                    WHEN act_monthly IS NULL THEN 0
                    WHEN act_monthly = 0 THEN 0
                    ELSE act_monthly
                END                 AS 金額
            FROM act_summary
            WHERE act_yymm = {$ym4} AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // 指定年月の全ての部門コードで製造経費・販管費の金額を取得 SQL
    private function getQueryStatementAll($session, $actCode, $detailCode)
    {
        $ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                CASE
                    WHEN sum(act_monthly) IS NULL THEN 0
                    WHEN sum(act_monthly) = 0 THEN 0
                    ELSE sum(act_monthly)
                END                 AS 金額
            FROM act_summary
            WHERE act_yymm = {$ym4}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // 指定年月の指定部門コードで製造経費・販管費の累計を取得 SQL
    private function getQuerySum($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                act_sum AS 累計
                ,
                act_yymm AS 年月
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // 指定年月の全ての部門コードで製造経費・販管費の累計を取得 SQL
    private function getQuerySumAll($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                sum(act_monthly) AS 累計
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // 指定年月の指定部門コードで製造経費・販管費の半期累計を取得 SQL
    private function getQueryHalfSum($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4 && $mm < 10) {
            $mm = '04';
        } else if ($mm >= 10 && $mm < 13) {
            $mm = '10';
        } else {
            $yyyy -= 1;
            $mm = '10';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                sum(act_monthly)
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // 指定年月の全ての部門コードで製造経費・販管費の半期累計を取得 SQL
    private function getQueryHalfSumAll($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4 && $mm < 10) {
            $mm = '04';
        } else if ($mm >= 10 && $mm < 13) {
            $mm = '10';
        } else {
            $yyyy -= 1;
            $mm = '10';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                sum(act_monthly)
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ///// List部   組立のライン別工数グラフの 明細データ作成
    private function getViewHTMLbody($session, $menu)
    {
        ///// 対象科目の取得
        if ($session->get_local('targetAct_id') == '000') {
            $query = $this->getQueryActCodeAll($session);
        } else {
            $query = $this->getQueryActCode($session);
        }
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            // $session->add('s_sysmsg', '製造経費のデータがありません！');
        }
        ///// 対象月の金額・累計の取得
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('targetAct_id') == '000') {
                $nowState[$i] = $this->getQueryStatementAll($session, $res[$i][0], $res[$i][1]);
                $nowSum[$i] = $this->getQuerySumAll($session, $res[$i][0], $res[$i][1]);
                $hulfSum[$i] = $this->getQueryHalfSumAll($session, $res[$i][0], $res[$i][1]);
            } else {
                $nowState[$i] = $this->getQueryStatement($session, $res[$i][0], $res[$i][1]);
                $nowSum[$i] = $this->getQuerySum($session, $res[$i][0], $res[$i][1]);
                $hulfSum[$i] = $this->getQueryHalfSum($session, $res[$i][0], $res[$i][1]);
            }
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>製造経費・販管費のデータがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $expenseFlg = 0;
            $laborFlg   = $this->getLaborAuth($session);
            $preData    = array();
            $nowData    = array();
            for ($i=0; $i<$rows; $i++) {
                if ($session->get_local('targetAct_id') == '000') {
                    ///// 全ての部門の対象科目・内訳の前期平均を取得
                    $preData[$i] = $this->getPreAverageActCodeAll($session, $res[$i][0], $res[$i][1]);
                    ///// 全ての部門の対象科目・内訳の今期平均を取得
                    $nowData[$i] = $this->getNowAverageActCodeAll($session, $res[$i][0], $res[$i][1]);
                } else {
                    ///// 対象科目・内訳の前期平均を取得
                    $preData[$i] = $this->getPreAverageActCode($session, $res[$i][0], $res[$i][1]);
                    ///// 対象科目・内訳の今期平均を取得
                    $nowData[$i] = $this->getNowAverageActCode($session, $res[$i][0], $res[$i][1]);
                }
                ///// 製造経費の経費と労務費をプロパティに保存
                if ($res[$i][0] <= 8000) {
                    $this->total_expense    += $nowState[$i];
                    $this->sum_expense      += $nowSum[$i];
                    $this->pre_expense      += $preData[$i];
                    $this->now_expense      += $nowData[$i];
                    $this->hulf_expense     += $hulfSum[$i];
                } else {
                    if ($expenseFlg == 0) {
                        $listTable .= "    <tr>\n";
                        $listTable .= "        <td class='winbox' width='16%' align='right' colspan='3'>&nbsp;</td>\n";
                        $listTable .= "        <td class='winbox total' width='20%' align='right' >経　費　計</td>\n";
                        $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->pre_expense) . "</td>\n";   // 前期平均 経費の小計
                        $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->now_expense) . "</td>\n";   // 今期平均 経費の小計
                        $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($this->total_expense) . "</td>\n"; // 経費の小計
                        $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->sum_expense)   . "</td>\n"; // 累計 経費の小計
                        $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->hulf_expense)   . "</td>\n"; // 半期累計 経費の小計
                        $listTable .= "    </tr>\n";
                        $expenseFlg = 1;
                    }
                    $this->total_laborCost  += $nowState[$i];
                    $this->sum_laborCost    += $nowSum[$i];
                    $this->pre_laborCost    += $preData[$i];
                    $this->now_laborCost    += $nowData[$i];
                    $this->hulf_laborCost   += $hulfSum[$i];
                }
                /*****
                if ($res[$i][10] != '') {   // コメントがあれば色を変える
                    $listTable .= "    <tr onDblClick='ActSummary.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='ActSummary.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                }
                *****/
                // $listTable .= "    <tr style='visibility:hidden;'>\n";の方法で隠す事も可能
                if ($expenseFlg == 0 || $laborFlg) {
                    $listTable .= "    <tr>\n";
                    $listTable .= "        <td class='winbox' width=' 4%' align='right' >" . ($i+1) . "</td>\n";    // 行番号
                    // $listTable .= "        <td class='winbox' width=' 8%' align='right' ><a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>明細</a></td>\n"; // 明細クリック用
                    $listTable .= "        <td class='winbox' width=' 6%' align='right' >{$res[$i][0]}</td>\n";     // 科目
                    $listTable .= "        <td class='winbox' width=' 6%' align='left'  >{$res[$i][1]}</td>\n";     // 内訳科目
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][2]}</td>\n";     // 科目名
                    $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($preData[$i]) . "</td>\n";// 前期平均
                    $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($nowData[$i]) . "</td>\n";// 今期平均
                    $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($nowState[$i]) . "</td>\n";// 金額
                    $listTable .= "        <td class='winbox' width='14%' align='right' >" . number_format($nowSum[$i]) . "</td>\n";// 累計
                    $listTable .= "        <td class='winbox' width='14%' align='right' >" . number_format($hulfSum[$i]) . "</td>\n";// 半期累計
                    $listTable .= "    </tr>\n";
                }
            }
            $this->total_cost = $this->total_expense + $this->total_laborCost;
            $this->sum_cost   = $this->sum_expense + $this->sum_laborCost;
            $this->pre_cost   = $this->pre_expense + $this->pre_laborCost;
            $this->now_cost   = $this->now_expense + $this->now_laborCost;
            $this->hulf_cost  = $this->hulf_expense + $this->hulf_laborCost;
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' width='16%' align='right' colspan='3'>&nbsp;</td>\n";
            $listTable .= "        <td class='winbox total' width='20%' align='right'>労　務　費　計</td>\n";
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->pre_laborCost) . "</td>\n";  // 前期平均 労務費の小計
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->now_laborCost) . "</td>\n";  // 今期平均 労務費の小計
            $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($this->total_laborCost) . "</td>\n";// 労務費の小計
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->sum_laborCost)   . "</td>\n";// 累計 労務費の小計
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->hulf_laborCost)   . "</td>\n";// 半期累計 労務費の小計
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox total' width='36%' align='right' colspan='4'>部　門　合　計</td>\n";
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->pre_cost) . "</td>\n";  // 前期平均 部門合計
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->now_cost) . "</td>\n";  // 今期平均 部門合計
            $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($this->total_cost) . "</td>\n";// 部門合計
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->sum_cost)   . "</td>\n";// 累計 部門合計
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->hulf_cost)   . "</td>\n";// 半期累計 部門合計
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   一覧表の ヘッダー部を作成
    private function getViewHTMLheader()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>{$title}</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 1%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>科目</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>内訳</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>勘定科目名</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>前期平均</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>今期平均</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>当月金額</th>\n";
        $listTable .= "        <th class='winbox' width='14%'>当期累計</th>\n";
        $listTable .= "        <th class='winbox' width='14%'>半期累計</th>\n";
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
    
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status)
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>出庫時間集計照会</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../act_summary.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../act_summary.js'></script>
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
    
    
    ////////// 指定科目・内訳科目で前期の平均を返す
    private function getPreAverageActCode($session, $actCode, $detailCode)
    {
        // 前期末の年月４桁を取得する(例：0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '03';
        } else {
            $yyyy -= 1;
            $mm = '03';
        }
        $now_ym4 = substr($yyyy, 2, 2) . $mm;
        $pre_ym4 = $now_ym4 - 99;
        $query = "
            SELECT
                CASE
                    WHEN act_sum IS NULL THEN 0
                    WHEN act_sum = 0 THEN 0
                    ELSE Uround(act_sum / 12, 0)
                END                 AS 前期平均
                ,
                act_yymm AS 年月
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// 全ての部門の指定科目・内訳科目で前期の平均を返す
    private function getPreAverageActCodeAll($session, $actCode, $detailCode)
    {
        // 前期末の年月４桁を取得する(例：0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '03';
        } else {
            $yyyy -= 1;
            $mm = '03';
        }
        $now_ym4 = substr($yyyy, 2, 2) . $mm;
        $pre_ym4 = $now_ym4 - 99;
        $query = "
            SELECT
                CASE
                    WHEN sum(act_sum) IS NULL THEN 0
                    WHEN sum(act_sum) = 0 THEN 0
                    ELSE Uround(sum(act_sum) / 12, 0)
                END                 AS 前期平均
                ,
                act_yymm AS 年月
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
            GROUP BY act_yymm
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// 指定科目・内訳科目で今期の平均を返す
    private function getNowAverageActCode($session, $actCode, $detailCode)
    {
        // targetDateYMでの今期末の年月４桁を取得する(例：0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy += 1;
            $mm = '03';
        } else {
            $mm = '03';
        }
        $ym4 = substr($yyyy, 2, 2) . $mm;
        // 今期末でのデータがある最後の年月を取得する
        $query = "SELECT act_yymm, act_ser FROM act_summary WHERE act_yymm <= {$ym4} ORDER BY act_yymm DESC LIMIT 1";
        $res = array();
        $this->getResult2($query, $res);
        $cnt = $res[0][1];
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = $ym4;
        $query = "
            SELECT
                CASE
                    WHEN act_sum IS NULL THEN 0
                    WHEN act_sum = 0 THEN 0
                    ELSE Uround(act_sum / {$cnt}, 0)
                END                 AS 今期平均
                ,
                act_yymm AS 年月
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// 全ての部門の指定科目・内訳科目で今期の平均を返す
    private function getNowAverageActCodeAll($session, $actCode, $detailCode)
    {
        // targetDateYMでの今期末の年月４桁を取得する(例：0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy += 1;
            $mm = '03';
        } else {
            $mm = '03';
        }
        $ym4 = substr($yyyy, 2, 2) . $mm;
        // 今期末でのデータがある最後の年月を取得する
        $query = "SELECT act_yymm, act_ser FROM act_summary WHERE act_yymm <= {$ym4} ORDER BY act_yymm DESC LIMIT 1";
        $res = array();
        $this->getResult2($query, $res);
        $cnt = $res[0][1];
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = $ym4;
        $query = "
            SELECT
                CASE
                    WHEN sum(act_sum) IS NULL THEN 0
                    WHEN sum(act_sum) = 0 THEN 0
                    ELSE Uround(sum(act_sum) / {$cnt}, 0)
                END                 AS 今期平均
                ,
                act_yymm AS 年月
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
            GROUP BY act_yymm
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// 労務費の明細 照会が出来る権限の取得 true=OK false=NG
    private function getLaborAuth($session)
    {
        $query = "SELECT pid FROM user_detailes WHERE uid = '{$session->get('User_ID')}'";
        $pid = 0;               // 初期化
        $this->getUniResult($query, $pid);
        if ($pid >= 60 || $session->get('Auth') >= 3 || $session->get('User_ID')== '011061') {       // 副部長以上かシステム管理者
            return true;
        } else {
            return false;
        }
    }
    
} // Class ActSummary_Model End

?>
