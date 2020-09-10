/**
* swEvnPrescrConsJournalWindow - окно журнала консультаций стационара
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      11.2012
* @comment      
**/
/*NO PARSE JSON*/
sw.Promed.swEvnPrescrConsJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrConsJournalWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrConsJournalWindow.js',
	
	title: lang['jurnal_konsultatsiy_statsionara'],
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	minHeight: 400,
	minWidth: 700,
	modal: true,
	plain: true,
	id: 'swEvnPrescrConsJournalWindow',
	
	//объект с параметрами рабочего места, с которыми была открыта форма
	userMedStaffFact: null,

	show: function() {
		sw.Promed.swEvnPrescrConsJournalWindow.superclass.show.apply(this, arguments);
		
		if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.');
		} else {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		this.doReset();
	},
	
	doSearch: function() {
		this.loadGridWithFilter(false);
	},
	doReset: function() {
		this.loadGridWithFilter(true);
	},
	loadGridWithFilter: function(clear) {
		var grid = this.EvnPrescrConsGrid;
		grid.removeAll();
		var LpuSection_id = this.userMedStaffFact.LpuSection_id;
		var LpuSectionProfile_id = this.userMedStaffFact.LpuSectionProfile_id;
		var MedPersonal_id  = this.userMedStaffFact.MedPersonal_id;
		if (clear)
		{
			//default filter
		}
		else
		{
			//doSearch
		}
		grid.loadData({
			globalFilters: {
				limit: 100,
				start: 0,
				LpuSection_id : LpuSection_id,
				LpuSectionProfile_id: LpuSectionProfile_id,
				MedPersonal_id : MedPersonal_id 
			}
		});
	},
	getSelectedRecord: function()
	{
		var record = this.EvnPrescrConsGrid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.data.EvnPrescrCons_id)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		return record;
	},
	openEmk: function()
	{
/*
Доступные действия над записями журнала: открыть эмк с правом добавления документов. Открыть эмк можно, если случай, из которого выписано направление, не закрыт.
Желательно реализовать следующую логику:

*/
		var record = this.getSelectedRecord();
		if (record == false) return false;
		if (record.data.Person_IsDead == 2 || record.data.Evn_IsClose == 2)
		{
			sw.swMsg.alert(lang['soobschenie'], lang['vyi_ne_mojete_otkryit_emk_dlya_dobavleniya_dokumentov_t_k_sluchay_zakryit_ili_patsient_umer']);
			return false;
		}
		if (getWnd('swPersonEmkWindow').isVisible())
		{
			getWnd('swPersonEmkWindow').hide();
		}
		// чтобы при открытии ЭМК загрузилась форма просмотра КВС с движением, из которого была назначена консультация
		var searchNodeObj = false;
		if(record.data.EvnPS_id) {
			searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnPS',
				Evn_id: record.data.EvnPS_id
			};
		}
		//чтобы врач имел возможность добавить свой документ по консультации
		var accessViewFormDelegate = {};
		accessViewFormDelegate['FreeDocumentList_'+ record.data.EvnPrescrCons_pid +'_adddoc'] = true;
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.data.Person_id,
			Server_id: record.data.Server_id,
			PersonEvn_id: record.data.PersonEvn_id,
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			searchNodeObj: searchNodeObj,
			accessViewFormDelegate: accessViewFormDelegate,
			onSaveEvnDocument: function(iscreate, data)
			{
				//log(['onSaveEvnDocument', iscreate, data]);
				//Если в эмк врач добавляет документ в случае, из которого выписано направление на консультацию, то проставляется отметка о выполнении назначения данным врачом.
				if(iscreate && data.XmlType_id && data.XmlType_id == 2 && data.Evn_id == record.data.EvnPrescrCons_pid) {
					sw.Promed.EvnPrescr.execRequest({
						ownerWindow: this
						,EvnPrescr_id: record.data.EvnPrescrCons_id
						,Timetable_id: null
						,PrescriptionType_id: 4
						,onExecSuccess: function(){}
					});
				}
			}.createDelegate(this),
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},

	initComponent: function() {

		this.EvnPrescrConsGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_view', text: lang['otkryit_emk'], tooltip: lang['otkryit_emk_s_pravom_dobavleniya_dokumentov'], handler: function() { this.openEmk(); }.createDelegate(this) },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_cancel', hidden: true, disabled: true},
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],

			stringfields: [
				{ name: 'EvnPrescrCons_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnPrescrCons_pid', type: 'int', hidden: true },
				{ name: 'EvnPS_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'LpuSection_id', type: 'int', hidden: true },
				{ name: 'Lpu_did', type: 'int', hidden: true },
				{ name: 'LpuSection_did', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'PrescriptionStatusType_id', type: 'int', hidden: true },
				{ name: 'Person_IsDead', type: 'int', hidden: true },
				{ name: 'Evn_IsClose', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true},
				{ name: 'PersonEvn_id', type: 'int', hidden: true},
				{ name: 'Server_id', type: 'int', hidden: true},
				{ name: 'EvnPrescrCons_IsCito', header: "Cito", renderer: sw.Promed.Format.checkColumn, width: 30},
				{ name: 'EvnDirection_setDate', type: 'string', header: lang['data_napravleniya'], width: 110 },
				{ name: 'Person_fio', type: 'string', header: lang['fio_patsienta'], autoexpand: true, autoExpandMin: 170 },
				{ name: 'Diag_FullName', type: 'string', header: lang['diagnoz'], width: 170 },
				{ name: 'MedPersonal_Name', type: 'string', header: lang['vrach_vyipisavshiy_napravlenie'], width: 190 },
				{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], width: 120 },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 150 },
				{ name: 'LpuSectionWard_Name', type: 'string', header: lang['palata'], width: 120 }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnPrescr&m=loadEvnPrescrConsJournal',
			/*
			object: 'EvnPrescrCons',
			layout: 'fit',
			root: 'data',
			totalProperty: 'totalCount',
			paging: true,
			*/
			region: 'center',
			toolbar: true,
			onLoadData: function() {
				this.setActionDisabled('action_view',!(this.getCount()>0));
			},
			onRowSelect: function(sm,rowIdx,record) {
				this.setActionDisabled('action_view',(record.data.Person_IsDead == 2 || record.data.Evn_IsClose == 2));
			},
			onDblClick: function() {
				this.openEmk();
			}.createDelegate(this),
			onEnter: function() {
				this.onDblClick();
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [ 
				this.EvnPrescrConsGrid
			]
		});
		sw.Promed.swEvnPrescrConsJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});
