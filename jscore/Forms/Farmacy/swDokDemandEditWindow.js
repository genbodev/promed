/**
* swDokDemandEditWindow - окно редактирования/добавления заявки на медикаменты.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Salakhov R.
* @version      
* @comment      Префикс для id компонентов ddew (DokDemandEditWindow)
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документа
*/
/*NO PARSE JSON*/

sw.Promed.swDokDemandEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swDokDemandEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokDemandEditWindow.js',
	action: null,	
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
	firstTabIndex: 50000,
	id: 'DokDemandEditWindow',
	listeners: {
		hide: function() {
			this.callback(this.owner, -1);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function(flag) {
		var form = this.DokDemandEditForm;
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
		if (flag==true) form.ownerCt.submit();
		return true;
	},
	submit: function(mode,onlySave) {
		var form = this.DokDemandEditForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		form.getForm().submit({
			params: {
				Contragent_sid: form.findById('ddewContragent_sid').getValue(),
				Mol_sid: form.findById('ddewMol_sid').getValue(),
				Contragent_tid: form.findById('ddewContragent_tid').getValue(),
				Mol_tid: form.findById('ddewMol_tid').getValue()
			},
			failure: function(result_form, action)  {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.DocumentUc_id) {
						//log(form.getForm().getValues());
						if (!onlySave || (onlySave!==1)) {
							//win.hide();
							win.callback(win.owner, action.result.DocumentUc_id);
						} else {
							new Ext.ux.window.MessageWindow({
								title: lang['sohranenie'],
								autoHeight: true,
								help: false,
								bodyStyle: 'text-align:center',
								closable: true,
								hideFx: {
									delay: 3000,
									mode: 'standard',
									useProxy: false
								},
								html: lang['dannyie_zayaki_na_medikamentyi_sohranenyi'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							if (!form.findById('ddewDocumentUc_id').getValue()) {
								form.findById('ddewDocumentUc_id').setValue(action.result.DocumentUc_id);
								//log(action.result.DocumentUc_id);
								win.DocumentUcStrPanel.params = {									
									DocumentUc_id: form.findById('ddewDocumentUc_id').getValue(),
									Contragent_id: form.findById('ddewContragent_sid').getValue(),
									mode: 'expenditure'
								};
								win.DocumentUcStrPanel.gFilters = {
									DocumentUc_id: form.findById('ddewDocumentUc_id').getValue()
								};
								if (mode=='add') {
									win.DocumentUcStrPanel.run_function_add = false;
									win.DocumentUcStrPanel.ViewActions.action_add.execute();
								}
							} else {
								if (mode=='add') {
									win.DocumentUcStrPanel.run_function_add = false;
									win.DocumentUcStrPanel.ViewActions.action_add.execute();
								}
								else if (mode=='edit') {
									win.DocumentUcStrPanel.run_function_edit = false;
									win.DocumentUcStrPanel.ViewActions.action_edit.execute();
								}
							}
						}
					} else {
						sw.swMsg.show({
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
	enableEdit: function(enable) {
		var form = this;
		if (enable) {
			form.findById('ddewDocumentUc_Num').enable();
			form.findById('ddewDocumentUc_setDate').enable();
			form.DocumentUcStrPanel.setReadOnly(false);
			form.buttons[0].enable();
		} else {
			form.findById('ddewDocumentUc_Num').disable();
			form.findById('ddewDocumentUc_setDate').disable();
			form.DocumentUcStrPanel.setReadOnly(true);
			form.buttons[0].disable();
		}
	},
	show: function() {
		sw.Promed.swDokDemandEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		var base_form = form.findById('DokDemandEditForm').getForm();
		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.focus();
		form.findById('DokDemandEditForm').getForm().reset();
		form.DocumentUcStrPanel.removeAll();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		form.DocumentUc_id = arguments[0].DocumentUc_id ? arguments[0].DocumentUc_id : null;
			
		if (arguments[0].callback)
			form.callback = arguments[0].callback;
		if (arguments[0].owner)
			form.owner = arguments[0].owner;
		if (arguments[0].onHide)
			form.onHide = arguments[0].onHide;
		if (arguments[0].action) {
			form.action = arguments[0].action;
		} else {			
			form.action = (form.DocumentUc_id && form.DocumentUc_id > 0) ? "edit" : "add";
		}		
		if (arguments[0] && arguments[0].filters)
			base_form.setValues(arguments[0].filters);		
		
		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) {
			case 'add':
				form.setTitle(lang['zayavka_dobavlenie']);
				form.enableEdit(true);
				loadMask.hide();
				//form.getForm().clearInvalid();
				form.findById('ddewDocumentUc_Num').focus(true, 50);
				form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:null}, globalFilters:{DocumentUc_id:null, mode: 'expenditure'}, noFocusOnLoad: true});
				break;
			case 'edit':
				form.setTitle(lang['zayavka_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['zayavka_prosmotr']);
				break;
		}

		if (form.action!='add') {
			form.findById('DokDemandEditForm').getForm().load( {
				params: {
					DocumentUc_id: form.DocumentUc_id
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
					//var1// loadMask.hide();
					//var1// form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:form.findById('ddewDocumentUc_id').getValue()}, globalFilters:{DocumentUc_id:form.findById('ddewDocumentUc_id').getValue(), mode: 'expenditure'}, noFocusOnLoad:true});
					
					loadContragent(form, 'ddewContragent_sid', {mode:'self_lpu'}, function() {						
						//var1// loadSprMol(this, 'ddewMol_sid','ddewContragent_sid',true);
						//var1// loadMask.hide();
						
						loadSprMol(this, 'ddewMol_sid','ddewContragent_sid', true);
						//						
						var Contragent_id = this.findById('ddewContragent_sid').getValue();						
						this.DocumentUcStrPanel.loadData({params:{DocumentUc_id:this.findById('ddewDocumentUc_id').getValue(), mode: 'expenditure', Contragent_id: Contragent_id}, globalFilters:{DocumentUc_id:this.findById('ddewDocumentUc_id').getValue(), mode: 'income'}, noFocusOnLoad:true});
						//
						loadMask.hide();
					}.createDelegate(form));
					
					loadContragent(form, 'ddewContragent_tid', {mode:'self_lpu'}, function() {
						loadSprMol(this, 'ddewMol_tid','ddewContragent_tid', true);
						loadMask.hide();
					}.createDelegate(form));
					
					if (form.action=='edit') {
						form.enableEdit(true);
						form.findById('ddewDocumentUc_Num').focus(true, 50);
					} else  {
						form.focus();
						form.enableEdit(false);
					}
				},
				url: '/?c=Farmacy&m=edit&method=DokDemand'
			});
		} else {
			loadContragent(form, 'ddewContragent_sid', {mode:'self_lpu'}, function() {				
				loadSprMol(this, 'ddewMol_sid', 'ddewContragent_sid');
				loadMask.hide();
			}.createDelegate(form));
			loadContragent(form, 'ddewContragent_tid', {mode:'self_lpu'}, function() {				
				loadSprMol(this, 'ddewMol_tid', 'ddewContragent_tid');
				loadMask.hide();
			}.createDelegate(form));
		}
	},
	
	initComponent: function() {
		// Форма с полями 
		var form = this;
		this.MainRecordAdd = function() {
			var tf = Ext.getCmp('DokDemandEditWindow');
			if (tf.findById('ddewDocumentUc_id').getValue()>0) {
				tf.DocumentUcStrPanel.params = {
					DocumentUc_id: tf.findById('ddewDocumentUc_id').getValue(),
					Contragent_id: tf.findById('ddewContragent_sid').getValue(),
					mode: 'expenditure'
				};
				tf.DocumentUcStrPanel.gFilters = {
					DocumentUc_id: tf.findById('ddewDocumentUc_id').getValue()
				};
				tf.DocumentUcStrPanel.run_function_add = false;
				tf.DocumentUcStrPanel.ViewActions.action_add.execute();
			} else {
				if (tf.doSave()) {
					tf.submit('add',1);
				}
				return false;
			}
		}		
		this.MainRecordEdit = function() {
			var tf = Ext.getCmp('DokDemandEditWindow');
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
		if (isFarmacyInterface) {
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
		} else  {
			sf.push({name: 'Drug_Name', id: 'autoexpand', header: lang['naimenovanie']});
			sf.push({name: 'DocumentUcStr_Count', width: 80, header: lang['kol-vo'], type: 'float'});
			sf.push({name: 'DocumentUcStr_PriceR', width: 100, header: lang['tsena'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUcStr_Ser', width: 60, header: lang['seriya']});
			sf.push({name: 'DocumentUcStr_godnDate', width: 110, header: lang['srok_godnosti'], type: 'date'});
		}
		
		this.DocumentUcStrPanel = new sw.Promed.ViewFrame({
			title:lang['medikamentyi'],
			id: 'ddewDocumentUcStrGrid',
			border: true,
			region: 'center',
			object: 'DocumentUcStr',
			editformclassname: isFarmacyInterface ? 'swDocumentUcStrEditWindow' : 'swDocumentUcStrLpuEditWindow', //в зависимости от режима используем разные формы, возможно стоит привязыватся к типу контрагента а не к режиму промеда
			dataUrl: '/?c=Farmacy&m=loadDocumentUcStrView',
			toolbar: true,
			autoLoadData: false,
			stringfields: sf,
			actions: [
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
				var win = Ext.getCmp('DokDemandEditForm');
				// Если данных есть сколько то :) 
				if (this.getCount()>0) {
					win.findById('ddewContragent_sid').disable();
					win.findById('ddewMol_sid').disable();
				} else  {
					win.findById('ddewContragent_sid').enable();					
					win.findById('ddewMol_sid').enable();
				}
			},
			onRowSelect: function (sm,index,record) {
				var win = Ext.getCmp('DokDemandEditForm');
			},
			focusOn: {name:'ddewDocumentUc_Name',type:'field'},
			focusPrev: {name:'ddewDocumentUc_Name',type:'field'}
		});
		
		this.DokDemandEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokDemandEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: [{
				id: 'ddewDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}, {
				id: 'ddewContragent_id',
				name: 'Contragent_id',
				value: null,
				xtype: 'hidden'
			}, {
				layout: 'column',						
				items: [{
					layout: 'form',
					items: [{
						tabIndex: form.firstTabIndex + 1,
						xtype: 'textfield',
						fieldLabel : lang['nomer'],
						name: 'DocumentUc_Num',
						id: 'ddewDocumentUc_Num',
						allowBlank:false
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel : lang['data'],
						tabIndex: form.firstTabIndex + 2,
						allowBlank: false,
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'DocumentUc_setDate',
						id: 'ddewDocumentUc_setDate'
					}]
				}]
			}, {
				layout: 'column',						
				items: [{
					layout: 'form',
					items: [{
						tabIndex: form.firstTabIndex + 1,
						xtype: 'swcustomobjectcombo',
						fieldLabel : lang['osnovanie'],
						name: 'DrugDocumentMotivation_id',
						id: 'DrugDocumentMotivation_id',
						comboSubject: 'DrugDocumentMotivation',
						sortField: 'DrugDocumentMotivation_Code',
						allowBlank: true
					}]
				}, {
					layout: 'form',
					items: [{
						tabIndex: form.firstTabIndex + 1,
						xtype: 'textfield',
						fieldLabel : lang['lgota'],
						name: 'DocumentUc_Lgot',
						id: 'ddewDocumentUc_Lgot',
						allowBlank: true
					}]
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: 125,
				title: lang['postavschik'],
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items: [{
					width:500,
					allowBlank: false,
					fieldLabel: lang['postavschik'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 12,
					id: 'ddewContragent_sid',
					name: 'Contragent_sid',
					hiddenName:'Contragent_sid',
					listeners: {
						change: function(combo) {
							this.findById('ddewMol_sid').setDisabled(!(combo.getValue()>0));
							
							if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
								this.findById('ddewMol_sid').setAllowBlank(false);
								this.findById('ddewMol_sid').enable();
								setFilterMol(this.findById('ddewMol_sid'), combo.getValue());
							} else {
								this.findById('ddewMol_sid').disable();
								this.findById('ddewMol_sid').setAllowBlank(true);
								this.findById('ddewMol_sid').setValue(null);
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					width:500,
					fieldLabel: lang['mol_postavschika'],
					hiddenName: 'Mol_sid',
					id: 'ddewMol_sid',
					lastQuery: '',
					linkedElements: [ ],
					tabIndex: TABINDEX_DPREW + 13,
					xtype: 'swmolcombo'
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				labelWidth: 125,
				title: lang['poluchatel'],
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items: [{
					width:500,
					allowBlank: false,
					fieldLabel: lang['poluchatel'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 14,
					id: 'ddewContragent_tid',
					name: 'Contragent_tid',
					hiddenName:'Contragent_tid',
					listeners: {
						change: function(combo) {
							this.findById('ddewMol_tid').setDisabled(!combo.getValue()>0);							
							if (combo.getValue()>0 && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
								this.findById('ddewMol_tid').setAllowBlank(false);
								this.findById('ddewMol_tid').enable();
								setFilterMol(this.findById('ddewMol_tid'), combo.getValue());
							} else {
								this.findById('ddewMol_tid').disable();
								this.findById('ddewMol_tid').setAllowBlank(true);
								this.findById('ddewMol_tid').setValue(null);
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					width:500,
					fieldLabel: lang['mol_poluchatelya'],
					hiddenName: 'Mol_tid',
					id: 'ddewMol_tid',
					lastQuery: '',
					linkedElements: [ ],
					tabIndex: TABINDEX_DPREW + 15,
					xtype: 'swmolcombo'
				}]
			}, {
				layout: 'column',						
				items: [{
					layout: 'form',
					items: [{
						tabIndex: form.firstTabIndex + 1,
						xtype: 'textfield',
						fieldLabel : lang['nomer_dogovora'],
						name: 'DocumentUc_DogNum',
						id: 'ddewDocumentUc_DogNum',
						allowBlank:false
					}]
				}, {
					layout: 'form',
					items: [{
						fieldLabel : lang['data_dogovora'],
						tabIndex: form.firstTabIndex + 2,
						allowBlank: false,
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'DocumentUc_DogDate',
						id: 'ddewDocumentUc_DogDate'
					}]
				}]
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) {
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
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, 
			[
				{ name: 'Contragent_sid' },
				{ name: 'Mol_sid' },
				{ name: 'DocumentUc_id' },
				{ name: 'DocumentUc_Num' },
				{ name: 'DocumentUc_setDate' },
				{ name: 'DrugFinance_id' }
			]),
			url: '/?c=Farmacy&m=save&method=DokDemand'
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
			//items: [form.DokDemandEditForm, this.DocumentUcStrPanel]
			items:
			[
				form.DokDemandEditForm,
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
		sw.Promed.swDokDemandEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});