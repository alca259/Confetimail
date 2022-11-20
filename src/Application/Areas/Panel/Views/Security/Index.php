<?php
// Controller names data
$controllerName = "Security";
$controllerUserName = "Users";
$areaName = Constants::$PanelAreaName;

// Actions
$comboUsersAction = "GetComboboxUsers";
$permissionsReadAction = "Permissions_Read";
$permissionsSaveAction = "Permissions_Save";
$permissionsGeneralReadAction = "PermissionsGeneral_Read";
$permissionsGeneralSaveAction = "PermissionsGeneral_Save";

// Var names
$treeName = "treePermissions";
$treeNameGeneral = "treeGeneral";
$comboUserName = "userSelect";

// Required partials
require_once('Application/Views/Shared/WindowNotification.html');
?>
<style type="text/css">
    div#permissions-management #treePermissions .k-sprite {
        background-image: url("/Public/img/ui/treeview-security.png");
    }

    div#permissions-management #treeGeneral .k-sprite {
        background-image: url("/Public/img/ui/treeview-security.png");
    }

    div#permissions-management .rootfolder {
        background-position: 0 0;
    }

    div#permissions-management .folder {
        background-position: 0 -16px;
    }

    div#permissions-management .perm_read {
        background-position: 0 -32px;
    }

    div#permissions-management .perm_write {
        background-position: 0 -48px;
    }

    div#permissions-management .perm_create {
        background-position: 0 -64px;
    }

    div#permissions-management .perm_unlink {
        background-position: 0 -80px;
    }

    div#permissions-management .box-col {
        min-width: 300px;
    }

    /* fix for style regression, available in internal builds */
    div#permissions-management .k-treeview {
        margin-top: 20px;
    }

        div#permissions-management .k-treeview .k-checkbox {
            opacity: 1;
            width: auto;
        }

    div#permissions-management .k-combobox {
        width: 100%;
    }

    div#permissions-management #arbol {
        display: none;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        // Obtenemos los datos
        var dataAdapterUsers = new kendo.data.DataSource({
            transport: {
                read: function (options) {
                    var dataToSend = "";

                    $.ajax({
                        url: "<?php echo StringUtil::UrlAction($comboUsersAction, $controllerUserName, $areaName); ?>",
                        dataType: "json",
                        type: "POST",
                        data: kendo.stringify(dataToSend),
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
            }
        });

	    var dataAdapterModels = new kendo.data.HierarchicalDataSource({
	        transport: {
	            read: function (options) {
	                var dataToSend = options.data;

	                $.ajax({
	                    url: "<?php echo StringUtil::UrlAction($permissionsReadAction, $controllerName, $areaName); ?>",
                        dataType: "json",
                        type: "POST",
                        data: kendo.stringify(dataToSend),
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
                model: {
                    id: "id",
                    text: "text",
                    children: "items"
                }
            }
        });

	    var dataAdapterGeneralModels = new kendo.data.HierarchicalDataSource({
	        transport: {
	            read: function (options) {
	                $.ajax({
	                    url: "<?php echo StringUtil::UrlAction($permissionsGeneralReadAction, $controllerName, $areaName); ?>",
	                    dataType: "json",
	                    type: "POST",
	                    contentType: "application/json",
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
	            model: {
	                id: "id",
	                text: "text",
	                children: "items"
	            }
	        }
	    });

	    // Creamos los arboles de kendo
	    $("#<?php echo $treeName; ?>").kendoTreeView({
	        checkboxes: {
	            checkChildren: true
	        },
	        dataSource: dataAdapterModels
	    });

	    $("#<?php echo $treeNameGeneral; ?>").kendoTreeView({
	        checkboxes: {
	            checkChildren: true
	        },
	        dataSource: dataAdapterGeneralModels
	    });

	    // Creamos los botones
	    $("#btnSave").kendoButton({
	        click: saveChanges,
	        icon: "tick"
	    });

	    $("#btnSaveGeneral").kendoButton({
	        click: saveChangesGeneral,
	        icon: "tick"
	    });

	    // Cargamos el combo
	    $("#<?php echo $comboUserName; ?>").width(400).kendoComboBox({
	        dataTextField: "UserName",
	        dataValueField: "UserId",
	        dataSource: dataAdapterUsers,
	        height: 370,
	        filter: "contains",
	        suggest: true,
	        change: onChangeUser
	    });

	    // Cargamos las pestañas
	    $("#tabstrip").kendoTabStrip({
	        animation: {
	            open: {
	                effects: "fadeIn"
	            }
	        }
	    });

	    function onChangeUser(e) {
	        if (this.selectedIndex >= 0) {
	            var dataItem = this.dataItem(this.selectedIndex);
	            $("#<?php echo $treeName; ?>").data("kendoTreeView").dataSource.read({ UserId: dataItem.UserId });
                $("#arbol").show();
            } else {
                $("#arbol").hide();
            }
        }
	});

    function saveChanges() {
        // Obtenemos los checks del arbol seleccionados
        var checkedNodes = [];
        var treeView = $("#<?php echo $treeName; ?>").data("kendoTreeView");
        var comboUser = $("#<?php echo $comboUserName; ?>").data("kendoComboBox");
        var dataItem = comboUser.dataItem(comboUser.selectedIndex);

        if (dataItem === undefined) {
            AlertBox("Un usuario tiene que ser seleccionado!", "Warning", MessageBoxDialogs.ErrorIcon);
            return false;
        }

        showLoading(true);

        // Obtenemos los datos para enviar
        checkedNodeIds("#<?php echo $treeName; ?>", treeView.dataSource.view(), checkedNodes, []);

        // Enviamos los datos al servidor
        var dataToSend = { permIds: checkedNodes, userId: dataItem.UserId };

        $.ajax({
            url: "<?php echo StringUtil::UrlAction($permissionsSaveAction, $controllerName, $areaName); ?>",
            dataType: "json",
            type: "POST",
            data: kendo.stringify(dataToSend),
            contentType: "application/json",
            processData: false,
            success: function (response) {
                if (response.success) {
                    ShowInfo({ message: "Acción realizada", title: "Permisos guardados" });
                } else {
                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                }
            },
            error: function (response) {
                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                console.log(response);
            },
            complete: function () {
                showLoading(false);
            }
        });
    }

    function saveChangesGeneral() {
        // Obtenemos los checks del arbol seleccionados
        var checkedNodes = [];
        var treeView = $("#<?php echo $treeNameGeneral; ?>").data("kendoTreeView");

        showLoading(true);

        // Obtenemos los datos para enviar
        checkedNodeIds("#<?php echo $treeNameGeneral; ?>", treeView.dataSource.view(), checkedNodes, []);

        // Enviamos los datos al servidor
        var dataToSend = { permIds: checkedNodes };

        $.ajax({
            url: "<?php echo StringUtil::UrlAction($permissionsGeneralSaveAction, $controllerName, $areaName); ?>",
            dataType: "json",
            type: "POST",
            data: kendo.stringify(dataToSend),
            contentType: "application/json",
            processData: false,
            success: function (response) {
                if (response.success) {
                    ShowInfo({ message: "Acción realizada", title: "Permisos guardados" });
                } else {
                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
                }
            },
            error: function (response) {
                AlertBox("Unknown error. Please, contact with the webmaster.", "Error", MessageBoxDialogs.ErrorIcon);
                console.log(response);
            },
            complete: function () {
                showLoading(false);
            }
        });
    }

    function checkedNodeIds(treename, nodes, checkedNodes, savedUids) {
        var treeSecurity = $(treename).data("kendoTreeView");
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].checked) {
                if (nodes[i].hasChildren) {
                    checkedNodes.push(nodes[i]);
                    savedUids.push(nodes[i].uid);
                } else {
                    var parentData = treeSecurity.dataItem(treeSecurity.parent(treeSecurity.findByUid(nodes[i].uid)));
                    if ($.inArray(parentData.uid, savedUids) == -1) {
                        checkedNodes.push(parentData);
                        savedUids.push(parentData.uid);
                    }
                }
            }

            if (nodes[i].hasChildren) {
                checkedNodeIds(treename, nodes[i].children.view(), checkedNodes, savedUids);
            }
        }
    }
</script>

<!-- Main -->
<div id="main">

    <!-- Intro -->
    <section id="options">
        <header>
            <h2>Administraci&oacute;n de permisos</h2>
        </header>

        <div id="permissions-management">
            <div id="tabstrip" class="k-content">
                <ul>
                    <li class="k-state-active">Permisos de usuarios</li>
                    <li>Permisos generales</li>
                </ul>
                <div>
                    <div class="boxFix">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="userSelect">Selecciona un usuario</label></div>
                            <div class="col-md-5">
                                <input type="text" id="<?php echo $comboUserName; ?>" /></div>
                            <div class="col-md-2">
                                <input type="button" class="k-primary" value="Guardar permisos" id="btnSave" /></div>
                        </div>
                        <hr />
                        <div id="arbol" class="row container">
                            <div id="<?php echo $treeName; ?>"></div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="content">
                        <div class="row container">
                            <div class="col-md-2 col-md-offset-10">
                                <input type="button" class="k-primary" value="Guardar permisos" id="btnSaveGeneral" /></div>
                        </div>
                        <div id="arbolGeneral" class="row container">
                            <div id="<?php echo $treeNameGeneral; ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
