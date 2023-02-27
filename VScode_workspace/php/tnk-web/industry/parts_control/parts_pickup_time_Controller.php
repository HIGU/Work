<?php
//////////////////////////////////////////////////////////////////////////////
// 資材管理の部品出庫 着手・完了時間 集計用  MVC Controller 部              //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/12 Created   parts_pickup_time_Controller.php                    //
// 2005/10/04 出庫作業者の登録テーブルに有効・無効を追加  伴うメソッド追加  //
// 2005/12/10 着手・完了時間の修正用ロジックを追加                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class PartsPickupTime_Controller
{
    ///// Private properties
    private $current_menu;                  // メニュー切替
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用データ取得
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list';    // 指定がない場合は一覧表を表示(特に初回)
        
        ///// キーフィールド & リクエスト データ取得
        $serial_no  = $request->get('serial_no');           // table連番 キーフィールド
        $user_id    = $request->get('user_id');             // 社員番号
        $plan_no    = $request->get('plan_no');             // 計画番号
        $user_name  = $request->get('user_name');           // 氏名(出庫作業者登録時の氏名)
        
        //////////////// 登録・修正・削除の POST 変数を ローカル変数に登録
        $apend      = $request->get('apend');               // 出庫着手の入力
        $delete     = $request->get('delete');              // 出庫着手の取消
        $editEnd    = $request->get('editEnd');             // 出庫完了の入力
        $editCancel = $request->get('editCancel');          // 出庫完了の取消
        $userEdit   = $request->get('userEdit');            // 出庫作業者の登録・変更
        $userOmit   = $request->get('userOmit');            // 出庫作業者の削除
        $userActive = $request->get('userActive');          // 出庫作業者の有効・無効(トグル)
        
        ////////// MVC の Model 部の 実行部ロジック切替
        if ($apend != '') {                                 // 出庫着手の入力 (追加)
            $response = $model->table_add($plan_no, $user_id);
            if ($response) {
                $request->add('plan_no', '');               // 登録できたのでplan_noの<input>データを消す
            } else {
                $current_menu = 'apend';                    // 登録出来なかったので追加画面にする
            }
        } elseif ($delete != '') {                          // 出庫着手の取消 (完全削除)
            $response = $model->table_delete($serial_no, $plan_no, $user_id);
            if (!$response) $current_menu = 'list';             // 削除出来なかったので出庫着手一覧画面にする
        } elseif ($editEnd != '') {                         // 出庫完了の入力 (変更)
            $response = $model->table_change('end', $serial_no, $user_id);
            if ($response) {
                // $current_menu = 'EndList';                      // 変更したので出庫完了一覧画面にする
                $request->add('plan_no', '');                   // 完了出来たのでplan_noの<input>データを消す
            } else {
                $current_menu = 'list';                         // 変更出来なかったので出庫着手一覧画面にする
            }
        } elseif ($editCancel != '') {                      // 出庫完了の取消 (変更)
            $response = $model->table_change('cancel', $serial_no, $user_id);
            if ($response) {
                // $current_menu = 'list';                         // 変更したので出庫着手一覧画面にする
            } else {
                $current_menu = 'EndList';                      // 変更出来なかったので出庫完了一覧画面にする
            }
        } elseif ($userEdit != '') {                        // 出庫作業者の登録・変更
            if ($user_name == '') {
                $request->add('user_name', $model->getUserName($user_id) );
            } else {
                if ($model->user_edit($user_id, $user_name)) {
                    // 登録できたのでuser_id, user_nameの<input>データを消す
                    $request->add('user_id', '');
                    $request->add('user_name', '');
                }
            }
        } elseif ($userOmit != '') {                        // 出庫作業者の削除
            $response = $model->user_omit($user_id, $user_name);
        } elseif ($userActive != '') {                      // 出庫作業者の有効・無効(トグル)
            if ($model->user_active($user_id, $user_name)) {
                $request->add('user_id', '');
                $request->add('user_name', '');
            }
        } elseif ($request->get('timeEdit') != '') {        // 着手・完了時間の修正
            $response = $model->timeEdit($request);
            if ($response) {
                $current_menu = 'EndList';                  // 変更出来たので出庫完了一覧画面にする
            } else {
                $current_menu = 'TimeEdit';                 // 変更出来なかったので時間修正画面にする
            }
        }
        
        $this->current_menu = $current_menu;
        
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('pickup');
        
        ///// キーフィールド & リクエスト データ取得
        $serial_no  = $request->get('serial_no');           // table連番 キーフィールド
        $user_id    = $request->get('user_id');             // 社員番号
        $plan_no    = $request->get('plan_no');             // 計画番号
        $user_name  = $request->get('user_name');           // 氏名(出庫作業者登録時の氏名)
        $userEdit   = $request->get('userEdit');            // 出庫作業者の登録・変更
        $userOmit   = $request->get('userOmit');            // 出庫作業者の削除
        $userCopy   = $request->get('userCopy');            // 出庫作業者の変更フラグ
        $userActive = $request->get('userActive');          // 出庫作業者の有効・無効(トグル)
        $current_menu = $this->current_menu;                // 共通メニュー切替用
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の Model部の View部の処理
        switch ($this->current_menu) {
        case 'EndList':                                     // 出庫完了 一覧表 表示
            $rows = $model->getViewDataEndList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParm = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('parts_pickup_time_ViewEndList.php');
            break;
        case 'apend':                                       // 出庫着手の入力 (追加) 複数の計画対応のため ApendListを取得する
            if ($user_id != '') {                           // user_id が指定されている時だけListを取得する
                $rows = $model->getViewDataApendList($user_id, $result);
                $res  = $result->get_array();
            }
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $userRows = $model->getViewActiveUser($result);
            $userRes  = $result->get_array();
            if ($user_id == '') {
                require_once ('parts_pickup_time_ViewApendUserID.php');
            } else {
                require_once ('parts_pickup_time_ViewApend.php');
            }
            break;
        case 'user':                                        // 出庫 作業者 登録 一覧表 表示
            $rows = $model->getViewUserList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            if ($userCopy == 'go') {
                $focus    = 'user_name';
                $readonly = "readonly style='background-color:#d6d3ce;'";
            } else {
                $focus    = 'user_id';
                $readonly = '';
            }
            require_once ('parts_pickup_time_ViewUser.php');
            break;
        case 'TimeEdit':                                    // 着手・完了の時間 修正 画面 表示
            if ($request->get('timeEdit') == '') {
                $rows = $model->getViewDataEdit($serial_no, $result);
                $plan_no    = $result->get('plan_no');
                $assy_no    = $result->get('assy_no');
                $assy_name  = $result->get('assy_name');
                $plan_pcs   = $result->get('plan_pcs');
                $user_id    = $result->get('user_id');
                $user_name  = $result->get('user_name');
                $str_time   = $result->get('str_time');
                $end_time   = $result->get('end_time');
                $serial_no  = $result->get('serial_no');
                $pick_time  = $result->get('pick_time');
                // これより以下は修正用データ
                $str_year   = $result->get('str_year');
                $str_month  = $result->get('str_month');
                $str_day    = $result->get('str_day');
                $str_hour   = $result->get('str_hour');
                $str_minute = $result->get('str_minute');
                $end_year   = $result->get('end_year');
                $end_month  = $result->get('end_month');
                $end_day    = $result->get('end_day');
                $end_hour   = $result->get('end_hour');
                $end_minute = $result->get('end_minute');
            } else {    // 登録エラーの場合はリクエストデータを表示する
                $rows = 1;  // rowsをエミュレート
                $plan_no    = $request->get('plan_no');
                $assy_no    = $request->get('assy_no');
                $assy_name  = $request->get('assy_name');
                $plan_pcs   = $request->get('plan_pcs');
                $user_id    = $request->get('user_id');
                $user_name  = $request->get('user_name');
                $str_time   = $request->get('str_time');
                $end_time   = $request->get('end_time');
                $serial_no  = $request->get('serial_no');
                $pick_time  = $request->get('pick_time');
                // これより以下は修正用データ
                $str_year   = $request->get('str_year');
                $str_month  = $request->get('str_month');
                $str_day    = $request->get('str_day');
                $str_hour   = $request->get('str_hour');
                $str_minute = $request->get('str_minute');
                $end_year   = $request->get('end_year');
                $end_month  = $request->get('end_month');
                $end_day    = $request->get('end_day');
                $end_hour   = $request->get('end_hour');
                $end_minute = $request->get('end_minute');
            }
            $pageParm = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('parts_pickup_time_TimeEdit.php');
            break;
        case 'list':                                        // 出庫着手 一覧表 表示
        default:                // リクエストデータにエラーの場合は初期値の着手一覧を表示
            $rows = $model->getViewDataList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            require_once ('parts_pickup_time_ViewList.php');
            break;
        }
        
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
