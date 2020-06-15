var pos_products = {},
pos_taxes,
popular_products,
total_product_count,
total_customer = 0,
total_category = 0,
no_image,
product_panel,
current_total,
current_total_formatted,
customers,
orders,
all_suppliers,
order_products,
offline = 0,
customer_id = 0,
total_discount_by_group =0,
total_discount_by_group_by_product_d ={},
multiply_qty = 0,
multiply_qty_manage_by_product_d ={},
customer_name = '',
pos_cart = {},
pos_orders = {},
pos_holds = {},
current_cart = 0,
pos_remove_id = 0,
uf_cart_total = 0,
uf_sub_total = 0,
discountApply = 0,
totalDiscount = 0,
uf_total_discount = 0,
coupon = '',
coupon_disc = 0,
cart_tax = 0,
validate_login = true,
currency_update = false,
start = 0,
start_customer = 0,
total_load_customer = 0,
// POS Update Code
pos_returns = {},
return_reasons = {},
credit_amount = 0,
global_change = 0,
use_credit = false,
only_credit_payment = false,
partial_credit_payment = false,
shipping_method = ''
change_customer = 0,
delivery_charge = 0,
option_files = {},
coupon_info = {},
booking_product_ids = [],
customer_group_id = 0;
// POS Update Code End
$(document).ready(function () {
  $( window ).resize(function() {
    $('#pos-side-panel').css('height', $(window).height());
    $('#product-panel').css('height', $(window).height() - 50 - 30);
    $('.sidepanel').css('height', $(window).height() - 50);
    $('#cart-panel').css('height', $(window).height());
    $('#order-container, #other-container, #return-container').css('height', $(window).height() - 60);
  });
  $('#pos-side-panel').css('height', $(window).height());
  $('#product-panel').css('height', $(window).height() - 50 - 30);
  $('.sidepanel').css('height', $(window).height() - 50);
  $('#cart-panel').css('height', $(window).height());
  $('#order-container, #other-container, #return-container').css('height', $(window).height() - 60);

  if (user_login == 0) {
    $('#loginModalParent').css('display', 'block');
  } else {
    $('#loginModalParent').css('display', 'none');
    $('#loader').css('display', 'block');
    getPopularProducts();
  };
  product_panel = $('#product-panel');
});

function loginUser () {
  var username = $('#input-username').val();
  var password = $('#input-password').val();
  if (username.length < 4 || password.length < 4) {
    $.toaster({
      priority: 'warning',
      message: message_error_credentials,
      timeout: 5000
    });
    validate_login = false;
  };

  if (validate_login == true) {
    validate_login = false;
    getLocalForage('pos_remove_id');
    $.ajax({
      url: 'index.php?route=wkpos/wkpos/userLogin',
      dataType: 'json',
      type: 'post',
      data: {username: username, password: password},
      beforeSend: function () {
        $('#loader').css('display', 'block');
      },
      success: function (json) {
        validate_login = true;
        if (json['success']) {
          $.toaster({
            priority: 'success',
            message: json['success'],
            timeout: 5000
          });
          $('.logger-name').text(json['name']);
          $('.logger-post').text('(' + json['group_name'] + ')');
          $('.logger-img').attr('src', json['image']);
          $('#first-name').val(json['firstname']);
          $('#last-name').val(json['lastname']);
          $('#user-name').val(json['username']);
          $('#account-email').val(json['email']);
          user_login = json['user_id'];
          $('#loginModalParent').css('display', 'none');
          if (pos_remove_id) {
            $('#clockin').css('display', 'block');
          } else {
            getPopularProducts();
          }
        };
        if (json['error']) {
          $.toaster({
            priority: 'danger',
            message: json['error'],
            timeout: 5000
          });
          $('#loader').css('display', 'none');
        };
      },
      error: function () {
        validate_login = true;
      }
    });
  };
}

function printProducts () {
  product_panel.html('');
  $.each(pos_products, function(id, item) {
    if (pos_products[id]) {
      if (!(show_lowstock_prod == 1) && (item['quantity'] < 1)) {
        return;
      }
      html = '<div class="col-sm-2 col-xs-6 product-select" product-id="' + item["product_id"] + '" option="' + item["option"] + '">';

      html += '  <img src="' + item["image"] + '" class="product-image" width="100%" height="100%">';
      html += '  <div class="col-xs-12 product-detail">';
      html += '    <b>' + item["name"] + '</b><br />';
      if (item["special"] == 0) {
        html += entry_price + ' <b>' + item["price"] + '</b>';
      } else {
        html += entry_price + ' <b>' + item["special"] + '</b> <span class="line-through">' + item["price"] + '</span>';
      };
      html += '  </div>';
      if (item['option']) {
        html += '<span class="label label-info option-noti" data-toggle="tooltip" title="' + text_option_notifier + '"><i class="fa fa-question-circle"></i></span>';
      }

      if (item["group_wise_price"].length) {
        var discount_text = ' ';
        $.each(item["group_wise_price"], function(key, discountvalue) {
          discount_text +=discountvalue.quantity +" or more " + discountvalue.price+',';
        });
        discount_text = discount_text.substring(',', discount_text.length - 1);
        html += '<span class="label label-danger special-tag" data-toggle="tooltip" title="' + discount_text + '"><i class="fa fa-star"></i></span>';
      }
      if (!(item["special"] == 0)) {
        html += '<span class="label label-danger special-tag" data-toggle="tooltip" title="' + text_special_price + '"><i class="fa fa-star"></i></span>';
      }
      if (!(item["unit_price_status"] == 0)) {
        html += '<span class="label label-danger special-tag" style="left:0;width: 25px;top:0;" data-toggle="tooltip" data-placement="right" title="' + text_unit_price + '"><i class="fa fa-balance-scale"></i></span>';
      }
      if (parseInt(item['quantity']) <= low_stock) {
        html += '<span class="label label-danger low-stock" data-toggle="tooltip" title="' + text_low_stock + '"><i class="fa fa-exclamation-triangle"></i></span>';
      }
      html += '<span class="label label-info pinfo" data-toggle="tooltip" title="' + button_product_info + '"><i class="fa fa-info-circle"></i></span>';

      html += '</div>';
      product_panel.append(html);
      $('.pinfo, .special-tag, .option-noti').tooltip();
    }
  });
}

function getPopularProducts() {
  $.ajax({
    url: 'index.php?route=wkpos/product/getPopularProducts',
    dataType: 'json',
    type: 'get',
    beforeSend: function () {
      $('#loading-text').text(text_loading_populars);
    },
    success: function (json) {
      $('.progress-bar').css('width', '20%');
      popular_products = json['products'];
      pos_taxes = json['taxes'];
      getAllProducts();
    },
    error: function () {
      $('.progress-bar').addClass('progress-bar-danger').css('width', '20%');
      $('#error-text').append('<br>' + error_load_populars + '<br>');
      if (localforage) {
        getLocalForage(pos_taxes);
      }
      getAllProducts();
    }
  });
}

function successProducts() {
  if (localforage) {
    localforage.setItem('pos_products', JSON.stringify(pos_products));
    getLocalForage('current_cart');
    if (!current_cart) {
      current_cart = 0;
      localforage.setItem('current_cart', current_cart);
    }
    getLocalForage('pos_remove_id');

    pos_cart[current_cart] = {};

    $('#current-cart').text(parseInt(current_cart) + 1);
    getLocalForage('pos_cart');
    getLocalForage('pos_holds');
  };

  $('.cart-hold').text(Object.keys(pos_cart).length - 1);
  if (currency_update == true) {
    updateCartCurrency();
    currency_update = false;
  }
  setTimeout(function(){
    printProducts();
    printCart();
  }, 500);
  getAllCategories();
}

function getAllProducts() {
  $.ajax({
    url: 'index.php?route=wkpos/product&start=' + start,
    dataType: 'json',
    type: 'get',
    data: {user_id: user_login},
    beforeSend: function () {
      $('#loading-text').text(text_loading_products);
    },
    success: function (json) {
      total_product_count = json['total_products'];
      start += json['count'];
      var width = 20 + ((start * 20) / total_product_count);

      $('.progress-bar').css('width', width + '%');
      $.each(json['products'], function (key, value) {
        pos_products[key] = value;
        if (typeof value.is_booking != 'undefined' && value.is_booking > 0) {
          booking_product_ids.push(parseInt(key));
        }
      });

      if (start == total_product_count || start > 200) {
        no_image = json['no_image'];
        successProducts();
        var book_start = 0, book_limit = 5;
        if (typeof getAllBookingProducts == 'function') {
          getAllBookingProducts(book_start, book_limit);
        }
      } else {
        getAllProducts();
      }
    },
    error: function () {
      if (localforage) {
        getLocalForage('pos_products');
        total_product_count = Object.keys(pos_products).length;
        printProducts();
        printCart();
        $('.progress-bar').addClass('progress-bar-danger').css('width', '40%');
        $('#error-text').append('<br>' + error_load_products + '<br>');
      };
      getAllCategories();
    }
  });
}

function getAllCategories() {
  $.ajax({
    url: 'index.php?route=wkpos/category',
    dataType: 'json',
    type: 'post',
    data: {user_id: user_login},
    beforeSend: function () {
      $('#loading-text').text(text_loading_categories);
    },
    success: function (json) {
      $('.progress-bar').css('width', '40%');
      categories = json['categories'];
      var category_html = '';

      for (var i = 0; i < categories.length; i++) {
        category_html += '<div class="margin-10">';
        category_html += '    <label class="categoryProduct cursor" category-id="' + categories[i]['category_id'] + '">' + categories[i]['name'] + '</label>';
          child_length = categories[i]['children'].length;
          if (child_length) {
        category_html += '    <button class="btn btn-default btn-xs eCategory" onclick="return false;"><i class="fa fa-plus"></i></button>';
        category_html += '    <div class="form-group sub-cat">';
              for (var j = 0; j < child_length; j++) {
        category_html += '        <div>';
        category_html += '          <label class="categoryProduct cursor" category-id="' + categories[i]['children'][j]['category_id'] + '">' + categories[i]['children'][j]['name'] + '</label>';
        category_html += '        </div>';
              }
        category_html += '    </div>';
          }
        category_html += '</div>';
      }
      $('#categoryList .modal-body').prepend(category_html);
      getAllCustomers();
    },
    error: function () {
      $('.progress-bar').addClass('progress-bar-danger').css('width', '40%');
      $('#error-text').append(error_load_categories + '<br>');
      $('.fa-spin').removeClass('fa-spin');
      getAllCustomers();
    }
  });
}

function getAllCustomers(nocontinue) {
  $.ajax({
    url: 'index.php?route=wkpos/customer',
    dataType: 'json',
    type: 'get',
    beforeSend: function () {
      $('#loading-text').text(text_loading_customers);
    },
    success: function (json) {
      $('.progress-bar').css('width', '60%');
      customers = json['customers'];
      start_customer = json['customers'].length;
      total_load_customer = json['total_customer'];
      if (!nocontinue) {
        getAllOrders();
      }
    },
    error: function () {
      $('.progress-bar').addClass('progress-bar-danger').css('width', '60%');
      $('#error-text').append(error_load_customers + '<br>');
      if (localforage) {
        pos_orders = getLocalForage('pos_orders');
      };
      getAllOrders();
    }
  });
}

function getAllOrders() {
  $.ajax({
    url: 'index.php?route=wkpos/order',
    dataType: 'json',
    type: 'post',
    data: {user_id: user_login},
    beforeSend: function () {
      $('#loading-text').text(text_loading_orders);
    },
    success: function (json) {
      $('.progress-bar').css('width', '80%');
      orders = json['orders'];
      order_products = json['order_products'];
      getRequestHistory();
      $('#postorder').find('.buttons-sp:first').removeAttr('disabled');
      // POS Update Code
      getAllReturns();
      $('.wkorder:first').trigger('click');
    },
    error: function () {
      $('.progress-bar').addClass('progress-bar-danger').css('width', '80%');
      $('#error-text').append(error_load_orders + '<br>');
      setTimeout(function () {
        $('#loader').css('display', 'none');
        $('.progress, #loading-text, #error-text').addClass('hide');
      }, 5000);
      // POS Update Code
      getAllReturns();
      // POS Update Code
    }
  });
}

// POS Update Code
function getAllReturns() {
  $.ajax({
    url: 'index.php?route=wkpos/return',
    type: 'post',
    dataType: 'json',
    data: {user_id: user_login},
    beforeSend: function () {
      $('#loading-text').text(text_loading_returns);
    },
    success: function(json) {
      $('.progress-bar').css('width', '100%');
      pos_returns = json['returns'];
      return_reasons = json['return_reasons'];
      return_actions = json['return_actions'];
      setTimeout(function () {
        $('#loader').css('display', 'none');
        $('.progress, #loading-text').addClass('hide');
        $('#loader').css('display', 'none');
      }, 700);

    },
    error: function() {
      $('.progress-bar').addClass('progress-bar-danger').css('width', '100%');
      $('#error-text').append(error_load_returns + '<br>');
      setTimeout(function () {
        $('#loader').css('display', 'none');
        $('.progress, #loading-text, #error-text').addClass('hide');
      }, 5000);
    },
  });
  productLoad();
}
 $('.report-load').hide();
function productLoad() {
   $('.report-load').show();
  $.ajax({
    url: 'index.php?route=wkpos/product&start=' + start,
    dataType: 'json',
    type: 'get',
    data: {user_id: user_login},
    beforeSend: function () {
      $('#loading-text').text(text_loading_products);
    },
    success: function (json) {
      total_product_count = json['total_products'];
      start += json['count'];
      $.each(json['products'], function (key, value) {
        pos_products[key] = value;
        if (typeof value.is_booking != 'undefined' && value.is_booking > 0) {
          booking_product_ids.push(parseInt(key));
        }
      });
      // printLoadingProduct(json['products']); // method use for print the cureent loaded product
         if(start == total_product_count || start > total_product_count) {
             $('.report-load').hide();
             $('#mode').show();
             LoadCustomer();
            // printProducts();

         } else {
           var text = start +'/'+total_product_count+ '  Product loading...'
           $('.report-load').html(text);
          $('#mode').hide();
           productLoad();
         }
    },
    error: function () {
    console.log('error in background load product');
    }
  });
}
function LoadCustomer() {
  if(start_customer==total_load_customer || start_customer > total_load_customer || typeof(total_load_customer)=='undefined') {
       $('.report-load').hide();
        $('#mode').show();
  } else {

    var text = start_customer +'/'+total_load_customer+ '  customer loading...'
    $('.report-load').html(text);
    $('.report-load').show();
    $.ajax({
      url: 'index.php?route=wkpos/customer&start='+start_customer,
      dataType: 'json',
      type: 'get',
      beforeSend: function () {
        $('#loading-text').text(text_loading_customers);
      },
      success: function (json) {


        start_customer +=250 ;
        total_load_customer = json['total_customer'];
        $.each(json['customers'], function (key, value) {
          customers[customers.length] = value;
        });
        if(start_customer==total_load_customer || start_customer > total_load_customer) {
            $('.report-load').hide();
          $('#mode').show();
        } else {
          $('#mode').hide();
          var text = start_customer +'/'+total_load_customer+ '  customer loading...'
          $('.report-load').html(text);
          $('.report-load').show();
          LoadCustomer();
        }

      },
      error: function () {
        console.log('loading customer backgrond error. ');
      }
    });
  }
}
function printLoadingProduct(productLoadingData) {
  $.each(productLoadingData, function(id, item) {
    if (pos_products[id]) {
      if (!(show_lowstock_prod == 1) && (item['quantity'] < 1)) {
        return;
      }
      html = '<div class="col-sm-2 col-xs-6 product-select" product-id="' + item["product_id"] + '" option="' + item["option"] + '">';

      html += '  <img src="' + item["image"] + '" class="product-image" width="100%" height="100%">';
      html += '  <div class="col-xs-12 product-detail">';
      html += '    <b>' + item["name"] + '</b><br />';
      if (item["special"] == 0) {
        html += entry_price + ' <b>' + item["price"] + '</b>';
      } else {
        html += entry_price + ' <b>' + item["special"] + '</b> <span class="line-through">' + item["price"] + '</span>';
      };
      html += '  </div>';
      if (item['option']) {
        html += '<span class="label label-info option-noti" data-toggle="tooltip" title="' + text_option_notifier + '"><i class="fa fa-question-circle"></i></span>';
      }
      if (!(item["special"] == 0)) {
        html += '<span class="label label-danger special-tag" data-toggle="tooltip" title="' + text_special_price + '"><i class="fa fa-star"></i></span>';
      }
      if (parseInt(item['quantity']) <= low_stock) {
        html += '<span class="label label-danger low-stock" data-toggle="tooltip" title="' + text_low_stock + '"><i class="fa fa-exclamation-triangle"></i></span>';
      }
      html += '<span class="label label-info pinfo" data-toggle="tooltip" title="' + button_product_info + '"><i class="fa fa-info-circle"></i></span>';

      html += '</div>';
    $('.product-select').last().after(html);
      $('.pinfo, .special-tag, .option-noti').tooltip();
    }
  });

}
// POS Update Code End

function addToCart (thisthis, options) {
  var by_barcode = false;
  var text_product_weight_text = '';
  if (thisthis) {
    var product_id = $(thisthis).attr('product-id');
    var option = $(thisthis).attr('option');
  } else {
    var product_id = options.product_id;
    var option = options.option;
    var thisthis = options.thisthis;
    by_barcode = true;
  }

  if (option == 'false') {

    if (pos_products[product_id]['unit_price_status'] == 1) {
      var option_html = '';

      if (pos_products[product_id]['unit_price_status'] == 1) {
        option_html += '<div class="form-group">';
        option_html += '  <label class="control-label" for="input-weight"> Product Weight : ' + parseFloat(pos_products[product_id]["weight"]).toFixed(3) + ' ' + pos_products[product_id]['weight_unit'] + '</label>';
        option_html += '<input type="text" name="product_weight" value="'+parseFloat(pos_products[product_id]["weight"]).toFixed(3)+'" placeholder="' + text_enter_weight + '" id="input-weight" class="form-control" onkeypress="javascript: if(event.which == 13) $(this).trigger(\'blur\'); if ((event.which < 46 || event.which > 57) && event.which != 13 && event.which != 8 && event.which != 37 && event.which != 39) { event.preventDefault(); }"/>';
        option_html += '</div>';
      }

      if (by_barcode) {
        option_html += '<button type="button" onclick="cart_product.add('  + product_id + ', 1, true, this, true);" class="form-control btn-primary"><span class="hidden-xs hidden-sm hidden-md">' + button_cart + '</span> <i class="fa fa-shopping-cart"></i></button>';
      } else {
        option_html += '<button type="button" onclick="cart_product.add('  + product_id + ', 1, true, this);" class="form-control btn-primary"><span class="hidden-xs hidden-sm hidden-md">' + button_cart + '</span> <i class="fa fa-shopping-cart"></i></button>';
      }

       text_product_weight_text += text_product_weight + ' ( ' + pos_products[product_id]['name'] + ' )';

      $('#global-modal-title').text(text_product_weight_text);
      $('#posProductOptions').html(option_html);
      $('#buttonModal').trigger('click');

    } else {
      cart_product.add(product_id, 1, false, thisthis);
    }
  } else {
    var option_length = Object.keys(pos_products[product_id]['options']).length;
    var product_options = pos_products[product_id]['options'];
    var option_html = '';

    for (var i = 0; i < option_length; i++) {
      if (product_options[i]['required'] == 1) {
        var is_required = ' required';
      } else {
        var is_required = '';
      };
      var option_value_length = Object.keys(product_options[i]['product_option_value']).length;

      if (product_options[i]['type'] == 'select') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label" for="input-option' + product_options[i]["product_option_id"] + '">' + product_options[i]["name"] + '</label>';
        option_html += '  <select name="option[' + product_options[i]["product_option_id"] + ']" id="input-option' + product_options[i]["product_option_id"] + '" class="form-control">';
        option_html += '    <option value="">' + text_select + '</option>';

        for (var j = 0; j < option_value_length; j++) {
          option_html += '<option value="' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '">' + product_options[i]["product_option_value"][j]["name"];
          if (product_options[i]["product_option_value"][j]['price']) {
            option_html += ' (' + product_options[i]["product_option_value"][j]['price_prefix'] + product_options[i]["product_option_value"][j]['price'] + ')';
          }
          option_html += '</option>';
        };

        option_html += '  </select>';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'radio') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label">' + product_options[i]["name"] + '</label>';
        option_html += '  <div id="input-option' + product_options[i]["product_option_id"] + '">';

        for (var j = 0; j < option_value_length; j++) {
          option_html += '<div class="radio radio-primary radio-inline">';
          option_html += '    <input id="input-option-value-' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '" type="radio" name="option[' + product_options[i]["product_option_id"] + ']" value="' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '" />';
          option_html += '  <label for="input-option-value-' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '">';
          option_html += '    ' + product_options[i]["product_option_value"][j]["name"] + '';
          if (product_options[i]["product_option_value"][j]['price']) {
            option_html += ' (' + product_options[i]["product_option_value"][j]['price_prefix'] + product_options[i]["product_option_value"][j]['price'] + ')';
          }
          option_html += '  </label>';
          option_html += '</div>';
        }
        option_html += '  </div>';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'checkbox') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label">' + product_options[i]["name"] + '</label>';
        option_html += '  <div id="input-option' + product_options[i]["product_option_id"] + '">';

        for (var j = 0; j < option_value_length; j++) {
          option_html += '<div class="checkbox checkbox-primary">';
          option_html += '    <input type="checkbox" id="input-option-value-' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '" name="option[' + product_options[i]["product_option_id"] + '][]" value="' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '" />';
          option_html += '  <label for="input-option-value-' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '">';
          if (product_options[i]["product_option_value"][j]["image"]) {
            if (product_options[i]["product_option_value"][j]["price"]) {
              var image_alt = product_options[i]["product_option_value"][j]["name"] + ' (' + product_options[i]["product_option_value"][j]["price_prefix"] + product_options[i]["product_option_value"][j]["price"] + ')';
            } else {
              var image_alt = product_options[i]["product_option_value"][j]["name"];
            };
            option_html += '<img src="' + product_options[i]["product_option_value"][j]["image"] + '" alt="' + image_alt + '" class="img-thumbnail" />';
          }
          option_html += product_options[i]["product_option_value"][j]["name"];
          if (product_options[i]["product_option_value"][j]['price']) {
            option_html += ' (' + product_options[i]["product_option_value"][j]['price_prefix'] + product_options[i]["product_option_value"][j]['price'] + ')';
          }
          option_html += '  </label>';
          option_html += '</div>';
          }
        option_html += '  </div>';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'image') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label">' + product_options[i]["name"] + '</label>';
        option_html += '  <div id="input-option' + product_options[i]["product_option_id"] + '">';

        for (var j = 0; j < option_value_length; j++) {
          if (product_options[i]["product_option_value"][j]["price"]) {
            var image_alt = product_options[i]["product_option_value"][j]["name"] + ' (' + product_options[i]["product_option_value"][j]["price_prefix"] + product_options[i]["product_option_value"][j]["price"] + ')';
          } else {
            var image_alt = product_options[i]["product_option_value"][j]["name"];
          };

          option_html += '<div class="radio">';
          option_html += '  <label>';
          option_html += '    <input type="radio" name="option[' + product_options[i]["product_option_id"] + ']" value="' + product_options[i]["product_option_value"][j]["product_option_value_id"] + '" />';
          if (product_options[i]["product_option_value"][j]["image"] == null) {
            option_html += '    <img src="' + no_image + '" alt="' + image_alt + '" class="img-thumbnail" /> ' + product_options[i]["product_option_value"][j]["name"];
          } else {
            option_html += '    <img src="' + product_options[i]["product_option_value"][j]["image"] + '" alt="' + image_alt + '" class="img-thumbnail" /> ' + product_options[i]["product_option_value"][j]["name"];
          };
          if (product_options[i]["product_option_value"][j]['price']) {
            option_html += ' (' + product_options[i]["product_option_value"][j]['price_prefix'] + product_options[i]["product_option_value"][j]['price'] + ')';
          }
          option_html += '  </label>';
          option_html += '</div>';
          }
        option_html += '  </div>';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'text') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label" for="input-option' + product_options[i]["product_option_id"] + '">' + product_options[i]["name"] + '</label>';
        option_html += '<input type="text" name="option[' + product_options[i]["product_option_id"] + ']" value="' + product_options[i]["value"] + '" placeholder="' + product_options[i]["name"] + '" id="input-option' + product_options[i]["product_option_id"] + '" class="form-control" />';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'textarea') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label" for="input-option' + product_options[i]["product_option_id"] + '">' + product_options[i]["name"] + '</label>';
        option_html += '<textarea name="option[' + product_options[i]["product_option_id"] + ']" rows="5" placeholder="' + product_options[i]["name"] + '" id="input-option' + product_options[i]["product_option_id"] + '" class="form-control">' + product_options[i]["value"] + '</textarea>';
      option_html += '</div>';
      }
      if (product_options[i]['type'] == 'file') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label">' + product_options[i]["name"] + '</label>';
        option_html += '<button type="button" id="button-upload' + product_options[i]["product_option_id"] + '" data-loading-text="' + text_loading + '" class="btn btn-default btn-block"><i class="fa fa-upload"></i> ' + button_upload + '</button>';
        option_html += '<input type="hidden" name="option[' + product_options[i]["product_option_id"] + ']" value="" id="input-option' + product_options[i]["product_option_id"] + '" />';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'date') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label" for="input-option' + product_options[i]["product_option_id"] + '">' + product_options[i]["name"] + '</label>';
        option_html += '<div class="input-group date">';
        option_html += '  <input type="text" name="option[' + product_options[i]["product_option_id"] + ']" value="' + product_options[i]["value"] + '" data-date-format="YYYY-MM-DD" id="input-option' + product_options[i]["product_option_id"] + '" class="form-control" />';
        option_html += '  <span class="input-group-btn">';
        option_html += '  <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>';
        option_html += '  </span></div>';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'datetime') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label" for="input-option' + product_options[i]["product_option_id"] + '">' + product_options[i]["name"] + '</label>';
        option_html += '<div class="input-group datetime">';
        option_html += '  <input type="text" name="option[' + product_options[i]["product_option_id"] + ']" value="' + product_options[i]["value"] + '" data-date-format="YYYY-MM-DD HH:mm" id="input-option' + product_options[i]["product_option_id"] + '" class="form-control" />';
        option_html += '  <span class="input-group-btn">';
        option_html += '  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>';
        option_html += '  </span></div>';
        option_html += '</div>';
      }
      if (product_options[i]['type'] == 'time') {
        option_html += '<div class="form-group' + is_required + '">';
        option_html += '  <label class="control-label" for="input-option' + product_options[i]["product_option_id"] + '">' + product_options[i]["name"] + '</label>';
        option_html += '<div class="input-group time">';
        option_html += '  <input type="text" name="option[' + product_options[i]["product_option_id"] + ']" value="' + product_options[i]["value"] + '" data-date-format="HH:mm" id="input-option' + product_options[i]["product_option_id"] + '" class="form-control" />';
        option_html += '  <span class="input-group-btn">';
        option_html += '  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>';
        option_html += '  </span></div>';
        option_html += '</div>';
      }
    };

    if (pos_products[product_id]['unit_price_status'] == 1) {
      option_html += '<div class="form-group">';
      option_html += '  <label class="control-label" for="input-weight"> Product Weight : ' + parseFloat(pos_products[product_id]["weight"]).toFixed(3) + ' ' + pos_products[product_id]['weight_unit'] + '</label>';
      option_html += '<input type="text" name="product_weight" value="'+parseFloat(pos_products[product_id]["weight"]).toFixed(3)+'" placeholder="' + text_enter_weight + '" id="input-weight" class="form-control" onkeypress="javascript: if(event.which == 13) $(this).trigger(\'blur\'); if ((event.which < 46 || event.which > 57) && event.which != 13 && event.which != 8 && event.which != 37 && event.which != 39) { event.preventDefault(); }"/>';
      option_html += '</div>';
    }

    option_html += '<input type="hidden" name="product_id" value="' + product_id + '">';
    if (by_barcode) {
      option_html += '<button type="button" onclick="cart_product.add('  + product_id + ', 1, true, this, true);" class="form-control btn-primary"><span class="hidden-xs hidden-sm hidden-md">' + button_cart + '</span> <i class="fa fa-shopping-cart"></i></button>';
    } else {
      option_html += '<button type="button" onclick="cart_product.add('  + product_id + ', 1, true, this);" class="form-control btn-primary"><span class="hidden-xs hidden-sm hidden-md">' + button_cart + '</span> <i class="fa fa-shopping-cart"></i></button>';
    }
    $('#global-modal-title').text(text_product_options);
    $('#posProductOptions').html(option_html);
    $('#buttonModal').trigger('click');
    datetimepickerFunction();
  };
};

$(document).on('keyup', '#search', function () {
  var keyword = $('#search').val();
  searchProduct(keyword)
})


function searchProduct (keyword) {
  var to_search = keyword.replace('\\', "");
  var to_search = to_search.replace('[', "");
    var to_search = to_search.replace('*', "");
  var search_check = true;
  $('#search').val(to_search);
  if (keyword !== to_search) {
    $.toaster({
      priority: 'warning',
      message: error_keyword,
      timeout: 3000
    });
    search_check = false;
  };

  if (search_check) {
    var search_keyword = to_search.toLowerCase();
    if (!(keyword == '')) {
      $('#product-panel').prev().text(text_search + ' - ' + keyword + ' ( ' + text_all_products + ' )');
    } else {
      $('#product-panel').prev().text(text_all_products);
    }

    $('#loader').css('display', 'block');
    product_panel.html('');

    var product_array = Object.keys(pos_products);
    var product_count = total_product_count;
    $.each(product_array, function( index, value ) {
      if (pos_products[value]) {
        if (!(show_lowstock_prod == 1) && (pos_products[value]['quantity'] < 1)) {

        }else{
          if (pos_products[value]["name"].toLowerCase().search(search_keyword) != '-1' || pos_products[value]["model"].toLowerCase().search(search_keyword) != '-1' || pos_products[value]["sku"].toLowerCase().search(search_keyword) != '-1' || pos_products[value]["barcode"].toLowerCase().search(search_keyword) != '-1') {
            html = '<div class="col-sm-2 col-xs-6 product-select" product-id="' + pos_products[value]["product_id"] + '" option="' + pos_products[value]["option"] + '">';

            html += '  <img src="' + pos_products[value]["image"] + '" class="product-image" width="100%" height="100%">';
            html += '  <div class="col-xs-12 product-detail">';
            html += '    <b>' + pos_products[value]["name"] + '</b><br />';
            if (pos_products[value]["special"] == 0) {
              html += entry_price + ' <b>' + pos_products[value]["price"] + '</b>';
            } else {
              html += entry_price + ' <b>' + pos_products[value]["special"] + '</b> <span class="line-through">' + pos_products[value]["price"] + '</span>';
            };
            html += '  </div>';
            if (pos_products[value]['option']) {
              html += '<span class="label label-info option-noti" data-toggle="tooltip" title="' + text_option_notifier + '"><i class="fa fa-question-circle"></i></span>';
            }
            if (pos_products[value]["group_wise_price"].length) {
              var discount_text = ' ';
              $.each(pos_products[value]["group_wise_price"], function(key, discountvalue) {
                discount_text +=discountvalue.quantity +" or more " + discountvalue.price+',';
              });
              discount_text = discount_text.substring(',', discount_text.length - 1);
              html += '<span class="label label-danger special-tag" data-toggle="tooltip" title="' + discount_text + '"><i class="fa fa-star"></i></span>';
            }
            if (!(pos_products[value]["unit_price_status"] == 0)) {
              html += '<span class="label label-danger special-tag" style="left:0;width: 25px;top:0;" data-toggle="tooltip" data-placement="right" title="'+text_unit_price+'"><i class="fa fa-balance-scale"></i></span>';
            }
            if (!(pos_products[value]["special"] == 0)) {
              html += '<span class="label label-danger special-tag" data-toggle="tooltip" title="' + text_special_price + '"><i class="fa fa-star"></i></span>';
            }
            if (parseInt(pos_products[value]['quantity']) <= low_stock) {
              html += '<span class="label label-danger low-stock" data-toggle="tooltip" title="' + text_low_stock + '"><i class="fa fa-exclamation-triangle"></i></span>';
            }
            html += '<span class="label label-info pinfo" data-toggle="tooltip" title="' + button_product_info + '"><i class="fa fa-info-circle"></i></span>';

            html += '</div>';
            product_panel.append(html);
          }
        }
      } else {
        product_count++;
      };
    });

    if (product_panel.text() == '') {
      product_panel.html('<div class="no-product"><strong>' + error_products + '</strong></div>');
    };
    $('#loader').css('display', 'none');
  };
}

function onlineStatus ($toast) {
  var status_button = $('#mode');

  if(status_button.hasClass('label-danger')) {
    if (navigator.onLine) {
      if ($toast) {
        $.toaster({
          priority: 'success',
          message: text_online_mode,
          timeout: 2000
        });
      };
      status_button.removeClass('label-danger').addClass('label-success').html('<i class="fa fa-toggle-on"></i> <span class="hidden-xs">' + text_online + '</span>');
      offline = 0;
    } else {
      if ($toast) {
        $.toaster({
          priority: 'danger',
          message: error_enter_online,
          timeout: 3000
        });
      };
    }
  } else {
    if ($toast) {
      $.toaster({
        priority: 'warning',
        message: text_offline_mode,
        timeout: 2000
      });
    };
    status_button.removeClass('label-success').addClass('label-danger').html('<i class="fa fa-toggle-off"></i> <span class="hidden-xs">' + text_offline + '</span>');
    offline = 1;
  }
}
$(document).on('click', '#mode', function () {
  credit_amount = 0;
  $('#balance-credit input:first').val('');
  $('#button-credit').click();
  onlineStatus(1);
});

window.addEventListener('online',  onlineStatus);
window.addEventListener('offline', onlineStatus);

function datetimepickerFunction() {
  $('.date').datetimepicker({
    pickTime: false
  });

  $('.datetime').datetimepicker({
    pickDate: true,
    pickTime: true
  });

  $('.time').datetimepicker({
    pickDate: false
  });
}

$(document).on('click', '#more-carts', function () {
  $('#upper-cart').slideToggle();
});

$(document).on('click', '.categoryProduct', function () {
  var category_id = $(this).attr('category-id');
  if (category_id == undefined) {
    return;
  };

  $('.categoryProduct').removeClass('onfocus');
  $(this).addClass('onfocus');
  $('#loader').css('display', 'block');
  product_panel.html('');
  product_panel.prev().text($(this).text());
  var product_count = total_product_count;

  if (category_id == 0) {
    for (var i = 0; i < product_count; i++) {
      if (pos_products[i]) {
        if (!(show_lowstock_prod == 1) && (pos_products[i]['quantity'] < 1)) {
          continue;
        }
        if ($.inArray( pos_products[i]["product_id"], popular_products ) != '-1') {
          html = '<div class="col-sm-2 col-xs-6 product-select" product-id="' + pos_products[i]["product_id"] + '" option="' + pos_products[i]["option"] + '">';

          html += '  <img src="' + pos_products[i]["image"] + '" class="product-image" width="100%" height="100%">';
          html += '  <div class="col-xs-12 product-detail">';
          html += '    <b>' + pos_products[i]["name"] + '</b><br />';
          if (pos_products[i]["special"] == 0) {
            html += entry_price + ' <b>' + pos_products[i]["price"] + '</b>';
          } else {
            html += entry_price + ' <b>' + pos_products[i]["special"] + '</b> <span class="line-through">' + pos_products[i]["price"] + '</span>';
          };

          html += '  </div>';
          if (pos_products[i]['option']) {
            html += '<span class="label label-info option-noti" data-toggle="tooltip" title="' + text_option_notifier + '"><i class="fa fa-question-circle"></i></span>';
          }
          if (!(pos_products[i]["special"] == 0)) {
            html += '<span class="label label-danger special-tag" data-toggle="tooltip" title="' + text_special_price + '"><i class="fa fa-star"></i></span>';
          }
          if (parseInt(pos_products[i]['quantity']) <= low_stock) {
            html += '<span class="label label-danger low-stock" data-toggle="tooltip" title="' + text_low_stock + '"><i class="fa fa-exclamation-triangle"></i></span>';
          }
          html += '<span class="label label-info pinfo" data-toggle="tooltip" title="' + button_product_info + '"><i class="fa fa-info-circle"></i></span>';
          if (!(pos_products[i]["unit_price_status"] == 0)) {
            html += '<span class="label label-danger special-tag" style="left:0;width: 25px;top:0;" data-toggle="tooltip" data-placement="right" title="' + text_unit_price + '"><i class="fa fa-balance-scale"></i></span>';
          }
          html += '</div>';
          product_panel.append(html);
        }
      } else {
        product_count++;
      };

    };
  } else {
    for (var i = 0; i < product_count; i++) {
      if (pos_products[i]) {
        if (!(show_lowstock_prod == 1) && (pos_products[i]['quantity'] < 1)) {
          continue;
        }
        if ($.inArray( category_id, pos_products[i]["categories"] ) != '-1') {
          html = '<div class="col-sm-2 col-xs-6 product-select" product-id="' + pos_products[i]["product_id"] + '" option="' + pos_products[i]["option"] + '">';

          html += '  <img src="' + pos_products[i]["image"] + '" class="product-image" width="100%" height="100%">';
          html += '  <div class="col-xs-12 product-detail">';
          html += '    <b>' + pos_products[i]["name"] + '</b><br />';
          if (pos_products[i]["special"] == 0) {
            html += entry_price + ' <b>' + pos_products[i]["price"] + '</b>';
          } else {
            html += entry_price + ' <b>' + pos_products[i]["special"] + '</b> <span class="line-through">' + pos_products[i]["price"] + '</span>';
          };
          html += '  </div>';
          if (pos_products[i]['option']) {
            html += '<span class="label label-info option-noti" data-toggle="tooltip" title="' + text_option_notifier + '"><i class="fa fa-question-circle"></i></span>';
          }
          if (!(pos_products[i]["special"] == 0)) {
            html += '<span class="label label-danger special-tag" data-toggle="tooltip" title="' + text_special_price + '"><i class="fa fa-star"></i></span>';
          }
          if (!(pos_products[i]["unit_price_status"] == 0)) {
            html += '<span class="label label-danger special-tag" style="left:0;width: 25px;top:0;" data-toggle="tooltip" data-placement="right" title="' + text_unit_price + '"><i class="fa fa-balance-scale"></i></span>';
          }
          if (parseInt(pos_products[i]['quantity']) <= low_stock) {
            html += '<span class="label label-danger low-stock" data-toggle="tooltip" title="' + text_low_stock + '"><i class="fa fa-exclamation-triangle"></i></span>';
          }
          html += '<span class="label label-info pinfo" data-toggle="tooltip" title="' + button_product_info + '"><i class="fa fa-info-circle"></i></span>';

          html += '</div>';
          product_panel.append(html);
        }
      } else {
        product_count++;
      };
    };
  }
  if (product_panel.text() == '') {
    product_panel.html('<div class="no-product"><strong>' + error_no_category_product + '</strong></div>');
  };
  $('.in').trigger('click');
  $('#loader').css('display', 'none');
});

$(document).on('click', '.eCategory', function () {
  if ($(this).children().hasClass('fa-plus')) {
    $(this).children().removeClass('fa-plus');
    $(this).children().addClass('fa-minus');
    $(this).next().slideDown();
  } else {
    $(this).children().removeClass('fa-minus');
    $(this).children().addClass('fa-plus');
    $(this).next().slideUp();
  };
});

$(document).on('keyup', '#searchCustomer', function () {
  var keyword = $('#searchCustomer').val();
  // if (keyword == '') {
  //   return;
  // };
  keyword = keyword.toLowerCase();

  var keys = Object.keys(customers);
  var customer_html = '';
  customer_html += '<hr class="margin-hr">';
  for (var i = 0; i < customers.length; i++) {
    if ((customers[i]["name"].toLowerCase().search(keyword) != '-1') || (customers[i]["email"].toLowerCase().search(keyword) != '-1') || (customers[i]["telephone"].toLowerCase().search(keyword) != '-1')) {
      customer_html += '<div customer-index="' + i + '" class="cursor selectCustomer">' + customers[i]["name"] + ' (' + customers[i]["telephone"] + ' <i class="fa fa-phone"></i>) (' + customers[i]["email"] + ' <i class="fa fa-envelope"></i>)</div>';
    }
  };
  if (customer_html == '') {
    customer_html += '<hr class="margin-hr">';
    customer_html += '<span>' + error_no_customer + '</span>';
  };
  $('#putCustomer').html(customer_html);
});

function updateCartOnCustomerSelect() {
  if (Object.keys(pos_cart).length) {
    var current_cart_length = Object.keys(pos_cart[current_cart]).length;
  } else {
    var current_cart_length = 0;
  }

  for (var i = 0; i < current_cart_length; i++) {
if(pos_products[pos_cart[current_cart][i]['product_id']]['special']) {
    if (symbol_position == 'L') {
      pos_cart[current_cart][i]['special'] = currency_code + parseFloat(pos_products[pos_cart[current_cart][i]['product_id']]['price_uf']).toFixed(2);
      pos_cart[current_cart][i]['total'] = currency_code + parseFloat(pos_cart[current_cart][i]['quantity'] * pos_products[pos_cart[current_cart][i]['product_id']]['price_uf']).toFixed(2);
    } else {
      pos_cart[current_cart][i]['special'] = parseFloat(pos_products[pos_cart[current_cart][i]['product_id']]['price_uf']).toFixed(2) + currency_code;
      pos_cart[current_cart][i]['total'] = currency_code + parseFloat(pos_cart[current_cart][i]['quantity'] * pos_products[pos_cart[current_cart][i]['product_id']]['price_uf']).toFixed(2) + currency_code;
    }
} else {
  pos_cart[current_cart][i]['special'] =0 ;
}
    pos_cart[current_cart][i]['uf'] = (pos_products[pos_cart[current_cart][i]['product_id']]['price_uf']*pos_cart[current_cart][i].weight);
    pos_cart[current_cart][i]['uf_total'] = pos_cart[current_cart][i]['quantity'] * pos_cart[current_cart][i]['uf'];

  }
}

$(document).on('click', '.selectCustomer', function () {
  $('#removeCoupon').trigger('click');
  customer_index = $(this).attr('customer-index');
  customer_id = customers[customer_index]['customer_id'];
  customer_name = customers[customer_index]['name'];
  customer_group_id = customers[customer_index]['customer_group_id'];
  //printProducts();  // comment for avoid extra loop
  //updateCartOnCustomerSelect();
  printCart();

  if (pricelist_status) {
      change_customer = customer_id;
      start = 0;
      changePrice(customer_id);
  }
  if (credit_status) {
    if (offline) {
      $('#balance-credit').html(error_credit_offline).css('padding', '6px 15px');
    } else {
      if (customers[customer_index]['credit_uf'] > 0) {
        $('#balance-credit').css('padding', '0').html('<span class="input-group-addon" id="avail-credit">' + customers[customer_index]['credit_f'] + '</span><input class="form-control" type="text" onkeypress="return validate(event, this)" /><span class="input-group-addon btn btn-success" id="button-credit">' + button_credit + '</span>');
      } else {
        $('#balance-credit').html(text_no_credit).css('padding', '6px 15px');
      }
    }
  }
  $('#customer-name').text(customer_name);
  $('.in').trigger('click');
  $.toaster({
    priority: 'success',
    message: text_select_customer,
    timeout: 3000
  });
});

$(document).on('click', '#addCustomer', function () {

  if (offline) {
    $.toaster({
      priority: 'danger',
      message: error_customer_add,
      timeout: 3000
    });
  } else {
    $(this).addClass('hide');
    $('.searchCustomer').addClass('hide');
    $('#customerSearch .modal-dialog').removeClass('modal-sm').addClass('modal-md');
    $('.addCustomer').removeClass('hide');
    printCart();
  };

});

$(document).on('click', '#button-customer', function () {
  $('#addCustomer').removeClass('hide');
  $('.searchCustomer').removeClass('hide');
  $('#customerSearch .modal-dialog').removeClass('modal-md').addClass('modal-sm');
  $('.addCustomer').addClass('hide');
});

$(document).on('click', '#removeCustomer', function () {
  $('.in').trigger('click');
  customer_id = 0;
    printCart();
  $('#customer-name').text(text_customer_select);
  $.toaster({
    priority: 'success',
    message: text_remove_customer,
    timeout: 3000
  });
});

$(document).on('click', '.pinfo', function() {
  var product_id = $(this).parent().attr('product-id');
    $('#detail-image').attr('src', pos_products[product_id]['image']);
    $('#productName').text(pos_products[product_id]['name']);
    $('#productPrice').text(pos_products[product_id]['price']);
    $('#productItem').text(pos_products[product_id]['quantity']);
    $('#supplier-info').css('display', 'none');
    $('#buttonProductDetails').trigger('click');
    to_cart = 'true';
  });

  $(document).on('click', '#others .product-select', function() {
    $('.sidepanel').removeClass('sidepanel-show');
    var product_id = $(this).attr('product-id');
    if (typeof isBooking === 'function' && isBooking(product_id) >= 0) {
      proceedToBookProduct(product_id);
    } else {
      $('#detail-image').attr('src', pos_products[product_id]['image']);
      $('#productName').text(pos_products[product_id]['name']);
      $('#productPrice').text(pos_products[product_id]['price']);
      $('#productItem').text(pos_products[product_id]['quantity']);
      $('#supplier-info').css('display', '');
      var html = '';
      var suppliers = pos_products[product_id]['suppliers'];
      var suppliers_length = Object.keys(suppliers).length;
      if (suppliers_length) {
        var k = 0;
        for (var i = 0; i < suppliers_length; i++) {
          if (suppliers[i]) {
            html += (++k) + ': ' + suppliers[i]['name'] + '<br/>';
          } else {
            suppliers_length++;
          }
        }
      }
      if (html == '') {
        html = '<span class="text-danger">' + error_no_supplier + '</span>';
      }
      $('#productSuppliers').html(html);

      $('#buttonProductDetails').trigger('click');
    }
});

$(document).on('mouseup', '#product-panel .product-select', function(e) {
  if ($(e.target).hasClass('fa-info-circle') || $(e.target).hasClass('pinfo')) {
    return;
  }
  var product_id = $(this).attr('product-id');
  if (typeof isBooking === 'function' && isBooking(product_id) != -1) {
    proceedToBookProduct(product_id);
  } else {
    addToCart(this);
    $('.close-it').click();
  }
});

function showAllProducts () {
  product_panel.html('');
  product_panel.prev().text(text_all_products);
  $('.categoryProduct').removeClass('onfocus');
  printProducts();
  $('.in').trigger('click');
}

$(document).on('click', '.button-payment', function () {
  if ($('.payment-parent').hasClass('panel-show')) {
    $('#button-payment').removeClass('onfocus');
    $('.payment-parent').removeClass('panel-show');
    return;
  };

  if (JSON.stringify(pos_cart[current_cart]) == '{}') {
    $.toaster({
      priority: 'warning',
      message: error_checkout,
      timeout: 3000
    });
    return;
  } else {
    $('.sidepanel').removeClass('sidepanel-show');
  };

  $('.parents').removeClass('panel-show');
  $('.wksidepanel').removeClass('onfocus');
  $('#button-payment').addClass('onfocus');
  $('.payment-parent').addClass('panel-show');
});

$(document).on('click', '.wkpaymentmethod', function () {
  $('.fa-chevron-right').addClass('hide');
  $('.fa-chevron-left').addClass('hide');
  $('.payment-child2>div').addClass('hide');
  $('.all-payment').removeClass('hide');
  $('#orderNote').val('');
  if (direction == 'ltr') {
    $(this).children('.fa-chevron-right').removeClass('hide');
  } else {
    $(this).children('.fa-chevron-left').removeClass('hide');
  }
  $('.text-danger').remove();
  $('.has-error').removeClass('has-error');
  var type = $(this).attr('type');
  if (type == 'cash-payment') {
    $('.cash-payment').removeClass('hide');
    $('#balance-due').text(current_total_formatted);
    $('.accept-payment').attr('ptype', 'cash');

    if (symbol_position == 'L') {
      var change = currency_code + '0.00';
    } else {
      var change = '0.00' + currency_code;
    }
    $('#change').text(change);
    $('#amount-tendered').val('');
  } else if (type == 'card-payment') {
    $('.accept-payment').attr('ptype', 'card');
    // $('.card-payment').removeClass('hide');
  };
});

$(document).on('click', '.wkaccounts', function () {
  $('.wkaccounts').removeClass('onselect');
  $(this).addClass('onselect');
  $('.fa-chevron-right').addClass('hide');
  $('.fa-chevron-left').addClass('hide');
  if (direction == 'ltr') {
    $(this).children('.fa-chevron-right').removeClass('hide');
  } else {
    $(this).children('.fa-chevron-left').removeClass('hide');
  }
  var type = $(this).attr('type');
  if (type == 'basic') {
    $('.other-account').addClass('hide');
    $('.basic-account').removeClass('hide');
  };
  if (type == 'other') {
    $('.basic-account').addClass('hide');
    $('.other-account').removeClass('hide');
  };
});

//Function to allow only numbers to textbox
function validate(key, thisthis, nodot) {
  //getting key code of pressed key
  var keycode = (key.which) ? key.which : key.keyCode;

  if (keycode == 46) {
    if (nodot) {
      return false;
    }

    var val = $(thisthis).val();
    if (val == val.replace('.', '')) {
      return true;
    } else {
      return false;
    }
  }

  //comparing pressed keycodes
  if (!(keycode == 8 || keycode == 9 || keycode == 46 || keycode == 116) && (keycode < 48 || keycode > 57)) {
    return false;
  } else {
    return true;
  }
}

$(document).on('keyup', '#amount-tendered', function () {
  var cash = parseFloat($(this).val());
  var input_credit = $('#balance-credit input:first').val();
  if (input_credit == undefined) {
    credit_amount = 0;
  }
  if (isNaN(cash)) {
    cash = 0;
  }
  if (!isNaN(cash)) {
    $('#button-credit').click();
    var change = cash - (current_total - uf_total_discount - coupon_disc + delivery_charge - credit_amount);
    var change_formatted;
    if (change > 0) {
      credit_amount -= change;
    }
    $('.text-danger').remove();

    if (symbol_position == 'L') {
      change_formatted = currency_code + parseFloat(change).toFixed(2);
      var reset_change = currency_code + '0.00';
      var balance_due = currency_code + Math.abs(parseFloat(change).toFixed(2));
    } else {
      change_formatted = parseFloat(change).toFixed(2) + currency_code;
      var reset_change = '0.00' + currency_code;
      var balance_due = Math.abs(parseFloat(change).toFixed(2)) + currency_code;
    }
    $('#change').text(reset_change);
    if (change < 0) {
      global_change = 0;
        $('#balance-due').after('<span class="text-danger">'+ text_balance_due + ' ' + balance_due + '</span>');
    } else {
      global_change = change;
      $('#change').text(change_formatted);

    };
  }
});

$(document).on('click', '.accept-payment', function () {

  if (JSON.stringify(pos_cart[current_cart]) == '{}') {
    $.toaster({
      priority: 'warning',
      message: error_checkout,
      timeout: 3000
    });
    return;
  };
  var thisthis = $(this);

  if ($(this).attr('ptype') == 'cash') {
    var amount_box = $('body #amount-tendered');
    var amount_tendered = amount_box.val();
    var total = parseFloat(current_total - uf_total_discount - coupon_disc);
    var tendered = parseFloat(amount_tendered ? amount_tendered : 0);
  // check here
    if (tendered < (total - credit_amount)) {
      amount_box.parent().parent().parent().addClass('has-error');
      var accept = confirm(text_tendered_confirm);
      if (accept == true) {
        acceptPayment(thisthis);
      } else {
        return false;
      }
    } else {
      acceptPayment(thisthis);
    }
  } else if ($(this).attr('ptype') == 'card') {
    var accept = confirm(text_card_confirm);
    if (accept == true) {
      acceptPayment(thisthis);
    }else {
      return false;
    }
  }

  var current_cart_length = Object.keys(pos_cart[current_cart]).length;
  if (!current_cart_length) {
    return;
  }
  var note = $('#holdNote').val();
  pos_holds[current_cart] = {};
    if (note) {
      var tmp_div = document.createElement("DIV");
      tmp_div.innerHTML = note;
      note = tmp_div.textContent || tmp_div.innerText || "";
      pos_holds[current_cart]['note'] = note;
    } else {
      pos_holds[current_cart]['note'] = '';
    }
    pos_holds[current_cart]['date'] = getCurrentDate();
    pos_holds[current_cart]['time'] = getCurrentTime();
    pos_holds[current_cart]['customer_id'] = customer_id;
    pos_holds[current_cart]['customer_name'] = customer_name;
    customer_id = 0;
    customer_name = '';

    var all_carts = Object.keys(pos_cart);
    var total_carts = all_carts.length;
    var last_cart = parseInt(all_carts[parseInt(total_carts - 1)]);
    var new_cart = last_cart + 1;
    pos_cart[new_cart] = {};
    current_cart = new_cart;
    all_carts = Object.keys(pos_cart);
    cartlocalStorage();
    showAllCarts();
    printCart();
});

function acceptPayment(thisthis) {
  getLocalForage('pos_orders');
  if (coupon_disc > (uf_cart_total - uf_total_discount)) {
    coupon_disc = (uf_cart_total - uf_total_discount);
  }
  $('#loader').css('display', 'block');
  var payment_type = thisthis.attr('ptype');
  var order_note = $('#orderNote').val();
  var discount = {};
  discount['discount'] = uf_total_discount;
  discount['name'] = $('#input-discname').val();
  var pos_coupon = {};
  pos_coupon['coupon'] = coupon;
  pos_coupon['discount'] = coupon_disc;
  var tax = parseFloat(cart_tax).toFixed(2);
  $('body #amount-tendered').parent().parent().parent().removeClass('has-error');
  if (!offline) {
    if (credit_amount > 0 && credit_amount >= current_total && $('#amount-tendered') == '' && $('.accept-payment').attr('ptype') == 'cash') {
      partial_credit_payment = false;
      use_credit = true;
      only_credit_payment = true;
    } else if (credit_amount > 0) {
      use_credit = true;
      partial_credit_payment = true;
      only_credit_payment = false;
    }
    $.ajax({
      url: 'index.php?route=wkpos/order/addOrder',
      type: 'post',
      dataType: 'json',
      data: {cart: pos_cart[current_cart], payment_method: payment_type, partial_credit_payment: partial_credit_payment, only_credit_payment: only_credit_payment, shipping_method: shipping_method, shipping_charge: delivery_charge, use_credit: use_credit, credit: credit_amount, add_credit: global_change, customer_id: customer_id, user_id: user_login, order_note: order_note, discount: discount, coupon: pos_coupon, tax: tax, currency: currency},
      beforeSend: function () {
        $('#loader').css('display', 'block');
      },
      success: function (json) {
        if (json['success']) {
          $.toaster({
            priority: 'success',
            message: json['success'],
            timeout: 5000
          });
          getAllCustomers(true);
        };
        getAllOrders();

        if (!detectmob()) {
          $('#postorder').css('display', 'block').find('.buttons-sp:first').attr('disabled', 'disabled');
        } else {
          $('#show-cart').click();
        }

      },
      complete: function () {
        $('#loader').css('display', 'none');
      },
    });
  } else {
    var order = {};
    var d = new Date();
    order['cart'] = pos_cart[current_cart];
    order['payment'] = payment_type;
    order['customer'] = customer_id;
    if (!(customer_name == '')) {
      order['cname'] = customer_name;
    }
    order['date'] = d.getFullYear() + '-' + d.getMonth() + '-' + d.getDate();
    order['time'] = d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
    order['user_login'] = user_login;
    order['order_note'] = order_note;
    order['cashier'] = cashier;
    order['discount'] = discount;
    order['tax'] = tax;
    order['currency'] = currency;
    order['coupon'] = pos_coupon;
    order['txn_id'] = Math.floor((Math.random() * 999999999) + 100000000);
    order['shipping_method'] = shipping_method;
    order['shipping_charge'] = delivery_charge;

    var olength = Object.keys(pos_orders).length;
    pos_orders[olength] = order;
    $.toaster({
      priority: 'success',
      message: text_order_success,
      timeout: 5000
    });
    if (localforage) {
      localforage.setItem('pos_orders',JSON.stringify(pos_orders));
    }
    getLocalForage('pos_orders');
    $('#postorder').css('display', 'block');
    $('#loader').css('display', 'none');
    $('#button-order').trigger('click');
    $('.wkorder[otype="3"]').click();
    $('#orders .order-display:last').trigger('click');
  };
  partial_credit_payment = false;
  only_credit_payment = false;
  uf_total_discount = 0;
  shipping_method = '';
  delivery_charge = 0;
  use_credit = false;
  discountApply = 0;
  global_change = 0;
  credit_amount = 0;
  coupon_disc = 0;
  coupon = '';
  $('#remove-credit').remove();
  $('#balance-credit').html(select_customer_first).css('padding', '6px 15px');
  $('.payment-parent').removeClass('panel-show');
  $('#button-payment').removeClass('onfocus');
  $('.all-payment').addClass('hide');
  $('.fa-chevron-right').addClass('hide');
  $('.fa-chevron-left').addClass('hide');
  for (var i = 0; i < Object.keys(pos_cart[current_cart]).length; i++) {
    if (pos_products[pos_cart[current_cart][i]['product_id']]) {
      if (typeof isBooking === 'function' && isBooking(pos_cart[current_cart][i]['product_id']) >= 0) {
        continue;
      }
      pos_products[pos_cart[current_cart][i]['product_id']]['quantity'] -= pos_cart[current_cart][i]['quantity'];
    }
  }
  cart_list.delete(0);
  customer_id = 0;
  customer_name = '';
  $('#customer-name').text(text_customer_select);
  $('.cash-payment').addClass('hide');
  $('#discrow,#couprow').css('display', 'none');
  $('#delivery-charge').val('');
  $('#delivery-charge').val('');
  $('#checkbox-home-delivery').prop("checked",false);
}

$(document).on('click', '#button-order', function () {
  // POS Update code
  if (!offline) {
    getAllOrders();
  }
  getLocalForage('pos_orders');
  $('#return-section').addClass('hide');
  $('#return-details').addClass('hide');
  $('.order-txn').text(text_order_id);
  $('.oid').text('');
  // POS Update code Ended
  // hiding the cash payment panel
  $('.cash-payment').addClass('hide');
  if ($('.order-parent').hasClass('panel-show')) {
    $(this).removeClass('onfocus');
    $('.order-parent').find('.wkorder .onfocus').click();
    $('.order-parent').removeClass('panel-show');
    $('.sidepanel').removeClass('sidepanel-show');
    return;
  };
  $('#sidepanel-inner').css('display', 'none');
  $('.parents').removeClass('panel-show');
  $('.wksidepanel').removeClass('onfocus');
  $(this).addClass('onfocus');
  $('.order-parent').addClass('panel-show');
  $('.sidepanel').addClass('sidepanel-show');
  if ($('#orders').text() == '') {
    $('.order-child .wkorder:first').trigger('click');
  }
});

$(document).on('click', '#button-account', function () {
  // hiding the cash payment panel
  $('.cash-payment').addClass('hide');
  $('.sidepanel').removeClass('sidepanel-show');
  if ($('.account-parent').hasClass('panel-show')) {
    $(this).removeClass('onfocus');
    $('.account-parent').removeClass('panel-show');
    return;
  };
  $('.parents').removeClass('panel-show');
  $('.wksidepanel').removeClass('onfocus');
  $(this).addClass('onfocus');
  $('.account-parent').addClass('panel-show');
  $('.wkaccounts:first').next().trigger('click');
});

$(document).on('click', '#button-other', function () {
  // hiding the cash payment panel
  $('.cash-payment').addClass('hide');
  if ($('.other-parent').hasClass('panel-show')) {
    $(this).removeClass('onfocus');
    $('.other-parent').removeClass('panel-show');
    $('.sidepanel').removeClass('sidepanel-show');
    return;
  };
  $('#sidepanel-inner').css('display', 'none');
  $('.parents').removeClass('panel-show');
  $('.wksidepanel').removeClass('onfocus');
  $(this).addClass('onfocus');
  $('.other-parent').addClass('panel-show');
  $('.sidepanel').removeClass('sidepanel-show');
  if ($('#others').text() == '') {
    $('.other-child .wkother:first').trigger('click');
  }
});

$(document).on('keyup', function (e) {
  if ((e.which == '13') && (user_login === '0')) {
    loginUser();
  };
});

$(document).on('click', '.wkorder', function () {
  $('.wkorder').removeClass('onfocus');
  var otype = $(this).addClass('onfocus').attr('otype');
  var order_html = '';
  if (otype == 1) {
    for (var i = 0; i < Object.keys(orders).length; i++) {
      order_html += '<div class="col-sm-2 order-display col-xs-4 cursor" order-id="' + orders[i]['order_id'] + '">';
      order_html += '  <div class="order-detail">';
      order_html += '    <div class="invoice-div">Order ID #' + orders[i]['order_id'] + '</div>';
      order_html += '    <div class="datetimeorder">';
      order_html += '      ' + orders[i]['time'] + '<br>';
      order_html += '      ' + orders[i]['date'];
      order_html += '    </div>';
      order_html += '  </div>';
      if (orders[i]['name']) {
        order_html += '  <div class="order-cname">' + orders[i]['name'] + '</div>';
      } else {
        order_html += '  <div class="order-cname">John Doe</div>';
      };
      order_html += '  <div class="table-responsive table-order">';
      order_html += '    <table class="width-100">';
      order_html += '      <tbody>';
      order_html += '       <tr>';
      order_html += '         <th>Status</th>';
      order_html += '         <td>' + orders[i]['status'] + '</td>';
      order_html += '       </tr>';
      order_html += '       <tr>';
      order_html += '         <th>Total</th>';
      order_html += '         <td>' + orders[i]['total'] + '</td>';
      order_html += '       </tr>';
      order_html += '      </tbody>';
      order_html += '    </table>';
      order_html += '  </div>';
      order_html += '</div>';
    };
  } else if (otype == 2) {
    var pos_cart_length = Object.keys(pos_cart).length;
    for (var i = 0; i < pos_cart_length; i++) {
      if (pos_cart[i]) {
        if (i == current_cart) {
          continue;
        };
        order_html += '<div class="col-sm-2 order-display order-display-hold col-xs-4 cursor">';
        order_html += '<div onclick="cart_list.select(' + i + ')" style="height: 91%;">';
        order_html += '  <div class="order-detail" style="height: 28px; padding-top:2px;">';
        order_html += '    <div class="datetimeorder">';
        if (pos_holds[i] && pos_holds[i]['time']) {
          order_html += '      <div class="hold-time">' + pos_holds[i]['time'] + '</div>';
        }
        if (pos_holds[i] && pos_holds[i]['date']) {
          order_html += '      <div class="hold-date">' + pos_holds[i]['date'] + '</div>';
        }
        order_html += '    </div>';
        order_html += '  </div>';
        if (typeof pos_holds[i]!=='undefined' && pos_holds[i]['customer_name']) {
          order_html += '  <div class="order-cname">' + pos_holds[i]['customer_name'] + '</div>';
        } else {
          order_html += '  <div class="order-cname">' + guest_name + '</div>';
        };
        order_html += '  <span class="pull-right label label-info note-info"><i class="fa fa-info-circle"></i> Note </span>';
        if (pos_holds[i] && pos_holds[i]['note']) {
          order_html += '  <div class="hold-note">' + pos_holds[i]['note'] + '</div>';
        } else {
          order_html += '  <div class="hold-note">No note</div>';
        }
        order_html += '  <div class="item-detail">' + text_item_detail + '</div>';
        order_html += '  <div class="table-responsive table-order">';
        order_html += '    <table class="width-100">';
        order_html += '      <tbody>';
        console.log(pos_cart[i]);
        for (var j = 0; j < Object.keys(pos_cart[i]).length; j++) {
          order_html += '      <tr>';
          order_html += '        <td>' + pos_cart[i][j]['name'] + '</td>';
          order_html += '        <td>x' + pos_cart[i][j]['quantity'] + '</td>';
          order_html += '        <td>' + pos_cart[i][j]['price'] + '</td>';
          order_html += '      </tr>';
          if (j == 4) {
            order_html += '<tr><td colspan="3" class="dot-css">...</td></tr>';
            break;
          };
        };
        order_html += '      </tbody>';
        order_html += '    </table>';
        order_html += '  </div>';
        order_html += '  </div>';
        order_html += '  <div class="alert-danger text-center" style="width: 100%; bottom: 0px; position: absolute;" onclick="deleteHoldCart(' + i + ');">';
        order_html += '    <i class="fa fa-trash">';
        order_html += '    </i>';
        order_html += '  </div>';
        order_html += '</div>';
      } else {
        ++pos_cart_length;
      }
    };
  } else if (otype == 3) {
    for (var i = 0; i < Object.keys(pos_orders).length; i++) {
      order_html += '<div class="col-sm-2 order-display col-xs-4 order-display-offline cursor" invoice-id="' + i + '" txn-id="' + pos_orders[i]['txn_id'] + '">';
      order_html += '  <div class="order-detail">';
      order_html += '    <div class="invoice-div">Txn ID #' + pos_orders[i]['txn_id'] + '</div>';
      order_html += '    <div class="datetimeorder">';
      order_html += '      ' + pos_orders[i]['time'] + ' <br>';
      order_html += '      ' + pos_orders[i]['date'];
      order_html += '    </div>';
      order_html += '  </div>';

      if (pos_orders[i]['customer'] && pos_orders[i]['cname']) {
        order_html += '  <div class="order-cname">' + pos_orders[i]['cname'] + '</div>';
      } else {
        order_html += '  <div class="order-cname">' + guest_name + '</div>';
      };
      order_html += '  <div class="item-detail">' + text_item_detail + '</div>';
      order_html += '  <div class="table-responsive table-order">';
      order_html += '    <table class="width-100">';
      order_html += '      <tbody>';
      for (var j = 0; j < Object.keys(pos_orders[i]['cart']).length; j++) {
        order_html += '        <tr>';
        order_html += '          <td>' + pos_orders[i]['cart'][j]['name'] + '</td>';
        order_html += '          <td>x' + pos_orders[i]['cart'][j]['quantity'] + '</td>';
        order_html += '          <td>' + pos_orders[i]['cart'][j]['price'] + '</td>';
        order_html += '        </tr>';
        if (j == 4) {
          order_html += '<tr><td colspan="3" class="dot-css">...</td></tr>';
          break;
        };
      };
      order_html += '      </tbody>';
      order_html += '    </table>';
      order_html += '  </div>';
      order_html += '</div>';
    };
    if (!(order_html == '')) {
      var sync_button = '<div class="row" style="margin: 0">';
      sync_button += ' <button class="btn buttons-sp pull-left" id="sync-orders">' + text_sync_order + '</button>';
      sync_button += '</div>';
      order_html = sync_button + order_html;
    }

  };
  if (order_html == '') {
    order_html = '<span class="col-xs-12 text-center">' + text_no_orders + '</span>';
  };
  $('#orders').html(order_html);
});

$(document).on('click', '.wkother', function () {
  var other_panel = $('#others');
  $('.wkother').removeClass('onfocus');
  var otype = $(this).addClass('onfocus').attr('otype');
  other_panel.html('');
  var other_html = '';
  if (otype == 1) {
    var product_count = total_product_count;
    for (var i = 0; i < product_count; i++) {
      if (pos_products[i]) {
        if (parseInt(pos_products[i]['quantity']) <= low_stock) {
          html = '<div class="col-sm-2 col-xs-6 product-select" product-id="' + pos_products[i]["product_id"] + '">';

          html += '  <img src="' + pos_products[i]["image"] + '" class="product-image" width="100%" height="100%">';
          html += '  <div class="col-xs-12 product-detail">';
          html += '    <b>' + pos_products[i]["name"] + '</b>';
          html += '  </div>';
          html += '</div>';
          other_panel.append(html);
        }
      } else {
        product_count++;
      }
    };
  } else if (otype == 2) {
    var product_count = total_product_count;
    var request_html = '', other_html = '';
    request_html += '<div class="table-responsive">';
    request_html += '  <table class="table table-bordered table-hover">';
    request_html += '    <thead class="btn-info">';
    request_html += '      <tr>';
    request_html += '        <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$(\'.request-check\').prop(\'checked\', this.checked);" /></td>';
    request_html += '        <td>Product Name</td>';
    request_html += '        <td style="width: 150px;">Quantity</td>';
    request_html += '        <td>Supplier</td>';
    request_html += '        <td>Comment</td>';
    request_html += '        <td>Requests</td>';
    request_html += '      </tr>';
    request_html += '    </thead>';
    request_html += '    <tbody>';
    for (var i = 0; i < product_count; i++) {
      if (pos_products[i]) {
        if (parseInt(pos_products[i]['quantity']) <= low_stock) {
          request_html += '<tr product-id="' + pos_products[i]['product_id'] + '">';
          request_html += '  <td><input type="checkbox" class="request-check form-control"></td>';
          request_html += '  <td>' + pos_products[i]['name'] + '</td>';
          request_html += '  <td><input type="number" class="form-control request-quantity" min="1" onkeypress="return validate(event, this, true)"></td>';
          request_html += '  <td><select class="form-control request-supplier">';
          var suppliers_length = Object.keys(pos_products[i]['suppliers']).length;
          if (suppliers_length) {
            for (var j = 0; j < suppliers_length; j++) {
              if (pos_products[i]['suppliers'][j]) {
                request_html += '    <option value="' + pos_products[i]['suppliers'][j]['id'] + '">' + pos_products[i]['suppliers'][j]['name'] + '</option>';
              } else {
                suppliers_length++;
              }
            }
          }
          request_html += '  </select></td>';
          request_html += '  <td><textarea class="form-control request-comment" placeholder="Comments"></textarea></td>';
          if (pos_products[i]['requests'] == 0) {
            request_html += '  <td>' + 'No requests made' + '</td>';
          } else {
            request_html += '  <td class="text-center"><span class="label label-info">' + pos_products[i]['requests'] + '</span></td>';
          }
          request_html += '</tr>';
        }
      } else {
        product_count++;
      }
    };
    request_html += '    </tbody>';
    request_html += '  </table>';
    request_html += '</div>';
    request_html += '<div class="col-sm-12 text-center">';
    request_html += '  <div class="form-group">';
    request_html += '    <label class="col-sm-2 control-label" for="input-extrainfo">Extra Info</label>';
    request_html += '    <div class="col-sm-10">';
    request_html += '      <textarea name="extra_info" placeholder="Extra Info" id="input-extrainfo" class="form-control" rows="3"></textarea>';
    request_html += '    </div>';
    request_html += '  </div>';
    request_html += '  <button class="buttons-sp" onclick="makeRequest();">Make Request</button>';
    request_html += '</div>';
    other_panel.append(request_html);
  } else if (otype == 3) {
    var request_html = '', other_html = '';
    request_html += '<div class="table-responsive">';
    request_html += '  <table class="table table-bordered table-hover">';
    request_html += '    <thead class="btn-info">';
    request_html += '      <tr>';
    request_html += '        <td>ID</td>';
    request_html += '        <td>Date</td>';
    request_html += '        <td class="text-center">Request Details</td>';
    request_html += '        <td>Status</td>';
    request_html += '      </tr>';
    request_html += '    </thead>';
    request_html += '    <tbody>';

    for (var i = 0; i < Object.keys(all_requests).length; i++) {
      request_html += '<tr>';
      request_html += '<td><b>' + all_requests[i]['request_id'] + '</b></td>';
      request_html += '<td>' + all_requests[i]['date_added'] + '</td>';
      request_html += '<td class="text-center">';
      request_html += '<div class="table-responsive">';
      request_html += '  <table class="table table-bordered table-hover">';
      request_html += '    <thead class="btn-info">';
      request_html += '      <tr>';
      request_html += '        <td>Product</td>';
      request_html += '        <td>Supplier</td>';
      request_html += '        <td>Quantity</td>';
      request_html += '      </tr>';
      request_html += '    </thead>';
      request_html += '    <tbody>';
      for (var j = 0; j < Object.keys(all_requests[i]['details']).length; j++) {
        request_html += '<tr>';
        request_html += '<td>' + all_requests[i]['details'][j]['name'] + '</td>';
        request_html += '<td>' + all_requests[i]['details'][j]['sname'] + '</td>';
        request_html += '<td>' + all_requests[i]['details'][j]['quantity'] + '</td>';
        request_html += '</tr>';
      }
      request_html += '    </tbody>';
      request_html += '  </table>';
      request_html += '</div>';
      request_html += '</td>';
      request_html += '<td>' + all_requests[i]['status'] + '</td>';
      request_html += '</tr>';
    }

    request_html += '    </tbody>';
    request_html += '  </table>';
    request_html += '</div>';

    other_panel.append(request_html);
  }
});

$(document).on('click', '.close-it', function () {
  $('.wksidepanel').removeClass('onfocus');
  $('.parents').removeClass('panel-show');
  $('.sidepanel').removeClass('sidepanel-show');
  if ($(this).parent().hasClass('payment-child2')) {
    $('.cash-payment').addClass('hide');
  }
  $('#return-form').html('');
  return_order_product_ids = [];
  $('#return-details').html('');
});

$(document).on('click', '.order-display', function () {
  if ($(this).children().hasClass('alert-danger')) {
    return;
  }
  $('.order-txn').text(text_order_id);
  $('.order-loader').removeClass('hide');
  $('#sidepanel-inner').css('display', 'none');

  var order_id = $(this).attr('order-id');
  var invoice_id = $(this).attr('invoice-id');
  var txn_id = $(this).attr('txn-id');
  var order_html = '', print_order_html = '';
  var total_quantity = 0;

  if (order_id) {
    $('#order-address').html(order_products[order_id]['address']);
    $('.order-date').html(order_products[order_id]['date']);
    $('.order-time').html(order_products[order_id]['time']);
    $('.opayment').html(order_products[order_id]['payment_method']);
    $('.onote').html(order_products[order_id]['note']);
    $('#cashier-name').html(order_products[order_id]['username']);
    $('.order-txn').text(text_order_id);
    $('.oid').html(order_id);

    for (var i in orders) {
      if (orders[i]['order_id'] == order_id) {
        $('#customer-name-in').html(orders[i]['name']);
        break;
      }
    }

    for (var i = 0; i < Object.keys(order_products[order_id]['products']).length; i++) {
      var option_value='';
      var option_value_print='';
      $.each(order_products[order_id]['products'][i]['option'], function (key_option, value_option) {
        if(key_option==0){
          option_value=value_option['value'];
        }else{
            option_value=option_value +','+value_option['value'];
        }
      });
      if(option_value!=''){
        option_value_print ='<br>('+ option_value +')';
      }
      var weight_order = '';
      if(order_products[order_id]['products'][i]['weight']) {
          var weight_order = '<span class="label label-warning" style="white-space: normal;font-style: italic;">W:' + order_products[order_id]['products'][i]['weight'] + '</span>';
      }

      total_quantity += parseInt(order_products[order_id]['products'][i]['quantity']);
      order_html += '<tr>';
      order_html += '  <td class="text-left">' + order_products[order_id]['products'][i]['name'] + '<ul style="list-style: none; padding: 0;">      <li><span class="label label-warning" style="white-space: normal;">'+option_value+'</span></li>    </ul>'+ weight_order +'</td>';
      order_html += '  <td class="text-center">x' + order_products[order_id]['products'][i]['quantity'] + '</td>';
      order_html += '  <td class="text-right">' + order_products[order_id]['products'][i]['total'] + '</td>';
      order_html += '<tr>';
      print_order_html += '<tr>';
      print_order_html += '  <td style="text-align: left">' + order_products[order_id]['products'][i]['name'] + '<span class="label label-warning" style="white-space: normal;font-style: italic;">' + option_value_print + '</span>'+ weight_order +'</td>';
      print_order_html += '  <td>' + order_products[order_id]['products'][i]['quantity'] + '</td>';
      print_order_html += '  <td>' + order_products[order_id]['products'][i]['price'] + '</td>';
      print_order_html += '  <td style="text-align: right">' + order_products[order_id]['products'][i]['total'] + '</td>';
      print_order_html += '</tr>';
    };
    $('#oitem-body').html(order_html);
    $('#receiptProducts').html(print_order_html);
    var total_html = '';
    var print_total_html = '';
    var order_total;
    for (var i = 0; i < Object.keys(order_products[order_id]['totals']).length; i++) {
      if (order_products[order_id]['totals'][i]['title'] == 'Total') {
        order_total = order_products[order_id]['totals'][i]['text'];
        continue;
      };


      total_html += '<tr>';
      total_html += '  <td class="text-left">' + order_products[order_id]['totals'][i]['title'] + '</td>';
      total_html += '  <td class="text-right">' + order_products[order_id]['totals'][i]['text'] + '</td>';
      total_html += '<tr>';
      print_total_html += '<tr>';
      if (i == 0) {
        print_total_html += '  <td>Total Quantity</td>';
        print_total_html += '  <td><b>' + total_quantity + '</b></td>';
        print_total_html += '  <td>' + order_products[order_id]['totals'][i]['title'] + '</td>';
        print_total_html += '  <td style="text-align: right">' + order_products[order_id]['totals'][i]['text'] + '</td>';
      } else {
        print_total_html += '  <td></td>';
        print_total_html += '  <td></td>';
        print_total_html += '  <td>' + order_products[order_id]['totals'][i]['title'] + '</td>';
        print_total_html += '  <td style="text-align: right">' + order_products[order_id]['totals'][i]['text'] + '</td>';
      }
      print_total_html += '</tr>';
    };
    $('#oTotals').html(total_html);
    $('#total-quantity-text').text('');
    $('#total-quantity').text('');
    $('#print-totals').html(print_total_html);
    $('.oTotal').html(order_total);
  };

  if (invoice_id) {
    $('.order-date').html(pos_orders[invoice_id]['date']);
    $('.order-time').html(pos_orders[invoice_id]['time']);
    if (pos_orders[invoice_id]['payment'] == 'cash') {
      $('.opayment').html(cash_payment_title);
    } else {
      $('.opayment').html('');
    }
    $('.onote').html(pos_orders[invoice_id]['order_note']);
    $('#cashier-name').html(pos_orders[invoice_id]['cashier']);
    $('.order-txn').text('Txn ID');
    $('.oid').html(txn_id);
    var uf_total = 0;
    for (var i = 0; i < Object.keys(pos_orders[invoice_id]['cart']).length; i++) {
      total_quantity += parseInt(pos_orders[invoice_id]['cart'][i]['quantity']);

      var weight_order = '';
      if(pos_orders[invoice_id]['cart'][i]['weight']) {
          var weight_order = '<span class="label label-warning" style="white-space: normal;font-style: italic;margin-left: 4%;">W:' + pos_orders[invoice_id]['cart'][i]['weight'] + pos_orders[invoice_id]['cart'][i]['weight_unit'] + '</span>';
      }

      order_html += '<tr>';
      order_html += '  <td class="text-left">' + pos_orders[invoice_id]['cart'][i]['name'] + weight_order +'</td>';
      order_html += '  <td class="text-center">x' + pos_orders[invoice_id]['cart'][i]['quantity'] + '</td>';
      order_html += '  <td class="text-right">' + pos_orders[invoice_id]['cart'][i]['total'] + '</td>';
      order_html += '<tr>';
      print_order_html += '<tr>';
      print_order_html += '  <td style="text-align: left">' + pos_orders[invoice_id]['cart'][i]['name'] + weight_order +'</td>';
      print_order_html += '  <td>' + pos_orders[invoice_id]['cart'][i]['quantity'] + '</td>';
      print_order_html += '  <td>' + pos_orders[invoice_id]['cart'][i]['price'] + '</td>';
      print_order_html += '  <td style="text-align: right">' + pos_orders[invoice_id]['cart'][i]['total'] + '</td>';
      print_order_html += '</tr>';
      uf_total += parseFloat(pos_orders[invoice_id]['cart'][i]['uf_total']);
    };
    $('#oitem-body').html(order_html);
    $('#receiptProducts').html(print_order_html);
    $('#total-quantity-text').text('Total Quantity');
    $('#total-quantity').text(total_quantity);
    var total_html = '';
    var print_total_html = '';

    if (pos_orders[invoice_id]['discount']['discount']) {
      var cdiscount = parseFloat(pos_orders[invoice_id]['discount']['discount']).toFixed(2);
    } else {
      var cdiscount = 0;
    }
    var ctax = 0;
    if (pos_orders[invoice_id]['tax']) {
      ctax = Number(parseFloat(pos_orders[invoice_id]['tax']).toFixed(2));
    }
    var shipping_charge = 0;
    if (pos_orders[invoice_id]['shipping_charge'] && pos_orders[invoice_id]['shipping_method']) {
      shipping_charge = Number(parseFloat(pos_orders[invoice_id]['shipping_charge']).toFixed(2));
      $('.oshipping').text(home_delivery_title);
    } else {
      $('.oshipping').text(text_pickup);
    }
    uf_total = parseFloat(uf_total).toFixed(2);

    if (symbol_position == 'L') {
      var subtotal = currency_code + uf_total;
      var discount = currency_code + cdiscount;
      var f_ctax = currency_code + ctax;
      var f_shipping_charge = currency_code + shipping_charge;
      var total = currency_code + parseFloat(uf_total - cdiscount + ctax + shipping_charge).toFixed(2);
    } else {
      var subtotal = uf_total + currency_code;
      var discount = cdiscount + currency_code;
      var f_ctax = ctax + currency_code;
      var f_shipping_charge = shipping_charge + currency_code;
      var total = parseFloat(uf_total - cdiscount + ctax + shipping_charge).toFixed(2) + currency_code;
    }

    total_html += '<tr>';
    total_html += '  <td class="text-left">' + 'Sub-Total' + '</td>';
    total_html += '  <td class="text-right">' + subtotal + '</td>';
    total_html += '</tr>';
    if (ctax > 0) {
      total_html += '<tr>';
      total_html += '  <td class="text-left">' + 'Tax' + '</td>';
      total_html += '  <td class="text-right">' + f_ctax + '</td>';
      total_html += '</tr>';
    }
    if (shipping_charge > 0) {
      total_html += '<tr>';
      total_html += '  <td class="text-left">' + home_delivery_title + '</td>';
      total_html += '  <td class="text-right">' + f_shipping_charge + '</td>';
      total_html += '</tr>';
    }
    print_total_html += '<tr>';
    print_total_html += '  <td></td>';
    print_total_html += '  <td></td>';
    print_total_html += '  <td>' + 'Sub-Total' + '</td>';
    print_total_html += '  <td style="text-align: right;">' + subtotal + '</td>';
    print_total_html += '</tr>';
    if (ctax > 0) {
      print_total_html += '<tr>';
      print_total_html += '  <td></td>';
      print_total_html += '  <td></td>';
      print_total_html += '  <td>' + 'Tax' + '</td>';
      print_total_html += '  <td style="text-align: right">' + f_ctax + '</td>';
      print_total_html += '</tr>';
    }
    if (shipping_charge > 0) {
      print_total_html += '<tr>';
      print_total_html += '  <td></td>';
      print_total_html += '  <td></td>';
      print_total_html += '  <td>' + home_delivery_title + '</td>';
      print_total_html += '  <td style="text-align: right">' + f_shipping_charge + '</td>';
      print_total_html += '</tr>';
    }

    if (cdiscount) {
      total_html += '<tr>';
      total_html += '  <td class="text-left">' + pos_orders[invoice_id]['discount']['name'] + '</td>';
      total_html += '  <td class="text-right">' + discount + '</td>';
      total_html += '<tr>';
      print_total_html += '<tr>';
      print_total_html += '  <td></td>';
      print_total_html += '  <td></td>';
      print_total_html += '  <td>' + pos_orders[invoice_id]['discount']['name'] + '</td>';
      print_total_html += '  <td style="text-align: right;">' + discount + '</td>';
      print_total_html += '<tr>';
    }

    $('#print-totals').html(print_total_html);
    $('#oTotals').html(total_html);
    $('.oTotal').html(total);
  };
  setTimeout(function () {
    $('#sidepanel-inner').css('display', 'block');
    $('.order-loader').addClass('hide');
    $('#return-button').removeClass('hide').parent().removeClass('hide');
  }, 800);
  if ($(window).width() < 993) {
    $.toaster({
      priority: 'danger',
      message: error_mobile_view,
      timeout: 5000
    });
  }
});

$(document).on('click', '.button-accounts', function () {
  var stype = $(this).attr('stype');
  if (offline) {
    $.toaster({
      priority: 'danger',
      message: error_save_setting,
      timeout: 5000
    });
    return;
  }
  if (stype == 'basic') {
    var postDetails = $('.basic-account input[type=\'text\'], .basic-account input[type=\'password\']');

    $.ajax({
      url: 'index.php?route=wkpos/wkpos/updateProfile',
      dataType: 'json',
      type: 'post',
      data: postDetails,
      beforeSend: function () {
        $('#loader').css('display', 'block');
        $('.has-error').removeClass('has-error');
        $('.text-danger').remove();
      },
      success: function (json) {
        if (json['success']) {
          $('.logger-name').text(postDetails[0].value + ' ' + postDetails[1].value);
          $('#account-ppwd').val('');
          $('#account-npwd').val('');
          $('#account-cpwd').val('');
          $.toaster({
            priority: 'success',
            message: json['success'],
            timeout: 5000
          });
          $('.wksidepanel').removeClass('onfocus');
          $('.parents').removeClass('panel-show');
        };
        if (json['error']) {
          $.each(json['errors'], function (key, value) {
            var selector = key.replace('_', '-');
            $('#' + selector).after('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' + value + '</span>').parent().parent().addClass('has-error');
          });
          $.toaster({
            priority: 'danger',
            message: json['error'],
            timeout: 5000
          });
        };
        $('#loader').css('display', 'none');
      }
    });
  };
  if (stype == 'other') {
    var language = $('#input-language').val();
    var new_currency = $('#input-currency').val();
    $.ajax({
      url: 'index.php?route=wkpos/wkpos/changeSettings',
      dataType: 'json',
      type: 'post',
      data: {language: language, currency: new_currency},
      beforeSend: function () {
        $('#loader').css('display', 'block');
      },
      success: function (json) {
        if (json['success']) {
          window.onbeforeunload = false;
          location = 'index.php?route=wkpos/wkpos';
          $('#loader').css('display', 'block');
          if ($('html').attr('dir') != json['dir']) {
            $('html').attr('dir',json['dir'])
            if (json['dir'] == 'ltr') {
              $('.fa-chevron-left').addClass('fa-chevron-right');
              $('.fa-chevron-left').addClass('fa-chevron-left');
              $('#wkpos-css').attr('href', 'wkpos/css/wkpos.css');
              $('#stylesheet').attr('href', 'catalog/view/theme/default/stylesheet/stylesheet.css');
              $('#bootstrap-css').attr('href', 'catalog/view/javascript/bootstrap/css/bootstrap.min.css');
            } else {
              $('.fa-chevron-right').addClass('fa-chevron-left');
              $('.fa-chevron-right').removeClass('fa-chevron-right');
              $('#wkpos-css').attr('href', 'wkpos/css/wkpos-rtl.css');
              $('#stylesheet').attr('href', 'wkpos/css/stylesheet-rtl.css');
              $('#bootstrap-css').attr('href', 'wkpos/css/bootstrap-rtl.css');
            }
          }
          currency_update = true;
          start = 0;
          getPopularProducts();
          currency = json['currency'];
          currency_code = json['currency_code'];
          symbol_position = json['symbol_position'];
          $('.currency strong').text(currency_code);
          if (symbol_position == 'L') {
            $('.input-group1 .currency:first').css('display', 'table-cell');
            $('.input-group1 .currency:last').css('display', 'none');
            $('.input-group2 .currency:first').css('display', 'table-cell');
            $('.input-group2 .currency:last').css('display', 'none');
            $('.input-group3 .currency:first').css('display', 'table-cell');
            $('.input-group3 .currency:last').css('display', 'none');
          }
          if (symbol_position == 'R') {
            $('.input-group1 .currency:first').css('display', 'none');
            $('.input-group1 .currency:last').css('display', 'table-cell');
            $('.input-group2 .currency:first').css('display', 'none');
            $('.input-group2 .currency:last').css('display', 'table-cell');
            $('.input-group3 .currency:first').css('display', 'none');
            $('.input-group3 .currency:last').css('display', 'table-cell');
          }
          $.toaster({
            priority: 'success',
            message: json['success'],
            timeout: 5000
          });

          $('.wksidepanel').removeClass('onfocus');
          $('.parents').removeClass('panel-show');
        };
      }
    });
  };
});

function updateCartCurrency() {
  for (var cart in pos_cart) {
    if (pos_cart.hasOwnProperty(cart)) {
      for (var product in pos_cart[cart]) {
        if (cart.hasOwnProperty(product)) {
          var cart_product = pos_cart[cart][product];
          if (pos_products[cart_product.product_id]) {
            if (pos_products[cart_product.product_id]['special']) {
              var price = pos_products[cart_product.product_id]['special'];
            } else {
              var price = pos_products[cart_product.product_id]['price_uf'];
            }
            cart_product.uf = price;
            cart_product.price = pos_products[cart_product.product_id]['price'];
          }
        }
      }
    }
  }
}

var pwidth = 0;
var psegments, orders_count;
$(document).on('click', '#sync-orders', function () {
  if (offline) {
    $.toaster({
      priority: 'danger',
      message: error_sync_orders,
      timeout: 5000
    });
    return;
  };
  orders_count = Object.keys(pos_orders).length;
  pwidth = 0;
  psegments = 100/orders_count;
  $('#loader').css('display', 'block');
  $('.progress').removeClass('hide');
  $('#loading-text').removeClass('hide').text(text_sync_order);
  $('.progress-bar').addClass('progress-bar-dan').css('width', 0);
  for (var i = 0; i < orders_count; i++) {
    syncOfflineOrders(i);
  };
});

function syncOfflineOrders (i) {
  setTimeout(function () {
    $.ajax({
      url: 'index.php?route=wkpos/order/addOrder',
      dataType: 'json',
      type: 'post',
      data: {cart: pos_orders[i]['cart'], payment_method: pos_orders[i]['payment'], shipping_method: pos_orders[i]['shipping_method'], shipping_charge: pos_orders[i]['shipping_charge'], customer_id: pos_orders[i]['customer'], offline: 1, user_id: pos_orders[i]['user_login'], order_note: pos_orders[i]['order_note'], txn_id: pos_orders[i]['txn_id'], discount: pos_orders[i]['discount'], currency: pos_orders[i]['currency']},
      beforeSend: function () {
      },
      success: function (json) {
        pwidth += psegments;
        $('.progress-bar').css('width', pwidth + '%');
        if ((i + 1) == orders_count) {
          pos_orders = {};
          localforage.setItem('pos_orders', JSON.stringify(pos_orders));
          $('#orders').html('<span class="col-xs-12 text-center">' + text_no_orders + '</span>');
          setTimeout(function () {
            $.toaster({
              priority: 'success',
              message: json['success'],
              timeout: 3000
            });
            setTimeout(function () {
              getAllOrders();
            }, 1000);
            $('#loader').css('display', 'none');
            $('.progress').addClass('hide');
            $('#loading-text').addClass('hide');
          }, 1000);
        };
      }
    });
  }, i * 1000);
}

function printBill() {
  $('#toaster').css('display', 'none');
  $('#top-div').css('display', 'none');
   $('#webkul_docker').css('display', 'none');
  $('body .bootstrap-datetimepicker-widget').css('display', 'none');
  $('#printBill').css('display', 'block');
  window.print();
  $('#toaster').css('display', 'block');
  $('#top-div').css('display', 'block');
  $('#webkul_docker').css('display', 'block');
  $('#printBill').css('display', 'none');
  $('#loader').css('display', 'none');
}

$(document).on('click', '#hold-carts', function () {
  $('#button-order').trigger('click');
  $('.order-child .wkorder:first').next().trigger('click');
});

function accountSettings (thisthis) {
  $('#button-account').trigger('click');
  $('.wkaccounts:first').trigger('click');
  $(thisthis).addClass('onfocus');
}

function logout () {
  if (offline) {
    $.toaster({
      priority: 'success',
      message: text_success_logout,
      timeout: 3000
    });
    $('#loginModalParent').css('display', 'block');
  } else {
    location = 'index.php?route=wkpos/wkpos/logout';
  };
}

$(document).on('click', '#resumeSession', function () {
  $('#clockin').css('display', 'none');
  getPopularProducts();
});

$(document).on('click', '#startSession', function () {
  localforage.removeItem('pos_cart');
  localforage.removeItem('pos_holds');
  localforage.removeItem('pos_products');
  localforage.removeItem('pos_remove_id');
  $('#clockin').css('display', 'none');
  getPopularProducts();
});

$(document).on('click', '#show-cart', function () {
  if ($(window).width() < 992) {
    var cartpanel = $('#cart-panel');
    if (cartpanel.attr('right-pos') == 0) {
      cartpanel.css('right', '-91.66%');
      cartpanel.attr('right-pos', 91.66);
    } else {
      cartpanel.css('right', 0);
      cartpanel.attr('right-pos', 0);
    }
  }
});

function holdOrder() {
  var current_cart_length = Object.keys(pos_cart[current_cart]).length;
  if (!current_cart_length) {
    $.toaster({
      priority: 'warning',
      message: text_empty_hold,
      timeout: 3000
    });
    $('.in').trigger('click');
    return;
  }
  var note = $('#holdNote').val();
  cart_list.add(note);
  $('.in').trigger('click');
  $('#holdNote').val('');
  $('.wkorder:nth-child(2)').trigger('click');
}

function checkTime(i) {
  if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
  return i;
}

function getCurrentDate() {
  var d = new Date();
  var date = d.getDate();
  var m = d.getMonth();
  var y = d.getFullYear();
  var month_list = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  var full_date = month_list[m] + ' ' + date + ' ' + y;
  return full_date;
}

function getCurrentTime() {
  var today = new Date();
  var h = today.getHours();
  var m = today.getMinutes();
  var s = today.getSeconds();
  m = checkTime(m);
  s = checkTime(s);
  var full_time = h + ":" + m + ":" + s;
  return full_time;
}

function deleteHoldCart(cart_id) {
  cart_list.delete(1, cart_id);
  $('.wkorder:nth-child(2)').trigger('click');
}

$(document).on('change', 'select[name=\'country_id\']', function() {
  $.ajax({
    url: 'index.php?route=account/account/country&country_id=' + this.value,
    dataType: 'json',
    beforeSend: function() {
      $('select[name=\'country_id\']').after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
    },
    complete: function() {
      $('.fa-spin').remove();
    },
    success: function(json) {
      if (json['postcode_required'] == '1') {
        $('input[name=\'postcode\']').parent().parent().addClass('required');
      } else {
        $('input[name=\'postcode\']').parent().parent().removeClass('required');
      }

      html = '<option value="">' + text_select + '</option>';

      if (json['zone'] && json['zone'] != '') {
        for (i = 0; i < json['zone'].length; i++) {
          html += '<option value="' + json['zone'][i]['zone_id'] + '"';

          html += '>' + json['zone'][i]['name'] + '</option>';
        }
      } else {
        html += '<option value="0" selected="selected">' + text_none + '</option>';
      }

      $('select[name=\'zone_id\']').html(html);
    },
    error: function(xhr, ajaxOptions, thrownError) {
      alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
    }
  });
});

function registerCustomer(thisthis, select) {
  var customer_data = $('.addCustomer input, .addCustomer select');
  $.ajax({
    url: 'index.php?route=wkpos/customer/addCustomer',
    data: customer_data,
    dataType: 'json',
    type: 'post',
    beforeSend: function () {
      thisthis.text('loading');
      $('.has-error').removeClass('has-error');
      $('.text-danger').remove();
    },
    success: function (json) {
      if (json['success']) {
        if (select) {
          customer_id = json['customer_id'];
          customer_name = $('#input-customer-firstname').val() + ' ' + $('#input-customer-lastname').val();
          $('#customer-name').text(customer_name);
          $.toaster({
            priority: 'success',
            message: text_cust_add_select,
            timeout: 3000
          });
        } else {
          $.toaster({
            priority: 'success',
            message: json['success'],
            timeout: 3000
          });
        }
        getAllCustomers(true);
        $('.in').trigger('click');
        $('#input-customer-firstname').val('');
        $('#input-customer-lastname').val('');
        $('#input-email').val('');
        $('#input-telephone').val('');
        $('#input-address-1').val('');
        $('#input-city').val('');
        $('#input-postcode').val('');
        $('#input-country').val('');
        $('#input-zone').val('');

        if (pricelist_status) {
            change_customer = customer_id;
            start = 0;
            changePrice(customer_id);
        }

        if (credit_status) {
          if (offline) {
            $('#balance-credit').html(error_credit_offline).css('padding', '6px 15px');
          } else {
            if (customers[customers.length - 1]['credit_uf'] > 0) {
              $('#balance-credit').css('padding', '0').html('<span class="input-group-addon" id="avail-credit">' + customers[customers.length - 1]['credit_f'] + '</span><input class="form-control" type="text" onkeypress="return validate(event, this)" /><span class="input-group-addon btn btn-success" id="button-credit">' + button_credit + '</span>');
            } else {
              $('#balance-credit').html(text_no_credit).css('padding', '6px 15px');
            }
          }
        }

      }
      if (json['error']) {
        if (json['firstname']) {
          $('#input-customer-firstname').after('<div class="text-danger">' + json['firstname'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['lastname']) {
          $('#input-customer-lastname').after('<div class="text-danger">' + json['lastname'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['email']) {
          $('#input-email').after('<div class="text-danger">' + json['email'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['telephone']) {
          $('#input-telephone').after('<div class="text-danger">' + json['telephone'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['address_1']) {
          $('#input-address-1').after('<div class="text-danger">' + json['address_1'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['city']) {
          $('#input-city').after('<div class="text-danger">' + json['city'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['postcode']) {
          $('#input-postcode').after('<div class="text-danger">' + json['postcode'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['country']) {
          $('#input-country').after('<div class="text-danger">' + json['country'] + '</div>').parent().parent().addClass('has-error');
        }
        if (json['zone']) {
          $('#input-zone').after('<div class="text-danger">' + json['zone'] + '</div>').parent().parent().addClass('has-error');
        }
      }

      if (select) {
        thisthis.html('<strong>' + text_register_select + '</strong>');
      } else {
        thisthis.html('<strong>' + text_register + '</strong>');
      }
    },
    error: function () {
      if (select) {
        thisthis.html('<strong>' + text_register_select + '</strong>');
      } else {
        thisthis.html('<strong>' + text_register + '</strong>');
      }
    }
  });
}

function printInvoice() {
  if (!offline) {
    $('.wkorder:first').trigger('click');
    $('#orders .order-display:first').trigger('click');
  }
  printBill();
  $('#postorder').css('display', 'none');
}
$(document).on('click', '.barcode-scan', function () {
  $('#bar-code').val('');
  setTimeout(function () {
    $('#bar-code').focus();
  }, 500);
});

$(document).on('keyup', '#bar-code', function (key) {
  if (key.which == 13) {
    var product = $(this).val();
    var product_id = 0;
    for (var p_id in pos_products) {
        if (pos_products[p_id]['barcode'] == product) {
          product_id = p_id;
          break;
        }
    }

    if (typeof isBooking === 'function' && isBooking(product_id) >= 0) {
      proceedToBookProduct(product_id);
    } else {
      if (!(product == product_id) && pos_products[product_id]) {
        if (pos_products[product_id]['option']) {
          var option = 'true';
          $('.in').trigger('click');
        } else {
          var option = 'false';
        }

        var options = {
          product_id: product_id,
          option: option,
          thisthis: $(this)
        };
        addToCart(false, options);
      } else {
        $.toaster({
          priority: 'warning',
          message: text_no_product,
          timeout: 5000
        });
      }
    }
    $(this).val('');
  }
});

$(document).on('click', '#barcodeScan', function () {
    $('#bar-code').focus();
});

$(document).on('keyup', '#fixedDisc,#percentDisc', function () {
  var fixedDisc = $('#fixedDisc').val();
  var percentDisc = $('#percentDisc').val();
  var fixed = parseFloat(fixedDisc).toFixed(2);
  var percent = parseFloat(percentDisc).toFixed(2);
  if (fixed == 'NaN') {
    fixed = 0;
  }
  if (percent == 'NaN') {
    percent = 0;
  }

  var percentDiscount = (uf_sub_total * percent)/100;
  var total_discount = (parseFloat(percentDiscount) + parseFloat(fixed)).toFixed(2);
  totalDiscount = total_discount;
  $('#total-discount').text(total_discount);
});

$(document).on('click', '#addDiscountbtn', function () {
  $('#fixedDisc').trigger('keyup');

  if (totalDiscount > parseFloat(uf_sub_total)) {
    $.toaster({
      priority: 'warning',
      message: error_cart_discount,
      timeout: 3000
    });
    uf_total_discount = totalDiscount = 0;
  } else {
    uf_total_discount = totalDiscount;

    if (!discountApply && (totalDiscount > 0)) {
      $.toaster({
        priority: 'success',
        message: text_success_add_disc,
        timeout: 3000
      });
    }

    if (totalDiscount > 0) {
      $('#discrow').css('display', '');
      discountApply = 1;
    } else {
      $('#discrow').css('display', 'none');
      discountApply = 0;
    }

    $('.in').trigger('click');
  }
  printCart();
});

$(document).on('click', '#removeDiscount', function () {
  uf_total_discount = 0;
  $('#discrow').css('display', 'none');

  $.toaster({
    priority: 'success',
    message: text_success_rem_disc,
    timeout: 3000
  });
  $('.in').trigger('click');
  discountApply = 0;
  printCart();
});

$(document).on('click', '#addCouponbtn', function () {
  var coupon_code = $('#coupon-code').val();
  $('.in').trigger('click');

  if (offline) {
    $.toaster({
      priority: 'warning',
      message: error_coupon_offline,
      timeout: 3000
    });
  } else {
    $.ajax({
      url: 'index.php?route=wkpos/order/applyCoupon',
      dataType: 'json',
      type: 'post',
      data: {coupon: coupon_code, customer: customer_id, subtotal: uf_sub_total, cart: pos_cart[current_cart]},
      beforeSend: function () {
        $('#loader').css('display', 'block');
      },
      success: function (json) {
        if (json['success']) {
          $.toaster({
            priority: 'success',
            message: json['success'],
            timeout: 3000
          });

          $('#couprow').css('display', 'none');

          coupon_info = json['coupon'];

          if (json['coupon']['type']) {
            coupon_disc = cartCouponDicount();
            coupon = json['coupon']['code'];

            $('#couprow').css('display', '');
          }

          $('#loader').css('display', 'none');
          printCart();

        };

        if (json['error']) {
          $.toaster({
            priority: 'danger',
            message: json['error'],
            timeout: 3000
          });
        };
      },
      complete: function() {
        $('#loader').css('display', 'none');
        printCart();
        $('#couprow').css('display', '');
      }
    });
  }
});

$(document).on('click', '#removeCoupon', function () {
  coupon_disc = 0;
  if (coupon) {
    coupon = '';
    $('#couprow').css('display', 'none');

    $.toaster({
      priority: 'success',
      message: text_coupon_remove,
      timeout: 3000
    });
  }
  $('#coupon-code').val('');
  coupon_info = {};
    printCart();
  $('.in').trigger('click');
});

function toggleFullScreen() {
  if ((document.fullScreenElement && document.fullScreenElement !== null) ||
   (!document.mozFullScreen && !document.webkitIsFullScreen)) {
    if (document.documentElement.requestFullScreen) {
      document.documentElement.requestFullScreen();
    } else if (document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if (document.documentElement.webkitRequestFullScreen) {
      document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
    }
  } else {
    if (document.cancelFullScreen) {
      document.cancelFullScreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitCancelFullScreen) {
      document.webkitCancelFullScreen();
    }
  }
}
function makeRequest() {
  if (offline) {
    $.toaster({
      priority: 'danger',
      message: error_request_offline,
      timeout: 5000
    });
    return;
  }

  var error = '', request_data = [], l = 0, check = 0;
  $('.has-error').removeClass('has-error');
  $('.text-danger').remove();

  $('#others .request-check').each(function(index) {
    if ($(this).is(':checked')) {
      check = 1;
      var parent_tr = $(this).parent().parent();
      var current_quant = parent_tr.children('td:nth-child(3)').children('.request-quantity');
      var quantity = current_quant.val();

      if (parseInt(quantity)) {
        var current_supplier = parent_tr.children('td:nth-child(4)').children('.request-supplier');
        var supplier_id = current_supplier.val();
        var product_id = parent_tr.attr('product-id');

        if (supplier_id && pos_products[product_id] && pos_products[product_id]['suppliers'][supplier_id]) {
          var supplier = pos_products[product_id]['suppliers'][supplier_id];

          if (quantity < parseInt(supplier['min']) || quantity > parseInt(supplier['max'])) {
            error = 1;
            current_quant.after('<span class="text-danger">' + error_supplier_range.replace('%range%', supplier['min'] + '-' + supplier['max']) + '</span>').parent().addClass('has-error');
          } else {
            var comment = parent_tr.children('td:nth-child(5)').children('.request-comment').val();
            var data = {
              'sid': supplier_id,
              'pid': product_id,
              'quant': quantity,
              'comm': comment
            };
            request_data[l++] = data;
          }
        } else {
          error = 1;
          current_supplier.after('<span class="text-danger">' + error_supplier + '</span>').parent().addClass('has-error');
        }
      } else {
        error = 1;
        current_quant.after('<span class="text-danger">' + text_no_quantity_added + '</span>').parent().addClass('has-error');
      }
    }
  });

  if (error || !check) {
    if (!check) {
      $.toaster({
        priority: 'danger',
        message: error_select_product,
        timeout: 5000
      });
    } else {
      $.toaster({
        priority: 'danger',
        message: error_warning,
        timeout: 5000
      });
    }
  } else {
    $.ajax({
      url: 'index.php?route=wkpos/supplier/addRequest',
      dataType: 'json',
      type: 'post',
      data: {request_data: request_data, comment: $('#input-extrainfo').val()},
      beforeSend: function () {
        $('#loader').css('display', 'block');
      },
      success: function (json) {
        if (json['success']) {
          $.toaster({
            priority: 'success',
            message: json['success'],
            timeout: 5000
          });

          // $('.wkother:nth-child(2)').trigger('click');
          $('.close-it').trigger('click');
          getAllProducts();
        };
        if (json['error']) {
          $.toaster({
            priority: 'danger',
            message: json['error'],
            timeout: 5000
          });
        };
        $('#loader').css('display', 'none');
      }
    });
  }
}

function getAllSuppliers() {
  $.ajax({
    url: 'index.php?route=wkpos/supplier',
    dataType: 'json',
    type: 'get',
    success: function (json) {
      all_suppliers = json['suppliers'];
    }
  });
}

function getRequestHistory() {
  $.ajax({
    url: 'index.php?route=wkpos/supplier/getRequestHistory',
    dataType: 'json',
    type: 'get',
    success: function (json) {
      all_requests = json['requests'];
      $('.wkother:nth-child(3)').trigger('click');
    }
  });
}

function priceUpdate(i) {
  var current_price = pos_cart[current_cart][i]['uf'];
  var product_name = pos_cart[current_cart][i]['name'];
  $('#update-label').text(product_name);
  $('#update-price').prop('value', current_price);
  $('#cart-index').val(i);
}

$(document).on('click', '#updatePricebtn', function () {
  var new_price = $('#update-price').val();
  if (isNaN(new_price)) {
	    $.toaster({
	      priority: 'danger',
	      message: error_price,
      timeout: 3000
	    });
    return false;
	  }
  var cart_index = $('#cart-index').val();
  pos_cart[current_cart][cart_index]['uf'] = new_price;
  pos_cart[current_cart][cart_index]['price_uf'] = new_price;
  pos_cart[current_cart][cart_index]['actual_price'] = new_price;
  pos_cart[current_cart][cart_index]['uf_total'] =  Number(new_price)*Number(pos_cart[current_cart][cart_index]['quantity']);

  pos_cart[current_cart][cart_index]['special'] = formatPrice(new_price);
  pos_cart[current_cart][cart_index]['custom'] = 1;
  $('.in').trigger('click');
  $.toaster({
    priority: 'success',
    message: text_success_price_up,
    timeout: 3000
  });
  cartlocalStorage();
  printCart();
});

$(document).on('click', '#cancelPriceUp', function () {
  var cart_index = $('#cart-index').val();
  pos_cart[current_cart][cart_index]['uf'] = pos_products[pos_cart[current_cart][cart_index]['product_id']]['price_uf'];
    pos_cart[current_cart][cart_index]['actual_price'] = pos_products[pos_cart[current_cart][cart_index]['product_id']]['price_uf'];
  // Updating Cart price with option
  if (pos_cart[current_cart][cart_index]['options'] && pos_products[pos_cart[current_cart][cart_index]['product_id']]['options']) {
    for (var prod_opt in pos_products[pos_cart[current_cart][cart_index]['product_id']]['options']) {
      for (var cart_opt in pos_cart[current_cart][cart_index]['options']) {
        if (typeof(pos_cart[current_cart][cart_index]['options'][cart_opt]['product_option_id']) != 'undefined' && typeof(pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_id']) != 'undefined' && pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_id'] == pos_cart[current_cart][cart_index]['options'][cart_opt]['product_option_id']) {
          for (var opt_id in pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_value']) {
              if (typeof(pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_value'][opt_id]['product_option_value_id']) != 'undefined' && typeof(pos_cart[current_cart][cart_index]['options'][cart_opt]['value']) != 'undefined' && pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_value'][opt_id]['product_option_value_id'] == pos_cart[current_cart][cart_index]['options'][cart_opt]['value']) {
                if (pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_value'][opt_id]['price_prefix'] == '+') {
                  pos_cart[current_cart][cart_index]['uf'] = Number(pos_cart[current_cart][cart_index]['uf']) +  Number(pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_value'][opt_id]['uf']);
                } else if (pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_value'][opt_id]['price_prefix'] == '-') {
                  pos_cart[current_cart][cart_index]['uf'] = Number(pos_cart[current_cart][cart_index]['uf']) - Number(pos_products[pos_cart[current_cart][cart_index]['product_id']]['options'][prod_opt]['product_option_value'][opt_id]['uf']);
                }
              }
          }
        }
      }
    }
  }
  // Updating Cart price with option ended
  pos_cart[current_cart][cart_index]['special'] = 0;
  delete pos_cart[current_cart][cart_index]['custom'];
  $('.in').trigger('click');
  $.toaster({
    priority: 'success',
    message: text_price_remove,
    timeout: 3000
  });
  cartlocalStorage();
  printCart();
});

$(document).on('click', '#addProductbtn', function () {
  var product_name = $.trim($('#addProduct input[name="product_name"]').val());
  var product_price = $.trim($('#addProduct input[name="product_price"]').val());
  var product_quantity = $.trim($('#addProduct input[name="product_quantity"]').val());
  var error = {};
  if (product_name.match(/<script>|alert|onmouseover=|onclick=|onmouseenter=|onblur=|onfocusin=|onfocusout=/)) {
    $.toaster({
      priority: 'danger',
      message: error_script,
      timeout: 3000
    });
    return false;
  }
  if (!(product_name && product_name.length > 3  && product_price > 0)) {
    error.product_name = error_product_name;
  }
  if (!(product_price && !isNaN(product_price) && product_quantity > 0)) {
    error.product_price = error_product_price;
  }
  if (!(product_quantity && !isNaN(product_quantity) && (product_quantity.indexOf(".") == -1))) {
    error.product_quantity = error_product_quant;
  }

  if (Object.keys(error).length > 0) {
    for (var value in error) {
      if (error.hasOwnProperty(value)) {
        $.toaster({
          priority: 'danger',
          message: error[value],
          timeout: 3000
        });
      }
    }
  } else {

    var add_product = {};
    product_price = parseFloat(product_price).toFixed(2);
    product_quantity = parseInt(product_quantity);
    add_product.name = product_name;
    add_product.model = product_name;
    add_product.options = [];
    add_product.price = formatPrice(product_price);
    add_product.product_id = 0;
    add_product.product_index = 0;
    add_product.quantity = product_quantity;
    add_product.remove = pos_remove_id++;
    add_product.special = 0;
    add_product.total = formatPrice(product_price * product_quantity);
    add_product.price_uf = product_price;
    add_product.actual_price = product_price;
    add_product.uf = product_price;
    add_product.uf_total = product_price * product_quantity;
    add_product.custom = 1;
    add_product.group_wise_price = [];
    add_product.unit_price_status = 0;



    var adding = Object.keys(pos_cart[current_cart]).length;
    pos_cart[current_cart][adding] = add_product;
    $('.in').trigger('click');
    $.toaster({
      priority: 'success',
      message: text_product_success,
      timeout: 3000
    });
    cartlocalStorage();
    printCart();
  }
});
$(document).ready(function () {
  var tendered_box = document.getElementById('amount-tendered');
  tendered_box.onpaste = function(e) {
    e.preventDefault();
  }
});

// POS Update Code
$(document).on('click', '.btn-return', function(){
  exchange = false;
  if (!offline) {
    if ($('#return-order').modal('show')) {
        setTimeout(function(){
          $('#search-order-id').focus();
        },500);
    }
  } else {
    $.toaster({
      priority: 'danger',
      message: error_return_offline,
      timeout: 3000
    });
    $('#return-order .modal-title').text(heading_return);
  }
});

$(document).on('click','#button-return', function() {
  $('.order-txn').text(text_return_id);
  $('.oid').text('');
  var return_html = '';
  for (var i = 0; i < Object.keys(pos_returns).length; i++) {
    return_html += '<div class="col-sm-3 return-display col-xs-5 cursor" return-id="' + pos_returns[i]['return_id'] + '">';
    return_html += '  <div class="order-detail">';
    return_html += '    <div class="invoice-div">' + text_return_id + ' #' + pos_returns[i]['return_id'] + '<br>' + text_order_id + ' #' + pos_returns[i]['order_id'] +'</div>';
    return_html += '    <div class="datetimeorder">' + text_return_date + ': ' + pos_returns[i]['date_added'] + '<br>';
    return_html += '        '+ text_order_date + ': ' + pos_returns[i]['date_ordered'] + '<br>';
    return_html += '    </div>';
    return_html += '  </div>';
    if (pos_returns[i]['name']) {
      return_html += '  <div class="order-cname">' + pos_returns[i]['name'] + '</div>';
    } else {
      return_html += '  <div class="order-cname">John Doe</div>';
    };
    return_html += '  <div class="table-responsive table-order">';
    return_html += '    <table class="width-100">';
    return_html += '      <tbody>';
    return_html += '       <tr>';
    return_html += '         <th>' + text_return_status + '</th>';
    return_html += '         <td>' + pos_returns[i]['return_status'] + '</td>';
    return_html += '       </tr>';
    return_html += '       <tr>';
    return_html += '         <th>' + text_return_action + '</th>';
    return_html += '         <td>' + pos_returns[i]['return_action'] + '</td>';
    return_html += '       </tr>';
    return_html += '      </tbody>';
    return_html += '    </table>';
    return_html += '  </div>';
    return_html += '</div>';
  }
  $('#returns').html(return_html);
  $('#return-section').addClass('hide');

  // hiding the cash payment panel
  $('.cash-payment').addClass('hide');
  if ($('.return-parent').hasClass('panel-show')) {
    $(this).removeClass('onfocus');
    $('.return-parent').removeClass('panel-show');
    $('.sidepanel').removeClass('sidepanel-show');
    return;
  };
  $('#sidepanel-inner').css('display', 'none');
  $('.parents').removeClass('panel-show');
  $('.wksidepanel').removeClass('onfocus');
  $(this).addClass('onfocus');
  $('.return-parent').addClass('panel-show');
  $('.sidepanel').addClass('sidepanel-show');
});

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
$(document).on('keyup', '#search-order-id', function(){
  var keyword = $(this).val();
  if (keyword == '') {
    return;
  };
  var search_html = '<table class="table table-striped table-hover">';
  search_html += '<thead>';
  search_html += '  <tr>';
  search_html += '    <td>' + text_order_id + '</td>';
  search_html += '    <td>' + entry_customer + '</td>';
  search_html += '    <td>' + entry_status + '</td>';
  search_html += '    <td>' + text_total + '</td>';
  search_html += '  </tr>';
  search_html += '</thead>';
  search_html += '<tbody>';
  $.each(orders, function(index, order){
    if (order['order_id'].startsWith(keyword)) {
      search_html += '<tr class="cursor return-order" order-index="' + order['order_id'] + '">';
      search_html +=' <td><strong>#'+order['order_id']+'</strong></td>';
      search_html +=' <td>'+order['name']+'</td>';
      search_html +=' <td>'+order['status']+'</td>';
      search_html +=' <td>'+order['total']+'</td>';
      search_html += '</tr>';
    }
  });

  search_html += '  </tbody>';
  search_html += '</table>';
  $('#search-order-result').html(search_html);
});

$(document).on('click','.return-order', function() {
  return_order_product_ids = [];
  $('.sidepanel').addClass('sidepanel-show');
  var return_order_id = $(this).attr('order-index');
  if (return_order_id) {
    $('.oid').text(return_order_id);
    $('#return-order').modal('hide');
    $('#return-section').removeClass('hide');
    var return_html = '';
    return_html += '  <table class="table">';
    return_html += '    <tbody>';
    for (var i = 0; i < Object.keys(order_products[return_order_id]['products']).length; i++) {
      return_html += '<tr>';
      return_html += '  <td class="text-left">';
      return_html += '    <div class="checkbox checkbox-primary">';
      return_html += '     <input class="return-checkbox" uf-total="' + order_products[return_order_id]['products'][i]['uf_total'] + '" uf-price="' + order_products[return_order_id]['products'][i]['uf_price'] + '" product-id="'+ order_products[return_order_id]['products'][i]['product_id'] +'" id="checkbox'+ order_products[return_order_id]['products'][i]['order_product_id'] +'" type="checkbox" value="'+ order_products[return_order_id]['products'][i]['order_product_id'] +'">';
      return_html += '     <label for="checkbox'+ order_products[return_order_id]['products'][i]['order_product_id'] +'"></label>';
      return_html += '    </div>';
      return_html += '  </td>';
      $.each(pos_products, function(id, item) {
        if (id == order_products[return_order_id]['products'][i]['product_id']) {
            return_html += '  <td class="text-left"><img src="'+item['image']+'" height="30" width="30"/></td>';
        }
      });
      return_html += '  <td class="text-left">' + order_products[return_order_id]['products'][i]['name'] + '</td>';
      return_html += '  <td class="text-center">x' + order_products[return_order_id]['products'][i]['quantity'] + '</td>';
      return_html += '  <td class="text-left">' + order_products[return_order_id]['products'][i]['total'] + '</td>';
      if (exchange) {
        return_html += '  <td class="text-left"><input type="number" class="form-control exchange-quantity" id="exchange-quantity'+order_products[return_order_id]['products'][i]['order_product_id']+'" placeholder="Qty" product-name="' + order_products[return_order_id]['products'][i]['name'] + '" order-product-quantity="'+ order_products[return_order_id]['products'][i]['quantity'] +'" value="1"/></td>';
      }
      return_html += '</tr>';
    };
    return_html += '    </tbody>';
    return_html += '    <tfoot>';
    return_html += '      <tr><td colspan="3"><a class="check-all">Select All</a>&nbsp;/&nbsp;<a class="uncheck-all">Unselect All</a></td>';
    $.each(orders, function(index, order) {
      if (return_order_id == order['order_id']) {
        if (exchange) {
          return_html += '        <td colspan="2" class="text-left">' + text_total + '</td>';
        } else {
          return_html += '        <td class="text-left">' + text_total + '</td>';
        }
        return_html += '        <td class="text-left uf-total" uf-total="' + order['uf_total'] + '">' + order['total']  + '</td>';
      }
    });
    return_html += '      </tr>';
    return_html += '      <tr>';
    if (exchange) {
      return_html += '        <td class="text-center" colspan="6"><button class="btn buttons-sp" id="exchange-products">' + button_exchange + '</button></td>';
    } else {
      return_html += '        <td class="text-center" colspan="5"><button class="btn buttons-sp" id="return-products">' + button_return + '</button></td>';
    }

    return_html += '      </tr>';
    return_html += '    </tfoot>';
    return_html += '  </table>';
  }
  $('#return-section').html(return_html).removeClass('hide');
  $('#sidepanel-inner').css('display', 'none');
});
var quantity_error = false;
$(document).on('keyup change', '.exchange-quantity', function(event){
  if (!(event.type == 'change' & event.type == 'change')) {
    if (parseInt($(this).attr('order-product-quantity')) < parseInt($(this).val())) {
      $.toaster({
       priority: 'warning',
       message: error_quantity_exceed.replace(/%s/,$(this).attr('product-name')),
       timeout: 5000
     });
     quantity_error = true;
     return false;
   } else {
     quantity_error = false;
   }
  }
});

var return_order_product_ids = [];
var return_product_ids = [];
$(document).on('change', '.return-checkbox', function(){
  // var return_uf_total = 0;
  if (this.checked) {
    return_order_product_ids.push($(this).val());
    return_product_ids.push($(this).attr('product-id'));
    // return_uf_total = parseFloat($('.uf-total').attr('uf-total')) - parseFloat($(this).attr('uf-total'));
    // $('.uf-total').text(formatPrice(return_uf_total)).attr('uf-total',return_uf_total);
  } else {
     const index = return_order_product_ids.indexOf($(this).val());
     const index1 = return_order_product_ids.indexOf($(this).attr('product-id'));
     return_order_product_ids.splice(index, 1);
     return_product_ids.splice(index1, 1);
    // return_uf_total = parseFloat($('.uf-total').attr('uf-total')) + parseFloat($(this).attr('uf-total'));
    // $('.uf-total').text(formatPrice(return_uf_total)).attr('uf-total',return_uf_total);
  }
  return_order_product_ids.sort(function(a, b){return a-b});
});

$(document).on('click', '#return-products', function() {
  $('#return-panel').remove();
  var return_order_id = $('.oid:last').text();
  var return_form = '';
  if (return_order_product_ids.length == 0) {
    $.toaster({
      priority: 'warning',
      message: error_return_product,
      timeout: 5000
    });
    return false;
  } else {
    return_form += '<div class="panel panel-default" id="return-panel">';
    return_form += '     <div class="panel panel-body color-black">';
    // Order Information
    return_form += '        <h3>' + text_order_info  + '</h3>';
    if (screen.width > 767) {
      return_form += '        <table class="table table-striped">';
      return_form += '         <thead>';
      return_form += '           <tr>';
      return_form += '             <td>' + text_order_id + '</td>';
      return_form += '             <td>' + entry_firstname + '</td>';
      return_form += '             <td>' + entry_lastname + '</td>';
      return_form += '             <td>' + entry_email + '</td>';
      return_form += '             <td>' + entry_telephone + '</td>';
      return_form += '             <td>' + text_order_date + '</td>';
      return_form += '           </tr>';
      return_form += '         </thead>';
      return_form += '         <tbody>';
      $.each(orders, function(index, order){
        var customername = order['name'].split(" ");
        if (order['order_id'] == return_order_id) {
          return_form +=' <tr class="cursor return-order" order-index="' + order['order_id'] + '">';
          return_form +='   <td><strong>#'+order['order_id']+'</strong></td>';
          return_form +='   <td><input type="text" class="form-control" disabled id="cfirstname" value="'+customername[0]+'"/></td>';
          return_form +='   <td><input type="text" class="form-control" disabled id="clastname" value="'+customername[1]+'"/></td>';
          return_form +='   <td><input type="text" class="form-control" disabled id="cemail" value="'+order['email']+'"/></td>';
          return_form +='   <td><input type="text" class="form-control" disabled id="ctel" value="'+order['telephone']+'"/></td>';
          return_form +='   <td>'+order['date']+'</td>';
          return_form +=' </tr>';
        }
      });
      return_form += '         </tbody>';
      return_form += '       </table>';
   } else {
     return_form += '<form class="form-horizontal">';
     $.each(orders, function(index, order){
       var customername = order['name'].split(" ");
       if (order['order_id'] == return_order_id) {
         return_form += '<div class="col-sm-12">';
         return_form += '  <label>' + text_order_id + '</label>';
         return_form += ' #'+order['order_id'];
         return_form += ' <div class="form-group-sm">';
         return_form += '  <label>' + entry_firstname + '</label>';
         return_form += '   <input type="text" class="form-control" id="cfirstname" value="'+ customername[0] +'">';
         return_form += ' </div>'; // form-group-sm
         return_form += ' <div class="form-group-sm">';
         return_form += '  <label>' + entry_lastname + '</label>';
         return_form += '   <input type="text" class="form-control" id="clastname" value="'+ customername[1] +'">';
         return_form += ' </div>'; // form-group-sm
         return_form += ' <div class="form-group-sm">';
         return_form += '  <label>' + entry_email + '</label>';
         return_form += '   <input type="text" class="form-control" id="cemail" value="'+ order['email'] +'">';
         return_form += ' </div>'; // form-group-sm
         return_form += ' <div class="form-group-sm">';
         return_form += '  <label>' + entry_telephone + '</label>';
         return_form += '   <input type="text" class="form-control" id="ctel" value="'+ order['telephone'] +'">';
         return_form += ' </div>'; // form-group-sm
         return_form += ' <div class="form-group-sm">';
         return_form += '  <label>' + text_order_date + '</label>';
         return_form += '   <div class="form-control">'+ order['date'] +'</div>';
         return_form += ' </div>'; // form-group-sm
         return_form += '</div>'; // col-sm-12
       }
     });
     return_form += '</form>';
   }
    // Order Information Ended

    // Product Information
    return_form += '  <form class="form-horizontal" id="return-product-form">';
    return_form += '     <legend>'+ text_product_info +'</legend>';
    return_form += '     <div class="panel-group" id="accordion">';
    var collapse_in = '';
    $.each(order_products[return_order_id]['products'], function(index, product){
      for (var i = 0; i < return_order_product_ids.length; i++) {
        collapse_in = i == 0 ? 'in' : '';
        if (product['order_product_id'] == return_order_product_ids[i]) {
          return_form +='   <div class="panel panel-default">';
          return_form +='     <div class="panel-heading">';
          return_form +='       <h4 class="panel-title">';
          return_form +='         <a data-toggle="collapse" data-parent="#accordion" id="collapse-href'+i+'" href="#collapse'+i+'">' + product['name'] + '</a>';
          return_form +='       </h4>';
          return_form +='     </div>';
          return_form +='     <div id="collapse'+i+'" class="panel-collapse collapse '+ collapse_in +'">';
          return_form +='       <div class="panel-body">';
          return_form +='         <div class="form-group required">';
          return_form +='           <label class="col-sm-2 control-label" for="input-product'+product['order_product_id']+'">'+entry_name+'</label>';
          return_form +='           <div class="col-sm-10">';
          return_form +='             <input type="text" name="product['+product['order_product_id']+']" value="'+product['name']+'" placeholder="'+entry_name+'" id="input-product'+product['order_product_id']+'" class="form-control return-product"/>';
          return_form +='           <input type="hidden" name="return_product_id['+product['order_product_id']+']" value="'+product['order_product_id']+'">';
          return_form +='           </div>';
          return_form +='         </div>';

          return_form +='         <div class="form-group required">';
          return_form +='           <label class="col-sm-2 control-label" for="input-model'+product['order_product_id']+'">'+entry_model+'</label>';
          return_form +='           <div class="col-sm-10">';
          return_form +='             <input type="text" name="model['+product['order_product_id']+']" value="'+product['model']+'" placeholder="'+entry_model+'" id="input-model'+product['order_product_id']+'" class="form-control"/>';
          return_form +="           </div>";
          return_form +='        </div>';

          return_form +='        <div class="form-group">';
          return_form +='          <label class="col-sm-2 control-label" for="input-quantity'+product['order_product_id']+'">'+entry_quantity+'</label>';
          return_form +='         <div class="col-sm-10">';
          return_form +='           <input type="text" name="quantity['+product['order_product_id']+']" value="1" placeholder="'+entry_quantity+'"; id="input-quantity'+product['order_product_id']+'" class="form-control" />'
          return_form +='         </div>';
          return_form +='       </div>';

          return_form +='       <div class="form-group required">';
          return_form +='         <label class="col-sm-2 control-label">'+entry_reason+'</label>'
          return_form +='         <div class="col-sm-10">';
          for (var id = 0; id < return_reasons.length; id++) {
            return_form +='         <div class="radio radio-primary">';
            return_form +='           ';
            return_form +='             <input id="return-reason-id-' + product['order_product_id'] + '-'+return_reasons[id].return_reason_id+'" type="radio" name="return_reason_id['+product['order_product_id']+']" value="'+ return_reasons[id].return_reason_id +'" />';
            return_form +='           <label for="return-reason-id-' + product['order_product_id'] + '-'+return_reasons[id].return_reason_id+'">' + return_reasons[id].name + '</label>';
            return_form +='         </div>';
          }
          return_form +='        </div>';
          return_form +='     </div>';

          return_form +='     <div class="form-group required">';
          return_form +='       <label class="col-sm-2 control-label">'+entry_opened+'</label>';
          return_form +='       <div class="col-sm-10 text-left">';
          return_form +='       <div class="radio radio-primary radio-inline">';
          return_form +='         <input id="opened-yes-'+product['order_product_id']+'" type="radio" name="opened['+product['order_product_id']+']" value="1" />';
          return_form +='         <label for="opened-yes-'+product['order_product_id']+'">' + text_yes +'</label>';
          return_form +='        </div>';
          return_form +='       <div class="radio radio-primary radio-inline">';
          return_form +='           <input id="opened-no-'+product['order_product_id']+'" type="radio" name="opened['+product['order_product_id']+']" value="0"/>';
          return_form +='           <label for="opened-no-'+product['order_product_id']+'">' + text_no +'</label>';
          return_form +='         </div>';
          return_form +='       </div>';
          return_form +='      </div>';

          return_form +='      <div class="form-group required">';
          return_form +='         <label for="return-action'+product['order_product_id']+'" class="col-sm-2 control-label">'+entry_action+'</label>';
          return_form +='         <div class="col-sm-10">';
          return_form +='           <select class="form-control" id="return-action'+product['order_product_id']+'" name="return_action['+product['order_product_id']+']">';
          return_form +='             <option value="0"></option>';
          for (var id = 0; id < return_actions.length; id++) {
            return_form +='           <option value="'+ return_actions[id].return_action_id +'">' + return_actions[id].name + '</option>';
          }
          return_form +='           </select>';
          return_form +='         </div>';
          return_form +='       </div>';
          if (i < return_order_product_ids.length - 1) {
            return_form +='   <a data-toggle="collapse" data-parent="#accordion" class=" buttons-sp pull-right" href="#collapse'+(i+1)+'">Next &nbsp;&nbsp;<i class="fa fa-arrow-right"></i></a>';
          }
          if (i <= return_order_product_ids.length && i > 0) {
            return_form +='   <a data-toggle="collapse" class="btn buttons-sp btn-primary pull-left" data-parent="#accordion" href="#collapse'+(i-1)+'"><i class="fa fa-arrow-left"></i>&nbsp;&nbsp; Previous</a>';
          }
          return_form +='       </div>';
          return_form +='     </div>';
          return_form +='   </div>';
        }
      }
    });

    return_form +='   </div>'; //Accordion ended

    return_form +='     <div class="form-group">';
    return_form +='         <label class="col-sm-2 control-label" for="input-comment"> '+entry_fault_detail+' </label>';
    return_form +='       <div class="col-sm-10">';
    return_form +='         <textarea name="comment" rows="8" placeholder="" id="input-comment" class="form-control"></textarea>';
    return_form +='         <input type="hidden" name="pos_user_id" value="'+user_login+'"/>';
    return_form +='         <input type="hidden" name="return_order_id" value="'+return_order_id+'"/>';
    return_form +='       </div>';
    return_form +='     </div>';

    return_form +='    </form>';
    return_form += '     <div class="modal-footer">';
    return_form += '       <button type="button" class="btn buttons-sp btn-warning pull-left close-it" onclick="$("#returns").html("");">' + button_close + '</button>'
    return_form += '       <button type="button" class="btn buttons-sp btn-primary" id="button-return-product" onclick="returnProducts();">' + button_submit + '</button>'
    return_form += '</div>';
    $('.return-form-parent').addClass('panel-show');
    $('#return-form').html(return_form);
  }
});

function returnProducts(){
  var order_data = {
      'firstname': $('#cfirstname').val(),
      'lastname': $('#clastname').val(),
      'email': $('#cemail').val(),
      'telphone': $('#ctel').val(),
  };
  $.ajax({
    url: 'index.php?route=wkpos/return/addReturn',
    type: 'post',
    dataType: 'json',
    data: $('#return-product-form').serialize()+ '&' + $.param(order_data),
    beforeSend: function () {
      $('#button-return-product').button('loading');
    },
    success:function(json){
      if (json['error']) {
        $.each(json['error'], function(i, error){
          if (typeof json['error'][i]['quantity'] != 'undefined') {
            $.toaster({
              priority: 'danger',
              message: json['error'][i]['quantity'],
              timeout: 4000
            });
          }

          if (typeof json['error'][i]['opened'] != 'undefined') {
            $.toaster({
              priority: 'danger',
              message: json['error'][i]['opened'],
              timeout: 6000
            });
          }

          if (typeof json['error'][i]['reason'] != 'undefined') {
            $.toaster({
              priority: 'danger',
              message: json['error'][i]['reason'],
              timeout: 8000
            });
          }

          if (typeof json['error'][i]['action'] != 'undefined') {
            $.toaster({
              priority: 'danger',
              message: json['error'][i]['action'],
              timeout: 8000
            });
          }

        });
        if (typeof json['error']['product'] != 'undefined') {
          $.toaster({
            priority: 'danger',
            message: json['error']['product'],
            timeout: 4000
          });
        }
        if (json['error'] != '') {
          return false;
        }
      }
      if(typeof json['success'] != 'undefined') { // json error ended
        return_order_product_ids = [];
        $.toaster({
          priority: 'success',
          message: json['success'],
          timeout: 4000
        });
        $('.close-it').click();
        $('#search-order-id').val('');
        $('#search-order-result').html('');
        setTimeout(function() {
            $('.sidepanel').removeClass('sidepanel-show').addClass('sidepanel-hide');
        }, 1000);
      }
    },
    complete: function () {
      $('#button-return-product').button('reset');
      if (screen.width < 768) {
        $('#show-cart').click();
      }
    },
  });
}
$(document).on('click', '.return-display', function(){
  $('#return-details').html('');
  var return_id = $(this).attr('return-id');
  $('.order-txn').text(text_return_id);
  $('.oid').text(return_id);
  var return_detail_html = '';
  $('.order-loader').removeClass('hide');
  $.each(pos_returns, function(index, pos_return){
    if (pos_return['return_id'] == return_id) {
      return_detail_html += '<div class="table">';
      return_detail_html += ' <div class="col-xs-12">';
      return_detail_html += '   <table class="table-hover table-striped">';
      return_detail_html += '    <tbody>';
      return_detail_html += '       <tr><td>'+ entry_customer +':</td><td>'+ pos_return['name'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ entry_email +':</td><td>'+ pos_return['email'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ entry_telephone +':</td><td>'+ pos_return['telephone'] +'</td></tr>';
      return_detail_html += '     </tbody>';

      return_detail_html += '     <tbody>';
      return_detail_html += '       <tr style="border-bottom: 1px solid #000;"><td colspan="2"></td></tr>';
      return_detail_html += '       <tr><td>'+ text_order_id +':</td><td>#'+ pos_return['order_id'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ text_return_date +':</td><td>'+ pos_return['date_added'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ text_order_date +':</td><td>'+ pos_return['date_ordered'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ entry_reason +':</td><td>'+ pos_return['return_reason'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ text_return_action +':</td><td>'+ pos_return['return_action'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ text_return_status +':</td><td>'+ pos_return['return_status'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ text_comment +':</td><td>'+ pos_return['comment'] +'</td></tr>';
      return_detail_html += '     </tbody>';

      return_detail_html += '     <tbody>';
      return_detail_html += '       <tr style="border-bottom: 1px solid #000;"><td colspan="2"></td></tr>';
      return_detail_html += '       <tr><td colspan="2"><h4>'+ text_product_detail +'</h4></td></tr>';
      return_detail_html += '       <tr><td>'+ entry_name +':</td><td>'+ pos_return['product'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ entry_model +':</td><td>'+ pos_return['model'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ entry_quantity +':</td><td>'+ pos_return['quantity'] +'</td></tr>';
      return_detail_html += '       <tr><td>'+ entry_opened1 +':</td><td>'+ pos_return['product_opened'] +'</td></tr>';
      return_detail_html += '     </tbody>';
      return_detail_html += '  </table>';
      return_detail_html += ' </div>';
      return_detail_html += '</div>';
    }
  });
  setTimeout(function(){
    $('.order-loader').addClass('hide');
    $('#return-details').html(return_detail_html).removeClass('hide');
  }, 600);
  if ($(window).width() < 993) {
    $.toaster({
      priority: 'danger',
      message: error_mobile_view_return,
      timeout: 5000
    });
  }
});
$(document).on('click', '.check-all', function() {
  return_order_product_ids = [];
  $('.return-checkbox').prop('checked', true);
  return_order_product_ids = $('.return-checkbox').map(function() {
    return this.value;
  }).get();

});

$(document).on('click', '.uncheck-all', function() {
  $('.return-checkbox').prop('checked', false );
  return_order_product_ids = [];
});
$(document).on('keyup', '#delivery-charge', function() {
    if ($('#checkbox-home-delivery').is(":checked") && $(this).val() > 0) {
      if (parseFloat($(this).val()) > Number(delivery_max)) {
        $.toaster({
          priority: 'warning',
          message: error_delivery_max.replace('_charge', delivery_max),
          timeout: 5000
        });
        delivery_charge = 100;
        return false
      } else {
        delivery_charge = parseFloat($(this).val());
      }
    } else {
      delivery_charge = 0;
    }
    printCart();
});

$(document).on('change', '#checkbox-home-delivery', function() {
  shipping_method = 'home_delivery';
  if ($(this).is(':checked') && $('#delivery-charge').val() > 0) {
    if (parseFloat($('#delivery-charge').val()) > Number(delivery_max)) {
      $.toaster({
        priority: 'warning',
        message: error_delivery_max.replace('_charge', delivery_max),
        timeout: 5000
      });
      delivery_charge = 100;
      return false
    } else {
      delivery_charge = parseFloat($('#delivery-charge').val());
    }
  } else {
    delivery_charge = 0;
  }
  printCart();
});

$(document).on('click', 'button[id^=\'button-upload\']', function() {
	var node = this;
	$('#form-upload').remove();
  if (!navigator.onLine) {
    $.toaster({
      priority: 'warning',
      message: error_offline_file,
      timeout: 3000
    });
    return false;
  }
	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');

	$('#form-upload input[name=\'file\']').trigger('click');

	if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}

	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);
      var fullPath = $('#form-upload input[name=\'file\']').val();
      var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
        var filename = fullPath.substring(startIndex);
        if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
            filename = filename.substring(1);
        }
			$.ajax({
				url: 'index.php?route=tool/upload',
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$(node).button('loading');
				},
				complete: function() {
					$(node).button('reset');
				},
				success: function(json) {
					$('.text-danger').remove();
					if (json['error']) {
						$(node).parent().find('input').after('<div class="text-danger">' + json['error'] + '</div>');
					}
					if (json['success']) {
            getLocalForage('option_files');
						alert(json['success']);
            option_files[json['code']] = filename;
            localforage.setItem('option_files', JSON.stringify(option_files));
						$(node).parent().find('input').val(filename);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});
function changePrice(customer_id) {
  $.ajax({
    url: 'index.php?route=wkpos/product&start=' + start,
    dataType: 'json',
    type: 'post',
    data: {user_id: user_login, customer_id: customer_id},
    beforeSend: function () {
      $('#loader').css('display', 'block');
      $('#loading-text').text(text_loading_products);
    },
    success: function (json) {
      total_product_count = json['total_products'];
      start += json['count'];
      var width = 20 + ((start * 20) / total_product_count);
      $('.progress-bar').css('width', width + '%');
      $.each(json['products'], function (key, value) {
        pos_products[key] = value;
        for (var _index in pos_cart[current_cart]) {
          if (key == pos_cart[current_cart][_index]['product_id']) {
            var _price_uf = Number(value['price_uf']);
            if (value['options']) {
              for (var _opt_index in value['options']) {
                for (var _cart_opt_index in pos_cart[current_cart][_index]['options']) {
                  if (value['options'][_opt_index]['product_option_value'] && value['options'][_opt_index]['product_option_id'] == pos_cart[current_cart][_index]['options'][_cart_opt_index]['product_option_id']) {
                    for (var _product_option_value in value['options'][_opt_index]['product_option_value']) {
                      if (pos_cart[current_cart][_index]['options'][_cart_opt_index]['value'] && pos_cart[current_cart][_index]['options'][_cart_opt_index]['value'].constructor == Array) {
                        for (var cart_option_val_index in pos_cart[current_cart][_index]['options'][_cart_opt_index]['value']) {
                          if (value['options'][_opt_index]['product_option_value'][_product_option_value]['product_option_value_id'] == pos_cart[current_cart][_index]['options'][_cart_opt_index]['value'][cart_option_val_index]) {
                            _price_uf += Number(value['options'][_opt_index]['product_option_value'][_product_option_value]['uf']);
                          }
                        }
                      } else if (value['options'][_opt_index]['product_option_value'][_product_option_value]['product_option_value_id'] == pos_cart[current_cart][_index]['options'][_cart_opt_index]['value']) {
                        _price_uf += Number(value['options'][_opt_index]['product_option_value'][_product_option_value]['uf']);
                      }
                    }
                  }
                }
              }
            }
            pos_cart[current_cart][_index]['price'] = formatPrice(_price_uf);
            pos_cart[current_cart][_index]['uf'] = Number(_price_uf);
            pos_cart[current_cart][_index]['uf_total'] = Number(_price_uf) * Number(pos_cart[current_cart][_index]['quantity']);
            pos_cart[current_cart][_index]['total'] = formatPrice(Number(_price_uf) * Number(pos_cart[current_cart][_index]['quantity']));
          }
        }
      });
      if (start >= total_product_count) {
        no_image = json['no_image'];
        $('#loader').css('display', 'none');
        printCart();
        printProducts();
      } else {
        changePrice(customer_id)
      }
    },
    error: function () {
      $('#loader').css('display', 'none');
      if (localforage && localforage.getItem('pos_products')) {
        getLocalForage('pos_products');
        total_product_count = Object.keys(pos_products).length;
        printProducts();
        printCart();
        $('.progress-bar').addClass('progress-bar-danger').css('width', '40%');
        $('#error-text').append('<br>' + error_load_products + '<br>');
      };
      getAllCategories();
    }
  });
}
$(document).on('click', '#button-payment', function(){
    getLocalForage('pos_products');
});

function getLocalForage(key) {
  localforage.getItem(key, function(err, value) {
    if (value && value !== undefined && value.length) {
      if (key == 'pos_products') {
        pos_products = JSON.parse(value);
      }
      if (key == 'pos_orders') {
        pos_orders = JSON.parse(value);
      }
      if (key == 'pos_returns') {
        pos_returns = JSON.parse(value);
      }
      if (key == 'pos_taxes') {
        pos_taxes = JSON.parse(value);
      }
      if (key == 'pos_cart') {
        pos_cart = JSON.parse(value);
      }
      if (key == 'current_cart') {
        current_cart = value;
      }
      if (key == 'pos_holds') {
        pos_holds = JSON.parse(value);
      }
      if (key == 'pos_remove_id') {
        pos_remove_id = value;
      }
    }
  });
}
$(document).on('click', '#remove-credit ', function() {
    use_credit = false;
    $('#balance-credit input:first').val('');
    $('#button-credit').click();
    $.toaster({
    priority: 'success',
    message: text_credit_removed,
    timeout: 5000
  });
  $(this).remove();
})
$(document).on('click', '#button-credit', function() {
  if (offline) {
    $.toaster({
      priority: 'warning',
      message: error_credit_offline,
      timeout: 5000
    });
    return;
  }
  var customer;
  for (var c in customers) {
  	if (customers[c]['customer_id'] == customer_id) {
  		customer = customers[c];
  	}
  }
  credit_amount = $('#balance-credit input:first').val() ? parseFloat($('#balance-credit input:first').val()) : 0;
  if (credit_amount > customer['credit_uf']) {
    $.toaster({
      priority: 'warning',
      message: error_credit_amount.replace('_amount', customer['credit_f']),
      timeout: 5000
    });
    credit_amount = 0;
    return;
  }
  var temp_current_total = current_total;
  var tendered_amount = $('#amount-tendered').val() ? parseFloat($('#amount-tendered').val()) : 0;
  var change = credit_amount - (temp_current_total - uf_total_discount - coupon_disc + delivery_charge - tendered_amount);
  if (change > 0) {
    credit_amount -= change;
    if (credit_amount < 0) {
      credit_amount = 0;
    }
    $('#balance-credit input:first').val(credit_amount);
  }
  $('#remove-credit').remove();
  $('.text-danger').remove();
  if (credit_amount > 0) {
    $(this).parent().parent().append('<button id="remove-credit" style="margin:4px;" class="btn btn-sm btn-danger pull-right">' + button_remove_credit +'</button>');
    $.toaster({
      priority: 'success',
      message: text_credit_applied.replace('_credit', formatPrice(credit_amount)),
      timeout: 5000
    });
  }
  if (symbol_position == 'L') {
    var reset_change = currency_code + '0.00';
    var balance_due = currency_code + Math.abs(parseFloat(change).toFixed(2));
  } else {
    var reset_change = '0.00' + currency_code;
    var balance_due = Math.abs(parseFloat(change).toFixed(2)) + currency_code;
  }
  if (change < 0) {
    $('#change').text(reset_change);
    $('#balance-due').after('<span class="text-danger">'+ text_balance_due + ' ' + balance_due + '</span>');
  }

});
$(document).on('keypress', '#balance-credit input:first', function(event) {
  if (event.which == 13) {
    $('#button-credit').click();
  }
});
$(document).ajaxSuccess(function(event, response, setting){
	if (setting.url == 'index.php?route=wkpos/return/addReturn') {
    getAllReturns();
  }
});

function detectmob() {
 if(navigator.userAgent.match(/Android/i)
 || navigator.userAgent.match(/webOS/i)
 || navigator.userAgent.match(/iPhone/i)
 || navigator.userAgent.match(/iPad/i)
 || navigator.userAgent.match(/iPod/i)
 || navigator.userAgent.match(/BlackBerry/i)
 || navigator.userAgent.match(/Windows Phone/i)
 ){
    return true;
 } else {
    return false;
 }
}
