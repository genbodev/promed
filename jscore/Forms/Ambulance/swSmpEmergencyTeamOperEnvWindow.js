/**
* swSmpEmergencyTeamOperEnvWindow - Оперативная обстановка по бригадам СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Storozhev
* @version      27.06.2012
*/

sw.Promed.swSmpEmergencyTeamOperEnvWindow = Ext.extend(sw.Promed.BaseForm, {

	modal: true,
	
	width: 800,
	
	height: 500,
	
	regionNumber: null,
	
	onCancel: Ext.emptyFn,
	
	callback: Ext.emptyFn,
	
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		}
	},

	deleteEmergencyTeam: function() {
		var grid = this.GridPanel.getGrid();
		
		var record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('EmergencyTeam_id') ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi_vyizova']);
								} else {
									grid.getStore().remove( record );
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_vyizova_voznikli_oshibki']);
							}
						},
						params: {
							EmergencyTeam_id: record.get('EmergencyTeam_id'),
							Lpu_id: getGlobalOptions().lpu_id
						},
						url: '/?c=EmergencyTeam&m=deleteEmergencyTeam'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_brigadu'],
			title: lang['vopros']
		});
	},
	
	setCenterEmergencyTeamOnMap: function(){
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) {
			console.log( 'Coudn\'t get selected emergency team row.' );
			return false;
		}
		if ( this.regionNumber == 60 ) {
			this.WialonPanel.setCenterUnitByEmergencyTeamId( record.data.EmergencyTeam_id );
		}
	},	

	initComponent: function() {
		
		var region_center = [],
			region_east = [],
			items = [];
		
		var opts = getGlobalOptions();
		this.regionNumber = opts.region.number;
//		this.regionNumber = 60; // For debug only
		if ( this.regionNumber == 60 ) {
			this.WialonPanel = new sw.Promed.WialonPanel({
				city: lang['pskov'],
				title: ''
			});
			region_east.push( this.WialonPanel );
		}
		
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: true,
			autoLoadData: false,
			stringfields: [
				{ header: 'ID', name: 'EmergencyTeam_id', key: true, hidden: true, hideable: false },
				{ header: lang['nomer'], name: 'EmergencyTeam_Num' },
				{ header: lang['starshiy_brigadyi'], name: 'Person_Fin', id: 'Person_Fin', width: 200 },
				{ header: lang['status'], name: 'EmergencyTeamStatus_Name',  renderer: this.changeRenderer, width: 200 }
			],
			dataUrl: '/?c=EmergencyTeam&m=loadEmergencyTeamOperEnv',
			totalProperty: 'totalCount',
			actions: [
				{ name: 'action_view', hidden: true },
				{ name: 'action_print', hidden: true },
				{ name: 'action_add', text: lang['dobavit'], handler: this.openEmergencyTeamEditWindow.createDelegate(this, ['add']) },
				{ name: 'action_edit', text: lang['izmenit'], handler: this.openEmergencyTeamEditWindow.createDelegate(this, ['edit']) },
				{ name: 'action_delete', text: lang['udalit'], handler: this.deleteEmergencyTeam.createDelegate(this) }
			]
		});
		
		region_center.push( this.GridPanel );
		
		items.push({
			region: 'center',
			layout: 'fit', // растягивает на всю высоту и ширину окна
			split: true,
			collapsible: true,
			items: region_center
		});
		
		if ( region_east.length > 0 ) {
			this.width = '90%';
			items.push({
				region: 'east',
				layout: 'fit', // растягивает на всю высоту и ширину окна
				title: lang['karta'],
				split: true,
				collapsible: true,
				width: 800,
				minWidth: 500,
				items: region_east
			});
		}		

		Ext.apply(this, {
			layout: 'border',
			items: items,
			buttonAlign: 'right',
			buttons: [{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(button, event) {
					button.ownerCt.onCancel();
					button.ownerCt.hide();
				}
			}]
		});
		
		var parentObject = this;
				
		this.GridPanel.ViewGridPanel.on('rowclick',function(){
			parentObject.setCenterEmergencyTeamOnMap();
		});
		
		sw.Promed.swSmpEmergencyTeamOperEnvWindow.superclass.initComponent.apply(this, arguments);
	},

	changeRenderer: function(val) {		
        if (val == lang['remont']) {
           return '<span style="color:green;">' + val + '</span>';
        } 
		else if(val == lang['zanyata']) {
           return '<span style="color:red;">' + val + '</span>';
        }
		else if(val == lang['svobodna']) {
           return '<span style="color:blue;">' + val + '</span>';
        }
        return val;
	},
	

	show: function() {
        sw.Promed.swSmpEmergencyTeamOperEnvWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
		
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		this.ARMType = arguments[0].ARMType;
	   
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.GridPanel.addActions({ name: 'action_setdutytime', text: lang['smena'], tooltip: lang['ustanovit_vremya_nachala_i_okonchaniya_smenyi'], handler: this.setEmergencyTeamDutyTime.createDelegate(this) });
		
		this.GridPanel.addActions({ name: 'action_status', text: lang['status'], tooltip: lang['izmenit_status_brigadyi'], handler: this.setEmergencyTeamStatus.createDelegate(this) });
		
		this.setTitle(lang['operativnaya_obstanovka_po_brigadam_smp']);
		
		with(this.GridPanel.ViewGridPanel.getStore()){
			load();
		}
		
		this.center();

	},
	
	openEmergencyTeamEditWindow: function(action){
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}
		
		wnd = 'swSmpEmergencyTeamEditWindow';

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_brigadyi_smp_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var grid = this.GridPanel;
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.SmpEmergencyTeamData ) {
				return false;
			}
			grid.getGrid().getStore().reload();
			this.autoEvent = false;
		}.createDelegate(this);

		if ( action == 'add' ) {
			formParams.EmergencyTeam_id = 0;
		} else {

			if ( !grid.getGrid().getSelectionModel().getSelected() ) {
				return false;
			}
				
			var selected_record = grid.getGrid().getSelectionModel().getSelected();

			if ( !selected_record.get('EmergencyTeam_id') ) {
				return false;
			}

			formParams.EmergencyTeam_id = selected_record.get('EmergencyTeam_id');

			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		formParams.ARMType = this.ARMType;
		params.formParams = formParams;

		getWnd(wnd).show(params);
	},
	
	getSelectedEmergencyTeam: function(){
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if ( !record ) {
			alert(lang['vyi_doljnyi_vyibrat_brigadu']);
			return false;
		}
		return record;
	},
	
	setEmergencyTeamDutyTime: function(){
		var record = this.getSelectedEmergencyTeam();
		if ( !record ) {
			return false;
		}
		
		getWnd('swSmpEmergencyTeamSetDutyTimeWindow').show({
			EmergencyTeam_id: record.get('EmergencyTeam_id'),
			callback: function(data){ /*this.updateEmergencyTeamDutyTime(record,data);*/ }.createDelegate(this)
		});
	},
	
	updateEmergencyTeamDutyTime: function(record,data){
		
		this.getLoadMask(lang['sohranenie']).show();
		
		Ext.Ajax.request({
			params: {
				EmergencyTeam_id: record.get('EmergencyTeam_id'),
				EmergencyTeamDuty_DateStart: data.date_start,
				EmergencyTeamDuty_TimeStart: data.time_start,
				EmergencyTeamDuty_DateFinish: data.date_finish,
				EmergencyTeamDuty_TimeFinish: data.time_finish
			},
			url: '/?c=EmergencyTeam&m=saveEmergencyTeamDutyTime',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {								
					var resp = Ext.util.JSON.decode(r.responseText);
					if( resp.success ) {
						selrecord.set('SendLpu_Nick', lpu_data.Lpu_Nick);
						//selrecord.set('CmpGroup_id', lpu_data.Lpu_id > 0 ? 1 : 2);
						selrecord.commit();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						if( parent_object ) parent_object.setStatusCmpCallCard(2, 1, null,parent_object);
					}
				}
			}.createDelegate(this)
		});
	},
	
	setEmergencyTeamStatus: function(){
		var record = this.getSelectedEmergencyTeam();
		if ( !record ) {
			return false;
		}

		getWnd('swSmpEmergencyTeamSetStatusWindow').show({
			EmergencyTeam_id: record.get('EmergencyTeam_id'),
			callback: function(data){ this.updateEmergencyTeamStatus(record,data); }.createDelegate(this)
		});
	},

	updateEmergencyTeamStatus: function(record,data){
		this.GridPanel.getGrid().getStore().reload();
	}
});