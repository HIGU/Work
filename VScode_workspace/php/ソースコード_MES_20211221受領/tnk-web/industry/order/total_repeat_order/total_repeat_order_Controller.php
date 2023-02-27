<?php
//////////////////////////////////////////////////////////////////////////////
// リピート部品発注の集計 結果 照会                      MVC Controller 部  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order_Controller.php                   //
// 2007/12/20 工程明細照会を追加 Action=Details                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class TotalRepeatOrder_Controller
{
    ///// Private properties
    private $menu;                              // TNK 共用メニュークラスのインスタンス
    private $request;                           // HTTP Controller部のリクエスト インスタンス
    private $result;                            // HTTP Controller部のリザルト   インスタンス
    private $session;                           // HTTP Controller部のセッション インスタンス
    private $model;                             // ビジネスモデル部のインスタンス
    private $uniq;                              // ブラウザーのキャッシュ対策用
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
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new TotalRepeatOrder_Model($this->request);
    }
    
    ///// MVC の Model部 実行ロジックの処理
    public function execute()
    {
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定
        $this->Init();
        
        if ($this->error) {
            $this->model->outListErrorMessage($this->session, $this->menu);
            return;
        }
        
        switch ($this->request->get('Action')) {
        case 'PageSet':                                     // リクエストのページ設定
            $this->model->setWhere($this->session);
            $this->model->setLimit($this->session);
            $this->model->setSQL($this->session);
            $this->model->setTotal();
            $this->model->outListViewHTML($this->session, $this->menu);
            break;
        case 'Details':                                     // 各工程の明細リスト生成(リストから2次的に呼出し)
            $this->model->setDetailsWhere($this->session);
            $this->model->setDetailsSQL($this->session);
            $this->model->setDetailsItem($this->session);
            $this->model->outDetailsViewHTML($this->session, $this->menu);
            break;
        default:
            // 何もしない。
        }
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('totalRepeatOrder');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            $this->CondFormExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'List':                                        // Ajax用 リスト表示
        case 'ListWin':                                     // 別ウィンドウでList表示
            $this->ViewListExecute($this->menu, $this->request, $this->result, $this->model, $uniq);
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
        // Actionの処理
        $this->InitAction($this->request);
        // showMenuの処理
        $this->InitShowMenu($this->request);
        
        // targetDateStrの処理
        $this->InitTargetDateStr($this->request, $this->session);
        // targetDateEndの処理
        $this->InitTargetDateEnd($this->request, $this->session);
        // targetLimitの処理
        $this->InitTargetLimit($this->request, $this->session);
        // targetVendorの処理
        $this->InitTargetVendor($this->request, $this->session);
        // targetPartsNoの処理
        $this->InitTargetPartsNo($this->request, $this->session);
        // targetProMarkの処理
        $this->InitTargetProMark($this->request, $this->session);
        
        // エラー処理
        if ($this->error) {
            $this->session->add('s_sysmsg', $this->errorMsg);
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
    
    ///// 実行処理用のデーター取得 ＆ 設定
    // Actionの処理
    private function InitAction($request)
    {
        if ($request->get('Action') == '') {
            // $request->add('Action', 'StartForm');       // 指定がない場合は権限マスターの一覧
        }
    }
    
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu($request)
    {
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'CondForm');      // 指定がない場合はCondition Form (条件設定)
        }
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
        if (!$targetDateStr) return;
        if (!checkdate(substr($targetDateStr, 4, 2), substr($targetDateStr, 6, 2), substr($targetDateStr, 0, 4))) {
            $this->error++;
            $this->errorMsg = '開始日付は無効な日付です！';
            $session->add_local('targetDateStr', '');
        }
        return;
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
        if (!$targetDateEnd) return;
        if (!checkdate(substr($targetDateEnd, 4, 2), substr($targetDateEnd, 6, 2), substr($targetDateEnd, 0, 4))) {
            $this->error++;
            $this->errorMsg = '終了日付は無効な日付です！';
            $session->add_local('targetDateEnd', '');
        }
        return;
    }
    
    ///// １頁の表示行数の取得・初期化
    // targetLimitの処理
    private function InitTargetLimit($request, $session)
    {
        $targetLimit = $request->get('targetLimit');
        if ($targetLimit == '') {
            if ($session->get_local('targetLimit') == '') {
                $targetLimit = ''; // workingDayOffset(-1);      // 指定がない場合は前営業日
            } else {
                $targetLimit = $session->get_local('targetLimit');
            }
        }
        $session->add_local('targetLimit', $targetLimit);
        $request->add('targetLimit', $targetLimit);
        if (!$targetLimit) return;
        if (!is_numeric($targetLimit)) {
            $this->error++;
            $this->errorMsg = 'ページ数は数字で入力して下さい。';
            $session->add_local('targetLimit', '');
        }
        return;
    }
    
    ///// 工程明細 照会用
    // targetVendorの処理
    private function InitTargetVendor($request, $session)
    {
        $targetVendor = $request->get('targetVendor');
        if ($targetVendor == '') {
            if ($session->get_local('targetVendor') == '') {
                $targetVendor = '';
            } else {
                $targetVendor = $session->get_local('targetVendor');
            }
        }
        $session->add_local('targetVendor', $targetVendor);
        $request->add('targetVendor', $targetVendor);
        if (!$targetVendor) return;
        if (!is_numeric($targetVendor)) {
            $this->error++;
            $this->errorMsg = '発注先コードは数字で入力して下さい。';
            $session->add_local('targetVendor', '');
        }
        return;
    }
    
    // targetPartsNoの処理
    private function InitTargetPartsNo($request, $session)
    {
        $targetPartsNo = $request->get('targetPartsNo');
        if ($targetPartsNo == '') {
            if ($session->get_local('targetPartsNo') == '') {
                $targetPartsNo = '';
            } else {
                $targetPartsNo = $session->get_local('targetPartsNo');
            }
        }
        $session->add_local('targetPartsNo', $targetPartsNo);
        $request->add('targetPartsNo', $targetPartsNo);
        if (!$targetPartsNo) return;
        if (strlen($targetPartsNo) != 9) {
            $this->error++;
            $this->errorMsg = '部品番号は９桁必要です。';
            $session->add_local('targetPartsNo', '');
        }
        return;
    }
    
    // targetProMarkの処理
    private function InitTargetProMark($request, $session)
    {
        $targetProMark = $request->get('targetProMark');
        if ($targetProMark == '') {
            if ($session->get_local('targetProMark') == '') {
                $targetProMark = '';
            } else {
                $targetProMark = $session->get_local('targetProMark');
            }
        }
        $session->add_local('targetProMark', $targetProMark);
        $request->add('targetProMark', $targetProMark);
        if (!$targetProMark) return;
        if (!ctype_alpha($targetProMark)) {
            $this->error++;
            $this->errorMsg = '工程記号は英字で入力して下さい。';
            $session->add_local('targetProMark', '');
        }
        return;
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        require_once ('total_repeat_order_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    private function ViewListExecute($menu, $request, $result, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('total_repeat_order_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('total_repeat_order_ViewListWin.php');
        }
        return true;
    }
    
} // class TotalRepeatOrder_Controller End

?>
