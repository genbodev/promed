/**
 * swSmpIllegalActEditWindow - окно редактирования случая противоправного действия в отношении персонала СМП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2010 Swan Ltd.
 * @author		Bykov Stas aka Savage (savage@swan.perm.ru)
 * @version      апрель.2012
 */

sw.Promed.swSmpIllegalActEditWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swSmpIllegalActEditWindow',
	objectSrc: '/jscore/Forms/Ambulance/swSmpIllegalActEditWindow.js',
	closable: true,
	closeAction: 'hide',
	cls: 'swSmpIllegalActEditWindow',
	maximizable: false,
	modal: true,
	maximized: false,
	plain: false,
	width: 750,
	autoHeight: true,
	//height: 400,
	layout: 'form',

	initComponent: function(a,b,c) {
		var me = this,
			opts = getGlobalOptions();

		me.addEvents({
			saveform: true
		});

		me.FileUploadPanel = new sw.Promed.FileUploadPanel({
			win: this,
			width: 730,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 50,
			limitCountCombo: 10,
			commentTextfieldWidth: 180,
			commentTextColumnWidth: .4,
			uploadFieldColumnWidth: .5,
			style: 'background: transparent',
			folder: 'pmmedia/',
			fieldsPrefix: 'pmMediaData',
			//border: false,
			frame: true,
			commentLabelWidth: 100,
			//bodyStyle: 'background: transparent',
			//style: 'background: transparent',
			dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
			saveUrl: '/?c=PMMediaData&m=uploadFile',
			saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
			deleteUrl: '/?c=PMMediaData&m=deleteFile'
		});


		me.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			toolbar: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			autoHeight: true,
			defaults: {
				width: 350,
				disabledClass: 'field-disabled'
			},
			items: [
				//скрытые поля
				{
					xtype: 'hidden',
					name: 'CmpIllegalAct_id',
				},
				{
					xtype: 'hidden',
					name: 'Person_id',
				},
				{
					xtype: 'hidden',
					name: 'Person_FirName',
				},
				{
					xtype: 'hidden',
					name: 'Person_SecName',
				},
				{
					xtype: 'hidden',
					name: 'Person_SurName',
				},

				{
					xtype: 'hidden',
					name: 'Address_Zip',
				},
				{
					xtype: 'hidden',
					name: 'KLCountry_id',
				},
				{
					xtype: 'hidden',
					name: 'KLRgn_id',
				},
				{
					xtype: 'hidden',
					name: 'KLSubRGN_id',
				},
				{
					xtype: 'hidden',
					name: 'KLCity_id',
				},
				{
					xtype: 'hidden',
					name: 'KLTown_id',
				},
				{
					xtype: 'hidden',
					name: 'KLStreet_id',
				},
				{
					xtype: 'hidden',
					name: 'Address_House',
				},
				{
					xtype: 'hidden',
					name: 'Address_Corpus',
				},
				{
					xtype: 'hidden',
					name: 'Address_Flat',
				},
				{
					fieldLabel: langs('МО регистрации случая'),
					valueField: 'Lpu_id',
					autoLoad: true,
					editable: true,
					hideTrigger: true,
					hiddenName: 'Lpu_id',
					displayField: 'Lpu_Nick',
					comAction: 'AllAddress',
					xtype: 'swlpuwithmedservicecombo'
				},
				{
					fieldLabel: langs('Дата регистрации случая'),
					format: 'd.m.Y',
					name: 'CmpIllegalAct_prmDT',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield',
					listeners: {
						select: function(){
							me.checkExistsCmpCallCard();
						}
					}
				},
				{
					fieldLabel: langs('Пациент'),
					xtype: 'trigger',
					name:'Person_Fio',
					readOnly: true,
					width: 284,
					cls: 'inputClearTextfieldsButton',
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"}
						]
					},
					onTriggerClick: function (e) {
						me.personSearch();
					}
				},
				new Ext.form.TwinTriggerField (
					{
						name: 'AddressText',
						readOnly: true,
						trigger1Class: 'x-form-search-trigger',
						trigger2Class: 'x-form-clear-trigger',
						fieldLabel: langs('Адрес вызова'),
						isXType: function(){
							//дичь - у компонента нет xtype
							return 'field';
						},
						onTrigger2Click: function () {
							var ownerForm = me.FormPanel.getForm(),
								addressForm = getWnd('swAddressEditWindow');

							ownerForm.findField('Address_Zip').setValue('');
							ownerForm.findField('KLCountry_id').setValue('');
							ownerForm.findField('KLRgn_id').setValue('');
							ownerForm.findField('KLSubRGN_id').setValue('');
							ownerForm.findField('KLCity_id').setValue('');
							ownerForm.findField('KLTown_id').setValue('');
							ownerForm.findField('KLStreet_id').setValue('');
							ownerForm.findField('Address_House').setValue('');
							ownerForm.findField('Address_Flat').setValue('');
							ownerForm.findField('Address_Corpus').setValue('');
							ownerForm.findField('AddressText').setValue('');

							me.checkExistsCmpCallCard();
						},
						onTrigger1Click: function () {
							//var ownerWindow = this.ownerCt.ownerCt;
							var ownerForm = me.FormPanel.getForm(),
								addressForm = getWnd('swAddressEditWindow');

							addressForm.show({
								fields: {
									Address_ZipEdit: ownerForm.findField('Address_Zip').value,
									KLCountry_idEdit: ownerForm.findField('KLCountry_id').value,
									KLRgn_idEdit: ownerForm.findField('KLRgn_id').value,
									KLSubRGN_idEdit: ownerForm.findField('KLSubRGN_id').value,
									KLCity_idEdit: ownerForm.findField('KLCity_id').value,
									KLTown_idEdit: ownerForm.findField('KLTown_id').value,
									KLStreet_idEdit: ownerForm.findField('KLStreet_id').value,
									Address_HouseEdit: ownerForm.findField('Address_House').value,
									Address_CorpusEdit: ownerForm.findField('Address_Corpus').value,
									Address_FlatEdit: ownerForm.findField('Address_Flat').value,
									Address_AddressEdit: ownerForm.findField('AddressText').value
								},
								callback: function (values) {
									ownerForm.findField('Address_Zip').setValue(values.Address_ZipEdit);
									ownerForm.findField('KLCountry_id').setValue(values.KLCountry_idEdit);
									ownerForm.findField('KLRgn_id').setValue(values.KLRgn_idEdit);
									ownerForm.findField('KLSubRGN_id').setValue(values.KLSubRGN_idEdit);
									ownerForm.findField('KLCity_id').setValue(values.KLCity_idEdit);
									ownerForm.findField('KLTown_id').setValue(values.KLTown_idEdit);
									ownerForm.findField('KLStreet_id').setValue(values.KLStreet_idEdit);
									ownerForm.findField('Address_House').setValue(values.Address_HouseEdit);
									ownerForm.findField('Address_Corpus').setValue(values.Address_CorpusEdit);
									ownerForm.findField('Address_Flat').setValue(values.Address_FlatEdit);
									ownerForm.findField('AddressText').setValue(values.Address_AddressEdit);
									ownerForm.findField('AddressText').focus(true, 500);
									me.checkExistsCmpCallCard();
								},
								onClose: function () {
									ownerForm.findField('AddressText').focus(true, 500);
								}
							})
						}
					}
				),
				{
					fieldLabel: langs('Вызов'),
					xtype: 'swbaselocalcombo',
					hiddenName: 'CmpCallCard_id',
					//displayField: 'CmpCallCard_prmDate',
					displayField: 'DisplayCardFormat',
					//codeField: 'CmpCallCard_prmDate',
					valueField: 'CallCard_id',
					forceSelection: true,
					tpl: '<tpl for="."><div class="x-combo-list-item">'+
						'{[Ext.isEmpty(values.CmpCallCard_prmDate)?"":values.CmpCallCard_prmDate]}' +
						'{[Ext.isEmpty(values.CmpCallCard_Numv)?"":", № в/д " + values.CmpCallCard_Numv]}' +
						'{[Ext.isEmpty(values.CmpCallCard_Ngod)?"":", № в/г " + values.CmpCallCard_Ngod]}' +
					'</div></tpl>',
					//displayTpl: '<tpl for="."> {CmpCallCard_prmDate}, № в/д {CmpCallCard_Ngod}, № в/г {CmpCallCard_Numv} </tpl>',
					store: new Ext.data.Store({
						url: '/?c=CmpCallCard&m=loadIllegalActCmpCards',
						key: 'CallCard_id',
						autoLoad: false,
						reader: new Ext.data.JsonReader(
							{
								root: 'data',
								fields: [
									{name:'CallCard_id', type:'int'},
									{name:'CmpCallCard_prmDate', type:'string', convert: function(v){return !Ext.isEmpty(v) ? new Date(v).format('d.m.Y H:i') : null;}},
									{name:'CmpCallCard_Ngod', type:'int'},
									{name:'CmpCallCard_Numv', type:'int'},
									{name:'DisplayCardFormat', type: 'string', convert: function(v,rec){
										var txt =
											(Ext.isEmpty(rec.CmpCallCard_prmDate)?"":new Date(rec.CmpCallCard_prmDate).format('d.m.Y H:i')) +
											(Ext.isEmpty(rec.CmpCallCard_Numv)?"":", № в/д " + rec.CmpCallCard_Numv) +
											(Ext.isEmpty(rec.CmpCallCard_Ngod)?"":", № в/г " + rec.CmpCallCard_Ngod);

										return txt;
									}}
								]
							}),
						sortInfo: {
							field: 'CallCard_id'
						}
					})
				},
				{
					fieldLabel: langs('Комментарий'),
					name: 'CmpIllegalAct_Comment',
					xtype: 'textfield'
				}
			]
		});

		Ext.apply(me, {
			buttons: [
				{
					handler: function() {
						me.doSave();
					},
					iconCls: 'save16',
					refId: 'saveBtn',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(me, -1),
				{
					handler: function() {
						me.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [
				me.FormPanel,
				{
					xtype: 'fieldset',
					autoHeight: true,
					title: langs('Файлы'),
					style: 'padding: 3px; margin-bottom: 2px; display:block;',
					items: [me.FileUploadPanel]
				}
			],
			layout: 'form'
		});

		sw.Promed.swSmpIllegalActEditWindow.superclass.initComponent.apply(me, arguments);
	},

	listeners: {},

	//ОСНОВНЫЕ ФУНКЦИИ ФОРМЫ

	show: function(opts) {
		var me = this,
			base_form = me.FormPanel.getForm(),
			defaultTitle = 'Случай противоправного действия в отношении персонала СМП';

		base_form.reset();
		me.FileUploadPanel.reset();

		base_form.findField('CmpCallCard_id').store.removeAll();

		if ( arguments[0].onSaveForm && !me.hasListener('saveform')) {
			var fn = arguments[0].onSaveForm;

			me.addListener('saveform', function(){
				fn(me);
			});
		}

		if ( arguments[0].action ) {
			me.action = arguments[0].action;
		}

		switch(me.action){
			case 'add' : {
				me.setTitle(defaultTitle+ ': Добавление');
				me.FileUploadPanel.enable();

				//me.FileUploadPanel.doLayout();
				break;
			}
			case 'edit' : {
				me.setTitle(defaultTitle+ ': Редактирование');
				me.FileUploadPanel.enable();
				break;
			}
			case 'view' : {
				me.setTitle(defaultTitle+ ': Просмотр');
				me.FileUploadPanel.disable();
				break;
			}
		};

		me.loadData(me, base_form, opts);

		base_form.isValid();

		sw.Promed.swSmpIllegalActEditWindow.superclass.show.apply(me, arguments);
	},

	loadData: function(me, form, opts){

		var formParams = {};

		me.loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});

		me.loadMask.show();

		//гарантия скрытия маски
		//@todo придумать способ исправить и почистить карму
		setTimeout(function () {
			if (me.loadMask.el.isVisible()) {me.loadMask.hide(); }
		}, 1000);

		me.loadCounter = {
			countLoadingStores: 0,
			countLoadedStores: 0
		};

		if(me.action != 'add'){
			Ext.Ajax.request({
				url: '/?c=CmpCallCard&m=loadCmpIllegalActForm',
				params: {
			 		CmpIllegalAct_id: opts.record.CmpIllegalAct_id
				},
				success: function (response){
					var formParams = Ext.util.JSON.decode(response.responseText),
						values = formParams[0];

					me.FileUploadPanel.listParams = {
						ObjectName: 'CmpIllegalAct',
						ObjectID: opts.record.CmpIllegalAct_id,
						callback: function() {
							if(me.action == 'view')
								me.FileUploadPanel.disable();
						}
					};

					me.FileUploadPanel.loadData();

					me.setValues(me, form, values);
					me.setEnableFields(null, values);
				},
				failure: function (a,b,c) {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function () {});
				}

			});
		}
		else {
			formParams = {
				CmpIllegalAct_prmDT : new Date(),
				Lpu_id : getGlobalOptions().lpu_id
			};

			me.FileUploadPanel.listParams = {
				ObjectName: 'CmpIllegalAct',
				ObjectID: null
			};

			me.setValues(me, form, formParams);
			me.setEnableFields();
		};
	},

	//загрузка полей и установка значений, зависимостей
	setValues: function(me, form, formParams){
		var opts = getGlobalOptions(),
			fields = me.getAllFields(),
			formParams = formParams || {};

		for(var i = 0; i < fields.length; i++){
			var fieldCmp = fields[i],
				fieldName = fieldCmp.getName(),
				fieldVal = fieldCmp.getValue();

			switch(fieldName){
				case 'CmpCallCard_id': {
					if(me.action != 'add'){

						formParams.ComboLoad = 2;
						formParams.CmpCallCard_prmDate = formParams.CmpIllegalAct_prmDT;

						me.setValueAfterStoreLoad(fieldCmp, formParams.CmpCallCard_id, formParams);

						//me.checkExistsCmpCallCard(formParams);
					}
					break;
				}
				case 'Lpu_id': {
					//if(me.action == 'add'){
						me.setValueAfterStoreLoad(fieldCmp, formParams.Lpu_id, formParams);
					//}
					break;
				}
				default :{
					//если в параметрах есть одноименный пункт со значением - значит это значение компонента
					if(formParams && formParams[fieldName])
						fieldCmp.setValue(formParams[fieldName]);

					break;
				}
			};
		};
	},

	//метод получения всех дочерних компонентов с указанного компонента
	getAllFields: function(parentEl){
		var me = this,
			parentEl = parentEl || me.FormPanel.getForm(),
			fieldsTop = parentEl.items.items,
			allFields = [];

		var getAllFields = function(cmps){
			for(var i = 0; i < cmps.length; i++){
				//заметка: собираем только поля может понадобится условие
				if(cmps[i].isXType('field')) {
					allFields.push(cmps[i]);
				}
				if(cmps[i].items && cmps[i].items.items.length){
					getAllFields(cmps[i].items.items)
				};
			}
		};

		getAllFields(fieldsTop);

		return allFields;
	},

	getAllValues: function(parentEl){
		var me = this,
			parentEl = parentEl || null,
			fields = me.getAllFields(parentEl),
			values = {};

		for(var i = 0; i < fields.length; i++) {

			var fieldCmp = fields[i],
				fieldVal = fieldCmp.getValue(),
				fieldName = fieldCmp.getName();

			switch(true) {
				case ( fieldCmp.ownerCt.xtype == "swdatetimefield" ):{
					fieldVal = fieldCmp.getStringValue();
					values[fieldName] = fieldVal;
					break;
				}
				case ( fieldCmp.getXType && fieldCmp.getXType() == "swdatefield" ):{
					values[fieldName] = Ext.util.Format.date(fieldVal, 'Y-m-d');
					//@todo подумать
					//values[fieldName] = Ext.util.Format.date(fieldVal, 'd.m.Y');
					break;
				}
				case (fieldVal instanceof Date):
				{
					//просто дата пришла
					values[fieldName] = Ext.util.Format.date(fieldVal, 'd.m.Y H:i');
					break;
				}
				case ( fieldCmp.getXType && fieldCmp.getXType() == "checkbox" ):{
					values[fieldName] = fieldVal ? 2 : 1;
					break;
				}
				default : {
					values[fieldName] = fieldVal;
				}
			}

		}
		return values;
	},


	doSave: function(){
		var me = this,
			values = me.getAllValues(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."}),
			base_form = me.FormPanel.getForm();

		if(!base_form.isValid()){
			sw.swMsg.alert(langs('Ошибка'), langs('Проверьте обязательные для заполнения поля'));
			return false;
		}

		if(!base_form.findField('AddressText').getValue() && !base_form.findField('Person_Fio').getValue() ){
			sw.swMsg.alert(langs('Ошибка'), langs('Хотя бы одно из полей «Пациент» или «Адрес вызова» должно быть заполнено. Сохранение невозможно.'));
			return false;
		}

		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=saveCmpIllegalActForm',
			params: values,
			failure: function (response, opts) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
			},
			callback: function (opt, success, response) {
				loadMask.hide();
				if (!success) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
				}

				var request = Ext.util.JSON.decode(response.responseText);

				me.FileUploadPanel.listParams = {
					ObjectName: 'CmpIllegalAct',
					ObjectID: request.CmpIllegalAct_id
				};
				me.FileUploadPanel.saveChanges();

				sw.swMsg.alert(langs('Сохранение'), langs('Случай сохранён'), function(){
					me.fireEvent('saveform');
				});

			}
		});

	},


	// функция простановки активности, редактируемости и видимости полей
	// эх, начнем...
	// field - изменяемый компонент
	// params - данные формы
	// переменные:
	// fields - изменяемый компонент или все
	// nextFocusCmp - компонент в который требуется поставить фокус
	// пока расширенная (обкаточная) версия

	/*
	* Пометка "если меняется этот компонент" нужна для случаев когда 2 элемента взаимосвязаны (чтобы не произошло цикличного взаимодействия)
	* или когда при установке значения в компонент оно само не очищалось
	* */
	setEnableFields: function(field, params){

		var me = this,
			region = getRegionNick(),
			base_form = me.FormPanel.getForm(),
			fields = me.getAllFields(),
			nextFocusCmp = null,
			params = params ? params : {};

		//золотые функции (взять на заметку)
		//setFieldValue
		//getFieldValue

		// поля
		for(var i = 0; i < fields.length; i++){
			var fieldCmp = fields[i],
				fieldName = fieldCmp.getName(),
				fieldVal = fieldCmp.getValue(),
				setHidden = false,
				clearFieldValue = false,
				setAllowBlank = true,
				setEnabled = true;

			switch(fieldName){
				case 'Lpu_id':{
					setAllowBlank = false;
					setEnabled = false;
					break;
				}
				case 'CmpIllegalAct_prmDT':{
					setAllowBlank = false;
					break;
				}
				case 'CmpCallCard_id':{
					setAllowBlank = false;
					break;
				}
				case 'CmpIllegalAct_Comment':{
					setAllowBlank = false;
					break;
				}
			};

			if(me.action == 'view'){
				setEnabled = false;
			}

			if(fieldCmp.getXType() != 'hidden')
				setHidden ? fieldCmp.hideContainer() : fieldCmp.showContainer();

			fieldCmp.allowBlank = setAllowBlank;
			fieldCmp.setDisabled( !setEnabled );

			if(clearFieldValue){
				fieldCmp.clearValue ? fieldCmp.clearValue() : fieldCmp.setValue(false);
			}

			fieldCmp.validate();
		};


		//доп. обработка
		//смена фокуса
		if(nextFocusCmp){
			base_form.findField(nextFocusCmp).focus();
		}

	},

	//возвращает массив компонентов по массиву имен
	getComponentsByName: function(fields){
		var me = this,
			base_form = me.FormPanel.getForm(),
			arrayCmps = [];

		for(var i = 0; i < fields.length; i++){

			var cmp = base_form.findField( fields[i] );

			if(cmp){
				arrayCmps.push(cmp);
			};
		};

		return arrayCmps;
	},

	//Вспомогательные функции

	//установка значения в комбик просле загрузки стора
	// cmp - компонент
	// val - значение
	// params - параметры загрузки
	// clb - возвратка
	setValueAfterStoreLoad: function(cmp, val, params, clb){
		var me = this,
			connection = cmp.getStore().proxy.getConnection(),
			transId = connection.transId ? connection.transId.tId : false,
			storeIsLoading = connection.isLoading(transId);

		if(!storeIsLoading){
			me.loadCounter.countLoadingStores++;
		}

		cmp.getStore().load({
			params: params,
			callback: function(o, success){

				me.loadCounter.countLoadedStores++;

				if(me.loadCounter.countLoadingStores == me.loadCounter.countLoadedStores && me.loadMask.el.isVisible()){
					me.loadMask.hide();
				}

				if(o && o.length){
					var record = this.findRecord(this.valueField, val);

					if(val && record){
						this.setValue(val);
						if(clb) clb(cmp, record);
					}
					else{
						this.setValue(null);
						if(clb) clb(cmp);
					}
				}
				else{
					this.getStore().removeAll();
					this.reset();
				}

			}.createDelegate(cmp)
		});
	},

	personSearch: function(){

		var me = this;

		if ( me.action == 'view' ) {
			return false;
		}

		var searchPersonWindow = getWnd('swPersonSearchWindow'),
			base_form = me.FormPanel.getForm(),
			personId = base_form.findField('Person_id'),
			personFirname = base_form.findField('Person_FirName'),
			personSecname = base_form.findField('Person_SecName'),
			personSurname = base_form.findField('Person_SurName'),
			personFio = base_form.findField('Person_Fio');

		searchPersonWindow.show({
			onSelect: function(person_data) {
				searchPersonWindow.hide();
				personFirname.setValue(person_data.Person_Firname);
				personSecname.setValue(person_data.Person_Secname);
				personSurname.setValue(person_data.Person_Surname);
				personFio.setValue(person_data.Person_Surname + ' ' + person_data.Person_Firname + ' ' + person_data.Person_Secname);
				personId.setValue(person_data.Person_id);
				me.checkExistsCmpCallCard();
			},
			forObject: 'CmpCallCard',
			personFirname: personFirname.getValue(),
			personSecname: personSecname.getValue(),
			personSurname: personSurname.getValue(),
			//Person_Age: base_form.findField('Person_Age').getValue(),
			searchMode: 'all'
		});

		if( personFirname.getValue() || personSecname.getValue() || personSurname.getValue() ){
			searchPersonWindow.doSearch();
		}
	},

	checkExistsCmpCallCard: function(formParams){
		var me = this,
			allParams = formParams ? formParams : me.getAllValues(),
			base_form = me.FormPanel.getForm(),
			params = {
				CmpCallCard_id : allParams.CmpCallCard_id,
				CmpCallCard_prmDate : allParams.CmpIllegalAct_prmDT,
				Person_id : allParams.Person_id,
				KLSubRgn_id : allParams.KLSubRgn_id,
				KLCity_id : allParams.KLCity_id,
				KLTown_id : allParams.KLTown_id,
				KLStreet_id : allParams.KLStreet_id,
				CmpCallCard_Dom : allParams.Address_House,
				CmpCallCard_Kvar : allParams.Address_Flat,
				ComboLoad: 2
			};

		base_form.findField('CmpCallCard_id').getStore().load({
			params: params
		});

	}

});
