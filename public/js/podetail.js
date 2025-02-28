
var temp_ba_file = null;
var temp_ba_extension = null;

var temp_nonbudget_olditem_file = null;
var temp_nonbudget_olditem_extension = null;

var temp_olditem_file = null;
var temp_olditem_extension = null;

$(document).ready(function () {
    // set minimal tanggal pengadaan 14 setelah tanggal pengajuan
    tableVendorRefreshed();
    $('.requirement_date').val(moment().add(14, 'days').format('YYYY-MM-DD'));
    $('.requirement_date').prop('min', moment().add(14, 'days').format('YYYY-MM-DD'));
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

    $('.count_item').change(function () {
        let select_item = $('.select_item');
        let count_item = $('.count_item');
        let subtot_price = $('.subtot_price');
        let price = select_item.find('option:selected').data('hargasewa');

        let count = count_item.val();

        subtot_price.val(setRupiah(count * price));
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
    let price_item = $('.price_item');
    let table_item = $('.table_item');
    let subtot_price = $('.subtot_price');

    let id = select_item.find('option:selected').val();
    let name = select_item.find('option:selected').text().trim();
    let price = select_item.find('option:selected').data('hargasewa');

    // let price_text = price_item.domElement.value;
    let count = count_item.val();

    if (id == "") {
        alert("Item harus dipilih");
        return;
    }
    if (count < 1) {
        alert("Jumlah Item minimal 1");
        return;
    }

    // tbody eq(0) supaya ga nyasar ke table other attachment
    table_item.find('tbody:eq(0)').append('<tr class="item_list" data-id="' + id + '" data-name="' + name + '" data-price="' + price + '" data-count="' + count + '"><td>' + name + '</td><td>' + count + '</td><td>' + setRupiah(price) + '</td><td>' + setRupiah(count * price) + '</td><td><i class="fa fa-trash text-danger remove_list mr-3" onclick="removeList(this)" aria-hidden="true"></i></td></tr>');

    select_item.val("");
    select_item.trigger('change');
    price_item.val(setRupiah(1));
    count_item.val("");
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
    // let authorization_select = $('.authorization_select2');

    // check table level if table has data / tr or not
    let row_count = 0;
    table_item.find('tbody:eq(0) tr').not('.empty_row').each(function () {
        row_count++;
    });
    if (row_count > 0) {
        salespoint_select.prop('disabled', true);
        table_item.find('.empty_row').remove();
    } else {
        salespoint_select.prop('disabled', false);
        table_item.find('tbody:eq(0)').append('<tr class="empty_row text-center"><td colspan="8">Item belum dipilih</td></tr>');
    }
}

// add vendor
function addCustomer(el) {
    let select_customer = $('.select_customer');
    let table_customer = $('.table_customer');
    let id = select_customer.find('option:selected').data('id');
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


    table_customer.find('tbody').append('<tr class="customer_list" data-customer_id="' + id + '"><td>' + code + '</td><td>' + name + '</td><td>' + nameManager + '</td><td>' + emailManager + '</td><td>' + phoneManager + '</td><td>' + type + '</td><td>Terdaftar</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>');
    select_customer.val('');
    select_customer.trigger('change');
    tableVendorRefreshed(select_customer);
}

function addOTVendor(el) {
    let vendor_name = $('.ot_vendor_name');
    let vendor_sales = $('.ot_vendor_sales');
    let vendor_phone = $('.ot_vendor_phone');
    let table_vendor = $('.table_vendor');
    if (vendor_name.val() == "") {
        alert('Nama Vendor tidak boleh kosong');
        return;
    }
    if (vendor_sales.val() == "") {
        alert('Sales Vendor tidak boleh kosong');
        return;
    }
    if (vendor_phone.val() == "") {
        alert('Telfon Vendor tidak boleh kosong');
        return;
    }
    if ($('.vendor_item_list').length > 1) {
        alert('Maksimal 2 vendor');
        return;
    }
    table_vendor.find('tbody').append('<tr class="vendor_item_list" data-name="' + vendor_name.val() + '" data-sales="' + vendor_sales.val() + '" data-phone="' + vendor_phone.val() + '"><td>-</td><td>' + vendor_name.val() + '</td><td>' + vendor_sales.val() + '</td><td>' + vendor_phone.val() + '</td><td>One Time</td><td><i class="fa fa-trash text-danger" onclick="removeVendor(this)" aria-hidden="true"></i></td></tr>'
    );
    vendor_name.val('');
    vendor_sales.val('');
    vendor_phone.val('');
    tableVendorRefreshed(vendor_name);
}

// remove vendor
function removeVendor(el) {
    let tr = $(el).closest('tr');
    tr.remove();
    tableVendorRefreshed();
}

// table on refresh
function tableVendorRefreshed(current_element) {
    let table_vendor = $('.table_vendor');

    let row_count = 0;
    table_vendor.find('tbody tr').not('.empty_row').each(function () {
        row_count++;
    });
    if (row_count > 0) {
        table_vendor.find('.empty_row').remove();
    } else {
        table_vendor.find('tbody').append('<tr class="empty_row text-center"><td colspan="7">Vendor belum dipilih</td></tr>');
    }
    if ($('.vendor_item_list').length < 2) {
        $('.vendor_ba_field').show();
    } else {
        $('.vendor_ba_field').hide();
    }
}

function overBudget() {
    $('#overBudgetModal').modal('show');

    hideMatriksApprovalOverBudget();
}

function notifOverBudget() {
    $('#notifOverBudgetModal').modal('show');
}

function overBudgetPopUp() {
    $('#modalOverBudgetCountainer').children().not(':first').remove();

    let is_over_budget = 0;

    $('.item_list').each(function () {
        let total = $(this).data("price");
        let over_budget = $(this).data("max");
        let selisih = over_budget - total;
        let nama_barang = $(this).data("name");

        if (total > over_budget) {
            let html = $('#template_over_budget').clone();
            $('#modalOverBudgetCountainer').append(html.show());
            html.find('.nama_barang').val(nama_barang)
            html.find('.nilai_ajuan').val((Number.isInteger(total)) ? setRupiah(total) : '-')
            html.find('.selisih').val((Number.isInteger(selisih)) ? setRupiah(selisih) : '-')
            html.find('.nilai_budget').val(Number.isInteger(over_budget) ? setRupiah(over_budget) : '-')

            is_over_budget++;

            notifOverBudget();
        }
    });

    console.log(is_over_budget);

    return is_over_budget;
}

function hideMatriksApprovalOverBudget() {
    let salespoint_over_budget = $('.salespoint_select2').val();

    if (salespoint_over_budget != 251 || salespoint_over_budget != 252) {
        $('#approval_over_budget_area').show();
    } else {
        $('#approval_over_budget_area').hide();
    }
}

function addRequest(type) {
    let hasil = overBudgetPopUp();

    if (type == 3) {
        type = 1;
    } else if (hasil > 0) {
        return false;
    }

    let item_list = $('.item_list');
    let vendor_item_list = $('.vendor_item_list');

    let input_field = $('#input_field');
    input_field.empty();

    // 0 save to draft
    // 1 start authorization
    input_field.append('<input type="hidden" name="type" value="' + type + '">')
    input_field.append('<input type="hidden" name="requirement_date" value="' + $('.requirement_date').val() + '">');
    input_field.append('<input type="hidden" name="salespoint" value="' + $('.salespoint_select2').val() + '">');
    input_field.append('<input type="hidden" name="authorization" value="' + $('.authorization_select2').val() + '">');
    input_field.append('<input type="hidden" name="item_type" value="' + $('.item_type').val() + '">');
    input_field.append('<input type="hidden" name="request_type" value="' + $('.request_type').val() + '">');
    input_field.append('<input type="hidden" name="is_it" value="' + $('.is_it').val() + '">');
    input_field.append('<input type="hidden" name="budget_type" value="' + $('.budget_type').val() + '">');
    input_field.append('<input type="hidden" name="division" value="' + $('.division_select').val() + '">');
    input_field.append('<input type="hidden" name="indirect_salespoint_id" value="' + $('.indirect_salespoint').val() + '">');
    input_field.append('<input type="hidden" name="reason" value="' + $('.reason').val() + '">');
    input_field.append('<input type="hidden" name="is_over_budget" value="' + hasil + '">');
    input_field.append('<input type="hidden" name="is_over_budget_hidden" value="' + $('.is_over_budget_hidden').val() + '">');

    let reason_over_budget = $('.reason_over_budget').val();
    input_field.append('<input type="hidden" name="reason_over_budget" value="' + reason_over_budget + '">');

    item_list.each(function (index, el) {
        input_field.append('<input type="hidden" name="item[' + index + '][id]" value="' + $(el).data('id') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][budget_pricing_id]" value="' + $(el).data('budget_pricing_id') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][maintenance_budget_id]" value="' + $(el).data('maintenance_budget_id') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][ho_budget_id]" value="' + $(el).data('ho_budget_id') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][name]" value="' + $(el).data('name') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][price]" value="' + $(el).data('price') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][count]" value="' + $(el).data('count') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][brand]" value="' + $(el).data('brand') + '">');
        input_field.append('<input type="hidden" name="item[' + index + '][type]" value="' + $(el).data('type') + '">');

        input_field.append('<input type="hidden" name="item[' + index + '][item_max_price]" value="' + $(el).data('max') + '">');

        $(el).find('.attachment').each(function (att_index, att_el) {
            let filename = $(att_el).prop('download');
            let file = $(att_el).prop('href');
            input_field.append('<input type="hidden" name="item[' + index + '][attachments][' + att_index + '][filename]" value="' + filename + '">');
            // base 64 data
            input_field.append('<input type="hidden" name="item[' + index + '][attachments][' + att_index + '][file]" value="' + file + '">');
        });
        if ($(el).data('files')) {
            $(el).data('files').forEach((file, index_file) => {
                input_field.append('<input type="hidden" name="item[' + index + '][files][' + index_file + '][id]" value="' + file.id + '">');
                input_field.append('<input type="hidden" name="item[' + index + '][files][' + index_file + '][file_completement_id]" value="' + file.file_completement_id + '">');
                input_field.append('<input type="hidden" name="item[' + index + '][files][' + index_file + '][name]" value="' + file.name + '">');
                input_field.append('<input type="hidden" name="item[' + index + '][files][' + index_file + '][file]" value="' + file.file + '">');
            });
        }
    });

    vendor_item_list.each(function (index, el) {
        input_field.append('<input type="hidden" name="vendor[' + index + '][id]" value="' + $(el).data('id') + '">');
        input_field.append('<input type="hidden" name="vendor[' + index + '][vendor_id]" value="' + $(el).data('vendor_id') + '">');
        input_field.append('<input type="hidden" name="vendor[' + index + '][name]" value="' + $(el).data('name') + '">');
        input_field.append('<input type="hidden" name="vendor[' + index + '][sales]" value="' + $(el).data('sales') + '">');
        input_field.append('<input type="hidden" name="vendor[' + index + '][phone]" value="' + $(el).data('phone') + '">');
    });

    if (vendor_item_list.length < 2) {
        let filename = $('#vendor_ba_preview').prop('download');
        let file = $('#vendor_ba_preview').prop('href');
        if (filename != "null") {
            input_field.append('<input type="hidden" name="ba_vendor_name" value="' + filename + '">');
            input_field.append('<input type="hidden" name="ba_vendor_file" value="' + file + '">');
        }
    }

    $('.opt_attachment').each(function (index, el) {
        let file = $(el).prop('href');
        let filename = $(el).prop('download');
        input_field.append('<input type="hidden" name="opt_attach[' + index + '][file]" value="' + file + '">');
        input_field.append('<input type="hidden" name="opt_attach[' + index + '][name]" value="' + filename + '">');
    });

    // FRI FORM
    if ($('.is_it').val() == true) {
        input_field.append($('#fri_form_create input').clone());
    }
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

