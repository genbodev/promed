/**
 * Рабочие списки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @autor        Dmitry Storozhev aka nekto_O
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @version      11.01.2012
 */
sw.Promed.swAnalyzerWorksheetJournalWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['rabochie_spiski'],
	id: 'AnalyzerWorksheetJournalWindow',
	modal: true,
	shim: false,
	maximized: true,
	layout: 'border',
	show: function () {
		sw.Promed.swAnalyzerWorksheetJournalWindow.superclass.show.apply(this, arguments);
		
		var that = this;
		if ( arguments[0].MedService_id ) {
			this.MedService_id = arguments[0].MedService_id;
		} else {
			this.MedService_id = null;
		}

		this.findById('AnalyzerWorksheetGrid').loadData({
			params: {
				MedService_id: that.MedService_id
			},
			globalFilters: {
				MedService_id: that.MedService_id
			}
		});
		
		this.grid.addActions({
			name: 'action_work',
			text:lang['v_rabotu'],
			handler: function () {
				var selected = that.grid.getGrid().getSelectionModel().getSelected();
				if (1 == selected.data.AnalyzerWorksheetStatusType_id) {
					var loadMask = new Ext.LoadMask(that.body, {msg: "Пожалуйста, подождите..."});
					Ext.Ajax.request({
						params: {
							AnalyzerWorksheet_id: selected.data.AnalyzerWorksheet_id
						},
						url: '/?c=AnalyzerWorksheet&m=work',
						callback: function (options, success, response) {
							loadMask.hide();
							if (success) {
								that.grid.loadData();
							}
						}
					});
				} else {
					Ext.Msg.alert(lang['vnimanie'], lang['v_rabotu_mojno_otpravit_tolko_novyie_rabochie_spiski']);
				}
			}
		});
		this.filterForm.getForm().findField('AnalyzerWorksheetStatusType_id').getStore().load();
	},
	openAnalyzerWorksheetEditWindow: function() {
		var that = this;
		var params = {
			callback: function(ct, id, data) {
				that.grid.loadData();
				data.action = 'edit';
				data.MedService_id = that.MedService_id;
				getWnd('swAnalyzerWorksheetEvnLabSampleWindow').show(data);
			},
			onHide: function() {
			},
			MedService_id: that.MedService_id,
			action: 'add'
		};
		getWnd('swAnalyzerWorksheetEditWindow').show(params);
	},
	doSearch: function(mode) {
		var params = this.filterForm.getForm().getValues();
		this.GridPanel.removeAll();
		this.GridPanel.loadData({globalFilters: params});
		return true;
	},
	doReset: function()	{
		this.filterForm.getForm().reset();
	},
	initComponent: function () {
		var that = this;
		this.grid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function () {this.openAnalyzerWorksheetEditWindow() }.createDelegate(this)},
				{name: 'action_edit'},
				{name: 'action_view', hidden: true},
				{
					name: 'action_delete',
				    handler: function (){
					    var selected = that.grid.getGrid().getSelectionModel().getSelected();
					    if (1 == selected.data.AnalyzerWorksheetStatusType_id) {
						    Ext.Msg.show({
							    title: lang['udalenie_rabochego_spiska'],
							    msg: lang['vyi_deystvitelno_hotite_udalit_rabochiy_spisok'],
							    buttons: Ext.Msg.YESNO,
							    fn: function(btn) {
								    if (btn === 'yes') {
									    var loadMask = new Ext.LoadMask(that.body, {msg: "Удаление рабочего списка..."});
									    Ext.Ajax.request({
										    params: {
											    AnalyzerWorksheet_id: selected.data.AnalyzerWorksheet_id
										    },
										    url: '/?c=AnalyzerWorksheet&m=delete',
										    callback: function(options, success, response) {
											    loadMask.hide();
											    if(success) {
												    that.grid.loadData();
											    }
										    }
									    });
								    }
							    },
							    icon: Ext.MessageBox.QUESTION
						    });
					    } else {
						    alert(lang['udalyat_mojno_tolko_rabochie_spiski_so_statusom_novyiy']);
					    }
				    }
				},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			scheme: 'lis',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=AnalyzerWorksheet&m=loadList',
			region: 'center',
			object: 'AnalyzerWorksheet',
			editformclassname: 'swAnalyzerWorksheetEvnLabSampleWindow',
			id: 'AnalyzerWorksheetGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'AnalyzerWorksheet_id', type: 'int', header: 'ID', key: true},
				{name: 'AnalyzerWorksheet_Code', type: 'string', header: lang['kod'], width: 120, isparams: true},
				{name: 'AnalyzerWorksheet_Name', type: 'string', header: lang['naimenovanie'], id:'autoexpand', width: 120, isparams: true},
				{name: 'AnalyzerWorksheet_setDT', type: 'string', header: lang['data_sozdaniya'], width: 120, isparams: true},
				{name: 'AnalyzerRack_DimensionX', type: 'int', hidden: true, isparams: true},
				{name: 'AnalyzerRack_DimensionY', type: 'int', hidden: true, isparams: true},
				{name: 'AnalyzerWorksheetStatusType_Name', type: 'string', header: lang['status_rabochego_spiska'], width: 150, isparams: true},
				{name: 'AnalyzerWorksheetStatusType_id', type: 'int', hidden: true, isparams: true},
				{name: 'Analyzer_id_Name', type: 'string', header: lang['analizator'], width: 120, isparams: true},
				{name: 'Analyzer_id', type: 'int', hidden: true, isparams: true}
			],
			//title: 'Рабочий список',
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				// todo: Вообще как-то неправильно переводить статус списка в работу и не давать менять его обратно.. А если по ошибке?
				if (record) {
					this.setActionDisabled('action_work', (record.get('AnalyzerWorksheetStatusType_id')!=1));
				}
			}
		});
		var filterForm = new Ext.form.FormPanel({
			floatable: false,
			autoHeight: true,
			animCollapse: false,
			labelAlign: 'right',
			defaults: {
				bodyStyle: 'background: #DFE8F6;'
			},
			region: 'north',
			frame: true,
			buttonAlign: 'left',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					that.doSearch();
				},
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				style:'padding: 0px 3px 3px 6px;',
				autoHeight: true,
				listeners: {
					expand: function() {
						this.ownerCt.doLayout();
						that.syncSize();
					},
					collapse: function() {
						that.syncSize();
					}
				},
				collapsible: true,
				collapsed: true,
				title: lang['filtr'],
				bodyStyle: 'background: #DFE8F6;',
				items: [
					{
					layout: 'column',
					items: [
						{
							layout:'form',
							bodyStyle:'background: #DFE8F6;',
							labelWidth:150,
							border:false,
							items:[
								{
									fieldLabel: lang['status_rabochego_spiska'],
									hiddenName: 'AnalyzerWorksheetStatusType_id',
									xtype: 'swcommonsprcombo',
									prefix:'lis_',
									allowBlank:true,
									sortField:'AnalyzerWorksheetStatusType_Code',
									comboSubject: 'AnalyzerWorksheetStatusType',
									width: 100
								}
							]
						},
						{
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [
								{
									xtype: 'button',
									handler: function()
									{
										that.doSearch();
									},
									iconCls: 'search16',
									text: BTN_FRMSEARCH
								}
							]
						},
						{
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [
								{
									xtype: 'button',
									handler: function()
									{
										this.doReset();
									}.createDelegate(this),
									iconCls: 'resetsearch16',
									text: BTN_FRMRESET
								}
							]
						}
					]
				}
				]
			}
			]
		});
		Ext.apply(this, {
			items: [
				filterForm,
				this.grid
			],
			buttons:
			[{
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					that.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swAnalyzerWorksheetJournalWindow.superclass.initComponent.apply(this, arguments);
		this.grid = this.findById('AnalyzerWorksheetGrid');
		this.GridPanel = this.grid;
		this.filterForm = filterForm;
	}
});