/**
 * Кастомное окно открытия формы "Все услуги", чтобы не плодить файлы для ext2
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 *
 */
Ext6.define('common.EMK.tools.swOpenWindowAllUslugaExt2', {
	extend: 'base.BaseForm',
	requires: [
		'common.EMK.controllers.EvnPrescribePanelCntr',
		'common.EMK.models.EvnPrescribePanelModel',
		'common.EMK.QuickPrescrSelect.swDrugQuickSelectWindow',
		'common.EMK.QuickPrescrSelect.swUslugaQuickSelectWindow',
	],
	title: '',
	noTaskBarButton: true,
	refId: 'swOpenWindowAllUslugaExt2',
	alias: 'widget.swOpenWindowAllUslugaExt2',
	controller: 'EvnPrescribePanelCntr',
	style: {
		display: 'none'
	},
	show: function () {
		this.callParent(arguments);
		if (!arguments || !arguments[0] || !arguments[0].Evn_id) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		let me = this;
		let ctrl = me.getController();

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		} else {
			me.callback = Ext6.emptyFn;
		}

		let conf = {
			objectPrescribe: 'EvnPrescrLabDiag',
			cbFn: me.callback
		};

		ctrl.loadData({
			isKVS: true,
			userMedStaffFact: arguments[0].userMedStaffFact,
			Person_id: arguments[0].Person_id,
			Server_id: arguments[0].Server_id,
			PersonEvn_id: arguments[0].PersonEvn_id,
			Evn_id: arguments[0].Evn_id,
			Evn_setDate: arguments[0].Evn_setDate,
			LpuSection_id: arguments[0].LpuSection_id,
			MedPersonal_id: arguments[0].MedPersonal_id
		}, arguments[0].evnParams);
		ctrl.openAllUslugaInputWnd(conf);
	},
	initComponent: function () {
		let me = this;

		this.DrugSelectPanel = Ext6.create('common.EMK.QuickPrescrSelect.swDrugQuickSelectWindow', {
			parentPanel: me,
			reference: 'DrugSelectPanel',
			onSelect: function(drug) {
			}
		});
		this.UslugaSelectPanel = Ext6.create('common.EMK.QuickPrescrSelect.swUslugaQuickSelectWindow', {
			parentPanel: me,
			reference: 'UslugaSelectPanel',
			onSelect: function(params){
				if(params.PacketPrescr_id)
					cntr.addPrescrToPacket(params,this);
				else
					cntr.saveEvnPrescr(params,this);
			}
		});
		this.callParent(arguments);
	}
});
