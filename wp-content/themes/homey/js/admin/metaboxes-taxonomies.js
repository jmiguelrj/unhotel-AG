(function($) {
    	
        $(document).ready(function ($) {
			"use strict";

    	/* Color picker metabox handle */
    	if($('.homey_colorpicker').length){
    		$('.homey_colorpicker').wpColorPicker();

    		$('a.homey_colorpick').on('click', function(e){
    			e.preventDefault();
    			$('.homey_colorpicker').val($(this).attr('data-color'));
    			$('.homey_colorpicker').change();
    		});	
    	}

    	homey_toggle_color_picker();
    	
    	$("body").on("click", "input.color-type", function(e){
			homey_toggle_color_picker();
		});
			   
    	function homey_toggle_color_picker(){
    		var picker_value = $('input.color-type:checked').val();
    		if(picker_value == 'custom'){
    			$('#homey_color_wrap').show();
    		} else {
    			$('#homey_color_wrap').hide();
    		}
    	}

    });
    	
})(jQuery);