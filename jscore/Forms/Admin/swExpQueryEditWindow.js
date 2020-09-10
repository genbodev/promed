/**
 * swExpQueryEditWindow - окно редактирования файлов информационного обмена с АО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.11.2013
 */

sw.Promed.swExpQueryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	id: 'swExpQueryEditWindow',
	width: 650,
	//height: 450,
	callback: Ext.emptyFn,
	draggable: true,
	maximizable: false,
	modal: true,
	objectSrc: '/jscore/Forms/Admin/swExpQueryEditWindow.js',
	title: langs('ЛЛО. Информационный обмен с АО. Настройка файла экспорта: Редактирование'),

	doSave: function()
	{
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
		var grid = this.DbaseStructureGrid.getGrid();

		grid.getStore().clearFilter();

		var DbaseStructureData = [];

		if ( grid.getStore().getCount() > 0 ) {
			var DbaseStructureData = getStoreRecords(grid.getStore());

			grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}

		params.DbaseStructureData = Ext.util.JSON.encode(DbaseStructureData);

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

	structureTypeRenderer: function(value,p,rec)
	{
		var type = rec.get('Dbase_ColumnType');
		var length = rec.get('Dbase_ColumnLength');
		var precision= rec.get('Dbase_ColumnPrecision');
		var result = type;

		if (!Ext.isEmpty(length)) {
			result += '('+length;
			if (!Ext.isEmpty(precision)) {
				result += ','+precision;
			}
			result += ')';
		}

		return result;
	},

	openExpDbaseStructureEditWindow: function(action)
	{
		if (!action || !action.inlist(['add','edit','view'])) {
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

		if ( getWnd('swExpDbaseStructureEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_polets_zaprosa_informatsionnogo_obmena_uje_otkryito']);
			return false;
		}

		var grid = this.DbaseStructureGrid.getGrid();
		var params = new Object();

		params.action = action;

		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.DbaseStructureData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.DbaseStructureData.DbaseStructure_id);

			if ( typeof record == 'object' ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.DbaseStructureData.RecordStatus_Code = 2;
				}
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.DbaseStructureData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('DbaseStructure_id') ) {
					grid.getStore().removeAll();
				}

				data.DbaseStructureData.DbaseStructure_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData({data: [ data.DbaseStructureData ]}, true);
			}
		}.createDelegate(this);

		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		} else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('DbaseStructure_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swExpDbaseStructureEditWindow').show(params);
	},

	deleteDbaseStructure: function()
	{
		var wnd = this;

		if ( !this.action.inlist(['add','edit']) ) {
			return false;
		}

		question = lang['udalit_pole'];

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = wnd.DbaseStructureGrid.getGrid();

					var idField = 'DbaseStructure_id';

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

	show: function()
	{
		sw.Promed.swExpQueryEditWindow.superclass.show.apply(this, arguments);

		if (!isSuperAdmin() || getGlobalOptions().region.nick=='kz') {
			sw.swMsg.alert(lang['soobschenie'], lang['net_dostupa_k_forme'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.restore();
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments && arguments[0].onHide) {
			this.action = arguments[0].action;
		}

		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		if ( this.action == 'add' ) {
			this.DbaseStructurePanel.isLoaded = true;
		} else {
			this.DbaseStructurePanel.isLoaded = false;
		}

		this.DbaseStructurePanel.expand();
		this.DbaseStructurePanel.fireEvent('expand', this.DbaseStructurePanel);
		this.DbaseStructureGrid.removeAll();

		this.DbaseStructureGrid.setActionDisabled('action_add', true);
		this.DbaseStructureGrid.setActionDisabled('action_edit', true);
		this.DbaseStructureGrid.setActionDisabled('action_view', true);
		this.DbaseStructureGrid.setActionDisabled('action_delete', true);

		base_form.clearInvalid();

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('ЛЛО. Информационный обмен с АО. Настройка файла экспорта: Добавление'));
				this.enableEdit(true);

				this.DbaseStructureGrid.setActionDisabled('action_add', false);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				var Query_id = base_form.findField('Query_id').getValue();

				if ( Ext.isEmpty(Query_id) ) {
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
						Query_id: Query_id
					},
					success: function() {
						if ( this.action == 'edit' ) {
							this.setTitle(langs('ЛЛО. Информационный обмен с АО. Настройка файла экспорта: Редактирование'));
							this.enableEdit(true);
						}
						else {
							this.setTitle(langs('ЛЛО. Информационный обмен с АО. Настройка файла экспорта: Просмотр'));
							this.enableEdit(false);
						}

						if ( this.action == 'edit' )  {
							this.DbaseStructureGrid.setActionDisabled('action_add', false);
						}

						loadMask.hide();

						/*if ( this.action == 'edit' ) {
							base_form.findField('Ord').focus(true, 250);
						} else {
							this.buttons[this.buttons.length - 1].focus();
						}*/
					}.createDelegate(this),
					url: '/?c=exp_Query&m=loadQueryForm'
				});
				break;

			default:
				loadMask.hide();
				this.hide();
				break;
		}
	},

	initComponent: function()
	{
		var wnd = this;

		this.DbaseStructureGrid = new sw.Promed.ViewFrame({
			id: 'EQEW_DbaseStructureGrid',
			region: 'center',
			object: 'exp_Query',
			dataUrl: '/?c=exp_Query&m=loadDbaseStructureGrid',
			paging: false,
			autoLoadData: false,
			root: 'data',
			stringfields:
				[
					{name: 'DbaseStructure_id', type: 'int', header: 'ID', key: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'Dbase_ColumnType', type: 'string', hidden: true},
					{name: 'Dbase_ColumnLength', type: 'int', hidden: true},
					{name: 'Dbase_ColumnPrecision', type: 'int', hidden: true},
					{name: 'Ord', header: lang['nomer'], type: 'int', width: 50},
					{name: 'Query_ColumnName', header: lang['naimenovanie_zapros'], type: 'string', width: 140},
					{name: 'Dbase_ColumnName', header: lang['naimenovanie_dbf'], type: 'string', width: 140},
					{name: 'ColumnType', header: lang['tip'], width: 80, renderer: wnd.structureTypeRenderer},
					{name: 'Description', header: lang['opisanie'], type: 'string', id: 'autoexpand'}
				],
			actions:
				[
					{name:'action_add', handler: function (){wnd.openExpDbaseStructureEditWindow('add');}},
					{name:'action_edit', handler: function (){wnd.openExpDbaseStructureEditWindow('edit');}},
					{name:'action_view', handler: function (){wnd.openExpDbaseStructureEditWindow('view');}},
					{name:'action_delete', handler: function (){wnd.deleteDbaseStructure();}},
					{name:'action_refresh', disabled: true, hidden: true}
				],
			onRowSelect: function(sm, index, record)
			{
				this.setActionDisabled('action_edit', true);
				this.setActionDisabled('action_view', true);
				this.setActionDisabled('action_delete', true);

				if ( typeof record != 'object' || Ext.isEmpty(record.get('DbaseStructure_id')) ) {
					return false;
				}

				this.setActionDisabled('action_view', false);

				if ( wnd.action.inlist(['add', 'edit']) ) {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}
			}
		});

		this.DbaseStructurePanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			height: 200,
			id: 'EQEW_DbaseStructurePanel',
			isLoaded: false,
			layout: 'border',
			//style: 'margin-bottom: 0.5em;',
			title: lang['spisok_poley'],

			items: [
				wnd.DbaseStructureGrid
			],
			listeners: {
				'expand': function(panel) {
					if ( panel.isLoaded === false ) {
						panel.isLoaded = true;
						wnd.DbaseStructureGrid.getGrid().getStore().load({
							params: {
								Query_id: wnd.FormPanel.getForm().findField('Query_id').getValue()
							}
						});
					}
					panel.doLayout();
				},
				'collapse': function(panel) {
					wnd.syncShadow();
				}
			}
		});

		this.FormPanel = new Ext.form.FormPanel({
			//bodyStyle: 'padding: 5px 20px 0',
			border: false,
			frame: true,
			id: 'EQEW_ExpQueryEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			url: '/?c=exp_Query&m=saveQuery',

			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'Query_id' },
				{ name: 'Query_Nick' },
				{ name: 'Name' },
				{ name: 'Filename' },
				{ name: 'Query' },
				{ name: 'Ord' }
			]),
			items: [{
				name: 'Query_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				allowNegative: false,
				allowDecimals: false,
				fieldLabel: lang['nomer'],
				name: 'Ord',
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['nik_zaprosa'],
				name: 'Query_Nick',
				xtype: 'textfield',
				width: 450
			}, {
				allowBlank: false,
				fieldLabel: lang['naimenovanie'],
				name: 'Name',
				xtype: 'textfield',
				width: 450
			}, {
				allowBlank: false,
				fieldLabel: lang['fayl'],
				name: 'Filename',
				xtype: 'textfield',
				width: 450
			}, {
				allowBlank: false,
				fieldLabel: lang['zapros'],
				name: 'Query',
				xtype: 'textarea',
				width: 450,
				height: 150,
				autoCreate: {tag: 'textarea', spellcheck: 'false'}
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel,
				this.DbaseStructurePanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'FSEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'FSEW_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swExpQueryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
