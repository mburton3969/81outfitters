var page = window.location.href;
var pageFirst = "";
var index = 0;
var indexFirst = 0;
var cols = 0;
var grid = true;
function getNextPage() {
	w = parseFloat($(container).css('width'));
	container.append('<div id="ajaxblock" style="width:'+w+'px;"><span></span></div>');
	if(index == -1) {
		$('#ajaxblock').remove();
		return false;
	}
	if(index > 1) {
		index = parseInt(index) + 1;
		page = pageFirst + "&page=" + index;
	} else {
		if(indexFirst == 1) {
			pageFirst = page;
		}
		indexFirst = parseInt(indexFirst) + 1;
		page = pageFirst + "&page=" + indexFirst;
	}
	if ($('.product-list').length>0) {
		grid = false;
	} else {
		grid = true;
	}
	$.ajax({
		url: page,
		type: "GET",
		data: '',
		beforeSend: function() {
			$('#product-preloader').addClass('loading');
		},
		success:function (data) {
			var data_html = $(data).find('.product-list:last-child').parent().clone();
			$('#ajaxblock').remove();
			if ($(data)) {
				if (grid == false) {
					data_html.find('.product-layout').attr('class', 'product-layout product-list col-xs-12');
				} else {
					if (cols == 2) {
						data_html.find('.product-layout').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
					} else if (cols == 1) {
						data_html.find('.product-layout').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
					} else {
						data_html.find('.product-layout').attr('class', 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12');
					}
				}
				if (!data_html.html() || $('#product-preloader').data('last') == index) {
					$('#product-preloader').addClass('hidden');
				}
				container.append(data_html.html());
			} else {
				index = -1;
			}
			$('.lazy img').not('.lazy-loaded').unveil(0, function () {
				$(this).load(function () {
					$(this).parent().addClass("lazy-loaded");
				});
			});
			var o1 = $('.date'),
			o2 = $('.datetime'),
			o3 = $('.time');
			if (o1.length) {
				o1.datetimepicker({
					pickTime: false
				});
			}
			if (o2.length) {
				$('.datetime').datetimepicker({
					pickDate: true,
					pickTime: true
				});
			}
			if (o3.length) {
				$('.time').datetimepicker({
					pickDate: false
				});
			}
			$('#product-preloader').removeClass('loading');
		},
		error: function(){
			$('#product-preloader').removeClass('loading');
		}
	});
}

function getContainer() {
	if ($('.product-list').length>0) {
		container = $('.product-list').parent();
	} else  if ($('.product-grid').length>0) {
		container = $('.product-grid').parent();
	} else {
		container = $('.product-layout').parent();
	}
	return container;
}
