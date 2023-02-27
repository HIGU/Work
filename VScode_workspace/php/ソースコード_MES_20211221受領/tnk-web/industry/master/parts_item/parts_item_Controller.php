<?php
//////////////////////////////////////////////////////////////////////////////
// 生産システムの部品・製品関係のアイテムマスター  MVC Controller 部        //
// Copyright (C) 2005-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_Controller.php                           //
// 2005/09/16 修正時のif ($preParts_no == '') $preParts_no = $parts_no;追加 //
// 2005/09/17 $this->model->set_page_rec(20);をコメント ComTableMntで対応   //
// 2005/09/26 display()にパラメータ(オブジェクト)追加                       //
// 2009/07/24 部品番号の途中に＃が入ったときの問題対応                 大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class PartsItem_Controller
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
        if ($current_menu == '') $current_menu = 'list'; // 指定がない場合は一覧表を表示(特に初回)
        
        ///// 表示用フィールド データ取得
        $parts_no   = $request->get('parts_no');        // mipn (部品番号)
        $parts_name = $request->get('parts_name');      // midsc(名称)
        $partsMate  = $request->get('partsMate');       // mzist(材質)
        $partsParent= $request->get('partsParent');     // mepnt(親機種)
        $partsASReg = $request->get('partsASReg');      // madat(AS登録日)
        
        ////////// 確認フォームで取消が押された時のリクエスト取得
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* 修正用 *********/
        $preParts_no = $request->get('preParts_no');
        
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
        
        //////////////// 登録・修正・削除の POST 変数を ローカル変数に登録
        $apend  = $request->get('apend');
        $edit   = $request->get('edit');
        $delete = $request->get('delete');
        
        ////////// MVC の Model 部の 実行部ロジック切替
        if ($apend != '') {         ////////// マスター追加
            $response = $model->table_add($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
            if (!$response) $current_menu = 'apend';    // 登録出来なかったので追加画面にする
        } elseif ($edit != '') {    ////////// マスター 変更
            $response = $model->table_change($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
            if (!$response) {
                $current_menu = 'edit';                 // 変更出来なかったので編集画面にする
                $cancel_edit  = '取消';                 // 変更時のデータで表示
            }
        } elseif ($delete != '') {  ////////// マスター完全削除
            $response = $model->table_delete($parts_no);
            if (!$response) $current_menu = 'edit';     // 削除出来なかったので編集画面にする
        }
        
        $this->current_menu = $current_menu;
        
        ////////// リクエストデータの一部を変更したので再登録
        $request->add('cancel_apend', $cancel_apend);
        $request->add('cancel_del',   $cancel_del);
        $request->add('cancel_edit',  $cancel_edit);
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('item');
        
        ///// ローカルへオブジェクトコピー(HTML埋め込み変数用)
        $current_menu = $this->current_menu;
        
        ///// 表示用フィールド データ取得
        $parts_no   = $request->get('parts_no');      // mipn (部品番号)
        $parts_no   = str_replace('シャープ', '#', $parts_no);
        $parts_name = $request->get('parts_name');    // midsc(名称)
        $partsMate  = $request->get('partsMate');     // mzist(材質)
        $partsParent= $request->get('partsParent');   // mepnt(親機種)
        $partsASReg = $request->get('partsASReg');    // madat(AS登録日)
        /********* 修正用 *********/
        $preParts_no = $request->get('preParts_no');  // 変更前の部品番号
        
        ///// キーフィールドのリクエスト取得
        $partsKey   = $request->get('partsKey');      // mipn(部品番号)のキーフィールド
        
        ////////// MVC の Model部の View部に渡すデータ取得
        switch ($current_menu) {
        case 'list':            // アイテム 一覧表 表示
        case 'table':           // アイテム 一覧表 のテーブル部のみ表示(Ajax用)
            if ($partsKey == '') {
                // キーフィールドが指定されていない(初回)ので入力フォームのみ
                $rows = 0; $res = array();
            } else {
                $rows = $model->getViewDataList($result);
                $res  = $result->get_array();
            }
            break;
        case 'edit':            // マスター修正
        case 'confirm_delete':  // 削除の確認
            if ($preParts_no == '') $preParts_no = $parts_no;   // 前の番号が設定されていない場合は初回と判定してparts_noを代入する
            if ($request->get('cancel_edit') == '') {     // 確認フォームの取消の時は前のデータをそのまま使う
                $rows = $model->getViewDataEdit($parts_no, $result);
                $parts_name = $result->get_once('parts_name');
                $partsMate  = $result->get_once('partsMate');
                $partsParent= $result->get_once('partsParent');
                $partsASReg = $result->get_once('partsASReg');
            }
            break;
        }
        
        ////////// HTML Header を出力してキャッシュ等を制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($current_menu) {
        case 'list':            // 一覧表 表示
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            // $model->set_page_rec(20);     // 1頁のレコード数
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            require_once ('parts_item_ViewList.php');
            break;
        case 'table':           // 出庫 一覧表 のテーブル部のみ表示(Ajax用)
            require_once ('parts_item_ViewTable.php');
            break;
        case 'apend':           // マスター追加
            require_once ('parts_item_ViewApend.php');
            break;
        case 'edit':            // マスター修正
            require_once ('parts_item_ViewEdit.php');
            break;
        case 'confirm_apend':   // 登録の確認
        case 'confirm_edit':    // 変更の確認
        case 'confirm_delete':  // 削除の確認
            require_once ('parts_item_ViewConfirm.php');
            break;
        default:                // リクエストデータにエラー
            require_once ('parts_item_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
