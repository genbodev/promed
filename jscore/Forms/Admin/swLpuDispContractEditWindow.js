/**
* swLpuDispContractEditWindow - окно редактирования/добавления договоров по сторонним специалистам с другими ЛПУ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      май 2010
* @comment      Префикс для id компонентов ldcef (LpuDispContractEditForm)
*               tabIndex : TABINDEX_LDCEF = 9800
*
*
* @input data: action - действие (add, edit, view)
*              LpuDispContract_id - ID договора с другим лпу 
*/

sw.Promed.swLpuDispContractEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 700,
	layout: 'form',
	title: WND_LPUDISPCONTRACT_ADD,
	id: 'LpuDispContractEditWindow',
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
	doSave: function() 
	{
		var form = this.LpuDispContractForm;
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
		var setDate = form.findById('ldcefLpuDispContract_setDate').getValue();
		var disDate = form.findById('ldcefLpuDispContract_disDate').getValue();
		
		if(setDate && disDate && disDate < setDate){
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.findById('ldcefLpuDispContract_setDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: 'Дата окончания не может быть раньше даты начала',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		/*
		if ((setDate>getDate()))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('ldcefLpuDispContract_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_zaklyucheniya_dogovora_ne_mojet_byit_bolshe_tekuschey'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		*/
		form.ownerCt.submit();
		return true;
	},
	submit: function() 
	{
		var form = this.LpuDispContractForm;
		var base_form = form.getForm();
		var w = this;
		var params = {};

		if (base_form.findField('Lpu_id').disabled) {
			params.Lpu_id = base_form.findField('Lpu_id').getValue();
		}

		var formServiceContract = Ext.getCmp('LPEW_ServiceContract');
		var storeServiceContract = formServiceContract.getGrid().getStore();
		var arrServiceContract = [];
		var serviceContract = '';

		if(storeServiceContract.getCount() > 0){
			storeServiceContract.each(function(rec) {
				arrServiceContract.push(rec.data);
			});
		}
		serviceContract = JSON.stringify(arrServiceContract);
		params.serviceContractList = serviceContract;

		w.getLoadMask("Подождите, идет сохранение данных...").show();
		base_form.submit(
		{
			params: params,
			failure: function(result_form, action) 
			{
				w.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				w.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.LpuDispContract_id)
					{
						form.ownerCt.callback(form.ownerCt.owner, action.result.LpuDispContract_id);
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								w.hide();
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
	show: function() 
	{
		sw.Promed.swLpuDispContractEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		var base_form = form.LpuDispContractForm.getForm();

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
		base_form.reset();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;

		Ext.getCmp('LPEW_ServiceContract').getGrid().getStore().removeAll();

		if (arguments[0].LpuDispContract_id) 
			form.LpuDispContract_id = arguments[0].LpuDispContract_id;
		else 
			form.LpuDispContract_id = null;
			
		if (arguments[0].Lpu_id) 
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;
		
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
			if ((form.LpuDispContract_id) && (form.LpuDispContract_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}
		form.getLoadMask(LOAD_WAIT).show();
		form.syncSize();
		base_form.setValues(arguments[0]);
		base_form.clearInvalid();
		base_form.findField('SideContractType_id').fireEvent('change', base_form.findField('SideContractType_id'), base_form.findField('SideContractType_id').getValue());

		switch (form.action) 
		{
			case 'add':
				form.setTitle(WND_LPUDISPCONTRACT_ADD);
				form.enableEdit(true);
				this.getLoadMask().hide();
				form.findById('ldcefLpuDispContract_setDate').focus(true, 50);
				
				break;
			case 'edit':
				form.setTitle(WND_LPUDISPCONTRACT_EDIT);
				form.enableEdit(true);
				break;
			case 'view':
				form.setTitle(WND_LPUDISPCONTRACT_VIEW);
				form.enableEdit(false);
				break;
		}
		
		if (form.action!='add')
		{
			base_form.load(
			{
				params: 
				{
					LpuDispContract_id: form.LpuDispContract_id,
					Lpu_id: form.Lpu_id
				},
				failure: function() 
				{
					form.getLoadMask().hide();
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
					form.loadLpuSection();
					form.getLoadMask().hide();
					base_form.findField('Lpu_id').setValue(form.Lpu_id);
					base_form.findField('SideContractType_id').fireEvent('change', base_form.findField('SideContractType_id'), base_form.findField('SideContractType_id').getValue());
					if (form.action=='edit')
						form.findById('ldcefLpuDispContract_setDate').focus(true, 50);
					else 
						form.focus();
				},
				url: '/?c=LpuPassport&m=loadLpuDispContract'
			});
			Ext.getCmp('LPEW_ServiceContract').loadData({
				globalFilters:{LpuDispContract_id: form.LpuDispContract_id}, 
				params:{LpuDispContract_id: form.LpuDispContract_id},
				callback: function(){
					if(arguments[0].length == 0) Ext.getCmp('LPEW_ServiceContract').getGrid().getStore().removeAll();
				}
			});
		}
	},
	loadLpuSection: function()
	{
		var Lpu_oid = this.findById('ldcefLpu_oid').getValue();
		var LpuSectionProfile_id = this.findById('ldcefLpuSectionProfile_id').getValue();
		var LpuSectionCombo = this.findById('ldcefLpuSection_id');
		var LpuSection_id = this.findById('ldcefLpuSection_id').getValue();
		LpuSectionCombo.clearValue();
		if ((Lpu_oid>0) && (LpuSectionProfile_id>0))
		{
			if (this.action!='view')
				LpuSectionCombo.setDisabled(false);
			
			LpuSectionCombo.getStore().removeAll();
			LpuSectionCombo.getStore().load(
			{
				params: 
				{
					Lpu_id: Lpu_oid,
					LpuSectionProfile_id: LpuSectionProfile_id,
					mode: 'combo'
				},
				callback: function() 
				{
					if (LpuSection_id>0) 
					{
						LpuSectionCombo.getStore().each(function(record) 
						{
							if (record.data.LpuSection_id == LpuSection_id)
							{
								LpuSectionCombo.setValue(LpuSection_id);
							}
						});
					}
				}
			});
		}
		else 
		{
			LpuSectionCombo.setDisabled(true);
			LpuSectionCombo.getStore().removeAll();
		}
	},
	deleteServiceContract: function(){
		var sc = this.findById('LPEW_ServiceContract');
		var index = sc.getSelectedIndex();
		if(index >= 0){
			var rec = sc.getGrid().getStore().removeAt(index);
		}
	},
	openServiceContractEditWindow: function(action){
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swServiceContract').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования слуги договора уже открыто'));
			return false;
		}

		var params = {LpuDispContract_id: this.LpuDispContract_id};
		params.action = action;

		var formServiceContract = Ext.getCmp('LPEW_ServiceContract');
		var storeServiceContract = formServiceContract.getGrid().getStore();
		var arrServiceContract = [];

		if(storeServiceContract.getCount() > 0){
			storeServiceContract.each(function(rec) {
				arrServiceContract.push(rec.data);
			});
		}

		params.arrServiceContract = arrServiceContract;

		if(action == 'edit' || action == 'view'){
			var sc = this.findById('LPEW_ServiceContract');
			var index = sc.getSelectedIndex();
			if(index >= 0){
				var rec = sc.getGrid().getStore().getAt(index);
				params.UslugaCategory_id = rec.get('UslugaCategory_id');
				params.UslugaComplex_id = rec.get('UslugaComplex_id');
				params.UslugaComplexLink_Kolvo = rec.get('LpuDispContractUslugaComplexLink_Kolvo');
				params.LpuDispContractUslugaComplexLink_id = rec.get('LpuDispContractUslugaComplexLink_id');
				params.index = index;
			}
		}

		params.callback = function(dt){
			var grid = current_window.findById('LPEW_ServiceContract').getGrid();
			var data = dt;

			var r = new Array(new Ext.data.Record(data));

			if(data.index != null && data.index >= 0){
				var record = grid.getStore().getAt(index);
				if(record){
					record.set('LpuDispContractUslugaComplexLink_Kolvo', data.LpuDispContractUslugaComplexLink_Kolvo);
					record.set('LpuDispContractUslugaComplexLink_id', data.LpuDispContractUslugaComplexLink_id);
					record.set('UslugaComplex_id', data.UslugaComplex_id);
					record.set('UslugaCategory_id', data.UslugaCategory_id);
					record.set('UslugaCategory_Name', data.UslugaCategory_Name);
					record.set('UslugaComplex_Code', data.UslugaComplex_Code);
					record.set('UslugaComplex_Name', data.UslugaComplex_Name);
					record.commit();
				}
			}else{
				grid.getStore().add(r);
			}
			//grid.getStore().loadData([ data ], true);
			/*
			var head = [];
			grid.getStore().fields.eachKey(function(key, item) {
				head.push(key);
			});

			for ( i = 0; i < head.length; i++ ) {
				record.set(head[i], dd[head[i]]);
			}

			record.commit();
			*/
		}
		getWnd('swServiceContract').show(params);
	},
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;
		
		this.LpuDispContractForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'LpuDispContractEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			items: 
			[{
				id: 'ldcefLpuDispContract_id',
				name: 'LpuDispContract_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				allowBlank: false,
				fieldLabel: 'Дата начала',
				id: 'ldcefLpuDispContract_setDate',
				name: 'LpuDispContract_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_LDCEF + 1,
				listeners:{
					'change':function (field, newValue, oldValue) {
						var base_form = form.LpuDispContractForm.getForm(),
							index,
							LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

						// Фильтруем список профилей отделений
						base_form.findField('LpuSectionProfile_id').clearValue();
						base_form.findField('LpuSectionProfile_id').getStore().clearFilter();
						base_form.findField('LpuSectionProfile_id').lastQuery = '';

						form.findById('ldcefLpuSectionProfile_id').setBaseFilter(function (rec) {
							var setDate = form.findById('ldcefLpuDispContract_setDate').getValue();

							if (!Ext.isEmpty(setDate)) {
								return (Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
								&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
							} else {
								return true;
							}
						}.createDelegate(this));


						index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
						});

						if ( index >= 0 ) {
							base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
							base_form.findField('LpuSectionProfile_id').fireEvent('select', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getStore().getAt(index));
						}
					}
				},
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: true,
				fieldLabel: 'Дата окончания',
				id: 'ldcefLpuDispContract_disDate',
				name: 'LpuDispContract_disDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_LDCEF + 1,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: lang['nomer_dogovora'],
				id: 'ldcefLpuDispContract_Num',
				name: 'LpuDispContract_Num',
				tabIndex: TABINDEX_LDCEF + 2,
				width: 100,
				xtype: 'textfield'
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				anchor: '100%',
				items: [{
					layout: 'column',
					border: false,
					defaults: {border: false},
					items: [{
						layout: 'form',
						labelWidth: 110,
						items: [{
							allowBlank: false,
							fieldLabel: 'Сторона договора',
							hiddenName: 'SideContractType_id',
							listeners: {
								'change': function(combo, newValue) {
									var base_form = form.LpuDispContractForm.getForm();
									if (newValue == 2) {
										base_form.findField('SideContractType2_id').setValue(1);
									} else {
										base_form.findField('SideContractType2_id').setValue(2);
									}
								}
							},
							comboSubject: 'SideContractType',
							tabIndex: TABINDEX_LDCEF + 2,
							width: 120,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						columnWidth: 1,
						labelWidth: 100,
						items: [{
							allowBlank: true,
							disabled: true,
							listWidth: 400,
							fieldLabel: lang['mo'],
							anchor: '100%',
							hiddenName: 'Lpu_id',
							xtype: 'swlpulocalcombo',
							tabIndex: TABINDEX_LDCEF + 2
						}]
					}]
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				anchor: '100%',
				items: [{
					layout: 'column',
					border: false,
					defaults: {border: false},
					items: [{
						layout: 'form',
						labelWidth: 110,
						items: [{
							allowBlank: true,
							disabled: true,
							fieldLabel: 'Сторона договора',
							hiddenName: 'SideContractType2_id',
							comboSubject: 'SideContractType',
							tabIndex: TABINDEX_LDCEF + 2,
							width: 120,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						columnWidth: 1,
						labelWidth: 100,
						items: [{
							allowBlank: false,
							listWidth: 400,
							fieldLabel: lang['mo'],
							id: 'ldcefLpu_oid',
							anchor: '100%',
							hiddenName: 'Lpu_oid',
							xtype: 'swlpulocalcombo',
							tabIndex: TABINDEX_LDCEF + 3,
							listeners: {
								change: function (combo, nV) {
									var fm = Ext.getCmp('LpuDispContractEditWindow');
									fm.loadLpuSection();
								}
							}
						}, {
							allowBlank: false,
							listWidth: 400,
							fieldLabel: 'Тип договора',
							hiddenName: 'ContractType_id',
							comboSubject: 'ContractType',
							tabIndex: TABINDEX_LDCEF + 3,
							anchor: '100%',
							xtype: 'swcommonsprcombo'
						}, {
							allowBlank: false,
							listWidth: 400,
							anchor: '100%',
							name: 'LpuSectionProfile_id',
							id: 'ldcefLpuSectionProfile_id',
							tabIndex: TABINDEX_LDCEF + 4,
							xtype: 'swlpusectionprofilecombo',
							listeners: {
								change: function (combo, nV) {
									var fm = Ext.getCmp('LpuDispContractEditWindow');
									fm.loadLpuSection();
								}
							}
						}, {
							allowBlank: true,
							listWidth: 400,
							disabled: true,
							xtype: 'swlpusectionlitecombo',
							anchor: '100%',
							name: 'LpuSection_id',
							id: 'ldcefLpuSection_id',
							tabIndex: TABINDEX_LDCEF + 5
						}]
					}]
				}]
			},{
				items: [
					new sw.Promed.ViewFrame({
						actions: [
							{name: 'action_add', handler: function() {this.openServiceContractEditWindow('add');}.createDelegate(this)},
							{name: 'action_edit', handler: function() {this.openServiceContractEditWindow('edit');}.createDelegate(this)},
							{name: 'action_view', handler: function() {this.openServiceContractEditWindow('view');}.createDelegate(this)},
							{name: 'action_delete', handler: function() {this.deleteServiceContract();}.createDelegate(this)},
							{name: 'action_refresh', hidden: true},
							{name: 'action_print', hidden: true}
						],
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 150,
						autoLoadData: false,
						dataUrl: '/?c=LpuPassport&m=loadServiceContract',
						id: 'LPEW_ServiceContract',
						paging: false,
						region: 'center',
						stringfields: [
							{name: 'LpuDispContractUslugaComplexLink_id', type: 'int', header: 'ID', key: true},
							{name: 'UslugaComplex_id', type: 'int', hidden: true},
							{name: 'UslugaCategory_id', type: 'int', hidden: true},
							{name: 'UslugaCategory_Name', type: 'string', header: langs('Категория'), width: 170},
							{name: 'UslugaComplex_Code', type: 'string', header: langs('Код'), width: 170},
							{name: 'UslugaComplex_Name', type: 'string', header: langs('Наименование'), width: 170},
							{name: 'LpuDispContractUslugaComplexLink_Kolvo', type: 'string', header: langs('Количество'), width: 170}
						],
						title: langs('Услуги договора'),
						toolbar: true,
						totalProperty: 'totalCount',
						onRowSelect: function(sm,rowIdx,record)
						{
							var gridPanel = this;
							var actionsPanel = gridPanel.ViewActions;
							var isRecord = (record && !Ext.isEmpty(record.get('UslugaComplex_id')));
							
							if (actionsPanel) {
								gridPanel.ViewActions.action_edit.setDisabled(false);
								gridPanel.ViewActions.action_delete.setDisabled(false);
								gridPanel.ViewActions.action_view.setDisabled(false);
							}
							
						}
					})
				],
			}],
			keys: [{
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
				{ name: 'Lpu_id' },
				{ name: 'LpuDispContract_id' },
				{ name: 'LpuDispContract_setDate' },
				{ name: 'LpuDispContract_disDate' },
				{ name: 'SideContractType_id' },
				{ name: 'ContractType_id' },
				{ name: 'LpuDispContract_Num' },
				{ name: 'Lpu_oid' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuSection_id' }
			]),
			timeout: 600,
			url: '/?c=LpuPassport&m=saveLpuDispContract'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE,
				tabIndex: TABINDEX_LDCEF+7
			}, 
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_LDCEF+9),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL,
				tabIndex: TABINDEX_LDCEF+8
			}],
			items: [form.LpuDispContractForm]
		});
		sw.Promed.swLpuDispContractEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('ldcefLpuSectionProfile_id').setBaseFilter(function(rec) {
			var setDate = this.findById('ldcefLpuDispContract_setDate').getValue();

			if ( !Ext.isEmpty(setDate)) {
				return (Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
			} else {
				return true;
			}
		}.createDelegate(this));
	}
	});