/**
 * swRegimeCreateWindow - Окно быстрого добавления режима
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Common.EMK
 * @author		GTP_fox
 * @access		public
 * @copyright	Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.PacketPrescrExt2.EvnCourseTreatInPacketWindowExt2', {
	/* свойства */
	alias: 'widget.EvnCourseTreatInPacketWindowExt2',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new new-packet-create-window EvnCourseTreatEditPanel',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	refId: 'EvnCourseTreatInPacketWindowExt2',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Назначение лекарственного средства',
	width: 850,
	height: 595,
	scrollable: 'y',
	parentPanel: {},
	parentCntr: {},
	mode: 'one',
	setValuesMode: true,
	requires: [
		'common.EMK.SpecificationDetail.OneDrugFormPanel'
	],
	listeners:{
		'hide':function(win){
			if(!win.saveLocalData && win.ofForms && win.ofForms == 'PacketPanelTreatmentStandards') win.callback(false);
		}
	},
	getForm: function(){
		var me = this,
			base_form = {};
		if(this.manyDrug)
			base_form = me.ManyDrugFormPanel.getForm();
		else
			base_form = me.OneDrugFormPanel.getForm();
		return base_form;
	},
	getDrugFormData: function(forTemplate)
	{
		var base_form,
			win = this,
			data = {};
		base_form = win.getForm();
		var mnn = base_form.findField('DrugComplexMnn_id'),
			mnn_rec = mnn.getSelectedRecord(),
			KolvoEd = base_form.findField('KolvoEd'),
			GoodsUnit_sid = base_form.findField('GoodsUnit_sid');
		if(!mnn_rec || Ext6.isEmpty(KolvoEd.getValue()) || Ext6.isEmpty(GoodsUnit_sid.getValue())){
			return false;
		}

		var DrugForm_Name = '';
		DrugForm_Name = mnn_rec.get('RlsClsdrugforms_Name');


		data.Drug_Name = mnn.getRawValue();
		data.Drug_id = base_form.findField('Drug_id').getValue() ||null;
		data.id = base_form.findField('id').getValue() ||null;
		data.DrugForm_Name = DrugForm_Name;
		data.DrugComplexMnn_id = mnn.getValue() || null;
		//data.DrugForm_Name = base_form.findField('DrugForm_Name').getValue();
		data.KolvoEd = base_form.findField('KolvoEd').getValue() || null;
		//data.DrugForm_Nick = thas.findById(thas.id +'_TreatDrugForm_Nick').text || null;
		data.Kolvo = base_form.findField('Kolvo').getValue() || null;
		//data.EdUnits_id = base_form.findField('EdUnits_id').getValue() || null;
		//data.EdUnits_Nick = base_form.findField('EdUnits_id').getRawValue() || null;
		data.GoodsUnit_id = base_form.findField('GoodsUnit_id').getValue() || null;
		data.GoodsUnit_Nick = base_form.findField('GoodsUnit_id').getRawValue() || null;
		//data.DrugComplexMnnDose_Mass = base_form.findField('DrugComplexMnnDose_Mass').getValue() || null;
		//data.DoseDay = base_form.findField('DoseDay').getValue() || null;
		//data.PrescrDose = base_form.findField('PrescrDose').getValue() || null;
		//data.GoodsUnit_id = base_form.findField('GoodsUnit_id').getValue() || null;
		data.GoodsUnit_sid = base_form.findField('GoodsUnit_sid').getValue() || null;
		data.GoodsUnit_SNick = base_form.findField('GoodsUnit_sid').getRawValue() || null;
		data.EvnCourseTreat_CountDay = base_form.findField('EvnCourseTreat_CountDay').getValue() || null;
		data.EvnCourseTreat_Duration = base_form.findField('EvnCourseTreat_Duration').getValue() || null;
		data.DurationType_id = base_form.findField('DurationType_id').getValue() || null;
		data.LatName = base_form.findField('LatName').getValue() || null;
		data.MethodInputDrug_id = data.Drug_id?'2':'1';
		data.FactCount = 0;
		if(!forTemplate){
			var id = base_form.findField('id').getValue();
			/*if(!isNaN(parseInt(id))){
				data.id = id || '';
			}*/
			log('id: '+data.id);
			data.status = data.id?'updated':'new';
		}
		data.EdUnits_id = null;
		data.EdUnits_Nick = null;
		data.DrugComplexMnnDose_Mass = null;
		data.DoseDay = (parseInt(data.Kolvo) * parseInt(data.EvnCourseTreat_CountDay)).toString()+data.GoodsUnit_Nick;
		//data.PrescrDose = parseInt(data.Kolvo) * parseInt(base_form.findField('EvnCourseTreat_CountDay').getValue())*(Продолжительность)*месяц(30дней)
		data.DrugForm_Nick =  base_form.findField('DrugComplexMnn_id').getSelectedRecord().get('RlsClsdrugforms_Name') || '';
		data = win.OneDrugFormPanel.reCountData(data);

		win.getDrugInteractionsDescription(data.DrugComplexMnn_id);
		
		return data;
	},
	/**
	 * Загружает на форму запись из общего грида с лек. назначениями по индексу либо первый (единственный)
	 * @param index {number|boolean}
	 * @param cb {function|boolean}
	 */
	loadTreat: function(index,cb){

		var me = this,
			store = me.TreatDrugListGrid.getStore(),
			base_form = me.getForm(),
			drug_field = base_form.findField('Drug_id');
		if(!index)
			index = 0;
		if(store.getCount() > 0){
			
			var rec = me.TreatDrugListGrid.getStore().getAt(index),
				data = rec.getData();
			if (!Ext6.isEmpty(data.DrugComplexMnn_id)) {
				Ext6.Ajax.request({
					url: '/?c=PersonAllergicReaction&m=checkPersonAllergicReaction',
					callback: function (opt, success, response) {
						
						if (response && response.responseText) {
							var check = Ext6.JSON.decode(response.responseText);
							me.OneDrugFormPanel.setErrorPanelVisible(!check);
						}
					},
					params: {
						Person_id: me.data.Person_id,
						DrugComplexMnn_id: data.DrugComplexMnn_id
					}
				});
				// прогружаем нужный МНН
				base_form.findField('DrugComplexMnn_id').getStore().load({
					params: {
						DrugComplexMnn_id: data.DrugComplexMnn_id
					},
					callback: function () {
						
						drug_field.getStore().proxy.extraParams.DrugComplexMnn_id = rec.get('DrugComplexMnn_id');
						drug_field.lastQuery = 'This query sample that is not will never appear';
						if(data.Drug_id){
							drug_field.getStore().load({
								params: {
									Drug_id: data.Drug_id
								},
								callback: function () {
									base_form.setValues({
										'DrugComplexMnn_id': data.DrugComplexMnn_id,
										'Drug_id': data.Drug_id
									});
									if(typeof cb === 'function') cb();
								}
							});
						}
						else{
							base_form.setValues({
								'DrugComplexMnn_id': data.DrugComplexMnn_id
							});
							drug_field.getStore().load({params: {DrugComplexMnn_id: data.DrugComplexMnn_id}});
							if(typeof cb === 'function') cb();
						}
					}
				});
				base_form.setValues({'id': index});
			}
			
			if(me.values)
				me.values = Ext6.Object.merge(me.values,data);
			else me.values = data;
			me.setValuesFromData(cb);
			me.unmask();
		}
	},
	show: function (data) {
		this.setValuesMode = true; // режим автоматического изменения данных формы
		this.callParent(arguments);
		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		if (Ext6.isEmpty(data.rec)
			|| (Ext6.isEmpty(data.rec.Drug_id) && Ext6.isEmpty(data.rec.DrugComplexMnn_id) && Ext6.isEmpty(data.rec.PacketPrescrTreat_id))) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствует препарат.');
			return false;
		}
		var me = this;
		me.data = data;
		me.parentPanel = data.parentPanel;
		me.parentCntr = data.parentCntr;
		me.callback = arguments[0].callback?arguments[0].callback:Ext6.emptyFn;
		me.PacketPrescr_id = data.PacketPrescr_id?data.PacketPrescr_id:null;

		me.ofForms = (data.ofForms) ? data.ofForms : null;
		me.saveLocalData = false;

		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=checkPersonAllergicReaction',
			params: {
				Person_id: me.data.Person_id,
				DrugComplexMnn_id: data.rec.DrugComplexMnn_id
			},
			callback: function (opt, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result === true) {
					//Есть аллергический запрет
					Ext6.MessageBox.show({
						title: 'Внимание!',
						msg: 'У пациента выявлена аллергическая реакция на данный препарат!',
						buttons: Ext6.Msg.OK,
						icon: Ext6.Msg.WARNING
					});
				}
			}
		});
		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=checkPersonDrugReaction',
			params: {
				Evn_id: me.data.Evn_id,
				Person_id: me.data.Person_id,
				DrugComplexMnn_id: data.rec.DrugComplexMnn_id
			},
			callback: function (opt, success, response) {
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.LS_LINK_ID) {
						Ext6.MessageBox.show({
							title: 'Внимание!',
							msg: 'У данного препарата есть взаимодействие с препаратом из назначений (' + result.DrugComplexMnn_RusName + '):<br> - ' + result.LS_EFFECT_NAME,
							buttons: Ext6.Msg.OK,
							icon: Ext6.Msg.WARNING
						});
					}
				}
			}
		});

		me.checkPersonDrugReactionInEvn(data.rec.DrugComplexMnn_id);

		me.mask('Подождите, идет загрузка...');
		var showFn = function(){
			me.reset();
			me.ModeToggler.setValue('one');
			me.loadValuesByRec(data.rec);
			if(me.ofForms) me.setLocalValuesFormData(data);
		};
		if(me.sprLoading){
			var components = me.query('combobox');
			me.loadDataLists(components,showFn);
		} else showFn();

		//me.getForm().findField('searchDrugNameCombo').focus();
		me.setValuesMode = false;
		me.unmask();
	},
	setMode: function(mode){
		var me = this, cb;
		me.TreatDrugListGrid.hide();
		switch(mode){
			case 'one':
				me.TabPanel.getLayout().setActiveItem(0);
				me.manyDrug = false;
				break;
			case 'many':
				var drug_data = me.OneDrugFormPanel.getDrugFormData();
				cb = function(){
					me.values = drug_data;
					me.setValuesFromData();
				};
				me.TabPanel.getLayout().setActiveItem(1);
				me.manyDrug = true;
				me.TreatDrugListGrid.show();
				break;
			default:
				me.TabPanel.getLayout().setActiveItem(0);
				me.manyDrug = false;
				mode = 'one';
		}
		//var base_form = me.getForm();
		//base_form.findField('searchDrugNameCombo').focus();
		me.ModeToggler.setValue(mode);
		me.mode = mode;
		//saveBtn.disable();
		me.reset(cb);
	},
	/**
	 * Функция нужна, чтобы позволить сохраниться форме составного лек. назначения,
	 * при очищенных обязательных полях после добавления в курс
	 * @param allowBlank
	 */
	setAllowBlank: function(allowBlank){
		var me = this,
			base_form = me.getForm();
		base_form.findField('DrugComplexMnn_id').allowBlank = allowBlank;
		base_form.findField('KolvoEd').allowBlank = allowBlank;
		base_form.findField('GoodsUnit_sid').allowBlank = allowBlank;
	},
	loadValuesIntoForm: function(PacketPrescrTreat_id){
		var me = this;
		// Дополним значения пришедшей записью
		var url = '/?c=PacketPrescr&m=loadPacketCourseTreatEditForm';
		var params = {
			PacketPrescrTreat_id: PacketPrescrTreat_id
		};
		Ext6.Ajax.request({
			url: url,
			params: params,
			callback: function(opt, success, response) {
				if (success && response && response.responseText) {
					var dec_data = Ext6.JSON.decode(response.responseText),
						data = dec_data[0],
						drugList = data.DrugListData = Ext6.JSON.decode(data.DrugListData);
					if(!me.values) me.values = {};
					me.values = Ext6.Object.merge(me.values, data, drugList[0]);
					me.setValuesFromData();
					me.setValuesMode = false; // Далее форма будет изменяться вручную
					me.OneDrugFormPanel.changeMethodText('force'); // Устанавливаем текст способа применения
				}
				else{
					me.unmask();
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'));
				}
			}
		});

	},
	setValuesFromData: function(cb){
		var me = this,
			base_form = me.getForm(),
			drug_field = base_form.findField('Drug_id'),
			fieldsVals = base_form.getFieldValues(),
			exceptionFields = [
				'PrescriptionIntroType_id',
				'EvnCourseTreat_setDate',
				'EvnCourseTreat_Duration',
				'DurationType_id',
				'EvnCourseTreat_CountDay',
				'PerformanceType_id',
				'EvnPrescrTreat_Descr',
				'EvnPrescrTreat_IsCito'
			];
		if(me.values){
			if (!Ext6.isEmpty(me.values.DrugComplexMnn_id)) {
				// прогружаем нужный МНН
				base_form.findField('DrugComplexMnn_id').getStore().load({
					params: {
						DrugComplexMnn_id: me.values.DrugComplexMnn_id
					},
					callback: function () {
						drug_field.getStore().proxy.extraParams.DrugComplexMnn_id = me.values.DrugComplexMnn_id;
						drug_field.lastQuery = 'This query sample that is not will never appear';
						if(cb && typeof cb === 'function'){ cb();}
						if(me.values.Drug_id){
							drug_field.getStore().load({
								params: {
									Drug_id: me.values.Drug_id
								},
								callback: function () {
									
									base_form.setValues({
										'DrugComplexMnn_id': me.values.DrugComplexMnn_id,
										'Drug_id': me.values.Drug_id
									});
								}
							});
						}
						else{
							
							base_form.setValues({
								'DrugComplexMnn_id': me.values.DrugComplexMnn_id
							});
							drug_field.getStore().load({params: {DrugComplexMnn_id: me.values.DrugComplexMnn_id}});
						}
					}
				});
			}
			Ext6.Object.each(fieldsVals, function (key, value, myself) {
				// @todo разобраться, когда все-таки заменять старые значения на новые пустые
				if(me.values[key])
					fieldsVals[key] = me.values[key];
				else {
					if(!me.manyDrug && !key.inlist(exceptionFields)){
						delete fieldsVals[key];
					} 
				}
			});
			me.setFormIsDirty(base_form);
			
			base_form.setValues(fieldsVals);
		}
	},
	setLocalValuesFormData: function(data){
		// заполнение полей локальными данными
		var me = this;
		if(me.ofForms && me.ofForms == 'PacketPanelTreatmentStandards' && data.rec && data.localRecordDataForm){
			me.values = Ext6.Object.merge(data.rec, data.localRecordDataForm);
			me.setValuesFromData();
		}
	},
	doSaveLocalData: function(params){
		//сохранение параметров формы локально
		var me = this;
		if(me.ofForms && me.ofForms == 'PacketPanelTreatmentStandards'){
			var params = params || null;
			var base_form = me.getForm();
			if(params == null) {
				sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении данных формы'));
				return true;
			}
			me.unmask();
			me.formStatus = 'edit';

			params.DrugComplexMnn_Name = base_form.findField('DrugComplexMnn_id').getRawValue('DrugComplexMnn_Name');
			me.saveLocalData = true;
			me.callback(params);
			me.reset();
			me.hide();
			return true;
		}else{
			return false;
		}
	},
	doSave: function (withPrint) {
		var me = this,
			base_form = me.getForm();

		if(!me.data && me.parentPanel)
			me.data = me.parentPanel.getController().getData();

		//костыль, при сохранении, не могут быть пустыми поля, которые сохранены в составляющих курса, а сами очищены
		if (me.manyDrug)
			me.setAllowBlank(true);
		
		if ( !base_form.isValid() ) {
			Ext6.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING
			});
			return;
		}
		if ( me.formStatus == 'save' ) {
			return false;
		}
		me.formStatus = 'save';
		me.mask('Сохранение лекарственного назначения...');

		var arr = [];
		
		if(me.manyDrug){
			var store = me.TreatDrugListGrid.getStore(),
				filters = store.getFilters();
			if (base_form.findField('DrugComplexMnn_id').getValue()) {
				me.TreatDrugListGrid.saveRecord();
				me.clearDrugNames();
			}
			filters.removeAll();
			store.each(function(rec) {
				// 2) Вид массива в конце файла
				//rec.data.MethodInputDrug_id = '1';
				if(rec.data['status'] == 'new')
					rec.data.id = null;
				arr.push(rec.data);
			});
		} else {
			var drug_data = me.OneDrugFormPanel.getDrugFormData();
			arr.push(drug_data);
		}
		


		if(!(arr.length>0)){
			me.unmask();
			me.formStatus = 'edit';
			sw.swMsg.alert(langs('Ошибка'), langs('Не добавлены медикаменты'));
			return false;
		}

		var DrugListData = Ext.util.JSON.encode(arr),
			values = base_form.getValues(),
			params = {
				parentEvnClass_SysNick: 'EvnVizitPL',
				signature: 0,
				accessType: '',
				DrugListData: DrugListData, // 1) Вид массива в конце файла
				EvnCourseTreat_id: base_form.findField('EvnCourseTreat_id').getValue || null,
				EvnCourseTreat_pid: me.data.Evn_id,
				MedPersonal_id: me.data.MedPersonal_id,
				LpuSection_id: me.data.LpuSection_id,
				Morbus_id: '',
				PrescriptionTreatType_id: '',
				PersonEvn_id: me.data.PersonEvn_id,
				Server_id: me.data.Server_id,
				PrescriptionStatusType_id: '',
				PrescriptionIntroType_id: 1,
				EvnCourseTreat_setDate: base_form.findField('EvnCourseTreat_setDate').getRawValue().toString(),
				EvnCourseTreat_CountDay: 1,
				EvnCourseTreat_Duration: 1,
				EvnCourseTreat_ContReception: 1,
				EvnCourseTreat_Interval: 0,
				DurationType_id: 1,
				DurationType_recid: 1,
				DurationType_intid: 1,
				PerformanceType_id: 1,
				EvnPrescrTreat_Descr: '',
				EvnPrescrTreat_IsCito: '',
				//id:'',
				DrugForm_Name:'',
				DrugComplexMnnDose_Mass:null,
				MethodInputDrug_id: 'on',
				DrugComplexMnn_id: '',
				Drug_id:'',
				KolvoEd:'',
				GoodsUnit_sid:'',
				Kolvo:'',
				GoodsUnit_id:'',
				EdUnits_id:'',
				DoseDay:'',
				PrescrDose:''
			};
		Ext6.apply(params,values);
		
		if(me.doSaveLocalData(params)) return true;

		Ext6.Ajax.request({
			url: '/?c=EvnPrescr&m=saveEvnCourseTreat',
			params: params,
			callback: function (opt, success, response) {
				me.unmask();

				if (success && response && response.responseText) {
					var data = Ext6.JSON.decode(response.responseText);
					if (data.Error_Msg) {
						return;
					}
					me.formStatus = 'edit';
					if (data.EvnPrescrTreat_id0)
						data.EvnPrescr_id = data.EvnPrescrTreat_id0;
					sw4.showInfoMsg({
						panel: me,
						type: 'success',
						text: 'Данные сохранены.'
					});
					if (!me.callback)
						me.parentPanel.getController().loadGrids();
					else
						me.callback(data);
					me.reset();
					me.hide();
				}
				else {
					me.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении данных формы'));
				}
			}
		});
		return true;
	},
	saveTemplate: function(){
		var me = this,
			base_form = me.getForm();

		if(!me.data && me.parentPanel)
			me.data = me.parentPanel.getController().getData();

		//костыль, при сохранении, не могут быть пустыми поля, которые сохранены в составляющих курса, а сами очищены
		if (me.manyDrug)
			me.setAllowBlank(true);
		
		if ( !base_form.isValid() ) {
			Ext6.MessageBox.show({
				title: 'Проверка данных формы',
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING
			});
			return;
		}

		if ( me.formStatus == 'save' ) {
			return false;
		}

		me.formStatus = 'save';
		me.mask('Сохранение шаблона');
		
		var arr = [];
		if(me.manyDrug){
			var store = me.TreatDrugListGrid.getStore(),
				filters = store.getFilters();
			if (base_form.findField('DrugComplexMnn_id').getValue()) {
				me.TreatDrugListGrid.saveRecord();
				me.clearDrugNames();
			}
			filters.removeAll();
			store.each(function(rec) {
				// 2) Вид массива в конце файла
				//rec.data.MethodInputDrug_id = '1';
				if(rec.data['status'] == 'new')
					rec.data.id = null;
				arr.push(rec.data);
			});
		} else {
			arr = me.OneDrugFormPanel.getDrugFormData();
		}
		

		var DrugListData = Ext.util.JSON.encode(arr),
			values = base_form.getValues(),
			params = {
				manyDrug: me.manyDrug?'many':'one',
				PacketPrescr_id: me.PacketPrescr_id,
				parentEvnClass_SysNick: 'EvnVizitPL',
				signature: 0,
				accessType: '',
				DrugListData: DrugListData, // 1) Вид массива в конце файла
				EvnCourseTreat_id: base_form.findField('EvnCourseTreat_id').getValue || null,
				EvnCourseTreat_pid: me.data.EvnVizitPL_id,
				MedPersonal_id: me.data.MedPersonal_id,
				LpuSection_id: me.data.LpuSection_id,
				Morbus_id: '',
				PrescriptionTreatType_id: '',
				PersonEvn_id: me.data.PersonEvn_id,
				Server_id: me.data.Server_id,
				PrescriptionStatusType_id: '',
				PrescriptionIntroType_id: 1,
				EvnCourseTreat_setDate: base_form.findField('EvnCourseTreat_setDate').getRawValue().toString(),
				EvnCourseTreat_CountDay: 1,
				EvnCourseTreat_Duration: 1,
				EvnCourseTreat_ContReception: 1,
				EvnCourseTreat_Interval: 0,
				DurationType_id: 1,
				DurationType_recid: 1,
				DurationType_intid: 1,
				PerformanceType_id: 1,
				EvnPrescrTreat_Descr: '',
				EvnPrescrTreat_IsCito: '',
				DrugForm_Name:'',
				DrugComplexMnnDose_Mass:null,
				MethodInputDrug_id: 'on',
				DrugComplexMnn_id: '',
				Drug_id:'',
				KolvoEd:'',
				GoodsUnit_sid:'',
				Kolvo:'',
				GoodsUnit_id:'',
				EdUnits_id:'',
				DoseDay:'',
				PrescrDose:''
			};
		Ext6.apply(params,values);

		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=saveDrugTemplateToPacket',
			params: params,
			callback: function (opt, success, response) {
				me.unmask();

				if (success && response && response.responseText) {
					var data = Ext6.JSON.decode(response.responseText);
					if (data.Error_Msg) {
						return;
					}

					var cb = function(){
						me.formStatus = 'edit';
						if (data.EvnPrescrTreat_id0)
							data.EvnPrescr_id = data.EvnPrescrTreat_id0;
						sw4.showInfoMsg({
							panel: me,
							type: 'success',
							text: 'Данные сохранены.'
						});
						if (!me.callback)
							me.parentPanel.getController().loadGrids();
						else
							me.callback(data);
						me.reset();
						me.hide();
					};
					cb();
				}
				else {
					me.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении данных формы'));
				}
			}
		});
		return true;
	},
	sprLoading: true,
	onSprLoad: function(){
		this.sprLoading = false;
	},
	reset: function(cb){
		var me = this,
			store = me.TreatDrugListGrid.getStore(),
			fOne = me.OneDrugFormPanel.getForm(),
			fMany = me.ManyDrugFormPanel.getForm(),
			mpanel = me.OneDrugFormPanel.MethodPanel,
			methodText = mpanel.down('#methodText');
		store.removeAll();
		methodText.reset();
		// @todo это говно надо убрать
		// Но к сожалению Ext6 пока не может предложить альтернативу способа сделать форму не isDirty,
		// Но к сожалению Ext6 пока не может предложить альтернативу способа сделать форму не isDirty,
		me.setFormIsDirty(fOne);
		me.setFormIsDirty(fMany);
		me.loadDefaultValues();
		//Включаем подсветку
		fOne.isValid();
		fMany.isValid();
		if(cb && typeof cb === 'function'){cb()};
	},
	loadValuesByRec: function(rec){
		var me = this,
			form = me.getForm(),
			dcm = form.findField('DrugComplexMnn_id'),
			rp = form.findField('LatName'),
			drug = form.findField('Drug_id'),
			dr_id = rec.Drug_id,
			mnn_id = rec.DrugComplexMnn_id;
		dcm.getStore().load({
			params: {
				DrugComplexMnn_id: mnn_id
			},
			callback: function () {
				dcm.setValue(mnn_id);
			}
		});
		rp.setValue(rec.LatName);
		drug.getStore().proxy.extraParams.DrugComplexMnn_id = mnn_id;
		drug.getStore().proxy.extraParams.Drug_id = dr_id;
		drug.lastQuery = 'This query sample that is not will never appear';
		drug.getStore().load({
			params: {
				DrugComplexMnn_id: mnn_id,
				Drug_id: dr_id
			},
			callback: function () {
				drug.setValue(dr_id);
			}
		});
		me.setDefaultDrugPackValues(mnn_id,dr_id);

	},
	setDefaultDrugPackValues: function(mnn_id,dr_id) {
		var me = this,
			base_form = this.getForm();


		var object_name = 'DrugComplexMnn_id';
		var object_id = mnn_id;
		if(!Ext6.isEmpty(dr_id) && dr_id>0){
			object_name = 'Drug_id';
			object_id = dr_id;
		}
		if (Ext6.isEmpty(this.DrugPackData)) {
			this.DrugPackData = new Object();
		}
		if (Ext6.isEmpty(this.DrugPackData[object_name])) {
			this.DrugPackData[object_name] = new Object();
		}

		if (object_id > 0) {
			me.loadDrugPackData(
				object_name,
				object_id,
				function(response_obj) {
					base_form.findField('GoodsUnit_id').filterList = response_obj.DoseMass_GoodsUnit_id + ',' + response_obj.FasMass_GoodsUnit_id;
					me.DrugPackData[object_name][object_id] = response_obj;
					me.setDrugPackFields(me.DrugPackData[object_name][object_id]);
				}
			);
		}
	},
	setDrugPackFields: function(data) {
		var values = {
				Kolvo: null,
				KolvoEd: null,
				GoodsUnit_id: null,
				GoodsUnit_sid: null,
				hb: null
			},
			base_form = this.getForm();

		if (!Ext6.isEmpty(data)) {
			if (data.KolvoEd && data.KolvoEd > 0) {  // Если KolvoEd заполнено при редактировании
				values.KolvoEd = data.KolvoEd
			} else {
				values.KolvoEd = 1;
			}

			//Доза на 1 прием
			if (data.Written_Kolvo && data.Written_GoodsUnit_id) {
				values.Kolvo = data.Written_Kolvo;
				values.GoodsUnit_id = data.Written_GoodsUnit_id;
			} else if (data.Fas_Kolvo > 0 && data.DoseMass_Kolvo > 0) { //если указано значащее количество лекарственных форм в первичной упаковке
				values.Kolvo = data.DoseMass_Kolvo * values.KolvoEd;
				values.GoodsUnit_id = data.DoseMass_GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
			} else if (data.FasMass_Kolvo > 0) {
				values.Kolvo = data.FasMass_Kolvo * values.KolvoEd;
				values.GoodsUnit_id = data.FasMass_GoodsUnit_id;
			} else {  //  Оставляем прежние значения
				values.Kolvo = base_form.findField('Kolvo').getValue();
				values.GoodsUnit_id = base_form.findField('GoodsUnit_id').getValue();
			}

			values.GoodsUnit_sid = data.GoodsUnit_id; //в качестве ед. изм. - лекарственная форма
		}
		base_form.setValues(values);
	},
	/**
	 * ищем данные для данного медикамента среди ранее загруженых
	 * @param object_name
	 * @param object_id
	 * @param callback
	 */
	loadDrugPackData: function (object_name, object_id, callback) {
		var params = new Object();

		if (object_id > 0) {
			if (!Ext6.isEmpty(this.DrugPackData) && !Ext6.isEmpty(this.DrugPackData[object_name]) && !Ext6.isEmpty(this.DrugPackData[object_name][object_id])) {
				var response_obj = this.DrugPackData[object_name][object_id];
				callback(response_obj);
			} else {
				params[object_name] = object_id;
				Ext.Ajax.request({
					params: params,
					url: '/?c=EvnPrescr&m=getDrugPackData',
					callback: function(opt, scs, response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (!Ext.isEmpty(response_obj)) {
							callback(response_obj);
						}
					}
				});
			}
		}
	},
	/**
	 * Делаем форму form - чистой - т.е. не isDirty
	 * для этого нужно обнулить оригинальное значение и вернуть флаг "чистоты"!
	 * @param form
	 */
	setFormIsDirty: function(form){
		var items = form.getFields().items,
			len = items.length;
		for(var i = 0; i < len; i++) {
			var c = items[i];
			c.value = '';
			if(c.mixins && c.mixins.field && typeof c.mixins.field['initValue'] == 'function') {
				c.mixins.field.initValue.apply(c);
				c.wasDirty = false;
			}
		}
	},
	/**
	 * При добавлении нового лек. назначения заполняем некоторые поля
	 */
	loadDefaultValues: function(){
		var me = this,
			base_form = me.getForm(),
			fieldValues = {},
			today = new Date();

		fieldValues.PrescriptionIntroType_id = 1; // Способ применения пероральное введение
		fieldValues.EvnCourseTreat_setDate = today.format('d.m.Y'); // Начать "сегодня"
		fieldValues.EvnCourseTreat_CountDay = 1; // Приёмов в сутки
		fieldValues.PerformanceType_id = 1; // Исполнение: самостоятельно
		fieldValues.DurationType_id = 1; // Продолжительность по умолчанию - день
		base_form.setValues(fieldValues); // Чтобы они не попали под isDirty действия
	},
	onSelectRecSearchCombo: function(rec){
		var me = this,
			form = me.getForm(),
			dcm = form.findField('DrugComplexMnn_id'),
			rp = form.findField('LatName'),
			drug = form.findField('Drug_id'),
			dr_id = rec.get('Drug_id'),
			mnn_id = rec.get('DrugComplexMnn_id');
		
		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=checkPersonAllergicReaction',
			params: {
				Person_id: me.data.Person_id,
				DrugComplexMnn_id: mnn_id
			},
			callback: function (opt, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result === true) {
					//Есть аллергический запрет
					Ext6.MessageBox.show({
						msg: '<p>Внимание! У пациента выявлена аллергическая реакция на данный препарат!</p>',
						buttons: Ext6.Msg.OK,
						icon: Ext6.Msg.WARNING
					});
				}
			}
		});

		me.checkPersonDrugReactionInEvn(mnn_id);
		
		dcm.getStore().load({
			params: {
				DrugComplexMnn_id: mnn_id
			},
			callback: function () {
				dcm.setValue(mnn_id);
			}
		});
		rp.setValue(rec.get('LatName'));
		drug.getStore().proxy.extraParams.DrugComplexMnn_id = mnn_id;
		drug.getStore().proxy.extraParams.Drug_id = dr_id;
		drug.lastQuery = 'This query sample that is not will never appear';
		drug.getStore().load({
			params: {
				DrugComplexMnn_id: mnn_id,
				Drug_id: dr_id
			},
			callback: function () {
				drug.setValue(dr_id);
			}
		});
		me.setDefaultDrugPackValues(mnn_id,dr_id);

	},
	checkPersonDrugReactionInEvn: function(DrugComplexMnn_id){
		if(getRegionNick() == 'kz')
			return false
		
		var me = this;
		var EvnCourseTreat_setDate = me.getForm().findField('EvnCourseTreat_setDate').getValue();
		var store = me.TreatDrugListGrid.getStore();
		var data = store.getData().items;
		me.mask('Подождите, идет загрузка...');
		var DrugComplexMnn_ids = '';
		for(var i = 0; i < data.length; i++){
			DrugComplexMnn_ids = DrugComplexMnn_ids + data[i].data.DrugComplexMnn_id + ',';
		}
		DrugComplexMnn_ids = DrugComplexMnn_id != null ? DrugComplexMnn_ids + DrugComplexMnn_id : DrugComplexMnn_ids.slice(0, -1);

		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=checkPersonDrugReactionInEvn',
			params: {
				EvnCourseTreat_setDate: Ext.util.Format.date(EvnCourseTreat_setDate, 'Y-m-d'),
				Evn_id: me.data.Evn_id,
				Person_id: me.data.Person_id,
				DrugComplexMnn_id: DrugComplexMnn_id,
				DrugComplexMnn_ids: DrugComplexMnn_ids,
				PacketPrescr_id: me.PacketPrescr_id
			},
			callback: function (opt, success, response) {
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.Drug_Name) {
						Ext6.MessageBox.show({
							title: 'Внимание!',
							msg: 'При использовании препарата ' + result.Drug_Name + ' в комплексе с препаратом ' + result.AntagonistDrug_Names + ' возможны побочные эффекты',
							buttons: Ext6.Msg.OK,
							icon: Ext6.Msg.WARNING
						});
					}
					me.unmask();
				}
			}
		});
	},
	getDrugInteractionsDescription: function(DrugComplexMnn_id = null){
		if(getRegionNick() == 'kz') 
			return false
		
		var me = this;
		var EvnCourseTreat_setDate = me.getForm().findField('EvnCourseTreat_setDate').getValue();
		var store = me.TreatDrugListGrid.getStore();
		var data = store.getData().items;
		me.mask('Подождите, идет загрузка...');
		var DrugComplexMnn_ids = '';
		for(var i = 0; i < data.length; i++){
			DrugComplexMnn_ids = DrugComplexMnn_ids + data[i].data.DrugComplexMnn_id + ',';
		}
		DrugComplexMnn_ids = DrugComplexMnn_id != null ? DrugComplexMnn_ids + DrugComplexMnn_id : DrugComplexMnn_ids.slice(0, -1);

		if(DrugComplexMnn_ids == ''){
			me.unmask();
			return false;
		}
		
			
		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=getDrugInteractionsDescription',
			params: {
				EvnCourseTreat_setDate: Ext.util.Format.date(EvnCourseTreat_setDate, 'Y-m-d'),
				Evn_id: me.data.Evn_id,
				Person_id: me.data.Person_id,
				DrugComplexMnn_ids: DrugComplexMnn_ids,
				PacketPrescr_id: me.PacketPrescr_id
			},
			callback: function (opt, success, response) {
				if (response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.tpl) {
						for(var key in result.tpl){
							var store = me.TreatDrugListGrid.getStore();
							var data = store.getData().items;
							for(var i = 0; i < data.length;i++){
								var mnn_id = key.split('_');
								if(data[i].data.DrugComplexMnn_id == mnn_id[0]){
									me.TreatDrugListGrid.getStore().getData().items[i].data.Description = result.tpl[key];
								}
							}
						}
						me.TreatDrugListGrid.reconfigure();
					}
					me.unmask();
				}
			}
		});
	},
	/**
	 * Очищение полей способа применения по кнопке "Очистить"
	 */
	clearDrugNames: function(){
		var me = this,
			panel = me.ManyDrugFormPanel.down('#manyDrugNamesPanel'),
			fields = panel.query('field');
		fields.forEach(function (e) {
			e.value = '';
			if(e.mixins && e.mixins.field && typeof e.mixins.field['initValue'] == 'function') {
				e.mixins.field.initValue.apply(e);
				e.wasDirty = false;
			}
			if (e && e.reset)
				e.reset();
		});
		me.searchCombo.reset();
	},
	/* конструктор */
	initComponent: function() {
		var win = this;
		win.manyDrug = false; // Убрать
		
		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model',
			fields: [
				{name: 'CourseType_id'},
				{name: 'DrugListData'},
				{name: 'DurationType_id'},
				{name: 'DurationType_intid'},
				{name: 'DurationType_recid'},
				{name: 'EvnCourseTreat_ContReception'},
				{name: 'EvnCourseTreat_CountDay'},
				{name: 'EvnCourseTreat_Duration'},
				{name: 'EvnCourseTreat_Interval'},
				{name: 'EvnCourseTreat_MaxCountDay'},
				{name: 'EvnCourseTreat_MinCountDay'},
				{name: 'EvnCourseTreat_PrescrCount'},
				{name: 'EvnCourseTreat_id'},
				{name: 'EvnCourseTreat_pid'},
				{name: 'EvnCourseTreat_setDate'},
				{name: 'EvnPrescrTreat_Descr'},
				{name: 'EvnPrescrTreat_IsCito'},
				{name: 'EvnReceptGeneralDrugLink_id'},
				{name: 'EvnReceptGeneral_id'},
				{name: 'LatName'},
				{name: 'LpuSection_id'},
				{name: 'Lpu_id'},
				{name: 'Morbus_id'},
				{name: 'PerformanceType_id'},
				{name: 'PersonEvn_id'},
				{name: 'PrescriptionIntroType_id'},
				{name: 'PrescriptionTreatType_id'},
				{name: 'ResultDesease_id'},
				{name: 'Server_id'},
				{name: 'accessType'}
			]
		});

		win.OneDrugFormPanel = Ext6.create('common.EMK.SpecificationDetail.OneDrugFormPanel', {
			parentPanel: win,
			inModalWindow: false,
			showRp: false
		});

		win.searchCombo = new Ext6.create('swSearchDrugComplexMnnCombo', {
			fieldLabel: 'Препарат',
			name: 'searchDrugNameCombo',
			listConfig: {
				cls: 'choose-bound-list-menu update-scroller',
				itemTpl: [
					'{Drug_Name} <span class="drug-lat-name">{LatName}</span>'
				]
			},
			triggers: {
				clear: {
					cls: 'sw-clear-trigger',
					hidden: true,
					handler: function () {
						if (this.disabled) return false;
						var fieldsAddDrug = win.OneDrugFormPanel.queryById('OneDrugAddFormPanel');
						fieldsAddDrug.query('field').forEach(function (e) {
							if (e && e.reset) {
								e.reset();
							}
						});
						this.reset();
						this.triggers.clear.hide();
						this.triggers.search.show();
					}
				},
				search: {
					cls: 'x6-form-search-trigger',
					handler: function () {
						//а хз что тут делать, и так работает
					}
				}
			},
			onBeforeLoad: function(store, oper){
				var xParams = {
					onlyMnn: win.onlyMnn.getValue(),
					findByLatName: false
				};
				const regex = /[a-z-\+\s]+$/gmi;
				const str = win.searchCombo.getRawValue();

				if (regex.test(str)) {
					xParams.findByLatName = true;
				}

				store.getProxy().setExtraParams(xParams);
			},
			listeners:{
				select: function(combo, record, eOpts ) {
					win.onSelectRecSearchCombo(record);
				},
				keyup: function (field, e) {
					if (!Ext6.isEmpty(e.target.value)){
						this.triggers.clear.show();
						this.triggers.search.hide();
					}else {
						this.triggers.clear.hide();
						this.triggers.search.show();
					}
				}
			}
		});
		win.TreatDrugListGrid = new Ext6.create('Ext6.grid.Panel', {
			margin: '0 16px',
			frame: false,
			border: false,
			userCls: 'TreatDrugListTable',
			emptyText: 'Для добавления заполните поля выше и нажмите кнопку "Добавить в курс"',
			default: {
				border: 0
			},
			columns: [
				{
					xtype: 'rownumberer',
					text: '',
					width: 31,
					hidden: getRegionNick() != 'kz'
				}, {
					autoHeight:true,
					xtype: 'templatecolumn',
					userCls: 'TreatDrugListTemplate',
					text: 'Препараты составного назначения',
					tpl: '<div><span style="color: #2196F3;" class="drugname">{Drug_Name}</span> <tpl if="{DoseDay}>Дневная доза - {DoseDay};</tpl><tpl if="{PrescrDose}">Доза курсовая - {PrescrDose};</tpl> </div>' +
						'<div id="{DrugComplexMnn_id}_description">{Description}</div>',
					flex: 1,
					hidden: getRegionNick() == 'kz'
				}, {
					text: 'Препараты составного назначения',
					dataIndex: 'Drug_Name',
					flex: 1,
					hidden: getRegionNick() != 'kz'
				}, {
					text: 'Доза',
					dataIndex: 'Kolvo',
					width: 64,
					hidden: getRegionNick() != 'kz'
				}, {
					text: 'Дневная',
					dataIndex: 'DoseDay',
					width: 64,
					hidden: getRegionNick() != 'kz'
				}, {
					text: 'Курсовая',
					dataIndex: 'PrescrDose',
					width: 64,
					hidden: getRegionNick() != 'kz'
				}, {
					xtype: 'actioncolumn',
					width: 30,
					sortable: false,
					menuDisabled: true,
					items: [{
						iconCls: 'grid-header-icon-delItem',
						cls: 'dellItem',
						handler: function (panel, rowIndex, colIndex, item, e, record) {
							if(record.get('status') != 'new'){
								record.set('status','deleted');
								record.commit();
							}
							else{
								panel.getStore().remove(record);
							}
							win.getDrugInteractionsDescription();
							win.TreatDrugListGrid.reconfigure();
						}
					}],
				}, {
					hidden: true,
					text: 'DrugComplexMnn_id',
					dataIndex: 'DrugComplexMnn_id'
				}
			],
			store: {
				fields: [
					'DrugComplexMnn_id', // '507727'
					'Drug_Name',
					'Drug_id', // null
					'Kolvo',
					'MethodInputDrug_id',
					'DrugForm_Name', // "Tabl. obductae"
					'KolvoEd',
					'DrugForm_Nick',
					'EdUnits_id', // null
					'EdUnits_Nick', // null
					'GoodsUnit_id', // null
					'GoodsUnit_Nick',
					'DrugComplexMnnDose_Mass', // 0
					'DoseDay', // ''
					'PrescrDose',
					'GoodsUnit_sid', // null
					'status', // 'updated', 'new', 'saved'
					'id',
					'FactCount',
					'FactDose', // null
					'MaxDoseDay', // "2 таб."
					'MinDoseDay', // "2 таб."
					'setDate', // '04.07.2018'
					'CountDay', // 1
					'Duration',
					'DurationType_id',
					'DurationType_Nick', // 'дн'
					'ContReception', // 2
					'DurationType_recid',
					'Interval', // ''
					'DurationType_intid',
					// Дополнительно
					'LatName'
				],
				filters: [
					function(item) {
						return item.data.status != 'deleted';
					}
				],
				/*sorters: [{
					property: 'Drug_Name',
					direction: 'ASC'
				}],*/
				pageSize: null
			},
			saveRecord: function(data)
			{
				var me = this,
					store = me.getStore();
				
				if(!data)
					data = win.getDrugFormData();
				if(data) {
					if (data.id) {
						var rec = store.findRecord('id', data.id);
						if (!rec)
							me.getSelectionModel().getSelectedRecord();
						if (rec)
							store.remove(rec);
					}

					// @todo КОСТЫЛЬ ПОТОМ УДАЛИТЬ
					data.DrugComplexMnnDose_Mass = null;
					// 2) Вид массива в конце файла
					store.add(data);
				}
				else{
					Ext6.MessageBox.show({
						title: 'Проверка данных формы',
						msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
						buttons: Ext6.Msg.OK,
						icon: Ext6.Msg.WARNING
					});
				}
			},
			listeners:{
				select: function (grid, record, index, eOpts) {
					win.mask('Подождите, идет загрузка...');
					//win.addToCourseBtn.setText('СОХРАНИТЬ ИЗМЕНЕНИЯ');
					win.addToCourseBtn.disable();
					var onLoadMnn = function(){
						win.addToCourseBtn.enable()
					};
					win.loadTreat(index,onLoadMnn);
				}
			}
		});
		win.addToCourseBtn = Ext6.create('Ext6.button.Button',{
			padding: '7 0',
			minWidth: 215,
			maxWidth: 215,
			margin: '17 0 0 0',
			height: 30,
			iconCls: 'add-course',
			cls: 'button-secondary',
			text: 'ДОБАВИТЬ В КУРС',
			handler: function(){
				win.setAllowBlank(false);
				win.TreatDrugListGrid.saveRecord();
				win.clearDrugNames();
				this.setText('ДОБАВИТЬ В КУРС');
				win.getForm().isValid();
			}
		});
		win.onlyMnn = new Ext6.create('Ext6.form.field.Checkbox', {
			cls: 'torg-name-on',
			labelStyle: 'margin-top: 4px;',
			style: 'margin-top: 2px; margin-left: 5px;',
			value: true,
			boxLabel: 'Только по МНН'
		});
		win.CommonParamPanel = Ext6.create('Ext6.panel.Panel', {
			title: 'ОБЩИЕ ПАРАМЕТРЫ КУРСА',
			width: '100%',
			frame: true,
			bodyPadding: '15 10 15 15',

			layout: {
				type: 'hbox'
			},
			defaults: {
				border: false
			},
			border: false,
			items: [
				{
					maxWidth: 355,
					defaults: {
						width: '100%',
						labelWidth: 130,
						padding: '5 0 0 0',
						margin: 0
					},
					items: [{
						xtype: 'commonSprCombo',
						comboSubject: 'PrescriptionIntroType',
						sortField: 'PrescriptionIntroType_id',
						name: 'PrescriptionIntroType_id',
						fieldLabel: 'Способ применения',
						allowBlank: false,
						forceSelection: true
					}, {
						xtype: 'datefield',
						allowBlank: false,
						format: 'd.m.Y',
						value: new Date(),
						plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
						fieldLabel: 'Начать',
						maxWidth: 241,
						name: 'EvnCourseTreat_setDate',
						listeners:{
							'change': function(){
								win.getDrugInteractionsDescription();
							}
						}
					}, {
						layout: {
							type: 'hbox'
						},
						border: false,
						items: [{
							xtype: 'numberfield',
							fieldLabel: 'Продолжать',
							name: 'EvnCourseTreat_Duration',
							hideTrigger: true,
							allowBlank: false,
							//value: 1,
							minValue: 1,
							labelWidth: 130,
							width: 212
						}, {
							xtype: 'commonSprCombo',
							displayCode: false,
							forceSelection: true,
							allowBlank: false,
							comboSubject: 'DurationType',
							name: 'DurationType_id',
							moreFields: [{name: 'DurationType_Genitive', type: 'string'}],
							displayField: 'DurationType_Genitive',
							padding: '0 0 0 18',
							width: 125
						}]
					}, {
						padding: '0 0 0 135',
						xtype: 'checkboxfield',
						boxLabel: 'До выписки',
						name: 'PayType_id',
						disabled: true
					}]
				}, {
					defaults: {
						width: '100%',
						labelWidth: 110,
						padding: '5 0 0 40',
						margin: 0,
						maxWidth: 365
					},
					items: [{
						xtype: 'numberfield',
						width: 184,
						hideTrigger: true,
						allowBlank: false,
						name: 'EvnCourseTreat_CountDay',
						fieldLabel: 'Приемов в сутки'
					}, {
						xtype: 'commonSprCombo',
						displayCode: false,
						comboSubject: 'PerformanceType',
						name: 'PerformanceType_id',
						forceSelection: true,
						fieldLabel: 'Исполнение',
						width: 365
					}, {
						xtype: 'textfield',
						fieldLabel: 'Комментарий',
						name: 'EvnPrescrTreat_Descr'
					}, {
						padding: '0 0 0 155',
						xtype: 'checkboxfield',
						boxLabel: 'Cito!',
						name: 'EvnPrescrTreat_IsCito'
					}]
				}
			]
		});
		win.ManyDrugFormPanel = new Ext6.form.FormPanel({
			trackResetOnLoad: true,
			autoHeight: true,
			border: false,
			defaults: {
				labelAlign: 'right',
				border: false
			},
			bodyPadding: 16,
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=EvnPrescr&m=saveEvnCourseTreat',
			items: [
				{
					xtype: 'hidden',
					name: 'EvnReceptGeneral_id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnCourseTreat_id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnReceptGeneralDrugLink_id'
				},
				{
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					//border: false,
					defaults: {
						border: false,
						labelWidth: 75
					},
					items: [
						{
							itemId: 'manyDrugNamesPanel',
							layout: {
								type: 'hbox'
							},
							bodyPadding: '0 10 15 15',
							defaults: {
								border: false
							},
							items: [
								{
									xtype: 'hiddenfield',
									name: 'id'
								},
								{
									layout: 'anchor',
									itemId: 'ManyDrugAddFormPanel',
									maxWidth: 460,
									defaults: {
										anchor: '100%',
										padding: '5 0 0 0',
										margin: 0
									},
									flex: 5,
									items: [win.searchCombo,
										{
											xtype: 'swDrugComplexMnnCombo',
											readOnly: true,
											allowBlank: false,
											name: 'DrugComplexMnn_id'
										}, {
											xtype: 'textfield',
											fieldLabel: 'Rp',
											hidden: true,
											readOnly: true,
											name: 'LatName'
										}, {
											xtype: 'swDrugCombo',
											userCls: 'drugs-trade-name',
											name: 'Drug_id'
										}, {
											layout: {
												type: 'hbox'
											},
											border: false,
											items: [{
												name: 'KolvoEd',
												minValue: 0.0001,
												xtype: 'numberfield',
												hideTrigger: true,
												allowBlank: false,
												fieldLabel: 'Кол-во ЛС на прием',
												labelWidth: 160,
												width: 237
											}, {
												xtype: 'commonSprCombo',
												displayCode: false,
												comboSubject: 'GoodsUnit',
												displayField: 'GoodsUnit_Nick',
												moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
												name: 'GoodsUnit_sid',
												hiddenName: 'GoodsUnit_sid',
												forceSelection: true,
												allowBlank: false,
												border: false,
												minHeight: 25,
												padding: '0 0 0 23',
												width: 200,
												loadingText: 'Загрузка...',
												listConfig: {
													scrollable: 'y', height: 300, resizable: true, resizeHandles: "se"
												}
											}]
										}, {
											bodyPadding: '0 0',
											layout: {
												type: 'hbox'
											},
											border: false,
											items: [{
												name: 'Kolvo',
												xtype: 'numberfield',
												hideTrigger: true,
												fieldLabel: 'Доза на прием',
												labelWidth: 160,
												width: 237
											}, {
												xtype: 'commonSprCombo',
												displayCode: false,
												comboSubject: 'GoodsUnit',
												displayField: 'GoodsUnit_Nick',
												moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
												name: 'GoodsUnit_id',
												padding: '0 0 0 23',
												width: 200,
												forceSelection: true,
												loadingText: 'Загрузка...',
												listConfig: {
													scrollable: 'y', height: 300, resizable: true, resizeHandles: "se"
												}
											}, {
												xtype: 'commonSprCombo',
												displayCode: false,
												comboSubject: 'GoodsUnit',
												displayField: 'GoodsUnit_Nick',
												moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
												name: 'GoodsUnit_id',
												padding: '0 0 0 23',
												width: 200,
												forceSelection: true,
												loadingText: 'Загрузка...',
												listConfig: {
													scrollable: 'y', height: 300, resizable: true, resizeHandles: "se"
												}
											}]
										},
										win.addToCourseBtn
									]
								},
								win.onlyMnn
							]
						},
						win.CommonParamPanel
					]
				}],
			/*listeners: {
				'dirtychange': function (comp, dirty, eOpts) {
					var saveBtn = win.down('#saveDrugPrescr');
					saveBtn.setDisabled(!dirty);
				}
			}*/
		});

		win.TabPanel = new Ext6.panel.Panel({
			scrollable: 'y',
			autoHeight: true,
			//plain: true,
			border: false,
			defaults: {
				border: false
			},
			layout: 'card',
			header: false,
			items: [{
				items: [win.OneDrugFormPanel]
			}, {
				items: [win.ManyDrugFormPanel]
			}]
		});
		
		win.ModeToggler = Ext6.create('Ext6.button.Segmented', {
			margin: '0 0 0 22',
			allowDepress: true,
			items:[{
				pressed: true,
				text: 'Один препарат',
				iconCls: 'tab-icon-oneDrug',
				value: 'one'
			}, {
				text: 'Составное назначение',
				iconCls: 'tab-icon-manyDrug',
				value: 'many'
			}],
			listeners: {
				change: function(toggler, value) {
					if(win.action == 'edit')
						return false;
					if (!Ext6.isEmpty(value)) {
						win.setMode(value);
					}
				}
			}
		});
		win.TogglerToolbar = new Ext6.create('Ext6.toolbar.Toolbar', {
			items: [
				win.ModeToggler
				,'->'
			]
		});
		
		Ext6.apply(win, {
			bodyPadding: 0,
			margin: 0,
			border: false,
			tbar: {
				padding: 0,
				layout: 'vbox',
				defaults: {
					xtype: 'toolbar',
					padding: '10 0',
					margin: 0,
					border: false,
					width: '100%',
					cls: 'toptoolbar',
					style: {
						background: '#eee',
						'borderBottom': '1px solid #ccc !important;'
					}
				},
				items: [
					win.TogglerToolbar
				]
			},
			items: [
				win.TabPanel,
				win.TreatDrugListGrid
			],
			buttons: ['->', {
				handler: function () {
					win.hide();
				},
				cls: 'buttonCancel',
				text: 'Отмена'
			}, {
				handler: function () {
					if (win.PacketPrescr_id)
						win.saveTemplate();
					else
						win.doSave();
				},
				cls: 'buttonAccept',
				text: 'Сохранить',
				margin: '0 20 0 0'
			}]
		});

		this.callParent(arguments);
	}
});