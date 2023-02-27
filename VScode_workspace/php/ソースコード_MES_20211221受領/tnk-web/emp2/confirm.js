//////////////////////////////////////////////////////////////////////////////
// ＴＮＫ社員メニュー用 入力フォーム等のチェック用 JavaScript               //
// Copyright (C) 2001-2019      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created   confirm.php                                         //
// 2002/08/07 obj.img_file.value=obj.photo.value; に注意、今後はPHPでできる //
// 2007/10/15 フォームのチェック用スクリプトだが win_open function を追加   //
// 2014/07/29 不在者照会では入力チェックをしないよう変更               大谷 //
// 2015/07/30 年齢順では入力チェックをしない様変更                     大谷 //
// 2019/09/17 有給管理台帳の年度チェックを追加                         大谷 //
//////////////////////////////////////////////////////////////////////////////
/* 入力文字が数字かどうかチェック */
function isDigit(str){
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c=str.charAt(i);
        if(("0" > c) || (c > "9")) {
            return false;
        }
    }
    return true;
}
/* 入力情報のチェック */
function inpConf(obj) {
    if (!obj.userid.value.length) {
        alert("[社員No]の入力欄が空白です。ログインできません。");
        obj.userid.focus();
        return false;
    }
    if (obj.userid.value.length!=6) {
        alert("[社員No]の入力値が不正です。ログインできません。");
        obj.userid.focus();
        return false;
    }
    if (!obj.passwd.value.length) {
        alert("[パスワード]の入力欄が空白です。ログインできません。");
        obj.passwd.focus();
        return false;
    }
    return true;
}
/* 電話番号をチェック */
function isTelnum(str) {
    var count = 0;
    var msg = "";
    for (i=0; i<str.length; i++) {
        var c = str.charAt(i);
        if ( ('0' <= c) && (c <= '9') ) {
            count++;
            continue;
        } else {
            if(c == '-') {
                continue;
            }
            msg="電話番号に使用できる文字は、\n"+
                "半角数字ととハイフン(-)だけです。";
            alert(msg);
            return false;
        }
    }
    if (count < 10) {
        msg="電話番号の桁数が10桁未満です。\n"+
            "市外局番から入力してください。";
        alert(msg);
        return false;
    }
    return true;
}
/* 入力列内に指定文字は存在するかをチェック */
function isInchar(str,substr) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == substr)
            return i+1;
    }
    return 0;
}
/* 検索項目のチェック */
function chkLookupTermsY(obj) {
    if (obj.lookupkeykind.selectedIndex != 0) {
        if (obj.lookupkeykind.selectedIndex != 5 && obj.lookupkeykind.selectedIndex != 6) {
            if (!obj.lookupkey.value.length) {
                alert("基本情報の検索キーの入力欄が空白です。検索は行えません。");
                obj.lookupkey.focus();
                return false;
            }
            if (obj.lookupkeykind.selectedIndex == 2) {
                /* フルネームによる検索時はスペースが必要 */
                if ( !(isInchar(obj.lookupkey.value," ") || isInchar(obj.lookupkey.value,"　")) ){
                    alert("フルネームによる検索時は姓と名がスペースで区切られていなければなりません。");
                    obj.lookupkey.focus();
                    obj.lookupkey.select();
                    return false;   
                }
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for(i=0; i<len; i++) {
                    c = str.charAt(i);
                    if(c == "　") {
                        c = " ";
                    }
                    substr += c;
                }
                obj.lookupkey.value = substr;
            } else {
                /* スペースを取り除く */
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for (i=0; i<len; i++) {
                    c = str.charAt(i);
                    if((c == " ") || (c == "　")) {
                        obj.lookupkey.value = substr;
                        break;
                    }
                    substr += c;
                }
            }
        }
    }
    if (obj.lookupyukyukind.selectedIndex != 0) {
        if (!obj.lookupyukyu.value.length) {
            alert("有給情報の検索キーの入力欄が空白です。日数を入力してください。");
            obj.lookupyukyu.focus();
            return false;
        }
        if (!isDigit(obj.lookupyukyu.value)) {
            alert("検索キーには半角数字を指定してください。");
            obj.lookupyukyu.focus();
            obj.lookupyukyu.select();
            return false;
        }
    }
    if (obj.lookupyukyufive.selectedIndex != 0) {
        if (!obj.lookupyukyuf.value.length) {
            alert("有給5日情報の検索キーの入力欄が空白です。日数を入力してください。");
            obj.lookupyukyuf.focus();
            return false;
        }
        if (!isDigit(obj.lookupyukyuf.value)) {
            alert("検索キーには半角数字を指定してください。");
            obj.lookupyukyuf.focus();
            obj.lookupyukyuf.select();
            return false;
        }
    }
    return true;
}

/* 検索項目のチェック */
function chkLookupTerms(obj) {
    if (obj.lookupkeykind.selectedIndex != 0) {
        if (obj.lookupkeykind.selectedIndex != 5 && obj.lookupkeykind.selectedIndex != 6) {
            if (!obj.lookupkey.value.length) {
                alert("検索対象の入力欄が空白です。検索は行えません。");
                obj.lookupkey.focus();
                return false;
            }
            if (obj.lookupkeykind.selectedIndex == 2) {
                /* フルネームによる検索時はスペースが必要 */
                if ( !(isInchar(obj.lookupkey.value," ") || isInchar(obj.lookupkey.value,"　")) ){
                    alert("フルネームによる検索時は姓と名がスペースで区切られていなければなりません。");
                    obj.lookupkey.focus();
                    obj.lookupkey.select();
                    return false;   
                }
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for(i=0; i<len; i++) {
                    c = str.charAt(i);
                    if(c == "　") {
                        c = " ";
                    }
                    substr += c;
                }
                obj.lookupkey.value = substr;
            } else {
                /* スペースを取り除く */
                var str    = obj.lookupkey.value;
                var len    = str.length;
                var substr = "";
                var c;
                for (i=0; i<len; i++) {
                    c = str.charAt(i);
                    if((c == " ") || (c == "　")) {
                        obj.lookupkey.value = substr;
                        break;
                    }
                    substr += c;
                }
            }
        }
    }
    return true;
}

/* 入力されたパスワードのチェック */
function chkPasswd(obj) {
    if (!obj.passwd.value.length) {
        alert("[新しいパスワード]の入力欄が空白です。");
        obj.passwd.focus();
        return false;
    }
    if (!obj.repasswd.value.length) {
        alert("[パスワード確認]の入力欄が空白です。");
        obj.repasswd.focus();
        return false;
    }
    if (obj.passwd.value!=obj.repasswd.value) {
        alert("パスワードが一致しません。");
        obj.repasswd.focus();
        obj.repasswd.select();
        return false;
    }
    return true;
}
/* DB操作入力内容をチェック */
function chkUserQuery(obj) {
    if (!obj.userquery.value.length) {
        alert("ユーザークエリーが入力されていません。");
        obj.userquery.focus();
        return false;
    }
    return true;
}
/* 新規ユーザーの入力内容をチェック */
function chkUserInfo(obj) {
    if (!obj.userid.value.length) {
        alert("[社員No]の入力欄が空白です。正しい入力を行ってください。");
        obj.userid.focus();
        return false;
    }
    if (obj.userid.value.length!=6) {
        alert("[社員No]の入力値が不正です。正しい入力を行ってください。");
        obj.userid.focus();
        obj.userid.select();
        return false;
    }
    if (!obj.name_1.value.length) {
        alert("[氏名]の入力欄が空白です。正しい入力を行ってください。");
        obj.name_1.focus();
        return false;
    }
    if (!obj.name_2.value.length) {
        alert("[氏名]の入力欄が空白です。正しい入力を行ってください。");
        obj.name_2.focus();
        return false;
    }
    if (!obj.kana_1.value.length) {
        alert("[フリガナ]の入力欄が空白です。正しい入力を行ってください。");
        obj.kana_1.focus();
        return false;
    }
    if (!obj.kana_2.value.length) {
        alert("[フリガナ]の入力欄が空白です。正しい入力を行ってください。");
        obj.kana_2.focus();
        return false;
    }
    if (!obj.spell_2.value.length) {
        alert("[スペル]の入力欄が空白です。正しい入力を行ってください。");
        obj.spell_2.focus();
        return false;
    }
    if (obj.spell_2.value.match(/[^a-z]/g)) {
        alert("[スペル]の入力値が不正です。半角小文字で入力を行ってください。");
        obj.spell_2.focus();
        return false;
    }
    if (!obj.spell_1.value.length) {
        alert("[スペル]の入力欄が空白です。正しい入力を行ってください。");
        obj.spell_1.focus();
        return false;
    }
    if (obj.spell_1.value.match(/[^a-z]/g)) {
        alert("[スペル]の入力値が不正です。半角小文字で入力を行ってください。");
        obj.spell_1.focus();
        return false;
    }
    if ( (!obj.zipcode_1.value.length) || (obj.zipcode_1.value.length != 3) ) {
        alert("[郵便番号]の入力欄が空白、又は文字数が不正です。正しい入力を行ってください。");
        obj.zipcode_1.focus();
        obj.zipcode_1.select();
        return false;
    }
    if ( (!obj.zipcode_2.value.length) || (obj.zipcode_2.value.length != 4) ) {
        alert("[郵便番号]の入力欄が空白、又は文字数が不正です。正しい入力を行ってください。");
        obj.zipcode_2.focus();
        obj.zipcode_2.select();
        return false;
    }
    if (!isDigit(obj.zipcode_1.value)) {
        alert("[郵便番号]には整数値を指定してください。");
        obj.zipcode_1.focus();
        obj.zipcode_1.select();
        return false;
    }
    if (!isDigit(obj.zipcode_2.value)) {
        alert("[郵便番号]には整数値を指定してください。");
        obj.zipcode_2.focus();
        obj.zipcode_2.select();
        return false;
    }
    /* 変更個所 2001/11/29 ここから */
    if (!obj.address.value.length) {
        alert("[住所]の入力欄が空白です。正しい入力を行ってください。");
        obj.address.focus();
        return false;
    }
    if (obj.address.value.length > 64) {
        alert("[住所]入力欄の文字数が不正です。正しい入力を行ってください。");
        obj.address.focus();
        obj.address.select();
        return false;
    }
    /* ここまで */
    if (!obj.tel.value.length) {
        alert("[電話番号]の入力欄が空白です。正しい入力を行ってください。");
        obj.tel.focus();
        return false;
    } 
    if (!isTelnum(obj.tel.value)) {
        obj.tel.focus();
        obj.tel.select();
        return false;   
    }
    if (!isInchar(obj.tel.value,"-")) {
        alert("電話番号は局番の間に\"-\"を入れてください。");
        obj.tel.focus();
        obj.tel.select();
        return false;   
    }
    if (!obj.birthday_1.value.length) {
        alert("[生年月日]の入力欄が空白です。正しい入力を行ってください。");
        obj.birthday_1.focus();
        obj.birthday_1.select();
        return false;
    }
    if (!obj.birthday_2.value.length) {
        alert("[生年月日]の入力欄が空白です。正しい入力を行ってください。");
        obj.birthday_2.focus();
        obj.birthday_2.select();
        return false;
    }
    if (!obj.birthday_3.value.length) {
        alert("[生年月日]の入力欄が空白です。正しい入力を行ってください。");
        obj.birthday_3.focus();
        obj.birthday_3.select();
        return false;
    }
    if (!isDigit(obj.birthday_1.value)) {
        alert("[生年月日]には整数値を指定してください。");
        obj.birthday_1.focus();
        obj.birthday_1.select();
        return false;
    }
    if (!isDigit(obj.birthday_2.value)) {
        alert("[生年月日]には整数値を指定してください。");
        obj.birthday_2.focus();
        obj.birthday_2.select();
        return false;
    }
    if (!isDigit(obj.birthday_3.value)) {
        alert("[生年月日]には整数値を指定してください。");
        obj.birthday_3.focus();
        obj.birthday_3.select();
        return false;
    }
    if (obj.birthday_1.value < 1900) {
        alert("[生年月日]の入力値が不正です。");
        obj.birthday_1.focus();
        obj.birthday_1.select();
        return false;
    }
    if ( (obj.birthday_2.value == 0) || (obj.birthday_2.value > 12) ){
        alert("[生年月日]の入力値が不正です。");
        obj.birthday_2.focus();
        obj.birthday_2.select();
        return false;
    }
    if( (obj.birthday_3.value == 0) || (obj.birthday_3.value > 31) ){
        alert("[生年月日]の入力値が不正です。");
        obj.birthday_3.focus();
        obj.birthday_3.select();
        return false;
    }
    if (!obj.entrydate_1.value.length) {
        alert("[入社年月日]の入力欄が空白です。正しい入力を行ってください。");
        obj.entrydate_1.focus();
        return false;
    }
    if (!obj.entrydate_2.value.length) {
        alert("[入社年月日]の入力欄が空白です。正しい入力を行ってください。");
        obj.entrydate_2.focus();
        return false;
    }
    if (!obj.entrydate_3.value.length) {
        alert("[入社年月日]の入力欄が空白です。正しい入力を行ってください。");
        obj.entrydate_3.focus();
        return false;
    }
    if (!isDigit(obj.entrydate_1.value)) {
        alert("[入社年月日]には整数値を指定してください。");
        obj.entrydate_1.focus();
        obj.entrydate_1.select();
        return false;
    }
    if (!isDigit(obj.entrydate_2.value)) {
        alert("[入社年月日]には整数値を指定してください。");
        obj.entrydate_2.focus();
        obj.entrydate_2.select();
        return false;
    }
    if (!isDigit(obj.entrydate_3.value)) {
        alert("[入社年月日]には整数値を指定してください。");
        obj.entrydate_3.focus();
        obj.entrydate_3.select();
        return false;
    }
    if (obj.entrydate_1.value < 1900) {
        alert("[入社年月日]の入力値が不正です。");
        obj.entrydate_1.focus();
        obj.entrydate_1.select();
        return false;
    }
    if ( (obj.entrydate_2.value == 0) || (obj.entrydate_2.value > 12) ){
        alert("[入社年月日]の入力値が不正です。");
        obj.entrydate_2.focus();
        obj.entrydate_2.select();
        return false;
    }
    if ( (obj.entrydate_3.value == 0) || (obj.entrydate_3.value > 31) ){
        alert("[入社年月日]の入力値が不正です。");
        obj.entrydate_3.focus();
        obj.entrydate_3.select();
        return false;
    }
    obj.img_file.value = obj.photo.value;
    return true;
}
/* 入力情報（資格登録・教育登録）のチェック */
function chkData(obj) {
    if (!isDigit(obj.begin_date_1.value)) {
        alert("年月日には整数値を指定してください。");
        obj.begin_date_1.focus();
        obj.begin_date_1.select();
        return false;
    }
    if (!isDigit(obj.begin_date_2.value)) {
        alert("年月日には整数値を指定してください。");
        obj.begin_date_2.focus();
        obj.begin_date_2.select();
        return false;
    }
    if (!isDigit(obj.begin_date_3.value)) {
        alert("年月日には整数値を指定してください。");
        obj.begin_date_3.focus();
        obj.begin_date_3.select();
        return false;
    }
    if (!isDigit(obj.end_date_1.value)) {
        alert("年月日には整数値を指定してください。");
        obj.begin_date_1.focus();
        obj.begin_date_1.select();
        return false;
    }
    if (!isDigit(obj.end_date_2.value)) {
        alert("年月日には整数値を指定してください。");
        obj.end_date_2.focus();
        obj.end_date_2.select();
        return false;
    }
    if (!isDigit(obj.end_date_3.value)) {
        alert("年月日には整数値を指定してください。");
        obj.end_date_3.focus();
        obj.end_date_3.select();
        return false;
    }
    if (!isDigit(obj.entry_num.value)) {
        alert("登録人数には整数値を指定してください。");
        obj.entry_num.focus();
        obj.entry_num.select();
        return false;
    }
    return true;
}

/* 検索項目のチェック */
function chkLookupFive(obj) {
    if (!obj.yukyulist.value.length) {
        alert("有給管理台帳の年度欄が空白です。検索は行えません。");
        obj.yukyulist.focus();
        return false;
    }
    if (!isDigit(obj.yukyulist.value)) {
        alert("年度には半角数字を指定してください。");
        obj.yukyulist.focus();
        obj.yukyulist.select();
        return false;
    }
    if (obj.yukyulist.value.length!=4) {
        alert("年度は4桁で指定してください。");
        obj.yukyulist.focus();
        obj.yukyulist.select();
        return false;
    }
    return true;
}

/***** ウィンドウのオープン関数  *****/
function win_open(url, w, h) {
    if (!w) w = 800;     // 初期値
    if (!h) h = 600;     // 初期値
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    w -= 10; h -= 30;   // 微調整が必要
    window.open(url, '', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,resizeble=yes,top='+top+',left='+left);
}
