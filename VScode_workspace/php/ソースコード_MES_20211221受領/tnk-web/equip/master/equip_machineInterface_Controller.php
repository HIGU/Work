<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理の機械とインターフェースのリレーション 照会＆メンテナンス    //
//              MVC Controller 部                                           //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/27 Created   equip_machineInterface_Controller.php               //
//            equip_function.phpを外すため getFactory()をControllerクラスへ //
// 2005/08/03 interface は JavaScript の予約語なので inter へ変更           //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ControllerをClass化しMain Controller を新設 View部の埋め込み  //
//            変数名がインスタンスに対応していないため__constructで全て処理 //
// 2005/09/18 キーフィールドpreMac_no をEdit側でなくここで設定              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class EquipMachineInterface_Controller
{
    ///// Private properties
    // private $uniq;                      // ブラウザーのキャッシュ対策用
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5 移行は __construct() に変更) (デストラクタ__destruct())
    public function __construct($menu, $request, $result, $model, $session)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('macint');
        
        /////////// 工場区分と工場名を取得する
        $factory   = $session->get('factory');
        $fact_name = $session->getFactName();
        
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用データ取得
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // 指定がない場合は一覧表を表示(特に初回)
        ///// 表示用フィールド データ取得
        $mac_no     = $request->get('mac_no');          // --|
        $interface  = $request->get('inter');           // --|--複合キー
        $csv        = $request->get('csv');
        $file_name  = $request->get('file_name');
        $regdate    = $request->get('regdate');
        $last_date  = $request->get('last_date');
        $last_user  = $request->get('last_user');
        ////////// 確認フォームで取消が押された時のリクエスト取得
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* 修正用 *********/
        $preMac_no    = $request->get('preMac_no');
        $preInterface = $request->get('preInterface');
        
        //////////////// 登録・修正・削除の POST 変数を ローカル変数に登録
        $apend  = $request->get('apend');
        $edit   = $request->get('edit');
        $delete = $request->get('delete');
        
        ////////// 確認フォームへ渡すデータ取得
        $confirm_apend  = $request->get('confirm_apend');
        $confirm_edit   = $request->get('confirm_edit');
        $confirm_delete = $request->get('confirm_delete');
        if ($confirm_apend != '') {
            $current_menu = 'confirm_apend';
        } elseif ($confirm_edit != '') {
            $current_menu = 'confirm_edit';
        } elseif ($confirm_delete != '') {
            $current_menu = 'confirm_delete';
        }
        ////////// 確認フォームで取消が押された時のステータスを取得しメニュー切替
        if ($cancel_apend != '') {
            $current_menu = 'apend';
        } elseif ($cancel_edit != '') {
            $current_menu = 'edit';
        } elseif ($cancel_del != '') {
            $current_menu = 'edit';
        }
        
        ////////// MVC の Model部の実行ロジック切替
        if ($apend != '') {         ////////// マスター追加
            $response = $model->table_add($mac_no, $interface, $csv, $file_name);
            if (!$response) $current_menu = 'apend';    // 登録出来なかったので追加画面にする
        } elseif ($edit != '') {    ////////// マスター 変更
            $response = $model->table_change($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name);
            if (!$response) {
                $current_menu = 'edit';                 // 変更出来なかったので編集画面にする
                $cancel_edit  = '取消';                 // 変更時のデータで表示
            }
        } elseif ($delete != '') {  ////////// マスター完全削除
            $response = $model->table_delete($mac_no, $interface);
            if (!$response) $current_menu = 'edit';     // 削除出来なかったので編集画面にする
        }
        
        ////////// MVC の Model部のメニューロジック切替 & 結果取得
        switch ($current_menu) {
        case 'list':            // 一覧表 表示
            $rows = $model->getViewDataList($result);
            $res = $result->get_array();
            break;
        case 'edit':            // マスター修正
            if ($preMac_no == '') $preMac_no = $mac_no;   // 前の番号が設定されていない場合は初回と判定してmac_noを代入する
            $mac_cnt = $model->getViewMac_noName($mac_no_name);   // 機械番号と機械名の配列を取得
            $inter_cnt = $model->getViewInterName($inter_name);   // インターフェース番号と名前の配列を取得
            if ($cancel_edit == '') {   // 確認フォームの取消の時は前のデータをそのまま使う
                $rows = $model->getViewDataEdit($mac_no, $interface, $result);
                $csv        = $result->get_once('csv');
                $file_name  = $result->get_once('file_name');
                $regdate    = $result->get_once('regdate');
                $last_date  = $result->get_once('last_date');
            }
            break;
        case 'confirm_apend':   // 登録の確認
        case 'confirm_edit':    // 変更の確認
        case 'confirm_delete':  // 削除の確認
        case 'apend':
            $mac_cnt = $model->getViewMac_noName($mac_no_name);   // 機械番号と機械名の配列を取得
            $inter_cnt = $model->getViewInterName($inter_name);   // インターフェース番号と名前の配列を取得
            break;
        }
        ////////// 単体の機械名称とインターフェース名の取得
        // $mac_name   = $model->getViewMacName($mac_no);
        // $interface_name = $model->getViewInterfaceName($interface);
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($current_menu) {
        case 'list':            // 一覧表 表示
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage');
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage', 'pageRec');
            require_once ('equip_machineInterface_ViewList.php');
            break;
        case 'apend':           // マスター追加
            require_once ('equip_machineInterface_ViewApend.php');
            break;
        case 'edit':            // マスター修正
            require_once ('equip_machineInterface_ViewEdit.php');
            break;
        case 'confirm_apend':   // 登録の確認
        case 'confirm_edit':    // 変更の確認
        case 'confirm_delete':  // 削除の確認
            require_once ('equip_machineInterface_ViewConfirm.php');
            break;
        default:                // リクエストデータにエラー
            require_once ('equip_machineInterface_ViewList.php');
        }
    }
    ///// MVC View部の処理
    public function display()
    {
        
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
} // Class END

?>
