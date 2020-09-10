/**
* swSmpEmergencyTeamOperEnvWithMapWindow - Оперативная обстановка по бригадам СМП
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

sw.Promed.swSmpEmergencyTeamOperEnvWithMapWindow = Ext.extend(sw.Promed.BaseForm, {

	modal: true,
	
//	width: 800,
	height: Ext.getBody().getViewSize().height/100*85,
	width: Ext.getBody().getViewSize().width/100*85,
	autoHeight: true,
	
	onCancel: Ext.emptyFn,
	
	callback: Ext.emptyFn,
	
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		}
	},
	id: 'swSmpEmergencyTeamOperEnvWithMapWindow',
	getBrigadeMarkers: function() {
		return [
			{
				lat: 58.0054001,
				lng: 56.20344469999998,
				marker: {title: lang['brigada_1']},
//				infoWindowOptions: {content: "Краткая информация о бригаде 1"},
				additionalInfo: {
					'EmergencyTeam_id':1,
					'EmergencyTeam_Grid': this.GridPanel
				},
				listeners: {
					'click': function(evt,snthelse1,snthelse2) {
//						console.log({evt:evt});
						console.log(this['EmergencyTeam_Grid']);
						console.log(this['EmergencyTeam_id']);
						this['EmergencyTeam_Grid'].ViewGridPanel.getSelectionModel().selectRow(this['EmergencyTeam_id']-1);
						this['EmergencyTeam_Grid'].ViewGridPanel.getView().focusRow(this['EmergencyTeam_id']-1);
					}
				}
			},{
				lat: 58.0678625,
				lng: 56.355129000000034,
				marker: {title: lang['brigada_2']},
//				infoWindowOptions: {content: "Краткая информация о бригаде 2"},
				additionalInfo:{
					'EmergencyTeam_id':2,
					'EmergencyTeam_Grid': this.GridPanel
				},
				listeners: {
					'click': function(evt,snthelse1,snthelse2) {
						console.log(this['EmergencyTeam_Grid']);
						console.log(this['EmergencyTeam_id']);
						this['EmergencyTeam_Grid'].ViewGridPanel.getSelectionModel().selectRow(this['EmergencyTeam_id']-1);
						this['EmergencyTeam_Grid'].ViewGridPanel.getView().focusRow(this['EmergencyTeam_id']-1);
					}
				}
			},{
				lat: 58.0011972,
				lng: 55.955081000000064,
				marker: {title: lang['brigada_3']},
//				infoWindowOptions: {content: "Краткая информация о бригаде 3"},
				additionalInfo:{
					'EmergencyTeam_id':3,
					'EmergencyTeam_Grid': this.GridPanel
				},
				listeners: {
					'click': function(evt,snthelse1,snthelse2) {
						console.log(this['EmergencyTeam_Grid']);
						console.log(this['EmergencyTeam_id']);
						this['EmergencyTeam_Grid'].ViewGridPanel.getSelectionModel().selectRow(this['EmergencyTeam_id']-1);
						this['EmergencyTeam_Grid'].ViewGridPanel.getView().focusRow(this['EmergencyTeam_id']-1);
					}
				}
			}
		]
	},
	initComponent: function() {
		var stringFields = [
				{ header: 'ID', name: 'EmergencyTeam_id', key: true, hidden: true, hideable: false },
				{ header: lang['nomer'], name: 'EmergencyTeam_Num',width: 50 },
				{ header: lang['starshiy_brigadyi'], name: 'Person_Fin', id: 'Person_Fin', width: 150},
				{ header: lang['profil'], name: 'EmergencyTeamSpec_Code', width: 80},
				{ header: lang['status'], name: 'EmergencyTeamStatus_Name', width: 100 },
				{ header: ' ', name: 'infoIcon', width: 30, sortable: false }
			]
		
		var gridPanelWidth = 5;
		
		for (var i = 0; i<stringFields.length; i++) {
			if (typeof stringFields[i].width != 'undefined') {
				gridPanelWidth += stringFields[i].width;
			}
		}
			
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: true,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			width:gridPanelWidth,
			autoHeight : false,
			height: Ext.getBody().getViewSize().height/100*80,
			layout: 'fit',
			border: false,
			autoLoadData: false,
			stripeRows: true,
			cls: 'additionalGridRowHoverClass',
			
			stringfields: stringFields,
			dataUrl: '/?c=EmergencyTeam&m=loadEmergencyTeamOperEnv',
			totalProperty: 'totalCount',
			actions: [
				{ name: 'action_view', text: lang['prosmotret'], handler: this.openEmergencyTeamEditWindow.createDelegate(this, ['view']) }
			]
		});
		this.GridPanel.ViewGridPanel.getStore().on('load',function(store,records,opts){
			Ext.each(records, function(rec,ind,arr){
				rec.set('infoIcon','<img src="/img/icons/info16.png" onclick="Ext.getCmp(\''+this.id+'\').openEmergencyTeamEditWindow(\'view\');" class=\'additionalGridRowHoverIcon\'>');
				rec.commit();
			}.createDelegate(this));
		}.createDelegate(this));
		
		this.GridPanel.ViewGridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			//Пока нет реальных координат, делаем функцию заглушку c центрированием
			mark = this.mapPanel.findMarkerBy('EmergencyTeam_id',rowIndex%3+1);
			if (typeof mark=='object') {
				this.mapPanel.setCenter([mark.getPosition().lat(),mark.getPosition().lng()]);
			}
		}.createDelegate(this));
		
		var windowWidth = this.width;
		this.mapPanel =  new sw.Promed.GoogleMapPanel({
			heigth:Ext.getBody().getViewSize().width/100*60,
			width:(windowWidth - gridPanelWidth-15),
			gmapType: 'map'  // map, panorama
			, fillLatLng: false
			,addMarkByClick: false
			,mapOptions: {
				zoom: 11,
				scaleControl: true,
				panControl: false,
				zoomControl: true,
				mapTypeControl: false,
				rotateControl: false,
				streetViewControl: false,
				overviewMapControl: true
			}
			,markers: this.getBrigadeMarkers()
			,findMarkerBy: function(key,value) {
				for (var i=0;i<this.googleMarkers.length;i++) {
					if (this.googleMarkers[i][key] == value) {
						return this.googleMarkers[i];
					}
				}
			}
			
		});
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'column',
			autoHeight : false,
			buttons: [
				{ text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(this.ownerCt.title);
					}
				},{
					text: lang['zakryit'],
					iconCls: 'close16',
					handler: function(button, event) {
						button.ownerCt.onCancel();
						button.ownerCt.hide();
					}
				}
			],
			items: [
//				{border: false,
//				layout: 'column',
//				items:[
					this.GridPanel,
					this.mapPanel	
//				]}
				]
		});
		
		sw.Promed.swSmpEmergencyTeamOperEnvWithMapWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
        sw.Promed.swSmpEmergencyTeamOperEnvWithMapWindow.superclass.show.apply(this, arguments);

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
		
		this.setTitle(lang['operativnaya_obstanovka_po_brigadam_smp']);
		
		navigator.geolocation.getCurrentPosition(function (location) {
			this.mapPanel.setCenter(location.coords.latitude, location.coords.longitude);
		}.createDelegate(this));
		
		
		with(this.GridPanel.ViewGridPanel.getStore()){
			load();
		}
		
		for (k in this.GridPanel.ViewActions) {
			if (this.GridPanel.ViewActions.hasOwnProperty(k)) {
				if (k!='action_view') {
					this.GridPanel.setActionHidden(k,true);
					this.GridPanel.setActionDisabled(k,true);
				}
			}
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
	}

});