/**
* swReagentModelEditWindow - окно просмотра, добавления и редактирования реактива модели анализатора
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package	  common
* @access	   public
* @author	   Arslanov Azat
*/

/*NO PARSE JSON*/
sw.Promed.swReagentModelEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swReagentModelEditWindow',
	objectSrc: '/jscore/Forms/Common/swReagentModelEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	title: 'Реактив модели анализатора',
	draggable: true,
	id: 'swReagentModelEditWindow',
	width: 600,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,
	doSave: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			return;
		}
		win.getLoadMask('Подождите, сохраняется запись...').show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();				
				win.action = 'edit';
				var data = {};
				var id = action.result.ReagentModel_id;
				form.findField('ReagentModel_id').setValue(id);
				data.ReagentModel_id = id;
				win.owner.refreshRecords(win.owner, id);
				win.hide();
			}
		});
	},
	initComponent: function() {
		var win = this;
		
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			labelAlign: 'right',
			labelWidth: 120,
			region: 'north',
			items: [{
				xtype: 'swdrugsimplecombo',
				fieldLabel : 'Реактив',
				width: 400,
				listWidth: 500,
				hiddenName: 'DrugNomen_id',
				value: '',
				allowBlank: false,
				triggerAction: 'all',
				trigger2Class: 'hideTrigger',
				//hideTrigger: true,
				listeners: {
					'render': function() {
						this.getStore().proxy.conn.url = '/?c=ReagentModel&m=loadReagentList';
					}
				}
			}, {
				name: 'ReagentModel_id',
				xtype: 'hidden'
			}, {
				name: 'AnalyzerModel_id',
				xtype: 'hidden'
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())  {
						case Ext.EventObject.C:
							if (this.action != 'view') {
								this.doSave();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { 
					//
				}
			}, 
			[
				{ name: 'ReagentModel_id' },
				{ name: 'DrugNomen_id' },
				{ name: 'AnalyzerModel_id' }
			]),
			timeout: 600,
			url: '/?c=ReagentModel&m=saveReagentModel'
		});		

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_GL + 29,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				},
				onTabElement: 'GREW_Marker_Word',
				tabIndex: TABINDEX_GL + 31,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swReagentModelEditWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swReagentModelEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;		

		this.center();

		var win = this,
		base_form = this.formPanel.getForm(); 

		base_form.reset();
		base_form.setValues(arguments[0]);
		
		switch (this.action) {
			case 'view':
				this.setTitle('Реактив: Просмотр');
				break;
			case 'edit':
				this.setTitle('Реактив: Редактирование');
				break;
			case 'add':
				this.setTitle('Реактив: Добавление');
				break;
			break;
		}

		if (this.action == 'add') {
			win.enableEdit(true);
			this.syncSize();
			this.doLayout();
		} else {
			win.enableEdit(false);
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() { win.hide(); } );
				},
				params: {
					ReagentModel_id: base_form.findField('ReagentModel_id').getValue()
				},
				success: function(form, action) {					
					win.getLoadMask().hide();
					if(win.action == 'edit') {
						win.enableEdit(true);
					}
				},
				url: '/?c=ReagentModel&m=loadReagentModel'
			});
		}
	}
});
