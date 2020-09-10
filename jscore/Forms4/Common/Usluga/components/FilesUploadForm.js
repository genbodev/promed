Ext6.define('usluga.components.FilesUploadForm', {
	extend: 'Ext6.form.FormPanel',
	alias: 'widget.FilesUploadForm',
	userCls: 'new-file-upload-form',
	viewModel: {
		data: {
			editable: true
		}
	},
	border: false,
	items: [
		{
			xtype: 'MultipleFileField',
			name: 'userfile[]',
			bind: {
				disabled: '{editable === false}'
			},
			buttonOnly: true,
			buttonConfig: {
				text: langs('Добавить файл'),
				ui: 'plain',
				cls: 'simple-button-link'
			},
			listeners: {
				change: 'uploadFiles'
			}
		}
	]
});