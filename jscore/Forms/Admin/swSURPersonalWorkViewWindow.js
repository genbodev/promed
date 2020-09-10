/**
 * swSURPersonalWorkViewWindow - окно просмотра данных места работы СУР
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			09.02.2017
 */
/*NO PARSE JSON*/

sw.Promed.swSURPersonalWorkViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSURPersonalWorkViewWindow',
	title: 'Место работы "СУР"',
	autoHeight: true,
	modal: true,
	width: 960,

	show: function () {
		sw.Promed.swSURPersonalWorkViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();
		var ID = null;

		if (arguments[0] && arguments[0].ID) {
			ID = arguments[0].ID;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Подождите, идет загрузка...'});
		loadMask.show();

		this.enableEdit(false);

		base_form.reset();
		base_form.load({
			url: '/?c=ServiceSUR&m=loadPersonalWork',
			params: {ID: ID},
			success: function () {
				loadMask.hide();
			},
			failure: function () {
				loadMask.hide();
			}
		});
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			autoHeight: true,
			frame: true,
			labelAlign: 'right',
			items: [{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					width: '47%',
					labelWidth: 195,
					style: 'margin-left: 0;',
					defaults: {
						anchor: '100%'
					},
					items: [{
						xtype: 'textfield',
						name: 'PersonFIO',
						fieldLabel: 'ФИО',
					}, {
						xtype: 'textfield',
						name: 'fpName',
						fieldLabel: 'Функциональное подразделение'
					}, {
						xtype: 'textfield',
						name: 'PersonalTypeRU',
						fieldLabel: 'Тип персонала'
					}, {
						xtype: 'textfield',
						name: 'PostTypeRU',
						fieldLabel: 'Тип должности'
					}, {
						xtype: 'textfield',
						name: 'PostFuncRU',
						fieldLabel: 'Наименование должности'
					}, {
						xtype: 'textfield',
						name: 'StatusPostRu',
						fieldLabel: 'Состояние должности'
					}, {
						xtype: 'textfield',
						name: 'SpecialityRU',
						fieldLabel: 'Специальность'
					}]
				}, {
					layout: 'form',
					border: false,
					width: '50%',
					labelWidth: 210,
					style: 'margin-left: 0;',
					defaults: {
						anchor: '100%'
					},
					items: [{
						xtype: 'textfield',
						name: 'PostCount',
						fieldLabel: 'Количество занимаемых ставок',
					}, {
						xtype: 'textfield',
						name: 'BeginDate',
						fieldLabel: 'Дата начала',
					}, {
						xtype: 'textfield',
						name: 'EndDate',
						fieldLabel: 'Дата окончания'
					}, {
						xtype: 'textfield',
						name: 'OrderNum',
						fieldLabel: 'Номер приказа'
					}, {
						xtype: 'textfield',
						name: 'RsnWorkRu',
						fieldLabel: 'Основание для оформления трудовых соглашений'
					}, {
						xtype: 'textfield',
						name: 'RsnWorkTerminationRu',
						fieldLabel: 'Уточнение причин прекращения трудового договора'
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: function(){}
			}, [
				{name: 'PersonFIO'},
				{name: 'fpName'},
				{name: 'PersonalTypeRU'},
				{name: 'PostTypeRU'},
				{name: 'PostFuncRU'},
				{name: 'StatusPostRu'},
				{name: 'SpecialityRU'},
				{name: 'PostCount'},
				{name: 'BeginDate'},
				{name: 'EndDate'},
				{name: 'OrderNum'},
				{name: 'RsnWorkRu'},
				{name: 'RsnWorkTerminationRu'}
			])
		});


		Ext.apply(this,{
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swSURPersonalWorkViewWindow.superclass.initComponent.apply(this, arguments);
	}
});