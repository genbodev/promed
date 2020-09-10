/**
 * swPersonPregnancyFinishedWindow - окно просмотра регистра беременных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      11.2014
 *
 */

sw.Promed.swPersonPregnancyFinishedWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	layout: 'border',
	maximized: true,
	resizable: false,
	closable: true,
	shim: false,
	width: 500,
	closeAction: 'hide',
	id: 'swPersonPregnancyFinishedWindow',
	objectName: 'swPersonPregnancyFinishedWindow',
	title: lang['rodyi'],
	plain: true,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	show: function()
	{
		sw.Promed.swPersonPregnancyFinishedWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.filtersPanel.getForm();
		base_form.reset();
		base_form.findField('EvnUsluga_setDate_From').setValue('01.01.2015');
		base_form.findField('EvnUsluga_setDate_To').setValue(getGlobalOptions().date);
		this.doSearch();
	},
	doSearch: function(clear) {
		var base_form = this.filtersPanel.getForm();

		if (clear) {
			base_form.reset();
		}

		var params = base_form.getValues();

		params.start = 0;
		params.limit = 100;

		this.GridPanel.getGrid().getStore().load({
			params: params
		});
	},
	openEmk: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		if (record)
		{
			var params = {
				userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
				Person_id: record.get('Person_id'),
				mode: 'workplace',
				ARMType: 'common',
				searchNodeObj: {
					parentNodeId: 'root',
					last_child: false,
					disableLoadViewForm: false,
					EvnClass_SysNick: 'EvnPS',
					Evn_id: record.get('EvnPS_id')
				}
			};
			getWnd('swPersonEmkWindow').show(params);
		}
	},
	initComponent: function()
	{
		var win = this;

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', text: lang['otkryit_emk'], handler: function() { win.openEmk(); } },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true }
			],
			uniqueId: true,
			autoLoadData: false,
			dataUrl: '/?c=PersonPregnancy&m=loadFinishedList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ name: 'PersonPregnancy_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'EvnPS_id', type: 'int', hidden: true },
				{ name: 'Person_Fio', type: 'string', header: lang['fio'], width: 150, id: 'autoexpand' },
				{ name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 150 },
				{ name: 'Lpu_Nick', type: 'string', header: lang['mo_gospitalizatsii'], width: 150 },
				{ name: 'LeaveType_Name', type: 'string', header: lang['ishod'], width: 150 },
				{ name: 'EvnPS_setDate', type: 'date', header: lang['data_postupleniya'], width: 150 },
				{ name: 'EvnPS_disDate', type: 'date', header: lang['data_vyipiski'], width: 150 },
				{ name: 'EvnUsluga_setDate', type: 'date', header: lang['data_ishoda'], width: 150 }
			],
			toolbar: true
		});

		this.filtersPanel = new Ext.form.FormPanel({
			frame: true,
			region: 'north',
			autoHeight: true,
			border: true,
			labelAlign: 'right',
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 80,
					items: [{
						xtype: 'textfield',
						anchor: '100%',
						name: 'Person_SurName',
						fieldLabel: lang['familiya']
					}, {
						xtype: 'textfield',
						anchor: '100%',
						name: 'Person_FirName',
						fieldLabel: lang['imya']
					}, {
						xtype: 'textfield',
						anchor: '100%',
						name: 'Person_SecName',
						fieldLabel: lang['otchestvo']
					}]
				}, {
					layout: 'form',
					labelWidth: 120,
					items: [{
						xtype: 'swdatefield',
						format:'d.m.Y',
						plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
						anchor: '100%',
						name: 'Person_BirthDay',
						fieldLabel: lang['data_rojdeniya']
					}, {
						xtype: 'swdatefield',
						format:'d.m.Y',
						plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
						anchor: '100%',
						name: 'EvnUsluga_setDate_From',
						fieldLabel: lang['data_ishoda_ot']
					}, {
						xtype: 'swdatefield',
						format:'d.m.Y',
						plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
						anchor: '100%',
						name: 'EvnUsluga_setDate_To',
						fieldLabel: lang['data_ishoda_do']
					}]
				}, {
					layout: 'form',
					width: 400,
					labelWidth: 150,
					items: [{
						fieldLabel: lang['mo_gospitalizatsii'],
						anchor: '100%',
						loadParams: {params: {where: ' where Lpu_EndDate is null'}},
						hiddenName: 'HospLpu_id',
						xtype: 'swlpusearchcombo'
					}, {
						fieldLabel: lang['ishod_gospitalizatsii'],
						anchor: '100%',
						hiddenName: 'LeaveType_id',
						xtype: 'swleavetypecombo'
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							style: 'margin-left: 150px;',
							items: [{
								xtype: 'button',
								text: BTN_FRMSEARCH,
								handler: function(){
									win.doSearch();
								},
								iconCls: 'search16'
							}]
						}, {
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [{
								xtype: 'button',
								text: lang['sbros'],
								handler: function(){
									win.doSearch(true);
								},
								iconCls: 'resetsearch16'
							}]
						}]
					}]
				}]
			}],
			keys: [{
				fn: function() {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this,
		{
			defaults:
			{
				border: false,
				bodyStyle: 'padding: 3px;'
			},
			items: [this.filtersPanel, this.GridPanel]
		});
		sw.Promed.swPersonPregnancyFinishedWindow.superclass.initComponent.apply(this, arguments);
	}
});