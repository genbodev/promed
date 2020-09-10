/**
* МСЭ - форма Мероприятия по медицинской реабилитации
*/

sw.Promed.swMeasuresForMedicalRehabilitation = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Мероприятия по медицинской реабилитации',
	maximized: false,
	maximizable: false,
	modal: true,
	resizable: false,
	height: 260,
	width: 460,
	onHide: Ext.emptyFn,
	shim: false,
	buttonAlign: "right",
	objectName: 'swMeasuresForMedicalRehabilitation',
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
		sw.Promed.swMeasuresForMedicalRehabilitation.superclass.show.apply(this, arguments);
		var params = {};
		var title = 'Мероприятия по медицинской реабилитации';
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

		var base_form = this.Frm.getForm();
		if(arguments[0].params){
			params = arguments[0].params;
		}

		if(params.MeasuresRehabMSE_IsExport){
			base_form.findField('MeasuresRehabMSE_IsExport').setValue(params.MeasuresRehabMSE_IsExport);
		}

		
		switch(this.action)
		{
			case 'add':
				debugger;
				this.setTitle(title+': добавление');
				base_form.findField('action').setValue('add');
				base_form.findField('EvnPrescrMse_id').setValue(params.EvnPrescrMse_id);
			break;
			
			case 'edit':
			case 'view':
				debugger;
				base_form.setValues(params);
				if(this.action == 'edit'){
					this.setTitle(title+': изменение');
					var BegDate = base_form.findField('MeasuresRehabMSE_BegDate');
					var EndDate = base_form.findField('MeasuresRehabMSE_EndDate');
					if(	params.MeasuresRehabMSE_IsExport && params.MeasuresRehabMSE_IsExport==2 ){
						//усли загруженно из регистра ИПРА
						if(params.MeasuresRehabMSE_BegDate) {
							BegDate.setMinValue(params.MeasuresRehabMSE_BegDate);
							EndDate.setMinValue(params.MeasuresRehabMSE_BegDate);
							EndDate.setMaxValue( new Date() );
						}
					}else{
						if(params.MeasuresRehabMSE_BegDate) {
							BegDate.setMaxValue( new Date() );
							EndDate.setMaxValue( new Date() );
						}
					}		
				} else if(this.action == 'view') {
					this.buttons[0].setVisible(false);
					this.setTitle(title+': просмотр');
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

		var begStickDate = new Date(frm.findField('MeasuresRehabMSE_BegDate').getValue()),
			endStickDate = new Date(frm.findField('MeasuresRehabMSE_EndDate').getValue()),
			dt = new Date();

		// проверка: дата окончания должна быть больше даты начала
		if (endStickDate < begStickDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					frm.findField('MeasuresRehabMSE_EndDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}
		/*
		// вычитаем из текущей даты 365 дней + 1 доп. день
		dt.setDate(dt.getDate() - 366);
		var diffDate = new Date(dt);
		// проверка: дата начала должна быть больше (текущая дата - 1 год)
		if (begStickDate < diffDate) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					frm.findField('MeasuresRehabMSE_BegDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['neobhodimo_ukazat_period_vremennoj_netrudosposobnosti_tolko_za_poslednie_12_mesjacev'],
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}
		*/
		// win.getLoadMask(lang['sohranenie_dannyih']).show();
		/*
		frm.submit({
			success: function(){
				win.getLoadMask().hide();
				win.hide();
				win.onHide();
			},
			failure: function(){
				debugger;
				win.getLoadMask().hide();
				sw.swMsg.alert(
					lang['oshibka'],
					lang['pri_sohranenii_dannyih_proizoshla_oshibka']
				);
			}
		});
		*/
		var params = {
			MeasuresRehabMSE_BegDate: frm.findField('MeasuresRehabMSE_BegDate').getValue(),
			MeasuresRehabMSE_EndDate: frm.findField('MeasuresRehabMSE_EndDate').getValue(),
			MeasuresRehabMSE_Type: frm.findField('MeasuresRehabMSE_Type').getValue(),
			MeasuresRehabMSE_SubType: frm.findField('MeasuresRehabMSE_SubType').getValue(),
			MeasuresRehabMSE_Name: frm.findField('MeasuresRehabMSE_Name').getValue(),
			MeasuresRehabMSE_Result: frm.findField('MeasuresRehabMSE_Result').getValue(),
			MeasuresRehabMSE_IsExport: frm.findField('MeasuresRehabMSE_IsExport').getValue()
		}	
		var rec = new Array(new Ext.data.Record(params));	
		win.onHide(rec);
		win.hide();
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
			url: '/?c=MeasuresRehab&m=saveMeasuresForMedicalRehabilitation',
			labelAlign: 'right',
			items: [
				{
					layout: 'form',
					border: false,
					width: 420,
					style: 'margin-top: 20px',
					items: [
						{
							xtype: 'hidden',
							name: 'MeasuresRehabMSE_id'
						},{
							xtype: 'hidden',
							name: 'EvnPrescrMse_id'
						},{
							xtype: 'hidden',
							name: 'Person_id'
						}, {
							xtype: 'hidden',
							name: 'action'
						}, {
							name: 'MeasuresRehabMSE_IsExport',
							xtype: 'hidden',
						},{
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
											name: 'MeasuresRehabMSE_BegDate',
											fieldLabel: lang['data_nachala'],
											maxValue : new Date(),
										}
									]
								}, {
									layout: 'form',
									columnWidth: .52,
									labelWidth: 120,
									items: [
										{
											xtype: 'swdatefield',
											// allowBlank: false,
											anchor: '100%',
											name: 'MeasuresRehabMSE_EndDate',
											fieldLabel: lang['data_okonchaniya']
										}
									]
								},
							]
						}, 
						{
							layout: 'form',
							border: false,
							labelWidth: 150,
							style: 'margin-top: 15px;',
							items: [
								{
									xtype: 'textfield',
									anchor: '100%',
									// allowBlank: false,
									fieldLabel: 'Тип мероприятия',
									// labelWidth: 100,
									style: 'margin-top: 5px;',
									name: 'MeasuresRehabMSE_Type'
								},
								{
									xtype: 'textfield',
									anchor: '100%',
									// allowBlank: false,
									fieldLabel: 'Подтип мероприятия',
									// labelWidth: 200,
									style: 'margin-top: 5px;',
									name: 'MeasuresRehabMSE_SubType'
								},
								{
									xtype: 'textfield',
									anchor: '100%',
									allowBlank: false,
									fieldLabel: 'Наименование',
									style: 'margin-top: 5px;',
									name: 'MeasuresRehabMSE_Name'
								},
								{
									xtype: 'textfield',
									anchor: '100%',
									allowBlank: false,
									fieldLabel: 'Результат',
									style: 'margin-top: 5px;',
									name: 'MeasuresRehabMSE_Result'
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
		sw.Promed.swMeasuresForMedicalRehabilitation.superclass.initComponent.apply(this, arguments);
	}
});