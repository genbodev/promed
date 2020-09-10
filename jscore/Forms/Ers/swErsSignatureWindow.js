/**
* Форма подписания запроса
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swErsSignatureWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'ЭРС. Подписание',
	modal: true,
	resizable: false,
	maximized: false,
	width: 500,
	height: 140,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swErsSignatureWindow',
	closeAction: 'hide',
	objectSrc: '/jscore/Forms/Common/swErsSignatureWindow.js',
	show: function() {
		sw.Promed.swErsSignatureWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			base_form = this.MainPanel.getForm();
		
		base_form.reset();
	},
	
	onLoad: function() {
		
	},
	
	initComponent: function() {
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoheight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				name: 'ErsSignature_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnERS_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				xtype: 'textfield',
				width: 300,
				disabled: true,
				name: 'ErsSignature_ERSNumber',
				fieldLabel: 'Документ'
			}, {
				xtype: 'textfield',
				disabled: true,
				width: 300,
				name: 'ErsSignature_Snils',
				fieldLabel: 'Сертификат'
			}],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'Lpu_id' },
				{ name: 'Org_INN' },
				{ name: 'Org_KPP' },
				{ name: 'Org_OGRN' },
			]),
			url: '/?c=Annotation&m=save'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			items: [
				this.MainPanel
			],
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}, {
				handler: function () {
					this.doSign();
				}.createDelegate(this),
				iconCls: 'signature16',
				text: 'Подписать'
			}]
		});
		
		sw.Promed.swErsSignatureWindow.superclass.initComponent.apply(this, arguments);
	}
});