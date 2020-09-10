<h3 class="rtmis-pageheader rtmis-pageheader--marginbottom">Создание объявления о семинаре</h3>
<form action="" method="POST">
	<div class="row">
		<div class="col-md-9">
			<div class="form-group">
				<label class="form-label" for="title">Заголовок</label>
				<input required class="form-control" type="text" id="title" name="title" value="" />
			</div>
			<div class="form-group">
				<label class="form-label" for="body">
					Текст (поддерживается синтаксис <a target="_blank"
													   href="http://ru.wikipedia.org/wiki/Textile_%28%D1%8F%D0%B7%D1%8B%D0%BA_%D1%80%D0%B0%D0%B7%D0%BC%D0%B5%D1%82%D0%BA%D0%B8%29">Textile</a>)
				</label>
				<textarea class="form-control" required id="body" name="body" cols="130" rows="10"></textarea>
			</div>

			<div class="form-group">
				<label>Дата и время проведения семинара</label><br/>
				<select id="begdt_day" name="begdt[day]" onchange="onChangeDate(this)">
					<?php for ($i = 1; $i <= 31; $i++) { ?>
						<option value="<?php echo sprintf('%02s', $i); ?>"><?php echo $i; ?></option>
					<?php } ?>
				</select>
				<select id="begdt_month" name="begdt[month]" onchange="onChangeDate(this)">
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
				<select id="begdt_year" name="begdt[year]" onchange="onChangeDate(this)">
					<?php for ($i = date('Y'); $i <= date('Y') + 1; $i++) { ?>
						<option value="<?php echo sprintf('%04s', $i); ?>"><?php echo $i; ?></option>
					<?php } ?>
				</select>
				<span>&nbsp;-&nbsp;</span>
				<select id="begdt_hour" name="begdt[hour]">
					<?php for ($i = 0; $i <= 23; $i++) { ?>
						<option value="<?php echo sprintf('%02s', $i); ?>"><?php echo sprintf('%02s', $i); ?></option>
					<?php } ?>
				</select>
				<select id="begdt_minute" name="begdt[minute]">
					<option value="00">00</option>
					<option value="15">15</option>
					<option value="30">30</option>
					<option value="45">45</option>
				</select>
			</div>
			<div class="form-group">
				<label>Дата и время окончания семинара</label><br/>
				<select disabled="disabled" id="enddt_day" name="enddt[day]">
					<?php for ($i = 1; $i <= 31; $i++) { ?>
						<option value="<?php echo sprintf('%02s', $i); ?>"><?php echo $i; ?></option>
					<?php } ?>
				</select>
				<select disabled="disabled" id="enddt_month" name="enddt[month]">
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
				<select disabled="disabled" id="enddt_year" name="enddt[year]">
					<?php for ($i = date('Y'); $i <= date('Y') + 1; $i++) { ?>
						<option value="<?php echo sprintf('%04s', $i); ?>"><?php echo $i; ?></option>
					<?php } ?>
				</select>
				<span>&nbsp;-&nbsp;</span>
				<select id="enddt_hour" name="enddt[hour]">
					<?php for ($i = 0; $i <= 23; $i++) { ?>
						<option value="<?php echo sprintf('%02s', $i); ?>"><?php echo sprintf('%02s', $i); ?></option>
					<?php } ?>
				</select>
				<select id="enddt_minute" name="enddt[minute]">
					<option value="00">00</option>
					<option value="15">15</option>
					<option value="30">30</option>
					<option value="45">45</option>
				</select>
			</div>
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

<script type="text/javascript">
    window.onload = function () {
        var d = new Date();

        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();

        ChangeSelectByValue('begdt_day', day + 1);
        ChangeSelectByValue('begdt_month', month);
        ChangeSelectByValue('begdt_year', year);

        ChangeSelectByValue('enddt_day', day + 1);
        ChangeSelectByValue('enddt_month', month);
        ChangeSelectByValue('enddt_year', year);
    };

    function onChangeDate(object) {
        if (object.id == 'begdt_day') {
            document.getElementById('enddt_day').value = object.value;
            return;
        }
        if (object.id == 'begdt_month') {
            document.getElementById('enddt_month').value = object.value;
            return;
        }
        if (object.id == 'begdt_year') {
            document.getElementById('enddt_year').value = object.value;
            return;
        }
    }
</script>