<?php
//////////////////////////////////////////////////////////////////////////////
// 部品 在庫 経歴 照会 (引当･発注状況照会)               MVC Controller 部  //
// Copyright (C) 2004-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created   parts_stock_history_Controller.php                  //
// 2006/08/01 正規表現を /^[A-Z0-9]{7} → /^[A-Z]{2}[0-9]{5} へ変更         //
// 2007/03/09 オリジナルはparts_stock_view.phpでparts_stock_plan_Controller //
//            .phpに合わせて完全なＭＶＣモデルでコーディングした。          //
//            変更経歴は backup/parts_stock_view.php を参照すること。       //
// 2007/07/27 $menu->set_retGET()に不要なurlencode()を指定していたのを削除  //
//            $menu->set_retGet() → $menu->set_retGET()へミススペル訂正    //
// 2007/08/02 Window版で予定と経歴の切替表示時に表示範囲がズレるため対応で  //
//            $session->get('stock_date_low')を取得するようにModel部を変更  //
// 2009/04/02 部品番号の最後が#の時に番号間違いになるのを訂正          大谷 //
// 2017/06/28 引当部品構成表からの呼び出しに対応                       大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class PartsStockHistory_Controller
{
    ///// Private properties
    private $menu;                              // TNK 共用メニュークラスのインスタンス
    private $request;                           // HTTP Controller部のリクエスト インスタンス
    private $result;                            // HTTP Controller部のリザルト   インスタンス
    private $session;                           // HTTP Controller部のセッション インスタンス
    private $model;                             // ビジネスモデル部のインスタンス
    private $error = 0;                         // エラーコード又はフラグ
    private $errorMsg = '';                     // エラーメッセージ
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($menu)
    {
        ///// MenuHeader クラスのインスタンスを properties に登録
        if (is_object($menu)) {
            $this->menu = $menu;
        } else {
            exit();
        }
        //////////// リクエストのインスタンスを登録
        $this->request = new Request();
        
        //////////// リザルトのインスタンスを登録
        $this->result = new Result();
        
        //////////// セッションのインスタンスを登録
        $this->session = new Session();
        
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定 (Modelで使用する)
        $this->Init();
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new PartsStockHistory_Model($this->request, $this->result, $this->session);
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('partsStockHist');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            $this->CondFormExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'List':                                        // Ajax用 リスト表示
        case 'ListWin':                                     // Ajax用 別ウィンドウでList表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu, $this->result);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'CommentSave':                                 // コメントの保存 ライン番号と年月日がキー
            $this->model->commentSave($this->request);
        case 'Comment':                                     // 別ウィンドウでコメントの照会・編集
            $this->model->getComment($this->request, $this->result);
            require_once ('parts_stock_history_ViewEditComment.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    protected function Init()
    {
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定
        // PageKeepの処理
        $this->InitPageKeep($this->menu, $this->request, $this->session);
        // showMenuの処理
        $this->InitShowMenu($this->request);
        // targetPartsNoの処理
        $this->InitTargetPartsNo($this->request, $this->session);
        // 引当部品構成表からの呼出し対応 allo_parts_row の処理
        $this->InitAlloPartsRow($this->request, $this->session);
        // 総材料費の未登録からの呼出対応 の処理
        $this->InitSetMaterial($this->request, $this->menu, $this->result, $this->session);
        // 引当部品構成表からの呼出対応 の処理
        $this->InitSetScno($this->request, $this->menu, $this->result, $this->session);
        // フォームからのパラメーター処理
        $this->InitSetFormParameter($this->menu, $this->request, $this->session);
        // エラー処理
        if ($this->error) {
            $_SESSION['s_sysmsg'] = $this->errorMsg;
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// リンク先を照会した場合の戻り値をチェック
    // page_keepを取得してrec_no 及びページ制御の処理
    private function InitPageKeep($menu, $request, $session)
    {
        ///// 呼出元に関係なくページキープさせる 旧タイプは上記のelse内に記述 2006/06/22
        $menu->set_retGET('page_keep', 'on');
        ///// 今回はページ制御が無いためpage_keepリクエストは無視する。
        return;
        if ($request->get('page_keep') != '') {
            $request->add('showMenu', 'List');  // ページ制御はList時に必要
            // クリックした行にマーカー用
            if ($session->get_local('rec_no') != '') {
                $request->add('rec_no', $session->get_local('rec_no'));
            }
            // ページ制御用 (呼出した時のページに戻す)
            if ($session->get_local('viewPage') != '') {
                $request->add('CTM_viewPage', $session->get_local('viewPage'));
            }
            if ($session->get_local('pageRec') != '') {
                $request->add('CTM_pageRec', $session->get_local('pageRec'));
            }
        }
    }
    
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu($request)
    {
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // 指定がない場合はCondition Form (条件設定)
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// 部品番号パラメータ 取得・設定
    // targetPartsNoの処理
    private function InitTargetPartsNo($request, $session)
    {
        if ($request->get('targetPartsNo') != '') {
            // 指定がある場合は何もしない
        } elseif ($request->get('parts_no') != '') {
            // 旧の parts_stock_view.php と互換性の確保ため
            $request->add('targetPartsNo', $request->get('parts_no'));
        } else {
            // 指定がない場合はセッションから取得
            $request->add('targetPartsNo', $session->get_local('targetPartsNo'));
        }
        $session->add_local('targetPartsNo', $request->get('targetPartsNo'));
        if (strlen($request->get('targetPartsNo')) != 9) {
            $this->error = 1;
            $this->errorMsg = '部品番号は９桁です。';
            return false;
        }
        // preg_match('/^[A-Z]{2}[A-Z0-9]{5}[-#]{1}[A-Z0-9]{1}$/', $request->get('targetPartsNo'));
                // 上記は部品番号の命名規則で調べる
        // ctype_alnum(substr($request->get('targetPartsNo'), 0, 7));
        // ctype_alpha();
        // ctype_digit();
        if (!preg_match('/^[A-Z]{2}[A-Z0-9]{5}[-#]{1}[A-Z0-9#]{1}$/', $request->get('targetPartsNo'))) {
            $this->error = 1;
            $this->errorMsg = '部品番号が間違っています！';
            return false;
        }
        return true;
    }
    
    ///// 引当部品構成表の行番号 取得・設定
    // allo_parts_rowの処理
    private function InitAlloPartsRow($request, $session)
    {
        if ($request->get('row') != '') {
            // 呼出し元の引当部品構成表で使用するためシステム変数を使用する
            $session->add('allo_parts_row', $request->get('row'));
        }
    }
    
    ///// 総材料費の未登録からの呼出対応 設定
    // setMaterialの処理
    private function InitSetMaterial($request, $menu, $result, $session)
    {
        if ($request->get('material') != '') {
            // allo_conf_parts_view.phpで照会した部品番号を取得するため
            $menu->set_retGET('material', $request->get('targetPartsNo'));
            $menu->set_retGET('row', $session->get('allo_parts_row'));      // 呼出元の行番号を返す。
            $menu->set_retGETanchor('mark');    // マークへジャンプさせる #が無い事に注意
            $result->add('material', '&material=' . urlencode($request->get('targetPartsNo')));
            if ($session->get('material_plan_no') != '') {
                $result->add('plan_no', $session->get('material_plan_no'));
            } else {
                $result->add('plan_no', '　');
            }
        } else {
            $result->add('material', '');
            $result->add('plan_no', '　');
        }
    }
    
    
    ///// 引当部品構成表からの呼出対応 設定
    // setMaterialの処理
    private function InitSetScno($request, $menu, $result, $session)
    {
        if ($request->get('aden_flg') != '') {
            // allo_conf_parts_view.phpで照会した部品番号を取得するため
            $menu->set_retGET('aden_flg', $request->get('aden_flg'));
            $menu->set_retGET('row', $session->get('allo_parts_row'));      // 呼出元の行番号を返す。
            $menu->set_retGETanchor('mark');    // マークへジャンプさせる #が無い事に注意
            $result->add('aden_flg', '&aden_flg=1');
            $result->add('sc_no', '&sc_no=' . urlencode($request->get('sc_no')));
        }
    }
    
    ///// フォームからのパラメーターをセッション へ設定
    // setFormParameterの処理
    private function InitSetFormParameter($menu, $request, $session)
    {
        // フォームからの呼出ならばstock_partsをセットする
        if (preg_match('/parts_stock_form.php/', $menu->out_RetUrl()) && $request->get('targetPartsNo') != '') {
            $session->add('stock_parts', $request->get('targetPartsNo'));
        }
        if ($request->get('date_low') != '') {
            $session->add('stock_date_low', $request->get('date_low'));
        }
        if ($request->get('date_upp') != '') {
            $session->add('stock_date_upp', $request->get('date_upp'));
        }
        // 表示行数の制限値
        if ($request->get('view_rec') != '') {
            $session->add('stock_view_rec', $request->get('view_rec'));
        }
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        //////////// 在庫予定照会から呼出されていなければアクションをセット 2007/02/08 ADD
        if (preg_match('/parts_stock_plan_Main.php/', $menu->out_RetUrl()) && $request->get('noMenu') == '') {
            $menu->set_retGET('material', '1');
            $stockViewFlg = false;
            // 在庫予定から呼出されているのでリターンパラメーターをセット
            $menu->set_retGET('showMenu', 'CondForm');
            $menu->set_retGET('targetPartsNo', $request->get('targetPartsNo'));
        } else {
            $menu->set_action('在庫予定照会',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
            $stockViewFlg = true;
        }
        
        require_once ('parts_stock_history_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    private function ViewListExecute($menu, $request, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('parts_stock_history_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('parts_stock_history_ViewListWin.php');
        }
        return true;
    }
    
} // class PartsStockHistory_Controller End

?>
