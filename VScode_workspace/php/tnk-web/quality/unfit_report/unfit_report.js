//////////////////////////////////////////////////////////////////////////////
// 全社共有 不適合報告書の照会・メンテナンス                                //
//                                           MVC View 部 (JavaScriptクラス) //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created    unfit_report.js                                    //
// 2008/08/29 masterstで本稼動開始                                          //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     unfit_report class テンプレートの拡張クラスの定義           *
/****************************************************************************
class unfit_report extends base_class
{   */
    ///// スーパークラスの継承
    unfit_report.prototype = new base_class();   // base_class の継承
    ///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
    var _GDEBUG = false;
    
    ///// Constructer の定義
    function unfit_report()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // プロパティーの初期化
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        unfit_report.prototype.set_focus        = set_focus;        // 指定の入力エレメントにフォーカス
        unfit_report.prototype.blink_disp       = blink_disp;       // 点滅表示メソッド
        unfit_report.prototype.obj_upper        = obj_upper;        // オブジェの値を大文字変換
        unfit_report.prototype.win_open         = win_open;         // サブウィンドウを中央に表示
        unfit_report.prototype.winActiveChk     = winActiveChk;     // サブウィンドウのActiveチェック
        unfit_report.prototype.win_show         = win_show;         // モーダルダイアログを表示(IE専用)
        unfit_report.prototype.sponsorNameCopy  = sponsorNameCopy;  // 作成者の名前のコピーメソッド
        unfit_report.prototype.attenCopy        = attenCopy;        // 報告先のtextereaへの表示メソッド
        unfit_report.prototype.attenCopy2       = attenCopy2;       // 報告先のtextereaへの表示メソッド グループ登録版
        unfit_report.prototype.apend_formCheck  = apend_formCheck;  // apend_form の入力チェックメソッド
        unfit_report.prototype.follow_formCheck = follow_formCheck; // follow_form の入力チェックメソッド
        unfit_report.prototype.group_formCheck  = group_formCheck;  // group_form の入力チェックメソッド
        unfit_report.prototype.groupMemberCopy  = groupMemberCopy;  // 報告先グループをatten[]へのコピーメソッド(内部でattenCopyを呼出す)
        unfit_report.prototype.ControlFormSubmit= ControlFormSubmit;// ControlForm のサブミットメソッド
        unfit_report.prototype.addFavoriteIcon  = addFavoriteIcon;  // お気に入りにアイコンを追加する
        
        return this;    // Object Return
    }
    
    /***** パラメーターで指定されたオブジェクトのエレメントにフォーカスさせる *****/
    function set_focus(obj, status)
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
    
    /***** 点滅表示のHTMLドキュメント *****/
    /***** blink_flg はグローバル変数に注意 下の例は0.5秒毎に点滅 *****/
    /***** <body onLoad='setInterval("templ.blink_disp(\"caption\")", 500)'> *****/
    function blink_disp(id_name)
    {
        if (blink_flag == 1) {
            document.getElementById(id_name).innerHTML = "";
            blink_flag = 2;
        } else {
            document.getElementById(id_name).innerHTML = "サンプルでアイテムマスターを表示しています";
            blink_flag = 1;
        }
    }
    
    /***** オブジェクトの値を大文字変換する *****/
    function obj_upper(obj) {
        obj.value = obj.value.toUpperCase();
        return true;
    }
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    function win_open(url, w, h) {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
    
    /***** サブウィンドウ側でWindowのActiveチェックを行う *****/
    /***** <body onLoad="setInterval('templ.winActiveChk()',100)">*****/
    function winActiveChk() {
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
    function win_show(url, w, h) {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        showModalDialog(url, 'show_win', "dialogWidth:" + w + "px;dialogHeight:" + h + "px");
    }
    
    /***** 作成者の名前のコピーメソッド *****/
    function sponsorNameCopy()
    {
        // フルでオブジェクトの指定
        document.apend_form.sponsor.value = document.apend_form.userID_name.value;
    }
    
    /***** 作成者の名前のコピーメソッド *****/
    function sponsorNameCopyFollow()
    {
        // フルでオブジェクトの指定
        document.follow_form.sponsor.value = document.follow_form.userID_name.value;
    }
    
    /***** attenView の表示用コピーメソッド *****/
    function attenCopy(obj)
    {
        document.apend_form.attenView.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (document.apend_form.attenView.value == "") {
                    document.apend_form.attenView.value += obj.options[i].text;
                } else {
                    document.apend_form.attenView.value += (", " + obj.options[i].text);
                }
            }
        }
        /*****  エレメント名を含める場合は以下の様にする
        for (var i=0; i<obj.elements['atten[]'].options.length; i++) {
            if (obj.elements['atten[]'].options[i].selected) {
                obj.attenView.value += obj.elements['atten[]'].options[i].text;
            }
        }
        *****/
    }
    
    /***** attenView の表示用コピーメソッド フォローアップ用*****/
    function attenCopyFollow(obj)
    {
        document.follow_form.attenView.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (document.follow_form.attenView.value == "") {
                    document.follow_form.attenView.value += obj.options[i].text;
                } else {
                    document.follow_form.attenView.value += (", " + obj.options[i].text);
                }
            }
        }
        /*****  エレメント名を含める場合は以下の様にする
        for (var i=0; i<obj.elements['atten[]'].options.length; i++) {
            if (obj.elements['atten[]'].options[i].selected) {
                obj.attenView.value += obj.elements['atten[]'].options[i].text;
            }
        }
        *****/
    }
    
    /***** attenView の表示用コピーメソッド グループ編集用 *****/
    function attenCopy2(obj, obj2)
    {
        obj2.value = "";
        for (var i=0; i<obj.options.length; i++) {
            if (obj.options[i].selected) {
                if (obj2.value == "") {
                    obj2.value += obj.options[i].text;
                } else {
                    obj2.value += (", " + obj.options[i].text);
                }
            }
        }
    }
    
    /***** apend_form の入力チェックメソッド *****/
    function apend_formCheck(obj) {
        if (obj.subject.value.length == 0) {
            alert("不適合内容が入力されていません！");
            obj.subject.focus();
            // obj.subject.select();
            return false;
        }
        if (obj.place.value.length == 0) {
            alert("発生場所が入力されていません！");
            obj.place.focus();
            // obj.place.select();
            return false;
        }
        if (obj.section.value.length == 0) {
            alert("責任部門が入力されていません！");
            obj.section.focus();
            // obj.section.select();
            return false;
        }
        if (obj.assy_no.value.length == 0) {
            if (obj.parts_no.value.length == 0) {
                alert("部品番号か製品番号のどちらかを必ず入力して下さい！");
                obj.assy_no.focus();
                // obj.parts_no.select();
                return false;
            }
        }
        if (obj.occur_cause.value.length == 0) {
            alert("発生原因が入力されていません！(不明の場合は『調査中』)");
            obj.occur_cause.focus();
            // obj.occur_cause.select();
            return false;
        }
        if (obj.occur_cause.value.length == 0) {
            alert("流出が入力されていません！(不明の場合は『調査中』、流出なしの場合は『流出無し』)");
            obj.occur_cause.focus();
            // obj.occur_cause.select();
            return false;
        }
        if (obj.unfit_num.value.length == 0) {
            alert("不適合数量が入力されていません！(調査中の場合は０)");
            obj.unfit_num.focus();
            // obj.unfit_num.select();
            return false;
        }
        if (obj.issue_num.value.length == 0) {
            alert("流出数量が入力されていません！(調査中・流出なしの場合は０)");
            obj.issue_num.focus();
            // obj.issue_num.select();
            return false;
        }
        if (obj.subject.value.match(/㈱/)) {
            // /m(オプション)で複数行のマッチングを行うが上記でもOK
            alert("件名に機種依存文字の ㈱ (1文字のカブ)が入っています。\n\n(株) (半角カッコとカブ)等に置換えて下さい。");
            obj.subject.focus();
            return false;
        }
        if (obj.subject.value.match(/[①-⑳]/)) {
            alert("件名に機種依存文字の ① 等が入っています。\n\n（１） (半角又は全角カッコと１)等に置換えて下さい。");
            obj.subject.focus();
            return false;
        }
        if (obj.sponsor.value.length == 0) {
            alert("作成者が指定されていません！");
            obj.userID_name.focus();
            // obj.userID_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("報告先が指定されていません！");
            obj.elements['atten[]'].focus();
            return false;
        }
        if (obj.measure[0].checked) {
            if (obj.receipt_no.value.length == 0) {
                alert("報告書を完了する際は受付No.を入力してください！");
                obj.receipt_no.focus();
                return false;
            }
        }
        return true;
    }
    /***** follow_form の入力チェックメソッド *****/
    function follow_formCheck(obj) {
        if (obj.sponsor.value.length == 0) {
            alert("作成者が指定されていません！");
            obj.userID_name.focus();
            // obj.userID_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("報告先が指定されていません！");
            obj.elements['atten[]'].focus();
            return false;
        }
        return true;
    }
    /***** group_form の入力チェックメソッド(グループ番号, グループ名, 出席者, 個人/共有用) *****/
    function group_formCheck(obj) {
        // obj.group_no2.value = obj.group_no2.value.toUpperCase();
        if (obj.group_no2.value.length == 0) {
            alert("グループ番号がブランクです。");
            obj.group_no2.focus();
            obj.group_no2.select();
            return false;
        }
        if (!this.isDigit(obj.group_no2.value)) {
            alert("グループ番号は数字で入力して下さい。");
            obj.group_no2.focus();
            obj.group_no2.select();
            return false;
        }
        if (obj.group_no2.value < 1 || obj.group_no2.value > 999) {
            alert("グループ番号は１から９９９までです！");
            obj.group_no2.focus();
            obj.group_no2.select();
            return false;
        }
        if (obj.group_name.value.length == 0) {
            alert("グループ名がブランクです。");
            obj.group_name.focus();
            obj.group_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("報告先が指定されていません！");
            obj.elements['atten[]'].focus();
            return false;
        }
        if (!obj.owner[0].checked && !obj.owner[1].checked) {
            alert("個人用 ／ 共有用 のどちらかをチェックして下さい。");
            obj.owner[0].focus();
            return false;
        }
        return true;
    }
    
    /***** group_nameの選択オプションを atten[] へコピーし attenViewに表示させる *****/
    /***** グローバル変数の Ggroup_member を使用する *****/
    function groupMemberCopy(groupObj, attenObj)
    {
        // 初期化
        for (var r=0; r<attenObj.options.length; r++) {
            attenObj.options[r].selected = false;
        }
        // コピー
        for (var i=1; i<groupObj.options.length; i++) {
            if (groupObj.options[i].selected) {
                for (var j=0; j<Ggroup_member[i-1].length; j++) {
                    for (var k=0; k<attenObj.options.length; k++) {
                        if (attenObj.options[k].value == Ggroup_member[i-1][j]) {
                            attenObj.options[k].selected = true;
                        } else {
                            // attenObj.options[k].selected = false;
                        }
                    }
                }
            }
        }
        // attenView へコピー
        this.attenCopy(attenObj);
    }
    
    /***** ControlForm の Submit メソッド 二重送信対策 *****/
    function ControlFormSubmit(radioObj, formObj)
    {
        radioObj.checked = true;
        formObj.submit();
        return false;       // ←これが二重Submitの対策
    }
    
    /***** お気に入りにアイコンを追加する 目的はデスクトップにアイコンを貼り付ける為 *****/
    function addFavoriteIcon(url, uid)
    {
        if (!confirm("お気に入りにアイコンを追加します。\n\n宜しいですか？")) return false;
        if (document.all && !window.opera) {
            if (uid >= 100 && uid <= 999999) {
                window.external.AddFavorite(url + "?calUid=" + uid, "不適合報告書 照会・作成");
            } else {
                window.external.AddFavorite(url, "不適合報告書 照会・作成");
            }
        }
        return false;       // ←これは二重 実行の対策
    }
    
    /***** 部品名検索のため部品番号をSubmitする *****/
    function PartsNoSubmit(no)
    {
        var parts_no = no;
        if (parts_no.value.length == 9) {
    	    document.apend_form.action="unfit_report_Main.php";
            document.ControlForm.showMenu.value = "Apend";
            document.apend_form.partsflg.value = "TRUE";
            document.apend_form.submit();
        }
    }
    /***** 製品名検索のため製品番号をSubmitする *****/
    function AssyNoSubmit(no)
    {
        var assy_no = no;
        if (assy_no.value.length == 9) {
    	    document.apend_form.action="unfit_report_Main.php";
            document.ControlForm.showMenu.value = "Apend";
            document.apend_form.assyflg.value = "TRUE";
            document.apend_form.submit();
        }
    }
    function chkCode(id) {
      work='';
      for (lp=0;lp<id.value.length;lp++) {
        unicode=id.value.charCodeAt(lp);
        if ((0xff0f<unicode) && (unicode<0xff1a)) {
          work+=String.fromCharCode(unicode-0xfee0);
        } else if ((0xff20<unicode) && (unicode<0xff3b)) {
          work+=String.fromCharCode(unicode-0xfee0);
        } else if ((0xff40<unicode) && (unicode<0xff5b)) {
          work+=String.fromCharCode(unicode-0xfee0);
        } else {
          work+=String.fromCharCode(unicode);
        }
      }
      id.value=work; /* 半角処理のみ */
      //id.value=work.toUpperCase(); /* 大文字に統一する場合に使用 */
      //id.value=work.toLowerCase(); /* 小文字に統一する場合に使用 */
    }
    function limitChars(target,maxlength,maxrow) {
        Str = target.value;
        lines = Str.split("\n").length;
        StrAry = Str.split("\n");
        if( lines > maxrow ){
            alert(maxrow+"行以内で入力して下さい。");
            target.value = Str.replace(/(.|\r\n|\r|\n)$/,"");
        }

        if ( target.value.length > maxlength ) {
        alert(maxlength + "字以内で入力してください");
        target.value = target.value.substr(0,maxlength);
        }
        target.focus();
    }

/*
}   // class unfit_report END  */


///// インスタンスの生成
var UnfitReport = new unfit_report();
// blink_disp()メソッド内で使用するグローバル変数のセット
var blink_flag = 1;


