/**
* swWorkPlaceCovidPeriodEditWindow - Периоды работы с пациентами COVID
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
*/

/*NO PARSE JSON*/
sw.Promed.swWorkPlaceCovidPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	maximizable: false,
	width: 600,
	height: 400,
	modal: true,
	title: 'Периоды работы с пациентами COVID',
	
	show: function() {		
		sw.Promed.swWorkPlaceCovidPeriodEditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0]['MedStaffFact_id']) {
			Ext.Msg.alert('Ошибка', 'Не передан идентификатор сотрудника');
			this.hide();
		}

		var win = this;
		var base_form = this.findById('WorkPlaceCovidPeriodEditForm').getForm();
		base_form.reset();
		this.CovidPeriods.reset();
		
		win.findById(win.id + 'infoPanel').getEl().dom.innerHTML = '';

		this.MedStaffFact_id = arguments[0]['MedStaffFact_id'];
		this.returnFunc = arguments[0]['callback'] || Ext.emptyFn;

		var loadMask = new Ext.LoadMask(Ext.get('WorkPlaceCovidPeriodEditForm'), { msg: LOAD_WAIT });
		loadMask.show();
		base_form.load({
			url: '/?c=WorkPlaceCovidPeriod&m=load',
			params: {
				MedStaffFact_id: this.MedStaffFact_id
			},
			success: function (form, action) {
				loadMask.hide();
				var resp_obj = Ext.util.JSON.decode(action.response.responseText)[0];
				if (resp_obj.WorkPlaceCovidPeriodData.length) {
					Ext.each(resp_obj.WorkPlaceCovidPeriodData, function(el) {
						win.CovidPeriods.addCombo(el);
					});
				} else {
					win.CovidPeriods.addCombo();
				}
			},
			failure: function (form, action) {
				loadMask.hide();
			}
		});
	},
	
	doSave: function() {
		var win = this;
		var loadMask = new Ext.LoadMask(Ext.get('WorkPlaceCovidPeriodEditForm'), { msg: LOAD_WAIT_SAVE });
		var base_form = win.findById('WorkPlaceCovidPeriodEditForm').getForm();
		var params = {
			WorkPlaceCovidPeriodData: Ext.util.JSON.encode(this.CovidPeriods.getValues())
		};
		
		if (!this.CovidPeriods.checkValues()) {
			return false;
		}

		loadMask.show();		
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result && action.result.success) {
					win.hide();
					win.returnFunc();
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.CovidPeriods = new Ext.form.FieldSet({
			title: 'Периоды работы с пациентами COVID-19',
			height: 220,
			style: 'margin-top: 15px;',
			autoScroll: true,
			items: [{
				border: false,
				id: 'WorkPlaceCovidPeriodFieldSet'
			}, {
				style: 'margin: 5px 10px;',
				text: 'Добавить',
				iconCls: 'add16',
				xtype: 'button',
				handler: function() {
					win.CovidPeriods.addCombo();
				}
			}],
			checkValues: function() {
				var data = {};
				var isValidDates = true;
				var isValid = true;
				Ext.each(this.findById('WorkPlaceCovidPeriodFieldSet').items.items, function(el) {
					el.items.items[0].items.items[0].el.dom.style.border = null;
					var begDate = el.items.items[0].items.items[0].getValue1();
					var endDate = el.items.items[0].items.items[0].getValue2();
					if (!begDate && !!endDate) {
						el.items.items[0].items.items[0].el.dom.style.border = '1px solid red';
						isValid = false;
					}
					if (!el.items.items[0].items.items[0].isValid() && begDate > endDate) {
						isValidDates = false;
					}
					if (!begDate || el.RecordStatus_Code == 3) return;
					data[el.id] = {
						begDate: begDate,
						endDate: endDate || new Date('2030-12-31')
					};
				});
				
				if (!isValidDates) {
					win.findById(win.id + 'infoPanel').getEl().dom.innerHTML = 'Дата окончания периода не может быть больше даты начала периода';
					return false;
				}
				
				if (!isValid) {
					win.findById(win.id + 'infoPanel').getEl().dom.innerHTML = 'Дата начала периода работы должна быть заполнена.';
					return false;
				}
				
				for (var el in data) {
					for (var ej in data) {
						if (
							el != ej && 
							data[el].begDate <= data[ej].endDate && 
							data[el].endDate >= data[ej].begDate
						) {
							isValid = false;
							this.findById(el).items.items[0].items.items[0].el.dom.style.border = '1px solid red';
							this.findById(ej).items.items[0].items.items[0].el.dom.style.border = '1px solid red';
						}
					}
				}
				
				win.findById(win.id + 'infoPanel').getEl().dom.innerHTML = !isValid ? 'Периоды работы не могут пересекаться' : '';
				
				return isValid;
			},
			getValues: function() {
				var data = [];
				Ext.each(this.findById('WorkPlaceCovidPeriodFieldSet').items.items, function(el) {
					
					var begDate = el.items.items[0].items.items[0].getValue1();
					var endDate = el.items.items[0].items.items[0].getValue2();
					
					if (!begDate) { // пустое поле считаем удалением
						el.RecordStatus_Code = 3;
					}
					
					if (el.RecordStatus_Code == 1) return; // не изменилось
					if (el.RecordStatus_Code == 3 && !el.oId) return; // добавлено и удалено без сохранения
					
					var a = {
						WorkPlaceCovidPeriod_id: el.oId,
						RecordStatus_Code: el.RecordStatus_Code,
						WorkPlaceCovidPeriod_begDate: begDate ? begDate.format('Y-m-d') : null,
						WorkPlaceCovidPeriod_endDate: endDate ? endDate.format('Y-m-d') : null
					};
					data.push(a);
				});
				return data;
			},
			reset: function() {
				this.lastItemsIndex = 0;
				this.findById('WorkPlaceCovidPeriodFieldSet').removeAll();
				this.findById('WorkPlaceCovidPeriodFieldSet').doLayout();
			},
			deleteCombo: function(index) {
				var el = this.findById(this.id + 'WorkPlaceCovidPeriodEl' + index);
				el.RecordStatus_Code = 3;
				el.items.items[0].items.items[0].disable();
				el.items.items[0].el.dom.style.opacity = 0.5;
				el.items.items[1].hide();
				el.items.items[2].show();
				this.checkValues();
			},
			undoDelete: function(index) {
				var el = this.findById(this.id + 'WorkPlaceCovidPeriodEl' + index);
				el.RecordStatus_Code = 2;
				el.items.items[0].items.items[0].enable();
				el.items.items[0].el.dom.style.opacity = 1;
				el.items.items[1].show();
				el.items.items[2].hide();
				this.checkValues();
			},
			addCombo: function(data) {
				if (!data) data = {};
				this.lastItemsIndex++;
				var element = {
					id: this.id + 'WorkPlaceCovidPeriodEl' + this.lastItemsIndex,
					oId: data.WorkPlaceCovidPeriod_id || null,
					RecordStatus_Code: data.RecordStatus_Code || 0,
					layout: 'column',
					style: 'padding: 5px 10px;',
					border: false,
					defaults:{
						xtype: 'panel',
						layout: 'form',
						border: false
					},
					items: [{
						width: 190,
						items: [{
							hideLabel: true,
							width: 180,
							emptyText: 'Период работы',
							xtype: 'daterangefield',
							iDx: this.lastItemsIndex,
							value: data.WorkPlaceCovidPeriod_DateRange || '',
							enableKeyEvents: true,
							listeners: {
								'keyup': function () {
									var el = win.findById(win.CovidPeriods.id + 'WorkPlaceCovidPeriodEl' + this.iDx);
									if (el.RecordStatus_Code != 3) {
										el.RecordStatus_Code = 2;
									}
									setTimeout(function() {
										win.CovidPeriods.checkValues();
									}, 500);
								},
								'select': function () {
									win.CovidPeriods.checkValues();
									var el = win.findById(win.CovidPeriods.id + 'WorkPlaceCovidPeriodEl' + this.iDx);
									if (el.RecordStatus_Code != 3) {
										el.RecordStatus_Code = 2;
									}
								},
								'blur': function () {
									setTimeout(function() {
										win.CovidPeriods.checkValues();
									}, 500);
								},
							}
						}]
					}, {
						width: 35,
						items: [{
							tooltip: 'Удалить',
							iconCls: 'delete16',
							iDx: this.lastItemsIndex,
							xtype: 'button',
							handler: function() {
								win.CovidPeriods.deleteCombo(this.iDx);
							}
						}]
					}, {
						width: 35,
						hidden: true,
						items: [{
							tooltip: 'Отменить удаление',
							iconCls: 'undo16',
							iDx: this.lastItemsIndex,
							xtype: 'button',
							handler: function() {
								win.CovidPeriods.undoDelete(this.iDx);
							}
						}]
					}]
				};
				this.findById('WorkPlaceCovidPeriodFieldSet').add(element);
				this.findById('WorkPlaceCovidPeriodFieldSet').doLayout();
			}
		});
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'WorkPlaceCovidPeriodEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 180,
			items: [{
				name: 'MedStaffFact_id',
				xtype: 'hidden'
			}, {
				fieldLabel: 'Сотрудник',
				name: 'Person_Fio',
				xtype: 'textfield',
				anchor: '100%',
				disabled: true
			}, {
				fieldLabel: 'Строка штатного расписания',
				name: 'PostMed_Name',
				xtype: 'textfield',
				anchor: '100%',
				disabled: true
			},
			this.CovidPeriods, 
			{
				id: this.id + 'infoPanel',
				style: 'text-align: center; font-weight: bold; color: #C00;',
				html: ''
			}],
			reader: new Ext.data.JsonReader({},	[
				{ name: 'MedStaffFact_id' },
				{ name: 'Person_Fio' },
				{ name: 'PostMed_Name' }
			]),
			url: '/?c=WorkPlaceCovidPeriod&m=save'
		});
		
		Ext.apply(this, {
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons: [{
				text: '<u>С</u>охранить',
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			}, {
				text:'-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function() {
					this.hide();
				}.createDelegate(this)
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
		sw.Promed.swWorkPlaceCovidPeriodEditWindow.superclass.initComponent.apply(this, arguments);
	}
});