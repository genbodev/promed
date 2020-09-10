/**
 * swVaccinesDosesCheckWindow  - окно результата проверки перед назначением, формы "Вакцины и дозы"
 */

Ext6.define('common.EMK.Vaccination.swVaccinesDosesCheckWindow', {
    extend: 'base.BaseForm',
	alias: 'widget.swVaccinesDosesCheckWindow',
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: '',
	renderTo: main_center_panel.body.dom,
	width: 650,
    modal: true,
    show: function () {
		this.callParent(arguments);
    },
    initComponent: function() {
		var me = this;
		var conf = me.initialConfig;
		this.params = conf.params
		this.title = this.params.title
		console.log( 'this.params.vacinationEnable_inGroup', this.params.vacinationEnable_inGroup)
		me.formPanel = Ext6.create('Ext6.form.FieldSet',{
			title: this.params.message_text,
			autoHeight: true,
			border: false,
			style: {
				paddingTop: '15px',
				paddingLeft: '10px',
				borderWidth: '0px !important'
			},
			items:[
				{
					xtype:'container',
					items:[
						{
							xtype: 'grid',
							padding: '0 0 0 0',
							rowLines: false,
							border: false,
							scrollable: true,
							maxHeight: 300,
							disableSelection: true,
							columns: [
								{	
									header: false, 
									dataIndex: 'Vaccination_Name',
									resizable: false, 
									sortable: false,
									width: '100%'
								},
							],
							store: new Ext6.create('Ext6.data.Store', {
								fields: [
									{name: 'Vaccination_Name'}
								],
								data: this.params.vacinationEnable_inGroup ? this.params.vacinationEnable_inGroup : this.params.disabled_vaccines_all
							})
						},
						{
							xtype: 'textfield',
							width: '100%',
							inputWrapCls: '',
							triggerWrapCls: '',
							fieldStyle: 'background:none',
							style: {
								marginTop: '25px',
							},
							hidden:!this.params.footer_text,
							value: this.params.footer_text
						},
					]
				}
			]
		})

        Ext6.apply(me, {
			items: [
				me.formPanel,
			],
			buttons: [
				'->',
				{
					cls: 'buttonCancel',
					text: this.params.vacinationEnable_inGroup ? 'Нет' : 'ОК',
					margin: 0,
					handler: function() {
						me.hide();
					},
				},
				{
					cls: 'buttonAccept',
					text: 'Да',
					margin: '0 19 0 0',
					handler: function() {
						var loadMask = new Ext6.LoadMask(me, {msg: "Подождите, идет сохранение..."});
							loadMask.show();
						me.successCallback(loadMask);
					},
					hidden:!this.params.vacinationEnable_inGroup ? true : false
				}
			]
		});

		me.callParent(arguments);
    }
 });