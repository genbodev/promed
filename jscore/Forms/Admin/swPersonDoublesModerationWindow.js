/**
* swPersonDoublesModerationWindow - окно с интерфейсом для модерации людей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @originalauthor       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      04.05.2010
*/

sw.Promed.swPersonDoublesModerationWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	closeAction: 'hide',
	doReset: function() {
		//
	},
    doResetSearch: function(){
        var thisWindow = this;
        var viewframe = this.findById('PersonDoublesGrid');
        var grid = viewframe.getGrid();
        var form = this.findById('pd_search_form').getForm();
        form.reset();
        if(thisWindow.LpuOnly){
        	form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);
        }
		form.findField('exceptSelectedLpu').disable();
		this.findById('PDMW_PersonInformationFrame1').reset();
		this.findById('PDMW_PersonInformationFrame2').reset();
		this.findById('PDMW_PersonInformationFrame3').reset();
        grid.getStore().removeAll();
        viewframe.clearTopTextCounter();
    },
	doSearch: function() {
        var thisWindow = this;
        var grid = this.findById('PersonDoublesGrid').getGrid();
        var form = this.findById('pd_search_form');
        var baseParams = form.getForm().getValues();
        if(form.getForm().findField('Lpu_did').disabled){
        	baseParams.Lpu_did = form.getForm().findField('Lpu_did').getValue();
        }
        grid.getStore().baseParams = baseParams;
        this.findById('PersonDoublesGrid').loadData();
	},
	draggable: false,
	id: 'PersonDoublesModerationWindow',
	selected_record: null,
	initComponent: function() {
		var wnd = this;
		
		Ext.apply(this, {
			buttonAlign: 'right',
			buttons: [ HelpButton(this), {
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'PDSW_CancelButton',
				text: lang['zakryit']
			}],
			enableKeyEvents: true,
			items: [ {
				border: false,
				layout: 'border',
				region: 'center',
				split: true,

				items: [
                    new Ext.form.FormPanel({
                        frame: true,
                        autoHeight: true,
                        region: 'north',
                        autoLoad: false,
                        buttonAlign: 'left',
                        width: 600,
                        id:'pd_search_form',
                        bodyStyle:'background:#FFF;padding:0;',
                        items: [
                            {
                                xtype: 'swtranslatedtextfield',
                                fieldLabel: lang['familiya'],
                                maskRe: /[^%]/,
                                name: 'PersonSurName',
                                width: 490
                            },
                            {
                                xtype: 'swtranslatedtextfield',
                                fieldLabel: lang['imya'],
                                maskRe: /[^%]/,
                                name: 'PersonFirName',
                                width: 490
                            },
                            {
                                xtype: 'swtranslatedtextfield',
                                fieldLabel: lang['otchestvo'],
                                maskRe: /[^%]/,
                                name: 'PersonSecName',
                                width: 490
                            },
                            {
                                xtype: 'swdatefield',
                                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                fieldLabel: lang['data_rojdeniya'],
                                format: 'd.m.Y',
                                name: 'PersonBirthDay'
                            },
							{
								layout: 'column',
								items: [{
									layout: 'form',
									border: false,
									items: [{
										xtype: 'swlpucombo',
										hiddenName: 'Lpu_did',
										fieldLabel: lang['mo'],
										listeners: {
											'change': function(combo, newValue, oldValue) {
												var index = combo.getStore().findBy(function(rec) {
													return (rec.get('Lpu_id') == newValue);
												});
												combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
											},
											'select': function(combo, record, index) {
												var base_form = this.findById('pd_search_form').getForm();

												if ( typeof record == 'object' && !Ext.isEmpty(record.get('Lpu_id')) ) {
													base_form.findField('exceptSelectedLpu').enable();
												}
												else {
													base_form.findField('exceptSelectedLpu').disable();
													base_form.findField('exceptSelectedLpu').setValue(null);
												}
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 2,
									items: [{
										disabled: true,
										xtype: 'checkbox',
										name: 'exceptSelectedLpu',
										labelSeparator: '',
										boxLabel: lang['krome_ukazannoy']
									}]
								}]
							}
                        ],
                        buttons: [
                            {
                                text: BTN_FRMSEARCH,
                                iconCls: 'search16',
                                handler: function() { Ext.getCmp("PersonDoublesModerationWindow").doSearch() }
                            },
                            {
                                text: BTN_FRMRESET,
                                iconCls: 'resetsearch16',
                                handler: function() {
                                    Ext.getCmp("PersonDoublesModerationWindow").doResetSearch()
                                }
                            }
                        ],
                        keys: [{
                            key: Ext.EventObject.ENTER,
                            fn: function(e) {
                                Ext.getCmp("PersonDoublesModerationWindow").doSearch()
                            },
                            stopEvent: true
                        }]
                    }),
					new sw.Promed.ViewFrame({
						actions: [
							//{ name: 'action_refresh', handler:function() { Ext.getCmp("PersonDoublesModerationWindow").doSearch(false) } },
                            { name: 'action_refresh', disabled: true, hidden: true },
							{ name: 'action_add', disabled: true, hidden: true },
							{ name: 'action_edit', disabled: true, hidden: true },
							{ name: 'action_view', disabled: true, hidden: true },
							{ name: 'action_delete', disabled: true, hidden: true },
							{ name: 'action_print', disabled: true, hidden: true }
						],
						autoLoadData: false,
						dataUrl: '/?c=PersonDoubles&m=loadPersonDoublesModerationList',
						id: 'PersonDoublesGrid',
						loadPersonDoublesDataGrid: function (sm, index, record) {
							//
						}.createDelegate(this),
						onLoadData: function() {
							var grid = this.findById('PersonDoublesGrid').getGrid();
							if ( this.selected_record && this.selected_record != undefined ) {
								var idx = grid.getStore().indexOfId(this.selected_record.id)
								if (idx != -1 ) {
									grid.getView().focusRow(grid.getStore().indexOfId(this.selected_record.id));
									grid.getSelectionModel().selectRow(grid.getStore().indexOfId(this.selected_record.id));
								} else {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectRow(0);
								}
							}
						}.createDelegate(this),
						onRowSelect: function (sm, index, record) {
							if ( !record.get('Person_id1') ) {
								return false;
							}

							this.findById('PDMW_PersonInformationFrame1').load({
								Person_id: record.get('Person_id1'),
								Server_id: record.get('Server_id1'),
								callback: function(form) {
									this.findById('PDMW_PersonInformationFrame2').load({
										Person_id: record.get('Person_id2'),
										Server_id: record.get('Server_id2') 
									});
								}.createDelegate(this)
							});
						}.createDelegate(this),
						region: 'west',
						stringfields: [
							{ name: 'PersonDoubles_id', type: 'int', header: 'ID', key: true, hidden: true },
							{ name: 'Person_id1', type: 'int', hidden: true },
							{ name: 'Person_id2', type: 'int', hidden: true },
							{ name: 'Person_Surname', header: lang['familiya'], id: 'autoexpand_groups', autoExpandMin: 100 },
							{ name: 'Person_Firname', width: 100, header: lang['imya'] },
							{ name: 'Person_Secname', width: 100, header: lang['otchestvo'] },
							{ name: 'Person_BirthDay', header: lang['data_rojdeniya'], width: 90 },
							{ name: 'Server_id1', type: 'int', hidden: true },
							{ name: 'Server_id2', type: 'int', hidden: true },
							{ name: 'Server_id', header: lang['identifikator_servera'], width: 90 },
							{ name: 'Person_IsBDZ1', type: 'int', hidden: true },
							{ name: 'Person_IsBDZ2', type: 'int', hidden: true },
							{ name: 'Person_IsBDZ', header: lang['bdz'], width: 40},
							{ name: 'Lpu_Nick', header: lang['mo'], width: 90 }
						],
						title: lang['dvoyniki'],
						toolbar: true,
                        paging: true,
						width: 600,
                        totalProperty: 'totalCount',
                        root: 'data'

					}),
					new Ext.Panel({
						layout: "form",
						bodyBorder: false,
						bodyStyle: 'padding: 1px 1px',
						border: true,
						frame: false,
						height:28,
						region: 'center',
						items: [ 
							new sw.Promed.PersonDoublesInformationPanel({
								button1OnHide: function() {
									if ( this.action == 'view' ) {
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this),
								button2Callback: function(callback_data) {
									var form = this.findById('PDMW_PersonInformationFrame1');

									//form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
									//form.getForm().findField('Server_id').setValue(callback_data.Server_id);

									this.findById('PDMW_PersonInformationFrame1').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
								}.createDelegate(this),
								button2OnHide: function() {
									this.findById('PDMW_PersonInformationFrame1').button1OnHide();
								}.createDelegate(this),
								button3OnHide: function() {
									this.findById('PDMW_PersonInformationFrame1').button1OnHide();
								}.createDelegate(this),
								button4OnHide: function() {
									this.findById('PDMW_PersonInformationFrame1').button1OnHide();
								}.createDelegate(this),
								button5OnHide: function() {
									this.findById('PDMW_PersonInformationFrame1').button1OnHide();
								}.createDelegate(this),
								id: 'PDMW_PersonInformationFrame1',
								region: 'north',
								border: true,
								height: 170,
								title: lang['glavnaya_zapis']
							}),
							new sw.Promed.PersonDoublesInformationPanel({
								button1OnHide: function() {
									if ( this.action == 'view' ) {
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this),
								button2Callback: function(callback_data) {
									var form = this.findById('PDMW_PersonInformationFrame2');

									//form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
									//form.getForm().findField('Server_id').setValue(callback_data.Server_id);

									this.findById('PDMW_PersonInformationFrame2').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
								}.createDelegate(this),
								button2OnHide: function() {
									this.findById('PDMW_PersonInformationFrame2').button1OnHide();
								}.createDelegate(this),
								button3OnHide: function() {
									this.findById('PDMW_PersonInformationFrame2').button1OnHide();
								}.createDelegate(this),
								button4OnHide: function() {
									this.findById('PDMW_PersonInformationFrame2').button1OnHide();
								}.createDelegate(this),
								button5OnHide: function() {
									this.findById('PDMW_PersonInformationFrame2').button1OnHide();
								}.createDelegate(this),
								id: 'PDMW_PersonInformationFrame2',
								region: 'center',
								border: true,
								height: 170,
								title: lang['dvoynik']
							}),
							new sw.Promed.PersonDoublesInformationPanel({
								button1OnHide: function() {
									if ( this.action == 'view' ) {
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this),
								button2Callback: function(callback_data) {
									var form = this.findById('PDMW_PersonInformationFrame3');

									//form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
									//form.getForm().findField('Server_id').setValue(callback_data.Server_id);

									this.findById('PDMW_PersonInformationFrame3').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
								}.createDelegate(this),
								button2OnHide: function() {
									this.findById('PDMW_PersonInformationFrame3').button1OnHide();
								}.createDelegate(this),
								button3OnHide: function() {
									this.findById('PDMW_PersonInformationFrame3').button1OnHide();
								}.createDelegate(this),
								button4OnHide: function() {
									this.findById('PDMW_PersonInformationFrame3').button1OnHide();
								}.createDelegate(this),
								button5OnHide: function() {
									this.findById('PDMW_PersonInformationFrame3').button1OnHide();
								}.createDelegate(this),
								id: 'PDMW_PersonInformationFrame3',
								region: 'south',
								border: true,
								height: 170,
								title: lang['posledniy_obyedinennyiy']
							})
						],
						buttonAlign: 'left',
						buttons: [
						{
							handler: function() {
								if (Ext.isEmpty(this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id')) || Ext.isEmpty(this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id'))) {
									sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
									return false;
								}
								
								var Records = [
									{
										"Person_id": this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id'),
										"Server_id": this.findById('PDMW_PersonInformationFrame1').getFieldValue('Server_pid'),
										"IsMainRec": 1
									},
									{
										"Person_id": this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id'),
										"Server_id": this.findById('PDMW_PersonInformationFrame2').getFieldValue('Server_pid'),
										"IsMainRec": 0
									}
								];
								var Mask = new Ext.LoadMask(Ext.get('PersonDoublesModerationWindow'), {msg:"Пожалуйста, подождите, идет объединение людей..."});
								Mask.show();
								this.selected_record = this.findById('PersonDoublesGrid').getGrid().getSelectionModel().getSelected();
								Ext.Ajax.request({
									url: C_PERSON_UNION,
									success: function(result){
										if ( result.responseText.length > 0 ) {
											var resp_obj = Ext.util.JSON.decode(result.responseText);
											if (resp_obj.success == true) {
                                                if (resp_obj.Info_Msg) {
                                                    sw.swMsg.alert(lang['soobschenie'], resp_obj.Info_Msg);
                                                }
                                                if (Ext.isEmpty(this.selected_record)) {
                                                    sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
                                                    Mask.hide();
                                                    return false;
                                                }

												this.findById('PDMW_PersonInformationFrame3').load({
													Person_id: this.selected_record.get('Person_id1'),
													Server_id: this.selected_record.get('Server_id1')
												});

												var indexSelectedRec = this.findById('PersonDoublesGrid').getGrid().getStore().indexOf(this.selected_record);
												if ( this.findById('PersonDoublesGrid').getGrid().getSelectionModel().hasNext() ) {
													this.findById('PersonDoublesGrid').getGrid().getStore().remove(this.selected_record);
													this.findById('PersonDoublesGrid').getGrid().getSelectionModel().selectRow(indexSelectedRec, false);
												} else if ( this.findById('PersonDoublesGrid').getGrid().getSelectionModel().hasPrevious() ){
													this.findById('PersonDoublesGrid').getGrid().getStore().remove(this.selected_record);
													this.findById('PersonDoublesGrid').getGrid().getSelectionModel().selectRow(--indexSelectedRec, false);
												} else{
													this.findById('PersonDoublesGrid').getGrid().getStore().remove(this.selected_record);
												}
											}
										}
										Mask.hide();
									}.createDelegate(this),
									params: {
										'Records': Ext.util.JSON.encode(Records),
										'PersonDoubles_id': this.selected_record.get('PersonDoubles_id'),
										'fromModeration': 1
									},
									failure: function(result){
										Mask.hide();
									},
									method: 'POST',
										timeout: 600000
								});
							}.createDelegate(this),
							iconCls: 'union16',
							id: 'PDMW_UnionButtonNow',
							text: lang['obyedinit_seychas']
						},
						{
							handler: function() {
								if (Ext.isEmpty(this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id')) || Ext.isEmpty(this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id'))) {
									sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
									return false;
								}
								
								var Records = [
									{
										"Person_id": this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id'),
										"Server_id": this.findById('PDMW_PersonInformationFrame1').getFieldValue('Server_pid'),
										"IsMainRec": 1
									},
									{
										"Person_id": this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id'),
										"Server_id": this.findById('PDMW_PersonInformationFrame2').getFieldValue('Server_pid'),
										"IsMainRec": 0
									}
								];
								var Mask = new Ext.LoadMask(Ext.get('PersonDoublesModerationWindow'), {msg:"Пожалуйста, подождите, идет объединение людей..."});
								Mask.show();
								this.selected_record = this.findById('PersonDoublesGrid').getGrid().getSelectionModel().getSelected();
								Ext.Ajax.request({
									url: C_PLAN_PERSON_UNION,
									success: function(result){
										if ( result.responseText.length > 0 ) {
											var resp_obj = Ext.util.JSON.decode(result.responseText);
											if (resp_obj.success == true) {
												if (Ext.isEmpty(this.selected_record)) {
                                                    sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
                                                    Mask.hide();
                                                    return false;
                                                }

												this.findById('PDMW_PersonInformationFrame3').load({
													Person_id: this.selected_record.get('Person_id1'),
													Server_id: this.selected_record.get('Server_id1')
												});
												
												if ( this.findById('PersonDoublesGrid').getGrid().getSelectionModel().hasNext() ) {
													this.findById('PersonDoublesGrid').getGrid().getSelectionModel().selectNext(false);
												} else if ( this.findById('PersonDoublesGrid').getGrid().getSelectionModel().hasPrevious() ){
													this.findById('PersonDoublesGrid').getGrid().getSelectionModel().selectPrevious(false);
												}
												this.findById('PersonDoublesGrid').getGrid().getStore().remove(this.selected_record);
											}
										}
										Mask.hide();
									}.createDelegate(this),
									params: {
											'Records': Ext.util.JSON.encode(Records)
										},
									failure: function(result){
										Mask.hide();
									},
									method: 'POST',
										timeout: 120000
								});
							}.createDelegate(this),
							iconCls: 'union16',
							id: 'PDMW_UnionButtonQueue',
							text: lang['zaplanirovat_obyedinenie']
						},
						{
							handler: function() {
								if (Ext.isEmpty(this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id')) || Ext.isEmpty(this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id'))) {
									sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
									return false;
								}
								
								var Mask = new Ext.LoadMask(Ext.get('PersonDoublesModerationWindow'), {msg:"Пожалуйста, подождите, идет смена записей..."});
								Mask.show();
								this.selected_record = this.findById('PersonDoublesGrid').getGrid().getSelectionModel().getSelected();
                                if (Ext.isEmpty(this.selected_record)) {
                                    sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
                                    Mask.hide();
                                    return false;
                                }

								controlStoreRequest = Ext.Ajax.request({
									url: C_PERSON_DCHANGE,
									success: function(result){
										Mask.hide();
										this.findById('PersonDoublesGrid').loadData();
									}.createDelegate(this),
									params: {
										"Person_id": this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id'),
										"Person_did": this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id')
									},
									failure: function(result){
										Mask.hide();
									},
									method: 'POST',
										timeout: 120000
								});
							}.createDelegate(this),
							iconCls: 'refresh16',
							id: 'PDMW_ChangeButton',
							text: lang['pomenyat_mestami_zapisi']
						},
						{
							handler: function() {
								if (Ext.isEmpty(this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id')) || Ext.isEmpty(this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id'))) {
									sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
									return false;
								}
                                this.selected_record = this.findById('PersonDoublesGrid').getGrid().getSelectionModel().getSelected();
                                if (Ext.isEmpty(this.selected_record)) {
                                    sw.swMsg.alert(lang['soobschenie'], lang['ne_vyibranyi_dvoyniki_deystvie_ne_vozmojno']);
                                    return false;
                                }

								var params = {
									Person_id: this.findById('PDMW_PersonInformationFrame1').getFieldValue('Person_id'),
									Person_did: this.findById('PDMW_PersonInformationFrame2').getFieldValue('Person_id'),
									callback: function() {
										wnd.findById('PersonDoublesGrid').loadData();
									}
								};
								
								getWnd('swPersonDoublesCancelWindow').show(params);
							}.createDelegate(this),
							iconCls: 'cancel16',
							id: 'PDMW_DenyButton',
							text: lang['otkazat_v_obyedinenii']
						}]
					})
				]
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if (e.altKey) {
						Ext.getCmp('PersonDoublesModerationWindow').hide();
					}
					else {
						return true;
					}
				},
				key: [ Ext.EventObject.P ],
				stopEvent: false
			}]
		});
		sw.Promed.swPersonDoublesModerationWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	maximized: true,
	modal: true,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonDoublesModerationWindow.superclass.show.apply(this, arguments);
		//this.findById('PersonDoublesGrid').loadData();
		var form = this.findById('pd_search_form');
		if(arguments[0] && arguments[0].LpuOnly){
        	this.LpuOnly = arguments[0].LpuOnly;
        	form.getForm().findField('Lpu_did').disable();
		} else {
			this.LpuOnly = false;
			form.getForm().findField('Lpu_did').enable();
		}
		this.doResetSearch();
		this.doSearch();
	},
	title: lang['moderatsiya_zapisey_dvoynikov']
});