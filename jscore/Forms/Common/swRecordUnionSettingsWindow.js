/**
 * swRecordUnionSettingsWindow - окно объединения отделений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			03.08.2015
 */
/*NO PARSE JSON*/
sw.Promed.swRecordUnionSettingsWindow = Ext.extend(sw.Promed.BaseForm,
{
	//autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	width: 460,
	height: 300,
	layout: 'form',
	id: 'swRecordUnionSettingsWindow',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,

	doSave: function() {
		var settings = {};

		for(var subObject in this.settings) {
			var checkbox = this.findById('RUSW_'+subObject);
			if (checkbox && checkbox.checked) {
				settings[subObject] = {
					removeIntersection: this.settings[subObject].removeIntersection,
					selectMainRecord: this.settings[subObject].selectMainRecord
				}
			}
		}

		var params = {
			Table: this.RecordType_Code,
			mainRecord: Ext.util.JSON.encode(this.mainRecord),
			minorRecord: Ext.util.JSON.encode(this.minorRecord),
			settings: Ext.util.JSON.encode(settings)
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет объединение..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=Utils&m=doRecordUnionWithSettings',
			params: params,
			timeout: 3600000,
			success: function(response) {
				loadMask.hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					sw.swMsg.alert(lang['vnimanie'],lang['zapisi_postavleny_v_ochered_na_objedinenie']);
					this.successFn.call(this);
					this.hide();
					if (Ext.getCmp('RecordUnionWindow')) {
						Ext.getCmp('RecordUnionWindow').hide();
					}
				}
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	removeIntersection: function(subObject) {
		var setting = this.settings[subObject];
		var linkPanel = Ext.getCmp('RUSW_'+subObject+'_link');

		getWnd('swRecordUnionSubObjectWindow').show({
			setting: Ext.apply({}, setting),	//Копия настроек
			RecordType_Code: this.RecordType_Code,
			RecordType_Name: this.RecordType_Name,
			mainRecord: this.mainRecord,
			minorRecord: this.minorRecord,
			callback: function(response) {
				if (response && response.selectMainRecord) {
					setting.selectMainRecord = response.selectMainRecord;
				}

				if (linkPanel && !setting.removeIntersection) {
					setting.removeIntersection = true;
					Ext.getDom(Ext.getCmp('RUSW_'+subObject+'_link').body).innerHTML = '<a href="javascript://" ' +
						'style="color: green;"' +
						'onClick="Ext.getCmp(\'swRecordUnionSettingsWindow\').removeIntersection(\'' + subObject + '\');">' +
						lang['peresecheniya_ustranenyi'] +
						'</a>';
					if (this.isAllIntersectionRemoved()) {
						Ext.getCmp('RUSW_SaveButton').enable();
					}
				}
			}.createDelegate(this)
		});
	},

	isAllIntersectionRemoved: function() {
		var flag = true;
		for(var subObject in this.settings) {
			var setting = this.settings[subObject];
			var checkbox = this.findById('RUSW_'+subObject);
			if (checkbox.checked && setting.hasIntersection && !setting.removeIntersection) {
				flag = false;
				break;
			}
		}
		return flag;
	},

	getRecordUnionSettings: function() {
		var  panel = this.findById('RUSW_SettingsPanel');
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение параметров..." });
		loadMask.show();

		Ext.getCmp('RUSW_SaveButton').disable();

		panel.removeAll();

		var params = {
			Table: this.RecordType_Code,
			mainRecord: Ext.util.JSON.encode(this.mainRecord),
			minorRecord: Ext.util.JSON.encode(this.minorRecord)
		};

		Ext.Ajax.request({
			params: params,
			url: '/?c=Utils&m=getRecordUnionSettings',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (!Ext.isEmpty(response_obj.Error_Msg)) {
					loadMask.hide();
					this.hide();
				}

				this.settings = response_obj.settings;

				for(var subObject in this.settings) {
					loadMask.hide();
					var setting = this.settings[subObject];

					//setting.hasIntersection = true;

					setting.removeIntersection = false;

					var removeIntersactionLink = '';
					if (setting.hasIntersection) {
						removeIntersactionLink = '<a href="javascript://" ' +
							'style="color: red;"' +
							'onClick="Ext.getCmp(\'swRecordUnionSettingsWindow\').removeIntersection(\'' + subObject + '\');">' +
							lang['imeyutsya_peresecheniya_ustranit'] +
							'</a>';
					}

					panel.add({
						border: false,
						labelWidth: 10,
						layout: 'column',
						items: [{
							border: false,
							labelWidth: 10,
							layout: 'form',
							width: 210,
							items: [{
								xtype: 'checkbox',
								id: 'RUSW_'+subObject,
								name: subObject,
								labelSeparator: '',
								checked: setting.hasForeignKey,
								disabled: setting.hasForeignKey,
								boxLabel: setting.title,
								listeners: {
									'check': function() {
										var allowSave = this.isAllIntersectionRemoved();
										Ext.getCmp('RUSW_SaveButton').setDisabled(!allowSave);
									}.createDelegate(this)
								}
							}]
						}, {
							border: false,
							labelWidth: 10,
							layout: 'form',
							style: 'float: right; margin-right: 15px; margin-top: 3px;',
							items: [{
								id: 'RUSW_'+subObject+'_link',
								border: false,
								html: removeIntersactionLink
							}]
						}]
					});
				}

				if (this.isAllIntersectionRemoved()) {
					Ext.getCmp('RUSW_SaveButton').enable();
				}

				this.doLayout();
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
			}
		});
	},

	show: function() {
		sw.Promed.swRecordUnionSettingsWindow.superclass.show.apply(this, arguments);

		Ext.getCmp('RUSW_SaveButton').disable();

		this.RecordType_Code = arguments[0].RecordType_Code;
		this.RecordType_Name = arguments[0].RecordType_Name;
		this.mainRecord = null;
		this.minorRecord = null;
		this.settings = null;
		this.successFn = Ext.emptyFn;
		var records = arguments[0].Records;
		if (records[0].IsMainRec) {
			this.mainRecord = records[0];
			this.minorRecord = records[1];
		} else {
			this.mainRecord = records[1];
			this.minorRecord = records[0];
		}

		if (arguments[0].successFn) {
			this.successFn = arguments[0].successFn;
		}

		this.setTitle(lang['vyibor_obyektov']);

		this.getRecordUnionSettings();
	},

	initComponent: function() {

		Ext.apply(this,{
			layout: 'fit',
			buttons:
			[{
				id: 'RUSW_SaveButton',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this),
				//iconCls: 'save16',
				text: lang['gotovo']
			},
			{
				text: '-'
			},
			//HelpButton(this),
			{
				handler: function()
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_LPEEW + 17,
				text: BTN_FRMCANCEL
			}],
			items: [{
				layout: 'form',
				frame: false,
				labelWidth: 10,
				defaults: {
					border: false,
					labelWidth: 10
				},
				items: [{
					style: 'margin-left: 15px; margin-bottom: 10px;',
					html: lang['vyiberite_obyektyi_kotoryie_neobhodimoperenesti_pri_obyedinenii']
				}, {
					id: 'RUSW_SettingsPanel',
					layout: 'form',
					items: []
				}]
			}]
		});

		sw.Promed.swRecordUnionSettingsWindow.superclass.initComponent.apply(this, arguments);
	}
});