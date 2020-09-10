var log = function() {
	for (var i=0; i<arguments.length; i++) {
		console.log(arguments[i]);
	}
	console.log();
};

var retFunc = function(params) {
	var socket = params['socket'],
		smpSockets = params['smpSockets'],
		post = params['post'],
		io = params['io'],
		apk = params['apk'];
		tnc = params['tnc'];
		CmpCallCardArr = [];
		//var smpCardsIntervalDS, smpCardsArrayDS, emergencyTeamIntervalDS, emergencyTeamArrayDS;
	
	var _processTNCData = function(data) {
		
		if (!data || !data.length) {
			log({ERROR_processTNCData_data: data});
			return [];
		}

		var result = [];
		
		for (var i=0 ; i<data.length ; i++) {
			result.push({
				'GeoserviceTransport_name' : data[i]['transport_name'] || '',
				'GeoserviceTransport_id' : data[i]['transport_id'] || '',
				'lat' : data[i]['lat'] || '',
				'lng' : data[i]['lon'] || '',
				'direction' : data[i]['direction'] || ''
			})
		}
		
		return result;
		
	};
	
	var smpEvents = {
		
		
		setEmergencyTeamStatusBeginHospitalized: function(params,callback){

			if (!smpSockets[socket.id]) {
				return false;
			}
			
			try {
				result = apk.APKEmergencyTeamStatusBeginHospitalized(params);
				if( typeof callback == 'function' ) {
					callback(result);
				}
				//smpEvents.setUpdateEmergencyTeamsARMCenterDisaster(true);
			} catch(e) {
				if( typeof callback == 'function' ) { callback(e); }
				log({'setEmergencyTeamStatusBeginHospitalized':e});
			}
			/*
			//если уфа или крым то emit в рамках подчиненных подстанций
			if(socket.session.regionNick && (socket.session.regionNick == 'ufa' || socket.session.regionNick == 'krym' || socket.session.regionNick == 'perm') ){
				io.sockets.in('OperDepartament_'+socket.session.OperDepartament).emit('setEmergencyTeamStatusBeginHospitalized',params);
			}
			else{
				//если нет или крым то emit в рамках текущей лпу
				io.sockets.in(response.Lpu_id).emit('setEmergencyTeamStatusBeginHospitalized',params);
			}
			*/		
		},
		/*	изменение карты вызова
		 * typeAction
		 * - addCall - добавление карты вызова
		 * - boostCall - ускорение карты вызова
		 * - feelBadlyCall - чувствует плохо
		 */ 
		changeCmpCallCard: function(CmpCallCardId,typeAction,callback){

			if (!smpSockets[socket.id] || !CmpCallCardId) {
				return false;
			}
			
			post.PostRequest({'CmpCallCard_id':CmpCallCardId},socket.session.cookies,socket.session.userAgent,'/?c=CmpCallCard4E&m=getCmpCallCardSmpInfo', function(data){
				try {
					var result = false;
					if (data){
						try {
							var response = JSON.parse(data);
							result = apk.APKChangeCmpCallCard(response);
						} catch(e) {
							log({'APKEmitChangeCmpCallCard_error':e});
						}

						//если уфа или крым то emit в рамках подчиненных подстанций
						if(socket.session.regionNick && (socket.session.regionNick == 'ufa' || socket.session.regionNick == 'krym' || socket.session.regionNick == 'perm') ){
							io.sockets.in('OperDepartament_'+socket.session.OperDepartament).emit('changeCmpCallCard', data, typeAction?typeAction:null);
						}
						else{
							//если нет или крым то emit в рамках текущей лпу
							io.sockets.in(response.Lpu_id).emit('changeCmpCallCard', data, typeAction?typeAction:null);
						}
					}
					if( typeof callback == 'function' ) callback(result);
				} catch (e) {
					log({'onСhangeCmpCallCardError': e, data: data});
				}
				
			});

		},
		/* изменение бригады
		 * typeAction
		 * - changeStatus - изменение статуса бригады
		 * - deleteTimeInterval - удаление временного интервала у бригады 
		 */
		changeEmergencyTeamStatus: function(reqData,typeAction,callback){
			if (!smpSockets[socket.id]) {
				return false;
			}
			CmpCallCardArr = (reqData['CmpCallCardArr'] !== undefined) ? reqData['CmpCallCardArr'] : ''; //массив назначенных вызовов

			post.PostRequest(
				reqData,
				socket.session.cookies,
				socket.session.userAgent,'/?c=EmergencyTeam4E&m=loadEmergencyTeam', 
				function(data){				
					if (data){
						try {
							var response = JSON.parse(data);
							var result = false;
							//если уфа или крым то emit в рамках подчиненных подстанций
							if(socket.session.regionNick && (socket.session.regionNick == 'ufa' || socket.session.regionNick == 'krym'  || socket.session.regionNick == 'perm') ){
								io.sockets.in('OperDepartament_'+socket.session.OperDepartament).emit('changeEmergencyTeamStatus', data, typeAction?typeAction:null);
								/*
								// отправим сообщение всем (в рамках подчиненных подстанций), кроме себя
								socket.broadcast.in('OperDepartament_'+socket.session.OperDepartament).emit('changeEmergencyTeamStatus', data, typeAction?typeAction:null);
								*/
							}
							else{
								//если нет или крым то emit в рамках текущей лпу
								io.sockets.in(response.Lpu_id).emit('changeEmergencyTeamStatus', data, typeAction?typeAction:null);
							}						
							try {
								var json = JSON.parse(data);
								json[0]['CmpCallCardArr'] = CmpCallCardArr;
								result = ( apk.APKSendStatus( json[0] ) ) ? 1 : 0;
							} catch(e) {
								log({'changeEmergencyTeamStatus_error':e});
							}
							
							if( typeof callback == 'function' ) callback(result);
						} catch (e) {
							log({'changeEmergencyTeamStatus': e, data: data});
						}
					}
				}
			);
		},
		
		//назначение бригады на вызов
		setEmergencyTeamToCall: function(data,callback){
			if (!smpSockets[socket.id]) {
				return false;
			}
			var result = false;
			try {
				result = apk.APKSendCmpCallCard(data);
				//smpEvents.setUpdateEmergencyTeamsARMCenterDisaster(true);
			} catch(e) {
				log({'setEmergencyTeamToCall_error':e});
			}
			if( typeof callback == 'function' ) callback(result);
		},
		
		
		/* добавление времени для бригад(ы)	
		 * typeAction
		 * - addTimeInterval - добавление времени
		 */
		addTimeEmergencyTeams: function(EmergencyTeam_data,typeAction,callback){
			if (!smpSockets[socket.id]) {
				return false;
			}
			if (EmergencyTeam_data){
				//smpEvents.setUpdateEmergencyTeamsARMCenterDisaster(true);
				io.sockets.in('authentificatedUsers').emit('addTimeEmergencyTeams', EmergencyTeam_data, typeAction?typeAction:null);
			}
			if( typeof callback == 'function' ) callback();
		},		
		
		/* выход бригады на смену
		 */
		setEmergencyTeamDutyTime: function(EmergencyTeams_data,callback){
			if (!smpSockets[socket.id]) {
				return false;
			}
			//если уфа или крым то emit в рамках подчиненных подстанций
			if(socket.session.regionNick && (socket.session.regionNick == 'ufa' || socket.session.regionNick == 'krym' || socket.session.regionNick == 'perm') ){
				io.sockets.in('OperDepartament_'+socket.session.OperDepartament).emit('setEmergencyTeamDutyTime', EmergencyTeams_data);
			}
			else{
				//если нет или крым то emit в рамках текущей лпу
				io.sockets.in(response.Lpu_id).emit('setEmergencyTeamDutyTime', EmergencyTeams_data);
			}
			if( typeof callback == 'function' ) callback();
		},
		/* 
		 * Получение информации от бригадах в Wialon
		 */
		getAllWialonCars: function(callback){
			if (!smpSockets[socket.id]) {
				return false;
			}

			post.PostRequest(
				{},
				socket.session.cookies,
				socket.session.userAgent,'/?c=Wialon&m=getAllAvlUnitsWithCoords', 
				function(data){
					if (data){
						callback(data);
					}
				}
			);
		},



		cancelCmpCard: function( data, callback ) {
			if (!smpSockets[socket.id]) {
				return false;
			}
			var result = false;
			try {
				result = apk.APKCancelCmpCallCard(data);
			} catch(e) {
				log({'setEmergencyTeamToCall_error':e});
			}
			if( typeof callback == 'function' ) callback(result);
		},

	/*
		getAllDispatchStationCmpCalls: function(callback){
			if (!smpSockets[socket.id]) {
				return false;
			};
			
			if(smpCardsIntervalDS&&smpCardsArrayDS){
				callback(smpCardsArrayDS);
				return true;
			}
			
			smpCardsIntervalDS = setInterval(function(){
				
				post.PostRequest(
					{},
					socket.session.cookies,
					socket.session.userAgent,'/?c=CmpCallCard4E&m=loadSMPDispatchStationWorkPlace', 
					function(data){
						if (data){
							smpCardsArrayDS = data;
							//callback(data);
						}
					}
				);
				
			}, 15000);
			
		},
		
		
		getAllDispatchStationCmpTeams: function(callback){

			if (!smpSockets[socket.id]) {
				return false;
			};
			
			if(
				( typeof emergencyTeamIntervalDS != 'undefined' )
				&& ( typeof emergencyTeamArrayDS != 'undefined' )
			)
			{
				callback(emergencyTeamArrayDS);
				return true;
			}
			
			emergencyTeamIntervalDS = setInterval(function(){
				
				post.PostRequest(
					{},
					socket.session.cookies,
					socket.session.userAgent,
					'/?c=EmergencyTeam4E&m=loadEmergencyTeamOperEnvForSmpUnit', 
					function(data){
						if (data){
							emergencyTeamArrayDS = data;
							//callback(data);
						}
					}
				);
				
			}, 15000);
			
		},
		*/
		
		/*
		// < арм ЦМК
		
		//получение бригад для ЦМК
		//здесь мы запускаем обновление по таймауту
		//и эмит события при апдейте
		//также вызывая функцию с параметром doRequestNow - обновляем данные вне очереди сторонним запросом
		//tables: EmergencyTeam4E CmpCallCards
		setUpdateEmergencyTeamsARMCenterDisaster: function(doRequestNow){
			
			if (!smpSockets[socket.id]) { return false;	};
			
			var doRequest = function(foreignEmit){
				post.PostRequest(
					{},
					socket.session.cookies,
					socket.session.userAgent, 
					'/?c=EmergencyTeam4E&m=loadEmergencyTeamsARMCenterDisaster',
					function(data){
						if (data){
							io.sockets.in('authentificatedUsers').emit('changeEmergencyTeamsARMCenterDisaster', data, {'foreignEmit':foreignEmit?true:false});
						}
					}
				);
			}
			
			if(doRequestNow){ doRequest(doRequestNow);}
			
			if(	typeof cmkDataEmergencyTeamsInterval == 'undefined' )
			{
				doRequest();
				cmkDataEmergencyTeamsInterval = setInterval(function(){
					doRequest();
				}, 15000);
			}
			
		},
		
		//получение данных о количестве подстанций и все что в них
		//tables: EmergencyTeam4E CmpCallCards
		setUpdateLpuBuildingsARMCenterDisaster: function(doRequestNow){
			
			if (!smpSockets[socket.id]) { return false;	};
			
			var doRequest = function(foreignEmit){
				post.PostRequest(
					{},
					socket.session.cookies,
					socket.session.userAgent, 
					'/?c=EmergencyTeam4E&m=getCountsTeamsCallsAndDocsARMCenterDisaster',
					function(data){
						if (data){
							io.sockets.in('authentificatedUsers').emit('changeLpuBuildingsARMCenterDisaster', data, {'foreignEmit':foreignEmit?true:false});
						}
					}
				);
			}
			
			if(doRequestNow){ doRequest(doRequestNow);}
			
			if(	typeof cmkLpuBuildingsTeamsInterval == 'undefined' )
			{
				doRequest();
				cmkLpuBuildingsInterval = setInterval(function(){
					doRequest();
				}, 20000);
			}
			
		},
		
		//получение данных о вызовах
		//tables: EmergencyTeam4E CmpCallCards
		setUpdateCallsARMCenterDisaster: function(doRequestNow){
			
			if (!smpSockets[socket.id]) { return false;	};
			
			var doRequest = function(foreignEmit){
				post.PostRequest(
					{},
					socket.session.cookies,
					socket.session.userAgent, 
					'/?c=EmergencyTeam4E&m=loadCmpCallCardsARMCenterDisaster',
					function(data){
						if (data){
							io.sockets.in('authentificatedUsers').emit('changeCmpCallCardsARMCenterDisaster', data, {'foreignEmit':foreignEmit?true:false});
						}
					}
				);
			}
			
			if(doRequestNow){ doRequest(doRequestNow);}
			
			if(	typeof cmkCmpCallCardsTeamsInterval == 'undefined' )
			{
				doRequest();
				cmkCmpCallCardsTeamsInterval = setInterval(function(){
					doRequest();
				}, 15000);
			}
			
		},
		*/
		//получение данных о трекерах
		//tables: EmergencyTeam4E CmpCallCards
		setUpdateGeoserviceTransportList: function(doRequestNow, callback){
			if (!smpSockets[socket.id]) { 	
				if( typeof callback == 'function' ) {
					callback({statement: 'NODE: socket not authenticated'});
				}
				return false;	
			};
			
			var doRequest = function(foreignEmit){
				post.PostRequest(
					{},
					socket.session.cookies,
					socket.session.userAgent, 
					'/?c=GeoserviceTransport&m=getGeoserviceTransportListWithCoords',
					function(data){
						try {
							var response = JSON.parse(data);
							if (data){
									//io.sockets.in('authentificatedUsers').emit('changeGeoserviceTransportList', data, {'foreignEmit':foreignEmit?true:false});
									//если уфа или крым то emit в рамках подчиненных подстанций
									if(socket.session.regionNick && (socket.session.regionNick == 'ufa' || socket.session.regionNick == 'krym' || socket.session.regionNick == 'perm') ){
										io.sockets.in('OperDepartament_'+socket.session.OperDepartament).emit('changeGeoserviceTransportList', data, {'foreignEmit':foreignEmit?true:false});
									}
									else{
										//если нет или крым то emit в рамках текущей лпу
										io.sockets.in(response.Lpu_id).emit('changeGeoserviceTransportList', data, {'foreignEmit':foreignEmit?true:false});
									}
								}
							//var arrEmergencyID = apk.APKemergencyTeamOnline();
							var arrEmergencyID = JSON.stringify( apk.APKemergencyTeamOnline() );
							if(socket.session.regionNick && (socket.session.regionNick == 'ufa' || socket.session.regionNick == 'krym' || socket.session.regionNick == 'perm') ){
								io.sockets.in('OperDepartament_'+socket.session.OperDepartament).emit('changeEmergencyTeamOnline', arrEmergencyID );
							}
							else{
								io.sockets.in(response.Lpu_id).emit('changeEmergencyTeamOnline', arrEmergencyID );
							}
						} catch (e) {
							log({'setUpdateGeoserviceTransportList': e, data: data});
						}
						
					}
				);
			}
			
			if(doRequestNow){ doRequest(doRequestNow);}
			
			if(	typeof cmkGeoserviceTransportListInterval == 'undefined' )
			{
				doRequest();
				if( typeof callback == 'function' ) callback({statement: 'NODE: starting automatic data downloading'});
				cmkGeoserviceTransportListInterval = setInterval(function(){
					doRequest();
				}, 15000);
			}else{
				if( typeof callback == 'function' ) callback({statement: 'NODE: automatic data download is started'});
			}
			
		},
		
		// АРМ ЦМК >
		
		/*
		 * Поулчение списка ТС для сервиса Уфы ТНЦ
		 */
		getTNCTransport: function(callback) {
			
			// Если запущен интервал для получения последних данных, берём последний полученный результат из кэша , 
			// если нет - получаем данные и запускаем интервал.
			
			if (!tnc) {
				return;
			}
			
			var data = [];
			
			
			if (tnc.getGetTransportInterval()) {
				
				callback ( _processTNCData( tnc.getLastCachedDataFromCache() ) );
				
			} else {
				
				tnc.getLastCachedDataFromService( function( data ) {
					callback( _processTNCData(data) );
				} );
				
				tnc.startGetTransportInterval( function(data) {
					//io.sockets.in('authentificatedUsers').emit('TNCTransportUpdata', _processTNCData(data) );
					
					//если уфа или крым то emit в рамках подчиненных подстанций
					if(socket.session.regionNick && (socket.session.regionNick == 'ufa' || socket.session.regionNick == 'krym' || socket.session.regionNick == 'perm') ){
						io.sockets.in('OperDepartament_'+socket.session.OperDepartament).emit('TNCTransportUpdata', _processTNCData(data) );
					}
					else{
						//если нет или крым то emit в рамках текущей лпу
						io.sockets.in(response.Lpu_id).emit('TNCTransportUpdata', _processTNCData(data) );
					}
				});
			}
			
			
		},

		// оформление отказа
		registrationFailure: function (data, callback){
			var result = false;
			try {
				result = apk.APKregistrationFailure(data);
				// io.sockets.in('authentificatedUsers').emit('registrationFailure', data);
			} catch(e) {
				log({'registrationFailure_error ':e});
				// io.sockets.in('authentificatedUsers').emit('registrationFailure', 'registrationFailure ERROR');
			}	
			if( typeof callback == 'function' ) callback(result);		
		},
		
		// событие обновления информации карты вызова
		updatedInformationOnCall: function (data, callback){
			var result = false;
			try {
				result = apk.APKupdatedInformationOnCall(data);
				// io.sockets.in('authentificatedUsers').emit('updatedInformationOnCall', data);
			} catch(e) {
				log({'UpdatedInformationOnCall_error ':e});
			}		
			if( typeof callback == 'function' ) callback(result);		
		},

		// Ухудшение состояния, карта вызова
		isDeterior: function (data, callback){
			var result = false;
			try {
				result = apk.APKisDeterior(data);
				// io.sockets.in('authentificatedUsers').emit('isDeterior', data);
			} catch(e) {
				log({'isDeterior_error ':e});
				// io.sockets.in('authentificatedUsers').emit('isDeterior', {'ERROR': e});
			}	
			if( typeof callback == 'function' ) callback(result);			
		},

		// событие НМП  "Вызов исполнен"
		eventCallCompleted: function(data, callback){
			var result = false;
			try {
				result = apk.APKEventCallCompleted(data);
			} catch(e) {
				log({'eventCallCompleted':e});
			}
			if( typeof callback == 'function' ) callback(result);
		}
		
	}
	return smpEvents;
}

exports.getSmpEventsObject = retFunc;
		
