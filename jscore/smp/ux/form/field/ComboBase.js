/**
 * Базовый компонент комбобокса для СМП
 */
Ext6.define('smp.ux.form.field.ComboBase', {
	extend: 'Ext6.form.field.ComboBox',
	
	/**
	 * Установка значения
	 * Перед тем как задать значение, проверяет загружены ли данные в хранилище,
	 * если нет, выполняет загрузку, после чего задает значение.
	 */
	setValue: function (value, doSelect) {
		// При инициализации компонента, value может быть undefined, поэтому незачем лишний раз выполнять загрузку хранилища
		// this.store.loaded кастомный флаг для проверки того было ли загружено хранилище
		// Проверяем только для режима 'remote'
		if (typeof value != 'undefined' && !this.store.loaded && this.queryMode == 'remote') {
//			this.store.addListener('load', function () {
//				this.store.loaded = true;
//				this.setValue(value, doSelect);
//			}, this);
//			this.store.load();
			// Заменил прослушивание события, закомментированного выше, на колбак,
			// т.к. при повторном вызове метода, после закрытия/открытия окнаб
			// содержащего комбобокс, харнилище, внутри колбака события, ставится null.
			this.store.load({
				scope: this,
				callback: function(records, operation, success){
					this.store.loaded = true;
					this.setValue(value, doSelect);
				}
			});
		}
		this.callParent(arguments);

		return this;
	}
});