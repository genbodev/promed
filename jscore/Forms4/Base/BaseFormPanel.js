/**
 * sw4.BaseFormPanel. Класс базовой формы
 *
 * @author  dimice
 *
 * @class sw4.BaseForm
 * @extends Ext6.window.Window
 */
Ext6.define('base.BaseFormPanel', {
	extend: 'Ext6.panel.Panel',
	alias: 'widget.BaseFormPanel',
	border: false,
	closeAction: 'hide', // просто скрывает форму , а не дестроит ее
	closeOnEsc: false,
	//resizable: false,
	onEsc: function() {
		// по Esc не должны закрываться формы.
		var me = this;
		if (this.closeOnEsc) {
			me.callParent(arguments);
		}
	},
	initComponent: function() {
		var me = this;
		me.addCodeRefresh();
		me.addHelpButton();
		me.callParent(arguments);
	},
	show: function() {
		this.callParent(arguments);

		if (isDebug()) {
			console.group('Форма: %s', this.id);
			console.log('Метод: %s','show()');
			console.log('Аргументы: %o', arguments);
			//console.dir(this);
			console.groupEnd();
		}
		var me = this;
		me.args = arguments;
		if (!me.sprLoaded) {
			// надо прогрузить комбики
			var components = me.query('combobox');
			me.mask('Загрузка локальных справочников');
			me.loadDataLists(components, function () {
				me.unmask();
				me.onSprLoad(me.args);
			});
		} else {
			me.onSprLoad(me.args);
		}
	},
	sprLoaded: false,
	onSprLoad: function(args) {

	},
	setTitle: function (title, iconCls) {
		this.callParent(arguments);

		// обновить надпись на кнопке в таскбаре
		if (this.taskButton) {
			this.taskButton.setButtonText(title);
		}
	},
	addCodeRefresh: function() {
		if (IS_DEBUG)
		{
			if (!this.tools)
			{
				this.tools = [];
			}
			this.tools.push({
				type: 'refresh',
				hidden: (!IS_DEBUG), /*!isAdmin && */
				qtip: 'Обновить функционал формы',
				handler: function(event, toolEl, panel) {
					this.refreshCode();
				}.createDelegate(this)
			});
		}
	},
	addHelpButton: function() {
		if (!this.tools)
		{
			this.tools = [];
		}
		this.tools.push({
			type: 'help',
			qtip: 'Помощь',
			handler: function(event, toolEl, panel) {
				ShowHelp(this.title);
			}.createDelegate(this)
		});
	},
	refreshCode: function() {
		var win = this;
		var className = Ext6.getClassName(win);
		var pathWindow = Ext6.Loader.getPath(className);
		Ext6.undefine(className);
		Ext6.Loader.loadScript({
			url: pathWindow + '?' + Ext6.id(),                    // URL of script
			scope: this,                   // scope of callbacks
			onLoad: function(o) {
				win.hide();
				win.destroy();
			}
		});
	},
	/**
	 *  Загрузка данных справочников, используемых на форме (если ранее не загружены)
	 */
	loadDataLists: function(components, callback) {
		var me = this;

		loadDataLists(me, components, callback);
	},
	getLoadMask: function(message) {
		var win = this;
		return {
			show: function() {
				win.mask(message);
			},
			hide: function() {
				win.unmask();
			}
		};
	}
});