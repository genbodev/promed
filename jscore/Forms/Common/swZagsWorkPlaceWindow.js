/**
 * swZagsWorkPlaceWindow - окно рабочего места работника ЗАГС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.05.2014
 */

sw.Promed.swZagsWorkPlaceWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	enableDefaultActions: false,
	id: 'swZagsWorkPlaceWindow',
	showToolbar: false,
	buttonPanelActions: {
		swMedSvidDeathAction: {
			text: lang['svidetelstva_o_smerti'],
			tooltip: lang['svidetelstva_o_smerti'],
			iconCls: 'svid-death16',
			handler: function()
			{
				getWnd('swMedSvidDeathStreamWindow').show({ARMType: 'zags'});
			}
		},
		swMedSvidPDeathAction: {
			text: lang['svidetelstva_o_perinatalnoy_smerti'],
			tooltip: lang['svidetelstva_o_perinatalnoy_smerti'],
			iconCls: 'svid-pdeath16',
			handler: function()
			{
				getWnd('swMedSvidPntDeathStreamWindow').show({ARMType: 'zags'});
			}
		}
	},

	doSearch: function(clear) {
		var form = this;
		if (clear) {
			form.doReset();
			this.GridPanel.removeAll();
			this.GridPanel.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					IsBad: '',
					Start_Date: '',
					End_Date: '',
					Lpu_id: 0,
					Person_Surname: '',
					Person_Firname: '',
					Person_Secname: '',
                    Child_Surname: '',
                    Sex_id: '',
                    Child_BirthDate: ''
				}
			});
		} else {
			var base_form = this.FilterPanel.getForm();
			var params = base_form.getValues();
			var Lpu_id = base_form.findField('Lpu_id').getValue() || '0';
			params.limit = 100;
			params.start = 0;
			this.GridPanel.removeAll();
			this.GridPanel.loadData({
				globalFilters: params
			});
		}
	},
	doReset: function () {
		var base_form = this.FilterPanel.getForm();
		var state_combo = base_form.findField('IsBad');
		if (state_combo) state_combo.setValue(0);

		base_form.findField('Give_Date').reset();
		base_form.findField('Person_Surname').reset();
		base_form.findField('Person_Firname').reset();
		base_form.findField('Person_Secname').reset();
        base_form.findField('Child_Surname').reset();
        base_form.findField('Child_BirthDate').reset();
        base_form.findField('Sex_id').reset();
	},

	printBirthSvid: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var isbad_id = record.get('BirthSvid_isBad');
		if (isbad_id != 1) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_otmecheno_kak_isporchennoe']); return false; }
		if (!record) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrano_ni_odno_svidetelstvo']);
			return false;
		}
		var svid_id = record.get('BirthSvid_id');
		if ( !svid_id ) { return false; }
		if(getRegionNick() == 'kz'){
        	printBirt({
				'Report_FileName': 'BirthSvid_Print.rptdesign',// #133782 BirthSvid.rptdesign - BirthSvid_Print.rptdesign
				'Report_Params': '&paramBirthSvid=' + svid_id,
				'Report_Format': 'pdf'
			});
			printBirt({
				'Report_FileName': 'BirthSvid_Print_check.rptdesign',// #133782 BirthSvid_check.rptdesign - BirthSvid_Print_check.rptdesign
				'Report_Params': '&paramBirthSvid=' + svid_id,
				'Report_Format': 'pdf'
			});
        } else {
			/*var id_salt = Math.random();
			var win_id = 'print_svid' + Math.floor(id_salt * 10000);
			var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + svid_id + '&svid_type=birth', win_id);*/
			printBirt({
				'Report_FileName': 'BirthSvid_Print.rptdesign',
				'Report_Params': '&paramBirthSvid=' + svid_id,
				'Report_Format': 'pdf'
			});
		}
	},

	openBirthSvidWindow: function(action) {
		var params = new Object();
		var grid = this.GridPanel.getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('BirthSvid_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var isbad_id = record.get('BirthSvid_isBad');
		if (isbad_id == 1 && action == 'edit') { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_ne_otmecheno_kak_isporchennoe']); return false; }
		params = record.data;
		getWnd('swMedSvidBirthEditWindow').show({
			action: action,
			formParams: params
		});
	},

	show: function()
	{
		sw.Promed.swZagsWorkPlaceWindow.superclass.show.apply(this, arguments);

		with ( this.LeftPanel.actions ) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}
        if(!this.FilterPanel.fieldSet.expanded){
            this.FilterPanel.fieldSet.expand();
        }
		this.GridPanel.setParam('start', 0);
	},

	initComponent: function()
	{

		var isBadStore = new Ext.data.SimpleStore({
			fields: [
				'IsBad',
				'displayText'
			],
			data: [[0, lang['vse']], [1, lang['tolko_deystvuyuschie']], [2, lang['tolko_isporchennyie']]]
		});

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: this,
			id: 'zwpwFilterPanel',
			filter: {
				title: lang['filtryi'],
				layout: 'form',
				items: [{
					layout: 'column',
					border: false,
					labelAlign: 'right',
					labelWidth: 140,
					items: [{
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-right:5px;',
						//columnWidth: .45,
						width: 340,
						labelAlign: 'right',
						labelWidth: 140,
						items: [
							new Ext.form.ComboBox({
								fieldLabel: lang['tip_svidetelstv'],
								hiddenName: 'IsBad',
								width: 175,
								typeAhead: true,
								triggerAction: 'all',
								lazyRender:true,
								mode: 'local',
								store: isBadStore,
								value: 0,
								valueField: 'IsBad',
								displayField: 'displayText'
							}),{
								xtype: 'swlpucombo',
								width: 175,
								fieldLabel: lang['mo'],
								listWidth: 500,
								name: 'Lpu_id'
							}, {
								xtype: 'textfield',
								maxLength: 30,
								width: 175,
								plugins: [ new Ext.ux.translit(true, true) ],
								fieldLabel: lang['familiya_materi'],
								name: 'Person_Surname'
							}, {
								xtype: 'textfield',
								maxLength: 30,
								width: 175,
								plugins: [ new Ext.ux.translit(true, true) ],
								fieldLabel: lang['imya_materi'],
								name: 'Person_Firname'
							}, {
								xtype: 'textfield',
								maxLength: 30,
								width: 175,
								plugins: [ new Ext.ux.translit(true, true) ],
								fieldLabel: lang['otchestvo_materi'],
								name: 'Person_Secname'
							}
						]
					}, {
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-right:5px;',
						//columnWidth: .36,
						labelAlign: 'right',
						labelWidth: 150,
						items:
							[{
								xtype: 'daterangefield',
								width: 175,
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
								fieldLabel: lang['period_datyi_vyidachi'],
								name: 'Give_Date'
							}, {
                                xtype: 'textfield',
                                maxLength: 30,
                                width: 175,
                                plugins: [ new Ext.ux.translit(true, true) ],
                                fieldLabel: lang['familiya_rebenka'],
                                name: 'Child_Surname'
                            }, {
                                xtype: 'daterangefield',
                                width: 175,
                                plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
                                fieldLabel: lang['data_rojdeniya_rebenka'],
                                name: 'Child_BirthDate'
                            }, {
                                xtype: 'swpersonsexcombo',
                                width: 175,
                                codeField: 'Sex_id',
                                fieldLabel: lang['pol_rebenka'],
                                //name: 'Child_Sex'
                                hiddenName: 'Sex_id'
                            }]
                    },  {
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-right:5px;',
						//columnWidth: .30,
						labelAlign: 'right',
						labelWidth: 120,
						items: [{
							xtype: 'button',
							text: lang['ustanovit_filtr'],
							tabIndex: 4217,
							minWidth: 125,
							disabled: false,
							topLevel: true,
							allowBlank:true,
							handler: function ()
							{
								this.doSearch();
							}.createDelegate(this)
						}, {
							xtype: 'button',
							text: lang['snyat_filtr'],
							tabIndex: 4218,
							minWidth: 125,
							disabled: false,
							topLevel: true,
							allowBlank:true,
							handler: function ()
							{
								this.doSearch(true);
							}.createDelegate(this)
						}]
					}]
				}]
			}
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', handler: function() {this.openBirthSvidWindow('view');}.createDelegate(this)},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print',
					menuConfig: {
						printObject: {text: lang['pechat_svidetelstva'],handler: function(){this.printBirthSvid();}.createDelegate(this)}
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=MedSvid&m=loadMedSvidBirthListGrid',
			id: 'ZWPW_BirthSvidGrid',
			object: 'BirthSvid',
			/*pageSize: 100,*/
			paging: true,
			region: 'center',
			root: 'data',
			totalProperty: 'totalCount',
            title: lang['medsvidetelstva_o_rojdenii'],
			stringfields: [
				{ name: 'BirthSvid_id', type: 'int', header: 'ID', key: true },
				{ name: 'BirthSvid_isBad', type: 'int', header: lang['isp'], hidden: true },
				{ name: 'BirthSvid_RcpDate', type: 'date', format: 'd.m.Y', header: lang['data_vyidachi'] },
				{ name: 'BirthSvid_Ser', type: 'string', header: lang['seriya'], width:75 },
				{ name: 'BirthSvid_Num', type: 'string', header: lang['nomer'], width:75 },
				{ name: 'Person_FIO', type: 'string', header: lang['fio_materi'], width: 250 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya_materi'], width: 150 },
                { name: 'BirthSvid_BirthChildDate', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya_rebenka'], width: 150 },
                { name: 'BirthSvid_ChildFamil', type: 'string', header: lang['familiya_rebenka'], width: 250 },
                { name: 'Child_Sex', type: 'string', header: lang['pol_rebenka'], width:75 },
				{ name: 'Lpu_Nick', type: 'string', header: lang['mo'], width: 150},
				{ name: 'MedPersonal_FIO', type: 'string', header: lang['fio_vracha'], width: 150}
			]
		});

		this.GridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (row.get('BirthSvid_isBad') == 2)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		sw.Promed.swZagsWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});