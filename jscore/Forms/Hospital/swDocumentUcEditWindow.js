/**
* swDocumentUcEditWindow - окно редактирования/добавления документа ввода остатков.
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
* @comment      Префикс для id компонентов duef (DocumentUcEditForm)
*               TABINDEX_DUEF = 8800;
*
*
* @input data: action - действие (add, edit, view)
*              DocumentUc_id - документ учета медикаментов
*/

sw.Promed.swDocumentUcEditWindow = Ext.extend(sw.Promed.BaseForm, {
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
	height: 600,
	layout: 'form',
	firstTabIndex: 15300,
	ARMType: null, 
	id: 'DocumentUcEditWindow',
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
	setDrugDocumentType: function() {
		var form = this.findById('duew_EditForm').getForm();
		var type_id = form.findField('DrugDocumentType_id').getValue()*1;

		if (type_id <= 0) {
			return false;
		}

		switch(type_id) {
			case 1: //Документ прихода/расхода  медикаментов
			case 6: //Приходная накладная
				form.findField('Contragent_sid').allowBlank = false;
				form.findField('Contragent_sid').ownerCt.show();
				break;
			case 3: //Документ ввода остатков
				form.findField('Contragent_sid').allowBlank = true;
				form.findField('Contragent_sid').ownerCt.hide();
				break;
		}

		if (type_id == 6 || type_id == 10) { //Приходная или расходная накладная
			this.DocumentUcStrPanel.getAction('action_duew_actions').show();
		} else {
			this.DocumentUcStrPanel.getAction('action_duew_actions').hide();
		}

		this.findById('duew_EditForm').ownerCt.doLayout();

		return true;
	},
	checkEmptyPrepSeries: function() { //проверяет наличие в спецификации позиций без серии, возвращает true, если таких позиций нет
		var result = true;
		this.DocumentUcStrPanel.getGrid().getStore().each(function(record){
			var ser = record.get('DocumentUcStr_Ser');
			if (!ser || ser == null || ser == '') {
				result = false;
				return false;
			}
		});
		return result;
	},
	doSave: function(flag) {
		var form = this.EditForm;
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
		if (flag==true) {
			var type_id = form.getForm().findField('DrugDocumentType_id').getValue()*1;

			if ((type_id == 6 || type_id == 10) && !this.checkEmptyPrepSeries()) { //Для приходной или расходной накладной делаем проверку на заполненность серии в позициях спецификации
				if (confirm(lang['ne_dlya_vseh_medikamentov_ukazana_seriya_prodoljit'])) {
					form.ownerCt.submit();
				}
			} else {
				form.ownerCt.submit();
			}
		}
		return true;
	},
	doDocExecute: function() {
		if (confirm(lang['posle_ispolneniya_redaktirovanie_dokumenta_stanet_nedostupno_prodoljit'])) {
			var tf = Ext.getCmp('DocumentUcEditWindow');
			if (tf.doSave()) {
				tf.submit('execute_doc');
			}
		}
		return false;
	},
	createDocumentUcStrList: function() {
		if (this.doSave()) {
			this.submit('create_by_contract', 1);
		}
	},
	copyDocumentUcStr: function() {
		var wnd = this;
		var record = wnd.DocumentUcStrPanel.getGrid().getSelectionModel().getSelected();

		if (record.get('DocumentUcStr_id') <= 0) {
			return false;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if (response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (success && response_obj.success) {
						wnd.DocumentUcStrPanel.edit_id = response_obj.DocumentUcStr_id;
						wnd.DocumentUcStrPanel.refreshRecords(null,0);
					} else {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При копировании возникла ошибка');
					}
				}
			},
			params: {
				DocumentUcStr_id: record.get('DocumentUcStr_id')
			},
			url: '/?c=Farmacy&m=copyDocumentUcStr'
		});
	},
	loadSpr: function(mode) {
		var form = this;
		form.findById('duefContragent_sid').getStore().load({
			params: {
				mode: 'sender'
			},
			callback: function() {
				form.findById('duefContragent_sid').setValue(form.findById('duefContragent_sid').getValue());
				// Читаем подчиненный Мол и проставляем 
				form.findById('duefContragent_sid').fireEvent('change',form.findById('duefContragent_sid'));
				
				form.findById('duefContragent_tid').getStore().load(
				{
					params:
					{
						mode: 'receiver'
					},
					callback: function() 
					{
						form.findById('duefContragent_tid').setValue(form.findById('duefContragent_tid').getValue());
						// Читаем подчиненный Мол и проставляем 
						form.findById('duefContragent_tid').fireEvent('change',form.findById('duefContragent_tid'));
						if (mode != 'view')
						{
							form.findById('duefContragent_sid').focus(true, 100);
						}
						if (mode != 'edit')
						{
							form.findById('duew_EditForm').getForm().clearInvalid();
						}
						
					}
				});
				
			}
		});
	},
	setFilterMol: function(combo, contragent)
	{
		form = this;
		//var combo = form.findById('edewMol_id');
		combo.getStore().load(
		{
			params: {Contragent_id: contragent.getValue()},
			callback: function() 
			{
				if (combo.getStore().getCount() == 1)
				{
					combo.setValue(combo.getStore().getAt(0).get('Mol_id'));
				}
				else 
				{
					if (combo.getStore().getCount() == 0)
					{
						combo.setValue(null);
					}
					else
					{
						if (combo.getValue()>0)
						{
							var value = combo.getValue();
							combo.setValue(null);
							combo.getStore().each(function (r)
							{
								if (r.get('Mol_id') == combo.getValue())
								{
									combo.setValue(combo.getValue());
								}
							});
						}
					}
				}
				
			}
		});
	},
	submit: function(mode,onlySave) 
	{
		var form = this.EditForm;
		var win = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		form.getForm().submit({
			params: {
				//DrugFinance_id: form.findById('duefDrugFinance_id').getValue()
			},
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code){
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
					if (action.result.Error_Msg){
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();				
				if (action.result) {
					if (action.result.DocumentUc_id) {
						//log(form.getForm().getValues());
						form.findById('duefDocumentUc_id').setValue(action.result.DocumentUc_id);
						win.DocumentUcStrPanel.setParam('DocumentUc_id',action.result.DocumentUc_id, false);
						win.DocumentUcStrPanel.setParam('DocumentUc_id',action.result.DocumentUc_id);
						
						if (mode == 'execute_doc') {														
							Ext.Ajax.request({
								callback: function(options, success, response) {
									if (response.responseText != '') {
										var response_obj = Ext.util.JSON.decode(response.responseText);							
										if (success && response_obj.success) {
											win.setAction('view');
											//alert('Накладная успешно создана');
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

						if (mode == 'create_by_contract') {
							getWnd('swWhsDocumentSupplySelectWindow').show({
								params: {
									//Contragent_sid: form.getForm().findField('Contragent_sid').getValue(),
									DrugFinance_id: form.getForm().findField('DrugFinance_id').getValue(),
									WhsDocumentCostItemType_id: form.getForm().findField('WhsDocumentCostItemType_id').getValue(),
									OrgSidOstatExists: true,
									WhsDocumentStatusType_Code: 2 //Действующие ГК
								},
								onSelect: function(selected_data) {
									Ext.Ajax.request({
										callback: function(options, success, response) {
											if (response.responseText != '') {
												var response_obj = Ext.util.JSON.decode(response.responseText);
												if (success && response_obj.success) {
													win.DocumentUcStrPanel.refreshRecords(null,0);
												} else {
													sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При создании списка медикаментов возникла ошибка');
												}
											}
										},
										params: {
											DocumentUc_id: action.result.DocumentUc_id,
											WhsDocumentSupply_id: selected_data.WhsDocumentSupply_id
										},
										url: '/?c=Farmacy&m=createDocumentUcStrListByWhsDocumentSupply'
									});
								}
							})

						}
						
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
								html: lang['dannyie_o_dokumente_vvoda_ostatkov_sohranenyi'],
								iconCls: 'info16',
								width: 250
							}).show(Ext.getDoc());
							
							switch(mode) {
								case 'add':
									win.DocumentUcStrPanel.run_function_add = false;
									win.DocumentUcStrPanel.ViewActions.action_add.execute();
								break;
								case 'edit':
									win.DocumentUcStrPanel.run_function_edit = false;
									win.DocumentUcStrPanel.ViewActions.action_edit.execute();
								break;						
							}
						}
						//после сохранения, "на лету" меняем окно добавления на окно редактирования
						win.setAction('edit');
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
	enableEdit: function(enable) {
		var wnd = this;
		var form = wnd.findById('duew_EditForm').getForm();
		var status_id = form.findField('DrugDocumentStatus_id').getValue();

		if (enable) {
			wnd.findById('duefDocumentUc_Num').enable();
			wnd.findById('duefDocumentUc_setDate').enable();
			wnd.findById('duefDocumentUc_didDate').enable();
			wnd.findById('duefDocumentUc_DogNum').enable();
			wnd.findById('duefDocumentUc_DogDate').enable();
			wnd.findById('duefContragent_sid').enable();
			//wnd.findById('duefMol_sid').enable();
			wnd.findById('duefContragent_tid').enable();
			//wnd.findById('duefMol_tid').enable();
			wnd.DocumentUcStrPanel.setReadOnly(false);
			wnd.DocumentUcStrPanel.getAction('action_duew_actions').enable();

			wnd.buttons[0].enable();
			if (status_id <= 1) { //Исполнение доступно только для документов со статусом "Новый"
				wnd.buttons[1].enable();
			} else {
				wnd.buttons[1].disable();
			}
		} else {
			wnd.findById('duefDocumentUc_Num').disable();
			wnd.findById('duefDocumentUc_setDate').disable();
			wnd.findById('duefDocumentUc_didDate').disable();
			wnd.findById('duefDocumentUc_DogNum').disable();
			wnd.findById('duefDocumentUc_DogDate').disable();
			wnd.findById('duefContragent_sid').disable();
			wnd.findById('duefMol_sid').disable();
			wnd.findById('duefContragent_tid').disable();
			wnd.findById('duefMol_tid').disable();
			wnd.DocumentUcStrPanel.setReadOnly(true);
			wnd.DocumentUcStrPanel.getAction('action_duew_actions').disable();

			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
		}
	},
	/*
	openDocumentUcStrEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('duew_EditForm').getForm();
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
	setAction: function(action) {
		var form = this;
		form.action = action;
		switch (form.action) {
			case 'add':
				form.setTitle(lang['uchet_medikamentov_dobavlenie']);
				form.enableEdit(true);
				break;
			case 'edit':
				form.setTitle(lang['uchet_medikamentov_redaktirovanie']);
				form.enableEdit(true);
				break;
			case 'view':
				form.setTitle(lang['uchet_medikamentov_prosmotr']);
				form.enableEdit(false);
				break;
		}
	},
	show: function() {
		sw.Promed.swDocumentUcEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		var base_form = form.findById('duew_EditForm').getForm();

		if(!form.DocumentUcStrPanel.getAction('action_duew_actions')) {
			form.DocumentUcStrPanel.addActions({
				name:'action_duew_actions',
				text:lang['deystviya'],
				menu: [{
					name: 'create_by_contract',
					iconCls: 'add16',
					text: lang['cozdat_na_osnove_spetsifikatsii_gk'],
					handler: form.createDocumentUcStrList.createDelegate(form)
				}, {
					name: 'copy_row',
					iconCls: 'copy16',
					text: lang['kopirovat_medikament'],
					handler: form.copyDocumentUcStr.createDelegate(form)
				}],
				iconCls: 'actions16'
			});
		}

		if (!arguments[0]) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		form.focus();
		form.findById('duew_EditForm').getForm().reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].DocumentUc_id) 
			form.DocumentUc_id = arguments[0].DocumentUc_id;
		else 
			form.DocumentUc_id = null;			
		if (arguments[0].callback)
			form.callback = arguments[0].callback;
		if (arguments[0].owner)
			form.owner = arguments[0].owner;
		if (arguments[0].onHide)
			form.onHide = arguments[0].onHide;
		if (arguments[0].action) {
			form.setAction(arguments[0].action);
		} else  {
			if ((form.DocumentUc_id) && (form.DocumentUc_id>0))
				form.setAction("edit");
			else 
				form.setAction("add");
		}
		if (arguments[0].ARMType) {
			form.ARMType = arguments[0].ARMType;
		}
		if (arguments[0] && arguments[0].filters) {
			base_form.setValues(arguments[0].filters);
		}

		//устанавливаем тип документа по умолчанию
		if (base_form.findField('DrugDocumentType_id').getValue() <= 0) {
			base_form.findField('DrugDocumentType_id').setValue(1); //Документ прихода/расхода  медикаментов
		}

		/*if (arguments[0] && arguments[0].disabled_fields) {
			for(var i = 0; i < arguments[0].disabled_fields.length(); i++)
				if (base_form.findField(arguments[0].disabled_fields[i]))
					base_form.findField(arguments[0].disabled_fields[i]).disable();
		}*/

		/*if (form.ARMType == "storehouse") {
			Ext.getCmp('duefBtnDocExecute').show();
		} else {
			Ext.getCmp('duefBtnDocExecute').hide();
		}*/
		Ext.getCmp('duefBtnDocExecute').show();

		this.setDrugDocumentType();
			
		form.findById('duew_EditForm').getForm().setValues(arguments[0]);
		var loadMask = new Ext.LoadMask(form.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		if (form.action =='add') {
			form.enableEdit(true);
			form.loadSpr(form.action);
			loadMask.hide();
			//form.getForm().clearInvalid();
			form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:null}, globalFilters:{DocumentUc_id:null}, noFocusOnLoad: true});
			form.setDrugDocumentType();
		} else {
			form.findById('duew_EditForm').getForm().load( {
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
					loadMask.hide();
					form.loadSpr(form.action);
					form.DocumentUcStrPanel.loadData({params:{DocumentUc_id:form.findById('duefDocumentUc_id').getValue(), mode:null}, globalFilters:{DocumentUc_id:form.findById('duefDocumentUc_id').getValue()}, noFocusOnLoad:true});
					if (form.action=='edit') {
						form.enableEdit(true);
					} else {
						form.focus();
						form.enableEdit(false);
					}
					form.setDrugDocumentType();

					// TODO: Удалить условие после того как все остатки будут правильно разнесены
					/*if ((form.findById('duefDrugFinance_id').getValue()!='') && (form.findById('duefDrugFinance_id').getValue()!=null))
					{
						form.findById('duefDrugFinance_id').disable();
					}
					else 
					{
						form.findById('duefDrugFinance_id').enable();
					}*/
				},
				url: '/?c=Farmacy&m=edit&method=DokUcLpu'
			});
		}
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		this.MainRecordAdd = function()
		{
			var tf = Ext.getCmp('DocumentUcEditWindow');
			if (tf.doSave())
			{
				tf.submit('add',1);
			}
			return false;
		}
		this.MainRecordEdit = function()
		{
			var tf = Ext.getCmp('DocumentUcEditWindow');
			if (tf.doSave())
			{
				tf.submit('edit',1);
			}
			return false;
		}
		
		/*
		this.AddRecord = function ()
		{
			var f = Ext.getCmp('DocumentUcEditWindow');
			var tf = f.DocumentUcStrPanel;
			// Можно добавить проверку на то , сохранен документ ранее или нет
			//if form.findById('duefDocumentUc_id').getValue()
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
		this.DocumentUcStrPanel = new sw.Promed.ViewFrame(
		{
			title:lang['medikamentyi'],
			id: 'DocumentUcStrGrid',
			border: true,
			region: 'center',
			object: 'DocumentUcStr',
			editformclassname: 'swDocumentUcStrEditWindow',//'swDocumentUcStrLpuEditWindow',
			dataUrl: '/?c=Farmacy&m=loadDocumentUcStrView',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true},
				{name: 'DocumentUc_id', hidden: true, isparams: true},
				{name: 'Drug_Name', id: 'autoexpand', header: lang['naimenovanie']},
				{name: 'DocumentUcStr_Count', width: 80, header: lang['kol-vo'], type: 'float'}, // +lang['ed_uch']+
				{name: 'DocumentUcStr_NdsPrice', width: 100, header: lang['tsena'], type: 'money', align: 'right'},
				{name: 'DocumentUcStr_NdsSum', width: 110, header: lang['summa'], type: 'money', align: 'right'},
				{name: 'DocumentUcStr_Ser', width: 120, header: lang['seriya']}, 
				{name: 'DocumentUcStr_godnDate', width: 110, header: lang['srok_godnosti'], type: 'date'},
				{name: 'PrepSeries_isDefect', width: 90, header: lang['falsifikat'], type: 'checkbox'}
			],
			actions:
			[
				{name:'action_add', func: form.MainRecordAdd.createDelegate(form)},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'}
			],
			onLoadData: function()
			{
				var win = Ext.getCmp('duew_EditForm');

				if (this.edit_id && this.edit_id > 0) {
					var idx = this.getIndexByValue({
						DocumentUcStr_id: this.edit_id
					});
					if (idx >= 0) {
						this.setSelectedIndex(idx);
					}
					this.focus();
					this.run_function_edit = false;
					this.ViewActions.action_edit.execute();
					this.edit_id = null;
				}
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('duew_EditForm');
			},
			focusOn: {name:'duefBtnSave',type:'button'},
			focusPrev: {name:'duefDocumentUc_didDate',type:'field'}
		});
		
		this.EditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'duew_EditForm',
			labelAlign: 'right',
			labelWidth: 131,
			items: [{
				id: 'duefDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			}, {
				id: 'duefContragent_id',
				name: 'Contragent_id',
				value: null,
				xtype: 'hidden'
			}, {
				name: 'DrugDocumentType_id',
				xtype: 'hidden'
			}, {
				name: 'DrugDocumentStatus_id',
				xtype: 'hidden'
			}, /*{ // Экспорт
				xtype: 'fieldset',				
				layout: 'column',
				autoHeight: true,
				title: lang['eksport'],
				style: 'padding: 5px;',
				width:600,
				labelAlign: 'right',
				labelWidth: 50,
				items: [{
					layout: 'form',
					items: [{
						style: "padding-left: 5px",
						xtype: 'button',
						id: 'duefBtnExport',
						text: lang['eksport'],
						handler: function() {
							alert(lang['vyizov_formyi_vyibora_dokumenta_dlya_eksporta']);
						}
					}]
				}, {
					layout: 'form',
					labelWidth: 5,
					items: [{
						width: 140,
						disabled: true,
						labelSeparator: '',
						xtype: 'textfield',
						id: 'duefExport_TotalInfo',
						name: 'Export_TotalInfo'
					}]
				}, {
					layout: 'form',
					labelWidth: 25,
					items: [{
						width: 80,
						disabled: true,
						fieldLabel: lang['№'],
						xtype: 'textfield',
						id: 'duefExport_DocNum',
						name: 'Export_DocNum'
					}]
				}, {
					layout: 'form',
					labelWidth: 35,
					items: [{
						width: 80,
						disabled: true,
						fieldLabel: lang['data'],
						xtype: 'textfield',
						id: 'duefExport_DocDate',
						name: 'Export_DocDate'
					}]
				}, {
					layout: 'form',
					items: [{
						width: 80,
						disabled: true,
						fieldLabel: lang['status'],
						xtype: 'textfield',
						id: 'duefDocumentUc_ExportDocStatus',
						name: 'DocumentUc_ExportDocStatus'
					}]
				}]
			}, { // Основание, Тип, Договор №
				layout: 'column',
				items: [{ //Основание
					tabIndex: form.firstTabIndex + 1,
					xtype: 'swcustomobjectcombo',
					fieldLabel : lang['osnovanie'],
					name: 'DrugDocumentMotivation_id',
					id: 'duefDrugDocumentMotivation_id',
					comboSubject: 'DrugDocumentMotivation',
					sortField: 'DrugDocumentMotivation_Code',
					allowBlank: true
				}, { //Тип
					xtype: 'swcustomobjectcombo',
					width: 120,
					id: 'duefSearch_DrugDocumentType',
					comboSubject: 'DrugDocumentType',
					sortField: 'DrugDocumentType_Code',
					fieldLabel: '<font style="color:red;">Вид документа</font>'
				}, { //Договор №
					width: 180,
					disabled: true,
					fieldLabel: lang['nomer_dogovora'],
					xtype: 'textfield',
					tabIndex: TABINDEX_DUEF + 5,
					id: 'duefDocumentUc_DogNum',
					name: 'DocumentUc_DogNum'
				}]
			}, { // Программа ЛЛО, Дата, Источник финансирования
				layout: 'column',
				items: [{ //Программа ЛЛО
					xtype: 'textfieldpmw',
					width: 120,
					id: 'duefSearch_FirName90',
					fieldLabel: lang['programma_llo']
				}, { //Дата
					disabled: true,
					fieldLabel: lang['data'],
					xtype: 'swdatefield',
					tabIndex: TABINDEX_DUEF + 6,
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					id: 'duefDocumentUc_DoсDate',
					name: 'DocumentUc_DoсDate'
				}, { //Источник финансирования
					id: 'duefDrugFinance_id',
					fieldLabel: lang['istochnik_finans'],
					width: 430,
					name: 'DrugFinance_id',
					value: null,
					lastQuery: '',
					tabIndex: TABINDEX_DUEF + 7,
					xtype: 'swdrugfinancecombo'
				}]
			},*/ { // Поставщик 
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['postavschik'],
				style: 'padding: 5px;',
				width:600,
				labelAlign: 'right',
				labelWidth: 125,
				items: 
				[{
					anchor: '100%',
					allowBlank: false,
					fieldLabel: lang['postavschik'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DUEF + 1,
					id: 'duefContragent_sid',
					name: 'Contragent_sid',
					hiddenName:'Contragent_sid',
					listeners:
					{
						change: function(combo) 
						{
							var form = combo.ownerCt.ownerCt.ownerCt; 
							form.findById('duefMol_sid').setDisabled(!(combo.getValue()>0));
							
							if (combo.getValue()>0)
							{
								// Договор доступен к заполнению если тип  = 1 или 2 
								var idx = combo.getStore().findBy(function(record) 
								{
									if (record.get('Contragent_id') == combo.getValue())
										return true;
								});
								if (idx>=0) {
									var row = combo.getStore().getAt(idx);									
									var mode = getDocumentUcMode(row.data);
									if (mode == 'income') { //приходный документ (income)
										// договор открыть к заполнению
										if (form.action != 'view') {
											form.findById('duefDocumentUc_DogNum').setDisabled(false);
											form.findById('duefDocumentUc_DogDate').setDisabled(false);
										}
										//form.findById('duefDrugFinance_id').setDisabled(false);
										form.DocumentUcStrPanel.setParam('mode', mode, false);
										form.DocumentUcStrPanel.setParam('Contragent_id', '', false);
										form.findById('duefMol_sid').setDisabled(true);
									} else { //расходный документ (expenditure)
										// договор закрыть
										//form.findById('duefDocumentUc_DogNum').setDisabled(true);
										//form.findById('duefDocumentUc_DogDate').setDisabled(true);
										//form.findById('duefDrugFinance_id').setDisabled(true);
										form.DocumentUcStrPanel.setParam('Contragent_id', row.get('Contragent_id'), false);
										form.DocumentUcStrPanel.setParam('mode', mode, false);
										form.findById('duefMol_sid').setDisabled(false);
									}
								}
								form.setFilterMol(form.findById('duefMol_sid'), combo);
							}
							else 
							{
								form.findById('duefMol_sid').getStore().removeAll();
								form.findById('duefMol_sid').setValue(null);
							}
						}
					}
				},  
				{
					allowBlank: true,
					anchor: '100%',
					hiddenName: 'Mol_sid',
					id: 'duefMol_sid',
					lastQuery: '',
					tabIndex: TABINDEX_DUEF + 2,
					xtype: 'swmolcombo'
				}]
			},
			{ // Потребитель 
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['potrebitel'],
				style: 'padding: 5px;',
				width:600,
				labelAlign: 'right',
				labelWidth: 125,
				items: 
				[{
					anchor: '100%',
					allowBlank: false,
					fieldLabel: lang['potrebitel'],
					xtype: 'swcontragentcombo',
					tabIndex: TABINDEX_DUEF + 3,
					id: 'duefContragent_tid',
					name: 'Contragent_tid',
					hiddenName:'Contragent_tid',
					listeners:
					{
						change: function(combo,record,index) 
						{
							var form = combo.ownerCt.ownerCt.ownerCt; 
							form.findById('duefMol_tid').setDisabled(!(combo.getValue()>0));
							
							var idx = combo.getStore().findBy(function(record) 
							{
								if (record.get('Contragent_id') == combo.getValue())
									return true;
							});
							if (idx>=0)
							{
								var row = combo.getStore().getAt(idx);
								if (row.get('ContragentType_id').inlist([1,3,4]))
								{
									form.findById('duefMol_tid').setDisabled(true);
									form.findById('duefMol_tid').getStore().removeAll();
									form.findById('duefMol_tid').setValue(null);
								}
								else 
								{
									form.findById('duefMol_tid').setDisabled(false);
									if (combo.getValue()>1) { // 1 = Пациент									
										form.setFilterMol(form.findById('duefMol_tid'), combo);
									} else  {
										form.findById('duefMol_tid').getStore().removeAll();
										form.findById('duefMol_tid').setValue(null);
									}
								}
							}
						}
					}
				},
				{
					allowBlank: true,
					anchor: '100%',
					hiddenName: 'Mol_tid',
					id: 'duefMol_tid',
					lastQuery: '',
					tabIndex: TABINDEX_DUEF + 4,
					xtype: 'swmolcombo'
				}]
			},
			{ // Договор
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['dogovor'],
				style: 'padding: 5px;',
				width:600,
				layout: 'column',
				items: 
				[{
					layout: 'form',
					border: false,
					columnWidth: .6,
					labelAlign: 'right',
					labelWidth: 125,
					items: 
					[{
						width: 180,
						disabled: true,
						fieldLabel: lang['nomer_dogovora'],
						xtype: 'textfield',
						tabIndex: TABINDEX_DUEF + 5,
						id: 'duefDocumentUc_DogNum',
						name: 'DocumentUc_DogNum'
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .4,
					labelAlign: 'right',
					labelWidth: 125,
					items: 
					[{
						disabled: true,
						fieldLabel: lang['data_dogovora'],
						xtype: 'swdatefield',
						tabIndex: TABINDEX_DUEF + 6,
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'duefDocumentUc_DogDate',
						name: 'DocumentUc_DogDate'
					}]
				}]
			}, {
				xtype: 'panel',
				border: true,
				style: 'padding: 1px;',
				width:600
			},
			{
				layout: 'column',
				labelWidth: 131,
				border: false,
				items:
					[{
						layout: 'form',
						border: false,
						width: 320,
						items: [{
							tabIndex: TABINDEX_DUEF + 7,
							xtype: 'swdrugfinancecombo',
							fieldLabel : lang['istochnik_finans'],
							name: 'DrugFinance_id',
							width: 180,
							allowBlank: false
						}]
					}, {
						layout: 'form',
						border: false,
						width: 320,
						items: [{
							tabIndex:  TABINDEX_DUEF + 7,
							xtype: 'swwhsdocumentcostitemtypecombo',
							fieldLabel : lang['statya_rashodov'],
							name: 'WhsDocumentCostItemType_id',
							width: 180,
							allowBlank: false
						}]
					}]
			},
			{
				tabIndex: TABINDEX_DUEF + 8,
				xtype: 'textfield',
				fieldLabel : lang['nomer_dokumenta'],
				name: 'DocumentUc_Num',
				id: 'duefDocumentUc_Num',
				allowBlank:false
			},
			{
				fieldLabel : lang['data_dokumenta'],
				tabIndex: TABINDEX_DUEF + 9,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_setDate',
				id: 'duefDocumentUc_setDate'
			},
			{
				fieldLabel : lang['data_postavki'],
				tabIndex: TABINDEX_DUEF + 10,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'DocumentUc_didDate',
				id: 'duefDocumentUc_didDate'
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
				{ name: 'DocumentUc_id' },
				{ name: 'DocumentUc_Num' },
				{ name: 'DocumentUc_setDate' },
				{ name: 'DocumentUc_didDate' },
				{ name: 'DrugDocumentType_id' },
				{ name: 'DrugDocumentStatus_id' },
				{ name: 'Contragent_tid' },
				{ name: 'Mol_tid' },
				{ name: 'Contragent_sid' },
				{ name: 'Mol_sid' },
				{ name: 'DocumentUc_DogNum' },
				{ name: 'DocumentUc_DogDate' },
				{ name: 'DrugFinance_id' },
				{ name: 'WhsDocumentCostItemType_id' }
			]),
			url: '/?c=Farmacy&m=saveDocumentUc'
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
				id: 'duefBtnSave',
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.ownerCt.doDocExecute();
				},
				iconCls: 'ok16',
				id: 'duefBtnDocExecute',
				text: lang['ispolnit']
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
			items:
			[
				form.EditForm,
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
		sw.Promed.swDocumentUcEditWindow.superclass.initComponent.apply(this, arguments);
		this.DocumentUcStrPanel.addListenersFocusOnFields();
	}
	});