<?php
    if ( empty($EvnSection_IsPriem) || $EvnSection_IsPriem == 1 ) {
?>
<div id="BleedingCardList_{pid}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BleedingCardList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BleedingCardList_{pid}_toolbar').style.display='none'">
	<div class="clear">

		<div class="data-table">
			<div class="caption">
				<h2>Карты наблюдений для оценки кровотечения</h2>
				<div id="BleedingCardList_{pid}_toolbar" class="toolbar">
					<a id="BleedingCardList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
				</div>
			</div>

			<table>

				<col class="first last" />
				<col class="toolbar"/>

				{items}

			</table>

		</div>

	</div>
</div>
<?php
    }
?>