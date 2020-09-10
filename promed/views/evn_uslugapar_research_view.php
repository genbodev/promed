<div id="EvnUslugaparFunctRequest_wraper_{id}" class="EvnUslugaparFunctRequest_wraper wrapper">
    <div class="container">
        <div class="header hidden" id="slider_{id}">
			<div class="wrapperBox"><div class="wrapper">
				{series}
			</div></div>
          <div class="slider"  onclick="(function(evt){
			var hiddenClass = 'hidden',
			slider = $('#EvnUslugaparFunctRequest_wraper_{id} #slider_{id}');
			if (slider.hasClass(hiddenClass)) {
				slider.removeClass(hiddenClass)
				slider.animate({top:'0px'},1000)
			} else {
				slider.addClass(hiddenClass)
				slider.animate({top:'-150px'},1000)
			}
		  }())"></div>
        </div>
		<div id="EvnUslugaparFunctRequest_content_{id}" class="EvnUslugaparFunctRequest_wraper">
			{content}
		</div>
    </div>
</div>