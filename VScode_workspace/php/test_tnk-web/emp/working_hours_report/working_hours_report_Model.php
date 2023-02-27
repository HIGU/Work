<?php
//////////////////////////////////////////////////////////////////////////////
// 就業週報の集計 結果 照会                                  MVC Model 部   //
// Copyright (C) 2008-2022     Norihisa.Ohya usoumu@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2008/09/22 Created   working_hours_report_Model.php                      //
// 2009/03/26 休業表示に対応                                                //
// 2017/03/28 最新の部課長に対応                                            //
// 2017/05/08 人員の表示を変更(部長以上総務課対応)                          //
// 2017/06/02 部課長説明 本格稼動                                           //
// 2017/06/07 商管（sid=19）は就業時間が違うため残業チェックを対応          //
// 2017/06/12 時間休の表示が間違っていたので修正                            //
//            カレンダが休日以外で、出退勤両方がなしで、不在理由がない場合  //
//            黄色になるよう変更                                            //
// 2017/06/13 残業時間のチェックに深夜残業を加味                            //
// 2017/06/22 エラーのみの照会を追加                                        //
//            任意研修と社員会の表示エラーと出勤MCの時間休エラーを解消      //
// 2017/06/29 職位別照会に対応（工場長依頼）                                //
// 2017/07/12 時間休時間と欠勤日数を表示(個別集計のみ 阿久津課長代理依頼)   //
// 2017/07/27 パート延長時間未打刻もエラー表示                              //
// 2017/08/02 中抜け対応の為、working_hours_report_data_newにDBを変更       //
//            24番目に外出MCを追加                                          //
// 2017/09/13 日東工器部門(sid=95)を除外する様、修正                        //
// 2018/03/30 萩野さんを技術課、安田さんをL組立課へ変更                     //
// 2018/09/26 並び順にコードテーブルの経理コードを追加                      //
// 2019/02/01 4/1の人事異動分を追加                                         //
// 2020/04/01 4/1の人事異動分を追加                                         //
// 2021/04/01 4/1の人事異動分を追加                                         //
// 2022/04/01 4/1の人事異動分を追加                                         //
// 2022/05/17 エラーチェックに早出残業を加味するよう修正（商管以外）        //
// 2022/06/02 対象部門に【残業（延長なし）】項目を追加                 和氣 //
//////////////////////////////////////////////////////////////////////////////
//最終的には \\Fs1\総務課専用\人事関係\月次報告 統計資料７期～９期.xls を作る
// ini_set('error_reporting', E_STRICT);               // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');                     // Error 表示 ON debug 用 リリース後コメント

require_once ('../../daoInterfaceClass.php');          // TNK 全共通 DAOインターフェースクラス


/*****************************************************************************************
*       MVCのModel部 クラス定義 daoInterfaceClass(base class) 基底クラスを拡張           *
*****************************************************************************************/
class WorkingHoursReport_Model extends daoInterfaceClass
{
    ////// Private properties
    private $where;                                    // 共用 SQLのWHERE句
    private $last_avail_pcs;                           // 最終有効数(最終予定在庫数)
    
    ////// public properties
    // public  $graph;                                 // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        // 基本WHERE区の設定
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
    
    ////// 対象年月のHTML <select> option の出力
    public function getTargetDateYMvalues($request)
    {
        // 初期化
        $option   = "\n";
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($request->get('targetDateYM') == $yyyymm) {
            $option .= "<option value='{$yyyymm}' selected>{$yyyy}年{$mm}月</option>\n";
        } else {
            $option .= "<option value='{$yyyymm}'>{$yyyy}年{$mm}月</option>\n";
        }
        while (1) {
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
            if ($yyyymm <= 201604)
                break;
        }
        return $option;
    }
    
    ////// 対象部門のHTML <select> option の出力
    public function getTargetSectionvalues($request)
    {
        // 初期化
        $option = "\n";
        // 管理者用
        if (getCheckAuthority(28)) {
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    //if (trim($res[$i]['section_name']) != '製造部') {
                        //if (trim($res[$i]['section_name']) != '管理部') {
                            //if (trim($res[$i]['section_name']) != '生産部') {
                                //if (trim($res[$i]['section_name']) != '栃木日東工器') {
                                    if($request->get('targetSection') == $res[$i]["sid"]) {
                                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                                    } else {
                                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                                    }
                                //}
                            //}
                        //}
                    //}
                }
                if($request->get('targetSection') == '-4') {
                    $option .= "<option value='-4' selected>残業</option>\n";
                } else {
                    $option .= "<option value='-4'>残業</option>\n";
                }
                //if ($_SESSION['User_ID'] == '300144') {
                if($request->get('targetSection') == '-5') {
                    $option .= "<option value='-5' selected>エラーのみ</option>\n";
                } else {
                    $option .= "<option value='-5'>エラーのみ</option>\n";
                }
                //}
                if ($_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '300055') {
                if($request->get('targetSection') == '-6') {
                    $option .= "<option value='-6' selected>残業（延長除く）</option>\n";
                } else {
                    $option .= "<option value='-6'>残業（延長除く）</option>\n";
                }
                }
            }
        } else if(getCheckAuthority(29)) {    // 工場長、副工場長は全てを閲覧できる
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    //if (trim($res[$i]['section_name']) != '製造部') {                    // 製造１課に統合の為
                        //if (trim($res[$i]['section_name']) != '管理部') {                // 各確認部署に移動させる
                            //if (trim($res[$i]['section_name']) != '生産部') {            // 組立１課に表示の為
                                //if (trim($res[$i]['section_name']) != '栃木日東工器') {  // 社長のみ
                                    if($request->get('targetSection') == $res[$i]["sid"]) {
                                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                                    } else {
                                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                                    }
                                //}
                            //}
                        //}
                    //}
                }
            }
        } else if(getCheckAuthority(42)) {    // 技術部は技術部のみ閲覧できる
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='38' or sid='18' or sid='4')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($request->get('targetSection') == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(43)) {    // 生産部は生産部のみ閲覧できる
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='8' or sid='32' or sid='2' or sid='3')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($request->get('targetSection') == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(55)) {    // 製造部は製造部のみ閲覧できる
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='17' or sid='34' or sid='35')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($request->get('targetSection') == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else {
        // 自部門のみ照会 各課の課長の社員番号を入れる
            if ($_SESSION['User_ID'] == '017370' || $_SESSION['User_ID'] == '300349') {    // 商品管理課   山口課長 村上
                $sid=19;    
            } else if ($_SESSION['User_ID'] == '012980' || $_SESSION['User_ID'] == '300098') {    // 品証課   岩本部長 薄井課長代理
                $sid=18;
            } else if ($_SESSION['User_ID'] == '018040') {    // 製造１課 阿久津課長
                $sid=34;
            } else if ($_SESSION['User_ID'] == '015202') {    // 製造２課 高橋課長
                $sid=35;
            } else if ($_SESSION['User_ID'] == '016713' || $_SESSION['User_ID'] == '016080') {    // 生管課   中山部長 吉成課長
                $sid=32;
            } else if ($_SESSION['User_ID'] == '017850' || $_SESSION['User_ID'] == '300055') {    // 総務課   上野部長 川崎課長
                $sid=5;
            } else if ($_SESSION['User_ID'] == '017728') {    // リニア組立課  安田課長
                $sid=3;
            } else if ($_SESSION['User_ID'] == '017507') {    // カプラ組立課 小山課長
                $sid=2;
            } else if ($_SESSION['User_ID'] == '014524') {    // 技術課 萩野部長代理
                $sid=4;
            }
            $query="select * from section_master where sflg=1 and sid={$sid} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                $option .= "<option value='{$res[0]['sid']}' selected>" . trim($res[0]['section_name']). "</option>\n";
            }
        }
        return $option;
    }
    // 週報確認一覧用 部門名の取得
    public function getTargetSectionConfirm()
    {
        // 初期化
        $res=array();
        $section_name=array();
        $section_count = 0;
        // 管理者用
        if (getCheckAuthority(28)) {
            $query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>95 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if (trim($res[$i]['section_name']) != '製造部') {
                        if (trim($res[$i]['section_name']) != '管理部') {
                            if (trim($res[$i]['section_name']) != '生産部') {
                                if (trim($res[$i]['section_name']) != '栃木日東工器') {
                                    $section_name[$section_count][0] = $res[$i]['section_name'];
                                    $section_name[$section_count][1] = $res[$i]['sid'];
                                    $section_count += 1;
                                }
                            }
                        }
                    }
                }
                //$section_name[$section_count][0] = '８級職以上';
                //$section_name[$section_count][1] = -3;
            }
        } else {
            // 自部門のみ照会 各課の課長の社員番号を入れる
            if ($_SESSION['User_ID'] == '014524') {           // 技術課   萩野部長代理
                $sid=4;
            } else if ($_SESSION['User_ID'] == '017370' || $_SESSION['User_ID'] == '300349') {    // 商品管理課   山口課長 村上
                $sid=19;    
            } else if ($_SESSION['User_ID'] == '012980' || $_SESSION['User_ID'] == '300098') {    // 品証課   岩本部長 薄井課長代理
                $sid=18;
            } else if ($_SESSION['User_ID'] == '018040') {    // 製造１課 阿久津課長
                $sid=34;
            } else if ($_SESSION['User_ID'] == '015202') {    // 製造２課 高橋課長
                $sid=35;
            } else if ($_SESSION['User_ID'] == '016713' || $_SESSION['User_ID'] == '016080') {    // 生管課   中山部長 吉成課長
                $sid=32;
            } else if ($_SESSION['User_ID'] == '017850' || $_SESSION['User_ID'] == '300055') {    // 総務課   上野部長 川崎課長
                $sid=5;
            } else if ($_SESSION['User_ID'] == '017728') {    // リニア組立課 安田課長
                $sid=3;
            } else if ($_SESSION['User_ID'] == '017507') {    // カプラ組立課 小山課長
                $sid=2;
            }
            $query="select * from section_master where sflg=1 and sid={$sid} order by sid asc";
            if($rows=getResult($query,$res)){
                $section_name[$section_count][0] = $res[0]['section_name'];
                $section_name[$section_count][1] = $res[0]['sid'];
            }
        }
        return $section_name;
    }
    ////// 確定内容のHTML <select> option の出力
    public function getTargetConfirmvalues($request)
    {
        // 初期化
        $option = "\n";
        $uid              = $request->get('uid');
        $working_date     = $request->get('str_date'); 
        $query = sprintf("SELECT confirm_flg FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $working_date);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり <option>選択
            if ($res_chk[0] == 1) {
                $option .= "<option value='1' selected>問題なし</option>\n";
                $option .= "<option value='2'>届出提出済</option>\n";
                $option .= "<option value='3'>届出依頼済</option>\n";
            } elseif ($res_chk[0] == 2) {
                $option .= "<option value='1'>問題なし</option>\n";
                $option .= "<option value='2' selected>届出提出済</option>\n";
                $option .= "<option value='3'>届出依頼済</option>\n";
            } elseif ($res_chk[0] == 3) {
                $option .= "<option value='1'>問題なし</option>\n";
                $option .= "<option value='2'>届出提出済</option>\n";
                $option .= "<option value='3' selected>届出依頼済</option>\n";
            }
        } else {                                    // 登録なし<option>未選択
            $option .= "<option value='1' selected>問題なし</option>\n";
            $option .= "<option value='2'>届出提出済</option>\n";
            $option .= "<option value='3'>届出依頼済</option>\n";
        }
        return $option;
    }
    ////// MVC の Model 部の結果 表示用のデータ取得
    ////// List部    データの明細 一覧表
    public function outViewListHTML($request, $menu, $check_flg)
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
        $file_name = "list/working_hours_report_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // fileを全てrwモードにする
        *****************/
        
                /***** 本文を作成 *****/
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu, $check_flg);        // 明細表示
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/working_hours_report_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // fileを全てrwモードにする
        
        /***** フッター部を作成 *****/
        // 固定のHTMLソースを取得
        $footHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $footHTML .= $this->getViewHTMLfooter($request);
        // 固定のHTMLソースを取得
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/working_hours_report_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // fileを全てrwモードにする
        return ;
    }
    public function outViewCorrectListHTML($request, $menu, $endflg)
    {    
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewCorrectHTMLbody($request, $menu, $endflg);        // 明細表示
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/working_hours_report_ViewCorrectList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // fileを全てrwモードにする
        return ;
    }
    public function outViewConfirmListHTML($request, $menu)
    {    
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewConfirmHTMLbody($request, $menu);        // 明細表示
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/working_hours_report_ViewConfirmList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // fileを全てrwモードにする
        return ;
    }
    
    public function outViewMailListHTML($request, $menu)
    {    
        // 固定のHTMLソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLソースを取得
        $listHTML .= $this->getViewMailHTMLbody($request, $menu);        // 明細表示
        // 固定のHTMLソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/working_hours_report_ViewConfirmList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);                                         // fileを全てrwモードにする
        return ;
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////// リクエストによりSQL文の基本WHERE区を設定
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
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   就業週報照会の明細データ作成
    private function getViewHTMLbody($request, $menu, $chek_flg)
    {
        $uid = array();                                                     // 社員番号
        $res = array();
        $s_name = array();                                                  // 部門名
        // 初期化
        $listTable  = '';
        if ($request->get('uid') != '') {                                   // 社員番号が入力されていれば取得
            $uid[0]     = $request->get('uid');
            $s_name[0]  = $this->getSectionNameOne($uid[0]);                // 社員No.より部門名を取得
        } else {
            $query = $this->getSectionUser($request->get('targetSection'), $request->get('targetPosition'), $request->get('targetDateStr'), $request->get('targetDateEnd')); // 選択部門より所属社員を取得
            if ($rows=getResult($query,$res)) {
                for ($i=0; $i<$rows; $i++) {
                    $uid[$i]   = $res[$i]['uid'];
                    $sid_t[$i] = $res[$i]['sid'];
                }
                // 今はいないのでコメント化
                /*
                if ($request->get('targetSection') == 4) {                  // 管理部所属社員の対応
                    $uid[$i] = '000817';                                    // 000817=管理部 小林さん
                    $res[$i]['sid'] = 9;                                    // sidに管理部を追加
                    $rows = $rows + 1;                                      // sid追加の為$rowsも１追加
                }
                */
                $s_name = $this->getSectionName($rows,$res);                // 選択部門の部門コードより部門名を取得
            } else {
                $uid    = '------';
                $s_name ='----------';
            }
        }
        $uid_num = count($uid);
        for ($t=0; $t<$uid_num; $t++) {                                     // 出向者の社員番号の変換(TimeProデータ)
            if ($uid[$t] == '014737') {                                     // 出向者の追加変更あれば下の戻しも同時に変更
                $uid[$t] = '914737';                                        // 014737=総務課 桝さん
            } else if ($uid[$t] == '020273') {                              // 020273=技術課 佐藤さん
                $uid[$t] = '920273';
            }
        }
        $today_ym   = date('Ymd');
        $listTable .= "<CENTER>\n";
        $listTable .= "<font size='4'><B>就業　週報<B></font>\n";
        $listTable .= "<HR width='300' color='black' noshade>\n";
        $listTable .= "</CENTER>\n";
        for ($t=0; $t<$uid_num; $t++) {
            if (substr($uid[$t], 0, 3) == '990') {                          // 横川作業員は除外
                continue;
            }
            if ($request->get('targetSection') == '-4' || $request->get('targetSection') == '-6') {   // 残業有り
                $working_data = array();
                $working_data = $this->getTimeProDataOver($request, $uid, $sid_t, $t);      // タイムプロデータの取得・変換
                if ($uid[$t] == '914737') {                                     // 出向者の社員番号の戻し
                    $uid[$t] = '014737';
                } else if ($uid[$t] == '920273') {
                    $uid[$t] = '020273';
                }
                $work_num    = $request->get('work_num');
                $howork_num  = $request->get('howork_num');
                if ($working_data) {
                    $listTable .= "<BR><CENTER>\n";
                    $listTable .= "<U><font size='2'>社員No.　". $uid[$t] ."　　".  $this->getUserName($uid[$t]) ."　　所属　". $s_name[$t] ."　　処理期間：　". format_date($request->get('targetDateStr')) ."　～　". format_date($request->get('targetDateEnd')) ."</U>\n";
                    $listTable .= "</CENTER>\n";
                    $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
                    $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                    $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='2'>\n";
                    if ($request->get('rows') <= 0) {
                        $listTable .= "    <tr>\n";
                        $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>データがありません。</td>\n";
                        $listTable .= "    </tr>\n";
                        $listTable .= "</table>\n";
                        $listTable .= "    </td></tr>\n";
                        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
                    } else {
                        if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                            $listTable .= "    <tr>\n";                                 // 明細データの表示
                            $listTable .= "        <th class='winbox'>日付</th>\n";
                            $listTable .= "        <th class='winbox'>曜日</th>\n";
                            $listTable .= "        <th class='winbox'>カレ<BR>ンダ</th>\n";
                            $listTable .= "        <th class='winbox'>不在</th>\n";
                            $listTable .= "        <th class='winbox'>出勤<BR>時刻</th>\n";
                            $listTable .= "        <th class='winbox'>退勤<BR>時刻</th>\n";
                            $listTable .= "        <th class='winbox'>所定<BR>時間</th>\n";
                            $listTable .= "        <th class='winbox'>延長<BR>時間</th>\n";
                            $listTable .= "        <th class='winbox'>早出<BR>残業</th>\n";
                            $listTable .= "        <th class='winbox'>深夜<BR>残業</th>\n";
                            $listTable .= "        <th class='winbox'>休出<BR>時間</th>\n";
                            $listTable .= "        <th class='winbox'>休出<BR>残業</th>\n";
                            $listTable .= "        <th class='winbox'>休出<BR>深夜</th>\n";
                            $listTable .= "        <th class='winbox'>法定<BR>時間</th>\n";
                            $listTable .= "        <th class='winbox'>法定<BR>残業</th>\n";
                            $listTable .= "        <th class='winbox'>遅刻<BR>早退</th>\n";
                            //$listTable .= "        <th class='winbox'>週報<BR>確認</th>\n";
                            $listTable .= "    </tr>\n";
                            for ($r=0; $r<$request->get('rows'); $r++) {                                // レコード数分繰返し
                                $listTable .= "<tr>\n";
                                for ($i=0; $i<$request->get('num'); $i++) {
                                    switch ($i) {
                                        case 1:                                         // 日付
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][3] == '休日') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '法休') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '休業') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][3] == '休日') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '法休') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '休業') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                    break;
                                                }
                                            }
                                        case 2:                                         // 曜日
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][3] == '休日') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '法休') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '休業') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][3] == '休日') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '法休') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][3] == '休業') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            }
                                        case 3:                                         // カレンダー
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '休日') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == '法休') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == '休業') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][$i] == '休日') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == '法休') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } elseif ($working_data[$r][$i] == '休業') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            }
                                        case 4:                                         // 不在理由
                                            if ($working_data[$r][3] == '休日') {
                                                if ($working_data[$r][23] == '済') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } elseif ($working_data[$r][3] == '法休') {
                                                if ($working_data[$r][23] == '済') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } elseif ($working_data[$r][3] == '休業') {
                                                if ($working_data[$r][23] == '済') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][5] == '0000') {
                                                        if ($working_data[$r][6] == '0000') {
                                                            if ($working_data[$r][$i] == '　') {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                                break;
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                                break;
                                                            }
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                            break;
                                                        }
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                    break;
                                                }
                                            }
                                            /*
                                            if ($working_data[$r][23] == '済') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                            */
                                        case 5:                                         // 出勤時刻
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][6] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $work_num += 1;                         // 出勤時刻が打刻されていれば出勤日数＋１
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][6] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $work_num += 1;                         // 出勤時刻が打刻されていれば出勤日数＋１
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 6:                                         // 退勤時刻
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][5] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '0000') {
                                                        if ($working_data[$r][5] == '0000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        }
                                                } else {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 7:                                         // 所定時間
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i] % 60;
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    //$minutes = $working_data[$r][$i]%60;
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i] % 60;
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    //$minutes = $working_data[$r][$i]%60;
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 8:                                         // 延長時間
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                                $res_chk = array();
                                                if ( getResult($query, $res_chk) > 0 ) {    // パート 延長チェック
                                                    if ($working_data[$r][6] >= '1645') {   // 延長30分以上 チェック
                                                        if ($working_data[$r][$i] == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                           $t_temp = $working_data[$r][$i];
                                                            // 延長時間計算（実時間）
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 16:15:00');
                                                            $endSec   = strtotime($end);
                                                                
                                                            $r_temp = ($endSec - $startSec)/60;
                                                            
                                                            if($r_temp>=60) {   // 延長は１時間までなので調整
                                                                $r_temp = 60;
                                                            } elseif($r_temp>=30) {
                                                                $r_temp = 30;
                                                            }
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                            
                                                            // 延長時間計算（申告分）
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            if ($hour_r == $hour) {
                                                            //if ($hour_r == $hour_s) {
                                                                if ($minutes_r == $minutes) {
                                                                //if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                }
                                                            } else {
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                    //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                    } else {                                // 延長無し チェック無し
                                                        if ($working_data[$r][$i] == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($working_data[$r][$i] / 60);
                                                            $minutes = $working_data[$r][$i]%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                } else {                                    // パート以外 延長チェック無し
                                                    if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($working_data[$r][$i] / 60);
                                                        $minutes = $working_data[$r][$i]%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                }
                                                break;
                                            }
                                        case 9:                                         // 早出残業
                                            if ($working_data[$r][23] == '済') {
                                                $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                                $s_temp = $t_temp + $working_data[$r][$i+2];    // 深夜残業加味
                                                $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                                $res_chk = array();
                                                if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                } elseif($sid_t[$t] == 19) {
                                                    if ($working_data[$r][6] >= '1830') {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // 残業時間計算（実時間）
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 18:00:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                        
                                                            // 早出計算
                                                            if ($working_data[$r][5] <= 800) {
                                                                $hour_temp = 0;
                                                               $deteObj = new DateTime($working_data[$r][5]);
                                                                $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                                if ($ceil_num2 == 60) {
                                                                    $ceil_num2 = 0;
                                                                    $hour_temp = 1;
                                                                }
                                                                $ceil_hour2 = $deteObj->format('H');
                                                                $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                                if ($ceil_hour2 < 10) {
                                                                    $ceil_hour2 = '0' . $ceil_hour2;
                                                                }
                                                                $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                                $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                                $startSec2 = strtotime('2017-05-17 8:30:00');
                                                                $endSec2   = strtotime($end2);
                                                            
                                                                $r_temp += ($startSec2 - $endSec2)/60;
                                                            }
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                            
                                                            // 残業時間計算（申告分）
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            
                                                            // 深夜残業加味
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                } else {
                                                    if ($working_data[$r][6] >= '1800') {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // 残業時間計算（実時間）
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 17:30:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                        
                                                            // 早出計算
                                                            if ($working_data[$r][5] <= 800) {
                                                                $hour_temp = 0;
                                                               $deteObj = new DateTime($working_data[$r][5]);
                                                                $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                                if ($ceil_num2 == 60) {
                                                                    $ceil_num2 = 0;
                                                                    $hour_temp = 1;
                                                                }
                                                                $ceil_hour2 = $deteObj->format('H');
                                                                $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                                if ($ceil_hour2 < 10) {
                                                                    $ceil_hour2 = '0' . $ceil_hour2;
                                                                }
                                                                $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                                $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                                $startSec2 = strtotime('2017-05-17 8:30:00');
                                                                $endSec2   = strtotime($end2);
                                                            
                                                                $r_temp += ($startSec2 - $endSec2)/60;
                                                            }
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                            
                                                            // 残業時間計算（申告分）
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            // 深夜残業加味
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                                $s_temp = $t_temp + $working_data[$r][$i+2];    // 深夜残業加味
                                                $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                                $res_chk = array();
                                                if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                } elseif($sid_t[$t] == 19) {
                                                    if ($working_data[$r][6] >= '1830') {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // 残業時間計算（実時間）
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            $startSec = strtotime('2017-05-17 18:00:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                        
                                                            // 早出計算
                                                            if ($working_data[$r][5] <= 800) {
                                                                $hour_temp = 0;
                                                               $deteObj = new DateTime($working_data[$r][5]);
                                                                $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                                if ($ceil_num2 == 60) {
                                                                    $ceil_num2 = 0;
                                                                    $hour_temp = 1;
                                                                }
                                                                $ceil_hour2 = $deteObj->format('H');
                                                                $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                                if ($ceil_hour2 < 10) {
                                                                    $ceil_hour2 = '0' . $ceil_hour2;
                                                                }
                                                                $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                                $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                                $startSec2 = strtotime('2017-05-17 8:30:00');
                                                                $endSec2   = strtotime($end2);
                                                            
                                                                $r_temp += ($startSec2 - $endSec2)/60;
                                                            }
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                                
                                                            // 残業時間計算（申告分）
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            
                                                            // 深夜残業加味
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                } else {
                                                    if ($working_data[$r][6] >= '1800') {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                        } else {
                                                            // 残業時間計算（実時間）
                                                            $deteObj = new DateTime($working_data[$r][6]);
                                                            $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            $ceil_hour = $deteObj->format('H');
                                                            $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                            if ($have >= '0000') {
                                                                $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            } else {
                                                                $end = '2017-05-18 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                            }
                                                            $startSec = strtotime('2017-05-17 17:30:00');
                                                            $endSec   = strtotime($end);
                                                            
                                                            $r_temp = ($endSec - $startSec)/60;
                                                        
                                                            // 早出計算
                                                            if ($working_data[$r][5] <= 800) {
                                                                $hour_temp = 0;
                                                               $deteObj = new DateTime($working_data[$r][5]);
                                                                $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                                if ($ceil_num2 == 60) {
                                                                    $ceil_num2 = 0;
                                                                    $hour_temp = 1;
                                                                }
                                                                $ceil_hour2 = $deteObj->format('H');
                                                                $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                                if ($ceil_hour2 < 10) {
                                                                    $ceil_hour2 = '0' . $ceil_hour2;
                                                                }
                                                                $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                                $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                                $startSec2 = strtotime('2017-05-17 8:30:00');
                                                                $endSec2   = strtotime($end2);
                                                            
                                                                $r_temp += ($startSec2 - $endSec2)/60;
                                                            }
                                                            
                                                            $hour_r = floor($r_temp / 60);
                                                            $minutes_r = $r_temp%60;
                                                            if($hour_r == 0) {
                                                                $hour_r = '0';
                                                            }
                                                            if($minutes_r == 0) {
                                                                $minutes_r = '00';
                                                            } else if($minutes_r < 10) {
                                                                $minutes_r = '0' . $minutes_r;
                                                            }
                                                                
                                                            // 残業時間計算（申告分）
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                            // 深夜残業加味
                                                            $hour_s = floor($s_temp / 60);
                                                            $minutes_s = $s_temp%60;
                                                            if($hour_s == 0) {
                                                                $hour_s = '0';
                                                            }
                                                            if($minutes_s == 0) {
                                                                $minutes_s = '00';
                                                            } else if($minutes_s < 10) {
                                                                $minutes_s = '0' . $minutes_s;
                                                            }
                                                            
                                                            //if ($hour_r == $hour) {
                                                            if ($hour_r == $hour_s) {
                                                                //if ($minutes_r == $minutes) {
                                                                if ($minutes_r == $minutes_s) {
                                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                }
                                                            } else {
                                                                    $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        }
                                                        break;
                                                    } else {
                                                        if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                        } elseif ($t_temp == '000000') {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                        } else {
                                                            $hour = floor($t_temp / 60);
                                                            $minutes = $t_temp%60;
                                                            if($hour == 0) {
                                                                $hour = '0';
                                                            }
                                                            if($minutes == 0) {
                                                                $minutes = '00';
                                                            } else if($minutes < 10) {
                                                                $minutes = '0' . $minutes;
                                                            }
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        case 11:                                        // 深夜残業
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 12:                                        // 休出時間
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $howork_num += 1;                       // 休日に打刻があれば休出日数＋１
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $howork_num += 1;                       // 休日に打刻があれば休出日数＋１
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 13:                                        // 休出残業
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 14:                                        // 休出深夜
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                            }
                                        case 15:                                        // 法定時間
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    $howork_num += 1;                       // 法定時間に打刻があれば休出日数＋１
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    $howork_num += 1;                       // 法定時間に打刻があれば休出日数＋１
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 16:                                        // 法定残業
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 17:                                        // 遅刻早退
                                            if ($working_data[$r][23] == '済') {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } else {
                                                if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            }
                                        case 23:                                        // 週報確認
                                            /*
                                            if ($working_data[$r][$i] == '済') {
                                                $listTable .= "<td class='winbox' align='center' bgcolor='white' nowrap>" . $working_data[$r][$i] ."</td>\n";
                                            } else {
                                                $listTable .= "<td class='winbox' align='center' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                            }
                                            */
                                            break;
                                        default:                                          
                                            break;
                                    }
                                }
                                
                                $listTable .= "    </tr>\n";
                            }
                        } else {
                            for ($r=0; $r<$request->get('rows'); $r++) {                                        // レコード数分繰返し
                                for ($i=0; $i<$request->get('num'); $i++) {
                                    switch ($i) {
                                        case 5:
                                            if ($working_data[$r][$i] != '0000') {
                                                $work_num += 1;                                 // 出勤時刻が打刻されていれば出勤日数＋１
                                            }
                                            break;
                                        case 12:
                                            if ($working_data[$r][$i] != '000000') {
                                                $howork_num += 1;                               // 休出時間があれば休出日数＋１
                                            }
                                            break;
                                        case 15:
                                            if ($working_data[$r][$i] != '000000') {
                                                $howork_num += 1;                               // 法定時間があれば休出日数＋１
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }
                        // 集計データ部分作成
                        $listTable = $this->getViewHTMLTotal($request, $listTable, $working_data, $work_num, $howork_num, $uid[$t], $chek_flg);
                    }
                }
            } else {        // 残業以外
                $working_data = array();
                $working_data = $this->getTimeProData($request, $uid, $t);      // タイムプロデータの取得・変換
                if ($uid[$t] == '914737') {                                     // 出向者の社員番号の戻し
                    $uid[$t] = '014737';
                } else if ($uid[$t] == '920273') {
                    $uid[$t] = '020273';
                }
                $work_num    = $request->get('work_num');
                $howork_num  = $request->get('howork_num');
                if ($request->get('targetSection') == '-5') {   // エラーのみ表示の場合
                    if ($this->getErrorCheck($request, $uid, $t, $working_data, $sid_t)) {
                        continue;                                                   // エラーチェックにかからなければ飛ばす
                    }
                }
                $listTable .= "<BR><CENTER>\n";
                $listTable .= "<U><font size='2'>社員No.　". $uid[$t] ."　　".  $this->getUserName($uid[$t]) ."　　所属　". $s_name[$t] ."　　処理期間：　". format_date($request->get('targetDateStr')) ."　～　". format_date($request->get('targetDateEnd')) ."</U>\n";
                $listTable .= "</CENTER>\n";
                $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
                $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='2'>\n";
                if ($request->get('rows') <= 0) {
                    $listTable .= "    <tr>\n";
                    $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>データがありません。</td>\n";
                    $listTable .= "    </tr>\n";
                    $listTable .= "</table>\n";
                    $listTable .= "    </td></tr>\n";
                    $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
                } else {
                    if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                        $listTable .= "    <tr>\n";                                 // 明細データの表示
                        $listTable .= "        <th class='winbox'>日付</th>\n";
                        $listTable .= "        <th class='winbox'>曜日</th>\n";
                        $listTable .= "        <th class='winbox'>カレ<BR>ンダ</th>\n";
                        $listTable .= "        <th class='winbox'>不在</th>\n";
                        $listTable .= "        <th class='winbox'>出勤<BR>時刻</th>\n";
                        $listTable .= "        <th class='winbox'>退勤<BR>時刻</th>\n";
                        $listTable .= "        <th class='winbox'>所定<BR>時間</th>\n";
                        $listTable .= "        <th class='winbox'>延長<BR>時間</th>\n";
                        $listTable .= "        <th class='winbox'>早出<BR>残業</th>\n";
                        $listTable .= "        <th class='winbox'>深夜<BR>残業</th>\n";
                        $listTable .= "        <th class='winbox'>休出<BR>時間</th>\n";
                        $listTable .= "        <th class='winbox'>休出<BR>残業</th>\n";
                        $listTable .= "        <th class='winbox'>休出<BR>深夜</th>\n";
                        $listTable .= "        <th class='winbox'>法定<BR>時間</th>\n";
                        $listTable .= "        <th class='winbox'>法定<BR>残業</th>\n";
                        $listTable .= "        <th class='winbox'>遅刻<BR>早退</th>\n";
                        //$listTable .= "        <th class='winbox'>週報<BR>確認</th>\n";
                        $listTable .= "    </tr>\n";
                        for ($r=0; $r<$request->get('rows'); $r++) {        // レコード数分繰返し
                            if ($request->get('targetSection') == '-5') {   // エラーのみ表示の場合
                                $error_flg = '';                                // エラーフラグ初期化
                                if ($working_data[$r][1] != $today_ym) {        // 当日以外
                                    if ($working_data[$r][5] == '0000') {           // 出勤打刻なし
                                        if ($working_data[$r][6] == '0000') {       // 退勤打刻なし
                                            if ($working_data[$r][3] == '休日' || $working_data[$r][3] == '法休' || $working_data[$r][3] == '休業') {
                                                continue;                           // 休日であれば表示しない
                                            } else {                                // 休日では無い時
                                                if ($working_data[$r][4] == '　') { // 不在理由が空欄
                                                } else {
                                                    continue;                       // 空欄でなければ表示しない
                                                }
                                            }
                                        } else {    // 出勤打刻がなく、退勤があればエラーの為、表示
                                            
                                        }
                                    } else {        // 出勤打刻あり
                                        if ($working_data[$r][6] == '0000') {
                                                    // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                                        } else {    // 出勤打刻ありで、退勤打刻ありの場合は延長残業チェック
                                            // 延長チェック
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // パート 延長チェック
                                                if ($working_data[$r][6] >= '1645') {   // 延長30分以上 チェック
                                                    if ($working_data[$r][8] == '000000') {    // 延長打刻なしなのでエラー表示
                                                        $error_flg = '1';
                                                    } else {
                                                        $t_temp = $working_data[$r][8];
                                                        // 延長時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 16:15:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        if($r_temp>=60) {   // 延長は１時間までなので調整
                                                            $r_temp = 60;
                                                        } elseif($r_temp>=30) {
                                                            $r_temp = 30;
                                                        }
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 延長時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        if ($hour_r == $hour) {
                                                            if ($minutes_r == $minutes) {   // 時間と分が一致 エラーなしなので非表示
                                                                //continue;
                                                            } else {    // 分が不一致なのでエラー表示
                                                                $error_flg = '1';
                                                            }
                                                        } else {    // 時間が不一致なのでエラー表示
                                                            $error_flg = '1';
                                                        }
                                                    }
                                                } else {                                // 延長無し チェック無し
                                                    //continue;
                                                }
                                            } else {                                    // パート以外 延長チェック無し
                                                //continue;
                                            }
                                            // 残業チェック
                                            $t_temp = $working_data[$r][9] + $working_data[$r][10];
                                            $s_temp = $t_temp + $working_data[$r][11];    // 深夜残業加味
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し 非表示
                                                continue;
                                            } elseif($sid_t[$t] == 19) {                // 商管の場合
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                            continue;                       // 18:30以降でも社員会や任意研修は非表示
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:30以降で残業の打刻がなければ表示
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                                    continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                                                }
                                                            } else {
                                                                // 時間と分が合わないのでエラーの為、表示
                                                            }
                                                        } else {
                                                            // 時間が合わないのでエラーの為、表示
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                        continue;                           // 18:30前は残業ではない為、非表示
                                                    }
                                                }
                                            } else {                                    // 商管以外の一般社員
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                            continue;                       // 18:00以降でも社員会や任意研修は非表示
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:00以降で残業の打刻がなければ表示
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                                    continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                                                }
                                                            } else {
                                                                // 時間と分が合わないのでエラーの為、表示
                                                            }
                                                        } else {
                                                            // 時間が合わないのでエラーの為、表示
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                        continue;                           // 18:00前は残業ではない為、非表示
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {                    // 当日の場合
                                    if ($working_data[$r][5] == '0000') {           // 出勤打刻なし
                                        if ($working_data[$r][6] == '0000') {       // 退勤打刻なし
                                            if ($working_data[$r][3] == '休日' || $working_data[$r][3] == '法休' || $working_data[$r][3] == '休業') {
                                                continue;                           // 休日であれば表示しない
                                            } else {                                // 休日では無い時
                                                if ($working_data[$r][4] == '　') { // 不在理由が空欄
                                                } else {
                                                    continue;                       // 空欄でなければ表示しない
                                                }
                                            }
                                        } else {    // 出勤打刻がなく、退勤があればエラーの為、表示
                                            
                                        }
                                    } else {                                        // 出勤打刻あり
                                        if ($working_data[$r][6] == '0000') {       // 退勤打刻なし
                                            continue;                               // 当日は出退勤打刻時まで飛ばす
                                        } else {    // 出勤打刻があり、退勤もすでにあればエラーチェック
                                            // 延長チェック
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // パート 延長チェック
                                                if ($working_data[$r][6] >= '1645') {   // 延長30分以上 チェック
                                                    if ($working_data[$r][8] == '000000') {    // 延長打刻なしなのでエラー表示
                                                        $error_flg = '1';
                                                    } else {
                                                        $t_temp = $working_data[$r][8];
                                                        // 延長時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 16:15:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        if($r_temp>=60) {   // 延長は１時間までなので調整
                                                            $r_temp = 60;
                                                        } elseif($r_temp>=30) {
                                                            $r_temp = 30;
                                                        }
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 延長時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        if ($hour_r == $hour) {
                                                            if ($minutes_r == $minutes) {   // 時間と分が一致 エラーなしなので非表示
                                                                //continue;
                                                            } else {    // 分が不一致なのでエラー表示
                                                                $error_flg = '1';
                                                            }
                                                        } else {    // 時間が不一致なのでエラー表示
                                                            $error_flg = '1';
                                                        }
                                                    }
                                                } else {                                // 延長無し チェック無し
                                                    //continue;
                                                }
                                            } else {                                    // パート以外 延長チェック無し
                                                //continue;
                                            }
                                            // 残業チェック
                                            $t_temp = $working_data[$r][9] + $working_data[$r][10];
                                            $s_temp = $t_temp + $working_data[$r][11];    // 深夜残業加味
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し 非表示
                                                continue;
                                            } elseif($sid_t[$t] == 19) {                // 商管の場合
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                            continue;                       // 18:30以降でも社員会や任意研修は非表示
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:30以降で残業の打刻がなければ表示
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                                    continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                                                }
                                                            } else {
                                                                // 時間と分が合わないのでエラーの為、表示
                                                            }
                                                        } else {
                                                            // 時間が合わないのでエラーの為、表示
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                        continue;                           // 18:30前は残業ではない為、非表示
                                                    }
                                                }
                                            } else {                                    // 商管以外の一般社員
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                            continue;                       // 18:00以降でも社員会や任意研修は非表示
                                                        }
                                                    } elseif ($t_temp == '000000') {    
                                                        // 18:00以降で残業の打刻がなければ表示
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes_s) {
                                                                if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                                    continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                                                }
                                                            } else {
                                                                // 時間と分が合わないのでエラーの為、表示
                                                            }
                                                        } else {
                                                            // 時間が合わないのでエラーの為、表示
                                                        }
                                                    }
                                                } else {
                                                    if ($error_flg == '') {             // 延長エラーOFFの場合は非表示
                                                        continue;                           // 18:00前は残業ではない為、非表示
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            // エラーのみ表示以外
                            $listTable .= "<tr>\n";
                            for ($i=0; $i<$request->get('num'); $i++) {
                                switch ($i) {
                                    case 1:                                         // 日付
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][3] == '休日') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '法休') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '休業') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][3] == '休日') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '法休') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '休業') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 4, 2) . "/". substr($working_data[$r][$i], 6, 2) ."</td>\n";
                                                break;
                                            }
                                        }
                                    case 2:                                         // 曜日
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][3] == '休日') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '法休') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '休業') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][3] == '休日') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '法休') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][3] == '休業') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        }
                                    case 3:                                         // カレンダー
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '休日') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == '法休') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == '休業') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][$i] == '休日') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == '法休') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } elseif ($working_data[$r][$i] == '休業') {
                                                $listTable .= "<td class='winbox' align='right' nowrap><font color='blue'>". $working_data[$r][$i] ."</font></td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        }
                                    case 4:                                         // 不在理由
                                        if ($working_data[$r][3] == '休日') {
                                            if ($working_data[$r][23] == '済') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } elseif ($working_data[$r][3] == '法休') {
                                            if ($working_data[$r][23] == '済') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } elseif ($working_data[$r][3] == '休業') {
                                            if ($working_data[$r][23] == '済') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        } else {
                                            if ($working_data[$r][5] == '0000') {
                                                    if ($working_data[$r][6] == '0000') {
                                                        if ($working_data[$r][$i] == '　') {
                                                            $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                            break;
                                                        } else {
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                            break;
                                                        }
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                        break;
                                                    }
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                                break;
                                            }
                                        }
                                        /*
                                        if ($working_data[$r][23] == '済') {
                                            $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $working_data[$r][$i] ."</td>\n";
                                            break;
                                        } else {
                                            $listTable .= "<td class='winbox' align='right' nowrap>". $working_data[$r][$i] ."</td>\n";
                                            break;
                                        }
                                        */
                                    case 5:                                         // 出勤時刻
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][6] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $work_num += 1;                         // 出勤時刻が打刻されていれば出勤日数＋１
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][6] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $work_num += 1;                         // 出勤時刻が打刻されていれば出勤日数＋１
                                                $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 6:                                         // 退勤時刻
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][5] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '0000') {
                                                    if ($working_data[$r][5] == '0000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    }
                                            } else {
                                                $listTable .= "<td class='winbox' align='right' nowrap>". substr($working_data[$r][$i], 0, 2) .":". substr($working_data[$r][$i], 2, 2) ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 7:                                         // 所定時間
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i] % 60;
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                //$minutes = $working_data[$r][$i]%60;
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i] % 60;
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                //$minutes = $working_data[$r][$i]%60;
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 8:                                         // 延長時間
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // パート 延長チェック
                                                if ($working_data[$r][6] >= '1645') {   // 延長30分以上 チェック
                                                    if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        $t_temp = $working_data[$r][$i];
                                                        // 延長時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 16:15:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        if($r_temp>=60) {   // 延長は１時間までなので調整
                                                            $r_temp = 60;
                                                        } elseif($r_temp>=30) {
                                                            $r_temp = 30;
                                                        }
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 延長時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        if ($hour_r == $hour) {
                                                        //if ($hour_r == $hour_s) {
                                                            if ($minutes_r == $minutes) {
                                                            //if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                            }
                                                        } else {
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour_r .":". $minutes_r ."</td>\n";
                                                                //$listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                } else {                                // 延長無し チェック無し
                                                    if ($working_data[$r][$i] == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($working_data[$r][$i] / 60);
                                                        $minutes = $working_data[$r][$i]%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                }
                                            } else {                                    // パート以外 延長チェック無し
                                                if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($working_data[$r][$i] / 60);
                                                    $minutes = $working_data[$r][$i]%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                            }
                                            break;
                                        }
                                    case 9:                                         // 早出残業
                                        if ($working_data[$r][23] == '済') {
                                            $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                            $s_temp = $t_temp + $working_data[$r][$i+2];    // 深夜残業加味
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し
                                                if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                } elseif ($t_temp == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($t_temp / 60);
                                                    $minutes = $t_temp%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } elseif($sid_t[$t] == 19) {
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        if ($have >= '0000') {
                                                             $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        } else {
                                                            $end = '2017-05-18 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        }
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                        
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            }
                                        } else {
                                            $t_temp = $working_data[$r][$i] + $working_data[$r][$i+1];
                                            $s_temp = $t_temp + $working_data[$r][$i+2];    // 深夜残業加味
                                            $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                                            $res_chk = array();
                                            if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し
                                                if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                } elseif ($t_temp == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                } else {
                                                    $hour = floor($t_temp / 60);
                                                    $minutes = $t_temp%60;
                                                    if($hour == 0) {
                                                        $hour = '0';
                                                    }
                                                    if($minutes == 0) {
                                                        $minutes = '00';
                                                    } else if($minutes < 10) {
                                                        $minutes = '0' . $minutes;
                                                    }
                                                    $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                }
                                                break;
                                            } elseif($sid_t[$t] == 19) {
                                                if ($working_data[$r][6] >= '1830') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } else if ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        $startSec = strtotime('2017-05-17 18:00:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                            
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            } else {
                                                if ($working_data[$r][6] >= '1800') {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } else if ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>----</td>\n";
                                                    } else {
                                                        // 残業時間計算（実時間）
                                                        $deteObj = new DateTime($working_data[$r][6]);
                                                        $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                        $ceil_hour = $deteObj->format('H');
                                                        $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                                        if ($have >= '0000') {
                                                             $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        } else {
                                                            $end = '2017-05-18 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                                        }
                                                        $startSec = strtotime('2017-05-17 17:30:00');
                                                        $endSec   = strtotime($end);
                                                        
                                                        $r_temp = ($endSec - $startSec)/60;
                                                        
                                                        // 早出計算
                                                        if ($working_data[$r][5] <= 800) {
                                                            $hour_temp = 0;
                                                            $deteObj = new DateTime($working_data[$r][5]);
                                                            $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                                            if ($ceil_num2 == 60) {
                                                                $ceil_num2 = 0;
                                                                $hour_temp = 1;
                                                            }
                                                            $ceil_hour2 = $deteObj->format('H');
                                                            $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                                            if ($ceil_hour2 < 10) {
                                                                $ceil_hour2 = '0' . $ceil_hour2;
                                                            }
                                                            $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                                            $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                                            $startSec2 = strtotime('2017-05-17 8:30:00');
                                                            $endSec2   = strtotime($end2);
                                                        
                                                            $r_temp += ($startSec2 - $endSec2)/60;
                                                        }
                                                        
                                                        $hour_r = floor($r_temp / 60);
                                                        $minutes_r = $r_temp%60;
                                                        if($hour_r == 0) {
                                                            $hour_r = '0';
                                                        }
                                                        if($minutes_r == 0) {
                                                            $minutes_r = '00';
                                                        } else if($minutes_r < 10) {
                                                            $minutes_r = '0' . $minutes_r;
                                                        }
                                                            
                                                        // 残業時間計算（申告分）
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                        // 深夜残業加味
                                                        $hour_s = floor($s_temp / 60);
                                                        $minutes_s = $s_temp%60;
                                                        if($hour_s == 0) {
                                                            $hour_s = '0';
                                                        }
                                                        if($minutes_s == 0) {
                                                            $minutes_s = '00';
                                                        } else if($minutes_s < 10) {
                                                            $minutes_s = '0' . $minutes_s;
                                                        }
                                                        //if ($hour_r == $hour) {
                                                        if ($hour_r == $hour_s) {
                                                            //if ($minutes_r == $minutes) {
                                                            if ($minutes_r == $minutes_s) {
                                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                            }
                                                        } else {
                                                                $listTable .= "<td class='winbox' align='right' bgcolor='yellow' nowrap>". $hour .":". $minutes ."</td>\n";
                                                        }
                                                    }
                                                    break;
                                                } else {
                                                    if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>---</td>\n";
                                                    } elseif ($t_temp == '000000') {
                                                        $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                                    } else {
                                                        $hour = floor($t_temp / 60);
                                                        $minutes = $t_temp%60;
                                                        if($hour == 0) {
                                                            $hour = '0';
                                                        }
                                                        if($minutes == 0) {
                                                            $minutes = '00';
                                                        } else if($minutes < 10) {
                                                            $minutes = '0' . $minutes;
                                                        }
                                                            $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    case 11:                                        // 深夜残業
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 12:                                        // 休出時間
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $howork_num += 1;                       // 休日に打刻があれば休出日数＋１
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $howork_num += 1;                       // 休日に打刻があれば休出日数＋１
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 13:                                        // 休出残業
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 14:                                        // 休出深夜
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                        }
                                    case 15:                                        // 法定時間
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                $howork_num += 1;                       // 法定時間に打刻があれば休出日数＋１
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                $howork_num += 1;                       // 法定時間に打刻があれば休出日数＋１
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 16:                                        // 法定残業
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 17:                                        // 遅刻早退
                                        if ($working_data[$r][23] == '済') {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' bgcolor='white' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        } else {
                                            if ($working_data[$r][$i] == '000000') {
                                                    $listTable .= "<td class='winbox' align='right' nowrap>----</td>\n";
                                            } else {
                                                $hour = floor($working_data[$r][$i] / 60);
                                                $minutes = $working_data[$r][$i]%60;
                                                if($hour == 0) {
                                                    $hour = '0';
                                                }
                                                if($minutes == 0) {
                                                    $minutes = '00';
                                                } else if($minutes < 10) {
                                                    $minutes = '0' . $minutes;
                                                }
                                                $listTable .= "<td class='winbox' align='right' nowrap>". $hour .":". $minutes ."</td>\n";
                                            }
                                            break;
                                        }
                                    case 23:                                        // 週報確認
                                        /*
                                        if ($working_data[$r][$i] == '済') {
                                            $listTable .= "<td class='winbox' align='center' bgcolor='white' nowrap>" . $working_data[$r][$i] ."</td>\n";
                                        } else {
                                            $listTable .= "<td class='winbox' align='center' nowrap><font color='red'>". $working_data[$r][$i] ."</font></td>\n";
                                        }
                                        */
                                        break;
                                    default:                                          
                                        break;
                                }
                            }
                            
                            $listTable .= "    </tr>\n";
                        }
                    } else {
                        for ($r=0; $r<$request->get('rows'); $r++) {                                        // レコード数分繰返し
                            for ($i=0; $i<$request->get('num'); $i++) {
                                switch ($i) {
                                    case 5:
                                        if ($working_data[$r][$i] != '0000') {
                                            $work_num += 1;                                 // 出勤時刻が打刻されていれば出勤日数＋１
                                        }
                                        break;
                                    case 12:
                                        if ($working_data[$r][$i] != '000000') {
                                            $howork_num += 1;                               // 休出時間があれば休出日数＋１
                                        }
                                        break;
                                    case 15:
                                        if ($working_data[$r][$i] != '000000') {
                                            $howork_num += 1;                               // 法定時間があれば休出日数＋１
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                    // 集計データ部分作成
                    $listTable = $this->getViewHTMLTotal($request, $listTable, $working_data, $work_num, $howork_num, $uid[$t], $chek_flg);
                }
            }
        }
        $listTable .= "
                        <form name='CorrectForm'  method='post' target='_parent'
                           onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                        >
        \n";
        $listTable .= "</form>\n";
        /*
        if ($request->get('rows') <= 0) {
        } else {
            $num = $request->get('rows') - 1;                                           // データがない日付を指定しても
            $str_date = $working_data[0][1];                                            // データがある最終日までを
            $end_date = $working_data[$num][1];                                         // 確定するように対応
            if (!getCheckAuthority(29)) {                                               // 部長代理以上は照会のみ
                if ($_SESSION['User_ID'] != '970227') {                                 // 保志さんは照会のみ
                    if ($_SESSION['User_ID'] == '010472') {                             // 総務課長 揚石課長
                        if ($request->get('targetSection') == 5) {                      // 総務課は確定できる
                            if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                                $listTable .= "
                                                <form name='CorrectForm'  method='post'  target='_parent' 
                                                    onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                >
                                \n";
                                $listTable .= "    <CENTER>\n";
                                $listTable .= "        <input type='button' name='correct1' value='修正内容登録' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='クリックすれば、この下に表示します。'>\n";
                                $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                                $listTable .= "    </CENTER>\n";
                                $listTable .= "</form>\n";
                        
                            }
                        } else if ($request->get('targetSection') == 31) {              // 出向者は確定できる
                            if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                                $listTable .= "
                                                <form name='CorrectForm'  method='post' target='_parent'
                                                    onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                >
                                \n";
                                $listTable .= "    <CENTER>\n";
                                $listTable .= "        <input type='button' name='correct1' value='修正内容登録' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='クリックすれば、この下に表示します。'>\n";
                                $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                                $listTable .= "    </CENTER>\n";
                                $listTable .= "</form>\n";
                        
                            }
                        } else if ($request->get('targetSection') == (-3)) {              // 8級職以上は確定できる
                            if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                                $listTable .= "
                                                <form name='CorrectForm'  method='post' target='_parent'
                                                    onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                >
                                \n";
                                $listTable .= "    <CENTER>\n";
                                $listTable .= "        <input type='button' name='correct1' value='修正内容登録' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='クリックすれば、この下に表示します。'>\n";
                                $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                                $listTable .= "    </CENTER>\n";
                                $listTable .= "</form>\n";
                        
                            }
                        }
                    } else {
                        if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                            $listTable .= "
                                            <form name='CorrectForm'  method='post' target='_parent'
                                                onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                            >
                            \n";
                            $listTable .= "    <CENTER>\n";
                            $listTable .= "        <input type='button' name='correct1' value='修正内容登録' onClick='WorkingHoursReport.checkANDexecute(document.CorrectForm, 3);' title='クリックすれば、この下に表示します。'>\n";
                            $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmexecute(" . $request->get('targetSection') . ", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                            $listTable .= "    </CENTER>\n";
                            $listTable .= "</form>\n";
                        }
                    }
                }
            }
        }
        */
        // return mb_convert_encoding($listTable, 'UTF-8');
        $request->add('check_flg', 'n');
        return $listTable;
    }
    ////// List部   就業週報照会の集計データ部分作成
    private function getViewHTMLTotal($request, $listTable, $working_data, $work_num, $howork_num, $uid, $check_flg)
    {
        $fixed_time    = 0;
        $fixed_hour    = 0;
        $fixed_min     = 0;
        $extend_time   = 0;
        $extend_hour   = 0;
        $extend_min    = 0;
        $overtime      = 0;
        $over_hour     = 0;
        $over_min      = 0;
        $midnight_over = 0;
        $mid_hour      = 0;
        $mid_min       = 0;
        $holiday_time  = 0;
        $hotime_hour   = 0;
        $hotime_min    = 0;
        $holiday_over  = 0;
        $hoover_hour   = 0;
        $hoover_min    = 0;
        $holiday_mid   = 0;
        $homid_hour    = 0;
        $homid_min     = 0;
        $legal_time    = 0;
        $legal_hour    = 0;
        $legal_min     = 0;
        $legal_over    = 0;
        $leover_hour   = 0;
        $leover_min    = 0;
        $late_time     = 0;
        $late_hour     = 0;
        $late_min      = 0;
        for ($r=0; $r<$request->get('rows'); $r++) {                            // 各時間の集計
            $fixed_time    += $working_data[$r][7];                             // 所定時間
            $extend_time   += $working_data[$r][8];                             // 延長時間
            $overtime      += $working_data[$r][9] + $working_data[$r][10];     // 早出残業時間
            $midnight_over += $working_data[$r][11];                            // 深夜残業時間
            $holiday_time  += $working_data[$r][12];                            // 休出時間
            $holiday_over  += $working_data[$r][13];                            // 休出残業
            $holiday_mid   += $working_data[$r][14];                            // 休出深夜
            $legal_time    += $working_data[$r][15];                            // 法定時間
            $legal_over    += $working_data[$r][16];                            // 法定残業
            $late_time     += $working_data[$r][17];                            // 遅刻早退
        }
        $fixed_hour  = floor($fixed_time / 60);                                 // 所定時間の時間部分計算
        $fixed_min   = $fixed_time%60;                                          // 所定時間の分数部分計算
        $extend_hour = floor($extend_time / 60);                                // 延長時間の時間部分計算
        $extend_min  = $extend_time%60;                                         // 延長時間の分数部分計算
        $over_hour   = floor($overtime / 60);                                   // 早出残業時間の時間部分計算
        $over_min    = $overtime%60;                                            // 早出残業の分数部分計算
        $mid_hour    = floor($midnight_over / 60);                              // 深夜残業時間の時間部分計算
        $mid_min     = $midnight_over%60;                                       // 深夜残業時間の分数部分計算
        $hotime_hour = floor($holiday_time / 60);                               // 休出時間の時間部分計算
        $hotime_min  = $holiday_time%60;                                        // 休出時間の分数部分計算
        $hoover_hour = floor($holiday_over / 60);                               // 休出残業時間の時間部分計算
        $hoover_min  = $holiday_over%60;                                        // 休出残業時間の分数部分計算
        $homid_hour  = floor($holiday_mid / 60);                                // 休出深夜時間の時間部分計算
        $homid_min   = $holiday_mid%60;                                         // 休出深夜時間の分数部分計算
        $legal_hour  = floor($legal_time / 60);                                 // 法定時間の時間部分計算
        $legal_min   = $legal_time%60;                                          // 法定時間の分数部分計算
        $leover_hour = floor($legal_over / 60);                                 // 法定残業時間の時間部分計算
        $leover_min  = $legal_over%60;                                          // 法定残業時間の分数部分計算
        $late_hour   = floor($late_time / 60);                                  // 遅刻早退時間の時間部分計算
        $late_min    = $late_time%60;                                           // 遅刻早退時間の分数部分計算
        $listTable .= "    <tr>\n";                                             // 集計データの表示
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>所定時間</th>\n";
        $listTable .= "        <th class='winbox'>延長時間</th>\n";
        $listTable .= "        <th class='winbox'>早出残業</th>\n";
        $listTable .= "        <th class='winbox'>深夜残業</th>\n";
        $listTable .= "        <th class='winbox'>休出時間</th>\n";
        $listTable .= "        <th class='winbox'>休出残業</th>\n";
        $listTable .= "        <th class='winbox'>休出深夜</th>\n";
        $listTable .= "        <th class='winbox'>法定時間</th>\n";
        $listTable .= "        <th class='winbox'>法定残業</th>\n";
        $listTable .= "        <th class='winbox'>遅刻早退</th>\n";
        //$listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        if ($fixed_min == 0) {                                      // 所定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $fixed_hour .":". $fixed_min ."0</td>\n";
        } else if ($fixed_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $fixed_hour .":0". $fixed_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $fixed_hour .":". $fixed_min ."</td>\n";
        }
        if ($extend_min == 0) {                                     // 延長時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $extend_hour .":". $extend_min ."0</td>\n";
        } else if ($extend_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $extend_hour .":0". $extend_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $extend_hour .":". $extend_min ."</td>\n";
        }
        if ($over_min == 0) {                                       // 早出残業時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $over_hour .":". $over_min ."0</td>\n";
        } else if ($over_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $over_hour .":0". $over_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $over_hour .":". $over_min ."</td>\n";
        }
        if ($mid_min == 0) {                                        // 深夜残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $mid_hour .":". $mid_min ."0</td>\n";
        } else if ($mid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $mid_hour .":0". $mid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $mid_hour .":". $mid_min ."</td>\n";
        }
        if ($hotime_min == 0) {                                     // 休出時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hotime_hour .":". $hotime_min ."0</td>\n";
        } else if ($hotime_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hotime_hour .":0". $hotime_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hotime_hour .":". $hotime_min ."</td>\n";
        }
        if ($hoover_min == 0) {                                     // 休出残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hoover_hour .":". $hoover_min ."0</td>\n";
        } else if ($hoover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hoover_hour .":0". $hoover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $hoover_hour .":". $hoover_min ."</td>\n";
        }
        if ($homid_min == 0) {                                      // 休出深夜集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $homid_hour .":". $homid_min ."0</td>\n";
        } else if ($homid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $homid_hour .":0". $homid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $homid_hour .":". $homid_min ."</td>\n";
        }
        if ($legal_min == 0) {                                      // 法定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $legal_hour .":". $legal_min ."0</td>\n";
        } else if ($legal_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $legal_hour .":0". $legal_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $legal_hour .":". $legal_min ."</td>\n";
        }
        if ($leover_min == 0) {                                     // 法定残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $leover_hour .":". $leover_min ."0</td>\n";
        } else if ($leover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $leover_hour .":0". $leover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $leover_hour .":". $leover_min ."</td>\n";
        }
        if ($late_min == 0) {                                       // 遅刻早退時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $late_hour .":". $late_min ."0</td>\n";
        } else if ($late_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $late_hour .":0". $late_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $late_hour .":". $late_min ."</td>\n";
        }
        //$listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "</table>\n";                                 // 日数データの表示
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        $listTable .= "</center>\n";
        $listTable .= "<table width='40%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='2'>\n";
        if ($request->get('rows') <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='4' width='100%' align='center' class='winbox'>データがありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox'>所定日数</th>\n";
            $listTable .= "        <th class='winbox'>出勤日数</th>\n";
            $listTable .= "        <th class='winbox'>休出日数</th>\n";
            $listTable .= "        <th class='winbox'>年休日数</th>\n";
            $listTable .= "        <th class='winbox'>時間休時間</th>\n";
            $listTable .= "        <th class='winbox'>欠勤日数</th>\n";
            $listTable .= "        <th class='winbox'>休業日数</th>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";        
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('fixed_num'), 2) ."</div></td>\n";    // 所定日数表示
            $work_num = $work_num - $howork_num - $request->get('hohalf_num');      // 出勤日数の計算 （打刻時間のある日数)-(休出日数)-(有休日数)
            if ($work_num < 0) {
                $work_num = 0;
            }
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($work_num, 2) ."</div></td>\n";     // 出勤日数表示
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($howork_num, 2) ."</div></td>\n";   // 休出日数表示
            if ($request->get('paidho_num') == 0) {                                 // 有休日数表示
                $listTable .= "        <td class='winbox'<div align='right'>0.00</div></td>\n";
            } else {
                $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('paidho_num'), 2) ."</div></td>\n";
            }
            if ($request->get('hotime_num') == 0) {                                 // 時間休時間表示
                $listTable .= "        <td class='winbox'<div align='right'>0.00</div></td>\n";
            } else {
                $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('hotime_num'), 2) ."</div></td>\n";
            }
            if ($request->get('noholy_num') == 0) {                                 // 欠勤日数表示
                $listTable .= "        <td class='winbox'<div align='right'>0.00</div></td>\n";
            } else {
                $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('noholy_num'), 2) ."</div></td>\n";
            }
            $listTable .= "        <td class='winbox'><div align='right'>". number_format($request->get('closure_num'), 2) ."</div></td>\n";   // 休出日数表示
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            if ($request->get('rows') <= 0) {
            } else {
                if ($check_flg == 'n') {
                    
                } else {
                    $num = $request->get('rows') - 1;                                           // データがない日付を指定しても
                    $str_date = $working_data[0][1];                                            // データがある最終日までを
                    $end_date = $working_data[$num][1];                                         // 確定するように対応
                    $request->add('str_date', $str_date);
                    $request->add('end_date', $end_date);
                    if (!getCheckAuthority(29)) {                                               // 部長代理以上は照会のみ
                        if ($_SESSION['User_ID'] != '970227') {                                 // 保志さんは照会のみ
                            if ($_SESSION['User_ID'] == '010472') {                             // 総務課長 揚石課長
                                if ($request->get('targetSection') == 5) {                      // 総務課は確定できる
                                    if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                                        $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                        \n";
                                        $listTable .= "    <CENTER>\n";
                                        $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                        $listTable .= "            <option value='1'>問題なし</option>\n";
                                        $listTable .= "            <option value='2'>届出提出済</option>\n";
                                        $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                        $listTable .= "        </select>\n";
                                        $tnk_uid    = 'tnk' . $uid;
                                        $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                                        $listTable .= "    </CENTER>\n";
                                        $listTable .= "</form>\n";
                                    }
                                } else if ($request->get('targetSection') == 31) {              // 出向者は確定できる
                                    if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                                        $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                        \n";
                                        $listTable .= "    <CENTER>\n";
                                        $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                        $listTable .= "            <option value='1'>問題なし</option>\n";
                                        $listTable .= "            <option value='2'>届出提出済</option>\n";
                                        $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                        $listTable .= "        </select>\n";
                                        $tnk_uid    = 'tnk' . $uid;
                                        $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                                        $listTable .= "    </CENTER>\n";
                                        $listTable .= "</form>\n";
                                
                                    }
                                } else if ($request->get('targetSection') == (-3)) {              // 8級職以上は確定できる
                                    if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                                        $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                        \n";
                                        $listTable .= "    <CENTER>\n";
                                        $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                        $query = sprintf("SELECT confirm_flg FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $str_date);
                                        $res_chk = array();
                                        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり <option>選択
                                            if ($res_chk[0] == 1) {
                                                $listTable .= "            <option value='1' selected>問題なし</option>\n";
                                                $listTable .= "            <option value='2'>届出提出済</option>\n";
                                                $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                            } elseif ($res_chk[0] == 2) {
                                                $listTable .= "            <option value='1'>問題なし</option>\n";
                                                $listTable .= "            <option value='2' selected>届出提出済</option>\n";
                                                $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                            } elseif ($res_chk[0] == 3) {
                                                $listTable .= "            <option value='1'>問題なし</option>\n";
                                                $listTable .= "            <option value='2'>届出提出済</option>\n";
                                                $listTable .= "            <option value='3' selected>届出依頼済</option>\n";
                                            }
                                        } else {                                    // 登録なし<option>未選択
                                            $listTable .= "            <option value='1' selected>問題なし</option>\n";
                                            $listTable .= "            <option value='2'>届出提出済</option>\n";
                                            $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                        }
                                        $listTable .= "        </select>\n";
                                        $tnk_uid    = 'tnk' . $uid;
                                        $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                                        $listTable .= "    </CENTER>\n";
                                        $listTable .= "</form>\n";
                                    }
                                }
                            } else {
                                if ($request->get('formal') == 'details') {                 // リスト形式のチェック
                                    $listTable .= "
                                                    <form name='CorrectForm". $uid ."'  method='post' target='_parent'
                                                        onSubmit='return WorkingHoursReport.checkANDexecute(this, 1)'
                                                    >
                                    \n";
                                    $listTable .= "    <CENTER>\n";
                                    $listTable .= "        <select name='ConfirmFlg". $uid ."' class='pt12b' onchange='WorkingHoursReport.getSelected(" . $uid . ")'>\n";
                                    $query = sprintf("SELECT confirm_flg FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $str_date);
                                    $res_chk = array();
                                    if ( getResult($query, $res_chk) > 0 ) {    // 登録あり <option>選択
                                        if ($res_chk[0][0] == 1) {
                                            $listTable .= "            <option value='1' selected>問題なし</option>\n";
                                            $listTable .= "            <option value='2'>届出提出済</option>\n";
                                            $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                        } elseif ($res_chk[0][0] == 2) {
                                            $listTable .= "            <option value='1'>問題なし</option>\n";
                                            $listTable .= "            <option value='2' selected>届出提出済</option>\n";
                                            $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                        } elseif ($res_chk[0][0] == 3) {
                                            $listTable .= "            <option value='1'>問題なし</option>\n";
                                            $listTable .= "            <option value='2'>届出提出済</option>\n";
                                            $listTable .= "            <option value='3' selected>届出依頼済</option>\n";
                                        } else {
                                            $listTable .= "            <option value='1' selected>問題なし</option>\n";
                                            $listTable .= "            <option value='2'>届出提出済</option>\n";
                                            $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                        }
                                    } else {                                    // 登録なし<option>未選択
                                        $listTable .= "            <option value='1' selected>問題なし</option>\n";
                                        $listTable .= "            <option value='2'>届出提出済</option>\n";
                                        $listTable .= "            <option value='3'>届出依頼済</option>\n";
                                    }
                                    $listTable .= "        </select>\n";
                                    $tnk_uid    = 'tnk' . $uid;
                                    $listTable .= "        <input type='button' name='correct2' value='週報確認' onClick='WorkingHoursReport.Confirmoneexecute(\"" . $tnk_uid . "\", " . $str_date . ", " . $end_date . ", " . $request->get('targetSection') . ", " . $request->get('targetSection') . ");' title='クリックすれば、別ウィンドウで表示します。'>\n";
                                    $listTable .= "    </CENTER>\n";
                                    $listTable .= "</form>\n";
                                }
                            }
                        }
                    }
                }
            }
            $listTable .= "<BR>\n";
        }
        if ($uid == '014737') {                                 // 出向者の社員番号の変換
             $uid = '914737';                                   // 014737=総務課 桝さん
        } else if ($uid == '020206') {                          // 020206=技術課 宝口さん
             $uid = '920206';
        }
        if ($uid != '914737') {                                 // 総合計の計算（出向者は除く）
            if ($uid != '920206') {
                if (substr($uid, 0, 1) == '9') {
                    $total_fixed_time_p    = $request->get('total_fixed_time_p') + $fixed_time;       // 所定時間計
                    $request->add('total_fixed_time_p', $total_fixed_time_p);
                    $total_extend_time_p   = $request->get('total_extend_time_p') + $extend_time;     // 延長時間計
                    $request->add('total_extend_time_p', $total_extend_time_p);
                    $total_overtime_p      = $request->get('total_overtime_p') + $overtime;           // 早出残業時間計
                    $request->add('total_overtime_p', $total_overtime_p);
                    $total_midnight_over_p = $request->get('total_midnight_over_p') + $midnight_over; // 早出残業時間計
                    $request->add('total_midnight_over_p', $total_midnight_over_p);
                    $total_holiday_time_p  = $request->get('total_holiday_time_p') + $holiday_time;   // 休出時間計
                    $request->add('total_holiday_time_p', $total_holiday_time_p);
                    $total_holiday_over_p  = $request->get('total_holiday_over_p') + $holiday_over;   // 休出残業計
                    $request->add('total_holiday_over_p', $total_holiday_over_p);
                    $total_holiday_mid_p   = $request->get('total_holiday_mid_p') + $holiday_mid;     // 休出深夜計
                    $request->add('total_holiday_mid_p', $total_holiday_mid_p);
                    $total_legal_time_p    = $request->get('total_legal_time_p') + $legal_time;       // 法定時間計
                    $request->add('total_legal_time_p', $total_legal_time_p);
                    $total_legal_over_p    = $request->get('total_legal_over_p') + $legal_over;       // 法定残業計
                    $request->add('total_legal_over_p', $total_legal_over_p);
                    $total_late_time_p     = $request->get('total_late_time_p') + $late_time;         // 遅刻早退計
                    $request->add('total_late_time_p', $total_late_time_p);
                } else {
                    $total_fixed_time_s    = $request->get('total_fixed_time_s') + $fixed_time;       // 所定時間計
                    $request->add('total_fixed_time_s', $total_fixed_time_s);
                    $total_extend_time_s   = $request->get('total_extend_time_s') + $extend_time;     // 延長時間計
                    $request->add('total_extend_time_s', $total_extend_time_s);
                    $total_overtime_s      = $request->get('total_overtime_s') + $overtime;           // 早出残業時間計
                    $request->add('total_overtime_s', $total_overtime_s);
                    $total_midnight_over_s = $request->get('total_midnight_over_s') + $midnight_over; // 早出残業時間計
                    $request->add('total_midnight_over_s', $total_midnight_over_s);
                    $total_holiday_time_s  = $request->get('total_holiday_time_s') + $holiday_time;   // 休出時間計
                    $request->add('total_holiday_time_s', $total_holiday_time_s);
                    $total_holiday_over_s  = $request->get('total_holiday_over_s') + $holiday_over;   // 休出残業計
                    $request->add('total_holiday_over_s', $total_holiday_over_s);
                    $total_holiday_mid_s   = $request->get('total_holiday_mid_s') + $holiday_mid;     // 休出深夜計
                    $request->add('total_holiday_mid_s', $total_holiday_mid_s);
                    $total_legal_time_s    = $request->get('total_legal_time_s') + $legal_time;       // 法定時間計
                    $request->add('total_legal_time_s', $total_legal_time_s);
                    $total_legal_over_s    = $request->get('total_legal_over_s') + $legal_over;       // 法定残業計
                    $request->add('total_legal_over_s', $total_legal_over_s);
                    $total_late_time_s     = $request->get('total_late_time_s') + $late_time;         // 遅刻早退計
                    $request->add('total_late_time_s', $total_late_time_s);
                }
                $total_fixed_time    = $request->get('total_fixed_time_p') + $request->get('total_fixed_time_s');       // 所定時間計
                $request->add('total_fixed_time', $total_fixed_time);
                $total_extend_time   = $request->get('total_extend_time_p') + $request->get('total_extend_time_s');     // 延長時間計
                $request->add('total_extend_time', $total_extend_time);
                $total_overtime      = $request->get('total_overtime_p') + $request->get('total_overtime_s');           // 早出残業時間計
                $request->add('total_overtime', $total_overtime);
                $total_midnight_over = $request->get('total_midnight_over_p') + $request->get('total_midnight_over_s'); // 早出残業時間計
                $request->add('total_midnight_over', $total_midnight_over);
                $total_holiday_time  = $request->get('total_holiday_time_p') + $request->get('total_holiday_time_s');   // 休出時間計
                $request->add('total_holiday_time', $total_holiday_time);
                $total_holiday_over  = $request->get('total_holiday_over_p') + $request->get('total_holiday_over_s');   // 休出残業計
                $request->add('total_holiday_over', $total_holiday_over);
                $total_holiday_mid   = $request->get('total_holiday_mid_p') + $request->get('total_holiday_mid_s');     // 休出深夜計
                $request->add('total_holiday_mid', $total_holiday_mid);
                $total_legal_time    = $request->get('total_legal_time_p') + $request->get('total_legal_time_s');       // 法定時間計
                $request->add('total_legal_time', $total_legal_time);
                $total_legal_over    = $request->get('total_legal_over_p') + $request->get('total_legal_over_s');       // 法定残業計
                $request->add('total_legal_over', $total_legal_over);
                $total_late_time     = $request->get('total_late_time_p') + $request->get('total_late_time_s');         // 遅刻早退計
                $request->add('total_late_time', $total_late_time);
            }
        }
        return $listTable;
    }
    
    ///// 所属部門IDより部門名を取得
    private function getSectionName($rows,$res)
    {
        $s_name = array();
        $res_n  = array();
        for ($i=0; $i<$rows; $i++) {
            $query="select section_name -- 00
                    from section_master
                    where sid={$res[$i]['sid']}
            ";
            if ($rows_n=getResult($query,$res_n)) {
                $s_name[$i] = $res_n[0][0];
            } else {
                $s_name[$i] = '----------';
            }
            $res_n  = array();
        }
        return $s_name;
    }
    
    ///// 社員番号が指定された場合の部門名の取得
    private function getSectionNameOne($uid)
    {
        
        $query="select sid   -- 00
                from user_detailes
                where uid='{$uid}'
        ";
        $res = array();
        if ($rows=getResult($query,$res)) {
            $sid = $res[0][0];
        } else {
            $s_name = '----------';
            return $s_name;
        }
        $query="select section_name -- 00
                from section_master
                where sid={$sid}
        ";
        if ($rows=getResult($query,$res)) {
            $s_name = $res[0][0];
            return $s_name;
        } else {
            $s_name = '----------';
            return $s_name;
        }
    }
    
    ///// 部門IDから所属社員番号を取得
    private function getSectionUser($sid, $position, $sdate, $edate)
    {
        if ($position == '') {              // すべての職位
            if ($sid == (-2)) {                    // 全ての社員No.取得(８級職以上は除く)
                /* アルバイトなし版
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ８級職以上の社員番号を取得
                /* アルバイトなし版
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4) || $sid == (-6)) {              // 残業有りのみ 8級職以上は除く
                /* アルバイトなし版
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // エラーのみ（取得は-2の全体と同じ）
                /* アルバイトなし版
                $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // 選択された部門IDより所属社員番号を取得（８級職以上は除く）
                if ($sid == 36) {                  // 生産部 組立１課にはsid=8生産部を追加(事務２人分の為)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //総務課はISOと出向者も含む なお社長は除外すること
                        $query="select u.uid -- 00
                            ,u.class  -- 01
                            ,u.sid    -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  部長以上含む版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8級職除外版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // 製造部製造１課にはsid=17製造部を追加(事務１名分の為)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8級職除外版 元は製造部も入っていた
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                    /* アルバイトなし版
                    $query="select u.uid -- 00
                                  ,u.class -- 01
                                  ,u.sid   -- 02
                                  ,c.act_id -- 03
                            from user_detailes as u
                            left outer join cd_table as c
                            on u.uid=c.uid
                            where u.sid={$sid} 
                            and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.sid<>95
                            ORDER BY c.act_id, u.uid
                    ";
                    */
                    $query="select u.uid -- 00
                                  ,u.class -- 01
                                  ,u.sid   -- 02
                                  ,c.act_id -- 03
                            from user_detailes as u
                            left outer join cd_table as c
                            on u.uid=c.uid
                            where u.sid={$sid} 
                            and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.sid<>95
                            ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '1') {       // 社員（パート・パートスタッフ・嘱託・契約社員以外）
            if ($sid == (-2)) {                    // 全ての社員No.取得(８級職以上は除く)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' パート and pid != '6' パートスタッフ and pid != '8' 嘱託 and pid != '9' 契約社員
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ８級職以上の社員番号を取得
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4) || $sid == (-6)) {              // 残業有りのみ 8級職以上は除く
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // エラーのみ（取得は-2の全体と同じ）
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // 選択された部門IDより所属社員番号を取得（８級職以上は除く）
                if ($sid == 36) {                  // 生産部 組立１課にはsid=8生産部を追加(事務２人分の為)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9'
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //総務課はISOと出向者も含む なお社長は除外すること
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9'
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  部長以上含む版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8級職除外版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // 製造部製造１課にはsid=17製造部を追加(事務１名分の為)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9'
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8級職除外版 元は製造部も入っていた
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and u.pid != '5' and u.pid != '6' and u.pid != '8' and u.pid != '9' and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '2') {       // パート・パートスタッフのみ
            if ($sid == (-2)) {                    // 全ての社員No.取得(８級職以上は除く)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' パート and pid != '6' パートスタッフ and pid != '8' 嘱託 and pid != '9' 契約社員
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ８級職以上の社員番号を取得
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4) || $sid == (-6)) {              // 残業有りのみ 8級職以上は除く
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // エラーのみ（取得は-2の全体と同じ）
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // 選択された部門IDより所属社員番号を取得（８級職以上は除く）
                if ($sid == 36) {                  // 生産部 組立１課にはsid=8生産部を追加(事務２人分の為)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '5' or u.pid = '6')
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //総務課はISOと出向者も含む なお社長は除外すること
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '5' or u.pid = '6')
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  部長以上含む版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8級職除外版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // 製造部製造１課にはsid=17製造部を追加(事務１名分の為)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '5' or u.pid = '6')
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8級職除外版 元は製造部も入っていた
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '5' or u.pid = '6') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '3') {       // 契約・その他
            if ($sid == (-2)) {                    // 全ての社員No.取得(８級職以上は除く)
                /* アルバイトなし版
                $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and (u.pid = '8' or u.pid = '9' or u.pid = '15') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' パート and pid != '6' パートスタッフ and pid != '8' 嘱託 and pid != '9' 契約社員
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ８級職以上の社員番号を取得
                /* アルバイトなし版
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and (u.pid = '8' or u.pid = '9' or u.pid = '15') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4) || $sid == (-6)) {              // 残業有りのみ 8級職以上は除く
                /* アルバイトなし版
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and (u.pid = '8' or u.pid = '9' or u.pid = '15') and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // エラーのみ（取得は-2の全体と同じ）
                /* アルバイトなし版
                $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                */
                $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and (u.pid = '8' or u.pid = '9' or u.pid = '15') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // 選択された部門IDより所属社員番号を取得（８級職以上は除く）
                if ($sid == 36) {                  // 生産部 組立１課にはsid=8生産部を追加(事務２人分の為)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '8' or u.pid = '9')
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //総務課はISOと出向者も含む なお社長は除外すること
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '8' or u.pid = '9')
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  部長以上含む版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8級職除外版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // 製造部製造１課にはsid=17製造部を追加(事務１名分の為)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '8' or u.pid = '9')
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8級職除外版 元は製造部も入っていた
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                    /* アルバイトなし版
                    $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '8' or u.pid = '9') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    */
                    $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date isnull OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and (u.pid = '8' or u.pid = '9' or u.pid = '15') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        } elseif ($position == '4') {       // 課長代理以上
            if ($sid == (-2)) {                    // 全ての社員No.取得(８級職以上は除く)
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                //  and pid != '5' パート and pid != '6' パートスタッフ and pid != '8' 嘱託 and pid != '9' 契約社員
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else if ($sid == (-3)) {              // ８級職以上の社員番号を取得
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class LIKE '8%' OR u.class LIKE '9%' OR u.class LIKE '10%' 
                        OR u.class LIKE '11%' OR u.class LIKE '12%') 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                ";
            } else if ($sid == (-4) || $sid == (-6)) {              // 残業有りのみ 8級職以上は除く
                $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.class NOT LIKE '8%' AND u.class NOT LIKE '9%' AND u.class NOT LIKE '10%' 
                        AND u.class NOT LIKE '11%' AND u.class NOT LIKE '12%' OR u.class IS NULL) AND (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43') and u.sid<>95
                        ORDER BY u.sid, c.act_id, u.uid
                ";
            } else if ($sid == (-5)) {              // エラーのみ（取得は-2の全体と同じ）
                 $query="select u.uid -- 00
                             ,u.class -- 01
                             ,u.sid   -- 02
                             ,c.act_id -- 03
                         from user_detailes as u
                         left outer join cd_table as c
                         on u.uid=c.uid
                         where (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43') and u.sid<>95
                         ORDER BY u.sid, c.act_id, u.uid
                ";
                /* 8級職 除外版
                $query="select uid -- 00
                             ,class -- 01
                             ,sid   -- 02
                         from user_detailes
                         where (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                         AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                         and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                         ORDER BY sid, uid
                ";
                */
            } else {                               // 選択された部門IDより所属社員番号を取得（８級職以上は除く）
                if ($sid == 36) {                  // 生産部 組立１課にはsid=8生産部を追加(事務２人分の為)
                    $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15'  and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43')
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    ";
                    */
                } else if ($sid == 5) {           //総務課はISOと出向者も含む なお社長は除外すること
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where (u.sid={$sid} OR u.sid=30 OR u.sid=31) 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43')
                        ORDER BY c.act_id, u.uid
                        ";
                        /*  部長以上含む版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=8 OR sid=9 OR sid=17 OR sid=31 OR sid=38 OR sid=99) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000' and uid != '010367'
                        ORDER BY uid
                        ";
                        */
                        /* 8級職除外版
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else if ($sid == 34) {           // 製造部製造１課にはsid=17製造部を追加(事務１名分の為)
                        $query="select u.uid -- 00
                            ,u.class -- 01
                            ,u.sid   -- 02
                            ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43')
                        ORDER BY c.act_id, u.uid
                        ";
                        /* 8級職除外版 元は製造部も入っていた
                        $query="select uid -- 00
                            ,class -- 01
                            ,sid   -- 02
                        from user_detailes
                        where (sid={$sid} OR sid=17) and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                        ";
                        */
                } else {
                        $query="select u.uid -- 00
                                ,u.class -- 01
                                ,u.sid   -- 02
                                ,c.act_id -- 03
                        from user_detailes as u
                        left outer join cd_table as c
                        on u.uid=c.uid
                        where u.sid={$sid} 
                        and (u.retire_date is null OR u.retire_date >= CAST('{$sdate}' AS DATE)) and u.uid != '000000' and u.uid != '010367' and u.pid != '15' and (u.pid = '46' or u.pid = '47' or u.pid = '50' or u.pid = '60' or u.pid = '70' or u.pid = '95' or u.pid = '110' or u.pid = '43') and u.sid<>95
                        ORDER BY c.act_id, u.uid
                    ";
                    /* 8級職除外版
                    $query="select uid -- 00
                                ,class -- 01
                                ,sid   -- 02
                        from user_detailes
                        where sid={$sid} and (class NOT LIKE '8%' AND class NOT LIKE '9%' AND class NOT LIKE '10%' 
                        AND class NOT LIKE '11%' AND class NOT LIKE '12%' OR class IS NULL) 
                        and (retire_date is null OR retire_date >= CAST('{$sdate}' AS DATE)) and uid != '000000'
                        ORDER BY uid
                    */
                }
            }
        }
        return $query;
    }
    ///// タイムプロデータの取得・変換
    private function getErrorCheck($request, $uid, $t, $working_data, $sid_t)
    {
        $today_ym   = date('Ymd');
        for ($r=0; $r<$request->get('rows'); $r++) {        // レコード数分繰返し
            $error_flg = '';                                // エラーフラグ初期化
            if ($working_data[$r][1] != $today_ym) {        // 当日以外
                if ($working_data[$r][5] == '0000') {           // 出勤打刻なし
                    if ($working_data[$r][6] == '0000') {       // 退勤打刻なし
                        if ($working_data[$r][3] == '休日' || $working_data[$r][3] == '法休' || $working_data[$r][3] == '休業') {
                            continue;                           // 休日であれば表示しない
                        } else {                                // 休日では無い時
                            if ($working_data[$r][4] == '　') { // 不在理由が空欄の場合はエラーなので表示
                                return false;
                            } else {
                                continue;                       // 空欄でなければ表示しない
                            }
                        }
                    } else {    // 出勤打刻がなく、退勤があればエラーの為、表示
                        return false;
                    }
                } else {        // 出勤打刻あり
                    if ($working_data[$r][6] == '0000') {
                        return false;   // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                    } else {    // 出勤打刻ありで、退勤打刻ありの場合は延長残業チェック
                        // 延長チェック
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // パート 延長チェック
                            if ($working_data[$r][6] >= '1645') {   // 延長30分以上 チェック
                                if ($working_data[$r][8] == '000000') {    // 延長打刻なしなのでエラー表示
                                    return false;   // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                                } else {
                                    $t_temp = $working_data[$r][8];
                                    // 延長時間計算（実時間）
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 16:15:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    if($r_temp>=60) {   // 延長は１時間までなので調整
                                        $r_temp = 60;
                                    } elseif($r_temp>=30) {
                                        $r_temp = 30;
                                    }
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // 延長時間計算（申告分）
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    if ($hour_r == $hour) {
                                        if ($minutes_r == $minutes) {   // 時間と分が一致 エラーなしなので非表示
                                            //continue;
                                        } else {    // 分が不一致なのでエラー表示
                                            return false;   // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                                        }
                                    } else {    // 時間が不一致なのでエラー表示
                                        return false;   // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                                    }
                                }
                            } else {                                // 延長無し チェック無し
                                //continue;
                            }
                        } else {                                    // パート以外 延長チェック無し
                            //continue;
                        }
                        // 残業チェック
                        $t_temp = $working_data[$r][9] + $working_data[$r][10];
                        $s_temp = $t_temp + $working_data[$r][11];    // 深夜残業加味
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し 非表示
                            continue;
                        } elseif($sid_t[$t] == 19) {                // 商管の場合
                            if ($working_data[$r][6] >= '1830') {
                                if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                    continue;                       // 18:30以降でも社員会や任意研修は非表示
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:30以降で残業の打刻がなければ表示
                                } else {
                                    // 残業時間計算（実時間）
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 18:00:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    // 早出計算
                                    if ($working_data[$r][5] <= 800) {
                                        $hour_temp = 0;
                                        $deteObj = new DateTime($working_data[$r][5]);
                                        $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                        if ($ceil_num2 == 60) {
                                            $ceil_num2 = 0;
                                            $hour_temp = 1;
                                        }
                                        $ceil_hour2 = $deteObj->format('H');
                                        $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                        if ($ceil_hour2 < 10) {
                                            $ceil_hour2 = '0' . $ceil_hour2;
                                        }
                                        $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                        $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                        $startSec2 = strtotime('2017-05-17 8:30:00');
                                        $endSec2   = strtotime($end2);
                                    
                                        $r_temp += ($startSec2 - $endSec2)/60;
                                    }
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // 残業時間計算（申告分）
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // 深夜残業加味
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                        } else {
                                           return false;            // 時間と分が合わないのでエラーの為、表示
                                        }
                                    } else {
                                        return false;               // 時間が合わないのでエラーの為、表示
                                    }
                                }
                            } else {
                                continue;                           // 18:30前は残業ではない為、非表示
                            }
                        } else {                                    // 商管以外の一般社員
                            if ($working_data[$r][6] >= '1800') {
                                if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                    continue;                       // 18:00以降でも社員会や任意研修は非表示
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:00以降で残業の打刻がなければ表示
                                } else {
                                    // 残業時間計算（実時間）
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 17:30:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    // 早出計算
                                    if ($working_data[$r][5] <= 800) {
                                        $hour_temp = 0;
                                        $deteObj = new DateTime($working_data[$r][5]);
                                        $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                        if ($ceil_num2 == 60) {
                                            $ceil_num2 = 0;
                                            $hour_temp = 1;
                                        }
                                        $ceil_hour2 = $deteObj->format('H');
                                        $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                        if ($ceil_hour2 < 10) {
                                            $ceil_hour2 = '0' . $ceil_hour2;
                                        }
                                        $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                        $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                        $startSec2 = strtotime('2017-05-17 8:30:00');
                                        $endSec2   = strtotime($end2);
                                    
                                        $r_temp += ($startSec2 - $endSec2)/60;
                                    }
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // 残業時間計算（申告分）
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // 深夜残業加味
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                        } else {
                                            return false;           // 時間と分が合わないのでエラーの為、表示
                                        }
                                    } else {
                                        return false;               // 時間が合わないのでエラーの為、表示
                                    }
                                }
                            } else {
                                continue;                           // 18:00前は残業ではない為、非表示
                            }
                        }
                    }
                }
            } else {                    // 当日の場合
                if ($working_data[$r][5] == '0000') {           // 出勤打刻なし
                    if ($working_data[$r][6] == '0000') {       // 退勤打刻なし
                        if ($working_data[$r][3] == '休日' || $working_data[$r][3] == '法休' || $working_data[$r][3] == '休業') {
                            continue;                           // 休日であれば表示しない
                        } else {                                // 休日では無い時
                            if ($working_data[$r][4] == '　') { // 不在理由が空欄
                                return false;
                            } else {
                                continue;                       // 空欄でなければ表示しない
                            }
                        }
                    } else {    // 出勤打刻がなく、退勤があればエラーの為、表示
                        return false;
                    }
                } else {                                        // 出勤打刻あり
                    if ($working_data[$r][6] == '0000') {       // 退勤打刻なし
                        continue;                               // 当日は出退勤打刻時まで飛ばす
                    } else {    // 出勤打刻があり、退勤もすでにあればエラーチェック
                        // 延長チェック
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND pid=5";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // パート 延長チェック
                            if ($working_data[$r][6] >= '1645') {   // 延長30分以上 チェック
                                if ($working_data[$r][8] == '000000') {    // 延長打刻なしなのでエラー表示
                                    return false;   // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                                } else {
                                    $t_temp = $working_data[$r][8];
                                    // 延長時間計算（実時間）
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 15) * 15;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 16:15:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    if($r_temp>=60) {   // 延長は１時間までなので調整
                                        $r_temp = 60;
                                    } elseif($r_temp>=30) {
                                        $r_temp = 30;
                                    }
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // 延長時間計算（申告分）
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    if ($hour_r == $hour) {
                                        if ($minutes_r == $minutes) {   // 時間と分が一致 エラーなしなので非表示
                                            //continue;
                                        } else {    // 分が不一致なのでエラー表示
                                            return false;   // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                                        }
                                    } else {    // 時間が不一致なのでエラー表示
                                        return false;   // 出勤打刻ありで、退勤打刻なしはエラーの為、表示
                                    }
                                }
                            } else {                                // 延長無し チェック無し
                                //continue;
                            }
                        } else {                                    // パート以外 延長チェック無し
                            //continue;
                        }
                        // 残業チェック
                        $t_temp = $working_data[$r][9] + $working_data[$r][10];
                        $s_temp = $t_temp + $working_data[$r][11];    // 深夜残業加味
                        $query = "SELECT * FROM user_detailes WHERE uid='{$uid[$t]}' AND (class LIKE '8%' OR class LIKE '9%' OR class LIKE '10%' OR class LIKE '11%' OR class LIKE '12%')";
                        $res_chk = array();
                        if ( getResult($query, $res_chk) > 0 ) {    // 8級職以上 残業チェック無し 非表示
                            continue;
                        } elseif($sid_t[$t] == 19) {                // 商管の場合
                            if ($working_data[$r][6] >= '1830') {
                                if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                    continue;                       // 18:30以降でも社員会や任意研修は非表示
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:30以降で残業の打刻がなければ表示
                                } else {
                                    // 残業時間計算（実時間）
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 18:00:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    // 早出計算
                                    if ($working_data[$r][5] <= 800) {
                                        $hour_temp = 0;
                                        $deteObj = new DateTime($working_data[$r][5]);
                                        $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                        if ($ceil_num2 == 60) {
                                            $ceil_num2 = 0;
                                            $hour_temp = 1;
                                        }
                                        $ceil_hour2 = $deteObj->format('H');
                                        $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                        if ($ceil_hour2 < 10) {
                                            $ceil_hour2 = '0' . $ceil_hour2;
                                        }
                                        $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                        $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                        $startSec2 = strtotime('2017-05-17 8:30:00');
                                        $endSec2   = strtotime($end2);
                                    
                                        $r_temp += ($startSec2 - $endSec2)/60;
                                    }
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // 残業時間計算（申告分）
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // 深夜残業加味
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                        } else {
                                            return false;           // 時間と分が合わないのでエラーの為、表示
                                        }
                                    } else {
                                        return false;               // 時間が合わないのでエラーの為、表示
                                    }
                                }
                            } else {
                                continue;                           // 18:30前は残業ではない為、非表示
                            }
                        } else {                                    // 商管以外の一般社員
                            if ($working_data[$r][6] >= '1800') {
                                if ($working_data[$r][4] == '社会' || $working_data[$r][4] == '任研') {
                                    continue;                       // 18:00以降でも社員会や任意研修は非表示
                                } elseif ($t_temp == '000000') {    
                                    return false;                   // 18:00以降で残業の打刻がなければ表示
                                } else {
                                    // 残業時間計算（実時間）
                                    $deteObj = new DateTime($working_data[$r][6]);
                                    $ceil_num = floor(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                    $ceil_hour = $deteObj->format('H');
                                    $have = $ceil_hour.sprintf( '%02d', $ceil_num);
                                    $end = '2017-05-17 ' . substr($have, 0, 2) . ":" . substr($have, 2, 2) . ":00";
                                    $startSec = strtotime('2017-05-17 17:30:00');
                                    $endSec   = strtotime($end);
                                    
                                    $r_temp = ($endSec - $startSec)/60;
                                    
                                    // 早出計算
                                    if ($working_data[$r][5] <= 800) {
                                        $hour_temp = 0;
                                        $deteObj = new DateTime($working_data[$r][5]);
                                        $ceil_num2 = ceil(sprintf('%d', $deteObj->format('i')) / 30) * 30;
                                        if ($ceil_num2 == 60) {
                                            $ceil_num2 = 0;
                                            $hour_temp = 1;
                                        }
                                        $ceil_hour2 = $deteObj->format('H');
                                        $ceil_hour2 = $ceil_hour2 + $hour_temp;
                                        if ($ceil_hour2 < 10) {
                                            $ceil_hour2 = '0' . $ceil_hour2;
                                        }
                                        $have2 = $ceil_hour2.sprintf( '%02d', $ceil_num2);
                                        $end2 = '2017-05-17 ' . substr($have2, 0, 2) . ":" . substr($have2, 2, 2) . ":00";
                                        $startSec2 = strtotime('2017-05-17 8:30:00');
                                        $endSec2   = strtotime($end2);
                                    
                                        $r_temp += ($startSec2 - $endSec2)/60;
                                    }
                                    
                                    $hour_r = floor($r_temp / 60);
                                    $minutes_r = $r_temp%60;
                                    if($hour_r == 0) {
                                        $hour_r = '0';
                                    }
                                    if($minutes_r == 0) {
                                        $minutes_r = '00';
                                    } else if($minutes_r < 10) {
                                        $minutes_r = '0' . $minutes_r;
                                    }
                                    
                                    // 残業時間計算（申告分）
                                    $hour = floor($t_temp / 60);
                                    $minutes = $t_temp%60;
                                    if($hour == 0) {
                                        $hour = '0';
                                    }
                                    if($minutes == 0) {
                                        $minutes = '00';
                                    } else if($minutes < 10) {
                                        $minutes = '0' . $minutes;
                                    }
                                    // 深夜残業加味
                                    $hour_s = floor($s_temp / 60);
                                    $minutes_s = $s_temp%60;
                                    if($hour_s == 0) {
                                        $hour_s = '0';
                                    }
                                    if($minutes_s == 0) {
                                        $minutes_s = '00';
                                    } else if($minutes_s < 10) {
                                        $minutes_s = '0' . $minutes_s;
                                    }
                                    if ($hour_r == $hour_s) {
                                        if ($minutes_r == $minutes_s) {
                                            continue;               // 申告と実残業時間が一致していればエラーではないので非表示
                                        } else {
                                            return false;           // 時間と分が合わないのでエラーの為、表示
                                        }
                                    } else {
                                        return false;               // 時間が合わないのでエラーの為、表示
                                    }
                                }
                            } else {
                                continue;                           // 18:00前は残業ではない為、非表示
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
    ///// タイムプロデータの取得・変換
    private function getTimeProData($request, $uid, $t)
    {
        $query = $this->getWorkingData($request, $uid[$t]);             // TimeProデータの取得
        $working_data = array();
        $field = array();
        $fixed_num = 0;
        $paidho_num = 0;
        $work_num = 0;
        $howork_num = 0;
        $hohalf_num = 0;
        $closure_num = 0;
        $hotime_num  = 0;       // 時間休時間カウント
        $noholy_num  = 0;       // 欠勤日数カウント
        if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
            $num = 0;
        } else {
            $num = count($field) + 1;
        }
        for ($r=0; $r<$rows; $r++) {                                    
            // TimeProデータの変換（曜日）
            switch ($working_data[$r][2]) {
                case 0:
                    $working_data[$r][2] = '月';
                    break;
                case 1:
                    $working_data[$r][2] = '火';
                    break;
                case 2:
                    $working_data[$r][2] = '水';
                    break;
                case 3:
                    $working_data[$r][2] = '木';
                    break;
                case 4:
                    $working_data[$r][2] = '金';
                    break;
                case 5:
                    $working_data[$r][2] = '土';
                    break;
                case 6:
                    $working_data[$r][2] = '日';
                    break;
            }
            // TimeProデータの変換（カレンダー）
            switch ($working_data[$r][3]) {
                case 1:
                    $working_data[$r][3] = '____';
                    $fixed_num += 1;
                    break;
                case 2:
                    $working_data[$r][3] = '休日';
                    break;
                case 3:
                    $working_data[$r][3] = '法休';
                    break;
                case 5:
                    $working_data[$r][3] = '休業';
                    $closure_num += 1;
                    break;    
            }
            // TimeProデータの変換（不在理由）
            switch ($working_data[$r][4]) {
                case 11:
                    $working_data[$r][4] = '有休';
                    $paidho_num += 1;                                   // 有休日数のカウント＋１
                    break;
                case 12:
                    $working_data[$r][4] = '欠勤';
                    $noholy_num  += 1;                                  // 欠勤日数のカウント＋１
                    break;
                case 13:
                    $working_data[$r][4] = '無欠';
                    break;
                case 14:
                    $working_data[$r][4] = '出張';
                    $work_num += 1;                                     // 出張の場合は出勤日数＋１
                    break;
                case 15:
                    $working_data[$r][4] = '振休';                      // 基本ない
                    break;
                case 16:
                    $working_data[$r][4] = '特休';                      // 出勤日数はカウントしない
                    break;
                case 17:
                    $working_data[$r][4] = '慶事';                      // 基本無し
                    break;
                case 18:
                    $working_data[$r][4] = '弔事';                      // 基本無し
                    break;
                case 19:
                    $working_data[$r][4] = '産休';                      // 出勤日数はカウントしない
                    break;
                case 20:
                    $working_data[$r][4] = '育休';
                    break;
                case 21:
                    $working_data[$r][4] = '生休';
                    break;
                case 22:
                    $working_data[$r][4] = '休職';
                    break;
                case 23:
                    $working_data[$r][4] = '労災';                      // 基本無し
                    break;
                default:
                    if ($working_data[$r][18] == 41) {
                        $working_data[$r][4] = '半AM';
                        $paidho_num += 0.5;                             // 出勤日数のカウント＋０．５（半休）
                        $hohalf_num += 0.5;                             // 有休日数のカウント＋０．５（半休）
                    } else if ($working_data[$r][23] == 42) {
                        $working_data[$r][4] = '半PM';             
                        $paidho_num += 0.5;                             // 出勤日数のカウント＋０．５（半休）
                        $hohalf_num += 0.5;                             // 有休日数のカウント＋０．５（半休）
                    } else if ($working_data[$r][18] == 62) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年2H';
                                $hotime_num  += 2;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 70) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年2H';
                                $hotime_num  += 2;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 70) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年1H';
                                $hotime_num  += 1;
                            }
                        }
                    } else if ($working_data[$r][18] == 65) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年2H';
                                $hotime_num  += 2;
                            }
                        }
                    } else if ($working_data[$r][18] == 66) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            }
                        }
                    } else if ($working_data[$r][18] == 67) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        }
                    } else if ($working_data[$r][18] == 68) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        }
                    } else if ($working_data[$r][18] == 69) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        }
                    } else if ($working_data[$r][18] == 70) {
                        if ($working_data[$r][23] == 62) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        }
                    } else if ($working_data[$r][23] == 62) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年2H';
                            $hotime_num  += 2;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 70) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年1H';
                            $hotime_num  += 1;
                        }
                    } else if ($working_data[$r][23] == 65) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年2H';
                            $hotime_num  += 2;
                        }
                    } else if ($working_data[$r][23] == 66) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年3H';
                            $hotime_num  += 3;
                        }
                    } else if ($working_data[$r][23] == 67) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        }
                    } else if ($working_data[$r][23] == 68) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        }
                    } else if ($working_data[$r][23] == 69) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        }
                    } else if ($working_data[$r][23] == 70) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        }
                    } else if ($working_data[$r][23] == 58) {
                        $working_data[$r][4] = '社会';
                    } else if ($working_data[$r][23] == 59) {
                        $working_data[$r][4] = '任研';
                    } else if ($working_data[$r][24] == 62) {
                        $working_data[$r][4] = '年1H';
                        $hotime_num  += 1;
                    } else if ($working_data[$r][24] == 65) {
                        $working_data[$r][4] = '年2H';
                        $hotime_num  += 2;
                    } else if ($working_data[$r][24] == 66) {
                        $working_data[$r][4] = '年3H';
                        $hotime_num  += 3;
                    } else if ($working_data[$r][24] == 67) {
                        $working_data[$r][4] = '年4H';
                        $hotime_num  += 4;
                    } else if ($working_data[$r][24] == 68) {
                        $working_data[$r][4] = '年5H';
                        $hotime_num  += 5;
                    } else if ($working_data[$r][24] == 69) {
                        $working_data[$r][4] = '年6H';
                        $hotime_num  += 6;
                    } else if ($working_data[$r][24] == 70) {
                        $working_data[$r][4] = '年7H';
                        $hotime_num  += 7;
                    } else {
                        $working_data[$r][4] = '　';
                    }
                    break;
            }
            // 週報確認フラグの取得
            if ($uid[$t] == '914737') {                                     // 出向者の社員番号の戻し
                $uid[$t] = '014737';
            } else if ($uid[$t] == '920206') {
                $uid[$t] = '020206';
            }
            $confirm = $this->getConfirmData($uid[$t], $working_data[$r][1]);
            $working_data[$r][23] = $confirm;
        }
        $request->add('fixed_num', $fixed_num);
        $request->add('paidho_num', $paidho_num);
        $request->add('work_num', $work_num);
        $request->add('howork_num', $howork_num);
        $request->add('hohalf_num', $hohalf_num);
        $request->add('closure_num', $closure_num);
        $request->add('hotime_num', $hotime_num);
        $request->add('noholy_num', $noholy_num);
        $request->add('rows', $rows);
        $request->add('num', $num);
        return $working_data;
    }
    
    ///// タイムプロデータの取得・変換
    private function getTimeProDataOver($request, $uid, $sid_t, $t)
    {
        $query = $this->getWorkingDataOver($request, $uid[$t], $sid_t[$t]);             // TimeProデータの取得
        $working_data = array();
        $field = array();
        $fixed_num = 0;
        $paidho_num = 0;
        $work_num = 0;
        $howork_num = 0;
        $hohalf_num = 0;
        $closure_num = 0;
        $hotime_num  = 0;
        $noholy_num  = 0;
        if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
            $num = 0;
        } else {
            $num = count($field) + 1;
        }
        for ($r=0; $r<$rows; $r++) {                                    
            // TimeProデータの変換（曜日）
            switch ($working_data[$r][2]) {
                case 0:
                    $working_data[$r][2] = '月';
                    break;
                case 1:
                    $working_data[$r][2] = '火';
                    break;
                case 2:
                    $working_data[$r][2] = '水';
                    break;
                case 3:
                    $working_data[$r][2] = '木';
                    break;
                case 4:
                    $working_data[$r][2] = '金';
                    break;
                case 5:
                    $working_data[$r][2] = '土';
                    break;
                case 6:
                    $working_data[$r][2] = '日';
                    break;
            }
            // TimeProデータの変換（カレンダー）
            switch ($working_data[$r][3]) {
                case 1:
                    $working_data[$r][3] = '____';
                    $fixed_num += 1;
                    break;
                case 2:
                    $working_data[$r][3] = '休日';
                    break;
                case 3:
                    $working_data[$r][3] = '法休';
                    break;
                case 5:
                    $working_data[$r][3] = '休業';
                    $closure_num += 1;
                    break;    
            }
            // TimeProデータの変換（不在理由）
            switch ($working_data[$r][4]) {
                case 11:
                    $working_data[$r][4] = '有休';
                    $paidho_num += 1;                                   // 有休日数のカウント＋１
                    break;
                case 12:
                    $working_data[$r][4] = '欠勤';
                    $noholy_num  += 1;                                  // 欠勤日数のカウント＋１
                    break;
                case 13:
                    $working_data[$r][4] = '無欠';
                    break;
                case 14:
                    $working_data[$r][4] = '出張';
                    $work_num += 1;                                     // 出張の場合は出勤日数＋１
                    break;
                case 15:
                    $working_data[$r][4] = '振休';                      // 基本ない
                    break;
                case 16:
                    $working_data[$r][4] = '特休';                      // 出勤日数はカウントしない
                    break;
                case 17:
                    $working_data[$r][4] = '慶事';                      // 基本無し
                    break;
                case 18:
                    $working_data[$r][4] = '弔事';                      // 基本無し
                    break;
                case 19:
                    $working_data[$r][4] = '産休';                      // 出勤日数はカウントしない
                    break;
                case 20:
                    $working_data[$r][4] = '育休';
                    break;
                case 21:
                    $working_data[$r][4] = '生休';
                    break;
                case 22:
                    $working_data[$r][4] = '休職';
                    break;
                case 23:
                    $working_data[$r][4] = '労災';                      // 基本無し
                    break;
                default:
                    if ($working_data[$r][18] == 41) {
                        $working_data[$r][4] = '半AM';
                        $paidho_num += 0.5;                             // 出勤日数のカウント＋０．５（半休）
                        $hohalf_num += 0.5;                             // 有休日数のカウント＋０．５（半休）
                    } else if ($working_data[$r][23] == 42) {
                        $working_data[$r][4] = '半PM';             
                        $paidho_num += 0.5;                             // 出勤日数のカウント＋０．５（半休）
                        $hohalf_num += 0.5;                             // 有休日数のカウント＋０．５（半休）
                    } else if ($working_data[$r][18] == 62) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年2H';
                                $hotime_num  += 2;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 70) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年2H';
                                $hotime_num  += 2;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 70) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年1H';
                                $hotime_num  += 1;
                            }
                        }
                    } else if ($working_data[$r][18] == 65) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 69) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 69) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年2H';
                                $hotime_num  += 2;
                            }
                        }
                    } else if ($working_data[$r][18] == 66) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 68) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 68) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年3H';
                                $hotime_num  += 3;
                            }
                        }
                    } else if ($working_data[$r][18] == 67) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 67) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 67) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年4H';
                                $hotime_num  += 4;
                            }
                        }
                    } else if ($working_data[$r][18] == 68) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 66) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 66) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年5H';
                                $hotime_num  += 5;
                            }
                        }
                    } else if ($working_data[$r][18] == 69) {
                        if ($working_data[$r][23] == 62) {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        } else if ($working_data[$r][23] == 65) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            } else if ($working_data[$r][24] == 65) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年6H';
                                $hotime_num  += 6;
                            }
                        }
                    } else if ($working_data[$r][18] == 70) {
                        if ($working_data[$r][23] == 62) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            if ($working_data[$r][24] == 62) {
                                $working_data[$r][4] = '年8H';
                                $hotime_num  += 8;
                            } else {
                                $working_data[$r][4] = '年7H';
                                $hotime_num  += 7;
                            }
                        }
                    } else if ($working_data[$r][23] == 62) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年2H';
                            $hotime_num  += 2;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 70) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年1H';
                            $hotime_num  += 1;
                        }
                    } else if ($working_data[$r][23] == 65) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年3H';
                            $hotime_num  += 3;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 69) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年2H';
                            $hotime_num  += 2;
                        }
                    } else if ($working_data[$r][23] == 66) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 68) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年3H';
                            $hotime_num  += 3;
                        }
                    } else if ($working_data[$r][23] == 67) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 67) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年4H';
                            $hotime_num  += 4;
                        }
                    } else if ($working_data[$r][23] == 68) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 66) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年5H';
                            $hotime_num  += 5;
                        }
                    } else if ($working_data[$r][23] == 69) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        } else if ($working_data[$r][24] == 65) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年6H';
                            $hotime_num  += 6;
                        }
                    } else if ($working_data[$r][23] == 70) {
                        if ($working_data[$r][24] == 62) {
                            $working_data[$r][4] = '年8H';
                            $hotime_num  += 8;
                        } else {
                            $working_data[$r][4] = '年7H';
                            $hotime_num  += 7;
                        }
                    } else if ($working_data[$r][23] == 58) {
                        $working_data[$r][4] = '社会';
                    } else if ($working_data[$r][23] == 59) {
                        $working_data[$r][4] = '任研';
                    } else if ($working_data[$r][24] == 62) {
                        $working_data[$r][4] = '年1H';
                        $hotime_num  += 1;
                    } else if ($working_data[$r][24] == 65) {
                        $working_data[$r][4] = '年2H';
                        $hotime_num  += 2;
                    } else if ($working_data[$r][24] == 66) {
                        $working_data[$r][4] = '年3H';
                        $hotime_num  += 3;
                    } else if ($working_data[$r][24] == 67) {
                        $working_data[$r][4] = '年4H';
                        $hotime_num  += 4;
                    } else if ($working_data[$r][24] == 68) {
                        $working_data[$r][4] = '年5H';
                        $hotime_num  += 5;
                    } else if ($working_data[$r][24] == 69) {
                        $working_data[$r][4] = '年6H';
                        $hotime_num  += 6;
                    } else if ($working_data[$r][24] == 70) {
                        $working_data[$r][4] = '年7H';
                        $hotime_num  += 7;
                    } else {
                        $working_data[$r][4] = '　';
                    }
                    break;
            }
            // 週報確認フラグの取得
            if ($uid[$t] == '914737') {                                     // 出向者の社員番号の戻し
                $uid[$t] = '014737';
            } else if ($uid[$t] == '920206') {
                $uid[$t] = '020206';
            }
            $confirm = $this->getConfirmData($uid[$t], $working_data[$r][1]);
            $working_data[$r][23] = $confirm;
        }
        $request->add('fixed_num', $fixed_num);
        $request->add('paidho_num', $paidho_num);
        $request->add('work_num', $work_num);
        $request->add('howork_num', $howork_num);
        $request->add('hohalf_num', $hohalf_num);
        $request->add('closure_num', $closure_num);
        $request->add('hotime_num', $hotime_num);
        $request->add('noholy_num', $noholy_num);
        $request->add('rows', $rows);
        $request->add('num', $num);
        return $working_data;
    }
    ///// タイムプロデータの取得・変換
    private function getTimeProDataErr($request, $uid, $t)
    {
        $query = $this->getWorkingData($request, $uid[$t]);             // TimeProデータの取得
        $working_data = array();
        $field = array();
        $fixed_num = 0;
        $paidho_num = 0;
        $work_num = 0;
        $howork_num = 0;
        $hohalf_num = 0;
        $closure_num = 0;
        $hotime_num  = 0;
        $noholy_num  = 0;
        if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
            $num = 0;
        } else {
            $num = count($field) + 1;
        }
        for ($r=0; $r<$rows; $r++) {                                    
            // TimeProデータの変換（曜日）
            switch ($working_data[$r][2]) {
                case 0:
                    $working_data[$r][2] = '月';
                    break;
                case 1:
                    $working_data[$r][2] = '火';
                    break;
                case 2:
                    $working_data[$r][2] = '水';
                    break;
                case 3:
                    $working_data[$r][2] = '木';
                    break;
                case 4:
                    $working_data[$r][2] = '金';
                    break;
                case 5:
                    $working_data[$r][2] = '土';
                    break;
                case 6:
                    $working_data[$r][2] = '日';
                    break;
            }
            // TimeProデータの変換（カレンダー）
            switch ($working_data[$r][3]) {
                case 1:
                    $working_data[$r][3] = '____';
                    $fixed_num += 1;
                    break;
                case 2:
                    $working_data[$r][3] = '休日';
                    break;
                case 3:
                    $working_data[$r][3] = '法休';
                    break;
                case 5:
                    $working_data[$r][3] = '休業';
                    $closure_num += 1;
                    break;    
            }
            // TimeProデータの変換（不在理由）
            switch ($working_data[$r][4]) {
                case 11:
                    $working_data[$r][4] = '有休';
                    $paidho_num += 1;                                   // 有休日数のカウント＋１
                    break;
                case 12:
                    $working_data[$r][4] = '欠勤';
                    $noholy_num  += 1;                                  // 欠勤日数のカウント＋１
                    break;
                case 13:
                    $working_data[$r][4] = '無欠';
                    break;
                case 14:
                    $working_data[$r][4] = '出張';
                    $work_num += 1;                                     // 出張の場合は出勤日数＋１
                    break;
                case 15:
                    $working_data[$r][4] = '振休';                      // 基本ない
                    break;
                case 16:
                    $working_data[$r][4] = '特休';                      // 出勤日数はカウントしない
                    break;
                case 17:
                    $working_data[$r][4] = '慶事';                      // 基本無し
                    break;
                case 18:
                    $working_data[$r][4] = '弔事';                      // 基本無し
                    break;
                case 19:
                    $working_data[$r][4] = '産休';                      // 出勤日数はカウントしない
                    break;
                case 20:
                    $working_data[$r][4] = '育休';
                    break;
                case 21:
                    $working_data[$r][4] = '生休';
                    break;
                case 22:
                    $working_data[$r][4] = '休職';
                    break;
                case 23:
                    $working_data[$r][4] = '労災';                      // 基本無し
                    break;
                default:
                    if ($working_data[$r][18] == 41) {
                        $working_data[$r][4] = '半休';
                        $paidho_num += 0.5;                             // 出勤日数のカウント＋０．５（半休）
                        $hohalf_num += 0.5;                             // 有休日数のカウント＋０．５（半休）
                    } else if ($working_data[$r][18] == 42) {
                        $working_data[$r][4] = '半休';             
                        $paidho_num += 0.5;                             // 出勤日数のカウント＋０．５（半休）
                        $hohalf_num += 0.5;                             // 有休日数のカウント＋０．５（半休）
                    } else {
                        $working_data[$r][4] = '　';
                    }
                    break;
            }
            // 週報確認フラグの取得
            if ($uid[$t] == '914737') {                                     // 出向者の社員番号の戻し
                $uid[$t] = '014737';
            } else if ($uid[$t] == '920206') {
                $uid[$t] = '020206';
            }
            $confirm = $this->getConfirmData($uid[$t], $working_data[$r][1]);
            $working_data[$r][23] = $confirm;
        }
        $request->add('fixed_num', $fixed_num);
        $request->add('paidho_num', $paidho_num);
        $request->add('work_num', $work_num);
        $request->add('howork_num', $howork_num);
        $request->add('hohalf_num', $hohalf_num);
        $request->add('closure_num', $closure_num);
        $request->add('hotime_num', $hotime_num);
        $request->add('noholy_num', $noholy_num);
        $request->add('rows', $rows);
        $request->add('num', $num);
        return $working_data;
    }
    
    ///// 日付・対象部門(社員番号) から就業データの取得
    private function getWorkingData($request, $uid)
    {
        /*
        $query = "SELECT substr(timepro, 3, 6) AS 社員番号    -- 00
                      ,substr(timepro, 17, 8)  AS 年月        -- 01
                      ,substr(timepro, 25, 2)  AS 曜日        -- 02
                      ,substr(timepro, 27, 2)  AS カレンダ    -- 03
                      ,substr(timepro, 173, 2) AS 不在理由    -- 04
                      ,substr(timepro, 33, 4)  AS 出勤時間    -- 05
                      ,substr(timepro, 41, 4)  AS 退勤時間    -- 06
                      ,substr(timepro, 79, 6)  AS 所定時間    -- 07
                      ,substr(timepro, 97, 6)  AS 延長時間    -- 08
                      ,substr(timepro, 85, 6)  AS 早出時間    -- 09
                      ,substr(timepro, 91, 6)  AS 残業時間    -- 10
                      ,substr(timepro, 109, 6) AS 深夜残業    -- 11
                      ,substr(timepro, 115, 6) AS 休出時間    -- 12
                      ,substr(timepro, 121, 6) AS 休出残業    -- 13
                      ,substr(timepro, 127, 6) AS 休出深夜    -- 14
                      ,substr(timepro, 155, 6) AS 法定時間    -- 15
                      ,substr(timepro, 161, 6) AS 法定残業    -- 16
                      ,substr(timepro, 133, 6) AS 遅早時間    -- 17
                      ,substr(timepro, 37, 2)  AS 出勤ＭＣ    -- 18
                      ,substr(timepro, 103, 6) AS 深夜早出    -- 19
                      ,substr(timepro, 167, 6) AS 法定深夜    -- 20
                      ,substr(timepro, 139, 6) AS 私用外出    -- 21
                      ,substr(timepro, 175, 1) AS 集計区分    -- 22
                      ,substr(timepro, 45, 2)  AS 退勤ＭＣ    -- 23
                   FROM timepro_daily_data 
                   WHERE substr(timepro, 3, 6)='{$uid}'
                   AND substr(timepro, 17, 8) >= {$request->get('targetDateStr')} AND substr(timepro, 17, 8) <= {$request->get('targetDateEnd')} 
                   ORDER BY 社員番号 , 年月;
        ";
        */
        $query = "SELECT uid AS 社員番号    -- 00
                      ,working_date  AS 年月        -- 01
                      ,working_day  AS 曜日        -- 02
                      ,calendar  AS カレンダ    -- 03
                      ,absence AS 不在理由    -- 04
                      ,str_time  AS 出勤時間    -- 05
                      ,end_time        AS 退勤時間    -- 06
                      ,fixed_time  AS 所定時間    -- 07
                      ,extend_time  AS 延長時間    -- 08
                      ,earlytime  AS 早出時間    -- 09
                      ,overtime  AS 残業時間    -- 10
                      ,midnight_over AS 深夜残業    -- 11
                      ,holiday_time AS 休出時間    -- 12
                      ,holiday_over AS 休出残業    -- 13
                      ,holiday_mid AS 休出深夜    -- 14
                      ,legal_time AS 法定時間    -- 15
                      ,legal_over AS 法定残業    -- 16
                      ,late_time AS 遅早時間    -- 17
                      ,str_mc  AS 出勤ＭＣ    -- 18
                      ,early_mid AS 深夜早出    -- 19
                      ,legal_mid AS 法定深夜    -- 20
                      ,private_out AS 私用外出    -- 21
                      ,total_div AS 集計区分    -- 22
                      ,end_mc  AS 退勤ＭＣ    -- 23
                      ,out_mc  AS 外出ＭＣ    -- 24
                   FROM working_hours_report_data_new
                   WHERE uid = '{$uid}'
                   AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                   ORDER BY 社員番号 , 年月;
        ";
        return $query;
    }
    
    ///// 日付・対象部門(社員番号) から就業データの取得 残業有りのみ
    private function getWorkingDataOver($request, $uid, $sid)
    {
        if ($sid == 19) {
            if( $request->get('targetSection') == '-6' ) {
            $query = "SELECT uid AS 社員番号    -- 00
                          ,working_date  AS 年月        -- 01
                          ,working_day  AS 曜日        -- 02
                          ,calendar  AS カレンダ    -- 03
                          ,absence AS 不在理由    -- 04
                          ,str_time  AS 出勤時間    -- 05
                          ,end_time        AS 退勤時間    -- 06
                          ,fixed_time  AS 所定時間    -- 07
                          ,extend_time  AS 延長時間    -- 08
                          ,earlytime  AS 早出時間    -- 09
                          ,overtime  AS 残業時間    -- 10
                          ,midnight_over AS 深夜残業    -- 11
                          ,holiday_time AS 休出時間    -- 12
                          ,holiday_over AS 休出残業    -- 13
                          ,holiday_mid AS 休出深夜    -- 14
                          ,legal_time AS 法定時間    -- 15
                          ,legal_over AS 法定残業    -- 16
                          ,late_time AS 遅早時間    -- 17
                          ,str_mc  AS 出勤ＭＣ    -- 18
                          ,early_mid AS 深夜早出    -- 19
                          ,legal_mid AS 法定深夜    -- 20
                          ,private_out AS 私用外出    -- 21
                          ,total_div AS 集計区分    -- 22
                          ,end_mc  AS 退勤ＭＣ    -- 23
                          ,out_mc  AS 外出ＭＣ    -- 24
                       FROM working_hours_report_data_new
                       WHERE uid = '{$uid}'
                       AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                       AND end_mc <> '58' AND end_mc <> '59'
                       AND (((earlytime <> '000000' OR overtime <> '000000' OR midnight_over <> '000000' OR holiday_time <> '000000'
                       OR holiday_over <> '000000' OR holiday_mid <> '000000' OR legal_time <> '000000' OR legal_over <> '000000'
                       OR early_mid <> '000000' OR legal_mid <> '000000')) OR (TO_NUMBER(end_time, '000000') >= 1830))
                       ORDER BY 社員番号 , 年月;
            ";
            } else {
            $query = "SELECT uid AS 社員番号    -- 00
                          ,working_date  AS 年月        -- 01
                          ,working_day  AS 曜日        -- 02
                          ,calendar  AS カレンダ    -- 03
                          ,absence AS 不在理由    -- 04
                          ,str_time  AS 出勤時間    -- 05
                          ,end_time        AS 退勤時間    -- 06
                          ,fixed_time  AS 所定時間    -- 07
                          ,extend_time  AS 延長時間    -- 08
                          ,earlytime  AS 早出時間    -- 09
                          ,overtime  AS 残業時間    -- 10
                          ,midnight_over AS 深夜残業    -- 11
                          ,holiday_time AS 休出時間    -- 12
                          ,holiday_over AS 休出残業    -- 13
                          ,holiday_mid AS 休出深夜    -- 14
                          ,legal_time AS 法定時間    -- 15
                          ,legal_over AS 法定残業    -- 16
                          ,late_time AS 遅早時間    -- 17
                          ,str_mc  AS 出勤ＭＣ    -- 18
                          ,early_mid AS 深夜早出    -- 19
                          ,legal_mid AS 法定深夜    -- 20
                          ,private_out AS 私用外出    -- 21
                          ,total_div AS 集計区分    -- 22
                          ,end_mc  AS 退勤ＭＣ    -- 23
                          ,out_mc  AS 外出ＭＣ    -- 24
                       FROM working_hours_report_data_new
                       WHERE uid = '{$uid}'
                       AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                       AND end_mc <> '58' AND end_mc <> '59'
                       AND (((extend_time <> '000000' OR earlytime <> '000000' OR overtime <> '000000' OR midnight_over <> '000000' OR holiday_time <> '000000'
                       OR holiday_over <> '000000' OR holiday_mid <> '000000' OR legal_time <> '000000' OR legal_over <> '000000'
                       OR early_mid <> '000000' OR legal_mid <> '000000')) OR (TO_NUMBER(end_time, '000000') >= 1830))
                       ORDER BY 社員番号 , 年月;
            ";
            }
        } else {
            if( $request->get('targetSection') == '-6' ) {
            $query = "SELECT uid AS 社員番号    -- 00
                          ,working_date  AS 年月        -- 01
                          ,working_day  AS 曜日        -- 02
                          ,calendar  AS カレンダ    -- 03
                          ,absence AS 不在理由    -- 04
                          ,str_time  AS 出勤時間    -- 05
                          ,end_time        AS 退勤時間    -- 06
                          ,fixed_time  AS 所定時間    -- 07
                          ,extend_time  AS 延長時間    -- 08
                          ,earlytime  AS 早出時間    -- 09
                          ,overtime  AS 残業時間    -- 10
                          ,midnight_over AS 深夜残業    -- 11
                          ,holiday_time AS 休出時間    -- 12
                          ,holiday_over AS 休出残業    -- 13
                          ,holiday_mid AS 休出深夜    -- 14
                          ,legal_time AS 法定時間    -- 15
                          ,legal_over AS 法定残業    -- 16
                          ,late_time AS 遅早時間    -- 17
                          ,str_mc  AS 出勤ＭＣ    -- 18
                          ,early_mid AS 深夜早出    -- 19
                          ,legal_mid AS 法定深夜    -- 20
                          ,private_out AS 私用外出    -- 21
                          ,total_div AS 集計区分    -- 22
                          ,end_mc  AS 退勤ＭＣ    -- 23
                          ,out_mc  AS 外出ＭＣ    -- 24
                       FROM working_hours_report_data_new
                       WHERE uid = '{$uid}'
                       AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                       AND end_mc <> '58' AND end_mc <> '59'
                       AND (((earlytime <> '000000' OR overtime <> '000000' OR midnight_over <> '000000' OR holiday_time <> '000000'
                       OR holiday_over <> '000000' OR holiday_mid <> '000000' OR legal_time <> '000000' OR legal_over <> '000000'
                       OR early_mid <> '000000' OR legal_mid <> '000000')) OR (TO_NUMBER(end_time, '000000') >= 1800))
                       ORDER BY 社員番号 , 年月;
            ";
            } else {
            $query = "SELECT uid AS 社員番号    -- 00
                          ,working_date  AS 年月        -- 01
                          ,working_day  AS 曜日        -- 02
                          ,calendar  AS カレンダ    -- 03
                          ,absence AS 不在理由    -- 04
                          ,str_time  AS 出勤時間    -- 05
                          ,end_time        AS 退勤時間    -- 06
                          ,fixed_time  AS 所定時間    -- 07
                          ,extend_time  AS 延長時間    -- 08
                          ,earlytime  AS 早出時間    -- 09
                          ,overtime  AS 残業時間    -- 10
                          ,midnight_over AS 深夜残業    -- 11
                          ,holiday_time AS 休出時間    -- 12
                          ,holiday_over AS 休出残業    -- 13
                          ,holiday_mid AS 休出深夜    -- 14
                          ,legal_time AS 法定時間    -- 15
                          ,legal_over AS 法定残業    -- 16
                          ,late_time AS 遅早時間    -- 17
                          ,str_mc  AS 出勤ＭＣ    -- 18
                          ,early_mid AS 深夜早出    -- 19
                          ,legal_mid AS 法定深夜    -- 20
                          ,private_out AS 私用外出    -- 21
                          ,total_div AS 集計区分    -- 22
                          ,end_mc  AS 退勤ＭＣ    -- 23
                          ,out_mc  AS 外出ＭＣ    -- 24
                       FROM working_hours_report_data_new
                       WHERE uid = '{$uid}'
                       AND working_date >= {$request->get('targetDateStr')} AND working_date <= {$request->get('targetDateEnd')} 
                       AND end_mc <> '58' AND end_mc <> '59'
                       AND (((extend_time <> '000000' OR earlytime <> '000000' OR overtime <> '000000' OR midnight_over <> '000000' OR holiday_time <> '000000'
                       OR holiday_over <> '000000' OR holiday_mid <> '000000' OR legal_time <> '000000' OR legal_over <> '000000'
                       OR early_mid <> '000000' OR legal_mid <> '000000')) OR (TO_NUMBER(end_time, '000000') >= 1800))
                       ORDER BY 社員番号 , 年月;
            ";
            }
        }
        return $query;
    }
    ///// 社員番号・就業日より週報の確認を取得
    private function getConfirmData($uid, $working_date)
    {
    $query="SELECT confirm   -- 00
            FROM working_hours_report_confirm
            WHERE uid='{$uid}' AND working_date={$working_date}
        ";
        $res = array();
        $confirm = '';
        if ($this->getResult2($query, $res) < 1) {
            $confirm = '未';
            return $confirm;
        } else {
            if ($res[0][0] == 't') {
                $confirm = '済';
            } else {
                $confirm = '未';
            }
            return $confirm;
        }
    }
    
    ///// 社員名を取得
    private function getUserName($uid)
    {
        $query = "
            SELECT trim(name)
            FROM
                user_detailes
            LEFT OUTER JOIN
                user_master USING(uid)
            WHERE
                uid = '{$uid}'
        ";
        $res = array();                        // 初期化
        if ($this->getResult2($query, $res) < 1) {
            $user_name = '未登録';
            return $user_name;
        } else {
            $user_name = $res[0][0];
            return $user_name;
        }
    }
    
    ///// List部   就業週報照会の訂正内容一覧データ作成
    private function getViewCorrectHTMLbody($request, $menu, $endflg)
    {
        // 初期化
        $listTable  = '';
        $query = $this->getSectionUser($request->get('targetSection'), $request->get('targetPosition'), $request->get('targetDateStr')); // 選択部門より所属社員を取得
        if ($rows=getResult($query,$res)) {
            for ($i=0; $i<$rows; $i++) {
                $uid[$i]   = $res[$i]['uid'];
            }
            // 今はいないのでコメント化
            /*
            if ($request->get('targetSection') == 4) {                  // 管理部所属社員の対応
                $uid[$i] = '000817';                                    // 000817=管理部 小林さん
                $res[$i]['sid'] = 9;                                    // sidに管理部を追加
                $rows = $rows + 1;                                      // sid追加の為$rowsも１追加
            }
            */
            $s_name = $this->getSectionName($rows,$res);                // 選択部門の部門コードより部門名を取得
        } else {
            $uid    = '------';
            $s_name ='----------';
        }
        $uid_num = count($uid);
        for ($t=0; $t<$uid_num; $t++) {                                     // 出向者の社員番号の変換(TimeProデータ)
            if ($uid[$t] == '014737') {                                     // 出向者の追加変更あれば下の戻しも同時に変更
                $uid[$t] = '914737';                                        // 014737=総務課 桝さん
            } else if ($uid[$t] == '020273') {                              // 020273=技術課 佐藤さん
                $uid[$t] = '920273';
            }
        }
        //if ($endflg == 't') {                                // 訂正済一覧か訂正内容一覧か判別
        //    $correct_data = $this->getCorrectEndData();    // 訂正済一覧の取得
        //} else {
        //$correct_data = $this->getCorrectData($request, $uid);       // 訂正内容一覧の取得
        //}
        //$rows = count($correct_data);
        //if ($correct_data != '') {
        //    for ($i=0; $i<$rows; $i++) {
        //        $user_name[$i] = $this->getUserName($correct_data[$i][0]);    // 社員番号より社員名を取得
        //    }
        //}
        $correct_data = array();
        $crr_num      = 0;
        $str_date = $request->get('targetDateStr');
        $end_date = $request->get('targetDateEnd');
        for ($t=0; $t<$uid_num; $t++) {
            $query = sprintf("SELECT * FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d AND working_enddate=%d AND confirm_flg > 1", $uid[$t], $str_date, $end_date);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
                $correct_data[$crr_num] = $res_chk[0];
                $crr_num = $crr_num + 1;
            } else {                                    // 登録無し なにもしない
                
            }
        }
        $rows = count($correct_data);
        if ($correct_data != '') {
            for ($i=0; $i<$rows; $i++) {
                $user_name[$i] = $this->getUserName($correct_data[$i][0]);    // 社員番号より社員名を取得
            }
        }
        $listTable .= "<center>\n";
        $listTable .= "    <form name='CorrectForm' method='post' target='_parent'>\n";
        $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "        <THEAD>\n";
        $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
        $listTable .= "            <tr>\n";
        $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
        $listTable .= "                <th class='winbox' nowrap>社員番号</th>\n";
        $listTable .= "                <th class='winbox' nowrap>社員名</th>\n";
        $listTable .= "                <th class='winbox' nowrap>開始年月日</th>\n";
        $listTable .= "                <th class='winbox' nowrap>終了年月日</th>\n";
        $listTable .= "                <th class='winbox' nowrap>確認内容</th>\n";
        $listTable .= "            </tr>\n";
        $listTable .= "        </THEAD>\n";
        $listTable .= "        <TFOOT>\n";
        $listTable .= "            <!-- 現在はフッターは何もない -->\n";
        $listTable .= "        </TFOOT>\n";
        $listTable .= "        <TBODY>\n";
        if ($correct_data == '') {
            $listTable .= "        <tr>\n";
            $listTable .= "            <td class='winbox' colspan='6' nowrap align='center'><div class='pt9'>訂正内容の登録がありません</div></td>\n";
            $listTable .= "        </tr>\n";
        } else {
            for ($r=0; $r<$rows; $r++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' nowrap align='right'>\n";
                $cnum = $r + 1;
                $listTable .= "        ". $cnum ."\n";
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $correct_data[$r][0] ."</div></td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $user_name[$r] ."</div></td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $correct_data[$r][1] ."</div></td>\n";
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>". $correct_data[$r][2] ."</div></td>\n";
                if ($correct_data[$r][4] == 2) {
                    $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>届出提出済</div></td>\n";
                } else {
                    $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'>届出依頼済</div></td>\n";
                }
                
                /*
                if ($endflg == 't') {
                    $listTable .= "        <td class='winbox' nowrap align='left'><input type='button' name='exec3' value='訂正取消' onClick='WorkingHoursReport.Correctexecute(". $correct_data[$r][0] .", ". $correct_data[$r][1] .", 1);' title='クリックすれば、訂正済を取り消します。'></td>\n";
                } else {
                    $listTable .= "        <td class='winbox' nowrap align='left'><input type='button' name='exec3' value='訂正済' onClick='WorkingHoursReport.Correctexecute(". $correct_data[$r][0] .", ". $correct_data[$r][1] .", 2);' title='クリックすれば、訂正済にします。'></td>\n";
                }
                */
            }
            $listTable .= "        </tr>\n";
        }
        $listTable .= "        </TBODY>\n";
        $listTable .= "        </table>\n";
        $listTable .= "            </td></tr>\n";
        $listTable .= "        </table> <!----------------- ダミーEnd ------------------>\n";
        $listTable .= "    </form>\n";
        $listTable .= "</center>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    // 訂正内容のうち訂正済以外のデータを取得
    private function getCorrectData ($request)
    {
        $query = "
            SELECT  uid                AS 社員番号     -- 0
                ,   working_date       AS 就業年月日   -- 1
                ,   correct_contents   AS 訂正内容     -- 2
            FROM
                working_hours_report_confirm
            WHERE 
                correct = 'f'
            ORDER BY
                working_date
        ";
        $res = array();
        if ($rows=$this->getResult2($query, $res) < 1) {
            $res = '';
            return $res;
        } else {
            return $res;
        }
    }
    // 訂正済みデータの取得
    private function getCorrectEndData ()
    {
        $query = "
            SELECT  uid                AS 社員番号     -- 0
                ,   working_date       AS 就業年月日   -- 1
                ,   correct_contents   AS 訂正内容     -- 2
            FROM
                working_hours_report_correct
            WHERE 
                correct = 't'
            ORDER BY
                working_date
        ";
        $res = array();
        if ($rows=$this->getResult2($query, $res) < 1) {
            $res = '';
            return $res;
        } else {
            return $res;
        }
    }
    ///// 訂正の完了・取消
    public function setCorrectData($request)
    {
        $uid              = $request->get('user_id');
        $uid              = sprintf('%06d', $uid);
        $working_date     = $request->get('date');
        $query = sprintf("SELECT * FROM working_hours_report_correct WHERE uid='%s' AND working_date=%d", $uid, $working_date);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            if ($request->get('CancelFlg') == 'n') {
                $query = sprintf("UPDATE working_hours_report_correct SET correct=TRUE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE uid='%s' AND working_date='%s'", $_SESSION['User_ID'], $uid, $working_date);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}を訂正済に出来ません！";      // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}を訂正済にしました！"; // .= に注意
                    return true;
                }
            } else {
                $query = sprintf("UPDATE working_hours_report_correct SET correct=FALSE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE uid='%s' AND working_date='%s'", $_SESSION['User_ID'], $uid, $working_date);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}を未訂正に出来ません！";      // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}を未訂正にしました！"; // .= に注意
                    return true;
                }
            }
        } else {                                    // 登録なしエラー
            $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}を訂正済に出来ません！2";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    }
    ///// List部   就業週報照会の週報確認一覧データ作成
    private function getViewConfirmHTMLbody($request, $menu)
    {
        // 初期化
        $request->add('check_flg', 'y');
        $listTable  = '';
        $section_name = $this->getTargetSectionConfirm();            // 週報確認一覧用 部門名の取得
        $rows = count($section_name);
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');  // ヘッダー表示用年月計算
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            if ($mm_b == 1) {
                $mm_b = '01';
            }
            if ($mm_b == 2) {
                $mm_b = '02';
            }
            if ($mm_b == 3) {
                $mm_b = '03';
            }
            if ($mm_b == 4) {
                $mm_b = '04';
            }
            if ($mm_b == 5) {
                $mm_b = '05';
            }
            if ($mm_b == 6) {
                $mm_b = '06';
            }
            if ($mm_b == 7) {
                $mm_b = '07';
            }
            if ($mm_b == 8) {
                $mm_b = '08';
            }
            if ($mm_b == 9) {
                $mm_b = '09';
            }
            $yyyymm_b = $yyyy . $mm_b;
        }
        $listTable .= "<center>\n";
        $listTable .= "    <form name='CorrectForm' method='post' target='_parent'>\n";
        $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "        <THEAD>\n";
        $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
        $listTable .= "            <tr>\n";
        $listTable .= "                <th class='winbox' nowrap>部門</th>\n";
        $listTable .= "                <th class='winbox' nowrap>日付</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy_b . "年". $mm_b ."月</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy . "年". $mm ."月</th>\n";
        $listTable .= "            </tr>\n";
        $listTable .= "        </THEAD>\n";
        $listTable .= "        <TFOOT>\n";
        $listTable .= "            <!-- 現在はフッターは何もない -->\n";
        $listTable .= "        </TFOOT>\n";
        $listTable .= "        <TBODY>\n";
        for ($r=0; $r<$rows; $r++) {
            $section_confirm = $this->getSectionConfirm($section_name[$r][1], $request); // 週報確認データの取得
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox' rowspan='3' nowrap>". $section_name[$r][0] ."</th>\n";
            $listTable .= "        <th class='winbox' nowrap>１日～１０日</th>\n";
            if ($section_confirm[0] == '未') {
                $targetDateStr = $yyyymm_b . '01';
                $targetDateEnd = $yyyymm_b . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[0] ."</a></B></div></td>\n";
            } else if ($section_confirm[0] == '済') {
                $targetDateStr = $yyyymm_b . '01';
                $targetDateEnd = $yyyymm_b . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[0] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[0] ."</B></div></td>\n";
            }
            if ($section_confirm[1] == '未') {
                $targetDateStr = $request->get('yyyymm') . '01';
                $targetDateEnd = $request->get('yyyymm') . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[1] ."</a></B></div></td>\n";
            } else if ($section_confirm[1] == '済') {
                $targetDateStr = $request->get('yyyymm') . '01';
                $targetDateEnd = $request->get('yyyymm') . '10';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[1] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[1] ."</B></div></td>\n";
            }
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox' nowrap>１１日～２０日</th>\n";
            if ($section_confirm[2] == '未') {
                $targetDateStr = $yyyymm_b . '11';
                $targetDateEnd = $yyyymm_b . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[2] ."</a></B></div></td>\n";
            } else if ($section_confirm[2] == '済') {
                $targetDateStr = $yyyymm_b . '11';
                $targetDateEnd = $yyyymm_b . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[2] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[2] ."</B></div></td>\n";
            }
            if ($section_confirm[3] == '未') {
                $targetDateStr = $request->get('yyyymm') . '11';
                $targetDateEnd = $request->get('yyyymm') . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[3] ."</a></B></div></td>\n";
            } else if ($section_confirm[3] == '済') {
                $targetDateStr = $request->get('yyyymm') . '11';
                $targetDateEnd = $request->get('yyyymm') . '20';
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[3] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[3] ."</B></div></td>\n";
            }
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox' nowrap>２０日～末日</th>\n";
            if ($section_confirm[4] == '未') {
                $targetDateStr = $yyyymm_b . '21';
                $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[4] ."</a></B></div></td>\n";
            } else if ($section_confirm[4] == '済') {
                $targetDateStr = $yyyymm_b . '21';
                $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[4] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[4] ."</B></div></td>\n";
            }
            if ($section_confirm[5] == '未') {
                $targetDateStr = $request->get('yyyymm') . '21';
                $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[5] ."</a></B></div></td>\n";
            } else if ($section_confirm[5] == '済') {
                $targetDateStr = $request->get('yyyymm') . '21';
                $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?AutoStart=y&targetSection=". $section_name[$r][1] ."&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;'>". $section_confirm[5] ."</a></B></div></td>\n";
            } else {
                $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[5] ."</B></div></td>\n";
            }
        }
        $listTable .= "        </tr>\n";
        $listTable .= "        </TBODY>\n";
        $listTable .= "        </table>\n";
        $listTable .= "            </td></tr>\n";
        $listTable .= "        </table> <!----------------- ダミーEnd ------------------>\n";
        $listTable .= "    </form>\n";
        $listTable .= "</center>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   就業週報照会の週報確認一覧データ作成
    private function getViewMailHTMLbody($request, $menu)
    {
        // 初期化
        $listTable  = '';
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');  // ヘッダー表示用年月計算
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            if ($mm_b == 1) {
                $mm_b = '01';
            }
            if ($mm_b == 2) {
                $mm_b = '02';
            }
            if ($mm_b == 3) {
                $mm_b = '03';
            }
            if ($mm_b == 4) {
                $mm_b = '04';
            }
            if ($mm_b == 5) {
                $mm_b = '05';
            }
            if ($mm_b == 6) {
                $mm_b = '06';
            }
            if ($mm_b == 7) {
                $mm_b = '07';
            }
            if ($mm_b == 8) {
                $mm_b = '08';
            }
            if ($mm_b == 9) {
                $mm_b = '09';
            }
            $yyyymm_b = $yyyy . $mm_b;
        }
        $test_str = 0;
        $test_end = 0;
        
        if ($request->get('targetDateStr') != '') {
            $test_str = $request->get('targetDateStr');
            $test_end = $request->get('targetDateEnd');
        } else {
            $test_str = 1;
            $test_end = 1;
        }
        $listTable .= "<center>\n";
        $listTable .= "    <form name='CorrectForm' method='post' target='_parent'>\n";
        $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "        <THEAD>\n";
        $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
        $listTable .= "            <tr>\n";
        $listTable .= "                <th class='winbox' nowrap>部門</th>\n";
        $listTable .= "                <th class='winbox' nowrap>日付</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy_b . "年". $mm_b ."月</th>\n";
        $listTable .= "                <th class='winbox' nowrap>". $yyyy . "年". $mm ."月</th>\n";
        $listTable .= "            </tr>\n";
        $listTable .= "        </THEAD>\n";
        $listTable .= "        <TFOOT>\n";
        $listTable .= "            <!-- 現在はフッターは何もない -->\n";
        $listTable .= "        </TFOOT>\n";
        $listTable .= "        <TBODY>\n";
        $section_confirm = $this->getMailConfirm($request); // メールデータの取得
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' rowspan='3' nowrap>". $test_str . "メール送信". $test_end . "</th>\n";
        $listTable .= "        <th class='winbox' nowrap>１日～１０日</th>\n";
        if ($section_confirm[0] == '未') {
            $targetDateStr = $yyyymm_b . '01';
            $targetDateEnd = $yyyymm_b . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[0] ."</a></B></div></td>\n";
        } else if ($section_confirm[0] == '済') {
            $targetDateStr = $yyyymm_b . '01';
            $targetDateEnd = $yyyymm_b . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>済</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[0] ."</B></div></td>\n";
        }
        if ($section_confirm[1] == '未') {
            $targetDateStr = $request->get('yyyymm') . '01';
            $targetDateEnd = $request->get('yyyymm') . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[1] ."</a></B></div></td>\n";
        } else if ($section_confirm[1] == '済') {
            $targetDateStr = $request->get('yyyymm') . '01';
            $targetDateEnd = $request->get('yyyymm') . '10';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>済</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[1] ."</B></div></td>\n";
        }
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' nowrap>１１日～２０日</th>\n";
        if ($section_confirm[2] == '未') {
            $targetDateStr = $yyyymm_b . '11';
            $targetDateEnd = $yyyymm_b . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[2] ."</a></B></div></td>\n";
        } else if ($section_confirm[2] == '済') {
            $targetDateStr = $yyyymm_b . '11';
            $targetDateEnd = $yyyymm_b . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>済</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[2] ."</B></div></td>\n";
        }
        if ($section_confirm[3] == '未') {
            $targetDateStr = $request->get('yyyymm') . '11';
            $targetDateEnd = $request->get('yyyymm') . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[3] ."</a></B></div></td>\n";
        } else if ($section_confirm[3] == '済') {
            $targetDateStr = $request->get('yyyymm') . '11';
            $targetDateEnd = $request->get('yyyymm') . '20';
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>済</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[3] ."</B></div></td>\n";
        }
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' nowrap>２０日～末日</th>\n";
        if ($section_confirm[4] == '未') {
            $targetDateStr = $yyyymm_b . '21';
            $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[4] ."</a></B></div></td>\n";
        } else if ($section_confirm[4] == '済') {
            $targetDateStr = $yyyymm_b . '21';
            $targetDateEnd = $yyyymm_b . $request->get('last_day_b');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>済</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[4] ."</B></div></td>\n";
        }
        if ($section_confirm[5] == '未') {
            $targetDateStr = $request->get('yyyymm') . '21';
            $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B><a href='../working_hours_report_Main.php?MailStart=y&targetDateStr=". $targetDateStr ."&targetDateEnd=". $targetDateEnd ."&check_flg=y' target='_parent' style='text-decoration:none;color:red;'>". $section_confirm[5] ."</a></B></div></td>\n";
        } else if ($section_confirm[5] == '済') {
            $targetDateStr = $request->get('yyyymm') . '21';
            $targetDateEnd = $request->get('yyyymm') . $request->get('last_day');
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>済</B></div></td>\n";
        } else {
            $listTable .= "        <td class='winbox' nowrap align='center'><div class='pt9'><B>". $section_confirm[5] ."</B></div></td>\n";
        }
        $listTable .= "        </tr>\n";
        $listTable .= "        </TBODY>\n";
        $listTable .= "        </table>\n";
        $listTable .= "            </td></tr>\n";
        $listTable .= "        </table> <!----------------- ダミーEnd ------------------>\n";
        $listTable .= "    </form>\n";
        $listTable .= "</center>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// 部門IDから部門の週報確認状況を取得
    private function getSectionConfirm($sid, $request)
    {
        // 日付計算
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            $yyyymm_b = $yyyy . $mm_b;
        }
        if ($mm == 1) {
            $mm = '01';
        }
        if ($mm == 2) {
            $mm = '02';
        }
        if ($mm == 3) {
            $mm = '03';
        }
        if ($mm == 4) {
            $mm = '04';
        }
        if ($mm == 5) {
            $mm = '05';
        }
        if ($mm == 6) {
            $mm = '06';
        }
        if ($mm == 7) {
            $mm = '07';
        }
        if ($mm == 8) {
            $mm = '08';
        }
        if ($mm == 9) {
            $mm = '09';
        }
        if ($mm_b == 1) {
            $mm_b = '01';
        }
        if ($mm_b == 2) {
            $mm_b = '02';
        }
        if ($mm_b == 3) {
            $mm_b = '03';
        }
        if ($mm_b == 4) {
            $mm_b = '04';
        }
        if ($mm_b == 5) {
            $mm_b = '05';
        }        
        if ($mm_b == 6) {
            $mm_b = '06';
        }        
        if ($mm_b == 7) {
            $mm_b = '07';
        }        
        if ($mm_b == 8) {
            $mm_b = '08';
        }        
        if ($mm_b == 9) {
            $mm_b = '09';
        }        
        $last_day   = date("t", mktime(0, 0, 0, $mm, 1, $yyyy));         // 月末最終日の取得(当月)
        $day_num1   = 10;                                                // １日～１０日の日数(当月)
        $str_day1   = $yyyy . $mm . '01';                                // １日～１０日の開始日(当月)
        $end_day1   = $yyyy . $mm . '10';                                // １日～１０日の最終日(当月)
        $day_num2   = 10;                                                // １１日～２０日の日数(当月)
        $str_day2   = $yyyy . $mm . '11';                                // １１日～２０日の開始日(当月)
        $end_day2   = $yyyy . $mm . '20';                                // １１日～２０日の最終日(当月)
        $day_num3   = $last_day - 20;                                    // ２１日～最終日の日数(当月)
        $str_day3   = $yyyy . $mm . '21';                                // ２１日～最終日の開始日(当月)
        $end_day3   = $yyyy . $mm . $last_day;                           // ２１日～最終日の最終日(当月)
        $last_day_b = date("t", mktime(0, 0, 0, $mm_b, 1, $yyyy_b));     // 月末最終日の取得(前月)
        $day_num1_b = 10;                                                // １日～１０日の日数(前月)
        $str_day1_b = $yyyy_b . $mm_b . '01';                            // １日～１０日の開始日(前月)
        $end_day1_b = $yyyy_b . $mm_b . '10';                            // １日～１０日の最終日(前月)
        $day_num2_b = 10;                                                // １１日～２０日の日数(前月)
        $str_day2_b = $yyyy_b . $mm_b . '11';                            // １１日～２０日の開始日(前月)
        $end_day2_b = $yyyy_b . $mm_b . '20';                            // １１日～２０日の最終日(前月)
        $day_num3_b = $last_day_b - 20;                                  // ２１日～最終日の日数(前月)
        $str_day3_b = $yyyy_b . $mm_b . '21';                            // ２１日～最終日の開始日(前月)
        $end_day3_b = $yyyy_b . $mm_b . $last_day_b;                     // ２１日～最終日の最終日(前月)
        $str_day = array($str_day1_b, $str_day1, $str_day2_b, $str_day2, $str_day3_b, $str_day3);
        $end_day = array($end_day1_b, $end_day1, $end_day2_b, $end_day2, $end_day3_b, $end_day3);
        $day_num = array($day_num1_b, $day_num1, $day_num2_b, $day_num2, $day_num3_b, $day_num3);
        $section_confirm = array();                                // 確認登録の未・済
        for ($i=0; $i<6; $i++) {
            $query = $this->getSectionUser($sid, $request->get('targetPosition'), $str_day[$i]);    // 部門ＩＤ毎の所属課員を取得
            $res = array();
            if ($this->getResult($query, $res) < 1) {              // 所属が誰もいなければ---表示
                $section_confirm[0] = '---';                       // １日～１０日の確認(前月)
                $section_confirm[1] = '---';                       // １日～１０日の確認(当月)
                $section_confirm[2] = '---';                       // １１日～２０日の確認(前月)
                $section_confirm[3] = '---';                       // １１日～２０日の確認(当月)
                $section_confirm[4] = '---';                       // ２１日～最終日の確認(前月)
                $section_confirm[5] = '---';                       // ２１日～最終日の確認(当月)
            } else {
                $rows = count($res);
                // 週報確認
                for ($r=0; $r<$rows; $r++) {
                    $query="SELECT COUNT(*)
                            FROM working_hours_report_confirm
                            WHERE uid='{$res[$r][0]}' AND working_date>='$str_day[$i]' AND working_date<='$end_day[$i]'
                    ";
                    $res_c = array();
                    $this->getResult2($query, $res_c);
                    if($res_c[0][0]!=$day_num[$i]) {    // 確認登録の個数と日数比較
                        $section_confirm[$i] = '未';    // 一人でも未確定の人がいれば未
                        break;
                    } else {
                        $section_confirm[$i] = '済';
                    }
                }
                
            }
        }
        $request->add('yyyymm', $yyyymm);                // 当月
        $request->add('last_day', $last_day);            // 当月最終日
        $request->add('yyyymm_b', $yyyymm_b);            // 前月
        $request->add('last_day_b', $last_day_b);        // 前月最終日
        return $section_confirm;
    }
    
    public function sendChkMail($request)
    {
        ///// パラメーターの分割
        $str_date   = $request->get('targetDateStr');
        $end_date   = $request->get('targetDateEnd');
        
        $str_year   = substr($str_date, 0, 4);
        $str_month  = substr($str_date, 4, 2);
        $str_day    = substr($str_date, 6, 2);
        $end_year   = substr($end_date, 0, 4);
        $end_month  = substr($end_date, 4, 2);
        $end_day    = substr($end_date, 6, 2);
        
        $subject      = '週報確認依頼の件';             // メール件名
        $sponsor_addr = 'usoumu@nitto-kohki.co.jp';     // 送信元アドレス
        //$atten      = $request->get('atten');         // 送信者(attendance) (配列)
        $atten      = array();
        $atten      = array('017850','009580','012980','017728','018040','015202','016713','011045','014834');          // 送信者(attendance) (配列)
        $atten_num  = count($atten);            // 送信者数
        // 送信者の名前取得 (引数３個は全て配列)
        $this->getAttendanceName($atten, $atten_name, $flag);
        // 送信者のメールアドレスの取得とメール送信
        for ($i=0; $i<$atten_num; $i++) {
            if ($flag[$i] == 'NG') continue;
            // 送信者のメールアドレス取得
            if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                continue;
            }
            $to_addres = $atten_addr;
            //$message  = "この案内は {$sponsor_name} さんが出席者にメール案内を出す設定にしたため送信されたものです。\n\n";
            //$message .= "{$subject}\n\n";
            $message  = "部門長各位\n\n";
            $message .= "{$str_year}年 {$str_month}月 {$str_day}日 ～ {$end_year}年 {$end_month}月 {$end_day}日の\n\n";
            $message .= "総合届の入力が完了しましたので、週報の確認をよろしくお願い致します。\n\n";
            $message .= "総務課\n\n";
            $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
            $attenSubject = '宛先： ' . $atten_name[$i] . ' 様　 ' . $subject;
            if (mb_send_mail($to_addres, $subject, $message, $add_head)) {
                // メール送信履歴を保存
                $this->setAttendanceMailHistory($str_date, $end_date);
            }
        }
        return true;
    }
    
    ////////// 送信者の名前取得
    private function getAttendanceName($atten, &$atten_name, &$flag)
    {
        $atten_num = count($atten);
        $atten_name = array();
        $flag = array();
        for ($i=0; $i<$atten_num; $i++) {
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid = '{$atten[$i]}' AND retire_date IS NULL AND sid != 31
            ";
            $atten_name[$i] = '';
            if ($this->getUniResult($query, $atten_name[$i]) < 1) {
                $_SESSION['s_sysmsg'] .= "メール案内で出席者の名前が見つかりません！ [ {$atten[$i]} ]";
                $flag[$i] = 'NG';
            } else {
                $flag[$i] = 'OK';
            }
        }
    }
    
    ////////// 送信者のメールアドレス取得
    private function getAttendanceAddr($atten)
    {
        $query = "
            SELECT trim(mailaddr) FROM user_master WHERE uid = '{$atten}'
        ";
        $atten_addr = '';
        if ($this->getUniResult($query, $atten_addr) < 1) {
            $_SESSION['s_sysmsg'] .= "メール案内で出席者のメールアドレスが見つかりません！ [ {$atten} ]";
        }
        return $atten_addr;
    }
    
    ////////// メール送信履歴を保存
    private function setAttendanceMailHistory($str_date, $end_date)
    {
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $sql = "
                INSERT INTO working_hours_report_mail
                (working_date, working_enddate, confirm, last_date, last_user)
                VALUES
                ($str_date, $end_date, TRUE, '$last_date', $last_user)
                ;
               ";
        query_affected($sql);
    }
    
    ///// 部門IDから部門の週報確認状況を取得
    private function getMailConfirm($request)
    {
        // 日付計算
        $yyyymm   = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($mm == 1) {
            $yyyy_b   = $yyyy - 1;
            $mm_b     = 12;
            $yyyymm_b = $yyyy_b . $mm_b;
        } else {
            $yyyy_b   = $yyyy;
            $mm_b     = $mm - 1;
            $yyyymm_b = $yyyy . $mm_b;
        }
        if ($mm == 1) {
            $mm = '01';
        }
        if ($mm == 2) {
            $mm = '02';
        }
        if ($mm == 3) {
            $mm = '03';
        }
        if ($mm == 4) {
            $mm = '04';
        }
        if ($mm == 5) {
            $mm = '05';
        }
        if ($mm == 6) {
            $mm = '06';
        }
        if ($mm == 7) {
            $mm = '07';
        }
        if ($mm == 8) {
            $mm = '08';
        }
        if ($mm == 9) {
            $mm = '09';
        }
        if ($mm_b == 1) {
            $mm_b = '01';
        }
        if ($mm_b == 2) {
            $mm_b = '02';
        }
        if ($mm_b == 3) {
            $mm_b = '03';
        }
        if ($mm_b == 4) {
            $mm_b = '04';
        }
        if ($mm_b == 5) {
            $mm_b = '05';
        }        
        if ($mm_b == 6) {
            $mm_b = '06';
        }        
        if ($mm_b == 7) {
            $mm_b = '07';
        }        
        if ($mm_b == 8) {
            $mm_b = '08';
        }        
        if ($mm_b == 9) {
            $mm_b = '09';
        }        
        $last_day   = date("t", mktime(0, 0, 0, $mm, 1, $yyyy));         // 月末最終日の取得(当月)
        $day_num1   = 10;                                                // １日～１０日の日数(当月)
        $str_day1   = $yyyy . $mm . '01';                                // １日～１０日の開始日(当月)
        $end_day1   = $yyyy . $mm . '10';                                // １日～１０日の最終日(当月)
        $day_num2   = 10;                                                // １１日～２０日の日数(当月)
        $str_day2   = $yyyy . $mm . '11';                                // １１日～２０日の開始日(当月)
        $end_day2   = $yyyy . $mm . '20';                                // １１日～２０日の最終日(当月)
        $day_num3   = $last_day - 20;                                    // ２１日～最終日の日数(当月)
        $str_day3   = $yyyy . $mm . '21';                                // ２１日～最終日の開始日(当月)
        $end_day3   = $yyyy . $mm . $last_day;                           // ２１日～最終日の最終日(当月)
        $last_day_b = date("t", mktime(0, 0, 0, $mm_b, 1, $yyyy_b));     // 月末最終日の取得(前月)
        $day_num1_b = 10;                                                // １日～１０日の日数(前月)
        $str_day1_b = $yyyy_b . $mm_b . '01';                            // １日～１０日の開始日(前月)
        $end_day1_b = $yyyy_b . $mm_b . '10';                            // １日～１０日の最終日(前月)
        $day_num2_b = 10;                                                // １１日～２０日の日数(前月)
        $str_day2_b = $yyyy_b . $mm_b . '11';                            // １１日～２０日の開始日(前月)
        $end_day2_b = $yyyy_b . $mm_b . '20';                            // １１日～２０日の最終日(前月)
        $day_num3_b = $last_day_b - 20;                                  // ２１日～最終日の日数(前月)
        $str_day3_b = $yyyy_b . $mm_b . '21';                            // ２１日～最終日の開始日(前月)
        $end_day3_b = $yyyy_b . $mm_b . $last_day_b;                     // ２１日～最終日の最終日(前月)
        $str_day = array($str_day1_b, $str_day1, $str_day2_b, $str_day2, $str_day3_b, $str_day3);
        $end_day = array($end_day1_b, $end_day1, $end_day2_b, $end_day2, $end_day3_b, $end_day3);
        $day_num = array($day_num1_b, $day_num1, $day_num2_b, $day_num2, $day_num3_b, $day_num3);
        $section_confirm = array();                                // 確認登録の未・済
        for ($i=0; $i<6; $i++) {
            $query="SELECT confirm
                        FROM working_hours_report_mail
                        WHERE working_date>='$str_day[$i]' AND working_enddate<='$end_day[$i]'
                    ";
            $res_c = array();
            //$this->getResult2($query, $res_c);
            if ( $this->getResult2($query, $res_c) > 0 ) {    // 登録あり メール送信チェック
                if($res_c[0][0] == 't') {    // メール送信履歴があれば済み
                    $section_confirm[$i] = '済';
                } else {
                    $section_confirm[$i] = '未';
                }
            } else {                                    // 登録無し メール未送信
                $section_confirm[$i] = '未';
            }
        }
        $request->add('yyyymm', $yyyymm);                // 当月
        $request->add('last_day', $last_day);            // 当月最終日
        $request->add('yyyymm_b', $yyyymm_b);            // 前月
        $request->add('last_day_b', $last_day_b);        // 前月最終日
        return $section_confirm;
    }
    
    // 週報の確認登録
    public function setConfirmData($request)
    {
        $sid        = $request->get('section_id');
        $str_date   = $request->get('str_date');
        $end_date   = $request->get('end_date');
        $format_str = format_date($str_date);
        $format_end = format_date($end_date);
        $str_ym     = substr($str_date, 0, 6);
        $end_ym     = substr($end_date, 0, 6);
        if ($str_ym != $end_ym) {
            $_SESSION['s_sysmsg'] .= "週報は同一月内で確認してください！！ 確認対象：{$format_str} ～ {$format_end}";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
        $str_day    = substr($str_date, 6, 2);
        $end_day    = substr($end_date, 6, 2);
        $date_num   = $end_day - $str_day + 1;
        $query = $this->getSectionUser($sid, $request->get('targetPosition'), $str_date);             // 選択部門より所属社員を取得
        if ($rows=getResult($query,$res)) {
            for ($i=0; $i<$rows; $i++) {
                $uid[$i] = $res[$i]['uid'];
            }
            // 今現在いないのでコメント化
            /*
            if ($request->get('targetSection') == 4) {                  // 管理部所属社員の対応
                $uid[$i] = '000817';                                    // 000817=管理部 小林さん
                $res[$i]['sid'] = 9;                                    // sidに管理部を追加
                $rows = $rows + 1;                                      // sid追加の為$rowsも１追加
            }
            */
            $s_name = $this->getSectionName($rows,$res);  // 選択部門の部門コードより部門名を取得
        } else {
            $_SESSION['s_sysmsg'] .= "{$format_str} ～ {$format_end}の週報確認登録に失敗しました！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
        $sql = '';
        $working_date = $str_date;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        for ($r=0; $r<$rows; $r++) {
            $working_date = $str_date;
            for ($i=0; $i<$date_num; $i++) {
                $query = sprintf("SELECT * FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid[$r], $working_date);
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
                    $sql .= "
                        UPDATE working_hours_report_confirm SET
                        confirm=TRUE, last_date='$last_date', last_user=$last_user
                        WHERE uid={$uid[$r]} AND working_date={$working_date}
                        ;
                    ";
                } else {                                    // 登録なし INSERT 新規
                    $sql .= "
                        INSERT INTO working_hours_report_confirm
                        (uid, working_date, confirm, last_date, last_user)
                        VALUES
                        ('{$uid[$r]}', $working_date, TRUE, '$last_date', $last_user)
                        ;
                    ";
                }
                $working_date = $working_date + 1;
            }
        }
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$format_str} ～ {$format_end}の週報確認登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "{$format_str} ～ {$format_end}の週報確認登録成功！"; // .= に注意
            return true;
        }
    }
    
    // 週報の確認登録(個人）
    public function setConfirmOneData($request)
    {
        $uid         = $request->get('uid');
        $str_date    = $request->get('str_date');
        $end_date    = $request->get('end_date');
        $confirm_flg = $request->get('confirm_flg');
        $format_str = format_date($str_date);
        $format_end = format_date($end_date);
        $str_ym     = substr($str_date, 0, 6);
        $end_ym     = substr($end_date, 0, 6);
        if ($str_ym != $end_ym) {
            $_SESSION['s_sysmsg'] .= "週報は同一月内で確認してください！！ 確認対象：{$format_str} ～ {$format_end}";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
        $str_day    = substr($str_date, 6, 2);
        $end_day    = substr($end_date, 6, 2);
        $date_num   = $end_day - $str_day + 1;
        $sql = '';
        $working_date = $str_date;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $working_date = $str_date;
        for ($i=0; $i<$date_num; $i++) {
            $query = sprintf("SELECT * FROM working_hours_report_confirm WHERE uid='%s' AND working_date=%d", $uid, $working_date);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
                $sql .= "
                    UPDATE working_hours_report_confirm SET
                    confirm=TRUE, confirm_flg=$confirm_flg, last_date='{$last_date}', last_user=$last_user
                    WHERE uid='{$uid}' AND working_date={$working_date}
                    ;
                ";
            } else {                                    // 登録なし INSERT 新規
                $sql .= "
                    INSERT INTO working_hours_report_confirm
                    (uid, working_date, working_enddate, confirm, confirm_flg, last_date, last_user)
                    VALUES
                    ('{$uid}', $working_date, $end_date, TRUE, $confirm_flg, '$last_date', $last_user)
                    ;
                ";
            }
            $working_date = $working_date + 1;
        }
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$format_str} ～ {$format_end}の週報確認登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "{$format_str} ～ {$format_end}の週報確認登録成功！"; // .= に注意
            return true;
        }
    }
    ////// List部   一覧表の ヘッダー部を作成
    private function getViewHTMLheader($request)
    {
        // タイトルをSQLのストアードプロシージャーから取得
        $query = "SELECT parts_stock_title('{$request->get('targetPartsNo')}')";
        $title = '';
        $this->getUniResult($query, $title);
        if (!$title) {                        // レコードが無い場合もNULLレコードが返るため変数の内容でチェックする
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
    
    ////// List部   一覧表の フッター部を作成
    private function getViewHTMLfooter($request)
    {
        // 合計
        $t_fixed_hour  = floor($request->get('total_fixed_time') / 60);       // 所定時間の時間部分計算
        $t_fixed_min   = $request->get('total_fixed_time')%60;                // 所定時間の分数部分計算
        $t_extend_hour = floor($request->get('total_extend_time') / 60);      // 延長時間の時間部分計算
        $t_extend_min  = $request->get('total_extend_time')%60;               // 延長時間の分数部分計算
        $t_over_hour   = floor($request->get('total_overtime') / 60);         // 早出残業時間の時間部分計算
        $t_over_min    = $request->get('total_overtime')%60;                  // 早出残業の分数部分計算
        $t_mid_hour    = floor($request->get('total_midnight_over') / 60);    // 深夜残業時間の時間部分計算
        $t_mid_min     = $request->get('total_midnight_over')%60;             // 深夜残業時間の分数部分計算
        $t_hotime_hour = floor($request->get('total_holiday_time') / 60);     // 休出時間の時間部分計算
        $t_hotime_min  = $request->get('total_holiday_time')%60;              // 休出時間の分数部分計算
        $t_hoover_hour = floor($request->get('total_holiday_over') / 60);     // 休出残業時間の時間部分計算
        $t_hoover_min  = $request->get('total_holiday_over')%60;              // 休出残業時間の分数部分計算
        $t_homid_hour  = floor($request->get('total_holiday_mid') / 60);      // 休出深夜時間の時間部分計算
        $t_homid_min   = $request->get('total_holiday_mid')%60;               // 休出深夜時間の分数部分計算
        $t_legal_hour  = floor($request->get('total_legal_time') / 60);       // 法定時間の時間部分計算
        $t_legal_min   = $request->get('total_legal_time')%60;                // 法定時間の分数部分計算
        $t_leover_hour = floor($request->get('total_legal_over') / 60);       // 法定残業時間の時間部分計算
        $t_leover_min  = $request->get('total_legal_over')%60;                // 法定残業時間の分数部分計算
        $t_late_hour   = floor($request->get('total_late_time') / 60);        // 遅刻早退時間の時間部分計算
        $t_late_min    = $request->get('total_late_time')%60;                 // 遅刻早退時間の分数部分計算
        // 社員
        $t_fixed_hour_s  = floor($request->get('total_fixed_time_s') / 60);       // 所定時間の時間部分計算
        $t_fixed_min_s   = $request->get('total_fixed_time_s')%60;                // 所定時間の分数部分計算
        $t_extend_hour_s = floor($request->get('total_extend_time_s') / 60);      // 延長時間の時間部分計算
        $t_extend_min_s  = $request->get('total_extend_time_s')%60;               // 延長時間の分数部分計算
        $t_over_hour_s   = floor($request->get('total_overtime_s') / 60);         // 早出残業時間の時間部分計算
        $t_over_min_s    = $request->get('total_overtime_s')%60;                  // 早出残業の分数部分計算
        $t_mid_hour_s    = floor($request->get('total_midnight_over_s') / 60);    // 深夜残業時間の時間部分計算
        $t_mid_min_s     = $request->get('total_midnight_over_s')%60;             // 深夜残業時間の分数部分計算
        $t_hotime_hour_s = floor($request->get('total_holiday_time_s') / 60);     // 休出時間の時間部分計算
        $t_hotime_min_s  = $request->get('total_holiday_time_s')%60;              // 休出時間の分数部分計算
        $t_hoover_hour_s = floor($request->get('total_holiday_over_s') / 60);     // 休出残業時間の時間部分計算
        $t_hoover_min_s  = $request->get('total_holiday_over_s')%60;              // 休出残業時間の分数部分計算
        $t_homid_hour_s  = floor($request->get('total_holiday_mid_s') / 60);      // 休出深夜時間の時間部分計算
        $t_homid_min_s   = $request->get('total_holiday_mid_s')%60;               // 休出深夜時間の分数部分計算
        $t_legal_hour_s  = floor($request->get('total_legal_time_s') / 60);       // 法定時間の時間部分計算
        $t_legal_min_s   = $request->get('total_legal_time_s')%60;                // 法定時間の分数部分計算
        $t_leover_hour_s = floor($request->get('total_legal_over_s') / 60);       // 法定残業時間の時間部分計算
        $t_leover_min_s  = $request->get('total_legal_over_s')%60;                // 法定残業時間の分数部分計算
        $t_late_hour_s   = floor($request->get('total_late_time_s') / 60);        // 遅刻早退時間の時間部分計算
        $t_late_min_s    = $request->get('total_late_time_s')%60;                 // 遅刻早退時間の分数部分計算
        // パート
        $t_fixed_hour_p  = floor($request->get('total_fixed_time_p') / 60);       // 所定時間の時間部分計算
        $t_fixed_min_p   = $request->get('total_fixed_time_p')%60;                // 所定時間の分数部分計算
        $t_extend_hour_p = floor($request->get('total_extend_time_p') / 60);      // 延長時間の時間部分計算
        $t_extend_min_p  = $request->get('total_extend_time_p')%60;               // 延長時間の分数部分計算
        $t_over_hour_p   = floor($request->get('total_overtime_p') / 60);         // 早出残業時間の時間部分計算
        $t_over_min_p    = $request->get('total_overtime_p')%60;                  // 早出残業の分数部分計算
        $t_mid_hour_p    = floor($request->get('total_midnight_over_p') / 60);    // 深夜残業時間の時間部分計算
        $t_mid_min_p     = $request->get('total_midnight_over_p')%60;             // 深夜残業時間の分数部分計算
        $t_hotime_hour_p = floor($request->get('total_holiday_time_p') / 60);     // 休出時間の時間部分計算
        $t_hotime_min_p  = $request->get('total_holiday_time_p')%60;              // 休出時間の分数部分計算
        $t_hoover_hour_p = floor($request->get('total_holiday_over_p') / 60);     // 休出残業時間の時間部分計算
        $t_hoover_min_p  = $request->get('total_holiday_over_p')%60;              // 休出残業時間の分数部分計算
        $t_homid_hour_p  = floor($request->get('total_holiday_mid_p') / 60);      // 休出深夜時間の時間部分計算
        $t_homid_min_p   = $request->get('total_holiday_mid_p')%60;               // 休出深夜時間の分数部分計算
        $t_legal_hour_p  = floor($request->get('total_legal_time_p') / 60);       // 法定時間の時間部分計算
        $t_legal_min_p   = $request->get('total_legal_time_p')%60;                // 法定時間の分数部分計算
        $t_leover_hour_p = floor($request->get('total_legal_over_p') / 60);       // 法定残業時間の時間部分計算
        $t_leover_min_p  = $request->get('total_legal_over_p')%60;                // 法定残業時間の分数部分計算
        $t_late_hour_p   = floor($request->get('total_late_time_p') / 60);        // 遅刻早退時間の時間部分計算
        $t_late_min_p    = $request->get('total_late_time_p')%60;                 // 遅刻早退時間の分数部分計算
        // 社員
        // 初期化
        $listTable = '';
        $listTable .= "<BR><CENTER>\n";
        $listTable .= "<B><U><font size='2'>　　処理期間：　". format_date($request->get('targetDateStr')) ."　～　". format_date($request->get('targetDateEnd')) ."　集計（出向者は除く）</U></B>\n";
        $listTable .= "</CENTER>\n";
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";                                             // 集計データの表示
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "        <th class='winbox'>所定時間</th>\n";
        $listTable .= "        <th class='winbox'>延長時間</th>\n";
        $listTable .= "        <th class='winbox'>早出残業</th>\n";
        $listTable .= "        <th class='winbox'>深夜残業</th>\n";
        $listTable .= "        <th class='winbox'>休出時間</th>\n";
        $listTable .= "        <th class='winbox'>休出残業</th>\n";
        $listTable .= "        <th class='winbox'>休出深夜</th>\n";
        $listTable .= "        <th class='winbox'>法定時間</th>\n";
        $listTable .= "        <th class='winbox'>法定残業</th>\n";
        $listTable .= "        <th class='winbox'>遅刻早退</th>\n";
        $listTable .= "        <th class='winbox'>　</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>社　員</td>\n";
        if ($t_fixed_min_s == 0) {                                      // 所定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_s .":". $t_fixed_min_s ."0</td>\n";
        } else if ($t_fixed_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_s .":0". $t_fixed_min_s ."</td>\n";
        } else {                                                             
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_s .":". $t_fixed_min_s ."</td>\n";
        }
        if ($t_extend_min_s == 0) {                                     // 延長時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_s .":". $t_extend_min_s ."0</td>\n";
        } else if ($t_extend_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_s .":0". $t_extend_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_s .":". $t_extend_min_s ."</td>\n";
        }
        if ($t_over_min_s == 0) {                                       // 早出残業時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_s .":". $t_over_min_s ."0</td>\n";
        } else if ($t_over_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_s .":0". $t_over_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_s .":". $t_over_min_s ."</td>\n";
        }
        if ($t_mid_min_s == 0) {                                        // 深夜残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_s .":". $t_mid_min_s ."0</td>\n";
        } else if ($t_mid_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_s .":0". $t_mid_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_s .":". $t_mid_min_s ."</td>\n";
        }
        if ($t_hotime_min_s == 0) {                                     // 休出時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_s .":". $t_hotime_min_s ."0</td>\n";
        } else if ($t_hotime_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_s .":0". $t_hotime_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_s .":". $t_hotime_min_s ."</td>\n";
        }
        if ($t_hoover_min_s == 0) {                                     // 休出残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_s .":". $t_hoover_min_s ."0</td>\n";
        } else if ($t_hoover_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_s .":0". $t_hoover_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_s .":". $t_hoover_min_s ."</td>\n";
        }
        if ($t_homid_min_s == 0) {                                      // 休出深夜集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_s .":". $t_homid_min_s ."0</td>\n";
        } else if ($t_homid_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_s .":0". $t_homid_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_s .":". $t_homid_min_s ."</td>\n";
        }
        if ($t_legal_min_s == 0) {                                      // 法定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_s .":". $t_legal_min_s ."0</td>\n";
        } else if ($t_legal_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_s .":0". $t_legal_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_s .":". $t_legal_min_s ."</td>\n";
        }
        if ($t_leover_min_s == 0) {                                     // 法定残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_s .":". $t_leover_min_s ."0</td>\n";
        } else if ($t_leover_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_s .":0". $t_leover_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_s .":". $t_leover_min_s ."</td>\n";
        }
        if ($t_late_min_s == 0) {                                       // 遅刻早退時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_s .":". $t_late_min_s ."0</td>\n";
        } else if ($t_late_min_s < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_s .":0". $t_late_min_s ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_s .":". $t_late_min_s ."</td>\n";
        }
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>パート</td>\n";
        if ($t_fixed_min_p == 0) {                                      // 所定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_p .":". $t_fixed_min_p ."0</td>\n";
        } else if ($t_fixed_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_p .":0". $t_fixed_min_p ."</td>\n";
        } else {                                                             
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour_p .":". $t_fixed_min_p ."</td>\n";
        }
        if ($t_extend_min_p == 0) {                                     // 延長時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_p .":". $t_extend_min_p ."0</td>\n";
        } else if ($t_extend_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_p .":0". $t_extend_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour_p .":". $t_extend_min_p ."</td>\n";
        }
        if ($t_over_min_p == 0) {                                       // 早出残業時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_p .":". $t_over_min_p ."0</td>\n";
        } else if ($t_over_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_p .":0". $t_over_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour_p .":". $t_over_min_p ."</td>\n";
        }
        if ($t_mid_min_p == 0) {                                        // 深夜残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_p .":". $t_mid_min_p ."0</td>\n";
        } else if ($t_mid_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_p .":0". $t_mid_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour_p .":". $t_mid_min_p ."</td>\n";
        }
        if ($t_hotime_min_p == 0) {                                     // 休出時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_p .":". $t_hotime_min_p ."0</td>\n";
        } else if ($t_hotime_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_p .":0". $t_hotime_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour_p .":". $t_hotime_min_p ."</td>\n";
        }
        if ($t_hoover_min_p == 0) {                                     // 休出残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_p .":". $t_hoover_min_p ."0</td>\n";
        } else if ($t_hoover_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_p .":0". $t_hoover_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour_p .":". $t_hoover_min_p ."</td>\n";
        }
        if ($t_homid_min_p == 0) {                                      // 休出深夜集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_p .":". $t_homid_min_p ."0</td>\n";
        } else if ($t_homid_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_p .":0". $t_homid_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour_p .":". $t_homid_min_p ."</td>\n";
        }
        if ($t_legal_min_p == 0) {                                      // 法定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_p .":". $t_legal_min_p ."0</td>\n";
        } else if ($t_legal_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_p .":0". $t_legal_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour_p .":". $t_legal_min_p ."</td>\n";
        }
        if ($t_leover_min_p == 0) {                                     // 法定残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_p .":". $t_leover_min_p ."0</td>\n";
        } else if ($t_leover_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_p .":0". $t_leover_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour_p .":". $t_leover_min_p ."</td>\n";
        }
        if ($t_late_min_p == 0) {                                       // 遅刻早退時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_p .":". $t_late_min_p ."0</td>\n";
        } else if ($t_late_min_p < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_p .":0". $t_late_min_p ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour_p .":". $t_late_min_p ."</td>\n";
        }
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>　</td>\n";
        $listTable .= "        <td class='winbox'>合　計</td>\n";
        if ($t_fixed_min == 0) {                                      // 所定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour .":". $t_fixed_min ."0</td>\n";
        } else if ($t_fixed_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour .":0". $t_fixed_min ."</td>\n";
        } else {                                                             
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_fixed_hour .":". $t_fixed_min ."</td>\n";
        }
        if ($t_extend_min == 0) {                                     // 延長時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour .":". $t_extend_min ."0</td>\n";
        } else if ($t_extend_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour .":0". $t_extend_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_extend_hour .":". $t_extend_min ."</td>\n";
        }
        if ($t_over_min == 0) {                                       // 早出残業時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour .":". $t_over_min ."0</td>\n";
        } else if ($t_over_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour .":0". $t_over_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_over_hour .":". $t_over_min ."</td>\n";
        }
        if ($t_mid_min == 0) {                                        // 深夜残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour .":". $t_mid_min ."0</td>\n";
        } else if ($t_mid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour .":0". $t_mid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_mid_hour .":". $t_mid_min ."</td>\n";
        }
        if ($t_hotime_min == 0) {                                     // 休出時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour .":". $t_hotime_min ."0</td>\n";
        } else if ($t_hotime_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour .":0". $t_hotime_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hotime_hour .":". $t_hotime_min ."</td>\n";
        }
        if ($t_hoover_min == 0) {                                     // 休出残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour .":". $t_hoover_min ."0</td>\n";
        } else if ($t_hoover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour .":0". $t_hoover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_hoover_hour .":". $t_hoover_min ."</td>\n";
        }
        if ($t_homid_min == 0) {                                      // 休出深夜集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour .":". $t_homid_min ."0</td>\n";
        } else if ($t_homid_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour .":0". $t_homid_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_homid_hour .":". $t_homid_min ."</td>\n";
        }
        if ($t_legal_min == 0) {                                      // 法定時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour .":". $t_legal_min ."0</td>\n";
        } else if ($t_legal_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour .":0". $t_legal_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_legal_hour .":". $t_legal_min ."</td>\n";
        }
        if ($t_leover_min == 0) {                                     // 法定残業集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour .":". $t_leover_min ."0</td>\n";
        } else if ($t_leover_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour .":0". $t_leover_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_leover_hour .":". $t_leover_min ."</td>\n";
        }
        if ($t_late_min == 0) {                                       // 遅刻早退時間集計表示
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour .":". $t_late_min ."0</td>\n";
        } else if ($t_late_min < 10) {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour .":0". $t_late_min ."</td>\n";
        } else {
            $listTable .= "        <td class='winbox' align='right' nowrap>". $t_late_hour .":". $t_late_min ."</td>\n";
        }
        $listTable .= "        <td class='winbox'>　</td>\n";
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
<title>就業週報照会</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../working_hours_report.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../working_hours_report.js'></script>
</head>
<body>  <!--  -->
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
    
} // Class WorkingHoursReport_Model End

?>
