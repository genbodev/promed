/**
 * swRegistryLLOEditWindow - окно редактирования реестра рецептов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Salakhov R.
 * @version      07.2015
 * @comment
 */
sw.Promed.swRegistryLLOEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Реестр рецептов',
	layout: 'border',
	id: 'RegistryLLOEditWindow',
	modal: true,
	shim: false,
	width: 600,
	height: 250,
	resizable: false,
	maximizable: false,
	maximized: false,
	setDisabled: function(disabled) {
		var wnd = this;

		var field_arr = [
			'KatNasel_id',
			'RegistryLLO_Date_Range',
			'DrugFinance_id',
			'WhsDocumentCostItemType_id',
			'WhsDocumentSupply_id'
		];

		for (var i in field_arr) if (wnd.form.findField(field_arr[i])) {
			var field = wnd.form.findField(field_arr[i]);
			if (disabled) {
				field.disable();
			} else {
				field.enable();
			}
		}

		if (disabled) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('RegistryLLOEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function() {
		var wnd = this;
		var params = new Object();

		if (this.action == 'add') {
			params.RegistryType_Code = '3'; //3 - Рецепты.
			params.RegistryStatus_id = null;
			params.RegistryLLO_accDate = (new Date()).format('d.m.Y');
			params.Org_id = getGlobalOptions().org_id;
		}

		wnd.getLoadMask('Подождите, идет сохранение...').show();
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result && action.result.RegistryLLO_id > 0) {
					var id = action.result.RegistryLLO_id;
					wnd.form.findField('RegistryLLO_id').setValue(id);
					wnd.callback(wnd.owner, id);
					wnd.hide();
				}
			}
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swRegistryLLOEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.RegistryLLO_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].RegistryLLO_id ) {
			this.RegistryLLO_id = arguments[0].RegistryLLO_id;
		}

		this.setTitle("Реестр рецептов");
		this.form.reset();
		this.setDisabled(this.action == 'view');

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
		loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));

				Ext.Ajax.request({
					params:{
						RegistryLLO_id: wnd.RegistryLLO_id
					},
					failure:function () {
						sw.swMsg.alert('Ошибка', 'Не удалось получить данные с сервера');
						loadMask.hide();
						wnd.hide();
					},
					success: function (response) {
						loadMask.hide();

						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false
						}

						wnd.form.setValues(result[0]);
						wnd.supply_combo.setValueById(result[0].WhsDocumentSupply_id);

						if (!Ext.isEmpty(result[0].RegistryLLO_begDate) || !Ext.isEmpty(result[0].RegistryLLO_endDate)) {
							wnd.form.findField('RegistryLLO_Date_Range').setValue(result[0].RegistryLLO_begDate + ' - ' + result[0].RegistryLLO_endDate);
						}
					},
					url:'/?c=RegistryLLO&m=load'
				});
				break;
		}
	},
	initComponent: function() {
		var wnd = this;

		wnd.supply_combo = new sw.Promed.SwBaseRemoteCombo({
			width: 300,
			allowBlank: true,
			displayField: 'WhsDocumentUc_Num',
			enableKeyEvents: true,
			fieldLabel: 'Контракт',
			forceSelection: false,
			hiddenName: 'WhsDocumentSupply_id',
			loadingText: 'Идет поиск...',
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			trigger2Class: 'x-form-search-trigger',
			resizable: true,
			selectOnFocus: true,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{WhsDocumentUc_Num}</h3></td><td style="width:20%;"></td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'WhsDocumentSupply_id',
			onTrigger2Click: function() {
				if (this.disabled)
					return false;

				var searchWindow = 'swWhsDocumentSupplySelectWindow';
				var params = this.getStore().baseParams;
				var combo = this;
				combo.disableBlurAction = true;
				getWnd(searchWindow).show({
					params: params,
					searchUrl: '/?c=Farmacy&m=loadWhsDocumentSupplyList',
					FilterPanelEnabled: true,
					onHide: function() {
						combo.focus(false);
						combo.disableBlurAction = false;
					},
					onSelect: function (data) {
						combo.fireEvent('beforeselect', combo);

						combo.getStore().removeAll();
						combo.getStore().loadData([{
							WhsDocumentSupply_id: data.WhsDocumentSupply_id,
							WhsDocumentUc_Name: data.WhsDocumentUc_Name,
							WhsDocumentUc_Date: data.WhsDocumentUc_Date,
							WhsDocumentUc_Num: data.WhsDocumentUc_Num,
							Contragent_sid: data.Contragent_sid,
							DrugFinance_id: data.DrugFinance_id,
							WhsDocumentCostItemType_id: data.WhsDocumentCostItemType_id
						}], true);

						combo.setValue(data.WhsDocumentSupply_id);
						var index = combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentSupply_id') == data.WhsDocumentSupply_id; });

						if (index == -1) {
							return false;
						}

						var record = combo.getStore().getAt(index);

						if ( typeof record == 'object' ) {
							combo.fireEvent('select', combo, record, 0);
							combo.fireEvent('change', combo, record.get('WhsDocumentSupply_id'));
						}

						getWnd(searchWindow).hide();
					}
				});
			},
			resetCombo: function() {
				this.lastQuery = '';
				this.getStore().removeAll();
				this.getStore().baseParams.query = '';
			},
			setValueById: function(document_id) {
				var combo = this;
				combo.store.baseParams.WhsDocumentSupply_id = document_id;
				combo.store.load({
					callback: function(){
						combo.setValue(document_id);
						combo.store.baseParams.WhsDocumentSupply_id = null;
					}
				});
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);
				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'WhsDocumentSupply_id'
						},
						[
							{name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id'},
							{name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name'},
							{name: 'WhsDocumentUc_Date', mapping: 'WhsDocumentUc_Date'},
							{name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num'},
							{name: 'Contragent_sid', mapping: 'Contragent_sid'},
							{name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
							{name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'}
						]),
					url: '/?c=Farmacy&m=loadWhsDocumentSupplyList'
				});
			}
		});

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'RegistryLLOEditForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 200,
				collapsible: true,
				url:'/?c=RegistryLLO&m=save',
				items: [{
					xtype: 'hidden',
					name: 'RegistryLLO_id'
				}, {
					xtype: 'hidden',
					name: 'RegistryStatus_id'
				}, {
					xtype: 'swkatnaselcombo',
					fieldLabel: 'Категория населения',
					name: 'KatNasel_id',
					width: 300,
					allowBlank: false
				}, {
					xtype: 'daterangefield',
					name: 'RegistryLLO_Date_Range',
					fieldLabel: 'Период формирования',
					plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 200,
					allowBlank: false
				}, {
					xtype: 'swdrugfinancecombo',
					fieldLabel: 'Источник финансирования',
					name: 'DrugFinance_id',
					width: 300,
					allowBlank: false
				}, {
					xtype: 'swwhsdocumentcostitemtypecombo',
					fieldLabel: 'Программа ЛЛО',
					name: 'WhsDocumentCostItemType_id',
					width: 300,
					allowBlank: false
				},
				wnd.supply_combo
				]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
					handler: function()
					{
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
					{
						text: '-'
					},
					HelpButton(this, 0),
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[form]
		});
		sw.Promed.swRegistryLLOEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('RegistryLLOEditForm').getForm();
	}
});