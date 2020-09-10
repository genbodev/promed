/**
 * swPersonPregnancyResultEditWindow - окно редактирования исхода беременности
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.04.2016
 */
/*NO PARSE JSON*/

sw.Promed.swPersonPregnancyResultEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonPregnancyResultEditWindow',
	width: 560,
	autoHeight: true,
	modal: true,

	listeners: {
		'resize': function() {
			this.syncShadow();
		}
	},

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var data = {};
		var formParams = getAllFormFieldValues(this.FormPanel);

		if (!Ext.isEmpty(base_form.findField('PregnancyResult_id').getValue())) {
			formParams.PregnancyResult_Name = base_form.findField('PregnancyResult_id').getFieldValue('PregnancyResult_Name');
		}
		if (!Ext.isEmpty(base_form.findField('BirthChildResult_id').getValue())) {
			formParams.BirthChildResult_Name = base_form.findField('BirthChildResult_id').getFieldValue('BirthChildResult_Name');
		}
		if (!Ext.isEmpty(base_form.findField('ChildStateResult_id').getValue())) {
			formParams.ChildStateResult_Name = base_form.findField('ChildStateResult_id').getFieldValue('ChildStateResult_Name');
		}

		data.PersonPregnancyResultData = formParams;

		this.callback(data);
		this.hide();
	},

	show: function() {
		sw.Promed.swPersonPregnancyResultEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		//ограничиваем возможные значения года рождения детей годом рождения матери
		if (arguments[0] && arguments[0].PersonBirthday) {
			var PersonBirthYear = arguments[0].PersonBirthday.getFullYear(),
				YearField = base_form.findField('PersonPregnancyResult_Year');
			if(PersonBirthYear) {
				YearField.minValue = PersonBirthYear;
			}
		}

		base_form.items.each(function(f){f.validate()});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Исход беременности: Добавление');
				this.enableEdit(true);
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Исход беременности: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Исход беременности: Просмотр');
					this.enableEdit(false);
				}
				loadMask.hide();
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PPREW_FormPanel',
			labelAlign: 'right',
			labelWidth: 130,
			url: '/?c=PersonPregnancy&m=savePersonPregnancyResult',
			items: [{
				xtype: 'hidden',
				name: 'PersonPregnancyResult_id'
			}, {
				xtype: 'hidden',
				name: 'PersonPregnancy_id'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'PersonPregnancyResult_Num',
				fieldLabel: '№ п/п',
				minValue: 0,
				maxValue: 99,
				width: 200
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'PersonPregnancyResult_Year',
				minValue: 1980,
				maxValue: new Date().getFullYear(),
				fieldLabel: 'Год',
				width: 200
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'PregnancyResult',
				hiddenName: 'PregnancyResult_id',
				fieldLabel: 'Исход',
				width: 200
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'PersonPregnancyResult_OutcomPeriod',
				fieldLabel: 'Срок, нед.',
				minValue: 0,
				width: 200
			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'BirthChildResult',
				hiddenName: 'BirthChildResult_id',
				fieldLabel: 'Ребенок родился',
				width: 200
			}, {
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'PersonPregnancyResult_WeigthChild',
				fieldLabel: 'Масса (вес), г',
				minValue: 0,
				width: 200
			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'ChildStateResult',
				hiddenName: 'ChildStateResult_id',
				fieldLabel: 'Текущее состояние ребенка',
				width: 200
			}, {
				xtype: 'numberfield',
				name: 'PersonPregnancyResult_AgeChild',
				fieldLabel: 'В каком возрасте',
				minValue: 0,
				width: 200
			}, {
				xtype: 'textarea',
				name: 'PersonPregnancyResult_Descr',
				fieldLabel: 'Особенности течения беременности',
				anchor: '100%'
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'PersonPregnancyResult_id'},
				{name: 'PersonPregnancy_id'},
				{name: 'PersonPregnancyResult_Num'},
				{name: 'PregnancyResult_id'},
				{name: 'PersonPregnancyResult_OutcomPeriod'},
				{name: 'BirthChildResult_id'},
				{name: 'PersonPregnancyResult_WeigthChild'},
				{name: 'ChildStateResult_id'},
				{name: 'PersonPregnancyResult_AgeChild'},
				{name: 'PersonPregnancyResult_Descr'}
			])
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'PPREW_SaveButton',
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

		sw.Promed.swPersonPregnancyResultEditWindow.superclass.initComponent.apply(this, arguments);
	}
});