<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)スケジュール表の照会・メンテナンス                  //
//                                                       MVC Controller 部  //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/01 Created   meeting_schedule_Controller.php                     //
// 2005/11/21 出席者のグループ指定の追加                                    //
// 2005/11/29 カレンダーの未来２ヶ月と過去１ヶ月の基準日を $day → 1 へ変更 //
// 2006/05/09 自分のスケジュールのみ表示(マイリスト)機能を追加              //
// 2007/05/10 会議削除時にキャンセルのメール送信のためdelete()メソッドを変更//
// 2008/09/01 出席者折りたたみ表示の為$atten_flgの受け渡しを追加       大谷 //
// 2009/12/17 照会・印刷用画面(Print)テスト                            大谷 //
// 2010/03/11 部課長用スケジュールを作成する際にテスト変更             大谷 //
// 2015/06/19 計画有給の照会を追加                                     大谷 //
// 2019/03/15 冷温水機稼働状況、社用車、不在者のメニューを追加         大谷 //
// 2019/03/19 変更時の処理に漏れがあったので修正                       大谷 //
// 2021/06/10 カレンダー移動用の年月受け渡しはいらなかったので削除     大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class MeetingSchedule_Controller
{
    ///// Private properties
    //private $showMenu;                  // メニュー切替
    //private $listSpan;                  // 一覧表示時の期間(1日間,7日間,14,28...)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用 リクエスト データ取得
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'List');              // 指定がない場合は一覧表を表示(特に初回)
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
        ///// 社用車用
        $carEdit  = $request->get('carEdit');               // 社用車の登録・変更
        $carOmit  = $request->get('carOmit');               // 社用車の削除
        $carActive= $request->get('carActive');             // 社用車の有効・無効(トグル)
        ///// 計画有給用
        $hdelete    = $request->get('hdel');                // 計画有給登録の削除
        
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
        ///// 社用車のグループ編集
        } elseif ($carEdit != '') {                          // 社用車の登録・変更
            $this->carEdit($request, $model);
        } elseif ($carOmit != '') {                          // 社用車の削除
            $this->carOmit($request, $model);
        } elseif ($carActive != '') {                        // 社用車の有効・無効(トグル)
            $this->carActive($request, $model);
        ///// 計画有給の削除
        } elseif ($hdelete != '') {                          // 計画有給の削除
            $this->holydayDelete($request, $model);
        }
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        // カレンダークラスをinclude
        require_once ('../CalendarClass.php');   // カレンダークラス
        
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
        ///// 社用車編集用
        $car_no     = $request->get('car_no');              // 社用車番号
        $car_name   = $request->get('car_name');            // 社用車名
        $car_dup    = $request->get('car_dup');             // 社用車の重複チェック
        $carCopy    = $request->get('carCopy');             // 社用車の編集データコピー
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
        
        ////////// MVC の Model部の View部に渡すデータ生成
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
            $rows = $model->getViewMyList($result);
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
            $rowsCar  = $model->getActiveCarList($result);
            $resCar   = $result->get_array();
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Apend', $year, $month, $day));
            break;
        case 'Edit':                                        // スケジュールの変更 １レコードのデータ
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $str_time   = $result->get_once('str_time');
            $end_time   = $result->get_once('end_time');
            $room_no    = $result->get_once('room_no');
            $car_no     = $result->get_once('car_no');
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
            // 社用車マスターの有効なリストを取得
            $rowsCar  = $model->getActiveCarList($result);
            $resCar   = $result->get_array();
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
        case 'Car':                                        // 社用車の 登録 一覧表 表示
            $rows = $model->getViewCarList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
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
        case 'Holyday':                                        // 計画有給
            $rows = $model->getViewHolyday($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Holyday', $year, $month, $day));
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'Absence':                                        // 不在予定
            $rows = $model->getViewAbsence($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Absence', $year, $month, $day));
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'Over':                                        // 残業予定
            $rows = $model->getViewOver($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Over', $year, $month, $day));
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        }
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {
        case 'List':                                        // スケジュールの一覧 画面
            if( $request->get('only') == 'yes' ) {
                require_once ('meeting_schedule_room.php');
            } else {
                require_once ('meeting_schedule_ViewList_k.php');
            }
            break;
        case 'Edit':                                        // スケジュールの変更 画面
            if( $request->get('only') == 'yes' ) {
                require_once ('meeting_schedule_apend.php');        // 兼用
            } else {
                require_once ('meeting_schedule_ViewApend.php');    // 兼用
            }
            break;
        case 'Apend':                                       // スケジュールの入力 画面
            if( $request->get('only') == 'yes' ) {
                require_once ('meeting_schedule_apend.php');
            } else {
                require_once ('meeting_schedule_ViewApend.php');
            }
            break;
        case 'Room':                                        // 会議室の 一覧表 表示
            if ($roomCopy == 'go') {
                $focus    = 'room_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'room_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_ViewRoom.php');
            break;
        case 'Car':                                         // 社用車の 一覧表 表示
            if ($carCopy == 'go') {
                $focus    = 'car_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'car_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_ViewCar.php');
            break;
        case 'Group':                                       // グループの 一覧表 表示
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'group_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_ViewGroup.php');
            break;
        case 'Print':                                       // スケジュールの照会・印刷
            require_once ('meeting_schedule_ViewPrint.php');
            break;
        case 'Holyday':                                     // 計画有給の一覧 画面
            require_once ('meeting_schedule_ViewHolyday.php');
            break;
        case 'Absence':                                     // 計画有給の一覧 画面
            require_once ('meeting_schedule_ViewAbsence.php');
            break;
        case 'Over':                                     // 残業予定の一覧 画面
            require_once ('meeting_schedule_ViewOver_k.php');
            break;
        default:                // リクエストデータにエラーの場合は初期値の一覧を表示
            require_once ('meeting_schedule_ViewList_k.php');
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
            $request->add('car_no', '');
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
            $request->add('car_no', '');
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
    
    ///// 社用車の編集 処理
    protected function carEdit($request, $model)
    {
        $car_no    = $request->get('car_no');             // 社用車番号
        $car_name  = $request->get('car_name');           // 社用車名
        $car_dup   = $request->get('car_dup');            // 社用車の重複チェック
        if ($model->car_edit($car_no, $car_name, $car_dup)) {
            // 登録できたのでcar_no, car_nameの<input>データを消す
            $request->add('car_no', '');
            $request->add('car_name', '');
            $request->add('car_dup', '');
        }
    }
    
    ///// 社用車の削除 処理
    protected function carOmit($request, $model)
    {
        $car_no    = $request->get('car_no');             // 社用車番号
        $car_name  = $request->get('car_name');           // 社用車名
        $response = $model->car_omit($car_no, $car_name);
        $request->add('car_no', '');                       // 削除時はコピーは必要ない
        $request->add('car_name', '');
        $request->add('car_dup', '');
    }
    
    ///// 社用車の有効・無効 処理
    protected function carActive($request, $model)
    {
        $car_no    = $request->get('car_no');             // 社用車番号
        $car_name  = $request->get('car_name');           // 社用車名
        if ($model->car_activeSwitch($car_no, $car_name)) {
            $request->add('car_no', '');
            $request->add('car_name', '');
            $request->add('car_dup', '');
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
    
    ///// 計画有給の削除(完全削除) 処理
    protected function holydayDelete($request, $model)
    {
        $response = $model->hdelete($request);                       // キャンセルのメール対応版
        if ($response) {
            $request->add('showMenu', 'Holyday');                    // どっちにしろ同じ画面へ
        } else {
            $request->add('showMenu', 'Holyday');                    // どっちにしろ同じ画面へ
        }
    }
} // End off Class MeetingSchedule_Controller

?>
