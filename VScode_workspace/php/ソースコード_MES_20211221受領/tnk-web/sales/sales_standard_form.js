//////////////////////////////////////////////////////////////////////////////
// ��� �����ץ����ѥե�����Υ����å��롼���� JavaScript                 //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/02 Created  sales_custom_form.js �� sales_standard_form.js       //
//            �����Ѥ�ɸ��Υ��ץ顦��˥��˥������ޥ���                    //
// 2006/08/29 ���1��3�ޤǤξ���͡������ͤ�����ͭ��̵�������å����ɲ�      //
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
function chk_select_form(obj) {
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
    if (obj.d_end.value < obj.d_start.value) {
        alert('�������Ƚ�λ������ž���Ƥ��ޤ���');
        obj.d_start.focus();
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
    if (!obj.lower_uri_ritu.value) {
        alert("���β����ͤ����Ϥ���Ƥ��ޤ���");
        obj.lower_uri_ritu.focus();
        obj.lower_uri_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.lower_uri_ritu.value)) {
        alert("���β����ͤ� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.lower_uri_ritu.focus();
        obj.lower_uri_ritu.select();
        return false;
    }
    if (!obj.upper_uri_ritu.value) {
        alert("���ξ���ͤ����Ϥ���Ƥ��ޤ���");
        obj.upper_uri_ritu.focus();
        obj.upper_uri_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.upper_uri_ritu.value)) {
        alert("���ξ���ͤ� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.upper_uri_ritu.focus();
        obj.upper_uri_ritu.select();
        return false;
    }
    var upper_uri = parseFloat(obj.upper_uri_ritu.value);   // ���������ʤ���ʸ�������Ӥˤʤ뤿��
    var lower_uri = parseFloat(obj.lower_uri_ritu.value);
    if (upper_uri < lower_uri) {
        alert("���ξ���ͤ������ͤ�꾮�����Ǥ���");
        obj.lower_uri_ritu.focus();
        obj.lower_uri_ritu.select();
        return false;
    }
    if (!obj.lower_mate_ritu.value) {
        alert("���β����ͤ����Ϥ���Ƥ��ޤ���");
        obj.lower_mate_ritu.focus();
        obj.lower_mate_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.lower_mate_ritu.value)) {
        alert("���β����ͤ� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.lower_mate_ritu.focus();
        obj.lower_mate_ritu.select();
        return false;
    }
    if (!obj.upper_mate_ritu.value) {
        alert("���ξ���ͤ����Ϥ���Ƥ��ޤ���");
        obj.upper_mate_ritu.focus();
        obj.upper_mate_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.upper_mate_ritu.value)) {
        alert("���ξ���ͤ� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.upper_mate_ritu.focus();
        obj.upper_mate_ritu.select();
        return false;
    }
    var upper_mate = parseFloat(obj.upper_mate_ritu.value);
    var lower_mate = parseFloat(obj.lower_mate_ritu.value);
    if (upper_mate < lower_mate) {
        alert("���ξ���ͤ������ͤ�꾮�����Ǥ���");
        obj.lower_mate_ritu.focus();
        obj.lower_mate_ritu.select();
        return false;
    }
    if (!obj.lower_equal_ritu.value) {
        alert("���β����ͤ����Ϥ���Ƥ��ޤ���");
        obj.lower_equal_ritu.focus();
        obj.lower_equal_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.lower_equal_ritu.value)) {
        alert("���β����ͤ� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.lower_equal_ritu.focus();
        obj.lower_equal_ritu.select();
        return false;
    }
    if (!obj.upper_equal_ritu.value) {
        alert("���ξ���ͤ����Ϥ���Ƥ��ޤ���");
        obj.upper_equal_ritu.focus();
        obj.upper_equal_ritu.select();
        return false;
    }
    if (!isDigitDot(obj.upper_equal_ritu.value)) {
        alert("���ξ���ͤ� �����ڤӾ����� �ʳ���ʸ��������ޤ���");
        obj.upper_equal_ritu.focus();
        obj.upper_equal_ritu.select();
        return false;
    }
    var upper_equal = (obj.upper_equal_ritu.value - 0);     // parseFloat(obj.upper_equal_ritu.value)
    var lower_equal = (obj.lower_equal_ritu.value - 0);     // �嵭��Ʊ����̣
    if ( upper_equal < lower_equal) {
        alert("���ξ���ͤ������ͤ�꾮�����Ǥ���");
        obj.lower_equal_ritu.focus();
        obj.lower_equal_ritu.select();
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

