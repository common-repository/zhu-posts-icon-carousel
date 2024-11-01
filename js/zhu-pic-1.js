/**
 * Finds the child element with class zhu-pic-1-strip and configures animation
 * to scroll to the left.  
 * 
 * @since 1.0.0
 * 
 * @returns {undefined}
 */
jQuery.fn.zhu_pic_scroll_a_step = function () {
    var th = this;

    var the_strip = jQuery(this).find('.zhu-pic-1-strip').first();
    var num_images = the_strip.children('a').length;
    if (num_images > 1) {

        // images are surrounded by <a> hence looking for <a> rather than <img>
        var images = the_strip.find('a');

        // get duration of scroll
        var scroll_duration = parseInt(the_strip.attr('scroll_duration'));
        if (scroll_duration < 0) {
            scroll_duration = 1000;
        }

        // get duration of pause 
        var pause_duration = parseInt(the_strip.attr('pause_duration'));
        if (pause_duration < 0 || isNaN(pause_duration)) {
            pause_duration = 5000;
        }

        // how far to scroll is determined by the width of the first image
        var first_image = images.first();
        var second_image = images.next().first();
        scroll_to_left = second_image.position().left;

        //copy a clone of first child to end and hide
        the_strip.append(first_image.clone().hide());

        //set fading of all images, except first which we are fading out quickly
        var step = 1.0 / num_images;
        var cnt = 0, opacity_value = 1.0;

        the_strip.children('a').each(function () {
            cnt = cnt + 1;

            if (cnt > 1) {
                jQuery(this).fadeTo(scroll_duration, opacity_value);
                opacity_value -= step;
            }
        });

        //fade out first preview and fade in second
        var the_previews = jQuery(this).find('.zhu-pic-1-preview').first();
        var previews = the_previews.find('.zhu-pic-1-preview-artice');

        var first_preview = previews.first();


        var second_preview = previews.next().first();
        first_preview.fadeOut(scroll_duration);
        second_preview.fadeIn(scroll_duration);
        the_previews.append(first_preview.clone().hide());

        //fade out first image quickly on carousel - this 
        //don't fade to 0.00 or fadeOut otherwise it becomes hidden and the UI "jumps"
        //doing this quicker that the scrolling duration, in my opnion, produces a better ux/feel
        first_image.fadeTo(scroll_duration / 3, 0.01);

        // remember contect of which instance of the widget we are working on 
        var this_this = this;

        //move carousel to negative left position relative to its parent
        the_strip.animate(
                {
                    'left': '-=' + scroll_to_left
                },
                scroll_duration,
                function () {
                    //remove image scrolled out of view
                    the_strip.find('a').first().remove();

                    //move strip back to original 0 relative position
                    the_strip.css({left: 0});

                    //preview area - remove first child
                    var the_previews = jQuery(this_this).find('.zhu-pic-1-preview').first();
                    var previews = the_previews.find('.zhu-pic-1-preview-artice').first().remove();

                    //wait and repeat scroll
                    setTimeout(function () {
                        th.zhu_pic_scroll_a_step();
                    }
                    , pause_duration);

                }
        );
    }
};


jQuery(document).ready(function () {
    jQuery('.zhu-pic-1-widget-inner').each(
            function () {
                jQuery(this).zhu_pic_scroll_a_step();
            });
});

