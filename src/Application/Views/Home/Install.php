<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
    <!-- title -->
    <title>
        <?php echo sprintf("Confetimail - %s", T_($ViewBag->Title)); ?>
    </title>

    <!-- metatags -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="/Public/img/ui/favicon.ico" />

    <!-- css -->
    <link href="/Public/css/reset.css" rel="stylesheet" />
    <link href="/Public/css/bootstrap.css" rel="stylesheet" />
    <link href="/Public/css/public.css" rel="stylesheet" />
    <link href="/Public/css/install.css" rel="stylesheet" />
    <!-- css viewport -->
    <link href="/Public/css/style-viewport.css" rel="stylesheet" />
</head>
<body>

    <div class="in-maintenance">

        <div id="branding">
            <h1 class="page-title"><?php echo sprintf("%s", T_("Config.Database.Title")); ?></h1>
        </div>

        <div id="page">

            <div id="sidebar-first" class="sidebar">
                <img id="logo" src="/Public/img/layout/ConfetimailBanner.jpg" alt="Confetimail" title="Confetimail" />
                <ol class="task-list">
                    <?php
                    if (!$ViewBag->Done) {
                        echo sprintf("<li class=\"active\">%s</li><li>%s</li>", T_("Config.Database.Start"), T_("Done"));
                    } else {
                        echo sprintf("<li class=\"done\">%s</li><li class=\"done\">%s</li>", T_("Config.Database.Start"), T_("Done"));
                    }
                    ?>
                </ol>
            </div>

            <div id="content">
                <?php
                if (!$ViewBag->Done)
                {
                    echo sprintf('
					<form action="" method="post" accept-charset="UTF-8">
						<div class="form-item">
							<label for="confirm">%s</label>
							<input type="password" id="confirm" name="confirm" value="" class="form-text" />
							<div class="description">%s</div>
						</div>
						<div class="form-actions">
							<input type="submit" value="%s" class="form-submit" />
						</div>', T_("Config.Database.AdminPassword"), T_("Config.Database.Description"), T_("Save.And.Finish"));

                    if (strlen($ViewBag->Error)>0)
                    {
                        echo "<p class='notifError'>".T_($ViewBag->Error)."</p>";
                    }

                    echo '</form>';
                }
                else
                {
                ?>
                <p class='notifOk'><?php echo sprintf("%s", T_("Config.Database.Ready")); ?></p>
                <div class='form-actions'>
                    <input type='button' value='<?php echo sprintf("%s", T_("Return.Back")); ?>' class='form-submit' onclick="window.location.href = '/'" />
                </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/Public/js/public.js"></script>

    <!-- Google analytics
    ================================================= -->
    <script type="text/javascript">
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments);
            }, i[r].l = 1 * new Date();
            a = s.createElement(o), m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m);
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-51940059-1', 'confetimail.net');
        ga('send', 'pageview');
    </script>
</body>
</html>
