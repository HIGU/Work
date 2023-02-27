<?php
//////////////////////////////////////////////////////////////////////////////
// 適正在庫数の照会 直近三年間の出荷数÷３×２               MVC Model 部   //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_Model.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class ReasonableStock_Model extends daoInterfaceClass
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
        for ($i=1; $i<=36; $i++) {
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
        $file_name = "list/reasonable_stock_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/reasonable_stock_ViewListFooter-{$_SESSION['User_ID']}.html";
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
            $where .= "AND act_payable.div = 'C' ";
            break;
        case 'CH':
            $where .= "AND act_payable.div = 'C' AND (order_plan.kouji_no NOT LIKE 'SC%' OR order_plan.kouji_no IS NULL)";
            break;
        case 'CS':
            $where .= "AND act_payable.div = 'C' AND order_plan.kouji_no LIKE 'SC%' ";
            break;
        case 'LA':
            $where .= "AND act_payable.div = 'L' ";
            break;
        case 'LH':
            $where .= "AND act_payable.div = 'L' AND long.parts_no NOT LIKE 'LC%' AND long.parts_no NOT LIKE 'LR%' ";
            break;
        case 'LB':
            $where .= "AND act_payable.div = 'L' AND (long.parts_no LIKE 'LC%' OR long.parts_no LIKE 'LR%') ";
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
                    ,to_char(long.in_date, 'FM0000/00/00')
                                    AS 入庫日           -- 03
                    ,in_pcs         AS 入庫数           -- 04
                    ,tnk_stock + nk_stock
                                    AS 現在庫           -- 05
                    ,CASE
                        WHEN tanka IS NULL THEN 0
                        ELSE tanka
                     END            AS 最新単価         -- 06
                    ,CASE
                        WHEN tanka IS NULL THEN 0
                        ELSE (tnk_stock + nk_stock) * tanka
                     END            AS 金額             -- 07
                    -------------------------------------------- 以下はリスト外
                    , to_char((CAST(to_char(long.in_date, 'FM00000000') AS date) - interval '10 day'), 'YYYYMMDD') -- 10日前へ(在庫経歴で10日前を見たいため)
                                    AS 入庫日parameter  -- 08
                    ,CASE
                        WHEN trim(order_plan.kouji_no) = '' THEN '' -- 本来は→'&nbsp;'
                        WHEN order_plan.kouji_no IS NULL THEN ''    -- 本来は→'&nbsp;'
                        ELSE order_plan.kouji_no
                     END            AS 工番             -- 09
                    FROM
                        long_holding_parts_work1 AS long
                    LEFT OUTER JOIN
                        act_payable
                        ON (long.parts_no=act_payable.parts_no AND long.in_date=act_payable.act_date AND long.den_no=act_payable.uke_no)
                        -- 同じ部品番号で同日に２回検収した場合の対応で(long.in_pcs=act_payable.genpin)を追加→(long.den_no=act_payable.uke_no)へ変更
                    LEFT OUTER JOIN
                        order_plan USING(sei_no)
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
        } else {
            $this->totalMsg = $this->getSumPrice($rows, $res);
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr onDblClick='ReasonableStock.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";                    // 行番号
                if ($request->get('targetSortItem') == 'tana') {
                    $listTable .= "        <td class='winbox' width=' 6%' align='center' style='background-color:#ffffc6;'>{$res[$i][0]}</td>\n";  // 棚番
                } else {
                    $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][0]}</td>\n";                 // 棚番
                }
                if ($request->get('targetSortItem') == 'parts') {
                    $listTable .= "        <td class='winbox' width='12%' align='center' style='background-color:#ffffc6;' title='部品番号をクリックすれば在庫経歴を照会できます。'\n";
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='center' title='部品番号をクリックすれば在庫経歴を照会できます。'\n";
                }
                $listTable .= "            onClick='ReasonableStock.win_open(\"" . $menu->out_action('在庫経歴') . "?parts_no=" . urlencode($res[$i][1]) . "&date_low={$res[$i][8]}&view_rec=500&noMenu=yes\", 900, 680)'\n";
                // $listTable .= "            onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                // $listTable .= "        ><span style='color:blue;'>{$res[$i][1]}</span></td>\n";                                 // 部品番号
                $listTable .= "        ><a href='javascript:void(0);'>{$res[$i][1]}</a></td>\n";                                 // 部品番号
                if ($request->get('targetSortItem') == 'name') {
                    $listTable .= "        <td class='winbox' width='27%' align='left' style='background-color:#ffffc6;'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // 部品名
                } else {
                    $listTable .= "        <td class='winbox' width='27%' align='left'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // 部品名
                }
                if ($request->get('targetSortItem') == 'date') {
                    $listTable .= "        <td class='winbox' width='12%' align='center' style='background-color:#ffffc6;'>{$res[$i][3]}</td>\n";  // 入庫日
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][3]}</td>\n";                 // 入庫日
                }
                if ($request->get('targetSortItem') == 'in_pcs') {
                    $listTable .= "        <td class='winbox' width=' 9%' align='right' style='background-color:#ffffc6;'>" . number_format($res[$i][4]) . "</td>\n"; // 入庫数
                } else {
                    $listTable .= "        <td class='winbox' width=' 9%' align='right'>" . number_format($res[$i][4]) . "</td>\n"; // 入庫数
                }
                if ($request->get('targetSortItem') == 'stock') {
                    $listTable .= "        <td class='winbox' width=' 9%' align='right' style='background-color:#ffffc6;'>" . number_format($res[$i][5]) . "</td>\n"; // TNK在庫
                } else {
                    $listTable .= "        <td class='winbox' width=' 9%' align='right'>" . number_format($res[$i][5]) . "</td>\n"; // 現在庫
                }
                if ($request->get('targetSortItem') == 'tanka') {
                    $listTable .= "        <td class='winbox' width='10%' align='right' style='background-color:#ffffc6;'>" . number_format($res[$i][6], 2) . "</td>\n"; // 最新単価
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='right'>" . number_format($res[$i][6], 2) . "</td>\n";     // 最新単価
                }
                if ($request->get('targetSortItem') == 'price') {
                    $listTable .= "        <td class='winbox' width='10%' align='right' style='background-color:#ffffc6;'>" . number_format($res[$i][7]) . "</td>\n";  // 金額
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='right'>" . number_format($res[$i][7]) . "</td>\n";  // 金額
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
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>長期滞留部品List部</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../reasonable_stock.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript' src='../reasonable_stock.js'></script>
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
            $sumPrice += $array[$i][7];
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

} // Class ReasonableStock_Model End

?>
