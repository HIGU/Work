//////////////////////////////////////////////////////////////////////////////
// 納期遅れの部品照会 条件選択フォーム JavaScriptによる入力チェック         //
// Copyright(C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// 変更経歴                                                                 //
// 2011/11/04 新規作成 delivery_late_form.js                                //
//////////////////////////////////////////////////////////////////////////////

/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ("0">c || c>"9") {
            return false;
        }
    }
    return true;
}

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1個目かチェック
                cnt_dot++;
            } else {
                return false;       // 2個目は false
            }
        } else {
            if ("0">c || c>"9") {
                return false;
            }
        }
    }
    return true;
}

/***** フォームの入力チェック function *****/
function chk_payable_form(obj) {
    /* 部品番号の入力チェック & 大文字変換 */
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert("部品番号の桁数は９桁です。");
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    }
    
    /* 発注先の入力チェック & 大文字変換 */
    if ( (obj.vendor.value.length < 5) && (obj.vendor.value.length > 0) ) {
        if (!isDigit(obj.vendor.value)) {
            alert("発注先コードに数字以外のデータがあります。");
            obj.vendor.focus();
            obj.vendor.select();
            return false;
        }
        switch (obj.vendor.value.length) {
        case 1:
            obj.vendor.value = ('0000' + obj.vendor.value);
            break;
        case 2:
            obj.vendor.value = ('000' + obj.vendor.value);
            break;
        case 3:
            obj.vendor.value = ('00' + obj.vendor.value);
            break;
        case 4:
            obj.vendor.value = ('0' + obj.vendor.value);
            break;
        }
    }
    
    /* 日付の入力内容をチェック */
    if (!obj.str_date.value.length) {
        alert("日付の選択開始日が入力されていません。");
        obj.str_date.focus();
        return false;
    }
    if (!isDigit(obj.str_date.value)) {
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
    if (!isDigit(obj.end_date.value)) {
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
    
    /* １頁の表示行数 入力チェック */
    if ( !isDigit(obj.paya_page.value) ) {
        alert('ページ数に数字以外の文字があります！');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    } else if (obj.paya_page.value < 1) {
        alert('ページ数は１以上です！');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    } else if (obj.paya_page.value > 999) {
        alert('ページ数は９９９までです！');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    }
    return true;
}

