/**
 * EvnCourseTreatEditPanel - Добавление лекарственного назначения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.SpecificationDetail.EvnCourseTreatEditPanel', {
	/* свойства */
	alias: 'widget.EvnCourseTreatEditPanel',
	autoShow: false,
	cls: 'EvnCourseTreatEditPanel',
	constrain: true,
	extend: 'base.BaseFormPanel',
	requires: [
		'common.EMK.SpecificationDetail.OneDrugFormPanel',
		'common.EMK.SpecificationDetail.ReceiptFormPanel'
	],
	header: false,
	border: false,
	scrollable: true,
	title: 'Лекарственное назначение',
	width: '100%',
	autoHeight: true,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	manyDrug: false,
	data: {},
	parentPanel: {},
	parentCntr: {},
	mode: 'search',
	setValuesMode: true,
	sprLoading: true,
	onSprLoad: function(){
		this.sprLoading = false;
	},
	/* конструктор */
	show: function(data) {
		var me = this;
		this.setValuesMode = true; // режим автоматического изменения данных формы
		this.callParent(arguments);
		me.action = (typeof data.record == 'object' ? 'edit' : 'add');
		if(me.action === 'edit' && Ext6.isEmpty(data.record.get('EvnCourse_id')) && Ext6.isEmpty(data.record.get('object')))
			me.action = 'addByDrug';
		me.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		me.formParams = (typeof data.formParams == 'object' ? data.formParams : {});
		me.ReceiptPanel.data = me.data = data;
		me.ReceiptPanel.parentPanel = me.parentPanel = data.parentPanel;
		me.ReceiptPanel.parentCntr = me.parentCntr = data.parentCntr;
		var DoseIcon = me.OneDrugFormPanel.MethodPanel.down('#DoseDay_warnIcon');
		DoseIcon.hide();
		
		me.mask('Подождите, идет загрузка...');
		var showFn = function(){
			log(me.action);
			delete me.values;
			var store = me.TreatDrugListGrid.getStore(),
				delBtn = me.down('#delDrugPrescr'),
				saveBtn = me.down('#saveDrugPrescr');
			delBtn.disable();
			saveBtn.disable();
			me.addToCourseBtn.setText('ДОБАВИТЬ В КУРС');
			switch(me.action){
				case 'edit':
					store.removeAll();
					delBtn.enable();
					me.loadValuesIntoForm();
					me.addToCourseBtn.setText('СОХРАНИТЬ ИЗМЕНЕНИЯ');
					break;
				case 'add':
					me.reset();
					me.reloadDrugGrid();
					me.loadDefaultValues();
					me.ModeToggler.setValue('search');
					//me.getForm().findField('searchDrugNameCombo').focus();
					me.setValuesMode = false;
					me.unmask();
					break;
				case 'addByDrug':
					me.reset();
					me.ModeToggler.setValue('search');
					me.onSelectRecSearchCombo(data.record);
					me.setValuesMode = false;
					me.unmask();
					break;
				default:
					me.setValuesMode = false;
					me.unmask();
			}
			me.formStatus = 'edit';
		};
		if(me.sprLoading){
			var components = me.query('combobox');
			me.loadDataLists(components,showFn);
		} else showFn();


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
		var store = me.TreatDrugListGrid.getStore(),
			filters = store.getFilters();
		if(!this.manyDrug)
			store.removeAll();

		if (base_form.findField('DrugComplexMnn_id').getValue()) {
			me.TreatDrugListGrid.saveRecord();
			me.clearDrugNames();
		}
		var arr = [];
		filters.removeAll();
		store.each(function(rec) {
			// 2) Вид массива в конце файла
			//rec.data.MethodInputDrug_id = '1';
			if(rec.data['status'] == 'new')
				rec.data.id = null;
			arr.push(rec.data);
		});

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
		Ext6.Ajax.request({
			url: '/?c=EvnPrescr&m=saveEvnCourseTreat',
			params: params,
			callback: function (opt, success, response) {
				me.unmask();
				function hiddenDeleted (item) {
					return item.data.status != 'deleted';
				}
				filters.add(hiddenDeleted);

				if (me.manyDrug)
					me.setAllowBlank(false);
				if (success && response && response.responseText) {
					var data = Ext6.JSON.decode(response.responseText);
					if (data.Error_Msg) {
						return;
					}

					var cb = function(EvnReceptGeneral_id){
						if(withPrint && EvnReceptGeneral_id){
							me.ReceiptForm.printRecept(EvnReceptGeneral_id);
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
	saveReceipt: function(data, cb){
		var me = this,
			params = {},
			def_data = me.data,
			form;
		me.mask('Сохранение рецепта...');

		switch(me.modeReceipt){
			case 2:
				form = me.ReceiptForm.getForm();
				var disableditems = me.ReceiptForm.query('[disabled=true]');
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
		Ext6.Ajax.request({
			url: '/?c=EvnRecept&m=saveEvnReceptGeneral',
			params: params,
			callback: function (opt, success, response) {
				Ext6.each(disableditems, function(item) { item.disable(); });
				me.unmask();
				me.updateReceptPanels();
				if (success && response && response.responseText) {
					var data = Ext6.JSON.decode(response.responseText);
					if (data.Error_Msg) {
						return;
					}
					cb(data.EvnReceptGeneral_id);
				}
				else {
					me.formStatus = 'edit';
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении рецепта'));
				}
			}
		});
	},
	updateReceptPanels: function(){
		var me = this;
		var specWnd = me.up('swSpecificationDetailWnd');
		if(specWnd && specWnd.evnPrescrCntr && specWnd.evnPrescrCntr.reloadReceptsPanels
			&& typeof specWnd.evnPrescrCntr.reloadReceptsPanels === 'function'){
			specWnd.evnPrescrCntr.reloadReceptsPanels();
		} else {
			var ReceptPanels = Ext6.ComponentQuery.query('panel[refId=\"EvnReceptPanel\"]');
			ReceptPanels.forEach(function(panel){
				panel.loadBothGrids();
			});
		}
	},
	saveTemplate: function(TemplateName){
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

		var drugData = me.getDrugFormData();

		var DrugListData = Ext.util.JSON.encode(drugData),
			values = base_form.getValues(),
			params = {
				Template_Name: TemplateName?TemplateName:'Новый шаблон',
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
			url: '/?c=PacketPrescr&m=saveDrugTemplate',
			params: params,
			callback: function (opt, success, response) {
				me.unmask();
				me.formStatus = 'edit';
				sw4.showInfoMsg({
					panel: me,
					type: 'success',
					text: 'Данные сохранены.'
				});
				me.reloadDrugGrid({
					DrugTemplateGrid: true
				});
			}
		});
		return true;
	},
	/* методы */
	deletePrescr: function () {
		var me = this,
			specPan = me.parentPanel;
		let cntr = typeof specPan == 'object' ? specPan.getController() : me.parentCntr;
		me.mask('Удаление назначения');
		if (cntr) {
			var grid = cntr.getGridByObject('EvnCourseTreat');
			if (grid) {
				var rec = grid.getSelectionModel().getSelectedRecord();
				if (rec){
					var cbFn = function(){
						me.unmask();
						if(grid.getStore().getCount() > 0){
							grid.getSelectionModel().select(0);
							cntr.openSpecification('EvnCourseTreat', grid, grid.getSelectionModel().getSelectedRecord(), true);
						}
						else {
							cntr.openSpecification();
						}
					};
					grid.deleteItem(rec,cbFn);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при поиске удаляемой записи'));
					me.unmask();
				}
			}
		}
	},
	setMode: function(mode){
		var me = this,
			selectTemplateBtn = me.down('#selectTemlateBtn'),
			saveTemplateBtn = me.down('#saveTemlateBtn');
		me.TreatDrugListGrid.hide();
		if(mode!='search'){
			me.ReceiptRadioGroup.show();
			//me.ReceiptCardPanel.show();
			me.TogglerToolbar.show();
		}
		switch(mode){
			case 'one':
				selectTemplateBtn.enable();
				saveTemplateBtn.enable();
				me.TabPanel.getLayout().setActiveItem(1);
				me.manyDrug = false;
				break;
			case 'many':
				selectTemplateBtn.disable();
				saveTemplateBtn.disable();
				me.TabPanel.getLayout().setActiveItem(2);
				me.manyDrug = true;
				me.TreatDrugListGrid.show();
				break;
			case 'search':
				me.TabPanel.getLayout().setActiveItem(0);
				me.TogglerToolbar.hide();
				me.ReceiptRadioGroup.hide();
				//me.ReceiptCardPanel.hide();
				me.searchCombo.focus();
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
		me.reset();
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
					callback: function (response) {
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
						if (response[0]) {
							me.checkRecommendDose(response[0]);
						}
					}
				});
			}
			if(me.values)
				me.values = Ext6.Object.merge(me.values,data);
			else me.values = data;
			me.setValuesFromData();
			me.unmask();
		}
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
	setValuesFromData: function(){
		var me = this,
			base_form = me.getForm(),
			fieldsVals = base_form.getFieldValues();
		if(me.values){
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
	loadValuesIntoForm: function(mode){
		var me = this,
			rec = me.data.record,
			params = {},
			url = '';
		// Дополним значения пришедшей записью
		switch(mode){
			case 'selectDrugTemplate':
				url = '/?c=PacketPrescr&m=loadPacketCourseTreatEditForm';
				params = {
					PacketPrescrTreat_id: rec.get('PacketPrescrTreat_id')
				};
				break;
			default:
				url = '/?c=EvnPrescr&m=loadEvnCourseTreatEditForm';
				params = {
					EvnCourseTreat_id: rec.get('EvnCourse_id'),
					parentEvnClass_SysNick: Ext6.JSON.encode({
						object: "EvnCourseTreatEditWindow",
						identField: "EvnCourseTreat_id"
					})
				};
		}
		me.values = rec.getData();
		Ext6.Ajax.request({
			url: url,
			params: params,
			callback: function(opt, success, response) {
				if (success && response && response.responseText) {
					var dec_data = Ext6.JSON.decode(response.responseText),
						data = dec_data[0],
						drugList = data.DrugListData = Ext6.JSON.decode(data.DrugListData);
					me.values = Ext6.Object.merge(me.values, data);

					if (drugList.length > 1)
						me.setMode('many');
					else
						me.setMode('one');
					drugList.forEach(function (e) {
						me.TreatDrugListGrid.saveRecord(e);
						log(e);
					});
					var onLoadMnn = function(){
						if(!Ext6.isEmpty(data.EvnReceptGeneral_id) && data.EvnReceptGeneral_id>0){
							me.ReceiptRadioGroup.setValue({'receipt_type': 2});
							me.ReceiptRadioGroup.disable();
						}
						if(mode == 'selectDrugTemplate')
							me.down('#saveDrugPrescr').enable();

					};
					me.loadTreat(false,onLoadMnn);
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
	setModeReceipt: function(value){

		var me = this,
			saveBtn = me.down('#saveDrugPrescr');
		me.modeReceipt = value;

		switch(value){
			case 1:
				me.ReceiptCardPanel.hide();
				break;
			case 2:
				me.ReceiptCardPanel.show();
				me.ReceiptCardPanel.getLayout().setActiveItem(0);
				me.loadReceiptGeneralFieldValues();
				saveBtn.enable();
				break;
			case 3:
				me.ReceiptCardPanel.show();
				me.ReceiptCardPanel.getLayout().setActiveItem(1);
				saveBtn.enable();
				break;
			default:
		}
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
						icon: Ext6.Msg.WARNING,
						title: "Внимание!",
						msg: "У пациента выявлена аллергическая реакция на данный препарат!",
						buttons: Ext6.Msg.YESNO,
						buttonText: {
							no : "Продолжить",
							yes : "Отмена"
						},
						fn: function(buttonId) {
							if (buttonId=="yes") {
								me.reset();
								me.hide();
							}
						}
					});
				}
			}
		});
		if(Ext.isEmpty(me.mode) || me.mode == 'search')
			me.ModeToggler.setValue('one');
		dcm.getStore().load({
			params: {
				DrugComplexMnn_id: mnn_id
			},
			callback: function (response) {
				dcm.setValue(mnn_id);
				if (response[0]) {
					me.checkRecommendDose(response[0]);
				}
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
	checkRecommendDose: function(rec) {
		var me = this;
		var view = me.parentCntr.getView();
		var pp = view.PersonInfoPanel || null;
		var Person_Age = !Ext.isEmpty(pp) ? pp.getFieldValue('Person_Age') : null;
		var Diag_id = !Ext.isEmpty(view.data.Diag_id) ? view.data.Diag_id : null;
		
		checkRecommendDose({
			record: rec.data,
			Person_Age: Person_Age,
			Diag_id: Diag_id,
			callback: function (dose) {
				var DoseIcon = me.OneDrugFormPanel.MethodPanel.down('#DoseDay_warnIcon');
				DoseIcon.setHtml('<img title="Суточная доза не должна превышать: '+ dose.DoseDay +' ' + dose.DayUnit +'&#10;Курсовая доза не должна превышать: '+ dose.DoseKurs +' ' + dose.KursUnit +' " style="margin: 5px" src="/img/icons/warn_yellow.png"/>');
				DoseIcon.show();
			}
		});
	},
	reset: function(){
		var me = this,
			store = me.TreatDrugListGrid.getStore(),
			fOne = me.OneDrugFormPanel.getForm(),
			fMany = me.ManyDrugFormPanel.getForm(),
			fReceipt = me.ReceiptForm.getForm(),
			mpanel = me.OneDrugFormPanel.MethodPanel,
			methodText = mpanel.down('#methodText');
		store.removeAll();
		methodText.reset();
		fReceipt.reset();
		me.searchCombo.reset();
		//me.setModeReceipt(1);
		me.ReceiptRadioGroup.setValue({'receipt_type': 1});
		// @todo это говно надо убрать
		// Но к сожалению Ext6 пока не может предложить альтернативу способа сделать форму не isDirty,
		me.setFormIsDirty(fOne);
		me.setFormIsDirty(fMany);
		me.loadDefaultValues();
		//Включаем подсветку
		fOne.isValid();
		fMany.isValid();
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
	 * Получение активной на данный момент формы ввода лек. средства
	 * @returns {{}} form
	 */
	getForm: function(){
		var me = this,
			base_form = {};
		if(this.manyDrug)
			base_form = me.ManyDrugFormPanel.getForm();
		else
			base_form = me.OneDrugFormPanel.getForm();
		return base_form;
	},
	/**
	 * Получение активного на данный момент компонента Ext.form.Panel
	 * @returns boolean || {{}}  base_form
	 */
	getReceiptForm: function(){
		var me = this,
			base_form = false;
		switch(me.modeReceipt){
			case 2:
				base_form = me.ReceiptForm.getForm();
				break;
			case 3:
				base_form = me.ReceiptFormPrivilege.getForm();
				break;
		}

		return base_form;
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
	/*setReceptNumber: function(action) {
		var me = this;
		var base_form = me.ReceiptForm.getForm();
		var receptform_id = base_form.findField('ReceptForm_id').getValue();
		var recept_date = Ext.util.Format.date(base_form.findField('EvnReceptGeneral_setDate').getValue(), 'd.m.Y');
		var sernum_data_check = (me.SerNumData.ReceptForm_id == receptform_id && me.SerNumData.EvnRecept_setDate == recept_date); //флаг необходимости получения данных серии и номера из кэша

		// чтобы не отрабатывало, пока форма не прогрузится
		if (Ext.isEmpty(receptform_id)) {
			return false;
		}

		if (action != 'update' && sernum_data_check) { //проверяем нет ли у нас уже номера и серии для указанных параметров
			base_form.findField('EvnReceptGeneral_Num').setValue(me.SerNumData.EvnRecept_Num);
			base_form.findField('EvnReceptGeneral_Ser').setValue(me.SerNumData.EvnRecept_Ser);
		} else if (me.SerNumData.state != 'loading' || !sernum_data_check) {
			//чтобы избежать многократных загрузок
			me.SerNumData.state = 'loading';
			me.SerNumData.ReceptForm_id = receptform_id;
			me.SerNumData.EvnRecept_setDate = recept_date;

			Ext6.Ajax.request({
				params: {
					isGeneral: 1,
					ReceptForm_id: receptform_id,
					EvnRecept_setDate: recept_date
				},
				callback: function(options, success, response) {
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						var recept_Num = (response_obj.EvnRecept_Num) ? response_obj.EvnRecept_Num : '';
						var recept_Ser = (response_obj.EvnRecept_Ser) ? response_obj.EvnRecept_Ser : '';
						var sernum_source = (response_obj.SerNum_Source) ? response_obj.SerNum_Source : '';

						var current_receptform_id = base_form.findField('ReceptForm_id').getValue();
						var current_recept_date = Ext.util.Format.date(base_form.findField('EvnReceptGeneral_setDate').getValue(), 'd.m.Y');

						if (current_receptform_id == current_receptform_id && current_recept_date == current_recept_date) { //проверяем актуальность параметров
							base_form.findField('EvnReceptGeneral_Num').setValue(recept_Num);
							base_form.findField('EvnReceptGeneral_Ser').setValue(recept_Ser);

							//если серия и номер пришли от нумератора - кэшируем их
							if (sernum_source == 'Numerator' && !Ext.isEmpty(recept_Num)) {
								me.SerNumData.EvnRecept_Num = recept_Num;
								me.SerNumData.EvnRecept_Ser = recept_Ser;
								me.SerNumData.ReceptForm_id = receptform_id;
								me.SerNumData.EvnRecept_setDate = recept_date;
							}
						}



						var params = {
						};
						form.setValues(params);



					}
					else {
						sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function() { base_form.findField('EvnReceptGeneral_setDate').focus(true); }.createDelegate(this) );
					}
					me.SerNumData.state = null;
				}.createDelegate(this),
				url: C_RECEPT_NUM
			});
		}
	},*/
	getTemplatesMenu: function(btn, e){
		var me = this,
			store = me.DrugTemplateGrid.getStore(),
			count = store.getCount();
		var showMenu = function(store){
			var menu = new Ext6.create('Ext6.menu.Menu',{plain: true,border: false,items: []});
			menu.removeAll();
			store.getRange().forEach(function (rec) {
				menu.add({
					width: 'auto',
					xtype: 'panel',
					border: false,
					columns: 2,
					defaults: {
						iconAlign: 'left',
						border: false,
						xtype: 'button',
						userCls: 'button-without-frame'
					},
					items: [
						{
							width: 200,
							textAlign: 'left',
							text: rec.get('PacketPrescrTreat_Name'),
							rec: rec,
							handler: function () {
								me.loadDrugTemplate(this.rec);
								menu.hide();
							}
						},{
							width: 32,
							iconCls: 'grid-header-icon-delItem',
							tooltip: 'Удалить шаблон',
							rec: rec,
							handler: function () {
								me.deleteTemplateDrug(this.rec, btn);
								menu.hide();
							}
						}
					]
				})
			});
			menu.showAt(btn.getX(),btn.getY()+btn.getHeight()+5);
		};
		if(count>0){
			showMenu(store)
		} else {
			me.reloadDrugGrid({
				DrugTemplateGrid: true,
				cbFn: showMenu
			});
		}


	},
	deleteTemplateDrug: function(rec, btn){
		var me = this,
			store = me.DrugTemplateGrid.getStore();
		me.mask('Удаление шаблона');
		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=deletePacketPrescrTreat',
			params: {
				PacketPrescrTreat_id: rec.get('PacketPrescrTreat_id')
			},
			callback: function(opt, success, response) {
				me.unmask();
				store.remove(rec);
				if(btn)
					me.getTemplatesMenu(btn);
			}
		});
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


		data['Drug_Name'] = mnn.getRawValue();
		data['Drug_id'] = base_form.findField('Drug_id').getValue() ||null;
		data['DrugForm_Name'] = DrugForm_Name;
		data['DrugComplexMnn_id'] = mnn.getValue() || null;
		//data['DrugForm_Name'] = base_form.findField('DrugForm_Name').getValue();
		data['KolvoEd'] = base_form.findField('KolvoEd').getValue() || null;
		//data['DrugForm_Nick'] = thas.findById(thas.id +'_TreatDrugForm_Nick').text || null;
		data['Kolvo'] = base_form.findField('Kolvo').getValue() || null;
		//data['EdUnits_id'] = base_form.findField('EdUnits_id').getValue() || null;
		//data['EdUnits_Nick'] = base_form.findField('EdUnits_id').getRawValue() || null;
		data['GoodsUnit_id'] = base_form.findField('GoodsUnit_id').getValue() || null;
		data['GoodsUnit_Nick'] = base_form.findField('GoodsUnit_id').getRawValue() || null;
		//data['DrugComplexMnnDose_Mass'] = base_form.findField('DrugComplexMnnDose_Mass').getValue() || null;
		//data['DoseDay'] = base_form.findField('DoseDay').getValue() || null;
		//data['PrescrDose'] = base_form.findField('PrescrDose').getValue() || null;
		//data['GoodsUnit_id'] = base_form.findField('GoodsUnit_id').getValue() || null;
		data['GoodsUnit_sid'] = base_form.findField('GoodsUnit_sid').getValue() || null;
		data['GoodsUnit_SNick'] = base_form.findField('GoodsUnit_sid').getRawValue() || null;
		data['EvnCourseTreat_CountDay'] = base_form.findField('EvnCourseTreat_CountDay').getValue() || null;
		data['EvnCourseTreat_Duration'] = base_form.findField('EvnCourseTreat_Duration').getValue() || null;
		data['DurationType_id'] = base_form.findField('DurationType_id').getValue() || null;
		data['LatName'] = base_form.findField('LatName').getValue() || null;
		data['MethodInputDrug_id'] = data['Drug_id']?'2':'1';
		data['FactCount'] = 0;
		if(!forTemplate){
			var id = base_form.findField('id').getValue();
			if(!isNaN(parseInt(id)))
				data['id'] = id || '';
			log('id: '+data['id']);
			data['status'] = data['id']?'updated':'new';
		}
		data['EdUnits_id'] = null;
		data['EdUnits_Nick'] = null;
		data['DrugComplexMnnDose_Mass'] = null;
		data['DoseDay'] = (parseInt(data['Kolvo']) * parseInt(data['EvnCourseTreat_CountDay'])).toString()+data['GoodsUnit_Nick'];
		//data['PrescrDose'] = parseInt(data['Kolvo']) * parseInt(base_form.findField('EvnCourseTreat_CountDay').getValue())*(Продолжительность)*месяц(30дней)
		data['DrugForm_Nick'] =  base_form.findField('DrugComplexMnn_id').getSelectedRecord().get('RlsClsdrugforms_Name') || '';
		data = win.OneDrugFormPanel.reCountData(data);

		return data
	},

	loadReceiptGeneralFieldValues: function(){

		var me = this,
			base_form = me.getForm(),
			values = base_form.getValues(),
			mpanel = me.OneDrugFormPanel.MethodPanel,
			methodText = mpanel.down('#methodText'),
			mnn = base_form.findField('DrugComplexMnn_id'),
			mnn_rec = mnn.getSelectedRecord();
		if(!Ext6.isEmpty(values.EvnReceptGeneral_id) && values.EvnReceptGeneral_id>0){

			//грузим рецепт из БД
			Ext6.Ajax.request({
				url: '/?c=EvnRecept&m=loadEvnReceptGeneralEditForm',
				params: {
					EvnReceptGeneralDrugLink_id: values.EvnReceptGeneralDrugLink_id,
					EvnReceptGeneral_id: values.EvnReceptGeneral_id,
					fromExt6: 2
				},
				callback: function(opt, success, response) {
					if (success && response && response.responseText) {
						var dec_data = Ext6.JSON.decode(response.responseText);
						values.fromBD = true;
						if(dec_data && dec_data[0] && typeof dec_data[0] === 'object'){
							var arrDBParams = dec_data[0];
							for(var key in arrDBParams){
								if(arrDBParams[key])
									values[key] = arrDBParams[key];
							}
						}
						//зануляет имеющиеся параметры
						//values = Ext6.Object.merge(values,dec_data[0]);
						me.ReceiptPanel.setValuesGeneralReceiptForm(values);
					}
					else
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных рецепта'));
				}
			});
		}
		else {

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
		}
	},

	setSignaGeneralReceiptForm: function(){
		var me = this,
			params = {},
			formRecept = me.ReceiptForm.getForm(),
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

	reloadDrugGrid: function(cfg){
		if(!cfg || (cfg && Ext6.isObject(cfg) && cfg.DrugTemplateGrid)){
			this.DrugTemplateGrid.getStore().load({
				params: {
					'MedPersonal_id': getGlobalOptions().medpersonal_id
				},
				callback: function(records, operation, success) {
					if(cfg && cfg.cbFn)
						cfg.cbFn(this)
				}
			});
		}
		if(!cfg || (cfg && Ext6.isObject(cfg) && cfg.lastSelectedDrugGrid)) {
			this.lastSelectedDrugGrid.getStore().load({
				params: {
					'MedPersonal_id': getGlobalOptions().medpersonal_id,
					'Lpu_id': getGlobalOptions().lpu_id
				},
				callback: function (records, operation, success) {
					if(cfg && cfg.cbFn)
						cfg.cbFn(this)
				}
			});
		}
	},
	loadDrugTemplate: function(rec){
		var me = this;
		if(rec && rec.get('PacketPrescrTreat_id')){
			me.mask('Подождите, идет загрузка...');
			this.setValuesMode = true; // режим автоматического изменения данных формы
			me.reset();
			me.data.record = rec;
			me.loadValuesIntoForm('selectDrugTemplate');
			me.setValuesMode = false;
			me.unmask();
		}
	},
	loadLastSelDrug: function(rec){
		var me = this;
		if(rec && rec.get('DrugComplexMnn_id')){
			me.mask('Подождите, идет загрузка...');
			this.setValuesMode = true; // режим автоматического изменения данных формы
			me.reset();
			me.data.record = rec;
			me.setValuesMode = false;
			me.unmask();
			me.onSelectRecSearchCombo(rec);
		}
	},
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
					width: 31
				}, {
					text: 'Препараты составного назначения',
					dataIndex: 'Drug_Name',
					flex: 1
				}, {
					text: 'Доза',
					dataIndex: 'Kolvo',
					width: 64
				}, {
					text: 'Дневная',
					dataIndex: 'DoseDay',
					width: 64
				}, {
					text: 'Курсовая',
					dataIndex: 'PrescrDose',
					width: 64
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
							else
								panel.getStore().remove(record);
							win.TreatDrugListGrid.reconfigure();
						}
					}]
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
					var saveBtn = win.down('#saveDrugPrescr');
					saveBtn.disable();
					win.addToCourseBtn.setText('СОХРАНИТЬ ИЗМЕНЕНИЯ');
					win.addToCourseBtn.disable();
					var onLoadMnn = function(){
						win.addToCourseBtn.enable()
					};
					win.loadTreat(index,onLoadMnn);
				}
			}
		});

		win.searchCombo = new Ext6.create('swSearchDrugComplexMnnCombo', {
			width: 83 + 450,
			margin: '0 11 0 23',
			labelWidth: 83,
			labelStyle: 'padding: 0px; vertical-align: middle;',
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

		win.OneDrugFormPanel = Ext6.create('common.EMK.SpecificationDetail.OneDrugFormPanel', {
			parentPanel: win,
			listeners: {
				'dirtychange': function (comp, dirty, eOpts) {
					var saveBtn = win.down('#saveDrugPrescr');
					saveBtn.setDisabled(!dirty);
				},
				'fieldvaliditychange': function ( comp, field, isValid, eOpts ){
					win.ReceiptRadioGroup.setDisabled(!comp.isValid());
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
				var saveBtn = win.down('#saveDrugPrescr');
				saveBtn.enable();
				win.getForm().isValid();
			}
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
						name: 'EvnCourseTreat_setDate'
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
						disabled: true,
						hidden: getCurArm().inlist(['polka', 'common'])
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
									items: [
										{
											xtype: 'swDrugComplexMnnCombo',
											readOnly: true,
											allowBlank: false,
											name: 'DrugComplexMnn_id'
										}, {
											xtype: 'textfield',
											fieldLabel: 'Rp',
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
											}]
										},
										win.addToCourseBtn
									]
								}
								/*,{
									width: 180,
									height: 100,
									html: 'Внимание! У пациента выявлена аллергическая реакция на данный препарат!',
									cls: 'drug-allerg'
									}*//*,{
								layout: 'border',
								width: 175,
								height: 165,
								padding: '5 0 0 15',
								bodyBorder: false,
								items: [{
									border: false,
									region: 'north',
									height: 135
									},{
									border: false,
									region: 'south',
									height: 30,
									items: [{
										padding: '7 0',
										width: 155,
										height: 30,
										xtype: 'button',
										cls: 'button-primary',
										text: 'ДОБАВИТЬ В КУРС'
									}]
								}]
							}*/
							]
						},
						win.CommonParamPanel
					]
				}],
			listeners: {
				'dirtychange': function (comp, dirty, eOpts) {
					var saveBtn = win.down('#saveDrugPrescr');
					saveBtn.setDisabled(!dirty);
				},
				/*'fieldvaliditychange': function ( comp, field, isValid, eOpts ){
					win.ReceiptRadioGroup.setDisabled(!comp.isValid());
				}*/
			}
		});
		win.DrugTemplateGrid = Ext6.create('Ext6.grid.Panel', {
			frame: false,
			border: false,
			cls: 'no-border-grid',
			emptyText: 'Вы можете создать свой шаблон, при добавлении, нажав на кнопку "Сохранить как шаблон"',
			rowLines: false,
			default: {
				border: 0
			},
			itemConfig: {
				height: 28
			},
			columns: [
				{
					dataIndex: 'PacketPrescrTreat_Name',
					flex: 1
				}, {
					xtype: 'actioncolumn',
					width: 30,
					sortable: false,
					menuDisabled: true,
					items: [{
						iconCls: 'grid-header-icon-delItem',
						cls: 'dellItem',
						handler: function (panel, rowIndex, colIndex, item, e, record) {
							win.deleteTemplateDrug(record);
						}
					}]
				}
			],
			store: {
				fields: [
					'PacketPrescrTreat_id',
					'PacketPrescrTreat_Name'
				],
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadDrugTemplateList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			listeners:{
				select: function (grid, rec) {
					win.loadDrugTemplate(rec);
				}
			}
		});

		win.lastSelectedDrugGrid = Ext6.create('Ext6.grid.Panel', {
			frame: false,
			border: false,
			rowLines: false,
			minHeight: 100,
			emptyText: 'Созданных Вами лекарственных назначений не обнаружено',
			default: {
				border: 0
			},
			itemConfig: {
				padding: 0,
				margin: 0
			},
			columns: [
				{
					dataIndex: 'Drug_Name',
					flex: 1
				}
			],
			store: {
				fields: [
					'Drug_id',
					'DrugComplexMnn_id',
					'Drug_Name'
				],
				autoLoad: false,
				folderSort: true,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PacketPrescr&m=loadLastSelectedDrugList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				extend: 'Ext6.data.Store',
				pageSize: null
			},
			listeners:{
				select: function (grid, rec) {
					win.loadLastSelDrug(rec);
				}
			}
		});

		win.selTemplatePanel = new Ext6.create('Ext6.panel.Panel',{
			border: false,
			items: [
				{
					xtype: 'fieldset',
					autoHeight: true,
					minHeight: 100,
					margin: '0 16px',
					cls: 'saved-on-tempate',
					title: 'Сохраненные в шаблон',
					border: false,
					collapsible: true,
					items: [
						win.DrugTemplateGrid
					]
				},
				{
					xtype: 'fieldset',
					autoHeight: true,
					minHeight: 100,
					margin: '0 16px',
					cls: 'last-appointed',
					border: false,
					title: 'Последние назначенные',
					collapsible: true,
					items: [
						win.lastSelectedDrugGrid
					]
				}
			]
		});

		win.ModeToggler = Ext6.create('Ext6.button.Segmented', {
			margin: '0 0 0 22',
			allowDepress: true,

			items:[{
				text: 'Один препарат',
				iconCls: 'tab-icon-oneDrug',
				value: 'one'
			}, {
				text: 'Составное назначение',
				iconCls: 'tab-icon-manyDrug',
				value: 'many'
			}, {
				text: 'Мед. изделия',
				iconCls: 'tab-icon-med-res',
				value: 'other'
			}, {
				pressed: true,
				hidden: true,
				value: 'search'
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

		win.ReceiptMenu = Ext6.create('Ext6.menu.Menu', {
			items: [{
				text: 'Копировать',
				itemId: 'copy',
				handler: function() {
					inDevelopmentAlert();
				}
			}]
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
				items: [win.selTemplatePanel]
			}, {
				items: [win.OneDrugFormPanel]
			}, {
				items: [win.ManyDrugFormPanel]
			}]
		});

		win.ReceiptRadioGroup = new Ext6.form.RadioGroup({
			margin: '24 16',
			name: 'receipt_type',
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
				{width:206, inputValue: 2, boxLabel: 'Рецепт за полную стоимость'},
				{width:158, inputValue: 3, boxLabel: 'Льготный рецепт'}
			],
			listeners: {
				change: function(group, nv, ov, e) {
					var value = '';
					if (!Ext6.isEmpty(nv) && Ext6.isObject(nv))
						value = nv.receipt_type;
					if (!Ext6.isEmpty(value)){
						win.setModeReceipt(value);
					}
				}
			}
		});

		win.ReceiptPanel = Ext6.create('common.EMK.SpecificationDetail.ReceiptFormPanel', {});
		// Для удобства взаимодействия
		win.ReceiptForm = win.ReceiptPanel.ReceiptForm;

		win.ReceiptPrivelegeFormInput = new Ext6.form.FormPanel({
			border: false,
			padding: '0 0 0 28',
			margin: '0 0 17 0',
			layout: {
				type: 'vbox'
			},
			defaults: {
				border: false,
				width: '100%',
				labelWidth: 120
			},
			items: [
				{
					xtype: 'datefield',
					allowBlank: false,
					format: 'd.m.Y',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
					fieldLabel: 'Дата',
					maxWidth: 231,
					name: 'EvnRecept_setDate'
				},
				{
					layout: {
						type: 'hbox'
					},
					defaults: {
						border: false
					},
					items: [
						{
							flex: 3,
							defaults: {
								border: false,
								labelWidth: 120
							},
							layout: {
								type: 'vbox'
							},
							items: [
								{
									width: 435,
									fieldLabel: 'Форма рецепта',
									displayCode: false,
									xtype: 'commonSprCombo',
									comboSubject: 'ReceptForm'
								},
								{
									width: '100%',
									defaults: {
										labelWidth: 120
									},
									layout: {
										type: 'hbox'
									},
									items: [
										{
											disabled: true,
											width: 231,
											fieldLabel: 'Серия',
											hideTrigger: true,
											xtype: 'numberfield',
											name: 'EvnRecept_Ser',
											margin: '0 50 0 0'
										},
										{
											disabled: true,
											width: 154,
											fieldLabel: 'Номер',
											labelWidth: 45,
											hideTrigger: true,
											xtype: 'numberfield',
											name: 'EvnRecept_Num'
										}
									]
								}
							]
						},
						{
							flex: 2,
							layout: {
								type: 'vbox'
							},
							defaults: {
								border: false,
								labelWidth: 110
							},
							items: [
								{
									width: '100%',
									fieldLabel: 'Тип рецепта',
									displayCode: false,
									xtype: 'commonSprCombo',
									comboSubject: 'ReceptType',
									name: 'ReceptType_id'
								},
								{
									width: '100%',
									fieldLabel: 'Срок действия',
									displayCode: false,
									name: 'ReceptValid_id',
									xtype: 'commonSprCombo',
									comboSubject: 'ReceptValid'
								}
							]
						}
					]
				},
				{
					xtype: 'commonSprCombo',
					comboSubject: 'PerformanceType',
					name: 'PerformanceType_id',
					fieldLabel: 'Отделение'
				},
				{
					xtype: 'swMedStaffFactCombo',
					fieldLabel: 'Врач',
					allowBlank: false,
					name: 'MedStaffFact_id'
				},
				{
					xtype: 'swDiagCombo',
					userCls: 'diagnoz',
					fieldLabel: 'Диагноз',
					name: 'Diag_id'
				}]
		});

		win.ReceiptFormPrivilege = new Ext6.form.FormPanel({
			border: false,
			layout: {
				type: 'vbox'
			},
			defaults: {
				border: false,
				width: '100%',
				labelWidth: 120
			},
			// reader: Ext6.create('Ext6.data.reader.Json', {
			// 	type: 'json',
			// 	model: win.id + '_ReceiptFormModel'
			// }),
			url: '/?c=EvnRecept&m=saveEvnReceptRls',
			items: [
				{
					xtype: 'hidden',
					name: 'EvnReceptGeneral_id'
				},
				win.ReceiptPrivelegeFormInput,
				{
					xtype: 'fieldset',
					padding: '0 0 0 0',
					title: 'Заявки и выписки',
					layout: 'anchor',
					defaults: {
						anchor: '100%'
					},
					collapsible: true,
					collapsed: true,
					items: [{
						html: 'Данная Форма в разработке',
						padding: '0 21 0 28',
						margin: '0 0 17 0',
						border: false,
						/*xtype: 'textfield',
						name: 'txt-test3',
						width: '100%',
					labelWidth: 120,
						fieldLabel: 'Alignment Test'*/
					}]
				},
				{
					xtype: 'fieldset',
					title: 'Льгота',
					layout: 'anchor',
					bodyPadding: '0 7 0 28',
					padding: '0 0 0 0',
					defaults: {
						anchor: '100%',
						border: false,
						labelWidth: 165
					},
					collapsible: true,
					collapsed: true,
					items: [{
						width: '100%',
						defaults: {
							labelWidth: 165,
							anchor: '100%'
						},
						layout: {
							type: 'hbox',
							padding: '0 0 0 28',
						},
						items: [
							{
								fieldLabel: 'Тип финансирования',
								hiddenName: 'DrugFinance_id',
								hideTrigger: true,
								xtype: 'commonSprCombo',
								comboSubject: 'DrugFinance',
								margin: '0 44 5 0'
							},
							{
								fieldLabel: 'Скидка',
								labelWidth: 49,
								width: 131,
								hideTrigger: true,
								xtype: 'commonSprCombo',
								hiddenName: 'ReceptDiscount_id',
								comboSubject: 'ReceptDiscount',
								margin: '0 50 5 0'
							},
							{
								xtype: 'checkboxfield',
								boxLabel: '7 Нозологий',
								hiddenName: 'EvnRecept_Is7Noz',
								margin: '0 0 0 5 0'
							}
						]
					}, {
						fieldLabel: 'Категория',
						hideTrigger: true,
						xtype: 'commonSprCombo',
						comboSubject: 'PrivilegeType',
						hiddenName: 'PrivilegeType_id',
						padding: '0 0 0 28',

					}]
				},
				{
					xtype: 'fieldset',
					title: 'Медикамент',
					padding: '0 0 0 0',
					layout: 'anchor',
					defaults: {
						border: false,
						anchor: '100%',
						labelWidth: 165
					},
					collapsible: true,
					collapsed: true,
					items: [{
						width: '100%',
						layout: {
							type: 'hbox',
							padding: '0 0 0 28',
						},
						items: [
							{
								width: 170,
								xtype: 'checkboxfield',
								boxLabel: 'Выписка по МНН',
								hiddenName: 'EvnRecept_IsMnn'
								//hiddenName: 'Drug_IsMnn',
							},
							{
								xtype: 'checkboxfield',
								boxLabel: 'Протокол ВК',
								hiddenName: 'EvnRecept_IsKEK'
							}
						]
					},{
						fieldLabel: 'Заявка',
						xtype: 'commonSprCombo',
						displayField: 'GoodsUnit_Nick',
						comboSubject: 'GoodsUnit',
						moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
						hiddenName: 'DrugRequestMnn_id',
						padding: '0 0 0 28',
					},{
						fieldLabel: 'МНН',
						xtype: 'commonSprCombo',
						hiddenName: 'DrugMnn_id',
						comboSubject: 'DrugMnn',
						padding: '0 0 0 28',
					},{
						fieldLabel: 'Торговое наименование',
						xtype: 'commonSprCombo',
						hiddenName: 'Drug_id',
						comboSubject: 'GoodsUnit',
						displayField: 'GoodsUnit_Nick',
						moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
						padding: '0 0 0 28',
						listConfig: {
							scrollable: 'y', height: 300, resizable: true, resizeHandles: "se"
						}
					},{
						width: '100%',
						layout: {
							type: 'hbox',
							padding: '0 0 0 28',
						},
						defaults: {
							labelWidth: 165
						},
						items: [
							{
								fieldLabel: 'Цена(руб.)',
								hideTrigger: true,
								name: 'Drug_Price',
								xtype: 'numberfield',
								width: 260,
								margin: '0 30 5 0'
							},
							{
								fieldLabel: 'Количество',
								labelWidth: 73,
								width: 158,
								hideTrigger: true,
								name: 'Drug_AllowedQuantity',
								xtype: 'numberfield'
							}
						]
					},{
						fieldLabel: 'Состав',
						name: 'EvnRecept_ExtempContents',
						xtype: 'textfield',
						comboSubject: 'GoodsUnit',
						displayField: 'GoodsUnit_Nick',
						moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
						padding: '0 0 0 28',
						listConfig: {
							scrollable: 'y', height: 300, resizable: true, resizeHandles: "se"
						}
					},{
						fieldLabel: 'Аптека',
						hiddenName: 'OrgFarmacy_id',
						xtype: 'commonSprCombo',
						comboSubject: 'GoodsUnit',
						displayField: 'GoodsUnit_Nick',
						moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
						padding: '0 0 0 28',
						listConfig: {
							scrollable: 'y', height: 300, resizable: true, resizeHandles: "se"
						}
					},{
						fieldLabel: 'Signa',
						name: 'EvnRecept_Signa',
						xtype: 'commonSprCombo',
						comboSubject: 'GoodsUnit',
						displayField: 'GoodsUnit_Nick',
						moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
						padding: '0 0 0 28',
						listConfig: {
							scrollable: 'y', height: 300, resizable: true, resizeHandles: "se"
						}
					}]
				}
			]
		});

		win.ReceiptPrivelegePanel = new Ext6.create('swPanel', {
			title: 'ЛЬГОТНЫЙ РЕЦЕПТ',
			autoHeight: true,
			//plain: true,
			border: false,
			cls: 'evn-course-treat-privilege-edit',
			threeDotMenu: win.ReceiptMenu,
			tools: [{
				type: 'receipt-copy',
				userCls: 'sw-tool',
				tooltip: 'Копировать',
				margin: '0 11 0 0',
				width: 16,
				handler: function() {
					inDevelopmentAlert();
				}
			},
				{
					type: 'receipt-print',
					userCls: 'sw-tool',
					tooltip: 'Печать',
					margin: '0 11 0 11',
					width: 16,
					handler: function() {
						inDevelopmentAlert();
					}
				}],
			width: '100%',
			defaults: {
				border: false,
				width: '100%',
				labelWidth: 120
			},
			frame: true,
			userCls: 'mode-of-application',
			bodyPadding: '15 21 15 0',
			layout: {
				type: 'vbox'
			},
			items: [win.ReceiptFormPrivilege]
		});

		win.ReceiptCardPanel = new Ext6.panel.Panel({
			scrollable: 'y',
			autoHeight: true,
			hidden: true,
			//plain: true,
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
				{
					items: [win.ReceiptPrivelegePanel]
				}
			]
		});

		win.TogglerToolbar = new Ext6.create('Ext6.toolbar.Toolbar', {
			items: [
				win.ModeToggler
				,'->',
				{
					width: 170,
					itemId: 'selectTemlateBtn',
					xtype: 'button',
					userCls: 'button-without-frame with-arrow-down-right',
					iconCls: 'template-btn-icon-select',
					text: 'Выбрать из шаблона',
					style: {
						color: 'black'
					},
					handler: function (btn, e) {
						win.getTemplatesMenu(btn, e);
					}
				},
				{
					itemId: 'saveTemlateBtn',
					xtype: 'button',
					userCls: 'button-without-frame',
					iconCls: 'template-btn-icon-save-as',
					style: {
						color: 'black'
					},
					text: 'Сохранить как шаблон',
					handler: function() {
						sw.swMsg.promptText(
							'Сохранить как шаблон',
							'Название',
							function (btn, text) {
								if(btn == 'ok')
									win.saveTemplate(text);
							}, 400
						);
					}
				}
			]
		});
		win.onlyMnn = new Ext6.create('Ext6.form.field.Checkbox', {
			cls: 'torg-name-on',
			labelStyle: 'margin-top: 4px;',
			style: 'margin-top: 7px;',
			value: true,
			boxLabel: 'Только по МНН'
		});
		Ext6.apply(win, {
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
					{
						height: 60,
						items: [
							win.searchCombo,
							/*{
								margin: '0 11 0 23',
								labelWidth: 83,
								width: 83 + 450,
								labelStyle: 'padding: 0px; vertical-align: middle;',
								xtype: 'combobox',
								fieldLabel: 'Препарат'
							},*/
							win.onlyMnn,
							{
								xtype: 'checkboxfield',
								cls: 'torg-name-on',
								labelStyle: 'margin-top: 4px;',
								style: 'margin-top: 7px;',
								disabled: true,
								boxLabel: 'Только из остатков'
							}
						]
					},
					win.TogglerToolbar
				]
			},
			items: [
				win.TabPanel,
				win.TreatDrugListGrid,
				win.ReceiptRadioGroup,
				win.ReceiptCardPanel
			],
			buttons: [
				{
					handler: function () {
						win.doSave();
					},
					itemId: 'saveDrugPrescr',
					cls: 'button-primary',
					text: 'СОХРАНИТЬ',
					margin: '0 10 0 19'
				},
				{
					cls: 'button-secondary',
					iconCls: 'menu-lvn-del',
					itemId: 'delDrugPrescr',
					text: 'УДАЛИТЬ НАЗНАЧЕНИЕ',
					handler: function () {
						Ext6.Msg.show({
							closable: true,
							title: 'Удаление назначения',
							msg: '<span class="msg-alert-text">Вы действительно хотите удалить назначение?</span>',
							buttons: Ext6.Msg.OKCANCEL,
							buttonText: {
								ok: 'Удалить',
								cancel: 'Отмена'
							},
							fn: function (btn) {
								if (btn == 'ok') {
									win.deletePrescr();
								}
							}
						});
					}
				},
				'->', {
					hidden: true,
					xtype: 'tbtext',
					userCls: 'save-tbar-text',
					reference: 'SaveTBarText',
					itemId: 'SaveTBarText',
					html: 'Данные сохранены'
				}
			]
		});

		this.callParent(arguments);
	}
});

/*
1)
	DrugListData: Ext.util.JSON.encode(
		[{
		"MethodInputDrug_id":"1",
		"Drug_Name":"Уголь Актив табл. 0.25г",
		"Drug_id":null,
		"DrugComplexMnn_id":203013,
		"DrugForm_Name":"Tabl.",
		"KolvoEd":1,
		"DrugForm_Nick":"табл.",
		"Kolvo":null,
		"EdUnits_id":null,
		"EdUnits_Nick":null,
		"GoodsUnit_id":null,
		"GoodsUnit_Nick":null,
		"DrugComplexMnnDose_Mass":null,
		"DoseDay":"1 табл.",
		"PrescrDose":"1 табл.",
		"GoodsUnit_sid":null,
		"status":"new","id":null,
		"FactCount":0
	}]),
2)
	"MethodInputDrug_id":"1",
		"Drug_Name":"Уголь Актив табл. 0.25г",
		"Drug_id":null,
		"DrugComplexMnn_id":203013,
		"DrugForm_Name":"Tabl.",
		"KolvoEd":1,
		"DrugForm_Nick":"табл.",
		"Kolvo":null,
		"EdUnits_id":null,
		"EdUnits_Nick":null,
		"GoodsUnit_id":null,
		"GoodsUnit_Nick":null,
		"DrugComplexMnnDose_Mass":null,
		"DoseDay":"1 табл.",
		"PrescrDose":"1 табл.",
		"GoodsUnit_sid":null,
		"status":"new","id":null,
		"FactCount":0

3)
		ReceptForm_id // Форма рецепта
		ReceptType_id  //Тип рецепта
		EvnReceptGeneral_setDate // Дата
		EvnReceptGeneral_Ser Серия
		EvnReceptGeneral_Num Номер
		EvnReceptGeneral_IsChronicDisease  Пациенту с хроническими заболеваниями
		EvnReceptGeneral_IsSpecNaz По специальному назначению
		ReceptUrgency_id  Срочность
		ReceptValid_id Срок действия
		Lpu_Name МО
		LpuSection_Name Отделение
		MedPersonal_Name Врач
		Diag_id Диагноз
		-------------------------Медикамент--------------------------------
		EvnReceptGeneralDrugLink_id0
		Drug_Name0 Наименование
		Drug_Kolvo_Pack0 Кол-во (уп.)
		Drug_Fas0 Кол-во (доз.)
		Drug_Signa0 Signa

*/

