/**
* amm_PresenceVacEditForm - окно редактирования серий вакцин
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       
* @version      2012.08.15c
* @comment      
*/

										
sw.Promed.amm_PresenceVacEditForm = Ext.extend(sw.Promed.BaseForm, {
	title: "",
        titleBase: "Наличие вакцин",
	id: 'amm_PresenceVacEditForm',
	border: false,
	width: 600,
	height: 250,
	maximizable: false,        
	layout:'fit',
	codeRefresh: true,
	closeAction: 'hide',
	objectName: 'amm_PresenceVacEditForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_PresenceVacEditForm.js',
	onHide: Ext.emptyFn,
	listeners: {
		'show': function(c) {
			this.setTitle('');
		},
		'readyFrmType': function(frmType, actType) {
			var pars = {
				modeType: actType,
				formType: frmType
			};
			this.setTitle(this.titleExt.init(pars).getTitle());
		}
  },
			
	edit: function(ParamEdit) {
			if (ParamEdit) {
				Ext.getCmp('VacPresence_SaveButton').enable();
				Ext.getCmp('VacPresence_VaccineCombo').enable();
				Ext.getCmp('VacPresence_Seria').enable();
				Ext.getCmp('VacPresence_Seria').enable();
				Ext.getCmp('VacPresence_Period').enable();
				Ext.getCmp('VacPresence_Manufacturer').enable();
				 Ext.getCmp('VacPresence_toHave').enable();
			 }
			else {
				Ext.getCmp('VacPresence_SaveButton').disable();
				Ext.getCmp('VacPresence_VaccineCombo').disable();
				Ext.getCmp('VacPresence_Seria').disable();
				Ext.getCmp('VacPresence_Period').disable();
				Ext.getCmp('VacPresence_Manufacturer').disable();
				Ext.getCmp('VacPresence_toHave').disable();
			}
		},
		
  initComponent: function() {
		var vEdiit = false;   
		var form = this;
		this.titleExt = sw.Promed.vac.utils.getFormTitleObj();
		this.FormPanel =
			new Ext.form.FormPanel({
				//                        frame: false,
				bodyStyle: 'padding: 5px',
				formParams: null,

				items: [
				{
					height : 20,
					border : false,
					cls: 'tg-label'
				},
				{
					//                                allowBlank: false,
					id: 'VacPresence_VaccineCombo',
					autoLoad: true,
					editable: false,
					fieldLabel: 'Вакцина',
					valueField: 'Vaccine_id',
					displayField: 'Vaccine_Name',
					tabIndex: TABINDEX_PRESVACEDITFRM + 10,
					//                                hiddenName: 'Vaccine_id',
					width: 450,
					allowBlank: false,
					xtype: 'amm_VaccineCombo'
				},
				{
					disabled: vEdiit,
					fieldLabel: 'Серия вакцины',
					tabIndex: TABINDEX_PRESVACEDITFRM + 11,
					id: 'VacPresence_Seria',
					width: 150,
					maxLength: 15,
					allowBlank: false,
					xtype: 'textfield'
				},
				{
					fieldLabel: 'Срок годности',
					tabIndex: TABINDEX_PRESVACEDITFRM + 12,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					id: 'VacPresence_Period'

				},
				{
					disabled: vEdiit,
					fieldLabel: 'Изготовитель',
					tabIndex: TABINDEX_PRESVACEDITFRM + 13,
					id: 'VacPresence_Manufacturer',
					width: 350,
					maxLength: 50,
					allowBlank: false,
					xtype: 'textfield'
				},
				{
					xtype: 'checkbox',
					height:24,
					tabIndex: TABINDEX_PRESVACEDITFRM + 14,
					id: 'VacPresence_toHave',
					checked: true,
					labelSeparator: '',
					boxLabel: 'В наличии'
				}
				]

			});
		 
		Ext.apply(this, {
			items: [
			this.FormPanel
			],
					
			buttons: [
			{
//				text: 'Сохранить',
								text: BTN_FRMSAVE,
//							tabIndex: TABINDEX_PEF + 60,
				iconCls: 'save16',
				tabIndex: TABINDEX_PRESVACEDITFRM + 31,
								id: 'VacPresence_SaveButton',
				handler: function() {
					var MyForm = Ext.getCmp('amm_PresenceVacEditForm').FormPanel;
					if (!MyForm.form.isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						return false;
					}
					var vacFormPanel = Ext.getCmp('vacPurpEditForm');
					var vaccineParamsUpd = new Object();
					vaccineParamsUpd.action = Ext.getCmp('amm_PresenceVacEditForm').FormPanel.formParams.action;
					vaccineParamsUpd.VacPresence_id = Ext.getCmp('amm_PresenceVacEditForm').FormPanel.formParams.VacPresence_id;
					vaccineParamsUpd.Vaccine_id = Ext.getCmp('VacPresence_VaccineCombo').getValue();
					vaccineParamsUpd.Seria = Ext.getCmp('VacPresence_Seria').getValue();
					vaccineParamsUpd.Period = Ext.getCmp('VacPresence_Period').getValue().format('Y-m-d');
					vaccineParamsUpd.Manufacturer = Ext.getCmp('VacPresence_Manufacturer').getValue();
					if (Ext.getCmp('VacPresence_toHave').getValue())
						vaccineParamsUpd.toHave = 1;
					else
						vaccineParamsUpd.toHave = 0;
							
					//                            vaccineParamsUpd.toHave = Ext.getCmp('VacPresence_toHave').getValue();
					vaccineParamsUpd.pmUser_id = getGlobalOptions().pmuser_id;
					Ext.Ajax.request({
						url: '/?c=Vaccine_List&m=Vac_Presence_save',
						method: 'POST',
						params:  vaccineParamsUpd,
						success: function(response, opts) {
							
														if (vaccineParamsUpd.action == 'add'  ){
//                                                              sw.Promed.vac.utils.consoleLog(response.responseText.rows);
															  if ( response.responseText.length > 0 ) {
																	var result = Ext.util.JSON.decode(response.responseText);
//                                                                     sw.Promed.vac.utils.consoleLog(result.rows[0].NewVacPresence_id);
																	   Ext.getCmp('amm_PresenceVacForm').fireEvent('success', result.rows[0].NewVacPresence_id);
//                                                                    if (!result.success) {
//                                                                        sw.Promed.vac.utils.consoleLog(result.rows.ddd.NewVacPresence_id);
//                                                                         alert(result.rows.ddd.NewVacPresence_id);
//                                                                    }
															  }      
//                                                              alert('123');
//                                                            vaccineParamsUpd.VacPresence_id = vaccineParamsUpd.NewVacPresence_id;
															
														}
														else
															Ext.getCmp('amm_PresenceVacForm').fireEvent('success', vaccineParamsUpd.VacPresence_id);
//							Ext.getCmp('amm_PresenceVacForm').fireEvent('success', vaccineParamsUpd.VacPresence_id);
														form.hide();
						}
								   
					});
				}
			},{
				text: '-'
			},
			//HelpButton(this, TABINDEX_PRESVACEDITFRM + 32),
                        {      text: BTN_FRMHELP,
                                iconCls: 'help16',
                                tabIndex: TABINDEX_PRESVACEDITFRM + 32,
                                handler: function(button, event)
                                {
                                        ShowHelp(this.ownerCt.titleBase);
                                }
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
				onTabAction: function () {
					Ext.getCmp('VacPresence_VaccineCombo').focus();
				}.createDelegate(this),
				
				tabIndex: TABINDEX_PRESVACEDITFRM + 33,
				text: '<u>З</u>акрыть'
			}
			]
				  
		});
				
		sw.Promed.amm_PresenceVacEditForm.superclass.initComponent.apply(this, arguments);
	},
	 
	show: function(vac_params) {

		sw.Promed.amm_PresenceVacEditForm.superclass.show.apply(this, arguments);  
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.vEdiit = true;
		base_form.setValues(vac_params);
		var editForm = Ext.getCmp('amm_PresenceVacEditForm');
		editForm.FormPanel.formParams = vac_params;
		Ext.getCmp('VacPresence_VaccineCombo').store.load({
			callback: function(){
				if (Ext.getCmp('VacPresence_VaccineCombo').getStore().getCount() > 0)
					if (vac_params.action == 'add'  ) {
						editForm.FormPanel.formParams.actType = sw.Promed.vac.cons.actType.ADDING;
						editForm.edit(true);
					} else if (vac_params.action == 'view') {
					  editForm.FormPanel.formParams.actType = sw.Promed.vac.cons.actType.VIEWING;
						Ext.getCmp('VacPresence_VaccineCombo').setValue(vac_params.Vaccine_id);
						editForm.edit(false);
					} else if (vac_params.action == 'edit') {
						editForm.FormPanel.formParams.actType = sw.Promed.vac.cons.actType.EDITING;
						Ext.getCmp('VacPresence_VaccineCombo').setValue (vac_params.Vaccine_id);
						editForm.edit(true);
					}
//        VacPresence_VaccineCombo.setValue(VacPresence_VaccineCombo.getStore().getAt(0).get('VacPresence_id'));
				Ext.getCmp('VacPresence_VaccineCombo').focus(true, 100); //фокус на первый элемент формы
				editForm.fireEvent(//событие "Тип формы определён"
				  'readyFrmType',
					sw.Promed.vac.cons.formType.VAC_AVAILABLE,
					editForm.FormPanel.formParams.actType
				);
			}
		});

	}

});

