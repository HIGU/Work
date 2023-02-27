//////////////////////////////////////////////////////////////////////////////
// 資材出庫時間の集計･分析 結果 照会           MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/30 Created    parts_pickup_analyze.js                            //
// 2006/06/21 入力チェックメソッドに数字のチェックを追加                    //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     parts_pickup_analyze class base_class の拡張クラスの定義             *
/****************************************************************************
class parts_pickup_analyze extends base_class
*/
///// スーパークラスの継承
parts_pickup_analyze.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function parts_pickup_analyze()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    // this.properties = false;                         // プロパティーの初期化
    this.blink_flag = 1;                                // blink_disp()メソッド内で使用する
    this.blink_msg  = "部品番号";                       //     〃      , checkANDexecute(), viewClear()
    this.intervalID;                                    // 点滅用のintervalID
    this.blink_id_name;                                 // 点滅対象の ID名 ID='???'
    this.parameter  = "";                               // Ajax通信時のパラメーター
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    parts_pickup_analyze.prototype.set_focus = function (obj, status)
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
    
    /***** 点滅表示メソッド *****/
    /***** blink_flg Private property 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    parts_pickup_analyze.prototype.blink_disp = function (id_name)
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
    parts_pickup_analyze.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    parts_pickup_analyze.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    parts_pickup_analyze.prototype.win_open = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, '', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    parts_pickup_analyze.prototype.winActiveChk = function ()
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
    parts_pickup_analyze.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** リストボックスで選択した日付の開始・終了日付をセットする *****/
    parts_pickup_analyze.prototype.dateCreate = function (obj)
    {
        if (!obj) return;     // parameterのチェック
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
    
    /***** ConditionForm の入力チェックメソッド *****/
    parts_pickup_analyze.prototype.checkConditionForm = function (obj)
    {
        // obj.targetPartsNo.value = obj.targetPartsNo.value.toUpperCase();
        if (!obj.targetDateStr.value) {
            alert("開始日付が入力されていません！");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (obj.targetDateStr.value.length != 8) {
            alert("開始日付の桁数は８桁です。");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateStr.value)) {
            alert("開始日付は数字で入力して下さい。");
            obj.targetDateStr.focus();
            obj.targetDateStr.select();
            return false;
        }
        if (!obj.targetDateEnd.value) {
            alert("終了日付が入力されていません！");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (obj.targetDateEnd.value.length != 8) {
            alert("終了日付の桁数は８桁です。");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        if (!this.isDigit(obj.targetDateEnd.value)) {
            alert("終了日付は数字で入力して下さい。");
            obj.targetDateEnd.focus();
            obj.targetDateEnd.select();
            return false;
        }
        // return false;   // デバッグ中
        /************
        if (!obj.targetPartsNo.value.match(/^[A-Z0-9]{7}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("部品番号が間違っています！");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ************/
        this.parameter  = "&targetDateYM=" + obj.targetDateYM.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    parts_pickup_analyze.prototype.checkANDexecute = function (obj, flg)
    {
        if (this.checkConditionForm(obj)) {
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else {
                this.AjaxLoadTable("ListWin", "showAjax");
            }
            // 点滅のメッセージを変更する
            // this.blink_msg = "部品番号";
            // this.stop_blink();
        }
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    parts_pickup_analyze.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("parts_pickup_analyze_Main.php"+parm, 700, 350);
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
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            xmlhttp.open("GET", "parts_pickup_analyze_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    parts_pickup_analyze.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        // this.blink_msg = "部品番号";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    parts_pickup_analyze.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class parts_pickup_analyze END  */


///// インスタンスの生成
var PartsPickupAnalyze = new parts_pickup_analyze();

