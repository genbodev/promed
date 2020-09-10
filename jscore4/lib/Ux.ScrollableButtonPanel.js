/* 
 * Ux.ScrollableButtonPanel
экспериментальный компонент для контейнера кнопок с возможностью прокрутки
 */

Ext.define('sw.ScrollableButtonPanel', {
    extend: 'Ext.tab.Bar',
    alias: 'widget.scrollpanel',
	orientation: 'vertical',
	cls: 'verticalScrollableButtonPanel',
	renderTpl: [
        '<div id="{id}-body" role="presentation" class="{baseCls}-body {bodyCls} {bodyTargetCls}{childElCls}',
            '<tpl if="ui"> {baseCls}-body-{ui}',
                '<tpl for="uiCls"> {parent.baseCls}-body-{parent.ui}-{.}</tpl>',
            '</tpl>"<tpl if="bodyStyle"> style="{bodyStyle} "</tpl>>',
            '{%this.renderContainer(out,values)%}',
        '</div>'
    ]		
})
