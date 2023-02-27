<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械マスター の 照会 ＆ メンテナンス                               //
//              MVC Controller 部                                           //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/13 Created   equip_macMasterMnt_Controller.php                   //
// 2002/08/08 register_globals = Off 対応                                   //
// 2003/06/17 servey(監視フラグ) Y/N が変更できない不具合を修正 及び        //
//              各入力フォームをプルダウン式に変更                          //
// 2003/06/19 $uniq = uniqid('script')を追加して JavaScript Fileを必ず読む  //
// 2004/03/04 新版テーブル equip_machine_master2 への対応                   //
// 2004/07/12 Netmoni & FWS 方式を統一 スイッチ方式 そのため Net&FWS方式追加//
//            CSV 出力設定等を 監視方式へ 項目名変更                        //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/24 ディレクトリ変更 equip/ → equip/master/                      //
// 2005/06/28 MVCのController部へ変更  equip_macMasterMnt_Controller.php    //
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
class EquipMacMstMnt_Controller
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
        $uniq = $menu->set_useNotCache('machine');
        
        /////////// 工場区分と工場名を取得する
        $factoryList = $session->get('factory');
        $fact_name   = $session->getFactName();
        
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用データ取得
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // 指定がない場合は一覧表を表示(特に初回)
        ///// 表示用フィールド データ取得
        $mac_no     = $request->get('mac_no');
        $mac_name   = $request->get('mac_name');
        $maker_name = $request->get('maker_name');
        $maker      = $request->get('maker');
        $factory    = $request->get('factory');
        $survey     = $request->get('survey');
        $csv_flg    = $request->get('csv_flg');
        $sagyouku   = $request->get('sagyouku');
        $denryoku   = $request->get('denryoku');
        $keisuu     = $request->get('keisuu');
        ////////// 確認フォームで取消が押された時のリクエスト取得
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* 修正用 *********/
        $pmac_no = $request->get('pmac_no');
        
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
        
        ////////// MVC の Model 部の実行 ロジック切替
        if ($apend != '') {         ////////// マスター追加
            $response = $model->table_add($mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu);
            if (!$response) $current_menu = 'apend';    // 登録出来なかったので追加画面にする
        } elseif ($edit != '') {    ////////// マスター 修正
            $response = $model->table_change($pmac_no,$mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu);
            if (!$response) {
                $current_menu = 'edit';                 // 変更出来なかったので編集画面にする
                $cancel_edit  = '取消';                 // 変更時のデータで表示
            }
        } elseif ($delete != '') {  ////////// マスター完全削除
            $response = $model->table_delete($mac_no);
            if (!$response) $current_menu = 'edit';     // 削除出来なかったので編集画面にする
        }
        
        ////////// MVC の Model部の ロジック切替 & 結果取得
        switch ($current_menu) {
        case 'list':            // 一覧表 表示
            $rows = $model->getViewDataList($factoryList, $result);
            $res = $result->get_array();
            break;
        case 'edit':            // マスター修正
            if ($pmac_no == '') $pmac_no = $mac_no;     // 前の番号が設定されていない場合は初回と判定してmac_noを代入する
        case 'confirm_delete':  // 削除の確認
            if ($cancel_edit == '') {   // 確認フォームの取消の時は前のデータをそのまま使う
                $rows = $model->getViewDataEdit($mac_no, $result);
                $mac_name   = $result->get_once('mac_name');
                $maker_name = $result->get_once('maker_name');
                $maker      = $result->get_once('maker');
                $factory    = $result->get_once('factory');
                $survey     = $result->get_once('survey');
                $csv_flg    = $result->get_once('csv_flg');
                $sagyouku   = $result->get_once('sagyouku');
                $denryoku   = $result->get_once('denryoku');
                $keisuu     = $result->get_once('keisuu');
            }
            break;
        }
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($current_menu) {
        case 'list':            // 一覧表 表示
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage');
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage', 'pageRec');
            require_once ('equip_macMasterMnt_ViewList.php');
            break;
        case 'apend':           // マスター追加
            require_once ('equip_macMasterMnt_ViewApend.php');
            break;
        case 'edit':            // マスター修正
            require_once ('equip_macMasterMnt_ViewEdit.php');
            break;
        case 'confirm_apend':   // 登録の確認
        case 'confirm_edit':    // 変更の確認
        case 'confirm_delete':  // 削除の確認
            require_once ('equip_macMasterMnt_ViewConfirm.php');
            break;
        default:                // リクエストデータにエラー
            require_once ('equip_macMasterMnt_ViewList.php');
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
