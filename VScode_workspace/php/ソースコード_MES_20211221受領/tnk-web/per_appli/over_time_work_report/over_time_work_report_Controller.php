<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告                                                           //
//                                                         MVC Controller 部  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_Controller.php                    //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class over_time_work_report_Controller
{
    ///// Private properties

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用 リクエスト データ取得
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'Appli');              // 指定がない場合は一覧表を表示(特に初回)
        }
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        $debug = $request->get('debug');   // デバッグフラグ取得

        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('over_time_work_report');

        ///// メニュー切替 リクエスト データ取得
        $showMenu   = $request->get('showMenu');            // ターゲットメニューを取得

        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        ////////// MVC の Model部の View部に渡すデータ生成
        // ログインユーザーUID
        $login_uid = $model->getUID();
        
        // 指定日、指定部署
        $date = $request->get('w_date');        // 指定日付取得
        $bumon = $request->get('ddlist_bumon'); // 指定部署取得
        
        // 作業日セット（照会の単日）
        if( $request->get('ddlist_year') == "" ) {
            $def_y = date('Y'); $def_m = date('m'); $def_d = date('d'); // 初期値（当日）
        } else {
            $def_y = $request->get('ddlist_year');  // 選択された 年
            $def_m = $request->get('ddlist_month'); // 選択された 月
            $def_d = $request->get('ddlist_day');   // 選択された 日
        }

        // 会社カレンダーの休日情報を取得。
        $holiday = json_encode($model->getHolidayRang($def_y-1,$def_y+1));

        switch ($showMenu) {
            case 'Appli':   // 申請（入力）画面
                if( $model->ReportCreate($request) ) {  // DBの基本データ作成 OK なら
                    $model->AppliUp($request);      // 申告情報登録処理
                    $model->AppliAdd($request);     // 申告行の追加処理
                    $model->UpComment($request);    // コメント更新処理
                }
                $cancel_uid = $request->get('cancel_uid');
                if( $cancel_uid ) {   // [取消実行]ボタンが押された。
                    $model->Cancel($request);
                    $cancel_name = $model->getName($cancel_uid);
                    $_SESSION['s_sysmsg'] .= "$cancel_name 様 の 取り消し完了しました。";
                }
                $list_view = $request->get('list_view');  // 'on' or NULL
                if( $list_view != "on" ) {
                    // キャプション
                    $menu->set_caption('作業日、部署名を選択後、[読込み]をクリックして下さい。');
                } else {
                    // キャプション
                    $menu->set_caption('事前申請 または、残業結果報告を登録して下さい。');
                    // 表示用データ取得
                    $rows = $model->getViewData($date, $bumon, $field, $res);
                    if( $rows <= 0 ) {
                        $rows = $model->GetNameList($bumon, $res);          // 登録がなければ、指定部署の氏名一覧を取得
                        $rows = $model->NameListCheck($date, $res, $rows);  // 他部門に登録があった場合弾く
                        $view_data = false; // 表示データ取得 NG.
                    } else {
                        $view_data = true;  // 表示データ取得 OK.
                    }
                    $now_dt  = new DateTime();                      // 現在日時
//                    $now_dt  = new DateTime("20211008");            // TEST 現在日時
                    $time_limit = '17:15';                          // 取消可能時間
//                    $time_limit = '12:15';                          // TEST 取消可能時間
                    $work_dt = new DateTime("$date $time_limit");   // 作業日の17:15
                    $limit_over = false;    // 事前申請可能
                    if( $now_dt > $work_dt ) {
                        $limit_over = true;
                    }
                }
                break;
            case 'Cancel':  // 申請（取消）画面
                $list_view   = $request->get('list_view');      // 'on' or NULL
                $cancel_uid  = $request->get('cancel_uid');     // 取り消し対象者UID
                $cancel_uno  = $request->get('cancel_uno');     // 取り消し対象者番号
                $type        = $request->get('type');           // type = 'yo' or 'ji'
                $cancel_name = $model->getName($cancel_uid);    // 取り消し対象者名
                // キャプション
                $menu->set_caption('取り消し理由を入力し[取消 実行]をクリックして下さい。');
                break;
            case 'Judge':   // 判定（承認）画面
                if( $request->get('admit') ) {
                    $model->AdmitUp($request);  // 承認処理
                }
                // キャプション
                $menu->set_caption('定時間外作業申告リスト');
                // 事前申請(1) or 事前申請不在未承認(2) or 残業結果報告(3)
                if( !($select = $request->get('select_radio')) ) $select = 1;   // 初期値 事前申請(1)
                
                if( $select==1 || $select==2 ) {
                    $column = "yo_ad_"; // 事前申請
                } else {
                    $column = "ji_ad_"; // 残業結果報告
                }
                
                $rows   = 0;  // 初期値
                $pos_na = $model->getPostsName();   // 'ka' or 'bu' or 'ko'
                $pos_no = $model->getPostsNo();     // 1 or 2 or 3
                $where0 = $column . "rt!='-1'"; // xx_ad_xx!=-1
                $where  = $where0;
                
                // 課長、部長の不在者チェック
                $absence_ka = $absence_bu = false;  // 不在者フラグ
                if( $pos_no > 1 ) { // 部長、工場長の場合
                    $deploy_rows = $model->getDeployAbsence($deploy_res, $absence_ka, $absence_bu);
                    if( $absence_bu || $absence_ka ) {  // 部長 or 課長 不在者あり
                        $where = "yo_ad_rt!='-1'";
                        // 不在未承認 選択時に表示するデータ取得
                        $rows = $model->GetUnapproved($deploy_res, $deploy_rows, $where, $res);
                        if( $rows < 0 ) $absence_bu = $absence_ka = false;
                    }
                }
                if( $select==2 && !$absence_bu && !$absence_ka ) $select=1; // 不在未承認 選択でも不在者なしなら１へ変更
                
                if( $select==1 || $select==3 ) {    // 未承認 選択時
                    if( $pos_na ) { // 承認者 'ka' or 'bu' or 'ko'
                        $where  = $where0;                          // xx_ad_xx!=-1
                        $where1 = $column . $pos_na . "='m'";       // xx_ad_xx='m'
                        $where2 = $model->getWhereDeploy();         // (deploy='xxx' OR deploy='xxx')
                        $where3 = $column . "st=" . ($pos_no-1);    // xx_ad_st=(x-1)
                        $where .= " AND " . $where1 . " AND " . $where2 . " AND " . $where3;   // xx_ad_xx='m' AND (deploy='xxx課' OR deploy='xxx課') AND xx_ad_st=(x-1)
                        $rows = $model->GetDateDeploy($where, $res); // 未承認のある日付と部署を取得
                    }
                }
                break;
            case 'Quiry':   // 照会（検索）画面
                // キャプション
                $menu->set_caption('照会条件を選択して、[実行]をクリックして下さい。');
                // 単日(1) or 連日(3)
                if( !($d_radio = $request->get('days_radio')) ) $d_radio = 1; // 初期値 単日(1)
                // 連日の年月日をセット
                if( $request->get('ddlist_year2') == "" ) {
                    $def_y2 = date('Y'); $def_m2 = date('m'); $def_d2 = date('d');
                } else {
                    $def_y2 = $request->get('ddlist_year2');    // 選択された 年
                    $def_m2 = $request->get('ddlist_month2');   // 選択された 月
                    $def_d2 = $request->get('ddlist_day2');     // 選択された 日
                }
                if( !($m_radio = $request->get('mode_radio')) ) $m_radio = 1; // 初期値 指定なし(1)
                $e_check0 = $request->get('err_check0');
                $e_check1 = $request->get('err_check1');
                $e_check2 = $request->get('err_check2');
                $e_check3 = $request->get('err_check3');
                break;
            case 'Results':   // 照会（結果） 画面
                $menu->set_RetUrl(PER_APPLI . "over_time_work_report/over_time_work_report_Main.php"); // 通常は指定する必要はない
                
                // [戻る]ボタンで、戻った時にデータを受け渡す為POSTデータセット
                $menu->set_retPOST('login_uid', $request->get('login_uid'));    // TEST用
                $menu->set_retPOST('showMenu', 'Quiry');
                $menu->set_retPOST('days_radio', $request->get('days_radio'));
                $menu->set_retPOST('ddlist_year', $request->get('ddlist_year'));
                $menu->set_retPOST('ddlist_month', $request->get('ddlist_month'));
                $menu->set_retPOST('ddlist_day', $request->get('ddlist_day'));
                $menu->set_retPOST('ddlist_year2', $request->get('ddlist_year2'));
                $menu->set_retPOST('ddlist_month2', $request->get('ddlist_month2'));
                $menu->set_retPOST('ddlist_day2', $request->get('ddlist_day2'));
                $menu->set_retPOST('ddlist_bumon', $request->get('ddlist_bumon'));
                $menu->set_retPOST('s_no', $request->get('s_no'));
                $menu->set_retPOST('mode_radio', $request->get('mode_radio'));
                $menu->set_retPOST('err_check0', $request->get('err_check0'));
                $menu->set_retPOST('err_check1', $request->get('err_check1'));
                $menu->set_retPOST('err_check2', $request->get('err_check2'));
                $menu->set_retPOST('err_check3', $request->get('err_check3'));
                
                // 表示データ取得
                $rows = $model->getResultsView($request, $res);
                break;
            default:        // リクエストデータにエラーの場合は初期値の一覧を表示
                break;
        }
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {
            case 'Appli':   // 申請（入力）画面
                require_once ('over_time_work_report_ViewAppli.php');
                break;
            case 'Cancel':  // 申請（取消）画面
                require_once ('over_time_work_report_ViewCancel.php');
                break;
            case 'Judge':   // 判定（承認）画面
                require_once ('over_time_work_report_ViewJudge.php');
                break;
            case 'Quiry':   // 照会（検索）画面
                require_once ('over_time_work_report_ViewInquiry.php');
                break;
            case 'Results':   // 照会（結果） 画面
                require_once ('over_time_work_report_ViewResults.php');
                break;
            default:        // リクエストデータにエラーの場合は初期値の一覧を表示
                require_once ('over_time_work_report_ViewAppli.php');
                break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class over_time_work_report_Controller

?>
