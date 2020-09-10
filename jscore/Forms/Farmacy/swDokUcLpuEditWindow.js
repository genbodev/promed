/**
* swDokUcLpuEditWindow - окно редактирования/добавления прихода расхода .
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
* @comment      Префикс для id компонентов dprew (DokUcLpuEditForm)
*               tabIndex (firstTabIndex): 15300+1 .. 15400
*
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документа
*/
/*NO PARSE JSON*/
sw.Promed.swDokUcLpuEditWindow = Ext.extend(sw.Promed.BaseForm,
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
	id: 'DokUcLpuEditWindow',
	codeRefresh: true,
	objectName: 'swDokUcLpuEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokUcLpuEditWindow.js',
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
	doSave: function(flag) 
	{
		var form = this.DokUcLpuEditForm;
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
		var form = this.DokUcLpuEditForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		form.getForm().submit(
		{
			params:
			{	WhsDocumentCostItemType_id:form.getForm().findField('dprewWhsDocumentCostItemType_id').getValue(),
				DrugFinance_id:form.getForm().findField('dprewDrugfinance_id').getValue(),
				Contragent_sid: form.getForm().findField('Contragent_sid').getValue(),
				Mol_sid: form.getForm().findField('Mol_sid').getValue(),
				Contragent_tid: form.getForm().findField('Contragent_tid').getValue(),
				Mol_tid: form.getForm().findField('Mol_tid').getValue()
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
								html: lang['dannyie_o_dokumente_ucheta_medikamentov_sohranenyi'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							if (!form.findById('dprewDocumentUc_id').getValue())
							{
								form.findById('dprewDocumentUc_id').setValue(action.result.DocumentUc_id);
							}
							//log(form.findById('dprewContragent_sid').getFieldValue('ContragentType_Code'));
							win.DocumentUcStrPanel.params = {
								DocumentUc_id: form.findById('dprewDocumentUc_id').getValue(),
								Contragent_id: form.findById('dprewContragent_sid').getValue(),
                                ContragentType_Code: form.findById('dprewContragent_sid').getFieldValue('ContragentType_Code'),
								DocumentUc_didDate: form.findById('dprewDocumentUc_didDate').getValue(),
								mode: win.getDocumentUcMode(), //(form.findById('dprewContragent_sid').getFieldValue('ContragentType_Code').inlist([1,3]))?'income':'expenditure'
								DrugFinance_id: form.getForm().findField('DrugFinance_id').getValue(),
								WhsDocumentCostItemType_id: form.getForm().findField('WhsDocumentCostItemType_id').getValue()
							};
							win.DocumentUcStrPanel.gFilters =
							{
								DocumentUc_id: form.findById('dprewDocumentUc_id').getValue()
							};
							
							
							if (!form.findById('dprewDocumentUc_id').getValue())
							{
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
	loadContragent: function(comboId, params, callback) 
	{
		var combo = this.findById(comboId);
		var value = combo.getValue();
		combo.getStore().load(
		{
			params: params,
			callback: function() 
			{
				combo.setValue(value);
				combo.fireEvent('change', combo);
				if (callback)
				{
					callback();
				}
			}.createDelegate(this)
		});
	},
	loadSprMol: function(comboId, contragentId, saveMol)
	{
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
	setFilterMol: function(combo, Contragent_id, saveMol)
	{
		// Устанавливаем фильтр и если по условиям фильтра найдена только одна запись - то устанавливаем эту запись 
		form = this;
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		var co = 0;
		var Mol_id = null;
		var molDateMax = {mol_id: '', begDate: ''};

		combo.getStore().filterBy(function(record) 
		{
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
			//•	Иначе: МОЛ контрагента Получателя, у которого самая большая дата начала периода действия.
			combo.setValue(molDateMax.mol_id);
		}else {
			if (!saveMol) {
				combo.setValue(null);
			} else {
				//если контрагент первоначальный, восстаноавливаем первоначальный Мол
				if (combo.id == 'dprewMol_sid' && Ext.getCmp('DokUcLpuEditForm').reader.jsonData[0].Contragent_sid == Contragent_id && Contragent_id > 0)
					combo.setValue(Ext.getCmp('DokUcLpuEditForm').reader.jsonData[0].Mol_sid);
				if (combo.id == 'dprewMol_tid' && Ext.getCmp('DokUcLpuEditForm').reader.jsonData[0].Contragent_tid == Contragent_id && Contragent_id > 0)
					combo.setValue(Ext.getCmp('DokUcLpuEditForm').reader.jsonData[0].Mol_tid);
			}
		}
	},
	setContragentCombo: function(c_combo, m_combo) {
		
		var c_types = isFarmacyInterface ? [2,3,5] : [2,5];
		if (this.action!='view')
			m_combo.setDisabled(!(c_combo.getValue()>0));
		if ((c_combo.getValue()>0) && (c_combo.getFieldValue('ContragentType_Code') && c_combo.getFieldValue('ContragentType_Code').inlist(c_types))){
			//m_combo.setAllowBlank(false);
			m_combo.setAllowBlank(true); //временно для показа
			if (this.action!='view')
				m_combo.enable();
			this.setFilterMol(m_combo, c_combo.getValue());
		} else {
			m_combo.setAllowBlank(true);
			m_combo.setValue(null);
			m_combo.disable();
		}
	},
	enableEdit: function(enable) {
		
		var form = this;
		var base_form = this.findById('DokUcLpuEditForm').getForm();

		if (enable) {
			form.findById('dprewContragent_sid').enable();
			//form.findById('dprewMol_sid').enable();
			form.findById('dprewContragent_tid').enable();
			//form.findById('dprewMol_tid').enable();
			form.findById('dprewDocumentUc_Num').enable();
			form.findById('dprewDocumentUc_setDate').enable();
			form.findById('dprewDocumentUc_didDate').enable();
			form.findById('dprewDocumentUc_DogNum').enable();
			form.findById('dprewDocumentUc_DogDate').enable();
			base_form.findField('DrugFinance_id').enable();
			base_form.findField('WhsDocumentCostItemType_id').enable();
			form.DocumentUcStrPanel.setReadOnly(false);
			form.buttons[0].enable();
		} else {
			form.findById('dprewContragent_sid').disable();
			form.findById('dprewMol_sid').disable();
			form.findById('dprewContragent_tid').disable();
			form.findById('dprewMol_tid').disable();
			form.findById('dprewDocumentUc_Num').disable();
			form.findById('dprewDocumentUc_setDate').disable();
			form.findById('dprewDocumentUc_didDate').disable();
			form.findById('dprewDocumentUc_DogNum').disable();
			form.findById('dprewDocumentUc_DogDate').disable();
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

		var base_form = this.findById('DokUcLpuEditForm').getForm();
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
	setDateValues: function()
	{
		setCurrentDateTime(
		{
			callback: function(date)
			{
				// проставить максимальные значения
				var f = this.findById('DokUcLpuEditForm').getForm();
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
	filterFinCombo: function(date) { //фильтрация справочников "источник финансирования" и "статья расходов"
        var tf = Ext.getCmp('DokUcLpuEditWindow');
		var base_form = this.findById('DokUcLpuEditForm').getForm();

		if (Ext.isEmpty(date)) {
			date = base_form.findField('DocumentUc_setDate').getValue();
		}
		if (!Ext.isEmpty(date)) {
			date = date.format('d.m.Y');
		}

        var DrugFinance = base_form.findField('DrugFinance_id').getValue();

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
            DrugFinance = '';//tf.findById('dprewDrugfinance_id').getStore().getAt(0).get('DrugFinance_id');
        }

        //tf.findById('dprewDrugfinance_id').setValue(DrugFinance);
	},
	getDocumentUcMode: function() { //ф-ция для определения типа документа (документ расхода/прихода)
		/*var mode = 'income';
		if (this.findById('dprewContragent_sid').getFieldValue('OrgFarmacy_id') == getGlobalOptions().OrgFarmacy_id)
			mode = 'expenditure';
		return mode;*/
		var mode = 'income';
		var combo = this.findById('dprewContragent_sid');		
		if (combo.getValue()>0) {								
			var idx = combo.getStore().findBy(function(record) {
				if (record.get('Contragent_id') == combo.getValue())
					return true;
			});
			if (idx>=0) {
				var row = combo.getStore().getAt(idx);
				mode = getDocumentUcMode(row.data);
			}
		}
		return mode;
	},
	show: function() 
	{
		sw.Promed.swDokUcLpuEditWindow.superclass.show.apply(this, arguments);
		var form = this;
        var today = new Date();
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
		form.findById('DokUcLpuEditForm').getForm().reset();
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
		if (arguments[0].Contragent_tid) {
			form.Contragent_tid = arguments[0].Contragent_tid;
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
		
		form.findById('DokUcLpuEditForm').getForm().setValues(arguments[0]);

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) 
		{
			case 'add':
				form.setTitle(lang['dokument_ucheta_medikamentov_dobavlenie']);
				form.enableEdit(true);
				if (form.Contragent_tid) {
					form.findById('DokUcLpuEditForm').getForm().findField('Contragent_tid').disable();
				}
				//form.getForm().clearInvalid();
				break;
			case 'edit':
				form.setTitle(lang['dokument_ucheta_medikamentov_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['dokument_ucheta_medikamentov_prosmotr']);
				break;
		}
		form.setDateValues();
		form.findById('dprewContragent_sid').getStore().baseParams.mode = 'sender';
		form.findById('dprewContragent_tid').getStore().baseParams.mode = 'receiver';
		if (form.action!='add')
		{
			form.findById('DokUcLpuEditForm').getForm().load(
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
					form.loadContragent('dprewContragent_sid', null, function()
					{
						this.findById('dprewContragent_sid').focus(true, 50);
						this.loadSprMol('dprewMol_tid','dprewContragent_tid', true);						
						var mode = form.getDocumentUcMode(); //(this.findById('dprewContragent_sid').getFieldValue('ContragentType_Code').inlist([1,3]))?'income':'expenditure';
						var Contragent_id = this.findById('dprewContragent_sid').getValue();
						var DocumentUc_didDate = this.findById('dprewDocumentUc_didDate').getValue();
						this.DocumentUcStrPanel.loadData({params:{DocumentUc_didDate: DocumentUc_didDate, DocumentUc_id:this.findById('dprewDocumentUc_id').getValue(), mode:mode, Contragent_id: Contragent_id}, globalFilters:{DocumentUc_id:this.findById('dprewDocumentUc_id').getValue(), mode: 'income'}, noFocusOnLoad:true});
					}.createDelegate(form));
					// получатель может быть больным, но тогда редактирование полей недоступно
					var p = {mode:'receiver'};
					/*if (form.findById('dprewContragent_tid').getValue()==1) 
					{
						// дисаблим поля 
						form.action='view';
						form.setTitle(lang['dokument_ucheta_medikamentov_prosmotr']);
						p['Contragent_id'] = form.findById('dprewContragent_tid').getValue();
					}*/
					form.loadContragent('dprewContragent_tid', p, function()
					{
						this.loadSprMol('dprewMol_sid','dprewContragent_sid', true);
						loadMask.hide();
						if(form.DrugFinance_id)
						{
							form.findById('dprewDrugfinance_id').getStore().load({
								callback: function() {
									form.findById('dprewDrugfinance_id').setValue(form.DrugFinance_id);
								}
							});
						}
					}.createDelegate(form));

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
						form.findById('dprewWhsDocumentCostItemType_id').getStore().load({
							callback: function() {
								form.findById('dprewWhsDocumentCostItemType_id').setValue(form.WhsDocumentCostItemType_id);
							}
						});
					}

					if (form.action=='edit') {
						form.enableEdit(true);
					} else {
						form.focus();
						form.enableEdit(false);
					}
					
				},
				url: '/?c=Farmacy&m=edit&method=DokUcLpu'
			});
			
		}
		else 
		{
			form.loadContragent('dprewContragent_sid', null, function()
			{
				/*
				var mode = 'expenditure';
				var m = this.findById('dprewContragent_sid').getFieldValue('ContragentType_id');
				var Contragent_id = this.findById('dprewContragent_sid').getValue();
				if (m && Contragent_id>0) 
				{
					mode = (m.inlist([1,3]))?'income':'expenditure';
					this.DocumentUcStrPanel.loadData({params:{DocumentUc_id:null, mode:mode, Contragent_id: Contragent_id}, globalFilters:{DocumentUc_id:null, mode: mode}, noFocusOnLoad: true});
				}
				*/
				this.loadSprMol('dprewMol_sid','dprewContragent_sid');
				this.findById('dprewContragent_sid').focus(true, 50);
			}.createDelegate(form));
			form.loadContragent('dprewContragent_tid', null, function()
			{

				this.loadSprMol('dprewMol_tid','dprewContragent_tid');
				loadMask.hide();
			}.createDelegate(form));

            this.findById('dprewDocumentUc_setDate').setValue(today);
            this.findById('dprewDocumentUc_didDate').setValue(today);
            form.filterFinCombo();
			
		}
		
	},
	drugIDFilt:function(){
		//Временно отключаю, так как при списании реактивов (#28525) остатки хранятся в регистре, и нужный источник финанчирования не отображается. Salakhov R.
		return; 
		var combo = this.findById('dprewContragent_sid');
		Ext.Ajax.request({
			url: '/?c=Farmacy&m=loadDrugFinanceList',
			callback: function(opt, success, response) {
				if (success) {
					this.findById('dprewDrugfinance_id').getStore().clearFilter();
					
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var DrugFinanceList = new Array;
					var inlistHasEmpty = true;
					var inlistDFflag = true;
					for (var i=0; i < response_obj.length; i++) {
						DrugFinanceList.push(response_obj[i].DrugFinance_id);
						if(Ext.isEmpty(response_obj[i].DrugFinance_id)){return true;}
						if(this.findById('dprewDrugfinance_id').getValue()==response_obj[i].DrugFinance_id&&inlistDFflag)
						{
							inlistDFflag = false;
						}
					}
					DrugFinanceList.push(this.findById('dprewDrugfinance_id').getValue());
					if (Ext.isArray(response_obj) && response_obj) {
						this.findById('dprewDrugfinance_id').getStore().filterBy(function(record) {
							if (record.get('DrugFinance_id').inlist(DrugFinanceList)) {
								return true;
							} else {return false;}
						});
					}
					//if(inlistDFflag) this.findById('dprewDrugfinance_id').setValue('');
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
			var tf = Ext.getCmp('DokUcLpuEditWindow');
			var base_form = tf.DokUcLpuEditForm.getForm();
			if(base_form.findField('DrugFinance_id').getValue()!=''&&base_form.findField('WhsDocumentCostItemType_id').getValue()!=''){
			//log(form.findById('dprewContragent_sid').getFieldValue('ContragentType_Code'));
			if (tf.findById('dprewDocumentUc_id').getValue()>0) {
				tf.DocumentUcStrPanel.params = {
					DocumentUc_id: tf.findById('dprewDocumentUc_id').getValue(),
					Contragent_id: tf.findById('dprewContragent_sid').getValue(),
                    ContragentType_Code: tf.findById('dprewContragent_sid').getFieldValue('ContragentType_Code'),
					DocumentUc_didDate: tf.findById('dprewDocumentUc_didDate').getValue(),
					mode: tf.getDocumentUcMode(), //(tf.findById('dprewContragent_sid').getFieldValue('ContragentType_Code').inlist([1,3]))?'income':'expenditure',
					DrugFinance_id: base_form.findField('DrugFinance_id').getValue(),
					WhsDocumentCostItemType_id: base_form.findField('WhsDocumentCostItemType_id').getValue()
				};
				tf.DocumentUcStrPanel.gFilters = {
					DocumentUc_id: tf.findById('dprewDocumentUc_id').getValue()
				};
				tf.DocumentUcStrPanel.run_function_add = false;
				tf.DocumentUcStrPanel.ViewActions.action_add.execute();
			} else {
				if (tf.doSave()) {
					tf.submit('add',1);
				}}
				return false;
			}else return false;
			
		}
		this.MainRecordEdit = function() {
			var tf = Ext.getCmp('DokUcLpuEditWindow');
			if (tf.doSave()) {
				tf.submit('edit',1);
			}
			return false;
		}
		
		this.DocumentUcStrPanel = new sw.Promed.ViewFrame(
		{
			title:lang['medikamentyi'],
			id: 'dprewDocumentUcStrGrid',
			border: true,
			region: 'center',
			object: 'DocumentUcStr',
			//editformclassname: 'swDocumentUcStrLpuEditWindow',
			editformclassname: isFarmacyInterface ? 'swDocumentUcStrEditWindow' : 'swDocumentUcStrLpuEditWindow', //в зависимости от режима используем разные формы, возможно стоит привязыватся к типу контрагента а не к режиму промеда
			dataUrl: '/?c=Farmacy&m=loadDocumentUcStrView',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true},
				{name: 'DocumentUc_id', hidden: true, isparams: true},
				{name: 'DrugDeleted', hidden: true, isparams: true},
				//{name: 'Drug_Code', header: 'Код', width: 100},
				{name: 'Drug_Name', id: 'autoexpand', header: lang['naimenovanie']},
				{name: 'DocumentUcStr_Count', width: 80, header: lang['kol-vo'], type: 'float'}, // +lang['ed_uch']+
				{name: 'DocumentUcStr_PriceR', width: 100, header: lang['tsena'], type: 'money', align: 'right'},
				{name: 'DocumentUcStr_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'},
				//{name: 'DocumentUcStr_SumNdsR', width: 110, header: 'НДС (розница)', type: 'money', align: 'right'},
				{name: 'DocumentUcStr_Ser', width: 60, header: lang['seriya']}, 
				{name: 'DocumentUcStr_godnDate', width: 110, header: lang['srok_godnosti'], type: 'date'}
			],
			actions:
			[
				{name:'action_add', func: form.MainRecordAdd.createDelegate(form)},
				{name:'action_edit', func: function() 
					{
						this.DocumentUcStrPanel.setParam('mode', this.getDocumentUcMode()/*(this.findById('dprewContragent_sid').getFieldValue('ContragentType_Code').inlist([1,3]))?'income':'expenditure'*/,false); 
						this.DocumentUcStrPanel.setParam('DocumentUc_didDate', this.findById('dprewDocumentUc_didDate').getValue()); 
						this.DocumentUcStrPanel.run_function_edit = false;
						this.DocumentUcStrPanel.runAction('action_edit');
					}.createDelegate(form)},
				{name:'action_view'},
				{name:'action_delete'}
			],
			onLoadData: function()
			{
				var win = Ext.getCmp('DokUcLpuEditForm');
				var c_types = isFarmacyInterface ? [2,3,5] : [2,5];
				// Если данных есть сколько то :) 				
				if (win.ownerCt.action!='view')
				{
					if (this.getCount()>0) {
						
						win.findById('dprewWhsDocumentCostItemType_id').disable();
						win.findById('dprewDrugfinance_id').disable();
						win.findById('dprewContragent_sid').disable();
						win.findById('dprewMol_sid').disable();
						win.findById('dprewContragent_tid').disable();
						win.findById('dprewMol_tid').disable();
						
					} else  {
						win.findById('dprewWhsDocumentCostItemType_id').enable();
						win.findById('dprewDrugfinance_id').enable();
						win.findById('dprewContragent_sid').enable();
                        var s_type_code = win.findById('dprewContragent_sid').getFieldValue('ContragentType_Code');
						if (!Ext.isEmpty(s_type_code) && s_type_code.inlist(c_types)) {
                            win.findById('dprewMol_sid').enable();
                        }
                        var t_type_code = win.findById('dprewContragent_tid').getFieldValue('ContragentType_Code');
						win.findById('dprewContragent_tid').enable();
						if (!Ext.isEmpty(t_type_code) && t_type_code.inlist(c_types)) {
                            win.findById('dprewMol_tid').enable();
                        }
					}
				}
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('DokUcLpuEditForm');
			},
			focusOn: {name:'dprewOk',type:'button'},
			focusPrev: {name:'dprewDocumentUc_DogDate',type:'field'}
		});
		
		this.DokUcLpuEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DokUcLpuEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[{
				id: 'dprewDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'DocumentUc_pid',
				value: null,
				xtype: 'hidden'
			}, 
			/*{
				id: 'dprewContragent_id',
				name: 'Contragent_id',
				value: null,
				xtype: 'hidden'
			},*/ 
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['postavschik'],
				style: 'padding: 3px; margin-bottom:2px; display:block;',
				items:
				[{
					//anchor: '100%',
					width:500,
					fieldLabel: lang['postavschik'],
					allowBlank: false,
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 10,
					id: 'dprewContragent_sid',
					name: 'Contragent_sid',
					hiddenName:'Contragent_sid',
					listeners: {
						change: function(combo) {log(combo);
							this.setContragentCombo(combo, this.findById('dprewMol_sid'));
							
							if(isFarmacyInterface) { //только для аптек. Соблюдение принципа "один из контрагентов должен быть аптекой".
								var c_combo = this.findById('dprewContragent_tid');
								var m_combo = this.findById('dprewMol_tid');
								
								if (combo.getFieldValue('Contragent_id') == getGlobalOptions().Contragent_id) {
									if (c_combo.getFieldValue('Contragent_id') == getGlobalOptions().Contragent_id) {
										c_combo.setValue(null);
										this.setContragentCombo(c_combo, m_combo);
									}
								} else {
									if (c_combo.getFieldValue('Contragent_id') != getGlobalOptions().Contragent_id) {
										c_combo.setValue(getGlobalOptions().Contragent_id);
										this.setContragentCombo(c_combo, m_combo);
									}
								}
							}
                            if (combo.getFieldValue('ContragentType_Code') == 1) {//организация
                                this.filterFinCombo();
                            } else if (combo.getFieldValue('ContragentType_Code') == 2) {//отделение
                                this.drugIDFilt();
                            } else {
                                this.findById('dprewDrugfinance_id').getStore().reload();
                            }
						}.createDelegate(this)
					}
				},
				{
					//allowBlank: false,
					allowBlank: true, //временно для показа
					width:500,
					hiddenName: 'Mol_sid',
					fieldLabel: lang['mol_postavschika'],
					id: 'dprewMol_sid',
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
				style: 'padding: 3px; margin-bottom: 2px; display:block;',
				items:
				[{
					width:500,
					allowBlank: false,
					fieldLabel: lang['poluchatel'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DPREW + 12,
					id: 'dprewContragent_tid',
					name: 'Contragent_tid',
					hiddenName:'Contragent_tid',
					listeners: {
						change: function(combo) {
							this.setContragentCombo(combo, this.findById('dprewMol_tid'));
							
							if(isFarmacyInterface) { //только для аптек. Соблюдение принципа "один из контрагентов должен быть аптекой".
								var c_combo = this.findById('dprewContragent_sid');
								var m_combo = this.findById('dprewMol_sid');
								
								if (combo.getFieldValue('Contragent_id') == getGlobalOptions().Contragent_id) {
									if (c_combo.getFieldValue('Contragent_id') == getGlobalOptions().Contragent_id) {
										c_combo.setValue(null);
										this.setContragentCombo(c_combo, m_combo);
									}
								} else {
									if (c_combo.getFieldValue('Contragent_id') != getGlobalOptions().Contragent_id) {
										c_combo.setValue(getGlobalOptions().Contragent_id);
										this.setContragentCombo(c_combo, m_combo);
									}
								}
 							}
						}.createDelegate(this)
					}
				},
				{
					allowBlank: false,
					//allowBlank: true, //временно для показа
					width:500,
					fieldLabel: lang['mol_poluchatelya'],
					hiddenName: 'Mol_tid',
					id: 'dprewMol_tid',
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
						tabIndex: TABINDEX_DPREW + 14,
						xtype: 'swdrugfinancecombo',
						fieldLabel : lang['istochnik_finans'],
						name: 'Drugfinance_id',
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
						tabIndex: TABINDEX_DPREW + 14,
						xtype: 'swwhsdocumentcostitemtypecombo',
						fieldLabel : lang['statya_rashodov'],
						name: 'WhsDocumentCostItemType_id',
						id: 'dprewWhsDocumentCostItemType_id',
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
					width: 320,
					items:
					[{
						tabIndex: TABINDEX_DPREW + 14,
						xtype: 'textfield',
						fieldLabel : lang['nomer_dokumenta'],
						name: 'DocumentUc_Num',
						id: 'dprewDocumentUc_Num',
						width: 184,
						allowBlank:false
					}]
				},
				{
					layout: 'form',
					border: false,
					width: 320,
					items:
					[{
						tabIndex: TABINDEX_DPREW + 17,
						xtype: 'textfield',
						fieldLabel : lang['nomer_dogovora'],
						name: 'DocumentUc_DogNum',
						id: 'dprewDocumentUc_DogNum',
						width: 184,
						allowBlank:true
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
					width: 320,
					items: 
					[{
						fieldLabel : lang['data_podpisaniya'],
						tabIndex: TABINDEX_DPREW + 15,
						allowBlank: false,
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'DocumentUc_setDate',
						id: 'dprewDocumentUc_setDate',
						listeners: {
							change: function(field, newValue, oldValue) {
                                var tfg = Ext.getCmp('DokUcLpuEditWindow');
                                var index = tfg.findById('dprewContragent_sid').getFieldValue('ContragentType_Code');//Получатель
                                log(index);
                                if (index == 1) {
                                    form.filterFinCombo();
                                }
							}
						}
					}]
				},
				{
					layout: 'form',
					border: false,
					width: 320,
					items: 
					[{
						fieldLabel : lang['data_dogovora'],
						tabIndex: TABINDEX_DPREW + 18,
						allowBlank: true,
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'DocumentUc_DogDate',
						id: 'dprewDocumentUc_DogDate'
					}]
				}]
			},
			{
				fieldLabel : lang['data_postavki'],
				tabIndex: TABINDEX_DPREW + 16,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_didDate',
				id: 'dprewDocumentUc_didDate'
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
				{name: 'DocumentUc_id'},
				{name: 'DocumentUc_pid'},
				{name: 'Contragent_sid'},
				{name: 'Mol_sid'},
				{name: 'Contragent_tid'},
				{name: 'Mol_tid'},
				{name: 'DocumentUc_Num'},
				{name: 'DocumentUc_setDate'},
				{name: 'DocumentUc_didDate'},
				{name: 'DocumentUc_DogNum'},
				{name: 'DocumentUc_DogDate'},
				{name: 'DrugFinance_id'},
				{name: 'WhsDocumentCostItemType_id'}
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
				handler: function() 
				{
					this.ownerCt.doSave(true);
				},
				id: 'dprewOk',
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
			//items: [form.DokUcLpuEditForm, this.DocumentUcStrPanel]
			items:
			[
				form.DokUcLpuEditForm,
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
		sw.Promed.swDokUcLpuEditWindow.superclass.initComponent.apply(this, arguments);
		this.DocumentUcStrPanel.addListenersFocusOnFields();
	}
	});