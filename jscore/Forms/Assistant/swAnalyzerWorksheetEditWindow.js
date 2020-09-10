/**
 * swAnalyzerWorksheetEditWindow - окно редактирования "Рабочий список"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      06.2012
 * @comment
 */
sw.Promed.swAnalyzerWorksheetEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: lang['rabochiy_spisok'],
	id: 'AnalyzerWorksheetEditWindow',
	modal: false,
	width: 600,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	layout: 'form',
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function(callback) {
		var that = this;
		var base_form = that.FormPanel.getForm();
		if ( !base_form.isValid() )
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						that.FormPanel.findById('AnalyzerWorksheetEditForm').getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		var name = base_form.findField('AnalyzerWorksheet_Name').getValue();
		if (name) {
			this.submit(callback);
		} else {
			this.regenerateAnalyzerWorksheetName(function () {
				that.submit(callback);
			});
		}
		return true;
	},
	submit: function(callback) {
		var that = this;
		var base_form = that.FormPanel.getForm();
		that.getLoadMask(lang['podojdite_idet_sohranenie']).show();
		var params = {};
		params.action = that.action;
		base_form.submit({
			params: params,
			failure: function(result_form, action)
			{
				that.getLoadMask().hide();
				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				that.getLoadMask().hide();
				base_form.findField('AnalyzerWorksheet_id').setValue(action.result.AnalyzerWorksheet_id);
				if (callback){
					callback();
				} else {
					var params  = base_form.getValues();
					// статус = новый
					params.AnalyzerWorksheetStatusType_Name = lang['novyiy'];
					// получаем анализатор, тип рабочего списка и размерность
					params.Analyzer_Name = base_form.findField('Analyzer_id').getRawValue();
					params.AnalyzerRack_DimensionX = base_form.findField('AnalyzerRack_id').getFieldValue('AnalyzerRack_DimensionX');
					params.AnalyzerRack_DimensionY = base_form.findField('AnalyzerRack_id').getFieldValue('AnalyzerRack_DimensionY');
					that.callback(that.owner, action.result.AnalyzerWorksheet_id, params);
				}
				that.hide();
			}
		});
	},
	regenerateAnalyzerWorksheetName: function(callback) {
		var that = this;
		var base_form = that.FormPanel.getForm();
		var oldvalue = base_form.findField('AnalyzerWorksheet_Name').getValue();
		//Генерируем имя для рабочего списка
		//Код анализатора+’ ’+код типа РС +’  ‘ + дата ГГММДД+ номер по порядку за сегодня.
		if (!oldvalue) {
			var dailyCount;
			var gendate = getGlobalOptions().date;
			var Analyzer = base_form.findField('Analyzer_id');
			var Analyzer_id = Analyzer.getValue();
			if (Analyzer_id) {//если анализатор выбран
				var Analyzer_Code = Analyzer.getFieldValue('Analyzer_Code');
				Ext.Ajax.request({
					url: '?c=AnalyzerWorksheet&m=getDailyCount',
					params: {
						gendate: gendate
					},
					callback: function(options, success, response) {
						if (success) {
							if ( response.responseText.length > 0 ) {
                                var result = Ext.util.JSON.decode(response.responseText);
								if (result[0].dailyCount || result[0].dailyCount == 0) {
									dailyCount = parseInt(result[0].dailyCount)+1;
									var AnalyzerWorksheet_Name = Analyzer_Code + ' ' + Date.parseDate(gendate, 'd.m.Y').format('ymd') + ' ' + dailyCount;
									base_form.findField('AnalyzerWorksheet_Name').setValue(AnalyzerWorksheet_Name);
									if (callback) {
										callback();
									}
								} else {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.ERROR,
										msg: result.ErrorMessage,
										title: lang['ne_udalos_opredelit_nomer_po_poryadku_za_segodnya']
									});
								}
							}
						}
					}
				});
			}
		}
	},
	show: function() {
		sw.Promed.swAnalyzerWorksheetEditWindow.superclass.show.apply(this, arguments);
		
		var that = this;
		var base_form = that.FormPanel.getForm();
		if ( arguments[0].MedService_id ) {
			this.MedService_id = arguments[0].MedService_id;
		} else {
			this.MedService_id = null;
		}
		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerWorksheet_id = null;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
			return false;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].AnalyzerWorksheet_id ) {
			this.AnalyzerWorksheet_id = arguments[0].AnalyzerWorksheet_id;
		}
		base_form.reset();
		that.getLoadMask(lang['zagruzka']).show();
		base_form.findField('Analyzer_id').getStore().load({
			params: {
				MedService_id: that.MedService_id,
				hideRuchMetodiki: 1
			}
		});
		switch (arguments[0].action) {
			case 'add':
				base_form.findField('AnalyzerWorksheet_Code').focus(true, 100);
				that.getLoadMask().hide();
				break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						that.getLoadMask().hide();
						that.hide();
					},
					params:{
						AnalyzerWorksheet_id: that.AnalyzerWorksheet_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false}
						base_form.setValues(result[0]);
						base_form.findField('Analyzer_id').fireEvent('change', base_form.findField('Analyzer_id'), base_form.findField('Analyzer_id').getValue());
						base_form.findField('AnalyzerWorksheet_Code').focus(true, 100);
						that.getLoadMask().hide();
						return true;
					},
					url:'/?c=AnalyzerWorksheet&m=load'
				});
				break;
		}
		return true;
	},
	initComponent: function() {
		var that = this;
		
		that.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			autoHeight: true,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				layout: 'form',
				id: 'AnalyzerWorksheetEditForm',
				bodyStyle:'background:#DFE8F6;',
				border: true,
				labelWidth: 150,
				collapsible: true,
				region: 'center',
				url:'/?c=AnalyzerWorksheet&m=save',
				items: [{
					name: 'AnalyzerWorksheet_id',
					xtype: 'hidden',
					value: 0
				},
					{
						fieldLabel: lang['naimenovanie'],
						name: 'AnalyzerWorksheet_Name',
						allowBlank:true,
						xtype: 'textfield',
						anchor: '100%',
						emptyText: lang['sozdaetsya_avtomaticheski']
					},
					{
						fieldLabel: lang['kod_rabochego_spiska'],
						name: 'AnalyzerWorksheet_Code',
						allowBlank:false,
						xtype: 'textfield',
						width: 150
					},
					{
						fieldLabel: lang['analizator'],
						hiddenName: 'Analyzer_id',
						xtype: 'swanalyzercombo',
						allowBlank:false,
						anchor: '100%',
						listeners: {
							'change': function (){
								var base_form = that.FormPanel.getForm();
								var Analyzer = base_form.findField('Analyzer_id');
								var Analyzer_id = Analyzer.getValue();
								var AnalyzerModel_id = Analyzer.getStore().data.get(Analyzer_id).data.AnalyzerModel_id;
								base_form.findField('AnalyzerRack_id').setValue(null);
								base_form.findField('AnalyzerRack_id').getStore().load({
									params: {AnalyzerModel_id: AnalyzerModel_id},
									callback: function() {
									}
								});
								that.regenerateAnalyzerWorksheetName();
							}
						}
					},
					{
						fieldLabel: lang['shtativ'],
						hiddenName: 'AnalyzerRack_id',
						xtype: 'swanalyzerrackcombo',
						allowBlank:false,
						sortField:'AnalyzerRack_Code',
						comboSubject: 'AnalyzerRack',
						anchor: '100%',
						listeners: {
							change: function (){
								that.regenerateAnalyzerWorksheetName();
							}
						}
					},
					{
						name: 'AnalyzerWorksheetStatusType_id',
						xtype: 'hidden',
						allowBlank:true,
						value: 1
					}
				]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'AnalyzerWorksheet_id'},
				{name: 'AnalyzerWorksheet_Code'},
				{name: 'AnalyzerWorksheet_Name'},
				{name: 'AnalyzerWorksheet_setDT'},
				{name: 'AnalyzerRack_id'},
				{name: 'AnalyzerWorksheetStatusType_id'},
				{name: 'Biomaterial_id'},
				{name: 'Analyzer_id'}
			]),
			url: '/?c=AnalyzerWorksheet&m=save'
		});
		Ext.apply(this, {
			modal: true,
			height: 230,
			buttons:[{
					handler: function()
					{
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
					{
						text: '-'
					},
					HelpButton(this, 0),//todo проставить табиндексы
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[
				that.FormPanel
			]
		});
		sw.Promed.swAnalyzerWorksheetEditWindow.superclass.initComponent.apply(this, arguments);
	}
});