/**
* swSmpFarmacyRegisterWindow - регистр прихода-расхода медикаментов СМП .
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Miyusov Alexandr
* @copyright    Copyright (c) 2013 Swan Ltd.
* @version      17.01.2013
*/


sw.Promed.swSmpFarmacyRegisterWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	maximizable: false,
	split: true,
	width: 700,
	height: 500,
	layout: 'fit',
	id: 'swSmpFarmacyRegisterWindow',
	title: lang['registr_prihoda-rashoda_medikamentov_smp'],
	codeRefresh: true,
	objectName: 'swSmpFarmacyRegisterWindow',
	objectSrc: '/jscore/Forms/Common/swSmpFarmacyRegisterWindow.js',
	listeners: 
	{
		hide: function() 
		{
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,	
	buttons: 
	[{
		text: '-'
	},{	
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},{
		handler: function() 
		{
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		// tabIndex: 207,
		text: BTN_FRMCLOSE
	}],
	show: function() 
	{
//		debugger;
		sw.Promed.swSmpFarmacyRegisterWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.focus();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		this.SmpFarmacyRegisterPanel.ViewActions['action_view'].hide();
		this.SmpFarmacyRegisterPanel.ViewActions['action_delete'].hide();
		
		this.SmpFarmacyHistoryPanel.ViewActions['action_delete'].hide();
		this.SmpFarmacyHistoryPanel.ViewActions['action_edit'].hide();
		this.SmpFarmacyHistoryPanel.ViewActions['action_view'].hide();
		this.SmpFarmacyHistoryPanel.ViewActions['action_add'].hide();
		this.SmpFarmacyHistoryPanel.ViewActions['action_refresh'].hide();
		
		this.SmpFarmacyRegisterPanel.runAction('action_refresh');

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
	},
	
	addDrug: function() {
		var form = this;
		getWnd('swSmpFarmacyAddDrugWindow').show({
			callback: function() {
				form.SmpFarmacyRegisterPanel.runAction('action_refresh');
			}
		});
	},
	
	removeDrug: function() {
		var form = this;
		
		if ( !this.SmpFarmacyRegisterPanel.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = this.SmpFarmacyRegisterPanel.getGrid().getSelectionModel().getSelected();

		if ( !selected_record.get('CmpFarmacyBalance_id') ||  !selected_record.get('DrugTorg_Name')||  !selected_record.get('CmpFarmacyBalance_DoseRest')) {
			return false;
		}

		getWnd('swSmpFarmacyRemoveDrugWindow').show({
			'Drug_id':selected_record.get('Drug_id'),
			'Drug_Fas':selected_record.get('Drug_Fas')||'1',
			'DrugUnit_Name':selected_record.get('Drug_PackName'),
			'CmpFarmacyBalance_id':selected_record.get('CmpFarmacyBalance_id'),
			'DrugTorg_Name':selected_record.get('DrugTorg_Name'),
			'maxDoseCount':selected_record.get('CmpFarmacyBalance_DoseRest'),
			'maxPackCount':selected_record.get('CmpFarmacyBalance_PackRest'),
			callback: function() {
				form.SmpFarmacyRegisterPanel.runAction('action_refresh');
			}
		});
	},

	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.SmpFarmacyRegisterPanel = new sw.Promed.ViewFrame(
		{
			title:lang['spisok_medikamentov'],
			id: 'SmpFarmacyRegisterGrid',
			border: true,
			anchor:'-0, 50%',
			dataUrl: '/?c=CmpCallCard&m=loadSmpFarmacyRegister',
			toolbar: true,
			autoLoadData: false,
			stringfields: [
				{name: 'CmpFarmacyBalance_id', header: 'ID', key: true, hidden: true, isparams: true},
				{name: 'Drug_id',hidden: true},
				{name: 'AddDate',header: lang['data_popolneniya'], width: 110},
				{name: 'DrugTorg_Name', id:'autoexpand', header: lang['naimenovanie']},
				{name: 'Drug_PackName', width: 140, header: lang['ed_uch']},
				{name: 'Drug_Fas', width: 140, header: lang['kol-vo_v_upakovke']},
				{name: 'CmpFarmacyBalance_PackRest', width: 150, header: lang['ostatok_ed_uch']},
				{name: 'CmpFarmacyBalance_DoseRest', width: 150, header: lang['ostatok_ed_doz']}
			],
			actions: [
				{name:'action_add', tooltip: lang['dobavit_medikament_v_registr'],func: form.addDrug.createDelegate(form) },
				{name:'action_edit', text:lang['spisat'], tooltip: lang['spisat_medikament_na_brigadu'], func: form.removeDrug.createDelegate(form) }
			],
			onRowSelect: function(sm,index,record)
			{
				if (record.data['CmpFarmacyBalance_id'] == null) {
					return false;
				}
				params = {'CmpFarmacyBalance_id': record.data['CmpFarmacyBalance_id'], start: 0, limit: 50};
				form.SmpFarmacyHistoryPanel.loadData({globalFilters:params});
			}
		});
		
		this.SmpFarmacyHistoryPanel = new sw.Promed.ViewFrame(
		{
			title: lang['istoriya_spisaniya_medikamenta'],
			id: 'SmpFarmacyHistoryGrid',
			paging: true,
			anchor:'-0, 50%',
			dataUrl: '/?c=CmpCallCard&m=loadSmpFarmacyRegisterHistory',
			toolbar: true,
			root: 'data',
			pageSize: 50,
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				
				{name: 'CmpFarmacyBalanceRemoveHistory_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugTorg_Name', header: lang['nazvanie_medikamenta'], width: 150},
				{name: 'EmergencyTeam_Num', header: lang['№_brigadyi'], width: 100},
				{name: 'Person_Fin', header: lang['starshiy_brigadyi'], width: 260},
				{name: 'CmpCallCard_prmDate', header: lang['data_cpisaniya'], type: 'date', width: 100},
				{name: 'CmpFarmacyBalanceRemoveHistory_DoseCount', width: 150, header: lang['kolichestvo_doz'], align: 'right'},
				{name: 'CmpFarmacyBalanceRemoveHistory_PackCount', width: 150, header: lang['kolichestvo_ed_ucheta'], align: 'right'}				
			],
			actions:
			[
				{name:'action_delete'} // Вроде никаких дополнительных действий не планируется 
			]
		});

	
		Ext.apply(this,	{
			layout: 'fit',
			items: [{
				layout: 'anchor',
				items:[
					form.SmpFarmacyRegisterPanel,
					form.SmpFarmacyHistoryPanel
				]					
			}]
		});

		sw.Promed.swSmpFarmacyRegisterWindow.superclass.initComponent.apply(this, arguments);
	}
	});