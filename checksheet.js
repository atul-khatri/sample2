
var total_checksheet=5;
  window.addMoreChecksheet = function(addmoremsg) {
    let currentChecksheet = $("#activeRow").val();
    if(currentChecksheet < total_checksheet){
      let no =  $("#tbody").find("tr").length+1;
      $("#tbody").find(".show").removeClass('show');
      let showclass='show';
      $("#tbody").append(html.replace(/\[no\]/g,no).replace(/\[showclass\]/g,showclass).replace(/\[ino\]/g,1).replace(/disabled/g,''));
      var files2Uploader = $("#customFile"+no).fileUploader(files_to_upload, "customFile"+no);
      $("#activeRow").val(parseInt(currentChecksheet)+1);
    }else{
      swal({
        title: "Warning",
        text: addmoremsg,
        icon: "info",
        buttons: false,
        dangerMode: true,
      });
    }
  }
  window.removeChecksheet= function(ele) {
      $(ele).closest("tr").remove();
      let currentChecksheet = $("#activeRow").val();
      if(currentChecksheet > 0){
        $("#activeRow").val(parseInt(currentChecksheet)-1);
      }
        $("#tbody").find(".show").removeClass('show');
        $.each($("#tbody").find("tr"),(k,v)=>{
          $(v).find("td:first-child").find('.unitclass').html(`Unit ${(k+1)}`); 
        })

        var lastTR = $("#tbody").find("tr:last-child");
        lastTR.find('.collapse').addClass('show');
     
  }

  window.showChecksheet= function(val) {
    
    if($('#'+val).hasClass('show')!=true){
    $("#tbody").find(".show").removeClass('show');
   
    }
    else{
      $('#'+val).addClass('show');
    }
    
}
var total_activity=6;
  window.addMoreCheckslist = function(ele,chkmsgact) {
    let h = $(ele).closest("div.row").next("div").html();
    let no = $(ele).closest("div.form-group").find(".mb-2").length;
    if(no < total_activity){
      let div = document.createElement("div");
      div.innerHTML=h;
      div.setAttribute('class','row mb-2 remove'+no);
      $(div).find("input:first-child").val('');
      $(div).find("button").attr('onclick',`removechecklist(this,'remove${no}')`);
      $(ele).closest("div.form-group").append(div);
    }else{
      swal({
        title: "Warning",
        text: chkmsgact,
        icon: "info",
        buttons: false,
        dangerMode: true,
      });
    }
  }

  window.getChecksheet = function(industry_id) {
    $.get("/checksheet/getchecksheet/"+industry_id,(res)=>{
      if(res==1){
         swal("Sorry!", "Check-sheet Template Not Available!","info");
    }else{
     // console.log(res);
     $("#maintable").html(res);
    }
    })
  }

  
window.getPreviouslyCreated = function(query=''){
  let params = "sortby=id&sorttype=asc&query="+query;
  let modal = $("#checksheetmodel");
  $.get("/client-checksheet/getchecksheetprevious?"+params,(res)=>{
    //console.log(res);
    modal.find("#previoustext").html(res);
    modal.modal('show');
  })
}
var template_id;
window.selectTemplate = function(ele,checksheet_id){
  $(".modal-body").find(".previous-checksheet-selected").removeClass("previous-checksheet-selected");
  $(ele).addClass("previous-checksheet-selected");
  template_id=checksheet_id;
  $("#addtemplatebtn").removeAttr("disabled");
}

window.addTemplateToView = function(viewid){
  $.get("/client-checksheet/getchecksheetpreviousbyid/"+template_id,(res)=>{
     if(res==1){
         swal("Sorry!", "Check-sheet Template Not Available!","info");
    }else{
     $("#maintable").html(res);
     $("#checksheetmodel").modal("hide");
    }
  })
}


window.getSubClient = function(client_id,e,sorry,chknot,selct,chksingle,choosesc) 
{
  $("#sub_client_id").html('<option value="">'+sorry+' '+chknot+'</option>');
  $("#sub_client_deptid").html('<option value="">'+choosesc+'</option>');

	if(client_id != '' ){
    $.get("/sub-client/getsubclientid/" + client_id, function (res) {
		if(res==1){
			swal(sorry, chknot,"info");
			$(e).val('');
		}else{
			let option = '<option value="">'+selct+' '+chksingle+'</option>';
			$.each(res,(k,v)=>{
				option+=`<option value="${v.id}">${v.name}</option>`;
			});
      $("#sub_client_id").html(option);

		}
	});
}
}

window.getSubClientDepartment = function(sub_client_id,e,sorry,chknot,selct,chksingle,choosesc) 
{
  $("#sub_client_deptid").html('<option value="">'+sorry+' '+chknot+'</option>');

	if(sub_client_id != '' ){
    $.get("/sub-client-department/get-sub-client-department/" + sub_client_id, function (res) {
		if(res==1){
			swal(sorry, chknot,"info");
		//	$(e).val('');
		}else{
			let option = '<option value="">'+selct+' '+chksingle+'</option>';
			$.each(res,(k,v)=>{
				option+=`<option value="${v.id}">${v.name}</option>`;
			});
      $("#sub_client_deptid").html(option);

		}
	});
}
}

let modalId = $('#image-gallery');
$(document).ready(function () {
  loadGallery(true, 'button.thumbnail');
  //This function disables buttons when needed
  function disableButtons(counter_max, counter_current) {
    $('#show-previous-image, #show-next-image').show();
    if(counter_max == 1){
      $('#show-previous-image, #show-next-image').hide();
    }
    else if (counter_max === counter_current) {
      $('#show-next-image').hide();
    } else if (counter_current === 1) {
      $('#show-previous-image').hide();
    }
  }
  function loadGallery(setIDs, setClickAttr) {
    let current_image,selector,counter = 0;
    $('#show-next-image, #show-previous-image').click(function () {
        if ($(this).attr('id') === 'show-previous-image') {
          current_image--;
        } else {
          current_image++;
        }
        selector = $('[data-image-id="' + current_image + '"]');
        updateGallery(selector);
      });
    function updateGallery(selector) {
      let $sel = selector;
      current_image = $sel.data('image-id');
      $('#image-gallery-title').text($sel.data('title'));
      $('#image-gallery-image').attr('src', $sel.data('image'));
      disableButtons(counter, $sel.data('image-id'));
    }
    if (setIDs == true) {
      $('[data-image-id]').each(function () {
          counter++;
          $(this).attr('data-image-id', counter);
        });
    }
    $(setClickAttr).on('click', function () {
        updateGallery($(this));
    });
  }
});

window.checkActivity = function(ele,msg){
    var activity_val = $(ele).val();
    var count = 0;
    if(activity_val != ''){
      $(ele).closest(".div_of_activity").find(".text_activity").each(function(){

        if($(this).val().toLowerCase() == activity_val.toLowerCase() ){
          count++;
          if(count > 1){
            swal({
              title: "Warning",
              text: msg,
              icon: "info",
              buttons: false,
              dangerMode: true,
            });
          $(this).focus();
      }
        }
      });
      
    }
}


window.checkUnitname = function(ele,msg){
  var activity_val = $(ele).val();
  var count = 0;
  if(activity_val != ''){
    $(ele).closest("#tbody").find(".text_unitname").each(function(){

      if($(this).val().toLowerCase() == activity_val.toLowerCase() ){
        count++;
        if(count > 1){
          swal({
            title: "Warning",
            text: msg,
            icon: "info",
            buttons: false,
            dangerMode: true,
          });
        $(this).focus();
    }
      }
    });
    
  }
}
