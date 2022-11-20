<?php
$actionLogin = "Login";
$controllerName = "Account";
$areaName = "";
?>

<div class="container" id="LoginPage">
    <form method="POST" action="<?php echo StringUtil::UrlAction($actionLogin, $controllerName, $areaName); ?>">
        <div class="row" id="loginForm">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3><?php echo T_("Log.In"); ?></h3>
                </div>
                <div class="panel-body">

                    <div class="row">
                        <div class="col-md-2 col-md-offset-1">
                            <?php echo T_("Mail"); ?>
                        </div>
                        <div class="col-md-5">
                            <input type="email"
                                name="email"
                                class="form-control"
                                required="required"
                                data-message="<?php echo T_("Required.Mail"); ?>"
                                data-message-mail="<?php echo T_("Invalid.Mail"); ?>" />
                        </div>
                        <div class="col-md-4">
                            <span data-for='email' class='k-invalid-msg'></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2 col-md-offset-1">
                            <?php echo T_("Password"); ?>
                        </div>
                        <div class="col-md-5">
                            <input type="password"
                                id="subPassword"
                                required="required"
                                data-message="<?php echo T_("Required.Password"); ?>"
                                name="subPassword"
                                class="form-control" />
                        </div>
                        <div class="col-md-4">
                            <span data-for='subPassword' class='k-invalid-msg'></span>
                        </div>
                    </div>

                    <div class="row" id="endForm">
                        <input type="submit" class="btn btn-default" value="<?php echo T_("Submit"); ?>" />
                    </div>
                    <div class="row center">
                        <?php
                        if (strlen($ViewBag->ErrorMessage) > 0)
                        {
                            echo sprintf("<span class='k-widget k-error-colored' id='subErrorMessage' role='alert'>
                                <span class='k-icon k-warning'></span>
                                <span id='subErrorMessageText'>%s</span>
                            </span>", $ViewBag->ErrorMessage);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <hr class="featurette-divider">
    <?php
    require_once("Application/Views/Shared/_LayoutFooter.php");
    ?>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        $("#loginForm").kendoValidator({
            messages: {
                required: function (input) {
                    return input.data("message");
                },
                email: function (input) {
                    return input.data("message-mail");
                }
            }
        });
    });
</script>
