/**
 * swBloodTransfusion - форма добавления случая переливания крови
 *
 * Kukuzapa forever!
 */
sw.Promed.swBloodTransfusion = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swBloodTransfusion',
	objectSrc: '/jscore/Forms/Hospital/swBloodTransfusion.js',
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	collapsible: false,
	draggable: true,
	modal: true,
	
	doSave: function(){
		var win = this;
		var base_form = this.formPanel.getForm();
		
		if (this.status == 'view') return false;
		
		this.status = 'view';
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			this.status = 'edit';
			return false;
		}

		/*var doze = base_form.findField('TransfusionFact_Dose').getValue();
		if (doze <= 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Поле Доза не может содержать значение меньшее либо равное нулю.',
				title: ERR_INVFIELDS_TIT
			});
			this.status = 'edit';
			return false;
		}*/

		var TransfusionComplication = [];
		
		this.ViewFrame.getGrid().getStore().each(function (record) {
			TransfusionComplication.push({
				TransfusionComplType_id: record.get('TransfusionComplType_id'),
				TransfusionCompl_FactDT: Ext.util.Format.date(record.get('TransfusionCompl_FactDT'),'Y-m-d'),
				TransfusionCompl_id: record.get('TransfusionCompl_id')
			});
		})
		
		var params = {
			TransfusionComplication: Ext.util.JSON.encode(TransfusionComplication),
			TransfusionFact_setDT: Ext.util.Format.date(base_form.findField('TransfusionFact_setDT').getValue()),
			TransfusionAgentType_id: base_form.findField('TransfusionAgentType_id').getValue(),
			TransfusionIndicationType_id: base_form.findField('TransfusionIndicationType_id').getValue(),
			VizitClass_id: base_form.findField('VizitClass_id').getValue(),
			TransfusionFact_Dose: base_form.findField('TransfusionFact_Dose').getValue(),
			TransfusionFact_Volume: base_form.findField('TransfusionFact_Volume').getValue(),
			TransfusionReactionType_id: base_form.findField('TransfusionReactionType_id').getValue(),
			TransfusionMethodType_id: base_form.findField('TransfusionMethodType_id').getValue(),
			EvnPS_id: base_form.findField('EvnPS_id').getValue(),
			EvnSection_id: base_form.findField('EvnSection_id').getValue(),
			TransfusionFact_id: base_form.findField('TransfusionFact_id').getValue(),
			action: this.action
		};
		
		Ext.Ajax.request({
			params: params,
			failure: function () {
				sw.swMsg.alert('Ошибка', 'Ошибка сохранения');
				this.status = 'edit';
			},
			success: function (response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				
				if (response_obj.success != true) {
					sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка сохранения');
					win.status = 'edit';
				}
				else {
					win.callback();
				}
			},
			url: '/?c=EvnSection&m=saveTransfusionFact'
		});
	},
	
	initComponent: function(){
		var win = this;
		
		this.buttons = [{
			handler: function() {
				this.doSave();
			}.createDelegate(this),
			iconCls: 'save16',
			text: BTN_FRMSAVE,
		}, {
			text: '-'
		}, {
			handler: function() {
				if (win.action == 'edit') {
					win.callback();
				} else {
					this.hide();
				}
			}.createDelegate(this),
			iconCls: 'cancel16',
			text: BTN_FRMCANCEL
		}]
		
		this.ViewFrame = new sw.Promed.ViewFrame({
			id: 'TransfusionComplList',
			title:'Осложнения',
			height:200,
			autoLoadData: false,
			style: 'margin-bottom: 0.5em;',
			editRecord: function(action){
				var params = {};
				if (action == 'add'){
					params.action = 'add';
					params.callback = function(data){
						var record = new Ext.data.Record.create(win.ViewFrame.jsonData['store']);
						
						data = {
							TransfusionComplType_Name: data['TransfusionComplType_Name'],
							TransfusionComplType_id: data['TransfusionComplType_id'],
							TransfusionCompl_FactDT: data['TransfusionCompl_FactDT']
						}
						
						win.ViewFrame.getGrid().getStore().add(new record(data));
						this.hide();
					}
					getWnd('swTransfusionComplication').show(params);
				} else if (action == 'delete') {
					var record = win.ViewFrame.getGrid().getStore().getAt(win.ViewFrame.getSelectedIndex());
					
					Ext.Ajax.request({
						params: { TransfusionCompl_id: record.get('TransfusionCompl_id') },
						failure: function () {
							sw.swMsg.alert('Ошибка', 'Ошибка сохранения');
						},
						success: function (response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							
							if (response_obj.success != true) {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка сохранения');
							}
							else {
								win.ViewFrame.getGrid().getStore().remove(record);
							}
						},
						url: '/?c=EvnSection&m=deleteTransfusionCompl'
					});
				} else {
					var record = win.ViewFrame.getGrid().getStore().getAt(win.ViewFrame.getSelectedIndex());
					params.action = action;
					params.TransfusionCompl_FactDT = record.get('TransfusionCompl_FactDT');
					params.TransfusionComplType_Name = record.get('TransfusionComplType_Name');
					params.TransfusionComplType_id = record.get('TransfusionComplType_id');
					params.callback = function(data){
						record.set('TransfusionComplType_id',data.TransfusionComplType_id);
						record.set('TransfusionComplType_Name',data.TransfusionComplType_Name);
						record.set('TransfusionCompl_FactDT',data.TransfusionCompl_FactDT);
						record.commit();
						this.hide();
					}
					getWnd('swTransfusionComplication').show(params);
				}
			},
			
			onRowSelect: function(sm, index, record){
				this.setActionDisabled('action_edit',false);
				this.setActionDisabled('action_view',false);
				this.setActionDisabled('action_delete',false);
				if (win.action == 'view') {
					this.setActionDisabled('action_edit',true);
					this.setActionDisabled('action_delete',true);
				}
			},
			
			stringfields:
				[
					{name: 'TransfusionCompl_id', type: 'string', header: 'TransfusionCompl_id', width:100, key: true },//поменять кей
					{name: 'TransfusionComplType_id', type: 'int', header: 'ID', hidden: true },
					{name: 'TransfusionCompl_FactDT', type:'date', dateFormat: 'd.m.Y', header: 'Дата осложнения', width: 180},
					{name: 'TransfusionComplType_Name',  type: 'string', header: 'Осложнение', width: 400}
				],
			actions:
				[
					{name:'action_add', handler: function() {win.ViewFrame.editRecord('add');}},
					{name:'action_edit', handler: function() {win.ViewFrame.editRecord('edit');}},
					{name:'action_view', handler: function() {win.ViewFrame.editRecord('view');}},
					{name:'action_delete', handler: function() {win.ViewFrame.editRecord('delete');}},
					{name:'action_refresh', hidden: true},
					{name:'action_print', hidden: true}
				]
		});
		
		this.formPanel = new Ext.form.FormPanel({
			url: '/?c=EvnSection&m=loadTransfusionFact',
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'TransfusionFactForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader(
				{
					success: function() {}
				},
				[
					{ name: 'TransfusionFact_setDT' },
					{ name: 'TransfusionMethodType_id' },
					{ name: 'TransfusionAgentType_id' },
					{ name: 'TransfusionIndicationType_id' },
					{ name: 'VizitClass_id' },
					{ name: 'TransfusionFact_Dose' },
					{ name: 'TransfusionFact_Volume' },
					{ name: 'TransfusionReactionType_id' },
					{ name: 'TransfusionComplData' }
				]
			),
			items:[{
				allowBlank: false,
				fieldLabel: 'Дата',
				format: 'd.m.Y',
				id: 'TransfusionFact_setDT',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				selectOnFocus: true,
				width: 100,
				value: new Date(),
				xtype: 'swdatefield'
			}, {
				comboSubject: 'TransfusionMethodType',
				fieldLabel: 'Способ переливания',
				hiddenName: 'TransfusionMethodType_id',
				lastQuery: '',
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'TransfusionAgentType',
				fieldLabel: 'Трансфузионные средства',
				hiddenName: 'TransfusionAgentType_id',
				lastQuery: '',
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'TransfusionIndicationType',
				fieldLabel: 'Показания к трансфузии',
				hiddenName: 'TransfusionIndicationType_id',
				lastQuery: '',
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			},{
				allowBlank: false,
				comboSubject: 'VizitClass',
				fieldLabel: 'Тип',
				hiddenName: 'VizitClass_id',
				lastQuery: '',
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				allowNegative: false,
				fieldLabel: 'Доза(ед)',
				minValue: 1,
				maxValue: 99999,
				xtype: 'numberfield',
				name: 'TransfusionFact_Dose',
				allowDecimals: false,
			}, {
				allowNegative: false,
				fieldLabel: 'Объем(мл)',
				minValue: 1,
				maxValue: 99999,
				allowBlank: false,
				xtype: 'numberfield',
				name: 'TransfusionFact_Volume',
				allowDecimals: false,
			}, {
				comboSubject: 'TransfusionReactionType',
				fieldLabel: 'Трансфузионные реакции',
				hiddenName: 'TransfusionReactionType_id',
				lastQuery: '',
				typeCode: 'int',
				width: 300,
				xtype: 'swcommonsprcombo'
			}, {
				xtype: 'hidden',
				name: 'EvnSection_id'
			}, {
				xtype: 'hidden',
				name: 'EvnPS_id'
			}, {
				xtype: 'hidden',
				name: 'TransfusionFact_id'
			}, {
				xtype: 'hidden',
				name: 'TransfusionComplData'
			}, this.ViewFrame]
		});
		
		Ext.apply(this,{items:[this.formPanel,this.buttons]});
		
		sw.Promed.swBloodTransfusion.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
		var win = this;
		sw.Promed.swBloodTransfusion.superclass.show.apply(this, arguments);
		
		var base_form = this.findById('TransfusionFactForm').getForm();
		var view_frame = this.findById('TransfusionComplList').getGrid().getStore();
		
		base_form.reset();
		view_frame.removeAll();
		
		this.setTitle('swBloodTransfusion');
		
		if (arguments && arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
			
			var actionList = {
				'add': 'Переливание препаратов крови: добавление',
				'edit': 'Переливание препаратов крови: редактирование',
				'view': 'Переливание препаратов крови: просмотр'
			}
			
			this.setTitle(actionList[this.action]);
			
			if (arguments[0].EvnSection_id) this.formPanel.getForm().findField('EvnSection_id').setValue(arguments[0].EvnSection_id);
			if (arguments[0].EvnPS_id) this.formPanel.getForm().findField('EvnPS_id').setValue(arguments[0].EvnPS_id);
			if (arguments[0].TransfusionFact_id) this.formPanel.getForm().findField('TransfusionFact_id').setValue(arguments[0].TransfusionFact_id);
			
			if (arguments[0].callback) {
				this.callback = arguments[0].callback;
			}
			
			this.status = (this.action=='view')?'view':'edit';
			
			if (this.action != 'add'){
				base_form.load({
					params:{
						TransfusionFact_id: arguments[0].TransfusionFact_id,
						EvnSection_id: arguments[0].EvnSection_id,
						EvnPS_id: arguments[0].EvnPS_id
					},
					success: function(data) {
						var transfusion_compl_list = base_form.findField('TransfusionComplData').getValue();
						
						if (transfusion_compl_list) {
							transfusion_compl_list = transfusion_compl_list.split('/');

							transfusion_compl_list.forEach(function (compl) {
								var rec = compl.split(';');
								
								var data = {
									TransfusionComplType_id: rec[0],
									TransfusionComplType_Name: rec[1],
									TransfusionCompl_FactDT: rec[2],
									TransfusionCompl_id: rec[3]
								}
								
								view_frame.loadData([ data ], true);
							})
						}
						
						if (win.action == 'view') {
							win.enableEdit(false);
							win.findById('TransfusionComplList').setActionDisabled('action_add',true);
							win.buttons[0].setDisabled(true);
							win.buttons[0].show();
						} else {
							win.enableEdit(true);
							win.findById('TransfusionComplList').setActionDisabled('action_add',false);
							win.buttons[0].setDisabled(false);
						}
					}
				});
			} else {
				this.enableEdit(true);
				this.findById('TransfusionComplList').setActionDisabled('action_add',false);
				this.buttons[0].setDisabled(false);
			}
		}
	}
});