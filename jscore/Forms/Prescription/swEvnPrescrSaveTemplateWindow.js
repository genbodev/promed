/**
* Назначения: Форма выбора параметров сохранения шаблона плана назначений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      22.12.2011
*/

sw.Promed.swEvnPrescrSaveTemplateWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['sohranenie_shablona_plana_naznacheniy'],
	modal: true,
	height: 145,
	width: 500,
	shim: false,
	resizable: false,
	buttonAlign: "right",
	objectName: 'swEvnPrescrSaveTemplateWindow',
	closeAction: 'hide',
	id: 'swEvnPrescrSaveTemplateWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrSaveTemplateWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.save();
			},
			iconCls: 'save16',
			text: lang['sohranit_shablon']
		},
		'-',
		{
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w) {
			var combo = w.CommonForm.getForm().findField('Type_id');
			combo.setValue(1);
			combo.fireEvent('select', combo, combo.getStore().getAt(0), 0);
			w.CommonForm.getForm().reset();
		}
	},
	
	show: function()
	{
		sw.Promed.swEvnPrescrSaveTemplateWindow.superclass.show.apply(this, arguments);
		
		if(!arguments[0] || !arguments[0].EvnPrescr_pid || !arguments[0].EvnPrescr_begDate){
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		var base_form = this.CommonForm.getForm(),
			diag_combo = base_form.findField('Diag_id');
		base_form.setValues(arguments[0]);
		
		// Проставим диагноз
		if(diag_combo.getValue() != '' && diag_combo.getValue() != null){
			diag_combo.getStore().load({
				params: { where: "where Diag_id = " + diag_combo.getValue() },
				callback: function(){
					diag_combo.getStore().each(function(rec){
						if(rec.get('Diag_id') == diag_combo.getValue())
							diag_combo.fireEvent('select', diag_combo, rec, 0);
					});
				}
			});
		}
		
		this.center();
	},
	
	save: function()
	{
		var base_form = this.CommonForm.getForm(),
			lm = this.getLoadMask(lang['sohranenie']);
		if(!base_form.isValid()){
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya']);
			return false;
		}
		
		lm.show();
		base_form.submit({
			failure: lm.hide(),
			success: function(){
				lm.hide();
				sw.swMsg.alert(lang['uvedomlenie'], lang['plan_naznacheniy_uspeshno_sohranen']);
				this.hide();
			}.createDelegate(this)
		});
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.CommonForm = new Ext.form.FormPanel({
			labelAlign: 'right',
			url: '/?c=EvnPrescr&m=saveEvnPrescrListAsXTemplate',
			labelWidth: 130,
			defaults: {
				border: false
			},
			bodyStyle: 'padding: 3px;',
			items: [
				{
					xtype: 'hidden',
					name: 'EvnPrescr_pid'
				}, {
					xtype: 'hidden',
					name: 'EvnPrescr_begDate'
				}, {
					xtype: 'hidden',
					name: 'LpuSection_id'
				}, {
					xtype: 'combo',
					store: new Ext.data.Store({
						autoLoad: true,
						data: [
							[1, lang['diagnoza']],
							[2, lang['gruppyi_diagnozov']]
						],
						reader: new Ext.data.ArrayReader({
							idIndex: 0
						}, [
							{mapping: 0, name: 'Type_id'},
							{mapping: 1, name: 'Type_Name'}
						])
					}),
					allowBlank: false,
					mode: 'local',
					listeners: {
						render: function(c){
							c.setValue(1);
							c.fireEvent('select', c, c.getStore().getAt(0), 0);
						},
						select: function(c, r, i) {
							var diagfields = this.CommonForm.find('name', 'diag'),
								diagcombos = this.CommonForm.find('hiddenName', 'Diag_id');
							diagfields[0].setVisible(true);
							diagfields[1].setVisible(true);
							diagcombos[0].enable();
							diagcombos[1].enable();
							
							switch(r.get('Type_id')){
								case 1:
									diagfields[1].setVisible(false);
									diagcombos[1].disable();
								break;
								case 2:
									diagfields[0].setVisible(false);
									diagcombos[0].disable();
									if(!diagcombos[0].getValue().inlist([null, ''])) {
										var c = diagcombos[0],
											Diag_pid = null;
										c.getStore().each(function(rec){
											if(c.getValue() == rec.get('Diag_id'))
												Diag_pid = rec.get('Diag_pid');
										});
										if(Diag_pid != null) {
											var c2 = diagcombos[1];
											c2.getStore().load({
												params: { where: 'where Diag_id = ' + Diag_pid },
												callback: function(){
													if(c2.getStore().getCount() == 0) return false;
														c2.fireEvent('select', c2, c2.getStore().getAt(0), 0);
														c2.focus(true);
												}
											});
										}
									}
								break;
							}
							
							diagfields[0].doLayout();
							diagfields[1].doLayout();
						}.createDelegate(this)
					},
					triggerAction: 'all',
					editable: false,
					hiddenName: 'Type_id',
					valueField: 'Type_id',
					displayField: 'Type_Name',
					fieldLabel: lang['shablon_dlya']
				}, {
					layout: 'form',
					width: 450,
					name: 'diag',
					items: [
						{
							xtype: 'swdiagcombo',
							allowBlank: false,
							anchor: '100%',
							fieldLabel: lang['diagnoz']
						}
					]
				}, {
					layout: 'form',
					width: 450,
					hidden: true,
					name: 'diag',
					items: [
						{
							xtype: 'swdiaggroupscombo',
							//allowBlank: false,
							anchor: '100%'
						}
					]
				}, {
					xtype: 'swmesagegroupcombo', // По-идее наверное надо другой спр-к юзать, но поскольку этот идентичен, пока оставим
					hiddenName: 'PersonAgeGroup_id'
				}
			]
		});
		
		Ext.apply(this,	{
			items: [this.CommonForm]
		});
		sw.Promed.swEvnPrescrSaveTemplateWindow.superclass.initComponent.apply(this, arguments);
	}
});