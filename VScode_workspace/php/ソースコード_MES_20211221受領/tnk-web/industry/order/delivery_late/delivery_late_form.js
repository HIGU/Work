//////////////////////////////////////////////////////////////////////////////
// Ǽ���٤�����ʾȲ� �������ե����� JavaScript�ˤ�����ϥ����å�         //
// Copyright(C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// �ѹ�����                                                                 //
// 2011/11/04 �������� delivery_late_form.js                                //
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
    
    /* ȯ��������ϥ����å� & ��ʸ���Ѵ� */
    if ( (obj.vendor.value.length < 5) && (obj.vendor.value.length > 0) ) {
        if (!isDigit(obj.vendor.value)) {
            alert("ȯ���襳���ɤ˿����ʳ��Υǡ���������ޤ���");
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
    } else if (obj.paya_page.value > 999) {
        alert('�ڡ������ϣ������ޤǤǤ���');
        obj.paya_page.focus();
        obj.paya_page.select();
        return false;
    }
    return true;
}

