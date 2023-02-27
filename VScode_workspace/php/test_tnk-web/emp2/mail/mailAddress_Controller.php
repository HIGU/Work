<?php
//////////////////////////////////////////////////////////////////////////////
// 社員マスターのメールアドレス 照会・メンテナンス                          //
//                                                       MVC Controller 部  //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created   mailAddress_Controller.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class mailAddress_Controller
{
    ///// Private properties
    private $showMenu;                  // メニュー切替
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用 リクエスト データ取得
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') $showMenu = 'Mail';            // 指定がない場合は一覧表を表示(特に初回)
        $condition = $request->get('confition');            // 一覧表の条件取得
        
        ///// キーフィールド リクエスト データ取得
        $uid        = $request->get('uid');                 // 社員番号
        $mailaddr   = $request->get('mailaddr');            // E_Mail アドレス
        
        ///// 登録・修正・削除の 実行指示リクエスト
        $mailEdit   = $request->get('mailEdit');            // アドレスの登録・変更
        $mailOmit   = $request->get('mailOmit');            // アドレスの削除
        $mailActive = $request->get('mailActive');          // アドレスの有効・無効(トグル)
        
        ///// 登録・編集 データのリクエスト取得
        $user_name  = $request->get('user_name');           // 社員の氏名
        $active     = $request->get('active');              // 有効・無効
        
        ////////// MVC の Model 部の 実行部ロジック切替
        if ($mailEdit != '') {                              // アドレスの登録・変更
            if ($model->mail_edit($uid, $mailaddr)) {
                // 登録できたのでuid, mailaddrの<input>データを消す
                $request->add('uid', '');
                $request->add('name', '');
                $request->add('mailaddr', '');
            }
        } elseif ($mailOmit != '') {                        // アドレスの削除
            $response = $model->mail_omit($uid, $mailaddr);
            $request->add('uid', '');                       // 削除時はコピーは必要ない
            $request->add('name', '');
            $request->add('mailaddr', '');
        } elseif ($mailActive != '') {                      // アドレスの有効・無効(トグル)
            if ($model->mail_activeSwitch($uid, $mailaddr)) {
                $request->add('uid', '');
                $request->add('name', '');
                $request->add('mailaddr', '');
            }
        }
        
        $this->showMenu = $showMenu;
        
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('meeting');
        
        ///// メニュー切替 リクエスト データ取得
        $showMenu   = $this->showMenu;                      // __construct()で変更されたターゲットメニューを取得
        $condition = $request->get('condition');            // 一覧表の条件取得
        
        ///// キーフィールド リクエスト データ取得
        $uid        = $request->get('uid');                 // 社員番号
        $mailaddr   = $request->get('mailaddr');            // E_Mail アドレス
        
        ///// 登録・編集 データのリクエスト取得
        $mailCopy   = $request->get('mailCopy');            // アドレスの編集データコピー
        $name       = $request->get('name');                // アドレスの編集時の確認用 氏名
        
        ////////// MVC の Model部の View部に渡すデータ生成
        switch ($showMenu) {
        case 'Mail':                                        // アドレスの 登録 一覧表 表示
            $rows = $model->getViewMailList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        }
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {
        case 'Mail':                                        // アドレスの 一覧表 表示
            if ($mailCopy == 'go') {
                $focus    = 'mailaddr';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'uid';
                $readonly = '';
            }
            require_once ('mailAddress_View.php');
            break;
        default:                // リクエストデータにエラーの場合は初期値の一覧を表示
            require_once ('mailAddress_View.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
} // End off Class mailAddress_Controller

?>
