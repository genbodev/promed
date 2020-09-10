/**
* swPersonXmlWindow - îêíî âûãðóçêè ïðèêðåïëåííîãî íàñåëåíèÿ â XML.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author
* @version      22.06.2011
* @comment      Ïðåôèêñ äëÿ id êîìïîíåíòîâ rxw (PersonXmlWindow)
*
*
* @input data: arm - èç êàêîãî ÀÐÌà âåä¸òñÿ âûãðóçêà
*/

/*NO PARSE JSON*/

sw.Promed.swImportAnswerFromSMO = Ext.extend(sw.Promed.BaseForm, {
	id: 'swImportAnswerFromSMO',
	width: 440,
	height: 140,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: false,
	title: 'Импорт ответа по прикрепленному населению от СМО',

	doSave: function() {
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if (!base_form.isValid())
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						wnd.formMode = 'iddle';
						wnd.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return;
		}

		wnd.getLoadMask("Подождите, выполняется импорт...").show();

		base_form.submit({
			failure: function(result_form, action) {
				wnd.formMode = 'iddle';
				wnd.getLoadMask().hide();
				if ( action.response.responseText ) {
					var answer = Ext.util.JSON.decode(action.response.responseText);
					if (answer.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], answer.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_importe_fayla']);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
				}
			},
			success: function(result_form, action) {
				wnd.formMode = 'iddle';
				wnd.getLoadMask().hide();

				if (action.result.Protocol_Link) {
						var link = '<a href="'+action.result.Protocol_Link+'" target="_blank">протоколе импорта</a>';
						sw.swMsg.alert('', ''+lang['zavershen']+' '+lang['import']+' '+lang['podrobnosti']+' '+lang['v']+' '+link+'.', function() {
                        });
					}
			}
		});
	},

	show: function()
	{
		sw.Promed.swImportAnswerFromSMO.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		this.ARMType = null;
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}

		wnd.formMode = 'iddle';

		base_form.reset();
		base_form.clearInvalid();
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			fileUpload: true,
			frame: true,
			id: 'IAFS_FormPanel',
			labelAlign: 'right',
			labelWidth: 100,
			region: 'center',
			items: [{
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: lang['vyiberite_fayl'],
				fieldLabel: lang['fayl'],
				name: 'ImportFile'
			}
			],
			url: '/?c=PersonCard&m=importAnswerFromSMO'
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.doSave();
					}.createDelegate(this),
					iconCls: 'refresh16',
					id: 'IAFS_ImportButton',
					text: lang['obnovit_dannyie']
				},{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					id: 'IAFS_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swImportAnswerFromSMO.superclass.initComponent.apply(this, arguments);
	}
});
