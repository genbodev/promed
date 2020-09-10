/**
* swEvnDirectionStacSelectWindow - окно выбора направления.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.002-18.02.2010
*/
/*NO PARSE JSON*/

sw.Promed.swEvnDirectionStacSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPSEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnDirectionStacSelectWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	width: 800,
	height: 600,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnDirectionSelectWindow').hide();
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
	minHeight: 600,
	minWidth: 800,
	title: lang['vyibor_napravleniya'],
	modal: true,
	personId: null,
	plain: true,
	resizable: true,
	id: 'EvnDirectionSelectWindow',
	doSelect: function() {
		var win = this;

		// смотря какой таб открыт такие и действия
		if (this.tabPanel.getActiveTab().id == 'tab_directions') {
			var grid = this.EvnDirectionNotAutoGrid.getGrid();
			var grid2 = this.EvnDirectionIsAutoGrid.getGrid();
			var record;

			if (grid2.getSelectionModel().getSelected() && grid2.getSelectionModel().getSelected().get('EvnDirection_id')) {
				record = grid2.getSelectionModel().getSelected();
			}
			if (grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('EvnDirection_id')) {
				record = grid.getSelectionModel().getSelected();
			}
			if (!record) {
				return false;
			}
			

			var evnDirectionData = record.data;

			win.callback(evnDirectionData);
			win.hide();
				
			
		}
	},
	initComponent: function() {
		var win = this;
		var evndirection_all_cnfg = {
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			focusOn: {
				name: 'EDSW_SelectButton',
				type: 'button'
			},
			focusPrev: {
				name: 'EDSW_CloseButton',
				type: 'button'
			},
			uniqueId: true,
			onDblClick: function() {
				win.doSelect();
			},
			onEnter: function() {
				win.doSelect();
			},
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			},
			paging: false,
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnDirection_id', type: 'int', header: 'ID', key: true },//
				{ name: 'EvnPS_id', type: 'int', hidden: true},//
				{ name: 'EvnQueue_id', type: 'int', hidden: true},//
				{ name: 'LpuSection_did', type: 'int', hidden: true},
				{ name: 'Diag_did', type: 'int', hidden: true },//
				{ name: 'DirType_id', type: 'int', hidden: true },//
				{ name: 'Lpu_did', type: 'int', hidden: true },
				{ name: 'Org_did', type: 'int', hidden: true },
				{ name: 'name', type: 'string',hidden:true},
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 150 },//
				{ name: 'LpuSection_Name', type: 'string', header: lang['profil'], width: 150 },//
				{ name: 'num', type: 'string', header: lang['nomer'], width: 100 },//
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 150 },
				{ name: 'recdate', type: 'date', header: lang['data_zapisi'], width: 110 },//
				{ name: 'EvnDirection_setDate', type: 'date', header: lang['data_napravleniya'], width: 110 },//
			],
			toolbar: true
		};
		
		this.EvnDirectionIsAutoGrid = new sw.Promed.ViewFrame(Ext.apply(evndirection_all_cnfg, {
			id: 'EDSW_EvnDirectionIsAutoGrid',
			region: 'south',
			title: lang['zapisi']
		}));
		this.EvnDirectionNotAutoGrid = new sw.Promed.ViewFrame(Ext.apply(evndirection_all_cnfg, {
			id: 'EDSW_EvnDirectionNotAutoGrid',
			region: 'center',
			title: lang['napravleniya']
		}));

		this.EvnDirectionExtGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnDirectionExt&m=loadList',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			focusOn: {
				name: 'EDSW_SelectButton',
				type: 'button'
			},
			focusPrev: {
				name: 'EDSW_CloseButton',
				type: 'button'
			},
			uniqueId: true,
			onDblClick: function() {
				this.doSelect();
			}.createDelegate(this),
			onEnter: function() {
				this.doSelect();
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			},
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnDirectionExt_id', type: 'int', header: 'ID', key: true },
				{ name: 'Lpu_Nick', type: 'string', header: lang['napravivshaya_mo'], width: 150 },
				{ name: 'Person_SurName', type: 'string', header: lang['familiya'], width: 150, id: 'autoexpand' },
				{ name: 'Person_FirName', type: 'string', header: lang['imya'], width: 150 },
				{ name: 'Person_SecName', type: 'string', header: lang['otchestvo'], width: 150 },
				{ name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 150 },
				{ name: 'Sex_Name', type: 'string', header: lang['pol'], width: 150 },
				{ name: 'Polis_Ser', type: 'string', header: lang['seriya_polisa'], width: 150 },
				{ name: 'Polis_Num', type: 'string', header: lang['nomer_polisa'], width: 150 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 150 },
				{ name: 'EvnDirectionExt_NPRID', type: 'string', header: lang['nomer_napravleniya'], width: 150 },
				{ name: 'PrehospType_Name', type: 'string', header: lang['tip_napravleniya'], width: 150 },
				{ name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 150 },
				{ name: 'EvnDirectionExt_setDT', type: 'date', header: lang['data_napravleniya'], width: 150 },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true }
			],
			toolbar: true
		});

		this.EvnDirectionNotAutoGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var result = '';
				if (row.get('enabled') == 2) {
					result = 'x-grid-panel';
				} else {
					result = 'x-grid-rowgray ';
				}
				return result;
			}
		});
		this.EvnDirectionIsAutoGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var result = '';
				if (row.get('enabled') == 2) {
					result = 'x-grid-panel';
				} else {
					result = 'x-grid-rowgray ';
				}
				return result;
			}
		});

		this.tabPanel = new Ext.TabPanel({
			enableTabScroll: true,
			region: 'center',
			activeTab: 0,
			layoutOnTabChange: true,
			items: [{
				title: lang['napravleniya_i_zapisi'],
				layout: 'border',
				id: 'tab_directions',
				items: [
					win.EvnDirectionNotAutoGrid,
					win.EvnDirectionIsAutoGrid
				]
			}, {
				title: lang['vneshnie_napravleniya'],
				layout: 'fit',
				id: 'tab_extdirections',
				items: [
					win.EvnDirectionExtGrid
				]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'EDSW_SelectButton',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EDSW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				new sw.Promed.PersonInformationPanelShort({
					id: 'EDSW_PersonInformationFrame',
					region: 'north'
				}),
				win.tabPanel
			]
		});
		sw.Promed.swEvnDirectionStacSelectWindow.superclass.initComponent.apply(this, arguments);

		this.EvnDirectionNotAutoGrid.addListenersFocusOnFields();
		this.EvnDirectionIsAutoGrid.addListenersFocusOnFields();
	},
	loadEvnDirectionGrid: function(storeData) {
		this.EvnDirectionNotAutoGrid.getGrid().getStore().removeAll();
		this.EvnDirectionIsAutoGrid.getGrid().getStore().removeAll();
		this.EvnDirectionNotAutoGrid.setActionDisabled('action_refresh', true);
		this.EvnDirectionIsAutoGrid.setActionDisabled('action_refresh', true);
		var i = 0, storeData1 = [], storeData2 = [],
			is_visible_auto_grid = this.useCase.inlist(['create_evnplstom_without_recording','create_evnpl_without_recording']);
		while (i < storeData.length) {
			if (storeData[i].EvnDirection_IsAuto && 2 == storeData[i].EvnDirection_IsAuto) {
				storeData2.push(storeData[i]);
			} else {
				storeData1.push(storeData[i]);
			}
			i++;
		}
		this.EvnDirectionNotAutoGrid.getGrid().getStore().loadData(storeData1);
		// Форма выбора направлений показывается с двумя гридами только при вызове из главного журнала АРМ врача поликлиники/стоматолога
		this.EvnDirectionIsAutoGrid.setVisible(is_visible_auto_grid);
		if (is_visible_auto_grid) {
			this.EvnDirectionIsAutoGrid.getGrid().getStore().loadData(storeData2);
		}
	},
	show: function() {
		sw.Promed.swEvnDirectionStacSelectWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.restore();
		this.center();

		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onDate = arguments[0].onDate || null;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.personId = arguments[0].Person_id || null;
		this.MedStaffFactId = arguments[0].MedStaffFact_id || null;
		this.LpuSectionId = arguments[0].LpuSection_id || null;
		this.parentClass = arguments[0].parentClass || null;
		this.formType = arguments[0].formType || null;
		this.DirType_id = arguments[0].DirType_id || null;
		this.storeData = arguments[0].storeData || null;
		this.useCase = arguments[0].useCase || '';

		this.findById('EDSW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		win.loadEvnDirectionGrid([]);
		if ( this.storeData ) {
			win.loadEvnDirectionGrid(win.storeData);
		} else if ( this.personId ) {
			var loadMask = new Ext.LoadMask(win.getEl(), {
				msg: "Получение списка направлений и записей..."
			});
			loadMask.show();
			Ext.Ajax.request({
				params: {
					 onDate: (typeof this.onDate == 'object' ? Ext.util.Format.date(this.onDate, 'd.m.Y') : this.onDate)
					,Person_id: this.personId
					,MedStaffFact_id:this.MedStaffFactId
					,LpuSection_id:this.LpuSectionId
					,formType:this.formType
					,parentClass: this.parentClass
					,DirType_id: this.DirType_id
					,useCase:this.useCase
				},
				callback: function(options, success, response) {
					loadMask.hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							win.storeData = response_obj;
							win.loadEvnDirectionGrid(win.storeData);
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poluchenii_spiska_napravleniy_i_zapisey']);
					}
				},
				url: '/?c=EvnDirection&m=loadEvnDirectionList'
			});
		}
		win.tabPanel.setActiveTab(1);
		win.tabPanel.setActiveTab(0);
		win.tabPanel.hideTabStripItem('tab_extdirections');
		if ((this.parentClass == 'EvnPS' || this.formType == 'stac') && getRegionNick() == 'astra') {
			// показываем вкладку внешние направления.
			win.tabPanel.unhideTabStripItem('tab_extdirections');

			this.EvnDirectionExtGrid.loadData({
				globalFilters: {
					notIdentOnly: 1,
					start: 0,
					limit: 100,
					onDate: (typeof this.onDate == 'object' ? Ext.util.Format.date(this.onDate, 'd.m.Y') : this.onDate)
				}
			})
		}
	}
});
