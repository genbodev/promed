/**
* swContragentEditWindow - окно редактирования/добавления реестра (счета).
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
* @comment      Префикс для id компонентов ctre (ContragentEditForm)
*               tabIndex (firstTabIndex): 15200+1 .. 15300
*
*
* @input data: action - действие (add, edit, view)
*              Contragent_id - ID реестра
*/
/*NO PARSE JSON*/
sw.Promed.swContragentEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	formARM: null,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	height: 596,
	layout: 'form',
	firstTabIndex: 15200,
	id: 'ContragentEditWindow',
	codeRefresh: true,
	objectName: 'swContragentEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swContragentEditWindow.js',
	listeners: 
	{
		beforehide: function() {
			var contragent_id = this.findById('ctreContragent_id').getValue();

			if (!Ext.isEmpty(contragent_id) && !this.deleted && this.presave) {
				this.deleted = true;
				this.cancelContragent();
			}
		},
		hide: function() 
		{
			this.callback(this.owner, -1);
		},
		beforeshow: function()
		{
			this.findById('ctreFillPanel').setVisible(true);
			this.findById('ctreOrgPanel').setVisible(false);
			this.findById('ctreOrgFarmacyPanel').setVisible(false);
			this.findById('ctreLpuSectionPanel').setVisible(false);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	openContragentDocument: function(action) {
		var wnd = this;
		var tf = Ext.getCmp('ContragentEditWindow');
		if (wnd.findById('ctreContragent_id').getValue()==''){
			if (tf.doSave()){
				tf.submit('add', 1, function() {
					wnd.presave = true;
					wnd.openDoc(action);
				});
			}
		} else {
			wnd.openDoc(action);
		}
	},
	openDoc: function(action) {
		var wnd = this;
		var grid = wnd.DocumentPanel.getGrid();
		var params = new Object();
		
		params.owner = wnd;
		params.action = action;
		
        if (action == 'add') {
            if (wnd.findById('ctreOrg_id').getValue() > 0) {
                params.Org_sid = wnd.findById('ctreOrg_id').getValue();
            }
            params.callback = function() { wnd.DocumentPanel.refreshRecords(null,0); };
            getWnd('swSelectWhsDocumentTypeWindow').show({
                onSelect: function() {
                    if (arguments[0] && arguments[0].WhsDocumentType_id) {
                        params.WhsDocumentType_id = arguments[0].WhsDocumentType_id;
                        params.WhsDocumentType_Name = arguments[0].WhsDocumentType_Name;
                        getWnd('swWhsDocumentSupplyEditWindow').show(params);
                    }
                }
            });
            return;
        }
		
		if ( !grid.getSelectionModel().getSelected() )
			return;
		var record = grid.getSelectionModel().getSelected();

		
		if (record.get('DocumentType') == 'WhsDocumentSupply') {
			params.WhsDocumentSupply_id = record.get('Document_id');
			getWnd('swWhsDocumentSupplyEditWindow').show(params);
		}
		
		if (record.get('DocumentType') == 'WhsDocumentTitle') {
			params.WhsDocumentTitle_id = record.get('Document_id');
			getWnd('swWhsDocumentTitleEditWindow').show(params);
		}
	},
	doSave: function(flag)
	{
		var form = this.ContragentEditForm;
		var contragenttype_id = form.findById('ctreContragentType_id').getValue();
		var contragent_id =this.findById('ctreContragent_id').getValue();
		
		/*if (contragent_id != '' && contragent_id > 0 && (contragenttype_id == 2 || contragenttype_id == 3 || contragenttype_id == 5)) { //проверка есть ли хоть один мол
			var cnt = 0;
			this.MolPanel.getGrid().getStore().each(function(record) {
				if (record.data.Mol_id > 0) cnt++;
			});
			if (cnt < 1) {
				Ext.Msg.alert(lang['oshibka'], lang['dlya_dannogo_tipa_kontragentov_obyazatelno_nalichie_mol']);
				return false;
			}
		}*/
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
	submit: function(mode,onlySave, callback) 
	{
		var form = this.ContragentEditForm;
		var win = form.ownerCt;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		var params = {
			Contragent_id: form.findById('ctreContragent_id').getValue(),
			ContragentType_id: form.findById('ctreContragentType_id').getValue(),
			Org_id: form.findById('ctreOrg_id').getValue(),
			OrgFarmacy_id: form.findById('ctreOrgFarmacy_id').getValue(),
			LpuSection_id: form.findById('ctreLpuSection_id').getValue(),
			Contragent_Code: form.findById('ctreContragent_Code').getValue(),
			Contragent_Name: form.findById('ctreContragent_Name').getValue(),
			Lpu_id: form.findById('ctreLpu_id').getValue()
		};

		form.getForm().submit(
		{
			params: params,
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
					if (action.result.Contragent_id)
					{
						//log(form.getForm().getValues());
						if (!onlySave || (onlySave!==1))
						{
							win.hide();
							win.callback(win.owner, action.result.Contragent_id);
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
								html: lang['dannyie_o_kontragente_sohranenyi'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							if (!form.findById('ctreContragent_id').getValue())
							{
								form.findById('ctreContragent_id').setValue(action.result.Contragent_id);
								//log(action.result.Contragent_id);
								win.MolPanel.params =
								{
									Contragent_id: form.findById('ctreContragent_id').getValue()
								};
								win.MolPanel.gFilters =
								{
									Contragent_id: form.findById('ctreContragent_id').getValue()
								};
								win.DocumentPanel.params =
								{
									Contragent_id: form.findById('ctreContragent_id').getValue()
								};
								win.DocumentPanel.gFilters =
								{
									Contragent_id: form.findById('ctreContragent_id').getValue()
								};
							} else {
								if (mode=='edit')
								{
									win.MolPanel.run_function_edit = false;
									win.MolPanel.runAction('action_edit');
								}
							}
							
							if(callback && getPrimType(callback) == 'function') {
								callback();
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
	cancelContragent: function() {
		var params = {
			Contragent_id: this.findById('ctreContragent_id').getValue()
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление контрагента..."});
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=Farmacy&m=deleteContragent',
			success: function(response) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},
	enableEdit: function(enable) 
	{
		var form = this;
		form.findById('ctreOrgPanel').setVisible(false);
		form.findById('ctreOrgFarmacyPanel').setVisible(false);
		form.findById('ctreLpuSectionPanel').setVisible(false);
		form.findById('ctreFillPanel').setVisible(false);
		form.findById('ctreOrg_id').setAllowBlank(true);
		form.findById('ctreOrgFarmacy_id').setAllowBlank(true);
		form.findById('ctreLpuSection_id').setAllowBlank(true);
		
		var c_type = form.findById('ctreContragentType_id').getValue() * 1;
		var contragent_id = form.findById('ctreContragent_id').getValue();

		var allowMolEdit = (isUserGroup('LpuUser') || isLpuAdmin() || (form.ARMType=='merch' && getGlobalOptions().Contragent_id==contragent_id));

		switch (c_type) {
			case 1: 
				form.findById('ctreOrgPanel').setVisible(true);
				form.findById('ctreOrg_id').setAllowBlank(false);
				form.MolPanel.setReadOnly(/*getGlobalOptions().Contragent_id != contragent_id*/!enable || !allowMolEdit);
				break;
			case 2: 
				form.findById('ctreLpuSectionPanel').setVisible(true);
				form.findById('ctreLpuSection_id').setAllowBlank(false);
				form.MolPanel.setReadOnly(!enable || !allowMolEdit);
				break;
			case 3: 
				form.findById('ctreOrgFarmacyPanel').setVisible(true);
				form.findById('ctreOrgFarmacy_id').setAllowBlank(false);
				form.MolPanel.setReadOnly(!enable || !allowMolEdit);
				break;
			case 5:
				form.findById('ctreOrgPanel').setVisible(true);
				form.findById('ctreOrg_id').setAllowBlank(false);
				form.MolPanel.setReadOnly(!enable || !allowMolEdit);
				break;
			case 6:
				form.MolPanel.setReadOnly(!enable && !allowMolEdit);
				form.findById('ctreFillPanel').setVisible(true);
				break;
			default:
				form.MolPanel.setReadOnly(true);
				form.findById('ctreFillPanel').setVisible(true);
				break;
		}

		//var allowAccess = (isUserGroup('LpuUser') || getGlobalOptions().isMinZdrav || isLpuAdmin() || isSuperAdmin());
		//Проверяется доступ пользователя к АРМ МЭК ЛЛО
		var isMEKLLO = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'mekllo'; }) > -1);
		var allowAccess = (isMEKLLO || getGlobalOptions().isMinZdrav || isSuperAdmin());

		form.findById('ctreContragent_id').disable();
		form.findById('ctreContragent_Code').disable();
		form.findById('ctreContragent_Name').disable();
		form.findById('ctreContragentType_id').disable();
		form.findById('ctreOrg_id').disable();
		form.findById('ctreOrgFarmacy_id').disable();
		form.findById('ctreLpuSection_id').disable();

		if (form.action=='add' && enable) {
			form.findById('ctreContragentType_id').enable();
			form.findById('ctreContragent_Code').enable();
			form.findById('ctreContragent_Name').enable();
			form.findById('ctreOrg_id').enable();
			form.findById('ctreOrgFarmacy_id').enable();
			form.findById('ctreLpuSection_id').enable();
		} else if (form.action=='edit' && enable && allowAccess) {
			if(isSuperAdmin()){
				form.findById('ctreContragent_Code').enable();
				if(form.findById('ctreContragentType_id').getValue() == 2){
					form.findById('ctreContragent_Name').enable();
				}
			}
		}
	 	/*else  {
			form.findById('ctreContragent_id').disable();
			form.findById('ctreContragent_Code').disable();
			form.findById('ctreContragent_Name').disable();
			form.findById('ctreContragentType_id').disable();
			form.findById('ctreOrg_id').disable();
			form.findById('ctreOrgFarmacy_id').disable();
	 		form.findById('ctreLpuSection_id').disable();
		}*/

        if (enable && (allowAccess||form.action=='add')) {
            form.DocumentPanel.setReadOnly(!isSuperAdmin() && !getGlobalOptions().isMinZdrav);
            form.buttons[0].enable();
        } else {
            form.DocumentPanel.setReadOnly(true);
            form.buttons[0].disable();
        }
	},
	setOrg: function(data, allowSetName)
	{
		var form = this;
		var combo = this.findById('ctreOrg_id');
		if (data['Org_id'])
		{
			combo.getStore().load(
			{
				callback: function() 
				{
					combo.setValue(data['Org_id']);
					//combo.focus(true, 250);
					if (allowSetName && form.findById('ctreContragentType_id').getValue()==5) {
						form.findById('ctreContragent_Name').setValue(combo.getFieldValue('Org_Nick'));
					}
					if (form.findById('ctreContragentType_id').getValue()==6)
					{
						form.findById('ctreFill_id').setValue(combo.getFieldValue('Org_Name'));
					}
					combo.fireEvent('change', combo);
				},
				params: 
				{
					Org_id: data['Org_id']
				}
			});
		}
		/*
		combo.setValue(data['Org_id']);
		combo.setRawValue(data['Org_Name']);
		*/
	},
	setOrgFarmacy: function(data, setfocus)
	{
		var form = this;
		var combo = this.findById('ctreOrgFarmacy_id');
		if (data['OrgFarmacy_id'])
		{
			combo.getStore().load(
			{
				callback: function() 
				{
					combo.setValue(data['OrgFarmacy_id']);
					//combo.focus(true, 250);
					if (form.findById('ctreContragentType_id').getValue()==5)
					{
						form.findById('ctreFill_id').setValue(combo.getFieldValue('OrgFarmacy_Name'));
					}
					combo.fireEvent('change', combo);
				},
				params: 
				{
					OrgFarmacy_id: data['OrgFarmacy_id']
				}
			});
		}
	},
	setName: function()
	{
		var form = this;
		if (form.findById('ctreContragentType_id').getValue()==1)
		{
			form.findById('ctreContragent_Name').setValue(form.findById('ctreOrg_id').getRawValue());
		}
		if (form.findById('ctreContragentType_id').getValue()==2)
		{
			form.findById('ctreContragent_Name').setValue(form.findById('ctreLpuSection_id').getRawValue());
		}
		if (form.findById('ctreContragentType_id').getValue()==3)
		{
			form.findById('ctreContragent_Name').setValue(form.findById('ctreOrgFarmacy_id').getRawValue());
		}
		if (form.findById('ctreContragentType_id').getValue()==5)
		{
			form.findById('ctreContragent_Name').setValue(form.findById('ctreOrg_id').getFieldValue('Org_Nick'));
		}
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	setCode: function()
	{
		var form = this;
		// Запрос к серверу для получения нового кода
		form.getLoadMask().show();
		Ext.Ajax.request(
		{
			url: '/?c=Farmacy&m=generateContragentCode',
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result[0]) {
						form.findById('ctreContragent_Code').setValue(result[0].Contragent_Code);
					} else {
						Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_sgenerirovat_kod']);
					}
					form.getLoadMask().hide();
				}
			}
		});
	},
	setTypeFilter: function(value) {
		// Установка фильтра на поле "тип контрагента"
		var form = this;
		var contragent_name = form.findById('ctreContragent_Name').getValue();
		var contragent_code = form.findById('ctreContragent_Code').getValue();
		if (isFarmacyInterface) {
			var mass = [1,3,4];
		} else {
			var mass = [1,2,5]; // возможно 3 и 4 надо убрать
		}
		if (getGlobalOptions().lpu_type_code && getGlobalOptions().lpu_type_code.inlist([22,79])) {	//МО СМП
			mass.push(7);
		}
		var combo = form.findById('ctreContragentType_id');
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		combo.getStore().filterBy(function(record) 
		{
			if (value==record.get('ContragentType_Code'))
			{
				combo.fireEvent('select', combo, record, 0);
				combo.fireEvent('change', combo, value, '');

				form.findById('ctreContragent_Name').setValue(contragent_name);
				form.findById('ctreContragent_Code').setValue(contragent_code);
			}
			return (record.get('ContragentType_Code').inlist(mass));
		});
		if (value==0)
		{
			combo.fireEvent('change', combo, '', '');
		}
	},
	setFilterLS: function()
	{
		var form = this;
		var bf = form.ContragentEditForm.getForm();
		var params = new Object();
		//params.isStac = true;
		if (swLpuSectionGlobalStore) {
			setLpuSectionGlobalStoreFilter(params);
			form.findById('ctreLpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}
	},
	show: function() 
	{
		sw.Promed.swContragentEditWindow.superclass.show.apply(this, arguments);
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
		form.deleted = false;
		form.presave = false;
		form.focus();
		form.findById('ContragentEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].Contragent_id) 
			form.Contragent_id = arguments[0].Contragent_id;
		else
			form.Contragent_id = null;
		if (arguments[0].ARMType) {
			form.ARMType = arguments[0].ARMType;
		}
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
			if ((form.Contragent_id) && (form.Contragent_id>0))
			{
				form.action = "edit";
				if ((getGlobalOptions().Contragent_id != form.Contragent_id) && isFarmacyInterface)
				{
					form.action = 'view';
				}
			}
			else 
			{
				form.action = "add";
			}
		}
		
		if (form.Contragent_id == null && form.action != "add") {
			sw.swMsg.show({title: lang['oshibka'], msg: lang['oshibka_otkryitiya_formyi_kontragent_ne_vyibran']});
			form.hide();
			return false;
		}

		form.enableEdit(form.action == "add");
		form.findById('ContragentEditForm').getForm().setValues(arguments[0]);
		form.findById('ContragentEditForm').getForm().findField('Org_id').getStore().baseParams.WithoutOrgEndDate = true;

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) 
		{
			case 'add':
				form.setTitle(lang['kontragentyi_dobavlenie']);
				form.enableEdit(true);
				loadMask.hide();
				//form.getForm().clearInvalid();
				form.findById('ctreContragentType_id').focus(true, 50);
				form.MolPanel.loadData({params:{Contragent_id:0}, globalFilters:{Contragent_id:0}, noFocusOnLoad: true});
				form.DocumentPanel.loadData({params:{Contragent_id:0}, globalFilters:{Contragent_id:0}, noFocusOnLoad: true});
				form.setTypeFilter(0);

				var contragent_type = 1; //по умолчанию тип контрагента - организация
				var combo = form.findById('ctreContragentType_id');
				if (arguments[0].ContragentType_id && arguments[0].ContragentType_id > 0)
					contragent_type = arguments[0].ContragentType_id;
				
				combo.setValue(contragent_type);
				combo.getStore().each(function(record) {
					if (record.data.ContragentType_id == combo.getValue()) {
						  combo.fireEvent('select', combo, record, 0);
					}
				});
				
				break;
			case 'edit':
				form.setTitle(lang['kontragentyi_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['kontragentyi_prosmotr']);
				break;
		}
		
		if (!getGlobalOptions().OrgFarmacy_id)
			form.setFilterLS();
		
		if (form.action!='add')
		{
			form.findById('ContragentEditForm').getForm().load(
			{
				params: 
				{
					Contragent_id: form.Contragent_id
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
					form.setOrg({Org_id:form.findById('ctreOrg_id').getValue()});
					form.setOrgFarmacy({OrgFarmacy_id:form.findById('ctreOrgFarmacy_id').getValue()});
					form.MolPanel.loadData({params:{Contragent_id:form.findById('ctreContragent_id').getValue(), Contragent_Name:form.findById('ctreContragent_Name').getValue(), ContragentType_id:form.findById('ctreContragentType_id').getValue()}, globalFilters:{Contragent_id:form.findById('ctreContragent_id').getValue()}, noFocusOnLoad:(form.action=='edit')});
					form.DocumentPanel.loadData({params:{Contragent_id:form.findById('ctreContragent_id').getValue()}, globalFilters:{Contragent_id:form.findById('ctreContragent_id').getValue()}, noFocusOnLoad:(form.action=='edit')});					
					form.setTypeFilter(form.findById('ctreContragentType_id').getValue());
					form.findById('ctreLpuSection_id').fireEvent('change', form.findById('ctreLpuSection_id'), form.findById('ctreLpuSection_id').getValue());
					
					if (form.action=='edit') {
						form.enableEdit(true);
						form.findById('ctreContragentType_id').focus(true, 50);
					} else {
						form.focus();
						form.enableEdit(false);
					}
				},
				url: '/?c=Farmacy&m=loadContragentEdit'
			});
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		this.MainRecordAdd = function()
		{
			var tf = Ext.getCmp('ContragentEditWindow');
			if (this.findById('ctreContragent_id').getValue()=='')
			{
				if (tf.doSave())
				{
					tf.submit('add',1, function() {
						tf.AddRecordMol();
					});
				}
			} else {
				tf.AddRecordMol();
			}
			
			return true;
		}
		/*
		this.MainRecordEdit = function()
		{
			var tf = Ext.getCmp('ContragentEditWindow');
			if (tf.doSave())
			{
				tf.submit('edit',1);
			}
			return false;
		}
		*/
		
		this.AddRecordMol = function ()
		{
			var vf = Ext.getCmp('ContragentEditWindow').MolPanel;
			var params = new Object();
			params.action = 'add';
			params.callback = function() 
			{
				Ext.getCmp('ContragentEditWindow').MolPanel.loadData();
			};
			params.Contragent_id = this.findById('ctreContragent_id').getValue();
			params.ContragentType_id = this.findById('ctreContragentType_id').getValue();
			params.Contragent_Name = this.findById('ctreContragent_Name').getValue();
			params.ContragentType_id = this.findById('ctreContragentType_id').getValue();
			params.onHide = function() 
			{
				// TODO: Продумать использование getWnd в таких случаях
				getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
			};

			if (params.ContragentType_id != 2 && params.ContragentType_id != 5) { //если не отделение И не аптека МУ
				getWnd('swPersonSearchWindow').show({
					onClose: function() {
						this.MolPanel.loadData();
					}.createDelegate(this),
					onSelect: function(data)  {
						params.Person_id = data.Person_id;
						params.Person_FIO = (data.PersonSurName_SurName ? data.PersonSurName_SurName : '')+(data.PersonFirName_FirName ? ' '+data.PersonFirName_FirName : '')+(data.PersonSecName_SecName ? ' '+data.PersonSecName_SecName : '');
						getWnd('swPersonSearchWindow').hide();
						getWnd('swMolEditWindow').show(params);
					},
					searchMode: 'all'
				});
			} else {
				//отделение ЛПУ - просто выбирается МедПерсонал
				params.LpuSection_id = this.findById('ctreLpuSection_id').getValue();
				getWnd('swMolEditWindow').show(params);
			}
		}
		
		
		this.MolPanel = new sw.Promed.ViewFrame(
		{
			title:lang['materialno-otvetstvennoe_litso'],
			id: 'MolGrid',
			border: true,
			region: 'center',
			layout: '',
			height: 140,
			object: 'Mol',
			editformclassname: 'swMolEditWindow',
			dataUrl: '/?c=Farmacy&m=loadMolView',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'Mol_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', hidden: true, isparams: true},
				{name: 'Mol_Code', type:'int', header: lang['kod'], width: 80},
				{name: 'Person_FIO', id: 'autoexpand', header: lang['familiya_imya_otchestvo']},
				{name: 'Mol_begDT', type: 'date', width: 100, header: lang['data_nachala']},
				{name: 'Mol_endDT', type: 'date', width: 100, header: lang['data_okonchaniya']}
			],
			actions:
			[
				{name:'action_add', func: function () {Ext.getCmp('ContragentEditWindow').MainRecordAdd();}}, //, func: form.MainRecordAdd
				{name:'action_edit'},
				{name:'action_view'}
			],
			onLoadData: function()
			{
				var win = Ext.getCmp('ContragentEditForm');
				
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('ContragentEditForm');
			},
			focusOn: {name:'ctreSaveButton',type:'button'},
			focusPrev: {name:'ctreContragent_Name',type:'field'}
		});
		
		this.DocumentPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { this.openContragentDocument('add'); }.createDelegate(this)},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function() { this.openContragentDocument('view'); }.createDelegate(this)},
				{name: 'action_delete', hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Farmacy&m=loadContragentDocumentsList',
			height: 270,
			editformclassname: null,
			id: 'ctreDocumentGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'Doc_id', type: 'int', header: 'ID', key: true},
				{name: 'Document_id', type: 'int', hidden: true},
				{name: 'DocumentType', type: 'string', hidden: true},
				{name: 'Document_Num', type: 'string', header: lang['nomer'], width: 120},
				{name: 'Document_Name', id: 'autoexpand', type: 'string', header: lang['naimenovanie']},
				{name: 'Document_begDate', type: 'date', header: lang['data_nachala'], width: 120},
				{name: 'Document_endDate', type: 'date', header: lang['data_okonchaniya'], width: 120}
			],
			title: lang['dokumentyi'],
			toolbar: true
		});
		
		this.ContragentEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'ContragentEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[{
				id: 'ctreContragent_id',
				name: 'Contragent_id',
				value: null,
				xtype: 'hidden'
			},
			{
				id: 'ctreLpu_id',
				name: 'Lpu_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				anchor: '100%',
				allowBlank: false,
				xtype: 'contragenttypecombo',
				tabIndex: form.firstTabIndex + 1,
				name: 'ContragentType_id',
				id: 'ctreContragentType_id',
				listeners: 
				{
					select: function(combo)
					{
						var win = Ext.getCmp('ContragentEditWindow');
						var nV = combo.getValue();
						//var code = combo.getFieldValue('ContragentType_Code');
						
						win.findById('ctreOrg_id').setAllowBlank(true);
						win.findById('ctreOrgFarmacy_id').setAllowBlank(true);
						win.findById('ctreLpuSection_id').setAllowBlank(true);
						win.findById('ctreFillPanel').setVisible(false);
						win.findById('ctreOrgPanel').setVisible(false);
						win.findById('ctreOrgFarmacyPanel').setVisible(false);
						win.findById('ctreLpuSectionPanel').setVisible(false);
						win.findById('ctreContragent_Code').setValue(null);
						win.findById('ctreContragent_Name').setValue(null);
						win.DocumentPanel.getAction('action_add').setDisabled(true);

						var allowMolEdit = (isUserGroup('LpuUser') || isLpuAdmin() || (win.ARMType=='merch' && getGlobalOptions().Contragent_id==contragent_id));

						switch (nV.toString())
						{
							case '1': 
								win.findById('ctreOrg_id').setAllowBlank(false);
								win.findById('ctreOrgPanel').setVisible(true);
								win.findById('ctreOrgFarmacy_id').setValue(null);
								win.findById('ctreLpuSection_id').setValue(null);
								win.DocumentPanel.getAction('action_add').setDisabled(false);
								win.MolPanel.setReadOnly(!allowMolEdit);
								break;
							case '2': 
								win.findById('ctreLpuSection_id').setAllowBlank(false);
								win.findById('ctreLpuSectionPanel').setVisible(true);
								win.findById('ctreOrg_id').setValue(null);
								win.findById('ctreOrgFarmacy_id').setValue(null);
								win.MolPanel.setReadOnly(!allowMolEdit);
								if(win.action == 'edit' && isSuperAdmin()){
									win.findById('ctreContragent_Name').enable();
								}
								break;
							case '3': 
								win.findById('ctreOrgFarmacy_id').setAllowBlank(false);
								win.findById('ctreOrgFarmacyPanel').setVisible(true);
								win.findById('ctreOrg_id').setValue(null);
								win.findById('ctreLpuSection_id').setValue(null);
								win.DocumentPanel.getAction('action_add').setDisabled(false);
								win.MolPanel.setReadOnly(!allowMolEdit);
								break;
							case '5': 
								// Аптека МУ
								win.findById('ctreOrg_id').setAllowBlank(false);
								win.findById('ctreOrgPanel').setVisible(true);
								win.findById('ctreOrgFarmacy_id').setValue(null);
								win.findById('ctreLpuSection_id').setValue(null);
								if (win.action=='add' && !Ext.isEmpty(getGlobalOptions().lpu_id)) {
									win.setOrg({Org_id: getGlobalOptions().org_id}, true);
								}
								win.MolPanel.setReadOnly(!allowMolEdit);
								break;
							default:
								win.findById('ctreFillPanel').setVisible(true);
								win.findById('ctreOrg_id').setValue(null);
								win.findById('ctreOrgFarmacy_id').setValue(null);
								win.findById('ctreLpuSection_id').setValue(null);
								win.MolPanel.setReadOnly(!allowMolEdit);
								break;
						}
					}
				}
			}, 
			{
				xtype:'panel',
				layout: 'form',
				border: false,
				id: 'ctreOrgPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					anchor: '100%',
					border: true,
					enableKeyEvents: true,
					fieldLabel: lang['organizatsiya'],
					id: 'ctreOrg_id',
					name: 'Org_id',
					hiddenName: 'Org_id',
					editable: false,
					triggerAction: 'none',
					xtype: 'sworgcombo',
					tabIndex: form.firstTabIndex + 2,
					listeners: 
					{
						'change': function() 
						{
							//
						},
						keydown: function(inp, e) 
						{
							if (e.getKey() == e.DELETE || e.getKey() == e.F4)
							{
								e.stopEvent();
								if (e.browserEvent.stopPropagation)
								{
									e.browserEvent.stopPropagation();
								}
								else
								{
									e.browserEvent.cancelBubble = true;
								}
								if (e.browserEvent.preventDefault)
								{
									e.browserEvent.preventDefault();
								}
								else
								{
									e.browserEvent.returnValue = false;
								}
								e.returnValue = false;
								
								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch (e.getKey())
								{
									case e.DELETE:
										inp.clearValue();
										inp.ownerCt.ownerCt.findById('ctreOrg_id').setRawValue(null);
										break;
									case e.F4:
										inp.onTrigger1Click();
										break;
								}
							}
						}
					},
					onTrigger1Click: function() 
					{
						if (this.disabled)
							return false;
						var form = Ext.getCmp('ContragentEditForm');
						var combo = this;
						if (!this.orgFormList)
						{
							this.orgFormList = new sw.Promed.swListSearchWindow(
							{
								title: lang['poisk_organizatsii'],
								id: 'OrgSearch',
								object: 'Org',
								editformclassname: 'swOrgEditWindow',
								disableActions: (getGlobalOptions().lpu_id>0?false:true),
								//dataUrl: '/?c=DrugRequest&m=index&method=getPersonGrid',
								//stringfields: 
								//[
								//	{name: 'DrugProtoMnn_id', key: true},
								//	{name: 'DrugProtoMnn_Name', id: 'autoexpand', header: 'Наименование'},
								//	{name: 'Lpu_Nick', hidden: false, header: 'ЛПУ прикрепления', width: 100}
								//],
								store: this.getStore(),
								useBaseParams: true
							});
						}
						this.orgFormList.show(
						{
							onSelect: function(data) 
							{
								var win = Ext.getCmp('ContragentEditWindow');
								win.setOrg(data, true);
							}, 
							onHide: function() 
							{
								combo.focus(false);
							}
						});
						return false;
					}
				}]
			},
			{
				xtype:'panel',
				layout: 'form',
				border: true,
				id: 'ctreOrgFarmacyPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['apteka'],
					id: 'ctreOrgFarmacy_id',
					name: 'OrgFarmacy_id',
					hiddenName: 'OrgFarmacy_id',
					editable: false,
					triggerAction: 'none',
					xtype: 'sworgfarmacyadvcombo',
					tabIndex: form.firstTabIndex + 3,
					listeners: 
					{
						'change': function() 
						{
							//
						},
						keydown: function(inp, e) 
						{
							if (e.getKey() == e.DELETE || e.getKey() == e.F4)
							{
								e.stopEvent();
								if (e.browserEvent.stopPropagation)
								{
									e.browserEvent.stopPropagation();
								}
								else
								{
									e.browserEvent.cancelBubble = true;
								}
								if (e.browserEvent.preventDefault)
								{
									e.browserEvent.preventDefault();
								}
								else
								{
									e.browserEvent.returnValue = false;
								}

								e.returnValue = false;

								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch (e.getKey())
								{
									case e.DELETE:
										inp.clearValue();
										inp.ownerCt.ownerCt.findById('ctreOrgFarmacy_id').setRawValue(null);
										break;
									case e.F4:
										inp.onTrigger1Click();
										break;
								}
							}
						}
					},
					onTrigger1Click: function() 
					{
						if (this.disabled)
							return false;
						var form = Ext.getCmp('ContragentEditForm');
						var combo = this;
						if (!this.farmacyFormList)
						{
							this.farmacyFormList = new sw.Promed.swListSearchWindow(
							{
								title: lang['poisk_apteki'],
								id: 'OrgFarmacySearch',
								object: 'OrgFarmacy',
								editformclassname: 'swOrgFarmacyEditWindow',
								store: this.getStore()
							});
						}
						this.farmacyFormList.show(
						{
							onSelect: function(data) 
							{
								var win = Ext.getCmp('ContragentEditWindow');
								win.setOrgFarmacy(data);
							}, 
							onHide: function() 
							{
								combo.focus(false);
							}
						});
						return false;
					}
				}]
			},
			{
				xtype:'panel',
				layout: 'form',
				border: true,
				id: 'ctreLpuSectionPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					anchor: '100%',
					linkedElements: [ ],
					id: 'ctreLpuSection_id',
					hiddenName:'LpuSection_id',
					xtype: 'swlpusectionglobalcombo',
					tabIndex: form.firstTabIndex + 3,
					listeners: 
					{
						change: function(combo, newValue, oldValue)
						{
							this.MolPanel.setParam('LpuSection_id', newValue, false);
							// выбрали отделение ЛПУ, берем код отделения и подставляем вместо кода контрагента (если код контрагента, еще не заведен)
							var code = this.ContragentEditForm.getForm().findField('ctreContragent_Code');
							
							if (!(code.getValue()>0))
							{
								this.findById('ctreContragent_Code').setValue(combo.getFieldValue('LpuSection_Code'));
							}
						}.createDelegate(this)
					}
				}]
			},
			{
				xtype:'panel',
				layout: 'form',
				border: true,
				id: 'ctreFillPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					xtype: 'descfield',
					fieldLabel: lang['naimenovanie'],
					id: 'ctreFill_id',
					tabIndex: form.firstTabIndex + 3
				}]
			},
			{
				tabIndex: form.firstTabIndex + 4,
				fieldLabel : lang['kod'],
				name: 'Contragent_Code',
				xtype: 'trigger',
				maxValue: 999999,
				minValue: 0,
				maskRe: /\d/,
				autoCreate: {tag: "input", size:14, maxLength: "6", autocomplete: "off"},
				id: 'ctreContragent_Code',
				allowBlank:false,
				triggerAction: 'all',
				triggerClass: 'x-form-plus-trigger',
				onTriggerClick: function() 
				{
					if (this.disabled)
						return false;
					Ext.getCmp('ContragentEditWindow').setCode(this);
				},
				enableKeyEvents:true,
				listeners:
				{
					keydown: function(inp, e) 
					{
						if (e.getKey() == e.F2)
						{
							this.onTriggerClick();
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							e.stopEvent(); 
						}
					}
				}
			},
			{
				anchor: '100%',
				tabIndex: form.firstTabIndex + 5,
				fieldLabel : lang['naimenovanie'],
				name: 'Contragent_Name',
				xtype: 'trigger',
				id: 'ctreContragent_Name',
				allowBlank:false,
				triggerAction: 'all',
				triggerClass: 'x-form-equil-trigger',
				onTriggerClick: function() 
				{
					Ext.getCmp('ContragentEditWindow').setName(this);
				},
				enableKeyEvents:true,
				listeners:
				{
					keydown: function(inp, e) 
					{
						if (e.getKey() == e.F2)
						{
							this.onTriggerClick();
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							e.stopEvent(); 
						}
					}
				}
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
				{ name: 'Contragent_id' },
				{ name: 'ContragentType_id' },
				{ name: 'Org_id' },
				{ name: 'OrgFarmacy_id' },
				{ name: 'LpuSection_id' },
				{ name: 'Contragent_Code' },
				{ name: 'Contragent_Name' },
				{ name: 'Lpu_id' }
			]),
			url: '/?c=Farmacy&m=saveContragent'
		});
		Ext.apply(this, 
		{
			border: false,
			buttons: 
			[{
				id: 'ctreSaveButton',
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
			items: [form.ContragentEditForm, this.MolPanel, this.DocumentPanel]
		});
		sw.Promed.swContragentEditWindow.superclass.initComponent.apply(this, arguments);
		form.MolPanel.addListenersFocusOnFields();
	}
	});