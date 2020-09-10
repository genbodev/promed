/**
* МСЭ - форма добавления/редактирования ЛВН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      01.08.2011
*/

sw.Promed.swEvnMseStickEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	resizable: false,
	height: 175,
	width: 460,
	onHide: Ext.emptyFn,
	shim: false,
	buttonAlign: "right",
	objectName: 'swEvnMseStickEditWindow',
	closeAction: 'hide',
	id: 'swEvnMseStickEditWindow',
	objectSrc: '/jscore/Forms/Mse/swEvnMseStickEditWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.save();
			},
			iconCls: 'save16',
			tooltip: lang['sohranit'],
			text: lang['sohranit']
		},
		'-',
		{
			text: lang['otmena'],
			tabIndex: -1,
			tooltip: lang['otmena'],
			iconCls: 'cancel16',
			handler: function()
			{
				this.ownerCt.hide();
			}
		}
	],
	listeners: {
		hide: function(w){
			w.disableFields(false);
			w.Frm.getForm().reset();
			w.buttons[0].setVisible(true);
		}
	},
	show: function()
	{
		sw.Promed.swEvnMseStickEditWindow.superclass.show.apply(this, arguments);
		
		if(!arguments[0]){
			this.hide();
			return false;
		}
		
		if(arguments[0].action)
			this.action = arguments[0].action;
		else {
			this.hide();
			return false;
		}
		
		if(arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		
		if(arguments[0].callback) {
			this.callback = arguments[0].callback;
			this.obj_call = arguments[0].owner;
			this.onHide = function(){
				this.callback(this.obj_call, 0);
			}
		}
		
		var base_form = this.Frm.getForm();
		if(arguments[0].owner){
			var data = arguments[0].owner.ViewGridPanel.getSelectionModel().getSelected().data;
			data.EvnMseStick_begDT = Ext.util.Format.date(data.EvnStick_setDate, 'd.m.Y');
			data.EvnMseStick_endDT = Ext.util.Format.date(data.EvnStick_disDate, 'd.m.Y');
		}
		
		switch(this.action)
		{
			case 'add':
				this.mode = 'ins';
				this.setTitle(lang['vremennaya_netrudosposobnost_vvod']);
				base_form.findField('Person_id').setValue(arguments[0].Person_id);
				base_form.findField('EvnStickClass').setValue('EvnMseStick');
			break;
			
			case 'edit':
			case 'view':
				this.mode = 'upd';
				base_form.setValues(data);
				var diag_id = base_form.findField('Diag_id').getValue();
				if ( diag_id != null && diag_id.toString().length > 0 ) {
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(record) {
								if ( record.get('Diag_id') == diag_id ) {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}
				
				if(this.action == 'edit'){
					this.setTitle(lang['vremennaya_netrudosposobnost_redaktirovanie']);
				} else if(this.action == 'view') {
					this.buttons[0].setVisible(false);
					this.setTitle(lang['vremennaya_netrudosposobnost_prosmotr']);
					this.disableFields(true);
				}
				
			break;
		}
		this.doLayout();
		this.center();
	},
	save: function()
	{
		var win = this;
		var frm = win.Frm.getForm();

		if (!frm.isValid()) {

			sw.swMsg.alert(
				lang['oshibka'],
				lang['zapolnenyi_ne_vse_obyazatelnyie_polya_obyazatelnyie_k_zapolneniyu_polya_vyidelenyi_osobo']
			);

			return false;
		}

		var begStickDate = new Date(frm.findField('EvnMseStick_begDT').getValue()),
			endStickDate = new Date(frm.findField('EvnMseStick_endDT').getValue()),
			dt = new Date();

		// вычитаем из текущей даты 365 дней + 1 доп. день
		dt.setDate(dt.getDate() - 366);
		var diffDate = new Date(dt);
		
		// проверка: дата начала не может быть больше текущей
		if (begStickDate > new Date()) {

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				fn: function() {
					frm.findField('EvnMseStick_begDT').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: langs('Дата начала не может быть больше текущей даты'),
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		// проверка: дата начала должна быть больше (текущая дата - 1 год)
		if (begStickDate < diffDate) {

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				fn: function() {
					frm.findField('EvnMseStick_begDT').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['neobhodimo_ukazat_period_vremennoj_netrudosposobnosti_tolko_za_poslednie_12_mesjacev'],
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		// проверка: дата окончания должна быть больше даты начала
		if (endStickDate < begStickDate ) {

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				fn: function() {
					frm.findField('EvnMseStick_begDT').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		win.getLoadMask(lang['sohranenie_dannyih']).show();
		frm.submit({

			params: {action: win.mode},

			success: function(){

				win.getLoadMask().hide();
				win.hide();
				win.onHide();
			},

			failure: function(){

				win.getLoadMask().hide();

				sw.swMsg.alert(
					lang['oshibka'],
					lang['pri_sohranenii_dannyih_proizoshla_oshibka']
				);
			}
		});
	},
	
	disableFields: function(o)
	{
		this.Frm.findBy(function(field){
			if(field.xtype && field.xtype != 'hidden'){
				if(o) field.disable();
				else field.enable();
			}
		});
	},
	
	initComponent: function()
	{
		var cur_win = this;
	
		this.Frm = new Ext.form.FormPanel({
			border: false,
			url: '/?c=Mse&m=saveEvnStick',
			labelAlign: 'right',
			bodyStyle: 'padding: 5px;',
			items: [
				{
					layout: 'form',
					border: false,
					width: 420,
					items: [
						{
							xtype: 'hidden',
							name: 'EvnStick_id'
						}, {
							xtype: 'hidden',
							name: 'Person_id'
						}, {
							xtype: 'hidden',
							name: 'EvnStickClass'
						}, {
							layout: 'column',
							border: false,
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'form',
									columnWidth: .48,
									labelWidth: 80,
									items: [
										{
											xtype: 'swdatefield',
											allowBlank: false,
											name: 'EvnMseStick_begDT',
											fieldLabel: lang['data_nachala']
										}
									]
								}, {
									layout: 'form',
									columnWidth: .52,
									labelWidth: 120,
									items: [
										{
											xtype: 'swdatefield',
											allowBlank: false,
											anchor: '100%',
											name: 'EvnMseStick_endDT',
											fieldLabel: lang['data_okonchaniya']
										}
									]
								}
							]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 80,
							items: [
								{
									xtype: 'swdiagcombo',
									anchor: '100%',
									allowBlank: false,
									fieldLabel: lang['diagnoz']
								},
								{
									xtype: 'swyesnocombo',
									anchor: '100%',
									hiddenName: 'EvnMseStick_IsStick',
									fieldLabel: langs('ЭЛН')
								},
								{
									xtype: 'numberfield',
									anchor: '100%',
									allowDecimals: false,
									allowNegative: false,
									name: 'EvnMseStick_StickNum',
									fieldLabel: langs('Номер ЛВН')
								}
							]
						}
					]
				}
			]
		});
	
		Ext.apply(this,
		{
			layout: 'fit',
			items: [this.Frm]
		});
		sw.Promed.swEvnMseStickEditWindow.superclass.initComponent.apply(this, arguments);
	}
});