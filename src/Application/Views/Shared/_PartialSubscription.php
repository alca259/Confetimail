<div class="row" id="subscribeForm">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3><?php echo T_("Receive.Email.Explosion"); ?></h3>
        </div>
        <div class="panel-body">


            <div class="row">
                <div class="col-md-2 col-md-offset-1">
                    <?php echo T_("Name"); ?>
                </div>
                <div class="col-md-5">
                    <input type="text"
                        name="name"
                        class="form-control"
                        required="required"
                        data-message="<?php echo T_("Required.Name"); ?>"
                        placeholder="<?php echo T_("Placeholder.Name"); ?>"
                        data-bind="value: Name" />
                </div>
                <div class="col-md-4">
                    <span data-for='name' class='k-invalid-msg'></span>
                </div>
            </div>

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
                        data-message-mail="<?php echo T_("Invalid.Mail"); ?>"
                        placeholder="<?php echo T_("Placeholder.Mail"); ?>"
                        data-bind="value: Email" />
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
                        class="form-control"
                        placeholder="<?php echo T_("Placeholder.Password"); ?>"
                        data-bind="value: Password" />
                </div>
                <div class="col-md-4">
                    <span data-for='subPassword' class='k-invalid-msg'></span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-md-offset-1">
                    <?php echo T_("Retype.Password"); ?>
                </div>
                <div class="col-md-5">
                    <input type="password"
                        id="subVerPassword"
                        required="required"
                        data-message="<?php echo T_("Required.Verify.Password"); ?>"
                        name="subVerPassword"
                        class="form-control"
                        placeholder="<?php echo T_("Placeholder.Retype.Password"); ?>"
                        data-bind="value: VerifyPassword" />
                </div>
                <div class="col-md-4">
                    <span data-for='subVerPassword' class='k-invalid-msg'></span>
                </div>
            </div>

            <div class="row" id="endForm">
                <button type="button" class="btn btn-default" data-bind="events: { click: SendForm }"><?php echo T_("Submit"); ?></button>
            </div>
            <div class="row center">
                <span class="k-widget k-error-colored"
                    id="subErrorMessage"
                    role="alert"
                    style="display: none;"><span class="k-icon k-warning"></span><span id="subErrorMessageText"></span>
                </span>
                <span class="k-widget k-success-colored"
                    id="subInfoMessage"
                    role="alert"
                    style="display: none;"><span class="k-icon k-i-tick"></span><span id="subInfoMessageText"></span>
                </span>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        $("#subscribeForm").kendoValidator({
            rules: {
                verifyPasswords: function (input) {
                    var ret = true;
                    if (input.is("[name=subVerPassword]")) {
                        ret = subscribeViewModel.get("Password") === subscribeViewModel.get("VerifyPassword");
                    }
                    return ret;
                }
            },
            messages: {
                verifyPasswords: "<?php echo T_("Passwords.Not.Match"); ?>",
                required: function (input) {
                    return input.data("message");
                },
                email: function (input) {
                    return input.data("message-mail");
                }
            }
        });

        kendo.bind($("#subscribeForm"), subscribeViewModel);

        // Binding enter key to submit
        $('#subscribeForm input').keypress(function (e) {
            if (e.which == 13) {
                subscribeViewModel.SendForm();
            }
        });
    });

    var subscribeViewModel = kendo.observable({
        Name: "",
        Email: "",
        Password: "",
        VerifyPassword: "",
        Reset: function () {
            this.set("Name", "");
            this.set("Email", "");
            this.set("Password", "");
            this.set("VerifyPassword", "");
        },
        SendForm: function () {
            if ($("#subscribeForm").data("kendoValidator").validate()) {

                var self = this;
                var dataToSend = this.toJSON();
                showLoading(true);

                $.ajax({
                    url: "<?php echo StringUtil::UrlAction("Subscribe_Save", "Account"); ?>",
                    dataType: "json",
                    type: "POST",
                    data: kendo.stringify(dataToSend),
                    contentType: "application/json",
                    processData: false,
                    success: function (response) {
                        if (response.success) {
                            self.HideMessages();
                            self.ShowSubscribeInfo(response.message);
                            self.Reset();
                        } else {
                            self.HideMessages();
                            self.ShowSubscribeError(response.message);
                        }
                    },
                    error: function (response) {
                        self.HideMessages();
                        self.ShowSubscribeError("<?php echo T_("Unknown.Error"); ?>");
                    },
                    complete: function () {
                        showLoading(false);
                    }
                });
            }
        },
        ShowSubscribeError: function (message) {
            $("#subErrorMessage #subErrorMessageText").html(message);
            $("#subErrorMessage").show(500).delay(10000).hide(100);
        },
        ShowSubscribeInfo: function (message) {
            $("#subInfoMessage #subInfoMessageText").html(message);
            $("#subInfoMessage").show(500).delay(5000).hide(100);
        },
        HideMessages: function () {
            $("#subErrorMessage").hide(0);
            $("#subInfoMessage").hide(0);
        }
    });
</script>
