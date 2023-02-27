<?php
//////////////////////////////////////////////////////////////////////////////
// 組立日程計画表(AS/400版)スケジュール 照会         MVC Controller 部      //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/23 Created   assembly_schedule_show_Controller.php               //
// 2006/01/26 InitPageKeep()メソッドを追加し引当チェック後戻り時に行マーカー//
// 2006/02/03 Init()のデフォルト値を変更(完了→着手・のみ→まで・List→Chart//
//            model->getViewGanttChart()の引数に$this->menuを追加           //
// 2006/02/08 Ajaxの場合はセッションに保存しない条件で || → && に修正      //
//            $allo_parts_url = $this->menu->out_action('引当構成表'); 追加 //
// 2006/03/03 InitTargetCompleteFlag()メソッドを追加 (完成分の日程表を表示) //
// 2006/03/09 初期値の変更 営業日当日→当月末last_day(), 着手日→完了日     //
// 2006/06/16 ガントチャートのみを別ウィンドウで開く機能を追加 ZoomGantt    //
// 2006/06/22 ズームで開くにpageParameter追加                               //
// 2006/10/19 InitLineMethod()を追加 targetLineMethodで複数ライン対応       //
// 2006/11/01 表示倍率の指定 targetScale を追加                             //
// 2006/11/09 showMenuにZoomGanttAjaxを追加しzoom画面でリロードをスムースへ //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyScheduleShow_Controller
{
    ///// Private properties
    private $menu;                              // TNK 共用メニュークラスのインスタンス
    private $request;                           // HTTP Controller部のリクエスト インスタンス
    private $result;                            // HTTP Controller部のリザルト   インスタンス
    private $session;                           // HTTP Controller部のセッション インスタンス
    private $model;                             // ビジネスモデル部のインスタンス
    
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
        ///// メニュー切替用 showMenuとshowLine のデータチェック ＆ 設定 (Modelで使用する)
        ///// targetDate の取得と初期化
        $this->Init();
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new AssemblyScheduleShow_Model($this->request);
        $this->session->add_local('viewPage', $this->model->get_viewPage());
        $this->session->add_local('pageRec' , $this->model->get_pageRec());
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('processShow');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        $rowsLine = $this->model->getViewLineList($this->result);
        $resLine  = $this->result->get_array();
        switch ($this->request->get('showMenu')) {
        case 'PlanList':                                    // 組立日程計画表 表示
            $rows = $this->model->getViewPlanList($this->request, $this->result);
            $res  = $this->result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_schedule_show_ViewPlanList.php');
            break;
        case 'ListTable':                                   // 上記のAjax用 表示
            $rows = $this->model->getViewPlanList($this->request, $this->result);
            $res  = $this->result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            $allo_parts_url = $this->menu->out_action('引当構成表');
            require_once ('assembly_schedule_show_ViewListTable.php');
            break;
        case 'GanttChart':                                  // 計画のガントチャート 表示
                // $rows = $this->model->getViewGanttChart($this->request, $this->result, $this->menu);
                // $res  = $this->result->get_array();
            // 頁データ取得のため上記の代わりに以下をダミーで使用する(Listだけなので高速)
            // $rows = $this->model->getViewPlanList($this->request, $this->result);
            // $res  = $this->result->get_array();
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
                // require_once ('assembly_schedule_show_ViewGanttChart.php');
            require_once ('assembly_schedule_show_ViewGanttChartAjax.php'); // 現在はAjax対応版
            break;
        case 'GanttTable':                                  // 上記のAjax用 表示
            $rows = $this->model->getViewGanttChart($this->request, $this->result, $this->menu);
            $res  = $this->result->get_array();
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_schedule_show_ViewGanttTable.php');
            break;
        case 'ZoomGantt':                                  // ガントチャートのみを別ウィンドウにインラインフレームで 表示
            $rows = $this->model->getViewZoomGantt($this->request, $this->result, $this->menu);
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_schedule_show_ViewZoomGantt.php');
            // 上記は内部で _ViewZoomGanttHeader.php と _ViewZoomGanttBody.php をインラインで呼出す。
            break;
        case 'ZoomGanttAjax':                              // 上記のAjaxリロード版
            $rows = $this->model->getViewZoomGantt($this->request, $this->result, $this->menu);
            // $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    protected function Init()
    {
        ///// メニュー切替用 showMenuとshowLine のデータチェック ＆ 設定
        // showMenuの処理
        $this->InitShowMenu();
        // showLineの処理
        $this->InitShowLine();
        // targetLineMethodの処理
        $this->InitLineMethod();
        // targetDateの処理
        $this->InitTargetDate();
        // targetDateSpanの処理
        $this->InitTargetDateSpan();
        // targetDateItemの処理
        $this->InitTargetDateItem();
        // targetCompleteFlagの処理
        $this->InitTargetCompleteFlag();
        // targetSeiKubunの処理
        $this->InitTargetSeiKubun();
        // targetDeptの処理
        $this->InitTargetDept();
        // targetScaleの処理
        $this->InitTargetScale();
        // PageKeepの処理
        $this->InitPageKeep();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// メニュー切替用 showMenuとshowLine のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            if ($this->session->get_local('showMenu') == '') {
                $showMenu = 'GanttChart';         // 指定がない場合はガントチャート PlanList=日程計画一覧
            } else {
                $showMenu = $this->session->get_local('showMenu');
            }
        }
        // Ajaxの場合はセッションに保存しない
        if ($showMenu != 'ListTable' && $showMenu != 'GanttTable' && $showMenu != 'ZoomGantt' && $showMenu != 'ZoomGanttAjax') {
            $this->session->add_local('showMenu', $showMenu);
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    // showLineの処理
    private function InitShowLine()
    {
        $showLine = $this->request->get('showLine');
        if ($showLine == '') {
            if ($this->session->get_local('showLine') == '') {
                $showLine = '';                // 指定がない場合は全てのラインを指定したものと見なす
            } else {
                $showLine = $this->session->get_local('showLine');
            }
        }
        if ($showLine == '0') $showLine = ''; // 0 は全体を意味する。
        $this->session->add_local('showLine', $showLine);
        $this->request->add('showLine', $showLine);
    }
    
    // targetLineMethodの処理
    private function InitLineMethod()
    {
        $LineMethod = $this->request->get('targetLineMethod');
        if ($LineMethod == '') {
            if ($this->session->get_local('targetLineMethod') == '') {
                $LineMethod = '1';              // 指定がない場合は1=個別指定とする。2=複数指定
            } else {
                $LineMethod = $this->session->get_local('targetLineMethod');
            }
        }
        if ($LineMethod == '1') {
            $this->session->add_local('arrayLine', array());        // 初期化
        } else {
            // 複数ラインarrayLineの処理
            $arrayLine = $this->session->get_local('arrayLine');
            if ( ($key=array_search($this->request->get('showLine'), $arrayLine)) === false) {
                $arrayLine[] = $this->request->get('showLine');
            } else {
                // unset ($arrayLine[$key]);   // ２回同じラインが指定された場合はトグル方式で削除したいが自動リロードを使用しているため出来ない
            }
            $this->session->add_local('arrayLine', $arrayLine);     // 保存
            $this->request->add('arrayLine', $arrayLine);
        }
        $this->session->add_local('targetLineMethod', $LineMethod);
        $this->request->add('targetLineMethod', $LineMethod);
    }
    
    ///// 指定年月日の取得・初期化
    // targetDateの処理
    private function InitTargetDate()
    {
        $targetDate = $this->request->get('targetDate');
        if ($targetDate == '') {
            if ($this->session->get_local('targetDate') == '') {
                // $targetDate = workingDayOffset('+0');   // 指定がない場合は営業日の当日
                $targetDate = date('Ym') . last_day();      // 指定がない場合は当月末
            } else {
                $targetDate = $this->session->get_local('targetDate');
            }
        }
        $this->session->add_local('targetDate', $targetDate);
        $this->request->add('targetDate', $targetDate);
    }
    
    ///// 指定年月日の範囲 取得・初期化
    // targetDateSpanの処理
    private function InitTargetDateSpan()
    {
        $targetDateSpan = $this->request->get('targetDateSpan');
        if ($targetDateSpan == '') {
            if ($this->session->get_local('targetDateSpan') == '') {
                $targetDateSpan = '1';   // 指定がない場合は指定日まで (指定日のみ=0)
            } else {
                $targetDateSpan = $this->session->get_local('targetDateSpan');
            }
        }
        $this->session->add_local('targetDateSpan', $targetDateSpan);
        $this->request->add('targetDateSpan', $targetDateSpan);
    }
    
    ///// 指定年月日が完了日か着手日か集荷日かの取得・初期化
    // targetDateItemの処理
    private function InitTargetDateItem()
    {
        $targetDateItem = $this->request->get('targetDateItem');
        if ($targetDateItem == '') {
            if ($this->session->get_local('targetDateItem') == '') {
                $targetDateItem = 'kanryou';   // 指定がない場合は着手日 (kanryou, chaku, syuka)
            } else {
                $targetDateItem = $this->session->get_local('targetDateItem');
            }
        }
        $this->session->add_local('targetDateItem', $targetDateItem);
        $this->request->add('targetDateItem', $targetDateItem);
    }
    
    ///// 完成分の日程か未完成分の日程かの取得・初期化
    // targetCompleteFlagの処理
    private function InitTargetCompleteFlag()
    {
        $targetCompleteFlag = $this->request->get('targetCompleteFlag');
        if ($targetCompleteFlag == '') {
            if ($this->session->get_local('targetCompleteFlag') == '') {
                $targetCompleteFlag = 'no';   // 指定がない場合は未完成分 (yes=complete, no=incomplete)
            } else {
                $targetCompleteFlag = $this->session->get_local('targetCompleteFlag');
            }
        }
        $this->session->add_local('targetCompleteFlag', $targetCompleteFlag);
        $this->request->add('targetCompleteFlag', $targetCompleteFlag);
    }
    
    ///// 指定 製品 区分の取得・初期化
    // targetSeiKubunの処理
    private function InitTargetSeiKubun()
    {
        $targetSeiKubun = $this->request->get('targetSeiKubun');
        if ($targetSeiKubun == '') {
            if ($this->session->get_local('targetSeiKubun') == '') {
                $targetSeiKubun = '0';   // 指定がない場合は0 (0=全て, 1=製品, 2=Lホヨウ, 3=C特注, 4=Lピストン)
            } else {
                $targetSeiKubun = $this->session->get_local('targetSeiKubun');
            }
        }
        $this->session->add_local('targetSeiKubun', $targetSeiKubun);
        $this->request->add('targetSeiKubun', $targetSeiKubun);
    }
    
    ///// 指定 製品 事業部の取得・初期化
    // targetDeptの処理
    private function InitTargetDept()
    {
        $targetDept = $this->request->get('targetDept');
        if ($targetDept == '') {
            if ($this->session->get_local('targetDept') == '') {
                $targetDept = '0';   // 指定がない場合は0 (0=全て, C=カプラ, L=リニア)
            } else {
                $targetDept = $this->session->get_local('targetDept');
            }
        }
        $this->session->add_local('targetDept', $targetDept);
        $this->request->add('targetDept', $targetDept);
    }
    
    ///// ズームガントチャートの倍率指定
    // targetScaleの処理
    private function InitTargetScale()
    {
        $targetScale = $this->request->get('targetScale');
        if ($targetScale == '') {
            if ($this->session->get_local('targetScale') == '') {
                $targetScale = '1.0';   // 指定がない場合は1.0倍表示
            } else {
                $targetScale = $this->session->get_local('targetScale');
            }
        }
        if ($targetScale < 0.3) $targetScale = '0.3';
        if ($targetScale > 1.7) $targetScale = '1.7';
        $this->session->add_local('targetScale', $targetScale);
        $this->request->add('targetScale', $targetScale);
    }
    
    ///// 計画番号で引当構成部品表を照会した場合の戻り値をチェック
    // page_keepを取得してmaterial_plan_no 及びページ制御の処理
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            // クリックした計画番号の行にマーカー用
            if ($this->session->get('material_plan_no') != '') {
                $this->request->add('material_plan_no', $this->session->get('material_plan_no'));
            }
            // ページ制御用 (呼出した時のページに戻す)
            if ($this->session->get_local('viewPage') != '') {
                $this->request->add('CTM_viewPage', $this->session->get_local('viewPage'));
            }
            if ($this->session->get_local('pageRec') != '') {
                $this->request->add('CTM_pageRec', $this->session->get_local('pageRec'));
            }
        }
    }
    
} // class AssemblyScheduleShow_Controller End

?>
