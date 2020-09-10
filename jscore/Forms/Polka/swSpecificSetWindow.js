/**
* swSpecificSetWindow - окно выбора типа добавляемой специфики.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-24.11.2009
* @comment      Префикс для id компонентов SSW (SpecificSetWindow)
*/

sw.Promed.swSpecificSetWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'SpecificSetWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.openSpecificEditWindow();
				},
				iconCls: 'ok16',
				text: lang['ok']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.Panel({
				border: false,
				frame: true,
				id: 'SpecificSetForm',
				items: [ new sw.Promed.SwSpecificTypeCombo({
					hiddenName: 'SpecificType_id',
					id: 'SSW_SpecificTypeCombo',
					lastQuery: '',
					width: 300
				})],
				labelAlign: 'right',
				labelWidth: 150,
				layout: 'form'
			})]
		});
		sw.Promed.swSpecificSetWindow.superclass.initComponent.apply(this, arguments);
	},
	maximizable: false,
	modal: true,
	openSpecificEditWindow: function() {
		var current_window = this;
		var specific_window = null;

		var params = current_window.params;
		var specific_type_combo = current_window.findById('SSW_SpecificTypeCombo');
		var specific_type_id = specific_type_combo.getValue();

		if ( specific_type_id == null || specific_type_id.toString().length == 0 ) {
			return false;
		}

		var current_record = specific_type_combo.getStore().getById(specific_type_id);

		if ( !current_record ) {
			return false;
		}

		switch ( current_record.get('SpecificType_Code') ) {
			case 1:
				params.formParams.EvnPLAbort_id = 0;
				break;
		}

		getWnd('swEvnPLAbortEditWindow').show(params);

		current_window.hide();
	},
	params: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swSpecificSetWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.params = null;

		if ( !arguments[0] || !arguments[0].params || !arguments[0].specificList ) {
			sw.swMsg.alert(lang['soobschenie'], lang['neverno_zadanyi_parametryi'], function() { current_window.hide(); } );
			return false;
		}

		current_window.params = arguments[0].params;

		var specific_list = arguments[0].specificList;
		var specific_type_combo = current_window.findById('SSW_SpecificTypeCombo');

		if ( specific_list.length == 0 ) {
			sw.swMsg.alert(lang['soobschenie'], lang['vvod_spetsifiki_nedostupen'], function() { current_window.hide(); } );
			return false;
		}

		var where = 'where SpecificType_id in (' + specific_list.join(', ') + ')';

		specific_type_combo.clearValue();
		specific_type_combo.getStore().removeAll();

		// Здесь должен быть фильтр на доступные типы специфики
		specific_type_combo.getStore().load({
			where: where
		})

		specific_type_combo.focus(true, 250);
	},
	title: WND_POL_SPECSETTYPE,
	width: 500
});