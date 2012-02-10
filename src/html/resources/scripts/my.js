
// toggle search form
var toggle_field_toggled = false;
var toggleField = function() {
    if (toggle_field_toggled) {
        $('#search > div').show();
        $('#toggle_field').text('▲');
        toggle_field_toggled = false;
    } else {
        $('#search > div').hide();
        $('#toggle_field').text('▼');
        toggle_field_toggled = true;
    }
}

// pagination
var paginationCallbackIgnore = true;
var paginationCallback = function(page_index, jq) {
    if (paginationCallbackIgnore) {
        paginationCallbackIgnore = false;
        return false;
    }
    $("#pagenum").val(page_index);
    $("#searchForm").submit();
    return false;
};
var paginationOpts = {
	    callback: paginationCallback,
	    current_page: parseInt($("#pagenum").val(),10),
	    items_per_page: parseInt($("#limit").val(),10),
	    num_display_entries: 10,
	    num_edge_entries: 2,
	    prev_text: "前へ",
	    next_text: "次へ"
};
