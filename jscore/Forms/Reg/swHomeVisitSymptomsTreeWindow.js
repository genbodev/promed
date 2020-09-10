/**
* swHomeVisitSymptomsTreeWindow - окно отображения дерева симптовом
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      22.08.2014
*/

sw.Promed.swHomeVisitSymptomsTreeWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 550,
	id: 'HomeVisitSymptomsTreeWindow',

	/**
	 * Возвращает строку выбранных симптомов
	 */
	getSymptomsString: function(callProf) {
		var text = '';
		var cssClass = '.symptoms';
		if(callProf == 2){
			cssClass = '.symptoms.stom';
		} else {
			cssClass = '.symptoms.ther';
		}
		$(cssClass).children().find('input:checked').next().each(
			function() {
				text += $(this).text() + ', ';
			}
		);
		text = text.substring(0, text.length - 2);
		return text;
	},
	
	/**
	 * Возвращает массив с идентификаторами выбранных синмпотов
	 */
	getSymptomsArray: function(callProf) {
		var arr = [];
		var cssClass = '.symptoms';
		if(callProf == 2){
			cssClass = '.symptoms.stom';
		} else {
			cssClass = '.symptoms.ther';
		}
		$(cssClass).children().find('input:checked').each(
			function() {
				arr.push($(this).val());
			}
		);
		return arr;
	},
	
	initComponent: function() {

		this.MainPanel = new Ext.Panel({
			autoScroll: true,
			height : 185,
			layout : 'fit',
			border : false,
			frame : true,
			style : 'padding: 10px',
			labelWidth : 100,
			url : C_HOMEVISIT_SYMPTOMS_TREE
			
		});
		Ext.apply(this, {
			buttons: [
				{
					text: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.returnFunc();
						this.hide();
					}.createDelegate(this)
				},
				{
					text:'-'
				}, 
				HelpButton(this, TABINDEX_HVSTW + 1),
				{
					handler: function() {
						this.ownerCt.returnFunc();
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					tabIndex: TABINDEX_HVSTW + 2,
					text: BTN_FRMCLOSE
				}
			],
			items: [
				this.MainPanel
			]
		});
		sw.Promed.swHomeVisitSymptomsTreeWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'fit',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	minHeight: 550,
	minWidth: 450,
	modal: true,
	plain: true,
	resizable: false,
	returnFunc: Ext.emptyFn,
	show: function() {
		sw.Promed.swHomeVisitSymptomsTreeWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;

		if (arguments[0].callback) {
			this.returnFunc = arguments[0].callback;
		}

		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		if (arguments[0].callProf) {
			this.callProf = arguments[0].callProf;
		}

		this.restore();
		this.center();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		//loadMask.show();
		if(this.callProf == 2){
			var callProfClassShow = '.stom';
			var callProfClassHide = '.ther';
		} else {
			var callProfClassShow = '.ther';
			var callProfClassHide = '.stom';
		}

		if (arguments[0].firsttime) {
			this.MainPanel.load({
				url: C_HOMEVISIT_SYMPTOMS_TREE,
				params: {
				},
				success: function (form, action)
				{
					loadMask.hide();
				},
				failure: function (form, action)
				{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				},
				scripts:true
			});
			setTimeout(function(){$(callProfClassHide).hide();},1000);
		} else {
			$(callProfClassHide).hide();
			$(callProfClassShow).show();
		}
	},
	title: lang['simptomyi'],
	width: 450
});
