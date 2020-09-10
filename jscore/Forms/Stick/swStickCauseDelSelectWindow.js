/**
 * swStickCauseDelSelectWindow - Форма выбора причины прекращения действия ЭЛН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2017 Swan Ltd.
 * @version      11.10.2017
 */

sw.Promed.swStickCauseDelSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swStickCauseDelSelectWindow',
	objectSrc: '/jscore/Forms/Common/swStickCauseDelSelectWindow.js',
	collapsible: false,
	draggable: true,
	autoHeight: true,
	id: 'StickCauseDelSelectWindow',
    buttonAlign: 'left',
    closeAction: 'hide',
	modal: true,
	resizable: false,
	plain: true,
	width: 400,
    title: 'Причина прекращения действия ЭЛН',
    callback: Ext.emptyFn,
    /**
     * Показываем окно
     * @return {Boolean}
     */
	show: function() {
        sw.Promed.swStickCauseDelSelectWindow.superclass.show.apply(this, arguments);

        this.callback = Ext.emptyFn;
        if (typeof arguments[0].callback == 'function') {
            this.callback = arguments[0].callback;
        }

        this.countNotPaid = 0;
        if (arguments[0].countNotPaid) {
			this.countNotPaid = arguments[0].countNotPaid;
		}

        this.existDuplicate = 0;
        if (arguments[0].existDuplicate) {
			this.existDuplicate = arguments[0].existDuplicate;
		}

		var base_form = this.FormPanel.getForm();
        base_form.reset();

		base_form.findField('StickCauseDel_id').lastQuery = "";
		base_form.findField('StickCauseDel_id').getStore().clearFilter();
        if (this.existDuplicate == 1) {
        	base_form.findField('StickCauseDel_id').getStore().filterBy(function(rec) {
				return rec.get('StickCauseDel_id') != 1;
			});
		}

        return true;
	},
    /**
     * Действия по нажатию кнопки выбор
     */
    doSelect: function(){
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        this.callback(base_form.findField('StickCauseDel_id').getValue());
        this.hide();
        return true;
    },
    /**
     * Декларируем компоненты формы и создаем форму
     */
	initComponent: function() {
        var thas = this;
        this.FormPanel = new sw.Promed.FormPanel({
			autoHeight: true,
			layout: 'form',
			items: [{
				fieldLabel: 'Причина',
				anchor: '100%',
				hiddenName: 'StickCauseDel_id',
				xtype: 'swcommonsprcombo',
				allowBlank: false,
				comboSubject: 'StickCauseDel'
			}]
        });

    	Ext.apply(this, {
			buttonAlign: "right",
			buttons: [{
				handler: function() {
					thas.doSelect();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					thas.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
            border: false,
			layout: 'form',
			items: [
                this.FormPanel
            ]
		});
		sw.Promed.swStickCauseDelSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});