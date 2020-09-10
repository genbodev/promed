/**
 * swIframeWindow - простое окно с ифреймом
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2016 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swIframeWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swIframeWindow',
	objectSrc: '/jscore/Forms/Admin/swIframeWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	title: '',
	draggable: true,
	id: 'swIframeWindow',
	width: 400,
	//autoHeight: true,
	height: 350,
	modal: true,
	plain: true,
	resizable: false,
	initComponent: function() {
		var win = this;

		win.IframePanel = new Ext.Panel({
			html: ''
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
				HelpButton(this),
				{
					iconCls: 'cancel16',
					handler: function() {
						this.ownerCt.hide();
					},
					onTabElement: 'MSEW_MedService_Name',
					text: BTN_FRMCANCEL
				}],
			items: [
				win.IframePanel
			]
		});
		sw.Promed.swIframeWindow.superclass.initComponent.apply(this, arguments);
	},
	onLoadIframe: function(iframe) {
		// iframe.contentWindow.document.getElementById("login").value = "123"; // нет доступа к ифрейму
	},
	show: function() {
		sw.Promed.swIframeWindow.superclass.show.apply(this, arguments);

		var win = this;

		win.setTitle('Окно для открытия страниц в iframe');
		win.IframePanel.body.update('');

		if (arguments && arguments[0]) {
			if (arguments[0].title) {
				win.setTitle(arguments[0].title);
			}

			if (arguments[0].url) {
				win.IframePanel.body.update('<iframe onLoad="getWnd(\'swIframeWindow\').onLoadIframe(this);" src="'+arguments[0].url+'" width="400" height="350" style="border: 0px; padding: 20px;" align="center">Ваш браузер не поддерживает плавающие фреймы!</iframe>');
			}
		}
	}
});
