/**
* Окно редактирования Целевых показателей
* вызывается из контр.карт дисп.наблюдения (PersonDispEditWindow)
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.EMK.PersonDispTargetRateEditWindow', {
	alias: 'widget.swPersonDispTargetRateEditWindowExt6',
	
	height: 225,
	//~ height: 600,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 488,
	cls: 'arm-window-new emk-forms-window',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(), //main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	
	title: 'Целевой показатель',
	show: function() {
		var win = this;
		this.callParent(arguments);
		
		win.taskButton.hide();
		this.action = arguments[0]['action'] || 'view';
		this.RateType_id = arguments[0]['RateType_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext6.emptyFn;
		
		var base_form = this.MainPanel.getForm();
		base_form.reset();
		this.FactRateGrid.getStore().removeAll();

		var loadMask = new Ext6.LoadMask(this.MainPanel, { msg: "Подождите, идет загрузка..." });
		this.MainPanel.getForm().load({
			url: '/?c=PersonDisp&m=loadPersonDispTargetRate',
			params: { 
				PersonDisp_id: this.PersonDisp_id,
				RateType_id: this.RateType_id
			},
			success: function (form, action) {
				var TypeName = win.MainPanel.queryById('RateType_Name').getValue();
				var names = TypeName.split('(');
				if(names.length>1) {
					var ed = names[1].slice(0,-1);
					win.MainPanel.queryById('ed1').setHtml(ed);
					win.MainPanel.queryById('ed2').setHtml(ed);
				}
				win.setTitle(names[0]); 
				if(win.action!='add') {
					win.FactRateGrid.load({
						params: { 
							PersonDisp_id: win.PersonDisp_id,
							RateType_id: win.RateType_id
						},
						callback: function() {
							if(win.FactRateGrid.getStore().getCount()>0) {
								var d = win.FactRateGrid.getStore().getAt(0).data.PersonDispFactRate_setDT;
								win.MainPanel.getForm().findField('ResultDate').setValue(d);
							}
						}
					});
				}
				loadMask.hide();
			},
			failure: function (form, action) {
				loadMask.hide();
				if (!action.result.success) {
					Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
					this.hide();
				}
			},
			scope: this
		});
		
		if (this.action=='view') {
			base_form.findField('TargetRate_Value').disable();
			base_form.findField('ResultDate').disable();
			base_form.findField('FactRate_Value').disable();
			this.queryById('button_save').disable();
		} else {
			base_form.findField('TargetRate_Value').enable();
			base_form.findField('ResultDate').enable();
			base_form.findField('FactRate_Value').enable();
			this.queryById('button_save').enable();
		}
	},
	doSave: function() 
	{
		var win = this;
		var form = this.MainPanel.getForm();
		
		
		var loadMask = new Ext6.LoadMask(win.MainPanel, { msg: "Подождите, идет сохранение..." });
		
		if (!form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = {
			PersonDisp_id: this.PersonDisp_id,
			RateType_id: this.RateType_id
		};
		var grid = this.FactRateGrid;
		var PersonDispFactRateData = [];
		grid.getStore().clearFilter();
		
		if(win.action=='add' || win.FactRateGrid.getStore().getCount()==0) {
			win.FactRateGrid.getStore().add({ 
				PersonDispFactRate_id:-1, 
				RecordStatus_Code: 0, 
				Rate_id: '', 
				PersonDispFactRate_setDT: win.MainPanel.getForm().findField('ResultDate').getValue(),
				PersonDispFactRate_Value: win.MainPanel.getForm().findField('FactRate_Value').getValue()
			});
		} else {
			var rec = win.FactRateGrid.getStore().getAt(0);
			rec.set('PersonDispFactRate_Value', win.MainPanel.getForm().findField('FactRate_Value').getValue());
			rec.set('PersonDispFactRate_setDT', win.MainPanel.getForm().findField('ResultDate').getValue() );
			rec.set('RecordStatus_Code', 2);
			rec.commit();
		}
		
		if ( grid.getStore().getCount() > 0 ) {
			PersonDispFactRateData = sw4.getStoreRecords(grid.getStore());
			grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}
		
		params.PersonDispFactRateData = Ext6.util.JSON.encode(PersonDispFactRateData);
		
		loadMask.show();		
		form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.returnFunc();
					}	
				}
				else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении показателей произошла ошибка'));
				}
							
			}.createDelegate(this)
		});
	},
	initComponent: function() {
		var win = this;
		
		win.FactRateGrid = new Ext6.grid.Panel({
			region: 'south',
			autoLoad: false,
			xtype: 'grid',
			columns: [
				{ dataIndex: 'PersonDispFactRate_id', key: true, type: 'int', header: 'PersonDispFactRate_id' },
				{ dataIndex: 'RecordStatus_Code', type: 'int', header: 'RecordStatus_Code'},
				{ dataIndex: 'Rate_id', hidden: true, type: 'int', header: 'Rate_id'},
				{ dataIndex: 'PersonDispFactRate_setDT', header: langs('Дата результата'), type: 'date', width: 150},
				{ dataIndex: 'PersonDispFactRate_Value', header: langs('Фактическое значение'), type: 'float'}
			],
			load: function() {
				var me = this;
				this.getStore().load({
					params: {
						PersonDisp_id: win.PersonDisp_id,
						RateType_id: win.RateType_id
					},
					callback: function(records, operation, success) {
						if(win.FactRateGrid.getStore().getCount()>0) {
							var DateValue = win.FactRateGrid.getStore().getAt(0).data.PersonDispFactRate_setDT;
							var FactValue = win.FactRateGrid.getStore().getAt(0).data.PersonDispFactRate_Value;
							win.MainPanel.getForm().findField('ResultDate').setValue(DateValue);
							win.MainPanel.getForm().findField('FactRate_Value').setValue(FactValue);
						}
					}
				});
			},
			
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonDispFactRate_id', key: true, type: 'int', header: 'PersonDispFactRate_id' },
					{ name: 'RecordStatus_Code', type: 'int', header: 'RecordStatus_Code' },
					{ name: 'Rate_id', hidden: true, type: 'int', header: 'Rate_id' },
					{ name: 'PersonDispFactRate_setDT', dateFormat: 'd.m.Y', header: langs('Дата результата'), type: 'date', width: 150},
					{ name: 'PersonDispFactRate_Value', header: langs('Фактическое значение'), type: 'float'}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadPersonDispFactRateList',
					reader: {
						type: 'json'
					}
				},
				sorters: [{
					property: 'PersonDispFactRate_id',
					direction: 'DESC'
				}]
			})
		});
		
		win.MainPanel = new Ext6.form.FormPanel({
			bodyPadding: '25 25 25 30',
			cls: 'dispcard',
			region: 'center',
			border: false,
			msgTarget: 'side',
			items:[{
				name: 'RateValueType_SysNick',
				xtype: 'hidden'
			}, {
				name: 'RateType_id',
				itemId: 'RateType_id',
				xtype: 'hidden'
			}, {
				name: 'RateType_Name',
				itemId: 'RateType_Name',
				xtype: 'hidden'
			}, {
				name: 'Sex_id',
				xtype: 'hidden'
			}, {
				fieldLabel: langs('Дата результата'),
				width: 280,
				labelWidth: 155,
				name: 'ResultDate',
				xtype: 'datefield',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
				formatText: null,
				invalidText: 'Неправильная дата',
			}, {
				layout: 'column',
				border: false,
				items: [{
					fieldLabel: langs('Целевое значение'),
					width: 70+155+5,
					labelWidth: 155,
					name: 'TargetRate_Value',
					xtype: 'numberfield'
				}, {
					xtype: 'label',
					itemId: 'ed1',
					html: '',
					userCls: 'x6-form-item-label-default',
					style: 'padding: 8px 10px;',
				}]
			}, {
				layout: 'column',
				border: false,
				style: 'padding-top: 4px',
				items: [{
					fieldLabel: langs('Фактическое значение'),
					width: 70+155+5,
					labelWidth: 155,
					name: 'FactRate_Value',
					xtype: 'numberfield'
				}, {
					xtype: 'label',
					itemId: 'ed2',
					html: '',
					userCls: 'x6-form-item-label-default',
					style: 'padding: 8px 10px;',
				}]
			}
			],
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{ name: 'RateType_id' },
						{ name: 'RateType_Name' },
						{ name: 'TargetRate_Value' },
						{ name: 'RateValueType_SysNick' },
						{ name: 'Sex_id' }
					]
				})
			}),
			url: '/?c=PersonDisp&m=savePersonDispTargetRate'		
		});

		Ext6.apply(win, {
			layout: 'border',
			items: [
				win.MainPanel
				//~ ,win.FactRateGrid
			],
			buttons: ['->',
			{
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel',
				userCls:'buttonPoupup buttonCancel',
				handler:function () {
					win.hide();
				}
			},
			{
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}
			]
		});

		this.callParent(arguments);
	}
});