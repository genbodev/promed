/**
* swDokSpisEditWindow - окно редактирования/добавления документа списания медикаментов.
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
* @comment      Префикс для id компонентов dsew (DokSpisEditWindow)
*               tabIndex (firstTabIndex): 15300+1 .. 15400
*
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документа
*/

sw.Promed.swDokSpisEditWindow = Ext.extend(sw.Promed.BaseForm, {
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
	id: 'DokSpisEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	filterFinCombo: function(date) { //фильтрация справочников "источник финансирования" и "статья расходов"
		var base_form = this.DokSpisEditForm.getForm();
		if (Ext.isEmpty(date)) {
			date = base_form.findField('DocumentUc_setDate').getValue();
		}
		if (!Ext.isEmpty(date)) {
			date = date.format('d.m.Y');
		}
		base_form.findField('DrugFinance_id').setDateFilter({Date: date});
		base_form.findField('WhsDocumentCostItemType_id').setDateFilter({Date: date});
	},
	doSave: function(flag) {
		var form = this.DokSpisEditForm;
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
	doDocExecute: function() {
		if (confirm(lang['posle_ispolneniya_redaktirovanie_dokumenta_stanet_nedostupno_prodoljit'])) {
			if (this.doSave()) {
				this.submit('execute_doc');
			}
		}
		return false;
	},
	submit: function(mode,onlySave) {
		var form = this.DokSpisEditForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		form.getForm().submit(
		{
			params: {
				WhsDocumentCostItemType_id:form.getForm().findField('WhsDocumentCostItemType_id').getValue(),
				DrugFinance_id:form.getForm().findField('dprewDrugfinance_id').getValue(),
				Contragent_sid: form.findById('dsewContragent_sid').getValue(),
				Mol_sid: form.findById('dsewMol_sid').getValue()
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
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.DocumentUc_id) {

						if (mode == 'execute_doc') {
							Ext.Ajax.request({
								callback: function(options, success, response) {
									if (response.responseText != '') {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (success && response_obj.success) {
											win.setAction('view');
											alert(lang['dokument_uspeshno_ispolnen']);
										} else {
											sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При исполнении документа возникла ошибка');
										}
									}
								},
								params: {
									DocumentUc_id: action.result.DocumentUc_id
								},
								url: '/?c=Farmacy&m=executeDocumentUc'
							});
						}

						//log(form.getForm().getValues());
						if (!onlySave || (onlySave!==1)) {
							win.hide();
							win.callback(win.owner, action.result.DocumentUc_id);
						} else {
							new Ext.ux.window.MessageWindow( {
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
								html: lang['dannyie_o_dokumente_spisaniya_medikamentov_sohranenyi'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							if (!form.findById('dsewDocumentUc_id').getValue())
							{
								form.findById('dsewDocumentUc_id').setValue(action.result.DocumentUc_id);
								//log(action.result.DocumentUc_id);
								win.DocumentUcStrPanel.params =
								{									
									DocumentUc_id: form.findById('dsewDocumentUc_id').getValue(),
									Contragent_id: form.findById('dsewContragent_sid').getValue(),
									mode: 'expenditure',
									DrugFinance_id: form.getForm().findField('DrugFinance_id').getValue(),
									WhsDocumentCostItemType_id: form.getForm().findField('WhsDocumentCostItemType_id').getValue()
								};
								win.DocumentUcStrPanel.gFilters =
								{
									DocumentUc_id: form.findById('dsewDocumentUc_id').getValue()
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
				if (Ext.getCmp('DokSpisEditForm').reader.jsonData[0].Contragent_sid == Contragent_id && Contragent_id > 0)
					combo.setValue(Ext.getCmp('DokSpisEditForm').reader.jsonData[0].Mol_sid);
			}
		}
	},	
	enableEdit: function(enable) 
	{
		var form = this;
		var base_form = this.findById('DokSpisEditForm').getForm();
		if (enable) {
			form.findById('dsewDocumentUc_Num').enable();
			form.findById('dsewDocumentUc_setDate').enable();
			form.findById('dsewDocumentUc_didDate').enable();
			base_form.findField('DrugFinance_id').enable();
			base_form.findField('WhsDocumentCostItemType_id').enable();
			form.DocumentUcStrPanel.setReadOnly(false);
			form.buttons[0].enable();
			form.buttons[1].enable();
		} else  {
			form.findById('dsewDocumentUc_Num').disable();
			form.findById('dsewDocumentUc_setDate').disable();
			form.findById('dsewDocumentUc_didDate').disable();
			base_form.findField('DrugFinance_id').disable();
			base_form.findField('WhsDocumentCostItemType_id').disable();
			form.DocumentUcStrPanel.setReadOnly(true);
			form.buttons[0].disable();
			form.buttons[1].disable();
		}
	},
	show: function() 
	{
		sw.Promed.swDokSpisEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.focus();
		form.findById('DokSpisEditForm').getForm().reset();
		form.DocumentUcStrPanel.removeAll();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].DocumentUc_id) 
			form.DocumentUc_id = arguments[0].DocumentUc_id;
		else 
			form.DocumentUc_id = null;
			
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			form.action = arguments[0].action;
		}
		else 
		{
			if ((form.DocumentUc_id) && (form.DocumentUc_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
			form.action = 'view';
		if (arguments[0].Contragent_sid) {
			form.Contragent_sid = arguments[0].Contragent_sid;
		}
		if (arguments[0].DrugFinance_id) {
			form.DrugFinance_id = arguments[0].DrugFinance_id;
		} else if(arguments[0].owner){
			form.DrugFinance_id = arguments[0].owner.getGrid().getSelectionModel().getSelected().data.DrugFinance_id
		}
		if (arguments[0].WhsDocumentCostItemType_id) {
			form.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		} else if(arguments[0].owner){
			form.WhsDocumentCostItemType_id = arguments[0].owner.getGrid().getSelectionModel().getSelected().data.WhsDocumentCostItemType_id
		}
		
		form.findById('DokSpisEditForm').getForm().setValues(arguments[0]);
		form.findById('dsewContragent_sid').getStore().baseParams.mode = 'self_lpu';

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		switch (form.action) 
		{
			case 'add':
				form.setTitle(lang['dokument_spisaniya_medikamentov_dobavlenie']);
				form.enableEdit(true);
				loadMask.hide();
				//form.getForm().clearInvalid();
				form.findById('dsewDocumentUc_Num').focus(true, 50);
				form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:null}, globalFilters:{DocumentUc_id:null, mode: 'expenditure'}, noFocusOnLoad: true});
				form.filterFinCombo();
				break;
			case 'edit':
				form.setTitle(lang['dokument_spisaniya_medikamentov_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['dokument_spisaniya_medikamentov_prosmotr']);
				break;
		}

		if (form.action!='add')
		{
			form.findById('DokSpisEditForm').getForm().load(
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
				success: function(options, action) {
					var response_data = Ext.util.JSON.decode(action.response.responseText);
					//var1// loadMask.hide();
					//var1// form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:form.findById('dsewDocumentUc_id').getValue()}, globalFilters:{DocumentUc_id:form.findById('dsewDocumentUc_id').getValue(), mode: 'expenditure'}, noFocusOnLoad:true});
					form.loadContragent('dsewContragent_sid', null/*{mode:'self_lpu'}*/, function() {
						//var1// this.loadSprMol('dsewMol_sid','dsewContragent_sid',true);
						//var1// loadMask.hide();
						
						this.loadSprMol('dsewMol_sid','dsewContragent_sid', true);

						var Contragent_id = this.findById('dsewContragent_sid').getValue();
						var DocumentUc_didDate = this.findById('dsewDocumentUc_didDate').getValue();
						this.DocumentUcStrPanel.loadData({params:{DocumentUc_didDate: DocumentUc_didDate, DocumentUc_id:this.findById('dsewDocumentUc_id').getValue(), mode: 'expenditure', Contragent_id: Contragent_id}, globalFilters:{DocumentUc_id:this.findById('dsewDocumentUc_id').getValue(), mode: 'income'}, noFocusOnLoad:true});

						loadMask.hide();
					}.createDelegate(form));

					if (form.action=='edit') {
						form.enableEdit(response_data[0].DrugDocumentStatus_id != 2); //2 - Исполнен
						form.findById('dsewDocumentUc_Num').focus(true, 50);
					} else  {
						form.focus();
						form.enableEdit(false);
					}

					form.filterFinCombo();
					if(form.DrugFinance_id)
					{
						form.findById('dprewDrugfinance_id').getStore().load({
							callback: function() {
								form.findById('dprewDrugfinance_id').setValue(form.DrugFinance_id);
							}
						});
					}
					if(form.WhsDocumentCostItemType_id)
					{
						form.findById('dprewCostItemType_id').getStore().load({
							callback: function() {
								form.findById('dprewCostItemType_id').setValue(form.WhsDocumentCostItemType_id);
							}
						});
					}
				},
				url: '/?c=Farmacy&m=edit&method=DokSpis'
			});
		} else {
			form.loadContragent('dsewContragent_sid', null/*{mode:'self_lpu'}*/, function() {
				this.loadSprMol('dsewMol_sid','dsewContragent_sid');
				loadMask.hide();
			}.createDelegate(form));
		}
	},
	drugIDFilt:function(){
		//Временно отключаю, так как при списании реактивов (#28525) остатки хранятся в регистре, и нужный источник финанчирования не отображается. Salakhov R.
		return;

		var combo = this.findById('dsewContragent_sid');
		Ext.Ajax.request({
			url: '/?c=Farmacy&m=loadDrugFinanceList',
			callback: function(opt, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var DrugFinanceList = new Array;
					var inlistDFflag = true;
					for (var i=0; i < response_obj.length; i++) {
						DrugFinanceList.push(response_obj[i].DrugFinance_id);
						if(this.findById('dprewDrugfinance_id').getValue()==response_obj[i].DrugFinance_id&&inlistDFflag)
						{
							inlistDFflag = false;
						}
					}

					if (Ext.isArray(response_obj) && response_obj) {
						this.findById('dprewDrugfinance_id').getStore().filterBy(function(record) {
							if (record.get('DrugFinance_id').inlist(DrugFinanceList)) {
								return true;
							} else {return false;}
						});
					}
					if(inlistDFflag)this.findById('dprewDrugfinance_id').setValue('');
				}
			}.createDelegate(this),
			failure: function() {
				sw.swMsg.alert(lang['vnimanie'], lang['ne_udalos_zagruzit_spisok_istochnikov_finansirovaniya_kontragenta']);
			},
			headers: { },
			params: {Contragent_id: combo.getFieldValue('Contragent_id')}
		});
	}
	,
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		this.MainRecordAdd = function() {
			var tf = Ext.getCmp('DokSpisEditWindow');
			var base_form = tf.DokSpisEditForm.getForm();
			if(base_form.findField('DrugFinance_id').getValue()!=''&&base_form.findField('WhsDocumentCostItemType_id').getValue()!=''){
			if (tf.findById('dsewDocumentUc_id').getValue()>0) {
				tf.DocumentUcStrPanel.params = {
					DocumentUc_id: tf.findById('dsewDocumentUc_id').getValue(),
					Contragent_id: tf.findById('dsewContragent_sid').getValue(),
					mode: 'expenditure',
					DrugFinance_id: base_form.findField('DrugFinance_id').getValue(),
					WhsDocumentCostItemType_id: base_form.findField('WhsDocumentCostItemType_id').getValue()
				};
				tf.DocumentUcStrPanel.gFilters = {
					DocumentUc_id: tf.findById('dsewDocumentUc_id').getValue()
				};
				tf.DocumentUcStrPanel.run_function_add = false;
				tf.DocumentUcStrPanel.ViewActions.action_add.execute();
			} else {tf.drugIDFilt();
				if (tf.doSave()) {
					tf.submit('add',1);
				}
				tf.drugIDFilt();
				return false;
			}
			}else return false;
		}		
		this.MainRecordEdit = function() {
			var tf = Ext.getCmp('DokSpisEditWindow');
			if (tf.doSave()) {
				tf.submit('edit',1);
			}
			return false;
		}

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
			id: 'dsewDocumentUcStrGrid',
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
						this.DocumentUcStrPanel.setParam('mode','expenditure',false); 
						this.DocumentUcStrPanel.run_function_edit = false;
						this.DocumentUcStrPanel.runAction('action_edit');
					}.createDelegate(form)
				},
				{name:'action_view'},
				{name:'action_delete'}
			],
			onLoadData: function() {
				var win = Ext.getCmp('DokSpisEditForm');
				// Если данных есть сколько то :) 
				if (this.getCount()>0) {
					win.findById('dprewDrugfinance_id').disable();
					win.findById('dprewCostItemType_id').disable();
					win.findById('dsewContragent_sid').disable();
					win.findById('dsewMol_sid').disable();
				} else  {
					win.findById('dprewDrugfinance_id').enable();
					win.findById('dprewCostItemType_id').enable();
					if (form.Contragent_sid) {
						win.findById('dsewContragent_sid').disable();
					} else {
						win.findById('dsewContragent_sid').enable();
					}
					win.findById('dsewMol_sid').enable();
				}
			},
			onRowSelect: function (sm,index,record) {
				var win = Ext.getCmp('DokSpisEditForm');
			},
			focusOn: {name:'dsewDocumentUc_Name',type:'field'},
			focusPrev: {name:'dsewDocumentUc_Name',type:'field'}
		});
		
		this.DokSpisEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokSpisEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[{
				id: 'dsewDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'dsewContragent_id',
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
					fieldLabel: lang['postavschik'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 12,
					id: 'dsewContragent_sid',
					name: 'Contragent_sid',
					hiddenName:'Contragent_sid',
					listeners: {
						change: function(combo) {
							log(combo);
							this.findById('dsewMol_sid').setDisabled(!(combo.getValue()>0));
							
							if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
								//this.findById('dsewMol_sid').setAllowBlank(false);
								this.findById('dsewMol_sid').setAllowBlank(true);
								this.findById('dsewMol_sid').enable();
								this.setFilterMol(this.findById('dsewMol_sid'), combo.getValue());
							} else {
								this.findById('dsewMol_sid').disable();
								this.findById('dsewMol_sid').setAllowBlank(true);
								this.findById('dsewMol_sid').setValue(null);
							}
							this.drugIDFilt();
						}.createDelegate(this)
					}
				}, {
					//allowBlank: false,
					allowBlank: true,
					width:505,
					fieldLabel: lang['mol_postavschika'],
					hiddenName: 'Mol_sid',
					id: 'dsewMol_sid',
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
							id: 'dprewCostItemType_id',
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
				id: 'dsewDocumentUc_Num',
				width: 184,
				allowBlank: false
			},
			{
				fieldLabel : lang['data_podpisaniya'],
				tabIndex: form.firstTabIndex + 2,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_setDate',
				id: 'dsewDocumentUc_setDate',
				listeners: {
					change: function(field, newValue, oldValue) {
						form.filterFinCombo();
					}
				}
			},
			{
				fieldLabel : lang['data_spisaniya'],
				tabIndex: form.firstTabIndex + 3,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_didDate',
				id: 'dsewDocumentUc_didDate'
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
				{ name: 'Contragent_sid' },
				{ name: 'Mol_sid' },
				{ name: 'DocumentUc_id' },
				{ name: 'DocumentUc_Num' },
				{ name: 'DocumentUc_setDate' },
				{ name: 'DocumentUc_didDate' },
				{ name: 'DrugFinance_id' },
				{ name: 'WhsDocumentCostItemType_id' }
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
				handler: function() 
				{
					this.ownerCt.doSave(true);
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, /*{
				handler: function() {
					this.ownerCt.doDocExecute();
				},
				iconCls: 'ok16',
				id: 'duefBtnDocExecute',
				text: lang['ispolnit']
			},*/
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
			//items: [form.DokSpisEditForm, this.DocumentUcStrPanel]
			items:
			[
				form.DokSpisEditForm,
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
		sw.Promed.swDokSpisEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});