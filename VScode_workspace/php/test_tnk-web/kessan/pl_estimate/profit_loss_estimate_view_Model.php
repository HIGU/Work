<?php
//////////////////////////////////////////////////////////////////////////////
// 損益予測の集計・分析 結果 照会(照会のみ)                  MVC Model 部   //
// Copyright (C) 2011-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/19 Created   profit_loss_estimate_view_Model.php                 //
// 2011/07/20 実際・差額の表示を追加。試修・商管・全体はコメント化          //
//            試修・商管は暫定的に過去１年分の平均を表示(計算済みを取得)    //
// 2011/07/25 年月の表示位置など、レイアウト調整                            //
// 2011/08/02 印刷ボタンのテスト                                            //
// 2011/08/04 単位と桁数を追加                                              //
// 2011/11/24 差額の計算が分かりにくかったので、数式を逆に変更              //
// 2014/08/25 すべての年月を表示するように変更                              //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント

require_once ('../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class ProfitLossEstimate_Model extends daoInterfaceClass
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
    
    ///// 対象年月のHTML <select> option の出力
    public function getTargetDateYMvalues($request)
    {
        // 初期化
        $option = "\n";
        $res_t = array(
            ["202201"],
            ["202202"],
            ["202203"],
            ["202204"],
            ["202205"],
            ["202206"],
            ["202207"],
            ["202208"],
            ["202209"],
            ["202210"],
            ["202211"],
            ["202212"],
        );
        //if ($request->get('targetDateYM') == $yyyymm) {
        //    $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
        //} else {
        //    $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
        //}
        for ($i=0; $i<12; $i++) {   // 12ヶ月前まで
            $yyyymm = $res_t[$i][0];
            $yyyy   = substr($yyyymm,0,4);
            $mm     = substr($yyyymm,4,2);
            if ($request->get('targetDateYM') == $yyyymm) {
                $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
            } else {
                $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
            }
        }
    
        return $option;
    }
    ///// 作成年月のHTML <select> option の出力
    public function getTargetDateYMDvalues($request)
    {
        // 初期化
        $res_t = array(
            ["20221101"],
            ["20221105"],
            ["20221110"],
            ["20221115"],
            ["20221120"],
            ["20221125"],
            ["20221130"],
        );

        for ($i=0; $i<7; $i++) {  // ３１日分(対象月分すべて)
            $ymd    = $res_t[$i][0];
            $yyyy   = substr($ymd,0,4);
            $mm     = substr($ymd,4,2);
            $dd     = substr($ymd,6,2);
            if ($request->get('targetDateYMD') == $ymd) {
                $option .= "<option value='{$ymd}' selected>{$yyyy}年{$mm}月{$dd}日</option>\n";
            } else {
                $option .= "<option value='{$ymd}'>{$yyyy}年{$mm}月{$dd}日</option>\n";
            }
        }
        
        return $option;
    }
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    データの明細 一覧表
    public function outViewListHTML($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        /*****************
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getViewHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/profit_loss_estimate_view_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        *****************/
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/profit_loss_estimate_view_ViewList-test.html";
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
        $file_name = "list/profit_loss_estimat_viewe_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        $query = "SELECT comment FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
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
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "<tr>\n";
        $listTable .= "        <td colspan='3' rowspan='2' width='200' align='center' class='pt11b' bgcolor='#ffffc6' nowrap>2022年12月予測<BR>（2022年11月15日時点）</td>\n";
        $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>カ　プ　ラ</td>\n";
        $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>リ　ニ　ア</td>\n";
        $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>試験・修理</td>\n";
        $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>商品管理</td>\n";
        $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>合　　　計</td>\n";
        $listTable .= "        <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>計算方法(商管・試修は過去１年間の平均)</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "<tr>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>予　測</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>実　際</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>差　額</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>予　測</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>実　際</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>差　額</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>予　測</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>実　際</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>差　額</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>予　測</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>実　際</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>差　額</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>予　測</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>実　際</td>\n";
        $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>差　額</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>\n";
        $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　　　　　　組立日程計画</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　　　　　　実際棚卸高</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　　納入予定金額(買掛)</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　最新総材料費より計算</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>\n";
        $listTable .= "        <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td> <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td> <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td> <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td> <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　　直近１年間の売上高比</td> <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_current_profit / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_current_profit_r / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_current_profit_d / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_current_profit / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_current_profit_r / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_current_profit_d / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_current_profit / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_current_profit_r / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_current_profit_d / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_current_profit / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_current_profit_r / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_current_profit_d / $tani), $keta) .    "</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>702</td>\n";
        $listTable .= "        <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        $res = array();
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
        if (!$title) {  // レコードが無い場合もNULLレコードが返るため変数の内容でチェックする
            $title = 'アイテムマスター未登録！';
        }
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>{$title}</th>\n";
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
    // 損益予測データの取得(CL共通)
    private function getQueryStatement1($request, $item)
    {
        $target_date = $request->get('targetDateYM');
        $create_date = $request->get('targetDateYMD');
        $query = "SELECT 
                            kin 
                    FROM    act_pl_estimate 
                    WHERE   target_ym={$target_date} AND cal_ymd={$create_date} AND note='{$item}'
        ";
        return $query;
    }
    // 損益実績データの取得(CL共通)
    private function getQueryStatement2($request, $item)
    {
        $target_date = $request->get('targetDateYM');
        //$target_date = 201106;
        $query = "SELECT 
                            kin 
                    FROM    profit_loss_pl_history
                    WHERE   pl_bs_ym={$target_date} AND note='{$item}'
        ";
        return $query;
    }
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status)
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>損益予測照会</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../profit_loss_estimate.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<style media=print>
<!--
/*ブラウザのみ表示*/
.dspOnly {
    display:none;
}
.footer {
    display:none;
}
// -->
</style>
<script type='text/javascript' src='../profit_loss_estimate_view.js'></script>
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
    
} // Class ProfitLossEstimate_Model End

?>
