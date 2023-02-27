//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム 貸出台帳メニュー           MVC View部(JavaScriptクラス) //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/16 Created   punchMark_lendList.js                               //
// 2007/11/16 AjaxのGETメソッドはSJIS→EUC-JPへ POSTメソッドはUTF-8→UTF-8 //
//  checkANDexecute(obj,flg)→checkANDexecute(obj,action,showMenu,location) //
//  viewClear()→viewClear(location) の２つのメソッドを変更 可用性・可読性  //
// 2007/11/30 win_open()メソッドのパラメーター変更(ウィンドウ名の追加)      //
// 2007/12/03 刻印･台帳ボタンの実行時の色を変化させるロジックを追加         //
//            上記にプラスしてviewClear()メソッドにフィールドの初期化も追加 //
//////////////////////////////////////////////////////////////////////////////

///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
var _GDEBUG = false;

/****************************************************************************
/*          punchMark_lendList class base_class の拡張クラスの定義          *
/****************************************************************************
class punchMark_lendList extends base_class
*/
///// スーパークラスの継承
punchMark_lendList.prototype = new base_class();    // base_class の継承
///// Constructer の定義
function punchMark_lendList()
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
    punchMark_lendList.prototype.set_focus = function (obj, status)
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
    punchMark_lendList.prototype.blink_disp = function (id_name)
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
    punchMark_lendList.prototype.stop_blink = function ()
    {
        document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
        clearInterval(this.intervalID);
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    punchMark_lendList.prototype.obj_upper = function (obj)
    {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    punchMark_lendList.prototype.win_open = function (url, w, h, winName)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        if (!winName) winName = ''; // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, winName, 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    punchMark_lendList.prototype.winActiveChk = function ()
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
    punchMark_lendList.prototype.win_show = function (url, w, h)
    {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** リストボックスで選択した日付の開始・終了日付をセットする *****/
    punchMark_lendList.prototype.dateCreate = function (obj)
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
    punchMark_lendList.prototype.checkConditionForm = function (obj)
    {
        /************
        if (!obj.targetPartsNo.value.match(/^[A-Z0-9]{7}[-#]{1}[A-Z0-9]{1}$/)) {
            alert("部品番号が間違っています！");
            obj.targetPartsNo.focus();
            obj.targetPartsNo.select();
            return false;
        }
        ************/
        var value = "";
        for (var i=0; i<obj.elements.length; i++) {
            if (obj.elements[i].value == "") continue;              // ブランクは対象外
            else if (obj.elements[i].value == "検索") continue;     // ボタンは対象外
            else if (obj.elements[i].value == "開く") continue;     // ボタンは対象外
            else if (obj.elements[i].value == "貸出") continue;     // ボタンは対象外
            else if (obj.elements[i].value == "返却") continue;     // ボタンは対象外
            value += ("&" + obj.elements[i].name + "=" + obj.elements[i].value);
        }
        // this.parameter = value.substring(1);    // 最初の&を削除
        this.parameter = value;
        
        return true;
    }
    
    /***** ConditionForm の入力チェックをしてAjax実行 *****/
    // パラメーターの説明
    // obj=FORMオブジェクト, action=実行メソッド, showMenu=表示メソッド, location=表示先のID
    punchMark_lendList.prototype.checkANDexecute = function (obj, action, showMenu, location)
    {
        if (showMenu == 'MarkList') {
            obj.ajaxLend.style.color = "black";
            obj.ajaxSearch.style.color = "blue";
        } else if(showMenu == 'LendList') {
            obj.ajaxSearch.style.color = "black";
            obj.ajaxLend.style.color = "blue";
        }
        var result = true;
        if (this.checkConditionForm(obj)) {
            if (showMenu == "List") {
                resuslt = this.AjaxLoadTable(action, showMenu, location);
            } else {    // ListWinを想定
                this.AjaxLoadTable(action, showMenu, location);
            }
            // 点滅のメッセージを変更する
            // this.blink_msg = "部品番号";
            // this.stop_blink();
        }
        if (result) {
            return false;   // 実際にsubmitはさせない
        } else {
            return true;    // Ajax失敗なのでsubmitさせる
        }
    }
    
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    punchMark_lendList.prototype.AjaxLoadTable = function (action, showMenu, location)
    {
        var parm = "?";
        parm += "Action=" + action + "&showMenu=" + showMenu;
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("punchMark_lendList_Main.php"+parm, 900, 700);
            return true;
        }
        if (!document.getElementById(location)) {
            alert("指定されたID=" + location + "正しくありません！");
            return true;
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
                return false;
                alert("ご使用のブラウザーは未対応です。\n\n" + e);
            }
        }
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(location).innerHTML = xmlhttp.responseText;
            } else if (xmlhttp.readyState == 4 && xmlhttp.status == 404) {
                alert("データの取得に失敗しました。管理担当者へ連絡して下さい。");
            } else {
                // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
                document.getElementById(location).innerHTML = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
            }
        }
        try {
            /***** GETメソッドを使用する場合
            xmlhttp.open("GET", "punchMark_lendList_Main.php" + parm, true);  // true=Ajaxの本領 非同期通信を行う。指定しなければtrue
            xmlhttp.setRequestHeader("If-Modified-Since", "Thu, 01 Jun 1970 00:00:00 GMT"); // IEのキャッシュ対応
            xmlhttp.send("");   // null → "" Linux の Konqueror/3.3 でエラー対応
            *****/
            xmlhttp.open("POST", "punchMark_lendList_Main.php", true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send(parm.substr(1));
        } catch (e) {
            return false;
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
        return true;
    }
    
    /***** 結果表示領域のクリアーメソッド *****/
    punchMark_lendList.prototype.viewClear = function (location)
    {
        document.ConditionForm.ajaxSearch.style.color   = "black";
        document.ConditionForm.ajaxLend.style.color     = "black";
        document.ConditionForm.targetPartsNo.value      = "";
        document.ConditionForm.targetMarkCode.value     = "";
        document.ConditionForm.targetShelfNo.value      = "";
        document.ConditionForm.targetNote.value         = "";
        document.getElementById(location).innerHTML     = "";
        // 点滅のメッセージを初期状態に戻す
        // this.blink_msg = "部品番号";
        // document.getElementById(this.blink_id_name).innerHTML = this.blink_msg;
    }
    
    /***** メソッド実装によるWaitMessage表示 *****/
    punchMark_lendList.prototype.WaitMessage = function ()
    {
        var WaitMsg = "<br><table width='100%' border='0'><tr><td align='center' style='font-size:20pt; font-weight:bold;'>処理中です。お待ち下さい。<br><img src='/img/tnk-turbine.gif' width='68' height='72'></td></tr></table>";
        document.getElementById("showAjax").innerHTML = WaitMsg;
    }
    
    return this;    // Object Return
    
}   /* class punchMark_lendList END  */


///// インスタンスの生成
var PunchMarkLendList = new punchMark_lendList();

