/**
* swUnionRegistryDBFWindow - окно выгрузки реестра в DBF.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2015 Swan Ltd.
* @author       Быков Станислав
* @version      13.10.2015
* @comment      Префикс для id компонентов urdbfw (UnionRegistryDBFWindow)
*
*
* @input data: Registry_id - ID регистра
*/

sw.Promed.swUnionRegistryDBFWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'UnionRegistryDBFWindow',
	layout: 'form',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	title: 'Формирование DBF',
	width: 400,

	initComponent: function() {
		this.TextPanel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryDBFTextPanel',
			html: 'Выгрузка данных реестра в формате DBF'
		});

		this.radioButtonGroup = new sw.Promed.Panel({
			items: [{
				xtype: 'radio',
				hideLabel: true,
				boxLabel: 'Скачать файл с сервера',
				inputValue: 0,
				id: 'urdbfw_radio_useexist',
				name: 'exporttype',
				checked: true
			}, {
				xtype: 'radio',
				hideLabel: true,
				boxLabel: 'Сформировать новый файл',
				inputValue: 1,
				id: 'urdbfw_radio_usenew',
				name: 'exporttype'
			}]
		});

		this.Panel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryDBFPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				this.TextPanel,
				this.radioButtonGroup
			]
		});

		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				id: 'urdbffOk',
				handler: function() {
					this.createDBF();
				}.createDelegate(this),
				iconCls: 'refresh16',
				text: 'Сформировать'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabElement: 'urdbffOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.Panel
			]
		});
		sw.Promed.swUnionRegistryDBFWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			if ( this.refresh )
				this.onHide();
		}
	},
	createDBF: function(addParams) {
		var Registry_id = this.Registry_id;
		var form = this;
		form.getLoadMask().show();
		
		var params = {
			Registry_id: Registry_id,
			OverrideExportOneMoreOrUseExist: 1
		};

		if (form.Panel.findById('urdbfw_radio_usenew').getValue()) {
			params.OverrideExportOneMoreOrUseExist = 2;
		}
		
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
				if (success) {
					if ( !response.responseText ) {
						var newParams = addParams;
						newParams.OverrideExportOneMoreOrUseExist = 1;
						newParams.onlyLink = 1;
						form.createDBF(newParams);
						return false;
					}

					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.Error_Code && result.Error_Code == '11' ) { // Уже есть выгруженный DBF
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								var newParams = addParams;

								if ( buttonId == 'yes' ) {
									newParams.OverrideExportOneMoreOrUseExist = 2;
								}
								else {
									newParams.OverrideExportOneMoreOrUseExist = 1;
								}

								form.createDBF(newParams);
							},
							msg: 'Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)',
							title: 'Подтверждение'
						});

						return false;
					}

					var alt = '', msg = '';

					form.refresh = true;

					if ( result.usePrevDBF ) {
						alt = 'Изменений с реестром не было произведено. Используется сохраненный DBF предыдущей выгрузки.';
						msg = ' (DBF предыдущей выгрузки).';
					}

					if ( result.Link ) {
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить реестр</a>'+msg;
						form.radioButtonGroup.hide();
						form.syncShadow();
						Ext.getCmp('urdbffOk').disable();
					}

					if ( result.success === false ) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						form.radioButtonGroup.hide();
						form.syncShadow();
						Ext.getCmp('urdbffOk').disable();
					}

					form.TextPanel.render();
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
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите. Идет формирование ' });
		}
		return this.loadMask;
	},
	show: function() {
		sw.Promed.swUnionRegistryDBFWindow.superclass.show.apply(this, arguments);

		var form = this;

		form.Panel.getForm().reset();

		form.onHide = Ext.emptyFn;
		form.refresh = false;
		form.Registry_id = null;

		Ext.getCmp('urdbffOk').enable();
		form.TextPanel.getEl().dom.innerHTML = 'Выгрузка данных реестра в формате DBF';
		form.TextPanel.render();
		
		if ( !arguments[0] || !arguments[0].Registry_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны необходимые входные параметры.',
				title: 'Ошибка'
			});

			this.hide();
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
			form.formUrl = '/?c=Registry&m=exportUnionRegistryToDBF';
		}

		this.radioButtonGroup.hide();
		this.syncShadow();

		form.getLoadMask('Получение данных по реестру').show();

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
					else if ( result.exportfile == 'exists' || result.exportfile == 'onlyexists' ) {
						// показываем выбор новый или старый файл
						form.radioButtonGroup.show();

						if ( result.exportfile == 'onlyexists' ) {
							form.Panel.findById('urdbfw_radio_usenew').hide();
						}
						else {
							form.Panel.findById('urdbfw_radio_usenew').show();
						}

						form.syncShadow();
					}
				}
			}
		});
	}
});