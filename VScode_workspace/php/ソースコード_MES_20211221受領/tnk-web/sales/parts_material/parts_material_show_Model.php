<?php
//////////////////////////////////////////////////////////////////////////////
// 部品売上げの材料費(購入費)の 照会         MVC Model 部                   //
// Copyright (C) 2006-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created   parts_material_show_Model.php                       //
// 2009/09/16 試験・修理部門は抜出さないように変更(単価登録なしなので)      //
// 2009/10/01 商品管理部門は抜出さないように変更(単価登録なしなので)        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PartsMaterialShow_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    
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
        case 'ListTable':
            $this->where = $this->SetInitWhere($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    組立日程計画 一覧表
    public function getViewListTable($request)
    {
        $query = "
            SELECT
                sum(Uround(数量*単価, 0))           AS 部品売上高
                ,
                sum(Uround(数量*ext_cost, 0))       AS 外作部品費
                ,
                sum(Uround(数量*int_cost, 0))       AS 内作部品費
                ,
                sum(Uround(数量*unit_cost, 0))      AS 合計部品費
                ,
                count(*)                            AS 総件数
                ,
                count(*)-count(unit_cost)
                                                    AS 未登録
            FROM
                hiuuri
            LEFT OUTER JOIN
                sales_parts_material_history ON (assyno=parts_no AND 計上日=sales_date)
            {$this->where}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '売上がありません！';
            $listTable = '';
            $listTable .= "<table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td align='center' class='caption_font'>売上がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $sales     = $res[0][0];    // 売上高
            $ext_cost  = $res[0][1];    // 外作費
            $int_cost  = $res[0][2];    // 内作費
            $unit_cost = $res[0][3];    // 部品費
            $soukensu  = $res[0][4];    // 総件数
            $mitouroku = $res[0][5];    // 未登録
            if ($sales) {
                $sales_parcent     = Uround($sales / $sales * 100, 2);
                $ext_cost_parcent  = Uround($ext_cost / $sales * 100, 2);
                $int_cost_parcent  = Uround($int_cost / $sales * 100, 2);
                $unit_cost_parcent = Uround($unit_cost / $sales * 100, 2);
            } else {
                $sales_parcent     = 0;
                $ext_cost_parcent  = 0;
                $int_cost_parcent  = 0;
                $unit_cost_parcent = 0;
            }
            $listTable = '';
            $listTable .= "<table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='140' align='center' class='note1_font'>単位：円</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>部品売上高</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>部品外作費</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>部品内作費</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>合計部品費</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td align='center' class='caption_font'>金　額</td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($sales, 0) . "</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($ext_cost, 0) . "</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($int_cost, 0) . "</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($unit_cost, 0) . "</span></td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td align='center' class='caption_font'>売上高比</td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($sales_parcent, 2) . "%</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($ext_cost_parcent, 2) . "%</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($int_cost_parcent, 2) . "%</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($unit_cost_parcent, 2) . "%</span></td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='5' align='right' class='caption_font'>総件数：" . number_format($soukensu) . "　　未登録件数：" . number_format($mitouroku) . "</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function SetInitWhere($request)
    {
        $where = '';    // 初期化
        ///// 指定日の範囲
        $where .= "WHERE 計上日 >= {$request->get('targetDateStr')} AND 計上日 <= {$request->get('targetDateEnd')}";
        ///// 製品事業部の指定 (今後細分化する可能性あり)
        if ($request->get('showDiv') == 'C') {
            $where .= " AND 事業部 = 'C'";
            $where .= " and (assyno not like 'NKB%%')";
        } elseif ($request->get('showDiv') == 'L') {
            $where .= " AND 事業部 = 'L'";
            $where .= " and (assyno not like 'SS%%')";
        } else {
            $where .= " and (assyno not like 'SS%%')";
            $where .= " and (assyno not like 'NKB%%')";
        }
        ///// 製品・部品番号の指定
        if ($request->get('targetItemNo') != '') {
            $where .= " AND assyno = '{$request->get('targetItemNo')}'";
        }
        ///// 売上区分の指定 (現在は部品のみを対象とする)
        switch ($request->get('targetSalesSegment')) {
        case '1':   // 製品
            $where .= " AND datatype = '1'";
            break;
        case '2':   // 部品合計
            $where .= " AND datatype >= '5'";
            break;
        case '5':   // 部品(移動)
            $where .= " AND datatype = '5'";
            break;
        case '6':   // 部品(直納NKT)
            $where .= " AND datatype = '6'";
            break;
        case '7':   // 部品(売上)
            $where .= " AND datatype = '7'";
            break;
        case '8':   // 部品(振替)
            $where .= " AND datatype = '8'";
            break;
        case '9':   // 部品(受注)
            $where .= " AND datatype = '9'";
            break;
        default :
        }
        return $where;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    
} // Class PartsMaterialShow_Model End

?>
