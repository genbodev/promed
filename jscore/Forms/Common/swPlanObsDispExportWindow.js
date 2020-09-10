/**
* swPlanObsDispExportWindow - окно настроек экспорта данных плана контрольных посещений в рамках ДН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

/*NO PARSE JSON*/
 
sw.Promed.swPlanObsDispExportWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPlanObsDispExportWindow',
	objectSrc: '/jscore/Forms/Common/swPlanObsDispExportWindow.js',
	closable: false,
	width : 500,
	height : 200,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: 'Экспорт плана контрольных посещений в рамках ДН',
	params: null,
	callback: Ext.emptyFn,
	doExport: function() {
		var win = this;
		var form = win.form;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask('Выполняется экспорт...').show();
			
		base_form.submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide();
			},
			params: {
				PlanObsDisp_id: win.PlanObsDisp_id, 
				DispCheckPeriod_Year: win.DispCheckPeriod_Year,
				DispCheckPeriod_Month: win.DispCheckPeriod_Month,
				Lpu_id: getGlobalOptions().lpu_id,
				PacketNumber: base_form.findField('PacketNumber').getValue()
			},
			success: function(result_form, action) {
				win.getLoadMask().hide();

				win.getPacketNumber();

				if (action.result && action.result.link) {
					if (action.result.link) {
						sw.swMsg.alert('Результат', 'Экспорт успешно завершён<br/><a target="_blank" download="" href="' + action.result.link + '">Скачать и сохранить архив экспорта</a>');
					}
					win.callback();
				} else {
					sw.swMsg.alert(langs('Ошибка'), 'При экспорте данных произошла ошибка');
				}
			}
		});
	},
	getPacketNumber: function() {
		var win = this;
		var base_form = win.form.getForm();

		base_form.findField('PacketNumber').setValue('');
		
		win.getLoadMask('Получение порядкового номера пакета').show();
		Ext.Ajax.request({
			url: '/?c=PlanObsDisp&m=getPlanObsDispExportPackNum',
			params: {
				Export_Year: win.DispCheckPeriod_Year,
				Export_Month: win.DispCheckPeriod_Month,
				Lpu_id: getGlobalOptions().lpu_id
			},
			callback: function (options, success, response) {
				if (success) {
					win.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.PacketNumber) {
						base_form.findField('PacketNumber').setValue(result.PacketNumber);
					}
				}
			}.createDelegate(this)
		});
	},
	/**
	 * Отображение окна
	 */
	show: function() {
		var win = this;
		sw.Promed.swPlanObsDispExportWindow.superclass.show.apply(win, arguments);		
		if (arguments[0].callback) {
            this.callback = arguments[0].callback;
        } else {
			this.callback = Ext.emptyFn;
		}

		var base_form = win.form.getForm();
		base_form.reset();
		
		if (arguments[0]['PlanObsDisp_id']) {
			win.PlanObsDisp_id = arguments[0]['PlanObsDisp_id'];
		} else {
			win.PlanObsDisp_id = null;
		}
		if (arguments[0]['DispCheckPeriod_Year']) {
			win.DispCheckPeriod_Year = arguments[0]['DispCheckPeriod_Year'];
		} else {
			win.DispCheckPeriod_Year = null;
		}
		if (arguments[0]['DispCheckPeriod_Month']) {
			win.DispCheckPeriod_Month = arguments[0]['DispCheckPeriod_Month'];
		} else {
			win.DispCheckPeriod_Month = null;
		}
		
		base_form.findField('ExportDate').setValue(new Date());

		win.getPacketNumber();
		//~ win.syncSize();
		//~ win.syncShadow();
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;

    	Ext.apply(this, {
			items: [ win.form = new Ext.form.FormPanel({
				url: '/?c=PlanObsDisp&m=exportPlanObsDisp',
				timeout: 1800,
				layout : 'form',
				autoHeight: true,
				border : false,
				frame : true,
				bodyStyle : 'padding: 5px',
				labelWidth : 1,
				items : [{
					style : 'padding-left: 5px',
					layout : 'form',
					labelWidth : 100,
					labelAlign : 'right',
					items: [{
						xtype: 'swdatefield',
						fieldLabel: 'Дата экспорта',
						name: 'ExportDate',
						allowBlank: false,
						width: 100
					}, {
						allowBlank: false,
						width: 100,
						name: 'PacketNumber',
						fieldLabel: 'Порядковый номер пакета',
						autoCreate: {tag: "input", maxLength: 5, autocomplete: "off"}, //по последнему приказу maxLength = 5
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}]
				}]
			})],
			buttons : [{
				text : 'Сформировать',
				iconCls : 'ok16',
				handler : function(button, event) {							
					win.doExport();
				}.createDelegate(this)
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			buttonAlign : "right"
		});
		sw.Promed.swPlanObsDispExportWindow.superclass.initComponent.apply(this, arguments);
	}
});