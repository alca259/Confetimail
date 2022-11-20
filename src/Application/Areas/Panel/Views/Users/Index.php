<?php
// Controller names data
$controllerName = "Users";
$areaName = Constants::$PanelAreaName;

// Actions
$gridReadAction = "Users_Read";
$gridCUDAction = "CUD_Users";
$userResetActionName = "ResetPassword_User";

// Misc
$gridName = "gridUsers";

// TODO: Dar funcionalidad de reseteo de contraseña
?>

<script type="text/javascript">
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
                    },
                    update: function (options) {
                        options.data.Action = "update";
                        showLoading(true);

                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($gridCUDAction, $controllerName, $areaName); ?>",
                            dataType: "json",
                            type: "POST",
                            data: kendo.stringify(options.data),
                            contentType: "application/json",
                            processData: false,
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
                                showLoading(false);
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                $("#<?php echo $gridName; ?>").data("kendoGrid").cancelChanges();
                            }
                        });
                    },
                    destroy: function (options) {
                        options.data.Action = "delete";
                        showLoading(true);

                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($gridCUDAction, $controllerName, $areaName); ?>",
                            dataType: "json",
                            type: "POST",
                            data: kendo.stringify(options.data),
                            contentType: "application/json",
                            processData: false,
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
                                showLoading(false);
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                $("#<?php echo $gridName; ?>").data("kendoGrid").cancelChanges();
                            }
                        });
                    },
                    create: function (options) {
                        options.data.Action = "create";
                        showLoading(true);

                        $.ajax({
                            url: "<?php echo StringUtil::UrlAction($gridCUDAction, $controllerName, $areaName); ?>",
                            dataType: "json",
                            type: "POST",
                            data: kendo.stringify(options.data),
                            contentType: "application/json",
                            processData: false,
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
                                showLoading(false);
                                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                                console.log(response);
                                // Prevent default error
                                $("#<?php echo $gridName; ?>").data("kendoGrid").cancelChanges();
                            }
                        });
                    }
                },
                schema: {
                    data: "rows",
                    total: "totalCount",
                    model: {
                        id: "UserId",
                        fields: {
                            UserId: { type: "number", editable: false },
                            CreateDate: { type: "date", editable: false },
                            UserDisplayName: { type: "string", validation: { required: true, nullable: false }},
                            UserName: { type: "string", validation: { required: true, nullable: false }},
                            UserEmail: { type: "string", validation: { required: true, nullable: false }},
                            UserIsSubscribed: { type: "boolean" },
                            UserIsActive: { type: "boolean" },
                            UserIsAdmin: { type: "boolean" }
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
                sort: { field: "UserEmail" , dir: "asc" }
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
            editable: "inline",
            toolbar: [ {
                name: "create",
                text: "Añadir nuevo"
            }, {
                name: "excel",
                text: "Exportar a excel"
            }],
            excel: {
                fileName: "Users.xlsx",
                filterable: true,
                allPages: true
            },
            columns: [
                {
                    field: "UserId",
                    title: "Id",
                    hidden: true
                }, {
                    field: "CreateDate",
                    title: "Registrado",
                    width: "150px",
                    format: "{0:dd/MM/yyyy}"
                }, {
                    field: "UserDisplayName",
                    title: "Nombre"
                }, {
                    field: "UserName",
                    title: "Usuario",
                    width: "150px"
                }, {
                    field: "UserEmail",
                    title: "Email",
                    width: "300px"
                }, {
                    field: "UserIsSubscribed",
                    title: "Suscrito",
                    width: "90px",
                    template: function(dataItem) {
                        if (dataItem.UserIsSubscribed)
                            return "<input type='checkbox' checked='checked' disabled='disabled' />";
                        else
                            return "<input type='checkbox' disabled='disabled' />";
                    },
                    attributes: {
                        style: "text-align: center;"
                    }
                }, {
                    field: "UserIsActive",
                    title: "Activo",
                    width: "80px",
                    template: function(dataItem) {
                        if (dataItem.UserIsActive)
                            return "<input type='checkbox' checked='checked' disabled='disabled' />";
                        else
                            return "<input type='checkbox' disabled='disabled' />";
                    },
                    attributes: {
                        style: "text-align: center;"
                    }
                }, {
                    field: "UserIsAdmin",
                    title: "Admin",
                    width: "80px",
                    template: function(dataItem) {
                        if (dataItem.UserIsAdmin)
                            return "<input type='checkbox' checked='checked' disabled='disabled' />";
                        else
                            return "<input type='checkbox' disabled='disabled' />";
                    },
                    attributes: {
                        style: "text-align: center;"
                    }
                }, {
                    command: [{
                        name: "edit",
                        text: {
                            edit: "Editar",
                            update: "Guardar",
                            cancel: "Cancelar"
                        }
                    }, {
                        name: "destroy",
                        text: "Borrar"
                    }, {
                        name: "custom-reset",
                        text: "Resetear"
                    }],
                    title: " ",
                    width: "250px"
                }
            ]
        });

        $(window).resize(function () {
            resizeGrid("<?php echo "#".$gridName; ?>");
        });

        setTimeout(function () { resizeGrid("<?php echo "#".$gridName; ?>"); }, 100);

        // Reinicio de clave
        $("#<?php echo $gridName; ?>").on("click", ".k-grid-custom-reset", function (e) {
            //custom actions
            e.preventDefault();
            var dataItem = $("#<?php echo $gridName; ?>").data('kendoGrid').dataItem($(e.currentTarget).closest("tr"));
            
            var dataToSend = { UserId: dataItem.UserId };
            showLoading(true);

            $.ajax({
                url: "<?php echo StringUtil::UrlAction($userResetActionName, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify(dataToSend),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    showLoading(false);
                    if (response.success) {
                        $("#<?php echo $gridName; ?>").data('kendoGrid').dataSource.read();
                        AlertBox(response.message, "Acción realizada", MessageBoxDialogs.InfoIcon);
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
    });

</script>

<!-- Main -->
<div id="main">

    <!-- Intro -->
    <section id="options">
        <header>
            <h2>Administraci&oacute;n de usuarios</h2>
        </header>

        <div id="users-management">
            <div id="tabstrip" class="k-content">
                <div id="<?php echo $gridName; ?>"></div>
            </div>
        </div>
    </section>

</div>