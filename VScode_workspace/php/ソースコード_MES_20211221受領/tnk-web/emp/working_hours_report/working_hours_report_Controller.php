<?php
//////////////////////////////////////////////////////////////////////////////
// 就業週報の集計 結果 照会                              MVC Controller 部  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/09/22 Created   working_hours_report_Controller.php                 //
// 2017/06/02 部課長説明 本格稼動                                           //
// 2017/06/29 エラー箇所等を訂正                                            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
require_once ('../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class WorkingHoursReport_Controller
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
        $this->model = new WorkingHoursReport_Model($this->request);
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('workingHoursReport');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            if ($this->request->get('CorrectFlg') == 'y') {
                $this->model->setCorrectData($this->request);
            }
            if ($this->request->get('ConfirmFlg') == 'y') {
                $this->model->setConfirmData($this->request);
            }
            if ($this->request->get('ConfirmOneFlg') == 'y') {
                $this->model->setConfirmOneData($this->request);
                $this->request->add('AutoStart', 'y');
            }
            $this->CondFormExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'List':                                        // Ajax用 リスト表示
        case 'ListWin':                                     // Ajax用 別ウィンドウでList表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $check_flg = 'n';
            $this->model->outViewListHTML($this->request, $this->menu, $check_flg);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'ListCo':                                        // Ajax用 リスト表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $check_flg = 'y';
            $this->request->add('showMenu', 'List');
            $this->model->outViewListHTML($this->request, $this->menu, $check_flg);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'Correct':                                     // Ajax用 別ウィンドウでList表示
            $this->ViewCorrectExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'CorrectList':                                     // Ajax用 別ウィンドウでList表示
            $endflg = '';
            $this->model->outViewCorrectListHTML($this->request, $this->menu, $endflg);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'CorrectEndList':                                     // Ajax用 別ウィンドウでList表示
            $endflg = 't';
            $this->model->outViewCorrectListHTML($this->request, $this->menu, $endflg);
            $endflg = '';
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'ConfirmList':                                     // Ajax用 別ウィンドウでList表示
            $this->model->outViewConfirmListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'MailList':                                     // Ajax用 別ウィンドウでList表示
            $this->request->add('showMenu', 'ConfirmList');
            $this->model->outViewMailListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
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
        $this->InitPageKeep($this->request, $this->session);
        // showMenuの処理
        $this->InitShowMenu($this->request);
        // targetDateStrの処理
        $this->InitTargetDateStr($this->request, $this->session);
        // targetDateEndの処理
        $this->InitTargetDateEnd($this->request, $this->session);
        // targetSectionの処理
        $this->InitTargetSection($this->request, $this->session);
        
        //////////// 入力内容のエラー情報取得
        $this->errorCheck($this->request);
    }
    
    ////////// エラー情報を取得してエラーの時は適切なレスポンスを返す
    protected function errorCheck($request)
    {
        if ($this->error != 0) {
            // $request->add('showMenu', 'CondForm');
            $_SESSION['s_sysmsg'] = $this->errorMsg;
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// リンク先を照会した場合の戻り値をチェック
    // page_keepを取得してrec_no 及びページ制御の処理
    private function InitPageKeep($request, $session)
    {
        if ($request->get('page_keep') != '') {
            $request->add('showMenu', 'List');  // 今回はGraphがベースだがページ制御はList時に必要
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
    // targetSectionの処理
    private function InitTargetSection($request)
    {
        $targetSection = $request->get('targetSection');
        $uid           = $request->get('uid');
        $formal        = $request->get('formal');
        $request->add('targetSection', $targetSection);
        $request->add('uid', $uid);
        $request->add('formal', $formal);
    }
    ///// 開始年月日の取得・初期化
    // targetDateStrの処理
    private function InitTargetDateStr($request, $session)
    {
        $targetDateStr = $request->get('targetDateStr');
        if ($targetDateStr == '') {
            if ($session->get_local('targetDateStr') == '') {
                $targetDateStr = ''; // workingDayOffset(-1);      // 指定がない場合は前営業日
            } else {
                $targetDateStr = $session->get_local('targetDateStr');
            }
        }
        $session->add_local('targetDateStr', $targetDateStr);
        $request->add('targetDateStr', $targetDateStr);
        return;
        if (!is_numeric($targetDateStr)) {
            $this->error++;
            $this->errorMsg = '開始日付は数字で入力して下さい。';
        }
        if (strlen($targetDateStr) != 8) {
            $this->error++;
            $this->errorMsg = '開始日付は８桁です。';
        }
        if (!is_numeric($uid)) {
            $this->error++;
            $this->errorMsg = '社員番号は数字で入力して下さい。';
        }
        if (strlen($uid) != 6) {
            $this->error++;
            $this->errorMsg = '社員番号は６桁です。';
        }
    }
    
    ///// 終了年月日の取得・初期化
    // targetDateEndの処理
    private function InitTargetDateEnd($request, $session)
    {
        $targetDateEnd = $request->get('targetDateEnd');
        if ($targetDateEnd == '') {
            if ($session->get_local('targetDateEnd') == '') {
                $targetDateEnd = ''; // workingDayOffset(-1);      // 指定がない場合は前営業日
            } else {
                $targetDateEnd = $session->get_local('targetDateEnd');
            }
        }
        $session->add_local('targetDateEnd', $targetDateEnd);
        $request->add('targetDateEnd', $targetDateEnd);
        return;
        if (!is_numeric($targetDateEnd)) {
            $this->error++;
            $this->errorMsg = '終了日付は数字で入力して下さい。';
        }
        if (strlen($targetDateEnd) != 8) {
            $this->error++;
            $this->errorMsg = '終了日付は８桁です。';
        }
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        require_once ('working_hours_report_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    private function ViewListExecute($menu, $request, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('working_hours_report_ViewListAjax.php');
            break;
        case 'CorrectList':
        case 'CorrectEndList':
            require_once ('working_hours_report_CorrectViewListAjax.php');
            break;
        case 'ConfirmList':
            require_once ('working_hours_report_ConfirmViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('working_hours_report_ViewListWin.php');
        }
        return true;
    }
    private function ViewCorrectExecute($menu, $request, $model, $uniq)
    {
        require_once ('working_hours_report_CorrectMain.php');
        return true;
    }
    
} // class WorkingHoursReport_Controller End

?>
