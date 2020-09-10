/**
 * swHomeVisitCancelWindow - окно отмены вызова на дом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      06.09.2016
 */

/*NO PARSE JSON*/
sw.Promed.swHomeVisitCancelWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Отмена вызова на дом',
	id: 'swHomeVisitCancelWindow',
	layout: 'fit',
	maximizable: false,
	width: 500,
	autoHeight: true,
	modal: true,

	doSave: function() {
		if (Ext.isEmpty(this.HomeVisit_id)) {
			return;
		}

		if (this.needLpuComment && Ext.isEmpty(Ext.getCmp('HVCW_LpuComment').getValue())) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Отмена вызова..." });
		loadMask.show();

		var params = {
			HomeVisit_id: this.HomeVisit_id,
			HomeVisit_LpuComment: Ext.getCmp('HVCW_LpuComment').getValue()
		};

		Ext.Ajax.request({
			url: '/?c=HomeVisit&m=cancelHomeVisit',
			params: params,
			success: function() {
				loadMask.hide();
				this.hide();
				this.returnFunc();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swHomeVisitCancelWindow.superclass.show.apply(this, arguments);

		this.returnFunc = Ext.emptyFn;
		this.allowLpuComment = false;

		if (arguments[0].callback) {
			this.returnFunc = arguments[0].callback;
		}
		if (arguments[0].needLpuComment) {
			this.needLpuComment = arguments[0].needLpuComment;
		}

		if (arguments[0].HomeVisit_id) {
			this.HomeVisit_id = arguments[0].HomeVisit_id;
		}

		var Address_Address, Person_Surname, Person_Firname, Person_Secname;
		if (arguments[0].Address_Address) {
			Address_Address = arguments[0].Address_Address;
		}
		if (arguments[0].Person_Surname) {
			Person_Surname = arguments[0].Person_Surname;
		}
		if (arguments[0].Person_Firname) {
			Person_Firname = arguments[0].Person_Firname;
		}
		if (arguments[0].Person_Secname) {
			Person_Secname = arguments[0].Person_Secname;
		}

		this.tpl.overwrite(this.TextPanel.body, {
			Address_Address: Address_Address,
			Person_Fio: Person_Surname+(Person_Firname?' '+Person_Firname:'')+(Person_Secname?' '+Person_Secname:'')
		});

		if (this.needLpuComment) {
			this.LpuCommentPanel.show();
			Ext.getCmp('HVCW_LpuComment').setAllowBlank(false);
		} else {
			this.LpuCommentPanel.hide();
			Ext.getCmp('HVCW_LpuComment').setAllowBlank(true);
		}

		this.doLayout();
		this.syncShadow();
	},

	initComponent: function() {
		this.tpl = new Ext.Template('Отменить вызов по адресу «{Address_Address}» для {Person_Fio}?');

		this.TextPanel = new Ext.Panel({
			bodyStyle: 'margin-bottom: 5px;',
			border: false,
			frame: false,
			autoHeight: true,
			html: ''
		});

		this.LpuCommentPanel = new Ext.FormPanel({
			border: false,
			autoHeight: true,
			labelAlign: 'top',
			items: [{
				id: 'HVCW_LpuComment',
				xtype: 'textarea',
				fieldLabel: 'Причина отмены',
				name: 'HomeVisit_LpuComment',
				autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 500},
				anchor: '100%'
			}]
		});

		Ext.apply(this, {
			items: [{
				border: false,
				frame: true,
				autoHeight: true,
				items: [
					this.TextPanel,
					this.LpuCommentPanel
				]
			}],
			buttons: [
				{
					text: 'Согласен',
					iconCls: 'save16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text:'-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function() {
						ShowHelp(this.title);
					}.createDelegate(this)
				},
				{
					text: BTN_FRMCANCEL,
					iconCls: 'cancel16',
					handler: function() {
						this.hide();
					}.createDelegate(this)
				}
			]
		});

		sw.Promed.swHomeVisitCancelWindow.superclass.initComponent.apply(this, arguments);
	}
});