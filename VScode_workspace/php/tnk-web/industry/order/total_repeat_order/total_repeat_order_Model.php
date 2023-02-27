<?php
//////////////////////////////////////////////////////////////////////////////
// リピート部品発注の集計 結果 照会                          MVC Model 部   //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order_Model.php                        //
// 2007/12/20 setWhereBody()に注文数がある物を対象にする条件を追加          //
//            明細クリックで各工程明細の照会を追加 Detailsがキーワード      //
// 2008/07/30 品証阿相課長依頼によりBODY部に親機種を追加               大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class TotalRepeatOrder_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $offset;                            // 共用 SQLのOFFSET句
    private $limit;                             // 共用 SQLのLIMIT句
    private $sql;                               // 共用 SQL文
    private $dateYMvalues;                      // targetDateYMvaluesの<select><option>データ
    private $total;                             // 合計件数
    private $viewRec;                           // 表示件数
    private $detailsPartsName;                  // 明細用部品名
    private $detailsVendor;                     // 明細用発注先名
    private $detailsPartsNo;                    // 明細用部品番号
    private $detailsProMark;                    // 明細用工程記号
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        ///// プロパティ(メンバー変数)の初期化
        $this->where  = '';
        $this->order  = '';
        $this->offset = '';
        $this->limit  = '';
        $this->sql    = '';
        $this->dateYMvalues = '';
        $this->total   = 0;
        $this->viewRec = 0;
        $this->detailsPartsName = '';
        $this->detailsVendor    = '';
        $this->detailsPartsNo   = '';
        $this->detailsProMark   = '';
    }
    
    ///// SQLのWHERE区の設定
    public function setWhere($session)
    {
        $this->where = $this->setWhereBody($session);
    }
    
    ///// 各工程明細のSQLのWHERE区の設定
    public function setDetailsWhere($session)
    {
        $this->where = $this->setDetailsWhereBody($session);
    }
    
    ///// SQLのWHERE区の設定
    public function setLimit($session)
    {
        $this->limit = $this->setLimitBody($session);
    }
    
    ///// SQL文の設定
    public function setSQL($session)
    {
        $this->sql = $this->setSQLbody($session);
    }
    
    ///// 各工程明細のSQL文の設定
    public function setDetailsSQL($session)
    {
        $this->sql = $this->setDetailsSQLbody($session);
    }
    
    ///// 合計件数の設定
    public function setTotal()
    {
        $this->total = $this->setTotalBody();
    }
    
    ///// 各工程明細の部品名・発注先名の設定
    public function setDetailsItem($session)
    {
        $this->setDetailsItemBody($session);
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
        $this->dateYMvalues = $option;
        return $option;
    }
    
    ////////// MVC の Model 部 各種リスト及びグラフ生成
    ///// List部    リスト生成
    public function outListViewHTML($session, $menu)
    {
                /***** ヘッダー部を作成 *****/
        $this->outViewHTMLheader($session, $menu);
        
                /***** 本文を作成 *****/
        $this->outViewHTMLbody($session, $menu);
        
                /***** フッター部を作成 *****/
        $this->outViewHTMLfooter($session, $menu);
        
        return ;
    }
    
    ///// エラーメッセージ用リスト出力
    public function outListErrorMessage($session, $menu)
    {
                /***** ヘッダー部を作成 *****/
        $this->outViewHTMLheader($session, $menu);
        
                /***** 本文を作成 *****/
        $this->outErrorMessageHTMLbody($session, $menu);
        
                /***** フッター部を作成 *****/
        $this->outViewHTMLfooter($session, $menu);
        
        return ;
    }
    
    ///// 各工程明細リスト生成
    public function outDetailsViewHTML($session, $menu)
    {
                /***** ヘッダー部を作成 *****/
        $this->outDetailsHTMLheader($session, $menu);
        
                /***** 本文を作成 *****/
        $this->outDetailsHTMLbody($session, $menu);
        
                /***** フッター部を作成 *****/
        $this->outDetailsHTMLfooter($session, $menu);
        
        return ;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文のWHERE区を設定
    protected function setWhereBody($session)
    {
        $where = "
            WHERE delivery >= {$session->get_local('targetDateStr')} AND delivery <= {$session->get_local('targetDateEnd')}
            AND (order_q - cut_siharai) > 0
        ";
        // 上記は注文数がある物を対象にしている。また、日東工器からの有償支給の打切はマイナス注文数が入っている事に注意。
        return $where;
    }
    
    ////////// 各工程の明細リスト用SQL文のWHERE区を設定
    protected function setDetailsWhereBody($session)
    {
        $where = "
            WHERE delivery >= {$session->get_local('targetDateStr')} AND delivery <= {$session->get_local('targetDateEnd')}
            AND order_process.vendor = '{$session->get_local('targetVendor')}' AND parts_no = '{$session->get_local('targetPartsNo')}'
            AND pro_mark = '{$session->get_local('targetProMark')}'
            AND (order_q - cut_siharai) > 0
        ";
        // 上記は注文数がある物を対象にしている。また、日東工器からの有償支給の打切はマイナス注文数が入っている事に注意。
        return $where;
    }
    
    protected function setLimitBody($session)
    {
        $limit = "LIMIT {$session->get_local('targetLimit')}";
        return $limit;
    }
    
    protected function setTotalBody()
    {
        $query = "
            SELECT count(*)
            FROM order_process
            {$this->where}
            GROUP BY parts_no, pro_mark, vendor
        ";
        $rows = $this->getResult2($query, $res);
        return $rows;
    }
    
    protected function setSQLbody($session)
    {
        $query = "
            SELECT
                parts_no AS 部品番号
                ,
                substr(midsc, 1, 16) AS 部品名
                ,
                substr(to_char(order_no, 'FM9999999'), 7, 1) AS 工程番号
                ,
                pro_mark AS 工程記号
                ,
                substr(name, 1, 14) AS 発注先名
                ,
                count(*) AS 件数
                ,
                sum(order_q - cut_siharai) AS 合計数量
                ,   -- 以下はリスト外
                order_process.vendor AS 発注先コード
                ,
                mepnt AS 親機種
            FROM order_process
            LEFT OUTER JOIN miitem ON (parts_no = mipn)
            LEFT OUTER JOIN vendor_master USING (vendor)
            {$this->where}
            GROUP BY 部品番号, 親機種, 部品名, 工程番号, 工程記号, order_process.vendor, vendor_master.name
            ORDER BY 件数 DESC, 合計数量 DESC, parts_no ASC, 工程番号 ASC, 工程記号 ASC, vendor ASC
            {$this->limit}
        ";
        return $query;
    }
    
    ///// 各工程の明細リストのSQL文
    protected function setDetailsSQLbody($session)
    {
        $query = "
            SELECT
                to_char(order_no, 'FM999999-9') AS 注文番号
                ,
                to_char(sei_no, 'FM0000000')    AS 製造番号
                ,
                substr(to_char(order_date, 'FM9999/99/99'), 3, 8)
                                                AS 発注日
                ,
                substr(to_char(delivery, 'FM9999/99/99'), 3, 8)
                                                AS 納期
                ,
                CASE
                    WHEN mtl_cond = '1' THEN '自給'
                    WHEN mtl_cond = '2' THEN '有償'
                    WHEN mtl_cond = '3' THEN '無償'
                    ELSE                     '未設定'
                END                             AS 材料条件
                ,
                order_price                     AS 単価
                ,
                CASE
                    WHEN pro_kubun = '0' THEN '新規'
                    WHEN pro_kubun = '1' THEN '継続'
                    WHEN pro_kubun = '2' THEN '暫定'
                    WHEN pro_kubun = '3' THEN '今回'
                    WHEN pro_kubun = '4' THEN '未定'
                    ELSE                      '未設定'
                END                             AS 単価区分
                ,
                order_q - cut_siharai           AS 発注数
                ,
                siharai                         AS 検収数
            FROM order_process
            {$this->where}
            ORDER BY delivery ASC
            {$this->limit}
        ";
        return $query;
    }
    
    protected function outViewHTMLheader($session, $menu)
    {
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader($session);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/total_repeat_order_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    protected function outViewHTMLbody($session, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($session, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/total_repeat_order_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    protected function outViewHTMLfooter($session, $menu)
    {
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/total_repeat_order_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    protected function outErrorMessageHTMLbody($session, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getErrorMessageHTMLbody($session, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/total_repeat_order_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 各工程の明細リスト出力 ヘッダー部
    protected function outDetailsHTMLheader($session, $menu)
    {
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getDetailsHTMLheader($session);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/total_repeat_order_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 各工程の明細リスト出力 ボディ部
    protected function outDetailsHTMLbody($session, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getDetailsHTMLbody($session, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/total_repeat_order_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 各工程の明細リスト出力 フッター部
    protected function outDetailsHTMLfooter($session, $menu)
    {
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getDetailsHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/total_repeat_order_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部  一覧表の ヘッダー部を作成
    private function getViewHTMLheader($session)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>親機種</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>部品番号</th>\n";
        $listTable .= "        <th class='winbox' width='23%'>部品名</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>工程</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>記号</th>\n";
        $listTable .= "        <th class='winbox' width='22%'>発注先名</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>件数</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>数量</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   一覧表の 本文
    private function getViewHTMLbody($session, $menu)
    {
        $res = array();
        $rows = $this->getResult2($this->sql, $res);
        $this->viewRec = $rows;
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>発注データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $res[-1][0] = '';   // ダミー初期化
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 8%' align='right'>\n";
                $listTable .= "            <a class='button' href='javascript:win_open(\"{$menu->out_self()}?Action=Details&showMenu=ListWin&targetVendor={$res[$i][7]}&targetPartsNo=" . urlencode($res[$i][0]) . "&targetProMark={$res[$i][3]}\", 900, 600, \"\");'>\n";
                $listTable .= "        " . ($i+1) . "</a></td>\n";    // 行番号
                if ($res[$i-1][0] != $res[$i][0]) {
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][8]}</td>\n";     // 親機種
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][0]}</td>\n";     // 部品番号
                $listTable .= "        <td class='winbox' width='23%' align='left'  >{$res[$i][1]}</td>\n";     // 部品名
                } else {
                $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";     // 親機種
                $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";     // 部品番号
                $listTable .= "        <td class='winbox' width='23%' align='left'  >&nbsp;</td>\n";     // 部品名
                }
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][2]}</td>\n";     // 工程
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][3]}</td>\n";     // 記号
                $listTable .= "        <td class='winbox' width='22%' align='left'  >{$res[$i][4]}</td>\n";     // 発注先名
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >{$res[$i][5]}</td>\n";     // 件数
                $listTable .= "        <td class='winbox' width=' 8%' align='right' >" . number_format($res[$i][6]) . "</td>\n";// 合計数量
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部  一覧表の フッター部を作成
    private function getViewHTMLfooter()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='100%' align='right' >表示件数：" . number_format($this->viewRec) . "件／合計：" . number_format($this->total) . "件</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// 各工程の明細 一覧表の ヘッダー部を作成
    private function getDetailsHTMLheader($session)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>注文番号</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>製造番号</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>発注日</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>納　期</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>材料</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>単価</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>区分</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>発注数</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>検収数</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// 各工程の明細 一覧表の ボディ部を作成
    private function getDetailsHTMLbody($session, $menu)
    {
        $res = array();
        $rows = $this->getResult2($this->sql, $res);
        $this->viewRec = $rows;
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>発注データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $res[-1][0] = '';   // ダミー初期化
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // 行番号
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";     // 注文番号
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][1]}</td>\n";     // 製造番号
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][2]}</td>\n";     // 発注日
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][3]}</td>\n";     // 納期
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][4]}</td>\n";     // 材料条件
                $listTable .= "        <td class='winbox' width='11%' align='right' >" . number_format($res[$i][5], 2) . "</td>\n";// 単価
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][6]}</td>\n";     // 単価区分
                $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($res[$i][7]) . "</td>\n";// 発注数
                $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($res[$i][8]) . "</td>\n";// 検収数
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// 各工程の明細 一覧表の フッター部を作成
    private function getDetailsHTMLfooter()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='100%' align='right'>部品番号：{$this->detailsPartsNo}　部品名：{$this->detailsPartsName}　工程：{$this->detailsProMark}　発注先：{$this->detailsVendor}　合計件数：" . number_format($this->viewRec) . "件</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// 工程の明細 部品名・発注先名の設定
    private function setDetailsItemBody($session)
    {
        $query = "
            SELECT substr(midsc, 1, 20) FROM miitem WHERE mipn = '{$session->get_local('targetPartsNo')}'
        ";
        $this->getUniResult($query, $partsName);
        $query = "
            SELECT substr(name, 1, 20) FROM vendor_master WHERE vendor = '{$session->get_local('targetVendor')}'
        ";
        $this->getUniResult($query, $vendorName);
        $this->detailsPartsName = trim($partsName);
        $this->detailsVendor    = mb_substr(str_replace('　', '', trim($vendorName)), 0, 10);
        $this->detailsPartsNo   = $session->get_local('targetPartsNo');
        $this->detailsProMark   = $session->get_local('targetProMark');
    }
    
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status, $menu)
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>繰返し発注の集計結果</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../total_repeat_order.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<!-- <script type='text/javascript' src='../total_repeat_order.js'></script> -->
<script type='text/javascript'>
    function win_open(url, w, h, winName)
    {
        if (!winName) winName = '';
        if (!w) w = 980;     // 初期値
        if (!h) h = 500;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, winName, 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
</script>
</head>
<body style='background-color:#d6d3ce;'>  <!--  -->
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = 
"
</center>
</body>
{$menu->out_alert_java(false)}
</html>
";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// エラーメッセージ用 一覧表の 本文
    private function getErrorMessageHTMLbody($session, $menu)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td width='100%' align='center' class='winbox'>開始又は終了日付にエラー又はその他エラーで処理を中止しました。</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
} // Class TotalRepeatOrder_Model End

?>
