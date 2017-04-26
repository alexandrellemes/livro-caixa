
jQuery(document).ready(function() {
	
    /*
        Fullscreen background
    */
    $.backstretch([
                    "assets/img/backgrounds/2.jpg"
	              , "assets/img/backgrounds/3.jpg"
	              , "assets/img/backgrounds/1.jpg"
	             ], {duration: 3000, fade: 750});
    
    /*
        Form validation
    */
    $('.login-form input[type="text"], .login-form input[type="password"], .login-form textarea').on('focus', function() {
    	$(this).removeClass('input-error');
    });
    
    $('.login-form').on('submit', function(e) {
    	
    	$(this).find('input[type="text"], input[type="password"], textarea').each(function(){
    		if( $(this).val() == "" ) {
    			e.preventDefault();
    			$(this).addClass('input-error');
    		}
    		else {
    			$(this).removeClass('input-error');
    		}
    	});
    	
    });
    
//    $('#valor').maskMoney();
    
});

function recoverPassword() {

	var emailInput = $('#emailInput').val();

	if (!emailInput) {
		alert('E-Mail deve ser informado!');
	}
	
	$.ajax({
		method : "POST",
		url : "process.php",
		data : {
			emailInput : emailInput
		},
		success: function(data) { alert(data);
	         var $title = $('<h1>').text(data.talks[0].talk_title);
	         var $description = $('<p>').text(data.talks[0].talk_description);
	         $('#info')
	            .append($title)
	            .append($description);
	    },
	    error: function(data) {
	          $('#info').html('<p>An error has occurred</p>');
	       },
	});
}
