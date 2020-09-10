/**
* swSQLDebugWindow - окно отладки SQL-запросов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE
* @version      седьмое число седьмого месяца 2013
*/
sw.Promed.swSQLDebugWindow = Ext.extend(sw.Promed.BaseForm, {
	title:'Отладка SQL-запросов',
	id: 'swSQLDebugWindow',
	autoHeight: true,
	width: 800,
	maximized: false,
	maximizable: true,
	resizable: true,
	show: function(callback) {
		sw.Promed.swSQLDebugWindow.superclass.show.apply(this, arguments);
		
		var base_form = this.formPanel.getForm();		
        base_form.getEl().dom.action = "/?c=Common&m=SQLDebug";
        base_form.getEl().dom.method = "post";
        base_form.getEl().dom.target = "_blank";
        base_form.standardSubmit = true;
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			layout: 'form',
			frame: true,
			items: [{
				xtype: 'fieldset',
				style: 'padding: 2px;',
				autoHeight: true,
				frame: false,
				title: 'База данных',
				items: [{
					border: false,
					layout: 'column',
					defaults: { bodyStyle: 'margin-left: 8px;' },
					items: [{
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'отчетная (search)',
							inputValue: 0,
							name: 'database_type',
							checked: true
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'реестровая (registry)',
							inputValue: 1,
							name: 'database_type'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'рабочая (default)',
							inputValue: 2,
							name: 'database_type'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'архивная (archive)',
							inputValue: 3,
							name: 'database_type'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'другая:',
							inputValue: 4,
							name: 'database_type'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'textfield',
							hideLabel: true,
							name: 'database_name'
						}]
					}]
				}]
			}, {
				xtype: 'fieldset',
				style: 'padding: 2px;',
				autoHeight: true,
				frame: false,
				title: 'Вид вывода результатов',
				items: [{
					border: false,
					layout: 'column',
					defaults: { bodyStyle: 'margin-left: 8px;' },
					items: [{
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'таблица',
							inputValue: 0,
							name: 'output_type',
							checked: true
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							xtype: 'radio',
							hideLabel: true,
							boxLabel: 'JSON',
							inputValue: 1,
							name: 'output_type'
						}]
					}]
				}]
			}, {
				autoHeight: true,
				xtype: 'fieldset',
				style: 'padding: 2px;',
				title: 'Запрос',
				items: [{
					anchor: '100%',
					height: 300,
					hideLabel: true,
					name: 'query',
					xtype: 'textarea'
				}]
			}, {
				autoHeight: true,
				xtype: 'fieldset',
				style: 'padding: 2px;',
				title: 'Параметры в виде JSON',
				items: [{
					anchor: '100%',
					height: 40,
					hideLabel: true,
					name: 'params',
					xtype: 'textarea'
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
						base_form.submit();
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
			items: [
				 win.formPanel
			]
		});
		
		sw.Promed.swSQLDebugWindow.superclass.initComponent.apply(this, arguments);
	}
});
