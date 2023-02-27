//////////////////////////////////////////////////////////////////////////////
// ��ȯ��˥塼(�ץ���೫ȯ�����)�����ϥե��������Υ����å��� JavaScript//
// 2002/02/12 Copyright(C) 2002-2004 Kobayashi tnksys@nitto-kohki.co.jp     //
// �ѹ�����                                                                 //
// 2003/12/15 �������� dev_req.js                                           //
// 2004/01/28 [�Ұ�No]�򣶷�̤���ʤ鼫ư���ͤ���褦���ѹ���                //
// 2004/02/23 ��ǧ�Ѥ˼Ұ��ֹ�Τ����Ϥ������Ǥ�¨�Ұ�̾���Ф�褦���ѹ�    //
//////////////////////////////////////////////////////////////////////////////
/* ����ʸ�����������ɤ��������å� */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return false;
        }
    return true;
}

/* ��ȯ�����θ�������������Ƥ�����å� */
function chk_dev_req_input(obj) {
    if(obj.dev_req_client.value.length) {
        if(obj.dev_req_client.value.length != 6) {
            switch (obj.dev_req_client.value.length) {
            case 1:
                obj.dev_req_client.value = ('00000' + obj.dev_req_client.value);
                break;
            case 2:
                obj.dev_req_client.value = ('0000' + obj.dev_req_client.value);
                break;
            case 3:
                obj.dev_req_client.value = ('000' + obj.dev_req_client.value);
                break;
            case 4:
                obj.dev_req_client.value = ('00' + obj.dev_req_client.value);
                break;
            case 5:
                obj.dev_req_client.value = ('0' + obj.dev_req_client.value);
                break;
            }
            // alert("����ԤμҰ���η���ϣ���Ǥ���");
            // obj.dev_req_client.focus();
            // obj.dev_req_client.select();
            // return false;
        }
    }
    if(obj.dev_req_sdate.value.length){
        if(obj.dev_req_sdate.value.length!=8){
            alert("�������դη���ϣ���Ǥ���");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
        if(!isDigit(obj.dev_req_sdate.value)){
            alert("�������դ˿����ʳ��Υǡ���������ޤ���");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
/*      if(!obj.dev_req_edate.value.length){
            alert("�������դ����Ϥ������Ͻ�λ���դ����Ϥ��Ʋ�������");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
*/  }
    if(obj.dev_req_edate.value.length){
        if(obj.dev_req_edate.value.length!=8){
            alert("��λ���դη���ϣ���Ǥ���");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
        if(!isDigit(obj.dev_req_edate.value)){
            alert("��λ���դ˿����ʳ��Υǡ���������ޤ���");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
/*      if(!obj.dev_req_sdate.value.length){
            alert("��λ���դ����Ϥ������ϳ������դ����Ϥ��Ʋ�������");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
*/  }
    if(obj.dev_req_No.value.length){
        if(!isDigit(obj.dev_req_No.value)){
            alert("�����˿����ʳ��Υǡ���������ޤ���");
            obj.dev_req_No.focus();
            obj.dev_req_No.select();
            return false;
        }
    }
    return true;
}

/* ��ȯ����� ���� ���� ���������ƥ����å� */
function chk_dev_req_submit(obj){
    if(!obj.iraisya.value.length){
        alert("����ԤμҰ��⤬̤���ϤǤ���");
        obj.iraisya.focus();
        obj.iraisya.select();
        return false;
    }
    if(obj.iraisya.value.length) {
        if(obj.iraisya.value.length != 6){
            switch (obj.iraisya.value.length) {
            case 1:
                obj.iraisya.value = ('00000' + obj.iraisya.value);
                break;
            case 2:
                obj.iraisya.value = ('0000' + obj.iraisya.value);
                break;
            case 3:
                obj.iraisya.value = ('000' + obj.iraisya.value);
                break;
            case 4:
                obj.iraisya.value = ('00' + obj.iraisya.value);
                break;
            case 5:
                obj.iraisya.value = ('0' + obj.iraisya.value);
                break;
            }
            // alert("����ԤμҰ���η���ϣ���Ǥ���");
            // obj.iraisya.focus();
            // obj.iraisya.select();
            // return false;
        }
    }
    /*  �����С����٤ǥ����å�����褦���ѹ� �Ұ�̾�γ�ǧ��¨������ͤˤ��뤿��
    if(!obj.mokuteki.value.length){
        alert("��Ū���ϥ����ȥ뤬̤���ϤǤ���");
        obj.mokuteki.focus();
        obj.mokuteki.select();
        return false;
    }
    if(!obj.naiyou.value.length){
        alert("�������Ƥ���̤���ϤǤ���");
        obj.naiyou.focus();
        obj.naiyou.select();
        return false;
    }
    */
    if(obj.yosoukouka.value.length > 0) {
        if(!isDigit(obj.yosoukouka.value)) {
            alert("ͽ�۸��̹���(ʬ��ǯ)�˿����ʳ��Υǡ���������ޤ���");
            obj.yosoukouka.focus();
            obj.yosoukouka.select();
            return false;
        }
    }
}

// ��ȯ�����Υ��ƥʥ�Administrator���¤Ǥ����
function chk_dev_req_edit(obj){
    if(!obj.yuusendo.value.length){
        alert("ͥ���٤�̤���ϤǤ���");
        obj.yuusendo.focus();
        obj.yuusendo.select();
        return false;
    }
    if(!obj.sagyouku.value.length){
        alert("��ȶ褬̤���ϤǤ���");
        obj.sagyouku.focus();
        obj.sagyouku.select();
        return false;
    }
    if(!obj.sintyoku.value.length){
        alert("��Ľ������̤���ϤǤ���");
        obj.sintyoku.focus();
        obj.sintyoku.select();
        return false;
    }
/*  if(!obj.kousuu.value.length){
        alert("��ȯ������̤���ϤǤ���");
        obj.kousuu.focus();
        obj.kousuu.select();
        return false;
    }
    if(!obj.kanryou.value.length){
        alert("��λ����̤���ϤǤ���");
        obj.kanryou.focus();
        obj.kanryou.select();
        return false;
    }
*/
}
