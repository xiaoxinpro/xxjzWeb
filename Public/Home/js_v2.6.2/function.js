
function WebApp_Login(str,u,p){
    //alert(str);
    window.webkit.messageHandlers.webAppLogin.postMessage({body:str,user:u,pass:p});
}

function WebApp_Logout(u){
    window.webkit.messageHandlers.webAppLogout.postMessage({user:u});
}

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

// alert("你好！");
