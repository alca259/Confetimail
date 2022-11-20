<?php
$readAction = "Entry_Read";
$createCommentAction = "Post_Comment";
$controllerName = "Blog";
$areaName = "";
?>

<div class="container" id="BlogReadPage">
    <div id="entries">
        <div id="blogContent"></div>
        <div id="blogSocial">
            <!-- Twitter -->
            <script>
                !function (d, s, id) {
                    var js,
                        fjs = d.getElementsByTagName(s)[0],
                        p = /^http:/.test(d.location) ? 'http' : 'https';
                    if (!d.getElementById(id)) {
                        js = d.createElement(s);
                        js.id = id;
                        js.src = p + "://platform.twitter.com/widgets.js";
                        fjs.parentNode.insertBefore(js, fjs);
                    }
                }(document, "script", "twitter-wjs");
            </script>
            <a href="https://twitter.com/share" 
                class="twitter-share-button" 
                data-via="confetimail" 
                data-related="confetimail">Tweet</a>

            <!-- Facebook -->
            <script>
                (function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/es_ES/sdk.js#xfbml=1&version=v2.4";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            </script>
            <div class="fb-share-button" 
                data-href="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" 
                data-layout="button_count">
            </div>

            <!-- Google -->
            <div class="g-plus" 
                data-action="share"
                data-href="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>"
                data-annotation="bubble">
            </div>

        </div>
        <?php
        if (Security::IsOnline())
        {
        ?>
        <div id="commentNew">
            <div class="row">
                <div class="col-md-12">
                    <label for="comment"><?php echo T_("Comments"); ?></label>
                    <textarea name="comment" data-bind="value: Comment" style="height: 200px; width: 100%;"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-10"></div>
                <div class="col-md-2"><button type='button' class='btn btn-default' data-bind="events: { click: SaveComment }"><?php echo T_("Publish"); ?></button></div>
            </div>
        </div>
        <?php
        }
        ?>

        <div id="comments"></div>
    </div>

    <hr class="featurette-divider">
    <?php
    require_once("Application/Views/Shared/_LayoutFooter.php");
    ?>
</div>

<script src="https://apis.google.com/js/platform.js" async defer></script>
<script type="text/javascript">

    var viewModel = kendo.observable({
        // Propiedades
        IdContent: "#blogContent",
        IdComments: "#comments",
        /**
        * Carga los datos pasados para una entrada
        * @verified: 2015-10-12
        */
        RefreshData: function () {
            var self = this;
            // Obtenemos los números de página
            $.ajax({
                url: "<?php echo StringUtil::UrlAction($readAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify({ SelectedBlogId: "<?php echo $ViewBag->BlogId; ?>" }),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    if (response.success) {
                        <?php
                        if (Security::IsOnline())
                        {
                        ?>
                        // Registramos el identificador del post
                        viewModelCreate.PostId = response.data.PostId;
                        <?php
                        }
                        ?>
                        // Pintado de datos en pantalla
                        self.DrawTemplate(response.data);
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
        * Muestra las entradas formateadas
        * @verified: 2015-10-12
        */
        DrawTemplate: function (line) {
            // Calculamos la imagen
            var image = line.PostImage == undefined || line.PostImage == "" ? "/Public/img/layout/placeholder-empty.png" : line.PostImage;
            // Obtenemos donde pintar
            var site = $(this.IdContent);

            // Obtenemos donde pintar los comentarios
            var commentDiv = $(this.IdComments);

            var template =
            "<div class='row'>" +
                "<div class='col-md-12'>" +
                    "<img class='featurette-image img-responsive center-block' src='" + image + "' />" +
                "</div>" +
            "</div>" +
            "<div class='row'>" +
                "<div class='col-md-12'>" +
                    "<div class='header'>" +
                        "<p class='pull-right'>" + line.PostPublishedDate + "</p>" +
                        "<p class='title'>" + line.PostSubject + "</p>" +
                    "</div>" +
                    "<div class='blogcontent'>" + line.PostBody + "</div>" +
                "</div>" +
            "</div>";

            site.html(template);

            // Pintamos los comentarios
            commentDiv.html("<ul></ul>");
            var commentUl = $(this.IdComments + " ul");
            var className = "alt-comment";

            $.each(line.PostComments, function (idx, line) {
                var templateComment =
                "<div>" +
                    "<div class='row reviewheader'>" +
                        "<div class='col-md-2'>" +
                            "<div>" + line.CommentUserId + "</div>" +
                            "<div>" + line.CommentPublishedDate + "</div>" +
                        "</div>" +
                        "<div class='col-md-10 " + className + "'>" +
                            "<div>" + line.CommentMessage + "</div>" +
                        "</div>" +
                    "</div>" +
                "</div>";

                commentUl.append("<li>" + templateComment + "</li>");
                className = className == "comment" ? "alt-comment" : "comment";
            });
        },
    });

    <?php
    if (Security::IsOnline())
    {
    ?>
    var viewModelCreate = kendo.observable({
        Comment: "",
        PostId: 0,
        /**
        * Guarda y envia el formulario
        */
        SaveComment: function () {
            var self = this;

            if (self.Comment == "") {
                AlertBox("<?php echo T_("Comments.Are.Mandatory"); ?>", "<?php echo T_("Error"); ?>", MessageBoxDialogs.ErrorIcon);
                return false;
            }

            // Obtenemos los números de página
            $.ajax({
                url: "<?php echo StringUtil::UrlAction($createCommentAction, $controllerName, $areaName); ?>",
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
            this.set("Comment", "");
        }
    });

    <?php
    }
    ?>

    $(document).ready(function () {
        // Init data
        viewModel.RefreshData();

        // Binding
        kendo.bind($("#entries"), viewModel);

        <?php
        if (Security::IsOnline())
        {
        ?>
        kendo.bind($("#commentNew"), viewModelCreate);
        <?php
        }
        ?>
    });
</script>