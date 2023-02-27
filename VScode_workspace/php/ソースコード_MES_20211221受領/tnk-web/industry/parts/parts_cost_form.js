//////////////////////////////////////////////////////////////////////////////
// ñ������ξȲ� �������ե����� JavaScript�ˤ�����ϥ����å�             //
// Copyright(C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2004/05/17 �������� parts_cost_form.js                                   //
// 2005/01/11 �ǥ��쥯�ȥ�� industry/ �� industry/parts/ ���ѹ�            //
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
function chk_parts_cost_form(obj) {
    /* �����ֹ�����ϥ����å� & ��ʸ���Ѵ� */
    if (!obj.parts_no.value.length) {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    }
    
    /* ���Ǥ�ɽ���Կ� ���ϥ����å� */
    if ( !isDigit(obj.cost_page.value) ) {
        alert('�ڡ������˿����ʳ���ʸ��������ޤ���');
        obj.cost_page.focus();
        obj.cost_page.select();
        return false;
    } else if (obj.cost_page.value < 1) {
        alert('�ڡ������ϣ��ʾ�Ǥ���');
        obj.cost_page.focus();
        obj.cost_page.select();
        return false;
    } else if (obj.cost_page.value > 999) {
        alert('�ڡ������ϣ������ޤǤǤ���');
        obj.cost_page.focus();
        obj.cost_page.select();
        return false;
    }
    return true;
}
