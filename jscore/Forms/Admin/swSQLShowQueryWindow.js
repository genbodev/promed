/**
* swSQLShowQueryWindow - окно отладки SQL-запросов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE
* @version      13.07.2013
*/
sw.Promed.swSQLShowQueryWindow = Ext.extend(sw.Promed.BaseForm, {
	title:'Отладка SQL-запросов',
	id: 'swSQLShowQueryWindow',
	height: 500,
	width: 700,
	maximized: false,
	maximizable: true,
	resizable: true,
	show: function(callback) {
		sw.Promed.swSQLShowQueryWindow.superclass.show.apply(this, arguments);
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			region: 'center',
			layout: 'border',
			items: [{
				xtype: 'fieldset',
				region: 'north',
				style: 'padding: 2px;',
				height: 45,
				frame: false,
				title: 'Запрос',
				items: [{
					name: 'query',
					anchor: '100%',
					hideLabel: true,
					xtype: 'textfield'
				}]
			}, {
				xtype: 'fieldset',
				style: 'padding: 2px;',
				region: 'center',
				title: 'Результат',
				layout: 'border',
				items: [{
					hideLabel: true,
					name: 'queryresult',
					region: 'center',
					xtype: 'textarea'
				}]
			}, {
				xtype: 'fieldset',
				region: 'south',
				style: 'padding: 2px;',
				height: 50,
				frame: false,
				title: 'Дополнительно',
				items: [{
					border: false,
					layout: 'column',
					bodyStyle: 'background:#DFE8F6;',
					defaults: { bodyStyle: 'background:#DFE8F6; margin-left: 8px;' },
					items: [{
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'параметры в запросе',
							inputValue: 1,
							name: 'return_type',
							checked: true
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'параметры в виде JSON',
							inputValue: 2,
							name: 'return_type'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'параметры в виде массива',
							inputValue: 3,
							name: 'return_type'
						}]
					}]
				}]
			}]
		});
		
		Ext.apply(this, {
			buttons: [{
				text      : 'Выполнить',
				tabIndex  : -1,
				tooltip   : 'Выполнить запрос',
				iconCls   : 'actions16',
				handler   : function() {
					var base_form = win.formPanel.getForm();
					if (!Ext.isEmpty(base_form.findField('query').getValue())) {
						// извелкаем параметры
						var query = base_form.findField('query').getValue();

						var return_type = base_form.findField('return_type').getGroupValue();

						var url = '';
						var indexOfQ = query.indexOf('?',0);
						if (indexOfQ > 0) {
							url = query.substr(0, indexOfQ + 1) + 'sql_debug='+return_type; // урл до знака вопроса
							query = query.substr(indexOfQ + 1);
						}
						var params = query + '&sql_debug='+return_type;
						
						// к урлу нужно добавить параметр &c= и параметр &m=
						// ищем &c=
						query = base_form.findField('query').getValue();
						var indexOfQ = query.indexOf('?c=',0);
						if (indexOfQ > 0) {
							query = query.substr(indexOfQ + 3);
							// в оставщемся ищем &, до него берём строку или полностью если нет
							var indexOfAm = query.indexOf('&',0);
							if (indexOfAm > 0) {
								query = query.substr(0, indexOfAm);
							}
							url = url + '&c=' + query;
						}
						
						// ищем &m=
						query = base_form.findField('query').getValue();
						var indexOfQ = query.indexOf('&m=',0);
						if (indexOfQ > 0) {
							query = query.substr(indexOfQ + 3);
							// в оставщемся ищем &, до него берём строку или полностью если нет
							var indexOfAm = query.indexOf('&',0);
							if (indexOfAm > 0) {
								query = query.substr(0, indexOfAm);
							}
							url = url + '&m=' + query;
						}
												
						Ext.Ajax.request(
						{
							url: url,
							params: params,
							callback: function(options, success, response) 
							{
								if (response.responseText)
								{
									base_form.findField('queryresult').setValue(response.responseText);
								}
							}
						});
					} else {
						sw.swMsg.alert('Ошибка', 'Введите запрос', function() { base_form.findField('query').focus(true); });
					}
				}
			}, {
				text: '-'
			}, {
				text      : 'Отмена',
				tabIndex  : -1,
				tooltip   : 'Отмена',
				iconCls   : 'cancel16',
				handler   : function() {
					win.hide();
				}
			}],
			layout: 'border',
			items: [
				 win.formPanel
			]
		});
		
		sw.Promed.swSQLShowQueryWindow.superclass.initComponent.apply(this, arguments);
	}
});
