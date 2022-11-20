<?php
// Controller names data
$controllerName = "Blog";
$areaName = Constants::$PanelAreaName;

// Actions
$imagesReadAction = "Images_Read";
$commentsReadAction = "Comments_Read";
$commentsDeleteAction = "Comment_Delete";
$postSaveAction = "SavePost";

// Misc
$imageThumbnail = "/Application/Helpers/Thumbnail";
$gridName = "gridComments";
?>

<!-- Main -->
<div id="main">

    <!-- Intro -->
    <section id="options">

        <header>
            <h2>Administraci&oacute;n de entradas</h2>
        </header>

        <div id="dialog-new-post">
            <div id="tabstrip" class="k-content">

                <div class="row">
                    <div class="col-md-offset-9 col-md-3">
                        <input type="button" id="btnSave" value="Guardar" class="k-primary" data-bind="click: SavePost" />
                        <input type="button" id="btnCancel" value="Volver atr&aacute;s" data-bind="click: ReturnMainPage" />
                    </div>
                </div>
                <hr />
                <input type="hidden" name="post_id" id="post_id" data-bind="value: Id" />

                <div class="row">
                    <div class="col-md-2"><label for="subject">T&iacute;tulo</label></div>
                    <div class="col-md-4"><input type="text" name="subject" id="subject" class="k-textbox" data-bind="value: Subject" /></div>
                    <div class="col-md-1"></div>
                    <div class="col-md-2"><label for="date_published">Fecha de publicaci&oacute;n</label></div>
                    <div class="col-md-3"><input type="datetime" name="date_published" id="date_published" data-bind="value: DatePublished" /></div>
                </div>

                <div class="row">
                    <div class="col-md-2"><label for="active">&iquest;P&uacute;blico?</label></div>
                    <div class="col-md-4"><input type="checkbox" name="active" id="active" class="k-input" data-bind="checked: Active" /></div>
                    <div class="col-md-1"></div>
                    <div class="col-md-2"><label for="image_frontend">Portada</label></div>
                    <div class="col-md-3">
                        <input type="text" name="image_frontend" id="image_frontend" data-bind="value: ImageFrontend" />
                        <input type="hidden" name="image_hidden" id="image_hidden" class="k-textbox" data-bind="events: { change: ChangeImageData }" />
                        <input type="button" id="btnOpenImageBrowser" value="..." data-bind="click: OpenImageBrowser" />
                    </div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-md-12">
                        <label for="post_body">Entrada</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea id="post_body" style="height: 500px" data-bind="value: PostBody"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><label>Comentarios</label></div>
                </div>
                <div id="comments">
                    <div id="<?php echo $gridName; ?>"></div>
                </div>
            </div>

        </div>

    </section>
</div>

<style type="text/css">
    #dialog-new-post {
        padding-bottom: 40px;
        width: 100%;
        margin: 0 auto;
    }

    #dialog-new-post #tabstrip {
        padding: 10px 15px 5px 15px;
    }

    #dialog-new-post #tabstrip .row {
        margin-bottom: 5px;
    }

    #dialog-new-post input[type="text"], #dialog-new-post input[type="datetime"], #dialog-new-post .k-datepicker {
        width: 100%;
    }

    #dialog-new-post input[type="text"]#image_frontend {
        width: 84%;
    }

    #dialog-new-post hr {
        border: 1px solid rgb(66, 139, 202);
        margin: 30px 0;
    }

    .k-widget.k-window {
        width: 950px;
    }
</style>

<?php
// Load notifications
require_once("Application/Views/Shared/WindowNotification.html");
require_once("Application/Views/Shared/WindowImageBrowser.html");
require_once("Application/Views/Shared/WindowConfirmDelete.html");
?>

<!-- View model -->
<script type="text/javascript">
    // Globals
    var PathImages = "<?php echo Constants::$ViewImagesFolder; ?>";

    var viewModel = kendo.observable({
        // Propiedades
        Id: 0,
        Subject: "",
        PostBody: "",
        ImageFrontend: "",
        DatePublished: getCurrentDate(),
        Active: false,
        Action: 'Draft',

        /**
         * Retorna el modo actual
         * @verified: 2015-08-03
         */
        SetAction: function () {
            this.Action = this.Id == 0 ? 'Draft' : 'Edit';
        },

        /**
         * Valida la entrada actual
         * @return bool
         * @verified: 2015-08-03
         */
        ValidatePost: function () {
            if (this.Subject.length == 0) {
                ShowWarning({ message: "El título es obligatorio." });
                $("#dialog-new-post #subject").focus();
                return false;
            }
            if (this.PostBody.length == 0 && this.Active) {
                ShowWarning({ message: "La entrada es obligatoria." });
                $("#dialog-new-post #post_body").focus();
                return false;
            }
            if (this.DatePublished.length == 0 && this.Active) {
                ShowWarning({ message: "La fecha de publicación es obligatoria." });
                $("#dialog-new-post #date_published").focus();
                return false;
            }
            if (this.ImageFrontend.length == 0 && this.Active) {
                ShowWarning({ message: "La imagen de portada es obligatoria." });
                $("#dialog-new-post #image_frontend").focus();
                return false;
            }
            return true;
        },

        /**
         * Guarda la entrada actual
         * @return bool
         * @verified: 2015-08-03
         */
        SavePost: function () {
            if (!this.ValidatePost()) return false;
            showLoading(true);

            // Guardamos el viewmodel en local
            var self = this;

            // Establecemos la accion a realizar
            this.SetAction();

            var dataToSend = this.toJSON();
            dataToSend.DatePublished = formatDate(this.DatePublished);

            $.ajax({
                url: "<?php echo StringUtil::UrlAction($postSaveAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify(dataToSend),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    showLoading(false);
                    if (response.success) {
                        // Remove class and show message
                        ShowInfo({ message: "Acción realizada", title: "Guardado de entrada" });

                        // Assign data if action are draft
                        if (self.Action == 'Draft') {
                            self.set("Id", response.data.Id);
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
         * Inicializa los datos de la pantalla
         * @verified: 2015-08-03
         */
        Initialize: function () {
            var error = '<?php echo str_replace('"', '', json_encode($ViewBag->Error)); ?>';

            if (error != "") {
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
                viewModel.set("Id", objArray.Id);
                viewModel.set("Subject", objArray.Subject);
                viewModel.set("PostBody", objArray.PostBody);
                viewModel.set("DatePublished", objArray.DatePublished);
                viewModel.set("ImageFrontend", objArray.ImageFrontend);
                viewModel.set("Active", eval(objArray.Active));
            } else {
                // Clean data
                viewModel.set("Id", 0);
                viewModel.set("Subject", "");
                viewModel.set("PostBody", "");
                viewModel.set("DatePublished", getCurrentDate());
                viewModel.set("ImageFrontend", "");
                viewModel.set("Active", false);
            }
        },

        /**
         * Nos devuelve a la página principal
         * @verified: 2015-08-03
         */
        ReturnMainPage: function () {
            window.location.href = "<?php echo StringUtil::UrlAction(Constants::$IndexName, $controllerName, $areaName); ?>";
        },

        OpenImageBrowser: function () {
            var url = "<?php echo StringUtil::UrlAction($imagesReadAction, $controllerName, $areaName); ?>";
            var urlThumbnail = "<?php echo $imageThumbnail; ?>";
            ImageBrowserBox(url, PathImages, urlThumbnail, "#image_hidden");
        },
        ChangeImageData: function (e) {
            this.set("ImageFrontend", e.target.value);
        }

    });

</script>

<!-- Inicializacion pantalla -->
<script type="text/javascript">
    /* ################# ON DOCUMENT READY ################# */
    $(document).ready(function () {
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
			    exec: function (e) {
			        var editor = $(this).data("kendoEditor");
			        editor.exec("inserthtml", { value: "{DisplayUser}" });
			    }
			}
        ];

        // Init gui editors
        $('#post_body').kendoEditor({
            tools: toolsVar,
            imageBrowser: {
                path: PathImages,
                transport: {
                    read: function (options) {
                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($imagesReadAction, $controllerName, $areaName); ?>",
                            type: "POST",
                            dataType: "json",
                            contentType: "application/json",
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
                    thumbnailUrl: "<?php echo $imageThumbnail; ?>",
                    imageUrl: function (path, name) {
                        //return "<?php echo Constants::GetMailImagesPath(); ?>" + "/" + path;
                        return "/" + path;
				    }
                }
            }
        });
        
        $("#date_published").kendoDatePicker({
            format: "dd/MM/yyyy",
            parseFormats: ["dd/MM/yyyy"]
        });

        // Init buttons
        $("#btnSave").kendoButton();
        $("#btnCancel").kendoButton();
        $("#btnOpenImageBrowser").kendoButton();

        // Binding
        kendo.bind($("#dialog-new-post"), viewModel);
    });
</script>

<!-- Control de comentarios -->
<script type="text/javascript">
    function deleteReview(dataItem) {
        var dataToSend = { Id: dataItem.CommentId };

        $.ajax({
            url: "<?php echo StringUtil::UrlAction($commentsDeleteAction, $controllerName, $areaName); ?>",
            dataType: "json",
            type: "POST",
            data: kendo.stringify(dataToSend),
            contentType: "application/json",
            processData: false,
            success: function (response) {
                showLoading(false);
                if (response.success) {
                    $("#<?php echo $gridName; ?>").data('kendoGrid').dataSource.read();
                    ShowInfo({ message: "Acción realizada", title: "Borrado de comentario" });
                } else {
                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                }
            },
            error: function (response) {
                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                console.log(response);
            }
        });
    }

    $(document).ready(function () {

        // Init components
        $("#<?php echo $gridName; ?>").kendoGrid({
            dataSource: {
                type: "odata",
                transport: {
                    read: function (options) {
                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($commentsReadAction, $controllerName, $areaName); ?>",
                            dataType: "json",
                            data: kendo.stringify({ PostId: viewModel.Id }),
                            type: "POST",
                            success: function (response) {
                                showLoading(false);
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
                        id: "CommentId",
                        fields: {
                            CommentId: { type: "number" },
                            CommentUser: { type: "string" },
                            CommentPublishedDate: { type: "date" },
                            CommentComment: { type: "string" },
                        }
                    }
                },
                error: function (e) {
                    e.preventDefault();
                },
                pageSize: 20,
                serverPaging: false,
                serverFiltering: false,
                serverSorting: false,
            },
            height: 550,
            filterable: true,
            resizable: true,
            groupable: true,
            reorderable: true,
            sortable: true,
            pageable: {
                numeric: false,
                previousNext: false,
                refresh: true,
            },
            scrollable: {
                virtual: true
            },
            editable: false,
            toolbar: [{
                name: "excel",
                text: "Exportar a excel"
            }],
            excel: {
                fileName: "Comments.xlsx",
                filterable: true,
                allPages: true
            },
            columns: [
                {
                    field: "CommentId",
                    title: "Id",
                    hidden: true
                }, {
                    field: "CommentUser",
                    title: "Usuario",
                    width: "150px"
                }, {
                    field: "CommentPublishedDate",
                    title: "Fecha",
                    width: "150px",
                    format: "{0:dd/MM/yyyy HH:mm:ss}"
                }, {
                    field: "CommentComment",
                    title: "Comentario"
                }, {
                    command: [
                        { name: "custom-delete", text: "Borrar", imageClass: "k-icon k-delete" }
                    ],
                    title: "&nbsp;",
                    width: "180px"
                }
            ]
        });

        // Se ejecuta si se elimina un comentario
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-delete", function (e) {
            //custom actions
            e.preventDefault();
            var dataItem = $("#<?php echo $gridName; ?>").data('kendoGrid').dataItem($(e.currentTarget).closest("tr"));
            DeleteBox(deleteReview, dataItem);
        });

    });
</script>