Ext6.define('common.XmlTemplate.EditorSpecMarkerNotice', {
	extend: 'Ext6.Component',
	xtype: 'xmltemplatespecmarkernotice',
	baseCls: 'sw-editor-notice',

	buttonHandler: function() {},

	renderTpl: [
		'<span id="{id}-icon" class="{baseCls}-icon icon-notice"></span>',
		'<span id="{id}-text" class="{baseCls}-text">Чтобы увидеть добавленные назначения, обновите спецмаркеры.</span>',
		'<span id="{id}-btn" class="{baseCls}-btn">Обновить осмотр</span>',
	],

	getButtonEl: function() {
		var me = this;
		return me.el.down('#'+me.id+'-btn');
	},

	afterRender: function() {
		var me = this;
		me.callParent(arguments);
		me.getButtonEl().on('click', me.buttonHandler, me);
	}
});