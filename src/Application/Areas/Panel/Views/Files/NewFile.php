<?php
// Controller names data
$controllerName = "Files";
$areaName = Constants::$PanelAreaName;

// Actions
$fileUploadAction = "NewFile";
$comboFileTypeAction = "GetComboboxFileTypes";
$comboFileCategoryAction = "GetComboboxCategoryFiles";

// Load notifications
require_once("Application/Views/Shared/WindowNotification.html");
require_once("Application/Views/Shared/MessageBoxDialogs.html");
?>

<style type="text/css">
    #file-name {
        height: 25px;
    }

    .k-edit-label {
        padding-top: 0.1em;
        height: 20px;
        font-weight: bold;
    }

    .k-edit-field {
        height: 33px;
    }

    #files-management #tabstrip {
        padding: 30px 15px 5px 15px;
    }
</style>

<script type="text/javascript">

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

    $(document).ready(function () {

        $("#file-type").kendoDropDownList({
            dataTextField: "type_name",
            dataValueField: "type_id",
            dataSource: dataAdapterFileTypes,
            optionLabel: "Elige un tipo...",
            height: '25px'
        });
        $("#file-category").kendoDropDownList({
            dataTextField: "category_name",
            dataValueField: "category_id",
            dataSource: dataAdapterFileCategories,
            optionLabel: "Elige una categoría...",
            height: '25px'
        });

        // ############# Button Actions ############## //
        $("#FileInput").kendoUpload({
		    localization: {
		        select: "Selecciona o arrastra los ficheros para subir..."
		    }
        });

        <?php
        if ($ViewBag->Error != "")
        {
            echo "AlertBox(\"".$ViewBag->Error."\", 'Error', MessageBoxDialogs.ErrorIcon);";
        }
        ?>
	});

</script>

<!-- Main -->
<div id="main">

    <!-- Intro -->
    <section id="options">
        <header>
            <h2>Nuevo fichero</h2>
        </header>

        <div id="files-management">
            <div id="tabstrip" class="k-content">
                <form id="subidaDeFicheros" action="<?php echo StringUtil::UrlAction($fileUploadAction, $controllerName, $areaName); ?>" method="POST">
                    <div class="groups">
                        <div class="subGroup">
                            <label class="fLabel" for="file-name">Nombre del fichero para mostrar</label>
                            <input type="text" id="file-name" name="FileName" class="k-textbox fObject" placeholder="Nombre del fichero a visualizar" />
                        </div>
                        <div class="subGroup">
                            <label class="fLabel" for="file-type">Tipo de fichero</label>
                            <input type="text" id="file-type" name="FileType" class="fObject" />
                        </div>
                        <div class="subGroup">
                            <label class="fLabel" for="file-category">Categor&iacute;a del fichero</label>
                            <input type="text" id="file-category" name="FileCategory" class="fObject" />
                        </div>
                        <div class="subGroup">
                            <label class="fLabel" for="file-active">&iquest;Fichero activo?</label>
                            <input type="checkbox" id="file-active" name="FileActive" class="fObject" checked="checked" />
                        </div>

                        <div class="subGroup upload-wrapper">
                            <label class="fLabel" for="FileInput">Ficheros para subir</label>
                            <div class="fObject">
                                <input name="FileInput[]" id="FileInput" type="file" />
                            </div>
                        </div>
                    </div>
                        
                    <div>
                        <div class="actions">
                            <input type="submit" class="k-button k-primary" value="Subir ficheros" />
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>
