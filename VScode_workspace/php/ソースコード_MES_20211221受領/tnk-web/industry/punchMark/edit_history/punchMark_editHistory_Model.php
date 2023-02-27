<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム 編集履歴メニュー                         MVC Model 部   //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/15 Created   punchMark_editHistory_Model.php                     //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PunchMarkEditHistory_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $offset;                            // 共用 SQLのOFFSET句
    private $limit;                             // 共用 SQLのLIMIT句
    private $sql;                               // 共用 SQL文
    private $masterOptions;                     // targetMasterの<select><option>データ
    private $historyOptions;                    // targetHistoryの<select><option>データ
    
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
        $this->order  = 'ORDER BY edit_date DESC';
        $this->offset = 'OFFSET 0';
        $this->limit  = 'LIMIT 500';
        $this->sql    = '';
        $this->masterOptions  = '';
        $this->historyOptions = '';
    }
    
    ///// SQLのWHERE区の設定
    public function setWhere($session)
    {
        $this->where = $this->setWhereBody($session);
    }
    
    ///// SQL文の設定
    public function setSQL($session)
    {
        $this->sql = $this->setSQLbody($session);
    }
    
    ///// マスター選択のHTML <select> option の出力
    public function getMasterOptions($session)
    {
        if ($this->masterOptions == '') {
            $this->masterOptions = $this->getMasterOptionsBody($session);
        }
        return $this->masterOptions;
    }
    
    ///// マスター更新内容の選択 HTML <select> option の出力
    public function getHistoryOptions($session)
    {
        if ($this->historyOptions == '') {
            $this->historyOptions = $this->getHistoryOptionsBody($session);
        }
        return $this->historyOptions;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    データの明細 一覧表
    public function outViewListHTML($session, $menu)
    {
                /***** ヘッダー部を作成 *****/
        /*****************
        $this->outViewHTMLheader($session);
        *****************/
        
                /***** 本文を作成 *****/
        $this->outViewHTMLbody($session, $menu);
        
                /***** フッター部を作成 *****/
        /************************
        $this->outViewHTMLfooter($session);
        ************************/
        return ;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文のWHERE区を設定
    protected function setWhereBody($session)
    {
        $where = '';
        if ($session->get_local('targetMaster') == 'parts') {
            $where .= "WHERE table_name = 'punchMark_parts_master'";
        } elseif ($session->get_local('targetMaster') == 'mark') {
            $where .= "WHERE table_name = 'punchMark_master'";
        } elseif ($session->get_local('targetMaster') == 'shape') {
            $where .= "WHERE table_name = 'punchMark_shape_master'";
        } elseif ($session->get_local('targetMaster') == 'size') {
            $where .= "WHERE table_name = 'punchMark_size_master'";
        }
        if ($session->get_local('targetHistory') != '' && $where != '') {
            $where .= " AND edit_code = '{$session->get_local('targetHistory')}'";
        } elseif ($session->get_local('punchMark_code') != '') {
            $where .= "WHERE edit_code = '{$session->get_local('targetHistory')}'";
        }
        return $where;
    }
    
    ///// マスター選択のHTML <select> option の出力
    protected function getMasterOptionsBody($session)
    {
        $options = "\n";
        if ($session->get_local('targetMaster') == 'parts') {
            $options .= "<option value='parts' selected>部品マスター</option>\n";
        } else {
            $options .= "<option value='parts'>部品マスター</option>\n";
        }
        if ($session->get_local('targetMaster') == 'mark') {
            $options .= "<option value='mark' selected>刻印マスター</option>\n";
        } else {
            $options .= "<option value='mark'>刻印マスター</option>\n";
        }
        if ($session->get_local('targetMaster') == 'shape') {
            $options .= "<option value='shape' selected>形状マスター</option>\n";
        } else {
            $options .= "<option value='shape'>形状マスター</option>\n";
        }
        if ($session->get_local('targetMaster') == 'size') {
            $options .= "<option value='size' selected>サイズマスター</option>\n";
        } else {
            $options .= "<option value='size'>サイズマスター</option>\n";
        }
        return $options;
    }
    
    ///// マスター更新内容の選択 HTML <select> option の出力
    protected function getHistoryOptionsBody($session)
    {
        $options = "\n";
        if ($session->get_local('targetHistory') == 'U') {
            $options .= "<option value='U' selected>変更履歴</option>\n";
        } else {
            $options .= "<option value='U'>変更履歴</option>\n";
        }
        if ($session->get_local('targetHistory') == 'D') {
            $options .= "<option value='D' selected>削除履歴</option>\n";
        } else {
            $options .= "<option value='D'>削除履歴</option>\n";
        }
        if ($session->get_local('targetHistory') == 'I') {
            $options .= "<option value='I' selected>追加履歴</option>\n";
        } else {
            $options .= "<option value='I'>追加履歴</option>\n";
        }
        return $options;
    }
    
    ///// 明細の出力 ヘッダー部
    protected function outViewHTMLheader($session)
    {
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader();
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/punchMark_editHistory_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 明細の出力 ボディー部
    protected function outViewHTMLbody($session, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($session, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/punchMark_editHistory_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 明細の出力 フッター部
    protected function outViewHTMLfooter($session)
    {
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/punchMark_editHistory_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   一覧表のSQLステートメント設定
    // 部品マスター・刻印マスター・形状マスター・サイズマスターの SQL
    private function setSQLbody($session)
    {
        $query = "
            SELECT
                -- COALESCE(pre_data, '&nbsp;')
                CASE
                    WHEN pre_data = '' THEN '&nbsp;'
                    ELSE pre_data
                END                                 AS 更新前データ -- 0
                ,
                to_char(edit_date, 'YYYY/MM/DD HH24:MI')
                                                    AS 変更日時     -- 1
                ,
                (SELECT name FROM user_detailes WHERE uid = substr(edit_user, 1, 6))
                                                    AS 更新者       -- 2
                ,
                substr(edit_user, 8)                AS IPホスト名   -- 3
                ---------------- 以下はリスト外 ------------------
                ,
                edit_sql                            AS 更新SQL      -- 4
            FROM
                punchMark_edit_history  AS edit
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        return $query;
    }
    
    ///// List部   検索結果の 明細データ作成
    private function getViewHTMLbody($session, $menu)
    {
        if ($this->sql == '') exit();
        $res = array();
        if ( ($rows=$this->getResult2($this->sql, $res)) <= 0) {
            // $session->add('s_sysmsg', '更新経歴がありません！');
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>更新経歴がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $viewSQL = str_replace("\n", '\n', $res[$i][4]);    // LFをリテラル文字定数へ変換
                $viewSQL = str_replace("\r", '', $viewSQL);         // CRを削除
                $viewSQL = str_replace("'", '', $viewSQL);          // シングルクォートを削除
                /*****
                if ($res[$i][10] != '') {   // コメントがあれば色を変える
                    $listTable .= "    <tr onDblClick='PunchMarkEditHistory.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='PunchMarkEditHistory.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                }
                *****/
                $listTable .= "    <tr ondblClick='alert(\"{$viewSQL}\");'>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // 行番号
                // $listTable .= "        <td class='winbox' width=' 8%' align='right' >\n";
                // $listTable .= "            <a href='javascript:PunchMarkEditHistory.win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>明細</a>\n";
                // $listTable .= "        </td>\n"; // 明細クリック用
                $listTable .= "        <td class='winbox' width='40%' align='left'  >{$res[$i][0]}</td>\n";     // 更新前データ
                $listTable .= "        <td class='winbox' width='20%' align='center'>{$res[$i][1]}</td>\n";     // 更新日時
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][2]}</td>\n";     // 更新者
                $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][3]}</td>\n";     // IPホスト名
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
    private function getViewHTMLheader()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>更新履歴</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='40%'>更新前データ</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>更新日時</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>更新者</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>IPホスト名</th>\n";
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
        $listTable .= "        <td class='winbox' width='79%' align='right'>合計件数</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>{$this->sumCount}</td>\n";
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
<title>刻印管理システム編集履歴</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../punchMark_editHistory.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../punchMark_editHistory.js'></script>
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
    
    
} // Class PunchMarkEditHistory_Model End

?>
