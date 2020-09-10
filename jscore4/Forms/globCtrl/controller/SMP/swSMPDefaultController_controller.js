/*
 * Базовый контроллер АРМов СМП
 */

Ext.define('SMP.swSMPDefaultController_controller', {
    extend: 'Ext.app.Controller', 
    
	/**
	 * Проверка правильно созданного подразделения в структуре МО
	 */
    checkHavingLpuBuilding: function( ) {
    	this.getLpuBuildingByCurrentMedService(function(LpuBuilding_id){
			if ( !LpuBuilding_id ) {
				Ext.Msg.alert('Внимание', 'Текущая служба СМП неверно заведена в структуре МО.'
					+ '<br />Дальнейшая работа может быть некорректной.'
					+ '<br />Обратитесь к администратору за помощью.'
				);
			}
		});
    },
	_geoserviceTransportStore: null,
	getGeoserviceTransportStore: function () {
		if (!this._geoserviceTransportStore) {
			this._geoserviceTransportStore = Ext.create('stores.smp.GeoserviceTransportStore');
		}
		return this._geoserviceTransportStore;
	},
	/**
	 * Возвращает LpuBuilding_id в callback функцию
	 */
	getLpuBuildingByCurrentMedService: function (callback) {
		if (!Ext.isFunction(callback)) {
			return;
		}
		
		var CurMedService_id = getGlobalOptions().CurMedService_id;
		if (CurMedService_id) {
			var url = '/?c=CmpCallCard4E&m=getLpuBuildingByCurMedServiceId';
			var params  = {
				CurMedService_id: CurMedService_id
			};
		} else {
			var url = '/?c=CmpCallCard4E&m=getLpuBuildingBySessionData';
			var params = {};
		}
		
		Ext.Ajax.request({
			url: url,
			params: params,
			callback: function(opt, success, response) {
				if (success){
					var res = Ext.JSON.decode(response.responseText, true);
					if (res && res[0] && res[0]['LpuBuilding_id']) {
						callback(res[0]['LpuBuilding_id']);
						return;
					}
				}
				
				Ext.Msg.alert('Ошибка', (res && res[0] && res[0]['Error_Msg'])? res[0]['Error_Msg'] : 'Не удалось определить текущее подразделение.');
				callback(null);
			}
		});
	}
});