//////////////////////////////////////////////////////////////////////////////
// 届出・申請書 site の JavaScriptクラス                                    //
// Copyright (C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/19 Created    act_appli.js                                       //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*            regulation class base_classの拡張クラスの定義                 *
/****************************************************************************
class regulation extends base_class
*/
///// スーパークラスの継承
regulation.prototype = new base_class();   // base_class の継承
///// Constructer の定義
function regulation()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "";                               // 〃
    this.winObj     = new Array();                      // win_open()メソッド内で使用する
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** 点滅表示のHTMLドキュメント *****/
    /***** blink_flg はグローバル変数に注意 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("templ.blink_disp(\"caption\")", 500)'> *****/
    regulation.prototype.blink_disp = function (id_name)
    {
        if (this.blink_flag == 1) {
            this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    regulation.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
        // document.body.focus();   // F2/F12キーを有効化する対応
        // document.mhForm.backwardStack.focus();  // 上記はIEのみのため、こちらに変更しNN対応
        // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
        // document.form_name.element_name.select();
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    regulation.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    regulation.prototype.win_open = function (url, winName, w, h)
    {
        if (!w) w = 964;     // 初期値
        if (!h) h = 708;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        for (var i=0; i<20; i++) {
            if (!this.winObj[i]) {
                left = (left + (20 * i));
                top  = (top  + (20 * i));
                if (!winName) winName = "ReguWin" + i;
                this.winObj[i] = window.open(url, winName, 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
                break;
            } else if (this.winObj[i].closed) {
                // 既に閉じられたウィンドウを初期化する
                this.winObj[i] = "";
            }
        }
        /*****      デバッグ用
        this.winObj[i].document.title = winName;
        var msg = "";
        for (var j in this.winObj[i]) {
            msg += j + " => " + this.winObj[i][j] + "\n";
        }
        alert(msg);
        *****/
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    regulation.prototype.winActiveChk = function ()
    {
        if (document.all) {     // IEなら
            if (document.hasFocus() == false) {     // IE5.5以上で使える
                window.focus();
                return;
            }
            return;
        } else {                // NN ならとワリキッテ
            window.focus();
            return;
        }
    }
    
    /***** 指定の大きさのモーダルダイアログを表示する *****/
    /***** IE 専用なのと Windows XP SP2 ではセキュリティの警告が出る *****/
    /***** ダイアログ内でリクエストを出す場合はフレームを切って行う *****/
    regulation.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    
    return this;    // Object Return
}   // class regulation END


///// インスタンスの生成
var Regu = new regulation();

