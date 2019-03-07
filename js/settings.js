(function ($) {
    $.filter_settings = {
        options: {},
        init: function (options) {
            this.initRouteSelector();
            this.initButtons();
        },
        initButtons: function () {
            var self = this;
            $('#ibutton-status').iButton({
                labelOn: "Вкл", labelOff: "Выкл"
            }).change(function () {
                var self = $(this);
                var enabled = self.is(':checked');
                if (enabled) {
                    self.closest('.field-group').siblings().show(200);
                } else {
                    self.closest('.field-group').siblings().hide(200);
                }
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
            });



            $(document).on('click', '.add-filter-btn', function () {
                var filter_category_id = $('[name="route_settings[category_id]"]').val();
                var route_hash = $('[name=route_hash]').val();
                self.filterDialog(null, filter_category_id, route_hash);
                return false;
            });

            $(document).on('click', '.edit-filter-btn', function () {
                var filter_category_id = $('[name="route_settings[category_id]"]').val();
                var route_hash = $('[name=route_hash]').val();
                self.filterDialog($(this).closest('tr').data('filter-id'), filter_category_id, route_hash);
                return false;
            });
            $(document).on('click', '.delete-filter-btn', function () {
                var $self = $(this);
                $.ajax({
                    type: 'POST',
                    url: '?plugin=filter&module=filter&action=delete',
                    dataType: 'json',
                    data: {
                        id: $(this).closest('tr').data('filter-id')
                    },
                    success: function (data, textStatus, jqXHR) {
                        if (data.status == 'ok') {
                            $self.closest('tr').remove();
                        }
                        if (data.status == 'fail') {
                            alert(data.errors.join(', '));
                        }
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });

            $(document).on('keyup change', '#search', function () {
                _this = this;
                $('#s-category-list .selected').removeClass('selected');
                $('#s-category-list #category-0').addClass('selected');
                $('.filter-table tbody tr').each(function () {
                    if ($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });


            $(document).on('click', '#s-filter-views a', function () {
                var li = $(this).parent();
                var view = li.data('view');
                if (view == 'split') {
                    $('.content.s-filter-form').addClass('left300px');
                    $('.s-filter-internal-sidebar').show();
                } else {
                    $('#s-category-list-block #category-0 a').click();
                    $('.s-filter-internal-sidebar').hide();
                    $('.content.s-filter-form').removeClass('left300px');
                }
                $('#s-filter-views li').removeClass('selected');
                li.addClass('selected');
                $('[name="route_settings[view]"]').val(view);
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
                return false;
            });


        },
        initRouteSelector: function () {
            var self = this;
            $('#route-selector').change(function () {
                var route_selector = $(this);
                var loading = $('<i class="icon16 loading"></i>');
                $(this).attr('disabled', true);
                $(this).after(loading);
                $('.route-container').find('input,select,textarea').attr('disabled', true);
                $('.route-container').slideUp('slow');
                $.get('?plugin=filter&module=settings&action=route&route_hash=' + $(this).val(), function (response) {
                    $('.route-container').html(response);
                    loading.remove();
                    route_selector.removeAttr('disabled');
                    $('.route-container').slideDown('slow');

                    $('.route-container .ibutton').iButton({
                        labelOn: "Вкл",
                        labelOff: "Выкл",
                        className: 'mini'
                    }).change(function () {
                        var f = $("#plugins-settings-form");
                        $.post(f.attr('action'), f.serialize());
                    });

                    var f = $("#plugins-settings-form");
                    $.post(f.attr('action'), f.serialize());
                });
                return false;
            }).change();
        },
        showFilterDialog: function () {
            var self = this;
            $('#filter-dialog').waDialog({
                disableButtonsOnSubmit: true,
                onLoad: function () {
                    if ($('#filter-description-content').length) {
                        self.initWysiwyg($(this));
                    }
                },
                onSubmit: function (d) {
                    var form = $(this);
                    if ($('#filter-description-content').length) {
                        $('#filter-description-content').waEditor('sync');
                        $('#filter-seo-description-content').waEditor('sync');
                    }
                    $.ajax({
                        type: 'POST',
                        url: form.attr('action'),
                        dataType: 'json',
                        data: form.serialize(),
                        success: function (data, textStatus, jqXHR) {
                            if (data.status == 'ok') {
                                $('#dialog-response').text(data.data.message);
                                $('#dialog-response').css('color', 'green');
                                $('#filter-dialog .cancel').click();
                                var filter_tr = $('#filter-tmpl').tmpl(data.data)
                                if ($('.filter-table').find('tr[data-filter-id="' + data.data.id + '"]').length) {
                                    $('.filter-table').find('tr[data-filter-id="' + data.data.id + '"]').replaceWith(filter_tr);
                                } else {
                                    $('.filter-table tbody').append(filter_tr);
                                }
                            }
                            if (data.status == 'fail') {
                                $('.dialog-buttons').find('input[type=submit]').removeAttr('disabled');
                                $('#dialog-response').text(data.errors.join(', '));
                                $('#dialog-response').css('color', 'red');
                            }

                        }, error: function (jqXHR, textStatus, errorThrown) {
                            alert(jqXHR.responseText);
                        }
                    });
                    return false;
                }
            });
        },
        filterDialog: function (filter_id, filter_category_id, route_hash) {
            filter_id = filter_id || null;
            var d = $('#filter-dialog');
            var p;
            if (!d.length) {
                p = $('<div></div>').appendTo('body');
            } else {
                p = d.parent();
            }
            var url = '?plugin=filter&module=filter&action=dialog';
            if (filter_id) {
                url += '&id=' + filter_id;
            }
            if (filter_category_id) {
                url += '&category_id=' + filter_category_id;
            }
            if (route_hash) {
                url += '&route_hash=' + route_hash;
            }
            var self = this;
            p.load(url, function () {
                self.showFilterDialog();
            });

        },
        initWysiwyg: function (d) {
            var field = d.find('.field.description');
            field.find('i').hide();
            field.find('.s-editor-core-wrapper').show();
            var height = (d.find('.dialog-window').height() * 0.8) || 350;
            $('#filter-description-content').waEditor({
                lang: wa_lang,
                toolbarFixedBox: false,
                maxHeight: height,
                minHeight: height,
                uploadFields: d.data('uploadFields')
            });
        }
    };
})(jQuery); 