<?php
////////////////////////////////////////////////////////////////////////////////
// 食堂メニュー予約                                                           //
//                                                         MVC Controller 部  //
// Copyright (C) 2022-2022 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2022/04/29 Created meal_appli_ViewMenuSelect.php                           //
// 2022/05/07 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class meal_appli_Controller
{
    ///// Private properties
    private $debug = "";// デバッグフラグ
    private $showMenu = "";// 

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data の初期化＆設定
        $this->debug = $request->get('debug');   // デバッグフラグ取得
        
        ///// メニュー切替用 リクエスト データ取得
        $this->showMenu = $request->get('showMenu');// 
        if( $this->showMenu == '' ) {
            $request->add('showMenu', 'MenuSelect');
            $this->showMenu = 'MenuSelect';//'Main';// 初回
        }
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('meal_appli');

        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        ////////// MVC の Model部の View部に渡すデータ生成
        // ログインユーザーUID
        $login_uid = $model->getUID();
        
        // 開始～終了期間をセット
        $week = date('w');
        $str_date = date('Ymd', strtotime("-{$week} day"));         // 開始日（当週の日曜日）
        $end_date = date('Ymd', strtotime("$str_date +20 day"));    // 終了日（当週より翌々週の土曜日）
        
        // 会社カレンダーの休日情報を取得。
        $holiday = json_encode($model->getHolidayRang($str_date,$end_date));
        
        $model->setEventDate();
        $event_date = $model->getEventDate();
        if( $login_uid == "300667" ) {
//            $event_date = "2022-06-22"; // TEST
        }
        
        $menu_name[0] = $model->getMenuIndex();  // メニュー
        $menu_name[1] = $model->getMenuName();   // 表示名

        $order_stop = "";
        if( $model->IsOrderable() ) $order_stop = " disabled";
//        $order_stop = " disabled";   // TEST 入力禁止にさせる

        $showMenu = $request->get('showMenu');  // 
        switch ($this->showMenu) {
            case 'Main':        // 表示メニュー選択画面
                // 表示用データ取得
                break;
            case 'MenuSelect':  // メニュー選択画面
            case 'MenuGuest':   // メニュー選択（来客用）画面
                // 表示用データ取得
                $input_uid = $request->get('input_uid');
                $btn_read_disabl = '';
                $btn_cancel_disabl = 'disabled';
                $btn_save_disabl = 'disabled';
                $res_sougou = $model->getSougouInfo($input_uid);
                if( ! $model->IsSyain($input_uid) ) {
                    if($input_uid) {
                        $_SESSION['s_sysmsg'] = "社員情報が取得できません。<BR>※入力内容【{$input_uid}】をお確かめ下さい。";
                    }
                    $input_uid = "";
                    break;
                }
                if( $request->get('save') ) {
                    if( $order_stop ) {
                        $_SESSION['s_sysmsg'] .= "予約受付時間を過ぎている為、保存できませんでした。";
                    } else {
                        // 登録処理
                        if( $this->showMenu == "MenuSelect" ) {
                            $model->setMealData($str_date, $end_date, $input_uid, "My", $request);
                        } else {
                            $model->setMealData($str_date, $end_date, $input_uid, "Guest", $request);
                        }
                    }
                    $input_uid = "";
                }
                $res = array();
                if( $request->get('read') ) {
                    if( $order_stop ) {
                        $_SESSION['s_sysmsg'] .= "予約受付時間を過ぎている為、変更はできません。";
                    }
                    // 読込み処理
                    if( $this->showMenu == "MenuSelect" ) {
                        $res = $model->getMealData($str_date, $end_date, $input_uid, "My");
                    } else {
                        $res = $model->getMealData($str_date, $end_date, $input_uid, "Guest");
                    }
                    $btn_read_disabl = 'disabled';
                    $btn_cancel_disabl = '';
                    $btn_save_disabl = '';
                }
                break;
            case 'OrderInfo':   // 注文情報画面
                // 表示用データ取得
                $res = $model->getOrderInfo($str_date, $end_date);
                break;
            case 'OrderDetail': // 注文情報（詳細）画面
                // 表示用データ取得
                $res  = $model->getOrderInfo($str_date, $end_date);
                $res2 = $model->getOrderDetail($str_date, $end_date);
                $detail = $request->get('detail');
                break;
            default:            // リクエストデータがエラーの場合は初期値の表示メニュー選択画面を表示
                break;
        }
        // キャプション
        $menu->set_caption('表示項目を選択して下さい。');
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($this->showMenu) {
            case 'Main':        // 表示メニュー選択画面
                require_once ('meal_appli_ViewMain.php');
                break;
            case 'MenuSelect':  // メニュー選択画面
                require_once ('meal_appli_ViewMenuSelect.php');
                break;
            case 'MenuGuest':   // メニュー選択（来客用）画面
                require_once ('meal_appli_ViewMenuGuest.php');
                break;
            case 'OrderInfo':   // 注文情報画面
                require_once ('meal_appli_ViewOrderInfo.php');
                break;
            case 'OrderDetail': // 注文情報（詳細）画面
                require_once ('meal_appli_ViewOrderDetail.php');
                break;
            default:            // リクエストデータにエラーの場合は初期値の表示メニュー選択画面を表示
                require_once ('meal_appli_ViewMain.php');
                break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class meal_appli_Controller

?>
