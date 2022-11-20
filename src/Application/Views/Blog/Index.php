<?php
$readAction = "Blog_Read";
$controllerName = "Blog";
$areaName = "";
?>

<div class="container" id="BlogPage">
    <div id="entries">
        <div id="blogContent"></div>
        <div id="blogPager">
            <div class="btn-toolbar" role="toolbar" aria-label="...">
                <div class="btn-group" role="group" aria-label="..." data-bind="events: { click: PageChange }">
                </div>
            </div>
        </div>
    </div>

    <hr class="featurette-divider">
    <?php
    require_once("Application/Views/Shared/_LayoutFooter.php");
    ?>
</div>

<script type="text/javascript">

    var viewModel = kendo.observable({
        // Propiedades
        FirstPage: 1,
        EndingPage: 1,
        CurrentPage: 1,
        EntriesPerPage: 3,
        MaxPageNumbers: 5,
        IncludeNavigationButtons: true,
        IncludeFirstLastButtons: true,
        IdContent: "#blogContent",
        IdPager: "#blogPager .btn-group",
        /**
        * Establece si deben o no habilitarse los tabs
        * @verified: 2015-08-03
        */
        PageChange: function (e) {
            if (!this.ValidatePageChange()) return false;
            if (e.target.value == "back") {
                var previousPage = parseInt(this.CurrentPage) - 1;
                if (!this.ValidatePageChange(previousPage)) return false;

                this.RefreshData(previousPage);
                return true;
            }
            if (e.target.value == "next") {
                var nextPage = parseInt(this.CurrentPage) + 1;
                if (!this.ValidatePageChange(nextPage)) return false;

                this.RefreshData(nextPage);
                return true;
            }
            this.RefreshData(e.target.value);
        },
        /**
        * Valida el cambio de una pagina
        * @verified: 2015-10-12
        */
        ValidatePageChange: function (checkPage) {
            if (checkPage == undefined) checkPage = this.CurrentPage;

            if (this.FirstPage > checkPage || checkPage > this.EndingPage) return false;
            return true;
        },
        /**
        * Inicializa los datos al cargar la pagina
        * @verified: 2015-10-12
        */
        Initialize: function () {
            this.RefreshData(1);
        },
        /**
        * Carga los datos pasados para una página
        * @verified: 2015-10-12
        */
        RefreshData: function (newPage) {
            var self = this;
            // Obtenemos los números de página
            $.ajax({
                url: "<?php echo StringUtil::UrlAction($readAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify({ SelectedPage: newPage, MaxResults: self.EntriesPerPage }),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    self.CleanContent();

                    if (response.success) {
                        self.set("EndingPage", parseInt(response.data.totalPages));
                        self.set("CurrentPage", parseInt(response.data.currentPage));

                        var site = $(self.IdContent);
                        site.append("<ul></ul>");
                        $.each(response.data.rows, function (idx, value) {
                            // Pintado de datos en pantalla
                            self.DrawTemplate(value);
                        });

                        self.DrawPager(response.data);
                    } else {
                        $(self.IdContent).html(response.message);
                    }
                },
                error: function (response) {
                    console.log(response);
                }
            });
        },
        /**
        * Limpia el contenido de las entradas
        * @verified: 2015-10-12
        */
        CleanContent: function () {
            $(this.IdContent).html("");
        },
        /**
        * Limpia el contenido de las paginas
        * @verified: 2015-10-12
        */
        CleanPager: function () {
            $(this.IdPager).html("");
        },
        /**
        * Muestra las entradas formateadas
        * @verified: 2015-10-12
        */
        DrawTemplate: function (line) {
            // Calculamos la imagen
            var image = line.PostImage == undefined || line.PostImage == "" ? "/Public/img/layout/placeholder-empty.png" : line.PostImage;
            // Obtenemos donde pintar
            var site = $(this.IdContent + " ul");

            "</a>";

            var template =
            "<div class='row'>" +
                "<div class='col-md-4'>" +
                    "<a href='" + line.PostUrl + "'>" +
                    "<img class='featurette-image img-responsive center-block' src='" + image + "' />" +
                    "</a>" +
                "</div>" +
                "<div class='col-md-8'>" +
                    "<div class='header'>" +
                        "<p class='pull-right'>" + line.PostPublishedDate + " - " + line.PostCommentsCount + " <?php echo T_("Comments"); ?></p>" +
                        "<p class='title'><a href='" + line.PostUrl + "'>" + line.PostSubject + "</a></p>" +
                    "</div>" +
                    "<div class='blogcontent'>" + line.PostBody + "</div>" +
                "</div>" +
            "</div>";

            site.append("<li>" + template + "</li>");
        },
        /**
        * Imprime la paginación
        * @verified: 2015-10-12
        */
        DrawPager: function (data) {
            // Repintado de botones de pagina
            this.CleanPager();
            var group = $(this.IdPager);
            var self = this;

            if (this.IncludeFirstLastButtons)
            {
                group.append("<button type='button' class='btn btn-default' value='1'>&lt;&lt;</button>");
            }

            // Controlamos los numeros que aparecen
            var pageUpDown = Math.floor(self.MaxPageNumbers / 2);
            var pageDownStart = parseInt(data.currentPage) - pageUpDown;
            var pageUpStart = parseInt(data.currentPage) + pageUpDown;

            $.range(1, data.totalPages).forEach(function (v) {
                // Controlamos el boton de retroceder
                if (v == 1 && self.IncludeNavigationButtons) {
                    group.append("<button type='button' class='btn btn-default' value='back'>&lt;</button>");
                }
                
                // Pintamos solamente los numeros que esten las posiciones mas cercanas a la pagina seleccionada
                if (pageDownStart <= v && v <= pageUpStart) {
                    var isSelected = parseInt(data.currentPage) == v ? " k-primary" : "";
                    group.append("<button type='button' class='btn btn-default" + isSelected + "' value='" + v + "'>" + v + "</button>");
                }

                // Controlamos el boton de avanzar
                if (v == data.totalPages && self.IncludeNavigationButtons) {
                    group.append("<button type='button' class='btn btn-default' value='next'>&gt;</button>");
                }
            });

            if (this.IncludeFirstLastButtons) {
                group.append("<button type='button' class='btn btn-default' value='" + data.totalPages + "'>&gt;&gt;</button>");
            }
        }
    });

    $(document).ready(function () {
        // Init data
        viewModel.Initialize();

        // Binding
        kendo.bind($("#entries"), viewModel);
    });
</script>