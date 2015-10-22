
var j = jQuery.noConflict();
j(document).ready(function() {

 j('#due_date').datepicker({
     
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