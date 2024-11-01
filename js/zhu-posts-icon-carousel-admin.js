/**
 * 
 * 2021.07.29   Updated zhu_pic_init_widget_css_editor to support block based 
 *              widget editor introduced into WP 5.8.
 */

/**
 * Show or hide the width/height input boxes if size is set to 'custom' 
 * 
 * Invoked by the change event on the image list drop down.
 * 
 * @since 1.0.0
 * 
 * @param {string} field_id     ID of the drop down
 * @param {string} div_id       ID of the div to show/hide
 */
function zhu_pic_set_visabity_for_custom(field_id, div_id) {
    var v = jQuery('#' + field_id).val();
    if (v === 'custom') {
        jQuery('#' + div_id).show();
    } else {
        jQuery('#' + div_id).hide();
    }
}

/**
 * Show or hide div (containing sub-options) dependant if checkbox is checked
 * 
 * @since 1.0.0
 * 
 * @param {string} field_id     ID of checkbox field
 * @param {string} div_id       ID of div to set visability
 */
function zhu_pic_set_visabity_for_checkbox_div(field_id, div_id) {
    var v = jQuery('#' + field_id).is(':checked');
    if (v) {
        jQuery('#' + div_id).show();
    } else {
        jQuery('#' + div_id).hide();
    }
}

/**
 * Initialises a textarea to become a CSS input.  Uses CodeMirror.
 * 
 * If the textarea is not visible a monitor is created to ensure that the 
 * initialisation takes place when it becomes visible.
 * 
 * @since 1.0.0
 * 
 * @param {string} textarea_id  ID of the textarea field
 */
function zhu_pic_init_widget_css_editor(textarea_id) {

    //Ignore template version created by WordPress on the widgets admin screen
    if (textarea_id.indexOf('__i__') < 0) {

        // 2021.07.29   Add support for WP block based widget editor introduced into WP 5.8.

        if (zhu_pic_admin.use_widgets_block_editor) {

            // when using the block based widget editor (introduced in WP 5.8)
            // when the widget is displayed, the parent of the item with the class '.widget-inside'
            // has another parent with the class 'wp-block-legacy-widget__edit-form' that has the 
            // hidden attribute set when the widget form is not on display
            // Only when our textarea is visible can we initialize CodeMirror.  I.e. when parent div
            // 'wp-block-legacy-widget__edit-form' is not hidden.

            var widget_insides_parent = jQuery('#' + textarea_id).parents('.widget-inside').first().parent().parent();

            if (widget_insides_parent.attr('hidden') === undefined) {
                zhu_pic_init_widget_css_editor_create_codemirror(textarea_id);
            } else {
                // Not currently on display - We need to wait until the textarea becomes visible
                // https://gabrieleromanato.name/jquery-detecting-new-elements-with-the-mutationobserver-object/
                var only_once = false;
                var observer = new MutationObserver(function (mutations) {
                    if (false == only_once) {
                        if (widget_insides_parent.attr('hidden') === undefined) {
                            only_once = true; // prevent multiple initilizations
                            zhu_pic_init_widget_css_editor_create_codemirror(textarea_id);
                        }
                    }
                });
                var config = {
                    attributes: true
                };
                observer.observe(widget_insides_parent[0], config);
            }
        } else {
            // classic wigit (pre WP 5.8)
            // when the widget is displayed, the parent of the item with the class .widget-inside 
            // is assigned class 'open' - this in an operation performed by WordPress
            // Only when our textarea is visible can we initialize CodeMirror

            var widget_insides_parent = jQuery('#' + textarea_id).parents('.widget-inside').first().parent();
            if (widget_insides_parent.attr('class').indexOf('open') >= 0) {
                zhu_pic_init_widget_css_editor_create_codemirror(textarea_id);
            } else {
                // Not currently on display - We need to wait until the textarea becomes visible
                // https://gabrieleromanato.name/jquery-detecting-new-elements-with-the-mutationobserver-object/
                var only_once = false;
                var observer = new MutationObserver(function (mutations) {
                    if (false == only_once) {
                        if (widget_insides_parent.attr('class').indexOf('open') >= 0) {
                            only_once = true; // prevent multiple initilizations
                            zhu_pic_init_widget_css_editor_create_codemirror(textarea_id);
                        }
                    }
                });
                var config = {
                    attributes: true
                };
                observer.observe(widget_insides_parent[0], config);
            }
        }
    }

}

/**
 * Utility function for zhu_pic_init_widget_css_editor()
 * 
 * @since 1.0.0
 * 
 * Initialises CodeMirror and sets an attribute against the textarea 
 * as a flag to ensur we done repeat this action for the same textarea
 * 
 * @param {string} textarea_id      ID of the textarea
 */
function zhu_pic_init_widget_css_editor_create_codemirror(textarea_id) {
    var ta = jQuery('#' + textarea_id);
    //avoid initialization of the same textarea more than once
    //avoids issues with multiple calls when widget is displayed within WordPress's customizer
    var flagged = ta.attr('done-code-mirror-flag');
    if (undefined === flagged || 'done' !== flagged) {
        ta.attr('done-code-mirror-flag', 'done');
        var cei = wp.codeEditor.initialize(ta, zhu_pic_cm_settings.codeEditor);
        cei.codemirror.on('change', function (cm, obj) {
            //Copy the content of the CodeMirror editor into the textarea.
            cm.save();
            //Invoke the change event on the textarea, so the form's Save button becomes enabled
            jQuery('#' + textarea_id).trigger('change');
        });
    }
}

/**
 * Searches the DOM for all elements with class zhu-pic-numeric-input-validator 
 * with a 'for' attribute.  If the for attribute is set, it is epected this 
 * is the ID of a numeric input box.  A hook is then added to the input box's
 * change event to check if its value is within a valid range.  The range is
 * taken from the input's elements min and max attributes.  If the current
 * input value is out of range the contents of the zhu-pic-numeric-input-validator 
 * element is then set to display a message to the user informing them of the
 * valid range.
 * 
 * Called when document is read or when a widget is added by the user
 * 
 * @see jQuery(document).ready() below
 * 
 * @since 1.0.0
 */
function zhu_pic_dynamic_add_validation_events() {

    //find elements used to display validation meetings for numeric inputs
    jQuery('.zhu-pic-numeric-input-validator').each(function () {

        //find the input element (via id) that this element is display validation messages for
        var forFieldID = jQuery(this).attr('for');
        if (undefined !== forFieldID) {

            //Ignore template version created by WordPress for drap and drop purposes
            if (forFieldID.indexOf('__i__') < 0) {

                var display_element = this;
                jQuery('#' + forFieldID).on('change', function (event) {

                    var value = jQuery(this).val();
                    var min = jQuery(this).attr('min');
                    var max = jQuery(this).attr('max');

                    if (undefined !== min) {
                        min = parseInt(min);
                    }

                    if (undefined !== max) {
                        max = parseInt(max);
                    }

                    if (undefined !== min && value < min) {
                        jQuery(display_element).html(zhu_pic_admin.value_too_low + zhu_pic_validate_get_range_display(min, max)).show();
                    } else if (undefined !== max && value > max) {
                        jQuery(display_element).html(zhu_pic_admin.value_too_high + zhu_pic_validate_get_range_display(min, max)).show();
                    } else
                    {
                        jQuery(display_element).html('').hide();
                    }
                });
            }
        }

    });
}

/**
 * Builds string containing the allow valid rage base on the supplied values
 * 
 * @since 1.0.0
 * 
 * @param {int|undefied} min    minimum allowed value or undefined if unknown
 * @param {int|undefied} max    maximum allowed value or undefined if unknown
 * 
 * @returns {String}        Range to display
 */
function zhu_pic_validate_get_range_display(min, max) {

    if (undefined === min && undefined === max) {
        return '';
    } else if (undefined !== min && undefined !== max) {
        return ' (' + zhu_pic_admin.must_be + ' ' + min + '-' + max + ')';
    } else if (undefined !== min) {
        return ' (' + zhu_pic_admin.must_be + '=>' + min + ')';
    } else {
        return ' (' + zhu_pic_admin.must_be + '<=' + max + ')';

    }

}


jQuery(document).ready(function () {

    //The 'widget-added' event is Invoked when a widget is added to 
    //a SideBar or WordPress's Widgets Admin Page
    jQuery(document).on('widget-added', function (event, widgetContainer) {
        // is this a zhu post icon carousel widget - if so it 
        // will have a textarea with the class zhu-pic-1-css-editor
        var is_this_a_pic_1 = widgetContainer.find('.zhu-pic-1-css-editor');
        if (is_this_a_pic_1.length > 0) {
            zhu_pic_init_widget_css_editor(is_this_a_pic_1.attr('id'));
        }

        zhu_pic_dynamic_add_validation_events();
    });


    zhu_pic_dynamic_add_validation_events();
});
