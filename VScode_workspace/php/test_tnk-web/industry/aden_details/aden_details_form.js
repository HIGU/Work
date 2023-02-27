//////////////////////////////////////////////////////////////////////////////
// A伝状況の照会 条件選択フォーム JavaScriptによる入力チェック              //
// Copyright(C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// 変更経歴                                                                 //
// 2016/03/25 新規作成 aden_details_form.js                                 //
// 2017/06/14 存在しない発注先番号の取得でエラーのため削除                  //
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
    if (obj.str_date.value > obj.end_date.value) {
        alert("日付の範囲を正しく入力してください。");
        obj.str_date.focus();
        return false;
    }
    /* LT差の入力内容をチェック */
    if (!obj.lt_str_date.value.length) {
        if (!obj.lt_end_date.value.length) {
        } else {
            alert("L/T差が片方しか入力されていません。");
            obj.lt_str_date.focus();
            return false;
        }
    } else if (!obj.lt_end_date.value.length) {
        alert("L/T差が片方しか入力されていません。");
        obj.lt_str_date.focus();
        return false;
    }
    if (!isDigit(obj.lt_str_date.value)) {
        alert("L/T差に数字以外のデータがあります。");
        obj.lt_str_date.focus();
        obj.lt_str_date.select();
        return false;
    }
    if (!isDigit(obj.lt_end_date.value)) {
        alert("L/T差に数字以外のデータがあります。");
        obj.lt_end_date.focus();
        obj.lt_end_date.select();
        return false;
    }
    if (obj.lt_str_date.value > obj.lt_end_date.value) {
        alert("L/T差の範囲を正しく入力してください。");
        obj.lt_str_date.focus();
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
    } else if (obj.paya_page.value > 9999) {
        alert('ページ数は９９９９までです！');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    }
    return true;
}

