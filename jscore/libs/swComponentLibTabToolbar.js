/**
* swComponentLibTabToolbar - класс тулбара с закладками.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/projects/promed
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Alexander Arefyev aka Alf (avaref@gmail.com)
* @version      24.08.2010
*/

Ext.ns('Ext.ux');

/**
* swTabToolbar - класс тулбара с закладками
*
* @access public
* @author Александр Арефьев (based on Ext JS 3.0 Community Forums)
* @xtype 'tabtoolbar'
*/

sw.Promed.swTabToolbar = Ext.extend(Ext.TabPanel, {
    activeTab: 0,
    frame: true,
    plain: false,
    border: false,
    layoutOnTabChange: true,
    enableTabScroll: true,
    defaultType: 'panel',
    style: 'border: none',

    /**
     * Constructor.
     * @param {Array} config Массив конфигурации меню для каждой закладки (title: 'имя закладки', items:[кнопки тулбара])
     */
    constructor: function(config) {
        config = [].concat(config);
        Ext.each(config, function(c) {
            Ext.apply(c, {
                frame: true,
                border: false,
                xtype: 'toolbar',
                style: 'border: none',
                cls: 'x-toolbar',
                autoHeight: true,
                defaultType: 'button'
            });
        });
        this.cls = "x-tab-toolbar";
        this.items = config;
        sw.Promed.swTabToolbar.superclass.constructor.call(this);
    }
});

Ext.reg("tabtoolbar", sw.Promed.swTabToolbar);
