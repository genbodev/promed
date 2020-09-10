/**
* Стандартная форма диагноза
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      20.12.2011
*/

sw.Promed.swDiagPanel = Ext.extend(Ext.Panel,
{
	title: '',
	//height: 55,
	labelAlign: 'right',
	layout: 'form',
	labelWidth: 120,
	bodyStyle: 'padding: 3px;',
	border: false,
	defaults: {
		border: false
	},
	diagField: null,
	diagPhase: null,
	copyBtn: null,
	phaseDescrName: 'EvnDiagPS_PhaseDescr',
	diagSetPhaseName: 'DiagSetPhase_id',
	diagPhaseFieldLabel: langs('Стадия/фаза'),
	showHSN: false,
	initComponent: function()
	{
		if (!this.diagField) {
			this.diagField = {
				xtype: 'swdiagcombo',
				hiddenName: 'Diag_id',
				width: 500,
				fieldLabel: langs('Основной диагноз')
			};
		}
		
		this.HSNPrefix = this.HSNPrefix || "";
		this.diagField.onChange = this.refreshHSN;
		var tabindex = (this.diagField.tabIndex)?this.diagField.tabIndex:0;
		var width = (this.diagField.width)?this.diagField.width:500;
		if (!this.diagPhase) {
			this.diagPhase = {
				xtype: 'swdiagsetphasecombo',
				hiddenName: this.diagSetPhaseName,
				fieldLabel: this.diagPhaseFieldLabel,
				tabIndex: tabindex,
				width: width,
				//hideEmptyRow: true,
				editable: false
			};
		}
		
		this.diagTranscript = {
			maxLength: 400,
			name: this.phaseDescrName,
			tabIndex: tabindex,
			xtype: 'textarea',
			height: 50,
			width: width,
			fieldLabel: langs('Расшифровка')
		};

		if (this.showHSN) {
		// Стадия ХСН (для диагноза) и ФК
			// #170429:
			//  1. Отображается только при выборе диагноза из группы ХСН.
			//  2. Обязательно для заполнения только для диагноза из группы ХСН и только для региона ufa.
			this.diagHSNStage = new Ext.form.ComboBox({
				xtype: 'combo',
				hiddenName: 'HSNStage_id',
				fieldLabel: langs('Стадия ХСН'),
				tabIndex: TABINDEX_EVPLEF + 27.1,
				valueField: 'HSNStage_id',
				displayField: 'HSNStage_Name',
				mode: 'local',
				triggerAction: 'all',
				forceSelection: true,
				editable: false,
				width: 480,
				hidden: true,
				store: new Ext.data.JsonStore(
				{
					key: 'HSNStage_id',
					url: '/?c=EvnSection&m=getHsnStage',
					autoLoad: true,

					fields:
					[
						{ name: 'HSNStage_id', type: 'int' },
						{ name: 'HSNStage_Name', type: 'string' }
					]
				})
			});

			this.diagHSNFuncClass = new Ext.form.ComboBox({
				xtype: 'combo',
				hiddenName: 'HSNFuncClass_id',
				fieldLabel: langs('Функциональный класс'),
				tabIndex: TABINDEX_EVPLEF + 27.2,
				valueField: 'HSNFuncClass_id',
				displayField: 'HSNFuncClass_Name',
				mode:'local',
				triggerAction: 'all',
				forceSelection: true,
				editable: false,
				width: 480,
				hidden: true,
				store: new Ext.data.JsonStore(
				{
					key: 'HSNFuncClass_id',
					url: '/?c=EvnSection&m=getHSNFuncClass',
					autoLoad: true,

					fields:
					[
						{ name: 'HSNFuncClass_id', type: 'int' },
						{ name: 'HSNFuncClass_Name', type: 'string' }
					]
				})
			});
		}		

		var diag_item;
		if (this.copyBtn) {
			diag_item = 
			{
				layout: 'column',
				defaults: {
					border: false
				},
				items: [{
					layout: 'form',
					items:[this.diagField]
				}, {
					layout: 'form',
					items:[this.copyBtn]
				}]
			};
		} else {
			diag_item = this.diagField;
		}

		if (this.showHSN) {
			Ext.apply(this,	{
				items: [ 
					diag_item,
					this.diagHSNStage,
					this.diagHSNFuncClass, 
					this.diagPhase, 
					this.diagTranscript
				]
			});
		} else {
			Ext.apply(this,	{
				items: [ 
					diag_item,
					this.diagPhase, 
					this.diagTranscript
				]
			});
		}
		sw.Promed.swDiagPanel.superclass.initComponent.apply(this, arguments);		
		
	},
	refreshHSN: function (c, n, o) {
		var parent = this;

		if (!parent.diagHSNStage) {
		//ищем нужного родителя
			var parent = this.findParentBy(function(rec) {
				return rec.diagHSNStage;
			});
		}

		if (parent && parent.showHSN) {	
			var diagCode = parent.Diag_Code || c.getFieldValue('Diag_Code');
			if (!diagCode)
				return false;

			isHsn = (diagCode.inlist(['I50.0', 'I50.1', 'I50.9']));
	
			allowBlank = !(isHsn && (getRegionNick() == 'ufa'));
			parent.diagHSNStage.setContainerVisible(isHsn);
			parent.diagHSNFuncClass.setContainerVisible(isHsn);
			parent.diagHSNStage.setAllowBlank(allowBlank);
			parent.diagHSNFuncClass.setAllowBlank(allowBlank);
			
			if (isHsn) {
				Ext.Ajax.request({
					url: '/?c=EvnSection&m=getLastHsnDetails',
					params:{
						Person_id: parent.personId
					},
					callback: function(options, success, response){
						var stageId = null,
							classId = null,
							alertTxt,
							res;

						if (success &&
							(res = Ext.util.JSON.decode(response.responseText)) &&
							(res = res[0]))
						{
							stageId = res.HSNStage_id || null;
							classId = res.HSNFuncClass_id || null;

							if (!parent.hideMsg && (stageId || classId))
								alertTxt =
									'Пациенту в предыдущем случае лечения установлены стадия ХСН и функциональный ' +
									'класс. При необходимости можно изменить стадию ХСН и функциональный класс.';
							
							parent.diagHSNStage.setValue(stageId);
							parent.diagHSNFuncClass.setValue(classId);
						}
						
						if (alertTxt)
							sw.swMsg.alert('', alertTxt);
					}
				});
			} else {
				parent.diagHSNStage.setValue(null);
				parent.diagHSNFuncClass.setValue(null);
			}
		}
	},
	hideHSNField: function() {
		if (!this.showHSN)
			return false;
		this.diagHSNStage.hideContainer();
		this.diagHSNFuncClass.hideContainer();
	}
});