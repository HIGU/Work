//////////////////////////////////////////////////////////////////////////////
// �ԣΣ˼Ұ���˥塼�� ���ϥե��������Υ����å��� JavaScript               //
// 2001/07/07 Copyright(C) 2001-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// �ѹ�����                                                                 //
// 2003/12/15 confirm.js ����ʬΥ���� login.js �򿷵��˺���                 //
// 2004/01/28 [�Ұ�No]�򣶷�̤���ʤ鼫ư���ͤ���褦���ѹ���                //
//////////////////////////////////////////////////////////////////////////////
/* ����ʸ�����������ɤ��������å� */
/* false=�����Ǥʤ�  ture=�����Ǥ��� */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            return false;
        }
    }
    return true;
}
/* ���Ͼ���Υ����å� & ���� */
function inpConf(obj) {
    if (!obj.userid.value.length) {
        alert("[�Ұ�No]�������󤬶���Ǥ���������Ǥ��ޤ���");
        obj.userid.focus();
        return false;
    }
    if (obj.userid.value.length != 6) {
        switch (obj.userid.value.length) {
        case 1:
            obj.userid.value = ('00000' + obj.userid.value);
            break;
        case 2:
            obj.userid.value = ('0000' + obj.userid.value);
            break;
        case 3:
            obj.userid.value = ('000' + obj.userid.value);
            break;
        case 4:
            obj.userid.value = ('00' + obj.userid.value);
            break;
        case 5:
            obj.userid.value = ('0' + obj.userid.value);
            break;
        }
        // alert("[�Ұ�No]�η���ϣ���Ǥ���");
        // obj.userid.focus();
        // return false;
    }
    if (!obj.passwd.value.length) {
        alert("[�ѥ����]�������󤬶���Ǥ���������Ǥ��ޤ���");
        obj.passwd.focus();
        return false;
    }
    return true;
}
