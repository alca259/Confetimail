<?php
// Controller names data
$controllerName = "Mails";
$areaName = Constants::$PanelAreaName;

// Actions
$gridFilesReadAction = "Attachments_Read";
$gridFilesToggleAction = "Attachments_Toggle";
$gridUsersReadAction = "Subscriptions_Read";
$mailImageReadAction = "Images_Read";

$mailSaveAction = "SaveMail";
$mailSendAction = "SendMail";

// Misc
$gridFilesName = "gridAttachments";
$gridUsersName = "gridSubscriptions";
$mailImageThumbnail = "/Application/Helpers/Thumbnail";
?>

<!-- Main -->
<div id="main">

    <!-- Intro -->
    <section id="options">

        <header>
            <h2>Administraci&oacute;n de e-mails</h2>
        </header>

        <div id="dialog-new-mail">
            <div class="actions">
                <input type="button" id="btnCancel" value="Volver atr&aacute;s" data-bind="click: ReturnMainPage" />
            </div>

            <div id="tabstrip" class="k-content">
                <ul>
                    <li class="k-state-active">
                        Email
                    </li>
                    <li data-bind="visible: EnableTabs">
                        Adjuntos
                    </li>
                    <li data-bind="visible: EnableTabs">
                        Enviados
                    </li>
                </ul>

                <div>
                    <div class="actions">
                        <input type="button" id="btnSave" value="Guardar" class="k-primary" data-bind="click: SaveMail" />
                    </div>

                    <input type="hidden" name="mail_id" id="mail_id" data-bind="value: IdMail" />

                    <div class="container">
                    <div class="row">
                        <div class="col-md-2"><label for="subject">T&iacute;tulo</label></div>
                        <div class="col-md-3"><input type="text" name="subject" id="subject" class="k-textbox" data-bind="value: Subject" /></div>
                        <div class="col-md-2"><label for="tematica">Tem&aacute;tica</label></div>
                        <div class="col-md-3"><input type="text" name="tematica" id="tematica" value="" class="k-textbox" data-bind="value: Tematica" /></div>
                    </div>

                    <div class="row">
                        <div class="col-md-2"><label for="date_send">Fecha de env&iacute;o</label></div>
                        <div class="col-md-3"><input type="datetime" name="date_send" id="date_send" data-bind="value: DateSend" /></div>
                        <div class="col-md-2"><label for="active">&iquest;Enviar a nuevos registros?</label></div>
                        <div class="col-md-3"><input type="checkbox" name="active" id="active" class="k-input" data-bind="checked: Active" /></div>
                    </div>

                    <div class="row">
                        <div class="col-md-2"><label for="is_confeti">&iquest;Es un confeti?</label></div>
                        <div class="col-md-3"><input type="checkbox" name="is_confeti" id="is_confeti" class="k-input" data-bind="checked: IsConfeti" /></div>
                        <div class="col-md-2"><label for="tematica">Descripci&oacute;n de la tem&aacute;tica</label></div>
                        <div class="col-md-3"><input type="text" name="tematica_desc" id="tematica_desc" value="" class="k-textbox" data-bind="value: TematicaDesc" /></div>
                    </div>

                    <div class="row">
                        <div class="col-md-2"><label for="image_frontend">Portada</label></div>
                        <div class="col-md-3">
                            <input type="text" name="image_carousel" id="image_carousel" data-bind="value: ImageCarousel" />
                            <input type="hidden" name="image_hidden_carousel" id="image_hidden_carousel" class="k-textbox" data-bind="events: { change: ChangeImageDataCarousel }" />
                            <input type="button" id="btnOpenImageBrowserCarousel" value="..." data-bind="click: OpenImageBrowserCarousel" />
                        </div>
                        <div class="col-md-2"><label for="image_frontend">Miniatura</label></div>
                        <div class="col-md-3">
                            <input type="text" name="image_frontend" id="image_frontend" data-bind="value: ImageFrontend" />
                            <input type="hidden" name="image_hidden_frontend" id="image_hidden_frontend" class="k-textbox" data-bind="events: { change: ChangeImageDataFrontend }" />
                            <input type="button" id="btnOpenImageBrowserFrontend" value="..." data-bind="click: OpenImageBrowserFrontend" />
                        </div>
                    </div>
                    </div>

                    <hr />
                    <ul id="panelbar">
                        <li>
                            <span class="k-link">Cabecera nuevos</span>
                            <div class="linForm">
                                <textarea id="header_for_new" style="width:100%;height:500px" data-bind="value: HeaderNew"></textarea>
                            </div>
                        </li>
                        <li>
                            <span class="k-link">Cabecera viejos</span>
                            <div class="linForm">
                                <textarea id="header_for_old" style="width:100%;height:500px" data-bind="value: HeaderOld"></textarea>
                            </div>
                        </li>
                        <li class="k-state-active">
                            <span class="k-link k-state-selected">Mensaje</span>
                            <div class="linForm">
                                <textarea id="body_message" style="width:100%;height:500px" data-bind="value: Message"></textarea>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <div class="actions">
                        <input type="button" id="btnBindFile" class="k-primary" value="Víncular seleccionados" data-bind="click: ToggleAttachments" data-parameter="true" />
                        <input type="button" id="btnUnbindFile" value="Desvíncular seleccionados" data-bind="click: ToggleAttachments" data-parameter="false" />
                    </div>

                    <div id='<?php echo $gridFilesName; ?>'></div>
                </div>

                <div>
                    <div class="actions">
                        <input type="button" id="btnSend" class="k-primary" value="Enviar seleccionados" data-bind="click: SendMails" data-parameter="false" />
                        <input type="button" id="btnResend" value="Re-enviar seleccionados" data-bind="click: SendMails" data-parameter="true" />
                    </div>

                    <div id='<?php echo $gridUsersName; ?>'></div>
                </div>
            </div>
        </div>
    </section>
</div>

<style type="text/css">
    #dialog-new-mail {
        padding-bottom: 80px;
        width: 100%;
        margin: 0 auto;
    }

    #dialog-new-mail .row {
        margin-bottom: 10px;
    }

    #dialog-new-mail #mail_id {
        display: none;
    }

    #dialog-new-mail .row label {
        max-width: 24.5%;
        min-width: 240px;
        display: inline-block;
        margin-top: 10px;
        font-size: 12pt;
        font-weight: bold;
    }

    #dialog-new-mail input[type="text"], #dialog-new-mail input[type="datetime"], #dialog-new-mail .k-datepicker {
        width: 100%;
    }

    #dialog-new-mail input[type="text"]#image_frontend, #dialog-new-mail input[type="text"]#image_carousel {
        width: 84%;
    }

    #dialog-new-mail hr {
        border: 1px solid rgb(66, 139, 202);
        margin: 30px 0;
    }

    #dialog-new-mail #gridPeople {
        margin: 10px auto 0 auto;
    }

    .k-widget.k-window {
        width: 950px;
    }
</style>

<?php
// Load notifications
require_once("Application/Views/Shared/WindowNotification.html");
require_once("Application/Views/Shared/WindowWaitingDialog.html");
require_once("Application/Views/Shared/WindowImageBrowser.html");
?>

<!-- View model -->
<script type="text/javascript">
    // Globals
    var PathImages = "<?php echo Constants::$ViewImagesFolder; ?>";

    var viewModel = kendo.observable({
        // Propiedades
        IdMail: 0,
        Subject: "",
        HeaderNew: "",
        HeaderOld: "",
        Message: "",
        DateSend: getCurrentDate(),
        Tematica: "",
        TematicaDesc: "",
        ImageCarousel: "",
        ImageFrontend: "",
        Active: false,
        IsConfeti: false,
        Action: 'Draft',
        checkedIdsAttachment: {},
        checkedIdsPeople: {},

        /**
        * Establece si deben o no habilitarse los tabs
        * @verified: 2015-08-03
        */
        EnableTabs: function() {
            return this.IdMail != 0
        },

        /**
         * Retorna el modo actual
         * @verified: 2015-08-03
         */
        SetAction: function() {
            this.Action = this.IdMail == 0 ? 'Draft' : 'Edit';
        },

        /**
         * Valida el correo actual
         * @return bool
         * @verified: 2015-08-03
         */
        ValidateMail: function() {
            if (this.Subject.length == 0) {
                ShowWarning({message: "El título es obligatorio."});
                $("#dialog-new-mail #subject").focus();
                return false;
            }
            if (this.Tematica.length == 0 && this.IsConfeti) {
                ShowWarning({ message: "La temática es obligatoria para los confetis." });
                $("#dialog-new-mail #tematica").focus();
                return false;
            }
            if (this.DateSend.length == 0) {
                ShowWarning({message: "La fecha de envío es obligatoria."});
                $("#dialog-new-mail #date_send").focus();
                return false;
            }
            if (this.Active) {
                if (this.HeaderNew.length == 0 || this.HeaderNew == "<div>​</div>") {
                    ShowWarning({message: "La cabecera para los nuevos registros es obligatoria"});
                    return false;
                }
                if (this.Message.length == 0 || this.Message == "<div>​</div>") {
                    ShowWarning({message: "El mensaje es obligatorio."});
                    return false;
                }
            }
            return true;
        },

        /**
         * Guarda el correo actual
         * @return bool
         * @verified: 2015-08-03
         */
        SaveMail: function() {
            if (!this.ValidateMail()) return false;
            showLoading(true);

            // Guardamos el viewmodel en local
            var self = this;

            // Establecemos la accion a realizar
            this.SetAction();

            var dataToSend = this.toJSON();
            dataToSend.DateSend = formatDate(this.DateSend);

            $.ajax({
                url: "<?php echo StringUtil::UrlAction($mailSaveAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify(dataToSend),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    showLoading(false);
                    if (response.success) {
                        // Remove class and show message
                        ShowInfo({ message: "Acción realizada", title: "Guardado de e-mail" });

                        // Assign data if action are draft
                        if (self.Action == 'Draft') {
                            self.set("IdMail", response.data.IdMail);
                            viewModel.set("EnableTabs", response.data.IdMail != 0);
                        }
                    } else {
                        AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                    }
                },
                error: function (response) {
                    showLoading(false);
                    AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                    console.log(response);
                }
            });
        },
        /**
         * Cambia el estado de los ficheros seleccionados con respecto al correo
         * @param e - Evento
         * @verified: 2015-08-03
         */
        ToggleAttachments: function (e) {
            e.preventDefault();

            // Getting data
            var bindData = $(e.sender.element).data('parameter');
            var selectedFiles = this.GetIdsSelected(this.checkedIdsAttachment);
            var dataToSend = {IdMail: this.IdMail, Files: selectedFiles, Bind: bindData};

            // Getting info about sent mails
            $.ajax({
                url: "<?php echo StringUtil::UrlAction($gridFilesToggleAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify(dataToSend),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    showLoading(false);
                    if (response.success) {
                        // Remove class and show message
                        ShowInfo({ message: "Acción realizada", title: "Datos des/vinculados" });

                        $('#<?php echo $gridFilesName; ?>').data('kendoGrid').dataSource.read();
                        $('#<?php echo $gridFilesName; ?>').data('kendoGrid').refresh();
                    } else {
                        AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                    }
                },
                error: function (response) {
                    showLoading(false);
                    AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                    console.log(response);
                }
            });
        },

        /**
         * Envia correos a las personas seleccionadas
         * @param e - Evento
         * @return bool
         * @verified: 2015-08-04
         */
        SendMails: function (e) {
            e.preventDefault();

            // Getting data
            var resend = $(e.sender.element).data('parameter');
            var checkedUsers = this.GetIdsSelected(this.checkedIdsPeople);
            var selectedUsers = [];
            var grid = $("#<?php echo $gridUsersName; ?>").data("kendoGrid");

            $.each(grid.dataSource.data(), function(key, item) {
                if (jQuery.inArray(item.user_id.toString(), checkedUsers) != -1) {
                    if (resend) {
                        // El envio se realiza a todos, se les haya enviado o no ya
                        selectedUsers.push(parseInt(item.user_id));
                    } else {
                        // Si es envio normal, solo enviaremos a los que no se haya enviado correctamente
                        if (item.mail_sent_status != 2) {
                            selectedUsers.push(parseInt(item.user_id));
                        }
                    }
                }
            });

            if (selectedUsers.length <= 0) return false;

            WaitBox("Espera por favor, estamos enviando los e-mails...", "Ejecutando acciones", 600, 90);

            var dataToSend = {IdMail: this.IdMail, Users: selectedUsers};

            // Getting info about sent mails
            $.ajax({
                url: "<?php echo StringUtil::UrlAction($mailSendAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify(dataToSend),
                contentType: "application/json",
                processData: false,
                timeout: 10 * 60 * 1000, // 10 minutos en milisegundos
                success: function (response) {
                    showLoading(false);
                    WaitDialog.CloseWindow();
                    if (response.success) {
                        // Remove class and show message
                        ShowInfo({ message: "Acción realizada", title: "Correos enviados" });

                        $('#<?php echo $gridUsersName; ?>').data('kendoGrid').dataSource.read();
                    } else {
                        AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                    }
                },
                error: function (response) {
                    showLoading(false);
                    WaitDialog.CloseWindow();
                    AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                    console.log(response);
                }
            });
        },

        /**
         * Inicializa los datos de la pantalla
         * @verified: 2015-08-03
         */
        Initialize: function () {
            var error = '<?php echo str_replace('"', '', json_encode($ViewBag->Error)); ?>';

            if (error != "")
            {
                AlertBox(error, 'Error', MessageBoxDialogs.ErrorIcon);
                return false;
            }

            var dataArray = '<?php echo $ViewBag->Data; ?>';
            var objArray = "";

            try {
                objArray = json_parse(dataArray);
            } catch (err) {
                try {
                    objArray = JSON.parse(JSON.stringify(dataArray));
                } catch (err) {
                    console.log('Cannot parse JSON object');
                }
            }
            if (typeof objArray == 'object') {
                // Load data
                viewModel.set("IdMail", objArray.Id);
                viewModel.set("Subject", objArray.Subject);
                viewModel.set("HeaderNew", objArray.HeaderNew);
                viewModel.set("HeaderOld", objArray.HeaderOld);
                viewModel.set("Message", objArray.Message);
                viewModel.set("DateSend", objArray.DateSend);
                viewModel.set("Tematica", objArray.Tematica);
                viewModel.set("TematicaDesc", objArray.TematicaDesc);
                viewModel.set("ImageFrontend", objArray.ImageFrontend);
                viewModel.set("ImageCarousel", objArray.ImageCarousel);
                viewModel.set("Active", eval(objArray.Active));
                viewModel.set("IsConfeti", eval(objArray.IsConfeti));
            } else {
                // Clean data
                viewModel.set("IdMail", 0);
                viewModel.set("Subject", "");
                viewModel.set("HeaderNew", "");
                viewModel.set("HeaderOld", "");
                viewModel.set("Message", "");
                viewModel.set("DateSend", getCurrentDate());
                viewModel.set("Tematica", "");
                viewModel.set("TematicaDesc", "");
                viewModel.set("ImageFrontend", "");
                viewModel.set("ImageCarousel", "");
                viewModel.set("Active", false);
                viewModel.set("IsConfeti", false);
            }
        },

        /**
         * Return a array of IDs of selected items on grid
         * @verified: 2015-08-03
         * @return Array
         **/
        GetIdsSelected: function (checkedIds) {
            var checked = [];
            for(var idx in checkedIds) {
                if(checkedIds[idx]) {
                    var dummy = parseInt(idx);
                    if (!isNaN(dummy)) {
                        checked.push(idx);
                    }
                }
            }
            return checked;
        },

        /**
         * Nos devuelve a la página principal
         * @verified: 2015-08-03
         */
        ReturnMainPage: function () {
            window.location.href = "<?php echo StringUtil::UrlAction(Constants::$IndexName, $controllerName, $areaName); ?>";
        },
        OpenImageBrowserFrontend: function () {
            var url = "<?php echo StringUtil::UrlAction($mailImageReadAction, $controllerName, $areaName); ?>";
            var urlThumbnail = "<?php echo $mailImageThumbnail; ?>";
            ImageBrowserBox(url, PathImages, urlThumbnail, "#image_hidden_frontend", { IdMail: this.IdMail });
        },
        OpenImageBrowserCarousel: function () {
            var url = "<?php echo StringUtil::UrlAction($mailImageReadAction, $controllerName, $areaName); ?>";
            var urlThumbnail = "<?php echo $mailImageThumbnail; ?>";
            ImageBrowserBox(url, PathImages, urlThumbnail, "#image_hidden_carousel", { IdMail: this.IdMail });
        },
        ChangeImageDataFrontend: function (e) {
            this.set("ImageFrontend", e.target.value);
        },
        ChangeImageDataCarousel: function (e) {
            this.set("ImageCarousel", e.target.value);
        }

    });

</script>

<!-- Inicializacion de grids -->
<script type="text/javascript">
    /* ############# Initializing data methods ############### */
    function initGridAttachments() {
        // Init components
        var grid = $("#<?php echo $gridFilesName ?>").kendoGrid({
            dataSource: {
                type: "odata",
                transport: {
                    read: function (options) {
                        options.data.IdMail = viewModel.IdMail;

                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($gridFilesReadAction, $controllerName, $areaName); ?>",
                            dataType: "json",
                            type: "POST",
                            data: kendo.stringify(options.data),
                            contentType: "application/json",
                            processData: false,
                            success: function (response) {
                                if (response.success) {
                                    options.success(response.data);
                                } else {
                                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                                    // Prevent default error
                                    options.success([]);
                                }
                            },
                            error: function (response) {
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                options.success([]);
                            }
                        });
                    }
                },
                schema: {
                    data: "rows",
                    total: "totalCount",
                    model: {
                        id: "file_id",
                        fields: {
                            file_id: { type: "number" },
                            file_name: { type: "string" },
                            file_url: { type: "string" },
                            full_url: { type: "string" },
                            file_type: { type: "string" },
                            mail_bind: { type: "string" },
                            file_date: { type: "date" }
                        }
                    }
                },
                error: function (e) {
                    e.preventDefault();
                },
                pageSize: 500,
                serverPaging: false,
                serverFiltering: false,
                serverSorting: false,
                sort: [
                    { field: 'mail_bind', dir: 'desc' },
                    { field: 'file_date', dir: 'desc' },
                ]
            },
            height: 550,
            filterable: true,
            resizable: true,
            groupable: true,
            reorderable: true,
            sortable: {
                mode: "multiple",
                allowUnsort: true
            },
            pageable: {
                numeric: false,
                previousNext: false,
                refresh: true,
            },
            scrollable: {
                virtual: true
            },
            dataBound: onDataBoundAttachments,
            columns: [
                {
                    template: "<input type='checkbox' class='checkbox' />",
                    width: "40px",
                    attributes: {
                        style: "text-align: center;"
                    }
                },{
                    field: "Image", title:"Imagen",
                    template: '#if(file_type == "Imagen") {#<img src="#:full_url#" width="60"/>#}#',
                    width: "80px"
                },
                { field: "file_id", title: "Id", hidden: true },
                { field: "file_name", title: "Nombre" },
                { field: "file_url", title: "URL", hidden: true },
                { field: "file_date", title: "Fecha", width: "150px", format: "{0:dd/MM/yyyy HH:mm}"},
                { field: "full_url", title: "URL" },
                { field: "file_type", title: "Tipo", width: "200px" },
                { field: "mail_bind", title: "Estado", width: "180px" }
            ]
        }).data('kendoGrid');

        // Configuring events
        grid.table.on("click", ".checkbox" , selectRowAttachment);

        //on click of the checkbox:
        function selectRowAttachment() {
            var checked = this.checked,
                row = $(this).closest("tr"),
                grid = $("#<?php echo $gridFilesName ?>").data("kendoGrid"),
                dataItem = grid.dataItem(row);

            viewModel.checkedIdsAttachment[dataItem.id] = checked;
            if (checked) {
                //-select the row
                row.addClass("k-state-selected");
            } else {
                //-remove selection
                row.removeClass("k-state-selected");
            }
        }

        //on dataBound event restore previous selected rows:
        function onDataBoundAttachments() {
            var view = this.dataSource.view();
            for(var i = 0; i < view.length;i++){
                if(viewModel.checkedIdsAttachment[view[i].id]){
                    this.tbody.find("tr[data-uid='" + view[i].uid + "']")
                        .addClass("k-state-selected")
                        .find(".checkbox")
                        .attr("checked","checked");
                }
            }
        }
    }

    function initGridPeople() {
        // Init components
        var grid = $("#<?php echo $gridUsersName; ?>").kendoGrid({
            dataSource: {
                type: "odata",
                transport: {
                    read: function (options) {
                        options.data.IdMail = viewModel.IdMail;

                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($gridUsersReadAction, $controllerName, $areaName); ?>",
                            dataType: "json",
                            type: "POST",
                            data: kendo.stringify(options.data),
                            contentType: "application/json",
                            processData: false,
                            success: function (response) {
                                if (response.success) {
                                    options.success(response.data);
                                } else {
                                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                                    // Prevent default error
                                    options.success([]);
                                }
                            },
                            error: function (response) {
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                options.success([]);
                            }
                        });
                    }
                },
                schema: {
                    data: "rows",
                    total: "totalCount",
                    model: {
                        id: "user_id",
                        fields: {
                            user_id: { type: "number" },
                            user_name: { type: "string" },
                            user_mail: { type: "string" },
                            mail_sent_date: { type: "string" },
                            mail_sent_status: { type: "number" },
                            mail_status_text: { type: "string" }
                        }
                    }
                },
                error: function (e) {
                    e.preventDefault();
                    if (e.errorThrown !== undefined) {
                        ShowError({message: e.errorThrown, title: "Server error"});
                    } else {
                        ShowError({message: "Cannot load users", title: "Server error"});
                    }
                },
                pageSize: 500,
                serverPaging: false,
                serverFiltering: false,
                serverSorting: false,
                sort: [
                    { field: 'mail_sent_status', dir: 'asc' },
                    { field: 'user_mail', dir: 'asc' },
                ]
            },
            height: 550,
            filterable: true,
            resizable: true,
            groupable: true,
            reorderable: true,
            sortable: {
                mode: "multiple",
                allowUnsort: true
            },
            pageable: {
                numeric: false,
                previousNext: false,
                refresh: true,
            },
            scrollable: {
                virtual: true
            },
            //selectable: "multiple",
            dataBound: onDataBoundPeople,
            columns: [
                {
                    template: "<input type='checkbox' class='checkbox' />",
                    headerTemplate: "<input type='checkbox' id='check-all' onclick='selectAllRowsPeople(this)'/>",
                    width: "40px",
                    attributes: {
                        style: "text-align: center;"
                    }
                },
                { field: "user_id", title: "Id", hidden: true },
                { field: "user_name", title: "Nombre", width: "250px" },
                { field: "user_mail", title: "E-Mail" },
                { field: "mail_sent_date", title: "Fecha envío", width: "160px" },
                { field: "mail_sent_status", title: "Estado", hidden: true },
                { field: "mail_status_text", title: "Estado", width: "200px" }
            ]
        }).data('kendoGrid');

        // Configuring events
        grid.table.on("click", ".checkbox" , selectRowPeople);

        //on click of the checkbox:
        function selectRowPeople() {
            var checked = this.checked,
                row = $(this).closest("tr"),
                grid = $("#<?php echo $gridUsersName; ?>").data("kendoGrid"),
                dataItem = grid.dataItem(row);

            viewModel.checkedIdsPeople[dataItem.id] = checked;
            if (checked) {
                //-select the row
                row.addClass("k-state-selected");
            } else {
                //-remove selection
                row.removeClass("k-state-selected");
            }
        }

        //on dataBound event restore previous selected rows:
        function onDataBoundPeople() {
            var view = this.dataSource.view();
            for(var i = 0; i < view.length;i++){
                if(viewModel.checkedIdsPeople[view[i].id]){
                    this.tbody.find("tr[data-uid='" + view[i].uid + "']")
                        .addClass("k-state-selected")
                        .find(".checkbox")
                        .attr("checked","checked");
                }
            }
        }
    }

    //on click of the check-all:
    function selectAllRowsPeople(ele) {
        var state = $(ele).is(':checked');
        var grid = $('#<?php echo $gridUsersName; ?>').data('kendoGrid');
        $.each(grid.dataSource.view(), function () {
            viewModel.checkedIdsPeople[this.id] = state;
        });
        if (state) {
            grid.select(grid.tbody.find(">tr"));
            $(".checkbox").prop('checked', true);
        } else {
            grid.clearSelection();
            $(".checkbox").prop('checked', false);
        }
    }

</script>

<!-- Inicializacion pantalla -->
<script type="text/javascript">
    /* ################# ON DOCUMENT READY ################# */
    $(document).ready(function() {
        // Init data
        viewModel.Initialize();

        // Init vars
        var toolsVar = [
            "bold", "italic", "underline", "strikethrough", "justifyLeft", "justifyCenter",
            "justifyRight", "justifyFull", "insertUnorderedList", "insertOrderedList",
            "indent", "outdent", "createLink", "unlink", "insertImage", "insertFile",
            "subscript", "superscript", "createTable", "addRowAbove", "addRowBelow",
            "addColumnLeft", "addColumnRight", "deleteRow", "deleteColumn", "viewHtml",
            "formatting", "cleanFormatting", "fontName", "fontSize", "foreColor", "backColor",
            {
                name: "person",
                tooltip: "Inserta un campo persona",
                template: "<a href='' role='button' class='k-tool k-group-start' title='Persona'>" +
                            "<img src='" + PathImages + "ui/btnUser.png' /></a>",
                exec: function(e) {
                    var editor = $(this).data("kendoEditor");
                    editor.exec("inserthtml", { value: "{DisplayUser}" });
                }
            }
        ];

        // Init gui
        $("#tabstrip").kendoTabStrip({
            animation:  {
                open: {
                    effects: "fadeIn"
                }
            }
        });

        // Init gui editors
        $('#header_for_new').kendoEditor({
            tools: toolsVar,
            imageBrowser: {
                path: PathImages,
                transport: {
                    read: function(options) {
                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($mailImageReadAction, $controllerName, $areaName); ?>",
                            type: "POST",
                            dataType: "json",
                            contentType: "application/json",
                            data: kendo.stringify({IdMail: viewModel.IdMail}),
                            success: function (response) {
                                if (response.success) {
                                    options.success(response.data.rows);
                                } else {
                                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                                    // Prevent default error
                                    options.success([]);
                                }
                            },
                            error: function (response) {
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                options.success([]);
                            }
                        });
                    },
                    thumbnailUrl: "<?php echo $mailImageThumbnail; ?>",
                    imageUrl: function (path, name) {
                        return "<?php echo Constants::GetMailImagesPath(); ?>" + "/" + path;
                    }
                }
            }
        });
        $('#header_for_old').kendoEditor({
            tools: toolsVar,
            imageBrowser: {
                path: PathImages,
                transport: {
                    read: function(options) {
                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($mailImageReadAction, $controllerName, $areaName); ?>",
                            type: "POST",
                            dataType: "json",
                            contentType: "application/json",
                            data: kendo.stringify({IdMail: viewModel.IdMail}),
                            success: function (response) {
                                if (response.success) {
                                    options.success(response.data.rows);
                                } else {
                                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                                    // Prevent default error
                                    options.success([]);
                                }
                            },
                            error: function (response) {
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                options.success([]);
                            }
                        });
                    },
                    thumbnailUrl: "<?php echo $mailImageThumbnail; ?>",
                    imageUrl: function (path, name) {
                        return "<?php echo Constants::GetMailImagesPath(); ?>" + "/" + path;
                    }
                }
            }
        });
        $('#body_message').kendoEditor({
            tools: toolsVar,
            imageBrowser: {
                path: PathImages,
                transport: {
                    read: function(options) {
                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($mailImageReadAction, $controllerName, $areaName); ?>",
                            type: "POST",
                            dataType: "json",
                            contentType: "application/json",
                            data: kendo.stringify({IdMail: viewModel.IdMail}),
                            success: function (response) {
                                if (response.success) {
                                    options.success(response.data.rows);
                                } else {
                                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                                    // Prevent default error
                                    options.success([]);
                                }
                            },
                            error: function (response) {
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                options.success([]);
                            }
                        });
                    },
                    thumbnailUrl: "<?php echo $mailImageThumbnail; ?>",
                    imageUrl: function (path, name) {
                        return "<?php echo Constants::GetMailImagesPath(); ?>" + "/" + path;
                    }
                }
            }
        });
        $("#date_send").kendoDatePicker({
            format: "dd/MM/yyyy",
            parseFormats: ["dd/MM/yyyy"]
        });
        // Init buttons
        $("#btnSave").kendoButton();
        $("#btnCancel").kendoButton();
        $("#btnBindFile").kendoButton();
        $("#btnUnbindFile").kendoButton();
        $("#btnSend").kendoButton();
        $("#btnResend").kendoButton();

        $("#panelbar").kendoPanelBar({
            expandMode: "single",
        });

        // Binding
        kendo.bind($("#dialog-new-mail"), viewModel);
        initGridAttachments();
        initGridPeople();

        $(window).resize(function () {
            resizeGrid("<?php echo "#".$gridFilesName; ?>", 200);
            resizeGrid("<?php echo "#".$gridUsersName; ?>", 200);
        });

        setTimeout(function () {
            resizeGrid("<?php echo "#".$gridFilesName; ?>", 200);
        }, 100);
        setTimeout(function () {
            resizeGrid("<?php echo "#".$gridUsersName; ?>", 200);
        }, 100);
    });
</script>