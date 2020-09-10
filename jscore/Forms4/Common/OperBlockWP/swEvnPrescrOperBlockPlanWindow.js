/**
 * swEvnPrescrOperBlockPlanWindow - Планирование операции
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.OperBlockWP.swEvnPrescrOperBlockPlanWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEvnPrescrOperBlockPlanWindow',
    autoShow: false,
	maximized: false,
	width: 800,
	height: 500,
	refId: 'operblockplanw',
	findWindow: false,
	closable: true,
	cls: 'arm-window-new',
	title: 'Планирование операции',
    header: true,
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	doSave: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();
		var params = {
			EvnDirection_id: win.EvnDirection_id,
			TimetableResource_id: win.TimetableResource_id,
			Resource_id: base_form.findField('Resource_id').getValue(),
			EvnRequestOper_isAnest: (base_form.findField('EvnRequestOper_isAnest').getValue()) ? 2 : 1,
			TimetableResource_begDate: base_form.findField('TimetableResource_begDate').getValue().format('Y-m-d'),
			TimetableResource_begTime: base_form.findField('TimetableResource_begTime').getValue(),
			TimetableResource_Time: base_form.findField('TimetableResource_Time').getValue()
		};
		// надо собрать все бригады
		var BrigData = [];
		win.BrigFieldset.items.items.forEach(function(panel) {
			if (!Ext.isEmpty(panel.child('[name=MedStaffFact_id]').getValue())) {
				BrigData.push({
					SurgType_id: panel.child('[name=SurgType_id]').getValue(),
					MedStaffFact_id: panel.child('[name=MedStaffFact_id]').getValue()
				});
			}
		});
		params.BrigDataJson = Ext6.JSON.encode(BrigData);
		// надо собрать все анестезии
		var AnestData = [];
		win.AnestFieldset.query('[name=AnesthesiaClass_id]').forEach(function(field) {
			if (!Ext.isEmpty(field.getValue())) {
				AnestData.push({
					AnesthesiaClass_id: field.getValue()
				});
			}
		});
		params.AnestDataJson = Ext6.JSON.encode(AnestData);
		Ext.Ajax.request ({
			params: params,
			async: false,
			cache: false,
			url: '/?c=OperBlock&m=getIntersectedResources',
			success: function(response, options) {
				var resp = Ext.util.JSON.decode(response.responseText);
				if ( resp.data.length > 0 ) {
					var sD = Ext6.Date.parse(resp.data[0]["TimetableResource_begTime"].date.split(".")[0], "Y-m-d H:i:s");
					var tT = win.getTimeFromMins(resp.data[0]["TimetableResource_Time"]);
					var message = resp.data[0]["MedPersonal_SurName"] + " " + resp.data[0]["MedPersonal_FirName"] + " " + resp.data[0]["MedPersonal_SecName"]
							+ " уже участвует в операции " +
						Ext6.Date.format(sD, 'd-m-Y H:i')
						+ ". Продолжить сохранение?";
					if ( resp.data[0]["Resource_id"] == params["Resource_id"] ) {
						message = "На данном столе уже запланирована операция с " +
							Ext6.Date.format(sD, 'd-m-Y H:i')
							+ " длительностью " + tT + ". Продолжить сохранение?";
					}
					Ext.MessageBox.show({
						title: "Конфликт!",
						msg: message,
						buttons: Ext.Msg.YESNO,
						icon: Ext.MessageBox.WARNING,
						fn: function (butn) {
							if ( butn == 'yes' ) {
								if (resp.success) {
									win.mask(LOAD_WAIT_SAVE);
									base_form.submit({
										params: params,
										success: function() {
											win.unmask();
											win.callback();
											win.hide();
										},
										failure: function() {
											win.unmask();
										}
									});
								}
							}
						}
					});
				} else {
					win.mask(LOAD_WAIT_SAVE);
					base_form.submit({
						params: params,
						success: function() {
							win.unmask();
							win.callback();
							win.hide();
						},
						failure: function() {
							win.unmask();
						}
					});
				}
			}
		});
/*
		win.mask(LOAD_WAIT_SAVE);
		base_form.submit({
			params: params,
			success: function() {
				win.unmask();
				win.callback();
				win.hide();
			},
			failure: function() {
				win.unmask();
			}
		});
*/
	},
	getTimeFromMins: function(mins) {
		var hours = Math.trunc(mins / 60);
		var minutes = mins % 60;
		return hours + 'ч. ' + minutes + 'м.';
	},
	show: function() {
		this.callParent(arguments);
		var win = this;

		if ( arguments[0] && arguments[0].EvnDirection_id ) {
			win.EvnDirection_id = arguments[0].EvnDirection_id;
		}
		else {
			this.hide();
			sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		this.callback = Ext6.emptyFn;
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		this.startDate = null;
		if ( arguments[0].startDate ) {
			this.startDate = arguments[0].startDate;
		}

		this.Resource_id = null;
		if ( arguments[0].Resource_id ) {
			this.Resource_id = arguments[0].Resource_id;
		}

		this.TimetableResource_id = null;

		this.restore();
		this.center();

		this.BrigFieldset.removeAll();
		this.AnestFieldset.removeAll();

		// прогрузить форму
		var base_form = win.FormPanel.getForm();
		base_form.reset();

		base_form.findField('TimetableResource_begDate').setMinValue(getValidDT(getGlobalOptions().date, '')); // дата начала ограничена текущей датой
		
		base_form.load({
			url: '/?c=OperBlock&m=loadEvnPrescrOperBlockPlanWindow',
			params: {
				EvnDirection_id: win.EvnDirection_id
			},
			success: function(form, action) {
				if (win.startDate && typeof win.startDate == 'object') {
					base_form.findField('TimetableResource_begDate').setValue(win.startDate.format('d.m.Y'));
					base_form.findField('TimetableResource_begTime').setValue(win.startDate.format('H:i'));
				}

				base_form.findField('Resource_id').getStore().proxy.extraParams.MedService_id = base_form.findField('MedService_id').getValue();
				base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.MedService_id = base_form.findField('MedService_id').getValue();

				win.BrigFieldset.removeAll();

				if (action.response && action.response.responseText)
				{
					var data = Ext6.decode(action.response.responseText);
					if (data[0]) {
						if (!Ext6.isEmpty(data[0].TimetableResource_id)) {
							// запланирована
							win.TimetableResource_id = data[0].TimetableResource_id;
						}
						if (win.Resource_id) {
							data[0].Resource_id = win.Resource_id.toString();
						}
						if (!Ext6.isEmpty(data[0].Resource_id)) {
							base_form.findField('Resource_id').getStore().load({
								params: {
									Resource_id: data[0].Resource_id,
									MedService_id: base_form.findField('MedService_id').getValue()
								},
								callback: function() {
									base_form.findField('Resource_id').setValue(data[0].Resource_id);
									base_form.findField('Resource_id').fireEvent('change', base_form.findField('Resource_id'), base_form.findField('Resource_id').getValue());
								}
							});
						}

						if (!Ext6.isEmpty(data[0].UslugaComplex_id)) {
							base_form.findField('UslugaComplex_id').getStore().load({
								params: {
									UslugaComplex_id: data[0].UslugaComplex_id,
									MedService_id: base_form.findField('MedService_id').getValue()
								},
								callback: function() {
									base_form.findField('UslugaComplex_id').setValue(data[0].UslugaComplex_id);
									base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
								}
							});
						}
						if (!Ext6.isEmpty(data[0].EvnRequestOper_isAnest)) {
							win.EvnRequestOper_isAnest = data[0].EvnRequestOper_isAnest;
						}
						if (!Ext6.isEmpty(data[0].AnestData[0])) {
							base_form.findField('EvnRequestOper_isAnest').setValue(true);
							base_form.findField('EvnRequestOper_isAnest').setDisabled(true);
						}
						data[0].AnestData.forEach(function (rec) {
							win.addAnest(rec);
						});

						data[0].BrigData.forEach(function (rec) {
							win.addBrig(rec);
						});
					}
				}

				// добавляем пустые поля
				win.addBrig();
				win.addAnest();
			},
			failure: function() {
			}
		});
	},
	addBrig: function(rec) {
		var win = this;
		var base_form = win.FormPanel.getForm();

		// у всех сущетсвюущих должна появиться кнопка "Удалить"
		win.BrigFieldset.query('#deleteButton').forEach(function(button) {
			button.show();
		});

		var MedStaffFactCombo = Ext6.create('swMedStaffFactCombo', {
			margin: '0 0 0 10px',
			columnWidth: .6,
			hideLabel: true,
			listeners: {
				'select': function() {
					// как только выбрали показываем ещё одно поля с сотрудниками, добавляем только если нет пустых
					var needAdd = true;
					var fields = win.BrigFieldset.query('[name=MedStaffFact_id]');
					fields.forEach(function(field) {
						if (Ext6.isEmpty(field.getValue())) {
							needAdd = false;
						}
					});

					if (needAdd) {
						win.addBrig();
					}
				}
			},
			name: 'MedStaffFact_id'
		});

		var block = new Ext6.Panel({
			layout: 'column',
			margin: 5,
			border: false,
			items: [{
				xtype: 'swSurgTypeCombo',
				columnWidth: .4,
				emptyText: 'Добавить сотрудника',
				hideLabel: true,
				name: 'SurgType_id'
			}, MedStaffFactCombo, {
				width: 100,
				margin: '0 0 0 10px',
				border: false,
				items: [{
					text: 'Удалить',
					itemId: 'deleteButton',
					hidden: true,
					width: 100,
					handler: function() {
						win.BrigFieldset.remove(block);
					},
					iconCls: 'x4-delete16',
					xtype: 'button'
				}]
			}]
		});

		win.BrigFieldset.add(block);
		
		sw4.swMedStaffFactGlobalStore.clearFilter();
		var onDate = base_form.findField('TimetableResource_begDate').getValue();
		if (!Ext.isEmpty(onDate)) {
			sw4.swMedStaffFactGlobalStore.filterBy(function(record) {
				if ( record.get('Lpu_id') != getGlobalOptions().lpu_id ) {
					return false;
				}

				var mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y');
				var mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');
				if ( (!Ext.isEmpty(mp_beg_date) && mp_beg_date > onDate) || (!Ext.isEmpty(mp_end_date) && mp_end_date < onDate) ) {
					return false;
				}
				return true;
			});
		}

		MedStaffFactCombo.getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));

		var components = block.query('combobox');
		win.loadDataLists(components, function() {
			if (rec) {
				block.down('[name=MedStaffFact_id]').setValue(parseInt(rec.MedStaffFact_id));
				block.down('[name=SurgType_id]').setValue(parseInt(rec.SurgType_id));
			}
		});
	},
	addAnest: function(rec) {
		var win = this;

		// у всех сущетсвюущих должна появиться кнопка "Удалить"
		win.AnestFieldset.query('#deleteButton').forEach(function(button) {
			button.show();
		});

		var block = new Ext6.Panel({
			layout: 'column',
			margin: 5,
			border: false,
			defaults: {
				labelAlign: 'right',
				labelWidth: 95
			},
			items: [{
				xtype: 'swAnesthesiaClassCombo',
				columnWidth: 1,
				fieldLabel: 'Вид анестезии',
				listeners: {
					'select': function() {
						// как только выбрали показываем ещё одно поля с анестезией, добавляем только если нет пустых
						var needAdd = true;
						var fields = win.AnestFieldset.query('[name=AnesthesiaClass_id]');
						fields.forEach(function(field) {
							if (Ext6.isEmpty(field.getValue())) {
								needAdd = false;
							}
						});

						if (needAdd) {
							win.addAnest();
						}
						win.isAnestFunc();
					}
				},
				name: 'AnesthesiaClass_id'
			}, {
				width: 100,
				margin: '0 0 0 10px',
				border: false,
				items: [{
					text: 'Удалить',
					itemId: 'deleteButton',
					hidden: true,
					width: 100,
					handler: function() {
						win.AnestFieldset.remove(block);
						win.isAnestFunc();
					},
					iconCls: 'x4-delete16',
					xtype: 'button'
				}]
			}]
		});

		win.AnestFieldset.add(block);

		var components = block.query('combobox');
		win.loadDataLists(components, function() {
			if (rec) {
				block.down('[name=AnesthesiaClass_id]').setValue(parseInt(rec.AnesthesiaClass_id));
			}
		});
	},
	isAnestFunc:function(){
		var win = this;
		var base_form = win.FormPanel.getForm();
		var fields = win.AnestFieldset.query('[name=AnesthesiaClass_id]');
		if (fields[1]) {
			base_form.findField('EvnRequestOper_isAnest').setValue(true);
			base_form.findField('EvnRequestOper_isAnest').setDisabled(true);
		}else{
			base_form.findField('EvnRequestOper_isAnest').setDisabled(false);
		}
	},
    initComponent: function() {
        var win = this;

		win.BrigFieldset = new Ext6.form.FieldSet({
			margin: '10px 0 0 0',
			style: 'padding: 10px;',
			title: 'Операционная бригада',
			items: []
		});

		win.AnestFieldset = new Ext6.form.FieldSet({
			margin: '10px 0 0 0',
			style: 'padding: 10px;',
			title: 'Анестезия',
			items: []
		});

		Ext6.define(win.id + '_FormModel', {
			extend:'Ext6.data.Model',
			fields:[
				{name: 'Person_Fio'},
				{name: 'UslugaComplex_id'},
				{name: 'Resource_id'},
				{name: 'TimetableResource_begDate'},
				{name: 'TimetableResource_begTime'},
				{name: 'TimetableResource_Time'},
				{name: 'MedService_id'}
			]
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 10px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 110
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			region: 'center',
			url: '/?c=OperBlock&m=saveEvnPrescrOperBlockPlanWindow',
			items: [{
				name: 'MedService_id',
				xtype: 'hidden'
			}, {
				xtype: 'textfield',
				anchor: '100%',
				readOnly: true,
				fieldLabel: 'Пациент',
				name: 'Person_Fio'
			}, {
				xtype: 'swUslugaComplexOperBlockCombo',
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Вид операции',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();
						// отфильтровать ресурсы по услуге
						base_form.findField('Resource_id').getStore().proxy.extraParams.UslugaComplex_id = newValue;
						base_form.findField('Resource_id').lastQuery = 'This query sample that is not will never appear';

						// если ещё не запланирована, то длительность берём из услуги
						if (!win.TimetableResource_id) {
							var minutes = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplexMedService_Time');
							if (minutes && minutes > 0) {
								var hours = Math.floor(minutes / 60).toString();
								var minutes = (minutes % 60).toString();
								while(hours.length < 2) { hours = "0" + hours; }
								while(minutes.length < 2) { minutes = "0" + minutes; }
								base_form.findField('TimetableResource_Time').setValue(hours + ":" +minutes);
							}
						}
					}
				},
				name: 'UslugaComplex_id'
			}, {
				xtype: 'swResourceCombo',
				allowBlank: false,
				anchor: '100%',
				fieldLabel: 'Стол',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();
						// отфильтровать услуги по ресурсу
						base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.Resource_id = newValue;
						base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
					}
				},
				name: 'Resource_id'
			}, {
				layout: 'column',
				border: false,
				defaults: {
					labelAlign: 'right',
					labelWidth: 110
				},
				items: [{
					xtype: 'datefield',
					allowBlank: false,
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					format: 'd.m.Y',
					fieldLabel: 'Дата начала',
					listeners: {
						'change': function(field, newValue) {
							win.BrigFieldset.removeAll();
							win.addBrig();
						}
					},
					name: 'TimetableResource_begDate'
				}, {
					xtype: 'textfield',
					allowBlank: false,
					plugins: [ new Ext6.ux.InputTextMask('99:99', true) ],
					width: 200,
					fieldLabel: 'Время начала',
					name: 'TimetableResource_begTime'
				}, {
					xtype: 'textfield',
					plugins: [ new Ext6.ux.InputTextMask('99:99', true) ],
					width: 200,
					fieldLabel: 'Длительность',
					name: 'TimetableResource_Time'
				}]
			},
				win.BrigFieldset,
				{
					xtype: 'fieldcontainer',
					fieldLabel: '',
					defaultType: 'checkboxfield',
					padding: '30px 10px 0px 10px',
					items: [
						{
							boxLabel: 'Необходимость анестезиологических препаратов',
							name: 'isAnest',
							id: 'EvnRequestOper_isAnest'
						}
					]
				},
				win.AnestFieldset]
		});

        Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: [{
				handler:function () {
					win.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, '->', {
				handler:function () {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});

		this.callParent(arguments);
    }
});