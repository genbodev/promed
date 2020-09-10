var LssConstants = {
    XmlCanonicalization: {
        C14N: 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
        C14NC: 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments',
        EXSLUSIVE_C14N: 'http://www.w3.org/2001/10/xml-exc-c14n#',
        EXSLUSIVE_C14NC: 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments',
        C14N11: 'http://www.w3.org/2006/12/xml-c14n11',
        C14NC11: 'http://www.w3.org/2006/12/xml-c14n11#WithComments'
    },
    XmlTransformation: {
        ENVELOPED: 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
        BASE64: 'http://www.w3.org/2000/09/xmldsig#base64',
        XPATH_FILTER2: 'http://www.w3.org/2002/06/xmldsig-filter2',
        XPATH: 'http://www.w3.org/TR/1999/REC-xpath-19991116'
    }
};

var LssClient = function ($) {
    var defaultExtension = '.doc';
    var usingByPass = false;
    var defaultTimeout = 0;
    var checkLssConnectivityTimeout = 10000;

    function YokuServiceClient() {
        $.support.cors = true;
        var httpBaseUrl = "http://127.0.0.1:61111/webhost";
        var sslBaseUrl = "https://127.0.0.1:61112/webhost";

        var POST = 'POST';

        function post(type, data, timeout) {
            return $.ajax({
                url: createUrl(POST, type),
                type: POST,
                data: JSON.stringify(data),
                contentType: "application/json",
                crossDomain: true,
                processData: false,
                dataType: "json",
                timeout: timeout
            });
        }

        function createUrl(methodName, type) {
            return getBaseUrl() + '/' + methodName + '?' + 'type' + '=' + type;
        }

        function getBaseUrl() {
            return window.location.protocol == "https:" ? sslBaseUrl : httpBaseUrl;
        }

        return {
            post: post,
            getBaseUrl: getBaseUrl
        };
    }

    function HeartBeatRequest() {
        var self = this;
        self.taskType = 'SystemStateTask';
        self.Payload = 'CheckLssPresence';
    }

    function SignRequest(data, certificate) {
        var self = this;
        self.taskType = 'SignTask';
        self.DataToSign = data;
        self.SignCertificate = certificate;
    }

    function SignVerificationRequest(signedData, originalData) {
        var self = this;
        self.taskType = 'VerifyTask';
        self.SignedData = signedData;
        self.OriginalData = originalData;
    }

    function EncryptionRequest(data, certificates) {
        var self = this;
        self.taskType = 'EncryptTask';
        self.DataToEncrypt = data;
        self.Certificates = certificates;
    }

    function DecryptionRequest(data) {
        var self = this;
        self.taskType = 'DecryptTask';
        self.EncryptedData = data;
    }

    function SignAndEncryptionRequest(data, certificates, signCertificate) {
        var self = this;
        self.taskType = 'SignAndEncryptTask';
        self.DataToSignAndEncrypt = data;
        self.Certificates = certificates;
        self.SignCertificate = signCertificate;
    }

    function DecryptAndVerifySignRequest(data, certificate) {
        var self = this;
        self.taskType = 'DecryptAndVerifyTask';
        self.EnctyptedDataAndSign = data;
    }

    function SelectCertificateRequest() {
        var self = this;
        self.taskType = 'SelectCertificateTask';
    }

    function HashRequest() {
        var self = this;
        self.taskType = 'HashTask';
    }

    function SignedContentRequest() {
        var self = this;
        self.taskType = 'SignedContentTask';
    }

    function SpecialSign01Request() {
        var self = this;
        self.taskType = 'SpecialSign01Task';
    }

    function SignFileRequest() {
        var self = this;
        self.taskType = 'SignFileRequest';
    }

    function SignXmlRequest() {
        var self = this;
        self.taskType = 'SignXmlTask';
    }

    function CertificateRequestMessage(subject, extendedKeyUsages) {
        var self = this;
        self.taskType = 'CreateCertificateRequestTask';
        self.Subject = subject;
        self.ExtendedKeyUsages = extendedKeyUsages;
    }

    function InstallCertificateIssuedByRequestMessage(certificate) {
        var self = this;
        self.taskType = 'InstallCertificateIssuedByRequestTask';
        self.Certificate = certificate;
    }

    function VerifySignedXmlRequest() {
        var self = this;
        self.taskType = 'VerifySignedXmlTask';
    }

    function generateGuid() {
        var d = new Date().getTime();
        var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (d + Math.random() * 16) % 16 | 0;
            d = Math.floor(d / 16);
            return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
        });
        return uuid;
    }

    function sendRequest(task, timeout) {
        var client = new YokuServiceClient();
        if (usingByPass) {
            task.ByPassVisualization = true;
        }
        return client.post(task.taskType, task, timeout);
    }

    function installCertificateIssuedByRequest(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var task = new InstallCertificateIssuedByRequestMessage(options.certificate);
        task.RequestId = options.requestId;
        return sendRequest(task, defaultTimeout);
    }

    function generateCertificateRequest(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var task = new CertificateRequestMessage(
            options.subject,
            options.extendedKeyUsages);
        task.RequestId = options.requestId;
        return sendRequest(task, defaultTimeout);
    }

    function sign(options) {

        var defaultOptions = {
            base64Data: '-',
            description: 'Описание не задано',
            documentName: 'Подпись',
            fileExtension: defaultExtension,
            isAttached: true,
            base64Certificate: '-',
            disableCertificateVerification: false,
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var task = new SignRequest(options.base64Data, options.base64Certificate);
        task.IsAttached = options.isAttached;
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.RequestId = options.requestId;
        task.TspServerUrl = options.tspServerUrl;
        task.TspServerTimeoutInMilliseconds = parseInt(options.tspServerTimeout);
        task.DisableCertificateVerification = options.disableCertificateVerification;

        return sendRequest(task, defaultTimeout);
    }

    function verifySign(options) {
        var defaultOptions = {
            base64Data: '-',
            base64DataWithoutSign: '-',
            isAttached: true,
            description: 'Описание не задано',
            documentName: 'Проверка подписи',
            fileExtension: defaultExtension,
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var task = new SignVerificationRequest(options.base64Data, options.base64DataWithoutSign);
        task.IsAttached = options.isAttached;
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.RequestId = options.requestId;

        return sendRequest(task, defaultTimeout);
    }

    function verifyFile(options) {
        var defaultOptions = {
            isAttached: true,
            description: 'Описание не задано',
            documentName: 'Проверка подписи',
            fileExtension: defaultExtension,
            requestId: generateGuid(),
			byPassVisualization : usingByPass
        };

        options = $.extend(defaultOptions, options);
        options.ViewDescriptor = { FileExtension: options.fileExtension };

        var client = new YokuServiceClient();
        var url = client.getBaseUrl() + "/VerifyFile/" + options.requestId;
        var data = new FormData();
        data.append('options', JSON.stringify(options));
        data.append('file', options.file);

        $.support.cors = true;

        return $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            crossDomain: true,
            processData: false,
            contentType: 'multipart/form-data',
        });
    }

    function encrypt(options) {
        var defaultOptions = {
            base64Data: '-',
            base64Certificates: [],
            description: 'Описание не задано',
            documentName: 'Шифрование',
            fileExtension: defaultExtension,
            disableCertificateVerification: false,
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var task = new EncryptionRequest(options.base64Data, options.base64Certificates);
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.DisableCertificateVerification = options.disableCertificateVerification;
        task.RequestId = options.requestId;

        return sendRequest(task, defaultTimeout);
    }

    function decrypt(options) {
        var defaultOptions = {
            base64Data: '-',
            description: 'Описание не задано',
            documentName: 'Расшифрование',
            fileExtension: defaultExtension,
            disableCertificateVerification: false,
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);
        var task = new DecryptionRequest(options.base64Data);
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.DisableCertificateVerification = options.disableCertificateVerification;
        task.RequestId = options.requestId;

        return sendRequest(task, defaultTimeout);
    }

    function signAndEncrypt(options) {
        var defaultOptions = {
            base64Data: '-',
            base64Certificates: [],
            description: 'Описание не задано',
            documentName: 'Подпись и шифрование',
            fileExtension: defaultExtension,
            base64SignCertificate: '-',
            disableCertificateVerification: false,
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);
        var task = new SignAndEncryptionRequest(options.base64Data, options.base64Certificates, options.base64SignCertificate);
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.TspServerUrl = options.tspServerUrl;
        task.TspServerTimeoutInMilliseconds = parseInt(options.tspServerTimeout);
        task.DisableCertificateVerification = options.disableCertificateVerification;
        task.RequestId = options.requestId;

        return sendRequest(task, defaultTimeout);
    }

    function decryptAndVerifySign(options) {
        var defaultOptions = {
            base64Data: '-',
            description: 'Описание не задано',
            documentName: 'Расшифрование и проверка подписи',
            fileExtension: defaultExtension,
            disableCertificateVerification: false,
            requestId: generateGuid()
        };

        options = options = $.extend(defaultOptions, options);
        var task = new DecryptAndVerifySignRequest(options.base64Data);
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.DisableCertificateVerification = options.disableCertificateVerification;
        task.RequestId = options.requestId;

        return sendRequest(task, defaultTimeout);
    }

    function selectCertificate(options) {
        var defaultOptions = {
            disableCertificateVerification: false,
            requestId: generateGuid()
        };
        options = $.extend(defaultOptions, options);
        var task = new SelectCertificateRequest();
        task.DisableCertificateVerification = options.disableCertificateVerification;
        task.RequestId = options.requestId;

        return sendRequest(task, defaultTimeout);
    }

    function hash(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };
        options = $.extend(defaultOptions, options);
        var task = new HashRequest();
        task.RequestId = options.requestId;
        task.DataToHash = options.base64Data;

        return sendRequest(task, defaultTimeout);
    }

    function hashFile(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);
        var client = new YokuServiceClient();
        var url = client.getBaseUrl() + "/hash/" + options.requestId;

        var data = new FormData();
        data.append('options', JSON.stringify(options));
        data.append('file', options.file);
        $.support.cors = true;
        return $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            crossDomain: true,
            processData: false,
            contentType: 'multipart/form-data',
        });
    }

    function getSignedContent(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var task = new SignedContentRequest();
        task.RequestId = options.requestId;
        task.SignedData = options.base64Data;

        return sendRequest(task, defaultTimeout);
    }

    function specialSign01(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var task = new SpecialSign01Request();
        task.RequestId = options.requestId;
        task.DataToSign = options.base64Data;
        task.SignCertificate = options.base64Certificate;
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.DisableCertificateVerification = options.disableCertificateVerification;

        return sendRequest(task, defaultTimeout);
    }

    function specialSignFile(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var client = new YokuServiceClient();
        var url = client.getBaseUrl() + "/SpecialSign/" + options.requestId;
        var data = new FormData();

        data.append('options', JSON.stringify(options));
        data.append('file', options.file);

        $.support.cors = true;
        return $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            crossDomain: true,
            processData: false,
            contentType: 'multipart/form-data',
        });
    }

    function signFile(options) {
        var defaultOptions = {
            requestId: generateGuid()
        };

        options = $.extend(defaultOptions, options);

        var client = new YokuServiceClient();
        var url = client.getBaseUrl() + "/SignFile/" + options.requestId;
        var data = new FormData();
        data.append('options', JSON.stringify(options));
        data.append('file', options.file);

        $.support.cors = true;

        return $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            crossDomain: true,
            processData: false,
            contentType: 'multipart/form-data',
        });
    }

    function signXml(options) {
        var defaultOptions = {
            requestId: generateGuid(),
            documentName: 'Документ Xml',
            fileExtension: '.xml',
            canonicalizationMethod: LssConstants.XmlCanonicalization.EXSLUSIVE_C14N,
            references: [
                {
                    transforms: [
                      { algorithm: LssConstants.XmlTransformation.ENVELOPED },
                      { algorithm: LssConstants.XmlCanonicalization.EXSLUSIVE_C14N },
                    ]
                }],
        };

        options = $.extend(defaultOptions, options);
        var task = new SignXmlRequest();
        task.RequestId = options.requestId;
        task.Xml = options.xml;
        task.CanonicalizationMethod = options.canonicalizationMethod;
        task.References = options.references.map(mapReference);
        task.SignatureId = options.signatureId;
        task.SignatureLocationPath = options.signatureLocationPath;
        task.SignCertificate = options.base64Certificate;
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };
        task.DisableCertificateVerification = options.disableCertificateVerification;

        return sendRequest(task, defaultTimeout);
    }

    function verifySignedXml(options) {
        var defaultOptions = {
            requestId: generateGuid(),
            documentName: 'Подписанный документ Xml',
            fileExtension: '.xml',
        };

        options = $.extend(defaultOptions, options);
        var task = new VerifySignedXmlRequest();
        task.RequestId = options.requestId;
        task.SignedXml = options.signedXml;
        task.DocumentName = options.documentName;
        task.Description = options.description;
        task.ViewDescriptor = { FileExtension: options.fileExtension };

        return sendRequest(task, defaultTimeout);
    }

    function mapReference(reference) {
        return {
            Id: reference.id,
            Type: reference.type,
            Uri: reference.uri,
            Transforms: reference.transforms.map(function (transform) {
                return {
                    Algorithm: transform.algorithm,
                    TransformValue: transform.transformValue
                }
            })
        }
    }

    function checkLssConnectivity() {
        var task = new HeartBeatRequest();
        return sendRequest(task, checkLssConnectivityTimeout);
    }

    function withBypass() {
        usingByPass = true;
        return this;
    }


    return {
        signFile: signFile,
        verifyFile: verifyFile,
        sign: sign,
        verify: verifySign,
        encrypt: encrypt,
        decrypt: decrypt,
        signAndEncrypt: signAndEncrypt,
        decryptAndVerifySign: decryptAndVerifySign,
        selectCertificate: selectCertificate,
        checkConnection: checkLssConnectivity,
        withBypass: withBypass,
        generateCertificateRequest: generateCertificateRequest,
        installCertificateIssuedByRequest: installCertificateIssuedByRequest,
        hash: hash,
        hashFile: hashFile,
        getSignedContent: getSignedContent,
        specialSignFile: specialSignFile,
        signXml: signXml,
        verifySignedXml: verifySignedXml
    }
}