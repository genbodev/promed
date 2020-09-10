/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 17.02.15
 * Time: 15:52
 * To change this template use File | Settings | File Templates.
 */
/**
 * swPersonCardPrintDialogWindowKz - диологовое окно для заявления о выбре МО, согласия/отказа от мед. вмешательств
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Polka
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.07.2014
 */

/*NO PARSE JSON*/

sw.Promed.swPersonCardPrintDialogWindowKz = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonCardPrintDialogWindowKz',
	width: 280,
	autoHeight: true,
	//autoWidth: true,
	modal: true,
	buttonAlign: 'center',
	title: lang['vyibor_vyirianta_pechati_zayavleniya'],

	print: function(type) {
		if (!type.inlist(['personally', 'deputy'])) {return false;}

		var base_form = this.FormPanel.getForm();
		var valid = base_form.isValid();
		if(valid){
			var params = this.params;
			Ext.Ajax.request({
				url: '/?c=PersonCard&m=printPersonCardAttachKz',
				params: {
					Person_id: params.Person_id,
					Server_id: 0,//params.Server_id,
					PCPDW_Deputy_id: this.findById('PCPDW_Deputy_id').getValue()
				},
				callback: function(o, s, r) {
					if( s ) {
						if( /<html>/g.test(r.responseText) ) {
							openNewWindow(r.responseText);
						}

					}
				}.createDelegate(this)
			});
			this.hide();
		}
		else{
			this.showMessage(lang['soobschenie'], lang['ne_vse_polya_formyi_zapolnenyi_korrektno']);
		}
	},

	showMessage: function(title, message, fn) {
		if ( !fn )
			fn = function(){};
		Ext.MessageBox.show({
			buttons: Ext.Msg.OK,
			fn: fn,
			icon: Ext.Msg.WARNING,
			msg: message,
			title: title
		});
	},

	show: function() {
		sw.Promed.swPersonCardPrintDialogWindowKz.superclass.show.apply(this, arguments);

		this.params = null;
		var base_form = this.FormPanel.getForm();
		if (!arguments[0].params) {
			this.showMessage(lang['oshibka'], lang['ne_byili_peredanyi_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		this.findById('PCPDW_Deputy_id').setAllowBlank(true);
		this.findById('PCPDW_Deputy_id').hide();
		this.params = arguments[0].params;

		base_form.reset();
		this.syncShadow();
	},

	initComponent: function() {
		var form = this;
		this.TextPanel = new Ext.Panel({
			autoHeight: true,
			border: false,
			style: 'margin-bottom: 5px;',
			id: 'PCPDW_TextPanel'
		});
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'background: #c6d4e6; padding: 5px 5px 0;',
			defaults: {bodyStyle: 'background: #c6d4e6;'},
			border: false,
			id: 'PCPDW_FormPanel',
			labelAlign: 'right',
			labelWidth: 90,
			items: [
				this.TextPanel,
				{
					xtype: 'radio',
					hideLabel: true,
					boxLabel: lang['lichno'],
					inputValue: 0,
					id: 'rxw_radio_useexist',
					name: 'exporttype',
					checked: true,
					listeners:{
						'check': function(radio,checked,c){
							if(checked){
								form.findById('PCPDW_Deputy_id').hide();
								form.findById('PCPDW_Deputy_id').setAllowBlank(true);
							}
						}
					}
				}, {
					xtype: 'radio',
					hideLabel: true,
					boxLabel: lang['predstavitel'],
					inputValue: 1,
					id: 'rxw_radio_usenew',
					name: 'exporttype',
					listeners:{
						'check': function(radio,checked,c){
							if(checked){
								form.findById('PCPDW_Deputy_id').show();
								form.findById('PCPDW_Deputy_id').setAllowBlank(false);
							}
						}
					}
				},
				{
					editable: false,
					hidden:true,
					id: 'PCPDW_Deputy_id',
					tabIndex: TABINDEX_PEF + 29,
					width: 250,
					allowBlank: true,
					hideLabel: true,
					xtype: 'swpersoncombo',
					onTrigger1Click: function() {
						var ownerWindow = Ext.getCmp('PersonEditWindow');
						var combo = this;

						var
							autoSearch = false,
							fio = new Array();

						if ( !Ext.isEmpty(combo.getRawValue()) ) {
							fio = combo.getRawValue().split(' ');

							// Запускать поиск автоматически, если заданы хотя бы фамилия и имя
							if ( !Ext.isEmpty(fio[0]) && !Ext.isEmpty(fio[1]) ) {
								autoSearch = true;
							}
						}
						getWnd('swPersonSearchWindow').show({
							autoSearch: autoSearch,
							onSelect: function(personData) {
								if ( personData.Person_id > 0 )
								{
									PersonSurName_SurName = Ext.isEmpty(personData.PersonSurName_SurName)?'':personData.PersonSurName_SurName;
									PersonFirName_FirName = Ext.isEmpty(personData.PersonFirName_FirName)?'':personData.PersonFirName_FirName;
									PersonSecName_SecName = Ext.isEmpty(personData.PersonSecName_SecName)?'':personData.PersonSecName_SecName;

									combo.getStore().loadData([{
										Person_id: personData.Person_id,
										Person_Fio: PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
									}]);
									combo.setValue(personData.Person_id);
									combo.collapse();
									combo.focus(true, 500);
									combo.fireEvent('change', combo);
								}
								getWnd('swPersonSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 500)},
							personSurname: !Ext.isEmpty(fio[0]) ? fio[0] : '',
							personFirname: !Ext.isEmpty(fio[1]) ? fio[1] : '',
							personSecname: !Ext.isEmpty(fio[2]) ? fio[2] : ''
						});
					},
					enableKeyEvents: true,
					listeners: {
						'change': function(combo) {
						},
						'keydown': function( inp, e ) {
							if ( e.F4 == e.getKey() )
							{
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;
								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;
								e.browserEvent.returnValue = false;
								e.returnValue = false;
								if ( Ext.isIE )
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								inp.onTrigger1Click();
								return false;
							}
						},
						'keyup': function(inp, e) {
							if ( e.F4 == e.getKey() )
							{
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;
								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;
								e.browserEvent.returnValue = false;
								e.returnValue = false;
								if ( Ext.isIE )
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								return false;
							}
						}
					}
				}
			]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					handler: function () {
						this.print('personally');
					}.createDelegate(this),
					id: 'PCPDW_PrintPerson',
					text: lang['pechat_zayavleniya_o_vyibore_mo']
				},{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					id: 'PCPDW_CancelButton',
					text: lang['otmena']
				}]
		});

		sw.Promed.swPersonCardPrintDialogWindowKz.superclass.initComponent.apply(this, arguments);
	}
});