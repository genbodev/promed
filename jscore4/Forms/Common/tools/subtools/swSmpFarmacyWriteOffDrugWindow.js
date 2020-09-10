/* 
 swSmpFarmacyWriteOffDrugWindow - окно списания медикамента
 */


Ext.define('sw.tools.subtools.swSmpFarmacyWriteOffDrugWindow', {
	alias: 'widget.swSmpFarmacyWriteOffDrugWindow',
	extend: 'Ext.window.Window',
	title: 'Списание медикамента',
	width: 500,
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
		var me = this;
		me.on('show', function(){
			drugOffFormPanel.getForm().isValid()
			me.loadData()
			
			buttWriteOff = me.down('container button[refId=writeOffBtn]')
			drugOffFormPanel.getForm().on('validitychange', function( form, valid, eOpts ){
				if(valid){
					buttWriteOff.setDisabled(false) 
				}
				else{
					buttWriteOff.setDisabled(true) 
				}
			})
		})
		
		
		
		conf = me.initialConfig
		
//		console.log(conf)
		//drugFormPanel.getForm().findField('drugName').setValue(conf.DDFGT)
		

/*end abstract controller*/		

		drugOffFormPanel = Ext.create('sw.BaseForm', {
			id: 'drugWriteOffForm',
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
							xtype: 'textfield',
							fieldLabel: 'Название',
							labelAlign: 'right',
							labelWidth: 120,
							name: 'DDFGT',
							readOnly: true
						},
						{
							xtype: 'smpAmbulanceTeamCombo',
//							fieldLabel: 'Бригада СМП',
							labelAlign: 'right',
							labelWidth: 120,
							allowBlank: false,
							editable: false
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
									name: 'countWriteOffDrugDoze',
									listeners: {
										'keydown' : function(cmp, e, eOpts){
											Ext.defer(function() {
											frm = drugOffFormPanel.getForm()
											val = frm.findField('Drug_Fas').getValue()
											if (val)
											{
												rounded = (cmp.getValue()/val).toString()
												countCharsAfterDot =  rounded.length - rounded.indexOf('.' , 0) - 1
												if (countCharsAfterDot > 2){countCharsAfterDot = 2}
												frm.findField('countDrugPacs').setValue((cmp.getValue()/val))
												frm.findField('countDrugPacs').setValue((cmp.getValue()/val).toFixed(countCharsAfterDot))
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
									name: 'countDrugPacs',
									readOnly: true
								}
							]
						}
					]
				}
			]
		})

		
		Ext.applyIf(me, {
			items: [
			drugOffFormPanel
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
									refId: 'writeOffBtn',
									iconCls: 'save16',
									text: 'Списать',
									disabled: true,
									handler: function(){
										me.writeOffDrug()
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
	loadData: function(){
		form = drugOffFormPanel.getForm()
		Ext.Object.each(conf, function(key, val, self){
			if(form.findField(key)){
				form.findField(key).setValue(val)
				if ((key == 'Drug_Fas') && (!val)){
					form.findField(key).setValue('1')
				}
			}
		})
	},
	writeOffDrug: function(){
		var me = this;
		frm = drugOffFormPanel.getForm()
		allvals = frm.getValues()
		if(frm.findField('countWriteOffDrugDoze').getValue() > frm.findField('CmpFarmacyBalance_DoseRest').getValue())
			{
				Ext.Msg.show({
					title: 'Превышен предел',
					msg: 'Указанное количество доз медикамента<br>превышает указанное в регистре.',
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					fn: function(){
						Ext.defer(function() {
						frm.findField('countWriteOffDrugDoze').focus(true)
						}, 300)
					}
			   });				
			}
		else
		{
			
			Ext.Ajax.request({
				url: '/?c=CmpCallCard4E&m=removeSmpFarmacyDrug',
				params: {
					CmpFarmacyBalanceRemoveHistory_DoseCount: allvals.countWriteOffDrugDoze,
					CmpFarmacyBalanceRemoveHistory_PackCount: allvals.countDrugPacs,
					CmpFarmacyBalance_DoseRest: allvals.CmpFarmacyBalance_DoseRest - allvals.countWriteOffDrugDoze,
					CmpFarmacyBalance_PackRest: allvals.CmpFarmacyBalance_PackRest - allvals.countDrugPacs,
					CmpFarmacyBalance_id: conf.CmpFarmacyBalance_id,
					Drug_id: conf.Drug_id,
					EmergencyTeam_id: allvals.smpAmbulanceTeamCombo
				}
			})
			this.close()
			
			gridup = Ext.ComponentQuery.query('swSmpFarmacyRegisterWindow grid[refId=drugListGrid]')[0]
			drugId = gridup.getSelectionModel().lastSelected.get('Drug_id')

			gridup.store.reload({
				callback: function(records, operation, success) {
					rec = gridup.store.findRecord('Drug_id', drugId)
					gridup.getView().select(rec)
				}
			})
			
			
			
			griddown = Ext.ComponentQuery.query('swSmpFarmacyRegisterWindow grid[refId=drugListHistoryGrid]')[0]
			griddown.store.reload()
		}
	}
	
})

