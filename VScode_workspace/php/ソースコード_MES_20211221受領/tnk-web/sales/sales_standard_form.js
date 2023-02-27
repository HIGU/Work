//////////////////////////////////////////////////////////////////////////////
// 売上 特注カプラ専用フォームのチェックルーチン JavaScript                 //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/02 Created  sales_custom_form.js → sales_standard_form.js       //
//            特注用を標準のカプラ・リニアにカスタマイズ                    //
// 2006/08/29 条件1〜3までの上限値・下限値の入力有り無しチェックを追加      //
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

/* 日付の入力内容をチェック */
function chk_select_form(obj) {
    if (!obj.d_start.value.length) {
        alert("日付の選択開始日が入力されていません。");
        obj.d_start.focus();
        return false;
    }
    if (!isDigit(obj.d_start.value)) {
        alert("開始日付に数字以外のデータがあります。");
        obj.d_start.focus();
        obj.d_start.select();
        return false;
    }
    if (obj.d_start.value.length != 8) {
        alert("日付の開始日が８桁でありません。");
        obj.d_start.focus();
        return false;
    }
    if (!obj.d_end.value.length) {
        alert("日付の選択終了日が選択されていません。");
        obj.d_end.focus();
        return false;
    }
    if (!isDigit(obj.d_end.value)) {
        alert("終了日付に数字以外のデータがあります。");
        obj.d_end.focus();
        obj.d_end.select();
        return false;
    }
    if (obj.d_end.value.length != 8) {
        alert("日付の終了日が８桁でありません。");
        obj.d_end.focus();
        return false;
    }
    if (obj.d_end.value < obj.d_start.value) {
        alert('開始日と終了日が逆転しています。');
        obj.d_start.focus();
        return false;
    }
    obj.assy_no.value = obj.assy_no.value.toUpperCase();
    if (obj.assy_no.value.length != 0) {
        if (obj.assy_no.value.length != 9) {
            alert("製品番号の桁数は９桁です。");
            obj.assy_no.focus();
            obj.assy_no.select();
            return false;
        }
    }
    if (!obj.lower_uri_ritu.value) {
        alert("条件１の下限値が入力されていません。");
        obj.lower_uri_ritu.focus();
        obj.lower_uri_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.lower_uri_ritu.value)) {
        alert("条件１の下限値に 数字及び少数点 以外の文字があります。");
        obj.lower_uri_ritu.focus();
        obj.lower_uri_ritu.select();
        return false;
    }
    if (!obj.upper_uri_ritu.value) {
        alert("条件１の上限値が入力されていません。");
        obj.upper_uri_ritu.focus();
        obj.upper_uri_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.upper_uri_ritu.value)) {
        alert("条件１の上限値に 数字及び少数点 以外の文字があります。");
        obj.upper_uri_ritu.focus();
        obj.upper_uri_ritu.select();
        return false;
    }
    var upper_uri = parseFloat(obj.upper_uri_ritu.value);   // これを入れないと文字列の比較になるため
    var lower_uri = parseFloat(obj.lower_uri_ritu.value);
    if (upper_uri < lower_uri) {
        alert("条件１の上限値が下限値より小さいです。");
        obj.lower_uri_ritu.focus();
        obj.lower_uri_ritu.select();
        return false;
    }
    if (!obj.lower_mate_ritu.value) {
        alert("条件２の下限値が入力されていません。");
        obj.lower_mate_ritu.focus();
        obj.lower_mate_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.lower_mate_ritu.value)) {
        alert("条件２の下限値に 数字及び少数点 以外の文字があります。");
        obj.lower_mate_ritu.focus();
        obj.lower_mate_ritu.select();
        return false;
    }
    if (!obj.upper_mate_ritu.value) {
        alert("条件２の上限値が入力されていません。");
        obj.upper_mate_ritu.focus();
        obj.upper_mate_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.upper_mate_ritu.value)) {
        alert("条件２の上限値に 数字及び少数点 以外の文字があります。");
        obj.upper_mate_ritu.focus();
        obj.upper_mate_ritu.select();
        return false;
    }
    var upper_mate = parseFloat(obj.upper_mate_ritu.value);
    var lower_mate = parseFloat(obj.lower_mate_ritu.value);
    if (upper_mate < lower_mate) {
        alert("条件２の上限値が下限値より小さいです。");
        obj.lower_mate_ritu.focus();
        obj.lower_mate_ritu.select();
        return false;
    }
    if (!obj.lower_equal_ritu.value) {
        alert("条件３の下限値が入力されていません。");
        obj.lower_equal_ritu.focus();
        obj.lower_equal_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.lower_equal_ritu.value)) {
        alert("条件３の下限値に 数字及び少数点 以外の文字があります。");
        obj.lower_equal_ritu.focus();
        obj.lower_equal_ritu.select();
        return false;
    }
    if (!obj.upper_equal_ritu.value) {
        alert("条件３の上限値が入力されていません。");
        obj.upper_equal_ritu.focus();
        obj.upper_equal_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.upper_equal_ritu.value)) {
        alert("条件３の上限値に 数字及び少数点 以外の文字があります。");
        obj.upper_equal_ritu.focus();
        obj.upper_equal_ritu.select();
        return false;
    }
    var upper_equal = (obj.upper_equal_ritu.value - 0);     // parseFloat(obj.upper_equal_ritu.value)
    var lower_equal = (obj.lower_equal_ritu.value - 0);     // 上記と同じ意味
    if ( upper_equal < lower_equal) {
        alert("条件３の上限値が下限値より小さいです。");
        obj.lower_equal_ritu.focus();
        obj.lower_equal_ritu.select();
        return false;
    }
    if ( !isDigit(obj.sales_page.value) ) {
        alert('ページ数に数字以外の文字があります！');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    } else if (obj.sales_page.value < 1) {
        alert('ページ数は１以上です！');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    } else if (obj.sales_page.value > 9999) {
        alert('ページ数は９９９９までです！');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    }
    return true;
}

