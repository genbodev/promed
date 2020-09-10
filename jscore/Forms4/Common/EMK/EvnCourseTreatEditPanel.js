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
	extend: 'Ext6.panel.Panel',
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
	mode: 'one',
	setValuesMode: true,
	/* конструктор */
	show: function(data) {
		this.setValuesMode = true; // режим автоматического изменения данных формы
		this.callParent(arguments);

		var me = this;
		me.data = data;


		me.action = (typeof data.record == 'object' ? 'edit' : 'add');
		me.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		me.formParams = (typeof data.formParams == 'object' ? data.formParams : {});
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
				me.loadDefaultValues();
				me.getForm().findField('searchDrugNameCombo').focus();
				me.setValuesMode = false;
				break;
			default:
				me.getForm().findField('searchDrugNameCombo').focus();
				me.setValuesMode = false;
		}
		me.formStatus = 'edit';
	},
	doSave: function () {

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
		var store = me.TreatDrugListGrid.getStore();
		if(!this.manyDrug)
			store.removeAll();

		if (base_form.findField('DrugComplexMnn_id').getValue()) {
			me.TreatDrugListGrid.saveRecord();
			me.clearDrugNames();
		}
		var arr = [];
		store.getFilters().removeAll();
		store.each(function(rec) {
			/* 2) Вид массива в конце файла */
			//rec.data.MethodInputDrug_id = '1';
			if(rec.data['status'] == 'new')
				rec.data.id = null;
			arr.push(rec.data);
		});
		var DrugListData = Ext.util.JSON.encode(arr);
		var values = base_form.getValues();
		var params = {
			parentEvnClass_SysNick: 'EvnVizitPL',
			signature: 0,
			accessType: '',
			DrugListData: DrugListData, /* 1) Вид массива в конце файла */
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
				form = me.FormReceipt.getForm();
				var disableditems = me.FormReceipt.query('[disabled=true]');
				Ext6.each(disableditems, function(item) { item.enable(); });
				params = form.getValues();
				break;
			case 3:
				form = me.FormReceiptPrivilege.getForm();
				var disableditems = me.FormReceiptPrivilege.query('[disabled=true]');
				Ext6.each(disableditems, function(item) { item.enable(); });
				params = form.getValues();
				break;
			default:
				return false;
		}
		params.EvnCourseTreatDrug_id = data.EvnCourseTreatDrug_id0_saved;
		params.EvnReceptGeneral_id = 0;
		params.EvnReceptGeneral_pid = def_data.EvnVizitPL_id;
		params.Person_id = def_data.Person_id;
		params.PersonEvn_id = def_data.PersonEvn_id;
		params.MedPersonal_id = def_data.MedPersonal_id;
		params.Server_id = def_data.Server_id;
		params.EvnReceptGeneralDrugLink_id = 0;

		// @todo пока работает по старой модели, поэтому добавляем то, как было
		params.Drug_Fas0 = params.Drug_Fas;
		params.Drug_Kolvo_Pack0 = params.Drug_Kolvo_Pack;
		params.Drug_Signa0 = params.Drug_Signa;
		params.EvnReceptGeneralDrugLink_id0 = 0;

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
					cb();
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
		if(me.parentPanel && me.parentPanel.ownerPanel && me.parentPanel.ownerPanel.EvnReceptPanel){
			me.parentPanel.ownerPanel.EvnReceptPanel.loadBothGrids();
		} else {
			var ReceptPanels = Ext6.ComponentQuery.query('panel[refId=\"EvnReceptPanel\"]');
			ReceptPanels.forEach(function(panel){
				panel.loadBothGrids();
			});
		}
	},
	/* методы */
	deletePrescr: function () {
		var me = this,
			specPan = me.parentPanel,
			cntr = specPan.getController();
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
							cntr.openSpecification('EvnCourseTreat', grid, grid.getSelectionModel().getSelectedRecord());
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
			saveBtn = me.down('#saveDrugPrescr');
		me.TreatDrugListGrid.hide();
		switch(mode){
			case 'one':
				me.TabPanel.getLayout().setActiveItem(0);
				me.manyDrug = false;
				break;
			case 'many':
				me.TabPanel.getLayout().setActiveItem(1);
				me.manyDrug = true;
				me.TreatDrugListGrid.show();
				break;
			default:
				me.TabPanel.getLayout().setActiveItem(0);
				me.manyDrug = false;
				mode = 'one';
		}
		var base_form = me.getForm();
		base_form.findField('searchDrugNameCombo').focus();
		me.ModeToggler.setValue(mode);
		me.mode = mode;
		saveBtn.disable();
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
			}
			if(me.values)
				me.values = Ext6.Object.merge(me.values,data);
			else me.values = data;
			me.setValuesFromData();
		}
	},
	/**
	 * Очищение полей способа применения по кнопке "Очистить"
	 */
	clearDrugNames: function(){
		var me = this,
			panel = me.FormPanelManyDrug.down('#manyDrugNamesPanel'),
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
			base_form.setValues(fieldsVals);
		}
	},
	loadValuesIntoForm: function(){
		var me = this,
			rec = me.data.record;
		// Дополним значения пришедшей записью
		me.values = rec.getData();
		Ext6.Ajax.request({
			url: '/?c=EvnPrescr&m=loadEvnCourseTreatEditForm',
			params: {
				EvnCourseTreat_id: rec.get('EvnCourse_id'),
				parentEvnClass_SysNick: Ext6.JSON.encode({
					object: "EvnCourseTreatEditWindow",
					identField: "EvnCourseTreat_id"
				})
			},
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
							me.ReceiptRadioGroup.setValue({'tep_receipt_type': 2});
						}
					};
					me.loadTreat(false,onLoadMnn);
					// Если по назначению заведен рецепт - открываем форму с ним
					me.setValuesMode = false; // Далее форма будет изменяться вручную
					me.changeMethodText('force'); // Устанавливаем текст способа применения
				}
				else
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'));
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
		base_form.setValues(fieldValues); // Чтобы они не попали под isDirty действия
	},
	setModeReceipt: function(value){

		var me = this;
		me.modeReceipt = value;
		switch(value){
			case 1:
				me.ReceiptCardPanel.hide();
				break;
			case 2:
				me.ReceiptCardPanel.show();
				me.ReceiptCardPanel.getLayout().setActiveItem(0);
				me.loadReceiptGeneralFieldValues();
				break;
			case 3:
				me.ReceiptCardPanel.show();
				me.ReceiptCardPanel.getLayout().setActiveItem(1);
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
	/**
	 * Задание текста способа применения вручную или событием с поля
	 * @param comp флаг события: 'force' - вручную из кода, либо тип component, если по событию с поля
	 * @returns {boolean}
	 */
	changeMethodText: function(comp){

		var win = this;
		if(win.setValuesMode)
			return false;
		var arrNames = [
			'PrescriptionIntroType_id', //Перорально
			'EvnPrescrTreat_Descr', //во время еды, запивая
			'EvnCourseTreat_Duration', // в течение 14
			'DurationType_id', //  дней
			'EvnCourseTreat_CountDay', // 2 раза в день
			'KolvoEd', // по 1
			'GoodsUnit_sid' // табл. за прием.
		];
		if((comp!='force' && comp.getName() && arrNames.indexOf(comp.getName()) != -1) || comp == 'force' ){
			var mpanel = win.MethodPanel,
				methodText = mpanel.down('#methodText'),
				base_form = win.getForm(),
				str_val = '';
			arrNames.forEach(function(e){
				if(e){
					var field = base_form.findField(e),
						rec, v = field.getValue(), temp;
					if(!field) return false;
					if (v) {
						switch (e) {
							case 'PrescriptionIntroType_id':
								rec = field.getSelectedRecord();
								str_val += rec.get('PrescriptionIntroType_Name');
								break;
							case 'EvnPrescrTreat_Descr':
								str_val += ', ' + v;
								break;
							case 'EvnCourseTreat_Duration':
								str_val += ', в течение ' + field.getRawValue();
								break;
							case 'DurationType_id':
								switch (v) {
									case 1:
										temp = ' дней';
										break;
									case 2:
										temp = ' недель';
										break;
									case 3:
										temp = ' месяцев';
										break;
									default:
										temp = ' дней';
								}
								str_val += temp;
								break;
							case 'EvnCourseTreat_CountDay':
								temp = ' раз в день';
								if ([2, 3, 4].indexOf(v % 10) != -1)
									temp = ' раза в день';
								str_val += ', ' + v + temp;
								break;
							case 'KolvoEd':
								str_val += ' по ' + v;
								break;
							case 'GoodsUnit_sid':
								rec = field.getSelectedRecord();
								str_val += ' ' + (rec?rec.get('GoodsUnit_Nick'):v) + ' за приём';
								break;
						}
					}
				}
			});
			methodText.setHtml(str_val);
		}
		else{
			return false;
		}
	},
	reset: function(){
		var me = this,
			store = me.TreatDrugListGrid.getStore(),
			fOne = me.FormPanelOneDrug.getForm(),
			fMany = me.FormPanelManyDrug.getForm(),
			fReceipt = me.FormReceipt.getForm(),
			mpanel = me.MethodPanel,
			methodText = mpanel.down('#methodText');
		store.removeAll();
		methodText.reset();
		fReceipt.reset();
		//me.setModeReceipt(1);
		me.ReceiptRadioGroup.setValue({'tep_receipt_type': 1});
		//Включаем подсветку
		fOne.isValid();
		fMany.isValid();
		// @todo это говно надо убрать
		// Но к сожалению Ext6 пока не может предложить альтернативу способа сделать форму не isDirty,
		me.setFormIsDirty(fOne);
		me.setFormIsDirty(fMany);
	},
	/**
	 * Делаем форму form - чистой - т.е. не isDirty
	 * для этого нужно обнулить оригинальное значение и вернуть флаг "чистоты"
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
	 * Получение активного на данный момент компонента Ext.form.Panel
	 * @returns {{}} form
	 */
	getForm: function(){
		var me = this,
			base_form = {};
		if(this.manyDrug)
			base_form = me.FormPanelManyDrug.getForm();
		else
			base_form = me.FormPanelOneDrug.getForm();
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
	getSerNumReceipt: function(date, cbFn){
		Ext6.Ajax.request({
			params: {
				isGeneral: 1,
				ReceptForm_id: 3,
				EvnRecept_setDate: date
			},
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var recept_Num = (response_obj.EvnRecept_Num) ? response_obj.EvnRecept_Num : '';
					var recept_Ser = (response_obj.EvnRecept_Ser) ? response_obj.EvnRecept_Ser : '';

					cbFn(recept_Ser,recept_Num);
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера рецепта', function() { base_form.findField('EvnReceptGeneral_setDate').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			url: C_RECEPT_NUM
		});
	},
	loadReceiptGeneralFieldValues: function(){

		var me = this,
			base_form = me.getForm(),
			values = base_form.getValues(),
			mpanel = me.MethodPanel,
			methodText = mpanel.down('#methodText'),
			mnn = base_form.findField('DrugComplexMnn_id'),
			mnn_rec = mnn.getSelectedRecord();

		if(!Ext6.isEmpty(values.EvnReceptGeneral_id) && values.EvnReceptGeneral_id>0){

			//грузим рецепт из БД
			Ext6.Ajax.request({
				url: '/?c=EvnRecept&m=loadEvnReceptGeneralEditForm',
				params: {
					EvnReceptGeneral_id: values.EvnReceptGeneral_id,
					fromExt6: 2
				},
				callback: function(opt, success, response) {
					if (success && response && response.responseText) {
						var dec_data = Ext6.JSON.decode(response.responseText);
						values.fromBD = true;
						values = Ext6.Object.merge(values,dec_data[0]);

						me.setValuesGeneralReceiptForm(values);
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
			var cbFn = function(ser, num){

				values.EvnReceptGeneral_Ser = ser.toString();
				values.EvnReceptGeneral_Num = num.toString();
				me.setValuesGeneralReceiptForm(values);
			};
			me.getSerNumReceipt(values.EvnCourseTreat_setDate,cbFn);

		}
	},
	setValuesGeneralReceiptForm: function(values){

		var me = this,
			data = me.data,
			form = me.FormReceipt.getForm(),
			lpu_s = form.findField('LpuSection_id'),
			Drug_Kolvo_Pack = 1;
		if(values.Drug_Kolvo_Pack)
			Drug_Kolvo_Pack = values.Drug_Kolvo_Pack;
		else
			Drug_Kolvo_Pack = (values.KolvoEd*values.EvnCourseTreat_CountDay*values.EvnCourseTreat_Duration);
		var params = {
			"EvnReceptGeneral_setDate": values.EvnCourseTreat_setDate,
			"Lpu_id": data.userMedStaffFact.Lpu_id,
			"MedPersonal_id": data.MedPersonal_id,
			"MedPersonal_Name":data.userMedStaffFact.MedPersonal_FIO,
			"MedStaffFact_id": data.userMedStaffFact.MedStaffFact_id,
			"LpuSection_id": data.userMedStaffFact.LpuSection_id,
			"LpuSection_Name": data.userMedStaffFact.LpuSection_Name,
			"Diag_id":	me.parentPanel.getData().Diag_id,
			"EvnReceptGeneral_Ser":values.EvnReceptGeneral_Ser,
			"EvnReceptGeneral_Num":values.EvnReceptGeneral_Num,
			"EvnCourseTreatDrug_id":values.id,
			"Drug_Name":values.Drug_Name,
			// (кол-во лс на прием)*(Приёмов в сутки)*(Продолжительность)*тип продолжительности / видимо надо разделить на кол-во в упаковке
			"Drug_Kolvo_Pack":Drug_Kolvo_Pack, // @todo проверить вычисления
			"Drug_Fas":values.Drug_Fas, // @todo проверить вычисления
			"Drug_Signa":values.Drug_Signa,
			// Параметры по умолчанию
			"ReceptType_id":2, // на листе
			"ReceptForm_id":3 // 107 форма
		};
		lpu_s.fireEvent('change', lpu_s, params.LpuSection_id);
		form.setValues(params);
	},
	filterMedStaffFactCombo: function(v) {
		var me = this;
		var base_form = me.FormReceipt.getForm();
		var msf = me.data.userMedStaffFact;

		var medstafffact_filter_params = {
			allowLowLevel: 'yes',
			isPolka: true
		};

		if (!Ext6.isEmpty(base_form.findField('EvnReceptGeneral_setDate').getValue())) {
			medstafffact_filter_params.onDate = base_form.findField('EvnReceptGeneral_setDate').getValue().format('d.m.Y');
		}

		// Фильтр на конкретное место работы
		if (!Ext6.isEmpty(msf.LpuSection_id) && !Ext6.isEmpty(msf.MedStaffFact_id)) {
			if (msf.MedStaffFactCache_IsDisableInDoc == 2) {
				sw.swMsg.alert(langs('Сообщение'), langs('Текущее рабочее место запрещено для выбора в документах'));
				medstafffact_filter_params.id = -1;
			}
			medstafffact_filter_params.id = msf.MedStaffFact_id;
		}

		medstafffact_filter_params.allowDuplacateMSF = true;
		medstafffact_filter_params.EvnClass_SysNick = 'EvnVizit';

		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		if(v){
			base_form.findField('MedStaffFact_id').setValue(v);
		}
	},
	filterLpuSectionCombo: function () {
		var win = this;
		var me = this;
		var base_form = me.FormReceipt.getForm(),
			LpuSectionCombo = base_form.findField('LpuSection_id'),
			LpuSection_id = base_form.findField('LpuSection_id').getValue();
			//LpuSectionProfile_Code = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code');


		if (!LpuSectionCombo.isVisible()) {
			return false;
		}

		LpuSectionCombo.getStore().clearFilter();
		LpuSectionCombo.lastQuery = '';

		var setComboValue = function (combo, id) {
			if (Ext6.isEmpty(id)) {
				return false;
			}

			var index = combo.getStore().findBy(function (rec) {
				return (rec.get('LpuSection_id') == id);
			});

			if (index == -1 && combo.isVisible()) {
				combo.clearValue();
			}
			else {
				combo.setValue(id);
			}

			return true;
		}

		//if (base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id) {
			setLpuSectionGlobalStoreFilter({
				isOnlyStac: true,
				/*mode: 'combo',
				Lpu_id: base_form.findField('Lpu_did').getValue()*/
				//lpuSectionProfileCode: LpuSectionProfile_Code
			});
			LpuSectionCombo.getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
			setComboValue(LpuSectionCombo, LpuSection_id);
		/*}
		else {
			LpuSectionCombo.getStore().load({
				params: {
					mode: 'combo',
					Lpu_id: base_form.findField('Lpu_id').getValue()
				},
				callback: function () {
					setComboValue(LpuSectionCombo, LpuSection_id);
				}
			});
		}*/
	},
	initComponent: function() {
		var win = this;
		win.manyDrug = false; // Убрать
		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model',
			fields: [
				{name: 'TariffValue_id'},
				{name: 'TariffValue_Code'},
				{name: 'TariffValue_Value'},
				{name: 'TariffValue_begDT'},
				{name: 'TariffValue_endDT'}
			]
		});
		// Ext6.define(win.id + '_ReceiptFormModel', {
		// 	extend: 'Ext6.data.Model',
		// 	fields: [
		// 		{name: 'TariffValue_id'},
		// 		{name: 'TariffValue_Code'},
		// 		{name: 'TariffValue_Value'},
		// 		{name: 'TariffValue_begDT'},
		// 		{name: 'TariffValue_endDT'}
		// 	]
		// });
		win.TreatDrugListGrid = Ext6.create('Ext6.grid.Panel', {
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
							}
							win.TreatDrugListGrid.reconfigure();
							//panel.getStore().remove(record);

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
			getDrugFormData: function()
			{
				var base_form;
				base_form = win.getForm();
				var data = {};
				/*'DrugComplexMnn_id': ,
				'Drug_Name': ,
				'Drug_id': ,
				'Kolvo': ,
				'MethodInputDrug_id': ,
				'Drug_Name': ,
				'Drug_id': ,
				'DrugForm_Name': ,
				'KolvoEd': ,
				'DrugForm_Nick': ,
				'EdUnits_id': ,
				'EdUnits_Nick': ,
				'GoodsUnit_id': ,
				'GoodsUnit_Nick': ,
				'DrugComplexMnnDose_Mass': ,
				'DoseDay': ,
				'PrescrDose': ,
				'GoodsUnit_sid': ,
				'status': ,
				'id': ,
				'FactCount': '',
				"DrugForm_Nick":"табл.",
				"DoseDay":"1 табл.",
				"PrescrDose":"1 табл.",
				*/
				var DrugForm_Name = '';
				if(base_form.findField('DrugComplexMnn_id').getSelectedRecord())
					DrugForm_Name = base_form.findField('DrugComplexMnn_id').getSelectedRecord().get('RlsClsdrugforms_Name');
				else{
					debugger;
				}

				data['Drug_Name'] = base_form.findField('DrugComplexMnn_id').getRawValue();
				data['Drug_id'] = base_form.findField('Drug_id').getValue() ||null;
				data['DrugForm_Name'] = DrugForm_Name;
				data['DrugComplexMnn_id'] = base_form.findField('DrugComplexMnn_id').getValue() || null;
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
				data['id'] = base_form.findField('id').getValue() || '';
				console.log('id: '+data['id']);
				data['status'] = data['id']?'updated':'new';
				data['EdUnits_id'] = null;
				data['EdUnits_Nick'] = null;
				data['DrugComplexMnnDose_Mass'] = null;
				data['DoseDay'] = (parseInt(data['Kolvo']) * parseInt(data['EvnCourseTreat_CountDay'])).toString()+data['GoodsUnit_Nick'];
				//data['PrescrDose'] = parseInt(data['Kolvo']) * parseInt(base_form.findField('EvnCourseTreat_CountDay').getValue())*(Продолжительность)*месяц(30дней)
				data['DrugForm_Nick'] =  base_form.findField('DrugComplexMnn_id').getSelectedRecord().get('RlsClsdrugforms_Name')||'';
				data = this.reCountData(data);

				return data
			},
			saveRecord: function(data)
			{
				var me = this,
					store = me.getStore();

				if(!data)
					data = me.getDrugFormData();

				if(data.id){
					var rec = store.findRecord('id',data.id);
					if(!rec)
						me.getSelectionModel().getSelectedRecord();
					if(rec)
						store.remove(rec);
				}
				/*me.getStore().add({
					'DrugComplexMnn_id': ,
					'Drug_Name': ,
					'Drug_id': ,
					'Kolvo': ,
					'MethodInputDrug_id': ,
					'Drug_Name': ,
					'Drug_id': ,
					'DrugForm_Name': ,
					'KolvoEd': ,
					'DrugForm_Nick': ,
					'EdUnits_id': ,
					'EdUnits_Nick': ,
					'GoodsUnit_id': ,
					'GoodsUnit_Nick': ,
					'DrugComplexMnnDose_Mass': ,
					'DoseDay': ,
					'PrescrDose': ,
					'GoodsUnit_sid': ,
					'status': ,
					'id': ,
					'FactCount':
				});*/
				//КОСТЫЛЬ ПОТОМ УДАЛИТЬ
				data.DrugComplexMnnDose_Mass = null;
				store.add(data);
			},
			reCountData: function(data)
			{
				// Расчет суточной и курсовой доз
				var dd_text='', kd_text='', dd=0, kd=0, ed = '', multi = 1;

				//Дневная доза – Прием в ед. измерения (либо количество ед. дозировки*дозировку)*Приемов в сутки
				if ( data['Kolvo'] && data['GoodsUnit_id'] /*&& data['EdUnits_id']*/ ) {
					// в ед. измерения
					dd = data['EvnCourseTreat_CountDay']*data['Kolvo'];
					if (data['GoodsUnit_Nick']){
						dd_text = dd +' '+ data['GoodsUnit_Nick'];
						ed = data['GoodsUnit_Nick'];
					}
				}
				if (data['KolvoEd'] && !data['Kolvo']) {
					// в ед. дозировки только если не указано в ед.измерения
					dd = data['EvnCourseTreat_CountDay']*data['KolvoEd'];
					if (data['GoodsUnit_SNick']){
						dd_text = dd +' '+ data['GoodsUnit_SNick'];
						ed = data['GoodsUnit_SNick'];
					}
				}
				if (dd > 0 && data['EvnCourseTreat_Duration']>0) {
					switch (data['DurationType_id']) {
						case 1: // дней
							multi = 1;
							break;
						case 2: // недель
							multi = 7;
							break;
						case 3: // месяцев
							multi = 30;
							break;
					}
					kd = dd*data['EvnCourseTreat_Duration']*multi;
					kd_text=kd +' '+ ed;
				}
				data['DoseDay'] = dd_text;
				data['PrescrDose'] = kd_text;
				return data;
			},
			listeners:{
				select: function (grid, record, index, eOpts) {
					win.loadTreat(index,false);
				}
			}
		});

		win.MethodPanel = Ext6.create('Ext6.panel.Panel', {
			title: 'СПОСОБ ПРИМЕНЕНИЯ',
			width: '100%',
			frame: true,
			userCls: 'mode-of-application',
			bodyPadding: '15 10',
			layout: {
				type: 'hbox'
			},
			defaults: {
				border: false
			},
			border: false,
			items: [
				{
					maxWidth: 450,
					defaults: {
						width: '100%',
						labelWidth: 140,
						padding: '5 0 0 0',
						margin: 0,
						listeners: {
							change: {
								fn: 'changeMethodText',
								scope: this
							}
						}
					},
					flex: 5,
					items: [{
						xtype: 'commonSprCombo',
						comboSubject: 'PrescriptionIntroType',
						sortField: 'PrescriptionIntroType_Code',
						name: 'PrescriptionIntroType_id',
						fieldLabel: 'Способ применения',
						listConfig: {
							cls: 'choose-bound-list-menu update-scroller'
						},
						allowBlank: false,
						forceSelection: true
					}, {
						xtype: 'datefield',
						allowBlank: false,
						format: 'd.m.Y',
						value: new Date(),
						plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
						fieldLabel: 'Начать',
						maxWidth: 251,
						name: 'EvnCourseTreat_setDate',
						listeners: {
							'change': function(val){
								this.checkPersonDrugReactionInEvn();
							}.createDelegate(this)
						}
					}, {
						border: false,
						layout: {
							type: 'hbox'
						},
						defaults: {
							allowBlank: false,
							listeners: {
								change: {
									fn: 'changeMethodText',
									scope: this
								}
							}
						},
						items: [{
							xtype: 'numberfield',
							fieldLabel: 'Продолжительность',
							name: 'EvnCourseTreat_Duration',
							hideTrigger: true,
							//value: 1,
							minValue: 1,
							labelWidth: 140,
							width: 140+76
						}, {
							xtype: 'commonSprCombo',
							displayCode: false,
							comboSubject: 'DurationType',
							name: 'DurationType_id',
							padding: '0 16 0 9',
							width: 102
						}, {
							xtype: 'checkboxfield',
							boxLabel: 'До выписки',
							disabled: true,
							name: 'PayType_id',
							hidden: getCurArm().inlist(['polka', 'common'])
						}]
					},
						{
							xtype: 'numberfield',
							hideTrigger: true,
							name: 'EvnCourseTreat_CountDay',
							labelWidth: 140,
							fieldLabel: 'Приёмов в сутки',
							maxWidth: 328,
							allowBlank: false,
							listeners: {
								change: {
									fn: 'changeMethodText',
									scope: this
								}
							}
						}, {
							layout: {
								type: 'hbox'
							},
							defaults: {
								listeners: {
									change: {
										fn: 'changeMethodText',
										scope: this
									}
								}
							},
							border: false,
							items: [{
								name: 'KolvoEd',
								minValue: 0.0001,
								xtype: 'numberfield',
								hideTrigger: true,
								fieldLabel: 'Кол-во ЛС на прием',
								allowBlank: false,
								labelWidth: 140,
								flex: 1
							}, {
								xtype: 'commonSprCombo',
								displayCode: false,
								displayField: 'GoodsUnit_Nick',
								moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
								comboSubject: 'GoodsUnit',
								name: 'GoodsUnit_sid',
								hiddenName: 'GoodsUnit_sid',
								border: false,
								forceSelection: true,
								allowBlank: false,
								minHeight: 25,
								padding: '0 0 0 8',
								flex: 1,
								loadingText: 'Загрузка...',
								listConfig: {
									scrollable: 'y', height: 300, resizable: true, resizeHandles: "se",
									cls: 'choose-bound-list-menu update-scroller'
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
								labelWidth: 140,
								//width: 220,
								flex: 1
							}, {
								xtype: 'commonSprCombo',
								displayCode: false,
								comboSubject: 'GoodsUnit',
								displayField: 'GoodsUnit_Nick',
								moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
								name: 'GoodsUnit_id',
								padding: '0 0 0 8',
								flex: 1,
								forceSelection: true,
								loadingText: 'Загрузка...',
								listConfig: {
									scrollable: 'y', height: 300, resizable: true, resizeHandles: "se",
									cls: 'choose-bound-list-menu update-scroller'
								}
							}]
						}, {
							xtype: 'commonSprCombo',
							displayCode: false,
							comboSubject: 'PerformanceType',
							name: 'PerformanceType_id',
							forceSelection: true,
							fieldLabel: 'Исполнение'
						}, {
							xtype: 'textfield',
							fieldLabel: 'Комментарий',
							name: 'EvnPrescrTreat_Descr'
						}, {
							padding: '0 0 0 145',
							xtype: 'checkboxfield',
							boxLabel: 'Cito!',
							name: 'EvnPrescrTreat_IsCito'
						}]
				}, {
					layout: 'vbox',
					flex: 3,
					bodyPadding: '0 30 10 15',
					items: [
						{
							border: false,
							cls: 'method-description',
							itemId: 'methodText',
							minHeight: 67,
							maxWidth: 250,
							style:{
								'font':'400 12px/14px Roboto, Helvetica, Arial, Geneva, sans-serif',
								'color': '#333 !important'
							},
							html: '',
							getValue: function(){
								return this.html.toString();
							},
							reset: function () {
								this.setHtml('');
							}
						},
						{
							xtype: 'button',
							handler: function () {
								//win.setMode('one');
								var fields = win.MethodPanel.query('field');
								fields.forEach(function (e) {
									if (e && e.reset)
										e.reset();
								});
							},
							cls: 'button-secondary',
							text: 'СБРОС'
						}
					]
				}
			]
		});

		win.FormPanelOneDrug = new Ext6.form.FormPanel({
			//autoScroll: true,
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
					xtype: 'hiddenfield',
					name: 'id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnCourseTreat_id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnReceptGeneral_id'
				},
				{
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					defaults: {
						border: false,
						labelWidth: 75
					},
					items: [
						{
							layout: {
								type: 'hbox'
							},
							bodyPadding: '0 10 15 15',
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'anchor',
									itemId: 'OneDrugAddFormPanel',
									maxWidth: 460,
									defaults: {
										anchor: '100%',
										padding: '5 0 0 0',
										margin: 0
									},
									flex: 5,
									items:[{
										xtype: 'swSearchDrugComplexMnnCombo',
										name: 'searchDrugNameCombo',
										listConfig: {
											cls: 'choose-bound-list-menu update-scroller'
										},
										triggers: {
											clear: {
												cls: 'sw-clear-trigger',
												hidden: true,
												handler: function () {
													if (this.disabled) return false;
													var fieldsAddDrug = win.FormPanelOneDrug.queryById('OneDrugAddFormPanel');
													fieldsAddDrug.query('field').forEach(function (e) {
														if (e && e.reset) {
															e.reset();
														}
													});
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
									},{
										xtype: 'swDrugComplexMnnCombo',
										readOnly: true,
										allowBlank: false,
										name: 'DrugComplexMnn_id'
									},{
										xtype: 'textfield',
										fieldLabel: 'Rp',
										readOnly: true,
										name: 'LatName'
									},{
										xtype: 'swDrugCombo',
										userCls: 'drugs-trade-name',
										name: 'Drug_id'
									}]
								}/*,{
								width: 260,
								height: 100,
								html: '<img src="img/icons/emk/panelicons/VarningAlertIcon.png"  style="float: left; margin-right: 13px">'+'<div style="overflow: auto">'+'<p>'+'Внимание! У пациента выявлена аллергическая реакция на данный препарат!'+'</p>'+'</div>',
								cls: 'drug-allerg'
							}*/
							]
						},
						win.MethodPanel
					]
				}],
			listeners: {
				'dirtychange': function (comp, dirty, eOpts) {
					var saveBtn = win.down('#saveDrugPrescr');
					saveBtn.setDisabled(!dirty);
				},
				'fieldvaliditychange': function ( comp, field, isValid, eOpts ){
					win.ReceiptRadioGroup.setDisabled(!comp.isValid())
				}
			}
		});

		win.addToCourseBtn = Ext6.create('Ext6.button.Button',{
			padding: '7 0',
			//width: 'auto',
			minWidth: 165,
			maxWidth: 190,
			margin: '17 0 0 0',
			height: 30,
			xtype: 'button',
			iconCls: 'add-course',
			cls: 'button-secondary',
			text: 'ДОБАВИТЬ В КУРС',
			handler: function(){
				win.TreatDrugListGrid.saveRecord();
				win.clearDrugNames();
			}
		});
		win.FormPanelManyDrug = new Ext6.form.FormPanel({
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
					xtype: 'hiddenfield',
					name: 'id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnCourseTreat_id'
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
									layout: 'anchor',
									itemId: 'MadyDrugAddFormPanel',
									maxWidth: 460,
									defaults: {
										anchor: '100%',
										padding: '5 0 0 0',
										margin: 0
									},
									flex: 5,
									items: [{
										xtype: 'swSearchDrugComplexMnnCombo',
										name: 'searchDrugNameCombo',
										listConfig: {
											cls: 'choose-bound-list-menu update-scroller'
										},
										listeners: {
											select: function (combo, record, eOpts) {
												win.onSelectRecSearchCombo(record);
											},
											keyup: function (field, e) {
												if (!Ext6.isEmpty(e.target.value)) {
													this.triggers.clear.show();
													this.triggers.search.hide();
												} else {
													this.triggers.clear.hide();
													this.triggers.search.show();
												}
											}
										},
										triggers: {
											clear: {
												cls: 'sw-clear-trigger',
												hidden: true,
												handler: function () {
													if (this.disabled) return false;
													this.setValue('');
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
									}, {
										xtype: 'swDrugComplexMnnCombo',
										readOnly: true,
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
									}, win.addToCourseBtn]
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
						{
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
										sortField: 'PrescriptionIntroType_Code',
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
						}]
				}],
			listeners: {
				'dirtychange': function (comp, dirty, eOpts) {
					var saveBtn = win.down('#saveDrugPrescr');
					saveBtn.setDisabled(!dirty);
				}
			}
		});

		win.ModeToggler = Ext6.create('Ext6.button.Segmented', {
			margin: '0 0 0 22',
			allowDepress: true,

			items:[{
				text: 'Один препарат',
				iconCls: 'tab-icon-oneDrug',
				pressed: true,
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
			cls: 'EvnCourseTreatEdit',
			defaults: {
				border: false
			},
			layout: 'card',
			header: false,

			tbar: {
				xtype: 'toolbar',
				cls: 'toptoolbar',
				items: [win.ModeToggler]
			},
			items: [{
				items: [win.FormPanelOneDrug]
			}, {
				items: [win.FormPanelManyDrug]
			}]
		});

		win.ReceiptRadioGroup = new Ext6.form.RadioGroup({
			margin: '24 16',
			name: 'tep_receipt_type',
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
						value = nv.tep_receipt_type;
					if (!Ext6.isEmpty(value)){
						win.setModeReceipt(value);
					}
				}
			}
		});

		win.FormReceipt = new Ext6.form.FormPanel({
			border: false,
			layout: {
				type: 'vbox'
			},
			defaults: {
				border: false,
				width: '100%',
				labelWidth: 120
			},
			padding: '0 0 0 28',
			margin: '0 0 17 0',
			/*reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_ReceiptFormModel'
			}),*/
			url: '/?c=EvnRecept&m=saveEvnReceptRls',
			items: [
				{
					xtype: 'datefield',
					allowBlank: false,
					format: 'd.m.Y',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
					fieldLabel: 'Дата',
					maxWidth: 231,
					name: 'EvnReceptGeneral_setDate',
					listeners: {
						'change': function() {
							win.filterMedStaffFactCombo();
						}
					}
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
									disabled: true,
									fieldLabel: 'Форма рецепта',
									displayCode: false,
									xtype: 'commonSprCombo',
									comboSubject: 'ReceptForm',
									name: 'ReceptForm_id'
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
											xtype: 'textfield',
											name: 'EvnReceptGeneral_Ser',
											margin: '0 50 0 0'
										},
										{
											disabled: true,
											width: 154,
											fieldLabel: 'Номер',
											labelWidth: 45,
											hideTrigger: true,
											xtype: 'textfield',
											name: 'EvnReceptGeneral_Num'
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
					xtype: 'swLpuCombo',
					additionalRecord: {
						value: -1,
						text: langs('Все'),
						code: 0
					},
					anyMatch: true,
					hideEmptyRow: true,
					listConfig:{
						minWidth: 500
					},
					fieldLabel: 'МО',
					name: 'Lpu_id',
					/*'change': function(combo, newValue, oldValue) {
						win.filterLpuSectionCombo();
					}*/
				},
				{
					allowBlank: false,
					fieldLabel: 'Отделение',
					name: 'LpuSection_id',
					itemId: 'LpuSectionCombo',
					xtype: 'SwLpuSectionGlobalCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.filterLpuSectionCombo();/*
							var base_form = parentWindow.FormPanel.getForm();
							var MedStaffFactFilterParams = {
								allowLowLevel: 'yes',
								//onDate:
							};
							MedStaffFactFilterParams.LpuSection_id = newValue;
							setMedStaffFactGlobalStoreFilter(MedStaffFactFilterParams);
							base_form.findField('MedStaffFact_id').setValue('');
							base_form.findField('MedStaffFact_id').getStore().removeAll();
							base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
						*/
						}
					}
				},
				{
					xtype: 'swMedStaffFactCombo',
					fieldLabel: 'Врач',
					name: 'MedStaffFact_id',
					listeners: {
						'change': function(comp, newValue, oldValue) {
							win.filterMedStaffFactCombo(newValue);
						}
					}
				},
				{
					xtype: 'swDiagCombo',
					userCls: 'diagnoz',
					fieldLabel: 'Диагноз',
					name: 'Diag_id'
				},
				{
					xtype: 'fieldset',
					title: 'Медикамент',
					padding: '0 0 0 0',
					layout: 'anchor',
					defaults: {
						border: false,
						anchor: '100%',
						labelWidth: 120
					},
					collapsible: true,
					collapsed: false,
					items: [
						{
							name: 'EvnReceptGeneralDrugLink_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Drug_Name',
							disabled: true,
							fieldLabel: 'Наименование',
							width: 517,
							tabIndex: TABINDEX_EVNPRESCR + 122,
							xtype: 'textfield'
						},
						{
							width: '100%',
							layout: {
								type: 'hbox'
							},
							defaults: {
								border: false,
								labelWidth: 120
							},
							items: [
								{
									width: 231,
									allowBlank: true,
									allowNegative: false,
									fieldLabel: 'Кол-во (уп.)',
									minValue: 0.01,
									name: 'Drug_Kolvo_Pack',
									hideTrigger: true,
									validateOnBlur: true,
									margin: '0 15 5 0',
									listeners: {
										'change': function (cmp, value) {
											/*var base_form = wnd.findById('EvnReceptGeneralEditForm').getForm();
											if(!Ext.isEmpty(value))
											{
												base_form.findField('Drug_Fas0').setValue(base_form.findField('Drug_Fas_0').getValue() * value);
											}
											else
												base_form.findField('Drug_Fas0').setValue('');*/
										}
									},
									xtype: 'numberfield'
								},
								{
									allowBlank: true,
									width: 194,
									allowNegative: false,
									labelWidth: 85,
									disabled: true,
									fieldLabel: 'Кол-во (доз.)',
									hideTrigger: true,
									minValue: 0.01,
									name: 'Drug_Fas',
									validateOnBlur: true,
									//value: 1,
									xtype: 'numberfield'
								}
							]
						},
						{
							allowBlank: true,
							fieldLabel: 'Signa',
							name: 'Drug_Signa',
							validateOnBlur: true,
							width: 517,
							xtype: 'textfield',
							bodyStyle: 'margin-top: 50px;'
						}
					]
				}
			]
		});

		win.ReceiptPanel = new Ext6.create('swPanel', {
			title: 'РЕЦЕПТ ЗА ПОЛНУЮ СТОИМОСТЬ',
			//threeDotMenu: win.ReceiptMenu,
			frame: true,
			autoHeight: true,
			width: '100%',
			border: false,
			cls: 'EvnCourseTreatEdit',
			defaults: {
				border: false,
				width: '100%',
				labelWidth: 120
			},
			bodyPadding: '15 21 15 0',
			userCls: 'mode-of-application',
			layout: {
				type: 'vbox'
			},
			items: [win.FormReceipt]
		});

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

		win.FormReceiptPrivilege = new Ext6.form.FormPanel({
			border: false,
			layout: {
				type: 'vbox'
			},
			defaults: {
				border: false,
				width: '100%',
				labelWidth: 120
			},
			/*reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_ReceiptFormModel'
			}),*/
			url: '/?c=EvnRecept&m=saveEvnReceptRls',
			items: [
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
			cls: 'EvnCourseTreatEdit evn-course-treat-privilege-edit',
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
			items: [win.FormReceiptPrivilege]
		});

		win.ReceiptCardPanel = new Ext6.panel.Panel({
			scrollable: 'y',
			autoHeight: true,
			hidden: true,
			//plain: true,
			border: false,
			cls: 'EvnCourseTreatEdit',
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

		Ext6.apply(win, {
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
					handler: function () {
						win.deletePrescr();
					},
					cls: 'button-secondary',
					iconCls: 'menu-lvn-del',
					itemId: 'delDrugPrescr',
					text: 'УДАЛИТЬ НАЗНАЧЕНИЕ'
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