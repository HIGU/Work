<?php
//////////////////////////////////////////////////////////////////////////////
// 指定検収日で指定保管場所の一覧(NKB入庫品)照会               MVC Model 部 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/21 Created   parts_storage_space_Model.php                       //
// 2006/06/24 getUniResult() → $this->getUniResult() へ変更  122行目       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PartsStorageSpace_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $last_avail_pcs;                    // 最終有効数(最終予定在庫数)
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5は __construct() ) (デストラクタ__destruct())
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
    public function getTargetDateValues($request)
    {
        // 初期化
        $option = "\n";
        $pre_ymd = '';
        for ($i=0; $i<39; $i++) {   // 31日+8日
            $yyyymmdd = workingDayOffset("-{$i}");
            if ($yyyymmdd == $pre_ymd) continue;
            $pre_ymd = $yyyymmdd;
            $yyyy = substr($yyyymmdd, 0, 4); $mm = substr($yyyymmdd, 4, 2); $dd = substr($yyyymmdd, 6, 2);
            $option .= "<option value='{$yyyymmdd}'>{$yyyy}年{$mm}月{$dd}</option>\n";
        }
        return $option;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    データの明細 一覧表
    public function outViewListHTML($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_storage_space_ViewListHeader-{$_SESSION['User_ID']}.html";
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
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_storage_space_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/parts_storage_space_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        $query = "SELECT comment FROM parts_storage_space_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO parts_storage_space_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        } else {
            $sql = "
                UPDATE parts_storage_space_comment SET comment='{$request->get('comment')}',
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
            parts_storage_space_comment ON(mipn=assy_no)
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
        $where = "
            data.ken_date >= {$request->get('targetDateStr')} AND data.ken_date <= {$request->get('targetDateEnd')}
            AND pro.next_pro = 'END..' AND plan.locate = '{$request->get('targetLocate')}'
        ";
        return $where;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   一覧表 作成
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
            $listTable .= "        <td width='100%' align='center' class='winbox'>データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right'>" . ($i+1) . "</td>\n";                     // 行番号
                $listTable .= "        <td class='winbox' width='12%' align='center'>\n";
                if ($request->get('showMenu') == 'List') {
                    $listTable .= "            <a href='{$menu->out_action('在庫経歴')}?parts_no=" . urlencode($res[$i][0]) . "' target='application' style='text-decoration:none;'>\n";
                }
                $listTable .= "        {$res[$i][0]}</a></td>\n";                                                               // 部品番号
                $listTable .= "        <td class='winbox' width='15%' align='left'>".mb_convert_kana($res[$i][1], 'k')."</td>\n";// 部品名
                $listTable .= "        <td class='winbox' width='17%' align='center'\n";
                $listTable .= "            onClick='alert(\"製造番号：{$res[$i][8]}\\n\\n注文番号：{$res[$i][9]}\");' title='ワンクリックで製造番号と注文番号を表示します。'\n";
                $listTable .= "            onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='black'; this.style.cursor='hand'; \"\n";
                $listTable .= "            onMouseout =\"this.style.backgroundColor=''; this.style.color=''; this.style.cursor='auto'; \"\n";
                $listTable .= "        >{$res[$i][2]}→{$res[$i][3]}</td>\n";                                                   // 受付→検収日
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][4]}</td>\n";                     // 受付番号
                $listTable .= "        <td class='winbox' width='10%' align='right'>".number_format($res[$i][5], 0)."</td>\n";  // 受付数(小数点以下3桁あるが整数部のみ表示)
                $listTable .= "        <td class='winbox' width='10%' align='right'>".number_format($res[$i][6], 0)."</td>\n";  // 検収数(小数点以下3桁あるが整数部のみ表示)
                $listTable .= "        <td class='winbox' width='21%' align='left'\n";
                $listTable .= "            onClick='alert(\"発注先コード：{$res[$i][10]}\");' title='ワンクリックで発注先コードを表示します。'\n";
                $listTable .= "            onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='black'; this.style.cursor='hand'; \"\n";
                $listTable .= "            onMouseout =\"this.style.backgroundColor=''; this.style.color=''; this.style.cursor='auto'; \"\n";
                $listTable .= "        >{$res[$i][7]}</td>\n";                                                                  // 発注先
                $listTable .= "    </tr>\n";
            }
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
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>部品番号</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>部　品　名</th>\n";
        $listTable .= "        <th class='winbox' width='17%'>受付→検収日</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>受付番号</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>受付数</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>検収数</th>\n";
        $listTable .= "        <th class='winbox' width='21%'>発注先名</th>\n";
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
    private function getQueryStatement($request)
    {
        $query = "
            SELECT
                  data.parts_no             AS 部品番号     -- 00
                , trim(substr(miitem.midsc, 1, 10))
                                            AS 部品名       -- 01
                , substr(to_char(data.uke_date, 'FM0000/00/00'), 3)
                                            AS 受付日       -- 02
                , substr(to_char(data.ken_date, 'FM0000/00/00'), 6)
                                            AS 検収日       -- 03
                , data.uke_no               AS 受付番号     -- 04
                , data.uke_q                AS 受付数       -- 05
                , data.genpin               AS 検収数       -- 06
                , trim(substr(ven.name, 1, 10))
                                            AS 発注先       -- 07
                --------------------------------------------以下はリスト外
                , data.sei_no               AS 製造番号     -- 08
                , data.order_no             AS 注文番号     -- 09
                , data.vendor               AS 発注先番号   -- 10
            FROM
                order_data AS data
            LEFT OUTER JOIN
                order_process AS pro USING (sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan AS plan USING (sei_no)
            LEFT OUTER JOIN
                miitem ON (plan.parts_no=mipn)
            LEFT OUTER JOIN
                vendor_master AS ven USING (vendor)
            WHERE
                data.ken_date >= {$request->get('targetDateStr')} AND data.ken_date <= {$request->get('targetDateEnd')}
                AND pro.next_pro = 'END..' AND plan.locate = '{$request->get('targetLocate')}'
            ORDER BY
                data.uke_no ASC
        ";
        return $query;
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
<link rel='stylesheet' href='../parts_storage_space.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../parts_storage_space.js'></script>
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
    
} // Class PartsStorageSpace_Model End

?>
