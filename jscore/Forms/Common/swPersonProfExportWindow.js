/**
* swPersonProfExportWindow - окно выгрузки данных по профилактическим мероприятиям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2016 Swan Ltd.
* @author
* @version      30.08.2017
*/

sw.Promed.swPersonProfExportWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'PersonProfExportWindow',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	plain: true,
	resizable: false,
	title: 'Выгрузка данных по профилактическим мероприятиям',
	width: 400,

	/* методы */
	createExportFile: function() {
		var
			win = this,
			base_form = win.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask().show();
		win.buttons[0].disable();

		var params = {
			Year: base_form.findField('Year').getRawValue(),
			Month: base_form.findField('Month').getValue()
		};

		Ext.Ajax.request({
			failure: function(response, options) {
				win.getLoadMask().hide();
				win.buttons[0].enable();

				win.TextPanel.getEl().dom.innerHTML = response.statusText;

				win.TextPanel.render();
				win.syncShadow();
			},
			params: params,
			timeout: 1800000,
			url: '/?c=Person&m=exportPersonProfData',
			success: function(response, options) {
				win.getLoadMask().hide();
				win.buttons[0].enable();

				var
					response_obj = Ext.util.JSON.decode(response.responseText),
					text = '';

				if ( response_obj.success == false ) {
					text = (response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при выгрузке файла');
				}
				else {
					if ( response_obj.Links ) {
						for ( var j = 0; j < response_obj.Links.length; j++ ) {
							text = text + '<div><a target="_blank" href="' + response_obj.Links[j] + '">PROF' + response_obj.Links[j].split('/PROF')[1] + '</a></div>';
						}
					}

					if ( response_obj.success === false ) {
						text = text + response_obj.Error_Msg;
					}
				}

				win.TextPanel.getEl().dom.innerHTML = text;
				win.TextPanel.render();
				win.syncShadow();
			}
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	onHide: Ext.emptyFn,
	show: function() {
		sw.Promed.swPersonProfExportWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !isLpuAdmin(getGlobalOptions().lpu_id) && !isMedStatUser() ) {
			sw.swMsg.alert(lang['oshibka'], lang['funktsional_nedostupen'], function() { win.hide(); });
			return false;
		}

		base_form.reset();

		win.onHide = Ext.emptyFn;

		win.buttons[0].enable();

		base_form.findField('Year').setValue(getGlobalOptions().date.substr(6, 4));
		base_form.findField('Month').setValue(parseInt(getGlobalOptions().date.substr(3, 2)));

		win.TextPanel.getEl().dom.innerHTML = 'Выгрузка данных по профилактическим мероприятиям';
		win.TextPanel.render();

		this.syncSize();
		this.syncShadow();
	},

	/* конструктор */
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'PersonProfExportTextPanel',
			html: 'Выгрузка данных по профилактическим мероприятиям'
		});

		win.monthCombo = new Ext.form.ComboBox({
			allowBlank: false,
			fieldLabel: 'Месяц',
			hiddenName: 'Month',
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
			triggerAction: 'all',
			width: 150
		});

		win.yearField = {
			allowBlank: false,
			allowDecimals: false,
			allowNegative: false,
			fieldLabel: 'Год',
			maxValue: parseInt(getGlobalOptions().date.substr(6, 4)) + 1,
			minLength: 4,
			minValue: 2000,
			name: 'Year',
			plugins: [new Ext.ux.InputTextMask('9999', false)],
			width: 70,
			xtype: 'numberfield'
		};

		win.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PersonProfExportPanel',
			labelAlign: 'right',
			labelWidth: 140,
			items: [
				win.yearField,
				win.monthCombo,
				win.TextPanel
			]
		});
		
		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				id: this.id + 'OkButton',
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
				onTabElement: this.id + 'CancelButton',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.FormPanel
			]
		});

		sw.Promed.swPersonProfExportWindow.superclass.initComponent.apply(this, arguments);
	}
});