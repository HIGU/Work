//////////////////////////////////////////////////////////////////////////////
// A�������ξȲ� �������ե����� JavaScript�ˤ�����ϥ����å�              //
// Copyright(C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// �ѹ�����                                                                 //
// 2016/03/25 �������� aden_details_form.js                                 //
// 2017/06/14 ¸�ߤ��ʤ�ȯ�����ֹ�μ����ǥ��顼�Τ�����                  //
//////////////////////////////////////////////////////////////////////////////

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
function chk_payable_form(obj) {
    /* �����ֹ�����ϥ����å� & ��ʸ���Ѵ� */
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    }
    
    /* ���դ��������Ƥ�����å� */
    if (!obj.str_date.value.length) {
        alert("���դ����򳫻��������Ϥ���Ƥ��ޤ���");
        obj.str_date.focus();
        return false;
    }
    if (!isDigit(obj.str_date.value)) {
        alert("�������դ˿����ʳ��Υǡ���������ޤ���");
        obj.str_date.focus();
        obj.str_date.select();
        return false;
    }
    if (obj.str_date.value.length != 8) {
        alert("���դγ�����������Ǥ���ޤ���");
        obj.str_date.focus();
        return false;
    }
    if (!obj.end_date.value.length) {
        alert("���դ�����λ�������򤵤�Ƥ��ޤ���");
        obj.end_date.focus();
        return false;
    }
    if (!isDigit(obj.end_date.value)) {
        alert("��λ���դ˿����ʳ��Υǡ���������ޤ���");
        obj.end_date.focus();
        obj.end_date.select();
        return false;
    }
    if (obj.end_date.value.length != 8) {
        alert("���դν�λ��������Ǥ���ޤ���");
        obj.end_date.focus();
        return false;
    }
    if (obj.str_date.value > obj.end_date.value) {
        alert("���դ��ϰϤ����������Ϥ��Ƥ���������");
        obj.str_date.focus();
        return false;
    }
    /* LT�����������Ƥ�����å� */
    if (!obj.lt_str_date.value.length) {
        if (!obj.lt_end_date.value.length) {
        } else {
            alert("L/T���������������Ϥ���Ƥ��ޤ���");
            obj.lt_str_date.focus();
            return false;
        }
    } else if (!obj.lt_end_date.value.length) {
        alert("L/T���������������Ϥ���Ƥ��ޤ���");
        obj.lt_str_date.focus();
        return false;
    }
    if (!isDigit(obj.lt_str_date.value)) {
        alert("L/T���˿����ʳ��Υǡ���������ޤ���");
        obj.lt_str_date.focus();
        obj.lt_str_date.select();
        return false;
    }
    if (!isDigit(obj.lt_end_date.value)) {
        alert("L/T���˿����ʳ��Υǡ���������ޤ���");
        obj.lt_end_date.focus();
        obj.lt_end_date.select();
        return false;
    }
    if (obj.lt_str_date.value > obj.lt_end_date.value) {
        alert("L/T�����ϰϤ����������Ϥ��Ƥ���������");
        obj.lt_str_date.focus();
        return false;
    }
    /* ���Ǥ�ɽ���Կ� ���ϥ����å� */
    if ( !isDigit(obj.paya_page.value) ) {
        alert('�ڡ������˿����ʳ���ʸ��������ޤ���');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    } else if (obj.paya_page.value < 1) {
        alert('�ڡ������ϣ��ʾ�Ǥ���');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    } else if (obj.paya_page.value > 9999) {
        alert('�ڡ������ϣ��������ޤǤǤ���');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    }
    return true;
}

