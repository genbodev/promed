/* 
Регистр прихода-расхода медикаментов СМП
*/


Ext.define('common.DispatcherStationWP.tools.swSmpCallCardFarmacyRegisterWindow', {
	alias: 'widget.swSmpCallCardFarmacyRegisterWindow',
	id: 'swSmpCallCardFarmacyRegisterWindow',
	extend: 'Ext.window.Window',
	title: 'Регистр прихода-расхода медикаментов СМП',
	width: 850,
	height: 500,
	maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	callback: Ext.emptyFn,
	cbscope: this,
	initComponent: function() {
		var me = this
		
		var drugList = Ext.create('Ext.grid.Panel', {
			//region: 'north',
			title: 'Список медикаментов',
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			viewConfig: {
				loadingText: 'Загрузка'
			},
			store: new Ext.data.JsonStore({
				autoLoad: true,
				numLoad: 0,
				fields: [
					{name: 'Drug_id', type: 'int'},
					{name: 'CmpFarmacyBalance_id', type: 'int'},
					{name: 'AddDate', type: 'string'},					
					{name: 'DDFGT', type: 'string'},
					{name: 'Drug_Fas', type: 'string'},
					{name: 'Drug_PackName', type: 'string'},
					{name: 'CmpFarmacyBalance_PackRest', type: 'string'},					
					{name: 'CmpFarmacyBalance_DoseRest', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadSmpFarmacyRegister',
					reader: {
						type: 'json',
						successProperty: 'success',
						root: 'data'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				}
			}),
			columns: [
				{ dataIndex: 'Drug_id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'AddDate', text: 'Дата пополнения', width: 120 },
				{ dataIndex: 'DDFGT', text: 'Наименование', flex: 1 },				
				{ dataIndex: 'Drug_PackName', text: 'Единица учета', width: 120 },				
				{ dataIndex: 'Drug_Fas', text: 'Кол-во в упаковке', width: 130 },
				{ dataIndex: 'CmpFarmacyBalance_PackRest', text: 'Остаток (ед. учета)', width: 130},
				{ dataIndex: 'CmpFarmacyBalance_DoseRest', text: 'Остаток (ед. доз)', width: 130 }
				
			],
			listeners: {
				beforecellclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts )
				{
					var form = drugOffFormPanel.getForm();
					form.findField('DDFGT').setValue(record.get('DDFGT'));
					form.findField('CmpFarmacyBalance_DoseRest').setValue(record.get('CmpFarmacyBalance_DoseRest'));
					form.findField('CmpFarmacyBalance_PackRest').setValue(record.get('CmpFarmacyBalance_PackRest'));
					form.findField('Drug_PackName').setValue(record.get('Drug_PackName'));
					form.findField('Drug_id').setValue(record.get('Drug_id'));
					form.findField('CmpFarmacyBalance_id').setValue(record.get('CmpFarmacyBalance_id'));
					if (!record.get('Drug_Fas')) {
						form.findField('Drug_Fas').setValue('1');
					} else {
						form.findField('Drug_Fas').setValue(record.get('Drug_Fas'));
					}
				}
			}
		});
		var drugOffFormPanel = Ext.create('sw.BaseForm', {
			id: this.id+'drugWriteOffForm',
			padding: '10 15',
			frame: true,
			 border: false,
			//dock: 'top',
			layout: {
					padding: '5 20',
					align: 'stretch',
					type: 'vbox'
			},
			dockedItems: [
				{
					xtype: 'container',
					flex: 1,
					dock: 'top',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'hidden',
							name: 'CmpFarmacyBalance_id'
						},
						{
							xtype: 'hidden',
							name: 'Drug_id'
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Название',
							labelAlign: 'right',
							labelWidth: 120,
							name: 'DDFGT',
							readOnly: true
						}
					]
				},
				{
					xtype: 'container',
					flex: 1,
					dock: 'top',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'container',
							flex: 1,
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: [
								{
									xtype: 'textfield',
									fieldLabel: 'Ед. учета',
									labelAlign: 'right',
									labelWidth: 120,
									name: 'Drug_PackName',
									readOnly: true
								},
								{
									xtype: 'textfield',
									width: 150,
									fieldLabel: 'Остаток (ед. доз)',
									labelAlign: 'right',
									labelWidth: 120,
									name: 'CmpFarmacyBalance_DoseRest',
									readOnly: true
								},
								{
									xtype: 'textfield',
									width: 150,
									fieldLabel: 'Кол-во (ед. доз.)',
									labelAlign: 'right',
									labelWidth: 120,
									enableKeyEvents: true,
									allowBlank: false,
									maskRe:/[0-9.]/i,
									name: 'DoseCount',
									listeners: {
										'keydown' : function(cmp, e, eOpts){
											Ext.defer(function() {
											var frm = drugOffFormPanel.getForm();
											var val = frm.findField('Drug_Fas').getValue();
											if (val)
											{
												var rounded = (cmp.getValue()/val).toString();
												var countCharsAfterDot =  rounded.length - rounded.indexOf('.' , 0) - 1;
												if (countCharsAfterDot > 2){
													countCharsAfterDot = 2
												}
												frm.findField('PackCount').setValue((cmp.getValue()/val));
												frm.findField('PackCount').setValue((cmp.getValue()/val).toFixed(countCharsAfterDot));
											}
											}, 200);
										}
									}
								}
							]
						},
						{
							xtype: 'container',
							flex: 1,
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: [
								{
									xtype: 'textfield',
									fieldLabel: 'Кол-во в упак.',
									labelAlign: 'right',
									labelWidth: 150,
									name: 'Drug_Fas',
									readOnly: true
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Остаток (ед. уч.)',
									labelAlign: 'right',
									labelWidth: 150,
									name: 'CmpFarmacyBalance_PackRest',
									readOnly: true
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Количество (ед. уч.)',
									labelAlign: 'right',
									labelWidth: 150,
									maskRe:/[0-9.]/i,
									name: 'PackCount',
									readOnly: true
								}
							]
						}
					]
				}
			]
		});

		
		Ext.applyIf(me, {
			
			xtype: 'container',
			layout: {
				align: 'stretch',
				type: 'vbox'
			},
			margin: '0 0 0 0',
			style: {
				'z-index': 90000
			},
			items: [
					drugList,
					drugOffFormPanel
			],
			buttons: [
				{
					xtype: 'button',
					id: this.id+'helpBtn',
					text: 'Помощь',
					iconCls   : 'help16',
					handler   : function()
					{
						ShowHelp(this.ownerCt.title);
					}
				},
				'->',
				{
					xtype: 'button',
					id: this.id+'writeOffBtn',
					iconCls: 'save16',
					text: 'Списать',
					handler: function(){
						var form = drugOffFormPanel.getForm();
						if (!drugOffFormPanel.getForm().isValid()) {
							Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
							return;
						}
						
						me.callback.apply(me.cbscope,[{
							Drug_id:form.findField('Drug_id').getValue(),
							CmpFarmacyBalance_id:form.findField('CmpFarmacyBalance_id').getValue(),
							Drug_Name:form.findField('DDFGT').getValue(),
							DoseCount:form.findField('DoseCount').getValue(),
							PackCount:form.findField('PackCount').getValue(),
							CmpFarmacyBalance_DoseRest:form.findField('CmpFarmacyBalance_DoseRest').getValue(),
							CmpFarmacyBalance_PackRest:form.findField('CmpFarmacyBalance_PackRest').getValue()
						}]);
						me.close();

					}
				},
				{
					xtype: 'button',
					id: this.id+'cancelBtn',
					iconCls: 'cancel16',
					text: 'Отменить',
					margin: '0 5',
					handler: function(){
						me.close()
					}
				},
				
			]
		})
		
		me.callParent()
	},
	show: function() {
		
		this.callParent();
		if (!arguments || !arguments[0]) {
			Ext.Msg.alert('Ошибка открытия формы', 'Не переданы необходисые параметры формы');
			return false;
		}
		
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].cbscope) {
			this.cbscope = arguments[0].cbscope;
		}
	}
})

