/**
* swSelectDirFailTypeWindow - окно с выбором причины отмены направления
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      19.05.2009
*/

/**
 * swSelectDirFailTypeWindow - окно выбора ЛПУ, в случае если человек прикреплен к нескольким ЛПУ
 *
 * @class sw.Promed.swSelectDirFailTypeWindow
 * @extends Ext.Window
 */
sw.Promed.swSelectDirFailTypeWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	border: false,
	closable: false,
	closeAction:'hide',
	time_id: null,
	/**
	 * Функция вызывающаяся после успешной отмены направления
	 */
	onClear: Ext.emptyFn, 
	/**
	 * Запрос к серверу после выбора ЛПУ
	 */
	submit: function() {
		var win = this;
		var form = this.findById('SelectDirFailTypeForm');
		var base_form = form.getForm();
		
		
		if (!form.getForm().isValid()) {
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'],
					lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		
		if (typeof this.onSelectValue == 'function') {
			this.onSelectValue({
				DirFailType_id: base_form.findField('DirFailType_id').getValue(), 
				EvnComment_Comment: base_form.findField('EvnComment_Comment').getValue()
			});
			this.hide();
		} else {
			win.getLoadMask(lang['otmena_napravleniya']).show();
			submitClearTime(
				{
					id: this.time_id,
					cancelType: this.cancelType,
					type: this.LpuUnitType_SysNick,
					DirFailType_id: base_form.findField('DirFailType_id').getValue(),
					EvnComment_Comment: base_form.findField('EvnComment_Comment').getValue()
				},
				function(response) {
					win.getLoadMask().hide();
					if (response.responseText)
					{
						var answer = Ext.util.JSON.decode(response.responseText);
						if (!answer.success)
						{
							if (answer.Error_Code)
							{
								Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
							}
							else
								if (!answer.Error_Msg) 
								{
									Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_osvobojdenie_priema_nevozmojno']);
								}
						}
						else
						{
							this.onClear();
						}
					}
					else
					{
						Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_otsutstvuet_otvet_servera']);
					}
					this.hide();
				}.createDelegate(this),
				function() {
					win.getLoadMask().hide();
				}.createDelegate(this)
			);
		}
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			border: false,
			frame: true,
			id : 'SelectDirFailTypeForm',
			labelAlign: 'top',
			labelWidth: 140,
			style: 'padding: 10px',
			items : [{
				allowBlank: false,
				comboSubject: 'DirFailType',
				fieldLabel: lang['prichina_otmenyi_napravleniya'],
				typeCode: 'int',
				sortField: 'DirFailType_Name',
				width: 450,
				codeField: false,
				onSelect : function(record, index){
					if(this.fireEvent('beforeselect', this, record, index) !== false){
						this.setValue(record.data[this.valueField || this.displayField]);
						this.collapse();
					}
				},
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{DirFailType_Name}&nbsp;',
					'</div></tpl>'
				),
				onLoadStore: function() {
					win.filterDirFailType();
				},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnComment_Comment',
				width: 450,
				xtype: 'textarea'
			}],
			url : ''
		});
		
    	Ext.apply(this, {
			buttonAlign: "right",
			buttons: [
				{
					text : lang['vyibrat'],
					iconCls : 'ok16',
					handler : function(button, event) {
						this.submit();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this),
				{
					text : lang['otmenit'],
					iconCls : 'cancel16',
					handler : function(button, event) {
						this.hide();
					}.createDelegate(this)
				}
			],
			items : [
				win.FormPanel
			]
		});
		sw.Promed.swSelectDirFailTypeWindow.superclass.initComponent.apply(this, arguments);
	},
	modal: true,
	plain: false,
	resizable: false,
	formType: 'polka',
	filterDirFailType: function() {
		var win = this;
		var base_form = this.findById('SelectDirFailTypeForm').getForm();
		
		base_form.findField('DirFailType_id').getStore().clearFilter();
		base_form.findField('DirFailType_id').lastQuery = '';
		base_form.findField('DirFailType_id').getStore().filterBy(function(rec){
			var flag = true;
			
			if (win.formType.inlist(['labdiag'])) {
				flag = rec.get('DirFailType_Code').inlist([9,10,11,12]);
			}
			
			if (win.formType.inlist(['funcdiag'])) {
				flag = rec.get('DirFailType_Code').inlist([5,13,14,15,16]);
			}
			
			if (win.formType.inlist(['polka'])) {
				flag = rec.get('DirFailType_Code') < 9;
			}
			
			return flag;
		});
	},
	show: function() {
		sw.Promed.swSelectDirFailTypeWindow.superclass.show.apply(this, arguments);

        this.time_id = arguments[0].time_id || null;
        this.cancelType = arguments[0].cancelType || 'cancel';
        this.LpuUnitType_SysNick = arguments[0].LpuUnitType_SysNick || 'polka';
        this.formType = arguments[0].formType || 'polka';
        this.onClear = (typeof arguments[0].onClear == 'function') ? arguments[0].onClear : Ext.emptyFn;
        this.onSelectValue = (typeof arguments[0].onSelectValue == 'function') ? arguments[0].onSelectValue : null;

		var base_form = this.findById('SelectDirFailTypeForm').getForm();
		base_form.reset();
		
		this.filterDirFailType();
		
		base_form.findField('DirFailType_id').focus(true, 250);
	}, //end show()
	title: lang['vyibor_prichinyi_otmenyi_napravleniya'],
	width: 500
});