/**
* swRegistryDeadBodiesWindow - журнал регистрации поступления и выдачи тел умерших
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @author       Shekunov Dmitriy
*/

sw.Promed.swRegistryDeadBodiesWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 880,
	id: 'RegistryDeadBodiesWindow',
	title: lang['jurnal_registratsii_postupleniya_i_vydachi_tel_umershih'], 
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	listeners:
	{
		beforeshow: function()
		{
			//
		},
		resize: function()
		{
			if(this.layout.layout) {
				this.doLayout();
			}
		}
	},
	getLoadMask: function() {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	
	getPeriod30DaysLast: function () {
		var date2 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		var date1 = date2.add(Date.DAY, -29).clearTime();
		return Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y');
	},
	
	loadGridWithFilter: function(clear) {
		var win = this;
		
		if (clear) {
			win.clearFilters();
		} 
		
		var baseForm = win.FilterPanel.getForm();
		
		if (!baseForm.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = baseForm.getValues();
		
		params.Lpu_sid = baseForm.findField('Lpu_sid').getValue() || '0';
		params.Refuse_Exists = baseForm.findField('Refuse_Exists').getValue() || '';

		params.start = 0;
		params.limit = 100;
		
		win.SearchGrid.removeAll();
		win.SearchGrid.loadData({
			globalFilters: params,
			callback: function () {
			}
		});
	},
	
	clearFilters: function() {
		var baseForm = this.FilterPanel.getForm();
		baseForm.reset();
		
		baseForm.findField('ReportPeriod').setValue(this.getPeriod30DaysLast());
		baseForm.findField('Lpu_sid').fireEvent('change', baseForm.findField('Lpu_sid'), baseForm.findField('Lpu_sid').getValue());
		baseForm.findField('Refuse_Exists').setValue('');
		
	},

	printJournal: function() {
		this.getLoadMask().show();
		var baseForm = this.FilterPanel.getForm();
		
		var params = baseForm.getValues();
		params.Lpu_sid = baseForm.findField('Lpu_sid').getValue() || '0';
		params.Refuse_Exists = baseForm.findField('Refuse_Exists').getValue() || '';

		Ext.Ajax.request({
			url: '/?c=RegistryDeadBodies&m=printRegistryDeadBodiesJournal',
			params: params,
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					openNewWindow(r.responseText);
				}
			}.createDelegate(this)
		});
		
	},
	
	initComponent: function() {
		var form = this;
		
		this.FilterPanel = new Ext.form.FormPanel(
			{
			id: 'RDBW_FilterPanel',
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.loadGridWithFilter();
				}.createDelegate(this),
				stopEvent: true
			}, {
				ctrl: true,
				fn: function(inp, e) {
					form.loadGridWithFilter(true);
				},
				key: 188,
				scope: this,
				stopEvent: true
			}],
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px; padding-bottom: 15px;',
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
					layout: 'column',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					columnWidth: .36,
					labelAlign: 'right',
					labelWidth: 100,
					items: [{
						layout: 'form',
						width: 350,
						labelWidth: 150,
						border: false,
						items: [{
								fieldLabel: lang['otchetniy_period'],
								xtype: 'daterangefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								allowBlank: false,
								name: 'ReportPeriod',
								maxValue: getGlobalOptions().date,
								//width: 175
								maxLength: 30,
								width: 175,
							}]
					}]
				}]
			}, {
				layout: 'column',
				border: false,	
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .45,
					width: 350,
					labelAlign: 'right',
					labelWidth: 150,
					items:
					[{
						xtype: 'daterangefield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						fieldLabel: lang['data_postupleniya_tela'],
						name: 'MorfoHistologicCorpse_recieptDate'
					}, {
						xtype: 'daterangefield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						fieldLabel: lang['data_vskryitiya'],
						name: 'EvnMorfoHistologicProto_autopsyDate'
					}, {
						xtype: 'daterangefield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						fieldLabel: lang['data_vydachi_tela'],
						name: 'MorfoHistologicCorpse_giveawayDate'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .36,
					width: 350,
					labelAlign: 'right',
					labelWidth: 130,
					items: 
						[{
						fieldLabel: lang['fio_umershego'],
						name: 'PersonDead_FIO',
						width: 200,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['nomer_med_karty'],
						name: 'EvnPS_NumCard',
						width: 200,
						xtype: 'textfield'
					}, {
						xtype: 'checkbox',
						// width: 200,
						fieldLabel: lang['otkaz_ot_vskryitiya'],
						name: 'Refuse_Exists'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .36,
					width: 340,
					labelAlign: 'right',
					labelWidth: 120,
					items:
						[{
							xtype: 'swlpucombo',
							width: 200,
							fieldLabel: lang['napravivshaya_mo'],
							hiddenName: 'Lpu_sid',
							listeners: {
								'change': function (combo, newValue, oldValue) {
									//
								}
							}
						}, {
						layout: 'column',
						border: false,
						labelAlign: 'right',
						style: 'float: right;',
						labelWidth: 100,
						items: [{
							layout: 'form',
							border: false,
							bodyStyle:'background:#DFE8F6;',
							//style: 'margin-left: 92px;',
							width: 80,
							items:
								[{
									xtype: 'button',
									text: lang['nayti'],
									iconCls: 'search16',
									disabled: false,
									topLevel: true,
									allowBlank:true,
									handler: function ()
									{
										form.loadGridWithFilter();
									}
								}]
						},  {
							layout: 'form',
							border: false,
							bodyStyle:'background:#DFE8F6;',
							width: 73,
							items:
								[{
									xtype: 'button',
									text: lang['sbros'],
									iconCls: 'resetsearch16',
									disabled: false,
									topLevel: true,
									allowBlank:true,
									handler: function ()
									{
										form.loadGridWithFilter(true);
									}
								}]
						}]
					}]
				}]
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame(
		{
			id: 'RDBW_SearchGrid',
			region: 'center',
			height: 203,
			title:lang['jurnal_registratsii_postupleniya_i_vydachi_tel_umershih_spisok'],
			dataUrl: '/?c=RegistryDeadBodies&m=loadRegistryDeadBodiesListGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				{ name: 'EvnDirectionMorfoHistologic_id', type: 'int', hidden: true },
				{ name: 'MorfoHistologicCorpseReciept_id', type: 'int', hidden: true },
				{ name: 'MorfoHistologicCorpseGiveaway_id', type: 'int', hidden: true },
				{ name: 'CorpseRecipient_id', type: 'int', hidden: true },
				{ name: 'EvnMorfoHistologicProto_id', type: 'int', hidden: true },
				{ name: 'MorfoHistologicRefuse_id', type: 'int', hidden: true },
				{ name: 'DeadPerson_id', type: 'int', hidden: true },
				{ name: 'Lpu_sid', type: 'int', hidden: true },
				{ name: 'Refuse_Exists', type: 'bool', hidden: true },
				{ name: 'MorfoHistologicCorpse_recieptDate', type: 'date', format: 'd.m.Y', header: lang['data_postupleniya_tela'], width: 120 },
				{ name: 'PersonDead_FIO', type: 'string', header: lang['fio_umershego'], width: 200, id: 'autoexpand' },
				{ name: 'Lpu_Name', type: 'string', header: lang['naimenovanie_mo'], width: 120 },
				{ name: 'EvnPS_NumCard', type: 'string', header: lang['nomer_med_karty'], width: 100 },
				{ name: 'EvnMorfoHistologicProto_autopsyDate', type: 'date', format: 'd.m.Y', header: lang['data_vskryitiya'], width: 120 },
				{ name: 'MorfoHistologicRefuseType_name', type: 'string', header: lang['prichina_otkaza_ot_vskryitiya'], width: 100 },
				{ name: 'MorfoHistologicCorpse_giveawayDate', type: 'date', format: 'd.m.Y', header: lang['data_vydachi_tela'], width: 120 },
				{ name: 'PersonRecipient_FIO', type: 'string', header: lang['fio_poluchatelya_tela'], width: 200 },
				{ name: 'Document', type: 'string', format: 'd.m.Y', header: lang['dokument_udostoveryayuschiy_lichnost_poluchatelya'], width: 120 }

			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true },
				{name:'action_edit', hidden: true, disabled: true },
				{name:'action_delete',  hidden: true, disabled: true},
				{name:'action_save',  hidden: true, disabled: true },
				{name:'action_view', hidden: true },
				{name:'action_print', handler: function() { this.printJournal(); }.createDelegate(this) },
				
			],
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm,index,record) {
				//
			},
		});
		
		Ext.apply(this, 
		{
			layout:'border',
			defaults: {split: true},
			buttons: 
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[form.FilterPanel,
			{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout:'border',
				items: [form.SearchGrid]
			}]
		});
		sw.Promed.swRegistryDeadBodiesWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function()
	{
		sw.Promed.swRegistryDeadBodiesWindow.superclass.show.apply(this, arguments);

		this.getLoadMask().show();
		
		this.center();
		this.maximize();
		this.SearchGrid.removeAll();
		this.clearFilters();
		this.loadGridWithFilter();

		this.getLoadMask().hide();
	}
});