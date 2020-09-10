/**
* swRoutingMapAddWindow - Добавление подчиненной МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Sharipov Fidan
* @version      11.2019

*/
sw.Promed.swRoutingMapAddWindow = Ext.extend(sw.Promed.BaseForm, {
	modal: true,
	width: 400,
	height: 138,
	resizable: false,
	closeAction: 'hide',
	buttonAlign: 'right',
	url: '/?c=RoutingMap&m=save',
	title: langs('Добавление подчиненной МО'),
	listeners: {
		hide: function() {
			this.doReset();
		}
	},
	initComponent: function () {
		let wnd = this;

		let formItems = [
			{
				allowBlank: false,
				fieldLabel: lang['mo'],
				hiddenName: 'Lpu_id',
				anchor: '100%',
				xtype: 'swlpucombo'
			} , new sw.Promed.SwBaseLocalCombo({
			hiddenName: 'RoutingLevel_id',
			fieldLabel: 'Уровень',
			displayField: 'RoutingLevel_name',
			valueField: 'RoutingLevel_id',
			allowBlank: false,
			anchor: '100%',
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'RoutingLevel_id'
				}, [
					{ name: 'RoutingLevel_id', type: 'int' },
					{ name: 'RoutingLevel_name', type: 'string' }
				]),
				listeners: {
					load: function (store, records) {
						let value = records[records.length - 1].get('RoutingLevel_id');
						wnd.formPanel.getForm().findField('RoutingLevel_id').setValue(value);
					}
				},
				url: '/?c=RoutingLevel&m=load'
			}),
			loadData: function (params) {
				this.getStore().baseParams = {
					RoutingLevel_id: wnd.RoutingLevel_id
				};
				this.getStore().reload();
			}
		})];

		wnd.formPanel = new Ext.form.FormPanel({
			frame: true,
			border: false,
			labelAlign: 'left',
			labelWidth: 50,
			bodyStyle:'padding: 5px',
			items: formItems
		});

		Ext.apply(wnd, {
			items: [
				wnd.formPanel
			],
			buttons: [{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				handler: function () {
					wnd.doSave();
				}
			}, {
				text: BTN_FRMCLOSE,
				iconCls: 'close16',
				handler: function () {
					wnd.hide();
				}
			}
		],
		});
		sw.Promed.swRoutingMapAddWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function () {
		let wnd = this;
		if (wnd.setRequiredArg(arguments[0])) {
			sw.swMsg.alert(lang['oshibka'], 'Переданы не все обязательные параметры');
			wnd.hide();
			return false;
		}

		let levelField = wnd.formPanel.getForm().findField('RoutingLevel_id');
		levelField.loadData();

		sw.Promed.swRoutingMapAddWindow.superclass.show.apply(this, arguments);
	},
	doReset: function () {
		let form = this.formPanel.getForm();
		form.reset();
		this.RoutingLevel_id = null;
		this.RoutingMap_pid = null;
		this.RoutingProfile_id = null;
	},
	doSave: function () {
		let wnd = this;
		let form = wnd.formPanel.getForm();
		if (!form.isValid()) {
			sw.swMsg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
			return;
		}

		let params = form.getValues();
		params.RoutingProfile_id = wnd.RoutingProfile_id;
		params.RoutingMap_pid = wnd.RoutingMap_pid;
		params.RoutingMap_begDate = getGlobalOptions().date;
		wnd.submit(params);
	},
	submit: function (params) {
		let wnd = this;
		Ext.Ajax.request({
			params: params,
			url: wnd.url,
			failure: function(response, options) {
				if (options.response.responseText) {
					var answer = Ext.util.JSON.decode(options.response.responseText);
					if (answer.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
						return;
					}
				}
				sw.swMsg.alert(lang['oshibka'], 'Не удалось сохранить данные');
			},
			success: function(response, options) {
				wnd.hide();
				let parentWnd = wnd.parentWnd;
				parentWnd.LpuGrid.reload();
				parentWnd.LpuTree.root.reload();
			}
		});
	},
	setRequiredArg: function (params) {
		let paramsIsEmpty = Ext.isEmpty(params);
		if (!paramsIsEmpty && !Ext.isEmpty(params.parentWnd)) {
			this.parentWnd = params.parentWnd;
		} if (!paramsIsEmpty && !Ext.isEmpty(params.RoutingProfile_id)) {
			this.RoutingProfile_id = params.RoutingProfile_id;
		} if (!paramsIsEmpty && !Ext.isEmpty(params.RoutingMap_pid)) {
			this.RoutingMap_pid = params.RoutingMap_pid;
		} if (!paramsIsEmpty && !Ext.isEmpty(params.RoutingLevel_id)) {
			this.RoutingLevel_id = params.RoutingLevel_id;
		}

		if (Ext.isEmpty(this.parentWnd)
			|| Ext.isEmpty(this.RoutingProfile_id)
		) return true;
		return false;
	}
});