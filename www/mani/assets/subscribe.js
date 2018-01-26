/*
* @Author: Mani
* @Date:   2017-08-28 19:20:58
* @Last Modified time: 2017-09-25 22:12:54
*/


function toggleShowSelection(id) {
    /*var coupon_description = $("#coupon_description_"+id).val();
    var coupon_discount    = $("#coupon_discount_"+id).val();
    var coupon_max_use     = $("#coupon_max_use_"+id).val();
    var coupon_enabled     = $("#coupon_enabled_"+id).is(":checked") ? 1 : 0;*/
    var action     = $("#media_enabled_"+id).is(":checked") ? "select" : "remove";
    var type       = $("#media_type_"+id).val();
    //console.log(action);
    //ajax
    $.get("/mani/ajax/?do=selectItem&id=" + id+"&type=" + type+"&" + action);
}
