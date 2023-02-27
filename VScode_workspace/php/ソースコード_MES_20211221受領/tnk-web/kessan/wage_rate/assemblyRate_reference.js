//////////////////////////////////////////////////////////////////////////////
// 組立賃率   照会画面 入力チェック JavaScript                              //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/14 Created assemblyRate_reference.js                             //
//////////////////////////////////////////////////////////////////////////////

/* 入力文字が数字かどうかチェック(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if("0">c||c>"9") {
            return ture;
        }
    }
    return false;
}

/* 入力年月が正しい値かチェック */
function ismonth(str) {
    var month = str.slice(4);
    if ((month < 1) || (12 < month)) {
        return false;
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
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* 決算処理の開始年月の計算 */
function start_ym() {
    var str = document.getElementById('end_ym').value;
    var len = str.length;
    if (len == 6) { 
        var month = str.slice(4, 6);
        var year  = str.slice(0, 4)
        if ((month < 10) && (3 < month)) {
            var str_month = '04';
            var start_ym = year + str_month;
            document.kessan_form.str_ym.value = start_ym;
        } else if ( 9 < month) {
            var str_month = '10';
            var start_ym = year + str_month;
            document.kessan_form.str_ym.value = start_ym;
        } else {
            var str_year = year - 1;
            var str_month = '10';
            var start_ym = str_year + str_month;
            document.kessan_form.str_ym.value = start_ym;
        }
    }
}

/* 単月入力年月データのチェック */
function ym_chk_tangetu(obj) {
        var str = obj.tan_str_ym.value;
        var end = obj.tan_end_ym.value;
        
        if(str > end){
            alert("終了年月は開始年月以降の年月を入力して下さい。");
            obj.tan_end_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if(ismonth(obj.tan_str_ym.value)){
        } else {
            alert("正しい開始年月を入力してください。");
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if(!obj.tan_str_ym.value.length){
            alert("開始年月が入力されていません。");
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        } else if(isDigit(obj.tan_str_ym.value)){
            alert("開始年月には数値以外の文字は入力出来ません｡");
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if (obj.tan_str_ym.value.length == 6) {
        } else {
            alert('開始年月はYYYYMMの６桁で入力してください。');
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if(ismonth(obj.tan_end_ym.value)){
        } else {
            alert("正しい終了年月を入力してください。");
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        }
        if(!obj.tan_end_ym.value.length){
            alert("終了年月が入力されていません。");
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        } else if(isDigit(obj.tan_end_ym.value)){
            alert("終了年月には数値以外の文字は入力出来ません｡");
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        }
        if (obj.tan_end_ym.value.length == 6) {
        } else {
            alert('終了年月はYYYYMMの６桁で入力してください。');
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        }
        document.tangetu_form.tangetu.value = "自由計算";
        document.tangetu_form.submit();
        return true;
}

/* 決算入力年月データのチェック */
function ym_chk_kessan(obj) {
        var str = obj.str_ym.value;
        var end = obj.end_ym.value;
        
        if(str > end){
            alert("終了年月は開始年月以降の年月を入力して下さい。");
            obj.end_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if(ismonth(obj.str_ym.value)){
        } else {
            alert("正しい開始年月を入力してください。");
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if(!obj.str_ym.value.length){
            alert("開始年月が入力されていません。");
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        } else if(isDigit(obj.str_ym.value)){
            alert("開始年月には数値以外の文字は入力出来ません｡");
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if (obj.str_ym.value.length == 6) {
        } else {
            alert('開始年月はYYYYMMの６桁で入力してください。');
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if(ismonth(obj.end_ym.value)){
        } else {
            alert("正しい終了年月を入力してください。");
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        }
        if(!obj.end_ym.value.length){
            alert("終了年月が入力されていません。");
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        } else if(isDigit(obj.end_ym.value)){
            alert("終了年月には数値以外の文字は入力出来ません｡");
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        }
        if (obj.end_ym.value.length == 6) {
        } else {
            alert('終了年月はYYYYMMの６桁で入力してください。');
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        }
        return true;
}

/* 作業者入力データのチェック */
function chk_entry(obj) {
    if(!obj.worker_rate_p.value.length){
        alert("作業者賃率(パート)が入力されていません。");
        obj.worker_rate_p.focus();
        obj.worker_rate_p.select();
        return false;
    }
    if(!isDigitDot(obj.worker_rate_p.value)){
        alert("作業者賃率(パート)には数値以外の文字は入力出来ません｡");
        obj.worker_rate_p.focus();
        obj.worker_rate_p.select();
        return false;
    }
    
    if(!obj.worker_rate_s.value.length){
        alert("作業者賃率(社員)が入力されていません。");
        obj.worker_rate_s.focus();
        obj.worker_rate_s.select();
        return false;
    }
    if(!isDigitDot(obj.worker_rate_s.value)){
        alert("作業者賃率(社員)には数値以外の文字は入力出来ません｡");
        obj.worker_rate_s.focus();
        obj.worker_rate_s.select();
        return false;
    }
    var rows_g = obj.rows_g.value;
    for (var i=0; i<rows_g; i++) {
        if(!obj.elements["worker_figure_s[" + i + "]"].value.length) {
            alert("作業者数(社員)が入力されていません。");
            obj.elements["worker_figure_s[" + i + "]"].focus();
            obj.elements["worker_figure_s[" + i + "]"].select();
            return false;
        }
        if(!isDigitDot(obj.elements["worker_figure_s[" + i + "]"].value)) {
            alert("作業者数(社員)には数値以外の文字は入力出来ません｡");
            obj.elements["worker_figure_s[" + i + "]"].focus();
            obj.elements["worker_figure_s[" + i + "]"].select();
            return false;
        }
        
        if(!obj.elements["worker_figure_p[" + i + "]"].value.length){
            alert("作業者数(パート)が入力されていません。");
            obj.elements["worker_figure_p[" + i + "]"].focus();
            obj.elements["worker_figure_p[" + i + "]"].select();
            return false;
        }
        if(!isDigitDot(obj.elements["worker_figure_p[" + i + "]"].value)){
            alert("作業者数(パート)には数値以外の文字は入力出来ません｡");
            obj.elements["worker_figure_p[" + i + "]"].focus();
            obj.elements["worker_figure_p[" + i + "]"].select();
            return false;
        }
        
        if(!obj.elements["standard_rate[" + i + "]"].value.length){
            alert("標準賃率が入力されていません。");
            obj.elements["standard_rate[" + i + "]"].focus();
            obj.elements["standard_rate[" + i + "]"].select();
            return false;
        }
        if(!isDigitDot(obj.elements["standard_rate[" + i + "]"].value)){
            alert("標準賃率には数値以外の文字は入力出来ません｡");
            obj.elements["standard_rate[" + i + "]"].focus();
            obj.elements["standard_rate[" + i + "]"].select();
            return false;
        }
        
    }
    return true;
}

/* 賃率計算結果の印刷 */
function framePrint() {
    list.focus();
    list.print();
}
