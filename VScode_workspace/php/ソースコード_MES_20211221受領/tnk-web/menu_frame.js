//////////////////////////////////////////////////////////////////////////////
// menu_frame.php の JavaScriptクラス                                       //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/31 Created    menu_frame.js                                      //
// 2005/09/02 SavaLocate()をNNの場合のみ保存するように変更IEはlogout.jsで   //
// 2005/09/03 IEだけ Window位置の初期値データを保存していたのを中止         //
// 2005/09/05 setWinOpen()メソッドを追加 menuOn/OffスイッチによるUnload対応 //
// 2005/09/07 終了時の処理にJavaScriptでクライアントの画面サイズを保存する  //
//            CookieのwinW/winH は不要になった                              //
// 2005/09/09 setCookie()/getCookie→setArrayCookie()/getArrayCookie()へ変更//
// 2005/09/11 siteMenuView()メソッドを追加 menu_frame.phpのonLoad=''で使用  //
//            win_close()でサブウィンドウが親ウィンドウの場合、先に終了して //
//            いるとエラーになるため try{}catch(){}を追加 e=[object Error]  //
// 2005/09/13 siteMenuView()メソッドの初期値を表示ONから非表示へ変更        //
// 2005/10/26 siteMenuView()にthis.Ajax("/setMenuOnOff.php?site=off");を追加//
// 2006/02/24 クラスメソッドの記述スタイルを擬似(名前なし)function()式に変更//
// 2021/12/10 「親ウィンドウは既に...」が文字化けの為、英語表記へ変更  和氣 //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*            menu_frame class   base_classの拡張クラス定義                 *
/****************************************************************************
class menu_frame extends base_class
*/
///// スーパークラスの継承
menu_frame.prototype = new base_class();   // base_class の継承
///// Class & Constructer の定義
function menu_frame()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    this.w  = screen.availWidth;        // クライアントの画面幅
    this.h  = screen.availHeight;       // クライアントの画面高さ
    this.win_name = "win";              // サブウィンドウのwindow名
    this.winX     = "";                 // X軸のWindow位置 初期値 window.screenLeft;は使えない
    this.winY     = "";                 // Y軸のWindow位置 初期値 window.screenTop; は使えない
    this.offX     = 0;                  // WindowのScreenOffset値 X軸
    this.offY     = 0;                  // WindowのScreenOffset値 Y軸
    this.winW     = 0;                  // Windowの幅
    this.winH     = 0;                  // Windowの高さ
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** サブウィンドウ名を取得する *****/
    menu_frame.prototype.getWinName = function ()
    {
        this.win_name = window.name;
        window.defaultStatus = "TNK Web System " + this.win_name;
        document.title = "TNK Web System " + this.win_name;
    }
    
    /***** ウィンドウ位置とOffset値を取得する *****/
    menu_frame.prototype.getWinLocate = function ()
    {
        this.winX = this.getArrayCookie(this.win_name, "winX");
        if (this.winX != "") this.winX -= 0;
        this.winY = this.getArrayCookie(this.win_name, "winY");
        if (this.winY != "") this.winY -= 0;
        ///// 計算で使用するため数値型に変換
        this.offX = (this.getArrayCookie(this.win_name, "offX") - 0);
        this.offY = (this.getArrayCookie(this.win_name, "offY") - 0);
        this.winW = (this.getArrayCookie(this.win_name, "winW") - 0);
        this.winH = (this.getArrayCookie(this.win_name, "winH") - 0);
    }
    
    /***** Window 位置を復元する *****/
    menu_frame.prototype.setWinLocate = function ()
    {
        ///// X,Y軸の最大値の算出
            // 下の様に計算で使用する場合は前もって数値型に変換する事
            /***** logout.js でも保存時に以下と同じチェックをしている(Double check) *****/
        var maxX = this.w - this.winW - (this.offX * 2);
        var maxY = this.h - this.winH - this.offY;
            // alert("maxX=" + maxX + " w=" + this.w + " winW=" + this.winW + " offX=" + this.offX);
        ///// 許容範囲内なら前回位置に戻す
        if (this.winX == '' || this.winY == '') return;     // 初回の場合は移動しない
        if (this.winX >= 0 && this.winY >= 0 && this.winX <= maxX && this.winY <= maxY) {
            window.moveTo(this.winX, this.winY);   // 前回の位置に戻す
        }
    }
    
    /***** site menu の表示・非表示を復元する(IE専用) *****/
    menu_frame.prototype.siteMenuView = function ()
    {
        if (document.all) {                         // IE4-
            try {
                // IE5 以上を想定 それ以外は何もしない
                // Cookieの設定値を取得
                var site = this.getArrayCookie(this.win_name, "site");
                if (site == '1') {
                    top.topFrame.cols = "10%,*";
                    // 上記を設定するとボタン名と合わなくなる為 base_class.jsのmenuStatusCheck()で補正
                    this.Ajax("/setMenuOnOff.php?site=on");     // サーバー側を合わせる
                } else {            // Cookieデータがない時の初期値にもなる1
                    top.topFrame.cols = "0%,*";
                    // 上記を設定するとボタン名と合わなくなる為 base_class.jsのmenuStatusCheck()で補正
                    this.Ajax("/setMenuOnOff.php?site=off");    // サーバー側を合わせる
                }
            } catch (e) {
                this.Debug(e, "menu_frame.js -> siteMenuView() -> top.topFrame.cols", 114);
            }
        }
    }
    
    /***** Window Offset値を保存する *****/
    menu_frame.prototype.SaveOffset = function ()
    {
        if (document.all) {                         // IE4-
                // IEの場合は必ずoffset値が必要
            var winX = window.screenLeft;           // Offset分加算された現在位置を取得
            var winY = window.screenTop;
            // IEはframeを切る前に保存するしか現在の所、方法はない → logout.js で対応した
            // this.setArrayCookie(this.win_name, "winX", winX);    // X軸のWindow位置を保存
            // this.setArrayCookie(this.win_name, "winY", winY);    // Y軸のWindow位置を保存
        } else if (document.getElementById) {          // NN6-
            // NNの場合は今の所Offset値は必要ないが将来のため
            var winX = window.screenX;              // 現在位置を保存
            var winY = window.screenY;
        } else {
            return;     // その他の未対応ブラウザーは何もしない
        }
            //alert("ブレイク\n\n winX=" + winX + "\n\n winY=" + winY);
        ///// 前回のWindow位置の取得・チェック
        var xData = this.getArrayCookie(this.win_name, "winX");
        var yData = this.getArrayCookie(this.win_name, "winY");
        if (xData != '' && yData != '') {
            window.moveTo(winX, winY);          // Offset量 取得のため一時的に移動(前回位置からOffset分だけ)
            this.offX = (winX - xData);
            this.offY = (winY - yData);
        } else {
            ///// 初回起動のWindowは初期値が以下の様にwindow_ctl.jsで設定されている
            var x = (this.w - this.winW) / 2;
            var y = (this.h - this.winH) / 2;
            window.moveTo(winX, winY);          // Offset量 取得のため一時的に移動(中央からOffset分だけ)
            this.offX = (winX - x);
            this.offY = (winY - y);
        }
            //alert("ブレイク\n\n this.offX=" + this.offX + "\n\n this.offY=" + this.offY);
        winX -= this.offX;
        winY -= this.offY;
        window.moveTo(winX, winY);              // 元の位置に戻す
            //alert("ブレイク\n\n winX=" + winX + "\n\n winY=" + winY);
        this.setArrayCookie(this.win_name, "offX", this.offX);          // X軸のOffset値を保存
        this.setArrayCookie(this.win_name, "offY", this.offY);          // Y軸のOffset値を保存
    }
    
    /***** Window 位置を保存する *****/
    menu_frame.prototype.SaveLocate = function ()
    {
        if (document.all) {                         // IE4-
            // IEはframe(iframe)にしていると10000の値を返すため、この時点ではは保存しない
            // logout.js で保存する
            // this.winX = window.screenLeft;          // 現在位置を保存
            // this.winY = window.screenTop;
            // 以下(top.frames)を使用してもNGである。
            // コメントのままにする。Xで終了した場合は位置を保存しない仕様にした。
            this.winX = top.frames.screenLeft;      // 現在位置を保存
            this.winY = top.frames.screenTop;
                // var msg = '';
                // for (var i in top.frames) {
                //     msg += i + " => " + top.frames[i] + "\n";
                // }
                // alert(msg);
                // alert("ブレイク\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
            // IEの場合は必ずoffset値が必要
            this.winX -= this.offX;
            this.winY -= this.offY;
                // alert("ブレイク\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
        } else if (document.getElementById) {       // NN6-
            this.winX = window.screenX;             // 現在位置を保存
            this.winY = window.screenY;
            // NNの場合は今の所Offset値は必要ないが将来のため
            this.winX -= this.offX;
            this.winY -= this.offY;
            // 現在はNNにしか対応していない
            //this.setArrayCookie(this.win_name, "winX", this.winX);    // X軸のWindow位置を保存
            //this.setArrayCookie(this.win_name, "winY", this.winY);    // Y軸のWindow位置を保存
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
    
    /***** Window の終了処理 *****/
    menu_frame.prototype.win_close = function ()
    {
        // this.SaveLocate();   // Xで終了時は位置を保存しない
        this.setArrayCookie(this.win_name, this.win_name, "0");
        try {
            parent.opener.focus();
        } catch (e) {
            //this.Debug(e + " 親ウィンドウは既に終了しています。", "menu_frame.js -> win_close() -> parent.opener.focus()", 224);
            //this.Debug(e + " The parent window has already ended.", "menu_frame.js -> win_close() -> parent.opener.focus()", 224);
        }
    }
    
    /***** Window のリロード時の処理 *****/
    menu_frame.prototype.setWinOpen = function ()
    {
        this.setArrayCookie(this.win_name, this.win_name, "1");
        window.self.focus();
    }
    
    // Constructer の実行部
    /***** 初期化 *****/
    this.getWinName();
    this.getWinLocate();
    this.SaveOffset();      // 起動時に1回だけOffset値をセットする
    this.setWinLocate();
    
    return this;    // Object Return
}   // class menu_frame END


///// インスタンスの生成
var menu = new menu_frame();

///// Windowの移動をイベントで検知
// onMove = menu.SaveLocate;
///// Windowのりサイズをイベントで検知
// onResize = menu.SaveSize;
///// 終了時の処理
// onUnload = menu.win_close;


