//商家中心商品图片拖拽功能（临时存放地）
var dragDom = function(className) {
    //图片拖拽
    function handleDragStart(e) {
      this.style.opacity ='1';
      dragSrcEl = this;
      e.dataTransfer.effectAllowed ='move';
      e.dataTransfer.setData('innerhtml', this.innerHTML);
    }
    function handleDragEnter(e) {
      this.classList.add('over');
    }
    function handleDragLeave(e) {
      this.classList.remove('over');
    }
    function handleDragOver(e) {
      if (e.preventDefault) {
        e.preventDefault();
      }
      return false;
    }
    //拖拽完成后，作用在拖拽元素上
    function handleDrop(e) {
      if (dragSrcEl != this) {
        dragSrcEl.innerHTML = this.innerHTML;
        this.innerHTML = e.dataTransfer.getData('innerhtml');
      }
      return false;
    }
    //拖拽完成后，作用在被拖拽元素上
    function handleDragEnd(e) {
      this.style.opacity ='1';

      [].forEach.call(divs, function(d) {
        d.classList.remove('over');
      }
      );
    }
    var dragDivs = document.querySelectorAll(className);
    [].forEach.call(dragDivs, function(d) {
      d.addEventListener('dragstart', handleDragStart, false);
      d.addEventListener('dragenter', handleDragEnter, false);
      d.addEventListener('dragover', handleDragOver, false);
      d.addEventListener('dragleave', handleDragLeave, false);
      d.addEventListener('drop', handleDrop, false);
      d.addEventListener('dragend', handleDragEnd, false);
    });
}


var imageUpload = function(options) {

    options = $.extend({
        url: '',
        target: document.body,
        fileName: 'upload_files',
        inputName: 'images[]',
        size: 500 * 1024,
        width: 50,
        height: 50,
        multiple: true,
        isModal: false,
        limit: 0,
        handle: '.action-upload',
        file_input: '.action-file-input',
        insertWhere: null,
        callback: null
    }, options || {});

    // var compos = '<div class="choose-image"><span class="image-box"></span><b class="action-upload" title="选择图片"><i class="icon-arrow-right-b"></i></b></div>';

    // var file_input_old = $(options.target).find('input[type=file]' + options.file_input);
    // var file_input = file_input_old.clone(true, true).attr('name', '');

    // compos = $(compos).insertAfter(file_input_old).prepend(file_input);
    // file_input_old.remove();


    var button, limit, isMultiple, name, uploadItem, hasImgNum;
    var selectId = [];

    $(options.target).on('click', options.handle, function(e) {
        var handle = $(this);
        var container = handle.parent();
        var file_input = container.find(options.file_input);
        file_input.click();
    })
    .on('change', options.file_input, function(e) {
        var file_input = $(this);
        var container = file_input.parent();
        var handle = container.find(options.handle);

        var fileName = this.getAttribute('data-filename') || options.fileName;
        var size = this.getAttribute('data-size');
        size = Number(size) || options.size;


        var insertWhere = container.find(this.getAttribute('data-insertwhere') || options.insertWhere || '.image-box');

        var multiple = this.multiple;
        var isModal = this.getAttribute('data-ismodal');
        var url = this.getAttribute('data-remote') || options.url;
        var limit = this.getAttribute('data-max') || options.limit;
        var inputName = this.getAttribute('name') || options.inputName;
        var callback = this.getAttribute('data-callback') || file_input.data('callback') || options.callback;

        var data = new FormData();
        var files = this.files;

        if (limit) {
            var length = container.find('.img-thumbnail:not(.action-upload)').length,
                filelen;
            if(multiple && files) {
                filelen = files.length;
            }
            else {
                filelen = 1;
            }
            if(length + filelen > limit) {
                alert('超出限制，最多上传' + limit + '张。');
                return false;
            }
        }

        if(!files || !Array.prototype.slice.call(files).every(function (file, i) {
            if(file.size > size) {
                $('#messagebox').message('抱歉，上传图片 "' + file.name + '" 须小于' + size / 1024 + 'kB!');
                file_input.val('');
                return false;
            }
            if(multiple) {
                data.append(fileName + '[]', file);
            }
            else {
                data.append(fileName, file);
            }
            return true;
        })) return false;

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (rs) {
                try {
                    rs = JSON.parse(rs);
                }
                catch (e) {}
                if(rs.success && rs.data) {
                    if(callback) {
                        return callback(rs);
                    }
                    var html = '';
                    if(multiple) {
                        if(isModal) {
                            $('#areaselect_modal').modal('hide');

                            var type = $('.nav-tabs .active').attr('data-type');
                            if($('.has-searched')){
                                var name = $('.has-searched').val();
                            }else{
                                var name = '';
                            }
                            if($('.gallery-condition .active').hasClass('time')){
                                var orderBy = $('.gallery-condition .active').attr('data-order') + ' ' + $('.gallery-condition .active').attr('data-sort');
                            }else{
                                var orderBy = $('.gallery-condition .active').attr('data-order');
                            }
                            $.each(rs.data, function (k, data) {
                                if(data.url) {
                                    var item = {}
                                    item.img_id = data.image_id;
                                    item.img_src = data.t_url;
                                    selectId.push(item);
                                    if(isMultiple && isMultiple == 'true') {
                                        html += '<div class="col-sm-3"><div class="thumbnail checked"><div class="img-show"><a href='+ data.url +'"><img src="' + data.t_url +'"></a></div><div class="caption" data-name="'+ data.url +'" data-url="'+ data.url +'"><p class="image-name">'+ data.image_name +'</p></div></div></div>';
                                    }else{
                                        html += '<div class="col-sm-3"><div class="thumbnail"><div class="img-show"><a href='+ data.url +'"><img src="' + data.t_url +'"></a></div><div class="caption" data-name="'+ data.url +'" data-url="'+ data.url +'"><p class="image-name">'+ data.image_name +'</p></div></div></div>';
                                    }
                                }
                            });

                            $(html).insertBefore($('.gallery > div:first-child'));
                        }else{
                            insertWhere = handle;
                            $.each(rs.data, function (k, data) {
                                if(data.url) {
                                    html += '<div class="handle img-thumbnail"><i class="icon-close-b" onclick="$(this).parent().remove();"></i><img src="' + data.url +'"><input type="hidden" name="' + inputName + '" value="' + data.image_id +'"></div>';
                                }
                            });
                            $(html).insertBefore(insertWhere);
                        }
                    }else{
                        var data = rs.data[fileName];
                        if(data.url) {
                            html = '<img src="' + data.url +'"><input type="hidden" name="' + inputName + '" value="' + data.image_id +'">';
                        }
                        $(insertWhere).html(html);
                    }
                }else if(rs.message) {
                    $('#messagebox').message(rs.message);
                    file_input.val('');
                }
            },
            error: function () {
                $('#messagebox').message('上传出错，请重试');
            }
        });
    });

    $('#gallery_modal').on('shown.bs.modal', function(event) {
        selectId = [];
        var that = $(this);
        button = $(event.relatedTarget);
        limit = button.attr('data-limit');
        isMultiple = button.attr('data-isMultiple');
        name = button.attr('data-name');
        uploadItem = button.parents('.multiple-upload').find('.multiple-item');
        hasImgNum = button.parent().find('.multiple-item');
        $('#gallery_modal').attr('data-ids',selectId)
        if (hasImgNum) {
            $(hasImgNum).each(function(index, el) {
                var item = {}
                item.img_id = $(el).find('input[name^="list"]').val();
                item.img_src = $(el).find('img').attr('src');
                selectId.push(item);
            })
            isChecked()
        }
        $(this).on('click','.gallery-modal-tabs li a', function(e) {
            e.preventDefault();
            $('.gallery-modal-tabs li').removeClass('active');
            $(this).parent().addClass('active');
            var urlData = $(this).attr('href');
            $.post(urlData, function(data) {
                $('.gallery-modal-content').empty().append(data);
                isChecked()
            });
        })
    })
    .on('click','.action-save',function(){
        if(isMultiple && isMultiple == 'true') {
            var multipleList = '';
            for (var i = 0; i < selectId.length; i++) {
                multipleList +=  '<div class="multiple-item">'
                                + '<div class="multiple-del glyphicon glyphicon-remove-circle"></div>'
                                + '<a class="select-image">'
                                + '<input type="hidden" name="'+ name +'" value="'+ selectId[i].img_id +'">'
                                + '<div class="img-put">'
                                + '<img src="'+ selectId[i].img_src +'">'
                                + '<i class="glyphicon glyphicon-picture"></i>'
                                + '</div>'
                                + '</a>'
                                + '</div>';
            };
            if(selectId.length == 0){
                $('#messagebox').message('请选择图片!');
                return;
            }
            if(selectId.length > limit){
                $('#messagebox').message('您选择得图片数量超出最大限制!');
                return;
            }
            button.parents('.multiple-upload').find('.multiple-item').remove();
            $('#gallery_modal').modal('hide');
            button.before(multipleList);
            
            //拖拽商品图片
            dragDom('.multiple-item');
        }else{
            var imgList = $('#gallery_modal').find('.checked');
            var imgsrc = imgList.find('img').attr('src');
            var url = imgList.find('.caption').attr('data-name');
            var img = '<img src="' + imgsrc + '">';
            if(imgList.length>0){
                button.find('.img-put').empty().append(img);
                button.find('input').val(url);
                $('#gallery_modal').modal('hide');
            }else{
                $('#messagebox').message('请选择图片!');
            }
        }
    })
    .on('hide.bs.modal', function (e) {
        $(this).removeData('bs.modal');
    })
    .on('click','.thumbnail',function(e){
        e.preventDefault();
        var item = {};
        item.img_id = $(this).find('.caption').attr('data-name')
        item.img_src = $(this).find('.caption').attr('data-url')
        if(isMultiple && isMultiple == 'true'){
            $(this).toggleClass('checked');
            if($(this).hasClass('checked')){
                selectId.push(item);
            }else{
                for (var i = 0; i < selectId.length; i++) {
                    if(selectId[i].img_id == item.img_id){
                        selectId.splice(i,1);
                    }
                };
            }
        }else{
            $(this).parent().siblings().find('.thumbnail').removeClass('checked');
            $(this).addClass('checked');
        }
    }).on('click', '.pagination a', function(e){
        e.preventDefault();
        if(!$(this).parent().hasClass('disabled')){
            $.post($(this).attr('href'),function(rs){
                setTimeout(function(){
                    var list = $('.img-show a');
                    if(hasImgNum){
                        for (var j = 0; j < list.length; j++) {
                            for (var i = 0; i < selectId.length; i++) {
                                if(selectId[i].img_id == $(list[j]).attr('href')){
                                    $(list[j]).parent().parent().addClass('checked');
                                }
                            };
                        };
                    }
                },100)
            });
        }
    });

    function isChecked() {
        var list = $('.img-show a');
        for (var j = 0; j < list.length; j++) {
            for (var i = 0; i < selectId.length; i++) {
                if (selectId[i].img_id == $(list[j]).attr('href')) {
                    $(list[j]).parent().parent().addClass('checked');
                }
            };
        };
    }

    $('.note-image-dialog').on('click','.action-save',function(){
        var imgList = $('.note-image-dialog').find('.checked');
        var imgsrc = imgList.find('img').attr('src');

        if(imgList.length>0){
            $('.note-image-dialog').modal('hide');
        }else{
            $('#messagebox').message('请选择图片!');
        }
    })
    .on('click','.thumbnail',function(){
        var urlArr = [];
        $(this).toggleClass('checked');
        var imgList = $('.note-image-dialog').find('.checked');
        for (var i = 0; i < imgList.length; i++) {
            var url = $(imgList[i]).find('.caption').attr('data-url');
            urlArr.push(url);
        };
        $(this).parents('.modal-body').find('.note-image-url').val(urlArr);
    });

    $('.multiple-upload').on('click','.multiple-del',function(){
        $(this).parents('.multiple-upload').find('.multiple-add').show();
        $(this).parent().remove();
    })

    function getList(type,orderBy,name) {
        $.post('<{url action=topshop_ctl_shop_image@search imageModal=true}>', {'img_type': type, 'orderBy': orderBy, 'image_name': name}, function(data) {
          $('.gallery-modal-content').empty().append(data);
        });
    }
   
}


//商品选择器（临时存放地）
var goodsChoose = function(options) {
    options = $.extend({
        url: '',
        getProUrl: '',
        getBrandUrl: '',
        getGoodsUrl: '',
        target: document.body,
        modalDom: '#goods_modal',
        handle: '.select-goods',
        catBox: '.goods-category',
        catlistItem: '.goods-category li',
        catDefault: '分类',
        brandBox: '.goods-brands',
        brandList: '#brand_list',
        brandListItem: '#brand_list li',
        brandDefault: '品牌',
        filterBtn: '#search_goods',
        skuFilterBtn: '#sku_search_goods',
        clearFilterBtn: '#clear_filter',
        clearBrandBtn: '#clear_brand',
        submitBtn: '#choose_goods',
        skuSubmitBtn: '#sku_choose_goods',
        checkAll: '#check_all',
        uncheckAll: '#uncheck_all',
        goodsList: '#goods_list',
        skuList: '#sku_list',
        goodsListItem: '#goods_list > ul > li',
        skuListItem: '#sku_list > ul > li',
        goodsSearchKey: '.goods-search-key',
        goodsSearchBn: '.goods-search-bn',
        goodsSearchBrand: '.goods-search-brand',
        insertWhere: null,
        textcol: null,
        view: null,
        getPro: function(insertDom, getProUrl, catId, brandId, name, bn, brand, callback){
            $.ajax({
                url: getProUrl,
                type: 'POST',
                dataType: 'html',
                data: {
                    "searchname": name,
                    "searchbn": bn,
                    "searchbrand": brand,
                    "catId": catId,
                    "brandId": brandId
                },
                success: function(rs) {
                    $(insertDom).html(rs);
                    var list = $(insertDom).find('li');
                    for (var i = 0; i < list.length; i++) {
                        var itemId = $(list[i]).attr('data-id');
                        for (var j = 0; j < selectId.length; j++) {
                            if(itemId == selectId[j]){
                                $(list[i]).addClass('checked');
                            }
                        };
                    };
                    if(callback) {
                        return callback();
                    }

                }
            });
        }
        // getBrand: function(getBraUrl, catId, insertDom){
        //     if (catId != '') {
        //         $.ajax({
        //             url: getBraUrl,
        //             type: 'POST',
        //             dataType: 'json',
        //             data: {
        //                 "catId": catId
        //             },
        //             success: function(data) {
        //                 if (data) {
        //                     var result = '';
        //                     for (var i = 0; i < data.length; i++) {
        //                         result += '<li data-val="' + data[i].brand_id + '">' + data[i].brand_name + '</li>';
        //                     };
        //                     $(insertDom).empty().append(result);
        //                 }
        //             }
        //         });
        //     }
        // }

    }, options || {});

    var getProUrl,
        catId,
        brandId,
        modalDomName,
        insertDom,
        data;

    var selectId = [];
    var url = $(options.handle).attr('data-remote') || options.url,
        editid = $(options.handle).attr('data-item_id'),
        getGoodsUrl = $(options.handle).attr('data-fetchgoods') || options.getGoodsUrl,
        insertWhere = $(options.insertWhere || '.selected-goods-list');
        limit = $(options.handle).data('limit') || options.limit;

    $(options.target).on('click', options.handle, function(e) {
        e.preventDefault();
        var container = $(this).parents('.select-goods-panel');

        insertWhere = $(container).find(this.getAttribute('data-insertwhere') || options.insertWhere || '.selected-goods-list');
        if($(this).attr('data-modal'))
            $($(this).attr('data-modal')).modal('show');
        else
            $(options.modalDom).modal('show');
    });

    $(options.handle).each(function() {
        var that = $(this);

        data = $(this).data();
        for (var i in data) {
            if(i === 'remote'){
                delete(data[i]);
            }
            if(i === 'fetchgoods'){
                delete(data[i]);
            }
            if(i === 'target'){
                delete(data[i]);
            }
            if(i === 'limit'){
                delete(data[i]);
            }
        };
        editid = $(this).attr('data-item_id');
        getGoodsUrl = $(this).attr('data-fetchgoods') || options.getGoodsUrl;
        if(editid && editid.length > 0){
            $.post(getGoodsUrl, data, function(rs) {
                if(rs){
                    insertWhere = that.parents('.select-goods-panel').find(options.insertWhere || '.selected-goods-list');
                    $(insertWhere).html(rs);
                };
            });
        }
        
        $($(this).attr('data-modal') || options.modalDom).on('show.bs.modal', function() {
            insertWhere = that.parents('.select-goods-panel').find(options.insertWhere || '.selected-goods-list');
            limit = that.data('limit') || options.limit;
            selectId = [];
            var lastSelected = $(insertWhere).find('tr');
            if(that.attr('data-modal') && that.attr('data-modal') == "#sku_modal") {
                lastSelected = $(insertWhere).find('.sku-item');
            }
            $(lastSelected).each(function(index, el) {
                selectId.push($(el).attr('date-itemid'));
            });
            url = that.attr('data-remote') || options.url;
            $(this).find('.modal-content').load(url);
        }).on('shown.bs.modal', function() {
            editid = that.attr('data-item_id')
            getGoodsUrl = that.attr('data-fetchgoods') || options.getGoodsUrl;
            if(that.attr('data-modal') && that.attr('data-modal') == "#sku_modal") {
                insertDom = options.skuList;
                getProUrl = $(options.skuFilterBtn).attr('data-remote') || options.getProUrl;
            } else {
                insertDom = options.goodsList;
                getProUrl = $(options.filterBtn).attr('data-remote') || options.getProUrl;
            }
            options.getPro(insertDom, getProUrl,'','','','','',function(){
                var list = $(insertDom).find('li');
                for (var i = 0; i < list.length; i++) {
                    var itemId = $(list[i]).attr('data-id');
                    for (var j = 0; j < selectId.length; j++) {
                        if(itemId == selectId[j]){
                            $(list[i]).addClass('checked');
                        }
                    };
                };
            });
        }).on('hide.bs.modal', function() {
            // options.getPro(options.goodsList, getProUrl);
            options.getPro(insertDom, getProUrl);
        }).on('click', options.catlistItem, function(e) {
            e.stopPropagation();
            var isData = $(this).attr('data-val');
            if(isData){
                catId = $(this).attr('data-val');
                //var getBraUrl = $(options.brandBox).attr('data-remote') || options.getBrandUrl;
                var catName = $(this).children('span').text();
                //options.getBrand(getBraUrl, catId, options.brandList);
                $(this).parents('.filters-list').hide().siblings('.filter-name').text(catName);
                //$(options.brandBox).find('.filter-name').text(options.brandDefault);
            }
        }).on('click', options.brandListItem, function(){
            brandId = $(this).attr('data-val');
            var catName = $(this).text();
            $(this).parents('.filters-list').hide().siblings('.filter-name').text(catName);
        }).on('click', options.filterBtn +',' + options.skuFilterBtn, function() {
            var name = $(this).parent().find('input[name="item_title"]').val();
            var bn = $(this).parent().find('input[name="item_bn"]').val();
            var brand = $(this).parent().find('input[name="item_brand"]').val();
            options.getPro(insertDom, getProUrl, catId, brandId, name, bn, brand, function(){
                // var list = $(options.goodsList).find('li');
                var list = $(insertDom).find('li');
                for (var i = 0; i < list.length; i++) {
                    var itemId = $(list[i]).attr('data-id');
                    for (var j = 0; j < selectId.length; j++) {
                        if(itemId == selectId[j]){
                            $(list[i]).addClass('checked');
                        }
                    };
                };
            });
        }).on('click', options.goodsListItem + ',' + options.skuListItem, function(){
            $(this).toggleClass('checked');
            var dataId = $(this).data('id');

            if($(this).hasClass('checked')){
                if(limit) {
                    if(typeof(limit) === 'number' && parseInt(limit) > 0){
                        limit = parseInt(limit);
                    }else{
                        return
                    }
                    if($(this).parent().parent().find('.checked').length > limit){
                        $('#messagebox').message('最多只能选择' + limit + '个商品')
                        $(this).removeClass('checked');
                        return
                    }
                }
                selectId.push(dataId);
            }else{
                for (var i = 0; i < selectId.length; i++) {
                    if(selectId[i] == dataId){
                        selectId.splice(i,1);
                    }
                };
            }
        })
        .on('click',options.checkAll,function(){
            var list = that.attr('data-modal') && that.attr('data-modal') == "#sku_modal" ? $(options.skuListItem) : $(options.goodsListItem);
            var thisIds = []
            list.addClass('checked')
            for (var i = 0; i < list.length; i++) {
                var ids = $(list[i]).data('id');
                thisIds.push(ids);
            };
            selectId = $.unique($.merge(selectId, thisIds));
        })
        .on('click',options.uncheckAll,function(){
            var list = that.attr('data-modal') && that.attr('data-modal') == "#sku_modal" ? $(options.skuListItem) : $(options.goodsListItem);//$(options.goodsListItem);
            var thisIds = [];
            list.removeClass('checked');
            var tempRemoveIds = selectId.concat();
            for (var i = 0; i < list.length; i++) {
                var ids = $(list[i]).data('id');
                thisIds.push(ids);
            };
            for (var i = 0; i < tempRemoveIds.length; i++) {
                for (var j = 0; j < thisIds.length; j++) {
                    if(tempRemoveIds[i] == thisIds[j]){
                        selectId.splice(0,1);
                        break;
                    }
                }
            };
        })
        .on('mouseover mouseout', options.catBox, function(event){
            var el = $(this).children('.filters-list');

            if(event.type == "mouseover"){
              el.show()
            }else if(event.type == "mouseout"){
              el.hide()
            }
        })
        .on('mouseover mouseout', options.brandBox, function(event){
            var el = $(this).children('.filters-list');

            if(event.type == "mouseover"){
              el.show()
            }else if(event.type == "mouseout"){
              el.hide()
            }
        })
        .on('mouseover mouseout', options.catlistItem, function(event){
            var el = $(this).children('.child-list');
            
            if(!!$(el)){
                if(event.type == "mouseover"){
                  el.show()
                }else if(event.type == "mouseout"){
                  el.hide()
                }
            }
        })
        .on('click', options.clearFilterBtn, function(e){
            e.preventDefault();
            catId = null;
            brandId = null;
            $(options.catBox).find('.filter-name').text(options.catDefault);
            $(options.brandBox).find('.filter-name').text(options.brandDefault);
            $(options.brandList).empty();
            $(options.goodsSearchKey).val('');
            $(options.goodsSearchBn).val('');
            $(options.goodsSearchBrand).val('');
            getProUrl = $(options.filterBtn).attr('data-remote') || options.getProUrl;
            if(that.attr('data-modal') && that.attr('data-modal') == "#sku_modal") {
                getProUrl = $(options.skuFilterBtn).attr('data-remote') || options.getProUrl;
            }
            options.getPro(insertDom, getProUrl,'','','','',function(){
                // var list = $(insertDom).find('li');
                // for (var i = 0; i < list.length; i++) {
                //     var itemId = $(list[i]).attr('data-id');
                //     for (var j = 0; j < selectId.length; j++) {
                //         if(itemId == selectId[j]){
                //             $(list[i]).addClass('checked');
                //         }
                //     };
                // };
            });
        })
        .on('click', options.clearBrandBtn, function(e){
            e.preventDefault();
            brandId = null;
            $(options.brandBox).find('.filter-name').text(options.brandDefault);
        })
        .on('click', options.submitBtn + ',' + options.skuSubmitBtn, function(){
            if (selectId.length == 0) {
                $('#messagebox').message('请选择商品');
                return;
            }
            if(that.attr('data-modal') && that.attr('data-modal') == '#sku_modal' && selectId.length > 4) {
                $('#messagebox').message('最多只能添加4件赠品');
                return;
            }
            var cid = {
                'item_id' : selectId
            };
            data = that.data();
            data = $.extend(data, cid);
            $.post(getGoodsUrl, data, function(rs) {
                if (rs) {
                    $(insertWhere).html(rs);
                    $(that.attr('data-modal') || options.modalDom).modal('hide');
                } else {
                    $('#messagebox').message('还未添加商品');
                }
            });
        })
        .on('click', '.pagination a', function(e){
            e.preventDefault();
            if(!$(this).parent().hasClass('disabled')){
                $.post($(this).attr('href'),function(rs){
                    $(insertDom).html(rs);
                    var list = $(insertDom).find('li');
                    for (var i = 0; i < list.length; i++) {
                        var itemId = $(list[i]).attr('data-id');
                        for (var j = 0; j < selectId.length; j++) {
                            if(itemId == selectId[j]){
                                $(list[i]).addClass('checked');
                            }
                        };
                    };
                });
            }
        });
    });
}

jQuery(function(){
    imageUpload();
    goodsChoose(); //商品选择器（临时存放地）
});
