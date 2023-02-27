//////////////////////////////////////////////////////////////////////////////
// 組立ラインのカレンダー メンテナンス         MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/11 Created   assembly_calendar.js                                //
// 2006/12/05 win_open()メソッドの変数名のミスを修正 name → winName        //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*          assembly_calendar class base_class の拡張クラスの定義             *
/****************************************************************************
class assembly_calendar extends base_class
*/
///// スーパークラスの継承
assembly_calendar.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function assembly_calendar()
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
    this.maxYear;                                       // 入力範囲の最大値
    
    var dateObj = new Date();
    this.maxYear = (dateObj.getFullYear() + 1);         // プロパティーの値をセット
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    assembly_calendar.prototype.set_focus = function (obj, status)
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
    assembly_calendar.prototype.blink_disp = function (id_name)
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
    assembly_calendar.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    assembly_calendar.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    assembly_calendar.prototype.win_open = function (url, w, h, winName)
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
    assembly_calendar.prototype.winActiveChk = function ()
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
    assembly_calendar.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** リストボックスで選択した日付の開始・終了日付をセットする *****/
    assembly_calendar.prototype.dateCreate = function (obj, flg, offset)
    {
        if (!obj) return;     // parameterのチェック
        // ボタン名がカレンダーへになっている場合に詳細編集へ戻す
        if (document.ConditionForm.SetTime) {
            document.ConditionForm.SetTime.value = "詳細編集へ";
        }
        if (offset < 0) {
            if (obj.targetDateY.value <= 2000) return;
        } else {
            if (obj.targetDateY.value >= this.maxYear) return;
        }
        var temp = parseInt(obj.targetDateY.value);     // 数値に変換(これがポイント)
        temp += offset;
        temp += "";                                     // 文字列変換
        obj.targetDateY.value = temp;
        this.checkANDexecute(obj, flg);
        return;
    }
    
    /***** カレンダーのアクション切替メソッド *****/
    assembly_calendar.prototype.setTargetCalendar = function (obj, flg, status)
    {
        if (!obj) return;     // parameterのチェック
        switch (status) {
        case 'BDSwitch':
            obj.BDSwitch.style.color = "blue";
            obj.Comment.style.color  = "";
            obj.SetTime.style.color  = "";
            obj.SetTime.value = "詳細編集へ";
            break;
        case 'Comment':
            obj.BDSwitch.style.color = "";
            obj.Comment.style.color  = "blue";
            obj.SetTime.style.color  = "";
            obj.SetTime.value = "詳細編集へ";
            break;
        case 'SetTime':
            obj.BDSwitch.style.color = "";
            obj.Comment.style.color  = "";
            obj.SetTime.style.color  = "blue";
            obj.SetTime.value = "詳細編集へ";
            break;
        }
        obj.targetCalendar.value = status;
        this.checkANDexecute(obj, flg);
        return;
    }
    
    /***** カレンダーのアクション名 切替メソッド *****/
    assembly_calendar.prototype.actionNameSwitch = function ()
    {
        if (document.ConditionForm.SetTime) {
            document.ConditionForm.SetTime.value = "カレンダーへ";
        }
    }
    
    /***** 対象期のカレンダーを初期化します *****/
    assembly_calendar.prototype.initFormat = function (obj, flg)
    {
        if (!obj) return;     // parameterのチェック
        if (confirm("カレンダーを初期状態に戻します。\n\n宜しいですか？")) {
            // ボタン名がカレンダーへになっている場合に詳細編集へ戻す
            if (document.ConditionForm.SetTime) {
                document.ConditionForm.SetTime.value = "詳細編集へ";
            }
            obj.targetFormat.value = "Execute";
            this.checkANDexecute(obj, flg);
            obj.targetFormat.value = "";    // 重要 2006/07/07
        }
        return;
    }
    
    /***** 開始時間と終了時間で時間(分)をセットする最小値と最大値のチェックも行う *****/
    assembly_calendar.prototype.setTimeValue = function (formObj, targetObj)
    {
        if (!formObj.str_hour.options)   return;     // parameterのチェック
        if (!formObj.str_minute.options) return;     // parameterのチェック
        if (!formObj.end_hour.options)   return;     // parameterのチェック
        if (!formObj.end_minute.options) return;     // parameterのチェック
        if (!targetObj) return;     // parameterのチェック
        var str_hour   = formObj.str_hour.options[formObj.str_hour.selectedIndex].value;
        var str_minute = formObj.str_minute.options[formObj.str_minute.selectedIndex].value;
        var end_hour   = formObj.end_hour.options[formObj.end_hour.selectedIndex].value;
        var end_minute = formObj.end_minute.options[formObj.end_minute.selectedIndex].value;
        if (str_hour < 24) {
            var str_date = "1970/01/01 ";   // 文末のスペースに注意
        } else {
            var str_date = "1970/01/02 ";   // 文末のスペースに注意
            str_hour = "00";
        }
        if (end_hour < 24) {
            var end_date = "1970/01/01 ";   // 文末のスペースに注意
        } else {
            var end_date = "1970/01/02 ";   // 文末のスペースに注意
            end_hour = "00";
        }
        targetObj.value = (Date.parse(end_date+end_hour+":"+end_minute+":00") - Date.parse(str_date+str_hour+":"+str_minute+":00")) / 1000 / 60;
        return;
        // 現在以下は使用しない
        // targetObj.value = (Date.parse("1970/01/01 "+end_hour+":"+end_minute+":00") - Date.parse("1970/01/01 "+str_hour+":"+str_minute+":00")) / 1000 / 60;
        for (var i in formObj.str_hour.options) {
            formObj.str_hour.options[i]
        }
    }
    
    /***** セットされた時間(分)の最小値と最大値のチェック *****/
    assembly_calendar.prototype.checkTimeValue = function (timeValue, formObj)
    {
        if (timeValue <= 0) {
            if (timeValue == "") timeValue = 0;
            alert(timeValue + " 分では登録できません。");
            return false;
        }
        if (timeValue > 1440) {
            alert(timeValue + " 分は 24時間 (1440分) を超えています。");
            return false;
        }
        if ((formObj.str_hour.value+formObj.str_minute.value) > "2400") {
            alert("開始時間が 24:00 を超えています。");
            return false;
        }
        if ((formObj.end_hour.value+formObj.end_minute.value) > "2400") {
            alert("終了時間が 24:00 を超えています。");
            return false;
        }
        return true;
    }
    
    /***** ConditionForm の入力チェックメソッド *****/
    assembly_calendar.prototype.checkConditionForm = function (obj)
    {
        // obj.targetPartsNo.value = obj.targetPartsNo.value.toUpperCase();
        if (obj.targetDateY.value < 2000 || obj.targetDateY.value > this.maxYear) {
            alert("対象期指定が不正です！");
            obj.targetDateY.focus();
            // obj.targetDateY.select();
            return false;
        }
        obj.targetDateStr.value = obj.targetDateY.value + "04";
        obj.targetDateEnd.value = parseInt(obj.targetDateY.value) + 1;  // parseInt()がポイント
        obj.targetDateEnd.value = obj.targetDateEnd.value + "03";
        /************
        if (!obj.targetPartsNo.value.match(/^[A-Z]{2}[0-9]{5}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("部品番号が間違っています！");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ************/
        this.parameter  = "&targetLine="   + obj.targetLine.value;
        this.parameter += "&targetDateY="   + obj.targetDateY.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        this.parameter += "&targetCalendar=" + obj.targetCalendar.value;
        if (obj.targetFormat.value) {
            this.parameter += "&targetFormat=" + obj.targetFormat.value;
        }
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    assembly_calendar.prototype.checkANDexecute = function (obj, flg)
    {
        if (this.checkConditionForm(obj)) {
            // obj.submit();
            // return false;
            if (flg == 1) {
                this.AjaxLoadTable("Calendar", "showAjax");
            } else {
                this.AjaxLoadTable("List", "showAjax");
                // this.AjaxLoadTable("ListWin", "showAjax");
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
    assembly_calendar.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("assembly_calendar_Main.php"+parm, 700, 350);
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
        var url = "assembly_calendar_Main.php" + parm;
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** URL指定 Ajax用ロードメソッド *****/
    assembly_calendar.prototype.AjaxLoadUrl = function (url)
    {
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
                document.getElementById("showAjax").innerHTML = xmlhttp.responseText;
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById("showAjax").innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
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
    assembly_calendar.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        // this.blink_msg = "部品番号";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    assembly_calendar.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class assembly_calendar END  */


///// インスタンスの生成
var AssemblyCalendar = new assembly_calendar();

