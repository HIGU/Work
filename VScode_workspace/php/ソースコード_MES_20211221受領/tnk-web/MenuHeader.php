<?php
//////////////////////////////////////////////////////////////////////////////
// TNK 共通メニューヘッダークラス                                           //
// Copyright (C) 2004-2011 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/16 Ver 1.00 Created   MenuHeader.php                             //
// 2004/07/20 Ver 1.01 戻り先のセッションルールロジックを追加               //
//             設定されていない場合のdefault=$_SERVER['HTTP_REFERER']は中止 //
//             呼出元で設定していない場合は ErrorPageへ飛ばす。             //
// 2004/07/22 Ver 1.02 formのname/target属性をプロパティ及びメソッドに追加  //
// 2004/07/23 Ver 1.03 set_frame()メソッドを追加(set_action()のフレーム版)  //
// 2004/07/24 Ver 1.04 set_retGET()/set_retPOST()メソッドを追加             //
// 2004/07/27 Ver 1.05 view_user()メソッドにSERVER_NAME/SERVER_ADDR/REMOTE_ //
//              ADDRを追加  戻るボタンの名前をRetIndex→backwardStackへ変更 //
// 2004/07/31 Ver 1.06 menu_siteをsubmit()に変更 out_site_java()を呼出すのは//
//              </html>の後 NN7.1- 対応 IEは問題なし 後でやめるかも？       //
//              スーパーグローバル変数のセキュリティチェック method を追加  //
//              global_chk() Protected methods でコンストラクタからの呼出   //
// 2004/08/08 Ver 1.07 上記のsubmit()版out_site_java()をout_site_javaEnd()に//
//              変更して従来のout_site_java()をオブジェクトのチェックをして //
//              menu_site名の Window があれば JavaScriptを実行するように変更//
// 2004/08/10 Ver 1.08 out_frame()を追加(out_actionを呼出す)out_actionを変更//
// 2004/09/19 Ver 1.09 リアルタイムクロック表示機能追加(Default)下位互換は  //
//                     set_notRealTime() プロパティで設定する。             //
// 2004/09/28 Ver 1.10 $_SESSION['s_sysmsg]をout_alert_java()メソッドで出力 //
// 2004/10/04 Ver 1.11 out_title_only_border()タイトルだけの出力メソッド追加//
// 2004/12/22 Ver 1.12 view_user()にscript_nameを追加 titleにnowrapを指定   //
// 2004/12/23          script_nameを center から right へ変更               //
//                   menu_OnOff()にfont-weight:normal;を追加 style tdの干渉 //
// 2005/01/13 戻るボタンに共通キーの割当てJavaScriptを追加 F12=123, F2=113  //
//   Ver 1.13 Protected methods に common_backward_key() methodを追加し     //
//            out_title_border()から呼出して使用 それに伴いformNameから     //
//            name='を削除。title_timeの<input size=19→18へ変更NN7.1対策   //
//            初回の場合の対応@で回避せずに登録する$_SESSION['s_sysmsg']='' //
// 2005/01/21 out_title_border()のデザイン変更 (bg_gra style 画像を追加)    //
//   Ver 1.14 追加画像はborder_silver.gif, border_silver_text.gif,          //
//            border_silver_button.gif  out_css()を直接記述からMENU_FORMへ  //
// 2005/01/28 set_retGET($name, $value)に $value = urlencode($value);を追加 //
//   Ver 1.15 $valueに全角文字等を使えるようにするための対応                //
// 2005/01/31 $retGETanchorプロパティを追加 set_retGETanchor($name='')で設定//
//   Ver 1.16 out_css()メソッドを out_css($file=MENU_FORM)へfile名変更可能に//
// 2005/02/10 out_title_only_border()にclass='bg_gra'の記述抜けを修正       //
//   Ver 1.17 MENU_FORMの.bg_graにheight:31px;を追加 ボタンの有無に関係なく //
// 2005/02/21 view_user()メソッドに border-width:0px;を追加                 //
//   Ver 1.18 ユーザー指定のスタイルシートで td{} に影響されないようにする  //
//   暫定     サイト間のアプリ共有の場合にRetUrlが設定されないので TOPへ飛ぶ//
// 2005/06/15 コード中のゴミを除去 if (!isset($_SESSION))の else 文を削除   //
//            out_site_javaEnd()にtop.menu_siteのチェック抜けを修正         //
//              バージョンの変更はない                                      //
// 2005/06/26 F2/F12キーを有効にするためbackwardNameプロパティを追加し      //
//   Ver 1.19 common_backward_key()メソッドにフォーカス機能を追加           //
// 2005/07/07 global_chk() ENT_QUOTES を追加 "'" シングルクォートを変換する //
//   Ver 1.20 htmlspecialchars()に上記のオプションを追加しセキュリティの強化//
//            ページ制御 機能は ComTableMntClass.php で実装                 //
// 2005/07/14 左上のサーバー名・アドレスをテスト用のサーバーの時は赤色で表示//
//   Ver 1.21 複数のサーバーで作業している時に注意を促すため                //
// 2005/08/03 HTML4.01に合わせてJavaScriptの記述を type='text/javascript'へ //
//   Ver 1.22 従来の記述は language='JavaScript' Content-Script も同時に変更//
//            menu_OnOff()メソッドにsite_viewのセッション存在チェックを追加 //
// 2005/08/20 ブラウザーのキャッシュ対策用の $uniq = uniqid('キーワード')を //
//   Ver 1.23 個々のスクリプトで処理してた→メソッドset_useNotCache()を実装 //
// 2005/08/20 PHP5 へ移行 修飾子 private public protected   __construct()   //
//   Ver 1.24    戻るのname=がリテラルだったのを $this->backwardName へ修正 //
//               戻先が設定されていない場合は戻るボタンをDisabledする       //
// 2005/08/28 JavaScript の base_class.js 取込ロジックを追加                //
//   Ver 1.25    JavaScript をオブジェクト指向で標準化する第１ステップ      //
// 2005/09/05 menu_OnOff()メソッドをbase_classのmenuOnOff()を使用するに変更 //
//   Ver 1.26    multi window処理をwindow_ctl.js/menu_frame.jsに実装したため//
// 2005/09/07 上記の Client side scriptingの場合に各ウィンドウ間で違いが出る//
//   Ver 1.27 ため常にサーバーとのStatusをチェックするmenuStatusCheck()追加 //
// 2005/09/09 メソッド out_retGET() out_retGETanchor() を追加  Propertiesで //
//   Ver 1.28 内部で使用していたが外部参照が出来ないためメソッドで追加      //
// 2005/11/01 out_html_header()メソッドを static メソッドへ変更 認証を必要と//
//   Ver 1.29 しないパラメーター __construct($auth=-1) を追加               //
// 2005/11/05 global_chk()メソッドの_GET _POST _REQUEST の２次元目の配列を  //
//   Ver 1.30 チェックし配列であればセキュリティチェックを実行するように変更//
// 2005/11/12 set_auth_chk(-1)認証を必要としない場合のロジックを改良        //
//   Ver 1.31 既に他のメニューで認証済みか Auth と User_ID でチェックする   //
// 2005/11/17 view_user()メソッドの権限表示部を実際の権限レベルを表示させる //
//   Ver 1.32 $auth = (int)$u_id → (int)$_SESSION['Auth'] へ変更           //
// 2006/04/12 view_user()メソッドのHTMLにfont-family:ＭＳ Ｐゴシック;を追加 //
//   Ver 1.33 CSSファイル等にbody {font-family}等で絶対指定された場合の対応 //
// 2006/07/06 out_alert_java() → out_alert_java($addSlashes=true)          //
//   Ver 1.34 初期結果のメッセージ等で\n等を出力したい場合の指定(false)     //
// 2006/08/01 上記を更にout_alert_java($addSlashes=true, $strip_tags=true)へ//
//   Ver 1.35 addSlashesはしないがstrip_tagsはする等の柔軟に対応するため    //
// 2007/01/22 out_title_border()→out_title_border($switchReload=0) へ変更し//
//   Ver 1.36 menu_OnOff()でのリロード切替を呼出し側で柔軟に変更できるように//
// 2007/06/21 publicメソッドout_retF2Script()を追加しiframe内でタイトル無し //
//   Ver 1.37 にフォーカスがあたっていてもF2/F12で戻れる機能を追加          //
//            2007/06/22 上記のメソッドにターゲットパラメーターを追加       //
// 2011/06/11 表題２の設定を追加$caption2                              大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
if (class_exists('MenuHeader')) {
    return;
}
require_once ('define.php');
define('MH_VERSION', '1.37');

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class MenuHeader
{
    ///// Private properties
    private $title;                     // タイトル名
    private $caption;                   // 表題名
    private $caption2;                  // 表題名2
    private $RetUrl = '';               // 戻り先URLdocument_root又はhttp://アドレス
    private $auth;                      // 要求権限レベル
    private $today;                     // カレント日時 date('Y/m/d H:i:s')
    private $site_index;                // サイトマップの Site_Index
    private $site_id;                   // サイトマップの Site_ID
    private $self;                      // 自分自身のdocument_rootアドレス+スクリプト名
    private $user_id;                   // 社員番号
    private $action;                    // 呼出先の抽象化(action)名とアドレス用の連想配列
    private $formName;                  // 戻るformのname属性に入れる名前(name=を含まない) JavaScript等で操作する場合
    private $backwardName;              // 戻るformの<input type='submit'のname属性に入れる名前(name=を含まない) JavaScript等で操作する場合
    private $target;                    // formやJavaScriptでアクセス先Windowの名前(target=を含む)フレーム対応
    private $retGET;                    // 戻り先へ渡すGETパラメーター
    private $retPOST;                   // 戻り先へ渡すPOSTパラメーター
    private $retGETanchor;              // 戻り先へ渡すGET anchor名 (linkの設定)
    private $_parent;                   // フレーム版の時の親フレームのアドレス それ以外は自分自身($self)
    private $real_time;                 // メニューの時計表示を1秒毎に更新するかのフラグ
    private $uniq = '';                 // ブラウザーのキャッシュ対策用
    private $jsBaseFlag = false;        // JavaScript の base_class set flag
    private $evtKeyFlag = false;        // JavaScript の base_class.evt_key_chk()メソッドがdocument.onkeydownに設定されているかチェック2007/06/21ADD
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5へ移行時は __construct() へ変更予定)
    public function __construct($auth=0, $RetUrl='', $title='Title is not set')
    {
        if (!isset($_SESSION)) {                    // セッションの開始チェック
            session_start();                        // Notice を避ける場合は頭に@
        }
        if ($RetUrl == '') {
            $RetName = $_SERVER['PHP_SELF'] . '_ret';       // 戻り先のセッション変数名の生成ルールによる
            if (isset($_SESSION["$RetName"])) {             // 呼出元でセットされているかチェック
                $this->set_RetUrl($_SESSION["$RetName"]);
            } else {
                // $this->set_RetUrl(ERROR . 'ErrorReturnPage.php');   // 設定されていない場合はError Pageに飛ばす
                ///// サイト間のアプリ共有の場合にRetUrlが設定されないので、取り合えずトップメニューに飛ばす
                // $this->set_RetUrl(TOP_MENU);         // 現在はボタンをDisabledする事により対応している
            }
        } else {
            $this->set_RetUrl($RetUrl);                 // 指定された Return URL 設定
        }
        $this->set_auth_chk($auth);                     // 要求権限レベルの設定及びチェック
        $this->set_title($title);                       // タイトルの設定
        $this->set_self($_SERVER['PHP_SELF']);          // 自分のアドレスの設定
        $this->action = array();                        // 連想配列の初期化
        $this->set_formName('mhForm');                  // 初期化(default=MenuHeaderのmh+Form)
        $this->set_backwardName('backwardStack');       // 初期化(default=MenuHeaderの戻るボタン名)
        $this->target = '';                             // 初期化(指定なし)
        $this->retGET  = '';                            // 初期化(パラメーターなし)
        $this->retPOST = '';                            // 初期化(パラメーターなし)
        $this->retGETanchor = '';                       // 初期化(パラメーターなし)
        $this->set_parent();                            // 親フレームのセット
        $this->global_chk();                            // スーパーグローバル変数のセキュリティチェック
        $this->real_time = true;                        // リアルタイムクロック
    }
    
    /*************************** Set & Check methods ************************/
    // 要求権限の設定とチェック
    public function set_auth_chk($auth)
    {
        $this->auth = $auth;
        if ($auth < 0) {                    // 認証を必要としない。(-1)
            // 既に他のメニューで認証済なら
            if (isset($_SESSION['Auth'])) {
                $this->auth = $_SESSION['Auth'];
            } else {
                $this->auth = 0;            // 最低権限で認証
                $_SESSION['Auth'] = $this->auth;
            }
            // 既に他のメニューでユーザー登録されているなら
            if (isset($_SESSION['User_ID'])) {
                $this->user_id = $_SESSION['User_ID'];
            } else {                    // 検査のuser_id=00000Aは intへcast時に000000ユーザーと同じになる
                $this->user_id = '000000';  // 認証を必要としない場合のuser_id
                $_SESSION['User_ID'] = $this->user_id;
            }
            return true;
        }
        if (isset($_SESSION['Auth'])) {     // 使用者の権限が設定されているか
            if ($_SESSION['Auth'] >= $this->auth) {   // 使用者の権限レベルが要求権限レベル以上あるか
                $this->user_id = $_SESSION['User_ID'];
                return true;
            }
            $_SESSION['s_sysmsg'] = '使用する権限がありません。';
            if (substr($this->RetUrl, 0, 4) == 'http') {
                header("Location: {$this->RetUrl}");
            } else {
                header('Location: ' . H_WEB_HOST . $this->RetUrl);
            }
            exit();     // 要求権限を満たしていない
        } else {
            $_SESSION['s_sysmsg'] = '認証期限が切れたか認証していません。';
            header('Location: http:' . WEB_HOST);
            exit();     // 認証エラー
        }
    }
    /******************************* Set methods ****************************/
    // Return URL の設定
    public function set_RetUrl($RetUrl)
    {
        $this->RetUrl = $RetUrl;
    }
    // タイトル名の設定
    public function set_title($title)
    {
        $this->title = $title;
    }
    // 表題名の設定
    public function set_caption($caption)
    {
        $this->caption = $caption;
    }
    // 表題名2の設定
    public function set_caption2($caption2)
    {
        $this->caption2 = $caption2;
    }
    // Site Index と Site ID の設定
    public function set_site($site_index, $site_id)
    {
        $this->site_index = $site_index;
        $this->site_id    = $site_id;
        $_SESSION['site_index'] = $this->site_index;
        $_SESSION['site_id']    = $this->site_id;
    }
    // Self url 自分自身のアドレスの設定
    public function set_self($self)
    {
        $this->self = $self;
    }
    // 呼出先のアドレスの設定と(その戻り先の設定)
    public function set_action($name, $addr)
    {
        // $name=抽象化(action)名 日本語でもOK
        // $addr=(document_rootからの)アドレス
        if ($name != '') {
            $this->action[$name] = $addr;
        } else {
            $this->action[] = $addr;
        }
        $addr_ret = $addr . '_ret';                 // 戻り先のセッション変数名の生成ルールによる
        $_SESSION["$addr_ret"] = $this->self;       // リターンアドレスをセット
    }
    // 呼出先のアドレスの設定と(その戻り先の設定) フレーム版
    public function set_frame($name, $addr)
    {
        // $name=抽象化(action)名 日本語でもOK
        // $addr=(document_rootからの)アドレス
        if ($name != '') {
            $this->action[$name] = $addr;
        } else {
            $this->action[] = $addr;
        }
        $addr_ret = $addr . '_ret';                 // 戻り先のセッション変数名の生成ルールによる
        $_SESSION["$addr_ret"] = $this->RetUrl;     // リターンアドレスは親フレームの戻り先
        $addr_parent = $addr . '_parent';           // 親子関係のセッション変数名 生成ルールによる
        $_SESSION["$addr_parent"] = $this->self;    // 子フレームのセッション変数に親フレームのアドレスを登録
    }
    // form name の設定
    public function set_formName($formName)
    {
        if ($formName != '') {
            $this->formName = $formName;
        }
    }
    // 戻るボタン <input type='submit' name の設定
    public function set_backwardName($backwardName)
    {
        if ($backwardName != '') {
            $this->backwardName = $backwardName;
        }
    }
    // Target Window Name の設定
    public function set_target($target)
    {
        if ($target != '') {
            $this->target = "target='{$target}'";
        }
    }
    // Return GET parameter の設定
    public function set_retGET($name, $value='')
    {
        if ($name != '') {
            $value = urlencode($value);
            if ($this->retGET == '') {
                $this->retGET = "?{$name}={$value}";
            } else {
                $this->retGET .= "&{$name}={$value}";
            }
        }
    }
    // Return POST parameter の設定
    public function set_retPOST($name, $value='')
    {
        if ($name != '') {
            if ($this->retPOST == '') {
                $this->retPOST = "                    <input type='hidden' name='{$name}' value='{$value}'>\n";
            } else {
                $this->retPOST .= "                    <input type='hidden' name='{$name}' value='{$value}'>\n";
            }
        }
    }
    // Return GET に link(アンカー名) の設定
    public function set_retGETanchor($name='')
    {
        if ($name != '') {
            $this->retGETanchor = "#{$name}";
        }
    }
    // Not real time clock view の設定
    public function set_notRealTime()
    {
        $this->real_time = false;
    }
    // ブラウザーのキャッシュ対策用 $uniq の設定
    public function set_useNotCache($prefix='ID')
    {
        if ($this->uniq == '') $this->uniq = uniqid($prefix);
        return $this->uniq;
    }
    /******************************* Out methods ****************************/
    // HTML Header 出力
    public static function out_html_header()
    {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');               // 日付が過去
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');  // 常に修正されている
        header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');                                     // HTTP/1.0
    }
    // タイトル名のみ出力
    public function out_title()
    {
        return $this->title;
    }
    // キャプション名のみ出力
    public function out_caption()
    {
        return $this->caption;
    }
    // キャプション名2のみ出力
    public function out_caption2()
    {
        return $this->caption2;
    }
    // サイトマップ用JavaScript出力
    public function out_site_java()
    {
        $site_java  = $this->out_jsBaseClass();
        $site_java .= "<script type='text/javascript'>\n";
        $site_java .= "<!--\n";
        // 子フレーム対応 $site_java .= "parent.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        $site_java .= "if (top.menu_site) {\n";
        $site_java .= "    top.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        $site_java .= "}\n";
        $site_java .= "// -->\n";
        $site_java .= "</script>\n";
        return $site_java;
    }
    // サイトマップ用JavaScript出力 (</body>の後に出力)
    public function out_site_javaEnd()
    {
        $site_java  = "<form name='siteForm' method='post' target='menu_site' action='" . SITE_MENU . "'>\n";
        $site_java .= "</form>\n";  // フォームにする事によって out_site_java()を呼出すのは</html>の後 NN7.1- 対応 IEは問題なし
        $site_java .= "<script type='text/javascript'>\n";
        $site_java .= "<!--\n";
        // 子フレーム対応 $site_java .= "parent.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        // submit()へ変更 $site_java .= "top.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        $site_java .= "if (top.menu_site) {\n";
        $site_java .= "    document.siteForm.submit();\n";
        $site_java .= "}\n";
        $site_java .= "// -->\n";
        $site_java .= "</script>\n";
        return $site_java;
    }
    // Menu Header用 CSS 使用ファイル宣言 出力
    public function out_css($file=MENU_FORM)
    {
        $css  = $this->out_jsBaseClass();
        $css .= "<link rel='stylesheet' href='{$file}?" . date('YmdHis') . "' type='text/css' media='screen'>\n";
        return $css;
    }
    // Menu Header用 JavaScript 使用ファイル宣言 出力
    public function out_javaFile($file)
    {
        $jf  = $this->out_jsBaseClass();
        $jf .= "<script type='text/javascript' src='{$file}?id=" . date('YmdHis') . "'></script>\n";
        return $jf;
    }
    // Menu Header用 JavaScript 使用ファイル宣言 出力
    public function out_jsBaseClass()
    {
        if (!$this->jsBaseFlag) {
            $this->jsBaseFlag = true;
            return "<script type='text/javascript' src='". JS_BASE_CLASS. "?id=" . date('YmdHis') . "'></script>\n";
        }
        return '';
    }
    // タイトルボーダーの出力
    public function out_title_border($switchReload=0)
    {
        if ($this->RetUrl != '') $disabled = ''; else $disabled = ' disabled';   // 戻先が設定されていない
        $this->today = date('Y/m/d H:i:s');     // 最新の日時を取得
        $title_border  = '';    // ダミー
        if (!$this->jsBaseFlag)
        $title_border .= "        ". $this->out_jsBaseClass();
        $title_border .= "        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
        $title_border .= "            <tr><td> <!-- ダミー(デザイン用) -->\n";
        $title_border .= "        <table width='100%' border='1' cellspacing='0' cellpadding='0' class='bg_gra'>\n";
        $title_border .= "            <tr>\n";
        $title_border .= "                <form method='post' action='{$this->RetUrl}{$this->retGET}{$this->retGETanchor}' name='{$this->formName}' {$this->target}>\n";
        $title_border .= $this->retPOST;
        $title_border .= "                    <td width='60' bgcolor='blue' align='center' valign='center' class='ret_border'>\n";
        $title_border .= "                        <input class='ret_font' type='submit' name='{$this->backwardName}' value='戻る'{$disabled}>\n";
        $title_border .= "                    </td>\n";
        $title_border .= "                </form>\n";
        $title_border .= $this->menu_OnOff($this->_parent . '?page_keep=1', $switchReload);
        $title_border .= "                <td nowrap align='center' class='title_font'>\n";
        $title_border .= "                    {$this->title}\n";
        $title_border .= "                </td>\n";
        $title_border .= "                <td align='center' width='140' class='today_font'>\n";
        if ($this->real_time == false) {
        $title_border .= "                    {$this->today}\n";
        } else {
        $title_border .= "                    <form name='clock_ctl'>\n";               // 下のサイズは正しくは19だがNN7.1対策で18へ変更
        $title_border .= "                        <input type='text' name='text_date' size='18' class='title_time' value='{$this->today}'>\n";
        $title_border .= "                    </form>\n";
        }
        $title_border .= "                </td>\n";
        $title_border .= "            </tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= "            </td></tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= $this->view_user($this->user_id);
        if ($this->real_time == true) {
            $title_border .= '        ' . $this->out_clock_java('document.clock_ctl.text_date');
        }
        if (!$this->evtKeyFlag) {
            $title_border .= '        ' . $this->common_backward_key();
            $this->evtKeyFlag = true;
        }
        return $title_border;
    }
    // タイトルボーダーだけの出力
    public function out_title_only_border()
    {
        $this->today = date('Y/m/d H:i:s');     // 最新の日時を取得
        $title_border  = '';    // ダミー
        if (!$this->jsBaseFlag)
        $title_border .= "        ". $this->out_jsBaseClass();
        $title_border .= "        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
        $title_border .= "            <tr><td> <!-- ダミー(デザイン用) -->\n";
        $title_border .= "        <table width='100%' border='1' cellspacing='0' cellpadding='0' class='bg_gra'>\n";
        $title_border .= "            <tr>\n";
        $title_border .= "                <td nowrap align='center' class='title_font'>\n";
        $title_border .= "                    {$this->title}\n";
        $title_border .= "                </td>\n";
        $title_border .= "                <td align='center' width='140' class='today_font'>\n";
        if ($this->real_time == false) {
        $title_border .= "                    {$this->today}\n";
        } else {
        $title_border .= "                    <form name='clock_ctl'>\n";               // 下のサイズは正しくは19だがNN7.1対策で18へ変更
        $title_border .= "                        <input type='text' name='text_date' size='18' class='title_time' value='{$this->today}'>\n";
        $title_border .= "                    </form>\n";
        }
        $title_border .= "                </td>\n";
        $title_border .= "            </tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= "            </td></tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= $this->view_user($this->user_id);
        if ($this->real_time == true) {
            $title_border .= '        ' . $this->out_clock_java('document.clock_ctl.text_date');
        }
        return $title_border;
    }
    // 自分自身のアドレスのみ出力
    public function out_self()
    {
        return $this->self;
    }
    // 親のアドレスのみ出力
    public function out_parent()
    {
        return $this->_parent;
    }
    // リターンアドレスのみ出力
    public function out_RetUrl()
    {
        return $this->RetUrl;
    }
    // GETデータの出力
    public function out_retGET()
    {
        return $this->retGET;
    }
    // GET anchor データの出力
    public function out_retGETanchor()
    {
        return $this->retGETanchor;
    }
    // 呼出先のアドレス出力
    public function out_action($name)
    {
        // $name=抽象化(action)名
        if (isset($this->action[$name])) {
            return $this->action[$name];
        } else {
            return (ERROR . 'ErrorActionPage.php');   // 設定されていない場合はError Pageに飛ばす
        }
    }
    // 呼出先のアドレス出力(フレーム版)
    public function out_frame($name)
    {
        // $name=抽象化(action)名
        return $this->out_action($name);
    }
    // 警告メッセージをJavaScriptで出力
    public function out_alert_java($addSlashes=true, $strip_tags=true)
    {
        if (!isset($_SESSION['s_sysmsg'])) $_SESSION['s_sysmsg'] = '';  // 初回の場合の対応@で回避せずに登録する
        if ($_SESSION['s_sysmsg'] != '') {
            if ($strip_tags) $_SESSION['s_sysmsg'] = strip_tags($_SESSION['s_sysmsg']);
            if ($addSlashes) $_SESSION['s_sysmsg'] = addslashes($_SESSION['s_sysmsg']);
            $alert_java  = "<script type='text/javascript'>\n";
            $alert_java .= "<!--\n";
            $alert_java .= "alert('{$_SESSION['s_sysmsg']}');\n";
            $alert_java .= "// -->\n";
            $alert_java .= "</script>\n";
            $_SESSION['s_sysmsg'] = '';     // 使用済みのメッセージは削除
            return $alert_java;
        } else {
            return '';
        }
    }
    // ブラウザーのキャッシュ対策用 $uniq の出力
    public function out_useNotCache($prefix='ID')
    {
        if ($this->uniq == '') $this->set_useNotCache($prefix);
        return $this->uniq;
    }
    // F2/F12キーで戻る処理 単独版 (ブラウザー上には表示は見えない)
    // 第１パラメーターはブラウザーのターゲット先, 第２パラメーターは１回のみ=Y 繰返し使用=N
    public function out_retF2Script($F2target='', $once='Y')
    {
        if ($this->evtKeyFlag && $once == 'Y') return "\n";
        if ($F2target == '') $F2target = $this->target; else $F2target = "target='{$F2target}'";
        $ret_form = '';
        $ret_form .= "\n<span style='visibility:hidden;'>\n";
        $ret_form .= "    <form method='post' action='{$this->RetUrl}{$this->retGET}{$this->retGETanchor}' name='_HIDDEN_RETURN_FORM' {$F2target}>\n";
        $ret_form .= $this->retPOST;
        $ret_form .= "    </form>\n";
        $ret_form .= "</span>\n";
        
        $ret_java  = $ret_form;
        if (!$this->jsBaseFlag) $ret_java .= $this->out_jsBaseClass();
        $ret_java .= "<script type='text/javascript'>\n";
        $ret_java .= "    var backward_obj = document._HIDDEN_RETURN_FORM;\n";
        $ret_java .= "    document.onkeydown = baseJS.evt_key_chk;\n";
        $ret_java .= "</script>\n";
        
        $this->evtKeyFlag = true;
        return $ret_java;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////////////////////////////////////////////////////////////////////////
    // 親フレームがセッション変数に登録されていればセット 無ければ自分をセット//
    ////////////////////////////////////////////////////////////////////////////
    protected function set_parent()
    {
        if (isset($_SESSION["{$this->self}_parent"])) {     // 親フレームのセッション変数が登録されていれば
            $this->_parent = $_SESSION["{$this->self}_parent"];     // 親フレームのアドレスをセット
        } else {
            $this->_parent = $this->self;                           // それ以外は自分自身をセット
        }
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // フレームメニューの On/Off(表示・非表示)関数                            //
    ////////////////////////////////////////////////////////////////////////////
    protected function menu_OnOff($script, $switchReload)
    {
        /***** サイトメニュー On / Off *****/
        if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';
        if ($_SESSION['site_view'] == 'on') {
            $site_view = 'MenuOFF';         // $frame_cols= '0%,*';
        } else {
            $site_view = 'MenuON';          // $frame_cols= '10%,*';
        }
        // out_title_border($switchReload)呼出し時にパラメーターで指示出来るように変更
        $reload = $switchReload;
        // if (preg_match('/order_schedule_Header.php/', $_SERVER['PHP_SELF'])) $reload = 1;
        // if (isset($_REQUEST['graph'])) $reload = 0; // グラフの場合は解除する
        // if (preg_match('/inspection_recourse_Header.php/', $_SERVER['PHP_SELF'])) $reload = 1;
        // 上記のものは強制的にリロード版にする
                                                             // ret_border は各メニューで使用している
        return "
                <td width='40' align='center' valign='center' class='ret_border'>
                    <input style='font-size:8.5pt; font-weight:normal; font-family:monospace;'
                        type='submit' name='site' value='{$site_view}' id='switch_name' class='menu_onoff'
                        onClick='baseJS.menuOnOff(\"switch_name\", \"/menu_frame.php?name={$script}\", $reload)'
                    >
                </td>
                <script type='text/javascript'>baseJS.menuStatusCheck(\"switch_name\", \"/menu_frame.php?name={$script}\", $reload)</script>
        ";
        // 子フレーム対応のため parent.location.href → top.location.href へ変更
        // onClick=\"top.location.href='/menu_frame_OnOff.php?name={$script}';\"
        // ↑でもOKだが↓の様にするのが見た目上スマート欠点はNNに対応していない事
        // onClick=\"top.topFrame.cols='0%,*'\" に変更 → base_class.js(menuOnOff())へ 2005/09/05
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // ヘッダーのメニューバーの下にユーザーＩＤ・ユーザー名の表示             //
    ////////////////////////////////////////////////////////////////////////////
    protected function view_user($u_id)
    {
        switch ($u_id) {
        case 0:
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
            $auth = (int)$_SESSION['Auth'];     // 整数型に変換 2005/11/17 (int)$u_id を実際の権限に変更
            $name = "Auth{$auth}";  // 文字列で連結
            break;
        default:
            $query = "SELECT trim(name) FROM user_detailes WHERE uid='{$u_id}'";
            if (getUniResult($query, $name) <= 0) {
                $name = 'check'; // 未登録又はエラーなら
            }
        }
        // 現在実行中の真実のスクリプト名(include/requireも含む)
        // $script_name = substr(basename(__FILE__), 0, -4);
        if ($_SESSION['Auth'] <= 2) {
            // ブラウザー上で表示しているスクリプト名のみ
            $script_name = basename($_SERVER['PHP_SELF'], '.php');
        } else {
            // フルアドレスで表示
            $script_name = substr($_SERVER['PHP_SELF'], 0, -4);
        }
        // テスト用のサーバーの時に赤字で表示させる（注意を促す）case文で複数指定できるようにしてある
        switch ($_SERVER['SERVER_ADDR']) {
            case '10.1.3.252':
                $color = '';
                break;
            default:
                $color = ' color:red;';
        }
        return "
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <td align='left' width='30%' nowrap style='font-size:10pt; font-weight:normal; font-family:ＭＳ Ｐゴシック; border-width:0px;{$color}'>
                {$_SERVER['SERVER_NAME']} [{$_SERVER['SERVER_ADDR']}] [{$_SERVER['REMOTE_ADDR']}]
            </td>
            <td align='right' width='40%' nowrap style='font-size:10pt; font-weight:normal; font-family:ＭＳ Ｐゴシック; border-width:0px;'>
            </td>
            <td align='right' width='30%' nowrap style='font-size:10pt; font-weight:normal; font-family:ＭＳ Ｐゴシック; border-width:0px;'>
                {$script_name}&nbsp;&nbsp;{$u_id}&nbsp;{$name}
            </td>
        </table>\n";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // GET/POST/REQUEST のスーパーグローバル変数のセキュリティチェック        //
    ////////////////////////////////////////////////////////////////////////////
    protected function global_chk()
    {
        // SQLインジェクション対策のためのaddslashes($value)は magic_quotes_gpc = On に設定しているため省略
        // 以下のstrip_tags()は無意味だが あえて残す htmlspecialchars()を取る場合があるため
        // テキストフィールドやエリア内で "<" のタグを必要な時は クラスのインスタンスを作成する前にローカル変数に保存する事
        // 2005/07/07 ENT_QUOTES を追加 "'" シングルクォートを変換するため
        // htmlspecialchars($value, ENT_QUOTES) の対象は &=&amp; "=&quot; '=&#039; <=&lt; >=&gt; の5個です。
        // htmlentities($value, ENT_QUOTES) は全てのHTML文字エンティティに変換する。
        foreach ($_GET as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $_GET[$key][$key2] = strip_tags(htmlspecialchars($value2, ENT_QUOTES));
                }
            } else {
                $_GET[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
            }
        }
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $_POST[$key][$key2] = strip_tags(htmlspecialchars($value2, ENT_QUOTES));
                }
            } else {
                $_POST[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
            }
        }
        // $_GET, $_POST, $_COOKIE, $_FILES の内容を格納した連想配列 ← $_FILESは余計だが
        foreach ($_REQUEST as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $_REQUEST[$key][$key2] = strip_tags(htmlspecialchars($value2, ENT_QUOTES));
                }
            } else {
                $_REQUEST[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
            }
        }
        // データ抽出用のCLI版スクリプト等のために上記の逆関数を以下に示す｡
        // function unhtmlentities ($string)
        // {
        //     $trans_tbl = get_html_translation_table (HTML_ENTITIES, ENT_QUOTES);
        //     $trans_tbl = array_flip ($trans_tbl);
        //     return strtr ($string, $trans_tbl);
        // }
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // リアルタイムクロック表示用JavaScript出力                               //
    ////////////////////////////////////////////////////////////////////////////
    protected function out_clock_java($controll_obj)
    {
        // $controll_obj = JavaScriptの時間の書き込み先コントロールオブジェクト
        $server_time = date('M d, Y H:i:s');    // 'Dec 31, 1999 23:59:59'の形式  Y,m,dは月を0〜11なので使わない
        $clock_java  = "<script type='text/javascript'>";
        $clock_java .= "var DateTime = new Date('{$server_time}'); ";
        $clock_java .= "setInterval('baseJS.disp_clock(1000, {$controll_obj})', 1000);";
        $clock_java .= "</script>\n";
        return $clock_java;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // 共通キー割当て用 JavaScript出力                                        //
    // 1.戻るボタン用 F12=123, F2=113  どちらでも使えるように                 //
    ////////////////////////////////////////////////////////////////////////////
    protected function common_backward_key()
    {
        $ret_java  = '';
        $ret_java .= "<script type='text/javascript'>";
        $ret_java .= "document.onkeydown = baseJS.evt_key_chk; ";
        $ret_java .= "var backward_obj = document.{$this->formName}; ";
        $ret_java .= "try {";
        $ret_java .= "    document.{$this->formName}.{$this->backwardName}.focus();";
        $ret_java .= "} catch (error) {";
        $ret_java .= "}";
        $ret_java .= "</script>\n";
        return $ret_java;
    }

} // class MenuHeader End
?>
