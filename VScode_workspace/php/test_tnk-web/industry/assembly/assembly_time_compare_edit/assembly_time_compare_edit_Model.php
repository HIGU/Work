<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の完成一覧より実績工数と登録工数の比較                MVC Model 部   //
// Copyright (C) 2006-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created   assembly_time_compare_Model.php                     //
// 2006/03/13 SetInitWhere()に targetDivision を追加。未登録でも照会可能へ  //
//            assy_time/sche.kansei→assy_time / (sche.plan - sche.cut_plan)//
// 2006/05/01 コメント照会・編集ロジックを追加                              //
// 2006/05/02 コメントがある場合はバックグランドの色とメッセージを変える。  //
//            実績工数の算出方法を登録標準ロットから完成数に分母を変更      //
// 2006/05/08 コメントの照会・編集用テーブルのキーを製品番号→計画番号へ変更//
//            上記に伴いassembly_time_comment → assembly_time_plan_comment //
// 2006/05/10 手作業・自動機・外注・全体 別に照会オプションを追加           //
// 2006/05/12 登録工数の算出に使う完成数を累計へ変更 comp_pcs → kansei へ  //
// 2006/06/24 getUniResult() → $this->getUniResult() へ変更   83行目       //
// 2006/08/31 リストの完了番号を削除して行番号とライングループを追加 及び   //
//            クリックした項目をソートする機能を実装 安全のためLIMIT2000 ADD//
// 2006/08/31 項目ソート機能 追加による 各メソッドの変更及び追加            //
// 2007/06/11 コメントがあればチップヘルプ(title)を表示するを追加           //
// 2007/06/12 コメント登録Windowから親ウィンドウの画面更新対応でcommentSave //
//            とgetViewHTMLtable()メソッドを変更(変更箇所にマークとジャンプ)//
// 2007/09/03 製品番号を指定できるように追加(高野絹江さんから依頼)          //
// 2007/09/05 $this->where のデバッグメッセージを削除するのを忘れていた修正 //
// 2008/09/02 実績工数をSQLで抜出す時(C標準)sche.plan - sche.cut_planが     //
//            0になってしまい割り算でエラーになってしまうのを修正      大谷 //
// 2013/01/29 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                   大谷 //
//            バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2013/01/31 リニアのみのDPE抜出SQLを訂正                             大谷 //
// 2013/04/08 最下部に実績と登録の合計を追加                           大谷 //
// 2015/06/30 テストの為一時変更後元に戻す                             大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class AssemblyTimeCompareEdit_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    
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
            $this->where = $this->SetInitWhere($request);
            $this->order = $this->SetInitOrder($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    実績工数明細 ＆ 登録工数ヘッダー 一覧表
    public function outViewListHTML($request, $menu, $session)
    {
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLtable($request, $menu, $session);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/assembly_time_compare_edit_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする

        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLtableTop($request, $menu, $session);
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/assembly_time_compare_edit_ViewListTop-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// 製品のコメントを保存
    public function commentSave($request, $result, $session)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        // if ($request->get('comment') == '') return;  // これを行うと削除できない
        if ($request->get('targetPlanNo') == '') return;
        if ($request->get('targetAssyNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            if ($request->get('comment') == '') {
                // データ無し
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // 登録後 親のリロードはしないでWindow終了
                return;
            }
            $sql = "
                INSERT INTO assembly_time_plan_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "計画番号：{$request->get('targetPlanNo')}\\n\\nコメントの登録が出来ませんでした！　管理担当者へ連絡して下さい。";
            } else {
                $_SESSION['s_sysmsg'] = "計画番号：{$request->get('targetPlanNo')}\\n\\nコメントを登録しました。";
            }
        } else {
            if ($request->get('comment') == '') {
                // コメントの内容が削除されて更新の場合は、実レコードも削除
                $sql = "DELETE FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
                if ($this->query_affected($sql) <= 0) {
                    $_SESSION['s_sysmsg'] = "計画番号：{$request->get('targetPlanNo')}\\n\\nコメントの削除が出来ませんでした！　管理担当者へ連絡して下さい。";
                } else {
                    $_SESSION['s_sysmsg'] = "計画番号：{$request->get('targetPlanNo')}\\n\\nコメントを削除しました。";
                }
            } elseif ($comment == $request->get('comment')) {
                // 変更無し
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // 登録後 親のリロードはしないでWindow終了
                return;
            } else {
                $sql = "
                    UPDATE assembly_time_plan_comment SET comment='{$request->get('comment')}',
                    last_date='{$last_date}', last_host='{$last_host}'
                    WHERE plan_no='{$request->get('targetPlanNo')}'
                ";
                if ($this->query_affected($sql) <= 0) {
                    $_SESSION['s_sysmsg'] = "計画番号：{$request->get('targetPlanNo')}\\n\\nコメントの変更が出来ませんでした！　管理担当者へ連絡して下さい。";
                } else {
                    $_SESSION['s_sysmsg'] = "計画番号：{$request->get('targetPlanNo')}\\n\\nコメントを変更しました。";
                }
            }
        }
        $session->add('regPlan', $request->get('targetPlanNo'));  // マーカー及びジャンプ用に登録
        $result->add('AutoClose', 'window.close();'); // 登録後 Window終了
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
        $where = '';    // 初期化
        ///// 開始日と終了日の指定
        $where .= "WHERE comp_date >= {$request->get('targetDateStr')} ";
        $where .= "AND comp_date <= {$request->get('targetDateEnd')} ";
        $where .= "AND comp.assy_no LIKE '%{$request->get('targetAssyNo')}%' ";
        switch ($request->get('targetDivision')) {
        case 'CA':
            $where .= "AND sche.dept = 'C' ";
            break;
        case 'CH':
            $where .= "AND sche.dept = 'C' AND note15 NOT LIKE 'SC%' ";
            break;
        case 'CS':
            $where .= "AND sche.dept = 'C' AND note15 LIKE 'SC%' ";
            break;
        case 'LA':
            $where .= "AND sche.dept = 'L' ";
            break;
        case 'LH':
            //$where .= "AND sche.dept = 'L' AND comp.assy_no NOT LIKE 'LC%' AND comp.assy_no NOT LIKE 'LR%' ";
            $where .= "AND sche.dept = 'L' AND comp.assy_no NOT LIKE 'LC%' AND comp.assy_no NOT LIKE 'LR%' AND CASE WHEN comp.assyno = '' THEN sche.dept='L' ELSE midsc not like 'DPE%%' END ";
            break;
        case 'LB':
            //$where .= "AND sche.dept = 'L' AND (comp.assy_no LIKE 'LC%' OR comp.assy_no LIKE 'LR%') ";
            $where .= "AND sche.dept = 'L' AND (comp.assy_no LIKE 'LC%' OR comp.assy_no LIKE 'LR%' OR midsc like 'DPE%%') ";
            break;
        }
        return $where;
    }
    
    ////////// リクエストによりSQL文の基本ORDER区を設定
    protected function SetInitOrder($request)
    {
        ///// targetSortItemで切替
        switch ($request->get('targetSortItem')) {
        case 'plan':
            $order = 'ORDER BY 計画番号 ASC';
            break;
        case 'assy':
            $order = 'ORDER BY 製品番号 ASC';
            break;
        case 'name':
            $order = 'ORDER BY 製品名 ASC';
            break;
        case 'pcs':
            $order = 'ORDER BY 完成数 ASC';
            break;
        case 'date':
            $order = 'ORDER BY 完成日 ASC';
            break;
        case 'in_no':
            $order = 'ORDER BY 入庫 ASC';
            break;
        case 'res':
            $order = 'ORDER BY 実績工数 DESC';
            break;
        case 'reg':
            $order = 'ORDER BY 登録工数 DESC';
            break;
        default:
            $order = 'ORDER BY line_group ASC';
        }
        return $order;
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
    private function getViewTest(&$res, $request)
    {
        $where = '';    // 初期化
        ///// 開始日と終了日の指定
        $where .= "WHERE comp_date >= {$request->get('targetDateStr')} ";
        $where .= "AND comp_date <= {$request->get('targetDateEnd')} ";
        $where .= "AND comp.assy_no LIKE '%{$request->get('targetAssyNo')}%' ";
/**/
        switch ($request->get('targetDivision')) {
        case 'CA':
            $where .= "AND sche.dept = 'C' ";
            break;
        case 'CH':
            $where .= "AND sche.dept = 'C' AND note15 NOT LIKE 'SC%' ";
            break;
        case 'CS':
            $where .= "AND sche.dept = 'C' AND note15 LIKE 'SC%' ";
            break;
        case 'LA':
            $where .= "AND sche.dept = 'L' ";
            break;
        }
/**/
//        $where .= "AND SUBSTRING(assy_no,1,1)='L' ";
//            WHERE comp_date>=20201207 AND comp_date<=20201207 AND SUBSTRING(assy_no,1,1)='L'
        // 指定 日付 + リニア の 一覧取得 [0]～[6]
        $query = sprintf("
            SELECT comp_date AS 完成日, line_group AS ライン, comp_no AS 完了Ｎｏ．,
                   assy_no AS 製品Ｎｏ．, substr(midsc, 1, 16) AS 製品名, plan_no AS 計画Ｎｏ．, comp_pcs AS 完成数
            FROM assembly_completion_history AS comp
            LEFT OUTER JOIN assembly_schedule as sche USING(plan_no)
            LEFT OUTER JOIN miitem as m on assy_no=m.mipn
            %s
            ORDER BY comp_date, line_group, comp_no
            ", $where);
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            return 0;
        }
        // その他 情報取得
        for($r=0; $r<$rows; $r++){
            // 登録No. + 標準ロット 取得 [7]～[8]
            $query = sprintf("
                select reg_no AS 登録Ｎｏ．, std_lot AS 標準ロット FROM assembly_time_header WHERE assy_no='%s' ORDER BY reg_no DESC LIMIT 1
                ", $res[$r][3]);
            $res_tmp = array();
            if ( $this->getResult2($query, $res_tmp) < 1 ) {
                return 0;
            }
            $res[$r][7] = $res_tmp[0][0]; // 登録No.
            $res[$r][8] = $res_tmp[0][1]; // 標準ロット

            // 手作業（工数・組立費・合計工数・合計組立費）[9]～[12]
            // 自動機（工数・組立費・合計工数・合計組立費）[13]～[16]
            // 外  注（工数・組立費・合計工数・合計組立費）[17]～[20]
            // 合  計（工数・組立費・合計工数・合計組立費）[21]～[24]
            $query = sprintf("
                select pro_mark AS 工程記号, assy_time AS 組立工数, setup_time AS 段取時間 FROM assembly_standard_time WHERE assy_no='%s' AND reg_no=%d
                ", $res[$r][3], $res[$r][7]);
            $res_tmp = array();
            if ( ($rows2=$this->getResult2($query, $res_tmp)) < 1 ) {
                return 0;
            }
            $res[$r][9] = $res[$r][10] = $res[$r][11] = $res[$r][12] = $res[$r][13] = $res[$r][14] = $res[$r][15] = $res[$r][16] = $res[$r][17] = $res[$r][18] = $res[$r][19] = $res[$r][20] = 0;
            for($r2=0; $r2<$rows2; $r2++){
                if(substr($res_tmp[$r2][0],0,1) == 'H' ) {
                    $res[$r][9] +=round(($res_tmp[$r2][1] + $res_tmp[$r2][2] / $res[$r][8]),3);
                    $res[$r][10] = round($res[$r][9] * 53, 2);
                    $res[$r][11] =round($res[$r][9]*$res[$r][6], 3);
                    $res[$r][12] =round($res[$r][10]*$res[$r][6]);
                } else if(substr($res_tmp[$r2][0],0,1) == 'M' ) {
                    $res[$r][13] +=round(($res_tmp[$r2][1] + $res_tmp[$r2][2] / $res[$r][8]),3);
                    $res[$r][14] = round($res[$r][13] * 1, 2);
                    $res[$r][15] = round($res[$r][13]*$res[$r][6], 3);
                    $res[$r][16] = round($res[$r][14]*$res[$r][6]);
                } else if(substr($res_tmp[$r2][0],0,1) == 'G' ) {
                    $res[$r][17] +=round(($res_tmp[$r2][1] + $res_tmp[$r2][2] / $res[$r][8]),3);
                    $res[$r][18] = round($res[$r][17] * 18.8, 2);
                    $res[$r][19] = round($res[$r][17]*$res[$r][6], 3);
                    $res[$r][20] = round($res[$r][18]*$res[$r][6]);
                }
            }
            $res[$r][21] = $res[$r][9] + $res[$r][13] + $res[$r][17];
            $res[$r][22] = $res[$r][10] + $res[$r][14] + $res[$r][18];
            $res[$r][23] = $res[$r][11] + $res[$r][15] + $res[$r][19];
            $res[$r][24] = $res[$r][12] + $res[$r][16] + $res[$r][20];
        }
        return $rows;
    }
/* TEST 用 */
    ///// List部   組立 完成 一覧表 & 実績工数 & 登録工数
    private function getViewHTMLtableTop($request, $menu, $session)
    {
        $rows = $this->getViewTest($res, $request);   // TEST
        $query = $this->getQueryStatement($request);
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>工数（社内）</td>";
        $listTable .= "        <td>工数（社外）</td>";
        $listTable .= "        <td>合計 工数</td>";
        $listTable .= "        <td>金額（社内）</td>";
        $listTable .= "        <td>金額（社外）</td>";
        $listTable .= "        <td>合計 金額</td>";
        $listTable .= "        </td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>ホヨウ</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>ＯＥＭ</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>合計</td>";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    ///// List部   組立 完成 一覧表 & 実績工数 & 登録工数
    private function getViewHTMLtable($request, $menu, $session)
    {
        $rows = $this->getViewTest($res, $request);   // TEST
//$v = 0;
//$_SESSION['s_sysmsg'] = "完成日={$res[$v][0]}：完了No.={$res[$v][2]}：製品No.={$res[$v][3]}：計画No.={$res[$v][4]}：完成数={$res[$v][5]}：登録No.={$res[$v][6]}：標準ロット={$res[$v][7]}：H工数={$res[$v][8]}：H組立={$res[$v][9]}：H合計工数={$res[$v][10]}：H完成金額={$res[$v][11]}：M工数={$res[$v][12]}：M組立={$res[$v][13]}：M合計工数={$res[$v][14]}：M完成金額={$res[$v][15]}：G工数={$res[$v][16]}：G組立={$res[$v][17]}：G合計工数={$res[$v][18]}：G完成金額={$res[$v][19]}";
        $query = $this->getQueryStatement($request);
        // 初期化
        $listTable = '';
/*
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>工数（社内）</td>";
        $listTable .= "        <td>工数（社外）</td>";
        $listTable .= "        <td>合計 工数</td>";
        $listTable .= "        <td>金額（社内）</td>";
        $listTable .= "        <td>金額（社外）</td>";
        $listTable .= "        <td>合計 金額</td>";
        $listTable .= "        </td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>ホヨウ</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>ＯＥＭ</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "        <td>　</td>";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td>合計</td>";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
/*
$listTable .= "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='./../assembly_time_compare_edit_ViewHeader.html?item={request->get('targetSortItem')}&{$uniq}' name='header' align='center' width='100%' height='35' title='項目'>\n";
$listTable .= "    表の項目を表示しています。\n";
$listTable .= "</iframe>\n";
*/
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
//        $res = array();
//        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
        if ( $rows < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>完成データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
//$listTable .= "    <tr><td class='winbox' nowrap colspan='15' align='right'>-----------------------------------------------------------------------------------------------------------------------------------------</td></tr>\n";
            if ($session->get('regPlan') != '') {
                $regPlan = $session->get('regPlan');
                $session->add('regPlan', '');
            } else {
                $regPlan = '';
            }
            $sum_comp = $sum_h_kou = $sum_h_kin = $sum_m_kou = $sum_m_kin = $sum_g_kou = $sum_g_kin = $sum_a_kou = $sum_a_kin = 0;
            for ($i=0; $i<$rows; $i++) {
                $sum_comp += $res[$i][6];
                $sum_h_kou += $res[$i][11];
                $sum_h_kin += $res[$i][12];
                $sum_m_kou += $res[$i][15];
                $sum_m_kin += $res[$i][16];
                $sum_g_kou += $res[$i][19];
                $sum_g_kin += $res[$i][20];
                $sum_a_kou += $res[$i][23];
                $sum_a_kin += $res[$i][24];
// 上段
                if ($regPlan == $res[$i][0]) {  // コメントを登録した直後はマークを付ける 2007/06/12
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#ffffc6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 4%' align='right' ><a name='Mark' style='color:black;'>" . ($i+1) . "</a></td>\n";                    // 行番号
//                } elseif ($res[$i][10] == '') {   // コメントがあれば色を変える 2007/06/11チップヘルプ(title)も表示する
                } elseif ($res[$i][1] == 'A') {   // コメントがあれば色を変える 2007/06/11チップヘルプ(title)も表示する
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。\nダブルクリックでコメントの照会・編集が出来ます。'>\n";
                    $listTable .= "        <td class='winbox' rowspan='2' width=' 4%' align='right' >" . ($i+1) . "</td>\n";                    // 行番号
                } else {
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#e6e6e6;'>\n";
                    $listTable .= "        <td class='winbox' rowspan='2' width=' 4%' align='right' >" . ($i+1) . "</td>\n";                    // 行番号
                }
if( $i==0 || $res[$i][0] != $res[$i-1][0] ) {
                $listTable .= "        <td class='winbox' nowrap rowspan='2' width='8%' align='center' >" . format_date($res[$i][0]) . "</td>\n"; // 完成日
} else {
                $listTable .= "        <td class='winbox' nowrap rowspan='2' width='8%' align='center' >　</td>\n"; // 完成日
}
                $listTable .= "        <td class='winbox' nowrap rowspan='2' width='5%' align='left'>{$res[$i][2]}</td>\n";   // 完了No.
if( $i==0 || $res[$i][1] != $res[$i-1][1] ) {
                $listTable .= "        <td class='winbox' nowrap rowspan='2' width=' 2%' align='center'>{$res[$i][1]}</td>\n"; // ライングループ
} else {
                $listTable .= "        <td class='winbox' nowrap rowspan='2' width=' 2%' align='center'>　</td>\n"; // ライングループ
}
                $listTable .= "        <td class='winbox' nowrap width='8%' align='center'>{$res[$i][3]}</td>\n"; // 製品番号
//                $listTable .= "        <td class='winbox' nowrap width='19%' align='left'>" . mb_convert_kana($res[$i][4], 'k') . "</td>\n";   // 製品名
                $listTable .= "        <td class='winbox' nowrap width='8%' align='center'>{$res[$i][5]}</td>\n";                     // 計画番号
                $listTable .= "        <td class='winbox' nowrap rowspan='2' width=' 6%' align='right' >" . number_format($res[$i][6]) . "</td>\n";// 完成数
//                $listTable .= "        <td class='winbox' nowrap width=' 5%' align='center'>{$res[$i][7]}</td>\n";                     // 登録No.
//                $listTable .= "        <td class='winbox' nowrap width='11%' align='left'>{$res[$i][8]}</td>\n";                       // 標準ロット
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'\n";
                $listTable .= "        onClick='AssemblyTimeCompare.win_open(\"../../assembly_time_show/assembly_time_show_Main.php?targetPlanNo={$res[$i][5]}&noMenu=yes\", 900, 600)'\n";
                $listTable .= "        onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                if ($res[$i][9]) {
                    $listTable .= "        >" . number_format($res[$i][9], 3) . "</td>\n";                                                              // 実績工数
                } else {
                    $listTable .= "        >未入力</td>\n";                                                                     // 実績工数なし
                }
/*
                $listTable .= "        <td class='winbox' width=' 8%' align='right'\n";
                $listTable .= "        onClick='AssemblyTimeCompare.win_open(\"../../assembly_time_show/assembly_time_show_Main.php?targetPlanNo={$res[$i][5]}&noMenu=yes\", 900, 600)'\n";
                $listTable .= "        onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                if ($res[$i][10]) {
                    $listTable .= "        >" . number_format($res[$i][10], 2) . "</td>\n";                                                              // 登録工数
                } else {
                    if ($res[$i][12]) { // 登録番号があれば
                        $listTable .= "        ><span style='color:gray;'>0.000</span></td>\n";
                    } else {
                        $listTable .= "        >未登録</td>\n";                                                                 // 登録工数なし
                    }
                }
*/
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][11], 3) . "</td>\n"; // A合計工数
//                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][12]) . "</td>\n"; // A完成金額
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][13], 3) . "</td>\n"; // M工数
//                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][14], 2) . "</td>\n"; // M組立費
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][15], 3) . "</td>\n"; // M合計工数
//                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][16]) . "</td>\n"; // M完成金額
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][17], 3) . "</td>\n"; // G工数
//                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][18], 2) . "</td>\n"; // G組立費
                $listTable .= "        <td class='winbox' style='border-right: 3px solid red;' nowrap width=' 8%' align='right'>" . number_format($res[$i][19], 3) . "</td>\n"; // G合計工数
//                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][20]) . "</td>\n"; // G完成金額
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][21], 3) . "</td>\n"; // A合計工数
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][23], 3) . "</td>\n"; // A合計金額

                $listTable .= "    </tr>\n";
// 下段
                if ($regPlan == $res[$i][0]) {  // コメントを登録した直後はマークを付ける 2007/06/12
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#ffffc6;'>\n";
//                } elseif ($res[$i][10] == '') {   // コメントがあれば色を変える 2007/06/11チップヘルプ(title)も表示する
                } elseif ($res[$i][1] == 'A') {   // コメントがあれば色を変える 2007/06/11チップヘルプ(title)も表示する
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。\nダブルクリックでコメントの照会・編集が出来ます。'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#e6e6e6;'>\n";
                }
//                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' nowrap colspan='2' width='16%' align='left'>" . mb_convert_kana($res[$i][4], 'k') . "</td>\n";   // 製品名
                $listTable .= "        <td class='winbox' width=' 8%' align='right'\n";
                $listTable .= "        onClick='AssemblyTimeCompare.win_open(\"../../assembly_time_show/assembly_time_show_Main.php?targetPlanNo={$res[$i][5]}&noMenu=yes\", 900, 600)'\n";
                $listTable .= "        onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                if ($res[$i][10]) {
                    $listTable .= "        >" . number_format($res[$i][10], 2) . "</td>\n";                                                              // 登録工数
                } else {
                    if ($res[$i][12]) { // 登録番号があれば
                        $listTable .= "        ><span style='color:gray;'>0.000</span></td>\n";
                    } else {
                        $listTable .= "        >未登録</td>\n";                                                                 // 登録工数なし
                    }
                }
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][12]) . "</td>\n"; // A完成金額
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][14], 2) . "</td>\n"; // M組立費
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][16]) . "</td>\n"; // M完成金額
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][18], 2) . "</td>\n"; // G組立費
                $listTable .= "        <td class='winbox' style='border-right: 3px solid red;' nowrap width=' 8%' align='right'>" . number_format($res[$i][20]) . "</td>\n"; // G完成金額
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][22], 2) . "</td>\n"; // A組立費
                $listTable .= "        <td class='winbox' nowrap width=' 8%' align='right'>" . number_format($res[$i][24]) . "</td>\n"; // A完成金額
                $listTable .= "    </tr>\n";
            }
// 合計上段
            $listTable .= "<tr>\n";
            $listTable .= "<td class='winbox' colspan='6' rowspan='2' align='right'>合計</td>\n";
            $listTable .= "<td class='winbox' rowspan='2' align='right'>" . number_format($sum_comp) . "</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_h_kou, 3) . "</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_m_kou, 3) . "</td>\n";
            $listTable .= "<td class='winbox' style='border-right: 3px solid red;' colspan='2' align='right'>" . number_format($sum_g_kou, 3) . "</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_a_kou, 3) . "</td>\n";
            $listTable .= "</tr>\n";
// 合計下段
            $listTable .= "<tr>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_h_kin) . "</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_m_kin) . "</td>\n";
            $listTable .= "<td class='winbox' style='border-right: 3px solid red;' colspan='2' align='right'>" . number_format($sum_g_kin) . "</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_a_kin) . "</td>\n";
            $listTable .= "</tr>\n";

            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
/* オリジナル *
    ///// List部   組立 完成 一覧表 & 実績工数 & 登録工数
    private function getViewHTMLtable($request, $menu, $session)
    {
        $query = $this->getQueryStatement($request);
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>完成データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            if ($session->get('regPlan') != '') {
                $regPlan = $session->get('regPlan');
                $session->add('regPlan', '');
            } else {
                $regPlan = '';
            }
            $sum_results = 0;
            $sum_entry   = 0;
            for ($i=0; $i<$rows; $i++) {
                $sum_results += $res[$i][8];
                $sum_entry   += $res[$i][9];
                if ($regPlan == $res[$i][0]) {  // コメントを登録した直後はマークを付ける 2007/06/12
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#ffffc6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' ><a name='Mark' style='color:black;'>" . ($i+1) . "</a></td>\n";                    // 行番号
                } elseif ($res[$i][10] == '') {   // コメントがあれば色を変える 2007/06/11チップヘルプ(title)も表示する
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='現在コメントは登録されていません。\nダブルクリックでコメントの照会・編集が出来ます。'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";                    // 行番号
                } else {
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#e6e6e6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";                    // 行番号
                }
                // $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 3%' align='center'>{$res[$i][12]}</td>\n";                    // ライングループ
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";                     // 計画番号
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][1]}</td>\n";                     // 製品番号
                $listTable .= "        <td class='winbox' width='19%' align='left'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // 製品名
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >" . number_format($res[$i][3]) . "</td>\n";// 完成数
                $listTable .= "        <td class='winbox' width='12%' align='center' >{$res[$i][4]}</td>\n";                    // 完成日
                // $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][5]}</td>\n";                     // 完了番号
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][6]}</td>\n";                     // 入庫
                $listTable .= "        <td class='winbox' width='11%' align='left'>{$res[$i][7]}</td>\n";                       // 工番
                $listTable .= "        <td class='winbox' width=' 8%' align='right'\n";
                $listTable .= "        onClick='AssemblyTimeCompare.win_open(\"../../assembly_time_show/assembly_time_show_Main.php?targetPlanNo={$res[$i][0]}&noMenu=yes\", 900, 600)'\n";
                $listTable .= "        onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                if ($res[$i][8]) {
                    $listTable .= "        >{$res[$i][8]}</td>\n";                                                              // 実績工数
                } else {
                    $listTable .= "        >未入力</td>\n";                                                                     // 実績工数なし
                }
                $listTable .= "        <td class='winbox' width=' 8%' align='right'\n";
                $listTable .= "        onClick='AssemblyTimeCompare.win_open(\"../../assembly_time_show/assembly_time_show_Main.php?targetPlanNo={$res[$i][0]}&noMenu=yes\", 900, 600)'\n";
                $listTable .= "        onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                if ($res[$i][9]) {
                    $listTable .= "        >{$res[$i][9]}</td>\n";                                                              // 登録工数
                } else {
                    if ($res[$i][11]) { // 登録番号があれば
                        $listTable .= "        ><span style='color:gray;'>0.000</span></td>\n";
                    } else {
                        $listTable .= "        >未登録</td>\n";                                                                 // 登録工数なし
                    }
                }
                $listTable .= "    </tr>\n";
            }
            $listTable .= "<tr>\n";
            $listTable .= "<td class='winbox' colspan='5' align='right'>実績合計</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_results, 3) . "</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>登録合計</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_entry, 3) . "</td>\n";
            $listTable .= "</tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
/**/
    
    ///// List部   組立 完成 一覧表 & 実績工数 & 登録工数
    private function getQueryStatement($request)
    {
        $query1 = "
            SELECT   comp.plan_no   AS 計画番号         -- 00
                    ,comp.assy_no   AS 製品番号         -- 01
                    ,substr(midsc, 1, 16)
                                    AS 製品名           -- 02
                    ,comp_pcs       AS 完成数           -- 03
                    ,to_char(comp_date, '0000/00/00')
                                    AS 完成日           -- 04
                    ,to_char(comp_no, '00000')
                                    AS 完了番号         -- 05
                    ,CASE
                        WHEN in_no='1' THEN '14'
                        WHEN in_no='2' THEN '52'
                        WHEN in_no='3' THEN '30'
                        WHEN in_no='4' THEN '39'
                        WHEN in_no='5' THEN '40'
                        WHEN in_no='6' THEN '91'
                        WHEN in_no='7' THEN '74'
                        WHEN in_no='8' THEN '60'
                        WHEN in_no='9' THEN '21'
                        ELSE in_no
                     END            AS 入庫             -- 06
                    ,CASE
                        WHEN trim(sche.note15) = '' THEN '&nbsp;'
                        ELSE substr(sche.note15, 1, 8)
                     END            AS 工番             -- 07
        ";
        switch ($request->get('targetProcess')) {
        case 'M':       // 自動機工程
            $query2 = "
                    ,0              AS 実績工数         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- 累計完成数を使いたいため追加
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)とkanseiを入替え
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)とcomp_pcsを入替え
                            END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '2' -- 自動機
                     )              AS 登録工数         -- 09
            ";
            break;
        case 'G':       // 外注工程
            $query2 = "
                    ,(   SELECT sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- comp_pcs と入替え time_head_std_lot(comp.assy_no, comp_date)
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '3' -- 外注
                     )              AS 実績工数         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- 累計完成数を使いたいため追加
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)とkanseiを入替え
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)とcomp_pcsを入替え
                            END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '3' -- 外注
                     )              AS 登録工数         -- 09
            ";
            break;
        case 'A':       // 全工程
            $query2 = "
                            -- 以下の計算の分母を完成累計で行う場合は(SELECT sum(comp_pcs) FROM assembly_completion_history WHERE plan_no=comp.plan_no)
                    ,(   SELECT sum(Uround(assy_time / (sche.plan - sche.cut_plan), 3)) -- 個々の工数を計算してから合計を出す事
                         FROM assembly_process_time AS pro
                         WHERE sche.plan - sche.cut_plan <> 0 AND pro.plan_no = comp.plan_no
                     ) -- 手作業 が無い場合はNULL値が返るため 0となる
                     +
                     (   SELECT CASE
                                    WHEN sum(assy_time) IS NULL THEN 0
                                    ELSE sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- comp_pcs と入替え time_head_std_lot(comp.assy_no, comp_date)
                                END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND (pro_seg = '2' OR pro_seg = '3') -- 自動機と外注
                     ) -- 自動機 (現在は登録工数を使っている)
                                    AS 実績工数         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- 累計完成数を使いたいため追加
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)とkanseiを入替え
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)とcomp_pcsを入替え
                            END
                         FROM assembly_standard_time AS mei
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                     )              AS 登録工数         -- 09
            ";
            break;
        case 'H':       // 手作業工程
        default:
            $query2 = "
                            -- 以下の計算の分母を完成累計で行う場合は(SELECT sum(comp_pcs) FROM assembly_completion_history WHERE plan_no=comp.plan_no)
                    ,(   SELECT sum(Uround(assy_time / (sche.plan - sche.cut_plan), 3)) -- 個々の工数を計算してから合計を出す事
                         FROM assembly_process_time AS pro
                         WHERE sche.plan - sche.cut_plan <> 0 AND pro.plan_no = comp.plan_no
                     )              AS 実績工数         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- 累計完成数を使いたいため追加
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)とkanseiを入替え
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)とcomp_pcsを入替え
                            END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '1' -- 手作業
                     )              AS 登録工数         -- 09
            ";
            break;
        }
        $query3 = "
                    ,comment        AS コメント         -- 10
                    ,time_head_reg_no(comp.assy_no, comp_date)
                                    AS 登録番号         -- 11
                    ,CASE
                        WHEN line_group = ' ' THEN '&nbsp;'
                        ELSE line_group
                     END            AS ライングループ   -- 12
                    FROM
                        assembly_completion_history AS comp
                    LEFT OUTER JOIN
                        assembly_schedule           AS sche
                        USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (comp.assy_no=mipn)
                    LEFT OUTER JOIN
                        assembly_time_plan_comment USING (plan_no)
                    {$this->where}
                    {$this->order}
                    LIMIT 2000
        ";
                    // デバッグ用 WHERE comp_date >= 20060309 AND comp_date <= 20060309
        $query = ($query1 . $query2 . $query3);
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
<title>組立の完成一覧List部</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../assembly_time_compare_edit.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../assembly_time_compare_edit.js'></script>
</head>
<body>
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
    
} // Class AssemblyTimeCompareEdit_Model End

?>
