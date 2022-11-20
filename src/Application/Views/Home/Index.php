<?php
// Define common data
$carouselName = "carouselHome";
?>
<div id="HomePage">
    <div id="<?php echo $carouselName; ?>" class="carousel slide" data-ride="carousel">

        <!-- Indicadores (Puntos) -->
        <ol class="carousel-indicators">
            <li data-target="#<?php echo $carouselName; ?>" data-slide-to="0" class="active"></li>
            <li data-target="#<?php echo $carouselName; ?>" data-slide-to="1"></li>
            <li data-target="#<?php echo $carouselName; ?>" data-slide-to="2"></li>
        </ol>

        <!-- Contenido -->
        <div class="carousel-inner" role="listbox">
            <div class="item active">
                <img src="<?php echo $ViewBag->TematicaImageUrl; ?>" class="img-responsive" />
                <div class="container">
                    <div class="carousel-caption">
                        <h1><?php echo utf8_encode($ViewBag->Tematica); ?></h1>
                        <p><?php echo utf8_encode($ViewBag->TematicaDesc); ?></p>
                        <p>
                            <?php
                            if (!Security::IsOnline())
                            {
                                echo T_("Carousel.Tematica.Button.Extra.Text")."<br /><br />";
                                echo sprintf("<a class='btn btn-lg btn-primary' href='%s' role='button'>%s</a>", StringUtil::UrlAction("Subscribe", "Account"), T_("Carousel.Tematica.Button"));
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="item">
                <img src="<?php echo $ViewBag->CarouselBlogImageUrl; ?>" class="img-responsive" />
                <div class="container">
                    <div class="carousel-caption">
                        <h1><?php echo T_("Carousel.Blog.Title"); ?></h1>
                        <p><?php echo $ViewBag->CarouselBlogText; ?></p>
                        <p><a class="btn btn-lg btn-primary" href="<?php echo $ViewBag->CarouselBlogUrl; ?>" role="button"><?php echo T_("Carousel.Blog.Button"); ?></a></p>
                    </div>
                </div>
            </div>

            <div class="item">
                <img src="/Public/img/content/carousel_store.jpg" class="img-responsive" />
                <div class="container">
                    <div class="carousel-caption">
                        <h1><?php echo T_("Carousel.Store.Title"); ?></h1>
                        <p><?php echo T_("Carousel.Store.Text"); ?></p>
                        <p><a class="btn btn-lg btn-primary" href="<?php echo StringUtil::UrlAction("", "Store"); ?>" role="button"><?php echo T_("Carousel.Store.Button"); ?></a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controles laterales -->
        <a class="left carousel-control" href="#<?php echo $carouselName; ?>" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only"><?php echo T_("Previous"); ?></span>
        </a>
        <a class="right carousel-control" href="#<?php echo $carouselName; ?>" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only"><?php echo T_("Next"); ?></span>
        </a>

    </div>

    <div class="container contenido">

        <div id="frontend-container">
            <div class="row">
                <div class="col-md-4">
                    <h2><?php echo T_("Pick.Up.Your.Confeti"); ?>:</h2>
                </div>
            </div>
            <hr class="featurette-divider-small">
            <div class="row">
                <div class="col-lg-4">
                    <a href="/#PastMonths">
                        <img class="img-circle" src="/Public/img/content/InicioEdicionesAnteriores.png" alt="<?php echo T_("Previous.Editions"); ?>" width="140" height="140">
                        <h2 class="inside-text"><?php echo T_("Previous.Editions"); ?></h2>
                    </a>
                </div>

                <div class="col-lg-4">
                    <a href="<?php echo StringUtil::UrlAction("", "Reviews"); ?>">
                        <img class="img-circle" src="/Public/img/content/InicioOpiniones.png" alt="<?php echo T_("Review.Users"); ?>" width="140" height="140">
                        <h2 class="inside-text"><?php echo T_("Review.Users"); ?></h2>
                    </a>
                </div>

                <div class="col-lg-4">
                    <a href="<?php echo StringUtil::UrlAction("", "Store"); ?>">
                        <img class="img-circle" src="/Public/img/content/InicioTienda.jpg" alt="<?php echo T_("Store"); ?>" width="140" height="140">
                        <h2 class="inside-text"><?php echo T_("Store"); ?></h2>
                    </a>
                </div>
            </div>
            <div class="row-middle-space"></div>
            <?php
            if (!Security::IsOnline())
            {
                require_once("Application/Views/Shared/_PartialSubscription.php");
            }
            ?>
        </div>

        <hr class="featurette-divider-top-small">
        <div class="row featurette" id="about">
            <div class="col-md-7">
                <h2 class="featurette-heading"><?php echo T_("What.Is.Confeti.Title"); ?></h2>
                <p class="lead">
                    <?php echo T_("What.Is.Confeti.P1"); ?>
                </p>
            </div>
            <div class="col-md-5">
                <img class="featurette-image img-responsive center-block" src="/Public/img/content/QueEsConfeti.png" alt="<?php echo T_("What.Is.Confeti.P1.Image"); ?>" title="<?php echo T_("What.Is.Confeti.P1.Image"); ?>">
            </div>
        </div>
        <br />
        <div class="row featurette">
            <div class="col-md-12">
                <p class="lead">
                    <?php echo T_("What.Is.Confeti.P2"); ?>
                </p>
                <p class="lead">
                    <?php echo T_("What.Is.Confeti.P3"); ?>
                    <?php echo sprintf("<a href='mailto:%s'>%s</a>", DEFAULT_FROM, DEFAULT_FROM); ?>
                </p>
                <p>
                    <?php echo T_("What.Is.Confeti.P4"); ?>
                </p>
            </div>
        </div>
        <br />
        <div class="row container">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/AsAOTt-_1qk" allow-fullscreen="true" frameborder="0"></iframe>
            </div>
        </div>

        <hr class="featurette-divider">
        <div class="row featurette" id="history">
            <div class="col-md-7 col-md-push-5">
                <h2 class="featurette-heading"><?php echo T_("History.Confeti.Title"); ?></h2>
                <p class="lead">
                    <?php echo T_("History.Confeti.P1"); ?>
                </p>
            </div>
            <div class="col-md-5 col-md-pull-7">
                <img class="featurette-image img-responsive center-block" src="/Public/img/content/HistoriaConfeti.png" alt="<?php echo T_("History.Confeti.P1.Image"); ?>" title="<?php echo T_("History.Confeti.P1.Image"); ?>">
            </div>
        </div>
        <div class="row featurette">
            <div class="col-md-12">
                <p class="lead">
                    <?php echo T_("History.Confeti.P2"); ?>
                </p>
                <p class="lead">
                    <?php echo T_("History.Confeti.P3"); ?>
                </p>
                <p class="lead">
                    <?php echo T_("History.Confeti.P4"); ?>
                </p>
            </div>
        </div>

        <hr class="featurette-divider">
        <div class="row featurette" id="WriteAboutUs">
            <div class="col-md-12">
                <h2 class="featurette-heading"><?php echo T_("Write.About.Us.Title"); ?></h2>
            </div>
        </div>
        <div class="row featurette">
            <div class="col-md-4">
                <a href="http://www.redheadsense.com/publi/unos-confetis-para-amenizar-el-mes/">
                    <img class="featurette-image img-responsive center-block" src="/Public/img/content/HablanEnBlogs.png" alt="<?php echo T_("Write.About.Us.P1.Image"); ?>" title="<?php echo T_("Write.About.Us.P1.Image"); ?>">
                </a>
            </div>
            <div class="col-md-4">
                <a href="http://www.patypeando.com/2014/08/dmingueando-lvi.html">
                    <img class="featurette-image img-responsive center-block" src="/Public/img/content/HablanEnBlogs2.png" alt="<?php echo T_("Write.About.Us.P2.Image"); ?>" title="<?php echo T_("Write.About.Us.P2.Image"); ?>">
                </a>
            </div>
            <div class="col-md-4">
                <a href="http://cuadernoderetales.blogspot.com.es/2014/08/publicidad-coleguil-confetimail.html">
                    <img class="featurette-image img-responsive center-block" src="/Public/img/content/HablanEnBlogs3.png" alt="<?php echo T_("Write.About.Us.P3.Image"); ?>" title="<?php echo T_("Write.About.Us.P3.Image"); ?>">
                </a>
            </div>
        </div>
        <br />
        <div class="row featurette">
            <div class="col-md-4">
                <a href="http://paniculata1.blogspot.com.es/2014/10/iniciativa-confeti-mail.html">
                    <img class="featurette-image img-responsive center-block" src="/Public/img/content/HablanEnBlogs4.png" alt="<?php echo T_("Write.About.Us.P4.Image"); ?>" title="<?php echo T_("Write.About.Us.P4.Image"); ?>">
                </a>
            </div>
            <div class="col-md-4">
                <a href="http://justanailaholic.com/tutoriales-para-la-vuelta-al-cole">
                    <img class="featurette-image img-responsive center-block" src="/Public/img/content/HablanEnBlogs5.png" alt="<?php echo T_("Write.About.Us.P5.Image"); ?>" title="<?php echo T_("Write.About.Us.P5.Image"); ?>">
                </a>
            </div>
            <div class="col-md-4">
                <a href="http://miburbujamisnormas.blogspot.com.es/2014/06/nueva-pestana.html">
                    <img class="featurette-image img-responsive center-block" src="/Public/img/content/HablanEnBlogs6.png" alt="<?php echo T_("Write.About.Us.P6.Image"); ?>" title="<?php echo T_("Write.About.Us.P6.Image"); ?>">
                </a>
            </div>
        </div>

        <hr class="featurette-divider">
        <div class="row featurette" id="PastMonths">
            <div class="col-md-12">
                <h2 class="featurette-heading"><?php echo T_("Past.Months.Title"); ?></h2>
            </div>
        </div>
        <?php
        if ($ViewBag->Mails)
        {
            $idx = 0;
            $divider = 6;
            foreach ($ViewBag->Mails as $mail)
            {
        	    if ($idx % $divider == 0)
                {
                    if ($idx != 0) echo "</div>";
                    echo "<br />";
                    echo "<div class='row featurette'>";
                }
            
                $img = $mail["image_frontend"] != ""
                    ? $mail["image_frontend"]
                    : "/Public/img/layout/placeholder-empty.png";
            
                echo "<div class='col-md-2'>";
                echo sprintf("<img class='featurette-image img-responsive center-block' src='%s' alt='%s' title='%s' />", $img, utf8_encode($mail["tematica"]), utf8_encode($mail["tematica_desc"]));
                echo "</div>";
            
                $idx++;
            }
        
            echo "</div>";
        }
        ?>

        <hr class="featurette-divider">
        <div class="row featurette" id="SocialNetworks">
            <div class="col-md-12">
                <h2 class="featurette-heading"><?php echo T_("Social.Networks"); ?></h2>
                <br />
                <?php
                require_once("Application/Views/Shared/_PartialSocialNetworks.php");
                ?>
            </div>
        </div>

        <hr class="featurette-divider">
        <?php
        require_once("Application/Views/Shared/_LayoutFooter.php");
        ?>

        <a href="/#" id="FlechitaLinda">
            <span class="glyphicon glyphicon-chevron-up" aria-hidden="true"><br/><?php echo T_("Up"); ?></span>
        </a>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script type="text/javascript">
    $('a[data-slide="prev"]').click(function () {
        $('#<?php echo $carouselName; ?>').carousel('prev');
    });

    $('a[data-slide="next"]').click(function () {
        $('#<?php echo $carouselName; ?>').carousel('next');
    });
</script>