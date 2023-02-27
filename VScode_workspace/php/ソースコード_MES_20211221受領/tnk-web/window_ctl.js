//////////////////////////////////////////////////////////////////////////////
// window_ctl.php の JavaScriptクラス                                       //
// Copyright (C) 2005-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/31 Created    window_ctl.js                                      //
// 2005/09/03 サブウィンドウの最大数を 10 → 20 へ変更(maxWin)              //
//            Window位置の初期値(中央)を保存する 後のOffset値の算出のため   //
// 2005/09/07 終了時の処理にJavaScriptでクライアントの画面サイズを保存する  //
//            CookieのwinW/winH は不要になった                              //
// 2005/09/09 setCookie()/getCookie→setArrayCookie()/getArrayCookie()へ変更//
// 2006/02/24 クラスメソッドの記述スタイルを擬似(名前なし)function()式に変更//
// 2014/09/22 アドレスバー非表示を試みたが、IE7以降は不可能                 //
//            （サーバ側では不可能だが、各PCの設定では可能                  //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*              window_ctl class   base_classの拡張クラス定義               *
/****************************************************************************
class window_ctl extends base_class
*/
///// スーパークラスの継承
window_ctl.prototype = new base_class();   // base_class の継承
///// Class & Constructer の定義
function window_ctl()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    this.w  = screen.availWidth;        // クライアントの画面幅
    this.h  = screen.availHeight;       // クライアントの画面高さ
    this.ws = screen.Width;             // Netscape 6.1 , 7.1 で使えない
    this.hs = screen.Height;            //      〃
    this.win_name = "win";              // ウィンドウの基本window名
    // this.maxWin = 15;                // base_class.jsで定義 ウィンドウの最大数(cookieの制限で20まで)
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** 旧バージョンのクッキーデータを削除 *****/
    window_ctl.prototype.dropOldVerCookie = function ()
    {
        if (this.getCookie("offX") != "") {
            this.delCookie("offX");
        }
        if (this.getCookie("offY") != "") {
            this.delCookie("offY");
        }
        if (this.getCookie("winW") != "") {
            this.delCookie("winW");
        }
        if (this.getCookie("winH") != "") {
            this.delCookie("winH");
        }
        var win_name;   // ローカル宣言
        var data;
        for (var i=1; i<=this.maxWin; i++) {
            win_name = ("win" + i);
            if ( (data=this.getCookie(win_name)) == "") {
                break;
            } else if (data == '0') {
                this.delCookie(win_name);
            } else if (data == '1') {
                this.delCookie(win_name);
            }
            if ( (data=this.getCookie(win_name+"W")) != "") {
                this.delCookie(win_name+"W");
            }
            if ( (data=this.getCookie(win_name+"H")) != "") {
                this.delCookie(win_name+"H");
            }
            if ( (data=this.getCookie(win_name+"X")) != "") {
                this.delCookie(win_name+"X");
            }
            if ( (data=this.getCookie(win_name+"Y")) != "") {
                this.delCookie(win_name+"Y");
            }
        }
    }
    
    /***** メニュー用のサブウィンドウ名を取得する *****/
    window_ctl.prototype.setWinName = function ()
    {
        var name = "win";
        var open_flg = 0;
        for (var i=1; i<=this.maxWin; i++) {
            name = ("win" + i);
            open_flg = this.getArrayCookie(name, name);
            if (open_flg == "1") continue;
            break;
        }
        this.win_name = name;
        return name;
    }
    
    /***** メニュー用のサブウィンドウをオープンする *****/
    window_ctl.prototype.subWin_open = function ()
    {
        var Cw = this.getArrayCookie(this.win_name, "winW");
        var Ch = this.getArrayCookie(this.win_name, "winH");
        if (Cw != "" && Ch != "") {
            var w = Cw;
            var h = Ch;
        } else {
            ///// 初期値を設定
            if (this.w > 1024) {
                // 1024 X 768
                var w = 1024;
                //var w = 1280;
                //var h =  768;
                var h = 768;
                var left = (this.w - w) / 2;
                var top  = (this.h - h) / 2;
                w -= 12; h -= 80;   // 微調整が必要
            } else {
                // 1024 未満(800X600, 640X480 等)
                // X:-12  Y:-80 微調整
                var w = (this.w - 12);
                var h = (this.h - 80);
                var left = 0;
                var top  = 0;
            }
        }
        this.setArrayCookie(this.win_name, "winW", w);  // Windowの大きさを保存
        this.setArrayCookie(this.win_name, "winH", h);
        ///// 前回のWindow位置の取得・チェック・復元
        var xData = this.getArrayCookie(this.win_name, "winX");
        var yData = this.getArrayCookie(this.win_name, "winY");
        if (xData != '' && yData != '') {
            left = xData;
            top  = yData;
        } else {
            ///// 初回の場合は初期値で保存
            this.setArrayCookie(this.win_name, "winX", left);
            this.setArrayCookie(this.win_name, "winY", top);
        }
        window.open("menu_frame.php", this.win_name, "menubar=yes,resizable=yes,width="+w+",height="+h+",left="+left+",top="+top+",screenX="+left+",screenY="+top);
            // fullscreen=yes フルスクリーンモード IE 専用 メニューが出ないためスライドショー等で使用(WinXPでは逆にメニューが出てしまう)
            // 位置指定は screenX=20,screenY=50 ← NN用  left=20,top=50 ← left,topはIE用
        this.setArrayCookie(this.win_name, this.win_name, "1");
    }
    
    /***** 自分自身のロケーションの変更（経歴を残さない） *****/
    window_ctl.prototype.chgLocation = function (url)
    {
        location.replace(url);
        // location.replace("http://www.tnk.co.jp/authenticate.php?background=on");
    }
    
    /***** テスト用のメッセージ出力 *****/
    window_ctl.prototype.test_disp = function ()
    {
        var msg = "";
        msg += "このパソコンの画面サイズは\n\n";
        msg += " 幅＝" + this.w  + "\n\n";
        msg += " 高＝" + this.h  + "\n\n";
        msg += " 幅＝" + this.ws + "\n\n";
        msg += " 高＝" + this.hs + "\n\n";
        alert(msg);
    }
    
    ///// Constructer の実行部
    /***** 初期化 *****/
    this.dropOldVerCookie();    // 一時的に旧バージョンのデータ削除メソッドを入れる
    this.setWinName();
    this.subWin_open();
    
    return this;    // Object Return
}   // class window_ctl END


