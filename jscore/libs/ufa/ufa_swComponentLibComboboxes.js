/**
* ufa_swComponentLibComboboxes - классы ниспадающих списков выбора.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @access	  public
* @package        Libs
* @author	  Magafurov SM
* @version	  08.06.2018
*/


sw.Promed.SwRzhdWorkerCategoryCombo = Ext.extend(sw.Promed.SwCommonSprCombo, {
	fieldLabel: 'Категория населения',
	editable: false,
	comboSubject: 'RzhdWorkerCategory',
	listWidth: 500
});
Ext.reg('swrzhdworkercategorycombo',sw.Promed.SwRzhdWorkerCategoryCombo);

sw.Promed.SwRzhdWorkerGroupCombo = Ext.extend(sw.Promed.SwCommonSprCombo,{
	fieldLabel: 'Группа рабочего',
	editable: false,
	comboSubject: 'RzhdWorkerGroup',
	moreFields: [
		{ name: 'RzhdWorkerCategory_id', mapping: 'RzhdWorkerCategory_id' }
	],
	listWidth: 500
});
Ext.reg('swrzhdworkergroupcombo', sw.Promed.SwRzhdWorkerGroupCombo);

sw.Promed.SwRzhdWorkerSubgroupCombo = Ext.extend(sw.Promed.SwCommonSprCombo,{
	fieldLabel: 'Подгруппа рабочего',
	comboSubject: 'RzhdWorkerSubgroup',
	listWidth: 500,
	moreFields: [
		{ name: 'RzhdWorkerGroup_id', mapping: 'RzhdWorkerGroup_id' }
	]
});
Ext.reg('swrzhdworkersubgroupcombo', sw.Promed.SwRzhdWorkerSubgroupCombo);

sw.Promed.SwRzhdOrgCombo = Ext.extend(sw.Promed.SwBaseLocalCombo,{ //SwBaseLocalCombo
	fieldLabel: 'Организация РЖД',
	displayField: 'Org_Nick',
	valueField: 'Org_id',
	triggerAction: 'all',
	trigger1Class: 'x-form-search-trigger',
	trigger2Class: 'x-form-clear-trigger',
	tpl: new Ext.XTemplate(
		'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: left;">',
		'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
		'<td style="padding: 2px;">{Org_Nick}&nbsp;</td>',
		'</tr></tpl>',
		'</table>'
	),
	store: new Ext.data.SimpleStore({
		fields: [
			{name: 'Org_id', type: 'int'},
			{name: 'Org_Nick', type: 'string'}
		],
		key: 'Org_id',
		data: []
	}),
	listeners:{
		'valid': function(){
			if(this.value == null){
				return;
			}
			if(this.getRawValue().trim()=="") {
				this.setValue(null);
			}
		}
	},
	onTrigger1Click: function() {
		if ( this.disabled ) {
			return;
		}
	
		var combo = this;
	
		getWnd('swOrgSearchWindow').show({
			object: 'rjd',
			onClose: function() {
				combo.focus(true, 200);
			},
			onSelect: function(result) {
				if ( result.Org_id > 0 ) {
					combo.setValue(result.Org_id);
					combo.setRawValue(result.Org_Nick)
					combo.focus(true, 250);
					combo.fireEvent('change', combo);
				}
				getWnd('swOrgSearchWindow').hide();
			}
		});
	},
	onTrigger2Click: function() {
		if ( this.disabled ) {
			return;
		}
		this.setValue(null);
	}
});
Ext.reg('swrzhdorgcombo',sw.Promed.SwRzhdOrgCombo);

sw.Promed.SwRzhdOrgCombo.prototype.initComponent = Ext.form.TwinTriggerField.prototype.initComponent;
sw.Promed.SwRzhdOrgCombo.prototype.getTrigger = Ext.form.TwinTriggerField.prototype.getTrigger;
sw.Promed.SwRzhdOrgCombo.prototype.initTrigger = Ext.form.TwinTriggerField.prototype.initTrigger;

sw.Promed.SwRegisterDisCauseCombo = Ext.extend(sw.Promed.SwCommonSprCombo,{
	fieldLabel: langs('Причина исключения'),
	hiddenName: 'RegisterDisCause_id',
	xtype: 'swcommonsprcombo',
	comboSubject: 'RegisterDisCause',
	width: 350,
	moreFields: [
		{ name: 'RegisterType_id',   mapping: 'RegisterType_id' },
		{ name: 'RegisterType_Code', mapping: 'RegisterType_Code' }
	],
	RegisterType_Code: null,
	setRegisterFilter(name) {
		var me = this;
		me.RegisterType_Code = name || me.RegisterType_Code;
		me.getStore().filterBy( function(rec) {
			return rec.get('RegisterType_Code') == me.RegisterType_Code;
		});
	},
	initComponent: function() {
		sw.Promed.SwRegisterDisCauseCombo.superclass.initComponent.apply(this, arguments);

		var me = this;
		me.addListener('beforequery', function(event) {
			event.combo.onLoad();
			return false; 
		});

		me.getStore().on('load', function(store) {
			me.setRegisterFilter();
		});
	}
});
Ext.reg('swregisterdiscausecombo',sw.Promed.SwRegisterDisCauseCombo);
