/**
* ЛИС: форма "Контроль качества"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    All
* @access     public
* @autor      Salavat Magafurov
* @copyright  Copyright (c) 2019 EMSIS.
* @version    01.07.2019
*/

sw.Promed.swQcControlMaterialValueWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swQcControlMaterialValueWindow',
	titleString: langs('Контрольные материалы / Методики'),
	modal: true,
	maximized: false,
	width: 650,
	height: 320,

	show: function() {
		var params = arguments[0],
			win = this,
			form = win.formPanel.getForm(),
			uslugaComplexField = form.findField('UslugaComplex_id'),
			analyzerField = form.findField('Analyzer_id'),
			analyzerTestField = form.findField('AnalyzerTest_id');

		win.action = params.action ? params.action : 'view';
		win.callback = params.callback ? params.callback : Ext.emptyFn;
		win.owner = params.owner ? params.owner : false;

		switch (win.action) {
			case "edit":
				win.setTitle(win.titleString + ': Редактирование');
				break;
			case "add":
				win.setTitle(win.titleString + ": Добавление");
				break;
			default:
				win.setTitle(win.titleString + ': Просмотр');
		}

		sw.Promed.swQcControlMaterialValueWindow.superclass.show.apply(win, arguments);

		if(!params.QcControlMaterial_id) {
			sw.swMsg.alert('Сообщение', 'Выберите материал');
			win.hide();
			return;
		}

		win.enableEdit( win.action.inlist(["edit","add"]));
		form.reset();
		form.setValues(params);

		var baseParams = { Analyzer_id: params.Analyzer_id };
		uslugaComplexField.getStore().baseParams = baseParams;
		analyzerTestField.getStore().baseParams = baseParams;
		analyzerTestField.getStore().baseParams.AnalyzerTest_isTest = 2;
		//uslugaComplexField.getStore().baseParams.medServiceComplexOnly = true;
		uslugaComplexField.setRawValue(params.UslugaComplex_Name);

		if(win.action.inlist(['edit','view'])) {
			uslugaComplexField.getStore().load({ params: { UslugaComplex_id: params.UslugaComplex_id } });
			win.loadAnalyzerTestStore(params.UslugaComplex_id);
		} else {
			uslugaComplexField.getStore().removeAll();
			analyzerTestField.getStore().removeAll();
		}

		if(win.action.inlist(['edit','add'])) {
			//win.setDisabledFields(!params.Analyzer_id);
			uslugaComplexField.setDisabled(!params.Analyzer_id);
			analyzerTestField.setDisabled(!params.Analyzer_id);
		}

		analyzerField.getStore().load({ params: { MedService_id: params.MedService_id } });
		if(win.action == 'add' && params.Analyzer_id) {
			analyzerField.fireEvent('select', analyzerField);
		}

		var b10field = form.findField('QcControlMaterialValue_B10'),
			b20field = form.findField('QcControlMaterialValue_B20'),
			allowBlank = params.QcControlMaterial_IsAttested != "true";

		b10field.allowBlank = allowBlank;
		b10field.validate();
		b20field.allowBlank = allowBlank;
		b20field.validate();
	},

	setDisabledFields: function(disabled) {
		var win = this,
			form = win.formPanel.getForm(),
			uslugaComplexField = form.findField('UslugaComplex_id'),
			analyzerTestField = form.findField('AnalyzerTest_id');

		uslugaComplexField.setDisabled(disabled);
		analyzerTestField.setDisabled(disabled);
	},

	loadAnalyzerTestStore: function(UslugaComplex_id) {
		var win = this,
			analyzerTestField =  win.formPanel.getForm().findField('AnalyzerTest_id');
		analyzerTestField.getStore().load({ params: { UslugaComplex_id: UslugaComplex_id }});
	},

	loadMaxValues: function(UslugaComplex_id) {
		var win = this,
			params = {
				UslugaComplex_id: UslugaComplex_id
			};

		win.showLoadMask('Загрузка справочника для параметров CV10, B10, CV20, B20');
		Ext.Ajax.request({
			params: params,
			url: '/?c=QcControlMaterialValue&m=getMaxValues',
			callback: function(options, success, response) {
				win.hideLoadMask();
				var resp_obj = response.responseText ? Ext.util.JSON.decode(response.responseText) : false;
				if(resp_obj.Error_Code || resp_obj.Error_Msg) {
					sw.swMsg.alert( 'Ошибка', 'При загрузке справочника' );
				}
				var formParams = {
					QcControlMaterialValue_B10: null,
					QcControlMaterialValue_B20: null,
					QcControlMaterialValue_CV10: null,
					QcControlMaterialValue_CV20: null
				};
				if(resp_obj.length) {
					formParams = resp_obj[0];
				}
				win.formPanel.getForm().setValues(formParams);
			}
		})
	},

	initComponent: function() {
		var win = this;

		win.UslugaComplexField = new sw.Promed.SwBaseLocalCombo({ //SwUslugaComplexNewCombo
			fieldLabel: langs('Код теста'),
			width: 400,
			listWidth: 500,
			disabled: true,
			hiddenName: 'UslugaComplex_id',
			valueField: 'UslugaComplex_id',
			displayField: 'UslugaComplex_Name',
			codeField: 'UslugaComplex_Code',
			tpl:'<tpl for="."><div class="x-combo-list-item">' +
					'<font color="red">{UslugaComplex_Code}</font>&nbsp;{UslugaComplex_Name}' +
				'</div></tpl>',
			store: new Ext.data.JsonStore({
				url: '/?c=QcControlMaterialValue&m=loadUslugaComplex',
				key: 'UslugaComplex_id',
				autoLoad: false,
				fields: [
					{ name: 'UslugaComplex_id', type: 'int' },
					{ name: 'UslugaComplex_Code', type: 'string' },
					{ name: 'UslugaComplex_Name', type: 'string' }
				]
			}),
			listeners: {
				select: function(combo, rec) {
					var analyzerTestField =  win.formPanel.getForm().findField('AnalyzerTest_id');
					analyzerTestField.clearValue();
					analyzerTestField.getStore().removeAll();

					if(!rec) return;

					var UslugaComplex_id = rec.get('UslugaComplex_id');

					win.loadAnalyzerTestStore(UslugaComplex_id);
					if(UslugaComplex_id && win.action != 'view') {
						win.loadMaxValues(UslugaComplex_id);
					}
				}
			}
		});

		win.formPanel = new sw.Promed.FormPanel({
			saveUrl: '/?c=QcControlMaterialValue&m=doSave',
			url: '/?c=QcControlMaterialValue&m=loadEditForm',
			object: 'QcControlMaterial',
			identField: 'QcControlMaterial_id',
			labelWidth: 200,
			defaults: {
				allowBlank: false
			},
			items: [
				{
					xtype: 'hidden',
					name: 'QcControlMaterialValue_id',
					allowBlank: true
				},
				{
					xtype: 'hidden',
					name: 'QcControlMaterial_id'
				},
				{
					xtype: 'hidden',
					name: 'MedService_id'
				},
				{
					xtype: 'textfield',
					name: 'QcControlMaterial_Name',
					fieldLabel: langs('Материал'),
					disabled: true
				},
				{
					xtype: 'swanalyzercombo',
					fieldLabel: langs('Анализатор'),
					anchor: '',
					width: 400,
					separateStore: true,
					listeners: {
						select: function(combo, rec) {
							var form = win.formPanel.getForm(),
								analyzerTestField =  form.findField('AnalyzerTest_id'),
								uslugaComplexField = form.findField('UslugaComplex_id');

							var Analyzer_id = combo.getValue();

							if(win.action != 'view') {
								analyzerTestField.getStore().removeAll();
								uslugaComplexField.getStore().removeAll();
								analyzerTestField.clearValue();
								uslugaComplexField.clearValue();
								analyzerTestField.setDisabled(!Analyzer_id);
								uslugaComplexField.setDisabled(!Analyzer_id);
							}


							uslugaComplexField.getStore().baseParams.Analyzer_id = Analyzer_id;
							analyzerTestField.getStore().baseParams.Analyzer_id = Analyzer_id;

							uslugaComplexField.getStore().load();
						}
					}
				},
				win.UslugaComplexField,
				{
					xtype: 'swanalyzertestcombo',
					fieldLabel: langs('Методика КМ'),
					disabled: true,
					anchor: '',
					width: 400,
					listWidth: 500,
					editable: true,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<div>{AnalyzerTest_Name}</div>',
						'<div style="font-size: 10px;">{AnalyzerTest_pid_Name}</div>',
						'</div>',
						'</div></tpl>'
					),
					store: new Ext.data.JsonStore({
						autoLoad: false,
						key: 'AnalyzerTest_id',
						fields: [
							{ type: 'int', name: 'AnalyzerTest_id' },
							{ type: 'string', name: 'AnalyzerTest_Code' },
							{ type: 'string', name: 'AnalyzerTest_Name' },
							{ type: 'string', name: 'AnalyzerTest_pid_Name' }
						],
						url: '?c=AnalyzerTest&m=loadList',
						listeners: {
							load: function () {
								var form = win.formPanel.getForm(),
									analyzerTestField = form.findField('AnalyzerTest_id');
								analyzerTestField.setValue(analyzerTestField.getValue());
							}
						}
					})
				},
				{
					xtype: 'numberfield',
					fieldLabel: langs('Среднее Xcp'),
					name: 'QcControlMaterialValue_X',
					decimalPrecision: 10
				},
				{
					xtype: 'numberfield',
					fieldLabel: langs('Отклонение Scp'),
					name: 'QcControlMaterialValue_S',
					decimalPrecision: 10
				},
				{
					xtype: 'numberfield',
					fieldLabel: langs('Коэффициент вариации CV10'),
					name: 'QcControlMaterialValue_CV10',
					decimalPrecision: 10
				},
				{
					xtype: 'numberfield',
					fieldLabel: langs('Смещение B10'),
					name: 'QcControlMaterialValue_B10',
					decimalPrecision: 10
				},
				{
					xtype: 'numberfield',
					fieldLabel: langs('Коэффициент вариации CV20'),
					name: 'QcControlMaterialValue_CV20',
					decimalPrecision: 10
				},
				{
					xtype: 'numberfield',
					fieldLabel: langs('Смещение B20'),
					name: 'QcControlMaterialValue_B20',
					decimalPrecision: 10
				}
			],
			reader: new Ext.data.JsonReader({
					success: function() {
						
					}
				},
				[
					'QcControlMaterialValue_id',
					'QcControlMaterial_id',
					'QcControlMaterial_Name',
					'UslugaComplex_id',
					'AnalyzerTest_id',
					'QcControlMaterialValue_X',
					'QcControlMaterialValue_S',
					'QcControlMaterialValue_CV10',
					'QcControlMaterialValue_CV20',
					'QcControlMaterialValue_B10',
					'QcControlMaterialValue_B20',
					'QcControlMaterialValue_begDT',
					'QcControlMaterialValue_endDT'
				]
			),
			afterSave: function(data) {
				win.hide();
				win.callback(win.owner,data.QcControlMaterial_id);
			}
		})
		Ext.apply(win, { items: win.formPanel });
		sw.Promed.swQcControlMaterialValueWindow.superclass.initComponent.apply(this, arguments);
	}
});