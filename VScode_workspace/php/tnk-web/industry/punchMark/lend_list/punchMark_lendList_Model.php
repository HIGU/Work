<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム 貸出台帳メニュー                         MVC Model 部   //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/16 Created   punchMark_lendList_Model.php                        //
// 2007/11/20 刻印検索で表示方法変更同じ部品は表示をブランク                //
// 2007/11/26 貸出フォームのデータ取得でgetLend()メソッドを追加             //
// 2007/11/30 win_open()にウィンドウ名を追加 LendRegist setLendBody()を追加 //
// 2007/12/03 setReturn(), setReturnCancel(), setLendCancel() を追加        //
// 2007/12/04 貸出しデータの登録取消が U → D へミス修正                    //
// 2007/12/05 貸出票の印刷 lendPrint()メソッドを追加                        //
// 2007/12/20 targetPartsNoのurlencode()が抜けていたので追加                //
// 2008/09/03 同じ部品番号でも違う内容の刻印が存在する為                    //
//            刻印コード以下も表示するように変更                       大谷 //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス
require_once ('../master/punchMark_MasterFunction.php');// 刻印管理システム共通マスター関数


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PunchMarkLendList_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $offset;                            // 共用 SQLのOFFSET句
    private $limit;                             // 共用 SQLのLIMIT句
    private $sql;                               // 共用 SQL文
    
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
        $this->offset = 'OFFSET 0';
        $this->limit  = 'LIMIT 500';
        $this->sql    = '';
    }
    
    ///// SQLのWHERE区の設定
    public function setLendWhere($session)
    {
        $this->where = $this->setLendWhereBody($session);
    }
    
    ///// SQLのORDER区の設定
    public function setLendOrder($session)
    {
        $this->order = $this->setLendOrderBody($session);
    }
    
    ///// SQL文の設定
    public function setLendSQL($session)
    {
        $this->sql = $this->setLendSQLbody($session);
    }
    
    ///// SQLのWHERE区の設定
    public function setMarkWhere($session)
    {
        $this->where = $this->setMarkWhereBody($session);
    }
    
    ///// SQLのORDER区の設定
    public function setMarkOrder($session)
    {
        $this->order = $this->setMarkOrderBody($session);
    }
    
    ///// SQL文の設定
    public function setMarkSQL($session)
    {
        $this->sql = $this->setMarkSQLbody($session);
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    貸出台帳の明細 一覧表
    public function outViewLendListHTML($session, $menu)
    {
                /***** ヘッダー部を作成 *****/
        /*****************
        $this->outViewHTMLheader($session, $menu);
        *****************/
        
                /***** 本文を作成 *****/
        $this->outViewLendHTMLbody($session, $menu);
        
                /***** フッター部を作成 *****/
        /************************
        $this->outViewHTMLfooter($session, $menu);
        ************************/
        return ;
    }
    
    ///// List部    刻印検索結果の明細 一覧表
    public function outViewMarkListHTML($session, $menu)
    {
                /***** ヘッダー部を作成 *****/
        /*****************
        $this->outViewHTMLheader($session, $menu);
        *****************/
        
                /***** 本文を作成 *****/
        $this->outViewMarkHTMLbody($session, $menu);
        
                /***** フッター部を作成 *****/
        /************************
        $this->outViewHTMLfooter($session, $menu);
        ************************/
        return ;
    }
    
    ///// 貸出フォームのデータ取得
    public function getLend($session, $result)
    {
        $this->getLendBody($session, $result);
    }
    
    ///// 貸出データの登録実行
    public function setLend($session)
    {
        $this->setLendBody($session);
    }
    
    ///// 貸出データの登録 取消
    public function setLendCancel($session)
    {
        $this->setLendCancelBody($session);
    }
    
    ///// 返却データの登録実行
    public function setReturn($session)
    {
        $this->setReturnBody($session);
    }
    
    ///// 返却データの登録 取消
    public function setReturnCancel($session)
    {
        $this->setReturnCancelBody($session);
    }
    
    ///// 刻印貸出票の印刷
    public function lendPrint($menu, $session)
    {
        $this->lendPrintBody($menu, $session);
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文のWHERE区を設定
    protected function setLendWhereBody($session)
    {
        $where = '';
        if ($session->get_local('targetPartsNo') != '') {
            $where .= "WHERE parts_no LIKE '%{$session->get_local('targetPartsNo')}%'";
        }
        if ($session->get_local('targetMarkCode') != '' && $where != '') {
            $where .= " AND punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        } elseif ($session->get_local('targetMarkCode') != '') {
            $where .= "WHERE punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        }
        if ($session->get_local('targetShelfNo') != '' && $where != '') {
            $where .= " AND shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        } elseif ($session->get_local('targetShelfNo') != '') {
            $where .= "WHERE shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        }
        if ($session->get_local('targetNote') != '' && $where != '') {
            $where .= " AND note LIKE '%{$session->get_local('targetNote')}%'";
        } elseif ($session->get_local('targetNote') != '') {
            $where .= "WHERE note LIKE '%{$session->get_local('targetNote')}%'";
        }
        return $where;
    }
    
    protected function setMarkWhereBody($session)
    {
        $where = '';
        if ($session->get_local('targetPartsNo') != '') {
            $where .= "WHERE parts_no LIKE '%{$session->get_local('targetPartsNo')}%'";
        }
        if ($session->get_local('targetMarkCode') != '' && $where != '') {
            $where .= " AND punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        } elseif ($session->get_local('targetMarkCode') != '') {
            $where .= "WHERE punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        }
        if ($session->get_local('targetShelfNo') != '' && $where != '') {
            $where .= " AND shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        } elseif ($session->get_local('targetShelfNo') != '') {
            $where .= "WHERE shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        }
        if ($session->get_local('targetNote') != '' && $where != '') {
            $where .= " AND note LIKE '%{$session->get_local('targetNote')}%'";
        } elseif ($session->get_local('targetNote') != '') {
            $where .= "WHERE note LIKE '%{$session->get_local('targetNote')}%'";
        }
        return $where;
    }
    
    protected function setLendOrderBody($session)
    {
        return 'ORDER BY lend_date DESC';
    }
    
    protected function setMarkOrderBody($session)
    {
        return 'ORDER BY parts_no ASC';
    }
    
    ///// 明細の出力 ヘッダー部
    protected function outViewHTMLheader($session, $menu)
    {
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader();
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/punchMark_lendList_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 貸出台帳 明細の出力 ボディー部
    protected function outViewLendHTMLbody($session, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewLendHTMLbody($session, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/punchMark_lendList_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 刻印検索結果 明細の出力 ボディー部
    protected function outViewMarkHTMLbody($session, $menu)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewMarkHTMLbody($session, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/punchMark_markList_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    ///// 明細の出力 フッター部
    protected function outViewHTMLfooter($session, $menu)
    {
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/punchMark_lendList_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   一覧表のSQLステートメント設定
    // 貸出台帳の SQL
    private function setLendSQLbody($session)
    {
        $query = "
            SELECT
                -- COALESCE(pre_data, '&nbsp;')
                punchmark_code                      AS 刻印コード   -- 0
                ,
                shelf_no                            AS 棚番         -- 1
                ,
                to_char(lend_date, 'YY/MM/DD HH24:MI:SS')
                                                    AS 貸出日時     -- 2
                ,
                COALESCE(
                    to_char(return_date, 'YY/MM/DD HH24:MI:SS'), '貸出中'
                )                                   AS 返却日時     -- 3
                ,
                (SELECT substr(name, 1, 6) FROM vendor_master WHERE vendor = lend_vendor)
                                                    AS 貸出先       -- 4
                ,
                (SELECT name FROM user_detailes WHERE uid = substr(lend_user, 1, 6))
                                                    AS 貸出者       -- 5
                ,
                parts_no                            AS 使用対象部品 -- 6
                ,
                CASE
                    WHEN lend.note = '' THEN '&nbsp;'
                    ELSE lend.note
                END                                 AS 備考         -- 7
                ---------------- 以下はリスト外 ------------------
                ,
                to_char(regdate, 'YYYY/MM/DD HH24:MI')
                                                    AS 更新日時     -- 8
                ,
                to_char(last_date, 'YYYY/MM/DD HH24:MI')
                                                    AS 変更日時     -- 9
                ,
                last_user                           AS 変更日時     -- 10
            FROM
                punchMark_lend_list  AS lend
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        return $query;
    }
    
    // 部品マスター・刻印マスターの SQL
    private function setMarkSQLbody($session)
    {
        $query = "
            SELECT
                mark.parts_no               AS 部品番号     -- 0
                ,
                (SELECT substr(midsc, 1, 10) FROM miitem WHERE mipn=CAST(parts_no AS CHAR(9)) LIMIT 1)
                                            AS 部品名       -- 1
                ,
                shelf_no                    AS 棚番         -- 2
                ,
                mark.punchMark_code         AS 刻印コード   -- 3
                ,
                mark                        AS 刻印内容     -- 4
                ,
                shape_name                  AS 形状名       -- 5
                ,
                size_name                   AS サイズ名     -- 6
                ,
                CASE
                    WHEN mark.note = '' THEN '&nbsp;'
                    ELSE mark.note
                END                         AS 備考         -- 7
                ,
                CASE
                    WHEN lend_flg IS TRUE THEN '貸出中'
                    ELSE ''
                END                         AS 貸出状況     -- 8
            FROM
                punchMark_parts_master AS mark
            LEFT OUTER JOIN
                punchMark_master USING (punchmark_code)
            LEFT OUTER JOIN
                punchMark_shape_master USING (shape_code)
            LEFT OUTER JOIN
                punchMark_size_master USING (size_code)
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        return $query;
    }
    
    ///// List部   貸出台帳の 明細データ作成
    private function getViewLendHTMLbody($session, $menu)
    {
        if ($this->sql == '') exit();
        $res = array();
        if ( ($rows=$this->getResult2($this->sql, $res)) <= 0) {
            // $session->add('s_sysmsg', '貸出台帳のデータがありません！');
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>貸出台帳のデータがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >\n";                       // 行番号
                if ($res[$i][3] == '貸出中' && substr($res[$i][2], 0, 8) == date('y/m/d')) {
                    $lend_url = "{$menu->out_self()}?Action=LendCancel&showMenu=CondForm&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=MarkList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>取消</a>\n";
                } else {
                    $listTable .= "        " . ($i+1) . "\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";     // 刻印コード
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][1]}</td>\n";     // 棚番
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][2]}</td>\n";     // 貸出日時
                $listTable .= "        <td class='winbox' width='10%' align='center'>\n";                       // 返却日時
                if ($res[$i][3] == '貸出中') {
                    $lend_url = "{$menu->out_self()}?Action=Return&showMenu=CondForm&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=LendList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>{$res[$i][3]}</a>\n";
                    $lend_url = "{$menu->out_self()}?Action=noAction&showMenu=LendPrint&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=LendList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>印　刷</a>\n";
                } elseif (substr($res[$i][3], 0, 8) == date('y/m/d')) {
                    $lend_url = "{$menu->out_self()}?Action=ReturnCancel&showMenu=CondForm&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=LendList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>取　消</a>" . substr($res[$i][3], 8) . "\n";
                } else {
                    $listTable .= "        {$res[$i][3]}\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width='13%' align='left'  >{$res[$i][4]}</td>\n";     // 貸出先
                $listTable .= "        <td class='winbox' width='10%' align='left'  >{$res[$i][5]}</td>\n";     // 貸出者
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][6]}</td>\n";     // 使用部品
                $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][7]}</td>\n";     // 備考
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   貸出台帳の 明細データ作成
    private function getViewMarkHTMLbody($session, $menu)
    {
        if ($this->sql == '') exit();
        $res = array();
        if ( ($rows=$this->getResult2($this->sql, $res)) <= 0) {
            // $session->add('s_sysmsg', 'データが見つかりません！');
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>データが見つかりません！</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $res[-1][0] = '';   // ダミー初期化
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right'>\n";
                if ($res[$i][8] == '貸出中') {
                    $listTable .= "            " . ($i+1) . "\n";
                    $addMsg = "<span style='color:red;'>{$res[$i][8]}</span>";
                } else {
                    $listTable .= "            <a href='javascript:PunchMarkLendList.win_open(\"{$menu->out_self()}?Action=LendRegist&showMenu=LendRegistForm&targetPartsNo=" . urlencode($res[$i][0]) . "&targetMarkCode={$res[$i][3]}&targetShelfNo={$res[$i][2]}\", 500, 600, \"LendRegist\");'>" . ($i+1) . "</a>\n";
                    $addMsg = '';
                }
                $listTable .= "         </td>\n";    // 行番号
                if ($res[$i-1][0] == $res[$i][0]) {
                    $listTable .= "        <td class='winbox' width='11%' align='center'>&nbsp;</td>\n";        // 部品番号
                    $listTable .= "        <td class='winbox' width='18%' align='left'  >&nbsp;</td>\n";        // 部品名
                } else {
                    $listTable .= "        <td class='winbox' width='11%' align='center'>{$res[$i][0]}</td>\n"; // 部品番号
                    $listTable .= "        <td class='winbox' width='18%' align='left'  >{$res[$i][1]}</td>\n"; // 部品名
                }
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][2]}</td>\n";     // 棚番
                //// 同じ部品番号でも違う内容の刻印が存在する為、刻印コード以下も表示するように変更
                //if ($res[$i-1][0] == $res[$i][0]) {
                //    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";        // 刻印コード
                //    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";        // 刻印内容
                //    $listTable .= "        <td class='winbox' width=' 6%' align='center'>&nbsp;</td>\n";        // 形状名
                //    $listTable .= "        <td class='winbox' width=' 6%' align='center'>&nbsp;</td>\n";        // サイズ
                //    $listTable .= "        <td class='winbox' width='26%' align='left'  >{$addMsg}&nbsp;</td>\n";// 備考
                //} else {
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][3]}</td>\n"; // 刻印コード
                    $tmpView = str_replace("\r", '<br>', $res[$i][4]);
                    $listTable .= "        <td class='winbox' width='14%' align='center'>{$tmpView}</td>\n";    // 刻印内容
                    $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][5]}</td>\n"; // 形状名
                    $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][6]}</td>\n"; // サイズ
                    $listTable .= "        <td class='winbox' width='22%' align='left'  >{$addMsg}{$res[$i][7]}</td>\n";// 備考
                //}
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
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>刻印コード</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>棚　番</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>貸出日時</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>返却日時</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>貸出先</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>貸出者</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>使用部品</th>\n";
        $listTable .= "        <th class='winbox' width='25%'>備　考</th>\n";
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
<title>刻印管理システム貸出台帳</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../punchMark_lendList.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../punchMark_lendList.js'></script>
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
    
    
    ///// 貸出フォームのデータ取得
    private function getLendBody($session, $result)
    {
        $execFlg = '';
        // 貸出日の取得
        $result->add('LendDate', date('Y/m/d H:i:s'));
        // 貸出先の取得
        if ($session->get_local('targetVendor') != '') {
            $query = "SELECT name FROM vendor_master WHERE vendor = '{$session->get_local('targetVendor')}'";
            $vendorName = 'マスター未登録';
            if ($this->getUniResult($query, $vendorName) < 1) {
                $execFlg = ' disabled';
            }
            $result->add('vendorName', $vendorName);
        } else {
            $result->add('vendorName', '&nbsp;');
            $execFlg = ' disabled';
        }
        // 担当者の取得
        if ($session->get_local('targetLendUser') != '') {
            $query = "SELECT name FROM user_detailes WHERE uid = '{$session->get_local('targetLendUser')}'";
            $result->add('LendUser', $session->get_local('targetLendUser'));
        } else {
            $query = "SELECT name FROM user_detailes WHERE uid = '{$session->get('User_ID')}'";
            $result->add('LendUser', $session->get('User_ID'));
        }
        $userName = 'マスター未登録';
        if ($this->getUniResult($query, $userName) < 1) {
            $execFlg = ' disabled';
        }
        $result->add('userName', $userName);
        // 使用部品名の取得
        $query = "SELECT midsc FROM miitem WHERE mipn = '{$session->get_local('targetPartsNo')}'";
        $partsName = 'マスター未登録';
        if ($this->getUniResult($query, $partsName) < 1) {
            $execFlg = ' disabled';
        }
        $result->add('partsName', $partsName);
        // 刻印内容・形状・サイズの取得
        $query = "
            SELECT mark, shape_name, size_name
            FROM punchmark_master
            LEFT OUTER JOIN punchmark_shape_master USING (shape_code)
            LEFT OUTER JOIN punchmark_size_master  USING (size_code)
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}'
            AND shelf_no = '{$session->get_local('targetShelfNo')}' LIMIT 1
        ";
        $mark  = 'マスター未登録';
        $shape = 'マスター未登録';
        $size  = 'マスター未登録';
        if ($this->getResult2($query, $res) > 0) {
            $mark  = $res[0][0];
            $shape = $res[0][1];
            $size  = $res[0][2];
        } else {
            $execFlg = ' disabled';
        }
        $result->add('Mark',  $mark);
        $result->add('Shape', $shape);
        $result->add('Size',  $size);
        $result->add('execFlg', $execFlg);
    }
    
    ///// 貸出データの登録実行
    private function setLendBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            INSERT INTO punchmark_lend_list
            (punchmark_code, shelf_no, lend_date, lend_vendor, lend_user, parts_no, note, last_user)
            VALUES ('{$session->get_local('targetMarkCode')}', '{$session->get_local('targetShelfNo')}'
                , now(), '{$session->get_local('targetVendor')}', '{$session->get_local('targetLendUser')}'
                , '{$session->get_local('targetPartsNo')}', '{$session->get_local('targetNote')}', '{$user}'
            )
            ;
            UPDATE punchmark_master SET lend_flg = TRUE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} を貸出しました。");
            // 台帳履歴の保存
            setEditHistory('punchMark_lend_list', 'I', $query);
        } else {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} の貸出登録に失敗しました。");
        }
    }
    
    ///// 貸出データの登録 取消
    private function setLendCancelBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            SELECT * FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $old_data = getPreDataRows($query);
        $query = "
            DELETE FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
            ;
            UPDATE punchmark_master SET lend_flg = FALSE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} の貸出を取消ました。");
            // 台帳履歴の保存
            setEditHistory('punchMark_lend_list', 'D', $query, $old_data);
        } else {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} の貸出取消に失敗しました。");
        }
    }
    
    ///// 返却データの登録実行
    private function setReturnBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            SELECT * FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $old_data = getPreDataRows($query);
        $query = "
            UPDATE punchmark_lend_list SET return_date = now(), last_date = now(), last_user = '{$user}'
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
            ;
            UPDATE punchmark_master SET lend_flg = FALSE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} を返却しました。");
            // 台帳履歴の保存
            setEditHistory('punchMark_lend_list', 'U', $query, $old_data);
        } else {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} の返却登録に失敗しました。");
        }
    }
    
    ///// 返却データの登録 取消
    private function setReturnCancelBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            SELECT * FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $old_data = getPreDataRows($query);
        $query = "
            UPDATE punchmark_lend_list SET return_date = NULL, last_date = now(), last_user = '{$user}'
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
            ;
            UPDATE punchmark_master SET lend_flg = TRUE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} の返却を取消しました。");
            // 台帳履歴の保存
            setEditHistory('punchMark_lend_list', 'U', $query, $old_data);
        } else {
            $session->add('s_sysmsg', "刻印コード：{$session->get_local('targetMarkCode')} の返却取消に失敗しました。");
        }
    }
    
    ///// 刻印貸出票の印刷 本体
    private function lendPrintBody($menu, $session)
    {
        $baseName = basename($_SERVER['SCRIPT_NAME'], '.php');
        // if(!extension_loaded('simplate')) { dl('simplate.so'); }
        $smarty = new simplate();
        $this->getLendPrintData($session, $smarty);
        $output  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $output .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
            // 自動印刷をする場合は プレビュー → 自動印刷 へ変更
        $output .= "<pxd name='プレビュー' title='{$menu->out_title()} 刻印貸出票' paper-type='B6' paper-name='B6-カット紙' orientation='portrait' delete='yes' save='no' print='yes' tool-fullscreen='no'>\n";
        $output .= "<page>\n";
        $output .= "<chapter name='１ページ' id='1' parent='' />\n";
        $output .= $smarty->fetch('刻印貸出票.tpl');
        $output .= "</page>\n";
        $output .= "</pxd>\n";
        header('Content-type: application/pxd;');
        header("Content-Disposition:inline;filename=\"{$baseName}.pxd\"");
        echo $output;
    }
    
    ///// 刻印貸出票の印刷用データ取得
    private function getLendPrintData($session, $smarty)
    {
        $query = "
            SELECT
                lend_date       -- 0
                ,
                lend_vendor     -- 1
                ,
                (SELECT substr(name, 1, 10) FROM vendor_master WHERE vendor = lend_vendor LIMIT 1) -- 2
                ,
                lend_user       -- 3
                ,
                (SELECT name FROM user_detailes WHERE uid = substr(lend_user, 1, 6) LIMIT 1) -- 4
                ,
                parts_no        -- 5
                ,
                (SELECT substr(midsc, 1, 12) FROM miitem WHERE mipn = parts_no LIMIT 1) -- 6
                ,
                shelf_no        -- 7
                ,
                punchmark_code  -- 8
                ,
                mark            -- 9
                ,
                shape_name      -- 10
                ,
                size_name       -- 11
                ,
                substr(lend.note, 1, 15) -- 12
            FROM
                punchMark_lend_list AS lend
            LEFT OUTER JOIN
                punchMark_master    AS master USING (punchmark_code, shelf_no)
            LEFT OUTER JOIN
                punchMark_shape_master  AS shape USING (shape_code)
            LEFT OUTER JOIN
                punchMark_size_master   AS size USING (size_code)
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) < 1) {
            $session->add('s_sysmsg', '刻印貸出票のデータ取得に失敗しました！　担当者に連絡して下さい。');
        } else {
            $smarty->assign('date', $res[0][0]);
            $smarty->assign('vendor', $res[0][1]);
            $smarty->assign('vendorName', $res[0][2]);
            $smarty->assign('user', $res[0][4]);
            $smarty->assign('partsNo', $res[0][5]);
            $smarty->assign('partsName', $res[0][6]);
            $smarty->assign('shelfNo', $res[0][7]);
            $smarty->assign('punchMarkCode', $res[0][8]);
            $smarty->assign('mark', $res[0][9]);
            $smarty->assign('shape', $res[0][10]);
            $smarty->assign('size', $res[0][11]);
            $smarty->assign('note', $res[0][12]);
            // $session->add('s_sysmsg', 'データの取得ＯＫ '.$res[0][6]);
        }
    }
    
} // Class PunchMarkLendList_Model End

?>
