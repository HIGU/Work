//////////////////////////////////////////////////////////////////////////////
// ����������Ψ �Ȳ���� ���ϥ����å� JavaScript                            //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/06 Created assemblyRate_actAllocate.js                           //
//////////////////////////////////////////////////////////////////////////////
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
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

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ǯ��������ͤ������å� */
function ismonth(str) {
    var month = str.slice(4);
    if ((month < 1) || (12 < month)) {
        return false;
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* �軻�����γ���ǯ��η׻� */
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

/* ñ������ǯ��ǡ����Υ����å� */
function ym_chk_tangetu(obj) {
        var str = obj.tan_str_ym.value;
        var end = obj.tan_end_ym.value;
        
        if(str > end){
            alert("��λǯ��ϳ���ǯ��ʹߤ�ǯ������Ϥ��Ʋ�������");
            obj.tan_end_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if(ismonth(obj.tan_str_ym.value)){
        } else {
            alert("����������ǯ������Ϥ��Ƥ���������");
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if(!obj.tan_str_ym.value.length){
            alert("����ǯ����Ϥ���Ƥ��ޤ���");
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        } else if(isDigit(obj.tan_str_ym.value)){
            alert("����ǯ��ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if (obj.tan_str_ym.value.length == 6) {
        } else {
            alert('����ǯ���YYYYMM�Σ�������Ϥ��Ƥ���������');
            obj.tan_str_ym.focus();
            obj.tan_str_ym.select();
            return false;
        }
        if(ismonth(obj.tan_end_ym.value)){
        } else {
            alert("��������λǯ������Ϥ��Ƥ���������");
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        }
        if(!obj.tan_end_ym.value.length){
            alert("��λǯ����Ϥ���Ƥ��ޤ���");
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        } else if(isDigit(obj.tan_end_ym.value)){
            alert("��λǯ��ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        }
        if (obj.tan_end_ym.value.length == 6) {
        } else {
            alert('��λǯ���YYYYMM�Σ�������Ϥ��Ƥ���������');
            obj.tan_end_ym.focus();
            obj.tan_end_ym.select();
            return false;
        }
        return true;
}

/* �軻����ǯ��ǡ����Υ����å� */
function ym_chk_kessan(obj) {
        var str = obj.str_ym.value;
        var end = obj.end_ym.value;
        
        if(str > end){
            alert("��λǯ��ϳ���ǯ��ʹߤ�ǯ������Ϥ��Ʋ�������");
            obj.end_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if(ismonth(obj.str_ym.value)){
        } else {
            alert("����������ǯ������Ϥ��Ƥ���������");
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if(!obj.str_ym.value.length){
            alert("����ǯ����Ϥ���Ƥ��ޤ���");
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        } else if(isDigit(obj.str_ym.value)){
            alert("����ǯ��ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if (obj.str_ym.value.length == 6) {
        } else {
            alert('����ǯ���YYYYMM�Σ�������Ϥ��Ƥ���������');
            obj.str_ym.focus();
            obj.str_ym.select();
            return false;
        }
        if(ismonth(obj.end_ym.value)){
        } else {
            alert("��������λǯ������Ϥ��Ƥ���������");
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        }
        if(!obj.end_ym.value.length){
            alert("��λǯ����Ϥ���Ƥ��ޤ���");
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        } else if(isDigit(obj.end_ym.value)){
            alert("��λǯ��ˤϿ��Ͱʳ���ʸ�������Ͻ���ޤ���");
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        }
        if (obj.end_ym.value.length == 6) {
        } else {
            alert('��λǯ���YYYYMM�Σ�������Ϥ��Ƥ���������');
            obj.end_ym.focus();
            obj.end_ym.select();
            return false;
        }
        return true;
}

/* ��Ψ�׻���̤ΰ��� */
function framePrint() {
    list.focus();
    list.print();
}
