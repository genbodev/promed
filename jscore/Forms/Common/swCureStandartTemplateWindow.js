/**
* swCureStandartTemplateWindow 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Aleksandr Permyakov (alexpm)
* @version      03 2012
* @comment      tabIndex: 
*/

/*NO PARSE JSON*/

sw.Promed.swCureStandartTemplateWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swCureStandartTemplateWindow',
	objectSrc: '/jscore/Forms/Common/swCureStandartTemplateWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['vyibor_naznacheniy_po_standartu'],
	draggable: false,
	id: 'swCureStandartTemplateWindow',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	autoScroll: true,
	cls: 'CureStandartTemplateWindow_additionalCss',
    sectionList: [
        {name: 'FuncDiagData',item: null},
        {name: 'LabDiagData',item: 'LabItemDiagData'},
        {name: 'OperData',item: null},
        {name: 'ProcData',item: null},
        {name: 'FuncTreatmentData',item: null},
        {name: 'LabTreatmentData',item: 'LabItemTreatmentData'},
        {name: 'DrugData',item: null}
    ],
	doReset: function() {
		var tpl = new Ext.XTemplate(' ');
		tpl.overwrite(this.mainPanel.body, {});
	},
	doSave: function() {
		if(this.checkboxes.length == 0) {
			return false;
		}
		var item_checked=false,
			i,
			checkbox,
			oper=[],
			proc=[],
			funcdiag=[],
			drug=[],
			labdiag={};
		for(i=0;i<this.checkboxes.length;i++) {
			checkbox = this.checkboxes[i];
			if( checkbox.checked && checkbox.prescCode.inlist(['FuncDiagData','FuncTreatmentData']) ) {
				funcdiag.push(checkbox.prescId); // UslugaComplex_id
				item_checked=true;
			}
			if(checkbox.checked && checkbox.prescCode=='OperData') {
				oper.push(checkbox.prescId); // UslugaComplex_id
				item_checked=true;
			}
			if(checkbox.checked && checkbox.prescCode=='ProcData') {
				proc.push(checkbox.prescId); // UslugaComplex_id
				item_checked=true;
			}
			if(checkbox.checked && checkbox.prescCode=='DrugData') {
				drug.push(checkbox.prescId); // ActMatters_id
				item_checked=true;
			}
			if( checkbox.checked && checkbox.prescCode.inlist(['LabDiagData','LabTreatmentData']) ) {
				if(!checkbox.is_complex) {
					labdiag[checkbox.prescId]=[checkbox.prescId]; // UslugaComplex_id
					item_checked=true;
				}
			}
		}
		for(i=0;i<this.checkboxes.length;i++) {
			checkbox = this.checkboxes[i];
			if(checkbox.checked && checkbox.prescCode.inlist(['LabItemDiagData','LabItemTreatmentData'])) {
				if(!Ext.isArray(labdiag[checkbox.prescPid])) {
					labdiag[checkbox.prescPid]=[];
				}
				labdiag[checkbox.prescPid].push(checkbox.prescId); // UslugaComplex_id
				item_checked=true;
			}
		}
		if(item_checked == false) {
			sw.swMsg.alert(lang['oshibka'], lang['net_vyidelennyih_naznacheniy']);
			return false;
		}
		var save_data = {};
		save_data.oper = oper;
		save_data.proc = proc;
		save_data.funcdiag = funcdiag;
		save_data.drug = drug;
		save_data.labdiag = labdiag;
		Ext.Ajax.request({
			url: '/?c=EvnPrescr&m=saveCureStandartForm',
			callback: function(opt, success, response) {
				this.getLoadMask().hide();
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success)
					{
						this.callback();
						this.hide();
					}
					else if ( response_obj.Error_Msg )
					{
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(lang['oshibka'], lang['nepravilnyiy_otvet_servera']);
					}
				}
			}.createDelegate(this),
			params: {
				//CureStandart_id: this.CureStandart_id,
				//Evn_rid: this.Evn_rid,
				PersonEvn_id: this.PersonEvn_id,
				Server_id: this.Server_id,
				Evn_pid: this.Evn_pid,
				save_data: Ext.util.JSON.encode(save_data),
				parentEvnClass_SysNick: this.parentEvnClass_SysNick
			}
		});
    },
    getSectionKey: function(code) {
        var key = code+'_Section';
        switch(code) {
            case 'LabItemDiagData':
                key = 'LabDiagData_Section';
                break;
            case 'LabItemTreatmentData':
                key = 'LabTreatmentData_Section';
                break;
        }
        return key;
    },
    onCheckCheckbox: function(code, checked) {
        var key = this.getSectionKey(code);
        if(checked) this.section[key].cntChecked++;
        else this.section[key].cntChecked--;
        if(this.section[key].cntChecked == 0)
            this.checkboxesSection[key].setValue(false);
        if(this.section[key].cntChecked == this.section[key].cntAll)
            this.checkboxesSection[key].setValue(true);
	},
	checkCheckboxes: function(code, checked, pid) {
		if(this.checkboxes.length == 0) {
			return false;
		}
		var i, checkbox, flag = false;
		for(i=0;i<this.checkboxes.length;i++) {
			checkbox = this.checkboxes[i];
			flag = (pid && pid > 0)?(checkbox.prescPid == pid && checkbox.prescCode == code):(checkbox.prescCode == code);
			if(flag) {
				checkbox.setValue(checked);
			}
		}
	},
	checkCheckboxesComplex: function(code, checked, pid) {
		if(this.checkboxesComplex.length == 0) {
			return false;
		}
		var i, checkbox, flag = false;
		for(i=0;i<this.checkboxesComplex.length;i++) {
			checkbox = this.checkboxesComplex[i];
			flag = (pid && pid > 0)?(checkbox.prescPid == pid && checkbox.prescCode == code):(checkbox.prescCode == code);
			if(flag) {
				checkbox.setValue(checked);
			}
		}
	},

	initComponent: function() {
		this.mainPanel = new Ext.Panel({
			autoScroll: true,
			split: true,
			autoHeight: true,
			bodyStyle: 'background-color: #fff',
			minSize: 600,
			frame: true,
			floatable: false,
			collapsible: false,
			animCollapse: false,
			collapsed: true,
			region: 'center',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false,
				style: 'border 0px'
			},
			items: 
			[{
				html: ''
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave()
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.mainPanel
			]
		});
		sw.Promed.swCureStandartTemplateWindow.superclass.initComponent.apply(this, arguments);
	},

	show: function() {
		sw.Promed.swCureStandartTemplateWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].CureStandart_id || !arguments[0].Evn_rid  || !arguments[0].Evn_pid )
		{
			return false;
		}
		this.action = arguments[0].action || 'view';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.CureStandart_id = arguments[0].CureStandart_id;
		this.Evn_rid = arguments[0].Evn_rid;//ТАП,КВС
		this.Evn_pid = arguments[0].Evn_pid;//посещение, движение
		this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick ||  null;
		this.PersonEvn_id = arguments[0].PersonEvn_id;
		this.Server_id = arguments[0].Server_id;

		this.doReset();
		this.getLoadMask(LOAD_WAIT);
		this.getLoadMask().show();
		this.checkboxes = [];
		this.checkboxesComplex = [];
		this.checkboxesSection = {};
        this.section = {};
        for (var i=0; i<this.sectionList.length; i++) {
            this.section[this.getSectionKey(this.sectionList[i].name)] = {cntAll: 0, cntChecked: 0};
        }
		this.buttons[0].setDisabled(true);
		Ext.Ajax.request({
			url: '/?c=EvnPrescr&m=loadCureStandartForm',
			callback: function(opt, success, response) {
				this.getLoadMask().hide();
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj['Error_Msg'])
					{
						sw.swMsg.alert(lang['oshibka'], response_obj['Error_Msg']);
					}
					else if ( response_obj['html'] && response_obj['checkboxes'])
					{
						var tpl = new Ext.XTemplate(response_obj['html']);
						tpl.overwrite(this.mainPanel.body, {});
						var el,cb,i,p;
						for(i=0;i<response_obj['checkboxes'].length;i++) {
							p = response_obj['checkboxes'][i]
							if(typeof p != 'object' || !p.code || !p.id)
								continue;
							el = Ext.get(p.code +'_'+ p.id +'_checkbox');
							if(el && this.action=='edit') {
                                this.section[this.getSectionKey(p.code)].cntAll++;
                                if(p.checked) this.section[this.getSectionKey(p.code)].cntChecked++;
								//el.update('');
								if ('DrugData' == p.code) {
									cb = new Ext.form.Checkbox({
										checked: (p.checked)?true:false,
										prescCode: 'DrugData',
										prescId: p.ActMatters_id,
										prescPid: p.pid || null,
										is_complex: false,
										name: 'DrugData_'+ p.id,
										listeners: {
											'check': function(checkbox, checked) {
												this.onCheckCheckbox(checkbox.prescCode, checked);
											}.createDelegate(this)
										},
										renderTo: 'DrugData_'+ p.id +'_checkbox' // DrugData_{CureStandartTreatmentDrug_id}_checkbox
									});
									this.checkboxes.push(cb);
								} else if (p.childCode) {
									cb = new Ext.form.Checkbox({
										checked: (p.checked)?true:false,
										code: p.code,
										prescCode: p.code+'Complex',
										prescId: p.id,// UslugaComplex_id
										is_complex: true,
										childCode: p.childCode,
										name: p.code +'_'+ p.id,
										listeners: {
											'check': function(checkbox, checked) {
												this.checkCheckboxes(checkbox.childCode, checked, checkbox.prescId);
												this.onCheckCheckbox(checkbox.code, checked);
											}.createDelegate(this)
										},
										renderTo: p.code +'_'+ p.id +'_checkbox'
									});
									this.checkboxesComplex.push(cb);
								} else {
									cb = new Ext.form.Checkbox({
										checked: (p.checked)?true:false,
										prescCode: p.code,
										prescId: p.id,// UslugaComplex_id
										prescPid: p.pid || null,// UslugaComplex_pid
										is_complex: false,
										name: p.code +'_'+ p.id,
										listeners: {
											'check': function(checkbox, checked) {
												this.onCheckCheckbox(checkbox.prescCode, checked);
											}.createDelegate(this)
										},
										renderTo: p.code +'_'+ p.id +'_checkbox'
									});
									this.checkboxes.push(cb);
								}
							}
						}
						if(this.checkboxes.length > 0 && this.action=='edit') {
							this.buttons[0].setDisabled(false);
						}
						
						for(p in this.section) {
							el = Ext.get(p +'_checkbox');
							if(el && this.action=='edit') {
								cb = new Ext.form.Checkbox({
									checked: (this.section[p].cntAll > 0 && this.section[p].cntAll == this.section[p].cntChecked)?true:false,
									name: p,
									listeners: {
										'check': function(checkbox, checked) {
                                            var code = checkbox.name.split('_')[0];
											switch(code) {
												case 'LabDiagData':
													this.checkCheckboxes('LabDiagData', checked);
													this.checkCheckboxesComplex('LabDiagDataComplex', checked);
													break;
												case 'LabTreatmentData':
													this.checkCheckboxes('LabTreatmentData', checked);
													this.checkCheckboxesComplex('LabTreatmentDataComplex', checked);
													break;
                                                default:
                                                    this.checkCheckboxes(code, checked);
                                                    break;
											}
										}.createDelegate(this)
									},
									renderTo: p +'_checkbox'
								});
								this.checkboxesSection[p] = cb;
							}
						}
						this.mainPanel.expand();
					}
					else
					{
						sw.swMsg.alert(lang['oshibka'], lang['nepravilnyiy_otvet_servera']);
					}
				}
			}.createDelegate(this),
			params: {
				CureStandart_id: this.CureStandart_id,
				Evn_rid: this.Evn_rid,
				Evn_pid: this.Evn_pid,
				parentEvnClass_SysNick: this.parentEvnClass_SysNick
			}
		});

	}
});