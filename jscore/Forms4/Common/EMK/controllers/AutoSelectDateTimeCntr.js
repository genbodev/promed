Ext6.define('common.EMK.controllers.AutoSelectDateTimeCntr', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.AutoSelectDateTimeCntr',
	data: {},
	loadUslugaGrid: function (conf) {
		var me = this,
			view = me.getView();
		view.UslugaGridStore.proxy.extraParams.Evn_id = conf.Evn_id;
		view.UslugaGridStore.proxy.extraParams.userLpuSection_id = getGlobalOptions().CurLpuSection_id || sw.Promed.MedStaffFactByUser.last.LpuSection_id || null;
		view.UslugaGridStore.proxy.extraParams.MedPersonal_id = conf.MedPersonal_id;
		view.UslugaGridStore.load();
	},
	getPrescriptionTypeCodeByObject: function(object){
		var prescr_code = null;
		switch (object) {
			case 'EvnCourseProc':
			case 'EvnPrescrProc':
				prescr_code = 6;
				break;
			case 'EvnPrescrOperBlock':
				prescr_code = 7;
				break;
			case 'EvnPrescrLabDiag':
				prescr_code = 11;
				break;
			case 'EvnPrescrFuncDiag':
				prescr_code = 12;
				break;
			case 'EvnPrescrConsUsluga':
				prescr_code = 13;
				break;
		}
		return prescr_code;
	},
	onBtnClick: function () {
		console.log('clicked')
	},
	onHide: function(){
		var cntr = this,
			view = cntr.getView();
		view.callback();
	},
	getCitoClass: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'grid-header-icon-cito';
		} else {
			return 'grid-header-icon-empty';
		}
	},
	getCitoTip: function(v, meta, rec) {
		if (rec.get('EvnPrescr_IsCito') > 1) {
			return 'Cito!';
		} else {
			return '';
		}
	},
	getDirectionClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnPrescrProc':
				if (rec.get('EvnDirection_id')) {
					return 'grid-header-icon-direction';
				} else {
					return 'grid-header-icon-empty';
				}
				break;
			default:
				return 'grid-header-icon-empty';
		}
	},
	getDirectionTip: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnPrescrProc':
				if (rec.get('EvnDirection_id')) {
					return 'Направление';
				} else {
					return '';
				}
				break;
			default:
				return '';
		}
	},
	getOtherMOClass: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnPrescrProc':
				if (rec.get('otherMO')) {
					return 'grid-header-icon-otherMO';
				} else {
					return 'grid-header-icon-empty';
				}
				break;
			default:
				return 'grid-header-icon-empty';
		}
	},
	getOtherMOTip: function(v, meta, rec) {
		switch(rec.get('object')) {
			case 'EvnPrescrFuncDiag':
			case 'EvnPrescrLabDiag':
			case 'EvnPrescrConsUsluga':
			case 'EvnPrescrProc':
				if (rec.get('otherMO')) {
					return 'Место оказания - другая МО';
				} else {
					return '';
				}
				break;
			default:
				return '';
		}
	},
	addCitoInPrescr: function(panel, rowIndex, colIndex, item, e, record){
		panel.mask('Обновление параметра');
		var cito = (record.get('EvnPrescr_IsCito')>1)?1:2,
			id = record.get('EvnPrescr_id');
		if(id){
			Ext.Ajax.request({
				params: {
					EvnPrescr_id: id,
					EvnPrescr_IsCito: cito
				},
				callback: function(options, success, response) {
					panel.unmask();
					if(success){
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							record.set('EvnPrescr_IsCito',cito)
						} else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при обновлении атрибута'));
						}
					}
				},
				url: '/?c=EvnPrescr&m=setCitoEvnPrescr'
			});
		}
		else
			panel.unmask();
	},
	onOtherMOClick: function(panel, rowIndex, colIndex, item, e, record){
	},
	onDirectionClick: function(panel, rowIndex, colIndex, item, e, record){
		var me = this,
			action = 'view',
			cbFn = function(data) {

			};
		if (!record) {
			return false;
		}
		var EvnDirection_id = record.get('EvnDirection_id');
		if (!EvnDirection_id) {
			return false;
		}

		var formParams = {
			EvnDirection_id: EvnDirection_id,
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			Lpu_gid: record.get('Lpu_gid'),
			EvnPrescrMse_id: record.get('EvnPrescrMse_id'),
			DirType_Code: record.get('DirType_Code')
		};

		// если направление на МСЭ, открываем соответсвующую форму
		if (formParams.EvnPrescrMse_id) {
			action = (formParams.Lpu_gid == getGlobalOptions().lpu_id) ? 'edit' : 'view';
			var params = {
				EvnPrescrMse_id: formParams.EvnPrescrMse_id,
				Person_id: formParams.Person_id,
				Server_id: formParams.Server_id,
				onHide: Ext.emptyFn
			};
			getWnd('swDirectionOnMseEditForm').show(params);
			return true;
		}

		var my_params = new Object({
			Person_id: me.data.Person_id,
			EvnDirection_id: formParams.EvnDirection_id,
			callback: cbFn,
			formParams: formParams,
			action: action
		});

		my_params.onHide = Ext.emptyFn;
		getWnd('swEvnDirectionEditWindow'+(record.get('DirType_Code')==9?'Ext6':'') ).show(my_params);
	}
});
