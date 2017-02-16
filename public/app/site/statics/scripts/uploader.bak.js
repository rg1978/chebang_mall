/**
 * jQuery Ajax File Uploader
 * Modified by Tyler Chao
 */

(function($) {
    $.fn.AjaxFileUpload = function(options) {
        var settings = {
            params: {},
            action: 'upload.php',
            size: null,
            limit: 0,
            onStart: function() { },
            onComplete: function(response) { },
            onCancel: function() { },
            validate_extensions : true,
            valid_extensions : ['gif','png','jpg','jpeg'],
            submit_button : null
        };

        var uploading_file = false;

        if (options) {
            $.extend(settings, options);
        }


        // 'this' is a jQuery collection of one or more (hopefully)
        //  file elements, but doesn't check for this yet
        return this.each(function() {
            var $element = $(this),
                $form,
                $iframe;

            // Skip elements that are already setup. May replace this
            //  with uninit() later, to allow updating that settings
            if($element.data('ajaxUploader-setup') === true) return;

            $element.on('change', function(e) {
                settings.size = $element.data('size') || settings.size;
                settings.action = $element.data('remote') || settings.action,
                settings.limit = $element.data('max') || settings.limit;

                // since a new image was selected, reset the marker
                uploading_file = false;

                upload_file(e);
            });

            var upload_file = function(e) {
                if($element.val() == '') return settings.onCancel.apply($element, [settings.params]);

                if (settings.limit) {
                    var length = $element.parent().siblings('.img-thumbnail').length,
                        filelen;
                    if($element[0].multiple && $element[0].files) {
                        filelen = $element[0].files.length;
                    }
                    else {
                        filelen = 1;
                    }
                    if(length + filelen > settings.limit) {
                        alert('超出限制，最多上传' + limit + '张。');
                        return false;
                    }
                }

                // make sure extension is valid
                var ext = $element.val().split('.').pop().toLowerCase();
                if(settings.validate_extensions && $.inArray(ext, settings.valid_extensions) < 0) {
                    // Pass back to the user
                    settings.onComplete.apply($element, [{status: false, message: '文件类型错误，必须为 ' + settings.valid_extensions.join(', ') + '文件。'}, settings.params]);
                } else {
                    uploading_file = true;

                    // Creates the form, extra inputs and iframe used to
                    //  submit / upload the file
                    wrapElement($element);

                    // Call user-supplied (or default) onStart(), setting
                    //  it's this context to the file DOM element
                    var ret = settings.onStart.apply($element, [settings.params]);

                    // let onStart have the option to cancel the upload
                    if(ret !== false) {
                        $form.submit(function(e) {
                            e.stopPropagation();
                        }).submit();
                    }
                }
            };

            // Mark this element as setup
            $element.data('ajaxUploader-setup', true);

            /*
            // Internal handler that tries to parse the response
            //  and clean up after ourselves.
            */
            var handleResponse = function(loadedFrame, element, form) {
                var response, responseStr = loadedFrame.contentWindow.document.body.innerText;
                try {
                    response = JSON.parse($.trim(responseStr));
                } catch(e) {
                    response = responseStr;
                }

                // Tear-down the wrapper form
                // $(loadedFrame).remove();
                // $(form).remove();

                uploading_file = false;

                // Pass back to the user
                settings.onComplete.apply(element, [response, element, settings.params]);
            };

            /*
            // Wraps element in a <form> tag, and inserts hidden inputs for each
            //  key:value pair in settings.params so they can be sent along with
            //  the upload. Then, creates an iframe that the whole thing is
            //  uploaded through.
            */
            var wrapElement = function(element) {
                // Create an iframe to submit through, using a semi-unique ID
                var elem_id = 'ajaxUploader_' + Math.round(new Date().getTime() / 1000);

                // Wrap it in a form
                settings.params['MAX_FILE_SIZE'] = settings.size;
                settings.params['FILE_LIMIT'] = settings.limit;

                $form = $('<form action="' + settings.action + '" method="post" id="' + elem_id + '_form" enctype="multipart/form-data" encoding="multipart/form-data" target="' + elem_id + '_iframe" style="display:none;"/>').append(element.clone())
                    // Insert <input type='hidden'>'s for each param
                    .append(function() {
                        var key, html = '';
                        for(key in settings.params) {
                            var paramVal = settings.params[key];
                            if (typeof paramVal === 'function') {
                                paramVal = paramVal();
                            }
                            html += '<input type="hidden" name="' + key + '" value="' + paramVal + '" />';
                        }
                        return html;
                    })
                .appendTo(document.body);

                $iframe = $('<iframe src="javascript:false;" width="0" height="0" frameborder="0" style="display:none;" name="'+ elem_id +'_iframe" id="' + elem_id + '_iframe"/>')
                    .load(function() {
                        handleResponse(this, element, $form);
                    })
                .appendTo(document.body);

            }

        });
    }
})(jQuery);


$(function () {
    var uploader = $('.action-file-input');
    uploader.AjaxFileUpload({
        onComplete: function(rs, element) {
            if (rs.error) {
                return alert(rs.message);
            }
            var data = $.makeArray(rs.data);
            var name = element.attr('name');
            var container = element.parents('.images-uploader');
            $.each(data, function () {
                element.parent('.action-upload').before('<div class="handle img-thumbnail"><i class="icon-close-b action-remove"></i><a href="' + this.url + '" target="_blank"><img src="' + this.url + '"></a><input type="hidden" name="' + name + '" value="' + this.image_id + '"></div>');
            });
            container.on('click', '.action-remove', function (e) {
                $(this).parent().remove();
            });
            container.on('mouseover', '.img-thumbnail', function (e) {
                $(this).find('.action-remove').show();
            });
            container.on('mouseout', '.img-thumbnail', function (e) {
                $(this).find('.action-remove').hide();
            });
        }
    });
})
