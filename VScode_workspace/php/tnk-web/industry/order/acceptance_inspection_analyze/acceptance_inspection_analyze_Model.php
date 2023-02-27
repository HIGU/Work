<?php
//////////////////////////////////////////////////////////////////////////////
// 受入検査の時間・件数の集計･分析 結果 照会                 MVC Model 部   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/08/09 Created   acceptance_inspection_analyze_Model.php             //
// 2006/11/30 outListLeadTime()受入検査日数の集計で初回リリース             //
// 2006/12/22 getDetailsHTMLbody()メソッドの検査時間にnumber_formatを追加   //
// 2007/01/10 Webで検収後にAS上で打切りされるケースがあり(通常は無いはず？) //
//            AND uke_date > 0 を3箇所のSELECT文に追加                      //
// 2007/01/19 検査中断(保留)機能追加により集計に反映 inspection_holding     //
// 2007/04/05 getDetailsHTMLfooter()メソッドの各合計時間を小数点３→０へ    //
// 2007/12/12 getViewHTMLconst()メソッドに$menuパラを追加。エラー表示対応   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class AcceptanceInspectionAnalyze_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $total_inspection;                  // 合計検査件数
    private $total_days;                        // 合計検査日数
    private $total_time;                        // 合計検査時間
    private $total_hold;                        // 合計中断時間
    private $total_actualTime;                  // 合計実時間
    private $total_average;                     // 全体の平均(日数又は時間)
    private $detail_user;                       // 明細表示の担当者
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        ///// プロパティ(メンバー変数)の初期化
        $this->total_inspection = 0;
        $this->total_days       = 0;
        $this->total_average    = 0;
        $this->total_time       = 0;
        $this->detail_user      = '';
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
        $yyyymm = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($request->get('targetDateYM') == $yyyymm) {
            $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
        } else {
            $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
        }
        for ($i=1; $i<=12; $i++) {   // 12ヶ月前まで
            $mm--;
            if ($mm < 1) {
                $mm = 12; $yyyy -= 1;
            }
            $mm = sprintf('%02d', $mm);
            $yyyymm = $yyyy . $mm;
            if ($request->get('targetDateYM') == $yyyymm) {
                $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
            } else {
                $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
            }
        }
        return $option;
    }
    
    ////////// MVC の Model 部 各種リスト及びグラフ生成
    ///// List部    担当者毎の検査日数のリスト生成
    public function outListLeadTime($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getLeadTimeHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getLeadTimeHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getLeadTimeHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// List部    担当者毎の受入検査時間リスト生成
    public function outListInspectionTime($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getInspectionTimeHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getInspectionTimeHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getInspectionTimeHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// List部    担当者毎の明細リスト生成 (共通でリストやグラフから2次的に呼ばれる)
    public function outListDetails($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getDetailsHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getDetailsHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getDetailsHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// コメントを保存
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
    
    ///// コメントを取得
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
    
    ///// エラーメッセージ用リスト出力
    public function outListErrorMessage($request, $menu)
    {
                /***** ヘッダー部を作成 *****/
        // 固定のHTMLソースを取得
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $headHTML .= $this->getLeadTimeHTMLheader($request);
        // 固定のHTMLソースを取得
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getErrorMessageHTMLbody($request, $menu);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        
                /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getLeadTimeHTMLfooter();
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTMLファイル出力
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
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
    ///// List部  検査日数 一覧表の ヘッダー部を作成
    private function getLeadTimeHTMLheader($request)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 7%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>明細</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>社員番号</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>氏　名</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>検査件数</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>検査日数</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>平均日数</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   検査日数 一覧表の 本文
    private function getLeadTimeHTMLbody($request, $menu)
    {
        $query = "
            SELECT uid          AS 社員番号
                , trim(name)    AS 社員名
                , count(uid)    AS 検査件数
                , sum(
                        EXTRACT(DAY FROM (end_timestamp - CAST(to_char(uke_date, 'FM9999-99-99') AS TIMESTAMP)))
                    )           AS 検査日数
                , Uround(
                        CAST(sum(EXTRACT(DAY FROM (end_timestamp - CAST(to_char(uke_date, 'FM9999-99-99') AS TIMESTAMP)))) / count(uid) AS NUMERIC)
                    , 3)        AS 平均日数
            FROM acceptance_kensa LEFT OUTER JOIN user_detailes USING(uid) LEFT OUTER JOIN order_data USING(order_seq)
            WHERE end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND end_timestamp < '{$request->get('targetDateEnd')} 24:00:00' AND uid IS NOT NULL AND uke_date > 0
            GROUP BY uid, name
            ORDER BY uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            $_SESSION['s_sysmsg'] = '受入検査の履歴がありません！';
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>受入検査の履歴がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                /*****
                if ($res[$i][10] != '') {   // コメントがあれば色を変える
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >" . ($i+1) . "</td>\n";    // 行番号
                $listTable .= "        <td class='winbox' width=' 8%' align='right' ><a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>明細</a></td>\n"; // 明細クリック用
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][0]}</td>\n";     // 社員番号
                if ($res[$i][0] == '00000A') {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >共有PC</td>\n";        // 氏　名
                } else {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][1]}</td>\n"; // 氏　名
                }
                $listTable .= "        <td class='winbox' width='15%' align='right' >{$res[$i][2]}</td>\n";     // 検査件数
                $listTable .= "        <td class='winbox' width='15%' align='right' >{$res[$i][3]}</td>\n";     // 検査日数
                $listTable .= "        <td class='winbox' width='20%' align='right' >{$res[$i][4]}</td>\n";     // 平均日数
                $listTable .= "    </tr>\n";
                ///// 検査件数と検査日数をプロパティに保存
                $this->total_inspection += $res[$i][2];
                $this->total_days       += $res[$i][3];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            $this->total_average = Uround($this->total_days / $this->total_inspection, 3);
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部  検査日数 一覧表の フッター部を作成
    private function getLeadTimeHTMLfooter()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='50%' align='right' >合計</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_inspection) . "</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_days) . "</td>\n";
        $listTable .= "        <td class='winbox' width='20%' align='right' >（avg.）" . number_format($this->total_average, 3) . "</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部  検査時間 一覧表の ヘッダー部を作成
    private function getInspectionTimeHTMLheader($request)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 7%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>明細</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>社員番号</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>氏　名</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>検査件数</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>検査時間(分)</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>平均時間(分)</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   検査時間 一覧表の 本文
    private function getInspectionTimeHTMLbody($request, $menu)
    {
        $query = "
            SELECT uid                              AS 社員番号
                , trim(name)                        AS 社員名
                , to_char(count(uid), 'FM9,999')    AS 検査件数
                , SUM(
                    CASE
                    WHEN hold.中断時間 IS NULL THEN
                        (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp)) * 24 * 60) +
                        (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp)) * 60) +
                        EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp)) +
                        Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp)) AS NUMERIC) / 60, 3)
                    ELSE
                        (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) * 24 * 60) +
                        (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) * 60) +
                        EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) +
                        Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) AS NUMERIC) / 60, 3)
                    END
                  )                                 AS 検査時間（分）
                , Uround(
                    CAST(
                        SUM(
                          CASE
                            WHEN hold.中断時間 IS NULL THEN
                            (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp)) * 24 * 60) +
                            (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp)) * 60) +
                            EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp)) +
                            Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp)) AS NUMERIC) / 60, 3)
                          ELSE
                            (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) * 24 * 60) +
                            (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) * 60) +
                            EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) +
                            Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp - hold.中断時間)) AS NUMERIC) / 60, 3)
                          END
                        ) / count(uid)
                    AS NUMERIC), 3
                  )                                AS 平均時間（分）
            FROM acceptance_kensa               AS kensa
                LEFT OUTER JOIN user_detailes   AS detail   USING(uid)
                LEFT OUTER JOIN order_data      AS data     USING(order_seq)
                LEFT OUTER JOIN (SELECT order_seq, sum(end_timestamp-str_timestamp) AS 中断時間 FROM inspection_holding
                 WHERE end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND end_timestamp < '{$request->get('targetDateEnd')} 24:00:00'
                 GROUP BY order_seq)            AS hold     USING(order_seq)
            WHERE kensa.end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND kensa.end_timestamp < '{$request->get('targetDateEnd')} 24:00:00' AND uid IS NOT NULL AND uke_date > 0
            GROUP BY uid, name
            ORDER BY uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            $_SESSION['s_sysmsg'] = '受入検査の履歴がありません！';
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>受入検査の履歴がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                /*****
                if ($res[$i][10] != '') {   // コメントがあれば色を変える
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >" . ($i+1) . "</td>\n";    // 行番号
                $listTable .= "        <td class='winbox' width=' 8%' align='right' ><a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>明細</a></td>\n"; // 明細クリック用
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][0]}</td>\n";     // 社員番号
                if ($res[$i][0] == '00000A') {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >共有PC</td>\n";        // 氏　名
                } else {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][1]}</td>\n"; // 氏　名
                }
                $listTable .= "        <td class='winbox' width='15%' align='right' >{$res[$i][2]}</td>\n";     // 検査件数
                $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($res[$i][3], 3) . "</td>\n";     // 検査時間
                $listTable .= "        <td class='winbox' width='20%' align='right' >" . number_format($res[$i][4], 3) . "</td>\n";     // 平均時間
                $listTable .= "    </tr>\n";
                ///// 検査件数と検査時間をプロパティに保存
                $this->total_inspection += $res[$i][2];
                $this->total_time       += $res[$i][3];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            $this->total_average = Uround($this->total_time / $this->total_inspection, 3);
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   一覧表の フッター部を作成
    private function getInspectionTimeHTMLfooter()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='50%' align='right' >合計</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_inspection) . "</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_time, 3) . "</td>\n";
        $listTable .= "        <td class='winbox' width='20%' align='right' >（avg.）" . number_format($this->total_average, 3) . "</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部  指定担当者の明細 一覧表の ヘッダー部を作成
    private function getDetailsHTMLheader($request)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>発行No.</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>部品番号</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>部品名</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>受付日</th>\n";
        $listTable .= "        <th class='winbox' width='10%' style='font-size:11pt;'>検開始日時</th>\n";
        $listTable .= "        <th class='winbox' width='10%' style='font-size:11pt;'>検終了日時</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>時間(分)</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>中断(分)</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>実検(分)</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%' style='font-size:11pt;'>受日数</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   指定担当者の明細 一覧表の 本文
    private function getDetailsHTMLbody($request, $menu)
    {
        $query = "
            SELECT to_char(data.order_seq, 'FM999-9999')    AS 発行連番
                , data.parts_no                             AS 部品番号
                , trim(substr(midsc, 1, 12))                AS 部品名
                , to_char(uke_date, 'FM9999-99-99')         AS 受付日
                , kensa.str_timestamp                       AS 開始日時
                , kensa.end_timestamp                       AS 終了日時
                ,
                (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp)) * 24 * 60) +
                (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp)) * 60) +
                EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp)) +
                Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp)) AS NUMERIC) / 60, 3)
                                                            AS 検査時間（分）
                , EXTRACT(DAY FROM (kensa.end_timestamp - CAST(to_char(uke_date, 'FM9999-99-99') AS TIMESTAMP)))
                                                            AS 検査日数
                ---------------------------------------------------------- 以下はリスト外のデータで使用
                , trim(name)                                AS 社員名
                , uid                                       AS 社員番号
                ,
                (EXTRACT(DAY FROM (hold.中断時間)) * 24 * 60) +
                (EXTRACT(HOUR FROM (hold.中断時間)) * 60) +
                EXTRACT(MINUTE FROM (hold.中断時間)) +
                Uround(CAST(EXTRACT(SECOND FROM (hold.中断時間)) AS NUMERIC) / 60, 3)
                                                            AS 中断時間（分）
            FROM acceptance_kensa               AS kensa
                LEFT OUTER JOIN user_detailes   AS detail USING(uid)
                LEFT OUTER JOIN order_data      AS data   USING(order_seq)
                LEFT OUTER JOIN order_plan      AS plan   USING(sei_no)
                LEFT OUTER JOIN miitem          AS item   ON(data.parts_no=mipn)
                LEFT OUTER JOIN (SELECT order_seq, sum(end_timestamp-str_timestamp) AS 中断時間 FROM inspection_holding
                 WHERE end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND end_timestamp < '{$request->get('targetDateEnd')} 24:00:00'
                 GROUP BY order_seq)            AS hold     USING(order_seq)
            WHERE kensa.end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND kensa.end_timestamp < '{$request->get('targetDateEnd')} 24:00:00'
                AND uid = '{$request->get('targetUid')}'  --'008044'  -- 個人を特定
                AND data.uke_date > 0
            ORDER BY kensa.end_timestamp ASC, kensa.str_timestamp ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            $_SESSION['s_sysmsg'] = '受入検査の履歴がありません！';
        }
        
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>受入検査の履歴がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                /*****
                if ($res[$i][10] != '') {   // コメントがあれば色を変える
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='コメントが登録されています。ダブルクリックでコメントの照会・編集が出来ます。' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // 行番号
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][0]}</td>\n";     // 発行No.(発行連番)
                $listTable .= "        <td class='winbox' width=' 9%' align='center'>{$res[$i][1]}</td>\n";     // 部品番号
                $listTable .= "        <td class='winbox' width='15%' align='left'  >{$res[$i][2]}</td>\n";     // 部品名
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][3]}</td>\n";     // 受付日
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][4]}</td>\n";     // 検査開始日時
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][5]}</td>\n";     // 検査終了日時
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($res[$i][6], 3) . "</td>\n";// 検査時間(分)
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($res[$i][10], 3) . "</td>\n";// 中断時間(分)
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($res[$i][6]-$res[$i][10], 3) . "</td>\n";// 実検査時間(分)
                $listTable .= "        <td class='winbox' width=' 6%' align='right'>{$res[$i][7]}</td>\n";     // 検査日数
                $listTable .= "    </tr>\n";
                ///// 検査時間と日数をプロパティに保存
                $this->total_time       += $res[$i][6];
                $this->total_hold       += $res[$i][10];
                $this->total_actualTime += ($res[$i][6]-$res[$i][10]);
                $this->total_days       += $res[$i][7];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            $this->total_inspection = $rows;
            $this->total_average = Uround($this->total_actualTime / $this->total_inspection, 3);
            if ($res[0][9] == '00000A') {
                $this->detail_user = '共有PC';
            } else {
                $this->detail_user = $res[0][8];
            }
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部  指定担当者の明細 一覧表の フッター部を作成
    private function getDetailsHTMLfooter()
    {
        $daysAverage = Uround($this->total_days / $this->total_inspection, 3);
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        ////////////// 上記は class='winbox_field list'としてフォントを強調したいがスペースの関係で断念
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='67%' align='right' >氏名：{$this->detail_user}&nbsp;&nbsp;&nbsp;&nbsp;合計件数＝" . number_format($this->total_inspection) . "&nbsp;&nbsp;平均時間＝" . number_format($this->total_average, 3) . "&nbsp;&nbsp;平均日数＝" . number_format($daysAverage, 3) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($this->total_time, 0) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($this->total_hold, 0) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($this->total_actualTime, 0) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 6%' align='right' >" . number_format($this->total_days) . "</td>\n";
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
<title>受入検査の集計結果</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../acceptance_inspection_analyze.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<!-- <script type='text/javascript' src='../acceptance_inspection_analyze.js'></script> -->
<script type='text/javascript'>
    function win_open(url, w, h, winName)
    {
        if (!winName) winName = '';
        if (!w) w = 980;     // 初期値
        if (!h) h = 500;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, winName, 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
</script>
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
    
    ///// エラーメッセージ用 一覧表の 本文
    private function getErrorMessageHTMLbody($request, $menu)
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td width='100%' align='center' class='winbox'>開始又は終了日付にエラーがあります。</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
} // Class AcceptanceInspectionAnalyze_Model End

?>
