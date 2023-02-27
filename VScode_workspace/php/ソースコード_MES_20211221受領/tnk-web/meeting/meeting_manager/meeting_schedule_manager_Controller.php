<?php
//////////////////////////////////////////////////////////////////////////////
// 部課長用会議スケジュール照会                      MVC Controller 部      //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_Controller.php             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class MeetingSchedule_Controller
{
    ///// Private properties
    //private $menu;                              // TNK 共用メニュークラスのインスタンス
    //private $request;                           // HTTP Controller部のリクエスト インスタンス
    //private $result;                            // HTTP Controller部のリザルト   インスタンス
    //private $session;                           // HTTP Controller部のセッション インスタンス
    //private $model;                             // ビジネスモデル部のインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用 リクエスト データ取得
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'GanttChart');              // 指定がない場合は一覧表を表示(特に初回)
        }
        if ($request->get('showMenu') == 'MyList') {
            $request->add('my_flg', 1);
        }
        ///// 登録・修正・削除の 実行指示リクエスト
        $apend      = $request->get('Apend');               // スケジュールの登録
        $delete     = $request->get('Delete');              // スケジュールの取消(削除)
        $edit       = $request->get('Edit');                // スケジュールの変更
        ///// 会議室用
        $roomEdit   = $request->get('roomEdit');            // 会議室の登録・変更
        $roomOmit   = $request->get('roomOmit');            // 会議室の削除
        $roomActive = $request->get('roomActive');          // 会議室の有効・無効(トグル)
        ///// グループ用
        $groupEdit  = $request->get('groupEdit');           // グループの登録・変更
        $groupOmit  = $request->get('groupOmit');           // グループの削除
        $groupActive= $request->get('groupActive');         // グループの有効・無効(トグル)
        
        ////////// MVC の Model 部の 実行部ロジック切替
        ///// スケジュールの編集
        if ($apend != '') {                                 // スケジュールの登録 (追加)
            $this->apend($request, $model);
        } elseif ($delete != '') {                          // スケジュールの取消 (完全削除)
            $this->delete($request, $model);
        } elseif ($edit != '') {                            // スケジュールの変更
            $this->edit($request, $model);
        ///// 会議室の編集
        } elseif ($roomEdit != '') {                        // 会議室の登録・変更
            $this->roomEdit($request, $model);
        } elseif ($roomOmit != '') {                        // 会議室の削除
            $this->roomOmit($request, $model);
        } elseif ($roomActive != '') {                      // 会議室の有効・無効(トグル)
            $this->roomActive($request, $model);
        ///// 出席者のグループ編集
        } elseif ($groupEdit != '') {                        // グループの登録・変更
            $this->groupEdit($request, $model);
        } elseif ($groupOmit != '') {                        // グループの削除
            $this->groupOmit($request, $model);
        } elseif ($groupActive != '') {                      // グループの有効・無効(トグル)
            $this->groupActive($request, $model);
        }
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        // カレンダークラスをinclude
        require_once ('../../CalendarClass.php');   // カレンダークラス
        
        // クラスのインスタンス作成
        // calendar(開始曜日, 当月以外の日付を表示するかどうか) の形で指定します。
        // ※開始曜日（0-日曜 から 6-土曜）、当月以外の日付を表示（0-No, 1-Yes）
        $calendar_now  = new Calendar(0, 0);
        $calendar_nex1 = new Calendar(0, 0);
        $calendar_nex2 = new Calendar(0, 0);
        $calendar_pre  = new Calendar(0, 0);

        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('meeting');
        
        ///// メニュー切替 リクエスト データ取得
        $showMenu   = $request->get('showMenu');            // ターゲットメニューを取得
        $listSpan   = $request->get('listSpan');            // 一覧表示時の期間(1日間,7日間,14,28...)
        
        ///// キーフィールド リクエスト データ取得
        $year       = $request->get('year');                // 会議予定の年４桁
        $month      = $request->get('month');               // 会議予定の月２桁
        $day        = $request->get('day');                 // 会議予定の日２桁
        $str_ymd    = $year . $month . $day;                // 開始年月日連結
        $serial_no  = $request->get('serial_no');           // table連番 キーフィールド
        $atten_flg  = $request->get('atten_flg');           // 報告先展開フラグ
        
        ////////// 確認フォームで取消が押された時のリクエスト取得
        $cancel_edit   = $request->get('cancel_edit');      // 
        
        ///// 登録・編集 データのリクエスト取得
        $subject    = $request->get('subject');             // 会議件名
        $str_time   = $request->get('str_time');            // 開始時間
        $end_time   = $request->get('end_time');            // 終了時間
        $sponsor    = $request->get('sponsor');             // 主催者
        $atten      = $request->get('atten');               // 出席者(attendance) (配列) グループでも使用
        $mail       = $request->get('mail');                // メールの送信 Y/N
        $str_hour   = $request->get('str_hour');            // 開始 時
        $str_minute = $request->get('str_minute');          // 開始 分
        $end_hour   = $request->get('end_hour');            // 終了 時
        $end_minute = $request->get('end_minute');          // 終了 分
        ///// 会議室編集用
        $room_no    = $request->get('room_no');             // 会議室番号
        $room_name  = $request->get('room_name');           // 会議室名
        $duplicate  = $request->get('duplicate');           // 会議室の重複チェック
        $roomCopy   = $request->get('roomCopy');            // 会議室の編集データコピー
        ///// グループ編集用
        $group_no2  = $request->get('group_no2');           // グループ番号
        $group_no   = $group_no2;         // TEST
        $group_name = $request->get('group_name');          // グループ名
        $owner      = $request->get('owner');               // グループを個人・共有
        $groupCopy  = $request->get('groupCopy');           // グループの編集データコピー
        ///// 照会・印刷用
        $showprint  = $request->get('showprint');           // 照会実行
        $print      = $request->get('print');               // 印刷実行
        $str_date   = $request->get('str_date');            // 開始日付
        $end_date   = $request->get('end_date');            // 終了日付
        
        // カレンダーリクエストのチェック及び取得
        if ($year != '' && $month != '' && $day != '') {
            $day_now  = getdate(mktime(0, 0, 0, $month, $day, $year));
            $day_nex1 = getdate(mktime(0, 0, 0, $month+1, 1, $year));
            $day_nex2 = getdate(mktime(0, 0, 0, $month+2, 1, $year));
            $day_pre  = getdate(mktime(0, 0, 0, $month-1, 1, $year));
        } else {
            // 当日を取得
            $day_now  = getdate();
            $day_nex1 = getdate(mktime(0, 0, 0, $day_now['mon']+1, 1, $day_now['year']));
            $day_nex2 = getdate(mktime(0, 0, 0, $day_now['mon']+2, 1, $day_now['year']));
            $day_pre  = getdate(mktime(0, 0, 0, $day_now['mon']-1, 1, $day_now['year']));
        }
        
        // カレンダーの全日付をリンクにする
        if ($showMenu != 'Edit') {
            $url = $menu->out_self() . "?showMenu={$showMenu}&" . $model->get_htmlGETparm() . "&id={$uniq}";
        } else {
            $url = $menu->out_self() . "?showMenu=List&" . $model->get_htmlGETparm() . "&id={$uniq}";
        }
        $calendar_now-> setAllLinkYMD($day_now['year'], $day_now['mon'], $url);
        $calendar_pre-> setAllLinkYMD($day_pre['year'], $day_pre['mon'], $url);
        $calendar_nex1->setAllLinkYMD($day_nex1['year'], $day_nex1['mon'], $url);
        $calendar_nex2->setAllLinkYMD($day_nex2['year'], $day_nex2['mon'], $url);
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        $resLine  = $result->get_array();
        switch ($showMenu) {
        case 'List':                                        // スケジュール 一覧表データ
            $rows = $model->getViewList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 出席者の複数データを取得
            $rowsAtten = array(); $resAtten = array();      // 初期化
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('List', $year, $month, $day));
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'MyList':                                      // スケジュール マイリスト一覧表
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $request->add('my_flg', 1);
            require_once ('meeting_schedule_manager_ViewGanttChartAjax.php'); // 現在はAjax対応版
            break;
        case 'Apend':                                       // スケジュールの入力に必要なUserデータ
            // 会議追加時の主催者の初期値設定 (本人の社員番号)
            if ($sponsor == '') if ($_SESSION['User_ID'] != '000000') $sponsor = $_SESSION['User_ID'];
            // 部署毎の社員番号と氏名を取得して selected の設定まで行う
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // グループマスターの有効なリストを取得 (JavaScriptへ渡す)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // 会議室マスターの有効なリストを取得
            $rowsRoom = $model->getActiveRoomList($result);
            $resRoom  = $result->get_array();
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Apend', $year, $month, $day));
            break;
        case 'Edit':                                        // スケジュールの変更 １レコードのデータ
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $str_time   = $result->get_once('str_time');
            $end_time   = $result->get_once('end_time');
            $room_no    = $result->get_once('room_no');
            $sponsor    = $result->get_once('sponsor');
            $atten_num  = $result->get_once('atten_num');
            $mail       = $result->get_once('mail');
            // 編集用のselectデータに分割
            $str_hour = substr($str_time, 0, 2);
            $str_minute = substr($str_time, -2);
            $end_hour = substr($end_time, 0, 2);
            $end_minute = substr($end_time, -2);
            // 出席者の複数データを取得
            $rowsAtten = $model->getViewAttenList($result, $serial_no);
            $resAtten  = $result->get_array();
            // 出席者の社員番号のみ抽出
            $atten = array();   // 初期化
            for ($i=0; $i<$rowsAtten; $i++) {
                $atten[$i] = $resAtten[$i][1];
            }
            // 部署毎の社員番号と氏名を取得して selected の設定まで行う
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // グループマスターの有効なリストを取得 (JavaScriptへ渡す)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // 会議室マスターの有効なリストを取得
            $rowsRoom = $model->getActiveRoomList($result);
            $resRoom  = $result->get_array();
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Edit', $year, $month, $day));
            break;
        case 'Room':                                        // 会議室の 登録 一覧表 表示
            $rows = $model->getViewRoomList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'Group':                                        // グループの 登録 一覧表 表示
            if ($group_no != '') {  // 編集がクリックされてgroup_noがセットされている時
                // グループ出席者の複数データを取得
                $rowsAtten = $model->getGroupAttenList($result, $group_no);
                $resAtten  = $result->get_array();
                // グループ出席者の社員番号のみ抽出
                $atten = array();   // 初期化
                for ($i=0; $i<$rowsAtten; $i++) {
                    $atten[$i] = $resAtten[$i][1];
                }
            }
            // 部署毎の社員番号と氏名を取得して selected の設定まで行う
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // グループリストの取得
            $rows = $model->getViewGroupList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 出席者の複数データを取得
            $rowsAtten = array(); $resAtten = array();      // 初期化
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getGroupAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            break;
        case 'Print':
            // 会議室マスターの有効なリストを取得
            $rowsRoom = $model->getActiveRoomList($result);
            $resRoom  = $result->get_array();
            if ($showprint != '' || $print != '') {
                $rows = $model->getPrintList($result, $request);
                $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
                $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
                // 出席者の複数データを取得
                $rowsAtten = array(); $resAtten = array();      // 初期化
                for ($i=0; $i<$rows; $i++) {
                    $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                    $resAtten[$i]  = $result->get_array();
                }
            } else {
                $rows = 0;
                $rowsAtten = 0;
                $pageControl = '';
            }
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Print', $year, $month, $day));
            break;
        case 'PlanList':                                    // 組立日程計画表 表示
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('meeting_schedule_manager_ViewPlanList.php');
            break;
        case 'ListTable':                                   // 上記のAjax用 表示
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            $allo_parts_url = $this->menu->out_action('引当構成表');
            require_once ('meeting_schedule_manager_ViewListTable.php');
            break;
        case 'GanttChart':                                  // 計画のガントチャート 表示
                // $rows = $this->model->getViewGanttChart($this->request, $this->result, $this->menu);
                // $res  = $this->result->get_array();
            // 頁データ取得のため上記の代わりに以下をダミーで使用する(Listだけなので高速)
            // $res  = $this->result->get_array();
            $request->add('my_flg', 0);
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
                // require_once ('assembly_schedule_show_ViewGanttChart.php');
            require_once ('meeting_schedule_manager_ViewGanttChartAjax.php'); // 現在はAjax対応版
            break;
        case 'GanttTable':                                  // 上記のAjax用 表示
            if($listSpan == 7) {
                $range = 7;
                $request->add('range', $range);
            } elseif($listSpan == 14) {
                $range = 14;
                $request->add('range', $range);
            } elseif($listSpan == 28) {
                $range = 28;
                $request->add('range', $range);
            } else {
                $range = 0;
                $request->add('range', 0);
            }
            $year_t  = $year;
            $month_t = $month;
            $day_t   = $day;
            if ($range > 0) {
                for ($r = 1; $r <= $range; $r++) {
                    $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}-{$r}.png";
                    $rows = $model->getViewGanttChart($request, $result, $menu, $str_ymd, $g_name, $r);
                    // 日付を一日進める
                    $str_ymd = $model->computeDate($year_t, $month_t, $day_t, 1);
                    $year_t       = substr($str_ymd, 0, 4);
                    $month_t      = substr($str_ymd, 4, 2);
                    $day_t        = substr($str_ymd, 6, 2);
                }
            } else {
                $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}.png";
                $rows = $model->getViewGanttChart($request, $result, $menu, $str_ymd, $g_name, '');
            }
            $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}";
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $request->add('my_flg', 0);
            require_once ('meeting_schedule_manager_ViewGanttTable.php');
            break;
        case 'ZoomGantt':                                  // ガントチャートのみを別ウィンドウにインラインフレームで 表示
            if($listSpan == 7) {
                $range = 7;
                $request->add('range', $range);
            } elseif($listSpan == 14) {
                $range = 14;
                $request->add('range', $range);
            } elseif($listSpan == 28) {
                $range = 28;
                $request->add('range', $range);
            } else {
                $range = 0;
                $request->add('range', 0);
            }
            //$rows = $model->getViewZoomGantt($request, $result, $menu);
            $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}";
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('meeting_schedule_manager_ViewZoomGantt.php');
            // 上記は内部で _ViewZoomGanttHeader.php と _ViewZoomGanttBody.php をインラインで呼出す。
            break;
        case 'ZoomGanttAjax':                              // 上記のAjaxリロード版
            $rows = $this->model->getViewZoomGantt($this->request, $this->result, $this->menu);
            // $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            break;
        }
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {
        case 'List':                                        // スケジュールの一覧 画面
            require_once ('meeting_schedule_manager_ViewList.php');
            break;
        case 'Edit':                                        // スケジュールの変更 画面
            require_once ('meeting_schedule_manager_ViewApend.php');    // 兼用
            break;
        case 'Apend':                                       // スケジュールの入力 画面
            require_once ('meeting_schedule_manager_ViewApend.php');
            break;
        case 'Room':                                        // 会議室の 一覧表 表示
            if ($roomCopy == 'go') {
                $focus    = 'room_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'room_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_manager_ViewRoom.php');
            break;
        case 'Group':                                       // グループの 一覧表 表示
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'group_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_manager_ViewGroup.php');
            break;
        case 'Print':                                       // スケジュールの照会・印刷
            require_once ('meeting_schedule_manager_ViewPrint.php');
            break;
        
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    protected function Init()
    {
        ///// メニュー切替用 showMenuとshowLine のデータチェック ＆ 設定
        // showMenuの処理
        $this->InitShowMenu();
        // showLineの処理
        $this->InitShowLine();
        // targetLineMethodの処理
        $this->InitLineMethod();
        // targetDateの処理
        $this->InitTargetDate();
        // targetDateSpanの処理
        $this->InitTargetDateSpan();
        // targetDateItemの処理
        $this->InitTargetDateItem();
        // targetCompleteFlagの処理
        $this->InitTargetCompleteFlag();
        // targetSeiKubunの処理
        $this->InitTargetSeiKubun();
        // targetDeptの処理
        $this->InitTargetDept();
        // targetScaleの処理
        $this->InitTargetScale();
        // PageKeepの処理
        $this->InitPageKeep();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// メニュー切替用 showMenuとshowLine のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            if ($this->session->get_local('showMenu') == '') {
                $showMenu = 'GanttChart';         // 指定がない場合はガントチャート PlanList=日程計画一覧
            } else {
                $showMenu = $this->session->get_local('showMenu');
            }
        }
        // Ajaxの場合はセッションに保存しない
        if ($showMenu != 'ListTable' && $showMenu != 'GanttTable' && $showMenu != 'ZoomGantt' && $showMenu != 'ZoomGanttAjax') {
            $this->session->add_local('showMenu', $showMenu);
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    // showLineの処理
    private function InitShowLine()
    {
        $showLine = $this->request->get('showLine');
        if ($showLine == '') {
            if ($this->session->get_local('showLine') == '') {
                $showLine = '';                // 指定がない場合は全てのラインを指定したものと見なす
            } else {
                $showLine = $this->session->get_local('showLine');
            }
        }
        if ($showLine == '0') $showLine = ''; // 0 は全体を意味する。
        $this->session->add_local('showLine', $showLine);
        $this->request->add('showLine', $showLine);
    }
    
    // targetLineMethodの処理
    private function InitLineMethod()
    {
        $LineMethod = $this->request->get('targetLineMethod');
        if ($LineMethod == '') {
            if ($this->session->get_local('targetLineMethod') == '') {
                $LineMethod = '1';              // 指定がない場合は1=個別指定とする。2=複数指定
            } else {
                $LineMethod = $this->session->get_local('targetLineMethod');
            }
        }
        if ($LineMethod == '1') {
            $this->session->add_local('arrayLine', array());        // 初期化
        } else {
            // 複数ラインarrayLineの処理
            $arrayLine = $this->session->get_local('arrayLine');
            if ( ($key=array_search($this->request->get('showLine'), $arrayLine)) === false) {
                $arrayLine[] = $this->request->get('showLine');
            } else {
                // unset ($arrayLine[$key]);   // ２回同じラインが指定された場合はトグル方式で削除したいが自動リロードを使用しているため出来ない
            }
            $this->session->add_local('arrayLine', $arrayLine);     // 保存
            $this->request->add('arrayLine', $arrayLine);
        }
        $this->session->add_local('targetLineMethod', $LineMethod);
        $this->request->add('targetLineMethod', $LineMethod);
    }
    
    ///// 指定年月日の取得・初期化
    // targetDateの処理
    private function InitTargetDate()
    {
        $targetDate = $this->request->get('targetDate');
        if ($targetDate == '') {
            if ($this->session->get_local('targetDate') == '') {
                // $targetDate = workingDayOffset('+0');   // 指定がない場合は営業日の当日
                $targetDate = date('Ym') . last_day();      // 指定がない場合は当月末
            } else {
                $targetDate = $this->session->get_local('targetDate');
            }
        }
        $this->session->add_local('targetDate', $targetDate);
        $this->request->add('targetDate', $targetDate);
    }
    
    ///// 指定年月日の範囲 取得・初期化
    // targetDateSpanの処理
    private function InitTargetDateSpan()
    {
        $targetDateSpan = $this->request->get('targetDateSpan');
        if ($targetDateSpan == '') {
            if ($this->session->get_local('targetDateSpan') == '') {
                $targetDateSpan = '1';   // 指定がない場合は指定日まで (指定日のみ=0)
            } else {
                $targetDateSpan = $this->session->get_local('targetDateSpan');
            }
        }
        $this->session->add_local('targetDateSpan', $targetDateSpan);
        $this->request->add('targetDateSpan', $targetDateSpan);
    }
    
    ///// 指定年月日が完了日か着手日か集荷日かの取得・初期化
    // targetDateItemの処理
    private function InitTargetDateItem()
    {
        $targetDateItem = $this->request->get('targetDateItem');
        if ($targetDateItem == '') {
            if ($this->session->get_local('targetDateItem') == '') {
                $targetDateItem = 'kanryou';   // 指定がない場合は着手日 (kanryou, chaku, syuka)
            } else {
                $targetDateItem = $this->session->get_local('targetDateItem');
            }
        }
        $this->session->add_local('targetDateItem', $targetDateItem);
        $this->request->add('targetDateItem', $targetDateItem);
    }
    
    ///// 完成分の日程か未完成分の日程かの取得・初期化
    // targetCompleteFlagの処理
    private function InitTargetCompleteFlag()
    {
        $targetCompleteFlag = $this->request->get('targetCompleteFlag');
        if ($targetCompleteFlag == '') {
            if ($this->session->get_local('targetCompleteFlag') == '') {
                $targetCompleteFlag = 'no';   // 指定がない場合は未完成分 (yes=complete, no=incomplete)
            } else {
                $targetCompleteFlag = $this->session->get_local('targetCompleteFlag');
            }
        }
        $this->session->add_local('targetCompleteFlag', $targetCompleteFlag);
        $this->request->add('targetCompleteFlag', $targetCompleteFlag);
    }
    
    ///// 指定 製品 区分の取得・初期化
    // targetSeiKubunの処理
    private function InitTargetSeiKubun()
    {
        $targetSeiKubun = $this->request->get('targetSeiKubun');
        if ($targetSeiKubun == '') {
            if ($this->session->get_local('targetSeiKubun') == '') {
                $targetSeiKubun = '0';   // 指定がない場合は0 (0=全て, 1=製品, 2=Lホヨウ, 3=C特注, 4=Lピストン)
            } else {
                $targetSeiKubun = $this->session->get_local('targetSeiKubun');
            }
        }
        $this->session->add_local('targetSeiKubun', $targetSeiKubun);
        $this->request->add('targetSeiKubun', $targetSeiKubun);
    }
    
    ///// 指定 製品 事業部の取得・初期化
    // targetDeptの処理
    private function InitTargetDept()
    {
        $targetDept = $this->request->get('targetDept');
        if ($targetDept == '') {
            if ($this->session->get_local('targetDept') == '') {
                $targetDept = '0';   // 指定がない場合は0 (0=全て, C=カプラ, L=リニア)
            } else {
                $targetDept = $this->session->get_local('targetDept');
            }
        }
        $this->session->add_local('targetDept', $targetDept);
        $this->request->add('targetDept', $targetDept);
    }
    
    ///// ズームガントチャートの倍率指定
    // targetScaleの処理
    private function InitTargetScale()
    {
        $targetScale = $this->request->get('targetScale');
        if ($targetScale == '') {
            if ($this->session->get_local('targetScale') == '') {
                $targetScale = '1.0';   // 指定がない場合は1.0倍表示
            } else {
                $targetScale = $this->session->get_local('targetScale');
            }
        }
        if ($targetScale < 0.3) $targetScale = '0.3';
        if ($targetScale > 1.7) $targetScale = '1.7';
        $this->session->add_local('targetScale', $targetScale);
        $this->request->add('targetScale', $targetScale);
    }
    
    ///// 計画番号で引当構成部品表を照会した場合の戻り値をチェック
    // page_keepを取得してmaterial_plan_no 及びページ制御の処理
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            // クリックした計画番号の行にマーカー用
            if ($this->session->get('material_plan_no') != '') {
                $this->request->add('material_plan_no', $this->session->get('material_plan_no'));
            }
            // ページ制御用 (呼出した時のページに戻す)
            if ($this->session->get_local('viewPage') != '') {
                $this->request->add('CTM_viewPage', $this->session->get_local('viewPage'));
            }
            if ($this->session->get_local('pageRec') != '') {
                $this->request->add('CTM_pageRec', $this->session->get_local('pageRec'));
            }
        }
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ///// 登録(追加) 処理
    protected function apend($request, $model)
    {
        $response = $model->add($request);
        if ($response) {
            $request->add('subject', '');                           // 登録できたので入力フィールドを消す
            $request->add('str_time', '');
            $request->add('end_time', '');
            $request->add('room_no', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'List');                      // 登録できたので一覧画面にする。
        } else {
            $str_time = $request->get('str_time');
            $end_time = $request->get('end_time');
            $request->add('str_hour', substr($str_time, 0, 2));     // 登録できなかったのでselectデータを復元
            $request->add('str_minute', substr($str_time, -2));
            $request->add('end_hour', substr($end_time, 0, 2));
            $request->add('end_minute', substr($end_time, -2));
        }
    }
    
    ///// 削除(完全削除) 処理
    protected function delete($request, $model)
    {
        // $serial_no  = $request->get('serial_no');                   // シリアル番号
        // $subject    = $request->get('subject');                     // 会議件名
        // $response = $model->delete($serial_no, $subject);
        $response = $model->delete($request);                       // キャンセルのメール対応版
        if ($response) {
            $request->add('showMenu', 'List');                      // 削除出来たので一覧画面にする。
        } else {
            $request->add('showMenu', 'Edit');                      // 削除出来なかったので編集画面を復元
        }
    }
    
    ///// 編集処理  処理
    protected function edit($request, $model)
    {
        if ($model->edit($request)) {
            $request->add('subject', '');                           // 変更できたので入力フィールドを消す
            $request->add('str_time', '');
            $request->add('end_time', '');
            $request->add('room_no', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'List');                      // 登録できたので一覧画面にする。
        } else {
            $request->add('showMenu', 'Edit');                      // 登録出来なかったので編集画面を復元
        }
    }
    
    ///// 会議室の編集 処理
    protected function roomEdit($request, $model)
    {
        $room_no    = $request->get('room_no');             // 会議室番号
        $room_name  = $request->get('room_name');           // 会議室名
        $duplicate  = $request->get('duplicate');           // 会議室の重複チェック
        if ($model->room_edit($room_no, $room_name, $duplicate)) {
            // 登録できたのでroom_no, room_nameの<input>データを消す
            $request->add('room_no', '');
            $request->add('room_name', '');
            $request->add('duplicate', '');
        }
    }
    
    ///// 会議室の削除 処理
    protected function roomOmit($request, $model)
    {
        $room_no    = $request->get('room_no');             // 会議室番号
        $room_name  = $request->get('room_name');           // 会議室名
        $response = $model->room_omit($room_no, $room_name);
        $request->add('room_no', '');                       // 削除時はコピーは必要ない
        $request->add('room_name', '');
        $request->add('duplicate', '');
    }
    
    ///// 会議室の有効・無効 処理
    protected function roomActive($request, $model)
    {
        $room_no    = $request->get('room_no');             // 会議室番号
        $room_name  = $request->get('room_name');           // 会議室名
        if ($model->room_activeSwitch($room_no, $room_name)) {
            $request->add('room_no', '');
            $request->add('room_name', '');
            $request->add('duplicate', '');
        }
    }
    
    ///// 出席者グループの編集 処理
    protected function groupEdit($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // グループ番号
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // グループ名
        $atten      = $request->get('atten');               // 出席者(attendance) (配列) グループでも使用
        $owner      = $request->get('owner');               // グループを個人・共有
        if ($model->group_edit($group_no, $group_name, $atten, $owner)) {
            // 登録できたのでgroup_no, group_nameの<input>データを消す
            $request->add('group_no', '');
            $request->add('group_no2', '');
            $request->add('group_name', '');
            $request->del('atten');
        }
    }
    
    ///// 出席者グループの削除 処理
    protected function groupOmit($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // グループ番号
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // グループ名
        $response = $model->group_omit($group_no, $group_name);
        $request->add('group_no', '');                      // 削除時はコピーは必要ない
        $request->add('group_no2', '');
        $request->add('group_name', '');
        $request->del('atten');
    }
    
    ///// 出席者グループの有効・無効 処理
    protected function groupActive($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // グループ番号
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // グループ名
        if ($model->group_activeSwitch($group_no, $group_name)) {
            $request->add('group_no', '');
            $request->add('group_no2', '');
            $request->add('group_name', '');
            $request->del('atten');
        }
    }
    
} // class AssemblyScheduleShow_Controller End

?>
