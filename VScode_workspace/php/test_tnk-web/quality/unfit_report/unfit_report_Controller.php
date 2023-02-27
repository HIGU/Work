<?php
//////////////////////////////////////////////////////////////////////////////
// 全社共有 不適合報告書の照会・メンテナンス                                //
//                                                       MVC Controller 部  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_Controller.php                         //
// 2008/08/29 masterstで本稼動開始                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
////////////// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
////////////// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class UnfitReport_Controller
{
    ////////// Private properties
    ////////// private $showMenu;                           // メニュー切替
    ////////// private $listSpan;                           // 一覧表示時の期間(1日間,7日間,14,28...)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model)
    {
        ////// POST Data の初期化＆設定
        ////// メニュー切替用 リクエスト データ取得
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'IncompleteList');    // 指定がない場合は一覧表を表示(特に初回)
        }
        
        ////// 登録・修正・削除の 実行指示リクエスト
        $apend      = $request->get('Apend');               // 不適合報告書の登録
        $partsflg   = $request->get('partsflg');            // 部品名表示の為のフラグ
        $assyflg    = $request->get('assyflg');             // 製品名表示の為のフラグ
        $delete     = $request->get('Delete');              // 不適合報告書の取消(削除)
        $edit       = $request->get('Edit');                // 不適合報告書の変更
        $follow     = $request->get('Follow');              // フォローアップの入力
        ////// グループ用
        $groupEdit  = $request->get('groupEdit');           // グループの登録・変更
        $groupOmit  = $request->get('groupOmit');           // グループの削除
        $groupActive= $request->get('groupActive');         // グループの有効・無効(トグル)
        
        ////// MVC の Model 部の 実行部ロジック切替
        ////// 不適合報告書の編集
        if ($apend != '') {                                 // 不適合報告書の登録 (追加)
            if ($partsflg == '' && $assyflg == '') {        // 部品・製品名表示フラグがONでない場合は追加
                $this->apend($request, $model);
            }
        } elseif ($delete != '') {                          // 不適合報告書の取消 (完全削除)
            $this->delete($request, $model);
        } elseif ($edit != '') {                            // 不適合報告書の変更
            if ($partsflg == '' && $assyflg == '') {        // 部品・製品名表示フラグがONでない場合は追加
                $this->edit($request, $model);
            }
        } elseif ($follow != '') {                          // フォローアップの入力
            $this->follow($request, $model);
            $request->add('showMenu', 'CompleteList');
        ////// 報告先のグループ編集
        } elseif ($groupEdit != '') {                       // グループの登録・変更
            $this->groupEdit($request, $model);
        } elseif ($groupOmit != '') {                       // グループの削除
            $this->groupOmit($request, $model);
        } elseif ($groupActive != '') {                     // グループの有効・無効(トグル)
            $this->groupActive($request, $model);
        }
    }
    
    ////////// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        ////// カレンダークラスをinclude
        require_once ('../../CalendarClass.php');              // カレンダークラス
        
        ////// クラスのインスタンス作成
        ////// calendar(開始曜日, 当月以外の日付を表示するかどうか) の形で指定します。
        ////// ※開始曜日（0-日曜 から 6-土曜）、当月以外の日付を表示（0-No, 1-Yes）
        $calendar_now  = new Calendar(0, 0);
        $calendar_nex1 = new Calendar(0, 0);
        $calendar_nex2 = new Calendar(0, 0);
        $calendar_pre  = new Calendar(0, 0);

        ////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('unfit');
        
        ////// メニュー切替 リクエスト データ取得
        $showMenu   = $request->get('showMenu');            // ターゲットメニューを取得
        $listSpan   = $request->get('listSpan');            // 一覧表示時の期間(1日間,7日間,14,28...)
        
        ////// キーフィールド リクエスト データ取得
        $year       = $request->get('year');                // 発生年月日の年４桁
        $month      = $request->get('month');               // 発生年月日の月２桁
        $day        = $request->get('day');                 // 発生年月日の日２桁
        $serial_no  = $request->get('serial_no');           // table連番 キーフィールド
        $atten_flg  = $request->get('atten_flg');           // 報告先展開フラグ
        
        ////// 確認フォームで取消が押された時のリクエスト取得
        $cancel_edit   = $request->get('cancel_edit');
        
        ////// 登録・編集 データのリクエスト取得
        $subject       = $request->get('subject');          // 不適合内容
        $occur_time    = $request->get('occur_time');       // 発生年月日
        $sponsor       = $request->get('sponsor');          // 作成者
        $receipt_no    = $request->get('receipt_no');       // 受付No.
        $atten         = $request->get('atten');            // 報告先(attendance) (配列) グループでも使用
        $mail          = $request->get('mail');             // メールの送信 Y/N
        $suihei        = $request->get('suihei');           // 水平展開 Y/N
        $kanai         = $request->get('kanai');            // 課内展開 Y/N
        $kagai         = $request->get('kagai');            // 課外展開 Y/N
        $hyoujyun      = $request->get('hyoujyun');         // 標準書展開 Y/N
        $kyouiku       = $request->get('kyouiku');          // 教育実施 Y/N
        $system        = $request->get('system');           // システム Y/N
        $measure       = $request->get('measure');          // 対策実施 Y/N
        $occuryear     = $request->get('occuryear');        // 発生源対策実施予定年
        $occurmonth    = $request->get('occurmonth');       // 発生源対策実施予定月
        $occurday      = $request->get('occurday');         // 発生源対策実施予定日
        $issueyear     = $request->get('issueyear');        // 流出原対策実施予定年
        $issuemonth    = $request->get('issuemonth');       // 流出原対策実施予定月
        $issueday      = $request->get('issueday');         // 流出原対策実施予定日
        $place         = $request->get('place');            // 発生場所
        $section       = $request->get('section');          // 責任部門
        $assy_no       = $request->get('assy_no');          // 製品番号
        $parts_no      = $request->get('parts_no');         // 部品番号
        $occur_cause   = $request->get('occur_cause');      // 発生原因
        $unfit_num     = $request->get('unfit_num');        // 不適合数量
        $issue_cause   = $request->get('issue_cause');      // 流出原因
        $issue_num     = $request->get('issue_num');        // 流出数量
        $unfit_dispose = $request->get('unfit_dispose');    // 不適合品の処置
        $occur_measure = $request->get('occur_measure');    // 発生源対策
        $issue_measure = $request->get('issue_measure');    // 流出対策
        $follow_who    = $request->get('follow_who');       // フォローアップ 誰が
        $follow_when   = $request->get('follow_when');      // フォローアップ いつ
        $follow_how    = $request->get('follow_how');       // フォローアップ どのように
        ////// グループ編集用
        $group_no2     = $request->get('group_no2');        // グループ番号
        $group_no      = $group_no2;         // TEST
        $group_name    = $request->get('group_name');       // グループ名
        $owner         = $request->get('owner');            // グループを個人・共有
        $groupCopy     = $request->get('groupCopy');        // グループの編集データコピー
        
        ////// カレンダーリクエストのチェック及び取得
        if ($year != '' && $month != '' && $day != '') {
            $day_now  = getdate(mktime(0, 0, 0, $month, $day, $year));
            $day_nex1 = getdate(mktime(0, 0, 0, $month+1, 1, $year));
            $day_nex2 = getdate(mktime(0, 0, 0, $month+2, 1, $year));
            $day_pre  = getdate(mktime(0, 0, 0, $month-1, 1, $year));
        } else {
        ////// 当日を取得
            $day_now  = getdate();
            $day_nex1 = getdate(mktime(0, 0, 0, $day_now['mon']+1, 1, $day_now['year']));
            $day_nex2 = getdate(mktime(0, 0, 0, $day_now['mon']+2, 1, $day_now['year']));
            $day_pre  = getdate(mktime(0, 0, 0, $day_now['mon']-1, 1, $day_now['year']));
        }
        
        ////// カレンダーの全日付をリンクにする
        if ($showMenu != 'Edit') {
            $url = $menu->out_self() . "?showMenu={$showMenu}&" . $model->get_htmlGETparm() . "&id={$uniq}";
        } else {
            $url = $menu->out_self() . "?showMenu=IncompleteList&" . $model->get_htmlGETparm() . "&id={$uniq}";
        }
        $calendar_now-> setAllLinkYMD($day_now['year'], $day_now['mon'], $url);
        $calendar_pre-> setAllLinkYMD($day_pre['year'], $day_pre['mon'], $url);
        $calendar_nex1->setAllLinkYMD($day_nex1['year'], $day_nex1['mon'], $url);
        $calendar_nex2->setAllLinkYMD($day_nex2['year'], $day_nex2['mon'], $url);
        
        ////// MVC の Model部の View部に渡すデータ生成
        switch ($showMenu) {
        case 'FollowList':                                  // 不適合報告書 フォローアップ完了リスト
            $rows = $model->getViewFollowList($result);
            $res  = $result->get_array();
            // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 報告先の複数データを取得
            $rowsAtten = array(); $resAtten = array();      // 初期化
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('FollowList', $year, $month, $day));
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'CompleteList':                                // 不適合報告書 対策完了リスト
            $rows = $model->getViewCompleteList($result);
            $res  = $result->get_array();
            // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 報告先の複数データを取得
            $rowsAtten = array(); $resAtten = array();      // 初期化
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('CompleteList', $year, $month, $day));
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'IncompleteList':                              // 不適合報告書 対策完了リスト
            $rows = $model->getViewIncompleteList($result);
            $res  = $result->get_array();
            // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // 報告先の複数データを取得
            $rowsAtten = array(); $resAtten = array();      // 初期化
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('IncompleteList', $year, $month, $day));
            // データなしの時のメッセージ生成
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'Apend':                                       // 不適合報告書の入力に必要なUserデータ
            // 不適合報告書追加時の作成者の初期値設定 (本人の社員番号)
            if ($sponsor == '') if ($_SESSION['User_ID'] != '000000') $sponsor = $_SESSION['User_ID'];
            // 部署毎の社員番号と氏名を取得して selected の設定まで行う
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // グループマスターの有効なリストを取得 (JavaScriptへ渡す)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Apend', $year, $month, $day));
            break;
        case 'Edit':                                        // 不適合報告書の変更 １レコードのデータ
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $place      = $result->get_once('place');
            $section    = $result->get_once('section');
            $occur_time = $result->get_once('occur_time');
            $sponsor    = $result->get_once('sponsor');
            $receipt_no = $result->get_once('receipt_no');
            $atten_num  = $result->get_once('atten_num');
            $mail       = $result->get_once('mail');
            // 報告先の複数データを取得
            $rowsAtten = $model->getViewAttenList($result, $serial_no);
            $resAtten  = $result->get_array();
            // 報告先の社員番号のみ抽出
            $atten = array();   // 初期化
            for ($i=0; $i<$rowsAtten; $i++) {
                $atten[$i] = $resAtten[$i][1];
            }
            // 部署毎の社員番号と氏名を取得して selected の設定まで行う
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // 発生原因のデータを取得
            $rowsCause   = $model->getViewCauseList($result, $serial_no);
            $partsflg   = $request->get('partsflg');        // 部品名表示の為のフラグ
            $assyflg    = $request->get('assyflg');         // 製品名表示の為のフラグ
            if ($partsflg == '' && $assyflg == '') {        // 部品・製品名表示フラグがONでない場合は追加
                $assy_no     = $result->get_once('assy_no');
                $parts_no    = $result->get_once('parts_no');
            } else {
                $assy_no     = $request->get('assy_no');
                $parts_no    = $request->get('parts_no');    
            }
            $occur_cause = $result->get_once('occur_cause');
            $unfit_num   = $result->get_once('unfit_num');
            $issue_cause = $result->get_once('issue_cause');
            $issue_num   = $result->get_once('issue_num');
            // 対策のデータを取得
            $rowsMeasure   = $model->getViewMeasureList($result, $serial_no);
            $unfit_dispose = $result->get_once('unfit_dispose');
            $occur_measure = $result->get_once('occur_measure');
            $issue_measure = $result->get_once('issue_measure');
            $follow_who    = $result->get_once('follow_who');
            $follow_when   = $result->get_once('follow_when');
            $follow_how    = $result->get_once('follow_how');
            $measure       = $result->get_once('measure');
            // 展開のデータを取得
            $rowsDevelop = $model->getViewDevelopList($result, $serial_no);
            $suihei      = $result->get_once('suihei');
            $kanai       = $result->get_once('kanai');
            $kagai       = $result->get_once('kagai');
            $hyoujyun    = $result->get_once('hyoujyun');
            $kyouiku     = $result->get_once('kyouiku');
            $system      = $result->get_once('system');
            // グループマスターの有効なリストを取得 (JavaScriptへ渡す)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Edit', $year, $month, $day));
            break;
        case 'Follow':                                      // フォローアップの入力・変更
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $place      = $result->get_once('place');
            $section    = $result->get_once('section');
            $occur_time = $result->get_once('occur_time');
            $receipt_no = $result->get_once('receipt_no');
            $atten_num  = $result->get_once('atten_num');
            $mail       = $result->get_once('mail');
            // 報告先の複数データを取得
            $rowsAtten = $model->getViewAttenList($result, $serial_no);
            $resAtten  = $result->get_array();
            // 報告先の社員番号のみ抽出
            $atten = array();   // 初期化
            for ($i=0; $i<$rowsAtten; $i++) {
                $atten[$i] = $resAtten[$i][1];
            }
            // 部署毎の社員番号と氏名を取得して selected の設定まで行う
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // 発生原因のデータを取得
            $rowsCause   = $model->getViewCauseList($result, $serial_no);
            $partsflg   = $request->get('partsflg');        // 部品名表示の為のフラグ
            $assyflg    = $request->get('assyflg');         // 製品名表示の為のフラグ
            if ($partsflg == '' && $assyflg == '') {        // 部品・製品名表示フラグがONでない場合は追加
                $assy_no     = $result->get_once('assy_no');
                $parts_no    = $result->get_once('parts_no');
            } else {
                $assy_no     = $request->get('assy_no');
                $parts_no    = $request->get('parts_no');    
            }
            $occur_cause = $result->get_once('occur_cause');
            $unfit_num   = $result->get_once('unfit_num');
            $issue_cause = $result->get_once('issue_cause');
            $issue_num   = $result->get_once('issue_num');
            // 対策のデータを取得
            $rowsMeasure   = $model->getViewMeasureList($result, $serial_no);
            $unfit_dispose = $result->get_once('unfit_dispose');
            $occur_measure = $result->get_once('occur_measure');
            $issue_measure = $result->get_once('issue_measure');
            $follow_who    = $result->get_once('follow_who');
            $follow_when   = $result->get_once('follow_when');
            $follow_how    = $result->get_once('follow_how');
            $measure       = $result->get_once('measure');
            // 展開のデータを取得
            $rowsDevelop = $model->getViewDevelopList($result, $serial_no);
            $suihei      = $result->get_once('suihei');
            $kanai       = $result->get_once('kanai');
            $kagai       = $result->get_once('kagai');
            $hyoujyun    = $result->get_once('hyoujyun');
            $kyouiku     = $result->get_once('kyouiku');
            $system      = $result->get_once('system');
            // フォローアップのデータを取得
            $rowsfollow     = $model->getViewFollow($result, $serial_no);
            $follow_section = $result->get_once('follow_section');
            $follow_quality = $result->get_once('follow_quality');
            $follow_opinion = $result->get_once('follow_opinion');
            $sponsor        = $result->get_once('follow_sponsor');
            $follow         = $result->get_once('follow');
            $follow_flg     = $result->get_once('follow_flg');
            // 不適合報告書追加時の作成者の初期値設定 (本人の社員番号)
            if ($sponsor == '') if ($_SESSION['User_ID'] != '000000') $sponsor = $_SESSION['User_ID'];
            // グループマスターの有効なリストを取得 (JavaScriptへ渡す)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // 表題(キャプション)の生成
            $menu->set_caption($model->get_caption('Follow', $year, $month, $day));
            break;
        case 'Group':                                       // グループの 登録 一覧表 表示
            if ($group_no != '') {                          // 編集がクリックされてgroup_noがセットされている時
                // グループ報告先の複数データを取得
                $rowsAtten = $model->getGroupAttenList($result, $group_no);
                $resAtten  = $result->get_array();
                // グループ報告先の社員番号のみ抽出
                $atten = array();                           // 初期化
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
            // 報告先の複数データを取得
            $rowsAtten = array(); $resAtten = array();      // 初期化
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getGroupAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            break;
        }
        
        ////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////// MVC の View 部の処理
        switch ($showMenu) {
        case 'Edit':                                        // 不適合報告書の変更 画面
            require_once ('unfit_report_ViewApend.php');    // 兼用
            break;
        case 'Apend':                                       // 不適合報告書の入力 画面
            require_once ('unfit_report_ViewApend.php');
            break;
        case 'Follow':                                      // フォローアップの入力・変更 画面
            require_once ('unfit_report_ViewFollow.php');
            break;
        case 'Group':                                       // グループの 一覧表 表示
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'group_no';
                $readonly = '';
            }
            require_once ('unfit_report_ViewGroup.php');
            break;
        default:                                            // リクエストデータにエラーの場合は初期値の一覧を表示
            require_once ('unfit_report_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 登録(追加) 処理
    protected function apend($request, $model)
    {
        $response = $model->add($request);
        if ($response) {
            $request->add('subject', '');                   // 登録できたので入力フィールドを消す
            $request->add('occur_time', '');
            $request->add('place', '');
            $request->add('section', '');
            $request->add('assy_no', '');
            $request->add('parts_no', '');
            $request->add('occur_cause', '');
            $request->add('unfit_num', '');
            $request->add('issue_cause', '');
            $request->add('issue_num', '');
            $request->add('unfit_dispose', '');
            $request->add('occur_measure', '');
            $request->add('issue_measure', '');
            $request->add('follow_who', '');
            $request->add('follow_when', '');
            $request->add('follow_how', '');
            $request->add('measure', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'IncompleteList');    // 登録できたので一覧画面にする。
        }
    }
    protected function follow($request, $model)
    {
        $response = $model->follow($request);
        if ($response) {
            $request->add('follow_section', '');            // 登録できたので入力フィールドを消す
            $request->add('follow_quality', '');
            $request->add('follow_opinion', '');
            $request->add('follow', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'completeList');      // 登録できたので一覧画面にする。
        }
    }
    ////////// 削除(完全削除) 処理
    protected function delete($request, $model)
    {
        ////// $serial_no  = $request->get('serial_no');        // シリアル番号
        ////// $subject    = $request->get('subject');          // 不適合内容
        ////// $response = $model->delete($serial_no, $subject);
        $response = $model->delete($request);               // キャンセルのメール対応版
        if ($response) {
            $request->add('showMenu', 'IncompleteList');    // 削除出来たので一覧画面にする。
        } else {
            $request->add('showMenu', 'Edit');              // 削除出来なかったので編集画面を復元
        }
    }
    
    ////////// 編集処理  処理
    protected function edit($request, $model)
    {
        if ($model->edit($request)) {
            $request->add('subject', '');                   // 変更できたので入力フィールドを消す
            $request->add('occur_time', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'IncompleteList');    // 登録できたので一覧画面にする。
        } else {
            $request->add('showMenu', 'Edit');              // 登録出来なかったので編集画面を復元
        }
    }
    
    ////////// 報告先グループの編集 処理
    protected function groupEdit($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // グループ番号
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // グループ名
        $atten      = $request->get('atten');               // 報告先(attendance) (配列) グループでも使用
        $owner      = $request->get('owner');               // グループを個人・共有
        if ($model->group_edit($group_no, $group_name, $atten, $owner)) {
            // 登録できたのでgroup_no, group_nameの<input>データを消す
            $request->add('group_no', '');
            $request->add('group_no2', '');
            $request->add('group_name', '');
            $request->del('atten');
        }
    }
    
    ////////// 報告先グループの削除 処理
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
    
    ////////// 報告先グループの有効・無効 処理
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
    
} //////////// End off Class UnfitReport_Controller

?>
