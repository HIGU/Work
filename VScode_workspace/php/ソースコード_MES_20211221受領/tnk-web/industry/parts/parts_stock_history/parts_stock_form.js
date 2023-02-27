//////////////////////////////////////////////////////////////////////////////
// ������ ���ʺ߸˷��� �Ȳ� ���ʻ���ե����� JavaScript�ˤ�����ϥ����å�   //
// Copyright(C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/12/20 Created  parts_stock_form.js                                  //
//////////////////////////////////////////////////////////////////////////////

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
//    document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
//    document.form_name.element_name.select();
}

/* ����ʸ�����������ɤ��������å� */
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
            if ("0">c || c>"9") {
                return false;
            }
        }
    }
    return true;
}

/***** �ե���������ϥ����å� function *****/
function chk_parts_stock_form(obj) {
    /* �����ֹ����ʸ���Ѵ� & ���ϥ����å� */
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    if (!obj.parts_no.value.length) {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    }
    
    /* �����ϰϻ���(����)�� ���ϥ����å� */
    if ( !isDigit(obj.date_low.value) ) {
        alert('�����ϰϻ���(����)�˿����ʳ���ʸ��������ޤ���');
        obj.date_low.focus();
        obj.date_low.select();
        return false;
    } else if (obj.date_low.value < 20000401) {
        alert('�����ϰϻ���(����)��20000401�ʾ�Ǥ���');
        obj.date_low.focus();
        obj.date_low.select();
        return false;
    } else if (obj.date_low.value.length != 8) {
        alert('�����ϰϻ���(����)�η���ϣ���Ǥ���');
        obj.date_low.focus();
        obj.date_low.select();
        return false;
    }
    
    /* �����ϰϻ���(���)�� ���ϥ����å� */
    if ( !isDigit(obj.date_upp.value) ) {
        alert('�����ϰϻ���(���)�˿����ʳ���ʸ��������ޤ���');
        obj.date_upp.focus();
        obj.date_upp.select();
        return false;
    } else if (obj.date_upp.value.length != 8) {
        alert('�����ϰϻ���(���)�η���ϣ���Ǥ���');
        obj.date_upp.focus();
        obj.date_upp.select();
        return false;
    }
    
    return true;
}
