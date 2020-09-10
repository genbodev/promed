/**
 * swTFOMSQueryEditWindow - окно редактировния запроса в ФСС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitrii Vlasenko
 * @version			18.08.2017
 */

/*NO PARSE JSON*/

sw.Promed.swTFOMSQueryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Запрос на просмотр ЭМК'),
	id: 'swTFOMSQueryEditWindow',
	layout: 'form',
	maximizable: false,
	shim: false,
	width: 500,
	autoHeight: true,
	minWidth: 500,
	minHeight: 420,
	modal: true,
	buttons:
		[{
			text: BTN_FRMSAVE,
			id: 'lbOk',
			iconCls: 'save16',
			handler: function() {
				this.ownerCt.doSave();
			}
		},{
			text:'-'
		},{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		},{
			text: BTN_FRMCANCEL,
			id: 'lbCancel',
			iconCls: 'cancel16',
			handler: function() {
				this.ownerCt.hide();
			}
		}],
	returnFunc: Ext.emptyFn,
	show: function()
	{
		sw.Promed.swTFOMSQueryEditWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('swTFOMSQueryEditWindow'), { msg: langs('Загрузка...') });
		loadMask.show();

		if (arguments[0].TFOMSQueryEMK_id)
			this.TFOMSQueryEMK_id = arguments[0].TFOMSQueryEMK_id;
		else
			this.TFOMSQueryEMK_id = null;

		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		else
			this.returnFunc = Ext.emptyFn;

		if (arguments[0].action)
			this.action = arguments[0].action;
		else
			this.action = 'add';


		var form = this;
		var base_form = form.MainPanel.getForm();
		base_form.reset();
		this.disableFields(this.action == 'view',arguments[0].ARMType);
		this.hideFields(this.action == 'setAccessDate');
		if (this.action == 'add') {
			base_form.findField('Org_id').setValue(getGlobalOptions().org_id);
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('TFOMSQueryEMK_insDT').setValue(getGlobalOptions().date);
			loadMask.hide();
			base_form.clearInvalid();
		} else {
			base_form.load({
				params:{
					TFOMSQueryEMK_id: form.TFOMSQueryEMK_id,
					noCount: true,
					start: 0,
					limit: 100
				},
				failure: function(f, o, a){
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'),
						title: langs('Ошибка')
					});
				},
				success: function(result, request) {
					loadMask.hide();
				},
				url: '/?c=TFOMSQuery&m=loadTFOMSQueryList'
			});
		}

	},
	doSave: function()
	{
		var form = this.MainPanel;
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						form.getFirstInvalidEl().focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		form.ownerCt.submit();
	},
	disableFields: function(action,ARMType) {
		var base_form = this.MainPanel.getForm();
		base_form.findField('TFOMSQueryEMK_insDT').setDisabled(action);
		base_form.findField('Org_id').setDisabled(action || ['tfoms', 'smo'].includes(ARMType));
		base_form.findField('Lpu_id').setDisabled(action || ['lpuadmin', 'mstat'].includes(ARMType));
		this.buttons[0].setVisible(!action);
	},
	hideFields: function(action) {
		this.panelMO.setVisible(action);
		this.panelTFOMS.setVisible(!action);
		this.syncShadow();
	},
	submit: function()
	{
		var formPanel = this.MainPanel,
			form = formPanel.getForm();
		var loadMask = new Ext.LoadMask(Ext.get('swTFOMSQueryEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var params = {};
		params.Org_id = form.findField('Org_id').getValue();
		form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.TFOMSQueryEMK_id) {
						params.TFOMSQueryEMK_id = action.result.TFOMSQueryEMK_id;
						formPanel.ownerCt.hide();
						formPanel.ownerCt.returnFunc(formPanel.ownerCt.owner, true, params);
					} else {
						Ext.Msg.alert(langs('Ошибка #100004'), langs('При сохранении произошла ошибка'));
					}
				} else {
					Ext.Msg.alert(langs('Ошибка #100005'), langs('При сохранении произошла ошибка'));
				}
			}
		});
	},
	initComponent: function()
	{
		this.panelMO = new Ext.Panel({
			layout: 'form',
			labelWidth: 150,
			items: [{
				fieldLabel: langs('Дата начала'),
				width: 100,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				name: 'TFOMSQueryEMK_begDate'
			}, {
				fieldLabel: langs('Дата окончания'),
				width: 100,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				name: 'TFOMSQueryEMK_endDate'
			}]
		});
		this.panelTFOMS = new Ext.Panel({
			layout: 'form',
			//labelWidth: 120,
			items: [{
				fieldLabel: langs('Дата'),
				xtype: 'swdatefield',
				format: 'd.m.Y',
				name: 'TFOMSQueryEMK_insDT',
				disabled: true
			}, {
				allowBlank: false,
				valueField: 'Org_id',
				hiddenName: 'Org_id',
				lastQuery: '',
				//xtype: 'swogrsmocombo',

				xtype: 'sworgsmocombo',
				fieldLabel: 'ТФОМС/ОМС',
				name: 'Org_id',
				//valueField: 'OrgSMO_id',
				value: getGlobalOptions().org_id
			}, {
				allowBlank: false,
				xtype: 'swlpucombo',
				fieldLabel: 'МО',
				name: 'Lpu_id'
			}]
		});
		this.MainPanel = new sw.Promed.FormPanel({
			frame: true,
			region: 'center',
			labelWidth: 120,
			items: [
				{
					name: 'TFOMSQueryEMK_id',
					xtype: 'hidden'
				},{
					name: 'TFOMSQueryStatus_id',
					xtype: 'hidden'
				},
				this.panelTFOMS,
				this.panelMO
			],
			reader: new Ext.data.JsonReader(
				{
					success: function() {}
				},
				[
					{ name: 'TFOMSQueryEMK_id' },
					{ name: 'Lpu_id' },
					{ name: 'Org_id' },
					{ name: 'TFOMSQueryStatus_id' },
					{ name: 'TFOMSQueryEMK_insDT' },
					{ name: 'TFOMSQueryEMK_begDate' },
					{ name: 'TFOMSQueryEMK_endDate' }
				]
			),
			url: '/?c=TFOMSQuery&m=saveTFOMSQuery'
		});

		Ext.apply(this,
			{
				items: [
					this.MainPanel
				]
			});
		sw.Promed.swTFOMSQueryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});

