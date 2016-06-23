var j = jQuery.noConflict();
j(document).ready(function() {

 j('#due_date').datepicker({
     
 dateFormat : 'mm-dd-yy'
 });
 j('#material_to_office_services').datepicker({
     
 dateFormat : 'mm-dd-yy'
 });
//alert(my_var);



 j('[name="EditTicket"]').submit(function(){
     var haserror = false;
     var agent = j("#agent").val();
     var status = j("#status").val();
     //var comment = j("#comments").val();
	 var comment = tinyMCE.activeEditor.getContent();
     var filUp = j("upload_file").val();
     
     
     j("#agent_label").removeClass("errorMsg");
     j("#comment_label").removeClass("errorMsg");
     j("#upload_file_label").removeClass("errorMsg");
     if(agent === ''){
         j("#agent_label").addClass("errorMsg");
         haserror = true;
     }
     if(status === '3' && j.trim(comment).length ==0){
		 tinyMCE.activeEditor.setContent('NO RESOLUTION ADDED');
         //alert('Insufficient comment for completed ticket');
         //j("#comment_label").addClass("errorMsg");
         //haserror = true;
     }
     if(filUp != ''){
         
         j("input:file").each(function(){             
             var files = j("#upload_file")[0].files;
             for(var i=0; i < files.length; i++){
                 var ext = files[i].name.split('.').pop();
                 if(j.inArray(ext,my_var)!= -1){
                     alert('You are not permitted to upload file ' + files[i].name + '. \n Files  with the extension ' + ext + ' are not permitted.');
                     j("#upload_file_label").addClass("errorMsg");
                     haserror = true;
                     //Clear input field for IE
                     $("#myFile").replaceWith($("#myFile").clone(true));
                     //Clear input field for real browsers
                     j("#upload_file").val("");
                 }
             }
         })
     }
     
     if(haserror)
          return false;
 });

  j('[name="AddTicket"]').submit(function(){
     
      var dueDate = j("#due_date").val();
      var desc = j("#request_desc").val();
      var title = j("#request_title").val();
      var filUp = j("upload_file").val();

      var haserror = false;
      j("#request_title_label").removeClass("errorMsg");
      j("#request_desc_label").removeClass("errorMsg");
      j("#request_due_date_label").removeClass("errorMsg");
      
      if(title === '' ){
          j("#request_title_label").addClass("errorMsg");
          haserror = true;
      }
      if(desc === ''){
          j("#request_desc_label").addClass("errorMsg");
          haserror = true;
      }
      if(filUp != ''){
         
         j("input:file").each(function(){             
             var files = j("#upload_file")[0].files;
             for(var i=0; i < files.length; i++){
                 var ext = files[i].name.split('.').pop();
                 if(j.inArray(ext,my_var)!= -1){
                     alert('You are not permitted to upload file ' + files[i].name + '. \n Files  with the extension ' + ext + ' are not permitted.');
                     j("#upload_file_label").addClass("errorMsg");
                     haserror = true;
                     //Clear input field for IE
                     $("#myFile").replaceWith($("#myFile").clone(true));
                     //Clear input field for real browsers
                     j("#upload_file").val("");
                 }
             }
         })
     }
      if(haserror)
          return false;

  });

  

 
});


function listbox_moveacross(sourceID, destID) {
    var src = document.getElementById(sourceID);
    var dest = document.getElementById(destID);

    for(var count=0; count < src.options.length; count++) {
 
        if(src.options[count].selected == true) {
                var option = src.options[count];
 
                var newOption = document.createElement("option");
                newOption.value = option.value;
                newOption.text = option.text;
                newOption.selected = true;
                try {
                         dest.add(newOption, null); //Standard
                         src.remove(count, null);
                 }catch(error) {
                         dest.add(newOption); // IE only
                         src.remove(count);
                 }
                count--;
        }
    }
}

function listbox_move(listID, direction) {
 
    var listbox = document.getElementById(listID);
    var selIndex = listbox.selectedIndex;
 
    if(-1 == selIndex) {
        alert("Please select an option to move.");
        return;
    }
 
    var increment = -1;
    if(direction == 'up')
        increment = -1;
    else
        increment = 1;
 
    if((selIndex + increment) < 0 ||
        (selIndex + increment) > (listbox.options.length-1)) {
        return;
    }
 
    var selValue = listbox.options[selIndex].value;
    var selText = listbox.options[selIndex].text;
    listbox.options[selIndex].value = listbox.options[selIndex + increment].value;
    listbox.options[selIndex].text = listbox.options[selIndex + increment].text;
 
    listbox.options[selIndex + increment].value = selValue;
    listbox.options[selIndex + increment].text = selText;
 
    listbox.selectedIndex = selIndex + increment;
}
/*
 * This function is used to select all items in a multiple select box.
 * It was built primarily for pushing data from one select box to another.
 * Values can only be used if the option is "selected". This sets all options in the
 * given select box to selected.
 *
 */
function selectItemsInSelectList(id) 
    { 
        //alert(id);
        selectBox = document.getElementById(id);

        for (var i = 0; i < selectBox.options.length; i++) 
        { 
             selectBox.options[i].selected = true; 
        } 
        return true;
    }

function changeRecStatus(id, status){
	if(id=="")
		return;
	else{
		if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById(id).innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","../../../PMA/CheersForPeers/ChangeStatus.php?id="+id+"&status="+status,true);
		xmlhttp.send();
	}
}