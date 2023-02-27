//////////////////////////////////////////////////////////////////////////////
// �����ץ��깩��������Υ����å��롼���� JavaScript custom_form.js     //
//  Copyright(C)2013-2013 N.Ohya norihisa_ooya@nitto-kohki.co.jp            //
// �ѹ�����                                                                 //
// 2013/01/24 �������� custom_form.js                                       //
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
function chk_custom_form(obj) {
    obj.assy_no.value = obj.assy_no.value.toUpperCase();
    if (obj.assy_no.value.length != 0) {
        if (obj.assy_no.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.assy_no.focus();
            obj.assy_no.select();
            return false;
        }
    }
    if (obj.assy_no.value.length == 0) {
        alert("�����ֹ�����Ϥ��Ƥ���������");
        obj.assy_no.focus();
        obj.assy_no.select();
        return false;
    }
    return true;
}

