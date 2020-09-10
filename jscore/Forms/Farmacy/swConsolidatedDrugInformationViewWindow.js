/**
* swConsolidatedDrugInformationViewWindow - окно просмотра информации о местонахождении и статусе ЛС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Salakhov R.
* @version      05.2013
* @comment      
*/
sw.Promed.swConsolidatedDrugInformationViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['informatsiya_o_medikamente'],
	layout: 'border',
	id: 'ConsolidatedDrugInformationViewWindow',
	modal: false,
	shim: false,
	resizable: false,
	maximizable: false,
	maximized: true,
	show: function() {
        var wnd = this;
		var msf_store = sw.Promed.MedStaffFactByUser.store;

		if (msf_store.findBy(function(rec) { return rec.get('ARMType') == 'minzdravdlo'; }) < 0 || !getGlobalOptions().region.nick.inlist([ 'pskov', 'saratov' ])) {
			return false;
		}

		this.Drug_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].Drug_id ) {
			this.Drug_id = arguments[0].Drug_id;
		}
		wnd.recept_load = false; //признак загрузки грида с рецептами
        wnd.setTitle(lang['informatsiya_o_medikamente']);
		wnd.TabPanel.setActiveTab('ostat_tab');

		this.OstatGrid.removeAll();
		this.ReceptGrid.removeAll();

		if (this.Drug_id > 0) {
			wnd.setDrugName();
			this.OstatGrid.loadData({globalFilters:{
				Drug_id: this.Drug_id
			}});
		} else {
			return false;
		}

		sw.Promed.swConsolidatedDrugInformationViewWindow.superclass.show.apply(this, arguments);
	},
	setDrugName: function() {
		var wnd = this;

		Ext.Ajax.request({
			failure: function() {
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_informatsiyu_o_medikamente']);
			},
			params: {
				Drug_id: wnd.Drug_id
			},
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( response_obj && response_obj[0] && response_obj[0].Drug_Name) {
					wnd.setTitle(lang['informatsiya_o_medikamente']+response_obj[0].Drug_Name);
				}
			}.createDelegate(this),
			url: '/?c=RlsDrug&m=loadDrugSimpleList'
		});
	},
	initComponent: function() {
		var wnd = this;

		wnd.OstatGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RlsDrug&m=loadFullOstatList',
			region: 'center',
			id: 'cdivOstatGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_refresh'},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print'}
			],
			stringfields: [
				{name: 'DrugOstatRegistry_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'Org_Name', header: lang['organizatsiya'], width: 120, id: 'autoexpand'},
				{name: 'DrugOstatRegistry_Kolvo', header: lang['kolichestvo_medikamenta'], width: 120},
				{name: 'DrugOstatRegistry_Price', header: lang['tsena_rub'], width: 120},
				{name: 'DrugOstatRegistry_Sum', header: lang['summa_rub'], width: 120},
				{name: 'Okei_Name', header: lang['ed_izmereniya'], width: 120},
				{name: 'WhsDocumentUc_Num', header: lang['№_gk'], width: 120},
				{name: 'WhsDocumentUc_Name', header: lang['naimenovanie'], width: 120, hidden: true, hideable: true},
				{name: 'WhsDocumentUc_Date', type: 'date', header: lang['data'], width: 120, hidden: true, hideable: true},
				{name: 'DrugFinance_Name', header: lang['istochnik_finansirovaniya'], width: 120},
				{name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashoda'], width: 120},
				{name: 'SubAccountType_Name', header: lang['tip_subscheta'], width: 120}
			],
			title: false,
			toolbar: false
		});

		wnd.ReceptGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RlsDrug&m=loadFullReceptList',
			region: 'center',
			id: 'cdivReceptGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_refresh'},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print'}
			],
			stringfields: [
				{name: 'EvnRecept_id', type: 'int', header: 'ID', key: true},
				{name: 'Recept_Status', header: lang['status'], type: 'string'},
				{name: 'EvnRecept_setDate', header: lang['data'], type: 'date', width: 100},
				{name: 'EvnRecept_Ser', header: lang['seriya'], type: 'string', width: 70},
				{name: 'EvnRecept_Num', header: lang['nomer'], type: 'string', width: 100},
				{name: 'Person_Surname', header: lang['familiya'], type: 'string'},
				{name: 'Person_Firname', header: lang['imya'], type: 'string'},
				{name: 'Person_Secname', header: lang['otchestvo'], type: 'string'},
				{name: 'Person_Birthday', header: lang['data_rojdeniya'], type: 'date', width: 100},
				{name: 'MedPersonal_Fio', header: lang['vrach'], type: 'string', width: 200, id: 'autoexpand'},
				{name: 'EvnRecept_Kolvo', header: lang['kolichestvo_medikamenta'], type: 'string', width: 80}
			],
			title: false,
			toolbar: false
		});

		wnd.TabPanel = new Ext.TabPanel({
			border: false,
			activeTab:0,
			autoScroll: true,
			region: 'center',
			layoutOnTabChange: true,
			listeners: {
				tabchange: function(panel, tab) {
					if (tab.id == 'recept_tab' && !wnd.recept_load) {
						wnd.ReceptGrid.loadData({
							globalFilters:{
								Drug_id: wnd.Drug_id
							},
							callback: function() {
								wnd.recept_load = true;
							}
						});
					}
				}
			},
			items: [{
				id: 'ostat_tab',
				title: lang['upakovki'],
				layout: 'border',
				frame: true,
				border:false,
				autoScroll: true,
				minHeight: 400,
				items:[wnd.OstatGrid]
			}, {
				id: 'recept_tab',
				title: lang['retseptyi'],
				layout: 'border',
				frame: true,
				border:false,
				autoScroll: true,
				minHeight: 400,
				items:[wnd.ReceptGrid]
			}]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[wnd.TabPanel]
		});
		sw.Promed.swConsolidatedDrugInformationViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});