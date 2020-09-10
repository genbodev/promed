/**
* swRegistryImportXMLWindow - окно загрузки реестра-ответа в формате XML.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Станислав Быков
* @version      19.02.2019
* @comment      Префикс для id компонентов rixf (RegistryImportXMLWindow)
*
*
* @input data: Registry_id - ID реестра
*/

sw.Promed.swRegistryImportXMLWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryImportXMLWindow',
	title: langs('Импорт ФЛК'),
	width: 400,
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() {
		this.RegistryImportTpl = new Ext.Template([
			'<div>{recAll}</div>'
		]);
		
		this.RegistryImportPanel = new Ext.Panel({
			id: 'RegistryImportPanel',
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
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			id: 'RegistryImportTextPanel',
			labelWidth: 50,
			url: '/?c=Registry&m=importRegistryFromXml',
			reader: new Ext.data.JsonReader( {
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' },
				{ name: 'recAll' }
			]),
			defaults: {
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items: [{
				xtype: 'fileuploadfield',
				id: 'riwuRegistryFile',
				anchor: '95%',
				emptyText: 'Выберите файл реестра',
				fieldLabel: 'Реестр',
				name: 'RegistryFile'
			},
			this.RegistryImportPanel ]
		});
		
		this.Panel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryImportPanelPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				this.TextPanel
			]
		});
		
		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				id: 'rixfOk',
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'refresh16',
				text: 'Загрузить'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabElement: 'rixfOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.Panel
			]
		});

		sw.Promed.swRegistryImportXMLWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
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
	submit: function() {
		var form = this.TextPanel;
		var win = this;

		win.getLoadMask(langs('Загрузка и обработка файла...')).show();
		
		form.getForm().submit({
			params: {
				Registry_id: win.Registry_id
			},
			failure: function(result_form, action) {
				win.getLoadMask().hide();

				if ( action.result ) {
					
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				var answer = action.result;

				if ( answer ) {
					if ( answer.Registry_id ){
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: 'Сообщение'
						});

						win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body, {
							recAll: "Всего записей с ошибками: <b>" + answer.recAll + "</b>"
						});

						win.buttons[0].disable();

						win.callback();
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
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
	show: function() {
		sw.Promed.swRegistryImportXMLWindow.superclass.show.apply(this, arguments);

		var form = this;

		form.Registry_id = null;
		form.callback = Ext.emptyFn;
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

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			form.onHide = arguments[0].onHide;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			form.callback = arguments[0].callback;
		}

		form.getLoadMask().hide();
	}
});