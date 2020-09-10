/**
 * swMPWorkPlacePriemWindow - окно рабочего места врача приемного отделения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Марков Андрей (по интерфейсу Александра Арефьева)
 * @prefix       mpwppr
 * @version      июнь 2010 
 */
/*NO PARSE JSON*/

sw.Promed.swMPWorkPlacePriemWindow = Ext.extend(sw.Promed.BaseForm,
{
	//useUecReader: true,
	codeRefresh: true,
	objectName: 'swMPWorkPlacePriemWindow',
	objectSrc: '/jscore/Forms/Common/swMPWorkPlacePriemWindow.js',
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_WPMP,
	iconCls: 'workplace-mp16',
	id: 'swMPWorkPlacePriemWindow',
	TimetableStacList: [],
	listeners:
	{
		activate: function(){
			sw.Applets.uec.startUecReader({callback: this.getDataFromUec.createDelegate(this)});
			sw.Applets.bdz.startBdzReader({callback: this.getDataFromBdz.createDelegate(this)});
			sw.Applets.BarcodeScaner.startBarcodeScaner({callback: this.getDataFromBarScan.createDelegate(this), ARMType: 'priem'});
		},
		deactivate: function() {
			sw.Applets.uec.stopUecReader();
			sw.Applets.bdz.stopBdzReader();
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
		},
		hide: function() {
			if(this.getGrid().auto_refresh)
			{
				clearInterval(this.getGrid().auto_refresh);
			}
		}
	},
	getDataFromBarScan: function(person_data) {
		var _this = this,
			form = Ext.getCmp('swMPWorkPlacePriemWindow');

		if (!Ext.isEmpty(person_data.Person_Surname)){
			form.findById('mpwpprSearch_SurName').setValue(person_data.Person_Surname);
		} else {
			form.findById('mpwpprSearch_SurName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Firname)){
			form.findById('mpwpprSearch_FirName').setValue(person_data.Person_Firname);
		} else {
			form.findById('mpwpprSearch_FirName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Secname)){
			form.findById('mpwpprSearch_SecName').setValue(person_data.Person_Secname);
		} else {
			form.findById('mpwpprSearch_SecName').setValue(null);
		}

		if (!Ext.isEmpty(person_data.Person_Birthday)){
			form.findById('mpwpprSearch_BirthDay').setValue(person_data.Person_Birthday);
		} else {
			form.findById('mpwpprSearch_BirthDay').setValue(null);
		}

		var callback = function() {
			var form = Ext.getCmp('swMPWorkPlacePriemWindow'),
			grid = form.mainGrid;

			if (grid.getStore().getCount() == 1) {
				grid.getSelectionModel().selectLastRow();
				_this.openEmk();
			} else if (grid.getStore().getCount() == 0) {
				_this.selfTreatment(person_data);
			}
		};

		form.doSearch({onLoad: callback});

	},
	
	//тип АРМа,
	ARMType: 'priem',
	//объект с параметрами рабочего места, с которыми была открыта форма
	userMedStaffFact: null,
	readOnly: false,
	setMenu: function(first) {
		/*var menu = this.createListStacSection(this.userMedStaffFact.LpuSection_id);
		this.ViewContextMenu.items.item(7).menu = menu;
		this.gridToolbar.items.item(5).menu = menu;*/
		if(first)
			this.createListPrehospWaifRefuseCause();
	},
	payTypeStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'PayType_id', type: 'int', mapping: 'PayType_id' },
			{ name: 'PayType_Code', type: 'int', mapping: 'PayType_Code' },
			{ name: 'PayType_Name', type: 'string', mapping: 'PayType_Name' },
			{ name: 'PayType_SysNick', type: 'string', mapping: 'PayType_SysNick' }
		],
		key: 'PayType_id',
		params: { object: 'PayType', order_by_field: 'PayType_Code' },
		sortInfo: {
			field: 'PayType_Code'
		},
		tableName: 'PayType'
	}),
	show: function()
	{
		sw.Promed.swMPWorkPlacePriemWindow.superclass.show.apply(this, arguments);
		
		if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.', function() { this.hide(); }.createDelegate(this));
			return false;
		}

		//this.ARMType = arguments[0].userMedStaffFact.ARMType;
		this.userMedStaffFact = arguments[0].userMedStaffFact;

		// Создание меню списка причин отказа
		this.setMenu(true);
		
		// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера ( из User_model)
		sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
		//this.setTitle(WND_WPMP+' ('+ this.userMedStaffFact.LpuSection_Name +'/'+this.userMedStaffFact.MedPersonal_FIO+')');

		// Загружаем справочник видов оплат
		// https://redmine.swan.perm.ru/issues/18631
		if ( this.payTypeStore.getCount() == 0 ) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Загрузка справочника видов оплаты..." });
			loadMask.show();

			this.payTypeStore.load({
				callback: function() {
					loadMask.hide();
				}.createDelegate(this)
			});
		}
		this.LpuSectionServiceCount = 0;
		Ext.Ajax.request({
			url: '/?c=LpuStructure&m=getLpuSectionServiceCount',
			params: {LpuSection_id: this.userMedStaffFact.LpuSection_id},
			callback: function(options, success, response) {
				if (success) {
					var responseObj = Ext.util.JSON.decode(response.responseText);
					if (responseObj.Count && responseObj.Count > 0) {
						this.findById('mpwpprSearch_isAll').show();
					} else {
						this.findById('mpwpprSearch_isAll').hide();
					}
					this.LpuSectionServiceCount = responseObj.Count;
					this.getCurrentDateTime();
					this.resetFilter();
					this.syncSize();
				}
			}.createDelegate(this)
		});
	}, 
	
	
	getGrid: function () 
	{
		return this.mainGrid;
	},	
	
	resetFilter: function () 
	{
		this.findById('mpwpprSearch_SurName').setValue(null);
		this.findById('mpwpprSearch_SecName').setValue(null);
		this.findById('mpwpprSearch_FirName').setValue(null);
		this.findById('mpwpprSearch_BirthDay').setValue(null);
		this.findById('mpwpprSearch_PSNumCard').setValue(null);
		this.findById('mpwpprSearch_EvnQueueShow_id').setValue(0);
		this.findById('mpwpprSearch_EvnDirectionShow_id').setValue(0);
		this.findById('mpwpprSearch_PrehospStatus_id').setValue(0);
		this.findById('mpwpprSearch_isAll').setValue(false);
		this.findById('mpwpprEvnDirection_isConfirmed').setValue(null);
	},
	doSearch: function(option)
	{
		var params = new Object();

		if ( this.findById('mpwpprSearch_FirName').getValue().length > 0 )
		{
			params.Person_FirName = this.findById('mpwpprSearch_FirName').getValue();
		}
		if ( this.findById('mpwpprSearch_SecName').getValue().length > 0 )
		{
			params.Person_SecName = this.findById('mpwpprSearch_SecName').getValue();
		}
		if ( this.findById('mpwpprSearch_PSNumCard').getValue().length > 0 )
		{
			params.PSNumCard = this.findById('mpwpprSearch_PSNumCard').getValue();
		}
		if ( this.findById('mpwpprSearch_SurName').getValue().length > 0 )
		{
			params.Person_SurName = this.findById('mpwpprSearch_SurName').getValue();
		}
		if (Ext.util.Format.date(this.findById('mpwpprSearch_BirthDay').getValue(), 'd.m.Y').length>0)
		{
			params.Person_BirthDay = Ext.util.Format.date(this.findById('mpwpprSearch_BirthDay').getValue(), 'd.m.Y');
		}
        if (Ext.util.Format.date(this.dateMenu.getValue(), 'd.m.Y').length > 0)
        {
            params.date = Ext.util.Format.date(this.dateMenu.getValue(), 'd.m.Y');
        }
        else
        {
            params.date = null;
        }
        params.EvnQueueShow_id = this.findById('mpwpprSearch_EvnQueueShow_id').getValue();
		params.EvnDirectionShow_id = this.findById('mpwpprSearch_EvnDirectionShow_id').getValue();
		params.PrehospStatus_id = this.findById('mpwpprSearch_PrehospStatus_id').getValue();
		params.EvnDirection_isConfirmed = this.findById('mpwpprEvnDirection_isConfirmed').getValue();
		// Для фильтрации списка госпитализированных по отделению передаем идентификатор отделения на сервер
		params.LpuSection_id = (this.userMedStaffFact && this.userMedStaffFact.LpuSection_id)?this.userMedStaffFact.LpuSection_id:null;
		if (this.LpuSectionServiceCount > 0) {
			params.isAll = this.findById('mpwpprSearch_isAll').getValue()?1:0;
		}
		
		var callback = (option && option.onLoad) || null;
		this.getGrid().loadStore(params, callback);
	},
	listRefresh:function(option)
	{
		if(typeof option != 'object')
		{
			option = {};
		}
		if(typeof option.onLoad != 'function')
		{
			option.onLoad =  function(record_list,options,success){
				this.restorePosition();
			}.createDelegate(this);
		}
		if ( !option.disableSavePosition )
		{
			this.savePosition();
		}
		this.doSearch(option);
	},
	//Печать шаблонов документов
	printDocTemplate: function() {

		var grid = this.getGrid(),
			rec = grid.getSelectionModel().getSelected();

		if (!rec) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}

		var params = {
			Evn_id: rec.get('EvnDirection_id'),
			Person_id: rec.get('Person_id'),
			EvnClass_id: 30, // по умолчанию категория "Карта выбывшего из стационара"
			enableOtherEvnClasses: true, // показывать остальные категории
			excludedEvnClasses: [11,13,29] // исключая категории
		};

		getWnd('swPrintTemplateSelectWindow').show(params);
	},
	//Печать списка
	printRecs: function(action)
	{
        var record = this.getGrid().getSelectionModel().getSelected();
        if (action && action == 'row' && !record) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
        if (action && action == 'row') {
			Ext.ux.GridPrinter.print(this.getGrid(),{rowId: record.id});
        } else {
			Ext.ux.GridPrinter.print(this.getGrid());
        }
        return true;
	},

	savePosition: function()
	{
		var record = this.getGrid().getSelectionModel().getSelected();
		if (record)
		{
			this.position = record.id;
		}
		else 
		{
			this.position = '';
		}
	},
	restorePosition: function()
	{
		if ( !Ext.isEmpty(this.position) )
		{
			var grid = this.getGrid(),
				value = this.position;
			grid.getStore().each(function(record)
			{
				if (record.id == value)
				{
					var index = grid.getStore().indexOf(record);
					grid.getView().focusRow(index);
					grid.getSelectionModel().selectRow(index);
					return record;
				}
			});
		}
		else 
		{
			this.getGrid().focus();
		}
		this.position = '';
	},
	
	getSelectedRecord: function(key_list)
	{
		var grid = this.getGrid();
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['spisok_zapisey_ne_nayden']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		if (key_list)
		{
			for(var i = 0;i < key_list.length;i++)
			{
				if(!record.get(key_list[i]))
				{
					Ext.Msg.alert(lang['oshibka'], lang['v_zapisi_otsutstvuet_odin_iz_neobhodimyih_parametrov']);
					return false;
				}
			}
		}
		return record;
	},
	openForm: function(wnd,oparams,title)
	{
		if (title && getWnd(wnd).isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno']+ title +lang['uje_otkryito']);
			return false;
		}
		var params = {
			onHide: Ext.emptyFn, //function() { this.listRefresh(); }.createDelegate(this)
			UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			UserLpuSection_id: this.userMedStaffFact.LpuSection_id,
			userMedStaffFact: this.userMedStaffFact,
			from:'workplacepriem',
			ARMType:this.ARMType
		};
		params = Ext.apply(params || {}, oparams || {});
		getWnd(wnd).show(params);
	},
	openPersonSearchWindow: function(option)
	{
		
		if (getRegionNick() == 'kz') {
			var onSelect = option.onSelect;
			option.onSelect = function(pdata) {
				var conf = {
					Person_id: pdata.Person_id,
					PersonEvn_id: pdata.PersonEvn_id,
					Server_id: pdata.Server_id,
					win: this,
					callback: function() {
						onSelect(pdata);
					}
				};
				sw.Promed.PersonPrivilege.checkExists(conf);
			}.createDelegate(this)
		}
		
		var params = {
			onClose: function() 
			{
				if(typeof option.onClose == 'function')
				{
					option.onClose();
				}
			},
			onSelect: function(pdata) 
			{
				if (pdata.Person_IsDead != 'true') {
					option.onSelect(pdata);
					getWnd('swPersonSearchWindow').hide();
				} else if (pdata.Person_IsDead == 'true') {
					sw.swMsg.alert(lang['oshibka'], (option.deadMsg || lang['gospitalizatsiya_nevozmojna_v_svyazi_so_smertyu_patsienta']));
				}
			}.createDelegate(this),
			searchMode: option.searchMode || 'all'
		};
		
		
		if (!Ext.isEmpty(option.person_data)){
			if (!Ext.isEmpty(option.person_data.Person_Firname)){
				params.personFirname = option.person_data.Person_Firname;
			}
			if (!Ext.isEmpty(option.person_data.Person_Secname)){
				params.personSecname = option.person_data.Person_Secname;
			}
			if (!Ext.isEmpty(option.person_data.Person_Surname)){
				params.personSurname = option.person_data.Person_Surname;
			}
			if (!Ext.isEmpty(option.person_data.Person_Birthday)){
				params.PersonBirthDay_BirthDay = option.person_data.Person_Birthday;
			}
			if (!Ext.isEmpty(option.person_data.Polis_Num)){
				params.Polis_EdNum = option.person_data.Polis_Num;
			}
		}

		if (getRegionNick() == 'ufa') {
			params.allowUnknownPerson = true;
		}
		
		this.openForm(
			'swPersonSearchWindow',
			params
			,
			lang['poisk_cheloveka']
		);
	},
	createEvnPS: function(pdata)
	{
		var win = this,
			index = -1,
			params = {
				action: 'add',
				Person_id: pdata.Person_id,
				PersonEvn_id: pdata.PersonEvn_id,
				Server_id: pdata.Server_id,
				EvnPS_id: null,
				LpuSection_pid: win.userMedStaffFact.LpuSection_id,
				MedStaffFact_pid: win.userMedStaffFact.MedStaffFact_id,
				EvnPS_setDate: Ext.util.Format.date(win.dateMenu.getValue(), 'd.m.Y'),
				callback: function(data) {
					win.position = 'EvnPS_'+data.evnPSData.EvnPS_id;
					win.listRefresh({disableSavePosition: true});
				}
			};

		index = win.payTypeStore.findBy(function(rec) {
			return (rec.get('PayType_SysNick') == getPayTypeSysNickOms());
		});
		if ( index >= 0 ) {
			params.PayType_id = this.payTypeStore.getAt(index).get('PayType_id');
		}

		if (pdata.EvnDirectionData && pdata.EvnDirectionData.EvnDirection_id) {
			params.EvnDirection_id = pdata.EvnDirectionData.EvnDirection_id;
			params.EvnDirection_IsAuto = pdata.EvnDirectionData.EvnDirection_IsAuto;
			params.EvnDirection_IsReceive = pdata.EvnDirectionData.EvnDirection_IsReceive;
			params.EvnDirection_setDate = pdata.EvnDirectionData.EvnDirection_setDate;
			params.EvnDirection_Num = pdata.EvnDirectionData.EvnDirection_Num;
			params.LpuSection_did = pdata.EvnDirectionData.LpuSection_id;
			params.Diag_did = pdata.EvnDirectionData.Diag_did; //диагноз направившего учреждения  по направлению
			params.Org_did = pdata.EvnDirectionData.Org_did;
			params.Lpu_sid = pdata.EvnDirectionData.Lpu_sid;
			params.TimetableStac_id = pdata.EvnDirectionData.TimetableStac_id || null;
			params.DirType_id = pdata.EvnDirectionData.DirType_id;
			params.EvnQueue_id = pdata.EvnDirectionData.EvnQueue_id || null;
			params.EvnPS_CodeConv = pdata.EvnDirectionData.EmergencyData_CallNum || null; //номер карты вызова
			params.EvnPS_NumConv = pdata.EvnDirectionData.EmergencyData_BrigadeNum || null; //номер наряда  (бригады)
			params.EvnPS_IsWithoutDirection = 2;
			params.PrehospDirect_id = sw.Promed.EvnDirectionAllPanel.calcPrehospDirectId(params.Lpu_sid, params.Org_did, params.LpuSection_did, params.EvnDirection_IsAuto);
			params.PrehospType_id = (params.DirType_id == 5)?1:2;//- тип госпитализации: «2. Экстренно», если КВС добавляется из АРМа по направлению с целью «На госпитализацию экстренную», иначе «1.Планово»;
			params.PrehospArrive_id = 1;//кем доставлен: 1. самостоятельно
			params.disableFields = ['EvnPS_IsWithoutDirection'];
			if (params.EvnPS_CodeConv) {
				params.disableFields = ['PrehospDirect_id','PrehospArrive_id','EvnPS_IsWithoutDirection'];
				params.PrehospDirect_id = 5;//кем направлен = 5. Скорая помощь, если выбирается запись с экстренной биркой
				params.PrehospArrive_id = 2;//кем доставлен: 2. Скорая помощь, если выбирается запись с экстренной биркой;
				params.PrehospType_id = 1;//- тип госпитализации: «2. Экстренно», если выбирается запись с экстренной биркой
				//params.EvnPS_IsWithoutDirection = 1;
			}
			if (getRegionNick() == 'kz' && !!pdata.EvnDirectionData.PayType_id) {
				params.PayType_id = pdata.EvnDirectionData.PayType_id;
			}
		} else {
			params.EvnPS_IsWithoutDirection = 1;
			params.PrehospType_id = 2;//- тип госпитализации: «1.Планово»;
			params.PrehospArrive_id = 1;//кем доставлен: 1. самостоятельно
		}
		
		win.checkReceptionTime({
			params: params,
			callback: function(){
				win.openForm('swEvnPSPriemEditWindow',params,lang['postuplenie_patsienta_v_priemnoe_otdelenie']);
			}
		});
	},
	openEvnPSPriemEditWindow: function(option) {
		var index;
		var params = {
			action: option.action
		};

		var rec = this.getSelectedRecord();
		if(getRegionNick() == 'ufa' && rec) {
			params.ScaleLams_id = rec.get('ScaleLams_id');
			params.ScaleLams_Value = rec.get('ScaleLams_Value');
			params.FaceAsymetry_Name = rec.get('FaceAsymetry_Name');
			params.HandHold_Name = rec.get('HandHold_Name');
			params.SqueezingBrush_Name = rec.get('SqueezingBrush_Name');
			params.PainResponse_Name = rec.get('PainResponse_Name');
			params.ExternalRespirationType_Name = rec.get('ExternalRespirationType_Name');
			params.SystolicBloodPressure_Name = rec.get('SystolicBloodPressure_Name');
			params.InternalBleedingSigns_Name = rec.get('InternalBleedingSigns_Name');
			params.LimbsSeparation_Name = rec.get('LimbsSeparation_Name');
			params.PrehospTraumaScale_Value = rec.get('PrehospTraumaScale_Value');
			params.CmpCallCard_id = rec.get('CmpCallCard_id');
			params.ResultECG = rec.get('ResultECG');
			params.ECGDT = rec.get('ECGDT');
			params.PainDT = rec.get('PainDT');
			params.TLTDT = rec.get('TLTDT');
			params.FailTLT = rec.get('FailTLT');
		}

		if(option.activatePanel) params.activatePanel = option.activatePanel;
		if(option.EvnPS_OutcomeDate) params.EvnPS_OutcomeDate = option.EvnPS_OutcomeDate;
		if(option.EvnPS_OutcomeTime) params.EvnPS_OutcomeTime = option.EvnPS_OutcomeTime;

		switch(option.from)
		{
			case 'reception':
				var r = this.getSelectedRecord(),
					n = r && r.id.split('_');
				if (r == false)
				{
					return false;
				}
				if (option.person)
				{
					params.Person_id = option.person.Person_id;
					params.PersonEvn_id = option.person.PersonEvn_id;
					params.Server_id = option.person.Server_id;
				}
				else
				{
					params.Person_id = r.get('Person_id');
					params.PersonEvn_id = r.get('PersonEvn_id');
					params.Server_id = r.get('Server_id');
				}
			break;
			
			case 'selfTreatment':
				var r = false;
				params.Person_id = option.person.Person_id;
				params.PersonEvn_id = option.person.PersonEvn_id;
				params.Server_id = option.person.Server_id;
				params.EvnPS_id = option.EvnPS_id || null;
				var n = ['EvnPS'];
				if(option.action == 'add' && option.result)
				{
					if(option.result.name == 'EvnDirection')
					{
						//добавляем как по направлению
						n[0] = 'EvnDirection';
						params.EvnDirection_id = option.result.EvnDirection_id;
						params.EvnDirection_setDate = option.result.EvnDirection_setDate;
						params.EvnDirection_Num = option.result.num;
						params.LpuSection_did = option.result.LpuSection_did;
						params.Diag_did = option.result.Diag_did;
						params.DirType_id = option.result.DirType_id;
						params.Org_did = option.result.Org_did;
						params.TimetableStac_id = option.result.TimetableStac_id;
					}
					if(option.result.name == 'EvnQueue')
					{
						//добавляем как из очереди
						n[0] = 'EvnQueue';
						n[1] = option.result.EvnQueue_id;
					}
					if(option.result.name == 'TimetableStac')
					{
						//добавляем как по бирке
						n[0] = 'TimetableStac';
						params.EvnPS_CodeConv = option.result.EvnPS_CodeConv; //номер карты вызова
						params.Diag_did = option.result.Diag_did; //диагноз направившего учреждения по бирке;
						params.EvnPS_NumConv = option.result.EvnPS_NumConv; //номер наряда  (бригады)
						params.TimetableStac_id = option.result.TimetableStac_id;
					}
				}
			break;
			
			default:
				var r = this.getSelectedRecord(),
					n = r && r.id.split('_');
				if (r == false)
				{
					return false;
				}
				params.Person_id = r.get('Person_id');
				params.PersonEvn_id = r.get('PersonEvn_id');
				params.Server_id = r.get('Server_id');
				params.EvnPS_id = r.get('EvnPS_id') || null;
		}

		//if(!params.Person_id || !params.PersonEvn_id || !params.Server_id)
		//{
		//	Ext.Msg.alert('Сообщение', 'Человек не выбран!');
		//	return false;
		//}
		
		if(r)
		{
			params.EvnDirection_id = r.get('EvnDirection_id') || null;
			params.EvnDirection_setDate = r.get('EvnDirection_setDate') || null;
			params.EvnDirection_Num = r.get('EvnDirection_Num') || null;
			params.LpuSection_did = r.get('LpuSection_did') || null;
			params.Diag_did = r.get('Diag_did') || null;
			params.DirType_id = r.get('DirType_id') || null;
			params.Org_did = r.get('Org_did') || null;
			params.TimetableStac_id = r.get('TimetableStac_id') || null;
			params.EvnPS_CodeConv = r.get('EvnPS_CodeConv');
			params.EvnPS_NumConv = r.get('EvnPS_NumConv');
		}
		if(option.action == 'add')
		{
			var PayType_SysNick = 'oms';
			switch ( getRegionNick() ) {
				case 'by': PayType_SysNick = 'besus'; break;
				case 'kz': PayType_SysNick = 'Resp'; break;
			}
			index = this.payTypeStore.findBy(function(rec) {
				return (rec.get('PayType_SysNick') == PayType_SysNick);
			});

			if ( index >= 0 ) {
				params.PayType_id = this.payTypeStore.getAt(index).get('PayType_id');
			}

			if (getRegionNick() == 'kz' && r && r.get('PayType_id')) {
				params.PayType_id = r.get('PayType_id');
			}

			params.LpuSection_pid = this.userMedStaffFact.LpuSection_id;
			params.MedStaffFact_pid = this.userMedStaffFact.MedStaffFact_id;
			params.EvnPS_setDate = Ext.util.Format.date(this.dateMenu.getValue(), 'd.m.Y');
		}
		else
		{
			if(!params.EvnPS_id)
			{
				Ext.Msg.alert(lang['soobschenie'], lang['patsient_ne_prinyat']);
				return false;
			}
		}
		
		params.callback = function(data) { this.listRefresh(); }.createDelegate(this);
		switch(n[0])
		{
			// из очереди
			case 'EvnQueue':
				params.disableFields = ['EvnPS_IsWithoutDirection'];
				if(option.action == 'add')
				{
					this.saveEvnDirectionAuto({
						EvnQueue_id: n[1],
						callback: function(data) {
							if(data.EvnDirection_id){
								params.PrehospArrive_id = 1;//кем доставлен: 1. самостоятельно, если КВС добавляется из АРМа по записи в АРМ с направлением; 
								params.EvnDirection_id = data.EvnDirection_id;
								params.EvnPS_IsWithoutDirection = 2;
								params.EvnDirection_Num = data.EvnDirection_Num;
								params.EvnDirection_setDate = data.EvnDirection_setDate;
								params.Diag_did =data.Diag_id; //диагноз направившего учреждения  по направлению;
								params.PrehospType_id = (data.DirType_id == 5)?1:2;//- тип госпитализации: «2. Экстренно», если КВС добавляется из АРМа по направлению с целью «На госпитализацию экстренную», иначе «1.Планово»;
							} else {
								params.EvnPS_IsWithoutDirection = 1;
								params.PrehospType_id = 2;//- тип госпитализации: «1.Планово»;
							}
							if (getRegionNick() == 'kz') {
								params.PrehospDirect_id = (data.Lpu_did != getGlobalOptions().lpu_id)?16:15;
							} else {
								params.PrehospDirect_id = (data.Lpu_did != getGlobalOptions().lpu_id)?2:1;
							}
							params.EvnQueue_id = data.EvnQueue_id;
							params.LpuSection_did = data.LpuSection_did;
							params.Org_did = data.Org_did;
							params.TimetableStac_id = data.TimetableStac_id;
							this.openForm('swEvnPSPriemEditWindow',params,lang['postuplenie_patsienta_v_priemnoe_otdelenie']);
						}.createDelegate(this)
					});
					return false;
				}
			break;
			
			// по направлению м.б. принят или нет (добавление, редактирование, просмотр)
			case 'EvnDirection':
				params.disableFields = ['EvnPS_IsWithoutDirection'];
				if(option.action == 'add')
				{
					params.PrehospArrive_id = 1;//кем доставлен: 1. самостоятельно, если КВС добавляется из АРМа по записи в АРМ с направлением; 
					params.PrehospType_id = (params.DirType_id == 5)?1:2;//- тип госпитализации: «2. Экстренно», если КВС добавляется из АРМа по направлению с целью «На госпитализацию экстренную», иначе «1.Планово»;
					if (getRegionNick() == 'kz') {
						params.PrehospDirect_id = (params.Org_did != getGlobalOptions().org_id)?16:15;
					} else {
						params.PrehospDirect_id = (params.Org_did != getGlobalOptions().org_id)?2:1;
					}
					params.EvnPS_IsWithoutDirection = 2;
				}
				else
				{
				}
			break;
			
			// самостоятельно без направления (редактирование, просмотр)
			case 'EvnPS':
				params.disableFields = ['EvnPS_IsWithoutDirection'];
				if(option.action == 'add')
				{
					params.PrehospArrive_id = 1;//кем доставлен: 1. самостоятельно, если КВС добавляется из АРМа по кн. «самостоятельное обращение» 
					params.PrehospType_id = 1;//- тип госпитализации: «2. Экстренно», если КВС добавляется из АРМа по кн. «самостоятельное обращение»
					params.EvnPS_IsWithoutDirection = 1;
					params.callback = function(data) {
						this.position = 'EvnPS_'+data.evnPSData.EvnPS_id;
						this.listRefresh({disableSavePosition: true});
					}.createDelegate(this);
				}
				else
				{
				}
			break;
			
			// по бирке м.б. принят или нет (добавление, редактирование, просмотр)
			case 'TimetableStac':
				if(option.action == 'add')
				{
					if(params.EvnPS_CodeConv) {
						params.disableFields = ['PrehospDirect_id','PrehospArrive_id','EvnPS_IsWithoutDirection'];
						params.PrehospDirect_id = 5;//кем направлен = 5. Скорая помощь, если выбирается запись с экстренной биркой
						params.PrehospArrive_id = 2;//кем доставлен: 2. Скорая помощь, если выбирается запись с экстренной биркой;
						params.PrehospType_id = 1;//- тип госпитализации: «2. Экстренно», если выбирается запись с экстренной биркой
						params.EvnPS_IsWithoutDirection = 1;
					} else {
						params.disableFields = ['EvnPS_IsWithoutDirection'];
						//params.PrehospDirect_id = 5;//кем направлен = 5. Скорая помощь, если выбирается запись с экстренной биркой
						//params.PrehospArrive_id = 2;//кем доставлен: 2. Скорая помощь, если выбирается запись с экстренной биркой;
						params.PrehospType_id = 2;
						params.EvnPS_IsWithoutDirection = 1;
					}
				}
				else
				{
				}
			break;
			default:
				Ext.Msg.alert(lang['oshibka'], lang['nepravilnyiy_identifikator_zapisi']);
				return false;
		}
		//params.onHide = function(data) { this.mainGrid.focus(); }.createDelegate(this);
		if(option.action == 'add'){
			this.checkReceptionTime({
				params: params,
				callback: function(){
					this.openForm('swEvnPSPriemEditWindow',params,lang['postuplenie_patsienta_v_priemnoe_otdelenie']);
				}.createDelegate(this)
			});
		} else {
			this.openForm('swEvnPSPriemEditWindow',params,lang['postuplenie_patsienta_v_priemnoe_otdelenie']);
		}
	},
	/** Проверка наличия поступления пациента в приемное отделение за последние 24 часа по всем ЛПУ
	 */
	checkReceptionTime: function(option) {
		Ext.Ajax.request({
			url: '/?c=EvnPS&m=checkReceptionTime',
			params: {
				Person_id: option.params.Person_id
				,EvnPS_setDate: Ext.util.Format.date(this.dateMenu.getValue(), 'd.m.Y')
			},
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['pri_proverke_nalichiya_postupleniya_patsienta_v_priemnoe_otdelenie_za_poslednie_24_chasa_proizoshla_oshibka']);
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						option.callback();
					} else if (answer.Alert_Msg) {
						sw.swMsg.show(
						{
							icon: Ext.MessageBox.QUESTION,
							msg: answer.Alert_Msg +lang['dobavit_novoe_postuplenie_patsienta'],
							title: lang['vopros'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								if ('yes' == buttonId)
								{
									option.callback();
								}
							}
						});
					}
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_proverke_nalichiya_postupleniya_patsienta_v_priemnoe_otdelenie_za_poslednie_24_chasa_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				}
			}
		});
	},
	/** Запрос при приёме из очереди
	 */
	saveEvnDirectionAuto: function(option) {
		var win = this;
		var params = {EvnQueue_id: option.EvnQueue_id};
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=saveEvnDirectionAuto',
			params: params,
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['pri_prieme_iz_ocheredi_proizoshla_oshibka']);
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						option.callback(answer);
					}
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_prieme_iz_ocheredi_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				}
			}
		});
	},
	cancelEvnDirection: function() {
		var win = this;
		var rec = this.getSelectedRecord();
		var grid = this.getGrid();
		if (Ext.isEmpty(rec.get('EvnPS_id'))) {
			sw.Promed.Direction.cancel({
				cancelType: 'decline',
				ownerWindow: win,
				formType: 'workplacepriem',
				userMedStaffFact: win.userMedStaffFact,
				EvnDirection_id: rec.get('EvnDirection_id'),
				TimetableStac_id: rec.get('TimetableStac_id'),
				EvnQueue_id: rec.get('EvnQueue_id'),
				defaultQueueFailCause_Code: 12,
				enabledQueueFailCauseCodeList: [4,12],
				/*
				personData: {
					Person_id: rec.get('Person_id'),
					Server_id: rec.get('Server_id'),
					PersonEvn_id: rec.get('PersonEvn_id'),
					Person_IsDead: rec.get('Person_IsDead'),
					Person_Firname: rec.get('Person_Firname'),
					Person_Secname: rec.get('Person_Secname'),
					Person_Surname: rec.get('Person_Surname'),
					Person_Birthday: rec.get('Person_Birthday')
				},
				*/
				callback: function (cfg) {
					grid.getStore().reload();
				}
			});
		}
	},
	// GridActions
	openEmk: function()
	{
		var r = this.getSelectedRecord();
		if (r == false)
		{
			return false;
		}
		var win = this;
		var n = r.id.split('_');
		var open = function(pdata) {
			var params = {
				Person_id: pdata.Person_id,
				Server_id: pdata.Server_id,
				PersonEvn_id: pdata.PersonEvn_id,
				ARMType: 'common',//this.ARMType,
				addStacActions: ['action_New_EvnPS', 'action_StacSvid'],
				callback: function()
				{
					this.listRefresh();
				}.createDelegate(this)
			};
			if(r.get('EvnPS_id')) {
				params.searchNodeObj = {
					parentNodeId: 'root',
					last_child: false,
					disableLoadViewForm: false,
					EvnClass_SysNick: 'EvnPS',
					Evn_id: r.get('EvnPS_id')
				};
			}

			Ext.Ajax.request({
				url: '/?c=EvnPS&m=beforeOpenEmk',
				params: {Person_id: pdata.Person_id},
				failure: function(response, options) {
					showSysMsg(langs('При получении данных для проверок произошла ошибка!'));
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if(!Ext.isArray(answer) || !answer[0])
						{
							showSysMsg(langs('При получении данных для проверок произошла ошибка! Неправильный ответ сервера.'));
							return false;
						}
						if (answer[0].countOpenEvnPS > 0)
						{
							//showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
							params.addStacActions = ['action_StacSvid']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
						}

						checkPersonPhoneVerification({
							Person_id: params.Person_id,
							MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
							callback: function(){win.openForm('swPersonEmkWindow',params,false)}
						});
					}
					else {
						showSysMsg(langs('При получении данных для проверок произошла ошибка! Отсутствует ответ сервера.'));
					}
				}
			});
		}.createDelegate(this);
			
		if(n[0] == 'TimetableStac' && !r.get('EvnPS_id') && (!r.get('Person_id') || !r.get('PersonEvn_id') || !r.get('Server_id')))
		{
			
			//Ext.Msg.alert(langs('Уведомление'), langs('Человек не идентифицирован, нельзя открыть ЭМК! Для идентификации пациента заведите поступление!'));
			//return;
			// бирка с неидентифицированным человеком
			this.openForm(
				'swPersonSearchWindow',
				{
					onClose: function(){
						open({
							Person_id: r.get('Person_id'),
							Server_id: r.get('Server_id'),
							PersonEvn_id: r.get('PersonEvn_id')
						});
					},
					onSelect: function(pdata) 
					{
						// устанавливаем связь с выбранным человеком 
						win.setPersonInTimetableStac(pdata);
						open(pdata);
						getWnd('swPersonSearchWindow').hide();
						
					}.createDelegate(this),
					searchMode: 'all'
				},
				lang['poisk_cheloveka']
			);
			return true;
		}
		
		open({
			Person_id: r.get('Person_id'),
			Server_id: r.get('Server_id'),
			PersonEvn_id: r.get('PersonEvn_id')
		});
	},
	reception: function()
	{
		var win = this,
			r = this.getSelectedRecord(),
			n = r.id.split('_');

		if(n[0] == 'TimetableStac' && !r.get('EvnPS_id') && (!r.get('Person_id') || !r.get('PersonEvn_id') || !r.get('Server_id')))
		{
			// бирка с неидентифицированным человеком
			win.openPersonSearchWindow({
				//searchMode
				onClose: function(){
					win.openEvnPSPriemEditWindow({from: 'reception', action: 'add'});
				},
				onSelect: function(pdata) 
				{
					// устанавливаем связь с выбранным человеком 
					win.setPersonInTimetableStac(pdata);
					win.openEvnPSPriemEditWindow({from: 'reception', action: 'add', person: pdata});
				}.createDelegate(this),
				deadMsg: lang['priem_nevozmojen_v_svyazi_so_smertyu_patsienta']
			});
			return true;
		}
		win.openEvnPSPriemEditWindow({from: 'reception', action: 'add'});
	},
	openPrintDoc: function(url)
	{
		window.open(url, '_blank');
	},
	/** Печать справки об отказе в госпитализации
	 *
	 */
	printHospRefuse: function () {
		var r = this.getSelectedRecord();
		if (r)
		{
			printBirt({
				'Report_FileName': 'printEvnPSPrehospWaifRefuseCause.rptdesign',
				'Report_Params': '&paramEvnPsID=' + r.get('EvnPS_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	/** Печать справки об отказе больного
	 *
	 */
	printPatientRefuse: function () {
		var r = this.getSelectedRecord();
		if (r)
		{
			printBirt({
				'Report_FileName': 'printPatientRefuse.rptdesign',
				'Report_Params': '&paramEvnPsID=' + r.get('EvnPS_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	/** "В архив" запись с направлением с очередью
	 *
	 */
	sendToArchive: function () {
		var r = this.getSelectedRecord();
		if (r) {
			var n = r.id.split('_');
			var win = this;
			
			sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: lang['otpravit_v_arhiv_zapis_o_postanovke_v_ochered'],
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						Ext.Ajax.request({
							url: '/?c=Queue&m=sendToArchive',
							params: {EvnQueue_id: n[1]},
							failure: function(response, options) {
								Ext.Msg.alert(lang['oshibka'], lang['pri_otpravke_v_arhiv_proizoshla_oshibka']);
							},
							success: function(response, action)
							{
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (answer.success) {
										win.listRefresh();
									}
								}
								else {
									Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_otpravke_v_arhiv_otsutstvuet_otvet_servera']);
								}
							}
						});
					}
				}
			});
		}
	},
	urgentOper: function() {
		var win = this;
		var r = this.getSelectedRecord();
		if (r) {
			// экстренная операция, получаем движение => открываем форму назначений.
			win.getLoadMask('Получение последнего движения').show();
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=getEvnSectionLast',
				params: {EvnPS_id: r.get('EvnPS_id'), useCase: 'urgentOper'},
				failure: function(response, options) {
					win.getLoadMask().hide();
					Ext.Msg.alert(lang['oshibka'], 'Ошибка получения последнего движения');
				},
				success: function(response, action)
				{
					win.getLoadMask().hide();
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if(answer.length > 0)
						{
							var conf = {
								userMedStaffFact: win.userMedStaffFact,
								parentEvnClass_SysNick: 'EvnSection',
								action: 'add',
								PrescriptionType_id: 7,
								PrescriptionType_Code: 7,
								data: {
									Diag_id: r.get('Diag_id'),
									Person_Surname: answer[0].Person_Surname,
									Person_Firname: answer[0].Person_Firname,
									Person_Secname: answer[0].Person_Secname,
									Person_id: r.get('Person_id'),
									PersonEvn_id: r.get('PersonEvn_id'),
									Server_id: r.get('Server_id'),
									Evn_pid: answer[0].EvnSection_id,
									IsCito: true
								},
								callbackEditWindow: function () {
									//
								},
								onHideEditWindow: function () {
									//
								}
							};
							sw.Promed.EvnPrescr.openEditWindow(conf);
						}
						else
						{
							Ext.Msg.alert(lang['oshibka'], 'Отсутствуют движения в рамках данного случая');
						}
					}
					else {
						Ext.Msg.alert(lang['oshibka'], 'Отсутствует ответ сервера');
					}
				}
			});
		}
	},
	/** Отмена поступления в приемное
	 *
	 */
	cancelReception: function () {
		var r = this.getSelectedRecord();
		if (r) {
			var EvnPS_id = r.get('EvnPS_id');
			var win = this;
			
			sw.swMsg.show(
			{
				icon: Ext.MessageBox.QUESTION,
				msg: lang['udalit_postuplenie_patsienta_v_priemnoe_otdelenie'],
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						Ext.Ajax.request({
							url: '/?c=Evn&m=deleteFromArm',
							params: {Evn_id: EvnPS_id, MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id},
							failure: function(response, options) {
								Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
							},
							success: function(response, action)
							{
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success) {
										if (answer.Error_Code) {
											sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
										}
									}
									win.listRefresh();
								}
								else {
									Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
								}
							}
						});
					}
				}
			});
		}
	},
	/** Открывает окошко для выбора отделения. 
	 *
	 */
	setHospitalization: function() {
		var win = this;
		var r = this.getSelectedRecord();
		if (getRegionNick().inlist(['vologda','msk', 'ufa'])) {
			getWnd('swSelectLpuSectionWardExt6').show({
				LpuSection_uid: this.userMedStaffFact.LpuSection_id,
				Person_BirthDay: r.get('Person_BirthDay'),
				Person_Fio: r.get('Person_Fio'),
				Person_id: r.get('Person_id'),
				EvnSection_setDate: this.dateMenu.getValue(),
				EvnPS_setDT:r.get('EvnPS_setDT'),
				EvnPS_id:r.get('EvnPS_id'),
				onSelect: function(data) {
					if (data.emptyWard) {
						win.openEvnPSPriemEditWindow({
							action: 'edit',
							activatePanel: 'EPSPEF_PriemLeavePanel',
							EvnPS_OutcomeDate: data.EvnSection_setDate,
							EvnPS_OutcomeTime: data.EvnSection_setTime
						});
					}
					else {
						win.hospitalization(data);
					}
				}
			});
		}
		else {
			getWnd('swLpuSectionSelectWindow').show({
				formMode: 'hospitalization',
				LpuSection_uid: this.userMedStaffFact.LpuSection_id,
				Person_BirthDay: r.get('Person_BirthDay'),
				EvnSection_setDate: this.dateMenu.getValue(),
				EvnPS_setDT:r.get('EvnPS_setDT'),
				EvnPS_id:r.get('EvnPS_id'),
				onSelect: function(data) {
					if(data.openEvnPSPriemEditWindow){
						win.openEvnPSPriemEditWindow({action: 'edit', activatePanel: 'EPSPEF_HospitalisationPanel'});
					}else{
						win.hospitalization(data);
					}
				}
			});
		}
	},
	/** На госпитализацию - создание движения если движения еще нет. 
	 *
	 */
	hospitalization: function(item) {
		//log(item);
		var r = this.getSelectedRecord();
		var IsHosp=1;
		if (r) {
			IsHosp = r.get('IsHospitalized');
			var win = this;
		}
		var params = {};
		params.EvnSection_pid = r.get('EvnPS_id');
		params.Person_id = r.get('Person_id');
		params.PersonEvn_id = r.get('PersonEvn_id');
		params.Server_id = r.get('Server_id');
		params.LpuSection_id = item.LpuSection_id;
		params.LpuSectionWard_id =  item.LpuSectionWard_id;
		params.EvnSection_setTime = item.EvnSection_setTime;
		params.EvnSection_setTime = item.EvnSection_setTime;
		params.EvnSection_setDate = Ext.util.Format.date(item.EvnSection_setDate, 'd.m.Y');
		params.vizit_direction_control_check = (item && !Ext.isEmpty(item.vizit_direction_control_check) && item.vizit_direction_control_check === 1) ? 1 : 0;
		if (!(IsHosp==2)) {
			// Создаем движение по указанному отделению
			win.getLoadMask(LOAD_WAIT_SAVE).show();
			Ext.Ajax.request({
				url: '/?c=EvnSection&m=saveEvnSectionInHosp',
				params: params,
				failure: function(response, options) {
					win.getLoadMask().hide();
					Ext.Msg.alert(lang['oshibka'], lang['pri_zapisi_na_gospitalizatsiyu_proizoshla_oshibka']);
				},
				success: function(response, action)
				{
					win.getLoadMask().hide();
					if (response.responseText) {
						var action = Ext.util.JSON.decode(response.responseText);

						if (!action.success) {
							if ( action.Error_Msg && 'YesNo' != action.Error_Msg) {
								if (action.Error_Code && action.Error_Code == '101') {
									sw.swMsg.alert(lang['oshibka'], action.Error_Msg, function() {
										win.openEvnPSPriemEditWindow({action: 'edit'});
									}); 
								} else {
									sw.swMsg.alert(lang['oshibka'], action.Error_Msg);
								}
							} else if ( action.Alert_Msg && 'YesNo' == action.Error_Msg ) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ( buttonId == 'yes' ) {
											if (action.Error_Code == 112) {
												item.vizit_direction_control_check = 1;
											}

											win.hospitalization(item);
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: action.Alert_Msg,
									title: lang['prodoljit_sohranenie']
								});
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						} else {
							win.listRefresh();
						}
					}
					else {
						Ext.Msg.alert(lang['oshibka'], lang['pri_zapisi_na_gospitalizatsiyu_proizoshla_oshibka_otsutstvuet_otvet_servera']);
					}
				}
			});
		}
	},
	/** Отмена госпитализации
	 *
	 */
	cancelHospitalization: function () {
		var r = this.getSelectedRecord();
		if (r) {
			var EvnPS_id = r.get('EvnPS_id');
			var win = this;
			var deleteEvnSectionInHosp = function(EvnSection_id){
				Ext.Ajax.request({
					url: '/?c=EvnSection&m=deleteEvnSectionInHosp', 
					params: {EvnSection_id: EvnSection_id, EvnPS_id: EvnPS_id},
					failure: function(response, options) {
						Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
					},
					success: function(response, action)
					{
						if (response.responseText) {
							var answer = Ext.util.JSON.decode(response.responseText);
							if (!answer.success) {
								if (answer.Error_Code) {
									sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
								}
							}
							win.listRefresh();
						}
						else {
							Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
						}
					}
				});
			};
			
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['otmenit_gospitalizatsiyu_po_vyibrannomu_cheloveku'],
				title: lang['podtverjdenie'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						Ext.Ajax.request({
							url: '/?c=EvnSection&m=getEvnSectionLast', 
							params: {EvnPS_id: EvnPS_id},
							failure: function(response, options) {
								Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
							},
							success: function(response, action)
							{
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if(answer.length > 0)
									{
										if(answer[0].MedPersonal_id > 0 || answer[0].ChildEvn_Cnt > 0)
										{
											Ext.Msg.alert(lang['soobschenie'], lang['otmenit_gospitalizatsiyu_nevozmojno_est_podchinennyie_dvijeniyu_sobyitiya_libo_v_dvijenii_ukazan_lechaschiy_vrach']);
										} else {
											deleteEvnSectionInHosp(answer[0].EvnSection_id);
										}
									}
									else
									{
										Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_dvijenie_v_ramkah_dannogo_sluchaya']);
									}
								}
								else {
									Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
								}
							}
						});
					}
				}
			});
		}
	},
	/** Создание списка отделений, в которые можно госпитализировать
	 *  Список состоит только из стационаров (исключая на дому) подразделения, в котором находится выбранное приемное отделение
	 */
	/*
	createListStacSection: function(LpuSection_id) {
		// Сначала получаем подразделение 
		var LpuUnit_id = null;
		var store = swLpuSectionGlobalStore;
		var idx = store.findBy(function(rec) {
			if (rec.get('LpuSection_id') == LpuSection_id) {
				return true;
			}
			else {
				return false;
			 }
		});
		var record = store.getAt(idx);
		if (record) {
			LpuUnit_id = record.get('LpuUnit_id');
		}
		
		var text = null;
		var LpuSectionProfile_did = null;
		var PrehospStatus_id = null;
		record = this.getGrid().getSelectionModel().getSelected();
		if (record) {
			LpuSectionProfile_did = record.get('LpuSectionProfile_did');
			PrehospStatus_id = record.get('PrehospStatus_id');
		}
		
		var win = this;
		var menuArr = new Array();
		var menu = new Ext.menu.Menu({id:'ListStacSectionMenu'});
		
		// Далее получаем список отделений в этом подразделении и из списка получаем меню 
		store.each(function(record) {
			if (record.get('LpuUnitType_id').inlist([1, 6, 9]) && record.get('LpuUnit_id')==LpuUnit_id && record.get('LpuSectionProfile_id')!=75) { // из стационаров (исключая на дому) подразделения, в котором находится выбранное приемное отделение
				text = record.get('LpuSection_Code')+'. '+record.get('LpuSection_Name');
				if(record.get('LpuSectionProfile_id') == LpuSectionProfile_did && PrehospStatus_id == 3)
					text = '<b>'+text+'</b>';
				menuArr.push({text: text, LpuSection_id: record.get('LpuSection_id'), iconCls: 'lpu-section16', handler: function() {win.hospitalization(this);}});
			}
		});
		for (key in menuArr) {
			if (key!='remove')
				menu.add(menuArr[key]);
		}
		return menu;
	},*/
	/** Создание меню списка причин отказа
	 *
	 */
	createListPrehospWaifRefuseCause: function() {
		var win = this;
		// Сначала получаем список причин отказа
		Ext.Ajax.request({
			url: '/?c=Utils&m=GetObjectList',
			params:  {object:'PrehospWaifRefuseCause', PrehospWaifRefuseCause_id:'',PrehospWaifRefuseCause_Code:'', PrehospWaifRefuseCause_Name:''},
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['pri_poluchenii_spiska_prichin_otkazov_proizoshla_oshibka']);
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					var menuArr = new Array();
					var menu = new Ext.menu.Menu({id:'ListPrehospWaifRefuseCauseMenu'});
					
					if (result) {
						for(var i = 0;i < result.length;i++) {
							menuArr.push({text: result[i]['PrehospWaifRefuseCause_Code']+'. '+result[i]['PrehospWaifRefuseCause_Name'], PrehospWaifRefuseCause_id: result[i]['PrehospWaifRefuseCause_id'], iconCls: 'sprav16', handler: function() {win.setPrehospWaifRefuseCause(this);}});
						}
					}
					for (key in menuArr) {
						if (key!='remove')
							menu.add(menuArr[key]);
					}
					win.ViewContextMenu.items.item(12).menu = menu;
					win.gridToolbar.items.item(11).menu = menu; // @todo исправить, чтобы меню вставлялось в правильное место
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_poluchenii_spiska_prichin_otkazov_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				}
			}
		});
	},
	/** Отказ в госпитализации
	 */
	setPrehospWaifRefuseCause: function(item) {
		//log(item);
		var r = this.getSelectedRecord();
		var win = this;
		var params = {};
		params.Person_id = r.get('Person_id');
		params.PersonEvn_id = r.get('PersonEvn_id');
		params.Server_id = r.get('Server_id');
		params.EvnPS_id = r.get('EvnPS_id');
		params.EvnPS_IsTransfCall = 1;
		params.PrehospWaifRefuseCause_id = item.PrehospWaifRefuseCause_id;
		params.LpuSection_pid = this.userMedStaffFact.LpuSection_id;
		params.MedPersonal_pid = this.userMedStaffFact.MedPersonal_id;
		params.PayType_SysNick = r.get('PayType_SysNick');
		params.PrehospArrive_SysNick = r.get('PrehospArrive_SysNick');
		params.PrehospArrive_Name = r.get('PrehospArrive_Name');
		params.PrehospType_SysNick = r.get('PrehospType_SysNick');
		params.PrehospDirection_Name = r.get('PrehospDirection_Name');
		params.Diag_id = r.get('Diag_id');

		if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'krym', 'perm', 'pskov', 'penza', 'msk' ]) && !Ext.isEmpty(item.PrehospWaifRefuseCause_id) ) {
			// для Бурятии, Карелии, Перми и Екатеринбурга показываем доп. форму
			if ( getWnd('swLeavePriemEditWindow').isVisible() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['forma_redaktirovaniya_ishoda_prebyivaniya_v_priemnom_otdelenii_v_dannyiy_moment_otkryita']);
				return false;
			}

			getWnd('swLeavePriemEditWindow').show({
				action: 'add',
				callback: function() {
					// Справку об отказе 
					if ( item.PrehospWaifRefuseCause_id ) {
						printBirt({
							'Report_FileName': 'printEvnPSPrehospWaifRefuseCause.rptdesign',
							'Report_Params': '&paramEvnPsID=' + r.get('EvnPS_id'),
							'Report_Format': 'pdf'
						});
						printBirt({
							'Report_FileName': 'printPatientRefuse.rptdesign',
							'Report_Params': '&paramEvnPsID=' + r.get('EvnPS_id'),
							'Report_Format': 'pdf'
						});
					}

					win.listRefresh();
				},
				formParams: params,
				IsRefuse: true,
				onHide: Ext.emprtFn
			});

		}
		else {
			// Создаем отказ и выводим справку об отказе
			Ext.Ajax.request({
				url: '/?c=EvnPS&m=saveEvnPSWithPrehospWaifRefuseCause',
				params: params,
				failure: function(response, options) {
					Ext.Msg.alert(lang['oshibka'], lang['pri_zapisi_sohranenii_v_gospitalizatsii_proizoshla_oshibka']);
				},
				success: function(response, action)
				{
					if (response.responseText) {
						var answer = Ext.util.JSON.decode(response.responseText);
						if (!answer.success) {
							if (answer.Error_Code) {
								sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
							}
						} else {
							// Справку об отказе 
							if (item.PrehospWaifRefuseCause_id){
								printBirt({
									'Report_FileName': 'printEvnPSPrehospWaifRefuseCause.rptdesign',
									'Report_Params': '&paramEvnPsID=' + r.get('EvnPS_id'),
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'printPatientRefuse.rptdesign',
									'Report_Params': '&paramEvnPsID=' + r.get('EvnPS_id'),
									'Report_Format': 'pdf'
								});
							}
							win.listRefresh();
						}
					}
					else {
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_otkaza_v_gospitalizatsii_proizoshla_oshibka_otsutstvuet_otvet_servera']);
					}
				}
			});
		}
	},
	/** Добавление случая АПЛ
	 */
	editEvnPL: function(action) {
		
		var r = this.getSelectedRecord();
		var win = this;
		
		if (action == 'add') {
			var params = {
				Diag_id: r.get('Diag_did'),
				EvnPL_lid: r.get('EvnPS_id'),
				EvnVizitPL_setDate: r.get('EvnPS_OutcomeDT'),
				EvnVizitPL_setTime: Ext.util.Format.date(r.get('EvnPS_OutcomeDT'), 'H:i'),
				Person_id: r.get('Person_id'),
				PersonEvn_id: r.get('PersonEvn_id'),
				Server_id: r.get('Server_id'),
				streamInput: true,
				action: action,
				callback: function(data) {
					win.listRefresh({disableSavePosition: false});
				}
			};
		} else {
			var params = {
				EvnPL_id: r.get('EvnPL_id'),
				Person_id: r.get('Person_id'),
				PersonEvn_id: r.get('PersonEvn_id'),
				Server_id: r.get('Server_id'),
				action: action,
				callback: function(data) {
					win.listRefresh({disableSavePosition: false});
				}
			};		
		}
		
		win.openForm('swEvnPLEditWindow', params, WND_POL_EPLEDIT);
		
	},
	/** Сохранение признака "Передан активный вызов"
	 */
	setActiveCall: function() {
		var r = this.getSelectedRecord();
		var win = this;
		var params = {};
		params.EvnPS_id = r.get('EvnPS_id');
		Ext.Ajax.request({
			url: '/?c=EvnPS&m=setActiveCall',
			params: params,
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['pri_zapisi_priznaka_peredan_aktivnyiy_vyizov_proizoshla_oshibka']);
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (!answer.success) {
						if (answer.Error_Code) {
							sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
						}
					} else {

						
						win.listRefresh();
					}
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_priznaka_peredan_aktivnyiy_vyizov_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				}
			}
		});
	},
	/** Сохранение признака "Талон передан на ССМП"
	 */
	setTransmitAmbulance: function() {
		var r = this.getSelectedRecord();
		var win = this;
		var params = {};
		params.EvnPS_id = r.get('EvnPS_id');
		Ext.Ajax.request({
			url: '/?c=EvnPS&m=setTransmitAmbulance',
			params: params,
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['pri_zapisi_priznaka_talon_peredan_na_ssmp_proizoshla_oshibka']);
			},
			success: function(response, action)
			{
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (!answer.success) {
						if (answer.Error_Code) {
							sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
						}
					} else {

						
						win.listRefresh();
					}
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_priznaka_talon_peredan_na_ssmp_proizoshla_oshibka_otsutstvuet_otvet_servera']);
				}
			}
		});
	},
	setPersonInTimetableStac: function(params) {
		var r = this.getSelectedRecord();
		if (r) {
			var TimetableStac_id = r.get('TimetableStac_id');
			Ext.Ajax.request({
				url: '/?c=TimetableGraf&m=setPersonInTimetableStac',
				params:  {TimetableStac_id:TimetableStac_id,Person_id:params.Person_id},
				failure: function(response, options) {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_zapisi_svyazi_birki_neidentifitsirovannogo_bolnogo_s_vyibrannyim_patsientom']);
				},
				callback: function(response, action)
				{
					r.set('Person_id', params.Person_id);
					r.set('Person_Fio', params.Person_Surname+' '+params.Person_Firname+' '+params.Person_Secname);
					r.commit();
				}
			});
		}
	},
	extDirection: function() {
		var win = this;
		var swPersonSearchWindow = getWnd('swPersonSearchWindow');
				if ( swPersonSearchWindow.isVisible() ) {
					sw.swMsg.alert('Окно поиска человека уже открыто', 'Для продолжения необходимо закрыть окно поиска человека.');
					return false;
				}

				var params = {
					onSelect: function(pdata)
					{
						getWnd('swPersonSearchWindow').hide();
						var personData = new Object();
						
						personData.Person_id = pdata.Person_id;
						personData.Person_IsDead = pdata.Person_IsDead;
						personData.Person_Firname = pdata.Person_Firname;
						personData.Person_Surname = pdata.Person_Surname;
						personData.Person_Secname = pdata.Person_Secname;
						personData.PersonEvn_id = pdata.PersonEvn_id;
						personData.Server_id = pdata.Server_id;
						personData.Person_Birthday = pdata.Person_Birthday;
						
						getWnd('swDirectionMasterWindow').show({
							type: 'ExtDirPriem',
							date: null,
							personData: personData,
							onClose: function() {
								this.buttons[0].show();
								this.buttons[1].show();
							},
							onDirection: function (dataEvnDirection_id) {
								var EvnDirId = false;
								if(dataEvnDirection_id.EvnDirection_id) {
									EvnDirId = dataEvnDirection_id.EvnDirection_id;
								} else {
									if(dataEvnDirection_id.evnDirectionData && dataEvnDirection_id.evnDirectionData.EvnDirection_id){
										EvnDirId = dataEvnDirection_id.evnDirectionData.EvnDirection_id;
									}
								}
								if(!EvnDirId) {
									sw.swMsg.alert(langs('Сообщение'), langs('Мастер выписки направлений не вернул идентификатор направления.'));
									return false;
								}
								
								win.listRefresh();
							}
						});
					},
					searchMode: 'all'
				};
				getWnd('swPersonSearchWindow').show(params);
	},
	selfTreatment: function(person_data)
	{
		var win = this;
		this.openPersonSearchWindow({
			//searchMode
			//onClose
			person_data: person_data,
			onSelect: function(pdata) 
			{
				Ext.Ajax.request({
					params: {
						useCase: 'self_treatment',
						Person_id: pdata.Person_id
					},
					callback: function(opt, success, response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if ( Ext.isArray(response_obj) ) {
							if ( response_obj.length > 0 ) {
								// выводим список этих направлений с возможностью выбрать одно из них
								getWnd('swEvnDirectionSelectWindow').show({
									useCase: 'self_treatment',
									storeData: response_obj,
									formType: 'stac',
									Person_Birthday: pdata.Person_Birthday,
									Person_Firname: pdata.Person_Firname,
									Person_Secname: pdata.Person_Secname,
									Person_Surname: pdata.Person_Surname,
									Person_id:pdata.Person_id,
									callback: function(evnDirectionData){
										if (evnDirectionData && evnDirectionData.EvnDirection_id){
											// создавать КВС со связью с направлением
											pdata.EvnDirectionData=evnDirectionData;
										} else {
											// создать КВС без связи с направлением
											pdata.EvnDirectionData=null;
										}
									},
									onHide: function(){
										// если направление не выбрано, то создавать КВС без связи с направлением
										win.createEvnPS(pdata);
									}
								});
							} else {
								// создать КВС без связи с направлением
								pdata.EvnDirectionData=null;
								win.createEvnPS(pdata);
							}
						} else {
							showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_nepravilnyiy_otvet_servera']);
							return false;
						}
					},
					url: '/?c=EvnDirection&m=loadEvnDirectionList'
				});
				return true;
				
				Ext.Ajax.request({
					params: {Person_id: pdata.Person_id},
					callback: function(opt, success, response) {
						var result = Ext.util.JSON.decode(response.responseText);
						//log(result);
						if (Ext.isArray(result) && result.length > 0) {
							var d = [],t = [],e = [];
							var storeData=[];
							for(var i=0;i<result.length;i++){
								if(result[i].name == 'EvnDirection' || result[i].name == 'EvnQueue'){
									d.push(i);
									storeData.push(result[i]);
								}
								if(result[i].name == 'TimetableStac' && Ext.isEmpty(result[i].EvnPS_CodeConv))
									t.push(i);
								if(result[i].name == 'TimetableStac' && !Ext.isEmpty(result[i].EvnPS_CodeConv))
									e.push(i);
							}
							//если у пациента несколько направлений в данное ЛПУ без признака отмены и не "погашенное" (нет связи со случаем лечения)
							if(d.length >= 1)
							{
								getWnd('swEvnDirectionStacSelectWindow').show({
									storeData: storeData,
									Person_Birthday: pdata.Person_Birthday,
									Person_Firname: pdata.Person_Firname,
									Person_Secname: pdata.Person_Secname,
									Person_Surname: pdata.Person_Surname,
									Person_id:pdata.Person_id,
									callback: function(evnDirectionData){
										if (evnDirectionData && evnDirectionData.EvnDirection_id){
											// создавать случай со связью с направлением
											pdata.EvnDirectionData=evnDirectionData;
										} else {
											// создать случай без связи с направлением
											pdata.EvnDirectionData=null;
										}
									},
									onHide: function(){
										// если направление не выбрано, то создавать случай без связи с направлением
										win.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata, result:pdata.EvnDirectionData});
									}
								});
								/*sw.swMsg.show(
								{
									icon: Ext.MessageBox.QUESTION,
									msg: lang['u_patsienta_est_neskolko_napravleniy_zavesti_postuplenie_patsienta_bez_napravleniya'],
									title: lang['vopros'],
									buttons: Ext.Msg.OKCANCEL,
									fn: function(buttonId)
									{
										if ('ok' == buttonId)
										{
											// принять как самостоятельно обратившегося
											this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata});
										}
									}.createDelegate(this)
								});*/
								return true;
							}
							//если у пациента несколько экстр. бирок в данное ЛПУ
							if(e.length > 1)
							{
								sw.swMsg.show(
								{
									icon: Ext.MessageBox.QUESTION,
									msg: lang['u_patsienta_est_neskolko_napravleniy_ssmp_zavesti_postuplenie_patsienta_ne_po_napravleniyu_ssmp'],
									title: lang['vopros'],
									buttons: Ext.Msg.OKCANCEL,
									fn: function(buttonId)
									{
										if ('ok' == buttonId)
										{
											// принять как самостоятельно обратившегося
											this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata});
										}
									}.createDelegate(this)
								});
								return true;
							}
							//если у пациента несколько бирок в данное ЛПУ
							if(t.length > 1)
							{
								sw.swMsg.show(
								{
									icon: Ext.MessageBox.QUESTION,
									msg: lang['u_patsienta_est_neskolko_birok_zavesti_postuplenie_patsienta_ne_po_zapisi'],
									title: lang['vopros'],
									buttons: Ext.Msg.OKCANCEL,
									fn: function(buttonId)
									{
										if ('ok' == buttonId)
										{
											// принять как самостоятельно обратившегося
											this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata});
										}
									}.createDelegate(this)
								});
								return true;
							}
							var checkSsmp = function(){
								// если есть у пациента одна экстр. бирка в данное ЛПУ
								if(e.length == 1)
								{
									i = e[0];
									sw.swMsg.show(
									{
										icon: Ext.MessageBox.QUESTION,
										msg: lang['u_patsienta_est_napravlenie_ssmp_№']+ result[i].num +', '+ result[i].recdate +','+ result[i].LpuSection_Name +','+ result[i].Diag_Name +lang['prinyat_patsienta_po_dannomu_napravleniyu'],
										title: lang['vopros'],
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId)
										{
											if ('yes' == buttonId)
											{
												// открыть КВС со связкой с найденной экстр. биркой
												this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata, result: result[i]});
											}
											else
											{
												// Принять пациента без электронного направления или экстренной бирки
												this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata});
											}
										}.createDelegate(this)
									});
								}
								else
								{
									// Принять пациента без электронного направления или экстренной бирки
									this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata});
								}
							}.createDelegate(this);
							
							var checkTTS = function(){
								// если есть у пациента одна экстр. бирка в данное ЛПУ
								if(t.length == 1)
								{
									i = t[0];
									sw.swMsg.show(
									{
										icon: Ext.MessageBox.QUESTION,
										msg: 'У пациента есть запись на '+ result[i].recdate +' в отделение "'+ result[i].LpuSection_Name +'". Принять пациента по данной записи?',
										title: lang['vopros'],
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId)
										{
											if ('yes' == buttonId)
											{
												// открыть КВС со связкой с найденной экстр. биркой
												this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata, result: result[i]});
											}
											else
											{
												checkSsmp();
											}
										}.createDelegate(this)
									});
								}
								else
								{
									// Принять пациента без электронного направления или экстренной бирки
									this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata});
								}
							}.createDelegate(this);
							
							//если есть у пациента одно направление в данное ЛПУ без признака отмены и не "погашенное" (нет связи со случаем лечения)
							if(d.length == 1)
							{
								i = d[0];
								sw.swMsg.show(
								{
									icon: Ext.MessageBox.QUESTION,
									msg: lang['u_patsienta_est_napravlenie_№']+ result[i].num +', '+ (result[i].recdate || lang['v_ocheredi']) +','+ result[i].LpuSection_Name +','+ result[i].Diag_Name +lang['prinyat_patsienta_po_dannomu_napravleniyu'],
									title: lang['vopros'],
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId)
									{
										if ('yes' == buttonId)
										{
											// открыть КВС со связкой с найденным направлением
											this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata, result: result[i]});
										}
										else
										{
											checkTTS();
										}
									}.createDelegate(this)
								});
							}
							else
							{
								checkTTS();
							}
							
							/*
							// если у пациента уже есть самостоятельное обращение в течение текущих стационарных суток
							if(result.EvnPS_id)
							{
								sw.swMsg.alert(lang['soobschenie'], lang['u_patsienta_uje_byilo_obraschenie_za_poslednie_sutki'], function() {
									this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'edit', person: pdata, EvnPS_id: result.EvnPS_id});
								}.createDelegate(this));
								return true;
							}
							*/
							
						} else {
							// Проверка ничего не вернула
							this.openEvnPSPriemEditWindow({from: 'selfTreatment', action: 'add', person: pdata});
						}
					}.createDelegate(this),
					url: '/?c=EvnPS&m=checkSelfTreatment'
				});
			}.createDelegate(this),
			deadMsg: lang['obraschenie_nevozmojno_v_svyazi_so_smertyu_patsienta']
		});
	},
	setActionDisabled: function(action, flag)
	{
		if (this.gridActions[action])
		{
			this.gridActions[action].initialConfig.initialDisabled = flag;
			this.gridActions[action].setDisabled(flag);
		}
	},
	curDate: null,
	stepDay: function(day)
	{
		var datefield = this.dateMenu;
		var date = (datefield.getValue() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		datefield.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		datefield.fireEvent('change', datefield, date, null);
	},
	prevDay: function ()
	{
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	currentDay: function ()
	{
		var date = Date.parseDate(this.curDate, 'd.m.Y');
		var datefield = this.dateMenu;
		datefield.setValue(Ext.util.Format.date(date, 'd.m.Y'));
		datefield.fireEvent('change', datefield, date, null);
	},
	getCurrentDateTime: function() 
	{
		var frm = this;
		frm.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request(
		{
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) 
			{
				if (success && response.responseText != '')
				{
					frm.getLoadMask().hide();
					var result  = Ext.util.JSON.decode(response.responseText);
					frm.curDate = result.begDate;
					frm.curTime = result.begTime;
					frm.userName = result.pmUser_Name;
					frm.currentDay();
				}
			}
		});
	},
	printAddressLeaf: function(leaf_type) {
		var r = this.getSelectedRecord();

		if ( typeof r != 'object' || !leaf_type || !leaf_type.inlist(['arrival','departure']) ) {
			return false;
		}

		var Person_id = r.get('Person_id');
		if ( Ext.isEmpty(Person_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_patsient']);
			return false;
		}

		var Lpu_id = getGlobalOptions().lpu_id;
		if ( Ext.isEmpty(Lpu_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazano_lpu']);
			return false;
		}

		var tpl = '';
		if (leaf_type == 'arrival') {
			tpl = 'LeafArrival.rptdesign'
		} else
		if (leaf_type == 'departure') {
			tpl = 'LeafDeparture.rptdesign'
		}

		printBirt({
			'Report_FileName': tpl,
			'Report_Params': '&paramPerson_id='+Person_id+'&paramLpu='+Lpu_id,
			'Report_Format': 'pdf'
		});
	},
	
	//BOB - 15.11.2017 функция перевода в реанимацию
	moveToReanimation: function(pdata) {
		var win = this;
		var param;
		console.log('BOB_pdata=',pdata); //BOB - 15.11.2017
		
		//из окна выбора карты ВС / реанимационных медслужб, если их было много
		if (pdata['Status'] && pdata['Status'] == 'FromManyEvnPS'){
				
			param = {
				Person_id : pdata.Person_id,
				Server_id : pdata.Server_id,
				PersonEvn_id : pdata.PersonEvn_id,
				EvnPS_id : pdata.EvnPS_id,
				Lpu_id: this.userMedStaffFact.Lpu_id,
				MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
				EvnSection_id : pdata.EvnSection_id, 
				LpuSection_id : pdata.LpuSection_id,
				MedService_id : pdata.MedService_id,       
				ARMType : 'FromManyEvnPS'
			};

		} else {
		
			var r = this.getSelectedRecord();
			console.log('BOB_r=',r); //BOB - 15.11.2017
			
			console.log('BOB_r=',r.data.groupField); //BOB - 15.11.2017
			if (r.data.groupField != 4) {
				sw.swMsg.alert(lang['oshibka'], 'Данный пациент не госпитализирован!'  );
				return false;
			};
			
			param = {
				EvnPS_id: r.data.EvnPS_id,
				Person_id: r.data.Person_id,
				Server_id: r.data.Server_id,
				PersonEvn_id: r.data.PersonEvn_id,
				Lpu_id: this.userMedStaffFact.Lpu_id,
				MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
					EvnSection_id : 0, 
					LpuSection_id : 0,
					MedService_id : 0,       
				ARMType: this.ARMType
			};
		}

		Ext.Ajax.request({
			url: '/?c=EvnReanimatPeriod&m=moveToReanimationFromPriem',
			params: param,
			callback: function(options, success, response){
				if(success){
					var answer = Ext.util.JSON.decode(response.responseText);
					console.log('BOB_answer=',answer); 
					
					// катастрофическая ошибка наверняка связанная с неправильным программированием
					if (answer['success'] == false) {
						sw.swMsg.alert(langs('Сообщение'), answer['success']+ ' ' + answer['Error_Msg']);
						return false;
					}
	
					if(answer['Status'] == 'AlreadyInReanimation')
						sw.swMsg.alert(lang['soobschenie'], answer['Message']);
					else if (answer['Status'] == 'DoneSuccessfully'){
	
						var params = {
							EvnReanimatPeriod_id: answer['EvnReanimatPeriod_id'],
							ERPEW_title: lang['redaktirovanie_reanimationnogo_perioda'],  			
							action: 'edit',
							UserMedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
							userMedStaffFact: win.userMedStaffFact,
							from: 'moveToReanimation', 
							ARMType: win.ARMType 
						};
						//console.log('BOB_params1=',params); 					
						
						var RP_saved = false;
						params.Callback = function(pdata) {
							getWnd('swEvnReanimatPeriodEditWindow').hide();                            
							RP_saved = pdata; 
							//	console.log('BOB_RP_saved=',RP_saved); 
							sw.swMsg.alert(langs('Сообщение'), 'Пациент переведён в реанимацию');
						};    
						getWnd('swEvnReanimatPeriodEditWindow').show(params);
					}
					else if (answer['Status'] == 'NoReanimatMedService'){  //отсутствуют службы реанимации   //BOB - 19.06.2019
						sw.swMsg.alert(langs('Сообщение'), answer['Message']);
					}
					else if (answer['Status'] == 'ManyReanimatMedService'){  //несколько служб реанимации    //BOB - 19.06.2019
	
						var personParams = {
							callback: function(pdata) {
								getWnd('ufa_ToReanimationFromFewPSWindow').hide();                            
								Ext.getCmp('swMPWorkPlacePriemWindow').moveToReanimation(pdata); 
							},    
							Server_id: answer['Server_id'],
							Person_id: answer['Person_id'],
							PersonEvn_id: answer['PersonEvn_id'],
							Lpu_id: win.userMedStaffFact.Lpu_id,
							Status:answer['Status'],
							EvnPS_id : answer['EvnPS_id'],
							EvnSection_id : answer['EvnSection_id'], 
							LpuSection_id : answer['LpuSection_id'],
							MedService_id : 0
						};
	
						getWnd('ufa_ToReanimationFromFewPSWindow').show(personParams);
					}
					else
						sw.swMsg.alert(lang['soobschenie'], answer['Message']);
				} else {
					sw.swMsg.alert(langs('Сообщение'), langs('Ошибка при получении реанимационных служб'));
				}
			},
			failure: function() {
				showSysMsg(langs('При обработке запроса на сервере произошла ошибка!'));
			},
		});	
		
		
		
		
	},
	printPersonSoglasie: function(paramEvnSection_id, Person_id){
		if(paramEvnSection_id){
			Ext.Msg.show({
				title:'Информированное добровольное согласие на мед.вмешательство',
				msg: 'Выберите вариант печати Информированного добровольного согласия на мед.вмешательство.',
				buttons: {
					yes: 'Лично',
					no: 'Представитель',
					cancel: 'Отмена'
				},
				fn: function(btn) {
					var paramPerson = Person_id;
					var paramDeputy = null;
					if ( btn != 'cancel' ) {
						if ( btn == 'yes' ) {
							paramDeputy = 1;
						}
						if ( btn == 'no' ) {
							paramDeputy = 2;
						}
						printBirt({
							'Report_FileName': 'Person_soglasie_stac.rptdesign',
							'Report_Params': '&paramPerson=' + paramPerson + (paramEvnSection_id?('&paramEvnSection=' + paramEvnSection_id):'') + (paramDeputy?('&paramDeputy=' + paramDeputy):''),
							'Report_Format': 'pdf'
						});
					}
				},
				icon: Ext.Msg.QUESTION,
			});
		}else{

			sw.swMsg.alert(lang['oshibka'], 'Отсутствует ID движения');

		}	
	},
	initComponent: function()
	{
		
		this.formActions = new Array();

		var wnd = this;

		this.dateMenu = new sw.Promed.SwDateField(
		{
			fieldLabel: lang['data'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999', false)
			],
			xtype: 'swdatefield',
			format: 'd.m.Y',
			hideLabel: true,
			listeners:
			{
				'keydown': function (inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ENTER)
					{
						e.stopEvent();
						wnd.doSearch();
					}
				},
				'change': function (field, newValue, oldValue) 
				{
					wnd.doSearch();
				}
			}
		});
		this.formActions.prev = new Ext.Action(
		{
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
				// на один день назад
				this.prevDay();
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action(
		{
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function()
			{
				// на один день вперед
				this.nextDay();
			}.createDelegate(this)
		});
		this.DoctorToolbar = new Ext.Toolbar(
		{
			items:
			[
				this.formActions.prev,
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				{
					xtype : "tbseparator"
				},
				this.formActions.next
			]
		});
		
		var prehospStatusArray = [[0, ''], [4, 'Госпитализирован'], [5,'Отказ']];

		if ( getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
			prehospStatusArray.push([2,'Принят']);
		}

		this.FilterPanel = new Ext.form.FormPanel({
			region: 'north',
			frame: true,
			tbar: this.DoctorToolbar,
			border: false,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 50,
			items: 
			[{
				xtype: 'fieldset',
				style:'padding: 0px 3px 3px 6px;',
				autoHeight: true,
				listeners: {
					expand: function() {
						this.ownerCt.doLayout();
						form.syncSize();
					},
					collapse: function() {
						form.syncSize();
					}
				},
				collapsible: true,
				collapsed: false,
				title: lang['filtr'],
				bodyStyle: 'background: #DFE8F6;',
				items: 
				[{
					layout: 'column',
					items: 
					[{
						layout: 'form',
						labelWidth: 55,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
							id: 'mpwpprSearch_SurName',
							name:'Person_Surname',
							fieldLabel: lang['familiya'],
							listeners:
							{
								'keydown': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					},{
						layout: 'form',
						labelWidth: 35,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
							id: 'mpwpprSearch_FirName',
							name:'Person_Firname',
							fieldLabel: lang['imya'],
							listeners:
							{
								'keydown': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					},{
						layout: 'form',
						labelWidth: 65,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
							id: 'mpwpprSearch_SecName',
							name:'Person_Secname',
							fieldLabel: lang['otchestvo'],
							listeners:
							{
								'keydown': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					},
					{
						layout: 'form',
						labelWidth: 25,
						items:
						[{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name:'Person_Birthday',
							id: 'mpwpprSearch_BirthDay',
							fieldLabel: lang['dr'],
							listeners: 
							{
								'keydown': function (inp, e) 
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					},{
						layout: 'form',
						labelWidth: 75,
						hidden: getRegionNick() != 'msk',
						items:
							[{
								xtype: 'textfieldpmw',
								width: 60,
								margins: '20 0',
								id: 'mpwpprSearch_PSNumCard',
								name:'PSNumCard',
								fieldLabel: lang['nomer_kvs'],
								listeners:
									{
										'keydown': function (inp, e)
										{
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												this.doSearch();
											}
										}.createDelegate(this)
									}
							}]
					}]
				},
				{
					layout: 'column',
					items: 
					[{
						layout: 'form',
						labelWidth: 55,
						items: 
						[{
							id: 'mpwpprSearch_EvnQueueShow_id',
							fieldLabel: lang['ochered'],
							mode: 'local',
							store: new Ext.data.SimpleStore(
							{
								key: 'EvnQueueShow_id',
								fields:
								[
									{name: 'EvnQueueShow_id', type: 'int'},
									{name: 'EvnQueueShow_Name', type: 'string'}
								],
								data: [[0, lang['ne_pokazyivat']], [1,lang['pokazat']], [2,lang['pokazat_vklyuchaya_arhiv']]]
							}),
							editable: false,
							enableKeyEvents: true,
							width: 170,
							triggerAction: 'all',
							lastQuery: '',
							displayField: 'EvnQueueShow_Name',
							valueField: 'EvnQueueShow_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">{EvnQueueShow_Name}</div></tpl>',
							hiddenName: 'EvnQueueShow_id',
							xtype: 'combo',
							listeners: 
							{
								'keypress': function (inp, e) 
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					},
					{
						layout: 'form',
						labelWidth: 135,
						items: 
						[{
							id: 'mpwpprSearch_EvnDirectionShow_id',
							fieldLabel: lang['plan_gospitalizatsiy'],
							mode: 'local',
							store: new Ext.data.SimpleStore(
							{
								key: 'EvnDirectionShow_id',
								fields:
								[
									{name: 'EvnDirectionShow_id', type: 'int'},
									{name: 'EvnDirectionShow_Name', type: 'string'}
								],
								data: [[0, lang['na_tekuschiy_den']], [1,lang['vse_napravleniya']]]
							}),
							editable: false,
							enableKeyEvents: true,
							width: 170,
							triggerAction: 'all',
							lastQuery: '',
							displayField: 'EvnDirectionShow_Name',
							valueField: 'EvnDirectionShow_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">{EvnDirectionShow_Name}</div></tpl>',
							hiddenName: 'EvnDirectionShow_id',
							xtype: 'combo',
							listeners: 
							{
								'keypress': function (inp, e) 
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 10,
						items: [{
							xtype: 'checkbox',
							id: 'mpwpprSearch_isAll',
							name: 'isAll',
							labelSeparator: '',
							boxLabel: lang['napravleniya_po_vsey_mo']
						}]
					}, {
						layout: 'form',
						labelWidth: 190,
						items: [{
							xtype: 'swyesnocombo',
							width: 60,
							fieldLabel: lang['gospitalizatsiya_podtverjdena'],
							id: 'mpwpprEvnDirection_isConfirmed',
							name: 'EvnDirection_isConfirmed',
							listeners:
							{
								'keypress': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					}]
				},
				{
					layout: 'column',
					items: 
					[{
						layout: 'form',
						labelWidth: 55,
						items: 
						[{
							id: 'mpwpprSearch_PrehospStatus_id',
							fieldLabel: lang['status'],
							mode: 'local',
							store: new Ext.data.SimpleStore(
							{
								key: 'PrehospStatus_id',
								fields:
								[
									{name: 'PrehospStatus_id', type: 'int'},
									{name: 'PrehospStatus_Name', type: 'string'}
								],
								data: prehospStatusArray
							}),
							editable: false,
							enableKeyEvents: true,
							width: 170,
							triggerAction: 'all',
							lastQuery: '',
							value: 0,
							displayField: 'PrehospStatus_Name',
							valueField: 'PrehospStatus_id',
							tpl: '<tpl for="."><div class="x-combo-list-item">{PrehospStatus_Name}</div></tpl>',
							hiddenName: 'PrehospStatus_id',
							xtype: 'combo',
							listeners: 
							{
								'keypress': function (inp, e) 
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					},
					{
						layout: 'form',
						items: 
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							id: 'mpwpprBtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function()
							{
								this.doSearch();
							}.createDelegate(this)
						}]
					},
					{
						layout: 'form',
						items: 
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							id: 'mpwpprBtnClear',
							text: lang['sbros'],
							iconCls: 'resetsearch16',
							handler: function()
							{
								this.resetFilter();
							}.createDelegate(this)
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: lang['schitat_s_kartyi'],
								iconCls: 'idcard16',
								handler: function()
								{
									wnd.readFromCard();
								}
							}]
					}]
				}]
			}]
		});
	
		var Actions =
		[
			{name:'active_call', text:langs('Передан активный вызов'), tooltip: langs('Передан активный вызов'), disabled: true, iconCls : 'active-call16', handler: function() { this.setActiveCall(); }.createDelegate(this)},
			{name:'ssmp', text:langs('Талон передан на ССМП'), tooltip: langs('Талон передан на ССМП'), disabled: true, iconCls : 'transmit-ambulance16', handler: function() { this.setTransmitAmbulance(); }.createDelegate(this)},
			{name:'open', text:langs('Редактировать поступление пациента'), tooltip: langs('Редактировать поступление пациента'), iconCls : 'reception-edit16', handler: function() { this.openEvnPSPriemEditWindow({action: 'edit'}); }.createDelegate(this)},
			{name: 'action_selfTreatment', tooltip: langs('Принять пациента без электронного направления или экстренной бирки'),text: langs('Принять без записи'), iconCls : 'reception-self16',handler: function(){form.selfTreatment();}},
			{name:'open_emk', text:langs('Открыть ЭМК'), tooltip: langs('Открыть ЭМК'), iconCls : 'x-btn-text', icon: 'img/icons/open16.png', handler: function() {this.openEmk();}.createDelegate(this)},
			{name:'priem',text:langs('Принять пациента'), tooltip: langs('Принять пациента'), iconCls : 'reception-accept16', handler: function() { this.reception(); }.createDelegate(this)},
			{name:'priem_cancel',	text:langs('Отменить прием '), tooltip: langs('Отменить прием '), disabled: true, hidden: true, iconCls : 'reception-cancel16', handler: function() { this.cancelReception(); }.createDelegate(this)},
			{name:'extdirection', text:langs('Внешнее направление'), tooltip: langs('Внешнее направление'), disabled: false, hidden: getRegionNick() != 'ekb', iconCls : 'add16', handler: function() {form.extDirection();}.createDelegate(this)},
			{name:'evnsection', 	text:langs('На госпитализацию '), tooltip: langs('На госпитализацию '), disabled: true, iconCls : 'hospitalization16', /*menu: new Ext.menu.Menu({id:'ListStacSectionMenu'}),*/ handler: function() { this.setHospitalization(); }.createDelegate(this)},
			{name:'evnsection_del', text:langs('Отменить госпитализацию'), tooltip: langs('Отменить госпитализацию'), disabled: true, hidden: true, iconCls : 'hospitalization-cancel16', handler: function() { this.cancelHospitalization(); }.createDelegate(this)},
			{name:'refusal', 		text:langs('Отказ в госпитализации'), tooltip: langs('Отказ в госпитализации'), disabled: true, iconCls : 'hospitalization-refuse16', menu: new Ext.menu.Menu({id:'ListPrehospWaifRefuseCauseMenu'})/*, handler: function() {return;}.createDelegate(this)*/},
			{name:'urgent_oper',	text: 'Экстренная операция', tooltip: 'Экстренная операция', disabled: true, iconCls: 'urgent_blue16', handler: function() { this.urgentOper(); }.createDelegate(this)},
			{name:'cancel', 		text:lang['otklonit'], tooltip: lang['otklonit'], disabled: true, handler: function() {this.cancelEvnDirection()}.createDelegate(this)},
			{name:'refusal_cancel', text:lang['otmenit_otkaz'], tooltip: lang['otmenit_otkaz'], disabled: true, hidden: true, iconCls : 'hospitalization-refuse-cancel16', handler: function() {this.setPrehospWaifRefuseCause({PrehospWaifRefuseCause_id:null})}.createDelegate(this)},

			{name:'add_evnpl', text:lang['dobavit_sluchay_apl'], tooltip: lang['dobavit_sluchay_apl'], disabled: true, hidden: true, iconCls : 'visit-new16', handler: function() {this.editEvnPL('add')}.createDelegate(this)},
			{name:'edit_evnpl', text:lang['redaktirovat_sluchay_apl'], tooltip: lang['redaktirovat_sluchay_apl'], disabled: true, hidden: true, iconCls : 'patient16', handler: function() {this.editEvnPL('edit')}.createDelegate(this)},

			{name:'print_hosp_refuse', text:lang['spravka_ob_otkaze_v_gospitalizatsii'], tooltip: lang['spravka_ob_otkaze_v_gospitalizatsii'], disabled: true, iconCls : 'print16', handler: function() { this.printHospRefuse(); }.createDelegate(this)},
			{name:'print_patient_refuse', text:lang['zayavlenie_ob_otkaze_ot_gospitalizatsii'], tooltip: lang['zayavlenie_ob_otkaze_ot_gospitalizatsii'], disabled: true, iconCls : 'print16', handler: function() { this.printPatientRefuse(); }.createDelegate(this)},
			{name:'send_to_archive', text:lang['v_arhiv'], tooltip: lang['v_arhiv'], disabled: true, iconCls : 'doc-nak16', handler: function() { this.sendToArchive(); }.createDelegate(this)},
			{name:'refresh', 		text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR, iconCls : 'refresh16', handler: function() { this.listRefresh(); }.createDelegate(this)},

			{name:'print_menu',	text:lang['pechat'], tooltip: lang['pechat'], disabled: false, iconCls : 'print16', menu: //https://redmine.swan.perm.ru/issues/27140
				new Ext.menu.Menu([{
					text: lang['003_u_-_meditsinskaya_karta_bolnogo'],
					handler: function () {
						var r = wnd.getSelectedRecord();
						var EvnPS_id = r.get('EvnPS_id');

						if(Ext.isEmpty(EvnPS_id)){
							alert(lang['patsient_ne_nahoditsya_v_priemnom']);
						}
						else{
							printEvnPS003({
								EvnPS_id: EvnPS_id
							});
						}
					}
				}, {
					text: lang['003-1_u_-_meditsinskaya_karta_preryivaniya_beremennosti'],
					handler: function () {
						var r = wnd.getSelectedRecord();
						var EvnPS_id = r.get('EvnPS_id');

						if(Ext.isEmpty(EvnPS_id)){
							alert(lang['patsient_ne_nahoditsya_v_priemnom']);
						}
						else{
							printBirt({
								'Report_FileName': 'han_EvnPS_Abort.rptdesign',
								'Report_Params': '&paramEvnPS=' + EvnPS_id,
								'Report_Format': 'pdf'
							});
						}
					}
				}, {
					text: lang['097_u_-_istoriya_razvitiya_novorojdennogo'],
					handler: function () {
						var r = wnd.getSelectedRecord();
						var EvnPS_id = r.get('EvnPS_id');

						if(Ext.isEmpty(EvnPS_id)){
							alert(lang['patsient_ne_nahoditsya_v_priemnom']);
						}
						else{
							printBirt({
								'Report_FileName': 'EvnPS_Child.rptdesign',
								'Report_Params': '&paramEvnPS=' + EvnPS_id,
								'Report_Format': 'pdf'
							});
						}
					}
				}, {
					text: lang['096_u_-_istoriya_rodov'],
					handler: function () {
						var r = wnd.getSelectedRecord();
						var EvnPS_id = r.get('EvnPS_id');

						if(Ext.isEmpty(EvnPS_id)){
							alert(lang['patsient_ne_nahoditsya_v_priemnom']);
						}
						else if (getRegionNick() == 'kz') {
							printBirt({
								'Report_FileName': 'EvnPS_Birth.rptdesign',
								'Report_Params': '&paramEvnPS=' + EvnPS_id,
								'Report_Format': 'pdf'
							});
						}
						else{
							printBirt({
								'Report_FileName': 'EvnPS_Birth_2016.rptdesign',
								'Report_Params': '&paramEvnPS=' + EvnPS_id,
								'Report_Format': 'pdf'
							});
						}
					}
				}, {
					text: (getRegionNick() == 'kz' ? lang['066-3_u_-_statkarta_vyibyivshego_iz_psih-statsionara'] : lang['066-1_u_-_statkarta_vyibyivshego_iz_psih_narko-statsionara']),
					handler: function () {
						var r = wnd.getSelectedRecord();
						var EvnPS_id = r.get('EvnPS_id');

						if(Ext.isEmpty(EvnPS_id)){
							alert(lang['patsient_ne_nahoditsya_v_priemnom']);
						}
						else{
							Ext.Ajax.request({ //https://redmine.swan.perm.ru/issues/36513
								url: '/?c=EvnPS&m=getMorbusCrazy',
								callback: function(options, success, response)
								{
									if (success)
									{
										var response_obj = Ext.util.JSON.decode(response.responseText);

										if(response_obj[0]){
											var paramEvnPS = EvnPS_id;
											var paramMorbus = response_obj[0].Morbus_id;
											var paramEvnsection = response_obj[0].EvnSection_id;
											if(getRegionNick() == 'kz'){
												printBirt({
													'Report_FileName': 'han_EvnPS_f066_3u.rptdesign',
													'Report_Params': '&paramEvnPS=' + paramEvnPS + '&paramMorbus=' + paramMorbus + '&paramEvnSection=' + paramEvnsection,
													'Report_Format': 'pdf'
												});
											} else {
												printBirt({
													'Report_FileName': 'hosp_f661u2.rptdesign',
													'Report_Params': '&paramEvnPS=' + paramEvnPS + '&paramMorbus=' + paramMorbus + '&paramEvnSection=' + paramEvnsection,
													'Report_Format': 'pdf'
												});
											}
										}
										else
											alert(lang['otsutstvuet_spetsifika_po_psihiatrii_narkologii']);
									}
								},
								params: {
									EvnPS_id: EvnPS_id
								}
							});
						}
					}
				}, {
					text: lang['066-1_u_-_statkarta_vyibyivshego_iz_narko-statsionara'],
					hidden: (getRegionNick() != 'kz'),
					handler: function () {
						var r = wnd.getSelectedRecord();
						var EvnPS_id = r.get('EvnPS_id');

						if(Ext.isEmpty(EvnPS_id)){
							alert(lang['patsient_ne_nahoditsya_v_priemnom']);
						}
						else{
							Ext.Ajax.request({ //https://redmine.swan.perm.ru/issues/36513
								url: '/?c=EvnPS&m=getMorbusCrazy',
								callback: function(options, success, response)
								{
									if (success)
									{
										var response_obj = Ext.util.JSON.decode(response.responseText);

										if(response_obj[0]){
											var paramEvnPS = EvnPS_id;
											var paramMorbus = response_obj[0].Morbus_id;
											var paramEvnsection = response_obj[0].EvnSection_id;
											printBirt({
												'Report_FileName': 'han_EvnPS_f066_1u.rptdesign',
												'Report_Params': '&paramEvnPS=' + paramEvnPS + '&paramMorbus=' + paramMorbus + '&paramEvnSection=' + paramEvnsection,
												'Report_Format': 'pdf'
											});
										}
										else
											alert(lang['otsutstvuet_spetsifika_po_psihiatrii_narkologii']);
									}
								},
								params: {
									EvnPS_id: EvnPS_id
								}
							});
						}
					}
				}, {
                    text: '066/у' + (getRegionNick() == 'kz' ? '' : '-02') + ' - Статистическая карта выбывшего из стационара',
                    handler: function () {
                        var r = wnd.getSelectedRecord();

                        if ( typeof r != 'object' ) {
                            return false;
                        }

                        var EvnPS_id = r.get('EvnPS_id');

                        if ( Ext.isEmpty(EvnPS_id) ) {
                            sw.swMsg.alert(lang['oshibka'], lang['patsient_ne_nahoditsya_v_priemnom']);
                            return false;
                        }

						var params = {};
						params.EvnPS_id = EvnPS_id;
						params.LpuUnitType_SysNick = 'stac';
						printEvnPS(params);
                    }
                }, {
                    text: '114/у - Сопроводительный лист и талон к нему',
                    handler: function () {
						var record = wnd.getSelectedRecord();

						if (!record) {
							return false;
						}
						if (Ext.isEmpty(record.get('EvnPS_id'))) {
							sw.swMsg.alert(lang['oshibka'], 'Отсутсвует КВС');
							return false;
						}

						printBirt({
							'Report_FileName': 'cmp_f114u.rptdesign',
							'Report_Params': '&paramEvnPS='+record.get('EvnPS_id'),
							'Report_Format': 'pdf'
						});
                    }
                },
                    {
                        text: lang['soglasie_na_med_vmeshatelstvo'],
                        handler: function () {
                            var r = wnd.getSelectedRecord();
                            if ( typeof r != 'object' ) {
                                return false;
                            }
							var paramEvnSection_id = r.get('EvnSection_id')?r.get('EvnSection_id'):'';
							var person_id = r.get('Person_id') || null;
							
                            if (getRegionNick() == 'ekb') {
                            	var paramEvnPS_id = r.get('EvnPS_id') || null;
                            	
								Ext.Ajax.request({
									params: {
										EvnPS_id: paramEvnPS_id
									},
									url: '/?c=EvnPS&m=getFirstProfileEvnSectionId',
									success: function(response){
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (response_obj && response_obj.Error_Msg ) {
											sw.swMsg.alert('Ошибка', 'Ошибка при получении профильного движения');
											return false;
										} else if ( response_obj && !Ext.isEmpty(response_obj[0].EvnSection_id) ) {
											wnd.printPersonSoglasie(response_obj[0].EvnSection_id, person_id);
										}
									}
								});
							} else {
								wnd.printPersonSoglasie(paramEvnSection_id, person_id);
							}
						}
                    },
                    {
                        text: (getRegionNick() == 'kz') ? lang['informirovannoe_soglasie_patsienta_na_provedenie_anestezii'] :lang['soglasie_na_anesteziologicheskoe_obespechenie_med_vmeshatelstva'],
                        handler: function () {
                            var r = wnd.getSelectedRecord();
                            if ( typeof r != 'object' ) {
                                return false;
                            }
                            var paramPerson = r.get('Person_id');
                            var paramEvnSection_id = r.get('EvnSection_id')?r.get('EvnSection_id'):'';
                            var paramLpu = getGlobalOptions().lpu_id;
                            if(getRegionNick() == 'kz'){
		                    	printBirt({
									'Report_FileName': 'Person_soglasie_stac_anst.rptdesign',
									'Report_Params': '&paramPerson=' + paramPerson + '&paramLpu=' + paramLpu + (paramEvnSection_id?('&paramEvnSection=' + paramEvnSection_id):''),
									'Report_Format': 'pdf'
								});
		                    } else {
								printBirt({
									'Report_FileName': 'Person_soglasie_stac_anst.rptdesign',
									'Report_Params': '&paramPerson=' + paramPerson + (paramEvnSection_id?('&paramEvnSection=' + paramEvnSection_id):''),
									'Report_Format': 'pdf'
								});
							}
                        }
                    },
                    {
                        text: (getRegionNick() == 'kz') ? lang['soglasie_na_operativnoe_lechenie'] :lang['soglasie_na_operativnoe_vmeshatelstvo'],
                        handler: function () {
                            var r = wnd.getSelectedRecord();
                            if ( typeof r != 'object' ) {
                                return false;
                            }
                            var paramPerson = r.get('Person_id');
							var paramEvnSection_id = r.get('EvnSection_id')?r.get('EvnSection_id'):'';
                            var paramLpu = getGlobalOptions().lpu_id;
                            if(getRegionNick() == 'kz'){
                            	printBirt({
									'Report_FileName': 'PersonInfoSoglasie_OperStac.rptdesign',
									'Report_Params': '&paramPerson=' + paramPerson + '&paramLpu=' + paramLpu + (paramEvnSection_id?('&paramEvnSection=' + paramEvnSection_id):''),
									'Report_Format': 'pdf'
								});
                            } else {
                            	printBirt({
									'Report_FileName': 'PersonInfoSoglasie_OperStac.rptdesign',
									'Report_Params': '&paramPerson=' + paramPerson + (paramEvnSection_id?('&paramEvnSection=' + paramEvnSection_id):''),
									'Report_Format': 'pdf'
								});
                            }
                        }
                    },
                    {
                        text: lang['otkaz_ot_provedeniya_meditsinskogo_vmeshatelstva'],
                        handler: function () {
                            var r = wnd.getSelectedRecord();
                            if ( typeof r != 'object' ) {
                                return false;
                            }
                            var paramPerson = r.get('Person_id');
                            var paramLpu = getGlobalOptions().lpu_id;
                            var paramEvnSection = ( r.get('EvnSection_id') ) ? '&paramEvnSection=' + r.get('EvnSection_id') : '';
                            if(getRegionNick() == 'kz'){
                            	printBirt({
									'Report_FileName': 'PersonInfoOtkaz.rptdesign',
									'Report_Params': '&paramPerson=' + paramPerson + '&paramLpu=' + paramLpu + paramEvnSection,
									'Report_Format': 'pdf'
								});
                            } else {
								printBirt({
									'Report_FileName': 'PersonInfoOtkaz.rptdesign',
									'Report_Params': '&paramPerson=' + paramPerson + paramEvnSection,
									'Report_Format': 'pdf'
								});
							}
                        }
                    },
					{
						id: 'mpwpprPrintEvnPLRefuse',
						text: lang['tap_otkaza_v_gospitalizatsii'],
						hidden: !(getRegionNick().inlist(['kareliya'])),
						handler: function() {
							var r = wnd.getSelectedRecord();

							if ( typeof r != 'object' ) {
								return false;
							}

							var EvnPS_id = r.get('EvnPS_id');

							if (getPolkaOptions().print_two_side && parseInt(getPolkaOptions().print_two_side) == 2) {
								// двусторонняя Да
								printBirt({
									'Report_FileName': 'f025-1u_all.rptdesign',
									'Report_Params': '&prmFntPnt=1&prmBckPnt=1&s=' + EvnPS_id,
									'Report_Format': 'pdf'
								});
							} else {
								// двусторонняя Нет
								printBirt({
									'Report_FileName': 'f025-1u_all.rptdesign',
									'Report_Params': '&prmFntPnt=1&prmBckPnt=0&s=' + EvnPS_id,
									'Report_Format': 'pdf'
								});
								printBirt({
									'Report_FileName': 'f025-1u_all.rptdesign',
									'Report_Params': '&prmFntPnt=0&prmBckPnt=1&s=' + EvnPS_id,
									'Report_Format': 'pdf'
								});
							}
						}
					},
					{
						text: lang['spravka_o_stoimosti_lecheniya'],
						iconCls: 'print16',
						hidden: ! getRegionNick().inlist(['perm', 'ufa']),
						handler: function() {
							var r = wnd.getSelectedRecord();

							if ( typeof r != 'object' ) {
								return false;
							}

							var EvnPS_id = r.get('EvnPS_id');

							if ( Ext.isEmpty(EvnPS_id) ) {
								sw.swMsg.alert(lang['oshibka'], lang['patsient_ne_nahoditsya_v_priemnom']);
								return false;
							}

							sw.Promed.CostPrint.print({
								Evn_id: EvnPS_id,
								type: 'EvnPS'
							});
						}
					},{
							text: 'Печать шаблона документа',
							iconCls: 'print16',
							hidden: false,
							handler: function() {
								this.printDocTemplate();
							}.createDelegate(this)
					}
					, { text: lang['pechat'], handler: function () {
                            this.printRecs('row');
                        }.createDelegate(this)
                    },
                    { text: lang['pechat_spiska'], handler: function () {
                            this.printRecs('all');
                        }.createDelegate(this)
                    },
					{text: langs('Листок прибытия'), hidden: getRegionNick()=='kz', handler: function () {wnd.printAddressLeaf('arrival');}},
					{text: langs('Листок убытия'), hidden: getRegionNick()=='kz', handler: function () {wnd.printAddressLeaf('departure');}},
					{
						text: langs('Согласие на обработку перс. данных'),
						handler: function () {
							var r = wnd.getSelectedRecord();
							if ( typeof r != 'object' ) {
								return false;
							}
							var paramPerson = r.get('Person_id');
							var paramLpu = getGlobalOptions().lpu_id;

							Ext.Ajax.request({
								url: '/?c=Person&m=savePersonLpuInfo',
								success: function(response){
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj && response_obj.Error_Msg ) {
										sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласие на обработку перс. данных');
										return false;
									} else if ( response_obj && !Ext.isEmpty(response_obj.PersonLpuInfo_id) ) {
										wnd.doSearch(false,function(){
											wnd.MainViewFrame.getGrid().getSelectionModel().selectRow(index);
										});
										if (getRegionNick() == 'kz') {
											var lan = (getAppearanceOptions().language == 'ru' ? 1 : 2);
											printBirt({
												'Report_FileName': 'PersonSoglasie_PersData.rptdesign',
												'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id + '&paramLang=' + lan,
												'Report_Format': 'pdf'
											});
										} else {
											printBirt({
												'Report_FileName': 'PersonSoglasie_PersData.rptdesign',
												'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
												'Report_Format': 'pdf'
											});
										}
									}
								},
								params: {
									Lpu_id: paramLpu,
									Person_id: paramPerson,
									PersonLpuInfo_IsAgree: 2
								}
							});
						}
					},{
						text: langs('Отзыв согласия на обработку перс. данных'),
						handler: function () {
							var r = wnd.getSelectedRecord();
							if ( typeof r != 'object' ) {
								return false;
							}
							var paramPerson = r.get('Person_id');
							var paramLpu = getGlobalOptions().lpu_id;

							Ext.Ajax.request({
								url: '/?c=Person&m=savePersonLpuInfo',
								success: function(response){
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj && response_obj.Error_Msg ) {
										sw.swMsg.alert('Ошибка', 'Ошибка при сохранении Отзыва согласия на обработку перс. данных');
										return false;
									} else if ( response_obj && !Ext.isEmpty(response_obj.PersonLpuInfo_id) ) {
										wnd.doSearch(false,function(){
											wnd.MainViewFrame.getGrid().getSelectionModel().selectRow(index);
										});
										printBirt({
											'Report_FileName': 'PersonOtkaz_PersData.rptdesign',
											'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
											'Report_Format': 'pdf'
										});
									}
								},
								params: {
									Lpu_id: paramLpu,
									Person_id: paramPerson,
									PersonLpuInfo_IsAgree: 1
								}
							});
						}
					},{
							text: langs('Согласие пациента на операцию переливания компонентов крови'),
							hidden: getRegionNick() != 'msk',
							handler: function () {
								var r = wnd.getSelectedRecord(),
									Person_id = r.get('Person_id');

								if (Ext.isEmpty(Person_id)) {
									sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_patsient']);
								} else {
									printBirt({
										'Report_FileName': 'ReportTransfusion.rptdesign',
										'Report_Params': '&paramPerson=' + Person_id,
										'Report_Format': 'pdf'
									});
								}
							}
					}, {
					text: 'Информированное добровольное согласие на госпитализацию',
					hidden: getRegionNick() != 'msk',
					handler: function() {
						var record = wnd.getSelectedRecord();
						if ( typeof record != 'object' ) {
							return false;
						}
						Ext.Msg.show({
							title:'Информированное добровольное согласие на госпитализацию',
							msg: 'Выберите вариант печати Информированного добровольного согласия на госпитализацию.',
							buttons: {
								yes: 'Лично',
								no: 'Представитель',
								cancel: 'Отмена'
							},
							fn: function(btn) {
								var EvnPS_id = record.get('EvnPS_id');
								var paramDeputy = null;
								if ( btn != 'cancel' ) {
									if ( btn == 'yes' ) {
										paramDeputy = 1;
									}
									if ( btn == 'no' ) {
										paramDeputy = 2;
									}
									printBirt({
										'Report_FileName': 'HospInfoConsent.rptdesign',
										'Report_Params': '&paramEvnPS_id=' + EvnPS_id + '&paramDeputy=' + paramDeputy,
										'Report_Format': 'pdf'
									});
								}
							},
							icon: Ext.Msg.QUESTION,
						});
					},
					},{
					text: langs('Согласие на получение плановой МП'),
					iconCls: 'print16',
					hidden: getRegionNick() != 'msk',
					handler: function () {
						var win = Ext.getCmp('swMPWorkPlacePriemWindow'),
							record = wnd.getSelectedRecord(),
							Person_id = record.get('Person_id'),
							MedStaffFact_id = win.userMedStaffFact.MedStaffFact_id;
							
						if (Ext.isEmpty(Person_id)) {
							sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_patsient']);
						} else if (Ext.isEmpty(MedStaffFact_id)) {
							MedStaffFact_id = null;
						} else {
							printBirt({
								'Report_FileName': 'r50_prn_HospSoglCOVID.rptdesign',
								'Report_Params': '&Person_id=' + Person_id + '&MedStaffFact_id=' + MedStaffFact_id,
								'Report_Format': 'pdf'
							});
						}
					}
				},{
					text: lang['talon_ambulatornogo_patsienta'],
					iconCls: 'print16',
					hidden: ! getRegionNick().inlist(['msk']),
					handler: function() {
						var r = wnd.getSelectedRecord();

						if ( typeof r != 'object' ) {
							return false;
						}

						var EvnPL_id = r.get('EvnPL_id');

						if ( Ext.isEmpty(EvnPL_id) ) {
							sw.swMsg.alert(lang['oshibka'], lang['ne_sformirovan_tap']);
							return false;
						}

						printEvnPL({
							type: 'EvnPL',
							EvnPL_id: EvnPL_id
						});
					}
				}])
			},
			{name:'print_barcode', text:langs('Печать браслета'), tooltip: langs('Печать браслета'),  iconCls : 'print16', hidden: ['ufa', 'buryatiya'].indexOf(getRegionNick()) < 0, disabled: true, handler: function() { this.printBand(); }.createDelegate(this) },
			{name:'move_reanimation', text: langs('Перевод в реанимацию'), tooltip: langs('Перевод в реанимацию'), iconCls: 'ambulance16', handler: this.moveToReanimation.createDelegate(this) },     //BOB - 15.11.2017

		];
		this.gridActions = new Array();
		
		for (i=0; i < Actions.length; i++)
		{
			this.gridActions[Actions[i]['name']] = new Ext.Action(Actions[i]);
		}
		delete(Actions);
		
		// Создание popup - меню и кнопок в ToolBar. Формирование коллекции акшенов
		this.ViewContextMenu = new Ext.menu.Menu();
		this.toolItems = new Ext.util.MixedCollection(true);
		var i = 0;
		for (key in this.gridActions)
		{
			if (key.inlist(['open_emk','action_selfTreatment','open','priem','priem_cancel','evnsection','evnsection_del','refusal','urgent_oper','cancel','print_menu','refusal_cancel','ssmp','active_call','refresh','print_hosp_refuse','print_patient_refuse','send_to_archive','move_reanimation','print_barcode']))
			{
				//evnsection
				this.toolItems.add(this.gridActions[key],key);
				if ((i == 1) || (i == 8) || (i == 9))
					this.ViewContextMenu.add('-');
				this.ViewContextMenu.add(this.gridActions[key]);
				i++;
			}
		}
		
		this.gridToolbar = new Ext.Toolbar(
		{
			id: 'mpwpprToolbar',
			items:
			[
				this.gridActions.open_emk,
				{
					xtype : "tbseparator"
				},
				this.gridActions.action_selfTreatment,
				{
					xtype : "tbseparator"
				},
				this.gridActions.priem,
				this.gridActions.priem_cancel,
				this.gridActions.extdirection,
				{
					xtype : "tbseparator"
				},
				this.gridActions.evnsection,
				this.gridActions.evnsection_del,
				{
					xtype : "tbseparator"
				},
				this.gridActions.refusal,
				this.gridActions.urgent_oper,
				this.gridActions.cancel,
				this.gridActions.print_menu,
				this.gridActions.refusal_cancel,
				this.gridActions.add_evnpl,
				this.gridActions.edit_evnpl,
				this.gridActions.print_barcode,
				{
					xtype : "tbseparator"
				},
				this.gridActions.refresh,
				{
					xtype : "tbseparator"
				},
				this.gridActions.move_reanimation,    //BOB - 15.11.2017    
				{
					xtype : "tbseparator"
				},
				//this.gridActions.print,
				{
					xtype : "tbfill"
				},
				{
					xtype : "tbseparator"
				},
				{
					text: '0 / 0',
					xtype: 'tbtext'
				}
			]
		});			

		this.reader = new Ext.data.JsonReader(
		{id: 'keyNote'},
		[
		{name: 'sortDate', type: 'int'},
		{name: 'groupField'},
		{name: 'EvnDirection_id'},
		{name: 'EvnDirection_IsConfirmed'},
		{name: 'PersonLpuInfo_IsAgree'},
		{name: 'Direction_exists'},
		{name: 'SMMP_exists'},
		{name: 'EvnPS_CodeConv'},
		{name: 'EvnPS_NumConv'},
		{name: 'TimetableStac_insDT'},
		{name:'EvnDirection_Num'},
		{name:'EvnDirection_setDate'},
		{name:'Diag_did'},
		{name:'LpuSection_did'},
		{name:'Lpu_did'},
		{name:'Org_did'},
		{name:'DirType_id'},
		{name:'TimetableStac_setDate', type: 'string'},
		{name:'EvnQueue_setDate'},
		{name: 'EvnPS_id'},
		{name: 'PayType_id'},
		{name: 'IsHospitalized'},
		{name: 'EvnPS_setDT',type: 'date',dateFormat: 'd.m.Y H:i'},
		{name: 'EvnPS_setDT_Diff'},
		{name: 'LpuSectionProfile_Name'},
		{name: 'LpuSectionProfile_did'},
		{name: 'TimetableStac_id'},
		{name: 'PrehospStatus_id'},
		{name: 'PrehospStatus_Name'},
		{name: 'EvnQueue_id'},
		{name: 'Person_id'},
		{name: 'PersonEvn_id'},
		{name: 'Server_id'},
		{name: 'Person_Fio'},
		{name: 'Person_age'},
		{name: 'Person_IsBDZ'},
		{name: 'Person_BirthDay',type: 'date',dateFormat: 'd.m.Y'},
		{name: 'Diag_id'},
		{name: 'Diag_CodeName'},
		{name: 'IsRefusal'},// признак наличия отказа
		{name: 'PrehospWaifRefuseCause_id'},
		{name: 'IsCall'},// Передан активный вызов 
		{name: 'IsSmmp'},// Талон передан на ССМП
		{name: 'PrehospArrive_id'}, // Кем доставлен (идентификатор)
		{name: 'PrehospArrive_SysNick'}, // Кем доставлен (системное наименование),
		{name: 'PrehospArrive_Name'}, // Кем доставлен (наименование в системе)
		{name: 'PrehospType_id'}, // Тип госпитализации (идентификатор)
		{name: 'PrehospType_SysNick'}, // Тип госпитализации (системное наименование)
		{name: 'PrehospDirection_Name'}, // Кем направлен (название отделения или организации)
		{name: 'pmUser_Name'},
		{name: 'LpuSection_Name'} // отделение (куда госпитализирован)
		,
		{name: 'PrivilegeType_Name'},
		{name: 'SocStatus_Name'},
		{name: 'PersonEncrypHIV_Encryp'},
		{name: 'EvnPL_id'},
		{name: 'EvnPL_NumCard'},
		{name: 'EvnPS_OutcomeDT',type: 'date',dateFormat: 'd.m.Y H:i'},
		{name: 'EvnPS_NumCard'},
		{name: 'FaceAsymetry_Name'},
		{name: 'HandHold_Name'},
		{name: 'SqueezingBrush_Name'},
		{name: 'ScaleLams_id'},
		{name: 'ScaleLams_Value'},
		{name: 'PainResponse_Name'},
		{name: 'ExternalRespirationType_Name'},
		{name: 'SystolicBloodPressure_Name'},
		{name: 'InternalBleedingSigns_Name'},
		{name: 'LimbsSeparation_Name'},
		{name: 'PrehospTraumaScale_Value'},
		{name: 'CmpCallCard_id'},
		{name: 'EvnSection_id'},
		{name: 'PersonQuarantine_IsOn'},
		{name: 'PainDT', type: 'date', dateFormat: 'Y-m-d H:i', convert: Ext.util.Format.dateRenderer('d-m-Y H:i')},
		{name: 'ECGDT', type: 'date', dateFormat: 'Y-m-d H:i', convert: Ext.util.Format.dateRenderer('d-m-Y H:i')},
		{name: 'TLTDT', type: 'date', dateFormat: 'Y-m-d H:i', convert: Ext.util.Format.dateRenderer('d-m-Y H:i')},
		{name: 'ResultECG'},
		{name: 'FailTLT'}
		]);

		this.gridStore = new Ext.data.GroupingStore(
		{
			reader: this.reader,
			autoLoad: false,
			url: '/?c=EvnPS&m=loadWorkPlacePriem',
			sortInfo: 
			{
				field: 'sortDate',
				direction: 'DESC'
			},
			groupField: 'groupField',
			listeners:
			{
				load: function(store, record, options)
				{

					// #156577 звуковое оповещение для поступивших из смп
					if(getRegionNick() == 'ufa') {
						Ext.each(record, function(rec) {
							if(rec.get('SMMP_exists')) {

								var stac_id = rec.get('TimetableStac_id');

								if ( stac_id && !wnd.TimetableStacList.includes( stac_id ) ) {
									Ext.get(wnd.id+'_ring').dom.play();
									wnd.TimetableStacList.push( stac_id );
								}
							}
						});
					};

					callback:
					{
						var count = store.getCount();
						var form = Ext.getCmp('swMPWorkPlacePriemWindow');
						var grid = form.getGrid();
						grid.lastLoadGridDate = new Date();
						if(grid.auto_refresh)
						{
							clearInterval(grid.auto_refresh);
						}
						grid.auto_refresh = setInterval(function(){
							var cur_date = new Date();
							// если прошло более 5 минут с момента последнего обновления
							if(grid.lastLoadGridDate.getTime() < (cur_date.getTime()-300))
							{
								form.listRefresh();
							}
						}.createDelegate(grid),300000);
						if (count>0)
						{
							// Если ставится фокус при первом чтении или количество чтений больше 0
							if (!grid.getTopToolbar().hidden)
							{
								grid.getTopToolbar().items.last().el.innerHTML = '0 / '+count;
							}
							form.gridActions.open_emk.setDisabled(true);
							form.gridActions.priem.setDisabled(true);
							form.gridActions.priem_cancel.setDisabled(true);
							form.gridActions.evnsection.setDisabled(true);
							form.gridActions.evnsection_del.setDisabled(true);
							form.gridActions.refusal.setDisabled(true);
							form.gridActions.urgent_oper.setDisabled(true);
							form.gridActions.cancel.setDisabled(true);
							form.gridActions.refusal_cancel.setDisabled(true);
							form.gridActions.add_evnpl.setDisabled(true);
							form.gridActions.edit_evnpl.setDisabled(true);
							form.gridActions.print_barcode.setDisabled(true);
						}
						else
						{
							grid.focus();
						}
					}
				},
				clear: function()
				{
					var form = Ext.getCmp('swMPWorkPlacePriemWindow');
					form.gridActions.open_emk.setDisabled(true);
					form.gridActions.priem.setDisabled(true);
					form.gridActions.priem_cancel.setDisabled(true);
					form.gridActions.evnsection.setDisabled(true);
					form.gridActions.evnsection_del.setDisabled(true);
					form.gridActions.refusal.setDisabled(true);
					form.gridActions.urgent_oper.setDisabled(true);
					form.gridActions.cancel.setDisabled(true);
					form.gridActions.refusal_cancel.setDisabled(true);
					form.gridActions.add_evnpl.setDisabled(true);
					form.gridActions.edit_evnpl.setDisabled(true);

					var print_menu = form.gridActions.print_menu.initialConfig.menu;
					print_menu.items.itemAt(14).setDisabled(true);	//Листок прибытия
					print_menu.items.itemAt(15).setDisabled(true);	//Листок убытия
					print_menu.items.itemAt(23).setDisabled(true);
					
					form.gridActions.print_barcode.setDisabled(true);
				},
				beforeload: function()
				{

				}
			}
		});

		this.mainGrid = new Ext.grid.GridPanel(
		{
			region: 'center',
			layout: 'fit',
			frame: true,
			tbar: this.gridToolbar,
			store: this.gridStore,
			loadMask: true,
			stripeRows: true,
			autoExpandColumn: 'autoexpand',
			columns: 
			[
			{hidden: true,hideable: false, dataIndex: 'keyNote'},
			{hidden: true,hideable: false, dataIndex: 'sortDate'},
			{header: lang['status'],hidden: true,hideable: false, dataIndex: 'groupField'},
			{hidden: true,hideable: false, dataIndex:'PrehospStatus_id'},
			{hidden: true,hideable: false, dataIndex:'PrehospStatus_Name'},
			{hidden: true,hideable: false, dataIndex:'EvnPS_id'},
			{hidden: true,hideable: false, dataIndex:'EvnQueue_id'},
			{hidden: true,hideable: false, dataIndex:'PayType_id'},
			{header: langs('Подтверждение'),width: 70, sortable: true, dataIndex:'EvnDirection_IsConfirmed',renderer: sw.Promed.Format.checkColumn},
			{hidden: !getRegionNick().inlist(['vologda','pskov', 'krasnoyarsk', 'khak']), header: '№ ТАП', width: 40, sortable: true, dataIndex: 'EvnPL_NumCard'},
			{header: langs('Согласие'),width: 24, sortable: true, dataIndex:'PersonLpuInfo_IsAgree',renderer: function(v, p, r) {
					if (v == '1') {
						return '<img src="../img/grid/deleted-card.png">';
					} else if (v == '2'){
						return '<img src="../img/grid/checkednonborder.gif">';
					}
					return '';
				}},
			{header: langs('Поступил'),width: 33, sortable: true,dataIndex:'EvnPS_setDT', renderer: function(value, metaData, record) {
					var dateDiff = record.get('EvnPS_setDT_Diff');
					var prehospStatus = record.get('PrehospStatus_id');
					var color = prehospStatus == 3
						&& dateDiff <= -1440
						&& getGlobalOptions().region
						&& getGlobalOptions().region.nick.inlist(['msk', 'ufa'])
						? '#ff1111' : '#000079';
					return '<span style="color: '+ color +'">' + Ext.util.Format.date(value, 'd.m.Y H:i') + '</span>';
				}},
			{header: langs('ФИО'), width: 70, sortable: true, dataIndex:'Person_Fio'},
			{header: "Дата рождения",width: 24,sortable: true,dataIndex:'Person_BirthDay',renderer: Ext.util.Format.dateRenderer('d.m.Y'), css: 'color: #000079;'},
			{header: langs('Возраст'),width:7,sortable: true, dataIndex:'Person_age'},
			{header: langs('БДЗ'), width: 40, sortable: false, dataIndex: 'Person_IsBDZ', renderer: sw.Promed.Format.checkColumn, hidden: getRegionNick() != 'kz'},
			{hidden: false,sortable: false,header: langs('Запись'),width: 50,sortable: true,dataIndex:'SMMP_exists',renderer: sw.Promed.Format.recordColumn},
			{hidden: true,hideable: false, dataIndex:'EvnPS_CodeConv'},
			{hidden: true,hideable: false, dataIndex:'EvnPS_NumConv'},
			{hidden: true,hideable: false, dataIndex:'TimetableStac_insDT'},
			{hidden: false,header: langs('Профиль'), width: 50, sortable: true, dataIndex:'LpuSectionProfile_Name'},
			{header: langs('Направление'),width: 100,sortable: true, dataIndex:'Direction_exists',renderer: sw.Promed.Format.dirNumColumn},
			{header: langs('Отделение'), width: 40, sortable: true, dataIndex:'LpuSection_Name' },
			{header: langs('Номер КВС'), width: 40, sortable: true, dataIndex:'EvnPS_NumCard', hidden: getRegionNick()!='perm'},
			{header: langs('Кем направлен'), width: 80, sortable: true, dataIndex:'PrehospDirection_Name', hidden: getRegionNick()!='msk' },
			{header: langs('Кем доставлен'), width: 80, sortable: true, dataIndex:'PrehospArrive_Name', hidden: getRegionNick()!='msk' },
				
			{header: "Последний диагноз", sortable: true, id:'autoexpand', dataIndex:'Diag_CodeName', checked: false},
			{header: langs('ОКС'), sortable: true, dataIndex: 'ResultECG', hidden: getRegionNick() != 'ufa',
				renderer: function(value) {
					return value
						? '<u style="color:blue;" onClick="swMPWorkPlacePriemWindow.showPopup(\'oks\')">'+value+'</u>'
						: '';
				}
			},
			{header: langs('Шкала оценки тяжести'), tooltip:langs('Шкала оценки тяжести'), sortable: true, width: 20, dataIndex: 'PrehospTraumaScale_Value',  hidden: getRegionNick() != 'ufa',
				renderer: function(value) {
					return value
						? '<u style="color:blue;" onClick="swMPWorkPlacePriemWindow.showPopup(\'trauma\')">'+value+'</u>'
						: '';
				}
			},
			{header: langs('Шкала LAMS'), tooltip:langs('Шкала LAMS'), sortable: true, width: 20, dataIndex: 'ScaleLams_Value', hidden: getRegionNick() != 'ufa',
				renderer: function(value) {
					return value
						? '<u style="color:blue;" onClick="swMPWorkPlacePriemWindow.showPopup(\'lams\')">'+value+'</u>'
						: '';
				}
			},
			{hidden: true,hideable: false, dataIndex:'Diag_id'},
			{hidden: true,hideable: false, dataIndex:'EvnDirection_Num'},
			{hidden: true,hideable: false, dataIndex:'EvnDirection_setDate'},
			{hidden: true,hideable: false, dataIndex:'Diag_did'},
			{hidden: true,hideable: false, dataIndex:'LpuSection_did'},
			{hidden: true,hideable: false, dataIndex:'LpuSection_dName'},
			{hidden: true,hideable: false, dataIndex:'Lpu_did'},
			{hidden: true,hideable: false, dataIndex:'Lpu_dName'},
			{hidden: true,hideable: false, dataIndex:'Org_did'},
			{hidden: true,hideable: false, dataIndex:'Org_dName'},
			{hidden: true,hideable: false, dataIndex:'DirType_id'},
			{hidden: true,hideable: false, dataIndex:'TimetableStac_setDate'},
			{hidden: true,hideable: false, dataIndex:'EvnQueue_setDate'},
			{hidden: true,hideable: false, dataIndex:'IsHospitalized'},
			{hidden: true,hideable: false, dataIndex:'Person_id'},
			{hidden: true,hideable: false, dataIndex:'PersonEvn_id'},
			{hidden: true,hideable: false, dataIndex:'Server_id'},
			{hidden: true,hideable: false, dataIndex:'EvnDirection_id'},
			{hidden: true,hideable: false, dataIndex:'TimetableStac_id'},
			{hidden: true,hideable: false, dataIndex:'LpuSectionProfile_did'},
			{hidden: true,hideable: false, dataIndex:'IsRefusal'},
			{hidden: true,hideable: false, dataIndex:'PrehospWaifRefuseCause_id'},
			{hidden: true,hideable: false, dataIndex:'IsCall'},
			{hidden: true,hideable: false, dataIndex:'IsSmmp'},
			{hidden: true,header: "Дочерний элемент",hideable: false, dataIndex:'childElement'},
			{hidden: true,hideable: false, dataIndex:'EvnSection_id'},
			{hidden: true,hideable: false, dataIndex:'PrehospArrive_id'},
			{hidden: true,hideable: false, dataIndex:'PrehospArrive_SysNick'},
			{hidden: true,hideable: false, dataIndex:'PrehospArrive_Name'}, 
			{hidden: true,hideable: false, dataIndex:'PrehospDirection_Name'},
			{hidden: true,hideable: false, dataIndex:'PrehospType_id'},
			{hidden: true,hideable: false, dataIndex:'PrehospType_SysNick'},
			{hidden: false,header: langs('Оператор'),width: 50,sortable: true,dataIndex:'pmUser_Name'},
			{header: "Тип льготы", width: 40, sortable: true, dataIndex:'PrivilegeType_Name'},
			{header: "Социальный Статус", width: 40, sortable: true, dataIndex:'SocStatus_Name'},
			{hidden: true,hideable: false, dataIndex:'EvnPL_id'},
			{hidden: true,hideable: false, dataIndex:'EvnPS_OutcomeDT'},
			{hidden: true,hideable: false, dataIndex:'EvnPS_NumCard'},
			{hidden: true,hideable: false, dataIndex:'PersonQuarantine_IsOn'}
			],
			
			view: new Ext.grid.GroupingView(
			{
				forceFit: true,
                enableGroupingMenu : false,
                enableNoGroups : false,
                hideGroupedColumn : true, 
				groupTextTpl: '<span style="color: rgb(113,0,0); font-size: 14px;">{[values.rs[0].data.PrehospStatus_Name]}</span> ({[values.rs.length]} {[values.rs.length == 1 ? "запись" : (values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})',
				getRowClass: function (row, index) {
					var cls = '';
					
					//log('test test test', row.get('PersonQuarantine_IsOn'));

					if (row.get('PersonQuarantine_IsOn') == 2) {
						cls = cls + 'x-grid-rowbackred ';
					}

					return cls;
				}
			}),
			lastLoadGridDate: null,
			auto_refresh: null,
			loadStore: function(params,callback)
			{
				if (!this.params)
					this.params = null;
				if (params)
				{
					this.params = params;
				}
				if(typeof callback != 'function')
				{
					callback =  Ext.emptyFn;
				}
				this.clearStore();
				this.getStore().load({params: this.params, callback: callback});
			},
			clearStore: function()
			{
				if (this.getEl())
				{
					if (this.getTopToolbar().items.last())
						this.getTopToolbar().items.last().el.innerHTML = '0 / 0';
					this.getStore().removeAll();
				}
			},
			focus: function () 
			{
				if (this.getStore().getCount()>0)
				{
					this.getView().focusRow(0);
					this.getSelectionModel().selectFirstRow();
				}
			},
			hasPersonData: function()
			{
				return this.getStore().fields.containsKey('Person_id') && this.getStore().fields.containsKey('Server_id');
			},
			sm: new Ext.grid.RowSelectionModel(
			{
				singleSelect: true,
				listeners:
				{
					'rowselect': function(sm, rowIdx, record)
					{
						if ( !record ) {
							return false;
						}

						var form = Ext.getCmp('swMPWorkPlacePriemWindow');
						var count = this.grid.getStore().getCount();
						var rowNum = rowIdx + 1;
						if (!this.grid.getTopToolbar().hidden)
						{
							this.grid.getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
						}
						
						if (!form.gridActions.open_emk.initialConfig.initialDisabled)
							form.gridActions.open_emk.setDisabled(false);
						
						if (!form.gridActions.print_hosp_refuse.initialConfig.initialDisabled)
							form.gridActions.print_hosp_refuse.setDisabled(record.get('IsRefusal') == 1);
						if (!form.gridActions.print_patient_refuse.initialConfig.initialDisabled)
							form.gridActions.print_patient_refuse.setDisabled(record.get('PrehospWaifRefuseCause_id') != 2);

						if (!form.gridActions.send_to_archive.initialConfig.initialDisabled)
							form.gridActions.send_to_archive.setDisabled(record.id.indexOf('EvnQueue') < 0);

						var print_menu = form.gridActions.print_menu.initialConfig.menu;
						print_menu.items.itemAt(6).setDisabled(Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));
						print_menu.items.itemAt(7).setDisabled(Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));
						print_menu.items.itemAt(8).setDisabled(Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));
						print_menu.items.itemAt(9).setDisabled(Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));
						print_menu.items.itemAt(14).setDisabled(Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));	//Листок прибытия
						print_menu.items.itemAt(15).setDisabled(Ext.isEmpty(record.get('Person_id')) || !Ext.isEmpty(record.get('PersonEncrypHIV_Encryp')));	//Листок убытия
						print_menu.items.itemAt(23).setDisabled(Ext.isEmpty(record.get('Person_id')) || Ext.isEmpty(record.get('EvnPL_id')));
						
						if (!form.readOnly)
						{

							form.gridActions.priem.setHidden(!Ext.isEmpty(record.get('EvnPS_id')));
							form.gridActions.cancel.setHidden(!Ext.isEmpty(record.get('EvnPS_id')));
							form.gridActions.refusal.setHidden(record.get('IsRefusal') == 2 || record.get('PrehospStatus_id') == 2);
						
							form.gridActions.refusal_cancel.setHidden(record.get('IsRefusal') == 1 || record.get('PrehospStatus_id') == 2);
						
							form.gridActions.add_evnpl.setHidden(true);
							form.gridActions.edit_evnpl.setHidden(true);
							
							form.gridActions.priem_cancel.setHidden(Ext.isEmpty(record.get('EvnPS_id')) || record.get('PrehospStatus_id') != 3);
							
							form.gridActions.evnsection.setHidden(record.get('IsHospitalized') == 2 || record.get('PrehospStatus_id') == 2);
							form.gridActions.evnsection_del.setHidden(record.get('IsHospitalized') == 1 || record.get('PrehospStatus_id') == 2);

							form.gridActions.priem.setDisabled(true);
							form.gridActions.cancel.setDisabled(true);
							form.gridActions.priem_cancel.setDisabled(true);

							form.gridActions.refusal.setDisabled(true);
							form.gridActions.urgent_oper.setDisabled(true);
							form.gridActions.refusal_cancel.setDisabled(true);
							
							form.gridActions.add_evnpl.setDisabled(true);
							form.gridActions.edit_evnpl.setDisabled(true);
								
							form.gridActions.evnsection.setDisabled(true);
							form.gridActions.evnsection_del.setDisabled(true);
							
							form.gridActions.active_call.setDisabled(true);
							form.gridActions.ssmp.setDisabled(true);

							form.gridActions.print_barcode.setDisabled(true);

							if ( Ext.isEmpty(record.get('EvnPS_id')) )
							{
								form.gridActions.open.setDisabled(true);
									
								if (!form.gridActions.priem.initialConfig.initialDisabled)
									form.gridActions.priem.setDisabled(false);
								if (!form.gridActions.cancel.initialConfig.initialDisabled)
									form.gridActions.cancel.setDisabled(false);
							}
							else
							{
								if (!form.gridActions.urgent_oper.initialConfig.initialDisabled)
									form.gridActions.urgent_oper.setDisabled(false);

								if (!form.gridActions.open.initialConfig.initialDisabled)
									form.gridActions.open.setDisabled(false);

								if (!form.gridActions.priem_cancel.initialConfig.initialDisabled && Ext.isEmpty(record.get('childElement')))
									form.gridActions.priem_cancel.setDisabled(record.get('IsHospitalized') == 2);

								if(!form.gridActions.print_barcode.initialConfig.initialDisabled)
									form.gridActions.print_barcode.setDisabled(false);

								if(record.get('IsHospitalized') == 1)
								{
									if (!form.gridActions.refusal.initialConfig.initialDisabled && Ext.isEmpty(record.get('childElement')))
										form.gridActions.refusal.setDisabled(false);
									if (!form.gridActions.refusal_cancel.initialConfig.initialDisabled)
										form.gridActions.refusal_cancel.setDisabled(false);
								}
								
								if ( record.get('IsRefusal') == 1 )
								{
									if (!form.gridActions.evnsection.initialConfig.initialDisabled)
										form.gridActions.evnsection.setDisabled(false);
									if (!form.gridActions.evnsection_del.initialConfig.initialDisabled && Ext.isEmpty(record.get('childElement')))
										form.gridActions.evnsection_del.setDisabled(false);
								}
								
								if ( record.get('IsRefusal') == 2 &&
										(getRegionNick().inlist(['ufa','ekb','krasnoyarsk','pskov']) ||
										(getRegionNick().inlist(['vologda', 'khak']) && record.get('PrehospWaifRefuseCause_id').inlist([1,2,3,6])) ||
										(getRegionNick() == 'msk' && record.get('PrehospWaifRefuseCause_id').inlist([1,2,3,4,5,6,7,8,9,16,17])))
								)
								{
									if (record.get('EvnPL_id') > 0) {
										form.gridActions.edit_evnpl.setHidden(false);
										form.gridActions.edit_evnpl.setDisabled(false);
									} else {
										form.gridActions.add_evnpl.setHidden(false);
										form.gridActions.add_evnpl.setDisabled(false);
									}
								}
								
								//active_call (доступно, если заведен отказ и Передан активный вызов = Нет),
								if ( record.get('IsRefusal') == 2 && record.get('IsCall') == 1 )
								{
									if (!form.gridActions.active_call.initialConfig.initialDisabled)
										form.gridActions.active_call.setDisabled(false);
								}
								//ssmp(доступно,  если Кем доставлен  = «2. Скорая помощь» и «Талон передан на ССМП»=Нет).
								if ( record.get('PrehospArrive_id') == 2 && record.get('IsSmmp') == 1 )
								{
									if (!form.gridActions.ssmp.initialConfig.initialDisabled)
										form.gridActions.ssmp.setDisabled(false);
								}

								if (getRegionNick() == 'kareliya') {
									Ext.getCmp('mpwpprPrintEvnPLRefuse').setDisabled(record.get('IsRefusal') != 2);
								}
							}
							
							form.setMenu(false);
						}
					}
				}
			})
		});
		
		// Добавляем созданное popup-меню к гриду
		
		this.mainGrid.addListener('rowcontextmenu', onMessageContextMenu,this);
		this.mainGrid.on('rowcontextmenu', function(grid, rowIndex, event)
		{
			// На правый клик переходим на выделяемую запись
			grid.getSelectionModel().selectRow(rowIndex);
		});
		// Функция вывода меню по клику правой клавиши
		function onMessageContextMenu(grid, rowIndex, e)
		{
			e.stopEvent();
			var coords = e.getXY();
			this.ViewContextMenu.showAt([coords[0], coords[1]]);
		}
		// Даблклик
		this.mainGrid.on('celldblclick', function(grid, row, col, object)
		{
			var win = Ext.getCmp('swMPWorkPlacePriemWindow');
			var rec = win.getSelectedRecord();
			if (!rec)
			{
				return false;
			}
			if (rec.get('EvnPS_id'))
			{
				win.openEvnPSPriemEditWindow({action: 'edit'});
			}
			else
			{
				win.reception();
			}
		});
		
		this.mainGrid.on('cellclick', function(grid, row, col, object)
		{
			var rec = grid.getSelectionModel().getSelected();
			var fieldName = grid.getColumnModel().getDataIndex(col);
			if (fieldName == 'Direction_exists' && rec.data.Direction_exists)
			{
				// Клик на иконку направления
				if (rec.get('EvnDirection_id'))
				{
					getWnd('swEvnDirectionEditWindow').show({
						Person_id: rec.get('Person_id'),
						EvnDirection_id: rec.get('EvnDirection_id'),
						action: 'view',
						formParams: new Object()
					});
				}
			}
		});



		this.SvidActions = {
				New_SvidBirth: {
					tooltip: lang['o_rojdenii'],
					text: lang['o_rojdenii'],
					//iconCls : 'epl-ddisp-new16',
					hidden: getRegionNick() == 'kz',
					disabled: false, 
					handler: function() 
					{
						if (getWnd('swPersonSearchWindow').isVisible()) {
							Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
							return false;
						}
						getWnd('swPersonSearchWindow').show({
							onSelect: function(pdata) {
								getWnd('swPersonSearchWindow').hide();
								getWnd('swMedSvidBirthEditWindow').show({
									action: 'add',
									formParams: {
										Person_id: pdata.Person_id,
										Server_id: pdata.Server_id
									}
								});
							},
							searchMode: 'all'
						});
					}
				},
				New_SvidDeath: {
					tooltip: lang['o_smerti'],
					text: lang['o_smerti'],
					//iconCls : 'epl-ddisp-new16',
					disabled: false, 
					handler: function() 
					{
						if (getWnd('swPersonSearchWindow').isVisible()) {
							Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
							return false;
						}
						getWnd('swPersonSearchWindow').show({
							onSelect: function(pdata) {
								getWnd('swPersonSearchWindow').hide();
								getWnd('swMedSvidDeathEditWindow').show({
									action: 'add',
									formParams: {
										Person_id: pdata.Person_id,
										Server_id: pdata.Server_id
									}
								});
							},
							searchMode: 'all'
						});
					}
				},
				New_SvidPntDeath: {
					tooltip: lang['o_perinatalnoy_smerti'],
					text: lang['o_perinatalnoy_smerti'],
					//iconCls : 'epl-ddisp-new16',
					disabled: false, 
					handler: function() 
					{
						if (getWnd('swPersonSearchWindow').isVisible()) {
							Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
							return false;
						}
						getWnd('swPersonSearchWindow').show({
							onSelect: function(pdata) {
								getWnd('swPersonSearchWindow').hide();
								getWnd('swMedSvidPntDeathEditWindow').show({
									action: 'add',
									formParams: {
										Person_id: pdata.Person_id,
										Server_id: pdata.Server_id
									}
								});
							},
							searchMode: 'all'
						});
					}
				}
		}
		

		var form = this;
		// Формирование списка всех акшенов 
		var configActions = 
		{
			/*action_selfTreatment: 
			{
				nn: 'action_selfTreatment',
				tooltip: lang['prinyat_patsienta_bez_elektronnogo_napravleniya_ili_ekstrennoy_birki'],
				text: lang['samostoyatelnoe_obraschenie'],
				iconCls : 'reception-self32',
				handler: function() 
				{
					form.selfTreatment();
				}
			},*/
			action_EditSchedule: {
				handler: function() {
					var form = Ext.getCmp('swMPWorkPlacePriemWindow');
					getWnd('swScheduleEditMasterWindow').show({
						UserLpuSection_id: form.userMedStaffFact.LpuSection_id,
						fromArm: 'priem'
					});
				},
				iconCls: 'schedule32',
				nn: 'action_EditSchedule',
				hidden: !(getRegionNick().inlist(['msk','vologda', 'ufa']) && isUserGroup('SchedulingPS')),
				text: 'Ведение расписания',
				tooltip: 'Ведение расписания'
			},
			action_logNotice: 
			{
				nn: 'action_logNotice',
				tooltip: lang['soobscheniya'],
				text: lang['jurnal_uvedomleniy'],
				iconCls : 'mail32',
				disabled: false,
				handler: function() 
				{
					getWnd('swMessagesViewWindow').show();
				}
			},
			action_PathoMorph:
			{
				nn: 'action_PathoMorph',
				tooltip: lang['patomorfologiya'],
				text: lang['patomorfologiya'],
				iconCls: 'pathomorph-32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.EvnDirectionHistologicViewAction,
						sw.Promed.Actions.EvnHistologicProtoViewAction,
						'-',
						sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
						sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
					]
				})
			},
			action_StacSvid:
			{
				nn: 'action_StacSvid',
				tooltip: lang['svidetelstvo'],
				text: lang['svidetelstvo'],
				iconCls : 'medsvid32',
				menuAlign: 'tr',
				hidden: !isMedSvidAccess(),
				menu: new Ext.menu.Menu({
					items: [
						form.SvidActions.New_SvidBirth,
						form.SvidActions.New_SvidDeath,
						form.SvidActions.New_SvidPntDeath
					]
				})
			},
			action_reports: //http://redmine.swan.perm.ru/issues/18509
			{
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls: 'report32',
				//hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
				handler: function() {
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show();
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show();
								}
							});
					}
				}
			},
			action_EvnDirectionExt:
			{
				handler: function() {
					getWnd('swEvnDirectionExtWindow').show();
				},
				hidden: !getRegionNick().inlist([ 'astra' ]),
				iconCls : 'pers-cards32',
				nn: 'action_EvnDirectionExt',
				text: lang['vneshnie_napravleniya'],
				tooltip: lang['vneshnie_napravleniya']
			},
			action_CarAccidents: {
				iconCls : 'pol-dtp16',
				nn: 'action_CarAccidents',
				text: lang['izvescheniya_o_dtp'],
				tooltip: lang['izvescheniya_o_dtp'],
				menu: new Ext.menu.Menu({
					items: [
						{
							text: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
							tooltip: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
							iconCls: 'stac-accident-injured16',
							handler: function()
							{
								getWnd('swEvnDtpWoundWindow').show();
							},
							hidden: false
						},
						{
							text: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
							tooltip: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
							iconCls: 'stac-accident-dead16',
							handler: function()
							{
								getWnd('swEvnDtpDeathWindow').show();
							},
							hidden: false
						},
						{
							text: langs('Журнал ДТП, производственных и криминальных травм'),
							tooltip: langs('Журнал ДТП, производственных и криминальных травм'),
							iconCls: 'stac-accident-injured16',
							handler: function()
							{
								getWnd('swTrafficAccidentProductionCriminalInjuryJournal').show();
							},
							hidden: getRegionNick().inlist(['kz'])
						}
					]
				})
			},
			action_EvnDirectionList: {
				hidden: (getRegionNick() != 'khak'),
				iconCls: 'journal16',
				nn: 'action_EvnDirectionList',
				text: lang['jurnal_napravleniy_na_gospitalizatsiyu'],
				tooltip: lang['jurnal_napravleniy_na_gospitalizatsiyu'],
				handler: function () {
					getWnd('swEvnDirectionJournalWindow', {
                        params: { userMedStaffFact: null}
					}).show();
				}
			},
			action_PregnancyRegistry: {
				hidden: (!getRegionNick().inlist(['vologda', 'ufa']) || !isUserGroup('OperPregnRegistry')),
				iconCls : 'registry32',
				nn: 'action_PregnancyRegistry',
				text: lang['registr_beremennyih'],
				tooltip: lang['registr_beremennyih'],
				handler: function(){
					getWnd('swPersonPregnancyWindow').show();
				}
			},

            // #175117
            // Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
            action_TimeJournal:
                {
                    nn: 'action_TimeJournal',
                    text: langs('Журнал учета рабочего времени сотрудников'),
                    tooltip: langs('Открыть журнал учета рабочего времени сотрудников'),
                    iconCls: 'report32',
                    disabled: false,

                    handler:
                        function()
                        {
                            var cur = sw.Promed.MedStaffFactByUser.current;

                            getWnd('swTimeJournalWindow').show(
                                {
                                    ARMType: (cur ? cur.ARMType : undefined),
                                    MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
                                    Lpu_id: (cur ? cur.Lpu_id : undefined)
                                });
                        }
                }
		}
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		var Only16pxIconActions = ['action_Templ', 'action_CarAccidents'];

		for(var key in configActions)
		{
			var iconCls = '';

			if (key.inlist(Only16pxIconActions))
				iconCls = configActions[key].iconCls;
			else
				iconCls = configActions[key].iconCls.replace(/16/g, '32');

			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_EditSchedule', 'action_selfTreatment','action_logNotice', 'action_PathoMorph', 'action_StacSvid', 'action_reports', 'action_EvnDirectionExt', 'action_PregnancyRegistry', (getRegionNick() != 'kz')?'action_CarAccidents':'', 'action_EvnDirectionList', 'action_TimeJournal' /* #175117 */];
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			border: false,
			id: form.id + '_hhd',
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});
		this.leftPanel =
		{
			animCollapse: false,
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'mpwpprLeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id + '_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					return;
				}
				
			},
			border: true,
			title: ' ',
			items: [
				new Ext.Button(
				{	
					cls:'upbuttonArr',
					iconCls:'uparrow',
					disabled: false, 
					handler: function() 
					{
						var el = form.findById(form.id + '_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					border: false,
					layout:'border',
					id: form.id + '_slid',
					height:100,
					items:[this.leftMenu]
				},			
				new Ext.Button(
				{
				cls:'upbuttonArr',
				iconCls:'downarrow',
				style:{width:'48px'},
				disabled: false, 
				handler: function() 
				{
					var el = form.findById(form.id + '_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;
					
				}
				})
			]
		};
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.FilterPanel,
				this.leftPanel,
				{
					layout: 'border',
					region: 'center',
					id: 'mpwpprSchedulePanel',
					items:
					[{
						html:'<audio id="'+this.id+'_ring'+'"><source src="/audio/web/WavLibraryNet_Sound5825.mp3" type="audio/mpeg"></audio>'
					},
						this.mainGrid
					]
				}
				
			],
			buttons: 
			[{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		
		sw.Promed.swMPWorkPlacePriemWindow.superclass.initComponent.apply(this, arguments);
		
	},

	/**
	 * Возвращает штрихкод с данными пациента
	 */
	getBarcodeData: function() {
		var row = this.getSelectedRecord();

		Person_id        = row.get('Person_id');
		EvnPS_NumCard    = row.get('EvnPS_NumCard');
		LpuSection       = row.get('LpuSection_Name');

		result = new Object();
		//result.barcode = Person_id;
		result.barcode = "";
		if(Ext.globalOptions.stac.band_Person_id)
			result.barcode = Person_id;

		if(Ext.globalOptions.stac.band_lpu_nick)
			result.lpu_nick = getGlobalOptions().lpu_nick;
		if(Ext.globalOptions.stac.band_numcard)
			result.NumCard = 'МК №' + EvnPS_NumCard;
		result.PersonData = "";

		if(Ext.globalOptions.stac.band_fio)
			result.PersonData = row.get('Person_Fio');

		if(Ext.globalOptions.stac.band_birthday)
			result.PersonData      += '(' + row.get('Person_BirthDay').format('d.m.Y') + ')';

		if(Ext.globalOptions.stac.band_lpusection)
			result.LpuSection = LpuSection;

		return result;
	},

	/**
	 * Печать браслета (штрихкод+фио+д/р)
	 */
	printBand: function() {
		String.prototype.replaceAll = function(search, replace){
			return this.split(search).join(replace);
		};

		band    = this.getBarcodeData();
		barcode = band.barcode;
		numcard = band.NumCard;
		lpu_nick = band.lpu_nick;
		personData = band.PersonData;
		lpusection = band.LpuSection;

		//Формируем строку для печати русского языка
		band_text = "";

		if(!Ext.isEmpty(lpu_nick))
			band_text += encodeURIComponent(lpu_nick).replaceAll("%","_") + '\\&';

		if(!Ext.isEmpty(lpusection))
			band_text += encodeURIComponent(lpusection).replaceAll("%","_") + '\\&';

		if(Ext.isEmpty(lpu_nick) && Ext.isEmpty(lpusection))
			band_text += '\\&\\&';
		else
			band_text += '\\&';

		if(!Ext.isEmpty(personData))
			band_text += encodeURIComponent(personData).replaceAll("%","_") + '\\&';

		if(!Ext.isEmpty(numcard))
			band_text += encodeURIComponent(numcard).replaceAll("%","_");

		options = getBandPrintOptions();
		font_width   = options.font_width;
		font_height  = options.font_height;
		margin_left   = options.margin_left;
		barcode_size = options.barcode_size;
		barcode_height = options.barcode_height;
		margin_bottom = options.margin_bottom;
		barcode_margin_bottom = options.barcode_margin_bottom;
		textFieldWidth = options.textfield_width;

		barcode_margin_left = parseInt(margin_left) + parseInt(textFieldWidth);

		//код на языке Zebra для печати
		var zpl = '^XA ^CWT,E:TT0003M_.TTF ^CFT,'+ font_height + ',' + font_width + ' '
				+ '^ATR ^CI28 ^FO' + margin_bottom+',' + margin_left 
				+ '^FB' + textFieldWidth + ',8,0,L,0 ^FH ^FD' + band_text + '^FS'
				+ '^CVY ^BY' + barcode_size + '^BCR,' + barcode_height + ',Y,N,N'
				+ '^FO' + barcode_margin_bottom +',' + barcode_margin_left+ ' ^FD'+ barcode +'^FS ^XZ'; //штрихкод

		printer_name = Ext.globalOptions.stac.band_printer;
		ZebraPrintZpl(zpl,printer_name);
	},

	/**
	 * метод для Приемного отделения и КВС
	 */
	showPopup: function(window) {
		var row = this.mainGrid.getSelectionModel().getSelected(),
			isTLT = !Ext.isEmpty( row.get('TLTDT') ),
			params = {};

		switch(window) {
			case 'trauma':
				params.title = 'Шкала оценки тяжести (Травма)';
				params.fields = [{
					fieldLabel: 'Реация на боль',
					value: row.get('PainResponse_Name')
				}, {
					fieldLabel: 'Характер внешнего дыхания',
					value: row.get('ExternalRespirationType_Name')
				}, {
					fieldLabel: 'Систолическое АД, мм рт. ст.',
					value: row.get('SystolicBloodPressure_Name')
				}, {
					fieldLabel: 'Признаки внутреннего кровотечения',
					value: row.get('InternalBleedingSigns_Name')
				}, {
					fieldLabel: 'Отрыв конечности',
					value: row.get('LimbsSeparation_Name')
				}, {
					fieldLabel: 'Итого баллов',
					value: row.get('PrehospTraumaScale_Value')
				}];
				break;

			case 'lams':
				params.title = 'Шкала LAMS (ОНМК)'
				params.fields = [{
					fieldLabel: 'Асимметрия лица',
					value: row.get('FaceAsymetry_Name')
				}, {
					fieldLabel: 'Удержание рук',
					value: row.get('HandHold_Name')
				}, {
					fieldLabel: 'Сжимание в кисти',
					value: row.get('SqueezingBrush_Name')
				}, {
					fieldLabel: 'Итого баллов',
					value: row.get('ScaleLams_Value')
				}];
				break;

			case 'oks':
				params.title = 'ОКС';
				params.fields = [{
					fieldLabel: 'Время начала болевых симптомов',
					value: row.get('PainDT')
				}, {
					fieldLabel: 'Результат ЭКГ',
					value: row.get('ResultECG')
				}, {
					fieldLabel: 'Время проведения ЭКГ',
					value: row.get('ECGDT')
				}, {
					fieldLabel: 'Время проведения ТЛТ',
					value: row.get('TLTDT')
				}, {
					fieldLabel: 'Причина отказа от ТЛТ',
					value: row.get('FailTLT')
				}];
				break;
		}

		showPopupWindow(params);
	}
});
