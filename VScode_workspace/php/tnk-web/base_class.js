//////////////////////////////////////////////////////////////////////////////
// サイト全体の共有基本 JavaScriptクラス                                    //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/25 Created  base_class.js (cookie_class, base_class)             //
// 2005/09/05 site_menu On/Off機能を JavaScriptで実装 (欠点はNN対応なし)    //
// 2005/09/07 Windownを2個以上開いた状態で片方をMenuOffした場合の対応で     //
//            menuStatusCheck()メソッドを追加 MenuHeader.phpからの呼出      //
// 各ウィンドウ個別仕様の要望でmenuStatusCheck()/menuOnOff()Ajax()をコメント//
// 2005/09/09 setArrayCookie()/getArrayCookie() メソッドを追加 値を配列格納 //
// 2005/09/10 delArrayCookie()を追加   base_classのプロパティに maxWin 追加 //
// 2005/09/11 menuStatusCookieSave(status)メソッドを追加しsiteのON/OFFを保存//
//       各ウィンドウ毎にsiteMenuの表示・非表示機能を追加 menu_fram.jsと連携//
// 2005/09/12 Windowの環境情報をリセットするEnvInfoReset()メソッドを追加    //
// 2005/09/15 setCookie(key, val, tmp) → setCookie(key, val)へ変更         //
// 2005/10/26 this.Ajax("/setMenuOnOff.php?site=???&id=1/2") をmenuOnOff()と//
//                                                 menuStatusCheck()に追加  //
// 2006/02/23 クラスメソッドの記述スタイルを擬似(名前なし)function()式に変更//
// 2006/02/25 evt_key_chk(evt)メソッドは擬似function()で定義すると          //
//                      他のスクリプトから関数の上書が出来ないため元に戻す  //
// 2006/02/27 エラーハンドラー用のメソッドも関数によるハンドリングなので戻す//
// 2006/04/04 menuStatusCheckメソッドでtop.window.nameがNULLだった場合の対応//
//            上記に追加でById("switch_name").disabled = true; を追加       //
//            menuOnOff()メソッドでも if (top.topFrame)で上記と同様な対応   //
// 2006/06/02 大文字変換用のグローバル変数_KEYUPPERFLGを追加 evt_key_chk()に//
//            a～zまでの入力チェック追加。keyInUpper()メソッド追加          //
// 2007/08/05 base_classにメソッドclipCopy(obj),clipCopyValue(data)を追加   //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*                      Cookie Class 基底クラスの定義                       *
/****************************************************************************
class cookie_class
*/
///// Class & Constructer の定義
function cookie_class()
{
    ///// Private properties
    // no properties
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    /***** クッキーデータ編集用 基本メソッド *****/
    /***** Cookie data get *****/
    cookie_class.prototype.getCookie = function (key)
    {
        var tmp1 = " " + document.cookie + ";";
        var tmp2 = "";
        var xx1  = 0;
        var xx2  = 0;
        var xx3  = 0;
        var len  = tmp1.length;
        while (xx1 < len) {
            xx2 = tmp1.indexOf(";", xx1);
            tmp2 = tmp1.substring(xx1 + 1, xx2);
            xx3 = tmp2.indexOf("=");
            if (tmp2.substring(0, xx3) == key) {
                return unescape(tmp2.substring(xx3 + 1, xx2 - xx1 - 1));
            }
            xx1 = xx2 + 1;
        }
        return "";
    }
    
    /***** Cookie data insert update *****/
    cookie_class.prototype.setCookie = function (key, val)
    {
        var tmp = key + "=" + escape(val) + "; ";
        tmp += "path=/; ";
        tmp += "expires=Fri, 31-Dec-2033 23:59:59; ";   // domain= path= 等を指定するとCookieにうまく書込めないlocaのためか？
        document.cookie = tmp;
    }
    
    /***** Cookie data drop *****/
    cookie_class.prototype.delCookie = function (key)
    {
        document.cookie = key + "=" + "; expires=1-Jan-1997 00:00:00;";
    }
    
    /***** Array Cookie insert update *****/
    cookie_class.prototype.setArrayCookie = function (parentKey, key, val)
    {
        if (!parentKey) {
            alert("親キーが指定されていないため登録出来ません！");
            return "";
        }
        var arrayKey = new Array();
        var arrayVal = new Array();
        var old_val = this.getCookie(parentKey);
        // var Reg = new RegExp(' ', 'g');         // IEでうまくいかないので正規表現オブジェクトを生成
        // old_val = old_val.replace(Reg, "");     // スペースを詰める(/ /g, "") g=全て対象
        if (old_val == "") {
            // new parentKey ADD
            arrayKey[0] = key;
            arrayVal[0] = val;
        } else {
            // parentKey Update
            var KeyVal = old_val.split(';');    // key=val単位で分割
            var data = new Array();
            var flag = 0;   // 見つかったか？のフラグ
            var i;
            for (i=0; i<KeyVal.length; i++) {
                ///// data[i] = new Array(2);
                data[i] = KeyVal[i].split('=');    // keyとvalに分割
                if (data[i][0] == key) {
                    arrayKey[i] = key;
                    arrayVal[i] = val;
                    flag = 1;
                } else {
                    arrayKey[i] = data[i][0];
                    arrayVal[i] = data[i][1];
                }
            }
            if (flag == 0) {    // 見つからなかった場合は最後に追加
                arrayKey[i] = key;
                arrayVal[i] = val;
            }
        }
        return this.writeArrayCookie(parentKey, arrayKey, arrayVal);
    }
    
    /***** Array Cookie Write Execute *****/
    cookie_class.prototype.writeArrayCookie = function (parentKey, arrayKey, arrayVal)
    {
        var tmp = "";
        for (var i=0; i<arrayKey.length; i++) {
            // if (!arrayKey[i] || !arrayVal[i]) continue;  // key && value undefinedのチェック(value=0があるためNG)
            if (!arrayKey[i]) continue;     // key undefinedのチェック(key=0は使えない)
            tmp += (arrayKey[i] + "=" + arrayVal[i] + ";");     // 区切りは ';'
        }
        // alert(parentKey + " => " + tmp + "\n\n" + "Cookieに登録しました。");
        this.setCookie(parentKey, tmp);
        return tmp;
    }
    
    /***** Array Cookie get *****/
    cookie_class.prototype.getArrayCookie = function (parentKey, key)
    {
        if (!parentKey) {
            alert("親キーが指定されていないため取得出来ません！");
            return "";
        }
        var val = this.getCookie(parentKey);
        if (val == "") {
            // alert("親キーが見つからないため取得出来ません！");
            return "";
        }
        var ichi;   // keyの見つかった位置
        var ichi2;  // key=の '=' の位置
        var owari;  // ';' の終わりの位置
        var Reg = new RegExp(key, 'i'); // 動的にパターンマッチングを行うために正規表現オブジェクトを生成
        if ( (ichi = val.search(Reg)) != -1) {
            if ( (ichi2 = val.indexOf('=', ichi)) != -1) {
                if ( (owari = val.indexOf(';', ichi2)) != -1) {
                    return val.substring(ichi2+1, owari);
                }
                // alert("終わりが見つからないため取得出来ません！");
                // return "";
            }
            // alert("＝が見つからないため取得出来ません！");
            // return "";
        }
        // alert("子キーが見つからないため取得出来ません！");
        return "";
    }
    
    /***** Array Cookie drop *****/
    cookie_class.prototype.delArrayCookie = function (parentKey, key)
    {
        if (!parentKey) {
            alert("親キーが指定されていないため削除出来ません！");
            return "";
        }
        var old_val = this.getCookie(parentKey);
        if (old_val != "") {
            var arrayKey = new Array();
            var arrayVal = new Array();
            var KeyVal = old_val.split(';');    // key=val単位で分割
            var data = new Array();
            var flag = 0;   // 見つかったか？のフラグ
            for (var i=0; i<KeyVal.length; i++) {
                ///// data[i] = new Array(2);
                data[i] = KeyVal[i].split('=');    // keyとvalに分割
                if (data[i][0] == key) {
                    arrayKey[i] = '';   // 見つかったのでブランクにする
                    arrayVal[i] = '';
                    flag = 1;
                } else {
                    arrayKey[i] = data[i][0];
                    arrayVal[i] = data[i][1];
                }
            }
            if (flag == 1) {    // 見つかった場合は書込む
                return this.writeArrayCookie(parentKey, arrayKey, arrayVal);
            }
        }
        // 削除対象なし
        return "";
    }
    
    return this;
}   // class cookie_class END



///// グローバル変数 _GDEBUG の初期値をセット
var _GDEBUG = false;
var _KEYUPPERFLG = false;   // 大文字変換処理フラグ

/****************************************************************************
/*      base class サイト全体の基本クラスの定義 (cookie_classを拡張)        *
/****************************************************************************
class base_class extends cookie_class
*/
///// Class & Constructer の定義
function base_class()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = "none";            // プロパティーの初期化
    this.maxWin = 15;                       // Windowの最大オープン数(20→15)Cookieの制限で最大は20
    // this._GDEBUG = false; // エラーハンドラ内で使用するためグローバル変数をクラスメンバーに変更はできない。
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** 共通キー割当て *****/
    /***** 1.戻るボタン用 F12=123, F2=113 どちらでも使えるように  *****/
    base_class.prototype.evt_key_chk    = evt_key_chk;  // 関数をメソッドとして登録する
    /***** デバッグモードによるエラー処理切替 *****/
    base_class.prototype.Debug          = Debug;        // 関数をメソッドとして登録する
    /***** Ajax の処理部 private methods *****/
    base_class.prototype.Ajax           = Ajax;         // 関数をメソッドとして登録する
    
    // Constructer のメソッド部
    /***** エラーハンドラ－の設定 *****/
    window.onerror = this.Debug;
    
    /***** 入力文字が数字かどうかチェック(ASCII code check) *****/
    base_class.prototype.isDigit = function (str)
    {
        var len = str.length;
        var c;
        for (i=0; i<len; i++) {
            c = str.charAt(i);
            if ((c < '0') || (c > '9')) {
                return false;
            }
        }
        return true;
    }
    
    /***** 入力文字がアルファベットかどうかチェック isDigit()の逆 *****/
    base_class.prototype.isABC = function isABC(str)
    {
        // var str = str.toUpperCase();    // 必要に応じて大文字に変換
        var len = str.length;
        var c;
        for (i=0; i<len; i++) {
            c = str.charAt(i);
            if ((c < 'A') || (c > 'Z')) {
                if (c == ' ') continue; // スペースはOK
                return false;
            }
        }
        return true;
    }
    
    /***** 入力文字が数字かどうかチェック 小数点対応 *****/
    base_class.prototype.isDigitDot = function (str)
    {
        var len = str.length;
        var c;
        var cnt_dot = 0;
        for (i=0; i<len; i++) {
            c = str.charAt(i);
            if (c == '.') {
                if (cnt_dot == 0) {     // 1個目かチェック
                    cnt_dot++;
                } else {
                    return false;       // 2個目は false
                }
            } else {
                if (('0' > c) || (c > '9')) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /***** リアルタイムクロック表示用メソッド obj=時間の書込み先 *****/
    base_class.prototype.disp_clock = function (mSec, obj)
    {
        DateTime.setTime(DateTime.getTime() + mSec);
        var yy = DateTime.getYear();
        var mm = DateTime.getMonth() + 1;
        var dd = DateTime.getDate();
        var hh = DateTime.getHours();
        var ii = DateTime.getMinutes();
        var ss = DateTime.getSeconds();
        if (yy < 2000) { yy += 1900; }
        if (mm < 10) { mm = '0' + mm; }
        if (dd < 10) { dd = '0' + dd; }
        if (hh < 10) { hh = '0' + hh; }
        if (ii < 10) { ii = '0' + ii; }
        if (ss < 10) { ss = '0' + ss; }
        obj.value = yy + '/' + mm + '/' + dd + ' ' + hh + ':' + ii + ':' + ss;
    }
    
    /***** site_menu On/Off  *****/
    base_class.prototype.menuOnOff = function (id, address, reload, ajax)
    {
        if (document.all) {                         // IE4-
            try {
                // 現在の設定値を取得  IE5 以上を想定 それ以外は何もしない
                if (top.topFrame) {     // ノーツ等で単独照会時の対応
                    var cols = top.topFrame.cols;
                } else {
                    document.getElementById("switch_name").disabled = true;
                    return;
                }
                if (cols == "10%,*") {
                    top.topFrame.cols = "0%,*";
                    document.getElementById(id).value = "MenuON";   //ボタン名は逆に注意
                    this.menuStatusCookieSave("off");
                    this.Ajax("/setMenuOnOff.php?site=off&id=1");   // サーバー側を合わせる
                } else {
                    top.topFrame.cols = "10%,*";
                    document.getElementById(id).value = "MenuOFF";  //ボタン名は逆に注意
                    this.menuStatusCookieSave("on");
                    this.Ajax("/setMenuOnOff.php?site=on&id=1");    // サーバー側を合わせる
                }
                ///// Client side script のため、ここにAjaxを使って状態をサーバーに書込む
                // if (ajax != "no") this.Ajax("/setMenuOnOff.php");
                ///// 各ウィンドウ個別仕様の要望でサーバー書込みをコメント(上記のAjax)
                // 以下は検査依頼・検査仕掛り等のメニューのためのリロードである但し食違いが出るのでAjax()で強制している
                if (reload == 1) {
                    // 強制リロード版
                    try {
                        cols = top.topFrame.cols;
                        // AjaxでClient側とServer側を合わせる様に前もって逆に設定しておく
                        if (cols == "0%,*") this.Ajax("/setMenuOnOff.php?site=on");
                        if (cols == "10%,*") this.Ajax("/setMenuOnOff.php?site=off");
                        // リロード
                        top.location.href = address;
                    } catch (e) {
                        this.Debug(e.message, "base_class.js->menuOnOff()->top.topFrame.cols", 341);
                    }
                }
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuOnOff()->top.topFrame.cols", 345);
            }
        } else {                                    // NN6.1-
            try {
                // 現在の設定値を取得  NN6.1 以上を想定
                top.location.href = address;
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuOnOff()->top.location.href", 352);
            }
        }
        return;
    }
    
    /***** site_menu On/Off Status Cookie Save  *****/
    base_class.prototype.menuStatusCookieSave = function (status)
    {
        // ウィンドウ名を取得
        var win_name = top.window.name;     // top.を指定する事を忘れない事
        // パラメーターが指定されていれば
        if (status == "on") {
            this.setArrayCookie(win_name, "site", '1');
            return;
        } else if(status == "off") {
            this.setArrayCookie(win_name, "site", '0');
            return;
        }
        // パラメーターが指定されていなければトグルスイッチで動作
        // Cookieの取得
        var site = this.getArrayCookie(win_name, "site");
        if (site == '0') {
            // 書込み
            this.setArrayCookie(win_name, "site", '1');
        } else {            // データがない状態は初期値の'1'(あり)と判断
            // 書込み
            this.setArrayCookie(win_name, "site", '0');
        }
        return;
    }
    
    /***** site_menu On/Off Status check  *****/
    base_class.prototype.menuStatusCheck = function (id, address, reload)
    {
        ///// ボタン名のみチェックし違っていれば修正(IE5-専用)
            // menu_frame.jsのonLoad='siteMenuView()'時に各ウィンドウ毎に初期化された物をここで補正
        if (document.all) {                     // IE4-
            // ウィンドウ名を取得
            if (top.window.name) {
                var win_name = top.window.name;     // top.を指定する事を忘れない事
            } else {
                var win_name = "default_win";
                document.getElementById("switch_name").disabled = true;
            }
            try {
                // 現在の設定値を取得  IE5 以上を想定 それ以外は何もしない
                var buttonName = document.getElementById(id).value;
                var site = this.getArrayCookie(win_name, "site");
                if ( (buttonName == "MenuON") && (site == '1') ) {
                    document.getElementById(id).value = "MenuOFF";  //ボタン名は逆に注意
                    this.Ajax("/setMenuOnOff.php?site=on&id=2");    // サーバー側を合わせる
                } else if ( (buttonName == "MenuOFF") && (site == '0') ) {
                    document.getElementById(id).value = "MenuON";   //ボタン名は逆に注意
                    this.Ajax("/setMenuOnOff.php?site=off&id=2");   // サーバー側を合わせる
                }
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuStatusCheck()->document.getElementById()", 409);
            }
        }
        ///// 各ウィンドウ個別仕様の要望で、以下の処理は何もしない
        return;
        ///// 現在の設定値を取得  IE5 NN6.1 以上を想定 それ以外は何もしない
        try {
            var buttonName = document.getElementById(id).value;
            var cols = "";
            if (buttonName == "MenuON")  cols = "0%,*";
            if (buttonName == "MenuOFF") cols = "10%,*";
            if (top.topFrame.cols != cols) {
                this.menuOnOff(id, address+"&noSwitch", reload, "no");
            }
        } catch (e) {
            try {       // NN6.1 以上を想定 それ以外は何もしない
                var buttonName = document.getElementById(id).value;
                if (buttonName == "MenuON") {
                    if (top.menu_site.innerWidth > 0) this.menuOnOff(id, address+"&noSwitch", reload, "no");
                }
                if (buttonName == "MenuOFF") {
                    if (top.menu_site.innerWidth <= 0) this.menuOnOff(id, address+"&noSwitch", reload, "no");
                }
            } catch (e) {
                this.Debug(e.message, "base_class.js->menuStatusCheck()->document.getElementById()", 433);
            }
        }
    }
    
    /***** Window の環境情報をリセットする  *****/
    base_class.prototype.EnvInfoReset = function ()
    {
        if (!confirm("Windowの位置や大きさ 及び 開いている等の情報をリセットします。\n\n宜しいですか？\n\n")) return;
        var win_name;
        for (var i=1; i<=this.maxWin; i++) {
            win_name = "win" + i;
            if (this.getCookie(win_name) == "") continue;
            this.delCookie(win_name);
        }
        alert("Windowの環境情報をリセットしました。\n\n一度全てのWindowを右上のＸで終了してから\n\nログインして下さい。");
    }
    
    /***** 大文字変換メソッド  *****/
    /***** 使用方法 <input type='text' name='???' onKeyUp='OBJ.keyInUpper(this);'> *****/
    base_class.prototype.keyInUpper = function (obj)
    {
        // http://msdn.microsoft.com/library/default.asp?url=/workshop/author/dhtml/reference/methods/findtext.asp
        if (_KEYUPPERFLG) obj.value = obj.value.toUpperCase();
        return true;
            var rangeObj = obj.createTextRange();
            rangeObj.collapse(true);
            rangeObj.text = obj.value.toUpperCase();
            // rangeObj.moveToPoint(0, 0);
            // rangeObj.select();
    }
    
    /***** WindowsのIEの場合引数で指定したオブジェクトのvalueをクリップボードへコピー  *****/
    base_class.prototype.clipCopy = function (obj)
    {
        if (document.all && navigator.userAgent.match(/windows/i) && obj.value) {
            var copy_obj = obj.createTextRange()
            copy_obj.execCommand("Copy")
            // alert(obj.value + " をクリップボードにコピーしました。");
            window.status = obj.value + " をクリップボードにコピーしました。";
        }
    }
    
    /***** WindowsのIEの場合引数で指定したvalueをクリップボードへコピー  *****/
    base_class.prototype.clipCopyValue = function (data)
    {
        if (document.all && navigator.userAgent.match(/windows/i) && data) {
            window.clipboardData.setData("text", data);
            // alert(data + " をクリップボードにコピーしました。");
            window.status = data + " をクリップボードにコピーしました。";
        }
    }
    
    return this;    // Object Return
}   // class base_class END

///// スーパークラスの継承
base_class.prototype = new cookie_class;   // cookie_class の継承

///// サイト全体の基本クラスのオブジェクト生成
var baseJS = new base_class();


/***** デバッグモードによるエラー処理切替 *****/
/***** エラーハンドラーのため関数として定義しprototypeで取込 *****/
function Debug(error, file, line)
{
    // 使用ブラウザー名の取得
    var browser = navigator.userAgent;
    // IEの場合は確率的に１行前が多いのでマイナスしている。
    var str = navigator.appName.toUpperCase();
    if (str.indexOf("EXPLORER") >= 0) line -= 1;
    // グローバル変数の_GDEBUG=trueの時はメッセージを出す。
    if (_GDEBUG) {
        var msg = "";
        msg += "Error Infomation     : " + error + "\n\n";
        msg += "Error File Name     : " + file + "\n\n";
        msg += "Error Line Number  : " + line + "\n\n";
        msg += "Use Browser Name : " + browser + "\n\n";
        alert(msg);
    } else {
        // alert("_GDEBUG が：" + _GDEBUG + "です。");
        Ajax("/error/ErrorScriptLog.php?error="+error+"&file="+file+"&line="+line+"&browser="+browser);
    }
}

/***** Ajax の処理部 private methods *****/
/***** エラーハンドラーから呼ばれるため関数として定義しprototypeで取込 *****/
function Ajax(url)
{
    if (url) {
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                // this.Debug(e.message, "base_class.js->Ajax()->new XMLHttpRequest()", 492);
                // alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        try {
            // alert("xmlhttp.open を実行します。");
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            // this.Debug(e.message, "base_class.js->Ajax()->xmlhttp.open()", 501);
            // alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
}

/***** キーボード入力イベント処理 共通キー割当て *****/
/***** 関数として定義しprototypeで取込、他のクラスから関数名を上書出来るようにする *****/
/***** 1.戻るボタン用 F12=123, F2=113 どちらでも使えるように  *****/
function evt_key_chk(evt)
{
    // グローバル変数の backward_obj が戻り先のコントロールオブジェクト
    var browser = navigator.appName;
    if (browser.charAt(0) == 'M') {         // IEの場合
        var chk_key = event.keyCode;        // IEではキーコードを調べるには event.keyCode を使う。
    } else {                                // NNの場合を想定
        var chk_key = evt.which;            // NNでは evt.which を使う。(evtはイベントによって呼び出される関数のカッコ内に入れる)
    }
    switch (chk_key) {
    case 113:   // F2
    case 123:   // F12
        backward_obj.submit();
        return true;
    case 112:   // F1   ← これを無効にするには(onHelp='return false')IEのみ
    case 114:   // F3   検索
    case 116:   // F5   更新ボタン
    case 117:   // F6   google
        if (browser.charAt(0) == 'M') {         // IEの場合
            event.keyCode = null;
        } else {                                // NNの場合を想定
            evt.which = null;
        }
        return false;
    default:
        if (chk_key >= 65 && chk_key <= 90) {   // A(a) ～ Z(z)まで、大文字小文字の区別が出来ない
            _KEYUPPERFLG = true;
        } else {
            _KEYUPPERFLG = false;
        }
    }
    return true;
}

