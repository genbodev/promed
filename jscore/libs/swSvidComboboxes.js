/**
* swSvidComboboxes - классы ниспадающих списков выбора для мед. свидетельств.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Rustam Salakhov
* @version      29.04.2010
*/

sw.Promed.SwCustomSvidCombo = Ext.extend(sw.Promed.SwBaseLocalCombo, {
	comboSubject: '',
	editable: false,
	initComponent: function() {		
		var combo_subject = this.comboSubject;
		
		alert(combo_subject);
	
		sw.Promed.SwCustomSvidCombo.superclass.initComponent.apply(this, arguments);
		
		this.displayField = combo_subject + '_Name';
		this.fieldLabel = langs('Статус заявки');
		this.id = combo_subject + '_id';
		this.name = combo_subject + '_id';
		this.hiddenName = combo_subject + '_id';
		this.tpl = new Ext.XTemplate(
			'<tpl for="."><div class="x-combo-list-item">',
			'<font color="red">{' + combo_subject + '_Code}</font>&nbsp;{' + combo_subject + '_Name}',
			'</div></tpl>'
		);		
		this.valueField = combo_subject + '_id';
		this.store = new Ext.db.AdapterStore({
			autoLoad: true,
			dbFile: 'Promed.db',
			fields: [
				{ name: combo_subject + '_id', type: 'int' },
				{ name: combo_subject + '_Name', type: 'string' }
			],
			key: combo_subject + '_id',
			sortInfo: {field: combo_subject + '_Code'},
			tableName: combo_subject
		});
	}
});
Ext.reg('swcustomsvidcombo', sw.Promed.SwCustomSvidCombo);
