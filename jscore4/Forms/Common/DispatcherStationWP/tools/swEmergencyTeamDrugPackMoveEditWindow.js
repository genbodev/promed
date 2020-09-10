/* 
Списание с укладки на пациента
*/


Ext.define('common.DispatcherStationWP.tools.swEmergencyTeamDrugPackMoveEditWindow', {
	alias: 'widget.swEmergencyTeamDrugPackMoveEditWindow',
	id: 'swEmergencyTeamDrugPackMoveEditWindow',
	extend: 'Ext.window.Window',
	title: 'Списание с укладки на пациента',
	width: 850,
	height: 500,
	maximizable: true,
	modal: true,
	layout: {
        align: 'stretch',
        type: 'vbox'
    },
	callback: Ext.emptyFn,
	initComponent: function() {
		var me = this
		
		this.drugList = Ext.create('Ext.grid.Panel', {
			//region: 'north',
			title: 'Список медикаментов',
			flex: 1,
			autoScroll: true,
			stripeRows: true,
			viewConfig: {
				loadingText: 'Загрузка'
			},
			store: new Ext.data.JsonStore({
				numLoad: 0,
				fields: [
					{name: 'EmergencyTeamDrugPack_id', type: 'int'},
					{name: 'Drug_id', type: 'int'},
					{name: 'DrugTorg_Name', type: 'string'},
					{name: 'EmergencyTeamDrugPack_Total', type: 'string'},
					// {name: 'Drug_id', type: 'int'},
					// {name: 'DDFGT', type: 'string'},
					// {name: 'CmpFarmacyBalance_id', type: 'int'},
					// {name: 'AddDate', type: 'string'},					
					// {name: 'Drug_Fas', type: 'string'},
					// {name: 'Drug_PackName', type: 'string'},
					// {name: 'CmpFarmacyBalance_PackRest', type: 'string'},					
					// {name: 'CmpFarmacyBalance_DoseRest', type: 'string'}
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard4E&m=loadEmergencyTeamDrugPackByCmpCallCardId',
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
				
				{ dataIndex: 'DrugTorg_Name', text: 'Наименование', flex: 1 },				
				{ dataIndex: 'EmergencyTeamDrugPack_Total', text: 'Остаток', width: 130 }
				
				// { dataIndex: 'Drug_id', text: 'ID', key: true, hidden: true, hideable: false },
				// { dataIndex: 'AddDate', text: 'Дата пополнения', width: 120 },
				// { dataIndex: 'DDFGT', text: 'Наименование', flex: 1 },				
				// { dataIndex: 'Drug_PackName', text: 'Единица учета', width: 120 },				
				// { dataIndex: 'Drug_Fas', text: 'Кол-во в упаковке', width: 130 },
				// { dataIndex: 'CmpFarmacyBalance_PackRest', text: 'Остаток (ед. учета)', width: 130},
				// { dataIndex: 'CmpFarmacyBalance_DoseRest', text: 'Остаток (ед. доз)', width: 130 }
				
			],
			listeners: {
				beforecellclick: function( cmp, td, cellIndex, record, tr, rowIndex, e, eOpts )
				{
					var form = me.drugOffFormPanel.getForm();
					form.findField('DrugTorg_Name').setValue(record.get('DrugTorg_Name'));
					form.findField('Drug_id').setValue(record.get('Drug_id'));
					form.findField('EmergencyTeamDrugPack_id').setValue(record.get('EmergencyTeamDrugPack_id'));
					
				}
			}
		});
		this.drugOffFormPanel = Ext.create('sw.BaseForm', {
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
							name: 'EmergencyTeamDrugPackMove_id'
						},
						{
							xtype: 'hidden',
							name: 'EmergencyTeamDrugPack_id'
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
							name: 'DrugTorg_Name',
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
					items: [,
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
									fieldLabel: 'Количество',
									labelAlign: 'right',
									labelWidth: 120,
									maskRe:/[0-9.]/i,
									allowBlank: false,
									name: 'EmergencyTeamDrugPackMove_Quantity'
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
					this.drugList,
					this.drugOffFormPanel
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
						var form = me.drugOffFormPanel.getForm();
						if (!me.drugOffFormPanel.getForm().isValid()) {
							Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
							return;
						}
						
						if (!me.drugOffFormPanel.getForm().findField('Drug_id').getValue()) {
							Ext.Msg.alert('Проверка данных формы', 'Не выбрано ни одной позиции для списания. Пожалуйста, выберите медикамент списания');
							return;	
						}
						
						me.callback(me.drugOffFormPanel.getForm().getValues());
						
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
		if (!arguments || !arguments[0] || !arguments[0].CmpCallCard_id) {
			Ext.Msg.alert('Ошибка открытия формы', 'Не переданы необходисые параметры формы');
			return false;
		}
		
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].formParams) {
			this.drugOffFormPanel.getForm().setValues( arguments[0].formParams );
		}
		
		this.drugList.getStore().load({
			params: {
				CmpCallCard_id:arguments[0].CmpCallCard_id
			}
		})

	}
})

