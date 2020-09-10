/**
 * swElectronicServiceEditWindow - пункт обслуживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swElectronicServiceEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: false,
	modal: true,
	height: 300,
	width: 600,
	id: 'swElectronicServiceEditWindow',
	title: 'Пункт обслуживания',
	layout: 'border',
	resizable: false,
	// имя основной формы
	formName: 'ElectronicServiceEditForm',
	// краткое имя формы (для айдишников)
	formPrefix: 'ESEW_',

	getMainForm: function()
	{
		return this[this.formName].getForm();
	},
	doSave: function() {

		var wnd = this,
			form = wnd.getMainForm();
		if (!form.isValid()) {

			sw.swMsg.show({

				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT,
				icon: Ext.Msg.WARNING,
				buttons: Ext.Msg.OK,

				fn: function() {
					wnd[wnd.formName].getFirstInvalidEl().focus(true);
				}
			});

			return false;
		}

		var params = form.getValues();
        if (!params.ElectronicService_isShownET) {params.ElectronicService_isShownET = false}

		wnd.onSave(params);
		wnd.hide();

		return true;
	},
	initComponent: function()
	{
		var wnd = this,
			formName = wnd.formName,
			formPrefix = wnd.formPrefix;

		wnd[formName] = new Ext.form.FormPanel({
			id: formName,
			region: 'center',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: false,
			labelWidth: 200,
			frame: true,
			border: false,
			items: [
			{
				name: 'Lpu_id',
				xtype: 'hidden'
			},
			{
				name: 'ElectronicService_id',
				xtype: 'hidden'
			}, {
				name: 'ElectronicService_Code',
				fieldLabel: 'Код',
				xtype: 'textfield',
				allowBlank: false,
				width: 100
			}, {
				name: 'ElectronicService_Name',
				fieldLabel: 'Наименование',
				xtype: 'textfield',
				allowBlank: false,
				width: 350
			}, {
				name: 'ElectronicService_Nick',
				fieldLabel: 'Краткое наименование',
				xtype: 'textfield',
				allowBlank: true,
				width: 350
			},
				{
				xtype: 'swcustomownercombo',
				fieldLabel: 'Замещающий пункт',
				hiddenName: 'ElectronicService_tid',
				displayField: 'ElectronicService_Name',
				valueField: 'ElectronicService_id',
				width: 350,
				allowBlank: true,
				store: new Ext.data.SimpleStore({
					autoLoad: false,
					fields: [
						{ name: 'ElectronicService_id', mapping: 'ElectronicService_id' },
						{ name: 'ElectronicService_Name', mapping: 'ElectronicService_Name' },
						{ name: 'ElectronicService_Code', mapping: 'ElectronicService_Code' },
						{ name: 'ElectronicQueueInfo_Name', mapping: 'ElectronicQueueInfo_Name'},
						{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
						{ name: 'UslugaComplexMedService_id', mapping: 'UslugaComplexMedService_id'},
						{ name: 'MedServiceType_SysNick', mapping: 'MedServiceType_SysNick' },
						{ name: 'MedService_id', mapping: 'MedService_id' }

					],
					key: 'ElectronicService_id',
					sortInfo: { field: 'ElectronicService_Name' },
					url:'/?c=ElectronicTalon&m=loadLpuBuildingElectronicServices'
				}),
				ownerWindow: wnd,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{ElectronicService_Code}</font>&nbsp;<span style="font-weight: bold;">{ElectronicQueueInfo_Name}</span>&nbsp;{ElectronicService_Name}',
					'</div></tpl>'
				)
			},
			{
				name: 'ElectronicService_Num',
				fieldLabel: 'Порядковый номер',
				xtype: 'numberfield',
				allowBlank: true,
				width: 100
			},
			{
				xtype: 'swcustomownercombo',
				fieldLabel: langs('Тип осмотра/исследования'),
				hiddenName: 'SurveyType_id',
				displayField: 'SurveyType_Name',
				valueField: 'SurveyType_id',
				width: 350,
				allowBlank: true,
				store: new Ext.data.SimpleStore({
					autoLoad: false,
					fields: [
						{ name: 'SurveyType_id', mapping: 'SurveyType_id' },
						{ name: 'SurveyType_Name', mapping: 'SurveyType_name' },
						{ name: 'SurveyType_Code', mapping: 'SurveyType_code' }
					],
					key: 'SurveyType_id',
					sortInfo: { field: 'SurveyType_Code' },
                    url: '/?c=ElectronicService&m=loadSurveyTypeList'
				}),
				ownerWindow: wnd,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{SurveyType_Code}</font>&nbsp;<span style="font-weight: bold;">{SurveyType_Name}</span>',
					'</div></tpl>'
				)
			},
			{
				name: 'ElectronicService_AvgTime',
				fieldLabel: 'Среднее время прохождения ПО',
				xtype: 'numberfield',
				allowBlank: true,
				width: 100
			},
            {
                name: 'ElectronicService_isShownET',
                boxLabel: 'Отображать повод обращения в списке записанных',
                xtype: 'checkbox',
                labelSeparator: '',
                width: 350,
                checked: false
            }
			],url: '/?c=ElectronicService&m=save'
		});

		Ext.apply(this, {
			buttons:
				[{
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
				this[this.formName]
			]
		});

		sw.Promed.swElectronicServiceEditWindow.superclass.initComponent.apply(this, arguments);
	},
    toggleFields: function(fieldsArr, action) {
        var wnd = this,
            form = wnd.getMainForm();

        fieldsArr.forEach(function(fName) {
        	var f = form.findField(fName);
        	if (f) {
        		if (action=='hide') {
                    f.hideContainer();
				}
        		if (action=='show') {
                    f.showContainer();
				}
			}
		})
	},
	setDisabled: function(disable) {

		var wnd = this,
			form = wnd.getMainForm(),
			field_arr = [];

		form.items.each(function(field){

			field.setDisabled(disable);
		});

		if (disable) {
			wnd.buttons[0].disable();
			wnd.buttons[1].disable();
		} else {
			wnd.buttons[0].enable();
			wnd.buttons[1].enable();
		}
	},
	show: function() {
		sw.Promed.swElectronicServiceEditWindow.superclass.show.apply(this, arguments);

		var wnd = this,
			form = wnd.getMainForm(),
			base_form = this.getMainForm(),
			loadMask = new Ext.LoadMask(
				wnd.getEl(),{
					msg: LOAD_WAIT
				}
			);

		var field = form.findField('ElectronicService_tid');
		if(arguments[0].queueIsService){
			field.hideContainer();
		} else {
			field.showContainer();
		}

		wnd.action = null;

		if (!arguments[0]){

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				title: lang['oshibka'],
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],

				fn: function() { wnd.hide(); }
			});
		}

		if(arguments[0].isProfosmotr) {
			this.isProfosmotr = arguments[0].isProfosmotr;
		}

		var args = arguments[0];
		wnd.focus();
		form.reset();

		this.setTitle("Пункт обслуживания");

		for (var field_name in args) {
			log(field_name +':'+ args[field_name]);
			wnd[field_name] = args[field_name];
		}

		var prof_fields = [
			'ElectronicService_tid',
            'ElectronicService_AvgTime',
			'SurveyType_id'
		]

		var combo = form.findField('ElectronicService_tid'),
			lpu_id = wnd.Lpu_id,
			current_electronicservice_id = wnd.ElectronicService_id;
       

    	if (this.isProfosmotr) {
			//base_form.findField('ElectronicService_Num').hideContainer();
    		wnd.toggleFields(prof_fields, 'show');

            combo.getStore().baseParams.Lpu_id = lpu_id;
            combo.getStore().baseParams.noLoad = true;
            combo.getStore().baseParams.CurrentElectronicService_id = current_electronicservice_id;

            combo.getStore().load({
                params: {
                    Lpu_id: lpu_id,
                    noLoad: true,
                    CurrentElectronicService_id: current_electronicservice_id
                },
                callback:function(){
                    if (args.ElectronicService_tid)
                        combo.setValue(args.ElectronicService_tid);
                }
            });

            survey_type = form.findField('SurveyType_id');

            survey_type.getStore().load({
                callback:function(){
                    if (args.SurveyType_id)
                        survey_type.setValue(args.SurveyType_id);
                }
			})
		} else {
			wnd.toggleFields(prof_fields, 'hide');
			base_form.findField('ElectronicService_Num').showContainer();
		}
        base_form.findField('SurveyType_id').setAllowBlank(!this.isProfosmotr);

		form.setValues(args);
		loadMask.show();

		switch (this.action){
			case 'add':

				this.setTitle(this.title + ": Добавление");
				loadMask.hide();
				wnd.setDisabled(false);

				form.findField('ElectronicService_Code').focus(true, 100);

				break;

			case 'edit':
			case 'view':
				loadMask.hide();
				wnd.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				wnd.setDisabled(this.action == "view");
				break;
		}
	}
});