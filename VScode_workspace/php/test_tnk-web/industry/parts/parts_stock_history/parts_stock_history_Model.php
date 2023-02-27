<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 予定 照会 (引当･発注状況照会)                   MVC Model 部   //
// Copyright (C) 2004-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created   parts_stock_history_Model.php                       //
// 2007/03/09 オリジナルはparts_stock_view.phpでparts_stock_plan_Model.php  //
//            に合わせて完全なＭＶＣモデルでコーディングした。              //
//            変更経歴は backup/parts_stock_view.php を参照すること。       //
// 2007/03/24 last_stock_day 経歴表示上の最終計上日メンバー変数を追加       //
// 2007/05/14 計上日のリンク設定に計上日kei_ymを渡すように追加 大谷         //
// 2007/05/17 getViewHTMLfooter()メソッドに月平均出庫数と保有月を追加       //
// 2007/06/09 フッターのmessageが2段になる場合があるため計算時在庫を0.8emへ //
// 2007/06/22 getViewHTMLconst()にMenuHeaderクラスのout_retF2Script()を追加 //
//                ↑ の呼出し時に noMenu のパラメータチェックも行う         //
// 2007/08/02 Window版で予定と経歴の切替表示時に表示範囲がズレるため対応で  //
//            $session->get('stock_date_low')を取得するように変更           //
// 2007/12/18 getQueryStatement()のSQL文を一部訂正(入庫と出庫が同時の場合)  //
// 2011/07/27 在庫経歴の表示範囲を５００行に変更                       大谷 //
// 2016/08/08 mouseOverを追加                                          大谷 //
// 2019/05/10 買掛PGM変更に伴う変更                                    大谷 //
// 2019/06/25 表示件数を1000件に、開始日を7年前に変更。                     //
//            総材料費でないことがある為                               大谷 //
// 2019/07/24 開始日を10年前に変更。総材料費でないことがある為。       大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class PartsStockHistory_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $last_stock_day;                    // 経歴表示上の最終計上日
    private $last_stock_pcs;                    // 現在在庫
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $result, $session)
    {
        $this->last_stock_day = '';             // 初期化
        $this->last_stock_pcs = '0';            // 初期化
        
        ///// 基本WHERE区の設定
        switch ($request->get('showMenu')) {
        case 'List':
        case 'ListWin':
            $this->where = $this->SetInitWhere($request, $result, $session);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    グラフデータの明細 一覧表
    public function outViewListHTML($request, $menu, $result)
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
        $file_name = "list/parts_stock_history_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu, $result);
        // 固定のHTMLソースを取得
        if ($request->get('noMenu') == '') {
            $listHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $listHTML .= $this->getViewHTMLconst('footer');
        }
        // HTMLファイル出力
        $file_name = "list/parts_stock_history_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/parts_stock_history_ViewListFooter-{$_SESSION['User_ID']}.html";
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
    protected function SetInitWhere($request, $result, $session)
    {
        // 経歴範囲の開始
        if ($request->get('date_low') != '') {
            $date_low = $request->get('date_low');
        } elseif ($session->get('stock_date_low') != '') {
            $date_low = $session->get('stock_date_low');    // 2007/08/02 Window版で予定と経歴の切替表示対応
        } else {
            //$date_low = (date('Ymd') - 50000);      // ５年前から
            $date_low = (date('Ymd') - 70000);      // ７年前から
            $date_low = (date('Ymd') - 100000);      // １０年前から
        }
        // 経歴範囲の終了
        if ($request->get('date_upp') != '') {
            $date_upp = $request->get('date_upp');
        } else {
            $date_upp = date('Ymd');                // 当日まで
        }
        // 表示行数の制限値
        if ($request->get('view_rec') != '') {
            $view_rec = $request->get('view_rec');
        } else {
            $sql = "SELECT tnk_stock FROM parts_stock_master WHERE parts_no='{$request->get('targetPartsNo')}'";
            $this->getUniResult($sql, $tnk_stock);
            if ($tnk_stock >= 100000) {
                //$view_rec = '500';      // 指定されていない場合(総材料費の未登録からの呼出等) 500→400
                $view_rec = '1000';      // 指定されていない場合(総材料費の未登録からの呼出等) 500→400
            } else {
                // 2006/10/13 200→300 へ変更 (理由:計画番号CA234885 部品番号CQ17202-0ﾁｭｳｲｼｮにおいて200では目的の入庫が表示しきれないため)
                // 2019/06/28 500→1000 へ変更 (目的の入庫が表示しきれないため)
                //$view_rec = '500';      // 指定されていない場合(総材料費の未登録からの呼出等) 500→200
                $view_rec = '1000';      // 指定されていない場合(総材料費の未登録からの呼出等) 500→200
            }
            /***** 2006/10/12 過去の総材料費をチェックする場合の経歴オフセットロジック追加 *****/
            if ($result->get('plan_no') != '　' && $result->get('plan_no') != '') {
                $sql = "
                    SELECT upd_date FROM parts_stock_history
                    WHERE parts_no='{$request->get('targetPartsNo')}' AND plan_no='{$result->get('plan_no')}'
                    ORDER BY upd_date ASC LIMIT 1
                ";
                ;
                if ($this->getUniResult($sql, $upd_date) > 0) {
                    ///// 対象の２年前から  // ５年前から // １０年前から
                    $date_low = ((substr($upd_date, 0, 4) - 10) . substr($upd_date, 4, 4));
                    ///// 対象の１ヶ月後まで
                    if (substr($upd_date, 4, 2) >= 12) {
                        $date_upp = ((substr($upd_date, 0, 4) + 1) . '01' . substr($upd_date, 6, 2));
                    } else {
                        $month = sprintf('%02d', substr($upd_date, 4, 2) + 1);
                        $date_upp = (substr($upd_date, 0, 4) . $month . substr($upd_date, 6, 2));
                    }
                }
            }
        }
        $where = "
            WHERE parts_no = '{$request->get('targetPartsNo')}' AND upd_date >= {$date_low} AND upd_date <= {$date_upp}
            ORDER BY parts_no DESC, upd_date DESC, serial_no DESC
            LIMIT {$view_rec}
        ";
        return $where;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
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
        $listTable .= "        <th class='winbox' width='10%'>計上日</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>摘　要</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>出庫数</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>入庫数</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>合計在庫</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>区分</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>伝票番号</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>栃木在庫</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>ＮＫ在庫</th>\n";
        $listTable .= "        <th class='winbox' width='16%'>備　考</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   一覧表のボディ部（明細）を作成
    private function getViewHTMLbody($request, $menu, $result)
    {
        $query = $this->getQueryStatement($request);
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) > 0 ) {
            $last_anchor_flg = TRUE;    // 最後の行にアンカーが必要の初期化
            for ($i=($rows-1); $i>=0; $i--) {
                // $res[$i][9] = mb_convert_kana($res[$i][9], 'k');    // 前もって半角カナに変換
                // $res[$i][9] = mb_substr($res[$i][9], 0, 12);        // 半角カナの場合オーバーするので変換後15文字にする
                ///// 行マーカーの設定
                if ($result->get('plan_no') == $res[$i][1]) {
                    $listTable .= "    <tr style='background-color:#ffffc6;'>\n";
                } else {
                    $listTable .= "    <tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                }
                ///// アンカーの設定(<td>タグにしているのはNN7.1の対応)
                if ($result->get('plan_no') == $res[$i][1]) {  // 計画番号が一致していればアンカーを立てる
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'  nowrap><a name='last' style='color:black;'>" . ($i + 1) . "</a></td>    <!-- 行ナンバーの表示 -->\n";
                    $last_anchor_flg = FALSE;   // 最後の行のアンカーが必要でない
                } else if ( ($i == 0) && ($last_anchor_flg) ) {    // 最後の行にアンカーを立てる
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'  nowrap><a name='last' style='color:black;'>" . ($i + 1) . "</a></td>    <!-- 行ナンバーの表示 -->\n";
                } else {
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'  nowrap>" . ($i + 1) . "</td>    <!-- 行ナンバーの表示 -->\n";
                }
                ///// 計上日のリンク(買掛)設定
                if ($res[$i][5] == '1' && (!$request->get('noMenu')) ) {  // 計上日
                    $listTable .= "        <td class='winbox' width='10%' align='center' nowrap><a href='{$menu->out_action('買掛実績照会')}?parts_no=" . urlencode($request->get('targetPartsNo')) . "&uke_no={$res[$i][6]}{$result->get('material')}&kei_ym={$res[$i][0]}&div=" . ' ' . "&vendor=". '' ."' target='_parent' style='text-decoration:none;'>{$res[$i][0]}</a></td>\n";
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='center' nowrap>{$res[$i][0]}</td>\n";
                }
                $listTable .= "        <td class='winbox' width='10%' align='center' nowrap>{$res[$i][1]}</td>\n";  // 摘　要
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][2]) . "</td>\n";  // 出庫数
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][3]) . "</td>\n";  // 入庫数
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][4]) . "</td>\n";  // 合計在庫
                $listTable .= "        <td class='winbox' width=' 5%' align='center' nowrap>{$res[$i][5]}</td>\n";  // 区分
                $listTable .= "        <td class='winbox' width=' 9%' align='center' nowrap>{$res[$i][6]}</td>\n";  // 伝票番号
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][7]) . "</td>\n";  // 栃木在庫
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][8]) . "</td>\n";  // ＮＫ在庫
                $listTable .= "        <td class='winbox' width='16%' align='left'   nowrap>{$res[$i][9]}</td>\n";  // 備考
                $listTable .= "    </tr>\n";
            }
            $this->last_stock_day = $res[0][0];     // 経歴表示上の最終日 保管
            $this->last_stock_pcs = $res[0][4];     // 合計在庫保管
        }
        if ($rows > 0) {
            $all_stock = $res[$i+1][4];     // 最後の合計在庫を保管
            $tnk_stock = $res[$i+1][7];     // 最後の栃木在庫を保管
            $nk_stock  = $res[$i+1][8];     // 最後のＮＫ在庫を保管
        } else {
            $all_stock = 0;                 // 初回の対応
            $tnk_stock = 0;
            $nk_stock  = 0;
        }
        $queryKen = $this->getQueryStatement2($request);
        $resKen   = array();
        $rowsKen  = $this->getResult2($queryKen, $resKen);
        for ($s=0; $s<$rowsKen; $s++) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' width=' 5%' align='right'  style='color:yellow;' nowrap>{$resKen[$s][0]}</td>\n";  // 行番号
            $listTable .= "        <td class='winbox' width='10%' align='center' style='color:yellow;' nowrap>{$resKen[$s][1]}</td>\n";  // 計上日
            $listTable .= "        <td class='winbox' width='10%' align='center' style='color:yellow;' nowrap>{$resKen[$s][2]}</td>\n";  // 摘　要
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:yellow;' nowrap>{$resKen[$s][3]}</td>\n";  // 出庫数
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:yellow;' nowrap>" . number_format($resKen[$s][4]) . "</td>\n";  // 入庫数
            $all_stock += $resKen[$s][4];
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:gray;' nowrap>" . number_format($all_stock) . "</td>\n";  // 合計在庫
            $listTable .= "        <td class='winbox' width=' 5%' align='center' style='color:gray;' nowrap>{$resKen[$s][6]}</td>\n";  // 区分
            $listTable .= "        <td class='winbox' width=' 9%' align='center' style='color:gray;' nowrap>{$resKen[$s][7]}</td>\n";  // 伝票番号
            $tnk_stock += $resKen[$s][4];
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:gray;' nowrap>" . number_format($tnk_stock) . "</td>\n";  // 栃木在庫
            $nk_stock += $resKen[$s][9];
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:gray;' nowrap>" . number_format($nk_stock) . "</td>\n";   // ＮＫ在庫
            $listTable .= "        <td class='winbox' width='16%' align='left'   style='color:gray;' nowrap>{$resKen[$s][10]}</td>\n"; // 備考
            $listTable .= "    </tr>\n";
        }
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
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
            $footer_title = "<span style='font-size:0.8em;'>計算時在庫：{$invent}　</span>月平均出庫：{$pickup}　<span style='color:teal;'>保有月：{$month}</span>";
        } else {
            $footer_title = '&nbsp;';
        }
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='43%' align='right'>'{$this->last_stock_day}現在の在庫</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>" . number_format($this->last_stock_pcs) . "</td>\n";
        $listTable .= "        <td class='winbox' width='48%' align='center'>{$footer_title}</td>\n";
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
            SELECT substr(to_char(ent_date, 'FM9999/99/99'), 3, 8)
                                                            as 計上日       -- 0
                , CASE
                    WHEN plan_no = '' THEN '&nbsp;'
                    ELSE plan_no
                  END                                       as 摘　要       -- 1
                , CASE
                    WHEN out_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN out_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as 出庫数       -- 2
                , CASE
                    WHEN in_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN in_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as 入庫数       -- 3
                , CASE
                    WHEN out_id = '1' AND in_id  = '2' THEN nk_stock  - stock_mv + stock_mv + tnk_stock -- 2007/12/18 ADD 冗長になるがロジックの意味を明確にするため
                    WHEN out_id = '1' THEN nk_stock  - stock_mv + tnk_stock
                    WHEN out_id = '2' AND in_id  = '1' THEN tnk_stock  - stock_mv + stock_mv + nk_stock -- 2007/12/18 ADD 冗長になるがロジックの意味を明確にするため
                    WHEN out_id = '2' THEN tnk_stock - stock_mv + nk_stock
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv + tnk_stock
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv + nk_stock
                    ELSE nk_stock + tnk_stock
                  END                                       as 合計在庫     -- 4
                , den_kubun                                 as 区分         -- 5
                , CASE
                    WHEN den_no = '' THEN '&nbsp;'
                    ELSE den_no
                  END                                       as 伝票番号     -- 6
                , CASE
                    WHEN out_id = '2' THEN tnk_stock - stock_mv
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv
                    ELSE tnk_stock
                  END                                       as 栃木在庫     -- 7
                , CASE
                    WHEN out_id = '1' THEN nk_stock  - stock_mv
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv
                    ELSE nk_stock
                  END                                       as ＮＫ在庫     -- 8
                , CASE
                    WHEN note = '' THEN '&nbsp;'
                    ELSE note
                  END                                       as 備　考       -- 9
            FROM
                parts_stock_history
        ";
        $query .= $this->where;
        return $query;
    }
    
    ///// List部   一覧表のSQLステートメント取得
    private function getQueryStatement2($request)
    {
        $query = "
            SELECT
                '&nbsp;'                                                AS 行番号               -- 0
                ,
                substr(to_char(data.uke_date, 'FM0000/00/00'), 3, 8)    AS 計上日               -- 1 (受付日)
                ,
                '検査中'                                                AS 摘要                 -- 2
                ,
                0                                                       AS 出庫数               -- 3
                ,
                -- to_char(data.uke_q, 'FM9,999,999')                      AS 入庫数               -- 4 (受付数)
                round(data.uke_q, 0)                                    AS 入庫数               -- 4 (受付数)
                ,
                0                                                       AS 合計在庫             -- 5
                ,
                '受'                                                    AS 区分                 -- 6
                ,
                data.uke_no                                             AS 伝票番号             -- 7 (受付番号)
                ,
                0                                                       AS 栃木在庫             -- 8
                ,
                0                                                       AS ＮＫ在庫             -- 9
                ,
                substr(vendor.name, 1, 6)                               AS 備考                 --10 (納入先)
            FROM
                order_plan      AS plan
                LEFT OUTER JOIN
                order_data      AS data
                    USING(sei_no)
                LEFT OUTER JOIN
                order_process   AS proc
                    USING(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                vendor_master   AS vendor
                    USING(vendor)
            WHERE
                plan.parts_no = '{$request->get('targetPartsNo')}' AND plan.zan_q > 0 AND data.uke_q > 0 AND data.ken_date = 0 AND proc.next_pro = 'END..'
            ORDER BY
                計上日 ASC, 伝票番号 ASC
        ";
        return $query;
    }
    
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status, $menu='')
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
<title>部品在庫経歴照会</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../parts_stock_history.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
<script type='text/javascript' src='../parts_stock_history.js'></script>
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
    
} // Class PartsStockHistory_Model End

?>
