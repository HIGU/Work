//////////////////////////////////////////////////////////////////////////
// ������Ψ�׻�ɽ ����(���)��������ž���֤����Ϥ���Ψ��ư����      //
// Java Script                                                          //
// 2002/09/23 Copyright(C) 2002 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                             //
// 2002/09/23 ��������                                                  //
//////////////////////////////////////////////////////////////////////////
/* ����ʸ�����������ɤ��������å� */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return true;
        }
    return false;
}
function ym_chk(obj){
    if(!obj.rate_ym.value.length){
        alert("[�о�ǯ��]�������󤬶���Ǥ���");
        obj.rate_ym.focus();
        obj.rate_ym.select();
        return false;
    }
    if(isDigit(obj.rate_ym.value)){
        alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.rate_ym.focus();
        obj.rate_ym.select();
        return false;
    }
    return true;
}
function kessan_chk(obj){
    if(!obj.str_ym.value.length){
        alert("[�о�ǯ��]�������󤬶���Ǥ���");
        obj.str_ym.focus();
        obj.str_ym.select();
        return false;
    }
    if(isDigit(obj.str_ym.value)){
        alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.str_ym.focus();
        obj.str_ym.select();
        return false;
    }
    if(!obj.end_ym.value.length){
        alert("[�о�ǯ��]�������󤬶���Ǥ���");
        obj.end_ym.focus();
        obj.end_ym.select();
        return false;
    }
    if(isDigit(obj.end_ym.value)){
        alert("���Ͱʳ���ʸ�������Ͻ���ޤ���");
        obj.end_ym.focus();
        obj.end_ym.select();
        return false;
    }
    if(obj.span[0].checked == false){
        if(obj.span[1].checked == false)
            if(obj.span[2].checked == false){
                alert("[��֡�������������]������ǲ�������");
                return false;
            }
    }
    return true;
}
