<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫・有効利用数(予定在庫数)マイナスリスト照会         MVC Model 部 //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/02 Created   parts_stock_avail_minus_Model.php                   //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../ComTableMntClass.php');     // TNK 全共通 テーブルメンテ&ページ制御Class
// require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*                             base class 基底クラスの定義                                *
*****************************************************************************************/
class PartsStockAvailMinus_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $totalMsg;                          // フッターに入れる合計件数
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        ///// 現在の所は特に初期化処理はない。
        $this->where = '';
        $this->order = '';
        $this->totalMsg = '';
    }
    
    ////////// SQL文の WHERE区 設定
    public function setWhere($request)
    {
        $this->where = $this->SetInitWhere($request);
    }
    
    ////////// SQL文の ORDER BY区 設定
    public function setOrder($request)
    {
        $this->order = $this->SetInitOrder($request);
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    資材在庫品目全て対象で保有月等の指定条件での 一覧表
    public function outViewListHTML($request, $menu, $session)
    {
        /************************* ヘッダー ***************************/
        // 固定のHTMLヘッダーソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLボディソースを取得
        $listHTML .= $this->getViewHTMLheader($request, $menu);
        // 固定のHTMLフッターソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_stock_avail_minus_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        /************************* ボディ ***************************/
        // 固定のHTMLヘッダーソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLボディソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu, $session);
        // 固定のHTMLフッターソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_stock_avail_minus_ViewListBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        /************************* フッター ***************************/
        // 固定のHTMLヘッダーソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLボディソースを取得
        $listHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLフッターソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/parts_stock_avail_minus_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// 部品のコメントを取得
    public function getComment($request, $result)
    {
        // 結果は真偽値で返る
        return $this->get_comment($request, $result);
    }
    
    ///// 部品のコメントを保存
    public function setComment($request, $result, $session)
    {
        // 結果はシステムメッセーへ出力
        $this->commentSave($request, $result, $session);
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function SetInitWhere($request)
    {
        ///// 基本WHERE区の設定
        if ($request->get('searchPartsNo') != '') {
            $where = "WHERE parts_no LIKE '%{$request->get('searchPartsNo')}%'";
        } else {
            $where = 'WHERE TRUE';
        }
        switch ($request->get('targetDivision')) {
        case 'AL':
            $where .= '';
            break;
        case 'CA':
            $where .= " AND division LIKE 'C%'";
            break;
        case 'CH':
            $where .= " AND division LIKE 'CH%'";
            break;
        case 'CS':
            $where .= " AND division LIKE 'CS%'";
            break;
        case 'LA':
            $where .= " AND division LIKE 'L%'";
            break;
        case 'LL':
            $where .= " AND division LIKE 'LL%'";
            break;
        case 'LB':
            $where .= " AND division LIKE 'LB%'";
            break;
        default:
            $where .= '';
        }
        switch ($request->get('targetMinusItem')) {
        case '1':   // 全て
            $where .= '';
            break;
        case '2':   // 現在在庫がマイナス
            $where .= " AND stock < 0";
            break;
        case '3':   // 途中の在庫がマイナス
            $where .= " AND mid_avail_pcs < 0";
            break;
        case '4':   // 最終在庫がマイナス
            $where .= " AND avail_pcs < 0";
            break;
        default:
            $where .= '';
        }
        return $where;
    }
    
    ////////// リクエストによりSQL文の基本ORDER区を設定
    protected function SetInitOrder($request)
    {
        ///// targetSortItemで切替
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $order = 'ORDER BY 部品番号 ASC';
            break;
        case 'name':
            $order = 'ORDER BY 部品名 ASC';
            break;
        case 'material':
            $order = 'ORDER BY 材質 DESC';
            break;
        case 'parent':
            $order = 'ORDER BY 親機種 DESC';
            break;
        case 'stock':
            $order = 'ORDER BY stock ASC';
            break;
        case 'avail_pcs':
            $order = 'ORDER BY avail_pcs ASC';
            break;
        case 'mid_plan_no':
            $order = 'ORDER BY mid_plan_no ASC';
            break;
        case 'mid_avail_date':
            $order = 'ORDER BY mid_avail_date ASC';
            break;
        case 'mid_avail_pcs':
            $order = 'ORDER BY mid_avail_pcs ASC';
            break;
        case 'TNKCC':
            $order = 'ORDER BY TNKCC DESC';
            break;
        default:
            $order = 'ORDER BY 部品番号 ASC';
        }
        return $order;
    }
    
    ///// 部品のコメントを取得
    protected function get_comment($request, $result)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            parts_stock_avail_minus_comment ON(mipn=parts_no)
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
    
    ///// 部品のコメントを保存
    protected function commentSave($request, $result, $session)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        // if ($request->get('comment') == '') return;  // これを行うと削除できない
        if ($request->get('targetPartsNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 未設定又は設定解除をチェック
        if ($request->get('targetFactor') == '') {
            $reg_factor = 'NULL';
        } else {
            $reg_factor = $request->get('targetFactor');
        }
        // コメント中のブラウザーが付けたCRを取り払う
        $comment = str_replace("\r", '', $request->get('comment'));
        $query = "SELECT comment, factor FROM parts_stock_avail_minus_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
        if ($this->getResult($query, $res) < 1) {
            if ($comment == '' && $request->get('targetFactor') == '') {
                // データ無し
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // 登録後 親のリロードはしないでWindow終了
                return;
            }
            $sql = "
                INSERT INTO parts_stock_avail_minus_comment (parts_no, comment, factor, last_date, last_user)
                VALUES ('{$request->get('targetPartsNo')}', '{$comment}', {$reg_factor}, '{$last_date}', '{$last_user}')
            ";
            if ($this->execute_Insert($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントの登録が出来ませんでした！　管理担当者へ連絡して下さい。";
            } else {
                $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントを登録しました。";
            }
        } else {
            $saveSQL = "SELECT * FROM parts_stock_avail_minus_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
            if ($comment == '' && $request->get('targetFactor') == '') {
                // コメントの内容が削除されて更新の場合は、実レコードも削除
                $sql = "DELETE FROM parts_stock_avail_minus_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
                if ($this->execute_Delete($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントの削除が出来ませんでした！　管理担当者へ連絡して下さい。";
                } else {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントを削除しました。";
                }
            } elseif ($res[0]['comment'] == $comment && $res[0]['factor'] == $request->get('targetFactor')) {
                // 変更無し
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // 登録後 親のリロードはしないでWindow終了
                return;
            } else {
                $sql = "
                    UPDATE parts_stock_avail_minus_comment SET comment='{$comment}', factor={$reg_factor},
                    last_date='{$last_date}', last_user='{$last_user}'
                    WHERE parts_no='{$request->get('targetPartsNo')}'
                ";
                if ($this->execute_Update($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントの変更が出来ませんでした！　管理担当者へ連絡して下さい。";
                } else {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントを変更しました。";
                }
            }
        }
        $session->add('regParts', $request->get('targetPartsNo'));  // マーカー及びジャンプ用に登録
        $result->add('AutoClose', 'window.close();'); // 登録後 Window終了
        return;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   一覧表の ヘッダー部 生成
    private function getViewHTMLheader($request, $menu)
    {
        // ソート項目の配列取得
        $item = $this->getSortItemArray($request);
        // HTMLの項目生成
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='11%'{$item['parts']} title='部品番号で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parts' target='_parent' onMouseover=\"status='部品番号で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">部品番号</a></th>\n";
        $listTable .= "        <th class='winbox' width='12%'{$item['name']} title='部品名で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=name' target='_parent' onMouseover=\"status='部品名で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">部　品　名</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['material']} title='材質で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=material' target='_parent' onMouseover=\"status='材質で、降順にソートします。'; return true;\" onMouseout=\"status='';\">材　質</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['parent']} title='親機種で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parent' target='_parent' onMouseover=\"status='親機種で、降順にソートします。'; return true;\" onMouseout=\"status='';\">親機種</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['stock']} title='現在の在庫数で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=stock' target='_parent' onMouseover=\"status='現在の在庫数で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">現在在庫</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['avail_pcs']} title='最終有効数(予定在庫数)で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=avail_pcs' target='_parent' onMouseover=\"status='最終有効数(予定在庫数)で、降順にソートします。'; return true;\" onMouseout=\"status='';\">最終在庫</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['mid_plan_no']} title='途中マイナスの計画番号で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=mid_plan_no' target='_parent' onMouseover=\"status='途中マイナスの計画番号で、降順にソートします。'; return true;\" onMouseout=\"status='';\">途中計画</a></th>\n";
        $listTable .= "        <th class='winbox' width='11%'{$item['mid_avail_date']} title='途中マイナスの日付で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=mid_avail_date' target='_parent' onMouseover=\"status='途中マイナスの日付で、降順にソートします。'; return true;\" onMouseout=\"status='';\">途中日付</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['mid_avail_pcs']} title='途中マイナスの在庫予定数で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=mid_avail_pcs' target='_parent' onMouseover=\"status='途中マイナスの在庫予定数で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">途中在</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 4%'{$item['TNKCC']} title='TNKCCで、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=TNKCC' target='_parent' onMouseover=\"status='TNKCCで、降順にソートします。'; return true;\" onMouseout=\"status='';\" class='factorFont'>CC</a></th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   SQL文生成
    private function getQueryStatement()
    {
        $query = "
            SELECT   parts_no           AS 部品番号         -- 00
                    , CASE
                        WHEN midsc IS NULL THEN '未登録'
                        ELSE trim(substr(midsc, 1, 9))
                      END               AS 部品名           -- 01
                    , CASE
                        WHEN mzist = '' THEN '&nbsp;'
                        WHEN mzist IS NULL THEN '&nbsp;'
                        ELSE trim(substr(mzist, 1, 6))
                      END               AS 材質             -- 02
                    , CASE
                        WHEN mepnt = '' THEN '&nbsp;'
                        WHEN mepnt IS NULL THEN '&nbsp;'
                        ELSE trim(substr(mepnt, 1, 6))
                      END               AS 親機種           -- 03
                    , to_char(stock, 'FM9,999,999')
                                        AS 現在在庫数       -- 04
                    , to_char(avail_pcs, 'FM9,999,999')
                                        AS 最終有効数       -- 05
                    , CASE
                        WHEN mid_plan_no IS NULL THEN '&nbsp;'
                        WHEN mid_plan_no = '' THEN '&nbsp;'
                        ELSE mid_plan_no
                      END               AS 途中の計画番号   -- 06
                    , CASE
                        WHEN mid_avail_date IS NULL THEN '&nbsp;'
                        ELSE to_char(mid_avail_date, 'FM9999/99/99')
                      END               AS 途中の日付       -- 07
                    , to_char(mid_avail_pcs, 'FM9,999,999')
                                        AS 途中の在庫数     -- 08
                    , CASE
                        WHEN miccc IS NULL THEN '&nbsp;'
                        WHEN miccc = '' THEN '&nbsp;'
                        WHEN miccc = 'E' THEN 'TCC'
                        WHEN miccc = 'D' THEN 'CC'
                        ELSE miccc
                      END               AS TNKCC            -- 09
                    ----------------------- 以下はリスト外 ---------------------
                    , stock                                 -- 10
                    , avail_pcs                             -- 11
                    , mid_avail_pcs                         -- 12
                    FROM
                        parts_stock_avail_minus_table
                    LEFT OUTER JOIN
                        miitem ON (parts_no = mipn)
                    {$this->where}
                    {$this->order}
        ";
        return $query;
    }
    
    ///// List部   一覧生成 ボディ部
    private function getViewHTMLbody($request, $menu, $session)
    {
        // ソート項目の配列取得
        $item = $this->getSortItemArray($request);
        $query = $this->getQueryStatement();
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>該当部品がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $this->totalMsg = $this->getSumPrice($rows, $res);
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' title='番号をクリックすれば部品番号をコピーします。'>\n";
                $listTable .= "            <a href='javascript:void(0);' onClick='PartsStockAvailMinus.clipCopyValue(\"{$res[$i][0]}\");'>" . ($i+1) . "</a></td>\n";// 行番号
                $listTable .= "        <td class='winbox' width='11%' align='center'{$item['parts']} title='部品番号をクリックすれば在庫予定状況を照会できます。'\n";
                $listTable .= "            onClick='PartsStockAvailMinus.win_open(\"" . $menu->out_action('在庫予定') . "?targetPartsNo=" . urlencode($res[$i][0]) . "&noMenu=yes\", 900, 680)'\n";
                $listTable .= "        ><a href='javascript:void(0);'>{$res[$i][0]}</a></td>\n";                                                        // 部品番号
                $listTable .= "        <td class='winbox' width='12%' align='left'{$item['name']}>" . mb_substr(mb_convert_kana($res[$i][1], 'ksa'), 0, 9) . "</td>\n";//部品名
                $listTable .= "        <td class='winbox' width=' 9%' align='left'{$item['material']}>" . mb_substr(mb_convert_kana($res[$i][2], 'ksa'), 0, 6) . "</td>\n";// 材質
                $listTable .= "        <td class='winbox' width=' 9%' align='left'{$item['parent']}>" . mb_substr(mb_convert_kana($res[$i][3], 'ksa'), 0, 6) . "</td>\n";// 親機種
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['stock']}>{$res[$i][4]}</td>\n";                              // 現在在庫数
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['avail_pcs']}>{$res[$i][5]}</td>\n";                          // 最終予定在庫数
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['mid_plan_no']}>{$res[$i][6]}</td>\n";                        // 途中の計画番号
                $listTable .= "        <td class='winbox' width='11%' align='right'{$item['mid_avail_date']}>{$res[$i][7]}</td>\n";                     // 途中の日付
                $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['mid_avail_pcs']}>{$res[$i][8]}</td>\n";                      // 途中のマイナス在庫数
                $listTable .= "        <td class='winbox factorFont' width=' 4%' align='left'{$item['TNKCC']}>{$res[$i][9]}</td>\n";                   // TNKCC
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   一覧生成 フッター部
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
    
    ///// ソートアイテムの配列を返す
    private function getSortItemArray($request)
    {
        // 初期化
        $itemArray = array('parts' => '', 'name' => '', 'material' => '', 'parent' => '', 'stock' => '',
            'avail_pcs' => '', 'mid_plan_no' => '', 'mid_avail_date' => '', 'mid_avail_pcs' => '', 'TNKCC' => '');
        // リクエストによりソート項目に色付け
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $itemArray['parts'] = " style='background-color:#ffffc6;'";
            break;
        case 'name':
            $itemArray['name'] = " style='background-color:#ffffc6;'";
            break;
        case 'material':
            $itemArray['material'] = " style='background-color:#ffffc6;'";
            break;
        case 'parent':
            $itemArray['parent'] = " style='background-color:#ffffc6;'";
            break;
        case 'stock':
            $itemArray['stock'] = " style='background-color:#ffffc6;'";
            break;
        case 'avail_pcs':
            $itemArray['avail_pcs'] = " style='background-color:#ffffc6;'";
            break;
        case 'mid_plan_no':
            $itemArray['mid_plan_no'] = " style='background-color:#ffffc6;'";
            break;
        case 'mid_avail_date':
            $itemArray['mid_avail_date'] = " style='background-color:#ffffc6;'";
            break;
        case 'mid_avail_pcs':
            $itemArray['mid_avail_pcs'] = " style='background-color:#ffffc6;'";
            break;
        case 'TNKCC':
            $itemArray['TNKCC'] = " style='background-color:#ffffc6;'";
            break;
        }
        return $itemArray;
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
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>長期滞留部品List部</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../parts_stock_avail_minus.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript' src='../parts_stock_avail_minus.js'></script>
</head>
<body
    onLoad='if (parent.document.ConditionForm.searchPartsNo) parent.document.ConditionForm.searchPartsNo.focus();'
>
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
        $stock = 0;     // 初期化
        $avail = 0;
        $mid   = 0;
        for ($i=0; $i<$rows; $i++) {
            if ($array[$i][10] < 0) $stock++;
            if ($array[$i][11] < 0) $avail++;
            if ($array[$i][12] < 0) $mid++;
        }
        $stock = number_format($stock);
        $avail = number_format($avail);
        $mid   = number_format($mid);
        return "合計件数：{$rows}件 &nbsp;&nbsp; 現在在庫マイナス：{$stock}件 &nbsp;&nbsp; 最終予定在庫マイナス：{$avail}件 &nbsp;&nbsp; 途中在庫マイナス：{$mid}件";
    }
    

} // Class PartsStockAvailMinus_Model End

?>
