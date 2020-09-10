/**
* swDokCreateSpisWindow - окно создания акта списания медикаментов. Для остатков медикаментов.
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
* @comment      Префикс для id компонентов dcsw (DokCreateSpisWindow)
*               tabIndex (firstTabIndex): 15300+1 .. 15400
*
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документа
*/
/*NO PARSE JSON*/
sw.Promed.swDokCreateSpisWindow = Ext.extend(sw.Promed.BaseForm, 
{
	codeRefresh: true,
	objectName: 'swDokCreateSpisWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokCreateSpisWindow.js',
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
	height: 295,
	layout: 'form',
	firstTabIndex: 15300,
	id: 'DokCreateSpisWindow',
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
		var form = this.DokCreateSpisForm;
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
		var form = this.DokCreateSpisForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();		
		form.getForm().submit({
			params: {
				Contragent_sid: form.findById('dcswContragent_sid').getValue(),
				Mol_sid: form.findById('dcswMol_sid').getValue()
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
				if (action.result) {
					if (action.result.DocumentUc_id) {
						Ext.Ajax.request({
							callback: function(options, success, response) {
								loadMask.hide();
								getWnd('swDokSpisEditWindow').show({
									DocumentUc_id: action.result.DocumentUc_id,
									DrugFinance_id: form.findById('dcswDrugfinance_id').getValue(),
									WhsDocumentCostItemType_id: form.findById('dcswCostItemType_id').getValue(),
									callback: function() {
										getWnd('swDokSpisEditWindow').hide();
										if(getWnd('swDokSpisViewWindow').isVisible() && Ext.getCmp('DokSpisGridPanel'))
											Ext.getCmp('DokSpisGridPanel').refreshRecords(null,0);
									}
								});
								win.callback(win.owner, action.result.DocumentUc_id);								
								win.hide();
							}.createDelegate(this),
							params: {
								DocumentUc_id: action.result.DocumentUc_id,
								save_data: Ext.util.JSON.encode(win.save_data)
							},
							url: '/?c=FarmacyDrugOstat&m=saveDocumentUcStrFromArray'
						});
					} else {
						loadMask.hide();
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() { form.hide(); },
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	loadContragent: function(comboId, params, callback) {
		var combo = this.findById(comboId);
		var value = this.Contragent_id;//combo.getValue(); //подгружаем контрагент из параметров окна
		combo.getStore().load({
			params: params,
			callback: function() {
				combo.setValue(value);
				combo.fireEvent('change', combo);
				if (callback) {
					callback();
				}
			}.createDelegate(this)
		});
	},
	loadSprMol: function(comboId, contragentId, saveMol) { //saveMol - сохранить ли значение в поле Мол, выставлять в true при начальной загрузке данных при редактировании
		var form = this;
		form.findById(comboId).getStore().load( {
			callback: function() {
				form.findById(comboId).setValue(form.findById(comboId).getValue());
				form.setFilterMol(form.findById(comboId), form.findById(contragentId).getValue(), saveMol);
			}
		});
	},
	setFilterMol: function(combo, Contragent_id, saveMol) {
		form = this;
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var co = 0;
		var molDateMax = {mol_id: '', begDate: ''};
		var Mol_id = null;
		combo.getStore().filterBy(function(record) {
			if ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0)) {
				co++;
				Mol_id = record.get('Mol_id');
				var molBegDate = record.get('Mol_begDT');
				//отберем id с большим значением даты, его и подставим
				if(molBegDate) {
					molBegDate = new Date(molBegDate);
					if(molDateMax.begDate){
						if(molDateMax.begDate < molBegDate){
							molDateMax.begDate = molBegDate;
							molDateMax.mol_id = Mol_id;
						}
					}else{
						molDateMax.begDate = molBegDate;
						molDateMax.mol_id = Mol_id;
					}
				}
			}
			return ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0));
		});
		if(co>1){
			Mol_id = (molDateMax.mol_id) ? molDateMax.mol_id : Mol_id;
			combo.setValue(Mol_id);
			combo.enable();
		}else if(co==1) {
			// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись 
			combo.setValue(Mol_id);
		} else {
			combo.setValue(form.Mol_id);			
		}
	},
	show: function() {
		sw.Promed.swDokCreateSpisWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.focus();
		form.findById('DokCreateSpisForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].DocumentUc_id) 
			form.DocumentUc_id = arguments[0].DocumentUc_id;
		else 
			form.DocumentUc_id = null;
			
		if (arguments[0].callback) {
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) {
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) {
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].save_data) {
			form.save_data = arguments[0].save_data;
		}
		if (arguments[0].Mol_id) {
			form.Mol_id = arguments[0].Mol_id;
		}
		if (arguments[0].Contragent_id) {
			form.Contragent_id = arguments[0].Contragent_id;
		}

		form.findById('DokCreateSpisForm').getForm().setValues(arguments[0]);
		form.findById('dcswContragent_sid').getStore().baseParams.mode = 'sender';

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		form.setTitle(lang['formirovanie_akta_spisaniya']);
		loadMask.hide();
		form.findById('dcswDocumentUc_Num').focus(true, 50);
		
		form.loadContragent('dcswContragent_sid', null/*{mode:'sender'}*/, function() {
			this.loadSprMol('dcswMol_sid','dcswContragent_sid');
			form.findById('dcswContragent_sid').disable();
			form.findById('dcswMol_sid').disable();
			loadMask.hide();
		}.createDelegate(form));

		if(form.save_data){
			if(form.save_data[0].DrugFinance_id)
			{
				form.findById('dcswDrugfinance_id').getStore().load({
					callback: function() {
						form.findById('dcswDrugfinance_id').setValue(form.save_data[0].DrugFinance_id);
					}
				});
			} else {
				form.findById('dcswDrugfinance_id').enable();
			}
			if(form.save_data[0].WhsDocumentCostItemType_id)
			{
				form.findById('dcswCostItemType_id').getStore().load({
					callback: function() {
						form.findById('dcswCostItemType_id').setValue(form.save_data[0].WhsDocumentCostItemType_id);
					}
				});
			} else {
				form.findById('dcswCostItemType_id').enable();
			}
		}
	},
	
	initComponent: function() {
		// Форма с полями 
		var form = this;		
		this.DokCreateSpisForm = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokCreateSpisForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[{
				id: 'dcswDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}, {
				id: 'dcswContragent_id',
				name: 'Contragent_id',
				value: null,
				xtype: 'hidden'
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: 125,
				title: lang['kontragent'],
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items: [{
					width:500,
					allowBlank: false,
					fieldLabel: lang['postavschik'],
					xtype: 'swcontragentcombo',
					tabIndex: form.firstTabIndex + 1,
					id: 'dcswContragent_sid',
					name: 'Contragent_sid',
					hiddenName:'Contragent_sid',
					listeners: {
						change: function(combo) {
							this.findById('dcswMol_sid').setDisabled(!(combo.getValue()>0));							
							if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
								this.findById('dcswMol_sid').setAllowBlank(false);
								this.findById('dcswMol_sid').enable();
								this.setFilterMol(this.findById('dcswMol_sid'), combo.getValue());
							} else {
								this.findById('dcswMol_sid').disable();
								this.findById('dcswMol_sid').setAllowBlank(true);
								this.findById('dcswMol_sid').setValue(null);
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					width:500,
					fieldLabel: lang['mol_postavschika'],
					hiddenName: 'Mol_sid',
					id: 'dcswMol_sid',
					lastQuery: '',
					linkedElements: [ ],
					tabIndex: form.firstTabIndex + 2,
					xtype: 'swmolcombo'
				}]
			}, {
				tabIndex: form.firstTabIndex + 3,
				xtype: 'swdrugfinancecombo',
				fieldLabel : 'Источник финанс.',
				name: 'DrugFinance_id',
                id: 'dcswDrugfinance_id',
				width: 300,
				allowBlank: false,
				disabled: true
			}, {
				tabIndex: form.firstTabIndex + 4,
				xtype: 'swwhsdocumentcostitemtypecombo',
				fieldLabel : 'Статья расходов',
				name: 'WhsDocumentCostItemType_id',
				id: 'dcswCostItemType_id',
				width: 300,
				allowBlank: false,
				disabled: true
			}, {
				tabIndex: form.firstTabIndex + 5,
				xtype: 'textfield',
				fieldLabel : lang['nomer_akta'],
				name: 'DocumentUc_Num',
				id: 'dcswDocumentUc_Num',
				allowBlank:false
			}, {
				fieldLabel : lang['data_podpisaniya'],
				tabIndex: form.firstTabIndex + 6,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_setDate',
				id: 'dcswDocumentUc_setDate'
			}, {
				fieldLabel : lang['data_spisaniya'],
				tabIndex: form.firstTabIndex + 7,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_didDate',
				id: 'dcswDocumentUc_didDate'
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
			reader: new Ext.data.JsonReader(
			{
				success: function() { }
			}, 
			[
				{ name: 'Contragent_sid' },
				{ name: 'Mol_sid' },
				{ name: 'DocumentUc_id' },
				{ name: 'DocumentUc_Num' },
				{ name: 'DocumentUc_setDate' },
				{ name: 'DocumentUc_didDate' },
				{ name: 'DrugFinance_id' }
			]),
			url: '/?c=Farmacy&m=save&method=DokSpis'
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
				tabIndex: form.firstTabIndex + 8,
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
				tabIndex: form.firstTabIndex + 9,
				text: BTN_FRMCANCEL
			}],
			items: [{
				border: false,
				region: 'center',
				layout: 'fit',
				items: [form.DokCreateSpisForm]
			}]
		});
		sw.Promed.swDokCreateSpisWindow.superclass.initComponent.apply(this, arguments);
	}
	});