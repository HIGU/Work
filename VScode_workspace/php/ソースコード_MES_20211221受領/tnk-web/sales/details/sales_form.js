//////////////////////////////////////////////////////////////////////////////
// ����˥塼�Υ����å��롼���� JavaScript uriage.js �� salse_form.js     //
// 2001/07/07 Copyright(C)2001-2013 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                                 //
// 2001/07/07 �������� uriage.js                                            //
// 2003/10/31 obj.assy_no.value.length!=9���ɲ� .value.toUpperCase()�ɲ�    //
// 2003/12/23 obj.sales_form.value �Υ����å����ɲ�                         //
// 2013/05/13 ���ڲ���������Ƴ���ΰ�standard_date�Υ����å����ɲ�    ��ë //
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

/* ���դ��������Ƥ�����å� */
function chk_sales_form(obj) {
    if (!obj.d_start.value.length) {
        alert("���դ����򳫻��������Ϥ���Ƥ��ޤ���");
        obj.d_start.focus();
        return false;
    }
    if (!isDigit(obj.d_start.value)) {
        alert("�������դ˿����ʳ��Υǡ���������ޤ���");
        obj.d_start.focus();
        obj.d_start.select();
        return false;
    }
    if (obj.d_start.value.length != 8) {
        alert("���դγ�����������Ǥ���ޤ���");
        obj.d_start.focus();
        return false;
    }
    if (!obj.d_end.value.length) {
        alert("���դ�����λ�������򤵤�Ƥ��ޤ���");
        obj.d_end.focus();
        return false;
    }
    if (!isDigit(obj.d_end.value)) {
        alert("��λ���դ˿����ʳ��Υǡ���������ޤ���");
        obj.d_end.focus();
        obj.d_end.select();
        return false;
    }
    if (obj.d_end.value.length != 8) {
        alert("���դν�λ��������Ǥ���ޤ���");
        obj.d_end.focus();
        return false;
    }
    obj.assy_no.value = obj.assy_no.value.toUpperCase();
    if (obj.assy_no.value.length != 0) {
        if (obj.assy_no.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.assy_no.focus();
            obj.assy_no.select();
            return false;
        }
    }
    if (!isDigitDot(obj.uri_ritu.value)) {
        alert("���ꤵ�줿Ψ�� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.uri_ritu.focus();
        obj.uri_ritu.select();
        return false;
    }
    if ( !isDigit(obj.sales_page.value) ) {
        alert('�ڡ������˿����ʳ���ʸ��������ޤ���');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    } else if (obj.sales_page.value < 1) {
        alert('�ڡ������ϣ��ʾ�Ǥ���');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    } else if (obj.sales_page.value > 9999) {
        alert('�ڡ������ϣ��������ޤǤǤ���');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    }
    return true;
}

/* ���դ��������Ƥ�����å� */
function chk_sales8_form(obj) {
    if (!obj.d_start.value.length) {
        alert("���դ����򳫻��������Ϥ���Ƥ��ޤ���");
        obj.d_start.focus();
        return false;
    }
    if (!isDigit(obj.d_start.value)) {
        alert("�������դ˿����ʳ��Υǡ���������ޤ���");
        obj.d_start.focus();
        obj.d_start.select();
        return false;
    }
    if (obj.d_start.value.length != 8) {
        alert("���դγ�����������Ǥ���ޤ���");
        obj.d_start.focus();
        return false;
    }
    if (!obj.d_end.value.length) {
        alert("���դ�����λ�������򤵤�Ƥ��ޤ���");
        obj.d_end.focus();
        return false;
    }
    if (!isDigit(obj.d_end.value)) {
        alert("��λ���դ˿����ʳ��Υǡ���������ޤ���");
        obj.d_end.focus();
        obj.d_end.select();
        return false;
    }
    if (obj.d_end.value.length != 8) {
        alert("���դν�λ��������Ǥ���ޤ���");
        obj.d_end.focus();
        return false;
    }
    if (!isDigit(obj.standard_date.value)) {
        alert("���ڴ�����դ˿����ʳ��Υǡ���������ޤ���");
        obj.standard_date.focus();
        obj.standard_date.select();
        return false;
    }
    if (obj.standard_date.value.length != 8) {
        alert("���ڴ�����դ�����Ǥ���ޤ���");
        obj.standard_date.focus();
        return false;
    }
    obj.assy_no.value = obj.assy_no.value.toUpperCase();
    if (obj.assy_no.value.length != 0) {
        if (obj.assy_no.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.assy_no.focus();
            obj.assy_no.select();
            return false;
        }
    }
    if (!isDigitDot(obj.uri_ritu.value)) {
        alert("���ꤵ�줿Ψ�� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.uri_ritu.focus();
        obj.uri_ritu.select();
        return false;
    }
    if ( !isDigit(obj.sales_page.value) ) {
        alert('�ڡ������˿����ʳ���ʸ��������ޤ���');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    } else if (obj.sales_page.value < 1) {
        alert('�ڡ������ϣ��ʾ�Ǥ���');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    } else if (obj.sales_page.value > 9999) {
        alert('�ڡ������ϣ��������ޤǤǤ���');
        obj.sales_page.focus();
        obj.sales_page.select();
        return false;
    }
    return true;
}

/* ���դ��������Ƥ�����å� ���롼�ץ����ɽ�����*/
function chk_sales_form_all(obj) {
    if (!obj.d_start.value.length) {
        alert("���դ����򳫻��������Ϥ���Ƥ��ޤ���");
        obj.d_start.focus();
        return false;
    }
    if (!isDigit(obj.d_start.value)) {
        alert("�������դ˿����ʳ��Υǡ���������ޤ���");
        obj.d_start.focus();
        obj.d_start.select();
        return false;
    }
    if (obj.d_start.value.length != 8) {
        alert("���դγ�����������Ǥ���ޤ���");
        obj.d_start.focus();
        return false;
    }
    if (!obj.d_end.value.length) {
        alert("���դ�����λ�������򤵤�Ƥ��ޤ���");
        obj.d_end.focus();
        return false;
    }
    if (!isDigit(obj.d_end.value)) {
        alert("��λ���դ˿����ʳ��Υǡ���������ޤ���");
        obj.d_end.focus();
        obj.d_end.select();
        return false;
    }
    if (obj.d_end.value.length != 8) {
        alert("���դν�λ��������Ǥ���ޤ���");
        obj.d_end.focus();
        return false;
    }
    return true;
}
