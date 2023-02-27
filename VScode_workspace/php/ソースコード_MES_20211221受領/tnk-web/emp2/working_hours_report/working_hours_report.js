//////////////////////////////////////////////////////////////////////////////
// 就業週報の集計 結果 照会                    MVC View部(JavaScriptクラス) //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/09/22 Created    working_hours_report.js                            //
// 2017/06/02 部課長説明 本格稼動                                           //
// 2017/06/15 開始・終了日付が20160331以前の場合エラーを表示                //
//            実際は20140401からデータは存在している                        //
//            自分のみエラー対象外                                          //
// 2017/06/28 開始日のみでも単日検索可能                                    //
// 2017/06/29 職位別照会に対応（工場長依頼）                                //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*     working_hours_report class base_class の拡張クラスの定義             *
/****************************************************************************
class working_hours_report extends base_class
*/
///// スーパークラスの継承
working_hours_report.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function working_hours_report()
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
    working_hours_report.prototype.set_focus = function (obj, status)
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
    working_hours_report.prototype.blink_disp = function (id_name)
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
    working_hours_report.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    working_hours_report.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    working_hours_report.prototype.win_open = function (url, w, h)
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
    working_hours_report.prototype.winActiveChk = function ()
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
    working_hours_report.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** リストボックスで選択した日付の開始・終了日付をセットする *****/
    working_hours_report.prototype.dateCreate = function (obj)
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
    working_hours_report.prototype.checkConditionForm = function (obj)
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
        if (obj.use_uid.value != 300144) {
            if (obj.targetDateStr.value < 20160401) {
                alert("開始日付は2016年4月1日以降にして下さい。");
                obj.targetDateStr.focus();
                obj.targetDateStr.select();
                return false;
            }
        }
        if (obj.targetDateStr.value) {
            if (obj.targetDateEnd.value) {
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
                if (obj.use_uid.value != 300144) {
                    if (obj.targetDateEnd.value < 20160401) {
                        alert("終了日付は2016年4月1日以降にして下さい。");
                        obj.targetDateEnd.focus();
                        obj.targetDateEnd.select();
                        return false;
                    }
                }
            } else {
                obj.targetDateEnd.value = obj.targetDateStr.value
            }
        }
        if (!obj.targetSection.value) {
            //if (!obj.uid.value) {
                alert("部門を選択してください！");
                return false;
            //}
            //if (obj.uid.value.length != 6) {
            //alert("社員番号の桁数は６桁です。");
            //obj.uid.focus();
            //obj.uid.select();
            //return false;
            //}
            //if (!this.isDigit(obj.uid.value)) {
            //    alert("社員番号は数字で入力して下さい。");
            //    obj.uid.focus();
            //    obj.uid.select();
            //    return false;
            //}
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
        this.parameter += "&targetSection=" + obj.targetSection.value;
        this.parameter += "&targetPosition=" + obj.targetPosition.value;
        var i;
        for (i = 0; i < obj.formal.length; i++) {
            if (obj.formal[i].checked) {
                this.parameter += "&formal=" + obj.formal[i].value;
            }
        }
        return true;
    }
    
    /***** ConditionForm の入力チェックメソッド 週報確認用*****/
    working_hours_report.prototype.checkConditionFormConfirm = function (obj)
    {
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
        if (obj.targetDateStr.value) {
            if (obj.targetDateEnd.value) {
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
            }
        } else {
            obj.targetDateEnd.value = obj.targetDateStr.value
        }
        this.parameter  = "&targetDateYM=" + obj.targetDateYM.value;
        this.parameter += "&targetDateStr=" + obj.targetDateStr.value;
        this.parameter += "&targetDateEnd=" + obj.targetDateEnd.value;
        return true;
    }
    
    /***** ConditionForm のAjax実行 訂正済み更新*****/
    working_hours_report.prototype.Confirmexecute = function (sid, str_date, end_date, section)
    {
        if (confirm("週報を確認済にしてよろしいですか？\n 確認済にすると元には戻せません。訂正内容の登録は終わってますか？")) {
                var parm = "&";
                parm += "section_id=" + sid;
                parm += "&str_date=" + str_date;
                parm += "&end_date=" + end_date;
                parm += "&targetSection=" + section;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&ConfirmFlg=y" + parm;
                document.CorrectForm.submit();
        }
    }
    /***** ConditionForm のAjax実行 訂正済み更新*****/
    working_hours_report.prototype.Confirmoneexecute = function (tnkuid, str_date, end_date, sid, section)
    {
        if (confirm("週報を確認済にしてよろしいですか？\n 確認状況の選択は間違っていませんか？。")) {
                var uid              = tnkuid.slice(3);
                var form_name        = "CorrectForm" + uid;
                var select_name      = "ConfirmFlg" + uid;
                var $elementReference = document.getElementById( select_name );
                var $selectedIndex    = $elementReference.selectedIndex;
                var confirm_flg       = $elementReference.options[$selectedIndex].value;
                var parm             = "&";
                parm += "uid=" + uid;
                parm += "&str_date=" + str_date;
                parm += "&end_date=" + end_date;
                parm += "&confirm_flg=" + confirm_flg;
                parm += "&section_id=" + sid;
                parm += "&targetSection=" + section;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&ConfirmOneFlg=y" + parm;
                document.CorrectForm.submit();
        }
    }
    /***** ConditionForm のselectを取得*****/
    working_hours_report.prototype.getSelected = function (select_name)
    {
        var $elementReference = document.getElementById( "ConfirmFlg005789" );
        var $selectedIndex = $elementReference.selectedIndex;
        var $value = $elementReference.options[$selectedIndex].value;
        document.getElementById( "selectOutputIndex" ).innerHTML = $selectedIndex;
        document.getElementById( "selectOutputValue" ).innerHTML = $value;
    }
    
    /***** ConditionForm のAjax実行 訂正済み更新*****/
    working_hours_report.prototype.Correctexecute = function (uid, date, flg)
    {
        if (flg == 2) {
            if (confirm("この訂正を訂正済にしてよろしいですか？")) {
                var parm = "&";
                parm += "user_id=" + uid;
                parm += "&date=" + date;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&CorrectFlg=y&CancelFlg=n" + parm;
                document.CorrectForm.submit();
            }
        } else {
            if (confirm("訂正済を取り消してよろしいですか？")) {
                var parm = "&";
                parm += "user_id=" + uid;
                parm += "&date=" + date;
                document.CorrectForm.action="../working_hours_report_Main.php?showMenu=CondForm&CorrectFlg=y&CancelFlg=y" + parm;
                document.CorrectForm.submit();
            }
        }
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    working_hours_report.prototype.checkANDexecute = function (obj, flg)
    {
        if (flg == 3) {
            this.AjaxLoadTable("Correct", "showAjax");
        } else if (flg == 4) {
            this.AjaxLoadTable("CorrectList", "showAjax");
        } else if (flg == 5) {
            this.AjaxLoadTable("CorrectEndList", "showAjax");
        } else if (flg == 6) {
            this.AjaxLoadTable("List", "showAjax");
        } else if (flg == 7) {
            if (this.checkConditionFormConfirm(obj)) {
                this.AjaxLoadTable("ConfirmList", "showAjax");
            }
        } else if (flg == 8) {
            this.AjaxLoadTable("ConfirmList", "showAjax");
        } else if (flg == 9) {
            this.AjaxLoadTable("CorrectList", "showAjax");
        } else if (flg == 11) {
            this.AjaxLoadTable("MailList", "showAjax");
        } else if (flg == 2) {
            
        } else if (this.checkConditionForm(obj)) {
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 10) {
                this.AjaxLoadTable("ListCo", "showAjax");
            } else {
                this.AjaxLoadTable("ListWin", "showAjax");
            }
            // 点滅のメッセージを変更する
            // this.blink_msg = "部品番号";
            // this.stop_blink();
        }
        return false;   // 実際にsubmitはさせない
    }
    /***** ConditionForm の確認フラグ *****/
    working_hours_report.prototype.ConfirmValue = function (flg)
    {
        
        var index  = document.CorrectForm.ConfirmFlg.selectedIndex; 
        var ConFlg = document.CorrectForm.ConfirmFlgt.options[index].value; 
        
        alert('ああああ');
        return ConFlg;   // 実際にsubmitはさせない
        return false;   // 実際にsubmitはさせない
    }
    
    /***** ConfirmFlgの時のAjax実行 *****/
    working_hours_report.prototype.ConfirmFlgexecute = function (obj, flg, section)
    {
        this.AjaxLoadTableConfirm("List", "showAjax", section);
        return false;   // 実際にsubmitはさせない
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるConfirmFlgの時のAjax用リロードメソッド *****/
    working_hours_report.prototype.AjaxLoadTableConfirm = function (showMenu, location, section)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += "&targetSection=" + section
        parm += "&formal=details"
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("working_hours_report_Main.php"+parm, 700, 350);
            return;
        }
        if (showMenu == "Correct") {    // 別ウィンドウで表示
            this.win_open("../working_hours_report_Main.php"+parm, 1000, 800);
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
            xmlhttp.open("GET", "working_hours_report_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    working_hours_report.prototype.AjaxLoadTable = function (showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("working_hours_report_Main.php"+parm, 700, 350);
            return;
        }
        if (showMenu == "Correct") {    // 別ウィンドウで表示
            this.win_open("../working_hours_report_Main.php"+parm, 1000, 800);
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
            xmlhttp.open("GET", "working_hours_report_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    working_hours_report.prototype.viewClear = function ()
    {
        document.getElementById("showAjax").innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        // this.blink_msg = "部品番号";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    working_hours_report.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class working_hours_report END  */


///// インスタンスの生成
var WorkingHoursReport = new working_hours_report();

