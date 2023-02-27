//////////////////////////////////////////////////////////////////////////////
// リピート部品発注の集計 照会                 MVC View部(JavaScriptクラス) //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order.js                               //
// 2007/12/20 showWinのウィンドウサイズを 750X500 → 850X600 へ変更         //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*          total_repeat_order class base_class の拡張クラスの定義          *
/****************************************************************************
class total_repeat_order extends base_class
*/
///// スーパークラスの継承
total_repeat_order.prototype = new base_class();    // base_class の継承
///// class & Constructer の定義
function total_repeat_order()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "";                               //     〃      , checkANDexecute(), viewClear()
    this.intervalID;                                    // 点滅用のintervalID
    this.blink_id_name;                                 // 点滅対象の ID名 ID='???'
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    total_repeat_order.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
    }
    
    /***** 点滅表示メソッド *****/
    /***** blink_flg Private property 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    total_repeat_order.prototype.blink_disp = function (id_name)
    {
        this.blink_id_name = id_name;
        if (this.blink_flag == 1) {
            // 初期値をプロパティで指定したため以下をコメント
            // this.blink_msg = document.getElementById(id_name).innerHTML;
            document.getElementById(id_name).innerHTML = "&nbsp;";
            this.blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = this.blink_msg;
            this.blink_flag = 1;
        }
    }
    
    /***** 点滅ストップメソッド *****/
    total_repeat_order.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    total_repeat_order.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    total_repeat_order.prototype.win_open = function (url, w, h, winName)
    {
        if (!winName) winName = "";
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, winName, 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    total_repeat_order.prototype.winActiveChk = function ()
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
    total_repeat_order.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** DOM を使用したイベント登録メソッド *****/
    total_repeat_order.prototype.addListener = function (obj, eventType, func, cap)
    {
        if (obj.attachEvent) {                          // IE の場合
            obj.attachEvent("on" + eventType, func);
        } else if (obj.addEventListener) {              // IE 以外
            obj.addEventListener(eventType, func, cap);
        } else {
            alert("ご使用のブラウザーは、このプログラムに未対応です。");
            return false;
        }
    }
    
    /***** DOM を使用したイベント設定(複数可)メソッド *****/
    total_repeat_order.prototype.setEventListeners = function (eventType, ID)
    {
        var eSource = document.getElementById(ID);
        this.addListener(eSource, eventType, catchEventListener, false);  // catchEventListener()は単独関数
        ///// 上記の eventType = "click" は "Click" のように大文字を使用してはいけない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    total_repeat_order.prototype.AjaxLoadTable = function (Action, showMenu, location)
    {
        if (!location) location = "showAjax1";   // Default値の設定
        var parm = "?";
        parm += "Action=" + Action;
        parm += "&showMenu=" + showMenu;
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("total_repeat_order_Main.php"+parm, 700, 350);
            return;
        }
        /***
        parm += "&CTM_selectPage="      + document.ControlForm.CTM_selectPage.value;
        parm += "&CTM_prePage="         + document.ControlForm.CTM_prePage.value;
        parm += "&CTM_pageRec="         + document.ControlForm.CTM_pageRec.value;
        ***/
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                try {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
                } catch (e) {}
                if (location == "showCateList") {
                    TotalRepeatOrder.setEventListeners("change", "targetCategory");
                    TotalRepeatOrder.setEventListeners("focus", "targetCategory");
                    document.getElementById("targetCategory").focus();
                }
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                if (location != "showCateList" && location != "showIDName") {
                    try {
                    document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
                    } catch (e) {}
                }
            }
        }
        var url = "total_repeat_order_Main.php" + parm;
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** URL指定 Ajax用ロードメソッド *****/
    total_repeat_order.prototype.AjaxLoadUrl = function (url, location)
    {
        if (!location) location = "showAjax1";   // Default値の設定
        if (!url) return;   // URLが指定されていなければ終了
        try {
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            try {
                var xmlhttp = new XMLHttpRequest();
            } catch (e) {
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    total_repeat_order.prototype.viewClear = function (showAjax)
    {
        document.getElementById(showAjax).innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        // this.blink_msg = "部品番号";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    total_repeat_order.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    /***** リストボックスで選択した日付の開始・終了日付をセットする *****/
    total_repeat_order.prototype.dateCreate = function (obj)
    {
        if (!obj) return;     // parameterのチェック
        if (!obj.targetDateYM.value) {      // 2006/11/30 ADD
            obj.targetDateStr.value = "";
            obj.targetDateEnd.value = "";
            return;
        }
        obj.targetDateStr.value = obj.targetDateYM.value + "01";
        var yyyy = obj.targetDateStr.value.substr(0, 4);
        var mm   = obj.targetDateStr.value.substr(4, 2);
        if (mm == 12) {
            yyyy = (yyyy - 0 + 1);      // 文字列を数値に変換するため - 0している。
            mm = 0;
        }
        var dateEnd = new Date(yyyy, mm, 1, 0, 0, 0)    // 次月の日付オブジェクトを作成
        dateEnd.setTime(dateEnd.getTime() - 1000);      // １秒前にして前月末にする
        yyyy = dateEnd.getYear();
        mm   = dateEnd.getMonth() + 1;
        var dd = dateEnd.getDate();
        if (yyyy < 2000) { yyyy += 1900; }
        if (mm < 10) { mm = "0" + mm; }
        if (dd < 10) { dd = "0" + dd; }
        obj.targetDateEnd.value = (yyyy + "" + mm + dd);
        return;
    }
    
    /***** 入力内容の 確認 ＆ 実行 メソッド *****/
    total_repeat_order.prototype.checkExecute = function (obj)
    {
        if (!obj) return;     // parameterのチェック
        if (!obj.targetDateStr.value) {
            alert("開始日付が入力されていません。");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        // 開始日付を正規表現でマッチングチェック
        if (!obj.targetDateStr.value.match(/^[2][0](?:[012][0-9]|[3][0])(?:[0][1-9]|[1][0-2])(?:[0][1-9]|[12][0-9]|[3][01])$/)) {
            alert("開始日付が間違っています！");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        // 上記は以下でもOK (?:を削除)
        // if (!obj.targetDateStr.value.match(/^[2][0]([012][0-9]|[3][0])([0][1-9]|[1][0-2])([0][1-9]|[12][0-9]|[3][01])$/)) {
        // 正規表現で数値８桁のチェック
        // if (!obj.targetDateStr.value.match(/^\d{8}$/)) {
        
        if (!obj.targetDateEnd.value) {
            alert("終了日付が入力されていません。");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        // 終了日付を正規表現でマッチングチェック
        if (!obj.targetDateEnd.value.match(/^[2][0](?:[012][0-9]|[3][0])(?:[0][1-9]|[1][0-2])(?:[0][1-9]|[12][0-9]|[3][01])$/)) {
            alert("終了日付が間違っています！");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        // 開始日付と終了日付の逆転チェック
        if (obj.targetDateStr.value > obj.targetDateEnd.value) {
            alert("開始日付と終了日付が逆転しています。");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        // return; // テスト中はここで終了
        // Ajax用パラメーターをセット
        this.parameter  = "&targetDateStr="   + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd="   + obj.targetDateEnd.value;
        this.parameter += "&targetLimit="   + obj.targetLimit.value;
        return true;
    }
    
    ///// Constructer
    this.addListener(self, "load", setInitOnLoad, false);
    
    return this;    // Object Return
    
}   /* class total_repeat_order END  */


///// インスタンスの生成
var TotalRepeatOrder = new total_repeat_order();

/***** 初期ロード・イベント設定 *****/
function setInitOnLoad()
{
    TotalRepeatOrder.set_focus(document.ConditionForm.targetDateYM, "noSelect");
    // TotalRepeatOrder.intervalID = setInterval("TotalRepeatOrder.blink_disp(\"blink_item\")", 1300);
    TotalRepeatOrder.setEventListeners("change", "targetDateYM");
    TotalRepeatOrder.setEventListeners("submit", "ConditionForm");
    TotalRepeatOrder.setEventListeners("click", "showWin");
    TotalRepeatOrder.setEventListeners("click", "clear");
}

function catchEventListener(evtObj)
{
    // ブラウザーの判定
    if (evtObj.srcElement) {        // IE
        var id = evtObj.srcElement.id;
    } else if (evtObj.target) {     // IE 以外
        var id = evtObj.target.id;
    } else {
        alert("ご使用のブラウザーは未対応です。");
        return false;
    }
    // id により処理の振分
    switch (id) {
    case "targetDateYM":
        TotalRepeatOrder.dateCreate(document.ConditionForm);
        return false;
    case "ConditionForm":
        if (TotalRepeatOrder.checkExecute(document.ConditionForm)) {
            // Ajax通信
            TotalRepeatOrder.AjaxLoadTable("PageSet", "List", "showAjax");
        }
        return false;
    case "showWin":
        if (TotalRepeatOrder.checkExecute(document.ConditionForm)) {
            var parm = "?";
            parm += "Action=PageSet";
            parm += "&showMenu=ListWin";
            parm += TotalRepeatOrder.parameter;
            TotalRepeatOrder.win_open("total_repeat_order_Main.php"+parm, 850, 600, "AIA_ListLeadTime");
        }
        return false;
        alert("windowは現在準備中です。");
    case "clear":
        TotalRepeatOrder.viewClear("showAjax");
        return false;
    default:
        alert("ブラウザーがNNの場合、予想外のイベントが発生します。\n\nID = "+id);
        return false;
    }
}

