/**
* swEvnRPStreamInputWindow - окно потокового отоваривания рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      22.01.2010
* @comment      Префикс для id компонентов ERPSIF (EvnRPStreamInputForm)
*/

sw.Promed.swEvnRPStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnRP: function() {
		/*var grid = this.findById('ERPSIF_EvnPLGrid');

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_talon_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var evn_pl_id = selected_record.get('EvnPL_id');

		if ( evn_pl_id == null ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							Evn_id: evn_pl_id
						},
						success: function(response, options) {
							grid.getGrid().getStore().remove(selected_record);

							if (grid.getGrid().getStore().getCount() == 0) {
								grid.addEmptyRecord(grid.getGrid().getStore());
							}

							grid.focus();
						},
						url: '/?c=Evn&m=deleteEvn'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_talon'],
			title: lang['vopros']
		});
		*/
	},
	draggable: true,
	height: 550,
	id: 'EvnRPStreamInputWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'ERPSIF_CancelButton',
/*
				onShiftTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
*/
				onTabAction: function () {
					this.findById('ERPSIF_EvnRecept_Ser').focus(true, 100);
				}.createDelegate(this),
				tabIndex: TABINDEX_ERPSIF + 2,
				text: lang['zakryit']
			}],
			items: [{
				autoHeight: true,
				layout: 'form',
				region: 'north',
				items: [ new Ext.form.FormPanel({
					bodyStyle: 'padding: 5px',
					border: false,
					frame: false,
					id: 'ERPSIF_StreamInformationForm',
					items: [{
						disabled: true,
						fieldLabel: lang['polzovatel'],
						id: 'ERPSIF_pmUser_Name',
						width: 380,
						xtype: 'textfield'
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'ERPSIF_Stream_begDateTime',
						width: 130,
						xtype: 'textfield'
					}],
					labelAlign: 'right',
					labelWidth: 120
				}),
				new Ext.form.FormPanel({
					animCollapse: false,
					autoHeight: true,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					buttonAlign: 'left',
					// collapsible: true,
					frame: false,
					id: 'EvnRPStreamInputParams',
					items: [{
						enableKeyEvents: true,
						fieldLabel: lang['seriya'],
						id: 'ERPSIF_EvnRecept_Ser',
						listeners: {
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.ENTER ) {
									e.stopEvent();
									this.openEvnRPEditWindow('add');
								}
								if ( e.getKey() == Ext.EventObject.INSERT ) {
									e.stopEvent();
									this.openEvnRPEditWindow('add');
								}
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						name: 'EvnRecept_Ser',
						tabIndex: TABINDEX_ERPSIF + 1,
						xtype: 'textfield'
					}],
					labelAlign: 'right',
					labelWidth: 120,
					title: lang['parametryi_vvoda']
				})]
			},
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { this.openEvnRPEditWindow('add'); }.createDelegate(this) },
					{ name: 'action_edit', disabled: true/*, handler: function() { this.openEvnRPEditWindow('edit'); }.createDelegate(this)*/ },
					{ name: 'action_view', disabled: true/*, handler: function() { this.openEvnRPEditWindow('view'); }.createDelegate(this)*/ },
					{ name: 'action_delete', disabled: true/*, handler: function() { this.deleteEvnRP(); }.createDelegate(this)*/ },
					{ name: 'action_refresh', disabled: true/*, handler: function() { this.refreshEvnRPGrid(); }.createDelegate(this)*/},
					{ name: 'action_print'/*, handler: function() { this.printEvnRP(); }.createDelegate(this)*/ }
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				dataUrl: '/?c=Farmacy&m=loadEvnRPStreamList',
				focusOn: {
					name: 'ERPSIF_CancelButton',
					type: 'button'
				},
				focusPrev: {
					name: 'ERPSIF_EvnRecept_Ser',
					type: 'field'
				},
				id: 'ERPSIF_EvnRPGrid',
				pageSize: 100,
				paging: false,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'EvnRecept_id', type: 'int', header: 'ID', key: true },
					{ name: 'EvnRecept_Ser', type: 'string', header: lang['seriya'], width: 170 },
					{ name: 'EvnRecept_Num', type: 'string', header: lang['nomer'], width: 170 },
					{ name: 'EvnRecept_Sum', type: 'money', align: 'right', header: lang['summa_rozn_s_nds'], width: 170 },
					{ name: 'EvnRecept_SumDiscount', type: 'money', align: 'right', header: lang['summa_rozn_s_nds_s_uchetom_skidki'], width: 200 },
					{ name: 'DelayType_Name', type: 'string', header: lang['tip_otovarivaniya'], id: 'autoexpand', autoExpandMin: 170 }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})]
		});
		sw.Promed.swEvnRPStreamInputWindow.superclass.initComponent.apply(this, arguments);
		this.findById('ERPSIF_EvnRPGrid').addListenersFocusOnFields();
	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('EvnRPStreamInputWindow').openEvnRPEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnRPStreamInputWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	openEvnRPEditWindow: function(action) {
	
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swEvnReceptProcessWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_obrabotki_retseptov_uje_otkryito']);
			return false;
		}

		var grid = this.findById('ERPSIF_EvnRPGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok_retseptov']);
			return false;
		}

		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}

			var record = grid.getStore().getById(data.EvnRecept_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnRecept_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ 'data': [ data ]}, true);
			}
			else {
				var evn_rp_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					evn_rp_fields.push(key);
				});

				for ( i = 0; i < evn_rp_fields.length; i++ ) {
					record.set(evn_rp_fields[i], data[evn_rp_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.EvnRecept_Ser = this.findById('ERPSIF_EvnRecept_Ser').getValue();
			params.onHide = function() {
				current_window.findById('ERPSIF_EvnRecept_Ser').focus(true, 100);
			};
			getWnd('swEvnReceptProcessWindow').show(params);
		}		
	},
	plain: true,
	pmUser_Name: null,
	printEvnRP: function() {
	/*
		var grid = this.findById('EPLSIF_EvnPLGrid').ViewGridPanel;

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var evn_pl_id = grid.getSelectionModel().getSelected().get('EvnPL_id');

		if ( evn_pl_id ) {
			window.open('/?c=EvnPL&m=printEvnPL&EvnPL_id=' + evn_pl_id, '_blank');
		}
	*/
	},
	refreshEvnRPGrid: function() {
		var grid = this.findById('ERPSIF_EvnRPGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	setBegDateTime: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					this.begDate = response_obj.begDate;
					this.begTime = response_obj.begTime;

					this.findById('ERPSIF_StreamInformationForm').findById('ERPSIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					this.findById('ERPSIF_StreamInformationForm').findById('ERPSIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					this.findById('ERPSIF_EvnRPGrid').getGrid().getStore().baseParams.begDate = response_obj.begDate;
					this.findById('ERPSIF_EvnRPGrid').getGrid().getStore().baseParams.begTime = response_obj.begTime;
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	show: function() {
		sw.Promed.swEvnRPStreamInputWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.begDate = null;
		this.begTime = null;
		this.pmUser_Name = null;

		var form = this.findById('EvnRPStreamInputParams');
		form.getForm().reset();		

		// Заполнение полей "Пользователь" и "Дата начала ввода"
		this.setBegDateTime();

		this.findById('ERPSIF_EvnRPGrid').getGrid().getStore().removeAll();
		this.findById('ERPSIF_EvnRPGrid').addEmptyRecord(this.findById('ERPSIF_EvnRPGrid').getGrid().getStore());
		//LoadEmptyRow(this.findById('ERPSIF_EvnRPGrid').getGrid(), 'data');
		// this.findById('EPLSIF_EvnPLGrid').getGrid().getStore().baseParams.limit = 100;
		// this.findById('EPLSIF_EvnPLGrid').getGrid().getStore().baseParams.start = 0;
		//var grid = this.findById('ERPSIF_EvnRPGrid').getGrid();
		//grid.getView().focusRow(0);
		//grid.getSelectionModel().selectFirstRow();		
		this.findById('ERPSIF_EvnRecept_Ser').focus(true, 100);
	},
	title: lang['forma_potokovoy_obrabotki_retseptov'],
	width: 800
});