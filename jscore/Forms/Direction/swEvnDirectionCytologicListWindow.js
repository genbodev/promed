/**
* swEvnDirectionCytologicListWindow - окно просмотра списка направлений на цитологическое исследование
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*/

sw.Promed.swEvnDirectionCytologicListWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 400,
	id: 'EvnDirectionCytologicListWindow',
	initComponent: function() {
		this.EvnDirectionCytologicGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnDirectionCytologic&m=loadEvnDirectionCytologicList',
			id: 'EDHLVW_EvnDirectionCytologicGrid',
			onDblClick: function() {
				this.onSelect();
			}.createDelegate(this),
			onEnter: function() {
				this.onSelect();
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				//
			}.createDelegate(this),
			paging: false,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnDirectionCytologic_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplex_id', type: 'int', hidden: true },
				{ name: 'PayType_id', type: 'int', hidden: true },
				{ name: 'EvnDirectionCytologic_Ser', type: 'string', header: langs('Серия'), id: 'autoexpand' },
				{ name: 'EvnDirectionCytologic_Num', type: 'string', header: langs('Номер'), width: 150 },
				{ name: 'EvnDirectionCytologic_setDate', type: 'date', header: langs('Дата направления'), width: 200 }
			],
			toolbar: true
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EDCLVW_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'EDCLVW_SelectButton',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: langs('Выбрать')
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EDCLVW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				this.PersonInfo,
				this.EvnDirectionCytologicGrid
			],
			layout: 'border'
		});
		sw.Promed.swEvnDirectionCytologicListWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnDirectionCytologicListWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 700,
	modal: true,
	onSelect: function() {
		if ( !this.EvnDirectionCytologicGrid.getGrid() || !this.EvnDirectionCytologicGrid.getGrid().getSelectionModel() || !this.EvnDirectionCytologicGrid.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var record = this.EvnDirectionCytologicGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || !record.get('EvnDirectionCytologic_id') ) {
			return false;
		}

		this.callback({
			EvnDirectionCytologic_id: record.get('EvnDirectionCytologic_id'),
			EvnDirectionCytologic_Ser: record.get('EvnDirectionCytologic_Ser'),
			EvnDirectionCytologic_Num: record.get('EvnDirectionCytologic_Num'),
			EvnDirectionCytologic_setDate: record.get('EvnDirectionCytologic_setDate'),
			UslugaComplex_id: record.get('UslugaComplex_id'),
			PayType_id: record.get('PayType_id')
		});

		this.hide();
	},
	personId: null,
	plain: true,
	resizable: true,
	show: function() {
		var win = this;

		sw.Promed.swEvnDirectionCytologicListWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.personId = null;

		if ( !arguments[0] || !arguments[0].Person_id ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].Person_id ) {
			this.personId = arguments[0].Person_id;
		}

		if( arguments[0].formParams ) {
			this.formParams = arguments[0].formParams; 
		}

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_Birthday ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.EvnDirectionCytologicGrid.removeAll();

		if ( this.personId ) {
			this.EvnDirectionCytologicGrid.loadData({
				globalFilters: {
					Person_id: this.personId
				}
			});
		}
		this.EvnDirectionCytologicGrid.addActions({ 
			name: 'action_add_outer', 
			text: langs('Внешнее направление'), 
			handler: function() {
				var callback = function (dir) {
					win.callback({
						EvnDirectionCytologic_id: dir.evnDirectionCytologicData.EvnDirectionCytologic_id,
						EvnDirectionCytologic_Ser: dir.evnDirectionCytologicData.EvnDirectionCytologic_Ser,
						EvnDirectionCytologic_Num: dir.evnDirectionCytologicData.EvnDirectionCytologic_Num,
						EvnDirectionCytologic_setDate: dir.evnDirectionCytologicData.EvnDirectionCytologic_setDate,
						PayType_id: dir.evnDirectionCytologicData.PayType_id
					});
					win.hide();
				}

				var params = {
					outer: true,
					action: 'add',
					callback: callback,
					formParams: win.formParams,
					external_direction: 1
				};
				getWnd('swEvnDirectionCytologicEditWindow').show(params);
			}
		});
	},
	title: langs('Направления на цитологическое исследование: Список'),
	width: 700
});
