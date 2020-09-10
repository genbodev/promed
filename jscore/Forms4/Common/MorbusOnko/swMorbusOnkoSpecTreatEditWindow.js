/**
 * swMorbusOnkoSpecTreatEditWindow - окно редактирования "Специальное лечение"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
 
Ext6.define('common.MorbusOnko.swMorbusOnkoSpecTreatEditWindow', {
	/* свойства */
	requires: [
		'common.MorbusOnko.AddOnkoComplPanel'
	],
	alias: 'widget.swMorbusOnkoSpecTreatEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoSpecTreateditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Специальное лечение',
	width: 800,

	/* методы */
	save: function () {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}
		
		var params = {};
		if (this.EvnPL_id)
			params.EvnPL_id = this.EvnPL_id;
		var lateCompls = this.OnkoLateComplTreatTypePanel.getValues();
		params.lateCompls = lateCompls.length ? lateCompls.join(',') : '';

		win.mask(LOAD_WAIT_SAVE);

		base_form.submit({
			params: params,
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) ) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}

				win.callback();
				win.hide();
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},
	onSprLoad: function(arguments) {

		var win = this;

		win.action = (typeof arguments[0].action == 'string' ? arguments[0].action : 'add');
		win.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext6.emptyFn);
		win.formParams = (typeof arguments[0].formParams == 'object' ? arguments[0].formParams : {});

		win.center();
		win.setTitle('Специальное лечение');
		
		if (arguments[0]['MorbusOnkoSpecTreat_id']) {
			this.MorbusOnkoSpecTreat_id = arguments[0]['MorbusOnkoSpecTreat_id'];
		} else {
			this.MorbusOnkoSpecTreat_id = null;
		}
		
		if (arguments[0]['MorbusOnko_id']) {
			this.MorbusOnko_id = arguments[0]['MorbusOnko_id'];
		} else {
			this.MorbusOnko_id = null;
		}
		
		if (arguments[0]['MorbusOnkoVizitPLDop_id']) {
			this.MorbusOnkoVizitPLDop_id = arguments[0]['MorbusOnkoVizitPLDop_id'];
		} else {
			this.MorbusOnkoVizitPLDop_id = null;
		}
		
		if (arguments[0]['MorbusOnkoDiagPLStom_id']) {
			this.MorbusOnkoDiagPLStom_id = arguments[0]['MorbusOnkoDiagPLStom_id'];
		} else {
			this.MorbusOnkoDiagPLStom_id = null;
		}
		
		if (arguments[0]['MorbusOnkoLeave_id']) {
			this.MorbusOnkoLeave_id = arguments[0]['MorbusOnkoLeave_id'];
		} else {
			this.MorbusOnkoLeave_id = null;
		}
		
		if (arguments[0]['EvnVizitPL_id']) {
			this.EvnVizitPL_id = arguments[0]['EvnVizitPL_id'];
		}

		if(arguments[0]['EvnSection_id']) {
			this.EvnSection_id = arguments[0]['EvnSection_id'];
		} else {
			this.EvnSection_id = null;
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(win.formParams);

		switch ( win.action ) {
			case 'add':
				win.setTitle(win.getTitle() + ': Добавление');
				base_form.findField('MorbusOnkoSpecTreat_specSetDT').focus();
				this.OnkoLateComplTreatTypePanel.setValues([null]);
				base_form.isValid();
				break;

			case 'edit':
				win.setTitle(win.getTitle() + ': Редактирование');

				win.mask(LOAD_WAIT);

				base_form.load({
					url: '/?c=MorbusOnkoSpecifics&m=loadMorbusOnkoSpecTreatEditForm',
					params: {
						MorbusOnkoSpecTreat_id: base_form.findField('MorbusOnkoSpecTreat_id').getValue()
					},
					success: function(form, action) {
						win.unmask();
						var result = Ext.util.JSON.decode(action.response.responseText);
						if (result[0]) {
							base_form.findField('MorbusOnkoSpecTreat_specSetDT').focus();
							if(result[0].lateCompls){
								win.OnkoLateComplTreatTypePanel.setValues(result[0].lateCompls);
							} else {
								win.OnkoLateComplTreatTypePanel.setValues([null]);
							}
							base_form.isValid();
						}
					},
					failure: function() {
						win.unmask();
					}
				});
				break;
		}
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model',
			fields: [
				{name: 'MorbusOnkoSpecTreat_id'}, 
				{name: 'MorbusOnko_id'}, 
				{name: 'MorbusOnkoLeave_id'}, 
				{name: 'MorbusOnkoVizitPLDop_id'}, 
				{name: 'MorbusOnkoDiagPLStom_id'}, 
				{name: 'MorbusOnkoSpecTreat_specSetDT'}, 
				{name: 'MorbusOnkoSpecTreat_specDisDT'}, 
				{name: 'TumorPrimaryTreatType_id'},
				{name: 'TumorRadicalTreatIncomplType_id'},
				{name: 'OnkoCombiTreatType_id'},
				{name: 'OnkoLateComplTreatType_id'}
			]
		});
		
		win.OnkoLateComplTreatTypePanel = Ext6.create('common.MorbusOnko.AddOnkoComplPanel', {
			objectName: 'OnkoLateComplTreatType',
			fieldLabelTitle: langs('Позднее осложнение лечения'),
			win: this,
			width: 740,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			fieldWidth: 700,
			labelWidth: 220
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyPadding: '20 35',
			defaults: {
				labelAlign: 'left',
				labelWidth: 220
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=MorbusOnkoSpecifics&m=saveMorbusOnkoSpecTreatEditForm',
			items: [{
				name: 'MorbusOnkoSpecTreat_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoLeave_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoVizitPLDop_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnkoDiagPLStom_id',
				xtype: 'hidden'
			}, {
				width: 700,
				bodyPadding: '0 0 5 0',
				border: false,
				defaults: {
					labelAlign: 'left',
					labelWidth: 220
				},
				layout: 'column',
				columns: [0.4, 0.4],
				items: [{
					fieldLabel: langs('Дата начала'),
					name: 'MorbusOnkoSpecTreat_specSetDT',
					allowBlank: false,
					width: 360,
					xtype: 'datefield'
				}, {
					labelAlign: 'right',
					labelWidth: 140,
					width: 280,
					fieldLabel: langs('Дата окончания'),
					name: 'MorbusOnkoSpecTreat_specDisDT',
					xtype: 'datefield'
				}]
			}, {
				fieldLabel: langs('Проведенное лечение первичной опухоли'),
				name: 'TumorPrimaryTreatType_id',
				xtype: 'commonSprCombo',
				sortField:'TumorPrimaryTreatType_Code',
				comboSubject: 'TumorPrimaryTreatType',
				width: 700,
				listeners: {
					'change': function(c,n){
						if(getRegionNick() == 'perm'){
							var base_form = win.FormPanel.getForm();
							if(n == 2){
								base_form.findField('TumorRadicalTreatIncomplType_id').enable();
							} else {
								base_form.findField('TumorRadicalTreatIncomplType_id').disable();
							}
						}
					}
				}
			}, {
				fieldLabel: langs('Причины незавершенности радикального лечения'),
				name: 'TumorRadicalTreatIncomplType_id',
				xtype: 'commonSprCombo',
				sortField:'TumorRadicalTreatIncomplType_Code',
				comboSubject: 'TumorRadicalTreatIncomplType',
				width: 700
			}, {
				fieldLabel: langs('Сочетание видов лечения'),
				name: 'OnkoCombiTreatType_id',
				xtype: 'commonSprCombo',
				typeCode: 'int',
				sortField:'OnkoCombiTreatType_Code',
				comboSubject: 'OnkoCombiTreatType',
				width: 700
			},
			win.OnkoLateComplTreatTypePanel
			]
		});

        Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SubmitButton',
				handler:function () {
					win.save();
				}
			}]
		});

		this.callParent(arguments);
    }
});