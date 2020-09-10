/**
 * swWorkGraphMiddleEditWindow
 *
 * График дежурств среднего медперсонала - редактор.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access    public
 * @copyright 2019
 */
Ext6.define('common.WorkGraphMiddle.swWorkGraphMiddleEditWindow', {
    extend: 'base.BaseForm',

    action: undefined,
    userMedStaffFact: undefined,

    recordData: undefined,

    callback: undefined,
    scope: undefined,

    title: lang['duty_middle'],

    width: 700,
    //   height: 400,
    bodyPadding: 10,

    modal: true,
    closeAction: 'hide',

    items: [{
            xtype: 'form',
            border: false,
            layout: 'vbox',
            flex: 1,

            defaults: {
                labelWidth: 200,
            },

            items: [{
                    xtype: 'swMedStaffFactCombo',
                    itemId: 'cmbMedStaffFact',
                    name: 'MedStaffFact_id',
                    fieldLabel: lang['sotrudnik'],
                    forceSelection: true,
                    allowBlank: false,
                    width: '100%'
                },
                {
                    xtype: 'datefield',
                    itemId: 'dtBegDate',
                    name: 'WorkGraphMiddle_begDate',
                    fieldLabel: lang['duty_begin_date'],
                    format: 'd.m.Y G:i',
                    allowBlank: false,
                    formatText: ''
                },
                {
                    xtype: 'datefield',
                    itemId: 'dtEndDate',
                    name: 'WorkGraphMiddle_endDate',
                    fieldLabel: lang['duty_end_date'],
                    format: 'd.m.Y G:i',
                    allowBlank: false,
                    formatText: ''
                }
            ]
        },
        {
            xtype: 'container',

            layout: {
                type: 'hbox',
                pack: 'end'
            },

            items: [{
                    xtype: 'button',
                    itemId: 'btnSave',
                    text: lang['sohranit'],
                    cls: 'button-primary',
                    iconCls: 'save16',
                    disabled: true
                },
                {
                    xtype: 'button',
                    itemId: 'btnCancel',
                    text: lang['otmena'],
                    cls: 'button-secondary',
                    iconCls: 'cancel16',
                    margin: '0 0 0 10'
                }
            ]
        }
    ],

    _form: undefined,
    _cmbMedStaffFact: undefined,
    _dtBegDate: undefined,
    _dtEndDate: undefined,

    _btnSave: undefined,

    /******* initComponent ********************************************************
     *
     ******************************************************************************/
    initComponent: function() {
        var me = this,
            btnCancel;

        this.callParent(arguments);

        this._form = this.down('form');
        this._form.addListener('validitychange', this._onValidityChange, this);

        this._cmbMedStaffFact = this.down('#cmbMedStaffFact');

        this._dtBegDate = this.down('#dtBegDate');
        this._dtBegDate.validator = _validator_dtBeg;

        this._dtEndDate = this.down('#dtEndDate');
        this._dtEndDate.validator = _validator_dtEnd;

        this._btnSave = this.down('#btnSave');
        this._btnSave.handler = this._onClick_save;
        this._btnSave.scope = this;

        btnCancel = this.down('#btnCancel');
        btnCancel.handler = () => this.close();

        this._initFinished = true;

        /******* _validator_dtBeg *****************************************************
         *
         */
        function _validator_dtBeg()
         {
          var endVal;

          if (me.action != 'view' &&
              (val = this.getValue()) &&
              (endVal = me._dtEndDate.getValue()) &&
              val > endVal)
           return (lang['duty_begin_date_err_high']);

          if (!this._validateLock)
           {
            me._dtEndDate._validateLock = true;
            me._dtEndDate.validate();
            delete me._dtEndDate._validateLock;
           }

          return (true);
         }

        /******* _validator_dtEnd *****************************************************
         *
         */
        function _validator_dtEnd()
         {
          var begVal;

          if (me.action != 'view' &&
              (val = this.getValue()) &&
              (begVal = me._dtBegDate.getValue()) &&
              val < begVal)
           return (lang['duty_end_date_err_low']);

          if (!this._validateLock)
           {
            me._dtBegDate._validateLock = true;
            me._dtBegDate.validate();
            delete me._dtBegDate._validateLock;
           }

          return (true);
         }
    },

    /******* show *****************************************************************
     *
     ******************************************************************************/
    show: function(params) {
        this.action = params.action;
        this.recordData = params.recordData;
        this.userMedStaffFact = params.userMedStaffFact;
        this.callback = params.callback;
        this.scope = params.scope;

        this._setTitle();
        this.callParent(arguments);
        this._prepareForm();

        this._btnSave.setHidden(this.action == 'view');
        this._callAfterInit(this._prepareForm);
    },

    /******* _callAfterInit *******************************************************
     *
     ******************************************************************************/
    _callAfterInit: function(fn) {
        if (this._initFinished)
         fn.call(this);
        else
         Ext.defer(() => this._callAfterInit(fn), 1);
    },

    /******* _setTitle ************************************************************
     *
     ******************************************************************************/
    _setTitle: function() {
        var title = lang['duty_middle'];

        if (this.action == 'add')
            title += ': ' + lang['dobavlenie'];
        else if (this.action == 'edit')
            title += ': ' + lang['redaktirovanie'];
        else if (this.action == 'view')
            title += ': ' + lang['prosmotr'];

        this.setTitle(title);
    },

    /******* _prepareForm *********************************************************
     *
     ******************************************************************************/
    _prepareForm: function() {
        var curDate,
            isReadOnlyByDate = false;

        if (this.action == 'edit' && this.recordData &&
            (curDate = getGlobalOptions().date.match(/(\d+).(\d+).(\d+)/))) {
            curDate = new Date(curDate[3], curDate[2] - 1, curDate[1]);
            isReadOnlyByDate = (this.recordData.WorkGraphMiddle_begDate <= curDate);
        }

        setMedStaffFactGlobalStoreFilter({
                LpuSection_id: this.userMedStaffFact.LpuSection_id,
                isMidMedPersonalOnly: true
            },
            sw4.swMedStaffFactGlobalStore);

        store = this._cmbMedStaffFact.getStore();
        store.removeAll();
        store.loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));

        this._cmbMedStaffFact.setReadOnly(this.action == 'view' || isReadOnlyByDate);
        this._dtBegDate.setReadOnly(this.action == 'view' || isReadOnlyByDate);
        this._dtEndDate.setReadOnly(this.action == 'view');

        if (this.action == 'add')
            this._form.getForm().getFields().each(_clearField);
        else
        if (this.recordData)
            this._form.getForm().setValues(this.recordData);


        this._btnSave.setHidden(this.action == 'view');

        /******* _clearField **********************************************************
         *
         */
        function _clearField(field) {
            field.setValue(null);
            field.isValid();
        }
    },

    /******* _onValidityChange ****************************************************
     *
     ******************************************************************************/
    _onValidityChange: function(form, isValid) {
        this._btnSave.setDisabled(!isValid);
    },

    /******* _onClick_save ********************************************************
     *
     ******************************************************************************/
    _onClick_save: function() {
        if (this.action == 'add') {
            this.setLoading(true);

            Ext6.Ajax.request({
                url: '/?c=WorkGraphMiddle&m=addWorkGraphMiddle',

                params: {
                    MedStaffFact_id: this._cmbMedStaffFact.getValue(),
                    WorkGraphMiddle_begDate: this._dtBegDate.getValue().dateFormat('Y-m-d\\TH:i:s'),
                    WorkGraphMiddle_endDate: this._dtEndDate.getValue().dateFormat('Y-m-d\\TH:i:s'),
                    pmUser_id: getGlobalOptions().pmuser_id
                },

                callback: this._onSave,
                scope: this
            });
        } else if (this.action == 'edit') {
            this.setLoading(true);

            Ext6.Ajax.request({
                url: '/?c=WorkGraphMiddle&m=updWorkGraphMiddle',

                params: {
                    WorkGraphMiddle_id: this.recordData.WorkGraphMiddle_id,
                    MedStaffFact_id: this._cmbMedStaffFact.getValue(),
                    WorkGraphMiddle_begDate: this._dtBegDate.getValue().dateFormat('Y-m-d\\TH:i:s'),
                    WorkGraphMiddle_endDate: this._dtEndDate.getValue().dateFormat('Y-m-d\\TH:i:s'),
                    pmUser_id: getGlobalOptions().pmuser_id
                },

                callback: this._onSave,
                scope: this
            });
        }
    },

    /******* _onSave **************************************************************
     *
     ******************************************************************************/
    _onSave: function(opts, success, response) {
        if (success && response.responseText.match(/"success"\s*:\s*true/)) {
            if (this.callback)
                this.callback.call(this.scope || window);

            this.close();
        }

        this.setLoading(false);
    }
});
