/**
* swDloContragentEditWindow - окно редактирования/добавления контрагента (на основе swContragentEditWindow).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Salakhov R.
* @version      
* @comment      Префикс для id компонентов dctre (DloContragentEditForm)
*               tabIndex (firstTabIndex): 15200+1 .. 15300
*
*
* @input data: action - действие (add, edit, view)
*              Contragent_id - ID реестра
*/
/*NO PARSE JSON*/
sw.Promed.swDloContragentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	formARM: null,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	height: 456,
	layout: 'form',
	firstTabIndex: 15200,
	id: 'DloContragentEditWindow',
	codeRefresh: true,
	objectName: 'swDloContragentEditWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDloContragentEditWindow.js',
	listeners: {
		beforehide: function() {
			var contragent_id = this.findById('dctreContragent_id').getValue();

			if (!Ext.isEmpty(contragent_id) && !this.deleted && this.presave) {
				this.deleted = true;
				this.cancelContragent();
			}
		},
		hide: function() {
			this.callback(this.owner, -1);
		},
		beforeshow: function() {
			this.findById('dctreFillPanel').setVisible(true);
			this.findById('dctreOrgPanel').setVisible(false);
			this.findById('dctreOrgFarmacyPanel').setVisible(false);
			this.findById('dctreLpuSectionPanel').setVisible(false);
			this.findById('dctreMedServicePanel').setVisible(false);
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	openContragentDocument: function(action) {
		var wnd = this;
		var tf = Ext.getCmp('DloContragentEditWindow');
		if (wnd.findById('dctreContragent_id').getValue()==''){
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
			if (wnd.findById('dctreOrg_id').getValue() > 0) {
                params.Org_sid = wnd.findById('dctreOrg_id').getValue();
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
	doSave: function(flag) {
		var form = this.DloContragentEditForm;
		var contragenttype_id = form.findById('dctreContragentType_id').getValue();
		var contragent_id =this.findById('dctreContragent_id').getValue();

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
	submit: function(mode,onlySave, callback) {
		var form = this.DloContragentEditForm;
		var win = form.ownerCt;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		var params = {
			Contragent_id: form.findById('dctreContragent_id').getValue(),
			ContragentType_id: form.findById('dctreContragentType_id').getValue(),
			Org_id: form.findById('dctreOrg_id').getValue(),
			OrgFarmacy_id: form.findById('dctreOrgFarmacy_id').getValue(),
			LpuSection_id: form.findById('dctreLpuSection_id').getValue(),
			MedService_id: form.findById('dctreLpuSection_id').getValue(),
			Contragent_Code: form.findById('dctreContragent_Code').getValue(),
			Contragent_Name: form.findById('dctreContragent_Name').getValue(),
			Lpu_id: form.findById('dctreLpu_id').getValue()
		};

		form.getForm().submit({
			params: params,
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
				if (action.result) {
					if (action.result.Contragent_id) {
						//log(form.getForm().getValues());
						if (!onlySave || (onlySave!==1)) {
							win.hide();
							win.callback(win.owner, action.result.Contragent_id);
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
								html: lang['dannyie_o_kontragente_sohranenyi'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							if (!form.findById('dctreContragent_id').getValue()) {
								form.findById('dctreContragent_id').setValue(action.result.Contragent_id);
								win.DocumentPanel.params = {
									Contragent_id: form.findById('dctreContragent_id').getValue()
								};
								win.DocumentPanel.gFilters = {
									Contragent_id: form.findById('dctreContragent_id').getValue()
								};
							}
							
							if(callback && getPrimType(callback) == 'function') {
								callback();
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
	cancelContragent: function() {
		var params = {
			Contragent_id: this.findById('dctreContragent_id').getValue()
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
	enableEdit: function(enable) {
		var form = this;
		form.findById('dctreOrgPanel').setVisible(false);
		form.findById('dctreOrgFarmacyPanel').setVisible(false);
		form.findById('dctreLpuSectionPanel').setVisible(false);
		form.findById('dctreMedServicePanel').setVisible(false);
		form.findById('dctreFillPanel').setVisible(false);
		form.findById('dctreOrg_id').setAllowBlank(true);
		form.findById('dctreOrgFarmacy_id').setAllowBlank(true);
		form.findById('dctreLpuSection_id').setAllowBlank(true);
		form.findById('dctreMedService_id').setAllowBlank(true);
		
		var c_type = form.findById('dctreContragentType_id').getValue() * 1;
		var contragent_id = form.findById('dctreContragent_id').getValue();

		switch (c_type) {
			case 1: 
				form.findById('dctreOrgPanel').setVisible(true);
				form.findById('dctreOrg_id').setAllowBlank(false);
				break;
			case 2: 
				form.findById('dctreLpuSectionPanel').setVisible(true);
				form.findById('dctreLpuSection_id').setAllowBlank(false);
				break;
			case 3: 
				form.findById('dctreOrgFarmacyPanel').setVisible(true);
				form.findById('dctreOrgFarmacy_id').setAllowBlank(false);
				break;
			case 5:
				form.findById('dctreOrgPanel').setVisible(true);
				form.findById('dctreOrg_id').setAllowBlank(false);
				break;
			case 6:
				form.findById('dctreFillPanel').setVisible(true);
				break;
			case 7:
				form.findById('dctreMedServicePanel').setVisible(true);
				form.findById('dctreMedService_id').setAllowBlank(false);
				break;
			default:
				form.findById('dctreFillPanel').setVisible(true);
				break;
		}

		//Проверяется доступ пользователя к АРМ МЭК ЛЛО
		var isMEKLLO = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'mekllo'; }) > -1);
		var allowAccess = (isMEKLLO || getGlobalOptions().isMinZdrav || isSuperAdmin());

		form.findById('dctreContragent_id').disable();
		form.findById('dctreContragent_Code').disable();
		form.findById('dctreContragent_Name').disable();
		form.findById('dctreContragentType_id').disable();
		form.findById('dctreOrg_id').disable();
		form.findById('dctreOrgFarmacy_id').disable();
		form.findById('dctreLpuSection_id').disable();
		form.findById('dctreMedService_id').disable();

		if (form.action=='add' && enable) {
			form.findById('dctreContragentType_id').enable();
			form.findById('dctreContragent_Code').enable();
			form.findById('dctreContragent_Name').enable();
			form.findById('dctreOrg_id').enable();
			form.findById('dctreOrgFarmacy_id').enable();
			form.findById('dctreLpuSection_id').enable();
			form.findById('dctreMedService_id').enable();
		} else if (form.action=='edit' && enable && allowAccess) {
			form.findById('dctreContragent_Code').enable();
			form.findById('dctreContragent_Name').enable();
		}

		if (enable && (allowAccess||form.action=='add')) {
            form.DocumentPanel.setReadOnly(!isSuperAdmin() && !getGlobalOptions().isMinZdrav);
            form.buttons[0].enable();
        } else {
            form.DocumentPanel.setReadOnly(true);
            form.buttons[0].disable();
		}
	},
	setOrg: function(data, allowSetName) {
		var form = this;
		var combo = this.findById('dctreOrg_id');
		if (data['Org_id']) {
			combo.getStore().load({
				callback: function() {
					combo.setValue(data['Org_id']);
					if (allowSetName && form.findById('dctreContragentType_id').getValue()==5) {
						form.findById('dctreContragent_Name').setValue(combo.getFieldValue('Org_Nick'));
					}
					if (form.findById('dctreContragentType_id').getValue()==6) {
						form.findById('dctreFill_id').setValue(combo.getFieldValue('Org_Name'));
					}
					combo.fireEvent('change', combo);
				},
				params: {
					Org_id: data['Org_id']
				}
			});
		}
	},
	setOrgFarmacy: function(data, setfocus) {
		var form = this;
		var combo = this.findById('dctreOrgFarmacy_id');
		if (data['OrgFarmacy_id']) {
			combo.getStore().load({
				callback: function() {
					combo.setValue(data['OrgFarmacy_id']);
					if (form.findById('dctreContragentType_id').getValue()==5) {
						form.findById('dctreFill_id').setValue(combo.getFieldValue('OrgFarmacy_Name'));
					}
					combo.fireEvent('change', combo);
				},
				params: {
					OrgFarmacy_id: data['OrgFarmacy_id']
				}
			});
		}
	},
	setName: function() {
		var form = this;
		if (form.findById('dctreContragentType_id').getValue()==1) {
			form.findById('dctreContragent_Name').setValue(form.findById('dctreOrg_id').getRawValue());
		}
		if (form.findById('dctreContragentType_id').getValue()==2) {
			form.findById('dctreContragent_Name').setValue(form.findById('dctreLpuSection_id').getRawValue());
		}
		if (form.findById('dctreContragentType_id').getValue()==3) {
			form.findById('dctreContragent_Name').setValue(form.findById('dctreOrgFarmacy_id').getRawValue());
		}
		if (form.findById('dctreContragentType_id').getValue()==5) {
			form.findById('dctreContragent_Name').setValue(form.findById('dctreOrg_id').getFieldValue('Org_Nick'));
		}
		if (form.findById('dctreContragentType_id').getValue()==7) {
			form.findById('dctreContragent_Name').setValue(form.findById('dctreMedService_id').getRawValue());
		}
	},
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	setCode: function() {
		var form = this;
		// Запрос к серверу для получения нового кода
		form.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=Farmacy&m=generateContragentCode',
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result[0]) {
						form.findById('dctreContragent_Code').setValue(result[0].Contragent_Code);
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
		var contragent_name = form.findById('dctreContragent_Name').getValue();
		var contragent_code = form.findById('dctreContragent_Code').getValue();
		if (isFarmacyInterface) {
			var mass = [1,3,4];
		} else {
			var mass = [1,2,5]; // возможно 3 и 4 надо убрать
		}
		if (getGlobalOptions().lpu_type_code && getGlobalOptions().lpu_type_code.inlist([22,79])) {	//МО СМП
			mass.push(7);
		}
		var combo = form.findById('dctreContragentType_id');
		combo.getStore().clearFilter();
		combo.lastQuery = '';
		combo.getStore().filterBy(function(record) {
			if (value==record.get('ContragentType_Code')) {
				combo.fireEvent('select', combo, record, 0);
				combo.fireEvent('change', combo, value, '');

				form.findById('dctreContragent_Name').setValue(contragent_name);
				form.findById('dctreContragent_Code').setValue(contragent_code);
			}
			return (record.get('ContragentType_Code').inlist(mass));
		});
		if (value==0) {
			combo.fireEvent('change', combo, '', '');
		}
	},
	setFilterLS: function() {
		var form = this;
		var bf = form.DloContragentEditForm.getForm();
		var params = new Object();
		//params.isStac = true;
		if (swLpuSectionGlobalStore) {
			setLpuSectionGlobalStoreFilter(params);
			form.findById('dctreLpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}
	},
	show: function() {
		sw.Promed.swDloContragentEditWindow.superclass.show.apply(this, arguments);
		var form = this;				
		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.deleted = false;
		form.presave = false;
		form.focus();
		form.findById('DloContragentEditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].Contragent_id)
			form.Contragent_id = arguments[0].Contragent_id;
		else
			form.Contragent_id = null;
		if (arguments[0].ARMType) {
			form.ARMType = arguments[0].ARMType;
		}
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
			if ((form.Contragent_id) && (form.Contragent_id>0)) {
				form.action = "edit";
				if ((getGlobalOptions().Contragent_id != form.Contragent_id) && isFarmacyInterface) {
					form.action = 'view';
				}
			} else {
				form.action = "add";
			}
		}
		
		if (form.Contragent_id == null && form.action != "add") {
			sw.swMsg.show({title: lang['oshibka'], msg: lang['oshibka_otkryitiya_formyi_kontragent_ne_vyibran']});
			form.hide();
			return false;
		}

		form.enableEdit(form.action == "add");
		form.findById('DloContragentEditForm').getForm().setValues(arguments[0]);
		form.findById('DloContragentEditForm').getForm().findField('Org_id').getStore().baseParams.WithoutOrgEndDate = true;

		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (form.action) {
			case 'add':
				form.setTitle(lang['kontragentyi_dobavlenie']);
				form.enableEdit(true);
				loadMask.hide();
				form.findById('dctreContragentType_id').focus(true, 50);
				form.DocumentPanel.loadData({params:{Contragent_id:0}, globalFilters:{Contragent_id:0}, noFocusOnLoad: true});
				form.setTypeFilter(0);

				var contragent_type = 1; //по умолчанию тип контрагента - организация
				var combo = form.findById('dctreContragentType_id');
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
		
		if (!getGlobalOptions().OrgFarmacy_id) {
			form.setFilterLS();
		}
		
		if (form.action!='add') {
			form.findById('DloContragentEditForm').getForm().load({
				params: {
					Contragent_id: form.Contragent_id
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
					form.setOrg({Org_id:form.findById('dctreOrg_id').getValue()});
					form.setOrgFarmacy({OrgFarmacy_id:form.findById('dctreOrgFarmacy_id').getValue()});
					form.DocumentPanel.loadData({params:{Contragent_id:form.findById('dctreContragent_id').getValue()}, globalFilters:{Contragent_id:form.findById('dctreContragent_id').getValue()}, noFocusOnLoad:(form.action=='edit')});
					form.setTypeFilter(form.findById('dctreContragentType_id').getValue());
					form.findById('dctreLpuSection_id').fireEvent('change', form.findById('dctreLpuSection_id'), form.findById('dctreLpuSection_id').getValue());
					
					if (form.action=='edit') {
						form.enableEdit(true);
						form.findById('dctreContragentType_id').focus(true, 50);
					} else {
						form.focus();
						form.enableEdit(false);
					}
				},
				url: '/?c=Farmacy&m=loadContragentEdit'
			});
		}
	},
	
	initComponent: function() {
		// Форма с полями 
		var form = this;
		this.MainRecordAdd = function() {
			var tf = Ext.getCmp('DloContragentEditWindow');
			if (this.findById('dctreContragent_id').getValue()=='') {
				if (tf.doSave()) {
					tf.submit('add',1, function() {});
				}
			}
			return true;
		}
		
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
			id: 'dctreDocumentGrid',
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
		
		this.DloContragentEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DloContragentEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: 
			[{
				id: 'dctreContragent_id',
				name: 'Contragent_id',
				value: null,
				xtype: 'hidden'
			},
			{
				id: 'dctreLpu_id',
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
				id: 'dctreContragentType_id',
				listeners: {
					select: function(combo) {
						var win = Ext.getCmp('DloContragentEditWindow');
						var nV = combo.getValue();
						var code = combo.getFieldValue('ContragentType_Code');

						win.findById('dctreOrg_id').setAllowBlank(true);
						win.findById('dctreOrgFarmacy_id').setAllowBlank(true);
						win.findById('dctreLpuSection_id').setAllowBlank(true);
						win.findById('dctreMedService_id').setAllowBlank(true);
						win.findById('dctreFillPanel').setVisible(false);
						win.findById('dctreOrgPanel').setVisible(false);
						win.findById('dctreOrgFarmacyPanel').setVisible(false);
						win.findById('dctreLpuSectionPanel').setVisible(false);
						win.findById('dctreMedServicePanel').setVisible(false);
						win.findById('dctreContragent_Code').setValue(null);
						win.findById('dctreContragent_Name').setValue(null);
						win.DocumentPanel.getAction('action_add').setDisabled(true);

						switch (nV.toString())
						{
							case '1': 
								win.findById('dctreOrg_id').setAllowBlank(false);
								win.findById('dctreOrgPanel').setVisible(true);
								win.findById('dctreOrgFarmacy_id').setValue(null);
								win.findById('dctreLpuSection_id').setValue(null);
								win.findById('dctreMedService_id').setValue(null);
								win.DocumentPanel.getAction('action_add').setDisabled(false);
								break;
							case '2': 
								win.findById('dctreLpuSection_id').setAllowBlank(false);
								win.findById('dctreLpuSectionPanel').setVisible(true);
								win.findById('dctreOrg_id').setValue(null);
								win.findById('dctreOrgFarmacy_id').setValue(null);
								win.findById('dctreMedService_id').setValue(null);
								break;
							case '3': 
								win.findById('dctreOrgFarmacy_id').setAllowBlank(false);
								win.findById('dctreOrgFarmacyPanel').setVisible(true);
								win.findById('dctreOrg_id').setValue(null);
								win.findById('dctreLpuSection_id').setValue(null);
								win.DocumentPanel.getAction('action_add').setDisabled(false);
								break;
							case '5': 
								// Аптека МУ
								win.findById('dctreOrg_id').setAllowBlank(false);
								win.findById('dctreOrgPanel').setVisible(true);
								win.findById('dctreOrgFarmacy_id').setValue(null);
								win.findById('dctreLpuSection_id').setValue(null);
								win.findById('dctreMedService_id').setValue(null);
								if (win.action=='add' && !Ext.isEmpty(getGlobalOptions().lpu_id)) {
									win.setOrg({Org_id: getGlobalOptions().org_id}, true);
								}
								break;
							case '7': 
								// Служба
								win.findById('dctreMedService_id').setAllowBlank(false);
								win.findById('dctreMedServicePanel').setVisible(true);
								win.findById('dctreOrg_id').setValue(null);
								win.findById('dctreLpuSection_id').setValue(null);
								break;
							default:
								win.findById('dctreFillPanel').setVisible(true);
								win.findById('dctreOrg_id').setValue(null);
								win.findById('dctreOrgFarmacy_id').setValue(null);
								win.findById('dctreLpuSection_id').setValue(null);
								win.findById('dctreMedService_id').setValue(null);
								break;
						}

                        var orgtype_code = null;
                        switch (code.toString()) {
                            case '1': //Организация
                                orgtype_code = '16'; //Поставщик
                                break;
                            case '3': //Аптека
                                orgtype_code = '4'; //Аптека
                                break;
                            case '5': //МО
                                orgtype_code = '11'; //МО
                                break;
                            case '6': //Региональный склад
                                orgtype_code = '5'; //Региональный склад ДЛО
                                break;
                        }
                        win.findById('DloContragentEditForm').getForm().findField('Org_id').getStore().baseParams.OrgType_Code = orgtype_code;
                    }
				}
			}, 
			{
				xtype:'panel',
				layout: 'form',
				border: false,
				id: 'dctreOrgPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					anchor: '100%',
					border: true,
					enableKeyEvents: true,
					fieldLabel: lang['organizatsiya'],
					id: 'dctreOrg_id',
					name: 'Org_id',
					hiddenName: 'Org_id',
					editable: false,
					triggerAction: 'none',
					xtype: 'sworgcombo',
					tabIndex: form.firstTabIndex + 2,
					listeners: {
						'change': function() {
							//
						},
						keydown: function(inp, e)  {
							if (e.getKey() == e.DELETE || e.getKey() == e.F4) {
								e.stopEvent();
								if (e.browserEvent.stopPropagation) {
									e.browserEvent.stopPropagation();
								} else {
									e.browserEvent.cancelBubble = true;
								}

								if (e.browserEvent.preventDefault) {
									e.browserEvent.preventDefault();
								} else {
									e.browserEvent.returnValue = false;
								}
								e.returnValue = false;
								
								if (Ext.isIE) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch (e.getKey()) {
									case e.DELETE:
										inp.clearValue();
										inp.ownerCt.ownerCt.findById('dctreOrg_id').setRawValue(null);
										break;
									case e.F4:
										inp.onTrigger1Click();
										break;
								}
							}
						}
					},
					onTrigger1Click: function() {
						if (this.disabled)
							return false;
						var form = Ext.getCmp('DloContragentEditForm');
						var combo = this;
						if (!this.orgFormList) {
							this.orgFormList = new sw.Promed.swListSearchWindow(
							{
								title: lang['poisk_organizatsii'],
								id: 'OrgSearch',
								object: 'Org',
								editformclassname: 'swOrgEditWindow',
								store: this.getStore(),
								useBaseParams: true,
								disableActions: (getGlobalOptions().lpu_id>0?false:true)
							});
						}
						this.orgFormList.show({
							onSelect: function(data) {
								var win = Ext.getCmp('DloContragentEditWindow');
								win.setOrg(data, true);
							}, 
							onHide: function() {
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
				id: 'dctreOrgFarmacyPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['apteka'],
					id: 'dctreOrgFarmacy_id',
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
										inp.ownerCt.ownerCt.findById('dctreOrgFarmacy_id').setRawValue(null);
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
						var form = Ext.getCmp('DloContragentEditForm');
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
								var win = Ext.getCmp('DloContragentEditWindow');
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
				id: 'dctreLpuSectionPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					anchor: '100%',
					linkedElements: [ ],
					id: 'dctreLpuSection_id',
					hiddenName:'LpuSection_id',
					xtype: 'swlpusectionglobalcombo',
					tabIndex: form.firstTabIndex + 3,
					listeners: {
						change: function(combo, newValue, oldValue) {
							// выбрали отделение ЛПУ, берем код отделения и подставляем вместо кода контрагента (если код контрагента, еще не заведен)
							var code = this.DloContragentEditForm.getForm().findField('dctreContragent_Code');
							
							if (!(code.getValue()>0)) {
								this.findById('dctreContragent_Code').setValue(combo.getFieldValue('LpuSection_Code'));
							}
						}.createDelegate(this)
					}
				}]
			},
			{
				xtype:'panel',
				layout: 'form',
				border: true,
				id: 'dctreMedServicePanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: [{
					anchor: '100%',
					linkedElements: [ ],
					id: 'dctreMedService_id',
					hiddenName:'MedService_id',
					xtype: 'swmedservicecombo',
					tabIndex: form.firstTabIndex + 3,
					initComponent: function() {
						sw.Promed.SwMedServiceCombo.superclass.initComponent.apply(this, arguments);

						this.store = new Ext.data.Store({
							autoLoad: true,
							baseParams: this.params,
							reader:new Ext.data.JsonReader({
								id:'MedService_id'
							}, [
								{name: 'MedService_id', mapping: 'MedService_id'},
								{name: 'MedService_Name', mapping: 'MedService_Name'},
								{name: 'Lpu_id_Nick', mapping: 'Lpu_id_Nick'},
								{name: 'Address_Address', mapping: 'Address_Address'},
								{name: 'Org_id', mapping: 'Org_id'},
								{name: 'OrgStruct_id', mapping: 'OrgStruct_id'},
								{name: 'Lpu_id', mapping: 'Lpu_id'},
								{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
								{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
								{name: 'LpuSection_id', mapping: 'LpuSection_id'}
							]),
							url:'/?c=MedService&m=loadMedServiceListWithStorage'
						});
					}
				}]
			},
			{
				xtype:'panel',
				layout: 'form',
				border: true,
				id: 'dctreFillPanel',
				bodyStyle:'background:transparent;',
				labelWidth: 130,
				items: 
				[{
					xtype: 'descfield',
					fieldLabel: lang['naimenovanie'],
					id: 'dctreFill_id',
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
				id: 'dctreContragent_Code',
				allowBlank:false,
				triggerAction: 'all',
				triggerClass: 'x-form-plus-trigger',
				onTriggerClick: function() 
				{
					if (this.disabled)
						return false;
					Ext.getCmp('DloContragentEditWindow').setCode(this);
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
				id: 'dctreContragent_Name',
				allowBlank:false,
				triggerAction: 'all',
				triggerClass: 'x-form-equil-trigger',
				onTriggerClick: function() 
				{
					Ext.getCmp('DloContragentEditWindow').setName(this);
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
				success: function() {
					//
				}
			}, 
			[
				{ name: 'Contragent_id' },
				{ name: 'ContragentType_id' },
				{ name: 'Org_id' },
				{ name: 'OrgFarmacy_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedService_id' },
				{ name: 'Contragent_Code' },
				{ name: 'Contragent_Name' },
				{ name: 'Lpu_id' }
			]),
			url: '/?c=Farmacy&m=saveContragent'
		});
		Ext.apply(this, {
			border: false,
			buttons: 
			[{
				id: 'dctreSaveButton',
				handler: function() {
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
				handler: function()  {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [form.DloContragentEditForm, this.DocumentPanel]
		});
		sw.Promed.swDloContragentEditWindow.superclass.initComponent.apply(this, arguments);
	}
});