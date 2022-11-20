<?php
// Controller names data
$controllerName = "Admin";
$areaName = Constants::$PanelAreaName;

// Actions
$usersReadAction = "UsersMonthly_Read";
$commentsReadAction = "CommentsMonthly_Read";
$postsReadAction = "PostsMonthly_Read";
$scoreReadAction = "ScoreMonthly_Read";
$surveyReadAction = "SurveyUnits_Read";

// Misc

?>

<!-- Main -->
<div id="main">

    <!-- Intro -->
    <section id="options">
        <header>
            <h2>Dashboard</h2>
        </header>

        <div id="admin-management">
            <div id="tabstrip" class="k-content">

                <div class="row">
                    <div class="col-md-6">
                        <div id="chartUsuarios"></div>
                    </div>

                    <div class="col-md-6">
                        <div id="chartComentarios"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div id="chartPosts"></div>
                    </div>

                    <div class="col-md-6">
                        <div id="chartValoraciones"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div id="chartEncuestas"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>

<!-- Usuarios -->
<script type="text/javascript">
    $("#chartUsuarios").kendoChart({
        dataSource: {
            transport: {
                read: function (options) {

                    $.ajax({
                        url: "<?php echo StringUtil::UrlAction($usersReadAction, $controllerName, $areaName); ?>",
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
            sort: {
                field: "date",
                dir: "asc"
            },
        },
        title: {
            text: "Usuarios por mes"
        },
        legend: {
            visible: false
        },
        seriesDefaults: {
            type: "line",
            style: "smooth",
            line: {
                color: "#FF0000"
            }
        },
        series: [{
            name: "Registros",
            field: "valueNumber",
        }],
        valueAxis: {
            labels: {
                format: "{0}"
            },
            axisCrossingValue: 0
        },
        categoryAxis: {
            field: "month",
            majorGridLines: {
                visible: false
            },
            labels: {
                rotation: "auto",
                template: function (e) {
                    return e.dataItem.monthName + "\n" + e.dataItem.yearName;
                }
            }
        },
        tooltip: {
            visible: true,
            format: "{0}",
            template: "#= series.name #: #= value #"
        }
    });

</script>

<!-- Comentarios -->
<script type="text/javascript">
    $("#chartComentarios").kendoChart({
        dataSource: {
            transport: {
                read: function (options) {

                    $.ajax({
                        url: "<?php echo StringUtil::UrlAction($commentsReadAction, $controllerName, $areaName); ?>",
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
            sort: {
                field: "date",
                dir: "asc"
            },
        },
        title: {
            text: "Comentarios por mes"
        },
        legend: {
            visible: false
        },
        seriesDefaults: {
            type: "line",
            style: "smooth",
            line: {
                color: "#0000FF"
            }
        },
        series: [{
            name: "Comentarios",
            field: "valueNumber"
        }],
        valueAxis: {
            labels: {
                format: "{0}"
            },
            axisCrossingValue: 0
        },
        categoryAxis: {
            field: "month",
            majorGridLines: {
                visible: false
            },
            labels: {
                rotation: "auto",
                template: function (e) {
                    return e.dataItem.monthName + "\n" + e.dataItem.yearName;
                }
            }
        },
        tooltip: {
            visible: true,
            format: "{0}",
            template: "#= series.name #: #= value #"
        }
    });
</script>

<!-- Posts -->
<script type="text/javascript">
    $("#chartPosts").kendoChart({
        dataSource: {
            transport: {
                read: function (options) {

                    $.ajax({
                        url: "<?php echo StringUtil::UrlAction($postsReadAction, $controllerName, $areaName); ?>",
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
            sort: {
                field: "date",
                dir: "asc"
            },
        },
        title: {
            text: "Publicaciones por mes"
        },
        legend: {
            visible: false
        },
        seriesDefaults: {
            type: "line",
            style: "smooth",
            line: {
                color: "#00FF00"
            }
        },
        series: [{
            name: "Publicaciones",
            field: "valueNumber",
        }],
        valueAxis: {
            labels: {
                format: "{0}"
            },
            axisCrossingValue: 0
        },
        categoryAxis: {
            field: "month",
            majorGridLines: {
                visible: false
            },
            labels: {
                rotation: "auto",
                template: function (e) {
                    return e.dataItem.monthName + "\n" + e.dataItem.yearName;
                }
            }
        },
        tooltip: {
            visible: true,
            format: "{0}",
            template: "#= series.name #: #= value #"
        }
    });
</script>

<!-- Valoraciones -->
<script type="text/javascript">
    $("#chartValoraciones").kendoChart({
        dataSource: {
            transport: {
                read: function (options) {

                    $.ajax({
                        url: "<?php echo StringUtil::UrlAction($scoreReadAction, $controllerName, $areaName); ?>",
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
            sort: {
                field: "date",
                dir: "asc"
            },
        },
        title: {
            text: "Valoracion media por mes"
        },
        legend: {
            visible: false
        },
        seriesDefaults: {
            type: "line",
            style: "smooth",
            line: {
                color: "#FF8866"
            }
        },
        series: [{
            name: "Media",
            field: "valueNumber",
        }],
        valueAxis: {
            labels: {
                format: "{0}"
            },
            axisCrossingValue: 0
        },
        categoryAxis: {
            field: "month",
            majorGridLines: {
                visible: false
            },
            labels: {
                rotation: "auto",
                template: function (e) {
                    return e.dataItem.monthName + "\n" + e.dataItem.yearName;
                }
            }
        },
        tooltip: {
            visible: true,
            format: "{0}",
            template: "#= series.name #: #= value #"
        }
    });
</script>

<!-- Encuestas del perfil -->
<script type="text/javascript">
    $("#chartEncuestas").kendoChart({
        dataSource: {
            transport: {
                read: function (options) {

                    $.ajax({
                        url: "<?php echo StringUtil::UrlAction($surveyReadAction, $controllerName, $areaName); ?>",
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
            sort: {
                field: "categories",
                dir: "asc"
            },
        },
        title: {
            text: "Intereses de los usuarios"
        },
        legend: {
            visible: false
        },
        seriesDefaults: {
            type: "column",
        },
        series: [{
            name: "Interes",
            field: "valueNumber",
            color: "#FFE066"
        }],
        valueAxis: {
            labels: {
                format: "{0}"
            },
            axisCrossingValue: 0
        },
        categoryAxis: {
            field: "categories",
            majorGridLines: {
                visible: false
            },
            labels: {
                rotation: "auto"
            }
        },
        tooltip: {
            visible: true,
            format: "{0}",
            template: "#= series.name #: #= value #"
        }
    });
</script>