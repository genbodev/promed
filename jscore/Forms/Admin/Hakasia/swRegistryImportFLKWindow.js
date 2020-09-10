/**
 * swRegistryImportFLKWindow - окно загрузки протокола ФЛК
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Registry
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Stanislav Bykov (savage@swan-it.ru)
 * @version      12.2018
 */

sw.Promed.swRegistryImportFLKWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'swRegistryImportFLKWindow',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	plain: true,
	resizable: false,
	title: langs('Импорт протоколов ФЛК'),
	width: 400,

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
		sw.Promed.swRegistryImportFLKWindow.superclass.show.apply(this, arguments);

		var form = this;

		form.getLoadMask().hide();

		form.Registry_id = null;
		form.onHide = Ext.emptyFn;

		form.buttons[0].enable();

		form.TextPanel.getForm().reset();

		if ( !arguments[0] || !arguments[0].Registry_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны необходимые входные параметры.',
				title: 'Ошибка'
			});
			form.hide();
			return false;
		}

		form.RegistryImportTpl.overwrite(form.RegistryImportPanel.body, {});

		if ( arguments[0].Registry_id ) {
			form.Registry_id = arguments[0].Registry_id;
		}

		if (arguments[0].KatNasel_SysNick)
		{
			form.KatNasel_SysNick = arguments[0].KatNasel_SysNick;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}
	},
	submit: function() {
		var form = this.TextPanel;
		var win = this;

		win.getLoadMask('Загрузка и анализ реестра. Подождите...').show();

		form.getForm().submit({
			params: {
				Registry_id: win.Registry_id,
				KatNasel_SysNick: win.KatNasel_SysNick
			},
			failure: function(result_form, action) {
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки акта произошла ошибка.<br/>Пожалуйста, повторите попытку позже.');
					}
				}

				win.getLoadMask().hide();
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				var answer = action.result;

				if ( answer ) {
					if ( answer.Registry_id ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: 'Сообщение'
						});

						win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body, {
							recAll: "Количество ошибок: <b>" + answer.recAll + "</b>"
						});

						win.buttons[0].disable();
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Во время выполнения операции загрузки акта произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},

	/* конструктор */
	initComponent: function() {
		var win = this;

		this.RegistryImportTpl = new Ext.Template([
			'<div>{recAll}</div>'
		]);

		this.RegistryImportPanel = new Ext.Panel({
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			height: 36,
			html: ''
		});

		this.TextPanel = new Ext.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			defaults: {
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			fileUpload: true,
			frame: true,
			labelWidth: 90,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' },
				{ name: 'recAll' }
			]),
			url: '/?c=Registry&m=importRegistryFLK',
			items: [{
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: 'Выберите файл',
				fieldLabel: 'Протокол ФЛК',
				name: 'RegistryFile'
			}, win.RegistryImportPanel ]
		});

		this.Panel = new Ext.Panel({
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
				handler: function() {
					win.doSave();
				},
				iconCls: 'refresh16',
				id: win.id + 'amifOk',
				text: 'Загрузить'
			}, {
				text: '-'
			},
			HelpButton(this), {
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: win.id + 'amifOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.Panel
			]
		});

		sw.Promed.swRegistryImportFLKWindow.superclass.initComponent.apply(this, arguments);
	}
});