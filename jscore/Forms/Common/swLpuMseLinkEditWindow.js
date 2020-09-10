/**
 * swLpuMseLinkEditWindow - окно редактирования связи бюро МСЭ с МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.2017
 */
/*NO PARSE JSON*/

sw.Promed.swLpuMseLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLpuMseLinkEditWindow',
	maximizable: false,
	maximized: false,
	resizable: false,
	layout: 'form',
	autoHeight: true,
	width: 460,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext.isEmpty(base_form.findField('LpuMseLink_endDate').getValue()) &&
			base_form.findField('LpuMseLink_begDate').getValue() > base_form.findField('LpuMseLink_endDate').getValue()
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('LpuMseLink_endDate').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Дата закрытия не может быть меньше даты отрытия',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};

		if (base_form.findField('Lpu_bid').disabled) {
			params.Lpu_bid = base_form.findField('Lpu_bid').getValue();
		}
		if (base_form.findField('MedService_id').disabled) {
			params.MedService_id = base_form.findField('MedService_id').getValue();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			success: function(result_form, action) {
				loadMask.hide();
				base_form.findField('LpuMseLink_id').setValue(action.result.LpuMseLink_id);

				this.callback();
				this.hide();
			}.createDelegate(this),
			failure: function(result_form, action) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swLpuMseLinkEditWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.ARMType = null;

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		var lpuOid = base_form.findField('Lpu_oid');
		var lpuBid = base_form.findField('Lpu_bid');
		var lpuMedService = base_form.findField('MedService_id');
		var lpuID = getGlobalOptions().lpu_id;

		lpuOid.setBaseFilter(function(rec) {
			return rec.get('Lpu_IsMse') == 1;
		});
		lpuBid.setBaseFilter(function(rec) {
			return rec.get('Lpu_IsMse') == 2;
		});

		lpuMedService.getStore().load();

		if (!arguments[0] || !arguments[0].action || !arguments[0].formParams) {
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		this.action = arguments[0].action;

		base_form.setValues(arguments[0].formParams);

		base_form.items.each(function(f){f.validate()});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				loadMask.hide();
				this.setTitle('Связь МО с бюро МСЭ: Добавление');
				this.enableEdit(true);

				var ownLpuIndex = lpuBid.getStore().find('Lpu_id', lpuID);
				var ownLpu = lpuBid.getStore().getAt(ownLpuIndex);

				if (this.ARMType != 'superadmin') {
					if(ownLpu){
						lpuBid.setValue(ownLpu?ownLpu.id:null);
						lpuMedService.setBaseFilter(function(rec) {
							return rec.get('Lpu_id') == ownLpu.id;
						});
					}
					lpuBid.disable();
					lpuMedService.disable();
				}
				break;

			case 'edit':
			case 'view':
				if (this.action=='edit') {
					this.setTitle('Связь МО с бюро МСЭ: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Связь МО с бюро МСЭ: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=LpuStructure&m=loadLpuMseLinkForm',
					params: {LpuMseLink_id: base_form.findField('LpuMseLink_id').getValue()},
					success: function () {
						loadMask.hide();
						if (this.ARMType != 'superadmin') {
							base_form.findField('Lpu_bid').disable();
							base_form.findField('MedService_id').disable();
						}
						var Lpu_bid = base_form.findField('Lpu_bid').getValue();
						var n=0;
						base_form.findField('MedService_id').setBaseFilter(function(rec){
							n++;
							console.warn('Lpu_bid = '+Lpu_bid);
							return rec.get('Lpu_id') == Lpu_bid;
						});
					}.createDelegate(this),
					failure: function (form,action) {
						loadMask.hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'LMLEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			url: '/?c=LpuStructure&m=saveLpuMseLink',
			items: [{
				xtype: 'hidden',
				name: 'LpuMseLink_id'
			}, {
				id: 'LMLEW_Lpu_bid',
				allowBlank: false,
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_bid',
				fieldLabel: 'МО МСЭ',
				width: 300,
				listeners: {
					select: function(elem,res){
						var lpuMedService = Ext.getCmp('LMLEW_MedService_id');
						var MSLpu_id = lpuMedService.getFieldValue('Lpu_id');
						if(MSLpu_id != res.id) lpuMedService.clearValue();
						lpuMedService.setBaseFilter(function(rec) {
							return rec.get('Lpu_id') == res.id;
						});
					}
				}
			}, {
				id: 'LMLEW_MedService_id',
				allowBlank: false,
				xtype: 'swmedservicecombo',
				hiddenName: 'MedService_id',
				fieldLabel: 'Бюро МСЭ',
				width: 300,
				anyMatch: true,
				params:{
					MedServiceType_id: 2, // с типом «5.Медико-социальная экспертиза»,
					//Lpu_id: getGlobalOptions().lpu_id
				},
				listeners: {
					beforequery: function(combo, record, index ){
						var lpuBid = Ext.getCmp('LMLEW_Lpu_bid');
						if(!lpuBid.getValue()) return false;
					},
				}
			},
			{
				allowBlank: false,
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_oid',
				fieldLabel: 'МО',
				width: 300
			}, {
				allowBlank: false,
				xtype: 'swdatefield',
				name: 'LpuMseLink_begDate',
				fieldLabel: 'Дата открытия',
				width: 120
			}, {
				xtype: 'swdatefield',
				name: 'LpuMseLink_endDate',
				fieldLabel: 'Дата закрытия',
				width: 120
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'LpuMseLink_id'},
				{name: 'Lpu_bid'},
				{name: 'Lpu_oid'},
				{name: 'MedService_id'},
				{name: 'LpuMseLink_begDate'},
				{name: 'LpuMseLink_endDate'}
			])
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'LMLEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swLpuMseLinkEditWindow.superclass.initComponent.apply(this, arguments);
	}
});