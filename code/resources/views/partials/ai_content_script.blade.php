<script nonce="{{ csp_nonce() }}">
    (function ($) {
        "use strict";
        /** ai content generation event */
        $(document).on('click', '.update', function (e) {
            e.preventDefault()
            var content = JSON.parse($(this).attr('data-content').replace(/<script.*?>.*?<\/script>/gi, ''));
            var modal = $('#content-form')
            modal.find('#contentForm').attr('action', '{{request()->routeIs("user.*") ? route("user.ai.content.update") : route("admin.content.update")}}')
            modal.find('.modal-title').html("{{translate('Update Content')}}")

            modal.find('input[name="name"]').val(content.name)
            modal.find('input[name="id"]').attr('disabled', false)
            modal.find('input[name="id"]').val(content.id)
            modal.find('textarea[name="content"]').val(content.content)
            modal.modal('show')
        })



        $(document).on('click', '.upload', function (e) {
            e.preventDefault()
            var modal = $('#upload-form')
            modal.find('#uploadForm').attr('action', '{{request()->routeIs("user.*") ? route("user.ai.content.image.upload") : route("admin.content.image.upload")}}')
            modal.find('.modal-title').html("{{translate('Upload Image Content')}}")

            modal.modal('show')
        })

        $(document).on('click', '.upload-video', function (e) {
            e.preventDefault()
            var modal = $('#upload-form')
            modal.find('#uploadForm').attr('action', '{{request()->routeIs("user.*") ? route("user.ai.content.video.upload") : route("admin.content.video.upload")}}')
            modal.find('.modal-title').html("{{translate('Upload Video Content')}}")
            modal.modal('show')
        })


        $(document).on('click', '.template-category', function (e) {
            e.preventDefault()

            var category = $(this).attr('data-category-id');
            var parent = $(this).attr('data-parent-id');
            var moduleType = $(this).attr('data-module');

            console.log(moduleType);


            $.ajax({
                method: 'post',
                url: "{{route('get.template.category')}}",
                dataType: 'json',
                beforeSend: function () {
                    $('.template-category-loader').removeClass('d-none');
                },

                data: {
                    "category_id": category,
                    "parent_id": parent,
                    "module_type": moduleType,
                    "user_id": "{{request()->routeIs('user.*') ? $user->id : null}}",
                    "_token": "{{csrf_token()}}",
                },
                success: function (response) {


                    if (response.status) {
                        var cleanContent = DOMPurify.sanitize(response.html);

                        if (moduleType == 'image') {
                            $('.image-category-section').html(cleanContent)

                        }
                        if (moduleType == 'video') {
                            $('.video-category-section').html(cleanContent)
                        } else {
                            $('.category-section').html(cleanContent)
                        }
                    }
                    else {
                        toastr(response.message, 'danger')
                    }
                },
                error: function (error) {

                    handleAjaxError(error);
                },

                complete: function () {
                    $('.template-category-loader').addClass('d-none');
                },
            })
        })

        $(document).on('click', '.select-template', function (e) {

            e.preventDefault()
            var id = $(this).attr('data-template-id');
            var templatetype = $(this).attr('data-templateType');


            var url = '{{ route("template.config", ["id" => ":id"]) }}';
            if (id) {

                if (templatetype == 'image') {
                    $('#imageTemplateId').val(id);
                } if (templatetype == 'video') {
                    $('#videoTemplateId').val(id);
                } else {
                    $('#templateId').val(id);
                }


                $('.select-template').removeClass('active');
                $(this).addClass('active')
            } else {
                $('.select-template').removeClass('active');
            }

            url = url.replace(':id', id).replace(':html', true);

            $.ajax({
                method: 'get',
                url: url,

                data: {
                    "user_id": "{{request()->routeIs('user.*') ? $user->id : null}}",
                    "template_id": id,
                    "template_type": templatetype
                },
                dataType: 'json',

                beforeSend: function () {
                    $('.input-section-loader').removeClass('d-none');
                },

                success: function (response) {
                    if (response.status) {
                        var cleanContent = DOMPurify.sanitize(response.html);

                        if (templatetype == 'image') {
                            $(".image-template-input-section").html(cleanContent);

                        } if (templatetype == 'video') {
                            $(".video-template-input-section").html(cleanContent);

                        } else {
                            $(".template-input-section").html(cleanContent);
                        }
                    } else {
                        toastr("Template not found!!", 'danger')
                    }
                },
                error: function (error) {

                    handleAjaxError(error);

                },
                complete: function () {
                    $('.input-section-loader').addClass('d-none');
                },

            })
        })

        var inputObj = {};
        $(document).on('keyup', ".prompt-input", function (e) {
            var value = $(this).val();
            var index = $(this).attr('data-name');
            if (value == "") {
                if (inputObj.hasOwnProperty(index)) {
                    delete inputObj[index];
                }
            }
            else {
                inputObj[index] = value;
            }

            replace_prompt();

        })


        function replace_prompt() {
            var originalPrompt = $('#promptPreview').attr('data-prompt_input');
            var prompt = originalPrompt;
            var len = Object.keys(inputObj).length

            if (len > 0) {
                for (var index in inputObj) {
                    prompt = prompt.replace(index, inputObj[index]);
                }
                $('#promptPreview').html(prompt);
            }
            else {
                $('#promptPreview').html($('#promptPreview').attr('data-prompt_input'));
            }
        }




        $(document).on('click', '.resubmit-ai-form', function (e) {
            $('.ai-content-form').submit()

            $(this).prop("disabled", true);

            $(this).html(`Generating <span class='ms-1' id="regenarate-loading"><span>&bull;</span><span>&bull;</span><span>&bull;</span></span>`)


        });

        $(document).on('click', '.resubmit-ai-image-form', function (e) {
            $('.ai-content-image-form').submit()

            $(this).prop("disabled", true);

            $(this).html(`Generating <span class='ms-1' id="regenarate-loading"><span>&bull;</span><span>&bull;</span><span>&bull;</span></span>`)


        });

        $(document).on('click', '.resubmit-ai-video-form', function (e) {
            $('.ai-content-video-form').submit()

            $(this).prop("disabled", true);

            $(this).html(`Generating <span class='ms-1' id="regenarate-loading"><span>&bull;</span><span>&bull;</span><span>&bull;</span></span>`)


        });


        $(document).on('submit', '.ai-content-form', function (e) {

            var data        = new FormData(this);
            var route       = $(this).attr('data-route');


            $.ajax({
                method: 'post',
                url: route,
                dataType: 'json',
                beforeSend: function () {

                    $('.ai-btn').prop("disabled", true);

                    if ("{{request()->routeIs('user.*')}}") {

                        $('.ai-btn').html(`{{translate('Generate')}} <i class="bi bi-send  generate-icon-btn"></i>`)
                        $('.ai-btn').html(`{{translate('Generate')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>`)
                    }
                    else {
                        $('.generate-icon-btn').addClass('d-none');
                        $('.ai-btn').addClass('btn__dots--loading');

                        $('.ai-btn').append('<span class="btn__dots"><i></i><i></i><i></i></span>');
                    }

                },
                cache: false,
                processData: false,
                contentType: false,
                data: data,
                success: function (response) {

                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-form').html(`Not satisfy? Retry`);
                    $('.resubmit-ai-form').prop("disabled", false);

                    if (response.status) {

                        $('.ai-modal-title').html('Result')

                        if (response.image_content) {

                            var images = response.image_content;
                            var contentHtml = '';

                            images.forEach(function (image, index) {
                                contentHtml += `
                            <div class="col-lg-3 col-md-4 mb-4">
                                <div class="card image-card position-relative">
                                <img src="${image}" class="card-img-top" alt="Generated Image">
                                <div class="form-check position-absolute top-0 end-0 m-2">
                                    <input class="form-check-input image-check" name="image_urls[]" type="checkbox" value="${image}" id="check-${index}" >
                                </div>
                                </div>
                            </div>
                            `;
                            });

                            let contentDiv = $('#image-content');

                            $('#image-content').html(contentHtml);
                            $('#ai-image-form').hide();
                            $('.ai-content-div').removeClass('d-none');

                        } else {
                            var cleanContent = DOMPurify.sanitize(response.message);
                            $('#content').html(cleanContent);
                            $('#ai-form').hide();
                            $('.ai-content-div').removeClass('d-none');
                        }
                    }
                    else {
                        toastr(response.message, "danger")
                    }

                },
                error: function (error) {
                    $('.resubmit-ai-form').html(`Not satisfy? Retry`);
                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-form').prop("disabled", false);

                    handleAjaxError(error);


                },
                complete: function () {

                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-form').html(`Not satisfy? Retry`);
                    $('.resubmit-ai-form').prop("disabled", false);

                    if ("{{request()->routeIs('user.*')}}") {
                        $('.ai-btn').html(`{{translate('Generate')}}<i class="bi bi-send  generate-icon-btn"></i>`)
                    }
                    else {
                        $('.generate-icon-btn').removeClass('d-none');
                        $('.ai-btn').removeClass('btn__dots--loading');
                        $('.ai-btn').find('.btn__dots').remove();
                    }

                },
            })

            e.preventDefault();
        });



        $(document).on('submit', '.ai-content-image-form', function (e) {
            var data = new FormData(this)
            var route = $(this).attr('data-route')


            $.ajax({
                method: 'post',
                url: route,
                dataType: 'json',
                beforeSend: function () {

                    $('.ai-btn').prop("disabled", true);

                    if ("{{request()->routeIs('user.*')}}") {

                        $('.ai-btn').html(`{{translate('Generate')}} <i class="bi bi-send  generate-icon-btn"></i>`)
                        $('.ai-btn').html(`{{translate('Generate')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>`)
                    }
                    else {
                        $('.generate-icon-btn').addClass('d-none');
                        $('.ai-btn').addClass('btn__dots--loading');

                        $('.ai-btn').append('<span class="btn__dots"><i></i><i></i><i></i></span>');
                    }

                },
                cache: false,
                processData: false,
                contentType: false,
                data: data,
                success: function (response) {

                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-image-form').html(`Not satisfy? Retry`);
                    $('.resubmit-ai-image-form').prop("disabled", false);

                    if (response.status) {

                        $('.ai-modal-title').html('Result')

                        if (response.image_content) {

                            var images = response.image_content;
                            var contentHtml = '';

                            images.forEach(function (image, index) {
                                contentHtml += `
                            <div class="col-lg-3 col-md-4 mb-4">
                                <div class="card image-card position-relative">
                                <img src="${image}" class="card-img-top" alt="Generated Image">
                                <div class="form-check position-absolute top-0 end-0 m-2">
                                    <input class="form-check-input image-check" name="image_urls[]" type="checkbox" value="${image}" id="check-${index}" >
                                </div>
                                </div>
                            </div>
                            `;
                            });

                            let contentDiv = $('#image-content');

                            $('#image-content').html(contentHtml);
                            $('#ai-image-form').hide();
                            $('.ai-content-div').removeClass('d-none');

                        } else {
                            var cleanContent = DOMPurify.sanitize(response.message);
                            $('#content').html(cleanContent);
                            $('#ai-form').hide();
                            $('.ai-content-div').removeClass('d-none');
                        }
                    }
                    else {
                        toastr(response.message, "danger")
                    }

                },
                error: function (error) {
                    $('.resubmit-ai-form').html(`Not satisfy? Retry`);
                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-form').prop("disabled", false);

                    handleAjaxError(error);


                },
                complete: function () {

                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-form').html(`Not satisfy? Retry`);
                    $('.resubmit-ai-form').prop("disabled", false);

                    if ("{{request()->routeIs('user.*')}}") {
                        $('.ai-btn').html(`{{translate('Generate')}}<i class="bi bi-send  generate-icon-btn"></i>`)
                    }
                    else {
                        $('.generate-icon-btn').removeClass('d-none');
                        $('.ai-btn').removeClass('btn__dots--loading');
                        $('.ai-btn').find('.btn__dots').remove();
                    }

                },
            })

            e.preventDefault();
        });



        $(document).on('submit', '.ai-content-video-form', function (e) {
            var data = new FormData(this)
            var route = $(this).attr('data-route')


            $.ajax({
                method: 'post',
                url: route,
                dataType: 'json',
                beforeSend: function () {

                    $('.ai-btn').prop("disabled", true);

                    if ("{{request()->routeIs('user.*')}}") {

                        $('.ai-btn').html(`{{translate('Generate')}} <i class="bi bi-send  generate-icon-btn"></i>`)
                        $('.ai-btn').html(`{{translate('Generate')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>`)
                    }
                    else {
                        $('.generate-icon-btn').addClass('d-none');
                        $('.ai-btn').addClass('btn__dots--loading');
                        $('.ai-btn').append('<span class="btn__dots"><i></i><i></i><i></i></span>');
                    }

                },
                cache: false,
                processData: false,
                contentType: false,
                data: data,
                success: function (response) {

                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-video-form').html(`Not satisfy? Retry`);
                    $('.resubmit-ai-video-form').prop("disabled", false);

                    if (response.status) {

                        $('.ai-modal-title').html('Result')

                        if (response.video_content) {


                            var videos = response.video_content;
                            var contentHtml = '';

                            videos.forEach(function (video, index) {
                                contentHtml += `
                            <div class="col-lg-3 col-md-4 mb-4">
                                <div class="card image-card position-relative">
                                <video class="card-img-top" controls>
                                    <source src="${video}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>

                                <div class="form-check position-absolute top-0 end-0 m-2">
                                    <input class="form-check-input video-check" name="video_urls[]" type="checkbox" value="${video}" id="check-${video}" >
                                </div>
                                </div>
                            </div>
                            `;
                            });

                            let contentDiv = $('#video-content');

                            $('#video-content').html(contentHtml);
                            $('#ai-video-form').hide();
                            $('.ai-content-div').removeClass('d-none');

                        } else {
                            var cleanContent = DOMPurify.sanitize(response.message);
                            $('#content').html(cleanContent);
                            $('#ai-form').hide();
                            $('.ai-content-div').removeClass('d-none');
                        }
                    }
                    else {
                        toastr(response.message, "danger")
                    }

                },
                error: function (error) {
                    $('.resubmit-ai-form').html(`Not satisfy? Retry`);
                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-form').prop("disabled", false);

                    handleAjaxError(error);


                },
                complete: function () {

                    $('.ai-btn').prop("disabled", false);
                    $('.resubmit-ai-form').html(`Not satisfy? Retry`);
                    $('.resubmit-ai-form').prop("disabled", false);

                    if ("{{request()->routeIs('user.*')}}") {
                        $('.ai-btn').html(`{{translate('Generate')}}<i class="bi bi-send  generate-icon-btn"></i>`)
                    }
                    else {
                        $('.generate-icon-btn').removeClass('d-none');
                        $('.ai-btn').removeClass('btn__dots--loading');
                        $('.ai-btn').find('.btn__dots').remove();
                    }

                },
            })

            e.preventDefault();
        });


        $(document).on('click', '.prompt-from-content', function (e) {
            e.preventDefault();

            const $btn      = $(this);
            const type      =$(this).attr('data-type');
            const content   = $('#postPreview').val();
            const modal     =$btn.closest('.modal');
            var route       = $(this).attr('data-route')

            $.ajax({
                url: route,
                type: 'POST',
                dataType: 'json',
                data: {
                     post_content: content,
                     type : type,
                     _token: '{{ csrf_token() }}'
                },
                beforeSend: function () {
                    $btn.prop('disabled', true);
                    $btn.addClass('disabled').css('pointer-events', 'none');

                    if ("{{request()->routeIs('user.*')}}") {

                        $btn.html(`{{translate('Generate prompt from content')}} <i class="bi bi-robot  generate-icon-btn"></i>`)
                        $btn.html(`{{translate('Generate prompt from content')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>`)
                    }
                    else {
                        $('.generate-icon-btn').addClass('d-none');
                        $btn.addClass('btn__dots--loading');
                        $btn.append('<span class="btn__dots"><i></i><i></i><i></i></span>');

                    }
                },
                success: function (response) {

                    $btn.prop('disabled', false);
                    $btn.removeClass('disabled').css('pointer-events', '');

                    console.log(response);
                    if (response.status && response.message) {

                        modal.find('textarea#promptPreview').val(response.message);
                    }else{
                        toastr(response.message, "danger")

                    }

                },
                complete: function () {

                    $btn.removeClass('disabled').css('pointer-events', '');

                    $btn.html(`
                        {{translate('Generate prompt from content')}}
                        <i class="bi bi-robot generate-icon-btn"></i>
                    `);
                    $btn.removeClass('btn__dots--loading');
                    $btn.find('.btn__dots').remove();
                },
                error: function (error) {
                    $btn.prop('disabled', false);
                    $btn.removeClass('disabled').css('pointer-events', '');
                    handleAjaxError(error);
                }
            });
        });


    })(jQuery);
</script>
