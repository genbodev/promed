/**
* swSelectEmergencyTeamWindow - форма выбора бригады
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Miyusov Alexandr
* @version      17.10.2012
*/

sw.Promed.swSelectEmergencyTeamWindow = Ext.extend(sw.Promed.BaseForm, {
	
	modal: true,
	width: 700,
	height: 500,
	autoHeight: true,
	resizable: false,
	plain: false,
	closable: false,
	title: lang['vyibor_brigadyi_dlya_peredachi_vyizova'],
	callback: Ext.emptyFn,
	onDoCancel: Ext.emptyFn,
	loadJsGoogleMaps: false,
	
	// Данные адреса вызова
	address: null,
	
	// ID талона вызова
	CmpCallCard_id: null,
	
	// Номер региона
	regionNumber: null,
	
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		},
		
		destroy: function(){
		}
	},
	
	getAddressTitle: function(){
		if ( this.address === null ) {
			return '';
		}
		
		if ( this.address.ULat && this.address.ULng ) {
			return this.address.UName;
		}
		
		var address = '';
		if ( this.address.Rgn_Name.length ){
			address += this.address.Rgn_Name;
		}
		// skiped KLSubRgn_FullName - нужен нет? хз
		if ( this.address.City_Name.length ){
			if ( address.length ) address += ', ';
			address += lang['g']+this.address.City_Name;
		}
		if ( this.address.Street_Name.length ){
			if ( address.length ) address += ', ';
			address += lang['ul']+this.address.Street_Name;
		}
		if ( this.address.House_Name.length ){
			if ( address.length ) address += ', ';
			address += lang['d']+this.address.House_Name;
		}
		
		return address;
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
	
	
	selectEmergencyTeam: function() {
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		this.callback(record.data);
		this.hide();
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
	
	directionFromEmergencyTeamToEmergencyAddress: function(){
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) {
			console.log( 'Coudn\'t get selected emergency team row.' );
			return false;
		}
		var EmergencyTeam_id = record.data.EmergencyTeam_id;
		if ( this.regionNumber == 60 ) {
			
			if ( this.WialonPanel.markerEmergencyCall === null ) {
				Ext.Msg.alert(lang['soobschenie'], lang['na_karte_ne_ustanovlen_marker_mesta_vyizova_marshrut_ne_mojet_byit_prolojen']);
				console.log(lang['neustanovlen_marker_this_wialonpanel_markeremergencycall_marshrut_do_mesta_vyizova_ne_mojet_byit_prolojen']);
				return false;
			}
			
			if ( typeof this.WialonPanel.cacheUnitsByEmergencyTeamId[EmergencyTeam_id] == 'undefined' ) {
				var wialon = this.WialonPanel;
				// Basic request
				Ext.Ajax.request({
					url: '?c=Wialon&m=getUnitIdByEmergencyTeamId',
					success: function(xmlhttp){
						var data = Ext.util.JSON.decode( xmlhttp.responseText );
						wialon.cacheUnitsByEmergencyTeamId[EmergencyTeam_id] = data[0];
						var start = new google.maps.LatLng( wialon.markerEmergencyCall.position.lat(), wialon.markerEmergencyCall.position.lng() );
						var unit_id = wialon.cacheUnitsByEmergencyTeamId[EmergencyTeam_id];
						var unit;
						for( var key in wialon.units  ) {
							var unit = wialon.units[ key ];
							if ( unit.id == unit_id ) {
								if ( typeof unit.pos != 'undefined' ) {
									var end = new google.maps.LatLng( unit.marker.position.lat(), unit.marker.position.lng() );
								}
								break;
							}
						}
						wialon.calcRoutBetweenTwoMarkers( start, end );
					},
					failure: function(){
						console.log('Failed to get wialon unit id');
					},
					params: {
						EmergencyTeam_id: EmergencyTeam_id
					}
				});
			} else {
				var start = new google.maps.LatLng( this.WialonPanel.markerEmergencyCall.position.lat(), this.WialonPanel.markerEmergencyCall.position.lng() );
				var unit_id = this.WialonPanel.cacheUnitsByEmergencyTeamId[EmergencyTeam_id];
				var unit;
				for( var key in this.WialonPanel.units  ) {
					var unit = this.WialonPanel.units[ key ];
					if ( unit.id == unit_id ) {
						if ( typeof unit.pos != 'undefined' ) {
							var end = new google.maps.LatLng( unit.marker.position.lat(), unit.marker.position.lng() );
						}
						break;
					}
				}
				this.WialonPanel.calcRoutBetweenTwoMarkers( start, end );
			}
		}
	},
	
	onCancel: function() {
		this.onDoCancel();
		this.hide();
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
	
	loadScriptGoogleMap: function( src, callback ) {
		if(this.loadJsGoogleMaps) callback.bind(this);

		var src = src || 'https://maps.google.com/maps/api/js?libraries=geometry&sensor=false&language=ru';
		var script = document.createElement('script');
		var appendTo = document.getElementsByTagName('head')[0];
		if ( typeof callback == 'function' ) {
			script.onload = callback.bind(this);
		}
		script.onerror = function(){
			this.loadJsGoogleMaps = false;
			console.log('ошибка при загрузке c maps.google.com');
		}
		script.src = src;
		appendTo.appendChild( script );
	},
	
	initComponent: function() {
		var opts = getGlobalOptions();
		this.regionNumber = opts.region.number;
		var items = [];
		var parentObject = this;

		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			onEnter: this.selectEmergencyTeam.createDelegate(this),
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ header: 'ID', name: 'EmergencyTeam_id', key: true, hidden: true, hideable: false },
				{ header: langs('Номер'), name: 'EmergencyTeam_Num', width:50 },
				{ header: langs('Старший бригады'), name: 'Person_Fin', id: 'Person_Fin', width:120 },
				{ header: langs('Профиль'), name: 'EmergencyTeamSpec_Name', width:240},
				{ header: langs('Статус'), name: 'EmergencyTeamStatus_Name', renderer: this.changeRenderer, width:150 },
				{ header: langs('Сетевой статус'),name: 'EmergencyTeam_isOnline', width:100}
			],
			dataUrl: '/?c=EmergencyTeam&m=loadEmergencyTeamOperEnv',
			totalProperty: 'totalCount'
		});	

		if ( this.regionNumber == 60 ) {
//		this.regionNumber = 60; // For debug only
			var src='https://maps.google.com/maps/api/js?libraries=geometry&sensor=false&language=ru';
			this.loadScriptGoogleMap(src, function(){
				//т.к. теперь скрипт с google загружаем по необходимости, то создаем WialonPanel только после загрузки скрипта
				this.loadJsGoogleMaps = true;
			this.WialonPanel = new sw.Promed.WialonPanel({
				city: lang['pskov'],
				width: '100%',
				height: 400,
				listeners: {
					afterInit: function(obj,map){
					}
				}
			});
				this.add(this.WialonPanel);
				this.add(this.GridPanel);
				this.doLayout();
				this.render();
				this.center();
				console.log(this);
			});			
		}else{
			items.push( this.GridPanel );
			console.log(this);
		}
		/*
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			onEnter: this.selectEmergencyTeam.createDelegate(this),
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ header: 'ID', name: 'EmergencyTeam_id', key: true, hidden: true, hideable: false },
				{ header: lang['nomer'], name: 'EmergencyTeam_Num', width:50 },
				{ header: lang['starshiy_brigadyi'], name: 'Person_Fin', id: 'Person_Fin', width:120 },
				{ header: lang['profil'], name: 'EmergencyTeamSpec_Name', width:240},
				{ header: lang['status'], name: 'EmergencyTeamStatus_Name', renderer: this.changeRenderer, width:150 },
				{ header: lang['setevoy_status'],name: 'EmergencyTeam_isOnline', width:100}
			],
			dataUrl: '/?c=EmergencyTeam&m=loadEmergencyTeamOperEnv',
			totalProperty: 'totalCount'
		});
		
		items.push( this.GridPanel );
		*/
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.selectEmergencyTeam.createDelegate(this)
			}, {
				text: lang['informatsiya_o_brigade'],
				handler: this.openEmergencyTeamEditWindow.createDelegate(this,['view'])
			}, 
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: this.onCancel.createDelegate(this)
			}],
			items: [{
				layout: 'column',
				autoHeight: true,
				items: items
			}]
		});
		
		this.GridPanel.ViewGridPanel.on('rowdblclick',function(){
			parentObject.selectEmergencyTeam();
		});
		
		this.GridPanel.ViewGridPanel.on('rowclick',function(){
			parentObject.directionFromEmergencyTeamToEmergencyAddress();
		})
		
		sw.Promed.swSelectEmergencyTeamWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function() {
        sw.Promed.swSelectEmergencyTeamWindow.superclass.show.apply(this, arguments);
		
	    this.setTitle(lang['vyibor_brigadyi_vyizova']);
		
		this.doLayout();
		this.restore();
		this.center();
		
		var parentObj = this;		
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].onDoCancel && getPrimType(arguments[0].onDoCancel) == 'function' ) {
			this.onDoCancel = arguments[0].onDoCancel;
		}
		
		if ( arguments[0].CmpCallCard_id ) {
			this.CmpCallCard_id = arguments[0].CmpCallCard_id;
			// Псков. Отображаем адрес на карте.
			// @TODO Вынести в отдельный метод
			if ( this.regionNumber == 60 ) {
				var loadMask = this.getLoadMask(LOAD_WAIT).show();
				Ext.Ajax.request({
					url: '?c=CmpCallCard&m=getCmpCallCardAddress',
					success: function(response,params){
						parentObj.getLoadMask().hide();
						var data = Ext.util.JSON.decode(response.responseText);
						if ( data.Error_Msg ){
							sw.swMsg.alert(lang['oshibka'],data.Error_Msg );
							return;
						}
						parentObj.address = data[0];
						var setCenterMap = function(location,title){
							var map = parentObj.WialonPanel.getMap();
							map.setCenter(location);
							parentObj.WialonPanel.markerEmergencyCall = new google.maps.Marker({
								map: map,
								position: location,
								title: title
							});
						}
						if ( parentObj.address.ULat && parentObj.address.ULng ) {
							setCenterMap(new google.maps.LatLng( parentObj.address.ULat, parentObj.address.ULng ),parentObj.getAddressTitle());
						} else {
							parentObj.WialonPanel.codeAddress(parentObj.getAddressTitle(),function(results,status){
								setCenterMap(results[0].geometry.location,parentObj.getAddressTitle());
							});
						}
					},
					failure: function(){
						parentObj.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_polucheniya_adresa_vyizova_voznikla_nepredvidennaya_oshibka_esli_oshibka_povtoritsya_obratites_k_administratoru']);
					},
					params: { CmpCallCard_id: this.CmpCallCard_id }
				});
			}
		}

		this.GridPanel.ViewGridPanel.getStore().load({
			params: {
				closeHide: 1,
				CmpCallCard: arguments[0].CmpCallCard,
				teamTime: arguments[0].AcceptTime

			},
			callback: function(a,b,c){
				if(a.length == 0) sw.swMsg.alert(lang['soobschenie'], lang['net_dostupnyih_brigad_dla_vibora']);
			}
		});
	}
	
});





