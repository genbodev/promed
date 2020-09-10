/**
* swHomeVisitAddWindow - форма добавления вызова на дом
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2014 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      21.08.2014
*/

/*NO PARSE JSON*/

/**
 * Комбобокс выбора территории облсуживания
 */
sw.Promed.SwHomeVisitWhoCallCombo = Ext.extend(sw.Promed.SwBaseLocalCombo, {
	codeField: 'HomeVisitWhoCall_id',
	displayField: 'HomeVisitWhoCall_Name',
	editable: true,
	fieldLabel: lang['kto_vyizyivaet'],
	forceSelection: true,
	hiddenName: 'HomeVisitWhoCall_id',
	store: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{name: 'HomeVisitWhoCall_Code', type: 'int'},
			{name: 'HomeVisitWhoCall_id', type: 'int'},
			{name: 'HomeVisitWhoCall_Name', type: 'string'}
		],
		key: 'HomeVisitWhoCall_id',
		sortInfo: {
			field: 'HomeVisitWhoCall_Code',
			direction: 'ASC'
		},
		tableName: 'HomeVisitWhoCall'
	}),
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<font color="red">{HomeVisitWhoCall_Code}</font>&nbsp;{HomeVisitWhoCall_Name}',
		'</div></tpl>'
	),
	valueField: 'HomeVisitWhoCall_id',
	width: 500,
	initComponent: function() {
		sw.Promed.SwHomeVisitWhoCallCombo.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swhomevisitwhocallcombo', sw.Promed.SwHomeVisitWhoCallCombo);

/**
 * Комбобокс выбора типа ЛПУ по возрасту приема взрослые/детские
 */
sw.Promed.SwHomeVisitStatusComboShort = Ext.extend(sw.Promed.SwBaseLocalCombo,
{
	store: new Ext.data.SimpleStore(
	{
		key: 'HomeVisitStatus_id',
		autoLoad: false,
		fields:
		[
			{name: 'HomeVisitStatus_id', type: 'int'},
			{name: 'HomeVisitStatus_Name', type: 'string'}
		],
		data: [
			[1, lang['novyiy']],
			[6, lang['naznachen_vrach']],
			[2, lang['otkaz']]
		]
	}),
	//triggerAction: 'all',
	editable: false,
	displayField:'HomeVisitStatus_Name',
	valueField: 'HomeVisitStatus_id',
	hiddenName:'HomeVisitStatus_id',
	fieldLabel: lang['status'],
	value: '1',
	initComponent: function()
	{
		sw.Promed.SwHomeVisitStatusComboShort.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swhomevisitstatuscomboshort', sw.Promed.SwHomeVisitStatusComboShort);

sw.Promed.swHomeVisitAddWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['dobavlenie_vyizova_na_dom'],
	id: 'HomeVisitAddWindow',
	layout: 'border',
	maximizable: false,
	width: 650,
	height: 620,
	modal: true,
	codeRefresh: true,
	objectName: 'HomeVisitAddWindow',
	objectSrc: '/jscore/Forms/Reg/swHomeVisitAddWindow.js',
	action:'add',
	Person_id: null,
	Server_id: null,
	Lpu_id: null,
	isCVI: false,
	returnFunc: function(owner) {},
	disabledField:function(fl,id){
		this.MainPanel.getForm().items.each(function(r){
			if(id&&id.inlist([3,6])&&r.hiddenName&&r.hiddenName.inlist(['HomeVisitStatus_id','MedStaffFact_id'])){
				log(1324234234);
				r.setDisabled(!fl);
			}else{
				r.setDisabled(fl);
			}
		})
	},
	getNum:function(clearNumerator){
		if(clearNumerator){
			this.Numerator_id = null;
		}
		var win = this;
		if(win.Numerator_id > 0){
			win.getNumByNumerator(win.Numerator_id);
		} else {
			var params = {
				NumeratorObject_SysName: 'HomeVisit',
				Lpu_id: this.SelectLpuCombo.getValue(),
				onDate: this.MainPanel.getForm().findField('HomeVisit_setDate').getValue().format('d.m.Y')
			};
			win.getLoadMask('Получение нумератора').show();
			win.NumeratorField.disable();
			Ext.Ajax.request({ //заполнение номера
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						
						if(response_obj && response_obj.length > 0){
							if(response_obj.length == 1){
								win.NumeratorField.triggers[1].setOpacity(0.5);
								win.NumeratorField.triggers[1].notActive = true;
								win.Numerator_id = response_obj[0].Numerator_id;
								//win.getNumByNumerator(win.Numerator_id);
							} else {
								win.Numerators = response_obj;
								win.NumeratorField.triggers[1].setOpacity(1);
								win.NumeratorField.triggers[1].notActive = false;
								for (var i = 0; i < response_obj.length; i++) {
									if (response_obj[i].DefaultNumerator) {
										// если в сессии есть выбранный нумератор, то выберем его
										win.Numerator_id = response_obj[i].Numerator_id;
										break;
									}
								}
								// если в сессии есть выбранный нумератор, то выберем его. иначе - первый в списке
								win.Numerator_id = (win.Numerator_id) ? win.Numerator_id : response_obj[0].Numerator_id;
							}
							if(win.Numerator_id) {
								win.defaultNumerator_id = win.Numerator_id;
								win.getNumByNumerator(win.Numerator_id);
								win.NumeratorField.enable();
							}
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], 'При получении нумератора возникли ошибки');
					}
				},
				params: params,
				url: '/?c=Numerator&m=getActiveNumeratorList'
			});
		}
	},
	getNumByNumerator: function (numerator_id) {
		if(numerator_id > 0){
			var win = this;
			var numparams = {
				Numerator_id: numerator_id,
				Lpu_id: this.SelectLpuCombo.getValue(),
				onDate: this.MainPanel.getForm().findField('HomeVisit_setDate').getValue().format('d.m.Y')
			};
			
			win.getLoadMask('Получение номера вызова').show();
			Ext.Ajax.request({ //заполнение номера
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj) {
							if (response_obj[1] && response_obj[1] == 'numerator404') {
								//win.NumeratorField.disable();
								win.NumeratorField.setValue('');
							} else if (response_obj[0] && response_obj[1] === false) {
								sw.swMsg.alert(lang['oshibka'], response_obj[0]);
								win.NumeratorField.setValue('');
							} else {
								win.NumeratorField.enable();
								win.NumeratorField.setValue(response_obj[0]);
							}
						} else {
							win.NumeratorField.disable();
							win.NumeratorField.setValue('');
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], 'При получении номера вызова возникли ошибки');
					}
				},
				params: numparams,
				url: '/?c=HomeVisit&m=getHomeVisitNum'
			});
		}
	},
	getMO: function (values) {
		var win = this;
			Ext.Ajax.request({ 
				callback: function (options, success, response) {
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj[0]) {
							win.SelectLpuCombo.fireEvent('select', win.SelectLpuCombo, win.SelectLpuCombo.setValue(response_obj[0]));
						}
					} else {
						win.SelectLpuCombo.fireEvent('select', win.SelectLpuCombo, win.SelectLpuCombo.setValue());
					}
				},
				params: values,
				url: '/?c=HomeVisit&m=getMO'
			});
	},
	filterMSF: function (lpuregion,store) {
		var win = this;
		var MedStaffFact = win.MainPanel.getForm().findField('MedStaffFact_id');
		var LpuRegion_id = ((lpuregion) ? lpuregion : win.MainPanel.getForm().findField('LpuRegion_cid').getValue());
		if(store){
			MedStaffFact.lastQuery = '';
			MedStaffFact.baseFilterFn = setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params,store, true);
		} else {
			setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params);
			MedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}
		var records = MedStaffFact.getStore().data;
		var callType = this.MainPanel.getForm().findField('HomeVisitCallType_id').getValue();
		if (callType != 6){
			if ( records.length > 0 && LpuRegion_id != "" ) {
				for (var i = 0; i < records.length; i++) {
					if ( !Ext.isEmpty(records.items[i].get('LpuRegion_List')) && !Ext.isEmpty(records.items[i].get('LpuRegion_MainList')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_List').toString().split(',')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_MainList').toString().split(',')) ) {
						MedStaffFact.setValue(records.items[i].get(MedStaffFact.valueField));
						break;
					}
				}
				if (!(MedStaffFact.getValue() > 0)) {
					for (var i = 0; i < records.length; i++) {
						if ( !Ext.isEmpty(records.items[i].get('LpuRegion_List')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_List').toString().split(',')) ) {
							MedStaffFact.setValue(records.items[i].get(MedStaffFact.valueField));
							break;
						}
					}
				}
				if (!(MedStaffFact.getValue() > 0)) {
					var index = MedStaffFact.getStore().findBy(function(rec){
						return (rec.get('MedPersonal_id') == getGlobalOptions()['CurMedPersonal_id']);
					});
					if(index > -1){
						MedStaffFact.setValue(MedStaffFact.getStore().getAt(index).get('MedStaffFact_id'));
					}
				}
			} else { // Или текущего врача если такого врача или участка нет
				var index = MedStaffFact.getStore().findBy(function(rec){
					return (rec.get('MedPersonal_id') == getGlobalOptions()['CurMedPersonal_id']);
				});
				if(index > -1){
					MedStaffFact.setValue(MedStaffFact.getStore().getAt(index).get('MedStaffFact_id'));
				}
			}
		}
	},
	show: function() 
	{
		this.returnFunc=null;
		this.HomeVisit_id=null;
		this.Person_id=null;
		this.Server_id=null;
		this.Lpu_id=null;
		this.action = 'add';
		this.HomeVisitStatus_id = null;
		this.callCenter = false;
		this.LpuRegion_id = null;
        var _this = this;
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['HomeVisit_id'])
			this.HomeVisit_id = arguments[0]['HomeVisit_id'];
		
		if (arguments[0]['Person_id']) {
			this.Person_id = arguments[0]['Person_id'];
		}
		
		if (arguments[0]['Server_id']) {
			this.Server_id = arguments[0]['Server_id'];
		}
		
		if (arguments[0]['Lpu_id']) {
			this.Lpu_id = arguments[0]['Lpu_id'];
		}
		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
		}
		if (arguments[0]['HomeVisitStatus_id']) {
			this.HomeVisitStatus_id = arguments[0]['HomeVisitStatus_id'];
		}
		if (arguments[0]['callCenter']) {
			this.callCenter = arguments[0]['callCenter'];
		}
		if (arguments[0]['HomeVisitCallType_id']) {
			this.HomeVisitCallType_id = arguments[0]['HomeVisitCallType_id'];
		}

		var base_form = this.MainPanel.getForm();

		base_form.reset();
		this.SelectLpuRegionCombo.getStore().removeAll();

		base_form.findField('HomeVisitStatus_id').getStore().clearFilter();

		this.StomLpuList = [];
		Ext.Ajax.request({
			url: '/?c=HomeVisit&m=getLpuPeriodStomMOList',
			success: function(response){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj.length > 0){
					for(var i=0;i<response_obj.length;i++){
						this.StomLpuList.push(response_obj[i].Lpu_id);
					}
				}
			}.createDelegate(this)
		});
		this.PersonInfoPanel.setTitle('');
		switch(this.action){
			case 'add':
				this.setTitle(lang['dobavlenie_vyizova_na_dom']);
				this.disabledField(false);
				this.PersonInfoPanel.load({ 
					Person_id: this.Person_id,
					Server_id: this.Server_id,
					callback: function() {
						this.PersonInfoPanel.setPersonTitle();
					}.createDelegate(this) 
				});
	
				var loadMask = new Ext.LoadMask(Ext.get('HomeVisitAddMainPanel'), { msg: "Загрузка данных..." });

				this.MainPanel.getForm().load({
					url: C_HOMEVISIT_ADD_GET,
					params:
					{
						Person_id: this.Person_id,
						Lpu_id: this.Lpu_id
					},
					success: function (response,action)
					{
						loadMask.hide();

						if(!getHomeVizitOptions().homevizit_spec_isallowed || this.callCenter){
							if(this.MainPanel.getForm().findField('HomeVisitCallType_id').getStore().data.length == 5){
								this.MainPanel.getForm().findField('HomeVisitCallType_id').getStore().data.removeAt(4);
							}
						} else {
							this.MainPanel.getForm().findField('HomeVisitCallType_id').getStore().reload();
						}

						var curDT = new Date();

						var response_obj = Ext.util.JSON.decode(action.response.responseText);
						if(response_obj[0].alert_msg){
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'no' ) {
										this.hide();
										return false;
									}
								}.createDelegate(this),
								icon: Ext.Msg.WARNING,
								msg: response_obj[0].alert_msg,
								title: 'Предупреждение'
							});
						}
						if(response_obj[0].Person_Attach){
							this.Person_Attach = response_obj[0].Person_Attach;
						} else {
							this.Person_Attach = '';
						}
						if(response_obj[0].Person_AttachS){
							this.Person_AttachS = response_obj[0].Person_AttachS;
						} else {
							this.Person_AttachS = '';
						}
						if(response_obj[0].LpuRegion_id){
							this.LpuRegion_id = response_obj[0].LpuRegion_id;
							this.MainPanel.getForm().findField('LpuRegion_cid').setValue(this.LpuRegion_id);
						}
						this.MainPanel.getForm().findField('CallProfType_id').setValue(1);
						if(this.StomLpuList.length == 0 || !getGlobalOptions().lpu_id.inlist(this.StomLpuList))
							this.MainPanel.getForm().findField('CallProfType_id').disable();
						else 
							this.MainPanel.getForm().findField('CallProfType_id').enable();
						this.MainPanel.getForm().findField('HomeVisitCallType_id').setValue(1);
						this.MainPanel.getForm().findField('HomeVisit_setDate').disable();
						this.MainPanel.getForm().findField('HomeVisit_setDate').setValue(getGlobalOptions().date);
						this.MainPanel.getForm().findField('HomeVisit_setTime').disable();
						this.MainPanel.getForm().findField('HomeVisit_setTime').setValue(curDT.format('H:i'));

						if ( ['regpol','regpol6'].in_array( getGlobalOptions()['CurMedServiceType_SysNick'] ) ) { // из АРМа оператора регистратуры поликлиники вызовы всегда в свою ЛПУ
							this.SelectLpuCombo.setValue(getGlobalOptions()['lpu_id']);
							this.SelectLpuCombo.setDisabled(true);
						} else {
							this.SelectLpuCombo.setDisabled(false);
							
							var params={
								KLTown_id: response_obj[0].KLTown_id,
								KLStreet_id: response_obj[0].KLStreet_id,
								KLCity_id: response_obj[0].KLCity_id,
								Address_House: response_obj[0].Address_House,
								Person_Age: this.PersonInfoPanel.DataView.getStore().getAt(0).get('Person_Age')
							};
//							PROMEDWEB-10332/PROMEDWEB-10895 - Подстановка МО работает некорректно. Временно отключаем этот функционал.
//							this.getMO(params);
						}
						this.SelectLpuCombo.fireEvent('select',_this.SelectLpuCombo,_this.SelectLpuCombo.getValue());

						this.MainPanel.getForm().findField('HomeVisit_setDate').fireEvent('change', this.MainPanel.getForm().findField('HomeVisit_setDate'), this.MainPanel.getForm().findField('HomeVisit_setDate').getValue());
						var LpuRegion_id = this.MainPanel.getForm().findField('LpuRegion_cid').getValue(),
							Person_Age = _this.findById('HVAW_PersonInfoFrame').getFieldValue('Person_Age');
						var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
						var win = this;
						var lpu = (this.SelectLpuCombo.getValue())?this.SelectLpuCombo.getValue():getGlobalOptions().lpu_id;
						var HomeVisitDate = this.MainPanel.getForm().findField('HomeVisit_setDate').getValue();
						this.medstafffact_filter_params = { isDoctor:true, Lpu_id:lpu, withLpuRegionOnly:true, LpuRegionType_HomeVisit:'terpedvop', HomeVisitDate:HomeVisitDate };
						if(getRegionNick().inlist(['kareliya','ekb'])){
							this.medstafffact_filter_params.isDoctor = false;
						}
						if(swMedStaffFactGlobalStore.data.length == 0 || lpu != getGlobalOptions().lpu_id){
							var params = {};
							if(lpu != getGlobalOptions().lpu_id){
								params = {Lpu_id:lpu,mode:'combo'};
								this.medstafffact_filter_params.isAliens = true;
							}
							MedStaffFact.getStore().load({
								params: params,
								callback:function(){
									win.filterMSF(false,MedStaffFact.getStore());
								}
							});
						} else {
							win.filterMSF();
						}

						this.SymptomsFiels.firsttime = true;

						if (!Ext.isEmpty(this.HomeVisitStatus_id)) {
							this.MainPanel.getForm().findField('HomeVisitStatus_id').setValue(this.HomeVisitStatus_id);
						}
		
						if (!Ext.isEmpty(this.HomeVisitCallType_id)) {
							this.MainPanel.getForm().findField('HomeVisitCallType_id').setValue(this.HomeVisitCallType_id);
						}
		
					}.createDelegate(this),
					failure: function (result_form, action)
					{
						loadMask.hide();

						var result = Ext.util.JSON.decode(action.response.responseText);
						if (result.Error_Msg) {
							// Ошибку уже показали
						} else {
							Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						}
						this.hide();
					}.createDelegate(this)
				});
				this.MainPanel.findById('btnApplicationCVI').setVisible(false);
			break;
				
			case 'edit':
			case 'view':
				this.setTitle(lang['redaktirovanie_vyizova_na_dom']);
				if (this.HomeVisitStatus_id && this.HomeVisitStatus_id.inlist([3, 6])) {
					this.disabledField(true, this.HomeVisitStatus_id);
				} else {
					this.disabledField(false);
				}
				this.MainPanel.getForm().findField('HomeVisit_setDate').disable();
				this.MainPanel.getForm().findField('HomeVisit_setTime').disable();
				var loadMask = new Ext.LoadMask(Ext.get('HomeVisitAddMainPanel'), { msg: "Загрузка данных..." });
				log("base_form.findField('HomeVisitStatus_id')",base_form.findField('HomeVisitStatus_id'))
				if(this.HomeVisitStatus_id==3){
					
					base_form.findField('HomeVisitStatus_id').getStore().loadData([[3,lang['odobren_vrachom']]],true)
				}else{
					base_form.findField('HomeVisitStatus_id').getStore().removeAt(3);
				}

				if ( this.HomeVisitStatus_id == 6 ) {
					// Если назначен врач, то убираем запись "Отказ"
					base_form.findField('HomeVisitStatus_id').getStore().filterBy(function(rec) {
						return rec.get('HomeVisitStatus_id') != 2;
					});
				}
				
				this.MainPanel.getForm().load({
					url: '/?c=HomeVisit&m=getHomeVisitEditWindow',
					params:
					{
						HomeVisit_id: this.HomeVisit_id
					},
					success: function (form,action)
					{
						var result = Ext.util.JSON.decode(action.response.responseText);
						if(result && result[0] && result[0].HomeVisit_Num) {
							this.NumeratorField.setValue(result[0].HomeVisit_Num);
						}
						if(result && result[0] && result[0].LpuRegion_cid) {
							this.SelectLpuRegionCombo.setValue(result[0].LpuRegion_cid);
						}
						if(result && result[0] && result[0].HomeVisitSource_id && result[0].HomeVisitSource_id.inlist([2,3,4,6,7])) {
							this.MainPanel.getForm().findField('CallProfType_id').disable();
						}
						this.PersonInfoPanel.load({ 
							Person_id: base_form.findField('Person_id').getValue(),
							Server_id: base_form.findField('Server_id').getValue(),
							callback: function() {
								this.PersonInfoPanel.setPersonTitle();
							}.createDelegate(this) 
						});
						if(_this.SelectLpuCombo.getValue() && !_this.Numerator_id){
							_this.getNum(true);
						}
						loadMask.hide();
						
						/*if (getGlobalOptions()['CurMedServiceType_SysNick'] == 'regpol') { // из АРМа оператора регистратуры поликлиники вызовы всегда в свою ЛПУ
							this.SelectLpuCombo.setValue(getGlobalOptions()['lpu_id']);
							this.SelectLpuCombo.setDisabled(true);
						} else {
							this.SelectLpuCombo.setDisabled(false);
							if (this.SelectLpuCombo.getValue() == null ) {
								this.SelectLpuCombo.setValue(this.Lpu_id);
							}
						}*/
						if(!getHomeVizitOptions().homevizit_spec_isallowed || this.callCenter){
							if(this.MainPanel.getForm().findField('HomeVisitCallType_id').getStore().data.length == 5){
								this.MainPanel.getForm().findField('HomeVisitCallType_id').getStore().data.removeAt(4);
							}
						} else {
							this.MainPanel.getForm().findField('HomeVisitCallType_id').getStore().reload();
						}
						var LpuRegion_id = this.MainPanel.getForm().findField('LpuRegion_cid').getValue();
						var LpuRegion_combo = this.MainPanel.getForm().findField('LpuRegion_cid');
						if(LpuRegion_id && (this.SelectLpuRegionCombo.getStore().data.length == 0 || !this.SelectLpuRegionCombo.getStore().getById(LpuRegion_id))){
							this.SelectLpuRegionCombo.getStore().load({
								params: {LpuRegion_id:LpuRegion_id},
								callback: function(){
									LpuRegion_combo.setValue(LpuRegion_id);
								}
							});
						}
						var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
						var MedStaffFact_val = this.MainPanel.getForm().findField('MedStaffFact_id').getValue();
						var callProf = this.MainPanel.getForm().findField('CallProfType_id').getValue();
						var LpuRegionType_HomeVisit = '';
						switch(callProf){
							case '1':
							LpuRegionType_HomeVisit = 'terpedvop';
							break;
							case '2':
							LpuRegionType_HomeVisit = 'stom';
							break;
							default:
							LpuRegionType_HomeVisit = '';
							break;
						}
						var win = this;
						var lpu = (this.SelectLpuCombo.getValue())?this.SelectLpuCombo.getValue():getGlobalOptions().lpu_id;
						var HomeVisitDate = this.MainPanel.getForm().findField('HomeVisit_setDate').getValue();
						this.medstafffact_filter_params = { isDoctor:true, Lpu_id:lpu, withLpuRegionOnly:true, HomeVisitDate:HomeVisitDate };
						if(LpuRegionType_HomeVisit.length > 0){
							this.medstafffact_filter_params.LpuRegionType_HomeVisit = LpuRegionType_HomeVisit;
							if (LpuRegionType_HomeVisit == 'terpedvop') {
								var callType = this.MainPanel.getForm().findField('HomeVisitCallType_id').getValue();
								if (callType == 6){
									this.medstafffact_filter_params.HomeVisit_onlySpecs = true;
								}
							}
						}
						if(getRegionNick().inlist(['kareliya','ekb'])){
							this.medstafffact_filter_params.isDoctor = false;
						}
						if(swMedStaffFactGlobalStore.data.length == 0 || lpu != getGlobalOptions().lpu_id){
							var params = {};
							if(lpu != getGlobalOptions().lpu_id){
								params = {Lpu_id:lpu,mode:'combo'};
								this.medstafffact_filter_params.isAliens = true;
							}
							MedStaffFact.getStore().load({
								params: params,
								callback:function(){
									win.filterMSF(false,MedStaffFact.getStore());
								}
							});
						} else {
							win.filterMSF();
						}
						MedStaffFact.setValue(MedStaffFact_val);
						this.MainPanel.findById('btnApplicationCVI').setVisible(
							!Ext.isEmpty(form.findField('PlaceArrival_id').getValue())
						);
						this.SymptomsFiels.firsttime = true;
					}.createDelegate(this),
					failure: function (result_form, action)
					{
						loadMask.hide();
						
						var result = Ext.util.JSON.decode(action.response.responseText);
						if (result.Error_Msg) {
							// Ошибку уже показали
						} else {
							Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
						}
						this.hide();
					}.createDelegate(this)
				});

				break;
		}

		sw.Promed.swHomeVisitAddWindow.superclass.show.apply(this, arguments);
	},
	
	doSave: function() 
	{
		var form = this.MainPanel.getForm();
		form.findField('MedStaffFact_id').setAllowBlank(!(form.findField('HomeVisitStatus_id').getValue().inlist([3,6]) ));

		if (!form.isValid()) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() {
					this.MainPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if ( Ext.isEmpty(form.findField('HomeVisit_Num').getValue()) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не задан номер вызова. Для формирования номера вызова необходимо задать нумератор.'));
			return false;
		}

		if (this.isCVI && !form.findField('isSavedCVI').getValue() && form.findField('HomeVisit_isQuarantine').getValue()) {
			sw.swMsg.alert(langs('Ошибка'), langs('Для вызова должна быть заполнена анкета по КВИ'));
			return false;
		}
		form.findField('HomeVisit_isQuarantine').setDisabled(false);

		var loadMask = new Ext.LoadMask(Ext.get('HomeVisitAddMainPanel'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		//предотвратим отправку формы дважды
		this.buttons[0].disable();
		
		//Чтобы не делать hidden поля со значениями, храним данные в объекте и при посылке запроса вручную их передаём
		var post = new Object();
		
		// передаём заблокированные поля
		var fields = form.items.items;
		for (var index = 0; index < fields.length; index ++) {

			var field = fields[index];
			var fieldName;
			if(!field.name) { 
				fieldName = field.hiddenName;
			} else {
				fieldName = field.name;
			}
			if (field.disabled) {
				post[fieldName] = field.value;
			}
		}

		post.Lpu_id = this.SelectLpuCombo.getValue();
		post.MedPersonal_id = this.MainPanel.getForm().findField('MedStaffFact_id').getFieldValue('MedPersonal_id');

		form.submit({
			params: post,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
					else {
						//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
					}
				}
				this.buttons[0].enable();
				this.hide();
			}.createDelegate(this),
			success: function(result_form, action) 
			{
				loadMask.hide();
				this.buttons[0].enable();
				Ext.Msg.alert('Успешно', 'Вызов на дом сохранён');
				this.hide();
				this.returnFunc();
			}.createDelegate(this)
		});
	},

	initComponent: function() 
	{
		this.NumeratorField = new Ext.form.TwinTriggerField({
			allowBlank: false,
			anchor : "98%",
			readOnly : true,
			forceSelection: true,
			//id : 'HVAW_Symptoms',
			name: 'HomeVisit_Num',
			//tabIndex: TABINDEX_HVAW + 6,
			fieldLabel: lang['nomer_vyizova'],
			typeAhead: false,
			trigger1Class: 'x-form-plus-trigger',
			trigger2Class: 'x-form-search-trigger',
			firsttime: true,
			setDefaultNumber: function(numerator_id){
				if(!numerator_id) return false;
				
				var homeVisitAddWindow = Ext.getCmp('HomeVisitAddWindow');
				if(homeVisitAddWindow.defaultNumerator_id && homeVisitAddWindow.defaultNumerator_id != numerator_id){
					this.setValue('');
					// устанавливаем значение в сессии для последующего выбора по умолчанию
					Ext.Ajax.request({ 
						callback: function (options, success, response) {
							var homeVisitAddWindow = Ext.getCmp('HomeVisitAddWindow');
							
							if (success && response.responseText != '') {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.defaultNumerator_id) {
									homeVisitAddWindow.defaultNumerator_id = response_obj.defaultNumerator_id;
								}
							} else {
								console.log('ошибка при установке нумератора в сессии');
								homeVisitAddWindow.defaultNumerator_id = false;
							}
						},
						failure: function(response, opts){
							console.log('ошибка при установке нумератора в сессии');
							homeVisitAddWindow.defaultNumerator_id = false;
						},
						params: {Numerator_id: numerator_id, NumeratorObject_SysName: 'HomeVisit'},
						url: '/?c=Numerator&m=setDefaultNumerator'
					});
				}
			},
			onTrigger1Click: function() {
				if(this.NumeratorField.disabled)
					return false;
				if(this.Numerator_id > 0){
					this.getNumByNumerator(this.Numerator_id);
				} else {
					sw.swMsg.alert(lang['oshibka'], 'Не задан активный нумератор');
				}
			}.createDelegate(this),
			onTrigger2Click: function() {
				if (this.NumeratorField.disabled || this.NumeratorField.triggers[1].notActive)
					return false;
				if (this.Numerators && this.Numerators.length > 0) {
					var me = this;
					if (me.numeratorMenu) {
						me.numeratorMenu.destroy();
						me.numeratorMenu = null;
					}
					me.numeratorMenu = new Ext.menu.Menu();
					var fontWeight = 'normal';
					for (var i = 0; i < this.Numerators.length; i++) {
						fontWeight = (me.defaultNumerator_id && me.defaultNumerator_id == this.Numerators[i].Numerator_id) ? 'bold' : 'normal';
						me.numeratorMenu.add({
							text: this.Numerators[i].Numerator_Name,
							value: this.Numerators[i].Numerator_id,
							style: {'font-weight': fontWeight},
							handler: function () {
								var homeVisitAddWindow = Ext.getCmp('HomeVisitAddWindow');
								homeVisitAddWindow.Numerator_id = this.value;
								homeVisitAddWindow.NumeratorField.setDefaultNumber(this.value);
							}
						});
					}
					me.numeratorMenu.show(this.NumeratorField.trigger.getFxEl());

				} else {
					sw.swMsg.alert(lang['oshibka'], 'Нет активных нумераторов');
				}
			}.createDelegate(this)
		});

		this.AddressField = new Ext.form.TwinTriggerField ({
			allowBlank: false,
			enableKeyEvents: true,
			fieldLabel: lang['adres_vyizova'],
			name: 'Address_Address',
			readOnly: true,
			tabIndex: TABINDEX_HVAW + 1,
			trigger1Class: 'x-form-search-trigger',
			trigger2Class: 'x-form-clear-trigger',
			width: 500,

			listeners: {
				'keydown': function(inp, e) {
					if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
						if ( e.F4 == e.getKey() )
							inp.onTrigger1Click();

						if ( e.F2 == e.getKey() )
							inp.onTrigger2Click();

						if ( e.DELETE == e.getKey() && e.altKey)
							inp.onTrigger3Click();

						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						return false;
					}
				},
				'keyup': function( inp, e ) {
					if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
						if ( e.browserEvent.stopPropagation )
							e.browserEvent.stopPropagation();
						else
							e.browserEvent.cancelBubble = true;

						if ( e.browserEvent.preventDefault )
							e.browserEvent.preventDefault();
						else
							e.browserEvent.returnValue = false;

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						return false;
					}
				}
			},
			onTrigger2Click: function() {
				var form = this.MainPanel.getForm();
				form.findField('KLCountry_id').setValue(null);
				form.findField('KLRgn_id').setValue(null);
				form.findField('KLSubRgn_id').setValue(null);
				form.findField('KLCity_id').setValue(null);
				form.findField('KLTown_id').setValue(null);
				form.findField('KLStreet_id').setValue(null);
				form.findField('Address_House').setValue('');
				form.findField('Address_Corpus').setValue('');
				form.findField('Address_Flat').setValue('');
				this.AddressField.setValue('');
			}.createDelegate(this),
			onTrigger1Click: function() {

				var form = this.MainPanel.getForm();
				getWnd('swAddressEditWindow').show({
					fields: {
						Address_ZipEdit: '',
						KLCountry_idEdit: form.findField('KLCountry_id').getValue(),
						KLRgn_idEdit: form.findField('KLRgn_id').getValue(),
						KLSubRGN_idEdit: form.findField('KLSubRgn_id').getValue(),
						KLCity_idEdit: form.findField('KLCity_id').getValue(),
						KLTown_idEdit: form.findField('KLTown_id').getValue(),
						KLStreet_idEdit: form.findField('KLStreet_id').getValue(),
						Address_HouseEdit: form.findField('Address_House').getValue(),
						Address_CorpusEdit: form.findField('Address_Corpus').getValue(),
						Address_FlatEdit: form.findField('Address_Flat').getValue(),
						Address_AddressEdit: this.AddressField.getValue(),
						addressType: 0,
						showDate: false
					},
					callback: function(values) {
						form.findField('KLCountry_id').setValue(values.KLCountry_idEdit);
						form.findField('KLRgn_id').setValue(values.KLRgn_idEdit);
						form.findField('KLSubRgn_id').setValue(values.KLSubRGN_idEdit);
						form.findField('KLCity_id').setValue(values.KLCity_idEdit);
						form.findField('KLTown_id').setValue(values.KLTown_idEdit);
						form.findField('KLStreet_id').setValue(values.KLStreet_idEdit);
						form.findField('Address_House').setValue(values.Address_HouseEdit);
						form.findField('Address_Corpus').setValue(values.Address_CorpusEdit);
						form.findField('Address_Flat').setValue(values.Address_FlatEdit);
						this.AddressField.setValue(values.Address_AddressEdit);
						
						var params={
							KLTown_id: values.KLTown_idEdit,
							KLStreet_id: values.KLStreet_idEdit,
							KLCity_id: values.KLCity_idEdit,
							Address_House: values.Address_HouseEdit,
							Person_Age: this.PersonInfoPanel.DataView.getStore().getAt(0).get('Person_Age')
						};
//						PROMEDWEB-10332/PROMEDWEB-10895 - Подстановка МО работает некорректно. Временно отключаем этот функционал.
//						this.getMO(params);
					}.createDelegate(this),
					onClose: function() {
						this.AddressField.focus(true, 500);
					}.createDelegate(this)
				})
			}.createDelegate(this)
		});
		
		this.SelectLpuCombo = new sw.Promed.SwLpuCombo({
			allowBlank: false,
			anchor : "98%",
			editable : true,
			forceSelection: true,
			id : 'HVAW_Lpu_id',
			tabIndex: TABINDEX_HVAW + 2,
			lastQuery : '',
			listeners: {
				'blur': function(combo) {
					if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
						combo.clearValue();
					}
				},
				'keydown': function (inp, e) {
					if (e.shiftKey == false && e.getKey() == Ext.EventObject.ENTER)
					{
						e.stopEvent();
					}
				}.createDelegate(this),
				'select':function(combo, record, index) {
					var win = this;
					var LpuRegion_id = this.MainPanel.getForm().findField('LpuRegion_cid').getValue();
					this.SelectLpuRegionCombo.getStore().removeAll();
					var arrRegTypeList = Ext.util.JSON.encode(['ter','ped','vop','stom','op']);
					if(getRegionNick() == 'kz')
						arrRegTypeList = Ext.util.JSON.encode(['ter','ped','vop','stom','op','pmsp']);
					this.SelectLpuRegionCombo.getStore().load({
						params:{Lpu_id: this.SelectLpuCombo.getValue(), LpuRegionTypeList:arrRegTypeList, showOpenerOnlyLpuRegions:1},
						callback: function(){
							if(win.SelectLpuRegionCombo.getStore().getById(LpuRegion_id)){
								win.SelectLpuRegionCombo.setValue(LpuRegion_id);
							}
						}
					});
					
					var callProf = this.MainPanel.getForm().findField('CallProfType_id').getValue();
					var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
					MedStaffFact.clearValue();
					var records = MedStaffFact.getStore().data;
					var callType = this.MainPanel.getForm().findField('HomeVisitCallType_id').getValue();
					if (callType != 6){
						if ( records.length > 0 && LpuRegion_id != "" ) {
							if(callProf == 2){
								for (var i = 0; i < records.length; i++) {
									if ( records.items[i].get('LpuRegion_id') == LpuRegion_id && records.items[i].get('PostMed_Code').inlist(['191','192','194','195']) ) {
										MedStaffFact.setValue(records.items[i].get(MedStaffFact.valueField));
										break;
									}
								}
							}
							if (!(MedStaffFact.getValue() > 0) && callProf == 1) {
								for (var i = 0; i < records.length; i++) {
									if ( !Ext.isEmpty(records.items[i].get('LpuRegion_List')) && !Ext.isEmpty(records.items[i].get('LpuRegion_MainList')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_List').toString().split(',')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_MainList').toString().split(',')) ) {
										MedStaffFact.setValue(records.items[i].get(MedStaffFact.valueField));
										break;
									}
								}
								if (!(MedStaffFact.getValue() > 0)) {
									for (var i = 0; i < records.length; i++) {
										if ( !Ext.isEmpty(records.items[i].get('LpuRegion_List')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_List').toString().split(',')) ) {
											MedStaffFact.setValue(records.items[i].get(MedStaffFact.valueField));
											break;
										}
									}
								}
								if (!(MedStaffFact.getValue() > 0)) {
									var index = MedStaffFact.getStore().findBy(function(rec){
										return (rec.get('MedPersonal_id') == getGlobalOptions()['CurMedPersonal_id']);
									});
									if(index > -1){
										MedStaffFact.setValue(MedStaffFact.getStore().getAt(index).get('MedStaffFact_id'));
									}
								}
							}
						}
					}
					this.getNum(true);
				}.createDelegate(this)
			},
			fieldLabel: lang['mo'],
			typeAhead: false,
			name: 'Lpu_id'
		});

		this.SelectLpuRegionCombo = new sw.Promed.SwBaseRemoteCombo(
		{
			displayField: 'LpuRegion_Name',
			editable: false,
			enableKeyEvents: true,
			forceSelection: true,
			fieldLabel: lang['uchastok'],
			hiddenName: 'LpuRegion_cid',
			queryDelay: 1,
			lastQuery: '',
			mode: 'remote',
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'LpuRegion_id'
				},
				[
					{name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'},
					{name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
					{name: 'LpuRegion_Descr', mapping: 'LpuRegion_Descr'},
					{name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
					{name: 'LpuRegionType_SysNick', mapping: 'LpuRegionType_SysNick'},
					{name: 'LpuRegionType_Name', mapping: 'LpuRegionType_Name'}
				]),
				listeners: {
					'load': function(store) {
						var win = this;
						var base_form = this.MainPanel.getForm();
						this.SelectLpuRegionCombo.lastQuery = '';
						this.SelectLpuRegionCombo.getStore().clearFilter();
						var callProf = this.MainPanel.getForm().findField('CallProfType_id').getValue();
						// Вынес фильтрацию в отдельную фукнцию
						var localFiltering = function(arr){
							var local_arr = arr;
							var win = this;
							var LpuRegion_id = this.MainPanel.getForm().findField('LpuRegion_cid').getValue();
							var LpuRegion_cid = this.SelectLpuRegionCombo.getValue();
							this.SelectLpuRegionCombo.getStore().filterBy(function(rec) {
								return ( rec.get('LpuRegionType_SysNick').inlist(arr) );
							});
							if ( !Ext.isEmpty(LpuRegion_cid) ) {
								var cindex = this.SelectLpuRegionCombo.getStore().findBy(function(rec) {
									return (rec.get('LpuRegion_id') == LpuRegion_cid && rec.get('LpuRegionType_SysNick').inlist(arr));
								});
								if ( cindex == -1 ) {
									this.SelectLpuRegionCombo.clearValue();
								}
							} else {
								if(!Ext.isEmpty(LpuRegion_id)){
									var index = this.SelectLpuRegionCombo.getStore().findBy(function(rec) {
										return (rec.get('LpuRegion_id') == LpuRegion_id && rec.get('LpuRegionType_SysNick').inlist(arr));
									});
									if(index >= 0){
										this.SelectLpuRegionCombo.setValue(LpuRegion_id);
										this.SelectLpuRegionCombo.fireEvent('select',this.SelectLpuRegionCombo,LpuRegion_id);
									}
								}
							}
							if(Ext.isEmpty(this.SelectLpuRegionCombo.getValue()) && this.LpuRegion_id){
								var index = this.SelectLpuRegionCombo.getStore().findBy(function(rec) {
									return (rec.get('LpuRegion_id') == win.LpuRegion_id && rec.get('LpuRegionType_SysNick').inlist(arr));
								});
								if(index >= 0){
									this.SelectLpuRegionCombo.setValue(win.LpuRegion_id);
									this.SelectLpuRegionCombo.fireEvent('select',this.SelectLpuRegionCombo,win.LpuRegion_id);
								}
							}
						}.createDelegate(this)
						if(callProf == 2){
							this.SelectLpuRegionCombo.setAllowBlank(true);
							var arr = ['stom'];
						} else {
							var arr = ['ped','ter','vop','op'];
							this.SelectLpuRegionCombo.setAllowBlank(false);
							if(this.PersonInfoPanel.DataView.getStore().data.length == 0){
								this.PersonInfoPanel.load({
									Person_id: base_form.findField('Person_id').getValue(),
									Server_id: base_form.findField('Server_id').getValue(),
									callback: function() {
										var age = win.PersonInfoPanel.DataView.getStore().getAt(0).get('Person_Age');
										if(age<18){
											arr = ['ped','vop','op'];
										} else {
											arr = ['ter','vop','op'];
										}
										if(getRegionNick() == 'kz')
											arr.push('pmsp'); // #153492 для Казахстана участки с типом ПМСП
										localFiltering(arr);
									}.createDelegate(this) 
								});
							} else {
								var age = this.PersonInfoPanel.DataView.getStore().getAt(0).get('Person_Age');
								if(age<18){
									arr = ['ped','vop','op'];
								} else {
									arr = ['ter','vop','op'];
								}
							}
							if(getRegionNick() == 'kz')
								arr.push('pmsp'); // #153492 для Казахстана участки с типом ПМСП
						}
						if(getRegionNick() == 'ufa'){
							this.SelectLpuRegionCombo.setAllowBlank(true);
						}
						localFiltering(arr);
					}.createDelegate(this)
				},
				url: C_LPUREGION_LIST
			}),
		
			tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegionType_Name} {LpuRegion_Name}</div></tpl>',
			triggerAction: 'all',
			valueField: 'LpuRegion_id',
			width: 500,
			xtype: 'swbaseremotecombo',
			onTrigger2Click: function() {
				this.clearValue();
			},
			trigger2Class: 'x-form-clear-trigger',
			listeners: {
				'select':function(combo,newval,oldval){
					if(newval && newval.data && newval.data.LpuRegion_id) {
						var LpuRegion_id = newval.data.LpuRegion_id;
						var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
						MedStaffFact.clearValue();
						var win = this;
						var lpu = (this.SelectLpuCombo.getValue())?this.SelectLpuCombo.getValue():getGlobalOptions().lpu_id;
						var HomeVisitDate = this.MainPanel.getForm().findField('HomeVisit_setDate').getValue();
						this.medstafffact_filter_params = { isDoctor:true, Lpu_id:lpu, withLpuRegionOnly:true, HomeVisitDate:HomeVisitDate };
						var callProf = this.MainPanel.getForm().findField('CallProfType_id').getValue();
						var LpuRegionType_HomeVisit = '';
						switch(callProf){
							case 1:
							LpuRegionType_HomeVisit = 'terpedvop';
							break;
							case 2:
							LpuRegionType_HomeVisit = 'stom';
							break;
							default:
							LpuRegionType_HomeVisit = '';
							break;
						}
						if(LpuRegionType_HomeVisit.length > 0){
							this.medstafffact_filter_params.LpuRegionType_HomeVisit = LpuRegionType_HomeVisit;
							if (LpuRegionType_HomeVisit == 'terpedvop') {
								var callType = this.MainPanel.getForm().findField('HomeVisitCallType_id').getValue();
								if (callType == 6){
									this.medstafffact_filter_params.HomeVisit_onlySpecs = true;
								}
							}
						}
						if(getRegionNick().inlist(['kareliya','ekb'])){
							this.medstafffact_filter_params.isDoctor = false;
						}
						if(swMedStaffFactGlobalStore.data.length == 0 || lpu != getGlobalOptions().lpu_id){
							var params = {};
							if(lpu != getGlobalOptions().lpu_id){
								params = {Lpu_id:lpu,mode:'combo'};
							}
							MedStaffFact.getStore().load({
								params: params,
								callback:function(){
									win.filterMSF(LpuRegion_id,MedStaffFact.getStore());
								}
							});
						} else {
							win.filterMSF(LpuRegion_id);
						}
					}
				}.createDelegate(this)
			}
		});
		
		this.SymptomsFiels = new Ext.form.TwinTriggerField({
			allowBlank: false,
			anchor : "98%",
			readOnly : true,
			forceSelection: true,
			id : 'HVAW_Symptoms',
			name: 'HomeVisit_Symptoms',
			tabIndex: TABINDEX_HVAW + 6,
			fieldLabel: lang['simptomyi'],
			typeAhead: false,
			trigger1Class: 'x-form-search-trigger',
			trigger2Class: 'x-form-clear-trigger',
			firsttime: true,
			onTrigger1Click: function() {
				var form = getWnd('swHomeVisitAddWindow').MainPanel.getForm();
				var callProf = form.findField('CallProfType_id').getValue();
				getWnd('swHomeVisitSymptomsTreeWindow').show({
					callback: function() {
						var form = getWnd('swHomeVisitAddWindow').MainPanel.getForm();
						var callProf = form.findField('CallProfType_id').getValue();
						this.setValue(getWnd('swHomeVisitSymptomsTreeWindow').getSymptomsString(callProf));
						var symptoms_arr = getWnd('swHomeVisitSymptomsTreeWindow').getSymptomsArray(callProf);
						if ( callProf == 1 && getWnd('swHomeVisitAddWindow').checkForSMP(symptoms_arr) ) {
							form.findField('HomeVisitStatus_id').setValue(2);
							form.findField('HomeVisit_LpuComment').setDisabled(false);
							form.findField('MedStaffFact_id').allowBlank = true;
							form.findField('HomeVisit_LpuComment').setValue(lang['neobhodimost_vyizova_smp']);
							sw.swMsg.alert(lang['vnimanie'], lang['pri_dannyih_simptomah_neobhodimo_vyizyivat_skoruyu_pomosch_po_telefonu'].replace("{SMP_PHONE}", getGlobalOptions().homevizit_smp_phone));
						} else if ( callProf == 1 && getWnd('swHomeVisitAddWindow').checkForNMP(symptoms_arr) ) {
							form.findField('HomeVisitStatus_id').setValue(2);
							form.findField('HomeVisit_LpuComment').setDisabled(false);
							form.findField('MedStaffFact_id').allowBlank = true;
							form.findField('HomeVisit_LpuComment').setValue(lang['neobhodimost_vyizova_neotlojnoy_pomoschi']);
							sw.swMsg.alert(lang['vnimanie'], lang['pri_dannyih_simptomah_neobhodimo_obratitsya_v_slujbu_neotlojnoy_pomoschi_po_telefonu'].replace("{NMP_PHONE}", getGlobalOptions().homevizit_nmp_phone));
						} else {
							form.findField('HomeVisitStatus_id').setValue(1);
							form.findField('HomeVisit_LpuComment').setDisabled(true);
							form.findField('MedStaffFact_id').allowBlank = true;
							form.findField('HomeVisit_LpuComment').setValue('');
						}
						if (callProf == 1 && getWnd('swHomeVisitAddWindow').checkForCVI(symptoms_arr)) {
							form.findField('HomeVisit_isQuarantine').setValue(true);
							form.findField('HomeVisit_isQuarantine').setDisabled(true);
						}
						this.firsttime = false;
					}.createDelegate(this),
					firsttime: this.firsttime,
					callProf: callProf
				});
			},
			onTrigger2Click: function() {
				this.SymptomsFiels.setValue('');
				this.SymptomsFiels.firsttime = true;
				var form = this.MainPanel.getForm();
				form.findField('HomeVisit_isQuarantine').setValue(false);
				form.findField('HomeVisit_isQuarantine').setDisabled(false);
				this.isCVI = false;
			}.createDelegate(this)
		});
		
		this.MainPanel = new sw.Promed.FormPanel({
			id:'HomeVisitAddMainPanel',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			layout: 'fit',
			items: [{
				layout: 'form',
				labelWidth: 105,
				items: [
				/** Скрытые поля для сохранения анкеты КВИ */
				{
					xtype: 'fieldset',
					id: 'ApplicationCVI',
					hidden: true,
					defaults: {
						xtype: 'hidden'
					},
					items: [
						{name: 'PlaceArrival_id'},
						{name: 'isSavedCVI'},
						{name: 'CVICountry_id'},
						{name: 'OMSSprTerr_id'},
						{name: 'ApplicationCVI_arrivalDate'},
						{name: 'ApplicationCVI_flightNumber'},
						{name: 'ApplicationCVI_isContact'},
						{name: 'ApplicationCVI_isHighTemperature'},
						{name: 'Cough_id'},
						{name: 'Dyspnea_id'},
						{name: 'ApplicationCVI_Other'}
					]
				},
				//
				{
					name: 'Person_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'Server_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'HomeVisit_id',
					xtype: 'hidden',
					value: 0
				},
				{
					name: 'CmpCallCard_id',
					xtype: 'hidden',
					value: 0
				},
				{
					name: 'LpuRegion_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'KLCountry_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'KLRgn_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'KLSubRgn_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'KLCity_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'KLTown_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'KLStreet_id',
					xtype: 'hidden',
					value: null
				},
				{
					name: 'Address_House',
					xtype: 'hidden',
					value: ''
				},
				{
					name: 'Address_Corpus',
					xtype: 'hidden',
					value: ''
				},
				{
					name: 'Address_Flat',
					xtype: 'hidden',
					value: ''
				},
				{
					comboSubject: 'CallProfType',
					width: 300,
					xtype: 'swcommonsprcombo',
					fieldLabel: lang['profil_vyizova'],
					hiddenName:'CallProfType_id',
					allowBlank: false,
					listeners: {
						'change':function(comp,newval){
							if(newval == 2){
								this.MainPanel.getForm().findField('Person_Attach').setValue(this.Person_AttachS);
							} else {
								this.MainPanel.getForm().findField('Person_Attach').setValue(this.Person_Attach);
							}
							var Lpu_id = this.SelectLpuCombo.getValue();
							var setDate = this.MainPanel.getForm().findField('HomeVisit_setDate').getValue();
							var lpuList = this.StomLpuList;
							// Добавляем фильтрацию списка МО
							// @task https://redmine.swan.perm.ru/issues/77243
							this.SelectLpuCombo.lastQuery = '';
							this.SelectLpuCombo.getStore().clearFilter();

							if ( !Ext.isEmpty(setDate) ) {
								this.SelectLpuCombo.setBaseFilter(function(rec) {
									return (
										(Ext.isEmpty(rec.get('Lpu_BegDate')) || getValidDT(rec.get('Lpu_BegDate'), '') <= setDate)
										&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || getValidDT(rec.get('Lpu_EndDate'), '') >= setDate)
										&& ((newval == 2) ? rec.get('Lpu_id').inlist(lpuList) : true)
									);
								});
							}

							if ( !Ext.isEmpty(Lpu_id) ) {
								var index = this.SelectLpuCombo.getStore().findBy(function(rec) {
									return (rec.get('Lpu_id') == Lpu_id);
								});

								if ( index == -1 ) {
									this.SelectLpuCombo.clearValue();
									this.SelectLpuRegionCombo.clearValue();
									this.SelectLpuRegionCombo.getStore().removeAll();
								} else {
									this.SelectLpuCombo.fireEvent('select', this.SelectLpuCombo, Lpu_id, 0);
								}
							}

							var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
							MedStaffFact.clearValue();
							var win = this;
							var lpu = (this.SelectLpuCombo.getValue())?this.SelectLpuCombo.getValue():getGlobalOptions().lpu_id;
							var HomeVisitDate = this.MainPanel.getForm().findField('HomeVisit_setDate').getValue();
							this.medstafffact_filter_params = { isDoctor:true, Lpu_id:lpu, withLpuRegionOnly:true, HomeVisitDate:HomeVisitDate };
							var callProf = newval;
							var LpuRegionType_HomeVisit = '';
							switch(callProf){
								case 1:
								LpuRegionType_HomeVisit = 'terpedvop';
								break;
								case 2:
								LpuRegionType_HomeVisit = 'stom';
								break;
								default:
								LpuRegionType_HomeVisit = '';
								break;
							}
							if(LpuRegionType_HomeVisit.length > 0){
								this.medstafffact_filter_params.LpuRegionType_HomeVisit = LpuRegionType_HomeVisit;
								if (LpuRegionType_HomeVisit == 'terpedvop') {
									var callType = this.MainPanel.getForm().findField('HomeVisitCallType_id').getValue();
									if (callType == 6){
										this.medstafffact_filter_params.HomeVisit_onlySpecs = true;
									}
								}
							}
							if(getRegionNick().inlist(['kareliya','ekb'])){
								this.medstafffact_filter_params.isDoctor = false;
							}
							if(swMedStaffFactGlobalStore.data.length == 0 || lpu != getGlobalOptions().lpu_id){
								var params = {};
								if(lpu != getGlobalOptions().lpu_id){
									params = {Lpu_id:lpu,mode:'combo'};
								}
								MedStaffFact.getStore().load({
									params: params,
									callback:function(){
										win.filterMSF(false,MedStaffFact.getStore());
									}
								});
							} else {
								win.filterMSF();
							}

							this.SymptomsFiels.setValue('');
						}.createDelegate(this)
					}
				},
				this.AddressField,
				{
					disabled: true,
					fieldLabel: lang['prikreplenie'],
					name: 'Person_Attach',
					width: 500,
					xtype: 'textfield'
				},
				{
					xtype: 'panel',
					layout: 'column',
					border: false,
					items:
					[{
						xtype: 'panel',
						layout: 'form',
						labelWidth: 105,
						border: false,
						items: 
						[{
							comboSubject: 'HomeVisitCallType',
							fieldLabel: lang['tip_vyizova'],
							hiddenName: 'HomeVisitCallType_id',
							valueField: 'HomeVisitCallType_id',
							width: 150,
							tpl: '<tpl for="."><div class="x-combo-list-item">{HomeVisitCallType_Name}&nbsp;</div></tpl>',
							xtype: 'swcommonsprcombo',
							allowBlank: false,
							listeners: {
								'select':  function(combo, record, index) {
									if (combo.getValue().inlist([ '2', '3', '4', '6' ])) {
										this.MainPanel.getForm().findField('HomeVisitWhoCall_id').setValue(5);
										this.SymptomsFiels.setAllowBlank(true);
										this.MainPanel.getForm().findField('HomeVisit_setDate').enable();
										this.MainPanel.getForm().findField('HomeVisit_setTime').enable();
									} else {
										this.SymptomsFiels.setAllowBlank(false);
										this.MainPanel.getForm().findField('HomeVisit_setDate').disable();
										this.MainPanel.getForm().findField('HomeVisit_setTime').disable();
									}
									var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
									MedStaffFact.clearValue();
									var win = this;
									var lpu = (this.SelectLpuCombo.getValue())?this.SelectLpuCombo.getValue():getGlobalOptions().lpu_id;
									var HomeVisitDate = this.MainPanel.getForm().findField('HomeVisit_setDate').getValue();
									this.medstafffact_filter_params = { isDoctor:true, Lpu_id:lpu, withLpuRegionOnly:true, HomeVisitDate:HomeVisitDate };
									var callProf = this.MainPanel.getForm().findField('CallProfType_id').getValue();
									var LpuRegionType_HomeVisit = '';
									switch(callProf){
										case 1:
										LpuRegionType_HomeVisit = 'terpedvop';
										break;
										case 2:
										LpuRegionType_HomeVisit = 'stom';
										break;
										default:
										LpuRegionType_HomeVisit = '';
										break;
									}
									if(LpuRegionType_HomeVisit.length > 0){
										this.medstafffact_filter_params.LpuRegionType_HomeVisit = LpuRegionType_HomeVisit;
										if (LpuRegionType_HomeVisit == 'terpedvop') {
											var callType = combo.getValue();
											if (callType == 6){
												this.medstafffact_filter_params.HomeVisit_onlySpecs = true;
											}
										}
									}
									if(getRegionNick().inlist(['kareliya','ekb'])){
										this.medstafffact_filter_params.isDoctor = false;
									}
									if(swMedStaffFactGlobalStore.data.length == 0 || lpu != getGlobalOptions().lpu_id){
										var params = {};
										if(lpu != getGlobalOptions().lpu_id){
											params = {Lpu_id:lpu,mode:'combo'};
										}
										MedStaffFact.getStore().load({
											params: params,
											callback:function(){
												win.filterMSF(false,MedStaffFact.getStore());
											}
										});
									} else {
										win.filterMSF();
									}
								}.createDelegate(this)
							}
						}]
					},
					{
						xtype: 'panel',
						layout: 'form',
						labelWidth: 80,
						border: false,
						items: 
						[{
							fieldLabel: lang['data_vyizova'],
							width: 95,
							name: 'HomeVisit_setDate',
							xtype: 'swdatefield',
							allowBlank: false,
							listeners: {
								'change': function(combo, newValue, oldValue) {
									if ( newValue < getValidDT(getGlobalOptions().date, '')) {
										combo.setValue(oldValue);
										sw.swMsg.alert(lang['oshibka'], lang['data_ne_mojet_byit_menshe_tekushey']);
										return false;
									}

									var Lpu_id = this.SelectLpuCombo.getValue();
									var callProf = this.MainPanel.getForm().findField('CallProfType_id').getValue();
									var lpuList = this.StomLpuList;
									// Добавляем фильтрацию списка МО
									// @task https://redmine.swan.perm.ru/issues/77243
									this.SelectLpuCombo.lastQuery = '';
									this.SelectLpuCombo.getStore().clearFilter();

									if ( !Ext.isEmpty(newValue) ) {
										this.SelectLpuCombo.setBaseFilter(function(rec) {
											return (
												(Ext.isEmpty(rec.get('Lpu_BegDate')) || getValidDT(rec.get('Lpu_BegDate'), '') <= newValue)
												&& (Ext.isEmpty(rec.get('Lpu_EndDate')) || getValidDT(rec.get('Lpu_EndDate'), '') >= newValue)
												&& ((callProf == 2) ? rec.get('Lpu_id').inlist(lpuList) : true)
											);
										});
									}

									if ( !Ext.isEmpty(Lpu_id) ) {
										var index = this.SelectLpuCombo.getStore().findBy(function(rec) {
											return (rec.get('Lpu_id') == Lpu_id);
										});

										if ( index == -1 ) {
											this.SelectLpuCombo.clearValue();
											this.SelectLpuRegionCombo.clearValue();
											this.SelectLpuRegionCombo.getStore().removeAll();
										} else {
											this.SelectLpuCombo.fireEvent('select', this.SelectLpuCombo, Lpu_id, 0);
										}
									}
								}.createDelegate(this)
							}
						}]
					},
					{
						xtype: 'panel',
						layout: 'form',
						labelWidth: 95,
						border: false,
						items: 
						[{
							allowBlank: false,
							fieldLabel: lang['vremya_vyizova'],
							name: 'HomeVisit_setTime',
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							xtype: 'swtimefield'
						}]
					}]
				},
				this.NumeratorField,				
				this.SelectLpuCombo,
				this.SelectLpuRegionCombo,
				{
					fieldLabel:lang['vrach'],
					hiddenName:'MedStaffFact_id',
					xtype:'swmedstafffactglobalcombo',
					width: 500,
					listWidth:700,
					tabIndex: TABINDEX_HVAW + 3,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;">',
						'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
						'<td>',
							'<div style="font-weight: bold;">{MedPersonal_Fio}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
							'<div style="font-size: 10px;">{PostMed_Name}</div>',
						'</td>',
						'</tr></table>',
						'</div></tpl>'
					),
					anchor:'auto'
				},
				{
					allowBlank: false,
					readOnly : false,
					fieldLabel: langs('Телефон'),
					name: 'HomeVisit_Phone',
					tabIndex: TABINDEX_HVAW + 4,
					width: 500,
					xtype: 'textfield'
				},
				{
					allowBlank: false,
					fieldLabel: lang['kto_vyizyivaet'],
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB )
								field.fireEvent('blur', field);
						},
						'render': function() {
							this.getStore().load();
						}
					},
					name: 'HomeVisitWhoCall_id',
					tabIndex: TABINDEX_HVAW + 5,
					width: 500,
					xtype: 'swhomevisitwhocallcombo'
				},
				this.SymptomsFiels,
				{
					xtype: 'button',
					id: 'btnApplicationCVI',
					text: 'Анкета по КВИ',
					hidden: true,
					style: 'float:right',
					listeners: {
						click: function () {
							this.showApplicationCVI();
						}.createDelegate(this)
					}
				},
				{
					xtype: 'swcheckbox',
					name: 'HomeVisit_isQuarantine',
					fieldLabel: langs('Карантин'),
					listeners: {
						check: function (box, newValue) {log('<>---isCVI', this.isCVI);
							if (newValue && this.isCVI) {
								this.MainPanel.findById('btnApplicationCVI').setVisible(true);
								if (this.action == 'add') {
									this.showApplicationCVI();
								}
							} else {
								this.MainPanel.findById('btnApplicationCVI').setVisible(false);
							}
						}.createDelegate(this)
					}
				},
				{
					anchor: '100%',
					fieldLabel : lang['dopolnitelnaya_informatsiya_'],
					height: 100,
					name: 'HomeVisit_Comment',
					tabIndex: TABINDEX_HVAW + 7,
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				},
				{
					allowBlank: false,
					lastQuery: '',
					name: 'HomeVisitStatus_id',
					hiddenName: 'HomeVisitStatus_id',
					tabIndex: TABINDEX_HVAW + 8,
					xtype: 'swhomevisitstatuscomboshort',
					listeners: {
						'select': function(combo, record) {
							if(record.get('HomeVisitStatus_id')==1){
								this.MainPanel.getForm().findField('MedStaffFact_id').setValue('');
							}
							this.MainPanel.getForm().findField('HomeVisit_LpuComment').setDisabled( record.get('HomeVisitStatus_id') != 2 );
							this.MainPanel.getForm().findField('HomeVisit_LpuComment').setAllowBlank( record.get('HomeVisitStatus_id') != 2 );
							this.MainPanel.getForm().findField('MedStaffFact_id').allowBlank = ( record.get('HomeVisitStatus_id').inlist([3,6]));
						}.createDelegate(this)
					}
				},
				{
					disabled: true,
					anchor: '100%',
					fieldLabel : lang['prichina_otkaza'],
					height: 100,
					name: 'HomeVisit_LpuComment',
					tabIndex: TABINDEX_HVAW + 9,
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}]
			}],
			reader: new Ext.data.JsonReader({
				//
			}, [
				{ name: 'Person_id' },
				{ name: 'Server_id' },
				{ name: 'KLCountry_id' },
				{ name: 'KLRgn_id' },
				{ name: 'KLSubRgn_id' },
				{ name: 'KLCity_id' },
				{ name: 'KLTown_id' },
				{ name: 'KLStreet_id' },
				{ name: 'Address_House' },
				{ name: 'Address_Corpus' },
				{ name: 'Address_Flat' },
				{ name: 'Address_Address' },
				{ name: 'Person_Attach' },
				{ name: 'LpuRegion_id' },
				{ name: 'Lpu_id' },
				{ name: 'HomeVisit_Num' },
				{ name: 'HomeVisit_Phone' },
				{ name: 'HomeVisit_id' },
				{ name: 'CmpCallCard_id' },
				{ name: 'CallProfType_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'HomeVisitCallType_id' },
				{ name: 'HomeVisit_setDate' },
				{ name: 'HomeVisit_setTime' },
				{ name: 'HomeVisitStatus_id' },
				{ name: 'HomeVisitWhoCall_id' },
				{ name: 'HomeVisit_Symptoms' },
				{ name: 'HomeVisit_Comment' },
				{ name: 'HomeVisit_LpuComment' },
				{ name: 'HomeVisit_isQuarantine' },
				{name: 'PlaceArrival_id'},
				{name: 'isSavedCVI'},
				{name: 'CVICountry_id'},
				{name: 'OMSSprTerr_id'},
				{name: 'ApplicationCVI_arrivalDate'},
				{name: 'ApplicationCVI_flightNumber'},
				{name: 'ApplicationCVI_isContact'},
				{name: 'ApplicationCVI_isHighTemperature'},
				{name: 'Cough_id'},
				{name: 'Dyspnea_id'},
				{name: 'ApplicationCVI_Other'}
			]),
			url: C_HOMEVISIT_ADD
		});
		
		this.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
			id: 'HVAW_PersonInfoFrame',
			title: lang['zagruzka'],
			collapsible: true,
			collapsed: true,
			plugins: [Ext.ux.PanelCollapsedTitle],
			floatable: false,
			border: true,
			titleCollapse: true,
			region: 'north'
		});

		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.PersonInfoPanel, this.MainPanel],
			buttons:
			[{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this),
				tabIndex: TABINDEX_HVAW + 10
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this),
				tabIndex: TABINDEX_HVAW + 11
			},
			{
				text: BTN_FRMCLOSE,
				iconCls: 'close16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this),
				onTabAction: function() {
					this.AddressField.focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_HVAW + 12
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swHomeVisitAddWindow.superclass.initComponent.apply(this, arguments);
	},
	
	checkForNMP: function (arr) {
		for (var i = 0; i < arr.length; i++) {
			el = arr[i];
			indexArr = [19, 24];
			if (getRegionNick() != 'vologda'){
				indexArr.push(7);
			}
			if (indexArr.indexOf(parseInt(el)) !== -1) {
				return true;
			}
		}
		
		if ( arr.indexOf("12") !== -1 && arr.indexOf("32") !== -1 ) {  // Рвота + онкозаболевание
			return true;
		}
		
		return false;
	},
	
	checkForSMP: function (arr) {
		for (var i = 0; i < arr.length; i++) {
			el = parseInt(arr[i]);
			if ([8, 20, 22, 38, 42, 46, 47].indexOf(el) !== -1) {
				return true;
			}
		}
		if (arr.indexOf("1") !== -1 ) { 
			if ( arr.indexOf("50") ) { // температура + недавние роды
				return true;
			}
			if ( arr.indexOf("25") !== -1 ) { // температура + сыпь
				return true;
			}
		}
		
		if ( arr.indexOf("12") !== -1 && arr.indexOf("25") !== -1 ) {  // Сыпь + рвота
			return true;
		}
		
		return false;
	},

	checkForCVI: function (arr) {
		this.isCVI = arr.includes('28');
		return this.isCVI;
	},

	showApplicationCVI: function () {
		var values = {};
		var form = this.MainPanel.getForm();
		if (!Ext.isEmpty(form.findField('PlaceArrival_id').getValue())) {
			this.MainPanel.findById('ApplicationCVI').findBy(function (obj) {
				values[obj.name] = obj.getValue();
			});
		}log('<>---PA', form.findField('PlaceArrival_id').getValue());
		getWnd('swApplicationCVIWindow').show({
			forObject: 'swHomeVisitAddWindow',
			fields: values,
			action: this.action == 'edit' ? 'view' : this.action
		});
	}
});