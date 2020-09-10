/**
* swExportRegistryInogWindow - окно выгрузки реестра по иногородним в DBF.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2015 Swan Ltd.
* @author       Быков Станислав
* @version      14.12.2017
*
* @input data: Registry_id - ID регистра
*/

sw.Promed.swExportRegistryInogWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'ExportRegistryInogWindow',
	layout: 'form',
	listeners: {
		'hide': function() {
			if ( this.refresh )
				this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	title: 'Экспорт сведений по иногородним',
	width: 400,

	/* конструктор */
	initComponent: function() {
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryDBFTextPanel',
			html: 'Выгрузка данных в формате DBF'
		});

		wnd.Panel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryDBFPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				wnd.TextPanel
			]
		});

		Ext.apply(wnd, {
			autoHeight: true,
			buttons: [{
				id: wnd.id + 'BtnOk',
				handler: function() {
					wnd.createDBF();
				},
				iconCls: 'refresh16',
				text: 'Экспорт'
			}, {
				text: '-'
			},
			HelpButton(wnd),
			{
				handler: function() {
					wnd.hide();
				},
				iconCls: 'cancel16',
				onTabElement: wnd.id + 'BtnOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				wnd.Panel
			]
		});

		sw.Promed.swExportRegistryInogWindow.superclass.initComponent.apply(this, arguments);
	},

	/* методы */
	createDBF: function(addParams) {
		var Registry_id = this.Registry_id;
		var form = this;

		form.getLoadMask().show();
		
		var params = {
			Registry_id: Registry_id
		};

		if (addParams != undefined) {
			for(var par in addParams) {
				params[par] = addParams[par];
			}
		} else {
			addParams = [];
		}
		
		Ext.Ajax.request({
			url: form.formUrl,
			params: params,
			timeout: 1800000,
			callback: function(options, success, response) {
				form.getLoadMask().hide();

				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.success === false ) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
					}
					else if ( result.Link ) {
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" href="' + result.Link + '">Скачать и сохранить реестр</a>';
					}

					form.TextPanel.render();

					form.syncShadow();
					form.buttons[0].disable();
				}
				else {
					var result = Ext.util.JSON.decode(response.responseText);
					form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
					form.TextPanel.render();
				}
			}
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите. Идет формирование...' });
		}
		return this.loadMask;
	},
	show: function() {
		sw.Promed.swExportRegistryInogWindow.superclass.show.apply(this, arguments);

		var form = this;

		form.onHide = Ext.emptyFn;
		form.Registry_id = null;

		form.buttons[0].enable();
		form.TextPanel.getEl().dom.innerHTML = 'Выгрузка данных в формате DBF';
		form.TextPanel.render();
		
		if ( !arguments[0] || !arguments[0].Registry_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Не указаны необходимые входные параметры.',
				title: 'Ошибка'
			});

			form.hide();

			return false;
		}

		if ( arguments[0].Registry_id ) {
			form.Registry_id = arguments[0].Registry_id;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( arguments[0].url ) {
			form.formUrl = arguments[0].url;
		}
		else {
			form.formUrl = '/?c=Registry&m=exportRegistryInog';
		}

		form.syncShadow();

		/*form.getLoadMask('Получение данных по реестру').show();

		Ext.Ajax.request({
			url: form.formUrl + 'CheckExist',
			params: {
				Registry_id: form.Registry_id
			},
			callback: function(options, success, response) {
				form.getLoadMask().hide();

				if ( success && response.responseText ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.exportfile == 'inprogress' ) {
						sw.swMsg.alert('Сообщение', 'Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).', function() {
							form.hide();
						});
					}
				}
			}
		});*/
	}
});