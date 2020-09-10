/**
* swParodontogramEditWindow - форма просмотра/редактирования пародонтограммы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      07.2013
* @comment      
*/

/*NO PARSE JSON*/
sw.Promed.swParodontogramEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swParodontogramEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swParodontogramEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'fit',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: ' ',
	draggable: true,
	id: 'swParodontogramEditWindow',
	width: 700,
	height: 260,
	modal: true,
	plain: true,
	resizable: false,

	initComponent: function() {
		var me = this;
        me.parodontogramPanel = new sw.Promed.ParodontogramPanel({});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
                    me.parodontogramPanel.doSave({
                        callback: function() {
                            me.hide();
                            me.callback();
                        }
                    });
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
                    me.hide();
				},
                iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
                me.parodontogramPanel
			]
		});
		sw.Promed.swParodontogramEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
        var me = this;
		sw.Promed.swParodontogramEditWindow.superclass.show.apply(this, arguments);

		if (!arguments[0]
            || !arguments[0].formParams
            || !arguments[0].formParams.Person_id
            || !arguments[0].formParams.EvnUslugaStom_id
            || !arguments[0].formParams.EvnUslugaStom_setDate
        ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {
                me.hide();
            });
			return false;
		}
        var formParams = arguments[0].formParams;
        me.action = arguments[0].action || 'view';
        me.callback = arguments[0].callback || Ext.emptyFn;
        me.onHide = arguments[0].onHide ||  Ext.emptyFn;

        switch (me.action) {
            case 'view':
                me.setTitle(lang['parodontogramma_prosmotr']);
                break;
            default:
                me.setTitle(lang['parodontogramma_redaktirovanie']);
                break;
        }

        me.parodontogramPanel.doReset();
        me.parodontogramPanel.applyParams(
            formParams.Person_id,
            formParams.EvnUslugaStom_id,
            formParams.EvnUslugaStom_setDate
        );
        me.parodontogramPanel.setReadOnly(me.action == 'view');
        me.parodontogramPanel.doLoad();

        me.center();
        me.syncSize();
        me.doLayout();
        return true;
	}
});
