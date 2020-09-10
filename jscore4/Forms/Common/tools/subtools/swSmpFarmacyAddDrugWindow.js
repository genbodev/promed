/* 
 swSmpFarmacyAddDrugWindow - окно добавления медикамента
 */


Ext.define('sw.tools.subtools.swSmpFarmacyAddDrugWindow', {
	alias: 'widget.swSmpFarmacyAddDrugWindow',
	extend: 'Ext.window.Window',
	title: 'Добавление медикамента',
	width: 650,
//	height: 300,
	modal: true,
	maximizable: true,
//	layout: {
//        align: 'stretch',
//        type: 'vbox'
//    },
	layout: 'fit',
	bodyBorder: false,
	manageHeight: false,
	initComponent: function() {
		var me = this
		me.on('show', function(){
			drugFormPanel.getForm().isValid()
		})
		
		
/*abstract controller*/
		
		var drugsCombobox = Ext.create('sw.dDrugsCombo', {
			name: 'dDrugsCombo',
			listeners:
			{
				'change' : function(me, newValue, oldValue, eOpts){
					if (typeof newValue == 'number'){
						drugPacsCombobox.store.load({
						params:{
								DrugPrepFas_id: newValue,
								mode: 'income'
						}
						})
					}
				}
			},
			onTrigger2Click: function(e) {
				var drugsCombobox = this;
				Ext.create('sw.tools.subtools.swDrugPrepWinSearch',{
					listeners:{
						onDrugSelect: function(rec, drugPrepFasId){
							//console.log(drugsCombobox);
							//drugsCombobox.setValue(rec);
							drugsCombobox.store.load({
								params:{
										DrugPrepFas_id: drugPrepFasId,
										Drug_id: rec,
										mode: 'income'
								},
								callback: function(records, operation, success) {
									unorec = drugsCombobox.store.getAt(0)
									drugsCombobox.setValue(unorec.get('DrugPrepFas_id'))
								}
							})
						}
					}
				}).show();
			}
		})
		var drugPacsCombobox = Ext.create('sw.dPacksDrugsCombo', {
			editable: false,
			name: 'drugPacsCombobox'
		})
		
		drugsCombobox.store.on('clear', function(){
			var drugForm = drugFormPanel.getForm();
			drugPacsCombobox.store.removeAll();
			drugPacsCombobox.setValue('');
			drugForm.findField('drugUnitName').setValue('');
			drugForm.findField('drugFormName').setValue('');
			drugForm.findField('countInPack').setValue('');
			drugForm.findField('countDrugDoze').setValue('');
			drugForm.findField('countDrugUnit').setValue('');
			drugForm.findField('dateDelivery').setValue('');
		})
		
		drugPacsCombobox.store.on('load', function(){
			if (drugPacsCombobox.store)
			{
				if (drugPacsCombobox.store.count() == 1){
					var drugForm = drugFormPanel.getForm(),
						unorec = drugPacsCombobox.store.getAt(0);				
					drugPacsCombobox.setValue(unorec.get('Drug_id'));
					drugForm.findField('drugUnitName').setValue(unorec.get('DrugUnit_Name'));
					drugForm.findField('drugFormName').setValue(unorec.get('DrugForm_Name'));
					drugForm.findField('countInPack').setValue(unorec.get('Drug_Fas'));
				}
				else{
					drugPacsCombobox.focus()
					drugPacsCombobox.expand()
				}
			}
		})
/*end abstract controller*/		

		var drugFormPanel = Ext.create('sw.BaseForm', {
			id: 'drugForm',
			frame: true,
			 border: false,
			//dock: 'top',
			layout: {
					padding: '5 20',
					align: 'stretch',
					type: 'vbox'
			},
			items: [
				drugsCombobox,
				drugPacsCombobox,
				 {
					xtype: 'container',
					height: 30,
					layout: {
						align: 'middle',
						pack: 'center',
						type: 'hbox'
					},
					items: [
						{
							xtype: 'textfield',
							flex: 1,
							width: 150,
							fieldLabel: 'Ед. учета',
							labelAlign: 'right',
							name: 'drugUnitName',
							readOnly: true
						},
						{
							xtype: 'textfield',
							flex: 1,
							width: 150,
							fieldLabel: 'Лек. форма',
							labelAlign: 'right',
							name: 'drugFormName',
							readOnly: true
						},
						{
							xtype: 'textfield',
							flex: 1,
							width: 218,
							fieldLabel: 'Кол-во в упак.',
							labelAlign: 'right',
							labelWidth: 150,
							name: 'countInPack',
							readOnly: true
						}

					]
				},
				{
					xtype: 'container',
					height: 30,
					layout: {
						align: 'middle',
						pack: 'center',
						type: 'hbox'
					},
					items: [

						{
							xtype: 'textfield',
							flex: 1,
							fieldLabel: 'Кол-во (ед. уч.)',
							labelAlign: 'right',
							labelWidth: 150,
							maskRe:/[0-9.]/i,
							name: 'countDrugUnit',
							enableKeyEvents: true,
							allowBlank: false,	
							listeners: {
								'keydown' : function(cmp, e, eOpts){
									Ext.defer(function() {
									var frm = me.down('form').getForm(),
										val = frm.findField('countInPack').getValue();
									if (val)
									{frm.findField('countDrugDoze').setValue(cmp.getValue()*val)}
									}, 200);
								}
							}
						},
						{
							xtype: 'textfield',
							flex: 1,
							fieldLabel: 'Количество (ед. доз.)',
							labelAlign: 'right',
							labelWidth: 150,
							maskRe:/[0-9.]/i,
							name: 'countDrugDoze',
							allowBlank: false,
							enableKeyEvents: true,
							listeners: {
								'keydown' : function(cmp, e, eOpts){
									Ext.defer(function() {
									var frm = me.down('form').getForm(),
									 val = frm.findField('countInPack').getValue();
									if (val)
									{frm.findField('countDrugUnit').setValue(cmp.getValue()/val)}
									}, 200);
								}
							}								
						},
						{
							xtype: 'datefield',
							flex: 1,
							width: 150,
							fieldLabel: 'Дата поставки',
							labelAlign: 'right',
							name: 'dateDelivery',
							allowBlank: false,
							format: 'd.m.Y',
							plugins: [new Ux.InputTextMask('99.99.9999')]
						}
					]
				}
			]
		})

		
		Ext.applyIf(me, {
			items: [
			drugFormPanel
			],
			 dockedItems: [
                {
                    xtype: 'container',
                    dock: 'bottom',
					
                    layout: {
						align: 'right',
                        type: 'vbox',
						padding: 3
                    },
                    items: [
						{
						xtype: 'container',
						height: 30,
						layout: {
							align: 'middle',
							pack: 'center',
							type: 'hbox'
						},
							items: [
								{
									xtype: 'button',
									refId: 'saveBtn',
									iconCls: 'save16',
									text: 'Сохранить',
									handler: function(){
										me.saveDrug()
									}
								},
								 {
									xtype: 'button',
									refId: 'cancelBtn',
									iconCls: 'cancel16',
									text: 'Отменить',
									margin: '0 5',
									handler: function(){
										me.close()
									}
								},
								{
									xtype: 'button',
									refId: 'helpBtn',
									text: 'Помощь',
									iconCls   : 'help16',
									handler   : function()
									{
										ShowHelp(me.title);
									}
								}
							]
						}
                    ]
                }
            ]
		})
		
		me.callParent(arguments)
	},
	setDrug : function(drugId, drugPrepFasId){	
		console.log(this);
		drugsCombobox.store.load({
			params:{
					DrugPrepFas_id: drugPrepFasId,
					Drug_id: drugId,
					mode: 'income'
			},
			callback: function(records, operation, success) {
				unorec = drugsCombobox.store.getAt(0)
				drugsCombobox.setValue(unorec.get('DrugPrepFas_id'))
			}
		})
//		
	},
	saveDrug : function(){
		var formpanel = this.down('form[id=drugForm]'),
			allParams = formpanel.getForm().getValues(),
			drugsCombobox = formpanel.getForm().findField('dDrugsCombo'),
			drugPacsCombobox = formpanel.getForm().findField('drugPacsCombobox');

		if	( (!formpanel.getForm().isValid()) || (allParams.countDrugUnit < 1) )
		{
			Ext.Msg.alert('Введите данные', 'Обязательные поля выделены особо')
			function hide_message() {
				Ext.defer(function() {
					Ext.MessageBox.hide();
				}, 1500);
			}
			hide_message();
		}
		else
		{		
			Ext.Ajax.request({
				url: '/?c=CmpCallCard4E&m=saveSmpFarmacyDrug',
				params: {
					CmpFarmacyBalanceAddHistory_AddDate: allParams.dateDelivery,
					CmpFarmacyBalanceAddHistory_RashCount: allParams.countDrugUnit,
					CmpFarmacyBalanceAddHistory_RashEdCount: allParams.countDrugDoze,
					DrugPrepFas_id: drugsCombobox.getValue(),
					Drug_id: drugPacsCombobox.getValue()
				},
				callback: function(opt, success, response) {
					if (success){
						formpanel.getForm().setValues('');
						this.close();
						var grid = Ext.ComponentQuery.query('swSmpFarmacyRegisterWindow grid[refId=drugListGrid]')[0];
						grid.store.reload({
							callback: function(records, operation, success) {
								var rec = grid.store.findRecord('Drug_id', drugPacsCombobox.getValue());
								grid.getView().select(rec);
							}
						})
					}
				}.bind(this)
			})
		}
	}
})

