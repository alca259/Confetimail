<?php
// Controller names data
$controllerName = "Files";
$areaName = Constants::$PanelAreaName;

// Actions
$gridReadAction = "Files_Read";
$gridUpdateAction = "UpdateFile";
$fileDeleteAction = "RemoveFile";

$fileRegisterServerAction = "SearchFiles";
$comboFileTypeAction = "GetComboboxFileTypes";
$comboFileCategoryAction = "GetComboboxCategoryFiles";

// Misc
$gridName = "gridFiles";

// Load notifications
require_once("Application/Views/Shared/WindowNotification.html");
?>

<script type="text/javascript">
    // Globals
    var checkedIds = {};

    // Datasources
    var dataAdapterFileTypes = new kendo.data.DataSource({
        transport: {
            read: function (options) {
                $.ajax({
                    url: "<?php echo StringUtil::UrlAction($comboFileTypeAction, $controllerName, $areaName); ?>",
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
		}
	});

    var dataAdapterFileCategories = new kendo.data.DataSource({
        transport: {
            read: function (options) {
                $.ajax({
                    url: "<?php echo StringUtil::UrlAction($comboFileCategoryAction, $controllerName, $areaName); ?>",
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
		}
	});

    var dataAdapterFiles = new kendo.data.DataSource({
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
		    },
		    update: function (options) {
		        options.data.Action = "update";
		        showLoading(true);

		        $.ajax({
		            url: "<?php echo StringUtil::UrlAction($gridUpdateAction, $controllerName, $areaName); ?>",
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
	            id: "file_id",
	            fields: {
	                file_id: { type: "number", editable: false },
	                file_name: { type: "string", editable: true, validation: { required: true } },
	                file_url: { type: "string", editable: false },
	                full_url: { type: "string", editable: false },
	                file_type: { type: "string", editable: true, validation: { required: true } },
	                file_category: { type: "string", editable: true, validation: { required: true } },
	                file_date: { type: "date", editable: false },
	                file_active: { type: "boolean", editable: true }
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
	    sort: [
            { field: 'file_date', dir: 'desc' },
	    ]
	});

    function initGridFiles() {
        var grid = $("#<?php echo $gridName; ?>").kendoGrid({
            dataSource: dataAdapterFiles,
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
            toolbar: [{
                name: "btnSearch",
                text: "Registrar ficheros del servidor",
                imageClass: "k-icon k-i-search"
            }, {
                name: "btnDelete",
                text: "Borrar ficheros seleccionados",
                imageClass: "k-icon k-delete"
            }, {
                name: "excel",
                text: "Exportar a excel"
            }],
            excel: {
                fileName: "Files.xlsx",
                filterable: true,
                allPages: true
            },
            dataBound: onDataBound,
            columns: [
				{
				    template: "<input type='checkbox' class='checkbox' />",
				    attributes: {
				        style: "text-align: center;"
				    },
				    //headerTemplate: "<input type='checkbox' id='check-all' onclick='selectAllRows(this)'/>",
				    width: "40px"
				}, {
				    field: "Image", title: "Imagen",
				    template: '#if(file_type == "Imagen") {#<img src="#:full_url#" width="60"/>#}#',
				    width: "80px"
				}, {
				    field: "file_id",
				    title: "Id",
				    hidden: true
				}, {
				    field: "file_name",
				    title: "Nombre",
				    width: "150px"
				}, {
				    field: "file_url",
				    title: "Url Relativa",
				    hidden: true
				}, {
				    field: "full_url",
				    title: "Url Completa"
				}, {
				    field: "file_type",
				    title: "Tipo",
				    width: "140px",
				    editor: fileTypeDropDownEditor,
				    template: "#=file_type#"
				}, {
				    field: "file_category",
				    title: "Categoría",
				    width: "120px",
				    editor: fileCategoryDropDownEditor,
				    template: "#=file_category#"
				}, {
				    field: "file_date",
				    title: "Modificado",
				    width: "140px",
				    format: "{0:dd/MM/yyyy HH:mm}"
				}, {
				    field: "file_active",
				    title: "Activo",
				    width: "80px",
				    template: function (dataItem) {
				        if (dataItem.file_active)
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
				            edit: "",
				            update: "",
				            cancel: ""
				        }
				    }],
				    title: "&nbsp;",
				    width: "160px"
				}
            ]
        }).data('kendoGrid');

        grid.table.on("click", ".checkbox", selectRow);

        function fileTypeDropDownEditor(container, options) {
            $('<input required data-text-field="type_name" data-value-field="type_name" data-bind="value:' + options.field + '"/>')
				.appendTo(container)
				.kendoDropDownList({
				    autoBind: true,
				    dataSource: dataAdapterFileTypes
				});
        }

        function fileCategoryDropDownEditor(container, options) {
            $('<input required data-text-field="category_name" data-value-field="category_name" data-bind="value:' + options.field + '"/>')
				.appendTo(container)
				.kendoDropDownList({
				    autoBind: true,
				    dataSource: dataAdapterFileCategories
				});
        }

        //on click of the checkbox:
        function selectRow() {
            var checked = this.checked,
				row = $(this).closest("tr"),
				grid = $("#<?php echo $gridName; ?>").data("kendoGrid"),
				dataItem = grid.dataItem(row);

            checkedIds[dataItem.id] = checked;
            if (checked) {
                //-select the row
                row.addClass("k-state-selected");
            } else {
                //-remove selection
                row.removeClass("k-state-selected");
            }
        }

        //on dataBound event restore previous selected rows:
        function onDataBound() {
            var view = this.dataSource.view();
            for (var i = 0; i < view.length; i++) {
                if (checkedIds[view[i].id]) {
                    this.tbody.find("tr[data-uid='" + view[i].uid + "']")
						.addClass("k-state-selected")
						.find(".checkbox")
						.attr("checked", "checked");
                }
            }
        }
    }

    /**
	 * Return a array of IDs of selected items on grid
	 * @return Array
	 **/
    function GetIdsSelected() {
        var checked = [];
        for (var i in checkedIds) {
            if (checkedIds[i]) {
                checked.push(i);
            }
        }
        return checked;
    }

    function deleteFiles_Action() {
        var dataToSend = { FilesId: GetIdsSelected() };
        showLoading(true);

        $.ajax({
            url: "<?php echo StringUtil::UrlAction($fileDeleteAction, $controllerName, $areaName); ?>",
		    dataType: "json",
		    type: "POST",
		    data: kendo.stringify(dataToSend),
		    contentType: "application/json",
		    processData: false,
		    timeout: 60 * 1000, // 1 minuto en milisegundos
		    success: function (response) {
		        showLoading(false);
		        if (response.success) {
		            $("#<?php echo $gridName; ?>").data("kendoGrid").dataSource.read();
		            ShowInfo({ message: "Acción realizada", title: "Borrado de fichero" });
		        } else {
		            AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
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

    function searchFiles_Action() {
        showLoading(true);

        $.ajax({
            url: "<?php echo StringUtil::UrlAction($fileRegisterServerAction, $controllerName, $areaName); ?>",
            dataType: "json",
            type: "POST",
            timeout: 60 * 1000, // 1 minuto
            success: function (response) {
                showLoading(false);
                if (response.success) {
                    $("#<?php echo $gridName; ?>").data("kendoGrid").dataSource.read();
                    ShowInfo({ message: response.message, title: "Registro de ficheros" });
                } else {
                    AlertBox(response.message, "Warning", MessageBoxDialogs.ErrorIcon);
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

    $(document).ready(function () {

        // Init data
        initGridFiles();

        // ############# Button Actions ############## //
        $(".k-grid-btnDelete").on('click', function (e) {
            deleteFiles_Action();
        });

        $(".k-grid-btnSearch").on('click', function (e) {
            searchFiles_Action();
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
            <h2>Administraci&oacute;n de ficheros</h2>
        </header>

        <div id="files-management">
            <div id="tabstrip" class="k-content">
                <div>
                    <div id="<?php echo $gridName; ?>"></div>
                </div>
            </div>
        </div>
    </section>
</div>
