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
Ext6.define('common.EMK.QuickPrescrSelect.swEvnPrescrTreatCreateWindow', {
	/* свойства */
	alias: 'widget.swEvnPrescrTreatCreateWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new new-packet-create-window EvnCourseTreatEditPanel',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	disabledRecept: false,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	refId: 'swEvnPrescrTreatCreateWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Назначение лекарственного средства',
	width: 850,
	height: 580,
	scrollable: 'y',
	parentPanel: {},
	parentCntr: {},
	requires: [
		'common.EMK.SpecificationDetail.OneDrugFormPanel'
	],
	listeners:{
		'hide':function(win){
			if(!win.saveLocalData && win.ofForms && win.ofForms == 'PacketPanelTreatmentStandards') win.callback(false);
		}
	},
	getForm: function(){
		return this.OneDrugFormPanel.getForm();
	},
	show: function (data) {
		this.setValuesMode = true; // режим автоматического изменения данных формы
		this.callParent(arguments);
		this.ReceiptPanel.ReceiptForm.getForm().reset();
		if (data.disabledRecept) {
			this.disabledRecept = data.disabledRecept;
		} else {
			this.disabledRecept = false;
		}
		if (this.ReceiptRadioGroup.getBoxes() && this.ReceiptRadioGroup.getBoxes()[1]) {
			this.ReceiptRadioGroup.getBoxes()[1].setDisabled(this.disabledRecept);
		}
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
		me.ReceiptPanel.data = me.data = data;
		me.ReceiptPanel.parentPanel = me.parentPanel = data.parentPanel;
		me.ReceiptPanel.parentCntr = me.parentCntr = data.parentCntr;
		me.callback = arguments[0].callback?arguments[0].callback:Ext6.emptyFn;
		me.PacketPrescr_id = data.PacketPrescr_id?data.PacketPrescr_id:null;

		me.ofForms = (data.ofForms) ? data.ofForms : null;
		me.loadLocalData_ReceiptCardPanel = false;
		me.saveLocalData = false;
		var DoseIcon = me.OneDrugFormPanel.MethodPanel.down('#DoseDay_warnIcon');
		DoseIcon.hide();

		if (!me.PacketPrescr_id) {//yl:контроль внутри пакета должен быть без привязки к ЭМК или пациенту
			me.allergic_msg=false;
			me.reaction_msg="";
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
						me.allergic_msg=true;
						Ext6.Msg.show({//yl:
							icon: Ext6.Msg.WARNING,
							title: "Внимание!",
							msg: "У пациента выявлена аллергическая реакция на данный препарат!",
							buttons: Ext6.Msg.YESNO,
							buttonText: {
								no: "Продолжить",
								yes: "Отмена"
							},
							fn: function (buttonId) {
								if (buttonId == "yes") {//Отмена
									me.hide();
								}else if(me.reaction_msg!=""){
									ReactionMsg();
								}
							}
						});
					}
				}
			});
			var ReactionMsg=function(){//yl:чтобы не наслаивалось на проверку аллергии
				Ext6.Msg.show({
					icon: Ext6.Msg.WARNING,
					title: "Внимание!",
					msg: me.reaction_msg,
					buttons: Ext6.Msg.YESNO,
					buttonText: {
						no: "Продолжить",
						yes: "Отмена"
					},
					fn: function (buttonId) {
						if (buttonId == "yes") {
							me.hide();
						}
					}
				});
			};
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
							me.reaction_msg="При использовании препарата '" + result.DrugComplexMnn_RusName2 + "' в комплексе с препаратом '" + result.DrugComplexMnn_RusName + "' (ТАП №" + result.EvnPL_NumCard + " от " + result.EvnPrescrTreat_setDT + ") возможны побочные эффекты";
							if(!me.allergic_msg){//если не было аллергии - сразу покажем, иначе - его запускает аллергия
								ReactionMsg();
							};
						}
					}
				}
			});
		}

		me.checkPersonDrugReactionInEvn(data.rec.DrugComplexMnn_id);

		me.getForm().findField('EvnCourseTreat_setDate').addListener('change',function(vl){
			var DrugComplexMnn_id = me.getForm().findField('DrugComplexMnn_id').getValue();
			me.checkPersonDrugReactionInEvn(DrugComplexMnn_id);
		});
		
		me.mask('Подождите, идет загрузка...');
		var showFn = function(){
			me.reset();
			if(data.rec.PacketPrescrTreat_id){
				me.loadValuesIntoForm(data.rec.PacketPrescrTreat_id);
			} else{
				me.loadValuesByRec(data.rec);
				if(me.ofForms) me.setLocalValuesFormData(data);
			}
		};
		if(me.sprLoading){
			var components = me.query('combobox');
			me.loadDataLists(components,showFn);
		} else showFn();

		var pp = me.parentPanel && me.parentPanel.ownerPanel && me.parentPanel.ownerPanel.ownerWin && me.parentPanel.ownerPanel.ownerWin.PersonInfoPanel;
		var Person_Age = !Ext.isEmpty(pp) ? pp.getFieldValue('Person_Age') : null;
		var Diag_id = data.Diag_id || null;
		
		checkRecommendDose({
			record: me.data.rec,
			Person_Age: Person_Age,
			Diag_id: Diag_id,
			callback: function (dose) {
				var DoseIcon = me.OneDrugFormPanel.MethodPanel.down('#DoseDay_warnIcon');
				DoseIcon.setHtml('<img title="Суточная доза не должна превышать: '+ dose.DoseDay +' ' + dose.DayUnit +'&#10;Курсовая доза не должна превышать: '+ dose.DoseKurs +' ' + dose.KursUnit +' " style="margin: 5px" src="/img/icons/warn_yellow.png"/>');
				DoseIcon.show();
			}
		});
		
		//me.getForm().findField('searchDrugNameCombo').focus();
		me.setValuesMode = false;
		me.unmask();
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
					// Если по назначению заведен рецепт - открываем форму с ним
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
	setValuesFromData: function(){
		var me = this,
			base_form = me.getForm(),
			drug_field = base_form.findField('Drug_id'),
			fieldsVals = base_form.getFieldValues();
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
				else delete fieldsVals[key];
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
			if(data.localRecordDataForm.ReceiptPanel){
				me.loadLocalData_ReceiptCardPanel = true;
				me.ReceiptRadioGroup.setValue({'receipt_type': 2});
				me.modeReceipt = 2;
				me.loadLocalData_ReceiptCardPanel = true;
				me.ReceiptCardPanel.show();
				data.localRecordDataForm.ReceiptPanel.EvnCourseTreat_setDate = data.localRecordDataForm.ReceiptPanel.EvnReceptGeneral_setDate;
				me.ReceiptPanel.setValuesGeneralReceiptForm(data.localRecordDataForm.ReceiptPanel);
			}else{
				me.loadLocalData_ReceiptCardPanel = false;
				me.ReceiptCardPanel.hide();
			}
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

			params.ReceiptPanel = me.saveReceipt(params, function(){});
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
		var drug_data = me.OneDrugFormPanel.getDrugFormData();
		arr.push(drug_data);


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

		/*if(me.ofForms && me.ofForms == 'PacketPanelTreatmentStandards'){
			me.unmask();
			me.formStatus = 'edit';
			me.callback(params);
			me.reset();
			me.hide();
			return false;
		}*/
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

					var cb = function(EvnReceptGeneral_id){
						if(withPrint && EvnReceptGeneral_id){
							me.ReceiptPanel.ReceiptForm.printRecept(EvnReceptGeneral_id);
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
					};
					if(me.modeReceipt == 2){ // Пока только платный рецепт
						me.saveReceipt(data, cb);
					}
					else cb();
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

		var drugData = me.OneDrugFormPanel.getDrugFormData();

		var DrugListData = Ext.util.JSON.encode(drugData),
			values = base_form.getValues(),
			params = {
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
	reset: function(){
		var me = this,
			fOne = me.getForm(),
			fReceipt = me.ReceiptPanel.ReceiptForm.getForm(),
			mpanel = me.OneDrugFormPanel.MethodPanel,
			methodText = mpanel.down('#methodText');
		methodText.reset();
		fReceipt.reset();
		me.ReceiptRadioGroup.setValue({'tcw_receipt_type': 1});
		// me.ReceiptCardPanel.hide();
		// @todo это говно надо убрать
		// Но к сожалению Ext6 пока не может предложить альтернативу способа сделать форму не isDirty,
		me.setFormIsDirty(fOne);
		me.loadDefaultValues();
		//Включаем подсветку
		fOne.isValid();
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
				GoodsUnit_sid: null
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
			base_form = me.OneDrugFormPanel.getForm(),
			fieldValues = {},
			today = new Date();

		fieldValues.PrescriptionIntroType_id = 1; // Способ применения пероральное введение
		fieldValues.EvnCourseTreat_setDate = today.format('d.m.Y'); // Начать "сегодня"
		fieldValues.EvnCourseTreat_CountDay = 1; // Приёмов в сутки
		fieldValues.PerformanceType_id = 1; // Исполнение: самостоятельно
		fieldValues.DurationType_id = 1; // Продолжительность по умолчанию - день
		base_form.setValues(fieldValues); // Чтобы они не попали под isDirty действия
	},
	setModeReceipt: function(value){
		var me = this;
		me.modeReceipt = value;
		switch(value){
			case 1:
				me.scrollTo(0, 0, true);
				Ext6.defer(function(){
					me.ReceiptCardPanel.hide();
				},550);
				break;
			case 2:
				me.loadReceiptGeneralFieldValues();
				me.ReceiptCardPanel.show();
				var h = me.OneDrugFormPanel.getHeight()?me.OneDrugFormPanel.getHeight():500;
				Ext6.defer(function(){
					me.scrollTo(0, h, true);
				},10);
				break;
			case 3:
				break;
			default:
				me.scrollTo(0, 0, true);
				Ext6.defer(function(){
					me.ReceiptCardPanel.hide();
				},550);
		}
	},
	loadReceiptGeneralFieldValues: function(){

		var me = this,
			base_form = me.getForm(),
			values = base_form.getValues(),
			mpanel = me.OneDrugFormPanel.MethodPanel,
			methodText = mpanel.down('#methodText'),
			mnn = base_form.findField('DrugComplexMnn_id'),
			mnn_rec = mnn.getSelectedRecord();

		if(me.loadLocalData_ReceiptCardPanel) return false;
		
		//рецепт еще не существует, собираем вручную
		values.fromBD = false;
		values.Drug_Signa =  methodText.getValue();
		values.Drug_Name = base_form.findField('Drug_id').getRawValue();
		if(mnn_rec)
			values.Drug_Fas = mnn_rec.get('Drug_Fas');
		if(Ext6.isEmpty(values.Drug_Name)){
			values.Drug_Name = mnn.getRawValue();
		}
		var cbFn = function(ser, num, ReceptForm_id){

			values.EvnReceptGeneral_Ser = ser.toString();
			values.EvnReceptGeneral_Num = num.toString();
			values.ReceptForm_id = ReceptForm_id;
			me.ReceiptPanel.setValuesGeneralReceiptForm(values);
		};
		me.ReceiptPanel.getSerNumFormReceipt(values,cbFn);


	},
	saveReceipt: function(data, cb){
		var me = this,
			params = {},
			def_data = me.data,
			form;
		me.mask('Сохранение рецепта...');

		switch(me.modeReceipt){
			case 2:
				form = me.ReceiptPanel.ReceiptForm.getForm();
				var disableditems = me.ReceiptPanel.ReceiptForm.query('[disabled=true]');
				Ext6.each(disableditems, function(item) { item.enable(); });
				params = form.getValues();
				break;
			case 3:
				form = me.ReceiptFormPrivilege.getForm();
				var disableditems = me.ReceiptFormPrivilege.query('[disabled=true]');
				Ext6.each(disableditems, function(item) { item.enable(); });
				params = form.getValues();
				break;
			default:
				return false;
		}
		//EvnCourseTreat_id совпадает с EvnCourse_id, при сохранении происходит присваивание EvnCourse_id значение EvnCourseTreat_id, однако для отката назначения нужен именно EvnCourse_id
		//PrescriptionType_id = 5 соответствует лекарственному назначению
		params.EvnCourse_id = data.EvnCourseTreat_id;
		params.PrescriptionType_id = 5;

		params.EvnReceptGeneral_id = form.findField('EvnReceptGeneral_id').getValue()?form.findField('EvnReceptGeneral_id').getValue():0;
		params.EvnReceptGeneral_pid = def_data.Evn_id;
		params.Person_id = def_data.Person_id;
		params.PersonEvn_id = def_data.PersonEvn_id;
		params.MedPersonal_id = def_data.MedPersonal_id;
		params.Server_id = def_data.Server_id;
		//params.EvnReceptGeneralDrugLink_id = 0;
		//params.EvnReceptGeneralDrugLink_id = form.findField('EvnReceptGeneralDrugLink_id').getValue()?form.findField('EvnReceptGeneralDrugLink_id').getValue():0;
		// @todo пока работает по старой модели, поэтому добавляем то, как было
		params.Drug_Fas0 = params.Drug_Fas;
		params.Drug_Kolvo_Pack0 = params.Drug_Kolvo_Pack;
		params.Drug_Signa0 = params.Drug_Signa;
		params.EvnReceptGeneralDrugLink_id0 = form.findField('EvnReceptGeneralDrugLink_id').getValue()?form.findField('EvnReceptGeneralDrugLink_id').getValue():0;
		if(!params.EvnReceptGeneral_id)
			params.EvnCourseTreatDrug_id = data.EvnCourseTreatDrug_id0_saved;
		if(!params.Diag_id || params.Diag_id == 'null')
			delete params.Diag_id;
		if(!params.ReceptValid_id || params.ReceptValid_id == 'null')
			delete params.ReceptValid_id;

		if(me.ofForms && me.ofForms == 'PacketPanelTreatmentStandards') return params;

		Ext6.Ajax.request({
			url: '/?c=EvnRecept&m=saveEvnReceptGeneral',
			params: params,
			callback: function (opt, success, response) {
				Ext6.each(disableditems, function(item) { item.disable(); });
				me.unmask();
				me.formStatus = 'edit';
				me.updateReceptPanels();
				if (success && response && response.responseText) {
					var data = Ext6.JSON.decode(response.responseText);
					if (data.Error_Msg) {
						Ext6.Ajax.request({
							url: '/?c=EvnPrescr&m=cancelEvnCourse',
							params: params
						});
						return;
					}
					cb(data.EvnReceptGeneral_id);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении рецепта'));
				}
			}
		});
	},
	updateReceptPanels: function(){
		var me = this;
		if(me.parentCntr && me.parentCntr.reloadReceptsPanels
			&& typeof me.parentCntr.reloadReceptsPanels === 'function'){
			me.parentCntr.reloadReceptsPanels();
		} else {
			var ReceptPanels = Ext6.ComponentQuery.query('panel[refId=\"EvnReceptPanel\"]');
			ReceptPanels.forEach(function(panel){
				panel.loadBothGrids();
			});
		}
	},
	setSignaGeneralReceiptForm: function(){
		var me = this,
			params = {},
			formRecept = me.ReceiptPanel.ReceiptForm.getForm(),
			form = me.getForm(),
			values = form.getValues(),
			methodText = me.OneDrugFormPanel.MethodPanel.down('#methodText'),
			mnn = form.findField('DrugComplexMnn_id'),
			mnn_rec = mnn.getSelectedRecord();
		if(methodText.getValue())
			params.Drug_Signa =  methodText.getValue();
		if(mnn_rec && mnn_rec.get('Drug_Fas'))
			params.Drug_Fas = mnn_rec.get('Drug_Fas');
		if(values.KolvoEd && values.EvnCourseTreat_CountDay && values.EvnCourseTreat_Duration)
			params.Drug_Kolvo_Pack = (values.KolvoEd*values.EvnCourseTreat_CountDay*values.EvnCourseTreat_Duration);

		formRecept.setValues(params);
	},
	checkPersonDrugReactionInEvn: function(DrugComplexMnn_id){
		if(getRegionNick() == 'kz')
			return false
		var me = this;
		var EvnCourseTreat_setDate = me.getForm().findField('EvnCourseTreat_setDate').getValue();

		Ext.Ajax.request({
			url: '/?c=PersonAllergicReaction&m=checkPersonDrugReactionInEvn',
			params: {
				EvnCourseTreat_setDate: Ext.util.Format.date(EvnCourseTreat_setDate, 'Y-m-d'),
				Evn_id: me.data.Evn_id,
				Person_id: me.data.Person_id,
				DrugComplexMnn_id: DrugComplexMnn_id
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
				}
			}
		});
	},
	/* конструктор */
	initComponent: function() {
		var win = this;
		win.ReceiptPanel = Ext6.create('common.EMK.SpecificationDetail.ReceiptFormPanel', {
			inModalWindow: false
		});
		win.ReceiptCardPanel = new Ext6.panel.Panel({
			autoHeight: true,
			hidden: true,
			border: false,
			defaults: {
				border: false
			},
			layout: 'card',
			header: false,
			bodyPadding: '0 16',
			items: [
				{
					items: [win.ReceiptPanel]
				},
				/*{
					items: [win.ReceiptPrivelegePanel]
				}*/
			]
		});

		win.OneDrugFormPanel = Ext6.create('common.EMK.SpecificationDetail.OneDrugFormPanel', {
			parentPanel: win,
			inModalWindow: false,
			showRp: false
		});

		win.ReceiptRadioGroup = new Ext6.form.RadioGroup({
			margin: '0 16 20 16',
			name: 'tcw_receipt_type',
			padding: '0 16',
			layout: {
				type: 'hbox',
				align: 'left'
			},
			defaults: {
				margin: '0 25 0 0'
			},
			items: [
				{width:100, inputValue: 1, boxLabel: 'Без рецепта', checked: true},
				{width:206, inputValue: 2, boxLabel: 'Рецепт за полную стоимость', disabled: win.disabledRecept},
				{width:158, inputValue: 3, boxLabel: 'Льготный рецепт', disabled: true}
			],
			listeners: {
				change: function(group, nv, ov, e) {
					if(win.loadLocalData_ReceiptCardPanel) return false;

					var value = '';
					if (!Ext6.isEmpty(nv) && Ext6.isObject(nv))
						value = nv.tcw_receipt_type;
					if (!Ext6.isEmpty(value)){
						win.setModeReceipt(value);
					}
				}
			}
		});
		Ext6.apply(win, {
			bodyPadding: 0,
			margin: 0,
			border: false,
			items: [
				win.OneDrugFormPanel,
				win.ReceiptRadioGroup,
				win.ReceiptCardPanel
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