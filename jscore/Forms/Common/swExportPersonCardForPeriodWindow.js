/**
* swExportPersonCardForPeriodWindow - окно выгрузки прикрепленного населения за период
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
* @comment      Префикс для id компонентов epcfpw (swExportPersonCardForPeriodWindow)
*/

sw.Promed.swExportPersonCardForPeriodWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'ExportPersonCardForPeriodWindow',
	title: lang['export_prikreplennogo_naseleniya_za_period'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'ExportPersonCardForPeriodTextPanel',
			html: lang['vyigruzka_spiska_prikreplennogo_naseleniya_v_formate_xml']
		});

		win.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'ExportPersonCardForPeriodPanel',
			labelAlign: 'right',
			labelWidth: 180,
			items: [{
				allowBlank: false,
				fieldLabel: 'Период выгрузки',
				name: 'ExportDateRange',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
				width: 180,
				//showToday: false,
				//syncMonthChange: true,
				//editable: false,
				//readOnly: true,
				listeners:{
					'select': function(cmp,value){		
						var newDate = new Date(cmp.getValue2());
						var reportDate = new Date(newDate.getFullYear(),newDate.getMonth()+1,1);
						var base_form = win.FormPanel.getForm();
						base_form.findField('ReportDate').setValue(reportDate);
					}.createDelegate(this),
					'blur': function(cmp){
						var newDate = new Date(cmp.getValue2());
						var reportDate = new Date(newDate.getFullYear(),newDate.getMonth()+1,1);
						var base_form = win.FormPanel.getForm();
						base_form.findField('ReportDate').setValue(reportDate);
					}.createDelegate(this)
				},
				xtype: 'daterangefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата формирования файла',
				name: 'FileCreationDate',
				width: 100,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Отчетная дата',
				name: 'ReportDate',
				width: 100,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				autoCreate: {tag: "input", maxLength: "3", autocomplete: "off"},
				fieldLabel: 'Порядковый номер пакета',
				maskRe: /[0-9]/,
				name: 'PackageNum',
				width: 100,
				xtype: 'textfield'
			},
				win.TextPanel
			]
		});
		
		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				id: 'epcfpwOk',
				handler: function() {
					var base_form = win.FormPanel.getForm();
					if ( base_form.findField('ExportDateRange').getValue2() > base_form.findField('ReportDate').getValue() )
					{
						sw.swMsg.show({
                        title: lang['vopros'],
                        msg: 'Отчетная дата попадает в отчетный период выгрузки. Продолжить формирование?',
                        buttons: Ext.Msg.YESNO,
                        fn: function ( buttonId ) {
                            if ( buttonId == 'yes' ) {
                                win.createExportFile();
                            }
                        }
                    });
					}
					else
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
				onTabElement: 'epcfpwOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.FormPanel
			]
		});

		sw.Promed.swExportPersonCardForPeriodWindow.superclass.initComponent.apply(this, arguments);
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

		if ( parseInt(base_form.findField('PackageNum').getValue()) == 0 ) {
			base_form.findField('PackageNum').focus(250, true);
			form.TextPanel.getEl().dom.innerHTML = 'Значение поля "Порядковый номер пакета" должно быть больше 0';
			form.TextPanel.render();
			form.syncShadow();
			return false;
		}

		/*if ( base_form.findField('ExportDateRange').getValue2() > base_form.findField('ReportDate').getValue() ) {
			
			base_form.findField('ReportDate').focus(250, true);
			form.TextPanel.getEl().dom.innerHTML = 'Значение поля "Отчетная дата" должно быть не более текущей и более даты окончания периода выгрузки';
			form.TextPanel.render();
			form.syncShadow();
			return false;
			

		}*/

		form.getLoadMask().show();
		form.buttons[0].disable();

		var params = {
			ExportDateRange: base_form.findField('ExportDateRange').getRawValue(),
			FileCreationDate: base_form.findField('FileCreationDate').getValue().format('d.m.Y'),
			ReportDate: base_form.findField('ReportDate').getValue().format('d.m.Y'),
			PackageNum: base_form.findField('PackageNum').getValue()
		}

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
			url: '/?c=PersonCard&m=exportPersonCardForPeriod',
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
					if ( response_obj.Count ) {
						text = text + '<div>Выгружено записей: ' + response_obj.Count + '</div>';
					}

					if ( response_obj.Link ) {
						text = text + '<div><a target="_blank" href="' + response_obj.Link + '">Скачать и сохранить список</a></div>';
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
		sw.Promed.swExportPersonCardForPeriodWindow.superclass.show.apply(this, arguments);

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

		base_form.findField('FileCreationDate').setMaxValue(getGlobalOptions().date);
		//base_form.findField('ReportDate').setMaxValue(getGlobalOptions().date);
		//base_form.findField('ReportDate').setMinValue(undefined);

		var dt = getValidDT(getGlobalOptions().date, '');
		if ( typeof dt == 'object' ) {
			dt = dt.add(Date.MONTH, -1);
			var initialPeriod = dt.getFirstDateOfMonth().format('d.m.Y') + ' - ' + dt.getLastDateOfMonth().format('d.m.Y');
			base_form.findField('ExportDateRange').setValue(initialPeriod);
		}

		base_form.findField('FileCreationDate').setValue(getGlobalOptions().date);

		var dt = getValidDT(getGlobalOptions().date, '');
		base_form.findField('ReportDate').setValue(dt.getFirstDateOfMonth().format('d.m.Y')); // 1 число текущего месяца
		base_form.findField('PackageNum').setValue(1);


		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_prikreplennogo_naseleniya_v_formate_xml'];
		form.TextPanel.render();

		this.syncSize();
		this.syncShadow();
	}
});