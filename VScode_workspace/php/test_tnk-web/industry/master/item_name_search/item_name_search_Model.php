<?php
//////////////////////////////////////////////////////////////////////////////
// アイテムマスターの品名による前方検索・部分検索            MVC Model 部   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created   item_name_search_Model.php                          //
// 2006/04/11 項目ソート時はメッセージ(品名の最初に一致など)を表示しない    //
// 2006/05/22 材質によるマスター検索を追加 targetItemMaterial  targetLimit  //
// 2006/05/23 在庫チェックオプションを追加 targetStockOption                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class ItemNameSearch_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $limit;                             // 共用 SQLのLIMIT句
    private $option;                            // 共有 SQL関数のoption
    
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
            $this->limit = $this->SetInitLimit($request);
            $this->SetInitStockOption($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    アイテムマスターの指定条件での 一覧表
    public function outViewListHTML($request, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLtable($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/item_name_search_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function SetInitWhere($request)
    {
        $where = '';    // 初期化
        switch ($request->get('targetDivision')) {
        case 'A':   // すべて
            $where = '';
            break;
        case 'C':
            $where = 'C';
            // $where .= "WHERE parts_no LIKE 'C%' OR parts_name LIKE '＊＊＊%' OR parts_name LIKE '　　　あ%'";
            break;
        case 'L':
            $where = 'L';
            // $where .= "WHERE parts_no LIKE 'L%' OR parts_name LIKE '＊＊＊%' OR parts_name LIKE '　　　あ%'";
            break;
        case 'T':
            $where = 'T';
            // $where .= "WHERE parts_no LIKE 'T%' OR parts_name LIKE '＊＊＊%' OR parts_name LIKE '　　　あ%'";
            break;
        case 'O':   // OTHER その他
            $where = 'F';
            // $where .= "WHERE (parts_no NOT LIKE 'C%' AND parts_no NOT LIKE 'L%' parts_no NOT LIKE 'T%') OR parts_name LIKE '＊＊＊%' OR parts_name LIKE '　　　あ%'";
        }
        return $where;
    }
    
    ////////// リクエストによりSQL文の基本ORDER区を設定
    protected function SetInitOrder($request)
    {
        ///// targetSortItemで切替
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $order = 'ORDER BY parts_no ASC';
            break;
        case 'name':
            $order = 'ORDER BY parts_name ASC';
            break;
        case 'material':
            $order = 'ORDER BY material ASC';
            break;
        case 'parent':
            $order = 'ORDER BY parent ASC';
            break;
        case 'date':
            $order = 'ORDER BY as_date ASC';
            break;
        default:
            $order = '';
        }
        return $order;
    }
    
    ////////// リクエストによりSQL文の基本LIMIT区を設定
    protected function SetInitLimit($request)
    {
        ///// targetLimitで切替
        switch ($request->get('targetLimit')) {
        case 10000:
            $limit = 'LIMIT 10000 OFFSET 0';
            $limit = 10000;  // 現在はこちらを採用
            break;
        case 8000:
            $limit = 'LIMIT 8000 OFFSET 0';
            $limit = 8000;  // 現在はこちらを採用
            break;
        case 4000:
            $limit = 'LIMIT 4000 OFFSET 0';
            $limit = 4000;
            break;
        case 2000:
            $limit = 'LIMIT 2000 OFFSET 0';
            $limit = 2000;
            break;
        case 1000:
            $limit = 'LIMIT 1000 OFFSET 0';
            $limit = 1000;
            break;
        case 600:
            $limit = 'LIMIT 600 OFFSET 0';
            $limit = 600;
            break;
        case 300:
        default:
            $limit = 'LIMIT 300 OFFSET 0';
            $limit = 300;
        }
        return $limit;
    }
    
    ////////// リクエストによりSQL関数の在庫チェックオプションを設定
    protected function SetInitStockOption($request)
    {
        $this->option = $request->get('targetStockOption');
        return $this->option;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   アイテムマスターの指定条件での一覧取得
    private function getViewHTMLtable($request, $menu)
    {
        $query = "
            SELECT   CASE
                        WHEN parts_no = '' THEN '&nbsp;'
                        ELSE parts_no
                     END            AS 部品番号         -- 00
                    ,CASE
                        WHEN substr(parts_name, 1, 3) = '＊＊＊' THEN '<span style=''color:teal;''>' || parts_name || '</span>'
                        WHEN substr(parts_name, 1, 3) = '　　　' THEN '<span style=''color:red;''>' || parts_name || '</span>'
                        ELSE parts_name
                     END            AS 部品名           -- 01
                    ,CASE
                        WHEN material = '' THEN '&nbsp;'
                        ELSE material
                     END            AS 材質             -- 02
                    ,CASE
                        WHEN parent = '' THEN '&nbsp;'
                        ELSE parent
                     END        AS 親機種名             -- 03
                    ,CASE
                        WHEN as_date IS NULL THEN '&nbsp;'
                        WHEN as_date = 0     THEN '&nbsp;'
                        ELSE to_char(as_date, 'FM0000/00/00')
                     END            AS 更新日           -- 04
                    FROM
        ";
        if ($request->get('targetItemName')) {
            $query .= "                item_master_name_search_stock('{$request->get('targetItemName')}', '{$this->where}', '{$this->option}', {$this->limit})\n";
        } else {
            $query .= "                item_master_material_search_stock('{$request->get('targetItemMaterial')}', '{$this->where}', '{$this->option}', {$this->limit})\n";
        }
        $query .= "            {$this->order}\n";
        // 初期化
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
        } else {
            $decrement = 0;
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                if (mb_ereg_match('<span', $res[$i][1])) {
                    $decrement++;
                    if ($request->get('targetSortItem') != '') {
                        continue;       // 項目ソート時はメッセージを表示しない
                    }
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >&nbsp;</td>\n";                        // 行番号
                } else {
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1-$decrement) . "</td>\n";     // 行番号
                }
                if ($request->get('targetSortItem') == 'parts') {
                    $listTable .= "        <td class='winbox' width='12%' align='center' style='background-color:#ffffc6;' title='部品番号をクリックすれば在庫経歴を照会できます。'\n";
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='center' title='部品番号をクリックすれば在庫経歴を照会できます。'\n";
                }
                if ($res[$i][0] != '&nbsp;') {
                    $listTable .= "            onClick='ItemNameSearch.win_open(\"" . $menu->out_action('在庫経歴') . "?parts_no=" . urlencode($res[$i][0]) . "&&view_rec=500&noMenu=yes\", 900, 680)'\n";
                    $listTable .= "            onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                }
                $listTable .= "        ><span style='color:blue;'>{$res[$i][0]}</span></td>\n";                                 // 部品番号
                if ($request->get('targetSortItem') == 'name') {
                    $listTable .= "        <td class='winbox' width='41%' align='left' style='background-color:#ffffc6;'>" . mb_convert_kana($res[$i][1], 'k') . "</td>\n";   // 部品名
                } else {
                    $listTable .= "        <td class='winbox' width='41%' align='left'>" . mb_convert_kana($res[$i][1], 'k') . "</td>\n";   // 部品名
                }
                if ($request->get('targetSortItem') == 'material') {
                    $listTable .= "        <td class='winbox' width='12%' align='left' style='background-color:#ffffc6;'>{$res[$i][2]}</td>\n";  // 材質
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='left'>{$res[$i][2]}</td>\n";                 // 材質
                }
                if ($request->get('targetSortItem') == 'parent') {
                    $listTable .= "        <td class='winbox' width='18%' align='left' style='background-color:#ffffc6;'>{$res[$i][3]}</td>\n"; // 親機種名
                } else {
                    $listTable .= "        <td class='winbox' width='18%' align='left'>{$res[$i][3]}</td>\n"; // 親機種名
                }
                if ($request->get('targetSortItem') == 'date') {
                    $listTable .= "        <td class='winbox' width='12%' align='center' style='background-color:#ffffc6;'>{$res[$i][4]}</td>\n"; // 更新日
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][4]}</td>\n"; // 更新日
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
    
    ///// 固定のList部    HTMLファイル出力  以下のソースは見ずらいが出力結果を見やすくするため
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
<title>アイテムマスターの検索結果List部</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../item_name_search.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../item_name_search.js'></script>
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
    
    
} // Class ItemNameSearch_Model End

?>
