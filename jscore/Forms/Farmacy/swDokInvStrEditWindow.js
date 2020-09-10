/**
* swDokInvStrEditWindow - окно редактирования строки инвентаризационной ведомости.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      
* @comment      Префикс для id компонентов disew (DokInvStrEditWindow)
*               tabIndex (firstTabIndex): 15300+1 .. 15400
*/
/*NO PARSE JSON*/
sw.Promed.swDokInvStrEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	codeRefresh: true,
	objectName: 'swDokInvStrEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokInvStrEditWindow.js',
	action: 'add',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: false,
	maximizable: false,
	split: true,
	width: 700,
	height: 446,
	layout: 'form',
	firstTabIndex: 15300,
	id: 'DokInvStrEditWindow',
	listeners: {
		hide: function() {
			//this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	save_data: null,
	Contragent_id: null,
	Mol_id: null,
	doSave: function(flag) {
		var form = this.DokInvStrEditForm;
		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (flag==true) this.submit();
		return true;
	},
	submit: function(mode,onlySave) {
		var form = this.DokInvStrEditForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();		
		form.getForm().submit({
			params: {
				DocumentUcStr_id: win.DocumentUcStr_id,
				DocumentUcStr_Sum: form.findById('disewDocumentUcStr_Sum').getValue()
			},
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				win.hide();
				var grid = Ext.getCmp('DokInvEditGrid');
				if (grid)
					grid.refreshRecords(null,0);
			}
		});
	},
	enableEdit: function(enable) {
		var form = this;
		if (enable) {
			form.findById('disewDocumentUcStr_Count').enable();
			form.findById('disewDocumentUcStr_EdCount').enable();
			form.buttons[0].enable();
		} else {
			form.findById('disewDocumentUcStr_Count').disable();
			form.findById('disewDocumentUcStr_EdCount').disable();
			form.buttons[0].disable();			
		}
	},
	show: function() {
		sw.Promed.swDokInvStrEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka']
			});
		}
		form.focus();
		form.findById('DokInvStrEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		
		if (arguments[0].action) 
			form.action = arguments[0].action;
			
		if (arguments[0].DocumentUcStr_id) 
			form.DocumentUcStr_id = arguments[0].DocumentUcStr_id;
		else 
			form.DocumentUcStr_id = null;

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		if (form.action == 'edit') {
			form.setTitle(lang['stroka_inventarizatsionnoy_vedomosti_redaktirovanie']);
		} else if (form.action == 'view') {
			form.setTitle(lang['stroka_inventarizatsionnoy_vedomosti_prosmotr']);
		}

		form.DokInvStrEditForm.getForm().load({
			params: {
				DocumentUcStr_id: form.DocumentUcStr_id
			},
			failure: function() {
				loadMask.hide();
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						form.hide();
					},
					icon: Ext.Msg.ERROR,
					msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
					title: lang['oshibka']
				});
			},
			success: function() {
				loadMask.hide();				
				form.enableEdit(form.action=='edit');
				/*var fld = form.findById('disewDocumentUcStr_Count');
				fld.fireEvent('change', fld, fld.getValue(), 0);*/
				var fld = form.findById('disewDocumentUcStr_OstCount');
				fld.fireEvent('change', fld, fld.getValue(), 0);
			},
			url: '/?c=Farmacy&m=loadDocumentInvStrView'
		});	

		//form.findById('disewDocumentUc_Num').focus(true, 50);
	},
	
	initComponent: function() {
		// Форма с полями 
		var form = this;		
		this.DokInvStrEditForm = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokInvStrEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[{
				id: 'disewDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}/*, {
				tabIndex: form.firstTabIndex + 3,
				xtype: 'textfield',
				fieldLabel : lang['nomer_akta'],
				name: 'DocumentUc_Num',
				id: 'disewDocumentUc_Num',
				disabled: true
			}*/, {
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: 175,
				title: lang['partiya_uchetnyie_dannyie'],
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items: [{					
					xtype: 'textfield',
					fieldLabel : lang['kontragent'],
					name: 'Contragent_Name',
					width: 450,
					disabled: true
				}, {					
					xtype: 'textfield',
					fieldLabel : lang['mol'],
					name: 'Mol_Name',
					width: 450,
					disabled: true
				}, {					
					xtype: 'textfield',
					fieldLabel : lang['istochnik_finansirovaniya'],
					name: 'DrugFinance_Name',
					width: 450,
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel : lang['statya_rashoda'],
					name: 'WhsDocumentCostItemType_Name',
					width: 450,
					disabled: true
				}, {
					xtype: 'textfield',
					fieldLabel : lang['medikament'],
					name: 'Drug_CodeName',
					width: 450,
					disabled: true
				}, {
					layout: 'column',				
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 330,
						items: [{					
							xtype: 'numberfield',
							fieldLabel : lang['kolichestvo_ed_uch'],
							id: 'disewDocumentUcStr_OstCount',
							name: 'DocumentUcStr_OstCount',
							width: 150,
							disabled: true,
							listeners: {
								'change': function(field, newValue, oldValue) {
									if (newValue < 0)
										return false;
									var price = Ext.getCmp('disewprice').getValue() > 0 ? Ext.getCmp('disewprice').getValue() : 0;
									Ext.getCmp('disewostsum').setValue(newValue * price);
								}
							}							
						}]
					}, {
						layout: 'form',
						border: false,
						width: 300,
						labelWidth: 145,
						items: [{					
							xtype: 'textfield',
							fieldLabel : lang['edinitsa_ucheta'],
							name: 'Drug_CodeName',
							width: 150,
							disabled: true
						}]
					}]
				}, {					
					xtype: 'numberfield',
					fieldLabel : lang['kol-vo_v_upakovke'],
					id: 'disewDrug_Fas',
					name: 'Drug_Fas',
					width: 150,
					disabled: true
				}, {
					layout: 'column',				
					border: false,					
					items: [{
						layout: 'form',
						border: false,
						width: 330,
						items: [{					
							xtype: 'numberfield',
							fieldLabel : lang['kolichestvo_ed_doz'],
							id: 'disewDocumentUcStr_OstEdCount',
							name: 'DocumentUcStr_OstEdCount',
							width: 150,
							disabled: true
						}]
					}, {
						layout: 'form',
						border: false,
						width: 300,
						labelWidth: 145,
						items: [{					
							xtype: 'textfield',
							fieldLabel : lang['edinitsa_dozirovki'],
							name: 'nn',
							width: 150,
							disabled: true
						}]
					}]
				}, {
					layout: 'column',				
					border: false,					
					items: [{
						layout: 'form',
						border: false,
						width: 330,
						items: [{					
							xtype: 'numberfield',
							fieldLabel : lang['tsena'],
							id: 'disewprice',
							name: 'price',
							width: 150,
							disabled: true
						}]
					}, {
						layout: 'form',
						border: false,
						width: 300,
						labelWidth: 145,
						items: [{					
							xtype: 'numberfield',
							fieldLabel : lang['summa'],
							id: 'disewostsum',
							name: 'ostsum',
							width: 150,
							disabled: true
						}]
					}]
				}, {
					layout: 'column',				
					border: false,					
					items: [{
						layout: 'form',
						border: false,
						width: 330,
						items: [{					
							xtype: 'textfield',
							fieldLabel : lang['seriya'],
							name: 'DocumentUcStr_Ser',
							width: 150,
							disabled: true
						}]
					}, {
						layout: 'form',
						border: false,
						width: 300,
						labelWidth: 145,
						items: [{					
							xtype: 'datefield',
							fieldLabel : lang['dogovor'],
							name: 'DocumentUc_InvDate',
							format: 'd.m.Y',
							width: 150,
							disabled: true
						}]
					}]
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: 175,
				title: lang['fakticheskoe_nalichie'],
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items: [{
					layout: 'column',				
					border: false,
					items: [{
						layout: 'form',
						border: false,
						width: 330,
						items: [{					
							xtype: 'numberfield',
							fieldLabel : lang['kolichestvo_ed_uch'],
							id: 'disewDocumentUcStr_Count',
							name: 'DocumentUcStr_Count',
							width: 150,
							disabled: false,
							listeners: {
								'change': function(field, newValue, oldValue) {
									if (newValue < 0)
										return false;
									var ost = Ext.getCmp('disewDocumentUcStr_OstCount').getValue() > 0 ? Ext.getCmp('disewDocumentUcStr_OstCount').getValue() : 0;
									var fas = Ext.getCmp('disewDrug_Fas').getValue() > 0 ? Ext.getCmp('disewDrug_Fas').getValue() : 0;
									var price = Ext.getCmp('disewprice').getValue() > 0 ? Ext.getCmp('disewprice').getValue() : 0;
									var balance = lang['norma'];
									if (newValue < ost) balance = lang['nedostacha'];
									if (newValue > ost) balance = lang['izbyitok'];
									
									Ext.getCmp('disewDocumentUcStr_EdCount').setValue(newValue * fas);									
									Ext.getCmp('disewDocumentUcStr_Sum').setValue(newValue * price);									
									Ext.getCmp('disewbalance').setValue(balance);									
								}
							}
						}]
					}, {
						layout: 'form',
						border: false,
						width: 300,
						labelWidth: 145,
						items: [{					
							xtype: 'textfield',
							fieldLabel : lang['edinitsa_ucheta'],
							name: 'Drug_CodeName',
							width: 150,
							disabled: true
						}]
					}]
				}, {
					layout: 'column',				
					border: false,					
					items: [{
						layout: 'form',
						border: false,
						width: 330,
						items: [{					
							xtype: 'numberfield',
							fieldLabel : lang['kolichestvo_ed_doz'],
							id: 'disewDocumentUcStr_EdCount',
							name: 'DocumentUcStr_EdCount',
							width: 150,
							disabled: false,
							listeners: {
								'change': function(field, newValue, oldValue) {
									if (newValue < 0)
										return false;
									var ost = Ext.getCmp('disewDocumentUcStr_OstEdCount').getValue() > 0 ? Ext.getCmp('disewDocumentUcStr_OstEdCount').getValue() : 0;
									var fas = Ext.getCmp('disewDrug_Fas').getValue() > 0 ? Ext.getCmp('disewDrug_Fas').getValue() : 0;
									var price = Ext.getCmp('disewprice').getValue() > 0 ? Ext.getCmp('disewprice').getValue() : 0;
									var balance = lang['norma'];
									if (newValue < ost) balance = lang['nedostacha'];
									if (newValue > ost) balance = lang['izbyitok'];
									
									Ext.getCmp('disewDocumentUcStr_Count').setValue(fas > 0 ? newValue/fas : 0);
									Ext.getCmp('disewDocumentUcStr_Sum').setValue(Ext.getCmp('disewDocumentUcStr_Count').getValue() * price);
									Ext.getCmp('disewbalance').setValue(balance);
								}
							}
						}]
					}, {
						layout: 'form',
						border: false,
						width: 300,
						labelWidth: 145,
						items: [{					
							xtype: 'textfield',
							fieldLabel : lang['edinitsa_dozirovki'],
							name: 'Drug_CodeName',
							width: 150,
							disabled: true
						}]
					}]
				}, {					
					xtype: 'numberfield',
					fieldLabel : lang['summa'],
					id: 'disewDocumentUcStr_Sum',
					name: 'DocumentUcStr_Sum',
					width: 150,
					disabled: true
				}, {					
					xtype: 'textfield',
					fieldLabel : lang['priznak_otkloneniya'],
					id: 'disewbalance',
					name: 'balance',
					width: 150,
					disabled: true
				}]
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey()) {
						case Ext.EventObject.C:
							this.doSave(false);
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, 
			[
				{ name: 'Contragent_Name' },
				{ name: 'Mol_Name' },
				{ name: 'DrugFinance_Name' },
				{ name: 'WhsDocumentCostItemType_Name' },
				{ name: 'Drug_CodeName' },
				{ name: 'Drug_Fas' },				
				{ name: 'DocumentUcStr_OstCount' },
				{ name: 'DocumentUcStr_OstEdCount' },
				{ name: 'DocumentUcStr_Count' },
				{ name: 'DocumentUcStr_EdCount' },
				{ name: 'DocumentUc_InvDate' },
				{ name: 'DocumentUcStr_Ser' },
				{ name: 'price' },				
				{ name: 'DocumentUcStr_Sum' },
				{ name: 'ostsum' },
				{ name: 'balance' }
			]),
			url: '/?c=Farmacy&m=saveDocumentInvUcStr'
		});
		Ext.apply(this, 
		{
			border: false,
			xtype: 'panel',
			region: 'center',
			layout:'border',			
			buttons: 
			[{
				handler: function() {
					this.ownerCt.doSave(true);
				},
				iconCls: 'save16',
				tabIndex: form.firstTabIndex + 6,
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: form.firstTabIndex + 7,
				text: BTN_FRMCANCEL
			}],
			items: [{
				border: false,
				region: 'center',
				layout: 'fit',
				items: [form.DokInvStrEditForm]
			}]
		});
		sw.Promed.swDokInvStrEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});