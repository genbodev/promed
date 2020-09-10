/**
* swDokCreateUcWindow - окно создания документа передачи. Для остатков медикаментов.
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
* @comment      Префикс для id компонентов dcuw (DokCreateUcWindow)
*               tabIndex (firstTabIndex): 15300+1 .. 15400
*
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документа
*/
/*NO PARSE JSON*/
sw.Promed.swDokCreateUcWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swDokCreateUcWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokCreateUcWindow.js',
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
	height: 382,
	layout: 'form',
	firstTabIndex: 15300,
	id: 'DokCreateUcWindow',
	listeners: {
		hide: function()  {
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
		var form = this.DokCreateUcForm;
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {form.getFirstInvalidEl().focus(true);},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (form.getForm().findField('Contragent_sid').getValue() == form.getForm().findField('Contragent_tid').getValue()) {
			Ext.Msg.alert(lang['oshibka'], lang['poluchatel_ne_doljen_sovpadat_s_postavschikom']);
			return false;
		}
		if (flag==true)
			this.submit();
		return true;
	},
	submit: function(mode,onlySave) {
		var form = this.DokCreateUcForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		form.getForm().submit({
			params: {
				Contragent_sid: form.getForm().findField('Contragent_sid').getValue(),
				Mol_sid: form.getForm().findField('Mol_sid').getValue(),
				Contragent_tid: form.getForm().findField('Contragent_tid').getValue(),
				Mol_tid: form.getForm().findField('Mol_tid').getValue()
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
				if (action.result)  {
					if (action.result.DocumentUc_id) {
						Ext.Ajax.request({
							callback: function(options, success, response) {
								loadMask.hide();
								getWnd('swDokUcLpuEditWindow').show({
									DocumentUc_id: action.result.DocumentUc_id,
									DrugFinance_id: form.findById('dcuwDrugfinance_id').getValue(),
									WhsDocumentCostItemType_id: form.findById('dcuwCostItemType_id').getValue(),
									callback: function() {
										getWnd('swDokUcLpuEditWindow').hide();
										if(getWnd('swDokUcLpuViewWindow').isVisible() && Ext.getCmp('DokUcLpuGridPanel'))
											Ext.getCmp('DokUcLpuGridPanel').refreshRecords(null,0);
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
						sw.swMsg.show( {
							buttons: Ext.Msg.OK,
							fn: function() {
								form.hide();
							},
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
		var value = comboId == 'dcuwContragent_sid' ? this.Contragent_id : combo.getValue();
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
	loadSprMol: function(comboId, contragentId, saveMol) {
		var form = this;
		form.findById(comboId).getStore().load(
		{
			callback: function() 
			{
				form.findById(comboId).setValue(form.findById(comboId).getValue());
				form.setFilterMol(form.findById(comboId), form.findById(contragentId).getValue(), saveMol);
			}
		});
	},
	setFilterMol: function(combo, Contragent_id, saveMol) {
		// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись 
		form = this;
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var co = 0;
		var Mol_id = null;
		var molDateMax = {mol_id: '', begDate: ''};
		
		combo.getStore().filterBy(function(record) {
			if ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0))
			{
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
		if (co==1) {
			combo.setValue(Mol_id);
		}else if(co>1 && molDateMax.begDate){
			//Иначе: МОЛ контрагента Получателя, у которого самая большая дата начала периода действия.
			combo.setValue(molDateMax.mol_id);
		} else {
			combo.setValue(combo.id == 'dcuwMol_sid' ? this.Mol_id : null);
		}
	},
	setContragentCombo: function(c_combo, m_combo) {
		var c_types = isFarmacyInterface ? [2,3,5] : [2,5];
		if (this.action!='view')
			m_combo.setDisabled(!(c_combo.getValue()>0));
		if ((c_combo.getValue()>0) && (c_combo.getFieldValue('ContragentType_Code') && c_combo.getFieldValue('ContragentType_Code').inlist(c_types))){
			m_combo.setAllowBlank(false);
			if (this.action!='view')
				m_combo.enable();
			this.setFilterMol(m_combo, c_combo.getValue());
		} else {
			m_combo.setAllowBlank(true);
			m_combo.setValue(null);
			m_combo.disable();
		}
	},
	setDateValues: function() {
		setCurrentDateTime({
			callback: function(date) {
				// проставить максимальные значения
				var f = this.findById('DokCreateUcForm').getForm();
				f.findField('DocumentUc_setDate').setMaxValue(date);
				f.findField('DocumentUc_DogDate').setMaxValue(date);
				f.findField('DocumentUc_didDate').setMaxValue(date);
			}.createDelegate(this),
			dateField: {},
			setDate: false,
			setTime: false,
			loadMask: false,
			windowId: this
		});
	},	
	show: function() {
		sw.Promed.swDokCreateUcWindow.superclass.show.apply(this, arguments);
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
		form.findById('DokCreateUcForm').getForm().reset();		
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
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

		form.findById('DokCreateUcForm').getForm().setValues(arguments[0]);
		form.findById('dcuwContragent_sid').getStore().baseParams.mode = 'sender';
		form.findById('dcuwContragent_tid').getStore().baseParams.mode = 'receiver';

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});		
		loadMask.show();
		form.findById('dcuwContragent_tid').focus(true, 50);
		
		form.setTitle(lang['formirovanie_dokumenta_ucheta']);
		form.setDateValues();
		
		form.loadContragent('dcuwContragent_sid', {mode:'sender'}, function() {
			this.loadSprMol('dcuwMol_sid','dcuwContragent_sid');
			form.findById('dcuwContragent_sid').disable();
			form.findById('dcuwMol_sid').disable();
		}.createDelegate(form));
		form.loadContragent('dcuwContragent_tid', {mode:'receiver'}, function() {			
			this.loadSprMol('dcuwMol_tid','dcuwContragent_tid');
			loadMask.hide();
		}.createDelegate(form));
		if(form.save_data){
			if(form.save_data[0].DrugFinance_id)
			{
				form.findById('dcuwDrugfinance_id').getStore().load({
					callback: function() {
						form.findById('dcuwDrugfinance_id').setValue(form.save_data[0].DrugFinance_id);
					}
				});
			} else {
				form.findById('dcuwDrugfinance_id').enable();
			}
			if(form.save_data[0].WhsDocumentCostItemType_id)
			{
				form.findById('dcuwCostItemType_id').getStore().load({
					callback: function() {
						form.findById('dcuwCostItemType_id').setValue(form.save_data[0].WhsDocumentCostItemType_id);
					}
				});
			} else {
				form.findById('dcuwCostItemType_id').enable();
			}
		}
	},
	
	initComponent: function() {
		// Форма с полями 
		var form = this;
		
		this.DokCreateUcForm = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokCreateUcForm',
			labelAlign: 'right',
			labelWidth: 134,
			items: 
			[{
				id: 'dcuwDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'DocumentUc_pid',
				value: null,
				xtype: 'hidden'
			}, 
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['postavschik'],
				style: 'padding: 3px; margin-bottom:2px; display:block;',
				labelWidth: 130,
				items:
				[{
					//anchor: '100%',
					width:500,
					fieldLabel: lang['postavschik'],
					allowBlank: false,
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 10,
					id: 'dcuwContragent_sid',
					name: 'Contragent_sid',
					hiddenName:'Contragent_sid',
					listeners: {
						change: function(combo) {
							this.setContragentCombo(combo, this.findById('dcuwMol_sid'));
						}.createDelegate(this)
					}
				},
				{
					allowBlank: false,
					width:500,
					hiddenName: 'Mol_sid',
					fieldLabel: lang['mol_postavschika'],
					id: 'dcuwMol_sid',
					lastQuery: '',
					linkedElements: [ ],
					tabIndex: TABINDEX_DPREW + 11,
					xtype: 'swmolcombo'
				}]
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['poluchatel'],
				style: 'padding: 3px; margin-bottom: 7px; display:block;',
				labelWidth: 130,
				items:
				[{
					width:500,
					allowBlank: false,
					fieldLabel: lang['poluchatel'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 12,
					id: 'dcuwContragent_tid',
					name: 'Contragent_tid',
					hiddenName:'Contragent_tid',
					listeners: {
						change: function(combo) {
							this.setContragentCombo(combo, this.findById('dcuwMol_tid'));
						}.createDelegate(this)
					}
				},
				{
					allowBlank: false,
					width:500,
					fieldLabel: lang['mol_poluchatelya'],
					hiddenName: 'Mol_tid',
					id: 'dcuwMol_tid',
					lastQuery: '',
					linkedElements: [ ],
					tabIndex: TABINDEX_DPREW + 13,
					xtype: 'swmolcombo'
				}]
			}, {
				tabIndex: TABINDEX_DPREW + 14,
				xtype: 'swdrugfinancecombo',
				fieldLabel : 'Источник финанс.',
				name: 'DrugFinance_id',
                id: 'dcuwDrugfinance_id',
				width: 300,
				allowBlank: false,
				disabled: true
			}, {
				tabIndex: TABINDEX_DPREW + 15,
				xtype: 'swwhsdocumentcostitemtypecombo',
				fieldLabel : 'Статья расходов',
				name: 'WhsDocumentCostItemType_id',
				id: 'dcuwCostItemType_id',
				width: 300,
				allowBlank: false,
				disabled: true
			}, {
				layout: 'column',				
				border: false,
				items: 
				[{
					layout: 'form',
					border: false,
					width: 285,
					items: 
					[{
						tabIndex: TABINDEX_DPREW + 16,
						xtype: 'textfield',
						fieldLabel : lang['nomer_dokumenta'],
						name: 'DocumentUc_Num',
						id: 'dcuwDocumentUc_Num',
						allowBlank:false
					}]
				},
				{
					layout: 'form',
					border: false,
					width: 280,
					hidden: true,
					items: 
					[{
						tabIndex: TABINDEX_DPREW + 19,
						xtype: 'textfield',
						fieldLabel : lang['nomer_dogovora'],
						name: 'DocumentUc_DogNum',
						id: 'dcuwDocumentUc_DogNum',
						allowBlank:true
					}]
				}]
			},
			{
				layout: 'column',				
				border: false,
				items: 
				[{
					layout: 'form',
					border: false,
					width: 280,
					items: 
					[{
						fieldLabel : lang['data_podpisaniya'],
						tabIndex: TABINDEX_DPREW + 17,
						allowBlank: false,
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'DocumentUc_setDate',
						id: 'dcuwDocumentUc_setDate'
					}]
				},
				{
					layout: 'form',
					border: false,
					width: 280,
					hidden: true,
					items: 
					[{
						fieldLabel : lang['data_dogovora'],
						tabIndex: TABINDEX_DPREW + 20,
						allowBlank: true,
						xtype: 'swdatefield',
						format: 'd.m.Y',						
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'DocumentUc_DogDate',
						id: 'dcuwDocumentUc_DogDate'
					}]
				}]
			},
			{
				fieldLabel : lang['data_postavki'],
				tabIndex: TABINDEX_DPREW + 18,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_didDate',
				id: 'dcuwDocumentUc_didDate'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e)  {
					switch (e.getKey()) {
						case Ext.EventObject.C:
							if (this.action != 'view') {
								this.doSave(false);
							}
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
			reader: new Ext.data.JsonReader( {
				success: function() { }
			}, 
			[
				{ name: 'DocumentUc_id' },
				{ name: 'DocumentUc_pid' },
				{ name: 'Contragent_sid' },
				{ name: 'Mol_sid' },
				{ name: 'Contragent_tid' },
				{ name: 'Mol_tid' },
				{ name: 'DocumentUc_Num' },
				{ name: 'DocumentUc_setDate' },
				{ name: 'DocumentUc_didDate' },
				{ name: 'DocumentUc_DogNum' },
				{ name: 'DocumentUc_DogDate' }
			]),
			url: '/?c=Farmacy&m=save&method=DokUcLpu'
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
				id: 'dcuwOk',
				iconCls: 'save16',
				tabIndex: TABINDEX_DPREW + 22,
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
				tabIndex: TABINDEX_DPREW + 23,
				text: BTN_FRMCANCEL
			}],
			items: [{
				border: false,
				region: 'center',
				layout: 'fit',
				items: [form.DokCreateUcForm]
			}]
		});
		sw.Promed.swDokCreateUcWindow.superclass.initComponent.apply(this, arguments);		
	}
	});