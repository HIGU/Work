//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス         MVC View部(JavaScriptクラス) //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/26 Created   common_authority.js                                 //
// 2006/08/02 document.getElementById(location)で IEのみNULLエラーになる時が//
//            あり try {} catch (e) {} を追加  NN7.1ではOK                  //
// 2006/09/06 権限名の修正機能追加に伴い edit/updateDivision  関係を追加    //
// 2006/12/05 win_open()メソッドの変数名のミスを修正 name → winName        //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*          common_authority class base_class の拡張クラスの定義            *
/****************************************************************************
class common_authority extends base_class
*/
///// スーパークラスの継承
common_authority.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function common_authority()
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
    common_authority.prototype.set_focus = function (obj, status)
    {
        if (obj) {
            obj.focus();
            if (status == "select") obj.select();
        }
    }
    
    /***** 点滅表示メソッド *****/
    /***** blink_flg Private property 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
    common_authority.prototype.blink_disp = function (id_name)
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
    common_authority.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    common_authority.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    common_authority.prototype.win_open = function (url, w, h, winName)
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
    common_authority.prototype.winActiveChk = function ()
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
    common_authority.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** DOM を使用したイベント登録メソッド *****/
    common_authority.prototype.addListener = function (obj, eventType, func, cap)
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
    common_authority.prototype.setEventListeners = function (eventType, ID)
    {
        var eSource = document.getElementById(ID);
        this.addListener(eSource, eventType, catchEventListener, false);  // catchEventListener()は単独関数
        ///// 上記の eventType = "click" は "Click" のように大文字を使用してはいけない
    }
    
    /***** 権限マスターの削除 確認 ＆ 実行 メソッド *****/
    common_authority.prototype.deleteDivision = function (division, div_name)
    {
        if (confirm(div_name + "\n\nを削除します。宜しいですか？")) {
            // メンバーの表示をクリアする
            this.viewClear("showAjax2");
            // Ajax用パラメーターをセット
            this.parameter  = "&targetDivision="   + division;
            // Ajax通信
            this.AjaxLoadTable("DeleteDivision", "ListDivision", "showAjax1");
        }
    }
    
    /***** 権限マスターの権限名の修正 ＆ 実行 メソッド *****/
    common_authority.prototype.editDivision = function (division, div_name)
    {
        // if (confirm(div_name + "\n\nを修正します。宜しいですか？")) {
            // メンバーの表示をクリアする
            this.viewClear("showAjax2");
            // Ajax用パラメーターをセット
            this.parameter  = "&targetDivision="   + division;
            // Ajax通信
            this.AjaxLoadTable("EditDivision", "ListDivision", "showAjax1");
        // }
    }
    
    /***** 権限マスターの権限名の修正登録 ＆ 実行 メソッド *****/
    common_authority.prototype.updateDivision = function (division, div_name)
    {
        if (confirm(div_name + "\n\nを登録します。宜しいですか？")) {
            // メンバーの表示をクリアする
            this.viewClear("showAjax2");
            // Ajax用パラメーターをセット
            this.parameter  = "&targetDivision="   + division;
            this.parameter += "&targetAuthName="   + div_name;
            // Ajax通信
            this.AjaxLoadTable("UpdateDivision", "ListDivision", "showAjax1");
        }
    }
    
    /***** 権限メンバーのリスト指示 メソッド *****/
    common_authority.prototype.listID = function (division)
    {
        // Ajax用パラメーターをセット
        this.parameter  = "&targetDivision="   + division;
        // Ajax通信
        this.AjaxLoadTable("ListID", "ListID", "showAjax2");
    }
    
    /***** 権限メンバーの削除 確認 ＆ 実行 メソッド *****/
    common_authority.prototype.deleteID = function (id, division)
    {
        if (confirm(id + "\n\nを削除します。宜しいですか？")) {
            // Ajax用パラメーターをセット
            this.parameter  = "&targetID="   + id;
            this.parameter  += "&targetDivision="   + division;
            // Ajax通信
            this.AjaxLoadTable("DeleteID", "ListID", "showAjax2");
        }
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    common_authority.prototype.AjaxLoadTable = function (Action, showMenu, location)
    {
        if (!location) location = "showAjax1";   // Default値の設定
        var parm = "?";
        parm += "Action=" + Action;
        parm += "&showMenu=" + showMenu;
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("common_authority_Main.php"+parm, 700, 350);
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
                    CommonAuthority.setEventListeners("change", "targetCategory");
                    CommonAuthority.setEventListeners("focus", "targetCategory");
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
        var url = "common_authority_Main.php" + parm;
        try {
            xmlhttp.open("GET", url);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    /***** URL指定 Ajax用ロードメソッド *****/
    common_authority.prototype.AjaxLoadUrl = function (url, location)
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
    common_authority.prototype.viewClear = function (showAjax)
    {
        document.getElementById(showAjax).innerHTML = "";
        // 点滅のメッセージを初期状態に戻す
        // this.blink_msg = "部品番号";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    common_authority.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class common_authority END  */


///// インスタンスの生成
var CommonAuthority = new common_authority();


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
    case "addDivisionForm":
    case "addDivision":
        // 入力項目のチェック
        var targetAuthName = document.getElementById("targetAuthName");
        if (!targetAuthName.value) {
            alert("権限名が入力されていません。説明文も含めて入力して下さい。");
            targetAuthName.focus();
            targetAuthName.select();
            return false;
        }
        // Ajax用パラメーターをセット
        CommonAuthority.parameter  = "&targetAuthName="   + targetAuthName.value;
        // Ajax通信
        // var url = "common_authority_Main.php?Action=AddDivision&showMenu=ListDivision&targetAuthName=" + targetAuthName.value;
        // CommonAuthority.AjaxLoadUrl(url, "showAjax1");
        CommonAuthority.AjaxLoadTable("AddDivision", "ListDivision", "showAjax1");
        return false;
    case "addIDForm":
    case "addID":
        // 入力項目のチェック
        var targetID = document.getElementById("targetID");
        if (!targetID.value) {
            alert("メンバーが入力されていません。\n\n社員番号・IPアドレス・MACアドレス・その他等を入力して下さい。");
            targetID.focus();
            targetID.select();
            return false;
        }
        var targetCategory = document.getElementById("targetCategory")[document.getElementById("targetCategory").selectedIndex];
        if (!targetCategory.value) {
            alert("種類が選択されていません。\n\n社員番号・IPアドレス・MACアドレス・その他を選択して下さい。");
            document.getElementById("targetCategory").focus();
            return false;
        }
        // Ajax用パラメーターをセット
        CommonAuthority.parameter  = "&targetID="   + targetID.value;
        CommonAuthority.parameter  += "&targetDivision="   + document.getElementById("targetDivision").value;
        CommonAuthority.parameter  += "&targetCategory="   + targetCategory.value;
        // Ajax通信
        CommonAuthority.AjaxLoadTable("AddID", "ListID", "showAjax2");
        return false;
    case "targetID":
        // 入力項目のチェック
        if (!document.getElementById("targetID").value) {
            return false;
        }
        // Ajax用パラメーターをセット
        CommonAuthority.parameter  = "&targetID="   + document.getElementById("targetID").value;
        // Ajax通信
        CommonAuthority.AjaxLoadTable("ConfirmID", "ListCategory", "showCateList");
        return false;
    case "targetCategory":
        // 入力項目のチェック
        if (!document.getElementById("targetID").value) {
            return false;
        }
        // Ajax用パラメーターをセット
        CommonAuthority.parameter  = "&targetID="   + document.getElementById("targetID").value;
        CommonAuthority.parameter  += "&targetCategory="   + document.getElementById("targetCategory")[document.getElementById("targetCategory").selectedIndex].value;
        // Ajax通信
        CommonAuthority.AjaxLoadTable("ConfirmID", "GetIDName", "showIDName");
        return false;
    default:
        alert("ブラウザーがNNの場合、予想外のイベントが発生します。");
        return false;
    }
}

