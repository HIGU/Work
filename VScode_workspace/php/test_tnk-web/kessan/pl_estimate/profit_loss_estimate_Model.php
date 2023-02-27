<?php
//////////////////////////////////////////////////////////////////////////////
// 損益予測の集計・分析 結果 照会(都度照会)                  MVC Model 部   //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/13 Created   profit_loss_estimate_Model.php                      //
// 2011/07/14 暫定的に完成。都度照会版なので、見る時間によって数字が変わって//
        //     しまう。                                                      //
        //     今後の予定は、このプログラムの計算部分を利用して、毎朝計算し  //
        //     DBに登録する。その後このプログラムを照会のみに変更する。      //
        //     照会のみの場合は、対象年月と予測日を選択(年月のみなら最新)    //
        //     追加で、調整用のプログラムを作成。調整方法は、対象年月指定。  //
        //     直接数字をいじるわけではなく、調整金額を入力する形            //
        //     調整を加味する版と加味しない版の切り替えを可能にした方がいい？//
        //     調整した場合は、色を変えるなど何か分かるように                //
        //     マウスカーソルでコメント表示か、画面欄外に表示させる。        //
        //     このプログラムは、都度計算テスト用として隠しで残す。          //
        //     DBのつくりは、金額・率・note・対象年月・登録日付              //
        //     調整のDBも似た作りに、登録日付ではなく調整日付。調整者も？    //
        //     ※修正点 前日を出すとき-1しているが多分ダメ                   //
// 2011/07/19 都度照会版としてコメント追加                                  //
// 2011/07/20 商管・試修は暫定的に過去１年間の平均で表示                    //
// 2014/08/25 年月を変更して戻した(意味無かったので)                        //
// 2018/04/17 リニアの損益データがリニア標準に変更となったので修正          //
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
        $yyyymm = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($request->get('targetDateYM') == $yyyymm) {
            $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
        } else {
            $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
        }
        for ($i=1; $i<=12; $i++) {   // 36ヶ月前まで
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
        $file_name = "list/profit_loss_estimate_ViewListHeader-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/profit_loss_estimate_ViewList-test.html";
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
        $file_name = "list/profit_loss_estimate_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
            $listTable .= "<tr>\n";
            $listTable .= "        <td colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6' nowrap>項　　　目</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>カ　プ　ラ</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>リ　ニ　ア</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>試験・修理</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>商品管理</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>合　　　計</td>\n";
            $listTable .= "        <td width='400' align='left' class='pt10b' bgcolor='#ffffc6' nowrap>計算方法(商管・試修はすべて過去１年間の平均)</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>\n";
            $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>組立日程計画</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>実際棚卸高</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>納入予定金額(買掛)</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>最新総材料費より計算</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>\n";
            $listTable .= "        <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td> <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td> <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td> <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td> <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>直近１年間の売上高比</td> <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>701</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>701</td>\n";
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
    // 売上高と期末棚卸高の一部を取得(CL共通)
    private function getQueryStatement1($request, $div)
    {
        //$str_date = $request->get('targetDateYM') . '01';
        // 2011/08/30 予測精度向上の為 売上高の取得方法を変更
        // これまでは、組立日程計画のみで予測していたが
        // 前日までの売上実績＋当日～月末までの組立日程計画の合算へ変更
        // 開始日は計算日当日
        $str_date = date('Ymd');
        $end_date = $request->get('targetDateYM') . '31';
        /*if ($div == 'C') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
            } else {
                $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
            }
        } elseif ($div == 'L') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 37.00;  // リニア 2008/10/01価格改定以前
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 44.00;  // リニア 2008/10/01価格改定以降
            } else {
                $rate = 53.00;  // リニア 2011/04/01価格改定以降
            }
        } else {
            $rate = 65.00;
        }*/
        /*$query = "SELECT  
                    a.plan_no       AS 計画番号,
                    a.parts_no      AS 部品番号,
                    a.kanryou       AS 完了予定日,
                    a.plan          AS 計画数,
                    a.cut_plan      AS 打切数,
                    a.kansei        AS 完成数,
                    (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 最新総材料費,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * (a.plan-a.cut_plan), 0)
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (a.plan-a.cut_plan), 0) 
                    END             AS 材料費金額,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS 最新仕切単価,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan), 0)
                    END
                                    AS 売上高
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}'
                    AND (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
        ";
        */
        // 2011/08/30 仕切単価が存在しない場合、売上が計算されなかった為
        // その際は最真相材料費の1.13倍で仕切単価を計算し、売上を計算するように変更
        // また最新総材料費の取得時、WHEN時に対象月末までの最新を抜き出しているがplan_no = u.計画番号に変更
        // 2011/09/05 材料費は在庫に入るときに管理費が追加されるため、1.026を掛けて計算する
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "SELECT
                    a.plan_no       AS 計画番号,
                    a.parts_no      AS 部品番号,
                    a.kanryou       AS 完了予定日,
                    a.plan          AS 計画数,
                    a.cut_plan      AS 打切数,
                    a.kansei        AS 完成数,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                 ELSE
                                     Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                             END
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                    END             AS 最新総材料費,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan), 0)
                                 ELSE
                                     Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan), 0)
                             END
                        ELSE
                             Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * (a.plan-a.cut_plan), 0) 
                    END             AS 材料費金額,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)
                            END

                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS 最新仕切単価,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN 
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan), 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan), 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)  * (a.plan-a.cut_plan), 0) 
                            END
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan), 0)
                    END
                                    AS 売上高
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}' 
                    AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F'
        ";
        return $query;
    }
    
    // 期首棚卸高の取得(前月の期末棚卸高 CL共通)
    private function getQueryStatement2($request, $div)
    {
        if ($div == 'C') {
            $div_note = 'カプラ期末材料仕掛品棚卸高';
        } else {
            $div_note = 'リニア標準期末材料仕掛品棚卸高';
            //$div_note = 'リニア期末材料仕掛品棚卸高';
        }
        if (substr($request->get('targetDateYM'),4,2)!=01) {
            $p1_ym = $request->get('targetDateYM') - 1;
        } else {
            $p1_ym = $request->get('targetDateYM') - 100;
            $p1_ym = $p1_ym + 11;
        }
        $query = "
            SELECT kin FROM profit_loss_pl_history
            WHERE pl_bs_ym={$p1_ym} AND note='{$div_note}'
        ";
        return $query;
    }
    
    // 材料費の取得１(CL共通)
    private function getQueryStatement3($request, $div)
    {
        $str_date = $request->get('targetDateYM') . '01';
        $end_date = $request->get('targetDateYM') . '31';
        // 科目６以上が入っていたため５まで変更
        /*
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222'
        ";
        */
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222' AND kamoku<=5
        ";
        return $query;
    }
    
    // 材料費の取得２(CL共通)
    private function getQueryStatement4($request, $div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%' and vendor !='01111' and vendor !='00222' LIMIT 1
        ";
        return $query;
    }
    
    // 材料費の取得３(CL共通)
    private function getQueryStatement5($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                OFFSET 0
                LIMIT 1
        ";
        return $query;
    }
    
    // 材料費の取得４(CL共通)
    private function getQueryStatement6($request, $div)
    {
        $end_date = date('Ym');
        $end_date = $end_date . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > $str_date
                    AND
                    proc.delivery <= $end_date
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // 材料費の取得５(CL共通)
    private function getQueryStatement7($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                OFFSET 0
                LIMIT 1

        ";
        return $query;
    }
    
    // 材料費の取得６(CL共通)
    private function getQueryStatement8($request, $div)
    {
        $end_date = date('Ym') . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // 期末棚卸高の取得１(CL共通)   // 前日までの買掛金額
    private function getQueryStatement9($request, $div)
    {
        $str_date = $request->get('targetDateYM') . '01';
        $end_date = $request->get('targetDateYM') . '31';
        // 科目６以上が入っていたため５まで変更
        /*
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' 
        ";
        */
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' and kamoku<=5
        ";
        return $query;
    }
    
    // 期末棚卸高の取得２(CL共通) 検査仕掛分(未検収件数)の合計を取得
    private function getQueryStatement10($request, $div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%'
                LIMIT 1
        ";
        return $query;
    }
    
    // 期末棚卸高の取得３(CL共通) 納期遅れ分の合計を取得
    private function getQueryStatement11($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd') - 1;
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
                OFFSET 0
                LIMIT 1
        ";
        return $query;
    }
    
    // 期末棚卸高の取得４(CL共通) 本日以降のサマリーを取得
    private function getQueryStatement12($request, $div)
    {
        $end_date = date('Ym') . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    uke_date <= 0       -- 未納入分
                    AND
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    // 期末棚卸高の取得５(CL共通) 次工程品(注文書未発行) 納期遅れ分の合計を取得
    private function getQueryStatement13($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd') - 1;
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
                OFFSET 0
                LIMIT 1
        ";
        return $query;
    }
    // 期末棚卸高の取得６(CL共通) 次工程品(注文書未発行) 本日以降のサマリーを取得
    private function getQueryStatement14($request, $div)
    {
        $end_date = date('Ym') . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- 製造用であり
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- 初工程を除外
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- 手順書状態の物を除外
                    AND
                    proc.plan_cond='R'              -- 注文書が予定のもの
                    AND
                    data.order_no IS NULL           -- 注文書が実際に無い物
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- 初工程が打切されていない物
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    ///// 部品・その他売上高、材料費の取得
    private function getQueryStatement15($request, $div)
    {
        $end_date = $request->get('targetDateYM');
        $str_date = $request->get('targetDateYM');
        if (substr($str_date,4,2)>=07) {
            $str_date = $str_date - 6;
            $str_date = $str_date . '01';
        } else {
            $str_date = $str_date - 100;
            $str_date = $str_date + 6;
            $str_date = $str_date . '01';
        }
        if (substr($end_date,4,2)!=01) {
            $end_date = $end_date - 1;
            $end_date = $end_date . '31';
        } else {
            $end_date = $end_date - 100;
            $end_date = $end_date + 11;
            $end_date = $end_date . '31';
        }
        $query = "
            SELECT
                Uround(sum(Uround(数量*単価, 0)) / 6, 0)         AS 部品売上高
                ,
                Uround(sum(Uround(数量*ext_cost, 0)) / 6, 0)       AS 外作部品費
                ,
                Uround(sum(Uround(数量*int_cost, 0)) / 6, 0)      AS 内作部品費
                ,
                Uround(sum(Uround(数量*unit_cost, 0)) / 6, 0)      AS 合計部品費
                ,
                count(*)                            AS 総件数
                ,
                count(*)-count(unit_cost)
                                                    AS 未登録
            FROM
                hiuuri
            LEFT OUTER JOIN
                sales_parts_material_history ON (assyno=parts_no AND 計上日=sales_date)
            WHERE 計上日 >= {$str_date} AND 計上日 <= {$end_date}
             AND 事業部 = '{$div}' AND (assyno not like 'NKB%%') AND (assyno not like 'SS%%')
             AND datatype >= '3' 
        ";
        return $query;
    }
    ///// 労務費・経費金額取得
    private function getQueryStatement16($request, $note_name)
    {
        
            $end_date = $request->get('targetDateYM');
            $str_date = $request->get('targetDateYM');
            if (substr($str_date,4,2)==12) {
                $str_date = $str_date - 11;
            } else {
                $str_date = $str_date - 99;
            }
            if (substr($end_date,4,2)!=01) {
                $end_date = $end_date - 1;
            } else {
                $end_date = $end_date - 100;
                $end_date = $end_date + 11;
            }
            $query = "
                SELECT sum(kin) FROM profit_loss_pl_history
                    WHERE pl_bs_ym<={$end_date} AND pl_bs_ym>={$str_date} AND note='{$note_name}'
        ";
        return $query;
    }
    // 売上高と期末棚卸高の一部を取得(CL共通) 売上明細より
    private function getQueryStatement17($request, $div)
    {
        $str_date  = $request->get('targetDateYM') . '01';
        $end_date  = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '31';
        } elseif (substr($end_date,6,2)!=01) {
            $end_date  = date('Ymd') - 1;
        }
        $cost_date = $request->get('targetDateYM') . '31';
        /*if ($div == 'C') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
            } else {
                $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
            }
        } elseif ($div == 'L') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 37.00;  // リニア 2008/10/01価格改定以前
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 44.00;  // リニア 2008/10/01価格改定以降
            } else {
                $rate = 53.00;  // リニア 2011/04/01価格改定以降
            }
        } else {
            $rate = 65.00;
        }*/
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "select
                        u.計上日        as 計上日,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '完成'
                                WHEN u.datatype=2 THEN '個別'
                                WHEN u.datatype=3 THEN '手打'
                                WHEN u.datatype=4 THEN '調整'
                                WHEN u.datatype=5 THEN '移動'
                                WHEN u.datatype=6 THEN '直納'
                                WHEN u.datatype=7 THEN '売上'
                                WHEN u.datatype=8 THEN '振替'
                                WHEN u.datatype=9 THEN '受注'
                                ELSE u.datatype
                            END             as 区分,                    -- 1
                            CASE
                                WHEN trim(u.計画番号)='' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.計画番号
                            END                     as 計画番号,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as 製品番号,        -- 3
                            CASE
                                WHEN trim(u.入庫場所)='' THEN '--'         --NULLでなくてスペースで埋まっている場合はこれ！
                                ELSE u.入庫場所
                            END                     as 入庫,            -- 4
                            u.数量          as 数量,                    -- 5
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                            END             AS 最新総材料費,            -- 6
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * u.数量, 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * u.数量, 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.計画番号 ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * u.数量, 0)
                            END             AS 材料費金額,              -- 7
                            u.単価          as 仕切単価,                -- 8
                            Uround(u.数量 * u.単価, 0) as 金額          -- 9
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.計画番号=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.計画番号=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.計上日=pmate.sales_date) 
                      where 計上日>={$str_date} and 計上日<={$end_date} and 事業部='{$div}' and datatype='1'
                      order by u.計上日, assyno
        ";
        return $query;
    }
    ///// 商管・試修損益取得(6ヶ月)
    private function getQueryStatement18($request, $note_name)
    {
        
            $end_date = $request->get('targetDateYM');
            $str_date = $request->get('targetDateYM');
            if (substr($str_date,4,2)==06) {
                $str_date = $str_date - 11;
            } else {
                $str_date = $str_date - 99;
            }
            if (substr($end_date,4,2)!=01) {
                $end_date = $end_date - 1;
            } else {
                $end_date = $end_date - 100;
                $end_date = $end_date + 11;
            }
            $query = "
                SELECT sum(kin) FROM profit_loss_pl_history
                    WHERE pl_bs_ym<={$end_date} AND pl_bs_ym>={$str_date} AND note='{$note_name}'
        ";
        return $query;
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
<script type='text/javascript' src='../profit_loss_estimate.js'></script>
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
