<?php
$readAction = "Reviews_Read";
$createAction = "Review_Create";

$controllerName = "Reviews";

$areaName = "";
?>

<div class="container" id="ReviewPage">
    <?php
    if (Security::IsOnline())
    {
    ?>
    <div id="reviewsNew">
        <div class="row">
            <div class="col-md-12">
                <label for="comment"><?php echo T_("Comments"); ?></label>
                <textarea name="comment" data-bind="value: Comment" style="height: 200px; width: 100%;"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-7"></div>
            <div class="col-md-1"><label for="number"><?php echo T_("Score"); ?></label></div>
            <div class="col-md-2">
                <span class="rating">
                    <input type="radio" class="rating-input" value="5" id="rating-input-1-5" name="rating-input-1" data-bind="events: { click: ClickScore }"/>
                    <label for="rating-input-1-5" class="rating-star"></label>
                    <input type="radio" class="rating-input" value="4" id="rating-input-1-4" name="rating-input-1" data-bind="events: { click: ClickScore }"/>
                    <label for="rating-input-1-4" class="rating-star"></label>
                    <input type="radio" class="rating-input" value="3" id="rating-input-1-3" name="rating-input-1" data-bind="events: { click: ClickScore }"/>
                    <label for="rating-input-1-3" class="rating-star"></label>
                    <input type="radio" class="rating-input" value="2" id="rating-input-1-2" name="rating-input-1" data-bind="events: { click: ClickScore }"/>
                    <label for="rating-input-1-2" class="rating-star"></label>
                    <input type="radio" class="rating-input" value="1" id="rating-input-1-1" name="rating-input-1" data-bind="events: { click: ClickScore }"/>
                    <label for="rating-input-1-1" class="rating-star"></label>
                </span>
            </div>
            <div class="col-md-2"><button type='button' class='btn btn-default' data-bind="events: { click: SaveComment }"><?php echo T_("Save"); ?></button></div>
        </div>
    </div>
    <?php
    }
    ?>
    <div id="entries">
        <div id="reviewsContent"></div>
        <div id="reviewsPager">
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

    <?php
    if (Security::IsOnline())
    {
    ?>
    var viewModelCreate = kendo.observable({
        Score: 0,
        Comment: "",
        /**
        * Guarda el valor de la puntuacion
        */
        ClickScore: function(e) {
            this.set("Score", parseInt(e.target.value));
        },
        /**
        * Guarda y envia el formulario
        */
        SaveComment: function () {
            var self = this;

            if (self.Comment == "")
            {
                AlertBox("<?php echo T_("Comments.Mandatory"); ?>", "<?php echo T_("Error"); ?>", MessageBoxDialogs.ErrorIcon);
                return false;
            }

            // Obtenemos los números de página
            $.ajax({
                url: "<?php echo StringUtil::UrlAction($createAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify(this.toJSON()),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    if (response.success) {
                        self.ClearContent();
                        viewModel.RefreshData();
                    } else {
                        AlertBox(response.message, "<?php echo T_("Error"); ?>", MessageBoxDialogs.ErrorIcon);
                    }
                },
                error: function (response) {
                    console.log(response);
                }
            });
        },
        /**
        * Limpia el formulario
        */
        ClearContent: function () {
            this.set("Score", "0");
            this.set("Comment", "");
        }
    });

    <?php
    }
    ?>

    var viewModel = kendo.observable({
        // Propiedades
        FirstPage: 1,
        EndingPage: 1,
        CurrentPage: 1,
        EntriesPerPage: 10,
        MaxPageNumbers: 5,
        IncludeNavigationButtons: true,
        IncludeFirstLastButtons: true,
        IdContent: "#reviewsContent",
        IdPager: "#reviewsPager .btn-group",
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

            if (newPage == undefined) {
                newPage = this.CurrentPage;
            }

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

                        var className = "alt-comment";

                        $.each(response.data.rows, function (idx, value) {
                            // Pintado de datos en pantalla
                            self.DrawTemplate(value, className);
                            className = className == "comment" ? "alt-comment" : "comment";
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
        * Muestra los comentarios con formato
        * @verified: 2015-10-12
        */
        DrawTemplate: function (line, className) {
            // Obtenemos donde pintar
            var site = $(this.IdContent + " ul");

            var template =
            "<div>" +
                "<div class='row reviewheader'>" +
                    "<div class='col-md-2'>" +
                        "<div>" + line.ReviewUserId + "</div>" +
                        "<div>" + line.ReviewPublishedDate + "</div>" +
                    "</div>" +
                    "<div class='col-md-10 " + className + "'>" +
                        "<div>" + htmlStarsFor(line.ReviewScore) + " <span class='scoreText'>" + line.ReviewScoreText + "</span></div>" +
                        "<div>" + line.ReviewMessage + "</div>" +
                    "</div>" +
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

            if (this.IncludeFirstLastButtons) {
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

        <?php
        if (Security::IsOnline())
        {
        ?>
        kendo.bind($("#reviewsNew"), viewModelCreate);
        <?php
        }
        ?>
    });
</script>