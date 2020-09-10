/**
* swExportPersonDispCardWindow - окно выгрузки карт диспансерного наблюдения за период
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2016 Swan Ltd.
* @author
* @version      27.07.2016
* @comment      Префикс для id компонентов epdfpw (swExportPersonDispCardWindow)
*/

sw.Promed.swExportPersonDispCardWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'ExportPersonDispCardWindow',
	title: 'Выгрузка карт диспансерного наблюдения',
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	setDefaultValues: function() {
		var base_form = this.FormPanel.getForm();
		var curr_date = new Date();
		var last_month = curr_date.getMonth();
		if (last_month > 0) {
			base_form.findField('Year').setValue(curr_date.getFullYear());
			base_form.findField('Month').setValue(last_month);
		} else {
			base_form.findField('Year').setValue(curr_date.getFullYear()-1);
			base_form.findField('Month').setValue(12);
		}
	},
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'ExportPersonDispCardTextPanel',
			html: lang['vyigruzka_spiska_kart_dispansernogo_nabludeniya_v_formate_xml']
		});

		win.monthCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: 'Месяц',
			width: 150,
			triggerAction: 'all',
			store: [
				[1, lang['yanvar']],
				[2, lang['fevral']],
				[3, lang['mart']],
				[4, lang['aprel']],
				[5, lang['may']],
				[6, lang['iyun']],
				[7, lang['iyul']],
				[8, lang['avgust']],
				[9, lang['sentyabr']],
				[10, lang['oktyabr']],
				[11, lang['noyabr']],
				[12, lang['dekabr']]
			],
			name: 'Month'
		});

		win.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'ExportPersonDispCardPanel',
			labelAlign: 'right',
			labelWidth: 180,
			items: [{
				xtype: 'numberfield',
				fieldLabel: 'Год',
				name: 'Year',
				allowDecimals: false,
				allowNegative: false,
				allowBlank: false,
				width: 70,
				plugins: [new Ext.ux.InputTextMask('9999', false)],
				minLength: 4
			}, win.monthCombo,
				win.TextPanel
			]
		});
		
		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				id: 'epdfpwOk',
				handler: function() {
					win.createExportFile();
				},
				iconCls: 'refresh16',
				text: lang['sformirovat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'epdfpwOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.FormPanel
			]
		});

		sw.Promed.swExportPersonDispCardWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	createExportFile: function() {
		var form = this;
		var base_form = form.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		form.getLoadMask().show();
		form.buttons[0].disable();

		var params = {
			Year: base_form.findField('Year').getRawValue(),
			Month: base_form.findField('Month').getValue()
		};
		console.log(params);

		Ext.Ajax.request({
			failure: function(response, options) {
				form.getLoadMask().hide();
				form.buttons[0].enable();

				form.TextPanel.getEl().dom.innerHTML = response.statusText;

				form.TextPanel.render();
				form.syncShadow();
			},
			params: params,
			timeout: 1800000,
			url: '/?c=PersonDisp&m=exportPersonDispCard',
			success: function(response, options) {
				form.getLoadMask().hide();
				form.buttons[0].enable();

				var
					response_obj = Ext.util.JSON.decode(response.responseText),
					text = '';

				if ( response_obj.success == false ) {
					text = (response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при выгрузке файла');
				}
				else {

					if ( response_obj.Links ) {
						for (var j = 0; j < response_obj.Links.length; j++) {
							text = text + '<div><a target="_blank" href="' + response_obj.Links[j] + '">DS'+response_obj.Links[j].split('/DS')[1]+'</a></div>';
						}

					}

					if ( response_obj.success === false ) {
						text = text + response_obj.Error_Msg;
					}
				}

				form.TextPanel.getEl().dom.innerHTML = text;
				form.TextPanel.render();
				form.syncShadow();
			}
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function() {
		sw.Promed.swExportPersonDispCardWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.FormPanel.getForm(),
			form = this;

		if ( !isSuperAdmin() && !isLpuAdmin(getGlobalOptions().lpu_id) ) {
			sw.swMsg.alert(lang['oshibka'], lang['funktsional_nedostupen'], function() { form.hide(); });
			return false;
		}

		base_form.reset();

		form.onHide = Ext.emptyFn;

		form.buttons[0].enable();
		form.setDefaultValues();

		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_spiska_kart_dispansernogo_nabludeniya_v_formate_xml'];
		form.TextPanel.render();

		this.syncSize();
		this.syncShadow();
	}
});