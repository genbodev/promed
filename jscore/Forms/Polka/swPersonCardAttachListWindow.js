/**
* swPersonCardAttachListWindow - окно "Список заявлений о выборе МО"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Storozhev
* @version      25.09.2012
*/

sw.Promed.swPersonCardAttachListWindow = Ext.extend(sw.Promed.BaseForm, {
	height: 500,
	width: 800,
	id: 'swPersonCardAttachListWindow',
	title: lang['spisok_zayavleniy_o_vyibore_mo'],
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	show: function() 
	{
		sw.Promed.swPersonCardAttachListWindow.superclass.show.apply(this, arguments);

		var bf = this.Filters.getForm(),
			lpuCombo = bf.findField('Lpu_id');
			
		lpuCombo.setDisabled(!isSuperAdmin());
		if(isSuperAdmin()) {
			lpuCombo.reset();
			lpuCombo.fireEvent('select', lpuCombo);
		} else {
			lpuCombo.setValue(getGlobalOptions().lpu_id);
		}
		
		this.doSearch();
	},
	
	doSearch: function(cb) {
		var store = this.Grid.ViewGridPanel.getStore(),
			frm = this.Filters.getForm();
		
		store['baseParams'] = frm.getValues();
		store['baseParams']['Lpu_id'] = frm.findField('Lpu_id').getValue();
		store.load({ callback: cb || Ext.emptyFn });
	},
	
	initComponent: function() 
	{
		this.Filters = new Ext.FormPanel({
			region: 'north',
			autoHeight: true,
			frame: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearch(f.focus.createDelegate(f));
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				title: lang['filtr'],
				autoHeight: true,
				labelAlign: 'right',
				collapsible: true,
				listeners: {
					collapse: function(p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this),
					expand: function(p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this)
				},
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 70,
						defaults: {
							anchor: '100%'
						},
						items: [{
							xtype: 'textfield',
							name: 'Person_SurName',
							fieldLabel: lang['familiya']
						}, {
							xtype: 'textfield',
							name: 'Person_FirName',
							fieldLabel: lang['imya']
						}, {
							xtype: 'textfield',
							name: 'Person_SecName',
							fieldLabel: lang['otchestvo']
						}]
					}, {
						layout: 'form',
						width: 300,
						defaults: {
							anchor: '100%'
						},
						items: [{
							xtype: 'swlpucombo',
							listeners: {
								/*render: function() {
									this.getStore().on('load', function() {
										var newrec;
										this.each(function(r) {
											if( Ext.isEmpty(r.get('Lpu_id')) ) {
												this.remove(r);
											}
											if( r.get('Lpu_id') === 0 ) {
												newrec = r;
											}
										}.createDelegate(this));
										this.remove(newrec); return;
										//log(newrec); return;
										
										if( typeof newrec == 'undefined' ) {
											newrec = {};
											var storeFields = this.fields.keys;
											for(var i=0; i<storeFields.length; i++) {
												newrec[storeFields[i]] = '';
											}
											newrec['Lpu_id'] = 0;
											newrec['Lpu_Nick'] = lang['vse_mo'];
											this.add(new Ext.data.Record(newrec));
										}
									});
								}*/
								select: function(c) {
									if(Ext.isEmpty(this.getValue())) {
										this.setValue(0);
										this.setRawValue(lang['vse_mo']);
									}
								}
							},
							fieldLabel: lang['mo']
						}]
					}]
				}]
			}]
		});
		
		this.Grid = new sw.Promed.ViewFrame({
			region: 'center',
			id: this.id + '_Grid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			pageSize: 100,
			border: false,
			actions: [
				{ name: 'action_add', text: lang['prikrepit'], disabled: true, tooltip: lang['prikrepit'], handler: function() {}.createDelegate(this) },
				{ name: 'action_edit', tooltip: '', hidden: true, handler: function(){}.createDelegate(this) },
				{ name: 'action_view', tooltip: '', hidden: true, handler: function(){}.createDelegate(this) },
				{ name: 'action_delete', disabled: true, tooltip: 'Удалить заявление', handler: function(){}.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			root: 'data',
			stringfields: [
				{ name: 'PersonCardAttach_id', type: 'int', hidden: true, key: true },
				{ name: 'PersonCardAttach_setDate', header: langs('Дата заявления'), width: 110,type:'date'/*, renderer: Ext.util.Format.dateRenderer('d.m.Y')*/ },
				{ name: 'Person_Fio', header: langs('Человек'), width: 300 },
				{ name: 'Lpu_N_Nick', type: 'string', header: langs('МО, принявшая заявление'), width: 200 },
				{ name: 'Lpu_O_Nick', type: 'string', header: langs('МО обслуживания'), id: 'autoexpand', width: 200 }
			],
			paging: true,
			dataUrl: '/?c=PersonCard&m=loadPersonCardAttachGrid',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this, {
			layout: 'border',
			items: [this.Filters, this.Grid],
			buttons: ['-', {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function() {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCLOSE,
				tabIndex: -1,
				tooltip: lang['zakryit'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swPersonCardAttachListWindow.superclass.initComponent.apply(this, arguments);
	}
});