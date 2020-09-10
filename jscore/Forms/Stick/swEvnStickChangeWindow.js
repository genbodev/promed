/**
 * Created by JetBrains PhpStorm.
 * User: EldarKE
 * Date: 09.06.11
 * Time: 13:22
 * To change this template use File | Settings | File Templates.
 */


sw.Promed.swEvnStickChangeWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	id: 'EvnStickChangeWindow',
	initComponent: function() {
		this.SearchGrid = new Ext.grid.GridPanel({
			height: 200,
			id: 'EvnStickChangeSearchGrid',
			region: 'center',
			columns:[{
				dataIndex: 'EvnStickDoc',
				header: lang['novyiy_suschestvuyuschiy'],
				resizable: false,
				sortable: false,
				width: 150
			}, {
				dataIndex: 'StickType_Name',
				header: lang['vid_dokumenta'],
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'EvnStick_setDate',
				header: lang['data_vyidachi'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'EvnStick_Ser',
				header: lang['seriya'],
				hidden: false,
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'EvnStick_Num',
				header: lang['nomer'],
				hidden: false,
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'StickOrder_Name',
				header: lang['poryadok_vyipiski'],
				hidden: false,
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'EvnStick_ParentTypeName',
				header: lang['tap_kvs'],
				hidden: false,
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'EvnStick_ParentNum',
				header: lang['nomer_tap_kvs'],
				hidden: false,
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'EvnStatus_Name',
				header: lang['tip_lvn'],
				hidden: false,
				resizable: false,
				sortable: false
			}, {
				dataIndex: 'StickWorkType_Name',
				header: 'Тип занятости',
				hidden: false,
				resizable: false,
				sortable: false
			}],
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						var sm = this.SearchGrid.getSelectionModel();

						if ( sm.hasSelection() ) {
							if ( sm.last < 2 ) {
								sw.swMsg.alert('Ошибка', "Новые ЛВН (справку учащегося) нельзя открыть на просмотр.");
							}
							else {
								this.onView();
							}
						}
						else {
							sw.swMsg.alert('Ошибка', "Прежде чем открыть на просмотр ЛВН (справку учащегося)<br /> необходимо выбрать её в списке.");
						}
					}.createDelegate(this),
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}]
			}),
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var selected_record = sm.getSelected();
						var toolbar = this.SearchGrid.getTopToolbar();

						if ( sm.last == 0 || sm.last == 1 ) {
							toolbar.items.items[0].disable();
						}
						else {
							toolbar.items.items[0].enable();
						}
					}.createDelegate(this)
				},
				singleSelect: true
			}),
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					this.onSelect();
				}.createDelegate(this)
			},
			loadMask: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, options) {
						if ( store.getCount() > 0 ) {
							this.SearchGrid.getSelectionModel().selectFirstRow();
							this.SearchGrid.getView().focusRow(0);
						}
					}.createDelegate(this)
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnStick_id'
				}, [{
					mapping: 'EvnStick_id',
					name: 'EvnStick_id',
					type: 'int'
				}, {
					mapping: 'EvnStick_pid',
					name: 'EvnStick_pid',
					type: 'int'
				}, {
					mapping: 'evnStickType',
					name: 'evnStickType',
					type: 'int'
				}, {
					mapping: 'parentClass',
					name: 'parentClass',
					type: 'string'
				}, {
					mapping: 'EvnStickDoc',
					name: 'EvnStickDoc',
					type: 'string'
				}, {
					mapping: 'StickType_Name',
					name: 'StickType_Name',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnStick_setDate',
					name: 'EvnStick_setDate',
					type: 'date'
				}, {
					mapping: 'EvnStick_Ser',
					name: 'EvnStick_Ser',
					type: 'string'
				}, {
					mapping: 'EvnStick_Num',
					name: 'EvnStick_Num',
					type: 'string'
				}, {
					mapping: 'StickOrder_Name',
					name: 'StickOrder_Name',
					type: 'string'
				}, {
					mapping: 'EvnStick_ParentTypeName',
					name: 'EvnStick_ParentTypeName',
					type: 'string'
				}, {
					mapping: 'EvnStick_ParentNum',
					name: 'EvnStick_ParentNum',
					type: 'string'
				}, {
					mapping: 'EvnStatus_Name',
					name: 'EvnStatus_Name',
					type: 'string'
				}, {
					mapping: 'StickWorkType_Name',
					name: 'StickWorkType_Name',
					type: 'string'
				}]),
				url: '/?c=Stick&m=getEvnStickChange'
			})
		});

		// TODO: Доп кнопки
		Ext.apply(this, {
			buttons: [{
			    text: lang['vyibrat'],
			    handler: function(){
					this.onSelect();
			    }.createDelegate(this),
			    iconCls: 'checked16'
			}, {
			    text: 'Получить ЛВН из ФСС',
			    handler: function(){
					this.openStickFSSDataEditWindow('add');
			    }.createDelegate(this),
			    iconCls: 'checked16'
			}, {
				text: '-'
			}, {
				handler: function()  {
					this.hide()
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [
				this.SearchGrid
			]
		});

		sw.Promed.swEvnStickChangeWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: [ Ext.EventObject.ENTER ],
		fn: function() {
			Ext.getCmp('EvnStickChangeWindow').onSelect();
		}
	}, {
		key: [ Ext.EventObject.F3 ],
		fn: function() {
			Ext.getCmp('EvnStickChangeWindow').onView();
		}
	}],
	listeners: {
		'hide': function() {
			this.SearchGrid.getStore().removeAll();
		}
	},
	modal: true,
	onRecordSelect: Ext.emptyFn,
	openStickFSSDataEditWindow: function(action) {
		if (!action.inlist(['add'])) {
			return false;
		}

		var params = {action: action};

		var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		if (this.params.formParams && typeof this.params.formParams.EvnStick_begDate == 'object') {
			date = this.params.formParams.EvnStick_begDate;
		}
		var person_age = swGetPersonAge(this.params.Person_Birthday, date);
		if (person_age >= 18) {
			params.Person_id = this.thisFormParams.Person_id;
		} else {
			params.Person_id = null;
		}

		params.callback = function(data) {
			this.hide();
			if (data.warnExist) {
				return false;
			}
			var params = this.params;
			params.action = 'add';
			params.evnStickType = 1;
			params.formParams.StickFSSData_id = data.StickFSSData_id;
			params.formParams.EvnStick_id = 0;
			params.EvnStick_Num = data.EvnStick_Num;
			if (data.Person_id != params.Person_id) {
				params.Person_Surname = data.Person_Surname;
				params.Person_Firname = data.Person_Firname;
				params.Person_Secname = data.Person_Secname;
				params.Person_id = data.Person_id;
				params.PersonEvn_id = data.PersonEvn_id;
				params.Server_id = data.Server_id;
				params.formParams.Person_id = data.Person_id;
				params.formParams.PersonEvn_id = data.PersonEvn_id;
				params.formParams.Server_id = data.Server_id;
			}

			getWnd('swEvnStickEditWindow').show(params);
		}.createDelegate(this);

		getWnd('swStickFSSDataEditWindow').show(params);
	},
	onSelect: function() {
		var sm = this.SearchGrid.getSelectionModel();

		if ( sm.hasSelection() ) {
			var params = this.params;

			if ( sm.last == 0 ) {
				params.action = 'add';
				params.evnStickType = 1;
				params.formParams.EvnStick_id = 0;

				getWnd('swEvnStickEditWindow').show(params);
			}
			else if ( sm.last == 1 ) {
				params.action = "add";
				params.formParams.EvnStickStudent_id = 0;

				getWnd('swEvnStickStudentEditWindow').show(params);
			}
			else {
				var record = sm.getSelected();

				if ( !record || !record.get('EvnStick_id') ) {
					return false;
				}

				params.action = 'edit';
				params.evnStickType = record.get('evnStickType');
				params.formParams.EvnStick_id = record.get('EvnStick_id');
				params.formParams.EvnStick_pid = record.get('EvnStick_pid');
				params.link = true;
				//params.parentClass = record.get('parentClass');
				params.parentNum = record.get('EvnStick_ParentNum');
				getWnd('swEvnStickEditWindow').show(params);
			}

			this.hide();
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['dlya_nachala_vyiberite_stroku_v_tablitse']);
		}
	},
	onView: function() {
		var sm = this.SearchGrid.getSelectionModel();

		if ( sm.hasSelection() ) {
			if ( sm.last < 2 ) {
				sw.swMsg.alert('Ошибка', "Новый ЛВН (справку учащегося) нельзя открыть на просмотр.");
			}
			else {
				var formParams = new Object();
				var params = this.params;
				var record = sm.getSelected();

				if ( !record || !record.get('EvnStick_id') ) {
					return false;
				}

				formParams.EvnStick_id = record.get('EvnStick_id');

				params.action = 'view';
				params.evnStickType = 1;
				params.formParams = formParams;
				//params.parentClass = record.get('parentClass');

				getWnd('swEvnStickEditWindow').show(params);
			}
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['dlya_nachala_vyiberite_stroku_v_tablitse']);
		}
	},
	selRow: function() {
		var record = this.SearchGrid.getSelectionModel().getSelected();
		
		if ( record ) {
			data = record.data;
			this.onRecordSelect(data);
		}
		else {
			sw.swMsg.alert(lang['soobschenie'], lang['vyi_ne_vyibrali_dokument_o_netrudosposobnosti']);
			return false;
		}
	},
	show: function() {
		this.buttons[0].setHandler(function() {
			this.onSelect();
		}.createDelegate(this));

		this.params = arguments[0];

		this.thisFormParams = {
			'Person_id': arguments[0].Person_id,
			'evnStickIsCont': 1
		};

		if ( arguments[0].formParams ) {
			this.thisFormParams.EvnStick_mid = arguments[0].formParams.EvnStick_mid;
		}
		
		// Это для возможности выбора ЛВН и занесения его в поле триггера
		if ( arguments[0].onSelect ) {
			this.buttons[0].setHandler(function(){this.selRow();}.createDelegate(this));
			this.onRecordSelect = arguments[0].onSelect;
			this.thisFormParams.StickExisting = 1;
		}

		sw.Promed.swEvnStickChangeWindow.superclass.show.apply(this, arguments);

		this.SearchGrid.getTopToolbar().items.items[0].disable();

		this.SearchGrid.getStore().load({
			params: this.thisFormParams
		});
	},
	title: lang['vyibor_dokumenta_netrudosposobnosti'],
	width: 850,
	height: 300
});
