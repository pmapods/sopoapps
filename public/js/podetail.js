
var temp_ba_file = null;
var temp_ba_extension = null;

var temp_nonbudget_olditem_file = null;
var temp_nonbudget_olditem_extension = null;

var temp_olditem_file = null;
var temp_olditem_extension = null;

$(document).ready(function () {
    // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
    tableCustomerRefreshed();
    $('.requirement_date').prop('min', moment().format('YYYY-MM-DD'));
    $('.requirement_date').trigger('change');

    $('.salespoint_select2').on('change', function () {
        let salespoint_select = $('.salespoint_select2');
        let authorization_select = $('.authorization_select2');
        let loading = $('.loading_salespoint_select2');
        let request_type = $('.request_type').val();

        authorization_select.prop('disabled', true);
        authorization_select.find('option').remove();
        var empty = new Option('-- Pilih Otorisasi --', "", false, true);
        authorization_select.append(empty);
        authorization_select.trigger('change');

        if (salespoint_select.val() == "") {
            return;
        }

        loading.show();
        $.ajax({
            type: "get",
            url: "/getsalespointauthorization",
            data: {
                salespoint: salespoint_select.val(),
                request_type: request_type
            },
            success: function (response) {
                let data = response.data;
                data.forEach(item => {
                    let option_text = "";
                    let names = [];
                    item.detail.forEach(detail => {
                        names.push(detail.name);
                    });
                    option_text += names.join(" -- ");
                    if (item.notes) {
                        option_text += " (" + item.notes + ")";
                    }
                    var newOption = new Option(option_text, item.id, false, true);
                    authorization_select.append(newOption);
                    authorization_select.find('option:selected').data('item', item);
                });
                authorization_select.val("");
                authorization_select.trigger('change');
                authorization_select.prop('disabled', false);
            },
            error: function (response) {
                alert('load data failed. Please refresh browser or contact admin')
            },
            complete: function () {
                loading.hide();
            }
        });
    });

    $('.select_item').change(function () {
        let select_item = $('.select_item');
        let price_item = $('.price_item');
        let price = select_item.find('option:selected').data('hargasewa');

        price_item.val(setRupiah(price));
    });

    $('.requirement_date').change(function () {
        let start_date = $('.requirement_date').val();

        $('.requirement_enddate').prop('min', moment(start_date).add(1, 'days').format('YYYY-MM-DD'));
        $('.requirement_enddate').trigger('change');
    });

    $('.requirement_enddate').change(function () {
        let start_date = $('.requirement_date').val();
        let end_date = $('.requirement_enddate').val();
        let count_item = $('.count_item');

        let diff_date = moment(end_date).diff(moment(start_date), 'days')

        if (diff_date == 0) {
            diff_date = diff_date + 1;
        }

        count_item.val(diff_date);
    });

    $('input[type=checkbox][name=is_dp]').click(function () {
        let select_dp = $('input[type=checkbox][name=is_dp]').is(':checked');
        let tot_price = $('.tot_price');
        let isit_dp = $('.isit_dp');
        let isit_dp_history = $('.isit_dp_history');
        let table_item = $('.table_item');
        let row_count = 0;

        table_item.find('tbody:eq(0) tr').not('.empty_row').each(function (index, el) {
            row_count++;
        });

        let tot_price2 = tot_price.val();

        if (row_count == 0) {
            $('input[type=checkbox][name=is_dp]').prop('checked', false);
            $('input[type=checkbox][name=is_dp]').trigger('change');
            alert("Silahkan pilih product terlebih dahulu");
            return;
        }

        if (select_dp) {
            isit_dp.val("");
            isit_dp_history.val("");
            $('.isit_dp').prop('disabled', false);
            $('.isit_dp').trigger('change');
        }
        else {
            isit_dp.val(tot_price2);
            isit_dp_history.val(tot_price2);
            $('.isit_dp').prop('disabled', true);
            $('.isit_dp').trigger('change');
        }

    });

    $('input[type=checkbox][name=is_disc]').click(function () {
        let select_dp = $('input[type=checkbox][name=is_disc]').is(':checked');
        let table_item = $('.table_item');
        let row_count = 0;
        let tot_price = $('.tot_price');
        let tot_price_history = $('.tot_price_history');
        let isit_dp = $('.isit_dp');
        let isit_dp_history = $('.isit_dp_history');
        let isit_disc = $('.isit_disc');

        table_item.find('tbody:eq(0) tr').not('.empty_row').each(function () {
            row_count++;
        });

        if (row_count == 0) {
            alert("Silahkan add product terlebih dahulu");
            $('input[type=checkbox][name=is_disc]').prop('checked', false);
            $('input[type=checkbox][name=is_disc]').trigger('change');
            return;
        }

        if (select_dp) {
            $('.isit_disc').prop('disabled', false);
            $('.isit_disc').trigger('change');
        }
        else {
            $('.isit_disc').prop('disabled', true);
            $('.isit_disc').trigger('change');

            isit_disc.val(0);
            tot_price.val(setRupiah(tot_price_history.val()));
            isit_dp.val(setRupiah(tot_price_history.val()));
            isit_dp_history.val(tot_price_history.val());
        }
    });

    $('.isit_disc').change(function () {
        let tot_price = $('.tot_price');
        let tot_price_history = $('.tot_price_history');
        let tot_price_history2 = $('.tot_price_history2');
        let isit_disc = $('.isit_disc');
        // let select_dp = $('.cara_bayar');
        let isit_dp = $('.isit_dp');
        let isit_dp_history = $('.isit_dp_history');

        let total_prc_bfr = tot_price_history.val();
        let total_disc = isit_disc.val();

        total_prc_disc = total_prc_bfr * (total_disc / 100);
        total_prc = total_prc_bfr - total_prc_disc;

        isit_dp.val(setRupiah(total_prc));
        isit_dp_history.val(total_prc);
        tot_price.val(setRupiah(total_prc));
        tot_price_history2.val(total_prc);
    });

    $('.count_qty').change(function () {
        let select_item = $('.select_item');
        let count_qty = $('.count_qty');
        let count_item = $('.count_item');
        let subtot_price = $('.subtot_price');
        let price = select_item.find('option:selected').data('hargasewa');

        let start_date = $('.requirement_date').val();
        let end_date = $('.requirement_enddate').val();
        let salespoint = $('.salespoint_select2').val();
        let approval = $('.authorization_select2').val();

        if (start_date == "" || end_date == "") {
            alert("Silahkan pilih tanggal awal sewa dan tanggal akhir sewa terlebih dahulu");
            count_qty.val("");
            return;
        }
        if (salespoint == "" || approval == "") {
            alert("Silahkan pilih salespoint dan matrix approval terlebih dahulu");
            count_qty.val("");
            return;
        }
        if (select_item.val() == "") {
            alert("Silahkan pilih product terlebih dahulu");
            count_qty.val("");
            return;
        }

        let count1 = count_qty.val();
        let count2 = count_item.val();

        subtot_price.val(setRupiah(count1 * count2 * price));
    });

    $('.authorization_select2').on('change', function () {
        let field = $('.authorization_list_field');
        // initial state
        field.empty();
        let selected_data = $(this).find('option:selected').data('item');
        if (selected_data) {
            selected_data.detail.forEach(item => {
                field.append('<div class="mb-3"><span class="font-weight-bold">' + item.name + ' -- ' + item.position + '</span><br><span>' + item.as + '</span></div>');
            });
        } else {
            field.append('<div>Belum memilih otorisasi</div>');
        }
    });
});

function addItem(el) {
    let select_item = $('.select_item');
    let count_item = $('.count_item');
    let count_qty = $('.count_qty');
    let price_item = $('.price_item');
    let table_item = $('.table_item');
    let subtot_price = $('.subtot_price');

    let id = select_item.find('option:selected').val();
    let name = select_item.find('option:selected').text().trim();
    let price = select_item.find('option:selected').data('hargasewa');
    let code = select_item.find('option:selected').data('code');

    // let price_text = price_item.domElement.value;
    let count = count_item.val();
    let countqty = count_qty.val();

    if (id == "") {
        alert("Item harus dipilih");
        return;
    }

    $('.requirement_date').prop('disabled', true);
    $('.requirement_date').trigger('change');
    $('.requirement_enddate').prop('disabled', true);
    $('.requirement_enddate').trigger('change');

    // tbody eq(0) supaya ga nyasar ke table other attachment
    table_item.find('tbody:eq(0)').append('<tr class="item_list" data-id="' + id + '" data-code="' + code + '" data-name="' + name + '" data-price="' + price + '" data-count="' + count + '" data-countqty="' + countqty + '" data-subtotal="' + (count * countqty * price) + '"><td>' + name + '</td><td>' + count + '</td><td>' + countqty + '</td><td>' + setRupiah(price) + '</td><td>' + setRupiah(count * countqty * price) + '</td><td><i class="fa fa-trash text-danger remove_list mr-3" onclick="removeList(this)" aria-hidden="true"></i></td></tr>');

    select_item.val("");
    select_item.trigger('change');
    price_item.val(setRupiah(1));
    count_item.val(count);
    count_qty.val("");
    subtot_price.val(setRupiah(1));
    tableRefreshed(el);
}

// remove button
function removeList(el) {
    let tr = $(el).closest('tr');
    tr.remove();
    tableRefreshed();
}

// table on refresh
function tableRefreshed() {
    let table_item = $('.table_item');
    let salespoint_select = $('.salespoint_select2');
    let tot_price = $('.tot_price');
    let tot_price_history = $('.tot_price_history');
    let select_dp = $('input[type=checkbox][name=is_dp]').is(':checked');
    let isit_dp = $('.isit_dp');
    let isit_dp_history = $('.isit_dp_history');

    // check table level if table has data / tr or not
    let row_count = 0;
    let total_price = 0;
    table_item.find('tbody:eq(0) tr').not('.empty_row').each(function (index, el) {
        row_count++;
        total_price += $(el).data('subtotal');
    });

    tot_price.val(setRupiah(total_price));
    tot_price_history.val(total_price);

    if (select_dp) {
        isit_dp.val("");
        isit_dp_history.val("");
        $('.isit_dp').prop('disabled', false);
        $('.isit_dp').trigger('change');
    }
    else {
        isit_dp.val(total_price);
        isit_dp_history.val(total_price);
        $('.isit_dp').prop('disabled', true);
        $('.isit_dp').trigger('change');
    }

    if (row_count > 0) {
        salespoint_select.prop('disabled', true);
        table_item.find('.empty_row').remove();
    } else {
        salespoint_select.prop('disabled', false);
        table_item.find('tbody:eq(0)').append('<tr class="empty_row text-center"><td colspan="8">Item belum dipilih</td></tr>');
    }
}

function tableCustomerRefreshed(current_element) {
    let table_vendor = $('.table_vendor');

    let row_count = 0;
    table_vendor.find('tbody tr').not('.empty_row').each(function () {
        row_count++;
    });
    if (row_count > 0) {
        table_vendor.find('.empty_row').remove();
    } else {
        table_vendor.find('tbody').append('<tr class="empty_row text-center"><td colspan="6">Vendor belum dipilih</td></tr>');
    }
    if ($('.vendor_item_list').length < 2) {
        $('.vendor_ba_field').show();
    } else {
        $('.vendor_ba_field').hide();
    }
}

// add vendor
function addCustomer(el) {
    let select_customer = $('.select_customer');
    let table_customer = $('.table_customer');
    let id = select_customer.find('option:selected').val();
    let id_cust = select_customer.find('option:selected').data('id');
    let name = select_customer.find('option:selected').data('name');
    let code = select_customer.find('option:selected').data('code');
    let salesperson = select_customer.find('option:selected').data('salesperson');
    let type = select_customer.find('option:selected').data('type');
    if (select_customer.val() == "") {
        alert('Harap pilih customer terlebih dulu');
        return;
    }

    let newSalesperson = salesperson.filter((position) => {
        return position.position == 103
    });

    let nameManager = "";
    let emailManager = "";
    let phoneManager = "";

    newSalesperson.forEach(e => {
        nameManager = e.name;
        emailManager = e.email;
        phoneManager = e.phone;
    });


    table_customer.find('tbody').append('<tr class="customer_list" data-id="' + id + '" data-customer_id="' + id_cust + '" data-customer_code="' + code + '" data-customer_name="' + name + '" data-customer_namemanager="' + nameManager + '" data-customer_emailmanager="' + emailManager + '" data-customer_phonemanager="' + phoneManager + '"><td>' + code + '</td><td>' + name + '</td><td>' + nameManager + '</td><td>' + emailManager + '</td><td>' + phoneManager + '</td><td>' + type + '</td><td><i class="fa fa-trash text-danger" onclick="removeCustomer(this)" aria-hidden="true"></i></td></tr>');
    select_customer.val('');
    select_customer.trigger('change');
    tableCustomerRefreshed(el);
}

// remove vendor
function removeCustomer(el) {
    let tr = $(el).closest('tr');
    tr.remove();
    tableCustomerRefreshed();
}

function addRequest(type) {
    let item_list = $('.item_list');
    let customer_list = $('.customer_list');
    let select_dp = $('input[type=checkbox][name=is_dp]').is(':checked');
    let select_disc = $('input[type=checkbox][name=is_disc]').is(':checked');

    let is_dp = 0;
    let is_disc = 0;

    if (select_dp) {
        is_dp = 1;
    }
    if (select_disc) {
        is_disc = 1;
    }

    let input_field = $('#input_field');
    input_field.empty();


    // 0 save to draft
    // 1 start authorization
    input_field.append('<input type="hidden" name="type" value="' + type + '">')
    input_field.append('<input type="hidden" name="requirement_date" value="' + $('.requirement_date').val() + '">');
    input_field.append('<input type="hidden" name="requirement_enddate" value="' + $('.requirement_enddate').val() + '">');
    input_field.append('<input type="hidden" name="salespoint" value="' + $('.salespoint_select2').val() + '">');
    input_field.append('<input type="hidden" name="authorization" value="' + $('.authorization_select2').val() + '">');
    input_field.append('<input type="hidden" name="request_type" value="' + $('.request_type').val() + '">');

    input_field.append('<input type="hidden" name="is_dp" value="' + is_dp + '">');
    input_field.append('<input type="hidden" name="isit_dp_history" value="' + parseInt($('.isit_dp_history').val()) + '">');
    input_field.append('<input type="hidden" name="is_disc" value="' + is_disc + '">');
    input_field.append('<input type="hidden" name="isit_disc" value="' + $('.isit_disc').val() + '">');
    input_field.append('<input type="hidden" name="tot_price_history2" value="' + parseInt($('.tot_price_history2').val()) + '">');

    item_list.each(function (index, el) {
        input_field.append('<input type="hidden" name="item[' + index + '][id]" value="' + $(el).data('id') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][code]" value="' + $(el).data('code') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][name]" value="' + $(el).data('name') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][price]" value="' + $(el).data('price') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][count]" value="' + $(el).data('count') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][countqty]" value="' + $(el).data('countqty') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][subtotal]" value="' + $(el).data('subtotal') + '">');
    });

    customer_list.each(function (index, el) {
        input_field.append('<input type="hidden" name="customer[' + index + '][id]" value="' + $(el).data('id') + '">');
        input_field.append('<input type="hidden" name="customer[' + index + '][customer_id]" value="' + $(el).data('customer_id') + '">');
        input_field.append('<input type="hidden" name="customer[' + index + '][customer_code]" value="' + $(el).data('customer_code') + '">');
        input_field.append('<input type="hidden" name="customer[' + index + '][customer_name]" value="' + $(el).data('customer_name') + '">');
        input_field.append('<input type="hidden" name="customer[' + index + '][customer_nameManager]" value="' + $(el).data('customer_namemanager') + '">');
        input_field.append('<input type="hidden" name="customer[' + index + '][customer_emailManager]" value="' + $(el).data('customer_emailmanager') + '">');
        input_field.append('<input type="hidden" name="customer[' + index + '][customer_phoneManager]" value="' + $(el).data('customer_phonemanager') + '">');
    });

    $('#addform').submit();
}

function approve() {
    $('#approveform').submit();
}

function reject() {
    var reason = prompt("Harap memasukan alasan penolakan");
    if (reason != null) {
        if (reason.trim() == '') {
            alert("Alasan Harus diisi");
            return;
        }
        $('#rejectform .input_field').append('<input type="hidden" name="reason" value="' + reason + '">');
        $('#rejectform').submit();
    }
}

