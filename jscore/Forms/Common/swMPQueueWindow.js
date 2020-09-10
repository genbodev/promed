/**
 * swMPQueueWindow - окно журнала направлений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Salakhov Rustam
 * @version      27.08.2010
 */
/*NO PARSE JSON*/

sw.Promed.swMPQueueWindow = Ext.extend(sw.Promed.BaseForm,
{
	layout: 'fit',
	modal: true,
	resizable: false,
	draggable: false,
	closable: true,
	LpuSectionProfile_id: null,
	Lpu_id: null,
	closeAction: 'hide',
	plain: true,
	maximized: true,
	title: WND_DIRECTION_JOURNAL,
	iconCls: 'workplace-mp16',
	id: 'swMPQueueWindow',
	mode: 'select', //режим открытия окна: 'select' - выбор записи, 'view' - просмотр очереди
	TimetableGraf_id: 0, // id бирки для которой открыт выбор записи из очереди по профилю
	params: {
		limit: 100,
		start: 0 
	},
	initComponent: function(){
		var ths = this;

		this.bj = new sw.Promed.BaseJournal({
			ownerWindow: ths,
			winType: 'queue',
			region: 'center',
			actions:[
				'action_add',
				'action_delete',
				'action_add_incoming',
				'action_leave_queue',
				'action_in_queue',
				'action_redirect',
				'action_rewrite',
				'action_view',
				'action_print'
			]
			,noAutoSearch: true// #142955 поиск не автоматический
		});

		Ext.apply(this, {
			layout: 'border',
			items: [
				this.bj
			],
			buttons: [{
				text: '-'
			}, HelpButton(this, TABINDEX_MPSCHED + 98), {
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});

		sw.Promed.swMPQueueWindow.superclass.initComponent.apply(this, arguments);

   },
	
	show: function(){
		this.ARMType = null;
		this.callback = Ext.emptyFn;
		this.mode = 'select';
		this.onSelect = Ext.emptyFn;
		this.TimetableGraf_id = 0;
		this.bj.Lpu_sid = null;
		this.bj.Lpu_did = null;
		this.bj.MedService_did = null;
		this.bj.LpuSectionProfile_id = null;
		var wnd = this;

		if (getGlobalOptions().groups.inlist(['TFOMSUser', 'SMOUser']) ){
			if (arguments[0].LpuSectionProfile_id) {
				this.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
			} else {
					this.LpuSectionProfile_id = null;
			}

			if (arguments[0].Lpu_id) {
				this.Lpu_id = arguments[0].Lpu_id;
			} else {
					this.Lpu_id = null;
			}

			/*Ext.getCmp('MPQW_SelectBtn').hide();
			this.QueueGrid.ViewActions.action_add.hide();
			this.QueueGrid.ViewActions.action_view.hide();
			this.QueueGrid.ViewActions.action_delete.hide();
			this.QueueGrid.ViewActions.action_edit.hide();*/

		} else {

			if (arguments[0]) {
				if( !arguments[0].userMedStaffFact )
				{
					Ext.Msg.alert('Уведомление', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.');
					this.hide();

					return false;
				}
				if (arguments[0].userMedStaffFact.userMedStaffFact) {
					this.userMedStaffFact = arguments[0].userMedStaffFact.userMedStaffFact;
				} else {
					this.userMedStaffFact = arguments[0].userMedStaffFact;
				}

				if (arguments[0].ARMType) this.ARMType = arguments[0].ARMType;
				if (arguments[0].callback) this.callback = arguments[0].callback;
				if (arguments[0].onSelect) this.onSelect = arguments[0].onSelect;
				if (arguments[0].mode) this.mode = arguments[0].mode;
				if(arguments[0].params && arguments[0].params.TimetableGraf_id) this.TimetableGraf_id = arguments[0].params.TimetableGraf_id;

				if (arguments[0].LpuSectionProfile_id) {
					this.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
				} else {
					this.LpuSectionProfile_id = null;
				}

				if (arguments[0].Lpu_id) {
					this.Lpu_id = arguments[0].Lpu_id;
				} else {
					this.Lpu_id = null;
				}

				if (arguments[0].MedService_id) {
					this.MedService_id = arguments[0].MedService_id;
				} else {
					this.MedService_id = null;
				}

				if (arguments[0].dateRangeMode) {
					this.bj.dateRangeMode = arguments[0].dateRangeMode;
				}
				if (arguments[0].resetRecordDate === true || arguments[0].resetRecordDate === false) {
					this.bj.resetRecordDate = arguments[0].resetRecordDate;
				}
			}
		}

		this.bj.ARMType = this.ARMType;

		var base_form = this.bj.mainForm.getForm();
		base_form.reset();

		this.bj.TabPanel.show();
		this.bj.useSearchType = true;
		this.bj.useCase = arguments[0].useCase || '';
		this.bj.MedService_did = null;
		base_form.findField('EvnStatus_id').setDisabled('record_from_queue' == this.bj.useCase);
		base_form.findField('DirType_id').clearValue();
		base_form.findField('DirType_id').getStore().clearFilter();
		base_form.findField('DirType_id').lastQuery = '';
		this.bj.mainGrid.getAction('action_view').setHidden(false);
		this.bj.mainGrid.getAction('action_rewrite').setHidden(false);
		this.bj.mainGrid.getAction('action_add_incoming').enable();
		this.bj.mainGrid.getAction('action_adddirection').setHidden(false);
		//this.bj.mainGrid.getAction('action_redirect').setHidden(false);
		this.bj.mainGrid.getAction('action_leave_queue').setHidden(false);
		if ('record_from_queue' == this.bj.useCase) {
			this.bj.mainGrid.getAction('action_view').setHidden(true);
			this.bj.mainGrid.getAction('action_rewrite').setHidden(true);
			this.bj.mainGrid.getAction('action_add_incoming').disable();
			this.bj.mainGrid.getAction('action_adddirection').setHidden(true);
			this.bj.mainGrid.getAction('action_redirect').setHidden(true);
			this.bj.mainGrid.getAction('action_cancel_incoming').setHidden(true);
			this.bj.mainGrid.getAction('action_cancel_outcoming').setHidden(true);
		}
		if(getRegionNick()==='vologda' && getCurArm()==='lab'){//#PROMEDWEB-14156
			this.bj.mainGrid.getAction('action_adddirection').disable();
		}
		if (this.bj.useCase.inlist(['record_from_queue', 'open_from_polka'])) {
			base_form.findField('DirType_id').getStore().filterBy(function(rec) {
				return ( 3 == rec.get('DirType_id') || 16 == rec.get('DirType_id') );
			});
		}
		
		this.bj.RecordMenu.items.items[1].enable();
		if (this.ARMType && this.ARMType == 'callcenter') {
			// вкладки для call-центра не нужны
			this.bj.TabPanel.hide();
			this.bj.useSearchType = false;
			this.bj.mainGrid.getAction('action_add_incoming').disable();
			this.bj.RecordMenu.items.items[1].disable();
		}

		base_form.findField('MedService_did').enable();
		
		if (getRegionNick() === 'kz') {
			if (this.ARMType && (this.ARMType === 'common' || this.ARMType === 'regpol')) {
				this.bj.mainGrid.getAction('action_add_incoming').disable();
				this.bj.RecordMenu.items.items[1].disable();
			}
		}
		
		if (this.ARMType && this.ARMType.inlist(['labdiag', 'funcdiag'])) {
			// вкладки для арм функц. диагностики не нужны
			this.bj.TabPanel.hide();
			this.bj.useSearchType = false;

			if(getRegionNick()=='buryatiya') {
				this.bj.mainGrid.getAction('action_add_incoming').enable();
				this.bj.RecordMenu.items.items[1].enable();
			} else {
				this.bj.mainGrid.getAction('action_add_incoming').disable();
				this.bj.RecordMenu.items.items[1].disable();
			}
			
			var MedService_id;
			if (this.userMedStaffFact && this.userMedStaffFact.MedService_id) {
				this.bj.MedService_did = this.userMedStaffFact.MedService_id;
				MedService_id = this.userMedStaffFact.MedService_id;
			}
			base_form.findField('MedService_did').disable();

			if(this.MedService_id)
				MedService_id = this.MedService_id;

			var usluga_combo = base_form.findField('UslugaComplex_id');
			usluga_combo.getStore().removeAll();
			usluga_combo.getStore().load({
				params: {
					UslugaGost_Code: 'FU',
					MedService_id: MedService_id?MedService_id:'',
					level: 0
				}
			});
			usluga_combo.getStore().baseParams = {
				UslugaGost_Code: 'FU',
				MedService_id: MedService_id?MedService_id:'',
				level: 0
			};
		}

		sw.Promed.swMPQueueWindow.superclass.show.apply(this, arguments);

		base_form.findField('LpuSectionProfile_did').enable();
		base_form.findField('Lpu_did').enable();

		if (this.Lpu_id&&this.LpuSectionProfile_id) {
			this.bj.LpuSectionProfile_id = this.LpuSectionProfile_id;
			base_form.findField('LpuSectionProfile_did').setValue(this.LpuSectionProfile_id);
			this.bj.Lpu_did = this.Lpu_id;
			base_form.findField('Lpu_did').setValue(this.Lpu_id);
			base_form.findField('Lpu_did').fireEvent('change', base_form.findField('Lpu_did'), base_form.findField('Lpu_did').getValue());
		} else if (this.Lpu_id&&this.MedService_id) {
			this.bj.MedService_did = this.MedService_id;
			base_form.findField('MedService_did').setValue(this.MedService_id);
			this.bj.Lpu_did = this.Lpu_id;
			base_form.findField('Lpu_did').setValue(this.Lpu_id);
			base_form.findField('Lpu_did').fireEvent('change', base_form.findField('Lpu_did'), base_form.findField('Lpu_did').getValue());
		}

		var fieldPersonSurname = base_form.findField('Person_SurName');
		var fieldPersonFirname = base_form.findField('Person_FirName');
		var fieldPersonSecname = base_form.findField('Person_SecName');
		var fieldPersonBirthday = base_form.findField('Person_Birthday');

		if(arguments[0].personData){// #142955 если выбран пациент
			if(fieldPersonSurname){
				fieldPersonSurname.disable();// disable спасает от двойного doReset в BaseJournal
				if(arguments[0].personData.Person_Surname)
					fieldPersonSurname.setValue(arguments[0].personData.Person_Surname);
			}
			if(fieldPersonFirname){
				fieldPersonFirname.disable();
				if(arguments[0].personData.Person_Firname)
					fieldPersonFirname.setValue(arguments[0].personData.Person_Firname);
			}
			if(fieldPersonSecname){
				fieldPersonSecname.disable();
				if(arguments[0].personData.Person_Secname)
					fieldPersonSecname.setValue(arguments[0].personData.Person_Secname);
			}
			if(fieldPersonBirthday){
				fieldPersonBirthday.disable();
				if(arguments[0].personData.Person_Birthday)
					fieldPersonBirthday.setValue(arguments[0].personData.Person_Birthday);
			}

			this.bj.allTime();
			this.bj.mode = 'allTime';
			this.bj.dateRangeMode = 'allTime';
		}
		else{// пациент не выбран
			if(fieldPersonSurname)
				base_form.findField('Person_SurName').enable();// при повторном открытии формы из левого меню может быть заблокировано
			if(fieldPersonFirname)
				base_form.findField('Person_FirName').enable();
			if(fieldPersonSecname)
				base_form.findField('Person_SecName').enable();
			if(fieldPersonBirthday)
				base_form.findField('Person_Birthday').enable();

			this.bj.resetRecordDate = true;
			this.bj.currentDay();
			this.bj.mode = 'day';
			this.bj.dateRangeMode = 'day';
		}

		var grid = this.bj.mainGrid;
		if(grid)
			grid.removeAll();

		this.bj.getCurrentDateTime();
	}
});
