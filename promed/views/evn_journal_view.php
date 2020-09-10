<?php $arch = 0;?>
<div class="journalPanel">
	<div class="frame">
	<?php if (!empty($PersonEncrypHIV_Encryp)) {?>
			<h2>{PersonEncrypHIV_Encryp}</h2>
	<?php } elseif($isMseDepers) { ?>
			<h2>* * *</h2>
	<?php } else { ?>
		<h2>{Person_Fio}, {Person_BirthDay}</h2>
	<?php } ?>
		<div style="padding-left: 35px; margin-bottom: 5px;">
			<a class="EvnJournal_PersonNotice_On button" title="Вкл" style="display: none;"><span>Вкл</span></a>
			<a class="EvnJournal_PersonNotice_Off button" title="Выкл" style="display: none;"><span>Выкл</span></a>
			Уведомлять о событиях пациента
		</div>
		<ul class="list">
			<?php foreach($evn as $item){ 
			if(isset($item['archiveRecord'])&&$item['archiveRecord']==1&&$arch==0){echo"<li><h1>Архивные данные</h1></li><li></li>";$arch++;}
			echo"<li>
				<table>
					<col style='width: 140px;' /><col />

					<tr>
						<td class='datetime'>{$item['Evn_DT']}</td>
						<td class='evn'><a class='{$item['EvnClass_SysNick']}_{$item['Evn_id']}_{$item['EvnStatus_Nick']}_openEmk link'>{$item['Evn_Header']} ({$item['EvnStatus_Name']})</a></td>
					</tr>
					<tr><td></td><td class='body'>{$item['Evn_Body']}</td></tr>
				</table>
			</li>";
			 }?>
		</ul>
		<div class="navigation">
			<a class="EvnJournal_loadPrevPage button" style="float: left" title="Позднение"><span>&lt;&lt;Позднее</span></a>
			<a class="EvnJournal_loadNextPage button" style="float: right" title="Ранее"><span>Ранее&gt;&gt;</span></a>
		</div>
	</div>
</div>