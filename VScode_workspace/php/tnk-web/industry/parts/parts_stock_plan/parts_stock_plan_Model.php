<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 予定 照会 (引当･発注状況照会)                   MVC Model 部   //
// Copyright (C) 2006-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/25 Created   parts_stock_plan_Model.php                          //
// 2006/05/28 メソッドの getViewHTMLtable() → getViewHTMLbody()へ名前変更  //
// 2007/02/08 タイトル行のラップ制御で $titleにnowrap追加しgetViewHTMLconst //
//            ('header')に overflow-x:hidden; overflow-y:scroll; を追加     //
//            備考のブランク対応でif ($res[$i][9] == '') {を追加            //
// 2007/02/09 上記の備考のブランクチェックは中止→parts_stock_plan()PL/pgsql//
// 2007/02/22 補用の引当の場合にラップしてしまうため計画番号に nowrap 追加  //
// 2007/02/26 製品名を15文字へ変更 mb_substr($res[$i][4], 0, 15)            //
//            半角カナの場合変換後文字数がオーバーするので変換後15文字にする//
//            上記に伴い製品名にnowrap 製品番号がSC工番の時 yellow 追加     //
// 2007/03/02 引当の予定計画を色変更するためgetPlanStatus()メソッドを追加   //
// 2007/03/24 header に $title = mb_convert_kana($title, 'k') を追加        //
// 2007/04/27 getViewHTMLbody()メソッドに購買回答納期を追加 (色はdarkred)   //
// 2007/05/17 getViewHTMLfooter()メソッドに月平均出庫数と保有月を追加       //
// 2007/05/21 必要日のチェック用にgetQueryStatement()メソッドにロジック追加 //
// 2007/06/22 getViewHTMLconst()にMenuHeaderクラスのout_retF2Script()を追加 //
//                ↑ の呼出し時に noMenu のパラメータチェックも行う         //
// 2016/08/08 mouseOverの追加                                          大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PartsStockPlan_Model extends daoInterfaceClass
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
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    グラフデータの明細 一覧表
    public function outViewListHTML($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader($request);
        // 固定のHTMLソースを取得
        if ($request->get('noMenu') == '') {
            $headHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $headHTML .= $this->getViewHTMLconst('footer');
        }
        // HTMLファイル出力
        $file_name = "list/parts_stock_plan_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        if ($request->get('noMenu') == '') {
            $listHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $listHTML .= $this->getViewHTMLconst('footer');
        }
        // HTMLファイル出力
        $file_name = "list/parts_stock_plan_ViewList-{$_SESSION['User_ID']}.html";
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
        if ($request->get('noMenu') == '') {
            $footHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $footHTML .= $this->getViewHTMLconst('footer');
        }
        // HTMLファイル出力
        $file_name = "list/parts_stock_plan_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        $query = $this->getQueryStatement($request);
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $listTable .= "    <tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
            $listTable .= "        <td class='winbox' width='79%' colspan='8' align='right'>{$res[0][4]}</td>\n";
            $listTable .= "        <td class='winbox' width=' 9%' colspan='1' align='right'>{$res[0][7]}</td>\n";
            $listTable .= "        <td class='winbox' width='12%' colspan='2' align='right'>&nbsp;</td>\n";
            $listTable .= "    </tr>\n";
            for ($i=1; $i<$rows; $i++) {
                $res[$i][4] = mb_convert_kana($res[$i][4], 'k');    // 前もって半角カナに変換
                $res[$i][4] = mb_substr($res[$i][4], 0, 15);        // 半角カナの場合オーバーするので変換後15文字にする
                if (mb_substr($res[$i][3], 0, 3) == '検査中') {
                    $colorKen = " style=' color:blue;'";
                } elseif (substr($res[$i][3], 0, 2) == 'SC') {
                    $colorKen = " style=' color:yellow;'";
                } elseif (mb_substr($res[$i][4], 0, 2) == '回答') {
                    $colorKen = " style=' color:darkred;'";
                } else {
                    $colorKen = '';
                }
                if ($request->get('aden_key') != '') {
                    if ($res[$i][2] == $request->get('aden_plan')) $colorPlan=" style=' background-color:#ffffc6;'"; else $colorPlan = '';
                } else {
                    if ($this->getPlanStatus($res[$i][2]) == 'P') $colorPlan=" style=' background-color:#ffffc6;'"; else $colorPlan = '';
                }
                $listTable .= "    <tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i) . "</td>\n";      // 行番号
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][0]}</td>\n";     // 集荷日
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][1]}</td>\n";     // 実施日
                $listTable .= "        <td class='winbox' width='10%' align='right' nowrap{$colorPlan}>{$res[$i][2]}</td>\n";   // 計画番号
                $listTable .= "        <td class='winbox' width='12%' align='right'{$colorKen}>{$res[$i][3]}</td>\n";           // 製品番号
                $listTable .= "        <td class='winbox' width='18%' align='left'  nowrap{$colorKen}>{$res[$i][4]}</td>\n";    // 製品名
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >{$res[$i][5]}</td>\n";     // 引当数
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >{$res[$i][6]}</td>\n";     // 発注数
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >{$res[$i][7]}</td>\n";     // 有効数
                $listTable .= "        <td class='winbox' width=' 4%' align='center'>{$res[$i][8]}</td>\n";     // チェック
                $listTable .= "        <td class='winbox' width=' 8%' align='left'>{$res[$i][9]}</td>\n";       // 備考
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            $this->last_avail_pcs = $res[$rows-1][7];
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
        $title = mb_convert_kana($title, 'k');  // 全角カナでは横幅が入りきらない場合があるため半角へ
        if (!$title) {  // レコードが無い場合もNULLレコードが返るため変数の内容でチェックする
            $title = 'アイテムマスター未登録！';
        }
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11' nowrap>{$title}</th>\n";
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
    private function getViewHTMLfooter($request)
    {
        // 計算時在庫数・月平均出庫数・保有月を追加
        $query = "
            SELECT invent_pcs, month_pickup_avr, hold_monthly_avr FROM inventory_average_summary
            WHERE parts_no = '{$request->get('targetPartsNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $invent = number_format($res[0][0], 0);
            $pickup = number_format($res[0][1], 0);
            $month  = number_format($res[0][2], 1);
            $footer_title = "計算時在庫：{$invent}　月平均出庫：{$pickup}　<span style='color:teal;'>保有月：{$month}</span>";
        } else {
            $footer_title = '&nbsp;';
        }
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='60%' align='center'>{$footer_title}</td>\n";
        $listTable .= "        <td class='winbox' width='19%' align='right'>最終有効在庫数</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>{$this->last_avail_pcs}</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>&nbsp;</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   一覧表のSQLステートメント取得
    private function getQueryStatement($request)
    {
        $query = "
            SELECT   CASE
                        WHEN syuka = 0 THEN '未定'
                        ELSE substr(to_char(syuka, 'FM9999/99/99'), 6, 5)
                     END            AS 集荷日                   -- 00
                    ,CASE
                        WHEN chaku = 0 THEN '未定'
                        ELSE substr(to_char(chaku, 'FM9999/99/99'), 6, 5)
                     END            AS 実施日                   -- 01
                    ,plan_no        AS 計画番号                 -- 02
                    ,CASE
                        WHEN assy_no = '' THEN '&nbsp;'
                        ELSE assy_no
                     END            AS 製品番号                 -- 03
                    ,CASE
                        WHEN assy_name = '' THEN '&nbsp;'
                        ELSE substr(assy_name, 1, 15)
                     END            AS 製品名                   -- 04
                    ,CASE
                        WHEN allocate = 0 THEN '&nbsp;'
                        ELSE to_char(allocate, 'FM9,999,999')
                     END            AS 引当数                   -- 05
                    ,CASE
                        WHEN order_pcs = 0 THEN '&nbsp;'
                        ELSE to_char(order_pcs, 'FM9,999,999')
                     END            AS 発注数                   -- 06
                    ,CASE
                        WHEN avail_pcs IS NULL THEN '&nbsp;'
                        ELSE to_char(avail_pcs, 'FM9,999,999')
                     END            AS 有効数                   -- 07
                    ,CASE
                        WHEN avail_msg = '' THEN '&nbsp;'
                        ELSE avail_msg
                     END            AS チェック                 -- 08
                    ,CASE
                        WHEN note = '' THEN '&nbsp;'
                        ELSE note
                     END            AS 備考                     -- 09
            FROM
        ";
        if ($request->get('requireDate')) {
            $query .= "
                parts_stock_plan('{$request->get('targetPartsNo')}', '必要日')
            ";
        } else {
            $query .= "
                parts_stock_plan('{$request->get('targetPartsNo')}')
            ";
        }
        return $query;
    }
    
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status, $menu='')
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
<title>部品在庫予定照会</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../parts_stock_plan.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
<script type='text/javascript' src='../parts_stock_plan.js'></script>
</head>
<body style='background-color:#d6d3ce;'>
<center>
";
        } elseif ($status == 'footer') {
            if (is_object($menu)) {
                $listHTML = $menu->out_retF2Script('_parent', 'N');
            } else {
                $listHTML = '';
            }
            $listHTML .= "</center>\n";
            $listHTML .= "</body>\n";
            $listHTML .= "</html>\n";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// 計画番号の適正をチェックしてテーブルから 確定=F, 予定=P 該当計画無し=''を返す
    private function getPlanStatus($plan_no)
    {
        $p_kubun = '';
        if (strlen($plan_no) == 8) {
            $query = "
                SELECT p_kubun FROM assembly_schedule WHERE plan_no='{$plan_no}'
            ";
            $this->getUniResult($query, $p_kubun);
            return $p_kubun;
        }
        return $p_kubun;
    }
    
} // Class PartsStockPlan_Model End

?>
