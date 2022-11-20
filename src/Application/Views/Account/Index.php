<?php
// Actions
$readUserAction = "Read_Profile";
$editProfileAction = "Save_Profile";
$changePasswordAction = "Change_Password";
$fileUploadAction = "Upload_File";

// Controllers and areas
$controllerName = "Account";
$areaName = "";
?>

<script type="text/javascript" src="/Public/js/kendo_core/upload.min.js"></script>

<div class="container" id="ProfilePage">
    <!-- main content output -->
    <div>
        <h3><?php echo T_("Profile"); ?></h3>
    </div>
    <div class="row">
        <div class="col-md-3">
            <img data-bind="attr: { src: ProfileUrl }" id="avatar" width="250" alt="<?php echo T_("Avatar"); ?>" />
            <br /><br />
            <button type="button" class="btn btn-default k-primary" data-bind="events: { click: EditProfile }, style: { display: ReadMode }"><?php echo T_("Edit"); ?></button>
            <button type="button" class="btn btn-default k-primary" data-bind="events: { click: SaveProfile }, style: { display: EditMode }"><?php echo T_("Save"); ?></button>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Name"); ?></div>
                <div class="col-md-9">
                    <span data-bind="html: Name, style: { display: ReadMode }"></span>
                    <input type="text" data-bind="value: Name, style: { display: EditMode }" />
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Username"); ?></div>
                <div class="col-md-9">
                    <span data-bind="html: Username"></span>
                </div>
            </div>
            <div class="row" data-bind="style: { display: EditMode }">
                <br />
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Avatar.Image"); ?></div>
                <div class="col-md-9">
                    <input type="file" name="image_avatar" id="image_avatar" />
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Mail"); ?></div>
                <div class="col-md-9">
                    <span data-bind="html: Email"></span>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Password"); ?></div>
                <div class="col-md-3">
                    <span data-bind="style: { display: ReadPasswordMode }">********</span>
                    <div data-bind="style: { display: WritePasswordMode }">
                        <label for="oldpassword"><?php echo T_("Old.Password"); ?></label>
                        <input name="oldpassword" type="password" data-bind="value: OldPassword" />
                        <br />
                        <label for="newpassword"><?php echo T_("Password"); ?></label>
                        <input name="newpassword" type="password" data-bind="value: NewPassword" />
                        <br />
                        <label for="retypepassword"><?php echo T_("Retype.Password"); ?></label>
                        <input name="retypepassword" type="password" data-bind="value: RetypePassword" />
                    </div>
                </div>
                <div class="col-md-6">
                    <a href="#" data-bind="events: { click: ChangePassword }, style: { display: ReadPasswordMode }"><?php echo T_("Change.Password"); ?></a>
                    <a href="#" data-bind="events: { click: SavePassword }, style: { display: WritePasswordMode }"><?php echo T_("Save.Change.Password"); ?></a>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Web"); ?></div>
                <div class="col-md-9">
                    <span id='weblabel'><a href="#" data-bind="html: WebName, attr: { href: WebUrl }, style: { display: ReadMode }"></a></span>
                    <div data-bind="style: { display: EditMode }">
                        <label for="weburl"><?php echo T_("Web.Url"); ?></label>
                        <br />
                        <input name="weburl" type="text" data-bind="value: WebUrl" />
                        <br />
                        <label for="webname"><?php echo T_("Web.Name"); ?></label>
                        <br />
                        <input name="webname" type="text" data-bind="value: WebName" />
                    </div>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Subscribed.Since"); ?></div>
                <div class="col-md-9">
                    <span data-bind="html: CreateDate"></span>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-2 col-md-offset-1"><?php echo T_("Subscribed"); ?></div>
                <div class='col-md-3'><input type='checkbox' data-bind="checked: Subscribed, enabled: EnabledCheckbox" /></div>
            </div>
        </div>
    </div>

    <br />

    <div id="tabProfile">
        <ul>
            <li class="k-state-active"><?php echo T_("Confeti.Mails"); ?></li>
            <li><?php echo T_("Interest.Survey"); ?></li>
        </ul>
        <div>
            <div class="boxFix">
                <?php
                if ($ViewBag->Mails)
                {
                    $idx = 0;
                    $divider = 4;
                    foreach ($ViewBag->Mails as $mail)
                    {
        	            if ($idx % $divider == 0)
                        {
                            if ($idx != 0) echo "</div>";
                            echo "<br />";
                            echo "<div class='row'>";
                        }
            
                        $img = $mail["image_frontend"] != ""
                            ? $mail["image_frontend"]
                            : "/Public/img/layout/placeholder-empty.png";
            
                        echo "<div class='col-md-3'>";
                        echo sprintf("<a href='%s' class='downloadContent'>", $mail['file_zip_url']);
                        echo sprintf("<img class='featurette-image img-responsive center-block' src='%s' alt='%s' title='%s' />", $img, utf8_encode($mail["tematica"]), utf8_encode($mail["tematica_desc"]));
                        echo "</a>";
                        echo "</div>";
            
                        $idx++;
                    }
        
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <div>
            <div class="boxFix">
                <?php
                if ($ViewBag->Surveys)
                {
                    $idx = 0;
                    $divider = 3;
                    foreach ($ViewBag->Surveys as $survey)
                    {
        	            if ($idx % $divider == 0)
                        {
                            if ($idx != 0) echo "</div>";
                            echo "<br />";
                            echo "<div class='row'>";
                        }

                        echo sprintf("<div class='col-md-3'>%s</div>", $survey['field_title']);
                        echo "<div class='col-md-1'>";
                        echo sprintf("<input type='checkbox' name='%s' id='%s' data-bind='checked: %s, enabled: EnabledCheckbox'/>", $survey['field_name'], $survey['field_name'], $survey['field_name']);
                        echo "</div>";
                        
                        $idx++;
                    }
                    
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

</div>

<?php
// Load notifications
require_once("Application/Views/Shared/WindowNotification.html");
?>

<script type="text/javascript">
    $("#tabProfile").kendoTabStrip({
        animation: {
            open: {
                effects: "fadeIn"
            }
        }
    });

    var profileModel = kendo.observable({
        // Properties
        Name: "",
        Username: "",
        Email: "",
        WebUrl: "#",
        WebName: "",
        CreateDate: "",
        Subscribed: false,
        ProfileUrl: "/Public/img/layout/placeholder-empty.png",

        // Surveys
        entertainment_recommendation: false,
        general_recommendation: false,
        background_icons_web: false,
        calendars: false,
        printables: false,
        cliparts: false,
        photographs: false,
        wallpapers: false,
        bullet_point: false,

        // Password Properties
        OldPassword: "",
        NewPassword: "",
        RetypePassword: "",

        // Extenden properties
        Readonly: true,
        ReadMode: "block",
        EditMode: "none",
        EnabledCheckbox: false,
        ReadPasswordMode: "block",
        WritePasswordMode: "none",

        // Functions
        EditProfile: function (e) {
            this.set("Readonly", false);
            this.set("ReadPasswordMode", "none");
            this.set("WritePasswordMode", "none");
            this.ResetPassword();
            this.Reload();
        },
        SaveProfile: function (e) {
            var self = this;
            showLoading(true);

            $.ajax({
                url: "<?php echo StringUtil::UrlAction($editProfileAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify(self.toJSON()),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    if (response.success) {
                        // Set data
                        self.set("Readonly", true);
                        self.set("ReadPasswordMode", "block");
                        self.set("WritePasswordMode", "none");
                        self.ResetPassword();
                        self.Reload();
                    } else {
                        ShowError({ message: response.message, title: "<?php echo T_("Error"); ?>" });
                    }
                },
                error: function (response) {
                    console.log(response);
                },
                complete: function (x) {
                    showLoading(false);
                }
            });
        },
        ChangePassword: function (e) {
            this.set("ReadPasswordMode", "none");
            this.set("WritePasswordMode", "block");
        },
        SavePassword: function (e) {
            var self = this;

            if (this.NewPassword != this.RetypePassword) {
                ShowError({ message: "<?php echo T_("Passwords.Not.Match"); ?>", title: "<?php echo T_("Error"); ?>" });
                return false;
            }

            showLoading(true);

            $.ajax({
                url: "<?php echo StringUtil::UrlAction($changePasswordAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                data: kendo.stringify({ OldPassword: self.OldPassword, NewPassword: self.NewPassword, RetypePassword: self.RetypePassword }),
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    if (response.success) {
                        // Set data
                        self.set("ReadPasswordMode", "block");
                        self.set("WritePasswordMode", "none");
                        self.ResetPassword();
                    } else {
                        ShowError({ message: response.message, title: "<?php echo T_("Error"); ?>" });
                    }
                },
                error: function (response) {
                    console.log(response);
                },
                complete: function (x) {
                    showLoading(false);
                }
            });
        },
        Reload: function (e) {
            this.set("ReadMode", this.Readonly ? "block" : "none");
            this.set("EditMode", !this.Readonly ? "block" : "none");
            this.set("EnabledCheckbox", !this.Readonly);
            this.set("TextSubscribe", this.Subscribed ? "<?php echo T_("Unsubscribe.Me"); ?>" : "<?php echo T_("Subscribe.Me"); ?>");
        },
        ResetPassword: function (e) {
            this.set("OldPassword", "");
            this.set("NewPassword", "");
            this.set("RetypePassword", "");
        },
        Initialize: function (e) {
            var self = this;

            $.ajax({
                url: "<?php echo StringUtil::UrlAction($readUserAction, $controllerName, $areaName); ?>",
                dataType: "json",
                type: "POST",
                contentType: "application/json",
                processData: false,
                success: function (response) {
                    if (response.success) {
                        // Set data
                        // Properties
                        self.set("Name", response.data.Name);
                        self.set("Username", response.data.Username);
                        self.set("Email", response.data.Email);
                        self.set("WebUrl", response.data.WebUrl);
                        self.set("WebName", response.data.WebName);
                        self.set("CreateDate", response.data.CreateDate);
                        self.set("Subscribed", response.data.Subscribed);
                        self.set("ProfileUrl", response.data.ProfileUrl);

                        // Surveys
                        self.set("entertainment_recommendation", response.data.entertainment_recommendation);
                        self.set("general_recommendation", response.data.general_recommendation);
                        self.set("background_icons_web", response.data.background_icons_web);
                        self.set("calendars", response.data.calendars);
                        self.set("printables", response.data.printables);
                        self.set("cliparts", response.data.cliparts);
                        self.set("photographs", response.data.photographs);
                        self.set("wallpapers", response.data.wallpapers);
                        self.set("bullet_point", response.data.bullet_point);
                    } else {
                        console.log(response);
                    }
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }
    });

    $(document).ready(function () {
        kendo.bind("#ProfilePage", profileModel);
        profileModel.Initialize();

        $("#image_avatar").kendoUpload({
            async: {
                saveUrl: "<?php echo StringUtil::UrlAction($fileUploadAction, $controllerName, $areaName); ?>",
                autoUpload: true
            },
            multiple: false,
            error: function (e) {
                var err = $.parseJSON(e.XMLHttpRequest.responseText);
                ShowError({ message: err.Message, title: "<?php echo T_("Error"); ?>" });
            },
            success: function (e) {
                if (!e.response.success) {
                    $("#image_avatar").data("kendoUpload").trigger("cancel");
                    ShowError({ message: e.response.message, title: "<?php echo T_("Error"); ?>" });
                } else {
                    $("#avatar").attr("src", profileModel.ProfileUrl + "?" + new Date().getTime());
                }
            }
        });
    });
</script>
