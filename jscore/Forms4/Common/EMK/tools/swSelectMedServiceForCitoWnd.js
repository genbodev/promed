/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Sobenin Alexander aka GTP_fox
* @version      12.09.2019
*/

/**
 * swSelectMedServiceForCitoWnd - окно с выбором службы для экстренной бирки
 *
 * @class sw.Promed.swSelectMedServiceForCitoWnd
 * @extends sw.Promed.BaseForm
 */
Ext6.define('common.EMK.tools.swSelectMedServiceForCitoWnd', {
	extend: 'base.BaseForm',
	autoHeight: true,
	border: false,
	closable: true,
	closeAction:'hide',
	modal: true,
	plain: false,
	resizable: false,
	title: langs('Выбор службы для экстренной записи'),
	winTitle: langs('Выбор службы для экстренной записи'),
	width: 500,
	listeners: {
		'hide': function(win) {
			if (win._isCancel) win.onHideFunc();
		}
	},
	show: function(data) {
		this.callParent(arguments);
		this.setTitle(data.winTitle || this.winTitle);

		this.formType = data.formType || 'polka';
		this._isCancel = true;
		// Функция вызывающаяся после выбора причины установки статуса
		this.callback = (typeof data.callback == 'function') ? arguments[0].callback : Ext6.emptyFn;
		// Функция вызывающаяся при отмене выбора причины установки статуса
		this.onHideFunc = (typeof arguments[0].onHide == 'function') ? arguments[0].onHide : Ext6.emptyFn;



		var me = this;


		me.data = data;
		me.PrescriptionType_Code = null;
		me.MedServiceType_SysNick = null;
		switch(me.data.objectPrescribe) {
			case 'EvnCourseProc':
				me.PrescriptionType_Code = 6;
				me.MedServiceType_SysNick = 'prock';
				break;
			case 'EvnPrescrLabDiag':
				me.PrescriptionType_Code = 11;
				me.MedServiceType_SysNick = 'lab';
				break;
			case 'EvnPrescrFuncDiag':
				me.PrescriptionType_Code = 12;
				break;
			case 'EvnPrescrConsUsluga':
				me.PrescriptionType_Code = 13;
				break;
			case 'EvnPrescrOperBlock':
				me.PrescriptionType_Code = 7;
				me.MedServiceType_SysNick = 'oper';
				break;
		}
		me.Resource_id = null;
		me.MedService_id = null;
		me.Lpu_id = getGlobalOptions().lpu_id; // по умолчанию своя МО
		if (me.data && me.data.record){
			var rec = me.data.record;
			if(rec.get('MedService_id'))
				me.MedService_id = rec.get('MedService_id');
			if(rec.get('Lpu_id'))
				me.Lpu_id = rec.get('Lpu_id');
			// Если служба бирки отличается от службы записи - подставляем службу бирки
			if(rec.get('ttms_MedService_id') && me.MedService_id != rec.get('ttms_MedService_id')){
				if(rec.get('ttms_MedService_id') == rec.get('pzm_MedService_id') && rec.get('pzm_Lpu_id')){
					me.Lpu_id = rec.get('pzm_Lpu_id');
					me.MedService_id = rec.get('ttms_MedService_id');
				}
			}

			if(rec.get('Resource_id'))
				me.Resource_id = rec.get('Resource_id');
			if(rec.get('UslugaComplex_id'))
				me.UslugaComplex_id = rec.get('UslugaComplex_id');
			if(rec.get('PrescriptionType_Code') && !me.PrescriptionType_Code)
				me.PrescriptionType_Code = rec.get('PrescriptionType_Code');
		}

		me.withResource = false;
		if (me.PrescriptionType_Code == 12) {
			me.withResource = true;
		}

		if (me.withResource) {
			me.ResourceFilterCombo.show();
		} else {
			me.ResourceFilterCombo.hide();
		}


		me.LpuFilterCombo.setValue(me.Lpu_id);

		me.MedServiceFilterCombo.clearValue();
		me.ResourceFilterCombo.clearValue();

		me.MedServiceFilterCombo.getStore().proxy.extraParams.filterByUslugaComplex_id = me.UslugaComplex_id;
		me.MedServiceFilterCombo.getStore().proxy.extraParams.userLpuSection_id = me.data.userMedStaffFact.LpuSection_id;
		me.MedServiceFilterCombo.getStore().proxy.extraParams.PrescriptionType_Code = me.PrescriptionType_Code;
		me.ResourceFilterCombo.getStore().proxy.extraParams.UslugaComplex_id = me.UslugaComplex_id;

		me.loadMedService();
	},
	loadMedService: function() {
		var me = this;
		// загружаем список служб, которые могут выполнять данную услугу, выбираем первую службу, если нам не передали конкретную
		me.mask(LOAD_WAIT);
		me.MedServiceFilterCombo.getStore().proxy.extraParams.filterByLpu_id = me.LpuFilterCombo.getValue();
		me.MedServiceFilterCombo.getStore().load({
			callback: function() {
				me.unmask();
				var record;
				if(me.MedService_id){
					record = me.MedServiceFilterCombo.getStore().findRecord('MedService_id', me.MedService_id);
					if(!record)
						record = me.MedServiceFilterCombo.getStore().findRecord('pzm_MedService_id', me.MedService_id);
				}
				if(!record)
					record = me.MedServiceFilterCombo.getFirstRecord();

				if (record)
					me.MedServiceFilterCombo.setValue(record.get('UslugaComplexMedService_key'));

				if (me.withResource && !Ext6.isEmpty(me.MedServiceFilterCombo.getValue())) {
					me.mask(LOAD_WAIT);
					me.ResourceFilterCombo.getStore().load({
						callback: function() {
							me.unmask();

							// берем первый ресурс в списке или данные из записи, с которой открыли форму
							var Resource_id = (me.Resource_id)?me.Resource_id:me.ResourceFilterCombo.getFirstRecord();
							if (Resource_id) {
								me.ResourceFilterCombo.setValue(Resource_id);
							}

						}
					});
				}
			}
		});
	},
	onSprLoad: function(args) {
	},
	save: function() {
		var base_form = this.FormPanel.getForm();
		if (!base_form.isValid()) {
			Ext6.Msg.alert(langs('Ошибка заполнения формы'), langs('Проверьте правильность заполнения полей формы.'));
			return false;
		}
		var params = {
			MedService_id: base_form.findField('MedService_id').getValue(),
			Resource_id: base_form.findField('Resource_id').getValue(),
			Lpu_id: base_form.findField('Lpu_id').getValue()
		};
		var MSData = base_form.findField('MedService_id').getSelectedRecord().getData();
		params = Ext6.apply(params,MSData);
		this.hide();
		this.callback(params);
		this._isCancel = false;

		return true;
	},
	initComponent: function() {
		var me = this;

		me.LpuFilterCombo = Ext6.create('swLpuCombo', {
			width: '100%',
			fieldLabel: '',
			listeners: {
				'change': function(LpuCombo, newValue, oldValue) {
					var MSCombo = me.MedServiceFilterCombo,
						MSComboStore = me.MedServiceFilterCombo.getStore();
					MSCombo.lastQuery = 'This query sample that is not will never appear';
					if (newValue > 0) {
						MSComboStore.proxy.extraParams.Lpu_id = newValue;
						MSComboStore.proxy.extraParams.filterByLpu_id = newValue;
						MSComboStore.proxy.extraParams.Lpu_isAll = 0;
						if(me.MedServiceType_SysNick)
							MSComboStore.proxy.extraParams.MedServiceType_SysNick = me.MedServiceType_SysNick;
					} else {
						MSComboStore.proxy.extraParams.Lpu_id = null;
						MSComboStore.proxy.extraParams.filterByLpu_id = null;
						MSComboStore.proxy.extraParams.Lpu_isAll = 1;
					}
					if (!MSCombo.getValue()
						|| (MSCombo.getFieldValue('Lpu_id') && MSCombo.getFieldValue('Lpu_id') != newValue)) {

						MSComboStore.removeAll();
						MSComboStore.load({callback: function(records,e,success){
							if(records.length && records[0])
								MSCombo.select(records[0]);
							else
								MSCombo.clearValue();
						}});
					}
				}
			},
			listConfig:{
				minWidth: 500
			},
			name: 'Lpu_id'
		});

		me.MedServiceFilterCombo = Ext6.create('swMedServicePrescrCombo', {
			fieldLabel: '',
			width: '100%',
			name: 'MedService_id',
			allowBlank: false,
			listeners: {
				'change': function (combo, newValue, oldValue) {
					var Lpu_id,
						resCombo = me.ResourceFilterCombo;
					if (combo.getValue() && combo.getSelectedRecord()) {
						Lpu_id = combo.getSelectedRecord().get('Lpu_id');
						if (Lpu_id)
							me.LpuFilterCombo.setValue(Lpu_id);
					}
					if (!me.LpuFilterCombo.getValue()) {
						Lpu_id = combo.getFieldValue('Lpu_id');
						if (Lpu_id)
							me.LpuFilterCombo.setValue(Lpu_id);
					}
					resCombo.getStore().proxy.extraParams.MedService_id = combo.getFieldValue('MedService_id');
					resCombo.lastQuery = 'This query sample that is not will never appear';

					if (!resCombo.getValue()
						|| (resCombo.getFieldValue('MedService_id') && resCombo.getFieldValue('MedService_id') != newValue)) {
						resCombo.clearValue();
						resCombo.getStore().removeAll();
						resCombo.getStore().load({
							callback: function (records, e, success) {
								if (records.length && records[0])
									resCombo.select(records[0]);
							}
						});
					}
				}
			}
		});

		me.ResourceFilterCombo = Ext6.create('swResourceCombo', {
			fieldLabel: '',
			width: '100%',
			name: 'Resource_id',
			xtype: 'combo'
		});

		this.FormPanel = Ext6.create('Ext6.form.FormPanel', {
			autoHeight: true,
			border: false,
			labelAlign: 'top',
			bodyPadding: 30,
			layout: 'vbox',
			items : [
				me.LpuFilterCombo,
				me.MedServiceFilterCombo,
				me.ResourceFilterCombo,
				{
					xtype: 'component',
					margin: '10 0 0 0',
					html: 'Будет создана <b>дополнительная</b> запись на '+(new Date().dateFormat('d.m.Y H:i'))
				}
			]
		});

		
		Ext6.apply(this, {
			buttonAlign: "right",
			buttons: ['->', {
				handler: function () {
					me.hide();
				},
				text: 'Отмена',
				cls: 'buttonCancel'
			}, {
				handler: function () {
					me.save();
				},
				cls: 'buttonAccept',
				text: 'Применить'
			}],
			items : [
				me.FormPanel
			]
		});

		this.callParent(arguments);
	}
});