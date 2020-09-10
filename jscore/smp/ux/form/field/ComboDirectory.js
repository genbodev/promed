/**
 * Базовый компонент комбобокса для СМП
 */
Ext6.define('smp.ux.form.field.ComboDirectory', {
	extend: 'smp.ux.form.field.ComboBase',
	alias: 'widget.comboDirectory',
	requires: [
		'smp.ux.form.field.ComboBase'
	],
	codeField: null,
	/**
	 * Использовать шаблон справочника?
	 * В случае true параметру displayTpl будет присвоен шаблон
	 * для справочников по умолчанию
	 */
	useDirectoryDisplayTpl: true,
	/*
	tpl: Ext6.create('Ext.XTemplate',
        '<tpl for=".">',
            '<li class="x6-boundlist-item"><span style="color: red;">{CmpReason_Code}</span>. {CmpReason_Name} </li>',
        '</tpl>'
    ),
    displayTpl: Ext6.create('Ext.XTemplate',
        '<tpl for=".">',
            '{CmpReason_Code}. ({CmpReason_Name})',
        '</tpl>'
    ),
    */
	initComponent: function () {
		if (!this.tpl) {
			var codeFieldPartTpl = this.codeField ? '<span style="color: red;">{' + this.codeField + '}</span>. ' : '';
			this.tpl = Ext6.create('Ext.XTemplate',''
				+ '<tpl for=".">'
					+ '<li class="x6-boundlist-item">'
						+ codeFieldPartTpl
						+ '{' + this.displayField + '}'
					+ '</li>'
				+ '</tpl>')
		}
		if (this.useDirectoryDisplayTpl) {
			var codeFieldPartTpl = this.codeField ? '{' + this.codeField + '}. ' : '';
			this.displayTpl =  Ext6.create('Ext.XTemplate',''
				+ '<tpl for=".">'
					+ codeFieldPartTpl
					+ '{' + this.displayField + '}'
				+ '</tpl>')
		}
		/*
		if (!this.tpl) {
			var codeFieldPartTpl = this.codeField ? '<span style="color: red;">{' + this.codeField + '}</span>. ' : '';
			this.tpl = ''
				+ '<tpl for=".">'
					+ '<div class="x4-boundlist-item">'
						+ codeFieldPartTpl
						+ '{' + this.displayField + '}'
					+ '</div>'
				+ '</tpl>';
		}
		
		if (this.useDirectoryDisplayTpl) {
			var codeFieldPartTpl = this.codeField ? '{' + this.codeField + '}. ' : '';
			this.displayTpl = ''
				+ '<tpl for=".">'
					+ codeFieldPartTpl
					+ '{' + this.displayField + '}'
				+ '</tpl>';
		}
		*/
		this.callParent(arguments);
	}
});