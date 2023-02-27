<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム管理メニュー プログラムの検索                   MVC Model 部   //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_search_Model.php                         //
// 2010/01/27 プログラム内容とコメントの検索条件がANDだったのをORに変更     //
// 2010/06/16 検索時のソート順をディレクトリ名−プログラム名の順に変更      //
// 2010/06/21 ディレクトリのoptionの並び順を変更（lowerは使えない）    大谷 //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class ProgMasterSearch_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $offset;                            // 共用 SQLのOFFSET句
    private $limit;                             // 共用 SQLのLIMIT句
    private $sql;                               // 共用 SQL文
    private $dir;                         // dirの<select><option>データ
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
        $this->order  = 'ORDER BY dir ASC, LOWER(p_id) ASC';
        $this->offset = 'OFFSET 0';
        $this->limit  = 'LIMIT 500';
        $this->sql    = '';
        $this->dir = '';
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
    public function getDirOptions($session)
    {
        if ($this->dir == '') {
            $this->dir = $this->getDirOptionsBody($session);
        }
        return $this->dir;
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
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文のWHERE区を設定
    protected function setWhereBody($session)
    {
        $where  = '';
        $db_flg = 0;
        if ($session->get_local('pid') != '') {
            $where .= "WHERE p_id LIKE '%{$session->get_local('pid')}%'";
        }
        if ($session->get_local('dir') != '' && $where != '') {
            $where .= " AND dir = '{$session->get_local('dir')}'";
        } elseif ($session->get_local('dir') != '') {
            $where .= "WHERE dir = '{$session->get_local('dir')}'";
        }
        if ($session->get_local('name_comm') != '' && $where != '') {
            $where .= " AND (p_name LIKE '%{$session->get_local('name_comm')}%' OR comment LIKE '%{$session->get_local('name_comm')}%')";
        } elseif ($session->get_local('name_comm') != '') {
            $where .= "WHERE (p_name LIKE '%{$session->get_local('name_comm')}%' OR comment LIKE '%{$session->get_local('name_comm')}%')";
        }
        //if ($session->get_local('name_comm') != '' && $where != '') {
        //    $where .= " AND comment LIKE '%{$session->get_local('name_comm')}%'";
        //} elseif ($session->get_local('name_comm') != '') {
        //    $where .= "WHERE comment LIKE '%{$session->get_local('name_comm')}%'";
        //}
        if ($session->get_local('db') != '' && $where != '') {
            $where .= " AND (db1 LIKE '%{$session->get_local('db')}%'";
            $db_flg = 1;
        } elseif ($session->get_local('db') != '') {
            $where .= "WHERE (db1 LIKE '%{$session->get_local('db')}%'";
            $db_flg = 1;
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db2 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db2 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db3 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db3 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db4 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db4 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db5 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db5 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db6 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db6 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db7 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db7 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db8 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db8 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db9 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db9 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db10 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db10 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db11 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db11 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($session->get_local('db') != '' && $where != '') {
            if ($db_flg == 1) {
                $where .= " OR db12 LIKE '%{$session->get_local('db')}%'";
            } else {
                $where .= " AND (db12 LIKE '%{$session->get_local('db')}%'";
                $db_flg = 1;
            }
        }
        if ($db_flg == 1) {
            $where .= ")";
        }
        return $where;
    }
    
    ///// 形状マスターのHTML <select> option の出力
    protected function getDirOptionsBody($session)
    {
        $query = "SELECT DISTINCT ON (dir) dir, p_id FROM program_master ORDER BY dir ASC";
        $res = array();
        if (($rows=getResult2($query, $res)) <= 0) return '';
        $options = "\n";
        $options .= "<option value='' style='color:red;'>未選択</option>\n";
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('dir') == $res[$i][0]) {
                $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][0]}</option>\n";
            } else {
                $options .= "<option value='{$res[$i][0]}'>{$res[$i][0]}</option>\n";
            }
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
        $file_name = "list/progMaster_search_ViewListHeader-{$session->get('User_ID')}.html";
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
        $file_name = "list/progMaster_search_ViewList-{$session->get('User_ID')}.html";
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
        $file_name = "list/progMaster_search_ViewListFooter-{$session->get('User_ID')}.html";
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
                p_id                                AS プログラムID     -- 0
                ,
                p_name                              AS プログラム名     -- 1
                ,
                dir                                 AS ディレクトリ     -- 2
                ,
                comment                             AS コメント         -- 3
                ,
                db1                                 AS 使用DB1          -- 4
                ,
                db2                                 AS 使用DB2          -- 5
                ,
                db3                                 AS 使用DB3          -- 6
                ,
                db4                                 AS 使用DB4          -- 7
                ,
                db5                                 AS 使用DB5          -- 8
                ,
                db6                                 AS 使用DB6          -- 9
                ,
                db7                                 AS 使用DB7          -- 10
                ,
                db8                                 AS 使用DB8          -- 11
                ,
                db9                                 AS 使用DB9          -- 12
                ,
                db10                                AS 使用DB10         -- 13
                ,
                db11                                AS 使用DB11         -- 14
                ,
                db12                                AS 使用DB12         -- 15
                ,
                last_date                           AS 登録日時         -- 16
            FROM
                program_master
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        $session->add('query', $query);
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
        //$array_lowercase = array_map('strtolower', $res);
        //array_multisort($array_lowercase, SORT_ASC, SORT_STRING, $res);
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
                    $listTable .= "    <tr onDblClick='ProgMasterSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='ProgMasterSearch.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                // 全件表示
                //$listTable .= "        <font size='2'><td class='winboxb' width=' 5%' align='right' rowspan='5'>" . ($i+1) . "</td>\n";    // 行番号
                // DB使用のみ版
                $listTable .= "        <font size='2'><td class='winboxb' width=' 5%' align='right' rowspan='3'>" . ($i+1) . "</td>\n";    // 行番号
                if ($session->get_local('pid') != '') {
                    $p_id = $res[$i][0];
                    $div_id = $session->get_local('pid');
                    $p_id = ereg_replace($div_id, "<B>{$div_id}</B>", $p_id);
                    $listTable .= "    <td class='winbox' width='38%' align='left'>{$p_id}</td>\n";     // プログラムID
                } else {
                    $listTable .= "    <td class='winbox' width='38%' align='left'>{$res[$i][0]}</td>\n";     // プログラムID
                }
                if ($session->get_local('name_comm') != '') {
                    $p_name = $res[$i][1];
                    $div_name = $session->get_local('name_comm');
                    $p_name = ereg_replace($div_name, "<B>{$div_name}</B>", $p_name);
                    $listTable .= "    <td class='winbox' width='24%' align='left'>{$p_name}</td>\n";     // プログラム名
                } else {
                    $listTable .= "    <td class='winbox' width='24%' align='left'>{$res[$i][1]}</td>\n";     // ディレクトリ
                }
                if ($session->get_local('dir') != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'><B>{$res[$i][2]}</B></td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][2]}</td>\n";     // ディレクトリ
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // ２行目のデータ
                if ($session->get_local('name_comm') != '') {
                    $p_comm = $res[$i][3];
                    $div_comm = $session->get_local('name_comm');
                    $p_comm = ereg_replace($div_comm, "<B>{$div_comm}</B>", $p_comm);
                    $listTable .= "    <td class='winboxb' width='65%' align='left' rowspan='2' colspan='2'>{$p_comm}</td>\n";     // コメント
                } else {
                    $listTable .= "    <td class='winboxb' width='65%' align='left' rowspan='2' colspan='2'>{$res[$i][3]}</td>\n";     // コメント
                }
                // 前件表示版
                /*****
                if ($res[$i][4] != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][4]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>　</td>\n";     // ディレクトリ
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // ２行目のデータ
                if ($res[$i][5] != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][5]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>　</td>\n";     // ディレクトリ
                }
                if ($res[$i][6] != '') {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>{$res[$i][6]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>　</td>\n";     // ディレクトリ
                }
                if ($res[$i][7] != '') {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>{$res[$i][7]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>　</td>\n";     // ディレクトリ
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // ２行目のデータ
                if ($res[$i][8] != '') {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>{$res[$i][8]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='33%' align='left'>　</td>\n";     // ディレクトリ
                }
                if ($res[$i][9] != '') {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>{$res[$i][9]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='32%' align='left'>　</td>\n";     // ディレクトリ
                }
                if ($res[$i][10] != '') {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>{$res[$i][10]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winbox' width='30%' align='left'>　</td>\n";     // ディレクトリ
                }
                $listTable .= "        </font></tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <font size='2'>\n";          // ２行目のデータ
                if ($res[$i][11] != '') {
                    $listTable .= "    <td class='winboxb' width='33%' align='left'>{$res[$i][11]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winboxb' width='33%' align='left'>　</td>\n";     // ディレクトリ
                }
                if ($res[$i][12] != '') {
                    $listTable .= "    <td class='winboxb' width='32%' align='left'>{$res[$i][12]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winboxb' width='32%' align='left'>　</td>\n";     // ディレクトリ
                }
                if ($res[$i][13] != '') {
                    $listTable .= "    <td class='winboxb' width='30%' align='left'>{$res[$i][13]}</td>\n";     // ディレクトリ
                } else {
                    $listTable .= "    <td class='winboxb' width='30%' align='left'>　</td>\n";     // ディレクトリ
                }
                *****/
                // DB使用のみ版
                $db_use = 0;
                for ($r=4; $r<16; $r++) {
                    if ($res[$i][$r] != '') {
                        $db_use = 1;
                    }
                }
                if ($db_use == 1) {
                    $db_url = '../progMaster_search_db_detail.php?db1='. $res[$i][4] .'&db2='. $res[$i][5] .'&db3='. $res[$i][6] .'&db4='. $res[$i][7] .'&db5='. $res[$i][8] .'&db6='. $res[$i][9] .'&db7='. $res[$i][10] .'&db8='. $res[$i][11] .'&db9='. $res[$i][12] .'&db10='. $res[$i][13] .'&db11='. $res[$i][14] .'&db12='. $res[$i][15] .'&key='. $session->get_local('db');
                    $listTable .= "    <td class='winbox' width='30%' align='center'><a href='". $db_url ."' onclick='ProgMasterSearch.win_open(\"". $db_url ."\", 1000, 440); return false;' title='クリックで使用ＤＢの詳細を表示します。'>一覧</a></td>\n";     // DB1
                } else {
                    $listTable .= "    <td class='winbox' width='30%' align='center'>-----</td>\n";     // DB1
                }
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winboxb' width='33%' align='left'>　{$res[$i][16]}　</td>\n";     // 更新日時
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
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>刻印管理システム検索結果</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../progMaster_search.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../progMaster_search.js'></script>
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
    
    
} // Class ProgMasterSearch_Model End

?>
