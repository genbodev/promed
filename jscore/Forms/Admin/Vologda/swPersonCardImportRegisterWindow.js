/**
 * swPersonCardImportRegisterWindow - окно импорта регистра прикрепленного населения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan-it.ru)
 * @version			16.04.2019
 */
/*NO PARSE JSON*/

sw.Promed.swPersonCardImportRegisterWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonCardImportRegisterWindow',
	maximizable: false,
	resizable: false,
	modal: true,
	autoHeight: true,
	width: 450,
	layout: 'form',
	title: 'Импорт регистра прикрепленного населения',
	listeners: {
		'hide': function() {
			// полностью убиваем форму, инчае глючит поле файла
			this.destroy();
			window[this.objectName] = null;
			delete sw.Promed[this.objectName];
		}
	},
	doImport: function() {
		var wnd = this;
		var baseForm = wnd.FormPanel.getForm();
		var importButton = Ext.getCmp('PCIRW_ImportButton');

		importButton.disable();

		if (!baseForm.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					importButton.enable();
					wnd.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}
		
		var params = {};
		
		if (this.ARMType != 'superadmin') {
			params.Lpu_aid = getGlobalOptions().lpu_id;
		}

		wnd.ResultPanel.setData();
		wnd.getLoadMask('Импорт данных...').show();

		baseForm.submit({
			params: params,
			success: function(form, action) {
				wnd.getLoadMask().hide();
				importButton.enable();

				if (action.result && action.result.success) {
					wnd.ResultPanel.setData(action.result);
					wnd.callback();
					if (action.result.background) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							title: 'Файл загружен',
							msg: 'Обработка будет продолжена в фоновом режиме',
							fn: function () {
								wnd.hide();
							}
						});
					}
				}
			},
			failure: function(form, action) {
				wnd.getLoadMask().hide();
				importButton.enable();

				if (action.result && action.result.Error_Msg) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.ERROR,
						title: langs('Ошибка'),
						msg: action.result.Error_Msg
					});
				}
			}
		});
	},

	show: function() {
		var wnd = this;
		var baseForm = wnd.FormPanel.getForm();

		sw.Promed.swPersonCardImportRegisterWindow.superclass.show.apply(wnd, arguments);
		
		this.ARMType = arguments[0] && arguments[0].ARMType ? arguments[0].ARMType : null;
		this.callback = arguments[0] && arguments[0].callback ? arguments[0].callback : Ext.emptyFn;

		baseForm.reset();
		wnd.ResultPanel.setData();
	},

	initComponent: function() {
		var wnd = this;

		wnd.FormPanel = new Ext.form.FormPanel({
			id: 'PCIRW_FormPanel',
			bodyStyle: 'margin-top: 5px;',
			labelAlign: 'right',
			labelWidth: 40,
			fileUpload: true,
			url: '/?c=PersonCard&m=importPersonCardRegister',
			items: [{
				allowBlank: false,
				allowedExtensions: [ 'xml', 'dbf' ],
				xtype: 'fileuploadfield',
				fieldLabel: langs('Файл'),
				name: 'File',
				width: 370
			}]
		});

		var getFileNameByLink = function(link) {
			return link?link.split('/').pop():'';
		};

		wnd.ResultPanel = new Ext.Panel({
			id: 'PCIRW_ResultPanel',
			tpl: new Ext.XTemplate(
				'<tpl if="success">',
				'<p style="{style}">Было создано {createdcount} прикреплений</p>',
				'</tpl>',
				'<tpl if="loglink">',
				'<p style="{style}"><a target="_blank" href="{loglink}">Скачать файл {logname}</a></p>',
				'</tpl>'
			),
			defaultData: {
				style: 'margin-left: 7px; font-size: 12px;',
				success: false,
				infilecount: 0,
				createdcount: 0,
				loglink: '',
				logname: ''
			},
			setData: function(_data) {
				var me = wnd.ResultPanel;
				var data = Ext.apply({}, _data, me.defaultData);
				data.logname = getFileNameByLink(data.loglink);
				me.tpl.overwrite(me.body, data);
				wnd.syncShadow();
			}
		});

		wnd.MainPanel = new Ext.Panel({
			layout: 'form',
			frame: true,
			items: [
				wnd.FormPanel,
				wnd.ResultPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.MainPanel
			],
			buttons: [
				{
					id: 'PCIRW_ImportButton',
					text: langs('Импорт'),
					iconCls: 'add16',
					handler: function () {
						wnd.doImport();
					}
				}, {
					text: '-'
				},
				HelpButton(wnd, 1),
				{
					id: 'PCIRW_CancelButton',
					text: langs('Закрыть'),
					iconCls: 'close16',
					handler: function () {
						wnd.hide();
					}
				}
			]
		});

		sw.Promed.swPersonCardImportRegisterWindow.superclass.initComponent.apply(wnd, arguments);
	}
});