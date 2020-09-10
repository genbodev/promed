/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      19.05.2009
*/

/**
 * swSelectEvnStatusCauseWindow - окно с выбором причины установки статуса
 *
 * @class sw.Promed.swSelectEvnStatusCauseWindow
 * @extends sw.Promed.BaseForm
 */
sw.Promed.swSelectEvnStatusCauseWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	border: false,
	closable: true,
	closeAction:'hide',
    modal: true,
    plain: false,
    resizable: false,
    winTitle: lang['vyibor_prichinyi_ustanovki_statusa'],
    width: 500,
    listeners: {
        'hide': function(win) {
            if (win._isCancel) win.onHide();
        }
    },
    show: function() {
        sw.Promed.swSelectEvnStatusCauseWindow.superclass.show.apply(this, arguments);
        this.setTitle(arguments[0].winTitle || this.winTitle);
        this.Evn_id = arguments[0].Evn_id || null;
        this.EvnStatus_id = arguments[0].EvnStatus_id || null;
        this.EvnClass_id = arguments[0].EvnClass_id || null;
        this.formType = arguments[0].formType || 'polka';
        this._isCancel = true;
        // Функция вызывающаяся после выбора причины установки статуса
        this.callback = (typeof arguments[0].callback == 'function') ? arguments[0].callback : Ext.emptyFn;
        // Функция вызывающаяся при отмене выбора причины установки статуса
        this.onHide = (typeof arguments[0].onHide == 'function') ? arguments[0].onHide : Ext.emptyFn;
        var base_form = this.FormPanel.getForm();
        base_form.reset();
        this.filterEvnStatusCause();
        base_form.findField('EvnStatusCause_id').setAllowBlank(13 == this.EvnStatus_id);
        base_form.findField('EvnStatusCause_id').focus(true, 250);
		base_form.findField('EvnStatusCause_id').setValue(1);
    },
    filterEvnStatusCause: function() {
        var win = this;
        var base_form = this.FormPanel.getForm();

		if (27 == win.EvnClass_id && win.Evn_id) {
			// запрашиваем тип направления, т.к. от него зависит список возможных причин
			win.getLoadMask('Получение данных направления').show();
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=getEvnDirectionInfo',
				params: {
					EvnDirection_id: win.Evn_id
				},
				callback: function (opt, success, response) {
					win.getLoadMask().hide();

					var DirType_Code = 0;

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj[0] && response_obj[0].DirType_Code) {
							DirType_Code = response_obj[0].DirType_Code;
						}
					}

					base_form.findField('EvnStatusCause_id').getStore().clearFilter();
					base_form.findField('EvnStatusCause_id').lastQuery = '';
					base_form.findField('EvnStatusCause_id').getStore().filterBy(function (rec) {
						var flag = true;
						var EvnStatusCauseCodeList = [ '1', '5', '18', '22', '24', '25', '26', '27', '28' ];

						if (DirType_Code.toString().inlist(['1', '5'])) {
							EvnStatusCauseCodeList = ['1', '5', '18', '22'];
							switch (getRegionNick()) {
								case 'kareliya':
									EvnStatusCauseCodeList.push('6');
									EvnStatusCauseCodeList.push('24');
									break;

								case 'ufa':
									EvnStatusCauseCodeList.push('2');
									EvnStatusCauseCodeList.push('9');
									break;

								case 'kz':
									EvnStatusCauseCodeList.push('3');
								case 'ekb':
									EvnStatusCauseCodeList.push('4');
									break;
							}		
						}
						flag = rec.get('EvnStatusCause_Code').toString().inlist(EvnStatusCauseCodeList);
							if (DirType_Code.toString() == '7') {
								EvnStatusCauseCodeList = ['1', '2', '3', '4', '5', '8', '14', '15', '18', '19'];
								flag = rec.get('EvnStatusCause_Code').toString().inlist(EvnStatusCauseCodeList);
							}
						return flag;
					});
				}
			});
		} else {
			base_form.findField('EvnStatusCause_id').getStore().clearFilter();
			base_form.findField('EvnStatusCause_id').lastQuery = '';
			base_form.findField('EvnStatusCause_id').getStore().filterBy(function (rec) {
				var flag = true;
				if (27 == win.EvnClass_id) {
					flag = rec.get('EvnStatusCause_Code').toString().inlist(['1', '3', '4', '5', '18', '22', '24', '25', '26', '27', '28']);
					if (win.formType.inlist(['labdiag'])) {
						flag = rec.get('EvnStatusCause_Code').toString().inlist(['1', '3', '4', '5', '14', '15', '16', '18']);
					}
					if (win.formType.inlist(['funcdiag'])) {
						flag = rec.get('EvnStatusCause_Code').inlist(['1', '5', '4', '9', '10', '11', '17']);
					}
					if (win.formType.inlist(['polka'])) {
						flag = rec.get('EvnStatusCause_Code').inlist(['1', '2', '6', '7', '8', '12', '13']);
					}
				}
				return flag;
			});
		}
    },
	submit: function() {
		var base_form = this.FormPanel.getForm();
		if (!base_form.isValid()) {
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return false;
		}
        this.callback({
            EvnStatusCause_id: base_form.findField('EvnStatusCause_id').getValue(),
            EvnStatusHistory_Cause: base_form.findField('EvnStatusHistory_Cause').getValue()
        });
        this._isCancel = false;
        this.hide();
        return true;
	},
	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			labelAlign: 'top',
			labelWidth: 140,
			style: 'padding: 10px',
			items : [{
				allowBlank: false,
				comboSubject: 'EvnStatusCause',
				fieldLabel: lang['prichina'],
				typeCode: 'int',
				sortField: 'EvnStatusCause_Code',
				width: 450,
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['kommentariy'],
				name: 'EvnStatusHistory_Cause',
				width: 450,
                height: 70,
                maxLength: 200,
				xtype: 'textarea'
			}],
			url : ''
		});
		
    	Ext.apply(this, {
			buttonAlign: "right",
			buttons: [{
                text : lang['vyibrat'],
                iconCls : 'ok16',
                handler : function() {
                    win.submit();
                }
            }, {
                text: '-'
            },
            HelpButton(this),
            {
                text : lang['zakryit'],
                iconCls : 'close16',
                handler : function(button, event) {
                    win.hide();
                }
            }],
			items : [
				win.FormPanel
			]
		});
		sw.Promed.swSelectEvnStatusCauseWindow.superclass.initComponent.apply(this, arguments);
	}
});