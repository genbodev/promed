/**
* Базовое окно для АРМ СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
*/

sw.Promed.swWorkPlaceSMPDefaultWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceSMPDefaultWindow',
	listeners: {
		hide: function() {
			this.stopTask();
			this.disconnectedByClient = true;
			this.io.arms[this.ARMType] = null;
			delete this.io.arms[this.ARMType];
			var noARMs = true;
			for (var k in this.io.arms) {
				if (this.io.arms.hasOwnProperty(k)) {
					noARMs = false;
				}
			}
			
			if (this.socket && noARMs) {
				log(this.socket);
				this.socket.disconnect();
			}
		}
	},
	
	processNodeJSError: function(data) {
		
		if (!data || !data.e) {
			return false;
		}
		
		sw.swMsg.alert(lang['soobschenie'], data.msg || lang['v_protsesse_vyipolneniya_zaprosa_na_servere_nodejs_proizoshla_oshibka_pojaluysta_obratites_k_administratoru']);
		log({NodeJsError:data.e});
		
		if (this.GridPanel && this.GridPanel.getStore()) {
			this.GridPanel.getStore().reload();
		}
	},
	show: function() {		
		var opts = getGlobalOptions();
		
		if ((typeof io == 'function') && opts.smp && opts.smp.NodeJSSocketConnectHost) {
			this.connectSocket();
		}		

		sw.Promed.swWorkPlaceSMPDefaultWindow.superclass.show.apply(this, arguments);
	},
	socket: null,

	connectSocket: function() {
		var opts = getGlobalOptions();
		var parentObject = this;
		
		if (!opts || !opts.smp || !opts.smp.NodeJSSocketConnectHost) {
			log('No socket conection host')
			return false;
		}
		
		this.socket = io(opts.smp.NodeJSSocketConnectHost);
		
		this.io = io;
		if (!this.io.arms){
			this.io.arms = {};
		} 
		this.io.arms[this.ARMType] = this;

		parentObject.socket.on('connect', function () {
			parentObject.socket.on('authentification', function (callback) {
				callback(document.cookie, opts.pmuser_id, navigator.userAgent);
			});
			parentObject.socket.on('logout', function(){
				location.replace(location.origin+'/?c=main&m=Logout');
			});
			parentObject.socket.on('disconnect', function () {
//				if (parentObject.disconnectedByClient) {
//					return false;
//				}
//				parentObject.startTask();
//				if(!parentObject.socketConnectInterval) {
//					parentObject.socketConnectInterval = setInterval(function() {
//						log({parentObject:parentObject});
//						if (!parentObject.socket.socket.connected) {
//							parentObject.socket.socket.connect();
//						}
//						else {
//							clearInterval(parentObject.socketConnectInterval);
//							parentObject.stopTask();
//						}
//					}, 4500)
//				}
			});
			//заблокировать изменеения
			parentObject.socket.on('lockCmpCallCard', function (data) {
				for (var key in parentObject.io.arms) {
					if (parentObject.io.arms.hasOwnProperty(key)) {
						log(key);
						parentObject.io.arms[key].asyncLockCmpCallCard(data);
					}
				}
			});
			//разблокировать и изменить
			parentObject.socket.on('unlockCmpCallCard', function (data) {
				for (var key in parentObject.io.arms) {
					if (parentObject.io.arms.hasOwnProperty(key)) {
						parentObject.io.arms[key].asyncUnlockCmpCallCard(data);
					}
				}
			});
			parentObject.socket.on('deleteCmpCallCard', function (data) {
				for (var key in parentObject.io.arms) {
					if (parentObject.io.arms.hasOwnProperty(key)) {
						parentObject.io.arms[key].asyncDeleteCmpCallCard(data);
					}
				}
			});
			parentObject.socket.on('addCmpCallCard', function (data) {
				for (var key in parentObject.io.arms) {
					if (parentObject.io.arms.hasOwnProperty(key)) {
						parentObject.io.arms[key].asyncAddCmpCallCard(data);
					}
				}
			});
		});
		
		if ( IS_DEBUG ) {
			this.socket.on('connect_error',function(err){
				parentObject.socket.disconnect();
				log(lang['ne_udalos_podklyuchit_nodejs']);		
			});
		}
	},
	
	emitEditingEvent: function(CmpCallCard_id, callback) {
		// временная мера
		callback();
		return false;

		var parentObject = this;
		if (!this.socket.connected) {
			callback();
			return false;
		}
		
		var lockCmpCallCard_callback = 1;
		
		this.socket.emit('lockCmpCallCard',
		
				CmpCallCard_id,
				
				this.getCallback(
				
					function (callbackData) {

						if (!callbackData.success) {
							sw.swMsg.alert(lang['oshibka'], callbackData.Error_Msg ? callbackData.Error_Msg : lang['oshibka_pri_blokirovanii_kartyi_vyizova']);
							return false;
						}
						callback();
						parentObject.lockInterval = setInterval(function() {
							parentObject.socket.emit('keepLockCmpCallCard',
								CmpCallCard_id,
								parentObject.getCallback(
									function (callbackData) {
										log(callbackData);
									}, 
									this.processNodeJSError
								)
							)
						}, 20000)

					}, 
					
					this.processNodeJSError
					
				)
			);
		return true;
	},
	emitEndEditingEvent: function(CmpCallCard_id){
		// временная мера
		return false;

		if (!this.socket.connected) {
			return false;
		}
		clearInterval(this.lockInterval);
		delete this.lockInterval;
		this.socket.emit('unlockCmpCallCard',
			CmpCallCard_id,
			this.getCallback(
				
				function (callbackData) {
					log(callbackData);
				}, 

				this.processNodeJSError

			)
		);
		return true;
	},
	emitDeletingEvent: function(CmpCallCard_id){
		// временная мера
		return false;

		if (!this.socket.connected) {
			return false;
		}
		clearInterval(this.lockInterval);
		delete this.lockInterval;
		this.socket.emit('deleteCmpCallCard',CmpCallCard_id,this.getCallback(
			function (callbackData) {
				log(callbackData);
			}, 
			this.processNodeJSError
		));
		return true;
	},
	emitAddingEvent: function(CmpCallCard_id) {
		// временная мера
		return false;
		
		if (!this.socket.connected) {
			return false;
		}
		this.socket.emit('addCmpCallCard',CmpCallCard_id, this.processNodeJSError);
	},
	emitEditingEvent: function(CmpCallCard_id) {
		if (!this.socket || !this.socket.connected) {
			return false;
		}
		this.socket.emit('changeCmpCallCard',CmpCallCard_id, 'addCall', function(data){
			console.log('NODE emit addCall : apk='+data);
		});
		// this.socket.on('changeCmpCallCard',function(data, type){
		// 	console.log('NODE ON changeCmpCallCard type='+type);
		// });
	},
	getCallback: function(callback, onError) {
		
		return function(callbackData, error) {
			
			if (error && ( typeof onError === 'function' ) ) {
				return onError(error);
			} 
			
			if ( typeof callback === 'function' ) {
				return callback(callbackData);
			}
			
			return false;
		}
	}
	,initComponent: function() {

		sw.Promed.swWorkPlaceSMPDefaultWindow.superclass.initComponent.apply(this, arguments);
	}
});