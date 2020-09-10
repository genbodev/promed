/**
* swSmpWorkPlanEditWindow - СМП: окно просмотра/редактирования/добавления планов выхода на смену автомобилей и бригад
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @author       Магафуров Салават
* @version      12.2017
*
*/

sw.Promed.swSmpWorkPlanEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSmpWorkPlanEditWindow',
	title: langs('План выхода на смену'),
	width: 1000,
	height: 475,
	//autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	resizable: false,
	callback: Ext.emptyFn,
	listeners: {
		'hide': function() {
			this.callback();
			this.MainPanel.getForm().reset();
			this.PlanGrid.getStore().removeAll();
		}
	},

	setEditable: function(enable) {
		this.enableEdit(enable);
		this.PlanGrid.getColumnModel().setEditable(6,enable);
	},

	loadStories: function(params) {
		var wnd = this;

		var formValues = {
			'CmpWorkPlan_id': params.CmpWorkPlan_id,
			'LpuBuilding_id': params.LpuBuilding_id, 
			'CmpWorkPlan_BegDT': new Date(params.CmpWorkPlan_BegDT),
			'CmpWorkPlan_EndDT': new Date(params.CmpWorkPlan_EndDT),
			'Lpu_id': params.Lpu_id
		}

		if(params.action == 'add') {
			var data = [
				new Ext.data.Record({ 'CmpPlanType_id' : '1', 'PlanType_Name' : 'план автомобилей', 'CmpWorkTime_id' : '1', 'WorkTime_Name' : 'будни день' }),
				new Ext.data.Record({ 'CmpPlanType_id' : '1', 'PlanType_Name' : 'план автомобилей', 'CmpWorkTime_id' : '2', 'WorkTime_Name' : 'будни ночь' }),
				new Ext.data.Record({ 'CmpPlanType_id' : '1', 'PlanType_Name' : 'план автомобилей', 'CmpWorkTime_id' : '3', 'WorkTime_Name' : 'воскресенье день' }),
				new Ext.data.Record({ 'CmpPlanType_id' : '1', 'PlanType_Name' : 'план автомобилей', 'CmpWorkTime_id' : '4', 'WorkTime_Name' : 'воскресенье ночь' }),
				new Ext.data.Record({ 'CmpPlanType_id' : '2', 'PlanType_Name' : 'план бригад', 'CmpWorkTime_id' : '1', 'WorkTime_Name' : 'будни день' }),
				new Ext.data.Record({ 'CmpPlanType_id' : '2', 'PlanType_Name' : 'план бригад', 'CmpWorkTime_id' : '2', 'WorkTime_Name' : 'будни ночь' }),
				new Ext.data.Record({ 'CmpPlanType_id' : '2', 'PlanType_Name' : 'план бригад', 'CmpWorkTime_id' : '3', 'WorkTime_Name' : 'воскресенье день' }),
				new Ext.data.Record({ 'CmpPlanType_id' : '2', 'PlanType_Name' : 'план бригад', 'CmpWorkTime_id' : '4', 'WorkTime_Name' : 'воскресенье ночь' })
			];
			wnd.PlanGrid.getStore().add(data);

		} else {
			var baseForm = wnd.MainPanel.getForm();
			baseForm.setValues(formValues);
			wnd.PlanGrid.getStore().load({params: { 'CmpWorkPlan_id': params.CmpWorkPlan_id}});
	
		}
	},

	initForm: function() {
		if(this.action == 'view') {
			this.setEditable(false);
			this.buttons[0].hide();
		} else {
			var baseForm = this.MainPanel.getForm();
			this.setEditable(true);
			this.buttons[0].show();
			if(this.action == 'add')
				baseForm.findField('LpuBuilding_id').setDisabled(false);
			else
				baseForm.findField('LpuBuilding_id').setDisabled(true);
		}
	},

	show: function() {
		sw.Promed.swSmpWorkPlanEditWindow.superclass.show.apply(this, arguments);
		var params = arguments[0];
		this.action = params.action;
		this.initForm();
		this.loadStories(params);
		if (arguments[0].callback)
			this.callback = arguments[0].callback;
		else
			this.callback = Ext.emptyFn;

		var baseForm = this.MainPanel.getForm();
		var LpuBuilding = baseForm.findField('LpuBuilding_id');
		var lpu_id = getGlobalOptions().lpu_id;
		var Lpu = baseForm.findField('Lpu_id');
		if(this.action == 'add') {
			Lpu.setDisabled( lpu_id != 150011 );
			Lpu.setValue( lpu_id );
			Lpu.setRawValue( getGlobalOptions().lpu_nick );
			Lpu.fireEvent('change', Lpu, lpu_id);
		} else {
			Lpu.disable();
			LpuBuilding.getStore().baseParams.Lpu_id = params.Lpu_id;
			LpuBuilding.getStore().load();
		}
	},

	savePlan: function() {
		var wnd = this;
		var baseForm = wnd.MainPanel.getForm();
		if(!baseForm.isValid() || !wnd.PlanGrid.isValid()) {
			sw.swMsg.alert('Ошибка', 'Заполните все поля.');
			return;
		}

		wnd.getLoadMask('Сохранение').show();
		var params = new Object();

		params.CmpWorkPlan_id = baseForm.findField('CmpWorkPlan_id').getValue(),
		params.LpuBuilding_id = baseForm.findField('LpuBuilding_id').getValue(),
		params.CmpWorkPlan_BegDT = baseForm.findField('CmpWorkPlan_BegDT').getValue().format('Y-m-d'),
		params.CmpWorkPlan_EndDT = baseForm.findField('CmpWorkPlan_EndDT').getValue().format('Y-m-d'),
		params.Data = [];


		wnd.PlanGrid.getStore().each(function(rec){
			var data = new Object();
			data.CmpPlanType_id = rec.get('CmpPlanType_id');
			data.CmpWorkTime_id = rec.get('CmpWorkTime_id');
			data.BrigadeCount = rec.get('BrigadeCount');
			data.CmpWorkPlanData_id = rec.get('CmpWorkPlanData_id');
			params.Data.push(data);
		});

		Ext.Ajax.request({
			url: wnd.action == 'edit' ? '/?c=CmpWorkPlan&m=updWorkPlan' : '/?c=CmpWorkPlan&m=addWorkPlan',
			params: { Data: Ext.util.JSON.encode(params) },
			callback: function(options, success, response) {
				wnd.getLoadMask().hide();
				var result = Ext.util.JSON.decode(response.responseText)[0];
				if(success) {
					if(result) {
						if(result.Error_Message) 
							sw.swMsg.alert(langs('Ошибка'), result.Error_Message);
						else
							wnd.hide();
					}
				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении'));
				}
			}
		});

	},
	initComponent: function() {

		var wnd = this;

		wnd.PlanGrid = new Ext.grid.EditorGridPanel({
			border: false,
			region: 'center',
			store: new Ext.data.JsonStore({
				url: '/?c=CmpWorkPlan&m=getWorkPlan',
				autoLoad: false,
				key: 'WorkPlan_id',
				fields: [
					{name: 'CmpWorkPlanData_id', type: 'int',hidden: true},
					{name: 'LpuBuilding_id', type: 'int'},
					{name: 'CmpPlanType_id', type: 'int'},
					{name: 'PlanType_Name', type: 'string'},
					{name: 'CmpWorkTime_id', type: 'int'},
					{name: 'WorkTime_Name', type: 'string'},
					{name: 'BrigadeCount', type: 'int'}
				],
				listeners: {
					'beforeload': function() {
						wnd.getLoadMask('Загрузка').show();
					},
					'load': function() {
						wnd.getLoadMask().hide();
					}
				}
			}),
			cm: new Ext.grid.ColumnModel([
				{ dataIndex: 'LpuBuilding_id', type: 'int', hidden: true},
				{ dataIndex: 'WorkPlan_id', type:'int', hidden:true },
				{ dataIndex: 'CmpPlanType_id', type:'int', hidden:true },
				{ dataIndex: 'CmpWorkTime_id', type:'int', hidden:true },
				{
					header: "Вид плана",
					dataIndex: 'PlanType_Name',
					sortable: true,
					menuDisabled: true,
					width: 150
				},
				{
					header: "Время суток",
					dataIndex: 'WorkTime_Name',
					sortable: true,
					menuDisabled: true,
					width: 150
				},
				{
					id: 'BrigadeCount',
					header: "Количество автомобилей/бригад",
					dataIndex: 'BrigadeCount',
					sortable: true,
					menuDisabled: true,
					allowBlank: false,
					width: 200,
					editor: new Ext.form.NumberField({
						name: 'BrigadeCount',
						minValue: 0,
						maxValue: 1000,
						fieldLabel: 'Количество автомобилей/бригад',
						allowBlank:false,
						width: 100
					}),
					renderer: function(value, meta) {
						if(Ext.isEmpty(value)) {
							value = "";
							meta.css += 'x-grid-cell-invalid';
						}
						return '<div style="text-align: right; width:100%;">'+value+'</>';
					}
				}
			]),
			stripeRows: true,
			clicksToEdit:1,
			height: 194,
			isValid: function(){
				var valid = true;
				this.getStore().each(function(record){
					if(Ext.isEmpty(record.get('BrigadeCount')))
						valid = false;
				})
				return valid;
			}
		});

		wnd.MainPanel = new Ext.form.FormPanel({
			layout: 'border',
			items: [
				new Ext.Panel({
					title: 'План',
					region: 'center',
					margins: '3 3 3 3',
					items: [
						new Ext.form.FieldSet({
							border: false,
							labelWidth: 200,
							height: 125,
							items: [{
								xtype: 'hidden',
								name: 'CmpWorkPlan_id'
							}, {
								hiddenName: 'Lpu_id',
								width: 200,
								listWidth: 200,
								fieldLabel: 'МО',
								xtype: 'swbaselocalcombo',
								valueField: 'Lpu_id',
								displayField: 'Lpu_Nick',
								tpl: new Ext.XTemplate(
									'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: left;">',
									'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
									'<td style="padding: 2px;">{Lpu_Nick}&nbsp;</td>',
									'</tr></tpl>',
									'</table>'
								),
								store: new Ext.data.JsonStore({
									autoLoad: true,
									url: '/?c=CmpWorkPlan&m=getLpuList',
									key: 'Lpu_id',
									fields: [
										{ name: 'Lpu_id', type: 'int' },
										{ name: 'Lpu_Nick', type: 'string' }
									],
									listeners: {
										load: function(store) {
											var baseForm = wnd.MainPanel.getForm();
											var Lpu = baseForm.findField('Lpu_id');
											Lpu.setValue( Lpu.getValue() );
										}
									}
								}),
								listeners: {
									change: function(combo, newValue, oldValue) {
										var baseForm = wnd.MainPanel.getForm();
										var LpuBuilding = baseForm.findField('LpuBuilding_id');
										LpuBuilding.getStore().baseParams.Lpu_id = newValue;
										LpuBuilding.getStore().load();
									},
									select: function(combo,rec,idx) {
										var baseForm = wnd.MainPanel.getForm();
										var LpuBuilding = baseForm.findField('LpuBuilding_id');
										LpuBuilding.setValue(null);
									}
								}
							}, {
								name: 'LpuBuilding_id',
								hiddenName: 'LpuBuilding_id',
								xtype: 'combo',
								width: 380,
								listWidth: 380,
								triggerAction: 'all',
								mode: 'local',
								fieldLabel: 'Подстанция',
								displayField: 'name',
								valueField: 'id',
								allowBlank: false,
								disabled: true,
								editable: false,
								tpl: new Ext.XTemplate(
									'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: left;">',
									'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
									'<td style="padding: 2px;">{name}&nbsp;</td>',
									'</tr></tpl>',
									'</table>'
								),
								store: new Ext.data.JsonStore({
									autoLoad: false,
									url: '/?c=CmpWorkPlan&m=getSubstationList',
									key: 'id',
									fields: [
										{ name: 'id', type: 'int' },
										{ name: 'name', type: 'string' }
									],
									listeners: {
										'load': function() {
											var baseForm = wnd.MainPanel.getForm();
											var substation_combo = baseForm.findField('LpuBuilding_id');
											substation_combo.setValue(substation_combo.getValue());
										}
									}
								})
							}, {
								name: 'CmpWorkPlan_BegDT',
								xtype: 'swdatefield',
								fieldLabel: 'Дата начала действия плана',
								allowBlank: false,
								invalidText: 'На эту дату уже существует план',
								listeners: {
									'blur': function() {
										var baseForm = wnd.MainPanel.getForm();
										baseForm.findField('CmpWorkPlan_EndDT').validate();
									}
								}
							},{
								name: 'CmpWorkPlan_EndDT',
								fieldLabel: 'Дата окончания действия плана',
								allowBlank: false,
								invalidText: 'Дата начала действия плана должна быть раньше даты окончания',
								xtype: 'swdatefield',
								validator: function(){
									var baseForm = wnd.MainPanel.getForm();
									var begDateValue = baseForm.findField('CmpWorkPlan_BegDT').getValue();
									var endDateValue = this.getValue();
									if(begDateValue > endDateValue) {
										return false;
									} else {
										return true;
									}
								}
							}
							]
						}),
						wnd.PlanGrid
					]
				})
			]
		});

		Ext.apply(wnd, {
			layout: 'fit',
			buttons: [
				new Ext.Button({
					iconCls: 'save16',
					text: BTN_FRMSAVE,
					handler: function() {
						wnd.savePlan();
					}
				}),
				'-',
				HelpButton(wnd),
				new Ext.Button({
					iconCls: 'close16',
					onTabElement: 'rifOk',
					text: BTN_FRMCLOSE,
					handler: function()
					{
						wnd.hide();
					}
				})
			],
			items: [
				wnd.MainPanel
			]
		});

		sw.Promed.swSmpWorkPlanEditWindow.superclass.initComponent.apply(wnd, arguments);
	}
});