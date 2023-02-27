//////////////////////////////////////////////////////////////////////////////
// ³«È¯¥á¥Ë¥å¡¼(¥×¥í¥°¥é¥à³«È¯°ÍÍê½ñ)ÍÑÆþÎÏ¥Õ¥©¡¼¥àÅù¤Î¥Á¥§¥Ã¥¯ÍÑ JavaScript//
// 2002/02/12 Copyright(C) 2002-2004 Kobayashi tnksys@nitto-kohki.co.jp     //
// ÊÑ¹¹·ÐÎò                                                                 //
// 2003/12/15 ¿·µ¬ºîÀ® dev_req.js                                           //
// 2004/01/28 [¼Ò°÷No]¤ò£¶·åÌ¤Ëþ¤Ê¤é¼«Æ°£°µÍ¤¹¤ë¤è¤¦¤ËÊÑ¹¹¡£                //
// 2004/02/23 ³ÎÇ§ÍÑ¤Ë¼Ò°÷ÈÖ¹æ¤Î¤ßÆþÎÏ¤·¤¿»þ¤Ç¤âÂ¨¼Ò°÷Ì¾¤¬½Ð¤ë¤è¤¦¤ËÊÑ¹¹    //
//////////////////////////////////////////////////////////////////////////////
/* ÆþÎÏÊ¸»ú¤¬¿ô»ú¤«¤É¤¦¤«¥Á¥§¥Ã¥¯ */
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

/* ³«È¯°ÍÍê½ñ¤Î¸¡º÷¾ò·ïÆþÎÏÆâÍÆ¤ò¥Á¥§¥Ã¥¯ */
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
            // alert("°ÍÍê¼Ô¤Î¼Ò°÷­â¤Î·å¿ô¤Ï£¶·å¤Ç¤¹¡£");
            // obj.dev_req_client.focus();
            // obj.dev_req_client.select();
            // return false;
        }
    }
    if(obj.dev_req_sdate.value.length){
        if(obj.dev_req_sdate.value.length!=8){
            alert("³«»ÏÆüÉÕ¤Î·å¿ô¤Ï£¸·å¤Ç¤¹¡£");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
        if(!isDigit(obj.dev_req_sdate.value)){
            alert("³«»ÏÆüÉÕ¤Ë¿ô»ú°Ê³°¤Î¥Ç¡¼¥¿¤¬¤¢¤ê¤Þ¤¹¡£");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
/*      if(!obj.dev_req_edate.value.length){
            alert("³«»ÏÆüÉÕ¤òÆþÎÏ¤·¤¿»þ¤Ï½ªÎ»ÆüÉÕ¤âÆþÎÏ¤·¤Æ²¼¤µ¤¤¡£");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
*/  }
    if(obj.dev_req_edate.value.length){
        if(obj.dev_req_edate.value.length!=8){
            alert("½ªÎ»ÆüÉÕ¤Î·å¿ô¤Ï£¸·å¤Ç¤¹¡£");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
        if(!isDigit(obj.dev_req_edate.value)){
            alert("½ªÎ»ÆüÉÕ¤Ë¿ô»ú°Ê³°¤Î¥Ç¡¼¥¿¤¬¤¢¤ê¤Þ¤¹¡£");
            obj.dev_req_edate.focus();
            obj.dev_req_edate.select();
            return false;
        }
/*      if(!obj.dev_req_sdate.value.length){
            alert("½ªÎ»ÆüÉÕ¤òÆþÎÏ¤·¤¿»þ¤Ï³«»ÏÆüÉÕ¤âÆþÎÏ¤·¤Æ²¼¤µ¤¤¡£");
            obj.dev_req_sdate.focus();
            obj.dev_req_sdate.select();
            return false;
        }
*/  }
    if(obj.dev_req_No.value.length){
        if(!isDigit(obj.dev_req_No.value)){
            alert("°ÍÍê­â¤Ë¿ô»ú°Ê³°¤Î¥Ç¡¼¥¿¤¬¤¢¤ê¤Þ¤¹¡£");
            obj.dev_req_No.focus();
            obj.dev_req_No.select();
            return false;
        }
    }
    return true;
}

/* ³«È¯°ÍÍê½ñ ºîÀ® Á÷¿® ¤ÎÆþÎÏÆâÍÆ¥Á¥§¥Ã¥¯ */
function chk_dev_req_submit(obj){
    if(!obj.iraisya.value.length){
        alert("°ÍÍê¼Ô¤Î¼Ò°÷­â¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
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
            // alert("°ÍÍê¼Ô¤Î¼Ò°÷­â¤Î·å¿ô¤Ï£¶·å¤Ç¤¹¡£");
            // obj.iraisya.focus();
            // obj.iraisya.select();
            // return false;
        }
    }
    /*  ¥µ¡¼¥Ð¡¼ºÆÅÙ¤Ç¥Á¥§¥Ã¥¯¤¹¤ë¤è¤¦¤ËÊÑ¹¹ ¼Ò°÷Ì¾¤Î³ÎÇ§¤òÂ¨½ÐÍè¤ëÍÍ¤Ë¤¹¤ë¤¿¤á
    if(!obj.mokuteki.value.length){
        alert("ÌÜÅªËô¤Ï¥¿¥¤¥È¥ë¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
        obj.mokuteki.focus();
        obj.mokuteki.select();
        return false;
    }
    if(!obj.naiyou.value.length){
        alert("°ÍÍêÆâÍÆ¤¬¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
        obj.naiyou.focus();
        obj.naiyou.select();
        return false;
    }
    */
    if(obj.yosoukouka.value.length > 0) {
        if(!isDigit(obj.yosoukouka.value)) {
            alert("Í½ÁÛ¸ú²Ì¹©¿ô(Ê¬¡¿Ç¯)¤Ë¿ô»ú°Ê³°¤Î¥Ç¡¼¥¿¤¬¤¢¤ê¤Þ¤¹¡£");
            obj.yosoukouka.focus();
            obj.yosoukouka.select();
            return false;
        }
    }
}

// ³«È¯°ÍÍê½ñ¤Î¥á¥ó¥Æ¥Ê¥ó¥¹Administrator¸¢¸Â¤Ç¤ÎÁàºî
function chk_dev_req_edit(obj){
    if(!obj.yuusendo.value.length){
        alert("Í¥ÀèÅÙ¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
        obj.yuusendo.focus();
        obj.yuusendo.select();
        return false;
    }
    if(!obj.sagyouku.value.length){
        alert("ºî¶È¶è¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
        obj.sagyouku.focus();
        obj.sagyouku.select();
        return false;
    }
    if(!obj.sintyoku.value.length){
        alert("¿ÊÄ½¾õ¶·¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
        obj.sintyoku.focus();
        obj.sintyoku.select();
        return false;
    }
/*  if(!obj.kousuu.value.length){
        alert("³«È¯¹©¿ô¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
        obj.kousuu.focus();
        obj.kousuu.select();
        return false;
    }
    if(!obj.kanryou.value.length){
        alert("´°Î»Æü¤¬Ì¤ÆþÎÏ¤Ç¤¹¡£");
        obj.kanryou.focus();
        obj.kanryou.select();
        return false;
    }
*/
}
