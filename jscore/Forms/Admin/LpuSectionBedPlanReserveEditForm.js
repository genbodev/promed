/**
* swLpuSectionBedStateEditForm - окно просмотра и редактирования коечного фонда
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      08.10.2010
*/
/*NO PARSE JSON*/
sw.Promed.swLpuSectionBedPlanReserveEditForm = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLpuSectionBedPlanReserveEditForm',
	objectSrc: '/jscore/Forms/Admin/LpuSectionBedPlanReserveEditForm.js',
	title: lang['planovyiy_rezerv_koek_redaktirovanie'],
	id: 'swLpuSectionBedPlanReserveEditForm',
	layout: 'fit',
	maximizable: false,
	shim: false,
	width: 450,
	height: 95,
	onHide: Ext.emptyFn,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	},
	{
		text: BTN_FRMCANCEL,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
		}
	}
	],
	listeners:
	{
		'hide': function(w)
		{
			w.CenterPanel.getForm().reset();
		}
	},
	doSave: function()
	{	
		var form = this;
		var base_form = this.CenterPanel.getForm();
		var LpuSection_CommonCount = parseInt(base_form.findField('LpuSection_CommonCount').getValue(), 10);
		var LpuSection_MaxEmergencyBed = parseInt(base_form.findField('LpuSection_MaxEmergencyBed').getValue(), 10);
		if(!base_form.isValid())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['pole_zapolneno_nekorrektno_libo_ne_zapolneno']);
			return false;
		}
		if(LpuSection_MaxEmergencyBed > LpuSection_CommonCount)
		{
			sw.swMsg.alert(lang['soobschenie'], lang['planovyiy_rezerv_koek_dlya_ekstrennyih_gospitalizatsiy_ne_mojet_byit_bolshe_obschego_kolichestva_koek_v_otdelenii_po_planu']);
			return false;
		}
		base_form.submit({
			params: {LpuSection_id: this.LpuSection_id},
			success: function()
			{
				form.hide();
				form.onHide();
			},
			failure: function()
			{
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_bd']);
				return false;
			}
		});
	},
	show: function() {
		sw.Promed.swLpuSectionBedPlanReserveEditForm.superclass.show.apply(this, arguments);
		
		frm = this;
		base_form = frm.CenterPanel.getForm();
		if(arguments[0].LpuSection_CommonCount)
		{
			base_form.findField('LpuSection_CommonCount').setValue(arguments[0].LpuSection_CommonCount);
		}
		if(arguments[0].LpuSection_MaxEmergencyBed)
		{
			base_form.findField('LpuSection_MaxEmergencyBed').setValue(arguments[0].LpuSection_MaxEmergencyBed);
		}
		if(arguments[0].onHide)
		{
			frm.onHide = arguments[0].onHide;
		}
		frm.LpuSection_id = arguments[0].LpuSection_id;
	},
	initComponent: function() {
		
		this.CenterPanel = new Ext.form.FormPanel({
			labelWidth: 300,
			labelAlign: 'right',
			border: false,
			url: '/?c=LpuStructure&m=updMaxEmergencyBed',
			bodyStyle: 'padding: 3px; background: #DFE8F6;',
			items: [
				{
					xtype: 'hidden',
					name: 'LpuSection_CommonCount'
				}, {
					xtype: 'textfield',
					minValue: 0,
					width: 50,
					name: 'LpuSection_MaxEmergencyBed',
					//plugins: [ new Ext.ux.InputTextMask('999', false) ],
					maxValue: 999,
					maxLength: 3,
					allowBlank: false,
					maskRe: /[0-9]/,
					fieldLabel: lang['planovyiy_rezerv_koek_dlya_ekst_gosp_ne_bolee']
				}
			]
		});
		
		Ext.apply(this,
		{
			xtype: 'form',
			border: false,
			items: [this.CenterPanel]
		});
		sw.Promed.swLpuSectionBedPlanReserveEditForm.superclass.initComponent.apply(this, arguments);
	}
});