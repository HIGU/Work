//////////////////////////////////////////////////////////////////////////////
// logout.php の JavaScriptクラス                                           //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/02 Created    logout.js                                          //
// 2005/09/07 終了時の処理にJavaScriptでクライアントの画面サイズを保存する  //
//            CookieのwinW/winH は不要になった                              //
// 2005/09/09 setCookie()/getCookie→setArrayCookie()/getArrayCookie()へ変更//
// 2006/02/21 SaveSize()メソッドの h -= 19; → h -= 20; へ変更(調整値変更)  //
// 2006/02/24 クラスメソッドの記述スタイルを擬似(名前なし)function()式に変更//
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*               logout class   base_classの拡張クラス定義                  *
/****************************************************************************
class logout extends base_class
*/
///// スーパークラスの継承
logout.prototype = new base_class();   // base_class の継承
///// Class & Constructer の定義
function logout()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    this.win_name = "win";              // サブウィンドウのwindow名
    this.winX     = 0;                  // X軸のWindow位置 初期値 window.screenLeft;は使えない
    this.winY     = 0;                  // Y軸のWindow位置 初期値 window.screenTop; は使えない
    this.offX     = 0;                  // WindowのScreenOffset値 X軸
    this.offY     = 0;                  // WindowのScreenOffset値 Y軸
    this.winW     = 0;                  // Windowの幅
    this.winH     = 0;                  // Windowの高さ
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** サブウィンドウ名を取得する *****/

    logout.prototype.getWinName = function ()
    {
        if (window.name) {
            // window.defaultStatus に保存していたのを使う。実際にはframe(iframe)をunLoadした時点で値はなくなる
            // this.win_name = window.defaultStatus;
            this.win_name = window.name;
        } else {
            this.win_name = "win";
        }
    }
    /***** ウィンドウ位置とOffset値を取得する *****/
    logout.prototype.getWinOffset = function ()
    {
        ///// 計算で使用するため数値型に変換
        this.offX = (this.getArrayCookie(this.win_name, "offX") - 0);
        this.offY = (this.getArrayCookie(this.win_name, "offY") - 0);
        this.winW = (this.getArrayCookie(this.win_name, "winW") - 0);
        this.winH = (this.getArrayCookie(this.win_name, "winH") - 0);
    }
    
    /***** Window 位置を保存する *****/
    logout.prototype.SaveLocate = function ()
    {
        if (document.all) {                         // IE4-
            // IEはframeにしていると10000の値を返すため、このロジックで保存する
            this.winX = window.screenLeft;          // 現在位置を保存
            this.winY = window.screenTop;
                // var msg = '';
                // for (var i in window) {
                //     msg += i + "=>" + window[i] + "\n";
                // }
                // alert(msg);
                // alert("ブレイク\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
            // IEの場合は必ずoffset値が必要
            this.winX -= this.offX;
            this.winY -= this.offY;
                // alert("ブレイク\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
        } else if (document.getElementById) {               // NN6-
            this.winX = window.screenX;            // 現在位置を保存
            this.winY = window.screenY;
            // NNの場合は今の所Offset値は必要ないが将来のため
            this.winX -= this.offX;
            this.winY -= this.offY;
        }
        ///// X,Y軸のエラーチェック・補正
        var w = screen.availWidth;        // クライアントの画面幅
        var h = screen.availHeight;       // クライアントの画面高さ
            // 下の様に計算で使用する場合は前もって数値型に変換する事
            /***** menu_frame.js(menu_window.js)でも復元時に以下と同じチェックをしている(Double check) *****/
        var maxX = w - this.winW - (this.offX * 2);
        var maxY = h - this.winH - this.offY;
            // alert("maxX=" + maxX + " w=" + w + " winW=" + this.winW + " offX=" + this.offX);
            // alert("maxY=" + maxY + " h=" + h + " winH=" + this.winH + " offY=" + this.offY);
        if (this.winX > maxX) this.winX = maxX;
        if (this.winY > maxY) this.winY = maxY;
        if (this.winX < 0) this.winX = 0;
        if (this.winY < 0) this.winY = 0;
        ///// X,Y軸の位置 保存
        this.setArrayCookie(this.win_name, "winX", this.winX);    // X軸のWindow位置を保存
        this.setArrayCookie(this.win_name, "winY", this.winY);    // Y軸のWindow位置を保存
    }
    
    /***** Window のサイズを保存する *****/
    logout.prototype.SaveSize = function ()
    {
        if (document.all) {                         // IE4-
            // var w  = document.body.clientWidth  + (this.offX * 2);
            // var h  = document.body.clientHeight + (this.offY + this.offX);
            var w  = document.body.clientWidth;
            var h  = document.body.clientHeight;
            h -= 20;     // window.statusbar.visible(NN)ステータスバーの表示・非表示のチェックが出来ないため強制
            // 上記のhは上側のメニュー表示のoffYと下側のボーダー分のoffX(Yも同じと仮定して)を足している
        } else if (document.getElementById) {       // NN6-
            var w = window.outerWidth - 8;
            var h = window.outerHeight - 50;
        }
        ///// X,Y軸の位置 保存
        this.winW = w;  // メンバーフィールドへ保存
        this.winH = h;
        this.setArrayCookie(this.win_name, "winW", w);     // Windowの幅を保存
        this.setArrayCookie(this.win_name, "winH", h);     // Windowの高さを保存
        
    }
    
    /***** Window の終了処理 *****/
    logout.prototype.win_close = function ()
    {
        this.setArrayCookie(this.win_name, this.win_name, "0");
        if (top.menu_site) {
            //parent.close();     // フレームのため親フレームをクローズしないとウインドウが終了しない
            (window.open('','_self').opener=window).close();  

        } else {
            //window.close();     // フレームがなければ自分のウィンドウを閉じる
            (window.open('','_self').opener=window).close();  
        }
    }
    
    ///// Constructer の実行部
    /***** 初期化 *****/
    this.getWinName();
    this.getWinOffset();
    
    return this;    // Object Return
}   // class logout END


///// インスタンスの生成
var menu = new logout();

///// Windowサイズを保存(位置より先に保存する事)

menu.SaveSize();

///// Window位置を保存

menu.SaveLocate();

///// Windowを閉じる

menu.win_close();


