/**
* swDirectionIncludeWindow - окно включения назначения в сущетсвующее направление
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      06.05.2016
* @comment      ..
*				
*/
/*NO PARSE JSON*/

sw.Promed.swDirectionIncludeWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swDirectionIncludeWindow',
	objectSrc: '/jscore/Forms/Prescription/swDirectionIncludeWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,	
	formStatus: 'edit',
	title: 'Вопрос',
	id: 'DirectionIncludeWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 145,
			region: 'center',
			items: [{
				html: "<span style='font-size:12px;'>Включить услугу в существующее направление?</span>",
				xtype: 'label'
			}, {
				id: 'DIW_RadioButtonGroupContainer',
				bodyStyle: "padding-top: 5px;",
				items: []
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: 'Продолжить'
			},{
				text: '-'
			},			
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function () {
					this.UslugaComplexPanel.getFirstCombo().focus(true, 250);
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swDirectionIncludeWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	loadMask: null,
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: function() {
		if (this.formStatus != 'save') {
			this.callback({
				include: 'cancel'
			});
		}
	},
	plain: true,
	resizable: false,
	doSave: function() {
		this.formStatus = 'save';

		var EvnDirection_id = this.findById('DIW_RadioButtonGroup').items.items[0].getGroupValue();
		if (EvnDirection_id > 0) {
			// если выбрали направление
			this.callback({
				include: 'yes',
				EvnDirection_id: EvnDirection_id
			});
		} else {
			this.callback({
				include: 'no'
			});
		}
		this.hide();
	},
	show: function() {
		sw.Promed.swDirectionIncludeWindow.superclass.show.apply(this, arguments);

		this.formStatus = 'edit';
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.parentEvnClass_SysNick = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].EvnDirections ) {
			this.EvnDirections = arguments[0].EvnDirections;
		}

		var items = [{
			boxLabel: 'Не включать',
			inputValue: 0,
			name: 'AttributePatient',
			checked: true
		}];

		this.EvnDirections.forEach(function(item) {
			items.push({
				boxLabel: 'Направление № '+item.EvnDirection_Num+' Дата '+item.EvnDirection_setDate+' в службу '+item.MedService_Nick+'',
				inputValue: item.EvnDirection_id,
				name: 'AttributePatient'
			});
		});

		this.findById('DIW_RadioButtonGroupContainer').removeAll();
		this.findById('DIW_RadioButtonGroupContainer').add(
			new Ext.form.RadioGroup({
				id:'DIW_RadioButtonGroup',
				xtype: 'radiogroup',
				columns: 1,
				items: items
			})
		);

		this.doLayout();
		this.syncShadow();
	},
	width: 550
});