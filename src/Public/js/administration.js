
/*
 * This function return current date in format dd/MM/yyyy
 */
function getCurrentDate() {
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1; //0-11
	var curr_year = d.getFullYear();
    return (curr_date + '/' + curr_month + '/' + curr_year);
}

/**
 * This function return parameter date in format dd/MM/yyyy
 * @param jsDate
 */
function formatDate(jsDate) {
    if (typeof jsDate === "string") return jsDate;

    var curr_date = jsDate.getDate();
    var curr_month = jsDate.getMonth() + 1; //0-11
    var curr_year = jsDate.getFullYear();
    return (curr_date + '/' + curr_month + '/' + curr_year);
}

function showLoading(active, divName) {
    if (divName == null || divName == "")
        divName = "loading";
    if (active != null)
        kendo.ui.progress($("#" + divName), active);
}

function htmlStarsFor(value) {
    // Min 0 - Max 5
    var emptyStar = "<img src='/Public/img/ui/star-empty-icon.png' alt='Empty star' title='Empty' />";
    var middleStar = "<img src='/Public/img/ui/star-middle-icon.png' alt='Empty star' title='Empty' />";
    var fullStar = "<img src='/Public/img/ui/star-full-icon.png' alt='Empty star' title='Empty' />";

    var currentValue;
    var dataReturn = "<div>";

    for (currentValue = 0; currentValue < 5; currentValue++) {
        if (currentValue < value && value >= currentValue + 1) {
            // Si 0 < 1.7 y 1.7 >= 1 - OK Full star
            dataReturn += fullStar;
        } else if (currentValue < value && value < currentValue + 1) {
            // Si 1 < 1.7 y 1.7 < 2 - OK Middle star
            dataReturn += middleStar;
        }
        else
        {
            // Si 2 > 1.7 o 1.7 < 3 - OK Empty star
            dataReturn += emptyStar;
        }
    }

    dataReturn += "</div>";

    return dataReturn;
}

function resizeGrid(grid, adjust) {
    var gridElement = $(grid),
        dataArea = gridElement.find(".k-grid-content"),
        gridHeight = gridElement.innerHeight(),
        otherElements = gridElement.children().not(".k-grid-content"),
        otherElementsHeight = 0;

    if (adjust == undefined) {
        adjust = 200;
    }

    otherElements.each(function () {
        otherElementsHeight += $(this).outerHeight();
    });

    var newHeight = window.innerHeight - otherElementsHeight - adjust;

    dataArea.height(newHeight);
    gridElement.height(newHeight + otherElementsHeight);
}