/**
* swPrehospWaifInspectionEditWindow - окно просмотра, добавления и редактирования осмотров беспризорных
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      29.09.2011
* @comment      tabIndex: TABINDEX_EPSPEF + (от 90 до 99)
*/

/*NO PARSE JSON*/
sw.Promed.swPrehospWaifInspectionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPrehospWaifInspectionEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swPrehospWaifInspectionEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['osmotr'],
	draggable: true,
	id: 'swPrehospWaifInspectionEditWindow',
	width: 700,
	height: 180,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	submit: function() {
		var form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		this.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				this.getLoadMask().hide();
			}.createDelegate(this),
			params: params,
			success: function(form, action) {
				this.getLoadMask().hide();
				this.hide();
				var data = {};
				this.callback(data);
			}.createDelegate(this)
		});
	},
	allowEdit: function(is_allow) {
		var form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			fields = ['LpuSection_id','MedStaffFact_id','Diag_id','PrehospWaifInspection_SetDT'];
		for(var i = 0; i < fields.length; i++)
		{
			form.findField(fields[i]).setDisabled(!is_allow);
		}
		if (is_allow)
		{
			save_btn.show();
		}
		else
		{
			save_btn.hide();
		}
	},

	initComponent: function() {

		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'PrehospWaifInspectionEditForm',
			labelAlign: 'left',
			labelWidth: 120,
			region: 'center',
			items: [{
				fieldLabel: lang['data_osmotra'],
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.findById('PrehospWaifInspectionEditForm').getForm();
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('MedStaffFact_id').clearValue();

						if ( !newValue ) {
							setLpuSectionGlobalStoreFilter();
							base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							setMedStaffFactGlobalStoreFilter({
								isStac: true
							});
							base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						else {
							setLpuSectionGlobalStoreFilter({
								onDate: Ext.util.Format.date(newValue, 'd.m.Y')
							});
							base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							setMedStaffFactGlobalStoreFilter({
								isStac: true,
								onDate: Ext.util.Format.date(newValue, 'd.m.Y')
							});
							base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						
						if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
							base_form.findField('LpuSection_id').setValue(lpu_section_id);
						}

						if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
							base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
						}
					}.createDelegate(this)
				},
				format: 'd.m.Y',
				name: 'PrehospWaifInspection_SetDT',
				id: 'PWIEW_PrehospWaifInspection_SetDT',
				allowBlank: false,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_EPSPEF + 91,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: lang['otdelenie'],
				hiddenName: 'LpuSection_id',
				id: 'PWIEW_LpuSectionRecCombo',
/*
				linkedElements: [
					'PWIEW_MedStaffFactRecCombo'
				],
*/
				listWidth: 500,
				tabIndex: TABINDEX_EPSPEF + 92,
				width: 350,
				xtype: 'swlpusectionglobalcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['vrach'],
				hiddenName: 'MedStaffFact_id',
				id: 'PWIEW_MedStaffFactRecCombo',
				listWidth: 500,
				// parentElementId: 'PWIEW_LpuSectionRecCombo',
				tabIndex: TABINDEX_EPSPEF + 93,
				width: 350,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['diagnoz'],
				hiddenName: 'Diag_id',
				tabIndex: TABINDEX_EPSPEF + 95,
				width: 450,
				xtype: 'swdiagcombo'
			}, {
				name: 'PrehospWaifInspection_id',
				xtype: 'hidden'
			}, {
				name: 'EvnPS_id',
				xtype: 'hidden'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.submit();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'PrehospWaifInspection_id' },
				{ name: 'EvnPS_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'Diag_id' },
				{ name: 'PrehospWaifInspection_SetDT' }
			]),
			timeout: 600,
			url: '/?c=PrehospWaifInspection&m=saveRecord'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_EPSPEF + 98,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'PWIEW_PrehospWaifInspection_SetDT',
				tabIndex: TABINDEX_EPSPEF + 99,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swPrehospWaifInspectionEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swPrehospWaifInspectionEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;

		this.doReset();
		this.center();

		var form = this.formPanel.getForm();
		//var lpu_section_rec_combo = form.findField('LpuSection_id');
		//var med_staff_fact_rec_combo = form.findField('MedStaffFact_id');
		var set_date_field = form.findField('PrehospWaifInspection_SetDT');
		var diag_combo = form.findField('Diag_id');
		
		form.setValues(arguments[0]);

		switch (this.action) {
			case 'view':
				this.setTitle(lang['osmotr_prosmotr']);
			break;

			case 'edit':
				this.setTitle(lang['osmotr_redaktirovanie']);
			break;

			case 'add':
				this.setTitle(lang['osmotr_dobavlenie']);
			break;

			default:
				return false;
			break;
		}

		if(this.action == 'add')
		{
			this.allowEdit(true);
			setCurrentDateTime({
				callback: function() {
					set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());
				},
				dateField: set_date_field,
				loadMask: false,
				setDate: true,
				setDateMaxValue: true,
				setTime: false,
				//timeField: base_form.findField('EvnPS_setTime'),
				windowId: this.id
			});
			
			var diag_id = diag_combo.getValue();
			if ( diag_id )
			{
				diag_combo.getStore().load({
					callback: function() {
						diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
					},
					params: {
						where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
					}
				});
			}

			this.syncSize();
			this.doLayout();
		}
		else
		{
			this.allowEdit(false);
			this.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					this.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { this.hide(); }.createDelegate(this) );
				}.createDelegate(this),
				params: {
					PrehospWaifInspection_id: form.findField('PrehospWaifInspection_id').getValue(),
					archiveRecord: win.archiveRecord
				},
				success: function() {
					this.getLoadMask().hide();
					if(this.action == 'edit')
					{
						this.allowEdit(true);
					}
					
					set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());
					
					var diag_id = diag_combo.getValue();
					if ( diag_id )
					{
						diag_combo.getStore().load({
							callback: function() {
								diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
							},
							params: {
								where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
							}
						});
					}

					this.syncSize();
					this.doLayout();
				}.createDelegate(this),
				url: '/?c=PrehospWaifInspection&m=getRecord'
			});
		}
	}
});
