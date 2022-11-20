<?php
// Controller names data
$controllerName = "Structure";
$areaName = Constants::$PanelAreaName;

// Actions
$gridReadAction = "Models_Read";
$modelChangeAction = "ReloadModel";
$modelSearchAction = "SearchModels";

// Misc
$gridName = "gridStructure";

// Required partials
require_once('Application/Views/Shared/WindowConfirmDelete.html');
require_once('Application/Views/Shared/WindowNotification.html');
?>

<script type="text/javascript">

    function crudModel_Callback(params) {
        var dataItem = params.dataItem;
        var action = params.action;
        var message = params.message;
        crudModel(dataItem, action, message);
    }

    function crudModel(dataItem, action, message) {
        var dataToSend = { IdModel: dataItem.ModelId, Action: action };
        showLoading(true);

        $.ajax({
            url: "<?php echo StringUtil::UrlAction($modelChangeAction, $controllerName, $areaName); ?>",
            dataType: "json",
            type: "POST",
            data: kendo.stringify(dataToSend),
            contentType: "application/json",
            processData: false,
            success: function (response) {
                showLoading(false);
                if (response.success) {
                    $("#<?php echo $gridName; ?>").data('kendoGrid').dataSource.read();
                    ShowInfo({ message: "Acción realizada", title: message });
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
    }

    function onDataBound(e) {
        var grid = $("#<?php echo $gridName; ?>").data("kendoGrid");
        var gridData = grid.dataSource.view();

        for (var i = 0; i < gridData.length; i++) {
            var currentUid = gridData[i].uid;

            var currentRow = grid.table.find("tr[data-uid='" + currentUid + "']");
            var installButton = $(currentRow).find(".k-grid-custom-install");
            var upgradeButton = $(currentRow).find(".k-grid-custom-upgrade");
            var dropButton = $(currentRow).find(".k-grid-custom-drop");

            // Ocultamos todos
            installButton.hide();
            upgradeButton.hide();
            dropButton.hide();

            // Si no está instalado, mostramos el boton de instalar, si no, mostramos el resto
            if (!gridData[i].ModelIsActive) {
                installButton.show();
            } else {
                upgradeButton.show();
                dropButton.show();
            }
        }
    }

    $(document).ready(function() {

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
                        id: "ModelId",
                        fields: {
                            ModelId: { type: "number" },
                            ModelName: { type: "string" },
                            ModelDesc: { type: "string" },
                            ModelIsActive: { type: "boolean" }
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
                sort: { field: "ModelName", dir: "asc" }
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
            dataBound: onDataBound,
            toolbar: [{
                name: "custom-search",
                text: "Buscar modelos",
                imageClass: "k-icon k-i-search"
            }, {
                name: "excel",
                text: "Exportar a excel"
            }],
            excel: {
                fileName: "Models.xlsx",
                filterable: true,
                allPages: true
            },
            columns: [
                {
                    field: "ModelId",
                    title: "Id",
                    hidden: true
                }, {
                    field: "ModelName",
                    title: "Nombre"
                },{
                    field: "ModelDesc",
                    title: "Descripción"
                }, {
                    field: "ModelIsActive",
                    title: "Instalado",
                    width: "140px",
                    template: function(dataItem) {
                        if (dataItem.ModelIsActive)
                            return "<input type='checkbox' checked='checked' disabled='disabled' />";
                        else
                            return "<input type='checkbox' disabled='disabled' />";
                    },
                    attributes: {
                        style: "text-align: center;"
                    }
                }, {
                    command: [
                        { name: "custom-install", text: "Instalar", imageClass: "k-icon k-i-plus" },
                        { name: "custom-upgrade", text: "Actualizar", imageClass: "k-icon k-i-refresh" },
                        { name: "custom-drop", text: "Borrar", imageClass: "k-icon k-delete" }
                    ],
                    title: "&nbsp;",
                    width: "200px"
                }
            ]
        });

        // Se ejecuta cuando se busca modelos nuevos
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-search", function (e) {
            //custom actions
            e.preventDefault();
            showLoading(true);

            $.ajax({
                url: "<?php echo StringUtil::UrlAction($modelSearchAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                success: function (response) {
                    showLoading(false);
                    if (response.success) {
                        $("#<?php echo $gridName; ?>").data('kendoGrid').dataSource.read();
                        ShowInfo({ message: "Acción realizada", title: "Modelo recargado" });
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
        });

        // Se ejecuta si instala un modelo
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-install", function (e) {
            //custom actions
            e.preventDefault();
            var dataItem = $("#<?php echo $gridName; ?>").data('kendoGrid').dataItem($(e.currentTarget).closest("tr"));
            crudModel(dataItem, "Install", "Modelo instalado");
        });

        // Se ejecuta si se actualiza un modelo
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-upgrade", function (e) {
            //custom actions
            e.preventDefault();
            var dataItem = $("#<?php echo $gridName; ?>").data('kendoGrid').dataItem($(e.currentTarget).closest("tr"));
            crudModel(dataItem, "Upgrade", "Modelo actualizado");
        });

        // Se ejecuta si se elimina un modelo
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-drop", function (e) {
            //custom actions
            e.preventDefault();
            var dataItem = $("#<?php echo $gridName; ?>").data('kendoGrid').dataItem($(e.currentTarget).closest("tr"));
            DeleteBox(crudModel_Callback, {dataItem: dataItem, action: "Drop", message: "Modelo eliminado"});
        });

        $(window).resize(function () {
            resizeGrid("<?php echo "#".$gridName; ?>");
        });

        setTimeout(function () { resizeGrid("<?php echo "#".$gridName; ?>"); }, 100);
    });
</script>

<!-- Main -->
<div id="main">

    <!-- Intro -->
    <section id="options">
        <header>
            <h2>Administraci&oacute;n de modelos</h2>
        </header>

        <div id="models-management">
            <div id="tabstrip" class="k-content">
                <div id="<?php echo $gridName; ?>"></div>
            </div>
        </div>
    </section>
    
</div>
