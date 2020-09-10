/**
* swRegistryImportXMLWindow - окно загрузки реестра-ответа в формате XML
*
* PromedWeb - The New Generation of Medical Statistic Software
* https://rtmis.ru/
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2020 RT MIS Ltd.
* @author       Stanislav Bykov
* @version      24.05.2020
* @comment      Префикс для id компонентов rixf (RegistryImportXMLWindow)
*
*
* @input data: Registry_id - ID реестра
*/

sw.Promed.swRegistryImportXMLWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryImportXMLWindow',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	title: 'Импорт реестра',
	width: 400,

	/* методы */
	doSave: function(options) {
		let
			form = this.TextPanel,
			win = this,
			params = {
				importType: win.importType,
				Registry_id: win.Registry_id
			};

		options = options || {};

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

		win.getLoadMask('Загрузка и анализ реестра. Подождите...').show();

		form.getForm().submit({
			params: params,
			failure: function(result_form, action) {
				win.getLoadMask().hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку позже.');
					}
				}
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				let answer = action.result;

				if ( answer ) {
					if ( !Ext.isEmpty(answer.Error_Msg) ) {
						
					}
					else if ( !Ext.isEmpty(answer.Registry_id) ) {
						win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body, {
							result: "Результат импорта: <b>" + answer.result + "</b>",
							recErrEvnCnt: "Случаев с ошибками:  <b>" + answer.recErrEvnCnt + "</b>",
							recErrCnt: "Всего записано ошибок:  <b>" + answer.recErrCnt + "</b>"
						});

						win.buttons[0].disable();

						win.callback();
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
				else {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							//form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
						title: 'Ошибка'
					});
				}
			}
		});

		return true;
	},
	onHide: Ext.emptyFn,
	show: function() {
		sw.Promed.swRegistryImportXMLWindow.superclass.show.apply(this, arguments);

		let win = this;

		if (
			!arguments[0]
			|| Ext.isEmpty(arguments[0].importType)
			|| Ext.isEmpty(arguments[0].Registry_id)
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + win.id + '.<br/>Не указаны необходимые входные параметры.',
				title: 'Ошибка'
			});
			win.hide();
			return false;
		}

		win.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext.emptyFn);
		win.importType = arguments[0].importType;
		win.onHide = (typeof arguments[0].onHide == 'function' ? arguments[0].onHide : Ext.emptyFn);
		win.Registry_id = arguments[0].Registry_id;

		win.buttons[0].enable();

		win.TextPanel.getForm().reset();

		win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body, {});

		if ( win.getLoadMask() ) {
			win.getLoadMask().hide();
		}
	},

	/* конструктор */
	initComponent: function() {
		let win = this;

		win.RegistryImportTpl = new Ext.Template([
			'<div>{result}</div><div>{recErrEvnCnt}</div><div>{recErrCnt}</div>'
		]);

		win.RegistryImportPanel = new Ext.Panel({
			bodyStyle: 'padding:2px',
			border: true,
			frame: false,
			height: 46,
			html: '',
			id: 'RegistryImportPanel',
			layout: 'fit'
		});

		win.TextPanel = new Ext.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			defaults: {
				allowBlank: false,
				anchor: '95%',
				msgTarget: 'side'
			},
			fileUpload: true,
			frame: true,
			id: 'RegistryImportTextPanel',
			labelWidth: 50,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' },
				{ name: 'result' },
				{ name: 'recErrEvnCnt' },
				{ name: 'recErrCnt' }
			]),
			url: '/?c=Registry&m=importRegistry',
			items: [{
				anchor: '95%',
				emptyText: 'Выберите файл реестра',
				fieldLabel: 'Реестр',
				name: 'RegistryFile',
				xtype: 'fileuploadfield'
			}, win.RegistryImportPanel ]
		});
		
		win.Panel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryImportPanelPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [
				win.TextPanel
			]
		});
		
		Ext.apply(win, {
			autoHeight: true,
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'refresh16',
				id: 'rixfOk',
				text: 'Загрузить'
			}, {
				text: '-'
			},
			HelpButton(win),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onTabElement: 'rixfOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.Panel
			]
		});

		sw.Promed.swRegistryImportXMLWindow.superclass.initComponent.apply(this, arguments);
	}
});