/**
* swRegistryReceptViewWindow - данные о рецепте
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      RegistryRecept
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      19.01.2013
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swRegistryReceptViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: true,
	height: 600,
	width: 800,
	id: 'RegistryReceptViewWindow',
	title: WND_REGISTRYRECEPT_VIEW, 
	layout: 'border',
	resizable: true,
	initComponent: function() 
	{
		var form = this;
		
		this.RegistryData = new Ext.Panel({
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px',
				bodyStyle: 'padding: 5px',
				title: lang['1_dannyie_reestra'],
				titleCollapse: true,
				collapsible: true,
				autoHeight: true,
				labelWidth: 100,
				layout: 'form',
				items: [{
					layout: 'form',
					border: false,
					width: '790',
					items: [{
						layout: 'column',
						border: false,
						defaults: {border: false},
						items: [{
							layout: 'form',
							columnWidth: .7,
							items: [{
								name: 'Contragent_Name',
								tabIndex: TABINDEX_RRVW + 0,
								anchor: '100%',
								fieldLabel: lang['postavschik'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'ReceptUploadType_Name',
								tabIndex: TABINDEX_RRVW + 2,
								anchor: '100%',
								fieldLabel: lang['tip_zagruzki'],
								readOnly: true,
								xtype: 'textfield'
							}]
						}, {
							layout: 'form',
							columnWidth: .3,
							items: [{
								name: 'ReceptUploadLog_setDT',
								tabIndex: TABINDEX_RRVW + 1,
								anchor: '100%',
								fieldLabel: lang['data'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'ReceptUploadLog_id',
								tabIndex: TABINDEX_RRVW + 3,
								anchor: '100%',
								fieldLabel: lang['id_zagruzki'],
								readOnly: true,
								xtype: 'textfield'
							}]
						}]
					}]
				}]
		});
		this.PersonData = new Ext.Panel({
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px',
				bodyStyle: 'padding: 5px',
				title: lang['2_dannyie_patsienta'],
				titleCollapse: true,
				collapsible: true,
				autoHeight: true,
				labelWidth: 100,
				layout: 'form',
				items: [{
					layout: 'form',
					border: false,
					width: '790',
					items: [{
						layout: 'column',
						border: false,
						defaults: {border: false},
						items: [{
							layout: 'form',
							columnWidth: .5,
							items: [{
								xtype: 'label',
								style: 'margin-left: 5px; font-weight: bold',
								text: lang['po_reestru_retseptov'],
								height: 25
							}, {
								name: 'RegistryReceptPerson_Fio',
								tabIndex: TABINDEX_RRVW + 13,
								anchor: '100%',
								fieldLabel: lang['fio_patsienta'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryReceptPerson_Sex',
								tabIndex: TABINDEX_RRVW + 15,
								anchor: '100%',
								fieldLabel: lang['pol_patsienta'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryReceptPerson_BirthDay',
								tabIndex: TABINDEX_RRVW + 17,
								anchor: '100%',
								fieldLabel: lang['data_rojdeniya'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								hiddenName: 'RegistryReceptPerson_Snils',
								tabIndex: TABINDEX_RRVW + 19,
								anchor: '100%',
								fieldLabel: lang['snils'],
								readOnly: true,
								xtype: 'swsnilsfield'
							}, {
								name: 'RegistryReceptPerson_UAddOKATO',
								tabIndex: TABINDEX_RRVW + 21,
								anchor: '100%',
								fieldLabel: lang['okato'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryReceptPerson_Privilege',
								tabIndex: TABINDEX_RRVW + 23,
								anchor: '100%',
								fieldLabel: lang['lgota'],
								readOnly: true,
								xtype: 'textfield'
							}]
						}, {
							layout: 'form',
							columnWidth: .5,
							labelWidth: 120,
							items: [{
								name: 'Person_id',
								tabIndex: TABINDEX_RRVW + 12,
								anchor: '100%',
								fieldLabel: 'Person_id',
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'Person_Fio',
								tabIndex: TABINDEX_RRVW + 14,
								anchor: '100%',
								fieldLabel: lang['fio_patsienta'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'Sex_Name',
								tabIndex: TABINDEX_RRVW + 16,
								anchor: '100%',
								fieldLabel: lang['pol_patsienta'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'Person_BirthDay',
								tabIndex: TABINDEX_RRVW + 18,
								anchor: '100%',
								fieldLabel: lang['data_rojdeniya'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'Person_Snils',
								tabIndex: TABINDEX_RRVW + 20,
								anchor: '100%',
								fieldLabel: lang['snils'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'Person_OKATO',
								tabIndex: TABINDEX_RRVW + 22,
								anchor: '100%',
								fieldLabel: lang['okato'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'PrivilegeType_Name',
								tabIndex: TABINDEX_RRVW + 24,
								anchor: '100%',
								fieldLabel: lang['lgota'],
								readOnly: true,
								height: 45,
								xtype: 'textarea'
							}]
						}]
					}]
				}]
		});
		
		this.RegistryReceptDataGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'RegistryReceptDataGrid',
			title:'',
			object: 'Registry',
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptDataGrid',
			autoLoadData: false,
			height: 100,
			region: 'center',
			toolbar: false,
			stringfields:
			[
				{name: 'RegistryReceptData_id', type: 'int', header: 'RegistryReceptData_id', key: true},
				{name: 'Drug_Code', header: lang['kod_ls'], width: 80},
				{name: 'Drug_Ser', header: lang['seriya_№'], width: 80},
				{name: 'Drug_KolVo', header: lang['kolichestvo'], width: 60},
				{name: 'Drug_Price', header: lang['tsena_rub'], width: 80},
				{name: 'Drug_Sum', header: lang['summa_rub'], width: 80},
				{name: 'WhsDocumentSupply_Num', header: lang['kontrakt'], width: 200},
				{name: 'Drug_Name', header: lang['torgovoe_naimenovanie'], width: 60, id: 'autoexpand'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		this.RegistryReceptEvnReceptGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'RegistryReceptEvnReceptGrid',
			title:'',
			object: 'Registry',
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptEvnReceptGrid',
			autoLoadData: false,
			height: 80,
			region: 'center',
			toolbar: false,
			stringfields:
			[
				{name: 'RegistryReceptEvnRecept_id', type: 'int', header: 'RegistryReceptEvnRecept_id', key: true},
				{name: 'EvnRecept_Num', header: lang['seriya_№'], width: 60},
				{name: 'EvnRecept_setDate', header: lang['data_vyipiski'], width: 60},
				{name: 'EvnRecept_endDate', header: lang['srok_deystviya'], width: 60},
				{name: 'Diag_Code', header: lang['kod_mkb'], width: 60},
				{name: 'EvnRecept_isVK', header: lang['vk'], width: 60},
				{name: 'EvnRecept_Finance', header: lang['finansirovanie'], width: 60},
				{name: 'EvnRecept_Persent', header: lang['%_oplatyi'], width: 60},
				{name: 'EvnRecept_Mnn', header: lang['mnn'], width: 60},
				{name: 'MedPersonal_Fio', header: lang['kod_i_fio_vracha'], width: 60, id: 'autoexpand'},
				{name: 'Lpu_Name', header: lang['kod_i_naimenovanie_mo'], width: 60},
				{name: 'EvnRecept_Status', header: lang['status'], width: 60}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		this.RegistryReceptReceptOtovGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'RegistryReceptReceptOtovGrid',
			title:'',
			object: 'Registry',
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptReceptOtovGrid',
			autoLoadData: false,
			height: 80,
			region: 'center',
			toolbar: false,
			stringfields:
			[
				{name: 'RegistryReceptReceptOtov_id', type: 'int', header: 'RegistryReceptReceptOtov_id', key: true},
				{name: 'EvnRecept_Num', header: lang['seriya_№'], width: 60},
				{name: 'EvnRecept_setDate', header: lang['data_vyipiski'], width: 60},
				{name: 'Diag_Code', header: lang['kod_mkb'], width: 60},
				{name: 'EvnRecept_Finance', header: lang['finansirovanie'], width: 60},
				{name: 'EvnRecept_Mnn', header: lang['mnn'], width: 60},
				{name: 'MedPersonal_Fio', header: lang['kod_i_fio_vracha'], width: 60, id: 'autoexpand'},
				{name: 'Lpu_Name', header: lang['kod_i_naimenovanie_mo'], width: 60},
				{name: 'EvnRecept_Status', header: lang['status'], width: 60}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		
		this.RegistryReceptDocumentUcGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'RegistryReceptDocumentUcGrid',
			title:'',
			object: 'Registry',
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptDocumentUcGrid',
			autoLoadData: false,
			height: 100,
			region: 'center',
			toolbar: false,
			stringfields:
			[
				{name: 'RegistryReceptDocumentUc_id', type: 'int', header: 'RegistryReceptDocumentUc_id', key: true},
				{name: 'EvnRecept_Num', header: lang['retsept_seriya_№'], width: 60},
				{name: 'DocumentUcStr_Count', header: lang['kolichestvo'], width: 60},
				{name: 'DocumentUcStr_Price', header: lang['tsena_rub'], width: 60},
				{name: 'DocumentUcStr_Sum', header: lang['summa_rub'], width: 60},
				{name: 'DocumentUcStr_Ser', header: lang['seriya'], width: 60},
				{name: 'DocumentUc_Num', header: lang['kontrakt'], width: 60},
				{name: 'Drug_Name', header: lang['torgovoe_naimenovanie'], width: 60, id: 'autoexpand'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});
		
		this.ReceptData = new Ext.Panel({
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px',
				bodyStyle: 'padding: 5px',
				title: lang['3_dannyie_retsepta_po_reestru'],
				titleCollapse: true,
				collapsible: true,
				autoHeight: true,
				layout: 'form',
				items: [{
					layout: 'form',
					border: false,
					width: '790',
					items: [{
						defaults: {border: false},
						border: false,
						labelWidth: 100,
						layout: 'column',
						items: [{
							layout: 'form',
							columnWidth: .5,
							items: [{
								name: 'RegistryRecept_Recent',
								tabIndex: TABINDEX_RRVW + 51,
								anchor: '100%',
								fieldLabel: lang['retsept'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_MedPersonalCode',
								tabIndex: TABINDEX_RRVW + 53,
								anchor: '100%',
								fieldLabel: lang['kod_vracha'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_LpuMod',
								tabIndex: TABINDEX_RRVW + 55,
								anchor: '100%',
								fieldLabel: lang['kod_mo'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_ProtoKEK',
								tabIndex: TABINDEX_RRVW + 57,
								anchor: '100%',
								fieldLabel: lang['vk'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_Diag',
								tabIndex: TABINDEX_RRVW + 59,
								anchor: '100%',
								fieldLabel: lang['kod_mkb'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_Persent',
								tabIndex: TABINDEX_RRVW + 61,
								anchor: '100%',
								fieldLabel: lang['oplata'],
								readOnly: true,
								xtype: 'textfield'
							}]
						}, {
							layout: 'form',
							columnWidth: .5,
							labelWidth: 120,
							items: [{
								name: 'RegistryRecept_SchetType',
								tabIndex: TABINDEX_RRVW + 52,
								anchor: '100%',
								fieldLabel: lang['tip_zapisi'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'OrgFarmacy_Name',
								tabIndex: TABINDEX_RRVW + 54,
								anchor: '100%',
								fieldLabel: lang['apteka'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_obrDate',
								tabIndex: TABINDEX_RRVW + 56,
								anchor: '100%',
								fieldLabel: lang['obraschenie'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_otpDate',
								tabIndex: TABINDEX_RRVW + 58,
								anchor: '100%',
								fieldLabel: lang['data_otpuska_ls'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'RegistryRecept_RecentFinance',
								tabIndex: TABINDEX_RRVW + 60,
								anchor: '100%',
								fieldLabel: lang['finansirovanie'],
								readOnly: true,
								xtype: 'textfield'
							}, { 
								name: 'WhsDocumentCostItemType_Name',
								tabIndex: TABINDEX_RRVW + 62,
								anchor: '100%',
								fieldLabel: lang['statya_rashoda'],
								readOnly: true,
								xtype: 'textfield'
							}]
						}]
					}]
				},
				this.RegistryReceptDataGrid]
		});
		
		this.RegistryReceptEvnReceptData = new Ext.Panel({
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px',
				bodyStyle: 'padding: 5px',
				title: lang['4_dannyie_o_vyipiske_retsepta'],
				titleCollapse: true,
				collapsible: true,
				autoHeight: true,
				layout: 'form',
				items: [ this.RegistryReceptEvnReceptGrid ]
		});
		
		this.RegistryReceptReceptOtovData = new Ext.Panel({
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px',
				bodyStyle: 'padding: 5px',
				title: lang['5_dannyie_ob_obespechenii_retsepta_lekarstvennyimi_sredstvami'],
				titleCollapse: true,
				collapsible: true,
				autoHeight: true,
				layout: 'form',
				items: [ this.RegistryReceptReceptOtovGrid ]
		});
		
		this.RegistryReceptDocumentUcData = new Ext.Panel({
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				xtype: 'fieldset',
				style: 'margin: 5px',
				bodyStyle: 'padding: 5px',
				title: lang['6_dannyie_ob_otpuschennyih_apteke_lekarstvennyih_sredstvah'],
				titleCollapse: true,
				collapsible: true,
				autoHeight: true,
				layout: 'form',
				items: [{
					layout: 'form',
					border: false,
					width: '790',
					items: [{
						defaults: {border: false},
						border: false,
						labelWidth: 100,
						layout: 'column',
						items: [{
							layout: 'form',
							columnWidth: .5,
							items: [{
								name: 'DocumentUc_Farmacy',
								tabIndex: TABINDEX_RRVW + 70,
								anchor: '100%',
								fieldLabel: lang['apteka'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'DocumentUc_Date',
								tabIndex: TABINDEX_RRVW + 72,
								anchor: '100%',
								fieldLabel: lang['data'],
								readOnly: true,
								xtype: 'textfield'
							}]
						}, {
							layout: 'form',
							columnWidth: .5,
							labelWidth: 120,
							items: [{
								name: 'DocumentUc_Finance',
								tabIndex: TABINDEX_RRVW + 71,
								anchor: '100%',
								fieldLabel: lang['finansirovanie'],
								readOnly: true,
								xtype: 'textfield'
							}, {
								name: 'DocumentUc_Statya',
								tabIndex: TABINDEX_RRVW + 73,
								anchor: '100%',
								fieldLabel: lang['statya_rashoda'],
								readOnly: true,
								xtype: 'textfield'
							}]
						}]
					}]
				}, this.RegistryReceptDocumentUcGrid ]
		});
		
		this.formPanel = new Ext.FormPanel(
		{
			region: 'center',
			labelAlign: 'right',
			layout: 'form',
			labelWidth: 50,
			autoScroll: true,
			border: false,
			listeners:
			{
				resize: function (p,nW, nH, oW, oH)
				{
					if (form.ReceptData.getEl()) {
						form.RegistryReceptDataGrid.ViewGridPanel.setWidth(form.ReceptData.getEl().getWidth()-14);
					}
					if (form.RegistryReceptEvnReceptData.getEl()) {
						form.RegistryReceptEvnReceptGrid.ViewGridPanel.setWidth(form.RegistryReceptEvnReceptData.getEl().getWidth()-14);
					}
					if (form.RegistryReceptReceptOtovData.getEl()) {
						form.RegistryReceptReceptOtovGrid.ViewGridPanel.setWidth(form.RegistryReceptReceptOtovData.getEl().getWidth()-14);
					}
					if (form.RegistryReceptDocumentUcData.getEl()) {
						form.RegistryReceptDocumentUcGrid.ViewGridPanel.setWidth(form.RegistryReceptDocumentUcData.getEl().getWidth()-14);
					}
				}
			},
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				// 1. данные реестра
				{ name: 'ReceptUploadLog_id' },
				{ name: 'ReceptUploadType_Name' },
				{ name: 'ReceptUploadLog_setDT' },
				{ name: 'Contragent_Name' },
				{ name: 'RegistryRecept_Price', type: 'float' },
				{ name: 'RegistryRecept_Sum', type: 'float' },
				// 2. данные пациента
				{ name: 'Person_RowNum' },
				{ name: 'RegistryReceptPerson_Fio' },
				{ name: 'RegistryReceptPerson_Sex' },
				{ name: 'RegistryReceptPerson_BirthDay' },
				{ name: 'RegistryReceptPerson_Snils' },
				{ name: 'RegistryReceptPerson_UAddOKATO' },
				{ name: 'RegistryReceptPerson_Privilege' },
				{ name: 'Person_id' },
				{ name: 'Person_Fio' },
				{ name: 'Sex_Name' },
				{ name: 'Person_BirthDay' },
				{ name: 'Person_Snils' },
				{ name: 'Person_OKATO' },
				{ name: 'PrivilegeType_Name' },
				// 3. данные реестра по рецепту
				{ name: 'RegistryRecept_Recent' },
				{ name: 'RegistryRecept_Diag' },
				{ name: 'RegistryRecept_ProtoKEK' }, 
				{ name: 'RegistryRecept_MedPersonalCode' },
				{ name: 'RegistryRecept_LpuMod' },
				{ name: 'OrgFarmacy_Name' },
				{ name: 'RegistryRecept_obrDate' },
				{ name: 'RegistryRecept_otpDate' },
				{ name: 'RegistryRecept_RecentFinance' },
				{ name: 'WhsDocumentCostItemType_Name' },
				{ name: 'RegistryRecept_Persent' },
				{ name: 'RegistryRecept_SchetType' },
				// 6. учётный документ
				{ name: 'DocumentUc_Farmacy' },
				{ name: 'DocumentUc_Date' },
				{ name: 'DocumentUc_Finance' },
				{ name: 'DocumentUc_Statya' }
			]),
			items:
			[
				this.RegistryData,
				this.PersonData,
				this.ReceptData,
				this.RegistryReceptEvnReceptData,
				this.RegistryReceptReceptOtovData,
				this.RegistryReceptDocumentUcData
			]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.formPanel
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, TABINDEX_RRVW + 91),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRVW + 92,
				onTabAction: function()
				{
					form.formPanel.getForm().findField('ReceptUploadLog_setDT').focus();
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swRegistryReceptViewWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swRegistryReceptViewWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].RegistryRecept_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.RegistryRecept_id = arguments[0].RegistryRecept_id;
		
		var form = this;
		var base_form = this.formPanel.getForm();
		base_form.reset();
		
		form.loadMask = form.getLoadMask(LOAD_WAIT);
		form.loadMask.show();
			
		var filters = new Object();
		filters.start = 0;
		filters.RegistryRecept_id = this.RegistryRecept_id || null;
		
		this.RegistryReceptEvnReceptGrid.loadData({ globalFilters: filters, noFocusOnLoad: true });
		this.RegistryReceptReceptOtovGrid.loadData({ globalFilters: filters, noFocusOnLoad: true });
		this.RegistryReceptDocumentUcGrid.loadData({ globalFilters: filters, noFocusOnLoad: true });
		this.RegistryReceptDataGrid.loadData({ globalFilters: filters, noFocusOnLoad: true });

		base_form.load({
			failure: function() {
				form.loadMask.hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
			}.createDelegate(this),
			params: {
				'RegistryRecept_id': form.RegistryRecept_id
			},
			success: function() {
				form.loadMask.hide();
				base_form.findField('ReceptUploadLog_setDT').focus();
			}.createDelegate(this),
			url: '/?c=RegistryRecept&m=loadRegistryReceptViewForm'
		});
	}
});