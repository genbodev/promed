<div id="content_{id}" class="content_emk">
	<div class="wraptocenter">

		<?php // Ограничение исходного размера https://redmine.swan.perm.ru/issues/75349 ?>
		<img id="contentImg_{id}" src="{firstImgSrc}&rows=512" alt="...">
		<div class="default-size-link bottom_frame_title">
			<a href="{firstImgSrc}" target="_blank">Показать в исходном размере</a>
		</div>
        <div class="loopcontainer bottom_frame_title" style="display: none">
            <p style="color: white"> Фрейм <text id="current_loop_item_number">1</text> из <text class="loop_item_count">0</text> </p>
        </div>
		</br>
		<div class="loopcontainer " style="display: none">
			</br>
			<div class="load_loop_info">
				<p style="color: white"> Загружено <text id="loop_item_count_loaded"> </text> из <text class="loop_item_count">0</text> </p>
				<div id="progressbar"> </div>
			</div>
			</br>
			<div class="loop_buttons" style="display:none">
				<img class="play_button" src="/img/dicomViewer/play.png" style="height:100px;width: 100px;" onclick="sw.Promed.DicomViewer.playClick()">
				<img class="pause_button" src="/img/dicomViewer/pause.png" style="height:100px;width: 100px;" onclick="sw.Promed.DicomViewer.pauseClick()">
			</div>
			
		</div>
	</div>
</div>
<div class="sidebar1_emk">
	{instances}
</div>