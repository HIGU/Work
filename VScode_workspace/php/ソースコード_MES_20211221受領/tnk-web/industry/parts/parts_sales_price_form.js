//////////////////////////////////////////////////////////////////////////////
// ñ���������������(����ñ��)���� �ե����� JavaScript�ˤ�����ϥ����å� //
// Copyright(C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/11/19 Created  parts_sales_price_form.js                            //
// 2010/06/25 ���դδ�����κ����ͤ�20380331����20990331���ѹ�          ��ë //
// 2013/01/30 �ץ�����ѹ��ΰ����դ����¤������                   ��ë //
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
function chk_parts_sales_price_form(obj) {
    /* �����ֹ����ʸ���Ѵ� & ���ϥ����å� */
    obj.parts.value = obj.parts.value.toUpperCase();
    if (!obj.parts.value.length) {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts.focus();
        obj.parts.select();
        return false;
    }
    if (obj.parts.value.length != 0) {
        if (obj.parts.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.parts.focus();
            obj.parts.select();
            return false;
        }
    }
    
    /* ������� ���ϥ����å� */
    if ( !isDigit(obj.regdate.value) ) {
        alert('������˿����ʳ���ʸ��������ޤ���');
        obj.regdate.focus();
        obj.regdate.select();
        return false;
    }  else if (obj.regdate.value < 20001001) {
        alert('�������20001001�ʾ�Ǥ���');
        obj.regdate.focus();
        obj.regdate.select();
        return false;
    } else if (obj.regdate.value > 20990331) {
        alert('�������20990331�ޤǤǤ���');
        obj.regdate.focus();
        obj.regdate.select();
        return false;
    }
    
    /* �������(����ñ��)�졼�Ȥ� ���ϥ����å� */
    if ( !isDigitDot(obj.sales_rate.value) ) {
        alert('�������(����ñ��)�졼�Ȥ˿����ʳ���ʸ��������ޤ���');
        obj.sales_rate.focus();
        obj.sales_rate.select();
        return false;
    } else if (obj.sales_rate.value < 1.00) {
        alert('�������(����ñ��)�졼�Ȥ�1.00�ʾ�Ǥ���');
        obj.sales_rate.focus();
        obj.sales_rate.select();
        return false;
    } else if (obj.sales_rate.value > 1.99) {
        alert('�������(����ñ��)�졼�Ȥ�1.99�ޤǤǤ���');
        obj.sales_rate.focus();
        obj.sales_rate.select();
        return false;
    }
    
    return true;
}
