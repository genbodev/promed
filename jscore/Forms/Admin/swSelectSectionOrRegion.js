/**
 * swSelectSectionOrRegion - окно выбора отделения или участка
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 */
/*NO PARSE JSON*/

sw.Promed.swSelectSectionOrRegion = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSelectSectionOrRegion',
	width: 470,
	autoHeight: true,
	modal: true,
	title: '',

	resetFormParams: function() {
		var base_form = this.FormPanel.getForm();
		base_form.reset();
	},

	save: function(){
		var win = this;
		var base_form = win.FormPanel.getForm();
		var lpuCombo = base_form.findField('Lpu_id');
		var lpusectionCombo = base_form.findField('LpuSection_id');
		var lpuregionCombo = base_form.findField('LpuRegion_id');

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					win.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {
			lpu_data: lpuCombo.findRecord('Lpu_id', lpuCombo.getValue()),
			lpusection_data: lpusectionCombo.findRecord('LpuSection_id', lpusectionCombo.getValue()),
			lpuregion_data: lpuregionCombo.findRecord('LpuRegion_id', lpuregionCombo.getValue())
		}
		if(this.callback) this.callback(params);
		this.hide();
	},

	show: function() {
		sw.Promed.swSelectSectionOrRegion.superclass.show.apply(this, arguments);

		this.action = null;
		this.mode = null;
		this.callback = null;
		var base_form = this.FormPanel.getForm();
		var LpuSectionCombo = base_form.findField('LpuSection_id');
		var LpuRegionCombo = base_form.findField('LpuRegion_id');
		var title = '';
		if (arguments[0].action && arguments[0].mode) {
			this.action = arguments[0].action;
			this.mode = arguments[0].mode;
			if(this.mode == 'section') {
				title = 'Выбор отделения';
				LpuSectionCombo.setAllowBlank(false);
				LpuRegionCombo.setAllowBlank(true);
				LpuRegionCombo.hideContainer();
			}else if(this.mode == 'region'){
				title = 'Выбор участка';
				LpuSectionCombo.setAllowBlank(true);
				LpuRegionCombo.setAllowBlank(false);
				LpuRegionCombo.showContainer();
			}
			this.setTitle(title);
		}
		if(!title){
			Ext.Msg.alert(lang['soobschenie'], 'Переданы не верные параметры');
			this.hide();
		}

		if (arguments[0].callback && typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		}

		this.resetFormParams();
		this.loadLpu();
	},

	loadLpu: function(){
		var base_form = this.FormPanel.getForm();
		base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);		
		this.loadLpuSection();
	},

	loadLpuSection: function(){
		var win = this;
		var base_form = this.FormPanel.getForm();
		var lpu_id = base_form.findField('Lpu_id').getValue();
		base_form.findField('LpuSection_id').setValue();
		base_form.findField('LpuSection_id').getStore().load({
			params: {
				Lpu_id: lpu_id,
				mode: 'combo'
			},
			callback: function(){
				win.loadLpuRegion();
				// loadMask.hide();
			}}
		);
	},

	loadLpuRegion: function(){
		var base_form = this.FormPanel.getForm();
		var lpu_id = base_form.findField('Lpu_id').getValue();
		var lpusection_id = base_form.findField('LpuSection_id').getValue();
		var params = {Lpu_id: lpu_id};
		if(lpusection_id) params.LpuSection_id = lpusection_id;
		base_form.findField('LpuRegion_id').setValue();
		base_form.findField('LpuRegion_id').getStore().load({
			params: params
		});
	},

	initComponent: function() {
		var win = this;
		this.FormPanel = new Ext.FormPanel({
			id: 'EPMEW_FormPanel',
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			bodyStyle: 'margin-top: 10px;',
			items: [
				{
					xtype: 'swlpucombo',
					hiddenName: 'Lpu_id',
					fieldLabel: 'МО',
					disabled: !(isSuperAdmin()),
					allowBlank:false,
					width: 280,
					listeners:{
						'select':function (combo) {
							win.loadLpuSection();
						}.createDelegate(this),
					}
				}, {
					width: 280,
					// allowBlank:false,
					name: 'LpuSection_id',
					fieldLabel: lang['otdelenie'],
					lastQuery:'',
					listeners:{
						'select':function (combo) {
							win.loadLpuRegion();
						}.createDelegate(this),
						'change':function (combo, newValue, oldValue) {
							// ...
						}.createDelegate(this)
					},
					xtype: 'swlpusectionglobalcombo',
				}, {
					displayField: 'LpuRegion_Name',
					editable: true,
					fieldLabel: lang['uchastok'],
					forceSelection: true,
					typeAhead: true,
					hiddenName: 'LpuRegion_id',
					triggerAction: 'all',
					valueField: 'LpuRegion_id',
					width: 280,
					xtype: 'swlpuregioncombo'
				}
			]
		});

		Ext.apply(this,
			{
				buttons: [
					{
						handler: function () {
							this.save();
						}.createDelegate(this),
						iconCls: 'save16',
						text: langs('Сохранить')
					},
					{
						text: '-'
					},
					HelpButton(this),
					{
						handler: function()
						{
							this.hide();
						}.createDelegate(this),
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}
				],
				items: [
					this.FormPanel
				]
			});

		sw.Promed.swSelectSectionOrRegion.superclass.initComponent.apply(this, arguments);
	}
});