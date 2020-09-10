/**
* amm_vacPlanParams - окно ввода параметров планирования прививок
*
* PromedWeb - The New Generation of Medical Statistic Software
*
* @copyright    Copyright (c) 2012 
* @author       
* @version      14.06.2012
* @comment      
*/

sw.Promed.amm_vacPlanParams = Ext.extend(Ext.Window, {
	id: 'amm_vacPlanParams',
	title: "Параметры планирования прививок",
	border: false,
	width: 300,
	height: 150,
	maximizable: false,  
	closeAction: 'hide',
	layout: 'border',
	codeRefresh: true,
	modal:true,
	
	initComponent: function() {
		var params = new Object();
		var form = this;
		
		Ext.apply(this, {
			style: 'margin: 5px; padding: 0px;',
			formParams: null,
			buttons: [{
				text: '-'
			},
			{
				text: 'Сформировать план',
				tabIndex: TABINDEX_VACIMPFRM + 20,
				handler: function() {
					if (! Ext.getCmp('vacPlanParamsDateRange').isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						return false;
					}
                                        
					//vacPlanParamsForm.getEl().mask();
					var Mask = new Ext.LoadMask(Ext.get('vacPlanParamsEditForm'), { msg: "Пожалуйста, подождите, идет формирование данных..." });
					Mask.show();
					Ext.Ajax.request({
							url: '/?c=Vaccine_List&m=formPlanVac',
							method: 'POST',
							params: {
								'DateStart': Ext.getCmp('vacPlanParamsEditForm').form.findField('vacPlanParamsDateRange').getValue1().format('d.m.Y'),
								'DateEnd': Ext.getCmp('vacPlanParamsEditForm').form.findField('vacPlanParamsDateRange').getValue2().format('d.m.Y'),
								'Person_id': Ext.getCmp('amm_vacPlanParams').formParams.person_id
							},
							callback: function(opt, success, resp){
								Mask.hide();
							},
							success: function(){
								Ext.getCmp('amm_PersonVacFixed').ViewGridPanel.getStore().reload(); 
								Ext.getCmp('amm_PersonVacPlan').ViewGridPanel.getStore().reload();
								Ext.getCmp('amm_PersonPersonMantu').ViewGridPanel.getStore().reload();
								//Ext.getCmp('amm_PersonDigest').ViewGridPanel.getStore().reload();
								Ext.getCmp('amm_Person063').ViewGridPanel.getStore().reload();
								Ext.getCmp('amm_kard063_tabpanel').setActiveTab(2);
								form.hide();
							}
					});
					
				}.createDelegate(this)
			}, {
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				tabIndex: TABINDEX_VACIMPFRM + 21,
				text: '<u>З</u>акрыть'
			}],
			
			items: [
				this.vacEditForm = new Ext.form.FormPanel({
					autoScroll: true,
					region: 'center',
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'vacPlanParamsEditForm',
					name: 'vacEditForm',
					labelAlign: 'right',
					labelWidth: 70,
					layout: 'form',
					items: [
						{
							height:5,
							border: false
						},
						{
							border: false,
							layout: 'form',
							items: [{

								autoHeight: true,
								layout: 'form',
								style: 'margin: 5px; padding: 5px;',
								title: 'Диапазон планирования',
								xtype: 'fieldset',
								anchor:'-10',
								items: [{
										
									allowBlank: false,
//                  name : "Date_PlanRange",
//id: 'vacPlanParamsDateRange',
									name : "vacPlanParamsDateRange",
                                                                        id : 'vacPlanParamsDateRange', 
									xtype : "daterangefield",
									width : 170,
									fieldLabel : 'Период',
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
									tabIndex : 1306

								}]

							}]

						}]
				})
			]
			
		});
		sw.Promed.amm_vacPlanParams.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(record) {
		sw.Promed.vac.utils.consoleLog(record);
		sw.Promed.amm_vacPlanParams.superclass.show.apply(this, arguments);
		this.formParams = record;

//    this.vacEditForm.form.findField('vacPlanParamsDate').setValue(new Date());
		this.vacEditForm.form.findField('vacPlanParamsDateRange').setValue(this.formParams.dt + ' - ' + this.formParams.dt2);
                this.vacEditForm.form.findField('vacPlanParamsDateRange').setMinValue (new Date())
//    Ext.getCmp('amm_vacPlanParams').formParams.dt
	}
});
