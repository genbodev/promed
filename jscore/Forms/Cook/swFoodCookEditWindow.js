/**
 * swFoodCookEditWindow - окно редактирования рецепта блюда
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			01.10.2013
 */

sw.Promed.swFoodCookEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swFoodCookEditWindow',
	maximizable: false,
	modal: true,
	objectSrc: '/jscore/Forms/Cook/swFoodCookEditWindow.js',
	resizable: false,
	title: lang['retsept_blyuda'],
	width: 800,

	deleteGridRecord: function(object) {
		var wnd = this;
		
		if ( this.action == 'view' ) {
			return false;
		}

		if ( typeof object != 'string' || !(object.inlist([ 'FoodCookSpec' ])) ) {
			return false;
		}
		
		var question = lang['udalit'];
		
		switch ( object ) {
			case 'FoodCookSpec':
				question = lang['udalit_ingredient'];
			break;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = wnd.findById('FCEW_' + object + 'Grid').getGrid();

					var idField = object + '_id';

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();
					
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
						break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
						break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	doSave: function() {
		var base_form = this.FormPanel.getForm();
		var wnd = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		// Собираем данные из списка "Ингредиенты"
		var FoodCookSpecGrid = this.FoodCookSpecGrid.getGrid();

		FoodCookSpecGrid.getStore().clearFilter();

		if ( FoodCookSpecGrid.getStore().getCount() > 0 ) {
			var FoodCookSpecData = getStoreRecords(FoodCookSpecGrid.getStore(), {
				exceptionFields: [
					'FoodStuff_Name'
				]
			});

			params.FoodCookSpecData = Ext.util.JSON.encode(FoodCookSpecData);

			FoodCookSpecGrid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}

		wnd.getLoadMask("Подождите, идет сохранение...").show();

		base_form.submit({
			failure: function(result_form, action) {
				sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
				wnd.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {
				wnd.getLoadMask().hide();

				if ( action.result ) {
					wnd.callback();
					wnd.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	openFoodCookSpecEditWindow: function(action) {
		if ( Ext.isEmpty(action) || !action.inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swFoodCookSpecEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_ingredienta_uje_otkryito']);
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.FoodCookSpecGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.FoodCookSpecData != 'object' ) {
				return false;
			}

			data.FoodCookSpecData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.FoodCookSpecData.FoodCookSpec_id);

			if ( typeof record == 'object' ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.FoodCookSpecData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.FoodCookSpecData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('FoodCookSpec_id') ) {
					grid.getStore().removeAll();
				}
				
				data.FoodCookSpecData.FoodCookSpec_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.FoodCookSpecData ], true);
			}
		}.createDelegate(this);

		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('FoodCookSpec_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swFoodCookSpecEditWindow').show(params);
	},

	show: function() {
		sw.Promed.swFoodCookEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		if ( this.action == 'add' ) {
			this.FoodCookSpecPanel.isLoaded = true;
		}
		else {
			this.FoodCookSpecPanel.isLoaded = false;
		}

		this.FoodCookSpecPanel.expand();
		this.FoodCookSpecPanel.fireEvent('expand', this.FoodCookSpecPanel);
		this.FoodCookSpecGrid.removeAll();

		this.FoodCookSpecGrid.setActionDisabled('action_add', true);
		this.FoodCookSpecGrid.setActionDisabled('action_edit', true);
		this.FoodCookSpecGrid.setActionDisabled('action_view', true);
		this.FoodCookSpecGrid.setActionDisabled('action_delete', true);

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['retsept_blyuda_dobavlenie']);
				this.enableEdit(true);

				this.FoodCookSpecGrid.setActionDisabled('action_add', false);

				LoadEmptyRow(this.FoodCookSpecGrid.getGrid());

				loadMask.hide();

				base_form.findField('FoodCook_Code').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				var FoodCook_id = base_form.findField('FoodCook_id').getValue();

				if ( Ext.isEmpty(FoodCook_id) ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						FoodCook_id: FoodCook_id
					},
					success: function() {
						if ( this.action == 'edit' ) {
							this.setTitle(lang['retsept_blyuda_redaktirovanie']);
							this.enableEdit(true);
						}
						else {
							this.setTitle(lang['retsept_blyuda_prosmotr']);
							this.enableEdit(false);
						}

						if ( this.action == 'edit' )  {
							this.FoodCookSpecGrid.setActionDisabled('action_add', false);
						}

						loadMask.hide();

						if ( this.action == 'edit' ) {
							base_form.findField('FoodCook_Code').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=FoodCook&m=loadFoodCookEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},

	initComponent: function() {
		var form = this;

		// Таблица "Ингредиенты"
		this.FoodCookSpecGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openFoodCookSpecEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openFoodCookSpecEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openFoodCookSpecEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord('FoodCookSpec'); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=FoodCook&m=loadFoodCookSpecGrid',
			id: 'FCEW_FoodCookSpecGrid',
			onDblClick: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( typeof record != 'object' || Ext.isEmpty(record.get('FoodCookSpec_id')) ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( form.action == 'edit' ) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'FoodCookSpec_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'FoodStuff_id', type: 'int', hidden: true },
				{ name: 'Okei_nid', type: 'int', hidden: true },
				{ name: 'Okei_bid', type: 'int', hidden: true },
				{ name: 'FoodStuff_Protein', type: 'float', hidden: true },
				{ name: 'FoodStuff_Fat', type: 'float', hidden: true },
				{ name: 'FoodStuff_Carbohyd', type: 'float', hidden: true },
				{ name: 'FoodStuff_Caloric', type: 'float', hidden: true },
				{ name: 'FoodCookSpec_Priority', type: 'int', header: lang['ocherednost'], width: 70 },
				{ name: 'FoodStuff_Name', type: 'string', header: lang['naimenovanie_produkta'], id: 'autoexpand' },
				{ name: 'FoodCookSpec_MassN', type: 'float', header: lang['massa_netto'], width: 100 },
				{ name: 'FoodCookSpec_MassB', type: 'float', header: lang['massa_brutto'], width: 100 },
				{ name: 'FoodCookSpec_Time', type: 'int', header: lang['vremya_prigotovleniya'], width: 70 },
				{ name: 'FoodCookSpec_Descr', type: 'string', header: lang['opisanie'], width: 150 }
			]
		});

		// Панель с гридом "Ингредиенты"
		this.FoodCookSpecPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'FCEW_FoodCookSpecPanel',
			isLoaded: false,
			layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						form.FoodCookSpecGrid.getGrid().getStore().load({
							params: {
								FoodCook_id: form.FormPanel.getForm().findField('FoodCook_id').getValue()
							}
						});
					}

					panel.doLayout();
				}
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['2_ingredientyi'],

			items: [
				form.FoodCookSpecGrid
			]
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0px;',
			border: false,
			frame: true,
			id: 'FoodCookEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			region: 'center',
			style: 'margin-bottom: 0.5em;',
			url: '/?c=FoodCook&m=saveFoodCook',

			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'FoodCook_id' },
				{ name: 'FoodCook_Code' },
				{ name: 'FoodCook_Name' },
				{ name: 'FoodCook_Descr' },
				{ name: 'FoodCook_DescrOrgan' },
				{ name: 'FoodCook_Caloric' },
				{ name: 'FoodCook_Protein' },
				{ name: 'FoodCook_Fat' },
				{ name: 'FoodCook_Carbohyd' },
				{ name: 'FoodCook_Time' },
				{ name: 'FoodCook_Mass' },
				{ name: 'Okei_id' }
			]),

			items: [{
				name: 'FoodCook_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['kod'],
				maxLength: 5,
				name: 'FoodCook_Code',
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['naimenovanie'],
				maxLength: 100,
				name: 'FoodCook_Name',
				width: 430,
				xtype: 'textfield'
			}, {
				fieldLabel: lang['opisanie_protsessa_prigotovleniya'],
				height: 100,
				name: 'FoodCook_Descr',
				width: 430,
				xtype: 'textarea'
			}, {
				fieldLabel: lang['opisanie_organolepticheskih_svoystv'],
				height: 100,
				name: 'FoodCook_DescrOrgan',
				width: 430,
				xtype: 'textarea'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowDecimails: true,
						allowNegative: false,
						fieldLabel: lang['massa_gotovogo_blyuda'],
						name: 'FoodCook_Mass',
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					labelWidth: 70,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['ed_izm'],
						hiddenName: 'Okei_id',
						width: 100,
						xtype: 'swokeicombo'
					}]
				}]
			}, {
				disabled: true,
				fieldLabel: lang['belki'],
				name: 'FoodCook_Protein',
				width: 100,
				xtype: 'numberfield'
			}, {
				disabled: true,
				fieldLabel: lang['jiryi'],
				name: 'FoodCook_Fat',
				width: 100,
				xtype: 'numberfield'
			}, {
				disabled: true,
				fieldLabel: lang['uglevodyi'],
				name: 'FoodCook_Carbohyd',
				width: 100,
				xtype: 'numberfield'
			}, {
				disabled: true,
				fieldLabel: lang['kalorii'],
				name: 'FoodCook_Caloric',
				width: 100,
				xtype: 'numberfield'
			},
				this.FoodCookSpecPanel
			]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel,
				this.FoodCookSpecPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'FCEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'FCEW_CancelButton',
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swFoodCookEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
