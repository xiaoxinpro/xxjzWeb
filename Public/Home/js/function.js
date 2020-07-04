
function SelectChange(){ 
    var objS = document.getElementById("pid"); 
    var grade = objS.options[objS.selectedIndex].value; 
    var url = grade;
    location.href=url;
} 

function isDelete(id){
    var r=confirm("是否要删除？");
    if (r===true)
    {
        location.href = id;
    }
}

//显示提示信息
function ShowAlert(msg){
    alert(msg);
}

function ShowAlert(msg,url){
    alert(msg);
    location.href = url;
}

//显示判断框
function ShowConfirm(msg,url){
    var i = confirm(msg);
    if(i === true){
        location.href = url;
    }
}

//改变分类下拉列表(json数据,类别框id,分类框id)
function ChangClass(s,sid,oid){
    var arr = JSON.parse(decodeURI(s)); 
    var type = document.getElementById(sid).value;
    var acclass = arr[type];
    //alert(acclass["8"]);
    var obj=document.getElementById(oid);
    //删除分类select中的选项
    obj.options.length=0;
    //添加分类选项
    for(id in acclass){
        obj.options.add(new Option(acclass[id],id));
    }
    //分类框获取焦点
    obj.focus();
    obj.click();
}

//数值格式化函数(数值,保留几位小数,小数点符号,千分位符号)
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 2 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
        var k = Math.pow(10, prec);
        return '' + Math.ceil(n * k) / k;
    };

    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    var re = /(-?\d+)(\d{3})/;
    while(re.test(s[0])) {
        s[0] = s[0].replace(re, "$1" + sep + "$2");
    }

    if((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function FormPost(url, args) {
    var body = $(document.body);
    var form = $('<form method="post" style="display: none;"></form>');
    var input;
    form.attr({"action":url});
    $.each(args,function(key,value){ 
        input = $("<input type='hidden'>");
        input.attr({"name":key});
        input.val(value);
        form.append(input);
    });
    form.appendTo(document.body);
    form.submit();
    // document.body.removeChild(form[0]);
}
