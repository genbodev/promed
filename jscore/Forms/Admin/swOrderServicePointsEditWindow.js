/**
 * swOrderServicePointsEditWindow - пункт обслуживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swOrderServicePointsEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: false,
	modal: true,
	height: 370,
	width: 560,
	id: 'swOrderServicePointsEditWindow',
	title: 'Порядок пунктов обслуживания',
	layout: 'border',
	resizable: false,
	// имя основной формы
	formName: 'swOrderServicePointsEditForm',
	// краткое имя формы (для айдишников)
	formPrefix: 'ESEW_',

	getMainForm: function()
	{
		return this[this.formName].getForm();
	},
	doSave: function() {

		var win = this,
			form = win.getMainForm();
		if (!form.isValid()) {

			sw.swMsg.show({

				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT,
				icon: Ext.Msg.WARNING,
				buttons: Ext.Msg.OK,

				fn: function() {
					win[win.formName].getFirstInvalidEl().focus(true);
				}
			});

			return false;
		}

		var params = form.getValues();
		params.AgeGroupDisp_id = form.findField('AgeGroupDisp_id').getValue();
		params.DispClass_id = form.findField('DispClass_id').getValue();
		var servicePoints_fields = {};
		var k = 1;
		for(var i = 1; i <= win.pointNumber; i++) {
			if(params['SurveyType_id_' + i]) {
				servicePoints_fields['SurveyType_id_' + k] = params['SurveyType_id_' + i];
				k++;
			}
			delete params['SurveyType_id_' + i];
		}
		if(k-1 <= 0) {
			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				title: ERR_INVFIELDS_TIT,
				msg: langs('Должно быть заполнено хотя бы одно поле в <br>группе полей "Порядок прохождения осмотра / исследования"'),

			});
			return false;
		}
		params.ServicePointsCount = k - 1;
		params.ServicePoints = Ext.util.JSON.encode(servicePoints_fields);
		params.ElectronicQueueInfo_id = win.ElectronicQueueInfo_id;
		params.action = win.action;

		Ext.Ajax.request({
			url: '/?c=ElectronicService&m=saveOrderServicePoints',
			params: params,
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj.success) {
					win.hide();
					win.callback();
				}
			}
		});	

		return true;
	},
	loadFields: function() 
	{
		var win = this;
		var base_form = win.getMainForm();
		var params = {
			ElectronicQueueInfo_id: win.ElectronicQueueInfo_id,
			AgeGroupDisp_id: win.AgeGroupDisp_id
		}
		win.loadMask.show();
		Ext.Ajax.request({	
			url: '/?c=ElectronicService&m=loadElectronicServiceOrder',
			params: params,
			success: function(response) {
				var fields = Ext.util.JSON.decode(response.responseText).data;
				var fieldsCount = fields.length;
				var fieldsContainer = Ext.getCmp('OrderServicePoints_fieldset');
				var AgeGroupDisp_field = base_form.findField('AgeGroupDisp_id');

				if(fields[0].DispClass_id) {
					base_form.findField('DispClass_id').setValue(fields[0].DispClass_id);
				}
				AgeGroupDisp_field.setValue(AgeGroupDisp_field.getValue());
				AgeGroupDisp_field.fireEvent('change', AgeGroupDisp_field, fields[0].AgeGroupDisp_id);
				for(var i = 0; i < fieldsCount; i++) {
					fieldsContainer.addServicePointField(fields[i].SurveyType_id);
				}
			},
			failure: function(response) {
				win.loadMask.hide();
			}
		});
	},
	resetForm: function() {
		var win = this;
		var base_form = win.getMainForm();
		var OrderServicePoints_fieldset = Ext.getCmp('OrderServicePoints_fieldset');
		win.pointNumber = 0;

		Ext.getCmp('OrderServicePoints_container').removeAll();
		OrderServicePoints_fieldset.doLayout();
		
		base_form.findField('AgeGroupDisp_id').clearValue();
		base_form.findField('DispClass_id').clearValue();
	},
	initComponent: function()
	{
		var win = this,
			formName = win.formName,
			formPrefix = win.formPrefix;

		win.SurveyType_store = new Ext.data.Store({
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				id: SurveyType_id
				}, [
					{name: 'SurveyType_id', mapping: 'SurveyType_id'},
					{name: 'SurveyType_code', mapping: 'SurveyType_code'},
					{name: 'SurveyType_name', mapping: 'SurveyType_name'}
				]
			),
			filterFields: function() {
				var fields = win.getMainForm().getValues();
					
				this.clearFilter();
				this.filterBy(function(rec) {
					for(var i = 1; i <= win.pointNumber; i++) {
						if(fields['SurveyType_id_' + i] && rec.get('SurveyType_id') == fields['SurveyType_id_' + i]) {
							return false;
						}
					}
					return true;
				});

			},
			listeners: {
				'load': function() {// Очищаем поля которые не соответствуют фильтру по возрастной группе
					var store = this;
					var pointsContainer = Ext.getCmp('OrderServicePoints_container');
					
					pointsContainer.items.each(function(item) {
						var value = item.field.getValue();
						item.field.lastQuery = '';
						if(store.findBy(function(rec) {
							return rec.get('SurveyType_id') == value;
						}) != -1) {
							item.field.setValue(value);
						} else {
							item.field.clearValue();
						}
					});
					this.filterFields();
					win.loadMask.hide();
				}
			},
			url: '/?c=ElectronicService&m=loadSurveyTypeList'
		});

		win[formName] = new Ext.form.FormPanel({
			id: formName,
			region: 'center',
			labelAlign: 'right',
			layout: 'form',
			height: 370,
			scroll: true,
			labelWidth: 190,
			frame: true,
			border: false,
			items: [{
				allowBlank: false,
				width: 300,
				listWidth: 400,
				fieldLabel: langs('Тип диспансеризации/осмотра'),
				hiddenName: 'DispClass_id',
				comboSubject: 'DispClass',
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.getMainForm();
						var params = {
							DispClass_id: newValue
						};
						
						if(newValue) {
							base_form.findField('AgeGroupDisp_id').clearValue();
							base_form.findField('AgeGroupDisp_id').getStore().removeAll();
							base_form.findField('AgeGroupDisp_id').getStore().load({params: params});
						}
						// win.SurveyType_store.removeAll();
						// win.SurveyType_store.load({params: params});
					}
				},
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: langs('Возрастная группа'),
				hiddenName: 'AgeGroupDisp_id',
				displayField: 'AgeGroupDisp_Name',
				valueField: 'AgeGroupDisp_id',
				store: new Ext.data.JsonStore({
					url: '/?c=ElectronicService&m=loadAgeGroupDispList',
					autoLoad: true,
					editable: false,
					key: 'AgeGroupDisp_id',
					fields: [
						{name: 'AgeGroupDisp_id', type: 'int'},
						{name: 'AgeGroupDisp_Name', type: 'string'}
					]
				}),
				listeners: {
					'change': function(combo, newValue) {
						var base_form = win.getMainForm();
						var params = {
							AgeGroupDisp_id: newValue,
							ElectronicQueueInfo_id: win.ElectronicQueueInfo_id
						};

						if(base_form.findField('DispClass_id').getValue()) {
							params.DispClass_id = base_form.findField('DispClass_id').getValue();
						}
						win.SurveyType_store.removeAll();
						win.SurveyType_store.load({	params: params});
					}
				},
				xtype: 'swbaselocalcombo'	
			}, {
				xtype: 'fieldset',
				title: 'Порядок прохождения осмотра / исследования:',
				id: 'OrderServicePoints_fieldset',
				height: 240,
				style: 'padding-left: 50px',
				autoScroll: true,
				border: false,
				addServicePointField: function(value) {
					var base_form = win.getMainForm();
					if(!win.pointNumber) {
						win.pointNumber = 0;
					}
					win.pointNumber ++;
					var pointsContainer = Ext.getCmp('OrderServicePoints_container');

					var newFieldContainer = new Ext.Panel({
						width: 320,
						layout: 'form'
					});

					var newField = new sw.Promed.SwBaseLocalCombo( 
						{	
							mode: 'local',
							fieldLabel: langs(win.pointNumber + '-й'),
							hiddenName: 'SurveyType_id_' + win.pointNumber,
							valueField: 'SurveyType_id',
							displayField: 'SurveyType_name',
							codeField: 'SurveyType_code',
							codeType: 'string',
							orderBy: 'SurveyType_id',
							mode: 'local',
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{SurveyType_code}</font>&nbsp;{SurveyType_name}'+
								'</div></tpl>'
							),
							orderNum: win.pointNumber,
							fieldContainer: newFieldContainer,

							listeners: {
								'change': function(combo, newValue) {
									combo.lastQuery = '';
									win.SurveyType_store.filterFields();
								}
							},
							onTrigger2Click: function () {//Кнопка "Очистить"
								if(this.disabled) {return false;}
								
								if(this.orderNum == win.pointNumber) {
									pointsContainer.remove(this.fieldContainer, true);
									win.pointNumber --;
								} else {
									this.clearValue();
								}
								this.lastQuery = '';
								win.SurveyType_store.filterFields();
							},
							xtype: 'swbaselocalcombo',
							store: win.SurveyType_store,
							initComponent: Ext.form.TwinTriggerField.prototype.initComponent,
							getTrigger: Ext.form.TwinTriggerField.prototype.getTrigger,
							initTrigger: Ext.form.TwinTriggerField.prototype.initTrigger,
							trigger2Class: 'x-form-clear-trigger',
							onTrigger1Click: Ext.form.ComboBox.prototype.onTriggerClick
						}
					);
					newFieldContainer.field = newField;
					
					newFieldContainer.add(newField);

					pointsContainer.add(newFieldContainer);
					pointsContainer.doLayout();
					if(value) {
						newField.setValue(value)
					}

					newField.lastQuery = '';
					//win.SurveyType_store.filterFields();

					return newField;
				},
				items:[{
					autoHeight: true,
					width: 410,
					id: 'OrderServicePoints_container',
				}, {
					xtype: 'label',
					html: '<a href="#" onclick="Ext.getCmp(\'OrderServicePoints_fieldset\').addServicePointField();return false;">Добавить</a>',
					style: {
						'margin-left': '150px'
					},
					region: 'center'
				}]
			}],
			url: '/?c=ElectronicService&m=saveOrderServicePoints'
			
		});

		Ext.apply(this, {
			buttons:
				[{
					handler: function()
					{
						win.doSave();
					},
					iconCls: 'save16',
					id: win.id + '_saveButton',
					text: BTN_FRMSAVE
				},
					{
						text: '-'
					},
					HelpButton(this, 0),
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items: [
				this[this.formName]
			]
		});


		sw.Promed.swOrderServicePointsEditWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'show': function() {
			this.resetForm();
		}
	},
	show: function() {
		sw.Promed.swOrderServicePointsEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			form = win.getMainForm();
		this.loadMask = new Ext.LoadMask(
			win.getEl(),{
				msg: LOAD_WAIT
			}
		);

		if (!arguments[0]){

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				title: langs('Ошибка'),
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),

				fn: function() { win.hide(); }
			});
		}

		if(arguments[0].ElectronicQueueInfo_id) {
			this.ElectronicQueueInfo_id = arguments[0].ElectronicQueueInfo_id;
		}

		if(arguments[0].AgeGroupDisp_id) {
			this.AgeGroupDisp_id = arguments[0].AgeGroupDisp_id;
		}

		win.focus();
		//form.reset();

		if(arguments[0].action) {
			this.action = arguments[0].action;
		}
		if(arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		this.setTitle("Порядок прохождения осмотров/исследований");

		log('args',arguments[0]);

		switch (this.action){
			case 'add':

				form.findField('AgeGroupDisp_id').setDisabled(false);
				form.findField('DispClass_id').setDisabled(false);

				win.SurveyType_store.load({params: { ElectronicQueueInfo_id: this.ElectronicQueueInfo_id }});

				Ext.getCmp('OrderServicePoints_fieldset').addServicePointField();
				this.setTitle(this.title + ": Добавление");
				win.loadMask.hide();
				win[win.formName].setDisabled(false);

				break;

			case 'edit':
			case 'view':
				win.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				win[win.formName].setDisabled(this.action == "view");

				win.loadFields();

				form.findField('AgeGroupDisp_id').setValue(win.AgeGroupDisp_id);
				form.findField('AgeGroupDisp_id').setDisabled(true);
				form.findField('DispClass_id').setDisabled(true);
				Ext.getCmp(win.id + '_saveButton').setDisabled(this.action == "view");
				break;
		}
	}
});