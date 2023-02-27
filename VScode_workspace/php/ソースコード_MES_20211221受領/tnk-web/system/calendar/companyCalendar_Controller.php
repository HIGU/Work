<?php
//////////////////////////////////////////////////////////////////////////////
// 会社の基本カレンダー メンテナンス                     MVC Controller 部  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/20 Created   companyCalendar_Controller.php                      //
// 2006/07/07 showMenu に TimeCopy 追加 display()メソッド                   //
// 2006/07/11 ControllerにExecute()メソッドを追加しActionとshowMenuの明確化 //
// 2006/11/29 カレンダーの初期メニューを$targetCalendar = 'SetTime' へ変更  //
// 2007/02/06 初期値の当年表示を当期表示へ変更 InitTargetDateY()の修正      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// require_once ('../../tnk_func.php');        // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class CompanyCalendar_Controller
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
    
    private $calendar = array();                // カレンダーオブジェクトの配列
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
        
        //////////// カレンダーのインスタンスを登録
        for ($i=0; $i<12; $i++) {
            $this->calendar[$i] = new CalendarTNK();
        }
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new CompanyCalendar_Model($this->request, $this->menu);
        
        //////////// ブラウザーのキャッシュ対策用
        $this->uniq = $this->menu->set_useNotCache('companyCalendar');
    }
    
    ///// MVC の Model部 実行ロジックの処理
    public function Execute()
    {
        switch ($this->request->get('Action')) {
        case 'Change':                                      // 会社の休日・営業日トグル切替
            $this->model->changeHoliday($this->request, $this->result, $this->menu);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'Calendar');
            break;
        case 'CommentSave':                                 // コメントの保存 日付キー
            $this->model->commentSave($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'EditComment');
            break;
        case 'Comment':                                     // コメントの照会・編集用データ取得
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'EditComment');
            break;
        case 'TimeList':                                    // 詳細編集用リスト(１ヶ月)取得
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'List');
            break;
        case 'bdDetailSave':                                // 詳細編集より営業日／休日の切替
            $this->model->changeHoliday($this->request, $this->result, $this->menu);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'bdCommentSave':                               // 詳細編集より営業日／休日のコメント変編集
            $this->model->commentSave($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'TimeSave':                                    // 時間編集データの保存 日付キー
            $this->model->timeSave($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'TimeCopy':                                    // 対象日に直近のデータをコピーして表示
            $this->model->getTimeDetail($this->request, $this->result, 2);  // 2=コピー(過去の直近のデータを自分の日付にコピー)
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'Format':                                      // 対象期の１年間を初期化する
            $this->model->deleteCalendar($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'Calendar');
            break;
        ////////// (現在は使用していない)submit版
        case 'CommentEdit':                                 // コメント編集準備
            $this->model->commentEdit($this->request, $this->result, $this->menu);
                // 条件選択フォームを踏み台にして表示
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'CondForm');
            break;
        default:
            // showMenuの処理
            $this->InitShowMenu($this->request);
        }
    }
    
    ///// MVC View部の処理
    public function display()
    {
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            $this->CondFormExecute($this->menu, $this->request, $this->model, $this->calendar, $this->result, $this->uniq);
            break;
        case 'Calendar':                                    // Ajax用 カレンダー表示
            $this->ViewCalendarExecute($this->menu, $this->request, $this->model, $this->calendar, $this->uniq);
            break;
        case 'EditComment':                                 // 別ウィンドウでコメントの照会・編集
            $this->model->getComment($this->request, $this->result);
            $this->ViewCommentExecute($this->menu, $this->request, $this->result, $this->uniq);
            break;
        case 'List':                                        // Ajax用 詳細編集用リスト(１ヶ月)表示
                // $this->model->getViewListTable($this->request, $this->result);
                // 上記の結果は $rows = $this->result->get('rows'), $res = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu, $this->uniq);
            $this->ViewListExecute($this->uniq);
            break;
        case 'TimeEdit':                                    // 別ウィンドウで対象日の詳細編集を行う
            $this->model->getTimeDetail($this->request, $this->result, 1);  // 1=自分のデータのみ取得
            $this->ViewTimeEditExecute($this->menu, $this->request, $this->result, $this->model, $this->uniq);
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
        // targetDateYの処理
        $this->InitTargetDateY($this->request, $this->session);
        // targetDateStrの処理
        $this->InitTargetDateStr($this->request);
        // targetDateEndの処理
        $this->InitTargetDateEnd($this->request);
        // targetCalendarの処理
        $this->InitTargetCalendar($this->request, $this->session);
        // targetFormatの処理
        $this->InitTargetFormat($this->request);
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
    
    ///// 基準年の取得・初期化
    // targetDateYの処理
    private function InitTargetDateY($request, $session)
    {
        $targetDateY = $request->get('targetDateY');
        if ($targetDateY == '') {
            if ($session->get_local('targetDateY') == '') {
                $targetDateY = date('Y');       // 指定がない場合は当年
                if (date('m') < 4) {            // 期の調整(4〜3月が当期)
                    $targetDateY--;
                }
            } else {
                $targetDateY = $session->get_local('targetDateY');
            }
        }
        $session->add_local('targetDateY', $targetDateY);
        $request->add('targetDateY', $targetDateY);
    }
    
    ///// 開始年月日の取得・初期化
    // targetDateStrの処理
    private function InitTargetDateStr($request)
    {
        if ($request->get('targetDateY') == '') return;
        // if ($request->get('targetDateStr') != '') return;    // サーバー側で計算をするためリクエストは無視
        $request->add('targetDateStr', $request->get('targetDateY') . '04');
    }
    
    ///// 終了年月日の取得・初期化
    // targetDateEndの処理
    private function InitTargetDateEnd($request)
    {
        if ($request->get('targetDateY') == '') return;
        // if ($request->get('targetDateEnd') != '') return;    // サーバー側で計算をするためリクエストは無視
        $request->add('targetDateEnd', ($request->get('targetDateY') + 1) . '03');
    }
    
    ///// カレンダーのステータス取得・初期化
    // targetCalendarの処理
    private function InitTargetCalendar($request, $session)
    {
        $targetCalendar = $request->get('targetCalendar');
        if ($targetCalendar == '') {
            if ($session->get_local('targetCalendar') == '') {
                // $targetCalendar = 'BDSwitch';                       // 指定がない場合は営業日と休日の切替
                $targetCalendar = 'SetTime';                        // 指定がない場合は詳細編集モードに
            } else {
                $targetCalendar = $session->get_local('targetCalendar');
            }
        }
        $session->add_local('targetCalendar', $targetCalendar);
        $request->add('targetCalendar', $targetCalendar);
    }
    
    ///// カレンダー初期化情報の取得・実行
    // targetFormatの処理
    private function InitTargetFormat($request)
    {
        if ($request->get('targetFormat') == 'Execute') {
            $request->add('Action', 'Format');
        }
        return;
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $request, $model, $calendar, $result, $uniq)
    {
        require_once ('companyCalendar_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    private function ViewCalendarExecute($menu, $request, $model, $calendar, $uniq)
    {
        $model->showCalendar($request, $calendar, $menu, $uniq);
        require_once ('companyCalendar_ViewCalendar.php');
        return;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 休日・営業日のコメント編集フォーム
    private function ViewCommentExecute($menu, $request, $result, $uniq)
    {
        require_once ('companyCalendar_ViewEditComment.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 営業時間と休憩時間の編集用リスト表示
    private function ViewListExecute($uniq)
    {
        require_once ('companyCalendar_ViewList.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 営業時間と休憩時間の編集用 Window表示
    private function ViewTimeEditExecute($menu, $request, $result, $model, $uniq)
    {
        require_once ('companyCalendar_ViewTimeEdit.php');
        return true;
    }
    
} // class CompanyCalendar_Controller End

?>
