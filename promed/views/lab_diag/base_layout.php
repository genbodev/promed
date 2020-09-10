<div class="printonly" style="display: block;">
	<p align="center" style="text-align:center"><span>@#@ПробаОрганизацияВыпИссл</span></p>
	<p align="center" style="text-align:center"><span>@#@ПробаОтделениеВыпИссл </span></p>
	<p align="center" style="text-align:center"><span>@#@ПротоколУслугиЗаголовок</span></p>
	<p align="center" style="text-align:center"><span>Дата взятия биоматериала: @#@ПробаДатаЗабора г.</span></p>
	<p>&nbsp;</p>
	<p style="text-align:justify"><span>Фамилия, И., О. @#@ФамилияИОПациента</span></p>
	<p style="text-align:justify"><span>Возраст @#@ВозрастПациента</span></p>
	<p style="text-align:justify"><span>Палата @#@ЗаявкаНаЛабИсслПалата</span></p>
	<p style="text-align:justify"><span>Медицинская карта N @#@НомерАмбКарты</span></p>
	<p>&nbsp;</p>
</div>
<div>
    <div class="template-block" id="block_resolution">
        <p class="template-block-caption" id="caption_resolution">
            <span style="font-weight: bold; font-size:10px;">Заключение: </span></p>
        <div class="template-block-data" id="data_resolution">
            <data class="data" id="resolution">
                <table border="1" cellpadding="1" cellspacing="0">
                    <thead>
                    <tr>
                        <th scope="col">
                            Код услуги</th>
                        <th scope="col">
                            Наименование услуги</th>
                        <th scope="col">
                            Ед. измерения</th>
                        <th scope="col">
                            Результат</th>
                        <th scope="col">
                            Норм. диапазон</th>
                        <th scope="col">
                            Критич. диапазон</th>
                        <th scope="col">
                            Комментарий</th>
                    </tr>
                    </thead>
                    <tbody>
                    {table_rows}
                    <tr>
                        <td>@#@{acode}_code</td>
                        <td>@#@{acode}_name</td>
                        <td>@#@{acode}_unit_of_measurement</td>
                        <td>@#@{acode}_value</td>
                        <td>@#@{acode}_norm_bound</td>
                        <td>@#@{acode}_crit_bound</td>
                        <td>@#@{acode}_commentrefvalues</td>
                    </tr>
                    {/table_rows}
                    </tbody>
                </table><br />
				Комментарий:<br />
				{EvnLabRequest_Comment}
            </data></div>
    </div>
</div>
<div>
	<p>&nbsp;</p>
	<p><span>Дата выдачи: @#@ТекущаяДата г.</span></p>
	<p align="right" style="text-align:right"><span>Подпись _________________ @#@ПробаФамилияИОВрачаВыпИссл </span></p>
</div>