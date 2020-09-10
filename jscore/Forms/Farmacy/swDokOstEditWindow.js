/**
* swDokOstEditWindow - окно редактирования/добавления документа ввода остатков.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      
* @comment      Префикс для id компонентов doew (DokOstEditWindow)
*               tabIndex (firstTabIndex): 15300+1 .. 15400
*
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документа
*/
/*NO PARSE JSON*/

sw.Promed.swDokOstEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	maximizable: false,
	split: true,
	width: 700,
	height: 500,
	layout: 'form',
	firstTabIndex: 15300,
	codeRefresh: true,
	objectName: 'swDokOstEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokOstEditWindow.js',
	id: 'DokOstEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.callback(this.owner, -1);
		},
		beforeshow: function()
		{
			// Никого не жалко, никого!!!
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	filterFinCombo: function(date) { //фильтрация справочников "источник финансирования" и "статья расходов"
        var tf = Ext.getCmp('DokOstEditForm');
		var base_form = this.DokOstEditForm.getForm();
        var DrugFinance = base_form.findField('DrugFinance_id').getValue();

		if (Ext.isEmpty(date)) {
			date = base_form.findField('DocumentUc_setDate').getValue();
		}
		if (!Ext.isEmpty(date)) {
			date = date.format('d.m.Y');
		}

		base_form.findField('DrugFinance_id').setDateFilter({Date: date});
		base_form.findField('WhsDocumentCostItemType_id').setDateFilter({Date: date});

        if (!Ext.isEmpty(DrugFinance)) {
            var index = tf.findById('dprewDrugfinance_id').getStore().findBy(function(record) {
                return (record.get('DrugFinance_id') == DrugFinance);
            });

            if ( index == -1 ) {
                DrugFinance = null;
            }
        }

        if (Ext.isEmpty(DrugFinance) && tf.findById('dprewDrugfinance_id').getStore().getCount() > 0) {
            DrugFinance = '';
        }

       // tf.findById('dprewDrugfinance_id').setValue(DrugFinance);
	},
	doSave: function(flag) 
	{
		var form = this.DokOstEditForm;
		if (!form.getForm().isValid()) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (flag==true) form.ownerCt.submit();
		return true;
	},
	submit: function(mode,onlySave) 
	{
		var form = this.DokOstEditForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
		{
			params: 
			{	
				WhsDocumentCostItemType_id:form.getForm().findField('dprefCostItemType_id').getValue(),
				DrugFinance_id:form.getForm().findField('dprewDrugfinance_id').getValue(),
				Contragent_tid: form.findById('doewContragent_tid').getValue(),
				Mol_tid: form.findById('doewMol_tid').getValue()
			},
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.DocumentUc_id)
					{
						//log(form.getForm().getValues());
						if (!onlySave || (onlySave!==1))
						{
							win.hide();
							win.callback(win.owner, action.result.DocumentUc_id);
						}
						else
						{
							new Ext.ux.window.MessageWindow(
							{
								title: lang['sohranenie'],
								autoHeight: true,
								help: false,
								bodyStyle: 'text-align:center',
								closable: true,
								hideFx:
								{
									delay: 3000,
									mode: 'standard',
									useProxy: false
								},
								html: lang['dannyie_o_dokumente_vvoda_ostatkov_sohranenyi'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							if (!form.findById('doewDocumentUc_id').getValue())
							{
								form.findById('doewDocumentUc_id').setValue(action.result.DocumentUc_id);
								//log(action.result.DocumentUc_id);
								win.DocumentUcStrPanel.params =
								{
									DocumentUc_id: form.findById('doewDocumentUc_id').getValue(),
									Contragent_id: form.findById('doewContragent_tid').getValue(),
									DrugDocumentType_Code: 3,
									mode: 'income',
									DrugFinance_id: form.getForm().findField('DrugFinance_id').getValue(),
									WhsDocumentCostItemType_id: form.getForm().findField('WhsDocumentCostItemType_id').getValue()
								};
								win.DocumentUcStrPanel.gFilters =
								{
									DocumentUc_id: form.findById('doewDocumentUc_id').getValue()
								};
								if (mode=='add')
								{
									win.DocumentUcStrPanel.run_function_add = false;
									win.DocumentUcStrPanel.ViewActions.action_add.execute();
								}
							}
							else
							{
								if (mode=='add')
								{
									win.DocumentUcStrPanel.run_function_add = false;
									win.DocumentUcStrPanel.ViewActions.action_add.execute();
								}
								else if (mode=='edit')
								{
									win.DocumentUcStrPanel.run_function_edit = false;
									win.DocumentUcStrPanel.ViewActions.action_edit.execute();
								}
							}
						}
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
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
		var value = combo.getValue();
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
		// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись 
		form = this;
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var co = 0;
		var Mol_id = null;
		combo.getStore().filterBy(function(record) {
			if ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0)) {
				co++;
				Mol_id = record.get('Mol_id');
			}
			return ((Contragent_id==record.get('Contragent_id')) && (Contragent_id>0));
		});
		if (co==1) {
			combo.setValue(Mol_id);
		} else {
			if (!saveMol) {
				combo.setValue(null);
			} else {
				//если контрагент первоначальный, восстаноавливаем первоначальный Мол
				if (Ext.getCmp('DokOstEditForm').reader.jsonData[0].Contragent_tid == Contragent_id && Contragent_id > 0)
					combo.setValue(Ext.getCmp('DokOstEditForm').reader.jsonData[0].Mol_tid);
			}
		}
	},	
	enableEdit: function(enable) 
	{
		var form = this;
		var base_form = this.findById('DokOstEditForm').getForm();

		if (enable) {
			form.findById('doewDocumentUc_Num').enable();
			form.findById('doewDocumentUc_setDate').enable();
			form.findById('doewDocumentUc_didDate').enable();
			base_form.findField('DrugFinance_id').enable();
			base_form.findField('WhsDocumentCostItemType_id').enable();
			form.DocumentUcStrPanel.setReadOnly(false);
			form.buttons[0].enable();
		} else {
			form.findById('doewDocumentUc_Num').disable();
			form.findById('doewDocumentUc_setDate').disable();
			form.findById('doewDocumentUc_didDate').disable();
			base_form.findField('DrugFinance_id').disable();
			base_form.findField('WhsDocumentCostItemType_id').disable();
			form.DocumentUcStrPanel.setReadOnly(true);
			form.buttons[0].disable();
		}
	},
	/*
	openDocumentUcStrEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('DokOstEditForm').getForm();
		var grid = this.DocumentUcStrPanel;

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swDocumentUcStrEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_stroki_uchetnogo_dokumenta_uje_otkryito']);
			return false;
		}

//		if ( action == 'add' && base_form.findField('DocumentUc_id').getValue() == 0 ) {
//			this.doSave({
//				openChildWindow: function() {
//					this.openDocumentUcStrEditWindow(action);
//				}.createDelegate(this)
//			});
//			return false;
//		}

		getWnd('swDocumentUcStrEditWindow').show({
			action: action,
			formParams: {
				DocumentUc_id: base_form.findField('DocumentUc_id').getValue()
			},
			mode: 'income'
		})
	},
	*/
	show: function() {
		sw.Promed.swDokOstEditWindow.superclass.show.apply(this, arguments);
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
		form.findById('DokOstEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.Contragent_tid = null;
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
		if (arguments[0].action) {
			form.action = arguments[0].action;
		} else {
			if ((form.DocumentUc_id) && (form.DocumentUc_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
			form.action = 'view';
		if (arguments[0].Contragent_tid) {
			form.Contragent_tid = arguments[0].Contragent_tid;
		}
		form.findById('DokOstEditForm').getForm().setValues(arguments[0]);
		form.findById('doewContragent_tid').getStore().baseParams.mode = 'self_lpu';

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) 
		{
			case 'add':
				form.setTitle(lang['dokument_vvoda_ostatkov_dobavlenie']);
				form.enableEdit(true);
				loadMask.hide();
				//form.getForm().clearInvalid();
				form.findById('doewDocumentUc_Num').focus(true, 50);
				form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:null}, globalFilters:{DocumentUc_id:null, mode: 'income'}, noFocusOnLoad: true});
				form.filterFinCombo();
				break;
			case 'edit':
				form.setTitle(lang['dokument_vvoda_ostatkov_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['dokument_vvoda_ostatkov_prosmotr']);
				break;
		}

		if (form.action!='add')
		{
			form.findById('DokOstEditForm').getForm().load(
			{
				params: 
				{
					DocumentUc_id: form.DocumentUc_id
				},
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					loadMask.hide();
					form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:form.findById('doewDocumentUc_id').getValue()}, globalFilters:{DocumentUc_id:form.findById('doewDocumentUc_id').getValue(), mode: 'income'}, noFocusOnLoad:true});
					
					form.loadContragent('doewContragent_tid', null/*{mode:'self_lpu'}*/, function() {
						this.loadSprMol('doewMol_tid','doewContragent_tid',true);
						loadMask.hide();
					}.createDelegate(form));
					
					if (form.action=='edit') {
						form.enableEdit(true);
						form.findById('doewDocumentUc_Num').focus(true, 50);
					} else  {
						form.focus();
						form.enableEdit(false);
					}

					form.filterFinCombo();
				},
				url: '/?c=Farmacy&m=edit&method=DokOst'
			});
		} else {
			form.loadContragent('doewContragent_tid', null/*{mode:'self_lpu'}*/, function() {
				this.loadSprMol('doewMol_tid','doewContragent_tid');
				loadMask.hide();
			}.createDelegate(form));
			//form.findById('doewDrugFinance_id').disable();
			//form.findById('doewDrugFinance_id').setValue(getGlobalOptions().FarmacyOtdel_id);
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		this.MainRecordAdd = function() {
			var tf = Ext.getCmp('DokOstEditWindow');
			var base_form = tf.DokOstEditForm.getForm();
			if(base_form.findField('DrugFinance_id').getValue()!=''&&base_form.findField('WhsDocumentCostItemType_id').getValue()!=''){
			if (tf.findById('doewDocumentUc_id').getValue()>0) {
				tf.DocumentUcStrPanel.params = {
					DocumentUc_id: tf.findById('doewDocumentUc_id').getValue(),
					Contragent_id: tf.findById('doewContragent_tid').getValue(),
					DrugDocumentType_Code: 3,
					mode: 'income',
					DrugFinance_id: base_form.findField('DrugFinance_id').getValue(),
					WhsDocumentCostItemType_id: base_form.findField('WhsDocumentCostItemType_id').getValue()
				};
				tf.DocumentUcStrPanel.gFilters = {
					DocumentUc_id: tf.findById('doewDocumentUc_id').getValue()
				};
				tf.DocumentUcStrPanel.run_function_add = false;
				tf.DocumentUcStrPanel.ViewActions.action_add.execute();
			} else {
				if (tf.doSave()) {
					tf.submit('add',1);
				}
				return false;
			}}else return false;
		}		
		this.MainRecordEdit = function() {
			var tf = Ext.getCmp('DokOstEditWindow');
			if (tf.doSave()) {
				tf.submit('edit',1);
			}
			return false;
		}
		
		/*
		this.AddRecord = function ()
		{
			var f = Ext.getCmp('DokOstEditWindow');
			var tf = f.DocumentUcStrPanel;
			// Можно добавить проверку на то , сохранен документ ранее или нет
			//if form.findById('doewDocumentUc_id').getValue()
			if (tf.function_action_add && ((tf.run_function_add==undefined) || tf.run_function_add))
			{
				if (!tf.function_action_add())
					return;
			}
			else
			{
				tf.run_function_add = undefined;
			}
			return false;
		}
		*/
		
		// в зависимости от выбранного интерфейса
		// постоянные поля 
		var sf = [
			{name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true},
			{name: 'DocumentUc_id', hidden: true, isparams: true}
		];
		if (isFarmacyInterface)
		{
			sf.push({name: 'DocumentUcStr_NZU', width: 60, header: lang['nzu']});
			sf.push({name: 'Drug_Name', id: 'autoexpand', header: lang['naimenovanie']});
			sf.push({name: 'DocumentUcStr_Count', width: 80, header: lang['kol-vo'], type: 'float'});
			sf.push({name: 'DocumentUcStr_Price', width: 80, header: lang['tsena_opt_bez_nds'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_Sum', width: 100, header: lang['summa_opt_bez_nds'], type: 'money', align: 'right'});
			//sf.push({name: 'DocumentUcStr_SumNds', width: 110, header: 'в т.ч. НДС', type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_PriceR', width: 100, header: lang['tsena_rozn_s_nds'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_SumR', width: 110, header: lang['summa_rozn_s_nds'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_Nds', width: 70, header: lang['nds_%'], type: 'string', align: 'left'});
			//sf.push({name: 'DocumentUcStr_SumNdsR', width: 110, header: 'НДС (розница)', type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_Ser', width: 60, header: lang['seriya']});
			sf.push({name: 'DocumentUcStr_godnDate', width: 110, header: lang['srok_godnosti'], type: 'date'});
		}
		else 
		{
			sf.push({name: 'Drug_Name', id: 'autoexpand', header: lang['naimenovanie']});
			sf.push({name: 'DocumentUcStr_Count', width: 80, header: lang['kol-vo'], type: 'float'});
			sf.push({name: 'DocumentUcStr_PriceR', width: 100, header: lang['tsena'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_Ser', width: 60, header: lang['seriya']});
			sf.push({name: 'DocumentUcStr_godnDate', width: 110, header: lang['srok_godnosti'], type: 'date'});
		}
		
		this.DocumentUcStrPanel = new sw.Promed.ViewFrame(
		{
			title:lang['medikamentyi'],
			id: 'doewDocumentUcStrGrid',
			border: true,
			region: 'center',
			object: 'DocumentUcStr',
			editformclassname: isFarmacyInterface ? 'swDocumentUcStrEditWindow' : 'swDocumentUcStrLpuEditWindow', //в зависимости от режима используем разные формы, возможно стоит привязыватся к типу контрагента а не к режиму промеда
			dataUrl: '/?c=Farmacy&m=loadDocumentUcStrView',
			toolbar: true,
			autoLoadData: false,
			stringfields: sf,
			actions:
			[
				{name:'action_add', func: form.MainRecordAdd.createDelegate(form)},
				{name:'action_edit', func: function() {
						this.DocumentUcStrPanel.setParam('mode','income',false);
						this.DocumentUcStrPanel.setParam('DrugDocumentType_Code',3,false);
						this.DocumentUcStrPanel.run_function_edit = false;
						this.DocumentUcStrPanel.runAction('action_edit');
					}.createDelegate(form)
				},
				{name:'action_view'},
				{name:'action_delete'}
			],
			onLoadData: function() {
				var win = Ext.getCmp('DokOstEditForm');
				// Если данных есть сколько то :) 
				if (this.getCount()>0) {
					win.findById('dprewDrugfinance_id').disable();
					win.findById('dprefCostItemType_id').disable();
					win.findById('doewContragent_tid').disable();
					win.findById('doewMol_tid').disable();
				} else  {
					win.findById('dprewDrugfinance_id').enable();
					win.findById('dprefCostItemType_id').enable();
					if (form.Contragent_tid) {
						win.findById('doewContragent_tid').disable();
					} else {
						win.findById('doewContragent_tid').enable();
					}
					win.findById('doewMol_tid').enable();
				}
			},
			onRowSelect: function (sm,index,record) {
				var win = Ext.getCmp('DokOstEditForm');
			},
			focusOn: {name:'doewDocumentUc_Name',type:'field'},
			focusPrev: {name:'doewDocumentUc_Name',type:'field'}
		});
		
		this.DokOstEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokOstEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[{
				id: 'doewDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'doewContragent_id',
				name: 'Contragent_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: 125,
				title: lang['kontragent'],
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items: [{
					width:505,
					allowBlank: false,
					fieldLabel: lang['poluchatel'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 12,
					id: 'doewContragent_tid',
					name: 'Contragent_tid',
					hiddenName:'Contragent_tid',
					listeners: {
						change: function(combo) {
							this.findById('doewMol_tid').setDisabled(!(combo.getValue()>0));
							
							if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
								this.findById('doewMol_tid').setAllowBlank(false);
								this.findById('doewMol_tid').enable();
								this.setFilterMol(this.findById('doewMol_tid'), combo.getValue());
							} else {
								this.findById('doewMol_tid').disable();
								this.findById('doewMol_tid').setAllowBlank(true);
								this.findById('doewMol_tid').setValue(null);
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					width:505,
					fieldLabel: lang['mol_poluchatelya'],
					hiddenName: 'Mol_tid',
					id: 'doewMol_tid',
					lastQuery: '',
					linkedElements: [ ],
					tabIndex: TABINDEX_DPREW + 13,
					xtype: 'swmolcombo'
				}]
			},
			{
				layout: 'column',
				labelWidth: 130,
				border: false,
				items:
					[{
						layout: 'form',
						border: false,
						width: 640,
						items: [{
							tabIndex: form.firstTabIndex + 1,
							xtype: 'swdrugfinancecombo',
							fieldLabel : lang['istochnik_finans'],
							name: 'DrugFinance_id',
                            id: 'dprewDrugfinance_id',
							width: 505,
							allowBlank: false
						}]
					}]
			},
			{
				layout: 'column',
				labelWidth: 130,
				border: false,
				items:
					[{
						layout: 'form',
						border: false,
						width: 640,
						items: [{
							tabIndex: form.firstTabIndex + 1,
							xtype: 'swwhsdocumentcostitemtypecombo',
							fieldLabel : lang['statya_rashodov'],
							name: 'WhsDocumentCostItemType_id',
							id:'dprefCostItemType_id',
							width: 505,
							allowBlank: false
						}]
					}]
			},
			{
				tabIndex: form.firstTabIndex + 1,
				xtype: 'textfield',
				fieldLabel : lang['nomer_dokumenta'],
				name: 'DocumentUc_Num',
				id: 'doewDocumentUc_Num',
				width: 184,
				allowBlank:false
			},
			{
				fieldLabel : lang['data_podpisaniya'],
				tabIndex: form.firstTabIndex + 2,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_setDate',
				id: 'doewDocumentUc_setDate',
				listeners: {
					change: function(field, newValue, oldValue) {
						form.filterFinCombo();
					}
				}
			},
			{
				fieldLabel : lang['data_postavki'],
				tabIndex: form.firstTabIndex + 3,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_didDate',
				id: 'doewDocumentUc_didDate'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
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
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{ name: 'Contragent_tid' },
				{ name: 'Mol_tid' },
				{ name: 'DocumentUc_id' },
				{ name: 'DocumentUc_Num' },
				{ name: 'DocumentUc_setDate' },
				{ name: 'DocumentUc_didDate' },
				{ name: 'DrugFinance_id' },
				{ name: 'WhsDocumentCostItemType_id' }
			]),
			url: '/?c=Farmacy&m=save&method=DokOst'
		});
		Ext.apply(this, 
		{
			border: false,
			xtype: 'panel',
			region: 'center',
			layout:'border',
			
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave(true);
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL
			}],
			//items: [form.DokOstEditForm, this.DocumentUcStrPanel]
			items:
			[
				form.DokOstEditForm,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items: 
					[
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.DocumentUcStrPanel]
						}
					]
				}
			]
		});
		sw.Promed.swDokOstEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});