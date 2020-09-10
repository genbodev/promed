/**
* swEvnPLDispTeenInspectionDirectionWindow - окно направления
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author		Dmitry Vlasenko
* @version		01.08.2013
* @comment		Префикс для id компонентов EPLDTIDF (EvnPLDispTeenInspectionDirectionForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeenInspectionDirectionWindow = Ext.extend(sw.Promed.BaseForm, {
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeenInspectionDirectionWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeenInspectionDirectionWindow.js',
	draggable: true,
	height: 400,
	id: 'EvnPLDispTeenInspectionDirectionWindow',
	loadEvnUslugaDispDopGrid: function() {
		var win = this;
		win.evnUslugaDispDopGrid.loadData({
			params: { Person_id: win.Person_id }, globalFilters: { Person_id: win.Person_id, EducationInstitutionType_id: win.EducationInstitutionType_id, DispClass_id: 6, PersonDispOrp_id: win.PersonDispOrp_id }
		});
	},
	showEvnUslugaDispDopEditWindow: function(action) {
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record || !record.get('SurveyTypeLink_id') ) {
			return false;
		}
		
		var personinfo = win.PersonInfoPanel;
		
		getWnd('swEvnPLDispTeenInspectionDirectionEditWindow').show({
			action: action,
			object: 'EvnPLDispTeenInspection',
			OmsSprTerr_Code: personinfo.getFieldValue('OmsSprTerr_Code'),
			Person_id: personinfo.getFieldValue('Person_id'),
			PersonDispOrp_id: win.PersonDispOrp_id,
			Person_Birthday: personinfo.getFieldValue('Person_Birthday'),
			Person_Firname: personinfo.getFieldValue('Person_Firname'),
			Person_Secname: personinfo.getFieldValue('Person_Secname'),
			Person_Surname: personinfo.getFieldValue('Person_Surname'),
			Sex_id: personinfo.getFieldValue('Sex_id'),
			Sex_Code: personinfo.getFieldValue('Sex_Code'),
			Person_Age: personinfo.getFieldValue('Person_Age'),
			UserLpuSection_id: win.UserLpuSection_id,
			UserMedStaffFact_id: win.UserMedStaffFact_id,
			formParams: {
				SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
				PersonEvn_id: win.PersonEvn_id,
				Server_id: win.Server_id,
				EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
				DispClass_id: 6
			},
			SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
			SurveyType_Code: record.get('SurveyType_Code'),
			SurveyType_Name: record.get('SurveyType_Name'),
			onHide: Ext.emptyFn,
			callback: function(data) {
				// обновить грид!
				win.loadEvnUslugaDispDopGrid();
				win.callback(data);
			}
			
		});
	},
	initComponent: function() {
		var win = this;
		
		this.evnUslugaDispDopGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { win.showEvnUslugaDispDopEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.showEvnUslugaDispDopEditWindow('view'); } },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			onLoadData: function() {
				this.doLayout();
				
			},
			id: 'EPLDTIDF_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispTeenInspection&m=loadEvnUslugaDispDopGridForDirection',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'EvnUslugaDispDop_ExamPlace', type: 'string', header: 'Место проведения (план)', width: 200 },
				{ name: 'EvnUslugaDispDop_setDate', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), header: 'Дата и время проведения (план)', width: 200 }
			]
		});
		
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			hidden: true,
			region: 'north'
		});
		
		Ext.apply(this, {
			items: [
				win.PersonInfoPanel,
				// маршрутная карта по сути
				win.evnUslugaDispDopGrid
			],
			buttons: ['-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDTIDF_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispTeenInspectionDirectionWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 570,
	minWidth: 800,
	modal: true,
	onHide: Ext.emptyFn,
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},

	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispTeenInspectionDirectionWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if (!arguments[0] || !arguments[0].Person_id || !arguments[0].PersonDispOrp_id || !arguments[0].EducationInstitutionType_id)
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { win.hide(); } );
			return false;
		}

		this.restore();
		this.center();

		this.Person_id = arguments[0].Person_id;
		this.PersonDispOrp_id = arguments[0].PersonDispOrp_id;
		
		if (arguments[0].Server_id != undefined)
		{
			this.Server_id = arguments[0].Server_id;
		}
		
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}
		
		this.EducationInstitutionType_id = arguments[0].EducationInstitutionType_id;
		
		win.getLoadMask(lang['zagruzka_dannyih']).show();
		
		this.PersonInfoPanel.load({ 
			Person_id: win.Person_id, 
			Server_id: win.Server_id, 
			callback: function() {
				win.getLoadMask().hide();
				win.PersonEvn_id = win.PersonInfoPanel.getFieldValue('PersonEvn_id');
			} 
		});
		
		this.onHide = Ext.emptyFn;
	
		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		
		// грузим грид услуг
		win.loadEvnUslugaDispDopGrid();	
		this.doLayout();
		
	},
	title: lang['napravlenie_na_periodicheskiy_osmotr'],
	width: 750
});