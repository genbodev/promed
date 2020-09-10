/**
 * swRecordUnionSubObjectWindow - окно настройки переноса зависимых объектов при объединении записей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.08.2015
 */
/*NO PARSE JSON*/
sw.Promed.swRecordUnionSubObjectWindow = Ext.extend(sw.Promed.BaseForm,
{
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	width: 400,
	//height: 250,
	layout: 'form',
	id: 'swRecordUnionSubObjectWindow',
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,

	doSave: function() {
		var base_form = this.FormPanel.getForm();
		var values = base_form.getValues();

		if (this.setting.allowChangeMainRecord) {
			this.callback({selectMainRecord: values.selectMainRecord});
		} else {
			this.callback();
		}
		
		this.hide();
	},

	show: function() {
		sw.Promed.swRecordUnionSubObjectWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.callback = arguments[0].callback;
		this.setting = arguments[0].setting;
		this.RecordType_Code = arguments[0].RecordType_Code;
		this.RecordType_Name = arguments[0].RecordType_Name;
		this.mainRecord = arguments[0].mainRecord;
		this.minorRecord = arguments[0].minorRecord;

		this.findById('RUSOW_SelectMainRecordPanel').hide();

		this.setTitle(lang['nastroyki_obyedineniya'] + this.setting.title);
		this.DescriptionTpl.overwrite(this.findById('RUSOW_DescriptionPanel').body, {
			description: this.setting.description
		});

		Ext.getCmp('RUSOW_SelectMainRecord_1').labelEl.dom.innerHTML = this.mainRecord.Record_Code+' '+this.mainRecord.Record_Name;
		Ext.getCmp('RUSOW_SelectMainRecord_2').labelEl.dom.innerHTML = this.minorRecord.Record_Code+' '+this.minorRecord.Record_Name;

		if (this.setting.allowChangeMainRecord) {
			this.findById('RUSOW_SelectMainRecordPanel').show();
			if (this.setting.selectMainRecord) {
				base_form.findField('selectMainRecord').setValue(this.setting.selectMainRecord);
			}
		}

		this.syncShadow();
	},

	initComponent: function() {
		this.DescriptionTpl = new Ext.Template([
			'<div>{description}</div>'
		]);

		this.FormPanel = new Ext.FormPanel({
			autoHeight: true,
			labelAlign: 'right',
			items: [{
				id: 'RUSOW_DescriptionPanel',
				border: false,
				style: 'margin-left: 10px; margin-top: 5px; margin-bottom: 5px; font-size: 12px;',
				html: ''
			}, {
				id: 'RUSOW_SelectMainRecordPanel',
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['glavnaya_zapis'],
				style: 'margin-left: 7px; margin-right: 7px; margin-bottom: 7px;',
				labelWidth: 1,
				items: [{
					xtype: 'radio',
					labelSeparator: '',
					boxLabel: lang['zapis_1'],
					inputValue: 1,
					id: 'RUSOW_SelectMainRecord_1',
					name: 'selectMainRecord',
					checked: true
				}, {
					xtype: 'radio',
					labelSeparator: '',
					boxLabel: lang['zapis_2'],
					inputValue: 2,
					id: 'RUSOW_SelectMainRecord_2',
					name: 'selectMainRecord'
				}]
			}]
		});

		Ext.apply(this,{
			layout: 'fit',
			buttons:
				[{
					handler: function()
					{
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
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
						tabIndex: TABINDEX_LPEEW + 17,
						text: BTN_FRMCANCEL
					}],
			items: [this.FormPanel]
		});

		sw.Promed.swRecordUnionSubObjectWindow.superclass.initComponent.apply(this, arguments);
	}
});