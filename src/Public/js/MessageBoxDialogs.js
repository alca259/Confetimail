var MessageBoxDialogs = {};

MessageBoxDialogs.ErrorIcon = "/Public/img/ui/icons_dialogs/error.png";
MessageBoxDialogs.InfoIcon = "/Public/img/ui/icons_dialogs/info.png";
MessageBoxDialogs.QuestionIcon = "/Public/img/ui/icons_dialogs/question.png";
MessageBoxDialogs.WarningIcon = "/Public/img/ui/icons_dialogs/warning.png";
MessageBoxDialogs.SuccessIcon = "/Public/img/ui/icons_dialogs/success.png";

function AlertBox(message, title, icon, width, height) {
    showAlertDialog = function() {
        AlertDialog.ShowWindow({
            title: title == undefined ? "Alert" : title,
            message: message,
            icon: icon == undefined ? MessageBoxDialogs.InfoIcon : icon,
            height: height == undefined ? undefined : height,
            width: width == undefined ? undefined : width
        });
    };
    showAlertDialog();
}
function ConfirmBox(message, title, callback, params, okText, cancelText, icon, width, height) {
    showConfirmDialog = function() {
        ConfirmDialog.ShowWindow({
            title: title == undefined ? "Confirm" : title,
            message: message,
            callback: callback == undefined ? undefined : callback,
            params: params == undefined ? undefined : params,
            okText: okText == undefined ? "OK" : okText,
            cancelText: cancelText == undefined ? "Cancel" : cancelText,
            icon: icon == undefined ? MessageBoxDialogs.QuestionIcon : icon,
            width: width == undefined ? undefined : width,
            height: height == undefined ? undefined : height
        });
    };
    showConfirmDialog();
}

var AlertDialog = function () {
    // Settings passed into the dialog.
    var _settings = null;

    /// <summary>
    /// Show the AlertDialog.
    /// <summary>
    /// <param name="settings" type="JSON object">
    /// Contains all the settings for the dialog.  Settings are:
    ///     - message [Required] : Message to be displayed to the user.
    ///     - title [Required] : Message to be displayed to the title.
    ///     - icon [Required] : MessageBoxDialogs.xyz where xyz represents one of the icon types listed above.
    ///     - height [Optional] : New height for the Confirm dialog.  Format should be <value>px.  For example: 500px.  Default is 100px.
    ///     - width [Optional] : New width for the Confirm dialog.  Format should be <value>px.  For example: 500px.  Default is 300px.
    // </param>
    var ShowWindow = function (settings) {
        _settings = settings;

        try {

            if (_settings.height != null) {                
                $("#AlertDialog").data("kendoWindow").setOptions({ height: _settings.height });
            }
            if (_settings.width != null) {
                $("#AlertDialog").data("kendoWindow").setOptions({ width: _settings.width });
                $("#AlertDialog_MessageColumn").css("max-width", (parseInt(_settings.width.toString().replace("px", "") - 100) + "px"));
                $("#AlertDialog_ButtonRow").css("width", (parseInt(_settings.width.toString().replace("px", "") - 50) + "px"));
            }

            $("#AlertDialog_Image").attr("src", _settings.icon);
            $("#AlertDialog_Message").html(_settings.message);

            // Open the dialog.
            var wnd = $("#AlertDialog").data("kendoWindow");
            wnd.center().title(_settings.title).open();
        }
        catch (e) {
            alert(e);
        }
    };

    var OkButton_OnClick = function () {
        var wnd = $("#AlertDialog").data("kendoWindow");
        wnd.close();
    };

    var Window_OnActivate = function (e) {
        $("#AlertDialog_OkButton").focus();
    };

    return {
        ShowWindow: ShowWindow,
        OkButton_OnClick: OkButton_OnClick,
        Window_OnActivate: Window_OnActivate
    };
}();

var ConfirmDialog = function () {
    // Settings passed into the dialog.
    var _settings = null;

    /// <summary>
    /// Show the ConfirmDialog.
    /// <summary>
    /// <param name="settings" type="JSON object">
    /// Contains all the settings for the dialog.  Settings are:
    ///     - message [Required] : Message to be displayed to the user.
    ///     - icon [Optional] : MessageBoxDialogs.xyz where xyz represents one of the icon types listed above.
    ///     - parent [Optional] : the parent of the dialog as a jQuery object.
    ///     - callback [Optional] : the function that gets called when the dialog is closed.</param>
    ///     - height [Optional] : New height for the Confirm dialog.  Format should be <value>px.  For example: 500px.  Default is 100px.
    ///     - width [Optional] : New width for the Confirm dialog.  Format should be <value>px.  For example: 500px.  Default is 300px.
    // </param>
    var ShowWindow = function (settings) {
        _settings = settings;

        try {
            if (_settings.height != null) {
                $("#ConfirmDialog").data("kendoWindow").setOptions({ height: _settings.height });
            }
            if (_settings.width != null) {
                $("#ConfirmDialog").data("kendoWindow").setOptions({ width: _settings.width });
                $("#ConfirmDialog_MessageColumn").css("max-width", (parseInt(_settings.width.toString().replace("px", "") - 100) + "px"));
                $("#ConfirmDialog_ButtonRow").css("width", (parseInt(_settings.width.toString().replace("px", "") - 50) + "px"));
            }

            $("#ConfirmDialog_Image").attr("src", _settings.icon == null ? MessageBoxDialogs.QuestionIcon : _settings.icon);
            $("#ConfirmDialog_Message").html(_settings.message);
            $("#ConfirmDialog_YesButton").html(_settings.okText);
            $("#ConfirmDialog_NoButton").html(_settings.cancelText);

            // Open the dialog.
            var wnd = $("#ConfirmDialog").data("kendoWindow");
            wnd.center().title(_settings.title).open();
        }
        catch (e) {
            alert(e);
        }
    };

    var Button_OnClick = function (button) {
        var wnd = $("#ConfirmDialog").data("kendoWindow");
        wnd.close();

        if (_settings.callback != null && button == "Yes") {
            _settings.callback.call(_settings.params.thisFunction, _settings.params.event, _settings.params);
        }
    };

    var Window_OnActivate = function (e) {
        $("#ConfirmDialog_YesButton").focus();
    };

    return {
        ShowWindow: ShowWindow,
        Button_OnClick: Button_OnClick,
        Window_OnActivate: Window_OnActivate
    };
}();