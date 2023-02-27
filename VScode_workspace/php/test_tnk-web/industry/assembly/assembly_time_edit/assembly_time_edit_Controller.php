<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理実績データ 編集  MVC Controller 部                         //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/07 Created   assembly_time_edit_Controller.php                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyTimeEdit_Controller
{
    ///// Private properties
    private $rowsDupli;                     // 同時計画の個数
    private $resDupli = array();            // 同時計画のレコード配列
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model, $result, $session)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用データ取得
        if ($request->get('showMenu') == '') $request->add('showMenu', 'List'); // 指定がない場合は一覧表を表示(特に初回)
        
        ////////// MVC の Model 部の 実行部ロジック切替
        if ($request->get('Apend') != '') {                 // 実績データの追加
            if ($model->Apend($request)) {
                $request->add('user_id', '');               // 登録できたのでuser_idの<input>データを消す
                $request->add('plan_no', '');               // 登録できたのでplan_noの<input>データを消す
                $request->add('str_time', '');              // 登録できたのでstr_timeの<input>データを消す
                $request->add('end_time', '');              // 登録できたのでend_timeの<input>データを消す
                $request->add('showMenu', 'List');          // 登録できたので一覧画面にする
            }
        } elseif ($request->get('Delete') != '') {          // 実績データの削除 (完全削除)
            if ($model->Delete($request)) {
                $request->add('showMenu', 'List');          // 削除出来たので一覧画面にする
            } else {
                $request->add('showMenu', 'ConfirmDelete'); // 削除出来なかったので削除の確認画面に戻す
            }
        } elseif ($request->get('Edit') != '') {            // 実績データの修正(編集)
            if ($model->Edit($request, $session)) {
                $request->add('user_id', '');               // 変更できたのでuser_idの<input>データを消す
                $request->add('plan_no', '');               // 変更できたのでplan_noの<input>データを消す
                $request->add('str_time', '');              // 変更できたのでstr_timeの<input>データを消す
                $request->add('end_time', '');              // 変更できたのでend_timeの<input>データを消す
                $request->add('showMenu', 'List');          // 変更できたので一覧画面にする
            }
        } elseif ($request->get('ConfirmApend') != '') {    // 追加 確認用に再計算する
            if (!$model->ConfirmApend($request, $result)) {
                // 再計算でエラーのため入力データをそのままにして追加画面に戻す
                $request->add('showMenu', 'ConfirmApendCancel');
            } else {
                $this->rowsDupli = $result->get('rows');
                $this->resDupli  = $result->get_array();
            }
        } elseif ($request->get('ConfirmDelete') != '') {   // 削除 確認用に同時計画分を取得
            if (!$model->ConfirmDelete($request, $result)) {
                // 同時計画分の取得でエラーの一覧画面に戻す
                $request->add('showMenu', 'List');
            } else {
                $this->rowsDupli = $result->get('rows');
                $this->resDupli  = $result->get_array();
            }
        } elseif ($request->get('ConfirmEdit') != '') {     // 修正 確認用に再計算する
            if (!$model->ConfirmEdit($request, $result, $session)) {
                // 再計算でエラーのため入力データをそのままにして修正画面に戻す
                $request->add('showMenu', 'ConfirmEditCancel');
            } else {
                $this->rowsDupli = $result->get('rows');
                $this->resDupli  = $result->get_array();
            }
        }
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model, $session)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('processEdit');
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        $rowsGroup = $model->getViewGroupList($result);
        $resGroup  = $result->get_array();
        switch ($request->get('showMenu')) {
        case 'List':                                        // 組立実績 一覧表 表示
            $rows = $model->getViewEndList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_time_edit_ViewList.php');
            break;
        case 'Edit':                                        // 組立実績 変更データ取得
            $rows = $model->getViewDataEdit($request->get('serial_no'), $request);
            // データは $request->get() で取得
          case 'ConfirmEditCancel':       // 取消が押された場合はリクエストをそのまま使う
            $rows = 1; // 空レコードを追加
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $model->ConfirmEditDupli($request, $result, $session);
            $rowsDupli = $result->get('rows');
            $resDupli  = $result->get_array();
            require_once ('assembly_time_edit_ViewEdit.php');
            break;
        case 'Apend':                                       // 組立実績の追加 (手入力)
            $request->add('str_year', date('Y')); $request->add('str_month', date('m')); $request->add('str_day', date('d'));
            $request->add('end_year', date('Y')); $request->add('end_month', date('m')); $request->add('end_day', date('d'));
            $request->add('assy_name', '&nbsp;'); $request->add('assy_no', '&nbsp;'); $request->add('plan', '&nbsp;'); $request->add('user_name', '&nbsp;');
          case 'ConfirmApendCancel':      // 取消が押された場合はリクエストをそのまま使う
            $rows = 1; // 空レコードを追加
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_time_edit_ViewApend.php');
            break;
        case 'ConfirmDelete':                               // 削除時の確認画面
            $rows = $model->getViewDataEdit($request->get('serial_no'), $request);
            // データは $request->get() で取得
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $rowsDupli = $this->rowsDupli;
            $resDupli  = $this->resDupli;
            require_once ('assembly_time_edit_ViewConfirmDelete.php');
            break;
        case 'ConfirmEdit':                                 // 変更時の確認画面
            $rows = 1; // 空レコードを追加
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $rowsDupli = $this->rowsDupli;
            $resDupli  = $this->resDupli;
            require_once ('assembly_time_edit_ViewConfirmEdit.php');
            break;
        case 'ConfirmApend':                                // 追加時の確認画面
            $rows = 1; // 空レコードを追加
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $rowsDupli = $this->rowsDupli;
            $resDupli  = $this->resDupli;
            require_once ('assembly_time_edit_ViewConfirmApend.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
