<div class="navbar-wrapper">
    <div class="container">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only"><?php echo T_("Toggle.Navigation"); ?></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/">
                        <img src="/Public/img/layout/ConfetiMailLogo.png" alt="Confetim@il" title="Confetim@il" height="30" />
                    </a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <?php
                        $menus = array(
                            "Home" =>       array(T_("Home"),           "/#", false, array(
                                // Submenu
                                "WhatIs" =>         array(T_("What.Is"),        "/#about", false),
                                "History" =>        array(T_("History"),        "/#history", false),
                                "WriteAboutUs" =>   array(T_("Write.About.Us"), "/#WriteAboutUs", false),
                                "PastMonths" =>     array(T_("Past.Months"),    "/#PastMonths", false),
                            )),
                            "Subscribe" =>  array(T_("Subscribe"),  StringUtil::UrlAction("Subscribe", "Account"), true),
                            "Blog" =>       array(T_("Our.Blog"),   StringUtil::UrlAction("", "Blog"), false),
                            "Reviews" =>    array(T_("Reviews"),    StringUtil::UrlAction("", "Reviews"), false),
                            "Store" =>      array(T_("Store"),      StringUtil::UrlAction("", "Store"), false),
                        );

                        if (strlen($ViewBag->CurrentMenu) <= 0) $ViewBag->CurrentMenu = "Home";

                        foreach($menus as $key_menu => $menu)
                        {
                            if ($menu[2] && $menu[2] == Security::IsOnline()) continue;
                            
                            if (isset($menu[3]) && is_array($menu[3]))
                            {
                                echo ($ViewBag->CurrentMenu == $key_menu) ? "<li class='active dropdown'>" : "<li class='dropdown'>";
                                echo sprintf("<a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>%s <span class='caret'></span></a>", $menu[0]);
                                echo "<ul class='dropdown-menu'>";
                                foreach($menu[3] as $key_submenu => $submenu)
                                {
                                    if ($submenu[2] && $submenu[2] == Security::IsOnline()) continue;
                                    echo "<li><a href=\"".$submenu[1]."\">".$submenu[0]."</a></li>";
                                }
                                echo "</ul></li>";
                            }
                            else
                            {
                                echo ($ViewBag->CurrentMenu == $key_menu) ? "<li class='active'>" : "<li>";
                                echo "<a href=\"".$menu[1]."\">".$menu[0]."</a>";
                                echo "</li>";
                            }
                        }
                        ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                        echo sprintf("<li><a href='?lang=en_US'><img src='/Public/img/layout/uk-icon.png' alt='%s' title='%s' /></a></li>", T_("English"), T_("English"));
                        echo sprintf("<li><a href='?lang=es_ES'><img src='/Public/img/layout/spain-icon.png' alt='%s' title='%s' /></a></li>", T_("Spanish"), T_("Spanish"));
                        
                        if (!Security::IsOnline())
                        {
                            echo sprintf("<li %s><a href='/Account/Login'>%s</a></li>", $ViewBag->CurrentMenu == "Login"
                            ? "class='active'"
                            : "", T_("Log.In"));
                        }
                        else
                        {
                            if (Security::IsAuthorizedAdmin())
                            {
                                $liAdmin = sprintf("<li><a href='/Panel/Admin'>%s</a></li>", T_("Admin"));
                            }
                            else
                            {
                                $liAdmin = "";
                            }
                            
                            echo sprintf("
                            <li class='dropdown %s'>
                                <a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>%s <span class='caret'></span></a>
                                <ul class='dropdown-menu'>
                                    <li><a href='/Account/Index'>%s</a></li>
                                    %s
                                    <li role='separator' class='divider'></li>
                                    <li><a href='/Account/LogOut'>%s</a></li>
                                </ul>
                            </li>
                            ", $ViewBag->CurrentMenu == "Account" ? "active" : "", T_('My.Account'), T_("Profile"), $liAdmin, T_("Log.Out"));
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        /**
        * Redirige los menus con anclas
        */
        $("#navbar a").on('click', function (e) {
            var findAlmohadilla = e.target.href.indexOf("#");
            if (findAlmohadilla < 0) return;

            var longURL = e.target.href.length - 1;
            if (findAlmohadilla == longURL) return;

            window.location.href = e.target.href;
        });
    });
</script>