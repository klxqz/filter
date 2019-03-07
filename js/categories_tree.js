(function ($) {

    // private helper methods in closure

    var getId = function (el) {
        var regexp = /^category-(.*?)-handler$/;
        return regexp.test(el.attr('id')) ?
                el.attr('id').replace(regexp, function () {
            return parseInt(arguments[1], 10) || 0;
        }) : 0;
    };

    var getContext = function (el) {
        if (!getId(el)) {
            var p = el.parent(), t = p.next(), u = t.find('ul:first');
        } else {
            var p = el.parents('li:not(.drag-newposition):first'), t = p.find('ul:first'), u = t;
        }
        return {
            parent: p,
            target: t,
            ul: u
        };
    };

    var onCollapse = function (el) {
        var context = getContext(el);
        if (context.parent.attr('data-type') == 'category' && !context.parent.hasClass('dynamic')) {
            context.parent.trigger('count_subtree', true);
        }
        el.removeClass('darr').addClass('rarr');
        context.target.hide();
    };

    var onExpand = function (el) {
        var context = getContext(el);
        if (context.parent.attr('data-type') == 'category') {
            context.parent.trigger('count_subtree', false);
        }
        el.removeClass('rarr').addClass('darr');
        context.target.show();
    };

    /**
     * @param context
     * @param {Boolean} status
     */
    var setLoadingIcon = function (context, status) {
        var icon = context.parent.find('.loading:first');
        if (status) {
            icon.show();
        } else {
            icon.hide();
        }
    };

    var collapse = function (el) {
        onCollapse(el);
        $.get('?plugin=filter&module=settings&action=categoryExpand&id=' + getId(el) + '&collapsed=1');
    };

    var expand = function (el) {
        if (el.data('loading_content')) {
            return;
        }
        var context = getContext(el);
        if (!context.ul.length) {
            setLoadingIcon(context, true);
        } else {
            onExpand(el);
        }

        var loading_content = !context.ul.length;
        el.data('loading_content', loading_content);
        $.get('?plugin=filter&module=settings&action=categoryExpand&id=' + getId(el) + (loading_content ? '&tree=1' : ''),
                function (html) {
                    if (loading_content) {
                        if (context.target.length) {
                            context.target.append(html);
                        } else {
                            context.parent.append(html);
                        }
                        setLoadingIcon(context, false);
                        onExpand(el);
                        el.data('loading_content', false);
                    }
                }
        );
    };

    var expandFilters = function (el) {
        $('#s-category-list .selected').removeClass('selected');
        $(el).parent().addClass('selected');
        var category_id = $(el).data('category-id');
        $('[name="route_settings[category_id]"]').val(category_id);
        var selector = '.filter-table tbody tr';
        $(selector).hide();
        if (category_id) {
            selector += '[data-category-id=' + category_id + ']';
        }
        $(selector).show();
    };




    $.categories_tree = {
        init: function () {
            $('#s-category-list-block').off('click', '.collapse-handler-ajax').on('click', '.collapse-handler-ajax', function () {
                var self = $(this);
                if (self.hasClass('darr')) {
                    collapse(self);
                } else {
                    expand(self);
                }
            });

            $('#s-category-list-block').off('click', '.s-product-list').on('click', '.s-product-list', function () {
                var self = $(this);
                expandFilters(self);
                var f = $("#plugins-settings-form");
                $.post(f.attr('action'), f.serialize());
                return false;
            });
            if ($('#s-category-list-block li.dr.selected').length) {
                expandFilters($('#s-category-list-block li.dr.selected a'));
            }

        }
    };
})(jQuery);