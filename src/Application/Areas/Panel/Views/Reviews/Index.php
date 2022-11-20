<?php
// Controller names data
$controllerName = "Reviews";
$areaName = Constants::$PanelAreaName;

// Actions
$gridReadAction = "Reviews_Read";
$postDeleteAction = "DeleteReview";

// Misc
$gridName = "gridReviews";
?>

<!-- Main -->
<div id="main">
    <!-- Intro -->
    <section id="options">
        <header>
            <h2>Administraci&oacute;n de comentarios</h2>
        </header>
        
        <div id="reviews-management">
            <div id="tabstrip" class="k-content">
                <div id="<?php echo $gridName; ?>"></div>
            </div>
        </div>
    </section>

</div>

<?php
// Load notifications
require_once("Application/Views/Shared/WindowNotification.html");
require_once("Application/Views/Shared/WindowConfirmDelete.html");
?>

<script type="text/javascript">

    function deleteReview(dataItem) {
        var dataToSend = { Id: dataItem.ReviewId };

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
                        id: "ReviewId",
                        fields: {
                            ReviewId: { type: "number" },
                            ReviewUser: { type: "string" },
                            ReviewPublishedDate: { type: "date" },
                            ReviewScore: { type: "float" },
                            ReviewComment: { type: "string" },
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
                sort: { field: "ReviewPublishedDate", dir: "desc" },
                //aggregate: [
                //    { field: "ReviewScore", aggregate: "average" },
                //]
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
                    field: "ReviewId",
                    title: "Id",
                    hidden: true
                }, {
                    field: "ReviewUser",
                    title: "Usuario",
                    width: "150px"
                }, {
                    field: "ReviewPublishedDate",
                    title: "Fecha",
                    width: "150px",
                    format: "{0:dd/MM/yyyy HH:mm:ss}"
                }, {
                    field: "ReviewScore",
                    title: "Valoración",
                    width: "190px",
                    template: function (dataItem) {
                        return htmlStarsFor(dataItem.ReviewScore);
                    },
                    attributes: {
                        style: "text-align: center;"
                    },
                    //aggregates: ["average"],
                    //footerTemplate: "Media: #=average#",
                    //groupFooterTemplate: "Media: #=average#"
                }, {
                    field: "ReviewComment",
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

        $(window).resize(function () {
            resizeGrid("<?php echo "#".$gridName; ?>");
        });

        setTimeout(function () { resizeGrid("<?php echo "#".$gridName; ?>"); }, 100);
    });
</script>