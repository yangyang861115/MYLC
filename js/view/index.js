// JavaScript Document 
$(document).ready(function() {
	"use strict";
    $('#formvals').formValidation({
        framework: 'bootstrap',
        icon: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            nameline: {
				verbose:false,
				icon: false,
				trigger: 'blur',
                validators: {
                    notEmpty: {
                        message: 'Your name is required'
                    }
                }
            },
            email: {
				verbose:false,
				icon: false,
				trigger: 'blur',
                   validators: {
                       notEmpty: {
                           message: 'The email is required and cannot be empty'
                       	},
						emailAddress: {
                        	message: 'The value is not a standard email address'
                    	},
						regexp: {
                            regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                            message: 'The value is not a valid email address'
		                }
                    }
				}
            }
    });
	$('#frmsbmt').on("click",function(e){
			$("#formvals").data('formValidation').validate();
			if(!$("#formvals").data('formValidation').isValid()) {
				return false; }
			e = e || window.event;
			e.preventDefault();
			$.ajax({
				url:"https://crucore.com/api.php",
				data:$("#formvals").serialize(),
				dataType:"JSON",
				type:"POST",
				success: function(data){
					if(data.success) {
						var msg = data.msg;
						$("#showresp").html(msg);
						// window.location="http://essentials24.org";
					  }
					else {
						alert("You are at Failure");
						var ply = new Ply({el:data.error});
						ply.open();
					}
				}
			});
	});
});
