/**
* swEvnPLEvnPSSearchWindow - форма поиска КВС (карты выбывшего из стационара) и ТАП (талона амбулаторного пациента)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      15.12.2010
* @comment      Префикс для id компонентов EPLEPSSW (EvnPLEvnPSSearchWindow)
*/

sw.Promed.swEvnPLEvnPSSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['podojdite'] });
		}

		return this.loadMask;
	},
	height: 500,
	id: 'EvnPLEvnPSSearchWindow',
	initComponent: function() {
		this.FilterPanel = new Ext.form.FormPanel({
			hidden: true,
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 140,

			items: [{
				layout: 'column',
				border: false,	
				items: [{
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						allowBlank: true,
						codeField: 'EvnClass_Code',
						displayField: 'EvnClass_Name',
						editable: false,
						fieldLabel: lang['iskat'],
						hiddenName: 'EvnClass_id',
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						listWidth: 350,
						store: new Ext.data.SimpleStore({
							autoLoad: true,
							data: [
								[ 0, 0, lang['vse_sluchai_lecheniya'] ],
								[ 3, 3, lang['sluchai_ambulatorno-poliklinicheskogo_lecheniya'] ],
								[ 6, 6, 'Случаи стоматологического лечения' ],
								[ 30, 30, lang['sluchai_gospitalizatsii'] ]
							],
							fields: [
								{ name: 'EvnClass_id', type: 'int'},
								{ name: 'EvnClass_Code', type: 'int'},
								{ name: 'EvnClass_Name', type: 'string'}
							],
							key: 'EvnClass_id',
							sortInfo: { field: 'EvnClass_Code' }
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{EvnClass_Code}</font>&nbsp;{EvnClass_Name}',
							'</div></tpl>'
						),
						value: 3,
						valueField: 'EvnClass_id',
						width: 170,
						xtype: 'swbaselocalcombo'
					}, {
						fieldLabel: lang['nomer_kartyi_talona'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						name: 'Evn_NumCard',
						width: 100,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['period'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						name: 'Evn_setDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}]
				}, {
					bodyStyle: 'padding-right: 5px;',
					border: false,
					labelWidth: 100,
					layout: 'form',
					items: [{
						fieldLabel: lang['familiya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Surname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['imya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Firname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['otchestvo'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Secname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}]
				}, {
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						disabled: false,
						handler: function () {
							this.loadGridWithFilter();
						}.createDelegate(this),
						minWidth: 125,
						text: lang['primenit_filtr'],
						topLevel: true,
						xtype: 'button'
					}, {
						disabled: false,
						handler: function () {
							this.loadGridWithFilter(true);
						}.createDelegate(this),
						minWidth: 125,
						text: lang['snyat_filtr'],
						topLevel: true,
						xtype: 'button'
					}]
				}]
			}, {
				allowBlank: true,
				editable: true,
				fieldLabel: lang['lpu'],
				forceSelection: false,
				hiddenName: 'Lpu_eid',
				listWidth: 500,
				setValue: function(v) {
					var text = v;

					if ( this.valueField ) {
						var r = this.findRecord(this.valueField, v);

						if ( r ) {
							text = r.get(this.displayField);

							if ( r.get('Lpu_EndDate') != '' ) {
								text = text + lang['zakryita']+ Ext.util.Format.date(Date.parseDate(r.get('Lpu_EndDate').slice(0, 10), "Y-m-d"), "d.m.Y") + ' )';
							}
						}
						else if ( this.valueNotFoundText !== undefined ) {
							text = this.valueNotFoundText;
						}
					}

					this.lastSelectionText = text;

					if ( this.hiddenField ) {
						this.hiddenField.value = v;
					}

					Ext.form.ComboBox.superclass.setValue.call(this, text);

					this.value = v;
				},
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{[(values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыта " + Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y") + ")" : values.Lpu_Nick ]}&nbsp;',
					'</div></tpl>'
				),
				typeAhead: true,
				width: 400,
				xtype: 'swlpulocalcombo'
			}]
		});
		
		this.PersonInfo = new sw.Promed.PersonInformationPanelShort();

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view', disabled: true },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_print', disabled: true }
			],
			autoLoadData: false,
			dataUrl: '/?c=Common&m=loadEvnPLEvnPSGrid',
			id: this.id + 'SearchGrid',
			onDblClick: function() {
				this.selectRecord();
			}.createDelegate(this),
			onEnter: function() {
				this.selectRecord();
			}.createDelegate(this),
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm,index,record) {
				//
			},
			paging: false,
			layout:'fit',
			region: 'center',
			stringfields: [
				{ name: 'Evn_id', type: 'int', header: 'ID', key: true },
				{ name: 'EvnClass_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnClass_Name', type: 'string', header: lang['tip'], width: 50 },
				{ name: 'Evn_NumCard', type: 'string', header: lang['nomer_tap_kvs'], width: 100 },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 100 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'] },
				{ name: 'Evn_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala_lecheniya'] },
				{ name: 'Evn_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya_lecheniya'] },
				{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], id: 'autoexpand' },
				{ name: 'Diag_Code', type: 'string', header: lang['kod_mkb-10'], width: 80 },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 200 }
			],
			toolbar: false
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function()  {
					this.selectRecord();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()  {
					this.hide()
				}.createDelegate(this),
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [{
				autoHeight: true,
				border: false,
				region: 'north',
				xtype: 'panel',

				items: [
					this.FilterPanel,
					this.PersonInfo
				]
			}, {
				border: false,
				layout: 'border',
				region: 'center',
				xtype: 'panel',

				items: [
					this.SearchGrid
				]
			}]
		});

		sw.Promed.swEvnPLEvnPSSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLEvnPSSearchWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnDirectionMorfoHistologicEditWindow('add');
				break;
			}
		},
		key: [ Ext.EventObject.INSERT ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'beforeshow': function() {
			//
		}
	},
	loadGridWithFilter: function(clear) {
		var base_form = this.FilterPanel.getForm();

		this.SearchGrid.removeAll({
			clearAll: true
		});

		this.SearchGrid.gFilters = null;

		if ( clear ) {
			base_form.reset();
		}
		else {
			if ( this.personId ) {
				this.SearchGrid.loadData({
					globalFilters: {
						Person_id: this.personId,
						Lpu_id: this.Lpu_id,
						EvnClass_SysNick: this.EvnClass_SysNick
					}
				});
			}
			else {
				this.SearchGrid.loadData({
					globalFilters: base_form.getValues()
				});
			}
		}
	},
	maximizable: true,
	maximized: false,
	modal: true,
	personId: null,
	plain: true,
	resizable: false,
	onPersonSelect: function() {},
	selectRecord: function(callback_data) {
		persData = {};
		var grid = this.findById(this.id+'SearchGrid');
		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		if( selected_record )
		{
			Ext.apply(persData, selected_record.data);
			persData.Evn_NumCard = selected_record.get('Evn_NumCard');
			persData.onHide = function() {
				var index = grid.getStore().findBy(function(rec) { return rec.get('Person_id') == selected_record.get('Person_id'); });
				grid.focus();
				grid.getView().focusRow(index);
				grid.getGrid().getSelectionModel().selectRow(index);
			}
			this.onPersonSelect(persData);
		}
		else
		{
			this.hide();
		}
	},
	show: function() {
		sw.Promed.swEvnPLEvnPSSearchWindow.superclass.show.apply(this, arguments);

		this.getLoadMask().show();

		this.restore();
		this.center();

		this.personId = null;
		this.Lpu_id = null;
		this.EvnClass_SysNick = null;


		if ( arguments[0] ) {
			if ( arguments[0].Person_id ) {
				this.personId = arguments[0].Person_id;
				this.Lpu_id = arguments[0].Lpu_id;
				this.EvnClass_SysNick = arguments[0].EvnClass_SysNick;
			}
			
			if ( arguments[0].onSelect )
			{
				this.onPersonSelect = arguments[0].onSelect;
			}
		}

		if ( this.personId ) {
			this.FilterPanel.hide();
			this.PersonInfo.show();
			this.loadGridWithFilter();
			this.setTitle(lang['vyibor_tap_kvs']);

			this.PersonInfo.load({
				Person_id: this.personId,
				Person_Birthday: (arguments[0] && arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
				Person_Firname: (arguments[0] && arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
				Person_Secname: (arguments[0] && arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
				Person_Surname: (arguments[0] && arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
			});
			
			this.doLayout();
		}	
		else {
			this.FilterPanel.show();
			this.PersonInfo.hide();
			this.loadGridWithFilter(true);
			this.setTitle(lang['poisk_tap_kvs']); 
			this.doLayout();
		}
		this.syncSize();
		
		this.getLoadMask().hide();
	},
	width: 800
});