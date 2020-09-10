/**
 * swCmpEmergencyTeamEditWindow - окно редактирования позиции в накладной
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.10.2014
 */
/*NO PARSE JSON*/

sw.Promed.swCmpEmergencyTeamEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swCmpEmergencyTeamEditWindow',
	width: 480,
	autoHeight: true,
	modal: true,

	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},

	doSave: function() {
		var base_form = this.FormPanel.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.getLoadMask("Подождите, идет сохранение...").show();

		var data = new Object();

		data.CmpEmergencyTeamData = getAllFormFieldValues(this.FormPanel);
		data.CmpEmergencyTeamData.CmpProfile_Name = base_form.findField('CmpProfile_id').getFieldValue('CmpProfile_Name');
		data.CmpEmergencyTeamData.CmpProfileTFOMS_Name = base_form.findField('CmpProfileTFOMS_id').getFieldValue('CmpProfileTFOMS_Name');

		this.callback(data);
		this.getLoadMask().hide();

		this.hide();
	},

	show: function() {
		sw.Promed.swCmpEmergencyTeamEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.onHide = Ext.emptyFn;
		this.callback = Ext.emptyFn;
		this.disallowCmpProfileIds = [];

		var wnd = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}
		if (arguments[0] && arguments[0].disallowCmpProfileIds) {
			this.disallowCmpProfileIds = arguments[0].disallowCmpProfileIds;
		}

		base_form.items.each(function(f){f.validate()});

		base_form.findField('CmpProfile_id').getStore().clearFilter();

		base_form.findField('CmpProfileTFOMS_id').getStore().clearFilter();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle(lang['brigadyi_smp_dobavlenie']);
				this.enableEdit(true);

				base_form.findField('CmpProfile_id').getStore().filterBy(function(rec) {
					return !rec.get('CmpProfile_id').inlist(wnd.disallowCmpProfileIds);
				});

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['brigadyi_smp_redaktirovanie']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['brigadyi_smp_prosmotr']);
					this.enableEdit(false);
				}

				base_form.findField('CmpProfile_id').getStore().filterBy(function(rec) {
					return !rec.get('CmpProfile_id').inlist(wnd.disallowCmpProfileIds);
				});
				base_form.findField('CmpProfile_id').setValue(base_form.findField('CmpProfile_id').getValue());
				base_form.findField('CmpProfileTFOMS_id').setValue(base_form.findField('CmpProfileTFOMS_id').getValue());

				loadMask.hide();
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'CETEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 160,
			items: [{
				xtype: 'hidden',
				name: 'CmpEmergencyTeam_id'
			}, {
				xtype: 'hidden',
				value: 0,
				name: 'RecordStatus_Code'
			}, {
				xtype: 'hidden',
				name: 'CmpSubstation_id'
			}, {
				allowBlank: false,
				comboSubject: 'CmpProfile',
				disabledClass: 'field-disabled',
				fieldLabel: lang['profil_brigadyi'],
				hiddenName: 'CmpProfile_id',
				loadParams: {params: {where: getRegionNick().inlist([ 'krym' ]) ? ' where Region_id = ' + getRegionNumber() : ''}},
				moreFields: [
					{ name: 'Region_id', type: 'int' }
				],
				width: 240,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				anchor: '100%',
				comboSubject: 'CmpProfileTFOMS',
				fieldLabel: lang['profil_brigadyi_tfoms'],
				hiddenName: 'CmpProfileTFOMS_id',
				id: 'CETEW_CmpProfileTFOMS_id',
				xtype: 'swcommonsprcombo'
			}, {
				autoCreate: {tag: "input", maxLength: "9", autocomplete: "off"},
				allowBlank: false,
				xtype: 'numberfield',
				allowNegative: false,
				name: 'CmpEmergencyTeam_Count',
				fieldLabel: lang['chislo_vyiezdnyih_brigad'],
				width: 240
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'CmpEmergencyTeam_id'},
				{name: 'RecordStatus_Code'},
				{name: 'CmpSubstation_id'},
				{name: 'CmpProfile_id'},
				{name: 'CmpProfileTFOMS_id'},
				{name: 'CmpEmergencyTeam_Count'}
			])
		});

		Ext.apply(this,
		{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'CETEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swCmpEmergencyTeamEditWindow.superclass.initComponent.apply(this, arguments);
	}
});