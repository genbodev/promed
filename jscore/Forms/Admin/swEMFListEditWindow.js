/**
* swEMFListEditWindow - (EMF - EvnMediaFiles) окно редактирования/добавления прикрепленных файлов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      25.03.2011
*/
/*NO PARSE JSON*/
sw.Promed.swEMFListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEMFListEditWindow',
	objectSrc: '/jscore/Forms/Admin/swEMFListEditWindow.js',
	height: 272,
	width: 700,
	border: false,
	modal: false,
	plain: false,
	collapsible: false,
	resizable: false,
	maximizable: false,
	bodyStyle: 'padding: 0px',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onSave: Ext.emptyFn,
	closeAction: 'hide',
	draggable: false,
	id: 'EMFListEditWindow',
	title: lang['prikreplennyie_faylyi'],
	saveParams: null,
	Evn_id: null,
	EvnXml_id: null,
	filterType: null,
	saveOnce: false,
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	initComponent: function() {
		var th = this;
		
		this.filesPanel = new sw.Promed.FileList({
			filterType: null,
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=remove'
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					th.filesPanel.saveChanges(function(rec){
						th.onSave();
					});
				},
				iconCls: 'save16',
				tabIndex: 10001,
				text: lang['sohranit']
			}, {
				handler: function() {
					var rec = th.filesPanel.FileGrid.getGrid().getSelectionModel().getSelected();
					if(!rec) {
						sw.swMsg.alert(lang['soobschenie'], lang['vyi_ne_vyibrali_fayl']);
						return false;
					}
					if(this.saveOnce) {
						th.onSelect(rec);
						th.hide();
					} else {
						if(rec.data.state != 'saved') {
							sw.swMsg.alert(lang['soobschenie'], lang['vyi_ne_sohranili_izmeneniya']);
							return false;
						}
						th.onSelect(rec);
						th.hide();
					}
				},
				iconCls: 'ok16',
				tabIndex: 10002,
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {					
					th.hide();
				},
				tabIndex: 10003,
				text: BTN_FRMCANCEL
			}],
			keys: [{
				fn: function(inp, e) {
					th.hide();
				},
				key: [
					Ext.EventObject.ESC
				],
				stopEvent: true
			}],
			items: [
				this.filesPanel
			]
		});
		sw.Promed.swEMFListEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swEMFListEditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0]) {
			arguments = [{}];
		}
		this.Evn_id = arguments[0].Evn_id || null;
		this.EvnXml_id = arguments[0].EvnXml_id || null;
		this.filterType = arguments[0].filterType || null;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.onSave = arguments[0].onSave || Ext.emptyFn;
		this.onSelect = arguments[0].onSelect || Ext.emptyFn;
		this.isSelect = (arguments[0].onSelect) ? true : false;
		this.saveOnce = arguments[0].saveOnce || false;
		
		this.buttons[0].setVisible(this.saveOnce == false);
		this.buttons[1].setVisible(this.isSelect);
		
		//загружаем файлы
		this.filesPanel.saveOnce = this.saveOnce;
		this.filesPanel.filterType = this.filterType;
		this.filesPanel.listParams = {
			Evn_id: this.Evn_id,
			EvnXml_id: this.EvnXml_id,
			filterType: this.filterType
		};		
		this.filesPanel.loadData({
			Evn_id: this.Evn_id,
			EvnXml_id: this.EvnXml_id,
			filterType: this.filterType
		});
	}
});