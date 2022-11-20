<?php
// Controller names data
$controllerName = "Blog";
$areaName = Constants::$PanelAreaName;

// Actions
$gridReadAction = "Posts_Read";
$postManageAction = "Manage";
$postDeleteAction = "DeletePost";

// Misc
$gridName = "gridPosts";

// Load notifications
require_once("Application/Views/Shared/WindowNotification.html");
require_once("Application/Views/Shared/WindowConfirmDelete.html");
?>

<!-- Main -->
<div id="main">
    <!-- Intro -->
    <section id="options">
        <header>
            <h2>Administraci&oacute;n del blog</h2>
        </header>
        
        <div id="posts-management">
            <div id="tabstrip" class="k-content">
                <div id="<?php echo $gridName; ?>"></div>
            </div>
        </div>
    </section>

</div>

<script type="text/javascript">

    function deletePost(dataItem) {
        var dataToSend = { Id: dataItem.PostId };

        $.ajax({
            url: "<?php echo StringUtil::UrlAction($postDeleteAction, $controllerName, $areaName); ?>",
            dataType: "json",
            type: "POST",
            data: kendo.stringify(dataToSend),
            contentType: "application/json",
            processData: false,
            success: function (response) {
                showLoading(false);
                if (response.success) {
                    $("#<?php echo $gridName; ?>").data('kendoGrid').dataSource.read();
                    ShowInfo({ message: "Acción realizada", title: "Borrado de entrada" });
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
                            url: "<?php echo StringUtil::UrlAction($gridReadAction, $controllerName, $areaName); ?>",
                            dataType: "json",
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
                        id: "PostId",
                        fields: {
                            PostId: { type: "number" },
                            PostSubject: { type: "string" },
                            PostPublishedDate: { type: "date" },
                            PostIsActive: { type: "boolean" }
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
                sort: { field: "PostPublishedDate", dir: "desc" },
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
            editable: false,
            toolbar: [{
                name: "custom-new",
                text: "Añadir nuevo",
                imageClass: "k-icon k-add"
            }, {
                name: "excel",
                text: "Exportar a excel"
            }],
            excel: {
                fileName: "Posts.xlsx",
                filterable: true,
                allPages: true
            },
            columns: [
                {
                    field: "PostId",
                    title: "Id",
                    hidden: true
                }, {
                    field: "PostSubject",
                    title: "Entrada"
                }, {
                    field: "PostPublishedDate",
                    title: "Fecha",
                    width: "150px",
                    format: "{0:dd/MM/yyyy}"
                }, {
                    field: "PostIsActive",
                    title: "Público",
                    width: "140px",
                    template: function (dataItem) {
                        if (dataItem.PostIsActive)
                            return "<input type='checkbox' checked='checked' disabled='disabled' />";
                        else
                            return "<input type='checkbox' disabled='disabled' />";
                    },
                    attributes: {
                        style: "text-align: center;"
                    }
                }, {
                    command: [
                        { name: "custom-edit", text: "Editar", imageClass: "k-icon k-edit" },
                        { name: "custom-delete", text: "Borrar", imageClass: "k-icon k-delete" }
                    ],
                    title: "&nbsp;",
                    width: "180px"
                }
            ]
        });

        // Se ejecuta cuando se crea un correo
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-new", function (e) {
            //custom actions
            e.preventDefault();
            window.location.href = "<?php echo StringUtil::UrlAction($postManageAction, $controllerName, $areaName); ?>";
        });

        // Se ejecuta si modifica un correo
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-edit", function (e) {
            //custom actions
            e.preventDefault();
            var dataItem = $("#<?php echo $gridName; ?>").data('kendoGrid').dataItem($(e.currentTarget).closest("tr"));
            window.location.href = "<?php echo StringUtil::UrlAction($postManageAction, $controllerName, $areaName); ?>/" + dataItem.PostId;
        });

        // Se ejecuta si se elimina un correo
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-delete", function (e) {
            //custom actions
            e.preventDefault();
            var dataItem = $("#<?php echo $gridName; ?>").data('kendoGrid').dataItem($(e.currentTarget).closest("tr"));
            DeleteBox(deletePost, dataItem);
        });


        $(window).resize(function () {
            resizeGrid("<?php echo "#".$gridName; ?>");
        });

        setTimeout(function () { resizeGrid("<?php echo "#".$gridName; ?>"); }, 100);
    });
</script>