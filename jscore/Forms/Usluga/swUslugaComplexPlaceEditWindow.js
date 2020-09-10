/**
* swUslugaComplexPlaceEditWindow - редактирование места оказания услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      16.07.2012
* @comment      Префикс для id компонентов UCPEW (UslugaComplexPlaceEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swUslugaComplexPlaceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaComplexPlaceEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaComplexPlaceEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		
		data.uslugaComplexPlaceData = {
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'UslugaComplexPlace_id': base_form.findField('UslugaComplexPlace_id').getValue(),
			'Lpu_id': base_form.findField('Lpu_id').getValue(),
			'LpuBuilding_id': base_form.findField('LpuBuilding_id').getValue(),
			'LpuUnit_id': base_form.findField('LpuUnit_id').getValue(),
			'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
			'Lpu_Name': base_form.findField('Lpu_id').getFieldValue('Lpu_Name'),
			'LpuBuilding_Name': base_form.findField('LpuBuilding_id').getFieldValue('LpuBuilding_Name'),
			'LpuUnit_Name': base_form.findField('LpuUnit_id').getFieldValue('LpuUnit_Name'),
			'LpuSection_Name': base_form.findField('LpuSection_id').getFieldValue('LpuSection_Name'),
			'UslugaComplexPlace_begDate': base_form.findField('UslugaComplexPlace_begDate').getValue(),
			'UslugaComplexPlace_endDate': base_form.findField('UslugaComplexPlace_endDate').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'pmUser_Name': getGlobalOptions().pmuser_name
		};

		log(data);
		
		var params = {};
		if (base_form.findField('Lpu_id').disabled) {
			params.Lpu_id = base_form.findField('Lpu_id').getValue();
		}
		if (base_form.findField('LpuBuilding_id').disabled) {
			params.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
		}
		if (base_form.findField('LpuUnit_id').disabled) {
			params.LpuUnit_id = base_form.findField('LpuUnit_id').getValue();
		}
		if (base_form.findField('LpuSection_id').disabled) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

				this.callback(data);
				this.hide();
			break;

			case 'remote':
				base_form.submit({
					params: params,
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.UslugaComplexPlace_id > 0 ) {
							base_form.findField('UslugaComplexPlace_id').setValue(action.result.UslugaComplexPlace_id);
							data.uslugaComplexPlaceData.UslugaComplexPlace_id = base_form.findField('UslugaComplexPlace_id').getValue();

							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'UslugaComplexPlaceEditWindow',
	refreshLpuStores: function() {
		var base_form = this.FormPanel.getForm();
		
		base_form.findField('LpuBuilding_id').getStore().removeAll();
		base_form.findField('LpuUnit_id').getStore().removeAll();
		base_form.findField('LpuSection_id').getStore().removeAll();
		
		var Lpu_id = base_form.findField('Lpu_id').getValue();
		
		if (Lpu_id == getGlobalOptions().lpu_id) {
			swLpuBuildingGlobalStore.clearFilter();
			swLpuUnitGlobalStore.clearFilter();
			swLpuSectionGlobalStore.clearFilter();
			
			base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
			base_form.findField('LpuUnit_id').getStore().loadData(getStoreRecords(swLpuUnitGlobalStore));
			base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		} else {
			base_form.findField('LpuBuilding_id').getStore().load({params: {'Lpu_id': Lpu_id}});
			base_form.findField('LpuUnit_id').getStore().load({params: {'Lpu_id': Lpu_id}});
			base_form.findField('LpuSection_id').getStore().load({params: {'Lpu_id': Lpu_id}});
		}
	},
	clearLpuCombos: function() {
		var base_form = this.FormPanel.getForm();
		base_form.findField('LpuBuilding_id').clearValue();
		base_form.findField('LpuUnit_id').clearValue();
		base_form.findField('LpuSection_id').clearValue();
	},
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaComplexPlaceEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplexPlace_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuUnit_id' },
				{ name: 'LpuSection_id' },
				{ name: 'UslugaComplexPlace_begDate' },
				{ name: 'UslugaComplexPlace_endDate' }
			]),
			url: '/?c=UslugaComplex&m=saveUslugaComplexPlace',
			items: [{
				name: 'UslugaComplexPlace_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaCategory_id',
                comboSubject: 'UslugaCategory',
				fieldLabel: lang['kategoriya'],
				allowBlank: false,
				tabIndex: TABINDEX_UCPEW,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						
						var uslugaCombo = base_form.findField('UslugaComplex_id');
						uslugaCombo.clearValue();

						if (!Ext.isEmpty(newValue)) {
							uslugaCombo.getStore().filterBy(function(record) {
								if (record.get('UslugaCategory_id') == newValue) {
									return true;
								} else {
									return false;
								}
							});

							uslugaCombo.getStore().baseParams.UslugaCategory_id = newValue;
							uslugaCombo.getStore().baseParams.UslugaComplex_id = null;
							loadMask = this.getLoadMask();
							loadMask.show();
							uslugaCombo.getStore().reload({
								callback: function() {
									loadMask.hide();
								}
							});
							this.lastQuery = 'This query sample that is not will never appear';
						} else {
							uslugaCombo.getStore().clearFilter();
							delete uslugaCombo.getStore().baseParams.UslugaCategory_id;
							this.lastQuery = 'This query sample that is not will never appear';
						}
					}.createDelegate(this)
				},
				xtype: 'swuslugacategorycombo'
			}, {
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				allowBlank: false,
				listWidth: 760,
				listeners: {
					/*'change': function(combo, newValue, oldValue)
					{
						if (!Ext.isEmpty(newValue)) {
							this.uslugaTree.getLoader().load(
								this.uslugaTree.getRootNode(), 
								function () {
									// this.uslugaTree.getRootNode().expand(true);
								}.createDelegate(this)
							);
						} else {
							this.uslugaTree.getRootNode().reload();
						}						
					}.createDelegate(this),*/
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});

						combo.fireEvent('select', combo, combo.getStore().getAt(index));
					}.createDelegate(this),
					'select': function(combo, record, index) {
						var base_form = this.FormPanel.getForm();

						base_form.findField('UslugaComplexPlace_begDate').setMaxValue(undefined);
						base_form.findField('UslugaComplexPlace_begDate').setMinValue(undefined);
						base_form.findField('UslugaComplexPlace_endDate').setMaxValue(undefined);
						base_form.findField('UslugaComplexPlace_endDate').setMinValue(undefined);

						base_form.findField('UslugaComplexPlace_endDate').setAllowBlank(true);

						if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
							if ( !Ext.isEmpty(record.get('UslugaComplex_begDT')) ) {
								base_form.findField('UslugaComplexPlace_begDate').setMinValue(record.get('UslugaComplex_begDT'));
								base_form.findField('UslugaComplexPlace_endDate').setMinValue(record.get('UslugaComplex_begDT'));
							}

							if ( !Ext.isEmpty(record.get('UslugaComplex_endDT')) ) {
								base_form.findField('UslugaComplexPlace_begDate').setMaxValue(record.get('UslugaComplex_endDT'));
								base_form.findField('UslugaComplexPlace_endDate').setMaxValue(record.get('UslugaComplex_endDT'));

								base_form.findField('UslugaComplexPlace_endDate').setAllowBlank(false);
								base_form.findField('UslugaComplexPlace_endDate').setValue(record.get('UslugaComplex_endDT'));
							}
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_UCPEW + 1,
				width: 400,
				xtype: 'swuslugacomplexallcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['lpu'],
				hiddenName: 'Lpu_id',
				id: 'UCPEW_Lpu_id',
				tabIndex: TABINDEX_UCPEW + 2,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.clearLpuCombos();
						this.refreshLpuStores();
					}.createDelegate(this)
				},
				width: 300,
				listWidth: 400,
				xtype: 'swlpucombo'
			}, {
				hiddenName: 'LpuBuilding_id',
				fieldLabel: lang['podrazdelenie'],
				id: 'UCPEW_LpuBuildingCombo',
				lastQuery: '',
				linkedElements: [
					'UCPEW_LpuUnitCombo',
					'UCPEW_LpuSectionCombo'
				],
				listWidth: 700,
				tabIndex: TABINDEX_UCPEW + 3,
				width: 450,
				xtype: 'swlpubuildingglobalcombo'
			}, {
				hiddenName: 'LpuUnit_id',
				id: 'UCPEW_LpuUnitCombo',
				linkedElements: [
					'UCPEW_LpuSectionCombo'					
				],
				listWidth: 600,
				parentElementId: 'UCPEW_LpuBuildingCombo',
				tabIndex: TABINDEX_UCPEW + 4,
				width: 450,
				xtype: 'swlpuunitglobalcombo'
			}, {
				hiddenName: 'LpuSection_id',
				id: 'UCPEW_LpuSectionCombo',
				lastQuery: '',
				parentElementId: 'UCPEW_LpuUnitCombo',
				listWidth: 700,
				tabIndex: TABINDEX_UCPEW + 5,
				width: 450,
				xtype: 'swlpusectionglobalcombo'
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_nachala'],
				allowBlank: false,
				format: 'd.m.Y',
				tabIndex: TABINDEX_UCPEW + 6,
				name: 'UslugaComplexPlace_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				tabIndex: TABINDEX_UCPEW + 7,
				name: 'UslugaComplexPlace_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('UslugaComplexPlace_endDate').disabled ) {
						base_form.findField('UslugaComplexPlace_endDate').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCPEW + 8,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_UCPEW + 9),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();
					if ( !base_form.findField('Lpu_id').disabled ) {
						base_form.findField('Lpu_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_UCPEW + 10,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUslugaComplexPlaceEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexPlaceEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexPlaceEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		var uslugaCategoryCombo = base_form.findField('UslugaCategory_id');
		var uslugaCombo = base_form.findField('UslugaComplex_id');
		var index;

		uslugaCombo.date = getGlobalOptions().date;

		if (!Ext.isEmpty(uslugaCombo.getValue())) {
			uslugaCombo.getStore().baseParams = [];
			uslugaCombo.getStore().load({
				params: {
					UslugaComplex_id: uslugaCombo.getValue()
				},
				callback: function() {
					index = uslugaCombo.getStore().findBy(function(rec) {
						return (rec.get('UslugaComplex_id') == uslugaCombo.getValue());
					});

					if ( index >= 0 ) {
						var record = uslugaCombo.getStore().getAt(index);

						uslugaCategoryCombo.setValue(record.get('UslugaCategory_id'));
						uslugaCategoryCombo.fireEvent('change', uslugaCategoryCombo, record.get('UslugaCategory_id'));
						uslugaCombo.setValue(record.get('UslugaComplex_id'));
						uslugaCombo.fireEvent('select', uslugaCombo, record);
					}
					else {
						uslugaCombo.fireEvent('select', uslugaCombo);
					}
				}
			});
		}
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		// открыть поля Категория и Услуга только для добавления из структуры ЛПУ.
		if ( arguments[0].mode && arguments[0].mode == 'LpuStructure' ) {
			base_form.findField('UslugaCategory_id').showContainer();
			if (this.action == 'add') {
				if ( base_form.findField('UslugaCategory_id').getStore().getCount() > 0 ) {
					index = base_form.findField('UslugaCategory_id').getStore().findBy(function(rec) {
						switch ( getRegionNick() ) {
							case 'kareliya':
							case 'perm':
								return (rec.get('UslugaCategory_SysNick') == 'tfoms');
							break;

							case 'kz':
								return (rec.get('UslugaCategory_SysNick') == 'classmedus');
							break;

							default:
								return (rec.get('UslugaCategory_SysNick') == 'gost2011');
							break;
						}
					});

					if ( index == -1 ) {
						index = 0;
					}

					base_form.findField('UslugaCategory_id').setValue(base_form.findField('UslugaCategory_id').getStore().getAt(index).get('UslugaCategory_id'));
					base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getStore().getAt(index).get('UslugaCategory_id'));
				}
			}
			base_form.findField('UslugaComplex_id').showContainer();
			base_form.findField('UslugaCategory_id').setAllowBlank(false);
			base_form.findField('UslugaComplex_id').setAllowBlank(false);
		} else {
			base_form.findField('UslugaCategory_id').setAllowBlank(true);
			base_form.findField('UslugaCategory_id').hideContainer();
			base_form.findField('UslugaComplex_id').setAllowBlank(true);
			base_form.findField('UslugaComplex_id').hideContainer();		
		}
		
		this.syncShadow();

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		var uslugaLpu_id = getGlobalOptions().lpu_id;
		
		if ( arguments[0].Lpu_id && !Ext.isEmpty(arguments[0].Lpu_id) ) {
			uslugaLpu_id = arguments[0].Lpu_id;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		var LpuBuilding_id = arguments[0].LpuBuilding_id || null;
		var LpuUnit_id = arguments[0].LpuUnit_id || null;
		var LpuSection_id = arguments[0].LpuSection_id || null;
		
		this.getLoadMask().show();
			
		switch ( this.action ) {
			case 'add':
				base_form.findField('Lpu_id').setValue(uslugaLpu_id);
				//base_form.findField('LpuBuilding_id').setValue(LpuBuilding_id);
				var idLpuBuilding = LpuBuilding_id;
				var comboLpuBuilding = base_form.findField('LpuBuilding_id');
				comboLpuBuilding.getStore().reload({
					callback: function() {
						if(idLpuBuilding) comboLpuBuilding.setValue(idLpuBuilding);
					}
				});
				base_form.findField('LpuUnit_id').setValue(LpuUnit_id);
				base_form.findField('LpuSection_id').setValue(LpuSection_id);
				this.setTitle(WND_USLUGA_PLACE_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' && (base_form.findField('Lpu_id').getValue() == uslugaLpu_id || isSuperAdmin())) {
					this.setTitle(WND_USLUGA_PLACE_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_USLUGA_PLACE_VIEW);
					this.enableEdit(false);
				}

				this.getLoadMask().hide();
				base_form.clearInvalid();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if (!isSuperAdmin()) {
			base_form.findField('Lpu_id').disable();
		}
		
		if (!isSuperAdmin() && arguments[0].LpuBuilding_id) {
			base_form.findField('LpuBuilding_id').disable();
		}
		
		if (!isSuperAdmin() && arguments[0].LpuUnit_id) {
			base_form.findField('LpuUnit_id').disable();
		}

		if (!isSuperAdmin() && arguments[0].LpuSection_id) {
			base_form.findField('LpuSection_id').disable();
		}
		
		this.refreshLpuStores();
		
		if ( !base_form.findField('Lpu_id').disabled ) {
			base_form.findField('Lpu_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 800
});