<?php
//////////////////////////////////////////////////////////////////////////////
// 長期滞留部品の照会 最終入庫日指定で現在在庫がある物       MVC Model 部   //
// Copyright (C) 2006-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created   long_holding_parts_Model.php                        //
//            同日の検収２件以上の対応でAND long.in_pcs=act_payable.genpin  //
// 2006/04/04 private $order を追加し項目クリックで対応項目ソート機能実装   //
// 2006/04/05 工番を最新単価へ変更。在庫経歴をin_dateを10日前へ変更         //
//            SetInitWhere()最終入庫日と範囲日を月数指定に対応              //
// ORDER BY((long.tnk_stock+long.nk_stock) * long.tanka)→ORDER BY 金額 DESC//
// 2006/04/06 substr(long.parts_name, 1, 20)→substr(long.parts_name, 1, 16)//
//            long.in_pcs=act_payable.genpin→long.den_no=act_payable.uke_no//
//            集合出庫の範囲及び回数(物の動き)の条件オプションを実装        //
//            parts_no=" . urlencode($res[$i][1]) urlencodeを追加 -#番号対応//
// 2006/06/24 getUniResult() → $this->getUniResult() へ変更  135行目       //
// 2007/04/18 getViewHTMLbody()メソッドにonMouseoverを削除して              //
//                                  <a href='javascript:void(0);'>を追加    //
// 2007/06/05 合計件数・合計金額をボディ部からフッター部へ移動              //
//            ORDER BY long.tanka → ORDER BY 最新単価                      //
// 2008/03/11 増山部長依頼で最終入庫日の範囲を1年前から11ヶ月前に変更  大谷 //
// 2011/07/28 生産管理課 石崎係長依頼により、親機種を追加              大谷 //
// 2012/01/17 金額計算に四捨五入を追加。合計金額が最新単価を集計して        //
//            いたのを訂正                                             大谷 //
// 2013/06/13 CSV出力を追加                                            大谷 //
// 2013/10/10 出庫範囲を60ヶ月前まで抽出できるように変更               大谷 //
// 2019/01/28 ツールを追加、バイモル・標準をコメント化                 大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class LongHoldingParts_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $totalMsg;                          // フッター部の合計件数・合計金額等
    
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
            $this->where = $this->SetInitWhere($request);
            $this->order = $this->SetInitOrder($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC の Model部  最終入庫日のデータ取得
    ///// Get部    <select>用 <option>リストを取得
    public function getTargetDateView($request)
    {
        $list = "\n"; //初期化
        // 範囲は１１ヶ月前から７年前
        for ($i=11; $i<=84; $i++) {
            if ($request->get('targetDate') == $i) {
                $list .= ("<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "ヶ月前</option>\n");
            } else {
                $list .= ("<option value='{$i}'>" . mb_convert_kana($i, 'N') . "ヶ月前</option>\n");
            }
        }
        return $list;
    }
    
    ///// 最終入庫日からの範囲データ生成
    public function getTargetDateSpanView($request)
    {
        $list = "\n"; //初期化
        // 範囲は１ヶ月から12ヶ月
        for ($i=1; $i<=12; $i++) {
            if ($request->get('targetDateSpan') == $i) {
                $list .= ("<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "ヶ月間</option>\n");
            } else {
                $list .= ("<option value='{$i}'>" . mb_convert_kana($i, 'N') . "ヶ月間</option>\n");
            }
        }
        if ($request->get('targetDateSpan') == 120) {
            $list .= "<option value='120' selected>最後まで</option>\n";
        } else {
            $list .= "<option value='120'>最後まで</option>\n";
        }
        return $list;
    }
    
    ///// 出庫が現在から何ヶ月の<option>データ生成
    public function getTargetOutDateView($request)
    {
        $list = "\n"; //初期化
        // 範囲は１ヶ月から36ヶ月
        for ($i=1; $i<=60; $i++) {
            if ($request->get('targetOutDate') == $i) {
                $list .= ("<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "ヶ月前</option>\n");
            } else {
                $list .= ("<option value='{$i}'>" . mb_convert_kana($i, 'N') . "ヶ月前</option>\n");
            }
        }
        return $list;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    長期滞留部品の指定条件での 一覧表
    public function outViewListHTML($request, $menu)
    {
        /************************* ボディ ***************************/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/long_holding_parts_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        /************************* フッター ***************************/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/long_holding_parts_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// 部品のコメントを保存
    public function commentSave($request)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        // if ($request->get('comment') == '') return;  // これを行うと削除できない
        if ($request->get('targetPartsNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM long_holding_parts_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO long_holding_parts_comment (parts_no, comment, last_date, last_host)
                values ('{$request->get('targetPartsNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        } else {
            $sql = "
                UPDATE long_holding_parts_comment SET comment='{$request->get('comment')}',
                last_date='{$last_date}', last_host='{$last_host}'
                WHERE parts_no='{$request->get('targetPartsNo')}'
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        }
        return ;
    }
    
    ///// 部品のコメントを取得
    public function getComment($request, $result)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            long_holding_parts_comment ON(mipn=parts_no)
            WHERE mipn='{$request->get('targetPartsNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $result->add('comment', $res[0][0]);
            $result->add('parts_name', $res[0][1]);
            $result->add('title', "{$request->get('targetPartsNo')}：{$res[0][1]}");
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
        $where = '';    // 初期化
        ///// 最終入庫日の取得
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '{$request->get('targetDate')} month', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        ///// 何日までの取得
        $query = "SELECT to_char((CAST(to_char({$date}, 'FM00000000') AS date) - interval '{$request->get('targetDateSpan')} month'), 'YYYYMMDD')";
        $this->getUniResult($query, $toDate);
        ///// 最終入庫日から何ヶ月までと製品グループの指定でWHERE区 生成
        $where .= "WHERE long.in_date <= {$date} ";
        $where .= "AND long.in_date >= {$toDate} ";
        ///// 集合出庫の条件付加オプションチェック
        if ($request->get('targetOutFlg') == 'on') {
            // 集合出庫の月数から年月日を生成
            $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '{$request->get('targetOutDate')} month', 'YYYYMMDD')";
            $this->getUniResult($query, $outDate);
            switch ($request->get('targetOutCount')) {
            case '0':   // ０回まで(動きの無いもの)
                $where .= "AND long.out_date1 < {$outDate} ";
            case '1':   // １回まで
                $where .= "AND long.out_date2 < {$outDate} ";
            case '2':   // ２回まで
                $where .= "AND long.out_date3 < {$outDate} ";
            }
        }
        switch ($request->get('targetDivision')) {
        case 'CA':
            $where .= "AND act_payable.div = 'C' AND long.parts_no LIKE 'C%' ";
            break;
        case 'CH':
            $where .= "AND act_payable.div = 'C' AND long.parts_no LIKE 'C%' AND (order_plan.kouji_no NOT LIKE 'SC%' OR order_plan.kouji_no IS NULL)";
            break;
        case 'CS':
            $where .= "AND act_payable.div = 'C' AND long.parts_no LIKE 'C%' AND order_plan.kouji_no LIKE 'SC%' ";
            break;
        case 'LA':
            $where .= "AND act_payable.div = 'L' AND long.parts_no LIKE 'L%' ";
            break;
        /*  バイモル・標準の区別はもうないのでコメント化
        case 'LH':
            $where .= "AND act_payable.div = 'L' AND long.parts_no NOT LIKE 'LC%' AND long.parts_no NOT LIKE 'LR%' ";
            break;
        case 'LB':
            $where .= "AND act_payable.div = 'L' AND (long.parts_no LIKE 'LC%' OR long.parts_no LIKE 'LR%') ";
            break;
        */
        case 'TA':  // ツール
            $where .= "AND (long.parts_no not LIKE 'C%' AND long.parts_no not LIKE 'L%') ";
            break;
        case 'OT':  // OTHER その他 完成入庫分
            $where .= "AND act_payable.div IS NULL ";
        }
        return $where;
    }
    
    ////////// リクエストによりSQL文の基本ORDER区を設定
    protected function SetInitOrder($request)
    {
        ///// targetSortItemで切替
        switch ($request->get('targetSortItem')) {
        case 'tana':
            $order = 'ORDER BY long.tnk_tana ASC, long.parts_no ASC';
            break;
        case 'parts':
            $order = 'ORDER BY long.parts_no ASC';
            break;
        case 'name':
            $order = 'ORDER BY long.parts_name ASC';
            break;
        case 'parent':
            $order = 'ORDER BY mepnt ASC';
            break;
        case 'date':
            $order = 'ORDER BY long.in_date ASC';
            break;
        case 'in_pcs':
            $order = 'ORDER BY long.in_pcs DESC';
            break;
        case 'stock':
            $order = 'ORDER BY long.tnk_stock DESC';
            break;
        case 'tanka':
            $order = 'ORDER BY 最新単価 DESC';   // long.tanka DESC';←これはtannkaがNULLの時、最大値になってしまう
            break;
        case 'price':
            $order = 'ORDER BY 金額 DESC';  // ((long.tnk_stock+long.nk_stock) * long.tanka)←これはtankaがNULLの時、最大値になってしまう';
            break;
        default:
            $order = 'ORDER BY long.tnk_tana ASC, long.parts_no ASC';
        }
        return $order;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   長期滞留部品の指定条件での一覧取得
    private function getViewHTMLbody($request, $menu)
    {
        $query = "
            SELECT   CASE
                        WHEN trim(long.tnk_tana) = '' THEN '&nbsp;'
                        ELSE long.tnk_tana
                     END            AS 棚番号           -- 00
                    ,long.parts_no  AS 部品番号         -- 01
                    ,trim(substr(long.parts_name, 1, 16))
                                    AS 部品名           -- 02
                    ,CASE
                        WHEN mepnt='' THEN '&nbsp;'
                        WHEN mepnt IS NULL THEN '&nbsp;'
                        ELSE mepnt
                     END            AS 親機種           -- 03
                    ,to_char(long.in_date, 'FM0000/00/00')
                                    AS 入庫日           -- 04
                    ,in_pcs         AS 入庫数           -- 05
                    ,tnk_stock + nk_stock
                                    AS 現在庫           -- 06
                    ,CASE
                        WHEN tanka IS NULL THEN 0
                        ELSE tanka
                     END            AS 最新単価         -- 07
                    ,CASE
                        WHEN tanka IS NULL THEN 0
                        ELSE UROUND((tnk_stock + nk_stock) * tanka, 0)
                     END            AS 金額             -- 08
                    -------------------------------------------- 以下はリスト外
                    , to_char((CAST(to_char(long.in_date, 'FM00000000') AS date) - interval '10 day'), 'YYYYMMDD') -- 10日前へ(在庫経歴で10日前を見たいため)
                                    AS 入庫日parameter  -- 09
                    ,CASE
                        WHEN trim(order_plan.kouji_no) = '' THEN '' -- 本来は→'&nbsp;'
                        WHEN order_plan.kouji_no IS NULL THEN ''    -- 本来は→'&nbsp;'
                        ELSE order_plan.kouji_no
                     END            AS 工番             -- 10
                    FROM
                        long_holding_parts_work1 AS long
                    LEFT OUTER JOIN
                        act_payable
                        ON (long.parts_no=act_payable.parts_no AND long.in_date=act_payable.act_date AND long.den_no=act_payable.uke_no)
                        -- 同じ部品番号で同日に２回検収した場合の対応で(long.in_pcs=act_payable.genpin)を追加→(long.den_no=act_payable.uke_no)へ変更
                    LEFT OUTER JOIN
                        order_plan USING(sei_no)
                    LEFT OUTER JOIN
                        miitem ON (long.parts_no=miitem.mipn)
                    {$this->where}
                    {$this->order}
        ";
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>該当部品がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            $this->totalMsg = '&nbsp;';
            $this->csvFlg   = '0';
        } else {
            $this->csvFlg   = '1';
            $this->totalMsg = $this->getSumPrice($rows, $res);
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr onDblClick='LongHoldingParts.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' ><div class='pt11b'>" . ($i+1) . "</div></td>\n";                    // 行番号
                if ($request->get('targetSortItem') == 'tana') {
                    $listTable .= "        <td class='winbox' width=' 5%' align='center' style='background-color:#ffffc6;'><div class='pt11b'>{$res[$i][0]}</div></td>\n";  // 棚番
                } else {
                    $listTable .= "        <td class='winbox' width=' 5%' align='center'><div class='pt11b'>{$res[$i][0]}</div></td>\n";                 // 棚番
                }
                if ($request->get('targetSortItem') == 'parts') {
                    $listTable .= "        <td class='winbox' width='11%' align='center' style='background-color:#ffffc6;' title='部品番号をクリックすれば在庫経歴を照会できます。'\n";
                } else {
                    $listTable .= "        <td class='winbox' width='11%' align='center' title='部品番号をクリックすれば在庫経歴を照会できます。'\n";
                }
                $listTable .= "            onClick='LongHoldingParts.win_open(\"" . $menu->out_action('在庫経歴') . "?parts_no=" . urlencode($res[$i][1]) . "&date_low={$res[$i][8]}&view_rec=500&noMenu=yes\", 900, 680)'\n";
                // $listTable .= "            onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                // $listTable .= "        ><span style='color:blue;'>{$res[$i][1]}</span></td>\n";                                 // 部品番号
                $listTable .= "        ><a href='javascript:void(0);'><div class='pt11b'>{$res[$i][1]}</div></a></td>\n";                                 // 部品番号
                if ($request->get('targetSortItem') == 'name') {
                    $listTable .= "        <td class='winbox' width='16%' align='left' style='background-color:#ffffc6;'><div class='pt11b'>" . mb_convert_kana($res[$i][2], 'k') . "</div></td>\n";   // 部品名
                } else {
                    $listTable .= "        <td class='winbox' width='16%' align='left'><div class='pt11b'>" . mb_convert_kana($res[$i][2], 'k') . "</div></td>\n";   // 部品名
                }
                if ($request->get('targetSortItem') == 'parent') {
                    $listTable .= "        <td class='winbox' width='16%' align='left' style='background-color:#ffffc6;'><div class='pt11b'>" . mb_convert_kana($res[$i][3], 'k') . "</div></td>\n";   // 親機種
                } else {
                    $listTable .= "        <td class='winbox' width='16%' align='left'><div class='pt11b'>" . mb_convert_kana($res[$i][3], 'k') . "</div></td>\n";   // 親機種
                }
                if ($request->get('targetSortItem') == 'date') {
                    $listTable .= "        <td class='winbox' width='11%' align='center' style='background-color:#ffffc6;'><div class='pt11b'>{$res[$i][4]}</div></td>\n";  // 入庫日
                } else {
                    $listTable .= "        <td class='winbox' width='11%' align='center'><div class='pt11b'>{$res[$i][4]}</div></td>\n";                 // 入庫日
                }
                if ($request->get('targetSortItem') == 'in_pcs') {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][5]) . "</div></td>\n"; // 入庫数
                } else {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right'><div class='pt11b'>" . number_format($res[$i][5]) . "</div></td>\n"; // 入庫数
                }
                if ($request->get('targetSortItem') == 'stock') {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][6]) . "</div></td>\n"; // TNK在庫
                } else {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right'><div class='pt11b'>" . number_format($res[$i][6]) . "</div></td>\n"; // 現在庫
                }
                if ($request->get('targetSortItem') == 'tanka') {
                    $listTable .= "        <td class='winbox' width='10%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][7], 2) . "</div></td>\n"; // 最新単価
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='right'><div class='pt11b'>" . number_format($res[$i][7], 2) . "</div></td>\n";     // 最新単価
                }
                if ($request->get('targetSortItem') == 'price') {
                    $listTable .= "        <td class='winbox' width='10%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][8]) . "</div></td>\n";  // 金額
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='right'><div class='pt11b'>" . number_format($res[$i][8]) . "</div></td>\n";  // 金額
                }
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
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
        //if ($_SESSION['User_ID'] == '300144') {
            if ($this->csvFlg == '1') {
                $csv_search = $this->where . $this->order;
                // SQLのサーチ部も日本語を英字に変更。'もエラーになるので/に一時変更
                $csv_search = str_replace('最新単価','saitanka',$csv_search);
                $csv_search = str_replace('金額','kingaku',$csv_search);
                $csv_search = str_replace('\'','/',$csv_search);
                $listTable .= "<td class='winbox' align='right'><a href='../long_holding_parts_csv.php?csvsearch=$csv_search'>CSV出力</a></td>\n";
            }
        //}
        $listTable .= "        <td class='winbox' align='right'>{$this->totalMsg}</td>\n";
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
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>長期滞留部品List部</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../long_holding_parts.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript' src='../long_holding_parts.js'></script>
</head>
<body>
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
    
    ///// 合計金額・件数を計算してメッセージを返す。
    private function getSumPrice($rows, $array)
    {
        $sumPrice = 0;     // 初期化
        for ($i=0; $i<$rows; $i++) {
            $sumPrice += $array[$i][8];
        }
        $sumPrice = number_format($sumPrice);
        return "合計件数 ： {$rows} 件 &nbsp;&nbsp;&nbsp&nbsp; 合計金額 ： {$sumPrice}";
    }
    
    
    ///// 旧プログラムをとりあえずダミーメソッドで残す。getTargetDateView()内で使用していた
    private function getDummyView($rows, $array)
    {
        $list = "\n";
        ///// １.0年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '1 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>１年前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>１年前</option>\n";
        }
        ///// 1.5年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '1.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>１年半前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>１年半前</option>\n";
        }
        ///// 2.0年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '2 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>２年前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>２年前</option>\n";
        }
        ///// 2.5年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '2.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>２年半前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>２年半前</option>\n";
        }
        ///// 3.0年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '3 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>３年前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>３年前</option>\n";
        }
        ///// 3.5年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '3.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>３年半前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>３年半前</option>\n";
        }
        ///// 4.0年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '4 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>４年前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>４年前</option>\n";
        }
        ///// 4.5年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '4.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>４年半前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>４年半前</option>\n";
        }
        ///// 5.0年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>５年前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>５年前</option>\n";
        }
        ///// 5.5年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '5.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>５年半前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>５年半前</option>\n";
        }
        ///// 6.0年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '6 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>６年前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>６年前</option>\n";
        }
        ///// 6.5年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '6.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>６年半前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>６年半前</option>\n";
        }
        ///// 7.0年前
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '7 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>７年前</option>\n";
        } else {
            $list .= "                <option value='{$date}'>７年前</option>\n";
        }
        return $list;
    }

} // Class LongHoldingParts_Model End

?>
