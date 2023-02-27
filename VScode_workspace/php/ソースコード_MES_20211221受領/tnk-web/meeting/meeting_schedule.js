//////////////////////////////////////////////////////////////////////////////
// 全社共有 打合せ(会議)スケジュール表の照会・メンテナンス                  //
//                                           MVC View 部 (JavaScriptクラス) //
// Copyright (C) 2005-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/02 Created    meeting_schedule.js                                //
// 2005/11/21 groupMemberCopy()メソッドの追加                               //
// 2005/11/22 ControlFormSubmit()メソッド 二重Submit対策で追加              //
// 2005/11/24 addFavoriteIcon(url,uid)お気に入りにアイコン追加メソッドを追加//
// 2006/01/25 apend_formCheck()メソッドに機種依存文字のチェックを追加       //
// 2006/04/17 �　銑瓦竜ー鎔預己源�対応                                      //
// 2009/12/17 照会・印刷用入力チェックprint_formCheckを追加            大谷 //
// 2019/03/15 不在者をウィンドウで表示する為、追加                     大谷 //
// 2020/09/11 通達発効状況照会ウインドウ表示を追加                     大谷 //
// 2020/11/27 営繕状況照会ウインドウ表示を追加                         大谷 //
// 2021/11/17 総合届承認待ち情報の表示関連を追加                       和氣 //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
/*     meeting_schedule class テンプレートの拡張クラスの定義           *
/****************************************************************************
class meeting_schedule extends base_class
{   */
    ///// スーパークラスの継承
    meeting_schedule.prototype = new base_class();   // base_class の継承
    ///// グローバル変数 _GDEBUG の初期値をセット(リリース時はfalseにセットする)
    var _GDEBUG = false;
    
    ///// Constructer の定義
    function meeting_schedule()
    {
        /***********************************************************************
        *                           Private properties                         *
        ***********************************************************************/
        // this.properties = false;                         // プロパティーの初期化
        
        /************************************************************************
        *                           Public methods                              *
        ************************************************************************/
        meeting_schedule.prototype.set_focus        = set_focus;        // 指定の入力エレメントにフォーカス
        meeting_schedule.prototype.blink_disp       = blink_disp;       // 点滅表示メソッド
        meeting_schedule.prototype.obj_upper        = obj_upper;        // オブジェの値を大文字変換
        meeting_schedule.prototype.win_open         = win_open;         // サブウィンドウを中央に表示
        meeting_schedule.prototype.win_open2        = win_open2;        // サブウィンドウを中央に表示2
        meeting_schedule.prototype.setAdmitCnt      = setAdmitCnt;      // 総合届承認待ち件数
        meeting_schedule.prototype.winActiveChk     = winActiveChk;     // サブウィンドウのActiveチェック
        meeting_schedule.prototype.win_show         = win_show;         // モーダルダイアログを表示(IE専用)
        meeting_schedule.prototype.strTimeCopy      = strTimeCopy;      // 開始時間のコピーメソッド
        meeting_schedule.prototype.endTimeCopy      = endTimeCopy;      // 終了時間のコピーメソッド
        meeting_schedule.prototype.sponsorNameCopy  = sponsorNameCopy;  // 主催者の名前のコピーメソッド
        meeting_schedule.prototype.attenCopy        = attenCopy;        // 参加者のtextereaへの表示メソッド
        meeting_schedule.prototype.attenCopy2       = attenCopy2;       // 参加者のtextereaへの表示メソッド グループ登録版
        meeting_schedule.prototype.roomCopy         = roomCopy;         // 会議場所のコピーメソッド
        meeting_schedule.prototype.carCopy          = carCopy;          // 社用車のコピーメソッド
        meeting_schedule.prototype.apend_formCheck  = apend_formCheck;  // apend_form の入力チェックメソッド
        meeting_schedule.prototype.room_formCheck   = room_formCheck;   // room_form の入力チェックメソッド
        meeting_schedule.prototype.car_formCheck    = car_formCheck;    // car_form の入力チェックメソッド
        meeting_schedule.prototype.group_formCheck  = group_formCheck;  // group_form の入力チェックメソッド
        meeting_schedule.prototype.print_formCheck  = print_formCheck;  // print_form の入力チェックメソッド
        meeting_schedule.prototype.groupMemberCopy  = groupMemberCopy;  // 参加者グループをatten[]へのコピーメソッド(内部でattenCopyを呼出す)
        meeting_schedule.prototype.ControlFormSubmit= ControlFormSubmit;// ControlForm のサブミットメソッド
        meeting_schedule.prototype.addFavoriteIcon  = addFavoriteIcon;  // お気に入りにアイコンを追加する
        meeting_schedule.prototype.checkANDexecute  = checkANDexecute;  // 不在者のウインドウ表示
        meeting_schedule.prototype.AjaxLoadTable    = AjaxLoadTable;    // 不在者のウインドウ表示2
        meeting_schedule.prototype.AjaxLoadPITable  = AjaxLoadPITable;  // PIカレンダーのウインドウ表示
        
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
    

    var subWinObj;     // サブウインドウオブジェクト
    
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    function win_open(url, w, h) {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要

        if( (subWinObj) && (!subWinObj.closed) ){   // サブウインドウが開かれているか？
            subWinObj.close();                      // サブウインドウを閉じる
        }

        subWinObj = window.open(url, 'view_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
//        subWinObj.blur();      // サブウインドウにフォーカスを設定する
//        window.focus();        // 自画面からフォーカスを取得
//        window.blur();         // 自画面からフォーカスを放す
//        subWinObj.focus();     // サブウインドウにフォーカスを設定する
    }
    
    var subWinObj2;         // サブウインドウオブジェクト
    var timerFlag = false;  // 時間指定フラグ
    /***** 指定の大きさのサブウィンドウを中央に表示する *****/
    /***** Windows XP SP2 ではセキュリティの警告が出る  *****/
    function win_open2(url, w, h) {
        if (!w) w = 800;     // 初期値
        if (!h) h = 600;     // 初期値
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // 微調整が必要
        
        if( (subWinObj2) && (!subWinObj2.closed) ){ // サブウインドウが開かれているか？
            //subWinObj2.close();                     // サブウインドウを閉じる
        } else {
            var openFlag = true;   // 初期値（true 表示）
            
            // 時間指定ありなら時間をチェックする
            if( timerFlag ) {
                var now = new Date();
                if( now.getHours()==10 && now.getMinutes()==30 && now.getSeconds()>=0 && now.getSeconds()<=10
                ||  now.getHours()==12 && now.getMinutes()==0 && now.getSeconds()>=0 && now.getSeconds()<=10
                ||  now.getHours()==15 && now.getMinutes()==0 && now.getSeconds()>=0 && now.getSeconds()<=10 ) {
                    // 表示させる
                    // alert( '現在時刻：' + now.toLocaleTimeString() );
                } else {
                    openFlag = false;   // false 非表示
                }
            }
            
            if( souAdmiCnt != 0 || (timerFlag && openFlag) ) {
                subWinObj2 = window.open(url, 'test_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
            }
        }
    }
    
    /* サーバー側とデータを通信するための機能を持つAPIを取得します。 */
    function createXmlHttpRequest()
    {
        var xmlhttp=null;
        if(window.ActiveXObject) {
            try {
                xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch(e) {
                try {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e2) {
                    ;
                }
            }
        } else if(window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest();
        }
        return xmlhttp;
    }
    
    /* 総合届承認待ち件数 */
    var souAdmiCnt = 0;     // 総合届承認待ち件数
    function setAdmitCnt() {
        var xmlhttp=createXmlHttpRequest();
        if(xmlhttp!=null) {
            xmlhttp.open("POST", "./meeting_schedule_sougou_admit_output.php", false);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xmlhttp.send();
            souAdmiCnt = xmlhttp.responseText;
//alert("TEST " + souAdmiCnt);
        } else {
//alert("TEST xmlhttp = NULL");
        }
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
    
    /***** 開始時間のコピーメソッド *****/
    function strTimeCopy()
    {
        // フルでオブジェクトの指定
        document.apend_form.str_time.value = document.apend_form.str_hour.value + ':' + document.apend_form.str_minute.value;
    }
    
    /***** 終了時間のコピーメソッド *****/
    function endTimeCopy()
    {
        // フルでオブジェクトの指定
        document.apend_form.end_time.value = document.apend_form.end_hour.value + ':' + document.apend_form.end_minute.value;
    }
    
    /***** 主催者の名前のコピーメソッド *****/
    function sponsorNameCopy()
    {
        // フルでオブジェクトの指定
        document.apend_form.sponsor.value = document.apend_form.userID_name.value;
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
    
    /***** 会議場所のコピーメソッド *****/
    function roomCopy()
    {
        // フルでオブジェクトの指定   2005/11/12 以降は使用しない
        // document.apend_form.note.value = document.apend_form.room.value;
    }
    
    /***** 社用車のコピーメソッド *****/
    function carCopy()
    {
        // フルでオブジェクトの指定   2005/11/12 以降は使用しない
        // document.apend_form.note.value = document.apend_form.car.value;
    }
    
    /***** apend_form の入力チェックメソッド *****/
    function apend_formCheck(obj) {
        if (obj.subject.value.length == 0) {
            alert("会議(打合せ)の件名が入力されていません！");
            obj.subject.focus();
            // obj.subject.select();
            return false;
        }
        if (obj.subject.value.match(/��/)) {
            // /m(オプション)で複数行のマッチングを行うが上記でもOK
            alert("件名に機種依存文字の �� (1文字のカブ)が入っています。\n\n(株) (半角カッコとカブ)等に置換えて下さい。");
            obj.subject.focus();
            return false;
        }
        if (obj.subject.value.match(/[��-��]/)) {
            alert("件名に機種依存文字の �� 等が入っています。\n\n（１） (半角又は全角カッコと１)等に置換えて下さい。");
            obj.subject.focus();
            return false;
        }
        if (obj.str_time.value.length == 0) {
            alert("開始時間が入力されていません！");
            obj.str_hour.focus();
            // obj.str_hour.select();
            return false;
        }
        if (obj.end_time.value.length == 0) {
            alert("終了時間が入力されていません！");
            obj.end_hour.focus();
            // obj.end_hour.select();
            return false;
        }
        if (obj.str_time.value > obj.end_time.value) {
            alert("開始時間と終了時間が逆転しています！");
            obj.str_hour.focus();
            return false;
        } else if (obj.str_time.value == obj.end_time.value) {
            alert("開始時間と終了時間が同じです！");
            obj.str_hour.focus();
            return false;
        }
        if (obj.sponsor.value.length == 0) {
            alert("主催者が指定されていません！");
            obj.userID_name.focus();
            // obj.userID_name.select();
            return false;
        }
        if (obj.attenView.value.length == 0) {
            alert("出席者が指定されていません！");
            obj.elements['atten[]'].focus();
            return false;
        }
        if (obj.room_no.value.length == 0) {
            alert("場所が指定されていません！");
            obj.room.focus();
            return false;
        }
        return true;
    }
    
    /***** room_form の入力チェックメソッド(会議室の番号, 会議室名, 重複チェック) *****/
    function room_formCheck(obj) {
        // obj.room_no.value = obj.room_no.value.toUpperCase();
        if (obj.room_no.value.length == 0) {
            alert("会議室の番号がブランクです。");
            obj.room_no.focus();
            obj.room_no.select();
            return false;
        }
        if (!this.isDigit(obj.room_no.value)) {
            alert("会議室の番号は数字で入力して下さい。");
            obj.room_no.focus();
            obj.room_no.select();
            return false;
        }
        if (obj.room_no.value < 1 || obj.room_no.value > 32000) {
            alert("会議室の番号は１から３２０００までです！");
            obj.room_no.focus();
            obj.room_no.select();
            return false;
        }
        if (obj.room_name.value.length == 0) {
            alert("会議室名がブランクです。");
            obj.room_name.focus();
            obj.room_name.select();
            return false;
        }
        if (!obj.duplicate[0].checked && !obj.duplicate[1].checked) {
            alert("重複チェックの する／しない のどちらかをチェックして下さい。");
            obj.duplicate[0].focus();
            return false;
        }
        return true;
    }
    
    /***** car_form の入力チェックメソッド(社用車の番号, 社用車名, 重複チェック) *****/
    function car_formCheck(obj) {
        // obj.car_no.value = obj.car_no.value.toUpperCase();
        if (obj.car_no.value.length == 0) {
            alert("社用車の番号がブランクです。");
            obj.car_no.focus();
            obj.car_no.select();
            return false;
        }
        if (!this.isDigit(obj.car_no.value)) {
            alert("社用車の番号は数字で入力して下さい。");
            obj.car_no.focus();
            obj.car_no.select();
            return false;
        }
        if (obj.car_no.value < 1 || obj.car_no.value > 32000) {
            alert("社用車の番号は１から３２０００までです！");
            obj.car_no.focus();
            obj.car_no.select();
            return false;
        }
        if (obj.car_name.value.length == 0) {
            alert("社用車名がブランクです。");
            obj.car_name.focus();
            obj.car_name.select();
            return false;
        }
        if (!obj.car_dup[0].checked && !obj.car_dup[1].checked) {
            alert("重複チェックの する／しない のどちらかをチェックして下さい。");
            obj.car_dup[0].focus();
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
            alert("出席者が指定されていません！");
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
    
    /***** print_form の入力チェックメソッド(日付) *****/
    function print_formCheck(obj) {
        // obj.group_no2.value = obj.group_no2.value.toUpperCase();
        if (!obj.str_date.value.length) {
        	alert("日付の選択開始日が入力されていません。");
        	obj.str_date.focus();
        	return false;
    	}
    	if (!this.isDigit(obj.str_date.value)) {
        	alert("開始日付に数字以外のデータがあります。");
        	obj.str_date.focus();
        	obj.str_date.select();
        	return false;
    	}
    	if (obj.str_date.value.length != 8) {
        	alert("日付の開始日が８桁でありません。");
        	obj.str_date.focus();
        	return false;
    	}
    	if (!obj.end_date.value.length) {
        	alert("日付の選択終了日が選択されていません。");
        	obj.end_date.focus();
        	return false;
    	}
    	if (!this.isDigit(obj.end_date.value)) {
        	alert("終了日付に数字以外のデータがあります。");
        	obj.end_date.focus();
        	obj.end_date.select();
        	return false;
    	}
    	if (obj.end_date.value.length != 8) {
        	alert("日付の終了日が８桁でありません。");
        	obj.end_date.focus();
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
                window.external.AddFavorite(url + "?calUid=" + uid, "会議(打合せ)スケジュール");
            } else {
                window.external.AddFavorite(url, "会議(打合せ)スケジュール");
            }
        }
        return false;       // ←これは二重 実行の対策
    }
    
    /***** ControlForm の入力チェックをしてAjax実行 *****/
    function checkANDexecute(flg)
    {
        // confirm("お気に入りにアイコンを追加します。\n\n宜しいですか？");
            if (flg == 1) {
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 2){
                this.parameter = "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 3) {
                this.parameter = "&requireDate=yes"
                this.AjaxLoadTable("List", "showAjax");
            } else if (flg == 4){
                this.parameter = "&requireDate=yes"
                this.parameter += "&noMenu=yes";
                this.AjaxLoadTable("ListWin", "showAjax");
            } else if (flg == 5){
                this.parameter = "&noMenu=yes";
                this.AjaxLoadPITable("ListWin", "showAjax");
            } else if (flg == 6){       // 通達発効状況照会用
                this.parameter = "&noMenu=yes";
                this.AjaxLoadTable("NotiWin", "showAjax");
            } else if (flg == 7){       // 営繕状況照会用
                this.AjaxLoadTable("EizWin", "showAjax");
            } else if (flg == 8){       // 会議室予定表用
                this.AjaxLoadTable("RooWin", "showAjax");
            } else if (flg == 9){       // 総合届（承認待ち）
                this.AjaxLoadTable("SadWin", "showAjax");
            } else {
                this.AjaxLoadTable("List", "showAjax");
            }
            // 点滅のメッセージを変更する
            // this.blink_msg = "部品番号";
            // this.stop_blink();
        return false;   // 実際にsubmitはさせない
    }
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    function AjaxLoadTable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("meeting_schedule_absence_Main.php"+parm, 500, 400);
            return;
        }
        // 通達発効状況照会用
        if (showMenu == "NotiWin") {    // 別ウィンドウで表示
            this.win_open("notification.php"+parm, 1100, 600);
            return;
        }
        // 営繕状況照会用
        if (showMenu == "EizWin") {    // 別ウィンドウで表示
            this.win_open("notification_eizen.php"+parm, 1200, 600);
            return;
        }
        // 会議室予定表用
        if (showMenu == "RooWin") {    // 別ウィンドウで表示
            this.win_open("meeting_schedule_room.php"+parm+"&year="+year+"&month="+month+"&day="+day, 1000, 650);
            return;
        }
        // 総合届（承認待ち）
        if (showMenu == "SadWin") {    // 別ウィンドウで表示
            this.win_open2("meeting_schedule_sougou_admit_list.php"+parm, 270, 250);
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
            xmlhttp.open("GET", "meeting_schedule_absence_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    /***** 画面更新をユーザーに違和感無く表示させるAjax用リロードメソッド *****/
    // onReadyStateChangeイベントを使って処理が完了していない場合のWaitMessageを出力。
    // parameter : ListTable=結果表示, WaitMsg=処理中です。お待ち下さい。
    function AjaxLoadPITable(showMenu, location)
    {
        if (!location) location = "showAjax";   // Default値の設定
        var parm = "?";
        parm += "showMenu=" + showMenu  // iframeのみ抽出
        parm += this.parameter;
        if (showMenu == "ListWin") {    // 別ウィンドウで表示
            this.win_open("meeting_schedule_pi_Main.php"+parm, 1000, 600);
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
            xmlhttp.open("GET", "meeting_schedule_pi_Main.php"+parm);
            xmlhttp.send(null);
        } catch (e) {
            alert(url + "\n\nをオープン出来ません！\n\n" + e);
        }
    }
    
    // 会議室予定表へ渡す年月日をセット
    var year = month = day = 0; 
    function setSelectDate(y,m,d)
    {
        year = y; month = m; day = d;
    }

    // ドロップダウンリストで選択した年月日をセット
    function setDdlistDate()
    {
        year = document.getElementById('id_year').value;
        month = document.getElementById('id_month').value;
        day = document.getElementById('id_day').value;

        if( ! isDate(year+month+day) ) { // 存在しない日付の場合、当月の最終日をセット
            if( month == 2 ) { // 2月のみ日付がズレる為、意図的に 3/1 をセット
                month = 3;
                day = 1;
            }
            setBeforDate();
            document.getElementById('id_year').value = ('0'+year).slice(-2);
            document.getElementById('id_month').value = ('0'+month).slice(-2);
            document.getElementById('id_day').value = ('0'+day).slice(-2);
        }
        viewWeek(); // 曜日を表示
    }

    // 存在する年月日ですか？
    function isDate( str )
    {
        var arr = (str.substr(0, 4) + '/' + str.substr(4, 2) + '/' + str.substr(6, 2)).split('/');
        
        if (arr.length !== 3) return false;
        var date = new Date(arr[0], arr[1] - 1, arr[2]);
        if (arr[0] !== String(date.getFullYear()) || arr[1] !== ('0' + (date.getMonth() + 1)).slice(-2) || arr[2] !== ('0' + date.getDate()).slice(-2)) {
            return false;
        } else {
            return true;
        }
    }

    // 前の日をセット
    function setBeforDate()
    {
        var dt = new Date(year, month-1, day);
        dt.setDate(dt.getDate() - 1);

        year = dt.getFullYear();
        month = dt.getMonth()+1;
        day = dt.getDate();
    }

    // 次の日をセット
    function setNextDate()
    {
        var dt = new Date(year, month-1, day);
        dt.setDate(dt.getDate() + 1);

        year = dt.getFullYear();
        month = dt.getMonth()+1;
        day = dt.getDate();
    }

    // 曜日を表示
    function viewWeek()
    {
        var hiduke = new Date(year, month-1, day);
        var week = hiduke.getDay();
        var yobi = new Array(" (日)"," (月)"," (火)"," (水)"," (木)"," (金)"," (土)");
        var obj = document.getElementById('id_week');
        if( week == 0 ) {
            obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
        } else if( week == 6 ) {
            obj.innerHTML = "<span style='color: blue;'>" + yobi[week] + "</span>";
        } else if( isHoliday(year, month, day) ) {
            obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
        } else {
            obj.innerHTML = "<span style='color: black;'>" + yobi[week] + "</span>";
        }
    }

    // 会社の休日情報をセットしておく。
    var holiday = "";
    function setHoliday(day)
    {
        holiday = day;
    }
    
    // 休日ですか？
    function isHoliday(y,m,d)
    {
        var date = ('0'+y).slice(-4)+'-'+('0'+m).slice(-2)+'-'+('0'+d).slice(-2);

        if( holiday.search(date) != -1 ) {
            return true;
        } else {
            return false;
        }
    }

    // 会議入力へ渡す変数をセット
//    function setApendData(hu, mi, room_no)
    function setApendData(s_hour,s_minute,e_hour,e_minute,room_no)
    {
/*
        var s_time = s_hour = s_minute = e_time = e_hour = e_minute = 0;

        if( mi < 30 ) {
            s_hour = ('0'+hu).slice(-2); s_minute = "00";
            e_hour = ('0'+hu).slice(-2); e_minute = "30";
        } else {
            s_hour = ('0'+hu).slice(-2); s_minute = "30";
            hu++;
            e_hour = ('0'+hu).slice(-2); e_minute = "00";
        }
*/
        s_hour = ('0'+s_hour).slice(-2); s_minute = ('0'+s_minute).slice(-2);
        e_hour = ('0'+e_hour).slice(-2); e_minute = ('0'+e_minute).slice(-2);
/**
        s_time = s_hour + ":" + s_minute;
        e_time = e_hour + ":" + e_minute;
alert("\n\nTEST\n\n" + s_time + '-' + e_time + '/' + room_no);
/**/

//        document.getElementById('id_str_time').value = s_time;
        document.getElementById('id_str_hour').value = s_hour;
        document.getElementById('id_str_minute').value = s_minute;
//        document.getElementById('id_end_time').value = e_time;
        document.getElementById('id_end_hour').value = e_hour;
        document.getElementById('id_end_minute').value = e_minute;
        document.getElementById('id_room_no').value = room_no;
    }

    // 自動更新（リロード）設定
    function reload() 
    {
        setInterval('document.reload_form.submit()', 300000);   // ５分 (1000 = 1秒)
    }
    
/*
}   // class meeting_schedule END  */


///// インスタンスの生成
var MeetingSchedule = new meeting_schedule();
// blink_disp()メソッド内で使用するグローバル変数のセット
var blink_flag = 1;


