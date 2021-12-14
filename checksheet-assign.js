var postdata={
	employee_id:'',
	task_id:''
};
$(".draggable").draggable({
	revert:'invalid',
	containment: "document",
	helper: "clone",
	cursor: "move"
});
$.each($(".droppable"),(k,v)=>{
	let accept=$(v).data('accept');
	$(v).droppable(
	{
		accept:"."+accept,
		classes: {
			"ui-droppable-active": "ui-state-active",
			"ui-droppable-hover": "ui-state-hover"
		},
		drop: function( event, ui ) {
			let p= $(ui.draggable[0]);
			var suretitile=p.data('suretitile');
			var ctext=p.data('ctext');
			swal({
				title: suretitile,
				text: ctext,
				icon: "info",
				buttons: true,
				dangerMode: true,
			})
			.then((willComfirm) => {
				if (willComfirm) {
					makeDrop(this,p);
				} 
			});
		}
	});
});

function makeDrop(ele,p){
	let html = `<p>${p.html()}</p>`;
	if(p.data('empid')){
		postdata.employee_id=p.data('empid');
	}

	postdata.task_id=$(ele).data("taskid");
	if( postdata.employee_id!='' && 
		postdata.task_id!=''){
		postdata['_token']=$("#token").val();
		var taskassigned=p.data('taskassigned');
	   var stayconfirm=p.data('stayconfirm');
	   var cbutton=p.data('cbutton');
	   var acceptbutton=p.data('acceptbutton');
	   var sorry=p.data('sorry');
	   var alreadyassign=p.data('alreadyassign');
	$.post("/task/assign",postdata,(res)=>{
		if(res==0){
			swal(sorry,alreadyassign,"info");
		}else{
			$( ele ).append(html);	
			swal({
				title:taskassigned,
				text:stayconfirm,
				icon:"info",
				buttons:[cbutton,acceptbutton],
				dangerMode: true,
			}).then((willComfirm) => {
				if(willComfirm)
				{
					window.location.href="/task/backtotask/1";
				}else{
					window.location.href="/task/backtotask/2";
				}
			});
		}
	})
}
}



window.getchecksheetbyclient = function(id,e,sorry,chknot,selct,chksingle) 
{
	if(id != '' ){
		let blankOption = '<option value="">'+selct+' '+chksingle+'</option>';
		$("#check_sheet_id").html(blankOption);
	$.get("/task/getchecksheetbyclient/"+id,(res)=>{
		if(res==1){
			swal(sorry, chknot,"info");
			$(e).val('');
		}else{
			let option = '<option value="">'+selct+' '+chksingle+'</option>';
			$.each(res,(k,v)=>{
				option+=`<option value="${v.id}">${v.name}</option>`;
			});
			$("#check_sheet_id").html(option);
		}
	});
}
}

window.freuqency=function(value){
	if(value=='monthly') {
		$("#dates").hide();
	} else{
		$("#dates").show();
	}  
	$.get("/client-checksheet/getfreuqency/"+value,(res)=>{
		if(res==1){
			$("#dynamictext").html('').hide();
		}else{
			$("#dynamictext").html(res).show();  
		}    
	})
}

window.removeasignedtask =function(id,tilte,text,stext,remp,sorry,someerror){
	swal({
		title:tilte,
		text:text,
		icon:"info",
		buttons:true,
		dangerMode: true,
	}).then((willComfirm) => {
		if(willComfirm)
		{
			$.get("/task/remove-assign-task/"+id,(res)=>{
				if(res==1){
							//swal("Success","The task has been removed.","success","false");
							swal({title:stext,text:remp,type:"success",buttons:false});
							window.location.reload();
						}else{
							swal(sorry,someerror,"error");
						}
					})
		}
	});
}



