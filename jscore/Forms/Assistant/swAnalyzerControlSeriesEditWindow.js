/**
 * swAnalyzerControlSeriesEditWindow - Контрольная серия
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @comment
 */
sw.Promed.swAnalyzerControlSeriesEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: langs('Контрольная серия'),
	id: 'AnalyzerControlSeriesEditWindow',
	modal: false,
	width: 650,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	layout: 'form',
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var params = {};
		params.MedPersonal_id = getGlobalOptions().medpersonal_id;

		win.getLoadMask('Подождите, идет сохранение...').show();
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				win.getLoadMask().hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();
				if (action.result) {
					win.callback();
					win.hide();
				}
			}
		});
	},
	show: function() {
		sw.Promed.swAnalyzerControlSeriesEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = win.FormPanel.getForm();
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.AnalyzerControlSeries_id = arguments[0].AnalyzerControlSeries_id || null;
		this.AnalyzerTest_id = arguments[0].AnalyzerTest_id || null;
		this.Analyzer_begDT = arguments[0].Analyzer_begDT || null;
		this.MedService_id = arguments[0].MedService_id || null;
		
		base_form.reset();
		
		base_form.findField('AnalyzerControlSeries_regDT').setMinValue(this.Analyzer_begDT);
		base_form.findField('AnalyzerControlSeries_regDT').setMaxValue(getGlobalOptions().date);
		
		if (this.AnalyzerControlSeries_id) {
			win.getLoadMask('Загрузка...').show();
			this.FormPanel.load({
				params: {
					AnalyzerControlSeries_id: win.AnalyzerControlSeries_id
				},
				success: function(f, r) {
					win.getLoadMask().hide();
					
				},
				url: '/?c=AnalyzerControlSeries&m=load'
			});
		} else {
			base_form.findField('AnalyzerTest_id').setValue(this.AnalyzerTest_id);
			base_form.findField('MedService_id').setValue(this.MedService_id);
		}
	},
	initComponent: function() {
		var win = this;

		win.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			autoHeight: true,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 190,
			items: [{
				name: 'AnalyzerControlSeries_id',
				xtype: 'hidden'
			}, {
				name: 'AnalyzerTest_id',
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			}, {
				fieldLabel: langs('Дата регистрации результата'),
				name: 'AnalyzerControlSeries_regDT',
				allowBlank: false,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				width: 100,
				xtype: 'swdatefield'
			}, {
				fieldLabel: langs('Результат'),
				name: 'AnalyzerControlSeries_Value',
				allowBlank: false,
				allowDecimals: true,
				xtype: 'numberfield',
				width: 100
			}, {
				fieldLabel: 'Контроль пройден',
				hiddenName: 'AnalyzerControlSeries_IsControlPassed',
				allowBlank: false,
				width: 100,
				xtype: 'swyesnocombo'
			}, {
				fieldLabel: langs('Примечание'),
				name: 'AnalyzerControlSeries_Comment',
				xtype: 'textfield',
				width: 400
			}],
            url:'/?c=AnalyzerControlSeries&m=save',
			reader: new Ext.data.JsonReader({
				success: function() {}
			}, [
				{name: 'AnalyzerControlSeries_id'},
				{name: 'AnalyzerTest_id'},
				{name: 'MedService_id'},
				{name: 'AnalyzerControlSeries_id'},
				{name: 'AnalyzerControlSeries_regDT'},
				{name: 'AnalyzerControlSeries_Value'},
				{name: 'AnalyzerControlSeries_IsControlPassed'},
				{name: 'AnalyzerControlSeries_Comment'}
			])
		});
		Ext.apply(this, {
			modal: true,
			height: 230,
			buttons:[{
				handler: function(){
						this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function()
				{
						this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.FormPanel
			]
		});
		sw.Promed.swAnalyzerControlSeriesEditWindow.superclass.initComponent.apply(this, arguments);
	}
});