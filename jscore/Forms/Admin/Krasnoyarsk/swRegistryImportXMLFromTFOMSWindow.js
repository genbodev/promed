/**
* swRegistryImportXMLFromTFOMSWindow - окно загрузки реестра-ответа в формате XML.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Stanislav Bykov
* @version      07.12.2019
* @comment      Префикс для id компонентов RIXFT (RegistryImportXMLFromTFOMSWindow)
*
*
* @input data: Registry_id - ID реестра
*/

sw.Promed.swRegistryImportXMLFromTFOMSWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryImportXMLFromTFOMSWindow',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	title: langs('Импорт результата проверки ТФОМС'),
	width: 450,

	/* методы */
	doSave: function() {
		var form = this.TextPanel;

		if ( !form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.submit();

		return true;
	},
	getLoadMask: function(MSG) {
		if ( MSG ) {
			delete(this.loadMask);
		}

		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}

		return this.loadMask;
	},
	onHide: Ext.emptyFn,
	show: function() {
		sw.Promed.swRegistryImportXMLFromTFOMSWindow.superclass.show.apply(this, arguments);

		var form = this;

		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.Registry_id = null;

		form.buttons[0].enable();
		form.TextPanel.getForm().reset();

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

		form.RegistryImportTpl.overwrite(form.RegistryImportPanel.body, {});

		if ( typeof arguments[0].callback == 'function' ) {
			form.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( arguments[0].Registry_id ) {
			form.Registry_id = arguments[0].Registry_id;
		}

		if ( form.getLoadMask() ) {
			form.getLoadMask().hide();
		}
	},
	submit: function() {
		var
			win = this,
			base_form = win.TextPanel.getForm();

		win.getLoadMask('Загрузка и анализ реестра. Подождите...').show();
		win.buttons[0].disable();

		base_form.submit({
			params: {
				Registry_id: win.Registry_id
			},
			failure: function(result_form, action) {
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}

				win.buttons[0].enable();
				win.getLoadMask().hide();
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				var answer = action.result;

				if ( answer ) {
					if ( answer.Registry_id ) {
						win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body, {
							flkResult: answer.recTFOMSErr == -1 && answer.recTFOMSRej == -1 ? 'Импорт результатов ФЛК/МЭК не выполнен' : 'Импорт результатов ФЛК/МЭК был выполнен успешно',
							bdzResult: answer.recBDZAll == -1 ? 'Импорт результатов проверки по БДЗ не выполнен' : 'Импорт результатов проверки по БДЗ был выполнен успешно',
							recTFOMSRej: answer.recTFOMSRej >= 0 ? "случаев с ошибками: <b>" + answer.recTFOMSRej + "</b>" : "",
							recTFOMSErr: answer.recTFOMSErr >= 0 ? "всего записано ошибок: <b>" + answer.recTFOMSErr + "</b>" : "",
							recTFOMSWarn: answer.recTFOMSWarn >= 0 ? "всего записано предупреждений: <b>" + answer.recTFOMSWarn + "</b>" : "",
							recBDZAll: answer.recBDZAll >= 0 ? "проверено пациентов по БДЗ: <b>" + answer.recBDZAll + "</b>" : ""
						});

						win.callback();
					}
					else {
						win.buttons[0].enable();

						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: langs('Ошибка')
						});
					}
				}
			}
		});
	},

	/* конструктор */
	initComponent: function() {
		var win = this;

		win.RegistryImportTpl = new Ext.Template([
			'<div><b>{flkResult}</b></div><div>{recTFOMSRej}</div><div>{recTFOMSErr}</div><div>{recTFOMSWarn}</div>'
				+ '<div>&nbsp;</div>'
				+ '<div><b>{bdzResult}</b></div><div>{recBDZAll}</div>'
		]);

		win.RegistryImportPanel = new Ext.Panel({
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			height: 110,
			html: ''
		});

		win.TextPanel = new Ext.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			labelWidth: 50,
			url: '/?c=Registry&m=importRegistryFromTFOMS',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' },
				{ name: 'recTFOMSRej' },
				{ name: 'recTFOMSErr' },
				{ name: 'recTFOMSWarn' },
				{ name: 'recBDZAll' }
			]),
			defaults: {
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items: [{
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: langs('Выберите файл реестра'),
				fieldLabel: langs('Реестр'),
				name: 'RegistryFile'
			}, win.RegistryImportPanel ]
		});

		win.Panel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				win.TextPanel
			]
		});

		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				id: 'RIXFT_Ok',
				handler: function() {
					win.doSave();
				},
				iconCls: 'refresh16',
				text: langs('Загрузить')
			}, {
				text: '-'
			},
			HelpButton(this), {
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'RIXFT_Ok',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.Panel
			]
		});

		sw.Promed.swRegistryImportXMLFromTFOMSWindow.superclass.initComponent.apply(this, arguments);
	}
});