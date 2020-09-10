<h3 class="rtmis-pageheader rtmis-pageheader--marginbottom">Редактирование объявления</h3>
<form action="" method="POST">
	<div class="row">
		<div class="col-md-9">
			<div class="form-group">
				<label class="form-label" for="body">
					Текст (поддерживается синтаксис <a target="_blank"
													   href="http://ru.wikipedia.org/wiki/Textile_%28%D1%8F%D0%B7%D1%8B%D0%BA_%D1%80%D0%B0%D0%B7%D0%BC%D0%B5%D1%82%D0%BA%D0%B8%29">Textile</a>)
				</label>
				<textarea class="form-control" required id="body" name="body" cols="130"
						  rows="10"><?php echo $body ?></textarea>
			</div>
			<div class="form-group">
				<label>Дата и время окончания публикации</label><br/>
				<select id="day" name="day">
					<?php for ($i = 1; $i <= 31; $i++) { ?>
						<option value="<?php echo sprintf('%02s', $i) ?>"><?php echo $i ?></option>
					<?php } ?>
				</select>
				<select id="month" name="month">
					<option value="01">Январь</option>
					<option value="02">Февраль</option>
					<option value="03">Март</option>
					<option value="04">Апрель</option>
					<option value="05">Май</option>
					<option value="06">Июнь</option>
					<option value="07">Июль</option>
					<option value="08">Август</option>
					<option value="09">Сентябрь</option>
					<option value="10">Октябрь</option>
					<option value="11">Ноябрь</option>
					<option value="12">Декабрь</option>
				</select>
				<select id="year" name="year">
					<?php for ($i = date('Y'); $i <= date('Y') + 1; $i++) { ?>
						<option value="<?php echo sprintf('%04s', $i) ?>"><?php echo $i ?></option>
					<?php } ?>
				</select>
				<span>&nbsp;-&nbsp;</span>
				<select id="hour" name="hour">
					<?php for ($i = 0; $i <= 23; $i++) { ?>
						<option value="<?php echo sprintf('%02s', $i) ?>"><?php echo sprintf('%02s', $i) ?></option>
					<?php } ?>
				</select>
				<select id="minute" name="minute">
					<option value="00">00</option>
					<option value="15">15</option>
					<option value="30">30</option>
					<option value="45">45</option>
				</select>
			</div>
			<input type="hidden" id="datetime_end" value="<?php echo $datetime_end ?>"/>
			<hr>
		</div>
	</div>
	<div class="row">
		<div class="col-md-9">
			<div class="d-flex">
				<button class="btn btn-primary ml-auto" type="submit">Сохранить</button>
			</div>
		</div>
	</div>
</form>
</form>

<script type="text/javascript">
    window.onload = function () {
        var datetime = document.getElementById('datetime_end').value;
        var d = new Date(datetime);

        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        var hour = d.getHours();
        var minute = d.getMinutes();

        ChangeSelectByValue('day', day);
        ChangeSelectByValue('month', month);
        ChangeSelectByValue('year', year);
        ChangeSelectByValue('hour', hour);
        ChangeSelectByValue('minute', minute);
    }
</script>
