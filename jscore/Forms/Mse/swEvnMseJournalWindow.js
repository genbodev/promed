/**
* Форма Журнал МСЭ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      26.03.2012
*/

sw.Promed.swEvnMseJournalWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['jurnal_mediko-sotsialnoy_ekspertizyi'],
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swEvnMseJournalWindow',
	closeAction: 'hide',
	id: 'swEvnMseJournalWindow',
	objectSrc: '/jscore/Forms/Mse/swEvnMseJournalWindow.js',
	
	show: function()
	{
		sw.Promed.swEvnMseJournalWindow.superclass.show.apply(this, arguments);
		
		
		var b_f = this.FilterPanel.getForm(),
			mscombo = b_f.findField('MedService_id');
		
		mscombo.getStore().baseParams.Lpu_isAll = 1; // Всех ЛПУ
		mscombo.getStore().load({
			callback: function() {
				this.filterBy(function(rec) {
					var f = false;
					if( rec.get('MedServiceType_id') == 2 ) // Только МСЭ
						f = true;
					return f;
				});
				this.loadData(getStoreRecords(this));
			}
		});
	},
	
	doSearch: function()
	{
		var grid = this.Grid.ViewGridPanel,
			form = this.FilterPanel.getForm();
		if( !form.isValid() ) {
			return false;
		}
		grid.getStore().baseParams = form.getValues();
		grid.getStore().load();
	},
	
	doReset: function()
	{
		this.FilterPanel.getForm().reset();
		this.Grid.ViewGridPanel.getStore().baseParams = {};
		this.Grid.ViewActions.action_refresh.execute();
	},
	
	openEvnMse: function( action )
	{
		var rec = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if( !rec ) {
			return false;
		}
		getWnd('swProtocolMseEditForm').show({
			EvnMse_id: rec.get('EvnMse_id'),
			action: action,
			Person_id: rec.get('Person_id'),
			Server_id: rec.get('Server_id')
		});
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.FilterPanel = new Ext.FormPanel({
			collapsible: true,
			titleCollapse: true,
			region: 'north',
			title: lang['jurnal_mse_poisk'],
			autoHeight: true,
			defaults: {
				labelAlign: 'right',
				border: false
			},
			floatable: false,
			animCollapse: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			bodyStyle: 'padding: 3px;',
			layout: 'column',
			items: [
				{
					layout: 'form',
					defaults: {
						anchor: '100%'
					},
					width: 400,
					labelWidth: 150,
					hidden: getGlobalOptions().use_depersonalized_expertise,
					items: [
						{
							xtype: 'textfieldpmw',
							name: 'Person_SurName',
							fieldLabel: lang['familiya']
						}, {
							xtype: 'textfieldpmw',
							name: 'Person_FirName',
							fieldLabel: lang['imya']
						}, {
							xtype: 'textfieldpmw',
							name: 'Person_SecName',
							fieldLabel: lang['otchestvo']
						}, {
							xtype: 'swdatefield',
							anchor: '',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Person_BirthDay',
							fieldLabel: lang['d_r']
						}
					]
				}, {
					layout: 'form',
					defaults: {
						anchor: '100%'
					},
					width: 450,
					labelWidth: 230,
					items: [
						{
							xtype: 'numberfield',
							anchor: '100%',
							hidden: getRegionNick() != 'kz',
							hiddenName: 'EvnPrescrMse_Num', // такого поля нет в БД, пока не действует
							allowBlank: true,
							fieldLabel: langs('№ направления')
						},
						{
							xtype: 'swlpulocalcombo',
							allowBlank: true,
							hiddenName: 'Lpu_id',
							fieldLabel: 'МО прикрепления'
						}, {
							xtype: 'swmedserviceglobalcombo',
							fieldLabel: lang['slujba_mse']
						}, {
							xtype: 'daterangefield',
							anchor: '',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							name: 'EvnMse_setDT',
							width: 170,
							fieldLabel: lang['datyi_osvidetelstvovaniya_ot_␓_do']
						}, {
							xtype: 'swdiagcombo',
							fieldLabel: lang['diagnoz_mse']
						}
					]
				}
			]
		});
		
		
		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
			pageSize: 20,
			border: false,
			enableColumnHide: false,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', handler: this.openEvnMse.createDelegate(this, ['view']) },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnMse_id', type: 'int', hidden: true, key: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnMse_NumAct', type: 'int', header: langs('Номер'), width: 60 },
				{ name: 'Person_id', type: 'int', header: langs('ИД Пациента'), hidden: !getGlobalOptions().use_depersonalized_expertise, width: 100 },
				{ name: 'Person_Fio', type: 'string', header: langs('ФИО Пациента'), hidden: getGlobalOptions().use_depersonalized_expertise, width: 250 },
				{ name: 'Person_BirthDay', type: 'string', header: langs('Дата рождения'), hidden: getGlobalOptions().use_depersonalized_expertise, width: 100 },
				{ name: 'Lpu_Nick', type: 'string', header: 'МО прикрепления', width: 150 },
				{ name: 'EvnMse_setDT', type: 'string', header: langs('Дата освидет'), width: 100 },
				{ name: 'DiagMse_Name', type: 'string', header: langs('Диагноз МСЭ'), id: 'autoexpand' },
				{ name: 'InvalidGroupType_Name', type: 'string', header: langs('Установлена инвалидность'), width: 180 },
				{ name: 'EvnMse_Sign', type: 'string', header: langs('Документ подписан'), width: 180 },
				{ name: 'EvnMse_ReExamDate', type: 'string', header: langs('Дата переосвидет.'), width: 120 }
			],
			paging: true,
			dataUrl: '/?c=Mse&m=searchData',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			buttons: [
				{
					handler: this.doSearch.createDelegate(this),
					iconCls: 'search16',
					text: BTN_FRMSEARCH
				},
				{
					handler: this.doReset.createDelegate(this),
					iconCls: 'resetsearch16',
					text: BTN_FRMRESET
				},
				'-',
				HelpButton(this),
				{
					text: lang['zakryit'],
					tabIndex: -1,
					tooltip: lang['zakryit'],
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			],
			keys: 
			[{
				fn: function(inp, e) {
					if ( e.getKey() == Ext.EventObject.ENTER ) {
						this.doSearch();
					}
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [this.FilterPanel, this.Grid]
		});
		sw.Promed.swEvnMseJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});