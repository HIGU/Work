<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の登録工数と実績工数の比較 照会       MVC Model 部                   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created   assembly_time_show_Model.php                        //
// 2006/03/03 工程明細のAjax用照会 getViewProcessTable()メソッドを追加      //
// 2006/03/05 データがない場合の表示を統一 getPlanData()メソッドを追加      //
// 2006/03/06 工数関係の合計を集計し色分けして表示(表を見やすくした)        //
//            実績工数の明細をいきなり出さずに表示・非表示ボタンで操作DHTML //
// 2006/03/09 str_time ASC, end_time ASC → str_time ASC, 完了日順 ASC      //
// 2006/03/13 getViewJissekiTable()の項目幅をパーセンテージへ変更           //
// 2006/05/02 getViewRegisterTable()に生産ロットによる合計工数表示追加      //
// 2006/05/05 上記のメソッドを完成数が０以下の場合に対応                    //
// 2006/05/10 getViewRegisterTable()を手作業・自動機・外注に分類            //
// 2006/05/12 外注段取工数の登録が出てきたため SQLロジックを追加            //
// 2006/05/19 登録工数のみの表示機能を追加 regOnly 計画数での合計工数も追加 //
// 2006/05/28 上記を更に改良しgetViewRegisterTable()を計画残数対応へ変更    //
// 2007/06/17 組立完了予定日時の追加のためgetViewRegisterTable()メソッドに  //
//            usedTime, workerCount を追加＋getPlanEndTime()メソッドを追加  //
// 2007/06/20 DBプロシージャーoverlaps_time_diff()を追加。休み時間対応へ    //
//            上記で稼働時間外の加算は残業時間に指示した時ロジックがポイント//
// 2007/06/22 更に上記phpロジックをストアードプロシージャーへ移行           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../daoInterfaceClass.php');    // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class AssemblyTimeShow_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    
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
        case 'ListTable':
            $this->where = $this->SetInitWhere($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    実績工数明細 ＆ 登録工数ヘッダー 一覧表
    public function getViewListTable($request)
    {
        $listTable = '';    // 初期化
        if ($request->get('regOnly') == 'no') {
            $listTable .= $this->getViewJissekiTable($request);
        }
        $listTable .= $this->getViewRegisterTable($request);
        /*** デバッグ用
        $handle = fopen('debug-assembly_time_show.html', 'w');
        fwrite($handle, $listTable);
        fclose($handle);
        ***/
        return $listTable;
    }
    
    ///// List部    登録工程明細 一覧表
    public function getViewProcessTable($request)
    {
        $query = "
            SELECT pro_no       AS 工程番号     -- 00
                ,pro_mark       AS 工程記号     -- 01
                ,line_no        AS ライン番号   -- 02
                ,assy_time      AS 登録工数     -- 03
                ,Uround(setup_time / std_lot, 3)
                                AS 段取工数     -- 04
                ,setup_time     AS 段取時間     -- 05
                ,man_count      AS 作業人数     -- 06
                ,assy_time + Uround(setup_time / std_lot, 3)
                                AS 合計工数     -- 07
                ,CASE
                    WHEN pro_seg = '1' THEN '手作業'
                    WHEN pro_seg = '2' THEN '自動機'
                    WHEN pro_seg = '3' THEN '外　注'
                    ELSE pro_seg
                 END            AS 工程区分     -- 08
            FROM
                assembly_standard_time
            LEFT OUTER JOIN
                assembly_time_header USING(assy_no, reg_no)
            LEFT OUTER JOIN
                assembly_process_master USING(pro_mark)
            WHERE
                assy_no='{$request->get('targetAssyNo')}' AND reg_no={$request->get('targetRegNo')}
            ORDER BY
                assy_no ASC, reg_no ASC, pro_no ASC
        ";
        $listTable = '';
        $listTable .= "<table width='700' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <caption><span style='color:red;'>工程明細</span>　登録番号：{$request->get('targetRegNo')}</caption>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' align='center'>工程番号</th>\n";
        $listTable .= "        <th class='winbox' align='center'>工程記号</th>\n";
        $listTable .= "        <th class='winbox' align='center'>工程区分</th>\n";
        $listTable .= "        <th class='winbox' align='center'>ライン番号</th>\n";
        $listTable .= "        <th class='winbox' align='center'>工数(分)</th>\n";
        $listTable .= "        <th class='winbox' align='center'>段取工数</th>\n";
        $listTable .= "        <th class='winbox' align='center'>合計工数</th>\n";
        $listTable .= "        <th class='winbox' align='center'>段取時間</th>\n";
        $listTable .= "        <th class='winbox' align='center'>作業人数</th>\n";
        $listTable .= "    </tr>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='700' colspan='9' align='center' class='winbox'>工程明細データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $kousu    = 0;
            $dan_kosu = 0;
            $sum_kosu = 0;
            $sum_dan  = 0;
            $sum_man  = 0;
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][0]}</td>\n";
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][1]}</td>\n";
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][8]}</td>\n";
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][2]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][3]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][4]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][7], 3) . "</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][5]}</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][6]}</td>\n";
                $listTable .= "    </tr>\n";
                $kousu    += $res[$i][3];
                $dan_kosu += $res[$i][4];
                $sum_kosu += $res[$i][7];
                $sum_dan  += $res[$i][5];
                $sum_man  += $res[$i][6];
            }
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='right' colspan='4'>合　計</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($kousu, 3)    . "</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($dan_kosu, 3) . "</td>\n";
            $listTable .= "        <td class='winbox' align='right' style='color:red;'>" . number_format($sum_kosu, 3) . "</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($sum_dan, 3)  . "</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($sum_man, 2)  . "</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function SetInitWhere($request)
    {
        $where = '';    // 初期化
        ///// 計画番号の指定
        $where .= "WHERE plan_no = '{$request->get('targetPlanNo')}'";
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
    ///// List部    実績工数明細 一覧表
    private function getViewJissekiTable($request)
    {
        $query = "SELECT plan_no        AS 計画番号     -- 00
                        ,parts_no       AS 製品番号     -- 01
                        ,substr(midsc, 1, 20)
                                        AS 製品名       -- 02
                        ,plan_pcs       AS 計画残数     -- 03
                        ,user_id        AS 社員番号     -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '応援者' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS 作業者       -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 開始日時     -- 06
                        ,CASE
                            WHEN to_char(end_time, 'YY/MM/DD HH24:MI:SS') = '70/01/01 00:00:00'
                            THEN '未完了'
                            ELSE to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                         END            AS 完了日時     -- 07
                        ,CASE
                            WHEN assy_time IS NULL
                            THEN 0
                            ELSE assy_time
                         END            AS 合計工数     -- 08
                        -----------------------------リストは上記まで
                        ,group_name     AS グループ名   -- 09
                        ,serial_no      AS 連番         -- 10
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 開始詳細     -- 11
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS 完了詳細     -- 12
                        ,CASE
                            WHEN plan_pcs > 0 AND assy_time IS NOT NULL
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE 0
                         END            AS 工数         -- 13
                        ,plan-cut_plan  AS 計画数       -- 14
                        ,kansei         AS 完成数       -- 15
                        ,CASE
                            WHEN end_time = '1970-01-01 00:00:00'
                            THEN CURRENT_TIMESTAMP(0)
                            ELSE end_time
                         END            AS 完了日順     -- 16
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    LEFT OUTER JOIN
                        assembly_process_group USING(group_no)
                    {$this->where}
                    ORDER BY
                        str_time ASC, 完了日順 ASC
        ";
        // 製品番号・製品名・計画数・完成数の取得
        $this->getPlanData($request, $res);
        $listTable = '';
        $listTable .= "<table width='870' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <caption><span style='color:blue;'>実績工数</span>　製品番号：{$res['assy_no']}　製品名：{$res['assy_name']}　計画数：" . number_format($res['keikaku']) . "　完成数：" . number_format($res['kansei']) . "</caption>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width='13.00%' align='center'>グループ名</th>\n";
        $listTable .= "        <th class='winbox' width=' 8.85%' align='center'>組立数</th>\n";
        $listTable .= "        <th class='winbox' width=' 9.29%' align='center'>社員番号</th>\n";
        $listTable .= "        <th class='winbox' width='12.52%' align='center'>作 業 者 名</th>\n";
        $listTable .= "        <th class='winbox' width='18.08%' align='center'>組立着手</th>\n";
        $listTable .= "        <th class='winbox' width='18.08%' align='center'>完了(中断)</th>\n";
        $listTable .= "        <th class='winbox' width='11.06%' align='center'>工数計(分)</th>\n";
        $listTable .= "        <th class='winbox' width=' 9.12%' align='center'>工数(分)</th>\n";
        $listTable .= "    </tr>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='8' width='870' align='center' class='winbox'>実績データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $sokosu = 0;
            $kosu   = 0;
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='group{$i}'   ></span></td>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='indust{$i}'  ></span></td>\n";
                $listTable .= "        <td class='winbox' align='center'><span id='emp_no{$i}'  ></span></td>\n";
                $listTable .= "        <td class='winbox' align='left'  ><span id='emp_name{$i}'></span></td>\n";
                $listTable .= "        <td class='winbox' align='center'><span id='chaku{$i}'   ></span></td>\n";
                $listTable .= "        <td class='winbox' align='center'><span id='kanryo{$i}'  ></span></td>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='sokousu{$i}' ></span></td>\n";
                $listTable .= "        <td class='winbox' align='right' ><span id='kousu{$i}'   ></span></td>\n";
                $listTable .= "    </tr>\n";
                $sokosu += $res[$i][8];
                $kosu   += $res[$i][13];
            }
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center' colspan='2'><input type='button' name='meisai' value='明細表示'\n";
            $listTable .= "            onClick=\"\n";
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "                document.getElementById('group{$i}').innerHTML=   '{$res[$i][9]}';\n";
                $listTable .= "                document.getElementById('indust{$i}').innerHTML=  '" . number_format($res[$i][3]) . "';\n";
                $listTable .= "                document.getElementById('emp_no{$i}').innerHTML=  '{$res[$i][4]}';\n";
                $listTable .= "                document.getElementById('emp_name{$i}').innerHTML='{$res[$i][5]}';\n";
                $listTable .= "                document.getElementById('chaku{$i}').innerHTML=   '{$res[$i][6]}';\n";
                $listTable .= "                document.getElementById('kanryo{$i}').innerHTML=  '{$res[$i][7]}';\n";
                $listTable .= "                document.getElementById('sokousu{$i}').innerHTML= '{$res[$i][8]}';\n";
                $listTable .= "                document.getElementById('kousu{$i}').innerHTML=   '{$res[$i][13]}';\n";
            }
            $listTable .= "            \"\n";
            $listTable .= "            style='color:blue;'>\n";
            $listTable .= "        <input type='button' name='noDisp' value='非表示'\n";
            $listTable .= "            onClick=\"\n";
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "                document.getElementById('group{$i}').innerHTML=   '';\n";
                $listTable .= "                document.getElementById('indust{$i}').innerHTML=  '';\n";
                $listTable .= "                document.getElementById('emp_no{$i}').innerHTML=  '';\n";
                $listTable .= "                document.getElementById('emp_name{$i}').innerHTML='';\n";
                $listTable .= "                document.getElementById('chaku{$i}').innerHTML=   '';\n";
                $listTable .= "                document.getElementById('kanryo{$i}').innerHTML=  '';\n";
                $listTable .= "                document.getElementById('sokousu{$i}').innerHTML= '';\n";
                $listTable .= "                document.getElementById('kousu{$i}').innerHTML=   '';\n";
            }
            $listTable .= "            \"\n";
            $listTable .= "            style='color:black;'>\n";
            $listTable .= "        </td>\n";
            $listTable .= "        <td class='winbox' align='right' colspan='4'>合計</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($sokosu, 3) . "</td>\n";
            $listTable .= "        <td class='winbox' align='right' style='color:blue;'>" . number_format($kosu, 3) . "</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <span id='jissekiMei'>\n";
            $listTable .= "    </span>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    ///// List部    登録工数 一覧表
    private function getViewRegisterTable($request)
    {
        // 計画番号から製品番号の取得(実績データの無い場合の対応)
        // 製品番号・製品名・計画数・完成数の取得
        $this->getPlanData($request, $res);
        // 登録工数の取得
        $assy_no    = $res['assy_no'];
        $assy_name  = $res['assy_name'];
        $keikaku    = $res['keikaku'];
        $kansei     = $res['kansei'];
        $kei_zan    = $keikaku - $kansei;
        $query = $this->getQueryStatement($assy_no, $kansei, $kei_zan);
        // 初期化
        $listTable = '';
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) > 0 ) {
            if ($request->get('regOnly') == 'yes') {
                $all_time = Uround($kei_zan * ($res[0][4]+$res[0][14]), 3);
                $need_time = Uround($all_time - $request->get('usedTime'), 3);  // 残り工数
                // 必要工数から完了予定日時を取得
                $end_date_time = $this->getPlanEndTime($request, $need_time, $str_date_time);
                $need_time = number_format($need_time, 3);
                $listTable .= "<table width='870' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
                $listTable .= "    <caption><span style='color:blue;'>予定</span>　計画番号：{$request->get('targetPlanNo')}　製品番号：{$assy_no}　製品名：{$assy_name}　計画数：" . number_format($keikaku) . "　完成数：" . number_format($kansei) . "</caption>\n";
                $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center' rowspan='1' style='color:blue;'>組立予定数での合計手作業工数</td>\n";
                $listTable .= "        <th class='winbox' align='center'>計画残数</th>\n";
                $listTable .= "        <th class='winbox' align='center'>手作業合計</th>\n";
                $listTable .= "        <th class='winbox' align='center'>自動機合計</th>\n";
                $listTable .= "        <th class='winbox' align='center'>　外　注&nbsp;&nbsp;</th>\n";
                $listTable .= "        <th class='winbox' align='center'>合計工数</th>\n";
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center' style='font-size:0.95em; color:blue; background-color:#ceffce;'>計画残 {$kei_zan} Ｘ (手作業工数 ". number_format($res[0][4], 3) . " ＋ 段取工数 " . number_format($res[0][14], 3) . ")</td>\n";// ロット合計工数
                $listTable .= "        <td class='winbox' align='right' >{$kei_zan}</td>\n";    // 計画残数  #ffffc6(薄い黄色) #ceffce(エメラルドグリーン)
                $listTable .= "        <td class='winbox' align='right' style='color:blue; background-color:#ceffce;'>" . number_format($kei_zan * ($res[0][4]+$res[0][14]), 3) . "</td>\n";  // 手作業工数(計画残)
                $listTable .= "        <td class='winbox' align='right' style='color:black;'>" . number_format($kei_zan * ($res[0][6]+$res[0][15]), 3) . "</td>\n";  // 自動機工数(計画残)
                $listTable .= "        <td class='winbox' align='right' style='color:black;'>" . number_format($kei_zan * ($res[0][8]+$res[0][16]), 3) . "</td>\n";  // 外注工数(計画残)
                $listTable .= "        <td class='winbox' align='right' style='color:black;'>" . number_format($kei_zan * ($res[0][4]+$res[0][14]+$res[0][6]+$res[0][15]+$res[0][8]+$res[0][16]), 3) . "</td>\n"; // 合計工数(計画残)
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";                                             // テスト用 計画番号 C4290968
                $listTable .= "        <td class='winbox' align='center' rowspan='1' style='color:blue;'>組立 完了 予定日時　　(開始)</td>\n";
                $listTable .= "        <th class='winbox' align='center' colspan='2'>現在までの使用工数</th>\n";
                $listTable .= "        <th class='winbox' align='center' colspan='3'>現時点での必要工数と作業者数</th>\n";
                $listTable .= "    </tr>\n";
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center' style='font-size:1.1em; color:blue; background-color:#ceffce;'>{$end_date_time}　<span style='font-size:0.8em;'>({$str_date_time})</span></td>\n";
                $listTable .= "        <td class='winbox' align='right' colspan='2'>" . number_format($request->get('usedTime'), 3) . "分</td>\n";
                $listTable .= "        <td class='winbox' align='right' colspan='3'>工数：{$need_time}分÷作業者：{$request->get('workerCount')}人</td>\n";
                $listTable .= "    </tr>\n";
                $listTable .= "</table>\n";
                $listTable .= "    </td></tr>\n";
                $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            }
        }
        $listTable .= '<br>';
        $listTable .= "<table width='870' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <caption><span style='color:red;'>登録工数</span>　製品番号：{$assy_no}　製品名：{$assy_name}　計画数：" . number_format($keikaku) . "　完成数：" . number_format($kansei) . "</caption>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' align='center'>&nbsp;</th>\n";
        $listTable .= "        <th class='winbox' align='center'>登録番号</th>\n";
        $listTable .= "        <th class='winbox' align='center'>標準出庫時間</th>\n";
        $listTable .= "        <th class='winbox' align='center'>標準ロット</th>\n";
        $listTable .= "        <th class='winbox' align='center'>総段取時間</th>\n";
        $listTable .= "        <th class='winbox' align='center'>設　定　日</th>\n";
        $listTable .= "        <th class='winbox' align='center'>手作業合計</th>\n";
        $listTable .= "        <th class='winbox' align='center'>自動機合計</th>\n";
        $listTable .= "        <th class='winbox' align='center'>　外　注&nbsp;&nbsp;</th>\n";
        $listTable .= "        <th class='winbox' align='center'>合計工数</th>\n";
        $listTable .= "    </tr>\n";
        if ($rows < 1) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='10' width='870' align='center' class='winbox'>工数が登録されてません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            if ($kansei > 0) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' colspan='3' align='right' >生産ロット</td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$kansei}</td>\n";                         // 生産ロット(完成数)
                $listTable .= "        <td class='winbox' colspan='2' align='right'>生産ロットでの工数</td>\n";     // 各工数
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][4]+$res[0][10], 3) . "</td>\n"; // 手作業工数(生産ロット)
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][6]+$res[0][11], 3) . "</td>\n"; // 自動機工数(生産ロット)
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][8]+$res[0][12], 3) . "</td>\n"; // 外注工数(生産ロットは関係ないが登録があるため)
                $listTable .= "        <td class='winbox' align='right' style='color:yellow;'>" . number_format($res[0][4]+$res[0][10]+$res[0][6]+$res[0][11]+$res[0][8]+$res[0][12], 3) . "</td>\n"; // 合計工数(生産ロット)
                $listTable .= "    </tr>\n";
            }
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' align='center'>\n";
                $listTable .= "            <input type='button' name='process' value='明細' onClick='AssemblyTimeShow.processExecute(\"{$assy_no}\", \"{$res[$i][0]}\")' style='color:red;'>\n";
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][0]}</td>\n";                         // 登録番号
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][9], 3) . "</td>\n"; // 標準資材出庫時間
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][1]) . "</td>\n";    // 標準ロット
                $listTable .= "        <td class='winbox' align='right' >{$res[$i][2]}</td>\n";                         // 総段取時間
                $listTable .= "        <td class='winbox' align='center'>{$res[$i][3]}</td>\n";                         // 設定日
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][4]+$res[$i][5], 3) . "</td>\n"; // 手作業合計 (工数＋段取)
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][6]+$res[$i][7], 3) . "</td>\n"; // 自動機合計 (工数＋段取)
                $listTable .= "        <td class='winbox' align='right' >" . number_format($res[$i][8]+$res[$i][13], 3) . "</td>\n";    // 外注工数 (工数＋段取)
                $listTable .= "        <td class='winbox' align='right' style='color:red;'>" . number_format($res[$i][4]+$res[$i][5]+$res[$i][6]+$res[$i][7]+$res[$i][8]+$res[$i][13], 3) . "</td>\n"; // 合計工数
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            $listTable .= "<div id='showAjax2'>\n";
            $listTable .= "</div>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    ///// List部   一覧表のSQLステートメント取得
    private function getQueryStatement($assy_no, $kansei, $kei_zan)
    {
        if ($kansei <= 0) {
            $kansei = 1;    // 以下で計算するためゼロ割回避
        }
        if ($kei_zan <= 0) {
            $kei_zan = 1;   // 以下で計算するためゼロ割回避
        }
        $query = "
            SELECT to_char(reg_no, '0000000')
                              AS 登録番号     -- 00
              ,std_lot        AS 標準ロット   -- 01
              ,(   SELECT sum(setup_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no
              )               AS 総段取時間   -- 02
              ,to_char(setdate, '0000/00/00')
                              AS 設定日       -- 03
              ,(   SELECT sum(assy_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS 手作業工数   -- 04
              ,(   SELECT Uround(sum(setup_time) / head.std_lot, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS 手段取工数   -- 05
              ,(   SELECT sum(assy_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS 自動機工数   -- 06
              ,(   SELECT Uround(sum(setup_time) / head.std_lot, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS 自段取工数   -- 07
              ,(   SELECT sum(assy_time)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS 外注工数     -- 08
              ,pick_time      AS 標準出庫時間 -- 09
              ---------------------------------------------------------- 生産ロット(完成数)による段取工数
              ,(   SELECT Uround(sum(setup_time) / {$kansei}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS 手段取工数生産   -- 10
              ,(   SELECT Uround(sum(setup_time) / {$kansei}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS 自段取工数生産   -- 11
              ,(   SELECT Uround(sum(setup_time) / {$kansei}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS 外段取工数生産   -- 12
              ------------------------------------------ 登録工数の外注段取工数 外注だけ後から追加したため
              ,(   SELECT Uround(sum(setup_time) / head.std_lot, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS 外段取工数       -- 13
              ---------------------------------------------------------- 計画残数による段取工数
              ,(   SELECT Uround(sum(setup_time) / {$kei_zan}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '1'
              )               AS 手段取工数生産   -- 14
              ,(   SELECT Uround(sum(setup_time) / {$kei_zan}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '2'
              )               AS 自段取工数生産   -- 15
              ,(   SELECT Uround(sum(setup_time) / {$kei_zan}, 3)
               FROM assembly_standard_time AS mei
               LEFT OUTER JOIN assembly_process_master AS master USING(pro_mark)
               WHERE mei.assy_no = head.assy_no AND mei.reg_no = head.reg_no AND master.pro_seg = '3'
              )               AS 外段取工数生産   -- 16
          FROM assembly_time_header AS head
          WHERE assy_no='{$assy_no}'
          ORDER BY
              setdate DESC, reg_no DESC
          LIMIT 5
        ";
        return $query;
    }
    
    ///// 必要工数(残り工数)から終了予定日時を取得
    ///// 後日 OVERLAPS の述語を使用したSQL文に変更予定 SELECT (TIME '083000', TIME '171500') OVERLAPS (TIME '171000', TIME '171000')
    private function getPlanEndTime($request, $need_time, &$str_date_time)
    {
        // 必要工数の取得
        $requireTime = Uround($need_time / $request->get('workerCount'), 3);
        // 工数(分)をINTERVAL型に変換
        $query = "
            SELECT INTERVAL '{$requireTime} minute'
        ";
        $this->getUniResult($query, $requireTime);
        // 現在日時を取得
        $query = "
            SELECT to_char(now() , 'YYYY/MM/DD HH24:MI:SS')
        ";
        $this->getUniResult($query, $now);
        $str_date_time = $now;
        $i = 0;
        while (1) {
                // 現在日時からの仮の終了時間を取得
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
            // 休み時間の加算
                // 午前のトイレ休憩
            if ($i == 0) {
                $str_overTime = date('Y/m/d 103000');   // 本日のトイレ休憩
                $end_overTime = date('Y/m/d 103500');   // (workingDayOffset(0)を使用しないのは休日出勤対応)
            } else {
                $str_overTime = workingDayOffset($i) . ' 103000';   // 指定稼働日のトイレ休憩
                $end_overTime = workingDayOffset($i) . ' 103500';
            }
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
                // 現在日時からの仮の終了時間を取得
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
                // 昼休み
            if ($i == 0) {
                $str_overTime = date('Y/m/d 120000');   // 本日の昼休み
                $end_overTime = date('Y/m/d 124500');   // (workingDayOffset(0)を使用しないのは休日出勤対応)
            } else {
                $str_overTime = workingDayOffset($i) . ' 120000';   // 指定稼働日の昼休み
                $end_overTime = workingDayOffset($i) . ' 124500';
            }
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
                // 現在日時からの仮の終了時間を取得
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
                // ３時休憩
            if ($i == 0) {
                $str_overTime = date('Y/m/d 150000');   // 本日の３時休憩
                $end_overTime = date('Y/m/d 151000');   // (workingDayOffset(0)を使用しないのは休日出勤対応)
            } else {
                $str_overTime = workingDayOffset($i) . ' 150000';   // 指定稼働日の３時休憩
                $end_overTime = workingDayOffset($i) . ' 151000';
            }
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
                // 現在日時からの仮の終了時間を取得
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $tempEndTime);
            // 稼働時間外の加算
            if ($i == 0) {
                $str_overTime = date('Y/m/d 171500');   // 本日の残業時間(workingDayOffset(0)を使用しないのは休日出勤対応)
            } else {
                $str_overTime = workingDayOffset($i) . ' 171500';   // 指定稼働日の残業時間
            }
            $end_overTime = workingDayOffset($i+1) . ' 083000';     // 次稼働日の開始時間に設定
            $query = "
                SELECT overlaps_time_diff(TIMESTAMP '{$now}', TIMESTAMP '{$tempEndTime}',
                TIMESTAMP '{$str_overTime}', TIMESTAMP '{$end_overTime}') + INTERVAL '{$requireTime}'
            ";
            $this->getUniResult($query, $requireTime);
            // 稼働時間外と休み時間を考慮した終了時間を取得
            $query = "
                SELECT to_char(TIMESTAMP '{$now}' + INTERVAL '{$requireTime}', 'YYYY/MM/DD HH24:MI:SS')
            ";
            $this->getUniResult($query, $planEndTime);
            // 終了時間が増えているかチェック
            $query = "
                SELECT (TIMESTAMP '{$tempEndTime}') < (TIMESTAMP '{$planEndTime}')
            ";
            $this->getUniResult($query, $end_check);
            if ($end_check == 't') {    // 必要工数が増えていれば繰返す
                $i++;
                continue;
            } else {
                break;
            }
        }
        return $planEndTime;
    }
    
} // Class AssemblyTimeShow_Model End

?>
