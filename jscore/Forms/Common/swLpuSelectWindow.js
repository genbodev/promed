/**
 * swLpuSelectWindow - Форма выбора ЛПУ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @version      11.2013
 */

sw.Promed.swLpuSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLpuSelectWindow',
	objectSrc: '/jscore/Forms/Common/swLpuSelectWindow.js',
	collapsible: false,
	draggable: true,
	height: 550,
	id: 'LpuSelectWindow',
    buttonAlign: 'left',
    closeAction: 'hide',
	maximized: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	resizable: false,
	plain: true,
	width: 800,
    title: lang['vyibor_lpu'],
    listeners:
    {
        hide: function(win)
        {
            win.onHide(win.hasSelect);
        }
    },

    callback: Ext.emptyFn,
    onHide: Ext.emptyFn,
    hasSelect: false,

    /**
     * Показываем окно
     * @return {Boolean}
     */
	show: function() {
        sw.Promed.swLpuSelectWindow.superclass.show.apply(this, arguments);
        this.hasSelect = false;
        this.callback = Ext.emptyFn;
        if (typeof arguments[0].callback == 'function') {
            this.callback = arguments[0].callback;
        }
        this.onHide = Ext.emptyFn;
        if (typeof arguments[0].onHide == 'function') {
            this.onHide = arguments[0].onHide;
        }
        this.loadLpuFrame();
        return true;
	},
    /**
     * Загрузка данных в грид
     */
    loadLpuFrame:function(){
        this.LpuFrame.removeAll();
        var baseParams = {};
        baseParams.start = 0;
        baseParams.limit = 100;
        this.LpuFrame.loadData({
            globalFilters: baseParams
        });
    },
    /**
     * Действия по нажатию кнопки выбор
     */
    doSelect: function(){
        var rec = this.LpuFrame.getGrid().getSelectionModel().getSelected();
        if (!rec || !rec.get('Lpu_id')) {
            this.hasSelect = false;
            this.hide();
            return false;
        }
        this.hasSelect = true;
        this.callback(rec);
        this.hide();
        return true;
    },

    /**
     * Декларируем компоненты формы и создаем форму
     */
	initComponent: function() {
        var thas = this;
        this.LpuFrame = new sw.Promed.ViewFrame({
            id: 'LpuSelectViewFrame',
            actions: [
                {name:'action_add', hidden: true, disabled: true},
                {name:'action_edit', hidden: true, disabled: true},
                {name:'action_view', hidden: true, disabled: true},
                {name:'action_delete', hidden: true, disabled: true},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true},
                {name:'action_resetfilter', hidden: true, disabled: true},
                {name:'action_save', hidden: true, disabled: true}
            ],
            stringfields: [
                { name: 'Lpu_id', type: 'int', header: 'ID', key: true},
                { name: 'Lpu_Name', type: 'string', hidden: true},
                { name: 'Lpu_Nick', type: 'string', header: lang['lpu'], width: 220, sortable: false},
                { name: 'UAddress_Address', type: 'string', header: lang['adres'], autoexpand: true, autoExpandMin: 400, sortable: false}
            ],
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=Common&m=loadLpuSelectList',
            object: 'Lpu',
            layout: 'fit',
            height: 300,
            root: 'data',
            totalProperty: 'totalCount',
            paging: true,
            region: 'center',
            toolbar: false,
            onLoadData: function() {
                //this.getGrid().getStore()
            },
            onDblClick: function() {
                thas.doSelect();
            },
            onEnter: function() {
                thas.doSelect();
            }
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
				text: BTN_FRMCLOSE
			}],
            border: false,
            layout: 'border',
			items: [
                this.LpuFrame
            ]
		});
		sw.Promed.swLpuSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});