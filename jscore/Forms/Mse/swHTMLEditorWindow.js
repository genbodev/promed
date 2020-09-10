/**
 * swHTMLEditorWindow - окно редактора HTML
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Mse
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitrii Vlasenko
 * @version			18.08.2017
 */

/*NO PARSE JSON*/

sw.Promed.swHTMLEditorWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swHTMLEditorWindow',
	width: 800,
	maximized: true,
	maximizable: false,
	layout: 'border',
	title: 'Редактор HTML',
	show: function() {
		sw.Promed.swHTMLEditorWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.callback = Ext.emptyFn;
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.action = 'edit';
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		this.Person_id = null;
		if (arguments[0] && arguments[0].Person_id) {
			this.Person_id = arguments[0].Person_id;
		}

		var base_form = win.FormPanel.getForm();
		if (arguments[0] && arguments[0].value) {
			base_form.findField('field').setValue(arguments[0].value);
		}

		win.setTitle('Редактор HTML');
		if (arguments[0] && arguments[0].title) {
			var title = arguments[0].title.replace(/<.*>/gi, ''); // убираем тэги и всё что в тегах жадной регуляркой
			win.setTitle(title);
		}
		
		base_form.findField('field').setDisabled(this.action == 'view');
		this.buttons[0].setVisible(this.action != 'view');
		this.buttons[2].setVisible(this.action == 'view'); // возможно не пригодится, но пока пускай будет для соответствия ТЗ
	},
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			layout: 'border',
			region: 'center',
			frame: true,
			labelAlign: 'right',
			labelWidth: 250,
			items: [{
				region: 'center',
				xtype: 'swhtmleditor',
				getPersonId: function() {
					return win.Person_id;
				},
				name: 'field',
				expandButtonFn: function() {
					win.callback(this.getValue());
					win.hide();
				},
				enableFont: false,
				enableSourceEdit: false,
				listeners: {
					push: function() {
						this.onFirstFocus();
					}
				},
				anchor: '100%',
				fieldLabel: '',
				hideLabel: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					var base_form = win.FormPanel.getForm();
					win.callback(base_form.findField('field').getValue());
					win.hide();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			}, {
				text	  : BTN_FRMCLOSE,
				tooltip   : langs('Закрыть'),
				iconCls   : 'cancel16',
				handler   : function() {
					this.ownerCt.hide();
				}
			}]
		});

		sw.Promed.swHTMLEditorWindow.superclass.initComponent.apply(this, arguments);
	}
});
