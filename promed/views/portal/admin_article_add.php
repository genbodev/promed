<div class="edit-form">
	<h1>Создание статьи</h1>
	<form action="" method="POST">
		<label>Заголовок</label>
		<br />
		<input required type="text" id="title" name="title" value="" />
		<label>Текст (поддерживается синтаксис <a target="_blank" href="http://ru.wikipedia.org/wiki/Textile_%28%D1%8F%D0%B7%D1%8B%D0%BA_%D1%80%D0%B0%D0%B7%D0%BC%D0%B5%D1%82%D0%BA%D0%B8%29">Textile</a>)</label>
		<br />
		<textarea required id="body" name="body" cols="130" rows="30"></textarea>
		<br />
		<button type="submit">Сохранить</button>
		<!--<button type="button" onclick="previewNews()">Предварительный просмотр</button>-->
	</form>
</div>