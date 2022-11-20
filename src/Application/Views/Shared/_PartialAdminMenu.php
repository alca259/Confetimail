<nav class="navbar navbar-inverse navbar-fixed-top">
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
                    "Home" =>       array(T_("Home"),       StringUtil::UrlAction("", "Admin", Constants::$PanelAreaName)),
                    "Users" =>      array(T_("Users"),      StringUtil::UrlAction("", "Users", Constants::$PanelAreaName)),
                    "Files" =>      array(T_("Files"),      "/#", array(
                        "File" =>      array(T_("Files"),      StringUtil::UrlAction("", "Files", Constants::$PanelAreaName)),
                        "NewFile" =>      array(T_("New.File"),      StringUtil::UrlAction("NewFile", "Files", Constants::$PanelAreaName)),
                    )),
                    "Mails" =>      array(T_("Mails"),      StringUtil::UrlAction("", "Mails", Constants::$PanelAreaName)),
                    "Structure" =>  array(T_("Structure"),  StringUtil::UrlAction("", "Structure", Constants::$PanelAreaName)),
                    "Security" =>   array(T_("Security"),   StringUtil::UrlAction("", "Security", Constants::$PanelAreaName)),
                    "Blog" =>       array(T_("Blog"),       StringUtil::UrlAction("", "Blog", Constants::$PanelAreaName)),
                    "Reviews" =>    array(T_("Reviews"),    StringUtil::UrlAction("", "Reviews", Constants::$PanelAreaName)),
                );

                if (strlen($ViewBag->CurrentMenu) <= 0) $ViewBag->CurrentMenu = "Home";

                foreach($menus as $key_menu => $menu)
                {
                    if (isset($menu[2]) && is_array($menu[2]))
                    {
                        echo ($ViewBag->CurrentMenu == $key_menu) ? "<li class='active dropdown'>" : "<li class='dropdown'>";
                        echo sprintf("<a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>%s <span class='caret'></span></a>", $menu[0]);
                        echo "<ul class='dropdown-menu'>";
                        foreach($menu[2] as $key_submenu => $submenu)
                        {
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
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo T_("My.Account"); ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href='/Account/Index'><?php echo T_("Profile"); ?></a></li>
                        <li role='separator' class='divider'></li>
                        <li><a href='/Account/LogOut'><?php echo T_("Log.Out"); ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
