<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム 検索メニュー                             MVC Model 部   //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/14 Created   punchMark_search_Model.php                          //
// 2007/11/15 製作中を赤色表示                                              //
// 2008/09/03 BODYの表示を調整                                         大谷 //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PunchMarkSearch_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $offset;                            // 共用 SQLのOFFSET句
    private $limit;                             // 共用 SQLのLIMIT句
    private $sql;                               // 共用 SQL文
    private $shapeCode;                         // shape_codeの<select><option>データ
    private $sizeCode;                          // size_codeの<select><option>データ
    private $makeFlg;                           // make_flgの<select><option>データ
    
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
        $this->shapeCode = '';
        $this->sizeCode  = '';
        $this->makeFlg   = '';
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
    
    ///// 形状マスターのHTML <select> option の出力
    public function getShapeCodeOptions($session)
    {
        if ($this->shapeCode == '') {
            $this->shapeCode = $this->getShapeCodeOptionsBody($session);
        }
        return $this->shapeCode;
    }
    
    ///// サイズマスターのHTML <select> option の出力
    public function getSizeCodeOptions($session)
    {
        if ($this->sizeCode == '') {
            $this->sizeCode = $this->getSizeCodeOptionsBody($session);
        }
        return $this->sizeCode;
    }
    
    ///// 製作状況のHTML <select> option の出力
    public function getMakeFlgOptions($session)
    {
        if ($this->makeFlg == '') {
            $this->makeFlg = $this->getMakeFlgOptionsBody($session);
        }
        return $this->makeFlg;
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
    
    ///// 刻印のコメントを保存
    public function commentSave($request)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        // if ($request->get('comment') == '') return;  // これを行うと削除できない
        if ($request->get('targetPlanNo') == '') return;
        if ($request->get('targetAssyNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM punchMark_search_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO punchMark_search_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        } else {
            $sql = "
                UPDATE punchMark_search_comment SET comment='{$request->get('comment')}',
                last_date='{$last_date}', last_host='{$last_host}'
                WHERE plan_no='{$request->get('targetPlanNo')}'
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの保存が出来ませんでした！　管理担当者へ連絡して下さい。";
            }
        }
        return ;
    }
    
    ///// 刻印のコメントを取得
    public function getComment($request, $result)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        if ($request->get('targetAssyNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            punchMark_search_comment ON(mipn=assy_no)
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
        $where = '';
        if ($session->get_local('parts_no') != '') {
            $where .= "WHERE parts_no LIKE '%{$session->get_local('parts_no')}%'";
        }
        if ($session->get_local('punchMark_code') != '' && $where != '') {
            $where .= " AND punchMark_code LIKE '%{$session->get_local('punchMark_code')}%'";
        } elseif ($session->get_local('punchMark_code') != '') {
            $where .= "WHERE punchMark_code LIKE '%{$session->get_local('punchMark_code')}%'";
        }
        if ($session->get_local('shelf_no') != '' && $where != '') {
            $where .= " AND shelf_no LIKE '%{$session->get_local('shelf_no')}%'";
        } elseif ($session->get_local('shelf_no') != '') {
            $where .= "WHERE shelf_no LIKE '%{$session->get_local('shelf_no')}%'";
        }
        if ($session->get_local('mark') != '' && $where != '') {
            $where .= " AND mark LIKE '%{$session->get_local('mark')}%'";
        } elseif ($session->get_local('mark') != '') {
            $where .= "WHERE mark LIKE '%{$session->get_local('mark')}%'";
        }
        if ($session->get_local('shape_code') != '' && $where != '') {
            $where .= " AND shape_code LIKE '%{$session->get_local('shape_code')}%'";
        } elseif ($session->get_local('shape_code') != '') {
            $where .= "WHERE shape_code LIKE '%{$session->get_local('shape_code')}%'";
        }
        if ($session->get_local('size_code') != '' && $where != '') {
            $where .= " AND size_code LIKE '%{$session->get_local('size_code')}%'";
        } elseif ($session->get_local('size_code') != '') {
            $where .= "WHERE size_code LIKE '%{$session->get_local('size_code')}%'";
        }
        if ($session->get_local('user_code') != '' && $where != '') {
            $where .= " AND user_code LIKE '%{$session->get_local('user_code')}%'";
        } elseif ($session->get_local('user_code') != '') {
            $where .= "WHERE user_code LIKE '%{$session->get_local('user_code')}%'";
        }
        if ($session->get_local('make_flg') != '' && $where != '') {
            $where .= " AND make_flg = '{$session->get_local('make_flg')}'";
        } elseif ($session->get_local('make_flg') != '') {
            $where .= "WHERE make_flg = '{$session->get_local('make_flg')}'";
        }
        if ($session->get_local('note_parts') != '' && $where != '') {
            $where .= " AND parts.note LIKE '%{$session->get_local('note_parts')}%'";
        } elseif ($session->get_local('note_parts') != '') {
            $where .= "WHERE parts.note LIKE '%{$session->get_local('note_parts')}%'";
        }
        if ($session->get_local('note_mark') != '' && $where != '') {
            $where .= " AND mark.note LIKE '%{$session->get_local('note_mark')}%'";
        } elseif ($session->get_local('note_mark') != '') {
            $where .= "WHERE mark.note LIKE '%{$session->get_local('note_mark')}%'";
        }
        if ($session->get_local('note_shape') != '' && $where != '') {
            $where .= " AND shape.note LIKE '%{$session->get_local('note_shape')}%'";
        } elseif ($session->get_local('note_shape') != '') {
            $where .= "WHERE shape.note LIKE '%{$session->get_local('note_shape')}%'";
        }
        if ($session->get_local('note_size') != '' && $where != '') {
            $where .= " AND size.note LIKE '%{$session->get_local('note_size')}%'";
        } elseif ($session->get_local('note_size') != '') {
            $where .= "WHERE size.note LIKE '%{$session->get_local('note_size')}%'";
        }
        return $where;
    }
    
    ///// 形状マスターのHTML <select> option の出力
    protected function getShapeCodeOptionsBody($session)
    {
        $query = "SELECT shape_code, shape_name FROM punchMark_shape_master ORDER BY shape_code ASC";
        $res = array();
        if (($rows=getResult2($query, $res)) <= 0) return '';
        $options = "\n";
        $options .= "<option value='' style='color:red;'>未選択</option>\n";
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('shape_code') == $res[$i][0]) {
                $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
            } else {
                $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
            }
        }
        return $options;
    }
    
    ///// サイズマスターのHTML <select> option の出力
    protected function getSizeCodeOptionsBody($session)
    {
        $query = "SELECT size_code, size_name FROM punchMark_size_master ORDER BY size_code ASC";
        $res = array();
        if (($rows=getResult2($query, $res)) <= 0) return '';
        $options = "\n";
        $options .= "<option value='' style='color:red;'>未選択</option>\n";
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('size_code') == $res[$i][0]) {
                $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
            } else {
                $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
            }
        }
        return $options;
    }
    
    ///// 製作状況のHTML <select> option の出力
    protected function getMakeFlgOptionsBody($session)
    {
        $options = "\n";
        $options .= "<option value='' style='color:red;'>未選択</option>\n";
        if ($session->get_local('make_flg') == 'f') {
            $options .= "<option value='f' selected>製作済</option>\n";
        } else {
            $options .= "<option value='f'>製作済</option>\n";
        }
        if ($session->get_local('make_flg') == 't') {
            $options .= "<option value='t' selected>製作中</option>\n";
        } else {
            $options .= "<option value='t'>製作中</option>\n";
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
        $file_name = "list/punchMark_search_ViewListHeader-{$session->get('User_ID')}.html";
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
        $file_name = "list/punchMark_search_ViewList-{$session->get('User_ID')}.html";
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
        $file_name = "list/punchMark_search_ViewListFooter-{$session->get('User_ID')}.html";
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
                parts.parts_no                      AS 部品番号     -- 0
                ,
                substr(midsc, 1, 10)                AS 部品名       -- 1
                ,
                parts.punchMark_code                AS 刻印コード   -- 2
                ,
                shelf_no                            AS 棚番         -- 3
                ,
                mark                                AS 刻印内容     -- 4
                ,
                shape_name                          AS 形状名       -- 5
                ,
                -- COALESCE(user_code, '&nbsp;') -- 左から順番にNULLでない最初の値を返す
                CASE
                    WHEN user_code = '' THEN '&nbsp;'
                    ELSE user_code
                END                                 AS 客先コード   -- 6
                ,
                size_name                           AS サイズ名     -- 7
                ,
                CASE WHEN make_flg IS TRUE THEN '製作中'
                     ELSE '製作済'
                END                                 AS  製作状況    -- 8
                ,
                CASE
                    WHEN parts.note = '' THEN '&nbsp;'
                    ELSE parts.note
                END                                 AS note_parts   -- 9
                ,
                CASE
                    WHEN mark.note = '' THEN '&nbsp;'
                    ELSE mark.note
                END                                 AS note_mark    --10
                ,
                CASE
                    WHEN shape.note = '' THEN '&nbsp;'
                    ELSE shape.note
                END                                 AS note_shape   --11
                ,
                CASE
                    WHEN size.note = '' THEN '&nbsp;'
                    ELSE size.note
                END                                 AS note_size    --12
            FROM
                punchMark_parts_master  AS parts
            LEFT OUTER JOIN
                miitem ON (parts_no = mipn)
            LEFT OUTER JOIN
                punchMark_master        AS mark  USING (punchmark_code)
            LEFT OUTER JOIN
                punchMark_shape_master  AS shape USING (shape_code)
            LEFT OUTER JOIN
                punchMark_size_master   AS size  USING (size_code)
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
            // $session->add('s_sysmsg', '対象データがありません！');
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>対象データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $tmpMark = str_replace("\n", '<br>', $res[$i][4]);
                $tmpMark = str_replace("\r", '', $tmpMark);
                /*****
                if ($res[$i][10] != '') {   // コメントがあれば色を変える
                    $listTable .= "    <tr onDblClick='PunchMarkSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='PunchMarkSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'><td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // 行番号
                // $listTable .= "        <td class='winbox' width=' 8%' align='right' >\n";
                // $listTable .= "            <a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>明細</a>\n";
                // $listTable .= "        </td>\n"; // 明細クリック用
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";     // 部品番号
                $listTable .= "        <td class='winbox' width='16%' align='left'  >{$res[$i][1]}</td>\n";     // 部品名
                $listTable .= "        <td class='winbox' width=' 7%' align='center'>{$res[$i][2]}</td>\n";     // 刻印コード
                $listTable .= "        <td class='winbox' width=' 7%' align='center'>{$res[$i][3]}</td>\n";     // 棚番
                $listTable .= "        <td class='winbox' width='23%' align='center'>{$tmpMark}   </td>\n";     // 刻印内容
                $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][5]}</td>\n";     // 形状名
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][6]}</td>\n";     // 客先
                $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][7]}</td>\n";     // サイズ名
                if ($res[$i][8] == '製作中') {
                    $listTable .= "        <td class='winbox' width='10%' align='center' style='color:red;'>{$res[$i][8]}</td>\n";// 製作状況
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][8]}</td>\n";     // 製作状況
                }
                $listTable .= "    </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'><td class='winbox' width=' 5%' align='left' colspan='1'>&nbsp;</td>\n";          // ２行目のデータ
                $listTable .= "        <td class='winbox' width='26%' align='left' colspan='2'>{$res[$i][9]}</td>\n";   // 部品マスター備考
                $listTable .= "        <td class='winbox' width='37%' align='left' colspan='3'>{$res[$i][10]}</td>\n";  // 刻印マスター備考
                $listTable .= "        <td class='winbox' width='16%' align='left' colspan='2'>{$res[$i][11]}</td>\n";  // 形状マスター備考
                $listTable .= "        <td class='winbox' width='16%' align='left' colspan='2'>{$res[$i][12]}</td>\n";  // サイズマスター備考
                $listTable .= "    </font></tr>\n";
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
        $listTable .= "        <th class='winbox' colspan='11'>検索結果</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>部品番号</th>\n";
        $listTable .= "        <th class='winbox' width='18%'>部品名</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>刻印コード</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>棚　番</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>刻印内容</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>形　状</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>客　先</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>サイズ</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>製作状況</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%' colspan='1'>&nbsp;</th>\n";
        $listTable .= "        <th class='winbox' width='29%' colspan='2'>部品マスター備考</th>\n";
        $listTable .= "        <th class='winbox' width='28%' colspan='3'>刻印マスター備考</th>\n";
        $listTable .= "        <th class='winbox' width='19%' colspan='2'>形状マスター備考</th>\n";
        $listTable .= "        <th class='winbox' width='19%' colspan='2'>サイズマスター備考</th>\n";
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
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>刻印管理システム検索結果</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../punchMark_search.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../punchMark_search.js'></script>
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
    
    
} // Class PunchMarkSearch_Model End

?>
