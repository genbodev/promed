/**
* swProphConsultEditForm - окно просмотра и редактирования наследственности по заболеваниям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2013 Swan Ltd.
* @author       
* @version      22.05.2013
* @comment      префикс PCEF
*/
/*NO PARSE JSON*/

sw.Promed.swProphConsultEditForm = Ext.extend(sw.Promed.BaseForm, {
	layout: 'form',
	title: lang['pokazanie_k_uglublennomu_profilakticheskomu_konsultirovaniyu'],
	id: 'ProphConsultEditForm',
	width: 450,
	autoHeight: true,
	modal: true,
	formStatus: 'edit',
	doSave: function()  {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';
		var form = this.FormPanel;
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();
		form.getForm().submit(
		{
			url: '/?c=ProphConsult&m=saveProphConsult',
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.ProphConsult_id) 
					{
						win.hide();
						win.callback(win.owner, action.result.ProphConsult_id);
					}
					else
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
				else
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
			}
		});
	},
	callback: Ext.emptyFn,
	show: function() {
		sw.Promed.swProphConsultEditForm.superclass.show.apply(this, arguments);
		
		this.formStatus = 'edit';
		var win = this;
		win.getLoadMask("Подождите, идет загрузка...").show();
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		
		win.object = 'EvnPLDispDop13';
		
        if (arguments[0].object)
        {
        	win.object = arguments[0].object;
        }

		this.callback = Ext.EmptyFn;
		this.date = null;
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		
		if (arguments[0].ProphConsult_id) {
			this.ProphConsult_id = arguments[0].ProphConsult_id;
		} else {
			this.ProphConsult_id = null;
		}

		if (arguments[0].disallowedRiskFactorTypeIds) {
			this.disallowedRiskFactorTypeIds = arguments[0].disallowedRiskFactorTypeIds;
		} else {
			this.disallowedRiskFactorTypeIds = [];
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		
		if (arguments[0].EvnPLDisp_id) {
			base_form.findField('EvnPLDisp_id').setValue(arguments[0].EvnPLDisp_id);
		}

		base_form.findField('RiskFactorType_id').getStore().clearFilter();
		base_form.findField('RiskFactorType_id').lastQuery = '';;

		if ( typeof arguments[0].date == 'object' ) {
			win.date = arguments[0].date;

			base_form.findField('RiskFactorType_id').getStore().filterBy(function(rec) {
				return (
					!rec.get('RiskFactorType_id').inlist(win.disallowedRiskFactorTypeIds)
					&& (Ext.isEmpty(rec.get('RiskFactorType_begDate')) || rec.get('RiskFactorType_begDate') <= win.date)
					&& (Ext.isEmpty(rec.get('RiskFactorType_endDate')) || rec.get('RiskFactorType_endDate') >= win.date)
				);
			});
		}

		switch (this.action)
		{
			case 'add':
				this.enableEdit(true);
				win.setTitle(lang['pokazanie_k_uglublennomu_profilakticheskomu_konsultirovaniyu_dobavlenie']);
				break;
			case 'edit':
				this.enableEdit(true);
				win.setTitle(lang['pokazanie_k_uglublennomu_profilakticheskomu_konsultirovaniyu_redaktirovanie']);
				break;
			case 'view':
				this.enableEdit(false);
				win.setTitle(lang['pokazanie_k_uglublennomu_profilakticheskomu_konsultirovaniyu_prosmotr']);
				break;
		}
		
		if (this.action != 'add') 
		{
			base_form.load(
			{
				url: '/?c=ProphConsult&m=loadProphConsultGrid',
				params: 
				{
					ProphConsult_id: win.ProphConsult_id
				},
				success: function() 
				{
					win.getLoadMask().hide();
					base_form.findField('RiskFactorType_id').focus(true, 100);
				},
				failure: function() 
				{
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function() { win.hide(); } );
				}
			});
		} 
		else 
		{
			win.getLoadMask().hide();
			base_form.findField('RiskFactorType_id').focus(true, 100);
		}
	},
	initComponent: function() 
	{
		this.FormPanel = new sw.Promed.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'background:#DFE8F6;padding:5px;',
			id: 'ProphConsultEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 130,
			items:
			[
				{
					name: 'ProphConsult_id',
					xtype: 'hidden'
				},
				{
					name: 'EvnPLDisp_id',
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					comboSubject: 'RiskFactorType',
					fieldLabel: lang['faktor_riska'],
					hiddenName: 'RiskFactorType_id',
					lastQuery: '',
					moreFields: [
						{name: 'RiskFactorType_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{name: 'RiskFactorType_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
					tabIndex: TABINDEX_PCEF + 1,
					width: 250,
					xtype: 'swcommonsprcombo'
				}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//alert('success');
				}
			},
			[
				{ name: 'ProphConsult_id' },
				{ name: 'EvnPLDisp_id' },
				{ name: 'RiskFactorType_id' }
			]
			)
		});
		
		Ext.apply(this,
		{
			border: false,
			items: [this.FormPanel],
			buttons:
			[
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_PCEF + 91,
					iconCls: 'save16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text:'-'
				},
				HelpButton(this, TABINDEX_PCEF + 92),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_PCEF + 93,
					iconCls: 'cancel16',
					handler: function()
					{
						this.hide();
					}.createDelegate(this)
				}
			]
		});
		
		sw.Promed.swProphConsultEditForm.superclass.initComponent.apply(this, arguments);
	}
});