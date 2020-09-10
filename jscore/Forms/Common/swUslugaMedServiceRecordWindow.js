/**
* swUslugaMedServiceRecordWindow - окно записи на услугу (услуги) служб
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* Алгоритм работы:
* Нет расписания - ставим в очередь
*	Полка, стац
*		сначала создаем направление (системное или электронное), затем ставим в очередь
*	Парка
*		сначала создаем системное направление, затем заказываем услугу, затем ставим в очередь
*
* Есть расписание - запись на бирку
*	Полка, стац
*		сначала создаем направление (системное или электронное), затем записываем на бирку
*	Парка
*		сначала создаем системное направление, затем заказываем услугу, затем записываем на бирку
*
* Если передан ид назначения, то при сохранении направления должна быть вызвана хранимка сохраняющая связь направления с назначением
*
* P.S. Херово, что это все этапы реализованы на прикладном уровне, т.к. нужна транзакция (в одном обращении к серверу передавать все параметры)
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      22.03.2012
* @comment      tabIndex: 
*/

/*NO PARSE JSON*/

sw.Promed.swUslugaMedServiceRecordWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaMedServiceRecordWindow',
	objectSrc: '/jscore/Forms/Common/swUslugaMedServiceRecordWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swUslugaMedServiceRecordWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	
	_isEmptyObject: function(o)
	{
		var nef = true;
		if (o)
		{
			for(var key in o) 
			{
				if (o[key]!='')
				{
					nef = false;
					break;
				}
			}
		}
		return nef;
	},
	doReset: function() {
		this.viewFrame.getGrid().getStore().baseParams = {};
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
	},
	doSearch: function() 
	{
		this.viewFrame.removeAll(true);
		var params = {};
		params.uslugaList = this.params.uslugaList.toString(); 
		params.start = 0; 
		params.limit = 100;
		this.viewFrame.loadData({globalFilters:params});
	},
	setMyTitle: function()
	{
		// формируем заголовок 
		var title = 'Запись пациента: <font color="blue">'+this.params.Person_Surname+' '+this.params.Person_Firname+' '+this.params.Person_Secname+'</font>';
		//log(this.params);
		if (this.params.MedService_Nick)
		{
			title = title + " / "+lang['slujba']+ " " + this.params.MedService_Nick;
		}
		this.setTitle(title);
	},
	getItem: function () 
	{
		return this.viewFrame;
	},
	/**
	* Выбор службы - открываем окно расписания службы
	*/
	onOkButtonClick: function() {
		var win = this;
		var rec = this.viewFrame.getGrid().getSelectionModel().getSelected();
		if (!rec) {
			sw.swMsg.alert(lang['soobschenie'], lang['vyi_nichego_ne_vyibrali']);
			return false;
		}
		var arguments = {
			Person: {
				Person_Surname: this.params.Person_Surname,
				Person_Firname: this.params.Person_Firname,
				Person_Secname: this.params.Person_Secname,
				Person_Birthday: this.params.Person_Birthday,
				Person_id: this.params.Person_id,
				Server_id: this.params.Server_id,
				PersonEvn_id: this.params.PersonEvn_id
			},
			MedService_id: rec.get('MedService_id'),
			MedServiceType_id: rec.get('MedServiceType_id'),
			MedService_Nick: rec.get('MedService_Nick'),
			MedService_Name: rec.get('MedService_Name'),
			MedServiceType_SysNick: rec.get('MedServiceType_SysNick'),
			Lpu_did: rec.get('Lpu_id'),
			/*
			ARMType: this.ARMType,
			fromEmk: this.fromEmk,
			mode: this.formMode,
			onSaveRecord: (typeof this.onSaveRecord == 'function') ? this.onSaveRecord : Ext.emptyFn,
			*/
			callback: function(data){
				getWnd('swTTMSScheduleRecordWindow').hide();
			}.createDelegate(this),
			UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id') || null,
			UslugaComplex_id: rec.get('UslugaComplex_id') || null,
			Diag_id: this.params.Diag_id || null,
			EvnDirection_rid: this.params.EvnDirection_rid,	//КВС, ТАП					
			EvnDirection_pid: this.params.EvnDirection_pid,	//Посещение, движение					
			EvnQueue_id: this.params.EvnQueue_id || null,
			QueueFailCause_id: this.params.QueueFailCause_id || null,
			EvnPrescr_id: this.params.EvnPrescr_id || null,
			PrescriptionType_Code: this.params.PrescriptionType_Code || null,
			LpuUnitType_SysNick: rec.get('LpuUnitType_SysNick') || null,
			LpuSection_uid: rec.get('LpuSection_id') || null,
			LpuSection_Name: rec.get('LpuSection_Name') || null,
			LpuUnit_did: rec.get('LpuUnit_id') || null,
			LpuSectionProfile_id: rec.get('LpuSectionProfile_id') || '0',
			parentEvnClass_SysNick: this.params.parentEvnClass_SysNick,						
			TimetableMedService_id: this.params.TimetableMedService_id
		};
		
		getWnd('swTTMSScheduleRecordWindow').show(arguments);
	},
	ttmsApply: function(ttms_win)
	{
		ttms_win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		Ext.Ajax.request({
			url: C_TTMS_APPLY,
			params: {
				TimetableMedService_id: ttms_win.TimetableMedService_id,
				Person_id: ttms_win.Person_id,
				Evn_id: null
			},
			callback: function(options, success, response) {
				ttms_win.getLoadMask().hide();
				ttms_win.loadSchedule();
				ttms_win.hide();
			},
			failure: function() {
				ttms_win.getLoadMask().hide();
			}
		});
	},
	/*
	* Тут вся логика по выписке направления при записи и постановке в очередь
	*/
	createDirection: function(conf)
	{
		//log('createDirection');
		//log(conf);
		if (!conf || !conf.params || !conf.params.Lpu_did) {
			return false;
		}
		var formParams = conf.params || null;
		if(formParams.LpuUnitType_SysNick && formParams.LpuUnitType_SysNick == 'parka') {
			formParams.timetable = 'TimetablePar';//для фильтра DirType_id
			formParams.DirType_id = 2;
		}
		formParams.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
		formParams.Diag_id = this.params.Diag_id;
		formParams.Person_id = this.params.Person_id;
		formParams.PersonEvn_id = this.params.PersonEvn_id;
		formParams.Server_id = this.params.Server_id;
		formParams.EvnDirection_pid = this.params.EvnDirection_pid;
		//при направлении по назначению обязательные поля
		formParams.EvnPrescr_id = this.params.EvnPrescr_id;
		formParams.PrescriptionType_Code = this.params.PrescriptionType_Code;
		
		var evnDirectionEditWindowParams = {callback: conf.callback, onHide: conf.onHide, formParams: formParams, is_cito: false};
		var saveSysEvnDirectionParams = {callback: conf.callback, params: formParams};
		if (conf.is_auto) {
			//создание системного направления
			this.saveSysEvnDirection(saveSysEvnDirectionParams);
		} else if (conf.params.Lpu_did == getGlobalOptions().lpu_id){
			//Эл.направление в своё ЛПУ
			sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyipisat_novoe_elektronnoe_napravlenie'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					
					if ('yes' == buttonId)
					{
						//Эл.направление в своё ЛПУ
						this.openDirectionEditWindow(evnDirectionEditWindowParams);
					}
					else 
					{
						//системное направление в своё ЛПУ
						this.saveSysEvnDirection(saveSysEvnDirectionParams);
					}
				}.createDelegate(this)
			});
		} else {
			//Эл.направление в чужое ЛПУ
			this.openDirectionEditWindow(evnDirectionEditWindowParams);
		}
	},	
	/** Создание системного направления
	 */
	saveSysEvnDirection: function(option) {
		option.params.EvnDirection_IsAuto = 2;
		option.params.EvnDirection_setDate = getGlobalOptions().date;
		option.params.EvnDirection_Num = '0';
		option.params.LpuSectionProfile_id = option.params.LpuSectionProfile_id || '0';
		option.params.MedPersonal_zid = '0';	
		option.params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=saveEvnDirection',
			params: option.params,
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['pri_sozdanii_sistemnogo_napravleniya_proizoshla_oshibka']);
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						answer.Evn_id = option.params.EvnDirection_pid;
						option.callback(answer);
						return true;
					} else if (answer.Error_Msg) {
						Ext.Msg.alert(lang['oshibka'], answer.Error_Msg);
						return false;
					}
				}
				Ext.Msg.alert(lang['oshibka'], lang['pri_sozdanii_sistemnogo_napravleniya_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				return false;
			}
		});
	},
	/** Создание электронного направления
	 */
	openDirectionEditWindow: function(options)
	{
		var callback = function(data){
			data.evnDirectionData.Evn_id = data.evnDirectionData.EvnDirection_pid || null;
			if(typeof options.callback == 'function')
				options.callback(data.evnDirectionData);
		};
		
		getWnd('swEvnDirectionEditWindow').show({
			action: 'add',
			callback: callback,
			onHide: (typeof options.onHide == 'function')?options.onHide:Ext.emptyFn,
			Person_id: this.params.Person_id,
			Person_Surname: this.params.Person_Surname,
			Person_Firname: this.params.Person_Firname,
			Person_Secname: this.params.Person_Secname,
			Person_Birthday: this.params.Person_Birthday,
			is_cito: options.is_cito || false,
			formParams: options.formParams
		});
	},
	
	
	initComponent: function() {
		var win = this;

		/*
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'left',
			labelWidth: 120,
			region: 'north',
			items: [
			],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});
		*/

		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandMin: 250,
			region: 'center',
			object: 'MedService',
			//border: false,
			dataUrl: C_REG_GETMSBYUSLUGA,
			toolbar: true,
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true},
				{name: 'MedService_id', type: 'int', hidden: true, isparams: true},
				{name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuUnitType_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuUnitType_SysNick', type: 'string', hidden: true, isparams: true},
				{name: 'LpuUnit_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuSection_id', type: 'int', hidden: true, isparams: true},
				{name: 'LpuSectionProfile_id', type: 'int', hidden: true, isparams: true},
				{name: 'MedServiceType_id', type: 'int', hidden: true, isparams: true},
				{name: 'MedServiceType_SysNick', type: 'string', hidden: true, isparams: true},
				{name: 'MedService_Nick', type: 'string', width: 70, header: lang['slujba']},
				{name: 'UslugaComplex_Name', type: 'string', id: 'autoexpand', header: lang['usluga']},
				{name: 'EvnQueue_Names', type: 'string', width: 80, header: lang['ochered']},
				{name: 'FreeTime', type: 'string', width: 180, header: lang['pervoe_svobodnoe_vremya']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() { this.onOkButtonClick(); }.createDelegate(this), text: lang['vyibrat'], tooltip: lang['vyibor_zapisi']},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function()
			{
			},
			onRowSelect: function (sm,index,record)
			{
			}
		});

		Ext.apply(this, {
			buttons: [/*{
				handler: function() {
					this.ownerCt.doSearch()
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, */{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [ 
				//this.filterPanel,
				this.viewFrame
			]
		});
		sw.Promed.swUslugaMedServiceRecordWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swUslugaMedServiceRecordWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.params = {
			Person_id: arguments[0].Person_id,
			PersonEvn_id: arguments[0].PersonEvn_id,
			Server_id: arguments[0].Server_id,						
			Person_Firname: arguments[0].Person_Firname,						
			Person_Surname: arguments[0].Person_Surname,						
			Person_Secname: arguments[0].Person_Secname,						
			Person_Birthday: arguments[0].Person_Birthday,						
			EvnPrescr_id: arguments[0].EvnPrescr_id || null,						
			PrescriptionType_Code: arguments[0].PrescriptionType_Code || null,						
			uslugaList: (Ext.isArray(arguments[0].uslugaList)) ? arguments[0].uslugaList : null,						
			EvnDirection_rid: arguments[0].EvnDirection_rid || null,						
			EvnDirection_pid: arguments[0].EvnDirection_pid || null,						
			parentEvnClass_SysNick: arguments[0].parentEvnClass_SysNick || null,						
			Diag_id: arguments[0].Diag_id || null,						
			TimetableMedService_id: arguments[0].TimetableMedService_id	|| null					
		};
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.callback = arguments[0].callback ||  Ext.emptyFn;
		this.setMyTitle();
		this.doReset();
		if (sw.Promed.MedStaffFactByUser.last)
		{
			this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		}
		else
		{
			sw.Promed.MedStaffFactByUser.selectARM({
				selectFirst: true,
				ARMType: this.ARMType,
				onSelect: function(data) {
					this.userMedStaffFact = data;
				}.createDelegate(this)
			});
		}
		this.doSearch();
		/*
		this.buttons[2].hide();
		
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid();

		this.syncSize();
		this.doLayout();
		*/

	}
});